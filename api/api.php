<?php
include("../common/common.php");

$pData = $_POST;
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
    if ($pData['opt'] != 'login' && !isset($_SESSION['uid'])) jsonBack('请重新登录');

    $act->$func();
}