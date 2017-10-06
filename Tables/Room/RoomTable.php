<?php
/**
 * 用户表
 */
namespace Tables\Room;

use Tables\RoomBase;

class RoomTable extends RoomBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('rooms');
        $this->setPk('rid');
        $this->setTableInId(99);
        parent::__construct();
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

    /**
     * 获取房间信息
     * @param $roomID
     * @return mixed
     */
    public function getRoomInfo($where){
        return $this->medoo->get($this->table(), '*', $where);
    }

    /**
     * 根据rid获取自增主播ID
     * @param $roomID
     * @return mixed
     */
    public  function  getAutoIDByRoomId($roomID){
        return $this->medoo->get($this->table(), 'autoID', ['rid' => $roomID]);
    }

}
