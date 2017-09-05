<?php
namespace Tables;

use Utils\DbMedoo;
use Utils\IdGenerator;

abstract class BaseTable
{
    protected $prefix ='';
    protected $tableName = '';
    protected $pk = '';
    protected $fields = array();
    protected $_map = array();
    protected $_auto = array();
    protected $error_code;
    protected $message=array();
    protected $medoo = null;
    protected $moduleInId = '';
    protected $tableInId = '';


    public function __construct()
    {
        $this->medoo = DbMedoo::instance('db1');
    }

    public function genId() {
        return IdGenerator::genId($this->getModuleInId(), $this->getTableInId());
    }

    /**
     * 获取组成id的模块标记
     * @return string
     */
    public function getModuleInId()
    {
        return $this->moduleInId;
    }

    /**
     * 设置组成id的模块标记
     * @param string $moduleInId 标记内容
     */
    public function setModuleInId($moduleInId)
    {
        $this->moduleInId = $moduleInId;
        return $this;
    }

    /**
     * 获取组成id的表id
     * @return string
     */
    public function getTableInId()
    {
        return $this->tableInId;
    }

    /**
     * 设置组成id的表id
     * @param string $tableInId 标记内容
     */
    public function setTableInId($tableInId)
    {
        $this->tableInId = $tableInId;
        return $this;
    }

    /**
     * 作用：获得表名(带前缀)
     * @return string
     */
    public function table()
    {
        return $this->prefix.$this->tableName;
    }

    /**
     * 作用：获得表名(不带前缀)
     * @return string
     */
    public function getTable()
    {
        return $this->tableName;
    }

    /**
     * 作用：设置表明
     * @param string $tableName 表名(不带前缀)
     */
    public function setTable($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * 获取主键列名
     * @return string
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * 设置主键列名
     * @param string $pk
     */
    public function setPk($pk)
    {
        $this->pk = $pk;
        return $this;
    }

    /**
     * 获取表前缀
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 设置表前缀
     * @param string $Prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 检查指定记录是否存在
     * @param mixed $pk
     * @return boolean
     */
    public function exist($pk)
    {
        return $this->findByPk($pk, $this->pk);
    }

    /**
     * 像指定表插入一条记录
     * @param array $data 要插入的数据
     * @return mixed 最后插入的自增id(lastInsertId)
     */
    public function insert($data)
    {
        return $this->medoo->insert($this->table(), $data);
    }

    /**
     * 根据某张表的主键删除一条记录
     * @param mixed $pk
     * @return int 影响行数
     */
    public function deleteByPk($pk)
    {
        return $this->medoo->delete($this->table(), [
            $this->pk => $pk
        ]);
    }

    /**
     * 根据某张表的主键更新一条记录
     * @param array $data
     * @param mixed $pk
     * @return int 影响行数
     */
    public function updateByPk($data, $pk)
    {
        return $this->medoo->update($this->table(), $data, [
            $this->pk => $pk
        ]);
    }

    /*
     * 作用：获得指定表的一条记录
     * @oaram mixed $pk
     * @param string $field
     * @return mixed 返回查询的结果
     * */
    public function findByPk($pk, $columns='')
    {
        if (empty($columns)) {
            $columns = '*';
        }
        return $this->medoo->get($this->table(), $columns, [
            $this->pk => $pk
        ]);
    }

    /*
     * 作用：获得指定字段值
     * @oaram mixed $pk
     * @param string $field
     * @return mixed 返回查询的结果
     * */
    public function findOneByPk($pk, $field)
    {
        return $this->medoo->get($this->table(), $field, [
            $this->pk => $pk
        ]);
    }

    /*
     * 作用：获得指定表的一条记录
     * @oaram mixed $pk
     * @param string/array $fields
     * @return mixed 返回查询的结果
     * */
    public function findRowByPk($pk, $fields='*')
    {
        return $this->medoo->get($this->table(), $fields, [
            $this->pk => $pk
        ]);
    }

    /**
     * 开启事物
     * @return boolean 成功开启事物返回true 否则返回false
     */
    public function beginTransaction()
    {
        return $this->medoo->getPdo()->beginTransaction();
    }

    /**
     * 回滚事物
     * @return boolean 成功返回true否则返回false
     */
    public function rollBack()
    {
        return $this->medoo->getPdo()->rollBack();
    }

    /**
     * 是否已经开启了事物
     * @return boolean 已经开启了事物返回true 否则返回false
     */
    public function inTransaction()
    {
        return $this->medoo->getPdo()->beginTransaction();
    }

    /**
     * 提交事物
     * @return boolean 成功返回true否则返回false
     */
    public function commit()
    {
        return $this->medoo->getPdo()->commit();
    }

    public function findMapByPks(array $pks, $columns=null) {
        if (empty($columns)) {
            $columns = '*';
        }
        if (empty($pks)) {
            return array();
        }
        $list = $this->medoo->select($this->table(), $columns, [
            $this->pk => $pks
        ]);
        $result = array();
        foreach ($list as $item) {
            $result[$item[$this->getPk()]] = $item;
        }
        return $result;
    }

    /**
     * 处理字段映射
     * @access public
     * @param array $data 当前数据
     * @param integer $type 类型 0 写入 1 读取
     * @return array
     * $data 格式：      array('表单字段' =>  '数据库字段');
     *
     */
    public function parseFieldsMap($data,$type=0)
    {
        // 检查字段映射
        if(!empty($this->_map)) {
            foreach($this->_map as $key=>$val) {
                if($type==1) { // 读取
                    if(isset($data[$val])) {
                        $data[$key] = $data[$val];
                        unset($data[$val]);
                    }
                } else {
                    if(isset($data[$key])) {
                        $data[$val] = $data[$key];
                        unset($data[$key]);
                    }
                }
            }
        }
        return $data;
    }

    /*
     * 作用：自动过滤字段
     * 负责把传来的数组清除掉不用的单元,留下与表的字段对应的单元
     * $this->fields属性可以手动设置过滤字段，没有设置，默认去对应表的所有字段
     * @param array $arr
     * @return array
     * */
    public function autoFilter($arr=array())
    {
        if(empty($this->fields)) {
            $this->fields = $this->medoo->getFields($this->table());
        }
        $data = array();
        foreach($arr as $key => $value)
        {
            if(in_array($key, $this->fields)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /*
     * 作用： 自动填充
     * @param array $data
     * @return array
     * 格式 $this->_auto =  array(
     * 							array('field(字段名)',
     * 								  '填充规则（value填充某个值/function：调用某个方法）',
     * 								  '附加规则（value:字符串；  function ：使用函数，表示填充的内容是一个函数名 ）'
     * 				                  '附加参数  (主要用于给回调函数提供参数)'
     * 								 )
     * 							);
     * 填充字段        必须
     * */
    public function autoFill($data)
    {
        if(empty($this->_auto)) {
            return $data;
        }
        foreach($this->_auto as $value)
        {
            if(!array_key_exists($value[0], $data)) {
                switch($value[1]) {
                    case 'value':
                        $data[$value[0]] = $value[2];
                        break;
                    case 'function':
                        if(isset($value[3])) {
                            $data[$value[0]] = call_user_func($value[2], $value[3]);
                        } else {
                            $data[$value[0]] = call_user_func($value[2]);
                        }
                        break;
                }
            }
        }
        return $data;
    }

    public function getDb() {
        return $this->medoo;
    }
}
