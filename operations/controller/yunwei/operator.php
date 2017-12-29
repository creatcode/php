<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/9
 * Time: 16:03
 */
class ControllerYunweiOperator extends Controller {
    /**
     * 开锁
     */
    public function openLock() {
        if(!isset($this->request->post['lat']) || !$this->request->post['lat']){
            $this->response->showErrorResult('参数错误或缺失',1);
        }
        if(!isset($this->request->post['lng']) || !$this->request->post['lng']){
            $this->response->showErrorResult('参数错误或缺失',1);
        }
        if(!(isset($this->request->post['full_bicycle_sn']) || isset($this->request->post['bicycle_sn']))){
            $this->response->showErrorResult('参数错误或缺失',1);
        }
        if(isset($this->request->post['full_bicycle_sn'])){
            $full_bicycle_sn = trim($this->request->post['full_bicycle_sn']);
            if (!$full_bicycle_sn) {
                $this->response->showErrorResult('参数错误或缺失',1);
            }
//            $w = array('full_bicycle_sn'=>$this->request->post['full_bicycle_sn']);
            $w = array('bicycle_sn' => substr($this->request->post['full_bicycle_sn'], -6));
        }else{
            $bicycle_sn = trim($this->request->post['bicycle_sn']);
            if (!$bicycle_sn) {
                $this->response->showErrorResult('参数错误或缺失',1);
            }
            # 补全full_bicycle_sn；
//            $reg_arr['lat'] = $this->request->post['lat'];
//            $reg_arr['lng'] = $this->request->post['lng'];
//            $region_info = $this->load->controller('common/base/haveRegionIdbyLatLng',$reg_arr);
//            if(empty($region_info)){
//                $this->response->showErrorResult('参数错误或缺失',1);
//            }
//            $full_bicycle_sn = $region_info['region_city_code'].str_pad($region_info['region_sort'],2,0,STR_PAD_LEFT).trim($this->request->post['bicycle_sn']);
//            $w = array('full_bicycle_sn'=>$full_bicycle_sn);
            $w = array('b.bicycle_sn'=>$bicycle_sn);
        }
        $time   = 0;
        $order  = "";
        $limit  = "0,1";
        $data   = array();
        //判断的条件写在startup
        if ($this->config->has('order_add_time')) {
            $time = $this->config->get('order_add_time');
        }
        $this->load->library('sys_model/bicycle', true);

        # 单车和锁信息；
        $bike_loke_info = $this->sys_model_bicycle->getBicycleAndLockInfo($w,$order,$limit);

        if(empty($bike_loke_info) && empty($bike_loke_info[0])){
            $this->response->showErrorResult('单车不存在');
        }

        $user_info = $this->load->controller('common/base/getAdminInfo',$this->request->post['user_name']);

        //可以判断是否在500米以内
        $lat = $this->request->post['lat'];
        $lng = $this->request->post['lng'];
        $this->load->library('tool/distance');
        $distance = $this->tool_distance->getDistance($lng, $bike_loke_info[0]['lng'], $lat, $bike_loke_info[0]['lat']);
        //var_dump($distance);exit;
        //if ($distance > 100) {
           // $this->response->showErrorResult('超过开锁距离，请在500米内开锁', 521);
       // }
        # 判断用户是否有权限开着把锁；
        if(isset($user_info['cooperator_id']) && isset($bike_loke_info[0]['cooperator_id'])){
            if($user_info['cooperator_id'] != 0){
                if($user_info['cooperator_id'] != $bike_loke_info[0]['cooperator_id']){
                    $this->response->showErrorResult('您无权限开此锁', 110);
                }
            }
        }else{
            $this->response->showErrorResult('您无权限开此锁', 110);
        }

        # 判断用户是否已经开过锁；
        $this->load->library('sys_model/open_lock', true);

        $add_look_result = $this->sys_model_open_lock->getOrdersInfo(array("admin_id" => $user_info['admin_id'],"open_state" => 1, "bicycle_id" => $bike_loke_info[0]['bicycle_id']));
        //if(!empty($add_look_result)){
           // $this->response->showErrorResult('不能同时开多把锁，请先关闭其他锁', 110);
        //}

        # 开锁
        if ($bike_loke_info[0]['lock_type'] == 2) {

        } elseif ($bike_loke_info[0]['lock_type'] == 1) {
            $this->instructions_instructions = new Instructions\Instructions($this->registry);
            $result = $this->instructions_instructions->openLock($bike_loke_info[0]['lock_sn'], $time);
        }

        #if(!$result['state']) {
            #$this->response->showErrorResult('开锁失败', 110);
       # }else{
            # 插入开锁记录；
            $open_lock = array();
            $open_lock['start_lng']         = $this->request->post['lng'];
            $open_lock['start_lat']         = $this->request->post['lat'];
            $open_lock['bicycle_id']        = $bike_loke_info[0]['bicycle_id'];
            $open_lock['full_bicycle_sn']   = $bike_loke_info[0]['full_bicycle_sn'];
            $open_lock['lock_sn']           = $bike_loke_info[0]['lock_sn'];
            $open_lock['add_time']          = time();
            $open_lock['start_time']        = time();
            $open_lock['open_state']       = 1;
            $open_lock['admin_id']           = $user_info['admin_id'];
            $open_lock['user_name']         = $user_info['admin_name'];
            $open_lock['cooperator_id']     = $user_info['cooperator_id'];
            $add_result = $this->sys_model_open_lock->addOrders($open_lock);
            if(!$add_result){
                $this->response->showErrorResult('开锁记录出错', 110);
            }
       # }
        #开锁成功后更新单车表bicycle的last_used_time 最后使用时间
        $bicyce_update_data = array('last_used_time' => time());
        $use_where = array('bicycle_id' => $bike_loke_info[0]['bicycle_id']);
        $return_msg = $this->sys_model_bicycle->updateBicycle($use_where,$bicyce_update_data);
        if(!$return_msg){
            $this->response->showErrorResult('更新最近使用时间出错', 110);
        }

        $re_data['lat']               = $bike_loke_info[0]['lat'];
        $re_data['lng']               = $bike_loke_info[0]['lng'];
        $re_data['lock_id']           = $bike_loke_info[0]['lock_id'];
        $re_data['bicycle_id']       = $bike_loke_info[0]['bicycle_id'];
        $re_data['full_bicycle_sn']  = isset($bike_loke_info[0]['full_bicycle_sn']) ? $bike_loke_info[0]['full_bicycle_sn'] : "";
        $re_data['lat']               = $bike_loke_info[0]['lat'];
        $re_data['lock_status_str']  = $bike_loke_info[0]['lock_status'] == 1 ? "开锁": $bike_loke_info[0]['lock_status'] == 2 ? "异常": "关锁" ;

        # 判断单车的状态
        $bike_state_arr = $this->load->controller('common/base/getBikeStatus',$bike_loke_info[0]);

        #统计故障个数
        $re_data['lock_type'] = $bike_loke_info[0]['lock_type'];

        $re_data['no_user_time']            = $bike_state_arr['no_user_time'];
        $re_data['show_value']['code']      = $bike_state_arr['show_value']['code'];
        $re_data['show_value']['value']     = $bike_state_arr['show_value']['value'];
        $re_data['show_code']               = $bike_state_arr['show_code'];
        $re_data['all_fault_num']           = $bike_state_arr['all_fault_num'];
        $re_data['fault_report_type_name']  = $bike_state_arr['fault_report_type_name'];
        if ($bike_loke_info[0]['lock_type'] == 2 || $bike_loke_info[0]['lock_type'] == 5) {
            $output = array(
                'mac_address' => $bike_loke_info[0]['mac_address'],
                'encrypt_key' => $bike_loke_info[0]['encrypt_key'],
                'password' => $bike_loke_info[0]['password'],
                'order_id' => $add_result,
                'lock_type' => $bike_loke_info[0]['lock_type'],
                'lock_sn' => $bike_loke_info[0]['lock_sn'],
                'add_time' => $open_lock['add_time'],
            );
            $output = array_merge($output, $re_data);
            $this->response->showSuccessResult($output);
        } elseif ($bike_loke_info[0]['lock_type'] == 1) {
            $output = array(
                'order_id' => $add_result,
                'lock_type' => $bike_loke_info[0]['lock_type']
            );
            $output = array_merge($output, $re_data);
            $this->response->showSuccessResult($output);
        }
        $this->response->showSuccessResult($re_data, '开锁指令已发');

    }

