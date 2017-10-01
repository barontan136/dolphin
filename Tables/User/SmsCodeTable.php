<?php
/**
 * 用户表
 */
namespace Tables\User;

use Tables\UserBase;

class SmsCodeTable extends UserBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('sms_code');
        $this->setPk('smsID');
        $this->setTableInId(94);
        parent::__construct();
    }


    /**
     * @param $where
     * @param $join
     * @param string $field
     * @return mixed
     */
    public function insertSendLog($data)
    {
        $now_time = date('Y-m-d H:i:s');
        $sms_log_id = $this->genId();//IdGenerator::genId($this->getModuleInId(), $this->getTableInId());
        $data["codeID"] = $sms_log_id;
        $data["createDatetime"] = $now_time;
        $data["updateDatetime"] = $now_time;

        $this->medoo->insert($this->table(), $data);
        return $sms_log_id;
    }

    public function updateByWhere($data, $where){
        
        return $this->medoo->update($this->table(), $data, $where);
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
