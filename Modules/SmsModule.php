<?php

namespace Modules;

use Config\GlobalConfig;
use Utils\Logging;
use Utils\IdGenerator;
use Tables\User\SmsCodeTable;
use Tables\User\Cache\VCodeRedisTable;

class SmsModule
{

    private static $sms = NULL;
    private $cd_time = 60;   //发送短信时间间隔    注册时2分钟，其他都为1分钟
    private $left_time = 0;   //离下次发送短信剩余时间
    private $valid_time = 180; //短信验证有效时间
    private $check_code = '';  //验证码
    private $message;
    private $err_code;
    private $platforms = array(2);  //0:广州首易 1:上海创蓝 2:阿里大鱼
    private $msg_tpl = array();
    private $UserModule = '';
    private $log = null;
    private $vCode = null;
//    private $configModule = null;

    public function __construct()
    {
        $this->SmsLogTable = new SmsCodeTable();
        $this->UserModule = new UserModule();
        $this->log = Logging::getLogger();
        $this->vCode = new  VCodeRedisTable();
//        $this->configModule = new ConfigModule();
        $this->platforms = [2]; //0:广州首易 1:上海创蓝 2:阿里大鱼

        $this->message = array(
            '006002' => '短信验证码不存在',
            '006003' => '短信验证码过期',
            '006004' => '规定时间内不能重复发送短信',
            '006005' => '短信发送失败',
            '006006' => '短信验证码不正确',
        );

        $this->msg_tpl = array(
            'SMS_9020010' => '验证码${code}，您正在注册成为${product}用户，感谢您的支持！',
            'SMS_10410948' => '验证码：${code},请勿轻易告诉别人!金世通人员不会向您索要验证码!如非本人操作,请联系在线客服。',
            );
    }

    /**
     *
     * 作用：单例模式
     * */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * 验证短信验证码
     * @param string $reg_mobile
     * @param string $check_code
     * @return bool
     */
    public function checkSmsCode($reg_mobile, $check_code)
    {
        $vcode = $this->vCode->getVerifyCode($reg_mobile);
        var_dump($check_code, $vcode);
        if (!$vcode) {
            $this->err_code = '6002';
            return FALSE;
        }

        if ($check_code != $vcode['vcode']) {
            $this->err_code = '6006';
            return FALSE;
        }
        //更新短信记录
        $data = [
            'updateDatetime' => date('Y-m-d H:i:s'),
            'status' => 1
        ];
        $where = [
            'codeID' => $vcode['codeID']
        ];
        $this->SmsLogTable->updateByWhere($data, $where);
        return TRUE;
    }

    /**
     * 返回错误码
     * @return mixed
     */
    public function getErrCode()
    {
        return $this->err_code;
    }

    /**
     * 生成短信验证码(4位随机数)
     * @return string
     */
    public function createSmsContent()
    {
        return $this->check_code = sprintf('%s', rand(1000, 9999));
    }

