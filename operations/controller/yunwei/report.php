<?php

/**
 * 判断是否登录，ignore为忽略列表
 * Class ControllerStartupLogin
 */
class ControllerYunWeiReport extends Controller
{

    /**
     * 获取联系方式
     */

    public function index()
    {
        $user_id = $this->request->post['user_id'];
        $sign    = $this->request->post['sign'];
        $data['user_id'] = $user_id;
        $check_result = $this->logic_user->checkUserSign($data, $sign);
        if(!$check_result['state']){
            $this->response->showErrorResult('未登录请先登录', 98);
        }


       //获取改维护人员的维修记录；


        $this->response->showSuccessResult();

    }

    public function submitReport(){

        if(!isset($this->request->post['user_name']) || empty($this->request->post['user_name'])){
            $this->response->showErrorResult('举报ID不能为空', 102);
        }
        if(!isset($this->request->post['full_bicycle_sn']) || empty($this->request->post['full_bicycle_sn'])){
            $this->response->showErrorResult('单车码不能为空', 102);
        }
        if(!isset($this->request->post['repair_type']) || empty($this->request->post['repair_type'])){
            $this->response->showErrorResult('维修方式不能为空', 102);
        }
//        if(!isset($this->request->post['fault_id']) || empty($this->request->post['fault_id'])){
//            $this->response->showErrorResult('故障记录ID不能为空', 102);
//        }

        # 处理图片；
        if(isset($this->request->files) && !empty($this->request->files)){
            $url = $this->load->controller('common/base/uploadImage',$this->request->files);
            if(!$url){
                $this->response->showErrorResult('上传图片失败，请重新上传', 101);
            }
        }else{
            $this->response->showErrorResult('请上传图片', 101);
        }

        $bicycle_info            = $this->load->controller('common/base/checkFullBickSn',$this->request->post['full_bicycle_sn']);
        $admin_info              = $this->load->controller('common/base/getAdminInfo',$this->request->post['user_name']);
        $data['admin_id']       = $admin_info['admin_id'];
        $data['bicycle_id']        = $bicycle_info['bicycle_id'];
        $data['repair_type']    = $this->request->post['repair_type'];
        $data['remarks']        = isset($this->request->post['remarks']) ? $this->request->post['remarks'] : "";
        $data['image']          = isset($url) ? $url : "";
        $data['add_time']       = time();
        # 入库；
        $this->load->library("sys_model/repair");
        $this->load->library("sys_model/fault");
        $this->load->library("sys_model/bicycle");
        $this->load->library("sys_model/lock");
        $arr['bicycle_id']  = $bicycle_info['bicycle_id'];
        $arr['page']         = 1;
        $arr['limit_yn']     = 2;
        $arr['code']         = 1;
        $fault_id_arr        = $this->load->controller('common/base/getBikeReportList',$arr);
        foreach($fault_id_arr as $v){
            $data['fault_id'] = $v['fault_id'];
            $resutl = $this->sys_model_repair->addRepair($data);
            if(!$resutl){
                $this->response->showErrorResult('数据提交失败，请重新提交', 110);
            }
            # 车修好，清除故障信息记录  # 处理 rich_fault 表；
            $this->sys_model_fault->updateFault(array("fault_id"=>$v['fault_id']),array("processed"=>1, 'handling_time' => time(), 'content' => $this->request->post['repair_type']));
            # 车修好，清除故障信息记录  # 处理 rich_bicycle 表 fault = 0；
            $up_data['fault'] = 0;
            $this->sys_model_bicycle->updateBicycle(array("bicycle_id" => $bicycle_info['bicycle_id']),$up_data);
            # 如果是结束不了订单故障则，特殊处理
            $fault_type_id_arr = explode(",",$v['fault_type_id']);
//            if(array("12",$fault_type_id_arr)){
//                $where = array("lock_sn" => $bicycle_info['lock_sn']);
//                $lock_info = $this->sys_model_lock->getLockInfo($where);
//                $fini_arr = array("device_id" => $bicycle_info['lock_sn'], "cmd" => "close", "lat" => $lock_info['lat'], 'lng' => $lock_info['lng']);
//                $this->load->library('logic/orders');
//                $result = $this->logic_orders->finishOrders($fini_arr);
//                if(!$result['state']){
//                    $this->response->showErrorResult();
//                };
//                //$fini_result = $this->load->controller('common/base/finishOrders',$fini_arr);
//            }
        }

        #违停信息记录；
        $list = $this->load->controller('common/base/getIllegalParking',$bicycle_info['bicycle_id']);
        foreach($list as $v){
            unset($data['fault_id']);
            $data['parking_id']   = $v['parking_id'];
            $re = $this->sys_model_repair->addRepair($data);
            if(!$re){
                $this->response->showErrorResult('数据提交失败，请重新提交', 110);
            }
            # 车修好，清除违停信息记录  # 处理 rich_illegal_parking 表；
            $this->sys_model_fault->updateIllegalParking(array("parking_id"=>$v['parking_id']),array("processed"=>1));
            # 车修好，清除故障信息记录  # 处理 rich_bicycle 表 illegal_parking = 0；
            $up_data['illegal_parking'] = 0;
            $this->sys_model_bicycle->updateBicycle(array("bicycle_id" => $bicycle_info['bicycle_id']),$up_data);
        }

        # 若只是低电量处理；
        if(empty($fault_id_arr) && empty($list)){
            if($this->request->post['repair_type'] != '' && $this->request->post['repair_type']){
                $resutl = $this->sys_model_repair->addRepair($data);
                if(!$resutl){
                    $this->response->showErrorResult('数据提交失败，请重新提交', 110);
                }
            }
        }

        $this->response->showSuccessResult();

    }

