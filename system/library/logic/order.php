<?php

/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/18
 * Time: 13:21
 * author yangjifang 1427047861@qq.com
 *
 * 取代原来的 orders 类
 * 原来的类方法太长了  不利于阅读 所以重新组织整理
 *
 * 用两种车 一种带锁的车 一种是桩车  带锁的车 扫码直接开锁  桩车是扫码 开桩的锁  还有一种游客 可以通过 银行卡直接扫码开车
 */

namespace Logic;

use Tool\Distance;
use Enum\OrderState;
use Enum\ErrorCode;
use Tool\IosPush;
use Enum\PushCode;
use Tool\AndroidPush;

class Order {

    private $registry;

    /**
     * 锁服发送过来的消息
     * @var
     */
    protected $data;

    /**
     * 要处理的订单
     * @var
     */
    protected $order;

    public function __construct($registry) {
        $this->registry = $registry;
        $this->sys_model_orders = new \Sys_Model\Orders($registry);
        $this->sys_model_bicycle = new \Sys_Model\Bicycle($registry);
        $this->sys_model_region = new \Sys_Model\Region($registry);
        $this->sys_model_bicycle_usage = new \Sys_Model\Bicycle_usage($registry);
        $this->sys_model_fault = new \Sys_Model\Fault($registry);
        $this->sys_model_repair = new \Sys_Model\Repair($registry);
        $this->sys_model_user = new \Sys_Model\User($registry);
        $this->sys_model_deposit = new \Sys_Model\Deposit($registry);
        $this->logic_calculate= new \Logic\Calculate($registry);
        $this->language = $registry->get('language');
        $this->event = $registry->get('event');
    }

    /**
     * 生成订单
     */
    public function addOrder($user, $bicycle, $lock, $region) {

        $data = array(
            'order_sn' => $this->make_sn($user['user_id']),
            'user_id' => $user['user_id'],
            'lock_sn' => $lock['lock_sn'],
            'lock_type' => $lock['lock_type'],
            'bicycle_id' => $bicycle['bicycle_id'],
            'bicycle_sn' => $bicycle['bicycle_sn'],
            'cooperator_id' => $bicycle['cooperator_id'],
            'user_name' => $user['real_name'],
            'region_id' => $region['region_id'],
            'code' => $region['code'],
            'region_name' => $region['region_name'],
            'add_time' => time(), //2017-12-25备注 数据库没有这个字段的
            'start_time' => time(),
        );

        $order_id = $this->sys_model_orders->addOrders($data);
        if ($order_id) {

            return callback(true, 'success_build_order', array(
                'order_id' => $order_id,
                'add_time' => $data['add_time'],
                'bicycle_sn' => $data['bicycle_sn'],
                'lock_sn' => $data['lock_sn'],
                'order_sn' => $data['order_sn'],
                'lock_type' => $data['lock_type'],
                'keep_time' => (isset($data['keep_time']) && !empty($data['keep_time'])) ? $data['keep_time'] : BOOK_EFFECT_TIME));
        } else {

            throw new \Exception('error_database_failure', ErrorCode::ERROR_DATABASE_FAILURE);
        }
    }

