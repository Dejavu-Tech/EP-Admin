<?php
namespace Home\Model;
use Think\Model;
/**
 * 优惠券模块
 * @author Albert.Z
 *
 */
class VoucherModel {



	public function checkUserCanGetOneVoucher( $voucher_id,$user_id,$check_count =false , $is_admin = false)
	{
		if($user_id <= 0)
		{
			return 4;
		}

		$voucher_info =  M('eaterplanet_ecommerce_coupon')->where( array('id' => $voucher_id) )->find();

		if($voucher_info['total_count'] !=-1 && $voucher_info['total_count'] <= $voucher_info['send_count']){
			return 1;//被抢光了
		}

	    if(!$is_admin)
		{
			if($check_count) {
				if($voucher_info['total_count'] !=-1 && $voucher_info['total_count'] <= $voucher_info['send_count']){
					return 1;//被抢光了
				}else {

					$get_count = M('eaterplanet_ecommerce_coupon_list')->where( array('voucher_id' => $voucher_id , 'user_id' => $user_id ) )->count();

				  if($voucher_info['person_limit_count'] > 0 && $voucher_info['person_limit_count'] <= $get_count) {
					  return 2;//已领过
				  }
				}
			}

			//判断是否是新人专享的优惠券
			if( $voucher_info['is_new_man'] == 1 )
			{
				//检测是否购买过
				$od_status = "1,2,4,6,7,8,9,10,11,12,14";

				$buy_count = M('eaterplanet_ecommerce_order')->where(" order_status_id in ({$od_status}) and member_id=".$user_id )->count();

				if( !empty($buy_count) && $buy_count >0 )
				{
					return 4;
				}

			}
		}

		return 0;

	}
	public function send_user_voucher_byId($voucher_id,$user_id,$check_count =false, $is_admin = false)
	{

		if($user_id <= 0)
		{
			return 4;
		}

		$voucher_info =  M('eaterplanet_ecommerce_coupon')->where( array('id' => $voucher_id) )->find();

		if($voucher_info['total_count'] !=-1 && $voucher_info['total_count'] <= $voucher_info['send_count']){
			return 1;//被抢光了
		}

	    if(!$is_admin)
		{
			if($check_count) {
				if($voucher_info['total_count'] !=-1 && $voucher_info['total_count'] <= $voucher_info['send_count']){
					return 1;//被抢光了
				}else {

					$get_count = M('eaterplanet_ecommerce_coupon_list')->where( array('voucher_id' => $voucher_id , 'user_id' => $user_id ) )->count();

				  if($voucher_info['person_limit_count'] > 0 && $voucher_info['person_limit_count'] <= $get_count) {
					  return 2;//已领过
				  }
				}
			}

			//判断是否是新人专享的优惠券
			if( $voucher_info['is_new_man'] == 1 )
			{
				//检测是否购买过
				$od_status = "1,2,4,6,7,8,9,10,11,12,14";

				$buy_count = M('eaterplanet_ecommerce_order')->where(" order_status_id in ({$od_status}) and member_id=".$user_id )->count();

				if( !empty($buy_count) && $buy_count >0 )
				{
					return 4;
				}

			}
		}




		//开始生产优惠券
		$begin_time = $voucher_info['begin_time'];
		$end_time = $voucher_info['end_time'];

		if(  $voucher_info['timelimit'] == 0)
		{
			$begin_time = time();
			$end_time = time() + 3600 * $voucher_info['get_over_hour'];
		}


		$voucher = array(
						'voucher_id' => $voucher_id,
						'voucher_title' => $voucher_info['voucher_title'],
						'user_id' => $user_id,
						'store_id' => 0,
						'type'     => $voucher_info['type'],
						'credit' => $voucher_info['credit'],
						'limit_money' => $voucher_info['limit_money'],
						'is_limit_goods_buy' => $voucher_info['is_limit_goods_buy'],
						'limit_goods_list' => $voucher_info['limit_goods_list'],
						'goodscates' => $voucher_info['goodscates'],
						'consume' => 'N',
						'begin_time' => $begin_time,
						'end_time' => $end_time,
						'add_time'=>time(),
		);
		//user_id
		$id = M('eaterplanet_ecommerce_coupon_list')->add( $voucher );


        if($id){
			M()->execute("update ".C('DB_PREFIX')."eaterplanet_ecommerce_coupon set send_count=send_count+1 where id =".$voucher_id );

        }
        return 3;//领取成功

	}

