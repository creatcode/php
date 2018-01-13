<?php
namespace Logic;

use Tool\Distance;

class Orders
{
    private $registry;

    /**
     * @var array 蓝牙锁类型集合，如果有新增可以在此处添加
     */
    private $_BLTCollections = array(2, 3, 4, 5);

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->sys_model_orders = new \Sys_Model\Orders($registry);
        $this->sys_model_bicycle = new \Sys_Model\Bicycle($registry);
        $this->sys_model_region = new \Sys_Model\Region($registry);
        $this->sys_model_bicycle_usage = new \Sys_Model\Bicycle_usage($registry);
        $this->sys_model_fault = new \Sys_Model\Fault($registry);
        $this->sys_model_repair = new \Sys_Model\Repair($registry);
        $this->sys_model_user = new \Sys_Model\User($registry);
        $this->sys_model_deposit = new \Sys_Model\Deposit($registry);
        $this->language = $registry->get('language');
    }

    /**
     * 预约单车
     * @param $data
     * @return array
     */
    public function addOrders($data)
    {
        $lock_sn = $data['lock_sn'];
        $order_info = $this->sys_model_orders->getOrdersInfo(array('lock_sn' => $lock_sn, 'order_state' => array('lt', 2)));
        $condition = array(
            'lock_sn' => $lock_sn
        );
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo($condition);
        if (empty($bicycle_info)) {
            return callback(false, 'error_bicycle_sn_nonexistence');
        }
        $condition = array(
            'region_id' => $bicycle_info['region_id']
        );
        $region = $this->sys_model_region->getRegionInfo($condition);
        if (is_array($region) && !empty($region)) {
            $region['area_code'] = sprintf('%03d%02d', $region['region_city_code'], $region['region_city_ranking']);
        }

        if (!empty($order_info) && ($order_info['add_time'] >= time() - BOOK_EFFECT_TIME)) {
            if ($data['user_id'] == $order_info['user_id']) {
                //return callback(false, 'error_repeat_book_bicycle');
            } else {
                //return callback(false, 'error_bicycle_has_book');
            }
        }

        $arr = array(
            'order_sn' => $this->make_sn($data['user_id']),
            'user_id' => $data['user_id'],
            'lock_sn' => $data['lock_sn'],
            'bicycle_id' => $bicycle_info['bicycle_id'],
            'bicycle_sn' => $bicycle_info['bicycle_sn'],
            'cooperator_id' => $bicycle_info['cooperator_id'],
            'user_name' => $data['user_name'],
            'region_id' => $region['region_id'],
            'area_code' => $region['area_code'],
            'region_name' => $region['region_name'],
            'add_time' => time(),
        );

        //免费车
        $config_free_bike_day = $this->registry->get('config')->get('config_free_bike_day') ? $this->registry->get('config')->get('config_free_bike_day') : 5;
        if ($bicycle_info['last_used_time'] < (time() - $config_free_bike_day * 24 * 60 * 60)) {
            if (isset($region['coupon_usable']) && $region['coupon_usable']) {
                $arr['is_limit_free'] = '1';
            }
        }

        if (isset($region['coupon_usable']) && !isset($arr['is_limit_free'])  && $region['coupon_usable']  && isset($data['is_month_card'])) {
            $arr['is_month_card'] = 1;
        }
	
        if (isset($data['client_version'])) $arr['client_version'] = $data['client_version'];
        if (isset($data['from_client'])) $arr['from_client'] = $data['from_client'];

        if (isset($data['order_state'])) $arr['order_state'] = $data['order_state']; //必须
//        if (isset($data['add_time'])) $arr['add_time'] = $data['add_time'];

        if (isset($data['lock_type'])) $arr['lock_type'] = $data['lock_type'];//锁类型

        if (isset($data['is_scenic'])) $arr['is_scenic'] = $data['is_scenic'];//是否景区

        if (isset($data['recharge_sn'])) $arr['recharge_sn'] = $data['recharge_sn'];//充值单号

        $order_id = $this->sys_model_orders->addOrders($arr);
        if ($order_id) {
            return callback(true, 'success_build_order', array('order_id' => $order_id, 'add_time' => $arr['add_time'], 'bicycle_sn' => $arr['bicycle_sn'], 'lock_sn' => $arr['lock_sn'], 'order_sn' => $arr['order_sn'], 'keep_time' => (isset($data['keep_time']) && !empty($data['keep_time'])) ? $data['keep_time'] : BOOK_EFFECT_TIME));
        } else {
            return callback(false, 'error_database_operation_failure');
        }
    }

    public function existsOrder($where)
    {
        $order_info = $this->sys_model_orders->getOrdersInfo($where);
        return $order_info;
    }

    /**
     *
     * 回调使订单生效
     * @param $data
     *  $data = [
     *     'device_id':'',
     *     'cmd':'',
     *     'result':'',
     *     'serialnum':'',
     *     'trade_no':''
     *     'lat':'',
     *     'lng':'',
     *  ]
     * @return array $data
     */
    public function effectOrders($data)
    {
        $device_id = $data['device_id'];
        $cmd = $data['cmd'];
        if (strtolower($cmd) == 'open' && strtolower($data['result']) == 'ok') {
            $order_info = $this->sys_model_orders->getOrdersInfo(array('order_state' => '-2', 'lock_sn' => $device_id, 'add_time' => $data['add_time']));
            if (!empty($order_info)) {
                $arr = array(
                    'start_time' => time(),
                    'order_state' => 1
                );
                if(isset($data['trade_no'])){
                    $arr['trade_no'] = $data['trade_no'];
                }
                $model_location = new \Sys_Model\Location_Records($this->registry);
                $location_info = $model_location->findLastLocation($device_id);

                if (in_array($order_info['lock_type'], $this->_BLTCollections)) {
                    $arr['start_lat'] = $data['lat'];
                    $arr['start_lng'] = $data['lng'];
                } else {
                    $arr['start_lng'] = $location_info['lng'] ? $location_info['lng'] : (isset($data['lng']) ? $data['lng'] : '');
                    $arr['start_lat'] = $location_info['lat'] ? $location_info['lat'] : (isset($data['lat']) ? $data['lat'] : '');
                }

                $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), $arr);

                if (!$update) {
                    return callback(false, 'error_update_order_state_failure');
                }

                //更新单车的使用状态，记得要在data传入bicycle_sn
                $this->sys_model_bicycle->updateBicycle(array('lock_sn' => $device_id), array('is_using' => 1, 'last_used_time' => time()));

                $line_data = array(
                    'user_id' => $order_info['user_id'],
                    'order_id' => $order_info['order_id'],
                    'lng' => in_array($order_info['lock_type'], $this->_BLTCollections) ? $data['lng'] : ($location_info['lng'] ? $location_info['lng'] : ''),
                    'lat' => in_array($order_info['lock_type'], $this->_BLTCollections) ? $data['lat'] : ($location_info['lat'] ? $location_info['lat'] : ''),
                    'add_time' => time(),
                );
                if (abs($line_data['lat']) > 0 && abs($line_data['lng']) > 0) {
                    $this->sys_model_orders->addOrderLine($line_data);
                }

                //这里没有景区的概念了 所以这条应该废弃了
                /*if ($order_info['is_scenic']) {
                    $this->insertRidesRecharge($order_info);
                    $this->changeTempRechargeStatus($order_info['recharge_sn']);
                }*/

                $output = array(
                    'order_id' => $order_info['order_id'],
                    'order_sn' => $order_info['order_sn'],
                    'cmd' => $cmd,
                    'user_id' => $order_info['user_id'],
                    'device_id' => $device_id
                );
                return callback(true, '', $output);
            }
            return callback(false, 'error_lock_order_nonexistence');
        }
        return callback(false, 'error_send_instruction');
    }
    
    private function changeTempRechargeStatus($recharge_sn, $status = 1) {
        $this->registry->get('db')->table('temp_recharge')->where(array('recharge_sn' => $recharge_sn))->update(array('used' => $status));
    }
   
    private function insertRidesRecharge($order_info) {
        $data = array(
            'order_sn' => $order_info['order_sn'],
            'recharge_sn' => $order_info['recharge_sn'],
            'add_time' => time(),
        );
        $this->registry->get('db')->table('rides_recharge')->insert($data);
    }

    /**
     * 关锁订单完成
     * @param $param array
     *      [
     *           'device_id':'',
     *           'cmd':''
     *           'lat':''
     *           'lng':''
     *      ]
     *
     * @return array
     */
    public function finishOrders($param)
    {
        $device_id = $param['device_id'];
        $cmd = $param['cmd'];

        if (strtolower($cmd) == 'closed' || strtolower($cmd) == 'normal') {
            $orders = $this->sys_model_orders->getOrdersList("(((order_state = '-3' OR order_state = '1') AND lock_type = 1) OR (order_state = '3' AND order_id <> (SELECT max(order_id) FROM rich_orders WHERE lock_sn = '{$device_id}' LIMIT 1))) AND lock_sn ='{$device_id}'");
            if (is_array($orders) && !empty($orders)) {
                foreach ($orders as $order_info) {
                    $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('order_state' => 2));

                    $faultInfo = array();
                    //订单状态为-3的处理
                    if ($order_info['order_state'] == '-3') {
                        $faultInfo = $this->sys_model_fault->getFaultInfo(array(
                            'bicycle_sn' => $order_info['bicycle_sn'],
                            'fault_type' => 12,
                            'processed' => 0,
                        ));
                        $order_end_time = $faultInfo['add_time'];
                    } elseif ($order_info['order_state'] == '3') {       // 蓝牙锁待计费
                        $order_end_time = $order_info['device_time'];
                    } else {
                        $order_end_time = time();
                    }

                    $arr = array(
                        'end_time' => $order_end_time,
                        'order_state' => 2,
                        'settlement_time' => time(),
                    );


                    try {
                        $this->sys_model_orders->begin();

                        //订单状态为-3的处理
                        if ($order_info['order_state'] == '-3') {
                            $faultData = array(
                                'handling_time' => TIMESTAMP,
                                'processed' => 1,
                                'content' => '系统检测到车锁已关，自动结束订单，并修复相关报障',
                            );

                            $repair_data = array(
                                'bicycle_id' => $order_info['bicycle_id'],
                                'repair_type' => 4,
                                'add_time' => TIMESTAMP,
                                'remarks' => '系统检测到车锁已关，自动结束订单，并修复相关报障',
                                'admin_id' => '0',
                                'admin_name' => 'system',
                                'fault_id' => $faultInfo['fault_id'],
                            );

                            $where = array(
                                'fault_id' => $faultInfo['fault_id']
                            );

                            if ($this->sys_model_fault->updateFault($where, $faultData) && $this->sys_model_repair->addRepair($repair_data)) {
                                $faultAllOk = !$this->sys_model_fault->getFaultList(array('bicycle_id' => $order_info['bicycle_id'], 'processed' => 0), null, 1);
                                if ($faultAllOk) {
                                    $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $order_info['bicycle_id']), array('fault' => 0));
                                }
                            }
                            $this->sys_model_user->updateUser(array('user_id' => $faultInfo['user_id']), array('is_freeze' => 0));
                        }
                        //开始时间为0，则赋值创建时间
                        $start_time = $order_info['start_time'] == 0 ? $order_info['start_time'] : $order_info['add_time'];
                        //结束时间为0，则开始时间结束时间赋值为0
                        $end_time = $arr['end_time'];
                        if ($end_time == 0 || $start_time > $end_time) {
                            $end_time = $start_time = 0;
                        }
                        //骑行时间
                        $riding_time = $end_time - $start_time;
                        $region_info = $this->sys_model_region->getRegionInfo(array('region_id' => $order_info['region_id']));
                        $time_recharge_unit = isset($region_info['region_charge_time']) && $region_info['region_charge_time'] > 0 ? $region_info['region_charge_time'] * 60 : TIME_CHARGE_UNIT;
                        $price_unit = isset($region_info['region_charge_fee']) ? $region_info['region_charge_fee'] : PRICE_UNIT;

                        //如果骑行时间大于1个计费单元则减去300秒
                        if ($riding_time > 3600) {
                            $riding_time = $riding_time - 300;
                        }

                        //骑行产生的金额
                        $amount = ceil($riding_time / $time_recharge_unit) * $price_unit;

                        //--------------月卡---------------
                        if (!$order_info['is_limit_free'] && $order_info['is_month_card'] && !$order_info['is_scenic']) {
                            $free_time = 7200;
                            if ($riding_time > $free_time) {
                                $riding = $riding_time - $free_time;
                                $amount = ceil($riding / $time_recharge_unit) * $price_unit;

                                $overtime_arr = array(
                                    'user_id' => $order_info['user_id'],
                                    'user_name' => $order_info['user_name'],
                                    'order_id' => $order_info['order_id'],
                                    'riding_time' => $riding,
                                    'is_crontab' => isset($data['is_cron_close']) ? 1 : 0,
                                    'close_time' => time(),
                                    'amount' => $amount,
                                    'is_normal_close' => isset($data['is_cron_close']) ? 0 : ($order_info['order_state'] == 3 ? 0 : 1)
                                );
                                $this->registry->get('db')->table('month_card_overtime')->insert($overtime_arr);
                                //实际不扣钱？
                                $amount = 0;
                            } else {
                                $amount = 0;
                            }
                        }

                        //--------------免费车------------
                        if ($order_info['is_limit_free']) {
                            $free_time = 10800;
                            if ($riding_time > $free_time) {
                                $riding = $riding_time - $free_time;
                                $amount = ceil($riding / $time_recharge_unit) * $price_unit;
                            } else {
                                $amount = 0;
                            }
                        }

                        //---------------活动价格---------------
                        $is_activity = false;
                        if (!empty($region_info) && isset($region_info['region_id'])) {
                            $activity_info = $this->sys_model_region->getRegionActivityInfo(array('region_id' => $region_info['region_id'], array('start_time' => array('elt', $end_time)), array('end_time' => array('egt', $end_time))));
                            if (!empty($activity_info)) {
                                //活动的价格
                                $is_activity = true;
                                $amount = ceil($riding_time / $time_recharge_unit) * $activity_info['price'];
                            }
                        }

                        //蓝牙锁故障订单价格
                        if ($order_info['order_state'] == '3') {
                            if (!$is_activity) $amount = 1;
                        }
                        
                        //两分钟及之内免费
                        if ($riding_time <= 120) {
                            $amount = 0;
                            $this->cancelOrderRecharge($order_info);
                        }

                        $sys_model_deposit = new \Sys_Model\Deposit($this->registry);
                        $sys_model_user = new \Sys_Model\User($this->registry);

                        //可否使用优惠券，默认可以使用
                        $coupon_usable = isset($region_info['coupon_usable']) ? $region_info['coupon_usable'] : 1;
                        //免费车产生的费用，不可使用优惠券
                        if ($order_info['is_limit_free']) {
                            $coupon_usable = 0;
                        }

                        $arr_data = array(
                            'user_id' => $order_info['user_id'],
                            'user_name' => $order_info['user_name'],
                            'amount' => $amount,
                            'order_sn' => $order_info['order_sn'],
                            'end_lat' => $param['lat'],
                            'end_lng' => $param['lng']
                        );

                        $arr['order_amount'] = $amount;
                        $arr['pay_amount'] = $amount;

                        $user_info = $sys_model_user->getUserInfo(array('user_id' => $order_info['user_id']));
                        if ($user_info['is_freeze'] == 1) {
                            $sys_model_user->updateUser(array('user_id' => $order_info['user_id']), array('is_freeze' => 0));
                        }
                        if (empty($user_info)) {
                            throw new \Exception('error_user_info');
                        }
                        //扣费金额大于骑行的费用
                        if ($user_info['available_deposit'] < $amount) {
                            $change_type = 'order_freeze';
                            $arr_data['left_amount'] = $user_info['available_deposit'];
                            $arr_data['present_amount'] = $user_info['present_amount'];
                            $arr_data['amount'] = $amount;

                            //赠送金额
                            if ($user_info['present_amount'] > 0) {
                                if ($user_info['available_deposit'] > 0) {
                                    if ($arr_data['amount'] > $user_info['available_deposit'] + $user_info['present_amount']) {
                                        $arr['present_amount'] = $user_info['present_amount'];
                                    } else {
                                        $arr['present_amount'] = $arr_data['amount'] - $user_info['available_deposit'];
                                    }
                                } else {
                                    if ($user_info['present_amount'] > $arr_data['amount']) {
                                        $arr['present_amount'] = $arr_data['amount'];
                                    } else {
                                        $arr['present_amount'] = $user_info['present_amount'];
                                    }
                                }
                            }
                        } else {
                            $change_type = 'order_pay';
                        }

                        //处理优惠券，免费车、区域优惠、月卡都不能使用优惠券
                        if ($coupon_usable  && !$order_info['is_limit_free'] && !$is_activity && $order_info['order_state'] != 3 && !$order_info['is_month_card'] && !$order_info['is_scenic']) {
                            if ($amount > 0) {
                                $sys_model_coupon = new \Sys_Model\Coupon($this->registry);
                                $coupon_info = $sys_model_coupon->getRightCoupon(array('user_id' => $order_info['user_id']));
                                if (!empty($coupon_info)) {
                                    $need_pay = false;

                                    if ($coupon_info['coupon_type'] == 1) {
                                        if ($coupon_info['number'] * 60 > $riding_time) {
                                            $arr['pay_amount'] = $arr_data['amount'] = 0;
                                        } else {
                                            $overtime = $riding_time - ($coupon_info['number'] * 60);
                                            //超时产生的金额
                                            $arr['pay_amount'] = $arr_data['amount'] = ceil($overtime / $time_recharge_unit) * $price_unit;
                                            $need_pay = true;
                                        }
                                    } //单次体验券
                                    elseif ($coupon_info['coupon_type'] == 2) {
                                        $arr['pay_amount'] = $arr_data['amount'] = 0;
                                    } //金额体验券，超出不扣，无超则扣整
                                    elseif ($coupon_info['coupon_type'] == 3) {
                                        $arr['pay_amount'] = $arr_data['amount'] = 0;
                                        if ($amount > $coupon_info['number']) {
                                            $arr['pay_amount'] = $arr_data['amount'] = $amount - $coupon_info['number'];
                                            $need_pay = true;
                                        }
                                    } elseif ($coupon_info['coupon_type'] == 4) {
                                        $arr['pay_amount'] = $arr_data['amount'] = $amount * ($coupon_info['number'] / 10);
                                        if ($amount > 0) {
                                            $need_pay = true;
                                        }
                                    }

                                    //防止免费扣优惠券
                                    if ($amount != 0) {
                                        $coupon_info['order_id'] = $order_info['order_id'];
                                        //更新优惠券的信息
                                        $update = $sys_model_coupon->dealCoupon($coupon_info);
                                        if ($update) {
                                            $arr['coupon_id'] = $coupon_info['coupon_id'];
                                        }
                                    }

                                    //重新判断类型
                                    if ($user_info['available_deposit'] < $arr_data['amount']) {
                                        $change_type = 'order_freeze';
                                        $arr_data['left_amount'] = $user_info['available_deposit'];
                                        $arr_data['present_amount'] = $user_info['present_amount'];

                                        //赠送金额
                                        if ($user_info['present_amount'] > 0) {
                                            if ($user_info['available_deposit'] > 0) {
                                                if ($arr_data['amount'] > $user_info['available_deposit'] + $user_info['present_amount']) {
                                                    $arr['present_amount'] = $user_info['present_amount'];
                                                } else {
                                                    $arr['present_amount'] = $arr_data['amount'] - $user_info['available_deposit'];
                                                }
                                            } else {
                                                if ($user_info['present_amount'] > $arr_data['amount']) {
                                                    $arr['present_amount'] = $arr_data['amount'];
                                                } else {
                                                    $arr['present_amount'] = $user_info['present_amount'];
                                                }
                                            }
                                        }
                                        //$arr_data['amount'] = $amount;
                                    } else {
                                        $change_type = 'order_pay';
                                    }

                                    if ($need_pay) {
                                        $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                                        if (!$insert_id) {
                                            throw new \Exception('error_insert_order_amount');
                                        }
                                    }
                                } else {
                                    $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                                    if (!$insert_id) {
                                        throw new \Exception('error_insert_order_amount');
                                    }
                                }
                            }
                        } else {
                            if ($amount > 0) {
                                $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                                if (!$insert_id) {
                                    throw new \Exception('error_insert_order_amount');
                                }
                            }
                        }
                        //轨迹
                        $line_data = array(
                            'user_id' => $order_info['user_id'],
                            'order_id' => $order_info['order_id'],
                            'lng' => $param['lng'],
                            'lat' => $param['lat'],
                            'add_time' => time(),
                        );
                        $this->sys_model_orders->addOrderLine($line_data);
                        $order_lines = $this->sys_model_orders->getOrderLine(array('order_id' => $order_info['order_id']));
                        $tool_distance = new Distance();
                        $distance = $tool_distance->sumDistance($order_lines);
                        $distance = round($distance * 1000, -1);

                        $arr['distance'] = $distance;
                        $arr['end_lat'] = isset($param['lat']) ? $param['lat'] : '';
                        $arr['end_lng'] = isset($param['lng']) ? $param['lng'] : '';

                        //更新订单状态
                        $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), $arr);
                        if (!$update) {
                            throw new \Exception('error_update_order_state_failure');
                        }
                        //单车表的lock_sn应该加了索引，所以使用此字段来更新
                        $this->sys_model_bicycle->updateBicycle(array('lock_sn' => $device_id), array('is_using' => 0, 'last_used_time' => time()));

                        //更新用户所在的区域表，后面用坐标
                        if (!$user_info['cooperator_id']) {
                            $sys_model_user->updateUser(array('user_id' => $order_info['user_id']), array('cooperator_id' => $order_info['cooperator_id']));
                        }

                        $this->sys_model_orders->commit();

                        $data = array(
                            'cmd' => 'close',
                            'order_sn' => $order_info['order_sn'],
                            'user_id' => $order_info['user_id'],
                            'device_id' => $device_id
                        );
                    } catch (\Exception $e) {
                        $this->sys_model_orders->rollback();
                        return callback(false, $e->getMessage());
                    }

                    // 增加信用分
                    $this->registry->get('load')->library('logic/credit', true);
                    $this->registry->get('logic_credit')->addCreditPointOnFinishCycling($order_info['user_id']);

                    //增加使用次数
                    $this->updateUsageCount($data['device_id']);
                }
                return callback(true, '', $data);
            }
        }
        return callback(false, 'data_error', $param);
    }

    /**
     * 关锁订单完成
     * @param $param
     * @return array
     */
    public function finishOrders1($param)
    {
        $device_id = $param['device_id'];
        $cmd = $param['cmd'];

        if (strtolower($cmd) == 'close' || strtolower($cmd) == 'normal') {
            $orders = $this->sys_model_orders->getOrdersList("(order_state in ('1', '3')) AND lock_sn ='{$device_id}'");
            if (is_array($orders) && !empty($orders)) {
                foreach ($orders as $order_info) {
                    $faultInfo = array();
                    //订单状态为-3的处理
                    if ($order_info['order_state'] == '-3') {
                        $faultInfo = $this->sys_model_fault->getFaultInfo(array(
                            'bicycle_sn' => $order_info['bicycle_sn'],
                            'fault_type' => 12,
                            'processed' => 0,
                        ));
                        $order_end_time = $faultInfo['add_time'];
                    } elseif ($order_info['order_state'] == '3') {       // 蓝牙锁待计费
                        $order_end_time = $order_info['device_time'];
                    } else {
                        $order_end_time = time();
                    }

                    $arr = array(
                        'end_time' => $order_end_time,
                        'order_state' => 2,
                        'settlement_time' => time(),
                    );

                    try {
                        $this->sys_model_orders->begin();

                        //订单状态为-3的处理
                        if ($order_info['order_state'] == '-3') {
                            $faultData = array(
                                'handling_time' => TIMESTAMP,
                                'processed' => 1,
                                'content' => '系统检测到车锁已关，自动结束订单，并修复相关报障',
                            );

                            $repair_data = array(
                                'bicycle_id' => $order_info['bicycle_id'],
                                'repair_type' => 4,
                                'add_time' => TIMESTAMP,
                                'remarks' => '系统检测到车锁已关，自动结束订单，并修复相关报障',
                                'admin_id' => '0',
                                'admin_name' => 'system',
                                'fault_id' => $faultInfo['fault_id'],
                            );

                            $where = array(
                                'fault_id' => $faultInfo['fault_id']
                            );

                            if ($this->sys_model_fault->updateFault($where, $faultData) && $this->sys_model_repair->addRepair($repair_data)) {
                                $faultAllOk = !$this->sys_model_fault->getFaultList(array('bicycle_id' => $order_info['bicycle_id'], 'processed' => 0), null, 1);
                                if ($faultAllOk) {
                                    $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $order_info['bicycle_id']), array('fault' => 0));
                                }
                            }
                            $this->sys_model_user->updateUser(array('user_id' => $faultInfo['user_id']), array('is_freeze' => 0));
                        }
                        //开始时间为0，则赋值创建时间
                        $start_time = $order_info['start_time'] == 0 ? $order_info['start_time'] : $order_info['add_time'];
                        //结束时间为0，则开始时间结束时间赋值为0
                        $end_time = $arr['end_time'];
                        if ($end_time == 0 || $start_time > $end_time) {
                            $end_time = $start_time = 0;
                        }
                        //骑行时间
                        $riding_time = $end_time - $start_time;
                        $region_info = $this->sys_model_region->getRegionInfo(array('region_id' => $order_info['region_id']));
                        $time_recharge_unit = isset($region_info['region_charge_time']) && $region_info['region_charge_time'] > 0 ? $region_info['region_charge_time'] * 60 : TIME_CHARGE_UNIT;
                        //如果骑行时间大于1个计费单元则减去300秒
                        if ($riding_time > $time_recharge_unit) {
                            $riding_time = $riding_time - 300;
                        }

                        $price_unit = isset($region_info['region_charge_fee']) ? $region_info['region_charge_fee'] : PRICE_UNIT;
                        //骑行产生的金额
                        $amount = ceil($riding_time / $time_recharge_unit) * $price_unit;

                        //--------------免费车------------
                        if ($order_info['is_limit_free'] && !$order_info['is_scenic']) {
                            $free_time = 10800;
                            if ($riding_time > $free_time) {
                                $riding = $riding_time - $free_time;
                                $amount = ceil($riding / $time_recharge_unit) * $price_unit;
                            } else {
                                $amount = 0;
                            }
                        }
            		
                        //---------------活动价格---------------
                        if (!empty($region_info) && isset($region_info['region_id'])) {
                            $activity_info = $this->sys_model_region->getRegionActivityInfo(array('region_id' => $region_info['region_id'], array('start_time' => array('elt', $end_time)), array('end_time' => array('egt', $end_time))));
                            if (!empty($activity_info)) {
                                //活动的价格
                                $amount = ceil($riding_time / $time_recharge_unit) * $activity_info['price'];
                            }
                        }

                        //蓝牙锁故障订单价格
                        if ($order_info['order_state'] == '3') {
                            //$amount = 1;
                        }
                        
                        //两分钟及之内免费
                        if ($riding_time <= 120) {
                            $amount = 0;
                        }

                        $sys_model_deposit = new \Sys_Model\Deposit($this->registry);
                        $sys_model_user = new \Sys_Model\User($this->registry);

                        //可否使用优惠券，默认可以使用
                        $coupon_usable = isset($region_info['coupon_usable']) ? $region_info['coupon_usable'] : 1;

                        $arr_data = array(
                            'user_id' => $order_info['user_id'],
                            'user_name' => $order_info['user_name'],
                            'amount' => $amount,
                            'order_sn' => $order_info['order_sn'],
                            'end_lat' => $param['lat'],
                            'end_lng' => $param['lng']
                        );

                        $arr['order_amount'] = $arr['pay_amount'] = $amount;

                        $user_info = $sys_model_user->getUserInfo(array('user_id' => $order_info['user_id']));
                        if ($user_info['is_freeze'] == 1) {
                            $sys_model_user->updateUser(array('user_id' => $order_info['user_id']), array('is_freeze' => 0));
                        }
                        if (empty($user_info)) {
                            throw new \Exception('error_user_info');
                        }
                        //扣费金额大于骑行的费用
                        if ($user_info['available_deposit'] < $amount) {
                            $change_type = 'order_freeze';
                            $arr_data['left_amount'] = $user_info['available_deposit'];
                            $arr_data['amount'] = $amount;
                        } else {
                            $change_type = 'order_pay';
                        }
                        //处理优惠券

                        if ($coupon_usable && !$order_info['is_limit_free']) {
                            if ($amount > 0) {
                                $sys_model_coupon = new \Sys_Model\Coupon($this->registry);
                                $coupon_info = $sys_model_coupon->getRightCoupon(array('user_id' => $order_info['user_id']));
                                if (!empty($coupon_info)) {
                                    $need_pay = false;

                                    if ($coupon_info['coupon_type'] == 1) {
                                        if ($coupon_info['number'] * 60 > $riding_time) {
                                            $arr['pay_amount'] = $arr_data['amount'] = 0;
                                        } else {
                                            $overtime = $riding_time - ($coupon_info['number'] * 60);
                                            //超时产生的金额
                                            $arr['pay_amount'] = $arr_data['amount'] = ceil($overtime / $time_recharge_unit) * $price_unit;
                                            $need_pay = true;
                                        }
                                    } //单次体验券
                                    elseif ($coupon_info['coupon_type'] == 2) {
                                        $arr['pay_amount'] = $arr_data['amount'] = 0;
                                    } //金额体验券，超出不扣，无超则扣整
                                    elseif ($coupon_info['coupon_type'] == 3) {
                                        $arr['pay_amount'] = $arr_data['amount'] = 0;
                                        if ($amount > $coupon_info['number']) {
                                            $arr['pay_amount'] = $arr_data['amount'] = $amount - $coupon_info['number'];
                                            $need_pay = true;
                                        }
                                    } elseif ($coupon_info['coupon_type'] == 4) {
                                        $arr['pay_amount'] = $arr_data['amount'] = $amount * ($coupon_info['number'] / 10);
                                        if ($amount > 0) {
                                            $need_pay = true;
                                        }
                                    }

                                    //防止免费扣优惠券
                                    if ($amount != 0) {
                                        $coupon_info['order_id'] = $order_info['order_id'];
                                        //更新优惠券的信息
                                        $update = $sys_model_coupon->dealCoupon($coupon_info);
                                        if ($update) {
                                            $arr['coupon_id'] = $coupon_info['coupon_id'];
                                        }
                                    }

                                    //重新判断类型
                                    if ($user_info['available_deposit'] < $arr_data['amount']) {
                                        $change_type = 'order_freeze';
                                        $arr_data['left_amount'] = $user_info['available_deposit'];
                                        //$arr_data['amount'] = $amount;
                                    } else {
                                        $change_type = 'order_pay';
                                    }

                                    if ($need_pay) {
                                        $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                                        if (!$insert_id) {
                                            throw new \Exception('error_insert_order_amount');
                                        }
                                    }
                                } else {
                                    $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                                    if (!$insert_id) {
                                        throw new \Exception('error_insert_order_amount');
                                    }
                                }
                            }
                        } else {
                            if ($amount > 0) {
                                $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                                if (!$insert_id) {
                                    throw new \Exception('error_insert_order_amount');
                                }
                            }
                        }
                        //轨迹
                        $line_data = array(
                            'user_id' => $order_info['user_id'],
                            'order_id' => $order_info['order_id'],
                            'lng' => $param['lng'],
                            'lat' => $param['lat'],
                            'add_time' => time(),
                        );
                        $this->sys_model_orders->addOrderLine($line_data);
                        $order_lines = $this->sys_model_orders->getOrderLine(array('order_id' => $order_info['order_id']));
                        $tool_distance = new Distance();
                        $distance = $tool_distance->sumDistance($order_lines);
                        $distance = round($distance * 1000, -1);

                        $arr['distance'] = $distance;
                        $arr['end_lat'] = isset($param['lat']) ? $param['lat'] : '';
                        $arr['end_lng'] = isset($param['lng']) ? $param['lng'] : '';

                        //更新订单状态
                        $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), $arr);
                        if (!$update) {
                            throw new \Exception('error_update_order_state_failure');
                        }
                        //单车表的lock_sn应该加了索引，所以使用此字段来更新
                        $this->sys_model_bicycle->updateBicycle(array('lock_sn' => $device_id), array('is_using' => 0, 'last_used_time' => time()));

                        //更新用户所在的区域表，后面用坐标
                        if (!$user_info['cooperator_id']) {
                            $sys_model_user->updateUser(array('user_id' => $order_info['user_id']), array('cooperator_id' => $order_info['cooperator_id']));
                        }

                        $this->sys_model_orders->commit();

                        $data = array(
                            'cmd' => 'close',
                            'order_sn' => $order_info['order_sn'],
                            'user_id' => $order_info['user_id'],
                            'device_id' => $device_id
                        );
                    } catch (\Exception $e) {
                        $this->sys_model_orders->rollback();
                        return callback(false, $e->getMessage());
                    }

                    // 增加信用分
                    $this->registry->get('load')->library('logic/credit', true);
                    $this->registry->get('logic_credit')->addCreditPointOnFinishCycling($order_info['user_id']);

                    //增加使用次数
                    $this->updateUsageCount($data['device_id']);
                }
                return callback(true, '', $data);
            }
        }
        return callback(false, 'data_error', $param);
    }

    public function recordLine($data)
    {
        return $this->addOrderLine($data);
    }

    public function addOrderLine($data)
    {
        $arr = array(
            'user_id' => $data['user_id'],
            'order_id' => $data['order_id'],
            'lng' => $data['lng'],
            'lat' => $data['lat'],
            'add_time' => time(),
            'status' => $data['status']
        );
        return $this->sys_model_orders->addOrderLine($arr);
    }

    /**
     * 需要获取锁的合伙人ID，做到互不影响
     * @param $lock_sn
     */
    public function getFreeAmount($lock_sn)
    {
        if (!is_object($this->registry->get('sys_model_lock'))) {
            $this->registry->get('load')->library('sys_model/lock');
        }
        if (!is_object($this->registry->get('sys_model_free_ride'))) {
            $this->registry->get('load')->library('sys_model/free_ride');
        }
        $lock_info = $this->registry->get('sys_model_lock')->getLockInfo(array('lock_sn' => $lock_sn));
        $cooperator_id = $lock_info['cooperator_id'];
        $free_time_info = $this->registry->get('sys_model_free_ride')->getFreeTimeInfo(array('cooperator_id' => $cooperator_id));
    }

    public function make_sn($user_id)
    {
        return mt_rand(10, 99) . sprintf('%010d', time() - 946656000) . sprintf('%03d', (float)microtime() * 1000) . sprintf('%03d', (int)$user_id % 1000);
    }

    public function getOrdersByUserId($user_id, $page, $left_join_coupon = false)
    {
        if ($left_join_coupon) {
            $field = 'orders.*,coupon.coupon_type,coupon.number';
            $join = array('coupon' => 'coupon.coupon_id=orders.coupon_id');
        } else {
            $field = '*';
            $join = array();
        }
        $limit = (empty($page) || $page < 1) ? 10 : (10 * ($page - 1) . ', 10');
        $orders = $this->sys_model_orders->getOrdersList(array('orders.user_id' => $user_id, '_string' => 'orders.order_state =\'2\' OR orders.order_state=\'3\''), 'orders.add_time DESC', $limit, $field, $join);
        foreach ($orders as &$order) {
            $order['duration'] = $order['order_state'] == 2 ? $this->_getOrderDuration($order) : 0;
            $order['distance'] = $order['order_state'] == 2 ? round($order['distance'] / 1000.0, 2) : 0;
        }
        return $orders;
    }

    public function getPreOrdersByUserId($user_id, $page) {
        $limit = (empty($page) || $page < 1) ? 10 : (10 * ($page - 1) . ', 10');
        $orders = $this->sys_model_orders->getOrdersList(array('user_id' => $user_id, 'order_state' => 3), 'add_time DESC', $limit);
        return $orders;
    }

    public function getPreOrdersCountByUserId($user_id) {
        return $this->sys_model_orders->getTotalOrders(array('user_id' => $user_id, 'order_state' => 3));
    }

    private function _getOrderDuration(&$order)
    {
        //修正预约有效期内没有取消预约的订单的数据
        if ($order['order_state'] == 0 && $order['start_time'] == 0 && $order['end_time'] == 0 && time() > ($order['add_time'] + BOOK_EFFECT_TIME)) {
            $order['order_state'] = -1;
            $order['end_time'] = $order['add_time'] + BOOK_EFFECT_TIME;
        }

        //计算的结束时间
        $end_time = ($order['order_state'] == 2 || $order['order_state'] == -1) ? $order['end_time'] : time();
        $duration = $end_time - ($order['order_state'] <= 0 ? $order['add_time'] : $order['start_time']);
        if ($duration < 0) {
            $duration = 0;
        }
        return ceil($duration / 60.0);
    }

    public function getOrdersCountByUserId($user_id)
    {
        return $this->sys_model_orders->getTotalOrders(array('user_id' => $user_id));
    }

    public function getOrderDetail($order_id)
    {
        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $order_id));
        if (empty($order_info)) {
            return $order_info;
        }

        if (($order_info['order_state'] == 2 && $order_info['end_time'] == 0) || ($order_info['order_state'] == 2 && $order_info['start_time'] > $order_info['end_time'])) {
            $order_info['end_time'] = $order_info['start_time'];
        }

        $order_info['duration'] = ($order_info['end_time'] > $order_info['start_time']) ? ceil(($order_info['end_time'] - $order_info['start_time']) / 60.0) : 0;
        $order_info['distance'] = round($order_info['distance'] / 1000.0, 2);
        $order_info['calorie'] = round(60 * $order_info['distance'] * 1.036, 2);
        $order_info['emission'] = $order_info['distance'] ? round($order_info['distance'] * 0.275 * 1000) : 0;

        $coupon_info = array();

        if ($order_info['coupon_id']) {
            $sys_model_coupon = new \Sys_Model\Coupon($this->registry);
            $coupon_info = $sys_model_coupon->getCouponInfo(array('coupon_id' => $order_info['coupon_id']), 'coupon_id,number,failure_time,coupon_type');
            if ($coupon_info['coupon_type'] == 1) {
                $show_hour = false;
                if ($coupon_info['number'] / 60 >= 1) $show_hour = true;//半小时取整
                $coupon_info['number'] = $show_hour ? round($coupon_info['number'] / 60, 2) : $coupon_info['number'];
                $coupon_info['unit'] = $show_hour ? $this->language->get('text_hour') : $this->language->get('text_minute');
            } elseif ($coupon_info['coupon_type'] == 2) {
                $coupon_info['unit'] = $this->language->get('text_time_unit');
            } elseif ($coupon_info['coupon_type'] == 3) {
                $coupon_info['unit'] = $this->language->get('text_money_unit');
            } elseif ($coupon_info['coupon_type'] == 4) {
                $coupon_info['unit'] = $this->language->get('text_discount_unit');
            }
            $coupon_info = array($coupon_info);
        }

