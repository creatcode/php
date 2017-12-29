<?php
/**
 * Created by PhpStorm.
 * User: LJW
 * Date: 2017/6/22 0022
 * Time: 9:56
 */
class ControllerDeliveryDelivery extends Controller
{

    public function index(){

        $post = $this->request->post(array('pnum','cooperator_id','page','user_name'));
        if( !$post['pnum'] || !is_numeric($post['cooperator_id']) ){
            $this->response->showJsonResult('参数错误，请检查参数',0,array(),101);
        };
        if(isset($post['page']) && $post['page']){
            $page = $post['page'];
        }else{
            $page = 1;
        }
        $w = array(
            'pnum' => $post['pnum'],
            'cooperator_id' => $post['cooperator_id'] ? $post['cooperator_id'] : 0,
            'user_name' => $post['user_name'],
            'page' => $page,
        );
        $return_data =  $this->getDeliveryList($w);
        unset($w);
        $this->load->library('sys_model/bicycle');
        $this->load->library('sys_model/admin');
        $this->load->library('sys_model/cooperator');
        if(!empty($return_data['data'])){
            foreach($return_data['data'] as &$v){
                #获取单车信息
                if($v['full_bicycle_sn']){
                    $w['full_bicycle_sn'] = $v['full_bicycle_sn'];
                }else{
                    $w['bicycle_sn'] = $v['bicycle_sn'];
                }
                $bicycleInfo = $this->sys_model_bicycle->getBicycleInfo($w,'full_bicycle_sn,bicycle_sn,region_name,cooperator_id,add_time,lock_sn');
                if(isset($bicycleInfo['cooperator_id']) && $bicycleInfo['cooperator_id']){
                    $cooperator_info = $this->sys_model_cooperator->getCooperatorInfo(array('cooperator_id' => $bicycleInfo['cooperator_id']));
                }else{
                    $cooperator_info = array();
                }

                if($v['user_name']){
                    $adminInfo = $this->sys_model_admin->getAdminInfo(array('admin_name' =>$v['user_name']));
                    $v['user_name'] = $adminInfo['nickname'];
                }else{
                    $v['user_name'] = '';
                }
                $v['full_bicycle_sn'] = $bicycleInfo['full_bicycle_sn'];
                $v['bicycle_sn'] = $bicycleInfo['bicycle_sn'];
                $v['region_name'] = isset($cooperator_info['cooperator_name']) ? $cooperator_info['cooperator_name'] : '平台';
                $v['cooperator_id'] = $bicycleInfo['cooperator_id'];
                $v['lock_sn'] = $bicycleInfo['lock_sn'];
            }
        }
        $this->response->showJsonResult('成功',1,$return_data);

    }

    public function getCooperatorList(){
        $cooperators = $this->load->controller('common/base/getAllCooperators',array());
        if(empty($cooperators)){
            $this->response->showJsonResult('无相关信息',0,array(),101);
        }
        $this->response->showJsonResult('成功',1,$cooperators);
    }

    //

