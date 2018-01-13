<?php
namespace Logic;

class Deposit {
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->sys_model_deposit = new \Sys_Model\Deposit($registry);
    }

    /**
     * 生成充值订单
     * @param $data
     * @return array
     */
    public function addRecharge($data) {
        $recharge_sn = $this->sys_model_deposit->makeSn($data['user_id']);
        $insert = array(
            'pdr_sn' => $recharge_sn,
            'pdr_user_id' => $data['user_id'],
            'pdr_user_name' => $data['user_name'],
            'pdr_amount' => $data['amount'],
            'pdr_type' => isset($data['type']) ? $data['type'] : 0,
            'pdr_trade_sn' => $recharge_sn,
            'pdr_add_time' => time(),
            'pdr_present_amount' => $data['pdr_present_amount']
        );

        if (isset($data['is_scenic'])) $insert['is_scenic'] = 1;	

        //充值优惠
        if ($insert['pdr_type'] == 0 && in_array($data['user_name'], array('15016870422', '13035837339', '13925716936'))) {
            $where = array(
                'recharge_amount' => $data['amount'],
                'start_time' => array('elt', time()),
                'end_time' => array('egt', time()),
                'state' => 1
            );
            $present_info = $this->db->table('present_recharge')->where($where)->find();

            if (!empty($present_info)) {
                $insert['pdr_present_amount'] = $present_info['present_amount'];
            }
        }

        $insert_id = $this->sys_model_deposit->addRecharge($insert);
        if ($insert_id) {
            $data = array('pdr_id' => $insert_id, 'pdr_sn' => $recharge_sn);
            return callback(true, '生成充值押金订单成功', $data);
        } else {
            return callback(false, '数据库操作失败，生成订单失败');
        }
    }

    /**
     * 获取用户的钱包明细
     * @param $user_id
     * @param $page
     * @return array
     */
    public function getDepositLogByUserId($user_id, $page = 1) {
        $page = intval($page);
        $page = $page<1 ? 1 : $page;
        $limit = (empty($page) || $page<1) ? 10 : (10 * ($page-1) . ', 10');
        $data = $this->sys_model_deposit->getDepositLogList(array('pdl_user_id'=>$user_id, 'pdl_type' => array('neq', 'cash_pay')), '*', 'pdl_add_time DESC', $limit);
        foreach ($data as &$item) {
            $item['deposit_type'] = $this->_getFriendlyDepositType($item['pdl_type']);
        }
        return callback(true, '操作成功', $data);
    }

    /**
     * 获取用户的钱包明细条目数
     * @param $user_id
     * @return integer
     */
    public function getDepositLogCountByUserId($user_id) {
        return $this->sys_model_deposit->getDepositLogCount(array('pdl_user_id'=>$user_id, 'pdl_type' => array('neq', 'cash_pay')));
    }

    private function _getFriendlyDepositType($type) {
        static $DEPOSIT_TYPE = array(
            'deposit' => '交押金成功',
            'recharge' => '充值成功',
            'order_pay' => '骑行花费',
            'order_freeze' => '骑行花费',
            'cash_apply' => '申请提现',
            'cash_pay' => '提现成功',
            'cash_cancel' => '取消提现申请',
            'deposit_cash_apply' => '申请退款',
            'pay_pre_finish' => '未正常结束补扣',
            'balance_cash_apply' => '余额提现',
            'recharge_offer' => '赠送金额',
            'charge_month_card' => '购买骑行卡'
        );
        return isset($DEPOSIT_TYPE[$type]) ? $DEPOSIT_TYPE[$type] : 'UnknownType';
    }
}

