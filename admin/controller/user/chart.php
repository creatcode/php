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
class ControllerUserChart extends Controller {
    private $cur_url = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载 Model
        $this->load->library('sys_model/data_sum', true);
        $this->load->library('sys_model/comment', true);
        $this->assign('lang',$this->language->all());

    }

    /**
     * 充值记录列表
     */
    public function index() {
        $this->load->library('sys_model/data_sum', true);
        $filter = $this->request->get(array('add_time','city_id','time_type','region_id'));
        $where = 'find_in_set(`pdr_payment_state`, \'1,-1,-2\')';
        $pdr_payment_time = explode(' 至 ', $filter['add_time']);
        if($filter['time_type']==1){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_payment_time[0].'-01-01');
                $lastday  = bcadd(86399,strtotime($pdr_payment_time[1].'-12-31'));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            } else {
                $firstday = strtotime(date('Y-01-01'));
                $lastday  = bcadd(86399,strtotime(date('Y-12-31')));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_payment_time[0]);
                $lastday  = bcadd(86399,strtotime($pdr_payment_time[1].'+1 month -1 day'));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            }else{
                $firstday = strtotime(date('Y-m'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-t')));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            }
        }else if($filter['time_type']==3){
            if(!empty($fiter['add_time'])){
                $firstday = strtotime($pdr_payment_time[0]);
                $lastday  = bcadd(86399,strtotime($pdr_payment_time[1]));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-m-d'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-d')));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            }
        }else{
            if(!empty($fiter['add_time'])){
                $firstday = strtotime($pdr_payment_time[0]);
                $lastday  = bcadd(86399,strtotime($pdr_payment_time[1]));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-m-01'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-d')));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            }
        }
       


        if (is_numeric($filter['region_id'])) {
            $where .= " AND u.region_id = ".$filter['region_id'];
        }
        if (is_numeric($filter['city_id'])) {
            $where .= " AND u.city_id = ".$filter['city_id'];
        }

        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }

        if($filter['time_type']==1){
            $field = "sum(r.pdr_amount) as total, FROM_UNIXTIME(pdr_payment_time, '%Y') as payment_date,pdr_type";
        }else if($filter['time_type']==2){
            $field = "sum(r.pdr_amount) as total, FROM_UNIXTIME(pdr_payment_time, '%Y-%m') as payment_date,pdr_type";
        }else{
            $field = "sum(r.pdr_amount) as total, FROM_UNIXTIME(pdr_payment_time, '%Y-%m-%d') as payment_date,pdr_type";
        
        }

        // 初始化订单统计数据
        $balanceDailyAmount = $depositDailyAmount = array();
        if ($filter['time_type']==1) {
            while ($firstday <= $lastday) {
            $tempDay = date('Y', $firstday);
            $balanceDailyAmount[$tempDay] = $depositDailyAmount[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
        }else if($filter['time_type']==2){
            while ($firstday <= $lastday) {
            $tempDay = date('Y-m', $firstday);
            $balanceDailyAmount[$tempDay] = $depositDailyAmount[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
    }else{
         while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $balanceDailyAmount[$tempDay] = $depositDailyAmount[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
    }
       

        // 插入数据
        $result = $this->sys_model_data_sum->getDepositSumForDaysCity($where,$field);
        if (is_array($result) && !empty($result)) {
            foreach ($result as $item) {
                if ($item['pdr_type'] == 0) {
                    // 余额充值
                    $balanceDailyAmount[$item['payment_date']] += $item['total'];
                } else if ($item['pdr_type'] == 1) {
                    // 押金充值
                    $depositDailyAmount[$item['payment_date']] += $item['total'];
                }
            }
        }

        $balanceOrderData = $depositOrderData = array();
        $balanceOrderTotal = $depositOrderTotal = 0;
        // 余额充值统计
        if (is_array($balanceDailyAmount) && !empty($balanceDailyAmount)) {
            foreach ($balanceDailyAmount as $key => $val) {
                $balanceOrderData[] = array(
                    'date' => $key,
                    'amount' => $val
                );
                $balanceOrderTotal += $val;
            }
        }
        $balanceOrderData = json_encode($balanceOrderData);
        $balanceOrderTotal = sprintf('%0.2f', $balanceOrderTotal);

        // 押金充值统计
        if (is_array($depositDailyAmount) && !empty($depositDailyAmount)) {
            foreach ($depositDailyAmount as $key => $val) {
                $depositOrderData[] = array(
                    'date' => $key,
                    'amount' => $val
                );
                $depositOrderTotal += $val;
            }
        }
        $depositOrderData = json_encode($depositOrderData);
        $depositOrderTotal = sprintf('%0.2f', $depositOrderTotal);

        //提现
        $cashApply = $this->cashApply($filter['add_time']);
        //消费
        $orders = $this->order($filter['add_time']);
        //注册金统计
        $reginster = $this->reginster($filter['add_time']);

        $this->assign('time_type',get_time_type());
        $this->assign('filter', $filter);
        $this->assign('filter_regions', $filter_regions);
        $this->assign('balanceOrderData', $balanceOrderData);
        $this->assign('balanceOrderTotal', $balanceOrderTotal);
        $this->assign('depositOrderData', $depositOrderData);
        $this->assign('depositOrderTotal', $depositOrderTotal);
        $this->assign('cashApplyData', $cashApply['cashApplyData']);
        $this->assign('cashApplyDepositTotal', $cashApply['cashApplyDepositTotal']);
        $this->assign('cashApplyBalanceTotal', $cashApply['cashApplyBalanceTotal']);
        $this->assign('orderData', $orders['orderData']);
        $this->assign('ordersTotal', $orders['ordersTotal']);
        $this->assign('orderAmountTotal', $orders['orderAmountTotal']);
        $this->assign('refundAmountTotal', $orders['refundAmountTotal']);
        $this->assign('reginAmountTotal', $reginster['reginAmountTotal']);
        $this->assign('reginData', $reginster['reginData']);

        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/chart'));
        $this->assign('cooperation_chart_url', $this->url->link('user/chart/cooperation'));

        $this->response->setOutput($this->load->view('user/chart', $this->output));
    }

    //根据合伙人统计
    public function cooperation() {
        $this->load->library('sys_model/data_sum', true);
        $filter = $this->request->get(array('add_time', 'cooperator_id'));
        $where = 'find_in_set(`pdr_payment_state`, \'1,-1,-2\')';
        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdr_add_time[0]);
            $lastday  = bcadd(86399, strtotime($pdr_add_time[1]));
            $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
            $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
        }

        #全部合伙人
        $this->load->library('sys_model/cooperator');
        $cooperatorList = $this->sys_model_cooperator->getCooperatorList();
        if(empty($cooperatorList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }
        if(is_numeric($filter['cooperator_id']) ){
            $w['cooperator_id'] = $filter['cooperator_id'];
        }else{
            $w['cooperator_id'] = $cooperatorList[0]['cooperator_id'];
        }

        // 初始化订单统计数据
        $balanceDailyAmount = $depositDailyAmount = array();
        while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $balanceDailyAmount[$tempDay] = $depositDailyAmount[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }

        // 插入数据
        $result = $this->sys_model_data_sum->getDepositSumForDaysCooperation($where,$w['cooperator_id']);
        if (is_array($result) && !empty($result)) {
            foreach ($result as $item) {
                if ($item['pdr_type'] == 0) {
                    // 余额充值
                    $balanceDailyAmount[$item['payment_date']] += $item['total'];
                } else if ($item['pdr_type'] == 1) {
                    // 押金充值
                    $depositDailyAmount[$item['payment_date']] += $item['total'];
                }
            }
        }

        $balanceOrderData = $depositOrderData = array();
        $balanceOrderTotal = $depositOrderTotal = 0;
        // 余额充值统计
        if (is_array($balanceDailyAmount) && !empty($balanceDailyAmount)) {
            foreach ($balanceDailyAmount as $key => $val) {
                $balanceOrderData[] = array(
                    'date' => $key,
                    'amount' => $val
                );
                $balanceOrderTotal += $val;
            }
        }
        $balanceOrderData = json_encode($balanceOrderData);
        $balanceOrderTotal = sprintf('%0.2f', $balanceOrderTotal);
        // 押金充值统计
        if (is_array($depositDailyAmount) && !empty($depositDailyAmount)) {
            foreach ($depositDailyAmount as $key => $val) {
                $depositOrderData[] = array(
                    'date' => $key,
                    'amount' => $val
                );
                $depositOrderTotal += $val;
            }
        }
        $depositOrderData = json_encode($depositOrderData);
        $depositOrderTotal = sprintf('%0.2f', $depositOrderTotal);

        //提现
        $cashApply = $this->cashApply($filter['add_time'],$w['cooperator_id']);
        //消费
        $this->assign('cooperator_id',$w['cooperator_id']);
        $orders = $this->order($filter['add_time'],$w['cooperator_id']);
        $this->assign('cooperList',$cooperatorList);
        $this->assign('filter', $filter);
        $this->assign('balanceOrderData', $balanceOrderData);
        $this->assign('balanceOrderTotal', $balanceOrderTotal);
        $this->assign('depositOrderData', $depositOrderData);
        $this->assign('depositOrderTotal', $depositOrderTotal);
        $this->assign('cashApplyData', $cashApply['cashApplyData']);
        $this->assign('cashApplyDepositTotal', $cashApply['cashApplyDepositTotal']);
        $this->assign('cashApplyBalanceTotal', $cashApply['cashApplyBalanceTotal']);
        $this->assign('orderData', $orders['orderData']);
        $this->assign('ordersTotal', $orders['ordersTotal']);
        $this->assign('orderAmountTotal', $orders['orderAmountTotal']);
        $this->assign('refundAmountTotal', $orders['refundAmountTotal']);
        $this->assign('reginAmountTotal', $total['reginAmountTotal']);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/chart'));
        $this->assign('cooperation_chart_url', $this->url->link('user/chart/cooperation'));
        $this->assign('chart_url', $this->url->link('user/chart'));
        $this->assign('recharge_url', $this->url->link('user/recharge'));

        $this->response->setOutput($this->load->view('user/cooperation_chart', $this->output));
    }


    /**
     * 提现统计图表
     * @param $add_time
     * @return array
     */
    public function cashApply() {
        $filter = $this->request->get(array('add_time','city_id','time_type','region_id'));
        // exit(var_dump($filter));
        $where = 'pdc_payment_state=\'1\'';
        $pdc_payment_time = explode(' 至 ', $filter['add_time']);
        if($filter['time_type']==1){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdc_payment_time[0].'-01-01');
                $lastday  = bcadd(86399,strtotime($pdc_payment_time[1].'-12-31'));
                $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
            } else {
                $firstday = strtotime(date('Y-01-01'));
                $lastday  = bcadd(86399,strtotime(date('Y-12-31')));
                $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdc_payment_time[0]);
                $lastday  = bcadd(86399,strtotime($pdc_payment_time[1].'+1 month -1 day'));
                $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
            }else{
                $firstday = strtotime(date('Y-m'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-t')));
                $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
            }
        }else if($filter['time_type']==3){
            if(!empty($fiter['add_time'])){
                $firstday = strtotime($pdc_payment_time[0]);
                $lastday  = bcadd(86399,strtotime($pdc_payment_time[1]));
                $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-m-d'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-d')));
                $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
            }
        }else{
            if(!empty($fiter['add_time'])){
                $firstday = strtotime($pdc_payment_time[0]);
                $lastday  = bcadd(86399,strtotime($pdc_payment_time[1]));
                $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-m-01'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-d')));
                $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
            }
        }
        
        if (is_numeric($filter['region_id'])) {
            $where .= " AND u.region_id = ".$filter['region_id'];
        }
        if (is_numeric($filter['city_id'])) {
            $where .= " AND u.city_id = ".$filter['city_id'];
        }

        if($filter['time_type']==1){
            $filed = "sum(pdc_amount) as total,FROM_UNIXTIME(pdc_payment_time, '%Y') as payment_date";
        }else if($filter['time_type']==2){
            $filed = "sum(pdc_amount) as total,FROM_UNIXTIME(pdc_payment_time, '%Y-%m') as payment_date";
        }else{
            $filed = "sum(pdc_amount) as total,FROM_UNIXTIME(pdc_payment_time, '%Y-%m-%d') as payment_date";
        
        }



        // 初始化订单统计数据
        $orderData = array();
        if ($filter['time_type']==1) {
            while ($firstday <= $lastday) {
            $tempDay = date('Y', $firstday);
            $orderData[$tempDay] = array();
            $firstday = strtotime('+1 day', $firstday);
        }
        }else if($filter['time_type']==2){
            while ($firstday <= $lastday) {
            $tempDay = date('Y-m', $firstday);
            $orderData[$tempDay] = array();
            $firstday = strtotime('+1 day', $firstday);
        }
        }else{
           while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $orderData[$tempDay] = array();
            $firstday = strtotime('+1 day', $firstday);
        } 
        }

        // 余额退款
        $balance_where = $where . ' AND pdc_type=\'0\'';
        $balance_result = $this->sys_model_data_sum->getCashSumForDaysCity($balance_where,$filed);
        $balanceTotal = 0;
        if (is_array($balance_result) && !empty($balance_result)) {
            foreach ($balance_result as $val) {
                $orderData[$val['payment_date']]['balance'] = $val['total'];
                $balanceTotal += $val['total'];
            }
        }

        // 押金退款
        $deposit_where = $where . ' AND pdc_type=\'1\'';
            $deposit_result = $this->sys_model_data_sum->getCashSumForDaysCity($deposit_where,$filed);
        $depositTotal = 0;
        if (is_array($deposit_result) && !empty($deposit_result)) {
            foreach ($deposit_result as $val) {
                $orderData[$val['payment_date']]['deposit'] = $val['total'];
                $depositTotal += $val['total'];
            }
        }

        $orders = array();
        if (is_array($orderData) && !empty($orderData)) {
            foreach ($orderData as $key => $val) {
                $orders[] = array(
                    'date' => $key,
                    'balance' => isset($val['balance']) ? $val['balance'] : 0,
                    'deposit' => isset($val['deposit']) ? $val['deposit'] : 0,
                );
            }
        }

        $orderData = json_encode($orders);
        $balanceTotal = sprintf('%0.2f', $balanceTotal);
        $depositTotal = sprintf('%0.2f', $depositTotal);

        return array('cashApplyData' => $orderData, 'cashApplyBalanceTotal' => $balanceTotal, 'cashApplyDepositTotal' => $depositTotal);
    }

    /**
     * @Author   obj
     * @DateTime 2018-01-09
     * @license  注册金统计
     * @return   [type]     [description]
     */
     public function reginster(){

       $fiter = $this->request->get(array('city_id','pdr_payment_time','region_id','time_type'));
        $where = " a.regin_amount > 0 ";
        if($fiter['city_id']){
            $where .= " AND user.city_id = ".$fiter['city_id'];
        }
        if($fiter['region_id']){
            $where .= " AND user.region_id = ".$fiter['region_id'];
        }
        $time_arr = explode(' 至 ', $fiter['pdr_payment_time']);
        if ($fiter['time_type']==1) {
            if(!empty($fiter['pdr_payment_time'])){
                    $firstday = strtotime($time_arr[0].'-01-01');
                    $lastday  = bcadd(86399,strtotime($time_arr[1].'-12-31'));
                    $where .= " AND a.regin_time >= '$firstday' AND a.regin_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-01-01'));
                $lastday  = bcadd(86399,strtotime(date('Y-12-31')));
                $where .= " AND a.regin_time >= '$firstday' AND a.regin_time <= '$lastday'";
            }
        }else if($fiter['time_type']==2){
            if(!empty($fiter['pdr_payment_time'])){
                    $firstday = strtotime($time_arr[0]);
                    $lastday  = bcadd(86399,strtotime($time_arr[1].'+1 month -1 day'));
                    $where .= " AND a.regin_time >= '$firstday' AND a.regin_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-m'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-t')));
                $where .= " AND a.regin_time >= '$firstday' AND a.regin_time <= '$lastday'";
            }
        }else if($fiter['time_type']==3){
            if(!empty($fiter['pdr_payment_time'])){
                    $firstday = strtotime($time_arr[0]);
                    $lastday  = bcadd(86399,strtotime($time_arr[1]));
                    $where .= " AND a.regin_time >= '$firstday' AND a.regin_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-m-d'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-d')));
                $where .= " AND a.regin_time >= '$firstday' AND a.regin_time <= '$lastday'";
            }
        }else{
            if(!empty($fiter['pdr_payment_time'])){
                    $firstday = strtotime($time_arr[0]);
                    $lastday  = bcadd(86399,strtotime($time_arr[1]));
                    $where .= " AND a.regin_time >= '$firstday' AND a.regin_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-m-01'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-d')));
                $where .= " AND a.regin_time >= '$firstday' AND a.regin_time <= '$lastday'";
            }
        }


        
        if($fiter['time_type']==1){
            $filed = "FROM_UNIXTIME(a.regin_time,'%Y') as date,sum(a.regin_amount) as amount";
        }else if($fiter['time_type']==2){
            $filed = "FROM_UNIXTIME(a.regin_time,'%Y-%m') as date,sum(a.regin_amount) as amount";
        }else{
            $filed = "FROM_UNIXTIME(a.regin_time,'%Y-%m-%d') as date,sum(a.regin_amount) as amount";
        }
        
        $rechargeLists = $this->sys_model_comment->getRegin2($where,$filed);
        // 初始化订单统计数据
        $chart_arr = array();
        if($fiter['time_type']==1){
             while ($firstday <= $lastday) {
            $tempDay = date('Y', $firstday);
            $chart_arr[$tempDay] = array(
                'date' => $tempDay,
                'amount' => 0,
            );
            $firstday = strtotime('+1 year', $firstday);
        }
        }else if($fiter['time_type']==2){
            while ($firstday <= $lastday) {
            $tempDay = date('Y-m', $firstday);
            $chart_arr[$tempDay] = array(
                'date' => $tempDay,
                'amount' => 0,
            );
            $firstday = strtotime('+1 month', $firstday);
        }
        }else{
            while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $chart_arr[$tempDay] = array(
                'date' => $tempDay,
                'amount' => 0,
            );
            $firstday = strtotime('+1 day', $firstday);
        }
        }
        //整理输出结果；
        foreach($rechargeLists as $v){
            $chart_arr[$v['date']] = array(
                'date'   => $v['date'],
                'amount' => $v['amount'],
            );
        }
        $reginAmountTotal=0;
        $regin = array();
        $total = $this->sys_model_comment->getReginLong($where);
        
        $reginAmountTotal = $total['orderAmountTotal'];
        $reginAmountTotal = sprintf('%0.2f', $reginAmountTotal);
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }

        $show_result = array_values($chart_arr);
        $regin = json_encode($show_result);
        return array(
            'reginData' => $regin,
            'reginAmountTotal' => $reginAmountTotal
        );
        // $this->assign('filter_regions', $filter_regions);
        // $this->assign('time_type',get_time_type());
        // $this->assign('action',$this->url->link('user/storage/chart'));
        // $this->assign('storage_action',$this->url->link('user/storage'));
        // $show_result = array_values($chart_arr);
        // $this->assign('data', json_encode($show_result));
        // $this->assign('filter', $fiter);
        // $this->assign('reginAmountTotal', $total['reginAmountTotal']);
        
        // $this->assign('user_rating_action',$this->url->link('user/storage'));
        // $this->response->setOutPut($this->load->view('user/storage_chart',$this->output));


    }



    /**
     * 消费记录图表
     * @param $add_time
     * @return array
     */
    public function order() {
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
        $settlement_time = explode(' 至 ', $filter['add_time']);
        if($filter['time_type']==1){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($settlement_time[0].'-01-01');
                $lastday  = bcadd(86399,strtotime($settlement_time[1].'-12-31'));
                $refundWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            } else {
                $firstday = strtotime(date('Y-01-01'));
                $lastday  = bcadd(86399,strtotime(date('Y-12-31')));
                $refundWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($settlement_time[0]);
                $lastday  = bcadd(86399,strtotime($settlement_time[1].'+1 month -1 day'));
                $refundWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }else{
                $firstday = strtotime(date('Y-m'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-t')));
                $refundWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }
        }else if($filter['time_type']==3){
            if(!empty($fiter['add_time'])){
                $firstday = strtotime($settlement_time[0]);
                $lastday  = bcadd(86399,strtotime($settlement_time[1]));
                $refundWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-m-d'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-d')));
                $refundWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }
        }else{
            if(!empty($fiter['add_time'])){
                $firstday = strtotime($settlement_time[0]);
                $lastday  = bcadd(86399,strtotime($settlement_time[1]));
                $refundWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";

            }else{
                $firstday = strtotime(date('Y-m-01'));
                $lastday  = bcadd(86399,strtotime(date('Y-m-d')));
                $refundWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
                $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
            }
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

        return array('orderData'=>$orderData, 'orderAmountTotal'=>$orderAmountTotal, 'refundAmountTotal'=>$refundAmountTotal, 'ordersTotal'=>$ordersTotal);
    }

}