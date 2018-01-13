<?php
/**
 * 转账
 * @Author   vincent
 * @DateTime 2017-08-09T22:52:24+0800
 */
namespace Sys_Model;

require_once(DIR_SYSTEM.'/library/payment/alipay/alipay-sdk-PHP-20170725114612/AopSdk.php');

class Trans {
	private $registry;
	public $db;
	public function __construct($registry)
	{
		$this->registry = $registry;
		$this->db = $registry->get('db');
	}

	/**
	 * [makeSn 生成申请单序列号]
	 * @param    [type]                   $user_id [description]
	 * @return   [type]                            [description]
	 * @Author   vincent
	 * @DateTime 2017-08-09T22:52:24+0800
	 */
	public function makeSn($user_id) {
		return mt_rand(10, 99)
		. sprintf('%010d', time() - 946656000)
		. sprintf('%03d', (float) microtime() * 1000)
		. sprintf('%03d', (int) $user_id % 1000);
	}

	/**
	 * [getTransApplyList 获取转账申请列表]
	 * @param    array                    $where [description]
	 * @param    string                   $order [description]
	 * @param    string                   $limit [description]
	 * @param    string                   $field [description]
	 * @param    array                    $join  [description]
	 * @return   [type]                          [description]
	 * @Author   vincent
	 * @DateTime 2017-08-10T14:53:52+0800
	 */
    public function getTransApplyList($condition = array(), $order = '', $limit = '', $field = '*', $join = array()) {
        $table = 'trans_apply as trans_apply';
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

    /**
     * [getTransApplyInfo 获取转账信息]
     * @param    [type]                   $where [description]
     * @return   [type]                          [description]
     * @Author   vincent
     * @DateTime 2017-08-10T14:55:01+0800
     */
    public function getTransApplyInfo($where) {
        return $this->db->table('trans_apply')->where($where)->limit(1)->find();
    }

    /**
     * [getTotalTransApply 统计转账申请信息]
     * @param    [type]                   $where [description]
     * @param    array                    $join  [description]
     * @return   [type]                          [description]
     * @Author   vincent
     * @DateTime 2017-08-10T14:55:39+0800
     */
    public function getTotalTransApply($where, $join = array()) {
        $table = 'trans_apply as trans_apply';
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
     * [addTransApply 添加转账申请]
     * @param    [type]                   $data [description]
     * @Author   vincent
     * @DateTime 2017-08-10T14:55:50+0800
     */
    public function addTransApply($data) {
        return $this->db->table('trans_apply')->insert($data);
    }

    /**
     * [updateTransApply 更新转账申请]
     * @param    [type]                   $where [description]
     * @param    [type]                   $data  [description]
     * @return   [type]                          [description]
     * @Author   vincent
     * @DateTime 2017-08-10T14:56:06+0800
     */
    public function updateTransApply($where, $data) {
        return $this->db->table('trans_apply')->where($where)->update($data);
    }

    /**
     * [addTrans 添加转账记录]
     * @param    [type]                   $data [description]
     * @Author   vincent
     * @DateTime 2017-08-11T01:38:10+0800
     */
    public function addTrans($data){
        return $this->db->table('trans')->insert($data);
    }

    /**
     * [updateTrans 更新转账记录]
     * @param    [type]                   $where [description]
     * @param    [type]                   $data  [description]
     * @return   [type]                          [description]
     * @Author   vincent
     * @DateTime 2017-08-11T01:39:05+0800
     */
    public function updateTrans($where,$data){
        return $this->db->table('trans')->where($where)->update($data);
    }

    /**
     * [AlipayFundTransToaccountTransferRequest 支付宝转账]
     * @param    [type]                   $data    [description]
     * @param    [type]                   $options [description]
     * @return   [type]                            [description]
     * @Author   vincent
     * @DateTime 2017-08-10T19:05:16+0800
     */
    public function AlipayFundTransToaccountTransferRequest($data,$options){
        $payee_type     = isset($data['payee_type'])?$data['payee_type']:'';
        $payee_type     = preg_match("/ALIPAY_LOGONID|ALIPAY_USERID/i", $payee_type)?strtoupper($payee_type):'ALIPAY_LOGONID';//默认为ALIPAY_USERID
        //商户转账唯一订单号
        $out_biz_no     = isset($data['out_biz_no'])?$data['out_biz_no']:'';
        //收款方账户类型1、ALIPAY_USERID：支付宝账号对应的支付宝唯一用户号。以2088开头的16位纯数字组成。 2、ALIPAY_LOGONID：支付宝登录号，支持邮箱和手机号格式。
        $payee_type     = $payee_type;
        //收款方账户
        $payee_account  = isset($data['payee_account'])?$data['payee_account']:'';
        //转账金额，单位：元。只支持2位小数
        $amount         = isset($data['amount'])?$data['amount']:'';
        $amount         = (float)sprintf("%.2f",$amount);//保留两位小数
        //付款方姓名
        $payer_show_name= isset($data['payer_show_name'])?$data['payer_show_name']:'';
        //
        $payee_real_name= isset($data['payee_real_name'])?$data['payee_real_name']:'';
        //收款方真实姓名
        $remark         = isset($data['remark'])?$data['remark']:'';

        $aop  = self::getAlipayAopClient('trans');//var_dump($aop);die;
        $request = new \AlipayFundTransToaccountTransferRequest();
        $request->setBizContent("{" .
        "\"out_biz_no\":\"$out_biz_no\"," .
        "\"payee_type\":\"$payee_type\"," .
        "\"payee_account\":\"$payee_account\"," .
        "\"amount\":\"$amount\"," .
        "\"payer_show_name\":\"$payer_show_name\"," .
        //"\"payee_real_name\":\"$payee_real_name\"," .
        "\"remark\":\"$remark\"," .
        "}");

        try{
            $response = $aop->execute($request);//var_dump($response);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $response->$responseNode->code;
        }catch(\Exception $e){
            file_put_contents(DIR_LOGS.'/alipay_trans_error'.'.log', '['.date('Y-m-d H:i:s')."] [AlipayFundTransToaccountTransferRequest Error]".$request->getBizContent()."\n",FILE_APPEND);
            return callback(false,'AlipayFundTransToaccountTransferRequest:'.$e->getMessage());
        }

        if(!empty($resultCode) && $resultCode == 10000){
            $log_path   = DIR_LOGS.'/alipaytrans';
            if(!is_dir($log_path)) mkdir($log_path);
            file_put_contents($log_path.'/'.date('Y-m-d').'.log', '['.date('Y-m-d H:i:s')."] ".serialize($response->$responseNode)."\n",FILE_APPEND);
            $data   = array(
                'trace_no'      => $response->$responseNode->order_id,
                'payment_time'  => strtotime($response->$responseNode->pay_date),
                );
            return callback(true,'success',$data);
        } else {
            file_put_contents(DIR_LOGS.'/alipay_trans_error'.'.log', '['.date('Y-m-d H:i:s')."] req:".$request->getBizContent().";res:".serialize($response->$responseNode)."\n",FILE_APPEND);
            return callback(false,'AlipayFundTransToaccountTransferRequest:['.$response->$responseNode->sub_code.']'.$response->$responseNode->sub_msg);
        }
    }

    /**
     * [AlipayTradeQueryRequest 查询支付订单-alipay]
     * @param    [type]                   $data    [description]
     * @param    [type]                   $options [description]
     * @return   [type]                            [description]
     * @Author   vincent
     * @DateTime 2017-08-10T20:00:42+0800
     */
    public function AlipayTradeQueryRequest($out_trade_no=null,$trade_no=null){
        $aop        = self::getAlipayAopClient('pay');
        $request    = new \AlipayTradeQueryRequest();
        if(!empty($trade_no)){
            $bizContent     = "{" .
            "\"trade_no\":\"$trade_no\"" .
            " }";
        }elseif(!empty($out_trade_no)){
            $bizContent     = "{" .
            "\"out_trade_no\":\"$out_trade_no\"" .
            " }";
        }else{
            return callback(false,'查询参数不能为空！');
        }

        $request->setBizContent ($bizContent);
        $response = $aop->execute($request);//var_dump($response);die;
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";//var_dump($responseNode);
        $resultCode = $response->$responseNode->code;
        if(!empty($resultCode) && $resultCode == 10000){
            $data   = array(
                'trade_no'          => $response->$responseNode->trade_no,
                'out_trade_no'      => $response->$responseNode->out_trade_no,
                'buyer_logon_id'    => $response->$responseNode->buyer_logon_id,
                'trade_status'      => $response->$responseNode->trade_status,
                'total_amount'      => $response->$responseNode->total_amount,
                'receipt_amount'    => $response->$responseNode->receipt_amount,
                'buyer_pay_amount'  => $response->$responseNode->buyer_pay_amount,
                'point_amount'      => $response->$responseNode->point_amount,
                'invoice_amount'    => $response->$responseNode->invoice_amount,
                'send_pay_date'     => $response->$responseNode->send_pay_date,
                'buyer_user_id'     => $response->$responseNode->buyer_user_id,
                );
            return callback(true,'success',$data);
        } else {
            //return callback(true,'success',array('buyer_user_id'=>'opeday7339@sandbox.com'));//测试用，正式环境注释该句
            return callback(false,'AlipayTradeQueryRequest:['.$response->$responseNode->sub_code.']'.$response->$responseNode->sub_msg);
        }
    }

    /**
     * [AlipayTradeFastpayRefundQueryRequest 统一收单交易退款查询]
     * @Author   vincent
     * @DateTime 2017-08-15T16:23:30+0800
     */
    public function AlipayTradeFastpayRefundQueryRequest(array $query){
        if(empty($query)) return callback(false,'查询参数不能为空！');

        $trade_no       = isset($query['trade_no'])?$query['trade_no']:'';
        $out_trade_no   = isset($query['out_trade_no'])?$query['out_trade_no']:'';
        $out_request_no = isset($query['out_request_no'])?$query['out_request_no']:'';

        if(empty($out_request_no)){
            return callback(false,'退款请求号不能为空！');
        }
        $bizContent  = "{\"out_request_no\":\"$out_request_no\"";
        if(!empty($trade_no)){
            $bizContent     .= "," .
            "\"trade_no\":\"$trade_no\"" .
            " }";
        }elseif(!empty($out_trade_no)){
            $bizContent     .= "," .
            "\"out_trade_no\":\"$out_trade_no\"" .
            " }";
        }else{
            return callback(false,'查询参数不能为空！');
        }
        $aop        = self::getAlipayAopClient('pay');
        $request    = new \AlipayTradeFastpayRefundQueryRequest();
        $request->setBizContent ($bizContent);
        $response = $aop->execute($request);var_dump($response);die;
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";var_dump($responseNode);
        $resultCode = $response->$responseNode->code;

    }

    static private function getAlipayAopClient($type){
        static $_aops = array();
        if(!isset($_aops[$type])){
            $_aop     = new \AopClient;
            $_aop->rsaPrivateKey = 'MIIEogIBAAKCAQEApg/NyaoTbGSnyqwesfQTSWK3k0z0NJcJ+hUxOhMJy5k05tmVu2yB2NhBSt840xmaM3rhDQryXhXL4GbxnhTqOvWNMA9TMb9miWpgCmM6FnfT+037BkI0iPR/VBxjvWmVa2n0J6k7Cfe4U1TRlSkviNBGYMMOoen6OF2vbuO+MM7IRHSJBPzPOhKndMJ7XV/aipPjcF2x7ulKdL5bHwQCwQ9MCYGEkD9GzRKlg8qxEyr3UwXkV+awBB2i/CenJnPHRaa5WvqS57dX24xxCAXJi77sLYsuzy1A6BCMwfBOIg77NxVYoHZAsHcu8g0UEhiaHxcZe1f0ryPU2p7a/V4H6QIDAQABAoIBAA5J19QdTTSLaKCYtL9tF+e9lbwX5/9Ka7JX9ndfwf0BvrqKSEbrLDUDToLpuBL55fm9/fVIVthfmb5CPVHzzL+r9y0GdIsYQ/NY8tCR/yKFxvidmhbMxYZvOzF6i951YkTuxfJPrmPKK9MHvkzOUWe9PAEBZ5Xo8v28mea5s9ixHq3dG/Xyda5y3sN4mvJwhpknAa8IIr176nUYS4GfHst8gJrbotrmXYoJnAbGILdkB1HE0z3LPmVCL9OMWe8KM5InaiqzExmKnMGH4f0UXuHVGA833ULJ7KS08q/mk5UFz4te1uVUmdVCEhLWrCiwTww9yDtBRU/huX34A0ajRE0CgYEA048CAl0jiI6Ag6XuEZFMy0gpTv70ZAMz1ylvOnSZhkQhA/2QkQ84YScAndD8CMXiEFFrlA5QHg4oY7OD32meFgh6JAvedKhqCf8V9UUhoDz0kFKM4/5MqTM2A8DW6ENgSbexD2DPL+AcZWTGtdxv7OknWm2ogZK2EOFu7LDlJicCgYEAyPIb3nP8KJ7s1e9FzH2a45ne/PKfYka/ZSzEdFaVJEB+DZpYG9au5URpjgOb4UxjbZNS/iJXmChSzxJV5ouSVtXNpCYmV4MA7lZISjvQxzF24GdeJr174USl5p9BGXAHgwBw8KtQe+oZiP/2SSyDPDCWVgAiR/nVcBdx4ho8u28CgYA7v57UphNcBMr840b6RSwIGWg5PD4vZMwmTzhvrPCS//jHFLzYixDkN0ywRY54t9Jhw2z7kpND+cGkbtWmqkCQfnJobcW08r+H9TfxXkXh6HU1sWrDFVX3T1RI+RR44lZ2W1+xNSkunz/FzxFj2U7u/OY1XtUBSzsKR3C5fjgpbwKBgDll7s/8EWsI+l2hmy5uMnAQ48EdDKnly4X646LXmIJiaZ85FzHcgNi8Uepe3YfdaglCq9IrvRmZaw37Ds5rz8E+ER9yRkF5CisKOlv5+gjr+CjhXeNy/I+8LCdUFiRN+9dPqPMV/Edo9yJK+u6r6FCpEEhAgYpNssnx6HXNSMvBAoGAR4jGFVFXPj63TohWmer6apFaswvmFbbMKjJ3Eps7LerHO6lU58T/evDy5bx732pAfmD9xKapzRrc+IbznhLJsXyfOiKxRDPM2ahPRw7Iu1b68INQonMpbaunzELH+Zw1zaB57BWGRX+9Owku1i4YXbzgf+pQg24EaTkukcSP0JY=';//请填写开发者私钥去头去尾去回车，一行字符串
            $_aop->format     = "json";
            $_aop->charset    = "utf-8";
            $_aop->signType   = "RSA2";
            //$_aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
            $_aop->gatewayUrl    = "https://openapi.alipaydev.com/gateway.do";//沙箱
            $type   = strtolower($type);
            switch ($type) {
                case 'pay'://支付专用账号
                    $_aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                    //$_aop->gatewayUrl    = "https://openapi.alipaydev.com/gateway.do";//沙箱
                    $_aop->appId         = "2017080908109485";
                    //$_aop->appId         = "2016073100130803";//沙箱
                    $_aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs/aWh2IsddGsNdT75tHUKCuRLd9g2uCbREkRcDWkhy13GYofo8Vi7L0NOx1uEtZrMW5rM7YkFDIOpbI25mW8axEGT2IcyhNSnTbSUOfd2nK7OYq9HCTvysMggkyQ7HdFr5koaXDgyqdaXvCp1fdfAaDJP3C8bkwOOcmekHPUXI3DcuLgf6xzpleC8nvFjW3wHvnbkOEj3Cf82A9cJSY8wKlDSEA0oHDME73m1XKjEagD88HcIAh6yDumyltmCFsH+7ZMQOXUfLg4K9//PWCMf9xlWr/FPolsLVPsdRuVqp1LlAXZxX7zXRWqNH2NpaTFfXl8u2nZ675yDj/kSrI2hwIDAQAB';
                    //$_aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwI7oPnoIW3U7AggCzybCd4XDYGNdnf5hIVwXWTKE9QKRTCq1CeHtHb5KPqnsxdEL5ia470FjpeK325D4owz3iGUyQRXUUiRclhERMvMJ7cDRxJi35tE7RdNKk+nW0tV+MLQAZHkn68VK9VxkEZT1/cwcfrwZjwmczVqoSLHXCHh8lVlyM4kjNl7v6ft2lJWd0V42AbbMdyu6B7Hzn9bU/CcPtx+UlYfNgWLU4YeOkFLEUKyFTP2ziQ+fgBCP8KMnd/aHn+/zIcreog+YqKU7C3tBV5/8/oOV+1DcmoffoddasD7+EWa9tFUMLsU69VfGoQLJYBWcOpAZ3HsMmPN/LQIDAQAB';//沙箱
                    break;
                case 'trans'://退款专用账号
                    $_aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                    //$_aop->gatewayUrl    = "https://openapi.alipaydev.com/gateway.do";//沙箱
                    $_aop->appId         = "2017081108142957";
                    //$_aop->appId         = "2016082000296330";
                    $_aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAm3FwnFhRl6sOQr2/P9kjFNs1E38y2q8XwZzdYTqTF+mGEHALx7RQJMAZO0znEy4Ohr9ztwcnY6usjF3WWjaXnyEpOJUGU25QM/Cr26N3ooa4mBkiOn0XW37ZWRXBdBKen0CPV+StNK28eySpbR6fKde1eo/fCOLl3MOg4QPz5Cf0Z8mwphamzRfUOqasSGazUusIHLarlFt7w902+MGRQKTYKYgENikN6cu4S5AxakZQ4CSC3wNxXjqaTzefXRW2kguI8IWYU4ziMtwZDsiDwoHnVa84EDno34ZWILyZfs61uskglkPzDtqukUUb4X7VJ9d4UxJwuAnhfoVeQl3GxQIDAQAB';
                    //$_aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1aqiy01RYESwYPkBshHbJwNLpRmJKyYuATRYdgcr2MmY5k2d/Ytprk5KYFGDGLHB6hNgwgBrW9kEcvBI0dMmivglWdlngBsv4vej2z3NBIbgB1ZFR76Zq5ERE6Luv7Yu0gYcBrckEpQObZsihs30eigOip8nZiS+M5EbXv0UVjPVAi/+/OBXqJRq9rXiGUxArumfkyIUymL4ikU9dSp0on1blhH24dopW7rpeOKj1bVKTZ1FEl1DpB9kNyGYgJTM31eBruzvnB8hlMedreIuTnIEFv32VVXesgETOfBmiyrUzk3SO8zXg+HHU6fq9RHeTbDZAldCJ8D9OLB4zeDm4QIDAQAB';
                    break;
                default:
                    throw new Exception("Unknow Alipay Account Type.", 1);
                    break;
            }

            $_aops[$type]   = $_aop;
        }
        return $_aops[$type];
    }

}
