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
                $_SESSION['uid']       = $data['id'];
                $_SESSION['uName']     = $data['name'];
                jsonBack('succ', 1, array('sessionid'=>session_id()));
            } else {
                jsonBack('账号密码输入错误');
            }
        } else {
            jsonBack('账号密码输入错误');
        }
    }

    function register(){
        $postArr['name'] = $this->pdata['userName'];
        $postArr['pw'] = md5(md5($this->pdata['passWord']).SALT);

        $sql = "select * from user where name = '{$postArr['name']}'";
        $data  = $this->db->fetch($sql);
        if ($data){
            jsonBack('该账户已存在');
        } else {
            if ($this->db->insert($postArr, 'user')) jsonBack('注册成功');
        }
    }
}
