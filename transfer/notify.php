<?php
$post = file_get_contents("php://input");
date_default_timezone_set('PRC');
$post = json_decode($post,true);
$cmd = isset($post['cmd'])?$post['cmd']:'';

file_put_contents('../system/storage/logs/notify.post.txt','---'.var_export($post,true).'---',FILE_APPEND);

//����sign�ж��Ƿ�Ϸ�������
//����notify_serial �ж��Ƿ� �ط�ָ�� ָ��Ψһ
//����timestamp' => 1513600627 �ж�ʱ���Ƿ���ȷ timestamp ����ʱ��ʱ��
//notify_time �������ķ���ʱ��

//$cmd_list = ['location','closed','opened','fault','heartbeat','default']; ����Ҫ
ob_start();
try{
    $_POST = $post;
    $_GET['route'] = "transfer/process/{$cmd}";
    require 'index.php';
}catch (Exception $e){

}finally{
    //ob_clean();
    //�����Ϣ�Ǹ����������� ������Ϣ�ɹ�  ������������ �����������ط�
    echo 'success';
}

/*function makeSign(array $data, $secret_key = '')
{
    if (!$data || !$secret_key) {
        return false;
    }
    //ǩ������һ�����ֵ����������
    ksort($data);
    //var_dump($data);
    $string = toUrlParam($data);
    //var_dump($string);
    //ǩ�����������string�����KEY����Կ
    $string = $string . "&key=" . $secret_key;
    //var_dump($string);
    //ǩ����������MD5����
    $string = md5($string);
    //ǩ�������ģ������ַ�תΪ��д
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
