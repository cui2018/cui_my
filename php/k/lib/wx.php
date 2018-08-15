<?php
/* 
 * 微信核心类
 * @Package Name: Wx
 * @Author: Keboy xolox@163.com
 * @Modifications:No20171117
 *
 */
class Wx extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
		$this->curl = Load::library('curl');
	}
	//微信jssdk
	public function jssdk() {
		echo '<script src="' . ufv('/js/share.js') . '"></script>' . "\r\n";
		if ( !ONLINE ) return;
		if ( !ISWECHAT ) return;
		$wechat = Load::config('wechat');
		$wechat = $wechat['crwy'];
		$wx = $this->redis->hget('wechat','crwy');
		$token = array();
		if ( $wx && ( NOW_TIME - $wx['timestamp'] < $wx['expires_in'] - 3600 ) ) {
			$token['timestamp'] = array_value($wx,'timestamp');
			$token['access_token'] = array_value($wx,'access_token');
			$token['ticket'] = array_value($wx,'ticket');
		}
		else {
			$token = $this->curl->post('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $wechat['appid'] . '&secret=' . $wechat['secret']);
			if ( array_isset($token,'access_token') ) $token['timestamp'] = NOW_TIME;
			else show_error('抱歉，微信jssdk加载失败，请刷新重新打开~');
		}
		$ticket = array_value($token,'ticket');
		if ( !$ticket ) {
			$getTicket = $this->curl->post('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $token['access_token'] . '&type=jsapi');
			if ( array_isset($getTicket,'ticket') ) {
				$token['ticket'] = $getTicket['ticket'];
				$this->redis->hset('wechat','crwy',$token);
				$ticket = $token['ticket'];
			}
			else show_error('抱歉，微信ticket加载失败，请刷新重新打开~');
		}
		$s = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$noncestr = '';
		for ( $i = 0; $i < 16; $i++ ) $noncestr .= substr($s,mt_rand(0,strlen($s)),1);
		$signature = sha1('jsapi_ticket=' . $ticket . '&noncestr=' . $noncestr . '&timestamp=' . $token['timestamp'] . '&url=' . URL);
		echo '<script src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>' . "\r\n";
		echo '<script src="' . ufv('/js/jssdk.js') . '"></script>' . "\r\n";
		echo '<script>' . "\r\n";
		echo 'wechat.config.debug = ' . (JSSDKDEBUG ? 'true' : 'false') . ';' . "\r\n";
		echo 'wechat.config.appId = "' . $wechat['appid'] . '";' . "\r\n";
		echo 'wechat.config.timestamp = "' . $token['timestamp'] . '";' . "\r\n";
		echo 'wechat.config.nonceStr = "' . $noncestr . '";' . "\r\n";
		echo 'wechat.config.signature = "' . $signature . '";' . "\r\n";
		echo 'if ( typeof wx != "undefined" ) {' . "\r\n";
		echo '	wx.config(wechat.config);' . "\r\n";
		echo '	wx.ready( function() {' . "\r\n";
		echo '		wechat.configLoaded = true;' . "\r\n";
		echo '		wx.hideOptionMenu();' . "\r\n";
		echo '		wechat.callFunctions();' . "\r\n";
		echo '	} );' . "\r\n";
		echo '}' . "\r\n";
		echo '</script>' . "\r\n";
	}
}