<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://www.eaterplanet.com/
 * @copyright Copyright (c) 2019-2022 Dejavu.Tech.
 * @license   https://www.eaterplanet.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Home\Controller;
use Home\Model\OrderModel;
class OrderController extends CommonController {

	protected function _initialize(){
		parent::_initialize();
		 // 获取当前用户ID
	}

	/**
		直接取消订单
		1、已付款待发货 状态
		2、是不是自己的订单
		3、判断后台是否开启了状态
		4、记录日志
		5、处理订单，
		6、处理退款，
		7、打印小票

		结束
	**/
	public function del_cancle_order()
	{
		$gpc = I('request.');
		$_GPC = I('request.');

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}

		$member_id = $weprogram_token['member_id'];

		$order_id = $_GPC['order_id'];

		$order_info = M('eaterplanet_ecommerce_order')->where( array('member_id' => $member_id,'order_id' => $order_id) )->find();

		$order_member_name = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($order_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '订单不存在') );
			die();
		}

		if( $order_info['order_status_id'] == 1)
		{
			$order_can_del_cancle = D('Home/Front')->get_config_by_name('order_can_del_cancle');

			if( empty($order_can_del_cancle) || $order_can_del_cancle == 0 )
			{
				//4、记录日志
				$order_history = array();
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 5;
				$order_history['notify'] = 0;
				$order_history['comment'] = '客户前台申请，直接取消已支付待发货订单';
				$order_history['date_added'] = time();

				M('eaterplanet_ecommerce_order_history')->add($order_history);


				//5、处理订单
				$result = D('Home/Weixin')->del_cancle_order($order_id);

				//6、发送取消通知订单给平台

				$weixin_template_cancle_order = D('Home/Front')->get_config_by_name('weixin_template_cancle_order');
				$platform_send_info_member_id = D('Home/Front')->get_config_by_name('platform_send_info_member');

				if( !empty($weixin_template_cancle_order) && !empty($platform_send_info_member_id) )
				{
					$weixin_template_order =array();
					$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid' );


					if( !empty($weixin_appid) && !empty($weixin_template_cancle_order) )
					{
						$head_pathinfo = "eaterplanet_ecommerce/pages/index/index";

						$pl_member_id =  explode(",", $platform_send_info_member_id);

						foreach($pl_member_id as $m_id){

							$weopenid = M('eaterplanet_ecommerce_member')->where( array('member_id' => $m_id ) )->find();

							$weixin_template_order = array(
													'appid' => $weixin_appid,
													'template_id' => $weixin_template_cancle_order,
													'pagepath' => $head_pathinfo,
													'data' => array(
																	'first' => array('value' => '您好，您收到了一个取消订单，请尽快处理','color' => '#030303'),
																	'keyword1' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),
																	'keyword2' => array('value' => '取消订单','color' => '#030303'),
																	'keyword3' => array('value' => sprintf("%01.2f", $order_info['total']),'color' => '#030303'),
																	'keyword4' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
																	'keyword5' => array('value' => $order_member_name['username'],'color' => '#030303'),
																	'remark' => array('value' => '此订单已于'.date('Y-m-d H:i:s').'被用户取消，请尽快处理','color' => '#030303'),
																	)
											);

							D('Seller/User')->just_send_wxtemplate($weopenid['we_openid'], 0, $weixin_template_order );

						}



					}
				}

				if( $result['code'] == 0 && $order_info['type'] != 'integral')
				{

					$is_print_cancleorder = D('Home/Front')->get_config_by_name('is_print_cancleorder');
					if( isset($is_print_cancleorder) && $is_print_cancleorder == 1 )
					{
						D('Seller/Printaction')->check_print_order($order_id,'用户取消订单');
						D('Seller/Printaction')->check_print_order2($order_id,'用户取消订单');
					}

					echo json_encode( array('code' => 0 ) );
					die();
				}
				else if($result['code'] == 0 && $order_info['type'] == 'integral')
				{
				    echo json_encode( array('code' => 0 ) );
				    die();
				}
				else{
					echo json_encode( array('code' => 1, 'msg' => $result['msg'] ) );
					die();
				}

			}else{
				echo json_encode( array('code' => 1, 'msg' => '未开启此项功能') );
				die();
			}

		}else{
			echo json_encode( array('code' => 1, 'msg' => '订单状态不正确，只有已支付，未发货的订单才能取消') );
			die();
		}


	}

	public function order_info()
	{
		$gpc = I('request.');

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}

		$member_id = $weprogram_token['member_id'];



		$order_id = $gpc['id'];

		$is_supply = isset($gpc['is_supply']) ? $gpc['is_supply'] :'';

		if( $is_supply > 0 )
		{
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');

			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}

			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();

			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$ck_order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('supply_id' => $supply_info['id'], 'order_id' =>  $order_id ) )->find();

				if( !empty($ck_order_goods) )
				{
					$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $ck_order_goods['order_id']) )->find();
				}else{
					echo json_encode( array('code' => 2 , 'msg' => '非法操作') );
					die();
				}
			}else{
				echo json_encode( array('code' => 2, 'msg' => '非法操作') );
				die();
			}
		}else{
			$order_info = M('eaterplanet_ecommerce_order')->where( array('member_id' => $member_id,'order_id' => $order_id) )->find();
		}
	    //gpc

		if($order_info['is_change_price'] == 1){
			$order_info['admin_change_price'] = $order_info['total'] - $order_info['old_price'];
		}

		$pick_up_info = array();
		$pick_order_info = array();

		if( $order_info['delivery'] == 'pickup' )
		{
			//查询自提点
			$pick_order_info = array();

			$pick_id = 0;
			$pick_up_info = array();

		}
		if( $order_info['delivery'] == 'localtown_delivery' && ($order_info['order_status_id'] != 3 && $order_info['order_status_id'] != 5 ) )
		{
			$order_info['orderdistribution_order'] = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_info['order_id'] ) )->find();
			if( !empty($order_info['orderdistribution_order']) && $order_info['orderdistribution_order'] > 0 )
			{
				$orderdistribution = M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => $order_info['orderdistribution_order']['orderdistribution_id']  ) )->find();
				$order_info['orderdistribution_order']['username'] = $orderdistribution['username'];
			}
		}
		$order_status_info = M('eaterplanet_ecommerce_order_status')->where( array('order_status_id' => $order_info['order_status_id'] ) )->find();

	    //10 name
		if($order_info['order_status_id'] == 10)
		{
			$order_status_info['name'] = '等待退款';
		}
		else if($order_info['order_status_id'] == 4 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '待自提';
			//已自提
		}
		else if($order_info['order_status_id'] == 6 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '已自提';

		}
		else if($order_info['order_status_id'] == 1 && $order_info['type'] == 'lottery')
		{
			//等待开奖
			//一等奖
			if($order_info['lottery_win'] == 1)
			{
				$order_status_info['name'] = '一等奖';
			}else {
				$order_status_info['name'] = '等待开奖';
			}

		}
		//核销订单
		if( $order_info['delivery'] == 'hexiao' ){
			if($order_info['store_id'] == 0){
				$order_info['shopname'] = '平台自营';
			}else{
				$store_info = M('eaterplanet_ecommerce_supply')->where( array('id' => $order_info['store_id'] ) )->find();
				$order_info['shopname'] = $store_info['shopname'];
			}
			//订单卷码
			$order_info['hexiao_volume_code'] = $order_info['hexiao_volume_code'];
			//二维码
			if(empty($order_info['hexiao_qr_code'])){
				$order_info['hexiao_qr_code'] = D('Home/Salesroom')->_get_ordergoods_hxqrcode($order_id,'',$order_info['hexiao_volume_code']);
			}else{
				$order_info['hexiao_qr_code'] = tomedia($order_info['hexiao_qr_code']);
			}
			//门店列表
			$salesroom_list = D('Home/Salesroom')->get_order_salesroom($order_id,0);
			foreach($salesroom_list as $slk=>$slv){
				$member_lon = $gpc['longitude'];
				$member_lat = $gpc['latitude'];
				$salesroom_list[$slk]['distance'] = D('Home/Salesroom')->cal_salesroom_distance($slv,$member_lon,$member_lat);
			}
			$distances = array_column($salesroom_list,'distance');
			array_multisort($distances,SORT_ASC,$salesroom_list);
			$order_info['salesroom_list'] = $salesroom_list;
		}
		//$order_info['type']

		//open_auto_delete


		if($order_info['order_status_id'] == 3)
		{
			$open_auto_delete = D('Home/Front')->get_config_by_name('open_auto_delete');
			$auto_cancle_order_time = D('Home/Front')->get_config_by_name('auto_cancle_order_time');

			$order_info['open_auto_delete'] = $open_auto_delete;
			//date_added
			if($open_auto_delete == 1)
			{
				$order_info['over_buy_time'] = $order_info['date_added'] + 3600 * $auto_cancle_order_time;
				$order_info['cur_time'] = time();
			}

		}


		$shipping_province = M('eaterplanet_ecommerce_area')->where( array('id' => $order_info['shipping_province_id'] ) )->find();

		$shipping_city = M('eaterplanet_ecommerce_area')->where( array('id' => $order_info['shipping_city_id'] ) )->find();

		$shipping_country = M('eaterplanet_ecommerce_area')->where( array('id' => $order_info['shipping_country_id'] ) )->find();


	    $order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->select();


		$shiji_total_money = 0;
		$member_youhui = 0.00;


		$pick_up_time = "";
		$pick_up_type = -1;
		$pick_up_weekday = '';
		$today_time = $order_info['pay_time'];

		$arr = array('天','一','二','三','四','五','六');

		$url = D('Home/Front')->get_config_by_name('shop_domain').'/';

		$attachment_type = D('Home/Front')->get_config_by_name('attachment_type');
		$qiniu_url = D('Home/Front')->get_config_by_name('qiniu_url');

		$is_can_received = 1;//是否可以收货，1、可以，0、不可以

		$hx_receive_info = array();//核销订单已收到待收货情况
		$receive_count = 0;//已收货
		$wait_count = 0;//待收货
		$goods_quantity = 0;//商品数量
		$refund_goods_quantity = 0;//退款商品数量
		$volume_code_list = array();//卷码列表
		$hx_order_count = 0;//按订单核销商品订单数
		$hx_time_count = 0;//按次数核销商品订单数
		$vi = 0;
	    foreach($order_goods_list as $key => $order_goods)
	    {
	    	$order_goods['name'] = htmlspecialchars_decode(stripslashes($order_goods['name']));
			$order_refund_goods = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' =>$order_id,'order_goods_id' => $order_goods['order_goods_id'] ) )->order('ref_id desc')->find();

			if(!empty($order_refund_goods))
			{
				$order_refund_goods['addtime'] = date('Y-m-d H:i:s', $order_refund_goods['addtime']);

				if($order_refund_goods['state'] == 0 || $order_refund_goods['state'] == 2){
					$is_can_received = 0;
				}
			}

			$order_option_info = M('eaterplanet_ecommerce_order_option')->field('value')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' => $order_id) )->select();

			$order_goods['order_refund_goods'] = $order_refund_goods;

	        foreach($order_option_info as $option)
	        {
	            $order_goods['option_str'][] = $option['value'];
	        }
			if(empty($order_goods['option_str']))
			{
				//option_str
				 $order_goods['option_str'] = '';
			}else{
				 $order_goods['option_str'] = implode(',', $order_goods['option_str']);
			}
	       //
		    $order_goods['shipping_fare'] = round($order_goods['shipping_fare'],2);
		    $order_goods['price'] = round($order_goods['price'],2);
		    $order_goods['total'] = round($order_goods['total'],2);

			if( $order_goods['is_vipcard_buy'] == 1 || $order_goods['is_level_buy'] ==1 )
			{
				$order_goods['price'] = round($order_goods['oldprice'],2);
			}
			$order_goods['real_total'] = round($order_goods['quantity'] * $order_goods['price'],2);

			/**
					$goods_images = file_image_thumb_resize($vv['goods_images'],400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= tomedia( file_image_thumb_resize($vv['goods_images'],400) );
					}

			**/


			if($attachment_type == 1)
			{
				$goods_images = $qiniu_url.resize($order_goods['goods_images'],400,400);
			}else{
				$goods_images = $url.resize($order_goods['goods_images'],400,400);
			}

			if( !is_array($goods_images) )
			{
				 $order_goods['image']=  tomedia( $goods_images );
				$order_goods['goods_images']= tomedia( $goods_images );
			}else{
				 $order_goods['image']=  $order_goods['goods_images'];
			}

		   $order_goods['hascomment'] = 0;

			$order_goods_comment_info = M('eaterplanet_ecommerce_order_comment')->field('comment_id')->where( array('goods_id' => $order_goods['goods_id'],'order_id' =>$order_id) )->find();


			if( !empty($order_goods_comment_info) )
			{
				$order_goods['hascomment'] = 1;
			}

			$order_goods['can_ti_refund'] = 1;

			$disable_info = M('eaterplanet_ecommerce_order_refund_disable')->where( array('order_id' => $order_id, 'order_goods_id' => $order_goods['order_goods_id']) )->find();

			if( !empty($disable_info) )
			{
				$order_goods['can_ti_refund'] = 0;
			}

			if($order_goods['is_refund_state'] == 1)
			{
				//已经再申请退款中了。或者已经退款了。
				$order_refund_info = M('eaterplanet_ecommerce_order_refund')->field('state')->where( array('order_id' => $order_id,'order_goods_id' => $order_goods['order_goods_id'] ) )->find();

				if( $order_refund_info['state'] == 3 )
				{
					$order_goods['is_refund_state'] = 2;
				}
			}

			//无售后期
			$open_aftersale = D('Home/Front')->get_config_by_name('open_aftersale');
			if( empty($open_aftersale) )
			{
				$order_goods['is_statements_state'] = 1;
			}
			//ims_

			$goods_info = M('eaterplanet_ecommerce_goods')->field('productprice as price')->where( array('id' => $order_goods['goods_id']) )->find();

			$goods_cm_info = M('eaterplanet_ecommerce_good_common')->field('pick_up_type,pick_up_modify,goods_share_image')->where( array('goods_id' => $order_goods['goods_id']) )->find();

			if($pick_up_type == -1 || $goods_cm_info['pick_up_type'] > $pick_up_type)
			{
				$pick_up_type = $goods_cm_info['pick_up_type'];

				if($pick_up_type == 0)
				{
					$pick_up_time = date('m-d', $today_time);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time)];
				}else if( $pick_up_type == 1 ){
					$pick_up_time = date('m-d', $today_time+86400);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
				}else if( $pick_up_type == 2 )
				{
					$pick_up_time = date('m-d', $today_time+86400*2);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
				}else if($pick_up_type == 3)
				{
					$pick_up_time = $goods_cm_info['pick_up_modify'];
				}
			}

			if( !empty($goods_cm_info['goods_share_image']) )
			{
				$order_goods['goods_share_image']=  tomedia( $goods_cm_info['goods_share_image'] );
			}else{
				$order_goods['goods_share_image'] = $order_goods['image'];
			}

			$order_goods['shop_price'] = $goods_info['price'];


			$store_info	= array('s_true_name' =>'','s_logo' => '');

			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');

			//$store_info['s_logo'] = D('Home/Front')->get_config_by_name('shoplogo');

			if( !empty($store_info['s_logo']) )
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				$store_info['s_logo'] = '';
			}


			$order_goods['store_info'] = $store_info;
			//核销订单
			if( $order_info['delivery'] == 'hexiao' ){
				$order_goods_saleshexiao = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('order_id' =>$order_id,'order_goods_id' => $order_goods['order_goods_id'] ) )->find();
				//有效期截止
				$order_goods['effect_end_time'] = date('Y-m-d H:i:s',$order_goods_saleshexiao['effect_end_time']);
				// 是否核销过期：0 否，1、是
				$order_goods['is_hexiao_expire'] = $order_goods_saleshexiao['effect_end_time']>time()?0:1;
				//总核销次数
				$order_goods['hexiao_count'] = $order_goods_saleshexiao['hexiao_count'];
				//剩余核销次数
				$order_goods['remain_hexiao_count'] = $order_goods_saleshexiao['remain_hexiao_count'];
				//核销方式：0 订单核销，1、自定义核销次数
				$order_goods['hexiao_type'] = $order_goods_saleshexiao['hexiao_type'];
				//商品核销卷码
				$order_goods['hexiao_volume_code'] = $order_goods_saleshexiao['hexiao_volume_code'];
				//商品核销二维码
				if(empty($order_goods['hexiao_qr_code'])){
					$order_goods['hexiao_qr_code'] = D('Home/Salesroom')->_get_ordergoods_hxqrcode($order_id,$order_goods['order_goods_id'],$order_goods['hexiao_volume_code']);
				}else{
					$order_goods['hexiao_qr_code'] = tomedia($order_goods['hexiao_qr_code']);
				}
				//是否核销完毕：0 否，1、是
				$order_goods['is_hexiao_over'] = $order_goods_saleshexiao['is_hexiao_over'];
				//核销过期操作时间
				$order_goods['expire_act_time'] = $order_goods_saleshexiao['expire_act_time'];

				$volume_code_list[$vi]['hexiao_volume_code'] = $order_goods['hexiao_volume_code'];

				if($order_goods['is_hexiao_over'] == 1){
					$receive_count = $receive_count + $order_goods_saleshexiao['goods_quantity'] - $order_goods_saleshexiao['refund_quantity'];
					$volume_code_list[$vi]['is_use'] = 1;
				}else{
					$receive_count = $receive_count + floor(($order_goods_saleshexiao['hexiao_count'] - $order_goods_saleshexiao['refund_quantity']*$order_goods_saleshexiao['one_hexiao_count'] - $order_goods_saleshexiao['remain_hexiao_count'])/$order_goods_saleshexiao['one_hexiao_count']);
					$volume_code_list[$vi]['is_use'] = 0;
				}
				// 是否核销过期：0 否，1、是
				$volume_code_list[$vi]['is_hexiao_expire'] = $order_goods_saleshexiao['effect_end_time']>time()?0:1;
				$volume_code_list[$vi]['effect_end_time'] = date('Y-m-d H:i:s',$order_goods_saleshexiao['effect_end_time']);
				$volume_code_list[$vi]['refund_quantity'] = $order_goods_saleshexiao['refund_quantity'];

				if($order_goods['hexiao_type'] == 0){
				    $hx_order_count = $hx_order_count + 1;
				}else{
				    $hx_time_count = $hx_time_count + 1;
				}
				//核销商品门店信息
				$salesroom_list = D('Home/Salesroom')->get_order_goods_salesroom($order_goods['order_goods_id'],$gpc['longitude'],$gpc['latitude'],0);
				$order_goods['salesroom_list'] = $salesroom_list;

				$vi++;
			}

			if( $order_goods['is_refund_state'] == 1 || ($order_goods['has_refund_money'] > 0 && $order_goods['has_refund_quantity'] > 0) )
			{
				$order_goods['is_refund_state'] = 1;
				$where = " order_id = '".$order_goods['order_id']."' and order_goods_id = '".$order_goods['order_goods_id']."' and state in (0,2,3) ";
				$refund_info = M('eaterplanet_ecommerce_order_refund')->field('ref_id,order_id,ref_money,real_refund_quantity,state')->where( $where )->find();
				if(!empty($refund_info)){
					$order_goods['refund_info'] = $refund_info;
				}else{
					$refund_info = array();
					$refund_info['order_id'] = $order_goods['order_id'];
					$refund_info['ref_money'] = $order_goods['has_refund_money'];
					$refund_info['real_refund_quantity'] = $order_goods['has_refund_quantity'];
					$refund_info['state'] = 3;
					$order_goods['refund_info'] = $refund_info;
				}
			}


			unset($order_goods['model']);
			unset($order_goods['rela_goodsoption_valueid']);
			unset($order_goods['comment']);


			//ims_   ims_eaterplanet_ecommerce_order_goods_refund addtime

			$order_goods_refund_list = M('eaterplanet_ecommerce_order_goods_refund')->field('real_refund_quantity,money,addtime')->where( "order_goods_id=".$order_goods['order_goods_id'] )->order('id asc')->select();

			if( empty($order_goods_refund_list) )
			{
				$order_goods_refund_list = array();
			}else{

				foreach( $order_goods_refund_list as $kre => $rval )
				{
					$rval['addtime'] = date('Y-m-d H:i:s', $rval['addtime']);
					$order_goods_refund_list[$kre] = $rval;
					$refund_goods_quantity = $refund_goods_quantity + $rval['real_refund_quantity'];
				}
			}

			$order_goods['order_goods_refund_list'] = $order_goods_refund_list;

			$goods_quantity = $goods_quantity + $order_goods['quantity'];
	        $order_goods_list[$key] = $order_goods;
			$shiji_total_money += $order_goods['quantity'] * $order_goods['price'];

			$member_youhui += ($order_goods['real_total'] - $order_goods['total']);
	    }
		//核销订单
		if( $order_info['delivery'] == 'hexiao' ){
			$hx_receive_info['goods_count'] = count($order_goods_list);//商品种类
			$hx_receive_info['goods_quantity'] = $goods_quantity;//商品总数量
			$hx_receive_info['receive_count'] = $receive_count;//已收货商品数量
			$hx_receive_info['wait_count'] = $goods_quantity-$receive_count-$refund_goods_quantity;//待收货商品数量
			$hx_receive_info['refund_goods_quantity'] = $refund_goods_quantity;//已退款商品数量
			$hx_receive_info['volume_code_list'] = $volume_code_list;//商品卷码列表
			$hx_record = D('Home/Salesroom')->get_last_ordergoods_hexiaorecord($order_id);
			if(!empty($hx_record)){
				//使用地址
				if(empty($hx_record['salesroom_name'])){
					$hx_receive_info['salesroom_name'] = $hx_record['smember_name'];
				}else{
					$hx_receive_info['salesroom_name'] = $hx_record['salesroom_name'];
				}
				//使用时间
				$hx_receive_info['use_time'] = date('Y-m-d H:i:s',$hx_record['addtime']);
			}
			if($hx_order_count > 0 && $hx_time_count > 0){//混合订单
			    $order_info['order_hexiao_type'] = 2;
			}else if($hx_order_count > 0 && $hx_time_count == 0){//按订单核销
			    $order_info['order_hexiao_type'] = 0;
			}else if($hx_order_count == 0 && $hx_time_count > 0){//按次数核销
			    $order_info['order_hexiao_type'] = 1;
			}
			$order_info['hx_receive_info'] = $hx_receive_info;//核销订单已收到待收货情况
		}

		unset($order_info['store_id']);
		unset($order_info['email']);
		unset($order_info['shipping_city_id']);
		unset($order_info['shipping_country_id']);
		unset($order_info['shipping_province_id']);
		//unset($order_info['comment']);
		unset($order_info['voucher_id']);
		unset($order_info['is_balance']);
		unset($order_info['lottery_win']);
		unset($order_info['ip']);
		unset($order_info['ip_region']);
		unset($order_info['user_agent']);

		$order_info['is_can_received'] = $is_can_received;
		$order_info['shipping_fare'] = round($order_info['shipping_fare'],2) < 0.01 ? '0.00':round($order_info['shipping_fare'],2) ;
		$order_info['voucher_credit'] = round($order_info['voucher_credit'],2) < 0.01 ? '0.00':round($order_info['voucher_credit'],2) ;
		$order_info['fullreduction_money'] = round($order_info['fullreduction_money'],2) < 0.01 ? '0.00':round($order_info['fullreduction_money'],2) ;

		$need_data = array();

		if($order_info['type'] == 'integral')
		{
			//暂时屏蔽积分商城
			$order_info['score'] = round($order_info['total'],2);
		}


		$order_info['total'] = round($order_info['total'] + $order_info['packing_fare'] +$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'] - $order_info['score_for_money'] + $order_info['localtown_add_shipping_fare'] - $order_info['fare_shipping_free'],2);

		if($order_info['total'] < 0)
		{
			$order_info['total'] = '0.00';
		}



		$order_info['real_total'] = round($shiji_total_money,2)+$order_info['shipping_fare'];
		$order_info['price'] = round($order_info['price'],2);
		$order_info['member_youhui'] = round($member_youhui,2) < 0.01 ? '0.00':round($member_youhui,2);
		$order_info['pick_up_time'] = $pick_up_time;



		$order_info['shipping_fare'] = sprintf("%.2f",$order_info['shipping_fare']);
		$order_info['voucher_credit'] = sprintf("%.2f",$order_info['voucher_credit']);
		$order_info['fullreduction_money'] = sprintf("%.2f",$order_info['fullreduction_money']);
		$order_info['total'] = sprintf("%.2f",$order_info['total']);
		$order_info['real_total'] = sprintf("%.2f",$order_info['real_total']);


		$order_info['date_added'] = date('Y-m-d H:i:s', $order_info['date_added']);


		if($order_info['delivery'] =='pickup')
		{


		}else{


		}


		if( !empty($order_info['pay_time']) && $order_info['pay_time'] >0 )
		{
			$order_info['pay_date'] = date('Y-m-d H:i:s', $order_info['pay_time']);
		}else{
			$order_info['pay_date'] = '';
		}

		$order_info['express_tuanz_date'] = date('Y-m-d H:i:s', $order_info['express_tuanz_time']);
		$order_info['receive_date'] = date('Y-m-d H:i:s', $order_info['receive_time']);


		//"delivery": "pickup", enum('express', 'pickup', 'tuanz_send')
		if($order_info['delivery'] == 'express')
		{
			$placeorder_trans_name = D('Home/Front')->get_config_by_name('placeorder_trans_name' );
			$order_info['delivery_name'] = $placeorder_trans_name?$placeorder_trans_name:'快递';
		}else if($order_info['delivery'] == 'pickup')
		{
			$delivery_ziti_name = D('Home/Front')->get_config_by_name('delivery_ziti_name' );
			$order_info['delivery_name'] = $delivery_ziti_name?$delivery_ziti_name:'自提';
		}else if($order_info['delivery'] == 'tuanz_send'){
			$delivery_tuanzshipping_name = D('Home/Front')->get_config_by_name('delivery_tuanzshipping_name' );
			$order_info['delivery_name'] = $delivery_tuanzshipping_name?$delivery_tuanzshipping_name:'团长配送';
		}
		$pin_rebate = [];
		//拼团获取返利信息
		if($order_info['type'] == 'pintuan'){
			$pin_rebate['reward_amount'] = 0;
			$pin_rebate['rebate_reward'] = 0;

			$pin_order = M('eaterplanet_ecommerce_pin_order')->where( array('order_id' => $order_id ) )->find();
			//获取拼团信息
			$pin_info = M('eaterplanet_ecommerce_pin')->where( array('pin_id' =>$pin_order['pin_id']  ) )->find();

			if($pin_info['is_pintuan_rebate'] == 1){
				if($pin_info['rebate_reward'] == 1){//积分
					$integral_flow = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' => $order_id,'type'=>'pintuan_rebate') )->find();
					if(!empty($integral_flow)){
						$pin_rebate['reward_amount'] = $integral_flow['score'];
						$pin_rebate['rebate_reward'] = 1;
					}
				}else if($pin_info['rebate_reward'] == 2){//余额
					$charge_flow = M('eaterplanet_ecommerce_member_charge_flow')->where( array('trans_id' => $order_id,'state'=>21) )->find();
					if(!empty($charge_flow)){
						$pin_rebate['reward_amount'] = $charge_flow['money'];
						$pin_rebate['rebate_reward'] = 2;
					}
				}
			}
		}

		//获取订单表单信息
		$order_form = D('Home/Allform')->getOrderFormInfo($order_id, $member_id);
		$need_data['order_form'] = $order_form;

		$need_data['order_info'] = $order_info;
		$need_data['order_status_info'] = $order_status_info;
		$need_data['shipping_province'] = $shipping_province;
		$need_data['shipping_city'] = $shipping_city;
		$need_data['shipping_country'] = $shipping_country;
		$need_data['order_goods_list'] = $order_goods_list;

		$need_data['goods_count'] = count($order_goods_list);
		$need_data['pin_rebate'] = $pin_rebate;

		//$order_info['order_status_id'] 13  平台介入退款
		$order_refund_historylist = array();
		$pingtai_deal = 0;

		//判断是否已经平台处理完毕

		$order_refund_historylist = M('eaterplanet_ecommerce_order_refund_history')->where( array('order_id' => $order_id) )->order('addtime asc')->select();

		foreach($order_refund_historylist as $key => $val)
		{
			if($val['type'] ==3)
			{
				$pingtai_deal = 1;
			}
		}

		$order_refund = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_id) )->find();

		if(!empty($order_refund))
		{
			$order_refund['addtime'] = date('Y-m-d H:i:s', $order_refund['addtime']);
		}

		$need_data['pick_up'] = $pick_up_info;



		if( empty($pick_order_info['qrcode']) && false)
		{

		}

		$need_data['pick_order_info'] = $pick_order_info;

		$order_pay_after_share = D('Home/Front')->get_config_by_name('order_pay_after_share');


		if($order_pay_after_share==1){
			$order_pay_after_share_title = D('Home/Front')->get_config_by_name('order_pay_after_share_title');
			$order_pay_after_share_img = D('Home/Front')->get_config_by_name('order_pay_after_share_img');
			$order_pay_after_share_img = !empty($order_pay_after_share_img) ? tomedia($order_pay_after_share_img) : '';
			$need_data['share_img'] = empty($order_pay_after_share_img) ? $need_data['order_goods_list'][0]['image']: $order_pay_after_share_img;
			$need_data['share_title'] = $order_pay_after_share_title;
		}else{
			if(empty($need_data['order_goods_list'][0]['goods_share_image']))
			{
				$need_data['share_img'] = $need_data['order_goods_list'][0]['image'];
			}
		}


		$order_can_del_cancle = D('Home/Front')->get_config_by_name('order_can_del_cancle');

		$order_can_del_cancle = empty($order_can_del_cancle) || $order_can_del_cancle == 0 ? 1 : 0;

		$is_hidden_orderlist_phone = D('Home/Front')->get_config_by_name('is_hidden_orderlist_phone');

		$is_show_guess_like = D('Home/Front')->get_config_by_name('is_show_order_guess_like');

		$user_service_switch = D('Home/Front')->get_config_by_name('user_service_switch');

		$common_header_backgroundimage = D('Home/Front')->get_config_by_name('common_header_backgroundimage');
		if($common_header_backgroundimage){
			$common_header_backgroundimage = tomedia($common_header_backgroundimage);
		}

		$order_can_shen_refund = D('Home/Front')->get_config_by_name('is_score_can_refund');

		if( $order_info['type'] == 'integral' )
		{
			if(  !isset($order_can_shen_refund) || empty($order_can_shen_refund) )
			{
				$order_can_shen_refund = 0;
			}else{
				$order_can_shen_refund = 1;
			}
		}else{
			$order_can_shen_refund = 1;
		}

		$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');
		$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');

		if( !isset($order_note_open) || $order_note_open == 0)
		{
			$order_note_open = 0;
		}

		if( !isset($order_note_name) || empty($order_note_name) )
		{
			$order_note_name = '店名';
		}

		$open_comment_gift = D('Home/Front')->get_config_by_name('open_comment_gift');
		$open_comment_gift = !empty($open_comment_gift) ? $open_comment_gift : 0;

		$result = D('Seller/Order')->check_comment_gift_score($member_id);
		if(!$result['is_comment_gift']){
			$open_comment_gift = 0;
		}
		//预售信息数组 begin
		$presale_info = [];
		$presale_result = D('Home/PresaleGoods')->getOrderPresaleInfo( $order_id );
		if( $presale_result['code'] == 0 )
        {
            $presale_info = $presale_result['data'];
        }
		//end

        //礼品卡订单信息 begin
        $virtualcard_result = D('Seller/VirtualCard')->getVirtualCardOrderInfO( $order_id );
		$virtualcard_info = [];
		if( $virtualcard_result['code'] == 0 && $virtualcard_result['data']['state'] > 0 )
        {
            $virtualcard_info = $virtualcard_result['data'];
        }

        //end



		echo json_encode(
			array(
				'code' => 0,
				'data' => $need_data,
				'order_note_open' => $order_note_open,
				'order_note_name' => $order_note_name,
				'pingtai_deal' => $pingtai_deal,
				'order_refund' => $order_refund,
				'order_can_shen_refund' => $order_can_shen_refund,
				'order_can_del_cancle' => $order_can_del_cancle,
				'order_pay_after_share' => $order_pay_after_share,
				'is_hidden_orderlist_phone' => $is_hidden_orderlist_phone,
				'is_show_guess_like' => $is_show_guess_like,
				'user_service_switch' => $user_service_switch,
				'open_comment_gift' => $open_comment_gift,
				'common_header_backgroundimage' => $common_header_backgroundimage,
                'presale_info' => $presale_info,//预售信息
                'virtualcard_info' => $virtualcard_info,//礼品卡信息
			)
		);

	}

	public function sign_dan_order()
	{
		$gpc = I('request.');

		$token = $gpc['token'];
		$order_id = $gpc['order_id'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];


		$community_info = D('Home/Front')->get_member_community_info($member_id);

		$order_info = M('eaterplanet_ecommerce_order')->where( array('head_id' => $community_info['id'],'order_id' => $order_id) )->find();

		if(!empty($order_info) && $order_info['order_status_id'] == 14)
		{
			$oh = array();
			$oh['order_id']=$order_id;
			$oh['order_status_id']= 4;

			$oh['comment']='团长签收货物';
			$oh['date_added']=time();
			$oh['notify']= $order_info['order_status_id'];

			M('eaterplanet_ecommerce_order_history')->add( $oh );

			//更改订单为已发货
			D('Home/Frontorder')->send_order_operate($order_id);
			echo json_encode( array('code' => 0) );
		}else{
			echo json_encode( array('code' => 1) );
		}


	}

	public function order_commission()
	{
		$gpc = I('request.');

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];

		if( empty($member_id) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);
		$head_id = $community_info['id'];

		$choose_date = $gpc['chooseDate'];

		$choose_date = str_replace('年','-', $choose_date);
		$choose_date = str_replace('月','-', $choose_date);
		$choose_date = $choose_date.'01 00:00:00';

		$BeginDate=date('Y-m-d', strtotime($choose_date));

		$end_date = date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59';

		$begin_time = strtotime($BeginDate.' 00:00:00');
		$end_time = strtotime($end_date);

		$where = " and addtime >= {$begin_time} and addtime < {$end_time} ";

		$money = M('eaterplanet_community_head_commiss_order')->where("head_id={$head_id} and state=0 {$where}")->sum('money');
		if( empty($money))
		{
			$money = 0;
		}
		echo json_encode( array('code' => 0, 'money' => $money) );
		die();



	}

	public function refundorderlist()
	{
		$gpc = I('request.');

		$is_tuanz  = isset($gpc['is_tuanz']) ? $gpc['is_tuanz'] :0;
		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];



		$page = isset($gpc['page']) ? $gpc['page']:'1';

		$size = isset($gpc['size']) ? $gpc['size']:'6';
		$offset = ($page - 1)* $size;

		$type =  isset($gpc['type']) ? $gpc['type']:'';


		$where = ' and o.member_id = '.$member_id;

		$fields = " orf.state as refund_state ,orf.order_goods_id as r_order_goods_id, ";

		$currentTab = isset($gpc['currentTab']) ? $gpc['currentTab']:0;


		if($currentTab == 0)
		{

		}else if($currentTab == 1){
			//售后
			$where .= ' and o.order_status_id = 12 ';
		}else if($currentTab == 2){
			$where .= ' and  orf.state =3 ';
		}else if($currentTab == 3)
		{
			$where .= ' and orf.state =1 ';
		}

		$sql = "select orf.ref_id,orf.state,orf.order_goods_id,o.order_id,o.order_num_alias,o.date_added,o.delivery,o.is_pin,{$fields} o.is_zhuli,o.shipping_fare,o.shipping_tel,o.shipping_name,o.voucher_credit,o.fullreduction_money,o.store_id,o.total,o.order_status_id,o.lottery_win,o.type from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund as orf left join  " .C('DB_PREFIX')."eaterplanet_ecommerce_order as o on orf.order_id = o.order_id
						where  1  {$where}
	                    order by orf.addtime desc limit {$offset},{$size}";

	    $list =  M()->query($sql);

	   $eaterplanet_ecommerce_order_status_list =  M('eaterplanet_ecommerce_order_status')->select();

	   $url = D('Home/Front')->get_config_by_name('shop_domain');

	   $status_arr = array();

	   foreach( $eaterplanet_ecommerce_order_status_list as $kk => $val )
	   {
		   $status_arr[ $val['order_status_id'] ] = $val['name'];
	   }

	    //createTime
	    foreach($list as $key => $val)
	    {

	        $val['createTime'] = date('Y-m-d H:i:s', $val['date_added']);

			switch( $val['state'] )
			{
				case 0:
					$val['status_name'] = '申请中';
				break;
				case 1:
					$val['status_name'] = '商家拒绝';
				break;
				case 2:

					break;
				case 3:
					$val['status_name'] = '退款成功';
				break;
				case 4:
					$val['status_name'] = '退款失败';
				break;
				case 5:
					$val['status_name'] = '撤销申请';
					break;
			}


			if($val['shipping_fare']<=0.001 || $val['delivery'] == 'pickup')
			{
				$val['shipping_fare'] = '免运费';
			}else{
				$val['shipping_fare'] = ''.$val['shipping_fare'];
			}


			if($val['order_status_id'] == 10)
			{
				$val['status_name'] = '等待退款';
			}
			else if($val['order_status_id'] == 4 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '待自提';
				//已自提
			}
			else if($val['order_status_id'] == 6 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '已自提';
				//已自提
			}
			else if($val['order_status_id'] == 1 && $val['type'] == 'lottery')
			{
				//等待开奖
				//一等奖
				if($val['lottery_win'] == 1)
				{
					$val['status_name'] = '一等奖';
				}else {
					$val['status_name'] = '等待开奖';
				}
			}
			else if($val['order_status_id'] == 2 && $val['type'] == 'lottery')
			{
				//等待开奖
				$val['status_name'] = '等待开奖';
			}

	        $quantity = 0;

			if( $val['order_goods_id'] > 0 )
			{
				$goods_sql = "select order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid
					from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods where  order_goods_id=".$val['order_goods_id']." and  order_id= ".$val['order_id']."";

			}else{
				$goods_sql = "select order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid
					from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods where   order_id= ".$val['order_id']."";

			}


	        $goods_list = M()->query($goods_sql); //M()->query($goods_sql);
			$total_commision = 0;
			if($val['delivery'] =='tuanz_send')
			{
				$total_commision += $val['shipping_fare'];
			}

	        foreach($goods_list as $kk => $vv)
	        {
	            //commision

				$order_option_list =  M('eaterplanet_ecommerce_order_option')->where( array('order_goods_id' =>$vv['order_goods_id'] ) )->select();


				if( !empty($vv['goods_images']))
				{

					$goods_images = $url. '/'.resize($vv['goods_images'],400,400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= $url.'/'.resize($vv['goods_images'],400,400) ;
					}

				}else{
					 $vv['goods_images']= '';
				}


				$goods_filed = M('eaterplanet_ecommerce_goods')->field('productprice as price')->where( array('id' => $vv['goods_id'] ) )->find();

				$vv['orign_price'] = $goods_filed['price'];
	            $quantity += $vv['quantity'];
	            foreach($order_option_list as $option)
	            {
	                $vv['option_str'][] = $option['value'];
	            }
				if( !isset($vv['option_str']) )
				{
					$vv['option_str'] = '';
				}else{
					$vv['option_str'] = implode(',', $vv['option_str']);
				}
	            // $vv['price'] = round($vv['price'],2);
	            $vv['price'] = sprintf("%.2f",$vv['price']);
	            $vv['orign_price'] = sprintf("%.2f",$vv['orign_price']);
	            $vv['total'] = sprintf("%.2f",$vv['total']);


	            $goods_list[$kk] = $vv;
	        }
			$val['total_commision'] = $total_commision;
	        $val['quantity'] = $quantity;
	        if( empty($val['store_id']) )
			{
				$val['store_id'] = 1;
			}


			$store_info	= array('s_true_name' =>'','s_logo' => '');

			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');

			$store_info['s_logo'] = D('Home/Front')->get_config_by_name('shoplogo');



			if( !empty($store_info['s_logo']))
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{

				$store_info['s_logo'] = '';
			}


			$order_goods['store_info'] = $store_info;

			$val['store_info'] = $store_info;

	        $val['goods_list'] = $goods_list;

			$val['total'] = $val['total'] + $val['shipping_fare']-$val['voucher_credit']-$val['fullreduction_money'];
			if($val['total'] < 0)
			{
				$val['total'] = 0;
			}

			$val['total'] = sprintf("%.2f",$val['total']);
	        $list[$key] = $val;
	    }

		$need_data = array('code' => 0);

		if( !empty($list) )
		{
			$need_data['data'] = $list;

		}else {
			$need_data = array('code' => 1);
		}

		echo json_encode( $need_data );
		die();

	}



	public function orderlist()
	{
		$gpc = I('request.');
		$_GPC = $gpc;

		$is_tuanz  = isset($gpc['is_tuanz']) ? $gpc['is_tuanz'] :0;
		$is_supply = isset($gpc['is_supply']) ? $gpc['is_supply'] :0;

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$sqlcondition = "";
		$left_join = "";

		$page = isset($gpc['page']) ? $gpc['page']:'1';

		$size = isset($gpc['size']) ? $gpc['size']:'6';
		$offset = ($page - 1)* $size;

		$type =  isset($gpc['type']) ? $gpc['type']:'';

		$order_status = isset($gpc['order_status']) ? $gpc['order_status']:'-1';


	    $soli_id = isset($_GPC['soli_id']) ? $_GPC['soli_id'] : 0;//是否群接龙

		if($is_tuanz == 1)
		{
			$community_info = D('Home/Front')->get_member_community_info($member_id);

			if( isset($_GPC['chooseDate']) && !empty($_GPC['chooseDate']) )
			{
				$where = ' and o.head_id = '.$community_info['id'] ;
			}else{
				//$where = ' and o.head_id = '.$community_info['id'].' and o.delivery != "express"  ';
				$where = ' and o.head_id = '.$community_info['id'].'  ';
			}

			$searchfield = isset($_GPC['searchfield']) && !empty($_GPC['searchfield']) ? $_GPC['searchfield'] : '';

			if( !empty($searchfield) && !empty($_GPC['keyword']))
			{
				$keyword = $_GPC['keyword'];

				switch($searchfield)
				{
					case 'ordersn':
						$where .= ' AND locate("'.$keyword.'",o.order_num_alias)>0';
					break;
					case 'member':
						$where .= ' AND (locate("'.$keyword.'",m.username)>0 or "'.$keyword.'"=o.member_id )';
						$left_join .= ' left join ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member as  m on m.member_id = o.member_id ';
					break;
					case 'address':
						$where .= ' AND ( locate("'.$keyword.'",o.shipping_name)>0 )';

					break;
					case 'mobile':
						$where .= ' AND ( locate("'.$keyword.'",o.shipping_tel)>0 )';

					break;
					case 'location':
						$where .= ' AND (locate("'.$keyword.'",o.shipping_address)>0 )';
					break;
					case 'shipping_no':
						$where .= ' AND (locate("'.$keyword.'",o.shipping_no)>0 )';
					break;
					case 'goodstitle':
						$left_join = ' inner join ( select DISTINCT(og.order_id) from ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_order_goods as og  where  (locate("'.$keyword.'",og.name)>0)) gs on gs.order_id=o.order_id';

					break;
					case 'trans_id':
						$where .= ' AND (locate("'.$keyword.'",o.transaction_id)>0 )';
					break;

				}
			}

		}
		else if( $is_supply == 1 )
		{
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');

			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}

			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();

			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$keyword = $_GPC['keyword'];

				if( !empty($keyword) )
				{
					$left_join = ' inner join ( select DISTINCT(og.order_id) from ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_order_goods as og  where  (locate("'.$keyword.'",og.name)>0) and og.supply_id = '.$supply_info['id'].' ) gs on gs.order_id=o.order_id';
				}else{
					$left_join = ' inner join ( select DISTINCT(og.order_id) from ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_order_goods as og  where   og.supply_id = '.$supply_info['id'].' ) gs on gs.order_id=o.order_id';
				}



				//$where = " and o.delivery != 'hexiao'";

			}else{

				echo json_encode( array('code' => 2, 'msg' => '未开启此项功能') );
				die();
			}
		}
		else{
			$where = ' and o.member_id = '.$member_id;
		}

	    if( isset($gpc['chooseDate']) && !empty($gpc['chooseDate']) )
		{
			$choose_date = $gpc['chooseDate'];

			$choose_date = str_replace('年','-', $choose_date);
			$choose_date = str_replace('月','-', $choose_date);
			$choose_date = $choose_date.'01 00:00:00';

			$BeginDate=date('Y-m-d', strtotime($choose_date));

			$end_date = date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59';

			$begin_time = strtotime($BeginDate.' 00:00:00');
			$end_time = strtotime($end_date);


			$where .= ' and o.date_added >= '.$begin_time.' and o.date_added < '.$end_time;
		}

		//全部 -1  待付款 3 待配送1 待提货4 已提货6
		//order_status $order_status
		$join = "";
		$fields = "";

		switch($order_status)
		{
			case -1:
			//全部 -1

			break;
			case 3:
			//待付款 3
				$where .= ' and o.order_status_id = 3 ';
			break;
			case 1:
			//待配送1
			$where .= ' and o.order_status_id = 1 ';

			break;
			case 4:
			//待提货4
			$where .= ' and o.order_status_id = 4 ';
			break;
			case 14:
			//待提货4
			$where .= ' and o.order_status_id = 14 ';
			break;

			case 22:
			//待确认佣金的
				$where .= ' and o.order_status_id in (1,4,14) ';
			break;
			case 357:
			//待确认佣金的
				$where .= ' and o.order_status_id in (3,5,7) ';
			break;
			case 6:
			//已提货6
				$where .= ' and o.order_status_id in (6,11) ';
			break;
			case 11:
			//已完成
			$where .= ' and o.order_status_id = 11 ';
			break;
			case 12:
			$fields = " orf.state as refund_state , ";

			$currentTab = isset($gpc['currentTab']) ? $gpc['currentTab']:0;

			$join = " ".C('DB_PREFIX').'eaterplanet_ecommerce_order_refund as orf,  ';
			$where .= ' and o.order_id = orf.order_id ';
			if($currentTab == 0)
			{

			}else if($currentTab == 1){
				//售后
				$where .= ' and o.order_status_id = 12 ';
			}else if($currentTab == 2){
				$where .= ' and  orf.state =3 ';
			}else if($currentTab == 3)
			{
				$where .= ' and orf.state =1 ';
			}

			break;
			case 7:
			//已退款
			$where .= ' and o.order_status_id = 7 ';
			break;
		}

		$where .= ' and o.type != "ignore" ';

		if( isset($soli_id) && $soli_id > 0 )
		{
			$where .= " and o.soli_id = {$soli_id} ";
		}


		if( !empty($type) )
		{
			//$where .= ' and o.type != "ignore" ';
		}

	    $sql = "select o.order_id,o.order_num_alias,o.date_added,o.delivery,o.is_pin,{$fields} o.is_zhuli,o.packing_fare,o.shipping_fare,o.shipping_tel,o.shipping_name,o.voucher_credit,o.score_for_money,o.fullreduction_money,o.store_id,o.total,o.order_status_id,o.lottery_win,o.type,os.name as status_name,o.ziti_mobile,o.localtown_add_shipping_fare,o.fare_shipping_free,o.third_distribution_type,o.payment_code "
			 . " from ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o {$left_join}, {$join}
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_status as os ".$sqlcondition."
	                    where   o.order_status_id = os.order_status_id {$where}
	                    order by o.date_added desc limit {$offset},{$size}";

	    $list =  M()->query($sql);

	    $open_auto_delete = D('Home/Front')->get_config_by_name('open_auto_delete');

	    $cancle_hour = D('Home/Front')->get_config_by_name('auto_cancle_order_time');
		$cancle_hour_time = time() - 3600 * $cancle_hour;


	   $url = D('Home/Front')->get_config_by_name('shop_domain');
	    //createTime
	    foreach($list as $key => $val)
	    {

			//判断是否需要取消订单
			//order_status_id 3 open_auto_delete

			if($open_auto_delete == 1 && $val['order_status_id'] == 3 &&  $val['date_added'] < $cancle_hour_time )
			{
				D('Home/Frontorder')->cancel_order($val['order_id'],  true);

				$val['order_status_id'] == 5;
			}

			$presale_result = D('Home/PresaleGoods')->getOrderPresaleInfo( $val['order_id'] );
			if( $presale_result['code'] == 0 )
            {
                $val['is_presale'] = 1;
            }else{
                $val['is_presale'] = 0;
            }

			if($val['delivery'] == 'pickup')
			{
				//$val['total'] = round($val['total'],2) - round($val['voucher_credit'],2);
			}else{
				//$val['total'] = round($val['total'],2)+round($val['shipping_fare'],2) - round($val['voucher_credit'],2);
			}
	        $val['createTime'] = date('Y-m-d H:i:s', $val['date_added']);

			// $val['delivery'] =='pickup'

			if($val['shipping_fare']<=0.001 || $val['delivery'] == 'pickup')
			{
				$val['shipping_fare'] = 0.00;
			}else{
				$val['shipping_fare'] = ''.$val['shipping_fare'];
			}


			if($val['order_status_id'] == 10)
			{
				$val['status_name'] = '等待退款';
			}
			else if($val['order_status_id'] == 4 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '待自提';
				//已自提
			}
			else if($val['order_status_id'] == 6 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '已自提';
				//已自提
			}
			else if($val['order_status_id'] == 1 && $val['type'] == 'lottery')
			{
				//等待开奖
				//一等奖
				if($val['lottery_win'] == 1)
				{
					$val['status_name'] = '一等奖';
				}else {
					$val['status_name'] = '等待开奖';
				}
			}
			else if($val['order_status_id'] == 2 && $val['type'] == 'lottery')
			{
				//等待开奖
				$val['status_name'] = '等待开奖';
			}

	        $quantity = 0;


			$goods_list = M('eaterplanet_ecommerce_order_goods')->field('order_id,order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,old_total,fullreduction_money,voucher_credit,rela_goodsoption_valueid,is_refund_state,has_refund_money,has_refund_quantity')->where( array('order_id' => $val['order_id']) )->select();

			$total_commision = 0;
			if($val['delivery'] =='tuanz_send')
			{
				$total_commision += $val['shipping_fare'];
			}


	        foreach($goods_list as $kk => $vv)
	        {
	            //commision

				if($is_tuanz == 1){


					$community_order_info = M('eaterplanet_community_head_commiss_order')->where( array('head_id' => $community_info['id'],'order_goods_id' => $vv['order_goods_id']) )->find();


					if(!empty($community_order_info))
					{
						$vv['commision'] = $community_order_info['money']-$community_order_info['add_shipping_fare'];
						$vv['commision'] = sprintf("%.2f",$vv['commision']);
						if($community_order_info['state'] == 2){
							$vv['commision'] = 0;
						}
						$total_commision += $vv['commision'];
					}else{
						$vv['commision'] = "0.00";
					}

					$vv['head_shipping_fare'] = $community_order_info['add_shipping_fare'];//团长配送费


					$vv['old_commision'] = $vv['commision'];
					$vv['del_commision'] = 0;

					//fen_type
					$vv['fen_type'] = $community_order_info['fen_type'];// 0 按照比例， 1按照金额
					$vv['fen_bili'] = $community_order_info['bili'];//比例
					$vv['fen_gumoney'] = $community_order_info['bili'];//按照金额

					$order_jishu = $vv['total'] -$vv['fullreduction_money']-$vv['voucher_credit']-$vv['has_refund_money'];

					$vv['order_jishu'] = $order_jishu;//有效金额：（基数）

					//has_refund_commission = 0
					$order_goods_refund_list = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $val['order_id'],'order_goods_id' => $vv['order_goods_id'] ) )->select();


					if( !empty($order_goods_refund_list) )
					{
						$kvbal_total_back_head_orderbuycommiss = 0;//合计退掉佣金

						foreach( $order_goods_refund_list as $kvval )
						{
							$kvbal_total_back_head_orderbuycommiss += $kvval['back_head_orderbuycommiss'];
						}
						$vv['del_commision'] = $kvbal_total_back_head_orderbuycommiss;

						$vv['old_commision'] += $vv['del_commision'];
					}


				}

				$order_option_list = M('eaterplanet_ecommerce_order_option')->where( array('order_goods_id' => $vv['order_goods_id']) )->select();

				if( !empty($vv['goods_images']))
				{

					$goods_images = $url. '/'.resize($vv['goods_images'],400,400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= $url.'/'.resize($vv['goods_images'],400,400) ;
					}

				}else{
					 $vv['goods_images']= '';
				}


				$goods_filed = M('eaterplanet_ecommerce_goods')->field('productprice as price')->where( array('id' => $vv['goods_id']) )->find();

				$vv['orign_price'] = $goods_filed['price'];
	            $quantity += $vv['quantity'];
	            foreach($order_option_list as $option)
	            {
	                $vv['option_str'][] = $option['value'];
	            }
				if( !isset($vv['option_str']) )
				{
					$vv['option_str'] = '';
				}else{
					$vv['option_str'] = implode(',', $vv['option_str']);
				}

				 // $vv['price'] = round($vv['price'],2);
	            $vv['price'] = sprintf("%.2f",$vv['price']);
	            $vv['orign_price'] = sprintf("%.2f",$vv['orign_price']);
	            //$vv['total'] = sprintf("%.2f",$vv['total']);
                $vv['total'] = sprintf("%.2f",$vv['old_total']);

				if( $vv['is_refund_state'] == 1 || ($vv['has_refund_money'] > 0 && $vv['has_refund_quantity'] > 0) )
				{
					$vv['is_refund_state'] = 1;
					$where = " order_id = '".$vv['order_id']."' and order_goods_id = '".$vv['order_goods_id']."' and state in (0,2,3) ";
					$refund_info = M('eaterplanet_ecommerce_order_refund')->field('ref_id,order_id,ref_money,real_refund_quantity,state')->where( $where )->find();
					if(!empty($refund_info)){
						$vv['refund_info'] = $refund_info;
					}else{
						$refund_info = array();
						$refund_info['order_id'] = $val['order_id'];
						$refund_info['ref_money'] = $vv['has_refund_money'];
						$refund_info['real_refund_quantity'] = $vv['has_refund_quantity'];
						$refund_info['state'] = 3;
						$vv['refund_info'] = $refund_info;
					}
				}
	            $goods_list[$kk] = $vv;
	        }

			$val['total_commision'] = $total_commision;
	        $val['quantity'] = $quantity;
	        if( empty($val['store_id']) )
			{
				$val['store_id'] = 1;
			}


			$store_info	= array('s_true_name' =>'','s_logo' => '');

			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');

			$store_info['s_logo'] = D('Home/Front')->get_config_by_name('shoplogo');



			if( !empty($store_info['s_logo']))
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{

				$store_info['s_logo'] = '';
			}


			$order_goods['store_info'] = $store_info;




			$val['store_info'] = $store_info;


	        $val['goods_list'] = $goods_list;

			if($val['type'] == 'integral')
			{
				//暂时屏蔽积分
				$val['score'] =  round($val['total'],2);
			}

			$val['total'] = $val['total'] + $val['packing_fare'] + $val['shipping_fare']-$val['voucher_credit']-$val['fullreduction_money']-$val['score_for_money']+$val['localtown_add_shipping_fare']-$val['fare_shipping_free'];
			if($val['total'] < 0)
			{
				$val['total'] = 0;
			}

			$val['total'] = sprintf("%.2f",$val['total']);

			if( $val['delivery'] == 'localtown_delivery' && ($val['order_status_id'] != 3 && $val['order_status_id'] != 5 ) )
			{
				$val['orderdistribution_order'] = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $val['order_id'] ) )->find();
				if( !empty($val['orderdistribution_order']) && $val['orderdistribution_order'] > 0 )
				{
					$orderdistribution = M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => $val['orderdistribution_order']['orderdistribution_id']  ) )->find();
					$val['orderdistribution_order']['username'] = $orderdistribution['username'];
				}
			}
	        $list[$key] = $val;
	    }

		$need_data = array('code' => 0);

		if( !empty($list) )
		{
			$need_data['data'] = $list;

		}else {
			$need_data = array('code' => 1);
		}


		$open_aftersale = D('Home/Front')->get_config_by_name('open_aftersale');

		if( empty($open_aftersale) )
		{
			$open_aftersale = 0;
		}

		$open_aftersale_time = D('Home/Front')->get_config_by_name('open_aftersale_time');
		if( empty($open_aftersale_time) )
		{
			$open_aftersale_time = 0;
		}

		$need_data['open_aftersale'] = $open_aftersale;
		$need_data['open_aftersale_time'] = $open_aftersale_time;


		echo json_encode( $need_data );
		die();

	}

	function receive_order_list()
	{
		$gpc = I('request.');



		$order_data = $gpc['order_data'];
		$token = $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);

		$is_member_hexiao = false;
		if( empty($community_info) && $member_info['pickup_id'] > 0  )
		{
			$parent_community_info = M('eaterplanet_ecommerce_community_pickup_member')->where( array('member_id' =>$member_id ) )->find();


			if(!empty($parent_community_info))
			{
				$is_member_hexiao = true;
				$community_info = M('eaterplanet_community_head')->where( array('id' => $parent_community_info['community_id'] ) )->find();
			}
		}


		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		if( is_array($order_data) )
		{
			$order_data_str = implode(',', $order_data);
		}else{
			$order_data_str = $order_data;
		}


		$where = ' and o.head_id = '.$community_info['id'];

		$where .= ' and o.order_status_id = 4 and order_id in ('.$order_data_str.') ';




		$sql = "select o.order_id,o.order_num_alias
				from ".C('DB_PREFIX')."eaterplanet_ecommerce_order  as o ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_status  as os
	                    where   o.order_status_id = os.order_status_id {$where}
	                    order by o.date_added desc ";

	    $list =  M()->query($sql);

		if( !empty($list) )
		{
			foreach($list as $val)
			{
				D('Home/Frontorder')->receive_order($val['order_id']);
				if($is_member_hexiao)
				{
					$pickup_member_record_data = array();
					$pickup_member_record_data['order_id'] = $val['order_id'];
					$pickup_member_record_data['order_sn'] = $val['order_num_alias'];
					$pickup_member_record_data['community_id'] = $community_info['id'];
					$pickup_member_record_data['member_id'] = $member_id;
					$pickup_member_record_data['addtime'] = time();

					M('eaterplanet_ecommerce_community_pickup_member_record')->add( $pickup_member_record_data );
				}

			}
			echo json_encode( array('code' => 0) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

		//load_model_class('frontorder')->receive_order($order_id);

		//string(15) "35,34,31,19,5,2"
		//string(32) "b55feabc517fa686f79c1bbd303cdeda"


	}

	function receive_order()
	{
		$gpc = I('request.');

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$order_id = $gpc['order_id'];

		if( empty($member_id) )
		{

			$result['code'] = 2;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}

		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id,'member_id' => $member_id) )->find();


	    if(empty($order_info)){

			$result['code'] = 1;

	        $result['msg'] = '非法操作,客户不存在该订单';

	        echo json_encode($result);

	        die();
	    }

		//检查订单是否能确认收货（存在售后未完成无法确认收货）
		$result = D('Seller/Order')->check_order_receive($order_id);
		if($result['status'] == 0){
			$result['code'] = 1;
			$result['msg'] = '您的订单处于售后处理中，请完成后再确认收货！';
			echo json_encode($result);
			die();
		}
		if($order_info['delivery'] == 'localtown_delivery'){
			$result = D('Seller/Order')->check_localtown_order_receive($order_id);
			if($result['status'] == 0){
				$result['code'] = 1;
				$result['msg'] = '该订单未有配送员接单或还未指定配送员，无法确认收货！';
				echo json_encode($result);
				die();
			}
			M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id) )->save( array('state' => 4) );
			D('Home/LocaltownDelivery')->write_distribution_log( $order_id, 0 , 4 , "用户确认收货" );
		}
		D('Home/Frontorder')->receive_order($order_id);

	    $result['code'] = 0;

	    echo json_encode($result);

	    die();

	}

	/**

		取消订单操作

	**/

	public function cancel_order()
	{

		$gpc = I('request.');

	    $token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$order_id = $gpc['order_id'];

		if( empty($member_id) )

		{

			$result['code'] = 2;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}


		$order_info = M('eaterplanet_ecommerce_order')->where( array('member_id' => $member_id,'order_id' => $order_id) )->find();


	    if(empty($order_info)){

			$result['code'] = 1;

	        $result['msg'] = '非法操作,客户不存在该订单';

	        echo json_encode($result);

	        die();

	    }

		//order_status_id == 3
		if($order_info['order_status_id'] != 3)
		{
			$result['code'] = 1;

	        $result['msg'] = '订单可能已取消,请刷新页面';

	        echo json_encode($result);

	        die();
		}

		D('Home/Frontorder')->cancel_order($order_id);

	    $result['code'] = 0;

	    echo json_encode($result);

	    die();



	}

	public function order_head_info()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];

		$is_share = $_GPC['is_share'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];


		$order_id = $_GPC['id'];

		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();

        if($order_info['is_change_price'] == 1){
            $order_info['admin_change_price'] = $order_info['total'] - $order_info['old_price'];
        }

		$pick_up_info = array();
		$pick_order_info = array();


		$community_info = D('Home/Front')->get_member_community_info($member_id);


		if($is_share){

			$userInfo = M('eaterplanet_ecommerce_member')->field('avatar')->where( array('member_id' => $order_info['member_id'] ) )->find();

			$order_info['avatar'] = $userInfo['avatar'];
		}

		if( $order_info['delivery'] == 'pickup' )
		{
			//查询自提点
			//$pick_order_info = M('eaterplanet_ecommerce_pick_order')->where( array('order_id' => $order_id) )->find();

			//$pick_id = $pick_order_info['pick_id'];

			//$pick_up_info = M('eaterplanet_ecommerce_pick_up')->where( array('id' => $pick_id ) )->find();

		}

		$order_status_info = M('eaterplanet_ecommerce_order_status')->where( array('order_status_id' => $order_info['order_status_id']) )->find();

	    //10 name
		if($order_info['order_status_id'] == 10)
		{
			$order_status_info['name'] = '等待退款';
		}
		else if($order_info['order_status_id'] == 4 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '待自提';
			//已自提
		}
		else if($order_info['order_status_id'] == 6 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '已自提';

		}
		else if($order_info['order_status_id'] == 1 && $order_info['type'] == 'lottery')
		{
			//等待开奖
			//一等奖
			if($order_info['lottery_win'] == 1)
			{
				$order_status_info['name'] = '一等奖';
			}else {
				$order_status_info['name'] = '等待开奖';
			}
		}

		//$order_info['type']
		//open_auto_delete

		if($order_info['order_status_id'] == 3)
		{
			$open_auto_delete = D('Home/Front')->get_config_by_name('open_auto_delete');

			$auto_cancle_order_time = D('Home/Front')->get_config_by_name('auto_cancle_order_time');

			$order_info['open_auto_delete'] = $open_auto_delete;
			//date_added
			if($open_auto_delete == 1)
			{
				$order_info['over_buy_time'] = $order_info['date_added'] + 3600 * $auto_cancle_order_time;
				$order_info['cur_time'] = time();
			}

		}



		$shipping_province = M('eaterplanet_ecommerce_area')->where( array('id' => $order_info['shipping_province_id'] ) )->find();

		$shipping_city = M('eaterplanet_ecommerce_area')->where( array('id' => $order_info['shipping_city_id'] ) )->find();

		$shipping_country = M('eaterplanet_ecommerce_area')->where( array('id' => $order_info['shipping_country_id']) )->find();

		$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->select();

		$shiji_total_money = 0;
		$member_youhui = 0.00;


		$pick_up_time = "";
		$pick_up_type = -1;
		$pick_up_weekday = '';
		$today_time = $order_info['pay_time'];
		$arr = array('天','一','二','三','四','五','六');

	    foreach($order_goods_list as $key => $order_goods)
	    {
			$order_goods['name'] = htmlspecialchars_decode($order_goods['name']);
			$order_option_info = M('eaterplanet_ecommerce_order_option')->field('value')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' => $order_id) )->select();


	        foreach($order_option_info as $option)
	        {
	            $order_goods['option_str'][] = $option['value'];
	        }
			if(empty($order_goods['option_str']))
			{
				//option_str
				 $order_goods['option_str'] = '';
			}else{
				 $order_goods['option_str'] = implode(',', $order_goods['option_str']);
			}
	       //
		    $order_goods['shipping_fare'] = round($order_goods['shipping_fare'],2);
		    //$order_goods['price'] = round($order_goods['price'],2);
            $order_goods['price'] = round($order_goods['price'],2);
		    $order_goods['total'] = round($order_goods['total'],2);
            $order_goods['old_total'] = round($order_goods['old_total'],2);

			if( $order_goods['is_vipcard_buy'] == 1 || $order_goods['is_level_buy'] ==1 )
			{
				$order_goods['price'] = round($order_goods['oldprice'],2);
			}
			$order_goods['real_total'] = round($order_goods['quantity'] * $order_goods['price'],2);


			$community_order_info = M('eaterplanet_community_head_commiss_order')->where( array('head_id' => $community_info['id'],'order_goods_id' => $order_goods['order_goods_id']) )->find();

			if(!empty($community_order_info))
			{
				$order_goods['commision'] = $community_order_info['money']-$community_order_info['add_shipping_fare'];
				if($community_order_info['state'] == 2){
					$order_goods['commision'] = 0;
				}
			}else{
				$order_goods['commision'] = 0;
			}


			$order_goods['old_commision'] = $order_goods['commision'];
			$order_goods['del_commision'] = 0;

			$order_goods['statements_end_date'] = date('Y-m-d H:i:s', $order_goods['statements_end_time']);

			//has_refund_commission = 0

			$order_goods_refund_list = M('eaterplanet_ecommerce_order_goods_refund')->where( "order_id={$order_id} and order_goods_id=".$order_goods['order_goods_id'] )->select();

			$refund_shipping_fare = 0;//退配送费
			if( !empty($order_goods_refund_list) )
			{
				$kvbal_total_back_head_orderbuycommiss = 0;//合计退掉佣金

				foreach( $order_goods_refund_list as $kvval )
				{
					$kvbal_total_back_head_orderbuycommiss += $kvval['back_head_orderbuycommiss'];
					$refund_shipping_fare += $kvval['refund_shipping_fare'];
				}
				$order_goods['del_commision'] = $kvbal_total_back_head_orderbuycommiss;

				$order_goods['old_commision'] += $order_goods['del_commision'];
			}

					//fen_type
			$order_goods['fen_type'] = $community_order_info['fen_type'];// 0 按照比例， 1按照金额
			$order_goods['fen_bili'] = $community_order_info['bili'];//比例
			$order_goods['fen_gumoney'] = $community_order_info['bili'];//按照金额

			$order_jishu = $order_goods['total'] -$order_goods['fullreduction_money']-$order_goods['voucher_credit']-$order_goods['has_refund_money'];

			$order_goods['order_jishu'] = $order_jishu;//有效金额：（基数）

			$order_goods['head_shipping_fare'] = $community_order_info['add_shipping_fare'];//团长配送费



			/**
					$goods_images = file_image_thumb_resize($vv['goods_images'],400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= tomedia( file_image_thumb_resize($vv['goods_images'],400) );
					}

			**/
			$goods_images = $order_goods['goods_images'];

			if( !is_array($goods_images) )
			{
				 $order_goods['image']=  tomedia( $goods_images );
				$order_goods['goods_images']= tomedia( $goods_images );
			}else{
				 $order_goods['image']=  $order_goods['goods_images'];
			}

		   $order_goods['hascomment'] = 0;

			$order_goods_comment_info = M('eaterplanet_ecommerce_order_comment')->field('comment_id')->where( array('goods_id' => $order_goods['goods_id'],'order_id' => $order_id) )->find();

			if( !empty($order_goods_comment_info) )
			{
				$order_goods['hascomment'] = 1;
			}

			//ims_

			$goods_info = M('eaterplanet_ecommerce_goods')->field('productprice as price')->where( array('id' => $order_goods['goods_id']) )->find();

			$goods_cm_info = M('eaterplanet_ecommerce_good_common')->field('pick_up_type,pick_up_modify')->where( array('goods_id' => $order_goods['goods_id']) )->find();

			if($pick_up_type == -1 || $goods_cm_info['pick_up_type'] > $pick_up_type)
			{
				$pick_up_type = $goods_cm_info['pick_up_type'];

				if($pick_up_type == 0)
				{
					$pick_up_time = date('m-d', $today_time);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time)];
				}else if( $pick_up_type == 1 ){
					$pick_up_time = date('m-d', $today_time+86400);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
				}else if( $pick_up_type == 2 )
				{
					$pick_up_time = date('m-d', $today_time+86400*2);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
				}else if($pick_up_type == 3)
				{
					$pick_up_time = $goods_cm_info['pick_up_modify'];
				}
			}

			$order_goods['shop_price'] = $goods_info['price'];


			$store_info	= array('s_true_name' =>'','s_logo' => '');

			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');

			if( !empty($store_info['s_logo']) )
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				$store_info['s_logo'] = '';
			}

			if( $order_goods['is_refund_state'] == 1 || ($order_goods['has_refund_money'] > 0 && $order_goods['has_refund_quantity'] > 0) )
			{
				$order_goods['is_refund_state'] = 1;
				$where = " order_id = '".$order_goods['order_id']."' and order_goods_id = '".$order_goods['order_goods_id']."' and state in (0,2,3) ";
				$refund_info = M('eaterplanet_ecommerce_order_refund')->field('ref_id,order_id,ref_money,real_refund_quantity,state')->where( $where )->find();
				if(!empty($refund_info)){
					$order_goods['refund_info'] = $refund_info;
				}else{
					$refund_info = array();
					$refund_info['order_id'] = $order_goods['order_id'];
					$refund_info['ref_money'] = $order_goods['has_refund_money'];
					$refund_info['real_refund_quantity'] = $order_goods['has_refund_quantity'];
					$refund_info['state'] = 3;
					$order_goods['refund_info'] = $refund_info;
				}
			}


			$order_goods['store_info'] = $store_info;

			unset($order_goods['model']);
			unset($order_goods['rela_goodsoption_valueid']);
			unset($order_goods['comment']);

	        $order_goods_list[$key] = $order_goods;
			$shiji_total_money += $order_goods['quantity'] * $order_goods['price'];

			$member_youhui += ($order_goods['real_total'] - $order_goods['total']);
	    }

		unset($order_info['store_id']);
		unset($order_info['email']);
		unset($order_info['shipping_city_id']);
		unset($order_info['shipping_country_id']);
		unset($order_info['shipping_province_id']);
		//unset($order_info['comment']);
		unset($order_info['voucher_id']);
		unset($order_info['is_balance']);
		unset($order_info['lottery_win']);
		unset($order_info['ip']);
		unset($order_info['ip_region']);
		unset($order_info['user_agent']);



		$order_info['shipping_fare'] = round($order_info['shipping_fare'],2) < 0.01 ? '0.00':round($order_info['shipping_fare'],2) ;
		$order_info['voucher_credit'] = round($order_info['voucher_credit'],2) < 0.01 ? '0.00':round($order_info['voucher_credit'],2) ;
		$order_info['fullreduction_money'] = round($order_info['fullreduction_money'],2) < 0.01 ? '0.00':round($order_info['fullreduction_money'],2) ;


		$order_info['total'] = round($order_info['total'],2)< 0.01 ? '0.00':round($order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money']+$order_info['localtown_add_shipping_fare']+$order_info['packing_fare'],2)-$order_info['fare_shipping_free']	;

		if($order_info['total'] < 0)
		{
			$order_info['total'] = '0.00';
		}


		$order_info['total'] = round($order_info['total'],2)< 0.01 ? '0.00':round($order_info['total'],2)	;
		$order_info['real_total'] = round($shiji_total_money,2)+$order_info['shipping_fare'];
		$order_info['price'] = round($order_info['price'],2);
		$order_info['member_youhui'] = round($member_youhui,2) < 0.01 ? '0.00':round($member_youhui,2);
		$order_info['pick_up_time'] = $pick_up_time;


		$order_info['shipping_fare'] = sprintf("%.2f",$order_info['shipping_fare']);
		$order_info['voucher_credit'] = sprintf("%.2f",$order_info['voucher_credit']);
		$order_info['fullreduction_money'] = sprintf("%.2f",$order_info['fullreduction_money']);
		$order_info['total'] = sprintf("%.2f",$order_info['total']);
		$order_info['real_total'] = sprintf("%.2f",$order_info['real_total']);


		$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');
		$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');
		if( !isset($order_note_open) || $order_note_open == 0)
		{
			$order_note_open = 0;
		}
		if( !isset($order_note_name) || empty($order_note_name) )
		{
			$order_note_name = '店名';
		}
		$order_info['order_note_open'] = $order_note_open;
		$order_info['order_note_name'] = $order_note_name;
		$order_info['note_content'] = $order_info['note_content']!='null' ? $order_info['note_content'] : '';


		$order_info['date_added'] = date('Y-m-d H:i:s', $order_info['date_added']);
		$need_data = array();

		if($order_info['delivery'] =='pickup')
		{


		}else{


		}

		if($order_info['type'] == 'integral')
		{
			//暂时屏蔽积分商城
			//$integral_order = M('integral_order')->field('score')->where( array('order_id' => $order_id) )->find();
			//$need_data['score'] = intval($integral_order['score']);

		}
		if( !empty($order_info['pay_time']) && $order_info['pay_time'] >0 )
		{
			$order_info['pay_date'] = date('Y-m-d H:i:s', $order_info['pay_time']);
		}else{
			$order_info['pay_date'] = '';
		}

		$order_info['express_tuanz_date'] = empty($order_info['express_tuanz_time']) ? '' : date('Y-m-d H:i:s', $order_info['express_tuanz_time']);
		$order_info['receive_date'] = date('Y-m-d H:i:s', $order_info['receive_time']);

		if($is_share==1){ $order_info['shipping_tel'] = substr_replace($order_info['shipping_tel'],'****',3,4); }

		//货到付款订单
		if($order_info['payment_code'] == 'cashon_delivery'){
			$order_info['cashondelivery_code_img'] = D('Home/Front')->getCashonDeliveryCode();
		}

		$need_data['order_info'] = $order_info;
		$need_data['order_status_info'] = $order_status_info;
		$need_data['shipping_province'] = $shipping_province;
		$need_data['shipping_city'] = $shipping_city;
		$need_data['shipping_country'] = $shipping_country;
		$need_data['order_goods_list'] = $order_goods_list;

		$need_data['goods_count'] = count($order_goods_list);

		//$order_info['order_status_id'] 13  平台介入退款
		$order_refund_historylist = array();
		$pingtai_deal = 0;

		//判断是否已经平台处理完毕

		$order_refund_historylist = M('eaterplanet_ecommerce_order_refund_history')->where( array('order_id' => $order_id) )->order('addtime asc')->select();

		foreach($order_refund_historylist as $key => $val)
		{
			if($val['type'] ==3)
			{
				$pingtai_deal = 1;
			}
		}

		$order_refund = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_id) )->find();

		if(!empty($order_refund))
		{
			$order_refund['addtime'] = date('Y-m-d H:i:s', $order_refund['addtime']);
		}

		$need_data['pick_up'] = $pick_up_info;

		if( empty($pick_order_info['qrcode']) && false)
		{

		}

		$need_data['pick_order_info'] = $pick_order_info;

		$open_aftersale = D('Home/Front')->get_config_by_name('open_aftersale');

		if( empty($open_aftersale) )
		{
			$open_aftersale = 0;
		}

		$open_aftersale_time = D('Home/Front')->get_config_by_name('open_aftersale_time');
		if( empty($open_aftersale_time) )
		{
			$open_aftersale_time = 0;
		}

		echo json_encode( array('code' => 0,'data' => $need_data,'pingtai_deal' => $pingtai_deal,'order_refund' => $order_refund, 'open_aftersale' => $open_aftersale, 'open_aftersale_time' => $open_aftersale_time ) );
		die();
	}


	public function order_share_info(){

		$_GPC = I('request.');

		$is_share = $_GPC['is_share'];

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


		$member_id = $weprogram_token['member_id'];

		$order_id = $_GPC['id'];

		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

		if($is_share){

			$userInfo = M('eaterplanet_ecommerce_member')->field('avatar')->where( array('member_id' => $order_info['member_id'] ) )->find();

			$order_info['avatar'] = $userInfo['avatar'];
		}

		$pick_up_info = array();
		$pick_order_info = array();

		if( $order_info['delivery'] == 'pickup' )
		{
			//查询自提点
			// $pick_order_info = M('eaterplanet_ecommerce_pick_order')->where( array('order_id' => $order_id ) )->find();

			// $pick_id = $pick_order_info['pick_id'];

			// $pick_up_info = M('eaterplanet_ecommerce_pick_up')->where( array('id' => $pick_id ) )->find();

		}

		$order_status_info = M('eaterplanet_ecommerce_order_status')->where( array('order_status_id' => $order_info['order_status_id'] ) )->find();

	    //10 name
		if($order_info['order_status_id'] == 10)
		{
			$order_status_info['name'] = '等待退款';
		}
		else if($order_info['order_status_id'] == 4 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '待自提';
			//已自提
		}
		else if($order_info['order_status_id'] == 6 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '已自提';

		}
		else if($order_info['order_status_id'] == 1 && $order_info['type'] == 'lottery')
		{
			//等待开奖
			//一等奖
			if($order_info['lottery_win'] == 1)
			{
				$order_status_info['name'] = '一等奖';
			}else {
				$order_status_info['name'] = '等待开奖';
			}

		}

		//$order_info['type']
		//open_auto_delete
		if($order_info['order_status_id'] == 3)
		{
			$open_auto_delete = D('Home/Front')->get_config_by_name('open_auto_delete');
			$auto_cancle_order_time = D('Home/Front')->get_config_by_name('auto_cancle_order_time');

			$order_info['open_auto_delete'] = $open_auto_delete;
			//date_added
			if($open_auto_delete == 1)
			{
				$order_info['over_buy_time'] = $order_info['date_added'] + 3600 * $auto_cancle_order_time;
				$order_info['cur_time'] = time();
			}
		}


		$shipping_province = M('eaterplanet_ecommerce_area')->where( array('id' => $order_info['shipping_province_id'] ) )->find();

		$shipping_city = M('eaterplanet_ecommerce_area')->where( array('id' => $order_info['shipping_city_id'] ) )->find();

		$shipping_country = M('eaterplanet_ecommerce_area')->where( array('id' => $order_info['shipping_country_id'] ) )->find();

		$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->select();


		$shiji_total_money = 0;
		$member_youhui = 0.00;

		$pick_up_time = "";
		$pick_up_type = -1;
		$pick_up_weekday = '';
		$today_time = $order_info['pay_time'];

		$arr = array('天','一','二','三','四','五','六');

	    foreach($order_goods_list as $key => $order_goods)
	    {
			$order_goods['name'] = htmlspecialchars_decode($order_goods['name']);
			$order_refund_goods = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_id, 'order_goods_id' => $order_goods['order_goods_id']  ) )->order('ref_id desc')->find();


			if(!empty($order_refund_goods))
			{
				$order_refund_goods['addtime'] = date('Y-m-d H:i:s', $order_refund_goods['addtime']);
			}


			$order_option_info = M('eaterplanet_ecommerce_order_option')->field('value')->where( array('order_id' => $order_id ,'order_goods_id' => $order_goods['order_goods_id'] ) )->select();


			$order_goods['order_refund_goods'] = $order_refund_goods;

	        foreach($order_option_info as $option)
	        {
	            $order_goods['option_str'][] = $option['value'];
	        }
			if(empty($order_goods['option_str']))
			{
				//option_str
				 $order_goods['option_str'] = '';
			}else{
				 $order_goods['option_str'] = implode(',', $order_goods['option_str']);
			}
	       //
		    $order_goods['shipping_fare'] = round($order_goods['shipping_fare'],2);
		    $order_goods['price'] = round($order_goods['price'],2);
			$order_goods['total'] = round($order_goods['total'],2);

			if( $order_goods['is_vipcard_buy'] == 1 || $order_goods['is_level_buy'] ==1 )
			{
				$order_goods['price'] = round($order_goods['oldprice'],2);
			}


			$order_goods['real_total'] = round($order_goods['quantity'] * $order_goods['price'],2);

			/**
					$goods_images = file_image_thumb_resize($vv['goods_images'],400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= tomedia( file_image_thumb_resize($vv['goods_images'],400) );
					}

			**/
			$goods_images = $order_goods['goods_images'];

			if( !is_array($goods_images) )
			{
				$order_goods['image']=  tomedia( $goods_images );
				$order_goods['goods_images']= tomedia( $goods_images );
			}else{
				$order_goods['image']=  $order_goods['goods_images'];
			}

		    $order_goods['hascomment'] = 0;

			$order_goods_comment_info =	M('eaterplanet_ecommerce_order_comment')->field('comment_id')->where( array('order_id' => $order_id, 'goods_id' => $order_goods['goods_id'] ) )->find();

			if( !empty($order_goods_comment_info) )
			{
				$order_goods['hascomment'] = 1;
			}

			$order_goods['can_ti_refund'] = 1;

			$disable_info = M('eaterplanet_ecommerce_order_refund_disable')->where( array('order_id' => $order_id, 'order_goods_id' => $order_goods['order_goods_id']  ) )->find();


			if( !empty($disable_info) )
			{
				$order_goods['can_ti_refund'] = 0;
			}


			if($order_goods['is_refund_state'] == 1)
			{
				//已经再申请退款中了。或者已经退款了。

				$order_refund_info = M('eaterplanet_ecommerce_order_refund')->field('state')->where(  array('order_id' =>$order_id,'order_goods_id' => $order_goods['order_goods_id'] ) )->find();

				if( $order_refund_info['state'] == 3 )
				{
					$order_goods['is_refund_state'] = 2;
				}


			}

			//ims_
			$goods_info = M('eaterplanet_ecommerce_goods')->field('productprice as price')->where( array('id' => $order_goods['goods_id'] ) )->find();

			$goods_cm_info = M('eaterplanet_ecommerce_good_common')->field('pick_up_type,pick_up_modify,goods_share_image')->where( array('goods_id' => $order_goods['goods_id'] ) )->find();


			if($pick_up_type == -1 || $goods_cm_info['pick_up_type'] > $pick_up_type)
			{
				$pick_up_type = $goods_cm_info['pick_up_type'];

				if($pick_up_type == 0)
				{
					$pick_up_time = date('m-d', $today_time);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time)];
				}else if( $pick_up_type == 1 ){
					$pick_up_time = date('m-d', $today_time+86400);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
				}else if( $pick_up_type == 2 )
				{
					$pick_up_time = date('m-d', $today_time+86400*2);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
				}else if($pick_up_type == 3)
				{
					$pick_up_time = $goods_cm_info['pick_up_modify'];
				}
			}

			if( !empty($goods_cm_info['goods_share_image']) )
			{
				$order_goods['goods_share_image']=  tomedia( $goods_cm_info['goods_share_image'] );
			}else{
				$order_goods['goods_share_image'] = $order_goods['image'];
			}

			$order_goods['shop_price'] = $goods_info['price'];


			$store_info	= array('s_true_name' =>'','s_logo' => '');

			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');

			//$store_info['s_logo'] = load_model_class('front')->get_config_by_name('shoplogo');

			if( !empty($store_info['s_logo']) )
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				$store_info['s_logo'] = '';
			}


			$order_goods['store_info'] = $store_info;

			unset($order_goods['model']);
			unset($order_goods['rela_goodsoption_valueid']);
			unset($order_goods['comment']);

			//ims_   ims_eaterplanet_ecommerce_order_goods_refund addtime

			$order_goods_refund_list = M('eaterplanet_ecommerce_order_goods_refund')->field('real_refund_quantity,money,addtime')->where( array('order_goods_id' =>$order_goods['order_goods_id'] ) )->order('id asc')->select();

			if( empty($order_goods_refund_list) )
			{
				$order_goods_refund_list = array();
			}else{

				foreach( $order_goods_refund_list as $kre => $rval )
				{
					$rval['addtime'] = date('Y-m-d H:i:s', $rval['addtime']);
					$order_goods_refund_list[$kre] = $rval;
				}
			}

			$order_goods['order_goods_refund_list'] = $order_goods_refund_list;

	        $order_goods_list[$key] = $order_goods;
			$shiji_total_money += $order_goods['quantity'] * $order_goods['price'];

			$member_youhui += ($order_goods['real_total'] - $order_goods['total']);
	    }

		unset($order_info['store_id']);
		unset($order_info['email']);
		unset($order_info['shipping_city_id']);
		unset($order_info['shipping_country_id']);
		unset($order_info['shipping_province_id']);
		// unset($order_info['comment']);
		unset($order_info['voucher_id']);
		unset($order_info['is_balance']);
		unset($order_info['lottery_win']);
		unset($order_info['ip']);
		unset($order_info['ip_region']);
		unset($order_info['user_agent']);


		//enum('express', 'pickup', 'tuanz_send')


		//var_dump($order_info['total'],$order_info['shipping_fare'],$order['voucher_credit'],$order['fullreduction_money'] );die();


		$order_info['shipping_fare'] = round($order_info['shipping_fare'],2) < 0.01 ? '0.00':round($order_info['shipping_fare'],2) ;
		$order_info['voucher_credit'] = round($order_info['voucher_credit'],2) < 0.01 ? '0.00':round($order_info['voucher_credit'],2) ;
		$order_info['fullreduction_money'] = round($order_info['fullreduction_money'],2) < 0.01 ? '0.00':round($order_info['fullreduction_money'],2) ;

		$need_data = array();

		if($order_info['type'] == 'integral')
		{
			//暂时屏蔽积分商城
			$order_info['score'] = round($order_info['total'],2);

		}

		$order_info['total'] = round($order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'] - $order_info['score_for_money'],2)	;

		if($order_info['total'] < 0)
		{
			$order_info['total'] = '0.00';
		}



		$order_info['real_total'] = round($shiji_total_money,2)+$order_info['shipping_fare'];
		$order_info['price'] = round($order_info['price'],2);
		$order_info['member_youhui'] = round($member_youhui,2) < 0.01 ? '0.00':round($member_youhui,2);
		$order_info['pick_up_time'] = $pick_up_time;


		$order_info['shipping_fare'] = sprintf("%.2f",$order_info['shipping_fare']);
		$order_info['voucher_credit'] = sprintf("%.2f",$order_info['voucher_credit']);
		$order_info['fullreduction_money'] = sprintf("%.2f",$order_info['fullreduction_money']);
		$order_info['total'] = sprintf("%.2f",$order_info['total']);
		$order_info['real_total'] = sprintf("%.2f",$order_info['real_total']);




		$order_info['date_added'] = date('Y-m-d H:i:s', $order_info['date_added']);


		// if($order_info['delivery'] =='pickup')
		// {}else{}

		if( !empty($order_info['pay_time']) && $order_info['pay_time'] >0 )
		{
			$order_info['pay_date'] = date('Y-m-d H:i:s', $order_info['pay_time']);
		}else{
			$order_info['pay_date'] = '';
		}

		$order_info['express_tuanz_date'] = date('Y-m-d H:i:s', $order_info['express_tuanz_time']);
		$order_info['receive_date'] = date('Y-m-d H:i:s', $order_info['receive_time']);

		//"delivery": "pickup", enum('express', 'pickup', 'tuanz_send')
		if($order_info['delivery'] == 'express')
		{
			$order_info['delivery_name'] = '快递';
		}else if($order_info['delivery'] == 'pickup')
		{
			$order_info['delivery_name'] = '用户自提';
		}else if($order_info['delivery'] == 'tuanz_send'){
			$order_info['delivery_name'] = '团长配送';
		}

		$need_data['order_info'] = $order_info;
		$need_data['order_status_info'] = $order_status_info;
		$need_data['shipping_province'] = $shipping_province;
		$need_data['shipping_city'] = $shipping_city;
		$need_data['shipping_country'] = $shipping_country;
		$need_data['order_goods_list'] = $order_goods_list;

		$need_data['goods_count'] = count($order_goods_list);

		//$order_info['order_status_id'] 13  平台介入退款
		$order_refund_historylist = array();
		$pingtai_deal = 0;

		//判断是否已经平台处理完毕

		$order_refund_historylist = M('eaterplanet_ecommerce_order_refund_history')->where(  array('order_id' => $order_id ) )->order('addtime asc')->select();


		foreach($order_refund_historylist as $key => $val)
		{
			if($val['type'] ==3)
			{
				$pingtai_deal = 1;
			}
		}

		$order_refund = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_id ) )->find();

		if(!empty($order_refund))
		{
			$order_refund['addtime'] = date('Y-m-d H:i:s', $order_refund['addtime']);
		}

		$need_data['pick_up'] = $pick_up_info;



		$need_data['pick_order_info'] = $pick_order_info;
		$order_pay_after_share = D('Home/Front')->get_config_by_name('order_pay_after_share');
		if($order_pay_after_share==1){
			$order_pay_after_share_img = D('Home/Front')->get_config_by_name('order_pay_after_share_img');
			$order_pay_after_share_img = $order_pay_after_share_img ? tomedia($order_pay_after_share_img) : '';
			$need_data['share_img'] = $order_pay_after_share_img;
		}

		//$order_info['order_status_id']
		//0开启，1关闭   取消订单
		$order_can_del_cancle = D('Home/Front')->get_config_by_name('order_can_del_cancle');
		$order_can_del_cancle = empty($order_can_del_cancle) || $order_can_del_cancle == 0 ? 1 : 0;

		$is_hidden_orderlist_phone = D('Home/Front')->get_config_by_name('is_hidden_orderlist_phone');
		$is_show_guess_like = D('Home/Front')->get_config_by_name('is_show_order_guess_like');
		$user_service_switch = D('Home/Front')->get_config_by_name('user_service_switch');

		echo json_encode(
			array(
				'code' => 0,
				'data' => $need_data,
				'pingtai_deal' => $pingtai_deal,
				'order_refund' => $order_refund,
				'order_can_del_cancle' => $order_can_del_cancle,
				'order_pay_after_share' => $order_pay_after_share,
				'is_hidden_orderlist_phone' => $is_hidden_orderlist_phone,
				'is_show_guess_like' => $is_show_guess_like,
				'user_service_switch' => $user_service_switch
			)
		);
		die();
	}

	/**
	 * 订单确认付款
	 */
	public function order_pay(){
		$gpc = I('request.');

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$order_id = $gpc['order_id'];

		$is_supply = $gpc['is_supply'];

		if( empty($member_id) )
		{
			$result['code'] = 2;
			$result['msg'] = '登录失效';
			echo json_encode($result);
			die();
		}
		$supply_info = array();
		$order_info = array();
		if($is_supply == 1){
			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();
			if(empty($supply_info)){
				$result['code'] = 1;
				$result['msg'] = '您不是商户，无法操作';
				echo json_encode($result);
				die();
			}
			$order_info = M('eaterplanet_ecommerce_order')->where( array('store_id' => $supply_info['id'],'order_id' => $order_id) )->find();
		}
		if(empty($order_info)){

			$result['code'] = 1;

			$result['msg'] = '非法操作,商户不存在该订单';

			echo json_encode($result);

			die();

		}
		//order_status_id == 3
		if($order_info['order_status_id'] != 3)
		{
			$result['code'] = 1;

			$result['msg'] = '订单不是未付款订单，无法确认付款';

			echo json_encode($result);

			die();
		}

		D('Seller/Order')->admin_pay_order($order_id,1);

		$result['code'] = 0;

		echo json_encode($result);

		die();
	}

	/**
	 * 订单改价页面
	 */
	public function order_change(){
		$gpc = I('request.');

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$order_id = $gpc['order_id'];

		$is_supply = $gpc['is_supply'];

		if( empty($member_id) )
		{
			$result['code'] = 2;
			$result['msg'] = '登录失效';
			echo json_encode($result);
			die();
		}
		$supply_info = array();
		$order_info = array();
		if($is_supply == 1){
			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();
			if(empty($supply_info)){
				$result['code'] = 1;
				$result['msg'] = '您不是商户，无法操作';
				echo json_encode($result);
				die();
			}
			$order_info = M('eaterplanet_ecommerce_order')->where( array('store_id' => $supply_info['id'],'order_id' => $order_id) )->find();
		}
		if(empty($order_info)){

			$result['code'] = 1;

			$result['msg'] = '非法操作,商户不存在该订单';

			echo json_encode($result);

			die();

		}
		//order_status_id == 3
		if($order_info['order_status_id'] != 3)
		{
			$result['code'] = 1;

			$result['msg'] = '订单不是未付款订单，无法改价';

			echo json_encode($result);

			die();
		}

		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->select();
		$member_youhui = 0;
		foreach($order_goods as $k=>$v){
			$order_goods[$k]['goods_img_url'] = tomedia($v['goods_images']);
			$order_goods[$k]['max_total'] = round($v['total']-$v['fullreduction_money']-$v['voucher_credit']-$v['score_for_money'],2);
			$member_youhui += round(($v['oldprice'] - $v['price'])*$v['quantity'],2);
			$order_goods[$k]['goods_optiontitle'] = D('Seller/Order')->get_order_option_sku($v['order_id'], $v['order_goods_id']);
			$order_goods[$k]['price'] = sprintf("%.2f", $v['price']);
		}
		$youhui_total = round($order_info['voucher_credit']+$order_info['fullreduction_money']+$order_info['score_for_money']+$member_youhui,2);
		$order_info['youhui_total'] = sprintf("%.2f", $youhui_total);
		$order_info['changeprice'] = sprintf("%.2f", $order_info['total'] - $order_info['old_price']);
		$order_info['buyer_total'] = sprintf("%.2f", $order_info['total'] + $order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'] - $order_info['score_for_money'] - $member_youhui);
		echo json_encode(
				array(
						'code' => 0,
						'order_goods' => $order_goods,
						'orders' => $order_info
				)
		);
		die();
	}

	/**
	 * 订单改价
	 */
	public function order_changeprice(){
		$gpc = I('request.');
		$order_id = $gpc['order_id'];
		$order_goods_ids = $gpc['order_goods_id'];
		$change_prices = $gpc['change_price'];
		$is_supply = $gpc['is_supply'];

		$token = $gpc['token'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];
		if( empty($member_id) )
		{
			$result['code'] = 2;
			$result['msg'] = '登录失效';
			echo json_encode($result);
			die();
		}
		$supply_info = array();
		$order_info = array();
		if($is_supply == 1){
			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();
			if(empty($supply_info)){
				$result['code'] = 1;
				$result['msg'] = '您不是商户，无法操作';
				echo json_encode($result);
				die();
			}
			$order_info = M('eaterplanet_ecommerce_order')->where( array('store_id' => $supply_info['id'],'order_id' => $order_id) )->find();
		}
		if(empty($order_info)){
			$result['code'] = 1;
			$result['msg'] = '非法操作,商户不存在该订单';
			echo json_encode($result);
			die();
		}
		//order_status_id == 3
		if($order_info['order_status_id'] != 3)
		{
			$result['code'] = 1;
			$result['msg'] = '订单不是未付款订单，无法改价';
			echo json_encode($result);
			die();
		}
		if(empty($order_goods_ids) || empty($change_prices)){
			$result['code'] = 1;
			$result['msg'] = '商品订单号和价格参数不能为空';
			echo json_encode($result);
			die();
		}
		$order_goods_ids = explode(',',$order_goods_ids);
		$change_prices = explode(',',$change_prices);
		if(count($order_goods_ids) != count($change_prices)){
			$result['code'] = 1;
			$result['msg'] = '商品订单号和价格参数不匹配';
			echo json_encode($result);
			die();
		}
		//改价后的商品实付价格不能低于0.1元
		foreach($order_goods_ids as $k=>$order_goods_id){
			$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$order_id,'order_goods_id' =>$order_goods_id  ) )->find();
			$total = $order_goods_info['total'];
			$fullreduction_money = $order_goods_info['fullreduction_money'];
			$voucher_credit = $order_goods_info['voucher_credit'];
			$score_for_money = $order_goods_info['score_for_money'];
			$total = round($total - $fullreduction_money - $voucher_credit - $score_for_money,2);
			if(!empty($change_prices[$k])){
				$change_price = $change_prices[$k];
				$total = floatval($total) + floatval($change_price);
				if(bccomp($total, '0.1', 2) == -1){
					$result['code'] = 1;
					$result['msg'] =  '改价后的商品实付价格不能低于0.1元';
					echo json_encode($result);
					die();
				}
			}
		}
		$change_amount = 0;
		foreach($order_goods_ids as $k=>$order_goods_id){
			$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$order_id,'order_goods_id' =>$order_goods_id  ) )->find();
			$total = $order_goods_info['total'];
			$update_data = array();
			if(!empty($change_prices[$k])){
				$change_amount = $change_amount + $change_prices[$k];
				$change_price = $change_prices[$k];
				$update_data['total'] = round(floatval($total) + floatval($change_price),2);
				$update_data['is_change_price'] = 1;
				M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$order_id,'order_goods_id' => $order_goods_id  ) )->save( $update_data);
			}
		}
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' =>$order_id) )->find();
		$order_total = $order_info['total'];
		$order_data = array();
		$order_data['total'] = round(floatval($order_total) + floatval($change_amount),2);
		$order_data['is_change_price'] = 1;
		M('eaterplanet_ecommerce_order')->where( array('order_id' =>$order_id) )->save($order_data);

		$oh = array();
		$oh['order_id'] = $order_id;
		$oh['order_status_id']=15;
		$oh['comment']='订单改价';
		$oh['date_added']=time();

		M('eaterplanet_ecommerce_order_history')->add($oh);

		$result['code'] = 0;
		echo json_encode($result);
		die();
	}

	/**
	 * 商户确定配送订单
	 */
	public function order_delivery(){
		$gpc = I('request.');
		$token = $gpc['token'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];
		$order_id = $gpc['order_id'];
		$is_supply = $gpc['is_supply'];
		if( empty($member_id) )
		{
			$result['code'] = 2;
			$result['msg'] = '登录失效';
			echo json_encode($result);
			die();
		}
		$supply_info = array();
		$order_info = array();
		if($is_supply == 1){
			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();
			if(empty($supply_info)){
				$result['code'] = 1;
				$result['msg'] = '您不是商户，无法操作';
				echo json_encode($result);
				die();
			}
			$order_info = M('eaterplanet_ecommerce_order')->where( array('store_id' => $supply_info['id'],'order_id' => $order_id) )->find();
		}
		if(empty($order_info)){
			$result['code'] = 1;
			$result['msg'] = '非法操作,商户不存在该订单';
			echo json_encode($result);
			die();
		}
		if($order_info['order_status_id'] != 1)
		{
			$result['code'] = 1;
			$result['msg'] = '订单不是待发货订单，无法确认配送';
			echo json_encode($result);
			die();
		}
		D('Seller/Order')->do_send_localtown($order_id,'商户操作，开始配送货物');
		$result['code'] = 0;
		echo json_encode($result);
		die();
	}

	/**
	 *  选择配送员
	 */
	public function choosemember()
	{
		$_GPC = I('request.');
		$kwd = trim($_GPC['keyword']);
		$token = $_GPC['token'];
		$order_id = $_GPC['order_id'];
		$is_supply = $_GPC['is_supply'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];
		if( empty($member_id) )
		{
			$result['code'] = 2;
			$result['msg'] = '登录失效';
			echo json_encode($result);
			die();
		}
		$supply_info = array();
		$order_info = array();
		if($is_supply == 1){
			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();
			if(empty($supply_info)){
				$result['code'] = 1;
				$result['msg'] = '您不是商户，无法操作';
				echo json_encode($result);
				die();
			}
			$order_info = M('eaterplanet_ecommerce_order')->where( array('store_id' => $supply_info['id'],'order_id' => $order_id) )->find();
		}
		if(empty($order_info)){
			$result['code'] = 1;
			$result['msg'] = '非法操作,商户不存在该订单';
			echo json_encode($result);
			die();
		}
		if($order_info['order_status_id'] != 4)
		{
			$result['code'] = 1;
			$result['msg'] = '订单不是已发货，待收货订单，无法指定配送员';
			echo json_encode($result);
			die();
		}

		$condition = '  1 ';
		if (!empty($kwd)) {
			$condition .= ' and ( username LIKE "%'.$kwd .'%" or mobile like "%'.$kwd .'%" )';
		}
		$condition .= ' and store_id='.$order_info['store_id'];
		/**
		分页开始
		 **/
		$per_page = 10;
		$page = isset($_GPC['page']) ? $_GPC['page'] : 1;
		$offset = ($page - 1) * $per_page;
		/**
		分页结束
		 **/
		$sql = 'SELECT * FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_orderdistribution  where '
				. $condition .
				' order by id asc limit '.$offset.','.$per_page;
		$list = M()->query($sql);

		if( !empty($list) )
		{
			echo json_encode( array('code' => 0, 'data' => $list) );
			die();
		}else{
			echo json_encode( array('code' => 1, 'msg'=>'暂无数据') );
			die();
		}
	}

	public function sub_orderchoose_distribution(){
		$_GPC = I('request.');
		$token = $_GPC['token'];
		$order_id = $_GPC['order_id'];
		$is_supply = $_GPC['is_supply'];
		$distribution_id = $_GPC['distribution_id'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];
		if( empty($member_id) )
		{
			$result['code'] = 2;
			$result['msg'] = '登录失效';
			echo json_encode($result);
			die();
		}
		$supply_info = array();
		$order_info = array();
		if($is_supply == 1){
			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();
			if(empty($supply_info)){
				echo json_encode(array('code' => 1, 'msg' => '您不是商户，无法操作'));
				die();
			}
			$order_info = M('eaterplanet_ecommerce_order')->where( array('store_id' => $supply_info['id'],'order_id' => $order_id) )->find();
		}
		if(empty($order_info)){
			echo json_encode(array('code' => 1, 'msg' => '非法操作,商户不存在该订单'));
			die();
		}
		if($order_info['order_status_id'] != 4)
		{
			echo json_encode(array('code' => 1, 'msg' => '订单不是已发货，待收货订单，无法指定配送员'));
			die();
		}
		$distribution_info = M('eaterplanet_ecommerce_orderdistribution')->where(array('id'=>$distribution_id,'store_id'=>$supply_info['id']))->find();
		if(empty($distribution_info)){
			echo json_encode(array('code' => 1, 'msg' => '配送员不存在'));
			die();
		}
		$res = D('Home/LocaltownDelivery')->distribution_get_order( $distribution_id , $order_id);
		if($res)
		{
			echo json_encode( array('code' => 0, 'msg' => '分配成功') );
			die();
		}else{
			echo json_encode( array('code' => 1, 'msg' => '已被分配，请刷新页面') );
			die();
		}
	}

	public function share_order(){
		$gpc = I('request.');
		$order_id = $gpc['order_id'];
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();
		$head_id = $order_info['head_id'];
		$member_id = $order_info['member_id'];
		if(empty($order_info)){

			echo json_encode( array('code' => 3, 'msg' => '订单信息不存在') );

			die();

		}
		$cart= D('Home/Car');
		$order_array = array();
		$order_status_id = $order_info['order_status_id'];

		$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id) )->order('order_goods_id asc')->select();
		$order_goods_array = array();
		$goods_quantity = 0;
		foreach($order_goods_list as $k=>$val){
			$goods_common_info = "";
			$where='1=1';
			if( $head_id > 0 )
			{
				$where .= " and (g.is_all_sale = 1 or g.id in (SELECT goods_id from ".C('DB_PREFIX')."eaterplanet_community_head_goods where head_id = {$head_id}) ) ";
			}
			$sql_pingoods = "select g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video,gc.pick_up_type,gc.pick_up_modify,gc.oneday_limit_count, gc.total_limit_count, gc.one_limit_count,gc.goods_start_count  from " .C('DB_PREFIX')."eaterplanet_ecommerce_goods as g,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc where {$where} and g.id=gc.goods_id limit 1 ";
			$goods_common_info = M()->query($sql_pingoods);
			$goods_common_info = $goods_common_info[0];

			$tmp_data = array();
			$order_goods_array[$k]['actId'] = $goods_common_info['id'];
			$order_goods_array[$k]['spuName'] = htmlspecialchars_decode($goods_common_info['goodsname']);
			$order_goods_array[$k]['spuCanBuyNum'] = $goods_common_info['total'];
			$order_goods_array[$k]['spuDescribe'] = $goods_common_info['subtitle'];
			$order_goods_array[$k]['end_time'] = $goods_common_info['end_time'];
			$order_goods_array[$k]['soldNum'] = $goods_common_info['seller_count'] + $goods_common_info['sales'];
			$order_goods_array[$k]['oneday_limit_count'] = $goods_common_info['oneday_limit_count'];
			$order_goods_array[$k]['total_limit_count'] = $goods_common_info['total_limit_count'];
			$order_goods_array[$k]['one_limit_count'] = $goods_common_info['one_limit_count'];
			$order_goods_array[$k]['goods_start_count'] = $goods_common_info['goods_start_count'];
			$productprice = $goods_common_info['productprice'];
			$order_goods_array[$k]['marketPrice'] = explode('.', $productprice);

			if( !empty($goods_common_info['big_img']) )
			{
				$order_goods_array[$k]['bigImg'] = tomedia($goods_common_info['big_img']);
			}

			$good_image = D('Home/Pingoods')->get_goods_images($goods_common_info['id']);
			if( !empty($good_image) )
			{
				$order_goods_array[$k]['skuImage'] = tomedia($good_image['image']);
			}
			$price_arr = D('Home/Pingoods')->get_goods_price($goods_common_info['id'], $member_id);
			$price = $price_arr['price'];

			$order_goods_array[$k]['actPrice'] = explode('.', $price);

			//$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'], $member_id);
			$order_goods_array[$k]['skuList']= D('Home/Pingoods')->get_goods_options_carquantity($goods_common_info['id'], $member_id, $head_id ,$token);
			if( !empty($tmp_data['skuList']) )
			{
				$order_goods_array[$k]['car_count'] = 0;
			}else{

				$car_count = $cart->get_wecart_goods($val['id'],"",$head_id ,$token);

				if( empty($car_count)  )
				{
					$order_goods_array[$k]['car_count'] = 0;
				}else{
					$order_goods_array[$k]['car_count'] = $car_count;
				}
			}
			$goods_quantity = $goods_quantity + $val['quantity'];

			if($order_status_id != 3 && $order_status_id != 5){//不是未付款和取消订单
				$order_goods_array[$k]['price'] = $val['price'];
				$order_goods_array[$k]['oldprice'] = $val['oldprice'];
			}
		}
		if($order_status_id != 3 && $order_status_id != 5) {//不是未付款和取消订单
			$order_array['goods_quantity'] = $goods_quantity;//商品数量
			$order_array['goods_count'] = count($order_goods_list);//商品种类数
			$order_array['order_total'] = $order_info['total'];//订单总金额
			$order_array['old_order_total'] = $order_info['old_price'];//订单原金额
			$order_array['save_money'] = $order_info['old_price'] - $order_info['total'];//省了多少钱
		}
		//用户信息
		$member_id = $order_info['member_id'];
		$member_info = M('eaterplanet_ecommerce_member')->where(array('member_id'=>$member_id))->find();
		$member_array = [];
		$member_array['avatar'] = $member_info['avatar'];//用户头像
		$member_array['username'] = $member_info['username'];//用户昵称
		//团长信息
		if($order_info['delivery'] != 'hexiao'){
			$head_id = $order_info['head_id'];
			$head_info = M('eaterplanet_community_head')->where(array('id'=>$head_id))->field('id,member_id,community_name,head_name,head_mobile,province_id,city_id,country_id,area_id,address')->find();
			$head_member_info = M('eaterplanet_ecommerce_member')->where(array('member_id'=>$head_info['member_id']))->find();
			$head_info['province_name'] = D('Seller/area')->get_area_info($head_info['province_id']);
			$head_info['city_name'] = D('Seller/area')->get_area_info($head_info['city_id']);
			$head_info['area_name'] = D('Seller/area')->get_area_info($head_info['area_id']);
			$head_info['country_name'] = D('Seller/area')->get_area_info($head_info['country_id']);
			$head_info['fulladdress'] = $head_info['province_name'].$head_info['city_name'].$head_info['area_name'].$head_info['country_name'].$head_info['address'];
			$head_info['head_images'] = $head_member_info['avatar'];
			$order_array['head_info'] = $head_info;
		}else{
			//门店信息
			$salesroom_list = D('Home/Salesroom')->get_order_salesroom($order_id,0);
			$order_array['salesroom_info'] = array();
			if(!empty($salesroom_list)){
				$order_array['salesroom_info'] = $salesroom_list[0];
			}
		}


		$need_data = array();
		$need_data['orders'] = $order_array;
		$need_data['order_goods_list'] = $order_goods_array;
		$need_data['members'] = $member_array;

		echo json_encode(
				array(
						'code' => 0,
						'data' => $need_data
				)
		);
	}
}
