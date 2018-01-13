<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/9
 * Time: 15:15
 */
class ControllerCityCity extends Controller{
    protected $error = [];

    public function index(){

        $this->load->library('sys_model/city');


        $total = $this->sys_model_city->getTotalCities([]);
        $limit = $this->getPagination($total);
        $city_list = $this->sys_model_city->getCityList([],'',$limit,'city_id,city_name,region_name,region.region_id',['region'=>'region.region_id = city.region_id']);

        foreach($city_list as $key => &$value){
            $value['edit_action'] = $this->url->link('city/city/edit',array('city_id'=>$value['city_id']));
            $value['delete_action'] = $this->url->link('city/city/delete',array('city_id'=>$value['city_id']));
        }

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }
        if (isset($this->session->data['error'])) {
            $this->assign('error', $this->session->data['error']);
            unset($this->session->data['error']);
        }


        $this->assign('title','城市列表');
        $this->assign('add_action',$this->url->link('city/city/add'));
        $this->assign('data_rows',$city_list);
        $this->assign('data_columns',$this->getDataColumns());

        $this->response->setOutput($this->load->view("city/city",$this->output));

    }

    public function add(){
        $this->load->library('sys_model/city');
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()){
            $data = $this->request->post(['region_id','city_name','deposit', 'monthly_card_money', 'yearly_card_money','cards_first_half','cards_afterwards_half','first_half','afterwards_half','consumer_limit','calculate_unit','free_start','free_end','city_bounds_northeast_lng','city_bounds_northeast_lat','city_bounds_southwest_lng','city_bounds_southwest_lat']);
            $today= date("Y-m-d",time());//今天的日期
            $start_time= strtotime($today." ".$data['free_start']);//每日免费开始的时间戳
            $end_time= strtotime($today." ".$data['free_end']);//每日免费结束的时间戳
            if($start_time>$end_time){//结束比开始大才正常，否则互换
                $temp=$data['free_start'];
                $data['free_start']=$data['free_end'];
                $data['free_end']=$temp;
            }
            
            $res = $this->sys_model_city->addCity($data);
            if($res){
                $this->assign('success','添加成功');
            }else{
                $this->error['warning'] = '添加失败';
            }
            $this->session->data['success'] = '添加城市成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '添加城市：' . $data['city_name']);
            
            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('city/city', $filter, true));
        }
        $this->load->library('sys_model/region');
        $regions = $this->sys_model_region->getRegionList($where = array(), '', '', 'r.*', array());

        $this->assign('title','添加城市');
        $this->assign('regions',$regions);
        $this->assign('error',$this->error);
        $this->assign('action',$this->url->link('city/city/add'));
        $this->assign('add_action',$this->url->link('city/city/add'));
        $this->assign('return_action',$this->url->link('city/city'));
        $this->response->setOutput($this->load->view("city/form",$this->output));
    }


    public function edit(){
        $this->load->library('sys_model/city');
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()){
            $city_id = $this->request->post('city_id');
            $data = $this->request->post(['region_id','city_name','deposit', 'monthly_card_money', 'yearly_card_money','cards_first_half','cards_afterwards_half','first_half','afterwards_half','consumer_limit','calculate_unit','free_start','free_end','city_bounds_northeast_lng','city_bounds_northeast_lat','city_bounds_southwest_lng','city_bounds_southwest_lat']);
            $today= date("Y-m-d",time());//今天的日期
            $start_time= strtotime($today." ".$data['free_start']);//每日免费开始的时间戳
            $end_time= strtotime($today." ".$data['free_end']);//每日免费结束的时间戳
            if($start_time>$end_time){//结束比开始大才正常，否则互换
                $temp=$data['free_start'];
                $data['free_start']=$data['free_end'];
                $data['free_end']=$temp;
            }
            $res = $this->sys_model_city->updateCity(['city_id'=>$city_id],$data);
            if($res){
                $this->assign('success','更新成功');
            }else{
                $this->error['warning'] = '添加失败';
            }
            $this->session->data['success'] = '更新城市成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '更新城市：' . $data['city_name']);
            
            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('city/city', $filter, true));
        }
        $city_id = (int)$this->request->get('city_id');
        $city = $this->sys_model_city->getCityInfo(['city_id'=>$city_id]);

        $this->load->library('sys_model/region');
        $regions = $this->sys_model_region->getRegionList($where = array(), '', '', 'r.*', array());

        $this->assign('title','编辑城市');
        $this->assign('data',$city);
        $this->assign('regions',$regions);
        $this->assign('error',$this->error);
        $this->assign('action',$this->url->link('city/city/edit'));
        $this->assign('return_action',$this->url->link('city/city'));
        $this->response->setOutput($this->load->view("city/form",$this->output));
    }


    public function delete(){
        $city_id = (int)$this->request->get('city_id');
        $this->load->library('sys_model/city');
        $res = $this->sys_model_city->deleteCity(['city_id'=>$city_id]);
        if($res){
            $this->session->data['success'] = '删除成功';
        }else{
            $this->session->data['error']['warning'] = '删除失败';
        }
        $this->load->controller('common/base/redirect', $this->url->link('city/city'));
    }

    protected function getDataColumns() {
        $this->setDataColumn('城市名称');
        $this->setDataColumn('所属区域');
        return $this->data_columns;
    }


    protected function validateForm(){
        $input = $this->request->post(array('region_id','city_name'));

        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }

        //排除重复要看看客户 是按照什么来标志一个城市的
        //todo 略

        return !$this->error;
    }
}