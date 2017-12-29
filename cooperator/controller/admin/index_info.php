<?php
class ControllerAdminIndex extends Controller {
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载bicycle Model
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/lock', true);
    }

    /**
     * 首页
     */
    public function index() {
        $condition = array();

        $list = array();
        $result = $this->sys_model_bicycle->getBicycleLockMarker($condition);
        $list = is_array($result) && !empty($result) ? json_encode($result) : json_encode(null);

        $this->assign('list', $list);

        $this->response->setOutput($this->load->view('admin/index_info', $this->output));
    }
}