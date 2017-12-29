<?php

/**
 *
 */
namespace Sms;

class Rest
{
    private $account_sid;
    private $account_token;
    private $app_id;
    private $sub_account_sid;
    private $sub_account_token;
    private $vo_ip_account;
    private $vo_ip_password;
    private $server_ip;
    private $server_port;
    private $soft_version;
    private $batch;
    private $body_type = 'json';
    private $enable_log = true;
    private $filename = '../log.txt';
    private $handle;

    public function __construct($server_ip = '', $server_port = '', $soft_version = '')
    {
        $this->batch = date("YmdHis");
        $this->server_ip = $server_ip;
        $this->server_port = $server_port;
        $this->soft_version = $soft_version;
        $this->handle = fopen($this->filename, 'a');
    }

    public function setAccount($account_sid, $account_token)
    {
        $this->account_sid = $account_sid;
        $this->account_token = $account_token;
    }

    public function setSubAccount($sub_account_sid, $sub_account_token, $vo_ip_account, $vo_ip_password)
    {
        $this->sub_account_sid = $sub_account_sid;
        $this->sub_account_token = $sub_account_token;
        $this->vo_ip_account = $vo_ip_account;
        $this->vo_ip_password = $vo_ip_password;
    }

    public function setAppId($app_id)
    {
        $this->app_id = $app_id;
    }

    public function showLog($log)
    {
        if ($this->enable_log) {
            fwrite($this->handle, $log . "\n");
        }
    }

