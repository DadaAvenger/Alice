<?php
	// $config = file_get_contents('../config/config.json');
	$str = file_get_contents('../config/config.json'); 
	// var_dump($str);
	$config = json_decode($str, true);
	// echo $config["url"];
	echo $config["DB_HOST"];
	echo "<br/>";
	echo $config["DB_USERNAME"];
	echo "<br/>";
	echo $config["DB_PASSWORD"];
	echo "<br/>";
	echo $config["DB_DATABASE"];
	echo "<br/>";

    // $conn = new mysqli("localhost", "root", "84966908", "zero");
    $conn = new mysqli($config["DB_HOST"], $config["DB_USERNAME"], $config["DB_PASSWORD"], $config["DB_DATABASE"]);
    //服务器
    // $this->conn = new mysqli("120.79.4.18", "root", "84966908", "zero");

    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    } else {
    	echo "yes";
    }
?>