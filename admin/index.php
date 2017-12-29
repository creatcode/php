<?php
/**
 * 打印500错误
 */
// error_reporting(E_ALL); //E_ALL
// function cache_shutdown_error() {
//     $_error = error_get_last();
//     if ($_error && in_array($_error['type'], array(1, 4, 16, 64, 256, 4096, E_ALL))) {
//         echo '<font color=red>你的代码出错了：</font></br>';
//         echo '致命错误:' . $_error['message'] . '</br>';
//         echo '文件:' . $_error['file'] . '</br>';
//         echo '在第' . $_error['line'] . '行</br>';
//     }
// }
// register_shutdown_function("cache_shutdown_error");

require_once 'config.php';

require_once DIR_SYSTEM . 'startup.php';

$application_config = 'admin';

require_once DIR_SYSTEM . 'framework.php';