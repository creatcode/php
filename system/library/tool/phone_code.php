<?php

namespace Tool;

class Phone_code
{
    private $adaptor;
    private $index = 0;
    private $smsSers = array(
        0 => 'Yunpian',
        1 => 'Rest',
    );

    public function __construct()
    {

    }

    public function send_sms($to, $data, $temp_id = SMS_TEMP_ID)
    {
        $res = array();
        while ($this->index < count($this->smsSers)) {
            $class = "\\Tool\\" . $this->smsSers[$this->index];//var_dump($this->index);
            $this->adaptor = new $class;
            try {
                $res = $this->adaptor->send_sms($to, $data, $temp_id = SMS_TEMP_ID);
                //file_put_contents('/dev/shm/sms/'.date('Y-m-d').'.log', date('[Y-m-d H:i:s] ').'[INFO] '.json_encode($res,JSON_UNESCAPED_UNICODE).PHP_EOL,FILE_APPEND);
                break;
            } catch (\Exception $e) {//发送失败，尝试切换备用短信服务
                file_put_contents('/dev/shm/sms/'.date('Y-m-d').'.log', date('[Y-m-d H:i:s] ').'[ERROR] '.$to.' '.$class.' '.$e->getMessage().PHP_EOL,FILE_APPEND);
                $this->index++;//var_dump($e->getMessage());
                continue;
            }
        }
        if (empty($res)) {
            return callback(false);
        } else {
            return $res;
        }
    }
}

/**
 * 容联
 */
class Rest
{
    private $handle;

    public function __construct()
    {
        $account_sid = '8a48b5514e5298b9014e62bb4deb0ee4';
        $account_token = 'f7c1d990dc6f4942ae5c54f75bf2055b';
        $app_id = '8a216da859aa5a950159b069b65e0439';
        $server_ip = 'app.cloopen.com';
        $server_port = '8883';
        $soft_version = '2013-12-26';

        $this->handle = new \Sms\Rest($server_ip, $server_port, $soft_version);
        $this->handle->setAccount($account_sid, $account_token);
        $this->handle->setAppId($app_id);
    }

    public function send_sms($phone, $code, $tempId = 150397, $text = '')
    {
        $data = array($code, SMS_TIMEOUT / 60);
        if($text){
            $data = $code;
        }
        $result = $this->handle->sendTemplateSMS($phone, $data, $tempId);//var_dump($result);
        if ($result == null) {
            throw new \Exception("Parse Result Fail.", 1);
        }
        if ($result['statusCode'] === '000000') {
            //计算发送短信数量
            $phone_num = count(explode(',',$phone));
            $data = [
                'total_count' => ceil(mb_strlen($text) / 70)*$phone_num
            ];
            return callback(true, '', $data);
        } else {
            $msg = isset($result['statusMsg']) ? $result['statusMsg'] : 'Unknow Error.';
            throw new \Exception($msg, 1);
        }
    }
}

/**
 * 云片
 */
class Yunpian
{
    private $handle;

    public function __construct()
    {
        require_once __DIR__ . '/yunpian/YunpianAutoload.php';

        $this->handle = new \SmsOperator();
    }

    public function send_sms($phone, $code, $tempId = '1882470', $text = '')
    {
        $sms_data['mobile'] = $phone;
        $sms_data['text'] = !empty($text) ? $text : "【小强单车】您的验证码是{$code}。如非本人操作，请忽略本短信";
        $result = $this->handle->batch_send($sms_data);

        if ($result->success) {
            $data = $result->getData();//var_dump($data);
            $res = isset($data['data'][0]) ? $data['data'][0] : '';
            $http_status_code = isset($res['http_status_code']) ? $res['http_status_code'] : '';
            $result_code = isset($res['code']) ? $res['code'] : '';
            if ($result_code == '0') {
                return callback(true, '', $data);
            } else {
                throw new \Exception($res['msg'], 1);
                var_dump('ERROR');
            }

        } else {
            throw new \Exception("Error Processing Request", 1);
        }
    }
}