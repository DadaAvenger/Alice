<?php
/**
 * PDO 数据库操作类
 */

class DB
{
    /** @var PDO pdo连接实例 */
    private $_pdo = null;

    /** @var string 配置标识*/
    private $_str = null;

    /** @var array(DB) 实例化对象 */
    static private $_instance = array();

    /** @var PDOStatement 结果集 */
    private $_sth = null;

    /** @var string 最后执行的SQL */
    public $lastSQL             = '';
    public $lase_pdo_parameters = array();

    /** @var bool|int 调试模式 */
    private $debugLevel = 2;

    /** @var bool 最后一个错误信息 */
    private $errorInfo = false;

    /** @var bool 所有错误信息 */
    private $errorInfoArray = false;
    // 插入忽略
    private $_ignore = false;
    // 插入替换
    private $_replace = false;
    // 插入更新
    private $_duplicate = false;

    /** @var array 数据库表达式 */
    private $comparison = array(
        'eq'      => '=',
        'neq'     => '!=',
        'gt'      => '>',
        'egt'     => '>=',
        'lt'      => '<',
        'elt'     => '<=',
        'notlike' => 'NOT LIKE',
        'like'    => 'LIKE'
    );

    // 连贯操作数据
    private $_data     = array();
    private $_table    = '';
    private $_alias    = '';
    private $_join     = '';
    private $_where    = '';
    private $_field    = '';
    private $_order_by = '';
    private $_group_by = '';
    private $_limit    = '';
    private $_having   = '';
    private $_showSql  = false;
    // 连贯操作 - 配置
    private $_fetch_type = \PDO::FETCH_ASSOC;
    // 连贯操作 - 预处理 $pdo_parameters
    private $pdo_parameters = array();

    /**
     * 获取DB实例
     * @param bool $str
     * @param bool $reConnect 重新连接
     * @return DB
     */
    static public function getInstance($str = false, $reConnect = false)
    {
        if (!$str) $str = DBConfig::$defaultDb;

        if (!array_key_exists($str, self::$_instance) || !self::$_instance[ $str ] instanceof self) {
            self::$_instance[ $str ] = new self($str);
        }

        /** @var self $pdo */
        $pdo = self::$_instance[ $str ];
        if (!$pdo->pdo_ping() || $reConnect) {
            self::$_instance[ $str ] = new self($str);
        }

        return self::$_instance[ $str ];
    }

    static public function unsetInstance($str = false)
    {
        if (!$str) $str = DBConfig::$defaultDb;

        if (array_key_exists($str, self::$_instance)) {
            self::$_instance[ $str ] = null;
        }
    }

    // 私有克隆
    private function __clone() { }

    // 私有构造
    private function __construct($str = false)
    {
        try {
            $this->connect($str);
        } catch (PDOException $e) {
            //sendMsg(DEVELOPER_MOBILE,$e->getMessage());
            echo json_encode(['ret'=>1,'msg'=>"{$str} error :".$e->getMessage(),'data'=>[]]);
            exit();
            //exit("{$str} error :".$e->getMessage());
        }
    }

