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

# 返回格式统一2
function jsonBackT($msg = 'failed', $ret = -1, $data = []){
    if (DEBUG_DEV) {
        echo json_encode(['rows' => $data['list'], 'total' => $data['total_number']], JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode(['ret' => $ret, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_SLASHES);
    }
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

# 自动加载
spl_autoload_register(function ($className) {
    // 加载模型类
/*    if (substr($className, -5) == 'Model') {
        $actionPath = MODEL_DIR . $className . '.php';
        if (file_exists($actionPath)) {
            require_once "{$actionPath}";
            return true;
        }
    }*/

    # 特殊加载自定义类
    $prefix   = 'Alice\\';
    $base_dir = ROOT;
    // 判断命名空间前缀
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) === 0) {
        // get the relative class name
        $relative_class = substr($className, $len);
        $file           = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        // if the file exists, require it
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
},true);

function getAccount(){
    if (DEBUG_DEV)return 2;
    if (empty($_SESSION['uid'])) jsonBack('账号过期，请重新登录');
    return $_SESSION['uid'];
}