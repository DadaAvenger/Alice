<?php

/**
 * Class Model 模型基类
 */

class Model
{
    protected $db;
    protected $_str;

    protected $table = null;

    protected $fields = null;

    protected $list = null;

    protected $idNameCacheArr = [];
    /** id-name 关联列表缓存 */
    protected $idNameCacheName  = null;
    protected $idNameCacheKey   = null;
    protected $idNameCacheValue = null;

    // where 条件按 $fields 筛选和排序
    protected $isOrderWhere = false;

    /** @var array 临时缓存用列表 */
    protected $tmpList = [];
    static private $redisLink;

    protected function __construct($table = false, $db_str = false  )
    {
        if (!$db_str) $db_str = DBConfig::$defaultDb;
        $this->_str = $db_str;
        $this->db = DB::getInstance($db_str);
        if($table !== false) $this->table = $table;
        //如果模型没有自定义fields字段，则自动获取
//        if($this->fields == null) {
//            $this->initTable();
//        }
    }

    /**
     * 初始化Model
     * @param bool $modelName 自定义model配置
     * @param array|boolean $config
     * @return mixed|static
     */
    public static function init($table = FALSE, $db_str = FALSE)
    {
        static $_models = array();

        // 需要模型类
        $modelName = get_called_class();
        $model_key = $modelName ."_". $table . $db_str;
        if (!array_key_exists($model_key, $_models) || !$_models[ $model_key ] instanceof self){
            if (!class_exists($modelName, false)) {
                //toUrl('ERROR', $modelName . '模型类不存在。');
                echo $modelName. '模型类不存在。';
                exit;
            }

            $instance = $_models[ $model_key ] = new static($table, $db_str);

            return $instance;
        }

        return $_models[ $model_key ];
    }

    public function getTable() {
        return $this->table;
    }

    // 调用获取数据列表
    public function getList($where = false, $field = false, $key = null, $limit = false, $offset = false, $orderBy = false, $groupBy = false, $having = ''){
        return $this->lists(['where' => $where, 'field' => $field, 'key' => $key, 'limit' => $limit, 'offset' => $offset, 'orderBy' => $orderBy, 'groupBy' => $groupBy, 'having' => $having]);
    }

    // 获取数据列表
    public function lists($arr=['where' => false, 'field' => false, 'key' => null, 'limit' => false, 'offset' => false, 'orderBy' => false, 'groupBy' => false, 'having'=>''])
    {
        extract($arr);
        if (!$this->table) return false;
        $where = $where ?: '1';
        $field = $field ?: '*';
        if ($key === null && in_array('id', $this->fields)) $key = 'id';
        if (!$orderBy && in_array('id', $this->fields, true)) {
            $orderBy = 'id desc';
        }else {
            $orderBy = $this->filterOrder($orderBy,$field);
        }
        if($groupBy) $groupBy = $this->filterGroup($groupBy,$field);


        if (is_array($where) && $this->isOrderWhere) {
            $tmpWhere = [];
            foreach ($this->fields as $tmpField) {
                if (!isset($where[ $tmpField ])) continue;
                $tmpWhere[ $tmpField ] = $where[ $tmpField ];
            }
            $where = $tmpWhere;
        }

        return $this->db->table($this->table)
            ->where($where)
            ->field($field)
            ->limit($limit, $offset)
            ->order_by($orderBy)
            ->group_by($groupBy)
            ->having($having)
            ->select($key);
    }


