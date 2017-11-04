<?php
namespace Modules;

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

        return isset($roomInfo['videoPublishDomain']) ? $roomInfo['videoPublishDomain'] : '';
    }

    /**
     * 获取主播房间详细信息
     * @param string $user_id
     * @param string | array $fields
     * @return mixed
     */
    public function getRoomDetail($user_id, $rid)
    {
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
            'weight'            => 0,
            'height'            => 0,
            'age'               => 0,
            'trueName'          => $userInfo['realName'],
            'nickname'          => $userInfo['nickname'],
            'id'                => $userInfo['mid'],
            'moderatorLevel'    => $userInfo['moderatorLevel'],
            'nextLevelNeed'     => $userInfo['moderatorNextLevelNeedCoin'],
            'levelEarnCoin'     => $userInfo['moderatorLevelCoin'],
            'headPic'           => $userInfo['headPic'],
            'verified'          => isset($userInfo['verified']) ? $userInfo['verified'] : '',
            'verifyInfo'        => isset($userInfo['verifyInfo']) ? $userInfo['verifyInfo'] : '',
            'earnCoin'          => 0,
        );

        $result = array(
            'rid'               => $roomInfo['rid'],
            'autoID'            => $roomInfo['autoID'],
            'msgIP'             => $roomInfo['msgIP'],
            'msgPort'           => $roomInfo['msgPort'],
            'videoPlayDomain'   => $roomInfo['videoPlayDomain'],
            'videoPublishDomain'=> $roomInfo['videoPublishDomain'],
            'videoPath'         => $roomInfo['videoPath'],
            'videoStreamName'   => $roomInfo['videoStreamName'],
            'videoPlayUrl'      => $roomInfo['videoPlayUrl'],
            'flowerNumber'      => $roomInfo['flowerNumber'],
            'isPlaying'         => $roomInfo['isPlaying'],
            'onlineNum'         => $roomInfo['onlineNum'],
            'danmuBg'           => $roomInfo['danmuBg'],
            'shareTitle'        => $roomInfo['shareTitle'],
            'shareContent'      => $roomInfo['shareContent'],
            'sharePic'          => $roomInfo['sharePic'],
            'shareUrl'          => $roomInfo['shareUrl'],
            'private'           => $roomInfo['private'],
            'messages'          => '',
            'userType'          => $userInfo['type'],
            //
            'isGuard'           => 0,
            'love'              => $love,
            'adminUids'         => $adminUids,
            'moderator'         => $moderator,
            'gifts'             => $giftList,
        );
        return $result;
    }

}