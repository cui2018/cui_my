<?php
/* 
 * 核心函数
 * @Package Name: fn
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */

/**
 * Method:u() URL地址
 * Parameter:
 *   url(string) -- 地址
 *   params(array|string) -- 代入get参数，多个参数可采用数组
 */
if ( !function_exists('u') ) {
	function u($url,$params = array()) {
		$param = array();
		if ( is_array($params) ) foreach ($params as $key) array_push($param,$key . '=' . get($key));
		elseif ( $params ) array_push($param,$params . '=' . get($params));
		if ( $param ) {
			$param = implode('&',$param);
			$param = ( strstr($url,'?') ? '&' : '?' ) . $param;
		}
		else $param = '';
		return (ROOT ? '' : ('/' . PROJECT)) . $url . $param;
	}
}
/**
 * Method:uv() URL地址
 * Parameter:
 *   url(string) -- 地址
 *   params(array|string) -- 代入get参数，多个参数可采用数组
 */
if ( !function_exists('uv') ) {
	function uv($url,$params = array()) {
		$param = '';
		if ( is_array($params) ) foreach ($params as $key) $param .= '&' . $key . '=' . get($key);
		elseif ( $params ) $param = '&' . $params . '=' . get($params);
		$param = ( strstr($url,'?') ? '&' : '?' ) . 'v=' . VERSION . $param;
		return (ROOT ? '' : ('/' . PROJECT)) . $url . $param;
	}
}
/**
 * Method:uuv() 上传文件URL地址
 * Parameter:
 *   url(string) -- 地址
 *   params(array|string) -- 代入get参数，多个参数可采用数组
 */
if ( !function_exists('uuv') ) {
	function uuv($url,$params = array()) {
		$param = '';
		if ( is_array($params) ) foreach ($params as $key) $param .= '&' . $key . '=' . get($key);
		elseif ( $params ) $param = '&' . $params . '=' . get($params);
		$param = ( strstr($url,'?') ? '&' : '?' ) . 'v=' . VERSION . $param;
		if ( substr($url,0,7) == 'http://' || substr($url,0,8) == 'https://' ) return $url . $param;
		else return (ONLINE ? 'https://vgame-cdn.edisonluorui.com' : '') . (ROOT ? '' : ('/' . PROJECT)) . '/upload' . $url . $param;
	}
}
/**
 * Method:uugv() 本地上传文件URL地址
 * Parameter:
 *   url(string) -- 地址
 *   version(string) -- 版本号
 *   params(array|string) -- 代入get参数，多个参数可采用数组
 */
if ( !function_exists('uugv') ) {
	function uugv($url,$version = '',$params = array()) {
		$param = '';
		if ( is_array($params) ) foreach ($params as $key) $param .= '&' . $key . '=' . get($key);
		elseif ( $params ) $param = '&' . $params . '=' . get($params);
		$param = ( strstr($url,'?') ? '&' : '?' ) . 't=' . $version . $param;
		return (ROOT ? '' : ('/' . PROJECT)) . '/upload' . $url . $param;
	}
}
/**
 * Method:ua() 后台URL地址
 * Parameter:
 *   url(string) -- 地址
 *   params(array|string) -- 代入get参数，多个参数可采用数组
 */
if ( !function_exists('ua') ) {
	function ua($url,$params = array()) {
		$param = array();
		if ( is_array($params) ) foreach ($params as $key) array_push($param,$key . '=' . get($key));
		elseif ( $params ) array_push($param,$params . '=' . get($params));
		if ( $param ) {
			$param = implode('&',$param);
			$param = ( strstr($url,'?') ? '&' : '?' ) . $param;
		}
		else $param = '';
		return (ROOT ? '/' . ADMIN : ('/' . PROJECT . '/' . ADMIN)) . $url . $param;
	}
}
/**
 * Method:uvt() URL地址禁用缓存
 * Parameter:
 *   url(string) -- 地址
 *   params(array|string) -- 代入get参数，多个参数可采用数组
 */
if ( !function_exists('uvt') ) {
	function uvt($url,$params = array()) {
		$param = '';
		if ( is_array($params) ) foreach ($params as $key) $param .= '&' . $key . '=' . get($key);
		elseif ( $params ) $param = '&' . $params . '=' . get($params);
		$param = ( strstr($url,'?') ? '&' : '?' ) . 'v=' . time() . $param;
		return (ROOT ? '' : ('/' . PROJECT)) . $url . $param;
	}
}
/**
 * Method:ufv() URL地址
 * Parameter:url(string) -- 地址
 */