    // 获取数据列表
    public function listsAndCount($arr=['where' => false, 'field' => false, 'key' => null, 'limit' => false, 'offset' => false, 'orderBy' => false, 'groupBy' => false, 'having'=>''])
    {
        extract($arr);
        if (!$this->table) return false;
        $where = $where ?: '1';
        $field = $field ?: '*';
        if ($key === null && in_array('id', $this->fields)) $key = 'id';
        if (!$orderBy && in_array('id', $this->fields, true)) {
            $orderBy = 'id desc';
        }else {
            $orderBy = $this->filterOrder($orderBy,$field);
        }
        if($groupBy) $groupBy = $this->filterGroup($groupBy,$field);


        if (is_array($where) && $this->isOrderWhere) {
            $tmpWhere = [];
            foreach ($this->fields as $tmpField) {
                if (!isset($where[ $tmpField ])) continue;
                $tmpWhere[ $tmpField ] = $where[ $tmpField ];
            }
            $where = $tmpWhere;
        }

        $data = $this->db->table($this->table)
            ->where($where)
            ->field($field)
            ->order_by($orderBy)
            ->group_by($groupBy)
            ->having($having)
            ->select($key);
        $return = [];
        $totals=count($data);
        $start= $offset; #计算每次分页的开始位置
        $countpage=ceil($totals/$limit); #计算总页面数
        $pagedata=array();
        $pagedata=array_slice($data,$start,$limit);
        $return['count'] = $totals;

        $return['list'] = $pagedata;
        return $return;
    }
    // 获取数据简版
    public function find($field = false, $where = [])
    {
        $field = $field ?? '*';
        if (!$this->table) return false;
        if(is_array($where)) {
            foreach ($this->fields as $_field) {
                if (!isset($where[ $_field ])) continue;
                $_where[ $_field ] = $where[ $_field ];
            }
            $where = $_where ?? [];
        }

        return $this->db->table($this->table)->where($where)->field($field)->select();
    }

    // 获取单列
    public function colList($field = false, $where = false)
    {
        if (!$this->table || !$field) return false;
        $where = $where ?: '1';
        echo $this->table;
        return $this->db->table($this->table)->where($where)->field($field)->selectColumn($field);
    }

    // 获取数据数量
    public function count($where=[],$field="*",$group_by=false,$having=false)
    {
        $where = $where ?: '1';
        $in = $group_by&&$having?true:false;
        return $this->db->table($this->table)->where($where)->group_by($group_by)->having($having)->count(false,[],$field,$in);
    }

    /**
     * 指定列求和
     * @param array  $where
     * @param string $field
     * @param bool   $_index
     * @return int
     */
    public function sum($where=[], $field='', $_index=false)
    {
        if(empty($field)){
            return 0;
        }

        $where = $where ?: '1';

        $result = $this->db->table($this->table)->where($where)->field("SUM({$field}) AS {$field}")->force($_index)->select();

        return $result ? $result[0][$field] : 0;
    }

    // 获取单条数据
    public function info($where = false, $field = false)
    {
        if (!$this->table) return false;
        $where = $where ?: '1';
        $field = $field ?: '*';

        $orderBy = null;
        if (in_array('id', $this->fields, true)) $orderBy = 'id desc';
        return $this->db->table($this->table)->field($field)->where($where)->order_by($orderBy)->get();
    }

    // 更新数据表
    public function update($data = false, $where = false, $limit = false)
    {
        if (!$data || !is_array($data)) return false;
        if (!$where) return false;

        $limit = $limit ?: 1;

        if (isset($data['id'])) unset($data['id']);

        $updateData = [];
        foreach ($this->fields as $field) {
            if (!isset($data[ $field ])) continue;
            $updateData[ $field ] = $data[ $field ];
        }

        return $this->db->table($this->table)->where($where)->data($updateData)->limit($limit)->upt();
    }

    // 插入数据
    public function create($data = false, $needs = [], $ignore=false , $ignore_id = true)
    {
        if (!$data || !is_array($data)) return false;

        if (isset($data['id']) && $ignore_id) unset($data['id']);

        $saveData = [];

        foreach ($this->fields as $field) {
            if (!isset($data[ $field ])) continue;
            $saveData[ $field ] = $data[ $field ];
        }

        foreach ($needs as $needKey) {
            if (!isset($saveData[ $needKey ])) return false;
        }

        return $this->db->table($this->table)->data($saveData)->ignore($ignore)->insert();
    }

    /**插入或更新
     * @param $data
     * @param null $chkField 数组格式
     * 出参bool值为false时才算失败
     * update的时候返回数字表示更新成功,返回true表示原数据和新数据一致
     * insert的时候返回数量或false
     */
    public function createOrUpdate($data, array $chkField=null) {
        $saveData = [];
        foreach ($this->fields as $field) {
            if (!isset($data[ $field ])) continue;
            $saveData[ $field ] = $data[ $field ];
        }
        if ( $chkField ) {
            foreach ($chkField as $field) {
                $chkData[$field] = $saveData[$field];
            }
        }else {
            $chkData = $saveData;
        }
        $chk = $this->db->table($this->table)->where($chkData)->get();
        if ( $chk ) {//
            $saveData['id'] = $chk['id'];
            $ret = $this->db->update($this->table, $saveData);
        }else {
            unset($saveData['id']);
            $ret = $this->db->table($this->table)->data($saveData)->insert();
        }
        return $ret;
    }

