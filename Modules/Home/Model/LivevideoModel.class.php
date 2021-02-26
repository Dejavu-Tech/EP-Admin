<?php
namespace Home\Model;
use Think\Model;
/**
 * 直播模型模型
 * @author Albert.Z
 *
 */
class LivevideoModel {

	function syncRoomList()
	{
		$accessToken = $this->getAccessToken();

		if (empty($accessToken)) {
			return array('errcode'=>40001, 'accessToken为空');
		}

		$page = 1;
		$start = 0;
		$pageSize = 30;
		$param = array(
			"start" => $start,
			"limit" => $pageSize
		);
		$roomIds = array();
		$url = 'https://api.weixin.qq.com/wxa/business/getliveinfo?access_token=' . $accessToken;

		$model = M('eaterplanet_ecommerce_wxlive');
		S('_inc_live_expirtime_', time());

		while (true) {
			$response = $this->_post($url, $param);
			$result = json_decode($response, true);

			$roomReqNum = S('_inc_live_roominfo_reqnum_');
			$num = intval($roomReqNum) + 1;
			S('_inc_live_roominfo_reqnum_', $num);

			if ($result['errcode'] != 0) {
				if ($result['errcode'] == 1) {
					return array('errcode'=>$result['errcode'], 'msg'=>'直播间列表为空');
				}

				if ($result['errcode'] == 48001) {
					return array('errcode'=>$result['errcode'], 'msg'=>'小程序没有直播权限');
				}

				return array('errcode'=>$result['errcode'], 'msg'=>$result['errmsg']);
			}

			foreach ($result['room_info'] as $room) {
				$roomId = (int) $room['roomid'];
				$roomIds[] = $roomId;

				$wxlive =  $model->where(array('roomid'=>$roomId))->find();
				$updateData = array('name' => (string) $room['name'], 'cover_img' => (string) $room['cover_img'], 'live_status' => (int) $room['live_status'], 'start_time' => (int) $room['start_time'], 'end_time' => (int) $room['end_time'], 'anchor_name' => (string) $room['anchor_name'], 'anchor_img' => (string) $room['anchor_img'], 'share_img' => (string) $room['share_img'], 'goods' => json_encode($room['goods']));

				if (empty($wxlive)) {
					$insertData = array_merge($updateData, array('roomid' => $roomId));
					$model->add($insertData);
					// if($room['live_status'] == '103') {
					// 	$this->syncLiveReplay($room['roomid']);
					// }
					// continue;
				}

				// $live_replay_lv = unserialize($wxlive['live_replay']);
				// if(!empty($wxlive) && empty($live_replay_lv) && $room['live_status']=='103') {
				// 	$this->syncLiveReplay($room['roomid']);
				// }
				$model->where( array('roomid' => $room['roomid'] ) )->save($updateData);
			}

			if ($result['total'] < $pageSize*$page) {
				break;
			}

			$page++;
			unset($room);
		}

		unset($result);

		$result = $model->where('roomid not in ( ' . implode(',', $roomIds) . ')' )->delete();
	}

	function syncLiveReplay($room_id)
	{
		$accessToken = $this->getAccessToken();

		if(!$accessToken) {
			return '';
			die();
		}

		$url = 'https://api.weixin.qq.com/wxa/business/getliveinfo?access_token='.$accessToken;
		$param = array(
			"action" => "get_replay",
			"room_id" => $room_id,
			"start" => 0,
			"limit" => 1
		);

		$res = $this->_post($url, $param);
		$res = json_decode($res);

		$replayReqNum = S('_inc_live_replay_reqnum_');
		$num = intval($replayReqNum) + 1;
		S('_inc_live_replay_reqnum_', $num);

		if($res->errcode == 0) {
			$live_replay = $res->live_replay;
			$updateData = array('live_replay'=>serialize($live_replay));
			M('eaterplanet_ecommerce_wxlive')->where( array('roomid' => $room_id ) )->save($updateData);
			return $live_replay;
		} else {
			// 代表未创建直播房间
			return '';
		}
	}

	function getRoomInfo($roomid)
	{
		$model = M('eaterplanet_ecommerce_wxlive');
		$res = $model->where( array('roomid' => $roomid ) )->find();
		return $res;
	}

	/**
	 * 新版同步回放1.0.5
	 * @param  [type] $room_id [description]
	 * @return [type]          [description]
	 */
	function syncLiveReplayNew($room_id, $begin = 0, $end = 1)
	{
		$accessToken = $this->getAccessToken();

		if(!$accessToken) {
			return '';
			die();
		}

		$url = 'https://api.weixin.qq.com/wxa/business/getliveinfo?access_token='.$accessToken;
		$param = array(
			"action" => "get_replay",
			"room_id" => $room_id,
			"start" => $begin,
			"limit" => $end
		);

		$res = $this->_post($url, $param);
		return json_decode($res, true);
	}

	/**
	 * 获取accessToken
	 * @return [String] [accessToken]
	 */
	private function getAccessToken()
	{
		$weixin_config = array();
		$weixin_config['appid'] = D('Home/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Home/Front')->get_config_by_name('wepro_appsecret');
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);
		return $jssdk->getweAccessToken();
	}

	private function _post($url, $data=array()) {
	   //初使化init方法
	   $ch = curl_init();

	   //指定URL
	   curl_setopt($ch, CURLOPT_URL, $url);

	   //设定请求后返回结果
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	   //声明使用POST方式来进行发送
	   curl_setopt($ch, CURLOPT_POST, 1);

	   //发送什么数据呢
	   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

	   //忽略证书
	   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	   //忽略header头信息
	   curl_setopt($ch, CURLOPT_HEADER, 0);

	   //设置超时时间
	   curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	   //发送请求
	   $output = curl_exec($ch);

	   //关闭curl
	   curl_close($ch);

	   //返回数据
	   return $output;
	}

}
