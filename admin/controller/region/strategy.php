<?php
use Tool\ArrayUtil;
class ControllerRegionStrategy extends Controller{


    private $cur_url = null;
    private $error = [];
    protected $page_size = 10;


    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function index(){
        $this->load->library('sys_model/strategy');
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');

        $total = $this->sys_model_strategy->getTotalStrategies([]);
        $limit = $this->getPagination($total);
        $strategy = $this->sys_model_strategy->getStrategyList([],'',$limit);

        $regions = [];
        $cities = [];
        foreach($strategy as $key => &$val ){
            array_push($regions,$val['region_id']);
            array_push($cities,$val['city_id']);
            $val['delete_action'] = $this->url->link('region/strategy/delete',['strategy_id'=>$val['strategy_id']]);
            $val['edit_action'] = $this->url->link('region/strategy/edit',['strategy_id'=>$val['strategy_id']]);
        }

        $regions = $this->sys_model_region->getRegionList(['region_id'=>['in',$regions]],'','','region_id,region_name');
        $cities = $this->sys_model_city->getCityList(['city_id'=>['in',$cities]],'','','city_id,city_name');
        $regions = ArrayUtil::changeArrayKey($regions,'region_id');
        $cities =  ArrayUtil::changeArrayKey($cities,'city_id');



        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }
        if (isset($this->session->data['error'])) {
            $this->assign('error', $this->session->data['error']);
            unset($this->session->data['error']);
        }


        $this->assign('cities',$cities);
        $this->assign('regions',$regions);
        $this->assign('user_types',get_user_type());
        $this->assign('bicycle_types',get_bicycle_type());
        $this->assign('data_rows',$strategy);
        $this->assign('data_columns',$this->getDataColumns());
        $this->assign('add_action',$this->url->link('region/strategy/add'));
        $this->response->setOutput($this->load->view('region/strategy_list',$this->output));
    }

    public function add(){
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()){
            $data = $this->request->post(array(
                'region_id', 'city_id', 'user_type', 'bicycle_type',
                'deposit', 'monthly_card_money', 'yearly_card_money',
                'cards_first_half','cards_afterwards_half','first_half',
                'afterwards_half'
            ));
            $this->load->library('sys_model/strategy');

            $res = $this->sys_model_strategy->addStrategy($data);
            if($res){
                $this->assign('success','添加成功');
            }else{
                $this->error['warning'] = '添加失败';
            }

        }

        $region_id = $this->request->post("region_id");
        if(!empty($region_id)){
            $this->load->library('sys_model/city');
            $cities = $this->sys_model_city->getCityList(['region_id'=>$region_id]);
            $this->assign('cities',$cities);
        }

        $this->load->library('sys_model/region');
        $regions = $this->sys_model_region->getRegionList([],'','','region_id,region_name');

        $this->assign('bicycle_types',get_bicycle_type());
        $this->assign('user_types',get_user_type());
        $this->assign('regions',$regions);
        $this->assign('error',$this->error);
        $this->assign('title','添加计费标准');
        $this->assign('action',$this->url->link('region/strategy/add'));
        $this->assign('return_action',$this->url->link('region/strategy'));
        $this->assign('get_city_action',$this->url->link('region/region/getCityByRegion'));
        $this->response->setOutput($this->load->view('region/strategy_form',$this->output));
    }

    public function edit(){
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()){
            $data = $this->request->post(array(
                'region_id', 'city_id', 'user_type', 'bicycle_type',
                'deposit', 'monthly_card_money', 'yearly_card_money',
                'cards_first_half','cards_afterwards_half','first_half',
                'afterwards_half','strategy_id'
            ));
            $this->load->library('sys_model/strategy');

            $res = $this->sys_model_strategy->updateStrategy(['strategy_id'=>$data['strategy_id']],$data);
            if($res){
                $this->assign('success','更新成功');
            }else{
                $this->error['warning'] = '更新失败';
            }
        }
        $strategy_id = $this->request->get('strategy_id');
        $this->load->library('sys_model/strategy');
        $strategy = $this->sys_model_strategy->getStrategyInfo(['strategy_id'=>$strategy_id]);


        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $regions = $this->sys_model_region->getRegionList([],'','','region_id,region_name');
        $cities = $this->sys_model_city->getCityList(['region_id'=>$strategy['region_id']]);
        $this->assign('cities',$cities);


        $this->assign('data',$strategy);
        $this->assign('bicycle_types',get_bicycle_type());
        $this->assign('user_types',get_user_type());
        $this->assign('regions',$regions);
        $this->assign('error',$this->error);
        $this->assign('title','更新计费标准');
        $this->assign('action',$this->url->link('region/strategy/edit',array('strategy_id'=>$strategy_id)));
        $this->assign('return_action',$this->url->link('region/strategy'));
        $this->assign('get_city_action',$this->url->link('region/region/getCityByRegion'));
        $this->response->setOutput($this->load->view('region/strategy_form',$this->output));
    }

    public function delete(){
        $strategy_id = (int)$this->request->get('strategy_id');
        $this->load->library('sys_model/strategy');
        $res = $this->sys_model_strategy->deleteStrategy(['strategy_id'=>$strategy_id]);
        if($res){
            $this->session->data['success'] = '删除成功';
        }else{
            $this->session->data['error']['warning'] = '删除失败';
        }
        $this->load->controller('common/base/redirect', $this->url->link('region/strategy'));
    }


    public function validateForm(){
        $input = $this->request->post(array(
            'region_id', 'city_id', 'user_type', 'bicycle_type',
            'deposit', 'monthly_card_money', 'yearly_card_money',
            'cards_first_half','cards_afterwards_half','first_half',
            'afterwards_half'
        ));

        $this->assign('data',$input);
        foreach ($input as $k => $v) {
            if(!is_numeric($v)){
                $this->error[$k] = '必须输入数字';
            }
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }

        return !$this->error;
    }

    protected function getDataColumns() {
        $this->setDataColumn('区域');
        $this->setDataColumn('城市');
        $this->setDataColumn('单车类型');
        $this->setDataColumn('用户类型');
        $this->setDataColumn('押金');
        $this->setDataColumn('月卡');
        $this->setDataColumn('年卡');
        $this->setDataColumn('月卡用户第一个半小时');
        $this->setDataColumn('月卡用户最后一个半小时');
        $this->setDataColumn('普通用户第一个半小时');
        $this->setDataColumn('普通用户最后一个半小时');
        return $this->data_columns;
    }
}

