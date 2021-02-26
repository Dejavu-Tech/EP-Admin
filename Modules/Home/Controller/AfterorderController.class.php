<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      http://www.ch871.com/
 * @copyright Copyright (c) 2019-2021 ch871.com.
 * @license   http://www.ch871.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Home\Controller;


class AfterorderController extends CommonController {

	public function get_order_money()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();


		if( empty($member_id) )
		{

			$result['code'] = 0;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}

		$order_id =  $_GPC['order_id'];
		$order_goods_id =  $_GPC['order_goods_id'];

		$ref_id = isset($_GPC['ref_id']) && $_GPC['ref_id'] > 0 ? intval($_GPC['ref_id']) : '';

		//total  eaterplanet_ecommerce_order

		//shipping_name  shipping_tel
		$total = 0;


		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id , 'member_id' => $member_id ) )->find();

		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$order_id,'order_goods_id' =>$order_goods_id  ) )->find();

		$order_goods['total_score'] = intval($order_goods['total']);

		$is_has_refund_deliveryfree = D('Home/Front')->get_config_by_name('is_has_refund_deliveryfree');

		$is_has_refund_deliveryfree = !isset($is_has_refund_deliveryfree) || $is_has_refund_deliveryfree == 1 ? 1:0;

		if( $is_has_refund_deliveryfree == 1 )
		{
			$ref_shipping_fare_old = M('eaterplanet_ecommerce_order_refund')->where( array('order_goods_id' => $order_goods_id,'state' => 3 ) )->sum('ref_shipping_fare');
			$ref_shipping_fare_new = $order_goods['shipping_fare'] - $ref_shipping_fare_old ;

			$order_goods['total'] = $order_goods['total'] + $ref_shipping_fare_new - $order_goods['voucher_credit']- $order_goods['fullreduction_money'] - $order_goods['score_for_money'];
		}else{

			$order_goods['total'] = $order_goods['total'] - $order_goods['voucher_credit']- $order_goods['fullreduction_money'] - $order_goods['score_for_money'];
		}

		$total = $order_goods['total'];



		$order_option_info = M('eaterplanet_ecommerce_order_option')->field('value')->where( array('order_id' =>$order_id,'order_goods_id' => $order_goods_id ) )->select();

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

		//$goods_images = resize($order_goods['goods_images'],400);
		$goods_images = $order_goods['goods_images'];

		if( !is_array($goods_images) )
		{
			$order_goods['image']=  tomedia( $goods_images );
			$order_goods['goods_images']= tomedia( $goods_images );
		}else{
			$order_goods['image']=  $order_goods['goods_images'];
		}


		$integral_flow = M('eaterplanet_ecommerce_member_integral_flow')->where( array('type' => 'orderbuy', 'order_goods_id' => $order_goods_id ) )->find();

		$use_score = 0;

		if( !empty($integral_flow) )
		{
			$use_score = $integral_flow['score'];
		}


		$one_goods_score = intval($use_score / $order_goods['quantity'] );


		$refund_integral_flow = M('eaterplanet_ecommerce_member_integral_flow')->where( array('type' => 'refundorder', 'order_goods_id' => $order_goods_id ) )->sum('score');

		if( $refund_integral_flow > 0 )
		{
			$use_score = $use_score - $refund_integral_flow;
		}

		$order_goods['use_score'] = $use_score;
		$order_goods['one_goods_score'] = $one_goods_score;
		$order_goods['type'] = $order_info['type'];


		$shipping_name = $order_info['shipping_name'];
		$shipping_tel = $order_info['shipping_tel'];

		//商品图片 ref_id
		$refund_info = array();
		$refund_image = array();

		if( !empty($ref_id) && $ref_id > 0  )
		{
			$refund_info = M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id ) )->find();

			$order_refund_image = M('eaterplanet_ecommerce_order_refund_image')->where( array('ref_id' =>$order_refund['ref_id'] ) )->select();

			if(!empty($order_refund_image))
			{

				foreach($order_refund_image as $key => $refund_image)
				{
					$refund_image['thumb_image'] =  tomedia( resize($refund_image['image'], 200) );

					$order_refund_image[$key] = $refund_image;

				}
			}
		}


		echo json_encode( array('code' =>1, 'total' => $total,'is_has_refund_deliveryfree' => $is_has_refund_deliveryfree,'refund_info' => $refund_info,'refund_image' => $refund_image,'order_goods' => $order_goods ,'shipping_name' => $shipping_name,'shipping_tel' => $shipping_tel, 'order_status_id'=>$order_info['order_status_id']) );

		die();
	}

	public function refund_sub()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			$result['code'] = 0;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();
		}

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();


	    $member_id = $weprogram_token['member_id'];

		if( empty($member_id) )
		{
			$result['code'] = 0;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();
		}

		$data = array();
		$data['order_id'] = $_GPC['order_id'];//订单号

		if(isset($_GPC['ref_id']) && $_GPC['ref_id'] > 0)
		{
			$data['ref_id'] =  isset($_GPC['ref_id']) ? $_GPC['ref_id'] : 0 ;//如果是退款申请修改，那么有此字段，这个是退款的编号
		}

		$data['complaint_type'] = $_GPC['complaint_type'];//1 退款，2退货
		$data['complaint_images'] = $_GPC['complaint_images'];//退款的图片
		$data['complaint_desc'] = $_GPC['complaint_desc'];//商品问题描述
		$data['complaint_mobile'] = $_GPC['complaint_mobile'];//联系人手机号
		$data['complaint_name'] = $_GPC['complaint_name'];//退款的联系人

		$data['complaint_reason'] = $_GPC['complaint_reason'];//问题类型
		$data['complaint_money'] = $_GPC['complaint_money'];//退款金额

		$is_has_refund_deliveryfree = D('Home/Front')->get_config_by_name('is_has_refund_deliveryfree');

		$is_has_refund_deliveryfree = !isset($is_has_refund_deliveryfree) || $is_has_refund_deliveryfree == 1 ? 1:0;


		$data['complaint_shipping_fare'] = $_GPC['complaint_shipping_fare'];//退款金额

		if( $is_has_refund_deliveryfree == 0 )
		{
		    $data['complaint_shipping_fare'] = 0;
		}

		if( !empty($data['complaint_images']) )
		{
			$data['complaint_images'] = explode(',', $data['complaint_images']);
		}

		$order_id = $data['order_id'];

		$order_goods_id = $_GPC['order_goods_id'];//子订单号


		$real_refund_quantity = isset($_GPC['real_refund_quantity']) ? $_GPC['real_refund_quantity'] : 1;



		$order_info = M('eaterplanet_ecommerce_order')->where( array('member_id' => $member_id, 'order_id' => $order_id ) )->find();

		if(empty($order_info) )
		{
			$result['code'] = 0;

	        $result['msg'] = '没有此订单';

	        echo json_encode($result);

	        die();
		}
		//确认收货后才能退款
		if($order_info['order_status_id'] < 6)
		{
			$result['code'] = 0;

			$result['msg'] = '收货后才能售后';

			echo json_encode($result);

			die();
		}

		$result = array('code' => 0);



		$refdata = array();

		$refdata['order_id'] = intval($data['order_id']);


		$refdata['ref_type'] = intval($data['complaint_type']);

		$refdata['ref_money'] = floatval($data['complaint_money']);

		//子订单商品数
		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$order_id,'order_goods_id' =>$order_goods_id  ) )->find();

		//子订单最后一个商品
		if( $real_refund_quantity + $order_goods['has_refund_quantity'] == $order_goods['quantity'] ){

			$ref_shipping_fare_old = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_goods_id' => $order_goods_id ) )->sum('refund_shipping_fare');

			$refdata['ref_shipping_fare'] = floatval( $order_goods['fenbi_li']*$order_goods['shipping_fare'] - $ref_shipping_fare_old );


		}else{
			$refdata['ref_shipping_fare'] = floatval(  $real_refund_quantity/$order_goods['quantity'] * $data['complaint_shipping_fare'] * $order_goods['fenbi_li'] );

		}

		$refdata['ref_member_id'] = $member_id;

		$refdata['ref_name'] = htmlspecialchars($data['complaint_reason']);

		$refdata['ref_mobile'] = htmlspecialchars($data['complaint_mobile']);

		$refdata['ref_description'] = htmlspecialchars($data['complaint_desc']);
		$refdata['complaint_name'] = htmlspecialchars($data['complaint_name']);

		$refdata['state'] = 0;

		$refdata['real_refund_quantity'] = $real_refund_quantity;

		$refdata['addtime'] = time();

		//complaint_name

		$order_info['total'] = round($order_info['total'],2)< 0.01 ? '0.00':round($order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'],2)	;

		if( !empty($order_goods_id) && $order_goods_id > 0 )
		{
			$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id ) )->find();

			$tp_total = round($order_goods_info['total'],2)< 0.01 ? '0.00':round($order_goods_info['total']+$order_goods_info['shipping_fare']-$order_goods_info['voucher_credit']-$order_goods_info['score_for_money']-$order_goods_info['fullreduction_money'],2)	;

			$order_info['total'] = $tp_total;
		}

		if( $refdata['ref_money'] <=0 )
		{
			//$result['msg'] = '退款金额不能为0';

			//echo json_encode($result);

			//die();
		}

		if($order_info['total'] < 0)
		{
			$order_info['total'] = '0.00';
		}

		if($refdata['ref_money'] > $order_info['total'])
		{

			$result['msg'] = '退款金额不能大于订单总额';

			echo json_encode($result);

			die();

		}

		if(!empty($data['ref_id']))
		{
			$ref_id = intval($data['ref_id']);

			unset($refdata['order_id']);

			unset($refdata['ref_member_id']);

			unset($refdata['addtime']);

			M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id) )->save( $refdata );

			$order_history = array();

			//$order_history['ref_id'] = $ref_id;
			$order_history['order_id'] = $order_id;

			$order_history['order_status_id'] = $order_info['order_status_id'];

			$order_history['notify'] = 0;

			$order_history['comment'] = '用户修改退款资料';

			$order_history['date_added']=time();

			//pdo_insert('eaterplanet_ecommerce_order_history', $order_history);
			//记录日志

			$order_refund_history = array();

			$order_refund_history['ref_id'] = $ref_id;

			$order_refund_history['order_id'] = $order_id;
			$order_refund_history['order_goods_id'] = $order_goods_id;


			$order_refund_history['message'] = '用户修改退款资料';

			$order_refund_history['type'] = 1;

			$order_refund_history['addtime'] = time();

			$orh_id = M('eaterplanet_ecommerce_order_refund_history')->add( $order_refund_history );

			if(!empty($data['complaint_images']))
			{

				foreach($data['complaint_images'] as $complaint_images)
				{

					$img_data = array();

					$img_data['orh_id'] = $orh_id;

					$img_data['image'] = htmlspecialchars($complaint_images);

					$img_data['addtime'] = time();

					M('eaterplanet_ecommerce_order_refund_history_image')->add( $img_data );

				}

			}

		}else {

			//real_refund_quantity

			//$refdata['real_refund_quantity'] = $real_refund_quantity;

			//申请中的退款数量

			$has_refund_quantity = $order_goods_info['has_refund_quantity'];

			$del_can_refund_quantity = $order_goods_info['quantity'] - $has_refund_quantity;

			if( $real_refund_quantity <= 0 )
			{
				$real_refund_quantity = $del_can_refund_quantity;
				$refdata['real_refund_quantity'] = $real_refund_quantity;
			}

			$refdata['order_goods_id'] = $order_goods_id;

			//store_id
			$refdata['store_id'] = $order_info['store_id'];

			$refdata['head_id'] = $order_info['head_id'];

			$ref_id = M('eaterplanet_ecommerce_order_refund')->add($refdata);


			$order_refund_history = array();

			$order_refund_history['ref_id'] = $ref_id;

			$order_refund_history['order_id'] = $order_id;
			$order_refund_history['order_goods_id'] = $order_goods_id;

			$order_refund_history['message'] = '已经提交退款申请，待平台审核';

			$order_refund_history['type'] = 1;

			$order_refund_history['addtime'] = time();

			$orh_id = M('eaterplanet_ecommerce_order_refund_history')->add($order_refund_history);


			$can_refund_order = true;

			if( !empty($order_goods_id) && $order_goods_id > 0 )
			{
				M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id  ) )->save( array('is_refund_state' => 1) );

				//判断是否全部都退款了

				$gdall = M('eaterplanet_ecommerce_order_goods')->field('is_refund_state')->where( array('order_id' => $order_id ) )->select();

				foreach( $gdall as $vv )
				{
					if( $vv['is_refund_state'] == 0 )
					{
						$can_refund_order = false;
						break;
					}
				}


				if( $real_refund_quantity != $del_can_refund_quantity )
				{
					$can_refund_order = false;
				}


			}


			/**
				判断是否所有订单都在退款中
			**/
			if($can_refund_order)
			{
				$up_order = array();

				$up_order['order_status_id'] = 12;
				$up_order['last_refund_order_status_id'] = $order_info['order_status_id'];

				M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->save( $up_order );
			}

			$order_history = array();

			$order_history['order_id'] = $order_id;
			$order_history['order_goods_id'] = $order_goods_id;

			$order_history['order_status_id'] = 12;

			$order_history['notify'] = 0;

			$order_history['comment'] = '用户申请退款中，申请退款'.$real_refund_quantity.'个';

			$order_history['date_added']=time();

			M('eaterplanet_ecommerce_order_history')->add( $order_history );

			if(!empty($data['complaint_images']))
			{
				//complaint_images
				foreach($data['complaint_images'] as $complaint_images)
				{
					$img_data = array();
					$img_data['ref_id'] = $ref_id;
					$img_data['uniacid'] = $_W['uniacid'];

					$img_data['image'] = htmlspecialchars($complaint_images);

					$img_data['addtime'] = time();

					M('eaterplanet_ecommerce_order_refund_image')->add( $img_data );
				}
			}

			//发送消息给管理员，有人申请退款了

			$platform_send_info_member_id = D('Home/Front')->get_config_by_name('platform_send_info_member');
			$weixin_template_apply_refund = D('Home/Front')->get_config_by_name('weixin_template_apply_refund');

			if( !empty($weixin_template_apply_refund) && !empty($platform_send_info_member_id) )
			{
				$weixin_template_order =array();
				$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid' );


				if( !empty($weixin_appid) && !empty($weixin_template_apply_refund) )
				{
					$head_pathinfo = "eaterplanet_ecommerce/pages/index/index";

					//$weopenid = M('eaterplanet_ecommerce_member')->where( array('member_id' => $platform_send_info_member_id ) )->find();
                    //dump($weopenid);die;
					$sql = "select * from dejavutech_eaterplanet_ecommerce_member where member_id in ( ".$platform_send_info_member_id.")";
					$we_openid = M()->query($sql);
					foreach($we_openid as $k=>$v){


					$weixin_template_order = array(
											'appid' => $weixin_appid,
											'template_id' => $weixin_template_apply_refund,
											'pagepath' => $head_pathinfo,
											'data' => array(
															'first' => array('value' => '您好，您收到了一个顾客退款申请订单，请尽快处理','color' => '#030303'),
															'keyword1' => array('value' =>$member_info['username'] ,'color' => '#030303'),//顾客信息
															'keyword2' => array('value' => $order_info['shipping_tel'],'color' => '#030303'),//联系方式
															'keyword3' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),
															'keyword4' => array('value' => sprintf("%01.2f", $order_info['total']),'color' => '#030303'),
															'keyword5' => array('value' => htmlspecialchars($data['complaint_reason']),'color' => '#030303'),

															'remark' => array('value' => '请在48小时内响应顾客的售后申请，请尽快处理','color' => '#030303'),
															)
									);
					D('Seller/User')->just_send_wxtemplate($v['we_openid'], 0, $weixin_template_order );

					}


				}
			}

		}

		$result['code'] = 1;

		echo json_encode($result);

		die();

	}


	public function test(){
		$platform_send_info_member_id = D('Home/Front')->get_config_by_name('platform_send_info_member');
		$sql = "select * from dejavutech_eaterplanet_ecommerce_member where member_id in (".$platform_send_info_member_id.")";
		$we_openid = M()->query($sql);
		dump($we_openid);
	}


	public function refunddetail()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

		if( empty($member_id) )
		{
			$result['code'] = 0;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();
		}

		$ref_id =  $_GPC['ref_id'];

		$order_refund = M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id ) )->find();

		$order_id =  $order_refund['order_id'];

		$order_info = M('eaterplanet_ecommerce_order')->where( array('member_id' => $member_id, 'order_id' =>$order_id ) )->find();

		if(empty($order_info) )
		{

			$result['code'] = 0;
	        $result['msg'] = '无此订单';
	        echo json_encode($result);
	        die();
		}


		if($order_refund['order_goods_id'] > 0)
		{
			$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' =>$order_refund['order_goods_id'],'order_id' => $order_id ) )->find();

		}else{

			$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->find();

		}

		$total = 0;

		$total = $order_goods['total'] + $order_goods['shipping_fare']- $order_goods['voucher_credit']- $order_goods['fullreduction_money'] - $order_goods['score_for_money'];

		$order_goods['total'] = $total;

		$order_option_info = M('eaterplanet_ecommerce_order_option')->field('value')->where( array('order_id' => $order_id ,'order_goods_id' => $order_refund['order_goods_id'] ) )->select();


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

		$goods_images = $order_goods['goods_images'];

		if( !is_array($goods_images) )
		{
			$order_goods['image']=  tomedia( $goods_images );
			$order_goods['goods_images']= tomedia( $goods_images );
		}else{
			$order_goods['image']=  $order_goods['goods_images'];
		}



		$order_refund['ref_type'] = $order_refund['ref_type'] ==1 ? '退款': '退款退货';

		$refund_state = array(

							0 => '申请中',

							1 => '商家拒绝',

							2 => '平台介入',

							3 => '退款成功',

							4 => '退款失败',

							5 => '撤销申请',
						);

		$order_refund['state_str'] = $refund_state[$order_refund['state']];

		$order_refund['addtime']  = date('Y-m-d H:i:s', $order_refund['addtime']);

		$order_refund_image = M('eaterplanet_ecommerce_order_refund_image')->where( array('ref_id' => $order_refund['ref_id'] ) )->select();

		$refund_images = array();

		if(!empty($order_refund_image))
		{

			foreach($order_refund_image as $refund_image)
			{

				$refund_image['thumb_image'] =  $refund_image['image'];

				$refund_images[] = $refund_image;

			}
		}

		if($order_refund['order_goods_id'] > 0)
		{
			$order_refund_historylist = M('eaterplanet_ecommerce_order_refund_history')->where( array('order_id' => $order_id,'order_goods_id' => $order_refund['order_goods_id'] ) )->order('addtime asc')->select();

		}else{

			$order_refund_historylist = M('eaterplanet_ecommerce_order_refund_history')->where( array('order_id' => $order_id) )->order('addtime asc')->select();
		}

		foreach($order_refund_historylist as $key => $val)
		{

			$val['addtime'] = date('Y-m-d H:i:s', $val['addtime']);

			$order_refund_historylist[$key] = $val;

		}



		echo json_encode( array('code' => 1,'order_refund' =>$order_refund, 'order_id' => $order_id ,'order_refund_historylist' => $order_refund_historylist, 'refund_images' => $refund_images,'order_goods' => $order_goods ,'order_info' => $order_info) );

		die();

	}


}
