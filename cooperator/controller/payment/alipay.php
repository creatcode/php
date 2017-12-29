<?php
class ControllerPaymentAliPay extends Controller {
    public function index() {

        $success = 'success';
        $fail = 'fail';
        $notify_time = $this->request->post['notify_time'];
        $notify_type = $this->request->post['notify_type'];
        $batch_no = $this->request->post['batch_no'];
        $success_num = $this->request->post['success_num'];
        $this->load->library('sys_model/deposit', true);
        $pdc_info = $this->sys_model_deposit->getDepositCashInfo(array('pdc_batch_no' => $batch_no));
        if (empty($pdc_info)) {
            exit('no result');
        }
        if ($success_num <= 0) {
            exit('success num equal zero');
        }
        if ($pdc_info['pdc_payment_state'] == 1) {
            exit($success);
        }
        if ($notify_type != 'batch_refund_notify') {
            exit('notify_type error');
        }

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
            $update = $this->sys_model_deposit->updateDepositCash(array('pdc_id' => $pdc_info['pdc_id']), $pdc_data);
            if ($update) {
                $arr['user_id'] = $pdc_info['pdc_user_id'];
                $arr['user_name'] = $pdc_info['pdc_user_name'];
                $arr['amount'] = $pdc_info['pdc_amount'];
                $arr['pdr_sn'] = $pdc_info['pdc_sn'];
                $arr['admin_name'] = $pdc_info['pdc_admin'];
                $arr['payment_code'] = $pdc_info['pdc_payment_code'];
                $arr['payment_name'] = $pdc_info['pdc_payment_name'];
                $this->sys_model_deposit->changeDeposit('cash_pay', $arr);
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
}