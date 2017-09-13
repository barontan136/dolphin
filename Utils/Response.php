<?php
namespace Utils;

class Response {
	/*
	* 封装通信接口数据
	* @param integer $code 状态码
	* @param string $message 状态信息
	* @param array $data 数据
	* return string
	*/
	public static function api_response($cmd, $code, $message='', $data=array()){
		$type = 'json';

		switch ($type) {
			case 'json':
				return self::response_json($cmd, $code, $message, $data);
			case 'xml':
				return self::response_xml($cmd, $code, $message, $data);
			case 'array':
				return var_export(self::grant_array($cmd, $code, $message, $data));
			default:
				return self::response_json($cmd, $code, $message, $data);
		}
	}

	/*
	* 封装数为为json数据类型
	* @param integer $code 状态码
	* @param string $message 状态信息
	* @param array $data 数据
	* return string
	*/
	public static function response_json($cmd, $code, $message='', $data=array()){
		$result = self::grant_array($cmd, $code, $message, $data);
		//echo self::decodeUnicode(json_encode($result));
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/*
	* 封装数为为xml数据类型
	* @param integer $code 状态码
	* @param string $message 状态信息
	* @param array $data 数据
	* return string
	*/
	public static function response_xml($cmd, $code, $message='', $data=array()){

		$result = self::grant_array($cmd, $code, $message, $data);

		header("Content-Type:text/xml");
		$xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
		$xml .= "<root>\n";
		$xml .= self::xml_encode($result);
		$xml .= "</root>";
		return $xml;
	}

	/*
	* 将数组转换为XML格式
	* @param array $array 数组
	* return string
	*/
	private static function xml_encode($array=array()){
		$xml = $attr = "";

		if(!empty($array)){
			foreach ($array as $key => $value) {
				if(is_numeric($key)){
					$attr = " id='{$key}'";
					$key = "item";
				}
				$xml .= "<{$key}{$attr}>" ;
				$xml .= is_array($value) ? self::xml_encode($value) : $value;
				$xml .="</{$key}>\n";
			}
		}
		return $xml;
	}

	/*
	* 按照接口格式生成原数据数组
	* @param string $code 状态码
	* @param string $message 状态信息
	* @param array $data 数据
	* return array
	*/
	private static function grant_array($cmd, $code, $message='', $data=array()){
//		if(!is_numeric($code)) {
//			return '';
//		}
		$result = array(
		    'cmd' => $cmd,
			'errno' => $code,
			'msg' => $message
			);

		if(!empty($data)) {
			$result['data'] = self::type_conversion($data);
		}

		return $result;
	}

	/*
	 * 作用:将所有返回的数据强制转为字符串类型
	 * @param array $data
	 * @return array $data
	 * */
	private static function type_conversion($data)
	{
		if(is_array($data))
		{
			foreach($data as $key => $value)
			{
				$data[$key] = is_array($data[$key]) ? self::type_conversion($data[$key]) : (string)$data[$key];
			}
		}
		else
		{
			$data = (string)$data;
		}
		return $data;
	}

	/*
	 * 解决json_encode中文UNICODE转码问题
	 * */
	private static function decodeUnicode($str)
	{
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
			create_function(
				'$matches',
				'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
			),
			$str);
	}
}