    /**
     * 响铃
     */
    public function beepLock() {
        $full_bicycle_sn = $this->request->post['full_bicycle_sn'];
        if (!isset($this->request->post['full_bicycle_sn']) || !$this->request->post['full_bicycle_sn']) {
            $this->response->showErrorResult('参数错误或缺失',1);
        }
        $this->load->library('sys_model/bicycle', true);
        $w      = array('full_bicycle_sn'=>$this->request->post['full_bicycle_sn']);
        $order  = "";
        $limit  = "0,1";
        # 单车和锁信息；
        $bike_loke_info = $this->sys_model_bicycle->getBicycleAndLockInfo($w,$order,$limit);
        if(empty($bike_loke_info) || empty($bike_loke_info[0])){
            $this->response->showErrorResult('单车不存在');
        }

        $this->load->library('instructions/instructions', true);
        $this->instructions_instructions->beepLock($bike_loke_info[0]['lock_sn']);
        $this->response->showSuccessResult('', '响铃指令已发送');
    }

    /**
     * 查找锁的位置
     */
    public function selectLock() {
        $device_id = $this->request->post['device_id'];
        if (empty($device_id)) {
            $this->response->showErrorResult('参数错误或缺失',1);
        }
        $this->load->library('instructions/instructions', true);
        $this->instructions_instructions->selectLocks($device_id);
        $this->response->showSuccessResult('', '查找锁位置信息指令已发送');
    }

