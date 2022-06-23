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

class ApicartController extends CommonController {

	//结算
	function checkout(){
		  $buy_type = I('get.buy_type','dan');
		  //undefined
		  if($buy_type == 'undefined')
		  {
			 $buy_type = 'dan';
		  }
		  $token = I('get.token');
		  $voucher_id = I('get.voucher_id',0);

		  $use_quan_str = I('get.use_quan_str','');
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


		  $weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		  $member_id = $weprogram_token['member_id'];
		  if( empty($member_id) )
		  {
			  //需要登录
			  echo json_encode( array('code' =>5) );
			  die();
		  }



		$cart=new \Lib\Cart();
		if ((!$cart->has_goodswecar($buy_type,$token) ) ) {
			//购物车中没有商品
			echo json_encode( array('code' =>4) );
			die();
		}

		$member_info = M('member')->where( array('member_id' => $member_id) )->find();

        $goods=$cart->get_all_goodswecar($buy_type, $token);
		//var_dump($goods);die();

        $add_where = array('member_id'=>$member_id );
        $address = M('address')->where( $add_where )->order('is_default desc,address_id desc')->find();
        if($address){
            $province_info = M('area')->field('area_name')->where( array('area_id' => $address['province_id']) )->find();
            $city_info = M('area')->field('area_name')->where( array('area_id' => $address['city_id']) )->find();
            $country_info = M('area')->field('area_name')->where( array('area_id' => $address['country_id']) )->find();
            $address['province_name'] = $province_info['area_name'];
            $address['city_name'] = $city_info['area_name'];
            $address['country_name'] = $country_info['area_name'];
        }

		$seller_goodss = array();
		$show_voucher = 0;

		//var_dump($goods);die();

		foreach($goods as $key => $val)
		{
			$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();
			$seller_goodss[ $goods_store_field['store_id'] ]['goods'][$key] = $val;
		}

		$quan_model = D('Home/Voucher');
		$pin_model = D('Home/Pin');
		$voucher_price = 0;
		$is_pin_over = 0;
		//计算优惠券
		foreach($seller_goodss as $store_id => $val)
		{
			//total
			$seller_voucher_list = array();
			$seller_total_fee = 0;
			$total_trans_free = 0;
			$is_no_quan = false;
			foreach($val['goods'] as $kk =>$d_goods)
			{
				$seller_total_fee += $d_goods['total'];

				//'pin_id' => $pin_id,

				if($buy_type == 'pin' && $d_goods['pin_id'] > 0)
				{
					$is_pin_over = $pin_model->getNowPinState($d_goods['pin_id']);
				}

				//$d_goods['goods_id']
				$tp_goods_info = M('goods')->field('type')->where( array('goods_id' => $d_goods['goods_id']) )->find();
				if($tp_goods_info['type'] == 'integral')
				{
					$is_no_quan = true;
				}


				if($d_goods['shipping']==1)
                {
                    //统一运费
                    $d_goods[$kk]['trans_free'] = $d_goods['goods_freight'];
                }else {
                    //运费模板
					 if(!empty($address))
					{
						$trans_free = D('Home/Transport')->calc_transport($d_goods['transport_id'], $d_goods['quantity']*$d_goods['weight'], $address['city_id'] );
					}else{
						$trans_free = 0;
					}
				   $d_goods[$kk]['trans_free'] = $trans_free;
                }
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
					//credit
					$seller_total_fee = round( $seller_total_fee - $chose_vouche['credit'], 2);
				}else if( !empty($vouche_list) &&  !empty($use_quan_arr) )
				{
					//var_dump($use_quan_arr);die();
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
			//trans_free

			//seller/2017-10-06/59d74b0611381.png

			$store_info = M('seller')->field('s_id,s_true_name,s_logo')->where( array('s_id' => $store_id) )->find();
			$store_info['s_logo'] = C('SITE_URL').'Uploads/image/'.$store_info['s_logo'];
			$val['store_info'] = $store_info;

			//unset($val['goods']);
			$seller_goodss[$store_id] = $val;
		}

        $trans_free_toal = 0;//运费


            //D('Transport')->calc_transport($v['id'], $goods_data['quantity'], $address['city_id'] );
			$total_free = 0;
		$is_ziti = 0;
		//is_ziti: res.data.is_ziti,
	    // pick_up_arr: res.data.pick_up_arr,
		$pick_up_arr = array();
            foreach($goods as $key => $good)
            {
			//goods_id
			$goods_info = M('goods')->field('pick_just,pick_up,store_id')->where( array('goods_id' => $good['goods_id']) )->find();

			if($goods_info['pick_just'] >= 1)
			{
				 $pick_up = $goods_info['pick_up'];
				 $is_ziti = $goods_info['pick_just'];
			}
                if($good['shipping']==1)
                {
                    //统一运费
                    $trans_free_toal += $good['goods_freight'];
                    $goods[$key]['trans_free'] = $good['goods_freight'];
                }else {
                    //运费模板
					 if(!empty($address))
					{
						$trans_free = D('Home/Transport')->calc_transport($good['transport_id'], $good['quantity']*$good['weight'], $address['city_id'] );
					}else{
						$trans_free = 0;
					}

				   $goods[$key]['trans_free'] = $trans_free;
                    $trans_free_toal +=$trans_free;

                }

				$total_free += $good['total'];
            }
		if(!empty($pick_up))
		{
			$pick_up = unserialize($pick_up);
			$pick_up_ids = implode(',',$pick_up);
			$pick_up_arr = M('pick_up')->where( array('id'=>array('in',$pick_up_ids)) )->select();
		}

        //$this->trans_free_toal = $trans_free_toal;
       // $this->goods = $goods;
		$pick_up_name = '';
		$pick_up_mobile = '';

		if($is_ziti >= 1)
		{
			//寻找上一个订单的自提电话 自提姓名
			//delivery = pickup  member_id
			$last_order_info = M('order')->where( array('member_id' => $member_id,'delivery' => 'pickup') )->order('order_id desc')->find();



			if(!empty($last_order_info))
			{
				$pick_up_name = $last_order_info['shipping_name'];
				$pick_up_mobile = $last_order_info['telephone'];
			}
		}

		$need_data = array();
		$need_data['code'] = 1;
		$need_data['is_pin_over'] = $is_pin_over;
		$need_data['is_integer'] = $is_no_quan ? 1: 0;
		$need_data['pick_up_arr'] = $pick_up_arr;
		$need_data['is_ziti'] = $is_ziti;

		$need_data['ziti_name'] = $pick_up_name;
		$need_data['ziti_mobile'] = $pick_up_mobile;
		$need_data['seller_goodss'] = $seller_goodss;
		$need_data['show_voucher'] = $show_voucher;

		$need_data['buy_type'] = $buy_type;
		$need_data['address'] = $address;
		$need_data['trans_free_toal'] = $trans_free_toal;

		$dispatching = I('get.dispatching','express');//pickup dispatching/express
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
		//member_info  account_money
		$is_yue_open_info =	M('config')->where( array('name' => 'is_yue_open') )->find();
		$is_yue_open =  $is_yue_open_info['value'];

		$need_data['is_yue_open'] = $is_yue_open;

		$need_data['can_yupay'] = 0;

		if($is_yue_open == 1 && $need_data['total_free'] >=0 && $member_info['account_money'] >= $need_data['total_free'])
		{
			$need_data['can_yupay'] = 1;
		}
		//member_info yu_money account_money

		$need_data['yu_money'] = $member_info['account_money'];
		$need_data['goods'] = $goods;

		echo json_encode($need_data);
		die();
		//$this->display('checkout');
	}



	public function get_user_pay_voucher()
	{
	    //{store_id:store_id,total_free:total_free}
	    $voucher_id = I('post.voucher_id',0);
	    $store_id = I('post.store_id');
	    $total_free = I('post.total_free');
	    $store_info = M('seller')->where( array('s_id' => $store_id) )->find();
	    $this->store_info = $store_info;
	    $this->voucher_id = $voucher_id;

	    $result = array('code' => '0');
	    $quan_model = D('Home/Voucher');
	    $user_vouche_list =  $quan_model->get_user_canpay_voucher(is_login(),$store_id,$total_free);

	   // $user_vouche_list = array();
	    $this->user_vouche_list =  $user_vouche_list;

	    $where = "total_count>send_count and (  store_id ={$store_id} or store_id = 0 ) and is_index_show=1   and end_time>".time();


	    $quan_list = M('voucher')->where($where)->order('add_time desc')->limit(8)->select();
	    $this->quan_list = $quan_list;

	    $html = $this->fetch('Cart:voucher_ajax_fetch');
	    $result['html'] = $html;
	    if(!empty($user_vouche_list)) {
	        $result['code'] =  1;
	    }else if(!empty($quan_list)) {
	       // $result['code'] = 2;
	    }

	    echo json_encode($result);
	    die();
	}


	//显示购物车中商品列表
	function show_cart_goods(){

		$buy_type = I('get.buy_type','dan');
		$token = I('get.token');

		  $weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		  $member_id = $weprogram_token['member_id'];


		  if( empty($member_id) )
		  {
			  //需要登录
			  echo json_encode( array('code' =>5) );
			  die();
		  }

		$cart=new \Lib\Cart();

		$goods=$cart->get_all_goodswecar($buy_type, $token, 0);

		//$cart = M('car')->where("token = '{$token}' and carkey like 'cart.%' ")->select();


		//$goods=$cart->get_all_goodswecar($buy_type, $token, 0); singledel

		$seller_goodss = array();

		foreach($goods as $key => $val)
		{
			$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();
			$seller_goodss[ $goods_store_field['store_id'] ]['goods'][$key] = $val;
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

			$store_info = M('seller')->field('s_id,s_true_name,s_logo')->where( array('s_id' => $store_id) )->find();
			$store_info['s_logo'] = C('SITE_URL').'Uploads/image/'.$store_info['s_logo'];
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

	//更新购物车商品数量
	function update_quantity(){
		$d=I('post.');
		$cart=new \Lib\Cart();

		$key=$d['cart_key'];
		$goods_id = explode(':',$key);
		$goods_id = $goods_id[0];

		$quantity = $d['quantity'];

		$data =session('cart.'.$key);

		//'sku_str'=>$data['sku_str']

		$goods=M('goods')->find($goods_id);
		$json = array('code' =>0);

		//商品存在
		if($goods){

			if($goods['quantity']<$quantity){
				$json['msg']='商品数量不足，剩余'.$goods['quantity'].'个！！';
				echo json_encode($json);
				die();
			}


			    $option = array();

			    if( !empty($data['sku_str'])){
			        $option = explode('_', $data['sku_str']);
			    }

			    $json=array('code' =>0);
			    $goods_model = D('Home/Goods');
			    $goods_quantity=$cart->get_goods_quantity($goods_id);


			    if($goods_quantity<$quantity){
			        $json['code'] =3;
			        $json['msg']='商品数量不足，剩余'.$goods_quantity.'个！！';
			        echo json_encode($json);
			        die();
			    }

			    $goods_options=$goods_model->get_goods_options($goods_id);

			    if(!empty($option))
			    {
			        $mul_opt_arr = array();

			        $goods_option_mult_value = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $data['sku_str'],'goods_id'=>$goods_id) )->find();

			        if( !empty($goods_option_mult_value) )
			        {
			            if($goods_option_mult_value['quantity']<$quantity){
			                $json['code'] =3;
			                $json['msg']='商品数量不足，剩余'.$goods_option_mult_value['quantity'].'个！！';
			                echo json_encode($json);
			                die();
			            }
			        }
			    }

				$cart->update($key,$quantity);

				$goods_list=$cart->get_all_goods();

				$squantity=0;
				$total=0;
				$total_all_price=0;
				$weight = 0;
				$w=new \Lib\Weight();

				foreach ($goods_list as $k => $v) {

    			    $squantity += $v['quantity'];
    			    $goods[$k] = $v;
    				$total_all_price+=$v['total'];

    				if($v['key'] ==$key)
    				{
    				    $price = $v['price'];
    				}

    				if ($v['shipping']) {
    					$weight += $w->convert($v['weight'], $v['weight_class_id'],C('WEIGHT_ID'));
    				}
				}

				//商品数量
				session('cart_total',$squantity);

				$json['code'] =1;

				$json['cart_total']=$squantity;

				//商品单价
				$json['price']=$price;
				//单个商品总价
				$json['total_price']=$price * $quantity;
				//所有商品总价
				$json['total_all_price']=$total_all_price;
				//所有商品重量
				$json['weight']=$weight;

			echo json_encode($json);
			die();
		}

	}
	//删除商品
	function remove(){

		$cart=new \Lib\Cart();

		$cart_key = I('post.cart_key');

		$cart->remove($cart_key);

		$goods_list=$cart->get_all_goods();

		$squantity=0;
		$total=0;
		$total_all_price=0;
		$weight = 0;
		$w=new \Lib\Weight();

		foreach ($goods_list as $k => $v) {

		    $squantity += $v['quantity'];
		    $goods[$k] = $v;
			$total_all_price+=$v['total'];

			if ($v['shipping']) {
				$weight += $w->convert($v['weight'], $v['weight_class_id'],C('WEIGHT_ID'));
			}
		}
		$json = array();

		//商品数量
		session('cart_total',$squantity);

		$json['code'] =1;

		$json['cart_total']=$squantity;

		//所有商品总价
		$json['total_all_price']=$total_all_price;
		//所有商品重量
		$json['weight']=$weight;

		echo json_encode($json);
		die();
	}
	/**
	 * 清空购物车
	 * 拼团等这种无需购物车的逻辑使用
	 */
	function clear_all_cart()
	{
	    $cart=new \Lib\Cart();
	    $cart->removeAll();
	    $total=$cart->count_goods();
	    session('cart_total',$total);
	}


	public function del_car_goods()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		$data_json = file_get_contents('php://input');
		$data = json_decode($data_json, true);

		$carkey = $data['carkey'];//I('post.car_key');

		$all_cart = M('car')->where("token = '{$token}' and carkey = '{$carkey}' ")->delete();

		echo json_encode( array('code' => 0) );
		die();

	}

