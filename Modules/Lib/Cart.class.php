<?php
// $Id:$
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
namespace Lib;
class Cart {
    private $data = array();
    public function get_cart_goods($goods_id, $sku_str) {
        $key = (int)$goods_id . ':';
        //$qty = 1
        if ($sku_str) {
            $key.= base64_encode($sku_str) . ':';
        } else {
            $key.= ':';//xx
        }
        $key = 'cart.' . $key;
        $s = session($key);
        return $s['quantity'];
    }

	public function get_wecart_goods($goods_id, $sku_str,$token)
	{
		$key = (int)$goods_id . ':';

        if ($sku_str) {
            $key.= base64_encode($sku_str) . ':';
        } else {
            $key.= ':';//xx
        }
        $key = 'cart.' . $key;

		$s_arr = M('car')->field('format_data')->where(array(
            'token' => $token,
            'carkey' => $key
        ))->find();
		$tmp_format_data = unserialize($s_arr['format_data']);

		return $tmp_format_data['quantity'];
	}

    //加入购物车
    //public function add($goods_id, $qty = 1, $option) {
    public function add($goods_id, $format_data = array() , $option) {
        $key = (int)$goods_id . ':';
        //$qty = 1
        $qty = $format_data['quantity'];
        if ($option) {
            $key.= base64_encode($option) . ':';
        } else {
            $key.= ':';
        }


		$cart = session('cart');
		$hashids = new \Lib\Hashids(C('PWD_KEY') , C('URL_ID'));
		foreach ($cart as $kk => $val) {
			$kk = 'cart.' . $kk;
			$val['singledel'] = 0;
			session($kk, $val);
		}

        if ((int)$qty && ((int)$qty > 0)) {
            $key = 'cart.' . $key;
            $s = session($key);
            //pin_type: pin, dan
            //
            //合并购物车商品
            if (!empty($s)) {
				if($format_data['singledel'] == 1)
				{
					$format_data['old_quantity'] = $s['quantity'];
				}else{
					$format_data['quantity']+= $s['quantity'];
				}
            }
            session($key, $format_data);
        }
        $this->data = array();
    }
    public function addwecar($token, $goods_id, $format_data = array() , $option) {
        $key = (int)$goods_id . ':';
        $qty = $format_data['quantity'];
        if ($option) {
            $key.= base64_encode($option) . ':';
        } else {
            $key.= ':';
        }

		if( $format_data['is_just_addcar'] == 0 )
		{
			$all_cart = M('car')->where("token = '{$token}' and carkey like 'cart.%' ")->select();

			if(!empty($all_cart))
			{
				foreach($all_cart as $val)
				{
					$tmp_format_data = unserialize($val['format_data']);
					$tmp_format_data['singledel'] = 0;
					M('car')->where( array('id' => $val['id']) )->save( array('format_data' => serialize($tmp_format_data) ) );
				}
			}
		}

        $s_arr = M('car')->field('format_data')->where(array(
            'token' => $token,
            'carkey' => 'cart.'.$key
        ))->find();

		//`carkey` =  'cart.27::'
		//$format_data_array['is_just_addcar'] = 1;

		//var_dump( M('car')->getLastSql() );die();
        if ((int)$qty && ((int)$qty > 0)) {
            $key = 'cart.' . $key;
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
            M('car')->where(array(
                'token' => $token,
                'carkey' => $key
            ))->save(array(
				'modifytime' => time(),
                'format_data' => serialize($format_data)
            ));
        } else {
            M('car')->add(array(
                'token' => $token,
                'carkey' => $key,
				'modifytime' => time(),
                'format_data' => serialize($format_data)
            ));
        }
        $this->data = array();
    }
    public function add_activitycar($token, $goods_id, $format_data = array() , $option) {
        $this->removeActivityAllcar($token);
        $key = (int)$goods_id . ':';
        $qty = $format_data['quantity'];
        if ($option) {
            $key.= base64_encode($option) . ':';
        } else {
            $key.= ':';
        }
        $key.= "buy_type:" . $option['buy_type'];
        if ((int)$qty && ((int)$qty > 0)) {
            $key = 'cart_activity.' . $key;
            $s_arr = M('car')->where(array(
                'token' => $token,
                'carkey' => $key
            ))->find();
            $s = array();
            if (!empty($s_arr)) {
                $s = unserialize($s_arr['format_data']);
            }
            if (!empty($s)) {
                $format_data['quantity']+= $s['quantity'];
            }
            if (!empty($s_arr)) {
                M('car')->where(array(
                    'token' => $token,
                    'carkey' => $key
                ))->save(array(
                    'format_data' => serialize($format_data)
                ));
            } else {
                M('car')->add(array(
                    'token' => $token,
                    'carkey' => $key,
                    'format_data' => serialize($format_data)
                ));
            }
        }
        $this->data = array();
    }
    public function add_activity($goods_id, $format_data = array() , $option) {
        $this->removeActivityAll(); //活动商品只能单独购买
        $key = (int)$goods_id . ':';
        //$qty = 1
        $qty = $format_data['quantity'];
        if ($option) {
            $key.= base64_encode($option) . ':';
        } else {
            $key.= ':';
        }
        $key.= "buy_type:" . $option['buy_type'];
        if ((int)$qty && ((int)$qty > 0)) {
            $key = 'cart_activity.' . $key;
            $s = session($key);
            //pin_type: pin, dan
            //
            //合并购物车商品
            if (!empty($s)) {
                $format_data['quantity']+= $s['quantity'];
            }
            session($key, $format_data);
        }
        $this->data = array();
    }
    public function removeActivityAll() {
        $s = session('cart_activity');
        if (isset($s)) {
            foreach ($s as $k => $v) {
                $key = 'cart_activity.' . $k; //重新给$key赋值
                session($key, null);
            }
        }
    }
    public function removeActivityAllcar($token) {
        M('car')->where(" token ='{$token}' and carkey like 'cart_activity.%' ")->delete();
    }
    //删除所有购物车商品
    public function removeAll() {
        $s = session('cart');
        if (isset($s)) {
            foreach ($s as $k => $v) {
                $key = 'cart.' . $k; //重新给$key赋值
                session($key, null);
            }
        }
    }

