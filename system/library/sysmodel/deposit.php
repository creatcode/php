<?php
/**
 * Created by PhpStorm.
 * User: wen
 * Date: 2016/12/7
 * Time: 15:24
 */
class Deposit {
    private $registry;
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
        return $this->db->table('deposit_recharge')->where($condition)->field($field)->order($order)->limit($limit)->select();
    }

    public function addRecharge($data) {
        return $this->db->table('deposit_recharge')->insert($data);
    }

    public function updateRecharge($where, $data) {
        return $this->db->table('deposit_recharge')->where($where)->update($data);
    }

    public function getRechargeInfo($where, $fields = '*') {
        return $this->db->table('deposit_recharge')->field($fields)->where($where)->find();
    }

    public function getRechargeCount($where) {
        return $this->db->table('deposit_recharge')->where($where)->count();
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
                $data_log['pdl_desc'] = '踩车，支付预存款，订单号：' . $data['order_sn'];
                $data_pd['available_deposit'] = array('exp', 'available_deposit-' . $data['amount']);
                //消息推送
                break;
            case 'order_freeze':
                $data_log['pdl_available_amount'] = -$data['amount'];
                $data_log['pdl_freeze_amount'] = $data['amount'];
                $data_log['pdl_desc'] = '踩车扣费，冻结预存款，订单号：' . $data['amount'];

                $data_pd['freeze_deposit'] = array('exp', 'freeze_deposit+' . $data['amount']);
                $data_pd['available_deposit'] = array('exp', 'available_deposit-' . $data['amount']);
                break;
            case 'recharge' :
                $data_log['pdl_available_amount'] = $data['amount'];
                $data_log['pdl_desc'] = '充值，充值单号：' . $data['pdr_sn'];
                $data_log['pdl_admin_name'] = $data['admin_name'];

                $data_pd['available_deposit'] = array('exp', 'available_deposit+' . $data['amount']);

                break;
            //押金申请提现
            case 'cash_apply' :
                $data_log['pdl_available_amount'] = -$data['amount'];
                $data_log['pdl_freeze_amount'] = $data['amount'];
                $data_log['pdl_desc'] = '申请提现，冻结预存款，提现单号：' . $data['pdc_sn'];

                $data_pd['available_deposit'] = array('exp', 'available_deposit-' . $data['amount']);
                $data_pd['freeze_deposit'] = array('exp', 'freeze_deposit+' . $data['amount']);
                $data_pd['available_state'] = '0';

                break;

            case 'cash_pay':
                $data_log['pdl_freeze_amount'] = -$data['amount'];
                $data_log['pdl_desc'] = '提现成功，提现单号：' . $data['order_sn'];
                $data_log['pdl_admin_name'] = $data['admin_name'];

                $data_pd['freeze_deposit'] = array('exp', array('freeze_deposit-' . $data['amount']));

                break;
            case 'cash_cancel':
                $data_log['pdl_available_amount'] = $data['amount'];
                $data_log['pdl_freeze_amount'] = -$data['amount'];
                $data_log['pdl_desc'] = '取消提现申请，解冻预存款，提现单号：' . $data['amount'];
                $data_log['pdl_admin_name'] = $data['admin_name'];

                $data_pd['freeze_deposit'] = array('exp', 'freeze_deposit-' . $data['amount']);
                $data_pd['available_deposit'] = array('exp', 'available_deposit+' . $data['amount']);
                $data_pd['available_state'] = '1';

                break;

            default:
                throw new Exception('参数错误');
        }
        //更新金额
        $update = $this->db->table('user')->where(array('user_id' => $data['user_id']))->update($data_pd);
        if (!$update) {
            throw new Exception('更新用户金额失败');
        }
        $insert = $this->db->table('deposit_log')->insert($data_log);
        if (!$insert) {
            throw new Exception('操作失败');
        }
        //支付成功，发送用户消息

        return $insert;
    }

    public function deleteRecharge($condition) {
        return $this->db->table('deposit_recharge')->where($condition)->delete();
    }

    public function addDepositCash($data) {
        return $this->db->table('deposit_cash')->insert($data);
    }

    public function getDepositCashInfo($where, $fields = '*') {
        return $this->db->table('deposit_cash')->field($fields)->where($where)->find();
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
}