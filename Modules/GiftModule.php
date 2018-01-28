<?php
/**
 * Created by PhpStorm.
 * User: 海昌
 * Date: 2017/10/21
 * Time: 14:28
 */

namespace Modules;


use Config\GlobalConfig;
use Tables\Record\GiftLogTable;
use Tables\Room\GiftTable;
use Tables\User\UserGiftsTable;

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

    /**
     * @param $user_id
     * @param $room_id
     * @param $p_id
     * @param $p_num
     * @return array
     * @throws GiftException
     */
    public function sendGift($user_id, $room_id, $p_id, $p_num)
    {
        $errcode = '';
        $response = [];
        do {
            $roomModule = new RoomModule();
            $room_info = $roomModule->getRoomInfo($room_id);
            if (empty($room_info)) {
                $errcode = '997001';
                break;
            }

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

            $userModule = new UserModule();
            $user_info = $userModule->getUserInfo($user_id);

            $userGiftsTable = new UserGiftsTable();
            $medoo = $userGiftsTable->getDb();
            $medoo->action(function ($database) use (
                $userAssetModule,
                $user_id,
                $cost_amount,
                $room_info,
                $userGiftsTable,
                $gift_info,
                $p_num,
                $user_info,
                $userModule
            ){
                $mod_id = $room_info['uid'];//主播ID
                try {
                    //扣除送礼人的账户余额
                    $affected_row = $userAssetModule->decUserAsset(
                        $user_id,
                        $cost_amount,
                        0,
                        GlobalConfig::OT_SEND_GIFT
                    );
                    if (!$affected_row) {
                        throw new \Exception('decUserAsset failed');
                    }
                    $affected_row = $userAssetModule->incUserAsset(
                        $mod_id,
                        0,
                        $cost_amount,
                        GlobalConfig::OT_RECEIVE_GIFT
                    );
                    if (!$affected_row) {
                        throw new \Exception('incUserAsset failed');
                    }
                } catch (UserAssetException $e) {
                    throw new \Exception($e->getExpCode());
                }

                $mod_info = $userModule->getUserInfo($mod_id);
                $now_time = date('Y-m-d H:i:s');
                $giftLogTable = new GiftLogTable();
                $data = [
                    'logID'             => $giftLogTable->genId(),
                    'giftID'            => $gift_info['gid'],
                    'name'              => $gift_info['name'],
                    'cost'              => $gift_info['price'],
                    'number'            => $p_num,
                    'uid'               => $user_id,
                    'nickname'          => $user_info['nickname'],
                    'toUid'             => $mod_id,
                    'toNickName'        => $mod_info['nickname'],
                    'createDatetime'    => $now_time,
                    'updateDatetime'    => $now_time,
                ];
                $giftLogTable->insert($data);
            });

            $response = [
                'fromUid'        => $user_info['uid'],
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