if ( !function_exists('ufv') ) {
	function ufv($url) {
		if ( ONLINE ) return 'https://vgame-cdn.edisonluorui.com' . $url . '?v=' . VERSION;
		else return 'http://video.cdn' . $url . '?v=' . VERSION;
	}
}
/**
 * Method:ujv() URL地址
 * Parameter:url(string) -- 地址
 */
if ( !function_exists('ujv') ) {
	function ujv($url) {
		if ( ONLINE ) return 'https://vgame-cdn.edisonluorui.com' . $url . '?v=' . VERSION;
		else return 'http://video.cdn' . $url . '?v=' . VERSION;
	}
}
/**
 * Method:ucv() URL地址
 * Parameter:url(string) -- 地址
 */
if ( !function_exists('ucv') ) {
	function ucv($url) {
		if ( ONLINE ) return 'https://vgame-cdn.edisonluorui.com' . $url . '?v=' . VERSION;
		else return 'http://video.cdn' . $url . '?v=' . VERSION;
	}
}
/**
 * Method:redirect() 重定向URL
 * Parameter:url(string) -- 转向地址，设置为特定值时，跳转到特定地址
 */
if ( !function_exists('redirect') ) {
	function redirect($url) {
		switch ($url) {
			case 'refer':	//跳转到上一个页面
				$url = $_SERVER['HTTP_REFERER'];
				break;
		}
		header("location:$url");
		exit;
	}
}
/**
 * Method:printr() 调试用的打印print_r代码格式
 * Parameter:
 *   item(array) -- 数组，对象等
 *   die(boolean) -- 是否开启exit
 */
if ( !function_exists('printr') ) {
	function printr($item = array(),$die = 1) {
		@ob_clean();
		if ( $die ) {
			echo '<!doctype html>' . "\n";
			echo '<html>' . "\n";
			echo '<head>' . "\n";
			echo '<meta charset="utf-8">' . "\n";
			echo '<title>printr</title>' . "\n";
			echo '</head>' . "\n";
			echo '<body>' . "\n";
		}
		echo '<pre>' . "\n";
		print_r($item);
		echo "\n" . '</pre>' . "\n";
		if ( $die ) {
			echo '</body>' . "\n";
			echo '</html>';
			exit();
		}
	}
}
/**
 * Method:printr2() 调试用的返回print_r代码格式
 * Parameter:item(array) -- 数组，对象等
 */
if ( !function_exists('printr2') ) {
	function printr2($arr = array(),$depth = 0) {
		$str = "Array\r\n";
		$str .= str_repeat('        ',$depth) . "(\r\n";
		foreach ($arr as $key => $item) $str .= '    ' . str_repeat('        ',$depth) . "[$key] => " . (is_array($item) ? printr2($item,$depth + 1) : $item) . "\r\n";
		$str .= str_repeat('        ',$depth) . ")";
		return $str;
	}
}
/**
 * Method:nl2br2() 换行格式化
 * Parameter:str(string) -- 字符串
 */
if ( !function_exists('nl2br2') ) {
	function nl2br2($str = '') {
		$str = preg_replace('/\r\n/','<br>',$str);
		$str = preg_replace('/\n/','<br>',$str);
		$str = preg_replace('/\r/','<br>',$str);
		$str = str_replace('	',' ',$str);
		return $str;
	}
}
/**
 * Method:api_success() 接口成功
 * Parameter:data(array) -- 返回数据
 */
if ( !function_exists('api_success') ) {
	function api_success($data = array()) {
		$json = array(
			'status' => 2000,
			'data' => $data
		);
		@ob_clean();
		echo json_encode($json);
		return $json;
	}
}
/**
 * Method:api_error() 接口报错
 * Parameter:msg(string) -- 错误提示语
 */
if ( !function_exists('api_error') ) {
	function api_error($code = 0,$msg = '') {
		$json = array(
			'status' => $code,
			'msg' => $msg
		);
		@ob_clean();
		echo json_encode($json);
		return $json;
	}
}
/**
 * Method:js_error() 脚本报错
 * Parameter:
 *   msg(string) -- 错误提示语
 *   op(string) -- 操作 close:关闭; refresh:刷新
 */
