<?php
class ControllerCommonHeader extends Controller {
    public function index() {
        if ($this->request->server['HTTPS']) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }
        $now = time();

        // 首页
        $this->assign('home_action', $this->url->link('admin/index'));
        // 运营设置
        $this->assign('setting_action', $this->url->link('system/operator'));
        // 用户中心
        $this->assign('user_action', $this->url->link('me/information'));
        $this->assign('message_action', $this->url->link('message/message'));

        $this->assign('static', HTTP_IMAGE);
        $this->assign('username', $this->logic_admin->getadmin_name());
        $this->assign('nickname', $this->logic_admin->getParam('nickname'));
        $this->assign('app_name', $this->config->get('config_app_name'));
        $this->assign('title', $this->document->getTitle());
        $this->assign('description', $this->document->getDescription());
        $this->assign('keywords', $this->document->getKeywords());
        $this->assign('links', $this->document->getLinks());
        $this->assign('styles', $this->document->getStyles());
        $this->assign('scripts', $this->document->getScripts());
        $this->assign('lang', $this->language->get('code'));
        $this->assign('direction', $this->language->get('direction'));
        $this->assign('logout_url', $this->url->link('common/logout'));
        $this->assign('information', $this->url->link('me/information'));

        $this->assign('violation_action', $this->url->link('operation/violation', 'method=json'));
        $this->assign('fault_action', $this->url->link('operation/fault', 'method=json'));
        $this->assign('other_action', $this->url->link('operation/feedback', 'method=json'));

        // 菜单
        $this->assign('menu', $this->url->link('common/menu'));

        // 菜单
        $this->assign('menu', $this->load->controller('common/menu'));

        $this->assign('http_server', $data['base']);

        //最后登录时间
        $this->load->library('sys_model/admin',true);
        $admin = $this->sys_model_admin->getAdminInfoById($this->logic_admin->getId());
        $this->assign('login_time', date('Y-m-d H:i:s', $admin['login_time']));
        $this->assign('admin_name', $admin['admin_name']);

        $this->load->language('common/header');
        $languages = $this->language->all();
//        $this->assign('heading_title', $this->language->get('heading_title'));
        $this->assign($languages);
        return $this->load->view('common/header', $this->output);
    }
}