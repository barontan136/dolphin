<?php
/**
 * Created by PhpStorm.
 * User: wyc20
 * Date: 2016/6/24
 * Time: 14:39
 */

namespace Plugins\Sms\AliSms;

use Plugins\Sms\AliSms\top\TopClient;
use Plugins\Sms\AliSms\top\AlibabaAliqinFcSmsNumSendRequest;

if(!defined('TOP_SDK_WORK_DIR')){
    define('TOP_SDK_WORK_DIR', "/tmp/");
}
class Sender {

    public function __construct() {

    }

    public static function send($phone, $template, $param){
        $client = new TopClient();
        $client->appkey = AliSmsConfig::$config['appkey'];
        $client->secretKey = AliSmsConfig::$config['secretKey'];
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName(AliSmsConfig::$config['signName']);
        $req->setSmsParam(json_encode($param));
        $req->setRecNum($phone);
        $req->setSmsTemplateCode($template);
        $resp = $client->execute($req);
        return json_decode(json_encode($resp), true);
    }
}