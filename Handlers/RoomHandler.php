<?php

namespace Handlers;

use Utils\Response;
use Utils\Logging;
use Config\ErrMessage;
use Modules\UserModule;
use Modules\RoomModule;


class RoomHandler
{
    private $userModule = NULL;
    private $roomModule = NULL;
    private $log = null;

    public function __construct()
    {
        $this->userModule = new UserModule();
        $this->roomModule = new RoomModule();
//        $this->token = TokenModule::getInstance();
        $this->log = Logging::getLogger();
    }

    /**
     * 更新开播标题
     * @param object $oInput
     * @return mixed|string
     */
    public function updateAnnouncement($oInput){

        $user_id  = $oInput->get('uid', ''); //设备惟一标识
        $title  = $oInput->get('title', ''); //设备惟一标识

        $errcode = '0';
        $response = [];
        do {
            if(empty($user_id) || empty($title)){
                $errcode = "980001";
                break;
            }
            //
            $ret = $this->userModule->updateUserAnnouncement($user_id, $title);
            if ($ret <= 0){
                $errcode = "980002";
                break;
            }
            //
            $user_info = $this->userModule->getUserInfo($user_id);
            $response['uid'] = $user_info['uid'];
            $response['nickname'] = $user_info['nickname'];
            $response['headPic'] = $user_info['headPic'];
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }

    /**
     * 主播信息列表
     * @param object $oInput
     * @return mixed|string
     */
    public function getRooms($oInput)
    {
        $user_id  = $oInput->get('uid', '');     // 0：最新 1：热门 10：关注
        $status  = $oInput->get('status', '0');     // 0：最新 1：热门 10：关注
        $tagId  = $oInput->get('tagId', '0');        // 0代表所有，其他代表相应tag

        $errcode = '0';
        $response = [];
        do {
            $response = $this->userModule->getModerators($user_id, $status, $tagId);
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }
}
