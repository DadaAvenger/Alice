<?php
	$content = $_GET['return'];
	$fileName = $_GET['name'];
	$name = __DIR__."/{$fileName}";
	$content = json_decode(urldecode($content),true);
	// $content["logReqTime"] = date("Y-m-d H:i:s");
	// if (!file_exists($name)){
	// 	var_dump("touch {$fileName}");
	// 	system("touch {$fileName}");
	// }
	// var_dump($content);
	file_put_contents($name,$content,FILE_APPEND  |  LOCK_EX);
	file_put_contents($name,"\n",FILE_APPEND  |  LOCK_EX);