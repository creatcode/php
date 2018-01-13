<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/18
 * Time: 16:04
 * refactoring by yangjifang 1427047861@qq.com
 * 开锁订单走这一块
 * 以前的代码为什么要把下订单放到这个目录里来呢 想不通 暂且这样吧
 *
 * 关于单车的应用场景
 * 单车分为 两种类型 带锁的车 和不带锁的桩车
 * 自带锁的车 用户扫码发送单车编号 即可以骑车
 *
 * 桩车需要用桩的编号 去开车 所以有两种开车方式
 */

use Enum\ErrorCode;

class ControllerStartupOrder extends Controller
{
    protected $route = '';

    protected $data;

    protected $user;

    protected $bicycle;

    protected $lock;

    protected $region;

    public function index(){

        $continue = $this->guard();
        if(!$continue) return ;

        $this->checkParam();

        $this->checkOrderCondition();

        $this->order();

    }

    /**
     * 判断是不是下订单
     */
    protected function guard(){

        $this->route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
        $this->route = strtolower($this->route);

        if($this->route == 'operator/operator/openlock'){
            return  true;
        }
        return false;
    }

    /**
     * 下订单
     */
    protected function order(){
        $this->load->library('logic/order');
        try{
            $result = $this->logic_order->addOrder($this->user,$this->bicycle,$this->lock,$this->region);
            $this->registry->set('order_result',$result);
        }catch ( Exception $e ){
            $this->response->showErrorResult($this->language->get($e->getMessage()),$e->getCode());
        }


    }

    /**
     * 检查参数
     */
    protected function checkParam(){
        $this->data = $this->request->post(['lat','lng','bicycle_sn']);
        $require = ['lat','lng','bicycle_sn'];
        foreach($require as $pk){
            if(empty($this->data[$pk])){
                $this->response->showErrorResult($this->language->get('error_missing_parameter'),ErrorCode::ERROR_MISSING_PARAMETER);
            }
        }
    }

    /**
     * 判断下订单条件是否成立
     * 针对用户的情况 目前还不知道客户的条件是什么 暂时设定为 只要注册的用户都可以
     */
    protected function checkOrderCondition(){
        //检查用户  基于目前的情况 能够登陆到这里 就算是完成检查了
        $this->checkUser();
        $this->checkBicycle();
        $this->checkLock();
        $this->checkRegion();
    }


    /**
     * 已交押金，有余额，允许租车 押金 和余额是不同概念
     */
    protected function checkUser(){
        $logic_user = $this->registry->get('startup_user');
        $this->user = $logic_user->getUserInfo();
    }


    protected function checkBicycle(){
        $this->load->library('sys_model/bicycle');
        $this->bicycle = $this->sys_model_bicycle->getBicycleInfo(['bicycle_sn'=>$this->data['bicycle_sn']]);
        if(!$this->bicycle){
            $this->response->showErrorResult($this->language->get('error_not_find_bicycle'),ErrorCode::ERROR_NOT_FIND_BICYCLE);
        }

        //检查单车的状态
        //有两种检查方式 一种是符合什么状态 一种是不符合什么状态
        $this->registry->set('bicycle',$this->bicycle);
    }

    protected function checkLock(){
        $this->load->library('sys_model/lock');
        $this->lock = $this->sys_model_lock->getLockInfo(['lock_sn'=>$this->bicycle['lock_sn']]);
        if(!$this->lock){
            $this->response->showErrorResult($this->language->get('error_not_find_lock'),ErrorCode::ERROR_NOT_FIND_LOCK);
        }

        //检查锁的状态 电池
        $this->registry->set('lock',$this->lock);
    }

    /**
     * 检查区域
     */
    protected function checkRegion(){
        $this->load->library('sys_model/region');
        $this->region = $this->sys_model_region->getRegionInfo(['region_id'=>$this->bicycle['region_id']]);
        $this->registry->set('region',$this->region);
    }


}
