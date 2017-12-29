<?php
class ControllerSystemSms extends Controller {
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
     * 短信平台设置
     */
    public function index() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $data = $this->request->post(array('config_sms_account_sid', 'config_sms_account_token', 'config_sms_app_id', 'config_sms_temp_id'));

            $this->logic_setting->editSetting($data);

            $this->session->data['success'] = '编辑短信参数成功！';
            

            $this->load->controller('common/base/redirect', $this->url->link('system/sms', '', true));
        }

        $this->assign('title', '短信平台设置');
        $this->getForm();
    }

    private function getForm() {
        $data = array();
        // ACCOUNT SID
        if (isset($this->request->post['config_sms_account_sid'])) {
            $data['config_sms_account_sid'] = $this->request->post['config_sms_account_sid'];
        } else {
            $data['config_sms_account_sid'] = $this->config->get('config_sms_account_sid');
        }

        // AUTH TOKEN
        if (isset($this->request->post['config_sms_account_token'])) {
            $data['config_sms_account_token'] = $this->request->post['config_sms_account_token'];
        } else {
            $data['config_sms_account_token'] = $this->config->get('config_sms_account_token');
        }

        // AppID
        if (isset($this->request->post['config_sms_app_id'])) {
            $data['config_sms_app_id'] = $this->request->post['config_sms_app_id'];
        } else {
            $data['config_sms_app_id'] = $this->config->get('config_sms_app_id');
        }

        // 模板ID
        if (isset($this->request->post['config_sms_temp_id'])) {
            $data['config_sms_temp_id'] = $this->request->post['config_sms_temp_id'];
        } else {
            $data['config_sms_temp_id'] = $this->config->get('config_sms_temp_id');
        }

        $this->assign('data', $data);
        $this->assign('action', $this->cur_url);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('system/sms_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('config_sms_account_sid', 'config_sms_account_token', 'config_sms_app_id', 'config_sms_temp_id'));

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