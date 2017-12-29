<?php
class ControllerStartupLogin extends Controller {
    public function index() {
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : '';

        $ignore = array(
            'common/login',
            'common/forgotten',
            'common/reset',
            'wx/wx',
            'wx/wx/info',
            'qq/qq',
            'qq/qq/info',
            'payment/wechat',
            'payment/alipay',
            'payment/alipay/index',
            'system/test'
        );

        $this->load->library('logic/admin', true);

        if (!$this->logic_admin->isLogged() && !in_array($route, $ignore)) {
            return new Action('common/login');
        }


        if (isset($this->request->get['route'])) {
            $ignore = array(
                'common/login',
                'common/logout',
                'common/forgotten',
                'common/reset',
                'error/not_found',
                'error/permission',

                'wx/wx',
                'wx/wx/info',
                'qq/qq',
                'qq/qq/info',
                'payment/wxpay',
                'payment/alipay',
                'payment/alipay/index',
            );
            if (!in_array($route, $ignore) && !isset($this->session->data['token'])) {
                return new Action('common/login');
            }
        } else {
            if (!isset($this->session->data['token'])) {
                return new Action('common/login');
            }
        }
    }
}