<?php

namespace Handlers;

require_once dirname(__DIR__) . '/Bootstrap/Worker.php';

use \GatewayWorker\Lib\Gateway;
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
     * 用户登录
     * @param object $oInput
     * @return mixed|string
     */
    public function login($oInput)
    {
        $user_id  = $oInput->get('uid', '1'); // 用户ID
        $room_id  = $oInput->get('rid', '1'); // 房间ID
        $client_id  = $oInput->get('client_id', '1'); // socket connect id

        $errcode = '0';
        $response = [];
        do {
            $user_info = $this->user->getUserInfo($user_id);
            $response = $user_info;

            Gateway::joinGroup($client_id, $room_id);
            $_SESSION['room_id'] = $room_id;
            $_SESSION['client_name'] = $user_info['user_name'];

        } while(false);
        var_dump($response);

        return Response::api_response(
            Common::getAction(__FUNCTION__),
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }


    /**
     * 发送消息
     * @param object $oInput
     * @return mixed|string
     */
    public function sendMsg($oInput)
    {
        $user_id  = $oInput->get('uid', '0');           // 用户ID
        $room_id  = $oInput->get('rid', '0');           // 房间ID
        $to_user_id  = $oInput->get('toUid ', '0');     // 发送消息的对象
        $msg  = $oInput->get('msg  ', '');              // 消息内容

        // 登陆时保存的room_id
        $back_room_id = $_SESSION['room_id'];
        if ($back_room_id != $room_id){
            var_dump('room is not same, check it~');
        }

        $errcode = '0';
        $response = [];
        do {
            $user_info = $this->user->getUserInfo($user_id);
            $response = $user_info;

        } while(false);
        var_dump($response);

        return Response::api_response(
            Common::getAction(__FUNCTION__),
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }


    /**
     * 赠送礼物
     * @param object $oInput
     * @return mixed|string
     */
    public function sendGift($oInput)
    {
        $user_id  = $oInput->get('uid', '0');           // 用户ID
        $room_id  = $oInput->get('rid', '0');           // 房间ID
        $p_id  = $oInput->get('pid', '0');     // 礼物ID
        $p_num  = $oInput->get('num', '');              // 数量

        $errcode = '0';
        $response = [];
        do {
            $user_info = $this->user->getUserInfo($user_id);
            $response = $user_info;
        } while(false);
        var_dump($response);

        return Response::api_response(
            Common::getAction(__FUNCTION__),
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }
}
