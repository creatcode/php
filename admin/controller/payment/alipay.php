<?php

/**
 * Class ControllerPaymentAliPay
 * 支付宝回调控制器
 */
class ControllerPaymentAliPay extends Controller {
    /**
     * 支付宝有密退款回调
     */
    public function index() {
        $success = 'success';
        $fail = 'fail';

		file_put_contents('/data/wwwroot/default/bike/admin/aaa.log', json_encode($this->request->post).PHP_EOL, FILE_APPEND);

        $notify_time = $this->request->post['notify_time'];
        $notify_type = $this->request->post['notify_type'];
        $batch_no = $this->request->post['batch_no'];
        $success_num = $this->request->post['success_num'];
        $this->load->library('sys_model/deposit', true);
        // 通过交易订单号获取最新记录
        $pdc_info = $this->sys_model_deposit->getDepositCashInfo(array('pdc_batch_no' => $batch_no));
        if (empty($pdc_info)) {
            exit('no result');
        }
        // 没有新退款成功订单，不用继续操作
        if ($success_num <= 0) {
            exit('success num equal zero');
        }
        // 订单状态已修改，不用继续操作
        if ($pdc_info['pdc_payment_state'] == 1) {
            exit($success);
        }
        // 操作类型是否批量退款回调
        if ($notify_type != 'batch_refund_notify') {
            exit('notify_type error');
        }

        // 验签
        $config = $this->getAliPayConfig();
        $noticeObj = new \Payment\alipay\alipayNotify($config);
        $verify = $noticeObj->verifyNotify();
        if (!$verify) {
            exit($fail);
        }
        $pdc_data = array();
        $pdc_data['pdc_payment_time'] = time();
        $pdc_data['pdc_payment_admin'] = 'admin';
        $pdc_data['pdc_payment_state'] = '1';

        try {
            $this->db->begin();
            // 更新提现订单
            $update = $this->sys_model_deposit->updateDepositCash(array('pdc_id' => $pdc_info['pdc_id']), $pdc_data);
            if ($update) {
                // 录入退款成功记录，减去冻结金额
                $arr['user_id'] = $pdc_info['pdc_user_id'];
                $arr['user_name'] = $pdc_info['pdc_user_name'];
                $arr['amount'] = $pdc_info['pdc_amount'];
                $arr['pdr_sn'] = $pdc_info['pdc_sn'];
                $arr['admin_name'] = $pdc_info['pdc_admin'];
                $arr['payment_code'] = $pdc_info['pdc_payment_code'];
                $arr['payment_name'] = $pdc_info['pdc_payment_name'];
                $this->sys_model_deposit->changeDeposit('cash_pay', $arr);
                // 更新充值订单
                $update = $this->sys_model_deposit->updateRecharge(array('pdr_sn' => $pdc_info['pdr_sn']), array('pdr_payment_state' => -1));
                if (!$update) {
                    throw new \Exception('更新充值订单失败');
                }
            }
            $this->db->commit();
            exit($success);
        } catch (\Exception $e) {
            $this->db->rollback();
            exit($fail);
        }
    }

    /**
     * 获取支付宝配置参数
     * @return array
     */
    private function getAliPayConfig() {
        $config = array(
            'partner' => $this->config->get('config_alipay_partner'),
            'seller_id' => $this->config->get('config_alipay_seller_id'),
            'key' => $this->config->get('config_alipay_key'),
            'sign_type' => strtoupper('md5'),
            'transport' => 'http',
            'cacert' => getcwd() . '\\cacert.pem'
        );
        return $config;
    }

