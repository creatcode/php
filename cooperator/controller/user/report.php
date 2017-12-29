<?php

/**
 * 统计报表
 * Class ControllerUserReport
 */
class ControllerUserReport extends Controller {
    private $cur_url = null;
    private $cooperator_id = 0;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');
        // 加载bicycle Model
        $this->load->library('sys_model/orders', true);
        $this->load->library('sys_model/deposit', true);
    }

    /**
     * 月报表
     */
    public function index() {
        // 导出
        $operation = $this->request->get('operation');
        if ($operation == 'export') {
            $filter = $this->request->post(array('search_time'));
            if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                list($startMonth, $endMonth) = explode(' 至 ', $filter['search_time']);
                $startMonth = strtotime($startMonth);
                $endMonth = strtotime($endMonth);
                // 月份数量
                $monthTotal = $this->calculateMonths($startMonth, $endMonth) + 1;
            } else {    // 默认当前月份
                $startMonth = $endMonth = strtotime(date('Y-m'));
                // 月份数量
                $monthTotal = 1;
            }
            $titles = $monthData = array();
            if (isset($this->request->get['page'])) {
                $page = (int)$this->request->get['page'];
            } else {
                $page = 1;
            }
            if ($monthTotal > 1) { // 查询大于1个月
                $title = date('Y年m月', $startMonth) . '-' . date('Y年m月', $endMonth) . '报表';
                for ($i = 0; $i < $monthTotal; $i++) {
                    $curMonthTimeHorizon = strtotime(date('Y-m-01', $startMonth) . '+' . $i . ' months');
                    $titles[] = date('Y年n月累计金额', $curMonthTimeHorizon);
                    $thisMonthTimeHorizon = array(
                        array('egt', $curMonthTimeHorizon),
                        array('elt', bcadd(86399, strtotime(date('Y-m-t', $curMonthTimeHorizon))))
                    );
                    $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
                }

            } else {      // 查询1个月的数据
                $title = date('Y年m月', $startMonth) . '报表';
                $thisMonthTimeHorizon = array(
                    array('egt', strtotime(date('Y-m-01', $startMonth))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t', $startMonth))))
                );
                $titles[] = date('Y年n月累计金额', $startMonth);
                $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
                // 本年
                $thisYearTimeHorizon = array(
                    array('egt', strtotime(date('Y-01-01', $startMonth))),
                    array('elt', bcadd(86399, strtotime(date('Y-12-31', $startMonth))))
                );
                $titles[] = '本年累计金额';
                $monthData[] = $this->getFinanceData($thisYearTimeHorizon, $this->cooperator_id);
            }

            $rowNames = array(
                'total' => '现金收入',
                'order_amount' => '消费金额',
                'order_refund' => '消费退回',
                'coupon_num' => '优惠劵',
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($monthData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }
            array_unshift($titles, '项目');

            $data = array(
                'title' => $title,
                'header' => $titles,
                'list' => $list
            );
            $this->load->controller('common/base/exportExcel', $data);
        } else {
            /** 页面显示 **/
            $filter = $this->request->get(array('search_time'));
            if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                list($startMonth, $endMonth) = explode(' 至 ', $filter['search_time']);
                $startMonth = strtotime($startMonth);
                $endMonth = strtotime($endMonth);
                // 月份数量
                $monthTotal = $this->calculateMonths($startMonth, $endMonth) + 1;
            } else {    // 默认当前月份
                $startMonth = $endMonth = strtotime(date('Y-m'));
                // 月份数量
                $monthTotal = 1;
            }
            $titles = $monthData = array();
            if (isset($this->request->get['page'])) {
                $page = (int)$this->request->get['page'];
            } else {
                $page = 1;
            }
            $total = $monthTotal;
            $rows = 3;
            $offset = ($page - 1) * $rows;
            $max = $total;
            if ($monthTotal > 1) { // 查询大于1个月
                if ($rows + $offset < $total) {
                    $max = $rows + $offset;
                }
                for ($i = $offset; $i < $max; $i++) {
                    $curMonthTimeHorizon = strtotime(date('Y-m-01', $startMonth) . '+' . $i . ' months');
                    $titles[] = date('Y年n月累计金额', $curMonthTimeHorizon);
                    $thisMonthTimeHorizon = array(
                        array('egt', $curMonthTimeHorizon),
                        array('elt', bcadd(86399, strtotime(date('Y-m-t', $curMonthTimeHorizon))))
                    );
                    $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
                }

            } else {      // 查询1个月的数据
                $thisMonthTimeHorizon = array(
                    array('egt', strtotime(date('Y-m-01', $startMonth))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-t', $startMonth))))
                );
                $titles[] = date('Y年n月累计金额', $startMonth);
                $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
                // 本年
                $thisYearTimeHorizon = array(
                    array('egt', strtotime(date('Y-01-01', $startMonth))),
                    array('elt', bcadd(86399, strtotime(date('Y-12-31', $startMonth))))
                );
                $titles[] = '本年累计金额';
                $monthData[] = $this->getFinanceData($thisYearTimeHorizon, $this->cooperator_id);
            }

            $rowNames = array(
                'total' => '现金收入',
                'order_amount' => '消费金额',
                'order_refund' => '消费退回',
                'coupon_num' => '优惠劵',
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($monthData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }

            // 分页
            $pagination = new Pagination();
            $pagination->total = $total;
            $pagination->page = $page;
            $pagination->page_size = $rows;
            $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
            $pagination = $pagination->render();
            $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

            $this->assign('list', $list);
            $this->assign('pagination', $pagination);
            $this->assign('results', $results);
            $this->assign('titles', $titles);
            $this->assign('filter', $filter);
            $this->assign('action', $this->cur_url);
            $this->assign('day_report_action', $this->url->link('user/report/day'));
            $this->assign('summary_report_action', $this->url->link('user/report/summary'));
            $this->assign('export_action', htmlspecialchars_decode($this->url->link('user/report', 'operation=export')));

            $this->response->setOutput($this->load->view('user/report_list', $this->output));
        }
    }

    /**
     * 日报表
     */
    public function day() {
        // 导出
        $operation = $this->request->get('operation');
        if ($operation == 'export') {
            $filter = $this->request->post(array('search_time'));
            if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                $startDay = strtotime($startDay);
                $endDay = strtotime($endDay);
                // 天数
                $dayTotal = $this->calculateDays($startDay, $endDay) + 1;
            } else {    // 默认当天
                $startDay = $endDay = strtotime(date('Y-m-d'));
                // 天数
                $dayTotal = 1;
            }
            $titles = $dayData = array();
            if (isset($this->request->get['page'])) {
                $page = (int)$this->request->get['page'];
            } else {
                $page = 1;
            }
            if ($dayTotal > 1) { // 查询大于1天
                $title = date('Y年n月j日', $startDay) . '-' . date('Y年n月j日', $endDay) . '报表';
                for ($i = 0; $i < $dayTotal; $i++) {
                    $curMonthTimeHorizon = strtotime(date('Y-m-d', $startDay) . '+' . $i . ' days');
                    $titles[] = date('Y年n月j日累计金额', $curMonthTimeHorizon);
                    $thisMonthTimeHorizon = array(
                        array('egt', $curMonthTimeHorizon),
                        array('elt', bcadd(86399, $curMonthTimeHorizon))
                    );
                    $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
                }

            } else {      // 查询1天的数据
                $title = date('Y年n月j日', $startDay) . '报表';
                $thisMonthTimeHorizon = array(
                    array('egt', strtotime(date('Y-m-d', $startDay))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                );
                $titles[] = date('Y年n月j日累计金额', $startDay);
                $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
            }

            $rowNames = array(
                'total' => '现金收入',
                'order_amount' => '消费金额',
                'order_refund' => '消费退回',
                'coupon_num' => '优惠劵',
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($dayData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }
            array_unshift($titles, '项目');

            $data = array(
                'title' => $title,
                'header' => $titles,
                'list' => $list
            );
            $this->load->controller('common/base/exportExcel', $data);
        } else {
            /** 页面显示 **/
            $filter = $this->request->get(array('search_time'));
            if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                $startDay = strtotime($startDay);
                $endDay = strtotime($endDay);
                // 天数
                $dayTotal = $this->calculateDays($startDay, $endDay) + 1;
            } else {    // 默认当天
                $startDay = $endDay = strtotime(date('Y-m-d'));
                // 天数
                $dayTotal = 1;
            }
            $titles = $dayData = array();
            if (isset($this->request->get['page'])) {
                $page = (int)$this->request->get['page'];
            } else {
                $page = 1;
            }
            $total = $dayTotal;
            $rows = 3;
            $offset = ($page - 1) * $rows;
            $max = $total;
            if ($dayTotal > 1) { // 查询大于1天
                if ($rows + $offset < $total) {
                    $max = $rows + $offset;
                }
                for ($i = $offset; $i < $max; $i++) {
                    $curMonthTimeHorizon = strtotime(date('Y-m-d', $startDay) . '+' . $i . ' days');
                    $titles[] = date('Y年n月j日累计金额', $curMonthTimeHorizon);
                    $thisMonthTimeHorizon = array(
                        array('egt', $curMonthTimeHorizon),
                        array('elt', bcadd(86399, $curMonthTimeHorizon))
                    );
                    $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
                }

            } else {      // 查询1天的数据
                $thisMonthTimeHorizon = array(
                    array('egt', strtotime(date('Y-m-d', $startDay))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                );
                $titles[] = date('Y年n月j日累计金额', $startDay);
                $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
            }

            $rowNames = array(
                'total' => '现金收入',
                'order_amount' => '消费金额',
                'order_refund' => '消费退回',
                'coupon_num' => '优惠劵',
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($dayData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }

            // 分页
            $pagination = new Pagination();
            $pagination->total = $total;
            $pagination->page = $page;
            $pagination->page_size = $rows;
            $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
            $pagination = $pagination->render();
            $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

            $this->assign('list', $list);
            $this->assign('pagination', $pagination);
            $this->assign('results', $results);
            $this->assign('titles', $titles);
            $this->assign('filter', $filter);
            $this->assign('action', $this->cur_url);
            $this->assign('month_report_action', $this->url->link('user/report'));
            $this->assign('summary_report_action', $this->url->link('user/report/summary'));
            $this->assign('export_action', htmlspecialchars_decode($this->url->link('user/report/day', 'operation=export')));

            $this->response->setOutput($this->load->view('user/report_day_list', $this->output));
        }
    }

    /**
     * 总报表
     */
    public function summary() {
        // 导出
        $operation = $this->request->get('operation');
        if ($operation == 'export') {
            $filter = $this->request->post(array('search_time'));
            $titles = $dayData = array();
            if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                $startDay = strtotime($startDay);
                $endDay = strtotime($endDay);
                $thisMonthTimeHorizon = array(
                    array('egt', $startDay),
                    array('elt', bcadd(86399, $endDay))
                );
                $title = sprintf('汇总报表（%s-%s）', date('Y年n月j日', $startDay), date('Y年n月j日', $endDay));
            } else {
                $title = '汇总报表(全部订单)';
                $thisMonthTimeHorizon = array();
            }
            $titles[] = '累计金额';
            $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);


            $rowNames = array(
                'total' => '现金收入',
                'order_amount' => '消费金额',
                'order_refund' => '消费退回',
                'coupon_num' => '优惠劵',
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($dayData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }
            array_unshift($titles, '项目');

            $data = array(
                'title' => $title,
                'header' => $titles,
                'list' => $list
            );
            $this->load->controller('common/base/exportExcel', $data);
        } else {
            /** 页面显示 **/
            $filter = $this->request->get(array('search_time'));
            $titles = $dayData = array();
            if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                $startDay = strtotime($startDay);
                $endDay = strtotime($endDay);
                $thisMonthTimeHorizon = array(
                    array('egt', $startDay),
                    array('elt', bcadd(86399, $endDay))
                );
                $titles[] = sprintf('累计金额（%s-%s）', date('Y年n月j日', $startDay), date('Y年n月j日', $endDay));
                $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
            } else {
                $titles[] = '全部订单';
                $thisMonthTimeHorizon = array();
                $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $this->cooperator_id);
            }


            $rowNames = array(
                'total' => '现金收入',
                'deposit_net' => '押金净值',
                'deposit_recharge' => '押金充值',
                'deposit_refund' => '退回押金',
                'balance_net' => '余额净值',
                'balance_recharge' => '余额充值',
                'balance_refund' => '余额退回',
                'order_amount' => '消费金额',
                'order_refund' => '消费退回',
                'coupon_num' => '优惠劵',
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($dayData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }

            $this->assign('list', $list);
            $this->assign('titles', $titles);
            $this->assign('filter', $filter);
            $this->assign('action', $this->cur_url);
            $this->assign('month_report_action', $this->url->link('user/report'));
            $this->assign('day_report_action', $this->url->link('user/report/day'));
            $this->assign('export_action', htmlspecialchars_decode($this->url->link('user/report/summary', 'operation=export')));

            $this->response->setOutput($this->load->view('user/report_summary_list', $this->output));
        }
    }

    /**
     * 获取财务数据
     */
    private function getFinanceData($timeHorizon = array(), $cooperator_id = 0) {
        // 初始化数据
        $data = array(
            'total' => 0,
            'deposit_net' => 0,
            'deposit_recharge' => 0,
            'deposit_refund' => 0,
            'balance_net' => 0,
            'balance_recharge' => 0,
            'balance_refund' => 0,
            'order_amount' => 0,
            'order_refund' => 0,
            'coupon_num' => 0,
        );

        if (empty($cooperator_id)) {
            ////////////////////////////////////////// 押金 //////////////////////////////////////////
            // 押金充值
            $fields = 'SUM(pdr_amount) as amount';
            $condition = array(
                'pdr_type' => '1',
                'pdr_payment_state' => array('in', array(1, -1)),
            );
            if (!empty($timeHorizon)) {
                $condition['pdr_payment_time'] = $timeHorizon;
            }
            $result = $this->sys_model_deposit->getRechargeInfo($condition, $fields);
            if (isset($result['amount'])) {
                $data['deposit_recharge'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);

            // 退回押金
            $fields = 'SUM(pdc_amount) as amount';
            $condition = array(
                'pdc_payment_state' => 1,
                'pdc_type' => 1,
            );
            if (!empty($timeHorizon)) {
                $condition['pdc_payment_time'] = $timeHorizon;
            }
            $result = $this->sys_model_deposit->getDepositCashInfo($condition, $fields);
            if (isset($result['amount'])) {
                $data['deposit_refund'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);

            // 押金净值
            $data['deposit_net'] = $data['deposit_recharge'] - $data['deposit_refund'];

            // 余额充值
            $fields = 'SUM(pdr_amount) as amount';
            $condition = array(
                'pdr_type' => '0',
                'pdr_payment_state' => array('in', array(1, -1)),
            );
            if (!empty($timeHorizon)) {
                $condition['pdr_payment_time'] = $timeHorizon;
            }
            $result = $this->sys_model_deposit->getRechargeInfo($condition, $fields);
            if (isset($result['amount'])) {
                $data['balance_recharge'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);

            // 余额退回
            $fields = 'SUM(pdc_amount) as amount';
            $condition = array(
                'pdc_payment_state' => 1,
                'pdc_type' => 0,
            );
            if (!empty($timeHorizon)) {
                $condition['pdc_payment_time'] = $timeHorizon;
            }
            $result = $this->sys_model_deposit->getDepositCashInfo($condition, $fields);
            if (isset($result['amount'])) {
                $data['balance_refund'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);
        }

        // 消费金额
        $fields = 'SUM(pay_amount) as amount';
        $condition = array(
            'order_state' => 2,
            'cooperator_id' => $cooperator_id
        );
        if (!empty($timeHorizon)) {
            $condition['settlement_time'] = $timeHorizon;
        }
        $result = $this->sys_model_orders->getOrdersInfo($condition, $fields);
        if (isset($result['amount'])) {
            $data['order_amount'] = (float)$result['amount'];
        }

        // 消费退回
        $fields = 'SUM(apply_cash_amount) as amount';
        $condition = array(
            'apply_state' => 1,
            'cooperator_id' => $cooperator_id
        );
        if (!empty($timeHorizon)) {
            $condition['apply_audit_time'] = $timeHorizon;
        }
        $result = $this->sys_model_orders->getOrderApplyInfo($condition, $fields, array('orders'=>'orders.order_sn=orders_modify_apply.order_sn'));
        if (isset($result['amount'])) {
            // 现金收入 = 消费金额
            $data['order_refund'] = (float)$result['amount'];
        }

        // 现金收入 = 消费金额 - 退回金额
        $data['total'] = $data['order_amount'] - $data['order_refund'];

        unset($fields);
        unset($condition);
        unset($result);

        if (empty($cooperator_id)) {
            // 余额净值 = 余额充值 - 余额退回 - 消费金额 + 消费退回
            $data['balance_net'] = $data['balance_recharge'] - $data['balance_refund'] - $data['order_amount'] - $data['order_refund'];
        }

        return $data;
    }

    /**
     * 计算连俩个日期相差月份
     * @param $startTime
     * @param $endTime
     * @return bool|string
     */
    private function calculateMonths($startTime, $endTime) {
        $startYear = date('Y', $startTime);
        $startMonth = date('m', $startTime);
        $endYear = date('Y', $endTime);
        $endMonth = date('m', $endTime);
        return ($endYear * 12 + $endMonth) - ($startYear * 12 + $startMonth);
    }
    /**
     * 计算连俩个日期相差月份
     * @param $startTime
     * @param $endTime
     * @return bool|string
     */
    private function calculateDays($startTime, $endTime) {
        $startDay = strtotime(date('Y-m-d', $startTime));
        $endDay = strtotime(date('Y-m-d', $endTime));
        return ($endDay - $startDay) / 86400;
    }
}
