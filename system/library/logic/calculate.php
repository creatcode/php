<?php

namespace Logic;

/**
 * 计算订单金额类
 */
class Calculate {

    private $sys_model_orders;
    private $sys_model_region;
    private $sys_model_city;
    private $sys_model_bicycle;
    private $sys_model_user;

    public function __construct($registry) {
        $this->registry = $registry;
        $this->sys_model_orders = new \Sys_Model\Orders($registry);
        $this->sys_model_city = new \Sys_Model\City($registry);
        $this->sys_model_region = new \Sys_Model\Region($registry);
        $this->sys_model_bicycle = new \Sys_Model\Bicycle($registry);
        $this->sys_model_user = new \Sys_Model\User($registry);
    }

    /**
     * 计算订单金额
     */
    public function co($order_id) {
        if(empty($order_id)){//没有订单id，直接返回0
            return 0;
        }
        $order_info = $this->get_order_info($order_id); //订单详情
        $bicycle_info = $this->get_bicycle_info($order_info['bicycle_id']);
        $region_info = $this->get_region_info($bicycle_info['region_id']);
        $city_info = $this->get_city_info($bicycle_info['city_id']);
        $co_base = array(); //计费用的基础数据，如每多少分钟多少钱，免费时间等
        if (empty($city_info)) {//如果没有城市数据，就用区域数据
            $co_base = $region_info;
        } else {
            $co_base = $city_info;
        }
        if (empty($co_base)) {//如果都没有数据，单车没得计费，直接返回0
            return 0;
        }
        if(!empty($co_base['free_start'])&&!empty($co_base['free_end'])) {//当有设置免费骑时间段，检验借车/还车任意一个动作是否在时间段内，是则免费
            $today = date("Y-m-d", time()); //今天的日期
            $start_time = strtotime($today . " " . $co_base['free_start']); //每日免费开始的时间戳
            $end_time = strtotime($today . " " . $co_base['free_end']); //每日免费结束的时间戳
            if ($start_time > $end_time) {//结束比开始大才正常，否则互换
                $temp = $co_base['free_start'];
                $co_base['free_start'] = $co_base['free_end'];
                $co_base['free_end'] = $temp;
            }
            $order_start_hourandminute = date("H:i:s", $order_info['start_time']); //订单开始的时分
            $order_end_hourandminute = empty($order_info['end_time']) ? time() : $order_info['end_time']; //订单结束的时分
            $order_end_hourandminute = date("H:i:s", $order_end_hourandminute);
            $order_start_today = strtotime($today . " " . $order_start_hourandminute); //换算成今天的开始时间
            $order_end_today = strtotime($today . " " . $order_end_hourandminute); //换算成今天的结束时间
            if ($order_start_today >= $start_time && $order_start_today <= $end_time) {//租车的时刻在免费期内，免费
                return 0;
            } else if ($order_end_today >= $start_time && $order_end_today <= $end_time) {//还车的时刻在免费期内，免费
                return 0;
            }
        }
        
        $order_amount = 0;
        $user_info = $this->sys_model_user->getUserInfo(['user_id' => $order_info['user_id']], 'card_expired_time'); //获取用户卡数据，用来判断是不是卡用户
        $duration = abs(bcsub(empty($order_info['end_time']) ? time() : $order_info['end_time'], $order_info['start_time'], 0)); //持续时间,单位秒
        $duration_min = ceil($duration / 60); //单位分钟
        if (!empty($user_info['card_expired_time']) && $user_info['card_expired_time'] >= time()) {//有卡且过期时间比当前时间大，则按月卡/年卡方式计费
            if ($duration_min <= $co_base['calculate_unit']) {//小于calculate_unit分钟，直接按calculate_unit分钟收费
                $order_amount = $co_base['cards_first_half'];
            } else {//大于calculate_unit分钟，每calculate_unit收cards_first_half元
                $unit_times = ceil(($duration_min - $co_base['calculate_unit']) / $co_base['calculate_unit']); //还有多少个calculate_unit要收费
                $order_amount = $unit_times * $co_base['cards_afterwards_half'] + $co_base['cards_first_half']; //剩下要收费+开始的第一个calculate_unit价格
            }
        } else {//没卡或者卡过期，按普通方式计费
            if ($duration_min <= $co_base['calculate_unit']) {//小于calculate_unit分钟，直接按calculate_unit分钟收费
                $order_amount = $co_base['first_half'];
            } else {//大于15分钟
                $unit_times = ceil(($duration_min - $co_base['calculate_unit']) / $co_base['calculate_unit']); //还有多少个calculate_unit要收费
                $order_amount = $unit_times * $co_base['afterwards_half'] + $co_base['first_half']; //剩下要收费+开始的第一个calculate_unit价格
            }
        }
        if ($order_amount > $co_base['consumer_limit']) {//订单金额大于最高消费，按最高消费计算
            $order_amount = $co_base['consumer_limit'];
        }
        return bcadd($order_amount,0,2);//处理成两位小数
    }

    /**
     * 获取订单数据
     */
    public function get_order_info($order_id) {
        return $this->sys_model_orders->getOrdersInfo(['order_id' => $order_id], 'user_id,bicycle_id,start_time,end_time', '');
    }

    /**
     * 获取单车数据
     */
    public function get_bicycle_info($bicycle_id) {
        return $this->sys_model_bicycle->getBicycleInfo(['bicycle_id' => $bicycle_id], 'city_id,region_id', '');
    }

    /**
     * 获取区域数据
     */
    public function get_region_info($region_id) {
        return $this->sys_model_region->getRegionInfo(['region_id' => $region_id]);
    }

    /**
     * 获取城市数据
     */
    public function get_city_info($city_id) {
        return $this->sys_model_city->getCityInfo(['city_id' => $city_id]);
    }

}
