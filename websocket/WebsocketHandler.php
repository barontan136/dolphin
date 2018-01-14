<?php

namespace Handlers;

require_once dirname(__DIR__) . '/Bootstrap/Worker.php';

use \GatewayWorker\Lib\Gateway;
use Modules\GiftModule;
use Modules\RoomModule;
use Utils\Response;
use Utils\Logging;
use Config\ErrMessage;
use Modules\UserModule;
use Utils\Common;


class WebsocketHandler
{
    private $user = NULL;
    private $log = null;

    public function __construct()
    {
        $this->user = new UserModule();
        $this->log = Logging::getLogger();
    }

    /**
     * 进入直播间
     * @param object $oInput
     * @return mixed|string
     */
    public function login($oInput)
    {
        $user_id  = $oInput->get('uid', ''); // 用户ID
        $room_id  = $oInput->get('rid', ''); // 房间ID
        $client_id  = $oInput->get('client_id', ''); // socket connect id

        $errcode = '0';
        $response = [];
        do {
            $user_info = $this->user->getUserInfo($user_id);
            $response = [
                'uid'           => $user_info['uid'],
                'type'          => $user_info['type'],
                'nickname'      => $user_info['nickname'],
                'sex'           => $user_info['sex'],
                'headPic'       => $user_info['headPic'],
                'level'         => $user_info['level'],
                'lowkeyEnter'   => $user_info['lowkeyEnter'],
                'guardType'     => $user_info['guardType'],
                'mountld'       => 0,
            ];

            Gateway::joinGroup($client_id, $room_id);
            $_SESSION['client_name'] = $user_info['regMobile'];

        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response,
            Common::getAction(__FUNCTION__)
        );
    }


    /**
     * 发送消息
     * @param object $oInput
     * @return mixed|string
     */
    public function sendMsg($oInput)
    {
        $user_id     = $oInput->get('uid', '');           // 用户ID
        $to_user_id  = $oInput->get('toUid', '0');        // 发送消息的对象
        $msg         = $oInput->get('msg', '');         // 消息内容

        $errcode = '0';
        $response = [];
        do {

            $user_info = $this->user->getUserInfo($user_id);
            $response = [
                'fromUid'     => $user_info['uid'],
                'fromNickname'=> $user_info['nickname'],
                'fromLevel'   => $user_info['level'],
                'fromType'    => $user_info['type'],
                'toUid'       => '',
                'toNickname'  => '',
                'toLevel'     => '',
                'toType'      => '',
                'msg'         => $msg,
                'time'        => date('Y-m-d H:i:s'),
            ];
            if (!empty($to_user_id)) {
                $to_user_info = $this->user->getUserInfo($to_user_id);
                $response['toUid'] = $to_user_info['user_id'];
                $response['toNickname'] = $to_user_info['nickname'];
                $response['toLevel'] = $to_user_info['level'];
                $response['toType'] = $to_user_info['tg_type'];
            }
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response,
            Common::getAction(__FUNCTION__)
        );
    }


    /**
     * 赠送礼物
     * @param object $oInput
     * @return mixed|string
     */
    public function sendGift($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID
        $room_id  = $oInput->get('rid', '');            // 房间ID
        $p_id  = $oInput->get('pid', '');               // 礼物ID
        $p_num  = $oInput->get('num', '');              // 数量

        $errcode = '0';
        $response = [];
        do {
            $user_info = $this->user->getUserInfo($user_id);
            $giftModule = new GiftModule();
            $gift_info = $giftModule->getGiftInfoById($p_id);
            $response = [
                'fromUid'       => $user_info['uid'],
                'fromNickname'  => $user_info['nickname'],
                'fromLevel'     => $user_info['level'],
                'fromType'      => $user_info['type'],
                'fromHeadPic'   => $user_info['headPic'],
                'pid'           => $gift_info['gid'],
                'num'           => $p_num,
                'cost'          => $gift_info['price'],
                'giftPic'       => $gift_info['img'],
                'name'          => $gift_info['name'],
                'combo'         => '',
                'comboNum'      => '',
                'effect'        => $gift_info['isBonus'],
            ];
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response,
            Common::getAction(__FUNCTION__)
        );
    }

    /**
     * 主播上报直播开始
     * @param $oInput
     * @return mixed|string
     */
    public function videoPublish($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID
        $room_id  = $oInput->get('rid', '');            // 房间ID
        $autoRetry  = $oInput->get('autoRetry', '');

        $errcode = '0';
        $response = [];
        do {
            $roomModule = new RoomModule();
            $result = $roomModule->getRoomDetail($user_id, $room_id);
            $response = [
                'videoPlayUrl' => $result['videoPlayUrl']
            ];
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response,
            Common::getAction(__FUNCTION__)
        );
    }

    /**
     * 上报直播结束
     * @param $oInput
     * @return mixed|string
     */
    public function videoUnpublish($oInput)
    {
        $errcode = '0';
        $response = [];
        do {
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response,
            Common::getAction(__FUNCTION__)
        );
    }

    /**
     * 用户关注主播上报
     * @param $oInput
     * @return mixed|string
     */
    public function userAttention($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID
        $room_id  = $oInput->get('rid', '');            // 房间ID

        $errcode = '0';
        $response = [];
        do {
            $user_info = $this->user->getUserInfo($user_id);
            $response = [
                'uid'      => $user_info['uid'],
                'nickname' => $user_info['nickname'],
                'level'    => $user_info['level'],
                'type'     => $user_info['type'],
            ];
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response,
            Common::getAction(__FUNCTION__)
        );
    }
}
