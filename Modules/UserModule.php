<?php
namespace Modules;

use Utils\Logging;
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
//        $this->userCacheTable = new  UserRedisTable();
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
        $user_info = $this->userTable->getUserInfoByUserId($user_id, $fields);
        $ret_data = array(
            'uid' => $user_info['uid'],
            'nickname' => $user_info['nickname'],
            'headPic' => $user_info['headPic'],
            'level' => $user_info['userLevel'],
            'attentionNum' => $user_info['attentionNum'],
            'type' => $user_info['type'],
            'birthday' => $user_info['birthday'],
            'sex' => $user_info['sex'],
            'signature' => $user_info['signature'],
            'fansNum' => $user_info['fansNum'],
            'bgImg' => $user_info['bgImg'],
            'levelCoin' => $user_info['levelCoin'],
            'nextLevel' => $user_info['nextLevel'],
            'nextLevelNeedCoin' => $user_info['nextLevelNeedCoin'],
            'isAttention' => $user_info['attentionNum'],
            'isFirstLogin' => empty($user_info['lastLogin']) ? 1 : 0,
            //
            'moderatorLevel' => $user_info['moderatorLevel'],
            'userLevelName' => $user_info['userLevelName'],
            'moderatorLevelCoin' => $user_info['moderatorLevelCoin'],
            'moderatorLevelName' => $user_info['moderatorLevelName'],
            'moderatorNextLevel' => $user_info['moderatorNextLevel'],
            'moderatorNextLevelNeedCoin' => $user_info['moderatorNextLevelNeedCoin'],
            'isPlaying' => $user_info['isPlaying'],
            'videoPlayUrl' => $user_info['videoPlayUrl'],
            'rid' => $user_info['rid'],
            'verified' => $user_info['verified'],
            'verifyInfo' => $user_info['verifyInfo'],
            'flowerNumber' => $user_info['flowerNumber'],
        );

        return $ret_data;
    }


}