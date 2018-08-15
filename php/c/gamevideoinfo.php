<?php
class Gamevideoinfo extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
	}
	//用户点击视频游戏(游戏详情)
	public function clickgamevideo() {
		if( !post('openid') || !post('game_id') ) return api_error(5001);
		else $this->_clickGameVideoTwo(post());
	}
	//用户点击视频游戏查看游戏结果
	public function gameresult() {
		if( !post('openid') || !post('game_id') ) return api_error(5001);
		else $this->_gameresultTwo(post());
	}
	//用户分享
	public function sharegamevideo() {
		if( !post('openid') ) return api_success();
		else $this->_shareGameVideoTwo(post());
	}
	//用户开玩游戏
	public function userplaygamevideo() {
		if( !post('openid') || !post('game_id') ) return api_error(5001);
		else $this->_userPlayGameTwo(post());
	}
	//用户完成新手引导
	public function newhandending() {
		if( !post('openid') ) return api_success();
		else $this->_newhandEnding(post());
	}
	//用户完成结果新手引导
	public function newhandresultending() {
		if( !post('openid') ) return api_success();
		else $this->_newhandResultEnding(post());
	}
	//用户点击视频游戏(游戏详情)
	private function _clickGameVideoTwo($data = array()) {
		$gamecomment = $this->redis->hget('gamecomment',decrypt($data['game_id']));
		$rescomment = array();
		$gamecomment = $gamecomment ? $gamecomment : array();
		foreach ( $gamecomment as $item ) {
			$user = $this->redis->hget('user', $item['openid']);
			unset($item['openid']);
			unset($item['game_id']);
			unset($item['update_time']);
			$item['com_time'] = date('Y-m-d',$item['com_time']);
			if ( !isset($item['state']) || $item['state'] == 1 ) {
				if ( $item['state'] ) unset($item['state']);
				$item['nick'] = $user['nick'] ? $user['nick'] : '用户';
				$item['headimg'] = $user['headimg'] ? $user['headimg'] : 'https://vgame-cdn.edisonluorui.com/upload/2018/0802/20180802093552794.jpg';
				array_push($rescomment,$item);
				if ( count($rescomment) >= 3 ) {
					$this->_resgameinfo($data,$rescomment);
					return;
				}
			}
		}
		$this->_resgameinfo($data,$rescomment);
	}
	private function _resgameinfo($data,$rescomment) {
		//游戏详情
		$game_id = decrypt($data['game_id']);
		$resgame = $this->redis->hget('game',$game_id);
		if ( !$resgame ) return api_error(4501);
		else {
			$res = array(
				'comment' => $rescomment ? $rescomment : array(),
				'score' => round(floatval($resgame['score']),1),
				'game_id' => $data['game_id'],
				'times' => $resgame['times'],
				'desc' => $resgame['desc'],
				'name' => $resgame['name'],
				'pic' => $resgame['pic'],
				'gameresult_state' => 0
			);
			$resuserplaygameresult = $this->redis->hget('userplaygameresult',$data['openid']);
			$resuserplaygameresult = $resuserplaygameresult ? $resuserplaygameresult : array();
			foreach ( $resuserplaygameresult as $item ) {
				if ( $item['game_id'] == $game_id ) {
					$res['gameresult_state'] = 1;
					break;
				}
			}
			$resharegamelist = $this->redis->hget('sharegamelist',$data['openid']);
			$resharegamelist = $resharegamelist ? $resharegamelist : array();
			$res['clearsharelock'] = 1;
			/*foreach ( $resharegamelist as $i => $item ) {
				if ( $item['game_id'] == $game_id ) {
					if ( $resharegamelist[$i]['sharestate'] == 1 ) $res['clearsharelock'] = 1;
					else $res['clearsharelock'] = 0;
					break;
				}
			}*/
			return api_success($res);
		}
	}
	//用户点击视频游戏查看游戏结果
	private function _gameresultTwo($data = array()) {
		$resuserplaygameresult = $this->redis->hget("userplaygameresult",$data['openid']);
		$resuserplaygameresult = $resuserplaygameresult ? $resuserplaygameresult : array();
		$game_id = decrypt($data['game_id']);
		$i = oa_search($resuserplaygameresult,array(
			'game_id' => $game_id
		));
		if ( $i !== FALSE ) {
			$resgame = $this->redis->hget('game',$game_id);
			if ( !$resgame ) return api_error(4501);
			else {
				foreach ( $resgame['result']['setting'][$resuserplaygameresult[$i]['grade']][2] as $item ) {
					unset($resuserplaygameresult[$i]['game_id']);
					unset($resuserplaygameresult[$i]['time']);
					if ( $resuserplaygameresult[$i]['result_score'] > $item['max'] ) continue;
					else {
						unset($resuserplaygameresult[$i]['grade']);
						$resuserplaygameresult[$i]['newstate'] = 0;
						$resnewhandresult = $this->redis->hget('newhandresult',$data['openid']);
						$resuserplaygameresult[$i]['result'] = array(
							'content' => $item['content'],
							'title' => $item['title']
						);
						if ( !$resnewhandresult ) $resuserplaygameresult[$i]['newstate'] = 1;
						return api_success($resuserplaygameresult[$i]);
					}
				}
				$gamecontent = $resgame['result']['setting'][$resuserplaygameresult[$i]['grade']][2][count($resgame['result']['setting'][$resuserplaygameresult[$i]['grade']][2]) - 1];
				unset($resuserplaygameresult[$i]['grade']);
				$resuserplaygameresult[$i]['newstate'] = 0;
				$resnewhandresult = $this->redis->hget('newhandresult',$data['openid']);
				$resuserplaygameresult[$i]['result'] = array(
					'content' => $gamecontent['content'],
					'title' => $gamecontent['title']
				);
				if ( !$resnewhandresult ) $resuserplaygameresult[$i]['newstate'] = 1;
				return api_success($resuserplaygameresult[$i]);
			}
		}
		else return api_error(4401);
	}
	//用户分享
	private function _shareGameVideoTwo($data = array()) {
		$game_id = $data['game_id'] ? decrypt($data['game_id']) : 0;
		$user = $this->redis->hget('user',$data['openid']);
		$usershare = array(
			'user_id' => $user['id'],
			'share_day' => day(),
			'game_id' => $game_id
		);
		rlog("addUserShare", $usershare);
		if( $game_id == 0 ) return api_success();
		$resharegamelist = $this->redis->hget('sharegamelist',$data['openid']);
		$resharegamelist = $resharegamelist ? $resharegamelist : array();
		$i = oa_search($resharegamelist,array(
			'game_id' => $game_id
		));
		if ( $i !== FALSE ) {
			$resharegamelist[$i]['sharestate'] = 1;
			$this->redis->hset('sharegamelist',$data['openid'],$resharegamelist);
		}
		else {
			$sharegame = array(
				'game_id' => $game_id,
				'sharestate' => 1
			);
			array_unshift($resharegamelist,$sharegame);
			$this->redis->hset('sharegamelist',$data['openid'],$resharegamelist);
		}
		return api_success();
	}
	//用户开玩游戏
	private function _userPlayGameTwo($data = array()) {
		$game_id = decrypt($data['game_id']);
		$resgame = $this->redis->hget('game',$game_id);
		if( !$resgame ) return api_error(4501);
		else {
			//用户开始玩游戏（计时开始）
			$this->_userrecord($data,$resgame);
			//存储用户玩过的游戏
			$this->_userplaygameredis($data);
			$restartgame = $this->redis->hget('startgame',$data['openid']);
			$restartgame = $restartgame ? $restartgame : array();
			$i = oa_search($restartgame,array(
				'game_id' => $game_id
			));
			$startgame = 1;
			if( $i !== FALSE ) {
				if ( $restartgame[$i]['state'] == 0 ) $startgame = 2;
				$restartgame[$i]['state'] = 0;
				$this->redis->hset('startgame',$data['openid'],$restartgame);
			}
			else {
				array_unshift($restartgame,array(
					'game_id' => $game_id,
					'state' => 0
				));
				$this->redis->hset('startgame',$data['openid'],$restartgame);
			}
			return api_success( array(
				'newhand' => encrypt('0'),
				'orientation' => $resgame['orientation'],
				'startgame' => $startgame,
				'setting' => $resgame['setting'],
				'result_guide' => $resgame['result_guide'],
				'version' => $resgame['version']
			));
		}
	}
	private function _userplaygameredis($data) {
		$game_id = decrypt($data['game_id']);
		$resuserplaygame = $this->redis->hget('userplaygame',$data['openid']);
		$resuserplaygame = $resuserplaygame ? $resuserplaygame : array();
		$i = oa_search($resuserplaygame,$game_id);
		if ( $i === FALSE ) {
			array_unshift($resuserplaygame,$game_id);
			$this->redis->hset('userplaygame',$data['openid'],$resuserplaygame);
		}
		$resuser = $this->redis->hget('user',$data['openid']);
		if ( !$resuser || $resuser['id'] == 0 ) return;
		else {
			$userplaygamedata = array(
				'user_id' => $resuser['id'],
				'game_id' => $game_id
			);
			rlog('addAndUpUserPlayGame',$userplaygamedata);
			return;
		}
	}
	private function _userrecord($data,$resgame) {
		$resuser2 = $this->redis->hget('user',$data['openid']);
		if ( !$resuser2 || $resuser2['id'] == 0 ) return;
		else {
			$time1 = time();
			$userontime = array(
				'user_id' => $resuser2['id'],
				'pro_id' => $resgame['project_id'],
				'gameclass_id' => $resgame['gc_id'],
				'game_id' => decrypt($data['game_id']),
				'starttime' => $time1
			);
			$this->redis->hset('usergametime',$resuser2['id'],array(
				'starttime' => $time1,
				'end' => 0
			));
			rlog('add_tg_gameontime',$userontime);
			return;
		}
	}
	//用户完成新手引导(一期)(二期)
	private function _newhandEnding($data = array()) {
		$this->redis->hset('newhand',$data['openid'],array(
			'state' => 1,
			'newhand_time' => time()
		));
		$resuser = $this->redis->hget('user',$data['openid']);
		if ( !$resuser ) return api_success();
		$newhand = array(
			'user_id' => $resuser['id'],
			'newhandtime' => time()
		);
		rlog('addNewHand',$newhand);
		return api_success();
	}
	//用户完成结果新手引导(二期)
	private function _newhandResultEnding($data = array()) {
		$this->redis->hset('newhandresult',$data['openid'],array(
			'state' => 1,
			'result_time' => time()
		));
		return api_success();
	}
}