<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.cloud/
 * @copyright Copyright (c) 2019-2024 Dejavu Tech.
 * @license   https://github.com/Dejavu-Tech/EP-Admin/blob/main/LICENSE
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Home\Controller;
use Home\Model\OrderModel;
class UserController extends CommonController {

	protected function _initialize(){
		parent::_initialize();
	}

	public function set_default_address()
	{
		$gpc = I('request.');

		$token =  $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$id = $gpc['id'];//I('get.id', 0);


		M('ims_eaterplanet_ecommerce_address')->where( array('member_id' => $member_id ) )->save( $up_data );


		$up_data = array();
		$up_data['is_default'] = 1;

		M('ims_eaterplanet_ecommerce_address')->where( array('address_id' => $id ) )->save( $up_data );


		echo json_encode( array('code' => 0) );
		die();

	}

	public function group_orders()
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

		$type = isset($_GPC['type']) ? $_GPC['type']:'0';

		$page = isset($_GPC['page']) ? $_GPC['page']:'1';//当前第几页

		$pre_page = isset($_GPC['pre_page']) ? $_GPC['pre_page']:'10';//每页数量

	    $offset = ($page -1) * $pre_page;

	    $where = ' ';

	    if($type == 1)
	    {
	        $where .= '  and p.state = 0 and p.end_time >'.time();
	    } else if($type == 2){
	        $where .= ' and p.state = 1 ';
	    } else if($type == 3){
	        $where .= ' and (o.order_status_id != 5 && o.order_status_id != 3) and (p.state = 2 or  (p.state =0 and p.end_time <'.time().')) ';

	    }else if($type == 0){

			//$where .= ' and o.order_status_id != 3 ';

		}



	    $sql = "select og.goods_id as goods_id, og.name as name,og.goods_images,g.productprice as orign_price,o.voucher_credit,os.name as status_name,os.order_status_id,og.quantity,og.order_goods_id,p.need_count,p.state,p.is_lottery,p.lottery_state,p.end_time,o.delivery,o.lottery_win,o.total,o.shipping_fare,o.order_id,o.store_id,og.price,po.pin_id,o.order_status_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o, ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og,

	        ".C('DB_PREFIX')."eaterplanet_ecommerce_pin as p,".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g ,".C('DB_PREFIX')."eaterplanet_ecommerce_order_status as os ,".C('DB_PREFIX')."eaterplanet_ecommerce_pin_order as po

	            where  po.order_id = o.order_id and o.order_status_id = os.order_status_id and  o.order_id = og.order_id and og.goods_id = g.id and po.pin_id = p.pin_id

	            and o.member_id = ".$member_id."  {$where} order by o.date_added desc limit {$offset},{$pre_page}";

	    $list = M()->query($sql);  //M()->query($sql);


	    foreach($list as $key => $val)
	    {

	        $val['price'] = round($val['price'],2);

			//delivery

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



			}else if($val['order_status_id'] == 10)
			{
				$val['status_name'] = '等待退款';

			}else if($val['order_status_id'] == 1 && $val['type'] == 'lottery')
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



			$pin_type = $val['state'];



			if($pin_type == 0 && $val['end_time'] <= time() )

			{

				$pin_type = 2;

			}



			switch($pin_type)

			{

				case 0:

					if($val['order_status_id'] == 2)

					{

						$val['status_name']  = $val['status_name'];

					}else{

						$val['status_name']  = '拼团中，'.$val['status_name'];

					}



					break;

				case 1:

				//7

					if($val['order_status_id'] == 7)

					{

						$val['status_name']  = '拼团成功，售后已退款';

					}else if($val['order_status_id'] == 1)

					{

						$val['status_name']  = '拼团成功，待发货';

					}

					else{

						$val['status_name']  = '拼团成功，'.$val['status_name'];

					}



					break;

				case 2:

					$val['status_name']  = '拼团失败，'.$val['status_name'];

					//order_status_id 2

					if($val['order_status_id'] == 2)

					{

						$val['status_name']  = '拼团失败';

					}

					break;

			}

	        $val['goods_images'] = tomedia( $val['goods_images'] );

			$order_option_list = M('eaterplanet_ecommerce_order_option')->where( array('order_goods_id' => $val['order_goods_id'] ) )->select();


			foreach($order_option_list as $option)

			{

				$val['option_str'][] = $option['value'];

			}

			if( !isset($val['option_str']) )

			{

				$val['option_str'] = '';

			}else{

				$val['option_str'] = implode(',', $val['option_str']);

			}


			$store_info	= array('s_true_name' =>'','s_logo' => '');

			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');

			$store_info['s_logo'] = D('Home/Front')->get_config_by_name('shoplogo');

			$store_info['s_logo'] = tomedia($store_info['s_logo']);


			//$val['store_info'] = $store_info;

			//order_id pin_id

			$first_tuan = M('eaterplanet_ecommerce_pin_order')->where( array('pin_id' => $val['pin_id'] ) )->order('id asc')->find();

			if( $first_tuan['order_id'] == $val['order_id'] )
			{
				$val['me_is_head'] = 1;
			}else{
				$val['me_is_head'] = 0;
			}


			 $val['price'] = round($val['price'],2);




			if($val['delivery'] == 'pickup')

			{

				$val['total'] = round($val['total'],2) - round($val['voucher_credit'],2);



			}else{

				$val['total'] = round($val['total'],2)+round($val['shipping_fare'],2) - round($val['voucher_credit'],2);


			}

			if($val['shipping_fare']<=0.001 || $val['delivery'] == 'pickup')

			{

				$val['shipping_fare'] = '免运费';

			}else{

				$val['shipping_fare'] = '运费:'.$val['shipping_fare'];

			}
	         $val['orign_price'] = round($val['orign_price'],2);



	        if($val['state'] == 0 && $val['end_time'] < time())

	        {

	            $val['state'] = 2;

	        }