    public function getMyReport(){

        if(!isset($this->request->post['user_name']) || empty($this->request->post['user_name'])){
            $this->response->showErrorResult('举报ID不能为空', 102);
        }
        $this->load->library("sys_model/repair");
        $this->load->library("sys_model/fault");
        $admin_info = $this->load->controller('common/base/getAdminInfo',$this->request->post['user_name']);
        if(isset($this->request->post['page']) && $this->request->post['page']){
            $page = $this->request->post['page'];
        }else{
            $page = 1;
        }
        $fields = "*";
        $order  = "add_time DESC";
        $limit  = ($page-1) * 10 .",10";
        $result     = $this->sys_model_repair->getRepairList(array("admin_id" => $admin_info['admin_id']),$order,$limit,$fields);
        $count      = $this->sys_model_repair->getTotalRepairs(array("admin_id" => $admin_info['admin_id']));
        $re_data    = array();
        $type_arr   = array( 1 => "现场维修",2 => "返仓维修",3 => "报废回收",4 => "其他",5 => "低电量处理");
        foreach($result as $k => $v){

            $bike_info       = $this->load->controller('common/base/getSimpleBikeInfo',$v['bicycle_id']);
            $fault_type_arr  = get_fault_status();
            $type_name_str   = $this->load->controller('common/base/idsChange',array("ids" => $v['repair_type'],"arr" => $type_arr));
            if($v['fault_id'] > 0 ){
                $fault_resut = $this->sys_model_fault->getFaultInfo(array("fault_id" => $v['fault_id']));
                $fault_str   = $this->load->controller('common/base/idsChange',array("ids" => $fault_resut['fault_type'],"arr" => $fault_type_arr));
            }else if($v['parking_id'] > 0 ){
                $fault_str   = "违停";
            }else{
                if($v['repair_type'] == 5){
                    $fault_str  = "低电量";
                }else{
                    $fault_str  = "低电量";
                }
            }
            $re_data[$k]['fault_type_name'] = $fault_str;
            $re_data[$k]['add_time']         = date("Y-m-d H:i",$v['add_time']);
            $re_data[$k]['full_bicycle_sn']  = $bike_info['full_bicycle_sn'];
            $re_data[$k]['repair_type']      = $type_name_str;
            $re_data[$k]['repair_id']        = $v['repair_id'];
            $re_data[$k]['image']            = $v['image'];
        }

        $json_data['total_items_count'] = $count;
        $json_data['total_page'] = ceil($count / 10);
        $json_data['page'] = $page;
        $json_data['items'] = $re_data;
        #var_dump($re_data);
        $this->response->showSuccessResult($json_data,'数据');

    }

