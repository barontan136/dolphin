<?php
/**
 * Created by PhpStorm.
 * User: haichang
 * Date: 2018/1/9
 * Time: 20:33
 */

namespace Modules;


use Tables\User\UserAssetTable;

class UserAssetModule
{
    public function getUserAsset($user_id)
    {
        $userAssetTable = new UserAssetTable();
        return $userAssetTable->findByPk($user_id);
    }

    public function decUserAsset($user_id)
    {

    }
}