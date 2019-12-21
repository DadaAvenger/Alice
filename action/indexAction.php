<?php
class indexAction{
    public $pdata;
    public $db;
    public function __construct(){
        $this->pdata = $_REQUEST;
        $this->db = DB::getInstance();
    }

    # 登录
    function login(){
         if (isset($_SESSION['uid'])) jsonBack('succ', 1, '已登录');
        $userName = $this->pdata['userName'];
        $passWord = md5(md5($this->pdata['passWord']).SALT);

        $sql = "select * from user where name = '{$userName}'";
        $data  = $this->db->fetch($sql);
        if ($data){
            if ($data['password'] == $passWord){
                $_SESSION['uid']       = $data['id'];
                $_SESSION['uName']     = $data['name'];
                jsonBack('succ', 1, array('sessionid'=>session_id(), 'uid' => $data['id'], 'username' => $data['name']));
            } else {
                jsonBack('账号密码输入错误');
            }
        } else {
            jsonBack('账号密码输入错误');
        }
    }

    # 退出登录
    public function logout() {
//        $this->setLog('success');
        session_destroy();
        unset($_SESSION);
        jsonBack('succ',1);
    }

    # 关闭注册
/*    function register(){
        $postArr['name'] = $this->pdata['userName'];
        $postArr['pw'] = md5(md5($this->pdata['passWord']).SALT);
        $postArr['email'] = $this->pdata['email'];

        $sql = "select * from user where name = '{$postArr['name']}'";
        $data  = $this->db->fetch($sql);
        if ($data){
            jsonBack('该账户已存在');
        } else {
            if ($this->db->save('user', $postArr)) jsonBack('注册成功');
        }
    }*/
}
