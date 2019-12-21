<?php
if(!empty($_REQUEST['sessionid'])){
	session_id($_REQUEST['sessionid']);
}
session_start();
header('Content-type: text/html; charset=UTF-8');
define('ROOT', dirname(__DIR__) . '/');
define('MODEL_DIR', ROOT . 'model/');
define('DB', ROOT . 'db/');
define('PUBLIC_DIR', ROOT . 'publicAction/');
define('ACTION_DIR', ROOT . 'action/');
define('SALT', 'ZhaJi');
include("env.config.php");   //开发环境常量定义

require_once ('DB.config.php');
require_once (PUBLIC_DIR.'publicAction.php');
require_once (DB.'DB.class.php');
require_once (MODEL_DIR.'Model.php');

