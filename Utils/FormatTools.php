<?php
namespace Utils;

class FormatTools
{
    //第一个是原串,第二个是 部份串
    public static function startWith($str, $needle)
    {
        return strpos($str, $needle) === 0;
    }

    //第一个是原串,第二个是 部份串
    public static function endWith($haystack, $needle)
     {
          $length = strlen($needle);
          if($length == 0)
          {
              return true;
          }
          return (substr($haystack, -$length) === $needle);
     }

}