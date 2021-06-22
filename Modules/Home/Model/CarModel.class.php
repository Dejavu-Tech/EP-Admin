<?php
namespace Home\Model;

/**
 * 拼团模型模型
 * @author Albert.Z
 *
 */
class CarModel {
private $data = array();


	public function get_wecartall_goods($goods_id, $sku_str, $community_id,$token,$car_prefix='cart.',$soli_id ='')
	{

		if( $community_id <=0 )
		{
			return 0;
		}

		$key = (int)$goods_id . ':'.$community_id.':';

	    if( !empty($soli_id) )
		{
			$key .= $soli_id.':';
		}

        $key = $car_prefix . $key;


		$s_arr = M('eaterplanet_ecommerce_car')->field('format_data')->where( " token='{$token}' and carkey like '%{$key}%' " )->select();

		$quantity = 0;

		foreach($s_arr as $val)
		{
			$tmp_format_data = unserialize($val['format_data']);

			$quantity += $tmp_format_data['quantity'];
		}

		return $quantity;
	}

	public function get_wecart_goods_solicount($goods_id, $community_id,$token,$soli_id = '')
	{

		$key = (int)$goods_id . ':'.$community_id.':'.$soli_id.':' ;

		$key = 'soitairecart.'.$key;

		$car_sql = "select format_data from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
					where  token ='{$token}' and carkey like '{$key}%' ";
		$s_arr = M()->query($car_sql);

		if( !empty($s_arr) )
		{
			$s_count = 0;

			foreach( $s_arr as $val )
			{
				$tmp_format_data = unserialize($val['format_data']);

				$s_count += $tmp_format_data['quantity'];
			}
			return $s_count;
		}else{
			return 0;
		}

	}




	public function get_wecart_goods($goods_id, $sku_str, $community_id,$token,$car_prefix='cart.',$soli_id = '')
	{

		if( $community_id <=0 )
		{
			return 0;
		}


		$key = (int)$goods_id . ':'.$community_id.':';

	    if( !empty($soli_id) )
		{
			$key .= $soli_id.':';
		}

        if ($sku_str) {
            $key.= base64_encode($sku_str) . ':';
        } else {
            $key.= ':';//xx
        }
        $key = $car_prefix . $key;




		//C('DB_PREFIX')

		$s_arr = M('eaterplanet_ecommerce_car')->field('format_data')->where( array('carkey' => $key,'token' => $token) )->find();

		$tmp_format_data = unserialize($s_arr['format_data']);

		return $tmp_format_data['quantity'];
	}

	//得到商品数量
	public function get_goods_quantity($goods_id) {

		$quantity = D('Seller/Redisorder')->get_goods_total_quantity($goods_id);
		if($quantity <= 0)
		{
			$info = M('eaterplanet_ecommerce_goods')->field('total as quantity')->where(array('id' =>$goods_id))->find();
			$quantity = $info['quantity'];
		}
		return $quantity;
	}

	 public function addwecar($token, $goods_id, $format_data = array() , $option, $community_id,$car_prefix='cart.',$soli_id='') {

        $key = (int)$goods_id . ':'.$community_id.':';

		if( !empty($soli_id) )
		{
			$key .= $soli_id.':';
		}

        $qty = $format_data['quantity'];
        if ($option) {
            $key.= base64_encode($option) . ':';
        } else {
            $key.= ':';
        }


		if( $format_data['is_just_addcar'] == 0 )
		{

			$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}'  and carkey like '".$car_prefix."%' ";
			$all_cart = M()->query($cart_sql);




			if(!empty($all_cart))
			{
				foreach($all_cart as $val)
				{
					$tmp_format_data = unserialize($val['format_data']);
					$tmp_format_data['singledel'] = 0;

					$tmp_format_data_new = array('format_data' => serialize($tmp_format_data) );

					M('eaterplanet_ecommerce_car')->where( array('id' => $val['id']) )->save( $tmp_format_data_new );
				}
			}
		}



		$carkey = $car_prefix.$key;

