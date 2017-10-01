<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/3/20
 * Time: 15:52
 */
namespace Tables\Config\Cache;

use Utils\RedisClient;

class KSConfigRedisTable
{
    protected $cKey = "LZCONF:%s"; //用户名(手机号)到验证码的映射，数据结构：HASH

    public function __construct()
    {
        $this->cache = RedisClient::getInstance();
    }

    /**
     * 根据提供的KEY有VAL将其值存入REDIS中
     * @param string $key
     * @param mixed $val
     * @return bool
     */
    public function setValueByKey($key, $val)
    {
        //数据结构为：KEY=>VAL
        try {
            $cKey = sprintf($this->cKey, $key);
            return $this->cache->set($cKey, $val);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 根据提供的KEY从Redis中取其值,失败返回FALSE
     * @param string $key
     * @return bool
     */
    public function getValueByKey($key)
    {
        //数据结构为：KEY=>VAL
        try {
            $cKey = sprintf($this->cKey, $key);
            return $this->cache->get($cKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 根据提供的KEY名称删除其在REDIS中的值
     * @param string $key
     * @return bool
     */
    public function delValueByKey($key)
    {
        //数据结构为：KEY=>VAL
        try {
            $cKey = sprintf($this->cKey, $key);
            return $this->cache->delete($cKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 根据KEY设置其值及TTL，单位：秒
     * @param $key
     * @param $val
     * @param int $ttl
     * @return bool
     */
    public function setValueByKeyAndTTL($key, $val, $ttl=0)
    {
        try{
            $cKey = sprintf($this->cKey, $key);
            if ($ttl) {
                return $this->cache->setex($cKey, $ttl, $val);
            }
            return $this->cache->set($cKey, $val);
        } catch (\Exception $e) {
            return false;
        }
    }
}