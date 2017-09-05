<?php

namespace Handlers;

use Utils\Response;
use Utils\Logging;
use Config\ErrMessage;
use Modules\UserModule;


class UserHandler
{
    private $user = NULL;
    private $log = null;

    public function __construct()
    {
        $this->user = new UserModule();
        $this->log = Logging::getLogger();
    }

    /**
     * 用户注册
     * @param object $oInput
     * @return mixed|string
     */
    public function userRegister($oInput)
    {
        $device_id  = $oInput->get('device_id', ''); //设备惟一标识

        $errcode = '000000';
        $response = [];
        do {


        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }


}
