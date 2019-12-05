<?php
/**
 * Created by PhpStorm.
 * User: Zero
 * Date: 2019/7/31
 * Time: 11:52
 */
namespace Alice\model;

class CostTypeModel extends \Model
{
    protected $table = "alice.cost_type";


    public function __construct()
    {
        parent::__construct();
//        $this->checkFields();
    }
}