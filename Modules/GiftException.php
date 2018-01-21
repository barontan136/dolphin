<?php
/**
 * Created by PhpStorm.
 * User: haichang
 * Date: 2018/1/9
 * Time: 20:59
 */

namespace Modules;


class GiftException extends \Exception
{
    private $exp_code = '';
    private $exp_message = '';

    public function __construct($code, $message='')
    {
        parent::__construct($message);
        $this->exp_message = $message;
        $this->exp_code = $code;
    }

    public function getExpCode()
    {
        return $this->exp_code;
    }

    public function getExpMessage()
    {
        return $this->exp_message;
    }
}