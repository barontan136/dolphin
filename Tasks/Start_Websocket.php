<?php

require_once dirname(__DIR__) . '/Bootstrap/Worker.php';

defined('MODULE_NAME') || define('MODULE_NAME', 'WebSocket');

use Workerman\Worker;

// 加载所有Applications/*/start.php，以便启动所有服务
foreach(glob(__DIR__.'/websocket/start*.php') as $start_file)
{
    require_once $start_file;
}
// 运行所有服务
Worker::runAll();
