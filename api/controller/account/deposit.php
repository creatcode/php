<?php
class ControllerAccountDeposit extends Controller {
    private $isScenic = false;
    /**
     *  支付宝押金充值
     */
    public function aliPayChargeDeposit() {
        $config = $this->getAliPayConfig();
        $user_id = $this->startup_user->userId();
        $user_info = $this->startup_user->getUserInfo();

        $pdr_sn = $this->request->post['pdr_sn'];
        if (!preg_match('/^\d{18}$/', $pdr_sn)) {
            $this->response->showErrorResult($this->language->get('error_pdr_sn_format'), 207);
        }
        $this->load->library('sys_model/deposit', true);
        $deposit_info = $this->sys_model_deposit->getRechargeInfo(array('pdr_sn' => $pdr_sn, 'pdr_user_id' => $user_id));
        if (empty($deposit_info)) {
            $this->response->showErrorResult($this->language->get('error_pdr_sn_nonexistence'), 208);
        }

        if (intval($deposit_info['pdr_payment_state'])) {
            $this->response->showErrorResult($this->language->get('error_repeat_payment'), 209);
        }

        if ($deposit_info['pdr_type'] == 1) {
            if ($user_info['deposit_state'] == 1) {
                $this->response->showErrorResult($this->language->get('error_repeat_payment_deposit'), 210);
            }
        }

        $parameter = array(
            'service' => $config['service'],
            'partner' => $config['partner'],
            'seller_id' => $config['seller_id'],
            'payment_type' => 1,
            '_input_charset' => $config['input_charset'],
            'out_trade_no' => $pdr_sn,
            'subject' => $pdr_sn,
            'total_fee' => $deposit_info['pdr_amount'],
            'body' => $this->config->get('config_name') . $this->language->get('text_voucher_platform'),
            'notify_url' => $this->config->get('config_alipay_notify_url')
        );

        $aliPaySubmit = new payment\alipay\alipaySubmit($config);
        $statement = $aliPaySubmit->buildRequestParaToString($parameter);
        $this->response->showSuccessResult(array('statement' => $statement), $this->language->get('success_get_paid_key'));
    }

