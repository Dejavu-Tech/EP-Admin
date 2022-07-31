<?php
namespace Home\Model;
use Think\Model;
/**
 * 商品模块
 * @author Albert.Z
 *
 */
class GoodsModel extends Model{


	public function check_goods_fav($goods_id, $member_id)
	{
		$user_favgoods = M('user_favgoods')->where( array('member_id' => $member_id, 'goods_id' => $goods_id) )->find();

		if(!empty($user_favgoods))
		{
			return true;
		} else {
			return false;
		}
	}

	public function check_goods_new_manbug($member_id )
	{
		//order_status_id in (1,2,4,6,7,8,9,10,11,12,13)
		$sql ="select count(o.id) as count from ".C('DB_PREFIX')."order as o ";

		$order_count = M('order')->where( array('member_id' => $member_id, 'order_status_id' =>array('in', '1,2,4,6,7,8,9,10,11,12,13')) )->count();

		return $order_count;
	}

	public function check_store_fav($store_id, $member_id)
	{
		$user_favstore = M('user_favstore')->where( array('member_id' => $member_id, 'store_id' => $store_id) )->find();

		if(!empty($user_favstore))
		{
			return true;
		} else {
			return false;
		}
	}


	/**
	   获取商品列表
	**/
	public function get_goods_list($fields='*', $where='1=1',$order='index_sort desc ,seller_count desc,goods_id asc',$offset=0,$perpage=10)
	{


	    $list = M('goods')->where($where)->order($order)->limit($offset,$perpage)->select();
	   // var_dump(M('goods')->getLastSql(), $list);
		//die();
	    return $list;
	}
	/**
		获取客户等级信息,某个商品的折扣
	**/
	public function get_member_level_info($member_id, $goods_id = 0)
	{
		$result = array();

		$member_info =  M('member')->field('level_id')->where( array('member_id' => $member_id) )->find();
		$result['level_id'] = $member_info['level_id'];

		if( empty($member_info['level_id']) || $member_info['level_id'] ==0)
		{
			$default_discount_info =  M('config')->where( array('name' => 'member_defualt_discount') )->find();
			$default_levelname_info =  M('config')->where( array('name' => 'member_default_levelname') )->find();

			$result['member_discount'] = $default_discount_info['value'];
			$result['level_name'] = $default_levelname_info['value'];
			$result['level'] = 0;
		}else{
			$level_info = M('member_level')->where( array('id'  => $member_info['level_id']) )->find();

			$result['member_discount'] = $level_info['discount'];
			$result['level_name'] = $level_info['levelname'];
			$result['level'] = $level_info['level'];
		}

		if(!empty($goods_id) && $goods_id > 0)
		{
			$desc_info = M('goods_description')->field('is_untake_level,level_discount')->where( array('goods_id' => $goods_id) )->find();

			if(!empty($desc_info))
			{
				if($desc_info['is_untake_level'] == 1)
				{
					$result['member_discount'] = 100;
				}else{
					$level_discount_arr = unserialize($desc_info['level_discount']);

					if( array_key_exists($member_info['level_id'],$level_discount_arr) && $level_discount_arr[$member_info['level_id']] > 0)
					{
						$result['member_discount'] = $level_discount_arr[$member_info['level_id']];
					}
				}
			}
		}

		$level_info = M('config')->where( array('name' => 'member_level_is_open') )->find();

		if($level_info['value'] == 0)
		{
			$result['member_discount'] = 100;
		}

		return $result;
	}


/**
     * 获取商品的不同价格，分普通商品，拼团商品
     * @param unknown $goods_id
     */
    public function get_goods_price($goods_id)
    {
        $price_arr = array();
        //if (!$price_arr = S('goods_price_cache'.$goods_id)) {
            //$this->customer_id
            $goods_info = M('goods')->field('type,danprice')->where( array('goods_id' =>$goods_id) )->find();


            if($goods_info['type'] =='pintuan' || $goods_info['type'] =='newman' || $goods_info['type'] =='lottery')
            {
                $pin_goods_info = M('pin_goods')->field('pin_price,pin_count')->where( array('goods_id' =>$goods_id) )->find();

                if(!empty($pin_goods_info))
                {
                    $price_arr = array('price' =>$pin_goods_info['pin_price'],'danprice' =>$goods_info['danprice'],  'pin_price' =>$pin_goods_info['pin_price'],'pin_count' => $pin_goods_info['pin_count']);

				   //pin_price
					$option_price_arr = M('goods_option_mult_value')->field('dan_price')->where( array('goods_id' => $goods_id) )->order('dan_price asc')->find();
					if( !empty($option_price_arr) )
					{
						//if($option_price_arr['dan_price'] < $price_arr['danprice'])
						//{
							$price_arr['danprice'] = $option_price_arr['dan_price'];
						//}
					}

					$option_pinprice_arr = M('goods_option_mult_value')->field('pin_price')->where( array('goods_id' => $goods_id) )->order('pin_price asc')->find();
					if( !empty($option_pinprice_arr) )
					{
						//if($option_pinprice_arr['pin_price'] < $pin_goods_info['pin_price'])
						//{
							$price_arr['price'] = $option_pinprice_arr['pin_price'];
							$price_arr['pin_price'] = $option_pinprice_arr['pin_price'];
						//}
						//
					}


				   //S('goods_price_cache'.$goods_id, $price_arr);
                }


            }
			else if($goods_info['type'] =='integral')
			{
				$pin_goods_info = M('intgral_goods')->field('score')->where( array('goods_id' =>$goods_id) )->find();

                if(!empty($pin_goods_info))
                {
                    $price_arr = array('price' =>$pin_goods_info['score'],'danprice' =>$pin_goods_info['score'],  'pin_price' =>$pin_goods_info['score']);

				   //pin_price
					$option_price_arr = M('goods_option_mult_value')->field('dan_price')->where( array('goods_id' => $goods_id) )->order('dan_price asc')->find();
					if( !empty($option_price_arr) )
					{
						$price_arr['danprice'] = $option_price_arr['dan_price'];
					}

					$option_pinprice_arr = M('goods_option_mult_value')->field('pin_price')->where( array('goods_id' => $goods_id) )->order('pin_price asc')->find();
					if( !empty($option_pinprice_arr) )
					{
						$price_arr['price'] = $option_pinprice_arr['pin_price'];
						$price_arr['pin_price'] = $option_pinprice_arr['pin_price'];
					}

                }
			}
			else if($goods_info['type'] =='bargain')
			{
				$pin_goods_info = M('bargain_goods')->field('bargain_price,bargain_count')->where( array('goods_id' =>$goods_id) )->find();

                if(!empty($pin_goods_info))
                {
                    $price_arr = array('price' =>$pin_goods_info['bargain_price'],'danprice' =>$goods_info['danprice'],  'pin_price' =>$pin_goods_info['bargain_price'],'pin_count' => $pin_goods_info['bargain_count']);

				   //pin_price
					$option_price_arr = M('goods_option_mult_value')->field('dan_price')->where( array('goods_id' => $goods_id) )->order('dan_price asc')->find();
					if( !empty($option_price_arr) )
					{
						$price_arr['danprice'] = $option_price_arr['dan_price'];
					}

					$option_pinprice_arr = M('goods_option_mult_value')->field('pin_price')->where( array('goods_id' => $goods_id) )->order('pin_price asc')->find();
					if( !empty($option_pinprice_arr) )
					{
						$price_arr['price'] = $option_pinprice_arr['pin_price'];
						$price_arr['pin_price'] = $option_pinprice_arr['pin_price'];
					}

                }
			}
			else{
				//获取最低价格
				//dan_price
				$option_price_arr = M('goods_option_mult_value')->field('dan_price')->where( array('goods_id' => $goods_id) )->order('dan_price asc')->find();



				if( !empty($option_price_arr) && $option_price_arr['dan_price'] >= 0.01)
				{
					$price_arr = array('price' => $option_price_arr['dan_price'],'danprice' => $option_price_arr['dan_price']);

					//if($price_arr['price'] > $goods_info['dan_price'])
					//{
					//	$price_arr = array('price' => $goods_info['dan_price'],'danprice' => $goods_info['dan_price']);

					//}
				}else{
					//danprice
					$goods_info = M('goods')->field('danprice')->where( array('goods_id' => $goods_id) )->find();
					$price_arr = array('price' => $goods_info['danprice'],'danprice' => $goods_info['danprice']);
				}

			}
            //未来还有更多类型
       // }

        return $price_arr;
    }

