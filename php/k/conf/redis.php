<?php
/* 
 * 缓存配置
 * @Package Name: redis
 * @Author: Keboy xolox@163.com
 * @Modifications:No20171223
 *
 */

$config = array(
	//默认缓存
	'default' => array(
		'hostname' => ONLINE ? 'r-m5e1afb96f73a5a4.redis.rds.aliyuncs.com' : 'localhost',
		'password' => 'ASDasd19981018',
		'port' => ONLINE ? 6379 : 6379,
		'prefix' => 'VG::'
	),
	//微信授权平台
	'wechat' => array(
		'hostname' => ONLINE ? 'r-m5e46d33484f2124.redis.rds.aliyuncs.com' : 'localhost',
		'password' => 'ASDasd19981018',
		'port' => ONLINE ? 6379 : 6379,
		'prefix' => 'O2::'
	)
);