    /**
     * 支付宝无密退款回调
     * @return array|bool
     */
    public function refund() {
		echo 'success';
        $this->load->library('sys_model/deposit', true);

        // 调试日志
        file_put_contents('/data/wwwroot/default/bike/admin/alipay_refund_notify_log.txt', date('Y-m-d H:i:s') . json_encode($this->request->post) . "\n", 8 );
        // 回调操作类型
        $notify_type = $this->request->post('notify_type');
        // 回调详细数据
        $result_details = $this->request->post('result_details');
        // 成功退款订单数
        $success_num = $this->request->post('success_num');
        // 批量退款订单号
        $batch_no = $this->request->post('batch_no');

        // 是否由批量退款接口回调
        if ($notify_type != 'batch_refund_notify') {
            file_put_contents('/data/wwwroot/default/bike/admin/alipay_refund_notify_log_err.txt', date('Y-m-d H:i:s') .'[unknow notify_type]'. json_encode($this->request->post) . "\n", 8 );
            return false;
        }
        // 拆解详细数据
        preg_match('/(\d*)\^([0-9.]*)\^(\w*)/', $result_details, $match);
        // 成功退款数大于 0
        if ($success_num > 0 && isset($match[3]) && $match[3] == 'SUCCESS') {
            $trace_no = isset($match[1]) ? $match[1] : '';
            $cash_amount = isset($match[2]) ? (float)$match[2] : 0;
            // 支付宝交易号不能为空
            if (empty($trace_no)) {
                file_put_contents('/data/wwwroot/default/bike/admin/alipay_refund_notify_log_err.txt', date('Y-m-d H:i:s') .'[unknow trace_no]'. json_encode($this->request->post) . "\n", 8 );
                return false;
            }

            // 查询提现订单
            $condition = array(
                //'pdc_batch_no' => $batch_no,
                'trace_no' => $trace_no,
            );
            $cash_info = $this->sys_model_deposit->getDepositCashInfo($condition);

            // 提现订单是否存在
            if (!is_array($cash_info) || empty($cash_info)) {
                file_put_contents('/data/wwwroot/default/bike/admin/alipay_refund_notify_log_err.txt', date('Y-m-d H:i:s') .'[unknow cash_info]'. json_encode($this->request->post) . "\n", 8 );
                return false;
            }
            // 订单已处理
            if ($cash_info['pdc_payment_state'] == 1) {
                file_put_contents('/data/wwwroot/default/bike/admin/alipay_refund_notify_log_err.txt', date('Y-m-d H:i:s') .'[payment has done]'. json_encode($this->request->post) . "\n", 8 );
                return false;
            }
            // 退款金额不一致
            if ($cash_amount != $cash_info['pdc_amount']) {
                file_put_contents('/data/wwwroot/default/bike/admin/alipay_refund_notify_log_err.txt', date('Y-m-d H:i:s') .'[amount not match]'. json_encode($this->request->post) . "\n", 8 );
                return false;
            }

            // 充值订单总金额
            $condition = array(
                'pdr_sn' => $cash_info['pdr_sn'],
            );
            $fields = 'pdr_amount';
            $recharge_info = $this->sys_model_deposit->getOneRecharge($condition, $fields);
            $pdr_amount = !empty($recharge_info) && isset($recharge_info['pdr_amount']) ? (int)$recharge_info['pdr_amount'] : 0;

            // 充值订单已退金额
            $condition = array(
                'pdr_sn' => $cash_info['pdr_sn'],
            );
            $fields = 'sum(`pdc_amount`) as total';
            $refunded_info = $this->sys_model_deposit->getDepositCashInfo($condition, $fields);
            $has_cash_amount = !empty($refunded_info) && isset($refunded_info['total']) ? (int)$refunded_info['total'] : 0;

            $pdr_payment_state = -1;
            // 退余额时未全部退完，状态为 -2 部分已退款
            if ($cash_info['pdc_type'] == 0 && ($pdr_amount - $has_cash_amount > $cash_amount)) {
                $pdr_payment_state = -2;
            }

            try {
                // 开始数据库事务处理
                $this->db->begin();
                // 更新提现订单信息
                $pdc_data = array();
                $pdc_data['pdc_payment_time'] = time();
                $pdc_data['pdc_payment_state'] = '1';
                $update = $this->sys_model_deposit->updateDepositCash(array('pdc_id' => $cash_info['pdc_id']), $pdc_data);

                // 退款记录数据
                $arr['user_id'] = $cash_info['pdc_user_id'];
                $arr['user_name'] = $cash_info['pdc_user_name'];
                $arr['amount'] = $cash_info['pdc_amount'];
                $arr['pdr_sn'] = $cash_info['pdc_sn'];
                $arr['admin_id'] = $cash_info['pdc_admin_id'];
                $arr['admin_name'] = $cash_info['pdc_admin_name'];
                $arr['payment_code'] = $cash_info['pdc_payment_code'];
                $arr['payment_name'] = $cash_info['pdc_payment_name'];
                // 录入退款成功记录，减去冻结金额
                $update2 = $this->sys_model_deposit->changeDeposit('cash_pay', $arr);
                // 更新退款订单数据
				$condition = array(
					'pdr_sn' => $cash_info['pdr_sn'],
				);
				$data = array(
					'pdr_payment_state' => $pdr_payment_state
				);
                $update3  = $this->sys_model_deposit->updateRecharge($condition, $data);

                if ($update && $update2 && $update3) {
                    $this->db->commit();
                    return true;
                }else{
                    $this->db->rollback();
                    file_put_contents('/data/wwwroot/default/bike/admin/alipay_refund_notify_log_err.txt', date('Y-m-d H:i:s') .'[update db fail update:'.$update.';update2:'.$update2.';update3:'.$update3.']'. json_encode($this->request->post) . "\n", 8 );
                    return true;
                }


            } catch (\Exception $e) {
                $this->db->rollback();
                file_put_contents('/data/wwwroot/default/bike/admin/alipay_refund_notify_log_err.txt', date('Y-m-d H:i:s') .'[sys error]['.$e->getMessage().']'. json_encode($this->request->post) . "\n", 8 );
                return false;
            }
        }
        //add vincent:2017-08-09 增加由于交易完成导致退款失败的状态：4
        elseif(isset($match[1]) && $match[3] == 'TRADE_HAS_FINISHED'){//交易已完成，不允许退款
            $trace_no = isset($match[1]) ? $match[1] : '';
            $condition = array(
                 'trace_no' => $trace_no,
                 'pdc_payment_state' => array('eq', '2')//1已退款，2退款中，3申请手工退款中，4交易完成无法退款[订单超过三个月]
            );
            $cash_info = $this->sys_model_deposit->getDepositCashInfo($condition);
            if(!empty($cash_info)){
                $pdc_data = array(
                     'pdc_payment_state' => '4'
                );
                $update = $this->sys_model_deposit->updateDepositCash(array('pdc_id' => $cash_info['pdc_id']), $pdc_data);
            }
        }
        // 未知作用
//        elseif(isset($match[1])) {
//            //
//            $trace_no = isset($match[1]) ? $match[1] : '';
//			$condition = array(
//				'trace_no' => $trace_no,
//				'pdc_payment_state' => array('neq', '1')
//			);
//            $cash_info = $this->sys_model_deposit->getDepositCashInfo($condition);
//			$pdc_data = array(
//				'pdc_payment_state' => '0'
//			);
//			$update = $this->sys_model_deposit->updateDepositCash(array('pdc_id' => $cash_info['pdc_id']), $pdc_data);
//		}
    }
}
