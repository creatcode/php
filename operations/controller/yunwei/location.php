<?php
class ControllerYunWeiLocation extends Controller {
    public function getBicycleLocation() {
        if (!isset($this->request->post['lat']) || !isset($this->request->post['lng']) || !isset($this->request->post['lat'])) {
            $this->response->showErrorResult('参数错误或缺失',1);
        }
        $this->load->library('tool/distance');
        $this->load->library('sys_model/bicycle');
        $lat = $this->request->post['lat'];
        $lng = $this->request->post['lng'];
        //距离单车点的距离（不进行坐标点的转换）
        $distance = isset($this->request->post['yn']) && $this->request->post['yn'] == 1 ? $distance_code = 1 : $distance_code = 1;
        $distance = 0.25;
        $arr = $this->tool_distance->getRange($lat, $lng, $distance);
        #$zoom = $this->request->post['zoom'];
        #if ($zoom < 15) {
            #$this->response->showErrorResult('放大倍数过小',125);
        #}

        $where = array();
        $where['l.lat'] = array(
            array('gt', $arr['min_lat']),
            array('lt', $arr['max_lat'])
        );
        $where['l.lng'] = array(
            array('gt', $arr['min_lng']),
            array('lt', $arr['max_lng'])
        );

        # 只能看到自己公司的锁
        $company_tell_phone = $this->config->get('config_phone');  #公司电话
        if(isset($_COOKIE["user_id"]) && $_COOKIE["user_id"] != ''){
            $user_info = $this->load->controller('common/base/getAdminInfoById',$_COOKIE["user_id"]);
            #若是平台的则可以看到全部
            if($user_info['cooperator_id'] != 0){
                $where['b.cooperator_id'] = $user_info['cooperator_id'];
                $this->load->library('sys_model/cooperator');
                $cooperInfo = $this->sys_model_cooperator->getCooperatorInfo(array('cooperator_id' => $user_info['cooperator_id']));
                $company_tell_phone = $cooperInfo['mobile']; #合伙人电话
            }
        }else{
            $where['b.cooperator_id'] = 99999999;
        }
        #var_dump($where);
        $result       = $this->sys_model_bicycle->getBicycleAndLockInfo($where);
        #var_dump($result);
        $re_data      = array();
        $new_data     = array();
        foreach ($result as $k=>$value) {
            $re_data[$k]['lat']                     = $value['lat'];
            $re_data[$k]['lng']                     = $value['lng'];
            $re_data[$k]['lock_id']                 = $value['lock_id'];
            $re_data[$k]['prefix_code']             = isset($value['full_bicycle_sn']) ? substr($value['full_bicycle_sn'], 0, 5) : "";
            $re_data[$k]['bicycle_id']              = $value['bicycle_id'];
            $re_data[$k]['full_bicycle_sn']         = isset($value['full_bicycle_sn']) ? $value['full_bicycle_sn'] : "";
            $re_data[$k]['lat']                     = $value['lat'];
            $re_data[$k]['lock_status_str']         = $value['lock_status'] == 1 ? "开锁" : $value['lock_status'] == 2 ? "异常" : "关锁";
            # 单车状态
            $bike_state_arr = $this->load->controller('common/base/getBikeStatus',$value);
            $re_data[$k]['all_fault_num']           = $bike_state_arr['all_fault_num'];
            $re_data[$k]['fault_report_type_name']  = $bike_state_arr['fault_report_type_name'];
            $re_data[$k]['no_user_time']            = $bike_state_arr['no_user_time'];
            $re_data[$k]['show_code']               = $bike_state_arr['show_code'];
            $re_data[$k]['show_value']['code']      = $bike_state_arr['show_value']['code'];
            $re_data[$k]['show_value']['value']     = $bike_state_arr['show_value']['value'];
            $re_data[$k]['lock_type']               = $bike_state_arr['lock_type'];
            # 若是故障则排除正常的；
            if (isset($bike_state_arr['code']) && $bike_state_arr['code'] == 1) {
                unset($code);
            } else {
                $new_data[] = $re_data[$k];
            }
        }

        # 电话号码处理；
        $return_result['list'] = $new_data;
        $return_result['company_telephone'] = $company_tell_phone;
        $this->response->showSuccessResult($return_result);
    }


