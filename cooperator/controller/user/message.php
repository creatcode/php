<?php
class ControllerUserMessage extends Controller {
    private $cooperator_id = null;
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');

        // 加载coupon Model
        $this->load->library('sys_model/message', true);
        $this->load->library('sys_model/user', true);
    }

    /**
     * 系统消息列表
     */
    public function index() {
        $filter = array();
        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $fields = 'm.*';
        $order = 'msg_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_message->getMessageList($condition, $fields, $order, $limit);
        $total = $this->sys_model_message->getTotalMessages($condition);

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                if ($item['user_id'] == '0') {
                    $item['user_name'] = '所有用户';
                } else {
                    $item['user_name'] = '自定义用户';
                }
                $item['msg_time'] = isset($item['msg_time']) && $item['msg_time'] > 0 ? date('Y-m-d H:i:s', $item['msg_time']) : '';
                $item['delete_action'] = $this->url->link('user/message/delete', 'msg_id='.$item['msg_id']);
                $item['info_action'] = $this->url->link('user/message/info', 'msg_id='.$item['msg_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/message/add'));

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->response->setOutput($this->load->view('user/message_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('消息标题');
        $this->setDataColumn('用户');
        $this->setDataColumn('消息时间');
        return $this->data_columns;
    }

    /**
     * 添加系统消息
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('msg_title', 'msg_image', 'user_type', 'user', 'msg_abstract', 'msg_link', 'msg_content'));
            $now = time();

            // 全部用户
            $msg_id = '';
            if ($input['user_type'] == 0) {
                $data = array(
                    'user_id' => 0,
                    'cooperator_id' => $this->cooperator_id,
                    'msg_time' => $now,
                    'msg_image' => $input['msg_image'],
                    'msg_title' => $input['msg_title'],
                    'msg_abstract' => $input['msg_abstract'],
                    'msg_content' => $input['msg_content'],
                    'msg_link' => $input['msg_link'],
                );
                $msg_id = $this->sys_model_message->addMessage($data);
            } elseif ($input['user_type'] == 1) {
                // 自定义用户
                if (is_array($input['user']) && !empty($input['user'])) {
                    $user_id = implode(',', $input['user']);
                    $data = array(
                        'user_id' => $user_id,
                        'cooperator_id' => $this->cooperator_id,
                        'msg_time' => $now,
                        'msg_image' => $input['msg_image'],
                        'msg_title' => $input['msg_title'],
                        'msg_abstract' => $input['msg_abstract'],
                        'msg_content' => $input['msg_content'],
                        'msg_link' => $input['msg_link'],
                    );
                    $msg_id = $this->sys_model_message->addMessage($data);
                }
            }

            $this->session->data['success'] = '添加系统消息成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加系统消息：ID ' . $msg_id,
                'log_ip' => $this->request->ip_address(),
                'log_type_id' => 2,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);
            
            $filter = array();

            $this->load->controller('common/base/redirect', $this->url->link('user/message', $filter, true));
        }

        $this->assign('title', '系统消息加');
        $this->getForm();
    }

    /**
     * 消息详情
     */
    public function info() {
        $msg_id = $this->request->get('msg_id');
        $condition = array(
            'msg_id' => $msg_id,
            'cooperator_id' => $this->cooperator_id,
        );
        $info = $this->sys_model_message->getMessageInfo($condition);
        $info['msg_image_url'] = HTTP_IMAGE . $info['msg_image'];

        $info['user_name'] = '';
        if ($info['user_id'] == 0) {
            $info['user_name'] = '所有的用户';
        } else {
            $user_names = array();
            $condition =array(
                'user_id' => array('in', $info['user_id'])
            );
            $users = $this->sys_model_user->getUserList($condition);
            if (is_array($users) && !empty($users)) {
                foreach ($users as $user) {
                    $user_names[] = $user['mobile'];
                }
            }
            $info['user_name'] = implode('<br/>', $user_names);
        }

        
        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('user/message'));

        $this->response->setOutput($this->load->view('user/message_info', $this->output));
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('msg_title', 'msg_image', 'user_type', 'msg_abstract', 'msg_link', 'msg_content'));

        $info['msg_image_url'] = !empty($info['msg_image']) ? HTTP_IMAGE . $info['msg_image'] : getDefaultImage();

        $condition = array(
            'cooperator_id' => $this->cooperator_id,
        );
        $users = $this->sys_model_user->getUserList($condition);

        $this->assign('data', $info);
        $this->assign('users', $users);
        $this->assign('action', $this->cur_url);
        $this->assign('return_action', $this->url->link('user/message'));
        $this->assign('upload_action', $this->url->link('common/upload'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/message_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('msg_title', 'msg_image', 'msg_abstract', 'msg_link', 'msg_content'));

        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }
        return !$this->error;
    }
}