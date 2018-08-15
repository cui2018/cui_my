<?php
class Login extends Controller {
	public function index() {
		$config = Load::config();
		$curl = Load::library('curl');
		$code = post('code');
		$res = $curl->post('https://api.weixin.qq.com/sns/jscode2session?appid=' . $config['wxapp']['appid'] . '&secret=' . $config['wxapp']['secret'] . '&js_code=' . $code . '&grant_type=authorization_code');
		if ( $res['errcode'] ) return api_error(4101,$res);
		$redis = Load::redis();
		$resnewhand = $redis->hget('newhand',$res['openid']);
		$resnewhand = $resnewhand ? 1 : 0;
		$redis->hset('user_session_key',$res['openid'],$res['session_key']);
		$user = $redis->hget('user',$res['openid']);
		if ( $user ) {
			$redis->hset('user',$res['openid'],$user);
			rlog('login',array(
				'openid' => $res['openid']
			));
		}
		else {
			$user = array(
				'id' => 0,
				'openid' => $res['openid'],
				'tel' => '',
				'nick' => '',
				'headimg' => '',
				'sex' => 0,
				'country' => '',
				'province' => '',
				'city' => '',
				'viptime' => 0,
				'viptype' => 0,
				'recharge' => 0,
				'oauth' => 0
			);
			$redis->hset('user',$res['openid'],$user);
			rlog('regist',array(
				'openid' => $res['openid']
			));
		}
		$setting = $redis->get('setting');
		$setting = $setting ? $setting : array();
		if( $setting['system_close'] == 1 ) return api_success(array(
			'openid' => $res['openid'],
			'oauth' => intval($user['oauth']),
			'info' => $user,
			'setting' => $setting,
			'newhand_state' => $resnewhand
		));
		else{
			//测试人员
			$usertest = $redis->hget('usertest', $res['openid']);
			if( !$usertest ) return api_error(10001);
			return api_success(array(
				'openid' => $res['openid'],
				'oauth' => intval($user['oauth']),
				'info' => $user,
				'setting' => $setting,
				'newhand_state' => $resnewhand
			));
		}
	}
	public function setInfo() {
		$data = post();
		if ( !$data['openid'] ) return api_error(5001);
		$redis = Load::redis();
		$session_key = $redis->hget('user_session_key',$data['openid']);
		if ( !$session_key ) return api_error(5004,'aa');
		$signature = sha1($data['rawData'] . $session_key);
		if ( $signature != $data['signature'] ) return api_error(5004,'bb');
		$info = $this->_decrypt($data['encryptedData'],$session_key,$data['iv']);
		if ( !$info ) return api_error(5004);
		$user = $redis->hget('user',$data['openid']);
		if ( !$user ) return api_error(5006);
		$user['id'] = intval($user['id']);
		$user['unionid'] = $info['unionId'];
		$user['nick'] = delslashes($data['info']['nickName']);
		$user['headimg'] = $data['info']['avatarUrl'];
		$user['sex'] = intval($data['info']['gender']);
		$user['country'] = delslashes($data['info']['country']);
		$user['province'] = delslashes($data['info']['province']);
		$user['city'] = delslashes($data['info']['city']);
		$user['viptime'] = intval($user['viptime']);
		$user['viptype'] = intval($user['viptype']);
		$user['recharge'] = intval($user['recharge']);
		$user['oauth'] = 1;
		$redis->hset('user',$data['openid'],$user);
		$data['info']['nickName'] = delslashes($data['info']['nickName']);
		rlog('setUserInfo',array(
			'openid' => $data['openid'],
			'unionid' => $info['unionId'],
			'info' => $data['info']
		));
		api_success($user);
	}
	private function _decrypt($data,$key,$iv) {
		$data = base64_decode($data);
		$key = base64_decode($key);
		$iv = base64_decode($iv);
		$result = openssl_decrypt($data,'AES-128-CBC',$key,1,$iv);
		$dataObj = json_decode($result,TRUE);
		return $dataObj;
	}
}