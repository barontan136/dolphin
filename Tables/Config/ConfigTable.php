<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/2/9
 * Time: 9:57
 */
namespace Tables\Config;

use Tables\ConfigBase;
use Tables\Config\Cache\KSConfigRedisTable;

class ConfigTable extends ConfigBase
{
    private $cCache = null;

    public function __construct()
    {
        parent::__construct();
        $this->setPrefix('lz_');
        $this->setTable('config');
        $this->setPk('confID');
        $this->setTableInId(99);
        $this->cCache = new  KSConfigRedisTable();
    }

    /**
     * 根据配置项的名称读取其值
     * @param $key
     * @return string | null
     */
    public function getValByKeyName($key)
    {
        $val = $this->cCache->getValueByKey($key);
        if ($val) {
            return $val;
        } else {
            $wheres = [
                'AND' => [
                    'key' => $key,
                    'status' => 1,
                ]
            ];
            $conf_val = $this->medoo->get($this->table(), 'value', $wheres);
            return $conf_val !== false ? $conf_val : "";
        }
    }

    /**
     * 根据配置项的名称更新其值
     * @param $key
     * @param $val
     * @return bool
     */
    public function setValByKeyName($key, $val)
    {
        if ($key && $val) {
            $this->cCache->setValueByKey($key, $val);
            $dt = date("Y-m-d H:i:s");
            return $this->medoo->update($this->table(),
                ['value' => $val, 'updateDatetime' => $dt],
                ['key' => $key]);
        }
        return false;
    }

    /**
     * 根据提供的配置项名称更新其状态值为1(启用)或0(禁用)
     * @param $key
     * @param int $val
     * @return bool
     */
    public function setValStatusByKeyName($key, $val = 0)
    {
        if ($key && in_array(intval($val), array(0, 1))) {
            if ($val) { //启用配置项，将配置项的值缓存至Redis中提供访问速度
                $conf_val = $this->medoo->get($this->table(), 'value', ['key' => $key]);
                $this->cCache->setValueByKey($key, $conf_val);
            } else {
                $this->cCache->delValueByKey($key);//禁用配置项，将其从Redis中删除
            }
            $dt = date("Y-m-d H:i:s");
            return $this->medoo->update($this->table(),
                ['status' => intval($val), 'updateDatetime' => $dt],
                ['key' => $key]);
        }
        return false;
    }

    /**
     * 根据KEY名称获取其值
     * @param $key
     * @return bool
     */
    public function getCacheByKeyName($key)
    {
        return $this->cCache->getValueByKey($key);
    }

    /**
     * 根据KEY设置其值及TTL
     * @param $key
     * @param $val
     * @param int $ttl
     * @return bool
     */
    public function setCacheByKeyAndTTL($key, $val, $ttl=0)
    {
        return $this->cCache->setValueByKeyAndTTL($key, $val, $ttl);
    }
}