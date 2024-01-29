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
namespace Home\Model;
class WeixinModel{


	public function refundOrder($order_id, $money=0, $uniacid=0,$order_goods_id=0,$is_back_sellcount = 1,$refund_quantity = 0,$is_zi_order_refund =0)

	{
		$_GPC = I('request.');
		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';

		set_time_limit(0);

		require_once $lib_path."/Weixin/lib/WxPay.Api.php";


		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();


		$openId = $member_info['openid'];
		$we_openid = $member_info['we_openid'];

		if( $order_info['from_type'] == 'wepro' )
		{
			$openId = $we_openid;
		}
		//we_openid
		//money
		$transaction_id = $order_info["transaction_id"];


		if( $order_info['type'] == 'integral' )
		{
			$total_fee = ( $order_info["shipping_fare"] )*100;

		}else{
			$total_fee = ($order_info["total"] + $order_info["shipping_fare"]-$order_info['voucher_credit']-$order_info['fullreduction_money'] - $order_info['score_for_money']-$order_info['fare_shipping_free'] )*100;
		}

		//预售begin
        $presale_result = D('Home/PresaleGoods')->getOrderPresaleInfo( $order_id );
		if( $presale_result['code'] ==0 )
        {
            $total_fee = $total_fee - $presale_result['data']['presale_ding_money'] * 100;
        }
        //end

		$refund_fee = $total_fee;



		//order_goods_id
		if( !empty($order_goods_id) )
		{
			$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' =>$order_goods_id ) )->find();

			$refund_fee = ($order_goods_info["total"] + $order_goods_info["shipping_fare"]-$order_goods_info['voucher_credit']-$order_goods_info['fullreduction_money'] - $order_goods_info['score_for_money']-$order_goods_info['fare_shipping_free'])*100;

			if( $order_info['type'] == 'integral' )
			{
				$refund_fee = ( $order_goods_info["shipping_fare"] )*100;
			}
		}


		if($money > 0 && $order_info['type'] != 'integral' )
		{
			$refund_fee = $money * 100;
		}else if(  $money > 0 && $order_info['type'] == 'integral' && !empty($order_goods_info) )
		{
			if(!empty($_GPC['refund_money'])){
					$refund_money = isset($_GPC['refund_money']) && $_GPC['refund_money'] >0  ? floatval($_GPC['refund_money']) : 0;
					//商品多个运费和
					$refund_fee = ( $money - $refund_money )*100;
					//商品多个商品单价和
					$order_info['total'] = $refund_money;
			}else{
				$order_refund_history_image = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_id) )->order('addtime asc')->find();
				$ref_shipping_fare = M('eaterplanet_ecommerce_order_refund')->field('ref_id,ref_money,real_refund_quantity,ref_shipping_fare')->where( array('order_id' => $order_id ))->order('ref_id desc')->find();
				$refund_fee = ( $ref_shipping_fare['ref_shipping_fare'] )*100;
				$order_info['total'] = $money ;
			}
		}
		// else if( isset($is_open_yinpay) && $is_open_yinpay == 3 )

		$is_open_yinpay = D('Home/Front')->get_config_by_name('is_open_yinpay');

		if($order_info['payment_code'] == 'yuer')
		{
			//余额支付的，退款到余额
			//退款到余额

			//增加客户余额
			$refund_fee = $refund_fee / 100;

			if( $refund_fee > 0 )
			{
				//判断是否积分类型的

				M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id']) )->setInc('account_money',$refund_fee);

				$account_money_info = M('eaterplanet_ecommerce_member')->field('account_money')->where( array('member_id' =>$order_info['member_id'] ) )->find();

				$account_money = $account_money_info['account_money'];


				$member_charge_flow_data = array();

				$member_charge_flow_data['member_id'] = $order_info['member_id'];
				$member_charge_flow_data['money'] = $refund_fee;
				$member_charge_flow_data['operate_end_yuer'] = $account_money;
				$member_charge_flow_data['state'] = 4;
				$member_charge_flow_data['trans_id'] = $order_id;
				$member_charge_flow_data['order_goods_id'] = $order_goods_id;
				$member_charge_flow_data['charge_time'] = time();
				$member_charge_flow_data['add_time'] = time();

				M('eaterplanet_ecommerce_member_charge_flow')->add($member_charge_flow_data);
			}


			if($order_info['order_status_id'] == 12)
			{
				$ref_count = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_info['order_id'], 'state' => 0 ) )->count();


				if( $ref_count <= 1 )
				{
					M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
				}
			}




			$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_info['order_id'] ) )->select();

			$goods_model = D('Home/Pingoods');

			foreach($order_goods_list as $order_goods)
			{
				//$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);

				if( !empty($order_goods_id) && $order_goods_id > 0  )
				{
					if($order_goods_id ==  $order_goods['order_goods_id'] )
					{

						if($is_back_sellcount == 1)
						{
							if( $is_zi_order_refund == 1 && $refund_quantity > 0 )
							{
								$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$refund_quantity,2);
							}else{
								//获取已退款数量
								$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
								if(!$has_refund_quantity){
									$has_refund_quantity = 0;
								}
								$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
							}
						}

						$score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' =>$order_info['order_id'],'type' => 'orderbuy' ) )->find();

						if( !empty($score_refund_info) && $is_zi_order_refund == 1  )
						{
							// D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
						}
					}
				}else if( empty($order_goods_id) || $order_goods_id <=0 ){
					if($is_back_sellcount == 1){
						//获取已退款数量
						$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
						if(!$has_refund_quantity){
							$has_refund_quantity = 0;
						}
						$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
					}


					$score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' => $order_info['order_id'] ,'type' => 'orderbuy') )->find();

					if( !empty($score_refund_info) )
					{
						// D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
					}
				}

				if( $order_info['type'] == 'integral' )
				{

					D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$order_info['total'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
				}
			}
			//分佣也要退回去
			if($is_zi_order_refund == 0)
					D('Seller/Community')->back_order_commission($order_info['order_id'],$order_goods_id);

			return array('code' => 1);
			//$this->refundOrder_success($order_info,$openId);
			//检测是否有需要退回积分的订单
		//货到付款订单
		}else if($order_info['payment_code'] == 'cashon_delivery')
		{
			//货到付款订单退款金额不到账，但有退款记录

			if($order_info['order_status_id'] == 12)
			{
				$ref_count = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_info['order_id'], 'state' => 0 ) )->count();

				if( $ref_count <= 1 )
				{
					M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
				}
			}

			$order_info['total'] = $refund_fee / 100;

			$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_info['order_id'] ) )->select();

			$goods_model = D('Home/Pingoods');

			foreach($order_goods_list as $order_goods)
			{
				//$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);

				if( !empty($order_goods_id) && $order_goods_id > 0  )
				{
					if($order_goods_id ==  $order_goods['order_goods_id'] )
					{

						if($is_back_sellcount == 1)
						{
							if( $is_zi_order_refund == 1 && $refund_quantity > 0 )
							{
								$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$refund_quantity,2);
							}else{
								//获取已退款数量
								$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
								if(!$has_refund_quantity){
									$has_refund_quantity = 0;
								}
								$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
							}
						}

						$score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' =>$order_info['order_id'],'type' => 'orderbuy' ) )->find();

						if( !empty($score_refund_info) && $is_zi_order_refund == 1  )
						{
							// D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
						}
					}
				}else if( empty($order_goods_id) || $order_goods_id <=0 ){
					if($is_back_sellcount == 1){
						//获取已退款数量
						$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
						if(!$has_refund_quantity){
							$has_refund_quantity = 0;
						}
						$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
					}


					$score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' => $order_info['order_id'] ,'type' => 'orderbuy') )->find();

					if( !empty($score_refund_info) )
					{
						// D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
					}
				}

				/*if( $order_info['type'] == 'integral' )
				{

					D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$order_info['total'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
				}*/
			}
			//分佣也要退回去
			if($is_zi_order_refund == 0)
				D('Seller/Community')->back_order_commission($order_info['order_id'],$order_goods_id);

			return array('code' => 1);
			//$this->refundOrder_success($order_info,$openId);
			//检测是否有需要退回积分的订单
		}
		else if($order_info['payment_code'] == 'admin'){

			if($order_info['order_status_id'] == 12)
			{
				$ref_count = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_info['order_id'], 'state' => 0 ) )->count();
				if( $ref_count <= 1 )
				{
					M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
				}
			}

			$order_info['total'] = $refund_fee / 100;

			$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_info['order_id'] ) )->select();


			$goods_model = D('Home/Pingoods');
			foreach($order_goods_list as $order_goods)
			{
				//$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],2);

				if( !empty($order_goods_id) && $order_goods_id > 0  )
				{
					if($order_goods_id ==  $order_goods['order_goods_id'] )
					{

						if($is_back_sellcount == 1)
						{
							if( $is_zi_order_refund == 1 && $refund_quantity > 0 )
							{
								$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$refund_quantity,2);
							}else{
								//获取已退款数量
								$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
								if(!$has_refund_quantity){
									$has_refund_quantity = 0;
								}
								$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
							}
						}

						$score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' =>$order_info['order_id'],'type' => 'orderbuy' ) )->find();

						if( !empty($score_refund_info) && $is_zi_order_refund == 1 )
						{
							 //D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
						}
					}
				}else if( empty($order_goods_id) || $order_goods_id <=0 ){
					if($is_back_sellcount == 1){
						//获取已退款数量
						$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
						if(!$has_refund_quantity){
							$has_refund_quantity = 0;
						}
						$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
					}


					$score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_goods_id' => $order_goods['order_goods_id'],'order_id' => $order_info['order_id'] ,'type' => 'orderbuy') )->find();

					if( !empty($score_refund_info) )
					{
						// D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
					}
				}

				if( $order_info['type'] == 'integral' )
				{
					//D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$order_goods['total'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
				}

			}
			//分佣也要退回去
			if($is_zi_order_refund == 0)
				D('Seller/Community')->back_order_commission($order_info['order_id'],$order_goods_id);
			return array('code' => 1);

		}
		else if($refund_fee == 0)
		{
			if($order_info['order_status_id'] == 12)
			{
				$ref_count = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_info['order_id'], 'state' => 0 ) )->count();
				if( $ref_count <= 1 )
				{
					M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
				}
			}

			//ims_ eaterplanet_ecommerce_order_goods
			$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_info['order_id']) )->select();

			$order_goods_name = '';
			$order_goods_name_arr = array();
			$goods_model = D('Home/Pingoods');

			//get_config_by_name($name)



			foreach ($order_goods as $key => $value) {
				//($order_id,$option,$goods_id,$quantity,$type='1')
				if($is_back_sellcount == 1)
				{
					if( $is_zi_order_refund == 1 && $refund_quantity > 0 )
					{
						$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$value['rela_goodsoption_valueid'],$value['goods_id'],$refund_quantity,2);
					}else{
						//获取已退款数量
						$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
						if(!$has_refund_quantity){
							$has_refund_quantity = 0;
						}
						$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$value['rela_goodsoption_valueid'],$value['goods_id'],$value['quantity']-$has_refund_quantity,2);
					}
				}

				$score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' =>$order_info['order_id'] ,'order_goods_id' =>$value['order_goods_id'] ,'type' => 'orderbuy') )->find();

				if( !empty($score_refund_info) )
				{
					// D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$value['order_goods_id'] );
				}

				//销量回退
				$order_goods_name_arr[] = $value['name'];
			}
			$order_goods_name = implode('\r\n', $order_goods_name_arr); //."\r\n";


			$msg = '订单退款: 您的订单'.$order_info['order_num_alias'].'参团未成功，现退款:'.round($order_info["total"],2).'元，商品名称：'.$order_goods_name;

			$url = D('Home/Front')->get_config_by_name('shop_domain');

			//weixin_template_refund_order
			//send_template_msg($wx_template_data,$url,$openid,C('weixin_template_refund_order'));

			/**
			{{first.DATA}}
			订单编号：{{keyword1.DATA}}
			退款金额：{{keyword2.DATA}}
			{{remark.DATA}}
			---------------------------
			校白君提醒您，您有一笔退款成功，请留意。
			订单编号：20088115853
			退款金额：¥19.00
			更多学生价好货，在底部菜单栏哦~猛戳“校园专区”，享更多优惠！
			**/

			$wx_template_data = array();
			$wx_template_data['first'] = array('value' => '退款通知', 'color' => '#030303');
			$wx_template_data['keyword1'] = array('value' => $order_goods_name, 'color' => '#030303');
			$wx_template_data['keyword2'] = array('value' => round($order_info["total"],2), 'color' => '#030303');
			$wx_template_data['remark'] = array('value' => '拼团失败已按原路退款', 'color' => '#030303');


			if( $order_info['from_type'] == 'wepro' )
			{
				$template_data = array();
				$template_data['keyword1'] = array('value' => $order_info['order_num_alias'], 'color' => '#030303');
				$template_data['keyword2'] = array('value' => '商户名称', 'color' => '#030303');
				$template_data['keyword2'] = array('value' => $order_goods_name, 'color' => '#030303');
				$template_data['keyword3'] = array('value' => $order_info['total'].'元', 'color' => '#030303');
				$template_data['keyword4'] = array('value' => '已按原路退款', 'color' => '#030303');
				$template_data['keyword5'] = array('value' => $member_info['uname'], 'color' => '#030303');


				$template_id = D('Home/Front')->get_config_by_name('weprogram_template_refund_order');

				$pagepath = 'eaterplanet_ecommerce/pages/order/order?id='.$order_info['order_id'];


				$member_formid_info = M('eaterplanet_ecommerce_member_formid')->where("member_id=".$order_info['member_id']." and formid != '' and state =0")->order('id desc')->find();

				if(!empty( $member_formid_info ))
				{
					D('Seller/User')->send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'] );

					M('eaterplanet_ecommerce_member_formid')->where( array('id' => $member_formid_info['id']) )->save(array('state' => 1));
				}

				if( $openid != '1')
				{
					//send_template_msg($wx_template_data,$url,$member_info['openid'],C('weixin_template_refund_order'));
				}
			}else{
				//send_template_msg($wx_template_data,$url,$member_info['openid'],C('weixin_template_refund_order'));
			}

			//检测是否有需要退回积分的订单

		}
		else if( isset($is_open_yinpay) && $is_open_yinpay == 3 )
		{
		    $order_relate_info = M('eaterplanet_ecommerce_order_relate')->where( array('order_id' => $order_id ) )->order('id desc')->find();

		    if( !empty($order_relate_info) && $order_relate_info['order_all_id'] > 0 )
		    {
		        $order_all_info = M('eaterplanet_ecommerce_order_all')->where( array('id' => $order_relate_info['order_all_id'] ) )->find();

		        if( !empty($order_all_info) && !empty($order_all_info['out_trade_no']) )
		        {

		            $out_trade_no = $order_all_info['out_trade_no'];

		            $appid =  D('Home/Front')->get_config_by_name('wepro_fuwu_appid');
		            $mch_id =      D('Home/Front')->get_config_by_name('wepro_fuwu_partnerid');

		            $nonce_str =    nonce_str();

		            $pay_key = D('Home/Front')->get_config_by_name('wepro_key');

		            $sub_appid = D('Home/Front')->get_config_by_name('wepro_appid');
		            $sub_mch_id = D('Home/Front')->get_config_by_name('wepro_sub_mch_id');


		            $post = array();
		            $post['appid'] = $appid;
		            $post['mch_id'] = $mch_id;
		            $post['nonce_str'] = $nonce_str;
		            $post['out_trade_no'] = $out_trade_no;
		            $post['sub_appid'] = $sub_appid;
		            $post['sub_mch_id'] = $sub_mch_id;

		            $sign = sign($post,$pay_key);

		            $post_xml = '<xml>
							   <appid>'.$appid.'</appid>
							   <mch_id>'.$mch_id.'</mch_id>
							   <nonce_str>'.$nonce_str.'</nonce_str>
							   <out_trade_no>'.$out_trade_no.'</out_trade_no>
							   <sub_appid>'.$post['sub_appid'].'</sub_appid>
							   <sub_mch_id>'.$post['sub_mch_id'].'</sub_mch_id>
							   <sign>'.$sign.'</sign>
							</xml>';

		            $url = "https://api.mch.weixin.qq.com/pay/orderquery";

		            $result = http_request($url,$post_xml);

		            $array = xml($result);

		            if( $array['RETURN_CODE'] == 'SUCCESS' && $array['RETURN_MSG'] == 'OK' )
		            {
		                if( $array['TRADE_STATE'] == 'SUCCESS' || $array['TRADE_STATE'] == 'REFUND' )
		                {
		                    $total_fee = $array['TOTAL_FEE'];
		                }
		            }

		        }
		    }

		    //


		    $input = new \WxPayRefund();

		    $input->SetTransaction_id($transaction_id);
		    $input->SetTotal_fee($total_fee);
		    $input->SetRefund_fee($refund_fee);

		    $mchid = D('Home/Front')->get_config_by_name('wepro_partnerid');

		    $refund_no = $mchid.date("YmdHis").$order_info['order_id'];

		    $input->SetOut_refund_no($refund_no);
		    $input->SetOp_user_id($mchid);


		    $res = \WxPayApi::refund($input,6,'teweixin');

		    if($res["return_code"] == 'FAIL'){
          		return array('code' => 0, 'msg' => $res['return_msg']);
			}

		    if( $res['err_code_des'] == '订单已全额退款' )
		    {
		        $res['result_code'] = 'SUCCESS';
		    }

		    if($res['result_code'] == 'SUCCESS')
		    {

		        if($order_info['order_status_id'] == 12)
		        {
		            $ref_count = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_info['order_id'], 'state' => 0 ) )->count();
		            if( $ref_count <= 1 )
		            {
		                M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
		            }
		        }

		        $order_info['total'] = $refund_fee / 100;


		        $order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_info['order_id']) )->select();

		        $order_goods_name_arr = array();
		        $order_goods_name = '';

		        foreach($order_goods_list as $order_goods)
		        {

		            $order_goods_name_arr[] = $order_goods['name'];
		            //...
		            if( !empty($order_goods_id) && $order_goods_id > 0  )
		            {
		                if($order_goods_id ==  $order_goods['order_goods_id'] )
		                {
		                    if($is_back_sellcount == 1)
		                    {
		                        if( $is_zi_order_refund == 1 && $refund_quantity > 0 )
		                        {
		                            D('Home/Pingoods')->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$refund_quantity,2);
		                        }else{
		                            //获取已退款数量
		                            $has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
		                            if(!$has_refund_quantity){
		                                $has_refund_quantity = 0;
		                            }
		                            D('Home/Pingoods')->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
		                        }
		                    }

		                    $score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' => $order_info['order_id'],'order_goods_id'=>$order_goods['order_goods_id'] ,'type' => 'orderbuy') )->find();

		                    if( !empty($score_refund_info) && $is_zi_order_refund == 1  )
		                    {
		                        // D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
		                    }
		                }
		            }else if( empty($order_goods_id) || $order_goods_id <=0 ){
		                if($is_back_sellcount == 1){
		                    $has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
		                    if(!$has_refund_quantity){
		                        $has_refund_quantity = 0;
		                    }
		                    D('Home/Pingoods')->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
		                }


		                $score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' => $order_info['order_id'],'order_goods_id' =>$order_goods['order_goods_id'] ,'type' => 'orderbuy') )->find();

		                if( !empty($score_refund_info) )
		                {
		                    // D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
		                }
		            }

		            if( $order_info['type'] == 'integral' )
		            {
		                D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$order_goods['total'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
		            }

		        }

		        $order_goods_name = implode('\r\n', $order_goods_name_arr); //."\r\n";


		        //分佣也要退回去
		        if($is_zi_order_refund == 0)
		            D('Seller/Community')->back_order_commission($order_info['order_id'],$order_goods_id);

		        return array('code' => 1);

		    } else {

		        $order_refund_history = array();
		        $order_refund_history['order_id'] =  $order_info['order_id'];
		        $order_refund_history['order_goods_id'] =  $order_goods_id;

		        $order_refund_history['message'] = $res['err_code_des'];
		        $order_refund_history['type'] = 2;
		        $order_refund_history['addtime'] = time();

		        M('eaterplanet_ecommerce_order_refund_history')->add($order_refund_history);

		        /**
		         M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 10, 'remarksaler' => $res[err_code_des]) );
		        **/

		        return array('code' => 0, 'msg' => $res['err_code_des']);

		    }

		}
		else {



            $order_relate_info = M('eaterplanet_ecommerce_order_relate')->where( array('order_id' => $order_id ) )->order('id desc')->find();

            if( !empty($order_relate_info) && $order_relate_info['order_all_id'] > 0 )
            {
                $order_all_info = M('eaterplanet_ecommerce_order_all')->where( array('id' => $order_relate_info['order_all_id'] ) )->find();

                if( !empty($order_all_info) && !empty($order_all_info['out_trade_no']) )
                {

                    $out_trade_no = $order_all_info['out_trade_no'];

                    $appid =  D('Home/Front')->get_config_by_name('wepro_appid');
                    $mch_id =      D('Home/Front')->get_config_by_name('wepro_partnerid');
                    $nonce_str =    nonce_str();

                    $pay_key = D('Home/Front')->get_config_by_name('wepro_key');


                    $post = array();
                    $post['appid'] = $appid;
                    $post['mch_id'] = $mch_id;
                    $post['nonce_str'] = $nonce_str;
                    $post['out_trade_no'] = $out_trade_no;

                    $sign = sign($post,$pay_key);

                    $post_xml = '<xml>
							   <appid>'.$appid.'</appid>
							   <mch_id>'.$mch_id.'</mch_id>
							   <nonce_str>'.$nonce_str.'</nonce_str>
							   <out_trade_no>'.$out_trade_no.'</out_trade_no>
							   <sign>'.$sign.'</sign>
							</xml>';

                    $url = "https://api.mch.weixin.qq.com/pay/orderquery";

                    $result = http_request($url,$post_xml);

                    $array = xml($result);

                    if( $array['RETURN_CODE'] == 'SUCCESS' && $array['RETURN_MSG'] == 'OK' )
                    {
                        if( $array['TRADE_STATE'] == 'SUCCESS' || $array['TRADE_STATE'] == 'REFUND' )
                        {
                           $total_fee = $array['TOTAL_FEE'];
                        }
                    }

                }
            }

            //


			$input = new \WxPayRefund();

			$input->SetTransaction_id($transaction_id);
			$input->SetTotal_fee($total_fee);
			$input->SetRefund_fee($refund_fee);

			$mchid = D('Home/Front')->get_config_by_name('wepro_partnerid');

			$refund_no = $mchid.date("YmdHis").$order_info['order_id'];

			$input->SetOut_refund_no($refund_no);
			$input->SetOp_user_id($mchid);


			$res = \WxPayApi::refund($input,6,$order_info['from_type']);

			if($res["return_code"] == 'FAIL'){
          		return array('code' => 0, 'msg' => $res['return_msg']);
			}

			if( $res['err_code_des'] == '订单已全额退款' )
			{
				$res['result_code'] = 'SUCCESS';
			}

			if($res['result_code'] == 'SUCCESS')
			{

				if($order_info['order_status_id'] == 12)
				{
					$ref_count = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_info['order_id'], 'state' => 0 ) )->count();
					if( $ref_count <= 1 )
					{
						M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );
					}
				}

				$order_info['total'] = $refund_fee / 100;


				$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_info['order_id']) )->select();

				$order_goods_name_arr = array();
				$order_goods_name = '';

				foreach($order_goods_list as $order_goods)
				{

					$order_goods_name_arr[] = $order_goods['name'];
					//...
					if( !empty($order_goods_id) && $order_goods_id > 0  )
					{
						if($order_goods_id ==  $order_goods['order_goods_id'] )
						{
							if($is_back_sellcount == 1)
							{
								if( $is_zi_order_refund == 1 && $refund_quantity > 0 )
								{
									D('Home/Pingoods')->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$refund_quantity,2);
								}else{
									//获取已退款数量
									$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
									if(!$has_refund_quantity){
										$has_refund_quantity = 0;
									}
									D('Home/Pingoods')->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
								}
							}

							$score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' => $order_info['order_id'],'order_goods_id'=>$order_goods['order_goods_id'] ,'type' => 'orderbuy') )->find();

							if( !empty($score_refund_info) && $is_zi_order_refund == 1  )
							{
								// D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
							}
						}
					}else if( empty($order_goods_id) || $order_goods_id <=0 ){
						if($is_back_sellcount == 1){
							$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $order_info['order_id'], 'order_goods_id' =>$order_goods['order_goods_id']) )->sum('quantity');
							if(!$has_refund_quantity){
								$has_refund_quantity = 0;
							}
							D('Home/Pingoods')->del_goods_mult_option_quantity($order_info['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity']-$has_refund_quantity,2);
						}


						$score_refund_info = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' => $order_info['order_id'],'order_goods_id' =>$order_goods['order_goods_id'] ,'type' => 'orderbuy') )->find();

						if( !empty($score_refund_info) )
						{
							// D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$score_refund_info['score'], 0 ,'退款增加积分', 'refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
						}
					}

					if( $order_info['type'] == 'integral' )
					{
						D('Admin/Member')->sendMemberPointChange($order_info['member_id'],$order_goods['total'], 0 ,'退款增加积分','refundorder', $order_info['order_id'] ,$order_goods['order_goods_id'] );
					}

				}

				$order_goods_name = implode('\r\n', $order_goods_name_arr); //."\r\n";


				//分佣也要退回去
				if($is_zi_order_refund == 0)
					D('Seller/Community')->back_order_commission($order_info['order_id'],$order_goods_id);

				return array('code' => 1);

			} else {

				$order_refund_history = array();
				$order_refund_history['order_id'] =  $order_info['order_id'];
				$order_refund_history['order_goods_id'] =  $order_goods_id;

				$order_refund_history['message'] = $res['err_code_des'];
				$order_refund_history['type'] = 2;
				$order_refund_history['addtime'] = time();

				M('eaterplanet_ecommerce_order_refund_history')->add($order_refund_history);

				/**
				M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 10, 'remarksaler' => $res[err_code_des]) );
				**/

				return array('code' => 0, 'msg' => $res['err_code_des']);

			}

		}




	}

	public function refundOrder2($order_id,$money =0)
	{

		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
		$data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
		RecursiveMkdir($data_path);

		set_time_limit(0);


		require_once $lib_path."/Weixin/lib/WxPay.Api.php";
		require_once $lib_path."/Weixin/log.php";

		//初始化日志
		$logHandler= new \CLogFileHandler( $data_path .date('Y-m-d').'.log');

		\Log::Init($logHandler, 15);
		\Log::DEBUG("进行中，订单ID ：".$order_id );

		//pin
		$order_info = M('order')->where( array('order_id' => $order_id) )->find();

		$member_info = M('member')->where( array('member_id' => $order_info['member_id']) )->find();

		$openId = $member_info['openid'];
		$we_openid = $member_info['we_openid'];

		if( $order_info['from_type'] == 'wepro' )
		{
			$openId = $we_openid;
		}
		//we_openid
		//money
		$transaction_id = $order_info["transaction_id"];



		$total_fee = ($order_info["total"])*100;
		$refund_fee = $total_fee;
		if($money > 0)
		{
			$refund_fee = $money * 100;
		}



		if($order_info['payment_code'] == 'yuer')
		{
			//余额支付的，退款到余额
			//退款到余额
			$member_charge_flow_data = array();
			$member_charge_flow_data['member_id'] = $order_info['member_id'];
			$member_charge_flow_data['money'] = $order_info["total"];
			$member_charge_flow_data['state'] = 4;
			$member_charge_flow_data['trans_id'] = $order_id;
			$member_charge_flow_data['charge_time'] = time();
			$member_charge_flow_data['add_time'] = time();

			M('member_charge_flow')->add($member_charge_flow_data);
			//增加客户余额
			M('member')->where( array('member_id'=> $order_info['member_id'] ) )->setInc('account_money',$order_info["total"] );


			$order_info['total'] = $refund_fee / 100;
			$this->refundOrder_success($order_info,$openId);
			//检测是否有需要退回积分的订单
		}
		else if($refund_fee == 0)
		{
			M('order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );

			$config_info = M('config')->where( array('name' => 'SITE_URL') )->find();

			$order_goods = M('order_goods')->where( array('order_id' => $order_info['order_id']) )->select();

			$order_goods_name = '';
			$order_goods_name_arr = array();

			foreach ($order_goods as $key => $value) {

				$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$value['rela_goodsoption_valueid'],$value['goods_id'],$value['quantity'],2);
				//销量回退
				//$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET seller_count = (seller_count - " . (int)$value['quantity'] . ") WHERE goods_id = '" . $value['goods_id'] . "' ");
				$order_goods_name_arr[] = $value['name'];
			}
			$order_goods_name = implode('\r\n', $order_goods_name_arr); //."\r\n";


			$msg = '订单退款: 您的订单'.$order_info['order_num_alias'].'参团未成功，现退款:'.round($order_info["total"],2).'元，商品名称：'.$order_goods_name;
			$url = $config_info['value'];

			//weixin_template_refund_order
			//send_template_msg($wx_template_data,$url,$openid,C('weixin_template_refund_order'));
			$url = $url."/index.php?s=/Order/info/id/{$order_info['order_id']}.html";

			/**
			{{first.DATA}}
			订单编号：{{keyword1.DATA}}
			退款金额：{{keyword2.DATA}}
			{{remark.DATA}}
			---------------------------
			校白君提醒您，您有一笔退款成功，请留意。
			订单编号：20088115853
			退款金额：¥19.00
			更多学生价好货，在底部菜单栏哦~猛戳“校园专区”，享更多优惠！
			**/

			$wx_template_data = array();
			$wx_template_data['first'] = array('value' => '退款通知', 'color' => '#030303');
			$wx_template_data['keyword1'] = array('value' => $order_goods_name, 'color' => '#030303');
			$wx_template_data['keyword2'] = array('value' => round($order_info["total"],2), 'color' => '#030303');
			$wx_template_data['remark'] = array('value' => '拼团失败已按原路退款', 'color' => '#030303');


			if( $order_info['from_type'] == 'wepro' )
			{
				$template_data = array();
				$template_data['keyword1'] = array('value' => $order_goods_name, 'color' => '#030303');
				$template_data['keyword2'] = array('value' => '参团未成功', 'color' => '#030303');
				$template_data['keyword3'] = array('value' => '拼团失败已按原路退款', 'color' => '#030303');

				$pay_order_msg_info =  M('config')->where( array('name' => 'weprogram_template_fail_pin') )->find();
				$template_id = $pay_order_msg_info['value'];


				$pagepath = 'pages/order/order?id='.$order_info['order_id'];

				/**
				$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'], 'state' => 0) )->find();

				send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);
				//更新
				M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );

				$order_info['member_id']
				**/

				$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'], 'formid' =>array('neq',''), 'state' => 0) )->order('id desc')->find();
				if(!empty( $member_formid_info ))
				{
					send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'] );
					M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
				}


				if( $openid != '1')
				{
					//notify_weixin_msg($member_info['openid'],$msg,'退款通知',$url);
					send_template_msg($wx_template_data,$url,$member_info['openid'],C('weixin_template_refund_order'));
				}
			}else{
				//notify_weixin_msg($member_info['openid'],$msg,'退款通知',$url);
				send_template_msg($wx_template_data,$url,$member_info['openid'],C('weixin_template_refund_order'));
			}

			//检测是否有需要退回积分的订单

			\Log::DEBUG("退款成功。。。退款日志:退款订单号:" . $order_info['order_id'].',ten:'.$transaction_id.'   退款金额： '
			.$order_info["total"].',退款给：openid='.$openId);

		} else {

			$input = new \WxPayRefund();
			$input->SetTransaction_id($transaction_id);
			$input->SetTotal_fee($total_fee);
			$input->SetRefund_fee($refund_fee);
			$refund_no = \WxPayConfig::MCHID.date("YmdHis").$order_info['order_id'];

			$input->SetOut_refund_no($refund_no);
			$input->SetOp_user_id(\WxPayConfig::MCHID);

			$res = (\WxPayApi::refund($input,6,$order_info['from_type']));

			//var_dump($res);die();  wx80131aa7dfc4ff71

			if($res['result_code'] == 'SUCCESS')
			{
				$order_info['total'] = $refund_fee / 100;
				$this->refundOrder_success($order_info,$openId);

				\Log::DEBUG("退款成功。。。退款日志:退款订单号:" . $order_info['order_id'].',ten:'.$transaction_id.'   退款金额： '.$order_info["total"].',退款给：openid='.$openid);
				//检测是否有需要退回积分的订单

			} else {

				M('order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 10, 'comment' => $res[err_code_des]) );

				\Log::DEBUG("退款失败。原因：{$res[err_code_des]}。。退款日志:退款订单号:" . $order_info['order_id'].',退款金额： '.$order_info["total"].',退款给：openid='.$openId);
			}

		}

		return true;
	}

	/**
		取消已经付款的 待发货订单
		5、处理订单，
		6、处理退款，
	**/
	public  function del_cancle_order($order_id)
	{
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();


		//判断订单状态是否已付款，避免多次退款，不合理
		if( $order_info['order_status_id'] == 1 )
		{
			//del_cancle_order

			$total_money = D('Seller/Commonorder')->get_order_paymoney( $order_id );

			$has_refun_money = D('Seller/Commonorder')->order_refund_totalmoney( $order_id );

			//是否预售订单begin
            $presale_result = D('Home/PresaleGoods')->getOrderPresaleInfo( $order_id );
            $presale_info = [];
            if( $presale_result['code'] == 0 )
            {
                $presale_info = $presale_result['data'];
                $total_money = $total_money - $presale_info['presale_ding_money'];
            }
            //end

			$refund_money = round($total_money - $has_refun_money,2);



			$result = $this->refundOrder($order_id, $refund_money);


			if( $result['code'] == 1 )
			{
				$order_history = array();
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 5;
				$order_history['notify'] = 0;
				$order_history['comment'] = '客户前台申请取消订单，取消成功，并退款。';
				$order_history['date_added'] = time();

				M('eaterplanet_ecommerce_order_history')->add( $order_history );

				M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 5) );

				//整笔
				D('Seller/Commonorder')->refund_one_order( $order_id ,1);


				return array('code' => 0);
			}else{
				$order_history = array();
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 10;
				$order_history['notify'] = 0;
				$order_history['comment'] = '申请取消订单，但是退款失败。';
				$order_history['date_added'] = time();

				M('eaterplanet_ecommerce_order_history')->add( $order_history );

				M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->save( array('order_status_id' => 10, 'remarksaler' => $result['msg']) );

				return array('code' => 1, 'msg' => $result['msg'] ,'s' => $result['code'] );
			}

		}
		 //如果退款成功了。那么就进行

	}




	public function test_form_msg()
	{
		$member_info = M('member')->where( array('member_id' => 26) )->find();

		$form_id_arr = M('member_formid')->where( array('member_id' => 26,'state' =>0) )->find();

		M('member_formid')->where( array('id' => $form_id_arr['id'] ) )->save( array('state' =>1) );
		$form_id = $form_id_arr['formid'];

		$template_data = array();
		$template_data['keyword1'] = array('value' => '338866', 'color' => '#030303');
		$template_data['keyword2'] = array('value' => '商品名称', 'color' => '#030303');
		$template_data['keyword3'] = array('value' => '18元', 'color' => '#030303');
		$template_data['keyword4'] = array('value' => '已按原路退款', 'color' => '#030303');
		$template_data['keyword5'] = array('value' => '小鱼', 'color' => '#030303');

		$pay_order_msg_info =  M('config')->where( array('name' => 'weprogram_template_refund_order') )->find();
		$template_id = $pay_order_msg_info['value'];


		$pagepath = 'pages/order/order?id='.$order_info['order_id'];

		$rs = 	send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$form_id);
			var_dump($rs);die();
	}

	public function refundOrder_success($order_info,$openid)
	{
		M('order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 7) );

		$member_info = M('member')->where( array('member_id' => $order_info['member_id']) )->find();


		$config_info = M('config')->where( array('name' => 'SITE_URL') )->find();

		$order_goods = M('order_goods')->where( array('order_id' => $order_info['order_id']) )->select();
		$goods_model = D('Home/Goods');

		$order_goods_name = '';
		$order_goods_name_arr = array();

		foreach ($order_goods as $key => $value) {
			//$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET quantity = (quantity + " . (int)$value['quantity'] . ") WHERE goods_id = '" . $value['goods_id'] . "' ");

			$goods_model->del_goods_mult_option_quantity($order_info['order_id'],$value['rela_goodsoption_valueid'],$value['goods_id'],$value['quantity'],2);
			//销量回退
			//$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET seller_count = (seller_count - " . (int)$value['quantity'] . ") WHERE goods_id = '" . $value['goods_id'] . "' ");
			$order_goods_name_arr[] = $value['name'];
		}

		$order_goods_name = implode('\r\n', $order_goods_name_arr); //."\r\n";

		$msg = '订单退款: 您的订单'.$order_info['order_num_alias'].'参团未成功，现退款:'.round($order_info["total"],2).'元，商品名称：'.$order_goods_name;
		$url = $config_info['value'];

		$wx_template_data = array();
		$wx_template_data['first'] = array('value' => '退款通知', 'color' => '#030303');
		$wx_template_data['keyword1'] = array('value' => $order_goods_name, 'color' => '#030303');
		$wx_template_data['keyword2'] = array('value' => $order_info['total'].'元', 'color' => '#030303');
		$wx_template_data['remark'] = array('value' => '已按原路退款', 'color' => '#030303');


		$url = $url."/index.php?s=/Order/info/id/{$order_info['order_id']}.html";


		if( $order_info['from_type'] == 'wepro' )
		{
			/**
			退款成功通知
			关键词
			订单号
			{{keyword1.DATA}}
			商品名称
			{{keyword2.DATA}}
			退款金额
			{{keyword3.DATA}}
			温馨提示
			{{keyword4.DATA}}
			备注
			{{keyword5.DATA}}
			**/

			//$total_money = ($order_info["total"],2);

			$template_data = array();
			$template_data['keyword1'] = array('value' => $order_info['order_num_alias'], 'color' => '#030303');
			$template_data['keyword2'] = array('value' => $order_goods_name, 'color' => '#030303');
			$template_data['keyword3'] = array('value' => $order_info['total'].'元', 'color' => '#030303');
			$template_data['keyword4'] = array('value' => '已按原路退款', 'color' => '#030303');
			$template_data['keyword5'] = array('value' => $member_info['uname'], 'color' => '#030303');

			$pay_order_msg_info =  M('config')->where( array('name' => 'weprogram_template_refund_order') )->find();
			$template_id = $pay_order_msg_info['value'];


			$pagepath = 'pages/order/order?id='.$order_info['order_id'];


			/**
				$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'], 'state' => 0) )->find();

				send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);
				//更新
				M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );

			**/
			$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'],'formid' =>array('neq',''), 'state' => 0) )->order('id desc')->find();

			//$order_info['member_id']
			if( !empty($member_formid_info) )
			{
				$rs = 	send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);
				M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
			}


			if( $openid != '1')
			{
				//notify_weixin_msg($openid,$msg,'退款通知',$url);
				send_template_msg($wx_template_data,$url,$openid,C('weixin_template_refund_order'));

			}
		}else{
			//notify_weixin_msg($openid,$msg,'退款通知',$url);
			send_template_msg($wx_template_data,$url,$openid,C('weixin_template_refund_order'));

		}



	}
}
?>
