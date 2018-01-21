<?php
/**
 * Created by PhpStorm.
 * User: haichang
 * Date: 2018/1/9
 * Time: 20:33
 */

namespace Modules;


use Tables\User\UserAssetTable;
use Tables\User\UserTable;

class UserAssetModule
{
    public function __construct()
    {
        $this->userAssetTable = new UserAssetTable();
    }

    public function getUserAsset($user_id)
    {
        return $this->userAssetTable->findByPk($user_id);
    }

    public function incUserAsset($user_id, $amount=0, $starAmount=0)
    {
        $result = $this->userAssetTable->findOneByPk($user_id, 'uid');
        if (empty($result)) {
            $now_time = date('Y-m-d H:i:s');
            $userTable = new UserTable();
            $user_info = $userTable->findByPk($user_id);
            if (empty($user_info)) {
                return false;
            }
            $data = [
                'uid'            => $user_id,
                'nickname'       => $user_info['nickname'],
                'realName'       => $user_info['realName'],
                'amount'         => $amount,
                'amountIn'       => $amount,
                'starAmount'     => $starAmount,
                'starAmountIn'   => $starAmount,
                'createDatetime' => $now_time,
                'updateDatetime' => $now_time,
            ];
            $this->userAssetTable->insert($data);
        }
        return $this->userAssetTable->incUserAsset($user_id, $amount, $starAmount);
    }

    public function decUserAsset($user_id, $amount=0, $starAmount=0)
    {
        return $this->userAssetTable->decUserAsset($user_id, $amount, $starAmount);
    }
}