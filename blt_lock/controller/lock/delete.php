<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/28 0028
 * Time: 8:53
 */
class ControllerLockDelete extends Controller
{

    public  function index(){
        $post_data = $this->request->post(array('full_bicycle_sn'));
        if(!$post_data['full_bicycle_sn']){
            $this->response->showErrorResult('full_bicycle_sn不能为空');
        }
        $this->load->library("sys_model/bicycle");
        $bicycle_list = $this->sys_model_bicycle->getBicycleList(array('full_bicycle_sn' => array('IN',$post_data['full_bicycle_sn'])),"bicycle_id DESC",'','lock_sn');
        if(empty($bicycle_list)){
            $this->response->showErrorResult('单车不存在');
        }

        $lock_sns = array();
        foreach($bicycle_list as $v){
            $lock_sns[] = $v['lock_sn'];
        }

        $this->load->library("sys_model/bicycle_delivery");
        $where = array('full_bicycle_sn' => array('IN',$post_data['full_bicycle_sn']));
        $this->sys_model_bicycle->updateBicycle($where, array('lock_id' => 0, 'lock_sn' => ''));
        $nums = $this->sys_model_bicycle_delivery->deleteDelivery($where);

        $this->sys_model_bicycle_delivery->deleteMacTemp(array('lock_sn' => array('IN', $lock_sns)));

        if(!$nums){
            file_put_contents('ccc.txt', json_encode($lock_sns), 8);

            $this->response->showSuccessResult($nums, '删除成功');
        }else{
            $this->response->showErrorResult('没有删除');
        }

    }



}