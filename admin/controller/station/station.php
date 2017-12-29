<?php

/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/12
 * Time: 17:03
 */

use Tool\ArrayUtil;

class ControllerStationStation extends Controller {

    public function __construct($registry) {
        parent::__construct($registry);
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';
    }

    public function index() {

        $filter = $this->request->get(array('station_state', 'add_time', 'region_id', 'city_id'));

        $condition = array();
        if (!empty($filter['station_state'])) {
            $condition['station_state'] = $filter['station_state'];
        }
        if (is_numeric($filter['region_id'])) {
            $condition['region_id'] = (int) $filter['region_id'];
        }
        if (!empty($filter['city_id'])) {
            $condition['city_id'] = (int) $filter['city_id'];
        }

        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        $this->assign('filter', $filter);

        if (isset($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = '';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $this->load->library('sys_model/station');
        $total = $this->sys_model_station->getTotalStations($condition);
        $stations = $this->sys_model_station->getStationList($condition, $order, $limit, '*');
        //echo json_encode($stations);
        $regions = [];
        $cities = [];
        foreach ($stations as $key => &$val) {
            array_push($regions, $val['region_id']);
            array_push($cities, $val['city_id']);
            $val['edit_action'] = $this->url->link('station/station/edit', ['station_id' => $val['station_id']]);
            $val['info_action'] = $this->url->link('station/station/info', ['station_id' => $val['station_id']]);
            $val['delete_action'] = $this->url->link('station/station/delete', ['station_id' => $val['station_id']]);
            $val['stake_action'] = $this->url->link('station/station/stake', ['station_id' => $val['station_id']]); //锁莊列表 可以添加 编辑删除锁桩
            $val['alarm_action'] = $this->url->link('station/station/alarm', ['station_id' => $val['station_id']]); //站点车辆高低阈值
            $val['adv_action'] = $this->url->link('station/station/adv', ['station_id' => $val['station_id']]); //广告查看 跳转到广告详情 远程站点广告投放 图片视频内容等等  此链接未定
        }

        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $regions = $this->sys_model_region->getRegionList(['region_id' => ['in', $regions]], '', '', 'region_id,region_name');
        $cities = $this->sys_model_city->getCityList(['city_id' => ['in', $cities]], '', '', 'city_id,city_name');
        $regions = ArrayUtil::changeArrayKey($regions, 'region_id');
        $cities = ArrayUtil::changeArrayKey($cities, 'city_id');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }
        $this->assign('filter_regions', $filter_regions);
        $this->assign('title', '站点列表');
        $this->assign('regions', $regions);
        $this->assign('cities', $cities);
        $this->assign('station_states', get_station_state());
        $this->assign('station_power_states', get_station_power_state());
        $this->assign('add_action', $this->url->link('station/station/add'));
        $this->assign('data_rows', $stations);
        $this->assign('data_columns', $this->getDataColumns());
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $this->assign('pagination', $pagination);
        $this->assign('action', $this->url->link('station/station'));
        $this->response->setOutput($this->load->view("station/station", $this->output));
    }

    protected function getDataColumns() {
        $this->setDataColumn('站点编号');
        $this->setDataColumn('所属城市');
        $this->setDataColumn('所属区域');
        $this->setDataColumn('站点名字');
        $this->setDataColumn('站点位置');
        $this->setDataColumn('站点状态');
        $this->setDataColumn('电池状态');
        $this->setDataColumn('可停车数');
        $this->setDataColumn('已停车数');
        return $this->data_columns;
    }

    public function add() {


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $input = $this->request->post(array('station_sn', 'station_states', 'power_state', 'region_id', 'city_id', 'threshold_height', 'threshold_low', 'station_name'));
            $data = array(
                'station_sn' => $input['station_sn'],
                'lat' => 10,
                'lng' => 10,
                'total' => 10,
                'used' => 0,
                'station_state' => $input['station_states'],
                'power_state' => $input['power_state'],
                'city_id' => $input['city_id'],
                'region_id' => $input['region_id'],
                'add_time' => time(),
                'threshold_height' => $input['threshold_height'],
                'threshold_low' => $input['threshold_low'],
                'station_name' => $input['station_name']
            );
            $this->load->library('sys_model/station');
            $station_id = $this->sys_model_station->addStation($data);
            $this->session->data['success'] = '添加成功！';

            $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
        }


        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($regions as $key => $val) {
            $regions[$key]['city'] = $this->sys_model_city->getCityList(['region_id' => $val['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }
        $this->assign('title', '添加站点');
        $this->assign('regions', $regions);
        $this->assign('station_states', get_station_state());
        $this->assign('station_power_states', get_station_power_state());
        $this->assign('return_action', $this->url->link('station/station'));
        $this->assign('action', $this->url->link('station/station/add'));
        $this->response->setOutput($this->load->view("station/station_form", $this->output));
    }

    public function edit() {
        $station_id = $this->request->get['station_id'];
        if (!$station_id) {
            $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
        }
        $this->load->library('sys_model/station');
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $input = $this->request->post(array('station_sn', 'station_states', 'power_state', 'region_id', 'city_id', 'threshold_height', 'threshold_low', 'station_name'));
            $data = array(
                'station_sn' => $input['station_sn'],
                'station_state' => $input['station_states'],
                'power_state' => $input['power_state'],
                'city_id' => $input['city_id'],
                'region_id' => $input['region_id'],
                'threshold_height' => $input['threshold_height'],
                'threshold_low' => $input['threshold_low'],
                'station_name' => $input['station_name']
            );
            $where['station_id'] = $station_id;
            $station_id = $this->sys_model_station->updateStation($where, $data);
            $this->session->data['success'] = '编辑成功！';
            $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
        }
        $data = $this->sys_model_station->getStationList(['station_id' => $station_id], '', '1', '*', [])[0];
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($regions as $key => $val) {
            $regions[$key]['city'] = $this->sys_model_city->getCityList(['region_id' => $val['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }
        $this->assign('title', '编辑站点');
        $this->assign('data', $data);
        $this->assign('regions', $regions);
        $this->assign('station_states', get_station_state());
        $this->assign('station_power_states', get_station_power_state());
        $this->assign('return_action', $this->url->link('station/station'));
        $this->assign('action', $this->url->link('station/station/edit', ['station_id' => $station_id]));
        $this->response->setOutput($this->load->view("station/station_form", $this->output));
    }

    public function delete() {
        $station_id = $this->request->get['station_id'];
        if (!$station_id) {
            $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
        }
        $this->load->library('sys_model/station');
        $where['station_id'] = $station_id;
        $this->sys_model_station->deleteStation($where);
        $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
    }

    /**
     * 站点详细信息
     */
    public function info() {
        $station_id = (int) $this->request->get('station_id');
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/station');
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');

        $station = $this->sys_model_station->getStationInfo(['station_id' => $station_id]);
        $region = $this->sys_model_region->getRegionInfo(['region_id' => $station['region_id']]);
        $city = $this->sys_model_city->getCityInfo(['city_id' => $station['city_id']]);


        $this->assign('title', '站点信息');
        $this->assign('data', $station);
        $this->assign('region', $region);
        $this->assign('city', $city);
        $this->assign('station_states', get_station_state());
        $this->assign('station_power_states', get_station_power_state());
        $this->assign('return_action', $this->url->link('station/station'));
        $this->response->setOutput($this->load->view("station/station_info", $this->output));
    }

    public function validateForm() {
        
    }

    /**
     * 站点锁桩信息
     */
    public function stake() {
        $station_id = $this->request->get('station_id');
        $this->load->library('sys_model/stake');
        $total = $this->sys_model_stake->getTotalStakes(['station_id' => $station_id]);
        $limit = $this->getPagination($total);

        $stakes = $this->sys_model_stake->getStakeList(['station_id' => $station_id], '', $limit);
        foreach ($stakes as $key => &$stake) {
            $stake['edit_action'] = $this->url->link('station/station/editStake', ['station_id' => $stake['station_id'], 'stake_id' => $stake['stake_id']]);
            $stake['delete_action'] = $this->url->link('station/station/deleteStake', ['station_id' => $stake['stake_id'], 'stake_id' => $stake['stake_id']]);
        }

        $this->assign('title', '锁桩列表');
        $this->assign('stake_states', get_stake_state());
        $this->assign('data_rows', $stakes);
        $this->assign('data_columns', [['text' => 'ID'], ['text' => '编号'], ['text' => '桩状态']]);
        $this->assign('add_action', $this->url->link('station/station/addStake', ['station_id' => $station_id]));
        $this->assign('return_action', $this->url->link('station/station'));
        $this->response->setOutput($this->load->view("station/stake", $this->output));
    }

    public function addStake() {
        $station_id = $this->request->get('station_id');
        if (!$station_id) {
            $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
        }
        $this->load->library('sys_model/stake');
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $input = $this->request->post(array('stake_states', 'stake_sn'));
            $data = array(
                'stake_state' => $input['stake_states'],
                'stake_sn' => $input['stake_sn'],
                'station_id' => $station_id
            );
            $stake_id = $this->sys_model_stake->addStake($data);
            $this->session->data['success'] = '添加成功！';
            $url = $this->url->link('station/station/stake&station_id=' . $station_id);

            $this->load->controller('common/base/redirect', $url);
        }
        $this->assign('action', $this->url->link('station/station/addStake', ['station_id' => $station_id]));
        $this->assign('title', '添加锁桩');
        $this->assign('stake_states', get_stake_state());
        $this->assign('return_action', $this->url->link('station/station/stake', ['station_id' => $station_id]));
        $this->response->setOutput($this->load->view("station/stake_form", $this->output));
    }

    public function editStake() {
        $station_id = $this->request->get('station_id');
        $stake_id = $this->request->get('stake_id');
        if (!$stake_id) {
            $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
        }
        $this->load->library('sys_model/stake');
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $input = $this->request->post(array('stake_states', 'stake_sn'));
            $data = array(
                'stake_state' => $input['stake_states'],
                'stake_sn' => $input['stake_sn'],
            );
            $where['stake_id'] = $stake_id;
            $stake_id = $this->sys_model_stake->updateStake($where, $data);
            $this->session->data['success'] = '编辑成功！';
            $url = $this->url->link('station/station/stake&station_id=' . $station_id);

            $this->load->controller('common/base/redirect', $url);
        }
        $data = $this->sys_model_stake->getStakeList(['stake_id' => $stake_id], '', '1', '*', [])[0];
        $this->assign('data', $data);
        $this->assign('action', $this->url->link('station/station/editStake', ['station_id' => $station_id, 'stake_id' => $stake_id]));
        $this->assign('title', '添加锁桩');
        $this->assign('stake_states', get_stake_state());
        $this->assign('return_action', $this->url->link('station/station/stake', ['station_id' => $station_id]));
        $this->response->setOutput($this->load->view("station/stake_form", $this->output));
    }

    public function deleteStake() {
        $station_id = $this->request->get['station_id'];
        $stake_id = $this->request->get['stake_id'];
        if (!$stake_id) {
            $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
        }
        $this->load->library('sys_model/stake');
        $where['stake_id'] = $stake_id;
        $this->sys_model_stake->deleteStake($where);
        $url = $this->url->link('station/station/stake&station_id=' . $station_id);
        $this->load->controller('common/base/redirect', $url);
    }

    public function alarm() {
        $station_id = $this->request->get['station_id'];
        if (!$station_id) {
            $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
        }
        $this->load->library('sys_model/station');
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $input = $this->request->post(array('threshold_low', 'threshold_height'));
            $data = array(
                'threshold_height' => $input['threshold_height'],
                'threshold_low' => $input['threshold_low'],
            );
            $where['station_id'] = $station_id;
            $station_id = $this->sys_model_station->updateStation($where, $data);
            $this->session->data['success'] = '编辑成功！';
            $this->load->controller('common/base/redirect', $this->url->link('station/station', [], true));
        }
        $data = $this->sys_model_station->getStationList(['station_id' => $station_id], '', '1', 'threshold_height,threshold_low', [])[0];
        $this->assign('action', $this->url->link('station/station/alarm', ['station_id' => $station_id]));
        $this->assign('data', $data);
        $this->assign('title', '站点阈值设置');
        $this->assign('return_action', $this->url->link('station/station'));
        $this->response->setOutput($this->load->view("station/alarm", $this->output));
    }

}