//        $order_info['pay_amount'] = $order_info['pay_amount'] - $order_info['refund_amount'];

        $order_info['coupon_info'] = $coupon_info;
        $locations = $this->sys_model_orders->getOrderLine(array('order_id' => $order_id), 'lng, lat');
        return array(
            'order_info' => $order_info,
            'locations' => $locations
        );
    }

    /**
     * 关闭蓝牙锁订单,直接通过order_id
     * @param $data
     * @return mixed
     */
    public function closeBLTOrder($data)
    {
        return $this->closeOrder($data);
    }

    /**
     * 关闭机械锁订单
     * @param $data
     * @return mixed
     */
    public function closeMCHOrder($data)
    {
        return $this->closeOrder($data, true);
    }

    /**
     * @param array $data order_id(必须有), lat, lng, finish_time
     * @param bool $request_image
     * @return array
     */
    public function closeOrder($data, $request_image = false)
    {
        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $data['order_id']));
        //更新订单的状态
        $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('order_state' => 2));

        $arr = array(
            'end_time' => isset($data['finish_time']) ? $data['finish_time'] : time(),
            'order_state' => 2,
            'settlement_time' => time(),
        );

        try {
            if ($order_info['start_time'] == 0) {
                $order_info['start_time'] = $order_info['add_time'];
            }
            $this->sys_model_orders->begin();
            $start_time = $order_info['start_time'];
            $end_time = $arr['end_time'];
            $riding_time = $end_time - $start_time;

            if ($riding_time < 0) $riding_time = 0;
            $region_info = $this->sys_model_region->getRegionInfo(array('region_id' => $order_info['region_id']));
            $time_recharge_unit = isset($region_info['region_charge_time']) && $region_info['region_charge_time'] > 0 ? $region_info['region_charge_time'] * 60 : TIME_CHARGE_UNIT;
            $price_unit = isset($region_info['region_charge_fee']) ? $region_info['region_charge_fee'] : PRICE_UNIT;

            //如果骑行时间大于1个计费单元则减去300秒
            if ($riding_time > 3600) {
                $riding_time = $riding_time - 300;
            }

            //骑行产生的金额
            $amount = ceil($riding_time / $time_recharge_unit) * $price_unit;

            //--------------月卡---------------
            if (!$order_info['is_limit_free'] && $order_info['is_month_card'] && !$order_info['is_scenic']) {
                $free_time = 7200;
                if ($riding_time > $free_time) {
                    $riding = $riding_time - $free_time;
                    $amount = ceil($riding / $time_recharge_unit) * $price_unit;

                    $overtime_arr = array(
                        'user_id' => $order_info['user_id'],
                        'user_name' => $order_info['user_name'],
                        'order_id' => $order_info['order_id'],
                        'riding_time' => $riding,
                        'is_crontab' => isset($data['is_cron_close']) ? 1 : 0,
                        'close_time' => time(),
                        'amount' => $amount,
                        'is_normal_close' => isset($data['is_cron_close']) ? 0 : ($order_info['order_state'] == 3 ? 0 : 1)
                    );
                    $this->registry->get('db')->table('month_card_overtime')->insert($overtime_arr);
                    //实际不扣钱？
                    $amount = 0;
                } else {
                    $amount = 0;
                }
            }

            //--------------免费车------------
            if ($order_info['is_limit_free'] && !$order_info['is_scenic']) {
                $free_time = 10800;
                if ($riding_time > $free_time) {
                    $riding = $riding_time - $free_time;
                    $amount = ceil($riding / $time_recharge_unit) * $price_unit;
                } else {
                    $amount = 0;
                }
            }
            
            //-----------优惠活动-----------
            $is_activity = false;
            if (!empty($region_info) && isset($region_info['region_id'])) {
                $activity_info = $this->sys_model_region->getRegionActivityInfo(array('region_id' => $region_info['region_id'], array('start_time' => array('elt', $end_time)), array('end_time' => array('egt', $end_time))));
                if (!empty($activity_info)) {
                    //活动的价格
                    //$amount = ceil($riding_time / $time_recharge_unit) * $activity_info['price'];
                    $amount = 0;
                    $is_activity = true;
                }
            }

            //两分钟之内免费
            if ($riding_time <= 120) {
                $amount = 0;
                $this->cancelOrderRecharge($order_info);
            }

            if ($order_info['order_state'] == 2) {
                //禁止多次提交
                throw new \Exception('订单已结束，无需多次提交');
            }
            if ($order_info['order_state'] == 0) {
                throw new \Exception('预约订单无需结束');
            }

            if ($amount > 0) {
                if (isset($data['is_cron_close'])) {
                    //$amount = $price_unit;
                    //有月卡不扣钱
                    if ($order_info['is_month_card']) {
                        $amount = 0;
                    }
                }
            }

            $arr_data = array(
                'user_id' => $order_info['user_id'],
                'user_name' => $order_info['user_name'],
                'amount' => $amount,
                'order_sn' => $order_info['order_sn'],
            );

            if (isset($data['lat']) && $data['lat'] > 0 && isset($data['lng']) && $data['lng'] > 0) {
                $arr_data['end_lat'] = $data['lat'];
                $arr_data['end_lng'] = $data['lng'];
                $line_data = array(
                    'user_id' => $order_info['user_id'],
                    'order_id' => $order_info['order_id'],
                    'lng' => $data['lng'],
                    'lat' => $data['lat'],
                    'add_time' => time(),
                );
                if (!isset($data['is_cron_close'])) {
                    $this->sys_model_orders->addOrderLine($line_data);
                }
            }

            $arr['order_amount'] = $amount;
            $arr['pay_amount'] = $amount;

            $sys_model_coupon = new \Sys_Model\Coupon($this->registry);
            $sys_model_user = new \Sys_Model\User($this->registry);
            $sys_model_deposit = new \Sys_Model\Deposit($this->registry);

            $user_info = $sys_model_user->getUserInfo(array('user_id' => $order_info['user_id']));
            if (empty($user_info)) {
                throw new \Exception('error_user_info');
            }
            //扣费金额大于骑行的费用
            if ($user_info['available_deposit'] < $amount) {
                $change_type = 'order_freeze';
                $arr_data['left_amount'] = $user_info['available_deposit'];
                $arr_data['present_amount'] = $user_info['present_amount'];
                $arr_data['amount'] = $amount;

                //赠送金额
                if ($user_info['present_amount'] > 0) {
                    if ($user_info['available_deposit'] > 0) {
                        if ($arr_data['amount'] > $user_info['available_deposit'] + $user_info['present_amount']) {
                            $arr['present_amount'] = $user_info['present_amount'];
                        } else {
                            $arr['present_amount'] = $arr_data['amount'] - $user_info['available_deposit'];
                        }
                    } else {
                        if ($user_info['present_amount'] > $arr_data['amount']) {
                            $arr['present_amount'] = $arr_data['amount'];
                        } else {
                            $arr['present_amount'] = $user_info['present_amount'];
                        }
                    }
                }
            } else {
                $change_type = 'order_pay';
            }

            //可否使用优惠券，默认可以使用
            $coupon_usable = isset($region_info['coupon_usable']) ? $region_info['coupon_usable'] : 1;

            if ($coupon_usable && !$order_info['is_limit_free'] && !$is_activity && !$order_info['is_month_card'] && !$order_info['is_scenic']) {
                if ($amount > 0) {
                    $coupon_info = $sys_model_coupon->getRightCoupon(array('user_id' => $order_info['user_id']));
                    if (!empty($coupon_info)) {
                        $need_pay = false;
                        //
                        if ($coupon_info['coupon_type'] == 1) {
                            if (isset($data['is_cron_close'])) $riding_time = 60 * 60;
                            if ($coupon_info['number'] * 60 > $riding_time) {
                                $arr['pay_amount'] = $arr_data['amount'] = 0;
                            } else {
                                $overtime = $riding_time - $coupon_info['number'] * 60;
                                //超时产生的金额
                                $arr['pay_amount'] = $arr_data['amount'] = ceil($overtime / $time_recharge_unit) * $price_unit;
                                $need_pay = true;
                            }
                        } //单次体验券
                        elseif ($coupon_info['coupon_type'] == 2) {
                            $arr['pay_amount'] = $arr_data['amount'] = 0;
                        } //金额体验券，超出不扣，无超则扣整
                        elseif ($coupon_info['coupon_type'] == 3) {
                            $arr['pay_amount'] = $arr_data['amount'] = 0;
                            if ($amount > $coupon_info['number']) {
                                $arr['pay_amount'] = $arr_data['amount'] = $amount - $coupon_info['number'];
                                $need_pay = true;
                            }
                        } elseif ($coupon_info['coupon_type'] == 4) {
                            $arr['pay_amount'] = $arr_data['amount'] = $amount * ($coupon_info['number'] / 10);
                            if ($amount > 0) {
                                $need_pay = true;
                            }
                        }

                        if ($amount != 0) {
                            $coupon_info['order_id'] = $order_info['order_id'];
                            //更新优惠券的信息
                            $update = $sys_model_coupon->dealCoupon($coupon_info);
                            if ($update) {
                                $arr['coupon_id'] = $coupon_info['coupon_id'];
                            }
                        }

                        //重新判断类型
                        if ($user_info['available_deposit'] < $arr_data['amount']) {
                            $change_type = 'order_freeze';
                            $arr_data['present_amount'] = $user_info['present_amount'];
                            $arr_data['left_amount'] = $user_info['available_deposit'];

                            //赠送金额
                            if ($user_info['present_amount'] > 0) {
                                if ($user_info['available_deposit'] > 0) {
                                    if ($arr_data['amount'] > $user_info['available_deposit'] + $user_info['present_amount']) {
                                        $arr['present_amount'] = $user_info['present_amount'];
                                    } else {
                                        $arr['present_amount'] = $arr_data['amount'] - $user_info['available_deposit'];
                                    }
                                } else {
                                    if ($user_info['present_amount'] > $arr_data['amount']) {
                                        $arr['present_amount'] = $arr_data['amount'];
                                    } else {
                                        $arr['present_amount'] = $user_info['present_amount'];
                                    }
                                }
                            }
                            //$arr_data['amount'] = $amount;
                        } else {
                            $change_type = 'order_pay';
                        }

                        if ($need_pay) {
                            $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                            if (!$insert_id) {
                                throw new \Exception('error_insert_order_amount');
                            }
                        }
                    } else {
                        $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                        if (!$insert_id) {
                            throw new \Exception('error_insert_order_amount');
                        }
                    }
                }
            } else {
                if ($amount > 0) {
                    $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                    if (!$insert_id) {
                        throw new \Exception('error_insert_order_amount');
                    }
                }
            }

            $order_lines = $this->sys_model_orders->getOrderLine(array('order_id' => $order_info['order_id']));
            $tool_distance = new \Tool\Distance();
            $distance = $tool_distance->sumDistance($order_lines);
            $distance = round($distance * 1000, -1);

            $arr['distance'] = $distance;
            $arr['end_lat'] = isset($data['lat']) ? $data['lat'] : '';
            $arr['end_lng'] = isset($data['lng']) ? $data['lng'] : '';
            if ($request_image) {
//                $arr['has_upload_image'] = 1;
//                $arr['image_url'] = $data['image_url'];
            }

            //更新订单状态
            $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), $arr);
            if (!$update) {
                throw new \Exception('error_update_order_state_failure');
            }

            $this->sys_model_orders->commit();
            $data = array(
                'cmd' => 'close',
                'order_sn' => $order_info['order_sn'],
                'user_id' => $order_info['user_id'],
                'device_id' => $order_info['lock_sn'],
            );
        } catch (\Exception $e) {
            $this->sys_model_orders->rollback();
            return callback(false, $e->getMessage());
        }
        //增加信用分
        $this->registry->get('load')->library('logic/credit', true);
        $this->registry->get('logic_credit')->addCreditPointOnFinishCycling($order_info['user_id']);

        //增加使用次数
        $this->updateUsageCount($data['device_id']);

        return callback(true, '', $data);
    }

    /**
     * 处理以前的交易记录
     * @param $data
     * @return array
     */
	public function dealPreFinish($data) {
        $finish_time = $data['finish_time'];
        $order_id = $data['order_id'];
        $lat = $data['lat'];
        $lng = $data['lng'];

        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $order_id, 'order_state' => 3));
        if (empty($order_info)) {
            return callback(false, '此订单已结束');
        }
        $start_time = $order_info['start_time'] ? $order_info['start_time'] : $order_info['add_time'];
        $pre_finish_time = $order_info['end_time'] ? $order_info['end_time'] : $finish_time;
        if ($finish_time > $pre_finish_time) {
            $riding_time = $finish_time - $start_time;
            $region_info = $this->sys_model_region->getRegionInfo(array('region_id' => $order_info['region_id']));
            $time_recharge_unit = isset($region_info['region_charge_time']) && $region_info['region_charge_time'] > 0 ? $region_info['region_charge_time'] * 60 : $this->registry->get('config')->get('config_time_charge_unit');
            $price_unit = isset($region_info['region_charge_fee']) ? $region_info['region_charge_fee'] : $this->registry->get('config')->get('config_price_unit');
            //重算的金额
            $amount = ceil($riding_time / $time_recharge_unit) * $price_unit;
            if ($order_info['order_amount'] == 0) //可以认定那时是免费的，对于免费的只改变订单的状态
            {
                //加多一个时间限制
                $this->sys_model_orders->updateOrders(array('order_id' => $order_id), array('order_state' => 2));
            }
            elseif ($order_info['order_amount'] >= $amount) //实际订单的金额大于或等于的，只改变订单的状态
            {
                $this->sys_model_orders->updateOrders(array('order_id' => $order_id), array('order_state' => 2));
            }
            else {
                if ($order_info['coupon_id']) //判断是否使用了优惠券
                {
                }
                $user_info = $this->sys_model_user->getUserInfo(array('user_id' => $order_info['user_id']));
                $extra_amount = $amount - $order_info['order_amount'];

                try {
                    $data = array(
                        'amount' => $extra_amount,
                        'freeze_amount' => ($user_info['available_deposit'] - $extra_amount < 0) ? $extra_amount - $user_info['available_deposit'] : 0,
                        'order_sn' => $order_info['order_sn'],
                        'user_id' => $user_info['user_id'],
                        'user_name' => $user_info['mobile'],
                    );
                    $this->registry->get('db')->begin();
                    $result_id = $this->sys_model_deposit->changeDeposit('pay_pre_finish', $data);

                    $order_data = array(
                        'order_state' => 2,
                        'order_amount' => array('exp', 'order_amount+' . $extra_amount),
                        'pay_amount' => array('exp', 'pay_amount+' . $extra_amount),
                        'end_time' => $finish_time,
                        'end_lat' => $lat,
                        'end_lng' => $lng
                    );
                    $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_id), $order_data);
                    if (!$update) {
                        throw new \Exception('订单更新失败');
                    }
                    $this->registry->get('db')->commit();
                }
                catch (\Exception $e) {
                    $this->registry->get('db')->rollback();
                    return callback(false, $e->getMessage());
                }
            }
        }
        //锁的时间比订单结束的时间更晚
        elseif($pre_finish_time > $data['finish_time']) {
            $this->sys_model_orders->updateOrders(array('order_id' => $order_id), array('order_state' => 2));
        } else {
            $this->sys_model_orders->updateOrders(array('order_id' => $order_id), array('order_state' => 2));
        }
        return callback(true, '订单结束成功');
    }

    private function cancelOrderRecharge($order_info) {
        if (isset($order_info['recharge_sn']) && $order_info['recharge_sn']) {
            $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('recharge_sn' => 0));
            $this->registry->get('db')->table('temp_recharge')->where(array('recharge_sn' => $order_info['recharge_sn']))->update(array('used' => 0));
        }
    }
	
    public function cancelOrders($data)
    {
        $lock_sn = $data['device_id'];
        $add_time = $data['serialnum'];
        $update = $this->sys_model_orders->updateOrders(array('lock_sn' => $lock_sn, 'add_time' => $add_time), array('order_state' => '-1'));
        return $update;
    }

    public function getLastSql()
    {
        return $this->sys_model_orders->getLastSql();
    }

    //锁使用次数添加
    private function updateUsageCount($lock_sn)
    {
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('lock_sn' => $lock_sn));

        if ($this->sys_model_bicycle_usage->getUsageCountInfo(array('bicycle_sn' => $bicycle_info['bicycle_sn'], 'date' => date('Y-m-d', time())))) {//有没有添加今天的使用次数记录的锁
            $this->sys_model_bicycle_usage->updateUsageCount(array('bicycle_sn' => $bicycle_info['bicycle_sn'], 'date' => date('Y-m-d', time())), array('count' => array('exp', 'count+1')));
        } else {
            $data = array(
                'date' => date('Y-m-d', time()),
                'bicycle_sn' => $bicycle_info['bicycle_sn'],
                'count' => 1,
            );
            $this->sys_model_bicycle_usage->addUsageCount($data);
        }
    }
}
