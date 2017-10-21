<?php
/**
 * Created by PhpStorm.
 * User: 海昌
 * Date: 2017/10/21
 * Time: 14:48
 */

namespace Tables\Room;


use Utils\RedisClient;

class GiftRedisTable
{
    private $cache = null;
    private $combo_key = 'combo_%s';

    public function __construct()
    {
        $this->cache = RedisClient::getInstance();
    }

    /**
     * 获取某个用户某个礼物的缓存键名
     * @param $user_id
     * @param $gift_id
     * @return string
     */
    public function getComboKey($user_id, $gift_id)
    {
        $str1 = substr($user_id, 0 ,16);
        $str2 = substr($gift_id, 0 ,16);
        return sprintf($this->combo_key, $str1 . $str2);
    }

    /**
     * 增加连击
     * @param $user_id
     * @param $gift_id
     * @param $num
     * @param $life_time
     * @return mixed
     */
    public function incCombo($user_id, $gift_id, $num, $life_time)
    {
        $key = $this->getComboKey($user_id, $gift_id);
        $this->cache->incrBy($key, $num);
        $this->cache->expire($key, $life_time);
    }


}