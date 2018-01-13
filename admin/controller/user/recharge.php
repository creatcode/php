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
class ControllerUserRecharge extends Controller
{
    private $cur_url = null;
    private $error = null;

    public function __construct($registry)
    {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载bicycle Model
        $this->load->library('sys_model/deposit', true);
        $this->load->library('sys_model/trans',true);
        $this->assign('lang',$this->language->all());
    }

    /**
     * 充值记录列表
     */
    public function index()
    {
        $filter = $this->request->get(array('filter_type', 'pdr_sn', 'mobile','email', 'pdr_amount', 'pdr_type', 'pdr_payment_state', 'pdr_admin','pdr_payment_name', 'pdr_payment_time','time_type','city_id','region_id'));

        $condition = array();
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = array('like', "%{$filter['pdr_sn']}%");
        }
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (!empty($filter['email'])) {
            $condition['email'] = array('like', "%{$filter['email']}%");
        }
        if (!empty($filter['pdr_payment_name'])) {
            $condition['pdr_payment_name'] = array('like', "%{$filter['pdr_payment_name']}%");
        }
        if (is_numeric($filter['pdr_amount'])) {
            $condition['pdr_amount'] = (float)$filter['pdr_amount'];
        }
        if (is_numeric($filter['region_id'])) {
            $condition['region.region_id'] = (int)$filter['region_id'];
        }
        if (is_numeric($filter['city_id'])) {
            $condition['city.city_id'] = (int)$filter['city_id'];
        }
        if (is_numeric($filter['pdr_type'])) {
            $condition['pdr_type'] = (int)$filter['pdr_type'];
        }
        if (is_numeric($filter['pdr_payment_state'])) {
            $condition['pdr_payment_state'] = (int)$filter['pdr_payment_state'];
        }
        if (!empty($filter['pdr_admin'])) {
            $condition['pdr_admin_name'] = array('like', "%{$filter['pdr_admin']}%");
        }

