<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/2/14
 * Time: 11:02
 */
namespace Tables\User\Cache;

use Utils\RedisClient;

class UserRedisTable
{
    private $cache = null;
    private $userStatic = "lzus:%s"; //用户静态信息，不经常改动，数据结构：HASH
    private $userDynamic = "lzud:%s"; //用户动态信息，经常变动，数据结构：HASH
    private $name2id = "lzun:%s"; //用户名(手机号)到用户id的映射，数据结构：键值对

    public function __construct()
    {
        $this->cache = RedisClient::getInstance();
    }

    /**
     * 设置用户基本缓存数据
     * @param $user_id string
     * @param $user_data array
     * @return bool
     */
    public function setUserStaticCacheByUserId($user_id, $user_data)
    {
        /**
         * user_data 数据结构(HASH)
         * name => 用户名称(手机号)
         * login_pwd => 用户密码(md5(md5(用户密码原文).salt))
         * deal_pwd => 交易密码(md5(md5(用户密码原文).salt))
         * device_id => 设备惟一标识ID
         * is_company => 1-公司账户(管理后台添加，不需要实名)，0-普通用户(APP或WAP上用户主动注册，需要实名)
         * status => 用户账号状态：1-启用(默认)，0-禁用
         */
        if ($user_id && is_array($user_data)) {
            $key = sprintf($this->userStatic, $user_id);
            return $this->cache->hMSet($key, $user_data);
        }
        return false;
    }

    /**
     * 根据user_id获取用户基本信息的hash全部属性值
     * @param $user_id string
     * @return mixed
     */
    public function getUserStaticCacheByUserId($user_id)
    {
        if ($user_id) {
            $key = sprintf($this->userStatic, $user_id);
            return $this->cache->hGetAll($key);
        }
        return false;
    }

    /**
     * 根据user_id更新其field_key的值为field_val
     * @param $user_id string
     * @param $field_key string
     * @param $field_val string
     * @return bool
     */
    public function setUserStaticFieldByUserId($user_id, $field_key, $field_val=null)
    {
        if ($user_id && $field_key && !is_null($field_val)) {
            $key = sprintf($this->userStatic, $user_id);
            return $this->cache->hSet($key, $field_key, $field_val);
        }
        return false;
    }

    /**
     * 根据user_id获取其field_key的值
     * @param $user_id string
     * @param $field_key string
     * @return bool
     */
    public function getUserStaticFieldByUserId($user_id, $field_key)
    {
        if ($user_id && $field_key) {
            $key = sprintf($this->userStatic, $user_id);
            return $this->cache->hGet($key, $field_key);
        }
        return false;
    }

    /**
     * 根据user_id设置用户动态数据
     * @param string $user_id
     * @param array $user_data
     * @param int $ttl
     * @return bool|mixed
     */
    public function setUserDynamicCacheByUserId($user_id, $user_data, $ttl = 0)
    {
        /**
         * user_data数据结构(HASH)
         * access_token  用户访问token具有时效性，目前有效期为7天(7*24*3600)
         * source 用户最后登录的平台类型：1-WAP，2-AOS，3-IOS
         */
        if ($user_id && is_array($user_data)) {
            $key = sprintf($this->userDynamic, $user_id);
            if ($ttl) {
                $this->cache->hMSet($key, $user_data);
                return $this->setExpireByKey($key, $ttl);
            } else {
                return $this->cache->hMSet($key, $user_data);
            }
        }
        return false;
    }

    /**
     * 根据user_id获取用户动态信息数据
     * @param $user_id
     * @return bool
     */
    public function getUserDynamicCacheByUserId($user_id)
    {
        if ($user_id) {
            $key = sprintf($this->userDynamic, $user_id);
            return $this->cache->hGetAll($key);
        }
        return false;
    }

    /**
     * 根据user_id更新其field_key的值为field_val
     * @param string $user_id
     * @param string $field_key
     * @param string $field_val
     * @param int $ttl
     * @return bool|mixed
     */
    public function setUserDynamicFieldByUserId($user_id, $field_key, $field_val, $ttl = 0)
    {
        if ($user_id && $field_key && $field_val) {
            $key = sprintf($this->userDynamic, $user_id);
            if ($ttl) {
                $this->cache->hSet($key, $field_key, $field_val);
                return $this->setExpireByKey($key, $ttl);
            }
            return $this->cache->hSet($key, $field_key, $field_val);
        }
        return false;
    }

    /**
     * 删除用户动态信息中的指定属性字段
     * @param string $user_id
     * @param string $field_key
     * @return bool
     */
    public function delUserDynamicFieldByUserId($user_id, $field_key)
    {
        if ($user_id && $field_key) {
            $key = sprintf($this->userDynamic, $user_id);
            return $this->cache->hDel($key, $field_key);
        }
        return false;
    }

    /**
     * 根据user_id判断Redis中存储的动态信息(包含access_token)是否存在(是否过期)
     * @param string $user_id
     * @return bool
     */
    public function isDynamicExistsByUserId($user_id)
    {
        if ($user_id) {
            $key = sprintf($this->userDynamic, $user_id);
            return $this->cache->exists($key);
        }
        return false;
    }

    /**
     * 根据user_id获取其field_key的值
     * @param $user_id
     * @param $field_key
     * @return bool
     */
    public function getUserDynamicFieldByUserId($user_id, $field_key)
    {
        if ($user_id && $field_key) {
            $key = sprintf($this->userDynamic, $user_id);
            return $this->cache->hGet($key, $field_key);
        }
        return false;
    }

    /**
     * 设置KEY的过期时间，单位：秒
     * @param string $key_name
     * @param int $ttl
     * @return mixed
     */
    public function setExpireByKey($key_name, $ttl)
    {
        return $this->cache->expire($key_name, intval($ttl));
    }

    /**
     * 设置用户名(手机号)映射到用户id
     * @param $user_name
     * @param $user_id
     * @return mixed
     */
    public function setUserNameToUserId($user_name, $user_id)
    {
        $key = sprintf($this->name2id, $user_name);
        return $this->cache->set($key, $user_id);
    }

    /**
     * 根据用户名(手机号)删除用户名与user_id映射关系
     * @param string $user_name
     * @return mixed
     */
    public function delUserNameToUserId($user_name)
    {
        $key = sprintf($this->name2id, $user_name);
        return $this->cache->delete($key);
    }

    /**
     * 根据user_name获取其值
     * @param $user_name
     * @return mixed
     */
    public function hasUserName($user_name)
    {
        $key = sprintf($this->name2id, $user_name);
        return $this->cache->exists($key);
    }

    /**
     * 根据用户名(手机号)获取其user_id的映射
     * @param string $user_name
     * @return mixed
     */
    public function getUserIdByUserName($user_name)
    {
        $key = sprintf($this->name2id, $user_name);
        return $this->cache->get($key);
    }

    /**
     * 自增用户静态数据
     * @param string $user_id
     * @param string $field
     * @param int $step
     */
    public function IncrUserStaticField($user_id, $field, $step)
    {
        $key = sprintf($this->userStatic, $user_id);
        return $this->cache->hIncrBy($key, $field, $step);
    }
}