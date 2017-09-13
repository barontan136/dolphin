<?php

namespace Tests\Handlers;

use Tests\Protocol;
use Utils\Common;
use Modules\UserModule;

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
            'access_token' => $jResp['data']['use_name'],
        ];
    }
}
