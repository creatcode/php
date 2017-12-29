<?php
class ControllerCommonFooter extends Controller {
    public function index() {
        $this->load->language('common/footer');

        $this->assign('static', HTTP_IMAGE);
        $this->assign('app_name', $this->config->get('app_name'));
        $languages = $this->language->all();
        $this->assign($languages);

        return $this->load->view('common/footer', $this->output);
    }
}