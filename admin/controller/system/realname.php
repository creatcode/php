<?php
class ControllerSystemRealname extends Controller {
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
     * 实名验证参数设置
     */
    public function index() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $data = $this->request->post(array('config_yin_han_user_code', 'config_yin_han_des_key', 'config_yin_han_sys_code', 'config_yin_han_api_url'));

            $this->logic_setting->editSetting($data);

            $this->session->data['success'] = '编辑实名验证参数成功！';
            

            $this->load->controller('common/base/redirect', $this->url->link('system/realname', '', true));
        }

        $this->assign('title', '实名验证参数设置');
        $this->getForm();
    }

    private function getForm() {
        $data = array();
        // 商户编号
        if (isset($this->request->post['config_yin_han_user_code'])) {
            $data['config_yin_han_user_code'] = $this->request->post['config_yin_han_user_code'];
        } else {
            $data['config_yin_han_user_code'] = $this->config->get('config_yin_han_user_code');
        }

        // key
        if (isset($this->request->post['config_yin_han_des_key'])) {
            $data['config_yin_han_des_key'] = $this->request->post['config_yin_han_des_key'];
        } else {
            $data['config_yin_han_des_key'] = $this->config->get('config_yin_han_des_key');
        }

        // 应用编号
        if (isset($this->request->post['config_yin_han_sys_code'])) {
            $data['config_yin_han_sys_code'] = $this->request->post['config_yin_han_sys_code'];
        } else {
            $data['config_yin_han_sys_code'] = $this->config->get('config_yin_han_sys_code');
        }

        // 接口地址
        if (isset($this->request->post['config_yin_han_api_url'])) {
            $data['config_yin_han_api_url'] = $this->request->post['config_yin_han_api_url'];
        } else {
            $data['config_yin_han_api_url'] = $this->config->get('config_yin_han_api_url');
        }

        $this->assign('data', $data);
        $this->assign('action', $this->cur_url);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('system/realname_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('config_yin_han_user_code', 'config_yin_han_des_key', 'config_yin_han_sys_code', 'config_yin_han_api_url'));

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