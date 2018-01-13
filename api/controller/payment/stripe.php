<?php

class ControllerPaymentStripe extends Controller {

    public function pay() {
        $token = $this->request->post['token'];
        if (empty($token)) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        require_once(DIR_SYSTEM . '/library/payment/stripe-php-5.8.0/init.php'); //导入第stripe第三方文件
        \Stripe\Stripe::setApiKey("sk_test_kx9oeqZ44C63hvtLGslAgLHt"); //服务器秘钥，stripe平台获取，如果平台上更换了，这里也要换
        $charge = \Stripe\Charge::create(array(
                    "amount" => 2000, //订单金额
                    "currency" => "usd", //货币
                    "description" => "bicycle charge", //收费项目
                    "source" => $token, //前端生成的token
        ));
        if ($charge->status == 'succeeded') {//支付成功
            $this->response->showSuccessResult($charge);
        } else {//支付失败
            $this->response->showErrorResult($charge);
        }
    }

    public function stripepay() {
        $token = $this->request->post['token'];
        $pdr_sn = $this->request->post['pdr_sn'];
        if (empty($token)) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $user_id = $this->startup_user->userId();
        $user_info = $this->startup_user->getUserInfo();
        $this->load->library('sys_model/deposit', true);
        $deposit_info = $this->sys_model_deposit->getRechargeInfo(array('pdr_sn' => $pdr_sn, 'pdr_user_id' => $user_id));
        if (empty($deposit_info)) {
            $this->response->showErrorResult($this->language->get('error_pdr_sn_nonexistence'), 208);
        }
        if ($deposit_info['pdr_type'] == 1) {//雅静重复交
            if ($user_info['deposit_state'] == 1) {
                $this->response->showErrorResult($this->language->get('error_repeat_payment_deposit'), 210);
            }
        }
        $this->load->library('logic/stripe');
        $data = array(
            "amount" => bcadd($deposit_info['pdr_amount'] * 100, 0, 0), //订单金额
            "currency" => "usd", //货币
            "description" => "bicycle charge", //收费项目
            "source" => $token, //前端生成的token
        );
        $pay_result = $this->logic_stripe->pay($data, $token);
        if ($pay_result->status == 'succeeded') {//支付成功
            $this->load->library('sys_model/user');
            if ($deposit_info['pdr_type'] == 1) {//押金，改押金状态
                $update_user = array(
                    'deposit' => $deposit_info['pdr_amount'],
                    'deposit_sn' => $deposit_info['pdr_sn'],
                    'deposit_state' => 1,
                );
                $this->sys_model_user->updateUser(['user_id' => $user_id], $update_user);
            }else if($deposit_info['pdr_type'] == 0){//改余额
                $update_user = array(
                    'available_deposit' => bcadd(bcadd($user_info['available_deposit'],$deposit_info['pdr_amount'],2),$deposit_info['pdr_present_amount'],2),
                );
                $this->sys_model_user->updateUser(['user_id' => $user_id], $update_user);
            }else if($deposit_info['pdr_type'] == 2){//买月注册金
                $user_info['card_expired_time']=empty($user_info['card_expired_time'])?time():$user_info['card_expired_time'];
                $update_user = array(
                    'card_expired_time' => bcadd($user_info['card_expired_time'],60*60*24*30,0)//后面再改strtotime转换 2018-01-10留坑
                );
                $this->sys_model_user->updateUser(['user_id' => $user_id], $update_user);
            }else if($deposit_info['pdr_type'] == 3){//买年注册金
                $user_info['card_expired_time']=empty($user_info['card_expired_time'])?time():$user_info['card_expired_time'];
                $update_user = array(
                    'card_expired_time' => bcadd($user_info['card_expired_time'],60*60*24*30*12,0)
                );
                $this->sys_model_user->updateUser(['user_id' => $user_id], $update_user);
            }
            $update_deposit = array(
                'pdr_payment_code' => 'stripe',
                'pdr_payment_name' => 'stripe',
                'pdr_payment_type' => 'stripe',
                'pdr_trade_sn' => $pay_result->balance_transaction,
                'pdr_payment_state' => 1,
                'pdr_payment_time' => time()
            );
            $this->sys_model_deposit->updateRecharge(['pdr_id'=>$deposit_info['pdr_id']], $update_deposit);

            $this->response->showSuccessResult();
        } else {//支付失败
            $this->response->showErrorResult();
        }
    }

}
