<?php
error_reporting(E_ALL); //E_ALL
function cache_shutdown_error() {
    $_error = error_get_last();
    if ($_error && in_array($_error['type'], array(1, 4, 16, 64, 256, 4096, E_ALL))) {
        echo '<font color=red>你的代码出错了：</font></br>';
        echo '致命错误:' . $_error['message'] . '</br>';
        echo '文件:' . $_error['file'] . '</br>';
        echo '在第' . $_error['line'] . '行</br>';
    }
}
register_shutdown_function("cache_shutdown_error");
use Tool\ArrayUtil;
class ControllerUserOrder extends Controller {
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载bicycle Model
        $this->load->library('sys_model/orders', true);
        $this->assign('lang',$this->language->all());
    }

    /**
     * 消费记录列表
     */
    public function index() {
        $filter = $this->request->get(array('filter_type', 'order_sn', 'lock_sn', 'bicycle_sn', 'user_name',  'region_name', 'order_state', 'add_time', 'start_time', 'end_time', 'settlement_time', 'amount' ,'ride_time','user_type','time_type','city_id','region_id'));

        $condition = array();
        if (!empty($filter['order_sn'])) {
            $condition['order_sn'] = array('like', "%{$filter['order_sn']}%");
        }
        if (!empty($filter['lock_sn'])) {
            $condition['lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['user_name'])) {
            $condition['user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (is_numeric($filter['city_id'])) {
            $condition['user.city_id'] = (int)$filter['city_id'];
        }
        if (is_numeric($filter['region_id'])) {
            $condition['user.region_id'] = (int)$filter['region_id'];
        }
        if (is_numeric($filter['order_state'])) {
            $condition['order_state'] = (int)$filter['order_state'];
        }
        if (is_numeric($filter['user_type'])) {
            $condition['user_type'] = (int)$filter['user_type'];
        }
        $pdr_add_time = explode(' 至 ', $filter['add_time']);
        if($filter['time_type']==1){
            if (!empty($filter['add_time'])) {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_add_time[1].'-12-31'))))
                );
            } else {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['add_time'])) {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['add_time'])) {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
                );
            }else{
                $condition['orders.add_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['add_time'])) {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
                );
            }
        }
        ////////-------------2
        $pdr_start_time = explode(' 至 ', $filter['start_time']);
        if($filter['time_type']==1){
            if (!empty($filter['start_time'])) {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime($pdr_start_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_start_time[1].'-12-31'))))
                );
            } else {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['start_time'])) {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime($pdr_start_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_start_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['start_time'])) {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime($pdr_start_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_start_time[1])))
                );
            }else{
                $condition['orders.start_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['start_time'])) {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime($pdr_start_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_start_time[1])))
                );
            }
        }
        ///////////-------3
        $pdr_end_time = explode(' 至 ', $filter['end_time']);
        if($filter['time_type']==1){
            if (!empty($filter['end_time'])) {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime($pdr_end_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_end_time[1].'-12-31'))))
                );
            } else {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['end_time'])) {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime($pdr_end_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_end_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['end_time'])) {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime($pdr_end_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_end_time[1])))
                );
            }else{
                $condition['orders.end_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['end_time'])) {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime($pdr_end_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_end_time[1])))
                );
            }
        }
        //////////////------4
        $pdr_settlement_time = explode(' 至 ', $filter['settlement_time']);
        if($filter['time_type']==1){
            if (!empty($filter['settlement_time'])) {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime($pdr_settlement_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_settlement_time[1].'-12-31'))))
                );
            } else {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['settlement_time'])) {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime($pdr_settlement_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_settlement_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['settlement_time'])) {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime($pdr_settlement_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_settlement_time[1])))
                );
            }else{
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['settlement_time'])) {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime($pdr_settlement_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_settlement_time[1])))
                );
            }
        }
        /////////////
        if (is_numeric($filter['amount'])) {
            $condition['pay_amount'] = array('egt',(int)$filter['amount']);
        }

        if (is_numeric($filter['ride_time'])) {
            $condition['orders.start_time'] = array('elt',time() - $filter['ride_time']*60);
            $condition['orders.order_state'] = 1;
        }


        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }

        $filter_types = array(
            'order_sn' => '订单编号',
            'lock_sn' => 'RFID',
            'bicycle_sn' => '车辆编号',
            'user_name' => '用户名',
            // 'cooperator_name' => '合伙人',
            // 'region_name' => '区域',
        );
        $user_type = array(
            '0' => 'App用户',
            '1' => '刷卡用户',
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

        $order = 'orders.add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'orders.*, city.city_name,region.region_name,user.user_type';
        $join = array(
            'user' => 'user.user_id=orders.user_id',
            'city' => 'city.city_id=user.city_id',
            'region' => 'region.region_id=user.region_id'
        );

        $result = $this->sys_model_orders->getOrdersList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_orders->getTotalOrders($condition, $join);

        $order_state = get_order_state();

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['order_state'] = isset($order_state[$item['order_state']]) ? $order_state[$item['order_state']] : '';
                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['start_time'] = !empty($item['start_time']) ? date('Y-m-d H:i:s', $item['start_time']) : '';
                $item['end_time'] = !empty($item['end_time']) ? date('Y-m-d H:i:s', $item['end_time']) : '';
                $item['settlement_time'] = !empty($item['settlement_time']) ? date('Y-m-d H:i:s', $item['settlement_time']) : '';

                $item['edit_action'] = $this->url->link('user/order/edit', 'order_id='.$item['order_id']);
                $item['delete_action'] = $this->url->link('user/order/delete', 'order_id='.$item['order_id']);
                $item['info_action'] = $this->url->link('user/order/info', 'order_id='.$item['order_id']);
                $item['edit_order_amount_action'] = $this->url->link('user/order/editOrderAmount', 'order_id='.$item['order_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('time_type',get_time_type());
        $this->assign('filter_regions', $filter_regions);
        $this->assign('data_columns', $data_columns);
        $this->assign('user_type', $user_type);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('order_state', $order_state);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/order/add'));
        $this->assign('chart_action', $this->url->link('user/order/chart'));
        $this->assign('city_ranking_action', $this->url->link('user/order/city_ranking'));
        $this->assign('order_free_chart', $this->url->link('user/order/free_chart'));
        $this->assign('order_free_list', $this->url->link('user/order/free_list'));

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }
        
        if (isset($this->session->data['error'])) {
            $this->assign('error', array('warning'=>$this->session->data['error']));
            unset($this->session->data['error']);
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

        $this->assign('export_action', $this->url->link('user/order/export'));

        $this->response->setOutput($this->load->view('user/order_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('区域');
        $this->setDataColumn('城市');
        $this->setDataColumn('用户类型');
        $this->setDataColumn('订单编号');
        $this->setDataColumn('RFID');
        $this->setDataColumn('车辆编号');
        $this->setDataColumn('用户名');
        $this->setDataColumn('状态');
        $this->setDataColumn('实付金额');
        $this->setDataColumn('已退金额');
        $this->setDataColumn('开始时间');
        $this->setDataColumn('结束时间');
        $this->setDataColumn('下单时间');
        $this->setDataColumn('结算时间');
        return $this->data_columns;
    }

    /**
     * 消费记录详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $order_id = $this->request->get('order_id');
        $condition = array(
            'order_id' => $order_id
        );
        $info = $this->sys_model_orders->getOrdersInfo($condition);
        if (!empty($info)) {
            $info['order_status'] = $info['order_state'];
            $model = array(
                'order_state' => get_order_state()
            );
            foreach ($model as $k => $v) {
                $info[$k] = isset($v[$info[$k]]) ? $v[$info[$k]] : '';
            }

            $info['add_time'] = (isset($info['add_time']) && !empty($info['add_time'])) ? date('Y-m-d H:i:s', $info['add_time']) : '';
            $info['start_time'] = (isset($info['start_time']) && !empty($info['start_time'])) ? date('Y-m-d H:i:s', $info['start_time']) : '';
            $info['end_time'] = (isset($info['end_time']) && !empty($info['end_time'])) ? date('Y-m-d H:i:s', $info['end_time']) : '';
            $info['settlement_time'] = (isset($info['settlement_time']) && !empty($info['settlement_time'])) ? date('Y-m-d H:i:s', $info['settlement_time']) : '';
            $info['pdr_payment_time'] = (isset($info['pdr_payment_time']) && !empty($info['pdr_payment_time'])) ? date('Y-m-d H:i:s', $info['pdr_payment_time']) : '';
        }

        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('user/order'));

        $this->response->setOutput($this->load->view('user/order_info', $this->output));
    }

    /**
     *修改订单金额
     */
    public function editOrderAmount(){
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateAmount()) {
            $input = $this->request->post(array('order_sn', 'amount', 'reason'));
            
            $query_result = $result = $this->sys_model_orders->getOrderApplyList(array('order_sn' =>$input['order_sn'], 'apply_state'=>array('NEQ','2')));
            if(!empty($query_result)) {
                $this->session->data['error'] = '该订单已经申请过修改订单金额。';
            }
            else {
                $info = $this->sys_model_orders->getOrdersInfo(array('order_sn'=>$input['order_sn']));
                $now = time();
                $data = array(
                    'order_sn' => $info['order_sn'],
                    'apply_user_id' => $info['user_id'],
                    'apply_user_name' => $info['user_name'],
                    'apply_admin_id' => $this->logic_admin->getId(),
                    'apply_admin_name' => $this->logic_admin->getadmin_name(),
                    'apply_cash_amount' =>  $input['amount'],
                    'apply_cash_reason' => $input['reason'],
                    'apply_add_time' => $now,
                );
                $this->sys_model_orders->addOrderApply($data);
                $this->session->data['success'] = '提交审批成功！';
            }
            $filter = $this->request->get(array('filter_type', 'order_sn', 'lock_sn', 'bicycle_sn', 'user_name', 'cooperator_name', 'region_name', 'order_state', 'add_time', 'start_time', 'end_time', 'settlement_time'));
            $this->load->controller('common/base/redirect', $this->url->link('user/order', $filter, true));
        }

        $order_id = $this->request->get('order_id');
        $condition = array(
            'order_id' => $order_id
        );
        $info = $this->sys_model_orders->getOrdersInfo($condition);
        if (!empty($info)) {
            $info['order_status'] = $info['order_state'];
            $model = array(
                'order_state' => get_order_state()
            );
            foreach ($model as $k => $v) {
                $info[$k] = isset($v[$info[$k]]) ? $v[$info[$k]] : '';
            }
            $info['add_time'] = (isset($info['add_time']) && !empty($info['add_time'])) ? date('Y-m-d H:i:s', $info['add_time']) : '';
            $info['start_time'] = (isset($info['start_time']) && !empty($info['start_time'])) ? date('Y-m-d H:i:s', $info['start_time']) : '';
            $info['end_time'] = (isset($info['end_time']) && !empty($info['end_time'])) ? date('Y-m-d H:i:s', $info['end_time']) : '';
            $info['settlement_time'] = (isset($info['settlement_time']) && !empty($info['settlement_time'])) ? date('Y-m-d H:i:s', $info['settlement_time']) : '';
            $info['pdr_payment_time'] = (isset($info['pdr_payment_time']) && !empty($info['pdr_payment_time'])) ? date('Y-m-d H:i:s', $info['pdr_payment_time']) : '';
        }
        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('user/order'));
        $this->assign('action', $this->cur_url . '&order_id=' . $order_id);
        $this->assign('error', $this->error);
        $this->response->setOutput($this->load->view('user/order_edit_amount', $this->output));
    }

    /**
     * 统计图表
     */
    public function chart() {
        $this->load->library('sys_model/cooperator', true);
        $filter = $this->request->get(array('city_id', 'add_time','user_type','time_type','region_id'));
        $refundWhere = '`apply_state`=\'1\'';
        $orderWhere = '`order_state`=\'2\'';
        
        if (is_numeric($filter['city_id'])) {
            $refundWhere .= " AND rich_user.city_id = '{$filter['city_id']}'";
            $orderWhere .= " AND rich_user.city_id = '{$filter['city_id']}'";
        }
        if (is_numeric($filter['region_id'])) {
            $refundWhere .= " AND rich_user.region_id = '{$filter['region_id']}'";
            $orderWhere .= " AND rich_user.region_id = '{$filter['region_id']}'";
        }
        if (is_numeric($filter['user_type'])) {
            $refundWhere .= " AND user_type = '{$filter['user_type']}'";
            $orderWhere .= " AND user_type = '{$filter['user_type']}'";
        }

        $pdr_add_time = explode(' 至 ', $filter['add_time']);
        if($filter['time_type']==1){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_add_time[0].'-01-01');
                $lastday = bcadd(86399,strtotime($pdr_add_time[1].'-12-31'));
                $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            } else {
                $firstday = strtotime(date('Y-01-01'));
                $lastday = bcadd(86399,strtotime(date('Y-12-31')));
                $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_add_time[0]);
                $lastday = bcadd(86399, strtotime($pdr_add_time[1].'+1 month -1 day'));
                $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            } else {
                $firstday = strtotime(date('Y-m'));
                $lastday = bcadd(86399, strtotime(date('Y-m-t')));
                $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_add_time[0]);
                $lastday = bcadd(86399, strtotime($pdr_add_time[1]));
                $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }else{
                $firstday = strtotime(date('Y-m-d'));
                $lastday = bcadd(86399, strtotime(date('Y-m-d')));
                $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }
        }else{
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_add_time[0]);
                $lastday = bcadd(86399, strtotime($pdr_add_time[1]));
                $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }else{
                $firstday = strtotime(date('Y-m-01'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-d')));
                $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }
        }        

       

        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }

        if($filter['time_type']==1){
            //消费金额
            $filed = "sum(pay_amount) as total, FROM_UNIXTIME(settlement_time, '%Y-%m-%d') as order_date";
            //退回总计
            $filed1= "sum(apply_cash_amount) as total, FROM_UNIXTIME(apply_audit_time, '%Y-%m-%d') as audit_time";
            //订单数
            $filed2="count(order_id) as total, FROM_UNIXTIME(settlement_time, '%Y-%m-%d') as order_date";
        }else if($filter['time_type']==2){
            $filed = "sum(pay_amount) as total, FROM_UNIXTIME(settlement_time, '%Y-%m') as order_date";
            $filed1= "sum(apply_cash_amount) as total, FROM_UNIXTIME(apply_audit_time, '%Y-%m') as audit_time";
            $filed2="count(order_id) as total, FROM_UNIXTIME(settlement_time, '%Y-%m') as order_date";
        }else{
            $filed = "sum(pay_amount) as total, FROM_UNIXTIME(settlement_time, '%Y') as order_date";
            $filed1= "sum(apply_cash_amount) as total, FROM_UNIXTIME(apply_audit_time, '%Y') as audit_time";
            $filed2="count(order_id) as total, FROM_UNIXTIME(settlement_time, '%Y') as order_date";
        
        }

        // 初始化订单统计数据
        $dailyAmount = $dailyOrders = array();
        if ($filter['time_type']==1) {
            while ($firstday <= $lastday) {
            $tempDay = date('Y', $firstday);
            $dailyAmount[$tempDay] = $dailyOrders[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
        }else if($filter['time_type']==2){
            while ($firstday <= $lastday) {
            $tempDay = date('Y-m', $firstday);
            $dailyAmount[$tempDay] = $dailyOrders[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
        }else{
            while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $dailyAmount[$tempDay] = $dailyOrders[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
        }
        
        
        $this->load->library('sys_model/data_sum', true);
        // 每天消费金额
        $join = 'LEFT JOIN rich_user on rich_user.user_id=rich_orders.user_id LEFT JOIN rich_city on rich_city.city_id=rich_user.city_id LEFT JOIN rich_region on rich_region.region_id=rich_user.region_id';
        $amountResult = $this->sys_model_data_sum->getOrderAmountForDays($orderWhere,$join,$filed);
        $amountResult = array_column($amountResult, 'total', 'order_date');

        // 每天退回消费金额
        $join = 'LEFT JOIN rich_orders ON rich_orders.order_sn=rich_orders_modify_apply.order_sn LEFT JOIN rich_user on rich_user.user_id=rich_orders.user_id LEFT JOIN rich_city on rich_city.city_id=rich_user.city_id LEFT JOIN rich_region on rich_region.region_id=rich_user.region_id';
        $refundResult = $this->sys_model_data_sum->getRefundOrderAmountForDays($refundWhere, $join,$filed1);
        $refundResult = array_column($refundResult, 'total', 'audit_time');

        // 每天订单数
        $join = 'LEFT JOIN rich_user on rich_user.user_id=rich_orders.user_id LEFT JOIN rich_city on rich_city.city_id=rich_user.city_id LEFT JOIN rich_region on rich_region.region_id=rich_user.region_id';
        $numberResult = $this->sys_model_data_sum->getOrderCountForDays($orderWhere,$join,$filed2);
        $numberResult = array_column($numberResult, 'total', 'order_date');
        $orderData = array();
        $orderAmountTotal = $refundAmountTotal = $ordersTotal = 0;
        if (is_array($dailyAmount) && !empty($dailyAmount)) {
            foreach ($dailyAmount as $key => $val) {
                $amount = isset($amountResult[$key]) ? $amountResult[$key] : $val;
                $refund = isset($refundResult[$key]) ? $refundResult[$key] : $val;
                $number = isset($numberResult[$key]) ? $numberResult[$key] : $val;
                $orderData[] = array(
                    'date' => $key,
                    'amount' => $amount,
                    'refund' => $refund,
                    'number' => $number,
                );
                $orderAmountTotal += $amount;
                $refundAmountTotal += $refund;
                $ordersTotal += $number;
            }
        }

        $orderData = json_encode($orderData);
        $orderAmountTotal = sprintf('%0.2f', $orderAmountTotal);
        $refundAmountTotal = sprintf('%0.2f', $refundAmountTotal);

        $condition = array();
        $order = '';
        $limit = '';
        $field = 'cooperator.cooperator_id, cooperator.cooperator_name';
        $cooperators = $this->sys_model_cooperator->getCooperatorList($condition, $order, $limit, $field);
        // $user_types = get_user_type();

        $user_types = array(
            '0' => 'App用户',
            '1' => '刷卡用户',
        );

        $this->assign('time_type',get_time_type());
        $this->assign('user_types', $user_types);
        $this->assign('filter_regions', $filter_regions);
        $this->assign('filter', $filter);
        $this->assign('user_types', $user_types);
        $this->assign('orderData', $orderData);
        $this->assign('orderAmountTotal', $orderAmountTotal);
        $this->assign('refundAmountTotal', $refundAmountTotal);
        $this->assign('cooperators', $cooperators);
        $this->assign('ordersTotal', $ordersTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/order'));
        $this->assign('city_ranking_action', $this->url->link('user/order/city_ranking'));
        $this->assign('cooperation_chart', $this->url->link('user/order/cooperation_chart'));
        $this->assign('order_free_chart', $this->url->link('user/order/free_chart'));
        $this->assign('order_free_list', $this->url->link('user/order/free_list'));

        $this->response->setOutput($this->load->view('user/order_chart', $this->output));
    }

    /**
     * 统计图表
     */
    public function cooperation_chart() {
        $this->load->library('sys_model/cooperator', true);
        $filter = $this->request->get(array('cooperator_id', 'add_time'));
        $where = '`order_state`=\'2\'';
        if (!empty($filter['cooperator_id'])) {
            $where .= " AND cooperator_id = '{$filter['cooperator_id']}'";
        }
        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdr_add_time[0]);
            $lastday  = bcadd(86399, strtotime($pdr_add_time[1]));
            $where .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
            $where .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
        }
        // 初始化订单统计数据
        $dailyAmount = $dailyOrders = array();
        while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $dailyAmount[$tempDay] = $dailyOrders[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
        // 每天消费金额
        $amountResult = $this->sys_model_data_sum->getOrderAmountForDays($where);
        $amountResult = array_column($amountResult, 'total', 'order_date');

        // 每天订单数
        $numberResult = $this->sys_model_data_sum->getOrderCountForDays($where);
        $numberResult = array_column($numberResult, 'total', 'order_date');

        $orderData = array();
        $orderAmountTotal = $ordersTotal = 0;
        if (is_array($dailyAmount) && !empty($dailyAmount)) {
            foreach ($dailyAmount as $key => $val) {
                $amount = isset($amountResult[$key]) ? $amountResult[$key] : $val;
                $number = isset($numberResult[$key]) ? $numberResult[$key] : $val;
                $orderData[] = array(
                    'date' => $key,
                    'amount' => $amount,
                    'number' => $number,
                );
                $orderAmountTotal += $amount;
                $ordersTotal += $number;
            }
        }

        $orderData = json_encode($orderData);
        $orderAmountTotal = sprintf('%0.2f', $orderAmountTotal);

        $condition = array();
        $order = '';
        $limit = '';
        $field = 'cooperator.cooperator_id, cooperator.cooperator_name';
        $cooperators = $this->sys_model_cooperator->getCooperatorList($condition, $order, $limit, $field);

        $this->assign('filter', $filter);
        $this->assign('orderData', $orderData);
        $this->assign('orderAmountTotal', $orderAmountTotal);
        $this->assign('cooperators', $cooperators);
        $this->assign('ordersTotal', $ordersTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/order'));
        $this->assign('city_ranking_action', $this->url->link('user/order/city_ranking'));
        $this->assign('chart_url', $this->url->link('user/order/chart'));

        $this->response->setOutput($this->load->view('user/cooperation_order_chart', $this->output));
    }

    /**
     * 城市排行
     */
    public function city_ranking() {

        $this->load->library('sys_model/data_sum', true);
        $filter = $this->request->get(array('cooperator_id', 'add_time'));
        $where = '';
        $time_horizon = $filter['add_time'] ? $filter['add_time'] : '全部订单';
        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);
            $firstday = strtotime($pdr_add_time[0]);
            $lastday  = bcadd(86399, strtotime($pdr_add_time[1]));
            $where .= " settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
        }

        /**
         * 500错误注释
         */
        // 总订单数排行
        // $all_orders_ranking = $this->sys_model_data_sum->city_all_orders_ranking($where);
        
        // 日均订单数排行
        // $daily_orders_ranking = $this->sys_model_data_sum->daily_orders_ranking($where);

        // 单车使用率排行
        // $daily_usage_bicycle_ranking = $this->sys_model_data_sum->daily_usage_bicycle_ranking($where);
        $user_types = array(
            'user_app' => 'App用户',
            'user_card' => '刷卡用户',
        );

        $this->assign('filter', $filter);
        $this->assign('user_types', $user_types);
        $this->assign('time_horizon', $time_horizon);
        $this->assign('all_orders_ranking', $all_orders_ranking);
        $this->assign('daily_orders_ranking', $daily_orders_ranking);
        $this->assign('daily_usage_bicycle_ranking', $daily_usage_bicycle_ranking);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/order'));
        $this->assign('chart_action', $this->url->link('user/order/chart'));
        $this->assign('cooperation_chart', $this->url->link('user/order/cooperation_chart'));
        $this->assign('order_free_chart', $this->url->link('user/order/free_chart'));
        $this->assign('order_free_list', $this->url->link('user/order/free_list'));

        $this->response->setOutput($this->load->view('user/order_city_ranking', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
         $filter = $this->request->get(array('filter_type', 'order_sn', 'lock_sn', 'bicycle_sn', 'user_name',  'region_name', 'order_state', 'add_time', 'start_time', 'end_time', 'settlement_time', 'amount' ,'ride_time','user_type','time_type','city_id','region_id'));

        $condition = array();
        if (!empty($filter['order_sn'])) {
            $condition['order_sn'] = array('like', "%{$filter['order_sn']}%");
        }
        if (!empty($filter['lock_sn'])) {
            $condition['lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['user_name'])) {
            $condition['user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (is_numeric($filter['city_id'])) {
            $condition['user.city_id'] = (int)$filter['city_id'];
        }
        if (is_numeric($filter['region_id'])) {
            $condition['user.region_id'] = (int)$filter['region_id'];
        }
        if (is_numeric($filter['order_state'])) {
            $condition['order_state'] = (int)$filter['order_state'];
        }
        if (is_numeric($filter['user_type'])) {
            $condition['user_type'] = (int)$filter['user_type'];
        }
        $pdr_add_time = explode(' 至 ', $filter['add_time']);
        if($filter['time_type']==1){
            if (!empty($filter['add_time'])) {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_add_time[1].'-12-31'))))
                );
            } else {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['add_time'])) {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['add_time'])) {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
                );
            }else{
                $condition['orders.add_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['add_time'])) {
                $condition['orders.add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
                );
            }
        }
        ////////-------------2
        $pdr_start_time = explode(' 至 ', $filter['start_time']);
        if($filter['time_type']==1){
            if (!empty($filter['start_time'])) {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime($pdr_start_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_start_time[1].'-12-31'))))
                );
            } else {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['start_time'])) {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime($pdr_start_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_start_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['start_time'])) {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime($pdr_start_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_start_time[1])))
                );
            }else{
                $condition['orders.start_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['start_time'])) {
                $condition['orders.start_time'] = array(
                    array('egt', strtotime($pdr_start_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_start_time[1])))
                );
            }
        }
        ///////////-------3
        $pdr_end_time = explode(' 至 ', $filter['end_time']);
        if($filter['time_type']==1){
            if (!empty($filter['end_time'])) {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime($pdr_end_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_end_time[1].'-12-31'))))
                );
            } else {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['end_time'])) {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime($pdr_end_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_end_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['end_time'])) {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime($pdr_end_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_end_time[1])))
                );
            }else{
                $condition['orders.end_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['end_time'])) {
                $condition['orders.end_time'] = array(
                    array('egt', strtotime($pdr_end_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_end_time[1])))
                );
            }
        }
        //////////////------4
        $pdr_settlement_time = explode(' 至 ', $filter['settlement_time']);
        if($filter['time_type']==1){
            if (!empty($filter['settlement_time'])) {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime($pdr_settlement_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_settlement_time[1].'-12-31'))))
                );
            } else {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['settlement_time'])) {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime($pdr_settlement_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_settlement_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['settlement_time'])) {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime($pdr_settlement_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_settlement_time[1])))
                );
            }else{
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['settlement_time'])) {
                $condition['orders.settlement_time'] = array(
                    array('egt', strtotime($pdr_settlement_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_settlement_time[1])))
                );
            }
        }
        /////////////
        if (is_numeric($filter['amount'])) {
            $condition['pay_amount'] = array('egt',(int)$filter['amount']);
        }

        if (is_numeric($filter['ride_time'])) {
            $condition['orders.start_time'] = array('elt',time() - $filter['ride_time']*60);
            $condition['orders.order_state'] = 1;
        }

        $order = 'orders.add_time DESC';
        $limit = "";
         $field = 'orders.*,city.city_name,region.region_name,user.user_type';
        $join = array(
            'user' => 'user.user_id=orders.user_id',
            'city' => 'city.city_id=user.city_id',
            'region' => 'region.region_id=user.region_id'
        );
        $result = $this->sys_model_orders->getOrdersList($condition, $order, $limit, $field, $join);
        $user_type=array(
            '0' =>'App用户',
            '1' =>'刷卡用户'
        );
        if (is_array($result) && !empty($result)) {
            $order_state = get_order_state();
            foreach ($result as &$v) {
                // exit(var_dump($v));
                $v['order_state'] = $order_state[$v['order_state']];
                $v['start_time'] = !empty($v['start_time']) ? date('Y-m-d H:i:s', $v['start_time']) : '';
                $v['end_time'] = !empty($v['end_time']) ? date('Y-m-d H:i:s', $v['end_time']) : '';
                $v['add_time'] = !empty($v['add_time']) ? date('Y-m-d H:i:s', $v['add_time']) : '';
                $v['settlement_time'] = !empty($v['settlement_time']) ? date('Y-m-d H:i:s', $v['settlement_time']) : '';
                $v['user_type'] = $user_type[(int)$v['user_type']];
               
            }
        }

        $data = array(
            'title' => '消费记录列表',
            'header' => array(
                'region_name' => '区域',
                'city_name' => '城市',
                'user_type' => '用户类型',
                'order_sn' => '订单编号',
                'lock_sn' => '锁编号',
                'bicycle_sn' => '单车编号',
                'user_name' => '用户名',
                'order_state' => '状态',
                'pay_amount' => '实付金额',
                'refund_amount' => '已退金额',
                'start_time' => '开始时间',
                'end_time' => '结束时间',
                'add_time' => '下单时间',
                'settlement_time' => '结算时间',
            ),
            'list' => $result
        );
        unset($result);
        $this->load->controller('common/base/exportExcel', $data);
    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('bicycle_sn', 'type', 'lock_sn'));
        $bicycle_id = $this->request->get('bicycle_id');
        if (isset($this->request->get['bicycle_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'bicycle_id' => $this->request->get['bicycle_id']
            );
            $info = $this->sys_model_bicycle->getBicycleInfo($condition);
        }

        $this->assign('data', $info);
        $this->assign('types', get_bicycle_type());
        $this->assign('action', $this->cur_url . '&bicycle_id=' . $bicycle_id);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/order_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('bicycle_sn', 'type', 'lock_sn'));

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
     * 验证表单数据
     * @return bool
     */
    private function validateAmount(){
        $input = $this->request->post(array('order_sn', 'amount', 'reason'));

        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        $info = $this->sys_model_orders->getOrdersInfo(array('order_sn'=>$input['order_sn']));

        if(!is_numeric($input['amount']) || $input['amount'] > $info['pay_amount'] || $input['amount']<0){
            $this->error['amount'] = '价格错误';
        }

        // 退回的金额不能超过实际支付金额
        if ($input['amount'] > ($info['pay_amount'] - $info['refund_amount'])) {
            $this->error['amount'] = '退还金额不能超过实际支付金额（扣除已退金额）';
        }

        if($this->error){
            $this->error['warning'] = '提交失败';
        }
        return !$this->error;
    }

    /**
     * 验证删除条件
     */
    private function validateDelete() {
        return !$this->error;
    }

    /**
     * [free_chart 免费车图表]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-21T15:31:33+0800
     */
	public function free_chart(){
        $filter = $this->request->get(array('add_time'));
        $where = ' WHERE `is_limit_free`=\'1\'';
        $where .= ' AND `order_state`=\'2\'';

        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);
            $firstday = strtotime($pdr_add_time[0]);
            $lastday  = bcadd(86399, strtotime($pdr_add_time[1]));
            $where .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
            $where .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
        }
        //
        $order 	= '';
        $limit 	= '';
        $field 	= "COALESCE(cooperator.cooperator_id,0) AS cooperator_id,COALESCE(cooperator.cooperator_name,'平台') AS cooperator_name,count(1) as num";
        $join = array(
            'cooperator' => 'cooperator.cooperator_id=orders.cooperator_id',
        );
        $group 	= 'cooperator.cooperator_id';
        //$free_list 	= $this->sys_model_orders->getOrdersList($where, $order, $limit, $field, $join, $group);
        $free_list    = $this->db->getRows("SELECT ".$field." FROM `rich_orders` orders LEFT JOIN `rich_cooperator` cooperator ON cooperator.cooperator_id=orders.cooperator_id ".$where." GROUP BY cooperator.cooperator_id");

        $data_chart 	= array();
        $ordersTotal 	= 0;
        // 初始化订单统计数据
        foreach ($free_list as $k => $v) {
        	$data_chart[] 	= array('label'=>$v['cooperator_name'],'value'=>$v['num']);
        	$ordersTotal 	+= $v['num'];
        }
        $data_chart 	= json_encode($data_chart);
        $this->assign('filter', $filter);
        $this->assign('data_chart', $data_chart);
        $this->assign('ordersTotal', $ordersTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/order'));
        $this->assign('chart_action', $this->url->link('user/order/chart'));
        $this->assign('city_ranking_action', $this->url->link('user/order/city_ranking'));
        $this->assign('cooperation_chart', $this->url->link('user/order/cooperation_chart'));
        $this->assign('order_free_list', $this->url->link('user/order/free_list'));

		$this->response->setOutput($this->load->view('user/order_free_chart', $this->output));
	}

	/**
	 * [free_list 免费车列表]
	 * @return   [type]                   [description]
	 * @Author   vincent
	 * @DateTime 2017-07-21T18:38:06+0800
	 */
	public function free_list(){
		$filter = $this->request->get(array('filter_type', 'order_sn', 'lock_sn', 'bicycle_sn', 'user_name', 'cooperator_name', 'region_name', 'order_state', 'add_time', 'start_time', 'end_time', 'ride_time'));

        $condition = array('is_limit_free'=>1);
        if (!empty($filter['order_sn'])) {
            $condition['order_sn'] = array('like', "%{$filter['order_sn']}%");
        }
        if (!empty($filter['lock_sn'])) {
            $condition['lock_sn'] = array('like', "%{$filter['lock_sn']}%");
        }
        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['user_name'])) {
            $condition['user_name'] = array('like', "%{$filter['user_name']}%");
        }
        if (!empty($filter['cooperator_name'])) {
            $condition['cooperator_name'] = array('like', "%{$filter['cooperator_name']}%");
        }
        if (!empty($filter['region_name'])) {
            $condition['region_name'] = array('like', "%{$filter['region_name']}%");
        }
        if (is_numeric($filter['order_state'])) {
            $condition['order_state'] = (int)$filter['order_state'];
        }
        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);
            $condition['orders.add_time'] = array(
                array('egt', strtotime($pdr_add_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
            );
        }
        if (!empty($filter['start_time'])) {
            $pdr_start_time = explode(' 至 ', $filter['start_time']);
            $condition['orders.start_time'] = array(
                array('egt', strtotime($pdr_start_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_start_time[1])))
            );
        }
        if (!empty($filter['end_time'])) {
            $pdr_end_time = explode(' 至 ', $filter['end_time']);
            $condition['orders.end_time'] = array(
                array('egt', strtotime($pdr_end_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_end_time[1])))
            );
        }

        if (is_numeric($filter['ride_time'])) {
            $condition['orders.start_time'] = array('elt',time() - $filter['ride_time']*60);
            $condition['orders.order_state'] = 1;
        }

        $filter_types = array(
            //'order_sn' => '订单sn',
            //'lock_sn' => '锁sn',
            'bicycle_sn' => '单车编号',
            'user_name' => '手机号',
            'cooperator_name' => '合伙人',
            'region_name' => '区域',
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

        $order = 'orders.add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'orders.*, cooperator.cooperator_name';
        $join = array(
            'cooperator' => 'cooperator.cooperator_id=orders.cooperator_id',
        );

        $result = $this->sys_model_orders->getOrdersList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_orders->getTotalOrders($condition, $join);

        $order_state = get_order_state();

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['order_state'] = isset($order_state[$item['order_state']]) ? $order_state[$item['order_state']] : '';
                $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['start_time'] = !empty($item['start_time']) ? date('Y-m-d H:i:s', $item['start_time']) : '';
                $item['end_time'] = !empty($item['end_time']) ? date('Y-m-d H:i:s', $item['end_time']) : '';
                $item['settlement_time'] = !empty($item['settlement_time']) ? date('Y-m-d H:i:s', $item['settlement_time']) : '';

                $item['edit_action'] = $this->url->link('user/order/edit', 'order_id='.$item['order_id']);
                $item['delete_action'] = $this->url->link('user/order/delete', 'order_id='.$item['order_id']);
                $item['info_action'] = $this->url->link('user/order/info', 'order_id='.$item['order_id']);
                $item['edit_order_amount_action'] = $this->url->link('user/order/editOrderAmount', 'order_id='.$item['order_id']);
            }
        }

        $this->setDataColumn('单车编号');
        $this->setDataColumn('手机号');
        $this->setDataColumn('合伙人');
        $this->setDataColumn('区域');
        $this->setDataColumn('状态');
        $this->setDataColumn('实付金额');
        $this->setDataColumn('已退金额');
        $this->setDataColumn('开始时间');
        $this->setDataColumn('结束时间');
        $this->setDataColumn('下单时间');
        $this->setDataColumn('结算时间');

        $this->assign('data_columns', $this->data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('order_state', $order_state);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/order/add'));
        $this->assign('chart_action', $this->url->link('user/order/chart'));
        $this->assign('index_action', $this->url->link('user/order'));
        $this->assign('city_ranking_action', $this->url->link('user/order/city_ranking'));
        $this->assign('order_free_chart', $this->url->link('user/order/free_chart'));
        $this->assign('order_free_list', $this->url->link('user/order/free_list'));

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

        $this->assign('export_action', $this->url->link('user/order/export'));

        $this->response->setOutput($this->load->view('user/order_free_list', $this->output));
	}
}
