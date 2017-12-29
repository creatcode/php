<?php
namespace YinHan;
class rsa {
    protected $_private_key;    //私钥证书,pem格式
    protected $_public_key;     //公钥证书,pem格式

    public function __construct($config){
        if(!isset($config['privateKey']) || empty($config['privateKey']))
            exit('Please set privateKey path!');
        if(!isset($config['publicKey']) || empty($config['publicKey']))
            exit('Please set publicKey path!');

        $this->_private_key = $config['privateKey'];
        $this->_public_key  = $config['publicKey'];
    }

    public  function encode($data){
        $priKey = file_get_contents($this->_private_key);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }

    public  function decode($data, $sign){
        $pubKey = file_get_contents($this->_public_key);
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_MD5);
        openssl_free_key($res);
        return $result;
    }
}