    public function getMyReportInfo(){
        if(!isset($this->request->post['repair_id']) || empty($this->request->post['repair_id'])){
            $this->response->showErrorResult('举报ID不能为空', 102);
        }
        $this->load->library("sys_model/repair");
        $this->load->library("sys_model/bicycle");
        $this->load->library("sys_model/fault");
        $result         = $this->sys_model_repair->getRepairInfo(array("repair_id" => $this->request->post['repair_id']));
        if(empty($result)){
            $result['repair_type']     = '';
            $result['fault_type']      = '';
            $result['end_time']        = '';
            $result['start_time']      = '';
            $result['lat']              = '';
            $result['lng']              = '';
            $result['full_bicycle_sn'] = '';
            $result['image']            = '';
            $result['remarks']          = '';
            $this->response->showSuccessResult($result,'数据');
        }
        $type_arr        = array( 1 => "现场维修",2 => "返仓维修",3 => "报废回收",4 => "其他", 5 => "低电量处理");
        $fault_type_arr  = get_fault_status();
        #var_dump($result);
        $type_name_str   = $this->load->controller('common/base/idsChange', array("ids" => $result['repair_type'], "arr" => $type_arr));
        $bicycleInfo     = $this->sys_model_bicycle->getBicycleAndLockInfo(array("bicycle_id" => $result['bicycle_id']));
        $bicycleInfo     = !empty($bicycleInfo) ? $bicycleInfo[0] : array();
        if ($result['fault_id'] > 0) {
            $fault_resut = $this->sys_model_fault->getFaultInfo(array("fault_id" => $result['fault_id']));
            $fault_str   = $this->load->controller('common/base/idsChange', array("ids" => $fault_resut['fault_type'], "arr" => $fault_type_arr));
            $start_time  = $fault_resut['add_time'] > 0 ? date("Y年m月d日 H:i", $fault_resut['add_time']) : "";
            #$lat         = $fault_resut['lat'];
            #$lng         = $fault_resut['lng'];
        } else if ($result['parking_id'] > 0) {
            $fault_str  = "违停";
            $park_resut = $this->sys_model_fault->getIllegalParkingInfo(array("parking_id" => $result['parking_id']));
            $start_time = $park_resut['add_time'] > 0 ? date("Y年m月d日 H:i", $park_resut['add_time']) : "";
            #$lat        = $park_resut['lat'];
            #$lng        = $park_resut['lng'];
        } else {
            if($result['repair_type'] == 5){
                $fault_str  = "低电量";
            }else{
                $fault_str  = "低电量";
            }
            $start_time = "";
            #$lat        = isset($bicycleInfo['lat']) ? $bicycleInfo['lat'] : "";
            #$lng        = isset($bicycleInfo['lng']) ? $bicycleInfo['lng'] : "";
        }
        $lat        = isset($bicycleInfo['lat']) ? $bicycleInfo['lat'] : "";
        $lng        = isset($bicycleInfo['lng']) ? $bicycleInfo['lng'] : "";
        $result['image']            = $result['image'] ? $result['image'] : "";
        $result['remarks']          = $result['remarks'] ? $result['remarks'] : "";
        $result['repair_type']     = $type_name_str;
        $result['fault_type']      = $fault_str;
        $result['end_time']        = $result['add_time'] > 0 ? date("Y年m月d日 H:i", $result['add_time']) : "";
        $result['start_time']      = $start_time;
        $result['lat']              = $lat;
        $result['lng']              = $lng;
        $result['full_bicycle_sn'] = $bicycleInfo['full_bicycle_sn'];

        $this->response->showSuccessResult($result,'数据');
    }


}