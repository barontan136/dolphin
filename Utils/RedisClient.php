<?php
namespace Utils;

use Config\Redis AS AB;
use Exception;
use Redis;
use RedisException;


class RedisClient
{
    public static $ins = null;
    protected $redis = null;

    final protected function __construct()
    {
        $this->redis = new Redis();
        @$this->redis->connect(AB::$redis1['host'], AB::$redis1['port']);
        $this->redis->auth(AB::$redis1['password']);
    }

    /**
     * 生成单例
     * */
    public static function getInstance()
    {
        // if (!self::$ins instanceof self) {
        //     try{
        //         self::$ins = new self();
        //     } catch(RedisException $e) {
        //         throw new Exception($e->getMessage(), '999999');
        //     }
        // }
        // return self::$ins;

        try{
            return new self();
        } catch(RedisException $e) {
            throw new Exception($e->getMessage(), '999999');
        }
    }


    /*
     * 作用：生成、获取、删除缓存  (針對redis的string存储类型)
     * @param string $key
     * @param mixed $value
     * @param int $cache_time  //缓存有效时间
     * */
    public function cache_data($key, $value='', $cache_time=0)
    {
        if($value !== '') {
            if($value === null) {
                //删除缓存数据
                if($this->redis->exists($key)) {
                    return $this->redis->del($key);
                } else {
                    return TRUE;
                }
            }
            //将数据转换成json格式保存到redis缓存
            $this->redis->set($key, json_encode($value));
            if((int)$cache_time > 0) {
                //设置过期时间
                $this->redis->expire($key, $cache_time);
            }
            return TRUE;
        }
        $res = $this->redis->get($key);
        return !empty($res) ? json_decode($res, true) : FALSE;
    }

    public function __call($method, $args) {
        if (method_exists($this->redis, $method)) {
            return call_user_func(array($this->redis, $method), ...$args);
        }
        throw new Exception('999999', ErrMessage::$message['999999']);
    }

}