    // 批量插入
    public function mulCreate($data = false, $ignore=false, $replace =false)
    {
        if (!$data || !is_array($data) || !is_array($data[0])) return false;

        $saveData = [];

        foreach ($data as $key => $datum){

            $tmp = [];

            foreach ($this->fields as $field) {
                if (!isset($datum[ $field ])) continue;
                $tmp[ $field ] = $datum[ $field ];
            }

            $saveData[$key] = $tmp;

        }

        return $this->db->table($this->table)->data($saveData)->ignore($ignore)->replace($replace)->mulInsert();
    }

    // 删除数据
    public function delete($where = false, $limit = false)
    {
        $limit = $limit ?: 1; // 默认删除一条
        if (!$where) return false;

        $_where = [];
        foreach ($this->fields as $field) {
            if (!isset($where[ $field ])) continue;
            $_where[ $field ] = $where[ $field ];
        }

        return $this->db->table($this->table)->where($_where)->limit($limit)->delete();
    }

    //
    public function getListCount() {
        $sql = 'SELECT FOUND_ROWS() AS count';
        return $this->db->get($sql);
    }

    // 统计
    public function get($where, $field='*')
    {
        if (!$this->table) return false;
        $where = $where ?: '1';

        return $this->db->table($this->table)->field($field)->where($where)->get();
    }

    // 统计方便使用
    public function totalList($where = false, $fields = false, $groupBy = false, $orderBy = false)
    {
        if (!$where || !$fields) return false;

        return $this->lists($where, $fields, false, null, null, $orderBy, $groupBy);
    }

    // 获取最后的执行的sql
    public function getLastSql()
    {
        return $this->db->getLastSql();
    }

    // 获取$db
    public function getDb()
    {
        if($this->table) $this->db->table($this->table);
        return $this->db;
    }

    /**
     * 根据id(key)获取name
     * @ps 数据来源id-name关联列表
     * @param bool $key
     * @return bool|mixed
     */
    public function getNameById($key = false)
    {
        if (!$key) return false;

        if (!$this->idNameCacheArr) $this->idNameCacheArr = $this->getIdNameArrCache();

        return $this->idNameCacheArr[ $key ] ?? false;
    }

    /*------------- id-name(key-fieldValue缓存) -----------------*/

    /**
     * 根据主键获取info
     * @param $id
     * @param bool $key
     * @return mixed
     */
    public function getInfoById($id, $key = false)
    {
        $list = $this->tmpList[self::class] ?? false;
        if(!$list){
            $list = $this->tmpList[self::class] = $this->lists();
        }

        $info = $list[$id];

        return $key ? $info[$key] : $info;
    }

    /**
     * 根据 日期条件获取 s_date e_date
     * @param $dateWhere
     * @return array|bool
     */
    protected function getSAEDate($dateWhere)
    {
        // =
        if (is_string($dateWhere)) {
            return [
                's_date' => $dateWhere,
                'e_date' => $dateWhere,
            ];
        }

        if (is_array($dateWhere)) {
            // between
            if ($dateWhere[0] === 'between') {
                return [
                    's_date' => $dateWhere[1][0],
                    'e_date' => $dateWhere[1][1],
                ];
            }

            // in
            if ($dateWhere[0] === 'in' && is_array($dateWhere[1])) {
                return [
                    's_date' => array_shift($dateWhere[1]),
                    'e_date' => array_pop($dateWhere[1]),
                ];
            }

            // egt gt
            if ($dateWhere[0] === 'egt' || $dateWhere[0] === 'gt') {
                return [
                    's_date' => $dateWhere[1],
                    'e_date' => date('Y-m-d'),
                ];
            }

            // lt elt 不允许
        }

        return false;
    }

    //sql
    //替换表名
    public function listBySql($sql, $s_date = null, $e_date = null, $key = false, $parameters = array())
    {
        $tmpsql    = str_replace('tmp_table', $this->table, $sql);
        $tmpResult = $this->db->find($tmpsql, $key, $parameters);

        return $tmpResult;
    }

    public function getone($sql){
    	$tmpsql    = str_replace('tmp_table', $this->table, $sql);
        $tmpResult = $this->db->fetch($tmpsql );

        return $tmpResult;
    }