    /**
     * 查找锁的位置
     */
    public function lockPosition() {
        $device_id = $this->request->post['device_id'];
        if (!$device_id) {
            $this->response->showErrorResult('参数错误或缺失',1);
        }

        $this->load->library('logic/location', true);
        $result = $this->logic_location->findDeviceCurrentLocation($device_id);
        if ($result) {
            $this->response->showSuccessResult($result, '查找成功');
        }
        $this->response->showErrorResult('查找失败');
    }

    # 单车信息（简报）
    public function getSimpleBikeInfo(){

        if(!isset($this->request->post['full_bicycle_sn']) || !$this->request->post['full_bicycle_sn']){
            $this->response->showErrorResult('单车编号不能为空！');
        }

        $this->load->library('sys_model/bicycle', true);
        $w      = array('full_bicycle_sn'=> $this->request->post['full_bicycle_sn']);
        $order  = "";
        $limit  = "0,1";
        $result = $this->sys_model_bicycle->getBicycleAndLockInfo($w,$order,$limit);
        if ($result) {
            $data = array();
            $data['bike_address'] = ""; # 怎么获取地址？
            $lock_status_arr        = get_lock_status();
            $data['lock_status']  = isset($lock_status_arr[$result[0]['lock_status']]) ? $lock_status_arr[$result[0]['lock_status']] : "";
            $data['bicycle_sn']   = $result[0]['bicycle_sn'];
            $data['lat']           = $result[0]['lat'];
            $data['lng']           = $result[0]['lng'];

            $this->response->showSuccessResult($data, '查找成功');
        }
        $this->response->showErrorResult('查找失败');

    }

