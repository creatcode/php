<?php
/**
 * 退款申请
 */
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
class ControllerUserRefundApply  extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载log Model
        $this->load->library('sys_model/deposit', true);
    }

    /**
     * 申请列表
     */
    public function index() {
        $filter = $this->request->get(array('apply_city','apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time','city_id','time_type'));

        $condition=array();
        if (!empty($filter['apply_user_name'])) {
            $condition['apply_user_name'] = array('like', "%{$filter['apply_user_name']}%");
        }
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = $filter['pdr_sn'];
        }
        if (!empty($filter['apply_admin_name'])) {
            $condition['apply_admin_name'] = array('like', "%{$filter['apply_admin_name']}%");
        }
        if (!empty($filter['apply_audit_admin_name'])) {
            $condition['apply_audit_admin_name'] = array('like', "%{$filter['apply_audit_admin_name']}%");
        }
        if (is_numeric($filter['apply_state'])) {
            $condition['apply_state'] = (int)$filter['apply_state'];
        }
        if (is_numeric($filter['city_id'])) {
            $condition['cash_apply.city_id'] = (int)$filter['city_id'];
        }
        if (!empty($filter['apply_add_time'])) {
            $apply_add_time = explode(' 至 ', $filter['apply_add_time']);
            $condition['apply_add_time'] = array(
                array('egt', strtotime($apply_add_time[0])),
                array('elt', bcadd(86399, strtotime($apply_add_time[1])))
            );
        }
        if (!empty($filter['apply_audit_time'])) {
            $apply_audit_time = explode(' 至 ', $filter['apply_audit_time']);
            $condition['apply_audit_time'] = array(
                array('egt', strtotime($apply_audit_time[0])),
                array('elt', bcadd(86399, strtotime($apply_audit_time[1])))
            );
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        //获取城市列表
        $this->load->library('sys_model/city');
        $cityList = $this->sys_model_city->getCityList('');
        if(empty($cityList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }
        // var_dump($cityList);
        if(is_numeric($filter['city_id'])){
            $w['city_id'] = $filter['city_id'];
        }else{
            $w['city_id'] = 0;
        }

        $order = 'apply_add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'cash_apply.*, city.city_name';
        $join = array(
            'user' => 'user.user_id=cash_apply.apply_user_id',
            'city' => 'city.city_id=cash_apply.city_id'

        );

        $result = $this->sys_model_deposit->getCashApplyList($condition, $order, $limit,$field,$join);
        $total = $this->sys_model_deposit->getTotalCashApply($condition);

        $apply_states = get_apply_states();
        $apply_states_colors = array('text-blue', 'text-green', 'text-red');
        // 是否拥有审核权限
        $show_audit_action = $this->logic_admin->hasPermission('user/refund_apply/audit');
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item = array(
                    'city_name' => $item['city_name'],
                    'apply_user_name' => $item['apply_user_name'],
                    'pdr_sn' => $item['pdr_sn'],
                    'apply_admin_name' => $item['apply_admin_name'],
                    'apply_state' => sprintf('<span class="%s">%s</span>', $apply_states_colors[$item['apply_state']], $apply_states[$item['apply_state']]),
                    'apply_cash_reason' => $item['apply_cash_reason'],
                    'apply_add_time' => !empty($item['apply_add_time']) ? date('Y-m-d H:i:s', $item['apply_add_time']) : '',
                    'apply_audit_admin_name' => $item['apply_audit_admin_name'],
                    'apply_audit_result' => $item['apply_audit_result'],
                    'apply_audit_time' => !empty($item['apply_audit_time']) ? date('Y-m-d H:i:s', $item['apply_audit_time']) : '',
                    'audit_action' => $show_audit_action && $item['apply_state'] == 0 ? $this->url->link('user/refund_apply/audit', http_build_query($filter) . '&page='. $page . '&apply_id='. $item['apply_id']) : ''
                );
            }
        }

        $filter_types = array(
            'apply_user_name' => '用户名称',
            'pdr_sn' => '充值单号',
            'apply_admin_name' => '申请管理员',
            'apply_audit_admin_name' => '审核管理员',
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }


       
        

        
        $data_columns = $this->getDataColumns();
        $this->assign('time_type',get_time_type());
        $this->assign('data_columns', $data_columns);
        $this->assign('apply_states', $apply_states);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/recharge'));

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
        $this->assign('cityList', $cityList);
        $this->assign('city_id',$w['city_id']);
        $this->assign('export_action', $this->url->link('user/refund_apply/export'));
        $this->assign('index_action', $this->url->link('user/refund_apply'));
        $this->assign('deposit_list', $this->url->link('user/refund_apply/deposit_list'));

        $this->response->setOutput($this->load->view('user/refund_list', $this->output));
    }

    /**
     * 审核
     */
    public function audit() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $success = true;
            $apply_id = $this->request->get('apply_id');
            $input = $this->request->post(array('apply_state', 'apply_audit_result'));

            // 审核通过
            if ($input['apply_state'] == 1) {
                $success = $this->cash($apply_id);
            }

            if ($success) {
                $this->session->data['success'] = '审核成功！';
                // 更新提现申请表
                $condition = array(
                    'apply_id' => $apply_id
                );
                $now = time();
                $data = array(
                    'apply_state' => $input['apply_state'],
                    'apply_audit_result' => $input['apply_audit_result'],
                    'apply_audit_admin_id' => $this->logic_admin->getId(),
                    'apply_audit_admin_name' => $this->logic_admin->getadmin_name(),
                    'apply_audit_time' => $now
                );
                $this->sys_model_deposit->updateCashApply($condition, $data);
                $filter = $this->request->get(array('apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'page'));
                $this->load->controller('common/base/redirect', html_entity_decode($this->url->link('user/refund_apply', $filter, true)));
            }
        }
        $this->assign('title', '提现审核');
        $this->getForm();
    }

    public function cash1() {
		echo (int)$this->cash($this->request->get('apply_id'));
		print_r($this->error);
        echo '#########################################';
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->get(array('apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time'));

        $condition = array();
        if (!empty($filter['apply_user_name'])) {
            $condition['apply_user_name'] = array('like', "%{$filter['apply_user_name']}%");
        }
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = $filter['pdr_sn'];
        }
        if (!empty($filter['apply_admin_name'])) {
            $condition['apply_admin_name'] = array('like', "%{$filter['apply_admin_name']}%");
        }
        if (!empty($filter['apply_audit_admin_name'])) {
            $condition['apply_audit_admin_name'] = array('like', "%{$filter['apply_audit_admin_name']}%");
        }
        if (is_numeric($filter['apply_state'])) {
            $condition['apply_state'] = (int)$filter['apply_state'];
        }
        if (!empty($filter['apply_add_time'])) {
            $apply_add_time = explode(' 至 ', $filter['apply_add_time']);
            $condition['apply_add_time'] = array(
                array('egt', strtotime($apply_add_time[0])),
                array('elt', bcadd(86399, strtotime($apply_add_time[1])))
            );
        }
        if (!empty($filter['apply_audit_time'])) {
            $apply_audit_time = explode(' 至 ', $filter['apply_audit_time']);
            $condition['apply_audit_time'] = array(
                array('egt', strtotime($apply_audit_time[0])),
                array('elt', bcadd(86399, strtotime($apply_audit_time[1])))
            );
        }

        $order = 'apply_id DESC';
        $result = $this->sys_model_deposit->getCashApplyList($condition, $order);

        $apply_states = get_apply_states();
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'apply_city' => $v['apply_city'],
                    'apply_user_name' => $v['apply_user_name'],
                    'pdr_sn' => $v['pdr_sn'],
                    'apply_admin_name' => $v['apply_admin_name'],
                    'apply_state' => $apply_states[$v['apply_state']],
                    'apply_cash_reason' => $v['apply_cash_reason'],
                    'apply_add_time' => !empty($v['apply_add_time']) ? date('Y-m-d H:i:s', $v['apply_add_time']) : '',
                    'apply_audit_admin_name' => $v['apply_audit_admin_name'],
                    'apply_audit_result' => $v['apply_audit_result'],
                    'apply_audit_time' => !empty($v['apply_audit_time']) ? date('Y-m-d H:i:s', $v['apply_audit_time']) : '',
                );
            }
        }

        $data = array(
            'title' => '退款申请',
            'header' => array(
                'apply_user_name' => '用户名称',
                'pdr_sn' => '充值单号',
                'apply_admin_name' => '申请管理员',
                'apply_state' => '申请状态',
                'apply_cash_reason' => '申请理由',
                'apply_add_time' => '申请时间',
                'apply_audit_admin_name' => '审核管理员',
                'apply_audit_result' => '审核结果',
                'apply_audit_time' => '审核时间'
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    /**
     * 验证表单
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('apply_state'));
        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请完善此项';
            }
        }
        // 不通过时必须填写驳回理由
        $apply_audit_result = $this->request->post('apply_audit_result');
        if ($input['apply_state'] == 2 && empty($apply_audit_result)) {
             $this->error['apply_audit_result'] = '请填写不通过的原因';
        }
        if ($this->error) {
            $this->error['warning'] = '警告：存在错误，请检查！';
        }
        return !$this->error;
    }

    /**
     * 显示表单
     */
    private function getForm() {
        // 申请提现金额
        $data = $this->request->post(array('apply_state', 'apply_audit_result'));
        // 充值订单id
        $apply_id = $this->request->get['apply_id'];

        // 提现申请信息
         $condition = array(
             'apply_id' => $apply_id
         );
        $cash_apply_info = $this->sys_model_deposit->getCashApplyInfo($condition);

        // 充值记录
        $condition = array(
            'pdr_sn' =>  $cash_apply_info['pdr_sn'],
        );
        $fields = 'dr.*,u.mobile,u.available_deposit';
        $recharge_info = $this->sys_model_deposit->getRechargeInfo($condition, $fields);

        // 支付途径
        $payment_types = get_payment_type();
        $recharge_info['pdr_payment_type'] = $payment_types[$recharge_info['pdr_payment_type']];
        // 充值订单状态
        $payment_states = get_payment_state();
        $recharge_info['pdr_payment_state'] = $payment_states[$recharge_info['pdr_payment_state']];
        // 充值时间
        $recharge_info['pdr_payment_time'] = !empty($recharge_info['pdr_payment_time']) ? date('Y-m-d H:i:s', $recharge_info['pdr_payment_time']) : '';

        $has_cash_amount = 0;
        // 退款记录
        $condition = array(
            'pdr_sn' =>  $cash_apply_info['pdr_sn'],
        );
        $cash_logs = $this->sys_model_deposit->getDepositCashList($condition);
        if (is_array($cash_logs) && !empty($cash_logs)) {
            foreach ($cash_logs as $cash_log) {
                if ($cash_log['pdc_payment_state'] == 1) {
                    $has_cash_amount += $cash_log['pdc_amount'];
                }
            }
        }

        $filter = $this->request->get(array('apply_user_name','apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'page', 'apply_id'));
        $this->assign('data', $data);
        $this->assign('cash_apply_info', $cash_apply_info);
        $this->assign('recharge_info', $recharge_info);
        $this->assign('cash_logs', $cash_logs);
        $this->assign('has_cash_amount', $has_cash_amount);
        $this->assign('return_action', $this->url->link('user/recharge'));
        $this->assign('action', $this->cur_url . '&' . http_build_query($filter));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/refund_form', $this->output));
    }

    /**
     * 退款操作
     */
    private function cash($apply_id) {
        // 申请提现信息
        $condition = array(
            'apply_id' => $apply_id
        );
        $cash_apply_info = $this->sys_model_deposit->getCashApplyInfo($condition);

        // 充值订单提现数据
        $recharge_info = $this->getRechargeCashData($cash_apply_info['pdr_sn']);
        // 是否有充值记录
        if (empty($recharge_info)) {
            $this->error['warning'] = '充值订单不存在';
            return false;
        }
        // 提现金额不能超过用户当前余额
        if ($cash_apply_info['apply_cash_amount'] > $recharge_info['allow_cash_amount']) {
            $this->error['warning'] = '提现金额不能超过' . $recharge_info['allow_cash_amount'] . '元' ;
            return false;
        }
        $recharge_info['cash_amount'] = $cash_apply_info['apply_cash_amount'];
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
                'cash_amount' => $cash_apply_info['apply_cash_amount'],
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
                    } else {    // 提现失败
                        // 取消提现
                        $this->sys_model_deposit->cashCancel($pdc_info);
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
                    } else {    // 提现失败
                        // 取消提现
                        $this->sys_model_deposit->cashCancel($pdc_info);
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
    }

    /**
     * 充值订单提现数据
     * @param $pdr_id
     * @return mixed
     */
    public function getRechargeCashData($pdr_sn) {
        $condition = array(
            'pdr_sn' => $pdr_sn,
            'pdr_type' => 0,
            'pdr_payment_state' => array('in', array(1, -2)),
        );
        // 充值记录
        $fields = 'dr.*,u.mobile,u.available_deposit';
        $recharge_info = $this->sys_model_deposit->getRechargeInfo($condition, $fields);
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
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('城市');
        $this->setDataColumn('用户名称');
        $this->setDataColumn('充值单号');
        $this->setDataColumn('申请管理员');
        $this->setDataColumn('申请状态');
        $this->setDataColumn('申请理由');
        $this->setDataColumn('申请时间');
        $this->setDataColumn('审核管理员');
        $this->setDataColumn('审核结果');
        $this->setDataColumn('审核时间');
        return $this->data_columns;
    }

    /**
     * [deposit_list 获取押金退款申请列表]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-25T17:21:56+0800
     */
    public function deposit_list(){
        $filter = $this->request->get(array('apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'apply_payment_type','city_id','time_type'));

        $condition = array();
        if (!empty($filter['apply_user_name'])) {
            $condition['apply_user_name'] = array('like', "%{$filter['apply_user_name']}%");
        }
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = $filter['pdr_sn'];
        }
        if (!empty($filter['apply_admin_name'])) {
            $condition['apply_admin_name'] = array('like', "%{$filter['apply_admin_name']}%");
        }
         if (is_numeric($filter['city_id'])) {
            $condition['deposit_apply.city_id'] = (int)$filter['city_id'];
        }
        if (!empty($filter['apply_audit_admin_name'])) {
            $condition['apply_audit_admin_name'] = array('like', "%{$filter['apply_audit_admin_name']}%");
        }
        if (is_numeric($filter['apply_state'])) {
            $condition['apply_state'] = (int)$filter['apply_state'];
        }
        if (!empty($filter['apply_add_time'])) {
            $apply_add_time = explode(' 至 ', $filter['apply_add_time']);
            $condition['apply_add_time'] = array(
                array('egt', strtotime($apply_add_time[0])),
                array('elt', bcadd(86399, strtotime($apply_add_time[1])))
            );
        }
        if (!empty($filter['apply_audit_time'])) {
            $apply_audit_time = explode(' 至 ', $filter['apply_audit_time']);
            $condition['apply_audit_time'] = array(
                array('egt', strtotime($apply_audit_time[0])),
                array('elt', bcadd(86399, strtotime($apply_audit_time[1])))
            );
        }
        if (!empty($filter['apply_payment_type'])) {
            $condition['apply_payment_type'] = $filter['apply_payment_type'];
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }


        //获取城市列表
        $this->load->library('sys_model/city');
        $cityList = $this->sys_model_city->getCityList('');
        if(empty($cityList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }
        // var_dump($cityList);
        if(is_numeric($filter['city_id'])){
            $w['city_id'] = $filter['city_id'];
        }else{
            $w['city_id'] = 0;
        }

        $order = 'apply_add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);
        $field = 'deposit_apply.*,pdr_payment_code,pdr_payment_name,pdr_payment_type,city.*';
        $join = array(
            'deposit_recharge'=>'deposit_recharge.pdr_sn=deposit_apply.pdr_sn',
            'city' => 'city.city_id=deposit_apply.city_id'
        );

        
        $result = $this->sys_model_deposit->getDepositApplyList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_deposit->getTotalDepositApply($condition);


        $apply_states = get_apply_states_deposit();
        $apply_payment_types = get_apply_payment_type();
        $apply_states_colors = array(0=>'text-blue', 1=>'text-blue',2=>'text-green', -1=>'text-red');
        // 是否拥有审核权限
        $show_audit_action_tech = $this->logic_admin->hasPermission('user/refund_apply/audit_deposit_tech');//技术审批
        $show_audit_action_fina = $this->logic_admin->hasPermission('user/refund_apply/audit_deposit_fina');//财务审批
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item = array(
                    'city_name' => $item['city_name'],
                    'apply_payment_type' => !empty($item['apply_payment_type'])?$apply_payment_types[$item['apply_payment_type']]:'unknow',
                    'apply_user_name' => $item['apply_user_name'],
                    'pdr_type' => $this->_get_friendly_payment_type($item['pdr_payment_type'], $item['pdr_payment_name']),
                    'apply_cash_amount' => $item['apply_cash_amount'],
                    'apply_admin_name' => $item['apply_admin_name'],
                    'apply_state' => sprintf('<span class="%s">%s</span>', $apply_states_colors[$item['apply_state']], $apply_states[$item['apply_state']]),
                    'apply_cash_reason' => $item['apply_cash_reason'],
                    'apply_add_time' => !empty($item['apply_add_time']) ? date('Y-m-d<\b\r/> H:i:s', $item['apply_add_time']) : '',
                    'apply_audit_admin_name' => $item['apply_audit_admin_name'],
                    'apply_audit_result' => strlen($item['apply_audit_result'])>30 ? substr($item['apply_audit_result'],0,30) . '...' : $item['apply_audit_result'],
                    'apply_audit_time' => !empty($item['apply_audit_time']) ? date('Y-m-d<\b\r/>H:i:s', $item['apply_audit_time']) : '',
                    'audit_action_tech' => $show_audit_action_tech && $item['apply_state'] == 0 ? $this->url->link('user/refund_apply/audit_deposit_tech', http_build_query($filter) . '&page='. $page . '&apply_id='. $item['apply_id']) : '',
                    'audit_action_fina' => $show_audit_action_fina && $item['apply_state'] == 1 ? $this->url->link('user/refund_apply/audit_deposit_fina', http_build_query($filter) . '&page='. $page . '&apply_id='. $item['apply_id']) : '',
                );
            }
        }

        $filter_types = array(
            'apply_user_name' => '用户名称',
            'pdr_sn' => '充值单号',
            'apply_admin_name' => '申请管理员',
            'apply_audit_admin_name' => '审核管理员',
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        

        $this->setDataColumn('城市');
        $this->setDataColumn('退款方式');
        $this->setDataColumn('用户手机号');
        $this->setDataColumn('充值方式');
        $this->setDataColumn('金额');
        $this->setDataColumn('申请管理员');
        $this->setDataColumn('申请状态');
        $this->setDataColumn('申请理由');
        $this->setDataColumn('申请时间');
        $this->setDataColumn('审核管理员');
        $this->setDataColumn('审核结果');
        $this->setDataColumn('审核时间');

        $this->assign('time_type',get_time_type());
        $this->assign('cityList', $cityList);
        $this->assign('city_id',$w['city_id']);
        $this->assign('data_columns', $this->data_columns);
        $this->assign('apply_states', $apply_states);
        $this->assign('apply_payment_types', $apply_payment_types);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/cashapply&pdc_type=1&pdc_payment_state=0'));

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

        $this->assign('export_action', $this->url->link('user/refund_apply/export'));
        $this->assign('index_action', $this->url->link('user/refund_apply'));
        $this->assign('deposit_list', $this->url->link('user/refund_apply/deposit_list'));

        $this->response->setOutput($this->load->view('user/refund_deposit_list', $this->output));
    }

    /**
     * 获取友好的支付方式字符串
     * @param $payment_type
     * @param $payment_name
     * @return string
     */
    private function _get_friendly_payment_type($payment_type, $payment_name) {
        if($payment_type=='web') {
            return '微信公众号';
        }
        else if($payment_type=='mini_app') {
            return '微信小程序';
        }
        else {
            return 'APP' . $payment_name;
        }

    }

    /**
     * [audit_deposit_tech 押金退款审核-技术]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-28T13:58:44+0800
     */
    public function audit_deposit_tech(){
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $apply_id = $this->request->get('apply_id');
            $input = $this->request->post(array('apply_state', 'apply_audit_result'));

            $condition = array(
                'apply_id' => $apply_id
            );
            $pda_info   = $this->sys_model_deposit->getDepositApplyInfo($condition);

            if(empty($pda_info)){//申请订单未找到
                $this->error['warning'] = '审核失败，申请订单未找到!';
                goto showForm;
            }

            $now = time();

            $this->db->begin();
            $res1 = $res2 = true;

            if ($input['apply_state'] == 1) {// 审核通过
                $apply_state    = 1;
            }else{//审核不通过
                $apply_state    = -1;
                $condition1['pdc_sn']            = $pda_info['pdc_sn'];
                $condition1['pdc_payment_state'] = 3;//只能更新申请状态订单
                $data1['pdc_payment_state']      = 0;
                $res1 = $this->sys_model_deposit->updateDepositCash($condition1, $data1);
                //var_dump($this->db->getLastSql());
            }

            $condition2 = array(
                'apply_id'      => $apply_id,
                'apply_state'   => 0,//只能审核状态为0的申请
            );
            $data2 = array(
                'apply_state'               => $apply_state,
                'apply_audit_result'        => $input['apply_audit_result'],
                'apply_audit_admin_id'      => $this->logic_admin->getId(),
                'apply_audit_admin_name'    => $this->logic_admin->getadmin_name(),
                'apply_audit_time'          => $now
            );
            $res2   = $this->sys_model_deposit->updateDepositApply($condition2, $data2);
            if($res1 && $res2){
                $this->db->commit();
                $filter = $this->request->get(array('apply_payment_type' ,'apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'page'));
                $this->load->controller('common/base/redirect', html_entity_decode($this->url->link('user/refund_apply/deposit_list', $filter, true)));
            }else{
                $this->db->rollback();
                $this->error['warning'] = '审核失败：更新押金退款记录 :'.$res1.';更新押金退款申请记录：'.$res2;
                goto showForm;
            }
        }
        showForm:
        $this->assign('title', '押金退款审核');
        $this->getDepositForm();
    }

    /**
     * [audit_deposit 押金退款审核-财务]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-26T16:23:25+0800
     */
    public function audit_deposit_fina() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $success = true;
            $apply_id = $this->request->get('apply_id');
            $input = $this->request->post(array('apply_state', 'apply_audit_result'));

            $condition = array(
                'apply_id' => $apply_id
            );
            $pda_info     = $this->sys_model_deposit->getDepositApplyInfo($condition);

            if(empty($pda_info)){//申请订单未找到
                $this->error['warning'] = '审核失败，申请订单未找到！';
                goto showForm;
            }

            $this->load->library('sys_model/user', true);

            $this->db->begin();
            $res1 = $res2 = $res3 = $res4 = true;

            //更新押金退款记录
            $condition1['pdc_sn']             = $pda_info['pdc_sn'];
            $pdc_info     = $this->sys_model_deposit->getDepositCashInfo($condition1);
            if(empty($pdc_info)){//退款订单未找到
                $this->db->rollback();
                $this->error['warning'] = '审核失败，退款订单未找到！';
                goto showForm;
            }
            if($input['apply_state'] == 1){//审核通过，押金退款状态1
                $data1['pdc_payment_state']     = 1;
            }else{//审核未通过，押金退款状态0
                $data1['pdc_payment_state']     = 0;
            }

            $res1     = $this->sys_model_deposit->updateDepositCash($condition1, $data1);

            // 审核通过
            if ($input['apply_state'] == 1) {

                //更新user表冻结押金值
                $condition2['user_id']         = $pda_info['apply_user_id'];
                $usr_info     = $this->sys_model_user->getUserInfo($condition2,'freeze_deposit');
                if(empty($usr_info)){//用户不存在
                    $this->db->rollback();
                    $this->error['warning'] = '审核失败，用户不存在！';
                    goto showForm;
                }
                if($usr_info['freeze_deposit']<$pda_info['apply_cash_amount']){//申请金额大于冻结金额
                    $this->db->rollback();
                    $this->error['warning'] = '审核失败，申请金额大于冻结金额！';
                    goto showForm;
                }
                $data2['freeze_deposit']     = $usr_info['freeze_deposit'] - $pda_info['apply_cash_amount'];
                $res2     = $this->sys_model_user->updateUser($condition2, $data2);

                //更新充值表记录状态
                $condition3['pdr_sn']             = $pda_info['pdr_sn'];
                $pdr_info     = $this->sys_model_deposit->getOneRecharge($condition3);
                if(empty($pdr_info)){//充值订单号不存在
                    $this->db->rollback();
                    $this->error['warning'] = '审核失败，充值订单号不存在！';
                    goto showForm;
                }

                $data3['pdr_payment_state']     = -1;
                $res3     = $this->sys_model_deposit->updateRecharge($condition3,$data3);
                //var_dump($this->db->getLastSql());
                $apply_state    = 2;
            }else{
                $apply_state    = -1;
            }

            // 更新押金退款申请记录
            $now = time();
            $condition4 = array(
                'apply_id'      => $apply_id,
                'apply_state'   => 1,//只能审核状态为1的申请
            );
            $data4 = array(
                'apply_state'				=> $apply_state,
                'apply_audit_result'        => $input['apply_audit_result'],
                'apply_audit_admin_id'         => $this->logic_admin->getId(),
                'apply_audit_admin_name'     => $this->logic_admin->getadmin_name(),
                'apply_audit_time'             => $now
            );
            $res4     = $this->sys_model_deposit->updateDepositApply($condition4, $data4);
            if($res1 && $res2 && $res3 && $res4){//成功
                $this->db->commit();
                $filter = $this->request->get(array('apply_payment_type' ,'apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'page'));
                $this->load->controller('common/base/redirect', html_entity_decode($this->url->link('user/refund_apply/deposit_list', $filter, true)));
            }else{//失败
                $this->db->rollback();
                $this->error['warning'] = '审核失败!更新押金退款记录 : '.$res1
                                            .'; 更新用户押金冻结金额：' . $res2
                                            .'; 更新充值记录状态：' . $res3
                                            .'; 更新押金退款申请记录：' . $res4;
                goto showForm;
            }

        }

        showForm:
        $this->assign('title', '押金退款审核');
        $this->getDepositForm();
    }

    /**
     * [getDepositForm 显示退款审核表单]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-26T16:27:21+0800
     */
    private function getDepositForm() {
        // 申请提现金额
        $data = $this->request->post(array('apply_state', 'apply_audit_result'));
        // 充值订单id
        $apply_id = $this->request->get['apply_id'];

        // 提现申请信息
         $condition = array(
             'apply_id' => $apply_id
         );
        $cash_apply_info = $this->sys_model_deposit->getDepositApplyInfo($condition);

        // 充值记录
        $condition = array(
            'pdr_sn' =>  $cash_apply_info['pdr_sn'],
        );
        $fields = 'dr.*,u.mobile,u.available_deposit';
        $recharge_info = $this->sys_model_deposit->getRechargeInfo($condition, $fields);

        $apply_payment_types = get_apply_payment_type();
        // 支付途径
        $payment_types = get_payment_type();
        $recharge_info['pdr_payment_type'] = $payment_types[$recharge_info['pdr_payment_type']];
        // 充值订单状态
        $payment_states = get_payment_state();
        $recharge_info['pdr_payment_state'] = $payment_states[$recharge_info['pdr_payment_state']];
        // 充值时间
        $recharge_info['pdr_payment_time'] = !empty($recharge_info['pdr_payment_time']) ? date('Y-m-d H:i:s', $recharge_info['pdr_payment_time']) : '';

        $has_cash_amount = 0;
        // 退款记录
        $condition = array(
            'pdr_sn' =>  $cash_apply_info['pdr_sn'],
        );
        $cash_logs = $this->sys_model_deposit->getDepositCashList($condition);
        if (is_array($cash_logs) && !empty($cash_logs)) {
            foreach ($cash_logs as $cash_log) {
                if ($cash_log['pdc_payment_state'] == 1) {
                    $has_cash_amount += $cash_log['pdc_amount'];
                }
            }
        }

        $filter = $this->request->get(array('apply_user_name', 'pdr_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'page', 'apply_id'));
        $this->assign('data', $data);
        $this->assign('apply_payment_types', $apply_payment_types);
        $this->assign('cash_apply_info', $cash_apply_info);
        $this->assign('recharge_info', $recharge_info);
        $this->assign('cash_logs', $cash_logs);
        $this->assign('has_cash_amount', $has_cash_amount);
        $this->assign('return_action', $this->url->link('user/recharge'));
        $this->assign('action', $this->cur_url . '&' . http_build_query($filter));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('user/refund_deposit_form', $this->output));
    }

}
