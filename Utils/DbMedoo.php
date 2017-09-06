<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Utils;

use Config\Db as DbConfig;
use Config;
use Exception;
use PDOException;

/**
 * 数据库类
 */
class DbMedoo
{
    /**
     * 实例数组
     *
     * @var array
     */
    protected static $instance = array();

    /**
     * 获取实例
     *
     * @param string $config_name
     * @return DbMedooConnBox
     * @throws Exception
     */
    public static function instance($config_name)
    {
        /*
        if (!isset(DbConfig::$$config_name)) {
            throw new Exception("\\Config\\Db::$config_name not set\n");
        }
        */

        if (empty(self::$instance[$config_name])) {
            $config = DbConfig::$$config_name;
            self::$instance[$config_name] = new DbMedooConnBox([
                'database_type' => $config['adapter'],
                'database_name' => $config['dbname'],
                'server'        => $config['host'],
                'port'          => $config['port'],
                'username'      => $config['user'],
                'password'      => $config['password'],
                'charset'       => $config['charset'],
                // 'prefix'        => $config['prefix'],
                'prefix'        => '',
                'option'        => $config['option']

            ]);
        }
        return self::$instance[$config_name];
    }

    public static function ping() {
        self::$instance = array_map(function($conn) {
            try {
                $conn->query("select 1");
            } catch (PDOException $e) {
                // 服务端断开时重连一次
                if ($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013) {
                    $conn->closeConnection();
                    $conn->connect();
                }
            }
            return $conn;
        }, self::$instance);
    }

    /**
     * 关闭数据库实例
     *
     * @param string $config_name
     */
    public static function close($config_name)
    {
        if (isset(self::$instance[$config_name])) {
            self::$instance[$config_name]->closeConnection();
            self::$instance[$config_name] = null;
        }
    }

    /**
     * 关闭所有数据库实例
     */
    public static function closeAll()
    {
        foreach (self::$instance as $name => $connection) {
            self::flush($name);
            self::close($name);
        }
        self::$instance = array();
    }
}
