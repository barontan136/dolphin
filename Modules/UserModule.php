<?php
namespace Modules;

use Tables\Room\RoomTable;
use Tables\User\ModerSignTable;
use Tables\User\SignTypeTable;
use Tables\User\UserAuthTable;
use Utils\Logging;
use Tables\User\UserTable;
use Tables\User\Cache\UserRedisTable;
use Utils\Common;
use Config\GlobalConfig;

class UserModule
{
    private $log = null;
    private $userTable = null;
    private $signType = null;
    private $userCacheTable = null;

    public function __construct()
    {
        $this->userTable = new UserTable();
        $this->userCacheTable = new UserRedisTable();
        $this->log = Logging::getLogger();
        $this->signType = new SignTypeTable();
    }

    /**
     * 获取用户注册信息
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function getUserInfoByMobile($mobile)
    {
        return $this->userTable->getUserInfoByMoible($mobile);
    }

    /**
     * 获取用户注册信息
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function getSignTypes()
    {
        $ret = $this->signType->select('', '', '*');
        $result = [];
        //
        foreach($ret as $item){
            $key = $item['signID'];
            $val = $item['signName'];
            $result[$key] = $val;
        }
        return $result;
    }

    /**
     * 更新主播开播标题
     * @param string $user_id
     * @param string $roomTitle
     * @return mixed
     */
    public function updateUserAnnouncement($user_id, $roomTitle){
        $data = array(
            'roomTitle' => $roomTitle,
        );
        $where = [
            'AND' =>
            ['uid' => $user_id, 'type' => GlobalConfig::USER_MODER]
        ];
        return $this->userTable->updateUser($data, $where);
    }

    /**
     * 用户注册
     * @param array $user_data
     * @return mixed
     */
    public function userRegister($user_data)
    {
        return $this->userTable->createUser($user_data);
    }
    /**
     * 保存用户静态信息至Redis
     * @param string $user_id
     * @param array $user_data
     * @return bool
     */
    public function saveUserStaticToRedis($user_id, $user_data)
    {
        return $this->userCacheTable->setUserStaticCacheByUserId($user_id, $user_data);
    }