	//清空商品
    public function removedancar($token) {
        //$key = 'cart.' . $key; //重新给$key赋值

		//M('car')->where( array('token' => $token, 'carkey' => $key) )->delete();
		M('car')->where(" token ='{$token}' and carkey like 'cart.%' ")->delete();

        //session($key, null);
    }

	//删除商品
    public function removecar($key,$token) {
        $key = 'cart.' . $key; //重新给$key赋值

		M('car')->where( array('token' => $token, 'carkey' => $key) )->delete();

        //session($key, null);
    }
    //删除商品
    public function remove($key) {
        $key = 'cart.' . $key; //重新给$key赋值
        session($key, null);
    }
    //更新购物车
    public function update($key, $qty) {
        $ckey = 'cart.' . $key;
        if ((int)$qty && ((int)$qty > 0)) {
            $arr = session($ckey);
            $arr['quantity'] = (int)$qty;
            session($ckey, $arr);
        } else {
            $this->remove($key);
        }
    }

	//获取购物车全部商品
    public function get_all_goodswecar($buy_type = 'dan', $token,$is_pay_need = 1) {
        if (!($this->data)) {

			if ($buy_type == 'dan') {
				$cart = M('car')->where("token = '{$token}' and carkey like 'cart.%' ")->order('modifytime desc')->select();
			} else {
				$cart = M('car')->where("token = '{$token}' and carkey like 'cart_activity.%' ")->select();
			}


			$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
			$member_id = $weprogram_token['member_id'];
			$goods_model = D('Home/Goods');

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
					//$val['goods_id'] pick_just
					$pick_just = M('goods')->field('pick_just')->where( array('goods_id' => $val['goods_id']) )->find();


					if($pick_just['pick_just'] > 0)
					{
						continue;
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

                $goods_query = M()->query("SELECT * FROM " . C('DB_PREFIX') . "goods p LEFT JOIN " . C('DB_PREFIX') . "goods_description pd ON (p.goods_id = pd.goods_id) WHERE p.goods_id = " . (int)$goods_id . " AND p.status = 1");
                if ($goods_query) {
                    $option_price = null;
                    $option_weight = 0;
                    $option_data = array();

					$max_quantity = $goods_query[0]['quantity'];
                    if (!empty($options)) {
                        //$options  "36_39"//option_value_id  $goods_id
                        $goods_option_mult_value = M('goods_option_mult_value')->where(array(
                            'rela_goodsoption_valueid' => $options,
                            'goods_id' => $goods_id
                        ))->find();
						//["pin_price"]=> string(4) "0.02" ["dan_price"]=> string(4) "0.02"

                        $options_arr = array();
                        $option_value_id_arr = explode('_', $options);
                        foreach ($option_value_id_arr as $id_val) {
                            $goods_option_value = M('goods_option_value')->where(array(
                                'option_value_id' => $id_val,
                                'goods_id' => $goods_id
                            ))->find();
                            $options_arr[$goods_option_value['goods_option_id']] = $goods_option_value['goods_option_value_id'];
                        }
                    }
                    foreach ($options_arr as $goods_option_id => $option_value) {
                        $option_query = M()->query("SELECT po.goods_option_id, po.option_id, o.name FROM " . C('DB_PREFIX') . "goods_option po LEFT JOIN `" . C('DB_PREFIX') . "option` o ON (po.option_id = o.option_id)
						  WHERE po.goods_option_id = '" . (int)$goods_option_id . "' AND po.goods_id = " . (int)$goods_id);
                        if ($option_query) {
                            $option_value_query = M()->query("SELECT pov.option_value_id,
				ov.value_name, pov.quantity, pov.subtract, pov.price, pov.price_prefix,pov.weight, pov.weight_prefix FROM " . C('DB_PREFIX') . "goods_option_value pov LEFT JOIN " . C('DB_PREFIX') . "option_value ov ON (pov.option_value_id = ov.option_value_id) WHERE pov.goods_option_value_id = '" . (int)$option_value . "' AND pov.goods_option_id = " . (int)$goods_option_id);
                            if ($option_value_query) {

								$max_quantity = $goods_option_mult_value['quantity'];

                                //根据商品类型获取不同价格 begin
                                if ($buy_type == 'pin') {
                                    $option_price = $goods_option_mult_value['pin_price'];
                                } else {
                                    $option_price = $goods_option_mult_value['dan_price'];
                                }
                                //根据商品类型获取不同价格 begin
                                $option_weight = $goods_option_mult_value['weight'];
                                if ($option_value_query[0]['subtract'] && (!$option_value_query[0]['quantity'] || ($option_value_query[0]['quantity'] < $quantity))) {
                                    $stock = false;
                                }
                                $option_data[] = array(
                                    'goods_option_id' => $goods_option_id,
                                    'goods_option_value_id' => $option_value,
                                    'option_id' => $option_query[0]['option_id'],
                                    'option_value_id' => $option_value_query[0]['option_value_id'],
                                    'name' => $option_query[0]['name'],
                                    'value' => $option_value_query[0]['value_name'],
                                   // 'type' => $option_query[0]['type'],
                                    'quantity' => $quantity,
                                    'subtract' => $option_value_query[0]['subtract'],
                                    'price' => $option_price,
                                    //'price_prefix' => $option_value_query[0]['price_prefix'],
                                    'weight' => $option_weight,
                                    //'weight_prefix' => $option_value_query[0]['weight_prefix']
                                );
                            }
                        }
                    }
                    $header_disc = 100;
					$shop_price = $goods_query[0]['price'];
					 $goods_query[0]['price'] = $goods_query[0]['danprice'];

                    $thumb_image = C('SITE_URL').resize($goods_query[0]['image'], C('goods_cart_thumb_width') , C('goods_cart_thumb_height'));
                    if (!empty($goods_query[0]['image']['fan_image'])) {
                        $thumb_image = C('SITE_URL').resize($goods_query[0]['fan_image'], C('goods_cart_thumb_width') , C('goods_cart_thumb_height'));
                    }

					$store_info = M('seller')->field('s_true_name,s_logo')->where('s_id='.$goods_query[0]['store_id'])->find();

					$s_logo = C('SITE_URL').'/Uploads/image/'.$store_info['s_logo'];


                    //$goods_query['price']

                    if ( !is_null($option_price)) {
                        $goods_query[0]['price'] = $option_price;
                    } else {
                        //根据商品类型获取不同价格 begin
                        if ($buy_type == 'pin') {//判断类型是否是积分商品
							if($goods_query[0]['type'] == 'integral')
							{
								$intgral_goods_info = M('intgral_goods')->field('score')->where( array('goods_id' => $goods_id) )->find();
								$goods_query[0]['price'] = $intgral_goods_info['score'];
							}else{
								$pin_goods = M('pin_goods')->field('pin_price')->where(array(
									'goods_id' => $goods_id
								))->find();
								$goods_query[0]['price'] = $pin_goods['pin_price'];
							}

                        }
                        //根据商品类型获取不同价格 begin

                    }
                    if (!empty($option_weight)) {
                        $goods_query[0]['weight'] = $option_weight;
                    }
                    //拼团才会有pin_id
                    $pin_id = 0;
                    if ($buy_type == 'pin' && isset($val['pin_id'])) {
                        $pin_id = $val['pin_id'];
                    }
					$price = $goods_query[0]['price'];
					//判断是否有团长折扣
					if( $buy_type == 'pin' && $pin_id == 0 && $goods_query[0]['head_disc'] != 100)
					{
						//&& $goods_query[0]['head_disc'] != 100

						$price = round(( $price * intval($goods_query[0]['head_disc']) )/100,2);

						//$goods_query[0]['price'] = $price;

						$header_disc = intval($goods_query[0]['head_disc']);
					}

					//判断是否客户折扣
					$level_info = $goods_model->get_member_level_info($member_id, $goods_id);


					//$result['member_discount'] = 100;
					if($level_info['member_discount'] > 0 && $level_info['member_discount'] != 100)
					{
						$price = round(( $price * $level_info['member_discount'])/100,2);
					}



                    //拼团 end
                    $this->data[$key] = array(
                        'key' => $val_uns['carkey'],
                        'goods_id' => $goods_query[0]['goods_id'] ,
                        'name' => $goods_query[0]['name'],
						'seller_name' => $store_info['s_true_name'],
						'seller_logo' => $s_logo,
                        'weight' => $option_weight,
						'singledel' => $val['singledel'],
						//$val['singledel']
                        'header_disc' => $header_disc,
						'member_disc' => $level_info['member_discount'],
                        'level_name' => $level_info['level_name'],
                        'pin_id' => $pin_id,
                        'shipping' => $goods_query[0]['shipping'],
                        'goods_freight' => $goods_query[0]['goods_freight'],
                        'transport_id' => $goods_query[0]['transport_id'],
                        'image' => $thumb_image,
                        'quantity' => $quantity,
						'max_quantity' => $max_quantity,
						'shop_price' => $shop_price,
                        'price' => $goods_query[0]['price'],
                        'total' => ($price) * $quantity,
                        //'model' => $goods_query[0]['model'],
                        'option' => $option_data,
						'sku_str' => $val['sku_str'],

                    );
                } else {
                    $this->removecar($key,$token);
                }
            }
        }
        return $this->data;
    }

    //获取购物车全部商品
    public function get_all_goods($buy_type = 'dan',$singledel =0) {
        if (!($this->data)) {
            if ($buy_type == 'dan') {
                $cart = session('cart');
            } else {
                $cart = session('cart_activity');
            }
            $hashids = new \Lib\Hashids(C('PWD_KEY') , C('URL_ID'));
            foreach ($cart as $key => $val) {
				if($buy_type =='dan' && $singledel == 1)
				{
					if($val['singledel'] == 0)
					{
						continue;
					}
				}
                //$pin_type = $val['pin_type'];
                $quantity = $val['quantity'];
                //$quantity
                $goods = explode(':', $key);
                $goods_id = $goods[0];
                $stock = true;
                // Options
                if (!empty($goods[1])) {
                    $options = base64_decode($goods[1]);
                } else {
                    $options = array();
                }
                $goods_query = M()->query("SELECT * FROM " . C('DB_PREFIX') . "goods p LEFT JOIN " . C('DB_PREFIX') . "goods_description pd ON (p.goods_id = pd.goods_id) WHERE p.goods_id = " . (int)$goods_id . " AND p.status = 1");
                if ($goods_query) {
                    $option_price = -1;
                    $option_weight = 0;
                    $option_data = array();
                    if (!empty($options)) {
                        //$options  "36_39"//option_value_id  $goods_id
                        $goods_option_mult_value = M('goods_option_mult_value')->where(array(
                            'rela_goodsoption_valueid' => $options,
                            'goods_id' => $goods_id
                        ))->find();
                        $options_arr = array();
                        $option_value_id_arr = explode('_', $options);
                        foreach ($option_value_id_arr as $id_val) {
                            $goods_option_value = M('goods_option_value')->where(array(
                                'option_value_id' => $id_val,
                                'goods_id' => $goods_id
                            ))->find();
                            $options_arr[$goods_option_value['goods_option_id']] = $goods_option_value['goods_option_value_id'];
                        }
                    }
                    foreach ($options_arr as $goods_option_id => $option_value) {
                        $option_query = M()->query("SELECT po.goods_option_id, po.option_id, o.name FROM " . C('DB_PREFIX') . "goods_option po LEFT JOIN `" . C('DB_PREFIX') . "option` o ON (po.option_id = o.option_id)
						  WHERE po.goods_option_id = '" . (int)$goods_option_id . "' AND po.goods_id = " . (int)$goods_id);
                        if ($option_query) {
                            $option_value_query = M()->query("SELECT pov.option_value_id,
				ov.value_name, pov.quantity, pov.subtract, pov.price, pov.price_prefix,pov.weight, pov.weight_prefix FROM " . C('DB_PREFIX') . "goods_option_value pov LEFT JOIN " . C('DB_PREFIX') . "option_value ov ON (pov.option_value_id = ov.option_value_id) WHERE pov.goods_option_value_id = '" . (int)$option_value . "' AND pov.goods_option_id = " . (int)$goods_option_id);
                            if ($option_value_query) {
                                //根据商品类型获取不同价格 begin
                                if ($buy_type == 'pin') {
                                    $option_price = $goods_option_mult_value['pin_price'];
                                } else {
                                    $option_price = $goods_option_mult_value['dan_price'];
                                }
                                //根据商品类型获取不同价格 begin
                                $option_weight = $goods_option_mult_value['weight'];
                                if ($option_value_query[0]['subtract'] && (!$option_value_query[0]['quantity'] || ($option_value_query[0]['quantity'] < $quantity))) {
                                    $stock = false;
                                }
                                $option_data[] = array(
                                    'goods_option_id' => $goods_option_id,
                                    'goods_option_value_id' => $option_value,
                                    'option_id' => $option_query[0]['option_id'],
                                    'option_value_id' => $option_value_query[0]['option_value_id'],
                                    'name' => $option_query[0]['name'],
                                    'value' => $option_value_query[0]['value_name'],
                                    'type' => $option_query[0]['type'],
                                    'quantity' => $quantity,
                                    'subtract' => $option_value_query[0]['subtract'],
                                    'price' => $option_price,
                                    'price_prefix' => $option_value_query[0]['price_prefix'],
                                    'weight' => $option_weight,
                                    'weight_prefix' => $option_value_query[0]['weight_prefix']
                                );
                            }
                        }
                    }
                    $header_disc = 100;
                    $thumb_image = resize($goods_query[0]['image'], C('goods_cart_thumb_width') , C('goods_cart_thumb_height'));
                    if (!empty($goods_query[0]['image']['fan_image'])) {
                       // $thumb_image = resize($goods_query[0]['fan_image'], C('goods_cart_thumb_width') , C('goods_cart_thumb_height'));
                    }



                    $goods_query[0]['price'] = $goods_query[0]['danprice'];


                    if (floatval($option_price) >= 0) {
                        $goods_query[0]['price'] = $option_price;
                    } else {
                        //根据商品类型获取不同价格 begin
                        if ($buy_type == 'pin') {
							if($goods_query[0]['type'] == 'integral')
							{
								$intgral_goods_info = M('intgral_goods')->field('score')->where( array('goods_id' => $goods_id) )->find();
								$goods_query[0]['price'] = $intgral_goods_info['score'];
							}else{
								$pin_goods = M('pin_goods')->field('pin_price')->where(array(
									'goods_id' => $goods_id
								))->find();
								$goods_query[0]['price'] = $pin_goods['pin_price'];
							}

                        }
                        //根据商品类型获取不同价格 begin

                    }
                    if (!empty($option_weight)) {
                        $goods_query[0]['weight'] = $option_weight;
                    }
                    //拼团才会有pin_id
                    $pin_id = 0;
                    if ($buy_type == 'pin' && isset($val['pin_id'])) {
                        $pin_id = $val['pin_id'];
                    }
                    //拼团 end
					$price = $goods_query[0]['price'];
					$origin_price = $price;
					if( $buy_type == 'pin' && $pin_id == 0 && $goods_query[0]['head_disc'] != 100)
					{
						//&& $goods_query[0]['head_disc'] != 100

						$price = round(( $price * intval($goods_query[0]['head_disc']) )/100,2);

						$goods_query[0]['price'] = $price;

						$header_disc = intval($goods_query[0]['head_disc']);
					}

                    $this->data[$key] = array(
                        'key' => $key,
                        'goods_id' => $goods_query[0]['goods_id'] ,
                        'name' => $goods_query[0]['name'],
                        'weight' => $option_weight,
                        'header_disc' => $header_disc,
                        'pin_id' => $pin_id,
                        'shipping' => $goods_query[0]['shipping'],
                        'goods_freight' => $goods_query[0]['goods_freight'],
                        'transport_id' => $goods_query[0]['transport_id'],
                        'image' => $thumb_image,
                        'quantity' => $quantity,
                        'price' => $origin_price,
                        'total' => ($goods_query[0]['price']) * $quantity,
                        'model' => $goods_query[0]['model'],
                        'option' => $option_data,
                    );
                } else {
                    $this->remove($key);
                }
            }
        }
        return $this->data;
    }
    public function count_goodscar($token) {
        $quantity = 0;
		$cart = M('car')->where( "token = '{$token}' and carkey like 'car.%' " )->select();
		foreach ($cart as $key => $val) {
			$format_data = unserialize($val['format_data']);
			$quantity += $format_data['quantity'];
		}
		    return $quantity;
	}
	public function count_activitycar($token) {
        $quantity = 0;
		$cart = M('car')->where( "token = '{$token}' and carkey like 'cart_activity.%' " )->select();
		foreach ($cart as $key => $val) {
			$format_data = unserialize($val['format_data']);
			$quantity += $format_data['quantity'];
		}
		    return $quantity;
	}
	//计算商品总数
	public function count_goods() {
		$goods_total = 0;
		$quantity = 0;
		$cart = session('cart');
		$hashids = new \Lib\Hashids(C('PWD_KEY') , C('URL_ID'));
		foreach ($cart as $key => $val) {
			//$pin_type = $val['pin_type'];
			$quantity+= $val['quantity'];
		}
		return $quantity;
	}
	//得到商品数量
	public function get_goods_quantity($goods_id) {
		return M('goods')->where(array(
			'goods_id' => $goods_id
		))->getField('quantity');
	}
	//取得商品重量
	public function getWeight() {
		$weight = 0;
		$w = new LibWeight();
		foreach ($this->get_all_goods() as $product) {
			if ($product['shipping']) {
				$weight+= $w->convert($product['weight'], $product['weight_class_id'], C('WEIGHT_ID'));
			}
		}
		return $weight;
	}
	//是否需要派送,下载类商品不需要配送
	public function has_shipping() {
		$shipping = false;
		foreach ($this->get_all_goods() as $product) {
			if ($product['shipping'] == 2) {
				$shipping = true;
				break;
			}
		}
		return $shipping;
	}
	//购物车是否为空
	public function has_goodswecar($buy_type = 'dan', $token) {
		if ($buy_type == 'dan') {
			$s = M('car')->where("token = '{$token}' and carkey like 'cart.%' ")->select();
		} else if ($buy_type == 'pin') {
			$s = M('car')->where("token = '{$token}' and carkey like 'cart_activity.%' ")->select();
		}
		return count($s);
	}
	//购物车是否为空
	public function has_goods($buy_type = 'dan') {
		if ($buy_type == 'dan') {
			$s = session('cart');
		} else if ($buy_type == 'pin') {
			$s = session('cart_activity');
		}
		return count($s);
	}
}