		$s_arr = M('eaterplanet_ecommerce_car')->field('format_data')->where( array('carkey' => $carkey,'token' => $token)  )->find();


        if ((int)$qty && ((int)$qty > 0)) {
            $key = $car_prefix . $key;
            $s = array();
            if (!empty($s_arr)) {
                $s = unserialize($s_arr['format_data']);
            }
            if (!empty($s)) {
				if( $format_data['is_just_addcar'] == 1 )
				{
					$format_data['quantity']+= $s['quantity'];
				}
            }
        }
        if (!empty($s_arr)) {

			$car_data = array();
			$car_data['format_data'] = serialize($format_data);
			$car_data['modifytime'] = time();


			M('eaterplanet_ecommerce_car')->where( array('token' => $token,'carkey' => $key) )->save( $car_data );

        } else {

			$car_data = array();
			$car_data['token'] = $token;
			$car_data['community_id'] = $community_id;
			$car_data['carkey'] = $key;
			$car_data['modifytime'] = time();
			$car_data['format_data'] = serialize($format_data);

			M('eaterplanet_ecommerce_car')->add( $car_data );


        }
        $this->data = array();
    }

	public function add_activitycar($token, $goods_id, $format_data = array() , $option) {
		global $_W;
		global $_GPC;

        $this->removeActivityAllcar($token);
        $key = (int)$goods_id . ':';
        $qty = $format_data['quantity'];
        if ($option) {
            $key.= base64_encode($option) . ':';
        } else {
            $key.= ':';
        }
        $key.= "buy_type:" ;
        if ((int)$qty && ((int)$qty > 0)) {
            $key = 'cart_activity.' . $key;

			$carkey = 'cart.'.$key;

			$s_arr = M('eaterplanet_ecommerce_car')->where( array('token' => $token, 'carkey' => $carkey) )->find();


            $s = array();
            if (!empty($s_arr)) {
                $s = unserialize($s_arr['format_data']);
            }
            if (!empty($s)) {
                $format_data['quantity']+= $s['quantity'];
            }
            if (!empty($s_arr)) {

				$car_data = array();
				$car_data['format_data'] = serialize($format_data);

				M('eaterplanet_ecommerce_car')->where( array('token' => $token,'carkey' => $key) )->save( $car_data );

            } else {
				$car_data = array();
				$car_data['token'] = $token;
				$car_data['carkey'] = $key;
				$car_data['format_data'] = serialize($format_data);

				M('eaterplanet_ecommerce_car')->add($car_data);
            }
        }
        $this->data = array();
    }

    /**
     * @desc 获取不同类型的购物车数据
     * @param string $buy_type
     * @param $token
     * @param $community_id
     * @param string $soli_id
     * @return array|mixed
     */
    public function getCarDataByTypeAndHeadIdAndToken( $buy_type = 'dan', $token, $community_id,$soli_id='' )
    {
        $cart = [];
        //单独购买类型
        if ($buy_type == 'dan') {
            $cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car  
                    where token='{$token}'  and community_id='{$community_id}' and carkey like 'cart.%' order by modifytime desc ";
            $cart = M()->query($cart_sql);

			}
			else if( $buy_type == 'pindan' )
			{
				$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and community_id={$community_id}  and carkey like 'pindancart.%' order by modifytime desc ";
				$cart = M()->query($cart_sql);

			}else if( $buy_type == 'pintuan' )
			{
				$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and community_id={$community_id} and carkey like 'pintuancart.%' order by modifytime desc ";
				$cart = M()->query($cart_sql);
			}
        else if( $buy_type == 'presale' )
        {
            $cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car  
                    where token='{$token}' and community_id={$community_id} and carkey like 'presalecart.%' order by modifytime desc ";
            $cart = M()->query($cart_sql);
        }
        else if( $buy_type == 'virtualcard' )
        {
            $cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car  
                    where token='{$token}' and community_id={$community_id} and carkey like 'virtualcardcart.%' order by modifytime desc ";
            $cart = M()->query($cart_sql);
        }
			else if( $buy_type == 'soitaire' )
			{
				$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and  community_id={$community_id} and carkey like 'soitairecart.%' order by modifytime desc ";
				$cart_arr = M()->query($cart_sql);



				$cart = array();
				if( !empty($cart_arr) )
				{
					foreach( $cart_arr as $key => $val )
					{
						$key_arr = explode(':', $val['carkey']);
						if( $key_arr[2] == $soli_id )
						{
							$cart[$key] = $val;
						}
					}
				}

			}
			else if( $buy_type == 'integral' )
			{
				$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}'  and community_id={$community_id} and carkey like 'integralcart.%' order by modifytime desc ";

				$cart = M()->query($cart_sql);
			}
			else {
				$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and community_id='{$community_id}' and carkey like 'cart_activity.%' order by modifytime desc ";
				$cart =  M()->query($cart_sql);

			}

        return $cart;
    }

    /**
     * @desc 获取所有购物车中的商品
     * @param string $buy_type
     * @param $token
     * @param int $is_pay_need
     * @param $community_id
     * @param string $soli_id
     * @return array
     */
	 public function get_all_goodswecar($buy_type = 'dan', $token,$is_pay_need = 1, $community_id,$soli_id='') {


        if (!($this->data)) {

            //获取购物车中数据
            $cart = $this->getCarDataByTypeAndHeadIdAndToken( $buy_type , $token, $community_id,$soli_id );

			$is_open_vipcard_buy =  D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
			$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;


			$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

			$member_id = $weprogram_token['member_id'];


			//$goods_model = D('Home/Goods');

            foreach ($cart as $key => $val_uns) {

				$val = unserialize( $val_uns['format_data'] );

				if($buy_type =='dan' && $is_pay_need == 1)
				{
					if(isset($val['singledel']) &&  $val['singledel'] == 0)
					{
						continue;
					}
				}else if($buy_type == 'dan' && $is_pay_need == 0)
				{
					//判断是否支持自提，如果支持自提，那么就不要剔除购物车列表
					//$val['goods_id'] pick_just pick_just

					$goods_common_info = D('Seller/Front')->get_goods_common_field($val['goods_id'] , 'pick_just');

					$pick_just = $goods_common_info['pick_just'];

					if(!empty($pick_just) && isset($pick_just['pick_just']) && $pick_just['pick_just'] > 0)
					{
						//continue;
					}else {


					}
				}



                //$pin_type = $val['pin_type'];
                $quantity = $val['quantity'];
                //$quantity
                $goods = explode(':', $key);
                $goods_id = $val['goods_id'];
                $stock = true;
                // Options sku_str
				$options  = $val['sku_str'];


				$goods_query_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as p left join ".C('DB_PREFIX')."eaterplanet_ecommerce_good_commiss as pd
									on p.id = pd.goods_id where p.id ={$goods_id}   ";

				$goods_query_arr = M()->query($goods_query_sql);

				$goods_query = $goods_query_arr[0];

				$goods_query['model'] = $goods_query['codes'];

                if ($goods_query) {
                    $option_price = null;
                    $option_weight = $goods_query['weight'];
                    $option_data = array();

					$max_quantity = $goods_query['total'];
                    if (!empty($options)) {

						$goods_option_mult_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $goods_id,'option_item_ids' => $options)  )->find();


						$goods_option_mult_value['pin_price'] = $goods_option_mult_value['pinprice'];
						$goods_option_mult_value['dan_price'] = $goods_option_mult_value['marketprice'];
						$goods_option_mult_value['costprice'] = $goods_option_mult_value['costprice'];
						$goods_query['card_price'] = $goods_option_mult_value['card_price'];
						$goods_query['costprice'] = $goods_option_mult_value['costprice'];
						$goods_query['model'] = $goods_option_mult_value['goodssn'];


                        $options_arr = array();
                        $option_value_id_arr = explode('_', $options);
                        foreach ($option_value_id_arr as $id_val) {
							$goods_option_value = M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_id' => $goods_id,'id' => $id_val) )->find();
                            $options_arr[$goods_option_value['goods_option_id']] = $goods_option_value['id'];
                        }
                    }

					$option_value_image = '';
                    if($options_arr){
	                    foreach ($options_arr as $goods_option_id => $goods_option_item_id ) {
							//option_value
							$option_query = M('eaterplanet_ecommerce_goods_option')->where( array('goods_id' => $goods_id,'id' => $goods_option_id) )->find();

	                        if ($option_query) {

								$option_value_query = M('eaterplanet_ecommerce_goods_option_item')->where( array('id' => $goods_option_item_id,'goods_id' => $goods_id) )->find();

	                            if ($option_value_query) {

									if( !empty($option_value_query['thumb']) )
									{
										$option_value_image = $option_value_query['thumb'];
									}

									$max_quantity = $goods_option_mult_value['stock'];

	                                //根据商品类型获取不同价格 begin  pinprice pin_price  productprice  dan_price
	                                if ($buy_type == 'pintuan' && $goods_query['type'] != 'spike') {
	                                    $option_price = $goods_option_mult_value['pin_price'];
	                                }else if($goods_query['type'] == 'spike')
									{
										$option_price = $goods_option_mult_value['dan_price'];
									}
	                                 else {
	                                    $option_price = $goods_option_mult_value['dan_price'];
	                                }
	                                //根据商品类型获取不同价格 begin
	                                $option_weight = $goods_option_mult_value['weight'];
	                                $costprice = $goods_option_mult_value['costprice'];
	                                $option_data[] = array(
	                                    'goods_option_id' => $goods_option_id,
	                                    'goods_option_value_id' => $goods_option_item_id,
	                                    'option_id' => $goods_option_id,
	                                    'option_value_id' => $goods_option_item_id,
	                                    'name' => $option_query['title'],
	                                    'value' => $option_value_query['title'],
	                                    'quantity' => $quantity,
	                                    'price' => $option_price,
										'card_price' => $goods_option_mult_value['card_price'],
										'costprice' => $costprice,
	                                    'weight' => $option_weight,
	                                );
	                            }
	                        }
	                    }
                    }


                    $header_disc = 100;
					$shop_price = $goods_query['productprice'];

					$goods_query['price'] = $goods_query['price'];

					$thumb_image = D('Home/Pingoods')->get_goods_images($goods_id);

					if( !empty($thumb_image) )
					{
						$thumb_image = tomedia($thumb_image['image']);
					}


					if(!empty($option_value_image))
					{
						$thumb_image = tomedia($option_value_image);
					}

					$store_info = array('s_true_name' => '');
					$s_logo = D('Seller/Front')->get_config_by_name('shoplogo');

					if( !empty($s_logo) )
					{
						$s_logo = tomedia($s_logo);
					}


                    //$goods_query['price']

                    if ( !is_null($option_price)) {
                        $goods_query['price'] = $option_price;
                    } else {
                        //根据商品类型获取不同价格 begin
                        if ($buy_type == 'pintuan') {//判断类型是否是积分商品
							if($goods_query['type'] == 'integral')
							{
								//TODO....等待打开积分
								//$intgral_goods_info = M('intgral_goods')->field('score')->where( array('goods_id' => $goods_id) )->find();
								//$goods_query['price'] = $intgral_goods_info['score'];
							}
							else if($goods_query['type'] == 'spike')
							{

							}
							else{

								if($goods_query['type'] == 'pin')
								{
									//ims_
									$pin_goods = M('eaterplanet_ecommerce_good_pin')->where( array('goods_id' => $goods_id) )->find();


									$goods_query['price'] = $pin_goods['pinprice'];
								}
							}

                        }
                        //根据商品类型获取不同价格 begin

                    }
                    if (!empty($option_weight)) {
                        $goods_query['weight'] = $option_weight;
                    }
                    //拼团才会有pin_id
                    $pin_id = 0;
                    if ($buy_type == 'pintuan' && isset($val['pin_id'])) {
                        $pin_id = $val['pin_id'];
                    }
					$price = $goods_query['price'];
					//判断是否有团长折扣 暂时屏蔽 TODO.....
					/**
					if( $buy_type == 'pin' && $pin_id == 0 && $goods_query['head_disc'] != 100)
					{
						$price = round(( $price * intval($goods_query['head_disc']) )/100,2);
						$header_disc = intval($goods_query['head_disc']);
					}
					**/

					//判断是否客户折扣  TODO。。。先关闭
					$level_info = array('member_discount' => 100,'level_name' =>'');

					$member_info = M('eaterplanet_ecommerce_member')->field('level_id')->where( array('member_id' => $member_id) )->find();
					//修改商品独立客户等级折扣设置 2020.05.11
					$goods_common = M('eaterplanet_ecommerce_good_common')->field('is_mb_level_buy,has_mb_level_buy,mb_level_buy_list,packing_free,goods_start_count')->where( array('goods_id' => $goods_id ) )->find();

					$goods_query['is_mb_level_buy'] = 0;
					$goods_query['levelprice'] = 0;
					$goods_query['goods_start_count'] = $goods_common['goods_start_count'];

					if( $buy_type == 'dan' || $buy_type == 'soitaire' )
					{
						//商品独立客户等级折扣 begin 关闭客户折扣
						if($member_info['level_id'] > 0 && $goods_common['has_mb_level_buy'] == 1 && $goods_common['is_mb_level_buy'] == 1 && $buy_type != 'pesale' ){
							$member_level_info = M('eaterplanet_ecommerce_member_level')->where( array('id' => $member_info['level_id'] ) )->find();

							$mb_level_buy_list = unserialize($goods_common['mb_level_buy_list']);
							$mb_level_discount_list = array();
							foreach($mb_level_buy_list as $k=>$v){
								$mb_level_discount_list[$v['level_id']] = $v['discount'];
							}
							if(isset($mb_level_discount_list[$member_info['level_id']]) && !empty($mb_level_discount_list[$member_info['level_id']])){
								$level_info['member_discount'] = $mb_level_discount_list[$member_info['level_id']];
							}else{
								$level_info['member_discount'] = 0;
							}

							if($level_info['member_discount']>0 && $level_info['member_discount'] <100)
							{
								$level_info['level_name'] = $member_level_info['levelname'];
								if($level_info['member_discount'] == 0){
									$price2 = round( $price,2);
								}else{
									$price2 = round(( $price * $mb_level_discount_list[$member_info['level_id']] )/100,2);
								}


								$goods_query['is_mb_level_buy'] = 1;
								$goods_query['levelprice'] = $price2;
								//商品独立客户等级折扣 end
							}
						}else{
						    //预售不参与客户等级折扣
							if($member_info['level_id'] > 0 && $goods_common['is_mb_level_buy'] == 1  && $buy_type != 'pesale')
							{
								$member_level_info = M('eaterplanet_ecommerce_member_level')->where( array('id' => $member_info['level_id'] ) )->find();

								$level_info['member_discount'] = $member_level_info['discount'] ;

								if( $level_info['member_discount'] > 0 && $level_info['member_discount'] < 100 )
								{
									$level_info['level_name'] = $member_level_info['levelname'];

									$price2 = round(( $price * $member_level_info['discount'] )/100,2);

									$goods_query['is_mb_level_buy'] = 1;
									$goods_query['levelprice'] = $price2;

									//$goods_query['price'] = $price2 ;

									//$price = $price2;//['price'];
								}

							}
						}
					}


					//判断商品能否参与满减活动  fullreduction_money
					$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
					$can_man_jian = 0;

					//预售不参与满减
                    if( $buy_type == 'pesale')
                    {
                       $is_open_fullreduction = 0;
                    }

					if( $buy_type == 'dan' || $buy_type == 'soitaire'  || $buy_type == 'virtualcard' )
					{
						if( !empty($is_open_fullreduction) && $is_open_fullreduction == 1)
						{
							 $gd_full_info = D('Home/Front')->get_goods_common_field($goods_id , 'is_take_fullreduction,supply_id,is_new_buy');
							 $is_take_fullreduction = $gd_full_info['is_take_fullreduction'];

							 //supply_id
							 if( $gd_full_info['supply_id'] > 0)
							 {

								$supply_info = M('eaterplanet_ecommerce_supply')->field('type')->where( array('id' => $gd_full_info['supply_id'] ) )->find();


								if(!empty($supply_info) && $supply_info['type'] == 1)
								{
									$can_man_jian = 0;
								}else{

									 if( $is_take_fullreduction == 1 )
									 {
										 $can_man_jian = 1;
									 }
								}
							 }else{
								if( $is_take_fullreduction == 1 )
								 {
									 $can_man_jian = 1;
								 }
							 }
							  $is_new_buy = $gd_full_info['is_new_buy'];
						}
					}


					$is_open_only_express = D('Home/Front')->get_config_by_name('is_open_only_express');

					$is_only_express = 0;

					if( !empty($is_open_only_express) && $is_open_only_express == 1)
					{
						$gd_s_info = D('Home/Front')->get_goods_common_field($goods_id , 'is_only_express');
						$is_only_express = $gd_s_info['is_only_express'];
					}

					if( $is_only_express  == 0 )
					{
						$gd_s_info = D('Home/Front')->get_goods_common_field($goods_id , 'is_only_distribution,supply_id,is_only_hexiao');
						$is_only_distribution = $gd_s_info['is_only_distribution'];

						$isopen_localtown_delivery = D('Home/Front')->get_config_by_name('isopen_localtown_delivery');
						if($gd_s_info['supply_id'] > 0){
							$isopen_supply_localtown_delivery = D('Home/Front')->get_supply_config_by_name('isopen_localtown_delivery',$gd_s_info['supply_id']);
							if( $is_only_distribution == 1 && $isopen_localtown_delivery == 1 && $isopen_supply_localtown_delivery == 1)
							{
								$is_only_express = 3;
							}
						}else{
							if( $is_only_distribution == 1 && $isopen_localtown_delivery == 1)
							{
								$is_only_express = 3;
							}
						}
						if($gd_s_info['is_only_hexiao'] == 1){
							$is_only_express = 2;
						}
					}

					// $is_open_vipcard_buy
					if( $is_open_vipcard_buy == 1 && $goods_query['is_take_vipcard'] == 1 )
					{

					}else{
						$goods_query['is_take_vipcard'] = 0;
					}

                    //拼团 end

                    //begin 检测是否校验组件
                    $isTradeComponts = D('Seller/MpModifyTradeComponts')->checkGoodsIsTradeComponts( $goods_id );
                    //end 检测是否校验组件
                    $this->data[$key] = array(
                        'key' => $val_uns['carkey'],
                        'goods_id' => $goods_id ,
						'is_only_express' => $is_only_express,
                        'name' => $goods_query['goodsname'],
						'seller_name' => $store_info['s_true_name'],
						'seller_logo' => $s_logo,
                        'weight' => $option_weight,
						'singledel' => $val['singledel'],
						//$val['singledel']
						'can_man_jian' => $can_man_jian,
                        'header_disc' => $header_disc,
						'member_disc' => $level_info['member_discount'],
                        'level_name' => $level_info['level_name'],
                        'pin_id' => $pin_id,
                        'shipping' => $goods_query['dispatchtype'],
                        'goods_freight' => $goods_query['dispatchprice'],
                        'transport_id' => $goods_query['dispatchid'],
                        'image' => $thumb_image,
                        'quantity' => $quantity,
						'max_quantity' => $max_quantity,
						'shop_price' => $shop_price,
                        'price' => $goods_query['price'],
						'costprice' => $goods_query['costprice'],
						'levelprice' => $goods_query['levelprice'],
						'card_price' => $goods_query['card_price'],
                        'total' => round( ($price) * $quantity , 2),
						'level_total' => round( $goods_query['levelprice'] * $quantity , 2),
						'card_total' => round($goods_query['card_price'] * $quantity ,2),
						'is_take_vipcard' => $goods_query['is_take_vipcard'],
						'is_mb_level_buy' => $goods_query['is_mb_level_buy'],
                         'model' => $goods_query['model'],
                        'option' => $option_data,
						'sku_str' => $val['sku_str'],
						'soli_id' => isset($val['soli_id']) ?  intval($val['soli_id']) : 0,
						'is_new_buy' => $is_new_buy,
                        'packing_free' => $goods_common['packing_free'],
						'goods_start_count' => $goods_query['goods_start_count'],
						'isTradeComponts' => $isTradeComponts,//是否参与交易组件
                    );



                } else {
                    $this->removecar($key,$token);
                }
            }
        }


        return $this->data;
    }

	//删除商品
    public function removecar($key,$token) {

        $key =  $key; //重新给$key赋值

		M('eaterplanet_ecommerce_car')->where( array('token' => $token,'carkey' => $key) )->delete();
    }

	//购物车是否为空
	public function has_goodswecar($buy_type = 'dan', $token, $community_id) {


		if ($buy_type == 'dan') {
			$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and community_id='{$community_id}' and carkey like 'cart.%' ";
			$s = M()->query($cart_sql);

		}
		else if( $buy_type == 'pindan' )
		{
			$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}'  and carkey like 'pindancart.%' ";
			$s = M()->query($cart_sql);

		}else if( $buy_type == 'pintuan' )
		{

			$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}'  and carkey like 'pintuancart.%' ";
			$s = M()->query($cart_sql);
		}

		else if( $buy_type == 'soitaire' )
		{
			$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and carkey like 'soitairecart.%' ";
			$s = M()->query($cart_sql);
		}

        else if( $buy_type == 'presale' )
        {
            $cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car  
						where token='{$token}' and carkey like 'presalecart.%' ";
            $s = M()->query($cart_sql);
        }
        else if( $buy_type == 'virtualcard' )
        {
            $cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car  
						where token='{$token}' and carkey like 'virtualcardcart.%' ";
            $s = M()->query($cart_sql);
        }

		else if( $buy_type == 'integral' )
		{
			$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and carkey like 'integralcart.%' ";
			$s = M()->query($cart_sql);
		}
		else if ($buy_type == 'pin') {

			$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}'  and carkey like 'cart_activity.%' ";
			$s = M()->query($cart_sql);
		}
		return count($s);
	}


	public function count_activitycar($token) {

        $quantity = 0;

		$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and carkey like 'cart_activity.%' ";
		$cart = M()->query($cart_sql);

		foreach ($cart as $key => $val) {
			$format_data = unserialize($val['format_data']);
			$quantity += $format_data['quantity'];
		}
		return $quantity;
	}

	public function removeActivityAllcar($token ,$car_prefix='pindancart.') {

		M('eaterplanet_ecommerce_car')->where(" token = '{$token}' and carkey like '".$car_prefix."%' ")->delete();
    }

	public function count_goodscar($token, $community_id) {

        $quantity = 0;

		$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and community_id='{$community_id}' and carkey like 'cart.%' ";
		$cart = M()->query($cart_sql);


		foreach ($cart as $key => $val) {
			$format_data = unserialize($val['format_data']);
			if(isset($format_data['quantity']))
				$quantity += $format_data['quantity'];
		}

		return $quantity;
	}

	/**
	 * @desc 获取客户购物车中新人专享商品种类数
	 * @param $token
	 * @param $gid 商品id
	 * @param $sku_str 商品规格id
	 * @return int
	 */
	public function get_new_goods_count($token,$gid,$sku_str){
		$count = 0;
		$cart_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_car
						where token='{$token}' and carkey like 'cart.%' ";
		$cart = M()->query($cart_sql);
		foreach ($cart as $key => $val) {
			$format_data = unserialize($val['format_data']);
			$goods_id = $format_data['goods_id'];
			$goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'is_new_buy');
			if( !empty($goods_description['is_new_buy']) &&  $goods_description['is_new_buy'] == 1){
				if($goods_id != $gid){
					$count++;
				}else{
					if(!empty($sku_str) && $sku_str != $format_data['sku_str']){
						$count++;
					}
				}
			}
		}
		return $count;
	}

}
