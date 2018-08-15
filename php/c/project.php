<?php
class Project extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
	}
	//类目查询（首页）
	public function index() {
		if ( !post('openid') ) return api_error(5001);
		else $this->_getProject(post());
	}
	//查询一级类目所有游戏(一期)（三期）
	public function projectgame() {
		if ( !post('openid') || !post('pro_id') ) return api_error(5001);
		else $this->_projectGame(post());
	}
	//类目查询（首页）(二期)
	private function _getAllGame($data = array()) {
		$res = array(
			'subscribe' => 1
		);
		$games = array();
		$resgames = $this->redis->hgetallvalue('games');
		$resgames = $resgames ? $resgames : array();
		$ressubscribe = $this->redis->hget('subscribe',$data['openid']);
		if ( !$ressubscribe ) $res['subscribe'] = 0;
		foreach ( $resgames as $item ) {
			foreach ( $item as $item1 ) {
				$item1['subscribe_style'] = array(
					'sub' => array(
						'bgcolor' => '#FFF'
					)
				);
				$item1['gvideo_new'] = (int)$item1['gvideo_new'];
				array_push($games,$item1);
			}
		}
		$res['games'] = $games;
		$resuserresult = $this->redis->hget('userplaygameresult',$data['openid']);
		$resuserresult = $resuserresult ? $resuserresult : array();
		foreach ( $res['games'] as $item ) {
			$realid = decrypt($item['id']);
			$item['gameresult_state'] = 0;
			foreach ( $resuserresult as $one ) {
				if ( $one['game_id'] == $realid ) {
					$item['gameresult_state'] = 1;
					break;
				}
			}
		}
		$resuserrecordstate = $this->redis->hget('userrecordstate',$data['openid']);
		if ( !$resuserrecordstate || $resuserrecordstate['record_state'] == 0 ) {
			$res['record_state'] = 0;
			return api_success($res);
		}
		else {
			foreach ( $resuserresult as $item ) {
				$resgame = $this->redis->hget('game',$item['game_id']);
				if ( !$resgame ) continue;
				else {
					$resrecordgamestate = $this->redis->hget('userrecordgamestate',$data['openid'] . $item['game_id']);
					if ( !$resrecordgamestate || $resrecordgamestate['gamerecord_state'] == 0 ) continue;
					else {
						$res['record_state'] = 1;
						return api_success($res);
					}
				}
			}
			$res['record_state'] = 0;
			return api_success($res);
		}
	}
	//类目查询(首页)(一期)(三期)
	private function _getProject($data = array()){
		$res = array();
		$resproject = $this->redis->get('project');
		$ressub_project = $this->redis->hget('subscribe_project', $data['openid']);
		$ressub_project = $ressub_project ? $ressub_project : array();
		foreach( $resproject as &$item ){
			$project_id = decrypt($item['id']);
			$resallgame = $this->redis->hget('gamesByMain', $project_id);
			$item['project_new'] = 0;
			$n = oa_search($resallgame, array(
				'gvideo_new' => 1
			) );
			if( $n !== FALSE ) $item['project_new'] = 1;
			$item['subscribe_state'] = 0;
			if( count($ressub_project) == 0 ) {
				continue;
			}
			$i = oa_search($ressub_project, array(
				'pro_id' => $project_id
			) );
			if( $i !== FALSE ) $item['subscribe_state'] = 1;
		}
		$resuserrecordstate = $this->redis->hget('userrecordstate', $data['openid']);
		$res['record_state'] = 0;
		if( $resuserrecordstate && $resuserrecordstate['record_state'] == 1 ){
			$resuserresult = $this->redis->hget('userplaygameresult', $data['openid']);
			$resuserresult = $resuserresult ? $resuserresult : array();
			foreach( $resuserresult as $item2) {
				$resgame = $this->redis->hget('game', $item2['game_id']);
				if( !$resgame ) continue;
				else {
					$resrecordgamestate = $this->redis->hget('userrecordgamestate', $data['openid'] + $item2['game_id']);
					if( !$resrecordgamestate || $resrecordgamestate['gamerecord_state'] == 0 ) continue;
					else $res['record_state'] = 1;
				}
			}
		}
		$res['resproject'] = $resproject;
		return api_success($res);
	}
	//查询一级类目所有游戏(一期)（三期）
	private function _projectGame($data = array()){
		$resallgame = $this->redis->hget('gamesByMain', decrypt($data['pro_id']));
		$resallgame = $resallgame ? $resallgame : array();
		$resuserplaygameresult = $this->redis->hget('userplaygameresult', $data['openid']);
		$resuserplaygameresult = $resuserplaygameresult ? $resuserplaygameresult : array();
		/*$ressub_project = $this->redis->hget('subscribe_project', $data['openid']);
		$ressub_project = $ressub_project ? $ressub_project : array();
		$i = oa_search($ressub_project, array(
			'pro_id' => decrypt($data['pro_id'])
		) );
		$subscribe_state = 0;
		if( $i !== FALSE ) $subscribe_state = 1;*/
		foreach( $resallgame as &$item ){
			$j = oa_search($resuserplaygameresult,array(
				'game_id' => decrypt($item['id'])
			));
			if( $j !== FALSE ) $item['gameresult_state'] = 1;
			else $item['gameresult_state'] = 0;
			unset($item['gc_id']);
			unset($item['setting']);
			unset($item['result']);
			unset($item['sort']);
			unset($item['sex']);
			unset($item['locktype']);
			unset($item['lockcon']);
			$item['gvideo_new'] = (int)$item['gvideo_new']; 
			$item['score'] = round(floatval($item['score']),1);
		}
		//if( $data['openid'] == 'ofxlG44acv6uDyfL9jgHomNznKmQ' ) printr($resallgame);
		return api_success( array(
			'games' => $resallgame,
			'subscribe_state' => 1
		) );
	}
}