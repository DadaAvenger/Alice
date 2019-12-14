<?php
/**
 * Created by PhpStorm.
 * User: Zero
 * Date: 2019/7/31
 * Time: 11:52
 */
namespace Alice\model;

class CostBudgetModel extends \Model
{
    protected $table = "alice.cost_budget";
    protected $fields = [
        'id','month','uid','budget','balance','create_time'
    ];

    public function __construct()
    {
        parent::__construct();
//        $this->checkFields();
    }
}