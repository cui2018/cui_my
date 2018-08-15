<?php
/* 
 * 缓存类
 * @Package Name: Redises
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */
class Redises {
	public $conn;
	//构造创建连接
	public function __construct($config) {
		$this->config = $config;
		$this->conn = new Redis();
		$this->conn->connect($config['hostname'],$config['port']);
		if ( array_value($config,'password') ) $this->conn->auth($config['password']);
	}
	public function hkeys($key) {
		return $this->conn->hKeys($this->config['prefix'] . $key);
	}
	public function exists($key) {
		return $this->conn->exists($this->config['prefix'] . $key);
	}
	public function get($key) {
		$value = $this->conn->get($this->config['prefix'] . $key);
		if ( is_numeric($value) ) $value = intval($value);
		if ( is_array(json_decode($value,TRUE)) ) $value = json_decode($value,TRUE);
		return $value;
	}
	public function set($key,$value) {
		if ( is_array($value) ) $value = json_encode2($value);
		return $this->conn->set($this->config['prefix'] . $key,$value);
	}
	public function del($key) {
		$this->conn->del($this->config['prefix'] . $key);
	}
	public function hexists($table,$key) {
		return $this->conn->hexists($this->config['prefix'] . $table,$key);
	}
	public function hget($table,$key) {
		$value = $this->conn->hget($this->config['prefix'] . $table,$key);
		if ( is_numeric($value) ) $value = intval($value);
		if ( is_array(json_decode($value,TRUE)) ) $value = json_decode($value,TRUE);
		return $value;
	}
	public function hset($table,$key,$value) {
		if ( is_array($value) ) $value = json_encode2($value);
		return $this->conn->hset($this->config['prefix'] . $table,$key,$value);
	}
	public function hdel($table,$key) {
		$this->conn->hdel($this->config['prefix'] . $table,$key);
	}
	public function hsetall($table,$data) {
		foreach ( $data as $key => $item ) {
			$this->hset($table,$key,$item);
		}
	}
	public function hgetall($table) {
		$keys = $this->hkeys($table);
		$list = array();
		foreach ( $keys as $key ) {
			$list[$key] = $this->hget($table,$key);
		}
		return $list;
	}
	public function hgetallvalue($table) {
		$keys = $this->hkeys($table);
		$data = array();
		foreach ( $keys as $key ) {
			array_push($data,$this->hget($table,$key));
		}
		return $data;
	}
	public function lpush($key,$value) {
		if ( is_array($value) ) $value = json_encode2($value);
		return $this->conn->lPush($this->config['prefix'] . $key,$value);
	}
	public function rpush($key,$value) {
		if ( is_array($value) ) $value = json_encode2($value);
		return $this->conn->rPush($this->config['prefix'] . $key,$value);
	}
	public function lpop($key) {
		$value = $this->conn->lPop($this->config['prefix'] . $key);
		if ( is_numeric($value) ) $value = intval($value);
		if ( is_array(json_decode($value,TRUE)) ) $value = json_decode($value,TRUE);
		return $value;
	}
	public function rpop($key) {
		$value = $this->conn->rPop($this->config['prefix'] . $key);
		if ( is_numeric($value) ) $value = intval($value);
		if ( is_array(json_decode($value,TRUE)) ) $value = json_decode($value,TRUE);
		return $value;
	}
	public function lrange($key,$start = 0,$length = 1000) {
		$value = $this->conn->lRange($this->config['prefix'] . $key,$start,$length);
		foreach ($value as &$item) {
			if ( is_numeric($item) ) $item = intval($item);
			if ( is_array(json_decode($item,TRUE)) ) $item = json_decode($item,TRUE);
		}
		return $value;
	}
	public function incr($key) {
		return $this->conn->incr($this->config['prefix'] . $key);
	}
	public function incrBy($key,$value) {
		return $this->conn->incrBy($this->config['prefix'] . $key,$value);
	}
	public function decr($key) {
		return $this->conn->decr($this->config['prefix'] . $key);
	}
	public function decrBy($key,$value) {
		return $this->conn->decrBy($this->config['prefix'] . $key,$value);
	}
}