	/**
	砍价
	**/
	public function _zan_bargain_order($zan_order_id)
	{
		$zan_order_info = M('bargain_order')->where( array('id' => $zan_order_id) )->find();
		//goods_id
		$goods_info = M('goods')->field('name,store_id,model,image')->where( array('goods_id' => $zan_order_info['goods_id']) )->find();

		$member_info = M('member')->where( array('member_id' => $zan_order_info['member_id']) )->find();

		$address_info = M('address')->where( array('address_id' => $zan_order_info['address_id']) )->find();


		$order = array();

		$order['member_id']=$zan_order_info['member_id'];
		$order['order_num_alias']= build_order_no($zan_order_info['member_id']);
		$order['name']=$goods_info['name'];
		$order['email']=$member_info['email'];
		$order['store_id']= $goods_info['store_id'];

		$order['telephone']=$address_info['telephone'];
		$order['shipping_name']=$address_info['name'];
		$order['shipping_address']=$address_info['address'];
		$order['shipping_city_id']=$address_info['city_id'];

		$order['shipping_country_id']=$address_info['country_id'];
		$order['shipping_province_id']=$address_info['province_id'];
		$order['shipping_tel']=$address_info['telephone'];
		$order['comment']='';
		$order['order_status_id']=1;
		$order['ip']=get_client_ip();
		$order['voucher_id']=0;
		$order['voucher_credit']=0;
		$order['shipping_fare'] = 0;
		$order['is_zhuli'] = 2;

		$order['is_pin'] = 0;

		$order['ip_region'] = '';
		$order['date_added'] =time();
		$order['total'] =0;
		$order['user_agent']='';

		$order['shipping_method']=0;
		$order['delivery']=$data['delivery'];

		$order['payment_code']='express';
		$order['type']='normal';

		$order['address_id']=$zan_order_info['address_id'];

		$order_id=M('Order')->add($order);

		//goods_images
		$this->execute("INSERT INTO ".C('DB_PREFIX')."order_goods SET order_id = '" .$order_id
	                ."',goods_id='".$zan_order_info['goods_id']."'"
	                .",store_id='".$goods_info['store_id']."'"
	                .",name='". addslashes($goods_info['name'])."'"
	                .",model='".$goods_info['model']."'"
					.",commiss_one_money='0'"
					.",commiss_two_money='0'"
					.",commiss_three_money='0'"
	                .",head_disc='100'"
	                .",is_pin='0'"
	                .",goods_images='".$goods_info['image']."'"
	                .",goods_type='normal'"
	                .",shipping_fare='0'"
	                .",quantity='1'"
	                .",price='0'"
	                .",rela_goodsoption_valueid='".$zan_order_info['sku_str']."'"
	                .",comment=''"
	                .",total='0'"
	            );

		$order_goods_id=$this->getLastInsID();

		$sku_str = ($zan_order_info['sku_str']);

		$options  = $sku_str;
		$option_data = array();

		$goods_id = $zan_order_info['goods_id'];


		//$good['sku_str']

		if(!empty($sku_str))
		{
			$options_arr = array();
			$option_value_id_arr = explode('_',$sku_str);

			foreach($option_value_id_arr as $id_val)
			{
				$goods_option_value = M('goods_option_value')->where( array('option_value_id' => $id_val,'goods_id' =>$goods_id) )->find();

				$options_arr[$goods_option_value['goods_option_id']] = $goods_option_value['goods_option_value_id'];

				$goods_option = M('goods_option')->where( array('goods_option_id' =>$goods_option_value['goods_option_id']) )->find();

				$option_value =  M('option_value')->where( array('option_value_id' =>$goods_option_value['option_value_id']) )->find();

				$this->execute("INSERT INTO ".C('DB_PREFIX')."order_option SET order_id = '" .$order_id
					."',order_goods_id='".$order_goods_id."'"
					.",goods_option_id='".(int)$goods_option_value['goods_option_id']."'"
					.",goods_option_value_id='".(int)$goods_option_value['goods_option_value_id']."'"
					.",name='".$goods_option['option_name']."'"
					.",value='".$option_value['value_name']."'"
				);

			}
		}

		$oh['order_id']=$order_id;
		$oh['order_status_id']=1;
		$oh['comment']='砍价成功';
		$oh['date_added']=time();
		$oh_id=M('OrderHistory')->add($oh);

		return $order_id;
	}

