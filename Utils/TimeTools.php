<?php
namespace Utils;

class TimeTools
{

	/**
	 * 获得当前格林威治时间的时间戳
	 *
	 * @return  integer
	 */
	public static function gmtime()
	{
	    return (time() - date('Z'));
	}



	/**
	 * 获得服务器的时区
	 *
	 * @return  integer
	 */
	public static function server_timezone()
	{
	    if (function_exists('date_default_timezone_get'))
	    {
	        return date_default_timezone_get();
	    }
	    else
	    {
	        return date('Z') / 3600;
	    }

	}

}