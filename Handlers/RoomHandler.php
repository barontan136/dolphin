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

        $user_id  = $oInput->get('uid', ''); //
        $location  = $oInput->get('location', ''); //
        $announcement  = $oInput->get('announcement', ''); //
        $tagIds  = $oInput->get('tagIds', ''); //
        $access_token  = $oInput->get('accessToken', ''); // 验证登录信息

        $errcode = '0';
        $response = [];
        do {
            if(empty($user_id)){
                $errcode = "999005";
                break;
            }

            $dynamic = $this->userModule->getUserDynamicByUserId($user_id);
            // TODO
            /*if (!isset($dynamic['accessToken']) || $access_token != $dynamic['accessToken']) {
                $errcode = "999006";
                break;
            }*/
            //
            $ret = $this->userModule->updateUserAnnouncement($user_id, $announcement);
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
     * 请求直播推流地址
     * @param object $oInput
     * @return mixed|string
     */
    public function requestLiveAddress($oInput){

        $user_id  = $oInput->get('uid', '');     // 进入房间的用户ID
        $room_id  = $oInput->get('rid', '');     // 房间ID
        $screen_width  = $oInput->get('screenWidth', '');     // 推流视频宽高
        $screen_height  = $oInput->get('screenHeight', '');     // 推流视频宽高

        $errcode = '0';
        $response = [];
        do {
            $address = $this->roomModule->getLiveAddress($room_id);

            $response = array(
                'encrypted' => $address
//                'encrypted' => 'rtmp://18277.livepush.myqcloud.com/live/18277_5624ed1e532c15a542b4ff01449899?bizid=18277&txSecret=adfc2f4b623c7d2cc7f9bb1302c19ee8&txTime=5A557CD0'
            );
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
    public function getInfo($oInput)
    {
        $user_id  = $oInput->get('uid', '');     // 进入房间的用户ID
        $room_id  = $oInput->get('rid', '');     // 房间ID
        $access_token  = $oInput->get('accessToken', ''); // 验证登录信息

        $errcode = '0';
        $response = [];
        do {
            // TODO
           /* $dynamic = $this->userModule->getUserDynamicByUserId($user_id);
            if (!isset($dynamic['accessToken']) || $access_token != $dynamic['accessToken']) {
                $errcode = "999006";
                break;
            }*/

            $response = $this->roomModule->getRoomDetail($user_id, $room_id);
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
        $user_id  = $oInput->get('uid', '');     //
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


    /**
     * 验证主播权限
     * @param object $oInput
     * @return mixed|string
     */
    public function canLive($oInput){
        $user_id  = $oInput->get('uid', '');     //
        $accessToken  = $oInput->get('accessToken', '0');     //

        $errcode = '0';
        $response = [];
        do {
            $result = $this->userModule->getUserInfo($user_id);
            if ($result == null || empty($result)){
                $errcode = '999005';
                break;
            }
            if (isset($result['verifiedID']) && $result['verifiedID'] != ''){
                $response['canLive'] = 1;
                $response['rid'] = $result['rid'];
            }
            elseif ($result['verifiedID'] == ''){
                // 更新该用户为实名认证用户,主播用户,并创建房间等信息
                $auth_info = 'system auto auth';
                $userModule = new UserModule();
                $ret = $userModule->setUserToModerator($user_id, $auth_info);
                if ($ret && !empty($ret)){
                    $response['canLive'] = 1;
                    $response['rid'] = isset($ret['rid'])? $ret['rid'] : '';
                }
//                $response['canLive'] = 5;
//                $response['rid'] = '';
            }
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }
}
