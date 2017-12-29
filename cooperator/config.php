<?php
//HTTP
define('HTTP_SERVER', 'http://test.bike.com/cooperator');
define('HTTP_CATALOG', 'http://test.bike.com/static/');
define('HTTPS_SERVER', 'http://test.bike.com/cooperator');
define('HTTPS_CATALOG', 'http://test.bike.com/static/');
if(!empty($_SERVER['HTTPS'])){
    define('HTTP_IMAGE', HTTPS_CATALOG);
}
else {
    define('HTTP_IMAGE', HTTP_CATALOG);
}

//DIR
define('DIR_BASE', dirname(dirname(__FILE__)));
define('DIR_APPLICATION', DIR_BASE . '/cooperator/');
define('DIR_SYSTEM', DIR_BASE . '/system/');
define('DIR_STATIC', DIR_BASE . '/static/');
define('DIR_TEMPLATE', DIR_BASE . '/cooperator/view/template/');
define('DIR_LANGUAGE', DIR_BASE . '/cooperator/language/');
define('DIR_CONFIG', DIR_BASE . '/system/config/');
define('DIR_MODIFICATION', DIR_BASE . '/system/storage/modification/');
define('DIR_CACHE', DIR_BASE . '/system/storage/cache/');
define('DIR_DOWNLOAD', DIR_BASE . '/system/storage/download/');
define('DIR_LOGS', DIR_BASE . '/system/storage/logs/');
define('DIR_UPLOAD', DIR_BASE . '/system/storage/upload/');
define('WX_SSL_CONF_PATH', DIR_SYSTEM . 'library/payment/cert/');

//DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_PORT', '3306');
define('DB_DATABASE', 'bike');
define('DB_PREFIX', 'rich_');

//CACHE
define('CACHE_HOSTNAME', '120.76.98.150');
define('CACHE_PORT', '11211');
define('CACHE_PREFIX', 'roachBike');
define('QUEUE_OPEN', true);

//Redis
define('REDIS_HOST', '120.76.98.150');
define('REDIS_PORT', '6379');

define('TIMESTAMP', time());

define('SMS_TIMEOUT', 60 * 3);//短信失效时间，单位是秒
define('USER_ID', '2016121288yiqiang');
define('USER_KEY', 'yiqiang');
define('MIN_RECHARGE', '1'); //最小的充值金额
define('MAX_RECHARGE', '100');//最大的充值金额
define('GAP_TIME', '120');//回传时间120秒
define('API_URL', 'http://47.90.39.93:8888?version=1');
define('NEW_API_URL', 'http://gps.dola520.com:8888?version=1');
define('OPEN_VALIDATE', false);

define('BOOK_EFFECT_TIME', 15 * 60);

define('OFFLINE_THRESHOLD', 3610); //后台认为单车失联（离线）的判断标准（单位秒）
define('LONG_TIME_OFFLINE_THRESHOLD', 86410); //后台认为单车长时间失联（离线）的判断标准（单位秒）

define('TIME_CHARGE_UNIT', 30 * 60);//计费单位/s
define('PRICE_UNIT', 1);//价格单元
