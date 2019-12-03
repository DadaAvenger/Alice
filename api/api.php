<?php
include("../common/common.php");
error_reporting(0);
if(isset($_REQUEST['log']) && $_REQUEST['log'] == 1){
    ini_set("display_errors", "On");
    error_reporting(E_ALL^E_NOTICE^E_DEPRECATED);
}

header('Content-type:application/json;charset=utf8');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Access-Control-Allow-Methods:POST');
$pData = checkData($_REQUEST);

$actionModule = $pData['action'] != '' ? $pData['action'].'Action' : '';
// setLog($_REQUEST);
if ($actionModule){
    $actionFile = '../action/'.$actionModule.'.php';
    if (!file_exists($actionFile)){
        jsonBack($actionModule . '类文件不存在');
    }
    include($actionFile);
    $act  = new $actionModule;

    $func = empty($pData['opt']) ? 'index' : $pData['opt'];
    if (!method_exists($act, $func)) {
        jsonBack($pData['action'] . '->' . $func . '方法不存在');
    }
    if ($pData['opt'] != 'register') $act->$func();
    if ($pData['opt'] != 'login'  &&  !isset($_SESSION['uid'])) jsonBack('请重新登录');
        $act->$func();
    
}
