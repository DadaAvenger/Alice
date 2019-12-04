<?php
require_once '../model/CostModel.php';
class dailyPayAction {
    public $pdata;
    public $db;
    public $table;
    public function __construct(){
        $this->pdata = $_REQUEST;
        $this->db = DB::getInstance();
        $this->table = 'daily_pay';
    }

    # 获取分类
    function getType(){
        $table = 'cost_type';
        $sql = "select * from {$table}";
        $data  = $this->db->find($sql);
        jsonBack('succ', 1, $data);
    }

    # 获取数据
    function getDailyPay(){
        $p = $this->pdata;
        $uid = $_SESSION['uid'];
        $costModel = Alice\model\CostModel::init();

        $startDate = $p['start_time'] ?? date('Y-m-01');
        $endDate = $p['end_time'] ?? date('Y-m-d');
        $where['use_time_str'] = ['between', [$startDate, $endDate]];

        if (!empty($p['type'])){
            $where['type'] = $p['type'];
        }
        if (!empty($p['mark'])){
            $where['mark'] = $p['mark'];
        }
        $where['uid'] = $uid;

        $data = $costModel->getPageData($p, $where);
//        jsonBack($costModel->getLastSql());
        jsonBack('succ', 1, $data);
    }

    # 添加数据
    function addDailyPay(){
        $postArr = array();
        $p = $this->pdata;
        $type = $p['type'];
        $postArr["type"] = $type;
        $postArr['mark'] = $p['mark'];
        $money = $p['money'];
        if (!(is_float($money)||is_numeric($money))) {
            jsonBack("费用有误");
        } else {
            $postArr["money"] = $money;
        }
        $postArr['uid'] = $_SESSION['uid'];
        $postArr['use_time_str'] = date("Y-m-d");
        $postArr['create_time_str'] = date("Y-m-d H:i:s");
        $data  = $this->db->save($this->table, $postArr);
        if ($data) jsonBack('succ', 1, $postArr);
        else jsonBack('插入失败');
    }

    # 获取图表数据
    function getChart(){
        $uid = $_SESSION['uid'];
        if (!empty($this->pdata['date'])) {
            $date = $this->pdata['date'];
            $arr = preg_split("/\//", $date);
            $date = $arr[2] . '-' . $arr[0] . '-' . $arr[1];
            $startDate = date('Y-m-01', strtotime($date));
            $endDate = date('Y-m-d', strtotime(date('Y-m', strtotime($date)) . '-' . date('t', strtotime($date))));
        } else {
            $startDate = date('Y-m-01', strtotime(date("Y-m-d")));
            $endDate = date('Y-m-d');
        }

        $sql = "select addtime,type, sum(money) as money, CAST(date_format(addtime, '%d') AS SIGNED) as day from {$this->table} where uid = {$uid} and addtime between '{$startDate}' and '{$endDate}' group by addtime, type";
        $data  = $this->db->find($sql);
//        $ret = array_column($data, NULL, 'day');
        for ($i = 1; $i <= date('d', strtotime($endDate)); $i++) {
            $ret['category'][] = $i;
        }
        $ret['legend'] = [1 => 'use', 2 => 'net', 3 => 'other', 4 => 'eat', 5 => 'eat2'];

        $color = ['#5e7e54','#e44f2f','#81b6b2','#eba422','#5e7e54',
                               '#e44f2f','#81b6b2','#eba422','#5e7e54','#e44f2f'];
        foreach ($data as $row){
            $barData[$row['type']][$row['day']] = $row['money'];
        }

        foreach ($ret['category'] as $d){
            foreach ($barData as $type => $row) {
                if (!isset($barData[$type][$d])) {
                    $barData[$type][$d] = 0;
                }
            }
        }
        $data = [];
        foreach ($barData as $type => $row){
            $r['name'] = $ret['legend'][$type];
            $r['type'] = 'bar';
            $r['stack'] = '总量';
            $r['label']['normal'] = ['show' => false, 'position' => 'insideRight'];
            $r['itemStyle']['normal'] = ['color' => $color[$type]];
            $r['data'] = array_values($row);
            $data[] = $r;
        }

        $ret['legend'] = array_values($ret['legend']);
        $ret['data'] = $data;

        jsonBack('succ', 1, $ret);
    }
}
