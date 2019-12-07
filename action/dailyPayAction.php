<?php
class dailyPayAction {
    public $pdata;
    public $db;
    public $table;
    public $costModel;
    public function __construct(){
        $this->pdata = $_REQUEST;
        $this->db = DB::getInstance();
        $this->table = 'daily_pay';
        $this->costModel = \Alice\model\CostModel::init();
    }

    # 获取分类
    function getType(){
        $typeModel = \Alice\model\CostTypeModel::init();
        $data  = $typeModel->lists();
        jsonBack('succ', 1, $data);
    }

    # 获取数据
    function getDailyPay(){
        $p = $this->pdata;
        $typeModel = \Alice\model\CostTypeModel::init();
        $typeData  = $typeModel->lists(['key' => 'id']);

        $startDate = $p['start_time'] ?? date('Y-m-01');
        $endDate = $p['end_time'] ?? date('Y-m-d');
        $where['date'] = ['between', [$startDate, $endDate]];

        if (!empty($p['type'])){
            $where['type'] = $p['type'];
        }
        if (!empty($p['mark'])){
            $where['mark'] = $p['mark'];
        }
        if (!empty($p['start_money']) && !empty($p['end_money'])){
            $where['money'] = ['between', [$p['start_money'], $p['end_money']]];
        }
        $where['uid'] = getAccount();

        $data = $this->costModel->getPageData($p, $where);
        foreach ($data['list'] as &$r){
            $r['type_name'] = $typeData[$r['type']]['name'];
        }
        jsonBack('succ', 1, $data);
    }

    # 添加数据
    function addDailyPay(){
        $p = $this->pdata;
        $money = $p['money'];
        if (!(is_float($money)||is_numeric($money))) {
            jsonBack("费用有误");
        }
        $save['type'] = $p['type'];
        $save['mark'] = $p['mark'];
        $save['money'] = $money;
        $save['uid'] = getAccount();
        $save['date'] = $p['date'] ?? date("Y-m-d");
        $save['create_time'] = $p['date'] ? date("Y-m-d H:i:s", strtotime($p['date'])) : date("Y-m-d H:i:s");

        if ($this->costModel->create($save)) {
            jsonBack('succ', 1, $save);
        } else {
//            jsonBack($this->costModel->getLastSql());
            jsonBack('插入失败');
        }
    }

    # 获取图表数据
    function getChart(){
    }
}
