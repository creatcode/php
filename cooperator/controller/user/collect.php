<?php
class ControllerUserCollect extends Controller {
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载user Model
        $this->load->library('sys_model/admin_menu_collect', true);
    }

    public function index() {
        $collectList = $this->sys_model_admin_menu_collect->getCollectList('m.menu_name,m.menu_icon,m.menu_action,m.menu_id');
        $data = array();
        foreach($collectList as $k=>$v){
            $data[$k]['menu_action'] = $this->url->link($v['menu_action']);
            $data[$k]['menu_action_route'] = $v['menu_action'];
            $data[$k]['menu_icon'] = $v['menu_icon'];
            $data[$k]['menu_name'] = $v['menu_name'];
            $data[$k]['menu_id'] = $v['menu_id'];
        }
        $this->assign('collectList', $data);

        $this->response->showSuccessResult($data, '操作成功');
    }

    //AJAX收藏
    public function collect() {
        if (isset($this->request->get['menu_id'])) {
            $menu_id = (int)$this->request->get['menu_id'];
        }else{
            $this->response->showErrorResult();
        }

        if($collect = $this->sys_model_admin_menu_collect->getCollect(array('menu_id'=>$menu_id))){
            $collect['status'] == 1 ?  $status = 0 : $status = 1;
            $this->sys_model_admin_menu_collect->updateCollect(array('menu_id'=>$menu_id),array('status'=>$status,'time'=>time()));
        }else{
            $data = array();
            $data['user_id'] = $this->logic_admin->getId();
            $data['menu_id'] = $menu_id;
            $data['time'] = time();
            $this->sys_model_admin_menu_collect->addCollect($data);
        }

        $collect = $this->sys_model_admin_menu_collect->getCollect(array('menu_id'=>$menu_id));

        $this->response->showSuccessResult($collect, '操作成功');
    }
}