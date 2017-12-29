<?php

class ControllerLocationLocation extends Controller
{
    public function getBicycleLocation()
    {
        if (!isset($this->request->post['lng']) || !isset($this->request->post['lat'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $lat = $this->request->post['lat'];
        $lng = $this->request->post['lng'];

        $this->load->library('tool/polygon');

        //距离单车点的距离（不进行坐标点的转换）
        //1公里
        $distance = 0.2; //(isset($this->request->get['fromApi']) && ($this->request->get['fromApi'] == 'ios')) ? 2 : 0.45;
        $this->load->library('tool/distance');
        $arr = $this->tool_distance->getRange($lat, $lng, $distance);

        $zoom = $this->request->post['zoom'];
        if ($zoom < 15) {
            $this->response->showErrorResult($this->language->get('error_map_zoom'), 125);
        }

        $this->load->library('sys_model/bicycle');
        $where = array();
        $where['l.lock_status'] = '0';
        //$where['b.type'] = 1;

        $where['l.lat'] = array(
            array('gt', $arr['min_lat']),
            array('lt', $arr['max_lat'])
        );

        $where['l.lng'] = array(
            array('gt', $arr['min_lng']),
            array('lt', $arr['max_lng'])
        );

        $result = $this->sys_model_bicycle->getBicycleLockMarker($where);
        
        //不同地区不同价格，后面可用缓存保存，预约界面显示
        $this->load->library('sys_model/region');
        $region_list = $this->sys_model_region->getRegionList(array(
            'region_bounds_northeast_lng' => array('gt', $lng),
            'region_bounds_southwest_lng' => array('lt', $lng),
            'region_bounds_northeast_lat' => array('gt', $lat),
            'region_bounds_southwest_lat' => array('lt', $lat)
        ));
	
        $new_regions = array();
        foreach ($region_list as $region) {
            $new_regions[$region['region_id']] = $region;
        }
		
        //超过多长时间就变成免费单车
        $free_bike_day = $this->config->get('config_free_bike_day');
        $over_day = isset($free_bike_day) ? $free_bike_day : 5;
		
        $data = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $item) {
                $item['fee'] = isset($new_regions[$item['region_id']]) ? $new_regions[$item['region_id']]['region_charge_fee'] : 0;
                $item['time_unit'] = isset($new_regions[$item['region_id']]) ? $new_regions[$item['region_id']]['region_charge_time'] : 30;
                $item['time_unit'] = ($item['time_unit'] == 30) ? '半小时' : '1小时';
                $item['area_code'] = sprintf('%03d%02d', $item['region_city_code'], $item['region_city_ranking']);
                $item['is_limit_free'] = (time() - $item['last_used_time']) > ($over_day * 60 * 60 * 24) && (isset($new_regions[$item['region_id']]['coupon_usable']) && $new_regions[$item['region_id']]['coupon_usable']) ? true : false;
                $data[] = $item;
            }
        }

        $this->response->showSuccessResult($data);
    }

    /**
     * 获取本地价格
     */
    public function getLocalPrice()
    {
        $city_code = isset($this->request->post['city_code']) ? $this->request->post['city_code'] : '';
        if (strlen($city_code) == 4) {
            $city_code = substr($city_code, -3);
        }

        $this->load->library('sys_model/region');
        $this->load->library('tool/polygon');

        $cur_lat = isset($this->request->post['cur_lat']) ? $this->request->post['cur_lat'] : '0';
        $cur_lng = isset($this->request->post['cur_lng']) ? $this->request->post['cur_lng'] : '0';

        $region_info = $this->sys_model_region->getRegionInfo(array(
            'region_bounds_northeast_lng' => array('gt', $cur_lng),
            'region_bounds_southwest_lng' => array('lt', $cur_lng),
            'region_bounds_northeast_lat' => array('gt', $cur_lat),
            'region_bounds_southwest_lat' => array('lt', $cur_lat)
        ));

        if (!empty($region_info)) {
            $arr = array(
                'price' => $region_info['region_charge_fee'],
                'unit' => strval($region_info['region_charge_time'] / 60)
            );
            $this->response->showSuccessResult($arr);
        }

        $region_info = $this->sys_model_region->getRegionInfo(array('region_city_code' => $city_code));
        if (!empty($region_info)) {
            $arr = array(
                'price' => $region_info['region_charge_fee'],
                'unit' => strval($region_info['region_charge_time'] / 60)
            );
            $this->response->showSuccessResult($arr);
        }

        $this->load->library('tool/polygon', true);

        $region_list = $this->sys_model_region->getRegionList();

        $storage_list = array();

        foreach ($region_list as $region) {
            $northeast['lng'] = $region['region_bounds_northeast_lng'];
            $northeast['lat'] = $region['region_bounds_northeast_lat'];
            $southwest['lng'] = $region['region_bounds_southwest_lng'];
            $southwest['lat'] = $region['region_bounds_southwest_lat'];

            $northeast_southwest = array($northeast, $southwest);

            $isInRegion = $this->tool_polygon->pointIsInRegion($cur_lng, $cur_lat, $northeast_southwest);

            if (!$isInRegion) {
                continue;
            }

            if (strlen($region['region_bounds']) == 2) continue;

            $storage_list[] = array(
                'region_charge_time' => $region['region_charge_time'],
                'region_charge_fee' => $region['region_charge_fee']
            );
        }

        $arr_data = array();

        if (empty($storage_list)) {
            $arr_data['price'] = $this->config->get('config_price_unit');
            $unit = $this->config->get('config_time_charge_unit');
            $f_unit = strval($unit / 3600);
            $arr_data['unit'] = $f_unit;
        } else {
            $arr_data['price'] = $storage_list[0]['region_charge_fee'];
            $arr_data['unit'] = is_numeric($storage_list[0]['region_charge_time']) ? strval($storage_list[0]['region_charge_time'] / 60) : $storage_list[0]['region_charge_time'];
        }

        $this->response->showSuccessResult($arr_data);
    }

    /**
     * 判断是否进入维护期
     * @return bool true 是， false 否
     */
    private function checkMaintenanceTime() {
        $now = time();
        $this->load->library('sys_model/system_maintenance');
        $condition = array(
            'start_time' => array('elt', $now),
            'end_time' => array('egt', $now),
        );
        $rec = $this->sys_model_system_maintenance->getSystemMaintenanceInfo($condition);
        if ($rec) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getBicycleLocation1()
    {
        if ($this->checkMaintenanceTime()) {
            $this->response->showErrorResult($this->language->get('error_system_maintaining'), 199);
        }

        if (!isset($this->request->post['lng']) || !isset($this->request->post['lat'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $lat = $this->request->post['lat'];
        $lng = $this->request->post['lng'];

        $this->load->library('tool/polygon');

        //距离单车点的距离（不进行坐标点的转换）
        //1公里
        $distance = 0.2; //(isset($this->request->get['fromApi']) && ($this->request->get['fromApi'] == 'ios')) ? 2 : 0.45;
        $this->load->library('tool/distance');
        $arr = $this->tool_distance->getRange($lat, $lng, $distance);

        $zoom = $this->request->post['zoom'];
        if ($zoom < 15) {
            $this->response->showErrorResult($this->language->get('error_map_zoom'), 125);
        }

        $this->load->library('sys_model/bicycle');
        $where = array();
        $where['l.lock_status'] = '0';
        //$where['b.type'] = 1;

        $where['l.lat'] = array(
            array('gt', $arr['min_lat']),
            array('lt', $arr['max_lat'])
        );

        $where['l.lng'] = array(
            array('gt', $arr['min_lng']),
            array('lt', $arr['max_lng'])
        );

        $result = $this->sys_model_bicycle->getBicycleLockMarker($where);
        
        //不同地区不同价格，后面可用缓存保存，预约界面显示
        $this->load->library('sys_model/region');
        $region_list = $this->sys_model_region->getRegionList(array(
            'region_bounds_northeast_lng' => array('gt', $lng),
            'region_bounds_southwest_lng' => array('lt', $lng),
            'region_bounds_northeast_lat' => array('gt', $lat),
            'region_bounds_southwest_lat' => array('lt', $lat)
        ));
	
        $new_regions = array();
        foreach ($region_list as $region) {
            $new_regions[$region['region_id']] = $region;
        }

        $data = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $item) {
                $item['fee'] = isset($new_regions[$item['region_id']]) ? $new_regions[$item['region_id']]['region_charge_fee'] : 0;
                $item['time_unit'] = isset($new_regions[$item['region_id']]) ? $new_regions[$item['region_id']]['region_charge_time'] : 30;
                $item['time_unit'] = ($item['time_unit'] == 30) ? '半小时' : '1小时';
                $item['area_code'] = sprintf('%03d%02d', $item['region_city_code'], $item['region_city_ranking']);
                $data[] = $item;
            }
        }

        $this->response->showSuccessResult($data);
    }
}
