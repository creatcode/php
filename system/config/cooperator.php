<?php
// Site
$_['site_base']         = substr(HTTP_SERVER, empty($_SERVER['HTTPS']) ? 7:8);
$_['site_ssl']          = !empty($_SERVER['HTTPS']);

// Database
$_['db_autostart']      = true;
$_['db_type']           = DB_DRIVER; // mpdo, mssql, mysql, mysqli or postgre
$_['db_hostname']       = DB_HOSTNAME;
$_['db_username']       = DB_USERNAME;
$_['db_password']       = DB_PASSWORD;
$_['db_database']       = DB_DATABASE;
$_['db_port']           = DB_PORT;

// Actions
$_['action_pre_action']  = array(
	'startup/startup',
	'startup/error',
	'startup/event',
	'startup/login',
	//'startup/permission'
	'startup/base'
);

// Actions
//$_['action_default']     = 'dashboard/dashboard';
$_['action_default'] = 'bicycle/bicycle';
// Action Events
$_['action_event'] = array(
//	'model/*/before' => 'event/debug/before',
//	'controller/*/before' => 'event/debug/before'
);
