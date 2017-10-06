<?php
/**
 * 用户表
 */
namespace Tables\User;

use Tables\UserBase;

class UserAttentionTable extends UserBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('user_attention');
        $this->setPk('atID');
        $this->setTableInId(90);
        parent::__construct();
    }

    /**
     * 获取用户关注信息
     * @return mixed
     */
    public function getAttBetweenUsers($user_id, $mod_id){

        return $this->medoo->get(
            $this->table(),
            '*',
            [
                'AND' => [
                    'beAttentionUid' => $mod_id,
                    'attentionUid' => $user_id,
                    'status' => 1
                ]
            ]
        );
    }
    /**
     * 检查两个用户是否有关注关系
     * @return mixed
     */
    public function checkAttUsers($user_id, $mod_id){
        $att = $this->getAttBetweenUsers($user_id, $mod_id);
        if (empty($att)){
            return false;
        }
        return true;
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
