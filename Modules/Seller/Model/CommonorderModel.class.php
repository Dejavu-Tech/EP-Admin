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
namespace Seller\Model;

class CommonorderModel{

	//TODO....设置售后期的情况下进行确认收货，跟系统自动结算两个方法

	/**
		获取一个订单中，商品的数量，
	**/
	public function get_order_goods_quantity( $order_id,$order_goods_id=0)
	{

		$where = "";

		if( !empty($order_goods_id) && $order_goods_id >0 )
		{
			$where .= " and order_goods_id={$order_goods_id} ";
		}
		$order_info  =  M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();
		//原来有的数量

		$total_quantity = M('eaterplanet_ecommerce_order_goods')->where( "order_id ={$order_id} {$where}" )->sum('quantity');

		$total_quantity = empty($total_quantity) ? 0 : $total_quantity;

		$refund_quantity = $this->refund_order_goods_quantity( $order_id,$order_goods_id,$uniacid);

		 $surplus_quantity = $total_quantity - $refund_quantity;
		if($order_info['delivery'] == 'hexiao'){
		    $used_quantity = D('Home/Salesroom')->get_hexiao_order_goods_used_quantity($order_id,$order_goods_id);
		    $surplus_quantity = $surplus_quantity - $used_quantity;
		}

		 return $surplus_quantity;
	}

	/**
		已经退掉的订单商品数量
	**/
	public function refund_order_goods_quantity( $order_id,$order_goods_id=0 )
	{
		$where = "";

		if( !empty($order_goods_id) && $order_goods_id >0 )
		{
			$where .= " and order_goods_id={$order_goods_id} ";
		}

		$refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where("order_id ={$order_id} {$where}")->sum('real_refund_quantity');

		$refund_quantity = empty($refund_quantity) ? 0 : $refund_quantity;

		return $refund_quantity;
	}

	/**
		该笔子订单已经退款了多少钱
	**/
	public function get_order_goods_refund_money( $order_id,$order_goods_id )
	{

		$where = "";

		if( !empty($order_goods_id) && $order_goods_id >0 )
		{
			$where .= " and order_goods_id={$order_goods_id} ";
		}

		$refund_money = M('eaterplanet_ecommerce_order_goods_refund')->where( "order_id ={$order_id} {$where}" )->sum('money');

		$refund_money = empty($refund_money) ? 0 : $refund_money;

		return $refund_money;

	}

