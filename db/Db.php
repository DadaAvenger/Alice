<?php
class Db{
    protected $conn;
    public function  __construct(){
//        $str = file_get_contents('../config/config.json');
//        $config = json_decode($str, true);
//        $this->conn = new mysqli($config["DB_HOST"], $config["DB_USERNAME"], $config["DB_PASSWORD"], $config["DB_DATABASE"]);

        // $this->conn = new mysqli("localhost", "root", "root", "zero");
        $this->conn = new mysqli("120.79.4.18", "root", "84966908", "zero");
        $this->conn ->query("SET NAMES utf8");

        if ($this->conn->connect_error) {
            die("连接失败: " . $this->conn->connect_error);
        }
    }

    public function find($sql){
        $data = $this->conn->query($sql);
        $result = [];
        if ($data){
            foreach ($data as $k => $v){
                $result[$k] = $v;
            }
        } 
        return $result;
    }

    public function insert($data, $table){
        $sql = "INSERT INTO {$table} SET ";
        foreach ($data as $k => $v){
            $sql .= $k ." = '" .$v. "',"; 
        }
        $sqls = substr($sql, 0, -1);
        $data = $this->conn->query($sqls);
        if ($data) return true;
        else return $sqls;
    }

    public function update($data, $table, $field){
        $sql = "UPDATE {$table} SET ";
        foreach ($data as $k => $v){
            $sql .= $k ." = '" .$v. "',"; 
        }
        $id = $data[$field];
        $sqls = substr($sql, 0, -1);
        $sqls .= " where {$field} = {$id}";
        $data = $this->conn->query($sqls);
        if ($data) return true;
        else return $sqls;
    }

    public function fetch($sql){
        $data = $this->conn->query($sql);
        if ($data->num_rows){
            foreach ($data as $k => $v){
                $result[$k] = $v;
            }
            return current($result);
        } 
        return '';
    }

    public function query($sql){
        $data = $this->conn->query($sql);
        return $data;
    }
}

