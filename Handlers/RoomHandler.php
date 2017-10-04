<?php

namespace Handlers;

use Utils\Response;
use Utils\Logging;
use Config\ErrMessage;
use Modules\UserModule;


class RoomHandler
{
    private $userModule = NULL;
    private $log = null;

    public function __construct()
    {
        $this->userModule = new UserModule();
        $this->token = TokenModule::getInstance();
        $this->log = Logging::getLogger();
    }

    /**
     * 用户信息
     * @param object $oInput
     * @return mixed|string
     */
    public function getUserInfo($oInput)
    {
        $user_id  = $oInput->get('uid', '1'); //设备惟一标识

        $errcode = '0';
        $response = [];
        do {
            $response = $this->userModule->getUserInfo($user_id);
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }
}
