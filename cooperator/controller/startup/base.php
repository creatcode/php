<?php
class ControllerStartupBase extends Controller {
    private $cooperator_id = 0;

    public function index() {
        $cooperator_id = $this->logic_admin->getParam('cooperator_id');
        if (!empty($cooperator_id)) {
            $this->cooperator_id = $cooperator_id;
        }else{
            return false;
        }
        if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->load->common = array(
                'header' => '',
                'footer' => ''
            );
        } else {
            $this->load->common = array(
                'header' => $this->load->controller('common/header'),
                'footer' => $this->load->controller('common/footer')
            );
        }

        // 当前网址
        $cur_url = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('action_default');
        if(substr_count($cur_url,'/') > 1) $cur_url = substr($cur_url,0,strrpos($cur_url,'/'));

        $this->load->library('sys_model/menu', true);
        $menu_id = $this->sys_model_menu->getMenuInfo(array('menu_action'=>$cur_url))['menu_id'];

        //菜单头部显示
        $join = array(
            'region' => 'region.region_id=bicycle.region_id',
            'cooperator' => 'cooperator.cooperator_id=bicycle.cooperator_id'
        );
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/data_sum',true);

        $condition = array(
            'cooperator_id' => $this->cooperator_id,
        );

        $total = $this->sys_model_bicycle->getTotalBicycles(array('bicycle.cooperator_id'=>$cooperator_id),$join);
        $join['lock'] = '`lock`.`lock_sn`=`bicycle`.`lock_sn`';
        $total_real= $this->sys_model_bicycle->getTotalBicycles("`lock`.lat<>'' AND `lock`.lng<>'' AND `bicycle`.cooperator_id = ".$cooperator_id,$join);
        // 使用中单车数
        $used_sum = $this->sys_model_data_sum->getUsedBicycleSumC($condition);
        // 故障单车数
        $fault_sum = $this->sys_model_data_sum->getFaultBicycleSumC($condition);

        $this->load->library('sys_model/admin_menu_collect', true);

        $data = array(
            'total_bicycle' => $total,
            'total_real_bicycle' => $total_real,
            'using_bicycle' => $used_sum,
            'fault_bicycle' => $fault_sum
        );


        $this->load->common['statistics_in_page_header'] = $this->load->view('common/statistics_in_page_header', $data);

//        $this->load->common['http_server'] = HTTP_SERVER;
        $this->load->common['cur_url'] = $cur_url;
        $this->load->common['menu_id'] = $menu_id;
        $this->load->common['menu_collect_status'] = $this->sys_model_admin_menu_collect->getCollect(array('menu_id'=>$menu_id))['status'];
    }
}
