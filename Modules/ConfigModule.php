<?php
/**
 * Created by emacs.
 * User: sunday
 * Date: 2017/2/15
 * Time: 11:40
 */
namespace Modules;

use Config\Rtmp;
use Utils\Common;
use Tables\Config\ConfigTable;
//use Tables\Deposit\WebConfigTable;
//use Tables\Shop\ShopConfigTable;

class ConfigModule
{
    private $confTable = null;
    private $webConfigTable = null;

    public function __construct()
    {
        $this->confTable = new ConfigTable();
//        $this->webConfigTable = new WebConfigTable();
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
        return $result;
//        return env($key, $result);
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
     * 如: https://www.szxiawa.com
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->getValByKeyName('website_url', 'www.szxiawa.com');
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
     * 获取主播播放地址
     * 如: rtmp://push.szxiawa.com/xiawaNormal/XXXXXXX?auth_key=XXXXXXXXX
     * @return string
     */
    public function getUserpublishDomain($roomID){
        return Common::get_push_url(Rtmp::$tx_biz_id, $roomID);
//        return sprintf(
//            $this->getValByKeyName('root_domain_play',"rtmp://push.szxiawa.com/%s/%s?auth_key="),
//            $this->getValByKeyName('stream_app_name','xiawaNormal'),
//            $roomID);
    }
    /**
     * 获取主播发布/推送地址
     * 如: rtmp://video-center.alivecdn.com/xiawaNormal/XXXXXXXX?vhost=push.szxiawa.com
     * @return string
     */
    public function getUserPlayDomain($roomID){

        $txTime = date("Y-m-d H:i:s", strtotime("+1 day"));
        return Common::get_push_url(Rtmp::$tx_biz_id, $roomID, Rtmp::$tx_push_key, $txTime);
        /*
        return sprintf(
            $this->getValByKeyName('root_domain_publish',"rtmp://video-center.alivecdn.com/%s/%s?vhost=push.szxiawa.com"),
            $this->getValByKeyName('stream_app_name','xiawaNormal'),
            $roomID);
        */
    }

}