        $pdr_payment_time = explode(' 至 ', $filter['pdr_payment_time']);
        if($filter['time_type']==1){
            if (!empty($filter['pdr_payment_time'])) {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime($pdr_payment_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_payment_time[1].'-12-31'))))
                );
            } else {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );

            }
        }else if($filter['time_type']==2){
            if (!empty($filter['pdr_payment_time'])) {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime($pdr_payment_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_payment_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['pdr_payment_time'])) {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime($pdr_payment_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_payment_time[1])))
                );
            }else{
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['pdr_payment_time'])) {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime($pdr_payment_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_payment_time[1])))
                );
            }
        }

        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }

        $filter_types = array(
            'pdr_sn' => $this->language->get('t4'),
            'mobile' => $this->language->get('t5'),
            'email' => $this->language->get('t6'),
            'facebook' => $this->language->get('t7'),
            'pdr_payment_name' => $this->language->get('t8'),
            'pdr_amount' => $this->language->get('t9'),
            // 'pdr_admin' => '管理员名称',
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

        $order = 'pdr_payment_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $join = array (
            'user' => 'user.user_id=deposit_recharge.pdr_user_id',
            'city' => 'city.city_id=user.city_id',
            'region' => 'user.region_id=region.region_id'
        );

        $result = $this->sys_model_deposit->getRechargeList2($condition, $order, $limit,'',$join);
        $total = $this->sys_model_deposit->getRechargeCount2($condition, $join);
        // $recharge_type = get_recharge_type();
        // $payment_state = get_payment_state();
        // $time_type = get_time_type();
        $recharge_type = array(
            '0' => $this->language->get('t11'),
            '1' => $this->language->get('t12'),
            '2' => $this->language->get('t13'),
            '3' => $this->language->get('t14')
        );
        $payment_state = array(
            '0' => $this->language->get('t16'),
            '1' => $this->language->get('t17'),
            '-1' => $this->language->get('t18'),
            '-2' => $this->language->get('t19'),
        );
        $time_type = array(
            '1' => $this->language->get('t23'),
            '2' => $this->language->get('t24'),
            '3' => $this->language->get('t25')
        );
        $pdr_payment_name = array(
            '0' => $this->language->get('t49')
        );
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                // 余额提现url
                if ($item['pdr_type'] == 0 && in_array($item['pdr_payment_state'], array(1, -2))) {
                    $item['cashapply_action'] = $this->url->link('user/recharge/cashapply', 'pdr_id=' . $item['pdr_id']);
                }

                $item['pdr_type'] = isset($recharge_type[$item['pdr_type']]) ? $recharge_type[$item['pdr_type']] : '';
                $item['pdr_payment_name'] = isset($pdr_payment_name[$item['pdr_payment_code']]) ? $pdr_payment_name[$item['pdr_payment_code']] : '';
                $item['pdr_payment_state'] = isset($payment_state[$item['pdr_payment_state']]) ? $payment_state[$item['pdr_payment_state']] : '';

                $item['pdr_payment_time'] = !empty($item['pdr_payment_time']) ? date('Y-m-d H:i:s', $item['pdr_payment_time']) : '';

                $item['edit_action'] = $this->url->link('user/recharge/edit', 'pdr_id=' . $item['pdr_id']);
                $item['delete_action'] = $this->url->link('user/recharge/delete', 'pdr_id=' . $item['pdr_id']);
                $item['info_action'] = $this->url->link('user/recharge/info', 'pdr_id=' . $item['pdr_id']);

            }
        }

        


        $data_columns = $this->getDataColumns();
        $this->assign('filter_regions', $filter_regions);
        // $this->assign('time_type',get_time_type());
        $this->assign('time_type',$time_type);
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('pdr_types', $recharge_type);
        $this->assign('payment_states', $payment_state);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/recharge/add'));
        $this->assign('chart_action', $this->url->link('user/recharge/chart'));
        $this->assign('card_char_url', $this->url->link('user/recharge/card_chart'));
        $this->assign('card_list_url', $this->url->link('user/recharge/card_list'));

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

        $this->assign('export_action', $this->url->link('user/recharge/export'));
        

        $this->response->setOutput($this->load->view('user/recharge_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns()
    {   
        $this->setDataColumn($this->language->get('t29'));
        $this->setDataColumn($this->language->get('t30'));
        $this->setDataColumn($this->language->get('t4'));
        $this->setDataColumn($this->language->get('t31'));
        $this->setDataColumn($this->language->get('t8'));
        $this->setDataColumn($this->language->get('t9'));
        $this->setDataColumn($this->language->get('t10'));
        $this->setDataColumn($this->language->get('t15'));
        // $this->setDataColumn('管理员名称');
        $this->setDataColumn($this->language->get('t32'));
        return $this->data_columns;
    }

    /**
     * 充值记录详情
     */
    public function info()
    {
        // 编辑时获取已有的数据
        $pdr_id = $this->request->get('pdr_id');
        $condition = array(
            'pdr_id' => $pdr_id
        );
        // $payment_states = get_payment_state();
        $payment_states = array(
            '0' => $this->language->get('t16'),
            '1' => $this->language->get('t17'),
            '-1' => $this->language->get('t18'),
            '-2' => $this->language->get('t19'),
        );
        $recharge_type = array(
            '0' => $this->language->get('t11'),
            '1' => $this->language->get('t12'),
            '2' => $this->language->get('t13'),
            '3' => $this->language->get('t14')
        );
        $join = array(
            'user' => 'user_id=pdr_user_id'
        );
        $info = $this->sys_model_deposit->getRechargeInfo($condition, '*',$join);
        $payment_type = array(
            '0' => $this->language->get('t60'),
            '1' => $this->language->get('t61'),
            // 'mini_app' => '小程序',
        );
        $pdr_payment_name = array(
            '0' => $this->language->get('t49')
        );
        if (!empty($info)) {
            $model = array(
                'pdr_type' => $recharge_type,
            );
            foreach ($model as $k => $v) {
                $info[$k] = isset($v[$info[$k]]) ? $v[$info[$k]] : '';
            }
            $info['pdr_payment_state_name'] = isset($payment_states[$info['pdr_payment_state']]) ? $payment_states[$info['pdr_payment_state']] : '';
            $info['pdr_payment_name'] = isset($pdr_payment_name[$info['pdr_payment_code']]) ? $pdr_payment_name[$info['pdr_payment_code']] : '';
            $info['pdr_payment_type'] = isset($payment_type[$info['pdr_payment_type']]) ? $payment_type[$info['pdr_payment_type']] : '';
            $info['pdr_payment_time'] = (isset($info['pdr_payment_time']) && !empty($info['pdr_payment_time'])) ? date('Y-m-d H:i:s', $info['pdr_payment_time']) : '';
            $info['pdr_add_time'] = (isset($info['pdr_add_time']) && !empty($info['pdr_add_time'])) ? date('Y-m-d H:i:s', $info['pdr_add_time']) : '';
            $info['pdr_admin_name'] = (isset($info['pdr_admin_name']) && !empty($info['pdr_admin_name'])) ? $info['pdr_admin_name'] : '-';
        }

        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('user/recharge'));

        $this->response->setOutput($this->load->view('user/recharge_info', $this->output));
    }

    /**
     * 统计图表
     */
    public function chart(){
        $this->load->library('sys_model/data_sum', true);
        $filter = $this->request->get(array('add_time','city_id','time_type','region_id'));
        $where = 'find_in_set(`pdr_payment_state`, \'1,-1,-2\')';
        $pdr_payment_time = explode(' 至 ', $filter['add_time']);
        if($filter['time_type']==1){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_payment_time[0].'-01-01');
                $lastday = bcadd(86399,strtotime($pdr_payment_time[1].'-12-31'));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            } else {
                $firstday = strtotime(date('Y-01-01'));
                $lastday = bcadd(86399,strtotime(date('Y-12-31')));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_payment_time[0]);
                $lastday = bcadd(86399, strtotime($pdr_payment_time[1].'+1 month -1 day'));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            } else {
                $firstday = strtotime(date('Y-m'));
                $lastday = bcadd(86399, strtotime(date('Y-m-t')));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_payment_time[0]);
                $lastday = bcadd(86399, strtotime($pdr_payment_time[1]));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            }else{
                $firstday = strtotime(date('Y-m-d'));
                $lastday = bcadd(86399, strtotime(date('Y-m-d')));
                $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
            }
        }else{
            if (!empty($filter['add_time'])) {
                $firstday = strtotime($pdr_payment_time[0]);
                $lastday = bcadd(86399, strtotime($pdr_payment_time[1]));
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
        $balanceDailyAmount = $depositDailyAmount = $reginsterDailyAmount = array();
        if ($filter['time_type']==1) {
            while ($firstday <= $lastday) {
            $tempDay = date('Y', $firstday);
            $balanceDailyAmount[$tempDay] = $depositDailyAmount[$tempDay] = $reginsterDailyAmount[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
        }else if($filter['time_type']==2){
            while ($firstday <= $lastday) {
            $tempDay = date('Y-m', $firstday);
            $balanceDailyAmount[$tempDay] = $depositDailyAmount[$tempDay] = $reginsterDailyAmount[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
        }else{
         while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $balanceDailyAmount[$tempDay] = $depositDailyAmount[$tempDay] = $reginsterDailyAmount[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }
        }
        
        
        // 插入数据
        $result = $this->sys_model_data_sum->getDepositSumForDaysCity($where,$field);
        // $result = $this->sys_model_data_sum->getDepositSumForYearsCity($where,$w['city_id']);
        if (is_array($result) && !empty($result)) {
            foreach ($result as $item) {
                if ($item['pdr_type'] == 0) {
                    // 余额充值
                    $balanceDailyAmount[$item['payment_date']] += $item['total'];
                } else if ($item['pdr_type'] == 1) {
                    // 押金充值
                    $depositDailyAmount[$item['payment_date']] += $item['total'];
                } else if ($item['pdr_type'] ==3) {
                    //注册金充值
                    $reginsterDailyAmount[$item['payment_date']] += $item['total'];
                }
            }
        }

       
        $balanceOrderData = $depositOrderData = $reginsterOrderData = array();
        $balanceOrderTotal = $depositOrderTotal = $reginsterOrderTotal = 0;
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


        // 注册金充值统计
        if (is_array($reginsterDailyAmount) && !empty($reginsterDailyAmount)) {
            foreach ($reginsterDailyAmount as $key => $val) {
                $reginsterOrderData[] = array(
                    'date' => $key,
                    'amount' => $val
                );
                $reginsterOrderTotal += $val;
            }
        }
        $reginsterOrderData = json_encode($reginsterOrderData);
        $reginsterOrderTotal = sprintf('%0.2f', $reginsterOrderTotal);

        $time_type = array(
            '1' => $this->language->get('t23'),
            '2' => $this->language->get('t24'),
            '3' => $this->language->get('t25')
        );

        $this->assign('time_type',$time_type);
        $this->assign('filter', $filter);
        $this->assign('filter_regions', $filter_regions);
        $this->assign('balanceOrderData', $balanceOrderData);
        $this->assign('balanceOrderTotal', $balanceOrderTotal);
        $this->assign('depositOrderData', $depositOrderData);
        $this->assign('depositOrderTotal', $depositOrderTotal);
        $this->assign('reginsterOrderData', $reginsterOrderData);
        $this->assign('reginsterOrderTotal', $reginsterOrderTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/recharge'));
        $this->assign('cooperation_char_url', $this->url->link('user/recharge/cooperation'));
        $this->assign('card_char_url', $this->url->link('user/recharge/card_chart'));
        $this->assign('card_list_url', $this->url->link('user/recharge/card_list'));

        $this->response->setOutput($this->load->view('user/recharge_chart', $this->output));
    }

    public function cooperation() {
        $this->load->library('sys_model/data_sum', true);
        $filter = $this->request->get(array('add_time','cooperator_id'));
        $where = 'find_in_set(`pdr_payment_state`, \'1,-1,-2\')';
        if (!empty($filter['add_time'])) {
            $pdr_payment_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdr_payment_time[0]);
            $lastday = bcadd(86399, strtotime($pdr_payment_time[1]));
            $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday = bcadd(86399, strtotime(date('Y-m-d')));
            $where .= " AND pdr_payment_time >= '$firstday' AND pdr_payment_time <= '$lastday'";
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

        $this->assign('cooperator_id',$w['cooperator_id']);
        $this->assign('cooperList',$cooperatorList);
        $this->assign('filter', $filter);
        $this->assign('balanceOrderData', $balanceOrderData);
        $this->assign('balanceOrderTotal', $balanceOrderTotal);
        $this->assign('depositOrderData', $depositOrderData);
        $this->assign('depositOrderTotal', $depositOrderTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/recharge'));
        $this->assign('cooperation_char_url', $this->url->link('user/recharge/cooperation'));
        $this->assign('char_url', $this->url->link('user/recharge/chart'));
        $this->assign('card_char_url', $this->url->link('user/recharge/card_chart'));
        $this->assign('card_list_url', $this->url->link('user/recharge/card_list'));


        $this->response->setOutput($this->load->view('user/cooperation_recharge_chart', $this->output));
    }

    /**
     * 导出
     */
    public function export()
    {
        $filter = $this->request->post(array('filter_type', 'pdr_sn', 'mobile', 'pdr_amount', 'pdr_type', 'pdr_payment_state', 'pdr_admin', 'pdr_payment_time','city_id','region_id'));

        $condition = array();
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = array('like', "%{$filter['pdr_sn']}%");
        }
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (!empty($filter['pdr_payment_name'])) {
            $condition['pdr_payment_name'] = array('like', "%{$filter['pdr_payment_name']}%");
        }
        if (is_numeric($filter['pdr_amount'])) {
            $condition['pdr_amount'] = (float)$filter['pdr_amount'];
        }
        if (is_numeric($filter['region_id'])) {
            $condition['region.region_id'] = (int)$filter['region_id'];
        }
        if (is_numeric($filter['city_id'])) {
            $condition['city.city_id'] = (int)$filter['city_id'];
        }
        if (is_numeric($filter['pdr_type'])) {
            $condition['pdr_type'] = (int)$filter['pdr_type'];
        }
        if (is_numeric($filter['pdr_payment_state'])) {
            $condition['pdr_payment_state'] = (int)$filter['pdr_payment_state'];
        }
        if (!empty($filter['pdr_admin'])) {
            $condition['pdr_admin_name'] = array('like', "%{$filter['pdr_admin']}%");
        }
        $pdr_payment_time = explode(' 至 ', $filter['pdr_payment_time']);
        if($filter['time_type']==1){
            if (!empty($filter['add_time'])) {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime($pdr_payment_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_payment_time[1].'-12-31'))))
                );
            } else {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['add_time'])) {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime($pdr_payment_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_payment_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['add_time'])) {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime($pdr_payment_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_payment_time[1])))
                );
            }else{
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['add_time'])) {
                $condition['pdr_payment_time'] = array(
                    array('egt', strtotime($pdr_payment_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_payment_time[1])))
                );
            }
        }
        $order = 'pdr_payment_time DESC';
        $limit = '';
        $join = array (
            'user' => 'user.user_id=deposit_recharge.pdr_user_id',
            'city' => 'city.city_id=user.city_id',
            'region' => 'user.region_id=region.region_id'
        );
        $pdr_payment_name = array(
            '0' => $this->language->get('t49')
        );
        $recharge_type = array(
            '0' => $this->language->get('t11'),
            '1' => $this->language->get('t12'),
            '2' => $this->language->get('t13'),
            '3' => $this->language->get('t14')
        );
        $payment_state = array(
            '0' => $this->language->get('t16'),
            '1' => $this->language->get('t17'),
            '-1' => $this->language->get('t18'),
            '-2' => $this->language->get('t19'),
        );
        $result = $this->sys_model_deposit->getRechargeList2($condition, $order, $limit,'' ,$join);
        $list = array();
        if (is_array($result) && !empty($result)) {
            // $recharge_type = get_recharge_type();
            // $payment_state = get_payment_state();
            foreach ($result as $v) {
                $list[] = array(
                    'region_name' => $v['region_name'],
                    'city_name' => $v['city_name'],
                    'pdr_sn' => $v['pdr_sn'],
                    'pdr_user_name' => $v['pdr_user_name'],
                    'pdr_payment_name' => $pdr_payment_name[$v['pdr_payment_code']],
                    'pdr_amount' => $v['pdr_amount'],
                    'pdr_type' => $recharge_type[$v['pdr_type']],
                    'pdr_payment_state' => $payment_state[$v['pdr_payment_state']],
                    // 'pdr_admin_name' => $v['pdr_admin_name'],
                    'pdr_payment_time' => !empty($v['pdr_payment_time']) ? date('Y-m-d H:i:s', $v['pdr_payment_time']) : '',
                );
            }
        }

        $data = array(
            'title' => $this->language->get('t3'),
            'header' => array(
                'region_name' => $this->language->get('t29'),
                'city_name' => $this->language->get('t30'),
                'pdr_sn' => $this->language->get('t4'),
                'pdr_user_name' => $this->language->get('t5'),
                'pdr_payment_name' => $this->language->get('t8'),
                'pdr_amount' => $this->language->get('t9'),
                'pdr_type' => $this->language->get('t10'),
                'pdr_payment_state' => $this->language->get('t15'),
                // 'pdr_admin_name' => '管理员名称',
                'pdr_payment_time' => $this->language->get('t32'),
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 余额退款
     */
    public function cashapply()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $input = $this->request->post(array('cash_amount', 'cash_reason'));

            // 充值记录信息
            $condition = array(
                'pdr_id' => $this->request->get('pdr_id')
            );
            $recharge_info = $this->sys_model_deposit->getOneRecharge($condition);
            // 添加提现申请
            $now = time();
            $data = array(
                'pdr_sn' => $recharge_info['pdr_sn'],
                'apply_user_id' => $recharge_info['pdr_user_id'],
                'apply_user_name' => $recharge_info['pdr_user_name'],
                'apply_admin_id' => $this->logic_admin->getId(),
                'apply_admin_name' => $this->logic_admin->getadmin_name(),
                'apply_cash_amount' =>  $input['cash_amount'],
                'apply_cash_reason' => $input['cash_reason'],
                'apply_add_time' => $now,
            );
            $this->sys_model_deposit->addCashApply($data);

            $this->session->data['success'] = $this->language->get('t50');
            $filter = $this->request->get(array());
            $this->load->controller('common/base/redirect', $this->url->link('user/recharge', $filter, true));
        }
        $this->assign('title', $this->language->get('t51'));
        $this->getForm();
    }

    /**
     * 验证表单
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('cash_amount', 'cash_amount'));
        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = $this->language->get('t52');
            }
        }
        // 充值记录信息
        $condition = array(
            'pdr_id' => $this->request->get('pdr_id')
        );
        $recharge_info = $this->sys_model_deposit->getOneRecharge($condition);

        //判断是否有进行中/待计费的订单
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $recharge_info['pdr_user_id'], 'order_state' => array('in', array('1', '3'))));
        if(!empty($order_info)) {
            $this->error['warning'] = $this->language->get('t53');
        }

        if ($this->error) {
            $this->error['warning'] = $this->language->get('t54');
        }
        return !$this->error;
    }

    /**
     * 退款操作
     */
    private function cash()
    {
        // 申请提现金额
        $data = $this->request->post(array('cash_amount'));
        // 充值订单id
        $pdr_id = $this->request->get['pdr_id'];
        // 充值订单提现数据
        $recharge_info = $this->getRechargeCashData($pdr_id);
        // 是否有充值记录
        if (empty($recharge_info)) {
            $this->error['warning'] = $this->language->get('t55');
            return false;
        }
        // 提现金额不能超过用户当前余额
        if ($data['cash_amount'] > $recharge_info['allow_cash_amount']) {
            $this->error['warning'] = $this->language->get('t56') . $recharge_info['allow_cash_amount'] . $this->language->get('t44');
            return false;
        }
        $recharge_info['cash_amount'] = $data['cash_amount'];
        // 操作管理员信息
        $recharge_info['admin_id'] = $this->logic_admin->getId();
        $recharge_info['admin_name'] = $this->logic_admin->getadmin_name();
        //写入到提现申请表，并写入日志
        $result = $this->sys_model_deposit->cashApply($recharge_info);
        if ($result['state']) {
            // 退款参数
            $pdc_info = array(
                'pdc_id' => $result['data']['pdc_id'],
                'pdc_sn' => $result['data']['pdc_sn'],
                'pdc_user_id' => $recharge_info['pdr_user_id'],
                'pdc_user_name' => $recharge_info['mobile'],
                'pdc_type' => $recharge_info['pdr_type'],
                'pdc_payment_name' => $recharge_info['pdr_payment_name'],
                'pdc_payment_code' => $recharge_info['pdr_payment_code'],
                'pdc_payment_type' => $recharge_info['pdr_payment_type'],
                'pdc_payment_state' => '0',
                'pdr_amount' => $recharge_info['pdr_amount'],
                'has_cash_amount' => $recharge_info['has_cash_amount'],
                'available_deposit' => $recharge_info['available_deposit'],
                'cash_amount' => $data['cash_amount'],
                'pdr_sn' => $recharge_info['pdr_sn'],
                'trace_no' => $recharge_info['trace_no'],
                'admin_id' => $recharge_info['admin_id'],
                'admin_name' => $recharge_info['admin_name'],
            );
            // 自动退款
            $auto_refund_deposit = $this->config->get('config_auto_refund_deposit');
            if ($auto_refund_deposit) {
                if ($pdc_info['pdc_payment_code'] == 'alipay') {
                    //支付宝无密码退款
                    $result = $this->sys_model_deposit->aliPayRefund($pdc_info);
                    if ($result['state'] == 1) {
                        $this->error['success'] = $this->language->get('success_application');
                        return true;
                    } else {
                        $this->error['warning'] = $result['msg'];
                        return false;
                    }
                } else if ($pdc_info['pdc_payment_code'] == 'wxpay') {
                    // 微信无密退款
                    $ssl_cert_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $pdc_info['pdc_payment_type'] . '/apiclient_cert.pem';
                    $ssl_key_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $pdc_info['pdc_payment_type'] . '/apiclient_key.pem';
                    define('WX_SSLCERT_PATH', $ssl_cert_path);
                    define('WX_SSLKEY_PATH', $ssl_key_path);
                    $result = $this->sys_model_deposit->wxPayRefund($pdc_info);
                    if ($result['state'] == true) {
                        $this->error['success'] = $this->language->get('success_application');
                        return true;
                    } else {
                        $this->error['warning'] = $result['msg'];
                        return false;
                    }
                }
            }
            $this->error['success'] = $this->language->get('success_application');
            return true;
        } else {
            $this->error['warning'] = $result['msg'];
            return false;
        }

        $filter = $this->request->get(array());
        $this->load->controller('common/base/redirect', $this->url->link('user/cashApply', $filter, true));
    }

    /**
     * 显示表单
     */
    private function getForm()
    {
        // 申请提现金额
        $data = $this->request->post(array('cash_amount', 'cash_reason'));
        // 充值订单id
        $pdr_id = $this->request->get['pdr_id'];
        // 充值订单提现数据
        $recharge_info = $this->getRechargeCashData($pdr_id);
        if (empty($recharge_info)) {
            $this->load->controller('error/not_found', $data);
            return;
        }

        // 支付途径
        // $payment_types = get_payment_type();
        $payment_types = array(
            'app' => $this->language->get('t60'),
            'web' => $this->language->get('t61'),
            // 'mini_app' => '小程序',
        );
        @$recharge_info['pdr_payment_type'] = $payment_types[$recharge_info['pdr_payment_type']];
        // 充值订单状态
        // $payment_states = get_payment_state();
        $payment_states = array(
            '0' => $this->language->get('t16'),
            '1' => $this->language->get('t17'),
            '-1' => $this->language->get('t18'),
            '-2' => $this->language->get('t19'),
        );
        $recharge_info['pdr_payment_state'] = $payment_states[$recharge_info['pdr_payment_state']];
        // 充值时间
        $recharge_info['pdr_payment_time'] = !empty($recharge_info['pdr_payment_time']) ? date('Y-m-d H:i:s', $recharge_info['pdr_payment_time']) : '';
        //判断是否有进行中/待计费的订单
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $recharge_info['pdr_user_id'], 'order_state' => array('in', array('1', '3'))));
        $has_waiting_checkout_order = '0';
        if ($order_info && isset($order_info['order_state']) && !empty($order_info)) {
            $has_waiting_checkout_order = '1';
        }
        // $boolean_state = get_common_boolean();
        $boolean_state = array(
            '1' => $this->language->get('t62'),
            '0' => $this->language->get('t63')
        );
        $has_waiting_checkout_order = $boolean_state[$has_waiting_checkout_order];
        $payment_name = array(
            '0' => $this->language->get('t49')
        );
        $payment_type = array(
            '0' => $this->language->get('t60'),
            '1' => $this->language->get('t61')
        );
        $this->assign('data', $data);
        $this->assign('payment_name', $payment_name);
        $this->assign('payment_type', $payment_type);
        $this->assign('has_waiting_checkout_order', $has_waiting_checkout_order);
        $this->assign('recharge_info', $recharge_info);
        $this->assign('return_action', $this->url->link('user/recharge'));
        $this->assign('action', $this->cur_url . '&pdr_id=' . $this->request->get['pdr_id']);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/recharge_cashapply', $this->output));
    }

    /**
     * 充值订单提现数据
     * @param $pdr_id
     * @return mixed
     */
    public function getRechargeCashData($pdr_id)
    {
        $condition = array(
            'pdr_id' => intval($pdr_id),
            'pdr_type' => 0,
            'pdr_payment_state' => array('in', array(1, -2)),
        );
        // 充值记录
        $fields = 'deposit_recharge.*,user.mobile,user.available_deposit';
        $join = array(
            'user' => 'user.user_id=deposit_recharge.pdr_user_id'
        );
        $recharge_info = $this->sys_model_deposit->getRechargeInfo($condition, $fields,$join);
        if (empty($recharge_info)) {
            return false;
        }
        // 已退金额
        $condition = array(
            'pdr_sn' => $recharge_info['pdr_sn'],
        );
        $fields = 'sum(`pdc_amount`) as total';
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($condition, $fields);
        $recharge_info['has_cash_amount'] = !empty($cash_info) && isset($cash_info['total']) ? $cash_info['total'] : 0;

        // 充值订单剩余可退金额
        $recharge_info['allow_cash_amount'] = $recharge_info['pdr_amount'] - $recharge_info['has_cash_amount'];
        // 不能超出用户当前可退金额
        $recharge_info['allow_cash_amount'] = $recharge_info['available_deposit'] < $recharge_info['allow_cash_amount'] ? $recharge_info['available_deposit'] : $recharge_info['allow_cash_amount'];

        return $recharge_info;
    }

    /**
     * [card_chart 充值卡图表]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-21T19:34:22+0800
     */
	public function card_chart(){
		$filter = $this->request->get(array('add_time'));
        $where = ' WHERE r.pdr_type=2';
        $where .= ' AND r.pdr_payment_state=1';

        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);
            $firstday = strtotime($pdr_add_time[0]);
            $lastday  = bcadd(86399, strtotime($pdr_add_time[1]));
            $where .= " AND r.pdr_payment_time >= '$firstday' AND r.pdr_payment_time <= '$lastday'";
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
            $where .= " AND r.pdr_payment_time >= '$firstday' AND r.pdr_payment_time <= '$lastday'";
        }
        //
        /*$res 	= $this->db->getRows("SELECT SUM(r.pdr_amount) AS `total_amout`,COUNT(1) AS `total_count`,`pdr_type` , c.cooperator_name FROM rich_deposit_recharge r LEFT JOIN rich_user u ON u.user_id = r.pdr_user_id LEFT JOIN `rich_cooperator` c ON c.cooperator_id=u.cooperator_id '. $where .' GROUP BY `cooperator_name`");*/
        $res 	= $this->db->getRows("SELECT SUM(r.pdr_amount) AS `total_amount`,COUNT(1) AS `total_count`,`pdr_type` , COALESCE(c.city_name,'平台') AS city_name FROM ".DB_PREFIX."deposit_recharge r LEFT JOIN ".DB_PREFIX."user u ON u.user_id = r.pdr_user_id LEFT JOIN `".DB_PREFIX."city` c ON c.city_id=u.city_id ".$where." GROUP BY `city_name`");
        $data_chart_count 	= array();
        $data_chart_amount 	= array();
        $total_count 		= 0;
        $total_amount 		= 0;
        // 初始化订单统计数据
        foreach ($res as $k => $v) {
        	$data_chart_count[] 	= array('label'=>$v['city_name'],'value'=>$v['total_count']);
        	$data_chart_amount[] 	= array('label'=>$v['city_name'],'value'=>$v['total_amount']);
        	$total_count 	+= $v['total_count'];
        	$total_amount 	+= $v['total_amount'];
        }
        $data_chart_count 	= json_encode($data_chart_count);
        $data_chart_amount 	= json_encode($data_chart_amount);
        $this->assign('filter', $filter);
        $this->assign('data_chart_count', $data_chart_count);
        $this->assign('data_chart_amount', $data_chart_amount);
        $this->assign('total_count', $total_count);
        $this->assign('total_amount', $total_amount);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/recharge'));
        $this->assign('cooperation_char_url', $this->url->link('user/recharge/cooperation'));
        $this->assign('char_url', $this->url->link('user/recharge/chart'));
        $this->assign('card_char_url', $this->url->link('user/recharge/card_chart'));
        $this->assign('card_list_url', $this->url->link('user/recharge/card_list'));

		$this->response->setOutput($this->load->view('user/recharge_card_chart', $this->output));
	}

	/**
	 * [card_list 充值卡列表]
	 * @return   [type]                   [description]
	 * @Author   vincent
	 * @DateTime 2017-07-21T21:46:13+0800
	 */
	public function card_list(){
		$filter = $this->request->get(array('filter_type', 'cooperator_name', 'pdr_sn', 'mobile', 'pdr_amount', 'pdr_type', 'pdr_payment_state', 'pdr_admin', 'pdr_payment_time'));

        $condition = array('pdr_type'=>2);
        if (!empty($filter['cooperator_name'])) {
            $condition['cooperator.cooperator_name'] = array('like', "%{$filter['cooperator_name']}%");
        }
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = array('like', "%{$filter['pdr_sn']}%");
        }
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (!empty($filter['pdr_payment_name'])) {
            $condition['pdr_payment_name'] = array('like', "%{$filter['pdr_payment_name']}%");
        }
        if (is_numeric($filter['pdr_amount'])) {
            $condition['pdr_amount'] = (float)$filter['pdr_amount'];
        }
        if (is_numeric($filter['pdr_type'])) {
            $condition['pdr_type'] = (int)$filter['pdr_type'];
        }
        if (is_numeric($filter['pdr_payment_state'])) {
            $condition['pdr_payment_state'] = (int)$filter['pdr_payment_state'];
        }
        if (!empty($filter['pdr_admin'])) {
            $condition['pdr_admin_name'] = array('like', "%{$filter['pdr_admin']}%");
        }
        if (!empty($filter['pdr_payment_time'])) {
            $pdr_payment_time = explode(' 至 ', $filter['pdr_payment_time']);
            $condition['pdr_payment_time'] = array(
                array('egt', strtotime($pdr_payment_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_payment_time[1])))
            );
        }

        $filter_types = array(
            'cooperator_name' => '合伙人',
            'pdr_sn' => '订单号',
            'mobile' => '手机号',
            'pdr_payment_name' => '充值方式',
            'pdr_amount' => '充值金额',
            'pdr_admin' => '管理员名称',
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

        $order = 'pdr_payment_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $field  = array("deposit_recharge.*","user.*","COALESCE(cooperator.cooperator_name,'平台') as cooperator_name");
        $join   = array(
            'user'          => 'user.user_id=deposit_recharge.pdr_user_id',
            'cooperator'    => 'cooperator.cooperator_id=user.cooperator_id',
        );
        $result = $this->sys_model_deposit->getRechargeList2($condition, $field, $order, $limit, $join);
        //var_dump($this->db->getlastsql());
        $total = $this->sys_model_deposit->getRechargeCount2($condition, $join);

        $recharge_type = get_recharge_type();
        $payment_state = get_payment_state();

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                // 余额提现url
                if ($item['pdr_type'] == 0 && in_array($item['pdr_payment_state'], array(1, -2))) {
                    $item['cashapply_action'] = $this->url->link('user/recharge/cashapply', 'pdr_id=' . $item['pdr_id']);
                }

                $item['pdr_type'] = isset($recharge_type[$item['pdr_type']]) ? $recharge_type[$item['pdr_type']] : '';
                $item['pdr_payment_state'] = isset($payment_state[$item['pdr_payment_state']]) ? $payment_state[$item['pdr_payment_state']] : '';

                $item['pdr_payment_time'] = !empty($item['pdr_payment_time']) ? date('Y-m-d H:i:s', $item['pdr_payment_time']) : '';

                $item['edit_action'] = $this->url->link('user/recharge/edit', 'pdr_id=' . $item['pdr_id']);
                $item['delete_action'] = $this->url->link('user/recharge/delete', 'pdr_id=' . $item['pdr_id']);
                $item['info_action'] = $this->url->link('user/recharge/info', 'pdr_id=' . $item['pdr_id']);

            }
        }

        $this->setDataColumn('合伙人');
        $this->setDataColumn('订单号');
        $this->setDataColumn('手机号');
        $this->setDataColumn('充值方式');
        $this->setDataColumn('充值金额');
        $this->setDataColumn('充值类型');
        $this->setDataColumn('支付状态');
        $this->setDataColumn('管理员名称');
        $this->setDataColumn('支付时间');

        $data_columns = $this->data_columns;
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('pdr_types', $recharge_type);
        $this->assign('payment_states', $payment_state);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/recharge/add'));
        $this->assign('chart_action', $this->url->link('user/recharge/chart'));
        $this->assign('card_char_url', $this->url->link('user/recharge/card_chart'));
        $this->assign('card_list_url', $this->url->link('user/recharge/card_list'));

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
        $this->assign('index_action', $this->url->link('user/recharge'));
        $this->assign('export_action', $this->url->link('user/recharge/export_card'));
        $this->assign('cooperation_chart_url', $this->url->link('user/recharge/cooperation'));

        $this->response->setOutput($this->load->view('user/recharge_card_list', $this->output));
	}

    /**
     * [export_card 导出充值卡记录列表]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-25T15:36:13+0800
     */
    public function export_card(){
        $filter = $this->request->post(array('filter_type', 'pdr_sn', 'mobile', 'pdr_amount', 'pdr_type', 'pdr_payment_state', 'pdr_admin', 'pdr_payment_time'));

        $condition = array('pdr_type'=>2);
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = array('like', "%{$filter['pdr_sn']}%");
        }
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (!empty($filter['pdr_payment_name'])) {
            $condition['pdr_payment_name'] = array('like', "%{$filter['pdr_payment_name']}%");
        }
        if (is_numeric($filter['pdr_amount'])) {
            $condition['pdr_amount'] = (float)$filter['pdr_amount'];
        }
        if (is_numeric($filter['pdr_type'])) {
            $condition['pdr_type'] = (int)$filter['pdr_type'];
        }
        if (is_numeric($filter['pdr_payment_state'])) {
            $condition['pdr_payment_state'] = (int)$filter['pdr_payment_state'];
        }
        if (!empty($filter['pdr_admin'])) {
            $condition['pdr_admin_name'] = array('like', "%{$filter['pdr_admin']}%");
        }
        if (!empty($filter['pdr_payment_time'])) {
            $pdr_payment_time = explode(' 至 ', $filter['pdr_payment_time']);
            $condition['pdr_payment_time'] = array(
                array('egt', strtotime($pdr_payment_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_payment_time[1])))
            );
        }
        $order = 'pdr_payment_time DESC';
        $limit = '';

        $result = $this->sys_model_deposit->getRechargeList($condition, '*', $order, $limit);
        $list = array();
        if (is_array($result) && !empty($result)) {
            $recharge_type = get_recharge_type();
            $payment_state = get_payment_state();
            foreach ($result as $v) {
                $list[] = array(
                    'pdr_sn' => $v['pdr_sn'],
                    'pdr_user_name' => $v['pdr_user_name'],
                    'pdr_payment_name' => $v['pdr_payment_name'],
                    'pdr_amount' => $v['pdr_amount'],
                    'pdr_type' => $recharge_type[$v['pdr_type']],
                    'pdr_payment_state' => $payment_state[$v['pdr_payment_state']],
                    'pdr_admin_name' => $v['pdr_admin_name'],
                    'pdr_payment_time' => !empty($v['pdr_payment_time']) ? date('Y-m-d H:i:s', $v['pdr_payment_time']) : '',
                );
            }
        }

        $data = array(
            'title' => '充值卡列表',
            'header' => array(
                'pdr_sn' => '订单号',
                'pdr_user_name' => '手机号',
                'pdr_payment_name' => '充值方式',
                'pdr_amount' => '充值金额',
                'pdr_type' => '充值类型',
                'pdr_payment_state' => '支付状态',
                'pdr_admin_name' => '管理员名称',
                'pdr_payment_time' => '支付时间',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * [depositapply 押金退款申请]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-25T18:11:31+0800
     */
    public function depositapply(){
        // 充值订单id
        $pdc_id = $this->request->get('pdc_id');
        $where  = array(
            'pdc_id'            => $pdc_id,
        );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        // var_dump($cash_info);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }
        $pdr_sn     = $cash_info['pdr_sn'];
        $pdc_sn     = $cash_info['pdc_sn'];

        //判断是否存在审核中或者审核通过的订单
        $map['pdc_sn']          = $pdc_sn;
        $map['apply_state']     = array('in','0,1');
    	$applyInfo 	= $this->sys_model_deposit->getDepositApplyInfo($map);
        if(!empty($applyInfo)){
        	die("<script>alert('该订单已在申请列表！');history.back(-1);</script>");
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateDepositForm()) {
            $input = $this->request->post(array('apply_payment_type', 'apply_account','apply_account_name','apply_bank_name','apply_sub_bank_name','cash_reason'));

            // 充值记录信息
            $condition = array(
                'pdr_sn' => $pdr_sn
            );

            $recharge_info = $this->sys_model_deposit->getOneRecharge($condition);
            // 添加退款申请
            $now = time();
            $data = array(
                'pdc_sn'                => $pdc_sn,
                'pdr_sn'				=> $recharge_info['pdr_sn'],
                'apply_user_id'			=> $recharge_info['pdr_user_id'],
                'apply_user_name'		=> $recharge_info['pdr_user_name'],
                'apply_admin_id'		=> $this->logic_admin->getId(),
                'apply_admin_name' 		=> $this->logic_admin->getadmin_name(),
                'apply_cash_amount' 	=> $recharge_info['pdr_amount'],
                'apply_cash_reason' 	=> $input['cash_reason'],
                'apply_add_time' 		=> $now,
                'apply_payment_type'	=> $input['apply_payment_type'],
                'apply_account' 		=> $input['apply_account'],
                'apply_account_name'	=> $input['apply_payment_type']==3?$input['apply_account_name'] : '',
                'apply_bank_name' 		=> $input['apply_payment_type']==3?$input['apply_bank_name'] : '',
                'apply_sub_bank_name'	=> $input['apply_payment_type']==3?$input['apply_sub_bank_name'] : '',
            );
            $this->db->begin();
            $res1    = $this->sys_model_deposit->addDepositApply($data);
            //提现支付状态改为3
            $res2    = $this->sys_model_deposit->updateDepositCash(array('pdc_sn'=>$pdc_sn), array('pdc_payment_state'=>3));
            if($res1 && $res2){
                $this->db->commit();
                $this->session->data['success'] = '添加退款申请成功！';
                $filter = array();
                $this->load->controller('common/base/redirect', $this->url->link('user/refund_apply/deposit_list', $filter, true));
            }else{
                $this->db->rollback();
                $this->error['warning']='申请失败！增加申请记录：'.$res1.';更新提现支付状态：'.$res2;
            }

        }

        $this->assign('title', '押金退款申请');
        $this->getDepositForm();
    }



    public function reginsterapply(){
        // 充值订单id
        $pdc_id = $this->request->get('pdc_id');
        $where  = array(
            'pdc_id'            => $pdc_id,
        );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        // var_dump($cash_info);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }
        $pdr_sn     = $cash_info['pdr_sn'];
        $pdc_sn     = $cash_info['pdc_sn'];

        //判断是否存在审核中或者审核通过的订单
        $map['pdc_sn']          = $pdc_sn;
        $map['apply_state']     = array('in','0,1');
        $applyInfo  = $this->sys_model_deposit->getReginsterApplyInfo($map);
        if(!empty($applyInfo)){
            die("<script>alert('该订单已在申请列表！');history.back(-1);</script>");
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateDepositForm()) {
            $input = $this->request->post(array('apply_payment_type', 'apply_account','apply_account_name','apply_bank_name','apply_sub_bank_name','cash_reason'));

            // 充值记录信息
            $condition = array(
                'pdr_sn' => $pdr_sn
            );

            $recharge_info = $this->sys_model_deposit->getOneRecharge($condition);
            // 添加退款申请
            $now = time();
            $data = array(
                'pdc_sn'                => $pdc_sn,
                'pdr_sn'                => $recharge_info['pdr_sn'],
                'apply_user_id'         => $recharge_info['pdr_user_id'],
                'apply_user_name'       => $recharge_info['pdr_user_name'],
                'apply_admin_id'        => $this->logic_admin->getId(),
                'apply_admin_name'      => $this->logic_admin->getadmin_name(),
                'apply_cash_amount'     => $recharge_info['pdr_amount'],
                'apply_cash_reason'     => $input['cash_reason'],
                'apply_add_time'        => $now,
                'apply_payment_type'    => $input['apply_payment_type'],
                'apply_account'         => $input['apply_account'],
                'apply_account_name'    => $input['apply_payment_type']==3?$input['apply_account_name'] : '',
                'apply_bank_name'       => $input['apply_payment_type']==3?$input['apply_bank_name'] : '',
                'apply_sub_bank_name'   => $input['apply_payment_type']==3?$input['apply_sub_bank_name'] : '',
            );
            $this->db->begin();
            $res1    = $this->sys_model_deposit->addReginsterApply($data);
            //提现支付状态改为3
            $res2    = $this->sys_model_deposit->updateDepositCash(array('pdc_sn'=>$pdc_sn), array('pdc_payment_state'=>3));
            if($res1 && $res2){
                $this->db->commit();
                $this->session->data['success'] = '添加退款申请成功！';
                $filter = array();
                $this->load->controller('common/base/redirect', $this->url->link('user/refund_apply/deposit_list', $filter, true));
            }else{
                $this->db->rollback();
                $this->error['warning']='申请失败！增加申请记录：'.$res1.';更新提现支付状态：'.$res2;
            }

        }

        $this->assign('title', '注册金退款申请');
        $this->getReginForm();
    }

    /**
     * [validateDepositForm 验证押金退款表单]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-25T18:15:30+0800
     */
    private function validateDepositForm() {
        $input = $this->request->post(array('apply_payment_type', 'apply_account','apply_account_name','apply_bank_name','apply_sub_bank_name'));
        $apply_payment_type = $input['apply_payment_type'];
        if(empty($apply_payment_type)){
            $this->error['apply_payment_type'] = '请完善此项';
        }
        if(empty($input['apply_account'])){
            $this->error['apply_account'] = '请完善此项';
        }
        if(!preg_match("/^([a-z0-9_@\.])+$/i",$input['apply_account'])){
            $this->error['apply_account'] = '输入有误';
        }
        if($apply_payment_type == 3){//银行卡支付
            if(!preg_match("/^([0-9])+$/i",$input['apply_account'])){
                $this->error['apply_account'] = '输入有误';
            }
            if(!preg_match("/^[\x{4e00}-\x{9fa5}]{2,8}$/u",$input['apply_account_name'])){//2-8个汉字
                $this->error['apply_account_name'] = '输入有误';
            }
            if(!preg_match("/^[\x{4e00}-\x{9fa5}]{1,20}$/u",$input['apply_bank_name'])){//1-20个汉字
                $this->error['apply_bank_name'] = '输入有误';
            }
            if(!preg_match("/^[\x{4e00}-\x{9fa5}]{1,20}$/u",$input['apply_sub_bank_name'])){//1-20个汉字
                $this->error['apply_sub_bank_name'] = '输入有误';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告：存在错误，请检查！';
            return !$this->error;
        }

        // 充值订单id
        $pdc_id = $this->request->get('pdc_id');

        $where  = array(
            'pdc_id'            => $pdc_id,
        );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }
        $pdr_sn     = $cash_info['pdr_sn'];

        // 充值记录信息
        $condition = array(
            'pdr_sn' => $pdr_sn
        );

        $recharge_info = $this->sys_model_deposit->getOneRecharge($condition);

        //判断是否有进行中/待计费的订单
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $recharge_info['pdr_user_id'], 'order_state' => array('in', array('1', '3'))));
        if(!empty($order_info)) {
            $this->error['warning'] = '用户有待计费/进行中的订单，不能申请退押金';
            return !$this->error;
        }

        //判断用户金额余额是否小于0
        $this->load->library('sys_model/user');
        $user_info = $this->sys_model_user->getUserInfo(array('user_id' => $recharge_info['pdr_user_id']));
        if($user_info['available_deposit']<0){
            $this->error['warning'] = '用户可用金额为：'.$user_info['available_deposit'].'，不能申请退押金!';
            return !$this->error;
        }
        //判断申请金额是否超出用户冻结的押金金额
        if(empty($user_info)) {
            $this->error['warning'] = '用户未找到，不能申请退押金';
            return !$this->error;
        }
        if($recharge_info['pdr_amount']>$user_info['freeze_deposit']){
             $this->error['warning'] = '申请失败，申请金额超出用户冻结押金金额！';
             return !$this->error;
        }

        return !$this->error;
    }

    private function validateReginForm() {
        $input = $this->request->post(array('apply_payment_type', 'apply_account','apply_account_name','apply_bank_name','apply_sub_bank_name'));
        $apply_payment_type = $input['apply_payment_type'];
        if(empty($apply_payment_type)){
            $this->error['apply_payment_type'] = '请完善此项';
        }
        if(empty($input['apply_account'])){
            $this->error['apply_account'] = '请完善此项';
        }
        if(!preg_match("/^([a-z0-9_@\.])+$/i",$input['apply_account'])){
            $this->error['apply_account'] = '输入有误';
        }
        if($apply_payment_type == 3){//银行卡支付
            if(!preg_match("/^([0-9])+$/i",$input['apply_account'])){
                $this->error['apply_account'] = '输入有误';
            }
            if(!preg_match("/^[\x{4e00}-\x{9fa5}]{2,8}$/u",$input['apply_account_name'])){//2-8个汉字
                $this->error['apply_account_name'] = '输入有误';
            }
            if(!preg_match("/^[\x{4e00}-\x{9fa5}]{1,20}$/u",$input['apply_bank_name'])){//1-20个汉字
                $this->error['apply_bank_name'] = '输入有误';
            }
            if(!preg_match("/^[\x{4e00}-\x{9fa5}]{1,20}$/u",$input['apply_sub_bank_name'])){//1-20个汉字
                $this->error['apply_sub_bank_name'] = '输入有误';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告：存在错误，请检查！';
            return !$this->error;
        }

        // 充值订单id
        $pdc_id = $this->request->get('pdc_id');

        $where  = array(
            'pdc_id'            => $pdc_id,
        );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }
        $pdr_sn     = $cash_info['pdr_sn'];

        // 充值记录信息
        $condition = array(
            'pdr_sn' => $pdr_sn
        );

        $recharge_info = $this->sys_model_deposit->getOneRecharge($condition);

        //判断是否有进行中/待计费的订单
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $recharge_info['pdr_user_id'], 'order_state' => array('in', array('1', '3'))));
        if(!empty($order_info)) {
            $this->error['warning'] = '用户有待计费/进行中的订单，不能申请退注册金';
            return !$this->error;
        }

        //判断用户金额余额是否小于0
        $this->load->library('sys_model/user');
        $user_info = $this->sys_model_user->getUserInfo(array('user_id' => $recharge_info['pdr_user_id']));
        if($user_info['available_deposit']<0){
            $this->error['warning'] = '用户可用金额为：'.$user_info['available_deposit'].'，不能申请退注册金!';
            return !$this->error;
        }
        //判断申请金额是否超出用户冻结的押金金额
        if(empty($user_info)) {
            $this->error['warning'] = '用户未找到，不能申请退注册金';
            return !$this->error;
        }
        if($recharge_info['pdr_amount']>$user_info['freeze_deposit']){
             $this->error['warning'] = '申请失败，申请金额超出用户冻结押金金额！';
             return !$this->error;
        }

        return !$this->error;
    }


    private function getReginForm(){
        // 申请提现金额
        $data = $this->request->post(array('apply_payment_type', 'apply_account','apply_account_name','apply_bank_name','apply_sub_bank_name','cash_reason'));
        // 充值订单id
        $pdc_id = $this->request->get('pdc_id');

        $where  = array(
            'pdc_id'            => $pdc_id,
        );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }

        $pdr_sn     = $cash_info['pdr_sn'];
        $pdc_sn     = $cash_info['pdc_sn'];

        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }

        // 充值订单提现数据
        $recharge_info = $this->getRechargeDepositData($pdr_sn);
        if (empty($recharge_info)) {
            $this->load->controller('error/not_found', $data);
            return;
        }

        $apply_payment_types = get_apply_payment_type();
        // 支付途径
        $payment_types = get_payment_type();
        $recharge_info['pdr_payment_type'] = $payment_types[$recharge_info['pdr_payment_type']];
        // 充值订单状态
        $payment_states = get_payment_state();
        $recharge_info['pdr_payment_state'] = $payment_states[$recharge_info['pdr_payment_state']];
        // 充值时间
        $recharge_info['pdr_payment_time'] = !empty($recharge_info['pdr_payment_time']) ? date('Y-m-d H:i:s', $recharge_info['pdr_payment_time']) : '';
        //判断是否有进行中/待计费的订单
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $recharge_info['pdr_user_id'], 'order_state' => array('in', array('1', '3'))));
        $has_waiting_checkout_order = '0';
        if ($order_info && isset($order_info['order_state']) && !empty($order_info)) {
            $has_waiting_checkout_order = '1';
        }
        $boolean_state = get_common_boolean();
        $has_waiting_checkout_order = $boolean_state[$has_waiting_checkout_order];

        $filter = $this->request->get(array('pdc_sn', 'pdc_user_name', 'pdc_type', 'pdr_sn', 'pdc_amount', 'pdc_payment_code', 'pdc_payment_type', 'pdc_payment_time', 'pdc_payment_state','page'));

        $this->assign('data', $data);
        $this->assign('apply_payment_types', $apply_payment_types);
        $this->assign('has_waiting_checkout_order', $has_waiting_checkout_order);
        $this->assign('recharge_info', $recharge_info);
        $this->assign('return_action', $this->url->link('user/cashapply').'&'.http_build_query($filter));
        $this->assign('action', $this->cur_url . '&pdc_id=' . $pdc_id . '&'.http_build_query($filter));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/recharge_depositapply', $this->output));
    }

    /**
     * [getDepositForm 显示押金退款表单]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-25T18:14:09+0800
     */
    private function getDepositForm(){
        // 申请提现金额
        $data = $this->request->post(array('apply_payment_type', 'apply_account','apply_account_name','apply_bank_name','apply_sub_bank_name','cash_reason'));
        // 充值订单id
        $pdc_id = $this->request->get('pdc_id');

        $where  = array(
            'pdc_id'            => $pdc_id,
        );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }

        $pdr_sn     = $cash_info['pdr_sn'];
        $pdc_sn     = $cash_info['pdc_sn'];

        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }

        // 充值订单提现数据
        $recharge_info = $this->getRechargeDepositData($pdr_sn);
        if (empty($recharge_info)) {
            $this->load->controller('error/not_found', $data);
            return;
        }

        $apply_payment_types = get_apply_payment_type();
        // 支付途径
        $payment_types = get_payment_type();
        $recharge_info['pdr_payment_type'] = $payment_types[$recharge_info['pdr_payment_type']];
        // 充值订单状态
        $payment_states = get_payment_state();
        $recharge_info['pdr_payment_state'] = $payment_states[$recharge_info['pdr_payment_state']];
        // 充值时间
        $recharge_info['pdr_payment_time'] = !empty($recharge_info['pdr_payment_time']) ? date('Y-m-d H:i:s', $recharge_info['pdr_payment_time']) : '';
        //判断是否有进行中/待计费的订单
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $recharge_info['pdr_user_id'], 'order_state' => array('in', array('1', '3'))));
        $has_waiting_checkout_order = '0';
        if ($order_info && isset($order_info['order_state']) && !empty($order_info)) {
            $has_waiting_checkout_order = '1';
        }
        $boolean_state = get_common_boolean();
        $has_waiting_checkout_order = $boolean_state[$has_waiting_checkout_order];

        $filter = $this->request->get(array('pdc_sn', 'pdc_user_name', 'pdc_type', 'pdr_sn', 'pdc_amount', 'pdc_payment_code', 'pdc_payment_type', 'pdc_payment_time', 'pdc_payment_state','page'));

        $this->assign('data', $data);
        $this->assign('apply_payment_types', $apply_payment_types);
        $this->assign('has_waiting_checkout_order', $has_waiting_checkout_order);
        $this->assign('recharge_info', $recharge_info);
        $this->assign('return_action', $this->url->link('user/cashapply').'&'.http_build_query($filter));
        $this->assign('action', $this->cur_url . '&pdc_id=' . $pdc_id . '&'.http_build_query($filter));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/recharge_depositapply', $this->output));
    }

    public function getRechargeDepositData($pdr_sn){
        $condition = array(
            'pdr_sn'            => $pdr_sn,
            'pdr_type'          => 1,
            'pdr_payment_state' => array('in', array(1, -2)),
        );
        // 充值记录
        $fields = 'deposit_recharge.*,user.mobile,user.available_deposit';
        $join = array(
            'user' => 'user.user_id=deposit_recharge.pdr_user_id'
        );
        $recharge_info = $this->sys_model_deposit->getRechargeInfo($condition, $fields,$join);
        if (empty($recharge_info)) {
            return false;
        }

        return $recharge_info;
    }

    /**
     * [depositapply 转账申请]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-08-09T23:14:52+0800
     */
    public function trans_apply(){
        $data = $this->request->post(array('cash_reason'));
        $pdc_id     = $this->request->get('pdc_id');

        $where  = array(
            'pdc_id'            => $pdc_id,
            'pdc_payment_code'  => 'alipay',//目前仅支持支付宝
            );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }
        $pdr_sn     = $cash_info['pdr_sn'];
        $pdc_sn     = $cash_info['pdc_sn'];

        //判断该订单是否存在审核中或者审核通过的订单
        $where2['pdc_sn']          = $pdc_sn;
        $where2['apply_state']     = array('in','0,1,2');
        $applyInfo  = $this->sys_model_trans->getTransApplyInfo($where2);
        if(!empty($applyInfo)){
            die("<script>alert('该订单已在申请列表！');history.back(-1);</script>");
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateTransForm()) {
            $input = $this->request->post(array('cash_reason'));

            // 充值记录信息
            $condition = array(
                'pdr_sn' => $pdr_sn,
            );

            $recharge_info = $this->sys_model_deposit->getOneRecharge($condition);
            // 添加转账申请
            $now = time();
            $data = array(
                'pdt_sn'                => $this->sys_model_trans->makeSn($recharge_info['pdr_user_id']),
                'pdc_sn'                => $pdc_sn,
                'pdr_sn'                => $recharge_info['pdr_sn'],
                'apply_user_id'         => $recharge_info['pdr_user_id'],
                'apply_user_name'       => $recharge_info['pdr_user_name'],
                'apply_admin_id'        => $this->logic_admin->getId(),
                'apply_admin_name'      => $this->logic_admin->getadmin_name(),
                'apply_amount'          => $cash_info['pdc_amount'],//与申请退款金额相同
                'apply_reason'          => $input['cash_reason'],
                'apply_add_time'        => $now,
            );
            $this->db->begin();
            $res1    = $this->sys_model_trans->addTransApply($data);

            //提现支付状态改为3
            $res2    = $this->sys_model_deposit->updateDepositCash(array('pdc_sn'=>$pdc_sn), array('pdc_payment_state'=>3));
            if($res1 && $res2){
                $this->db->commit();
                $this->session->data['success'] = '添加转账申请成功！';
                $filter = array();
                $this->load->controller('common/base/redirect', $this->url->link('user/trans_apply', $filter, true));
            }else{
                $this->db->rollback();
                $this->error['warning']='申请失败！增加申请记录：'.$res1.';更新提现支付状态：'.$res2;
            }

        }

        showForm:
        $this->assign('title', '转账申请');
        $this->getTransForm();
    }

    /**
     * [getDepositForm 显示转账申请表单]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-08-09T23:21:36+0800
     */
    private function getTransForm(){
        // 申请提现金额
        $data = $this->request->post(array('cash_reason'));
        // 提现订单id
        $pdc_id     = $this->request->get('pdc_id');

        $where  = array(
            'pdc_id'            => $pdc_id,
            );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }
        $pdr_sn     = $cash_info['pdr_sn'];
        $pdc_sn     = $cash_info['pdc_sn'];
        //提现信息
        $where  = array(
            'pdc_sn'            => $pdc_sn,
            'pdc_payment_code'  => 'alipay',//目前仅支持支付宝
            );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }

        $pdr_sn = $cash_info['pdr_sn'];
        // 充值订单提现数据
        $condition = array(
            'pdr_sn'            => $pdr_sn,
            'pdr_payment_state' => array('in', array(1, -2)),
        );
        // 充值记录
        $fields = 'deposit_recharge.*,user.mobile,user.available_deposit';
        $join = array(
            'user' => 'user.user_id=deposit_recharge.pdr_user_id'
        );
        $recharge_info = $this->sys_model_deposit->getRechargeInfo($condition, $fields,$join);
        if (empty($recharge_info)) {
            $this->load->controller('error/not_found', $data);
            return;
        }

        // 支付途径
        $payment_types = get_payment_type();
        $recharge_info['pdr_payment_type'] = $payment_types[$recharge_info['pdr_payment_type']];
        // 充值订单状态
        $payment_states = get_payment_state();
        $recharge_info['pdr_payment_state'] = $payment_states[$recharge_info['pdr_payment_state']];
        // 充值时间
        $recharge_info['pdr_payment_time'] = !empty($recharge_info['pdr_payment_time']) ? date('Y-m-d H:i:s', $recharge_info['pdr_payment_time']) : '';
        //判断是否有进行中/待计费的订单
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $recharge_info['pdr_user_id'], 'order_state' => array('in', array('1', '3'))));
        $has_waiting_checkout_order = '0';
        if ($order_info && isset($order_info['order_state']) && !empty($order_info)) {
            $has_waiting_checkout_order = '1';
        }
        $boolean_state = get_common_boolean();
        $has_waiting_checkout_order = $boolean_state[$has_waiting_checkout_order];

        $filter = $this->request->get(array('pdc_sn', 'pdc_user_name', 'pdc_type', 'pdr_sn', 'pdc_amount', 'pdc_payment_code', 'pdc_payment_type', 'pdc_payment_time', 'pdc_payment_state'));
        $this->assign('data', $data);
        $this->assign('has_waiting_checkout_order', $has_waiting_checkout_order);
        $this->assign('recharge_info', $recharge_info);
        $this->assign('cash_info', $cash_info);
        $this->assign('return_action', $this->url->link('user/cashapply'). '&' . http_build_query($filter));
        $this->assign('action', $this->cur_url . '&pdc_id='.$pdc_id);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/recharge_transapply', $this->output));
    }

    /**
     * [validateDepositForm 验证转账申请表单]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-25T18:15:30+0800
     */
    private function validateTransForm() {
        if ($this->error) {
            $this->error['warning'] = '警告：存在错误，请检查！';
            return !$this->error;
        }

       // 提现订单id
        $pdc_id     = $this->request->get('pdc_id');

        $where  = array(
            'pdc_id'            => $pdc_id,
            );
        $cash_info = $this->sys_model_deposit->getDepositCashInfo($where);
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }
        $pdr_sn     = $cash_info['pdr_sn'];
        $pdc_sn     = $cash_info['pdc_sn'];
        if (empty($cash_info)) {//订单不存在
            $this->load->controller('error/not_found', $data);
            return;
        }
        // 充值记录信息
        $condition = array(
            'pdr_sn' => $pdr_sn,
        );
        $recharge_info = $this->sys_model_deposit->getOneRecharge($condition);

        //判断是否有进行中/待计费的订单
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $recharge_info['pdr_user_id'], 'order_state' => array('in', array('1', '3'))));
        if(!empty($order_info)) {
            $this->error['warning'] = '用户有待计费/进行中的订单，不能申请退押金';
            return !$this->error;
        }

        //判断用户金额余额是否小于0
        $this->load->library('sys_model/user');
        $user_info = $this->sys_model_user->getUserInfo(array('user_id' => $recharge_info['pdr_user_id']));
        if($user_info['available_deposit']<0){
            $this->error['warning'] = '用户可用金额为：'.$user_info['available_deposit'].'，不能申请转账退款!';
            return !$this->error;
        }

        //判断用户是否存在
        if(empty($user_info)) {
            $this->error['warning'] = '用户未找到，不能申请转账退款！';
            return !$this->error;
        }

        //判断申请金额是否超出用户可用金额
        if($cash_info['pdc_type'] == 1){//退押金
            //判断申请金额是否超出用户冻结金额
            if($cash_info['pdc_amount']>$user_info['freeze_deposit']){
                 $this->error['warning'] = '申请失败，申请金额超出用户冻结金额！';
                 return !$this->error;
            }
        }elseif($cash_info['pdc_type'] == 0){
            //判断申请金额是否超出用户冻结金额
            if($cash_info['pdc_amount']>$user_info['freeze_deposit']){
                 $this->error['warning'] = '申请失败，申请金额超出用户冻结金额！';
                 return !$this->error;
            }
        }else{
            $this->error['warning'] = '申请失败，未知的金额类型！';
            return !$this->error;
        }

        return !$this->error;
    }
}