	/**
		优惠券活动页面领券
	**/
	public function send_user_voucher_byId_frombonus($voucher_id,$user_id,$check_count =false,$is_double = false)
	{
	    $voucher_info = M('voucher')->where( array('id' => $voucher_id) )->find();

	    if($check_count) {
	        if($voucher_info['total_count'] <= $voucher_info['send_count']){
	            return -1;//被抢光了
	        }else {
	          $get_count =  M('voucher_list')->where( "voucher_id={$voucher_id} and user_id={$user_id} " )->count();

	          if($voucher_info['person_limit_count'] > 0 && $voucher_info['person_limit_count'] <= $get_count) {
	              return -2;//已领过
	          }
	        }
	    }

        $voucher_list_one = M('voucher_list')->where( array('voucher_id' =>$voucher_id,'user_id' =>0 ) )->order('id desc')->find();

        if($voucher_list_one){
			$credit = $voucher_list_one['credit'];
			//get_over_hour
			if($is_double)
			{
				$credit = 2 * $voucher_list_one['credit'];
			}
			$end_time = $voucher_list_one['end_time'];

			if( $voucher_info['get_over_hour']  > 0)
			{
				$end_time = time() + intval(3600 * $voucher_info['get_over_hour']);
			}

            M('voucher')->where( array('id' => $voucher_id) )->setInc('send_count');
            M('voucher_list')->where( array('id' => $voucher_list_one['id'])  )->save( array('user_id' => $user_id,'end_time' => $end_time ,'credit' => $credit) );
        }
        return $voucher_list_one['id'];//领取成功

	}
	/**
	 * 获取用户可用给当前店铺商品支付的优惠券
	 * @param unknown $user_id
	 * @param unknown $store_id
	 * @param unknown $total_money
	 */
	public function get_user_canpay_voucher($user_id,$store_id,$total_money, $uniacid = '',$goods_ids = array())
	{
		//ims_
		$voucher_list = M()->query("select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_coupon_list
					where  (is_limit_goods_buy = 0 and (limit_money = 0 or (limit_money<=".$total_money.") ) ) and  (store_id=".$store_id." or store_id=0) and user_id=".$user_id." and consume='N'  and  begin_time<".time().' and end_time >'.time() );

		//-----------
		//var_dump( $voucher_list );
		//die();


		if( empty($voucher_list) )
		{
			$voucher_list = array();
		}

		//判断是否有限制商品的券

		$voucher_list_goods = M('eaterplanet_ecommerce_coupon_list')->where( "is_limit_goods_buy = 1 and  (store_id={$store_id} or store_id=0) and user_id={$user_id} and consume='N' and  begin_time<".time().' and end_time >'.time() )->select();

		if( !empty($voucher_list_goods) )
		{
			foreach($voucher_list_goods as $gd_quan)
			{
				if( empty($gd_quan['limit_goods_list']) )
				{
					//(limit_money = 0 or (limit_money<=:total_money) )
					if($gd_quan['limit_money'] ==0  || $gd_quan['limit_money'] <= $total_money)
					{
						$voucher_list[] = $gd_quan;
					}
				}else{
					$voucher_goods_ids = explode(',', $gd_quan['limit_goods_list']);
					$voucher_goods_ids_total_money = 0;

					$is_in = false;

					foreach($goods_ids as $key_goods_id => $money_goods_id)
					{
						if( in_array($key_goods_id, $voucher_goods_ids ) )
						{
							$voucher_goods_ids_total_money += $money_goods_id;
							$is_in = true;
						}
					}

					if( $is_in && $voucher_goods_ids_total_money >= $gd_quan['limit_money'] )
					{
						$voucher_list[] = $gd_quan;
					}
				}
			}
		}

		//判断是否有限制商品分类的券

		$voucher_list_cate = M('eaterplanet_ecommerce_coupon_list')->where( "is_limit_goods_buy = 2 and  (store_id={$store_id} or store_id=0) and user_id={$user_id} and consume='N' and  begin_time<".time().' and end_time >'.time() )->select();


		if( !empty($voucher_list_cate) )
		{
			foreach($voucher_list_cate as $gd_quan)
			{
				if( empty($gd_quan['goodscates']) )
				{
					if($gd_quan['limit_money'] ==0  || $gd_quan['limit_money'] <= $total_money)
					{
						$voucher_list[] = $gd_quan;
					}
				}else{
					$voucher_goods_cate = $gd_quan['goodscates'];

					$voucher_goods_ids_total_money = 0;

					$is_in = false ;




					foreach($goods_ids as $key_goods_id => $money_goods_id)
					{
						$cate_gd_arr = M('eaterplanet_ecommerce_goods_to_category')->field('cate_id')->where( array('goods_id' => $key_goods_id) )->select();

						if( !empty($cate_gd_arr) )
						{
							foreach($cate_gd_arr as $cate_val)
							{
								if( $cate_val['cate_id'] == $voucher_goods_cate )
								{
									$is_in = true;
									$voucher_goods_ids_total_money += $money_goods_id;
								}
							}
						}


					}


					if($is_in &&  $voucher_goods_ids_total_money >= $gd_quan['limit_money'] )
					{
						$voucher_list[] = $gd_quan;
					}
				}
			}
		}



		//---------------
		if( !empty($voucher_list) )
		{
			foreach($voucher_list as $key => $val)
			{
				$val['begin_time'] = date('Y-m-d H:i:s', $val['begin_time']);
				$val['end_time'] = date('Y-m-d H:i:s', $val['end_time']);

				//---begin

				$coupon_info = M('eaterplanet_ecommerce_coupon')->where( array('id' =>$val['voucher_id'] ) )->find();
				if( $coupon_info['catid'] > 0 )
				{
					$cate_info = M('eaterplanet_ecommerce_coupon_category')->where( array('id' => $coupon_info['catid']) )->find();

					$val['cate_name'] = $cate_info['name'];
				}else{
					$val['cate_name'] = '';
				}
				//--end

				$voucher_list[$key] = $val;
			}
		}

		//credit

		 $last_index_sort = array_column($voucher_list, 'credit');
         array_multisort($last_index_sort, SORT_DESC, $voucher_list);

		return $voucher_list;
	}

}
