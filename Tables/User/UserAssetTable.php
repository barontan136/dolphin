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

    /**
     * @param $user_id
     * @param int $amount
     * @param int $starAmount
     * @return bool
     */
    public function incUserAsset($user_id, $amount=0, $starAmount=0)
    {
        $nowtime = date('Y-m-d H:i:s');
        $result = $this->findByPk($user_id);
        if (empty($result)) {
            $data = [
                'amount'         => $amount,
                'amountIn'       => $amount,
                'starAmount'     => $starAmount,
                'starAmountIn'   => $starAmount,
                'createDatetime' => $nowtime,
                'updateDatetime' => $nowtime,
            ];
            $this->insert($data);
            return true;
        }

        $data = [
            'amount[+]'         => $amount,
            'amountIn[+]'       => $amount,
            'starAmount[+]'     => $starAmount,
            'starAmountIn[+]'   => $starAmount,
            'updateDatetime'    => $nowtime,
        ];

        $where = [
            'uid' => $user_id
        ];

        return $this->medoo->update($this->table(), $data, $where);
    }

    /**
     * @param $user_id
     * @param int $amount
     * @param int $starAmount
     * @return mixed
     */
    public function decUserAsset($user_id, $amount=0, $starAmount=0)
    {
        $data['updateDatetime'] = date('Y-m-d H:i:s');
        if ($amount > 0) {
            $data['amount[-]'] = $amount;
            $data['amountOut[+]'] = $amount;
        }
        if ($starAmount > 0) {
            $data['starAmount[-]'] = $starAmount;
            $data['starAmountOut[+]'] = $starAmount;
        }

        $where = [
            'AND' => [
                'uid' => $user_id,
            ]
        ];
        if ($amount > 0) {
            $where['AND']['amount[>=]'] = $amount;
        }

        if ($starAmount > 0) {
            $where['AND']['starAmount[>=]'] = $starAmount;
        }

        return $this->medoo->update($this->table(), $data, $where);
    }
}
