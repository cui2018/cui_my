<?php
/* 
 * Cookie类
 * @Package Name: Cookie
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */
class Cookie {
	//超时时间，默认一周
	public $expire = 604800;
	//目录
	public $path = '/';
	//域名
	public $domain = '';
	public function __construct() {
		$this->path = '/' . (ROOT ? '' : PROJECT . '/');
	}
	//获取cookie
	public function get($name) {
		$name = strtoupper('k_' . NAME . '_' . $name);
		$value = array_value($_COOKIE,$name);
		$v = json_decode(stripslashes($value),TRUE);
		$value = is_array($v) ? $v : $value;
		return $value;
	}
	//设置cookie
	public function set($name,$value = '',$expire = '',$path = '',$domain = '') {
		$name = strtoupper('k_' . NAME . '_' . $name);
		$expire = $expire ? $expire : $this->expire;
		$path = $path ? $path : $this->path;
		$domain = $domain ? $domain : $this->domain;
		$v = is_array($value) ? json_encode2($value) : $value;
		setcookie($name,$v,time() + $expire,$path,$domain);
		return $value;
	}
	//清除cookie
	public function delete($name) {
		$this->set($name,'',-3600);
	}
	//清空cookie
	public function clear($path = '',$domain = '') {
		$path = $path ? $path : $this->path;
		$domain = $domain ? $domain : $this->domain;
		foreach ($_COOKIE as $key => $item){
			setcookie($key,'',-3600,$path,$domain);
		}
	}
}