    /**
     * 开始骑行
     * 处理开锁命令数据
     * @param $data
     */
    public function open($data) {
        file_put_contents('../system/storage/logs/notify.post.txt', "---order open---\n", FILE_APPEND);
        $this->order = $this->sys_model_orders->getOrdersInfo(" order_state = '" . OrderState::WAITING_OPEN_LOCK . "'  AND lock_sn ='{$data['lock_sn']}'");
        if (!$this->order) {
            throw new \Exception("error_not_find_order", ErrorCode::ERROR_NOT_FIND_ORDER);
        }
        //改变订单的状态为骑行状态
        $this->order['order_state'] = OrderState::RIDING;
        $this->order['start_time'] = time();
        $this->sys_model_orders->updateOrders(['order_id' => $this->order['order_id']], $this->order);
        //改变单车的状态
        $this->sys_model_bicycle->updateBicycle(array('lock_sn' => $data['lock_sn']), array('is_using' => 1, 'last_used_time' => time()));

        $user = $this->sys_model_user->getUserInfo(['user_id' => $this->order['user_id']], 'user_id,ios_token,android_token');
        if (!empty($user['ios_token'])) {
            $exdata = [
                'order_sn' => $this->order['order_sn'],
                'order_id' => $this->order['order_id'],
                'order_state' => $this->order['order_state']
            ];
            $ios_push = new IosPush();
            $ios_push->push($user['ios_token'], $this->language->get('order_finish_success'), PushCode::ORDER_OPEN_SUCCESS, $exdata);
        }
        if (!empty($user['android_token'])) {
            $exdata = [
                'order_sn' => $this->order['order_sn'],
                'order_id' => $this->order['order_id'],
                'order_state' => $this->order['order_state'],
                'type'=>2//2 开锁推送类型
            ];
            $android_push = new AndroidPush();
            $android_push->push($user['android_token'], $exdata);
        }
    }

    /**
     * 结束骑行
     * 处理关锁命令数据
     * @param $data array [
     *   'lng':'',
     *   'lat':'',
     *   'lock_sn':''
     * ]
     */
    public function close($data) {
        file_put_contents('../system/storage/logs/notify.post.txt', "---order close---\n", FILE_APPEND);
        $this->data = $data;
        //默认都是按照最后一个取的
        $this->order = $this->sys_model_orders->getOrdersInfo(" order_state = '" . OrderState::RIDING . "'  AND lock_sn ='{$data['lock_sn']}'");
        if (!$this->order) {
            throw new \Exception("error_not_find_order", ErrorCode::ERROR_NOT_FIND_ORDER);
        }
        if (empty($this->order['end_lng']))
            $this->order['end_lng'] = $data['lng'];
        if (empty($this->order['end_lat']))
            $this->order['end_lat'] = $data['lat'];

        //更新单车信息
        $this->sys_model_bicycle->updateBicycle(array('lock_sn' => $data['lock_sn']), array('is_using' => 0, 'last_used_time' => time()));

        $line_data = array(
            'user_id' => $this->order['user_id'],
            'order_id' => $this->order['order_id'],
            'lng' => $data['lng'],
            'lat' => $data['lat'],
            'add_time' => time(),
        );
        if (abs($line_data['lat']) > 0 && abs($line_data['lng']) > 0) {
            $this->sys_model_orders->addOrderLine($line_data);
        }

        $args = [
            'user' => '',
            'order' => '',
            'bicycle' => '',
            'lock' => '',
        ];
        //看看有没有必要通过这种方式来计费
        //$this->event->trigger("order_after_riding",$args);
        //或者还是这种方式
        $this->finishOrder();
    }

    /**
     * 骑行中
     * 心跳数据 只有骑行中的车才需要心跳吧
     * @param $data  [
     *        'lock_sn'=>'',
     *        'lng'=>'',
     *        'lat'=>'',
     *  ]
     */
    public function riding($data) {
        file_put_contents('../system/storage/logs/notify.post.txt', "---order riding---\n", FILE_APPEND);
        //由于在open的时候不会发送定位数据 所以起始位置的处理要在这里
        $this->order = $this->sys_model_orders->getOrdersInfo(" order_state = '" . OrderState::RIDING . "'  AND lock_sn ='{$data['lock_sn']}'");
        if (!$this->order) {
            throw new \Exception("error_not_find_order", ErrorCode::ERROR_NOT_FIND_ORDER);
        }
        if (empty($this->order['start_lng']))
            $this->order['start_lng'] = $data['lng'];
        if (empty($this->order['start_lat']))
            $this->order['start_lat'] = $data['lat'];

        //更新单车信息
        $this->sys_model_bicycle->updateBicycle(array('lock_sn' => $data['lock_sn']), array('is_using' => 1, 'last_used_time' => time()));

        $line_data = array(
            'user_id' => $this->order['user_id'],
            'order_id' => $this->order['order_id'],
            'lng' => $data['lng'],
            'lat' => $data['lat'],
            'add_time' => time(),
        );
        if (abs($line_data['lat']) > 0 && abs($line_data['lng']) > 0) {
            $this->sys_model_orders->addOrderLine($line_data);
        }
    }

