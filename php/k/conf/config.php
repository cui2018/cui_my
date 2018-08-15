<?php
/* 
 * 全局配置
 * @Package Name: config
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */

$config = array(
	//允许的uri地址后缀
	'suffix' => array('.html','.do','.js','.css',''),
	//aes
	'aes' => array(
		'mode' => 'ecb',
		'cipher' => 128,
		'padding' => 'pkcs7',
		'key' => 'c8o060EpkW296da3',
		'iv' => 'y9Ns04Mkldd6es8P'
	),
	//默认允许上传文件类型
	'upload_allow' => array('jpg','png','gif','xls','xlsx','zip','mp3','mp4','swf','txt','doc','docx','sql'),
	//默认允许上传文件大小
	'upload_maxsize' => 300 * 1024 * 1024,
	//同步服务器
	'servers' => array(
		array(
			'hostname' => '172.31.158.226',
			'username' => 'vgame_res',
			'password' => 'fKJ5sTHGGy',
			'port' => 21
		)
	),
	//小程序服务配置
	'wxapp' => array(
		'token' => 'crxl2018',
		'encodingAESKey' => 'QdLEvPXSmdnOBAipuRM72glLhBsEhjnd5nnnlp3XzRH',
		'appid' => 'wx578f79064bf7b7ee',
		'secret' => '58ccafd9fce624ec205629bc46a7f7f3'
	),
	//百度接口配置
	'baidu' => array(
		'name' => 'xlcr',
		'appid' => '11170447',
		'apikey' => 'vFsCO4x33fuF5Rt0wjKpQpuf',
		'secret' => 'b1XRZ2GSw2QRASPnOnrG1Qn3GG4amgoC',
		'api' => array(
			'token' => 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id=vFsCO4x33fuF5Rt0wjKpQpuf&client_secret=b1XRZ2GSw2QRASPnOnrG1Qn3GG4amgoC',
			'spam' => 'https://aip.baidubce.com/rest/2.0/antispam/v2/spam'
		)
	)
);