    # 单车信息（详情信息）
    public function getBikeInfo(){

        if(!isset($this->request->post['full_bicycle_sn']) || !$this->request->post['full_bicycle_sn']){
            $this->response->showErrorResult('单车编号不能为空！');
        }
        $this->load->library('sys_model/bicycle', true);
        $w      = array('full_bicycle_sn'=>$this->request->post['full_bicycle_sn']);
        $order  = "";
        $limit  = "0,1";
        # 单车和锁信息；
        $bike_loke_info = $this->sys_model_bicycle->getBicycleAndLockInfo($w,$order,$limit);
        if(empty($bike_loke_info) || empty($bike_loke_info[0])){
            $this->response->showErrorResult('单车不存在');
        }
        if(abs($bike_loke_info[0]['battery']) < LOW_BATTERY_CODE){
            $bike_loke_info[0]['low_battery'] = 1;
        }else{
            $bike_loke_info[0]['low_battery'] = 0;
        }
        # 举报故障信息；
        $fault_c                    = array("bicycle_id" => $bike_loke_info[0]['bicycle_id'],"page" => 1);
        $result['fault_info']     = $this->load->controller('common/base/getBikeReportList',$fault_c);
        # 违停记录
        $result['parking_info']   =  $this->load->controller('common/base/getIllegalParking',$bike_loke_info[0]['bicycle_id'],1,1);
        # 最近使用者信息；
        $result['pre_user_info']  = $this->getBikeLastOrder($bike_loke_info[0]['bicycle_id']);


        # 判断单车的状态
        $bike_state_arr = $this->load->controller('common/base/getBikeStatus',$bike_loke_info[0]);
        # 最早举报者；
        if(!empty($result['parking_info']) || !empty($result['fault_info'])){
            if( empty($result['parking_info']) ){
                $bike_loke_info[0]['report_user_name']    = $result['fault_info'][0]['user_name'];
                $bike_loke_info[0]['report_image_url']    = $result['fault_info'][0]['fault_image'];
                $bike_loke_info[0]['report_user_mobile']  = $result['fault_info'][0]['user_mobile'];
                $bike_loke_info[0]['report_content']      = $result['fault_info'][0]['fault_content'];
                $bike_loke_info[0]['report_add_time']     = $result['fault_info'][0]['add_time'];
            }else if( empty($result['fault_info']) ){
                $bike_loke_info[0]['report_user_name']    = $result['parking_info'][0]['user_name'];
                $bike_loke_info[0]['report_image_url']    = $result['parking_info'][0]['file_image'];
                $bike_loke_info[0]['report_user_mobile']  = $result['parking_info'][0]['user_mobile'];
                $bike_loke_info[0]['report_content']      = $result['parking_info'][0]['content'];
                $bike_loke_info[0]['report_add_time']     = $result['parking_info'][0]['add_time'];
            }else if($result['fault_info'][0]['add_time'] < $result['parking_info'][0]['add_time']){
                $bike_loke_info[0]['report_user_name']    = $result['fault_info'][0]['user_name'];
                $bike_loke_info[0]['report_image_url']    = $result['fault_info'][0]['fault_image'];
                $bike_loke_info[0]['report_user_mobile']  = $result['fault_info'][0]['user_mobile'];
                $bike_loke_info[0]['report_content']      = $result['fault_info'][0]['fault_content'];
                $bike_loke_info[0]['report_add_time']     = $result['fault_info'][0]['add_time'];
            }else{
                $bike_loke_info[0]['report_user_name']    = $result['parking_info'][0]['user_name'];
                $bike_loke_info[0]['report_image_url']    = $result['parking_info'][0]['file_image'];
                $bike_loke_info[0]['report_user_mobile']  = $result['parking_info'][0]['user_mobile'];
                $bike_loke_info[0]['report_content']      = $result['parking_info'][0]['content'];
                $bike_loke_info[0]['report_add_time']     = $result['parking_info'][0]['add_time'];
            }
        }else{
            $bike_loke_info[0]['report_user_name']    = '';
            $bike_loke_info[0]['report_image_url']    = '';
            $bike_loke_info[0]['report_user_mobile']  = '';
            $bike_loke_info[0]['report_content']      = '';
            $bike_loke_info[0]['report_add_time']     = '';
        }
        #统计故障个数
        $bike_loke_info[0]['no_user_time']            = $bike_state_arr['no_user_time'];
        $bike_loke_info[0]['show_value']['code']      = $bike_state_arr['show_value']['code'];
        $bike_loke_info[0]['show_value']['value']     = $bike_state_arr['show_value']['value'];
        $bike_loke_info[0]['show_code']               = $bike_state_arr['show_code'];
        $bike_loke_info[0]['all_fault_num']           = $bike_state_arr['all_fault_num'];
        $bike_loke_info[0]['fault_report_type_name']  = $bike_state_arr['fault_report_type_name'];
        $bike_loke_info[0]['fault_time']              = (string)time();

        $result['bike_loke_info'] = $bike_loke_info[0];

        /*
        $bike_loke_info[0]['all_fault_num'] = 0; #故障个数为0；
        if(!empty($result['fault_info'])){
            $bike_loke_info[0]['fault_time'] = end($result['fault_info'])['add_time'];
            $fault_type_id_str ='';
            foreach($result['fault_info'] as $arr){
                $fault_type_id_str .= $fault_type_id_str != '' ? ','.$arr['fault_type'] : $arr['fault_type'];
            }
            $fault_type_id_arr = explode(",",$fault_type_id_str);
            $fault_type_id_arr = array_unique($fault_type_id_arr);
            if(!empty($result['parking_info'])){
                $fault_type_id_arr[] = "违停";
            }
            $bike_loke_info[0]['all_fault_num'] = count($fault_type_id_arr);
            $bike_loke_info[0]['fault_report_type_name'] = implode(",",$fault_type_id_arr);
        }else if(!empty($result['parking_info'])){
            $bike_loke_info[0]['fault_time'] = $result['parking_info'][0]['add_time'];
            $bike_loke_info[0]['fault_report_type_name'] = "违停";
        }else{
            if($bike_loke_info[0]['low_battery'] == 1){
                # 判断维护人员是否已经处理过该改低电量的单车；
                $check_arr['bicycle_id'] = $bike_loke_info[0]['bicycle_id'];
                $check_battery_result = $this->load->controller('common/base/checkLowBattery', $check_arr);
                if ($check_battery_result) {
                    $bike_loke_info[0]['fault_report_type_name'] = "低电量";
                }
            }
            $bike_loke_info[0]['fault_time'] = (string)time();
        }
        $result['bike_loke_info'] = $bike_loke_info[0];
        */
        if ($result) {
            $this->response->showSuccessResult($result, '查找成功');
        }
        $this->response->showErrorResult('查找失败');
        #var_dump($result);
        exit;

    }

