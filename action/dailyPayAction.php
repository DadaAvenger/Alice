<?php
class dailyPayAction {
    public $pdata;
    public $db;
    public $costModel;
    public function __construct(){
        $this->pdata = $_REQUEST;
        $this->db = DB::getInstance();
        $this->costModel = \Alice\model\CostModel::init();
    }

    # 获取用户已添加分类
    function getAccountType(){
        $accountType = $this->costModel->lists(['where' => ['uid' => getAccount()], 'field' => 'count(type) as type_count, type',
            'key' => 'type', 'orderBy' => 'type_count desc', 'groupBy' => 'type']);

        $typeModel = \Alice\model\CostTypeModel::init();
        $data  = $typeModel->lists();
        $data = array_column($data, NULL, 'id');

        foreach ($accountType as $r){
//            jsonBack($data[$r['type']]);
            $row = ['id' => $r['type'], 'name' => $data[$r['type']]['name']];
            $ret[] = $row;
        }

        jsonBack('succ', 1, $ret);
    }

    # 获取分类
    function getType(){
        $typeModel = \Alice\model\CostTypeModel::init();
        $data  = $typeModel->lists();
        jsonBack('succ', 1, $data);
    }

    # 添加分类
    function addType(){
        $typeModel = \Alice\model\CostTypeModel::init();
        if (empty($this->pdata['name'])) jsonBack('请输入类型名称');
        $data  = $typeModel->create(['name' => $this->pdata['name']]);
        jsonBack('succ', 1, $data);
    }

    # 修改分类
    function editType(){
        $typeModel = \Alice\model\CostTypeModel::init();
        if (empty($this->pdata['id'])) jsonBack('请输入类型id');
        if (empty($this->pdata['name'])) jsonBack('请输入修改类型名称');
        $data  = $typeModel->update(['name' => $this->pdata['name']], ['id' => $this->pdata['id']]);
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
        if (!(is_float($money) || is_numeric($money))) {
            jsonBack("费用有误");
        }
        $save['type'] = $p['type'];
        $save['mark'] = $p['mark'];
        $save['money'] = $money;
        $save['uid'] = getAccount();
        $save['date'] = $p['date'] ?? date("Y-m-d");
        $save['create_time'] = $p['date'] ? date("Y-m-d H:i:s", strtotime($p['date'])) : date("Y-m-d H:i:s");

        if ($this->costModel->update($save, ['id' => $p['id']])) {
            $this->autoUpdateBalance();
            jsonBack('succ', 1, $save);
        } else {
//            jsonBack($this->costModel->getLastSql());
            jsonBack('更新失败');
        }
    }

    # 修改
    function editDailyPay(){
        $p = $this->pdata;
        $money = $p['money'];
        if (empty($p['id'])) jsonBack('缺少行id');
        if (!empty($money) && !(is_float($money) || is_numeric($money))) {
            jsonBack("费用有误");
        }
        if (!empty($p['type'])) $save['type'] = $p['type'];
        if (!empty($p['mark'])) $save['mark'] = $p['mark'];
        if (!empty($p['money'])) $save['money'] = $p['money'];
        if (!empty($p['type'])) $save['type'] = $p['type'];
        if (!empty($p['date'])) $save['date'] = $p['date'];
        if (!empty($p['create_time'])) $save['create_time'] = $p['create_time'];

        if ($this->costModel->update($save, ['id' => $p['id'], 'uid' => getAccount()])) {
            if (!empty($p['money'])) $this->autoUpdateBalance();
            jsonBack('succ', 1, $save);
        } else {
//            jsonBack($this->costModel->getLastSql());
            jsonBack('插入失败');
        }
    }

    # 删除
    function deleteDailyPay(){
        $p = $this->pdata;
        if (empty($p['id'])) jsonBack('缺少行id');

        if ($this->costModel->delete(['id' => $p['id'], 'uid' => getAccount()])) {
            $this->autoUpdateBalance();
            jsonBack('succ', 1, '删除成功');
        } else {
//            jsonBack($this->costModel->getLastSql());
            jsonBack('删除失败');
        }
    }

    # 获取饼图图表数据
    function getPieChart(){
        $p = $this->pdata;
        $totalData = [];
        $typeModel = \Alice\model\CostTypeModel::init();
        $typeData  = $typeModel->lists(['key' => 'id']);

        $pieType = $p['pie_type'] ?? 1;
        $startDate = $p['start_time'] ?? date('Y-m-01');
        $endDate = $p['end_time'] ?? date('Y-m-d');
        $where['date'] = ['between', [$startDate, $endDate]];

        if (!empty($p['type'])){
            $where['type'] = $p['type'];
        }

        $where['uid'] = getAccount();

        $data = $this->costModel->getPageData($p, $where);
        foreach ($data['list'] as $r){
            if ($pieType == 1){
                # 单饼图，汇总
                $totalData[$r['type']]['money'] += $r['money'];
                if (!isset($totalData[$r['type']]['type_name'])) $totalData[$r['type']]['type_name'] = $typeData[$r['type']]['name'];
            } else {
                # 多饼图，按月份区分
                $month = date('m', strtotime($r['date']));
                $totalData[$month][$r['type']] += $r['money'];
                if (!isset($totalData[$month][$r['type']]['type_name'])) $totalData[$month][$r['type']]['type_name'] = $typeData[$r['type']]['name'];
            }
        }
        jsonBack('succ', 1, $totalData);
    }

    # 自动计算余额
    public function autoUpdateBalance(){
        $costBudgetModel = \Alice\model\CostBudgetModel::init();

        $where['uid'] = $whereBudget['uid'] = getAccount();
        $where['date'] = ['between', [date('Y-m-01'), date('Y-m-d')]];
        $costData = $this->costModel->lists(['where' => $where, 'field' => 'sum(money) as money_total']);

        $whereBudget['month'] = date('Y-m');
        $budgetData = $this->costModel->lists(['where' => $where, 'field' => 'id, budget']);

        if (empty($budgetData)){
            # 获取默认预算
            $userModel = \Alice\model\UserModel::init();
            $defaultBudget = $userModel->get(['id' => getAccount()], 'budget');
            # 计算当前余额
            $balance = $defaultBudget['budget'] - current($costData)['money_total'];

            $save['uid'] = getAccount();
            $save['budget'] = $defaultBudget['budget'];
            $save['balance'] = $balance;
            $save['create_time'] = date('Y-m-d H:i:s');
            $save['month'] = date('Y-m');
            $costBudgetModel->create($save);
        } else {
            $balance = $budgetData['budget'] - current($costData)['money_total'];
            $costBudgetModel->update(['balance' => $balance], ['id' => $budgetData['id']]);
        }
    }
}
