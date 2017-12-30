<?php
/**
 * Time: 2017/2/23 20:31
 */
define('WEIXIN_URL', 'http://bike.e-stronger.com/bike/wechat');
define('ANDROID_DOWNLOAD_URL', 'http://a.app.qq.com/o/simple.jsp?pkgname=cn.estronger.bike');
define('IOS_DOWNLOAD_URL', 'https://itunes.apple.com/cn/app/xiao-qiang-dan-che/id1196263366');
define('WEIXIN_LITTLE_URL', 'http://bike.e-stronger.com/bike/app.php');
error_reporting(0);

$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
file_put_contents('/dev/shm/logs/app.log', 'ua:'.$ua."\n", FILE_APPEND);
if($ua != '' && preg_match("/MicroMessenger/i", $ua)) {
    //判断是不是蓝牙单车
    $bicycle_sn = $_GET['b'];
    if(is_numeric($bicycle_sn)){
        try {
            $db = new mysqli("10.29.255.105","bike","W8L~UV.0RkZVoBlNJCYr","bike");
            $sql = 'select lock.lock_type from rich_bicycle bicycle join rich_lock `lock` on bicycle.lock_sn = lock.lock_sn where bicycle.full_bicycle_sn = ?';
            file_put_contents('/dev/shm/logs/app.log', date('Y-m-d H:i:s ') .$sql ."\n", FILE_APPEND);
            $re = $db->prepare($sql);
            $re->bind_param('i', $bicycle_sn);
            $re->execute();
            $re->bind_result($lock_type);
            $re->fetch();
            file_put_contents('/dev/shm/logs/app.log', date('Y-m-d H:i:s ') .$bicycle_sn .':' . $lock_type ."\n", FILE_APPEND);
            if($lock_type == 2 || $lock_type == 5){
                header('Location: ' . ANDROID_DOWNLOAD_URL, true, 302);
//                if(!preg_match("/(iphone|ipad)/i", $ua)){
//                    header('Location: ' . WEIXIN_LITTLE_URL, true, 302);
//                }
                exit();
            }
        } catch (PDOException $e) {
            exit();
        }
    }

    header('Location: ' . WEIXIN_URL, true, 302);
}
else if($ua != '' && preg_match("/(iphone|ipad)/i", $ua)){
    header('Location: ' . IOS_DOWNLOAD_URL, true, 302);
}
else {
    header('Location: ' . ANDROID_DOWNLOAD_URL, true, 302);
}
