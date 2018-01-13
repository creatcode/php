<?php

use Tool\ArrayUtil;

class ControllerOperationWorkOrder extends Controller
{

    private $cur_url = null;
    private $error = null;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';
    }

    /**
     * 工单列表
     */
    public function index()
    {
        $filter = $this->request->get(array());
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }
        $rows = $this->config->get('config_limit_admin');
//        $total = 100;
        $type = get_work_order_state();;
        $condition = array();
        $order = '';
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $this->load->library('sys_model/work_order', true);
        $total = $this->sys_model_work_order->getTotalWorkOrder($condition);
        $works = $this->sys_model_work_order->getWorkOrderList($condition, $order, $limit, '*');
//        echo json_encode($stations);
        $operators = [];
        foreach ($works as $key => &$val) {
            array_push($operators, $val['admin_id']);
            $val['status_string'] = $type[$val['status_string']];
            $val['edit_action'] = $this->url->link('operation/workorder/edit', ['id' => $val['id']]);
            $val['delete_action'] = $this->url->link('operation/workorder/delete', ['id' => $val['id']]);
            $val['create_time'] = date("Y-m-d H:i", $val['create_time']);
        }

        $this->load->library('sys_model/admin');
        $where['type'] = 3;
        $where['admin_id'] = ['in', $operators];
        $operations = $this->sys_model_admin->getAdminList($where, '', '', 'admin_id,nickname');
        $operations = ArrayUtil::changeArrayKey($operations, 'admin_id');

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();

        $this->assign('pagination', $pagination);
        $this->assign('operations', $operations);
        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $works);

        $this->response->setOutput($this->load->view('operation/workorder_list', $this->output));
    }


    /**
     * 编辑工单
     */
    public function edit()
    {
        $id = $this->request->get['id'];
        if (!$id) {
            $this->load->controller('common/base/redirect', $this->url->link('operation/workorder', [], true));
        }
        $this->load->library('sys_model/work_order');
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $input = $this->request->post(array('id', 'admin_id', 'content'));
            $data = array(
                'admin_id' => $input['admin_id'],
                'content' => $input['content'],
            );
            $where['id'] = $id;

            $id = $this->sys_model_work_order->updateWorkOrder($where, $data);

            $this->session->data['success'] = '编辑成功！';
            $this->load->controller('common/base/redirect', $this->url->link('operation/workorder', [], true));
        }
        $data = $this->sys_model_work_order->getWorkOrderList(['id' => $id], '', '1', '*', [])[0];

        $this->load->library('sys_model/admin');
        $where['type'] = 3;
        $operations = $this->sys_model_admin->getAdminList($where, '', '', 'admin_id,nickname');

//        $type = $this->gettype();
        $this->assign('title', '编辑工单');
        $this->assign('data', $data);
        $this->assign('operations', $operations);
        $this->assign('station_states', get_station_state());
//        $this->assign('type', $type);
        $this->assign('return_action', $this->url->link('operation/workorder'));
        $this->assign('action', $this->url->link('operation/workOrder/edit', ['id' => $id]));
        $this->response->setOutput($this->load->view("operation/workorder_edit", $this->output));
    }

    /**
     *删除
     */
    public function delete()
    {
        $id = (int)$this->request->get('id');
        $this->load->library('sys_model/work_order');
        $res = $this->sys_model_work_order->deleteWorkOrder(['id' => $id]);
        if ($res) {
            $this->session->data['success'] = '删除成功';
        } else {
            $this->session->data['error']['warning'] = '删除失败';
        }
        $this->load->controller('common/base/redirect', $this->url->link('operation/workorder'));
    }

//    /**
//     * 工单状态
//     * @return type
//     */
//    public function gettype()
//    {
//        return $type = array(
//            1 => '已处理',
//            2 => '待处理',
//            3 => '待处理'
//        );
//    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns()
    {

        $this->setDataColumn('工单号');

        $this->setDataColumn('运维人员');
        $this->setDataColumn('客服人员');
        $this->setDataColumn('工单内容');
        $this->setDataColumn('工单状态');
        $this->setDataColumn('发布时间');
        $this->setDataColumn('操作');
        return $this->data_columns;
    }

}