    public function deliverySubmit(){

        $post = $this->request->post(array('pnum','cooperator_id','bicycle_sn','full_bicycle_sn','lock_sn','user_name'));

        if( !$post['pnum'] || !is_numeric($post['cooperator_id']) || !($post['bicycle_sn'] || $post['full_bicycle_sn']) ){
            $this->response->showJsonResult('参数错误，请检查参数',0,array(),101);
        };
        if($post['full_bicycle_sn']){
            $w['full_bicycle_sn'] = $post['full_bicycle_sn'];
        }else{
            $w['bicycle_sn'] = $post['bicycle_sn'];
        }

        #判断是否已经入库；
        $this->load->library('sys_model/bicycle_delivery');
        $result = $this->sys_model_bicycle_delivery->getDeliveryInfo($w);
        if(!empty($result)){
            $this->response->showJsonResult('该单车已经出货',2,array(),104);
        }

        #获取单车信息
        $this->load->library('sys_model/bicycle');
        $bicycleInfo = $this->sys_model_bicycle->getBicycleInfo($w,'lock_sn');
        if(empty($bicycleInfo)){
            $this->response->showJsonResult('无此单车',0,array(),103);
        }
        #数据入库
        $data = $post;
        $data['lock_sn'] = $bicycleInfo['lock_sn'];
        $data['add_time'] = time();
        $result = $this->sys_model_bicycle_delivery->addDelivery($data);
        if(!$result){
            $this->response->showJsonResult('入库失败',0,array(),102);
        }
        #入库成功修改bicycle表和lock表的
        $this->load->library('sys_model/lock');
        $this->load->library('sys_model/region');
        #修改bicycle
        $up_bdata['is_activated'] = 1;
        $up_bdata['cooperator_id'] = $post['cooperator_id'];
        $cooperator_region_info = $this->sys_model_region->getCooperatorToRegionList(array('cooperator_id' => $post['cooperator_id']));
        if(!empty($cooperator_region_info) && $cooperator_region_info[0]['region_id']){
            $region_info = $this->sys_model_region->getRegionInfo(array('region_id' => $cooperator_region_info[0]['region_id']));
            $up_bdata['region_id']   = $cooperator_region_info[0]['region_id'];
            $up_bdata['region_name'] = isset($region_info['region_name']) ? $region_info['region_name'] : '';
        }else{
            $up_bdata['region_id']   = '0';
            $up_bdata['region_name'] = '平台';
        }
        $this->sys_model_bicycle->updateBicycle($w,$up_bdata);
        unset($w);
        #修改lock
        $w['lock_sn'] = $bicycleInfo['lock_sn'];
        $up_data['cooperator_id'] = $post['cooperator_id'];
        unset($up_data['is_activated']);
        $this->sys_model_lock->updateLock($w,$up_data);
        unset($w);
        # 返回数据
        $w = array(
            'pnum' => $post['pnum'],
            'cooperator_id' => $post['cooperator_id'] ? $post['cooperator_id'] : 0,
            'user_name' => $post['user_name'],
        );
        $return_data =  $this->getDeliveryList($w);
        $this->response->showJsonResult('成功',1,array('total' => $return_data['total'], 'mytotal' => $return_data['mytotal']));

    }

    public function getDeliveryList($arr){

        if( !isset($arr['pnum']) || !isset($arr['cooperator_id'])){
           return array();
        }
        $this->load->library('sys_model/bicycle_delivery');
        $w = array(
            'pnum' => $arr['pnum'],
            'cooperator_id' => $arr['cooperator_id'],
        );
        if(isset($arr['page'])){
            $limit = (($arr['page']-1)*10).',10';
            $result['page_now'] = $arr['page'];
        }else{
            $limit = '0,10';
            $result['page_now'] = 1;
        }

        $data = $this->sys_model_bicycle_delivery->getDeliveryList($w,'id DESC',$limit);
        $result['data'] = $data;

        $totals = $this->sys_model_bicycle_delivery->getTotalDeliverys($w);
        if($totals > 0){
            $result['page_total'] = ceil($totals/10);
        }else{
            $result['page_total'] = 1;
        }
        $result['total'] = $totals;

        $w['user_name'] = $arr['user_name'];
        $mytotals = $this->sys_model_bicycle_delivery->getTotalDeliverys($w);
        $result['mytotal'] = $mytotals;

        return $result;

    }
    //清除出货信息
    public function deleteDelivery() {
        $post = $this->request->post(array('did'));
        if (!$post['did']) $this->response->showErrorResult('did不能为空');
        $where = array('id' => $post['did']);
        $this->load->library('sys_model/bicycle_delivery');
        $result = $this->sys_model_bicycle_delivery->deleteDelivery($where);
        $result ? $this->response->showSuccessResult([], '删除成功') : $this->response->showErrorResult('删除失败');
    }

}
