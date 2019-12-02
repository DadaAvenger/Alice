<?php
class balanceAction {
    public $pdata;
    public $db;
    public $table;
    public function __construct(){
        $this->pdata = $_REQUEST;
        $this->db = new Db;
        $this->table = 'users';
    }

    function getBalance(){
        $uid = $_SESSION['uid'];
        $sql = "select balance, month_balance, month_budget from {$this->table} where uid = {$uid}";
        $total  = current($this->db->find($sql));

        $startDate = $this->pdata['startDate'] ?? date('Y-m-01', strtotime(date("Y-m-d")));
        $endDate = $this->pdata['endDate'] ?? date('Y-m-d');
        $sql = "select budget,money from daily_pay where uid = {$uid} and addtime between '{$startDate}' and '{$endDate}'order by id desc";
        $data = $this->db->find($sql);
        // var_dump($sql);
        $total['month_balance'] = $total['month_budget'];
        foreach ($data as $r){
            if ($r['budget']) $total['month_balance'] += $r['money'];
            else $total['month_balance'] -= $r['money'];
        }
        $total['month_balance'] = sprintf("%.2f",$total['month_balance']);
        $total['rest_day'] = $this->getRestDay();

        jsonBack('succ', 1, $total);
    }

    function editBalance(){
        $postArr['uid'] = $_SESSION['uid'];
        $postArr['month_budget'] = $this->pdata['month_budget'];
        $data = $this->db->update($postArr, $this->table, 'uid');
        if ($data) jsonBack('succ', 1, $postArr);
        else jsonBack('更新失败');
    }

    function getRestDay(){
        $day = date("d");
        if ($day > 15){
            $getDay = strtotime(date('Y-m-15', strtotime("+1 months",strtotime(date("Y-m-d")))));
        } else {
            $getDay = strtotime(date('Y-m-15', strtotime(date("Y-m-d"))));
        }
        $today = strtotime(date("Y-m-d"));    
        $rest = ($getDay-$today)/86400;
        return $rest;
    }
}