    /**
     * 获取商品规格
     * @param unknown $goods_id
     */
    public function get_goods_options($goods_id) {

        $result = array();
        $goods_option_name = array();
        $goods_option_data = array();
        $goods_option_query = M()->query("SELECT * FROM " . C('DB_PREFIX') . "goods_option po LEFT JOIN "
            . C('DB_PREFIX') . "option o ON po.option_id = o.option_id WHERE po.goods_id =".(int)$goods_id);



        foreach ($goods_option_query as $goods_option) {
            $goods_option_value_data = array();
            $goods_option_value_query = M()->query("SELECT pov.*,ov.value_name FROM " . C('DB_PREFIX')
                . "goods_option_value pov LEFT JOIN ". C('DB_PREFIX')
                ."option_value ov ON pov.option_value_id=ov.option_value_id"
                ." WHERE pov.goods_option_id = '"
                . (int)$goods_option['goods_option_id'] . "'");


            foreach ($goods_option_value_query as $goods_option_value) {


                $goods_option_value_data[] = array(
                    'goods_option_value_id' => $goods_option_value['goods_option_value_id'],
                    'option_value_id'         => $goods_option_value['option_value_id'],
                    'quantity'                 => $goods_option_value['quantity'],
                    'name'					  =>$goods_option_value['value_name'],
                    'image'					  =>isset($goods_option_value['image'])?$goods_option_value['image']:'',
                    'price'                   =>'¥'.$goods_option_value['price'],
                    'price_prefix'            => $goods_option_value['price_prefix'],

                );
            }
            $goods_option_name[] = $goods_option['name'];
            $goods_option_data[] = array(
                'goods_option_id'      => $goods_option['goods_option_id'],
                'option_id'            => $goods_option['option_id'],
                'name'                 => $goods_option['name'],
                'type'                 => $goods_option['type'],
                'option_value'         => $goods_option_value_data,
                'required'             => $goods_option['required']
            );
        }
        $result['list'] = $goods_option_data;
        $result['name'] = $goods_option_name;
        return $result;
    }

