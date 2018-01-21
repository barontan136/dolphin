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

    public function incUserAsset($user_id, $amount=0, $starAmount=0)
    {
        $data = [
            'amount[+]'         => $amount,
            'amountIn[-]'       => $amount,
            'starAmount[+]'     => $starAmount,
            'starAmountIn[-]'   => $starAmount,
        ];

        $where = [
            'uid' => $user_id
        ];

        return $this->medoo->update($this->table(), $data, $where);
    }

    public function decUserAsset($user_id, $amount=0, $starAmount=0)
    {
        if ($amount <= 0 && $starAmount <=0) {
            return false;
        }
        if ($amount > 0) {
            $data = ['amount[-]'] = $amount;
            $data = ['amountOut[+]'] = $amount;
        }
        if ($starAmount > 0) {
            $data = ['starAmount[-]'] = $starAmount;
            $data = ['starAmountOut[+]'] = $starAmount;
        }

        $where = [
            'AND' => [
                'uid' => $user_id,
            ]
        ];
        if ($amount > 0) {
            $where['AND']['amount[>]'] = 0;
        }

        if ($starAmount > 0) {
            $where['AND']['starAmount[>]'] = 0;
        }

        return $this->medoo->update($this->table(), $data, $where);
    }
}
