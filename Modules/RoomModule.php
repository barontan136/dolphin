<?php
namespace Modules;

use Config\GlobalConfig;
use Config\Rtmp;
use Tables\Record\GagLogTable;
use Tables\Room\GiftTable;
use Tables\Room\RoomAdminTable;
use Tables\Room\RoomTable;
use Tables\User\UserAttentionTable;
use Tables\User\UserGagTable;
use Utils\Logging;
use Tables\User\UserTable;
use Tables\User\Cache\UserRedisTable;
use Utils\Common;
use \GatewayWorker\Lib\Gateway;


class RoomModule
{
    private $log = null;
    private $userTable = null;
    private $roomTable = null;
    private $userCacheTable = null;

    public function __construct()
    {
        $this->userTable = new UserTable();
        $this->userCacheTable = new UserRedisTable();
        $this->roomTable = new RoomTable();
        $this->log = Logging::getLogger();
    }


    /**
     * 获取直播推流地址
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function getLiveAddress($rid)
    {

        $roomTable = new RoomTable();
        $roomInfo = $roomTable->getRoomInfo(['rid' => $rid]);

        if (!isset($roomInfo['videoPublishDomain']) || $roomInfo['videoPublishDomain'] == '') {
            return '';
        }
        $txTime = date("Y-m-d") . " 23:59:59";
        $path = Common::get_push_url(Rtmp::$tx_biz_id, $rid, Rtmp::$tx_push_key, $txTime);
//
//        // 获取鉴权后的推送URL
//        $path = $roomInfo['videoPublishDomain'] . Common::get_pulish_auth_key($rid);
        return $path;
    }

    /**
     * 获取主播房间详细信息
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function getRoomDetail($user_id, $rid)
    {
        try {

            // 获取房间信息
            $roomTable = new RoomTable();
            $roomInfo = $roomTable->getRoomInfo(['rid' => $rid]);
            if (empty($roomInfo)) return false;

            // 获取礼物列表
            $giftTable = new GiftTable();
            $giftList = $giftTable->getGiftList();
            // 获取用户关注关系
            $userAttention = new UserAttentionTable();
            $love = $userAttention->checkAttUsers($user_id, $roomInfo['uid']);
            // 获取主播管理员列表ID
            $roomAdminTable = new RoomAdminTable();
            $adminUids = $roomAdminTable->getRoomAdminIdsByID($roomInfo['rid']);
            // 获取主播详细信息
            $userInfo = $this->userTable->getUserInfoByUserId($roomInfo['uid']);
            $moderator = array(
                'weight' => 0,
                'height' => 0,
                'age' => 0,
                'trueName' => $userInfo['realName']?? '',
                'nickname' => $userInfo['nickname'] ?? '',
                'id' => $userInfo['mid'] ?? '',
                'moderatorLevel' => $userInfo['moderatorLevel'] ?? '',
                'nextLevelNeed' => $userInfo['moderatorNextLevelNeedCoin'] ?? '',
                'levelEarnCoin' => $userInfo['moderatorLevelCoin'] ?? '',
                'headPic' => $userInfo['headPic'] ?? '',
                'verified' => $userInfo['verified'] ? $userInfo['verified'] : '',
                'verifyInfo' => $userInfo['verifyInfo'] ? $userInfo['verifyInfo'] : '',
                'earnCoin' => 0,
            );

            $result = array(
                'rid' => $roomInfo['rid']?? '',
                'autoID' => $roomInfo['autoID'] ?? '',
                'msgIP' => $roomInfo['msgIP'] ?? '',
                'msgPort' => $roomInfo['msgPort'] ?? '',
                'videoPlayDomain' => $roomInfo['videoPlayDomain'] ?? '',
                'videoPublishDomain' => $roomInfo['videoPublishDomain'] ?? '',
                'videoPath' => $roomInfo['videoPath'] ?? '',
                'videoStreamName' => Common::get_play_url(Rtmp::$tx_biz_id, $roomInfo['rid']),
                'videoPlayUrl' => Common::get_play_url(Rtmp::$tx_biz_id, $roomInfo['rid']),
                'flowerNumber' => $roomInfo['flowerNumber'] ?? '',
                'isPlaying' => $roomInfo['isPlaying'] ?? '',
                'onlineNum' => $roomInfo['onlineNum'] ?? '',
                'danmuBg' => $roomInfo['danmuBg'] ?? '',
                'shareTitle' => $roomInfo['shareTitle'] ?? '',
                'shareContent' => $roomInfo['shareContent'] ?? '',
                'sharePic' => $roomInfo['sharePic'] ?? '',
                'shareUrl' => $roomInfo['shareUrl'] ?? '',
                'private' => $roomInfo['private'] ?? '',
                'messages' => '',
                'userType' => $userInfo['type'] ?? '',
                //
                'isGuard' => 0,
                'loved' => $love ? 'true' : 'false',
                'adminUids' => $adminUids,
                'moderator' => $moderator,
                'gifts' => $giftList,
            );
        } catch (\Exception $e) {
            $this->log->error(sprintf("getRoomDetail:%s", $e->getMessage()));
        }
        return $result;
    }

    /**
     * @param $rid
     * @return mixed
     */
    public function getRoomInfo($rid)
    {
        $roomTable = new RoomTable();
        $result = $roomTable->getRoomInfo(['rid' => $rid]);
        if (empty($result)) {
            return [];
        }

        return $result;
    }

