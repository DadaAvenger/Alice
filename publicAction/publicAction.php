<?php
function jsonBack($msg = 'failed', $ret = -1, $data = []){
    echo json_encode(['ret' => $ret, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_SLASHES);
    exit;
}

function setLog($content){
	$content = urlencode(json_encode($content));
	file_get_contents("http://120.79.4.18/cool/cache/log.php?return={$content}&name=log");
}
