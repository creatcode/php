<?php
class ControllerUserUser extends Controller {
    private $cooperator_id = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');

        // 加载user Model
        $this->load->library('sys_model/user', true);
    }

    public function index() {
        $filter = $this->request->get(array('filter_type', 'mobile', 'deposit', 'available_deposit', 'credit_point', 'available_state', 'add_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (is_numeric($filter['deposit'])) {
            $condition['deposit'] = (float)$filter['deposit'];
        }
        if (is_numeric($filter['available_deposit'])) {
            $condition['available_deposit'] = (float)$filter['available_deposit'];
        }
        if (is_numeric($filter['credit_point'])) {
            $condition['credit_point'] = (int)$filter['credit_point'];
        }
        if (is_numeric($filter['available_state'])) {
            $condition['available_state'] = (int)$filter['available_state'];
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('gt', strtotime($add_time[0])),
                array('lt', bcadd(86399, strtotime($add_time[1])))
            );
        }

        $filter_types = array(
            'mobile' => '手机号码',
            'credit_point' => '信用积分'
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_user->getUserList($condition, '*', $order, $limit);
        $total = $this->sys_model_user->getTotalUsers($condition);

        $available_states = get_common_boolean();
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['available_state'] = isset($available_states[$item['available_state']]) ? $available_states[$item['available_state']] : '';
                $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);

                $item['edit_action'] = $this->url->link('user/user/edit', 'user_id='.$item['user_id']);
                $item['delete_action'] = $this->url->link('user/user/delete', 'user_id='.$item['user_id']);
                $item['info_action'] = $this->url->link('user/user/info', 'user_id='.$item['user_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('available_states', $available_states);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/user/add'));

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

        $this->assign('export_action', $this->url->link('user/user/export'));

        $this->response->setOutput($this->load->view('user/user_list', $this->output));
    }

    // 表格字段
    protected function getDataColumns() {
        $this->setDataColumn('手机号码');
        $this->setDataColumn('押金(元)');
        $this->setDataColumn('可用金额(元)');
        $this->setDataColumn('信用积分');
        $this->setDataColumn('是否可踩车');
        $this->setDataColumn('注册时间');
        return $this->data_columns;
    }


    /**
     * 单车详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $user_id = $this->request->get('user_id');

        $condition = array(
            'user_id' => $user_id,
            'cooperator_id' => $this->cooperator_id
        );
        $info = $this->sys_model_user->getUserInfo($condition);
        if (!empty($info)) {
            $info['login_time'] = date('Y-m-d H:i:s', $info['login_time']);
            $info['add_time'] = date('Y-m-d H:i:s', $info['add_time']);
        }

        $verify_states = $available_states = get_common_boolean();

        $this->assign('verify_states', $verify_states);
        $this->assign('available_states', $available_states);
        $this->assign('return_action', $this->url->link('user/user'));
        $this->assign('data', $info);

        $this->response->setOutput($this->load->view('user/user_info', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
//        @ini_set('memory_limit', '512M');
        $filter = $this->request->post(array('filter_type', 'mobile', 'deposit', 'available_deposit', 'credit_point', 'available_state', 'add_time'));

        $condition = array();
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (is_numeric($filter['deposit'])) {
            $condition['deposit'] = (float)$filter['deposit'];
        }
        if (is_numeric($filter['available_deposit'])) {
            $condition['available_deposit'] = (float)$filter['available_deposit'];
        }
        if (is_numeric($filter['credit_point'])) {
            $condition['credit_point'] = (int)$filter['credit_point'];
        }
        if (is_numeric($filter['available_state'])) {
            $condition['available_state'] = (int)$filter['available_state'];
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('gt', strtotime($add_time[0])),
                array('lt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        $order = 'add_time DESC';
        $limit = '';

        $result = $this->sys_model_user->getUserList($condition, '*', $order, $limit);
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $available_states = get_common_boolean();
                $list[] = array(
                    'mobile' => $v['mobile'],
                    'deposit' => $v['deposit'],
                    'available_deposit' => $v['available_deposit'],
                    'credit_point' => $v['credit_point'],
                    'available_state' => $available_states[$v['available_state']],
                    'add_time' => date("Y-m-d H:i:s",$v['add_time']),
                );
            }
        }

        $data = array(
            'title' => '用户列表',
            'header' => array(
                'mobile' => '手机号码',
                'deposit' => '押金(元)',
                'available_deposit' => '可用金额(元)',
                'credit_point' => '信用积分',
                'available_state' => '是否可踩车',
                'add_time' => '注册时间',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    public function getUserList() {
        $filter = $this->request->get(array('mobile'));
        $condition = array();

        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_user->getUserList($condition, 'user_id, mobile', $order, $limit);
        $total = $this->sys_model_user->getTotalUsers($condition);

        $available_states = get_common_boolean();
//        if (is_array($result) && !empty($result)) {
//            foreach ($result as &$item) {
//
//            }
//        }

//        $data_columns = $this->getDataColumns();
//        $this->assign('data_columns', $data_columns);
//        $this->assign('available_states', $available_states);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);

//        $this->assign('action', $this->cur_url);
//        $this->assign('add_action', $this->url->link('user/user/add'));

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
        $this->assign('static', HTTP_CATALOG);
//        $this->assign('export_action', $this->url->link('user/user/export'));

        $this->response->setOutput($this->load->view('user/modal_user_list', $this->output));
    }
}