    # 单车信息（详情信息）
    public function getNormalBikeInfo(){

        if(!isset($this->request->post['full_bicycle_sn']) || !$this->request->post['full_bicycle_sn']){
            $this->response->showErrorResult('单车编号不能为空！');
        }
        $this->load->library('sys_model/bicycle', true);
        $w      = array('full_bicycle_sn'=>$this->request->post['full_bicycle_sn']);
        $order  = "";
        $limit  = "0,1";
        # 单车和锁信息；
        $bike_loke_info = $this->sys_model_bicycle->getBicycleAndLockInfo($w,$order,$limit);
        if(empty($bike_loke_info)){
            $this->response->showErrorResult('单车不存在');
        }
        if(abs($bike_loke_info[0]['battery']) < LOW_BATTERY_CODE){
            $bike_loke_info[0]['battery'] = "低电量";
        }else{
            $bike_loke_info[0]['battery'] = "无";
        }
        if($bike_loke_info[0]['lock_status'] == 1){
            $bike_loke_info[0]['lock_status'] = "开锁";
        }else if($bike_loke_info[0]['lock_status'] == 2){
            $bike_loke_info[0]['lock_status'] = "锁异常";
        }else{
            $bike_loke_info[0]['lock_status'] = "关锁";
        }

        $result['bike_loke_info'] = $bike_loke_info[0];
        # 举报故障信息；
        #$result['fault_info']     = $this->getBikeReportList($result['bike_loke_info']['bicycle_id']);
        # 违停记录
        #$result['parking_info']   =  $this->load->controller('common/base/getIllegalParking',$result['bike_loke_info']['bicycle_id'],1,1);
        # 最近使用者信息；
        $result['pre_user_info']  = $this->getBikeLastOrder($result['bike_loke_info']['bicycle_id']);

        if ($result) {
            $this->response->showSuccessResult($result, '查找成功');
        }
        $this->response->showErrorResult('查找失败');
        var_dump($result);
        exit;

    }