if ( !function_exists('js_error') ) {
	function js_error($msg = '',$op = '') {
		$js = '$( function() {';
		$js .= '	dialog("' . $msg . '", function() {';
		if ( $op == 'close' ) $js .= '		wx.closeWindow();';
		elseif ( $op == 'refresh' ) $js .= '		refresh();';
		elseif ( $op == 'error' ) $js .= '		error();';
		$js .= '	} );';
		$js .= '} );';
		echo $js;
		return $js;
	}
}
/**
 * Method:jsonp_success() jsonp接口成功
 * Parameter:data(array) -- 返回数据
 */
if ( !function_exists('jsonp_success') ) {
	function jsonp_success($data = array()) {
		$json = array(
			'status' => 1,
			'data' => $data
		);
		@ob_clean();
		echo get('callbackparam') . '(' . json_encode2($json) . ')';
		return $json;
	}
}
/**
 * Method:jsonp_error() jsonp接口报错
 * Parameter:msg(string) -- 错误提示语
 */
if ( !function_exists('jsonp_error') ) {
	function jsonp_error($msg = '') {
		$json = array(
			'status' => 0,
			'msg' => $msg
		);
		@ob_clean();
		echo get('callbackparam') . '(' . json_encode2($json) . ')';
		return $json;
	}
}
/**
 * Method:json_encode2() json编码（中文不转码）
 * Parameter:arr(array) -- 数组
 */
if ( !function_exists('json_encode2') ) {
	function json_encode2($arr = array()) {
		$json = '';
		$isAssoc = is_assoc($arr);
		$json .= $isAssoc ? '[' : '{';
		$jin = array();
		if ( $arr ) {
			foreach ($arr as $key => $item) {
				if ( is_array($item) ) $item = json_encode2($item);
				elseif ( is_string($item) ) $item = '"' . str_replace("\\","\\\\",str_replace('"','\"',$item)) . '"';
				elseif ( $item === '' ) $item = '""';
				if ( $isAssoc ) array_push($jin,$item);
				else array_push($jin,'"' . $key . '":' . $item);
			}
			$json .= implode(',',$jin);
		}
		$json .= $isAssoc ? ']' : '}';
		return $json;
	}
}
/**
 * Method:json_decode2() json解码（替换换行）
 * Parameter:json(string) -- 数组
 */
if ( !function_exists('json_decode2') ) {
	function json_decode2($json = '{}') {
		$json = preg_replace('/\r\n/','',$json);
		$json = preg_replace('/\n/','',$json);
		$json = preg_replace('/\r/','',$json);
		return json_decode($json,TRUE);
	}
}
/**
 * Method:is_assoc() 返回数组是否为索引数组
 * Parameter:arr(array) -- 数组
 */
if ( !function_exists('is_assoc') ) {
	function is_assoc($array) {
		if ( is_array($array) ) {
			$keys = array_keys($array);
			if ( !$keys || $keys[0] !== 0 ) return FALSE;
			return $keys == array_keys($keys);
		}
		return FALSE;
	}
}
/**
 * Method:get() GET方式参数
 * Parameter:
 *   key(string) -- 键
 *   fn(string) -- 处理函数
 */
if ( !function_exists('get') ) {
	function get($key = '',$fn = '') {
		$get = array();
		foreach ( explode('&',$_SERVER['QUERY_STRING']) as $item ) {
			$at = strpos($item,'=');
			if ( $at !== FALSE ) $get[substr($item,0,$at)] = substr($item,$at + 1);
		}
		$at = strpos($_SERVER['REQUEST_URI'],'?');
		if ( $at !== FALSE ) {
			$queryString = substr($_SERVER['REQUEST_URI'],$at + 1);
			foreach ( explode('&',$queryString) as $item ) {
				$at = strpos($item,'=');
				if ( $at !== FALSE ) $get[substr($item,0,$at)] = substr($item,$at + 1);
			}
		}
		if ( $key ) {
			$rtn = array_value($get,$key);
			$rtn = $fn ? $fn($rtn) : $rtn;
			return $rtn;
		}
		else return $get;
	}
}
/**
 * Method:post() POST方式参数
 * Parameter:
 *   key(string) -- 键
 *   fn(string) -- 处理函数
 */
