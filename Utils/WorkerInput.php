<?php
namespace Utils;

class WorkerInput{
    protected $_data = null;
    protected $_client = null;

    public function __construct($data, $clientData = []) {
        $this->_data = $data;
        $this->_client = $clientData;
    }

    public function getData() {
        return $this->_data;
    }

    /**
     * 获取输入参数 支持过滤和默认值
     * 使用方法:
     * <code>
     * I('id',0); 获取id参数 自动判断get或者post
     * I('name/s','','htmlspecialchars');
     *     获取属性:name,如果不存在返回'',如果存在用 htmlspecialchars 过滤
     * </code>
     * @param string $name 变量的名称 支持指定类型
     * @param mixed $default 不存在的时候默认值
     * @param mixed $filter 参数过滤方法
     * @return mixed
     */
    public function get($name, $default=null, $filter=null, ...$args) {
        $type = 'non';
        if (strpos($name,'/')) { // 指定修饰符
            list($name,$type) 	=	explode('/',$name,2);
        }

        if (isset($this->_data[$name])) { // 取值操作
            $data       =   $this->_data[$name];
            $filters    =   isset($filter)?$filter:null;
            if ($filters) {
                if (is_string($filters)) {
                    if(0 === strpos($filters,'/')){
                        if(1 !== preg_match($filters,(string)$data)){
                            // 支持正则验证
                            return isset($default) ? $default : null;
                        }
                    }else{
                        $filters = explode(',',$filters);
                    }
                } elseif(is_int($filters)) {
                    $filters = array($filters);
                }

                if (is_array($filters)) {
                    foreach ($filters as $filter) {
                        if (function_exists($filter)) {
                            // $data = is_array($data)
                            //       ? array_map_recursive($filter, $data)
                            //       : $filter($data); // 参数过滤
                            if (!empty($args) && is_array($args)) {
                                $data = $filter($data, ...$args); // 参数过滤
                            } else {
                                $data = $filter($data); // 参数过滤
                            }
                        } else {
                            $data = filter_var(
                                $data,is_int($filter)
                                ? $filter
                                : filter_id($filter)
                            );
                            if(false === $data) {
                                return isset($default) ? $default : null;
                            }
                        }
                    }
                }
            }
            if (!empty($type)) {
                switch(strtolower($type)) {
        		case 'a':	// 数组
        			$data =	(array)$data;
        			break;
        		case 'd':	// 数字
        			$data =	(int)$data;
        			break;
        		case 'f':	// 浮点
        			$data =	(float)$data;
        			break;
        		case 'b':	// 布尔
        			$data =	(boolean)$data;
        			break;
                case 's':   // 字符串
                    $data = (string)$data;
                    break;
                }
            }
        } else { // 变量默认值
            $data = $default;
        }
        return $data;
    }

    public function set($name, $value) {
        $this->_data[$name] = $value;
    }

    /**
     * 获取客户端信息
     */
    public function getClientData() {
        return $this->_client;
    }

    public function getClient($name, $default = null) {
        return is_array($this->_client) && array_key_exists($name, $this->_client)
            ? $this->_client[$name]
            : $default;
    }

    public function setClient($name, $value) {
        if (!is_array($this->_client)) {
            $this->_client = [];
        }
        $this->_client[$name] = $value;
    }
}