	public function clear_dan_cars()
	{
		$token = I('get.token');

		$all_cart = M('car')->where("token = '{$token}' and carkey like 'cart.%' ")->select();

		if(!empty($all_cart))
		{
			foreach($all_cart as $val)
			{
				$tmp_format_data = unserialize($val['format_data']);
				if($tmp_format_data['singledel'] == 1)
				{
					M('car')->where( array('id' => $val['id']) )->delete();
				}
			}
		}
		echo json_encode( array('code' => 0) );
		die();
	}

	/**
		检测是否限购商品
	**/
	public function check_car_buy_count()
	{

	}

	public function checkout_flushall()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		$data_json = file_get_contents('php://input');
		$data = json_decode($data_json, true);

		$car_key = $data['car_key'];//I('post.car_key');
		$all_keys_arr = $data['all_keys_arr'];

		$save_keys = array();
		foreach($all_keys_arr as $val)
		{

			$tmp_val = explode('_', $val);

			$save_keys[ $tmp_val[0] ] = $tmp_val[1];
		}


		$all_cart = M('car')->where("token = '{$token}' and carkey like 'cart.%' ")->select();

		if(!empty($all_cart))
		{
			foreach($all_cart as $val)
			{

				$tmp_format_data = unserialize($val['format_data']);
				$tmp_format_data['singledel'] = 0;
				$tmp_format_data['quantity'] = $save_keys[$val['carkey']];

				//检测库存是否足够
				M('car')->where( array('id' => $val['id']) )->save( array('format_data' => serialize($tmp_format_data) ) );

			}
		}