if ( !function_exists('post') ) {
	function post($key = '',$fn = '') {
		$data = file_get_contents('php://input');
		if ( $data ) {
			$data = json_decode($data,TRUE);
			if ( !is_array($data) ) $data = '';
		}
		if ( $data ) {
			if ( $key ) {
				$rtn = array_value($data,$key);
				$rtn = $fn ? $fn($rtn) : $rtn;
				return $rtn;
			}
			else return $data;
		}
		else {
			if ( $key ) {
				$rtn = isset($_POST[$key]) ? $_POST[$key] : '';
				$rtn = $fn ? $fn($rtn) : $rtn;
				return $rtn;
			}
			else return $_POST;
		}
	}
}
/**
 * Method:upfile() 上传文件
 * Parameter:
 *   key(string) -- 键
 *   allow(array) -- 允许上传类型
 *   maxsize(int) -- 允许上传大小
 */
if ( !function_exists('upfile') ) {
	function upfile($key = '',$allow = array(),$maxsize = 0) {
		if ( array_value($_FILES,$key,'error') != 0 ) return '';
		$config = Load::config();
		$mimes = Load::config('mimes');
		$allow = array_merge($config['upload_allow'],$allow);
		$isAllow = FALSE;
		$ext = '';
		foreach ($allow as $item) {
			$one = $mimes[$item];
			if ( !is_array($one) ) $one = array($one);
			foreach ($one as $types) if ( $types == $_FILES[$key]['type'] ) {
				$isAllow = TRUE;
				$ext = $item;
				break;
			}
		}
		if ( !$isAllow ) show_admin_error('上传文件格式不合法！');
		$maxsize = $maxsize ? $maxsize * 1024 * 1024 : $config['upload_maxsize'];
		if ( $_FILES[$key]['size'] > $maxsize ) show_admin_error('上传大小超过最大限制！');
		$path = date('/Y/md/');
		$filename = date('YmdHis') . mt_rand(100,999) . '.' . $ext;
		$file_path = $path . $filename;
		@mkdir('./upload' . $path,0755,TRUE);
		move_uploaded_file($_FILES[$key]['tmp_name'],'./upload' . $file_path);
		if ( OSS && SYNC && ONLINE ) Load::oss()->upload('./upload' . $file_path,'upload' . $file_path);
		if ( !OSS && SYNC && ONLINE ) {
			require_once './k/sdk/ftp.php';
			foreach ($config['servers'] as $server) {
				$ftp = new Ftp();
				$ftp->connect(array(
					'hostname' => $server['hostname'],
					'username' => $server['username'],
					'password' => $server['password'],
					'port' => $server['port'],
					'debug' => FALSE
				));
				$ftp->mkdir('/upload',0777);
				$ftp->mkdir('/upload' . date('/Y'),0777);
				$ftp->mkdir('/upload' . date('/Y/md'),0777);
				$ftp->upload('./upload' . $file_path,'./upload' . $file_path);
				$ftp->close();
			}
		}
		return $file_path;
	}
}
/**
 * Method:form_time() 时间选择表单
 * Parameter:
 *   attr(string|array) -- 表单name|属性列表
 *   time(string|array) -- 时间选择插件mode|配置列表
 */
if ( !function_exists('form_time') ) {
	$timeLoaded = FALSE;
	function form_time($attr = array(),$time = array()) {
		global $timeLoaded;
		if ( is_string($attr) ) $attr = array(
			'name' => $attr
		);
		if ( $time && is_string($time) ) $time = array(
			'mode' => $time
		);
		$html = '<input type="text"';
		foreach ($attr as $key => $item) $html .= ' ' . $key . '="' . $item . '"';
		$html .= '>';
		$html .= $timeLoaded ? '' : '<script src="' . uv('/js/calenda.js') . '"></script>';
		$html .= '<script>$("input[name=' . $attr['name'] . ']").calenda(' . json_encode2($time) . ');</script>';
		$timeLoaded = TRUE;
		return $html;
	}
}
/**
 * Method:form_editor() 编辑器表单
 * Parameter:
 *   name(string) -- 表单name
 *   value(string) -- 表单值
 *   config(array) -- 编辑器配置
 */
