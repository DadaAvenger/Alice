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
        $startDate = $p['start_time'] ?? date('Y-m-01');
        $endDate = $p['end_time'] ?? date('Y-m-d');
        $where['date'] = ['between', [$startDate, $endDate]];

        if (!empty($p['type'])){
            $where['type'] = $p['type'];
        }
        if (!empty($p['mark'])){
            $where['mark'] = $p['mark'];
        }
        $where['uid'] = getAccount();

        $data = $this->costModel->getPageData($p, $where);
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
        $save['date'] = date("Y-m-d");
        $save['create_time'] = date("Y-m-d H:i:s");

        if ($this->costModel->create($save)) {
            jsonBack('succ', 1, $save);
        } else {
            jsonBack($this->costModel->getLastSql());
            jsonBack('插入失败');
        }
    }

    # 获取图表数据
    function getChart(){
    }
}
