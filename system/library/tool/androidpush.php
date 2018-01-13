<?php

namespace Tool;

class AndroidPush {

    /**
     * @param $deviceToken
     * @param $message
     * @param $code int 消息状态码
     * @param $exdata array 附加消息
     * @return bool|int
     * @throws \Exception
     */
    public function push($to, $exdata = []) {
        $url = "http://23.106.154.237/firebase.php"; //中转站，国内被屏蔽了
        $ch = curl_init();
        //参数设置  
        $res = curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $fields = array(
            "to" => $to,
            'content' => json_encode($exdata)
        );
        //设置post数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec ($ch);
        return $result;
    }

}
