<?php
/**
 * 用户表
 */
namespace Tables\Payment;

use Tables\PaymentBase;

class WithdrawOrderTable extends PaymentBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('withdraw_log');
        $this->setPk('withdrawSn');
        $this->setTableInId(98);
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
