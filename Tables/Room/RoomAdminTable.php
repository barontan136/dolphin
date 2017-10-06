<?php
/**
 * 用户表
 */
namespace Tables\Room;

use Tables\RoomBase;

class RoomAdminTable extends RoomBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('room_admin');
        $this->setPk('adminID');
        $this->setTableInId(97);
        parent::__construct();
    }

    /**
     * 获取该房间的管理员列表
     * @param $roomID
     * @return mixed
     */
    public function getRoomAdminIdsByID($roomID){
        $where = array(
            'rid' => $roomID,
            'status' => 1
        );
        return $this->getRoomAdmin('uid', ['AND' => $where]);
    }

    /**
     * @param $field
     * @param $where
     * @return mixed
     */
    public function getRoomAdmin($field='*', $where){
        return $this->select('', $where, $field);
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
