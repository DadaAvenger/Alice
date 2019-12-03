<?php
if(!empty($_REQUEST['sessionid'])){ 
	session_id($_REQUEST['sessionid']);
}
session_start();
header('Content-type: text/html; charset=UTF-8');
require_once ('../db/Db.php');
require_once ('../publicAction/publicAction.php');
define('SALT', 'ZhaJi');

