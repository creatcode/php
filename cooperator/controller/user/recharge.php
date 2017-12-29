<?php
class ControllerUserRecharge extends Controller {
    private $cooperator_id = null;
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');

        // 加载bicycle Model
        $this->load->library('sys_model/deposit', true);
    }

    /**
     * 充值记录列表
     */
    public function index() {
        $filter = $this->request->get(array('filter_type', 'pdr_sn', 'mobile', 'pdr_amount', 'pdr_payment_state', 'pdr_admin', 'pdr_add_time'));

        $condition = array(
            'pdr_type' => 0,
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = array('like', "%{$filter['pdr_sn']}%");
        }
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (is_numeric($filter['pdr_amount'])) {
            $condition['pdr_amount'] = (float)$filter['pdr_amount'];
        }
        if (is_numeric($filter['pdr_payment_state'])) {
            $condition['pdr_payment_state'] = (int)$filter['pdr_payment_state'];
        }
        if (!empty($filter['pdr_admin'])) {
            $condition['pdr_admin'] = array('like', "%{$filter['pdr_admin']}%");
        }
        if (!empty($filter['pdr_add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['pdr_add_time']);
            $condition['pdr_add_time'] = array(
                array('egt', strtotime($pdr_add_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
            );
        }

        $filter_types = array(
            'pdr_sn' => '订单号',
            'mobile' => '手机号',
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

        $order = 'pdr_add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_deposit->getRechargeList($condition, '*', $order, $limit);
        $total = $this->sys_model_deposit->getRechargeCount($condition);

        $recharge_type = get_recharge_type();
        $payment_state = get_payment_state();

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['pdr_payment_state'] = isset($payment_state[$item['pdr_payment_state']]) ? $payment_state[$item['pdr_payment_state']] : '';

                $item['pdr_add_time'] = date('Y-m-d H:i:s', $item['pdr_add_time']);
                $item['edit_action'] = $this->url->link('user/recharge/edit', 'pdr_id='.$item['pdr_id']);
                $item['delete_action'] = $this->url->link('user/recharge/delete', 'pdr_id='.$item['pdr_id']);
                $item['info_action'] = $this->url->link('user/recharge/info', 'pdr_id='.$item['pdr_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('payment_states', $payment_state);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/recharge/add'));
        $this->assign('chart_action', $this->url->link('user/recharge/chart'));

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
        $this->assign('recharge_free_list', $this->url->link('user/recharge/card_list'));

        $this->response->setOutput($this->load->view('user/recharge_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('订单号');
        $this->setDataColumn('手机号');
        $this->setDataColumn('充值金额');
        $this->setDataColumn('支付状态');
        $this->setDataColumn('管理员名称');
        $this->setDataColumn('下单时间');
        return $this->data_columns;
    }

    /**
     * 充值记录详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $pdr_id = $this->request->get('pdr_id');
        $condition = array(
            'pdr_id' => $pdr_id,
            'cooperator_id' => $this->cooperator_id
        );
        $info = $this->sys_model_deposit->getRechargeInfo($condition, '*');
        if (!empty($info)) {
            $info['pdr_payment_state_name'] = $info['pdr_payment_state'];
            $model = array(
                'pdr_payment_state_name' => get_payment_state(),
                'pdr_payment_type' => get_payment_type()
            );
            foreach ($model as $k => $v) {
                $info[$k] = isset($v[$info[$k]]) ? $v[$info[$k]] : '';
            }
            $info['pdr_add_time'] = (isset($info['pdr_add_time']) && !empty($info['pdr_add_time'])) ? date('Y-m-d H:i:s', $info['pdr_add_time']) : '';
            $info['pdr_payment_time'] = (isset($info['pdr_payment_time']) && !empty($info['pdr_payment_time'])) ? date('Y-m-d H:i:s', $info['pdr_payment_time']) : '';
        }

        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('user/recharge'));

        $this->response->setOutput($this->load->view('user/recharge_info', $this->output));
    }

    /**
     * 统计图表
     */
    public function chart() {
        $filter = $this->request->get(array('add_time'));

        $condition = array(
            'pdr_payment_state' => 1,
            'cooperator_id' => $this->cooperator_id
        );

        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdr_add_time[0]);
            $lastday  = bcadd(86399, strtotime($pdr_add_time[1]));
            $condition['pdr_add_time'] = array(
                array('egt', $firstday),
                array('elt', $lastday)
            );
        } else {
            $firstday = strtotime(date('Y-m-01'));
            $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
            $condition['pdr_add_time'] = array(
                array('egt', $firstday),
                array('elt', $lastday)
            );
        }
        // 初始化订单统计数据
        $balanceDailyAmount = array();
        while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $balanceDailyAmount[$tempDay] = 0;
            $firstday = strtotime('+1 day', $firstday);
        }

        $field = '*';
        $order = 'pdr_add_time DESC';
        $result = $this->sys_model_deposit->getRechargeList($condition, $field, $order);
        if (is_array($result) && !empty($result)) {
            foreach ($result as $item) {
                $tempDay = date('Y-m-d', $item['pdr_add_time']);
                if ($item['pdr_type'] == 0) {
                    // 余额充值
                    $balanceDailyAmount[$tempDay] += $item['pdr_amount'];
                }
            }
        }

        $balanceOrderData = array();
        $balanceOrderTotal = 0;
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

        $this->assign('filter', $filter);
        $this->assign('balanceOrderData', $balanceOrderData);
        $this->assign('balanceOrderTotal', $balanceOrderTotal);
        $this->assign('action', $this->cur_url);
        $this->assign('index_action', $this->url->link('user/recharge'));
        $this->assign('recharge_free_list', $this->url->link('user/recharge/card_list'));

        $this->response->setOutput($this->load->view('user/recharge_chart', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('filter_type', 'pdr_sn', 'mobile', 'pdr_amount', 'pdr_payment_state', 'pdr_admin', 'pdr_add_time'));

        $condition = array(
            'pdr_type' => 0,
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = array('like', "%{$filter['pdr_sn']}%");
        }
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (is_numeric($filter['pdr_amount'])) {
            $condition['pdr_amount'] = (float)$filter['pdr_amount'];
        }
        if (is_numeric($filter['pdr_payment_state'])) {
            $condition['pdr_payment_state'] = (int)$filter['pdr_payment_state'];
        }
        if (!empty($filter['pdr_admin'])) {
            $condition['pdr_admin'] = array('like', "%{$filter['pdr_admin']}%");
        }
        if (!empty($filter['pdr_add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['pdr_add_time']);
            $condition['pdr_add_time'] = array(
                array('egt', strtotime($pdr_add_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
            );
        }
        $order = 'pdr_add_time DESC';
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
                    'pdr_amount' => $v['pdr_amount'],
                    'pdr_payment_state' => $payment_state[$v['pdr_payment_state']],
                    'pdr_admin' => $v['pdr_admin'],
                    'pdr_add_time' => date("Y-m-d H:i:s",$v['add_time']),
                );
            }
        }

        $data = array(
            'title' => '充值记录列表',
            'header' => array(
                'pdr_sn' => '订单号',
                'pdr_user_name' => '手机号',
                'pdr_amount' => '充值金额',
                'pdr_payment_state' => '支付状态',
                'pdr_admin' => '管理员名称',
                'pdr_add_time' => '下单时间',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    public function card_list(){
        $filter = $this->request->get(array('filter_type', 'pdr_sn', 'mobile', 'pdr_amount', 'pdr_payment_state', 'pdr_admin', 'pdr_add_time'));

        $condition = array(
            'pdr_type' => 2,
            'cooperator_id' => $this->cooperator_id
        );
        if (!empty($filter['pdr_sn'])) {
            $condition['pdr_sn'] = array('like', "%{$filter['pdr_sn']}%");
        }
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (is_numeric($filter['pdr_amount'])) {
            $condition['pdr_amount'] = (float)$filter['pdr_amount'];
        }
        if (is_numeric($filter['pdr_payment_state'])) {
            $condition['pdr_payment_state'] = (int)$filter['pdr_payment_state'];
        }
        if (!empty($filter['pdr_admin'])) {
            $condition['pdr_admin'] = array('like', "%{$filter['pdr_admin']}%");
        }
        if (!empty($filter['pdr_add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['pdr_add_time']);
            $condition['pdr_add_time'] = array(
                array('egt', strtotime($pdr_add_time[0])),
                array('elt', bcadd(86399, strtotime($pdr_add_time[1])))
            );
        }

        $filter_types = array(
            'pdr_sn' => '订单号',
            'mobile' => '手机号',
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

        $order = 'pdr_add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_deposit->getRechargeList($condition, '*', $order, $limit);
        $total = $this->sys_model_deposit->getRechargeCount($condition);

        $recharge_type = get_recharge_type();
        $payment_state = get_payment_state();

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['pdr_payment_state'] = isset($payment_state[$item['pdr_payment_state']]) ? $payment_state[$item['pdr_payment_state']] : '';

                $item['pdr_add_time'] = date('Y-m-d H:i:s', $item['pdr_add_time']);
                $item['edit_action'] = $this->url->link('user/recharge/edit', 'pdr_id='.$item['pdr_id']);
                $item['delete_action'] = $this->url->link('user/recharge/delete', 'pdr_id='.$item['pdr_id']);
                $item['info_action'] = $this->url->link('user/recharge/info', 'pdr_id='.$item['pdr_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('payment_states', $payment_state);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/recharge/add'));
        $this->assign('chart_action', $this->url->link('user/recharge/chart'));
        $this->assign('recharge_free_list', $this->url->link('user/recharge/card_list'));

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
        $this->assign('export_action', $this->url->link('user/recharge/export'));

        $this->response->setOutput($this->load->view('user/recharge_card_list', $this->output));
    }
}