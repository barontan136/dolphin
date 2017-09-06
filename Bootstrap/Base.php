<?php

date_default_timezone_set('Asia/Shanghai');
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/Utils/helpers.php';

define('APP_ROOT', dirname(__DIR__));

try {
    (new Dotenv\Dotenv(APP_ROOT))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
}
