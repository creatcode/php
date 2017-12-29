<?php
class ControllerCommonBase extends Controller {

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
    public function getAdminInfo($admin_id){
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
            $result = $this->sys_model_fault->getIllegalParkingList($w);
            if(!empty($result)){
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
    public function getEarliestBikeReportList($bicycle_sn,$page = 1,$limit_yn = 1){

        $w           = array("bicycle_sn" => $bicycle_sn);
        $order       = 'add_time ASC';
        $limit_code  = 1;
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
                    $fault_type_name = in_array($arr,array(1,2,3,4,5,6,7,8,9,10)) ? $fault_type_arr[$arr] : "";
                    $fault_type .=  $fault_type == '' ? $fault_type_name : ",".$fault_type_name;
                }
            }else{
                $fault_type = in_array($v['fault_type'],array(1,2,3,4,5,6,7,8,9,10)) ? $fault_type_arr[$v['fault_type']] : "";
            }
            if($v['user_id'] > 0){
                if($v['cooperator_id']>0){
                    $admin_info = $this->getAdminInfo($v['user_id']);
                    $report_user_name = $admin_info['admin_name'];
                    $report_user_mobile = ''; # 暂无
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


}