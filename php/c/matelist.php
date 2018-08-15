<?php
class Matelist extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
	}
	//可匹配游戏列表
	public function index() {
		if ( !post('openid') ) return api_error(5001);
		else $this->_getAllMateGame(post());
	}
	//用户查询好友匹配
	public function friendmate() {
		if ( !post('openid') || !post('game_id') ) return api_error(5001);
		else $this->_friendMate(post());
	}
	//用户点击生成图片
	public function friendmatepic() {
		if ( !post('openid') || !post('game_id') || !post('friend_openid') ) return api_error(5001);
		else $this->_friendMatePic(post());
	}
	//好友分享
	public function friendship() {
		if ( !post('openid') ) return api_error(5001);
		else $this->_getFriendShip(post());
	}
	//好友分享下标
	public function friendpic() {
		$this->_friendPic();
	}
	//好友删除
	public function frienddel() {
		if ( !post('openid') || !post('friend_openid') ) return api_error(5001);
		else $this->_friendDel(post());
	}
	//可匹配游戏列表(一期)(二期)
	private function _getAllMateGame($data = array()) {
		$res = array();
		$resuserplaygame = $this->redis->hget('userplaygame',$data['openid']);
		$resuserplaygame = $resuserplaygame ? $resuserplaygame : array();
		$resuserplaygameresult = $this->redis->hget('userplaygameresult',$data['openid']);
		$resuserplaygameresult = $resuserplaygameresult ? $resuserplaygameresult : array();
		foreach ( $resuserplaygame as $item ) {
			$game = array();
			$resgame = $this->redis->hget('game',$item);
			if ( !$resgame ) continue;
			else {
				$game['game_id'] = encrypt($item);
				$game['name'] = $resgame['name'];
				$i = oa_search($resuserplaygameresult,array(
					'game_id' => $item
				));
				if ( $i === FALSE ) {
					$game['gamerecord_red_state'] = 0;
					array_push($res,$game);
				}
				else {
					//游戏圆点
					$resuserrecordgamestate = $this->redis->hget('userrecordgamestate',$data['openid'] . $item);
					if ( !$resuserrecordgamestate || $resuserrecordgamestate['gamerecord_state'] == 0 ) $game['gamerecord_red_state'] = 0;
					else $game['gamerecord_red_state'] = 1;
					array_push($res,$game);
				}
			}
		}
		return api_success($res);
	}
	//用户查询好友匹配(一期)(二期)
	private function _friendMate($data = array()) {
		$res = array();
		$data['game_id'] = decrypt($data['game_id']);
		$resuserplaygameresult = $this->redis->hget('userplaygameresult',$data['openid']);
		$resuserplaygameresult = $resuserplaygameresult ? $resuserplaygameresult : array();
		$n = oa_search($resuserplaygameresult,array(
			'game_id' => $data['game_id']
		));
		if ( $n !== FALSE ) {
			$resfriendship = $this->redis->hget('friendship',$data['openid']);
			$resfriendship = $resfriendship ? $resfriendship : array();
			foreach ( $resfriendship as $i => $item ) {
				$resfriendplaygameresult = $this->redis->hget('userplaygameresult',$item);
				$resfriendplaygameresult = $resfriendplaygameresult ? $resfriendplaygameresult : array();
				$j = oa_search($resfriendplaygameresult,array(
					'game_id' => $data['game_id']
				));
				if ( $j !== FALSE ) {
					$red = array();
					$resgame = $this->redis->hget('game',$data['game_id']);
					if ( !$resgame ) continue;
					else {
						$resuser = $this->redis->hget('user',$item);
						if ( !$resuser ) continue;
						else {
							$red['friend'] = array(
								'friend_openid' => encrypt($item),
								'headimg' => $resuser['headimg'],
								'nick' => $resuser['nick']
							);
							$resrecordgamestate = $this->redis->hget('friendrecordgamestate',$data['openid'] . $item . $data['game_id']);
							if ( !$resrecordgamestate || $resrecordgamestate['friendrecord_state'] == 0 ) $red['friendrecord_red_state'] = 0;
							else $red['friendrecord_red_state'] = 1;
							$ab = $resuserplaygameresult[$n]['grade'] . $resfriendplaygameresult[$j]['grade'];
							$ba = $resfriendplaygameresult[$j]['grade'] . $resuserplaygameresult[$n]['grade'];
							$red['friend_score'] = array_isset($resgame,'result','relation',$ab) ? array_value($resgame,'result','relation',$ab) : array_value($resgame,'result','relation',$ba);
							unset($red['friend_score']['title']);
							unset($red['friend_score']['content']);
							$red['game_id'] = encrypt((string)$data['game_id']);
							array_push($res, $red);
						}
					}
				}
			}
			$arr = array();
			$num = count($res);
			for ( $i = 0; $i < $num; $i++ ) {
				if ( isset($res[$i]) && $res[$i] && $res[$i] != NULL ) {
					array_push($arr,$res[$i]);
				}
			}
			return api_success($arr);
		}
		else return api_success($res);
	}
	//用户点击生成图片(同时清除红点)
	private function _friendMatePic($data = array()) {
		$data['game_id'] = decrypt($data['game_id']);
		$data['friend_openid'] = decrypt($data['friend_openid']);
		$resgame = $this->redis->hget('game',$data['game_id']);
		$resrecordgamestate = $this->redis->hget('friendrecordgamestate',$data['openid'] . $data['friend_openid'] . $data['game_id']);
		$resrecordgamestate = $resrecordgamestate ? $resrecordgamestate : array();
		$resrecordgamestate['friendrecord_state'] = 0;
		$this->redis->hset('friendrecordgamestate',$data['openid'] . $data['friend_openid'] . $data['game_id'],$resrecordgamestate);
		$resfriendship = $this->redis->hget('friendship',$data['openid']);
		$resfriendship = $resfriendship ? $resfriendship : array();
		foreach ( $resfriendship as $item ) {
			$resotherfriendplaygameresult = $this->redis->hget('friendrecordgamestate',$data['openid'] . $item . $data['game_id']);
			if ( !$resotherfriendplaygameresult || $resotherfriendplaygameresult['friendrecord_state'] == 0 ) continue;
			else {
				$resuserrecordgamestate = $this->redis->hget('userrecordgamestate',$data['openid'] . $data['game_id']);
				$resuserrecordgamestate = $resuserrecordgamestate ? $resuserrecordgamestate : array();
				$resuserrecordgamestate['gamerecord_state'] = 1;
				$this->redis->hset('userrecordgamestate',$data['openid'] . $data['game_id'],$resuserrecordgamestate);
				$this->_friendresultpic($data,$resgame);
			}
		}
		$resuserrecordgamestate = $this->redis->hget('userrecordgamestate',$data['openid'] . $data['game_id']);
		$resuserrecordgamestate = $resuserrecordgamestate ? $resuserrecordgamestate : array();
		$resuserrecordgamestate['gamerecord_state'] = 0;
		$this->redis->hset('userrecordgamestate',$data['openid'] . $data['game_id'],$resuserrecordgamestate);
		$resuserrecordstate = $this->redis->hget('userrecordstate',$data['openid']);
		$resuserrecordstate = $resuserrecordstate ? $resuserrecordstate : array();
		$resuserrecordstate['record_state'] = 0;
		$this->redis->hset('userrecordstate',$data['openid'],$resuserrecordstate);
		$this->_friendresultpic($data,$resgame);
	}
	private function _friendresultpic($data,$game) {
		$resuserplaygameresult = $this->redis->hget('userplaygameresult',$data['openid']);
		$resuserplaygameresult = $resuserplaygameresult ? $resuserplaygameresult : array();
		$resfriendplaygameresult = $this->redis->hget('userplaygameresult',$data['friend_openid']);
		$resfriendplaygameresult = $resfriendplaygameresult ? $resfriendplaygameresult : array();
		$n = oa_search($resuserplaygameresult,array(
			'game_id' => $data['game_id']
		));
		$m = oa_search($resfriendplaygameresult,array(
			'game_id' => $data['game_id']
		));
		$resfriend = $this->redis->hget('user',$data['friend_openid']);
		$ab = $resuserplaygameresult[$n]['grade'] . $resfriendplaygameresult[$m]['grade'];
		$ba = $resfriendplaygameresult[$m]['grade'] . $resuserplaygameresult[$n]['grade'];
		$obj = array_isset($game,'result','relation',$ab) ? array_value($game,'result','relation',$ab) : array_value($game,'result','relation',$ba);
		return api_success(array(
			'friend_id' => $resfriend['openid'],
			'friend_hendimg' => $resfriend['headimg'],
			'friend_nick' => $resfriend['nick'],
			'pic_content' => $obj
		));
	}
	//好友分享(一期)(二期)
	private function _getFriendShip($data = array()) {
		if ( !$data['sender'] && !$data['sendeid'] ) return api_success();
		else {
			if ( !$data['sendeid'] ) {
				if ( $data['sender'] == $data['openid'] ) return api_success();
				else {
					rlog('friendship',array(
						'openid' => $data['openid'],
						'sender' => $data['sender']
					));
					return api_success();
				}
			}
			else {
				$resuser = Load::database()->one('user',$data['sendeid']);
				if ( !$resuser ) return api_success();
				else {
					if ( $resuser['openid'] == $data['openid'] ) return api_success();
					else {
						rlog('friendship',array(
							'openid' => $data['openid'],
							'sender' => $resuser['openid']
						));
						return api_success();
					}
				}
			}
		}
	}
	//好友分享下标
	private function _friendPic() {
		$res = $this->redis->get('sharepic');
		return api_success($res);
	}
	//好友删除(二期)
	private function _friendDel($data = array()) {
		$resfriendship = $this->redis->hget('friendship',$data['openid']);
		$resfriendship = $resfriendship ? $resfriendship : array();
		$i = oa_search($resfriendship,$data['friend_openid']);
		if ( $i !== FALSE ) {
			array_splice($resfriendship,$i,1);
			if( sizeof($resfriendship) == 0 ) $this->redis->hdel('friendship',$data['openid']);
			else $this->redis->hset('friendship',$data['openid'],$resfriendship);
			$resopenid = $this->redis->hget('user',$data['openid']);
			$resfriend_openid = $this->redis->hget('user',$data['friend_openid']);
			//删除数据库里好友
			if ( $resopenid && $resfriend_openid ) rlog('delfriendship',array(
				'userone_id' => $resopenid['id'],
				'usertwo_id' => $resfriend_openid['id']
			));
		}
		return api_success('删除成功');
	}
}