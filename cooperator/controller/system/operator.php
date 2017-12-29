<?php
class ControllerSystemOperator extends Controller {
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
     * 运营设置
     */
    public function index() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $data = $this->request->post(array('config_operator_deposit','config_wechat', 'config_phone', 'config_email','config_web'));

            $this->logic_setting->editSetting($data);

            $this->session->data['success'] = '设置成功！';

            $this->load->controller('common/base/redirect', $this->url->link('system/operator', '', true));
        }

        $this->assign('title', '运营设置');
        $this->getForm();
    }

    private function getForm() {
        $data = array();
        // 押金
        if (isset($this->request->post['config_operator_deposit'])) {
            $data['config_operator_deposit'] = $this->request->post['config_operator_deposit'];
        } else {
            $data['config_operator_deposit'] = $this->config->get('config_operator_deposit');
        }

        // 微信公众号
        if (isset($this->request->post['config_wechat'])) {
            $data['config_wechat'] = $this->request->post['config_wechat'];
        } else {
            $data['config_wechat'] = $this->config->get('config_wechat');
        }

        // 联系电话
        if (isset($this->request->post['config_phone'])) {
            $data['config_phone'] = $this->request->post['config_phone'];
        } else {
            $data['config_phone'] = $this->config->get('config_phone');
        }

        // 电子邮箱
        if (isset($this->request->post['config_email'])) {
            $data['config_email'] = $this->request->post['config_email'];
        } else {
            $data['config_email'] = $this->config->get('config_email');
        }

        // 官网
        if (isset($this->request->post['config_web'])) {
            $data['config_web'] = $this->request->post['config_web'];
        } else {
            $data['config_web'] = $this->config->get('config_web');
        }

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $this->assign('data', $data);
        $this->assign('action', $this->cur_url);
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('system/operator_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('config_operator_deposit','config_wechat', 'config_phone', 'config_email','config_web'));

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