<?php

require_once dirname(__DIR__) . '/Bootstrap/Worker.php';

defined('MODULE_NAME') || define('MODULE_NAME', 'WebSocket');

use Workerman\Worker;

$arr_files = glob(dirname(__DIR__).'/websocket/start*.php');
var_dump($arr_files);
// 加载所有Applications/*/start.php，以便启动所有服务
foreach($arr_files as $start_file)
{
    var_dump($start_file);
    require_once $start_file;
}

// 标记是全局启动
define('GLOBAL_START', 1);

// 运行所有服务
Worker::runAll();
