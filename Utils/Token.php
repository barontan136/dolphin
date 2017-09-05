<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/11/16
 * Time: 9:59
 */
namespace Utils;

class Token
{
    public static function getToken()
    {
        $token = sprintf("T%s%s", date('ymdHis'), uniqid());
		return $token;
    }
}