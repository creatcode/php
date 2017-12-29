<?php

/**
 * 支付宝支付成功回调地址
 */
class ControllerPaymentAlipay extends Controller {
    public function notify() {
        $success = 'success';
        $failure = 'fail';
//        $this->log->write(json_encode($this->request->post));
        file_put_contents('alipay_notify_log.txt', date('Y-m-d H:i:s') . json_encode($this->request->post) . "\n", 8 );
        $pdr_sn = $this->request->post['out_trade_no'];
        $trade_no = $this->request->post['trade_no'];
        $config = $this->getConfig();
        $noticeObj = new \payment\alipay\AlipayNotify($config);
        $this->load->library('sys_model/deposit', true);
        $recharge_info = $this->sys_model_deposit->getRechargeInfo(array('pdr_sn' => $pdr_sn));
        if (empty($recharge_info)) {
            exit('no result');
        }
        if ($recharge_info['pdr_payment_state'] == 1) {
            exit($success);
        }
        $verify = $noticeObj->verifyNotify();
        if (!$verify) {
            //exit($failure);
        }
        $trade_status = $this->request->post['trade_status'];
        file_put_contents('trade_status.log', $trade_status . "\n", 8);
        if ($trade_status != 'TRADE_SUCCESS' && $trade_status != 'TRADE_FINISHED') {
            exit($failure);
        }

        $payment_type = 'app';

        $payment_info = array(
            'payment_code' => 'alipay',
            'payment_name' => $this->language->get('text_alipay_payment'),
            'payment_type' => $payment_type,
            'payment_time' => strtotime($this->request->post['gmt_payment'])
        );

        $result = $this->sys_model_deposit->updateDepositChargeOrder($trade_no, $pdr_sn, $payment_info, $recharge_info);
        exit($result['state'] ? $success : $failure);
    }

    private function getConfig() {
        $config = array();
        $config['partner'] = $this->config->get('config_alipay_partner');
        $config['seller_id'] = $this->config->get('config_alipay_seller_id');
        $config['cacert'] = getcwd() . '\\cacert.pem';
        $config['private_key_path'] = 'key/rsa_private_key.pem';
        $config['ali_public_key_path'] = 'key/alipay_public_key.pem';

        $config['key'] = $this->config->get('config_alipay_key');
        $config['notify_url'] = "";
        $config['sign_type'] = strtoupper('MD5');
        $config['input_charset'] = strtolower('utf-8');
        $config['transport'] = 'http';
        $config['payment_type'] = "1";
        $config['service'] = "mobile.securitypay.pay";

        return $config;
    }

    /**
     * 退款异步通知
     * @return array|bool
     */
    public function refund_notify() {
        $this->load->library('sys_model/deposit', true);

        // 调试日志
        file_put_contents('alipay_refund_notify_log.txt', date('Y-m-d H:i:s') . json_encode($this->request->post) . "\n", 8 );
        $notify_type = $this->request->get('notify_type');
        $result_details = $this->request->get('result_details');
        if ($notify_type != 'batch_refund_notify') {
            return false;
        }
        preg_match('/(\d*)\^([0-9.]*)\^(\w*)/', $result_details, $match);
        if (isset($match[3]) && $match[3] == 'SUCCESS') {
            $trace_no = isset($match[1]) ? $match[1] : '';
            if (empty($trace_no)) {
                return false;
            }
            try {
                // 查询提现订单
                $condition = array(
                    'trace_no' => $trace_no
                );
                $cash_info = $this->sys_model_deposit->getDepositCashInfo($condition);
                if (!is_array($cash_info) || empty($cash_info)) {
                    return false;
                }
                // 开始数据库事务处理
                $this->db->begin();
                // 更新提现订单信息
                $pdc_data = array();
                $pdc_data['pdc_payment_time'] = time();
                $pdc_data['pdc_payment_admin'] = 'admin';
                $pdc_data['pdc_payment_state'] = '1';
                $update = $this->sys_model_deposit->updateDepositCash(array('pdc_id' => $cash_info['pdc_id']), $pdc_data);
                if ($update) {
                    $arr['user_id'] = $cash_info['pdc_user_id'];
                    $arr['user_name'] = $cash_info['pdc_user_name'];
                    $arr['amount'] = $cash_info['pdc_amount'];
                    $arr['pdr_sn'] = $cash_info['pdc_sn'];
                    $arr['admin_name'] = 'admin';
                    $arr['payment_code'] = $cash_info['pdc_payment_code'];
                    $arr['payment_name'] = $cash_info['pdc_payment_name'];

                    $this->sys_model_deposit->changeDeposit('cash_pay', $arr);
                    $update = $this->sys_model_deposit->updateRecharge(array('pdr_sn' => $cash_info['pdr_sn']), array('pdr_payment_state' => -1));
                    if (!$update) {
                        throw new \Exception('更新充值单号失败');
                    }
                }
                $this->db->commit();
                return true;
            } catch (\Exception $e) {
                $this->db->rollback();
                return false;
            }
        }
    }
}
