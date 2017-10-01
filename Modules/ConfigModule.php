<?php
/**
 * Created by emacs.
 * User: sunday
 * Date: 2017/2/15
 * Time: 11:40
 */
namespace Modules;

use Utils\Common;
use Tables\Config\ConfigTable;
use Tables\Deposit\WebConfigTable;
use Tables\Shop\ShopConfigTable;

class ConfigModule
{
    private $confTable = null;
    private $webConfigTable = null;

    public function __construct()
    {
        $this->confTable = new ConfigTable();
        $this->webConfigTable = new WebConfigTable();
    }

    /**
     * 根据键获取相应的配置信息
     * @param string $key 键
     * @param mixed $default 如果键不存在则返回的默认值
     */
    public function getValByKeyName($key, $default = '')
    {
        $result = $this->confTable->getValByKeyName($key);
        $result = $result !== '' ? $result : $default;
        return env($key, $result);
    }

    /**
     * 设置相应键的配置信息
     * @param string $key 键
     * @param mixed $val 配置参数的值
     */
    public function setValByKeyName($key, $val)
    {
        return $this->confTable->setValByKeyName($key, $val);
    }

    /**
     * @return float 当前金价
     */
    public function getGoldPrice()
    {
        //原来的表使用的是元
        return $this->webConfigTable->getGoldPrice();
    }

    /**
     * 获取新手转享金金价(单位:分/g)
     * @return int
     */
    public function getNewerGoldPrice()
    {
        //原来的表使用的是元
        return $this->getValByKeyName('newer_gold_price', '');
    }

    /**
     * 获取url前缀
     * 如: https://dev.51kingstone.com:8081
     * @return string
     */
    public function getHttpUrlPrefix()
    {
        return $this->getValByKeyName('httpurl_prefix', '');
    }

    /**
     * 获取官网地址
     * 如: https://www.avicks.com
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->getValByKeyName('website_url', 'www.avicks.com');
    }

    /**
     * 获取img url前缀
     * 如: http://qn-cdn.51kingstone.com
     * @return string
     */
    public function getHttpImgPrefix()
    {
        return $this->getValByKeyName('httpimg_url', '');
    }

    /**
     * 获取 最小充值金额(单位:分)
     * 默认10000
     * @return int
     */
    public function getRechargeMin()
    {
        return $this->getValByKeyName('recharge_min', 10000);
    }

    /**
     * 获取 最小提现金额(单位:分)
     * 默认100
     * @return int
     */
    public function getWithdrawMin()
    {
        return $this->getValByKeyName('withdraw_min', 100);
    }

    /**
     * 获取提现手续费
     * @return int
     */
    public function getWithdrawFee()
    {
        return $this->getValByKeyName('withdraw_fee', 2);
    }

    /**
     * 获取卖金手续费
     * @return int
     */
    public function getSoldGoldFeeDesc()
    {
        $result = $this->getValByKeyName('sold_fee_desc', '');
        return sprintf($result, $this->getSoldGoldFee());
    }

    /**
     * 获取卖金手续费
     * @return int
     */
    public function getSoldGoldFee()
    {
        return $this->getValByKeyName('sold_fee', 70);
    }

    /**
     * 获取提现手续费说明文本
     * @return mixed|null|string
     */
    public function getWithdrawFeeTip()
    {
        return $this->getValByKeyName('withdraw_fee_tip', '');
    }

    /**
     * 根据键获取url
     * @param string $key 键
     * @return string
     */
    public function getUrlByKeyName($key, ...$args)
    {
        $url = $this->getValByKeyName($key);
        if (!empty($args)) {
            $url = sprintf($url, ...$args);
        }
        if (strpos($url, 'http') !== 0) {
            $host = $this->getHttpUrlPrefix();
            $url = $host . $url;
        }
        return $url;
    }

    /**
     * 获取cdn资源动带上cdn的host
     * @return string
     */
    public function getCdnImgUrlByName($key)
    {
        $url = $this->getValByKeyName($key);
        if (strpos($url, 'http') !== 0) {
            $cdnHost = $this->getHttpImgPrefix();
            $url = $cdnHost . $url;
        }
        return $url;

    }

    /**
     * 根据键获取带登录凭证的url
     * @param string $key 键
     * @return string
     */
    public function getLoginUrlByKeyName($key, $user_id, $access_token, ...$args)
    {
        $url = $this->getUrlByKeyName($key, ...$args);
        if ($url) {
            $url = Common::formatUrlAdditionUserInfo($url, $user_id, $access_token);
        }
        return $url;
    }

    /**
     * 获取当前充值平台类型
     */
    public function getPayPlatform()
    {
        return $this->getValByKeyName('pay_platform', 0);
    }

    /**
     * 获取金价来源说明
     */
    public function getGoldSourceDesc()
    {
        return $this->getValByKeyName('gold_source_desc', '');
    }


    /**
     * 获取好友邀请的上线奖励金豆数
     */
    public function getInviteReward()
    {
        return $this->getValByKeyName('invite_reward', 500);
    }

