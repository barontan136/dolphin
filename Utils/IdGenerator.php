<?php

namespace Utils;

class IdGenerator
{
    public static function genId($moduleInId, $tableInId) {
        list($mic, $sec) = explode(" ", microtime());
        $micro = ($sec*1000000+intval(round($mic*1000000)));
        return sprintf("%s%s%s%s", dechex($micro), uniqid(), $moduleInId,$tableInId );
    }
}
