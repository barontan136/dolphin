<?php

require_once dirname(__DIR__) . '/Bootstrap/Worker.php';

defined('MODULE_NAME') || define('MODULE_NAME', 'Room');

use Workerman\Worker;
use Utils\Task;
use Handlers\RoomHandler;

$task = new Task('Room', RoomHandler::class);
// task进程数可以根据需要多开一些

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