		foreach( $car_key as $key )
		{
			$car_info = M('car')->where( array('carkey' => $key,'token' => $token) )->find();

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
					M('car')->where( array('id' => $car_info['id']) )->save( array('format_data' => serialize($tmp_format_data) ) );
					echo json_encode( array('code' => 6,'msg' => $check_json['msg']) );
					die();
				}
				$check_json = $this->_check_goods_quantity($goods_id,$quantity,$sku_str);
				//var_dump($check_json);die();
				if($check_json['code'] != 0)
				{
					echo json_encode( array('code' => 6,'msg' => $check_json['msg']) );
					die();
				}
				//_check_goods_quantity($goods_id,$quantity,$sku_str) sku_str

				M('car')->where( array('id' => $car_info['id']) )->save( array('format_data' => serialize($tmp_format_data) ) );
			}
		}
		echo json_encode( array('code' => 0) );
		die();
	}

	private function _check_can_buy($member_id, $goods_id,$quantity)
	{
		$goods_model = D('Home/Goods');

		$can_buy_count = $goods_model->check_goods_user_canbuy_count($member_id, $goods_id);

		$goods_info = M('goods')->field('name')->where( array('goods_id' => $goods_id) )->find();

		$goods_description = M('goods_description')->field('per_number')->where( array('goods_id' => $goods_id) )->find();
		//per_number

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

	public function _check_goods_quantity($goods_id,$quantity,$sku_str)
	{
		$cart=new \Lib\Cart();
		$goods_model = D('Home/Goods');

		$goods_info = M('goods')->field('name')->where( array('goods_id' => $goods_id) )->find();

		$goods_quantity=$cart->get_goods_quantity($goods_id);

		$json = array('code' => 0);

		if($goods_quantity<$quantity){
			$json['code'] =3;
			$json['msg']= mb_substr($goods_info['name'],0,4,'utf-8').'...，商品数量不足，剩余'.$goods_quantity.'个！！';

		}else if(!empty($sku_str))
		{
			$mul_opt_arr = array();

			$goods_option_mult_value = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $sku_str,'goods_id'=>$goods_id) )->find();

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

	//加入购物车
	function add(){

		  $data_json = file_get_contents('php://input');
		  $data = json_decode($data_json, true);
		  //$data['buy_type'] == 'dan'
		  if( !isset($data['buy_type']) || empty($data['buy_type']) )
		  {
			  $data['buy_type'] = 'dan';
		  }
		  $token = I('get.token');
		  $weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
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
		$product=M('goods')->find($goods_id);



		if( $product['status'] != 1)
		{
			$json['code'] =6;
			$json['msg']='商品已下架!';
			echo json_encode($json);
			die();
		}


		//判断是否积分兑换
		//type integral
		if( $product['type'] == 'integral')
		{
			//判断积分是否足够 member_id
			$integral_model = D('Seller/Integral');
			$check_result = $integral_model->check_user_score_can_pay($member_id, $data['sku_str'], $goods_id);

			if( $check_result['code'] == 1 )
			{
				$json['code'] =6;
				$json['msg']='剩余'.$check_result['cur_score'].'积分，积分不足!';
				echo json_encode($json);
				die();
			}
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

			$cart=new \Lib\Cart();

			if (isset($data['quantity'])) {
				$quantity = $data['quantity'];
			} else {
				$quantity = 1;
			}

			$option = array();

			if( !empty($data['sku_str'])){
			    $option = explode('_', $data['sku_str']);
			}

            $cart_goods_quantity = $cart->get_wecart_goods($goods_id,$data['sku_str'] ,$token);



			$json=array('code' =>0);
			$goods_model = D('Home/Goods');
			$goods_quantity=$cart->get_goods_quantity($goods_id);



			//检测商品限购 6

			$can_buy_count = $goods_model->check_goods_user_canbuy_count($member_id, $goods_id);
			$goods_description = M('goods_description')->field('per_number')->where( array('goods_id' => $goods_id) )->find();

			if($can_buy_count == -1)
			{
				$json['code'] =6;
				//$json['msg']='已经不能再买了';

				$json['msg']='每人最多购买'.$goods_description['per_number'].'个哦';

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
				$json['msg']='商品数量不足，剩余'.$goods_quantity.'个！！';
				echo json_encode($json);
				die();
			}
			$goods_options=$goods_model->get_goods_options($goods_id);

			if(!empty($option))
			{
				$mul_opt_arr = array();

				$goods_option_mult_value = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $data['sku_str'],'goods_id'=>$goods_id) )->find();

				if( !empty($goods_option_mult_value) )
				{
					if($goods_option_mult_value['quantity']<$quantity+$cart_goods_quantity){
					    $json['code'] =3;
						$json['msg']='商品数量不足，剩余'.$goods_option_mult_value['quantity'].'个！！';
						echo json_encode($json);
						die();
					}
				}
			}
			//buy_type

		   // $this->clear_all_cart();
		    $format_data_array = array('quantity' => $quantity,'goods_id' => $goods_id,'sku_str'=>$data['sku_str'],'buy_type' =>$data['buy_type']);
			//区分活动商品还是普通商品。做两个购物车，活动商品是需要直接购买的，单独购买商品加入正常的购物车TODO....
		    //is_just_addcar 0  1
			if($data['buy_type'] == 'dan' && $is_just_addcar == 0)
		    {

				//$cart->removedancar($token);
				//清空一下购物车
				//singledel
				$format_data_array['is_just_addcar'] = 0;
				$format_data_array['singledel'] = 1;
		        $cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str']);
				$total=$cart->count_goodscar($token);
		    }
			else if($data['buy_type'] == 'dan' && $is_just_addcar == 1)
			{
				//singledel
				$format_data_array['is_just_addcar'] = 1;
				$format_data_array['singledel'] = 1;
				$cart->addwecar($token,$goods_id,$format_data_array,$data['sku_str']);
				$total=$cart->count_goodscar($token);
			}
			else {
		        //buy_type:pin  活动购物车。
		        $pin_id = isset($data['pin_id']) ? $data['pin_id'] : 0;

				$pin_goods =  M('pin_goods')->where(array('goods_id' => $goods_id))->find();

				//lottery
				if( $pin_goods['type'] == 'lottery' && $product['type'] == 'lottery' )
				{
					$now_time = time();
					$lottery_goods_info =  M('lottery_goods')->where( array('goods_id' => $goods_id) )->find();

					if($lottery_goods_info['end_time'] < $now_time)
					{
						$json['code'] =6;
						$json['msg']='抽奖活动已结束';
						echo json_encode($json);
						die();
					}

				}

				//检测商品是否老带新，新人才能参团
				if($pin_id > 0 )
				{

					if($pin_goods['type'] == 'newman')
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
				}

		        $format_data_array['pin_id'] = $pin_id;

		        $cart->add_activitycar($token, $goods_id,$format_data_array,$data['sku_str']);
				$total=$cart->count_activitycar($token);
		    }



			$carts = M('car')->where( "token = '{$token}' and carkey ='cart_total'" )->find();
			if( !empty($carts) )			{
				M('car')->where(  "token = '{$token}' and carkey ='cart_total'" )->save( array('format_data' => $total) );
			} else{
				M('car')->add( array( 'token' => $token,'carkey' =>'cart_total', 'format_data' => $total) );
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

}
