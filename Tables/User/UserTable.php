<?php
/**
 * 用户表
 */
namespace Tables\User;

use Tables\UserBase;

class UserTable extends UserBase
{
    public function __construct()
    {
        $this->setPrefix("ol_");
        $this->setTable('users');
        $this->setPk('user_id');
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

}
