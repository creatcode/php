<?php

namespace Logic;

class Stripe {

    public function __construct($registry) {
        $this->registry = $registry;
        $this->sys_model_user = new \Sys_Model\User($registry);
    }

    /**
     * 支付
     * @param type $data 支付用的数组
     * @param type $token 前端生成的token
     * @return int
     */
    public function pay($data,$token) {

        if(empty($token)){
            return 1;
        }
        require_once(DIR_SYSTEM.'/library/payment/stripe-php-5.8.0/init.php');//导入第stripe第三方文件
	\Stripe\Stripe::setApiKey("sk_test_kx9oeqZ44C63hvtLGslAgLHt");//服务器秘钥，stripe平台获取，如果平台上更换了，这里也要换
        if(empty($data['source'])){
            $data['source']=$token;
        }
	$charge = \Stripe\Charge::create($data);
        return $charge;
    }

}
