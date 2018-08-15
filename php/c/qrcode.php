<?php
class Qrcode extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
		$this->wechat = Load::redis('wechat');
		$this->config = Load::config();
		$this->curl = Load::library('curl');
		$this->oss = Load::oss();
	}
	//获取二维码
	public function index() {
		if ( !post('openid') ) return api_error(5012);
		else $this->_getqrcodephp(post());
	}
	//收集formid
	public function userformid() {
		$data = post();
		if ( array_isset($data,'formid') && array_isset($data,'openid') ) {
			if ( $data['formid'] && $data['formid'] != 'the formId is a mock one' ) rlog('addModelformid',array(
				'openid' => $data['openid'],
				'formid' => $data['formid']
			));
		}
		return api_success();
	}
	//获取二维码
	private function _getqrcodephp($data = array()) {
		$resuser = $this->redis->hget('user',$data['openid']);
		$resqrcode = $this->redis->hget('qrcode',$data['openid']);
		if ( !$resqrcode ) {
			$res = $this->_setQrCode('pages/index/index',$resuser['id']);
			$this->redis->hset('qrcode',$data['openid'],$res);
			return api_success($res);
		}
		else return api_success($resqrcode);
	}
	private function _setQrCode($page = 'pages/index/index',$scene = '') {
		$data = json_encode(array(
			'scene' => $scene,
			'page' => $page
		));
		$token = $this->_getToken();
		$fileData = $this->_send('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $token,$data);
		$fname = newOrderId('') . '.png';
		file_put_contents('./upload/qrcode/' . $fname,$fileData);
		$this->oss->upload('./upload/qrcode/' . $fname,'upload/qrcode/' . $fname);
		@unlink('./upload/qrcode/' . $fname);
		return array(
			'status' => 1,
			'data' => $fname
		);
	}
	private function _getToken() {
		$token = array();
		$wx = $this->wechat->hget('wechat','vgame');
		if ( $wx && ( time() - $wx['timestamp'] < 60 ) ) $token['access_token'] = $wx['access_token'] ? $wx['access_token'] : '';
		else {
			$token = $this->curl->post('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->config['wxapp']['appid'] . '&secret=' . $this->config['wxapp']['secret']);
			if ( array_isset($token,'access_token') ) {
				$token['timestamp'] = time();
				$token['state'] = 1;
				$this->wechat->hset('wechat','vgame',$token);
			}
			else return FALSE;
		}
		return $token['access_token'];
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
		return $return;
	}
}