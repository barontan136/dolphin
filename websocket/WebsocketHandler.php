<?php

namespace Handlers;

require_once dirname(__DIR__) . '/Bootstrap/Worker.php';

use \GatewayWorker\Lib\Gateway;
use Modules\GiftModule;
use Modules\UserAssetModule;
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
            $_SESSION['room_id'] = $room_id;
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
        $room_id     = $oInput->get('rid', '');           // 房间ID
        $to_user_id  = $oInput->get('toUid ', '');        // 发送消息的对象
        $msg         = $oInput->get('msg  ', '');         // 消息内容

        $errcode = '0';
        $response = [];
        do {

            // 登陆时保存的room_id
            $back_room_id = $_SESSION['room_id'];
            if ($back_room_id != $room_id){
                var_dump('room is not same, check it');
                $errcode = '997001';
                break;
            }

            $user_info = $this->user->getUserInfo($user_id);
            $to_user_info = $this->user->getUserInfo($to_user_id);
            $response = [
                'fromUid'     => $user_info['uid'],
                'fromNickname'=> $user_info['nickname'],
                'fromLevel'   => $user_info['level'],
                'fromType'    => $user_info['type'],
                'toUid'       => $to_user_info['user_id'],
                'toNickname'  => $to_user_info['nickname'],
                'toLevel'     => $to_user_info['level'],
                'toType'      => $to_user_info['tg_type'],
                'msg'         => $msg,
                'time'        => date('Y-m-d H:i:s'),
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



        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response,
            Common::getAction(__FUNCTION__)
        );
    }


}
