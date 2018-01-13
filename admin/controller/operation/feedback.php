<?php
class ControllerOperationFeedback extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载feedback Model
        $this->load->library('sys_model/feedback', true);
        $this->assign('lang',$this->language->all());
    }

    /**
     * 反馈列表
     */
    public function index() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //AJAX请求
            if ($this->request->get('method') == 'json') {
                $this->apiIndex();
                return;
            }
        }

        $filter = $this->request->get(array('filter_type', 'user_name', 'content', 'add_time'));

        $condition = array();
        if (!empty($filter['user_name'])) {
            $condition['user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (!empty($filter['content'])) {
            $condition['content'] = array('like', "%{$filter['content']}%");
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('gt', strtotime($add_time[0])),
                array('lt', bcadd(86399, strtotime($add_time[1])))
            );
        }

        $filter_types = array(
            'user_name' => $this->language->get('t2'),
            'content' => $this->language->get('t3'),
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

        $result = $this->sys_model_feedback->getFeedbackList($condition, $order, $limit);
        $total = $this->sys_model_feedback->getTotalFeedbacks($condition);


        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['add_time'] = isset($item['add_time']) && !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';

                $item['edit_action'] = $this->url->link('operation/feedback/edit', 'feedback_id='.$item['feedback_id']);
                $item['delete_action'] = $this->url->link('operation/feedback/delete', 'feedback_id='.$item['feedback_id']);
                $item['info_action'] = $this->url->link('operation/feedback/info', 'feedback_id='.$item['feedback_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('operation/feedback/add'));

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

        $this->assign('export_action', $this->url->link('operation/feedback/export'));
        $this->assign('time_type',get_time_type());
        $this->response->setOutput($this->load->view('operation/feedback_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn($this->language->get('t2'));
        $this->setDataColumn($this->language->get('t3'));
        $this->setDataColumn($this->language->get('t4'));
        return $this->data_columns;
    }

    /**
     * index AJAX请求
     */
    protected function apiIndex() {
        if (isset($this->request->post['page'])) {
            $page = (int)$this->request->post['page'];
        } else {
            $page = 1;
        }

        $condition = array();
        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_feedback->getFeedbackList($condition, $order, $limit);
        $total = $this->sys_model_feedback->getTotalFeedbacks($condition);

        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'content' => $v['content'],
                    'add_time' => date('Y-m-d H:i:s', $v['add_time']),
                    'uri' => $this->url->link('operation/feedback/info', 'feedback_id='. $v['feedback_id'])
                );
            }
        }

        $statisticsMessages = $this->load->controller('common/base/statisticsMessages');

        $data = array(
            'title' => array(
                'content' => '反馈内容',
                'add_time' => '反馈时间'
            ),
            'list' => $list,
            'page' => $page,
            'total' => ceil($total / $rows),
            'statistics' => $statisticsMessages
        );

        $this->response->showSuccessResult($data, '获取成功');
    }

    /**
     * 添加反馈
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('feedback_sn', 'type', 'lock_sn'));
            $now = time();
            $data = array(
                'feedback_sn' => $input['feedback_sn'],
                'type' => (int)$input['type'],
                'lock_sn' => $input['lock_sn'],
                'add_time' => $now
            );
            $this->sys_model_feedback->addFeedback($data);

            $this->session->data['success'] = '添加反馈成功！';
            
            $filter = $this->request->get(array('filter_type', 'feedback_sn', 'type', 'lock_sn', 'is_using'));

            $this->load->controller('common/base/redirect', $this->url->link('operation/feedback', $filter, true));
        }

        $this->assign('title', '反馈添加');
        $this->getForm();
    }

    /**
     * 编辑反馈
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('feedback_sn', 'type', 'lock_sn'));
            $feedback_id = $this->request->get['feedback_id'];
            $data = array(
                'feedback_sn' => $input['feedback_sn'],
                'type' => (int)$input['type'],
                'lock_sn' => $input['lock_sn']
            );
            $condition = array(
                'feedback_id' => $feedback_id
            );
            $this->sys_model_feedback->updateFeedback($condition, $data);

            $this->session->data['success'] = '编辑反馈成功！';

            $filter = $this->request->get(array('filter_type', 'feedback_sn', 'type', 'lock_sn', 'is_using'));

            $this->load->controller('common/base/redirect', $this->url->link('operation/feedback', $filter, true));
        }

        $this->assign('title', '编辑反馈');
        $this->getForm();
    }

    /**
     * 删除反馈
     */
    public function delete() {
        if (isset($this->request->get['feedback_id']) && $this->validateDelete()) {
            $condition = array(
                'feedback_id' => $this->request->get['feedback_id']
            );
            $this->sys_model_feedback->deleteFeedback($condition);

            $this->session->data['success'] = '删除反馈成功！';
        }
        $filter = $this->request->get(array('filter_type', 'feedback_sn', 'type', 'lock_sn', 'is_using'));
        $this->load->controller('common/base/redirect', $this->url->link('operation/feedback', $filter, true));
    }

    /**
     * 反馈详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $feedback_id = $this->request->get('feedback_id');
        $condition = array(
            'feedback_id' => $feedback_id
        );
        $info = $this->sys_model_feedback->getFeedbackInfo($condition);
        if (!empty($info)) {
            $model = array(
                'type' => get_feedback_type(),
                'is_using' => get_common_boolean()
            );
            foreach ($model as $k => $v) {
                $info[$k] = isset($v[$info[$k]]) ? $v[$info[$k]] : '';
            }
        }

        $this->assign('data', $info);

        $this->response->setOutput($this->load->view('operation/feedback_info', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('filter_type', 'user_name', 'content', 'add_time'));

        $condition = array();
        if (!empty($filter['user_name'])) {
            $condition['user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (!empty($filter['content'])) {
            $condition['content'] = array('like', "%{$filter['content']}%");
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

        $result = $this->sys_model_feedback->getFeedbackList($condition, $order, $limit);
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'user_name' => $v['user_name'],
                    'content' => $v['content'],
                    'add_time' => date("Y-m-d H:i:s",$v['add_time']),
                );
            }
        }

        $data = array(
            'title' => '客户反馈管理',
            'header' => array(
                'user_name' => '用户名',
                'content' => '反馈内容',
                'add_time' => '反馈时间',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('feedback_sn', 'type', 'lock_sn'));
        $feedback_id = $this->request->get('feedback_id');
        if (isset($this->request->get['feedback_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'feedback_id' => $this->request->get['feedback_id']
            );
            $info = $this->sys_model_feedback->getFeedbackInfo($condition);
        }

        $this->assign('data', $info);
        $this->assign('types', get_feedback_type());
        $this->assign('action', $this->cur_url . '&feedback_id=' . $feedback_id);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('operation/feedback_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('feedback_sn', 'type', 'lock_sn'));

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

    /**
     * 验证删除条件
     */
    private function validateDelete() {
        return !$this->error;
    }
}