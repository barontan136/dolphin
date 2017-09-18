<?php
/**
 * 用户表
 */
namespace Tables\Payment;

use Tables\PaymentBase;

class RechargeOrderTable extends PaymentBase
{
    public function __construct()
    {
        $this->setPrefix("lz_");
        $this->setTable('recharge_log');
        $this->setPk('recharegeSn');
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
