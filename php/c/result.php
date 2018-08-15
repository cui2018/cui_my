<?php
class Result extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
	}
	//用户游戏生成结果
	public function usergameresult() {
		$data = post();
		if ( !post('openid') || !post('game_id') || !post('newhand') || !array_isset($data,'end') ) return api_error(5001);
		else $this->_userGameresultTwo($data);
	}
	//用户游戏生成结果(二期)
	private function _userGameresultTwo($data = array()) {
		$res = array();
		$data['game_id'] = decrypt($data['game_id']);
		$resgame = $this->redis->hget('game',$data['game_id']);
		if ( !$resgame ) return api_error(4901);
		if ( $resgame['result_guide'] == 1 ) {
			if ( $data['end'] != 'a' && $data['end'] != 'b' && $data['end'] != 'c' && $data['end'] != 'd' ) $data['end'] = 'c';
			$min = intval($resgame['result']['setting'][strtolower($data['end'])][0]);
			$max = intval($resgame['result']['setting'][strtolower($data['end'])][1]);
			$res['result_score'] = mt_rand($min,$max);
			$data['res'] = $res;
			$this->_saveresult($data,$resgame);
		}
		else if ( $resgame['result_guide'] == 2 ) {
			//后台设置最小值
			$resultmin = $resgame['result']['setting']['d'][0];
			$res['result_score'] = intval($resultmin) + (is_numeric($data['end']) ? intval($data['end']) : 10);
			$data['res'] = $res;
			if ( $res['result_score'] > $resgame['result']['setting']['a'][2][0]['max'] ) {
				$data['end'] = 'a';
				$this->_saveresult($data,$resgame);
			}
			else if ( $res['result_score'] > $resgame['result']['setting']['b'][2][0]['max'] ) {
				$data['end'] = 'b';
				$this->_saveresult($data,$resgame);
			}
			else if ( $res['result_score'] > $resgame['result']['setting']['c'][2][0]['max'] ) {
				$data['end'] = 'c';
				$this->_saveresult($data,$resgame);
			}
			else {
				$data['end'] = 'd';
				$this->_saveresult($data,$resgame);
			}
		}
		else return api_error(4901);
	}
	private function _saveresult($data,$gameinfo) {
		//用于用户重新开始还是继续游戏
		$restartgame = $this->redis->hget('startgame',$data['openid']);
		$restartgame = $restartgame ? $restartgame : array();
		$i = oa_search($restartgame,array(
			'game_id' => $data['game_id']
		));
		if ( $i !== FALSE ) {
			$restartgame[$i]['state'] = 1;
			$this->redis->hset('startgame',$data['openid'],$restartgame);
		}
		else {
			array_unshift($restartgame,array(
				'game_id' => $data['game_id'],
				'state' => 1
			));
			$this->redis->hset('startgame',$data['openid'],$restartgame);
		}
		//往数据库中插入用户结果
		$resuser = $this->redis->hget('user',$data['openid']);
		$usergameresult = array(
			'gr_grade' => $data['end'],
			'user_id' => $resuser['id'],
			'nick' => $resuser['nick'],
			'game_id' => $data['game_id'],
			'gr_score' => $data['res']['result_score'],
			'gr_time' => time()
		);
		rlog('userGameResult',$usergameresult);
		//用户结果存储redis
		$resuserplaygameresult = $this->redis->hget('userplaygameresult',$data['openid']);
		$resuserplaygameresult = $resuserplaygameresult ? $resuserplaygameresult : array();
		$i = oa_search($resuserplaygameresult,array(
			'game_id' => $data['game_id']
		));
		if ( $i !== FALSE ) array_splice($resuserplaygameresult,$i,1);
		$gameresult = array(
			'grade' => $data['end'],
			'result_score' => $data['res']['result_score'],
			'game_id' => $data['game_id'],
			'time' => date('Y-m-d',$usergameresult['gr_time'])
		);
		array_unshift($resuserplaygameresult,$gameresult);
		$this->redis->hset('userplaygameresult',$data['openid'],$resuserplaygameresult);
		//用户历史结果记录redis(按游戏区分)
		$resusergameresulthistory = $this->redis->hget('usergameresulthistory',$data['openid'] . $data['game_id']);
		$resusergameresulthistory = $resusergameresulthistory ? $resusergameresulthistory : array();
		array_unshift($resusergameresulthistory,$gameresult);
		$this->redis->hset('usergameresulthistory',$data['openid'] . $data['game_id'],$resusergameresulthistory);
		//用户历史结果记录redis(不按游戏区分)
		$resusergameresulthistory = $this->redis->hget('usergameresulthistory',$data['openid']);
		$resusergameresulthistory = $resusergameresulthistory ? $resusergameresulthistory : array();
		array_unshift($resusergameresulthistory,$gameresult);
		$this->redis->hset('usergameresulthistory',$data['openid'] . $data['game_id'],$resusergameresulthistory);
		//结束游戏计算此次游戏时间
		$resusergametime = $this->redis->hget('usergametime',$resuser['id']);
		$resusergametime = $resusergametime ? $resusergametime : array();
		$time1 = time();
		$userontime = array(
			'user_id' => $resuser['id'],
			'starttime' => $resusergametime['starttime'],
			'endtime' => $time1,
			'ontime' => $time1 - $resusergametime['starttime']
		);
		$this->redis->hset('usergametime',$resuser['id'],array(
			'starttime' => $time1,
			'end' => 1
		));
		//rlog('over_tg_gameontime',$userontime);
		$resfriendship = $this->redis->hget('friendship',$data['openid']);
		$resfriendship = $resfriendship ? $resfriendship : array();
		foreach ( $resfriendship as $item ) {
			$resgameresult = $this->redis->hget('userplaygameresult',$item);
			$resgameresult = $resgameresult ? $resgameresult : array();
			$i = oa_search($resgameresult,array(
				'game_id' => $data['game_id']
			));
			if ( $i === FALSE ) continue;
			else {
				$friendship = $this->redis->hget('friendship',$item);
				$friendship = $friendship ? $friendship : array();
				if ( oa_search($friendship,$data['openid']) !== FALSE ) {
					//最外层圆点
					$this->redis->hset('userrecordstate',$item,array(
						'record_state' => 1
					));
					//游戏列表圆点
					$this->redis->hset('userrecordgamestate',$item . $data['game_id'],array(
						'gamerecord_state' => 1
					));
					//好友列表圆点
					$this->redis->hset('friendrecordgamestate',$item . $data['openid'] . $data['game_id'],array(
						'friendrecord_state' => 1
					));
				}
			}
		}
		return api_success();
	}
}