    # 获取单车的故障举报信息；
    public function getBikeReportList($bicycle_id,$page = 1,$limit_yn = 1){

        $w           = array("bicycle_id" => $bicycle_id ,'processed' => array("gt",0));
        $order       = 'add_time DESC';
        $limit_code  = $this->config->get('config_limit_admin');
        $start       = ($page-1)*$limit_code;
        $limit       = $limit_yn == 1 ? "$start,$limit_code" : "";
        $this->load->library('sys_model/fault', true);
        $result = $this->sys_model_fault->getFaultList($w,$order,$limit);
        $data   = array();
        foreach($result as $k => $v){
            $fault_code         = explode(",",$v['fault_type']);
            $fault_type_arr     = get_fault_status();
            $fault_type         = '';
            $report_user_name   = '';
            $report_user_mobile = '';
            if(!empty($fault_code)){
                foreach($fault_code as $arr){
                    $fault_type_name = in_array($arr,array(1,2,3,4,5,6,7,8,9,10,11,12)) ? $fault_type_arr[$arr] : "";
                    $fault_type .=  $fault_type == '' ? $fault_type_name : ",".$fault_type_name;
                }
            }else{
                $fault_type = in_array($v['fault_type'],array(1,2,3,4,5,6,7,8,9,10,11,12)) ? $fault_type_arr[$v['fault_type']] : "";
            }
            if($v['user_id'] > 0){
                if($v['cooperator_id']>0){
                    $admin_info = $this->getAdminInfo($v['user_id']);
                    $report_user_name   = $admin_info['admin_name'];
                    $report_user_mobile = $admin_info['mobile']; # 暂无
                }else{
                    $user_info  = $this->getUserInfo($v['user_id']);
                    $report_user_name   = $user_info['real_name'];
                    $report_user_mobile = $user_info['mobile'];
                }
            }

            $data[$k]['fault_type_id']  = $v['fault_type'];
            $data[$k]['fault_type']     = $fault_type;
            $data[$k]['user_name']      = $report_user_name;
            $data[$k]['user_mobile']    = $report_user_mobile;
            $data[$k]['user_id']         = $v['user_id'];
            $data[$k]['cooperator_id']  = $v['cooperator_id'];
            $data[$k]['lat']             = $v['lat'];
            $data[$k]['lng']             = $v['lng'];
            $data[$k]['fault_content']  = $v['fault_content'];
            $data[$k]['fault_image']    = $v['fault_image'];
            $data[$k]['add_time']        = $v['add_time'];
            $data[$k]['address']         = "";
        }
        return $data;
    }

    # 获取单车最近的订单信息；
    public function getBikeLastOrder($bicycle_id){
        $w           = array("bicycle_id" => $bicycle_id,"order_state" => array(array("gt",0)));
        $order       = 'end_time DESC';
        $limit       = "0,1";
        $this->load->library('sys_model/orders', true);
        $this->load->library('sys_model/normal_parking', true);
        $result = $this->sys_model_orders->getOrdersList($w,$order,$limit);
        if(!empty($result)){
            $user_info = $this->getUserInfo($result[0]['user_id']);
            # 单车最后的停车照片；
            $data['parking_image'] = "";
            $data['content']        = "";
            if($result[0]['bicycle_sn'] > 0){
                $parking_info = $this->sys_model_normal_parking->getList(array("bicycle_sn" => $result[0]['bicycle_sn']), "*", "add_time DESC",1);
                if(isset($parking_info[0]) && !empty($parking_info[0])){
                    $data['parking_image'] = $parking_info[0]['parking_image'];
                    $data['content']        = $parking_info[0]['content'];;
                }
            }
            $data['user_name']  = isset($user_info['real_name']) ? $user_info['real_name'] : '';
            $data['mobile']     = isset($user_info['mobile']) ? $user_info['mobile'] : "";
            $data['user_id']    = $result[0]['user_id'];
            $data['add_time']   = $result[0]['add_time'];
            $data['end_time']   = $result[0]['end_time'];
            $data['order_id']   = $result[0]['order_id'];
            $data['end_lng']    = $result[0]['end_lng'];
            $data['end_lat']    = $result[0]['end_lat'];
        }else{
            $data['parking_image'] = "";
            $data['content']        = "";
            $data['user_name']      = "";
            $data['mobile']         = "";
            $data['user_id']        = "";
            $data['add_time']       = "";
            $data['end_time']       = "";
            $data['order_id']       = "";
            $data['end_lng']        = "";
            $data['end_lat']        = "";
        }
        return $data;
    }

