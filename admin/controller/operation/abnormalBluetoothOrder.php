<?php

class ControllerOperationAbnormalBluetoothOrder extends Controller
{
    private $sys_model_orders;
    private $sys_model_intelligent;

    /**
     * ControllerOperationAbnormalBluetoothOrder constructor.
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
//        $this->load->library('sys_model/orders', true);
//        $this->load->library('sys_model/intelligent', true);
        $this->sys_model_orders = new Sys_Model\orders($registry);
        $this->sys_model_intelligent = new Sys_Model\intelligent($registry);
    }


    /**
     * 蓝牙单车预计费未结束订单
     */
    public function index()
    {
        $filter = $this->request->get(array('add_time'));
        $condition = array();
        $condition['order_state'] = 3;
        $order = 'order_id desc';
        $field = 'order_id,order_sn,order_state,lock_sn,bicycle_sn,user_name,pay_amount,start_time,add_time';
        $result = $this->sys_model_orders->getOrdersList($condition, $order, '', $field);
        $bluetooth_order_list = [];
        //判断最新订单是不是之前那张订单而且是不是在骑行状态或者已经结束，如果不是的话，就是判断上一张蓝牙订单没有正常结束
        //循环这个语句导致有可能超时
        foreach ($result as $item){
            $info = $this->sys_model_intelligent->getAbnormalOrdersList(['bicycle_sn'=>$item['bicycle_sn']], 'order_id desc', '0,1', 'order_id,order_state');
            if(($info[0]['order_state'] == 1 || $info[0]['order_state'] == 2) && $info[0]['order_id'] != $item['order_id']){
                $bluetooth_order_list[] = $item;
//                break;
            }
        }

        $this->assign('data_rows', $bluetooth_order_list);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('info_action', $this->url->link('operation/abnormalBluetoothOrder/info'));
        $this->response->setOutput($this->load->view('intelligent/abnormal_bluetooth_order_list', $this->output));
    }

    /**
     * 蓝牙的单车出现未计费订单的关联订单
     */
    public function info()
    {
        $order_id = $this->request->get['order_id'];
        $bicycle_sn = $this->request->get['bicycle_sn'];
        $result = [];
        if ($order_id && $bicycle_sn) {
            $condition['order_id'] = ['egt', $order_id];
            $condition['bicycle_sn'] = $bicycle_sn;

            $order = 'order_id desc';
            $field = 'orders.*, cooperator.cooperator_name';
            $join = array(
                'cooperator' => 'cooperator.cooperator_id=orders.cooperator_id',
            );
            $result = $this->sys_model_orders->getOrdersList($condition, $order, '', $field, $join);
            $order_state = get_order_state();
            if (is_array($result) && !empty($result)) {
                foreach ($result as &$item) {
                    $item['order_state'] = isset($order_state[$item['order_state']]) ? $order_state[$item['order_state']] : '';
                    $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                    $item['start_time'] = !empty($item['start_time']) ? date('Y-m-d H:i:s', $item['start_time']) : '';
                    $item['end_time'] = !empty($item['end_time']) ? date('Y-m-d H:i:s', $item['end_time']) : '';
                    $item['info_action'] = $this->url->link('user/order/info', 'order_id=' . $item['order_id']);
                }
            }
        }
        $this->assign('data_rows', $result);
        $this->assign('title', '蓝牙单车预计费未结束关联订单');
        $this->response->setOutput($this->load->view('intelligent/user_abnormal_order_list', $this->output));
    }

    protected function page($total, $page, $rows, $filter, $offset)
    {
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));
        return array('pagination' => $pagination, 'results' => $results);
    }
}