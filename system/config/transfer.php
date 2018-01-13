<?php
// site
$_['site_base'] = substr(HTTP_SERVER, 7);
$_['site_ssl'] = false;

$_['db_autostart'] = true;
$_['db_type'] = DB_DRIVER;
$_['db_hostname'] = DB_HOSTNAME;
$_['db_username'] = DB_USERNAME;
$_['db_password'] = DB_PASSWORD;
$_['db_database'] = DB_DATABASE;
$_['db_port'] = DB_PORT;

$_['action_pre_action'] = array (
    //'startup/router',
    'startup/startup',
    'startup/event'
);

//$_['action_default'] = 'transfer/home';

$_['action_event'] = array(
);