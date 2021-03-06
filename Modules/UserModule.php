<?php
namespace Modules;

use Tables\Room\RoomTable;
use Tables\User\ModerSignTable;
use Tables\User\SignTypeTable;
use Tables\User\UserAssetTable;
use Tables\User\UserAttentionTable;
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
    private $userAssets = null;

    public function __construct()
    {
        $this->userTable = new UserTable();
        $this->userCacheTable = new UserRedisTable();
        $this->log = Logging::getLogger();
        $this->signType = new SignTypeTable();
        $this->userAssets = new UserAssetTable();
    }


    /**
     * 短信验证码通过后,缓存用户注册信息
     * @param string $reg_mobile
     * @param string $dev_num
     * @return mixed
     */
    public function setReadyUserData($reg_mobile, $dev_num){
        // TODO
//        $user_id = '55c08ce07a88e59eaead9a009f9999';//$this->userTable->genId();
        $user_id = $this->userTable->genId();
        $user_data = array(
            'user_id' => $user_id,
            'name' => $reg_mobile,
            'regMobile' => $reg_mobile,
            'deviceNum' => $dev_num
        );
        $this->userCacheTable->setUserReadyDataByUserId($user_id, $user_data);
        return $user_id;
    }

    /**
     * 获取用户预注册的缓存信息
     * @param string $user_id
     * @return mixed
     */
    public function getReadyUserData($user_id){

        return $this->userCacheTable->getUserReadyDataByUserId($user_id);
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
    public function login()
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
     * 创建用户资产纪录
     * @param $user_id
     * @param $nickname
     * @return
     */
    public function createUserAssetes($user_id, $nickname){
        $now_time = date('Y-m-d H:i:s');
        $data = array(
            'uid'             => $user_id,
            'nickname'        => $nickname,
            'createDatetime'  => $now_time,
            'updateDatetime'  => $now_time,
        );
        $this->userAssets->insert($data);
    }


    /**
     * 根据手机号码获取用户ID
     * @param string $reg_mobile
     * @return mixed
     */
    public function getUserIdByRegMobile($reg_mobile)
    {
        return $this->userTable->getUserInfoByMoible($reg_mobile, 'uid');
//        return $this->userCacheTable->getUserIdByUserName($reg_mobile);
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
     * 根据user_id获取用户静态信息
     * @param $user_id
     * @return mixed
     */
    public function getUserStaticByUserId($user_id)
    {
        return $this->userCacheTable->getUserStaticCacheByUserId($user_id);
    }

    /**
     * 根据user_id获取用户动态信息
     * @param string $user_id
     * @return bool
     */
    public function getUserDynamicByUserId($user_id)
    {
        return $this->userCacheTable->getUserDynamicCacheByUserId($user_id);
    }

    /**
     * 获取用户动态信息字段及其值
     * @param string $user_id
     * @param string $field
     * @return bool
     */
    public function getUserDynamicFieldByUserId($user_id, $field)
    {
        return $this->userCacheTable->getUserDynamicFieldByUserId($user_id, $field);
    }

    /**
     * 删除用户动态信息字段及其值
     * @param string $user_id
     * @param string $field
     * @return bool
     */
    public function delUserDynamicFieldByUserId($user_id, $field)
    {
        return $this->userCacheTable->delUserDynamicFieldByUserId($user_id, $field);
    }

    /**
     * 获取用户静态信息中的字段值
     * @param string $user_id
     * @param string $field
     * @return bool
     */
    public function getUserStaticFieldByUserId($user_id, $field)
    {
        return $this->userCacheTable->getUserStaticFieldByUserId($user_id, $field);
    }

    /**
     * 更新用户静态信息中的字段值
     * @param string $user_id
     * @param string $field
     * @param string $val
     * @return bool
     */
    public function updateUserStaticFieldByUserId($user_id, $field, $val)
    {
        return $this->userCacheTable->setUserStaticFieldByUserId($user_id, $field, $val);
    }


    /**
     * 获取用户注册信息
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function registerByMobile(
        $nickname,
        $regMobile,
        $source,
        $deviceNum,
        $password,
        $sex,
        $user_id = ''
    )
    {
        do{
            $configModule = new ConfigModule();
            $now = date('Y-m-d H:i:s');
            if (empty($user_id) || $user_id == ''){
                $user_id = $this->userTable->genId();
            }
            $salt = '1234';
            //登录密码根据加密规则加密，存储数据库的是加密后的密文
            $pwd_md5 = md5($password . $salt);
            $user_data = array(
                'uid'          => $user_id,
                'userName'     => $regMobile,
                'nickname'     => $nickname,
                'sex'          => $sex,
                'regMobile'    => $regMobile,
                'regTime'      => $now,
                'password'     => $pwd_md5,
                'salt'         => $salt,
                'deviceNum'    => $deviceNum,
                'source'       => $source,
                'type'         => 1,
                'headPic'      => $configModule->getValByKeyName('user_head_pic_default', ""),
                'bgImg'        => $configModule->getValByKeyName('user_bg_pic_default', ""),
                'signature'   =>  $configModule->getValByKeyName('signature_default', ""),
                'createDatetime' => $now,
                'updateDatetime' => $now
            );

            $this->userRegister($user_data);

            $this->saveUserStaticToRedis($user_id, array(
                'name'       => $regMobile,
                'regMobile' => $regMobile,
                'deviceNum'  => $deviceNum,
                'login_pwd'  => $pwd_md5,
                'type'     => 1,
                'status'     => 1,
            ));

            // 创建用户资产纪录
            $this->createUserAssetes($user_id, $nickname);

        }while(0);

        return $user_data;
    }


    /**
     * 获取用户注册信息
     * @param string $user_id
     * @param string | array $fields
     * @param string $mid 主播编号
     * @return mixed
     */
    public function setUserToModerator($user_id, $auth_info, $mid = '')
    {
        $data = array();
        try{
            $medoo = $this->userTable->getDb();
            $medoo->action(function($database) use(
                $user_id,
                $auth_info,
                $mid,
                &$data
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
                    'msgIP' => $configModule->getValByKeyName('websocket_ip', 'ws://api.szxiawa.com'),
                    'msgPort' => $configModule->getValByKeyName('websocket_port', 'ws://api.szxiawa.com'),
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

                $data = array(
                    'rid' => $roomID,
                );
            });
        }catch(\Exception $e){
            $this->log->error(sprintf(
                '%s rollBackTrans:%s',
                __FUNCTION__,
                $e->getMessage()
            ), $e);
            return false;
        }

        return $data;
    }

    /**
     * 获取用户个人信息
     * @param string $user_id
     * @return mixed
     */
    public function getMyInfo($user_id){

        $user_info = $this->userTable->getUserInfoByUserId($user_id);
        if (empty($user_info)){
            return null;
        }
        $ret_data = array(
            'uid' => $user_info['uid'],
            'nickname' => $user_info['nickname'],
            'headPic' => $user_info['headPic'],
            'signature' => $user_info['signature'],
            'level' => $user_info['userLevel'],
            'rid' => $user_info['rid'],
            'verifiedID' => isset($user_info['verifiedID'])?$user_info['verifiedID']:'',
            'verifyInfo' => isset($user_info['verifyInfo'])?$user_info['verifyInfo']:'',
            'sex' => $user_info['sex'],
            'mobile' => $user_info['regMobile'],
            'attentionNum' => $user_info['attentionNum'],
            'isAttention' => $user_info['attentionNum']??1,
            'fansNum' => $user_info['fansNum'],
            'type' => $user_info['type'],
            'birthday' => $user_info['birthday'],
            'isFirstLogin' => $user_info['lastLogin'] ? 1 : 0,
            'coin' => 0,                                    // 充值金币
            'levelCoin' => $user_info['levelCoin'],       // 当前等级进度
            'nextLevel' => $user_info['nextLevel'],
            'nextLevelNeedCoin' => $user_info['nextLevelNeedCoin'],
            //
            'moderatorLevel' => $user_info['moderatorLevel'],
            'moderatorLevelName' => $user_info['moderatorLevelName'],
            'userLevelName' => $user_info['userLevelName'],
            'isPlaying'    => $user_info['isPlaying'],
            'flowerNumber' => $user_info['flowerNumber'],
            'incomeAvailable' => 0,                        // 收入
        );

        // 获取用户资产
        $userAssteTable = new UserAssetTable();
        $user_asste = $userAssteTable->getUserByUserId($user_id);
        $ret_data['coin'] = $user_asste['amount']??0;
        $ret_data['incomeAvailable'] = $user_asste['starAmount']??0;
        return $ret_data;
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
        if (empty($user_info)){
            return null;
        }
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
            'isPlaying'    => $user_info['isPlaying'],
            'videoPlayUrl' => $user_info['videoPlayUrl'],
            'rid' => $user_info['rid'],
            'verifiedID' => isset($user_info['verifiedID'])?$user_info['verifiedID']:'',
            'verifyInfo' => isset($user_info['verifyInfo'])?$user_info['verifyInfo']:'',
            'flowerNumber' => $user_info['flowerNumber'],
            'guardType'    => $user_info['guardType'],
            'lowkeyEnter'  => $user_info['lowkeyEnter'],
            'regMobile'    => $user_info['regMobile'],
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

        if ($req_uid != '' && $status == 10){// 0：所有 1：最新 2：热门 10：关注
            // 该用户关注的主播信息
            $user_list = $this->userTable->select(
                [
                    "[><]lz_user_attention(b)" => ['a.uid' => 'beAttentionUid']
                ],
                [
                    'AND' => ['a.type' => GlobalConfig::USER_MODER, 'b.attentionUid' => $req_uid],
                    'ORDER' => ['a.isPlaying' => 'DESC', 'b.updateDatetime' => 'DESC']
                ],
                '*'
            );
        }
        elseif ($status > 0){// 0：所有 1：最新 2：热门 10：关注
            if ($status = 1){
                $user_list = $this->userTable->select(
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
            $user_list = $this->userTable->select(
                [
                    "[><]lz_moder_sign(b)" => ['a.uid' => 'uid']
                ],
                [
                    'AND' => ['a.type' => GlobalConfig::USER_MODER, 'b.signID' => $tagID],
                    'ORDER' => ['a.isPlaying' => 'DESC', 'b.updateDatetime' => 'DESC']
                ],
                '*'
            );
        }
        else{
            $user_list = $this->userTable->select('',
                [
                    'AND' => ['type' => GlobalConfig::USER_MODER],
                    'ORDER' => ['isPlaying' => 'DESC']
                ],
                '*'
            );
        }
        //
        $roomTable = new RoomTable();
        $result = array();
        foreach($user_list as $info) {
            $item['rid'] = $info['rid'];
            $item['sex'] = $info['sex'];
            $item['mid'] = $info['mid'];
            $item['nickname'] = $info['nickname'];
            $item['headPic'] = $info['headPic'];
            $item['isPlaying'] = $info['isPlaying'];
//            $item['playStartTime']  = $info['rid'];
//            $item['onlineNum']      = $info['onlineNum'];
            $item['fansNum'] = $info['fansNum'];
            $item['moderatorLevel'] = $info['moderatorLevel'];
            $item['verified'] = isset($info['verified']) ? $info['verified'] : '';
            $item['verifyInfo'] = isset($info['verifyInfo']) ? $info['verified'] : '';
            $item['videoPlayUrl'] = $info['videoPlayUrl'];
            $item['city'] = '外星人';

            if (isset($item['rid']) && !empty($item['rid'])) {

                $roomInfo = $roomTable->getRoomInfo(['rid'=>$item['rid']]);
                $item['onlineNum'] = intval($roomInfo['onlineNum']);
                $item['announcement'] = $roomInfo['roomTitle'];
                $item['playStartTime'] = !empty($roomInfo['lastStartTime']) ? strtotime($roomInfo['lastStartTime']) : time();
            }

            array_push($result, $item);
            //$result = array_merge($result, $item);
        }

        return $result;
    }

    /**
     * @param $user_id
     * @param $room_id
     * @return array
     * @throws UserException
     */
    public function attentionUser($user_id, $room_id)
    {
        $error_code = '';
        $response = [];
        do {
            $user_info = $this->getUserInfo($user_id);
            if (empty($user_info)) {
                $error_code = '997002';
                break;
            }

            $roomModule = new RoomModule();
            $room_info = $roomModule->getRoomInfo($room_id);
            if (empty($room_info)) {
                $error_code = '997001';
                break;
            }

            $userAttentionTable = new UserAttentionTable();
            $userAttention = $userAttentionTable->getAttBetweenUsers(
                $user_id,
                $room_info['uid'],
                null
            );
            if (empty($userAttention)) {
                $nowTime = date('Y-m-d H:i:s');
                $data = [
                    'atID'            => $userAttentionTable->genId(),
                    'beAttentionUid'  => $room_info['uid'],
                    'beNickname'      => $room_info['nickname'],
                    'attentionUid'    => $user_id,
                    'Nickname'        => $user_info['nickname'],
                    'status'          => 1,
                    'create_datetime' => $nowTime,
                    'update_datetime' => $nowTime,
                ];
                $userAttentionTable->insert($data);
            } else {
                $affect_row = $userAttentionTable->attentionUser($user_id, $room_info['uid']);
                if (!$affect_row) {
                    $error_code = '997004';
                    break;
                }
            }

            $response = [
                'uid'      => $user_info['uid'],
                'nickname' => $user_info['nickname'],
                'level'    => $user_info['level'],
                'type'     => $user_info['type'],
            ];
        } while (false);

        if ($error_code) {
            throw new UserException($error_code);
        }

        return $response;
    }


    /**
     * @param $user_id
     * @param $room_id
     * @return array
     * @throws UserException
     */
    public function unAttentionUser($user_id, $room_id)
    {
        $error_code = '';
        $response = [];
        do {
            $user_info = $this->getUserInfo($user_id);
            if (empty($user_info)) {
                $error_code = '997002';
                break;
            }

            $roomModule = new RoomModule();
            $room_info = $roomModule->getRoomInfo($room_id);
            if (empty($room_info)) {
                $error_code = '997001';
                break;
            }

            $userAttentionTable = new UserAttentionTable();
            $affect_row = $userAttentionTable->unAttention(
                $user_id,
                $room_info['uid']
            );
            if (!$affect_row) {
                $error_code = '997003';
                break;
            }
            $response = [
                'uid'      => $user_info['uid'],
                'nickname' => $user_info['nickname'],
                'level'    => $user_info['level'],
                'type'     => $user_info['type'],
            ];
        } while (false);

        if ($error_code) {
            throw new UserException($error_code);
        }

        return $response;
    }
}
