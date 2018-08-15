<?php
define('V','2.5.0');
define('TEST',FALSE);
define('AUTHOR',1);
define('NOW_TIME',time());
define('WECHAT','test');
define('VERSION',TEST ? time() : '1.2.0');
define('JSSDKDEBUG',FALSE);
define('ROOT',TRUE);
define('SYNC',TRUE);
define('OSS',TRUE);
define('ADMIN','adm');
define('SERVER_IP',isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['SERVER_ADDR']);
define('SERVER_NAME',$_SERVER['HTTP_HOST']);
define('URL',(isset($_SERVER['REDIRECT_HTTPS']) && $_SERVER['REDIRECT_HTTPS'] == 'on' ? 'https://' : 'http://'). SERVER_NAME . $_SERVER['REQUEST_URI']);
define('ONLINE',SERVER_IP != '127.0.0.1' && !preg_match('/^192\.168\.\d{1,3}.\d{1,3}$/',SERVER_IP) && !preg_match('/^172\.16\.\d{1,3}.\d{1,3}$/',SERVER_IP));
define('ISWECHAT',isset($_SERVER['HTTP_USER_AGENT']) ? preg_match('/MicroMessenger/i',$_SERVER['HTTP_USER_AGENT']) : FALSE);
define('JSSERVER',FALSE);
date_default_timezone_set('PRC');
define('PROJECT',substr(dirname(__FILE__),(strrpos(dirname(__FILE__),'\\') ? strrpos(dirname(__FILE__),'\\') : strrpos(dirname(__FILE__),'/')) + 1));
define('NAME',ROOT ? 'vg' : PROJECT);
if ( TEST ) {
	error_reporting(E_ALL);
	ini_set('display_errors','1');
}
header('Content-Type:text/html;charset=utf-8');
require_once 'k/core.php';