    /**
     * @param $operate_id
     * @param $setUid
     * @return array
     * @throws RoomException
     */
    public function setAdmin($operate_id, $setUid, $room_id)
    {
        $error_code = '';
        $response = [];
        do {
            $userModule = new UserModule();
            $operate_info = $userModule->getUserInfo($operate_id);

            $set_info = $userModule->getUserInfo($setUid);
            $roomAdminTable = new RoomAdminTable();
            $result = $roomAdminTable->getRoomAdminByRidAndUid(
                $room_id,
                $setUid
            );
            if (!empty($result)) {
                $nowtime = date('Y-m-d H:i:s');
                $data = [
                    'adminID' => $roomAdminTable->genId(),
                    'uid' => $setUid,
                    'rid' => $room_id,
                    'operateUid' => $operate_info['uid'],
                    'operateNickname' => $operate_info['nickname'],
                    'status' => 1,
                    'create_datetime' => $nowtime,
                    'update_datetime' => $nowtime,
                ];
                $roomAdminTable->insert($data);
            } else {
                $affect_row = $roomAdminTable->setRoomAdmin(
                    $operate_id,
                    $setUid,
                    $room_id
                );
                if (!$affect_row) {
                    $error_code = '997005';
                    break;
                }
            }

            $response = [
                "operatorUid" => $operate_id,
                "operatorNickname" => $operate_info['nickname'],
                "setAdminUid" => $setUid,
                "setAdminNickname" => $set_info['nickname'],
            ];
        } while (false);

        if ($error_code) {
            throw new RoomException($error_code);
        }

        return $response;
    }

    /**
     * @param $operate_id
     * @param $setUid
     * @return array
     * @throws RoomException
     */
    public function unsetAdmin($operate_id, $setUid, $room_id)
    {
        $error_code = '';
        $response = [];
        do {
            $userModule = new UserModule();
            $operate_info = $userModule->getUserInfo($operate_id);

            $set_info = $userModule->getUserInfo($setUid);
            $roomAdminTable = new RoomAdminTable();
            $affect_row = $roomAdminTable->unsetRoomAdmin(
                $operate_id,
                $setUid,
                $room_id
            );
            if (!$affect_row) {
                $error_code = '997006';
                break;
            }

            $response = [
                "operatorUid" => $operate_id,
                "operatorNickname" => $operate_info['nickname'],
                "setAdminUid" => $setUid,
                "setAdminNickname" => $set_info['nickname'],
            ];
        } while (false);

        if ($error_code) {
            throw new RoomException($error_code);
        }

        return $response;
    }

    /**
     * @param $op_user_id
     * @param $gag_uid
     * @param $expires
     * @return array
     * @throws RoomException
     */
    public function gagUser($op_user_id, $gag_uid, $expires, $room_id)
    {
        $error_code = '';
        $response = [];
        do {
            $userModule = new UserModule();
            $op_user_info = $userModule->getUserInfo($op_user_id);
            if (empty($op_user_info)) {
                $error_code = '998003';
                break;
            }

            $userGagTable = new UserGagTable();
            $medoo = $userGagTable->getDb();
            $medoo->action(function ($database) use (
                $userGagTable,
                $expires,
                $gag_uid,
                $op_user_id,
                $userModule,
                $op_user_info,
                $room_id
            ) {
                $gag_end_time = time() + $expires * 60;
                $now_time = date('Y-m-d H:i:s');
                $data = [
                    'gagID' => $userGagTable->genId(),
                    'uid' => $gag_uid,
                    'startTime' => time(),
                    'expires' => $expires,
                    'endTime' => $gag_end_time,
                    'roomID' => $room_id,
                    'status' => GlobalConfig::GAG_ING,
                    'operateUid' => $op_user_id,
                    'operateNickName' => $op_user_info['nickname'],
                    'createDatetime' => $now_time,
                    'updateDatetime' => $now_time,
                ];
                $userGagTable->insert($data);

                $gag_user_info = $userModule->getUserInfo($gag_uid);
                $gagLogTable = new GagLogTable();
                $data = [
                    'logID' => $gagLogTable->genId(),
                    'uid' => $gag_uid,
                    'nickname' => $gag_user_info['nickname'],
                    'operateType' => GlobalConfig::GAG_ING,
                    'operateUid' => $op_user_id,
                    'createDatetime' => $now_time,
                    'updateDatetime' => $now_time,
                ];
                $gagLogTable->insert($data);
            });
        } while (false);

        if ($error_code) {
            throw new RoomException($error_code);
        }

        return $response;
    }

