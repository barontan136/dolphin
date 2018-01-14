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

    /**
     * @param $room_id
     * @param $uid
     * @param string $fields
     * @return mixed
     */
    public function getRoomAdminByRidAndUid($room_id, $uid, $fields='*')
    {
        $where = [
            'AND' => [
                'rid'  => $room_id,
                'uid'  => $uid,
            ]
        ];
        return $this->medoo->get($this->table(), $fields, $where);
    }

    /**
     * @param $operate_id
     * @param $uid
     * @param $room_id
     * @return mixed
     */
    public function setRoomAdmin($operate_id, $uid, $room_id)
    {
        $data = [
            'status'           => 1,
            'operateUid'       => $operate_id,
            'update_datetime'  => date('Y-m-d H:i:s')
        ];

        $where = [
            'AND' => [
                'status'   => 2,
                'uid'      => $uid,
                'rid'      => $room_id,
            ]
        ];
        return $this->medoo->update($this->table(), $data, $where);
    }

    /**
     * @param $operate_id
     * @param $uid
     * @param $room_id
     * @return mixed
     */
    public function unsetRoomAdmin($operate_id, $uid, $room_id)
    {
        $data = [
            'status'           => 2,
            'operateUid'       => $operate_id,
            'update_datetime'  => date('Y-m-d H:i:s')
        ];

        $where = [
            'AND' => [
                'status'   => 1,
                'uid'      => $uid,
                'rid'      => $room_id,
            ]
        ];
        return $this->medoo->update($this->table(), $data, $where);
    }
}
