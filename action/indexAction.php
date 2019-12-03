<?php
class indexAction{
    public $pdata;
    public $db;
    public function __construct(){
        $this->pdata = $_REQUEST;
        $this->db = new Db;
    }

    function login(){
         if (isset($_SESSION['uid'])) jsonBack(array(1));
        $userName = $this->pdata['userName'];
        $passWord = md5(md5($this->pdata['passWord']).SALT);

        $sql = "select * from user where name = '{$userName}'";
        $data  = $this->db->fetch($sql);
        if ($data){
            if ($data['pw'] == $passWord){
                $_SESSION['uid']       = $data['uid'];
                $_SESSION['uName']     = $data['username'];
                jsonBack('succ', 1, array('sessionid'=>session_id()));
            } else {
                jsonBack('输入有误');
            }
        } else {
            jsonBack('输入有误');
        }
    }
}
