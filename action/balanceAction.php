<?php
class balanceAction {
    public $pdata;
    public $db;
    public $table;
    public function __construct(){
        $this->pdata = $_REQUEST;
        $this->db = DB::getInstance();
        $this->costBudgetModel = \Alice\model\CostBudgetModel::init();
    }


    # 获取预算余额
    function getBalanceByMonth(){
        $p = $this->pdata;

        $startDate = !empty($p['start_time']) ? $p['start_time'] : date('Y-m');
        $endDate = !empty($p['end_time']) ? $p['end_time'] : date('Y-m');
        $where['month'] = ['between', [$startDate, $endDate]];

        $where['uid'] = getAccount();

        $data = $this->costBudgetModel->getPageData($p, $where);
        if (!$data['list']){
            include 'dailyPayAction.php';
            $dailyAction = new dailyPayAction();
            $dailyAction->autoUpdateBalance();
            $data = $this->costBudgetModel->getPageData($p, $where);
//            jsonBack($this->costBudgetModel->getLastSql());
        }


        jsonBack('succ', 1, $data);
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
