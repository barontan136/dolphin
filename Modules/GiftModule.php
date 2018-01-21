<?php
/**
 * Created by PhpStorm.
 * User: 海昌
 * Date: 2017/10/21
 * Time: 14:28
 */

namespace Modules;


use Tables\Room\GiftTable;

class GiftModule
{
    private $giftTable = null;

    public function __construct()
    {
        $this->giftTable = new GiftTable();
    }

    /**
     * @param $gift_id
     * @return mixed
     */
    public function getGiftInfoById($gift_id)
    {
        return $this->giftTable->findByPk($gift_id);
    }

    public function sendGift($user_id, $p_id, $p_num)
    {
        $errcode = '';
        $response = [];
        do {
            $userAssetModule = new UserAssetModule();
            $user_asset = $userAssetModule->getUserAsset($user_id);
            if (empty($user_asset)) {
                $errcode = '998005';
                break;
            }

            $giftModule = new GiftModule();
            $gift_info = $giftModule->getGiftInfoById($p_id);
            if (empty($gift_info)) {
                $errcode = '998006';
                break;
            }

            $cost_amount = $this->getCostAmount($p_num, $gift_info['cost']);
            if ($user_asset['amount'] < $cost_amount) {
                $errcode = '998007';
                break;
            }
            $affected_row = $userAssetModule->decUserAsset($user_id, $cost_amount);
            if (!$affected_row) {

            }


            $userModule = new UserModule();
            $user_info = $userModule->getUserInfo($user_id);
            $response = [
                'fromUid'       => $user_info['uid'],
                'fromNickname'  => $user_info['nickname'],
                'fromLevel'     => $user_info['level'],
                'fromType'      => $user_info['type'],
                'fromHeadPic'   => $user_info['headPic'],
                'pid'           => $gift_info['gid'],
                'num'           => $p_num,
                'cost'          => $gift_info['price'],
                'giftPic'       => $gift_info['img'],
                'name'          => $gift_info['name'],
                'combo'         => '',
                'comboNum'      => '',
                'effect'        => $gift_info['isBonus'],
            ];
        } while (false);

        if ($errcode) {
            throw new GiftException($errcode);
        }

        return $response;
    }

    /**
     * @param $p_num
     * @param $unit_price
     * @return mixed
     */
    public function getCostAmount($p_num, $unit_price)
    {
        return $p_num * $unit_price;
    }
}