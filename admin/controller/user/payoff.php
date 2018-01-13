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
class ControllerUserPayoff extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载region Model
        $this->load->library('sys_model/payoff', true);
    }

    /**
     * 结算记录列表
     */
    public function index() {
        $filter = $this->request->get(array('city_id','user_type', 'search_time','time_type','region_id','payoff_state'));

        $condition = array();
        if (is_numeric($filter['city_id'])) {
            $condition['city_id'] = (int)$filter['city_id'];
        }
        if (is_numeric($filter['region_id'])) {
            $condition['region_id'] = (int)$filter['region_id'];
        }
        if (is_numeric($filter['user_type'])) {
            $condition['user_type'] = (int)$filter['user_type'];
        }
        if (is_numeric($filter['payoff_state'])) {
            $condition['payoff_state'] = (int)$filter['payoff_state'];
        }
        

        $pdr_add_time = explode(' 至 ', $filter['search_time']);
        if($filter['time_type']==1){
            if (!empty($filter['search_time'])) {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_add_time[1].'-12-31'))))
                );
            } else {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['search_time'])) {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['search_time'])) {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
                );
            }else{
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['search_time'])) {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
                );
            }
        }

        
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'payoff_add_time ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'payoff.*, city.city_name,region.region_name,user.user_type';
        $join = array(
            'user' => 'user.user_id=payoff.puser_id',
            'city' => 'city.city_id=user.city_id',
            'region'=> 'region.region_id=user.region_id'
        );
        $result = $this->sys_model_payoff->getPayoffList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_payoff->getTotalPayoffs($condition,$join);
        $payoff_states = get_payoff_state();

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['payoff_start_time'] = !empty($item['payoff_start_time']) ? date('Y.m.d', $item['payoff_start_time']) : '';
                $item['payoff_end_time'] = !empty($item['payoff_end_time']) ? date('Y.m.d', $item['payoff_end_time']) : '';
                $item['show_pay_button'] = $item['payoff_state'] == 0 ? 1 : 0;
                $item['payoff_state'] = isset($payoff_states[$item['payoff_state']]) ? $payoff_states[$item['payoff_state']] : '';
                $item['pay_action'] = $this->url->link('user/payoff/pay', 'payoff_id='.$item['payoff_id']);
                $item['delete_action'] = $this->url->link('user/payoff/delete', 'payoff_id='.$item['payoff_id']);
            }
        }

        // 加载合伙人model
        // $this->load->library('sys_model/cooperator', true);

        // $condition = array();
        // $order = '';
        // $limit = '';
        // $field = 'cooperator.cooperator_id, cooperator.cooperator_name';
        // $cooperators = $this->sys_model_cooperator->getCooperatorList($condition, $order, $limit, $field);
        
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }

        $data_columns = $this->getDataColumns();
        $this->assign('filter_regions', $filter_regions);
        $this->assign('time_type',get_time_type());
        $this->assign('user_types', array('0'=>"App用户",'1'=>'刷卡用户'));
        $this->assign('data_columns', $data_columns);
        
        $this->assign('data_rows', $result);
        $this->assign('payoff_states',$payoff_states);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);
        $this->assign('add_action', $this->url->link('user/payoff/add'));
        $this->assign('export_action', $this->url->link('user/payoff/export'));

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

        $this->response->setOutput($this->load->view('user/payoff_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('区域');
        $this->setDataColumn('城市');
        $this->setDataColumn('用户类型');
        $this->setDataColumn('结算时间段');
        // $this->setDataColumn('合伙人');
        $this->setDataColumn('单车数');
        $this->setDataColumn('桩车数');
        $this->setDataColumn('累计骑行次数');
        $this->setDataColumn('周平均骑行次数/辆');
        $this->setDataColumn('骑行金额');
        // $this->setDataColumn('合同提成比例');
        $this->setDataColumn('收入金额');
        // $this->setDataColumn('补贴费用');
        $this->setDataColumn('支付合计');
        $this->setDataColumn('总部成本收回');
        $this->setDataColumn('收款账户');
        $this->setDataColumn('支付状态');
        $this->setDataColumn('备注');
        return $this->data_columns;
    }

    /**
     * 添加结算
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('cooperator_id', 'payoff_time', 'bicycle_total', 'orders_total', 'daily_usage', 'orders_amount', 'commission_ratio', 'payoff_base', 'subsidy', 'payoff_amount', 'cost_recovery', 'account_payee', 'payoff_remarks','user_type'));

            $now = time();
            $payoff_time = explode(' 至 ', $input['payoff_time']);
            $data = array(
                'city' => $input['city'],
                'cooperator_id' => $input['cooperator_id'],
                'payoff_admin_id' => $this->logic_admin->getId(),
                'payoff_add_time' => $now,
                'payoff_start_time' => strtotime($payoff_time[0]),
                'payoff_end_time' => bcadd(86399, strtotime($payoff_time[1])),
                'bicycle_total' => (int)$input['bicycle_total'],
                'orders_total' => (int)$input['orders_total'],
                'daily_usage' => (float)$input['daily_usage'],
                'orders_amount' => (float)$input['orders_amount'],
                'commission_ratio' => (float)$input['commission_ratio'],
                'payoff_base' => (float)$input['payoff_base'],
                'subsidy' => (float)$input['subsidy'],
                'payoff_amount' => (float)$input['payoff_amount'],
                'cost_recovery' => (float)$input['cost_recovery'],
                'account_payee' => $input['account_payee'],
                'payoff_state' => 0,
                'payoff_remarks' => $input['payoff_remarks'],
            );
            $this->sys_model_payoff->addPayoff($data);

            $this->session->data['success'] = '添加合伙人结算成功！';

            // // 加载合伙人信息
            // $this->load->library('sys_model/cooperator', true);
            // // 获取合伙人信息
            // $condition = array(
            //     'cooperator_id' => $input['cooperator_id']
            // );
            // $cooperatorInfo = $this->sys_model_cooperator->getCooperatorInfo($condition);
            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '添加' . $cooperatorInfo['cooperator_name'] . '结算(' . $input['payoff_time'] . ')');

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('user/payoff', $filter, true));
        } else if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->request->get_request_header('channel') == 'api')) {
            $this->apiGetPayoffInfo();
            return;
        }

        $this->assign('title', '结算添加');
        $this->getForm();
    }

    /**
     * 支付结算
     */
    public function pay() {
        if (isset($this->request->get['payoff_id']) && $this->validateDelete()) {
            $now = time();
            $condition = array(
                'payoff_id' => $this->request->get['payoff_id']
            );
            $data = array(
                'payoff_state' => 1,
                'payoff_pay_time' => $now
            );
            $this->sys_model_payoff->updatePayoff($condition, $data);

            $this->session->data['success'] = '修改结算成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '修改结算支付状态：' . $this->request->get['payoff_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('user/payoff', $filter, true));
    }

    /**
     * 删除结算
     */
    public function delete() {
        if (isset($this->request->get['payoff_id']) && $this->validateDelete()) {
            $condition = array(
                'payoff_id' => $this->request->get['payoff_id']
            );
            $this->sys_model_region->deletePayoff($condition);

            $this->session->data['success'] = '删除结算成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '删除结算：' . $this->request->get['region_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('user/payoff', $filter, true));
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('city_id','user_type', 'search_time','time_type','region_id'));

        $condition = array();
        if (is_numeric($filter['city_id'])) {
            $condition['city_id'] = (int)$filter['city_id'];
        }
        if (is_numeric($filter['region_id'])) {
            $condition['region_id'] = (int)$filter['region_id'];
        }
        if (is_numeric($filter['user_type'])) {
            $condition['user_type'] = (int)$filter['user_type'];
        }
        $pdr_add_time = explode(' 至 ', $filter['search_time']);
        if($filter['time_type']==1){
            if (!empty($filter['search_time'])) {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0].'-01-01')),
                    array('elt', bcadd(86399, bcadd(86399,strtotime($pdr_add_time[1].'-12-31'))))
                );
            } else {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime(date('Y-01-01'))),
                    array('elt', bcadd(86399,strtotime(date('Y-12-31'))))
                );
            }
        }else if($filter['time_type']==2){
            if (!empty($filter['search_time'])) {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1].'+1 month -1 day')))
                );
            } else {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime(date('Y-m'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t'))))
                );
            }
        }else if($filter['time_type']==3){
            if (!empty($filter['search_time'])) {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
                );
            }else{
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime(date('Y-m-d'))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d'))))
                );
            }
        }else{
            if (!empty($filter['search_time'])) {
                $condition['payoff.payoff_add_time'] = array(
                    array('egt', strtotime($pdr_add_time[0])),
                    array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
                );
            }
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'payoff_add_time ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'payoff.*, city.city_name,region.region_name,user.*';
        $join = array(
            'user' => 'user.user_id=payoff.puser_id',
            'city' => 'city.city_id=user.city_id',
            'region'=> 'region.region_id=user.region_id'
        );
        $user = array();
        $user_type=array(
            '0' =>'App用户',
            '1' =>'刷卡用户'
        );
        $result = $this->sys_model_payoff->getPayoffList($condition, $order, $limit, $field, $join);
        if (is_array($result) && !empty($result)) {
            $payoff_states = get_payoff_state();
            foreach ($result as &$item) {
                if (!isset($user[$item['user_id']])) {
                    $user[$item['user_id']] = $item['user_name'];
                }
                $item['payoff_start_time'] = !empty($item['payoff_start_time']) ? date('Y.m.d', $item['payoff_start_time']) : '';
                $item['payoff_end_time'] = !empty($item['payoff_end_time']) ? date('Y.m.d', $item['payoff_end_time']) : '';
                $item['payoff_time'] = $item['payoff_start_time'] . '-' . $item['payoff_end_time'];
                $item['payoff_pay_time'] = !empty($item['payoff_pay_time']) ? date('Y.m.d', $item['payoff_pay_time']) : '';
                $item['payoff_state'] = isset($payoff_states[$item['payoff_state']]) ? $payoff_states[$item['payoff_state']] : '';
                $item['user_type'] = isset($user_type[$item['user_type']]) ? $user_type[$item['user_type']] : '';
            }
        }

        $data = array(
            'title' => sprintf('财务结算表', implode(',', $cooperators)),
            'header' => array(
                // 'payoff_pay_time' => '结算时间',
                'region_name' => '区域',
                'city_name' => '城市',
                'user_type' => '用户类型',
                'payoff_time' => '结算时间段',
                'bicycle_total' => '单车数',
                'bicycle_total' => '桩车数',
                'orders_total' => '累计骑行次数',
                'daily_usage' => '周平均骑行次数/辆',
                'orders_amount' => '骑行金额',
                // 'commission_ratio' => '合同提成比例',
                'payoff_base' => '收入金额',
                // 'subsidy' => '补贴费用',
                'payoff_amount' => '支付合计',
                'cost_recovery' => '总部成本收回',
                'account_payee' => '收款账户',
                'payoff_state' => '支付状态',
                'payoff_remarks' => '备注',
                'sign' => '签名',
            ),
            'list' => $result
        );
        unset($result);
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 获取结算信息
     */
    private function apiGetPayoffInfo() {
        $input = $this->request->post(array('payoff_time', 'cooperator_id'));
        if (empty($input['payoff_time'])) {
            $this->response->showErrorResult('结算时间段不能为空');
        } else {
            $payoff_time = explode(' 至 ', $input['payoff_time']);
        }
        if (empty($input['cooperator_id'])) {
            $this->response->showErrorResult('合伙人不能为空');
        }

        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/orders', true);

        // 合伙人单车数量
        $condition = array(
            'cooperator_id' => $input['cooperator_id'],
            'lock_sn' => array('neq', ''),
            'add_time' => array('elt', bcadd(86399, strtotime($payoff_time[1])))
        );
        $bicycle_total = $this->sys_model_bicycle->getTotalBicycles($condition);

        // 累计骑行次数
        $condition = array(
            'cooperator_id' => $input['cooperator_id'],
            'order_state' => '2',
            'settlement_time' => array(
                array('egt', strtotime($payoff_time[0])),
                array('elt', bcadd(86399, strtotime($payoff_time[1])))
            ),
        );
        $orders_total = $this->sys_model_orders->getTotalOrders($condition);

        // 订单总额
        $condition = array(
            'cooperator_id' => $input['cooperator_id'],
            'order_state' => '2',
            'settlement_time' => array(
                array('egt', strtotime($payoff_time[0])),
                array('elt', bcadd(86399, strtotime($payoff_time[1])))
            ),
        );
        $field = 'SUM(pay_amount) as amount';
        $result = $this->sys_model_orders->getOrdersInfo($condition, $field);
        $orders_amount = isset($result['amount']) ? $result['amount'] : 0;

        // 退还金额
        $condition = array(
            'apply_state' => 1,
            'cooperator_id' => $input['cooperator_id'],
            'apply_audit_time' => array(
                array('egt', strtotime($payoff_time[0])),
                array('elt', bcadd(86399, strtotime($payoff_time[1])))
            ),
        );
        $fields = 'SUM(apply_cash_amount) as amount';
        $join = array('orders' => 'orders.order_sn=orders_modify_apply.order_sn');
        $result = $this->sys_model_orders->getOrderApplyInfo($condition, $fields, $join);
        $refund_amount = isset($result['amount']) ? $result['amount'] : 0;

        // 实际骑行金额 = 订单总额 - 退还金额
        $orders_amount -= $refund_amount;

        $data = array(
            'bicycle_total' => (float)$bicycle_total,
            'orders_total' => (float)$orders_total,
            'orders_amount' => (float)$orders_amount
        );
        $this->response->showSuccessResult($data);
    }

    /**
     * 表单
     */
    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('cooperator_id', 'payoff_time', 'bicycle_total', 'orders_total', 'daily_usage', 'orders_amount', 'commission_ratio', 'payoff_base', 'subsidy', 'payoff_amount', 'cost_recovery', 'account_payee', 'payoff_remarks','user_type'));
        $region_id = $this->request->get('region_id');
        if (isset($this->request->get['region_id']) && !empty($this->request->get['region_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'payoff_id' => $this->request->get['payoff_id']
            );
            $info = $this->sys_model_payoff->getPayoffInfo($condition);
        }

        // 加载合伙人model
        // $this->load->library('sys_model/cooperator', true);

        
        // $condition = array();
        // $order = '';
        // $limit = '';
        // $field = 'cooperator.cooperator_id, cooperator.cooperator_name';
        // $cooperators = $this->sys_model_cooperator->getCooperatorList($condition, $order, $limit, $field);

        $this->assign('data', $info);
        $this->assign('time_type',get_time_type());
        $this->assign('info', $info);
        $this->assign('user_types', array('0'=>"App用户",'1'=>'刷卡用户'));
        $this->assign('action', $this->cur_url . '&region_id=' . $region_id);
        $this->assign('return_action', $this->url->link('user/payoff'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/payoff_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('cooperator_id', 'payoff_time', 'bicycle_total', 'orders_total', 'daily_usage', 'orders_amount', 'commission_ratio', 'payoff_base', 'subsidy', 'payoff_amount', 'cost_recovery', 'account_payee', 'payoff_remarks'));

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