<?php
class Comment extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
	}
	public function index() {
		if ( !post('openid') || !post('game_id') ) return api_error(5001);
		else $this->_getUserGameScoreRedis(post(),TRUE);
	}
	//评论列表
	public function commentlist() {
		if ( !post('openid') || !post('game_id') ) return api_error(5001);
		else $this->_getUserGameScoreRedis(post(),FALSE);
	}
	//用户对游戏评论
	public function usergamescore() {
		if ( !post('openid') || !post('game_id') || !post('gamecomment') ) return api_success();
		else {
			$data = post();
			$data['gamescore'] = $data['gamescore'] ? $data['gamescore'] : 0;
			$this->_userGameScore($data);
		}
	}
	//用户修改游戏评论
	public function reusergamescore() {
		if ( !post('openid') || !post('game_id') || !post('gamecomment') || !post('gamescore') ) return api_success();
		else {
			$data = post();
			$data['gamescore'] = $data['gamescore'] ? $data['gamescore'] : 0;
			$this->_reUserGameScore($data);
		}
	}
	//用户查询游戏评论
	public function usergamescore_one() {
		if ( !post('openid') || !post('game_id') ) return api_error(5001);
		else $this->_userGameScoreone(post());
	}
	//评论列表(从redis中查询)
	private function _getUserGameScoreRedis($data = array(),$bool) {
		$resuser = $this->redis->hget('user',$data['openid']);
		if ( !$resuser ) return api_error(4102);
		else {
			if ( !$data['page'] ) $data['page'] = 1;
			$res = array(
				'nowpage' => $data['page']
			);
			$i = (intval($data['page']) - 1) * 10;
			$data['game_id'] = decrypt($data['game_id']);
			$resgamecomment = $this->redis->hget('gamecomment',$data['game_id']);
			$rescomment = array();
			$resgamecomment = $resgamecomment ? $resgamecomment : array();
			foreach ( $resgamecomment as $item ) {
				$user = $this->redis->hget('user', $item['openid']);
				unset($item['openid']);
				unset($item['game_id']);
				unset($item['update_time']);
				$item['com_time'] = date('Y-m-d',$item['com_time']);
				if ( !isset($item['state']) || $item['state'] == 1 ) {
					if ( isset($item['state']) ) unset($item['state']);
					$item['nick'] = $user['nick'] ? $user['nick'] : '用户';
					$item['headimg'] = $user['headimg'] ? $user['headimg'] : 'https://vgame-cdn.edisonluorui.com/upload/2018/0802/20180802093552794.jpg';
					array_push($rescomment,$item);
				}
			}
			$res['resgames_com'] = array_slice($rescomment,$i,10);
			$res['totalpage'] = intval((count($rescomment) + 9) / 10);
			$resgame = $this->redis->hget('game',$data['game_id']);
			if ( $bool ) {
				$res['times'] = $resgame['times'];
				$res['score'] = round(floatval($resgame['score']),1);
			}
			return api_success($res);
		}
	}
	//用户对游戏评论
	private function _userGameScore($data = array()) {
		$resuser = $this->redis->hget('user',$data['openid']);
		if ( !$resuser ) return api_error(4102);
		else {
			$gamecomment = delslashes($data['gamecomment']);
			$res = Load::library('baidu')->spam($gamecomment);
			if ( !$res['status'] ) return api_error(5010);
			else {
				$data['game_id'] = decrypt($data['game_id']);
				$resuserplaygameresult = $this->redis->hget('userplaygameresult',$data['openid']);
				$resuserplaygameresult = $resuserplaygameresult ? $resuserplaygameresult : array();
				if ( oa_search($resuserplaygameresult,array(
					'game_id' => $data['game_id']
				)) === FALSE ) return api_success();
				$resusergamecomment = $this->redis->hget('usergamecomment',$data['openid']);
				$resusergamecomment = $resusergamecomment ? $resusergamecomment : array();
				if ( oa_search($resusergamecomment,array(
					'game_id' => $data['game_id']
				)) !== FALSE ) return api_success();
				$resgame = $this->redis->hget('game',$data['game_id']);
				//游戏分数
				$resgame['score'] = round(floatval($resgame['score']),1);
				//用户评论后的游戏分数
				$resgame['score'] = (round(floatval($resgame['score'] * $resgame['times']),1) + intval($data['gamescore'])) / (intval($resgame['times']) + 1);
				//评论人数
				$resgame['times'] = intval($resgame['times']) + 1;
				//更改redis游戏信息
				$this->redis->hset('game',$data['game_id'],$resgame);
				//更改库里游戏信息
				$updategame = array(
					'score' => $resgame['score'],
					'times' => $resgame['times'],
					'id' => $data['game_id']
				);
				rlog('updateGame',$updategame);	
				//存储用户评论redis
				$usergamescore = array(
					'openid' => $data['openid'],
					'game_id' => encrypt($data['game_id']),
					'score' => $data['gamescore'],
					'game_com' => $gamecomment,
					'com_time' => time(),
					'state' => 1,
					'update_time' => ''
				);
				$resgamecomment = $this->redis->hget('gamecomment',$data['game_id']);
				$resgamecomment = $resgamecomment ? $resgamecomment : array();
				//if ( count($resgamecomment) >= 3 ) array_pop($resgamecomment);
				array_unshift($resgamecomment,$usergamescore);
				if ( !json_decode(json_encode2($resgamecomment),TRUE) ) return api_success();
				$this->redis->hset('gamecomment',$data['game_id'],$resgamecomment);
				//存储用户评论redis
				array_unshift($resusergamecomment,array(
					'game_id' => $data['game_id'],
					'score' => $data['gamescore'],
					'game_com' => $gamecomment
				));
				if ( !json_decode(json_encode2($resusergamecomment),TRUE) ) return api_success();
				$this->redis->hset('usergamecomment',$data['openid'],$resusergamecomment);
				//存储用户评论
				$usergamescore['game_id'] = $data['game_id'];
				$usergamescore['user_id'] = $resuser['id'];
				$usergamescore['headimg'] = $resuser['headimg'];
				$usergamescore['nick'] = $resuser['nick'];
				rlog('addUserGameScore',$usergamescore);
				return api_success('评论成功！');
			}
		}
	}
	//用户修改游戏评论
	private function _reUserGameScore($data = array()) {
		$resuser = $this->redis->hget('user',$data['openid']);
		if( !$resuser ) return api_error(4102);
		else {
			$gamecomment = delslashes($data['gamecomment']);
			$res = Load::library('baidu')->spam($gamecomment);
			if ( !$res['status'] ) return api_error(5010);
			else {
				$data['game_id'] = decrypt($data['game_id']);
				$resgamecomment = $this->redis->hget('gamecomment',$data['game_id']);
				$resgamecomment = $resgamecomment ? $resgamecomment : array();
				$i = oa_search($resgamecomment,array(
					'openid' => $data['openid']
				));
				if ( $i !== FALSE ) {
					//修改游戏信息分数
					$resgame = $this->redis->hget('game',$data['game_id']);
					if ( !$resgame ) return api_error(4501);
					else {
						$resgame['score'] = (round(floatval($resgame['score'] * $resgame['times']),1) - intval($resgamecomment[$i]['score']) + intval($data['gamescore'])) / $resgame['times'];
						//更改redis游戏分数
						$this->redis->hset('game',$data['game_id'],$resgame);
						//更改数据库游戏分数
						rlog('updateGameScore',array(
							'id' => $data['game_id'],
							'score' => $resgame['score']
						));
						$resgamecomment[$i]['score'] = $data['gamescore'];
						$resgamecomment[$i]['game_com'] = $gamecomment;
						$resgamecomment[$i]['update_time'] = time();
						if ( !json_decode(json_encode2($resgamecomment),TRUE) ) return api_success();
						$this->redis->hset('gamecomment',$data['game_id'],$resgamecomment);
					}
					//修改存储用户评论redis
					$resusergamecomment = $this->redis->hget("usergamecomment",$data['openid']);
					$resusergamecomment = $resusergamecomment ? $resusergamecomment : array();
					$i = oa_search($resusergamecomment,array(
						'game_id' => $data['game_id']
					));
					if ( $i !== FALSE ) {
						$resusergamecomment[$i]['score'] = $data['gamescore'];
						$resusergamecomment[$i]['game_com'] = $gamecomment;
						if ( !json_decode(json_encode2($resusergamecomment),TRUE) ) return api_success();
						$this->redis->hset('usergamecomment',$data['openid'],$resusergamecomment);
					}
					else {
						array_unshift($resusergamecomment,array(
							'game_id' => $data['game_id'],
							'score' => $data['gamescore'],
							'game_com' => $gamecomment
						));
						if ( !json_decode(json_encode2($resusergamecomment),TRUE) ) return api_success();
						$this->redis->hset('usergamecomment',$data['openid'],$resusergamecomment);
					}
					rlog('addUserGameScore',array(
						'game_id' => $data['game_id'],
						'user_id' => $resuser['id'],
						'nick' => $resuser['nick'],
						'headimg' => $resuser['headimg'],
						'score' => $data['gamescore'],
						'game_com' => $gamecomment,
						'com_time' => time()
					));
					return api_success('评论成功！');
				}
				else return api_error(4401);
			}
		}
	}
	//用户查询游戏评论
	private function _userGameScoreone($data = array()) {
		$resusergamecomment = $this->redis->hget('usergamecomment',$data['openid']);
		$resusergamecomment = $resusergamecomment ? $resusergamecomment : array();
		$game_id = decrypt($data['game_id']);
		$i = oa_search($resusergamecomment,array(
			'game_id' => $game_id
		));
		if ( $i !== FALSE ) {
			unset($resusergamecomment[$i]['game_id']);
			$resusergamecomment[$i]['com_state'] = 1;
			return api_success($resusergamecomment[$i]);
		}
		else return api_success(array(
			'com_state' => 0,
			'score' => 0,
			'game_com' => ''
		));
	}
}