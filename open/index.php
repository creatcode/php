<?php
/**
 * 东莞市亦强软件有限公司
 * Author: 罗剑波
 * Time: 2017/2/23 20:31
 */
define('WEIXIN_URL', 'http://bike.e-stronger.com/bike/wechat');
define('ANDROID_DOWNLOAD_URL', 'http://a.app.qq.com/o/simple.jsp?pkgname=cn.estronger.bike');
define('IOS_DOWNLOAD_URL', 'https://itunes.apple.com/cn/app/xiao-qiang-dan-che/id1196263366');
define('WEIXIN_LITTLE_URL', 'http://bike.e-stronger.com/bike/app.php');
error_reporting(0);


/**
 * 获取微信版本号
 * @param $ua
 * @return string
 */
function getMicroMessengerVersion($ua)
{
    $pos = strpos($ua, 'micromessenger');
    $a = substr($ua, $pos);
    $b = strtok($a, ' ');
    return $version = trim(str_replace('micromessenger/', '', $b));
}

/**
 * 比较微信版本
 * @param $version
 * @param $ua
 * @param $success_url
 * @param $fail_url
 */
function versionCompared($version, $ua, $success_url, $fail_url)
{
    //传过来的版本号
    $version_array = explode('.', $version);
    file_put_contents('/dev/shm/logs/app.log', date('Y-m-d H:i:s ') . '微信版本号:' . $version . "\n", FILE_APPEND);
    if (preg_match("/(iphone|ipad)/i", $ua)) {
        $lowest_version_array = [6, 5, 6];
    } else {
        $lowest_version_array = [6, 5, 7];
    }
    //判断版本
    if ($version_array[0] >= $lowest_version_array[0] && $version_array[1] >= $lowest_version_array[1] && $version_array[2] >= $lowest_version_array[2]) {
        header('Location:' . $success_url, true, 302);
    } else {
        header('Location:' . $fail_url, true, 302);
    }
}


/**
 * 判断是否是景区
 * @param $bicycle_sn
 * @return bool
 */
function isScenic($bicycle_sn)
{
    if (is_numeric($bicycle_sn)) {
        try {
            $db = new mysqli("10.29.255.105", "bike", "W8L~UV.0RkZVoBlNJCYr", "bike");
            $sql = 'select is_scenic from rich_bicycle where full_bicycle_sn = ?';
            file_put_contents('/dev/shm/logs/app.log', date('Y-m-d H:i:s ') . $sql . "\n", FILE_APPEND);
            $re = $db->prepare($sql);
            $re->bind_param('i', $bicycle_sn);
            $re->execute();
            $re->bind_result($is_scenic);
            $re->fetch();
            file_put_contents('/dev/shm/logs/app.log', date('Y-m-d H:i:s ') . $bicycle_sn . ':' . $is_scenic . "\n", FILE_APPEND);
            if ($is_scenic) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            exit();
        }
    }
}


$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
file_put_contents('/dev/shm/logs/app.log', 'ua:' . $ua . "\n", FILE_APPEND);
if ($ua != '' && preg_match("/MicroMessenger/i", $ua)) {
    $bicycle_sn = $_GET['b'];
    $is_scenic = isScenic($bicycle_sn);
    $version = getMicroMessengerVersion($ua);
    if ($is_scenic) {
//        等上线后改回来
        // $success_url = 'http://bike.e-stronger.com/bike/wechat/scenicCodeGood.html';
        // $fail_url = 'http://bike.e-stronger.com/bike/wechat/scenicCodeJudge.html';
       $success_url = 'http://a.app.qq.com/o/simple.jsp?pkgname=cn.estronger.bike';
       $fail_url = 'http://a.app.qq.com/o/simple.jsp?pkgname=cn.estronger.bike';
    } else {
        $success_url = 'http://bike.e-stronger.com/bike/wechat/goTips.html';
        $fail_url = ANDROID_DOWNLOAD_URL;
    }
    versionCompared($version, $ua, $success_url, $fail_url);
    exit();
//    header('Location: ' . WEIXIN_URL, true, 302);
} else if ($ua != '' && preg_match("/(iphone|ipad)/i", $ua)) {
    header('Location: ' . IOS_DOWNLOAD_URL, true, 302);
} else {
    header('Location: ' . ANDROID_DOWNLOAD_URL, true, 302);
}