    /**
     * 发送短信
     * @param string $reg_mobile
     * @param string $msg
     * @param int $user_id
     * @return bool
     */
    public function sendSms($reg_mobile, $msg, $user_id = 0)
    {
        $this->log->info(sprintf(
            "reg_mobile:%s, msg:%s, user_id:%s",
            $reg_mobile,
            json_encode($msg), $user_id)
        );

        if (empty($this->platforms)) {
            $this->err_code = '6005';
            return FALSE;
        }
        //生成短信内容并发送短信
        $result = array();
        if (!isset($msg['tpl_id'])) {
            $this->err_code = '999999';
            return FALSE;
        }

        $tpl_id = $msg['tpl_id'];
        $content = $this->parseMsgContent($tpl_id, $msg);
       if (isset($msg['code']) && $msg['code'] == '') {
            $msg['code'] = $this->createSmsContent();
        }
        $res = \Plugins\Sms\AliSms\Sender::send($reg_mobile, $tpl_id, $msg);
        $this->log->info(sprintf('sendMessage:%s', Logging::json_pretty($res)));
        $result = isset($res['result']) ? $res['result'] : FALSE;
        if (!$result) {
            $this->err_code = '6005';
            return FALSE;
        }

        $platform = 0;
        $data = array(
            'mobile' => $reg_mobile,
            'smsBody' => $content,
            'uid' => $user_id,
            'platform' => $platform,
            'code' => $msg['code'],
            'status' => 0,
        );
        $SendID = $res['request_id'];
        $return_value = $result['err_code'] . '-' . $result['model'];
        $this->log->info(sprintf(
            'checkCode:%s, platform:%s, sendId:%s, returnVal:%s',
            $this->check_code,
            $platform,
            $SendID,
            $return_value
        ));

        if ($SendID && $this->check_code) {
            $data['smsId'] = $SendID;
            $data['retMsg'] = $return_value;
            try {
                $sms_log_id = $this->SmsLogTable->insertSendLog($data);
                $this->vCode->setVerifyCode(
                    $reg_mobile,
                    [
                        'vcode'      => $this->check_code,
                        'codeID' => $sms_log_id
                    ]
                );
                return true;
            } catch (\Exception  $e) {
                $this->log->error(sprintf(
                    'function:%s, line:%s, code:%s, msg:%s',
                    __FUNCTION__,
                    __LINE__,
                    $e->getCode(),
                    $e->getMessage()
                ), $e);
                $this->err_code = '006005';
                return false;
            }
        } else {
            $this->err_code = '006005';
            return false;
        }
    }

    /**
     * 作用： 判断是否可发送短信
     * @param string $reg_mobile
     * @return bool
     * */
    protected function enableSend($reg_moblie)
    {
        //获得上次发送短信时间
        /*
        $sms_time = $this->getSmsTime($reg_moblie);
        if ($sms_time == '') {
            $sms_time = 0;
        }
        //上次发送短信至今间隔时间
        $interval_time = time();
        $interval_time = $interval_time - $sms_time;
        if ($interval_time <= $this->cd_time) {
            $this->left_time = $this->cd_time - $interval_time;
            return FALSE;
        }*/
        return TRUE;
    }

    /**
     * 作用：解析短信内容
     * @param string msg
     * @return string
     * */
    public function parseMsgContent($tpl_id, $data)
    {
        $msg = $this->msg_tpl[$tpl_id];
        foreach ($data as $k => $v) {
            if ($k == 'code' && $v == '') {
                $v = $this->createSmsContent();
            }
            $msg = str_replace('${' . $k . '}', $v, $msg);
        }
        return $msg;
    }

    /**
     * 作用： 调用广州首益
     * @param string $mobile
     * @param string $msg
     * @return array|bool|mixed
     */
    protected function callSmsMobset($mobile, $msg)
    {
        $msg = iconv("utf-8", "gb2312//IGNORE", $msg);
        $str = "http://web.mobset.com/SDK/Sms_Send.asp?CorpID=124202" . "&LoginName=Admin" . "&Passwd=194142" . "&send_no=" . $mobile . "&Timer=" . "&msg=" . urlencode($msg) . '&LongSms=1';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);

        $result = explode(",", $output);
        $result[1] = iconv("gb2312//IGNORE", "utf-8", $result[1]);

        if ($result[0] <= 0) {
            return FALSE;
        }
        return $result;
    }

    /**
     * @param $mobile
     * @param $msg
     * @return array|bool|mixed
     * 作用：调用上海创蓝短信接口
     */

    protected function callSmsChuanglan($mobile, $msg)
    {
        $clapi = new \Plugins\Sms\ChuanglanSms\ChuanglanSmsApi();
        $result = $clapi->sendSMS($mobile, $msg, 'true');
        $result = $clapi->execResult($result);
        if ($result[1] != 0) {
            return FALSE;
        }
        return $result;
    }

    /**
     * 作用：返回错误信息
     * @param string $code
     * @return string
     * */
    public function getErrorMessage($code = '')
    {
        $err_code = $code ? $code : $this->err_code;
        return $this->message[$err_code];
    }

}

