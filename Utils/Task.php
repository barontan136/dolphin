<?php
namespace Utils;
use Exception;
use Workerman\Worker;

use Config\Task as TaskConfig;

class Task extends Worker
{
    protected $handler = null;

    public function __construct($name, $handlerClass)
    {

        if (!array_key_exists($name, TaskConfig::$config)) {
            printf("config not found Module:%s\n", $name);
            exit(250);
        }
        $config = TaskConfig::$config[$name];
        /*$config = array(
            'address'   => 'Text://127.0.0.1:10130',
            'workers'   => 1,
        );*/
        $uri = $config['address']; //地址
        $this->count = max(intval($config['workers']), 1); //进程熟练

        $this->name = $name;
        parent::__construct($uri);

        $this->onWorkerStart = function($worker) use ($uri, $handlerClass) {
            $this->logger = Logging::getLogger($this->name);
            $this->logger->info(sprintf("Worker starting @ %s", $uri));
            $this->handler = new $handlerClass();
        };

        $this->onMessage = function($connection, $data) {
            list($startmicro, $startsecond) = explode(' ', microtime());
            //接到消息先检查mysql链接
            DbMedoo::ping();
            $jArr = json_decode($data, true);
            $jStr = '';
            $jRetStr = '';
            $sAction = 'notfound';
            try {
                if ($jArr['r']) {
                    $jStr = json_encode($jArr['r']);
                } else {
                    $jStr = '';
                }
                $sAction = isset($jArr['a']) ? $jArr['a'] : 'notexists';
                $this->logger->info(sprintf('[%s][input][%s]', $sAction, $data));
                if (method_exists($this->handler, $jArr['a'])) {
                    $oInput = new WorkerInput($jArr['r'], $jArr['a']);

                    $jRetStr = call_user_func_array(
                        array($this->handler, $jArr['a']), array($oInput)
                    );
                } else {
                    $jRetStr = json_encode(array(
                        'errno' => '10001',
                        'msg' => '方法不存在！'
                    ));
                }
                $this->logger->info(sprintf('[%s][output][%s]', $sAction, $jRetStr));
                $connection->send($jRetStr);
            } catch (Exception $ex) {
                $jRetStr = json_encode(array(
                    'errno' => '10001',
                    'msg' => sprintf(
                        'Exception:%s, Code:%s',
                        $ex->getMessage(),
                        $ex->getCode()
                    )
                ));

                $this->logger->error(sprintf('[%s][output][%s]', $sAction, $jRetStr), $ex);
                $connection->send($jRetStr);
            } finally {
                $data = null;
                $jArr = null;
                $jStr = null;
                $jRetStr = null;
                $connection->close();
            }
            list($endmicro, $endsecond) = explode(' ', microtime());
            $lefttime = ($endsecond - $startsecond) + ($endmicro - $startmicro);
            $this->logger->info(sprintf('[%s][lefttime] %.6f', $sAction, $lefttime));
        };
    }
}