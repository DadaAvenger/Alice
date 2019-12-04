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
    protected $table = "zhaji_ali.cost";
//    protected $fields = [
//        'id','account_id','create_time','content_title','object_type','object_id','object_name','content_log','operator','opt_ip'
//    ];

    public function __construct()
    {
        parent::__construct();
//        $this->checkFields();
    }
}