    public function query($sql){
    	$tmpsql    = str_replace('tmp_table', $this->table, $sql);
        $tmpResult = $this->db->query($tmpsql );

        return $tmpResult;
    }

    /**
     * 判断表名是否存在
     * @param $dbname
     * @param $tb
     * @return mixed
     */
    public function tbExist($dbname, $tb)
    {
        if(!$dbname && strpos($tb, '.') > -1){
            $tmp = explode('.', $tb);
            $dbname = $tmp[0];
            $tb = $tmp[1];
        }
        $sql = "select TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='{$dbname}' and TABLE_NAME='{$tb}'";

        try{
            $res = $this->db->fetchColumn($sql);
        }catch (MySQLException $mySQLException){
            return false;
        }

        return $res;
    }

    /**
     * 查询条件过滤
     * @param $where
     * @return mixed
     */
    public function filterWhere(&$where)
    {
        if(empty($this->fields)){
            return $where;
        }

        foreach ($where as $key => &$val){
            if(!in_array($key, $this->fields) || empty($val)){
                unset($val);
            }
        }

        return $where;
    }

    public function filterGroup(&$groupBy,$field = false) {
        if(empty($this->fields) && !$field){
            return $groupBy;
        }
        $groupBy = strtolower($groupBy);
        if(in_array($groupBy,$this->fields)) {
            return $groupBy;
        }
        if($field && !empty($field)) $field = explode(",",$field);
        foreach ($field as $_field) {
            if(stripos($_field,$groupBy) !== false) {
                return $groupBy;
            }
        }
        $group_arr = explode(",",$groupBy);
        foreach ($group_arr as $group) {
            if(in_array($group,$this->fields)) {
                return $groupBy;
            }
        }
        return false;
    }

    public function filterOrder(&$orderBy,$field = false) {
        if(empty($this->fields) && !$field){
            return $orderBy;
        }
        $orderBy = strtolower($orderBy);
        $orderBy = trim($orderBy);
        $orderByArr = explode(" ",$orderBy);
        $tmpOrderBy = $orderByArr[0];
        if(in_array($tmpOrderBy,$this->fields)) {
            return $orderBy;
        }
        if($field && !empty($field)) $field = explode(",",$field);
        foreach ($field as $_field) {
            if(stripos($_field,$tmpOrderBy) != false) {
                return $orderBy;
            }
        }
        $order_arr = explode(",",$orderBy);
        foreach ($order_arr as $order) {
            if(in_array($order,$this->fields)) {
                return $orderBy;
            }
        }
        return false;
    }


    /**
     * 通过指定条件从缓存中获取指定信息
     * @param array $where
     * @param array $data
     * @return array|bool|null
     */
    public function getByWhereFromCache($where=[], $data=[])
    {
        !$data && $data = $this->list;
        !$data && $data = $this->lists();

        $result = [];
        foreach ($data as $key => &$item){
            $flag = true;
            foreach ($where as $field => $val){
                if(!isset($item[$field]) || $item[$field] != $val){
                    $flag = false;
                }
            }

            $flag && $result[] = $item;
        }

        return $result;
    }

    /**
     * @param string $type file | redis
     * @return object
     */
    public function getCache() {
        if (empty(self::$redisLink)) {
            self::$redisLink = new Redis();
            self::$redisLink->connect("127.0.0.1", 6379, 2);
            self::$redisLink->auth("GDTce120f@3l");
        }
        return self::$redisLink;
    }

    public function checkFields() {
        if(DEBUG_DEV !== TRUE) {  //本地调试模式，校验模型和数据表是否对应
            return;
        }
        $table_name = "";
        $table_schema = "";

        if(stripos($this->table,'.') !== false) {
            $arr = explode('.', $this->table);
            $table_name = $arr[1];
            $table_schema = $arr[0];
            $sql = "select column_name,column_comment,data_type from information_schema.columns where table_name = '{$table_name}' and table_schema = '{$table_schema}'  ";
        }else {
            $table_name = $this->table;
            $sql = "select column_name,column_comment,data_type from information_schema.columns where table_name = '{$table_name}' ";
        }

        $res = $this->db->find($sql);
        $names = array_column($res,'column_name');
        $comments = array_column($res,'column_comment');
        $types = array_column($res,'data_type');

        $_fields = [];
        //校验模型定义的fields是否正确
        foreach ($this->fields as $field) {
            if(!in_array($field , $names )) {
                $_fields[] = $field;
            }
        }
        if($_fields) {
            $temp = implode('、', $_fields);
            jsonBack("请将模型{$table_name}的fields定义：{$temp}删除，数据表中没有定义！");
        }

        $_names = [];
        foreach ($names as $name) {
            if(!in_array($name, $this->fields)) {
                $_names[] = $name;
            }
        }
        if($_names) {
            $temp = implode('、', $_names);
            jsonBack("模型{$table_name}的fields定义缺少：{$temp}，请完善！");
        }

    }

