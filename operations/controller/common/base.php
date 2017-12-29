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
                            $report_user_name   = isset($admin_info['nickname']) ? $admin_info['nickname'] : "";
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

        $w           = array("bicycle_id" => $bicycle_id,"processed" => array("lt",1), 'fault_type' => array("neq",'12'));
        $order       = 'add_time ASC';
        $limit_code  = 1;
        $start       = ($page-1)*$limit_code;
        $limit       = $limit_yn == 1 ? "$start,$limit_code" : "";
        $this->load->library('sys_model/fault', true);
        $result = $this->sys_model_fault->getFaultList($w,$order,$limit);
        $data   = array();
        foreach($result as $k => $v){
            $fault_code         = explode(",",$v['fault_type']);
            $fault_type_arr     = $this->fault_type();
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
        $re = isset($data[0]) ? $data[0] : array();
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
        $w["fault_type"] = array("neq",'12');
        $order       = 'add_time ASC';
        $limit_code  = $this->config->get('config_limit_admin');
        $start       = ($page-1)*$limit_code;
        $limit       = $limit_yn == 1 ? "$start,$limit_code" : "";
        $this->load->library('sys_model/fault', true);
        $result = $this->sys_model_fault->getFaultList($w,$order,$limit);
        $data   = array();
        foreach($result as $k => $v){
            $fault_code         = explode(",",$v['fault_type']);
            $fault_type_arr     = $this->fault_type();
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
                    $report_user_name   = isset($admin_info['nickname']) ? $admin_info['nickname'] : "";
                    $report_user_mobile = isset($admin_info['mobile']) ? $admin_info['mobile'] : "";; # 暂无
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

    # 解密
    public function rsaDecrypt($data){

    }

    #加密
    public function rsaEncryption($data){



    }


    public function rsa($arr){
        $data = $arr['data'];
        $key = isset($arr['key']) ? $arr['key'] : "public";
        $crypt = isset($arr['crypt']) ? $arr['crypt'] : "en";
        #密钥文件的路径
        $privateKeyFilePath = DIR_APPLICATION.'rsa_private_key.pem';
        #公钥文件的路径
        $publicKeyFilePath  = DIR_APPLICATION.'rsa_public_key.pem';
        extension_loaded('openssl') or die('php需要openssl扩展支持');
        (file_exists($privateKeyFilePath) && file_exists($publicKeyFilePath))
        or die('密钥或者公钥的文件路径不正确');

        #生成Resource类型的密钥，如果密钥文件内容被破坏，openssl_pkey_get_private函数返回false
        #$privateKey = openssl_pkey_get_private(file_get_contents($privateKeyFilePath));
        #生成Resource类型的公钥，如果公钥文件内容被破坏，openssl_pkey_get_public函数返回false
        #$publicKey = openssl_pkey_get_public(file_get_contents($publicKeyFilePath));

        $pi_key =  openssl_pkey_get_private(file_get_contents($privateKeyFilePath));//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $pu_key = openssl_pkey_get_public(file_get_contents($publicKeyFilePath));//这个函数可用来判断公钥是否是可用的

        $encrypted = "";
        $decrypted = "";

        #私钥加密
        if($crypt == "en" && $key == "private"){
            openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密
            $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
            return $encrypted;
        }

        #私钥加密的内容通过公钥可用解密出来
        if($crypt != "en" && $key == "private"){
            #openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密
            #$encrypted = base64_encode($encrypted);
            openssl_public_decrypt(base64_decode($data),$decrypted,$pu_key);//私钥加密的内容通过公钥可用解密出来
            return $decrypted;
        }

        #公钥加密
        if($crypt == "en" && $key == "public"){
            openssl_public_encrypt($data,$encrypted,$pu_key);//公钥加密
            $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
            return $encrypted;
        }

        #公钥加密的内容通过私钥可用解密出来
        if($crypt != "en" && $key == "public"){
            #openssl_public_encrypt($data,$encrypted,$pu_key);//公钥加密
            #$encrypted = base64_encode($encrypted);
            openssl_private_decrypt(base64_decode($data),$decrypted,$pi_key);//私钥加密的内容通过公钥可用解密出来
            return $decrypted;
        }

        return array("privateKey",$pi_key,'publicKey',$pu_key);
    }


    /**
     * 订单完成
     * @param $data
     * @return array
     */
    function finishOrders($data) {
        $this->load->library('logic/orders');
        $result = $this->logic_orders->finishOrders($data);
        return $result;
    }


    //锁使用次数添加
    function updateUsageCount($lock_sn){

        $this->load->library('sys_model/bicycle_usage', true);
        $this->load->library('sys_model/bicycle', true);
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('lock_sn'=>$lock_sn));
        if($this->sys_model_bicycle_usage->getUsageCountInfo(array('bicycle_sn' =>$bicycle_info['bicycle_sn'],  'date'=> date('Y-m-d', time())))){//有没有添加今天的使用次数记录的锁
            $this->sys_model_bicycle_usage->updateUsageCount(array('bicycle_sn' =>$bicycle_info['bicycle_sn'],  'date'=> date('Y-m-d', time())), array('count'=> array('exp', 'count+1')));
        }else{
            $data = array(
                'date'=> date('Y-m-d', time()),
                'bicycle_sn'=> $bicycle_info['bicycle_sn'],
                'count'=> 1,
            );
            $this->sys_model_bicycle_usage->addUsageCount($data);
        }
    }

    #合伙人信息
    function getCooperatorInfo($data){
        $this->load->library('sys_model/cooperator', true);
        if(isset($data['cooperator_id']) && $data['cooperator_id'] >0 ){
            $result = $this->sys_model_cooperator->getCooperatorInfo(array('cooperator_id' => $data['cooperator_id']));
            if(!empty($result)){
                return $result;
            }
        }
        return array();
    }


    #判断单车的状态；（）
    public function getBikeStatus($bicycle_arr){

        $offline_time = OFFLINE_THRESHOLD;
        # 获取单车的所有故障记录
        $all_fault = $this->load->controller('common/base/getBikeReportList', array("bicycle_id" => $bicycle_arr['bicycle_id']));
        # 获取改单车的故障信息；
        $fault_result = $this->load->controller('common/base/getEarliestBikeReportList', $bicycle_arr['bicycle_id'], 1, 1);
        # 获取单车违停信息；
        $liestIllegalParking_result = $this->load->controller('common/base/getEarliestIllegalParking', $bicycle_arr['bicycle_id'], 1, 1);
        # 获取单车的最近使用信息
        //$bikeUsexx = $this->load->controller('common/base/getBikeLastOrder', $bicycle_arr['bicycle_id']);
        # 是否在使用
        $use_result = $this->load->controller('common/base/checkBikeUse', $bicycle_arr['bicycle_id']);
        # 判断维护人员是否已经处理过该改低电量的单车；
        $check_arr['bicycle_id'] = $bicycle_arr['bicycle_id'];
        $check_battery_result = $this->load->controller('common/base/checkLowBattery', $check_arr);
        $order_end_time = time() - 2 * 24 * 60 * 60;
        # 判断是违停还是故障；
        if (empty($fault_result) && empty($liestIllegalParking_result)) {
            $re_data['show_value']['code']       = 0;
            $re_data['show_value']['value']      = "";
            #若不是故障也不是违停则判断是否是低电量；
            if (abs($bicycle_arr['battery']) < LOW_BATTERY_CODE && $check_battery_result) {
                $re_data['show_value']['code']   = 3;
                $re_data['show_value']['value']  = "低电量";
            }
        } else if (empty($liestIllegalParking_result)) {
            $re_data['show_value']['code']       = 1;
            $re_data['show_value']['value']      = isset($fault_result['fault_type']) ? $fault_result['fault_type'] : "";
        } else if (empty($fault_result)) {
            $re_data['show_value']['code']       = 2;
            $re_data['show_value']['value']      = isset($liestIllegalParking_result['content']) ? "违停" : "违停";
        } else if ($fault_result['add_time'] < $liestIllegalParking_result['add_time']) {
            $re_data['show_value']['code']       = 1;
            $re_data['show_value']['value']      = isset($fault_result['fault_type']) ? $fault_result['fault_type'] : "";;
        } else {
            $re_data['show_value']['code']       = 2;
            $re_data['show_value']['value']      = isset($liestIllegalParking_result['content']) ? "违停" : "违停";
        }
        $re_data['lock_type']       = $bicycle_arr['lock_type'];
        #  图标优先级处理；
        if ((time() - $bicycle_arr['system_time'] > $offline_time) && !in_array((int)$bicycle_arr['lock_type'], array(2, 4, 5, 6))) {
            $re_data['show_code']       = 1;
        } else if (isset($re_data['show_value']['code']) && $re_data['show_value']['code'] > 0) {
            if($re_data['show_value']['code'] == 1){
                $re_data['show_code']   = 2;
            }else{
                $re_data['show_code']   = $re_data['show_value']['code'] == 2 ? 3 : 4;
            }
        } else if (abs($bicycle_arr['battery']) < LOW_BATTERY_CODE && $check_battery_result && in_array($bicycle_arr['lock_type'], array(2, 4, 5, 6))) {
            $re_data['show_code']           = 4;
            $re_data['show_value']['code']  = 3;
            $re_data['show_value']['value'] = "低电量";
        } else if (!empty($use_result)) {
            if (isset($this->request->post['yn']) && $this->request->post['yn'] == 2) {
                $re_data['code'] = 1;#判断要返回的是全部单车，还是只是故障的单车
            }
            $re_data['show_code'] = 6;
        } else if (!empty($bicycle_arr['last_used_time']) && $bicycle_arr['last_used_time'] < $order_end_time) {
            $no_user_time = floor((time() - $bicycle_arr['last_used_time']) / (24 * 3600));
            $no_user_time = $no_user_time > 5 ? "5+" : $no_user_time;
            $re_data['show_code'] = 5;
        }  else if( in_array($bicycle_arr['lock_type'], array(2, 4, 5, 6)) ){
            $re_data['show_code']           = 8; //蓝牙锁
            if (isset($this->request->post['yn']) && $this->request->post['yn'] == 2) {
                $re_data['code'] = 1;#判断要返回的是全部单车，还是只是故障的单车
            }
        } else {
            if (isset($this->request->post['yn']) && $this->request->post['yn'] == 2) {
                $re_data['code'] = 1;#判断要返回的是全部单车，还是只是故障的单车
            }
            $re_data['show_code'] = 7;
        }

        # 故障类型去重
        if(!empty($all_fault)){
            $fault_type_id_str ='';
            foreach($all_fault as $arr){
                $fault_type_id_str .= $fault_type_id_str != '' ? ','.$arr['fault_type'] : $arr['fault_type'];
            }
            $fault_type_id_arr = explode(",",$fault_type_id_str);
            $fault_report_type_name = empty($fault_type_id_arr) ? '' : implode(",",$fault_type_id_arr);
            $fault_type_id_arr = array_unique($fault_type_id_arr);
            if(!empty($liestIllegalParking_result)){
                $fault_type_id_arr[] = "违停";
                $fault_report_type_name = $fault_report_type_name != '' ? implode(",",$fault_type_id_arr) : "违停";
            }else{
                $fault_report_type_name = implode(",",$fault_type_id_arr);
            }
        }else if(!empty($liestIllegalParking_result)){
            $fault_type_id_arr[]    = "违停";
            $fault_report_type_name = "违停";
        }else{
            $fault_type_id_arr      = array();
            $fault_report_type_name = "";
        }

        #判断是否低电量
        if($re_data['show_value']['code'] == 3){
            $fault_report_type_name = "低电量";
        }
        #统计故障个数
        $re_data['all_fault_num']           = !empty($fault_type_id_arr) ? count($fault_type_id_arr) : 0;
        $re_data['fault_report_type_name']  = $fault_report_type_name;
        $re_data['no_user_time']            = isset($no_user_time) ? $no_user_time : "";

        return $re_data;

    }


    #判断单车的状态；（）
    public function getBikeStatus2($bicycle_arr){

        $offline_time = OFFLINE_THRESHOLD;
        # 获取单车的所有故障记录
        $all_fault                = $this->load->controller('common/base/getBikeReportList', array("bicycle_id" => $bicycle_arr['bicycle_id']));
        # 获取改单车的故障信息；
        $fault_result             = $this->load->controller('common/base/getEarliestBikeReportList', $bicycle_arr['bicycle_id'], 1, 1);
        # 获取单车违停信息；
        $illegal_parking_add_time = $bicycle_arr['illegal_parking_add_time']; //$this->load->controller('common/base/getEarliestIllegalParking', $bicycle_arr['bicycle_id'], 1, 1);
        # 获取单车的最近使用信息
        $bikeUsexx                = $bicycle_arr['use_end_time'];//$this->load->controller('common/base/getBikeLastOrder', $bicycle_arr['bicycle_id']);
        # 是否在使用
        $use_result               = $bicycle_arr['user_id'] ? true : false;//$this->load->controller('common/base/checkBikeUse', $bicycle_arr['bicycle_id']);
        # 判断维护人员是否已经处理过该改低电量的单车；
        $check_arr['bicycle_id']  = $bicycle_arr['bicycle_id'];
        $check_battery_result     = $bicycle_arr['checkLowBattery'];//$this->load->controller('common/base/checkLowBattery', $check_arr);
        $order_end_time           = time() - 2 * 24 * 60 * 60;
        # 判断是违停还是故障；
        if (empty($fault_result) && !$illegal_parking_add_time) {
            $re_data['show_value']['code']       = 0;
            $re_data['show_value']['value']      = "";
            #若不是故障也不是违停则判断是否是低电量；
            if (abs($bicycle_arr['battery']) < LOW_BATTERY_CODE && $check_battery_result) {
                $re_data['show_value']['code']   = 3;
                $re_data['show_value']['value']  = "低电量";
            }
        } else if (!$illegal_parking_add_time) {
            $re_data['show_value']['code']       = 1;
            $re_data['show_value']['value']      = isset($fault_result['fault_type']) ? $fault_result['fault_type'] : "";
        } else if (empty($fault_result)) {
            $re_data['show_value']['code']       = 2;
            $re_data['show_value']['value']      = "违停";
        } else if ($fault_result['add_time'] < $illegal_parking_add_time) {
            $re_data['show_value']['code']       = 1;
            $re_data['show_value']['value']      = isset($fault_result['fault_type']) ? $fault_result['fault_type'] : "";;
        } else {
            $re_data['show_value']['code']       = 2;
            $re_data['show_value']['value']      = "违停";
        }
        $re_data['lock_type']       = $bicycle_arr['lock_type'];
        #  图标优先级处理；
        if ((time() - $bicycle_arr['system_time'] > $offline_time) && ((int)$bicycle_arr['lock_type'] != 2)) {
            $re_data['show_code']       = 1;
        } else if (isset($re_data['show_value']['code']) && $re_data['show_value']['code'] > 0) {
            if($re_data['show_value']['code'] == 1){
                $re_data['show_code']   = 2;
            }else{
                $re_data['show_code']   = $re_data['show_value']['code'] == 2 ? 3 : 4;
            }
        } else if (abs($bicycle_arr['battery']) < LOW_BATTERY_CODE && $check_battery_result && (int)$bicycle_arr['lock_type'] != 2) {
            $re_data['show_code']           = 4;
            $re_data['show_value']['code']  = 3;
            $re_data['show_value']['value'] = "低电量";
        } else if (!empty($use_result)) {
            if (isset($this->request->post['yn']) && $this->request->post['yn'] == 2) {
                $re_data['code'] = 1;#判断要返回的是全部单车，还是只是故障的单车
            }
            $re_data['show_code'] = 6;
        } else if (!empty($bikeUsexx) && $bikeUsexx < $order_end_time) {
            $no_user_time = floor((time() - $bikeUsexx) / (24 * 3600));
            $no_user_time = $no_user_time > 5 ? "5+" : $no_user_time;
            $re_data['show_code'] = 5;
        }  else if( (int)$bicycle_arr['lock_type'] == 2 ){
            $re_data['show_code']           = 8; //蓝牙锁
            if (isset($this->request->post['yn']) && $this->request->post['yn'] == 2) {
                $re_data['code'] = 1;#判断要返回的是全部单车，还是只是故障的单车
            }
        } else {
            if (isset($this->request->post['yn']) && $this->request->post['yn'] == 2) {
                $re_data['code'] = 1;#判断要返回的是全部单车，还是只是故障的单车
            }
            $re_data['show_code'] = 7;
        }

        # 故障类型去重
        if(!empty($all_fault)){
            $fault_type_id_str ='';
            foreach($all_fault as $arr){
                $fault_type_id_str .= $fault_type_id_str != '' ? ','.$arr['fault_type'] : $arr['fault_type'];
            }
            $fault_type_id_arr = explode(",",$fault_type_id_str);
            $fault_report_type_name = empty($fault_type_id_arr) ? '' : implode(",",$fault_type_id_arr);
            $fault_type_id_arr = array_unique($fault_type_id_arr);
            if($illegal_parking_add_time){
                $fault_type_id_arr[] = "违停";
                $fault_report_type_name = $fault_report_type_name != '' ? implode(",",$fault_type_id_arr) : "违停";
            }else{
                $fault_report_type_name = implode(",",$fault_type_id_arr);
            }
        }else if($illegal_parking_add_time){
            $fault_type_id_arr[]    = "违停";
            $fault_report_type_name = "违停";
        }else{
            $fault_type_id_arr      = array();
            $fault_report_type_name = "";
        }

        #判断是否低电量
        if($re_data['show_value']['code'] == 3){
            $fault_report_type_name = "低电量";
        }
        #统计故障个数
        $re_data['all_fault_num']           = !empty($fault_type_id_arr) ? count($fault_type_id_arr) : 0;
        $re_data['fault_report_type_name']  = $fault_report_type_name;
        $re_data['no_user_time']            = isset($no_user_time) ? $no_user_time : "";

        return $re_data;

    }


    #根据经纬度判断改点属于那个区域；
    public function haveRegionIdbyLatLng($arr){
        if( !isset($arr['lat']) || !isset($arr['lng']) || empty($arr['lat']) || empty($arr['lng']) ){
            return array();
        }
        $lat = $arr['lat'];
        $lng = $arr['lng'];
        $this->load->library('sys_model/region', true);
        $region_list = $this->sys_model_region->getRegionList();
        $arr1 = array();
        foreach($region_list as $v){
            if($lat > $v['region_bounds_southwest_lat'] and $lat < $v['region_bounds_northeast_lat'] and $lng > $v['region_bounds_southwest_lng'] and $lng < $v['region_bounds_northeast_lng']){
                $arr1 =  $v;
            }
        }
        return $arr1;

    }

    public function fault_type(){
        return array(
            '1' => '二维码脱落',
            '2' => '车铃不响',
            '3' => '刹车失灵',
            '4' => '龙头歪斜',
            '5' => '车胎漏气',
            '6' => '链条坏了',
            '7' => '踏板坏了',
            '8' => '其他',
            '9' => '开不了锁',
            '10' => '关不了锁',
            '11'  => '违规停车',
            '12' => '结束不了车'
        );
    }

    public function getAllCooperators($w = array()){
        $this->load->library('sys_model/cooperator');
        $arr = $this->sys_model_cooperator->getCooperatorList($w,'add_time DESC','','cooperator_id,cooperator_name,state,add_time');
        return $arr;
    }


}