    /**
     * 关锁 结束订单
     */
    public function finishOrder() {
        //找到当前正在执行的订单
        $this->sys_model_orders->begin();
        //处理异常订单
        //计费
        $this->culcalateOrder();
        //改变订单状体
        $this->closeOrder();
        $this->sys_model_orders->commit();

        //推送
        $user = $this->sys_model_user->getUserInfo(['user_id' => $this->order['user_id']], 'user_id,ios_token,android_token');
        if (!empty($user['ios_token'])) {
            $exdata = [
                'order_sn' => $this->order['order_sn'],
                'order_id' => $this->order['order_id'],
                'order_state' => $this->order['order_state']
            ];
            $ios_push = new IosPush();
            $ios_push->push($user['ios_token'], $this->language->get('order_finish_success'), PushCode::ORDER_FINISH_SUCCESS, $exdata);
        }
        if (!empty($user['android_token'])) {
            $exdata = [
                'order_sn' => $this->order['order_sn'],
                'order_id' => $this->order['order_id'],
                'order_state' => $this->order['order_state'],
                'type'=>3//3 关锁推送类型
            ];
            $android_push = new AndroidPush();
            $android_push->push($user['android_token'], $exdata);
        }
    }

    /**
     *
     */
    protected function closeOrder() {
        $this->order['order_state'] = OrderState::FINISHED;
        $this->order['end_time'] = time();
        $this->sys_model_orders->updateOrders(['order_id' => $this->order['order_id']], $this->order);
    }

    /**
     * 计费，测试版的
     */
    protected function culcalateOrder() {
        //$this->load->library('logic/calculate');
        $this->order['order_amount'] = $this->logic_calculate->co($this->order['order_id']);
        $this->sys_model_orders->updateOrders(['order_id' => $this->order['order_id']], $this->order);
    }

    public function co() {
        $userinfo = $this->sys_model_user->getUserInfo(['user_id' => $this->order['user_id']], 'card_expired_time');//获取卡过期时间，用来判断是不是卡用户
        $order_amount=0;//订单金额
        $duration= abs(bcsub(time(), $this->order['start_time'],0));//持续时间
        $duration_min=ceil(bcdiv($duration,60));
        if (!empty($userinfo['card_expired_time']) && $userinfo['card_expired_time'] >= time()) {//有卡且过期时间比当前时间大，则按月卡/年卡方式计费
            if($duration_min<=15){//小于15分钟，直接按15分钟收费
                $order_amount=0;
            }else{//大于15分钟，每15分钟0.5元
                $order_amount=0+bcadd(($duration_min-15)/15*0.5,0,2);//时长-15的差除以15得出有多少个15分钟，再乘以价格0.5，bcadd函数处理成两位小数
            }
        } else {//没卡或者卡过期，按普通方式计费
            if($duration_min<=15){//小于15分钟，直接按15分钟收费
                $order_amount=1;
            }else{//大于15分钟
                $order_amount=1+bcadd(($duration_min-15)/15*1.5,0,2);//时长-15的差除以15得出有多少个15分钟，再乘以价格1.5，bcadd函数处理成两位小数
            }
        }
        if($order_amount>10){//订单金额大于最高消费，按最高消费计算
            $order_amount=10;
        }
    }

    public function make_sn($user_id) {
        return mt_rand(10, 99) . sprintf('%010d', time() - 946656000) . sprintf('%03d', (float) microtime() * 1000) . sprintf('%03d', (int) $user_id % 1000);
    }

}
