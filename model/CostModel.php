<?php
/**
 * Created by PhpStorm.
 * User: Zero
 * Date: 2019/7/31
 * Time: 11:52
 */
namespace Alice\model;

class CostModel extends \Model
{
    protected $table = "alice.cost";
    protected $fields = [
        'id','date','create_time','uid','type','money','mark'
    ];

    public function __construct()
    {
        parent::__construct();
//        $this->checkFields();
    }
}