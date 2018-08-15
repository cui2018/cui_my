<?php
/* 
 * 阿里云oss对象存储
 * @Package Name: Osslib
 * @Author: Keboy xolox@163.com
 * @Modifications:No20180705
 *
 */
require_once './k/sdk/oss/autoload.php';
use OSS\OssClient;
use OSS\Core\OssException;
class Osslib {
	public $oss;
	public function __construct($config) {
		$this->config = $config;
		$this->oss = new OssClient($config['keyId'],$config['keySecret'],$config['endpoint'],FALSE);
	}
	//获取指定目录下的目录及文件
	public function dir($folder = '') {
		$info = $this->oss->listObjects($this->config['bucket'],array(
			'delimiter' => '/',
			'prefix' => $folder,
			'max-keys' => 1000,
			'marker' => ''
		));
		$res = array(
			'dir' => array(),
			'file' => array()
		);
		foreach ( $info->getPrefixList() as $item ) {
			array_push($res['dir'],$item->getPrefix());
		}
		foreach ( $info->getObjectList() as $item ) {
			$file = array(
				'name' => $item->getKey(),
				'size' => $item->getSize(),
				'time' => $item->getLastModified()
			);
			if ( $file['name'] == $folder ) continue;
			array_push($res['file'],$file);
		}
		return $res;
	}
	//获取所有文件
	public function files() {
		$info = $this->oss->listObjects($this->config['bucket'],array(
			'delimiter' => '',
			'prefix' => '',
			'max-keys' => 1000,
			'marker' => ''
		));
		$res = array();
		foreach ( $info->getObjectList() as $item ) {
			$file = array(
				'name' => $item->getKey(),
				'size' => $item->getSize(),
				'time' => $item->getLastModified()
			);
			if ( $file['size'] == 0 ) continue;
			array_push($res,$file);
		}
		return $res;
	}
	//获取所有目录
	public function dirs() {
		$info = $this->oss->listObjects($this->config['bucket'],array(
			'delimiter' => '',
			'prefix' => '',
			'max-keys' => 1000,
			'marker' => ''
		));
		$res = array();
		foreach ( $info->getObjectList() as $item ) {
			$file = array(
				'name' => $item->getKey(),
				'size' => $item->getSize(),
				'time' => $item->getLastModified()
			);
			if ( $file['size'] > 0 ) continue;
			array_push($res,$file);
		}
		return $res;
	}
	//判断文件或目录是否存在
	public function exists($file) {
		$exist = $this->oss->doesObjectExist($this->config['bucket'],$file);
		return $exist ? 1 : 0;
	}
	//创建目录
	public function mkdir($folder) {
		$this->oss->createObjectDir($this->config['bucket'],$folder);
	}
	//删除文件
	public function delete($file) {
		$deleteObject = is_array($file) ? 'deleteObjects' : 'deleteObject';
		$this->oss->$deleteObject($this->config['bucket'],$file);
	}
	//复制文件
	public function copy($from,$to) {
		$this->oss->copyObject($this->config['bucket'],$from,$this->config['bucket'],$to);
	}
	//写文件内容
	public function write($cloud,$content) {
		$this->oss->putObject($this->config['bucket'],$cloud,$content);
	}
	//文件上传
	public function upload($local,$cloud) {
		$this->oss->uploadFile($this->config['bucket'],$cloud,$local);
	}
	//文件下载
	public function get($cloud,$local = '') {
		if ( $local ) $this->oss->getObject($this->config['bucket'],$cloud,array(
			OssClient::OSS_FILE_DOWNLOAD => $local
		));
		else return $this->oss->getObject($this->config['bucket'],$cloud);
	}
}