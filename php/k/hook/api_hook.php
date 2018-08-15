<?php
/* 
 * 接口全局类
 * @Package Name: Api_hook
 * @Author: Keboy xolox@163.com
 * @Modifications:No20180725
 *
 */
class Api_hook extends Controller {
	//签名校验
	public function checkSign() {
		$data = post();
		if ( !array_value($data,'sign') ) {
			api_error(5004);
			exit;
		}
		if ( array_value($data,'info') ) return;
		$sign = $data['sign'];
		unset($data['sign']);
		$keys = array_keys($data);
		sort($keys);
		$param = array();
		foreach ( $keys as $key ) {
			array_push($param,$key . '=' . (is_string($data[$key]) || is_numeric($data[$key]) ? $data[$key] : md5(json_encode2($data[$key]))));
		}
		$signature = implode('&',$param);
		$signature .= '&key=Asc9C8Uv89I4gdNfsBG89dF8ds80VgYu';
		$signature = md5($signature);
		if ( $sign != $signature ) {
			api_error(5004);
			exit;
		}
	}
}