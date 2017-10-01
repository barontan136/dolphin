<?php

namespace Handlers;

use Modules\SmsModule;
use Utils\Response;
use Utils\Logging;
use Config\ErrMessage;
use Modules\UserModule;


class UserHandler
{
    private $user = NULL;
    private $log = null;
    private $smsModule = null;

    public function __construct()
    {
        $this->userModule = new UserModule();
        $this->log = Logging::getLogger();
        $this->smsModule = new SmsModule();
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


    /**
     * 注册/登录/绑定时，请求获取验证码
     * @return mixed
     */
    public function getMobileBindVCode($oInput)
    {
        $user_id  = $oInput->get('uid', '');       // 用户ID
        $mobile  = $oInput->get('mobile', '');    // 接收短信的手机号

        $response = [];
        $errcode = '0';
        //发送短信信息
        try {//发送验证码
            $data = array(
                'tpl_id' => 'SMS_10410948',
                'code' => $this->smsModule->createSmsContent()
            );
            $this->log->info(sprintf("sendMessage->reg_mobile:%s, user_id:%s, code:%s", $mobile, $user_id, $data['code']));
            if (!$this->smsModule->sendSms($mobile, $data, $user_id)) {
                $errcode = $this->smsModule->getErrCode();
            }
        } catch (\Exception $e) {
            return Response::api_response($e->getCode(), $e->getMessage());
        }

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }

    /**
     * 注册/登录/绑定时，请求获取验证码
     * @return mixed
     */
    public function mobileBind($oInput)
    {
        $user_id = $oInput->get('uid', '0');       // 用户ID
        $mobile = $oInput->get('mobile', '');    // 接收短信的手机号
        $check_code = $oInput->get('vcode ','');

        //校验验证码
        if (!$this->smsModule->checkSmsCode($mobile, $check_code)) {
            $err_code = $this->smsModule->getErrCode();
            return Response::api_response($err_code, ErrMessage::$message[$err_code]);
        }
        // 以下验证通过
        $user_info = $this->userModule->getUserInfoByMobile($mobile);
        if (isset($user_info['uid']) && $user_info['uid'] == $user_id){
            // 表示登录或者绑定，刷新access_token，并返回

        }

        return Response::api_response('000000', ErrMessage::$message['000000']);
    }

}
