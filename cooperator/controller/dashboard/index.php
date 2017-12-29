<?php
class ControllerDashboardIndex extends Controller {
    public function index() {
        $this->log->write();
        $this->assign('header', $this->load->controller('common/header'));
        $this->assign('footer', $this->load->controller('common/footer'));
    }
}