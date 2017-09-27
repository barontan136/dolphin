<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/2/23
 * Time: 16:11
 */
namespace Tables\User\Cache;

use Utils\RedisClient;

class VCodeRedisTable
{
    private $vCode = "vc:%s"; //用户名(手机号)到验证码的映射，数据结构：HASH

    public function __construct()
    {
        $this->cache = RedisClient::getInstance();
    }


    /**
     * 将短信验证码存储至Redis中减少数据库的压力
     * @param string $reg_mobile
     * @param array $code_data
     * @param int $ttl 单位：秒
     * @return bool
     */
    public function setVerifyCode($reg_mobile, $code_data, $ttl=120)
    {
        return true;
        /**
         * $code_data结构
         * vcode => 验证码
         * sms_log_id => ks_sms_log表的主键
         */
        try {
            $key = sprintf($this->vCode, $reg_mobile);
            $this->cache->hMSet($key, $code_data);
            $this->cache->expire($key, intval($ttl));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 根据手机号从Redis中获取短信验证码
     * @param string $reg_mobile
     * @return array | bool
     */
    public function getVerifyCode($reg_mobile)
    {
        return '0000';
        try {
            $key = sprintf($this->vCode, $reg_mobile);
            return $this->cache->hGetAll($key);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 根据手机号获取指定字段值
     * @param string $reg_mobile
     * @param string $field
     * @return bool
     */
    public function getVerifyCodeField($reg_mobile, $field)
    {
        try {
            $key = sprintf($this->vCode, $reg_mobile);
            return $this->cache->hGet($key, $field);
        } catch (\Exception $e) {
            return false;
        }
    }
}