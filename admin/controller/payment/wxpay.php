<?php

/**
 * Class ControllerPaymentAliPay
 * 微信回调控制器
 */
class ControllerPaymentWxPay extends Controller {
    /**
     * 支付宝有密退款回调
     */
    public function index() {
        $success = 'success';
        $fail = 'fail';
        $this->load->library('sys_model/deposit', true);

        $file_content = file_get_contents("php://input");
        file_put_contents('/data/wwwroot/default/bike/admin/wxpay_refund_notify_log.txt', $file_content . PHP_EOL, FILE_APPEND);
        if (!$file_content) {
            exit('no result');
        }

        $xml_result = simplexml_load_string($file_content);
        $new_xml = $xml_result->asXML();
        $arr = $this->from_xml($new_xml);
        echo $success;
    }
}
