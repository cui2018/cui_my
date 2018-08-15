<?php
/* 
 * URI解析
 * @Package Name: uri
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */

class Uri {
	public static $class = 'index';
	public static $method = 'index';
	public static $param = array();
	public static $c;
	public function __construct() {
		$this->path = get('uri');
      	$this->path = explode('?',$this->path);
      	$this->path = $this->path[0];
		$this->config = Load::config();
		$this->_checkSuffix();
		$this->_pathSplit();
		$this->_init();
	}
	//检验后缀
	private function _checkSuffix() {
		foreach ($this->config['suffix'] as $suffix) if ( substr($this->path,-strlen($suffix),strlen($suffix)) == $suffix ) {
			$this->path = substr($this->path,1,strlen($this->path) - strlen($suffix) - 1);
			if (substr($this->path,-1,1) == '/') $this->path = substr($this->path,0,strlen($this->path) - 1);
			return;
		}
		show_404();
	}
	//拆分路径
	private function _pathSplit() {
		$arr = $this->path;
		$this->path = array();
		if ( $arr ) foreach (explode('/',$arr) as $item) array_push($this->path,$item);
	}
	//初始化
	private function _init() {
		$path = 'c/';
		$find = 1;
		Load::hook('pre_controller');
		if ( $this->path ) foreach ($this->path as $item) {
			if ( $find == 1 ) {
				if ( file_exists($path . $item . '.php') ) {
					self::$class = $item;
					require_once $path . $item . '.php';
					$class = ucfirst($item);
					if ( class_exists($class) ) self::$c = new $class();
					else show_404();
					$find = 2;
				}
				elseif ( is_dir($path . $item . '/') ) $path .= $item . '/';
				else {
					if ( file_exists($path . self::$class . '.php') ) {
						require_once $path . self::$class . '.php';
						$class = ucfirst(self::$class);
						if ( class_exists($class) ) {
							self::$c = new $class();
							if ( !method_exists(self::$c,self::$method) ) show_404();
							array_push(self::$param,$item);
							$find = 3;
						}
						else show_404();
					}
					else show_404();
				}
			}
			elseif ( $find == 2 ) {
				if ( method_exists(self::$c,$item) ) {
					self::$method = $item;
					$find = 3;
				}
				elseif ( method_exists(self::$c,'index') ) {
					self::$method = 'index';
					array_push(self::$param,$item);
					$find = 3;
				}
				else show_404();
			}
			elseif ( $find == 3 ) array_push(self::$param,$item);
		}
		else {
			if ( file_exists($path . self::$class . '.php') ) {
				require_once $path . self::$class . '.php';
				$class = ucfirst(self::$class);
				if ( class_exists($class) ) {
					self::$c = new $class();
					if ( !method_exists(self::$c,self::$method) ) show_404();
				}
				else show_404();
			}
			else show_404();
		}
		Load::hook('post_controller_constructor');
		if ( !method_exists(self::$c,self::$method) ) show_404();
		call_user_func_array(array(
			self::$c,
			self::$method
		),self::$param);
		Load::hook('post_controller');
	}
}
new Uri();