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
        $this->setPrefix("lz_");
        $this->setTable('users');
        $this->setPk('uid');
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
            $this->medoo->debug()->select($this->table().'(a)',$join,$field,$where);
            return $this->medoo->select($this->table().'(a)',$join,$field,$where);
        }else{
            return $this->medoo->select($this->table(),$field,$where);
        }
    }

    /**
     * 根据用户uid返回用户相关信息
     * @param string $user_id
     * @param string | array $field
     * @return mixed
     */
    public function getUserInfoByUserId($user_id, $field='*')
    {
        return $this->medoo->get($this->table(),
            $field,
            array('uid' => $user_id));
    }

    /**
     * 根据用户手机号返回用户相关信息
     * @param string $mobile
     * @param string | array $field
     * @return mixed
     */
    public function getUserInfoByMoible($mobile, $field='*')
    {
        return $this->medoo->get($this->table(),
            $field,
            array('regMobile' => $mobile));
    }

    /**
     * 用户注册
     * @param array $user_data 字段名=>值
     * @return mixed
     */
    public function createUser($user_data)
    {
        try {
            $this->medoo->insert($this->table(), $user_data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 更新用户
     * @param array $data 字段名=>值
     * @param array $where
     * @return mixed
     */
    public function updateUser($user_data, $where)
    {
        try {
            $this->medoo->update($this->table(), $user_data, $where);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
