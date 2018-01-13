<?php
$post = file_get_contents("php://input");
date_default_timezone_set('PRC');
$post = json_decode($post,true);
$cmd = isset($post['cmd'])?$post['cmd']:'';

file_put_contents('../system/storage/logs/notify.post.txt','---'.var_export($post,true).'---',FILE_APPEND);

//根据sign判断是否合法的命令
//根据notify_serial 判断是否 重发指令 指令唯一
//根据timestamp' => 1513600627 判断时序是否正确 timestamp 锁的时序时间
//notify_time 是锁服的发送时间

//$cmd_list = ['location','closed','opened','fault','heartbeat','default']; 不需要
ob_start();
try{
    $_POST = $post;
    $_GET['route'] = "transfer/process/{$cmd}";
    require 'index.php';
}catch (Exception $e){

}finally{
    //ob_clean();
    //这个信息是告诉锁服务器 接收信息成功  如果不返回这个 锁服务器会重发
    echo 'success';
}

/*function makeSign(array $data, $secret_key = '')
{
    if (!$data || !$secret_key) {
        return false;
    }
    //签名步骤一：按字典序排序参数
    ksort($data);
    //var_dump($data);
    $string = toUrlParam($data);
    //var_dump($string);
    //签名步骤二：在string后加入KEY：密钥
    $string = $string . "&key=" . $secret_key;
    //var_dump($string);
    //签名步骤三：MD5加密
    $string = md5($string);
    //签名步骤四：所有字符转为大写
    $result = strtoupper($string);

    return $result;
}

function toUrlParam(array $data)
{
    $buff = "";
    foreach ($data as $k => $v) {
        if ($k != "sign" && $v != "") {
            if (is_array($v)) {
                if (!empty($v)) {
                    $v = json_encode($v);
                } else {
                    continue;
                }
            }
            $buff .= $k . "=" . $v . "&";
        }
    }
    $buff = trim($buff, "&");

    return $buff;
}

$secret_key='BE6pLoackN3KH9ePIShGhXCwROJeU7y0';*/
