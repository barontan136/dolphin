<?php
namespace Utils;
use Logger;
use Config\Log4php as LogConfig;

class Logging
{
	//private static $confile = dirname(__FILE__).'../Config/logger.properties';
    protected static $is_init = false;

    public static function init()
    {
        if (empty(self::$is_init)) {
            if (!file_exists(APP_LOG_PATH)) {
                mkdir(APP_LOG_PATH, 0755, true);
            }
            self::$is_init = true;
            Logger::configure(LogConfig::get());
        }
        return self::$is_init;
    }

    public static function getLogger($name='')
    {
        self::init();
        if (empty($name)) {
            if (defined('MODULE_NAME')) {
                $name = MODULE_NAME;
            } else {
                $name = 'default';
            }
        }
        return Logger::getLogger($name);
	}

    public static function json_pretty($data)
    {
        $json_encode_options = 0;
        $json_encode_options |= JSON_PRETTY_PRINT;
        $json_encode_options |= JSON_UNESCAPED_SLASHES;
        $json_encode_options |= JSON_UNESCAPED_UNICODE;

        return json_encode($data, $json_encode_options);
    }
}