    //user_fav_store_toggle
	public function user_fav_store_toggle($store_id, $member_id)
	{
		$res = $this->check_store_fav($store_id, $member_id);

		if($res)
		{
			//删除
			$rs = M('user_favstore')->where( array('member_id' => $member_id, 'store_id' => $store_id) )->delete();
			return 1;
		} else {
			//添加
			$data = array();
			$data['member_id'] = $member_id;
			$data['store_id'] = $store_id;
			$data['add_time'] = time();
			M('user_favstore')->add($data);
			return 2;
		}
	}

	/**
		关注取消商品收藏
		删除返回1
	**/
	public function user_fav_goods_toggle($goods_id, $member_id)
	{
		$res = $this->check_goods_fav($goods_id, $member_id);

		if($res)
		{
			//删除
			$rs = M('user_favgoods')->where( array('member_id' => $member_id, 'goods_id' => $goods_id) )->delete();
			return 1;
		} else {
			//添加
			$data = array();
			$data['member_id'] = $member_id;
			$data['goods_id'] = $goods_id;
			$data['add_time'] = time();
			M('user_favgoods')->add($data);
			return 2;
		}
	}

	public function get_goods_pin_avatar($goods_id,$limit =10)
	{
		$sql = "select distinct(m.member_id), m.avatar from ".C('DB_PREFIX')."order_goods as og ,".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."member as m
		 where og.order_id=o.order_id and o.member_id=m.member_id and og.pin_id>0 and og.goods_id={$goods_id} order by og.pin_id desc limit {$limit}";

		$avatar_list = M()->query($sql);

		return $avatar_list;
	}

