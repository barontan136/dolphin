<?php

require_once dirname(__DIR__) . '/Bootstrap/Base.php';

defined('APP_LOG_PATH') ||
    define('APP_LOG_PATH', APP_ROOT.'/data/logs/tasks');
define('MODULE_TYPE', 'WORKER');
