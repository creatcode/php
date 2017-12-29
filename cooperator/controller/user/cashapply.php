<?php

/**
 * 申请退款
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
        $filter = $this->request->get(array('pdc_sn', 'pdc_user_name', 'pdr_sn', 'pdc_amount', 'pdc_payment_code', 'pdc_add_time', 'pdc_payment_state'));

        $condition = array();
        if (!empty($filter['pdc_sn'])) {
            $condition['pdc_sn'] = array('like', "%{$filter['pdc_sn']}%");
        }
        if (!empty($filter['pdc_user_name'])) {
            $condition['pdc_user_name'] = array('like', "%{$filter['pdc_user_name']}%");
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
        if (!empty($filter['pdc_add_time'])) {
            if (strpos($filter['pdc_add_time'], '至')) {
                $pdc_add_time = explode(' 至 ', $filter['pdc_add_time']);
                $condition['pdc_add_time'] = array(
                    array('gt', stragreetime($pdc_add_time[0])),
                    array('lt', bcadd(86399, strtotime($pdc_add_time[1])))
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

        $order = 'pdc_add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_deposit->getDepositCashList($condition, $limit, $order);
        $total = $this->sys_model_deposit->getDepositCashTotal($condition);

        foreach ($result as &$item) {
            $item['pdc_payment_state_text'] = $item['pdc_payment_state'] == 1 ? '<span style="color: #00a65a">已退款</span>' : '<span style="color: #dd4b39">未退款</span>';
            $item['info_action'] = $this->url->link('user/cashapply/edit', 'pdc_id='.$item['pdc_id']);
            $item['pdc_add_time'] = date('Y-m-d H:i:s', $item['pdc_add_time']);
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('payment_state', array('已退款', '未退款'));
        $this->assign('payment_states', array(array('text' => '未退款', 'value' => '0'), array('text' => '已退款', 'value' => '1')));

        $payment_types = array(
            array('code' => 'alipay', 'text' => '支付宝'),
            array('code' => 'wxpay', 'text' => '微信')
        );

        $this->assign('payment_types', $payment_types);

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
                $this->cashSubmit($cash_info);
            } elseif ($this->request->post['type'] == 'disagree') {
                $this->cashCancel($cash_info);
            }
            $filter = $this->request->get(array());
            $this->load->controller('common/base/redirect', $this->url->link('user/cashApply', $filter, true));
        }
        $this->assign('title', '提现审核');
        $this->getForm();
    }

    /**
     * 导出
     */
    public function export() {
        $filter = $this->request->post(array('pdc_sn', 'pdc_user_name', 'pdr_sn', 'pdc_amount', 'pdc_payment_code', 'pdc_add_time', 'pdc_payment_state'));

        $condition = array();
        if (!empty($filter['pdc_sn'])) {
            $condition['pdc_sn'] = array('like', "%{$filter['pdc_sn']}%");
        }
        if (!empty($filter['pdc_user_name'])) {
            $condition['pdc_user_name'] = array('like', "%{$filter['pdc_user_name']}%");
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
        if (!empty($filter['pdc_add_time'])) {
            if (strpos($filter['pdc_add_time'], '至')) {
                $pdc_add_time = explode(' 至 ', $filter['pdc_add_time']);
                $condition['pdc_add_time'] = array(
                    array('gt', stragreetime($pdc_add_time[0])),
                    array('lt', bcadd(86399, strtotime($pdc_add_time[1])))
                );
            }
        }
        if (is_numeric($filter['pdc_payment_state'])) {
            $condition['pdc_payment_state'] = $filter['pdc_payment_state'];
        }
        $order = 'pdc_add_time DESC';
        $limit = '';

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
                    'pdc_add_time' => date("Y-m-d H:i:s",$v['pdc_add_time']),
                    'pdc_payment_state' => $pdc_payment_state[$v['pdc_payment_state']],
                );
            }
        }

        $data = array(
            'title' => '充值记录列表',
            'header' => array(
                'pdc_sn' => '申请编号',
                'pdc_user_name' => '申请人',
                'pdr_sn' => '充值订单号',
                'pdc_amount' => '金额',
                'pdc_payment_name' => '支付方式',
                'pdc_add_time' => '申请时间',
                'pdc_payment_state' => '	提现支付状态',
            ),
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    private function cashCancel($pdc_info) {
        $result = $this->sys_model_deposit->cashCancel($pdc_info);
    }

    private function cashSubmit($pdc_info) {
        if ($pdc_info['pdc_payment_code'] == 'alipay') {
            //支付宝有密码退款
            $this->sys_model_deposit->aliPayRefund($pdc_info);
        } else {
            $ssl_cert_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/apiclient_cert.pem';
            $ssl_key_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/apiclient_key.pem';
            define('WX_SSLCERT_PATH', $ssl_cert_path);
            define('WX_SSLKEY_PATH', $ssl_key_path);
            $result = $this->sys_model_deposit->wxPayRefund($pdc_info);
            $filter = array();
            if ($result['state'] == true) {
                $this->load->controller('common/base/redirect', $this->url->link('user/cashapply', $filter, true));
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

        if ($cash_info['pdc_add_time']) {
            $cash_info['pdc_add_time'] = date('Y-m-d H:i:s', $cash_info['pdc_add_time']);
        }

        if ($cash_info['pdc_payment_time']) {
            $cash_info['pdc_payment_time'] = date('Y-m-d H:i:s', $cash_info['pdc_payment_time']);
        }
        if ($cash_info['pdc_payment_state']) {
            $cash_info['pdc_payment_state_text'] = '已付款';
        } else {
            $cash_info['pdc_payment_state_text'] = '未付款';
        }


        $this->assign('pdc_id', $condition['pdc_id']);
        $this->assign('data', $cash_info);
        $this->assign('action', $this->cur_url . '&pdc_id=' . $condition['pdc_id']);
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
        $this->setDataColumn('充值订单号');
        $this->setDataColumn('金额');
        $this->setDataColumn('支付方式');
        $this->setDataColumn('申请时间');
        $this->setDataColumn('提现支付状态');
        return $this->data_columns;
    }
}