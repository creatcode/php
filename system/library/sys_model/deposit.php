<?php

/**
 * Created by PhpStorm.
 * User: wen
 * Date: 2016/12/7
 * Time: 15:24
 */
namespace Sys_Model;
use payment\alipay\AlipayTradeRefundRequest;
use payment\alipay\AopClient;

class Deposit {
    private $registry;
    public $db;
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->db = $registry->get('db');
    }

    public function makeSn($user_id) {
        return mt_rand(10, 99)
        . sprintf('%010d', time() - 946656000)
        . sprintf('%03d', (float) microtime() * 1000)
        . sprintf('%03d', (int) $user_id % 1000);
    }

    public function getRechargeList($condition = array(), $field = '*', $order = '', $limit = '') {
        $on = 'dr.pdr_user_id=u.user_id,c.city_id=u.city_id';
        return $this->db->table('deposit_recharge as dr,user as u,city as c')->where($condition)->field($field)->order($order)->limit($limit)->join('left')->on($on)->select();
    }

    /**
     * [getRechargeList2 description]
     * @param    array                    $condition [description]
     * @param    string                   $field     [description]
     * @param    string                   $order     [description]
     * @param    string                   $limit     [description]
     * @return   [type]                              [description]
     * @Author   vincent
     * @DateTime 2017-08-22T17:39:26+0800
     */
    public function getRechargeList2($condition = array(),  $order = '', $limit = '',$field = '*',$join = array()) {
        $table = 'deposit_recharge as deposit_recharge';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);
            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->field($field)->join($joinType)->where($condition)->order($order)->limit($limit)->select();
    }

    public function addRecharge($data) {
        return $this->db->table('deposit_recharge')->insert($data);
    }

    public function updateRecharge($where, $data) {
        return $this->db->table('deposit_recharge')->where($where)->update($data);
    }

    public function getRechargeInfo($where, $fields = '*',$join='') {
        $table = 'deposit_recharge as deposit_recharge';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
         return $this->db->table($table)->field($fields)->where($where)->find();
    }

    public function getOneRecharge($where, $field = '*', $order = '',$join='') {
        $table = 'deposit_recharge as deposit_recharge';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->field($field)->where($where)->order($order)->find();
    }
    
    /**
     * [getRechargeList2 description]
     * @param    array                    $condition [description]
     * @param    string                   $field     [description]
     * @param    string                   $order     [description]
     * @param    string                   $limit     [description]
     * @param    array                    $join      [description]
     * @return   [type]                              [description]
     * @Author   vincent
     * @DateTime 2017-08-22T18:23:15+0800
     */
    public function getRechargeCount2($condition = array(), $join = array()) {
        $table = 'deposit_recharge as deposit_recharge';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);
            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->where($condition)->count();
    }

    public function getRechargeCount($where) {
        $on = 'dr.pdr_user_id=u.user_id';
        return $this->db->table('deposit_recharge as dr,user as u')->where($where)->join('left')->on($on)->count();
    }

    public function getDepositCashCount($where) {
        return $this->db->table('deposit_cash')->where($where)->count();
    }

    public function getDepositLogCount($where) {
        return $this->db->table('deposit_log')->where($where)->count();
    }

    public function getDepositLogList($where = array(), $field = '*', $order = '', $limit = '') {
        return $this->db->table('deposit_log')->where($where)->field($field)->order($order)->limit($limit)->select();
    }

    /**
     * @param $change_type
     * @param array $data
     * @return mixed
     */
    public function changeDeposit($change_type, $data = array()) {
        $data_log = array();
        $data_pd = array();
        $data_msg = array();

        $data_log['pdl_user_id'] = $data['user_id'];
        $data_log['pdl_user_name'] = $data['user_name'];
        $data_log['pdl_add_time'] = TIMESTAMP;
        $data_log['pdl_type'] = $change_type;

        $data_msg['time'] = date('Y-m-d H:i:s');
        //$data_msg['pd_url'] =

        switch ($change_type) {
            case 'order_pay' :
                $data_log['pdl_available_amount'] = -$data['amount'];
                $data_log['pdl_desc'] = '单车骑行，扣除支付预存款，订单号：' . $data['order_sn'];
                $data_log['pdl_payment_code'] = 'deposit';
                $data_log['pdl_payment_name'] = '余额支付';
                $data_log['pdl_sn'] = $data['order_sn'];
                $data_pd['available_deposit'] = array('exp', 'available_deposit-' . $data['amount']);
                //消息推送
                break;
            case 'pay_pre_finish' :
                $data_log['pdl_available_amount'] = -$data['amount'];
                $data_log['pdl_desc'] = '预结束订单增扣金额，订单号：' . $data['order_sn'];
                $data_log['pdl_payment_code'] = 'deposit';
                $data_log['pdl_payment_name'] = '余额支付';
                $data_log['pdl_sn'] = $data['order_sn'];
                $data_log['pdl_admin_id'] = 0;
                $data_log['pdl_admin_name'] = 'system';

                $data_pd['available_deposit'] = array('exp', 'available_deposit-' . $data['amount']);
                if ($data['freeze_amount'] > 0) {
                    $data_pd['available_state'] = 0;//不可以
                    $data_pd['freeze_recharge'] = array('exp', 'freeze_recharge+' . $data['freeze_amount']);
                }
                break;
            case 'order_freeze':
                $present_amount = isset($data['present_amount']) ? $data['present_amount'] : 0;
                $data_log['pdl_available_amount'] = -$data['amount'];
                $data_log['pdl_freeze_amount'] = ($data['amount'] > $data['left_amount'] + $present_amount) ? $data['amount'] - $data['left_amount'] + $present_amount : 0;
                $data_log['pdl_desc'] = '单车骑行，费用不足，冻结预存款，订单号：' . $data['order_sn'];

                $data_log['pdl_payment_code'] = 'deposit';
                $data_log['pdl_payment_name'] = '余额支付';
                $data_log['pdl_sn'] = $data['order_sn'];
                $data_log['pdl_admin_id'] = 0;
                $data_log['pdl_admin_name'] = 'system';

                if ($data['left_amount'] > 0 && $present_amount > 0) {
                    //消费大于
                    if ($data['amount'] > $data['left_amount'] + $present_amount) {
                        $data_pd['freeze_recharge'] = $data['amount'] - ($data['left_amount'] + $present_amount);
                        $data_pd['available_deposit'] = array('exp', 'available_deposit-' . ($data['amount'] - $present_amount));
                        if ($present_amount > 0) $data_pd['present_amount'] = 0;
                        $data_pd['available_state'] = '0';
                    } else {
                        $data_log['pdl_type'] = 'recharge_offer_pay';
                        $data_pd['available_deposit'] = 0;
                        $data_pd['present_amount'] = array('exp', 'present_amount-' . ($present_amount + $data['left_amount'] - $data['amount']));
                    }
                } elseif ($data['left_amount'] == 0 && $present_amount > 0) {
                    if ($data['amount'] > $present_amount) {
                        $data_pd['available_deposit'] = array('exp', 'available_deposit-' . ($data['amount'] - $present_amount));
                        $data_pd['freeze_recharge'] = array('exp', 'freeze_recharge+' . ($data['amount'] - $present_amount));
                        $data_pd['present_amount'] = 0;
                    } elseif ($present_amount >= $data['amount']) {
                        $data_pd['present_amount'] = array('exp', 'present_amount-' . $data['amount']);
                        $data_log['pdl_type'] = 'recharge_offer_pay';
                        $data_log['pdl_desc'] = '行程消费赠送金额，订单号：' . $data['order_sn'];
                    }
                } elseif ($data['left_amount'] < 0 && $present_amount > 0) {
                    if ($present_amount + $data['left_amount'] > $data['amount']) {
                        $data_pd['available_deposit'] = 0;
                        $data_pd['present_amount'] = $present_amount + $data['left_amount'] - $data['amount'];
                        $data_log['pdl_desc'] = '行程消费赠送金额，订单号：' . $data['order_sn'];
                    } else {
                        $data_pd['present_amount'] = 0;
                        $data_pd['available_deposit'] = array('exp', 'available_deposit-' . ($present_amount + $data['left_amount'] - $data['amount']));
                        $data_pd['freeze_recharge'] = array('exp', 'available_deposit+' . ($present_amount + $data['left_amount'] - $data['amount']));
                    }
                } else {
                    $data_pd['freeze_recharge'] = ($data['amount'] - $data['left_amount']);
                    $data_pd['available_deposit'] = array('exp', 'available_deposit-' . $data['amount']);
                    $data_pd['available_state'] = '0';
                }
                break;
            case 'recharge' :
                $data_log['pdl_payment_code'] = isset($data['payment_code']) ? $data['payment_code'] : '';
                $data_log['pdl_payment_name'] = isset($data['payment_name']) ? $data['payment_name'] : '';
                $data_log['pdl_available_amount'] = $data['amount'];
                $data_log['pdl_desc'] = '充值，充值单号：' . $data['pdr_sn'];
                $data_log['pdl_sn'] = $data['pdr_sn'];
                $data_log['pdl_admin_id'] = 0;
                $data_log['pdl_admin_name'] = 'system';

                $user_info = $this->db->table('user')->field('user_id,available_deposit,freeze_recharge,available_state')->where(array('user_id' => $data['user_id']))->find();

                $present_amount = isset($data['present_amount']) ? $data['present_amount'] : 0;

                //处理赠送金额
                $is_overdue = 0;
				if (!empty($user_info) && $user_info['freeze_recharge'] > 0) {
					if ($user_info['freeze_recharge'] > $data['amount']) {
                        //所欠的车费大于充值金额和赠送金额的总额
                        if ($user_info['freeze_recharge'] >= ($data['amount'] + $present_amount)) {
                            $is_overdue = 1;
                            $data_pd['freeze_recharge'] = array('exp', 'freeze_recharge-' . ($data['amount'] + $present_amount));
                        } else {
                            $is_overdue = 2;
                            $left_amount = $data['amount'] + $present_amount - $user_info['freeze_recharge'];
                            $data_pd['freeze_recharge'] = 0;
                            $data_pd['present_amount'] = array('exp', 'present_amount+' . $left_amount);
                        }
                    } else {
                        $data_pd['freeze_recharge'] = 0;
                        if ($present_amount > 0) {
                            $data_pd['present_amount'] = array('exp', 'present_amount+' . $present_amount);
                        }
                    }
                }

                if ($is_overdue) {
                    if ($is_overdue == 1) {
                        $data_pd['available_deposit'] = array('exp', 'available_deposit+' . ($data['amount'] + $present_amount));
                    } elseif ($is_overdue == 2) {
                        $data_pd['available_deposit'] = 0;
                    }
                } else {
                    $data_pd['available_deposit'] = array('exp', 'available_deposit+' . $data['amount']);
                    if ($present_amount > 0) {
                        $data_pd['present_amount'] = array('exp', 'present_amount+' . $present_amount);
                    }
                }

                if (($data['amount'] + $present_amount - $user_info['freeze_recharge']) > 0) {
                    if ($user_info['available_state'] == 0) {
                        $data_pd['available_state'] = '1';
                    }
                } else {
                    if (intval($user_info['available_state']) == 1) {
                        $data_pd['available_state'] = '0';
                    }
                }

                break;
            //余额不足扣时，扣赠送也扣
            case 'order_recharge_offer' :
                $data_log['pdl_payment_code'] = 'recharge_offer';
                $data_log['pdl_payment_name'] = '充值赠送金额';
                $data_log['pdl_available_amount'] = $data['amount'];
                $data_log['pdl_desc'] = '充值优惠，赠送金额' . $data['amount'];
                $data_log['pdl_sn'] = $data['pdr_sn'];

                $data_pd['present_amount'] = array('exp', 'present_amount+' . $data['amount']);
                break;
            //赠送金额消费
            case 'recharge_offer_pay' :
                $data_log['pdl_payment_code'] = 'recharge_offer_pay';
                $data_log['pdl_payment_name'] = '行程消费赠送金额';
                $data_log['pdl_available_amount'] = $data['amount'];
                $data_log['pdl_sn'] = $data['order_sn'];
                //扣取金额，需重新
                $data_pd['present_amount'] = array('exp', 'present_amount-' . $data['amount']);
                break;
            case 'deposit' :
                $data_log['pdl_payment_code'] = isset($data['payment_code']) ? $data['payment_code'] : '';
                $data_log['pdl_payment_name'] = isset($data['payment_name']) ? $data['payment_name'] : '';
                $data_log['pdl_available_amount'] = $data['amount'];
                $data_log['pdl_desc'] = '充值押金，充值单号：' . $data['pdr_sn'];
                $data_log['pdl_admin_id'] = 0;
                $data_log['pdl_admin_name'] = 'system';
                $data_log['pdl_sn'] = $data['pdr_sn'];

                $data_pd['deposit'] = array('exp', 'deposit+' . $data['amount']);
                $data_pd['deposit_state'] = '1';
                break;
            // 押金申请提现
            case 'deposit_cash_apply' :
                $data_log['pdl_available_amount'] = -$data['amount'];
                $data_log['pdl_freeze_amount'] = $data['amount'];
                $data_log['pdl_desc'] = '申请押金提现，冻结预存款，提现单号：' . $data['pdc_sn'];

                $data_log['pdl_admin_id'] = $data['admin_id'];
                $data_log['pdl_admin_name'] = $data['admin_name'];
                $data_log['pdl_sn'] = $data['pdc_sn'];
                $data_log['pdl_payment_code'] = 'deposit';
                $data_log['pdl_payment_name'] = '余额支付';
                $data_pd['deposit'] = array('exp', 'deposit-' . $data['amount']);
                $data_pd['freeze_deposit'] = array('exp', 'freeze_deposit+' . $data['amount']);
                $data_pd['deposit_state'] = '0';
                $data_pd['available_state'] = '0';
                break;
            // 余额申请提现
            case 'balance_cash_apply' :
                $data_log['pdl_available_amount'] = -$data['amount'];
                $data_log['pdl_freeze_amount'] = $data['amount'];
                $data_log['pdl_desc'] = '申请余额提现，冻结预存款，提现单号：' . $data['pdc_sn'];

                $data_log['pdl_admin_id'] = $data['admin_id'];
                $data_log['pdl_admin_name'] = $data['admin_name'];
                $data_log['pdl_sn'] = $data['pdc_sn'];
                $data_log['pdl_payment_code'] = 'deposit';
                $data_log['pdl_payment_name'] = '余额支付';
                $data_pd['available_deposit'] = array('exp', 'available_deposit-' . $data['amount']);
                $data_pd['freeze_deposit'] = array('exp', 'freeze_deposit+' . $data['amount']);
                break;

            case 'cash_pay':
                $data_log['pdl_freeze_amount'] = -$data['amount'];
                $data_log['pdl_desc'] = '提现成功，提现单号：' . $data['pdr_sn'];
                $data_log['pdl_admin_id'] = $data['admin_id'];
                $data_log['pdl_admin_name'] = $data['admin_name'];

                $data_log['pdl_payment_code'] = 'deposit';
                $data_log['pdl_payment_name'] = '余额支付';

                $data_pd['freeze_deposit'] = array('exp', array('freeze_deposit-' . $data['amount']));
                break;
            //取消押金提现申请
            case 'deposit_cash_cancel':
                $data_log['pdl_available_amount'] = $data['amount'];
                $data_log['pdl_freeze_amount'] = -$data['amount'];
                $data_log['pdl_desc'] = '取消押金提现申请，解冻预存款，提现单号：' . $data['amount'];
                $data_log['pdl_admin_id'] = 0;
                $data_log['pdl_admin_name'] = 'system';

                $data_log['pdl_payment_code'] = 'deposit';
                $data_log['pdl_payment_name'] = '余额支付';

                $data_pd['freeze_deposit'] = array('exp', 'freeze_deposit-' . $data['amount']);
                $data_pd['deposit'] = array('exp', 'deposit+' . $data['amount']);
                $data_pd['deposit_state'] = '1';
                break;
            //取消余额提现申请
            case 'balance_cash_cancel':
                $data_log['pdl_available_amount'] = $data['amount'];
                $data_log['pdl_freeze_amount'] = -$data['amount'];
                $data_log['pdl_desc'] = '取消余额提现申请，解冻预存款，提现单号：' . $data['amount'];
                $data_log['pdl_admin_name'] = $data['admin_name'];

                $data_log['pdl_payment_code'] = 'deposit';
                $data_log['pdl_payment_name'] = '余额支付';

                $data_pd['freeze_deposit'] = array('exp', 'freeze_deposit-' . $data['amount']);
                $data_pd['deposit'] = array('exp', 'available_deposit+' . $data['amount']);
                break;
            //购买月卡
            case 'charge_month_card' :
                $data_log['pdl_payment_code'] = isset($data['payment_code']) ? $data['payment_code'] : '';
                $data_log['pdl_payment_name'] = isset($data['payment_name']) ? $data['payment_name'] : '';
                $data_log['pdl_available_amount'] = $data['amount'];
                $data_log['pdl_desc'] = '充值卡，充值单号：' . $data['pdr_sn'];
                $data_log['pdl_sn'] = $data['pdr_sn'];
                $data_log['pdl_admin_id'] = 0;
                $data_log['pdl_admin_name'] = 'system';


                $month_card_info = $this->db->table('month_card')->where(array('recharge_sn' => $data['pdr_sn']))->find();
                $data['number'] = $month_card_info['time_length'] * 24 * 3600;

                $insert_data = array(
                    'start_time' => time(),
                    'end_time' => time() + $data['number'],
                    'payment_state' => 1
                );
                $user_info = $this->db->table('user')->field('card_expired_time')->where(array('user_id' => $data['user_id']))->find();

                $this->db->table('month_card')->where(array('recharge_sn' => $data['pdr_sn']))->update($insert_data);
                if ($user_info['card_expired_time'] < time()) {
                    $data_pd['card_expired_time'] = bcadd(86399, strtotime(date('Y-m-d'))) + $data['number'];
                } else {
                    $data_pd['card_expired_time'] = array('exp', 'card_expired_time+' . $data['number']);
                }

                $data_pd['available_state'] = 1;

                break;

            default:
                throw new \Exception('参数错误');
        }
        //更新金额
        if ($change_type == 'deposit') {
            //充值押金时，检测是否再次充值押金，如果是已实名并且有余额则更新状态
            $user_info = $this->db->table('user')->where(array('user_id' => $data['user_id']))->find();
            if ($user_info['verify_state'] == 1 && $user_info['available_deposit'] > 0) {
                $data_pd['available_state'] = 1;
            }
        }

        $is_repeat = false;
        if (in_array($change_type, array('order_pay', 'order_freeze', 'recharge_offer_pay'))) {
            $deposit_log = $this->db->table('deposit_log')->where(array('pdl_sn' => $data['order_sn']))->find();
            //如果已经存在此order_sn的订单
            if ($deposit_log) {
                $is_repeat = true;
            }
        }
        //如果不是重复提交
        if (!$is_repeat) {
            $update = $this->db->table('user')->where(array('user_id' => $data['user_id']))->update($data_pd);
            if (!$update) {
//            throw new \Exception('更新用户金额失败');
            }
            //写入记录
            $insert = $this->db->table('deposit_log')->insert($data_log);
            if ($change_type == 'recharge' && isset($present_amount) && $present_amount > 0) {
                $data_log['pdl_type'] = 'recharge_offer';
                $data_log['pdl_payment_code'] = 'recharge_offer';
                $data_log['pdl_payment_name'] = '充值赠送金额';
                $data_log['pdl_available_amount'] = $present_amount;
                $data_log['pdl_desc'] = '充值优惠，赠送金额' . $data['amount'];
                $this->db->table('deposit_log')->insert($data_log);
            }
            if (!$insert) {
                throw new \Exception('操作失败');
            }
        } else {
            $insert = 0;
        }

        return $insert;
    }

    public function updateMonthCard($setting_id, $user) {

    }

    public function deleteRecharge($condition) {
        return $this->db->table('deposit_recharge')->where($condition)->delete();
    }

    public function addDepositCash($data) {
        return $this->db->table('deposit_cash')->insert($data);
    }

    public function getDepositCashInfo($where, $fields = '*',$join='') {
        $table = 'deposit_cash as deposit_cash';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->field($fields)->where($where)->find();
    }

    

    public function getDepositCashList($where = array(), $limit = '', $order = '', $field = '*',$join=array()) {
        $table = 'deposit_cash as deposit_cash';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
     
        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
        
    }

    public function getDepositCashTotal($where = array(),$join) {
        $table = 'deposit_cash as deposit_cash';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->where($where)->count();
    }

    public function deleteDepositCash($where) {
        return $this->db->table('deposit_cash')->where($where)->delete();
    }

    public function updateDepositCash($where, $data) {
        return $this->db->table('deposit_cash')->where($where)->update($data);
    }
    

    /**
     * 验证充值金额
     * @param $amount
     * @return array
     */
    public function validateRecharge($amount) {
        $amount = floatval($amount);
        $min = floatval(MIN_RECHARGE);
        $max = floatval(MAX_RECHARGE);
        if ($amount < $min) {
            return callback(false, '充值金额不可小于' . $min);
        } elseif ($amount > $max) {
            return callback(false, '充值金额不可大于' . $max);
        }
        return callback(true);
    }

    public function updateDepositChargeOrder($out_trade_no, $trade_no, $payment_info, $recharge_info) {
        $condition = array();
        $condition['pdr_sn'] = $recharge_info['pdr_sn'];
        $condition['pdr_payment_state'] = 0;
        $update = array();
        $update['pdr_payment_state'] = 1;
        $update['pdr_payment_time'] = $payment_info['payment_time'];
        $update['pdr_payment_code'] = $payment_info['payment_code'];
        $update['pdr_payment_name'] = $payment_info['payment_name'];
        $update['pdr_payment_type'] = $payment_info['payment_type'];
        $update['pdr_trade_sn'] = $trade_no;
        $update['pdr_admin_name'] = 'system';
        $update['trace_no'] = $out_trade_no;

        try {
            $this->db->begin();
            $info = $this->db->table('deposit_recharge')->field('pdr_sn')->where(array('pdr_sn' => $recharge_info['pdr_sn'], 'pdr_payment_state' => 1))->find();
            if ($info) {
                throw new \Exception('订单已处理');
            }
            $state = $this->db->table('deposit_recharge')->where($condition)->update($update);
            if (!$state) {
                throw new \Exception('更新充值状态失败');
            }
            $data = array();
            $data['user_id'] = $recharge_info['pdr_user_id'];
            $data['user_name'] = $recharge_info['pdr_user_name'];
            $data['amount'] = $recharge_info['pdr_amount'];
            $data['pdr_sn'] = $recharge_info['pdr_sn'];
            $data['admin_name'] = $recharge_info['pdr_admin_name'];
            $data = array_merge($data, $payment_info);
            $type = $recharge_info['pdr_type'] ? 'deposit' : 'recharge';

            $data['present_amount'] = $recharge_info['pdr_present_amount'];

            if ($recharge_info['pdr_type'] == '2') {
                $type = 'charge_month_card';
            }
            if ($recharge_info['pdr_type'] == 3) $type = 'recharge';

            $this->changeDeposit($type, $data);
            $this->db->commit();
            return callback(true);
        } catch (\Exception $e) {
            $this->db->rollback();
            return callback(false, $e->getMessage());
        }
    }

    /**
     * 申请提现
     * @param $pdr_info
     * @return mixed
     */
    public function cashApply($pdr_info) {
        $change_type = $pdr_info['pdr_type'] == 1 ? 'deposit_cash_apply' : 'balance_cash_apply';
        $data['pdc_sn'] = $this->makeSn($pdr_info['pdr_user_id']);
        $data['user_id'] = $pdr_info['pdr_user_id'];
        $data['user_name'] = $pdr_info['pdr_user_name'];
        $data['amount'] = $pdr_info['cash_amount'];
        $data['admin_id'] = isset($pdr_info['admin_id']) ? $pdr_info['admin_id'] : 0;
        $data['admin_name'] = isset($pdr_info['admin_name']) ? $pdr_info['admin_name'] : 'system';

        $insert_arr['pdc_sn'] = $data['pdc_sn'];
        $insert_arr['pdc_user_id'] = $data['user_id'];
        $insert_arr['pdc_user_name'] = $data['user_name'];
        $insert_arr['pdc_amount'] = $data['amount'];
        $insert_arr['pdc_type'] = $pdr_info['pdr_type'];
        $insert_arr['pdc_payment_name'] = $pdr_info['pdr_payment_name'];
        $insert_arr['pdc_payment_code'] = $pdr_info['pdr_payment_code'];
        $insert_arr['pdc_payment_type'] = $pdr_info['pdr_payment_type'];
        $insert_arr['pdc_add_time'] = time();
        $insert_arr['pdc_admin_id'] = $data['admin_id'];
        $insert_arr['pdc_admin_name'] = $data['admin_name'];
        $insert_arr['pdr_sn'] = $pdr_info['pdr_sn'];
        $insert_arr['trace_no'] = $pdr_info['trace_no'];

        try {
            $this->db->begin();
            // 提交提现订单
            $insert = $this->db->table('deposit_cash')->insert($insert_arr);
            if (!$insert) {
                throw new \Exception('写入申请库失败');
            }
            // 录入提现日志，并冻结金额
            $this->changeDeposit($change_type, $data);
            $this->db->commit();
            return callback(true, '', array('pdc_id' => $insert, 'pdc_sn' => $data['pdc_sn']));
        } catch (\Exception $e) {
            $this->db->rollback();
            return callback(false, $e->getMessage());
        }
    }

    /**
     * 取消提现
     * @param $pdc_info
     * @return array
     */
    public function cashCancel($pdc_info) {
        $change_type = $pdc_info['pdc_type'] == 1 ? 'deposit_cash_cancel' : 'balance_cash_cancel';
        $data['user_id'] = $pdc_info['pdc_user_id'];
        $data['user_name'] = $pdc_info['pdc_user_name'];
        $data['amount'] = $pdc_info['cash_amount'];
        $data['admin_name'] = 'system';

        try {
            $this->db->begin();
            $this->changeDeposit($change_type, $data);
            $effect = $this->deleteDepositCash(array('pdc_id' => $pdc_info['pdc_id']));
            if (!$effect) {
                throw new \Exception('删除申请记录失败');
            }
            $this->db->commit();
            return callback(true);
        } catch (\Exception $e) {
            $this->db->rollback();
            return callback(false, $e->getMessage());
        }
    }

    /**
     * 支付宝有密退款
     * @param $cash_info
     * @return string
     */
    /*    public function aliPayRefund($cash_info) {
            $config = $this->registry->get('config');
            $alipay_config = array();
            $alipay_config['key'] = $config->get('config_alipay_key');
            $alipay_config['partner'] = $config->get('config_alipay_partner');
            $alipay_config['seller_id'] = $config->get('config_alipay_seller_id');
            $alipay_config['sign_type'] = strtoupper('md5');
            $alipay_config['input_charset'] = strtolower('utf-8');

            if (!empty($cash_info) && $cash_info['pdc_payment_state'] == 0 && $cash_info['pdc_payment_code'] == 'alipay') {
                $aliPaySubmit = new \payment\alipay\alipaySubmit($alipay_config);
                $parameter = $this->getPara($alipay_config);
                $refund_amount = $cash_info['pdc_amount'];
                $batch_no = $cash_info['pdc_batch_no'];
                if (empty($batch_no)) {
                    $batch_no = date('YmdHis') . $cash_info['pdc_id'];
                    $this->db->table('deposit_cash')->where(array('pdc_id' => $cash_info['pdc_id']))->update(array('pdc_batch_no' => $batch_no));
                } else {
                    $date = substr($batch_no, 0, 8);
                    if ($date != date('Ymd')) {
                        $batch_no = date('Ymd') . substr($batch_no, 8);
                        $this->db->table('deposit_cash')->where(array('pdc_id' => $cash_info['pdc_id']))->update(array('pdc_batch_no' => $batch_no));
                    }
                }
                $parameter['batch_no'] = $batch_no;
                $parameter['detail_data'] = $cash_info['trace_no'] . '^' . $refund_amount . '^协商退款';

                $pay_url = $aliPaySubmit->buildRequestParaToString($parameter);
                $pay_url = $aliPaySubmit->alipay_gateway_new . $pay_url;

    //            return $pay_url;
                @header("Location: " . $pay_url);
                exit;
            }
            return '';
        }*/


    public function getPara($alipay_config) {
        $parameter = array(
            'service' => 'refund_fastpay_by_platform_pwd',
            'partner' => trim($alipay_config['partner']),
            '_input_charset' => strtolower('utf-8'),
            'sign_type' => strtoupper('MD5'),
            'notify_url' => 'http://bike.e-stronger.com/bike/admin/payment/alipay.php',
            'seller_email' => trim($alipay_config['seller_id']),
            'refund_date' => date('Y-m-d H:i:s'),
            'batch_no' => '',
            'batch_num' => '1',
            'detail_data' => '',
        );
        return $parameter;
    }

    /**
     * 支付宝无密退款
     * @param $cash_info
     * @return string
     */
    public function aliPayRefund($cash_info) {
        // 提现订单是否支付
        if (!empty($cash_info) && $cash_info['pdc_payment_state'] == 0 && $cash_info['pdc_payment_code'] == 'alipay') {
            // 退款配置参数
            $config = $this->getAliPayConfig();

            // 支付宝基本配置
            $alipay_config = array();
            $alipay_config['key'] = $config['key'];
            $alipay_config['partner'] = $config['partner'];
            $alipay_config['seller_id'] = $config['seller_id'];
            $alipay_config['sign_type'] = $config['sign_type'];
            $alipay_config['input_charset'] = $config['_input_charset'];

            // 退款数据
            $parameter = array(
                'service' => $config['service'],
                'partner' => $config['partner'],
                '_input_charset' => $config['_input_charset'],
                'sign_type' => $config['sign_type'],
                'notify_url' => 'http://bike.e-stronger.com/bike/admin/payment/alipay_refund.php',
                'batch_no' => '',
                'refund_date' => date('Y-m-d H:i:s'),
                'batch_num' => '1',
                'detail_data' => '',
                'use_freeze_amount' => 'N',
                'return_type' => 'xml',
            );
            $refund_amount = $cash_info['cash_amount'];
			// 重新生成批号
			$batch_no = date('YmdHis') . $cash_info['pdc_id'] . token(3, 'number');
            $this->db->table('deposit_cash')->where(array('pdc_id' => $cash_info['pdc_id']))->update(array('pdc_batch_no' => $batch_no));

            $parameter['batch_no'] = $batch_no;
            $parameter['detail_data'] = $cash_info['trace_no'] . '^' . $refund_amount . '^协商退款';

            $aliPaySubmit = new \payment\alipay\alipaySubmit($alipay_config);
            // 生成请求url
            $pay_url = $aliPaySubmit->buildRequestParaToString($parameter);
            // 拼接API URL
            $pay_url = $aliPaySubmit->alipay_gateway_new . $pay_url;
            // GET 请求并获取响应数据
            $alipayResponse = file_get_contents($pay_url);
            // XML 转 数组
            $data = $this->parseXml($alipayResponse);
            // 申请退款成功
            if (isset($data['is_success']) && $data['is_success'] == 'T') {
				// 更改提现订单状态，状态为 2 退款中
				$this->db->table('deposit_cash')->where(array('pdc_id' => $cash_info['pdc_id']))->update(array('pdc_payment_state' => '2'));
                return callback(true);
            } else {
                // 申请失败
                return callback(false, $data['error']);
            }
        }
    }

    /**
     * 支付宝退款配置
     * @return array
     */
    private function getAliPayConfig() {
        $cfg = $this->registry->get('config');
        $config = array();
        // 接口名称
        $config['service'] = 'refund_fastpay_by_platform_nopwd';
        // 合作者身份id
        $config['partner'] = $cfg->get('config_alipay_partner');
        // 参数编吗字符给事
        $config['_input_charset'] = strtolower('utf-8');
        // 签名方式
        $config['sign_type'] = strtoupper('MD5');
        // 异步通知url
        $config['notify_url'] = $cfg->get('config_alipay_refund_notify_url');
        // 充退异步通知url
        $config['dback_notify_url'] = $cfg->get('config_alipay_refund_notify_url');

        // 商户id
        $config['seller_id'] = $cfg->get('config_alipay_seller_id');
        // 证书类型
        $config['cacert'] = getcwd() . '\\cacert.pem';
        // 私钥文件
        $config['private_key_path'] = 'key/rsa_private_key.pem';
        // 公钥文件
        $config['ali_public_key_path'] = 'key/alipay_public_key.pem';
        // 应用key
        $config['key'] = $cfg->get('config_alipay_key');
        // 传输协议
        $config['transport'] = 'http';

        return $config;
    }

    /**
     * xml 转 数组
     * @param $xml
     * @return mixed
     */
    private function parseXml($xml) {
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    public function wxPayRefund($cash_info) {
        // 检验是否微信支付并且未退款状态
        if (!empty($cash_info) && $cash_info['pdc_payment_state'] == 0 && $cash_info['pdc_payment_code'] == 'wxpay') {
            $config = $this->registry->get('config');
            // 根据支付类型选择不同支付配置参数
            switch ($cash_info['pdc_payment_type']) {
                case 'app':
                    $wx_app_id = $config->get('config_wxpay_appid');
                    $wx_mch_id = $config->get('config_wxpay_mchid');
                    $wx_app_secert_id = $config->get('config_wxpay_appsecert');
                    $wx_key = $config->get('config_wxpay_key');
                    break;
                case 'web':
                    $wx_app_id = $config->get('config_wxpay_mp_app_id');
                    $wx_mch_id = $config->get('config_wxpay_mp_mchid');
                    $wx_app_secert_id = $config->get('config_wxpay_mp_appsecert');
                    $wx_key = $config->get('config_wxpay_mp_key');
                    break;
                case 'mini_app':
                    $wx_app_id = $config->get('config_wxpay_app_app_id');
                    $wx_mch_id = $config->get('config_wxpay_app_mchid');
                    $wx_app_secert_id = $config->get('config_wxpay_app_appsecert');
                    $wx_key = $config->get('config_wxpay_app_key');
                    break;
                default:
                    $wx_app_id = $config->get('config_wxpay_appid');
                    $wx_mch_id = $config->get('config_wxpay_mchid');
                    $wx_app_secert_id = $config->get('config_wxpay_appsecert');
                    $wx_key = $config->get('config_wxpay_key');
                    break;
            }
            // 退款金额
            $refund_amount = $cash_info['cash_amount'];
            if ($refund_amount > 0) {
                /** 化成分做单位 **/
                // 充值订单总金额
                $total_fee = $cash_info['pdr_amount'] * 100;
                // 退款金额
                $refund_fee = $refund_amount * 100;
                // 退款批次号，支付宝要求当天日期
                $batch_no = isset($cash_info['pdc_batch_no']) ? $cash_info['pdc_batch_no'] : '';
                if (empty($batch_no)) {
                    $batch_no = date('YmdHis') . $cash_info['pdc_id'];
                    $this->db->table('deposit_cash')->where(array('pdc_id' => $cash_info['pdc_id']))->update(array('pdc_batch_no' => $batch_no));
                } else {

                    //还需判断流水号是否今天，如非，前面八位要替换成当天的日期
                }

                define('WXPAY_APPID', $wx_app_id);
                define('WXPAY_MCHID', $wx_mch_id);
                define('WXPAY_KEY', $wx_key);
                define('WXPAY_APPSECRET', $wx_app_secert_id); //jdk支付会使用到

                library('payment/wechat/wxpayconfig');
                library('payment/wechat/wxpaydata');

                // 开始提交微信退款订单
                $input = new \Payment\WeChat\WxPayRefund();
                //$input->SetTransaction_id($cash_info['pdr_sn']);
                $input->SetOut_trade_no($cash_info['pdr_sn']);
                $input->SetTotal_fee($total_fee);
                $input->SetRefund_fee($refund_fee);
                $input->SetOut_refund_no($batch_no);
                $input->SetOp_user_id(\Payment\WeChat\WxPayConfig::MCHID);
                // 申请结果数据
                $data = \Payment\WeChat\WxPayApi::refund($input);

                // 未结算资金不足，转试用可用余额
                if (!empty($data) && isset($data['err_code']) && $data['err_code'] == 'NOTENOUGH') {
                    $input->SetRefund_account('REFUND_SOURCE_RECHARGE_FUNDS');
                    $data = \Payment\WeChat\WxPayApi::refund($input);
                }

                //微信同步产生结果
                if (!empty($data) && $data['return_code'] == 'SUCCESS') {
                    if ($data['result_code'] == 'SUCCESS') {
                        $pdc_data = array();
                        $pdc_data['pdc_payment_time'] = time();
                        $pdc_data['pdc_payment_state'] = '1';
                        try {
                            $this->db->begin();
                            // 更新退款订单表
                            $update = $this->updateDepositCash(array('pdc_id' => $cash_info['pdc_id']), $pdc_data);
                            if ($update) {
                                // 退款记录数据
                                $arr['user_id'] = $cash_info['pdc_user_id'];
                                $arr['user_name'] = $cash_info['pdc_user_name'];
                                $arr['amount'] = $cash_info['cash_amount'];
                                $arr['pdr_sn'] = $cash_info['pdc_sn'];
                                $arr['admin_id'] = $cash_info['admin_id'];
                                $arr['admin_name'] = $cash_info['admin_name'];
                                $arr['payment_code'] = $cash_info['pdc_payment_code'];
                                $arr['payment_name'] = $cash_info['pdc_payment_name'];
                                // 录入退款成功记录，减去冻结金额
                                $this->changeDeposit('cash_pay', $arr);
                                // 更新充值订单状态
                                $pdr_payment_state = -1;
                                // 退余额时未全部退完，状态为 -2 部分已退款
                                if ($cash_info['pdc_type'] == 0 && ($cash_info['pdr_amount'] - $cash_info['has_cash_amount'] > $cash_info['cash_amount'])) {
                                    $pdr_payment_state = -2;
                                }
                                $this->updateRecharge(array('pdr_sn' => $cash_info['pdr_sn']), array('pdr_payment_state' => $pdr_payment_state));
                            }
                            $this->db->commit();
                            return callback(true);
                        } catch (\Exception $e) {
                            $this->db->rollback();
                            return callback(false, $e->getMessage());
                        }
                    }
                }
                return callback(false, isset($data['err_code_des']) ? $data['err_code_des'] : $data['return_msg']);
            }
        }
        return callback(false, '参数错误');
    }

    // ------------------------------------------------- 提现申请 -------------------------------------------------
    // ----------- 写 -----------
    /**
     * 添加提现申请
     * @param $data
     * @return mixed
     */
    public function addCashApply($data) {
        return $this->db->table('cash_apply')->insert($data);
    }

    /**
     * 更新提现申请
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateCashApply($where, $data) {
        return $this->db->table('cash_apply')->where($where)->update($data);
    }

    /**
     * 删除提现申请
     * @param $where
     * @return mixed
     */
    public function deleteCashApply($where) {
        return $this->db->table('cash_apply')->where($where)->delete();
    }

    // ----------- 读 -----------
    /**
     * 获取提现申请列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getCashApplyList($where = array(), $order = '', $limit = '', $field = '*', $join = array()) {
        $table = 'cash_apply as cash_apply';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
     
        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取提现申请信息
     * @param $where
     * @return mixed
     */
    public function getCashApplyInfo($where) {
        return $this->db->table('cash_apply')->where($where)->limit(1)->find();
    }

    /**
     * 统计提现申请信息
     * @param $where
     * @return mixed
     */
    public function getTotalCashApply($where, $join = array()) {
        $table = 'cash_apply as cash_apply';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }

        return $this->db->table($table)->where($where)->limit(1)->count(1);
    }
    
    /**
     * [getCashApplyList 获取押金提现申请列表]
     * @param    array                    $where [description]
     * @param    string                   $order [description]
     * @param    string                   $limit [description]
     * @param    string                   $field [description]
     * @param    array                    $join  [description]
     * @return   [type]                          [description]
     * @Author   vincent
     * @DateTime 2017-07-25T17:02:52+0800
     */
    public function getDepositApplyList($where = array(), $order = '', $limit = '', $field = 'deposit_apply.*', $join = array()) {
        $table = 'deposit_apply as deposit_apply';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);
            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
    }
    /**
     * [getDepositApplyInfo 获取押金退款信息]
     * @param    [type]                   $where [description]
     * @return   [type]                          [description]
     * @Author   vincent
     * @DateTime 2017-07-26T15:19:08+0800
     */
    public function getDepositApplyInfo($where) {
        return $this->db->table('deposit_apply')->where($where)->limit(1)->find();
    }
    /**
     * [getTotalDepositApply 统计押金退款申请信息]
     * @param    [type]                   $where [description]
     * @param    array                    $join  [description]
     * @return   [type]                          [description]
     * @Author   vincent
     * @DateTime 2017-07-26T15:19:49+0800
     */
    public function getTotalDepositApply($where, $join = array()) {
        $table = 'deposit_apply as deposit_apply';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);
            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->where($where)->limit(1)->count(1);
    }
    /**
     * [addCashApply 添加押金退款申请]
     * @param    [type]                   $data [description]
     * @Author   vincent
     * @DateTime 2017-07-26T15:19:56+0800
     */
    public function addDepositApply($data) {
        return $this->db->table('deposit_apply')->insert($data);
    }
    /**
     * [updateDepositApply 更新押金退款申请]
     * @param    [type]                   $where [description]
     * @param    [type]                   $data  [description]
     * @return   [type]                          [description]
     * @Author   vincent
     * @DateTime 2017-07-26T17:14:00+0800
     */
    public function updateDepositApply($where, $data) {
        return $this->db->table('deposit_apply')->where($where)->update($data);
    }




    /**
     * 获取注册金提现申请列表]
     */
    public function getReginsterApplyList($where = array(), $order = '', $limit = '', $field = 'reginster_apply.*', $join = array()) {
        $table = 'reginster_apply as reginster_apply';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);
            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
    }
    /**
     * 获取注册金退款信息]
     */
    public function getReginsterApplyInfo($where) {
        return $this->db->table('reginster_apply')->where($where)->limit(1)->find();
    }
    /**
     * 统计注册金退款申请信息]
     */
    public function getTotalReginsterApply($where, $join = array()) {
        $table = 'reginster_apply as reginster_apply';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);
            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->where($where)->limit(1)->count(1);
    }
    /**
     * 添加注册金退款申请
     */
    public function addReginsterApply($data) {
        return $this->db->table('reginster_apply')->insert($data);
    }
    /**
     * 更新注册金退款申请]
     */
    public function updateReginsterApply($where, $data) {
        return $this->db->table('reginster_apply')->where($where)->update($data);
    }
}
