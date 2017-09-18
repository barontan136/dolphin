<?php
/**
 * 用户表
 */
namespace Tables\Record;

use Tables\RecordBase;

class GagLogTable extends RecordBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('gag_log');
        $this->setPk('logID');
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
