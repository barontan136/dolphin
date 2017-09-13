<?php

namespace Tests\Handlers;

use Tests\Protocol;
use Utils\Common;
use Tables\User\UserTable;
use Tables\User\Cache\UserRedisTable;
use Tables\User\Cache\VCodeRedisTable;
use Modules\UserModule;
use Modules\UserRegisterException;

class UserTest extends Base
{
    public function __construct() {
        $this->name = 'User';
        parent::__construct();
    }

    public function testGetUserInfo() {
        $action = 'getUserInfo';
        $params = array(
            'user_id'       => '1'
        );
        $ret = $this->protocol->request($action, $params);
        $jResp = $this->parseResult($ret);
        $user_info = [
            'user_id'      => $jResp['data']['user_id'],
            'access_token' => $jResp['data']['access_token'],
        ];

        $action = 'modifyBindMobile';
        $mobile = '15007155658';
        $vCodeRedisTable = new VCodeRedisTable();
        $check_code = '1234';
        $vCodeRedisTable->setVerifyCode(
            $mobile,
            [
                'vcode'      => $check_code,
                'sms_log_id' => $check_code
            ]
        );
        $params = array(
            'mobile'     => $mobile,
            'check_code' => $check_code,
        );
        $params = array_merge($user_info, $params);
        $ret = $this->protocol->request($action, $params);
        $jResp = $this->parseResult($ret);
    }
}