    /**
     * 微信
     */
    public function wxPayChargeDeposit() {
        library('payment/wechat/wxpaydata');
        library('payment/wechat/wxpayapi');
        library('payment/wechat/wxpayconfig');

        $user_id = $this->startup_user->userId();
        $user_info = $this->startup_user->getUserInfo();

        $client = $this->request->get_request_header('client');
        // 微信端
        switch ($client) {
            case 'wechat' :
                $type = 'JSAPI';
                break;
            case 'miniapp' :
                $type = 'mini_app';
                break;
            default:
                $type = 'APP';
                break;
        }

        if (!isset($this->request->post['pdr_sn']) && empty($this->request->post['pdr_sn'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $pdr_sn = $this->request->post['pdr_sn'];
        if (!preg_match('/^\d{18}$/', $pdr_sn)) {
            $this->response->showErrorResult($this->language->get('error_pdr_sn_format'), 207);
        }
        $this->load->library('sys_model/deposit', true);
        $deposit_info = $this->sys_model_deposit->getRechargeInfo(array('pdr_sn' => $pdr_sn, 'pdr_user_id' => $user_id));
        if (empty($deposit_info)) {
            $this->response->showErrorResult($this->language->get('error_pdr_sn_nonexistence'), 208);
        }

        if ($deposit_info['pdr_type'] == 1 && $user_info['deposit_state'] == 1) {
            $this->response->showErrorResult($this->language->get('error_repeat_payment_deposit'), 210);
        }

        if ($this->request->get_request_header('sing') == 'BBC') {
            $this->isScenic = true;
        }

        if (intval($deposit_info['pdr_payment_state'])) {
            $this->response->showErrorResult($this->language->get('error_repeat_payment'), 209);
        }
        $amount = round((floatval($deposit_info['pdr_amount'])) * 100); //将元转成分
        $config = $this->getWxPayConfig($type);

        if ($type == 'APP') {
            $data['appid'] = $config['app_id'];
            $data['mch_id'] = $config['mchid'];
            $data['notify_url'] = $config['notify_url'];
            $data['nonce_str'] = $this->random_string('nozero', 14);
            $data['spbill_create_ip'] = getIP();
            $data['trade_type'] = $type;

            $data['body'] = $deposit_info['pdr_type'] ? $this->config->get('config_app_name') . '-' . ($deposit_info['pdr_type'] == 2 ? '购买骑行卡' : $this->language->get('text_deposit_recharge')) : $this->config->get('config_app_name') . '-' . $this->language->get('text_recharge');
            $data['out_trade_no'] = $pdr_sn;
            $data['total_fee'] = $amount;
        } elseif ($type == 'JSAPI' || $type == 'mini_app') {
            $openid = $this->request->post('openid');

            $data['appid'] = $config['mp_app_id'];
            $data['mch_id'] = $config['mp_mchid'];
            $data['openid'] = $openid;
            $data['notify_url'] = $config['notify_url'];
            $data['nonce_str'] = $this->random_string('nozero', 14);
            $data['spbill_create_ip'] = getIP();
            $data['trade_type'] = 'JSAPI';

            $data['body'] = $deposit_info['pdr_type'] ? $this->config->get('config_app_name') . '-' . ($deposit_info['pdr_type'] == 2 ? '购买骑行卡' : $this->language->get('text_deposit_recharge')) : $this->config->get('config_app_name') . '-' . $this->language->get('text_recharge');
            $data['out_trade_no'] = $pdr_sn;
            $data['total_fee'] = $amount;

        }

        $sign = $this->getSign($data, $type);
        $wx_arr = $data;
        $data['sign'] = $sign;
        $wx_arr['sign'] = $sign;
        $wx_arr = $this->ToXml($wx_arr);
        $wx_arr = simplexml_load_string($wx_arr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xml_data = $wx_arr->asXML();
        if (!extension_loaded('curl')) {
            trigger_error($this->language->get('error_open_curl'), E_USER_ERROR);
            exit();
        }
        $wx_return = $this->postXmlCurl($xml_data, $type);
        $return_data = $this->FromXml($wx_return);
        if ($return_data['return_code'] == 'SUCCESS' && $return_data['result_code'] == 'SUCCESS') {
            if ($type == 'APP') {
                $this->response->showSuccessResult($this->sign_to_app($return_data['prepay_id'], $type));
            } else {
                $this->response->showSuccessResult($this->getJsApiParameters($this->sign_to_app($return_data['prepay_id'], $type), $type), $this->language->get('success_checkout'));
            }
        } else {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
    }

    /**
     * 支付宝配置
     * @return array
     */
    private function getAliPayConfig() {
        $config = array();
        $config['partner'] = $this->config->get('config_alipay_partner');
        $config['seller_id'] = $this->config->get('config_alipay_seller_id');
        $config['cacert'] = getcwd() . '\\cacert.pem';
        $config['private_key_path'] = 'key/rsa_private_key.pem';
        $config['ali_public_key_path'] = 'key/alipay_public_key.pem';

        $config['key'] = $this->config->get('config_alipay_key');
        $config['notify_url'] = $this->config->get('config_alipay_notify_url');
        $config['sign_type'] = strtoupper('MD5');
        $config['input_charset'] = strtolower('utf-8');
        $config['transport'] = 'http';
        $config['payment_type'] = "1";
        $config['service'] = "mobile.securitypay.pay";

        return $config;
    }

    private function getWxPayConfig($type = 'APP') {
        $config = array();
        if ($type == 'APP') {   // 开放平台
            $config['app_id'] = $this->config->get('config_wxpay_appid');
            $config['mchid'] = $this->config->get('config_wxpay_mchid');
            $config['key'] = $this->config->get('config_wxpay_key');
            $config['app_secret'] = $this->config->get('config_wxpay_appsecert');
        } elseif ($type == 'JSAPI') {   // 公众平台
            $config['mp_app_id'] = 'wxcbfa44fc0c22072f';
            $config['mp_mchid'] = '1266405301';
            $config['mp_key'] = '0CA74876C2ED33CF74466EC5E764D5C5';
            $config['mp_app_secret'] = 'dfa95aa9409e9c8586c7d256e851ad83';
        } elseif ($type == 'mini_app') {   // 小程序
            $config['mp_app_id'] = $this->isScenic ? 'wx19b38eb4e493fa2a' : 'wx8f9bbd8556b72750';
            $config['mp_mchid'] = '1435773702';
            $config['mp_key'] = '8ea956fdc144bd71a2736b54ce14fba4';
            $config['mp_app_secret'] = $this->isScenic ? '22cc1ace52b389740247207546c16e4f' : '1540f0e905743100089f04176c6dce0f';
        }


        $config['ssl_cert_path'] = '../cert/apiclient_cert.pem';
        $config['ssl_key_path'] = '../cert/apiclient_key.pem';

        $config['curl_proxy_host'] = '0.0.0.0';
        $config['curl_proxy_port'] = 0;
        $config['report_level'] = 1;
        //$config['notify_url'] = 'https://bike.e-stronger.com/bike/wxapp/api/payment/wxpay.php';
        $config['notify_url'] = $this->config->get('config_wxpay_notify_url');

        return $config;
    }


    /**
     *
     * 获取jsapi支付的参数
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @throws WxPayException
     *
     * @return json数据，可直接填入js函数作为参数
     */
    public function getJsApiParameters($UnifiedOrderResult, $type = 'JSAPI')
    {
        $data['appId'] = $UnifiedOrderResult["appid"];
        $data['timeStamp'] = (string)time();
        $data['nonceStr'] = $this->random_string('nozero', 14);
        $data['package'] = "prepay_id=" . $UnifiedOrderResult['prepayid'];
        $data['signType'] = "MD5";
        $wxmp_arr = $data;
        $wxmp_arr['paySign'] = $this->getSign($data, $type);
//        $parameters = json_encode($wxmp_arr);
        return $wxmp_arr;
    }

    public function random_string($type = 'alnum', $len = 8)
    {
        switch ($type)
        {
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique': // todo: remove in 3.1+
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt': // todo: remove in 3.1+
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
        }
    }

    //执行第二次签名，才能返回给客户端使用
    public function sign_to_app($prepayId, $type = 'APP')
    {
        $config = $this->getWxPayConfig($type);
        if($type == 'APP') {
            $sign_to_app_data["appid"] = $config['app_id'];
            $sign_to_app_data["partnerid"] = $config['mchid'];
            $sign_to_app_data["package"] = "Sign=WXPay";
            $sign_to_app_data["noncestr"] = $this->random_string('alnum', 32);
            $sign_to_app_data["prepayid"] = $prepayId;
            $sign_to_app_data["timestamp"] = (string)time();
        }elseif($type == 'JSAPI' || $type == 'mini_app') {
            $sign_to_app_data["appid"] = $config['mp_app_id'];
            $sign_to_app_data["partnerid"] = $config['mp_mchid'];
            $sign_to_app_data["package"] = "Sign=WXPay";
            $sign_to_app_data["noncestr"] = $this->random_string('alnum', 32);
            $sign_to_app_data["prepayid"] = $prepayId;
            $sign_to_app_data["timestamp"] = (string)time();
        }
        $s = $this->getSign($sign_to_app_data, $type);
        $sign_to_app_data["sign"] = $s;
        return $sign_to_app_data;
    }

    public function getSign($wxpay_data, $type = "APP")
    {
        $config = $this->getWxPayConfig($type);
        //签名步骤一：按字典序排序参数
        ksort($wxpay_data);

        $string = $this->ToUrlParams($wxpay_data);

        //签名步骤二：在string后加入KEY
        $type == "APP" ? $KEY = $config['key'] : $KEY = $config['mp_key'];
        $string = $string . "&key=" . $KEY;

        //签名步骤三：MD5加密
        $string = md5($string);

        //签名步骤四：所有字符转为大写
        return strtoupper($string);
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($arr)
    {
        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     * */
    public function ToXml($arr)
    {
        if (!is_array($arr) || count($arr) <= 0) {
            throw new \Exception($this->language->get('error_data_parse_failure'));
        }

        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        if (!$xml) {
            //throw new WxPayException("xml数据异常！");
            echo json_encode(array('code' => FALSE, 'msg' => $this->language->get('error_data_parse_failure')));
            exit;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml 需要post的xml数据
     * @param string $url url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second url执行超时时间，默认30s
     * @throws WxPayException
     */
    public function postXmlCurl($xml, $type, $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder', $useCert = FALSE, $second = 30)
    {
        $config = $this->getWxPayConfig($type);
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        //如果有配置代理这里就设置代理
        if ($config['curl_proxy_host'] != "0.0.0.0" && $config['curl_proxy_port'] != 0) {
            curl_setopt($ch, CURLOPT_PROXY, $config['curl_proxy_host']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $config['curl_proxy_port']);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //严格校验
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $config['ssl_cert_path']);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $config['ssl_key_path']);
        }

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            echo json_encode(array('code' => FALSE, 'msg' => $this->language->get('error_curl') . $error));
            exit;
            //throw new WxPayException("curl出错，错误码:$error");
        }
    }
}
