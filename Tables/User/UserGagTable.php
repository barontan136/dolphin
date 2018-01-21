<?php
/**
 * 用户表
 */
namespace Tables\User;

use Config\GlobalConfig;
use Tables\UserBase;

class UserGagTable extends UserBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('user_gag');
        $this->setPk('gagID');
        $this->setTableInId(91);
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

    public function updateGagStatusByAdmin($roomID, $gag_id, $op_id, $op_name)
    {
        $data = [
            'updateDatetime'   => date('Y-m-d H:i:s'),
            'status'           => GlobalConfig::GAG_ADMIN_CANCEL,
            'operateUid'       => $op_id,
            'operateNickName'  => $op_name
        ];

        $where = [
            'AND' => [
                'uid'        => $gag_id,
                'status'     => GlobalConfig::GAG_ING,
                'roomID'     => $roomID
            ]
        ];
        return $this->medoo-update($this->table(), $data, $where);
    }
}
