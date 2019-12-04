<?php
if(!empty($_REQUEST['sessionid'])){
	session_id($_REQUEST['sessionid']);
}
session_start();
header('Content-type: text/html; charset=UTF-8');
require_once ('../publicAction/publicAction.php');
require_once ('../db/DB.class.php');
require_once ('../model/Model.php');
define('SALT', 'ZhaJi');

