<?php
namespace Home\Model;
use Think\Model;
/**
 * 拼团模型
 * @author Albert.Z
 *
 */
class PinModel extends Model{

    /**
     * 检测拼团状态并返回可使用的拼团id
     * @param unknown $pin_id
     * @return unknown|number
     */
	function checkPinState($pin_id){
	   $pin_info = M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id) )->find();

	   if($pin_info['state'] == 0 && $pin_info['end_time'] > time()) {
	       return $pin_id;
	   } else {
	       return 0;
	   }
	}

	/***
		虚拟客户成团
	**/
	public function jia_over_order( $pin_id )
	{

		$pin_info = M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id ) )->find();

		$buy_count =  $this->get_tuan_buy_count($pin_id);

		$del_count = $pin_info['need_count'] - $buy_count;


		if($del_count > 0)
		{

			$jia_list = M('eaterplanet_ecommerce_jiauser')->order('rand()')->limit($del_count)->select();

			foreach($jia_list as $jia_member)
			{
				$tmp_arr = array();
				//jiapinorder
				$tmp_arr['uniacid'] = 0;
				$tmp_arr['pin_id'] = $pin_id;
				$tmp_arr['uname'] = $jia_member['username'];
				$tmp_arr['avatar'] = $jia_member['avatar'];
				$tmp_arr['order_sn'] = build_order_no($pin_id);;
				$tmp_arr['mobile'] = $jia_member['mobile'];
				$tmp_arr['addtime'] = time() + mt_rand(60,120);


				M('eaterplanet_ecommerce_jiapinorder')->add( $tmp_arr );
			}

			M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id ) )->save( array('is_jiqi' => 1)  );
			$this->updatePintuanSuccess($pin_id);

			return true;
		}else{

			return false;
		}

	}


	/**
	 * 检测拼团当前真实状态
	 * 因为拼团时间是可变，过期拼团的订单状态可能是拼团中
	 */
	public function getNowPinState($pin_id)
	{
	    $pin_info = M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id) )->find();

	    if($pin_info['state'] == 0 && $pin_info['end_time'] <= time()) {
	        return 2;
	    } else {
	        return $pin_info['state'];
	    }

	}

	/**
	 * 获取拼团已成功购买价数量
	 */
	public function get_tuan_buy_count($pin_id=0 , $where =' and o.order_status_id = 2 ')
	{
	    $buy_count_sql =  "select count(o.order_id) as count  from ".C('DB_PREFIX'). "eaterplanet_ecommerce_pin as p,".C('DB_PREFIX'). "eaterplanet_ecommerce_pin_order as po,"
		.C('DB_PREFIX'). "eaterplanet_ecommerce_order_goods as og,
		   ".C('DB_PREFIX'). "eaterplanet_ecommerce_order as o
		       where p.pin_id = po.pin_id  and po.order_id=o.order_id and og.order_id = o.order_id {$where}  and p.pin_id={$pin_id}  ";

	    $count_tuan =M()->query($buy_count_sql);
	    return $count_tuan[0]['count'];
	}




	/**
	 * 获取商品正在进行中的团
	 */
	public function get_goods_pintuan($goods_id,$limit =8)
	{
	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

	    $fujin_sql = "select distinct(p.pin_id) as pin_id,p.need_count,o.order_id,o.shipping_city_id,p.end_time,m.name,m.avatar  from ".C('DB_PREFIX'). "eaterplanet_ecommerce_pin as p,".C('DB_PREFIX'). "eaterplanet_ecommerce_order_goods as og,".C('DB_PREFIX'). "eaterplanet_ecommerce_pin_order as po, 
		   ".C('DB_PREFIX'). "eaterplanet_ecommerce_order as o,".C('DB_PREFIX'). "eaterplanet_ecommerce_member as m  
		       where p.pin_id = po.pin_id and po.order_id = o.order_id  and og.order_id=o.order_id and o.member_id = m.member_id and o.order_status_id =2 and og.goods_id={$goods_id} and p.end_time>".time()." group by po.pin_id order by p.end_time asc   limit {$limit}";

		$fujin_countsql = "select distinct(p.pin_id) as pin_id,p.need_count,o.order_id,o.shipping_city_id,p.end_time,m.name,m.avatar  from ".C('DB_PREFIX'). "eaterplanet_ecommerce_pin as p,".C('DB_PREFIX'). "eaterplanet_ecommerce_order_goods as og,".C('DB_PREFIX'). "eaterplanet_ecommerce_pin_order as po, 
		   ".C('DB_PREFIX'). "eaterplanet_ecommerce_order as o,".C('DB_PREFIX'). "eaterplanet_ecommerce_member as m  
		       where p.pin_id = po.pin_id and po.order_id=o.order_id  and og.order_id=o.order_id  and o.member_id = m.member_id and o.order_status_id =2 and og.goods_id={$goods_id} and p.end_time>".time()." group by po.pin_id  order by p.end_time asc   ";
	    $fujin_tuan_arr_count =M()->query($fujin_countsql);

		$fujin_tuan_count = count($fujin_tuan_arr_count);


		// 		/and o.order_status_id =2
	    $fujin_tuan =M()->query($fujin_sql);

		//var_dump($fujin_tuan);die();
	    $result = array();

	   $weixin_notify_model = D('Home/Weixinnotify');
	    if(!empty($fujin_tuan))
	    {

	        foreach($fujin_tuan as $pintuan)
	        {

	           $buy_count = $this->get_tuan_buy_count($pintuan['pin_id']);
	           $pintuan['buy_count'] =$buy_count;
	           $pintuan['re_need_count'] = $pintuan['need_count'] - $buy_count;
	           //shipping_city_id
			   $area_info = M('area')->where( array('area_id' => $pintuan['shipping_city_id']) )->find();

			   $pintuan['area_name'] = $area_info['area_name'];
	           $order_id = $hashids->encode($pintuan['order_id']);
	           $url = $weixin_notify_model->getSiteUrl()."/index.php?s=/Group/info/group_order_id/{$order_id}";

	           $pintuan['url'] = $url;
	           if($buy_count > 0)
	           {
	               //存在进行中的
	               $result[] = $pintuan;
	           }
	        }

	    }
		//fujin_tuan_count
	    return  array('list' => $result, 'count' => $fujin_tuan_count);
	}

	/**
		检测订单是否团长订单
	**/
	public function checkOrderIsTuanzhang($order_id)
	{
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();
		$pin_order = M('eaterplanet_ecommerce_pin_order')->where( array('order_id' => $order_id) )->find();
		$pin_id = $pin_order['pin_id'];

		if($order_info['is_pin'] == 0)
		{
			return false;
		}


		$pin_info = M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id ) )->find();


		$is_tuan  = false;
		if( $pin_info['order_id'] ==  $order_id)
		{
			$is_tuan = true;
		}

		return $is_tuan;
	}

	/**
	 * 检测拼团是否成功
	 */
	public function checkPinSuccess($pin_id)
	{
	    $pin_info = M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id) )->find();
	    if(empty($pin_info)) {
	        return false;
	    }


		$pin_order_sql = "select count(po.id) as count from ".C('DB_PREFIX'). "eaterplanet_ecommerce_pin_order as po,".C('DB_PREFIX'). "eaterplanet_ecommerce_order as o, 
	                      ".C('DB_PREFIX'). "eaterplanet_ecommerce_order_goods as og 
	                          where po.pin_id = ".$pin_id." and o.order_status_id in(1,2,4,6)
	                          and og.order_id = po.order_id and o.order_id = po.order_id  order by po.add_time asc ";

		$pin_order_arr_count = M()->query($pin_order_sql);
	    $pin_order_count = $pin_order_arr_count[0]['count'];


	    if($pin_order_count >= $pin_info['need_count'])
	    {
	        return true;
	    } else {
	        return false;
	    }
	}
	/**
	 * 拼团成功
	 */
	public function updatePintuanSuccess($pin_id)
	{

		$pin_up_sql = array();
		$pin_up_sql['state'] = 1;
		$pin_up_sql['success_time'] = time();

		M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id ) )->save( $pin_up_sql );

	    $pin_order_sql = "select po.order_id,og.order_goods_id,m.openid,m.we_openid,m.member_id,o.type,o.head_id,o.from_type,o.order_num_alias,o.delivery,og.name,og.price,og.store_id 
							from ".C('DB_PREFIX'). "eaterplanet_ecommerce_pin_order as po,".C('DB_PREFIX'). "eaterplanet_ecommerce_order as o,  
	                      ".C('DB_PREFIX'). "eaterplanet_ecommerce_order_goods as og,".C('DB_PREFIX'). "eaterplanet_ecommerce_member as m  
	                          where po.pin_id = ".$pin_id."  and o.order_status_id in(2) 
	                          and og.order_id = po.order_id and o.order_id = po.order_id and o.member_id= m.member_id order by po.add_time asc ";

		$pin_order_arr = M()->query($pin_order_sql);

		$i = 0;

		$order_model = D('Home/Frontorder');

		$template_id = D('Home/Front')->get_config_by_name('weprogram_template_pin_tuansuccess' );

		$pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');

		if( empty($pintuan_model_buy) )
		{
			$pintuan_model_buy = 0;
		}

		$url = D('Home/Front')->get_config_by_name('shop_domain');
		$community_model = D('Seller/Community');

		$fenxiao_model = D('Home/Commission');//D('Home/Fenxiao');

		$template_order_success_notice= D('Home/Front')->get_config_by_name('template_order_success_notice');

		$tuanzhang_member_id = 0;
		//拼团信息
		$pin_info = M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id ) )->find();
		//拼团返利订单设置是否发货
		$pin_order_arr = $this->setPintuanRebateOrderSuccess($pin_info, $pin_order_arr);

	    foreach($pin_order_arr as $pin_order)
	    {
			//拼团订单发货
			if($pin_order['is_send'] == 1){
				$order_model->change_order_status($pin_order['order_id'],1);

				$oh = array();

				$oh['order_id']=$pin_order['order_id'];
				$oh['order_status_id']=1;
				$oh['comment']='拼团成功';
				$oh['date_added']=time();
				$oh['notify']=1;

				M('eaterplanet_ecommerce_order_history')->add( $oh );

				//拼团成功加入缓存S中
				$day_time = strtotime( date('Y-m-d '.'00:00:00') );
				$day_key = 'new_ordernotice_'.$day_time;
				$day_arr = S( $day_key );

				if( empty($day_arr) )
				{
					$day_arr = array();
					$day_arr[] = $oh['order_id'];
				}else{
					$day_arr[] = $oh['order_id'];
				}

				S($day_key, $day_arr );

				//判断是否要插入到团长配送的订单中去,兼容团长逻辑 TODO...


				//if( $pintuan_model_buy == 1 && $pin_order['type'] != 'ignore' && $pin_order['delivery'] != 'express' && $pin_order['head_id'] > 0 )
				if( $pintuan_model_buy == 1 && $pin_order['type'] != 'ignore' && $pin_order['head_id'] > 0 )
				{
					$shipping_money = 0;
					if($pin_order['delivery'] == 'tuanz_send')
					{
						$shipping_money = $pin_order['shipping_fare'];
					}
					$community_model->ins_head_commiss_order($pin_order['order_id'],$pin_order['order_goods_id'], $shipping_money);
				}


				//发送拼团成功begin

				$pagepath = 'eaterplanet_ecommerce/moduleA/pin/share?id='.$pin_order['order_id'];

				//发送拼团成功end
				$member_formid_info = M('eaterplanet_ecommerce_member_formid')->where(" member_id=".$pin_order['member_id']." and formid != '' ")->order('id desc')->find();

				$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $pin_order['member_id'] ) )->find();




				$mb_subscribe = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $pin_order['member_id'], 'type' => 'pin_tuansuccess' ) )->find();

				//...todo
				if( !empty($mb_subscribe) )
				{
					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_pin_tuansuccess' );

					$pin_order_name = mb_substr( $pin_order['name'],0,10,'utf-8');

					$template_data = array();
					$template_data['number3'] = array('value' => $pin_order['order_num_alias'] );
					$template_data['thing1'] = array('value' => $pin_order_name );
					$template_data['amount7'] = array('value' => sprintf("%.2f",$pin_order['price']) );

					D('Seller/User')->send_subscript_msg( $template_data,$url,$pagepath,$member_info['we_openid'],$template_id );

					M('eaterplanet_ecommerce_subscribe')->where( array('id' => $mb_subscribe['id'] ) )->delete();
				}

				$wx_template_data = array();
				$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');

				if( !empty($weixin_appid) && !empty($template_id) )
				{
					$template_data = array();
					$template_data['keyword1'] = array('value' => $pin_order['order_num_alias'], 'color' => '#030303');
					$template_data['keyword2'] = array('value' => $pin_order['name'], 'color' => '#030303');
					$template_data['keyword3'] = array('value' => sprintf('%.2f', $pin_order['price'] ) , 'color' => '#030303');

					if( $pin_order['type'] == 'ignore' )
					{
						$template_data['keyword3'] = array('value' => '0元开团', 'color' => '#030303');
					}

					$res = D('Seller/User')->send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'], 0);


					$data = array();

					$data['username'] = $member_formid_info['formid'];
					$data['member_id'] = $order_info['member_id'];
					$data['avatar'] = json_encode($res );
					$data['order_time'] = time();
					$data['order_id'] = $order_id;
					$data['state'] = 0;
					$data['order_url'] = $member_info['we_openid'];
					$data['add_time'] = time();

					M('eaterplanet_ecommerce_notify_order')->add( $data );

					//客户下单成功发送公众号提醒给团长  weixin_template_order_buy
				}

				if($i == 0)
				{
					//暂时关闭发送拼团成功通知
					$tuanzhang_member_id = $pin_order['member_id'];




				} else {
					//插入佣金团分佣
					$this->ins_member_commiss_order($tuanzhang_member_id,$pin_order['order_id'],$pin_order['store_id'],$pin_order['order_goods_id']);

					//发送拼团成功

				}

				if($pin_order['delivery'] == 'pickup' && $pin_order['type'] != 'lottery')
				{	//如果订单是抽奖类型，那么久暂时不修改订单的发货状态
					//$order_model->change_order_status($pin_order['order_id'],4);
					//暂时关闭自提发货信息发送
					//$weixin_notify->sendPickupMsg($pin_order['order_id']);
				}

				//暂时关闭商品分佣
				//$fenxiao_model->ins_member_commiss_order($pin_order['member_id'],$pin_order['order_id'],$pin_order['store_id'],$pin_order['order_goods_id']);
				$fenxiao_model->ins_member_commiss_order($pin_order['member_id'],$pin_order['order_id'],$pin_order['store_id'], $pin_order['order_goods_id'] );

				//暂时关闭分享列表分佣
				//$share_model->send_order_commiss_money( $pin_order['order_id'] );

				//暂时关闭有新订单来的通知
				//$weixin_notify->send_neworder_template_msg($pin_order['store_id'],$pin_order['order_num_alias']);
				$i++;

				//通知开关状态 0为关，1为开

				if(!empty($template_order_success_notice))
				{
					//判断是否关联团长

					$weixin_template_order =array();
					$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
					$weixin_template_order_buy = D('Home/Front')->get_config_by_name('weixin_template_order_buy');
					if( !empty($weixin_appid) && !empty($weixin_template_order_buy) )
					{
						$head_pathinfo = "eaterplanet_ecommerce/moduleA/groupCenter/groupDetail?groupOrderId=".$pin_order['order_id'];

						$weixin_template_order = array(
								'appid' => $weixin_appid,
								'template_id' => $weixin_template_order_buy,
								'pagepath' => $head_pathinfo,
								'data' => array(
										'first' => array('value' => '您好团长，您收到了一个新订单，请尽快接单处理','color' => '#030303'),
										'tradeDateTime' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
										'orderType' => array('value' => '用户购买','color' => '#030303'),
										'customerInfo' => array('value' => $member_info['username'],'color' => '#030303'),
										'orderItemName' => array('value' => '订单编号','color' => '#030303'),

										'orderItemData' => array('value' => $pin_order['order_num_alias'],'color' => '#030303'),

										'remark' => array('value' => '点击查看订单详情','color' => '#030303'),
								)
						);
					}
					//$pin_order['head_id'] > 0

					$headid = $pin_order['head_id'];
					if($headid > 0)
					{

						$head_info = M('eaterplanet_community_head')->where( array('id' => $headid ) )->find();

						$head_weopenid = M('eaterplanet_ecommerce_member')->where( array('member_id' => $head_info['member_id'] ) )->find();

						$mnzember_formid_info = M('eaterplanet_ecommerce_member_formid')->where( "member_id=".$head_info['member_id']." and formid != ''" )->order('id desc')->find();


						$template_ids = D('Home/Front')->get_config_by_name('weprogram_template_pay_order');

						$sd_result = D('Seller/User')->send_wxtemplate_msg(array(),$url,$head_pathinfo,$head_weopenid['we_openid'],$template_ids,$mnzember_formid_info['formid'], 0,$weixin_template_order);


					}
				}

				$platform_send_info_member= D('Home/Front')->get_config_by_name('platform_send_info_member');

				if($platform_send_info_member){

					$template_ids = D('Home/Front')->get_config_by_name('weprogram_template_pay_order');

					$platform_send_info =array();
					$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
					$weixin_template_order_buy = D('Home/Front')->get_config_by_name('weixin_template_order_buy');
					if( !empty($weixin_appid) && !empty($weixin_template_order_buy) )
					{
						$head_pathinfo = "eaterplanet_ecommerce/moduleA/groupCenter/groupDetail?groupOrderId=".$pin_order['order_id'];

						$platform_send_info = array(
								'appid' => $weixin_appid,
								'template_id' => $weixin_template_order_buy,
								'pagepath' => $head_pathinfo,
								'data' => array(
										'first' => array('value' => '您好平台，您收到了一个新订单，请尽快接单处理','color' => '#030303'),
										'tradeDateTime' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
										'orderType' => array('value' => '用户购买','color' => '#030303'),
										'customerInfo' => array('value' => $member_info['username'],'color' => '#030303'),
										'orderItemName' => array('value' => '订单编号','color' => '#030303'),

										'orderItemData' => array('value' => $pin_order['order_num_alias'],'color' => '#030303'),

										'remark' => array('value' => '点击查看订单详情','color' => '#030303'),
								)
						);
					}



					$memberid = $platform_send_info_member;

					$result = explode(",", $memberid);

					foreach($result as $re){

						$pingtai = M('eaterplanet_ecommerce_member')->where( array('member_id' => $re ) )->find();

						$mnzember_formid_info = M('eaterplanet_ecommerce_member_formid')->where( " member_id={$re} and formid != '' " )->order('id desc')->find();

						$sd_result = D('Seller/User')->send_wxtemplate_msg(array(),$url,$head_pathinfo,$pingtai['we_openid'],$template_ids,$mnzember_formid_info['formid'], 0,$platform_send_info);


					}

				}
			}else{
				//拼团返利订单 不发货退款，进行返利
				if($pin_order['is_pintuan_rebate'] == 1) {
					$res = D('Home/Weixin')->refundOrder($pin_order['order_id'], 0);

					if( $res['code'] == 0 )
					{
						//拼团失败，订单退款
						$history_data = array();
						$history_data['order_id'] = $pin_order['order_id'];
						$history_data['order_status_id'] = 7;
						$history_data['notify'] = 0;
						$history_data['comment'] = '拼团失败，订单退款失败，错误原因： '.$res['msg'];
						$history_data['date_added'] = time();

						M('eaterplanet_ecommerce_order_history')->add( $history_data );

					}else{
						M('eaterplanet_ecommerce_order')->where( array('order_id' => $pin_order['order_id']) )->save( array('order_status_id' => 7) );

						//拼团失败，订单退款
						$history_data = array();
						$history_data['order_id'] = $pin_order['order_id'];
						$history_data['order_status_id'] = 7;
						$history_data['notify'] = 0;
						$history_data['comment'] = '拼团失败，订单退款';
						$history_data['date_added'] = time();

						M('eaterplanet_ecommerce_order_history')->add( $history_data );

						D('Seller/Commonorder')->refund_one_order( $pin_order['order_id'] , 1);

						//拼团返利订单 退款返利
						$this->pintuanRebateAmount($pin_info,$pin_order);
					}
				}
			}
	    }

		//拼团打印权限
		$is_open_pintuan_orderprint = D('Home/Front')->get_config_by_name('is_open_pintuan_orderprint');
		$is_print_auto = D('Home/Front')->get_config_by_name('is_print_auto');
		if($is_open_pintuan_orderprint == 1 && (empty($is_print_auto) || $is_print_auto == 0 )){
			foreach($pin_order_arr as $pin_order)
			{
				//小票打印
				D('Seller/Printaction')->check_print_order($pin_order['order_id']);
				D('Seller/Printaction')->check_print_order2($pin_order['order_id']);
			}
		}

	}

	/**
	 * @desc 拼团返利订单 退款返利
	 * @param $pin_info
	 * @param $pin_order
	 */
	public function pintuanRebateAmount($pin_info, $pin_order){
		$rebate_reward = $pin_info['rebate_reward'];//返利奖励：1、送积分，2、送余额
		$reward_amount = $pin_info['reward_amount'];//返利赠送的积分或余额数
		if($rebate_reward == 1){//送积分
			D('Seller/Order')->charge_member_score( $pin_order['member_id'] , $reward_amount, 'in',  'pintuan_rebate', $pin_order['order_id']);

		}else if($rebate_reward == 2){//送余额
			//赠送余额
			M('eaterplanet_ecommerce_member')->where( array('member_id' => $pin_order['member_id']) )->setInc('account_money',$reward_amount);
			$account_money_info = M('eaterplanet_ecommerce_member')->field('account_money')->where( array('member_id' =>$pin_order['member_id'] ) )->find();
			$account_money = $account_money_info['account_money'];

			$member_charge_flow_data = array();
			$member_charge_flow_data['member_id'] = $pin_order['member_id'];
			$member_charge_flow_data['money'] = $reward_amount;
			$member_charge_flow_data['operate_end_yuer'] = $account_money;
			$member_charge_flow_data['state'] = 21;
			$member_charge_flow_data['trans_id'] = $pin_order['order_id'];
			$member_charge_flow_data['order_goods_id'] = $pin_order['order_goods_id'];
			$member_charge_flow_data['charge_time'] = time();
			$member_charge_flow_data['remark'] = "拼团返利赠送余额:".$reward_amount."元";
			$member_charge_flow_data['add_time'] = time();
			M('eaterplanet_ecommerce_member_charge_flow')->add($member_charge_flow_data);
		}
	}

	/**
	 * @desc 拼团返利设置订单是否发货，普通拼团全部发货
	 * @param $pin_id
	 * @param $pin_order_arr
	 * @return mixed
	 */
	public function setPintuanRebateOrderSuccess($pin_info, $pin_order_arr){
		$pin_id = $pin_info['pin_id'];
		$is_pintuan_rebate = $pin_info['is_pintuan_rebate'];//是否拼团返利，1、是，0、否
		//新人参团	0、无限制，1、必中，2、必不中
		$new_member_group_reward_type = D('Home/Front')->get_config_by_name('new_member_group_reward_type');
		if($is_pintuan_rebate == 1){//拼团返利订单
			$random_delivery_count = $pin_info['random_delivery_count'];//随机发货人数
			//拼团订单数
			$pin_count = count($pin_order_arr);
			if($pin_count <= $random_delivery_count){//拼团订单不足随机发货人数，全部订单发货
				foreach($pin_order_arr as $k=>$v){
					$member_id = $v['member_id'];
					//判断是否新人参团
					$pin_order_arr[$k]['is_new'] = $this->checkIsNewPintuanRebate($member_id,$pin_id);
					if($new_member_group_reward_type == 2 && $pin_order_arr[$k]['is_new'] == 1){
						$pin_order_arr[$k]['is_send'] = 0;
					}else{
						$pin_order_arr[$k]['is_send'] = 1;
					}
					$pin_order_arr[$k]['is_pintuan_rebate'] = 1;
				}
			}else{
				$new_orderid_array = array();//新人订单id数组
				$all_array = array();//所有订单id数组
				foreach($pin_order_arr as $k=>$v){
					$all_array[] = $v['order_id'];
					$member_id = $v['member_id'];
					//判断是否新人参团
					$pin_order_arr[$k]['is_new'] = $this->checkIsNewPintuanRebate($member_id,$pin_id);
					if($pin_order_arr[$k]['is_new'] == 1){
						$new_orderid_array[] = $v['order_id'];
					}
					$pin_order_arr[$k]['is_pintuan_rebate'] = 1;
				}
				//设置拼团返利订单是否发货
				$pin_order_arr = $this->setPintuanRebateOrderSend($pin_order_arr,$random_delivery_count, $new_orderid_array,$all_array);
			}
		}else{//普通拼团订单
			foreach($pin_order_arr as $k=>$v){
				$pin_order_arr[$k]['is_send'] = 1;
				$pin_order_arr[$k]['is_pintuan_rebate'] = 0;
			}
		}
		return $pin_order_arr;
	}

	/**
	 * @desc 判断客户是否新人（新人指未付款过的客户）
	 * @param $member_id
	 * @return int
	 */
	public function checkIsNewPintuanRebate($member_id){
		$is_new = 0;
		$sql = " SELECT count(1) as count FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o "
			 . " where o.member_id=".$member_id
			 . " and o.order_status_id not in (3,5)";
		$pin_arr = M()->query($sql);
		if(empty($pin_arr) || $pin_arr[0]['count'] <= 1){
			$is_new = 1;
		}
		return $is_new;
	}

	/**
	 * @desc 拼团返利订单设置发货
	 * @param $pin_order_arr 拼团订单信息
	 * @param $random_delivery_count 随机发货数
	 * @param $new_orderid_array	新人订单id数组
	 * @param $all_orderid_array	订单id数组
	 */
	public function setPintuanRebateOrderSend($pin_order_arr,$random_delivery_count, $new_orderid_array,$all_array){
		$order_ids_array = array();
		//新人参团	0、无限制，1、必中，2、必不中
		$new_member_group_reward_type = D('Home/Front')->get_config_by_name('new_member_group_reward_type');
		if($new_member_group_reward_type == 0){//新人无限制
			if($random_delivery_count >= count($all_array)){
				$order_ids_array = $all_array;
			}else{
				//获取随机订单id
				$order_ids_array = $this->getRandomArray($all_array, $random_delivery_count);
			}
		}else if($new_member_group_reward_type == 1){//新人必中
			if($random_delivery_count >= count($all_array)){
				$order_ids_array = $all_array;
			}else{
				if($random_delivery_count > count($new_orderid_array)){
					//剩余订单数
					$sy_count = $random_delivery_count - count($new_orderid_array);
					//去除新人订单
					$order_ids = $this->removeArrayVal($all_array, $new_orderid_array);
					//随机获取剩余订单
					$sy_orderids_array = $this->getRandomArray($order_ids, $sy_count);
					//合并新人订单和随机剩余订单
					$order_ids_array = array_merge($sy_orderids_array,$new_orderid_array);
					$order_ids_array = array_unique($order_ids_array);
				}else if($random_delivery_count <= count($new_orderid_array)){
					$order_ids_array = $new_orderid_array;
				}
			}
		}else if($new_member_group_reward_type == 2){//新人必不中
			//去除新人订单
			$order_ids = $this->removeArrayVal($all_array, $new_orderid_array);
			//随机获取订单
			$order_ids_array = $this->getRandomArray($order_ids, $random_delivery_count);
		}
		foreach($pin_order_arr as $k=>$v){
			if (in_array($v['order_id'], $order_ids_array)){
				$pin_order_arr[$k]['is_send'] = 1;
			}else{
				$pin_order_arr[$k]['is_send'] = 0;
			}
		}
		return $pin_order_arr;
	}

	/**
	 * @desc 去除前一个数组包含后面一个数组的值
	 * @param $order_ids
	 * @param $other_ids
	 * @return mixed
	 */
	public function removeArrayVal($order_ids, $other_ids){
		foreach($other_ids as $k=>$v){
			foreach( $order_ids as $ak=>$av) {
				if($av == $v){
					unset($order_ids[$ak]);
				}
			}
		}
		return $order_ids;
	}

	/**
	 * @desc 获取数组随机值
	 * @param $array	数组
	 * @param $num		随机个数
	 * @return array
	 */
	public function getRandomArray($array, $num){
		if($num == 1){
			$key = array_rand($array , $num);
			$random_keys[] = $key;
		}else{
			$random_keys = array_rand($array, $num);
		}
		$result = array();
		foreach($random_keys as $k=>$v){
			$result[] = $array[$v];
		}
		return $result;
	}

	public function get_area_info($id)
	{
		$area_info = M('eaterplanet_ecommerce_area')->where( array('id' => $id ) )->find();

		return $area_info;
	}

	/**
	 * 开新团
	 */
	function openNewTuan($order_id,$goods_id,$member_id){

		$goods_detail = M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id ) )->find();


	    $pin_data = array();
	    $pin_data['user_id'] = $member_id;

	    $pin_data['order_id'] = $order_id;
	    $pin_data['state'] = 0;
		$pin_data['is_commiss_tuan'] = 0;
	    $pin_data['is_newman_takein'] = 0;
	    $pin_data['begin_time'] = time();
	    $pin_data['add_time'] = time();

		if($goods_detail['type'] == 'pin')
		{
			$pin_data['is_lottery'] = 0;
	    	$pin_data['lottery_state'] = 0;

			$goods_info = M('eaterplanet_ecommerce_good_pin')->where( array('goods_id' => $goods_id ) )->find();

			$pin_data['end_time'] = time() + intval(60*60 * $goods_info['pin_hour']);
			if($pin_data['end_time'] > $goods_info['end_time'])
			{
				$pin_data['end_time'] = $goods_info['end_time'];
			}
			$pin_data['need_count'] = $goods_info['pin_count'];

			//order_id
			$order = M('eaterplanet_ecommerce_order')->field('type')->where( array('order_id' => $order_id ) )->find();
			//if( $order['type'] == 'ignore' )
			//{

				$goods_pin = M('eaterplanet_ecommerce_good_pin')->where( array('goods_id' => $goods_id ) )->find();

				$pin_data['is_commiss_tuan'] = $goods_pin['is_commiss_tuan'];
				$pin_data['is_newman_takein'] = $goods_pin['is_newman'];
			//}

			$pin_data['is_pintuan_rebate'] = $goods_pin['is_pintuan_rebate'];
			$pin_data['random_delivery_count'] = $goods_pin['random_delivery_count'];
			$pin_data['rebate_reward'] = $goods_pin['rebate_reward'];
			if($goods_pin['rebate_reward'] == 1){//送积分
				$pin_data['reward_amount'] = $goods_pin['reward_point'];
			}else if($goods_pin['rebate_reward'] == 2){//送余额
				$pin_data['reward_amount'] = $goods_pin['reward_balance'];
			}

		}else if($goods_detail['type'] == 'lottery')
		{
			$pin_data['is_lottery'] = 1;
	    	$pin_data['lottery_state'] = 0;
		}

		$pin_id = M('eaterplanet_ecommerce_pin')->add( $pin_data );

		//$pin_data['need_count']
		$total = $pin_data['need_count'];
		D('Seller/Redisorder')->sysnc_pintuan_total($pin_id, $total);

	    return $pin_id;
	}


	/**
		检测拼团商品是否可以0元开团
	**/
	public function check_goods_iszero_opentuan( $goods_id )
	{
		$pin_goods = M('eaterplanet_ecommerce_good_pin')->where( array('goods_id' => $goods_id) )->find();

		if( $pin_goods['is_commiss_tuan'] == 1 && $pin_goods['is_zero_open'] == 1 )
		{
			return 1;
		}else{
			return 0;
		}

	}



	/**
		插入订单通知表
	**/

	function insertNotifyOrder($order_id)
	{

		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->field('member_id,order_num_alias')->find();

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();

		$group_url = '';

		$data = array();

		$data['username'] = $member_info['username'];
		$data['member_id'] = $order_info['member_id'];
		$data['avatar'] = $member_info['avatar'];
		$data['order_time'] = time();
		$data['order_id'] = $order_id;
		$data['state'] = 0;
		$data['order_url'] = $group_url;
		$data['add_time'] = time();

		M('eaterplanet_ecommerce_notify_order')->add( $data );

		$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->find();

		//发送模板消息
		$is_tuanz =  $this->checkOrderIsTuanzhang($order_id );

		$order_num_alias = $order_info['order_num_alias'];
		$name = $order_goods_info['name'];
		$price = $order_goods_info['price'];

		$url =  D('Home/Front')->get_config_by_name('shop_domain');
		if( $is_tuanz )
		{
			//$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);

			//发送开团消息
			$pin_info = M('eaterplanet_ecommerce_pin')->where( array('order_id' => $order_id ) )->find();

			//订单编号 团购名称 拼团价 邀请人数 截止时间
			$need_count = $pin_info['need_count'];
			$end_time = date('Y-m-d H:i:s', $pin_info['end_time']);

			$template_id = D('Home/Front')->get_config_by_name('weprogram_template_open_tuan');

			$pagepath = 'eaterplanet_ecommerce/moduleA/pin/share?id='.$order_id;

			//发送拼团成功end
			$member_formid_info = M('eaterplanet_ecommerce_member_formid')->where(" member_id=".$order_info['member_id']." and formid != '' ")->order('id desc')->find();

			$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();


				$mb_subscribe = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $order_info['member_id'], 'type' => 'open_tuan' ) )->find();



				//...todo
				if( !empty($mb_subscribe) )
				{

					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_open_tuan');

					$name2 = mb_substr($name,0,4,'utf-8');


					$template_data = array();
					$template_data['thing1'] = array('value' => $name2 );
					$template_data['amount2'] = array('value' => sprintf("%.2f", $price) );
					$template_data['thing3'] = array('value' => $need_count );
					$template_data['time5'] = array('value' => $end_time );

					$res =  D('Seller/User')->send_subscript_msg( $template_data,$url,$pagepath,$member_info['we_openid'],$template_id);




					M('eaterplanet_ecommerce_subscribe')->where( array('id' => $mb_subscribe['id'] ) )->delete();

				}


			$wx_template_data = array();
			$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');




		}else{
			//发送参团消息

			$template_id = D('Home/Front')->get_config_by_name('weprogram_template_take_tuan' );

			$pagepath = 'eaterplanet_ecommerce/moduleA/pin/share?id='.$order_id;

			//发送拼团成功end
			\Think\Log::record("begin 1");

			$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();




				$mb_subscribe = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $order_info['member_id'], 'type' => 'take_tuan' ) )->find();

				//...todo
				if( !empty($mb_subscribe) )
				{
				    \Think\Log::record("begin 2");

					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_take_tuan');

					$name = mb_substr($name,0,10,'utf-8');

					$template_data = array();
					$template_data['thing1'] = array('value' => $name );
					$template_data['number2'] = array('value' => $order_num_alias );
					$template_data['amount3'] = array('value' => sprintf("%.2f", $price) );


					$res = D('Seller/User')->send_subscript_msg( $template_data,$url,$pagepath,$member_info['we_openid'],$template_id);

					\Think\Log::record("begin 3". json_encode($res) );

					M('eaterplanet_ecommerce_subscribe')->where( array('id' => $mb_subscribe['id'] ) )->delete();

				}



		}


	}


	/**
	 * 插入拼团订单
	 *
	 */
	function insertTuanOrder($pin_id,$order_id)
	{
	    $pin_order_data = array();
	    $pin_order_data['pin_id'] = $pin_id;
	    $pin_order_data['order_id'] = $order_id;
	    $pin_order_data['add_time'] = time();
	    M('eaterplanet_ecommerce_pin_order')->add($pin_order_data);

	}


	/***
		生成拼团的佣金账户
	**/
	public function commission_account($member_id)
	{

		$info = M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $member_id ) )->find();

		if( empty($info) )
		{
			$ins_data = array();
			$ins_data['member_id'] = $member_id;
			$ins_data['money'] = 0;
			$ins_data['dongmoney'] = 0;
			$ins_data['getmoney'] = 0;

			M('eaterplanet_ecommerce_pintuan_commiss')->add( $ins_data );
		}
	}


	public function send_pinorder_commiss_money($order_id,$order_goods_id)
	{

		$pintuan_commiss_order = M('eaterplanet_ecommerce_pintuan_commiss_order')->where( array('order_id' => $order_id ,'order_goods_id' =>$order_goods_id,'state' => 0 ) )->find();

		if( !empty($pintuan_commiss_order) )
		{

			M('eaterplanet_ecommerce_pintuan_commiss_order')->where( array('id' => $pintuan_commiss_order['id'] ) )->save( array('state' => 1,'statement_time' => time() ) );

			$this->commission_account($pintuan_commiss_order['member_id']);

			M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $pintuan_commiss_order['member_id'] ) )->setInc('money',$pintuan_commiss_order['money'] );
		}

	}


	/**
		拼团成功后，给团长发放订单提成到记录表
		拼团成功后，可以发送佣金
		@param $member_id 团长的id
	**/
	public function ins_member_commiss_order($member_id,$order_id,$store_id,$order_goods_id )
	{
		//判断商品是否开启佣金团  eaterplanet_ecommerce_order_goods

		$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id ) )->find();

		if( empty($order_goods_info) )
		{
			return false;
		}

		$goods_id = $order_goods_info['goods_id'];

		//找出佣金是多少
		$goods_pin = M('eaterplanet_ecommerce_good_pin')->where( array('goods_id' => $goods_id ) )->find();

		if( empty($goods_pin) )
		{
			return false;
		}

		if( $goods_pin['is_commiss_tuan'] == 1 )
		{
			$commiss_type  = $goods_pin['commiss_type'];
			$commiss_money = $goods_pin['commiss_money'];

			if( $commiss_type == 0 )
			{
				$commiss_money = round( ($commiss_money * ( $order_goods_info['total'] + $order_goods_info['shipping_fare'] - $order_goods_info['voucher_credit'] ) ) / 100,2);
			}
			//注入记录中，待结算

			//eaterplanet_ecommerce_pintuan_commiss_order
			$com_order_data = array();

			$com_order_data['member_id'] = $member_id;
			$com_order_data['order_id'] = $order_id;
			$com_order_data['order_goods_id'] = $order_goods_id;
			$com_order_data['type'] = $commiss_type == 0 ? 1:2;
			$com_order_data['bili'] = $goods_pin['commiss_money'];
			$com_order_data['store_id'] = $store_id;
			$com_order_data['state'] = 0;
			$com_order_data['money'] = $commiss_money;
			$com_order_data['addtime'] = time();

			M('eaterplanet_ecommerce_pintuan_commiss_order')->add( $com_order_data );

		}

	}


	/***
		客户拼团佣金申请，余额 审核流程
	**/
	public function send_apply_yuer( $id )
	{

		$info = M('eaterplanet_ecommerce_pintuan_tixian_order')->where( array('id' => $id ) )->find();

		if( $info['type'] == 1 && $info['state'] == 0 )
		{
			$del_money = $info['money'] - $info['service_charge_money'];
			if( $del_money >0 )
			{
				D('Seller/User')->sendMemberMoneyChange($info['member_id'], $del_money, 11, '拼团佣金提现到余额,提现id:'.$id);
			}
		}

		M('eaterplanet_ecommerce_pintuan_tixian_order')->where(  array('id' => $id ) )->save( array('state' => 1,'shentime' => time() ) );

		$money = $info['money'];

		//将冻结的钱划一部分到已提现的里面
		M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $info['member_id']) )->setInc('getmoney',$money );
		M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $info['member_id']) )->setInc('dongmoney',-$money );

		return array('code' => 0,'msg' => '提现成功');
	}

	/**
		提现到微信零钱
	**/
	public function send_apply_weixin_yuer($id)
	{
		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';

		require_once $lib_path."/Weixin/lib/WxPay.Api.php";

		$open_weixin_qiye_pay = D('Home/Front')->get_config_by_name('open_weixin_qiye_pay');

		$info = M('eaterplanet_ecommerce_pintuan_tixian_order')->where( array('id' => $id ) )->find();

		if( empty($open_weixin_qiye_pay) || $open_weixin_qiye_pay ==0 )
		{
			return array('code' => 1,'msg' => '未开启企业付款');
		}else{
			if( $info['type'] == 2 && $info['state'] == 0 )
			{
				$del_money = $info['money'] - $info['service_charge_money'];
				if( $del_money >0 )
				{
					$mb_info = M('eaterplanet_ecommerce_member')->field('we_openid')->where( array('member_id' => $info['member_id'] ) )->find();

					$partner_trade_no = build_order_no($info['id']);
					$desc = date('Y-m-d H:i:s').'申请的提现已到账';

					$username = $info['bankusername'];
					$amount = $del_money * 100;

					$openid = $mb_info['we_openid'];

					$res =  \WxPayApi::payToUser($openid,$amount,$username,$desc,$partner_trade_no);

					if(empty($res) || $res['result_code'] =='FAIL')
					{
						//show_json(0, $res['err_code_des']);
						return array('code' => 1,'msg' => $res['err_code_des'] );
					}else{

						M('eaterplanet_ecommerce_pintuan_tixian_order')->where( array('id' => $id ) )->save( array('state' => 1,'shentime' => time() ) );

						$money = $info['money'];

						//将冻结的钱划一部分到已提现的里面

						M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $info['member_id'] ) )->setInc('getmoney',$money);
						M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $info['member_id'] ) )->setInc('dongmoney',-$money);

						return array('code' => 0,'msg' => '提现成功');
					}
				}
			}else{
				return array('code' => 1,'msg' => '已提现');
			}
		}

	}

	/***
		提现到支付宝，提现到银行卡
	**/
	public function send_apply_alipay_bank($id)
	{
		$info = M('eaterplanet_ecommerce_pintuan_tixian_order')->where( array('id' => $id ) )->find();

		if( ( $info['type'] == 3 || $info['type'] == 4) && $info['state'] == 0 )
		{
			M('eaterplanet_ecommerce_pintuan_tixian_order')->where( array('id' => $id ) )->save( array('state' => 1,'shentime' => time() ) );

			$money = $info['money'];

			//将冻结的钱划一部分到已提现的里面
			M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $info['member_id']) )->setInc('getmoney',$money);
			M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $info['member_id']) )->setInc('dongmoney',-$money);

			return array('code' => 0,'msg' => '提现成功');
		}else{

			return array('code' => 1,'msg' => '已提现');
		}
	}


	public function send_apply_success_msg($apply_id)
	{
		$apply_info = M('eaterplanet_ecommerce_pintuan_tixian_order')->where( array('id' => $apply_id ) )->find();

		$member_info = M('eaterplanet_ecommerce_member')->field('we_openid')->where( array('member_id' => $apply_info['member_id'] ) )->find();

		switch($apply_info['type'])
		{
			case 1:
				$bank_name = '余额';
			break;
			case 2:
				$bank_name = '微信余额';
			break;
			case 3:
				$bank_name = '支付宝';
			break;
			case 4:
				$bank_name = '银行卡';
			break;
		}


		$dao_zhang = floatval( $apply_info['money']-$apply_info['service_charge_money']);

		$template_data = array();
		$template_data['keyword1'] = array('value' => sprintf("%01.2f", $apply_info['money']), 'color' => '#030303');
		$template_data['keyword2'] = array('value' => $apply_info['service_charge_money'], 'color' => '#030303');

		$template_data['keyword3'] = array('value' => sprintf("%01.2f", $dao_zhang), 'color' => '#030303');

		$template_data['keyword4'] = array('value' => $bank_name, 'color' => '#030303');

		$template_data['keyword5'] = array('value' => '提现成功', 'color' => '#030303');
		$template_data['keyword6'] = array('value' => date('Y-m-d H:i:s' , $apply_info['addtime']), 'color' => '#030303');
		$template_data['keyword7'] = array('value' => date('Y-m-d H:i:s' , $apply_info['shentime']), 'color' => '#030303');


		$template_id = D('Home/Front')->get_config_by_name('weprogram_template_apply_tixian');

		$url = D('Home/Front')->get_config_by_name('shop_domain');
		$pagepath = 'eaterplanet_ecommerce/pages/user/me';

		$member_formid_info = M('eaterplanet_ecommerce_member_formid')->where(" member_id=".$head_info['member_id']." and formid != '' and state =0 ")->order('id desc')->find();

		if(!empty( $member_formid_info ))
		{

			$wx_template_data = array();
			$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
			$weixin_template_apply_tixian = D('Home/Front')->get_config_by_name('weixin_template_apply_tixian');

			if( !empty($weixin_appid) && !empty($weixin_template_apply_tixian) )
			{
				$wx_template_data = array(
									'appid' => $weixin_appid,
									'template_id' => $weixin_template_apply_tixian,
									'pagepath' => $pagepath,
									'data' => array(
													'first' => array('value' => '尊敬的用户，您的提现已到账','color' => '#030303'),
													'keyword1' => array('value' => date('Y-m-d H:i:s' , $apply_info['addtime']),'color' => '#030303'),
													'keyword2' => array('value' => $community_head_commiss_info['bankname'],'color' => '#030303'),
													'keyword3' => array('value' => sprintf("%01.2f", $apply_info['money']),'color' => '#030303'),
													'keyword4' => array('value' => $apply_info['service_charge'],'color' => '#030303'),
													'keyword5' => array('value' => sprintf('%.2f', $dao_zhang),'color' => '#030303'),
													'remark' => array('value' => '请及时进行对账','color' => '#030303'),
											)
								);
			}

			D('Seller/User')->send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'] , 0,$wx_template_data);

			M('eaterplanet_ecommerce_member_formid')->where( array('id' => $member_formid_info['id'] ) )->save( array('state' => 1) );
		}
	}



}