	/**
		返回用户还能够买多少份该商品
	**/
	public function check_goods_user_canbuy_count($user_id, $goods_id)
	{
		$goods_desc = M('goods_description')->where( array('goods_id' => $goods_id) )->find();//per_number

		if($goods_desc['per_number'] > 0)
		{
			$query = M()->query("SELECT sum(og.quantity) as count  FROM " . C('DB_PREFIX') . "order as o,
			" . C('DB_PREFIX') . "order_goods as og where  o.order_id = og.order_id and  og.goods_id =" . (int)$goods_id ."
			 and o.member_id = {$user_id}  and o.order_status_id in (1,2,3,4,6,7,9,11,12,13)");
			$buy_count = $query[0]['count'];

			if($buy_count >=$goods_desc['per_number'])
			{
				return -1;
			} else {
				return ($goods_desc['per_number'] - $buy_count);
			}
		} else{
			return 0;
		}
	}



	/**
	 扣除/增加商品多规格库存
	 1扣除， 2 增加
	 **/
	public function del_goods_mult_option_quantity($order_id,$option,$goods_id,$quantity,$type='1')
	{
	    //todo 库存减的不对。
	    //$goods['option'],$goods_id[0],$goods['quantity'],1
	    //`order_id` int(11) NOT NULL,
	    // `order_goods_id` int(11) NOT NULL, quantity

		$tp_goods = M('goods')->field('quantity')->where( array('goods_id' => $goods_id) )->find();

	    $order_goods = M('order_goods')->where( array('order_id' => $order_id,'goods_id' => $goods_id) )->find();

	    $option_list = M('order_option')->where( array('order_id' => $order_id, 'order_goods_id' =>$goods_id ) )->order('order_option_id asc')->select();

	    //$option_list = M('goods_option_value')->where( array('goods_id' => $goods_id) )->select();

	    if($type == 1)
	    {
			//	rela_goodsoption_value_id order_id  goods_id quantity addtime type last_quantity
			$quantity_order_data = array();
			$quantity_order_data['order_id'] = $order_id;
			$quantity_order_data['goods_id'] = $goods_id;
			$quantity_order_data['rela_goodsoption_value_id'] = $option;
			$quantity_order_data['quantity'] = $quantity;
			$quantity_order_data['type'] = 0;
			$quantity_order_data['last_quantity'] = $tp_goods['quantity'];
			$quantity_order_data['addtime'] = time();
			M('order_quantity_order')->add($quantity_order_data);

	        //扣除库存
	        $this->execute("UPDATE " . C('DB_PREFIX') . "goods SET quantity = (quantity - " . (int)$quantity . ") WHERE goods_id = '" . $goods_id . "' ");
	        //销量增加
	        $this->execute("UPDATE " . C('DB_PREFIX') . "goods SET seller_count = (seller_count + " . (int)$quantity . ") WHERE goods_id = '" . $goods_id . "' ");




	    } else if($type == 2){
			$quantity_order_data = array();
			$quantity_order_data['order_id'] = $order_id;
			$quantity_order_data['goods_id'] = $goods_id;
			$quantity_order_data['rela_goodsoption_value_id'] = $option;
			$quantity_order_data['quantity'] = $quantity;
			$quantity_order_data['type'] = 1;
			$quantity_order_data['last_quantity'] = $tp_goods['quantity'];
			$quantity_order_data['addtime'] = time();
			M('order_quantity_order')->add($quantity_order_data);

	        //增加库存
	        $this->execute("UPDATE " . C('DB_PREFIX') . "goods SET quantity = (quantity + " . (int)$quantity . ") WHERE goods_id = '" . $goods_id . "' ");
	        //销量减少
	        $this->execute("UPDATE " . C('DB_PREFIX') . "goods SET seller_count = (seller_count - " . (int)$quantity . ") WHERE goods_id = '" . $goods_id . "' ");
	    }


	    foreach($option_list as $op_li)
	    {
	        if($type == 1)
	        {
	            $goods_option_value = M('goods_option_value')->where( array('goods_option_id'=>$op_li['goods_option_id'],'goods_option_value_id' => $op_li['goods_option_value_id']) )->setDec('quantity',$quantity);

	        } else if($type ==2){
	            $goods_option_value = M('goods_option_value')->where( array('goods_option_id'=>$op_li['goods_option_id'],'goods_option_value_id' => $op_li['goods_option_value_id']) )->setInc('quantity',$quantity);
	        }
	    }

	    if(!empty($option))
	    {

	        if($type == 1)
	        {
	            M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $option,'goods_id' =>$goods_id) )->setDec('quantity',$quantity);

	        } else if($type ==2){
	            M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $option,'goods_id' =>$goods_id) )->setInc('quantity',$quantity);
	        }
	    }
	}

	/**
		扣除/增加商品多规格库存
		1扣除， 2 增加
	**/
	public function del_goods_mult_option_quantity2($order_id,$quantity,$type='1')
	{
		//todo 库存减的不对。

		$order_goods = M('order_goods')->where( array('order_id' => $order_id) )->find();
		$goods_id = $order_goods['goods_id'];
		$option_list = M('order_option')->where( array('order_id' => $order_id) )->order('order_option_id asc')->select();

		//$option_list = M('goods_option_value')->where( array('goods_id' => $goods_id) )->select();

		if($type == 1)
		{
			//扣除库存
			$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET quantity = (quantity - " . (int)$quantity . ") WHERE goods_id = '" . $goods_id . "' ");
			//销量增加
			$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET seller_count = (seller_count + " . (int)$quantity . ") WHERE goods_id = '" . $goods_id . "' ");

		} else if($type == 2){
			//增加库存
			$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET quantity = (quantity + " . (int)$quantity . ") WHERE goods_id = '" . $goods_id . "' ");
			//销量减少
			$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET seller_count = (seller_count - " . (int)$quantity . ") WHERE goods_id = '" . $goods_id . "' ");
		}

		$option = array();

		foreach($option_list as $op_li)
		{
			$option[$op_li['goods_option_id']] = $op_li['goods_option_value_id'];
		}

		$mul_opt_arr = array();

		foreach($option as $key => $option_value)
		{
			$goods_option_value = M('goods_option_value')->where( array('goods_option_id'=>$key,'goods_option_value_id' => $option_value) )->find();
			$mul_opt_arr[] = $goods_option_value['option_value_id'];
		}

		$rela_goodsoption_valueid = implode('_', $mul_opt_arr);

		$goods_option_mult_value = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $rela_goodsoption_valueid,'goods_id'=>$goods_id) )->find();

		if( !empty($goods_option_mult_value) )
		{
			if($type == 1)
			{
				$this->execute("UPDATE " . C('DB_PREFIX') . "goods_option_mult_value
					SET quantity = (quantity - " . (int)$quantity . ")
					WHERE rela_goodsoption_valueid = '" . $rela_goodsoption_valueid . "' and goods_id=".$goods_id);

				foreach($option as $key => $option_value)
				{
					$goods_option_value = M('goods_option_value')->where( array('goods_option_id'=>$key,'goods_option_value_id' => $option_value) )->setDec('quantity',$quantity);
					//$User->where('id=5')->setInc('score',3); +  quantity
					//$User->where('id=5')->setDec('score',5); -
				}
			} else if($type ==2){
				$this->execute("UPDATE " . C('DB_PREFIX') . "goods_option_mult_value
					SET quantity = (quantity + " . (int)$quantity . ")
					WHERE rela_goodsoption_valueid = '" . $rela_goodsoption_valueid . "' and goods_id=".$goods_id);

				foreach($option as $key => $option_value)
				{
					$goods_option_value = M('goods_option_value')->where( array('goods_option_id'=>$key,'goods_option_value_id' => $option_value) )->setInc('quantity',$quantity);
				}
			}
		}

	}


	private function _curl_get_avatar($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	/**
		获取首页商品
	**/
	public function _get_index_user_wxqrcode($goods_id,$member_id)
	{
		//qrcode
		$jssdk = new \Lib\Weixin\Jssdk( C('weprogram_appid'), C('weprogram_appscret') );

		$weqrcode = $jssdk->getAllWeQrcode('pages/index/index','0_'.$member_id );
		//line_color
		//var_dump($weqrcode);die();

		//保存图片

		$image_dir = ROOT_PATH.'Uploads/image/goods';
		$image_dir .= '/'.date('Y-m-d').'/';

		$file_path = C('SITE_URL').'Uploads/image/goods/'.date('Y-m-d').'/';
		$kufile_path = $dir.'/'.date('Y-m-d').'/';

		RecursiveMkdir($image_dir);
		$file_name = md5('qrcode_'.$goods_id.'_'.$member_id.time()).'.png';
		//qrcode
		file_put_contents($image_dir.$file_name, $weqrcode);
		return $image_dir.$file_name;
	}

	public function _get_goods_user_wxqrcode($goods_id,$member_id)
	{
		//qrcode
		$jssdk = new \Lib\Weixin\Jssdk( C('weprogram_appid'), C('weprogram_appscret') );

		$weqrcode = $jssdk->getAllWeQrcode('pages/goods/index',$goods_id.'_'.$member_id );
		//line_color
		//var_dump($weqrcode);die();

		//保存图片

		$image_dir = ROOT_PATH.'Uploads/image/goods';
		$image_dir .= '/'.date('Y-m-d').'/';

		$file_path = C('SITE_URL').'Uploads/image/goods/'.date('Y-m-d').'/';
		$kufile_path = $dir.'/'.date('Y-m-d').'/';

		RecursiveMkdir($image_dir);
		$file_name = md5('qrcode_'.$goods_id.'_'.$member_id.time()).'.png';
		//qrcode
		file_put_contents($image_dir.$file_name, $weqrcode);
		return $image_dir.$file_name;
	}

	public function _get_compare_qrcode_bgimg($bg_img, $qrcode_img,$s_x = '500',$s_y = '660')
	{
		$image_dir = ROOT_PATH."Uploads/image/".date('Y-m-d')."/";

		RecursiveMkdir($image_dir);

		$thumb_image_name = "thumb_img".md5($qrcode_img).'.png';

		$image = new \Think\Image();
		$image->open($qrcode_img);
		$image->thumb(230, 230)->save($image_dir.$thumb_image_name);

		$thumb_qrcode_img  = $image_dir.$thumb_image_name;

		$bg_img = ROOT_PATH."Uploads/image/".$bg_img;

		$dst = imagecreatefromjpeg ($bg_img);
		$src = imagecreatefromstring(file_get_contents($thumb_qrcode_img));

		if (imageistruecolor($src))
			imagetruecolortopalette($src, false, 65535);

		list($src_w, $src_h) = getimagesize($thumb_qrcode_img);
		list($dst_w, $dst_h, $dst_type) = getimagesize($bg_img);
		imagecopymerge($dst, $src, $s_x, $s_y, 0, 0, $src_w, $src_h, 100);

		$last_img = $image_dir;

		$file_path = C('SITE_URL').'Uploads/image/'.date('Y-m-d').'/';
		$return_file_path = ''.date('Y-m-d').'/';


		$last_img_name = "last_qrcode".md5( time().$bg_img.$qrcode_img).'';

		switch ($dst_type) {
			case 1://GIF
				$last_img_name .= '.gif';
				header('Content-Type: image/gif');
				imagegif($dst, $last_img.$last_img_name);
				break;
			case 2://JPG
				$last_img_name .= '.jpg';
				//header('Content-Type: image/jpeg');
				imagejpeg($dst, $last_img.$last_img_name);
				break;
			case 3://PNG
				$last_img_name .= '.png';
				header('Content-Type: image/png');
				imagepng($dst, $last_img.$last_img_name);
				break;
			default:
				break;
		}
		imagedestroy($dst);
		imagedestroy($src);
		imagedestroy($goods_src);
		//imagedestroy($avatar_src);

		//return_file_path
		$result = array('full_path' => $file_path.$last_img_name,'need_path' => $return_file_path.$last_img_name);

		return $result;
	}

	//_get_compare_zan_img($rocede_path,$goods_img,$goods_title,$goods_price)
	public function _get_compare_zan_img($goods_img,$goods_title,$goods_price)
	{
		//$qrcode_img = ROOT_PATH."Uploads/image/2018-01-19/7b4f87260dff5247f2313dfe6cc0fe83.png";

		$image_dir = ROOT_PATH."Uploads/image/".date('Y-m-d')."/";


		RecursiveMkdir($image_dir);




		// 70 70 /alidata/www/pinduoduo/Common/img/bg.jpg
		// Common/img/bg.png 590 460 11258
		$bg_img = ROOT_PATH."/resource/images/bg.jpg";
		//$goods_img = ROOT_PATH."Uploads/image/goods/2017-11-25/1511619168701f098ef1190364f8beb079a306673c.jpg";

		$thumb_goods_name = "thumb_img".md5($goods_img).'.png';

		$image2 = new \Think\Image();
		$image2->open($goods_img);
		$image2->thumb(750, 585)->save($image_dir.$thumb_goods_name);

		$thumb_goods_img = $image_dir.$thumb_goods_name;


		$image_dir = ROOT_PATH.'Uploads/image/goods';
		$image_dir .= '/'.date('Y-m-d').'/';

		$file_path = C('SITE_URL').'Uploads/image/goods/'.date('Y-m-d').'/';
		$return_file_path = 'goods/'.date('Y-m-d').'/';

		RecursiveMkdir($image_dir);

		//$image_dir.$thumb_avatar_name

		//文字：74 640
		//长按二维码领取： 517 640
		//商品文字： 24  710
		//快和我一起领取吧： 24 817
		//市场价，单价 24 895

		//var_dump($thumb_goods_img);die();

		//$dst = imagecreatefromstring(file_get_contents($bg_img));
		$dst = imagecreatefromjpeg ($bg_img);

		$goods_src = imagecreatefromstring(file_get_contents($thumb_goods_img));


		if (imageistruecolor($goods_src))
			imagetruecolortopalette($goods_src, false, 65535);

		if (imageistruecolor($avatar_src))
			imagetruecolortopalette($avatar_src, false, 65535);


		list($goods_src_w, $goods_src_h) = getimagesize($thumb_goods_img);
		list($dst_w, $dst_h, $dst_type) = getimagesize($bg_img);


		imagecopymerge($dst, $goods_src, ($dst_w - $goods_src_w) / 2, 0, 0, 0, $goods_src_w, $goods_src_h, 100);

		//imagecopymerge($dst, $avatar_src, 24, 615, 0, 0, $avatar_w, $avatar_h, 100);

		$ttf_path = ROOT_PATH."resource/js/simhei.ttf";

		//打上文字

		$black = imagecolorallocate($dst, 20,20,20);//黑色
		$red = imagecolorallocate($dst, 237, 48, 43); //红色 201 55 49
		$huise = imagecolorallocate($dst, 159, 159, 159); //灰色 159 159 159
		$fense = imagecolorallocate($dst, 248, 136, 161); //粉色 248 136 161
		//ffb7d7 248 136 161


		//$goods_title = "我免费领取了【大白兔奶糖果零食铁盒装114g】的所得税的色舞认太热太热太热";
		$goods_title = $goods_title;
		$need_goods_title = mb_substr($goods_title,0,12,'utf-8')."\r\n";
		$need_goods_title .= mb_substr($goods_title,12,12,'utf-8');

		imagefttext($dst, 25, 0, 120, 660, $black, $ttf_path, $username);
		//imagefttext($dst, 15, 0, 518, 920, $huise, $ttf_path, '长按二维码领取');
		imagefttext($dst, 30, 0, 24, 750, $black, $ttf_path, $need_goods_title);
		imagefttext($dst, 15, 0, 24, 860, $fense, $ttf_path, "限时爆款价");
		imagefttext($dst, 36, 0, 24, 920, $black, $ttf_path, "¥".$goods_price['price']);
		imagefttext($dst, 18, 0, 186, 920, $huise, $ttf_path, "市场价¥".$goods_price['market_price']);

		$last_img = $image_dir;

		$last_img_name = "last_avatar".md5( time().$need_goods_title.$username).'';

		switch ($dst_type) {
			case 1://GIF
				$last_img_name .= '.gif';
				header('Content-Type: image/gif');
				imagegif($dst, $last_img.$last_img_name);
				break;
			case 2://JPG
				$last_img_name .= '.jpg';
				//header('Content-Type: image/jpeg');
				imagejpeg($dst, $last_img.$last_img_name);
				break;
			case 3://PNG
				$last_img_name .= '.png';
				header('Content-Type: image/png');
				imagepng($dst, $last_img.$last_img_name);
				break;
			default:
				break;
		}
		imagedestroy($dst);

		imagedestroy($goods_src);
		//imagedestroy($avatar_src);

		//return_file_path
		$result = array('full_path' => $file_path.$last_img_name,'need_path' => $return_file_path.$last_img_name);

		return $result;
	}

	/**
	 * 前端已售单位自定义
	 * @return [string] [自定义值，默认件]
	 */
	public function get_sale_unit()
	{
		$isnull_goods_sale_unit =  D('Home/Front')->get_config_by_name('isnull_goods_sale_unit');
		if($isnull_goods_sale_unit==1) {
			return '';
		} else {
			$goods_sale_unit =  D('Home/Front')->get_config_by_name('goods_sale_unit');
			return $goods_sale_unit ? $goods_sale_unit : '件';
		}
	}

}