    /**
     * 直接执行sql
     * @param $sql
     * @return array
     */
    public function Q($sql)
    {
        $this->_sth = $this->_pdo->query($sql);

        $result = [];
        while ($row =$this->_sth->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * 预处理模式- 受影响的行数
     * @param $sql
     * @param array $parameters
     * @return int|mixed|string
     * @throws MySQLException
     */
    public function query($sql, $parameters = array())
    {
        $this->lastSQL = $sql;

        if ($this->_showSql) return $this->getLastSql();

        $this->watchException($sql,$parameters);
        $ret = $this->errorInfo['code'] == 0 ? true : false;

        return $this->_sth->rowCount()?:$ret;
    }

    /**
     * 预处理模式- 所有结果
     * @param $sql
     * @param array $parameters
     * @param bool $key
     * @return array
     * @throws MySQLException
     */
    public function fetchAll($sql, $parameters = array(), $key = false)
    {
        $result        = array();
        $this->lastSQL = $sql;
        $this->watchException($sql,$parameters);
        if ($key) {
            $result = [];
            while ($tmpResult = $this->_sth->fetch(\PDO::FETCH_ASSOC)) {
                $result[ $tmpResult[ $key ] ] = $tmpResult;
            }
        } else {
            while ($row = $this->_sth->fetch(\PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * 预处理模式- 所有结果(兼容)
     * @param $sql
     * @param bool $key
     * @param array $parameters
     * @return array
     * @throws MySQLException
     */
    public function find($sql, $key = false, $parameters = array())
    {
        return $this->fetchAll($sql, $parameters, $key);
    }

    /**
     * 预处理模式- 获取指定列所有数据
     * @param $sql
     * @param array $parameters
     * @param int $position
     * @return array
     * @throws MySQLException
     */
    public function fetchColumnAll($sql, $parameters = array(), $position = 0)
    {
        $result        = array();
        $this->lastSQL = $sql;
        $this->watchException($sql,$parameters);
        while ($row = $this->_sth->fetchColumn($position)) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * 预处理模式- 是否存在
     * @param $sql
     * @param array $parameters
     * @return bool
     * @throws MySQLException
     */
    public function exists($sql, $parameters = array())
    {
        $this->lastSQL = $sql;
        $data          = $this->fetch($sql, $parameters);

        return !empty($data);
    }

    /**
     * 预处理模式- 单条数据
     * @param $sql
     * @param array $parameters
     * @param int $type
     * @return mixed
     * @throws MySQLException
     */
    public function fetch($sql, $parameters = array(), $type = \PDO::FETCH_ASSOC)
    {
        $this->lastSQL = $sql;
        $this->watchException($sql,$parameters);

        return $this->_sth->fetch($type);
    }

    /**
     * 预处理模式- 指定列单条数据
     * @param $sql
     * @param array $parameters
     * @param int $position
     * @return mixed
     * @throws MySQLException
     */
    public function fetchColumn($sql, $parameters = array(), $position = 0)
    {
        $this->lastSQL = $sql;
        $this->watchException($sql,$parameters);

        return $this->_sth->fetchColumn($position);
    }

    /**
     * 计算数量 COUNT
     * @param bool $sql
     * @param array $parameters
     * @param string $field
     * @return mixed
     * @throws MySQLException
     */
    public function count($sql = false, $parameters = [], $field="*",$in=false)
    {
        if (!$sql) {
            // 连贯操作
            $table      = $this->_parseTable();
            if($in){
                $sql        = "select count(*) from (SELECT count({$field}) FROM " . $table . $this->_parseCondition().") a";
            }else{
                $sql        = 'SELECT count('.$field.') FROM ' . $table . $this->_parseCondition();
            }

            $parameters = $this->pdo_parameters;
        } else {
            $this->pdo_parameters = $parameters;

        }

        $this->clearOption();
        $this->lastSQL = $sql;

        $this->watchException($sql,$parameters);

        return $this->_sth->fetchColumn(0);


    }

    /**
     * 兼容旧操作 保存数据
     * @param $table
     * @param $data
     * @return int|string
     * @throws MySQLException
     */
    public function save($table, $data)
    {
        return $this->table($table)->data($data)->insert();
    }

    /**
     * 连贯操作 - 设置表
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->_table = $table;

        return $this;
    }

    public function alias($alias) {
        $this->_alias = " as ".trim($alias);

        return $this;
    }

    public function join($join)
    {
        $this->_join = " ".$join;

        return $this;
    }

    /**
     * 连贯操作 - 设置查询字段
     * @param $field
     * @return $this
     */
    public function field($field)
    {
        $this->_field = $field;

        return $this;
    }

    /**
     * 连贯操作 - 条件
     * @param $where
     * @return $this
     */
    public function where($where)
    {
        $this->_where = $where;

        return $this;
    }

    /**
     * 连贯操作 - 条件
     * @param $havingWhere
     * @return $this
     */
    public function having($havingWhere)
    {
        $this->_having = $havingWhere;

        return $this;
    }

    /**
     * 连贯操作 - 数据(插入、更新)
     * @param $data
     * @return $this
     */
    public function data($data)
    {
        $this->_data = $data;

        return $this;
    }
    /**
     * @param int $page_size
     * @param int $page
     * 连贯操作，定位查询数据范围
     */
    public function page($page_size = 50, $page = 1) {
        if(!is_numeric($page_size)) $page_size = 50;
        if(!is_numeric($page)) $page = 1;
        $start = $page_size * ($page-1);
        $offset = $page_size;
        $this->_limit = $start . ',' . $offset;

        return $this;
    }

    /**
     * 连贯操作 - 排序
     * @param string $order_by
     * @return $this
     */
    public function order_by($order_by)
    {
        $this->_order_by = $order_by;

        return $this;
    }

    /**
     * 连贯操作 - 分组
     * @param string $group_by
     * @return $this
     */
    public function group_by($group_by)
    {
        $this->_group_by = $group_by;

        return $this;
    }

    /**
     * 连贯操作 - limit
     * @param $limit
     * @param bool $offset
     * @return $this
     */
    public function limit($limit, $offset = false)
    {
        if (!$offset) {
            $this->_limit = ($limit + 0);
        } else {
            $this->_limit = ($offset + 0) . ',' . ($limit + 0);
        }

        return $this;
    }

    /**
     * 连贯操作 - 返回SQL 不执行(未完成) TODO : 怎么设计？
     * @param bool $show_sql
     * @return $this
     */
    public function show_sql($show_sql = false)
    {
        $this->_showSql = $show_sql;

        return $this;
    }

    /**
     * 强制使用索引
     * @param bool $_index
     * @return $this
     */
    public function force($_index = false)
    {
        if ($_index) {
            $this->_force_index = "FORCE INDEX({$_index})";
        }

        return $this;
    }

    /**
     * 插入替换已经存在的（insert专用）
     * @param bool $_replace
     * @return $this
     */
    public function replace($_replace = false)
    {
        $this->_replace = $_replace;

        return $this;
    }

    /**
     * 连贯操作 - 插入数据
     * @return int|mixed|string
     * @throws MySQLException
     */
    public function insert()
    {
        $table          = $this->_parseTable();
        $sql            = "INSERT INTO {$table} ";
        $sql            .= $this->_parseData('insert');
        $this->lastSQL  = $sql;
        $pdo_parameters = $this->pdo_parameters;
        $this->clearOption();

        if ($this->_showSql) return $this->getLastSql();

        $this->watchException($sql,$pdo_parameters);
        $id = $this->_pdo->lastInsertId();
        if (empty($id)) {
            return $this->_sth->rowCount();
        } else {
            return $id;
        }
    }

    /**
     * 连贯操作 - 批量插入
     */
    public function mulInsert()
    {
        $table = $this->_parseTable();

        $sql = "INSERT INTO {$table} ";
        if ($this->_ignore) {
            $sql = "INSERT IGNORE INTO {$table} ";
        } elseif ($this->_replace) {
            $sql = "REPLACE INTO {$table} ";
        }

        $sql .= $this->_parseMulData('insert');

        if ($this->_duplicate && is_string($this->_duplicate) && strlen($this->_duplicate) > 1) {
            $sql .= $this->_duplicate;
        }

        $this->lastSQL  = $sql;
        $this->_sth     = $this->_pdo->prepare($sql);
        $pdo_parameters = $this->pdo_parameters;
        $this->clearOption();
        $this->watchException($sql,$pdo_parameters);
        //return $n;

        return $this->_sth->rowCount();
    }

    private function _parseMulData($type)
    {
        if ((!isset($this->_data)) || (empty($this->_data))) {
            return false;
        }
        //如果数据是字符串，直接返回
        if (is_string($this->_data)) {
            $data = $this->_data;

            return $data;
        }
        switch ($type) {
            case 'insert':

                $placeholder = $fields = array();

                $fields = array_keys($this->_data[0]);
                $values = [];

                foreach ($this->_data as $key => $tData) {
                    foreach ($tData as $tmpField => $value) {
                        if ($value === false || $value === true || !is_scalar($value)) continue;//过滤恒为false和true

                        $placeholder[] = ':field_' . $key . '_' . $tmpField;

                        $this->pdo_parameters[ 'field_' . $key . '_' . $tmpField ] = $value;
                    }
                    $values[]    = '(' . implode(",", $placeholder) . ')';
                    $placeholder = [];
                }

                return '(' . implode(",", $fields) . ') VALUES ' . implode(",", $values);

            case 'update':
                return false;
            default:
                return false;
        }
    }


    /**
     * 插入重复键值更新（insert专用）
     * @param array|bool $_duplicate
     * @return $this
     */
    public function duplicate($_duplicate = false)
    {
        $this->_duplicate = false;
        if (is_array($_duplicate)) {
            $this->_duplicate = ' on duplicate key update';
            $tmpStr           = ' ';
            foreach ($_duplicate as $field => $value) {
                if(is_numeric($field)){
                    $this->_duplicate .= "{$tmpStr}{$value}=values({$value})";
                }else{
                    $this->_duplicate .= "{$tmpStr}{$field}={$value}";
                }
                $tmpStr           = ',';
            }
        }

        return $this;
    }

    /**
     * 插入忽略已经存在的（insert专用）
     * @param bool $_ignore
     * @return $this
     */
    public function ignore($_ignore = false)
    {
        $this->_ignore = $_ignore;

        return $this;
    }

    /**
     * 连贯操作 - 更新数据
     * @param $table
     * @param $data
     * @param string $field
     * @return bool|int|mixed|string
     * @throws MySQLException
     */
    public function update($table, $data, $field = 'id')
    {
        $id = $data[ $field ];
        return $this->table($table)->where("{$field}='$id'")->data($data)->upt();
    }

    /**
     * @param $table
     * @param $id
     * @param string $field
     * @return bool|int|mixed|string
     * @throws MySQLException
     */
    public function remove($table, $id, $field = 'id')
    {
        return $this->table($table)->where("{$field}='$id'")->delete();
    }

    /**
     * @return bool|int|mixed|string
     * @throws MySQLException
     */
    public function upt(){
        $table = $this->_parseTable();
        $sql   = "UPDATE {$table} ";
        $sql   .= $this->_parseData('update');
        $where = $this->_parseWhere();
        if (!$where) return false;
        $sql            .= ' WHERE ' . $where;
        $pdo_parameters = $this->pdo_parameters;
        $this->clearOption();

        return $this->query($sql, $pdo_parameters);
    }

    /**
     * 连贯操作 - 获取单条数据
     * @param bool $sql
     * @param null $parameters
     * @return mixed
     * @throws MySQLException
     */
    public function get($sql = false, $parameters = null)
    {
        if ($sql) return $this->fetch($sql, $parameters) ?: [];
        $table          = $this->_parseTable();
        $field          = $this->_parseField();
        $sql            = 'SELECT ' . $field . ' FROM ' . $table . $this->_parseCondition();
        $this->lastSQL  = $sql;
        $pdo_parameters = $this->pdo_parameters;
        $this->clearOption();
        $this->watchException($sql,$pdo_parameters);

        return $this->_sth->fetch($this->_fetch_type) ?: [];
    }

    /**
     * 兼容旧的类方法名
     * @return mixed
     */
    public function getSql(){
        return $this->getLastSql();
    }

    /**
     * 连贯操作 - 获取数据列表
     * @param bool $key
     * @return array|mixed|string
     * @throws MySQLException
     */
    public function select($key = false)
    {
        $this->_select();

        if ($this->_showSql) {
            return $this->getLastSql();
        }

        $result = array();
        if ($key) {
            while ($tmpResult = $this->_sth->fetch(\PDO::FETCH_ASSOC)) {
                $result[ $tmpResult[ $key ] ] = $tmpResult;
            }
        } else {
            while ($row = $this->_sth->fetch(\PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * 连贯操作 - 获取指定列数组
     * @param bool $option
     * @return array|bool
     * @throws MySQLException
     */
    public function selectColumn($option = false)
    {
        if (!$option) return false;
        $this->_select();
        $result = array();
        if (!is_numeric($option)) {
            while ($tmpResult = $this->_sth->fetch(\PDO::FETCH_ASSOC)) {
                $result[] = $tmpResult[ $option ];
            }
        } else {
            while ($row = $this->_sth->fetchColumn($option)) {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * 连贯操作 - 查询预处理
     * @throws MySQLException
     */
    private function _select()
    {
        $table          = $this->_parseTable();
        $field          = $this->_parseField();
        $sql            = 'SELECT ' . $field . ' FROM ' . $table . $this->_parseCondition();
        $this->lastSQL  = $sql;
        $pdo_parameters = $this->pdo_parameters;
        $this->clearOption();

        if (!$this->_showSql) {
            $this->watchException($sql,$pdo_parameters);

        }

    }

    /**
     * 连贯操作 - 删除数据
     * @return bool|int|mixed|string
     * @throws MySQLException
     */
    public function delete()
    {
        $table = $this->_parseTable();
        $where = $this->_parseWhere();
        if (!$where) return false;
        $sql            = 'DELETE FROM ' . $table . ' WHERE ' . $where;
        $pdo_parameters = $this->_parsePdoParameters($this->pdo_parameters);
        $this->clearOption();

        $rowCount = $this->query($sql, $pdo_parameters);
        if($rowCount === true) $rowCount = 0;
        return $rowCount;
    }

    /**
     * 连贯操作 - 清除状态
     */
    private function clearOption()
    {
        $this->_data               = array();
        $this->_table              = '';
        $this->_alias              = '';
        $this->_join               = '';
        $this->_where              = '';
        $this->_field              = '';
        $this->_order_by           = '';
        $this->_group_by           = '';
        $this->_having           = '';
        $this->_limit              = '';
        $this->lase_pdo_parameters = $this->pdo_parameters;
        $this->pdo_parameters      = array();
    }


    /**
     * 开始事务
     * @return bool
     */
    public function begin()
    {
        return $this->beginTransaction();
    }

    public function beginTransaction()
    {
        return $this->_pdo->beginTransaction();
    }

    /**
     * 判断是否处于事务
     * @return bool
     */
    public function inTransaction()
    {
        return $this->_pdo->inTransaction();
    }

    /**
     * 事务回滚
     * @return bool
     */
    public function rollBack()
    {
        return $this->_pdo->rollBack();
    }

    /**
     * 事务提交
     * @return bool
     */
    public function commit()
    {
        return $this->_pdo->commit();
    }

    /**
     * sql 执行错误处理
     * @param $sql
     * @param $pdo_parameters
     * @throws MySQLException
     */
    function watchException($sql,$pdo_parameters)
    {
        try {
            if (!$this->pdo_ping()) $this->connect($this->_str);
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $this->_sth = $this->_pdo->prepare($sql);
            $execute_state = $this->_sth->execute($pdo_parameters);
            if (!$execute_state && $this->debugLevel) {
                $errorInfo = $this->_sth->errorInfo();
                $this->errorInfo = [
                    'sql' => $this->getLastSql(),
                    'error' => $errorInfo[2],
                    'code' => intval($this->_sth->errorCode())
                ];
                $this->errorInfoArray[] = $this->errorInfo;
                if ($this->debugLevel == 1) {
                    /*$msg = '错误原因：'  . implode('|',$errorInfo)." 语句:{$this->getLastSql()}";
                    sendMsg(DEVELOPER_MOBILE,$msg);
                    echo PHP_EOL;*/
                    throw new MySQLException("SQLS: {$this->getLastSql()}\n" . $errorInfo[2], intval($this->_sth->errorCode()));
                }
            }
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            exit($errorMsg);
        }
    }


    /**
     * 解析数据,添加数据时$type=add,更新数据时$type=save
     * @param $type
     * @return array|bool|string
     */
    private function _parseData($type)
    {
        if ((!isset($this->_data)) || (empty($this->_data))) {
            return false;
        }
        //如果数据是字符串，直接返回
        if (is_string($this->_data)) {
            $data = $this->_data;

            return $data;
        }
        switch ($type) {
            case 'insert':

                $placeholder = $fields = array();

                foreach ($this->_data as $key => $value) {
                    // $value = $this->_parseValue($value);
                    if ($value === false || $value === true || !is_scalar($value)) continue;//过滤恒为false和true
                    $placeholder[] = ':field_' . $key;
                    $fields[]      = $this->_parseSpecialField($key);

                    $this->pdo_parameters[ 'field_' . $key ] = $value;
                }

                return '(' . implode(",", $fields) . ') VALUES (' . implode(",", $placeholder) . ')';

            case 'update':

                $fields = array();

                foreach ($this->_data as $key => $value) {
                    // $value = $this->_parseValue($value);
                    if ($value === false || $value === true || !is_scalar($value)) continue; // 过滤恒为false和true和非标量数据
                    $fields[] = $this->_parseSpecialField($key) . '=:field_' . $key;

                    $this->pdo_parameters[ 'field_' . $key ] = $value;
                }

                return ' SET ' . implode(',', $fields);
                break;
            default:
                return false;
        }
    }

    /**
     * 解析sql查询条件
     * @return string
     */
    private function _parseCondition()
    {
        $condition = "";

        if(!empty($this->_alias) && is_string($this->_alias)) {
            $condition .= $this->_alias;
        }

        if(!empty($this->_join) && is_string($this->_join)) {
            $condition .= $this->_join;
        }

        if (!empty($this->_where)) {
            $where = $this->_parseWhere($this->_where);
            if ($where) {
                $condition .= ' WHERE ' . $where;
            }
        }

        if (!empty($this->_group_by) && is_string($this->_group_by)) {
            $condition .= " GROUP BY " . $this->_group_by;
        }

        if (!empty($this->_having) && is_string($this->_having)) {
            $condition .= " HAVING " . $this->_having;
        }

        if (!empty($this->_order_by) && is_string($this->_order_by)) {
            $condition .= " ORDER BY " . $this->_order_by;
        }
        if (!empty($this->_limit) && (is_string($this->_limit) || is_numeric($this->_limit))) {
            $condition .= " LIMIT " . $this->_limit;
        }
        if (empty($condition)) return "";

        return $condition;
    }

    /**
     * where条件分析
     * @param array|bool|string $where
     * @return bool|string
     */
    private function _parseWhere($where = false)
    {
        /** @var array|bool|string $where */
        $where = $where ?: $this->_where;
        // 字符串直接返回
        if (is_string($where)) {
            return $where;
        }

        $whereStr = '';

        $operate = ' AND '; // 默认进行 AND 运算

        if (array_key_exists('_logic', $where)) {
            // 定义逻辑运算规则 例如 OR XOR AND NOT
            $operate = ' ' . strtoupper($where['_logic']) . ' ';
            unset($where['_logic']);
        }

        foreach ($where as $key => $val) {
            if (is_array($val) && empty($val)) continue;
            $whereStr .= "( ";
            if (0 === strpos($key, '_')) {
                // 解析特殊条件表达式 暂时不做处理
                $whereStr .= $this->_parseSpecialWhere($key, $val);
            } else {
                $filedKey = $this->_parseSpecialField($key);
                $key      = $this->_parsePdoParameters($key);
                if (is_array($val)) {
                    if (is_string($val[0])) {
                        if (preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i', $val[0])) { // 比较运算
                            $whereStr                                    .= $filedKey . ' ' . $this->comparison[ strtolower($val[0]) ] . ' ' . ':condition_' . $key;
                            $this->pdo_parameters[ 'condition_' . $key ] = $this->_parseValue($val[1]);
                        } elseif ('exp' == strtolower($val[0])) {
                            // 使用表达式
                            $whereStr                                    .= ' (' . $filedKey . ' ' . ':condition_' . $key . ') ';
                            $this->pdo_parameters[ 'condition_' . $key ] = $this->_parseValue($val[1]);
                        } elseif (preg_match('/IN/i', $val[0])) { // IN 运算
                            if (is_string($val[1])) {
                                $val[1] = explode(',', $val[1]);
                            }
                            $tmpKeys = array();
                            foreach ($val[1] as $i => $item) {
                                $tmpKey                          = 'condition_' . $key . '_' . $i;
                                $tmpKeys[]                       = ":{$tmpKey}";
                                $this->pdo_parameters[ $tmpKey ] = $item;
                            }
                            $tmpKeys  = implode(',', $tmpKeys);
                            $whereStr .= $filedKey . ' ' . strtoupper($val[0]) . ' (' . $tmpKeys . ')';
                        } elseif (preg_match('/BETWEEN/i', $val[0])) { // BETWEEN运算
                            $data     = is_string($val[1]) ? explode(',', $val[1]) : $val[1];
                            $whereStr .= ' (' . $filedKey . ' BETWEEN ' . ':condition_' . $key . '_1' . ' AND ' . ':condition_' . $key . '_2' . ' )';

                            $this->pdo_parameters[ 'condition_' . $key . '_1' ] = $data[0];
                            $this->pdo_parameters[ 'condition_' . $key . '_2' ] = $data[1];
                        } else {
                            exit('操作符错误:' . $val[0]);
                        }
                    } else {
                        $count = count($val);
                        if (is_string($val[ $count - 1 ]) && in_array(strtoupper(trim($val[ $count - 1 ])), array(
                                'AND',
                                'OR',
                                'XOR'
                            ))) {
                            $rule  = strtoupper(trim($val[ $count - 1 ]));
                            $count = $count - 1;
                        } else {
                            $rule = 'AND';
                        }
                        for ($i = 0; $i < $count; $i++) {
                            $data = is_array($val[ $i ]) ? $val[ $i ][1] : $val[ $i ];
                            if ('exp' == strtolower($val[ $i ][0])) {
                                // $whereStr .= '(' . $key . ' ' . ':condition_' . $key . ') ' . $rule . ' ';
                                $whereStr .= "{$key} :condition_{$key}_{$i}) {$rule} ";

                                $this->pdo_parameters["condition_{$key}_{$i}"] = $data;
                            } else {
                                $op       = is_array($val[ $i ]) ? $this->comparison[ strtolower($val[ $i ][0]) ] : '=';
                                $whereStr .= "({$key} {$op} :condition_{$key}_{$i}) {$rule} ";

                                $this->pdo_parameters["condition_{$key}_{$i}"] = $this->_parseValue($data);
                            }
                        }
                        $whereStr = substr($whereStr, 0, -4);
                    }
                } else {
                    $whereStr                                    .= $filedKey . " = " . ':condition_' . $key;
                    $this->pdo_parameters[ 'condition_' . $key ] = $this->_parseValue($val);
                }
            }
            $whereStr .= ' )' . $operate;
        }
        $whereStr = substr($whereStr, 0, -strlen($operate));

        return empty($whereStr) ? '' : $whereStr;
    }

    /**
     * 特殊条件分析
     * @param $key
     * @param $val
     * @return bool|string
     */
    private function _parseSpecialWhere($key, $val)
    {
        $whereStr = '';
        switch ($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr = $this->_parseWhere($val);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val, $where);
                if (array_key_exists('_logic', $where)) {
                    $op = ' ' . strtoupper($where['_logic']) . ' ';
                    unset($where['_logic']);
                } else {
                    $op = ' AND ';
                }
                $array = array();
                foreach ($where as $field => $data) {
                    $array[] = '`' . $this->_parseSpecialField($field) . '`=:condition_' . $field;

                    $this->pdo_parameters[ 'condition_' . $field ] = $this->_parseValue($data);
                }
                $whereStr = implode($op, $array);
                break;
        }

        return $whereStr;
    }

    /**
     * 表名处理
     * @return string
     */
    private function _parseTable()
    {

        $tmpTb  = $this->_table;
        $AsName = '';

        if (is_array($this->_table)) {
            $tmpTb  = $this->_table[0];
            $AsName = ' AS `' . $this->_table[1] . '`';
        }

        $parts = explode(".", $tmpTb, 2);

        if (count($parts) > 1) {
            $table = $parts[0] . ".`{$parts[1]}`";
        } else {
            $table = "`$tmpTb`";
        }

        return $table . $AsName;
    }

    /**
     * TODO : 表连接处理
     * @uses Db::_parseJoin()
     */
    private function _parseJoin()
    {

    }

    /**
     * 过滤字段
     * @return string
     */
    private function _parseField()
    {
        if (is_array($this->_field)) {
            // 完善数组方式传字段名的支持
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array = array();
            foreach ($this->_field as $key => $field) {
                if (!is_numeric($key)) {
                    $array[] = $this->_parseSpecialField($key) . ' AS ' . $this->_parseSpecialField($field);
                } else {
                    $array[] = $this->_parseSpecialField($field);
                }

            }
            $fieldsStr = implode(',', $array);
        } elseif (is_string($this->_field) && !empty($this->_field)) {
            $fieldsStr = $this->_parseSpecialField($this->_field);
        } else {
            $fieldsStr = '*';
        }

        return $fieldsStr;
    }

    /**
     * 字段和表名添加` 保证指令中使用关键字不出错 针对mysql
     * @param $value
     * @return string
     */
    private function _parseSpecialField($value)
    {
        $value = trim($value);
        if (false !== strpos($value, ' ') || false !== strpos($value, ',') || false !== strpos($value, '*') || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos($value, '`')) {
            //如果包含* 或者 使用了sql方法 则不作处理
        } else {
            $value = '`' . $value . '`';
        }

        return $value;
    }

    /**
     * value分析
     * @param $value
     * @return array|string
     */
    private function _parseValue($value)
    {
        if (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value = $value[1];
        } elseif (is_array($value)) {
            $value = array_map(array(
                $this,
                '_parseValue'
            ), $value);
        } elseif (is_null($value)) {
            $value = '\'\'';
        }

        return $value;
    }


    private function _parsePdoParameters($pdo_parameters)
    {
        if (is_array($pdo_parameters)) {
            foreach ($pdo_parameters as $key => $value) {
                if (preg_match('/\./i', $key)) {
                    $nKey                    = str_replace('.', '_', $key);
                    $pdo_parameters[ $nKey ] = $value;
                    unset($pdo_parameters[ $key ]);
                }

            }
        } else {
            if (preg_match('/\./i', $pdo_parameters)) {
                $pdo_parameters = str_replace('.', '_', $pdo_parameters);
            }
        }

        return $pdo_parameters;
    }

    /**
     * 调试模式
     * @param $debug
     */
    public function setDebug($debug = false)
    {
        $this->debugLevel = intval($debug);
    }

    /**
     * 获取错误信息
     * @return array
     */
    public function errorInfo()
    {
        return $this->_sth->errorInfo();
    }

    /**
     * 获取错误码
     * @return string
     */
    function errorCode()
    {
        return $this->_sth->errorCode();
    }

    /**
     * 获取指定编号的记录.
     * @param int $id 要获取的记录的编号.
     * @param string $field 字段名, 默认为'id'.
     * @param $table
     * @return mixed
     * @throws MySQLException
     */
    public function load($table, $id, $field = 'id')
    {
        $sql = "SELECT * FROM {$table} WHERE `{$field}`='{$id}'";
        $row = $this->get($sql);

        return $row;
    }

    public function getLastSql()
    {
        $sql = $this->lastSQL;
        arsort($this->lase_pdo_parameters);
        foreach ($this->lase_pdo_parameters as $key => $value) {
            $sql = str_replace(":{$key}", "'{$value}'", $sql);
        }

        return $sql;
    }

    public function getErrorInfo()
    {
        return $this->errorInfo;
    }

    public function getAllErrorInfo()
    {
        return $this->errorInfoArray;
    }

    /**
     * 检查连接是否可用
     * @return Boolean
     */
    public function pdo_ping()
    {
        try {
            set_error_handler(function() { /* ignore errors */ });
            $this->_pdo->getAttribute(PDO::ATTR_SERVER_INFO);
            restore_error_handler();
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                return false;
            }
        }
        return true;
    }

    public function connect($str){
        $config = DBConfig::$$str;
        global $logErrors;
        $logErrors = $logErrors ."\n".json_encode($config);
        $dsn        = $config['type'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'];
        $options    = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $config['charset'],
            \PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        );
        $this->_pdo = new \PDO($dsn, $config['user'], $config['password'], $options);
        $this->_str = $str;
    }

}

class MySQLException extends \Exception
{
}
