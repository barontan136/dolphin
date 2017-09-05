<?php
namespace Modules;

use Utils\Logging;
use Utils\SaltTool;
use Config\KafkaConfig;
use Tables\User\UserTable;
use Tables\User\Cache\UserRedisTable;
use Utils\Common;
class UserModule
{
    private $log = null;
    private $userTable = null;
    private $userCacheTable = null;

    public function __construct()
    {
        $this->userTable = new UserTable();
        $this->userCacheTable = new  UserRedisTable();
        $this->log = Logging::getLogger();
    }

    /**
     * 获取用户注册信息
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function getUserInfo($user_id, $fields = '*')
    {
        return $this->userTable->getUserInfoByUserId($user_id, $fields);
    }


}