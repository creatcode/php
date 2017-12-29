<?php
namespace YinHan;
include dirname(__FILE__).DIRECTORY_SEPARATOR."common.php";
class YinHan {
    private $param;
    private $config;
    private $qry_batch_no;
    public function __construct($registry) {
        $this->setConfig($registry);
    }

    private function makeQueryBatchNo() {
        return date('YmdHis') . rand(10000,99999);
    }

    public function setIDCondition($real_name, $id_card, $qry_reason = '实名认证') {
        $this->param['condition']['realName'] = $real_name;
        $this->param['condition']['idCard'] = $id_card;
        $this->param['header']['qryReason'] = $qry_reason;
    }

    public function setQueryBatchNo($qry_batch_no) {
        $this->qry_batch_no = $qry_batch_no;
    }

    protected function setConfig($registry) {
        $config = $registry->get('config');

        $this->config['apiUrl'] = $config->get('config_yin_han_api_url') . 'idcard';
        $this->config['userCode'] = $config->get('config_yin_han_user_code');
        $this->config['desKey'] = $config->get('config_yin_han_des_key');
        $this->config['desIv'] = date('dHis');
        $this->config['rsa_key'] = array(
            "privateKey" => dirname(__FILE__).DIRECTORY_SEPARATOR."rsa_key/rsa_private_key.pem", //私钥证书路径
            "publicKey"  => dirname(__FILE__).DIRECTORY_SEPARATOR."rsa_key/rsa_public_key.pem",  //公钥证书路径
        );
        $this->qry_batch_no = $this->qry_batch_no ? $this->qry_batch_no : $this->makeQueryBatchNo();
        $this->param['header'] = array(
            'qryBatchNo' => $this->qry_batch_no,
            'userCode' => $config->get('config_yin_han_user_code'),
            'sysCode' => $config->get('config_yin_han_sys_code'),
            'qryDate' => date('Ymd'),
            'qryTime' => date('His'),
        );
    }

    public function getQueryBatchNo() {
        return $this->qry_batch_no;
    }

    public function idCardAuth() {
        $common = new \YinHan\common($this->config);
        $result = $common->send($this->param);
        return $result;
    }
}