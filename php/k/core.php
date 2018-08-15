<?php
/* 
 * 系统核心
 * @Package Name: core
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */
require_once 'error.php';
require_once 'fn.php';
require_once 'loader.php';
Load::hook('pre_system');
require_once 'controller.php';
require_once 'uri.php';
Load::hook('post_system');