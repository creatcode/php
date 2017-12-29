<?php
class ControllerUserChart extends Controller {
    private $cur_url = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载 Model
        $this->load->library('sys_model/data_sum', true);
    }

    /**
     * 充值记录列表
     */
    public function index() {
        $this->load->library('sys_model/data_sum', true);
        $filter = $this->request->get(array('add_time','city_id','time_type'));
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


        //获取城市列表
        $this->load->library('sys_model/city');
        $cityList = $this->sys_model_city->getCityList();
        if(empty($cityList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }
        // var_dump($cityList);
        if(is_numeric($filter['city_id'])){
            $w['city_id'] = $filter['city_id'];
        }else{
            $w['city_id'] = 0;
        }

        // 初始化订单统计数据
        $balanceDailyAmount = $depositDailyAmount = array();
        while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $balanceDailyAmount[$tempDay] = $depositDailyAmount[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }

        // 插入数据
        $result = $this->sys_model_data_sum->getDepositSumForDays($where,$w['city_id']);
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

        $this->assign('time_type',get_time_type());
        $this->assign('cityList', $cityList);
        $this->assign('city_id',$w['city_id']);
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
    public function cashApply($add_time, $cooperator_id = 999999) {
        $filter = array('add_time'=>$add_time);
        $where = 'pdc_payment_state=\'1\'';
        if (!empty($filter['add_time'])) {
            $pdc_add_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdc_add_time[0]);
            $lastday  = bcadd(86399, strtotime($pdc_add_time[1]));
            $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
            $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
        }


        // 初始化订单统计数据
        $orderData = array();
        while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $orderData[$tempDay] = array();
            $firstday = strtotime('+1 day', $firstday);
        }

        // 余额退款
        $balance_where = $where . ' AND pdc_type=\'0\'';
        if($cooperator_id == 999999){
            $balance_result = $this->sys_model_data_sum->getCashSumForDays($balance_where);
        }else{
            $balance_result = $this->sys_model_data_sum->getCashSumForDaysCooperation($balance_where,$cooperator_id);
        }

        $balanceTotal = 0;
        if (is_array($balance_result) && !empty($balance_result)) {
            foreach ($balance_result as $val) {
                $orderData[$val['payment_date']]['balance'] = $val['total'];
                $balanceTotal += $val['total'];
            }
        }

        // 押金退款
        $deposit_where = $where . ' AND pdc_type=\'1\'';

        if($cooperator_id == 999999){
            $deposit_result = $this->sys_model_data_sum->getCashSumForDays($deposit_where);
        }else{
            $deposit_result = $this->sys_model_data_sum->getCashSumForDaysCooperation($deposit_where,$cooperator_id);
        }

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
     * 消费记录图表
     * @param $add_time
     * @return array
     */
    public function order($add_time, $cooperator_id = 999999) {
        $filter = array('add_time'=>$add_time, 'cooperator_id'=>$cooperator_id);
        $refundWhere = '`apply_state`=\'1\'';
        $orderWhere = '`order_state`=\'2\'';
        if ($filter['cooperator_id'] != 999999) {
            $refundWhere .= " AND cooperator_id = '{$filter['cooperator_id']}'";
            $orderWhere .= " AND cooperator_id = '{$filter['cooperator_id']}'";
        }
        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdr_add_time[0]);
            $lastday  = bcadd(86399, strtotime($pdr_add_time[1]));
            $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
            $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
            $refundWhere .= " AND apply_audit_time >= '$firstday' AND apply_audit_time <= '$lastday'";
            $orderWhere .= " AND settlement_time >= '$firstday' AND settlement_time <= '$lastday'";
        }
        // 初始化订单统计数据
        $dailyAmount = $dailyOrders = array();
        while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $dailyAmount[$tempDay] = $dailyOrders[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
        // 每天消费金额
        $amountResult = $this->sys_model_data_sum->getOrderAmountForDays($orderWhere);
        $amountResult = array_column($amountResult, 'total', 'order_date');

        // 每天退回消费金额
        $join = 'LEFT JOIN rich_orders ON rich_orders.order_sn=rich_orders_modify_apply.order_sn';
        $refundResult = $this->sys_model_data_sum->getRefundOrderAmountForDays($refundWhere, $join);
        $refundResult = array_column($refundResult, 'total', 'audit_time');

        // 每天订单数
        $numberResult = $this->sys_model_data_sum->getOrderCountForDays($orderWhere);
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