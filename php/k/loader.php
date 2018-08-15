<?php
/* 
 * 加载器
 * @Package Name: load
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */

class Load {
	//加载配置文件列表
	public static $configs = array();
	//加载数据库列表
	public static $databases = array();
	//加载缓存列表
	public static $redises = array();
	//加载类列表
	public static $libraries = array();
	//加载oss列表
	public static $osses = array();
	//读取配置文件
	public static function config($name = '') {
		if ( !$name ) $name = 'config';
		if ( isset(self::$configs[$name]) ) return self::$configs[$name];
		elseif ( file_exists('k/conf/' . $name . '.php') ) {
			require_once 'k/conf/' . $name . '.php';
			if ( isset($config) ) {
				self::$configs[$name] = $config;
				return $config;
			}
			else return array();
		}
		else return array();
	}
	//读取数据库
	public static function database($name = 'default') {
		$config = self::config('database');
		if ( isset($config[$name]) ) {
			if ( isset(self::$databases[$name]) ) return self::$databases[$name];
			if ( !self::$databases && file_exists('k/lib/database-' . $config[$name]['driver'] . '.php') ) require_once 'k/lib/database-' . $config[$name]['driver'] . '.php';
			self::$databases[$name] = new Database($config[$name]);
			return self::$databases[$name];
		}
		else show_error('数据库连接失败，配置文件不正确');
	}
	//读取缓存
	public static function redis($name = 'default') {
		$config = self::config('redis');
		if ( isset($config[$name]) ) {
			if ( isset(self::$redises[$name]) ) return self::$redises[$name];
			if ( !self::$redises && file_exists('k/lib/redises.php') ) require_once 'k/lib/redises.php';
			self::$redises[$name] = new Redises($config[$name]);
			return self::$redises[$name];
		}
		else show_error('缓存连接失败，配置文件不正确');
	}
	//读取类
	public static function library($name = '',$param = array()) {
		if ( isset(self::$libraries[$name]) ) return self::$libraries[$name];
		elseif ( $name && file_exists('k/lib/' . $name . '.php') ) {
			require_once 'k/lib/' . $name . '.php';
			$class = ucfirst($name);
			self::$libraries[$name] = new $class($param);
			return self::$libraries[$name];
		}
		else show_error('类文件不存在');
	}
	//读取视图
	public static function view($file = '',$data = array()) {
		if ( $file && file_exists('v/' . $file) ) {
			extract($data);
			require_once 'v/' . $file;
		}
		else show_error('视图文件不存在');
	}
	//读取CSS
	public static function css($file = '',$data = array()) {
		if ( $file && file_exists('css/' . $file . '.css') ) {
			extract($data);
			header('Content-type:text/css;charset=utf-8');
			require_once 'css/' . $file . '.css';
		}
		else show_error('CSS文件不存在');
	}
	//读取钩子
	public static function hook($mode = '') {
		$config = self::config('hook');
		$path = get('uri');
		$path = explode('?',$path);
		$path = $path[0];
		if ( isset($config[$mode]) ) foreach ($config[$mode] as $hook) {
			if ( (isset($hook['uri']) && $hook['uri'] && preg_match($hook['uri'],$path)) || (!isset($hook['uri']) || !$hook['uri']) ) {
				if ( file_exists('k/hook/' . $hook['class'] . '.php') ) {
					require_once 'k/hook/' . $hook['class'] . '.php';
					$class = ucfirst($hook['class']);
					$hookClass = new $class();
					if ( !isset($hook['method']) || !$hook['method'] ) $hook['method'] = 'index';
					$hookClass->$hook['method']();
				}
				else show_error('钩子文件不存在');
			}
		}
	}
	//读取辅助函数
	public static function fn($file = '') {
		if ( $file && file_exists('k/fn/' . $file . '.php') ) require_once 'k/fn/' . $file . '.php';
		else show_error('辅助函数文件不存在');
	}
	//读取oss
	public static function oss($name = 'default') {
		$config = self::config('oss');
		if ( isset($config[$name]) ) {
			if ( isset(self::$osses[$name]) ) return self::$osses[$name];
			if ( !self::$osses && file_exists('k/lib/osslib.php') ) require_once 'k/lib/osslib.php';
			self::$osses[$name] = new Osslib($config[$name]);
			return self::$osses[$name];
		}
		else show_error('oss连接失败，配置文件不正确');
	}
}