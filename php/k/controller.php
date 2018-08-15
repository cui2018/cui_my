<?php
/* 
 * 控制器父类
 * @Package Name: Controller
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */

class Controller {
	public static $data = array(
		'webconfig' => array()
	);
	public function __construct() {
		$redis = Load::redis();
		$setting = $redis->get("setting");
		//平台维护
		if( $setting['system_close'] == 0 ){
			if( $_SERVER['REQUEST_URI'] != '/login' ){
				//测试人
				$data = post();
				$openid = $data['openid'] ? $data['openid'] : $data['open_id'];
				$usertest = $redis->hget('usertest', $openid);
				if( !$usertest ) {
					api_error(10001);
					exit;
				}
			}
		}
	}
}