    /**
     * 1.查询分页数据
     * 2.计算总数量
     * 3.格式化返回数据：page\page_size\total_number\total_page\list
     * 4.key == false时，返回不带索引key的数组
     */
    public function getPageData($pdata, $where = [] ,$key = false,$field='*', $groupBy = false,$having = '', $download = false) {
        $page = $pdata['page'] ?? 1;
        if (empty($pdata['page_size']) && $pdata['rows'])  $pdata['page_size'] = $pdata['rows'];
        $page_size = $pdata['page_size'] ?? 50;
        $orderField = $pdata['orderField'] ?? 'id';
        $orderDirection = $pdata['orderDirection'] ?? 'SORT_DESC';
        $offset = $page_size * ($page - 1);
        if( strripos($orderDirection , "desc") ) {
            $orderDirection = "desc";
        }else if ( strripos($orderDirection , "asc")) {
            $orderDirection = "asc";
        }
        $orderBy = $orderField . " " . $orderDirection;
        if ($download) $page_size = $offset = false;
        $data['page'] = $page;
        $data['page_size'] = $page_size;
        $data['total_number'] = ceil( $this->count($where,"*",$groupBy,$having));
        $data['total_page']   = $page_size ? ceil( $data['total_number']/$page_size ) : $data['total_number'];
        $data['list'] = $this->lists(['where'=>$where,'limit'=>$page_size,'offset'=>$offset,'orderBy'=>$orderBy,'key'=>$key,'field'=>$field , 'groupBy'=>$groupBy,'having' => $having]);
        if($key == false || !in_array($key, $this->fields, true)) {  //非有效索引，则返回json数组
            $data['list'] = array_values($data['list']);
        }

        return $data;
    }

    /**
     * 模型重设链接的数据库，默认ADS
     * 缓存表结构文件：cache/平台子目录/数据库连接名称_数据库名_表名
     */
    public function initTable($table = false, $db_str = false) {
        if($table !== false) $this->table = $table;
        if($db_str !== false) $this->db = DB::getInstance($db_str);
        if(!$this->table) return false;
        //本地调试模式不走缓存，避免本地频繁清空缓存操作
        if(TABLE_CACHE_OPEN === false) {
            $this->fields = $this->getFieldsByDB();
        }else {
            $subDir = "table";   //加一层子目录
            $file_name = str_replace(".","_",$this->_str."_". $this->table);
            $table_data = CommonFunc::fileCache($file_name,FALSE, $subDir);
            if(!$table_data) {
                $table_data = ['fields'=> $this->getFieldsByDB()];
                //判断子目录是否存在，并给777权限
                $cacheDir = FILE_CACHE_DIR.$subDir;
                if(!file_exists($cacheDir)) {
                    mkdir ($cacheDir,0777,true);
                    exec("chmod 777 $cacheDir");  //二次设置，保证为777权限
                }
                CommonFunc::fileCache($file_name, $table_data ,$subDir);
            }
            $this->fields = $table_data['fields'];
        }
        if($this->fields == null) {
            exit('模型初始化失败: fields定义为null' );
        }
    }

    public function getFieldsByDB() {
        if(stripos($this->table,'.') !== false) {
            $arr = explode('.', $this->table);
            $table_name = $arr[1];
            $table_schema = $arr[0];
            $sql = "select column_name,column_comment,data_type from information_schema.columns where table_name = '{$table_name}' and table_schema = '{$table_schema}'  ";
        }else {
            $table_name = $this->table;
            $sql = "select column_name,column_comment,data_type from information_schema.columns where table_name = '{$table_name}' ";
        }
        $res = $this->db->find($sql);
        return array_column($res,'column_name') ?? null;
    }
}
