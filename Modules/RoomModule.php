<?php
namespace Modules;

use Config\Rtmp;
use Tables\Room\GiftTable;
use Tables\Room\RoomAdminTable;
use Tables\Room\RoomTable;
use Tables\User\UserAttentionTable;
use Utils\Logging;
use Tables\User\UserTable;
use Tables\User\Cache\UserRedisTable;
use Utils\Common;
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
    public function getLiveAddress($rid){

        $roomTable = new RoomTable();
        $roomInfo = $roomTable->getRoomInfo(['rid' => $rid]);

        if (!isset($roomInfo['videoPublishDomain']) || $roomInfo['videoPublishDomain'] == ''){
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
                'headPic' => userInfo['headPic'] ?? '',
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
        }catch (\Exception $e){
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
                    'adminID'         => $roomAdminTable->genId(),
                    'uid'             => $setUid,
                    'rid'             => $room_id,
                    'operateUid'      => $operate_info['uid'],
                    'operateNickname' => $operate_info['nickname'],
                    'status'          => 1,
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
                "operatorUid"        => $operate_id,
                "operatorNickname"   => $operate_info['nickname'],
                "setAdminUid"        => $setUid,
                "setAdminNickname"   => $set_info['nickname'],
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
                "operatorUid"        => $operate_id,
                "operatorNickname"   => $operate_info['nickname'],
                "setAdminUid"        => $setUid,
                "setAdminNickname"   => $set_info['nickname'],
            ];
        } while (false);

        if ($error_code) {
            throw new RoomException($error_code);
        }

        return $response;
    }

    public function gagUser($gag_uid)
    {

    }
}