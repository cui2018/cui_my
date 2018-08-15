<?php
/* 
 * 百度类
 * @Package Name: Baidu
 * @Author: Keboy xolox@163.com
 * @Modifications:No20180725
 *
 */
class Baidu {
	public function __construct() {
		$this->config = Load::config();
	}
	public function getToken() {
		$redis = Load::redis('wechat');
		$token = $redis->hget('baidu',$this->config['baidu']['name']);
		if ( !$token || time() - $token['time'] > $token['expires_in'] - 86400 ) {
			$res = $this->_send($this->config['baidu']['api']['token']);
			if ( $res['access_token'] ) {
				$res['time'] = time();
				$redis->hset('baidu',$this->config['baidu']['name'],$res);
				return $res;
			}
		}
		else return $token;
	}
	public function spam($chat = '') {
		$token = $this->getToken();
		$res = $this->_send($this->config['baidu']['api']['spam'] . '?access_token=' . $token['access_token'],array(
			'content' => $chat
		));
		if ( $res['result'] && $res['result']['spam'] == 0 ) return array(
			'status' => 1
		);
		elseif ( $res['result'] && $res['result']['spam'] > 0 ) return array(
			'status' => 0,
			'spam' => $res['result']['spam']
		);
		else return array(
			'status' => 1,
			'spam' => -1
		);
	}
	//发送请求
	private function _send($url,$data) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,30);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		$return = curl_exec($ch);
		curl_close($ch);
		return json_decode($return,TRUE);
	}
}