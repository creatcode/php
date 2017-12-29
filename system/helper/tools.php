<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2016/7/14
 * Time: 10:40
 * 常用函数
 */
/**
 * 是否是手机号码
 *
 * @param string $phone	手机号码
 * @return boolean
 */
function isPhone($phone) {
    if (strlen ( $phone ) != 11 || ! preg_match ( '/^1[3|4|5|7|8][0-9]\d{4,8}$/', $phone )) {
        return false;
    } else {
        return true;
    }
}
/**
 * 验证字符串是否为数字,字母,中文和下划线构成
 * @param string $username
 * @return bool
 */
function isCheckString($str) {
    if(preg_match('/^[\x{4e00}-\x{9fa5}\w_]+$/u',$str)){
        return true;
    }else{
        return false;
    }
}
/**
 * 是否为一个合法的email
 * @param sting $email
 * @return boolean
 */
function isEmail($email) {
    if (filter_var ($email, FILTER_VALIDATE_EMAIL )) {
        return true;
    } else {
        return false;
    }
}
/**
 * 是否为一个合法的url
 * @param string $url
 * @return boolean
 */
function isUrl($url) {
    if (filter_var ($url, FILTER_VALIDATE_URL )) {
        return true;
    } else {
        return false;
    }
}
/**
 * 是否为一个合法的ip地址
 * @param string $ip
 * @return boolean
 */
function isIp($ip) {
    if (ip2long($ip)) {
        return true;
    } else {
        return false;
    }
}
/**
 * 是否为整数
 * @param int $number
 * @return boolean
 */
function isNumber($number) {
    if(preg_match('/^[-\+]?\d+$/',$number)){
        return true;
    }else{
        return false;
    }
}
/**
 * 是否为正整数
 * @param int $number
 * @return boolean
 */
function isPositiveNumber($number) {
    if(ctype_digit ($number)){
        return true;
    }else{
        return false;
    }
}
/**
 * 是否为小数
 * @param float $number
 * @return boolean
 */
function isDecimal($number) {
    if(preg_match('/^[-\+]?\d+(\.\d+)?$/',$number)){
        return true;
    }else{
        return false;
    }
}
/**
 * 是否为正小数
 * @param float $number
 * @return boolean
 */
function isPositiveDecimal($number) {
    if(preg_match('/^\d+(\.\d+)?$/',$number)){
        return true;
    }else{
        return false;
    }
}
/**
 * 是否为英文
 * @param string $str
 * @return boolean
 */
function isEnglish($str) {
    if(ctype_alpha($str))
        return true;
    else
        return false;
}
/**
 * 是否为中文
 * @param string $str
 * @return boolean
 */
function isChinese($str) {
    if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$str))
        return true;
    else
        return false;
}
/**
 * 是否为合法的身份证(支持15位和18位)
 * @param string $card
 * @return boolean
 */
function isIDCard($card) {
    if (preg_match('/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/', $card) || preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/', $card)) {
        return true;
    } else {
        return false;
    }
}
/**
 * 验证日期格式是否正确
 * @param string $date
 * @param string $format
 * @return boolean
 */
function isDate($date,$format='Y-m-d') {
    $rec = date_parse_from_format($format,$date);
    if(empty($rec['errors'])){
        return true;
    } else {
        return false;
    }
}

function string2bytes($src) {
    preg_match('/(\d*)(\S*)/i', $src, $matches);
    $multiple = 1;
    switch($matches[2]) {
        case 'KB':
        case 'K': $multiple = 1024;break;
        case 'MB':
        case 'M': $multiple = pow(1024, 2);break;
        case 'GB':
        case 'G': $multiple = pow(1024, 3);break;
        case 'TB':
        case 'T': $multiple = pow(1024, 4);break;
    }
    return $multiple * $matches[1];
}