<?php

namespace Handlers;

use Config\GlobalConfig;
use Modules\SmsModule;
use Modules\TokenModule;
use Utils\Response;
use Utils\Logging;
use Config\ErrMessage;
use Modules\UserModule;


class UserHandler
{
    private $userModule = NULL;
    private $log = null;
    private $smsModule = null;
    private $token = null;

    public function __construct()
    {
        $this->userModule = new UserModule();
        $this->token = TokenModule::getInstance();
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
        $user_id  = $oInput->get('uid', ''); // 惟一标识
        $access_token  = $oInput->get('accessToken', ''); // 验证登录信息

        $errcode = '0';
        $response = [];
        do {
            $dynamic = $this->userModule->getUserDynamicByUserId($user_id);
            if ($dynamic && isset($dynamic['accessToken']) && $access_token == $dynamic['accessToken']) {
                $response = $this->userModule->getUserInfo($user_id);
            }
            else{
                $errcode = '999006';
                break;
            }
        } while(false);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }


    /**
     * 用户登陆
     * @return mixed
     */
    public function login($oInput){

        $mobile  = $oInput->get('mobile', '');       // 用户手机号
        $password  = $oInput->get('password', '');    // 用户密码
        $plateform = $oInput->get('plateform', 0);      // 平台
        $device_id = $oInput->get('deviceName', '11223344');      // 设备ID
        $client_ip = $oInput->get('client_ip', '');      // 客户端IP

        $errcode = '0';
        $response = [];
        do {
            // 检查用户是否存在
            $user_id = $this->userModule->getUserIdByRegMobile($mobile);
            if ($user_id) {
                $user = $this->userModule->getUserStaticByUserId($user_id);
                if ($user && isset($user['password'])) {

                    if ($password == $user['password']) {
                        //每次登录都重新生成access_token
                        $access_token = $this->token->createAccessToken($user_id, $device_id, intval($plateform));
//                        $this->userModule->updateUserLoginInfo($user_id, $plateform, $client_ip);

                        $user_data = array(
                            'uid'      => $user_id,
                            'nickname'    => $user['nickname'],
                            'regMobile'   => $user['regMobile'],
                            'accessToken' => $access_token,
                        );
                        return Response::api_response('000000', ErrMessage::$message['000000'], $user_data);
                    } else {
                        //密码验证失败
                        return Response::api_response('999905', ErrMessage::$message['999905']);
                    }
                } else {
                    //缓存数据异常
                    $errcode = '998003';
                }
            } else {
                //用户不存在
                $errcode = '998003';
            }
        }while(0);

        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }


    /**
     * 获取直播标签列表
     * @return mixed
     */
    public function getConfig($oInput){

        $errcode = '0';
        $response = [];
        try{
            $sign_data = $this->userModule->getSignTypes();

            var_dump($sign_data);
            $response['moderatorTags'] = $sign_data;
        }catch(\Exception $e){
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
    public function getMobileBindVCode($oInput)
    {
        $user_id  = $oInput->get('uid', '');       // 用户ID
        $mobile  = $oInput->get('mobile', '');    // 接收短信的手机号

        var_dump($user_id, $mobile);

        $response = [];
        $errcode = '0';

        do{
            if (strlen($mobile) < 10 || strlen($mobile) > 13){
                $errcode = '10005';
                break;
            }

            //发送短信信息
            try {//发送验证码
                $data = array(
                    'tpl_id' => 'SMS_10410948',
                    'code' => $this->smsModule->createSmsContent()
                );
                $this->log->info(sprintf("sendMessage->reg_mobile:%s, user_id:%s, code:%s", $mobile, $user_id, $data['code']));
                /*if (!$this->smsModule->sendSms($mobile, $data, $user_id)) {
                    $errcode = $this->smsModule->getErrCode();
                }*/
            } catch (\Exception $e) {
                return Response::api_response($e->getCode(), $e->getMessage());
            }
        }while(0);


        return Response::api_response(
            $errcode,
            ErrMessage::$message[$errcode],
            $response
        );
    }


    /**
     * 阿里实名认证,同时用于用户开通主播权限,需要调用第三方阿里认证接口
     * @return mixed
     */
    public function alipayUserAuth($oInput){

        $user_id = $oInput->get('uid', '');         // 用户ID
        $check_code = $oInput->get('code','');      // 用于验证的验证码

        $response['isCertified'] = 0;
        $errcode = '0';
        do{
            try{
                $user_info = $this->userModule->getUserInfo($user_id);
                var_dump($user_info);
                if (!isset($user_info['type']) || $user_info['type'] == GlobalConfig::USER_MODER){
                    $errcode = '990002';
                    break;
                }

                // 调用阿里实名认证接口
                // TODO
                $auth_info = 'auth info';

                // 更新该用户为实名认证用户,主播用户,并创建房间等信息
                if ($this->userModule->setUserToModerator($user_id, $auth_info)){
                    $response['isCertified'] = 1;
                }
            }catch(\Exception $e){
                $this->log->error($e);
                $errcode = '990001';
            }
        }while(0);

        return Response::api_response($errcode, ErrMessage::$message[$errcode], $response);
    }

    /**
     * 注册/登录/绑定时，请求获取验证码
     * @return mixed
     */
    public function mobileBind($oInput)
    {
        $user_id = $oInput->get('uid', '');       // 用户ID
        $device_id = $oInput->get('device', '000000000');       // 设备ID
        $mobile = $oInput->get('mobile', '');    // 接收短信的手机号
        $check_code = $oInput->get('vcode','');

        $response = [];
        $errcode = '0';
        //校验验证码
//        if (!$this->smsModule->checkSmsCode($mobile, $check_code)) {
//            $err_code = $this->smsModule->getErrCode();
//            return Response::api_response($err_code, ErrMessage::$message[$err_code]);
//        }
        // 以下验证通过
        $user_info = $this->userModule->getUserInfoByMobile($mobile);
        if (isset($user_info['uid']) && $user_info['uid'] == $user_id){
            // 表示登录或者绑定，刷新access_token，并返回
            // 每次登录都重新生成access_token
            $access_token = $this->token->createAccessToken($user_id, $device_id, 0);
            $response['accessToken'] = $access_token;
        }
        elseif (empty($user_info)){
            // 新增用户,手机号为mobile
            try{
                // 手机号注册逻辑
                $user_id = $this->userModule->setReadyUserData($mobile, $device_id);
            }catch(\Exception $e){
                // TODO
            }
        }
        $response['uid'] = $user_id;

        return Response::api_response($errcode, ErrMessage::$message[$errcode], $response);
    }

    /**
     * 注册接口
     * @return mixed
     */
    public function userRegister($oInput)
    {
        $user_id = $oInput->get('uid', '');             // 用户ID
        $password = $oInput->get('password', '');       // 密码
        $plateform = $oInput->get('plateform', 0);      // 平台
        $source = $oInput->get('source', 0);            // 来源
        $nickname = $oInput->get('nickname', '');       // 昵称
        $sex = $oInput->get('sex', 0);                  // 男女

        $response = [];
        $errcode = '0';
        do {
            // 校验UID是否存在Redis
            $user_data_ready = $this->userModule->getReadyUserData($user_id);
            if (empty($user_data_ready) || !isset($user_data_ready['regMobile'])) {
                $errcode = '999001';
                break;
            }

            // 检查用户是否存在
            $user_info = $this->userModule->getUserInfoByMobile($user_data_ready['regMobile']);
            if ($user_info){
                $errcode = '999002';
                break;
            }

            // 新增用户,手机号为mobile,
            try {
                // 手机号注册逻辑
                $user_data = $this->userModule->registerByMobile(
                    $nickname,
                    $user_data_ready['regMobile'],
                    $source,
                    $user_data_ready['deviceNum'],
                    $password,
                    $sex,
                    $user_id
                );
            } catch (\Exception $e) {
                // TODO
            }

            $access_token = $this->token->createAccessToken(
                $user_id,
                $user_data['deviceNum'],
                $plateform
            );
        }while(0);
        $response['accessToken'] = $access_token;
        $response['uid'] = $user_id;

        return Response::api_response($errcode, ErrMessage::$message[$errcode], $response);
    }

}
