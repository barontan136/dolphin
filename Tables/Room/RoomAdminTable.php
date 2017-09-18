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