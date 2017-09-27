<?php
namespace Modules;

use Tables\User\Cache\UserRedisTable;

class TokenModule
{
    private $access_token = null;
    private $userCacheTable = null;

    final protected function __construct()
    {
        $this->userCacheTable = new  UserRedisTable();
    }

    public static function getInstance()
    {
        return new self();
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * 生成access_token串
     * @param string $device_id
     * @return string
     */
    private function createToken($device_id)
    {
        return $this->access_token = sprintf('T%s', md5('LZ_' . time() . $device_id));
    }

    /**
     * 生成、存储access_token至Redis并返回access_token
     * @param string $user_id
     * @param string $device_id
     * @param int $pla
     * @return string
     */
    public function createAccessToken($user_id, $device_id, $pla)
    {
        $access_token = $this->createToken($device_id);
        $invalid_time = 7 * 24 * 3600;
        $this->userCacheTable->setUserDynamicCacheByUserId($user_id,
            array(
                'accessToken' => $access_token,
                'source' => intval($pla)
            ),
            $invalid_time);
        return $access_token;
    }

    /**
     * 删除指定用户的access_token
     * @param string $user_id
     * @return bool
     */
    public function deleteAccessToken($user_id)
    {
        return $this->userCacheTable->delUserDynamicFieldByUserId($user_id, 'accessToken');
    }

    /**
     * 检查用户access_token是否有效
     * @param string $user_id
     * @param string $access_token
     * @return bool
     */
    public function checkAccessToken($user_id, $access_token)
    {
        $dynamic = $this->userCacheTable->getUserDynamicCacheByUserId($user_id);
        if ($dynamic && isset($dynamic['accessToken']) && $dynamic['accessToken']) {
            return $dynamic['accessToken'] == $access_token ? true : false;
        }
        return false;
    }


    /**
     * 检查当前用户的token是否存在
     * @param string $user_id
     * @return bool
     */
    public function checkExist($user_id)
    {
        return $this->userCacheTable->isDynamicExistsByUserId($user_id);
    }

    /**
     * 根据user_id更新用户动态信息字段的值
     * @param string $user_id
     * @param string $field
     * @param string $value
     * @param int $ttl
     * @return bool|mixed
     */
    public function setDynamicField($user_id, $field, $value, $ttl=0)
    {
        return $this->userCacheTable->setUserDynamicFieldByUserId($user_id, $field, $value, $ttl);
    }

    /**
     * 根据user_id获取用户动态信息字段的值
     * @param string $user_id
     * @param string $field
     * @return bool
     */
    public function getDynamicField($user_id, $field)
    {
        return $this->userCacheTable->getUserDynamicFieldByUserId($user_id, $field);
    }

    /**
     * 根据user_id删除用户动态信息字段及值
     * @param string $user_id
     * @param string $field
     * @return bool
     */
    public function delDynamicField($user_id, $field)
    {
        return $this->userCacheTable->delUserDynamicFieldByUserId($user_id, $field);
    }

}