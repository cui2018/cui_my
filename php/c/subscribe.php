<?php
class Subscribe extends Controller {
	public function __construct() {
		$this->redis = Load::redis();
	}
	//查看订阅列表(一期)(三期)
	public function index(){
		if ( !post('open_id') ) return api_error(5001);
		else $this->_getAllSubscribeProject(post());
	}
	//订阅(三期)
	public function subscribeProject() {
		if ( !post('open_id') ) return api_error(5001);
		else $this->_subscribeProject(post());
	}
	//订阅(二期)
	/*public function subscribeProject() {
		if ( !post('open_id') ) return api_error(5001);
		else $this->_subscribe(post());
	}*/
	//取消观看
	public function disconnect() {
		if ( !post('open_id') ) return api_error(5001);
		else return api_success('取消观看成功');
	}
	//订阅/取消订阅(二期)
	private function _subscribe($data = array()) {
		$resubscribe = $this->redis->hget('subscribe',$data['open_id']);
		if ( $data['state'] == 0 ) {
			if ( !$resubscribe ) {
				$this->redis->hset('subscribe',$data['open_id'],array(
					'state' => 1,
					'sub_time' => time()
				));
			}
			rlog('addUserSubscribe',array(
				'open_id' => $data['open_id'],
				'pro_id' => 0
			));
			return api_success(array(
				'state' => 1,
				'msg' => '订阅成功'
			));
		}
		elseif ( $data['state'] == 1 ) {
			$this->redis->hdel('subscribe',$data['open_id']);
			rlog('deleteUserSubscribe',array(
				'open_id' => $data['open_id'],
				'pro_id' => 0
			));
			return api_success(array(
				'state' => 0,
				'msg' => '取消订阅成功'
			));
		}
		else return api_error(5001);
	}
	//取消观看
	private function _disconnect($data = array()) {
		$resuser = $this->redis->hget('user',$data['open_id']);
		$resusergametime = $this->redis->hget('usergametime',$resuser['id']);
		$resusergametime = $resusergametime ? $resusergametime : array();
		if ( $resusergametime['end'] == 0 ) {
			$day1 = day();
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
			rlog('over_tg_gameontime',$userontime);
		}
		return api_success('取消观看成功');
	}
	//订阅/取消订阅(一期)(三期)
	private function _subscribeProject($data = array()){
		$resub_project = $this->redis->hget('subscribe_project',$data['open_id']);
		$resub_project = $resub_project ? $resub_project : array();
		$resproject = $this->redis->get('project');
		$i = oa_search($resproject, array(
			'id' => $data['pro_id']
		) );
		$data['pro_id'] = decrypt($data['pro_id']);
		if( $i !== FALSE ) {
			$j = oa_search($resub_project, array(
				'pro_id' => $data['pro_id']
			) );
			if( $data['state'] == 0 ) {
				//修改redis订阅人数
				$resproject[$i]['subuser'] = (int)$resproject[$i]['subuser'] + 1;
				$this->redis->set('project', $resproject);
				//修改数据库订阅人数
				rlog("updateProject", array(
					'id' => $data['pro_id'],
					'subuser' => $resproject[$i]['subuser']
				) );
				if( $j !== FALSE ) return api_success( array(
					'state' => 1,
					'subuser' => $resproject[$i]['subuser'],
					'msg' => '订阅成功'
				) );
				array_unshift($resub_project, array(
					'pro_id' => $data['pro_id']
				) );
				$this->redis->hset('subscribe_project',$data['open_id'],$resub_project);
				rlog('addUserSubscribe', array(
					'open_id' => $data['open_id'],
					'pro_id' => $data['pro_id']
				) );
				return api_success( array(
					'state' => 1,
					'subuser' => $resproject[$i]['subuser'],
					'msg' => '订阅成功'
				) );
			}
			else if( $data['state'] == 1 ){
				if( $j !== FALSE ){
					array_splice($resub_project,$j,1);
					if( count($resub_project) == 0 ) $this->redis->hdel('subscribe_project', $data['open_id']);
					else  $this->redis->hset('subscribe_project', $data['open_id'], $resub_project);
				}
				//修改redis订阅人数
				$resproject[$i]['subuser'] = (int)$resproject[$i]['subuser'] - 1;
				$this->redis->set('project', $resproject);
				//修改数据库订阅人数
				rlog("updateProject", array(
					'id' => $data['pro_id'],
					'subuser' => $resproject[$i]['subuser']
				) );
				rlog("deleteUserSubscribe", array(
					'open_id' => $data['open_id'],
					'pro_id' => $data['pro_id']
				) );
				return api_success( array(
					'state' => 0,
					'subuser' => $resproject[$i]['subuser'],
					'msg' => '取消订阅成功'
				) );
			}
			else return api_error(5001);
		}
		else return api_error(5006);
	}
	//查看订阅列表(三期)
	private function _getAllSubscribeProject($data = array()){
		$resub_project = $this->redis->hget('subscribe_project',$data['open_id']);
		$resub_project = $resub_project ? $resub_project : array();
		if( count($resub_project) < 1 ){
				return api_success($resub_project);
		}
		else {
			$res = array();
			$resproject = $this->redis->get("project");
			foreach($resub_project as $item){
				$n = oa_search($resproject,array(
					'id' => encrypt($item['pro_id'])
				));
				if( $n !== FALSE ){
					$sub_project = array(
						'pro_id' => $resproject[$n]['id'],
						'pic' => $resproject[$n]['pic'],
						'name' => $resproject[$n]['name'],
						'score' => $resproject[$n]['score'],
						'subuser' => $resproject[$n]['subuser']
					);
					array_push($res, $sub_project);
				}
			}
			return api_success($res);
			/*foreach($resproject as $item){
				$n = oa_search($resub_project,array(
					'pro_id' => decrypt($item['id'])
				));
				if ( $n !== FALSE ) {
					$sub_project = array(
						'pro_id' => $item['id'],
						'pic' => $item['pic'],
						'name' => $item['name'],
						'score' => $item['score'],
						'subuser' => $item['subuser']
					);
					array_push($res, $sub_project);
				}
			}*/
		}
	}
}