	        $list[$key] = $val;

	    }



		$need_data = array();

		$need_data['code'] = 1;

		if( !empty($list) )

		{

			$need_data['code'] = 0;

			$need_data['data'] = $list;

		}

		echo json_encode($need_data);

		die();

	}


	public function sub_user_shop()
	{
		$gpc = I('request.');

		$token =  $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$id = $gpc['id'];


		$data = array();
		$data['member_id'] = $member_id;
		$data['shop_name'] = $gpc['head_name'];
		$data['shop_mobile'] = $gpc['head_mobile'];
		$data['imgGroup'] = $gpc['imgGroup'];
		$data['otherImgGroup'] = $gpc['otherImgGroup'];
		$data['state'] = 0;
		$data['addtime'] = time();



		//------
		$has_shopinfo =  M('eaterplanet_ecommerce_member_shopinfo')->where( array('member_id' => $member_id ) )->find();

		if( !empty($has_shopinfo)  )
		{
			echo json_encode( array('code' => 1, 'msg' => '您已经申请过了') );
			die();
		}else{
			M('eaterplanet_ecommerce_member_shopinfo')->add($data);

			echo json_encode( array('code' => 0) );
			die();
		}



	}


	public function user_index_shareqrcode()
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



		$community_id =  $_GPC['community_id'];
		//community_id: undefined



		$community_config_qrcode_json = D('Home/Front')->get_config_by_name('community_config_qrcode_'.$community_id );

		$community_config_qrcode_arr = unserialize($community_config_qrcode_json);

		$load_new = false;
		if( empty($community_config_qrcode_arr) )
		{
			$load_new = true;
		}else {
			if( $community_config_qrcode_arr['endtime'] < time() )
			{
				$load_new = true;
			}
		}


        //member_id

        $is_login_showprice = D('Home/Front')->get_config_by_name('is_login_showprice');

        if( !empty($is_login_showprice) && $is_login_showprice == 1 )
        {
			$load_new = true;
        }



        if( $load_new)
		{
			$goods_model = D('Home/Pingoods');

			$community_info = M('eaterplanet_community_head')->where( array('id' => $community_id) )->find();

			$tuanz_member_id = $community_info['member_id'];

			if(empty($member_id))
			{
				$member_id = $tuanz_member_id;
			}

			$qrcode_image = $goods_model->_get_index_wxqrcode($member_id,$community_id);

			$member_info = M('eaterplanet_ecommerce_member')->field('avatar,username,wepro_qrcode')
						->where( array('member_id' => $tuanz_member_id) )->find();

			//fff562	 get_weindex_share_image
			$avatar = $goods_model->get_user_avatar($member_info['avatar'], $tuanz_member_id,2);

			//is_hyaline	Bool	false
			$result =  $goods_model->get_weindex_share_image($community_id,$qrcode_image,$avatar , $member_id);


			$data = array();

			$data['image_path']  = $result['full_path'];

			$ed_time = time() + 1800;
			$js_arr = array('endtime' => $ed_time,'image_path' => $data['image_path'] );

			$cd_key = 'community_config_qrcode_'.$community_id;
			D('Seller/Config')->update( array( $cd_key => serialize($js_arr) ) );



		}else{
			$data = array();
			$data['image_path']  = $community_config_qrcode_arr['image_path'];
		}

		$shop_domain = D('Home/Front')->get_config_by_name('shop_domain');

		$data['image_path'] = $shop_domain.'/'.$data['image_path'];
		$result = array('code' => 0, 'image_path' => $data['image_path'] );
		echo json_encode($result);
		die();

	}

	public function myvoucherlist()
	{

		$_GPC = I('request.');

		$type =  isset($_GPC['type']) && !empty($_GPC['type']) ? $_GPC['type'] : 1;

        $page = isset($_GPC['page']) && !empty($_GPC['page']) ? $_GPC['page'] : 1;

        $pre_page = 20;

        $offset = ($page -1) * $pre_page;



		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		if( empty($member_id) )

		{

			$result['code'] = 3;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}



        $condition = "user_id=".$member_id;

        if($type == 1)

        {

            //未使用

            $condition .= " and consume= 'N' and end_time> ".time();



        } else if($type == 2){

            //已使用

            $condition .= " and (consume= 'Y' or end_time< ".time().")";

        }

        $list = M('eaterplanet_ecommerce_coupon_list')->where($condition)->order('add_time desc')->limit($offset,$pre_page)->select();

        $now_time = time();


		$category_list = M('eaterplanet_ecommerce_coupon_category')->field('id,name,status')->where( array('merchid' => 0) )->order('id desc')->select();

		$category = array();
		foreach($category_list as $val)
		{
			$category[$val['id']] = $val;
		}

		$k = 0;
		$result_list = array();
        foreach($list as $key => $val)
        {

			$couponid = $val['voucher_id'];

			$coupon = M('eaterplanet_ecommerce_coupon')->field('catid')->where( array('id' => $couponid) )->find();

        	$val['tag'] = $category[$coupon['catid']]['name'];

			$store_info	= array('s_true_name' =>'','s_logo' => '');

			$store_info['s_true_name'] = D('Home/Front')->get_config_by_name('shoname');

			$store_info['s_logo'] = D('Home/Front')->get_config_by_name('shoplogo');

			if( !empty($store_info['s_logo']))
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{

				$store_info['s_logo'] = '';
			}

			$val['store_name'] = $store_info['s_true_name'];

			$val['s_logo'] = $store_info['s_logo'];

			//now_time

			$val['is_over'] = 0;
			$val['is_use'] = 0;

			if($val['end_time'] < $now_time)
			{
				$val['is_over'] = 1;
			}

			if($val['consume'] == 'Y')
			{
				$val['is_use'] = 1;
			}

			$val['begin_time'] = date('Y.m.d H:i:s', $val['begin_time']);
			$val['end_time']   = date('Y.m.d H:i:s', $val['end_time']);

			$result_list[$k] = $val;
			$k++;
        }

		if( empty($result_list) )
		{
			echo json_encode( array('code' =>1) );
		}else {
			echo json_encode( array('code' =>0, 'list' => $result_list) );
		}

	}

	public function refunddetail()

	{
		$gpc = I('request.');

		$token =  $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];

		if( empty($member_id) )

		{

			$result['code'] = 0;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}

		$ref_id =  $gpc['ref_id'];

		$order_refund = M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id ) )->find();

		$order_id =  $order_refund['order_id'];



		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id,'member_id' => $member_id) )->find();

		if(empty($order_info) )
		{

			$result['code'] = 0;
	        $result['msg'] = '无此订单';
	        echo json_encode($result);
	        die();
		}

		if($order_refund['order_goods_id'] > 0)
		{
			$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_refund['order_goods_id'] ,'order_id' =>$order_id ) )->find();

		}else{
			$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id) )->find();
		}

		if($order_refund['order_goods_id'] > 0)
		{
			$order_refund_history = M('eaterplanet_ecommerce_order_refund_history')->where( array('type' => 2, 'order_goods_id' => $order_refund['order_goods_id'], 'order_id' =>$order_id ) )->order('addtime asc')->find();

		}else{

			$order_refund_history = M('eaterplanet_ecommerce_order_refund_history')->where( array('type' => 2,'order_id' => $order_id) )->order('addtime asc')->find();
		}


		$refund_reason = array(

							'97' =>'商品有质量问题',

							'98' =>'没有收到货',

							'99' =>'商品少发漏发发错',

							'100' =>'商品与描述不一致',

							'101' =>'收到商品时有划痕或破损',

							'102' =>'质疑假货',

							'111' =>'其他',

						);


		$order_refund['ref_type'] = $order_refund['ref_type'] ==1 ? '仅退款': '退款退货';

		$refund_state = array(

							0 => '申请中',

							1 => '商家拒绝',

							2 => '平台介入',

							3 => '退款成功',

							4 => '退款失败',

							5 => '撤销申请',

						);

		$order_refund['state'] = $refund_state[$order_refund['state']];

		$order_refund['addtime']  = date('Y-m-d H:i:s', $order_refund['addtime']);


		$order_refund_image = M('eaterplanet_ecommerce_order_refund_image')->where( array('ref_id' => $order_refund['ref_id']) )->select();

		$url = D('Home/Front')->get_config_by_name('shop_domain');

		$refund_images = array();

		if(!empty($order_refund_image))

		{

			foreach($order_refund_image as $refund_image)
			{

				$refund_image['thumb_image'] = $refund_image['image'] ;


				$refund_images[] = $refund_image;

			}

		}



		if($order_refund['order_goods_id'] > 0)
		{

			$order_refund_historylist = M('eaterplanet_ecommerce_order_refund_history')->where( array('order_goods_id' => $order_refund['order_goods_id'],'order_id' => $order_id) )->order('addtime asc')->select();

		}else{
			$order_refund_historylist = M('eaterplanet_ecommerce_order_refund_history')->where( array('order_id' => $order_id) )->order('addtime asc')->select();
		}





		//ims_
		//.type ==3

		$pingtai_deal = 0;

		foreach($order_refund_historylist as $key => $val)

		{

			if($val['type'] ==3)
			{
				$pingtai_deal = 1;
			}

			$order_refund_history_image = M('eaterplanet_ecommerce_order_refund_history_image')->where( array('orh_id' => $val['id']) )->select();

			if(!empty($order_refund_history_image))

			{

				foreach($order_refund_history_image as $kk => $vv)
				{

					$vv['thumb_image'] =  $url. resize($vv['image'], 200,200) ;


					$order_refund_history_image[$kk] = $vv;

				}

			}

			$val['order_refund_history_image'] = $order_refund_history_image;

			$val['addtime'] = date('Y-m-d H:i:s', $val['addtime']);



			$order_refund_historylist[$key] = $val;

		}



		echo json_encode( array('code' => 1,'pingtai_deal' => $pingtai_deal,'order_refund' =>$order_refund, 'order_id' => $order_id ,'order_refund_history' => $order_refund_history,'order_refund_historylist' => $order_refund_historylist, 'refund_images' => $refund_images,'order_goods' => $order_goods ,'order_info' => $order_info) );

		die();

	}



	public function cancel_refund()

	{

		$gpc = I('request.');


		$token =  $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];

		$ref_id =  $gpc['ref_id'];

		$order_refund = M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id ) )->find();

		$order_id =  $order_refund['order_id'];



		//$order_id =  $gpc['order_id'];



		if( empty($member_id) )

		{

			$result['code'] = 0;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}



		$result = array('code' => 0);


		$order_info = M('eaterplanet_ecommerce_order')->where( array('member_id' => $member_id, 'order_id' => $order_id) )->find();

		if(empty($order_info) )

		{

			$result['msg'] = '非法操作';

			echo json_encode($result);

			die();

		}

		M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id) )->save(  array('state' => 5) );

		//ims_


		$order_history = array();

		$order_history['order_id'] = $order_id;

		$order_history['order_goods_id'] = $order_refund['order_goods_id'];


		$order_history['order_status_id'] = 4;

		$order_history['notify'] = 0;

		$order_history['comment'] = '用户撤销退款';

		$order_history['date_added']=time();


		M('eaterplanet_ecommerce_order_history')->add($order_history);


		//ims_eaterplanet_ecommerce_order_goods
		if($order_refund['order_goods_id'] > 0)
		{

			M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_refund['order_goods_id']) )->save( array('is_refund_state' => 0) );

			if($order_info['order_status_id'] == 12)
			{
				//检测  last_refund_order_status_id

				$order_gd_info = M('eaterplanet_ecommerce_order_goods')->where( array('is_refund_state' => 1, 'order_id' => $order_id ) )->find();

				if( empty($order_gd_info) )
				{
					if($order_info['last_refund_order_status_id'] > 0)
					{
						M('eaterplanet_ecommerce_order')->where(  array('member_id' => $member_id, 'order_id' => $order_id) )->save( array('order_status_id' => $order_info['last_refund_order_status_id'] ) );
					}else{
						M('eaterplanet_ecommerce_order')->where(  array('member_id' => $member_id, 'order_id' => $order_id) )->save( array('order_status_id' => 4) );
					}
				}
			}

		}else{

			if($order_info['last_refund_order_status_id'] > 0)
			{

				M('eaterplanet_ecommerce_order')->where(  array('member_id' => $member_id, 'order_id' => $order_id) )->save( array('order_status_id' => $order_info['last_refund_order_status_id'] ) );
			}else{
				M('eaterplanet_ecommerce_order')->where(  array('member_id' => $member_id, 'order_id' => $order_id) )->save( array('order_status_id' => 4) );

			}


		}




		$result['code'] = 1;

		echo json_encode($result);

		die();

	}


	public function refund_sub()
	{
		$gpc = I('request.');


		$token =  $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];



		if( empty($member_id) )

		{

			$result['code'] = 0;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}

		$data = array();
		$data['order_id'] = $gpc['order_id'];
		$data['complaint_type'] = $gpc['complaint_type'];
		$data['complaint_images'] = $gpc['complaint_images'];
		$data['complaint_desc'] = $gpc['complaint_desc'];
		$data['complaint_mobile'] = $gpc['complaint_mobile'];
		$data['complaint_reason'] = $gpc['complaint_reason'];
		$data['complaint_money'] = $gpc['complaint_money'];

		if( !empty($data['complaint_images']) )
		{
			$data['complaint_images'] = explode(',', $data['complaint_images']);
		}

		$order_id = $data['order_id'];


		$order_goods_id = $gpc['order_goods_id'];

		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id,'member_id' => $member_id) )->find();


		if(empty($order_info) )

		{

			$result['code'] = 0;

	        $result['msg'] = '没有此订单';

	        echo json_encode($result);

	        die();

		}



		$result = array('code' => 0);



		$refdata = array();

		$refdata['order_id'] = intval($data['order_id']);


		$refdata['ref_type'] = intval($data['complaint_type']);

		$refdata['ref_money'] = floatval($data['complaint_money']);

		$refdata['ref_member_id'] = $member_id;

		$refdata['ref_name'] = htmlspecialchars($data['complaint_reason']);

		$refdata['ref_mobile'] = htmlspecialchars($data['complaint_mobile']);

		$refdata['ref_description'] = htmlspecialchars($data['complaint_desc']);

		$refdata['state'] = 0;

		$refdata['addtime'] = time();

		$order_info['total'] = round($order_info['total'],2)< 0.01 ? '0.00':round($order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'],2)	;

		if( !empty($order_goods_id) && $order_goods_id > 0 )
		{
			//ims_eaterplanet_ecommerce_order_goods

			$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id ) )->find();

			$tp_total = round($order_goods_info['total'],2)< 0.01 ? '0.00':round($order_goods_info['total']+$order_goods_info['shipping_fare']-$order_goods_info['voucher_credit']-$order_goods_info['score_for_money']-$order_goods_info['fullreduction_money'],2)	;

			$order_info['total'] = $tp_total;
		}else{

			$tp_total = 0;

			$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id, 'is_refund_state' => 0) )->select();

			foreach($order_goods_list as $order_goods_info)
			{
				$tp_total += round($order_goods_info['total'],2)< 0.01 ? '0.00':round($order_goods_info['total']+$order_goods_info['shipping_fare']-$order_goods_info['voucher_credit']-$order_goods_info['score_for_money']-$order_goods_info['fullreduction_money'],2)	;

			}

			$order_info['total'] = $tp_total;
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

			M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id ) )->save( $refdata );


			$order_history = array();

			$order_history['order_id'] = $order_id;

			$order_history['order_status_id'] = $order_info['order_status_id'];

			$order_history['notify'] = 0;

			$order_history['comment'] = '用户修改退款资料';

			$order_history['date_added']=time();

		//	M('eaterplanet_ecommerce_order_history')->add($order_history);


			$order_refund_history = array();

			$order_refund_history['order_id'] = $order_id;
			$order_refund_history['order_goods_id'] = $order_goods_id;
			$order_refund_history['message'] = $refdata['ref_description'];

			$order_refund_history['type'] = 1;

			$order_refund_history['addtime'] = time();


			$orh_id = M('eaterplanet_ecommerce_order_refund_history')->add($order_refund_history);


			if(!empty($data['complaint_images']))
			{

				foreach($data['complaint_images'] as $complaint_images)
				{

					$img_data = array();

					$img_data['orh_id'] = $orh_id;
					$img_data['uniacid'] = $_W['uniacid'];

					$img_data['image'] = htmlspecialchars($complaint_images);

					$img_data['addtime'] = time();

					M('eaterplanet_ecommerce_order_refund_history_image')->add($img_data);
				}

			}

		}else {

			$refdata['order_goods_id'] = $order_goods_id;

			$ref_id = M('eaterplanet_ecommerce_order_refund')->add($refdata);

			$can_refund_order = true;

			if( !empty($order_goods_id) && $order_goods_id > 0 )
			{
				M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id ) )->save( array('is_refund_state' => 1) );

				//判断是否全部都退款了
				$gdall = M('eaterplanet_ecommerce_order_goods')->field('is_refund_state')->where( array('order_id' =>$order_id ) )->select();

				foreach( $gdall as $vv )
				{
					if( $vv['is_refund_state'] == 0 )
					{
						$can_refund_order = false;
						break;
					}
				}

			}else{
				M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->save( array('is_refund_state' => 1) );
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

				$order_history = array();

				$order_history['order_id'] = $order_id;

				$order_history['order_goods_id'] = $order_goods_id;

				$order_history['order_status_id'] = 12;

				$order_history['notify'] = 0;

				$order_history['comment'] = '用户前台申请退款中';

				$order_history['date_added']=time();

				M('eaterplanet_ecommerce_order_history')->add($order_history);
			}else{

				//部分商品退款
				$order_history = array();

				$order_history['order_id'] = $order_id;

				$order_history['order_goods_id'] = $order_goods_id;

				$order_history['order_status_id'] = 12;

				$order_history['notify'] = 0;

				$order_history['comment'] = '用户申请部分商品退款';

				$order_history['date_added']=time();

				M('eaterplanet_ecommerce_order_history')->add($order_history);
			}





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

					M('eaterplanet_ecommerce_order_refund_image')->add($img_data);

				}

			}

		}







		$result['code'] = 1;

		echo json_encode($result);

		die();

	}

	public function goods_express()//
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

		$order_id = $_GPC['order_id'];// I('get.order_id',0);

		if( empty($member_id) )
		{
			$result['code'] = 2;
	        $result['msg'] = '登录失效';
	        echo json_encode($result);
	        die();
		}


		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

		$now_time = time();

		if($now_time - $order_info['shipping_cha_time'] >= 43200)

		{

			//即时查询接口

			$seller_express = M('eaterplanet_ecommerce_express')->where( array('id' => $order_info['shipping_method'] ) )->find();


			if(!empty($seller_express['simplecode']))
			{

				//887406591556327434  YTO

				//TODO...

				$ebuss_info = D('Home/Front')->get_config_by_name('kdniao_id');

				$exappkey = D('Home/Front')->get_config_by_name('kdniao_api_key');


				$req_url = "http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx";

				$requestData= "{'OrderCode':'".$order_id."','ShipperCode':'".$seller_express['simplecode']."','LogisticCode':'". $order_info['shipping_no']."'}";

                $kdniao_freestatus = D('Home/Front')->get_config_by_name('kdniao_freestatus');

                $customerName = $order_info['shipping_tel'];
                $customerName = substr($customerName,7);

				$datas = array(

					'EBusinessID' => $ebuss_info,

					'RequestType' => '1002',

					'RequestData' => urlencode($requestData) ,

					'DataType' => '2',

					'CustomerName'=>$customerName

				);


				if( isset($kdniao_freestatus) && $kdniao_freestatus ==1 )
				{
					$datas['RequestType'] = '8001';
				}

				$datas['DataSign'] = $this->encrypt($requestData, $exappkey);

				$result=$this->sendPost($req_url, $datas);

				$result = json_decode($result);
               // var_dump($result);


				//根据公司业务处理返回的信息......

				//Traces

				if(!empty($result->Traces))
				{
					$order_info['shipping_traces'] = serialize($result->Traces);

					$up_data = array('shipping_cha_time' => time(), 'shipping_traces' => $order_info['shipping_traces']);

					M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->save( $up_data );
				}

			}

		}

		//ims_

		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->find();

		$goods_info = array();

		$goods_info = D('Home/Pingoods')->get_goods_images($order_goods['goods_id']);


		$goods_info['image'] = tomedia($goods_info['image']);

		$seller_express = M('eaterplanet_ecommerce_express')->where( array('id' => $order_info['shipping_method'] ) )->find();

		$order_info['shipping_traces'] =  unserialize($order_info['shipping_traces']) ;

		echo json_encode( array('code' => 0, 'seller_express' => $seller_express, 'goods_info' => $goods_info, 'order_info' => $order_info) );

		die();

	}

	public function get_order_money()
	{
		$gpc = I('request.');

		$token =  $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];



		if( empty($member_id) )

		{

			$result['code'] = 0;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}

		$order_id =  $gpc['order_id'];
		$order_goods_id =  $gpc['order_goods_id'];

		//total  eaterplanet_ecommerce_order


		$total = 0;

		if( !empty($order_goods_id) && $order_goods_id > 0 )
		{
			$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id , 'member_id' => $member_id ) )->find();

			$order_goods_all = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id,'order_id' => $order_id) )->select();

			foreach($order_goods_all as $val )
			{
				$total += $val['total'] + $val['shipping_fare']- $val['voucher_credit']- $val['fullreduction_money'] - $val['score_for_money'];
			}

			echo json_encode( array('code' =>1, 'total' => $total, 'order_status_id'=>$order_info['order_status_id']) );

			die();
		}else{

			$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id,'member_id' =>$member_id ) )->find();

			$order_goods_all = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id, 'is_refund_state' => 0) )->select();

			foreach($order_goods_all as $val )
			{
				$total += $val['total'] + $val['shipping_fare']- $val['voucher_credit']- $val['fullreduction_money'] - $val['score_for_money'];
			}


			//$order_info['total'] = $order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money']- $order_info['score_for_money'];

			//还要判断依据退款的
			if($total < 0){
				$total = 0;
			}

			$total = round($total, 2);

			echo json_encode( array('code' =>1, 'total' => $total, 'order_status_id'=>$order_info['order_status_id']) );

			die();
		}


	}

	function encrypt($data, $appkey) {

		return urlencode(base64_encode(md5($data.$appkey)));

	}
	function sendPost($url, $datas) {

		$temps = array();

		foreach ($datas as $key => $value) {

			$temps[] = sprintf('%s=%s', $key, $value);

		}

		$post_data = implode('&', $temps);

		$url_info = parse_url($url);

		if(empty($url_info['port']))

		{

			$url_info['port']=80;

		}

		$httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";

		$httpheader.= "Host:" . $url_info['host'] . "\r\n";

		$httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";

		$httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";

		$httpheader.= "Connection:close\r\n\r\n";

		$httpheader.= $post_data;

		$fd = fsockopen($url_info['host'], $url_info['port']);

		fwrite($fd, $httpheader);

		$gets = "";

		$headerFlag = true;

		while (!feof($fd)) {

			if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {

				break;

			}

		}

		while (!feof($fd)) {

			$gets.= fread($fd, 128);

		}

		fclose($fd);



		return $gets;

	}

	//TODO.....next

	public function getaddress()
	{
		$gpc = I('request.');

		$token =  $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}


	    $member_id = $weprogram_token['member_id'];


		$address_list = M('eaterplanet_ecommerce_address')->where( array('member_id' => $member_id) )->order('is_default desc , address_id desc')->select();


		foreach( $address_list as $key => $val )
		{
			//province_id  city_id country_id

			$province_info = M('eaterplanet_ecommerce_area')->field('name')->where( array('id' => $val['province_id']) )->find();


			$city_info = M('eaterplanet_ecommerce_area')->field('name')->where( array('id' => $val['city_id']))->find();

			$country_info = M('eaterplanet_ecommerce_area')->field('name')->where( array('id' => $val['country_id'] ) )->find();

			$val['province_name'] = $province_info['name'];
			$val['city_name'] = $city_info['name'];
			$val['country_name'] = $country_info['name'];

			$address_list[$key] = $val;
		}

		if( !empty($address_list) )
		{
			echo json_encode( array('code' => 0, 'list' => $address_list) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}

	/**
		收集订阅消息
	**/
	public function collect_subscriptmsg()
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

		//'pay_order','send_order','hexiao_success','apply_community','open_tuan','take_tuan','pin_tuansuccess','apply_tixian'
		$type_str = $_GPC['type'];

		$type_arr = explode(',', $type_str);

		foreach( $type_arr as $type )
		{
			$info = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $member_id, 'type' => $type ) )->find();

			if( !empty($info) && false )
			{
				continue;
			}

			$template_id = "";

			switch( $type )
			{
				case 'pay_order':
					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_pay_order');
				break;
				case 'send_order':
					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_send_order');
				break;
				case 'hexiao_success':
					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_hexiao_success');
				break;
				case 'apply_community':
					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_apply_community');
				break;
				case 'open_tuan':
					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_open_tuan');
				break;
				case 'take_tuan':
					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_take_tuan');
				break;
				case 'pin_tuansuccess':
					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_pin_tuansuccess');
				break;
				case 'apply_tixian':
					$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_apply_tixian');
				break;
                case 'presale_ordercan_continuepay':
                    $template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_presale_ordercan_continuepay');
                    break;
            }

			if( empty($template_id) )
			{
				continue;
			}

			$ins_data = array();
			$ins_data['uniacid'] = 0;
			$ins_data['member_id'] = $member_id;
			$ins_data['template_id'] = $template_id;
			$ins_data['type'] = $type;
			$ins_data['addtime'] = time();

			M('eaterplanet_ecommerce_subscribe')->add( $ins_data );

		}


		echo json_encode( array('code' => 0) );
		die();


	}

	public function order_comment()

	{
		$gpc = I('request.');

		$token =  $gpc['token'];



		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];

	    $order_id =  $gpc['order_id'];
		$goods_id =  $gpc['goods_id'];


		if( empty($member_id) )

		{

			$result['code'] = 3;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}


		//ims_ eaterplanet_ecommerce_order_goods ims_eaterplanet_ecommerce_order_goods


		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$order_id ,'goods_id' => $goods_id) )->find();


		$order_option_list =M('eaterplanet_ecommerce_order_option')->where( array('order_goods_id' => $order_goods['order_goods_id']) )->select();


	    $order_goods['goods_images'] = tomedia($order_goods['goods_images']);


		$goods_filed = M('eaterplanet_ecommerce_goods')->field('productprice')->where( array('id' => $order_goods['goods_id']) )->find();

		$order_goods['orign_price'] = $goods_filed['productprice'];



		foreach($order_option_list as $option)
		{
			$order_goods['option_str'][] = $option['value'];
		}

		if( !isset($order_goods['option_str']) )

		{

			$order_goods['option_str'] = '';

		}else{

			$order_goods['option_str'] = implode(',', $order_goods['option_str']);

		}

		$order_goods['price'] = round($order_goods['price'],2);

		$order_goods['orign_price'] = round($order_goods['orign_price'],2);





	    $image_info = D('Home/Pingoods')->get_goods_images( $order_goods['goods_id'] );
