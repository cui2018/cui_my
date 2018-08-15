<?php
/* 
 * 钩子配置
 * @Package Name: hook
 * @Author: Keboy xolox@163.com
 * @Modifications:No20160629
 *
 */

/*
 * 配置说明
 *   pre_system 在系统执行的早期调用，这个时候只有 基础类 和 钩子类 被加载了， 还没有执行到路由或其他的流程。
 *   pre_controller 在你的控制器调用之前执行，所有的基础类都已加载，路由和安全检查也已经完成。
 *   post_controller_constructor 在你的控制器实例化之后立即执行，控制器的任何方法都还尚未调用。
 *   post_controller 在你的控制器完全运行结束时执行。
 *   post_system 在最终的页面发送到浏览器之后、在系统的最后期被调用。
 *
 * $config['pre_system'][] = array(
 *   'class' => '钩子类名',
 *   'method' => '钩子方法名',
 *   'uri' => '路由检查'
 * );
 *
 * class:钩子类名对应k/hook/对应类名.php
 * method:钩子方法名，可选，默认为index方法
 * uri:钩子作用域，格式为uri的正则表达式，可选，默认为空。如，非/adm/开头的路径写为“/^(?!\/adm\/)/”
 */

$config['post_controller_constructor'][] = array(
	'class' => 'api_hook',
	'method' => 'checkSign',
	'uri' => ''
);