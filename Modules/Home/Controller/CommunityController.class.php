<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.io/
 * @copyright Copyright (c) 2019-2023 Dejavu Tech.
 * @license   https://e-p.io/license
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Home\Controller;

class CommunityController extends CommonController {

	public function get_member_ziti_order()
	{
		$gpc = I('request.');



		$xq_member_id = $gpc['memberId'];

		$token = $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];



		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);


		if( empty($community_info) && $member_info['pickup_id'] > 0  )
		{

			$parent_community_info = M('eaterplanet_ecommerce_community_pickup_member')->where( array('member_id' =>$member_id ) )->find();

			if(!empty($parent_community_info))
			{
				$community_info = M('eaterplanet_community_head')->where( array('id' => $parent_community_info['community_id'] ) )->find();
			}
		}


		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 2) );
			die();
		}

		$where = ' and o.head_id = '.$community_info['id']." and o.member_id = ".$xq_member_id;

		$where .= ' and o.order_status_id = 4 ';


		$sql = "select o.order_id,o.order_num_alias,o.date_added,o.delivery,o.is_pin,o.is_zhuli,o.shipping_fare,o.voucher_credit,o.store_id,o.total,o.order_status_id,o.lottery_win,o.type,os.name as status_name,o.shipping_name,o.shipping_tel,o.payment_code "
			 . " from ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_status as os
	                    where     o.delivery != 'express' and o.delivery != 'hexiao' and o.order_status_id = os.order_status_id {$where}
	                    order by o.date_added desc ";

	    $list =  M()->query($sql);

	    //createTime
	    foreach($list as $key => $val)
	    {
			$val['checked'] = 1;
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
				$val['shipping_fare'] = '免运费';
			}else{
				$val['shipping_fare'] = '运费:'.$val['shipping_fare'];
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
	        $url = D('Home/Front')->get_config_by_name('shop_domain').'/';


	        $goods_sql = "select order_id,order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid,is_refund_state,has_refund_money,has_refund_quantity"
                       . " from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods where  order_id= ".$val['order_id']."";

	        $goods_list = M()->query($goods_sql); //M()->query($goods_sql);
	        foreach($goods_list as $kk => $vv)
	        {
	            //commision

				if($is_tuanz == 1){

					$community_order_info = M('eaterplanet_community_head_commiss_order')->where( array('head_id' => $community_info['id'],'order_goods_id' => $vv['order_goods_id']) )->find();


					if(!empty($community_order_info))
					{
						$vv['commision'] = $community_order_info['money'];
					}else{
						$vv['commision'] = 0;
					}

				}

				$order_option_list = M('eaterplanet_ecommerce_order_option')->where( array('order_goods_id' => $vv['order_goods_id']) )->select();


				if( !empty($vv['goods_images']))
				{

					$goods_images = $url.resize($vv['goods_images'],400,400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']=  $url.resize($vv['goods_images'],400,400) ;
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
	            $vv['price'] = round($vv['price'],2);
	            $vv['orign_price'] = round($vv['orign_price'],2);

	            $vv['checked'] = 1;

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
				$store_info['s_logo'] = $url .tomedia($store_info['s_logo']);
			}else{

				$store_info['s_logo'] = '';
			}


			$order_goods['store_info'] = $store_info;




			$val['store_info'] = $store_info;


	        $val['goods_list'] = $goods_list;

			if($val['type'] == 'integral')
			{
				//暂时屏蔽积分
				//$integral_order = M('integral_order')->field('score')->where( array('order_id' => $val['order_id']) )->find();
				//$val['score'] = intval($integral_order['score']);
			}

			$val['total'] = round($val['total'],2);

			//货到付款订单
			if($val['payment_code'] == 'cashon_delivery'){
				$val['cashondelivery_code_img'] = D('Home/Front')->getCashonDeliveryCode();
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

		echo json_encode( $need_data );
		die();

	}


	public function get_community_zhitui_qrcode()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$head_id = $community_info['id'];


		$community_zhitui_qrcode_json = D('Home/Front')->get_config_by_name("community_zhitui_qrcode".$head_id);

		if( empty($community_zhitui_qrcode_json)  )
		{
			$path = "eaterplanet_ecommerce/moduleA/groupCenter/apply";
			$zhitui_qrcod = D('Home/Pingoods')->_get_commmon_wxqrcode($path, $head_id);

			if( empty($zhitui_qrcod) )
			{
				$zhitui_qrcod = '';
			}else{
				$zhitui_qrcod = tomedia($zhitui_qrcod);

				$arr = array();
				$arr['qrcode'] = $zhitui_qrcod;
				$arr['express_time'] = time() + 600;


				$hd_key = "community_zhitui_qrcode".$head_id;

				D('Seller/Config')->update( array( $hd_key => serialize($arr)) );
			}



			echo json_encode( array('code' => 0, 'qrcode' => $zhitui_qrcod ) );
			die();

		}else{

			$community_zhitui_qrcode_arr = unserialize($community_zhitui_qrcode_json);

			if( $community_zhitui_qrcode_arr['express_time'] < time() )
			{

				$path = "eaterplanet_ecommerce/moduleA/groupCenter/apply";
				$zhitui_qrcod = D('Home/Pingoods')->_get_commmon_wxqrcode($path, $head_id);

				if( empty($zhitui_qrcod) )
				{
					$zhitui_qrcod = '';
				}else{
					$zhitui_qrcod = tomedia($zhitui_qrcod);
					$arr = array();
					$arr['qrcode'] = $zhitui_qrcod;
					$arr['express_time'] = time() + 600;

					$ky = "community_zhitui_qrcode".$head_id;

					D('Seller/Config')->update( array( $ky => serialize($arr) ) );
				}

				echo json_encode( array('code' => 0, 'qrcode' => $zhitui_qrcod ) );
				die();
			}else{

				echo json_encode( array('code' => 0, 'qrcode' => $community_zhitui_qrcode_arr['qrcode'] ) );
				die();

			}
		}






	}



	//------begin---
	/**
		获取团长绑定核销人员二维码接口
	**/
	public function get_community_bind_member_qrcode()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info =  D('Home/Front')->get_member_community_info($member_id);

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$head_id = $community_info['id'];

		$community_memberbind_qrcode = D('Home/Front')->get_config_by_name('community_memberbind_qrcode'.$head_id);


		if( empty($community_memberbind_qrcode) )
		{

			$path = "eaterplanet_ecommerce/moduleA/groupCenter/bind_member_hexiao";
			$hexiao_qrcod = D('Home/Pingoods')->_get_commmon_wxqrcode($path, $head_id);

			$data = array();
			$data['time'] = time();
			$data['hexiao_qrcod'] = $hexiao_qrcod;

			$kd_lcc = 'community_memberbind_qrcode'.$head_id;

			D('Seller/Config')->update( array( $kd_lcc => serialize($data) ) );



			echo json_encode( array('code' => 0, 'qrcode' => tomedia($hexiao_qrcod) ) );
			die();
		}else{
			$hexiao_data = unserialize($community_memberbind_qrcode);

			if($hexiao_data['time'] + 600 < time() )
			{
				$path = "eaterplanet_ecommerce/moduleA/groupCenter/bind_member_hexiao";
				$hexiao_qrcod = D('Home/Pingoods')->_get_commmon_wxqrcode($path, $head_id);

				$data = array();
				$data['time'] = time();
				$data['hexiao_qrcod'] = $hexiao_qrcod;

				$ky = 'community_memberbind_qrcode'.$head_id;

				D('Seller/Config')->update( array( $ky => serialize($data) ) );

				echo json_encode( array('code' => 0, 'qrcode' => tomedia($hexiao_qrcod) ) );
				die();
			}else{
				echo json_encode( array('code' => 0, 'qrcode' => tomedia($hexiao_data['hexiao_qrcod']) ) );
				die();
			}
		}

	}



	/**
		团长的核销人员列表
	**/
	public function get_community_hexiao_memberlist()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$head_id = $community_info['id'];


		$member_list = M()->query("select * from ".C('DB_PREFIX').
							"eaterplanet_ecommerce_community_pickup_member where  community_id={$head_id}  and state = 1 order by id desc ");

		if( empty($member_list) )
		{
			echo json_encode( array('code' => 1, 'log' => '暂时没有核销人员') );
			die();
		}else{

			foreach( $member_list as $key => $val )
			{

				$mb_info = M('eaterplanet_ecommerce_member')->field('avatar,username')->where( array('member_id' =>$val['member_id'] ) )->find();

				$member_record_count_arr = M()->query(" select count(order_id) as count from ".C('DB_PREFIX')
										."eaterplanet_ecommerce_community_pickup_member_record where  member_id=".$val['member_id'] );



				$member_record_count = $member_record_count_arr[0]['count'];

				$val['avatar'] = $mb_info['avatar'];
				$val['username'] = $mb_info['username'];
				$val['member_record_count'] = $member_record_count;

				$member_list[$key] = $val;
				//ims_eaterplanet_ecommerce_community_pickup_member_record
			}

			echo json_encode( array('code' => 0, 'member_list' => $member_list) );
			die();
		}


		//ims_eaterplanet_ecommerce_community_pickup_member



	}

	/**
		绑定
	**/

	public function bind_community_member_do()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$name = $_GPC['name'];
		$mobile = $_GPC['mobile'];
		$community_id = $_GPC['community_id'];


		$is_in_info = M('eaterplanet_ecommerce_community_pickup_member')->where( array('member_id' => $member_id) )->find();
		if( !empty($is_in_info) )
		{
			echo json_encode( array('code' => 0) );
			die();
		}

		//pickup_id
		//eaterplanet_ecommerce_community_pickup_member


		$is_in_info = M('eaterplanet_ecommerce_community_pickup_member')->where( array('member_id' => $member_id ) )->find();

		if( !empty($is_in_info) )
		{
			echo json_encode( array('code' => 0) );
			die();
		}


		$ins_data = array();
		$ins_data['community_id'] = $community_id;
		$ins_data['member_id'] = $member_id;
		$ins_data['state'] = 1;
		$ins_data['remark'] = '前台扫码添加,姓名：'.$name.',手机号:'.$mobile;
		$ins_data['addtime'] = time();

		$pickup_id = M('eaterplanet_ecommerce_community_pickup_member')->add($ins_data);

		M('eaterplanet_ecommerce_member')->where(  array('member_id' => $member_id) )->save( array('pickup_id' => $pickup_id ));

		echo json_encode( array('code' => 0) );
		die();
	}


	//2、查看客户核销记录的

	public function get_member_hexiao_orderlist()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		//eaterplanet_ecommerce_community_pickup_member_record
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';

		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;

		$where = " and member_id = {$member_id} ";


		$sql = "select *
				from ".C('DB_PREFIX')."eaterplanet_ecommerce_community_pickup_member_record
	                    where  1  {$where}
	                      order by id desc limit {$offset},{$size}";

	    $list =  M()->query($sql);
		if( !empty($list) )
		{
			foreach($list as  $key => $val)
			{

				$mb_info = M('eaterplanet_ecommerce_member')->field('username,avatar')->where( array('member_id' =>$val['member_id'] ) )->find();

				$val['username'] = $mb_info['username'];
				$val['avatar'] = $mb_info['avatar'];

				$val['addtime'] = date('Y-m-d H:i:s', $val['addtime']);

				$list[$key] = $val;
			}
		}

		if( empty($list) )
		{
			echo json_encode(array('code' => 1));
			die();
		}else{
			echo json_encode( array('code' =>0, 'data' => $list) );
			die();
		}

	}


	//------end----

	/***
		团长对清单进行收货
	**/
	public function sub_head_delivery()
	{

		$gpc = I('request.');

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

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$head_id = $community_info['id'];

		$list_id = $gpc['list_id'];


		$list_info = M('eaterplanet_ecommerce_deliverylist')->where( array('id' => $list_id, 'head_id' => $head_id) )->find();


		if( !empty($list_info) )
		{

			M('eaterplanet_ecommerce_deliverylist')->where( array('id' => $list_id ) )->save( array('state' => 2,'head_get_time' => time() ) );

			//对订单操作，可以去提货了 load_model_class('frontorder')->send_order_operate($order_id);


			$order_ids_all = M('eaterplanet_ecommerce_deliverylist_order')->where( array('list_id' => $list_id ) )->select();

			if( !empty($order_ids_all) )
			{
				foreach($order_ids_all as $order_val)
				{
					$order_status_id_info = M('eaterplanet_ecommerce_order')->field('order_status_id')->where( array('order_id' => $order_val['order_id'] ) )->find();

					$order_status_id  = $order_status_id_info['order_status_id'];
					//配送中才能
					if($order_status_id == 14)
					{

						$history_data = array();
						$history_data['order_id'] = $order_val['order_id'];
						$history_data['order_status_id'] = 4;
						$history_data['notify'] = 0;
						$history_data['comment'] = '前台团长签收配送清单';
						$history_data['date_added'] = time();

						M('eaterplanet_ecommerce_order_history')->add( $history_data );

						//send_order_operate
						D('Home/Frontorder')->send_order_operate($order_val['order_id']);
					}

				}
			}
			echo json_encode( array('code' => 0) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}


	}

	/**
		获取团长清单的商品列表
	**/
	public function get_head_deliverygoods()
	{
		$gpc = I('request.');

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

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$head_id = $community_info['id'];

		//eaterplanet_ecommerce_deliverylist_goods

		$list_id = $gpc['list_id'];

		$page = isset($gpc['page']) ? $gpc['page']:'1';

		$size = isset($gpc['size']) ? $gpc['size']:'20';
		$offset = ($page - 1)* $size;

		$where = " and list_id = {$list_id} ";


		$sql = "select *
				from ".C('DB_PREFIX')."eaterplanet_ecommerce_deliverylist_goods
	                    where  1  {$where}
	                      order by id desc limit {$offset},{$size}";

	    $list =  M()->query($sql);
		if( !empty($list) )
		{
			foreach($list as  $key => $val)
			{
				$val['goods_image'] = tomedia($val['goods_image']);
				$list[$key] = $val;
			}
		}

		if( empty($list) )
		{
			echo json_encode(array('code' => 1));
			die();
		}else{
			echo json_encode( array('code' =>0, 'data' => $list) );
			die();
		}

	}

	/**
		获取团长的清单
	**/
	public function get_head_deliverylist()
	{
		$gpc = I('request.');

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

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$head_id = $community_info['id'];

		$page = isset($gpc['page']) ? $gpc['page']:'1';

		$size = isset($gpc['size']) ? $gpc['size']:'6';
		$offset = ($page - 1)* $size;

		$where = " and head_id = {$head_id} ";

		$status = isset( $gpc['status'] ) ? $gpc['status']: -1;
		$kyw = isset( $gpc['keywords'] ) ? $gpc['keywords']: '';

		if($status >= 0)
		{
			$where .= " and state = {$status} ";
		} else {

		}
		if(!empty($kyw))
		{
			$where .= ' and (head_name like "%'.$kyw.'%" or head_mobile like "%'.$kyw.'%" or line_name like "%'.$kyw.'%" or clerk_name like "%'.$kyw.'%" or clerk_mobile like "%'.$kyw.'%" )';

		}


		$sql = "select *
				from ".C('DB_PREFIX')."eaterplanet_ecommerce_deliverylist
	                    where  1  {$where}
	                      order by id desc limit {$offset},{$size}";
		$list = M()->query($sql);


		if( !empty($list) )
		{
			foreach ($list as $key => &$val) {
				$val['express_time'] = $val['express_time'] ? date('Y-m-d H:i', $val['express_time']) :  '';
				$val['head_get_time'] = $val['head_get_time'] ? date('Y-m-d H:i', $val['head_get_time']) : '';
				$val['create_time'] = $val['create_time'] ? date('Y-m-d H:i',$val['create_time']) : '';
			}
		}

		if( empty($list) )
		{
			echo json_encode(array('code' => 1));
			die();
		}else{
			echo json_encode( array('code' =>0, 'data' => $list) );
			die();
		}

	}

	//--
	public function headorderlist()
	{
		$gpc = I('request.');




		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$head_id = $community_info['id'];

		$page = isset($gpc['page']) ? $gpc['page']:'1';

		$size = isset($gpc['size']) ? $gpc['size']:'6';
		$offset = ($page - 1)* $size;

		$where = " and co.head_id = {$head_id} ";

		$order_status = $gpc['order_status'];

		if($order_status == 1)
		{
			$where .= " and co.state = 0 ";
		} else if($order_status == 2){
			$where .= " and co.state = 1 ";
		}


        /*$sql = "select co.order_id,co.state,co.money,co.addtime ,og.total,og.name
                from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
                        where  co.order_goods_id = og.order_goods_id {$where}
                          order by co.id desc limit {$offset},{$size}";*/

		$sql = "select co.order_id,co.state,co.money,co.addtime ,og.total,og.name,co.type
				from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co left join
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og on co.order_goods_id = og.order_goods_id
	                    where 1=1  {$where}
	                      order by co.id desc limit {$offset},{$size}";

	    $list =  M()->query($sql);

		if( !empty($list) )
		{
			foreach($list as $key => $val)
			{
				$val['total'] = sprintf("%.2f",$val['total']);
				$val['money'] = sprintf("%.2f",$val['money']);

				$val['addtime'] = date('Y-m-d H:i:s',$val['addtime']);

				$order_info= M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $val['order_id']) )->find();

				$val['order_num_alias'] = $order_info['order_num_alias'];
				if($val['type'] == 'tuijian'){
					$val['name'] = "推荐团长现金奖励";
				}
				$list[$key] = $val;
			}
		}

		if( empty($list) )
		{
			echo json_encode(array('code' => 1));
			die();
		}else{
			echo json_encode( array('code' =>0, 'data' => $list) );
			die();
		}


	}

	public function cashlist()
	{
		$gpc = I('request.');


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

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$page = isset($gpc['page']) ? $gpc['page']:'1';

		$size = isset($gpc['size']) ? $gpc['size']:'6';
		$offset = ($page - 1)* $size;

		//begin select

		$sql = "select *
				from ".C('DB_PREFIX')."eaterplanet_community_head_tixian_order
	                    where  head_id =".$community_info['id']." and member_id={$member_id}   order by id desc limit {$offset},{$size}";

	    $list =  M()->query($sql);

		foreach($list as $key => $val)
		{
			$val['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
			$val['id'] = sprintf("%04d", $val['id']);

			$list[$key] = $val;
		}

		if( empty($list) )
		{
			echo json_encode( array('code' => 1) );
			die();
		} else{
			echo json_encode( array('code' => 0, 'data' => $list) );
			die();
		}
		//ims_

	}

	public function get_community_member_orderlist()
	{
		$gpc = I('request.');

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];


		$member_info =  M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);

		if( empty($community_info) && $member_info['pickup_id'] > 0  )
		{

			$parent_community_info = M('eaterplanet_ecommerce_community_pickup_member')->where( array('member_id' => $member_id ) )->find();

			if(!empty($parent_community_info))
			{
				$community_info = M('eaterplanet_community_head')->where( array('id' => $parent_community_info['community_id'] ) )->find();
			}


		}



		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$page = isset($gpc['page']) ? $gpc['page']:'1';

		$size = isset($gpc['size']) ? $gpc['size']:'20';
		$offset = ($page - 1)* $size;

		//begin select
		$where = ' and h.head_id = '.$community_info['id'];

		 //date: that.data.date,
		//searchKey: that.data.searchKey,
		if( isset($gpc['date']) && !empty($gpc['date']) )
		{

		}

		if( isset($gpc['searchKey']) && !empty($gpc['searchKey']) )
		{
			$keywords = $gpc['searchKey'];

			$mb_order_list = M()->query( "select member_id from ". C('DB_PREFIX')."eaterplanet_ecommerce_order
							where head_id=".$community_info['id']." and (shipping_tel like '%{$keywords}%' or shipping_name like '%{$keywords}%' ) " );

			$member_id_ids = array();
			if( !empty($mb_order_list) ){
				foreach($mb_order_list as $val)
				{
					$member_id_ids[] = 	$val['member_id'];
				}
			}


			if( !empty($member_id_ids) )
			{
				$member_id_ids_str = implode(',', $member_id_ids);
				$where .=" and (m.username like '%{$keywords}%' or h.member_id in({$member_id_ids_str}) ) ";
			}else{
				$where .=" and (m.username like '%{$keywords}%'  ) ";
			}

		}

		 $sql = "select h.member_id,h.id ,m.avatar, m.username ,("."select count(order_id) from ".C('DB_PREFIX')."eaterplanet_ecommerce_order
								where  order_status_id =4 and delivery!='express' and delivery!='hexiao' and member_id=m.member_id and head_id= ".$community_info['id'].") as m_count
				from ".C('DB_PREFIX')."eaterplanet_community_history as h left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on h.member_id = m.member_id
	                    where  1   {$where}
	                     group by h.member_id order by m_count desc, h.id desc limit {$offset},{$size}";


	    $list =  M()->query($sql);


		//ims_eaterplanet_ecommerce_order
		$need_list = array();

		foreach($list as $key => $val)
		{
			$mb_info_ck = M('eaterplanet_ecommerce_member')->where( array('member_id' => $val['member_id'] ) )->find();
			if( empty($mb_info_ck) )
			{
				continue;
			}

			$last_order_info = M('eaterplanet_ecommerce_order')->field('shipping_tel,shipping_name')->where( array('head_id' => $community_info['id'],'member_id' =>$val['member_id'] ) )->order('order_id desc')->find();

			if( empty($last_order_info) )
			{
				$val['mobile'] = '未下单';
			}else{
				$val['mobile'] = $last_order_info['shipping_tel'];
			}
			$val['shipping_name'] = $last_order_info['shipping_name'];

			$order_count = M('eaterplanet_ecommerce_order')->where( array('head_id' => $community_info['id'],'member_id' => $val['member_id'],'order_status_id' => 4) )->where("delivery!='express' and delivery!='hexiao'")->count();


			//$val['username'] = $member_info['username'];
			//$val['avatar'] = $member_info['avatar'];
			$val['order_count'] = $order_count;

			$list[$key] = $val;
			$need_list[$key] = $val;
		}


		$close_community_delivery_orders = D('Home/Front')->get_config_by_name('close_community_delivery_orders');


		if( !empty($need_list) )
		{
			$list = array();
			$i =0;
			foreach($need_list as $key => $val)
			{
				$list[$i] = $val;
				$i ++;
			}

			echo json_encode( array('code' => 0, 'data' => $list , 'close_community_delivery_orders'=>$close_community_delivery_orders ) );
			die();
		}else {
			echo json_encode( array('code' => 1) );
			die();
		}

	}


	public function bind_community_info()
	{
		$gpc = I('request.');



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

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$cardname = $gpc['cardname'];
		$cardaccount = $gpc['cardaccount'];
		$cardno = $gpc['cardno'];

		//ims_
		//pdo_update('eaterplanet_community_head', $data, array('id' => $head_info['id']));

		$data = array();
		$data['bankname'] = $cardname;
		$data['bankaccount'] = $cardaccount;
		$data['bankusername'] = $cardno;


		M('eaterplanet_community_head_commiss')->where( array('head_id' => $community_info['id']) )->save( $data );

		echo json_encode( array('code' => 0) );
		die();

	}

	public function get_community_info()
	{
		$gpc = I('request.');

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

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info['re_id'] = sprintf("%05d", $community_info['id']);

		$head_id = $community_info['id'];
		//已获佣金情况
		$commission_info = D('Seller/Community')->get_head_commission_info($member_id, $head_id);

		//总订单数量
		$total_order_count =  D('Home/Frontorder')->get_community_head_order_count($head_id);

		//待配送 1  1
		$wait_send_count =  M('eaterplanet_ecommerce_order')->where( array('order_status_id' => 1, 'head_id' => $head_id)  )->where(" type != 'ignore' ")->count();

		//待签收 14 14

		$wait_qianshou_count = M('eaterplanet_ecommerce_order')->where( array('order_status_id' => 14, 'head_id' => $head_id)  )->count();

		//待提货  4 4
		$wait_tihuo_count = M('eaterplanet_ecommerce_order')->where( array('order_status_id' => 4, 'head_id' => $head_id)  )->count();

		//完成6-》  (6,11)
		$has_success_count = M('eaterplanet_ecommerce_order')->where( array('order_status_id' => array('in','6,11'), 'head_id' => $head_id)  )->count();



		//客户数量
		$total_member_count = D('Seller/Community')->get_community_head_member_count($head_id);

		//预计佣金 state=0
		$pre_total_money = M('eaterplanet_community_head_commiss_order')->where( array('state' => 0,'head_id' => $head_id) )->sum('money');

		if( empty($pre_total_money) )
		{
			$pre_total_money = 0;
		}


		//退配送费
		$refund_shipping_fare = 0;
		$refund_shipping_sql = "SELECT SUM( refund_shipping_fare ) as refund_shipping_fare "
							 . " FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund ogf, ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order oco "
							 . " WHERE ogf.order_id = oco.order_id "
							 . " AND oco.head_id = ".$head_id
							 . " AND oco.state = 0 "
							 . " AND oco.add_shipping_fare > 0 ";
		$refund_shippings = M()->query($refund_shipping_sql);
		if(!empty($refund_shippings)){
			$refund_shipping_fare = $refund_shippings[0]['refund_shipping_fare'];
		}
		$pre_total_money = $pre_total_money - $refund_shipping_fare;
		$commission_info['mix_total_money'] = $pre_total_money + $commission_info['money'] + $commission_info['dongmoney'] + $commission_info['getmoney'];



		$today_time = strtotime( date('Y-m-d').' 00:00:00' );
		//今日订单总数

		$today_order_count = M('eaterplanet_ecommerce_order')->where("head_id={$head_id} and date_added>={$today_time}")->count();


		//今日有效订单

		$today_effect_order_count = M('eaterplanet_ecommerce_order')->where( "order_status_id not in(3,5,7,12) and head_id={$head_id} and date_added>={$today_time} " )->count();


		//1、销售额：团长下面今日订单额度的总和，当日订单总数页面的总和  TODO..
		$seven_pay_where = " and head_id ={$head_id} and pay_time >= {$today_time} and order_status_id in (1,4,6,11,14) ";

		$seven_pay_money_info = D('Seller/Order')->get_order_sum(' sum(total+shipping_fare-voucher_credit-fullreduction_money) as total ' , $seven_pay_where);

		$head_today_pay_money = empty($seven_pay_money_info['total']) ? 0:$seven_pay_money_info['total'];

		$head_today_pay_money = sprintf("%.2f",$head_today_pay_money);

		//2、新增客户数：团长新增的用户统计  TODO..

		$sql_count = "select count(h.id) as count from ".C('DB_PREFIX').
					"eaterplanet_community_history as h, ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m where m.member_id=h.member_id and h.head_id={$head_id} and m.create_time>={$today_time} ";

		$today_add_head_member_arr = M()->query($sql_count);

		$today_add_head_member = $today_add_head_member_arr[0]['count'];

		//3.售后订单（笔）:今日团长下面，申请售后的订单
		$sql_count = "select count(ref_id) as count from ".C('DB_PREFIX').
					"eaterplanet_ecommerce_order_refund where  head_id={$head_id} and addtime>={$today_time} ";

		$today_after_sale_order_count_arr = M()->query($sql_count);

		$today_after_sale_order_count = $today_after_sale_order_count_arr[0]['count'];

		//4、今日访客：统计今日浏览团长商城的用户数量  TODO..
		$sql_count = "select count(id) as count from ".C('DB_PREFIX').
					"eaterplanet_community_history where  head_id={$head_id} and addtime>={$today_time} ";

		$today_invite_head_member_arr = M()->query($sql_count);

		$today_invite_head_member = $today_invite_head_member_arr[0]['count'];


		//今日付款人数
		$sql_count = "select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where  head_id={$head_id} and pay_time>={$today_time} group by member_id  ";
		$today_pay_order_list = M()->query($sql_count);

		$today_pay_order_count = count($today_pay_order_list);



		//今日预计佣金
		//$today_pre_total_money = M('eaterplanet_community_head_commiss_order')->where( " state=0 and head_id={$head_id} and addtime>={$today_time}" )->sum('money');
		$pre_sql = "SELECT SUM( co.money ) as money "
				    . "FROM ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order AS co "
					. "LEFT JOIN ".C('DB_PREFIX')."eaterplanet_ecommerce_order AS o ON co.order_id = o.order_id"
					. " WHERE co.head_id = ".$head_id
					. " AND co.addtime >= ".$today_time
					. " AND o.order_status_id not in (3,5,7,12) and co.state != 2 ";
		$today_pre_total_moneys = M()->query($pre_sql);
		if(!empty($today_pre_total_moneys)){
			$today_pre_total_money = $today_pre_total_moneys[0]['money'];
		}
		if(empty($today_pre_total_money))
		{
			$today_pre_total_money = 0;
		}
		//退配送费
		$today_refund_shipping_fare = 0;
		$today_refund_shipping_sql = "SELECT SUM( refund_shipping_fare ) as refund_shipping_fare "
				. " FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund ogf, ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order oco "
				. " WHERE ogf.order_id = oco.order_id "
				. " AND oco.head_id = ".$head_id
				. " AND oco.state = 0 "
				. " AND oco.addtime >= '".$today_time."'"
				. " AND oco.add_shipping_fare > 0 ";
		$today_refund_shippings = M()->query($today_refund_shipping_sql);
		if(!empty($today_refund_shippings)){
			$today_refund_shipping_fare = $today_refund_shippings[0]['refund_shipping_fare'];
		}
		$today_pre_total_money = $today_pre_total_money - $today_refund_shipping_fare;


		//今日预计佣金+得到佣金
		$today_all_total_money = M('eaterplanet_community_head_commiss_order')->where( "(state=0 or state =1) and head_id={$head_id} and addtime>={$today_time}" )->sum('money');

		if(empty($today_all_total_money))
		{
			$today_all_total_money = 0;
		}

		$today_all_total_money = sprintf("%.2f", $today_all_total_money);


		$month_day = date('Y-m').'-01 00:00:00';
		$month_time = strtotime($month_day);

		//本月收入  and (state=0 or state =1)
		$month_pre_total_money = M('eaterplanet_community_head_commiss_order')->where( "head_id={$head_id} and (state=0 or state =1) and addtime>={$month_time}" )->sum('money');

		if(empty($month_pre_total_money))
		{
			$month_pre_total_money = 0;
		}

		/**待确认佣金*/

		//$wait_sub_total_money = M('eaterplanet_community_head_commiss_order')->where( "head_id={$head_id} and state=0" )->sum('money');
		$sql = "select sum( co.money ) as money from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
	                    where  co.order_goods_id = og.order_goods_id and  og.is_refund_state = 0 and co.state = 0 and co.head_id = ".$head_id." order by co.id desc ";

		$pre_total_money_list = M()->query($sql);

		$wait_sub_total_money = $pre_total_money_list[0]['money'];
		if( empty($wait_sub_total_money) )
		{
			$wait_sub_total_money = 0;
		}


		//提现中
		$dongmoney = M('eaterplanet_community_head_tixian_order')->where( array('head_id' => $head_id, 'state' => 0) )->sum('money');
		if( empty($dongmoney) )
		{
			$dongmoney =  0 ;
		}



		/**已成功提现金额**/

		$tixian_sucess_money = M('eaterplanet_community_head_tixian_order')->where( array('head_id' => $head_id, 'state' => 1) )->sum('money');

		if( empty($tixian_sucess_money) )
		{
			$tixian_sucess_money = 0;
		}


		$head_commiss_tixianway_yuer  = D('Home/Front')->get_config_by_name('head_commiss_tixianway_yuer');
		$head_commiss_tixianway_weixin  = D('Home/Front')->get_config_by_name('head_commiss_tixianway_weixin');
		$head_commiss_tixianway_alipay  = D('Home/Front')->get_config_by_name('head_commiss_tixianway_alipay');
		$head_commiss_tixianway_bank  = D('Home/Front')->get_config_by_name('head_commiss_tixianway_bank');

		$community_info['head_commiss_tixianway_yuer'] = empty($head_commiss_tixianway_yuer) ? 1 : ($head_commiss_tixianway_yuer == 2 ? 1:0);
		$community_info['head_commiss_tixianway_weixin'] = empty($head_commiss_tixianway_weixin) ? 1 : ($head_commiss_tixianway_weixin == 2 ? 1:0);
		$community_info['head_commiss_tixianway_alipay'] = empty($head_commiss_tixianway_alipay) ? 1 : ($head_commiss_tixianway_alipay == 2 ? 1:0);
		$community_info['head_commiss_tixianway_bank'] = empty($head_commiss_tixianway_bank) ? 1 : ($head_commiss_tixianway_bank == 2 ? 1:0);


		//上一微信真实姓名 eaterplanet_community_head_tixian_order
		$last_weixin_realname = "";

		//C('DB_PREFIX')
		$last_weixin_info = M('eaterplanet_community_head_tixian_order')->where( array("member_id={$member_id} and head_id={$head_id} and type=2") )->find();

		if( !empty($last_weixin_info) )
		{
			$last_weixin_realname = $last_weixin_info['bankusername'];
		}

		//上一支付宝账号
		$last_alipay_name = '';
		$last_alipay_account = '';

		$last_alipay_info = M('eaterplanet_community_head_tixian_order')->where("member_id={$member_id} and head_id={$head_id} and type=3")->find();

		if( !empty($last_alipay_info) )
		{
			$last_alipay_name = $last_alipay_info['bankusername'];
			$last_alipay_account = $last_alipay_info['bankaccount'];
		}

		//上一银行卡信息
		$last_bank_bankname = '';
		$last_bank_account = '';
		$last_bank_name = '';

		$last_bank_info = M('eaterplanet_community_head_tixian_order')->where( "member_id={$member_id} and head_id={$head_id} and type=4" )->find();

		if( !empty($last_bank_info) )
		{
			$last_bank_bankname = $last_bank_info['bankname'];
			$last_bank_account = $last_bank_info['bankaccount'];
			$last_bank_name = $last_bank_info['bankusername'];
		}

		$community_info['last_weixin_realname'] = $last_weixin_realname;
		$community_info['last_alipay_name'] = $last_alipay_name;
		$community_info['last_alipay_account'] = $last_alipay_account;

		$community_info['last_bank_bankname'] = $last_bank_bankname;
		$community_info['last_bank_account'] = $last_bank_account;
		$community_info['last_bank_name'] = $last_bank_name;


		$head_commission_levelname 	= D('Home/Front')->get_config_by_name('head_commission_levelname');
		$default_comunity_money 	= D('Home/Front')->get_config_by_name('default_comunity_money');

		$level_list = array(
			0	=> array('levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money )
		);


		$hd_level_list = M('eaterplanet_ecommerce_community_head_level')->where(1)->select();

		if( !empty($hd_level_list) )
		{
			foreach( $hd_level_list as $val )
			{
				$tmp = array();
				$tmp['levelname'] = $val['levelname'];//等级名称
				$tmp['commission'] = $val['commission'];//分佣比例

				$level_list[$val['id']] = $tmp;
			}
		}
		$is_show_head_level = D('Home/Front')->get_config_by_name('is_show_head_level');

		$community_info['is_show_head_level'] = $is_show_head_level;
		$community_info['head_level_info'] = $level_list[ $community_info['level_id'] ];

		// 接龙开关
		$is_open_solitaire = D('Home/Front')->get_config_by_name('is_open_solitaire');

		$is_show_community_ranking = D('Home/Front')->get_config_by_name('is_show_community_ranking');

		//是否显示团长排行， 0不显示，1显示
        $is_show_community_ranking = empty($is_show_community_ranking) ? 0 : $is_show_community_ranking;

		$result = array();
		$result['code'] = 0;
		$result['member_info'] = $member_info;
		$result['community_info'] = $community_info;
		$result['commission_info'] = $commission_info;
		$result['total_order_count'] = $total_order_count;
		$result['total_member_count'] = $total_member_count;
		$result['today_order_count'] = $today_order_count;
		$result['today_effect_order_count'] = $today_effect_order_count;
		$result['today_all_total_money'] = $today_all_total_money;
		$result['today_pay_order_count'] = $today_pay_order_count;
		$result['today_pre_total_money'] = $today_pre_total_money;
		$result['month_pre_total_money'] = $month_pre_total_money;
		$result['pre_total_money'] = $pre_total_money;
		$result['wait_sub_total_money'] = $wait_sub_total_money;
		$result['dongmoney'] = $dongmoney;
		$result['tixian_sucess_money'] = $tixian_sucess_money;

		$result['wait_send_count'] = $wait_send_count;
		$result['wait_qianshou_count'] = $wait_qianshou_count;
		$result['wait_tihuo_count'] = $wait_tihuo_count;
		$result['has_success_count'] = $has_success_count;

		$result['head_today_pay_money'] = $head_today_pay_money;//今日销售额
		$result['today_add_head_member'] = $today_add_head_member;//今日新增客户数
		$result['today_after_sale_order_count'] = $today_after_sale_order_count;//今日售后订单
		$result['today_invite_head_member'] = $today_invite_head_member;//今日访客
		$result['is_show_community_ranking'] = $is_show_community_ranking;//团长排行

		$result['is_open_solitaire'] = $is_open_solitaire;


		$community_tixian_fee = D('Home/Front')->get_config_by_name('community_tixian_fee');

		$community_min_money = D('Home/Front')->get_config_by_name('community_min_money');

		if( empty($community_min_money) )
		{
			$community_min_money = 0;
		}

		if( empty($community_tixian_fee) )
		{
			$community_tixian_fee = 0;
		}
		$result['community_tixian_fee'] = $community_tixian_fee;
		$result['community_min_money'] = $community_min_money;



		$open_community_addhexiaomember = D('Home/Front')->get_config_by_name('open_community_addhexiaomember');

		if( empty($open_community_addhexiaomember) )
		{
			$open_community_addhexiaomember = 0;
		}

		$result['open_community_addhexiaomember'] = $open_community_addhexiaomember;

		//团长等级
		$open_community_head_leve = D('Home/Front')->get_config_by_name('open_community_head_leve');

		if( empty($open_community_head_leve) )
		{
			$open_community_head_leve = 0;
		}

		$result['open_community_head_leve'] = $open_community_head_leve;

		$head_commiss_tixian_publish = D('Home/Front')->get_config_by_name('head_commiss_tixian_publish');

		$result['head_commiss_tixian_publish'] = htmlspecialchars_decode( $head_commiss_tixian_publish );


		$is_need_subscript = 0;
		$need_subscript_template = array();


			$apply_tixian_info = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $member_id , 'type' => 'apply_tixian') )->find();

			if( empty($apply_tixian_info) )
			{
				$weprogram_subtemplate_apply_tixian = D('Home/Front')->get_config_by_name('weprogram_subtemplate_apply_tixian');

				if( !empty($weprogram_subtemplate_apply_tixian) )
				{
					$need_subscript_template['apply_tixian'] = $weprogram_subtemplate_apply_tixian;
				}
			}

			if( !empty($need_subscript_template) )
			{
				$is_need_subscript = 1;
			}


		$result['is_need_subscript'] = $is_need_subscript;
		$result['need_subscript_template'] = $need_subscript_template;

		// 分享信息
		$result['shop_index_share_title'] = D('Home/Front')->get_config_by_name('shop_index_share_title');
		$shop_index_share_image = D('Home/Front')->get_config_by_name('shop_index_share_image');
		$result['shop_index_share_image'] = "";
		if($shop_index_share_image) {
			$result['shop_index_share_image'] = tomedia($shop_index_share_image);
		}

		echo json_encode(  $result );
		die();
	}
	//begin
	/**
		获取团长的下级列表接口
	**/
	public function get_head_child_headlist()
	{
		$gpc = I('request.');
		$_GPC = I('request.');

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

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$head_id = $community_info['id'];


		//...

		$page = isset($_GPC['page']) ? $_GPC['page']:'1';

		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;

		//begin select
		//$where = ' and head_id = '.$head_id;

		$level = isset($_GPC['level']) ? $_GPC['level']: 1;

		$level_1_ids = array();
		$level_2_ids = array();
		$level_3_ids = array();

		$head_id_arr = array($head_id);

		if( $level == 1 )
		{
			$list = array();

			$sql = "select * from ".C('DB_PREFIX')."eaterplanet_community_head
	                    where  agent_id in (".implode(',', $head_id_arr).")   order by id desc limit {$offset},{$size}";

			$list =  M()->query($sql);

			foreach( $list as $vv )
			{
				$level_1_ids[] = $vv['id'];
			}

		}else if( $level == 2 )
		{
			$list = array();

			$sql = "select * from ".C('DB_PREFIX')."eaterplanet_community_head
	                    where  agent_id in (".implode(',', $head_id_arr).")   order by id desc limit {$offset},{$size}";

			$list1 =  M()->query($sql);

			if( !empty($list1) )
			{
				foreach( $list1 as $vv )
				{
					$level_1_ids[] = $vv['id'];
				}

				$level_sql2 =" select * from ".C('DB_PREFIX').
							"eaterplanet_community_head  where
								agent_id in (select id from ".C('DB_PREFIX')."eaterplanet_community_head
								where agent_id ={$head_id}  order by id desc )  order by id desc ";

				$list2 =  M()->query($level_sql2);

				if( !empty($list2) ||   !empty($level_1_ids))
				{
					foreach( $list2 as $vv )
					{
						$level_2_ids[] = $vv['id'];
					}

					$need_ids = empty($level_1_ids) ? array() : $level_1_ids;
					if(!empty($level_2_ids))
					{
						foreach($level_2_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}

					$sql =" select * from ".C('DB_PREFIX').
								"eaterplanet_community_head  where
									id in (".implode(',', $need_ids ).")  order by id desc limit {$offset},{$size}";

					$list =  M()->query($sql);
				}
			}

		}else if( $level == 3 ){
			$sql = "select * from ".C('DB_PREFIX')."eaterplanet_community_head
	                    where   agent_id in (".implode(',', $head_id_arr).")   order by id desc limit {$offset},{$size}";

			$list1 =  M()->query($sql);

			if( !empty($list1) )
			{
				foreach( $list1 as $vv )
				{
					$level_1_ids[] = $vv['id'];
				}
				$need_ids = empty($level_1_ids) ? array() : $level_1_ids;

				$level_sql2 =" select * from ".C('DB_PREFIX').
							"eaterplanet_community_head  where
								agent_id in (select id from ".C('DB_PREFIX')."eaterplanet_community_head
								where agent_id ={$head_id}  order by id desc )  order by id desc ";

				$list2 =  M()->query($level_sql2);

				if( !empty($list2) ||   !empty($level_1_ids))
				{
					foreach( $list2 as $vv )
					{
						$level_2_ids[] = $vv['id'];
					}

					if(!empty($level_2_ids))
					{
						foreach($level_2_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
				}


				$level_sql3 =" select * from ".C('DB_PREFIX').
							"eaterplanet_community_head  where
								agent_id in (".implode(',', $need_ids).")  order by id desc ";

				$list3 =  M()->query($level_sql3);

				if( !empty($list3) )
				{
					foreach( $list3 as $vv )
					{
						$level_3_ids[] = $vv['id'];
					}

					if(!empty($level_3_ids))
					{
						foreach($level_3_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
				}

				$level_sql3 =" select * from ".C('DB_PREFIX').
						"eaterplanet_community_head where  id in (".implode(',',$need_ids).") order by id desc limit {$offset},{$size}";

				$list =  M()->query($level_sql3);

			}



		}

		//---------等级

		$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();

		$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');
		$default_comunity_money = D('Home/Front')->get_config_by_name('default_comunity_money');

		$list_default = array(
			array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);

		$community_head_level = array_merge($list_default, $community_head_level);

		$level_id_to_name = array( 0=> empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname );


		foreach($community_head_level as $kk => $vv)
		{
			$level_id_to_name[$vv['id']] = $vv['levelname'];
		}
		//---------等级

		$level_list = array();
		$need_list = array();

		if( !empty($list) ){
			foreach($list as $key => $val)
			{
				$mb_info = M('eaterplanet_ecommerce_member')->field('avatar,username')->where( array('member_id' => $val['member_id'] ) )->find();

				$val['level_name'] = $level_id_to_name[ $val['level_id'] ];

				$val['avatar'] = $mb_info['avatar'];
				$val['username'] = $mb_info['username'];
				$val['apptime'] = date('Y-m-d H:i:s', $val['apptime']);

				$need_list[$key] = $val;
			}
		}

		if( !empty($need_list) )
		{
			echo json_encode( array('code' => 0, 'data' => $need_list) );
			die();
		}else {
			echo json_encode( array('code' => 1) );
			die();
		}

	}

	/**
		获取团长分销账户信息情况
	**/
	public function get_head_distribute_info()
	{
		$gpc = I('request.');
		$_GPC = I('request.');

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$head_id = $community_info['id'];

		$open_community_head_leve  = D('Home/Front')->get_config_by_name('open_community_head_leve');
		if( empty($open_community_head_leve) )
		{
			$open_community_head_leve = 0;
		}
		//总收入 wait_money
		//eaterplanet_community_head_commiss_order

		/**总收入 0 1 **/

		$total_money =  M('eaterplanet_community_head_commiss_order')->where( "(type='tuijian' or type ='commiss') and head_id={$head_id} and state=1" )->sum('money');

		if( empty($total_money) )
		{
			$total_money = 0;
		}
		//tuijian commiss


		//待确认

		$tixian_wait_money =  M('eaterplanet_community_head_commiss_order')->where( "(type='tuijian' or type ='commiss') and head_id={$head_id} and state=0" )->sum('money');

		if( empty($tixian_wait_money) )
		{
			$tixian_wait_money = 0;
		}

		//已确认
		$tixian_success_money = M('eaterplanet_community_head_commiss_order')->where( "(type='tuijian' or type ='commiss') and head_id={$head_id} and state=1" )->sum('money');

		if( empty($tixian_success_money) )
		{
			$tixian_success_money = 0;
		}

		//下级团长数量
		$level_count1 = 0;
		$level_count2 = 0;
		$level_count3 = 0;
		$level_count1 = M('eaterplanet_community_head')->where( array('agent_id' => $head_id ) )->count();

		if( empty($level_count1) || $level_count1 == 0 )
		{
			$level_count1 = 0;
		}else{

			$level_sql2 =" select count(1) as count from ".C('DB_PREFIX').
						"eaterplanet_community_head  where
							agent_id in (select id from ".C('DB_PREFIX')."eaterplanet_community_head
							where agent_id ={$head_id}  )  ";

			$level_count2_arr = M()->query($level_sql2);

			$level_count2 = $level_count2_arr[0]['count'];

			if( empty($level_count2) || $level_count2 == 0 )
			{
				$level_count2 = 0;
			}else{

				$level_sqllist2 =" select id from ".C('DB_PREFIX').
						"eaterplanet_community_head  where
							agent_id in (select id from ".C('DB_PREFIX')."eaterplanet_community_head
							where agent_id ={$head_id} )  ";

				$level_list2 =   M()->query($level_sqllist2);

				if( !empty($level_list2) )
				{
					$level_arr2 = array();

					foreach( $level_list2 as $vvv )
					{
						$level_arr2[] = $vvv['id'];
					}

					$level_sql3 =" select count(1) as count from ".C('DB_PREFIX').
						"eaterplanet_community_head where  agent_id in (".implode(',',$level_arr2).") ";

					$level_count3_arr = M()->query( $level_sql3 );

					$level_count3 = $level_count3_arr[0]['count'];

					if( empty($level_count3) || $level_count3 == 0 )
					{
						$level_count3 = 0;
					}
				}
			}
		}

		$need_data = array();
		$need_data['open_community_head_leve'] = $open_community_head_leve;
		$need_data['total_money'] = $total_money;
		$need_data['wait_money'] =  sprintf("%.2f",$tixian_wait_money);
		$need_data['success_money'] = sprintf("%.2f",$tixian_success_money);
		$need_data['level_count1'] = $level_count1;
		$need_data['level_count2'] = $level_count1 + $level_count2 ;
		$need_data['level_count3'] = $level_count1 + $level_count2 + $level_count3;
		echo json_encode( array('code' => 0, 'data' => $need_data ) );
		die();
	}

	/**
		获取团长分销明细
	**/

	public function get_head_distribute_order()
	{
		$gpc = I('request.');
		$_GPC = I('request.');

		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();


		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$head_id = $community_info['id'];


		$page = isset($_GPC['page']) ? $_GPC['page']:'1';

		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;

		$where = ' and head_id = '.$head_id;


		$type = isset($_GPC['type']) ? $_GPC['type']: '';
		$level = isset($_GPC['level']) ? $_GPC['level']: 0;

		if( empty($type) )
		{
			$where .= " and type in ('commiss','tuijian')";
		}

		if( $level > 0 )
		{
			//level
			$where .= " and level=".$level;
		}
		//commiss

		$sql = "select * from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order
	                    where  1  {$where}   order by id desc limit {$offset},{$size}";

	    $list = M()->query($sql);

		$status_list = M('eaterplanet_ecommerce_order_status')->select();

		$status_arr = array();
		foreach($status_list as $vv)
		{
			$status_arr[ $vv['order_status_id'] ] = $vv['name'];
		}

		$need_list = array();
		foreach($list as $key => $val)
		{
			$val['addtime'] = date('Y-m-d H:i:s', $val['addtime']);

			$child_head = M('eaterplanet_community_head')->where( array('id' => $val['child_head_id'] ) )->find();

			$val['child_head_name'] = $child_head['head_name'];
			$val['community_name'] = $child_head['community_name'];

			if( $val['type'] == 'commiss')
			{
				$order_info = M('eaterplanet_ecommerce_order')->field('order_num_alias,order_status_id')
							  ->where( array('order_id' => $val['order_id'] ) )->find();

				$val['order_num_alias'] = $order_info['order_num_alias'];
				$val['order_status'] = $status_arr[ $order_info['order_status_id'] ];
			}

			$need_list[$key] = $val;
		}

		if( !empty($need_list) )
		{
			echo json_encode( array('code' => 0, 'data' => $need_list) );
			die();
		}else {
			echo json_encode( array('code' => 1) );
			die();
		}
	}

	//end

	public function tixian_community_info()
	{
		$gpc = I('request.');



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

		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$head_id = $community_info['id'];
		$commission_info = D('Seller/Community')->get_head_commission_info($member_id, $head_id);

		$tixian_money = $gpc['tixian_money'];


		$type = isset($gpc['type']) ? $gpc['type'] : 0;//0 沿用原来的逻辑 1余额 2 微信 3 支付宝 4 银行

		$bankname = isset($gpc['bankname']) ? $gpc['bankname'] : ''; //银行名称

		$bankaccount = isset($gpc['bankaccount']) ? $gpc['bankaccount'] : '';//卡号，支付宝账号 使用该字段

		$bankusername = isset($gpc['bankusername']) ? $gpc['bankusername'] : '';//持卡人姓名，微信名称，支付宝名称， 使用该字段

		$service_charge = D('Home/Front')->get_config_by_name('community_tixian_fee');

		if($commission_info['money'] >= $tixian_money )
		{
			$data = array();
			$data['member_id'] = $member_id;
			$data['head_id'] = $head_id;
			$data['money'] = floatval($tixian_money);
			$data['state'] = 0;

			$data['type'] = $type;
			$data['bankname'] = $bankname;
			$data['bankaccount'] = $bankaccount;
			$data['bankusername'] = $bankusername;
			$data['service_charge'] = round( ($tixian_money * $service_charge) /100,2);

			$data['shentime'] = 0;
			$data['addtime'] = time();

			M('eaterplanet_community_head_tixian_order')->add($data);

			M()->execute("update ".C('DB_PREFIX')."eaterplanet_community_head_commiss set money=money - {$tixian_money},dongmoney=dongmoney+{$tixian_money}
						where  head_id={$head_id} ");

			echo json_encode( array('code' => 0) );
			die();
		}else{

			echo json_encode( array('code'=>1) );
			die();
		}


	}


	public function sub_community_head()
	{
		$gpc = I('request.');



		$token = $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


 	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();



		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$province_name = $gpc['province_name'];
		$city_name = $gpc['city_name'];
		$area_name = $gpc['area_name'];
		$country_name = $gpc['country_name'];

		$lon_lat = $gpc['lon_lat'];

		$lon_lat_arr = explode(',', $lon_lat);
		$wechat = $gpc['wechat'];



		$province_info = D('Home/Front')->get_area_ninfo_by_name($province_name);
		$province_id = $province_info['id'];

		$city_info = D('Home/Front')->get_area_ninfo_by_name($city_name);
		$city_id = $city_info['id'];

		$area_info = D('Home/Front')->get_area_ninfo_by_name($area_name);
		$area_id = $area_info['id'];

		$country_info = D('Home/Front')->get_area_ninfo_by_name($country_name);
		$country_id = $country_info['id'];


		$addr_detail = $gpc['addr_detail'];
		$community_name = $gpc['community_name'];
		$mobile = $gpc['mobile'];
		$head_name = $gpc['head_name'];

		$community_id = isset($gpc['community_id']) && intval($gpc['community_id']) > 0 ?  $gpc['community_id'] : 0;


		$data = array();
		$data['member_id'] = $member_id;
		$data['community_name'] = $community_name;
		$data['head_name'] = $head_name;
		$data['head_mobile'] = $mobile;
		$data['province_id'] = $province_id;
		$data['city_id'] = $city_id;
		$data['country_id'] = $country_id;
		$data['area_id'] = $area_id;
		$data['address'] = $addr_detail;
		$data['lon'] = $lon_lat_arr[0];
		$data['lat'] = $lon_lat_arr[1];
		$data['state'] = 0;
		$data['apptime'] = time();
		$data['addtime'] = time();
		$data['wechat'] = $wechat;

		$head_info = D('Home/Front')->get_member_community_info($member_id);

		$parent_head_id = 0;

		if( $member_info['share_id'] > 0 )
		{
			$parent_head_info = D('Home/Front')->get_member_community_info($member_info['share_id']);
			if( !empty($parent_head_info) )
			{
				$parent_head_id = $parent_head_info['id'];
			}
		}

		$data['agent_id'] = $parent_head_id;

		if( $community_id > 0 )
		{
			$data['agent_id'] = $community_id;
		}



		if( empty($head_info) )
		{
			$head_id =  M('eaterplanet_community_head')->add($data);

		}else{
			unset($data['uniacid']);
			unset($data['addtime']);

			M('eaterplanet_community_head')->where( array('id' => $head_info['id']) )->save($data);
			$head_id = $head_info['id'];
		}


		$head_commiss_info = M('eaterplanet_community_head_commiss')->where( array('member_id' =>$member_id,'head_id' => $head_id ) )->find();

		if( empty($head_commiss_info) )
		{
			$datas = array();
			$datas['member_id'] = $member_id;

			$datas['head_id'] = $head_id;
			$datas['money'] = 0;
			$datas['dongmoney'] = 0;
			$datas['getmoney'] = 0;
			$datas['bankname'] = '';
			$datas['bankaccount'] = '';
			$datas['bankusername'] = '';
			$datas['share_avatar'] = '';
			$datas['share_wxcode'] = '';
			$datas['share_title'] = '';
			$datas['share_desc'] = '';

			M('eaterplanet_community_head_commiss')->add( $datas );

		}

		echo json_encode( array('code' => 0) );
		die();

	}

	/**
	 * 获取城市列表
	 */
	public function get_city_list()
	{
		$gpc = I('request.');


		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


 	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();


		if( empty($member_info) )
		{
			//echo json_encode( array('code' => 1) );
			//die();
		}


		$city_ids_Arr = M('eaterplanet_community_head')->field('city_id')->order('city_id asc')->select();




		$city_ids = array();
		foreach ($city_ids_Arr as $k => $val) {
			$city_ids[] = $val['city_id'];
		}

		$city_ids_str = implode(',', $city_ids);

		$city_list = M('eaterplanet_ecommerce_area')->where( array('id' => array('in', $city_ids_str) ) )->select();


		// {
		 //        "districtCode": "152900",
		 //        "districtLevel": "CITY",
		 //        "parentDistrictCode": "150000",
		 //        "districtName": "阿拉善盟",
		 //        "firstLetter": "A",
		 //        "serviceStatus": "N"
		 //    }

		$city_arr = array();
		foreach ($city_list as $key => $value) {
			$city_arr[$key]["city_id"] = $value["id"];
			$city_arr[$key]["districtCode"] = $value["code"];
			$city_arr[$key]["districtLevel"] = "CITY";
			$city_arr[$key]["parentDistrictCode"] = $value["code"];
			$city_arr[$key]["districtName"] = $value["name"];
			$city_arr[$key]["firstLetter"] = $this->getFirstCharter($value["name"]);
			$city_arr[$key]["serviceStatus"] = "N";
		}

		echo json_encode( array('code' => 0, 'data' => $city_arr) );
		die();

	}

	/**
	 * 获取首字母
	 * @param  [type] $str [description]
	 * @return [type]      [description]
	 */
	function getFirstCharter($str)
	{
	    if (empty($str)) {
	        return '';
	    }
	    $fchar = ord($str{0});
	    if ($fchar >= ord('A') && $fchar <= ord('z'))
	        return strtoupper($str{0});
	    $s1 = iconv('UTF-8', 'gb2312', $str);
	    $s2 = iconv('gb2312', 'UTF-8', $s1);
	    $s = $s2 == $str ? $s1 : $str;
	    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;

	    if ($asc >= -20319 && $asc <= -20284)
	        return 'A';

	    if ($asc >= -20283 && $asc <= -19776)
	        return 'B';

	    if ($asc >= -19775 && $asc <= -19219)
	        return 'C';

	    if ($asc >= -19218 && $asc <= -18711)
	        return 'D';

	    if ($asc >= -18710 && $asc <= -18527)
	        return 'E';

	    if ($asc >= -18526 && $asc <= -18240)
	        return 'F';

	    if ($asc >= -18239 && $asc <= -17923)
	        return 'G';

	    if ($asc >= -17922 && $asc <= -17418)
	        return 'H';

	    if ($asc >= -17417 && $asc <= -16475)
	        return 'J';

	    if ($asc >= -16474 && $asc <= -16213)
	        return 'K';

	    if ($asc >= -16212 && $asc <= -15641)
	        return 'L';

	    if ($asc >= -15640 && $asc <= -15166)
	        return 'M';

	    if ($asc >= -15165 && $asc <= -14923)
	        return 'N';

	    if ($asc >= -14922 && $asc <= -14915)
	        return 'O';

	    if ($asc >= -14914 && $asc <= -14631)
	        return 'P';

	    if ($asc >= -14630 && $asc <= -14150)
	        return 'Q';

	    if ($asc >= -14149 && $asc <= -14091)
	        return 'R';

	    if ($asc >= -14090 && $asc <= -13319)
	        return 'S';

	    if ($asc >= -13318 && $asc <= -12839)
	        return 'T';

	    if ($asc >= -12838 && $asc <= -12557)
	        return 'W';

	    if ($asc >= -12556 && $asc <= -11848)
	        return 'X';

	    if ($asc >= -11847 && $asc <= -11056)
	        return 'Y';

	    if ($asc >= -11055 && $asc <= -10247)
	        return 'Z';

	    return null;

	}

	/**
	 * 获取申请页面
	 */
	public function get_apply_page()
	{

		$info = M('eaterplanet_ecommerce_config')->field( 'value' )->where( array('name' => 'communityhead_apply_page') )->find();


	    if(!empty($info['value'])){
	    	echo json_encode( array('code' => 0, 'data' => htmlspecialchars_decode(htmlspecialchars_decode($info['value']))) );
			die();
	    }else{
	    	echo json_encode( array('code' => 1 ));
			die();
	    }
	}


	public function check_head_subscriptapply()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

	    $member_id = $weprogram_token['member_id'];

		$is_need_subscript = 0;
		$need_subscript_template = array();

		//'pay_order','send_order','hexiao_success','apply_community','open_tuan','take_tuan','pin_tuansuccess','apply_tixian'


			$apply_community_info = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $member_id, 'type' => 'apply_community' ) )->find();

			if( empty($apply_community_info) )
			{
				$weprogram_subtemplate_apply_community = D('Home/Front')->get_config_by_name('weprogram_subtemplate_apply_community');

				if( !empty($weprogram_subtemplate_apply_community) )
				{
					$need_subscript_template['apply_community'] = $weprogram_subtemplate_apply_community;
				}
			}

			$apply_tixian_info = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $member_id, 'type' => 'apply_tixian' ) )->find();

			if( empty($apply_tixian_info) )
			{
				$weprogram_subtemplate_apply_tixian = D('Home/Front')->get_config_by_name('weprogram_subtemplate_apply_tixian');

				if( !empty($weprogram_subtemplate_apply_tixian) )
				{
					$need_subscript_template['apply_tixian'] = $weprogram_subtemplate_apply_tixian;
				}
			}

			if( !empty($need_subscript_template) )
			{
				$is_need_subscript = 1;
			}



		echo json_encode( array('code' => 0, 'is_need_subscript' => $is_need_subscript,'need_subscript_template' => $need_subscript_template ) );
		die();

	}


	//----------begin ----------------

	/**
	 * 团长设置页面团长资料
	 */
	public function get_head_info()
	{
		$_GPC = I('request.');


		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


 	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();


		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}



		$id = $_GPC['id'];

		if($id > 0)
		{

			$item = M('eaterplanet_community_head')->where( array('id' => $id ) )->find();

		    $item['province_name'] = D('Seller/Area')->get_area_info($item['province_id']);
		    $item['city_name'] = D('Seller/Area')->get_area_info($item['city_id']);
		    $item['area_name'] = D('Seller/Area')->get_area_info($item['area_id']);
		    $item['country_name'] = D('Seller/Area')->get_area_info($item['country_id']);

			if( $item['member_id'] > 0)
			{
				$head_commiss_info = M('eaterplanet_community_head_commiss')->where( array('head_id' => $item['id'],'member_id' =>$item['member_id'] ) )->find();

				if( !empty($head_commiss_info) )
				{
					$item['bankname'] = $head_commiss_info['bankname'];
					$item['bankaccount'] = $head_commiss_info['bankaccount'];
					$item['bankusername'] = $head_commiss_info['bankusername'];
					$item['share_wxcode'] = tomedia($head_commiss_info['share_wxcode']);
				}
			}
			$item['member_info'] = $member_info;
			$item['rest'] = D('Seller/Communityhead')->is_community_rest($id);
			$item['re_id'] = sprintf("%05d", $item['id']);


			$delivery_type_tuanz  = D('Home/Front')->get_config_by_name('delivery_type_tuanz');

			$close_community_reset_btn  = D('Home/Front')->get_config_by_name('close_community_reset_btn');


			if(empty($delivery_type_tuanz))
			{
				$delivery_type_tuanz = 2;
			}
			$item['delivery_type_tuanz'] = $delivery_type_tuanz;
			$item['close_reset_btn'] = $close_community_reset_btn;

			if(!empty($item)){
		    	echo json_encode( array('code' => 0, 'data' => $item) );
				die();
		    }else{
		    	// 无数据
		    	echo json_encode( array('code' => 2 ));
				die();
		    }
		}
	}

	/**
	 * 团长设置休息状态
	 */
	public function set_head_rest()
	{
		$_GPC = I('request.');


		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


 	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();


		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}


		$head_id = $_GPC['id'];
		$rest = $_GPC['rest'];

		if($head_id > 0){

			$result = M('eaterplanet_community_head')->where( array('id' => $head_id) )->save( array('rest' => $rest) );
			if(!empty($result)){
				echo json_encode( array('code' => 0, 'data' => '修改成功'));
				die();
			}else{
				echo json_encode( array('code' => 2, 'data' => '修改失败'));
				die();
			}
		}else{
	    	// id不存在
	    	echo json_encode( array('code' => 2, 'data' => 'id不存在' ));
			die();
	    }
	}

	/**
	 * 团长信息修改
	 */
	public function modify_head_info()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

 	    $member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$head_id = isset($_GPC['id']) ? $_GPC['id'] : 0;

	    $data = array();
	    $data['head_name'] = $_GPC['head_name'];
	    $data['head_mobile'] = $_GPC['head_mobile'];
		$data['is_modify_shipping_method'] = $_GPC['is_modify_shipping_method'];//是否自定义配送开关。0跟随系统，1开启，2关闭
	    $data['is_modify_shipping_fare'] = $_GPC['is_modify_shipping_fare'];//是否自定义配送费，0跟随系统，1自定义
	    $data['shipping_fare'] = $_GPC['shipping_fare'];// 自定义配送费


	    if($head_id > 0){



			$rs = M('eaterplanet_community_head')->where( array('id' => $head_id, 'member_id' => $member_id) )->save( $data );


			$commiss_data = array();
	    	$commiss_data['share_wxcode'] = $_GPC['share_wxcode'];
	    	if($commiss_data['share_wxcode']){

				$rs = M('eaterplanet_community_head_commiss')->where( array('head_id' => $head_id, 'member_id' => $member_id) )->save( $commiss_data );
    		}

			if(!empty($rs)){
				echo json_encode( array('code' => 0, 'data' => '修改成功'));
				die();
			}else{
				echo json_encode( array('code' => 2, 'data' => '修改失败'));
				die();
			}
		}else{
	    	// id不存在
	    	echo json_encode( array('code' => 2, 'data' => 'id不存在' ));
			die();
	    }
	}

	/**
	 * 团长信息修改
	 */
	public function modify_head_commiss()
	{
		$_GPC = I('request.');

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

 	    $member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$head_id = isset($_GPC['id']) ? $_GPC['id'] : 0;

	    $data = array();
	    $data['bankname'] = $_GPC['bankname'];
	    $data['bankaccount'] = $_GPC['bankaccount'];
	    $data['bankusername'] = $_GPC['bankusername'];
	    // $data['address'] = $_GPC['address'];
	    // $data['lon'] = $_GPC['lon'];
	    // $data['lat'] = $_GPC['lat'];
	    // $data['state'] = $_GPC['state'];
	    if($head_id > 0){

			$rs = M('eaterplanet_community_head_commiss')->where( array('head_id' => $head_id, 'member_id' => $member_id) )->save( $data );

			if(!empty($rs)){
				echo json_encode( array('code' => 0, 'data' => '修改成功'));
				die();
			}else{
				echo json_encode( array('code' => 2, 'data' => '修改失败'));
				die();
			}
		}else{
	    	// id不存在
	    	echo json_encode( array('code' => 2, 'data' => 'id不存在' ));
			die();
	    }
	}


	public function community_index_shareqrcode()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			$member_id = 0;
		}else{
			$member_id = $weprogram_token['member_id'];
		}

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$community_info = D('Home/Front')->get_member_community_info($member_id);
		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$community_id = $community_info['id'];

		$community_index_shareqrcode_json = D('Home/Front')->get_config_by_name('community_index_shareqrcode_'.$community_id );
		$community_index_shareqrcode_arr = unserialize($community_index_shareqrcode_json);

		$load_new = false;
		if( empty($community_index_shareqrcode_arr) )
		{
			$load_new = true;
		}else {
			if( $community_index_shareqrcode_arr['endtime'] < time() )
			{
				$load_new = true;
			}
		}

		if( $load_new || true )
		{
			$goods_model = D('Home/Pingoods');
			$qrcode_image = $goods_model->_get_index_wxqrcode($member_id,$community_id,'jpg');

			$data = array();
			$data['image_path']  = '/'.$qrcode_image;
			$ed_time = time() + 300;
			$js_arr = array('endtime' => $ed_time,'image_path' => $data['image_path'] );

			$cd_key = 'community_index_shareqrcode_'.$community_id;
			D('Seller/Config')->update( array( $cd_key => serialize($js_arr) ) );
		}else{
			$data = array();
			$data['image_path']  ='/'.$community_index_shareqrcode_arr['image_path'];
		}

		$shop_domain = D('Home/Front')->get_config_by_name('shop_domain');

		$data['image_path'] = $shop_domain.$data['image_path'];
		$result = array('code' => 0, 'qrcode' => $data['image_path'] );
		echo json_encode($result);
		die();

	}

	/**
	 * 团长排行榜
	 */
	public function community_ranking_list(){
		$_GPC = I('request.');
		$token =  $_GPC['token'];
		$type = $_GPC['type'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			$member_id = 0;
		}else{
			$member_id = $weprogram_token['member_id'];
		}
		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();
		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'mgs'=> '请登录') );
			die();
		}
		$community_info = D('Home/Front')->get_member_community_info($member_id);
		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1, 'mgs'=> '您不是团长，无法查看') );
			die();
		}
		$begin_time = 0;
		$end_time = 0;
		if($type == 1){//今日
			$begin_time = strtotime(date('Y-m-d'.'00:00:00',time()));
			$end_time = strtotime(date('Y-m-d'.'00:00:00',time()+3600*24));
		}else if($type == 2){//昨日
			$begin_time = strtotime(date('Y-m-d'.'00:00:00',time()-3600*24));
			$end_time = strtotime(date('Y-m-d'.'00:00:00',time()));
		}else if($type == 3){//上周
			$begin_time = strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'))));
			$end_time = strtotime(date("Y-m-d H:i:s",mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'))));
		}else if($type == 4){//上月
			//上月开始时间
			$begin_time = strtotime(date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m")-1,1,date("Y"))));
			//上月结束时间
			$end_time = strtotime(date("Y-m-d H:i:s",mktime(23,59,59,date("m") ,0,date("Y"))));
		}
		$list = array();
		$sql = " select * from ( "
			 . " select co.head_id,count(distinct(co.order_id)) as order_count, "
			 . " sum(co.money) as money "
			 . " from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co "
			 . " left join ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o "
			 . " on co.order_id = o.order_id "
			 . " where co.addtime >= ".$begin_time." and co.addtime <= ".$end_time
			 . " and o.order_status_id not in (3,5,7,12) and co.state != 2 "
			 . " group by co.head_id) t "
			 . " order by money desc "
			 . " limit 20 ";
		//echo $sql;
		$list = M()->query($sql);
		foreach($list as $k=>$v){
			$community_head = M('eaterplanet_community_head')->where( array('id' => $v['head_id']) )->find();

			$list[$k]['community_name'] = $community_head['community_name'];

			$member_info = M('eaterplanet_ecommerce_member')->field('avatar')->where( array('member_id' => $community_head['member_id']) )->find();

			$list[$k]['avatar'] = $member_info['avatar'];
		}

        $is_show_community_ranking = D('Home/Front')->get_config_by_name('is_show_community_ranking');
        //是否显示团长排行， 0不显示，1显示
        $is_show_community_ranking = empty($is_show_community_ranking) ? 0 : $is_show_community_ranking;


		echo json_encode(array('code' => 0,'data'=>$list , 'is_show_community_ranking' => $is_show_community_ranking ));
		die();
	}
    //----------end----------------------
}
