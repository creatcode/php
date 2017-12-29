<?php
class ControllerOperationRechargeOffer extends Controller {
    private $curl_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);
        //当前网址
        $this->curl_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';
        $this->load->library('sys_model/recharge_offer');
    }

    public function index() {
        $filter = array();
        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }
        $order = '';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $results = $this->sys_model_recharge_offer->getRechargeOfferList($condition, $order, $limit);
        if (is_array($results) && !empty($results)) {
            foreach ($results as &$item) {
                $item['start_time'] = !empty($item['start_time']) ? date('Y-m-d H:i:s', $item['start_time']) : '';
                $item['end_time'] = !empty($item['end_time']) ? date('Y-m-d H:i:s', $item['end_time']) : '';
                $item['state'] = $item['state'] ? '启用' : '禁用';
                $item['edit_action'] = $this->url->link('operation/recharge_offer/edit', 'recharge_id=' . $item['prc_id']);
            }
        }
        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $total = $this->sys_model_recharge_offer->getRechargeOfferTotal($condition);

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $results);
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->response->setOutput($this->load->view('operation/recharge_offer_list', $this->output));
    }

    private function getDataColumns() {
        $this->setDataColumn('充值金额');
        $this->setDataColumn('赠送金额');
        $this->setDataColumn('生效时间');
        $this->setDataColumn('失效时间');
        $this->setDataColumn('状态');
        return $this->data_columns;
    }

    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $recharge_id = $this->request->get['recharge_id'];
            $input = $this->request->post(array('present_amount', 'effect_time', 'state'));
            $data = array(
                'present_amount' => $input['present_amount'],
                'state' => $input['state']
            );
            $where = array(
                'prc_id' => $recharge_id
            );

            $effect_time = explode(' 至 ', $input['effect_time']);
            if (is_array($effect_time) && !empty($effect_time)) {
                $data['start_time'] = strtotime($effect_time[0] . ' 00:00:00');
                $data['end_time'] = strtotime($effect_time[1] . ' 23:59:59');
            }
            $update = $this->sys_model_recharge_offer->updateRechargeOffer($where, $data);
            $this->session->data['success'] = '编辑成功';
            if ($update) {
                $this->load->library('sys_model/admin_log', true);
                $data = array(
                    'admin_id' => $this->logic_admin->getId(),
                    'admin_name' => $this->logic_admin->getadmin_name(),
                    'log_description' => '编辑充值优惠：',
                    'log_ip' => $this->request->ip_address(),
                    'log_time' => date('Y-m-d H:i:s')
                );
                $this->sys_model_admin_log->addAdminLog($data);

                $filter = array();
                $this->load->controller('common/base/redirect', $this->url->link('operation/recharge_offer', $filter, true));
            }

        }
        $this->assign('title', '充值优惠编辑');
        $this->getForm();
    }

    private function getForm() {
        $info = $this->request->post(array('gift_amount', 'effect_time', 'state'));
        $post = $this->request->get(array('recharge_id'));
        if (isset($this->request->get['recharge_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'prc_id' => $this->request->get['recharge_id']
            );
            $info = $this->sys_model_recharge_offer->getRechargeOfferInfo($condition);
            if (!empty($info)) {
                $info['effect_time'] = date('Y-m-d', $info['start_time']) . ' 至 ' . date('Y-m-d', $info['end_time']);
            }
        }

        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('operation/recharge_offer'));
        $this->assign('action', $this->curl_url . '&recharge_id=' . $post['recharge_id']);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('operation/recharge_offer_form', $this->output));
    }

    private function validateForm() {
        return !$this->error;
    }
}