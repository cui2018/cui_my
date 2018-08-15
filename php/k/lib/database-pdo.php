<?php
/* 
 * 数据库类
 * @Package Name: Database
 * @Author: Keboy xolox@163.com
 * @Modifications:No20171024
 *
 */
class Database {
	//主键名
	public $primary = 'id';
	//构造创建连接
	public function __construct($config) {
		$this->config = $config;
		$host = explode(':',$config['hostname']);
		if ( count($host) == 1 ) $host[1] = 3306;
		$dsn = "mysql:host={$host[0]};port={$host[1]};dbname={$config['database']}";
		$this->conn = new PDO($dsn,$config['username'],$config['password'],array(
			//PDO::ATTR_PERSISTENT => true,
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
		));
	}
	private $_sql = array();
	public function sql($sql = '') {
		$sql = preg_replace('/TABLE\((.+?)\)/','`' . $this->config['prefix'] . '$1`',$sql);
		$exec = strtoupper(substr($sql,0,strpos($sql,' ')));
		$query = $this->conn->query($sql);
		if ( $exec == 'SELECT' || $exec == 'SHOW' ) return $query->fetchAll(PDO::FETCH_ASSOC);
		elseif ( $exec == 'INSERT' ) return $this->insert_id();
	}
	public function select($fields = '*') {
		if ( is_array($fields) ) $fields = implode(',',$fields);
		$this->_sql['select'] = $fields;
		return $this;
	}
	public function from($table) {
		$table = '`' . $this->config['prefix'] . $table . '`';
		$this->_sql['from'] = $table;
		return $this;
	}
	public function where($where = '1=1') {
		$where = $this->_where($where);
		$this->_sql['where'] = $where;
		return $this;
	}
	public function groupby($fields) {
		if ( is_array($fields) ) $fields = implode(',',$fields);
		$this->_sql['groupby'] = $fields;
		return $this;
	}
	public function having($having = '1=1') {
		$having = $this->_where($having);
		$this->_sql['having'] = $having;
		return $this;
	}
	public function orderby($orderby = '') {
		$orderby = $this->_order($orderby);
		$this->_sql['orderby'] = $orderby;
		return $this;
	}
	public function limit($limit = '') {
		if ( $limit ) $this->_sql['limit'] = $limit;
		return $this;
	}
	public function dump() {
		$sql = '';
		if ( array_isset($this->_sql,'insert') ) {
			$sql = "INSERT INTO {$this->_sql['insert']}";
			if ( array_isset($this->_sql,'set') ) {
				$keys = '`' . implode('`,`',array_keys($this->_sql['set'])) . '`';
				$values = "'" . implode("','",$this->_sql['set']) . "'";
				$sql .= " ($keys) VALUES ($values)";
			}
		}
		elseif ( array_isset($this->_sql,'update') ) {
			$sql = "UPDATE {$this->_sql['update']}";
			if ( array_isset($this->_sql,'set') ) {
				$str = array();
				foreach ( $this->_sql['set'] as $key => $item ) {
					if ( preg_match('/^(.+) (\+|\-|\*|\/|\%)/i',$key) ) $str[] = preg_replace('/^(.+) (\+|\-|\*|\/|\%)/i','`$1`=`$1`$2' . "'$item'",$key);
					else $str[] = "`$key`='$item'";
				}
				$str = implode(',',$str);
				$sql .= " SET $str";
			}
			if ( array_isset($this->_sql,'where') ) $sql .= " WHERE {$this->_sql['where']}";
			else return '';
		}
		elseif ( array_isset($this->_sql,'delete') ) {
			$sql = "DELETE FROM {$this->_sql['delete']}";
			if ( array_isset($this->_sql,'where') ) $sql .= " WHERE {$this->_sql['where']}";
			else return '';
		}
		elseif ( array_isset($this->_sql,'select') ) {
			if ( !array_value($this->_sql,'from') ) return '';
			$sql = "SELECT {$this->_sql['select']} FROM {$this->_sql['from']}";
			if ( array_isset($this->_sql,'where') ) $sql .= " WHERE {$this->_sql['where']}";
			if ( array_isset($this->_sql,'groupby') ) $sql .= " GROUP BY {$this->_sql['groupby']}";
			if ( array_isset($this->_sql,'having') ) $sql .= " HAVING {$this->_sql['having']}";
			if ( array_isset($this->_sql,'orderby') ) $sql .= " ORDER BY {$this->_sql['orderby']}";
			if ( array_isset($this->_sql,'limit') ) $sql .= " LIMIT {$this->_sql['limit']}";
		}
		return $sql;
	}
	public function one($column = '') {
		$sql = $this->dump();
		$res = $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC);
		if ( $column && array_isset($res,$column) ) $res = $res[$column];
		$this->_sql = array();
		return $res;
	}
	public function get() {
		$sql = $this->dump();
		$res = $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$this->_sql = array();
		return $res;
	}
	public function sum($column) {
		$this->_sql['select'] = "SUM(`$column`)";
		$sql = $this->dump();
		$res = $this->conn->query($sql)->fetch(PDO::FETCH_NUM);
		$this->_sql = array();
		return $res[0];
	}
	public function max($column) {
		$this->_sql['select'] = "MAX(`$column`)";
		$sql = $this->dump();
		$res = $this->conn->query($sql)->fetch(PDO::FETCH_NUM);
		$this->_sql = array();
		return $res[0];
	}
	public function min($column) {
		$this->_sql['select'] = "MIN(`$column`)";
		$sql = $this->dump();
		$res = $this->conn->query($sql)->fetch(PDO::FETCH_NUM);
		$this->_sql = array();
		return $res[0];
	}
	public function rows() {
		$this->_sql['select'] = "COUNT(1)";
		$sql = $this->dump();
		$res = $this->conn->query($sql)->fetch(PDO::FETCH_NUM);
		$this->_sql = array();
		return $res[0];
	}
	public $page = array();
	public function page($pagesize = 20,$pageName = 'curpage') {
		$curpage = get($pageName,'intval');
		$curpage = $curpage < 1 ? 1 : $curpage;
		$sql = "SELECT COUNT(1) FROM `{$this->_sql['from']}`";
		if ( array_isset($this->_sql,'where') ) $sql .= " WHERE {$this->_sql['where']}";
		$res = $this->conn->query($sql)->fetch(PDO::FETCH_NUM);
		$total = $res[0];
		$pages = Load::library('pages');
		$pages->setPage(array(
			'page_name' => $pageName,
			'cur_page' => $curpage,
			'page_size' => $pagesize,
			'total' => $total,
			'pages' => ceil($total / $pagesize)
		));
		$this->page = $pages->getPage();
		$sql = $this->dump();
		$sql .= " LIMIT " . (($curpage - 1) * $pagesize) . ",$pagesize";
		$res = $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$this->_sql = array();
		return $res;
	}
	public function insert($table) {
		$table = '`' . $this->config['prefix'] . $table . '`';
		$this->_sql['insert'] = $table;
		return $this;
	}
	public function set($records = array()) {
		$this->_sql['set'] = $records;
		return $this;
	}
	public function insert_id() {
		return $this->conn->lastInsertId();
	}
	public function update($table) {
		$table = '`' . $this->config['prefix'] . $table . '`';
		$this->_sql['update'] = $table;
		return $this;
	}
	public function delete($table) {
		$table = '`' . $this->config['prefix'] . $table . '`';
		$this->_sql['delete'] = $table;
		return $this;
	}
	public function exec() {
		$sql = $this->dump();
		if ( $sql ) $this->conn->exec($sql);
		$this->_sql = array();
		return $this;
	}
	public function close() {
		$this->conn = NULL;
	}
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
				return implode(' AND ',$str);
			}
			else {
				if (preg_match('/^\d+$/',$where)) return "`$this->primary`='" . intval($where) . "'";
				else return $where;
			}
		}
	}
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
	public function __destruct() {
		$this->close();
	}
}