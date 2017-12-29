<?php
class ControllerPaymentWxpay extends Controller {
    public function notify() {
        $file_content = file_get_contents("php://input");
        if (!$file_content) {
            exit('no result');
        }

        $xml_result = simplexml_load_string($file_content);
        $new_xml = $xml_result->asXML();
        $arr = $this->from_xml($new_xml);
        $success = 'success';
        $failure = 'fail';
        $pay_sn = $trade_no = $arr['out_trade_no'];
        $this->load->library('sys_model/deposit', true);
        $recharge_info = $this->sys_model_deposit->getRechargeInfo(array('pdr_sn' => $pay_sn));
        if (empty($recharge_info)) {
            exit('no result');
        }
        if ($recharge_info['pdr_payment_state'] == 1) {
            exit($success);
        }

        // 支付途径
        $payment_type = 'app';
        if (isset($arr['trade_type']) && $arr['trade_type'] == 'JSAPI') {
            // 微信端appId
            $wechat_appid = $this->config->get('config_wxpay_mp_app_id');
            // 小程序appId
            $mini_app_appid = $this->config->get('config_wxpay_app_app_id');
            if ($arr['appid'] == $wechat_appid) {                   // 是否通过微信端支付
                $payment_type = 'web';
            } else if ($arr['appid'] == $mini_app_appid) {          // 是否通过小程序支付
                $payment_type = 'mini_app';
            }
        }

        $_pay_time=$arr["time_end"];
        $pay_time =substr($_pay_time,0,4).'-'.substr($_pay_time,4,2).'-'.substr($_pay_time,6,2).' ';
        $pay_time.=substr($_pay_time,8,2).':'.substr($_pay_time,10,2).':'.substr($_pay_time,12,2);
        $payment_info = array(
            'payment_code' => 'wxpay',
            'payment_name' => $this->language->get('text_weixin_payment'),
            'payment_type' => $payment_type,
            'payment_time' => strtotime($pay_time)
        );

        $result = $this->sys_model_deposit->updateDepositChargeOrder($trade_no, $pay_sn, $payment_info, $recharge_info);

        if ($result['state'] && $recharge_info['is_scenic'] == 1) {
            $insert = array(
                'user_id' => $recharge_info['pdr_user_id'],
                'recharge_sn' => $recharge_info['pdr_sn'],
            );
            $this->db->table('temp_recharge')->insert($insert);
        }

        exit($result['state'] ? $success : $failure);
    }

    public function from_xml($xml) {
        if (!$xml) {
            echo json_encode(array('code' => false, 'msg' => $this->language->get('error_xml_data')));
            exit;
        }
        //禁止引用外部XML实体
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }
}
