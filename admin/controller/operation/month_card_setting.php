<?php



class ControllerOperationMonthCardSetting extends Controller {
    private $curl_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        $this->curl_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';
        $this->load->library('sys_model/month_card_setting');
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

        $results = $this->sys_model_month_card_setting->getMonthCardSettingList($condition, $order, $limit);
        if (is_array($results) && !empty($results)) {
            foreach ($results as &$item) {
                $item['state'] = $item['state'] ? '启用' : '禁用';
                $item['edit_action'] = $this->url->link('operation/month_card_setting/edit', 'setting_id=' . $item['setting_id']);
            }
        }
        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $total = $this->sys_model_month_card_setting->getMonthCardSettingTotal($condition);

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $results);
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->url = $this->curl_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->response->setOutput($this->load->view('operation/month_card_setting_list', $this->output));
    }

    public function getDataColumns() {
        $this->setDataColumn('时长/月');
        $this->setDataColumn('抬头');
        $this->setDataColumn('售价');
        $this->setDataColumn('状态');
        return $this->data_columns;
    }

    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $setting_id = $this->request->get['setting_id'];
            $input = $this->request->post(array('time_length', 'title', 'amount', 'state'));
            $data =array(
                'time_length' => $input['time_length'],
                'title' => $input['title'],
                'amount' => $input['amount'],
                'state' => $input['state']
            );

            $where = array(
                'setting_id' => $setting_id,
            );

            $update = $this->sys_model_month_card_setting->updateMonthCardSetting($where, $data);
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
                $this->load->controller('common/base/redirect', $this->url->link('operation/month_card_setting', $filter, true));
            }
        }
        $this->assign('title', '编辑月卡');
        $this->getForm();
    }

    public function getForm() {
        $info = $this->request->post(array('time_length', 'amount', 'state'));
        $post = $this->request->get(array('setting_id'));
        if (isset($this->request->get['setting_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'setting_id' => $this->request->get['setting_id']
            );
            $info = $this->sys_model_month_card_setting->getMonthCardSetting($condition);
        }

        $this->assign('data', $info);
        $this->assign('return_action', $this->url->link('operation/month_card_setting'));
        $this->assign('action', $this->curl_url . '&setting_id=' . $post['setting_id']);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('operation/month_card_setting_form', $this->output));
    }

    private function validateForm() {
        $input = $this->request->post(array('title', 'amount', 'state'));
        if (!$input['amount']) {
            $this->error['error_amount'] = '售价不能为0';
        }
        if (!is_numeric($input['amount'])) {
            $this->error['error_amount'] = '请输入数字';
        }
        if ($input['amount'] < 0) {
            $this->error['error_amount'] = '售价不能小于0';
        }
        return !$this->error;
    }
}