    /**
     * 获取买金订单，输入验证码或交易密码的时间，单位：秒，默认值30秒
     * @return mixed|null|string
     */
    public function getSubmitBuyOrderTime()
    {
        return $this->getValByKeyName('submitBuyOrderTime', 30);
    }

    /**
     * 获取提金订单，输入验证码或交易密码的时间，单位：秒，默认值30秒
     * @return mixed|null|string
     */
    public function getSubmitTakeOrderTime()
    {
        return $this->getValByKeyName('submitTakeOrderTime', 30);
    }

    /**
     * 获取商城订单，输入验证码或交易密码的时间，单位：秒，默认值30秒
     * @return mixed|null|string
     */
    public function getSubmitShopOrderTime()
    {
        return $this->getValByKeyName('submitShopOrderTime', 30);
    }

    /**
     * 获取卖金订单，输入验证码或交易密码的时间，单位：秒，默认值30秒
     * @return mixed|null|string
     */
    public function getSubmitSoldGoldTime()
    {
        return $this->getValByKeyName('submitSoldGoldTime', 30);
    }

    public function getCashChargeTime()
    {
        return $this->getValByKeyName('submit_recharge_time', 30);
    }

    public function getWithdrawCashTime()
    {
        return $this->getValByKeyName('submit_withdraw_time', 30);
    }

    public function getSubmitRedeemGoldTime()
    {
        return $this->getValByKeyName('submitRedeemGoldTime', 30);
    }

    public function getRegisterCouponDesc()
    {
        return $this->getValByKeyName('register_coupon_desc', '');
    }

    /**
     * 获取易宝支付回调地址
     * @return mixed|null|string
     */
    public function getYeepayPaymentNotifyUrl()
    {
        return $this->getValByKeyName('yeepay_payment_notify_url', '');
    }

    /**
     * 获取易宝提现回调地址
     * @return mixed|null|string
     */
    public function getYeepayWithdrawNotifyUrl()
    {
        return $this->getValByKeyName('yeepay_withdraw_notify_url', '');
    }

    /**
     * 获取网易支付回调地址
     * @return mixed|null|string
     */
    public function getNtespayPaymentNotifyUrl()
    {
        return $this->getValByKeyName('ntespay_payment_notify_url', '');
    }

    /**
     * 获取切换的支付渠道
     * @return mixed
     */
    public function getSwitchPlatform()
    {
        return $this->getValByKeyName('switch_platform', 0);
    }

    /**
     * 持仓盈亏说明
     * @return string
     */
    public function getProfitLossDesc()
    {
        return $this->getValByKeyName('profit_loss_desc', '');
    }

    /**
     * 历史盈亏说明
     * @return string
     */
    public function getHistoryProfitLossDesc()
    {
        return $this->getValByKeyName('history_profit_loss_desc', '');
    }

    /**
     * 红包功能金库配置项目
     * @return string
     */
    public function getRedPacketImgUrlIndex()
    {
        return $this->getCdnImgUrlByName('rd_img_url_index', '');
    }

    public function getRedPacketImgUrl()
    {
        return $this->getCdnImgUrlByName('rd_img_url', '');
    }

    public function getRedPacketShareUrl()
    {
        return $this->getUrlByKeyName('rd_share_url');
    }

    public function getRedPacketIsShow()
    {
        return $this->getValByKeyName('rd_is_show', '0');
    }

    public function getFriendShareTitle()
    {
        return $this->getValByKeyName('friend_share_title', '悦分享');
    }

    public function getFriendShareDesc()
    {
        return $this->getValByKeyName('friend_share_desc', '拿邀请双重礼');
    }

    public function getRedPacketCofferIsShow()
    {
        return $this->getValByKeyName('rd_coffer_is_show', '0');
    }

    public function getGoldPriceTitle()
    {
        return $this->getValByKeyName('gold_price_title', '金价走势');
    }

    public function getGoldPriceSource()
    {
        return $this->getValByKeyName('gold_price_source', '参考国际金价');
    }

    /**
     * 获取下线购买新手金赠送金豆数
     * @return mixed|null|string
     */
    public function getNewerGoldRewardAmount()
    {
        //奖励调整，从10元调整为3元(实名) + 7元(首购)
        return $this->getValByKeyName('buy_newer_gold_reward', '700');
    }

    public function getAdditionTotalWeight()
    {
        return $this->getValByKeyName('addition_total_weight', '7000000');
    }

    public function getIosDownloadUrl()
    {
        return $this->getValByKeyName(
            'os_download_url',
            'https://itunes.apple.com/us/app/jin-shi-tong-huang-jin-xiao/id1123053688?mt=8'
        );
    }

    /**
     * 金价提醒文字说明
     * @return string
     */
    public function getPriceNotifyDesc()
    {
        return $this->getValByKeyName('price_notify_desc', '');
    }

    public function getAosDownloadUrl()
    {
        return $this->getValByKeyName(
            'aos_download_url',
            'http://download.51kingstone.com/kingstone_v311_ks.apk'
        );
    }

