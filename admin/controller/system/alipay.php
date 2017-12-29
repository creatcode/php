<?php
class ControllerSystemAlipay extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载bicycle Model
        $this->load->library('logic/setting', true);
    }

    /**
     * 支付宝设置
     */
    public function index() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $data = $this->request->post(array('config_alipay_seller_id', 'config_alipay_key', 'config_alipay_partner'));

            $this->logic_setting->editSetting($data);

            $this->session->data['success'] = '编辑支付宝成功！';
            

            $this->load->controller('common/base/redirect', $this->url->link('system/alipay', '', true));
        }

        $this->assign('title', '支付宝设置');
        $this->getForm();
    }

    private function getForm() {
        $data = array();
        // 收款账号
        if (isset($this->request->post['config_alipay_seller_id'])) {
            $data['config_alipay_seller_id'] = $this->request->post['config_alipay_seller_id'];
        } else {
            $data['config_alipay_seller_id'] = $this->config->get('config_alipay_seller_id');
        }

        // 交易安全校验码
        if (isset($this->request->post['config_alipay_key'])) {
            $data['config_alipay_key'] = $this->request->post['config_alipay_key'];
        } else {
            $data['config_alipay_key'] = $this->config->get('config_alipay_key');
        }

        // 合作商户号
        if (isset($this->request->post['config_alipay_partner'])) {
            $data['config_alipay_partner'] = $this->request->post['config_alipay_partner'];
        } else {
            $data['config_alipay_partner'] = $this->config->get('config_alipay_partner');
        }

        $this->assign('data', $data);
        $this->assign('action', $this->cur_url);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('system/alipay_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('config_alipay_seller_id', 'config_alipay_key', 'config_alipay_partner'));

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
}