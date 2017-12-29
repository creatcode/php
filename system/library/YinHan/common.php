<?php
namespace YinHan;
include dirname(__FILE__).DIRECTORY_SEPARATOR."des.php";
include dirname(__FILE__).DIRECTORY_SEPARATOR."rsa.php";

class common {
    //定义api接口url
    protected $apiUrl;
    //平台分配的用户编号
    protected $userCode;
    //平台KEY值
    protected $desKey;
    //设置偏移量
    protected $desIv;
    //设置私钥证书，公钥证书路径
    protected $rsa_key = array();

    public function __construct($config){
        if(is_array($config)){
            $this->apiUrl = $config['apiUrl'];
            $this->userCode = $config['userCode'];
            $this->desKey = $config['desKey'];
            $this->desIv = $config['desIv'];
            $this->rsa_key = $config['rsa_key'];
        }else{
            die("请配置系统参数");
        }
    }

    public function send($params){
        $jsonParams = json_encode($params);
        unset($params);

        //数据加密
        $desObj = new des($this->desKey, $this->desIv);
        $condition = $desObj->encrypt($jsonParams);

        //生成签名
        $rsaObj = new rsa($this->rsa_key);
        $signature = $rsaObj->encode($condition);
        unset($rsaObj);

        //构造curl请求参数
        $urlParams = array(
            "condition" => $condition,
            "userCode"  => $this->userCode,
            "signature" => $signature,
            "vector"    => $this->desIv,
        );
        $param = http_build_query($urlParams);

        //发送请求
        $data = $this->sendPost($this->apiUrl, $param);

        //解析返回数据
        $jsonData = json_decode($data);

        //结果处理
        if(is_object($jsonData)){
            if(!empty($jsonData->contents)){ //调用成功
                //解密
                $lastDate = $desObj->decrypt($jsonData->contents);
                return json_decode($lastDate);
            }elseif(!empty($jsonData->msg)){ //调用失败
                return $jsonData;
            }
        }else{
            return "检查api地址或网络";
        }
    }

    protected function sendPost($url, $param){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, false);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}
?>