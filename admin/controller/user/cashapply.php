<?php

/**
 * 提现管理
 * Class ControllerUserCashApply
 */
class ControllerUserCashApply extends Controller {
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载bicycle Model
        $this->load->library('sys_model/deposit', true);
    }

    public function index() {
        $filter = $this->request->get(array('pdc_sn', 'pdc_user_name', 'pdc_type', 'pdr_sn', 'pdc_amount', 'pdc_payment_code', 'pdc_payment_type', 'pdc_payment_time', 'pdc_payment_state','time_type'));

        $condition = array();
        if (!empty($filter['pdc_sn'])) {
            $condition['pdc_sn'] = array('like', "%{$filter['pdc_sn']}%");
        }
        if (!empty($filter['pdc_user_name'])) {
            $condition['pdc_user_name'] = array('like', "%{$filter['pdc_user_name']}%");
        }
        if (is_numeric($filter['pdc_type'])) {
            $condition['pdc_type'] = $filter['pdc_type'];
        }
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = array('like', "%{$filter['pdr_sn']}%");
        }
        if (!empty($filter['pdc_amount'])) {
            $condition['pdc_amount'] = array('like', "%{$filter['pdc_amount']}%");
        }
        if (!empty($filter['pdc_payment_code'])) {
            $condition['pdc_payment_code'] = array('like', "%{$filter['pdc_payment_code']}%");
        }
        if (!empty($filter['pdc_payment_type'])) {
            $condition['pdc_payment_type'] = array('like', "%{$filter['pdc_payment_type']}%");
        }
        if (!empty($filter['pdc_payment_time'])) {
            if (strpos($filter['pdc_payment_time'], '至')) {
                $pdc_payment_time = explode(' 至 ', $filter['pdc_payment_time']);
                $condition['pdc_payment_time'] = array(
                    array('egt', strtotime($pdc_payment_time[0])),
                    array('elt', bcadd(86399, strtotime($pdc_payment_time[1])))
                );
            }
        }
        if (is_numeric($filter['pdc_payment_state'])) {
            $condition['pdc_payment_state'] = $filter['pdc_payment_state'];
        }

        $filter_types = array(
            'pdc_sn' => '申请编号',
            'pdc_user_name' => '申请人',
            'pdr_sn' => '充值订单号',
            'pdc_amount' => '金额',
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }

        $cashapply_types = get_cashapply_type();
        $payment_types = get_payment_type();
        $order = 'pdc_payment_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $offset = $offset<0?0:$offset;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_deposit->getDepositCashList($condition, $limit, $order);
        $total = $this->sys_model_deposit->getDepositCashTotal($condition);

        foreach ($result as &$item) {
            switch ($item['pdc_payment_state']) {
                case '0':
                    $pdc_payment_state_text = '<span style="color: #dd4b39">未退款</span>';
                    break;
                case '1':
                    $pdc_payment_state_text = '<span style="color: #00a65a">已退款</span>';
                    break;
                case '2':
                    $pdc_payment_state_text = '<span style="color: #00a65a">退款中</span>';
                    break;
                case '3':
                    $pdc_payment_state_text = '<span style="color: #00a65a">申请中</span>';
                    break;
                case '4':
                    $pdc_payment_state_text = '<span style="color: #00a65a">退款失败</span>';
                    break;
                default:
                    $pdc_payment_state_text = '<span style="color: #dd4b39">unknown</span>';
                    break;
            }
            $item['pdc_type_name'] = isset($cashapply_types[$item['pdc_type']]) ? $cashapply_types[$item['pdc_type']] : '';
            $item['pdc_payment_type'] = isset($payment_types[$item['pdc_payment_type']]) ? $payment_types[$item['pdc_payment_type']] : '';
            $item['pdc_payment_state_text'] = $pdc_payment_state_text;
            $item['info_action'] = $this->url->link('user/cashapply/edit', 'pdc_id='.$item['pdc_id']);
            $item['pdc_payment_time'] = !empty($item['pdc_payment_time']) ? date('Y-m-d H:i:s', $item['pdc_payment_time']) : '';
            $item['apply_deposit_action'] = $this->url->link('user/recharge/depositapply', 'pdc_id='.$item['pdc_id'].'&page='.$page.'&'.http_build_query($filter));
            //add vincent:2017-08-10 转账申请
            $item['apply_trans_action'] = $this->url->link('user/recharge/trans_apply', 'pdc_id='.$item['pdc_id']. '&' . http_build_query($filter));
        }

        $payment_codes = array(
            array('code' => 'alipay', 'text' => '支付宝'),
            array('code' => 'wxpay', 'text' => '微信')
        );

        $data_columns = $this->getDataColumns();
        $this->assign('time_type',get_time_type());
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('cashapply_types', $cashapply_types);
        $this->assign('payment_types', $payment_types);
        $this->assign('payment_codes', $payment_codes);
        $this->assign('payment_state', array('已退款', '未退款'));
        //add vincent:2017-08-09 增加退款失败状态【由于交易完成导致无法退款】
        $this->assign('payment_states', array(array('text' => '未退款', 'value' => '0'), array('text' => '已退款', 'value' => '1'),array('text' => '退款中', 'value' => '2'),array('text' => '申请中', 'value' => '3'),array('text' => '退款失败', 'value' => '4')));

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results  = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->assign('export_action', $this->url->link('user/cashapply/export'));
        $this->assign('chart_action', $this->url->link('user/cashapply/chart'));
        $this->assign('cooperation_chart_url', $this->url->link('user/chart/cooperation'));
        $this->assign('cooperation_cashapply_url', $this->url->link('user/cashapply/cooperation'));

        $this->response->setOutput($this->load->view('user/cash_apply_list', $this->output));
    }

    /**
     * 处理退款页
     */
    public function edit() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $pdc_id = $this->request->post['pdc_id'];
            $this->load->library('sys_model/deposit', true);
            $cash_info = $this->sys_model_deposit->getDepositCashInfo(array('pdc_id' => $pdc_id));
            if ($cash_info['pdc_payment_state'] == 1) {
                $this->error['warning'] = '已退款，无需再操作';
                return !$this->error;
            }

            if ($this->request->post['type'] == 'agree') {
                $cash_info['pdr_amount'] = $cash_info['pdc_amount'];
                $cash_info['cash_amount'] = $cash_info['pdc_amount'];
                $cash_info['has_cash_amount'] = 0;
                $cash_info['admin_id'] = $this->logic_admin->getId();
                $cash_info['admin_name'] = $this->logic_admin->getadmin_name();
                $this->cashSubmit($cash_info);
            } elseif ($this->request->post['type'] == 'disagree') {
                $this->cashCancel($cash_info);
            }
            $filter = $this->request->get(array('pdc_sn', 'pdc_user_name', 'pdc_type', 'pdr_sn', 'pdc_amount', 'pdc_payment_code', 'pdc_payment_type', 'pdc_payment_time', 'pdc_payment_state', 'page'));
            header('Location:' . htmlspecialchars_decode($this->url->link('user/cashApply', $filter, true)));
