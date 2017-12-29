<?php
class ControllerUserOrder extends Controller {
    private $cooperator_id = null;
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');

        // 加载bicycle Model
        $this->load->library('sys_model/orders', true);
    }

    /**
     * 消费记录列表
     */
    public function index() {
        $filter = $this->request->get(array('filter_type', 'order_sn', 'lock_sn', 'bicycle_sn', 'user_name', 'region_name', 'order_state', 'add_time', 'start_time', 'end_time', 'settlement_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
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
        if (!empty($filter['settlement_time'])) {
            $pdr_settlement_time = explode(' 至 ', $filter['settlement_time']);
            $condition['orders.settlement_time'] = array(
                array('egt', strtotime($pdr_settlement_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_settlement_time[1])))
            );
        }

        $filter_types = array(
            'order_sn' => '订单sn',
            'lock_sn' => '锁sn',
            'bicycle_sn' => '单车sn',
            'user_name' => '手机号',
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

        $result = $this->sys_model_orders->getOrdersList($condition, $order, $limit);
        $total = $this->sys_model_orders->getTotalOrders($condition);

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
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('order_state', $order_state);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/order'));
        $this->assign('chart_action', $this->url->link('user/order/chart'));
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

        $this->response->setOutput($this->load->view('user/order_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('订单sn');
        $this->setDataColumn('锁sn');
        $this->setDataColumn('单车sn');
        $this->setDataColumn('手机号');
        $this->setDataColumn('区域');
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
     * 统计图表
     */
    public function chart() {
        $this->load->library('sys_model/data_sum', true);
        $filter = $this->request->get(array('add_time'));
        $refundWhere = '`apply_state`=\'1\' AND cooperator_id = \'' . $this->cooperator_id . '\'';
        $orderWhere = '`order_state`=\'2\' AND cooperator_id = \'' . $this->cooperator_id . '\'';
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

        $this->assign('filter', $filter);
        $this->assign('orderData', $orderData);
        $this->assign('orderAmountTotal', $orderAmountTotal);
        $this->assign('refundAmountTotal', $refundAmountTotal);
        $this->assign('ordersTotal', $ordersTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/order'));
        $this->assign('chart_action', $this->url->link('user/order/chart'));
        $this->assign('order_free_chart', $this->url->link('user/order/free_chart'));
        $this->assign('order_free_list', $this->url->link('user/order/free_list'));

        $this->response->setOutput($this->load->view('user/order_chart', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('filter_type', 'order_sn', 'lock_sn', 'bicycle_sn', 'user_name', 'region_name', 'order_state', 'add_time', 'start_time', 'end_time', 'settlement_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
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
        if (!empty($filter['settlement_time'])) {
            $pdr_settlement_time = explode(' 至 ', $filter['settlement_time']);
            $condition['orders.settlement_time'] = array(
                array('egt', strtotime($pdr_settlement_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_settlement_time[1])))
            );
        }
        $order = 'orders.add_time DESC';
        $limit = '';
        $result = $this->sys_model_orders->getOrdersList($condition, $order, $limit);
        if (is_array($result) && !empty($result)) {
            $order_state = get_order_state();
            foreach ($result as &$v) {
                $v['order_state'] = $order_state[$v['order_state']];
                $v['start_time'] = !empty($v['start_time']) ? date('Y-m-d H:i:s', $v['start_time']) : '';
                $v['end_time'] = !empty($v['end_time']) ? date('Y-m-d H:i:s', $v['end_time']) : '';
                $v['add_time'] = !empty($v['add_time']) ? date('Y-m-d H:i:s', $v['add_time']) : '';
                $v['settlement_time'] = !empty($v['settlement_time']) ? date('Y-m-d H:i:s', $v['settlement_time']) : '';
            }
        }

        $data = array(
            'title' => '消费记录列表',
            'header' => array(
                'order_sn' => '订单sn',
                'lock_sn' => '锁sn',
                'bicycle_sn' => '单车sn',
                'user_name' => '手机号',
                'region_name' => '区域',
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
/*    public function free_chart(){
        $filter = $this->request->get(array('add_time'));
        $where = '`is_limit_free`=\'1\'';
        $where .= ' AND `order_state`=\'2\'';
        $where .= ' AND cooperator.cooperator_id = \''. $this->cooperator_id.'\'';
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
        $order  = '';
        $limit  = '';
        $field  = 'cooperator.cooperator_id,cooperator.cooperator_name,count(1) as num';
        $join = array(
            'cooperator' => 'cooperator.cooperator_id=orders.cooperator_id',
        );
        $group  = 'cooperator.cooperator_id';
        $free_list  = $this->sys_model_orders->getOrdersList($where, $order, $limit, $field, $join, $group);

        $data_chart     = array();
        $ordersTotal    = 0;
        // 初始化订单统计数据
        foreach ($free_list as $k => $v) {
            $data_chart[]   = array('label'=>$v['cooperator_name'],'value'=>$v['num']);
            $ordersTotal    += $v['num'];
        }
        $data_chart     = json_encode($data_chart);
        $this->assign('filter', $filter);
        $this->assign('data_chart', $data_chart);
        $this->assign('ordersTotal', $ordersTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/order'));
        $this->assign('chart_action', $this->url->link('user/order/chart'));
        $this->assign('order_free_chart', $this->url->link('user/order/free_chart'));
        $this->assign('order_free_list', $this->url->link('user/order/free_list'));

        $this->response->setOutput($this->load->view('user/order_free_chart', $this->output));
    }*/

    /**
     * [free_list 免费车列表]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-07-21T18:38:06+0800
     */
    public function free_list(){
        $filter = $this->request->get(array('filter_type', 'order_sn', 'lock_sn', 'bicycle_sn', 'user_name', 'region_name', 'order_state', 'add_time', 'start_time', 'end_time', 'settlement_time'));

        $condition = array(
            'cooperator_id' => $this->cooperator_id,
            'is_limit_free' => 1,
        );
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
        if (!empty($filter['settlement_time'])) {
            $pdr_settlement_time = explode(' 至 ', $filter['settlement_time']);
            $condition['orders.settlement_time'] = array(
                array('egt', strtotime($pdr_settlement_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_settlement_time[1])))
            );
        }

        $filter_types = array(
            'bicycle_sn' => '单车编号',
            'user_name' => '手机号',
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

        $result = $this->sys_model_orders->getOrdersList($condition, $order, $limit);
        $total = $this->sys_model_orders->getTotalOrders($condition);

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
        $this->assign('index_action', $this->url->link('user/order'));
        $this->assign('chart_action', $this->url->link('user/order/chart'));
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
