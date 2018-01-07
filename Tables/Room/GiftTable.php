<?php
/**
 * 用户表
 */
namespace Tables\Room;

use Tables\RoomBase;

class GiftTable extends RoomBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('gifts');
        $this->setPk('gid');
        $this->setTableInId(98);
        parent::__construct();
    }

    /**
     * 获取所有可用礼物列表
     * @return mixed
     */
    public function getGiftList(){
        return $this->medoo->select($this->table(), '*', ['status' => 1]);
    }

    /**
     * @param $where
     * @param $join
     * @param string $field
     * @return mixed
     */
    public function select($join='',$where,$field='*'){
        if(!empty($join)){
            return $this->medoo->select($this->table().'(a)',$join,$field,$where);
        }else{
            return $this->medoo->select($this->table(),$field,$where);
        }
    }


}