    public function getBicycleLocation2() {
        if (!isset($this->request->post['lat']) || !isset($this->request->post['lng']) || !isset($this->request->post['lat'])) {
            $this->response->showErrorResult('参数错误或缺失',1);
        }
        $this->load->library('tool/distance');
        $this->load->library('sys_model/bicycle');
        $lat = $this->request->post['lat'];
        $lng = $this->request->post['lng'];
        //距离单车点的距离（不进行坐标点的转换）
        $distance = isset($this->request->post['yn']) && $this->request->post['yn'] == 1 ? $distance_code = 1 : $distance_code = 1;
        $arr = $this->tool_distance->getRange($lat, $lng, $distance);
        #$zoom = $this->request->post['zoom'];
        #if ($zoom < 15) {
        #$this->response->showErrorResult('放大倍数过小',125);
        #}
        # 只能看到自己公司的锁
        $cooperator_id = '';
        $company_tell_phone = $this->config->get('config_phone');  #公司电话
        if(isset($_COOKIE["user_id"]) && $_COOKIE["user_id"] != ''){
            $user_info = $this->load->controller('common/base/getAdminInfoById',$_COOKIE["user_id"]);
            #若是平台的则可以看到全部
            if($user_info['cooperator_id'] != 0){
                $cooperator_id = $user_info['cooperator_id'];
                $this->load->library('sys_model/cooperator');
                $cooperInfo = $this->sys_model_cooperator->getCooperatorInfo(array('cooperator_id' => $user_info['cooperator_id']));
                $company_tell_phone = $cooperInfo['mobile']; #合伙人电话
            }
        }else{
            $cooperator_id = 99999999;
        }

        $result       = $this->sys_model_bicycle->getCurrentLocationBicy($arr['min_lat'],$arr['min_lng'],$arr['max_lat'],$arr['max_lng'],'',$cooperator_id);
        //$this->response->showSuccessResult($result);
        $re_data      = array();
        $new_data     = array();
        foreach ($result as $k=>$value) {
            $re_data[$k]['lat']                     = $value['lat'];
            $re_data[$k]['lng']                     = $value['lng'];
            $re_data[$k]['lock_id']                 = $value['lock_id'];
            $re_data[$k]['prefix_code']             = isset($value['full_bicycle_sn']) ? substr($value['full_bicycle_sn'], 0, 5) : "";
            $re_data[$k]['bicycle_id']              = $value['bicycle_id'];
            $re_data[$k]['full_bicycle_sn']         = isset($value['full_bicycle_sn']) ? $value['full_bicycle_sn'] : "";
            $re_data[$k]['lat']                     = $value['lat'];
            $re_data[$k]['lock_status_str']         = $value['lock_status'] == 1 ? "开锁" : $value['lock_status'] == 2 ? "异常" : "关锁";
            # 单车状态
            $bike_state_arr = $this->load->controller('common/base/getBikeStatus2',$value);
            $re_data[$k]['all_fault_num']           = $bike_state_arr['all_fault_num'];
            $re_data[$k]['fault_report_type_name']  = $bike_state_arr['fault_report_type_name'];
            $re_data[$k]['no_user_time']            = $bike_state_arr['no_user_time'];
            $re_data[$k]['show_code']               = $bike_state_arr['show_code'];
            $re_data[$k]['show_value']['code']      = $bike_state_arr['show_value']['code'];
            $re_data[$k]['show_value']['value']     = $bike_state_arr['show_value']['value'];
            $re_data[$k]['lock_type']               = $bike_state_arr['lock_type'];
            # 若是故障则排除正常的；
            if (isset($bike_state_arr['code']) && $bike_state_arr['code'] == 1) {
                unset($code);
            } else {
                $new_data[] = $re_data[$k];
            }
        }

        # 电话号码处理；
        $return_result['list'] = $new_data;
        $return_result['company_telephone'] = $company_tell_phone;
        $this->response->showSuccessResult($return_result);
    }

}
