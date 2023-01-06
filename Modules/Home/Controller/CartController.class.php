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

class CartController extends CommonController {

	public function add()
	{
		global $_W;
		global $_GPC;

		$data = array();
		$data['goods_id'] = $_GPC['goods_id'];
		$data['community_id'] = $_GPC['community_id'];
		$data['quantity'] = $_GPC['quantity'];
		$data['sku_str'] = $_GPC['sku_str'];
		if($_GPC['sku_str'] == 'undefined')
		{
			$_GPC['sku_str'] = '';
			$data['sku_str']  = '';
		}




		$data['buy_type'] = $_GPC['buy_type'];
		$data['pin_id'] = $_GPC['pin_id'];
		$data['is_just_addcar'] = $_GPC['is_just_addcar'];

		if( !isset($data['buy_type']) || empty($data['buy_type']) )
		{
		  $data['buy_type'] = 'dan';
		}
		$token = $_GPC['token'];



		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;

		$weprogram_token = pdo_fetch("select member_id from ".tablename('eaterplanet_ecommerce_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);

		$member_id = $weprogram_token['member_id'];


		$is_just_addcar = empty($data['is_just_addcar']) ? 0: 1;

		$goods_id = $data['goods_id'];
		if( empty($member_id))
		{
		    $result = array('code' =>4);
		    echo json_encode($result);
		    die();
		}

		if (isset($data['goods_id'])) {
			$goods_id = $data['goods_id'];
		} else {
			$goods_id = 0;
		}

		$goods_param = array();
		$goods_sql = "select * from ".tablename('eaterplanet_ecommerce_goods')." where uniacid=:uniacid and id=:id limit 1";

		$product = pdo_fetch($goods_sql, array(':uniacid' => $_W['uniacid'], ':id' => $goods_id));

		if( $product['grounding'] != 1)
		{
			$json['code'] =6;
			$json['msg']='商品已下架!';
			echo json_encode($json);
			die();
		}


		//判断是否积分兑换
		if( $product['type'] == 'integral')
		{
			//判断积分是否足够 member_id 暂时关闭以下代码
			/**
			$integral_model = D('Seller/Integral');
			$check_result = $integral_model->check_user_score_can_pay($member_id, $data['sku_str'], $goods_id);

			if( $check_result['code'] == 1 )
			{
				$json['code'] =6;
				$json['msg']='剩余'.$check_result['cur_score'].'积分，积分不足!';
				echo json_encode($json);
				die();
			}
			**/
		}

		//6
		if($is_just_addcar == 1)
		{
			if($product['pick_just'] > 0)
			{
				$json['code'] =6;
				$json['msg']='自提商品，请立即购买';
				echo json_encode($json);
				die();
			}
		}

		//商品存在
		if($product){

			$cart= load_model_class('car');

			if (isset($data['quantity'])) {
				$quantity = $data['quantity'];
			} else {
				$quantity = 1;
			}

			$option = array();

			if( !empty($data['sku_str'])){
			    $option = explode('_', $data['sku_str']);
			}

            $cart_goods_quantity = $cart->get_wecart_goods($goods_id,$data['sku_str'],$data['community_id'] ,$token);

			$json=array('code' =>0);
			//$goods_model = D('Home/Goods');
			$goods_quantity=$cart->get_goods_quantity($goods_id);


			//检测商品限购 6

			$can_buy_count = D('Home/Front')->check_goods_user_canbuy_count($member_id, $goods_id);

			$goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'total_limit_count');

			if($can_buy_count == -1)
			{
				$json['code'] =6;
				//$json['msg']='已经不能再买了';

				$json['msg']='每人最多购买'.$goods_description['total_limit_count'].'个哦';

				echo json_encode($json);
				die();
			}else if($can_buy_count >0 && $quantity >$can_buy_count)
			{
				$json['code'] =6;
				$json['msg']='您还能购买'.$can_buy_count.'份';
				echo json_encode($json);
				die();
			}

			//已加入购物车的总数

			if($goods_quantity<$quantity+$cart_goods_quantity){
			    $json['code'] =3;
			    if ($goods_quantity==0) {
			    	$json['msg']='已抢光';
			    }else{
					$json['msg']='商品数量不足，剩余'.$goods_quantity.'个！！';
			    }

				echo json_encode($json);
				die();
			}
			//rela_goodsoption_valueid

			if(!empty($option))
			{
				$mul_opt_arr = array();

				//ims_

				$mult_sql = "select * from ".tablename('eaterplanet_ecommerce_goods_option_item_value')."
							where uniacid=:uniacid and option_item_ids = :sku_str and goods_id =:goods_id limit 1 ";

				$goods_option_mult_value = pdo_fetch($mult_sql, array(':uniacid' => $_W['uniacid'],':sku_str' =>$data['sku_str'],':goods_id' => $goods_id ));

				if( !empty($goods_option_mult_value) )
				{
					if($goods_option_mult_value['stock']<$quantity+$cart_goods_quantity){
					    $json['code'] =3;
						$json['msg']='商品数量不足，剩余'.$goods_option_mult_value['stock'].'个！！';
						echo json_encode($json);
						die();
					}
				}
			}
			//buy_type

		   // $this->clear_all_cart(); $data['community_id']
		    $format_data_array = array('quantity' => $quantity,'community_id' => $data['community_id'] ,'goods_id' => $goods_id,'sku_str'=>$data['sku_str'],'buy_type' =>$data['buy_type']);
			//区分活动商品还是普通商品。做两个购物车，活动商品是需要直接购买的，单独购买商品加入正常的购物车TODO....
		    //is_just_addcar 0  1
			if($data['buy_type'] == 'dan' && $is_just_addcar == 0)
		    {

				//$cart->removedancar($token);
				//清空一下购物车
				//singledel
				$format_data_array['is_just_addcar'] = 0;
				$format_data_array['singledel'] = 1;

		        $cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str'],$data['community_id']);
				$total=$cart->count_goodscar($token ,$data['community_id']);
		    }
			else if($data['buy_type'] == 'dan' && $is_just_addcar == 1)
			{
				//singledel
				$format_data_array['is_just_addcar'] = 1;
				$format_data_array['singledel'] = 1;
				$cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str'],$data['community_id']);
				$total=$cart->count_goodscar($token, $data['community_id']);
			}
			else {
		        //buy_type:pin  活动购物车。
		        $pin_id = isset($data['pin_id']) ? $data['pin_id'] : 0;

				//lottery
				if( $product['type'] == 'lottery' && $product['type'] == 'lottery' )
				{
					/**
					//等待把抽奖的活动打开
					$now_time = time();
					$lottery_goods_info =  M('lottery_goods')->where( array('goods_id' => $goods_id) )->find();

					if($lottery_goods_info['end_time'] < $now_time)
					{
						$json['code'] =6;
						$json['msg']='抽奖活动已结束';
						echo json_encode($json);
						die();
					}
					**/
				}

				//检测商品是否老带新，新人才能参团
				if($pin_id > 0 )
				{
					//等待把老带新的活动打开
					/**
					if($product['type'] == 'newman')
					{
						$new_mamn_buy = $goods_model->check_goods_new_manbug($member_id);
						if($new_mamn_buy>0)
						{
							$json['code'] =5;
							$json['msg']='该商品只能新人参团';
							echo json_encode($json);
							die();
						}
					}
					**/
				}

		        $format_data_array['pin_id'] = $pin_id;

		        $cart->add_activitycar($token, $goods_id,$format_data_array,$data['sku_str']);
				$total=$cart->count_activitycar($token);
		    }


			$car_total_sql = "select * from ".tablename('eaterplanet_ecommerce_car')." where token=:token and community_id=:community_id and uniacid=:uniacid and carkey ='cart_total' ";
			$carts = pdo_fetch($car_total_sql, array(':token' => $token,':community_id' => $data['community_id'],':uniacid' => $_W['uniacid']));


			if( !empty($carts) )
			{
				$car_data = array();
				$car_data['format_data'] = serialize(array('quantity' => $total));
				$car_data['modifytime'] = 1;

				pdo_update('eaterplanet_ecommerce_car', $car_data, array('token' => $token,'community_id' => $data['community_id'],'carkey' => 'cart_total'));

			} else{

				$car_data = array();
				$car_data['token'] = $token;
				$car_data['uniacid'] = $_W['uniacid'];
				$car_data['community_id'] = $data['community_id'];
				$car_data['carkey'] = 'cart_total';
				$car_data['format_data'] = serialize(array('quantity' => $total));
				pdo_insert('eaterplanet_ecommerce_car', $car_data);
			}
			//session('cart_total',$total);
			$json ['code']  = 1;
			if( $data['buy_type'] != 'dan' )
			{
			    $json ['code']  = 2;
			}
			$json['success']='成功加入购物车！！';
			$json['total']=$total;
			echo json_encode($json);
			die();
		}

	}

	//显示购物车中商品列表
	function show_cart_goods(){

		global $_W;
		global $_GPC;

		$token = $_GPC['token'];
		$community_id = $_GPC['community_id'];


		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		$weprogram_token = pdo_fetch("select member_id from ".tablename('eaterplanet_ecommerce_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);

		$member_id = $weprogram_token['member_id'];

		$buy_type = isset($_GPC['buy_type']) ? $_GPC['buy_type']: 'dan';

		if( empty($member_id) )
		{
			  //需要登录
			  echo json_encode( array('code' =>5) );
			  die();
		}

		$cart =  load_model_class('car');

		$goods = $cart->get_all_goodswecar($buy_type, $token, 0, $community_id);


		$seller_goodss = array();

		foreach($goods as $key => $val)
		{
			//$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();
			//$seller_goodss[ $goods_store_field['store_id'] ]['goods'][$key] = $val;
			$seller_goodss[ 1 ]['goods'][$key] = $val;
		}

		$ck_goodstype_count = 0;

		foreach($seller_goodss as $store_id => $val)
		{
			//total
			$seller_voucher_list = array();
			$seller_total_fee = 0;
			$total_trans_free = 0;

			$tmp_goods = array();

			$is_store_ck = false;

			foreach($val['goods'] as $kk =>$d_goods)
			{
				$seller_total_fee += $d_goods['total'];

				$total_trans_free  += $d_goods[$kk]['trans_free'];
				$val['goods'][$kk] = $d_goods;

				$tp_val = array();
				$tp_val['id'] = $d_goods['goods_id'];
				$tp_val['key'] = $d_goods['key'];
				if($d_goods['singledel'] == 1)
				{
					$tp_val['isselect'] = true;
					$is_store_ck = true;
					$ck_goodstype_count++;
				} else {
					$tp_val['isselect'] = false;
				}

				$tp_val['imgurl'] = $d_goods['image'];
				$tp_val['edit'] = 'inline';
				$tp_val['title'] = $d_goods['name'];
				$tp_val['finish'] = 'none';
				$tp_val['description'] = 'description';

				$option_arr  = array();
				$option_str = "";
				foreach($d_goods['option'] as $option_val)
				{
					$option_arr[] = $option_val['name'].':'.$option_val['value'];
				}
				if(!empty($option_arr))
				{
					$option_str = implode(',', $option_arr);
				}

				$tp_val['goodstype'] = $option_str;
				$tp_val['goodstypeedit'] = $option_str;
				$tp_val['goodsnum'] = $d_goods['quantity'];
				$tp_val['max_quantity'] = $d_goods['max_quantity'];
				$tp_val['cartype'] = 'inline';
				$tp_val['currntprice'] = $d_goods['price'];
				$tp_val['price'] = $d_goods['shop_price'];

				$tmp_goods[] = $tp_val;

			}

			//$store_info = M('seller')->field('s_id,s_true_name,s_logo')->where( array('s_id' => $store_id) )->find();
			//$store_info['s_logo'] = C('SITE_URL').'Uploads/image/'.$store_info['s_logo'];

			$store_info = array('s_true_name' => '','s_id' => 1);
			$s_logo = D('Home/Front')->get_config_by_name('shoplogo');

			if( !empty($s_logo) )
			{
				$s_logo = tomedia($s_logo);
			}

			$val['store_info'] = $store_info;

			$store_data = array();
			$store_data['id'] = $store_info['s_id'];
			if($is_store_ck)
			{
				$store_data['isselect'] = true;
			} else {
				$store_data['isselect'] = false;
			}

			$store_data['shopname'] = $store_info['s_true_name'];
			$store_data['caredit'] = 'inline';
			$store_data['finish'] = 'none';
			$store_data['count'] = '0.00';
			$store_data['goodstype'] = 2;
			$store_data['goodstypeselect'] = 0;
			$store_data['shopcarts'] = $tmp_goods;


			$seller_goodss[$store_id] = $store_data;
			$i++;
		}

		$need_data = array();
		$need_data['code'] = 0;
		$need_data['carts'] = $seller_goodss;

		echo json_encode( $need_data );
		die();

	}

	public function checkout_flushall()
	{
		global $_W;
		global $_GPC;

		$token = $_GPC['token'];

		$community_id = $_GPC['community_id'];

		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		$weprogram_token = pdo_fetch("select member_id from ".tablename('eaterplanet_ecommerce_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);

		$member_id = $weprogram_token['member_id'];

		$data = array();
		$data['car_key'] = $_GPC['car_key'];
		$data['all_keys_arr'] = $_GPC['all_keys_arr'];

		//car_key:cart.6:MTc0:,cart.13:MjcwXzI3Mw==:
		//all_keys_arr:cart.6:MTc0:_1,cart.13:MjcwXzI3Mw==:_1

		$car_key = explode(',', $data['car_key']);
		$all_keys_arr = explode(',', $data['all_keys_arr']) ;

		$save_keys = array();
		if(!empty($all_keys_arr)){
			foreach($all_keys_arr as $val)
			{
				$tmp_val = explode('_', $val);
				$save_keys[ $tmp_val[0] ] = $tmp_val[1];
			}
		}


		$all_cart = pdo_fetchall("select * from ".tablename('eaterplanet_ecommerce_car')." where uniacid=:uniacid and community_id=:community_id and token=:token and carkey like 'cart.%' ",
		array(':uniacid' => $_W['uniacid'],':community_id' => $community_id, ':token' => $token));

		if(!empty($all_cart))
		{
			foreach($all_cart as $val)
			{
				$tmp_format_data = unserialize($val['format_data']);
				$tmp_format_data['singledel'] = 0;
				$tmp_format_data['quantity'] = $save_keys[$val['carkey']];
				pdo_update('eaterplanet_ecommerce_car', array('format_data' => serialize($tmp_format_data) ), array('id' => $val['id'] ,'community_id' => $community_id));
			}
		}

		if(!empty($car_key)){
			foreach( $car_key as $key )
			{
				$car_info = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_car')." where uniacid=:uniacid and community_id=:community_id and carkey=:carkey and token=:token ",
				array(':token' => $token,':carkey' => $key,':uniacid' => $_W['uniacid'], ':community_id' => $community_id));

				if( !empty($car_info) )
				{
					$tmp_format_data = unserialize($car_info['format_data']);
					$tmp_format_data['singledel'] = 1;
					$quantity = $tmp_format_data['quantity'];
					$goods_id = $tmp_format_data['goods_id'];
					$sku_str = $tmp_format_data['sku_str'];

					$check_json = $this->_check_can_buy($member_id, $goods_id,$quantity);

					if($check_json['code'] != 0)
					{
						$tmp_format_data['quantity'] = $check_json['count'];

						pdo_update('eaterplanet_ecommerce_car', array('format_data' => serialize($tmp_format_data) ),
						array('id' =>  $car_info['id'], 'community_id' => $community_id));

						echo json_encode( array('code' => 6,'msg' => $check_json['msg']) );
						die();
					}
					$check_json = $this->_check_goods_quantity($goods_id,$quantity,$sku_str);

					if($check_json['code'] != 0)
					{
						echo json_encode( array('code' => 6,'msg' => $check_json['msg']) );
						die();
					}
					pdo_update('eaterplanet_ecommerce_car', array('format_data' => serialize($tmp_format_data) ),
					array('id' => $car_info['id'], 'community_id' => $community_id));

				}
			}
		}
		echo json_encode( array('code' => 0) );
		die();
	}

	public function del_car_goods()
	{
		global $_W;
		global $_GPC;

		$token = $_GPC['token'];
		$community_id = $_GPC['community_id'];

		$carkey = $_GPC['carkey'];


		$sql_del = "delete from ".tablename('eaterplanet_ecommerce_car')." where uniacid={$_W[uniacid]} and community_id={$community_id} and token='{$token}' and carkey='{$carkey}' ";

		$all_cart = pdo_query($sql_del);

		echo json_encode( array('code' => 0) );
		die();

	}

	public function _check_goods_quantity($goods_id,$quantity,$sku_str)
	{
		global $_W;
		global $_GPC;


		$goods_info = pdo_fetch("select goodsname as name from ".tablename('eaterplanet_ecommerce_goods')." where id=:id and uniacid=:uniacid ",array(':uniacid'=>$_W['uniacid'],':id' => $goods_id));

		$goods_quantity= load_model_class('car')->get_goods_quantity($goods_id);

		$json = array('code' => 0);

		if($goods_quantity<$quantity){
			$json['code'] =3;
			$json['msg']= mb_substr($goods_info['name'],0,4,'utf-8').'...，商品数量不足，剩余'.$goods_quantity.'个！！';

		}else if(!empty($sku_str))
		{
			$mul_opt_arr = array();

			$goods_option_mult_value = pdo_fetch("select stock as quantity  from ".tablename('eaterplanet_ecommerce_goods_option_item_value')." where uniacid=:uniacid and goods_id=:goods_id and option_item_ids=:option_item_ids ", array(':goods_id'=>$goods_id,':option_item_ids' => $sku_str,':uniacid' => $_W['uniacid']));

			if( !empty($goods_option_mult_value) )
			{
				if($goods_option_mult_value['quantity']<$quantity+$cart_goods_quantity){
					$json['code'] =3;
					$json['msg']=mb_substr($goods_info['name'],0,4,'utf-8').'...，商品数量不足，剩余'.$goods_option_mult_value['quantity'].'个！！';
				}
			}
		}
		return $json;
	}

	private function _check_can_buy($member_id, $goods_id,$quantity)
	{
		global $_W;
		global $_GPC;

		$can_buy_count =  D('Home/Front')->check_goods_user_canbuy_count($member_id, $goods_id);

		$goods_info = pdo_fetch("select goodsname as name from ".tablename('eaterplanet_ecommerce_goods')." where id=:id and uniacid=:uniacid ",array(':uniacid'=>$_W['uniacid'],':id' => $goods_id));

		$goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'per_number');



		$json = array();
		if($can_buy_count == -1)
		{
			$json['code'] =6;
			$json['msg']=mb_substr($goods_info['name'],0,4,'utf-8').'...，每人最多购买'.$goods_description['per_number'].'个哦';

		}else if($can_buy_count >0 && $quantity >$can_buy_count)
		{
			$json['code'] =6;
			$json['msg']=mb_substr($goods_info['name'],0,4,'utf-8').'...，您还能购买'.$can_buy_count.'份';
			$json['count']=$can_buy_count;

		}else{
			$json['code'] = 0;
		}
		return $json;
	}

	public function checkout()
	{
		global $_W;
		global $_GPC;


	  $buy_type = isset($_GPC['buy_type']) ? $_GPC['buy_type'] : 'dan';

	  if($buy_type == 'undefined')
	  {
		 $buy_type = 'dan';
	  }

	  $community_id = $_GPC['community_id'];
	  $token = $_GPC['token'];

	  $voucher_id = isset($_GPC['voucher_id']) ? $_GPC['voucher_id'] : 0;

	  $use_quan_str = isset($_GPC['use_quan_str']) ? $_GPC['use_quan_str'] : '';
	  $use_quan_arr = array();

	  if($use_quan_str != '')
	  {
		  $use_quan_arr_tmp = explode('@',$use_quan_str );
		  foreach($use_quan_arr_tmp as $val)
		  {
			 $tmp_arr = explode('_', $val);
			 $use_quan_arr[$tmp_arr[0]] = $tmp_arr[1];
		  }
	  }


		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;

		$weprogram_token = pdo_fetch("select member_id from ".tablename('eaterplanet_ecommerce_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);

		$member_id = $weprogram_token['member_id'];


	  if( empty($member_id) )
	  {
		  //需要登录
		  echo json_encode( array('code' =>5) );
		  die();
	  }

	$cart = load_model_class('car');



	if ((!$cart->has_goodswecar($buy_type,$token,$community_id) ) ) {
		//购物车中没有商品
		echo json_encode( array('code' =>4) );
		die();
	}


	$member_param = array();
	$member_param[':uniacid'] = $_W['uniacid'];
	$member_param[':member_id'] = $member_id;

	$member_info = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_member')." where uniacid=:uniacid and member_id=:member_id",$member_param);



	$goods=$cart->get_all_goodswecar($buy_type, $token,1,$community_id);

	$add_where = array(':member_id'=>$member_id,':uniacid' => $_W['uniacid'] );

	$add_sql = "select * from ".tablename('eaterplanet_ecommerce_address')." where member_id=:member_id  and uniacid=:uniacid order by is_default desc,address_id desc ";

	$address = pdo_fetch($add_sql, $add_where);


	if($address){
		//get_area_info($id)
		$province_info =  D('Home/Front')->get_area_info($address['province_id']);// M('area')->field('area_name')->where( array('area_id' => $address['province_id']) )->find();
		$city_info = D('Home/Front')->get_area_info($address['city_id']);//M('area')->field('area_name')->where( array('area_id' => $address['city_id']) )->find();
		$country_info = D('Home/Front')->get_area_info($address['country_id']);//M('area')->field('area_name')->where( array('area_id' => $address['country_id']) )->find();

		$address['province_name'] = $province_info['name'];
		$address['city_name'] = $city_info['name'];
		$address['country_name'] = $country_info['name'];
	}

	$seller_goodss = array();
	$show_voucher = 0;


	foreach($goods as $key => $val)
	{
		//$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();
		$seller_goodss[ 1 ]['goods'][$key] = $val;
	}

	$quan_model = load_model_class('voucher');
	$pin_model = load_model_class('pin');//D('Home/Pin');


	$voucher_price = 0;
	$is_pin_over = 0;
	//计算优惠券
	foreach($seller_goodss as $store_id => $val)
	{

		$seller_voucher_list = array();
		$seller_total_fee = 0;
		$total_trans_free = 0;
		$is_no_quan = false;
		foreach($val['goods'] as $kk =>$d_goods)
		{
			$seller_total_fee += $d_goods['total'];

			if($buy_type == 'pin' && $d_goods['pin_id'] > 0)
			{
				$is_pin_over = $pin_model->getNowPinState($d_goods['pin_id']);
			}

			$tp_goods_info = pdo_fetch("select type from ".tablename("eaterplanet_ecommerce_goods")." where id=:id and uniacid=:uniacid ",array(':id' => $d_goods['goods_id'], ':uniacid'=>$_W['uniacid']));


			$is_no_quan = true;

			if($tp_goods_info['type'] == 'integral')
			{
				//$is_no_quan = true;
			}


			$d_goods[$kk]['trans_free'] = 0;
			/**
			if($d_goods['shipping']==1)
			{
				//统一运费
				$d_goods[$kk]['trans_free'] = $d_goods['goods_freight'];
			}else {
				//运费模板
				 if(!empty($address))
				{
					$trans_free = load_model_class('transport')->calc_transport($d_goods['transport_id'], $d_goods['quantity'],$d_goods['quantity']*$d_goods['weight'], $address['city_id'] );
				}else{
					$trans_free = 0;
				}
			   $d_goods[$kk]['trans_free'] = $trans_free;
			}
			**/

			$total_trans_free  += $d_goods[$kk]['trans_free'];
			$val['goods'][$kk] = $d_goods;

		}

		$chose_vouche = array();
		$show_voucher = 0;

		if(!$is_no_quan)
		{

			$vouche_list =  $quan_model->get_user_canpay_voucher($member_id,$store_id,$seller_total_fee);


			if(!empty($vouche_list) && empty($use_quan_arr) ) {
				$show_voucher = 1;
				reset($vouche_list);
				$chose_vouche = current($vouche_list);
				$voucher_price += $chose_vouche['credit'];

				$seller_total_fee = round( $seller_total_fee - $chose_vouche['credit'], 2);
			}else if( !empty($vouche_list) &&  !empty($use_quan_arr) )
			{

				foreach($vouche_list as $tmp_voucher)
				{
					if($tmp_voucher['id'] == $use_quan_arr[$store_id])
					{
						$show_voucher = 1;
						$chose_vouche = $tmp_voucher;
						$seller_total_fee = round( $seller_total_fee - $chose_vouche['credit'], 2);
						$voucher_price += $chose_vouche['credit'];
						break;
					}
				}
			}

		}

		$val['chose_vouche'] = $chose_vouche;
		$val['show_voucher'] = $show_voucher;

		$val['voucher_list'] = $vouche_list;
		$val['total'] = $seller_total_fee;

		if($val['total'] < 0)
		{
			$val['total'] = 0;
		}

		$val['trans_free'] = $total_trans_free;



		$s_logo = D('Home/Front')->get_config_by_name('shoplogo');
		if( !empty($s_logo) )
		{
			$s_logo = tomedia( $s_logo );
		}

		$store_info = array('s_id' => 1,'s_true_name' => '','s_logo' => $s_logo );

		$val['store_info'] = $store_info;

		$seller_goodss[$store_id] = $val;
	}

	$trans_free_toal = 0;//运费


	$total_free = 0;
	$is_ziti = 2;

	$pick_up_time = "";
	$pick_up_type = -1;
	$pick_up_weekday = '';
	$today_time = time();

	$arr = array('天','一','二','三','四','五','六');

	$pick_up_arr = array();
	foreach($goods as $key => $good)
	{
		//暂时关闭


		//ims_eaterplanet_ecommerce_goods
		//ims_ eaterplanet_ecommerce_good_common

		$goods_info = pdo_fetch("select pick_up_type,pick_up_modify from ".tablename('eaterplanet_ecommerce_good_common').
						" where uniacid=:uniacid and goods_id=:goods_id ",
						array(':uniacid' => $_W['uniacid'], ':goods_id' => $good['goods_id']));
		if($pick_up_type == -1 || $goods_info['pick_up_type'] > $pick_up_type)
		{
			$pick_up_type = $goods_info['pick_up_type'];

			if($pick_up_type == 0)
			{
				$pick_up_time = date('m-d', $today_time);
				$pick_up_weekday = '周'.$arr[date('w',$today_time)];
			}else if( $pick_up_type == 1 ){
				$pick_up_time = date('m-d', $today_time+86400);
				$pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
			}else if( $pick_up_type == 2 )
			{
				$pick_up_time = date('m-d', $today_time+86400*2);
				$pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
			}else if($pick_up_type == 3)
			{
				$pick_up_time = $goods_info['pick_up_modify'];
			}
		}

		/**
		if($goods_info['pick_just'] >= 1)
		{
			 $pick_up = $goods_info['pick_up'];
			 $is_ziti = $goods_info['pick_just'];
		}
		**/


		$trans_free_toal += $good['goods_freight'];
		$goods[$key]['trans_free'] = $good['goods_freight'];

		/**
		if($good['shipping']==1)
		{
			//统一运费
			$trans_free_toal += $good['goods_freight'];
			$goods[$key]['trans_free'] = $good['goods_freight'];
		}else {
			//运费模板
			 if(!empty($address))
			{
				$trans_free =   load_model_class('transport')->calc_transport($good['transport_id'], $good['quantity'], $good['quantity']*$good['weight'], $address['city_id'] );
			}else{
				$trans_free = 0;
			}

		   $goods[$key]['trans_free'] = $trans_free;
			$trans_free_toal +=$trans_free;

		}
		**/

		$total_free += $good['total'];
	}

	//暂时关闭自提代码
	/**
	if(!empty($pick_up))
	{
		$pick_up = unserialize($pick_up);
		$pick_up_ids = implode(',',$pick_up);
		$pick_up_arr = M('pick_up')->where( array('id'=>array('in',$pick_up_ids)) )->select();
	}
	**/


	$pick_up_name = '';
	$pick_up_mobile = '';



	if($is_ziti >= 1)
	{
		//寻找上一个订单的自提电话 自提姓名

		$last_order_info = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_order').
							" where uniacid=:uniacid and member_id=:member_id and delivery =:delivery order by order_id desc ",
							array(':uniacid' => $_W['uniacid'], ':member_id' => $member_id, ':delivery' => 'pickup' ));

		if(!empty($last_order_info))
		{
			$pick_up_name = $last_order_info['shipping_name'];
			$pick_up_mobile = $last_order_info['telephone'];
		}
	}
	/**
	**/

	$need_data = array();
	$need_data['code'] = 1;

	$need_data['pick_up_time'] = $pick_up_time;
	$need_data['pick_up_type'] = $pick_up_type;
	$need_data['pick_up_weekday'] = $pick_up_weekday;

	$need_data['is_pin_over'] = $is_pin_over;
	$need_data['is_integer'] = 0;//$is_no_quan ? 1: 0;
	$need_data['pick_up_arr'] = $pick_up_arr;
	$need_data['is_ziti'] = 2;

	$need_data['ziti_name'] = $pick_up_name;
	$need_data['ziti_mobile'] = $pick_up_mobile;
	$need_data['seller_goodss'] = $seller_goodss;
	$need_data['show_voucher'] = $show_voucher;

	$need_data['buy_type'] = $buy_type;
	$need_data['address'] = $address;
	$need_data['trans_free_toal'] = $trans_free_toal;

	$dispatching = isset($_GPC['dispatching']) ? $_GPC['dispatching']:'pickup';
	//is_ziti == 2
	if($dispatching == 'express')
	{
		$need_data['total_free'] = $total_free + $trans_free_toal - $voucher_price;
	}else{
		$need_data['total_free'] = $total_free  - $voucher_price;
	}
	if($is_ziti == 2)
	{
		$need_data['total_free'] = $total_free  - $voucher_price;
	}



	if($need_data['total_free'] < 0)
	{
		$need_data['total_free'] = 0;
	}

	//判断是否可以余额支付

	//暂时关闭 客户余额功能
	/**
	$is_yue_open_info =	M('config')->where( array('name' => 'is_yue_open') )->find();
	$is_yue_open =  $is_yue_open_info['value'];
	**/

	$is_yue_open = 0;

	$need_data['is_yue_open'] = $is_yue_open;

	$need_data['can_yupay'] = 0;

	//暂时关闭 客户余额功能
	/**
	if($is_yue_open == 1 && $need_data['total_free'] >=0 && $member_info['account_money'] >= $need_data['total_free'])
	{
		$need_data['can_yupay'] = 1;
	}
	**/

	$need_data['yu_money'] = $member_info['account_money'];
	$need_data['goods'] = $goods;

	echo json_encode($need_data);
	die();
}

public function sub_order()
{
	global $_W;
	global $_GPC;

	$token = $_GPC['token'];

	$token_param = array();
	$token_param[':uniacid'] = $_W['uniacid'];
	$token_param[':token'] = $token;

	$weprogram_token = pdo_fetch("select member_id from ".tablename('eaterplanet_ecommerce_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);

	$member_id = $weprogram_token['member_id'];


	$data_s  = array();
	$data_s['pay_method'] = $_GPC['wxpay'];
	$data_s['buy_type'] = $_GPC['buy_type'];
	$data_s['pick_up_id'] = $_GPC['pick_up_id'];
	$data_s['dispatching'] = $_GPC['dispatching'];
	$data_s['ziti_name'] = $_GPC['ziti_name'];
	$data_s['quan_arr'] = $_GPC['quan_arr'];
	$data_s['comment'] = $_GPC['comment'];
	$data_s['ziti_mobile'] = $_GPC['ziti_mobile'];
	$data_s['ck_yupay'] = $_GPC['ck_yupay'];



	$json=array();

	$pay_method = $data_s['pay_method'];//支付类型
	$order_msg_str = $data_s['order_msg_str'];//商品订单留言
	$comment = $data_s['comment'];//商品订单留言

	$pick_up_id = $data_s['pick_up_id'];
	$dispatching = $data_s['dispatching'];
	$ziti_name = $data_s['ziti_name'];
	$ziti_mobile = $data_s['ziti_mobile'];
	$ck_yupay = $data_s['ck_yupay'];
	/**

	pick_up_id: that.data.pick_up_id,
	dispatching: that.data.dispatching, //express  pickup
	ziti_name: t_ziti_name,
	ziti_mobile: t_ziti_mobile
	**/
	$order_msg_arr = explode('@,@', $order_msg_str);

	$quan_arr = $data_s['quan_arr'];//商品订单留言

	$order_quan_arr = array();


	if( !empty($quan_arr) )
	{
		if( !is_array($quan_arr) )
		{
			$quan_arr = array($quan_arr);
		}

		foreach($quan_arr as $q_val)
		{
			$tmp_q = array();
			$tmp_q = explode('_',$q_val);

			$voucher_info = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_coupon_list')." where uniacid=:uniacid and consume='N' and id=:id and user_id=:user_id and end_time >:end_time  ",
				array(':end_time' => time(),':user_id' => $member_id, ':id' => $tmp_q[1],':uniacid' => $_W['uniacid']));

			if( !empty($voucher_info) )
			{
				//$order_quan_arr[$tmp_q[0]] = $tmp_q[1];
				$order_quan_arr[1] = $tmp_q[1];
			}
		}
	}


	$msg_arr = array();
	foreach($order_msg_arr as $val)
	{
		$tmp_val = explode('@_@', $val);
		$msg_arr[ $tmp_val[0] ] = $tmp_val[1];
	}


	$cart= load_model_class('car');

	// 验证商品数量
	//buy_type:buy_type
	$buy_type = $data_s['buy_type'];//I('post.buy_type');


	$is_pin = 0;
	if($buy_type == 'pin')
	{
		$is_pin = 1;
	}


	$goodss = $cart->get_all_goodswecar($buy_type,$token,1,$data_s['pick_up_id']);
	//付款人


	$member_param = array();
	$member_param[':uniacid'] = $_W['uniacid'];
	$member_param[':member_id'] = $member_id;

	$payment = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_member')." where uniacid=:uniacid and member_id=:member_id",$member_param);



	//收货人
	$addr_param = array();
	$addr_param[':uniacid'] = $_W['uniacid'];
	$addr_param[':member_id'] = $member_id;

	//$addr_sql = "select * from ".tablename('eaterplanet_ecommerce_address')." where uniacid=:uniacid and member_id=:member_id order by  is_default desc,address_id desc limit 1";
	//$address = pdo_fetch($addr_sql, $addr_param);

	$seller_goodss = array();

	foreach($goodss as $key => $val)
	{
		//单商户先屏蔽
		//$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();
		$seller_goodss[ 1 ][$key] = $val;

		$cart->removecar($val['key'],$token);
	}


	$pay_total = 0;
	//M('order_all')


	$order_all_data = array();
	$order_all_data['uniacid'] = $_W['uniacid'];
	$order_all_data['member_id'] = $member_id;
	$order_all_data['order_num_alias'] = build_order_no($member_id);
	$order_all_data['transaction_id'] = '';
	$order_all_data['order_status_id'] = 3;
	$order_all_data['is_pin'] = $is_pin;
	$order_all_data['paytime'] = 0;

	$order_all_data['addtime'] = time();

	pdo_insert('eaterplanet_ecommerce_order_all',$order_all_data);
	$order_all_id = pdo_insertid();

	//暂时屏蔽积分商城模块
	//$integral_model = D('Seller/Integral');
	$order_ids_arr = array();
	$del_integral = 0;

	$community_info = pdo_fetch("select * from ".tablename('eaterplanet_community_head')." where uniacid=:uniacid and id=:id ",
						array(':uniacid' => $_W['uniacid'], ':id' => $data_s['pick_up_id']));
	//$data_s['pick_up_id']

	//community_info

	$community_detail_info = D('Home/Front')->get_community_byid($data_s['pick_up_id']);

	foreach($seller_goodss as $kk => $vv)
	{

		$data = array();

		$data['member_id']=$member_id;
		$data['name']= $payment['username'];

		$data['telephone']= $data_s['ziti_mobile'];
		$data['shipping_name']= $data_s['ziti_name'];
		$data['shipping_tel']= $data_s['ziti_mobile'];

		$data['shipping_address'] = $community_detail_info['fullAddress'];
		$data['shipping_province_id']=$community_info['province_id'];
		$data['shipping_city_id']=$community_info['city_id'];
		$data['shipping_stree_id']=$community_info['country_id'];
		$data['shipping_country_id']=$community_info['area_id'];

		$data['shipping_method'] = 0;
		$data['delivery']=$dispatching;
		$data['pick_up_id']=$pick_up_id;

		$data['ziti_name']=$community_info['head_name'];
		$data['ziti_mobile']=$community_info['head_mobile'];
		//$data['ziti_address']=$community_detail_info['fullAddress'];

		//$pick_up_id = $data_s['pick_up_id'];
		//$dispatching = $data_s['dispatching'];
		//$ziti_name = $data_s['ziti_name'];
		//$ziti_mobile = $data_s['ziti_mobile'];

		$data['payment_method']=$pay_method;

		$data['address_id']= $address['address_id'];
		$data['voucher_id'] = isset($order_quan_arr[$kk]) ? $order_quan_arr[$kk]:0;


		$data['user_agent']=$_SERVER['HTTP_USER_AGENT'];
		$data['date_added']=time();

		//$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		$subject='';
		$fare = 0;


		$trans_free_toal = 0;//运费

		$order_total = 0;
		$is_lottery = 0;
		$is_integral = 0;
		$is_spike = 0;

		foreach($goodss as $key => $good)
		{
			/**
			if($good['shipping']==1)
			{
				//统一运费
				$trans_free_toal += $good['goods_freight'];
				$trans_free = $good['goods_freight'];
			}else {
				//运费模板
				$trans_free = load_model_class('transport')->calc_transport($good['transport_id'], $good['quantity'], $good['quantity']*$good['weight'], $address['city_id'] );

				//$trans_free = D('Home/Transport')->calc_transport($good['transport_id'], $good['quantity']*$good['weight'], $address['city_id'] );
				$trans_free_toal +=$trans_free;
			}
			**/
			$trans_free = 0;
			$trans_free_toal +=$trans_free;
		   //sku_str


			$order_total += $good['total'];

			$tp_goods_info = pdo_fetch("select type from ".tablename('eaterplanet_ecommerce_goods')." where id=:id and uniacid=:uniacid ", array(':id' => $good['goods_id'], ':uniacid' =>$_W['uniacid'] ));

			$tp_goods_info['store_id'] = 1;

			if($tp_goods_info['type'] == 'lottery')
			{
				$is_lottery = 1;
			}
			if($tp_goods_info['type'] == 'spike')
			{
				$is_spike = 1;
				$is_pin = 0;
			}
			//暂时屏蔽积分商城模块
			/**
			if($tp_goods_info['type'] == 'integral')
			{
				$is_integral = 1;
				$is_pin = 0;
				$check_result = $integral_model->check_user_score_can_pay($member_id, $good['sku_str'], $good['goods_id'] );
				if($check_result['code'] == 1)
				{
					die();
				}
			}
			**/

			$goods_data[] = array(
				'goods_id'   => $good['goods_id'],
				'store_id' => $tp_goods_info['store_id'],
				'name'       => $good['name'],
				'model'      => $good['model'],
				'is_pin' => $is_pin,
				'pin_id' => $good['pin_id'],
				'header_disc' => $good['header_disc'],
				'member_disc' => $good['member_disc'],
				'level_name' => $good['level_name'],
				'option'     => $good['sku_str'],
				'quantity'   => $good['quantity'],
				'shipping_fare' => $trans_free,
				'price'      => $good['price'],
				'total'      => $good['total'],
				'comment' => htmlspecialchars($comment)
			);

		}

		//$is_pin; is_lottery
		//'pintuan', 'normal', 'lottery'
		$data['type'] = 'normal';
		if($is_pin == 1)
		{
			$data['type'] = 'pintuan';
			if($is_lottery == 1)
			{
				$data['type'] = 'lottery';
			}
		}
		if($is_integral == 1)
		{
			$data['type'] = 'integral';
			$is_pin = 0;
		}
		if($is_spike == 1)
		{
			$data['type'] = 'spike';
			$is_pin = 0;
		}

		$data['shipping_fare'] = floatval($trans_free_toal);


		$data['store_id']= $kk;


		$data['goodss'] = $goods_data;
		$data['order_num_alias']=build_order_no($member_id);

		$data['totals'][0]=array(
			'code'=>'sub_total',
			'title'=>'商品价格',
			'text'=>'¥'.$order_total,
			'value'=>$order_total
		);
		$data['totals'][1]=array(
			'code'=>'shipping',
			'title'=>'运费',
			'text'=>'¥'.$trans_free_toal,
			'value'=>$trans_free_toal
		);

		$data['totals'][2]=array(
			'code'=>'total',
			'title'=>'总价',
			'text'=>'¥'.($order_total+$trans_free_toal),
			'value'=>($order_total+$trans_free_toal)
		);

		$data['from_type'] = 'wepro';

		if($data['voucher_id'] > 0) {

			//暂时屏蔽优惠券，等待开启

			$voucher_info = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_coupon_list')." where uniacid=:uniacid and id=:id ",
									array(':uniacid' => $_W['uniacid'],':id' => $data['voucher_id']));

			$data['voucher_credit'] = $voucher_info['credit'];

			pdo_update('eaterplanet_ecommerce_coupon_list', array('ordersn' => $data['order_num_alias'],'consume' => 'Y','usetime' => time()), array('id' => $data['voucher_id'] ));

		} else {
			$data['voucher_credit'] = 0;
		}

		$data['comment'] = htmlspecialchars($comment);

		//判断自提 dispatching:"pickup"
		//dispatching, //express  pickup

		if($dispatching == 'express')
		{
			$data['total']=($order_total+$fare - $data['voucher_credit']);
		}else{
			$data['total'] = ($order_total - $data['voucher_credit']);
		}
		//积分商城
		//暂时屏蔽积分商城模块
		/**
		if($data['type'] == 'integral')
		{
			$del_integral += $order_total;//扣除积分
			$data['total'] = 0;
			$order_total = 0;
		}
		**/


		$oid= load_model_class('frontorder')->addOrder($data);// D('Order')->addOrder($data);


		//暂时屏蔽自提模块
		/**
		if($data['delivery'] == 'pickup')
		{
			$verify_bool = true;
			$verifycode = 0;
			while($verify_bool)
			{
				$code  = (ceil(time()/100)+rand(10000000,40000000)).rand(1000,9999);
				$verifycode = $code ? $code : rand(100000,999999);
				$verifycode = str_replace('1989','9819',$verifycode);
				$verifycode = str_replace('1259','9521',$verifycode);
				$verifycode = str_replace('12590','95210',$verifycode);
				$verifycode = str_replace('10086','68001',$verifycode);

				$pick_order = M('pick_order')->where( array('pick_sn' => $verifycode) )->find();
				if(empty($pick_order))
				{
					$verify_bool = false;
				}
			}
			$pick_data = array();
			$pick_data['pick_sn'] = $verifycode;
			$pick_data['pick_id'] = $pick_up_id;
			$pick_data['order_id'] = $oid;
			$pick_data['state'] = 0;

			$pick_data['ziti_name'] = $ziti_name;
			$pick_data['ziti_mobile'] = $ziti_mobile;


			$pick_data['addtime'] = time();
			M('pick_order')->add($pick_data);
		}
		**/

		$order_ids_arr[] = $oid;
		//$pay_total = $pay_total + $order_total+$trans_free_toal - $data['voucher_credit'];

		if($dispatching == 'express')
		{
			$pay_total = $pay_total + $order_total+$trans_free_toal - $data['voucher_credit'];
		}else{
			$pay_total = $pay_total + $order_total - $data['voucher_credit'];
		}



		$order_relate_data = array();

		$order_relate_data['uniacid'] = $_W['uniacid'];
		$order_relate_data['order_all_id'] = $order_all_id;
		$order_relate_data['order_id'] = $oid;
		$order_relate_data['addtime'] = time();

		pdo_insert('eaterplanet_ecommerce_order_relate',$order_relate_data);


	}

	$order_all_data = array();
	$order_all_data['total_money'] = $pay_total;

	pdo_update('eaterplanet_ecommerce_order_all', $order_all_data, array('id' => $order_all_id,'uniacid' => $_W['uniacid']));


	if($order_all_id){

		$order = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_order')." where order_id=:order_id and uniacid=:uniacid ",array(':order_id'=>$oid,':uniacid' => $_W['uniacid']) );

		$member_info = pdo_fetch('select we_openid,account_money from '.tablename('eaterplanet_ecommerce_member')." where member_id=:member_id and unionid=:uniacid " ,array(':uniacid' => $_W['uniacid'],':member_id'=>$member_id));


		if( $pay_total<=0 || ($ck_yupay == 1 && $member_info['account_money'] >= $pay_total) )
		{
			/****
			//暂时关闭


			//检测是否需要扣除积分
			if($del_integral> 0 && $is_integral == 1)
			{
				$integral_model->charge_member_score( $member_id, $del_integral,'out', 'orderbuy', $oid);
			}

			if($ck_yupay == 1 && $pay_total >0)
			{
				//开始余额支付
				$member_charge_flow_data = array();
				$member_charge_flow_data['member_id'] = $member_id;
				$member_charge_flow_data['trans_id'] = $oid;
				$member_charge_flow_data['money'] = $pay_total;
				$member_charge_flow_data['state'] = 3;
				$member_charge_flow_data['charge_time'] = time();
				$member_charge_flow_data['add_time'] = time();
				M('member_charge_flow')->add($member_charge_flow_data);
				//开始处理扣钱
				M('member')->where( array('member_id' => $member_id) )->setInc('account_money',-$pay_total);
			}
			***/
			//eaterplanet_ecommerce_order_all

			//开始处理订单状态
			//$order_all = M('order_all')->where( array('id' => $order_all_id) )->find();
			$order_all = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_order_all')." where id=:id and uniacid=:uniacid ",array(':id'=>$order_all_id, ":uniacid"=>$_W['uniacid']));

			if($order&&($order['order_status_id']!=1))
			{
				//支付完成
				$o = array();
				$o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;
				$o['paytime']=time();
				$o['transaction_id'] = $transaction_id;

				pdo_update('eaterplanet_ecommerce_order_all', $o, array( 'id' => $out_trade_no,'uniacid' =>$_W['uniacid'] ));

				// ims_

				$order_relate_list_sql = "select * from ".tablename('eaterplanet_ecommerce_order_relate')." where order_all_id=:id and uniacid=:uniacid ";
				$order_relate_list = pdo_fetchall($order_relate_list_sql, array(':uniacid' =>$_W['uniacid'], ':id' => $order_all['id'] ));

				foreach($order_relate_list as $order_relate)
				{

					$order = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_order')." where uniacid=:uniacid and order_id=:order_id " ,array(':uniacid' => $_W['uniacid'], ':order_id' => $order_relate['order_id'] ));

					if( $order && $order['order_status_id'] == 3)
					{
						$o = array();
						$o['payment_code'] = 'yuer';
						$o['order_id']=$order['order_id'];
						$o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;
						$o['date_modified']=time();
						$o['pay_time']=time();
						$o['transaction_id'] = $is_integral ==1? '积分兑换':'余额支付';

						//ims_
						pdo_update('eaterplanet_ecommerce_order', $o, array('order_id' => $order['order_id'],'uniacid' => $_W['uniacid']));


						//暂时屏蔽
						//$kucun_method = C('kucun_method');
						//$kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);
						$kucun_method = 0;

						//$goods_model = D('Home/Goods');

						if($kucun_method == 1)
						{//支付完减库存，增加销量

							$order_goods_list = pdo_fetchall("select * from ".tablename('eaterplanet_ecommerce_order_goods')." where order_id=:order_id and uniacid=:uniacid ", array(':order_id' => $order['order_id'], ':uniacid' => $_W['uniacid']) );

							foreach($order_goods_list as $order_goods)
							{
								load_model_class('pingoods')->del_goods_mult_option_quantity($order['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],1);

							}
						}

						$oh = array();
						$oh['uniacid'] = $_W['uniacid'];
						$oh['order_id']=$order['order_id'];
						$oh['order_status_id']= $order['is_pin'] == 1 ? 2:1;
						$oh['comment']='买家已付款';
						$oh['date_added']=time();
						$oh['notify']=1;

						pdo_insert('eaterplanet_ecommerce_order_history', $oh);


						//发送购买通知
						//TODO 先屏蔽，等待调试这个消息
						//$weixin_nofity = D('Home/Weixinnotify');
						//$weixin_nofity->orderBuy($order['order_id']);


						if($order['is_pin'] == 1)
						{

							$pin_order = pdo_fetch('select * from '.tablename('eaterplanet_ecommerce_pin_order')." where order_id=:order_id and uniacid=:uniacid ", array(':order_id' => $order['order_id'], ':uniacid' => $_W['uniacid']) );


							load_model_class('pin')->insertNotifyOrder($order['order_id']);


							$is_pin_success = load_model_class('pin')->checkPinSuccess($pin_order['pin_id']);

							if($is_pin_success) {
								//todo send pintuan success notify
								load_model_class('pin')->updatePintuanSuccess($pin_order['pin_id']);
							}
						}

					}

				}
				//返回支付成功给app
				$data = array();
				$data['code'] = 0;
				$data['has_yupay'] = 1;
				$data['is_integral'] = $is_integral;
				$data['is_spike'] = $is_spike;

			}

		}else{

			$fee = $pay_total;
			$appid = D('Home/Front')->get_config_by_name('wepro_appid');
			$body =         '商品购买';
			$mch_id =       D('Home/Front')->get_config_by_name('wepro_partnerid');
			$nonce_str =    nonce_str();
			$notify_url =   $_W['siteroot'].'addons/eaterplanet_ecommerce/notify.php';


			$openid =       $payment['we_openid'];
			$out_trade_no = $order_all_id.'-'.time();
			$spbill_create_ip = $_SERVER['REMOTE_ADDR'];
			$total_fee =    $fee*100;
			$trade_type = 'JSAPI';
			$pay_key = D('Home/Front')->get_config_by_name('wepro_key');

			$post['appid'] = $appid;
			$post['body'] = $body;
			$post['mch_id'] = $mch_id;
			$post['nonce_str'] = $nonce_str;
			$post['notify_url'] = $notify_url;

			$post['openid'] = $openid;
			$post['out_trade_no'] = $out_trade_no;
			$post['spbill_create_ip'] = $spbill_create_ip;
			$post['total_fee'] = $total_fee;
			$post['trade_type'] = $trade_type;
			$sign = sign($post,$pay_key);


			$post_xml = '<xml>
				   <appid>'.$appid.'</appid>
				   <body>'.$body.'</body>
				   <mch_id>'.$mch_id.'</mch_id>
				   <nonce_str>'.$nonce_str.'</nonce_str>
				   <notify_url>'.$notify_url.'</notify_url>
				   <openid>'.$openid.'</openid>
				   <out_trade_no>'.$out_trade_no.'</out_trade_no>
				   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
				   <total_fee>'.$total_fee.'</total_fee>
				   <trade_type>'.$trade_type.'</trade_type>
				   <sign>'.$sign.'</sign>
				</xml> ';
			$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
			$xml = http_request($url,$post_xml);
			$array = xml($xml);

			if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
				$time = time();
				$tmp='';
				$tmp['appId'] = $appid;
				$tmp['nonceStr'] = $nonce_str;
				$tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
				$tmp['signType'] = 'MD5';
				$tmp['timeStamp'] = "$time";

				$prepay_id = (string)$array['PREPAY_ID'];
				//ims_
				$order_sql = "update ".tablename('eaterplanet_ecommerce_order')." set perpay_id='{$prepay_id}' where uniacid={$_W[uniacid]} and order_id in (".implode(',', $order_ids_arr).") ";

				pdo_query($order_sql);

				//M('order')->where( array('order_id' => array('in',$order_ids_arr) ) )->save( array('perpay_id' => (string)$array['PREPAY_ID']) );
				$data = array();
				$data['code'] = 0;
				$data['appid'] = $appid;
				$data['timeStamp'] = "$time";
				$data['nonceStr'] = $nonce_str;
				$data['signType'] = 'MD5';
				$data['package'] = 'prepay_id='.$array['PREPAY_ID'];
				$data['paySign'] = sign($tmp,$pay_key);
				$data['out_trade_no'] = $out_trade_no;

				if($is_pin == 1)
				{
					$data['redirect_url'] = '../groups/group?id='.$oid.'&is_show=1';
				} else {
					$data['redirect_url'] = '../orders/order_show_all?order_all_id=' + $order_all_id;
				}

			}else{
				$data = array();
				$data['code'] = 1;
				$data['text'] = "错误";
				$data['RETURN_CODE'] = $array['RETURN_CODE'];
				$data['RETURN_MSG'] = $array['RETURN_MSG'];
				}
				$data['has_yupay'] = 0;
			}

			if($is_pin == 1)
			{
				$data['order_id'] = $oid;
				$data['order_all_id'] = $order_all_id;
			}else{
				$data['order_id'] = $oid;
				$data['order_all_id'] = $order_all_id;
			}
			$data['is_spike'] = $is_spike;
			echo json_encode($data);
			die();
		}else{
			echo json_encode( array('code' =>1,'order_all_id' =>$order_all_id) );
			die();
		}

	}


	public function wxpay()
	{
		global $_W;
		global $_GPC;

		$token = $_GPC['token'];
		$order_id = $_GPC['order_id'];


		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;

		$weprogram_token = pdo_fetch("select member_id from ".tablename('eaterplanet_ecommerce_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);

		$member_id = $weprogram_token['member_id'];


		if( empty($member_id) )
		{
			echo json_encode( array('code' =>1,'msg' =>'未登录') );
			die();
		}

		//
		$member_info = pdo_fetch("select we_openid from ".tablename('eaterplanet_ecommerce_member')." where uniacid=:uniacid and member_id=:member_id ", array(':member_id' => $member_id, ':uniacid' => $_W['uniacid']));


		$order = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_order')." where uniacid=:uniacid and order_id=:order_id ",array(':uniacid' => $_W['uniacid'] , ':order_id' => $order_id));


		//支付才减库存，才需要判断
		$kucun_method = D('Home/Front')->get_config_by_name('kucun_method', $uniacid);

		if( empty($kucun_method) )
		{
			$kucun_method = 0;
		}

		if($kucun_method == 1)
		{
			/*** 检测商品库存begin  **/

			$sql = "select name,quantity,rela_goodsoption_valueid,goods_id from ".tablename('eaterplanet_ecommerce_order_goods')."
					where order_id=:order_id and uniacid=:uniacid ";

			$order_goods_list = pdo_fetchall($sql, array(':order_id' => $order_id, ':uniacid' => $_W['uniacid']));

			//goods_id
			foreach($order_goods_list as $val)
			{
				$quantity = $val['quantity'];

				$goods_id = $val['goods_id'];

				$can_buy_count = D('Home/Front')->check_goods_user_canbuy_count($member_id, $goods_id);

				$goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'total_limit_count');

				if($can_buy_count == -1)
				{
					$json['code'] = 2;

					$json['msg']='每人最多购买'.$goods_description['total_limit_count'].'个哦';

					echo json_encode($json);
					die();
				}else if($can_buy_count >0 && $quantity >$can_buy_count)
				{
					$json['code'] = 2;
					$json['msg']='您还能购买'.$can_buy_count.'份';
					echo json_encode($json);
					die();
				}
				//rela_goodsoption_valueid
				if(!empty($val['rela_goodsoption_valueid']))
				{
					$mul_opt_arr = array();

					//ims_

					$mult_sql = "select * from ".tablename('eaterplanet_ecommerce_goods_option_item_value')."
								where uniacid=:uniacid and option_item_ids = :sku_str and goods_id =:goods_id limit 1 ";

					$goods_option_mult_value = pdo_fetch($mult_sql, array(':uniacid' => $_W['uniacid'],':sku_str' =>$val['rela_goodsoption_valueid'],':goods_id' => $goods_id ));

					if( !empty($goods_option_mult_value) )
					{
						if($goods_option_mult_value['stock']<$quantity){
							$json['code'] =2;
							$json['msg']='商品数量不足，剩余'.$goods_option_mult_value['stock'].'个！！';
							echo json_encode($json);
							die();
						}
					}
				}

			}
			/*** 检测商品库存end **/
		}


		$pin_order = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_pin_order')." where uniacid=:uniacid and order_id=:order_id ", array(':order_id' => $order_id, ':uniacid' => $_W['uniacid']));

		if( !empty($pin_order) )
		{
			$pin_model =  load_model_class('pin');
			$is_pin_over = $pin_model->getNowPinState($pin_order['pin_id']);
			if($is_pin_over != 0)
			{
				 pdo_query("delete from ".tablename('eaterplanet_ecommerce_pin_order')." where order_id = {$order_id} ");

				 pdo_query("delete from ".tablename('eaterplanet_ecommerce_pin')." where pin_id = ".$pin_order['pin_id']." and order_id = ".$order_id);

				$order_goods_info = pdo_fetch("select goods_id from ".tablename('eaterplanet_ecommerce_order_goods')." where uniacid=:uniacid and order_id=:order_id ", array(':order_id' => $order_id,':uniacid' => $_W['uniacid']));

				//新开团

				$pin_id = $pin_model->openNewTuan($order_id,$order_goods_info['goods_id'],$member_id);
				//插入拼团订单
	            $pin_model->insertTuanOrder($pin_id,$order_id);

			}
		}


		//单独支付一个店铺的订单
		pdo_query("delete from ".tablename('eaterplanet_ecommerce_order_relate')." where order_id=".$order_id." and uniacid=".$_W['uniacid']);

		$order_all_data = array();
		$order_all_data['member_id'] = $member_id;
		$order_all_data['uniacid'] = $_W['uniacid'];
		$order_all_data['order_num_alias'] = build_order_no($member_id);
		$order_all_data['transaction_id'] = '';
		$order_all_data['order_status_id'] = 3;
		$order_all_data['is_pin'] = $order['is_pin'];
		$order_all_data['paytime'] = 0;
		$order_all_data['total_money'] = $order['total'];
		$order_all_data['addtime'] = time();

		pdo_insert('eaterplanet_ecommerce_order_all', $order_all_data);
		$order_all_id = pdo_insertid();

		$order_relate_data = array();
		$order_relate_data['uniacid'] = $_W['uniacid'];
		$order_relate_data['order_all_id'] = $order_all_id;
		$order_relate_data['order_id'] = $order_id;
		$order_relate_data['addtime'] = time();

		pdo_insert('eaterplanet_ecommerce_order_relate',$order_relate_data);//ims_

		if( $order['delivery'] == 'pickup' )
		{
			$fee = $order['total'];
		}else {
			$fee = $order['total'];
		}



		$appid = D('Home/Front')->get_config_by_name('wepro_appid');
		$body =         '商品购买';
		$mch_id =       D('Home/Front')->get_config_by_name('wepro_partnerid');
		$nonce_str =    nonce_str();
		$notify_url =    $_W['siteroot'].'addons/eaterplanet_ecommerce/notify.php';
		$openid =       $member_info['we_openid'];
		$out_trade_no = $order_all_id.'-'.time();
		$spbill_create_ip = $_SERVER['REMOTE_ADDR'];
		$total_fee =    $fee*100;
		$trade_type = 'JSAPI';
		$pay_key = D('Home/Front')->get_config_by_name('wepro_key');



		$post['appid'] = $appid;
		$post['body'] = $body;
		$post['mch_id'] = $mch_id;
		$post['nonce_str'] = $nonce_str;
		$post['notify_url'] = $notify_url;
		$post['openid'] = $openid;
		$post['out_trade_no'] = $out_trade_no;
		$post['spbill_create_ip'] = $spbill_create_ip;
		$post['total_fee'] = $total_fee;
		$post['trade_type'] = $trade_type;
		$sign = sign($post,$pay_key);


		$post_xml = '<xml>
			   <appid>'.$appid.'</appid>
			   <body>'.$body.'</body>
			   <mch_id>'.$mch_id.'</mch_id>
			   <nonce_str>'.$nonce_str.'</nonce_str>
			   <notify_url>'.$notify_url.'</notify_url>
			   <openid>'.$openid.'</openid>
			   <out_trade_no>'.$out_trade_no.'</out_trade_no>
			   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
			   <total_fee>'.$total_fee.'</total_fee>
			   <trade_type>'.$trade_type.'</trade_type>
			   <sign>'.$sign.'</sign>
			</xml> ';
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$xml = http_request($url,$post_xml);
		$array = xml($xml);
		if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
			$time = time();
			$tmp='';
			$tmp['appId'] = $appid;
			$tmp['nonceStr'] = $nonce_str;
			$tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
			$tmp['signType'] = 'MD5';
			$tmp['timeStamp'] = "$time";

			$prepay_id = (string)$array['PREPAY_ID'];

			$order_sql = "update ".tablename('eaterplanet_ecommerce_order')." set perpay_id='{$prepay_id}' where uniacid={$_W[uniacid]} and order_id =".$order_id;

			pdo_query($order_sql);


			$data['code'] = 0;
			$data['timeStamp'] = "$time";
			$data['nonceStr'] = $nonce_str;
			$data['signType'] = 'MD5';
			$data['package'] = 'prepay_id='.$array['PREPAY_ID'];
			$data['paySign'] = sign($tmp, $pay_key);
			$data['out_trade_no'] = $out_trade_no;
			$data['is_pin'] = $order['is_pin'];

			if($order['is_pin'] == 1)
			{
				$data['redirect_url'] = '../groups/group?id='.$order_id.'&is_show=1';
			} else {
				$data['redirect_url'] = '../orders/order?id=' + $order_id;
			}

		}else{
			$data['code'] = 1;
			$data['text'] = "错误";
			$data['RETURN_CODE'] = $array['RETURN_CODE'];
			$data['RETURN_MSG'] = $array['RETURN_MSG'];
		}


		echo json_encode($data);
		die();
	}

	/**
	 * 获取购物车总数
	 */
	public function count() {

		$data = I('param.');

		var_dump($data,999);die();
		global $_W;
		global $_GPC;

		$data = array();
		$token = $_GPC['token'];
		$community_id = $_GPC['community_id'];

		$cart= load_model_class('car');
		$total=$cart->count_goodscar($token, $community_id);

		$data['code'] = 0;
		$data['data'] = $total;
		echo json_encode($data);
		die();

	}

}
