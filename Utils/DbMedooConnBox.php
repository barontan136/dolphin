<?php
namespace Utils;

use Utils\DbMedooConn;

/**
 * 数据库连接类，依赖 PDO_MYSQL 扩展
 * 在 https://github.com/catfan/medoo 的基础上修改而成
 */
class DbMedooConnBox
{
    protected $conn = null;
    protected $options = null;
    public function __construct($options)
    {
        $this->options = $options;
        $this->connect();
    }

    public function connect() {
        $this->conn = new DbMedooConn($this->options);
    }

    public function __call($method,  $arg_array)
    {
        return call_user_func_array(
            array($this->conn, $method),
            $arg_array
        );
    }
}
