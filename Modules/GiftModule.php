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
}