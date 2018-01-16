<?php
/**
 * 用户表
 */
namespace Tables\User;

use Tables\UserBase;

class UserAssetTable extends UserBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('user_asset');
        $this->setPk('uid');
        $this->setTableInId(92);
        parent::__construct();
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function getUserByUserId($user_id){
        return $this->medoo->select($this->table(),
            '*',
            ['uid' => $user_id]);
    }

}