    /**
     * @param $op_user_id
     * @param $gag_uid
     * @param $room_id
     * @return array
     * @throws RoomException
     */
    public function ungagUser($op_user_id, $gag_uid, $room_id)
    {
        $error_code = '';
        $response = [];
        do {
            $userModule = new UserModule();
            $op_user_info = $userModule->getUserInfo($op_user_id);
            if (empty($op_user_info)) {
                $error_code = '998003';
                break;
            }
            $gag_user_info = $userModule->getUserInfo($gag_uid);

            $userGagTable = new UserGagTable();
            $medoo = $userGagTable->getDb();
            $medoo->action(function ($database) use (
                $userGagTable,
                $gag_uid,
                $op_user_id,
                $userModule,
                $op_user_info,
                $room_id,
                $gag_user_info
            ) {
                $affect_row = $userGagTable->updateGagStatusByAdmin(
                    $room_id,
                    $gag_uid,
                    $op_user_id,
                    $op_user_info['nickname']
                );
                if (!$affect_row) {
                    throw new \Exception('updateGagStatusByAdmin failed');
                }

                $now_time = date('Y-m-d H:i:s');
                $gagLogTable = new GagLogTable();
                $data = [
                    'logID' => $gagLogTable->genId(),
                    'uid' => $gag_uid,
                    'nickname' => $gag_user_info['nickname'],
                    'operateType' => GlobalConfig::GAG_ADMIN_CANCEL,
                    'operateUid' => $op_user_id,
                    'createDatetime' => $now_time,
                    'updateDatetime' => $now_time,
                ];
                $gagLogTable->insert($data);
            });
            $response = [
                'operatorUid' => $op_user_id,
                'operatorNickname' => $op_user_info['nickname'],
                'gagUid' => $gag_uid,
                'gagNickname' => $gag_user_info['nickname']
            ];
        } while (false);

        if ($error_code) {
            throw new RoomException($error_code);
        }

        return $response;
    }

    /**
     * @param $user_id
     * @param $room_id
     * @return array
     * @throws RoomException
     */
    public function videoPublish($user_id, $room_id)
    {
        $error_code = '';
        $response = [];
        do {
            $roomModule = new RoomModule();
            $result = $roomModule->getRoomInfo($room_id);
            if (empty($result)) {
                $error_code = '997001';
                break;
            }
            $userTable = new UserTable();
            $medoo = $userTable->getDb();
            $medoo->action(function ($database) use (
                $user_id,
                $userTable,
                $room_id
            ){
                $data = [
                    'isPlaying' => 1
                ];
                $affect_row = $userTable->updateByPk($data, $user_id);
                if (!$affect_row) {
                    throw new \Exception('update user table failed');
                }

                $roomTable = new RoomTable();
                $affect_row = $roomTable->updateByPk($data, $room_id);
                if (!$affect_row) {
                    throw new \Exception('update room table failed');
                }
            });
            $response = [
                'videoPlayUrl' => $result['videoPlayUrl']
            ];
        } while (false);

        if ($error_code) {
            throw new RoomException($error_code);
        }

        return $response;
    }

    /**
     * @param $user_id
     * @param $room_id
     * @return array
     * @throws RoomException
     */
    public function videoUnpublish($user_id, $room_id)
    {
        $error_code = '';
        $response = [];
        do {
            $roomModule = new RoomModule();
            $result = $roomModule->getRoomInfo($room_id);
            if (empty($result)) {
                $error_code = '997001';
                break;
            }
            $userTable = new UserTable();
            $medoo = $userTable->getDb();
            $medoo->action(function ($database) use (
                $user_id,
                $userTable,
                $room_id
            ){
                $data = [
                    'isPlaying' => 0
                ];
                $affect_row = $userTable->updateByPk($data, $user_id);
                if (!$affect_row) {
                    throw new \Exception('update user table failed');
                }

                $roomTable = new RoomTable();
                $affect_row = $roomTable->updateByPk($data, $room_id);
                if (!$affect_row) {
                    throw new \Exception('update room table failed');
                }
            });
            $response = [
                'videoPlayUrl' => $result['videoPlayUrl']
            ];
        } while (false);

        if ($error_code) {
            throw new RoomException($error_code);
        }

        return $response;
    }

    public function loginRoom($user_id, $client_id, $room_id)
    {
        $error_code = '';
        $response = [];
        do {

            $user_info = $this->user->getUserInfo($user_id);

            $data = [
                'onlineNum[+]'    => 1,
                'updateDatetime'  => date('Y-m-d H:i:s')
            ];
            $roomTable = new RoomTable();
            $roomTable->updateByPk($data, $room_id);

            Gateway::joinGroup($client_id, $room_id);
            $_SESSION['client_name'] = $user_info['regMobile'];

            $response = [
                'uid' => $user_info['uid'],
                'type' => $user_info['type'],
                'nickname' => $user_info['nickname'],
                'sex' => $user_info['sex'],
                'headPic' => $user_info['headPic'],
                'level' => $user_info['level'],
                'lowkeyEnter' => $user_info['lowkeyEnter'],
                'guardType' => $user_info['guardType'],
                'mountld' => 0,
            ];
        } while (false);

        if ($error_code) {
            throw new RoomException($error_code);
        }

        return $response;
    }
}