//            $this->load->controller('common/base/redirect', $this->url->link('user/cashApply', $filter, true));
        }
        $this->assign('title', '提现审核');
        $this->getForm();
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('pdc_sn', 'pdc_user_name', 'pdc_type', 'pdr_sn', 'pdc_amount', 'pdc_payment_code', 'pdc_payment_type', 'pdc_payment_time', 'pdc_payment_state'));

        $condition = array();
        if (!empty($filter['pdc_sn'])) {
            $condition['pdc_sn'] = array('like', "%{$filter['pdc_sn']}%");
        }
        if (!empty($filter['pdc_user_name'])) {
            $condition['pdc_user_name'] = array('like', "%{$filter['pdc_user_name']}%");
        }
        if (is_numeric($filter['pdc_type'])) {
            $condition['pdc_type'] = $filter['pdc_type'];
        }
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = array('like', "%{$filter['pdr_sn']}%");
        }
        if (!empty($filter['pdc_amount'])) {
            $condition['pdc_amount'] = array('like', "%{$filter['pdc_amount']}%");
        }
        if (!empty($filter['pdc_payment_code'])) {
            $condition['pdc_payment_code'] = array('like', "%{$filter['pdc_payment_code']}%");
        }
        if (!empty($filter['pdc_payment_type'])) {
            $condition['pdc_payment_type'] = array('like', "%{$filter['pdc_payment_type']}%");
        }
        if (!empty($filter['pdc_payment_time'])) {
            if (strpos($filter['pdc_payment_time'], '至')) {
                $pdc_payment_time = explode(' 至 ', $filter['pdc_payment_time']);
                $condition['pdc_payment_time'] = array(
                    array('egt', strtotime($pdc_payment_time[0])),
                    array('elt', bcadd(86399, strtotime($pdc_payment_time[1])))
                );
            }
        }
        if (is_numeric($filter['pdc_payment_state'])) {
            $condition['pdc_payment_state'] = $filter['pdc_payment_state'];
        }
        $order = 'pdc_payment_time DESC';
        $limit = '';

        $cashapply_type = get_cashapply_type();
        $payment_types = get_payment_type();
        $result = $this->sys_model_deposit->getDepositCashList($condition, $limit, $order);
        $list = array();
        if (is_array($result) && !empty($result)) {
            $pdc_payment_state = array(
                '1' => '已退款',
                '0' => '未退款',
            );
            foreach ($result as $v) {
                $list[] = array(
                    'pdc_sn' => $v['pdc_sn'],
                    'pdc_user_name' => $v['pdc_user_name'],
                    'pdr_sn' => $v['pdr_sn'],
                    'pdc_amount' => $v['pdc_amount'],
                    'pdc_payment_name' => $v['pdc_payment_name'],
                    'pdc_payment_time' => !empty($v['pdc_payment_time']) ? date('Y-m-d H:i:s', $v['pdc_payment_time']) : '',
                    'pdc_payment_state' => isset($pdc_payment_state[$v['pdc_payment_state']]) ? $pdc_payment_state[$v['pdc_payment_state']] : '',
                    'pdc_payment_type' => isset($payment_types[$v['pdc_payment_type']]) ? $payment_types[$v['pdc_payment_type']] : '',
                    'pdc_type' => isset($cashapply_type[$v['pdc_type']]) ? $cashapply_type[$v['pdc_type']] : '',
                );
            }
        }

        $data = array(
            'title' => '提现记录列表',
            'header' => array(
                'pdc_sn' => '申请编号',
                'pdc_user_name' => '申请人',
                'pdc_type' => '提现类型',
                'pdr_sn' => '充值订单号',
                'pdc_amount' => '金额',
                'pdc_payment_name' => '支付方式',
                'pdc_payment_type' => '支付途径',
                'pdc_payment_time' => '支付时间',
                'pdc_payment_state' => '	提现支付状态',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 统计图表
     */
    public function chart() {
        $this->load->library('sys_model/data_sum', true);
        $filter = $this->request->get(array('add_time','city_id','time_type'));
        $where = 'pdc_payment_state=\'1\'';
        if (!empty($filter['add_time'])) {
            $pdc_payment_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdc_payment_time[0]);
            $lastday  = bcadd(86399, strtotime($pdc_payment_time[1]));
            $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
            $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
        }

        //获取城市列表
        $this->load->library('sys_model/city');
        $cityList = $this->sys_model_city->getCityList('');
        if(empty($cityList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }
       
        if(is_numeric($filter['city_id'])){
            $w['city_id'] = $filter['city_id'];
        }else{
            $w['city_id'] = 0;
        }

        // 初始化订单统计数据
        $orderData = array();
        while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $orderData[$tempDay] = array();
            $firstday = strtotime('+1 day', $firstday);
        }

        // 余额
        $balance_where = $where . ' AND pdc_type=\'0\'';
        $balance_result = $this->sys_model_data_sum->getCashSumForDaysCity($balance_where,$w['city_id']);
        $balanceTotal = 0;
        if (is_array($balance_result) && !empty($balance_result)) {
            foreach ($balance_result as $val) {
                $orderData[$val['payment_date']]['balance'] = $val['total'];
                $balanceTotal += $val['total'];
            }
        }

        // 押金
        $deposit_where = $where . ' AND pdc_type=\'1\'';
        $deposit_result = $this->sys_model_data_sum->getCashSumForDaysCity($deposit_where,$w['city_id']);
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

        $this->assign('time_type',get_time_type());
        $this->assign('filter', $filter);
        $this->assign('cityList', $cityList);
        $this->assign('city_id',$w['city_id']);
        $this->assign('orderData', $orderData);
        $this->assign('balanceTotal', $balanceTotal);
        $this->assign('depositTotal', $depositTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/cashapply'));
        $this->assign('cooperation_cashapply_url', $this->url->link('user/cashapply/cooperation'));
        $this->response->setOutput($this->load->view('user/cash_apply_chart', $this->output));
    }


    public function cooperation() {
        $this->load->library('sys_model/data_sum', true);
        $filter = $this->request->get(array('add_time', 'cooperator_id'));
        $where = 'pdc_payment_state=\'1\'';
        if (!empty($filter['add_time'])) {
            $pdc_payment_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdc_payment_time[0]);
            $lastday  = bcadd(86399, strtotime($pdc_payment_time[1]));
            $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
            $where .= " AND pdc_payment_time >= '$firstday' AND pdc_payment_time <= '$lastday'";
        }

        #全部合伙人
        $this->load->library('sys_model/cooperator');
        $cooperatorList = $this->sys_model_cooperator->getCooperatorList();
        if(empty($cooperatorList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }
        if(is_numeric($filter['cooperator_id'])){
            $w['cooperator_id'] = $filter['cooperator_id'];
        }else{
            $w['cooperator_id'] = 0;
        }

        // 初始化订单统计数据
        $orderData = array();
        while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $orderData[$tempDay] = array();
            $firstday = strtotime('+1 day', $firstday);
        }


        // 余额
        $balance_where = $where . ' AND pdc_type=\'0\'';
        $balance_result = $this->sys_model_data_sum->getCashSumForDaysCooperation($balance_where, $w['cooperator_id']);
        $balanceTotal = 0;
        if (is_array($balance_result) && !empty($balance_result)) {
            foreach ($balance_result as $val) {
                $orderData[$val['payment_date']]['balance'] = $val['total'];
                $balanceTotal += $val['total'];
            }
        }

        // 押金
        $deposit_where = $where . ' AND pdc_type=\'1\'';
        $deposit_result = $this->sys_model_data_sum->getCashSumForDaysCooperation($deposit_where, $w['cooperator_id']);
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

        $this->assign('cooperList', $cooperatorList);
        $this->assign('filter', $filter);
        $this->assign('cooperator_id',$filter['cooperator_id']);
        $this->assign('orderData', $orderData);
        $this->assign('balanceTotal', $balanceTotal);
        $this->assign('depositTotal', $depositTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/cashapply'));

        $this->assign('cashapply_chart', $this->url->link('user/cashapply/chart'));
        $this->response->setOutput($this->load->view('user/cooperation_cash_apply_chart', $this->output));
    }

    private function cashCancel($pdc_info) {
        $result = $this->sys_model_deposit->cashCancel($pdc_info);
    }

    private function cashSubmit($pdc_info) {
        if ($pdc_info['pdc_payment_code'] == 'alipay') {
            //支付宝无密码退款
            $result = $this->sys_model_deposit->aliPayRefund($pdc_info);
            if ($result['state'] == 1) {
                $this->load->controller('common/base/redirect', $this->url->link('user/cashapply'));
            } else {
                die($result['msg']);
            }
        } else {
            $ssl_cert_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $pdc_info['pdc_payment_type'] . '/apiclient_cert.pem';
            $ssl_key_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $pdc_info['pdc_payment_type'] . '/apiclient_key.pem';
            define('WX_SSLCERT_PATH', $ssl_cert_path);
            define('WX_SSLKEY_PATH', $ssl_key_path);
            $result = $this->sys_model_deposit->wxPayRefund($pdc_info);
            if ($result['state'] == 1) {
                $this->load->controller('common/base/redirect', $this->url->link('user/cashapply'));
            } else {
                die($result['msg']);
            }
        }
    }

    private function getForm() {
        $condition = array();
        $condition['pdc_id'] = intval($this->request->get['pdc_id']);
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($condition);
        if (empty($cash_info)) {

        }

        //申请时间
        if ($cash_info['pdc_add_time']) {
            $cash_info['pdc_add_time'] = date('Y-m-d H:i:s', $cash_info['pdc_add_time']);
        }
        // 退款时间
        if ($cash_info['pdc_payment_time']) {
            $cash_info['pdc_payment_time'] = date('Y-m-d H:i:s', $cash_info['pdc_payment_time']);
        }
        
        //fix vincent:2017-08-09 完善退款状态
        switch ($cash_info['pdc_payment_state']) {
            case '0':
                $cash_info['pdc_payment_state_text'] = '未退款';
                break;
            case '1':
                $cash_info['pdc_payment_state_text'] = '已退款';
                break;
            case '2':
                $cash_info['pdc_payment_state_text'] = '退款中';
                break;
            case '3':
                $cash_info['pdc_payment_state_text'] = '申请中';
                break;
            case '4':
                $cash_info['pdc_payment_state_text'] = '退款失败';
                break;
            default:
                $cash_info['pdc_payment_state_text'] = 'Unknow';
                break;
        }

        $cashapply_types = get_cashapply_type();
        $cash_info['pdc_type'] = $cashapply_types[$cash_info['pdc_type']];

        $filter = $this->request->get(array('apply_user_name', 'order_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'cooperator_id', 'page'));
        $this->assign('pdc_id', $condition['pdc_id']);
        $this->assign('data', $cash_info);
        $this->assign('return_action', $this->url->link('user/cashapply'));
        $this->assign('action', $this->cur_url . '&' . http_build_query($filter) . '&pdc_id=' . $condition['pdc_id']);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/cash_apply_operator', $this->output));
    }

    private function validateForm() {
        $input = $this->request->post(array('pdc_id', 'type'));
        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '参数错误';
            }
        }
        if ($this->error) {
            $this->error['warning'] = '警告：存在错误，请检查！';
        }
        return !$this->error;
    }

    protected function getDataColumns() {
        $this->setDataColumn('申请编号');
        $this->setDataColumn('申请人');
        $this->setDataColumn('退款类型');
        $this->setDataColumn('充值订单号');
        $this->setDataColumn('金额');
        $this->setDataColumn('支付方式');
        $this->setDataColumn('支付途径');
        $this->setDataColumn('支付时间');
        $this->setDataColumn('提现支付状态');
        return $this->data_columns;
    }
}
