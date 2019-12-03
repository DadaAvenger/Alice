<?php
# 数据检查过滤
function checkData($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $v) {
            $data[$key] = checkData($v);
            if ($key === 'action') {
                $data[$key] = stripcslashes($data[$key]);
            }
        }
    } else {
        $data = getStr($data);
    }
    return $data;
}

# 返回格式统一
function jsonBack($msg = 'failed', $ret = -1, $data = []){
    echo json_encode(['ret' => $ret, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_SLASHES);
    exit;
}

# 记录log
function setLog($content){
	$content = urlencode(json_encode($content));
	file_get_contents("http://120.79.4.18/cool/cache/log.php?return={$content}&name=log");
}

# 用户输入内容过滤函数
function getStr($str)
{
    $tmpStr = trim($str);
    $tmpStr = strip_tags($tmpStr);
    $tmpStr = htmlspecialchars($tmpStr);
    $tmpStr = addslashes($tmpStr);
    return $tmpStr;
}