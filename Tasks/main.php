<?php
require_once dirname(__DIR__) . '/Bootstrap/Gateway.php';

ini_set('serialize_precision', -1); //设置JSON序列化时浮点型数据类型精度处理方法

define('MODULE_NAME', 'Gateway');

use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
use Utils\Token;
use Utils\Common;
use Utils\Logging;
use Config\GatewayConstants;

// 创建一个Worker监听2345端口，使用http协议通讯
$uri = GatewayConstants::LISTEN_URI;
$http = new Worker($uri);
$http->onWorkerStart = function ($worker) use ($uri) {
    echo "Worker starting @ $uri \r\n";
};

$logger = Logging::getLogger();

$config = GatewayConstants::RPC_API_NODE;

// 启动10个进程对外提供服务
$http->count = 10;
// 接收到浏览器发送的数据时回复hello world给浏览器
$http->onMessage = function ($connection, $data) use ($logger, $config, $des) {
    //开始时间
    list($startmicro, $startsecond) = explode(' ', microtime());

    $jArr = $data['post'];
    $logger->info(sprintf('[input][raw] [%s]', Logging::json_pretty($jArr)));
    $aResult = [];
    $isEncrypt = false;

    //定义回调函数
    $output = function($connection, $module, $action, $aResult, $rawArr) use(
        $startmicro,
        $startsecond,
        $logger
    ) {
        $logger->info(sprintf(
            "[%s] [%s][output][retNewJson] [%s]",
            $module,
            $action,
            Logging::json_pretty($aResult)
        ));
        $retStr = json_encode($aResult, JSON_UNESCAPED_UNICODE);
        $retEncrypt = $retStr;

        $connection->send($retEncrypt);
        $connection->close();

        list($endmicro, $endsecond) = explode(' ', microtime());
        $lefttime = ($endsecond - $startsecond) + ($endmicro - $startmicro);
        $logger->info(sprintf(
            '[%s] [%s][lefttime] %.6f',
            $module,
            $action,
            $lefttime
        ));

    };

    $module = trim($jArr['module']);     //模块名称
    $action = trim($jArr['method']);     //方法名称
    $params = $jArr['ras'];           //请求参数,DES_CBC加密
    $token = $jArr['token'];            //上次服务端返回给客户端的token
    $ver = $jArr['version'];              //客户端版本号
    $pla = intval($jArr['platform']);      //客户端平台类型,0-WEB, 1-AOS, 2-IOS
    $packageId = intval($jArr['packageId']);      //分包号（多个APP）
    $channel = intval($jArr['channel']);      //渠道名称
    $deviceName = intval($jArr['deviceName']);      //设备号
    $androidVersion = intval($jArr['androidVersion']);      //android 版本号

    //获取客户端ip
    $IP = isset($data['server']['HTTP_REMOTEIP']) ? $data['server']['HTTP_REMOTEIP'] : $data['server']['REMOTE_ADDR'];

    if (!isset($jArr['module']) && !isset($jArr['method'])
        && !isset($jArr['ras']) && !isset($jArr['token'])
        && !isset($jArr['version']) && !isset($jArr['platform'])
    ) {
        $aResult = array(
            "return_code" => "10003",
            "return_message" => "参数不完整",
            'token' => Token::getToken(),
        );
        $newArr = array('a'=>$action, 'r'=>$params, 'c'=>[
            'ip'       => $IP,
            'token'    => $token,   //上次服务端返回给客户端的token
            'module'   => $module,  //模块名称
            'version'  => $ver,     //客户端版本号
            'platform' => $pla,     //客户端平台类型,0-WEB, 1-AOS, 2-IOS
        ]);
        return $output($connection, 'NOTFOUND', 'NOTFOUND', $aResult, $des, $isEncrypt, $newArr);
    }

    $rawArr = [];
    $rawArr = json_decode($params, true);
    $newArr = array('a'=>$action, 'r'=>$rawArr, 'c'=>[
        'ip'       => $IP,
        'token'    => $token, //上次服务端返回给客户端的token
        'module'   => $module, //模块名称
        'version'  => $ver, //客户端版本号
        'platform' => $pla, //客户端平台类型,0-WEB, 1-WAP, 2-AOS, 3-IOS, 99-ADMIN
    ]);

    try {
        $address = Common::getWorkerAddress($pla, $ver, $module);
        $logger->info(sprintf('[%s] [%s][address][%s]', $module, $action, $address));
        $task = new  AsyncTcpConnection($address);
        $newJson = json_encode($newArr);
        $logger->info(sprintf(
            "[%s] [%s][input][newJson] [%s]",
            $module,
            $action,
            $newJson
        ));

        $task->send($newJson);
        $task->onMessage = function ($task_connection, $jStr) use (
            $connection,
            $logger,
            $module,
            $action,
            $isEncrypt,
            $des,
            $newArr,
            $output
        ) {
            // 结果
            $logger->info(sprintf(
                "[%s] [%s][output][retData] [%s]",
                $module,
                $action,
                $jStr
            ));
            $aResult = json_decode($jStr, true);
            $aResult['token'] = Token::getToken();
            return $output($connection, $module, $action, $aResult, $des, $isEncrypt, $newArr);
        };

        $task->connect();

    } catch (\Exception $e) {
        $logger->info(sprintf("[Exception][%s][%s][%s][%s]", $module, $action, $isEncrypt, $params));
        $aResult = array(
            "return_code" => "10001",
            "return_message" => sprintf(
                "module:%s, action:%s, Exception:%s, Code:%s",
                $module, $action,
                $e->getMessage(), $e->getCode()
            ),
            'token' => Token::getToken(),
        );
        return $output($connection, $module, $action, $aResult, $des, $isEncrypt, $newArr);
    }

};
// 运行worker
Worker::runAll();
