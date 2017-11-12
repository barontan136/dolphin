<?php
namespace Utils;

use Config\KafkaConfig;
use Config\Task as TaskConfig;
use Config\GatewayConstants;
use Config\GlobalConfig;
use Modules\ConfigModule;
use Utils\RedisClient;

class Common
{
	/**
	 *  验证手机号码有效性
     * @param $mobile
	 * */
	public static function is_valid_mobile($mobile)
	{
		$url = 'http://apis.baidu.com/apistore/mobilenumber/mobilenumber?phone=' . $mobile;
		return self::execute_baidu_api($url);
	}

    /**
     *  获取阿里云直播的推送鉴权码
     * @param $mobile
     * */
	public static function get_pulish_auth_key($rid){

        // 直播推流地址鉴权操作
        $configModule = new ConfigModule();
        $appName = $configModule->getValByKeyName('stream_app_name','xiawaNormal');
        $authKey = $configModule->getValByKeyName('domain_publish_auth_key','xiawa0903');
        //
        $authPath = '/' . $appName . '/'. $rid;
        $timeStamp = time() + 1800;
        $stringToAuth = $authPath . '-' . $timeStamp . '-0-0-' .$authKey;

        return  '&auth_key=' .$timeStamp . '-0-0-' . md5($stringToAuth) ;
    }

    /**
     *  调用百度API
     * @param $url
     * */
	public static function execute_baidu_api($url)
	{
		$ch = curl_init();
	//	$url = 'http://apis.baidu.com/datatiny/cardinfo/cardinfo?cardnum=' . $bank_no;
		$header = array('apikey:3c0cec7cf3f06fd2aa42e3446d7ed186');

		// 添加apikey到header
		curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 执行HTTP请求
		curl_setopt($ch , CURLOPT_URL , $url);
		$res = curl_exec($ch);
		curl_close($ch);

		return $res;
	}



    /**
     *  校验银行信息
     * */
    public static function check_bank_name($card_no, $bank_name)
    {
        $res = self::get_open_bank($card_no);
        $json_arr = json_decode($res, TRUE);
        if (empty($json_arr)) {
            return FALSE;
        }
        if ($json_arr['status'] == 1) {
            $bank_list = array(array('工商', '工商银行'),
                array('中国银行', '中国银行'),
                array('建设', '建设银行'),
                array('邮政', '邮政储蓄'),
                array('邮储', '邮政储蓄'),
                array('中信', '中信银行'),
                array('光大', '光大银行'),
                array('华夏', '华夏银行'),
                array('招商', '招商银行'),
                array('兴业', '兴业银行'),
                array('浦东', '浦发银行'),
                array('浦发', '浦发银行'),
                array('平安', '平安银行'),
                array('深圳发展', '平安银行'),
                array('广发', '广发银行'),
                array('广东发展', '广发银行'),
                array('民生', '民生银行'),
                array('农业', '农业银行'),
                array('交通', '交通银行'),
                array('北京银行', '北京银行'),
                array('上海', '上海银行')
            );
            foreach ($bank_list as $value)
            {
                if (strpos($json_arr['data']['bankname'], $value[0]) !== FALSE) {
                    return $value[1] === $bank_name;
                }
            }
        }
        return FALSE;
    }


    /*
     * 作用：生成带时间的订单号
     * @param string $order_sn
     * @return string
     * */
    public static function get_ordersn_with_time($order_sn)
    {
        if (empty($order_sn)) {
            return '';
        }
        return $order_sn . '_' . date('His');
    }

    /*
     * 作用：获取真实订单号
     * @param string $order_sn
     * @return string
     * */
    public static function get_real_order_sn($order_sn)
    {
        if (!preg_match('/^[A-Za-z0-9]+_\d{6}$/', $order_sn)) {
            return $order_sn;
        }
        return substr($order_sn, 0, strpos($order_sn, '_'));
    }


    /*
     * 作用：根据身份证，获得用户的性别及生日信息
     * @param string $id_no
     * @param array
     * */
    public static function get_identity_info($id_no)
    {
        if (empty($id_no)) {
            return '';
        }
        $birthday = strlen($id_no) == 15 ? ('19' . substr($id_no, 6, 6)) : substr($id_no, 6, 8);
        $year = substr($birthday, 0, 4);
        $mothy = substr($birthday, 4, 2);
        $day = substr($birthday, -2);
        $birthday = date('Y-m-d', mktime(0, 0, 0, $mothy, $day, $year));
        $gender = substr($id_no, (strlen($id_no) == 15 ? -1 : -2), 1) % 2 ? 1 : 2;  //1:男 2:女

        return array('gender' => $gender, 'birthday' => $birthday);
    }

