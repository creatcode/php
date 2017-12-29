<?php

class ControllerUserOrderApply  extends Controller {
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载log Model
        $this->load->library('sys_model/orders', true);
    }

    /**
     * 申请列表
     */
    public function index() {
        $filter = $this->request->get(array('apply_user_name', 'order_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'city_id','time_type'));

        $condition = array();
        if (!empty($filter['apply_user_name'])) {
            $condition['apply_user_name'] = array('like', "%{$filter['apply_user_name']}%");
        }
        if (!empty($filter['order_sn'])) {
            $condition['order_sn'] = $filter['order_sn'];
        }
        // if (!empty($filter['city_name'])) {
        //     $condition['city_name'] = array('like', "%{$filter['city_name']}%");
        // }
        if (!empty($filter['apply_audit_admin_name'])) {
            $condition['apply_audit_admin_name'] = array('like', "%{$filter['apply_audit_admin_name']}%");
        }
        if (is_numeric($filter['apply_state'])) {
            $condition['apply_state'] = (int)$filter['apply_state'];
        }
        if (is_numeric($filter['city_id'])) {
            $condition['orders_apply.citys_id'] = (int)$filter['city_id'];
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
        $field = 'orders_apply.*,city.*';
        $join = array(
            'orders' => 'orders.order_sn=orders_apply.order_sn',
            'city' => 'city.city_id=orders_apply.citys_id'
        );
        $result = $this->sys_model_orders->getOrderApplyList($condition, $order, $limit, $field, $join);
        $total = $this->sys_model_orders->getTotalOrderApply($condition, $join);
        // echo "<pre>";
        // var_dump($result);
        $apply_states = get_apply_states();
        $apply_states_colors = array('text-blue', 'text-green', 'text-red');
        // 是否拥有审核权限
        $show_audit_action = $this->logic_admin->hasPermission('user/refund_apply/audit');
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item = array(
                    'apply_user_name' => $item['apply_user_name'],
                    'city_name' => $item['city_name'],
                    'apply_cash_amount' => $item['apply_cash_amount'],
                    'order_sn' => $item['order_sn'],
                    'apply_admin_name' => $item['apply_admin_name'],
                    'apply_state' => sprintf('<span class="%s">%s</span>', $apply_states_colors[$item['apply_state']], $apply_states[$item['apply_state']]),
                    'apply_cash_reason' => $item['apply_cash_reason'],
                    'apply_add_time' => !empty($item['apply_add_time']) ? date('Y-m-d H:i:s', $item['apply_add_time']) : '',
                    'apply_audit_admin_name' => $item['apply_audit_admin_name'],
                    'apply_audit_result' => $item['apply_audit_result'],
                    'apply_audit_time' => !empty($item['apply_audit_time']) ? date('Y-m-d H:i:s', $item['apply_audit_time']) : '',
                    'audit_action' => $show_audit_action && $item['apply_state'] == 0 ? $this->url->link('user/order_apply/audit',  http_build_query($filter) . '&page='. $page . '&apply_id='. $item['apply_id']) : ''
                );
            }
        }

        $filter_types = array(
            'apply_user_name' => '用户名称',
            'order_sn' => '订单sn',
            'apply_admin_name' => '申请管理员',
            'apply_audit_admin_name' => '审核管理员'
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        // 加载合伙人model
        $this->load->library('sys_model/cooperator', true);
        $condition = array();
        $order = '';
        $limit = '';
        $field = 'cooperator.cooperator_id, cooperator.cooperator_name';
        $cooperators = $this->sys_model_cooperator->getCooperatorList($condition, $order, $limit, $field);

        $data_columns = $this->getDataColumns();
        $this->assign('time_type',get_time_type());
        $this->assign('data_columns', $data_columns);
        $this->assign('apply_states', $apply_states);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('cooperators', $cooperators);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $order_filter = array('order_state'=>2);
        $this->assign('add_action', $this->url->link('user/order', $order_filter, true));

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

        $this->assign('export_action', $this->url->link('user/order_apply/export'));

        $this->response->setOutput($this->load->view('user/order_apply_list', $this->output));
    }

    /**
     * 审核
     */
    public function audit() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $success = true;
            $apply_id = $this->request->get('apply_id');
            $input = $this->request->post(array('apply_state', 'apply_audit_result'));

            $now = time();
            $condition = array(
                'apply_id' => $apply_id
            );
            $data = array(
                'apply_state' => $input['apply_state'],
                'apply_audit_result' => $input['apply_audit_result'],
                'apply_audit_admin_id' => $this->logic_admin->getId(),
                'apply_audit_admin_name' => $this->logic_admin->getadmin_name(),
                'apply_audit_time' => $now
            );
            $this->sys_model_orders->updateOrderApply($condition, $data);

            // 审核通过
            if ($input['apply_state'] == 1) {
                $success = $this->cash($apply_id);
            }

            if ($success) {
                $this->session->data['success'] = '审核成功！';
                $filter = $this->request->get(array('apply_user_name', 'order_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'page'));
                $this->load->controller('common/base/redirect', html_entity_decode($this->url->link('user/order_apply', $filter, true)));
            }
        }
        $this->assign('title', '订单金额审批');
        $this->getForm();
    }


    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('apply_user_name', 'order_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'cooperator_id'));

        $condition = array();
        if (!empty($filter['apply_user_name'])) {
            $condition['apply_user_name'] = array('like', "%{$filter['apply_user_name']}%");
        }
        if (!empty($filter['order_sn'])) {
            $condition['order_sn'] = $filter['order_sn'];
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
        if (is_numeric($filter['cooperator_id'])) {
            $condition['orders.cooperator_id'] = (int)$filter['cooperator_id'];
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
        $limi = '';
        $field = 'orders_apply.*, cooperator.cooperator_name';
        $join = array(
            'orders' => 'orders.order_sn=orders_apply.order_sn',
            'cooperator' => 'cooperator.cooperator_id=orders.cooperator_id'
        );
        $result = $this->sys_model_orders->getOrderApplyList($condition, $order, $limi, $field, $join);
        $apply_states = get_apply_states();
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $list[] = array(
                    'apply_user_name' => $v['apply_user_name'],
                    'cooperator_name' => $v['cooperator_name'],
                    'order_sn' => $v['order_sn'],
                    'apply_cash_amount' => $v['apply_cash_amount'],
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
            'title' => '订单修改金额申请',
            'header' => array(
                'apply_user_name' => '用户名称',
                'cooperator_name' => '合伙人	',
                'order_sn' => '订单sn',
                'apply_cash_amount' => '金额',
                'apply_admin_name' => '申请管理员',
                'apply_state' => '申请状态',
                'apply_cash_reason' => '申请理由',
                'apply_add_time' => '申请时间',
                'apply_audit_admin_name' => '审核管理员',
                'apply_audit_time' => '审核时间',
                'apply_audit_result' => '审核结果',
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
        $data = $this->request->post(array('apply_state', 'apply_audit_result'));
        $apply_id = $this->request->get['apply_id'];
        $condition = array(
            'apply_id' => $apply_id
        );
        $order_apply_info = $this->sys_model_orders->getOrderApplyInfo($condition);
        $condition = array(
            'order_sn' =>  $order_apply_info['order_sn'],
        );
        $fields = '*';
        $order_info = $this->sys_model_orders->getOrdersInfo($condition, $fields);
        if (!empty($order_info)) {
            $order_info['order_status'] = $order_info['order_state'];
            $model = array(
                'order_state' => get_order_state()
            );
            foreach ($model as $k => $v) {
                $order_info[$k] = isset($v[$order_info[$k]]) ? $v[$order_info[$k]] : '';
            }
            $order_info['add_time'] = (isset($order_info['add_time']) && !empty($order_info['add_time'])) ? date('Y-m-d H:i:s', $order_info['add_time']) : '';
            $order_info['start_time'] = (isset($order_info['start_time']) && !empty($order_info['start_time'])) ? date('Y-m-d H:i:s', $order_info['start_time']) : '';
            $order_info['end_time'] = (isset($order_info['end_time']) && !empty($order_info['end_time'])) ? date('Y-m-d H:i:s', $order_info['end_time']) : '';
            $order_info['settlement_time'] = (isset($order_info['settlement_time']) && !empty($order_info['settlement_time'])) ? date('Y-m-d H:i:s', $order_info['settlement_time']) : '';
            $order_info['pdr_payment_time'] = (isset($order_info['pdr_payment_time']) && !empty($order_info['pdr_payment_time'])) ? date('Y-m-d H:i:s', $order_info['pdr_payment_time']) : '';
        }


        $filter = $this->request->get(array('apply_user_name', 'order_sn', 'apply_admin_name', 'apply_audit_admin_name', 'apply_state', 'apply_add_time', 'apply_audit_time', 'cooperator_id', 'page'));
        $this->assign('data', $data);
        $this->assign('order_apply_info', $order_apply_info);
        $this->assign('order_info', $order_info);
        $this->assign('return_action', $this->url->link('user/recharge'));
        $this->assign('action', $this->cur_url . '&' . http_build_query($filter) . '&apply_id=' . $this->request->get['apply_id']);
        $this->assign('error', $this->error);
        $this->response->setOutput($this->load->view('user/order_apply_form', $this->output));
    }

    /**
     * 退款操作
     */
    private function cash($apply_id) {
        $condition = array(
            'apply_id' => $apply_id
        );
        $order_apply_info = $this->sys_model_orders->getOrderApplyInfo($condition);
        $condition = array(
            'order_sn' =>  $order_apply_info['order_sn'],
        );
        $order_info = $this->sys_model_orders->getOrdersInfo($condition);
        // 是否有充值记录
        if (empty($order_info)) {
            $this->error['warning'] = '订单不存在';
            return false;
        }
        // 退回的金额不能超过实际支付金额
        if ($order_apply_info['apply_cash_amount'] > ($order_info['pay_amount'] - $order_info['refund_amount'])) {
            $this->error['warning'] = '退还金额不能超过实际支付金额（扣除已退金额）' ;
            return false;
        }
        $this->sys_model_orders->begin();
        // 修改订单实际支付金额
        $new_refund_amount = $order_info['refund_amount'] + $order_apply_info['apply_cash_amount'];
        $condition = array(
            'order_sn' =>  $order_apply_info['order_sn'],
        );
        $data = array('refund_amount' => $new_refund_amount);
        $change_order_result = $this->sys_model_orders->updateOrders($condition, $data);
        // 修改用户余额 欠费 还有骑行状态
        $this->load->library('sys_model/user', true);
        $condition = array(
            'user_id' =>  $order_info['user_id'],
        );
        $user_info = $this->sys_model_user->getUserInfo($condition);
        if (empty($user_info)) {
            $this->error['warning'] = '用户不存在';
            return false;
        }
        $new_available_deposit = $user_info['available_deposit'] + $order_apply_info['apply_cash_amount'];
        $data = array(
            'available_deposit' => $new_available_deposit
        );
        if($user_info['freeze_recharge'] > 0){
            $new_freeze_recharge = $user_info['freeze_recharge'] - $order_apply_info['apply_cash_amount'];
            $new_freeze_recharge < 0 ? $data['freeze_recharge'] = 0 : $data['freeze_recharge'] = $new_freeze_recharge;
            if(0 === $data['freeze_recharge'] && 0 == $user_info['is_freeze']){
                $data['available_state'] = 1;
            }
        }
        $change_user_result = $this->sys_model_user->updateUser($condition, $data);
        if($change_order_result && $change_user_result){
            $this->sys_model_orders->commit();
            return true;
        }else{
            $this->sys_model_orders->rollback();
            $this->error['warning'] = '修改出错';
            return false;
        }
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('用户名称');
        $this->setDataColumn('城市');
        $this->setDataColumn('订单sn');
        $this->setDataColumn('金额');
        $this->setDataColumn('申请管理员');
        $this->setDataColumn('申请状态');
        $this->setDataColumn('申请理由');
        $this->setDataColumn('申请时间');
        $this->setDataColumn('审核管理员');
        $this->setDataColumn('审核时间');
        $this->setDataColumn('审核结果');
        return $this->data_columns;
    }
}