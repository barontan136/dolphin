<?php
/**
 * Created by PhpStorm.
 * User: 海昌
 * Date: 2018/1/14
 * Time: 14:49
 */

namespace Modules;


class RoomException extends \Exception
{
    private $exp_code = '';
    private $exp_msg = '';

    public function __construct($code, $message='')
    {
        parent::__construct($message);
        $this->exp_code = $code;
        $this->exp_msg = $message;
    }

    public function getExpCode()
    {
        return $this->exp_code;
    }

    public function getExpMsg()
    {
        return $this->exp_msg;
    }
}