    /**
     * @return mixed|null|string
     */
    public function getPriceUpNoticeDesc()
    {
        return $this->getValByKeyName('upTip', '金价已上涨至%s元/克，敬请留意！');
    }

    /**
     * @return mixed|null|string
     */
    public function getPriceDownNoticeDesc()
    {
        return $this->getValByKeyName('downTip', '金价已下跌至%s元/克，敬请留意！');
    }


    // 获取金价报警提醒相关参数
    public function getAlarmOnOff()
    {
        return $this->getValByKeyName('alarm_on_off', 'off');
    }

    public function getAlarmDiffGoldPrice()
    {
        $alarm_price = $this->getValByKeyName('alarm_diff_gold_price', 1);
        if ($alarm_price > 5) $alarm_price = 5;
        return $alarm_price;
    }

    public function getAutoAdjustPrice()
    {
        $adjust_price = $this->getValByKeyName('alarm_auto_adjust_price', 0.5);
        if ($adjust_price > 1) $adjust_price = 1;
        return $adjust_price;
    }

    public function getNoticeMobiles()
    {
        return $this->getValByKeyName('alarm_price_notice_mobiles', '13418547378,18998998065');
    }
    public function getLastNoticeTime(){
        return  $this->getValByKeyName('alarm_notice_last_time', '123456');
    }
    public function setLastNoticeTime($time){
        if ($time == '') return;
        return  $this->setValByKeyName('alarm_notice_last_time', $time);
    }

    public function getAlarmSTOP()
    {
        return $this->getValByKeyName('alarm_stop_trade', 'OK');
    }

    public function setAlarmSTOP($stop)
    {
        if ($stop == '') return;
        return $this->setValByKeyName('alarm_stop_trade', $stop);
    }
    public function getPriceStopArr(){
        return  $this->getValByKeyName('price_stop_arr', '');
    }

    /**
     * @return mixed
     */
    public function getTakeGoldTip()
    {
        return $this->getValByKeyName('take_message', '提取的金条为标准投资金条，提取的克重为10的整数倍');
    }

    /**
     * 获取购物车地址
     * @return mixed
     */
    public function getCartUrl()
    {
        return $this->getValByKeyName('cart_url', 'https://www.avicks.com/mobile/shop/cart');
    }

    /**
     * 根据开始时间计算平台运营天数
     * @return int
     */
    public function getAdditionTotalDays()
    {
        date_default_timezone_set('	Asia/Shanghai');
        $star_date = $this->getValByKeyName('addition_total_days', '2014-08-28');
        $gap_time = time() - strtotime($star_date);
        return intval(ceil($gap_time / 86400.0)); //向上取整，符合自然天算法
    }

    /**
     * 获取平台用户总营收(单位：元)
     * @return mixed
     */
    public function getAdditionTotalProfitAndUpdate()
    {
        $is_set = $this->confTable->getCacheByKeyName('is_update_addition_total_profit_current');
        if (!$is_set) { //判断当天是否更新过，没有更新就更新
            $init_num = $this->getValByKeyName('addition_total_profit_init', 42876348.62);
            $current = $this->getValByKeyName('addition_total_profit_current', 0);//每天[10000, 30000]随机数总和
            $rand_num = rand(1000000, 3000000);
            $current = round(round($rand_num / 100.0, 2) + floatval($current), 2);
            $ttl = strtotime(date('Y-m-d 23:59:59', time())) - time(); //计算时间差值23:59:59距当前时间相差的秒数
            $this->setValByKeyName('addition_total_profit_current', "$current");
            $this->confTable->setCacheByKeyAndTTL('is_update_addition_total_profit_current', '1', $ttl);
        } else {
            $init_num = $this->getValByKeyName('addition_total_profit_init', 42876348.62);
            $current = $this->getValByKeyName('addition_total_profit_current', 0);//每天[10000, 30000]随机数总和
        }

        return round($current + floatval($init_num), 2);
    }

    /**
     * @return mixed
     */
    public function getKstoneId()
    {
        return $this->getValByKeyName('kstone_id', 98);
    }

    /**
     * @return mixed
     */
    public function getBuryPointStatus()
    {
        return $this->getValByKeyName('bury_point_status', 1);
    }
    
    /**
     * 获取杠杆金倍数
     * @return mixed
     */
    public function getRatioTimes()
    {
        return $this->getValByKeyName('ratio_times', '1,2');
    }

    public function getLeverGoldTip()
    {
        return $this->getValByKeyName('lever_gold_tip', '');
    }

    /**
     * 平台预留平仓本金的比例
     * @return mixed
     */
    public function getLeverCloseRatio()
    {
        return $this->getValByKeyName('lever_close_ratio', 10);
    }

    /**
     * 平台预留预警本金的比例
     * @return mixed
     */
    public function getLeverAlarmRatio()
    {
        return $this->getValByKeyName('lever_alarm_ratio', 15);
    }

    public function getRechargeReturnMsg()
    {
        return $this->getValByKeyName('recharge_result_tip', '您的杠杆金平仓价已由#%s元/克#降至#%s元/克#');
    }
}
