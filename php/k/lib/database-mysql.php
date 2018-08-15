<?php
/* 
 * 数据库类
 * @Package Name: Database
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */
class Database {
	//主键名
	public $primary = 'id';
	//构造创建连接
	public function __construct($config) {
		$this->config = $config;
		$this->conn = mysql_connect($config['hostname'],$config['username'],$config['password'],TRUE);
		mysql_query("SET NAMES 'UTF8'",$this->conn);
		mysql_select_db($config['database'],$this->conn);
	}
	//sql语句查询
	public function sql($sql = '') {
		$sql = preg_replace('/TABLE\((.+?)\)/','`' . $this->config['prefix'] . '$1`',$sql);
		$exec = strtoupper(substr($sql,0,strpos($sql,' ')));
		$query = mysql_query($sql,$this->conn);
		if ( $exec == 'SELECT' || $exec == 'SHOW' ) {
			$arr = array();
			while ( $row = mysql_fetch_assoc($query) ) $arr[] = $row;
			return $arr;
		}
		elseif ( $exec == 'INSERT' ) return $this->insert_id();
	}
	//多条查询
	public function get($table,$where = '',$order = '',$limit = '',$column = '*') {
		$where = $this->_where($where);
		$order = $this->_order($order);
		$sql = "SELECT $column FROM `{$this->config['prefix']}$table`";
		if ( $where ) $sql .= " WHERE $where";
		if ( $order ) $sql .= " ORDER BY $order";
		if ( $limit ) $sql .= " LIMIT $limit";
		$query = mysql_query($sql,$this->conn);
		$arr = array();
		while ( $row = mysql_fetch_assoc($query) ) $arr[] = $row;
		return $arr;
	}
	//分页查询
	public $page = array();
	public function page($table,$where = '',$order = '',$pagesize = 20,$column = '*',$pageName = 'curpage') {
		$curpage = get($pageName,'intval');
		$curpage = $curpage < 1 ? 1 : $curpage;
		$total = $this->rows($table,$where);
		$pages = Load::library('pages');
		$pages->setPage(array(
			'page_name' => $pageName,
			'cur_page' => $curpage,
			'page_size' => $pagesize,
			'total' => $total,
			'pages' => ceil($total / $pagesize)
		));
		$this->page = $pages->getPage();
		$where = $this->_where($where);
		$order = $this->_order($order);
		$sql = "SELECT $column FROM `{$this->config['prefix']}$table`";
		if ( $where ) $sql .= " WHERE $where";
		if ( $order ) $sql .= " ORDER BY $order";
		$sql .= " LIMIT " . (($curpage - 1) * $pagesize) . ",$pagesize";
		$query = mysql_query($sql,$this->conn);
		$arr = array();
		while ( $row = mysql_fetch_assoc($query) ) $arr[] = $row;
		return $arr;
	}
	//单条查询
	public function one($table,$where = '',$column = '*',$order = '') {
		$where = $this->_where($where);
		$order = $this->_order($order);
		$sql = "SELECT $column FROM `{$this->config['prefix']}$table`";
		if ( $where ) $sql .= " WHERE $where";
		else return array();
		if ( $order ) $sql .= " ORDER BY $order";
		$sql .= " LIMIT 1";
		$query = mysql_query($sql,$this->conn);
		$row = mysql_fetch_assoc($query);
		return $column != '*' && !strpos($column,',') ? $row[str_replace('`','',$column)] : $row;
	}
	//sum查询
	public function sum($table,$where = '',$column = '') {
		$where = $this->_where($where);
		$sql = "SELECT SUM($column) FROM `{$this->config['prefix']}$table`";
		if ( $where ) $sql .= " WHERE $where";
		else return array();
		$query = mysql_query($sql,$this->conn);
		$row = mysql_fetch_row($query);
		return $row ? ($row[0] ? $row[0] : 0) : 0;
	}
	//max查询
	public function max($table,$where = '',$column = '') {
		$where = $this->_where($where);
		$sql = "SELECT MAX($column) FROM `{$this->config['prefix']}$table`";
		if ( $where ) $sql .= " WHERE $where";
		$query = mysqli_query($this->conn,$sql);
		$row = mysqli_fetch_row($query);
		return $row ? ($row[0] ? $row[0] : 0) : 0;
	}
	//min查询
	public function min($table,$where = '',$column = '') {
		$where = $this->_where($where);
		$sql = "SELECT MIN($column) FROM `{$this->config['prefix']}$table`";
		if ( $where ) $sql .= " WHERE $where";
		$query = mysqli_query($this->conn,$sql);
		$row = mysqli_fetch_row($query);
		return $row ? ($row[0] ? $row[0] : 0) : 0;
	}
	//查询记录数
	public function rows($table,$where = '') {
		$where = $this->_where($where);
		$sql = "SELECT COUNT(1) FROM `{$this->config['prefix']}$table`";
		if ( $where ) $sql .= " WHERE $where";
		$sql .= " LIMIT 1";
		$query = mysql_query($sql,$this->conn);
		$row = mysql_fetch_row($query);
		return $row[0];
	}
	//插入记录
	public function insert($table,$records = array()) {
		$keys = '`' . implode('`,`',array_keys($records)) . '`';
		$values = "'" . implode("','",$records) . "'";
		$sql = "INSERT INTO `{$this->config['prefix']}$table` ($keys) VALUES ($values)";
		$this->sql($sql);
	}
	//更新记录
	public function update($table,$records = array(),$where = '') {
		$str = array();
		foreach ($records as $key => $item) {
			if ( preg_match('/^(.+) (\+|\-|\*|\/|\%)/i',$key) ) $str[] = preg_replace('/^(.+) (\+|\-|\*|\/|\%)/i','`$1`=`$1`$2' . "'$item'",$key);
			else $str[] = "`$key`='$item'";
		}
		$str = implode(',',$str);
		$where = $this->_where($where);
		$sql = "UPDATE `{$this->config['prefix']}$table` SET $str";
		if ( $where ) {
			$sql .= " WHERE $where";
			$this->sql($sql);
		}
	}
	//删除记录
	public function delete($table,$where = '') {
		$where = $this->_where($where);
		$sql = "DELETE FROM `{$this->config['prefix']}$table`";
		if ( $where ) {
			$sql .= " WHERE $where";
			$this->sql($sql);
		}
	}
	//获取新插入记录
	public function newOne($table) {
		return $this->one($table,$this->insert_id());
	}
	//新插入数据id
	public function insert_id() {
		return mysql_insert_id($this->conn);
	}
	//关闭连接
	public function close() {
		mysql_close($this->conn);
	}
	//设置查询条件
	private function _where($where) {
		if ( isset($where) && $where !== '' ) {
			if ( is_array($where) ) {
				$str = array();
				foreach ($where as $key => $item) {
					if ( preg_match('/^(.+) (>|>=|<|<=|!=|<>)/i',$key) ) $str[] = str_replace(' ','',preg_replace('/^(.+) (>|>=|<|<=|!=|<>)/i','`$1` $2',$key)) . "'$item'";
					elseif ( preg_match('/^(.+) IN/i',$key) ) $str[] = preg_replace('/^(.+) IN/i','`$1` IN',$key) . " (" . (is_array($item) ? implode(',',$item) : $item) . ")";
					elseif ( preg_match('/^(.+) LIKE/i',$key) ) $str[] = preg_replace('/^(.+) LIKE/i','`$1` LIKE',$key) . " '%$item%'";
					else $str[] = "`$key`='$item'";
				}
				$str = implode(' AND ',$str);
				$str = preg_replace('/TABLE\((.+?)\)/','`' . $this->config['prefix'] . '$1`',$str);
				return $str;
			}
			else {
				if (preg_match('/^\d+$/',$where)) return "`$this->primary`='" . intval($where) . "'";
				else return preg_replace('/TABLE\((.+?)\)/','`' . $this->config['prefix'] . '$1`',$where);
			}
		}
	}
	//设置排序
	private function _order($order) {
		if ( isset($order) && $order !== '' ) {
			$str = array();
			if ( is_array($order) ) {
				foreach ($order as $key => $item) $str[] = "`$key` " . ($item ? strtoupper($item) : 'ASC');
				return implode(',',$str);
			}
			else return $order;
		}
		else return '';
	}
	//析构自动关闭连接
	public function __destruct() {
		$this->close();
	}
}