    public function getUserInfo($user_id){
        if($user_id !='' && $user_id > 0 ){
            $this->load->library("sys_model/user", true);
            $w['user_id'] = $user_id;
            $result = $this->sys_model_user->getUserInfo($w);
            if(!empty($result)){
                return $result;
            }
        }
        return array();
    }

    public function getAdminInfo($admin_id){
        if($admin_id !='' && $admin_id > 0 ){
            $this->load->library("sys_model/admin", true);
            $w['admin_id'] = $admin_id;
            $result = $this->sys_model_admin->getAdminInfo($w);
            if(!empty($result)){
                return $result;
            }
        }
        return array();
    }

    /**
     * 获取开锁密钥
     */
    public function openLockSecretKey() {
        $input = $this->request->post(array('order_id', 'keySource'));

        $this->load->library('sys_model/open_lock', true);
        // 订单信息
        $condition = array(
            'open_sn_id' => $input['order_id']
        );

        $order_info = $this->sys_model_open_lock->getOrdersInfo($condition);
        // 订单不存在
        if (!$order_info || !is_array($order_info)) {
            $this->response->showErrorResult('订单不存在');
        }
        // 订单非等待开锁状态
        if ($order_info['open_state'] != -2) {
//            $this->response->showErrorResult('订单未生效或已结束');
        }

        $this->load->library('sys_model/lock', true);
        // 锁信息
        $condition = array(
            'lock_sn' => $order_info['lock_sn']
        );
        $lock_info = $this->sys_model_lock->getLockInfo($condition);
        // 车锁不存在
        if (!$lock_info || !is_array($lock_info)) {
            $this->response->showErrorResult('车锁不存在');
        }

        $data = array(
            'encrypt_key' => $lock_info['encrypt_key'],
            'password' => $lock_info['password'],
            'server_time' => time()
        );
        // 泺平锁需要配合临时key加密下
        if ($lock_info['lock_factory'] == 2) {
            if (empty($input['keySource']) || (strlen($input['keySource']) != 8)) {
                $this->response->showErrorResult('非法keySource');
            }
            // 随机索引，相当于加密的key
            $rnd = mt_rand(0, strlen($lock_info['password']) - 16);
            // 以索引作开始取16个字符串
            $pwd = substr($lock_info['password'], $rnd, 16);
            // 填充原字符
            $keySource = strtoupper($input['keySource'] . '00000000');

            $aes = new \Tool\Crypt_AES();
            $aes->set_key($pwd);
            $encryptResult = $aes->encrypt($keySource);

            $data['encrypt_key'] = $rnd + 128;
            $data['password'] = $encryptResult;
        }
        $this->response->showSuccessResult($data, '获取开锁密钥成功');
    }

    /**
     * 变成不是免费车
     * @api_param  user_name
     * @api_param  sign
     * @api_param  bicycle_sn
     */
    public function changeNotFree()
    {
        if(!isset($this->request->post['bicycle_sn']) || !$this->request->post['bicycle_sn']){
            $this->response->showErrorResult('单车编号不能为空！');
        }
        $this->load->library('sys_model/bicycle', true);
        $condition['full_bicycle_sn'] = $this->request->post['bicycle_sn'];
        $data['last_used_time'] = time();
        $result = $this->sys_model_bicycle->updateBicycle($condition,$data);
        $result ? $this->response->showSuccessResult('', '成功改变单车状态') : $this->response->showErrorResult('更新失败', 4);
    }
}