if ( !function_exists('form_editor') ) {
	$editorLoaded = FALSE;
	function form_editor($name,$value = '',$config = array()) {
		global $editorLoaded;
		$html = $editorLoaded ? '' : '<script src="' . uv('/editor/kindeditor.js') . '"></script>';
		$html .= '<textarea name="' . $name . '">' . $value . '</textarea>';
		$html .= '<script>'
			  . 'KindEditor.ready(function(K){'
			  . 'K.create("textarea[name=' . $name . ']",{'
			  . 'themeType:"simple",'
			  . 'resizeType:1,'
			  . 'uploadJson:"' . u('/editor/php/upload_json.php') . '",'
			  . 'fileManagerJson:"' . u('/editor/php/file_manager_json.php') . '",'
			  . 'allowFileManager:true,'
			  . 'width:"' . (array_isset($config,'width') ? $config['width'] : '100%') . '",'
			  . 'height:"' . (array_isset($config,'height') ? $config['height'] : '270px') . '",'
			  . 'afterBlur:function(){this.sync();}'
			  . '});'
			  . '});'
			  . '</script>';
		$editorLoaded = TRUE;
		return $html;
	}
}
/**
 * Method:logger() 日志
 * Parameter:
 *   name(string) -- 文件名
 *   arr(array) -- 日志内容
 */
if ( !function_exists('logger') ) {
	function logger($name,$arr = array()) {
		@file_put_contents('./log/' . $name . '.log',implode('	',$arr) . "\r\n",FILE_APPEND);
	}
}
/**
 * Method:newOrderId() 生成订单ID
 * Parameter:
 *   name(string) -- 订单名
 *   len(string) -- 订单随机码长度
 */
if ( !function_exists('newOrderId') ) {
	function newOrderId($name = NAME,$len = 4) {
		$name = strtoupper($name);
		return $name . date('YmdHis') . mt_rand(pow(10,$len - 1),pow(10,$len) - 1);
	}
}
/**
 * Method:array_isset() 判断多维数组键是否存在
 */
if ( !function_exists('array_isset') ) {
	function array_isset() {
		$params = func_get_args();
		$arr = array_shift($params);
		if ( !$params ) return isset($arr);
		else foreach ($params as $key) {
			if ( !isset($arr[$key]) ) return FALSE;
			else $arr = $arr[$key];
		}
		return TRUE;
	}
}
/**
 * Method:array_value() 获取多维数组键的值
 */
if ( !function_exists('array_value') ) {
	function array_value() {
		$params = func_get_args();
		$arr = array_shift($params);
		if ( !$params ) return $arr;
		else foreach ($params as $key) {
			if ( !isset($arr[$key]) ) return FALSE;
			else $arr = $arr[$key];
		}
		return $arr;
	}
}
/**
 * Method:array_delete() 数组删除指定元素
 */
if ( !function_exists('array_delete') ) {
	function array_delete($item,$arr) {
		$i = array_search($item,$arr);
		if ( $i !== NULL ) array_splice($arr,$i,1);
		return $arr;
	}
}
/**
 * Method:ip_address() 获取ip地址
 */
if ( !function_exists('ip_address') ) {
	function ip_address() {
		$ip = '';
		if ( array_isset($_SERVER,'REMOTE_ADDR') && array_isset($_SERVER,'HTTP_CLIENT_IP') ) $ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif ( array_isset($_SERVER,'REMOTE_ADDR') ) $ip = $_SERVER['REMOTE_ADDR'];
		elseif ( array_isset($_SERVER,'HTTP_CLIENT_IP') ) $ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif ( array_isset($_SERVER,'HTTP_X_FORWARDED_FOR') ) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		if ( !$ip ) {
			$ip = '0.0.0.0';
			return $ip;
		}
		if ( !strpos($ip,',') ) {
			$x = explode(',',$ip);
			$ip = trim(end($x));
		}
		return $ip;
	}
}
/**
 * Method:encrypt() AES+Base64加密
 * Parameter:item(string) -- 明文
 */
if ( !function_exists('encrypt') ) {
	function encrypt($item) {
		if ( is_array($item) ) $item = json_encode2($item);
		return base64_encode(Load::library('encrypt')->encrypt($item));
	}
}
/**
 * Method:decrypt() AES+Base64解密
 * Parameter:item(string) -- 密文
 */
