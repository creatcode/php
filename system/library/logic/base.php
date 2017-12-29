<?php
class ControllerCommonBase extends Controller {
    private $data = array();

    public function uploadImage($files){
        $file_info['state'] = 'FAILURE';
        if (isset($files['file_image']) || isset($files['file_image'])) {
            $uploader = new Uploader(
                'file_image',
                array(
                    'allowFiles'  => array('.jpeg', '.jpg', '.png'),
                    'maxSize'     => 10 * 1024 * 1024,
                    'pathFormat'  => 'illegal_parking/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:4}'
                ),
                empty($files['file_image']) ? 'base64' : 'upload', // upload, base64 or remote
                $files //文件上传变量数组，base64的不用提供，内部直接用$_POST[字段名]作为数据
            );
            $file_info = $uploader->getFileInfo();
        }

        if ($file_info['state'] == 'SUCCESS') {
            return $file_info['url'];
        }
        return false;
    }

    # 检查单车全码是否存在
    public function checkFullBickSn($full_bicycle_sn){

        $this->load->library("sys_model/bicycle");
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('full_bicycle_sn' => $full_bicycle_sn));
        if (empty($bicycle_info)) {
            return false;
        }
        return $bicycle_info;
    }

    # 维护人员信息
    public function getAdminInfo($admin_name){
        if($admin_name !=''){
            $this->load->library("sys_model/admin", true);
            $w['admin_name'] = $admin_name;
            $result = $this->sys_model_admin->getAdminInfo($w);
            if(!empty($result)){
                return $result;
            }
        }
        return array();
    }
    # 维护人员信息
    public function getAdminInfoById($admin_id){
        if($admin_id !=''){
            $this->load->library("sys_model/admin", true);
            $w['admin_id'] = $admin_id;
            $result = $this->sys_model_admin->getAdminInfo($w);
            if(!empty($result)){
                return $result;
            }
        }
        return array();
    }

    # 违停记录
    public function getIllegalParking($bicycle_id){
        if($bicycle_id !=''){
            $this->load->library("sys_model/fault", true);
            $w['bicycle_id'] = $bicycle_id;
            $w['processed']  = array("lt",1);
            $result = $this->sys_model_fault->getIllegalParkingList($w);
            if(!empty($result)){
                foreach($result as &$v){
                    if($v['user_id'] > 0){
                        if($v['reporter_type'] == 2){
                            $admin_info = $this->getAdminInfoById($v['user_id']);
                            $report_user_name   = isset($admin_info['admin_name']) ? $admin_info['admin_name'] : "";
                            $report_user_mobile = isset($admin_info['mobile']) && $admin_info['mobile'] > 0 ? $admin_info['mobile'] : ""; # 暂无
                        }else{
                            $user_info  = $this->getUserInfo($v['user_id']);
                            $report_user_name   = isset($user_info['real_name']) ? $user_info['real_name'] : "";
                            $report_user_mobile = isset($user_info['mobile']) ? $user_info['mobile'] : "";
                        }
                    }
                    $v['user_name']   = isset($report_user_name) ? $report_user_name : "";
                    $v['user_mobile'] = isset($report_user_mobile) ? $report_user_mobile : "";
                }
                return $result;
            }
        }
        return array();
    }

    # 最早违停记录
    public function getEarliestIllegalParking($bicycle_id){
        if($bicycle_id !=''){
            $this->load->library("sys_model/fault", true);
            $w['bicycle_id'] = $bicycle_id;
            $w['processed']  = array("lt",1);
            $o = "add_time ASC";
            $limit = "0,1";
            $result = $this->sys_model_fault->getIllegalParkingList($w,$o,$limit);
            if(!empty($result)){
                return $result[0];
            }
        }
        return array();
    }

    # 获取最早的单车的故障举报信息；
    public function getEarliestBikeReportList($bicycle_id,$page = 1,$limit_yn = 1){

        $w           = array("bicycle_id" => $bicycle_id,"processed" => array("lt",1));
        $order       = 'add_time ASC';
        $limit_code  = 1;
        $start       = ($page-1)*$limit_code;
        $limit       = $limit_yn == 1 ? "$start,$limit_code" : "";
        $this->load->library('sys_model/fault', true);
        $result = $this->sys_model_fault->getFaultList($w,$order,$limit);
        $data   = array();
        foreach($result as $k => $v){
            $fault_code         = explode(",",$v['fault_type']);
            $fault_type_arr     = array(
                '1' => '二维码脱落',
                '2' => '车铃不响',
                '3' => '刹车失灵',
                '4' => '龙头歪斜',
                '5' => '车胎漏气',
                '6' => '链条掉了',
                '7' => '踏板坏了',
                '8' => '其他',
                '9' => '开不了锁',
                '10' => '关不了锁',
                '11'  => '违规停车',
                '12' => '结束不了车'
            );
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
                if($v['reporter_type'] == 2){
                    $admin_info = $this->getAdminInfoById($v['user_id']);
                    $report_user_name = isset($admin_info['admin_name']) ? $admin_info['admin_name'] : "";
                    $report_user_mobile = ''; # 暂无
                }else{
                    $user_info  = $this->getUserInfo($v['user_id']);
                    $report_user_name   = isset($user_info['real_name']) ? $user_info['real_name'] : "";
                    $report_user_mobile = isset($user_info['mobile']) ? $user_info['mobile'] : "";
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
        $re = isset($data[0]) ? $data[0] : "";
        return $re;
    }

    # 获取用户信息
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

    # 单车是否在使用中
    public function checkBikeUse($bicycle_id){
        if($bicycle_id !='' && $bicycle_id > 0 ){
            $this->load->library("sys_model/orders", true);
            $w['bicycle_id']  = $bicycle_id;
            $w['order_state'] = 1;
            $result = $this->sys_model_orders->getOrdersList($w);
            if(!empty($result)){
                return $result;
            }
        }
        return array();
    }

    # 单车信息（简报）
    public function getSimpleBikeInfo($bicycle_id){
        if($bicycle_id !='' && $bicycle_id > 0 ){
            $this->load->library('sys_model/bicycle', true);
            $w      = array('bicycle_id'=>$bicycle_id);
            $order  = "";
            $limit  = "0,1";
            $result = $this->sys_model_bicycle->getBicycleAndLockInfo($w,$order,$limit);
            if ($result) {
                $data = array();
                $data['bike_address'] = ""; # 怎么获取地址？
                $lock_status_arr        = get_lock_status();
                $data['lock_status']  = isset($lock_status_arr[$result[0]['lock_status']]) ? $lock_status_arr[$result[0]['lock_status']] : "";
                $data['bicycle_sn']   = $result[0]['bicycle_sn'];
                $data['full_bicycle_sn']   = $result[0]['full_bicycle_sn'];
                $data['lat']           = $result[0]['lat'];
                $data['lng']           = $result[0]['lng'];
                return $data;
            }
        }
        return array();
    }

    # 数字字符串转中文字符串
    public function idsChange($arr){
        if(!empty($arr) && isset($arr['ids']) &&  $arr['ids'] != '' && isset($arr['arr']) && !empty($arr['arr'])){
            $ids = $arr['ids'];
            $arr = $arr['arr'];
            $type_name_arr = explode(",",$ids);
            $type_name_str = "";
            foreach($type_name_arr as $v1){
                if(isset($arr[$v1])){
                    $type_name_str .=  $type_name_str == "" ?  $arr[$v1] : ",".$arr[$v1];
                }
            }
            return $type_name_str;
        }
        return '';
    }

    # 获取单车的故障举报信息；
    public function getBikeReportList($arr){
        $bicycle_id = $arr['bicycle_id'];
        $page       = isset($arr['page']) ? $arr['page'] : 1;
        $limit_yn   = isset($arr['limit_yn']) ? $arr['limit_yn'] : 1;
        $w["bicycle_id"] = $bicycle_id;
        $w["processed"] = array("lt",1);
        $order       = 'add_time ASC';
        $limit_code  = $this->config->get('config_limit_admin');
        $start       = ($page-1)*$limit_code;
        $limit       = $limit_yn == 1 ? "$start,$limit_code" : "";
        $this->load->library('sys_model/fault', true);
        $result = $this->sys_model_fault->getFaultList($w,$order,$limit);
        var_dump($result);
        $data   = array();
        foreach($result as $k => $v){
            $fault_code         = explode(",",$v['fault_type']);
            $fault_type_arr     = array(
                '1' => '二维码脱落',
                '2' => '车铃不响',
                '3' => '刹车失灵',
                '4' => '龙头歪斜',
                '5' => '车胎漏气',
                '6' => '链条掉了',
                '7' => '踏板坏了',
                '8' => '其他',
                '9' => '开不了锁',
                '10' => '关不了锁',
                '11'  => '违规停车',
                '12' => '结束不了车'
            );
            $fault_type         = '';
            $report_user_name   = '';
            $report_user_mobile = '';
            if(!empty($fault_code)){
                foreach($fault_code as $arr1){
                    $fault_type_name = in_array($arr1,array(1,2,3,4,5,6,7,8,9,10,11,12)) ? $fault_type_arr[$arr1] : "";
                    $fault_type .=  $fault_type == '' ? $fault_type_name : ",".$fault_type_name;
                }
            }else{
                $fault_type = in_array($v['fault_type'],array(1,2,3,4,5,6,7,8,9,10,11,12)) ? $fault_type_arr[$v['fault_type']] : "";
            }
            if($v['user_id'] > 0){
                if($v['reporter_type'] == 2){
                    $admin_info = $this->getAdminInfoById($v['user_id']);
                    $report_user_name   = is_int($admin_info['admin_name']) ? $admin_info['admin_name'] : "";
                    $report_user_mobile = ''; # 暂无
                }else{
                    $user_info  = $this->getUserInfo($v['user_id']);
                    $report_user_name   = isset($user_info['real_name']) ? $user_info['real_name'] : "";
                    $report_user_mobile = isset($user_info['mobile']) ? $user_info['mobile'] : "";
                }
            }
            $data[$k]['fault_id']        = $v['fault_id'];
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
        $w           = array("bicycle_id" => $bicycle_id,"order_state" => array("gt",0));
        $order       = 'end_time DESC';
        $limit       = "0,1";
        $this->load->library('sys_model/orders', true);
        $result = $this->sys_model_orders->getOrdersList($w,$order,$limit);
        if(!empty($result)){
            $user_info = $this->getUserInfo($result[0]['user_id']);
            $data['user_name']  = isset($user_info['real_name']) ? $user_info['real_name'] : '';
            $data['mobile']     = isset($user_info['mobile']) ? $user_info['mobile'] : "";
            $data['user_id']    = $result[0]['user_id'];
            $data['add_time']   = $result[0]['add_time'];
            $data['end_time']   = $result[0]['end_time'];
            $data['order_id']   = $result[0]['order_id'];
            return $data;
        }
        return array();
    }

    # 判断维护人员是否已经处理过该低电量的单车；
    public function checkLowBattery($arr){
        if(isset($arr['bicycle_id']) &&  $arr['bicycle_id'] > 0){
            $this->load->library('sys_model/repair', true);
            $fields = "*";
            $order  = "add_time DESC";
            $limit  = "";
            $time_code = time() - 2*60*60;
            $w      = array("bicycle_id" => $arr['bicycle_id'],"add_time" => array("gt",$time_code));
            $result = $this->sys_model_repair->getRepairList($w,$order,$limit,$fields);
            foreach($result as $v){
                if($v['repair_type']){
                    $repair_type = explode(",",$v['repair_type']);
                    if(in_array(5,$repair_type)){
                        return false;
                    }
                }
            }
        }
        return true;
    }

}