<?php
class Pages {
	private $_ico = '&';
	private $_pageArr = array();
	private $_pageName, $_showPageNum = 5;
	/**
	 * Method:setPage() 设置分页参数
	 * Parameter:arr(array) -- 分页参数
	 */
	public function setPage($arr = array()) {
		$this->_pageArr[count($this->_pageArr)] = $arr;
	}
	/**
	 * Method:getPage() 获取分页代码
	 * Parameter:
	 *   mode(int) -- 分页模式
	 *   get(int) -- 获取第get个分页信息
	 * Return:string -- 分页代码
	 */
	public function getPage($get = 0) {
		$uri = get('uri');
		$get = $get ? intval($get) : count($this->_pageArr) - 1;
		if ( $get < 0 ) return '';
		$arr = $this->_pageArr[$get];
		if ( !is_array($arr) ) return '';
		$this->_pageName = $arr['page_name'];
		$option = array();
		for ($i = 1; $i <= $arr['pages']; $i++) $option[$this->_ico . $this->_getQueryString($i)] = $i;
		$option1 = array();
		for ($i = 1; $i <= $arr['pages']; $i++) $option1[$i] = $i;
		$prev = ($arr['cur_page'] - 1 > 0) ? ($arr['cur_page'] - 1) : 1;
		$next = ($arr['cur_page'] + 1 <= $arr['pages']) ? ($arr['cur_page'] + 1) : $arr['pages'];
		$droplist = '<select onChange="window.location.href=this.value;">';
		foreach ($option as $key => $item) $droplist .= '<option value="' . $uri . $key . '"' . ($key == $this->_ico . $this->_getQueryString($arr['cur_page']) ? ' selected' : '') . '>' . $item . '</option>';
		$droplist .= '</select>';
		$html = array(
			'all' => ' 总计<span>' . $arr['total'] . '</span>条',
			'pages' => ' 记录为<span>' . $arr['pages'] . '</span>页',
			'perpage' => ' 每页<span>' . $arr['page_size'] . '</span>条',
			'curpage' => ' 当前<span>' . $arr['cur_page'] . '</span>/<span>' . $arr['pages'] . '</span>',
			'full' => ' ' . $this->_fullHtml($arr),
			'pn' => ' <a href="' . $uri . $this->_ico . $this->_getQueryString(1) . '">[首页]</a> <a href="' . $uri . $this->_ico . $this->_getQueryString($prev) . '">[上一页]</a> <a href="' . $uri . $this->_ico . $this->_getQueryString($next) . '">[下一页]</a> <a href="' . $uri . $this->_ico . $this->_getQueryString($arr['pages']) . '">[尾页]</a>',
			'jump' => ' 跳转至第 ' . $droplist . ' 页',
		);
		return array(
			0 => $html['full'],
			1 => $html['all'] . $html['perpage'] . $html['curpage'] . '　' . $html['pn'] . '　' . $html['jump'],
			2 => $html['all'] . $html['perpage'] . $html['curpage'] . '　' . $html['full'] . '　' . $html['jump']
		);
	}
	/**
	 * Method:_fullHtml() 获取full类型html
	 * Parameter:arr(array) -- 分页参数
	 * Return:string -- html代码
	 */
	private function _fullHtml($arr) {
		$uri = explode('&',$_SERVER['REQUEST_URI']);
		$uri = $uri[0];
		$result = '';
		$startPageNum = 1;
		while ( $startPageNum + $this->_showPageNum <= $arr['cur_page'] ) $startPageNum += $this->_showPageNum;
		$endPageNum = $startPageNum + $this->_showPageNum;
		$endPageNum = $endPageNum > $arr['pages'] ? $arr['pages'] : $endPageNum;
		if ( 1 < $arr['cur_page'] ) $result .= '<a href="' . $uri . $this->_ico . $this->_getQueryString(1) . '">&lt;&lt;</a>';
		else $result .= '<span class="disabled">&lt;&lt;</span>';
		if ( 1 < $startPageNum ) $result .= '<a href="' . $uri . $this->_ico . $this->_getQueryString($startPageNum - 1) . '">&lt;</a>';
		else $result .= '<span class="disabled">&lt;</span>';
		for ($i = $startPageNum; $i <= $endPageNum; $i++) {
			if ( $i != $arr['cur_page'] ) $result .= '<a href="' . $uri . $this->_ico . $this->_getQueryString($i) . '">' . $i . '</a>';
			else $result .= '<span class="current">' . $i . '</span>';
		}
		if ( $arr['pages'] > $endPageNum ) $result .= '<a href="' . $uri . $this->_ico . $this->_getQueryString($endPageNum) . '">&gt;</a>';
		else $result .= '<span class="disabled">&gt;</span>';
		if ( $arr['pages'] > $arr['cur_page'] ) $result .= '<a href="' . $uri . $this->_ico . $this->_getQueryString($arr['pages']) . '">&gt;&gt;</a>';
		else $result .= '<span class="disabled">&gt;&gt;</span>';
		return $result;
	}
	/**
	 * Method:_getQueryString() 获取页面参数
	 * Parameter:curPage(int) -- 指定页码
	 * Return:string -- 页面参数
	 */
	private function _getQueryString($curPage = 1) {
		$hasCurPage = FALSE;
		$queryArr = array();
		foreach (get() as $key => $value) {
			if ( $key == 'uri' ) continue;
			if ($key == $this->_pageName) {
				$hasCurPage = TRUE;
				array_push($queryArr,$this->_pageName . '=' . $curPage);
			}
			else array_push($queryArr,$key . '=' . urlencode($value));
		}
		if ( !$hasCurPage ) array_push($queryArr,$this->_pageName . '=' . $curPage);
		return implode('&',$queryArr);
	}
}