    /*
     * 作用：获得处理过的手机号码或未处理的用户名
     * @param string $mobile
     * @return string
     * */
    public static function get_deal_mobile($mobile)
    {
        if (is_numeric($mobile))
        {
            $mobile_top = substr($mobile, 0, 3);
            $mobile_last = substr($mobile, -3);
            return $mobile_top . '*****' . $mobile_last;
        } else {
            return $mobile;
        }

    }



    /*
 * 验证银行卡信息
 */
    public static function get_open_bank($bank_no)
    {
        $url = 'http://apis.baidu.com/datatiny/cardinfo/cardinfo?cardnum=' . $bank_no;
        return self::execute_baidu_api($url);
    }

    public static function gmtime()
    {
        return (time() - date('Z'));
    }

    /**
     * 将浮点数转换成字符串（保留2未小数）
     *
     * */
    public static function float_to_string($data)
    {
        if (empty($data)) {
            return '';
        }

        if (is_array($data)) {
            return array_map("self::float_to_string", $data);
        } elseif (is_float($data)) {
            return number_format($data, 2, '.', '');
        } else {
            return $data;
        }
    }


    /**
     *  根据用户身份证获得年龄
     * @param $id
     * */
    public static function get_age_by_id($id)
    {
        // 过了这年的生日才算多了1周岁
        if(empty($id)) return '';

        $date = strtotime(substr($id, 6, 8));
        //获得出生年月日的时间戳
        $today = strtotime('today');
        //获得今日的时间戳
        $diff = floor(($today-$date)/86400/365);
        //得到两个日期相差的大体年数

        //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($id,6,8).' +'.$diff.'years') > $today?($diff + 1) : $diff;

        return $age;
    }

    /**
     *  获取处理过的银行卡号
     *
     * */
    public static function get_hide_card_no($card_no)
    {
        if (empty($card_no)) {
            return '';
        }
        return substr($card_no, 0, 4) . '****' . substr($card_no, -3);
    }


    public static function foramtPage($page_no, $page_num, $record_count)
    {
        $page_no      = max(intval($page_no), 1); //当前页
        $page_num     = max(intval($page_num), 0); //每页显示大小
        $record_count = max(intval($record_count), 0); //记录数量
        $start        = 0; //记录开始
        $page_count   = 1; //最大页
        $next_no      = 0;

        $page_num = max(10, min(50, $page_num)); //10-50条记录每页

        do {
            if ($record_count < $page_num) {
                break;
            }

            $page_count = max(0, ceil($record_count / $page_num));
            $page_no = min($page_count, $page_no);
            if ($page_no < $page_count) {
                $next_no;
            }
            //分页
        } while(false);

        $start = ($page_no - 1) * $page_num;

        return [
            'page_no'      => $page_no,
            'page_num'     => $page_num,
            'page_count'   => $page_count,
            'next_no'      => $next_no,
            'record_count' => $record_count,
            'start'        => $start,
        ];
    }

    public static function format_show_money($price) {
        return sprintf("%.2f", $price);
    }


    /**
     * 随机6位数密码(纯数字)
     */
    public static function randomPass() {
        $result = '';
        $charlist = '0123456789';
        $charlen = strlen($charlist);
        for($i=0; $i<6; $i++) {
            $result .= $charlist[mt_rand(0, $charlen-1)];
        }
        return $result;
    }

    public static function getWorkerAddress($platform, $version, $module)
    {
        $config = GatewayConstants::RPC_API_NODE;
        $address = '';
        do {
            if (!array_key_exists($platform, $config)) {
                break;
            }
            if (!array_key_exists($version, $config[$platform])) {
                break;
            }
            if (!array_key_exists($module, $config[$platform][$version])) {
                break;
            }
            $address = $config[$platform][$version][$module]['address'];
        } while(false);
        if (empty($address)) {
            $address = TaskConfig::$config[$module]['address'];
        }
        return $address;
    }


    /**
     * 求两个日期之间相差的天数
     * (针对1970年1月1日之后，求之前可以采用泰勒公式)
     * @param string $day1
     * @param string $day2
     * @return number
     */
    public static function diffBetweenTwoDays ($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }

    public static function getAction($method) {
        return 'On' . strtoupper(substr($method, 0, 1)) . substr($method, 1);
    }

}
