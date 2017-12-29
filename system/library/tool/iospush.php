<?php
namespace Tool;

class IosPush {
    /**
     * @param $deviceToken
     * @param $message
     * @param $code int 消息状态码
     * @param $exdata array 附加消息
     * @return bool|int
     * @throws \Exception
     */
    public function push($deviceToken,$message,$code,$exdata=[]){

        //手机注册应用返回唯一的deviceToken
        //$deviceToken = '6ad7b13f b05e6137 a46a60ea 421e5016 4b701671 cc176f70 33bb9ef4 38a8aef9';
        //ck.pem通关密码
        $pass = '123456';
        //消息内容
        //$message = 'A test message!';
        //badge我也不知是什么
        $badge = 4;
        //sound我也不知是什么（或许是推送消息到手机时的提示音）
        $sound = 'Duck.wav';
        //建设的通知有效载荷（即通知包含的一些信息）
        $body = array();
        $body['id'] = "4f94d38e7d9704f15c000055";
        $body['aps'] = array('alert' => $message);
        $body['code'] = $code;
        $body['exdata'] = $exdata;
        if ($badge)
            $body['aps']['badge'] = $badge;
        if ($sound)
            $body['aps']['sound'] = $sound;
        //把数组数据转换为json数据
        $payload = json_encode($body);
        //echo strlen($payload),"\r\n";

        //下边的写法就是死写法了，一般不需要修改，
        //唯一要修改的就是：ssl://gateway.sandbox.push.apple.com:2195这个是沙盒测试地址，ssl://gateway.push.apple.com:2195正式发布地址

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', dirname(__FILE__).'/iospush/apns-dev.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);

        if (!$fp) {
            throw new \Exception("Failed to connect $err $errstr\n");
        }

        // send message
        $msg = chr(0) . pack("n",32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n",strlen($payload)) . $payload;

        $result = fwrite($fp, $msg);
        fclose($fp);

        return $result;
    }
}