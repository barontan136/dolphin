<?php
namespace Utils;

use Medoo;

use PDO;
use Exception;
use PDOException;

/**
 * 数据库连接类，依赖 PDO_MYSQL 扩展
 * 在 https://github.com/catfan/medoo 的基础上修改而成
 */
class DbMedooConn extends Medoo
{
    protected $logger = null;
    protected $transactionLevel = 0;

    public function __construct($options = null) {
        parent::__construct($options);
        $this->logger = Logging::getLogger();
    }

    /**
     * 清理sql的log
     */
    public function clearLog()
    {
        $this->log = array();
    }

    /**
     * 返回并清理sql的log
     */
    public function popLog()
    {
        $log = $this->log();
        $this->clearLog();
        return $log;
    }

    /**
     * 关闭连接
     */
    public function closeConnection()
    {
        $this->clearLog();
        $this->pdo = null;
    }

	public function query($query)
	{
		if ($this->debug_mode)
		{
			echo $query;

			$this->debug_mode = false;

			return false;
		}

		// $this->logs[] = $query;
        $this->sqllog($query);
		return $this->pdo->query($query);
	}

	public function exec($query)
	{
		if ($this->debug_mode)
		{
			echo $query;

			$this->debug_mode = false;

			return false;
		}

		// $this->logs[] = $query;
        $this->sqllog($query);

		return $this->pdo->exec($query);
	}

    public function sqllog($query) {
        $this->logger->debug(sprintf('[sqllog:%s]', $query));
    }

    public function getPdo() {
        return $this->pdo;
    }

    /**
     * 开启事物
     * @return boolean 成功开启事物返回true 否则返回false
     */
    public function beginTransaction()
    {
        //$this->getPdo()->inTransaction()
        $this->transactionLevel++;
        if ($this->transactionLevel > 1) {
            return true;
        }
        return $this->getPdo()->beginTransaction();
    }

    /**
     * 回滚事物
     * @return boolean 成功返回true否则返回false
     */
    public function rollBack()
    {
        if (0 == $this->transactionLevel) {
            return true;
        }
        $this->transactionLevel = 0;
        return $this->getPdo()->rollBack();
    }

    /**
     * 是否已经开启了事物
     * @return boolean 已经开启了事物返回true 否则返回false
     */
    public function inTransaction()
    {
        return $this->transactionLevel > 0;
    }

    /**
     * 提交事物
     * @return boolean 成功返回true否则返回false
     */
    public function commit()
    {
        if ($this->transactionLevel > 1) {
            $this->transactionLevel--;
            return true;
        }
        $this->transactionLevel = 0;
        return $this->getPdo()->commit();
    }

    public function action($actions)
	{
        $result = false;
        do {
            if (!is_callable($actions)) {
                break;
            }

            //如果抛出异常或者返回假则自动回滚事物
            try {
                $this->beginTransaction();
                $result = $actions($this);
                if ($result === false) {
                    $this->rollBack();
                    break;
                }
				$this->commit();
            } catch(Exception $e) {
                $this->logger->error(sprintf(
                    'DbMedoo [%s:%s]',
                    $e->getMessage(),
                    $e->getCode()
                ), $e);
				$this->rollBack();
                throw $e;
            }
        } while(false);
        return $result;
	}
}