if ( !function_exists('decrypt') ) {
	function decrypt($item) {
		$item = Load::library('encrypt')->decrypt(base64_decode($item));
		if ( is_numeric($item) ) $item = floatval($item);
		$item = str_replace("\'","",$item);
		if ( is_array(json_decode($item,TRUE)) ) $item = json_decode($item,TRUE);
		return $item;
	}
}
/**
 * Method:day() 获取距1970-01-01的天数
 * Parameter:time(int) -- 时间戳
 */
if ( !function_exists('day') ) {
	function day($time = '') {
		if ( $time === '' ) $time = time();
		return intval(($time + 3600 * 8) / (3600 * 24)) + 1;
	}
}
/**
 * Method:daytotime() 将日时间戳转换为秒时间戳
 * Parameter:day(int) -- 日时间戳
 */
if ( !function_exists('daytotime') ) {
	function daytotime($day = '') {
		if ( $day === '' ) return time();
		return ($day - 1) * (3600 * 24) - (3600 * 8);
	}
}
/**
 * Method:format_data_size() 将日时间戳转换为秒时间戳
 * Parameter:
 *   size(int) -- 数据大小(b)
 *   round(int) -- 小数位数
 *   tag(boolean) -- 单位是否嵌套标签
 */
if ( !function_exists('format_data_size') ) {
	function format_data_size($size = 0,$round = 0,$tag = FALSE) {
		$units = array('Byte','KB','MB','GB','TB','PB','EB','ZB','YB','DB','NB');
		foreach ( $units as $i => $unit ) {
			if ( $size / 1024 < 1 ) break;
			if ( $i >= count($units) - 1 ) break;
			$size /= 1024;
		}
		if ( $round ) $size = round($size,$round);
		if ( $tag ) $unit = '<small>' . $unit . '</small>';
		return $size . $unit;
	}
}
/**
 * Method:duration() 秒数转换为00:00:00格式
 * Parameter:second(int) -- 秒数
 */
if ( !function_exists('duration') ) {
	function duration($second = 0) {
		$str = '';
		if ( $second >= 3600 ) $str .= intval($second / 3600) . ':';
		$str .= str_pad(intval($second % 3600 / 60),2,'0',STR_PAD_LEFT) . ':';
		$str .= str_pad($second % 60,2,'0',STR_PAD_LEFT);
		return $str;
	}
}
/**
 * Method:nick() 昵称格式化
 * Parameter:nick(char) -- 昵称
 */
if ( !function_exists('nick') ) {
	function nick($nick) {
		$emoji = Load::config('emoji');
		$char = str_replace(array_keys($emoji),$emoji,$nick);
		return $char;
	}
}
/**
 * Method:strlength() 计算字符串长度
 * Parameter:str(char) -- 字符串
 */
if ( !function_exists('strlength') ) {
	function strlength($str) {
		$mb = mb_strlen($str,"UTF-8");
		$len = strlen($str);
		$en = ($mb * 3 - $len) / 2;
		$cn = $mb - $en;
		$mix = ceil($en / 3 + $cn);
		return $mix;
	}
}
if ( !function_exists('rlog') ) {
	function rlog($type,$data) {
		Load::redis()->rpush('log',array(
			'type' => $type,
			'data' => $data,
			'ip' => ip_address(),
			'time' => time()
		));
	}
}
if ( !function_exists('delslashes') ) {
	function delslashes($str) {
		if ( !is_string($str) ) return $str;
		$str = str_replace("'",'',$str);
		$str = str_replace('"','',$str);
		$str = str_replace("\\",'',$str);
		$str = str_replace("\r\n",'',$str);
		$str = str_replace("\r",'',$str);
		$str = str_replace("\n",'',$str);
		return $str;
	}
}
if ( !function_exists('oa_search') ) {
	function oa_search($oa = array(),$sea = array(),$getOne = FALSE) {
		foreach ( $oa as $key => $item ) {
			if ( is_array($sea) ) {
				$bingo = TRUE;
				foreach ( $sea as $seaKey => $seaItem ) {
					if ( $item[$seaKey] != $seaItem ) {
						$bingo = FALSE;
						break;
					}
				}
				if ( $bingo ) {
					if ( $getOne ) return $item;
					else return $key;
				}
			}
			else {
				if ( $item == $sea ) {
					if ( $getOne ) return $item;
					else return $key;
				}
			}
		}
		return FALSE;
	}
}