    /**
     * 获取用户注册信息
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function registerByMobile(
        $regMobile,
        $source,
        $deviceNum
    )
    {
        do{
            $configModule = new ConfigModule();
            $now = date('Y-m-d H:i:s');
            $user_id = $this->userTable->genId();
            //登录密码根据加密规则加密，存储数据库的是加密后的密文
//            $pwd_md5 = md5(md5($password) . $salt);
            $user_data = array(
                'uid'       => $user_id,
                'nickname'     => $regMobile,
                'regMobile'    => $regMobile,
                'regTime'      => $now,
                'password'     => '',
                'salt'         => '0000',
                'deviceNum'    => $deviceNum,
                'source'       => $source,
                'type'         => 1,
                'headPic'      => $configModule->getValByKeyName('user_head_pic_default', ""),
                'bgImg'        => $configModule->getValByKeyName('user_bg_pic_default', ""),
                'createDatetime' => $now,
                'updateDatetime' => $now
            );

            $this->userRegister($user_data);

            $this->saveUserStaticToRedis($user_id, array(
                'name'       => $regMobile,
                'regMobile' => $regMobile,
                'deviceNum'  => $deviceNum,
                'type'     => 1,
                'status'     => 1,
            ));

        }while(0);

        return $user_data;
    }


    /**
     * 获取用户注册信息
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function setUserToModerator($user_id, $auth_info)
    {
        $mid = 1234567;         // 主播编号

        try{
            $medoo = $this->userTable->getDb();
            $medoo->action(function($database) use(
                $user_id,
                $auth_info,
                $mid
            ) {
                $date_now = date('Y-m-d H:i:s');
                $configModule = new ConfigModule();

                // 添加主播认证信息表
                $userAuthTable = new UserAuthTable();
                $authID = $userAuthTable->genId();
                $auth_data = array(
                    'authID' => $authID,
                    'uid' => $user_id,
                    'info' => $auth_info,
                    'createDatetime' => $date_now,
                    'updateDatetime' => $date_now
                );
                $userAuthTable->insert($auth_data);

                // 添加房间信息表
                $roomTable = new RoomTable();
                $roomID = $roomTable->genId();
                $room_data = array(
                    'rid' => $roomID,
                    'uid' => $user_id,
                    'videoPlayDomain' => $configModule->getUserPlayDomain($roomID),
                    'videoPublishDomain' => $configModule->getUserpublishDomain($roomID),
                    'videoPath' => '',
                    'videoStreamName' => '',
                    'videoPlayUrl' => $configModule->getUserPlayDomain($roomID),
                    'danmuBg' => $configModule->getValByKeyName('danmu_bg_default', ''),
                    'shareTitle' => $configModule->getValByKeyName('wx_share_title', ''),
                    'shareContent' => $configModule->getValByKeyName('wx_share_content', ''),
                    'sharePic' => $configModule->getValByKeyName('wx_share_ico', ''),
                    'shareUrl' => $configModule->getValByKeyName('wx_share_url', ''),
                    'createDatetime' => $date_now,
                    'updateDatetime' => $date_now
                );
                $roomTable->insert($room_data);
                $autoID = $roomTable->getAutoIDByRoomId($roomID);

                // 更改用户类型为主播用户
                $user_data = array(
                    'type' => GlobalConfig::USER_MODER,
                    'mid' => $autoID,
                    'videoPlayUrl' => $configModule->getUserPlayDomain($roomID),
                    'rid' => $roomID,
                    'verifiedID' => $authID,
                    'verifiedInfo' => $auth_info,
                    'updateDatetime' => $date_now,
                );
                $this->userTable->updateByPk($user_data, $user_id);
            });
        }catch(\Exception $e){
            $this->log->error(sprintf(
                '%s rollBackTrans:%s',
                __FUNCTION__,
                $e->getMessage()
            ), $e);
            return false;
        }

        return true;
    }


    /**
     * 获取用户注册信息
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function getUserInfo($user_id)
    {
        $user_info = $this->userTable->getUserInfoByUserId($user_id);
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
            'verified' => isset($user_info['verified'])?$user_info['verified']:'',
            'verifyInfo' => isset($user_info['verifyInfo'])?$user_info['verified']:'',
            'flowerNumber' => $user_info['flowerNumber'],
        );

        return $ret_data;
    }


    /**
     * 获取主播信息
     * @param string $user_id 用户ID
     * @param string $status 0：所有 1：最新 2：热门 10：关注
     * @param string $tagID  标签ID
     * @return mixed
     */
    public function getModerators($req_uid = 0, $status = 0, $tagID = 0){

        $authTable = new UserAuthTable();
        $userSignTable = new ModerSignTable();
        $where_user = array(
            'type' => GlobalConfig::USER_MODER
        );
        $where_user_auth = array(
            'status' => 1
        );
        if ($req_uid != '' && $status == 10){// 0：所有 1：最新 2：热门 10：关注
            // 该用户关注的主播信息
            $user_info = $this->userTable->select(
                [
                    "[><]lz_user_attention(b)" => ['a.uid' => 'beAttentionUid']
                ],
                [
                    'AND' => ['a.type' => GlobalConfig::USER_MODER, 'b.attentionUid' => $req_uid],
                    'ORDER' => ['a.isPlaying' => 'DESC', 'b.updateDatetime' => 'DESC']
                ],
                '*'
            );
            var_dump($user_info);
        }
        elseif ($status > 0){// 0：所有 1：最新 2：热门 10：关注
            if ($status = 1){
                $user_info = $this->userTable->select(
                    [
                        "[><]lz_user_auth(b)" => ['a.uid' => 'uid']
                    ],
                    [
                        'AND' => ['a.type' => GlobalConfig::USER_MODER],
                        'ORDER' => ['a.isPlaying' => 'DESC', 'b.updateDatetime' => 'DESC']
                    ],
                    '*');
            }
            else{// ($status = 2){
                // TODO
            }
        }
        elseif($tagID > 0){
            $user_info = $this->userTable->select(
                [
                    "[><]lz_moder_sign(b)" => ['uid' => 'uid','signID' => $tagID]
                ],
                [
                    'AND' => ['a.type' => GlobalConfig::USER_MODER],
                    'ORDER' => ['a.isPlaying' => 'DESC', 'b.updateDatetime' => 'DESC']
                ],
                ['a.*']
            );
        }
        else{
            $user_info = $this->userTable->select('',
                [
                    'AND' => ['type' => GlobalConfig::USER_MODER],
                    'ORDER' => ['isPlaying' => 'DESC']
                ],
                '*'
            );
        }
        var_dump($user_info);
        //

        return $user_info;
    }


}