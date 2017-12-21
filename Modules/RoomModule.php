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
        $txTime = date("Y-m-d H:i:s", strtotime("+1 day"));
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
                'trueName' => isset($userInfo['realName']) ?? '',
                'nickname' => isset($userInfo['nickname']) ?? '',
                'id' => isset($userInfo['mid']) ?? '',
                'moderatorLevel' => isset($userInfo['moderatorLevel']) ?? '',
                'nextLevelNeed' => isset($userInfo['moderatorNextLevelNeedCoin']) ?? '',
                'levelEarnCoin' => isset($userInfo['moderatorLevelCoin']) ?? '',
                'headPic' => isset($userInfo['headPic']) ?? '',
                'verified' => isset($userInfo['verified']) ? $userInfo['verified'] : '',
                'verifyInfo' => isset($userInfo['verifyInfo']) ? $userInfo['verifyInfo'] : '',
                'earnCoin' => 0,
            );

            $result = array(
                'rid' => isset($roomInfo['rid']) ?? '',
                'autoID' => isset($roomInfo['autoID']) ?? '',
                'msgIP' => isset($roomInfo['msgIP']) ?? '',
                'msgPort' => isset($roomInfo['msgPort']) ?? '',
                'videoPlayDomain' => isset($roomInfo['videoPlayDomain']) ?? '',
                'videoPublishDomain' => isset($roomInfo['videoPublishDomain']) ?? '',
                'videoPath' => isset($roomInfo['videoPath']) ?? '',
                'videoStreamName' => Common::get_play_url(Rtmp::$tx_biz_id, $roomInfo['rid']),
                'videoPlayUrl' => Common::get_play_url(Rtmp::$tx_biz_id, $roomInfo['rid']),
                'flowerNumber' => isset($roomInfo['flowerNumber']) ?? '',
                'isPlaying' => isset($roomInfo['isPlaying']) ?? '',
                'onlineNum' => isset($roomInfo['onlineNum']) ?? '',
                'danmuBg' => isset($roomInfo['danmuBg']) ?? '',
                'shareTitle' => isset($roomInfo['shareTitle']) ?? '',
                'shareContent' => isset($roomInfo['shareContent']) ?? '',
                'sharePic' => isset($roomInfo['sharePic']) ?? '',
                'shareUrl' => isset($roomInfo['shareUrl']) ?? '',
                'private' => isset($roomInfo['private']) ?? '',
                'messages' => '',
                'userType' => isset($userInfo['type']) ?? '',
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

}