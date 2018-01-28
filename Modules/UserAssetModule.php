<?php
/**
 * Created by PhpStorm.
 * User: haichang
 * Date: 2018/1/9
 * Time: 20:33
 */

namespace Modules;


use Config\GlobalConfig;
use Tables\Record\AssetLogTable;
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

    /**
     * @param $user_id
     * @param $amount
     * @param $starAmount
     * @param $trade_sn
     * @param $operate_type
     * @return bool
     * @throws UserAssetException
     */
    public function incUserAsset($user_id, $amount, $starAmount, $operate_type, $trade_sn='')
    {
        $error_code = '';
        do {
            if ($amount <= 0 && $starAmount <= 0) {
                $error_code = '999005';
                break;
            }
            $result = $this->userAssetTable->findOneByPk($user_id, 'uid');
            $user_amount = isset($result['amount']) ? $result['amount'] : 0;
            $user_starAmount = isset($result['starAmount']) ? $result['starAmount'] : 0;
            $medoo = $this->userAssetTable->getDb();
            $medoo->action(function ($database) use (
                $user_id,
                $amount,
                $starAmount,
                $user_amount,
                $user_starAmount,
                $trade_sn,
                $operate_type
            ) {
                $affect_row = $this->userAssetTable->incUserAsset(
                    $user_id,
                    $amount,
                    $starAmount
                );
                if (!$affect_row) {
                    throw new \Exception('incUserAsset failed');
                }

                $assetLogTable = new AssetLogTable();
                $data = [
                    'logID'              => $assetLogTable->genId(),
                    'uid'                => $user_id,
                    'tradeSn'            => $trade_sn,
                    'operateType'        => $operate_type,
                    'amount'             => $amount,
                    'remainAmount'       => $amount + $user_amount,
                    'starAmount'         => $starAmount,
                    'remianStarAmoutn'   => $starAmount + $user_starAmount,
                    'operateDesc'        => $this->getOperateDesc($operate_type),
                    'capitalStatus'      => GlobalConfig::CAPITAL_INC,
                    'createDatetime'     => date('Y-m-d H:i:s')
                ];
                $assetLogTable->insert($data);
            });

        } while (false);

        if ($error_code) {
            throw new UserAssetException($error_code);
        }

        return true;
    }

    /**
     * @param $user_id
     * @param $amount
     * @param $starAmount
     * @param $trade_sn
     * @param $operate_type
     * @return bool|mixed
     * @throws UserAssetException
     */
    public function decUserAsset($user_id, $amount, $starAmount, $operate_type, $trade_sn='')
    {
        $error_code = '';
        do {
            if ($amount <= 0 && $starAmount <= 0) {
                $error_code = '999005';
                break;
            }
            $result = $this->userAssetTable->findOneByPk($user_id, 'uid');
            $user_amount = isset($result['amount']) ? $result['amount'] : 0;
            $user_starAmount = isset($result['starAmount']) ? $result['starAmount'] : 0;
            $medoo = $this->userAssetTable->getDb();
            $medoo->action(function ($database) use (
                $user_id,
                $amount,
                $starAmount,
                $user_amount,
                $user_starAmount,
                $trade_sn,
                $operate_type
            ) {
                $affect_row = $this->userAssetTable->decUserAsset(
                    $user_id,
                    $amount,
                    $starAmount
                );
                if (!$affect_row) {
                    throw new \Exception('decUserAsset failed');
                }

                $assetLogTable = new AssetLogTable();
                $data = [
                    'logID'              => $assetLogTable->genId(),
                    'uid'                => $user_id,
                    'tradeSn'            => $trade_sn,
                    'operateType'        => $operate_type,
                    'amount'             => $amount,
                    'remainAmount'       => $amount - $user_amount,
                    'starAmount'         => $starAmount,
                    'remianStarAmoutn'   => $starAmount - $user_starAmount,
                    'operateDesc'        => $this->getOperateDesc($operate_type),
                    'capitalStatus'      => GlobalConfig::CAPITAL_DEC,
                    'createDatetime'     => date('Y-m-d H:i:s')
                ];
                $assetLogTable->insert($data);
            });

        } while (false);

        if ($error_code) {
            throw new UserAssetException($error_code);
        }

        return true;
    }

    /**
     * @param $operate_type
     * @param array $ext
     * @return string
     */
    public function getOperateDesc($operate_type, $ext=[])
    {
        $desc = '';
        switch ($operate_type) {
            case GlobalConfig::OT_RECHARGE:
                $desc = '充值';
                break;
            case GlobalConfig::OT_WITHDRAW:
                $desc = '提现';
                break;
            case GlobalConfig::OT_SEND_GIFT:
                $gift_name = '';
                $desc = sprintf('送礼物%s', $gift_name);
                break;
            case GlobalConfig::OT_RECEIVE_GIFT:
                $gift_name = '';
                $desc = sprintf('收礼物%s', $gift_name);
                break;
        }

        return $desc;
    }
}