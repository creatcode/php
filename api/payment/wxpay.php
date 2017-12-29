<?php
$post = file_get_contents("php://input");
date_default_timezone_set('PRC');
file_put_contents('/data/wwwroot/default/bike/wxpay.log', date('Y-m-d H:i:s ') . $post . "\n", FILE_APPEND);
$_GET['route'] = 'payment/wxpay/notify';
require '../index.php';