    function curl_post($url, $data, $header, $post = 1)
    {
        $ch = curl_init();
        $res = curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, $post);
        if ($post) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);

        if ($result == false) {
            if ($this->body_type == 'json') {
                $result = callback(false, '网络错误');
            } else {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><Response><statusCode>172001</statusCode><statusMsg>网络错误</statusMsg></Response>";
            }
        }

        curl_close($ch);

        return $result;
    }

    public function account_auth()
    {
        if ($this->server_ip == '') {
//            $data = new stdClass();
//            $data->state = false;
//            $data->msg = 'IP为空';
//            return $data;
            return callback(false, 'IP为空');
        }
        if ($this->server_port <= 0) {
            return callback(false, '端口错误（小于等于0）');
        }

        if ($this->soft_version == "") {
            return callback(false, '版本号为空');
        }

        if ($this->account_sid == "") {
            return callback(false, '主账号为空');
        }

        if ($this->account_token == "") {
            return callback(false, '主账号令牌为空');
        }

        if ($this->app_id == "") {
            return callback(false, '应用ID为空');
        }

        return callback(true);
    }

    public function sub_auth()
    {
        if ($this->server_ip == '') {
//            $data = new stdClass();
//            $data->state = false;
//            $data->msg = 'IP为空';
//            return $data;
            return callback(false, 'IP为空');
        }
        if ($this->server_port <= 0) {
            return callback(false, '端口错误（小于等于0）');
        }

        if ($this->soft_version == "") {
            return callback(false, '版本号为空');
        }

        if ($this->account_sid == "") {
            return callback(false, '主账号为空');
        }

        if ($this->account_token == "") {
            return callback(false, '主账号令牌为空');
        }

        if ($this->app_id == "") {
            return callback(false, '应用ID为空');
        }

        return callback(true);
    }

    public function queryAccountInfo()
    {
        $auth = $this->account_auth();
        if ($auth['state'] == false) {
            return $auth;
        }
        $sig = strtoupper(md5($this->account_sid . $this->account_token . $this->batch));
        $url = "https://$this->server_ip:$this->server_port/$this->soft_version/Accounts/$this->account_sid/AccountInfo?sig=$sig";
        $this->showLog("request url = " . $url);

        $auth_en = base64_encode($this->account_sid . ":" . $this->batch);
        $header = array("Accept:application/$this->body_type", "Content-Type:application/$this->body_type;charset=utf-8", "Authorization:$auth_en");
        $result = $this->curl_post($url, "", $header, 0);
        $this->showLog("response body = " . $result);
        if ($this->body_type == 'json') {
            $datas = json_decode($result);
        } else {
            $datas = simplexml_load_string(trim($result, "\t\n\r"));
        }
        return $datas;
    }

    public function querySubAccount($friendlyName)
    {
        $auth = $this->account_auth();
        if ($auth['state'] == false) {
            return $auth;
        }

        if ($this->body_type == 'json') {
            $body = "{'appId':'$this->app_id','friendlyName':'$friendlyName'}";
        } else {
            $body = "
            <SubAccount>
              <appId>$this->app_id</appId>
              <friendlyName>$friendlyName</friendlyName>
            </SubAccount>";
        }
        $this->showLog("request body = " . $body);

        $sig = strtoupper(md5($this->account_sid . $this->account_token . $this->batch));

        $url = "https://$this->server_ip:$this->server_port/$this->soft_version/Accounts/$this->account_sid/QuerySubAccountByName?sig=$sig";
        $this->showLog("request url = " . $url);

        $auth_en = base64_encode($this->account_sid . ":" . $this->batch);

        $header = array("Accept:application/$this->body_type", "Content-Type:application/$this->body_type;charset=utf-8", "Authorization:$auth_en");

        $result = $this->curl_post($url, $body, $header);

        $this->showLog("response body = " . $result);
        if ($this->body_type == "json") {//JSON格式
            $datas = json_decode($result);
        } else { //xml格式
            $datas = simplexml_load_string(trim($result, " \t\n\r"));
        }
        return $datas;
    }

    public function sendSMS($to, $sms_body)
    {
        $auth = $this->account_auth();
        if ($auth['state'] == false) {
            return $auth;
        }

        if ($this->body_type == 'json') {
            $body = "{'to':'$to','body':'$sms_body','appId':'$this->app_id'}";
        } else {
            $body = "<SMSMessage>
                    <to>$to</to>
                    <body>$sms_body</body>
                    <appId>$this->app_id</appId>
                  </SMSMessage>";
        }
        $this->showLog("request body = " . $body);
        $sig = strtoupper(md5($this->account_sid . $this->account_token . $this->batch));
        $url = "https://$this->server_ip:$this->server_port/$this->soft_version/Accounts/$this->account_sid/SMS/Messages?sig=$sig";
        $this->showLog("request url = " . $url);
        $auth_en = base64_encode($this->account_sid . ":" . $this->batch);
        $header = array("Accept:application/$this->body_type", "Content-Type:application/$this->body_type;charset=utf-8", "Authorization:$auth_en");

        $result = $this->curl_post($url, $body, $header);
        $this->showLog("response body = " . $result);
        if ($this->body_type == "json") {
            $data = json_decode($result);
        } else {
            $data = simplexml_load_string(trim($result, " \t\n\r"));
        }
        return $data;
    }

    function sendTemplateSMS($to, $datas, $tempId)
    {

        $auth = $this->account_auth();
        if ($auth['state'] == false) {
            return $auth;
        }

        if ($this->body_type == "json") {
            $data = "";
            for ($i = 0; $i < count($datas); $i++) {
                $data = $data . "'" . $datas[$i] . "',";
            }
            $body = "{'to':'$to','templateId':'$tempId','appId':'$this->app_id','datas':[" . $data . "]}";
        } else {
            $data = "";
            for ($i = 0; $i < count($datas); $i++) {
                $data = $data . "<data>" . $datas[$i] . "</data>";
            }
            $body = "<TemplateSMS>
                    <to>$to</to>
                    <appId>$this->app_id</appId>
                    <templateId>$tempId</templateId>
                    <datas>" . $data . "</datas>
                  </TemplateSMS>";
        }
        $this->showLog("request body = " . $body);
        // 大写的sig参数
        $sig = strtoupper(md5($this->account_sid . $this->account_token . $this->batch));
        // 生成请求URL
        $url = "https://$this->server_ip:$this->server_port/$this->soft_version/Accounts/$this->account_sid/SMS/TemplateSMS?sig=$sig";
        $this->showLog("request url = " . $url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $auth_en = base64_encode($this->account_sid . ":" . $this->batch);
        // 生成包头
        $header = array("Accept:application/$this->body_type", "Content-Type:application/$this->body_type;charset=utf-8", "Authorization:$auth_en");

        // 发送请求
        $result = $this->curl_post($url, $body, $header);
        //p($url);p($body);p($header);p($result);
        $this->showLog("response body = " . $result);
        if ($this->body_type == "json") {//JSON格式
            $datas = json_decode($result, true);
        } else { //xml格式
            $datas = simplexml_load_string(trim($result, " \t\n\r"));
        }
        return $datas;
        /*if ($datas['statusCode'] == 0) {
            if ($this->body_type == "json") {
                $datas['TemplateSMS'] = $datas['templateSMS'];
                unset($datas->templateSMS);
            }
            return callback(true);
        } elseif ($datas['statusCode'] == 160040) {
            return callback(false, '', array('code'=>160040));
        }  else {
            return callback(false);
        }*/
    }
}