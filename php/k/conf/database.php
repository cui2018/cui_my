<?php
/* 
 * 数据库配置
 * @Package Name: database
 * @Author: Keboy xolox@163.com
 * @Modifications:No20171223
 *
 */

$config = array(
	//默认数据库
	'default' => array(
		'hostname' => ONLINE ? 'rm-m5e8930io3917v3nk.mysql.rds.aliyuncs.com' : 'rm-m5enf4d2o78qyk26w2o.mysql.rds.aliyuncs.com',
		'username' => ONLINE ? 'crxl2018' : 'crxl2018',
		'password' => ONLINE ? 'ASDasd19981018' : 'ASDasd19981018',
		'database' => ONLINE ? 'vgame' : 'vgame',
		'prefix' => 'vg_',
		'driver' => 'mysqli'
	)
);