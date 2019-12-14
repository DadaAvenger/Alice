<?php
/**
 * Created by PhpStorm.
 * User: Zero
 * Date: 2019/7/31
 * Time: 11:52
 */
namespace Alice\model;

class UserModel extends \Model
{
    protected $table = "alice.user";
    protected $fields = [
        'id','name','password','day_set','type','create_time', 'img', 'word', 'budget'
    ];

    public function __construct()
    {
        parent::__construct();
//        $this->checkFields();
    }
}