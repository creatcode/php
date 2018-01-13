<?php

class ControllerOperationCustomerServiceFeedback extends Controller
{
    protected $error = [];
    private $cur_url = null;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->cur_url = $this->url->link($this->request->get['route']);
    }

    /**
     * 客服反馈列表
     */
    public function index()
    {
        $filter = $this->request->get(array('type_string'));

        $condition = array();
        if (!empty($filter['type_string'])) {
            $condition['type_string'] = $filter['type_string'];
        }
        $this->assign('filter', $filter);

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }
        $order = '';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $this->load->library('sys_model/feedback_customer_service', true);
        $total = $this->sys_model_feedback_customer_service->getTotalFeedbackCustomerService($condition);
        $stations = $this->sys_model_feedback_customer_service->getFeedbackCustomerServiceList($condition, $order, $limit, '*');
//        echo json_encode($stations);
        $type = get_feedback_type();
        foreach ($stations as $key => &$val) {
            $val['type_string'] = $type[$val['type_string']];
            $val['edit_action'] = $this->url->link('operation/customerServiceFeedback/edit', ['feedback_id' => $val['feedback_id']]);
            $val['delete_action'] = $this->url->link('operation/customerServiceFeedback/delete', ['feedback_id' => $val['feedback_id']]);
            $val['create_time'] = date("Y-m-d H:i", $val['create_time']);
        }

        $this->assign('title', '客服反馈列表');
        $this->assign('type_string', get_feedback_type());
        $this->assign('add_action', $this->url->link('operation/customerServiceFeedback/add'));
        $this->assign('data_rows', $stations);
        $this->assign('data_columns', $this->getDataColumns());

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();

        $this->assign('pagination', $pagination);
        $this->assign('action', $this->url->link('operation/customerServiceFeedback'));
        $this->response->setOutput($this->load->view("operation/customerservicefeedback_list", $this->output));

    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns()
    {
        $this->setDataColumn('用户名称');
        $this->setDataColumn('客服名称');
//        $this->setDataColumn('工单号');
        $this->setDataColumn('反馈内容');
        $this->setDataColumn('类别');
        $this->setDataColumn('反馈时间');
        $this->setDataColumn('操作');
        return $this->data_columns;
    }

    /**
     * 新增反馈
     */
    public function add()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $input = $this->request->post(array('user_name', 'type_string', 'operation_id', 'content'));
            $admin_id = $this->logic_admin->getData();
            $data = array(
                'user_name' => $input['user_name'],
                'type_string' => $input['type_string'],
                'operation_id' => $input['operation_id'],
                'content' => $input['content'],
                'create_time' => time(),
                'service_name' => $admin_id['nickname'],
            );
            if ($data['type_string'] == 4) {

                $con = array(
                    'wo_id' => build_order_no(),
                    'service_name' => $admin_id['nickname'],
                    'admin_id' => $input['operation_id'],
                    'content' => $input['content'],
                    'create_time' => time(),
                    'user_name' => $input['user_name'],
                );
                $this->load->library('sys_model/work_order');
               $this->sys_model_work_order->addWorkOrder($con);
//               var_dump($result);
//                exit;
            }
            $this->load->library('sys_model/feedback_customer_service');
            $feedback_id = $this->sys_model_feedback_customer_service->addFeedbackCustomerService($data);
            $this->session->data['success'] = '添加成功！';

            $this->load->controller('common/base/redirect', $this->url->link('operation/customerServiceFeedback', [], true));
        }
        $this->load->library('sys_model/admin');
        $where['type'] = 3;
        $operations = $this->sys_model_admin->getAdminList($where, '', '', 'admin_id,nickname');
        $type = get_feedback_type();
        $this->assign('title', '添加反馈');
        $this->assign('operations', $operations);
        $this->assign('type', $type);
        $this->assign('return_action', $this->url->link('operation/customerServiceFeedback'));
        $this->assign('action', $this->url->link('operation/customerServiceFeedback/add'));
        $this->response->setOutput($this->load->view("operation/customerservicefeedback_add", $this->output));
    }

    /**
     * 编辑反馈
     */
    public function edit()
    {
        $feedback_id = $this->request->get['feedback_id'];
        if (!$feedback_id) {
            $this->load->controller('common/base/redirect', $this->url->link('operation/customerServiceFeedback', [], true));
        }
        $this->load->library('sys_model/feedback_customer_service');
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $input = $this->request->post(array('feedback_id', 'type_string', 'content'));
            $data = array(
                'type_string' => $input['type_string'],
                'content' => $input['content'],

            );
            $where['feedback_id'] = $feedback_id;

            $feedback_id = $this->sys_model_feedback_customer_service->updateFeedbackCustomerService($where, $data);

            $this->session->data['success'] = '编辑成功！';
            $this->load->controller('common/base/redirect', $this->url->link('operation/customerServiceFeedback', [], true));
        }
        $data = $this->sys_model_feedback_customer_service->getFeedbackCustomerServiceList(['feedback_id' => $feedback_id], '', '1', '*', [])[0];


        $type = get_feedback_type();
        $this->assign('title', '编辑反馈');
        $this->assign('data', $data);
//        $this->assign('regions', $regions);
        $this->assign('station_states', get_station_state());
        $this->assign('type', $type);
        $this->assign('return_action', $this->url->link('operation/customerServiceFeedback'));
        $this->assign('action', $this->url->link('operation/customerServiceFeedback/edit', ['feedback_id' => $feedback_id]));
        $this->response->setOutput($this->load->view("operation/customerservicefeedback_edit", $this->output));
    }


    public function delete()
    {
        $feedback_id = (int)$this->request->get('feedback_id');
        $this->load->library('sys_model/feedback_customer_service');
        $res = $this->sys_model_feedback_customer_service->deleteFeedbackCustomerService(['feedback_id' => $feedback_id]);
        if ($res) {
            $this->session->data['success'] = '删除成功';
        } else {
            $this->session->data['error']['warning'] = '删除失败';
        }
        $this->load->controller('common/base/redirect', $this->url->link('operation/customerServiceFeedback'));
    }


    /**
     * 验证表单数据
     * @return bool
     */
    protected function validateForm()
    {
        $input = $this->request->post(array('user_name', 'type_string', 'operation_id'));

        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }

        //
        //todo 略

        return !$this->error;
    }


    /**
     * 验证删除条件
     */
    private function validateDelete()
    {
        return !$this->error;
    }


}
