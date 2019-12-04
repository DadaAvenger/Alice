<?php

/**
 * 数据库连接配置
 */
class DBConfig
{
    static public $defaultDb = 'dada';

    # 本地测试
    static public $test = array(
        'index'     => 'test',
        'type'      => 'mysql',
        'host'      => 'localhost',
        'port'      => '3306',
        'user'      => 'root',
        'password'  => '84966908',
        'dbname'    => 'zha_ji',
        'table_pre' => '',
        'charset'   => 'utf8'
    );

    # 线上
    static public $dada = array(
        'index'     => 'dada',
        'type'      => 'mysql',
        'host'      => '120.79.4.18',
        'port'      => '3306',
        'user'      => 'root',
        'password'  => '84966908',
        'dbname'    => 'alice',
        'table_pre' => '',
        'charset'   => 'utf8'
    );
}
