<?php
namespace Utils;
/*
 * 日志类
 * */

class Log
{
	const EXT = 'log';

	public static function write($content, $file, $line, $arg_desc = '', $log_file='sys_error', $product='app')
	{
		$log_path = self::make_dir($product);
		$log = $log_path . '/' . $log_file . '-' . date('YmdH') . '.' . self::EXT;

		$str = "\r\n-- ". date('Y-m-d H:i:s'). " --------------------------------------------------------------\r\n";
		$str .= "FILE: $file\r\nLINE: $line\r\n";
		if ($arg_desc != '')
		{
			$str .= "$arg_desc:\r\n";
		}

		$str .= '$content=' . self::print_data($content);

		$fh = fopen($log, 'ab');
		fwrite($fh, $str);
		fclose($fh);
	}

	private static function print_data($content)
	{
		$str = '';
		if (is_array($content))
		{
			$str .= 'array(';
			foreach ($content AS $key => $list)
			{
				if (is_array($list))
				{
					$str .= $key . ' => ' . self::print_data($list);
				}
				else
				{
					$str .= "'$key' => '$list'\r\n";
				}
			}
			$str .= ")\r\n";
		}
		else
		{
			$str .= $content;
		}
		return $str;
	}
	private static function make_dir($path='')
	{
		$dir = dirname(__DIR__) . '/data/log/' . $path . '/' . date('Ymd');
		if(is_dir($dir) || mkdir($dir, 0777, TRUE))
		{
			return $dir;
		}
		else
		{
			return FALSE;
		}
	}
}

?>