	/**
		获取订单商品支付的金额公共方法
	**/
	public function get_order_goods_paymoney( $order_goods_id )
	{
		$order_goods_info  =  M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id) )->find();

		if( empty($order_goods_info) )
		{
			return 0;
		}else{
			$pay_free = $order_goods_info['total'] + $order_goods_info['shipping_fare']-$order_goods_info['voucher_credit']-$order_goods_info['fullreduction_money']-$order_goods_info['score_for_money'];

			$pay_free = round($pay_free, 2);

			$order_info = M('eaterplanet_ecommerce_order')->field('type')->where( array('order_id' => $order_goods_info['order_id']) )->find();
			if( $order_info['type'] == 'integral' )
			{
				$pay_free = round($order_goods_info['shipping_fare'], 2);
			}

			return $pay_free;
		}
	}

	/**
	*获取订单金额--公共方法
	**/
	public function get_order_paymoney( $order_id )
	{
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

		if( empty($order_info) )
		{
			return 0;
		}else{

			$pay_free = $order_info['total'] -$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money'];

			if( $order_info['type'] != 'integral' )
			{
			    if($order_info['is_free_shipping_fare'] == 0 ){
			        $pay_free += $order_info['shipping_fare'];
			    }
			}

			$pay_free = round($pay_free, 2);

			return $pay_free;
		}

	}

	/**
	 *
	 * 获取订单已经退款了多少钱
	 */
	public function order_refund_totalmoney( $order_id )
	{
	    $total_money = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' =>$order_id ) )->sum('money');

	    if( empty($total_money) )
	    {
	        $total_money = 0;
	    }

	    return $total_money;

	}


	/**
	 * 获取订单商品 购买支付时，抵扣用了多少积分
	 */
	public function get_order_goods_buyscore($order_id, $order_goods_id)
	{
	    $integral_flow_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' => $order_id, 'order_goods_id' =>$order_goods_id,'type' => 'orderbuy' ) )->find();

	    if( empty($integral_flow_info) )
	    {
	        return 0;
	    }else{
	        return $integral_flow_info['score'];
	    }

	}

	/**
	 * 退款积分
	 */
	public function refund_order_goods_intrgral( $order_id, $order_goods_id ,$score )
	{
	    $in_data = array();
	    $in_data['uniacid'] = 0;
	    $in_data['order_goods_id'] = $order_goods_id;
	    $in_data['order_id'] = $order_id;
	    $in_data['refund_score'] = $score;
	    $in_data['addtime'] = time();

	    $res = M('eaterplanet_ecommerce_order_goods_refund_intrgral')->add($in_data);

	}

	/**
		退款整笔订单
		@auth Albert.Z
		@param is_back_buyscore 是否退积分，0否，1 是
		mail 353399459@qq.com
		time:2020-03-07
	**/
	public function refund_one_order( $order_id ,$is_back_buyscore = 0)
	{
		$_GPC = I('request.');
		$order_goods_list = M('eaterplanet_ecommerce_order_goods')->field('order_goods_id,quantity,shipping_fare,fenbi_li')->where( array('order_id' => $order_id , 'is_refund_state' => 0) )->select();
		if( !empty($order_goods_list) )
		{
			foreach( $order_goods_list as $val)
			{
				$order_goods_id = $val['order_goods_id'];
				$refund_shipping_fare = 0;
				if( $is_back_buyscore == 0)
				{
				    $this->refund_order_goods_intrgral( $order_id, $order_goods_id ,0 );

				}else if($is_back_buyscore == 1){
				    $integral_flow_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' => $order_id, 'order_goods_id' =>$order_goods_id,'type' => 'orderbuy' ) )->find();
				    if( !empty($integral_flow_info) )
				    {
				        $has_refflow_score = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' => $order_id, 'order_goods_id' =>$order_goods_id,'type' => 'refundorder' ) )->sum('score');

				        if( !empty($has_refflow_score) && $has_refflow_score > 0 )
				        {
				            $integral_flow_info['score'] -= $has_refflow_score;
				            if($integral_flow_info['score'] < 0)
				            {
				                $integral_flow_info['score'] =0;
				            }
				        }

				        $this->refund_order_goods_intrgral( $order_id, $order_goods_id ,$integral_flow_info['score'] );
				    }
				}else if( $is_back_buyscore == 2)//退运费
				{
                    $has_refund_shipping_fare = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_goods_id' => $val['order_goods_id'] ) )->sum('refund_shipping_fare');
					$refund_shipping_fare = $val['shipping_fare']-$has_refund_shipping_fare;
				}


				$is_has_refund_deliveryfree = D('Home/Front')->get_config_by_name('is_has_refund_deliveryfree');
				$is_has_refund_deliveryfree = !isset($is_has_refund_deliveryfree) || $is_has_refund_deliveryfree == 1 ? 1:0;
				if($is_has_refund_deliveryfree == 0){//不退配送费
					$refund_shipping_fare = $val['shipping_fare'];
				}

				$pay_total_money = $this->get_order_goods_paymoney( $order_goods_id );

				$has_refund_money = $this->get_order_goods_refund_money( $order_id,$order_goods_id );

				$has_refund_quantity = $this->refund_order_goods_quantity( $order_id,$order_goods_id);


				$real_refund_quantity = $val['quantity']- $has_refund_quantity;
				$refund_quantity = $real_refund_quantity - $has_refund_quantity;
				//$refund_money = round($pay_total_money - $has_refund_money - $val['shipping_fare'],2);
				$refund_money_sum = isset($_GPC['refund_money']) && $_GPC['refund_money'] >0  ? floatval($_GPC['refund_money']) : 0;
				$refund_money = $refund_money_sum * $val['fenbi_li'];
				$is_back_sellcount = 1;

				$this->ins_order_goods_refund($order_id, $order_goods_id,$pay_total_money,$real_refund_quantity, $refund_quantity,$refund_money, $is_back_sellcount, $refund_shipping_fare);

				M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id ) )->save( array('is_refund_state' => 1 ) );
			}
		}

	}

	/**
		每日订单-商品购买记录每日销量
	**/
	public function inc_daygoods_buy( $goods_id, $quantity )
	{

	    //begin
	    $today_zero_time = strtotime( date('Y-m-d').' 00:00:00' );

	    //$clear_goodsdaysales  = S('clear_goodsdaysales');
		$clear_goodsdaysales = D('Home/Front')->get_config_by_name('clear_goodsdaysales');
	    if( !isset($today_zero_time) || $clear_goodsdaysales != $today_zero_time )
	    {
	        D('Seller/Commonorder')->clear_goods_daysales();
	        //S('clear_goodsdaysales', $today_zero_time );

			$config_data = array();
			$config_data['clear_goodsdaysales'] = $today_zero_time;
			D('Seller/Config')->update($config_data);
	    }
	    //end

		M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id ) )->setInc('day_salescount', $quantity);

	}
	/**
		每日订单-商品退款每日销量
	**/
	public function dec_daygoods_refund( $goods_id, $quantity )
	{
		M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id ) )->setInc('day_salescount', -$quantity);
	}

	/**
		清理商品 每日销量
	**/
	public function clear_goods_daysales()
	{
		M('eaterplanet_ecommerce_goods')->where( "1=1" )->save( array('day_salescount' => 0 ) );
	}

	/**
		插入子订单退款
	 *  $refund_shipping_fare 退款运费
	**/
	public function ins_order_goods_refund($order_id, $order_goods_id,$pay_total_money,$real_refund_quantity, $refund_quantity,$refund_money, $is_back_sellcount, $refund_shipping_fare)
	{
		//计算需要抵扣多少佣金 ims_ eaterplanet_ecommerce_order

		$commiss_info = M('eaterplanet_community_head_commiss_order')->where( " order_id={$order_id} and order_goods_id={$order_goods_id} and type='orderbuy' " )->find();

		// eaterplanet_ecommerce_order_goods
		$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( " order_goods_id={$order_goods_id} " )->find();

		//order_status_id
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

		$now_begin_time = strtotime( date('Y-m-d'.' 00:00:00') );
		$now_end_time = $now_begin_time + 86400;
		//今日订单今日退款成功减去今日销量
		if( $order_info['date_added'] >= $now_begin_time && $order_info['date_added'] < $now_end_time  )
		{
			$this->dec_daygoods_refund( $order_goods_info['goods_id'], $real_refund_quantity );
		}

		$refund_data = array();
		$refund_data['order_goods_id'] = $order_goods_id;
		$refund_data['order_id'] = $order_id;
		$refund_data['uniacid'] = 0;
		$refund_data['real_refund_quantity'] = $real_refund_quantity;
		$refund_data['quantity'] = $refund_quantity;
		$refund_data['money'] = $refund_money;
		$refund_data['refund_shipping_fare'] = $refund_shipping_fare;
		$refund_data['order_status_id'] = $order_info['order_status_id'];
		$refund_data['is_back_quantity'] = $is_back_sellcount;

		//---  以下需要计算了 refundorder
		$refund_data['back_score_for_money'] = 0;//退还积分兑换商品的积分 orderbuy
		$refund_data['back_send_score'] = 0; //退还赠送积分 goodsbuy
		$refund_data['back_head_orderbuycommiss'] = 0; //退还团长佣金
		$refund_data['back_head_supplycommiss'] = 0; //退还商户佣金
		$refund_data['back_head_commiss_1'] = 0; //退1级团长佣金
		$refund_data['back_head_commiss_2'] = 0; //退2级团长佣金
		$refund_data['back_head_commiss_3'] = 0; //退3级团长佣金
		$refund_data['back_member_commiss_1'] = 0; //退客户1级佣金
		$refund_data['back_member_commiss_2'] = 0; //退客户2级佣金
		$refund_data['back_member_commiss_3'] = 0; //退客户3级佣金
		$refund_data['addtime'] = time(); //添加时间


		if( !empty($commiss_info) && $commiss_info['state'] == 1 )
		{
			//已经结算了

		    //INSERT
		    $id = M('eaterplanet_ecommerce_order_goods_refund')->add( $refund_data );


		    M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id) )->setInc('has_refund_money', $refund_money );
		    M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id) )->setInc('has_refund_quantity', $real_refund_quantity );

		    //has_refund_money

		    return $id;
		}else{
			//未结算的
			$score_for_money_info = M('eaterplanet_ecommerce_member_integral_flow')->where( " order_id={$order_id} and order_goods_id={$order_goods_id} and type='orderbuy' " )->find();

			if( !empty($score_for_money_info) )
			{
			    if( $refund_money > 0 &&  $pay_total_money > 0)
			    {
			        $refund_data['back_score_for_money'] =  ($refund_money / $pay_total_money ) * $score_for_money_info['score'] ;

			    }else{
			        $refund_data['back_score_for_money'] =   0 ;

			    }

				//$refund_money

				$refund_intrgral_info = M('eaterplanet_ecommerce_order_goods_refund_intrgral')->where( array('order_goods_id' =>$order_goods_id ,'order_id' => $order_id ) )->order('id desc')->find();

				if( !empty($refund_intrgral_info) )
				{
				    $refund_data['back_score_for_money'] = $refund_intrgral_info['refund_score'];
				}

				//退回去给用户
				D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$refund_data['back_score_for_money'], 0 ,'退款'.$real_refund_quantity.'个商品，增加积分','refundorder', $order_info['order_id'] ,$order_goods_id );

				$send_score_info = M('eaterplanet_ecommerce_member_integral_flow')->where(" order_id={$order_id} and order_goods_id={$order_goods_id} and type='goodsbuy' ")->find();

			}
			if( !empty($send_score_info) )
			{
				$refund_data['back_send_score'] =  intval( ($refund_money / $pay_total_money ) * $send_score_info['score'] );

				$refund_data['back_send_score'] = $refund_data['back_send_score'] <= 0 ? 0 : $refund_data['back_send_score'];
				//减去相应的分数，然后插入


				M('eaterplanet_ecommerce_member_integral_flow')->where( " order_id={$order_id} and type='goodsbuy' and order_goods_id={$order_goods_id} " )->setInc('score', -$refund_data['back_send_score']);

			}

			//$refund_data['back_head_orderbuycommiss'] = 0; //退还团长佣金

			$head_commisslist = M('eaterplanet_community_head_commiss_order')->where( " type in ('orderbuy','commiss') and order_id={$order_id} and order_goods_id={$order_goods_id} and state=0 " )->select();

			if( !empty($head_commisslist) )
			{
				foreach( $head_commisslist as $val )
				{


					if( $val['type'] == 'orderbuy' )
					{

						//$goods_shipping_fare  =$real_refund_quantity * round( ($real_refund_quantity / $order_goods_info['quantity'] ) * $order_goods_info['shipping_fare'] , 2);

						$head_orderbuycommiss = round( ($refund_money / $pay_total_money ) * $val['money'] , 2);

						$head_orderbuycommiss = $head_orderbuycommiss <= 0 ? 0 : $head_orderbuycommiss;

						M('eaterplanet_community_head_commiss_order')->where( array('id' => $val['id'] ) )->setInc('money', -$head_orderbuycommiss);


						$refund_data['back_head_orderbuycommiss'] = $head_orderbuycommiss;
					}
					if( $val['type'] == 'commiss' )
					{
						$val['money'] = $val['money'] - $val['add_shipping_fare'];
						if( $val['level'] == 1 )
						{
							$head_levelcommiss = round( ($refund_money / $pay_total_money ) * $val['money'] , 2);

							$head_levelcommiss = $head_levelcommiss <= 0 ? 0 : $head_levelcommiss;

							M('eaterplanet_community_head_commiss_order')->where( "id=".$val['id'] )->setInc('money', -$head_levelcommiss);

							$refund_data['back_head_commiss_1'] = $head_levelcommiss;
						}
						if( $val['level'] == 2 )
						{
							$head_levelcommiss = round( ($refund_money / $pay_total_money ) * $val['money'] , 2);

							$head_levelcommiss = $head_levelcommiss <= 0 ? 0 : $head_levelcommiss;

							M('eaterplanet_community_head_commiss_order')->where( array('id' => $val['id'] ) )->setInc('money', -$head_levelcommiss);

							$refund_data['back_head_commiss_2'] = $head_levelcommiss;
						}
						if( $val['level'] == 3 )
						{
							$head_levelcommiss = round( ($refund_money / $pay_total_money ) * $val['money'] , 2);

							$head_levelcommiss = $head_levelcommiss <= 0 ? 0 : $head_levelcommiss;

							M('eaterplanet_community_head_commiss_order')->where( array('id' => $val['id'] ) )->setInc('money', -$head_levelcommiss);

							$refund_data['back_head_commiss_3'] = $head_levelcommiss;
						}
					}
				}
			}

			//back_head_supplycommiss    ims_eaterplanet_supply_commiss_order ims_

			$supply_commisslist = M('eaterplanet_supply_commiss_order')->where( " order_id={$order_id} and order_goods_id={$order_goods_id} and state=0 " )->find();

			if( !empty($supply_commisslist) )
			{
				$supply_orderbuycommiss = round( ($refund_money / $pay_total_money ) * $supply_commisslist['total_money'] , 2);
				$supply_orderbuycommiss_money = round( ($refund_money / $pay_total_money ) * $supply_commisslist['money'] , 2);

				$supply_orderbuycommiss = $supply_orderbuycommiss <= 0 ? 0 : $supply_orderbuycommiss;
				$supply_orderbuycommiss_money = $supply_orderbuycommiss_money <= 0 ? 0 : $supply_orderbuycommiss_money;

				M('eaterplanet_supply_commiss_order')->where( array('id' =>$supply_commisslist['id'] ) )->setInc('money', -$supply_orderbuycommiss_money );
				M('eaterplanet_supply_commiss_order')->where( array('id' =>$supply_commisslist['id'] ) )->setInc('total_money', -$supply_orderbuycommiss );

				$refund_data['back_head_supplycommiss'] = $supply_orderbuycommiss;
			}

			//

			//$refund_data['back_member_commiss_1'] = 0; //退客户1级佣金
			//$refund_data['back_member_commiss_2'] = 0; //退客户2级佣金
			//$refund_data['back_member_commiss_3'] = 0; //退客户3级佣金

			$member_commisslist = M('eaterplanet_ecommerce_member_commiss_order')->where( " order_id={$order_id} and order_goods_id={$order_goods_id} and state=0 " )->select();

			if( !empty($member_commisslist) )
			{
				foreach( $member_commisslist as $val )
				{
						if( $val['level'] == 1 )
						{
							$member_levelcommiss = round( ($refund_money / $pay_total_money ) * $val['money'] , 2);

							$member_levelcommiss = $member_levelcommiss <= 0 ? 0 : $member_levelcommiss;

							M('eaterplanet_ecommerce_member_commiss_order')->where( array('id' => $val['id'] ) )->setInc('money', -$member_levelcommiss );


							$refund_data['back_member_commiss_1'] = $member_levelcommiss;
						}
						if( $val['level'] == 2 )
						{
							$member_levelcommiss = round( ($refund_money / $pay_total_money ) * $val['money'] , 2);

							$member_levelcommiss = $member_levelcommiss <= 0 ? 0 : $member_levelcommiss;

							M('eaterplanet_ecommerce_member_commiss_order')->where( array('id' => $val['id'] ) )->setInc('money', -$member_levelcommiss);

							$refund_data['back_member_commiss_2'] = $member_levelcommiss;
						}
						if( $val['level'] == 3 )
						{
							$member_levelcommiss = round( ($refund_money / $pay_total_money ) * $val['money'] , 2);

							$member_levelcommiss = $member_levelcommiss <= 0 ? 0 : $member_levelcommiss;

							M('eaterplanet_ecommerce_member_commiss_order')->where( array('id' => $val['id'] ) )->setInc('money', -$member_levelcommiss );

							$refund_data['back_member_commiss_3'] = $member_levelcommiss;
						}
				}
			}

			//INSERT
			$id = M('eaterplanet_ecommerce_order_goods_refund')->add( $refund_data );


			M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id) )->setInc('has_refund_money', $refund_money );
			M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id) )->setInc('has_refund_quantity', $real_refund_quantity );

			//has_refund_money

			return $id;
		}

	}

	/**
		后台订单详情 部分商品退款操作，检测是否整单退款
		TODO....
	**/
	public function check_refund_order_goods_status($order_id, $order_goods_id, $refund_money,$is_back_sellcount,$real_refund_quantity, $refund_quantity,$is_refund_shippingfare, $ref_comment = '后台操作立即退款,')
	{
		$refund_total_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( "order_id={$order_id} and order_goods_id={$order_goods_id} " )->sum('quantity');

		$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( "order_goods_id={$order_goods_id} " )->find();

		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

		if( $refund_total_quantity >= $order_goods_info['quantity'] || $order_goods_info['has_refund_money'] >= $order_goods_info['total'])
		{
			M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id ) )->save( array('is_refund_state' => 1 ) );

			$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->select();

			$is_all_refund = true;

			foreach($order_goods_list as $val )
			{
				if($val['is_refund_state'] != 1)
				{
					$is_all_refund = false;
				}
			}

			if($is_all_refund)
			{
				$comment = $ref_comment.'退款金额:'.$refund_money.'元';

				if( $order_info['type'] == 'integral' )
				{
					if( $order_info['shipping_fare'] > 0 )
					{
						$comment = $ref_comment.'退款金额:'.$order_info['shipping_fare'].'元，积分:'.$order_info['total'];
					}else{
						$comment = $ref_comment.'退还积分:'.$order_info['total'];
					}
				}

				if($is_refund_shippingfare == 1)
				{
					//最后一次金额
					$shipping_fare = M('eaterplanet_ecommerce_order_goods_refund')->where( "order_id={$order_id} and order_goods_id={$order_goods_id} " )->order('id desc')->find();
					if(!empty($shipping_fare['refund_shipping_fare'])){
						$comment .= '. 退配送费：'.$shipping_fare['refund_shipping_fare'].'元';
					}

				}

				if($is_back_sellcount == 1)
				{
					$comment .= '. 退款商品数量：'.$real_refund_quantity.'. 退库存/扣销量：'.$refund_quantity;
				}else{
					$comment .= '. 退款商品数量：'.$real_refund_quantity.'. 不退库存/不扣销量';
				}


				$order_history = array();
				$order_history['uniacid'] = $_W['uniacid'];
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 7;
				$order_history['notify'] = 0;
				$order_history['comment'] =  $comment;
				$order_history['date_added'] = time();

				M('eaterplanet_ecommerce_order_history')->add( $order_history );

				M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 7) );


				$is_print_admin_cancleorder = D('Home/Front')->get_config_by_name('is_print_admin_cancleorder');

				if( isset($is_print_admin_cancleorder) && $is_print_admin_cancleorder == 1 )
				{
					D('Seller/Printaction')->check_print_order($order_id,'后台操作取消订单');
					D('Seller/Printaction')->check_print_order2($order_id,'后台操作取消订单');
				}

			}

		}else{


		}



	}

	/**
		整单退款，切割退款金额到子订单
	**/
	public  function def_order_refund_togoods( $order_id, $refund_money,$free_tongji,$is_refund_shippingfare )
	{



	}



}
?>
