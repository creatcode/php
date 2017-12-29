<?php
class ControllerRegionRegion extends Controller {
    private $cur_url = null;
    private $error = null;
    protected  $page_size = 10;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载region Model
        $this->load->library('sys_model/region', true);
    }

    /**
     * 区域列表
     */
    public function index() {
        $filter = array();

        $condition = array();
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'region_sort ASC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_region->getRegionList($condition, $order, $limit);
        $total = $this->sys_model_region->getTotalRegions($condition);
        if (is_array($result) && !empty($result)) {
            $this->load->library('sys_model/bicycle');
            $this->load->library('sys_model/lock');
            foreach ($result as &$item) {
                $item['lock_total']    = 0;//$this->sys_model_lock->getTotalLocks(array('region_id' => $item['region_id']));
                $item['bike_total']    = $this->sys_model_bicycle->getTotalBicycles(array('region_id' => $item['region_id'],'lock_id' => array( array('neq' , '0') )));
                $item['edit_action']   = $this->url->link('region/region/edit', 'region_id='.$item['region_id']);
                $item['delete_action'] = $this->url->link('region/region/delete', 'region_id='.$item['region_id']);
                $item['info_action']   = $this->url->link('region/region/info', 'region_id='.$item['region_id']);
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('action', $this->cur_url);
        $this->assign('total', $total);
        $this->assign('add_action', $this->url->link('region/region/add'));
        $this->assign('return_action', $this->url->link('region/region'));
        $this->assign('yunyin_action', $this->url->link('system/operator/yunyin'));


        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('city_action',$this->url->link('region/region/getCityByRegion'));
        $this->assign('station_action',$this->url->link('region/region/getStationByCity'));
        $this->response->setOutput($this->load->view('region/region_list', $this->output));
    }

    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
       // $this->setDataColumn('排序');
        $this->setDataColumn('区域');
        //$this->setDataColumn('收费(每%分钟/%元)');
        $this->setDataColumn('城市');
        $this->setDataColumn('站点数');
        $this->setDataColumn('总桩车数');
        $this->setDataColumn('总单车数');
        //$this->setDataColumn('总单车锁数');
        return $this->data_columns;
    }

    /**
     * 添加区域
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('region_name', 'upper_expend', 'region_city_code', 'region_bounds', 'region_bounds_northeast_lng', 'region_bounds_northeast_lat', 'region_bounds_southwest_lng', 'region_bounds_southwest_lat', 'region_charge_time', 'region_charge_fee','deposit', 'monthly_card_money', 'yearly_card_money',
                'cards_first_half','cards_afterwards_half','first_half',
                'afterwards_half'));
            $now = time();
            $condition = array(
                'region_city_code' => (int)$input['region_city_code']
            );
            $field = 'region_city_ranking';
            $region_city_ranking = ((int)$this->sys_model_region->getMaxRegions($condition, $field) + 1);
            $data = array(
                'region_name' => $input['region_name'],
                'region_sort' => (int)$input['region_sort'],
                'region_city_code' => $input['region_city_code'],
                'region_city_ranking' => (int)$region_city_ranking,
                'region_bounds' => $input['region_bounds'],
                'region_bounds_northeast_lng' => $input['region_bounds_northeast_lng'],
                'region_bounds_northeast_lat' => $input['region_bounds_northeast_lat'],
                'region_bounds_southwest_lng' => $input['region_bounds_southwest_lng'],
                'region_bounds_southwest_lat' => $input['region_bounds_southwest_lat'],
                'region_charge_time' => (int)$input['region_charge_time'],
                'region_charge_fee' => $input['region_charge_fee'],
                'add_time' => $now
            );
            $this->sys_model_region->addRegion($data);

            $this->session->data['success'] = '添加区域成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '添加区域：' . $data['region_name']);
            
            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('region/region', $filter, true));
        }

        $this->assign('title', '区域添加');
        $this->getForm();
    }

    /**
     * 编辑区域
     */
    public function edit() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('region_name', 'upper_expend', 'region_city_code', 'region_bounds', 'region_bounds_northeast_lng', 'region_bounds_northeast_lat', 'region_bounds_southwest_lng', 'region_bounds_southwest_lat', 'region_charge_time', 'region_charge_fee', 'park','deposit', 'monthly_card_money', 'yearly_card_money',
                'cards_first_half','cards_afterwards_half','first_half',
                'afterwards_half'));
            $region_id = $this->request->get['region_id'];
            $park_str  = '';
            if(!empty($input['park'])){
                foreach($input['park'] as $v){
                    $park_str .=  $park_str == '' ? $v : ','.$v;
                }
            }
            $data = array(
                'region_name' => $input['region_name'],
                'region_sort' => (int)$input['region_sort'],
                'region_city_code' => $input['region_city_code'],
                'region_bounds' => $input['region_bounds'],
                'region_bounds_northeast_lng' => $input['region_bounds_northeast_lng'],
                'region_bounds_northeast_lat' => $input['region_bounds_northeast_lat'],
                'region_bounds_southwest_lng' => $input['region_bounds_southwest_lng'],
                'region_bounds_southwest_lat' => $input['region_bounds_southwest_lat'],
                'region_charge_time' => (int)$input['region_charge_time'],
                'region_charge_fee' => $input['region_charge_fee'],
                'park_bounds'   => $park_str
            );
            $condition = array(
                'region_id' => $region_id
            );
            $this->sys_model_region->updateRegion($condition, $data);

            $this->session->data['success'] = '编辑区域成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '编辑区域：' . $data['region_name']);

            $filter = array();
            $this->load->controller('common/base/redirect', $this->url->link('region/region', $filter, true));
        }

        $this->assign('title', '编辑区域');
        $this->getForm();
    }

    /**
     * 删除区域
     */
    public function delete() {
        if (isset($this->request->get['region_id']) && $this->validateDelete()) {
            $condition = array(
                'region_id' => $this->request->get['region_id']
            );
            $this->sys_model_region->deleteRegion($condition);

            $this->session->data['success'] = '删除区域成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '删除区域：' . $this->request->get['region_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('region/region', $filter, true));
    }

    /**
     * ajax
     * 根据区域来获取城市
     */
    public function getCityByRegion()
    {
        $region_id = (int)$this->request->post("region_id");
        $page = (int)$this->request->post('page');
        $page_size = (int)$this->request->post('page_size');

        if(empty($page)){
            $page = 1;
        }

        $this->page_size = empty($page_size)?$this->page_size:$page_size;
        $start = ($page - 1) * $this->page_size;
        $limit = "{$start},{$this->page_size}";

        $this->load->library('sys_model/city');
        $cities = $this->sys_model_city->getCityList(['region_id' => $region_id],'',$limit);

        $this->load->library('sys_model/bicycle');
        foreach($cities as $key => &$city){
            $city['total_bicycle'] = $this->sys_model_bicycle->getTotalBicycles(array('city_id' => $city['city_id'],'lock_id' => array( array('neq' , '0') )));;
        }

        $this->response->showSuccessResult($cities, "获取列表成功");
    }

    /**
     * ajax
     *  根据城市获取站点
     */
    public function getStationByCity(){
        $city_id = (int)$this->request->post("city_id");

        $page = (int)$this->request->post('page');
        if(empty($page)){
            $page = 1;
        }

        $start = ($page - 1) * $this->page_size;
        $limit = "{$start},{$this->page_size}";

        $this->load->library('sys_model/station');
        $stations = $this->sys_model_station->getStationList(['city_id'=>$city_id],'',$limit);

        $this->response->showSuccessResult($stations, "获取列表成功");
    }




    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('region_name', 'upper_expend', 'region_city_code', 'region_bounds', 'region_bounds_northeast_lng', 'region_bounds_northeast_lat', 'region_bounds_southwest_lng', 'region_bounds_southwest_lat', 'region_city_ranking','deposit', 'monthly_card_money', 'yearly_card_money',
                'cards_first_half','cards_afterwards_half','first_half',
                'afterwards_half'));
        $region_id = $this->request->get('region_id');
        if (isset($this->request->get['region_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'region_id' => $this->request->get['region_id']
            );
            $info = $this->sys_model_region->getRegionInfo($condition);
        }
        $info['region_bounds'] = !empty($info['region_bounds']) ? $info['region_bounds'] : '[]';
        $info['region_city_ranking'] = !empty($info['region_city_ranking']) ? str_pad($info['region_city_ranking'],2,"0",STR_PAD_LEFT) : '';
        # 处理停放点
        $park_bounds       = isset($info['park_bounds']) ? $info['park_bounds'] : '';
        $park_bounds_arr   = $park_bounds ? explode(',',$park_bounds) : array();
        $park_latlng_arr   = array();
        if(!empty($park_bounds_arr)){
            foreach($park_bounds_arr as $v){
                $latlgn_arr = explode('-',$v);
                if(!empty($latlgn_arr) && $latlgn_arr[0]){
                    $park_latlng_arr[] = array(
                        'lat' =>   $latlgn_arr[0],
                        'lng' =>   $latlgn_arr[1],
                    );
                }

            }
        }
        $info['park_bounds']  = $park_latlng_arr;

        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&region_id=' . $region_id);
        $this->assign('return_action', $this->url->link('region/region'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('region/region_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('region_name', 'upper_expend', 'region_city_code', 'region_bounds_northeast_lng', 'region_bounds_northeast_lat', 'region_bounds_southwest_lng', 'region_bounds_southwest_lat','deposit', 'monthly_card_money', 'yearly_card_money',
                'cards_first_half','cards_afterwards_half','first_half',
                'afterwards_half'));

        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        $region_charge_time = $this->request->post('region_charge_time');
        $region_charge_fee = $this->request->post('region_charge_fee');
        if ($region_charge_time <= 0) {
            $this->error['region_charge_time'] = '收费标准的时间必须大于或者等于1分钟！';
        }
        if ($region_charge_fee <= 0) {
            $this->error['region_charge_fee'] = '收费标准的金额必须大于或者等于1元！';
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }
        return !$this->error;
    }

    /**
     * 验证删除条件
     */
    private function validateDelete() {
        return !$this->error;
    }



}
