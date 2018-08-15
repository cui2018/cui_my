<?php
/* 
 * Curl类
 * @Package Name: Curl
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */
class Curl {
	//默认连接名
	public $name = 'default';
	//最高允许并发数
	public $concurrent = 0;
	//自动连接次数
	public $connection = 1;
	//超时时间
	public $timeout = 30;
	//数据格式
	public $datatype = 'array';
	//POST请求
	public function post($url,$data = '',$name = '',$concurrent = 0,$connection = 1,$timeout = 0,$datatype = 'array') {
		$setting = array();
		if ( is_array($url) ) $setting = $url;
		else {
			$setting['url'] = $url;
			$setting['data'] = $data;
			$setting['name'] = $name;
			$setting['concurrent'] = $concurrent;
			$setting['connection'] = $connection;
			$setting['timeout'] = $timeout;
			$setting['datatype'] = $datatype;
		}
		if ( !isset($setting['data']) ) $setting['data'] = '';
		if ( is_array($setting['data']) ) $setting['data'] = json_encode2($setting['data']);
		if ( !isset($setting['name']) || !$setting['name'] ) $setting['name'] = $this->name;
		if ( !isset($setting['concurrent']) || !$setting['concurrent'] ) $setting['concurrent'] = $this->concurrent;
		if ( !isset($setting['connection']) || !$setting['connection'] ) $setting['connection'] = $this->connection;
		if ( !isset($setting['timeout']) || !$setting['timeout'] ) $setting['timeout'] = $this->timeout;
		if ( !isset($setting['datatype']) || !$setting['datatype'] ) $setting['datatype'] = $this->datatype;
		for ( $i = 0; $i < $setting['connection']; $i++ ) {
			$return = $this->_send($setting);
			if ( $return ) {
				switch ( $setting['datatype'] ) {
					case 'text':
						return $return;
					case 'array':
						return json_decode($return,TRUE);
					case 'json':
						return json_encode(array(
							'status' => 1,
							'data' => $return
						));
					case 'jsonp':
						return get('callbackparam') . '(' . json_encode2(array(
							'status' => 1,
							'data' => addslashes($return)
						)) . ')';
				}
			}
		}
	}
	//发送请求
	private function _send($setting) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$setting['url']);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,$setting['timeout']);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$setting['data']);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		$return = curl_exec($ch);
		curl_close($ch);
		return $return;
	}
}