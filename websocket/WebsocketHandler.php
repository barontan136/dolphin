<?php

namespace Handlers;

require_once dirname(__DIR__) . '/Bootstrap/Worker.php';

use \GatewayWorker\Lib\Gateway;
use Modules\GiftException;
use Modules\GiftModule;
use Modules\RoomException;
use Modules\RoomModule;
use Modules\UserException;
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
            try {
                $roomModule = new RoomModule();
                $roomModule->loginRoom(
                    $user_id,
                    $client_id,
                    $room_id
                );
            } catch (RoomException $e) {
                $errcode = $e->getExpCode();
                break;
            } catch (\Exception $e) {
                $this->log->error(
                    sprintf('[%s][exception msg][%s]', __FUNCTION__, $e->getMessage())
                );
                $errcode = '999999';
                break;
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
            try {
                $giftModule = new GiftModule();
                $giftModule->sendGift($user_id, $room_id, $p_id, $p_num);
            } catch (GiftException $e) {
                $errcode = $e->getExpCode();
            } catch (\Exception $e) {
                $this->log->error(
                    sprintf('[%s][exception msg][%s]', __FUNCTION__, $e->getMessage())
                );
                $errcode = '999999';
                break;
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
            try {
                $roomModule = new RoomModule();
                $response = $roomModule->videoPublish($user_id, $room_id);
            } catch (RoomException $e) {
                $errcode = $e->getExpCode();
            } catch (\Exception $e) {
                $this->log->error(
                    sprintf('[%s][exception msg][%s]', __FUNCTION__, $e->getMessage())
                );
                $errcode = '999999';
                break;
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
     * 上报直播结束
     * @param $oInput
     * @return mixed|string
     */
    public function videoUnpublish($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID
        $room_id  = $oInput->get('rid', '');            // 房间ID

        $errcode = '0';
        $response = [];
        do {
            try {
                $roomModule = new RoomModule();
                $response = $roomModule->videoUnpublish($user_id, $room_id);
            } catch (RoomException $e) {
                $errcode = $e->getExpCode();
            } catch (\Exception $e) {
                $this->log->error(
                    sprintf('[%s][exception msg][%s]', __FUNCTION__, $e->getMessage())
                );
                $errcode = '999999';
                break;
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
     * 禁言
     * @param $oInput
     * @return mixed|string
     */
    public function gag($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID
        $gagUid  = $oInput->get('gagUid', '');            // 被禁用户ID
        $expires  = $oInput->get('expires', '');            // 被禁言时长，单位分钟
        $room_id  = $oInput->get('rid', '');

        $errcode = '0';
        $response = [];
        do {
            try {
                $roomModule = new RoomModule();
                $roomModule->gagUser(
                    $user_id,
                    $gagUid,
                    $expires,
                    $room_id
                );
                $response = [
                    'operatorUid'  => $user_id
                ];
            } catch (RoomException $e) {
                $errcode = $e->getExpCode();
                break;
            } catch (\Exception $e) {
                $this->log->error(
                    sprintf(
                        '[%s][exception msg][%s]',
                        __FUNCTION__,
                        $e->getMessage()
                    )
                );
                $errcode = '999999';
                break;
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
     * 解禁
     * @param $oInput
     * @return mixed|string
     */
    public function unGag($oInput)
    {
        $op_user_id = $oInput->get('uid', '');
        $unGagUid = $oInput->get('unGagUid', '');
        $room_id = $oInput->get('rid', '');

        $errcode = '0';
        $response = [];
        do {
            try {
                $roomModule = new RoomModule();
                $response = $roomModule->ungagUser(
                    $op_user_id,
                    $unGagUid,
                    $room_id
                );
            } catch (RoomException $e) {
                $errcode = $e->getExpCode();
                break;
            } catch (\Exception $e) {
                $this->log->error(
                    sprintf(
                        '[%s][exception msg][%s]',
                        __FUNCTION__,
                        $e->getMessage()
                    )
                );
                $errcode = '999999';
                break;
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
            try {
                $userModule = new UserModule();
                $response = $userModule->attentionUser($user_id, $room_id);
            } catch (UserException $e) {
                $errcode = $e->getExpCode();
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
     * 用户取消关注主播上报
     * @param $oInput
     * @return mixed|string
     */
    public function userUnAttention($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID
        $room_id  = $oInput->get('rid', '');            // 房间ID

        $errcode = '0';
        $response = [];
        do {
            $userModule = new UserModule();
            $response = $userModule->unAttentionUser($user_id, $room_id);
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response,
            Common::getAction(__FUNCTION__)
        );
    }

    /**
     * TODO
     * 用户分享直播间上报
     * @param $oInput
     * @return mixed|string
     */
    public function userShare($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID
        $room_id  = $oInput->get('rid', '');            // 房间ID

        $errcode = '0';
        $response = [];
        do {
            $userModule = new UserModule();
            $user_info = $userModule->getUserInfo($user_id);
            if (empty($user_info)) {
                $errcode = '997002';
                break;
            }
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

    /**
     * 设为管理员通告
     * @param $oInput
     * @return mixed|string
     */
    public function setAdmin($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID
        $room_id  = $oInput->get('rid', '');            // 房间ID
        $setUid   = $oInput->get('setUid', '');            // 被设置的用户ID

        $errcode = '0';
        $response = [];
        do {
            try {
                $roomModule = new RoomModule();
                $response = $roomModule->setAdmin($user_id, $setUid, $room_id);
            } catch (RoomException $e) {
                $errcode = $e->getExpCode();
            } catch (\Exception $e) {
                $this->log->error(
                    sprintf('[%s][exception msg][%s]', __FUNCTION__, $e->getMessage())
                );
                $errcode = '999999';
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
     * 取消管理员通告
     * @param $oInput
     * @return mixed|string
     */
    public function unsetAdmin($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID
        $room_id  = $oInput->get('rid', '');            // 房间ID
        $setUid   = $oInput->get('setUid', '');            // 被设置的用户ID

        $errcode = '0';
        $response = [];
        do {
            try {
                $roomModule = new RoomModule();
                $response = $roomModule->setAdmin($user_id, $setUid, $room_id);
            } catch (RoomException $e) {
                $errcode = $e->getExpCode();
            } catch (\Exception $e) {
                $this->log->error(
                    sprintf('[%s][exception msg][%s]', __FUNCTION__, $e->getMessage())
                );
                $errcode = '999999';
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
     * TODO
     * 主播升级
     * @param $oInput
     * @return mixed|string
     */
    public function moderatorLevelIncrease($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID

        $errcode = '0';
        $response = [];
        do {
            $userModule = new UserModule();
            $user_info = $userModule->getUserInfo($user_id);
            if (empty($user_info)) {
                $errcode = '997002';
                break;
            }
            $response = [
                'mid'                => $user_info['uid'],
                'nickname'           => $user_info['nickname'],
                'moderatorLevel'     => $user_info['moderatorLevel'],
                'moderatorLevelName' => $user_info['moderatorLevelName'],
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
     * TODO
     * 用户升级
     * @param $oInput
     * @return mixed|string
     */
    public function userLevelIncrease($oInput)
    {
        $user_id  = $oInput->get('uid', '');            // 用户ID

        $errcode = '0';
        $response = [];
        do {
            $userModule = new UserModule();
            $user_info = $userModule->getUserInfo($user_id);
            if (empty($user_info)) {
                $errcode = '997002';
                break;
            }
            $response = [
                'uid'       => $user_info['uid'],
                'nickname'  => $user_info['nickname'],
                'level'     => $user_info['level'],
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
     * 系统消息
     * @param $oInput
     * @return mixed|string
     */
    public function onSystemMsg($oInput)
    {
        $type  = $oInput->get('type', '');       // 0不弹框 1弹框
        $msg  = $oInput->get('msg', '');         // html内容

        $errcode = '0';
        $response = [];
        do {

            $response = [
                'type'  => $type,
                'msg'   => $msg,
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
     * TODO
     * 全站广播消息
     * @param $oInput
     * @return mixed|string
     */
    public function onNewBulletBarrage($oInput)
    {
        $type  = $oInput->get('type', '');       // 0不弹框 1弹框
        $msg  = $oInput->get('msg', '');         // html内容

        $errcode = '0';
        $response = [];
        do {
            $response = [
                'type'  => $type,
                'msg'   => $msg,
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