//		M('goods')->field('image')->where( array('goods_id' => ) )->find();

		$goods_image = tomedia($image_info['image']);

		$open_comment_gift = D('Home/Front')->get_config_by_name('open_comment_gift');
		$open_comment_gift = !empty($open_comment_gift) ? $open_comment_gift : 0;
		//打到上限关闭
		$commentresult = D('Seller/Order')->check_comment_gift_score($member_id);
		if(!$commentresult['is_comment_gift']){
			$open_comment_gift = 0;
		}
		$comment_gift_publish = D('Home/Front')->get_config_by_name('comment_gift_publish');
		$comment_gift_publish = htmlspecialchars_decode($comment_gift_publish);


		echo json_encode( array('code' => 0 ,'order_goods' =>$order_goods, 'goods_image' => $goods_image,'goods_id' =>$order_goods['goods_id']
				,'open_comment_gift' =>$open_comment_gift,'comment_gift_publish' =>$comment_gift_publish
			) );

		die();

	}


	public function sub_comment()

	{
		$gpc = I('request.');

		$token =  $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];

		if( empty($member_id) )

		{

			$result['code'] = 3;

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}


		//cur_rel:cur_rel,cur2_rel:cur2_rel,cur3_rel:cur3_rel,imgs:imgs,


		$data  = array();
		$data['order_id'] = $gpc['order_id'];
		$data['goods_id'] = $gpc['goods_id'];
		$data['cur_rel'] = 5;
		$data['cur2_rel'] = 5;
		$data['cur3_rel'] = 5;
		$data['imgs'] = $gpc['imgs'];
		$data['comment_content'] = $gpc['comment_content'];



	    $order_id =  $data['order_id'];

	    $goods_id = $data['goods_id'];

	    $cur_rel = empty($data['cur_rel']) ? 5:$data['cur_rel'];

		$cur2_rel = empty($data['cur2_rel']) ? 5:$data['cur2_rel'];

		$cur3_rel = empty($data['cur3_rel']) ? 5:$data['cur3_rel'];

		$imgs = $data['imgs'];



		$comment_content =  htmlspecialchars($data['comment_content']);

		$order_goods = M('eaterplanet_ecommerce_order_goods')->field('name,goods_images')->where( array('goods_id' => $goods_id,'order_id' => $order_id) )->find();

		$order_info = M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $order_id)  )->find();

		$member_info = M('eaterplanet_ecommerce_member')->field('username as uname , avatar')->where( array('member_id' => $member_id) )->find();

		//ims_

	    $data = array();

	    $data['member_id'] = $member_id;

	    $data['order_id'] =  $order_id;

	    $data['goods_id'] =  $goods_id;



		$data['goods_name'] = $order_goods['name'];

		$data['goods_image'] = $order_goods['goods_images'];

		$data['order_num_alias'] = $order_info['order_num_alias'];

		$data['avatar'] = $member_info['avatar'];

		$data['user_name'] = $member_info['uname'];

		//open_comment_shenhe state state

		$open_comment_shenhe = D('Home/Front')->get_config_by_name('open_comment_shenhe');

		if( empty($open_comment_shenhe) || $open_comment_shenhe == 0 )
		{
			$data['state'] = 1;
		}


	    $data['star'] =  $cur_rel;

	    $data['star2'] =  $cur2_rel;

	    $data['star3'] =  $cur3_rel;

	    $data['images'] =  serialize($imgs);

	    $data['is_picture'] =  empty($imgs) ? 0 :1;

	    $data['content'] = $comment_content;

	    $data['add_time'] = time();

		$comment_id = M('eaterplanet_ecommerce_order_comment')->add($data);

		if(isset($data['state']) && $data['state'] == 1){
		    //好评有礼送积分
		    D('Seller/Order')->sendCommentGift($comment_id);
		    M('eaterplanet_ecommerce_order_comment')->where(array('comment_id'=>$comment_id,'member_id'=>$member_id))->save(array('is_send_point'=>1));
		}
	  //TOD...暂时屏蔽
	  // $goods_info = M('goods')->field('store_id')->where( array('goods_id' => $goods_id) )->find();

	   // $group_info =  M('group')->field('seller_id')->where( array('seller_id' => $goods_info['store_id']) )->find();


	   //判断所有订单都评价了吗？
	   $comment_all_order = true;
	    //eaterplanet_ecommerce_order_goods

		$order_goods_all =	M('eaterplanet_ecommerce_order_goods')->field('goods_id')->where( array('order_id' => $order_id) )->select();

		foreach($order_goods_all as $val)
		{

			$order_comment = M('eaterplanet_ecommerce_order_comment')->field('comment_id')->where( array('goods_id' => $val['goods_id'],'order_id' =>$order_id) )->find();

			if(empty($order_comment))
			{
				$comment_all_order = false;
				break;
			}
		}

		if($comment_all_order)
		{
			$order_history = array();

			$order_history['order_id'] = $order_id;

			$order_history['order_status_id'] = 11;

			$order_history['notify'] = 0;

			$order_history['comment'] = '用户提交评论,订单完成。';

			$order_history['date_added']=time();


			M('eaterplanet_ecommerce_order_history')->add($order_history);

			//ims_

			M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 11,'finishtime' => time()) );

		}


	    echo json_encode( array('code' => 1) );

	    die();

	}

	//begin....

	/**
		客户积分流水
		controller:  user.get_user_integral_flow

		token,
		page
		返回：
			code=0 有数据，code=1 没有数据。


			金额：money，
			时间： charge_time
			状态  state：1 充值成功，3 余额支付

	**/
	public function get_user_integral_flow()
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

		if( empty($member_id) )
		{
			$result['code'] = 1;
	        $result['msg'] = '登录失效';
	        echo json_encode($result);
	        die();
		}

		$per_page = 20;

	    $page = isset($_GPC['page']) ? $_GPC['page'] : 1;


	    $offset = ($page - 1) * $per_page;

	    $list = array();

		$list = M('eaterplanet_ecommerce_member_integral_flow')->where(" member_id = ".$member_id."  ")->order('id desc')->limit($offset,$per_page)->select();

		if( !empty($list) )
		{
			foreach($list as &$value)
			{
				$value['current_yuer'] = $value['after_operate_score'];

				$value['addtime'] = date('Y-m-d H:i:s', $value['addtime']);

				if( in_array($value['type'], array('goodsbuy','refundorder','orderbuy','pintuan_rebate') ))
				{
					$od_info = M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $value['order_id'] ) )->find();

					if( !empty($od_info) )
					{
						$value['trans_id'] = $od_info['order_num_alias'];
					}

					$order_goods_info = M('eaterplanet_ecommerce_order_goods')->field('name')->where( array('order_goods_id' => $value['order_goods_id']) )->find();

					$value['goods_name'] = $order_goods_info['name'];

				}
			}
			echo json_encode( array('code' => 0, 'data' => $list) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}


	//end...

	//----begin---
	/**
		客户充值流水
		controller:  user.get_user_charge_flow

		token,
		page
		返回：
			code=0 有数据，code=1 没有数据。
			金额：money，
			时间： charge_time
			状态  state：1 充值成功，3 余额支付

	**/
	public function get_user_charge_flow()
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


		$per_page = 20;

	    $page = isset($_GPC['page']) ? $_GPC['page'] : 1;


	    $offset = ($page - 1) * $per_page;



	    $list = array();

		$sql = 'select * from  '.C('DB_PREFIX')."eaterplanet_ecommerce_member_charge_flow
			where member_id = ".$member_id."  and state in (1,3,4,5,8,9,10,11,12,20,21) order by id desc limit {$offset},{$per_page}";

		$list = M()->query($sql);

		if( !empty($list) )
		{
			foreach($list as &$value)
			{
				$value['current_yuer'] = $value['operate_end_yuer'];
				$value['charge_time'] = date('Y-m-d H:i:s', $value['charge_time']);
				if($value['state'] == 3  || $value['state'] == 4 || $value['state'] == 21)
				{
					$od_info = M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $value['trans_id'] ) )->find();

					if( !empty($od_info) )
					{
						$value['trans_id'] = $od_info['order_num_alias'];
					}
					//6
					if( $value['state'] == 4 && !empty($value['order_goods_id']) && $value['order_goods_id'] > 0 )
					{
						$value['state'] = 6;
					}

				}
			}
			echo json_encode( array('code' => 0, 'data' => $list) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}


	//----end----

	/**
		获取用户信息 can_yupay show_user_pin
	**/
	public function get_user_info()//
	{
		$gpc = I('request.');

		$token =  $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$needAuth = false;
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			$needAuth = true;
			//echo json_encode( array('code' => 1) );
			//die();
		}

		$member_id = $weprogram_token['member_id'];

		if($member_id){

			$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();


			$member_info['full_user_name'] = base64_decode($member_info['full_user_name']);

			// AFTER TO DO .
			$member_level_list = array();
			$level_name = '';

			$is_show_member_level = D('Home/Front')->get_config_by_name('is_show_member_level');
			$member_level_arr = array(
									0 => array('level_name' => '普通客户', 'discount' => 100),
								);

			$mb_level_list = M('eaterplanet_ecommerce_member_level')->where(1)->order('id asc')->select();

			if( !empty($mb_level_list) )
			{
				foreach( $mb_level_list as $val )
				{
					$tmp = array();
					$tmp['level_name'] = $val['levelname'];
					$tmp['discount'] = $val['discount'];

					$member_level_arr[$val['id']] = $tmp;
				}
			}

			$member_info['is_show_member_level'] = $is_show_member_level;//是否显示客户等级信息
			$member_info['member_level_info'] = $member_level_arr[ $member_info['level_id'] ];


			$opencommiss = D('Home/Front')->get_config_by_name('commiss_level');

			//待付款数量
			$wait_pay_count = D('Home/Frontorder')->get_member_order_count($member_id," and order_status_id =3 ");
			//待配送数量
			$wait_send_count = D('Home/Frontorder')->get_member_order_count($member_id," and order_status_id = 1 and type <> 'ignore' ");
			//待提货数量
			$wait_get_count = D('Home/Frontorder')->get_member_order_count($member_id," and order_status_id =4 ");
			//已提货数量
			$has_get_count = D('Home/Frontorder')->get_member_order_count($member_id," and order_status_id in(6,11) ");
			//售后退款
			$refund_send_count = D('Home/Frontorder')->get_member_order_count($member_id," and order_status_id in(7,12,13) ");

			$head_info = D('Home/Front')->get_member_community_info($member_id);



			if( empty($head_info) )
			{
				$member_info['is_head'] = 0;
			}else{
				if($head_info['state'] == 1)
					$member_info['is_head'] = 1;
				else if($head_info['state'] == 0)
				{
					$member_info['is_head'] = 2;
				}
				else if($head_info['state'] == 2)
				{
					$member_info['is_head'] = 3;
				}
			}


            $wechat_div = D('Home/Front')->get_config_by_name("wechat_div");
			$wechat_div = $wechat_div?$wechat_div:"微信";
            $member_info['wechat_div'] = $wechat_div;
			$member_info['wait_pay_count'] = $wait_pay_count;
			$member_info['wait_send_count'] = $wait_send_count;
			$member_info['wait_get_count'] = $wait_get_count;
			$member_info['has_get_count'] = $has_get_count;

			//判断是否有提货码，没有就生成 hexiao_qrcod
			//  eaterplanet_ecommerce/moduleA/groupCenter/pendingDeliveryOrders?memberId=49

			if( empty($member_info['hexiao_qrcod']))
			{
				$path = "eaterplanet_ecommerce/moduleA/groupCenter/pendingDeliveryOrders";
				$hexiao_qrcod = D('Home/Pingoods')->_get_commmon_wxqrcode($path, $member_id);


				if( empty($hexiao_qrcod) )
				{
					$member_info['hexiao_qrcod'] = '';
				}else{
					M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->save( array('hexiao_qrcod' => $hexiao_qrcod) );

					$member_info['hexiao_qrcod'] =  tomedia($hexiao_qrcod);
				}

			}else{
				$member_info['hexiao_qrcod'] =  tomedia($member_info['hexiao_qrcod']);
			}

			//判断是否是核销员
			$salesroom_member_info = M('eaterplanet_ecommerce_salesroom_member')->where( array('member_id' => $member_id,'state'=>1) )->find();
			if(!empty($salesroom_member_info)){
				$member_info['is_hexiao_member'] = 1;
			}else{
				$member_info['is_hexiao_member'] = 0;
			}

			$is_show_member_id = D('Home/Front')->get_config_by_name('is_show_member_id');
			$member_info['is_show_member_id'] = $is_show_member_id;

			$supp_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();

			$is_supply = 0; //未申请 2审核通过 1审核中
			if( !empty($supp_info) ){
				$is_supply = ($supp_info['state'] == 1) ? 2 : 1;
			}




			$is_show_auth_mobile = D('Home/Front')->get_config_by_name('is_show_auth_mobile');
			if( empty($is_show_auth_mobile) )
			{
				$is_show_auth_mobile = 0;
			}




			//分销等级，如果是0，那么就是未开启分销
			$commiss_level = D('Home/Front')->get_config_by_name('commiss_level');
			if( empty($commiss_level) )
			{
				$commiss_level = 0;
			}

			//是否需要分享多少人成为分销
			$commiss_sharemember_need = D('Home/Front')->get_config_by_name('commiss_sharemember_need');

			//分享多少个人成为分销商
			$commiss_share_member_update = D('Home/Front')->get_config_by_name('commiss_share_member_update');

			//是否需要填写表单
			$commiss_biaodan_need = D('Home/Front')->get_config_by_name('commiss_biaodan_need');

			//是否需要审核
			$commiss_become_condition = D('Home/Front')->get_config_by_name('commiss_become_condition');


			//自定义表单格式
			$commiss_diy_form = D('Home/Front')->get_config_by_name('commiss_diy_form');


			$commiss_diy_name = D('Home/Front')->get_config_by_name('commiss_diy_name');


			$share_member_count = M('eaterplanet_ecommerce_member')->where( "share_id={$member_id} and (agentid =0 or agentid={$member_id} )" )->count();

			$create_time = strtotime( date('Y-m-d').' 00:00:00' );

			$today_share_member_count = M('eaterplanet_ecommerce_member')->where( "share_id={$member_id} and (agentid =0 or agentid={$member_id}) and create_time>={$create_time}" )->count();


			$create_y_time = $create_time - 86400;
			$create_end_time = $create_time;

			$yestoday_share_member_count = M('eaterplanet_ecommerce_member')->where( "share_id={$member_id} and (agentid =0 or agentid={$member_id}) and create_time>={$create_y_time} and create_time < {$create_end_time}" )->count();

			//佣金团收益金额， 已结算多少钱，未结算多少钱
			$pintuan_money = 0;
			$pintuan_hasstatement_money = 0;
			$pintuan_unstatement_money =0;


			$pin_commiss = M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $member_id ) )->find();

			if( !empty($pin_commiss) )
			{
				$pintuan_money = $pin_commiss['money'] + $pin_commiss['dongmoney']+ $pin_commiss['getmoney'];
			}

			$tp_hasstatement_money = M('eaterplanet_ecommerce_pintuan_commiss_order')->where( array('member_id' => $member_id, 'state' => 1) )->sum('money');

			if( !empty($tp_hasstatement_money) && $tp_hasstatement_money > 0 )
			{
				$pintuan_hasstatement_money = $tp_hasstatement_money;
			}

			$tp_unstatement_money = M('eaterplanet_ecommerce_pintuan_commiss_order')->where( array('member_id' => $member_id,'state' => 0 ) )->sum('money');

			if( !empty($tp_unstatement_money) && $tp_unstatement_money > 0 )
			{
				$pintuan_unstatement_money = $tp_unstatement_money;
			}
			$isopen_presale = D('Home/Front')->get_config_by_name('isopen_presale');

			$isopen_presale = empty($isopen_presale) ? 0 : 1;

			//是否开启礼品卡活动 begin
            $isopen_virtualcard = D('Home/Front')->get_config_by_name('isopen_virtualcard');
            $is_open_virtualcard_show = D('Home/Front')->get_config_by_name('is_open_virtualcard_show');

            //礼品卡中心
            $virtualcard_name_modify = D('Home/Front')->get_config_by_name('virtualcard_name_modify');
            $virtualcard_name_modify = !isset($virtualcard_name_modify) || empty($virtualcard_name_modify) ? '礼品卡中心' : $virtualcard_name_modify;

            if( isset($isopen_virtualcard) && $isopen_virtualcard == 1 )
            {
                $is_open_virtualcard_show = !isset($is_open_virtualcard_show) ? 0 : $is_open_virtualcard_show;
            }else{
                $is_open_virtualcard_show = 0;
            }
            //end

			$result = array();
			$result['code'] = 0;
			$result['data'] = $member_info;

			$result['is_supply'] = $is_supply;

			$result['is_show_auth_mobile'] = $is_show_auth_mobile;


			$result['commiss_level'] = $commiss_level;

			$result['share_member_count'] = $share_member_count;
			$result['today_share_member_count'] = $today_share_member_count;
			$result['yestoday_share_member_count'] = $yestoday_share_member_count;

			$result['commiss_sharemember_need'] = $commiss_sharemember_need;
			$result['commiss_share_member_update'] = $commiss_share_member_update;
			$result['commiss_biaodan_need'] = $commiss_biaodan_need;
			$result['commiss_diy_form'] = unserialize($commiss_diy_form);

			$result['commiss_become_condition'] = $commiss_become_condition;

			$result['pintuan_money'] = $pintuan_money;
			$result['pintuan_hasstatement_money'] = $pintuan_hasstatement_money;
			$result['pintuan_unstatement_money'] = $pintuan_unstatement_money;
			$result['isopen_presale'] = $isopen_presale;
			$result['is_open_virtualcard_show'] = $is_open_virtualcard_show;//是否开启 礼品卡客户中心入口
			$result['virtualcard_name_modify'] = $virtualcard_name_modify;//礼品卡中心名称自定义

			//是否开启商户手机端 member_id
			$supply_is_open_mobilemanage = D('Home/Front')->get_config_by_name('supply_is_open_mobilemanage');

			if( empty($supply_is_open_mobilemanage) || $supply_is_open_mobilemanage == 0 )
			{
				$supply_is_open_mobilemanage = 0;
			}

			$result['is_open_supplymobile'] = 0;

			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();

			if( !empty($supply_info) &&  $supply_info['state'] == 1 && $supply_info['type'] == 1 && $supply_info['is_open_mobilemanage'] == 1 )
			{
				$result['is_open_supplymobile'] = 1;
			}

		} else {
			$result = array();
			$result['code'] = 0;
			$result['is_open_supplymobile'] = 0;
		}

		//判断是否开启了 会员卡 is_open_vipcard_buy
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$modify_vipcard_name = D('Home/Front')->get_config_by_name('modify_vipcard_name');
		$modify_vipcard_logo = D('Home/Front')->get_config_by_name('modify_vipcard_logo');

		$modify_vipcard_name = empty($modify_vipcard_name) ? '吃货星球会员': $modify_vipcard_name;

		if( !empty($modify_vipcard_logo) )
		{
			$modify_vipcard_logo = tomedia($modify_vipcard_logo);
		}

		$result['is_open_vipcard_buy'] = $is_open_vipcard_buy;
		$result['modify_vipcard_name'] = $modify_vipcard_name;
		$result['modify_vipcard_logo'] = $modify_vipcard_logo;

		$result['is_vip_card_member'] = 0;

		if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
		{
			//card_id //card_begin_time //card_end_time

			$now_time = time();

			if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
			{
				$result['is_vip_card_member'] = 1;//还有会员
			}else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){
				$result['is_vip_card_member'] = 2;//已过期
			}
		}

		//判断是否开启了 会员卡 is_open_vipcard_buy
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$modify_vipcard_name = D('Home/Front')->get_config_by_name('modify_vipcard_name');
		$modify_vipcard_logo = D('Home/Front')->get_config_by_name('modify_vipcard_logo');

		$modify_vipcard_name = empty($modify_vipcard_name) ? '吃货星球会员': $modify_vipcard_name;

		if( !empty($modify_vipcard_logo) )
		{
			$modify_vipcard_logo = tomedia($modify_vipcard_logo);
		}

		$result['is_open_vipcard_buy'] = $is_open_vipcard_buy;
		$result['modify_vipcard_name'] = $modify_vipcard_name;
		$result['modify_vipcard_logo'] = $modify_vipcard_logo;

		$result['is_vip_card_member'] = 0;

		if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
		{
			//card_id //card_begin_time //card_end_time

			$now_time = time();

			if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
			{
				$result['is_vip_card_member'] = 1;//还有会员
			}else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){
				$result['is_vip_card_member'] = 2;//已过期
			}
		}

		//判断是否开启同城配送begin
		$isopen_localtown_delivery = D('Home/Front')->get_config_by_name('isopen_localtown_delivery');
		$isopen_localtown_delivery = isset($isopen_localtown_delivery) && $isopen_localtown_delivery > 0 ? $isopen_localtown_delivery: 0;

		$result['is_localtown_distributionman'] = 0;//是否同城配送员，1是，0 不是

		if( $isopen_localtown_delivery == 1 && $member_id > 0 )
		{
			$orderdistribution = M('eaterplanet_ecommerce_orderdistribution')->where( array('member_id' => $member_id ) )->find();

			if( !empty($orderdistribution) )
			{
				$result['is_localtown_distributionman'] = 1;
			}
		}


		//end

		//签到奖励 begin
		$isopen_signinreward = D('Home/Front')->get_config_by_name('isopen_signinreward');
		$show_signinreward_icon = D('Home/Front')->get_config_by_name('show_signinreward_icon');

		if( empty($isopen_signinreward) )
		{
			$isopen_signinreward = 0;
		}

		if( empty($show_signinreward_icon) )
		{
			$show_signinreward_icon = 0;
		}

		$result['show_user_tuan_mobile'] = D('Home/Front')->get_config_by_name('show_user_tuan_mobile');

		$result['isopen_signinreward'] = $isopen_signinreward;
		$result['show_signinreward_icon'] = $show_signinreward_icon;
		//签到奖励  end

		$result['commiss_diy_name'] = D('Home/Front')->get_config_by_name('commiss_diy_name');
		$result['user_tool_showtype'] = D('Home/Front')->get_config_by_name('user_tool_showtype');

		$result['needAuth'] = $needAuth;

		//判断是否开启邀新有礼begin
		$is_open_invite_invitation = D('Home/Front')->get_config_by_name('is_invite_open_status');
		$is_open_invite_invitation = !empty($is_open_invite_invitation) ? $is_open_invite_invitation : 0;
		$result['is_open_invite_invitation'] = $is_open_invite_invitation;

		//end

        //判断是否开启平台入口
        $isopen_admin_managefront = D('Home/Front')->get_config_by_name('isopen_admin_managefront');
        $isopen_admin_managefront = isset($isopen_admin_managefront) ? $isopen_admin_managefront : 0;
        if($isopen_admin_managefront == 1)
        {
            //检测是否平台管理员
            $platform_admin_member = D('Home/Front')->get_config_by_name('platform_admin_member');

            $platform_admin_member_arr = explode(',', $platform_admin_member );
            if( !empty($platform_admin_member_arr) &&  in_array( $member_id , $platform_admin_member_arr ) )
            {
                $isopen_admin_managefront = 1;
            }else{
                $isopen_admin_managefront = 0;
            }
        }

        $result['isopen_admin_managefront'] = $isopen_admin_managefront;
        //fixed 20211228
		$result['isopen_virtualcard'] = D('Home/Front')->get_config_by_name('isopen_virtualcard');
		$result['isopen_virtualcard'] = $isopen_virtualcard;

        //end..


		echo json_encode(  $result );
		die();

	}

	public function supply_apply()
	{
		$gpc = I('request.');

		$token =  $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();


		$supp_info = M('eaterplanet_ecommerce_supply')->where( array('member_id' => $member_id) )->find();


		if( !empty($supp_info) )
		{
			echo json_encode( array('code' => 1,'msg' => '您已经申请过了') );
			die();
		}

		$data = array();

		$data['name'] = $gpc['name'];

		$data['shopname'] = $gpc['shopname'];
		$data['member_id'] = $member_id;
		$data['logo'] = $gpc['logo'];
		$data['mobile'] = $gpc['mobile'];
		$data['product'] = $gpc['product'];
		$data['apptime'] = 0;
		$data['state'] = 0;
		$data['addtime'] = time();

		if( empty($data['name']) )
		{
			echo json_encode( array('code' => 1,'msg' => '商户联系人不能为空') );
			die();
		}
		if( empty($data['shopname']) )
		{
			echo json_encode( array('code' => 1,'msg' => '商户名称不能为空') );
			die();
		}
		if( empty($data['mobile']) )
		{
			echo json_encode( array('code' => 1,'msg' => '商户手机号不能为空') );
			die();
		}

		M('eaterplanet_ecommerce_supply')->add($data);

		echo json_encode( array('code' => 0) );
		die();
	}


	public function get_member_form_id()
	{
		$gpc = I('request.');

		$token =  $gpc['token']; //I('get.token');

		$from_id = $gpc['from_id'];  //I('get.from_id');


		if($from_id != 'the formId is a mock one')
		{

			$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

			$member_id = $weprogram_token['member_id'];


			$member_formid_data = array();

			$member_formid_data['member_id'] = $member_id;
			$member_formid_data['state'] = 0;
			$member_formid_data['formid'] = $from_id;
			$member_formid_data['addtime'] = time();

			M('eaterplanet_ecommerce_member_formid')->add( $member_formid_data );
		}

		echo json_encode( array('code' => 1) );

		die();

	}

	public function getPhoneNumber()
    {
		$_GPC = I('request.');

		$iv = $_GPC['iv'];
		$encryptedData = $_GPC['encryptedData'];
		$token = $_GPC['token'];
		$res = $this->decryptData($encryptedData, $iv, $token);

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(empty($weprogram_token) || empty($weprogram_token['member_id']))
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$member_id = $weprogram_token['member_id'];


		$phoneNumber = $res->phoneNumber;
		if($phoneNumber){
			$param = array();
			$param['telephone'] = $phoneNumber;

			M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->save($param);

			$result = array('code' => 0, 'phoneNumber' =>$phoneNumber);
		} else{
			$result = array('code' => 1, 'msg' => '获取失败，请手动输入手机号', 'error' => $res);
		}

		echo json_encode($result);
		die();
    }

    // 小程序解密
   	public function decryptData($encryptedData, $iv, $token)
    {
		$caceh_data = S('wepro_'.$token);

		$session_info = M('eaterplanet_ecommerce_weprogram_token')->where( array('token' => $token ) )->find();

		$session_key = '';
		if(!empty($session_info) && !empty($session_info['session_key'])) $session_key = $session_info['session_key'];



		$appid_info = M('eaterplanet_ecommerce_config')->where( array('name' => 'wepro_appid') )->find();

        if (strlen($session_key) != 24) {
            return -41001;
        }
        $aesKey=base64_decode($session_key);

        if (strlen($iv) != 24) {
            return -41002;
        }
        $aesIV=base64_decode($iv);
		$aesCipher=base64_decode($encryptedData);
		$result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );

        if( $dataObj  == NULL )
        {
            return -41003;
        }

        if( $dataObj->watermark->appid != $appid_info['value'] )
        {
            return -41003;
        }

        return  $dataObj;
    }

	public function applogin()
	{
		$gpc = I('request.');

		$code = $gpc['code'];

		$appid_info = M('eaterplanet_ecommerce_config')->where( array('name' => 'wepro_appid') )->find();

		$appsecret_info = M('eaterplanet_ecommerce_config')->where( array('name' => 'wepro_appsecret') )->find();

		$appid 		= $appid_info['value'];
		$appsecret  = $appsecret_info['value'];


		$url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appsecret}&js_code={$code}&grant_type=authorization_code";

		$open_str = http_request($url);

		$data = json_decode($open_str, true);

		$expires_time = time() + $data['expires_in'];
		$token = md5($data['openid'].time());


		$token_data = array();
		$token_data['wepro_openid_'.$token] = $data['openid'];
		$token_data['wepro_expires_time_'.$token] = $expires_time;
		$token_data['wepro_session_key_'.$token] = $data['session_key'];
		$token_data['wepro_unionid_'.$token] = $data['unionid'];

		S('wepro_'.$token, $token_data);

		$werp_data = array();
		$werp_data['token'] = $token;

        	$member_info = M('eaterplanet_ecommerce_member')->where( array('we_openid' => $data['openid']) )->find();
		$result = array('code' => 0, 'token' => $token,'openid' =>$data['openid'],'member_info'=>$member_info,);
		echo json_encode($result);
		die();
	}


	public function applogin_do()
	{
		$gpc = I('request.');

		$token =  $gpc['token'];

		$nickName = $gpc['nickName'];

		$avatarUrl = $gpc['avatarUrl'];


		$share_id = $gpc['share_id'];
		$community_id = $gpc['community_id'];

		$caceh_data = S('wepro_'.$token);


		$openid = $caceh_data['wepro_openid_'.$token];
		$expires_time = $caceh_data['wepro_expires_time_'.$token];
		$session_key = $caceh_data['wepro_session_key_'.$token];
		$unionid = $caceh_data['wepro_unionid_'.$token];


		$orign_nickname = $nickName;
		$nickName = \Lib\Weixin\WeChatEmoji::clear($nickName);

		$nickName = trim($nickName);


		if( empty($openid) )
		{
			echo json_encode(array('code' =>1,'member_id' => 0));
			die();
		}



		$member_info = M('eaterplanet_ecommerce_member')->where( array('we_openid' => $openid) )->find();

		if( !empty($unionid) && empty($member_info) )
		{
			$member_info = M('eaterplanet_ecommerce_member')->where( array('unionid' => $unionid) )->find();
		}

		$isblack = 0;
		//是否可以领取礼包： 1、可以，0、不可以，2、老用户, 3、老用户且有邀请人
		$is_can_collect_gift = 0;

		$is_invite_open_status = D('Home/Front')->get_config_by_name('is_invite_open_status');

		if(!empty($member_info) )
		{
			 $data = array();

			 $data['we_openid'] = trim($openid);
			 $data['avatar'] = trim($avatarUrl);
			 $data['username'] = trim($nickName);

			 $data['full_user_name'] = base64_encode($orign_nickname);

	         $data['last_login_time']	=	time();
			 $data['last_login_ip']	=	get_client_ip();
			if(!empty($is_invite_open_status) && $is_invite_open_status == 1) {
				//邀新有礼
				if (empty($member_info['share_id'])) {
					if (intval($share_id) > 0 && $share_id != $member_info['member_id']) {
						$data['share_id'] = intval($share_id);
						//保存邀请记录
						$is_can_collect_gift = D('Home/Invitegift')->insertInvitegiftRecord($share_id, $member_info['member_id'], 1);
					}
				} else {
					$is_can_collect_gift = 3;
				}
			}
			 M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_info['member_id'] ) )->save(  $data );

			/**
			ims_eaterplanet_ecommerce_car
			**/


			$old_token_info = M('eaterplanet_ecommerce_weprogram_token')->where( array('member_id' => $member_info['member_id'] ) )->find();

			if( !empty($old_token_info) )
			{
				M('eaterplanet_ecommerce_car')->where( array('token' => $old_token_info['token'] ) )->delete();
			}


			M('eaterplanet_ecommerce_weprogram_token')->where( array('member_id' => $member_info['member_id']) )->delete();


			$member_id  = $member_info['member_id'];

			$weprogram_token_data = array();
			$weprogram_token_data['token'] = $token;
			$weprogram_token_data['member_id'] = $member_id;
			$weprogram_token_data['session_key'] = $session_key;
			$weprogram_token_data['expires_in'] = $expires_time;

			M('eaterplanet_ecommerce_weprogram_token')->add($weprogram_token_data);

			$isblack = $member_info['isblack'];

		}else {
			$data = array();

			$data['openid'] = $openid;
			$data['we_openid'] = trim($openid);
			$data['unionid'] = trim($unionid);
			$data['reg_type'] = 'weprogram';
			$data['username']=trim($nickName);
			$data['avatar']=trim($avatarUrl);
			$data['last_login_time']=time();
			$data['create_time']	=	time();
			$data['last_login_ip']	=	get_client_ip();
			$data['full_user_name']	=	base64_encode($orign_nickname);
			$data['account_money']	= 0;
			$data['score']	= 0;

			if( $share_id > 0 )
			{
				$commiss_level = D('Home/Front')->get_config_by_name('commiss_level');

				if( !empty($commiss_level) && $commiss_level > 0)
				{
					//开启分销，判断有没有开启分享，跟上级是否分销


					$share_member_info = M('eaterplanet_ecommerce_member')->field('comsiss_flag,comsiss_state')->where( array('member_id' => $share_id ) )->find();

					if( $share_member_info['comsiss_flag'] == 1 && $share_member_info['comsiss_state'] ==1 )
					{
						//是分销身份
						$data['agentid']	=	$share_id;
					}else{
						//看当前是否开启分享判断，如果没有开启，那么分享id就是0
						$commiss_sharemember_need = D('Home/Front')->get_config_by_name('commiss_sharemember_need');
						if( empty($commiss_sharemember_need) || $commiss_sharemember_need == 0 )
						{
							//$share_id = 0;
						}
					}

				}else{
					//未开启分销：
					//$share_id = 0;
				}
			}

			$data['share_id'] = intval($share_id);

            //是否开启需要审核
			$is_user_shenhe = D('Home/Front')->get_config_by_name('is_user_shenhe');
			if( !empty($is_user_shenhe) && $is_user_shenhe > 0){
				$data['is_comsiss_audit'] = 1;
			}else{
				$data['is_comsiss_audit'] = 0;
			}

            if( !empty($is_user_shenhe) && $is_user_shenhe == 1 )
            {
                $data['is_apply_state'] = 0;
            }else{
                $data['is_apply_state'] = 1;
            }


			$member_id = M('eaterplanet_ecommerce_member')->add( $data );

			if($community_id > 0) {
				D('Seller/Community')->in_community_history($member_id, $community_id);
			}

			$weprogram_token_data = array();
			$weprogram_token_data['token'] = $token;
			$weprogram_token_data['member_id'] = $member_id;
			$weprogram_token_data['session_key'] = $session_key;
			$weprogram_token_data['expires_in'] = $expires_time;

			M('eaterplanet_ecommerce_weprogram_token')->add($weprogram_token_data);

			if( $share_id > 0 )
			{
				$commiss_level = D('Home/Front')->get_config_by_name('commiss_level');

				if( !empty($commiss_level) && $commiss_level > 0)
				{
					//如果上级已经是分销了。那么就直接划到上级的名下

					//检测是否填写过表单

					$share_member_info = M('eaterplanet_ecommerce_member')->field('is_writecommiss_form,comsiss_flag,comsiss_state')->where( array('member_id' =>$share_id ) )->find();

					if( !empty($share_member_info) )
					{
						if(  $share_member_info['comsiss_flag'] == 1  && $share_member_info['comsiss_state'] == 1 )
						{
							M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->save( array('agentid' => $share_id ) );
						}
					}

				}
				if(!empty($is_invite_open_status) && $is_invite_open_status == 1){
					//保存邀请记录
					$is_can_collect_gift = D('Home/Invitegift')->insertInvitegiftRecord($share_id,$member_id, 0);
				}
			}


			/**
			if($share_id > 0)
			{
				$share_member = M('member')->field('we_openid')->where( array('member' => $share_id) )->find();

				$member_formid_info = M('member_formid')->where( array('member_id' => $share_id, 'state' => 0) )->find();
				//更新
				if(!empty($member_formid_info))
				{
					$template_data['keyword1'] = array('value' => $data['name'], 'color' => '#030303');
					$template_data['keyword2'] = array('value' => '普通客户', 'color' => '#030303');
					$template_data['keyword3'] = array('value' => date('Y-m-d H:i:s'), 'color' => '#030303');
					$template_data['keyword4'] = array('value' => '恭喜你，获得一位新成员', 'color' => '#030303');

					$pay_order_msg_info =  M('config')->where( array('name' => 'wxprog_member_take_in') )->find();
					$template_id = $pay_order_msg_info['value'];
					$url =C('SITE_URL');
					$pagepath = 'pages/dan/me';
					send_wxtemplate_msg($template_data,$url,$pagepath,$share_member['we_openid'],$template_id,$member_formid_info['formid']);
					M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
				}

			}
			**/
		}

		//如果开启单团长模式，则自动选择默认团长
		$open_danhead_model = D('Home/Front')->get_config_by_name('open_danhead_model');

		if( empty($open_danhead_model) )
		{
			$open_danhead_model = 0;
		}
		if($open_danhead_model == 1 && $member_id > 0 )
		{

			$default_head = M('eaterplanet_community_head')->field('id')->where( array('is_default' => 1) )->find();

			if( !empty($default_head) )
			{
				D('Seller/Community')->in_community_history($member_id, $default_head['id']);
			}
		}


        //是否开启需要强制收集表单


        //是否强制手机
        $isparse_formdata = 0;

        $is_get_formdata = D('Home/Front')->get_config_by_name('is_get_formdata');
        if( isset($is_get_formdata) && $is_get_formdata == 1 )
        {
            $now_member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id))->find();
            if( $now_member_info['is_apply_state'] != 1 && $now_member_info['is_write_form'] != 1 )
            {
                $isparse_formdata = 1;
            }
        }

        echo json_encode(array('code' =>1,'member_id' => $member_id ,'isblack' => $isblack , 'isparse_formdata' => $isparse_formdata, 'is_can_collect_gift'=>$is_can_collect_gift ));
        die();


	}




	/**
		获取版权信息
	**/
	public function get_copyright()
	{
		$gpc = I('request.');


		$info = M('eaterplanet_ecommerce_config')->field('value')->where( array('name' => 'footer_copyright_desc') )->find();

		$common_header_backgroundimage = D('Home/Front')->get_config_by_name('user_header_backgroundimage');
		if( !empty($common_header_backgroundimage) )
		{
			$common_header_backgroundimage = tomedia($common_header_backgroundimage);
		}

		$is_show_about_us = D('Home/Front')->get_config_by_name('is_show_about_us');

		// 余额开关
		$is_open_yue_pay = D('Home/Front')->get_config_by_name('is_open_yue_pay');
		if( empty($is_open_yue_pay) )
		{
			$is_open_yue_pay = 0;
		}

		// 积分开关
		$is_show_score = D('Home/Front')->get_config_by_name('is_show_score');
		if( empty($is_show_score) )
		{
			$is_show_score = 0;
		}

		// 订单图标
		$user_order_menu_icons = D('Home/Front')->get_config_by_name('user_order_menu_icons');
		if( !empty($user_order_menu_icons) )
		{
			$user_order_menu_icons = unserialize($user_order_menu_icons);
			foreach ($user_order_menu_icons as &$v) {
				if(!empty($v) )
					$v = tomedia($v);
			}
		}

		// 菜单图片
		$user_tool_icons = D('Home/Front')->get_config_by_name('user_tool_icons');
		if( !empty($user_tool_icons) )
		{
			$user_tool_icons = unserialize($user_tool_icons);
			foreach ($user_tool_icons as &$v) {
				if(!empty($v) )
					$v = tomedia($v);
			}
		}

		//是否关闭团长申请
		$close_community_apply_enter = D('Home/Front')->get_config_by_name('close_community_apply_enter');
		// 退出登录
		$ishow_user_loginout_btn = D('Home/Front')->get_config_by_name('ishow_user_loginout_btn');
		// 商户自定义名称
		$supply_diy_name = D('Home/Front')->get_config_by_name('supply_diy_name');
		//商户申请
		$enabled_front_supply = D('Home/Front')->get_config_by_name('enabled_front_supply');
		if( empty($enabled_front_supply) )
		{
			$enabled_front_supply = 0;
		}
		// 客服开关
		$user_service_switch = D('Home/Front')->get_config_by_name('user_service_switch');
		// 提货码显示方式
		$fetch_coder_type = D('Home/Front')->get_config_by_name('fetch_coder_type');
		// 我的拼单
		$show_user_pin = D('Home/Front')->get_config_by_name('show_user_pin');

		$commiss_diy_name = D('Home/Front')->get_config_by_name('commiss_diy_name');


		$show_user_change_comunity = D('Home/Front')->get_config_by_name('show_user_change_comunity');
		$show_user_change_comunity_map = D('Home/Front')->get_config_by_name('show_user_change_comunity_map');

		//是否单团长模式begin

		$open_danhead_model = D('Home/Front')->get_config_by_name('open_danhead_model');

		if( empty($open_danhead_model) )
		{
			$open_danhead_model = 0;
		}

		$default_head_info = array();

		if( $open_danhead_model == 1 )
		{

			$default_head = M('eaterplanet_community_head')->field('id')->where( array('is_default' => 1) )->find();

			if( !empty($default_head) )
			{
				$default_head_info = D('Home/Front')->get_community_byid($default_head['id'], $where);
			}
		}

		//是否单团长模式end


		//客户中心群接龙开关begin
		$is_open_solitaire  = D('Home/Front')->get_config_by_name('is_open_solitaire');
		if( empty($is_open_solitaire) )
		{
			$is_open_solitaire = 0;
		}
		//客户中心群接龙开关 end

		$user_top_font_color = D('Home/Front')->get_config_by_name('user_top_font_color');
		$excharge_nav_name = D('Home/Front')->get_config_by_name('excharge_nav_name');

		$hide_community_change_btn = D('Home/Front')->get_config_by_name('hide_community_change_btn');
		$hide_community_change_word = D('Home/Front')->get_config_by_name('hide_community_change_word');

		$close_community_index = D('Home/Front')->get_config_by_name('close_community_index');

		echo json_encode(
			array(
				'code' => 0,
				'data' => $info['value'],
				'common_header_backgroundimage' => $common_header_backgroundimage,
				'is_show_about_us'=> $is_show_about_us,
				'is_show_score' => $is_show_score,
				'is_open_yue_pay' => $is_open_yue_pay,
				'user_order_menu_icons' => $user_order_menu_icons,
				'user_tool_icons' => $user_tool_icons,
				'close_community_apply_enter' => $close_community_apply_enter,
				'ishow_user_loginout_btn' => $ishow_user_loginout_btn,
				'supply_diy_name' => $supply_diy_name,
				'enabled_front_supply' => $enabled_front_supply,
				'user_service_switch' => $user_service_switch,
				'fetch_coder_type' => $fetch_coder_type,
				'show_user_pin' => $show_user_pin,
				'commiss_diy_name' => $commiss_diy_name,
				'show_user_change_comunity' => $show_user_change_comunity,
				'show_user_change_comunity_map' => $show_user_change_comunity_map,
				'open_danhead_model' => $open_danhead_model,
				'default_head_info'  => $default_head_info,
				'is_open_solitaire'  => $is_open_solitaire,
				'user_top_font_color' => $user_top_font_color,
				'excharge_nav_name' => $excharge_nav_name,
				'hide_community_change_btn' => $hide_community_change_btn,
				'hide_community_change_word' => $hide_community_change_word,
				'close_community_index' => $close_community_index
			)
		);


	}

	public function get_about_us(){

		$about_us = D('Home/Front')->get_config_by_name('personal_center_about_us');

		echo json_encode( array('code' =>0, 'data' => htmlspecialchars_decode( $about_us) ) );
		die();
	}

	// 更新资料
	public function update_user_info(){
		$_GPC = I('request.');

		$result = array();
		$result['code'] = 1;


		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

	    $member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			// 未登录
			echo json_encode( array('code' => 2) );
			die();
		}


		$param = array();
		$param['username'] = $_GPC['nickName'];
		$param['avatar'] = $_GPC['avatarUrl'];
		$member_id = $_GPC['memberId'];

		if($member_id && !empty($param['username']) && !empty($param['avatar']) ){

			M('eaterplanet_ecommerce_member')->where(  array('member_id' => $member_id) )->save($param);
			$result['code'] = 0;
		}

		echo json_encode($result);
		die();
	}


	//获取账户余额
	public function get_account_money(){
		$gpc = I('request.');

		$result = array();
		$result['code'] = 1;
		$result['data'] = 0;
		$result['msg'] = '';

		$token =  $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			$result['msg'] = '未登录';
			echo json_encode( $result ); //未登录
			die();
		}

	    $member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		$chargetype_list = M('eaterplanet_ecommerce_chargetype')->order( 'id asc' )->select();

		if( empty($chargetype_list) )
		{
			$chargetype_list  = array();
		}

		$excharge_nav_name = D('Home/Front')->get_config_by_name('excharge_nav_name');
		$result['excharge_nav_name'] = $excharge_nav_name;


		if(!empty($member_info)) {
			$result['code'] = 0;
			$result['data'] = $member_info['account_money'];

			$result['chargetype_list'] = $chargetype_list;
			$member_charge_publish = D('Home/Front')->get_config_by_name('member_charge_publish');
			$result['member_charge_publish'] = htmlspecialchars_decode($member_charge_publish);
			$result['recharge_get_money'] = D('Home/Front')->get_config_by_name('recharge_get_money');

		}

		echo json_encode($result);
		die();
	}


	//end...

	//----begin---
	/**
	配送员收入明细
	controller:  user.get_user_distribution_order
	token,
	page
	返回：code=0 有数据，code=1 没有数据。
		  data 配送收入列表，
	 **/
	public function get_user_distribution_order()
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

		$orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

		if( $orderdistribution_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
			die();
		}

		$per_page = 20;
		$page = isset($_GPC['page']) ? $_GPC['page'] : 1;
		$offset = ($page - 1) * $per_page;
		$list = array();
		$sql = "select id,order_id,state,shipping_money,is_statement,addtime from  ".C('DB_PREFIX')."eaterplanet_ecommerce_orderdistribution_order "
			 . " where orderdistribution_id = ".$orderdistribution_id." order by id desc limit {$offset},{$per_page}";

		$list = M()->query($sql);
		if( !empty($list) )
		{
			foreach($list as &$value)
			{
				$value['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
				$order_info = M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $value['order_id'] ) )->find();
				$value['order_num_alias'] = $order_info['order_num_alias'];
			}
			echo json_encode( array('code' => 0, 'data' => $list) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}

    //客户保存 收集表单信息
    public function save_formData()
    {
        $gpc = I('request.');

        $token =  $gpc['token'];

        $weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
        {
            echo json_encode( array('code' => 1) );
            die();
        }

        $member_id = $weprogram_token['member_id'];


        $form_username = I('request.form_username','','htmlspecialchars');
        $form_mobile = I('request.form_mobile','','htmlspecialchars');

        if( empty($form_username) )
        {
            echo json_encode( array('code' => 1, 'message' => '真实姓名不能为空') );
            die();
        }
        if( empty($form_mobile) )
        {
            echo json_encode( array('code' => 1, 'message' => '手机号不能为空') );
            die();
        }

        $form_info = array();
        $form_info['username'] = $form_username;
        $form_info['mobile'] = $form_mobile;

        $up_data = array();
        $up_data['is_write_form'] = 1;
        $up_data['form_info'] = serialize( $form_info );

        M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->save( $up_data );

        echo json_encode( array('code' => 0) );
        die();
    }

	/**
	 * 领券中心
	 */
	public function collect_voucher()
	{
		$_GPC = I('request.');
		$token =  $_GPC['token'];
		$coupon_id =  $_GPC['coupon_id'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$member_id = $weprogram_token['member_id'];
		if( empty($member_id) )
		{
			$result['code'] = 3;
			$result['msg'] = '登录失效';
			echo json_encode($result);
			die();
		}
		if( empty($coupon_id) )
		{
			echo json_encode(array('code'=>1,'msg'=>'优惠券数据错误'));
			die();
		}
		$category_list = M('eaterplanet_ecommerce_coupon_category')->field('id,name,status')->where( array('merchid' => 0) )->order('id desc')->select();
		$category = array();
		foreach($category_list as $val)
		{
			$category[$val['id']] = $val;
		}
		$params = "id in (".$coupon_id.") and ((timelimit=1 and end_time>".time().") or timelimit=0)";
		$list = M('eaterplanet_ecommerce_coupon')->where($params)->order('add_time desc')->select();

		$result_list = array();
		$k = 0;
		$nowtime = time();
		foreach($list as $key => $val)
		{

			//验证买家是否有 优惠券
			$count = M('eaterplanet_ecommerce_coupon_list')->where(array('voucher_id'=>$val['id'],'user_id'=>$member_id))->count();
			if($count > 0){
				$val['is_collect'] = 1;
			}else{
				$val['is_collect'] = 0;
			}
			//判断买家 是否未使用优惠券
			//is_use 0 未使用，1、已使用，2 用户不存在优惠券数据
			if($val['is_collect'] == 1){
				$no_use_count = M('eaterplanet_ecommerce_coupon_list')->where(array('voucher_id'=>$val['id'],'user_id'=>$member_id,'consume'=>'N'))->count();
				if($no_use_count > 0){
					$val['is_use'] = 0;
				}else{
					$val['is_use'] = 1;
				}
			}else{
				$val['is_use'] = 2;
			}

			//判断买家 获取的优惠券是否过期
			//is_over 0、未过期，1、已过期，2 用户不存在优惠券数据
			// if($val['end_time'] < $nowtime){
			// 	$val['is_over'] = 1;
			// }else{
				if($val['is_collect'] == 1){
					$no_over_count = M('eaterplanet_ecommerce_coupon_list')->where(array('voucher_id'=>$val['id'],'user_id'=>$member_id))->where('end_time > '.$nowtime)->count();
					if($no_over_count > 0){
						$val['is_over'] = 0;
					}else{
						$val['is_over'] = 1;
					}
				}else{
					$val['is_over'] = 2;
				}
			// }

			$val['tag'] = $category[$val['catid']]['name'];
			$val['begin_time'] = date('Y.m.d H:i:s', $val['begin_time']);
			$val['end_time']   = date('Y.m.d H:i:s', $val['end_time']);

			$result_list[$k] = $val;
			$k++;
		}

		if( empty($result_list) )
		{
			echo json_encode(array('code'=>1,'msg'=>'暂无优惠券数据'));
			die();
		}else {
			echo json_encode( array('code' =>0, 'list' => $result_list) );
		}

	}

	/**
	 * 获取用户真实姓名和手机号
	 */
	public function get_realname_tel() {
		$_GPC = I('request.');
		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(empty($weprogram_token) || empty($weprogram_token['member_id']))
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->field('realname,telephone')->where( array('member_id' => $member_id) )->find();

		$result = array('code' => 0, 'data' =>$member_info);
		echo json_encode($result);
		die();
	}

	public function update_realname_tel() {
		$_GPC = I('request.');
		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(empty($weprogram_token) || empty($weprogram_token['member_id']))
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$member_id = $weprogram_token['member_id'];
		$result['code'] = 2;

		$param = array();
		$param['realname'] = $_GPC['realname'];
		$param['telephone'] = $_GPC['telephone'];

		if( $member_id ){
			M('eaterplanet_ecommerce_member')->where(  array('member_id' => $member_id) )->save($param);
			$result['code'] = 0;
		} else {
			$result['message'] = "用户不存在";
		}

		echo json_encode($result);
		die();
	}
}
