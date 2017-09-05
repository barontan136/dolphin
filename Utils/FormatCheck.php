<?php
namespace Utils;
/*
 *
 * 格式检查类
 *
 * */
class FormatCheck
{
	private static $ins = NULL;
	private $doc_info;
	private $reg_mobile;
	private $user_id;
	private $salt;
	private $check_code;
	private $card_no;
	private $days;
	private $region;
	private $goods_id;
	private $gold_kg;
	private $gold_price;
	private $integral;
	private $coupon_sn;
	private $surplus;
	private $order_amount;
	private $card_id;
	private $order_id;
	private $rec_sn;
	private $yeecheck_code;
	private $pwd;
	private $id_no;

	final private function __construct() {
		$this->id_no = '/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/';
		$this->doc_info = '/^[a-zA-Z0-9][a-zA-Z0-9\_\-\*\+\@]{3,19}$/';
		$this->reg_mobile = '/^0?(13|14|15|17|18)[0-9]{9}$/';
		$this->user_id = '/^[1-9]\d{0,9}$/';
		$this->salt = '/^\d{4}$/';
		$this->check_code = '/^\d{4}$/';
		$this->card_no = '/^\d{16,19}$/';
		$this->days = '/^([12]{1}\d|[1-9])$/';
		$this->region = '/^\d{1,4}$/';
		$this->goods_id = '/^[1-9]\d{3,7}$/';
		$this->gold_kg = '/^[1-9]\d{0,4}$/';
		$this->gold_price = '/^(?:[0]\.[1-9]\d?|0\.0[1-9]|[1-9]\d{0,7}(?:\.\d{1,2})?)$/';
		$this->integral = '/^[1-9]\d{0,9}$/';
		$this->coupon_sn = '/^[\da-zA-Z]{10}$/';
		$this->surplus = '/^(?:[0]\.[1-9]\d?|0\.0[1-9]|[1-9]\d{0,7}(?:\.\d{1,2})?)$/';
		$this->order_amount = '/^(?:[0]\.[1-9]\d?|0\.0[1-9]|[1-9]\d{0,7}(?:\.\d{1,2})?)$/';
		$this->card_id = '/^[1-9]\d{0,9}$/';
		$this->order_id = '/^[1-9]\d{0,9}$/';
		$this->rec_sn = '/^\w{18}$/';
		$this->yeecheck_code = '/^\d{6}$/';
		$this->pwd = '/^(((?![^a-zA-Z]+$)(?!\D+$))|((?![^a-zA-Z]+$)(?![^\_\-\*\+\@]+$))|((?!\D+$)(?![^\_\-\*\+\@]+$)))[a-zA-Z0-9\-\_\+\@\*]{6,16}$/';
	}


	public static function getInstance()
	{
		// if(!self::$ins instanceof self) {
		// 	self::$ins = new self;
		// }
		// return self::$ins;
        return new self();
	}

	public function __get($property)
	{
		if(isset($this->$property))
			return $this->$property;
		return null;
	}

    public function check_preg_match($exp, $check_data)
	{
		return preg_match($exp, $check_data);
	}



	/*
	 * 作用：检查参数格式方法
	 * @param  array $check_arr  检测类型的关联数组
	 * @param  array $code_arr   检测错误信息
	 * @return 检测成功返回true,否则返回对应的错误码
	 * */
	public function check_data_format($check_arr, $code_arr)
	{
		foreach($check_arr as $key => $value)
		{
			if(empty($value) || (!$this->check_preg_match($this->$key, $value)))
			{
				foreach($code_arr as $err_key => $err_no)
				{
					if($key == $err_key)
					{
						return $err_no;
					}
				}
			}
		}
		return TRUE;
	}












}

?>