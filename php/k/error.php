<?php
/* 
 * 错误提示函数
 * @Package Name: error
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */

/**
 * Method:show_404() 404错误
 */
if ( !function_exists('show_404') ) {
	function show_404() {
		@ob_clean();
		require_once 'k/err/404.php';
		exit;
	}
}
/**
 * Method:show_error() 错误
 */
if ( !function_exists('show_error') ) {
	function show_error($msg = '',$header = '友情提示~！') {
		@ob_clean();
		require_once 'k/err/error.php';
		exit;
	}
}
/**
 * Method:show_notice() 提示
 */
if ( !function_exists('show_notice') ) {
	function show_notice($msg = '',$header = '友情提示~！') {
		@ob_clean();
		require_once 'k/err/notice.php';
		exit;
	}
}
/**
 * Method:show_admin_error() 后台错误
 */
if ( !function_exists('show_admin_error') ) {
	function show_admin_error($msg = '',$op = '') {
		@ob_clean();
		require_once 'k/err/admin/error.php';
		exit;
	}
}
/**
 * Method:show_admin_notice() 后台提示
 */
if ( !function_exists('show_admin_notice') ) {
	function show_admin_notice($msg = '',$op = '') {
		@ob_clean();
		require_once 'k/err/admin/notice.php';
		exit;
	}
}
/**
 * Method:show_admin_op() 后台操作
 */
if ( !function_exists('show_admin_op') ) {
	function show_admin_op($arr = array(),$jsapi = '',$data = '') {
		@ob_clean();
		require_once 'k/err/admin/op.php';
		exit;
	}
}