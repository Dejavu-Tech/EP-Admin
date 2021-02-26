<?php
require_once dirname(__FILE__) ."/lib/WxPay.Api.php";
require_once dirname(__FILE__) .'/lib/WxPay.Notify.php';
require_once dirname(__FILE__) .'/log.php';


$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';

$data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";

RecursiveMkdir($data_path);




//初始化日志
//\Think\Log::record("begin notify222");

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		global $INI;
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);




		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			//DO
			return true;
		}
		return false;
	}

	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		global $_W;
		global $_GPC;


		//global $_W;

		 $out_trade_no_str = $data['out_trade_no'];
		$out_trade_no_arr =  explode('-',$out_trade_no_str);
		$out_trade_no = $out_trade_no_arr[0];



		$data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";

		RecursiveMkdir($data_path);

		$file = $data_path.date('Y-m-d').'.txt';
		$handl = fopen($file,'a');
		fwrite($handl,"Queryorder");
		fwrite($handl,"call back:" . json_encode($data));
     	 fwrite($handl,"小程序开始查询支付：");
		fclose($handl);



		$notfiyOutput = array();

		//\Think\Log::record("call back:" . json_encode($data));

		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if( false &&  !$this->Queryorder($data["transaction_id"])){




			$msg = "订单查询失败";
			return false;
		}else {
			/**
			{
				"appid":"wx334ca53b2a62661a",
				"bank_type":"CFT",
				"cash_fee":"1",
				"fee_type":"CNY",
				"is_subscribe":"N",
				"mch_id":"1246637501",
				"nonce_str":"eKWYmZBlPgeRUoeyNpOAFuBXvXWVofsD",
				"openid":"o_57D5DcRw-r6SdRxF98ikhf5dLY",
				"out_trade_no":"7-1540693614",
				"result_code":"SUCCESS",
				"return_code":"SUCCESS",
				"sign":"2B480C75338FCDE3972DF21AC7CC7596",
				"time_end":"20181028102711",
				"total_fee":"1",
				"trade_type":"JSAPI",
				"transaction_id":"4200000236201810287313447913"
			}
			**/



			$total_fee = $data['total_fee'];
			$transaction_id = $data['transaction_id'];
			$out_trade_no_arr =  explode('-',$data['out_trade_no']);
			$out_trade_no = $out_trade_no_arr[0];

			if( isset($out_trade_no_arr[2]) && $out_trade_no_arr[2] == 'charge' )
			{
				//暂时屏蔽会员充值代码

				$member_charge_flow_info = M('eaterplanet_ecommerce_member_charge_flow')->where( array('id' => $out_trade_no ) )->find();

				if(!empty($member_charge_flow_info) && $member_charge_flow_info['state'] == 0)
				{
					$charge_flow_data = array();
					$charge_flow_data['trans_id'] = $transaction_id;
					$charge_flow_data['state'] = 1;
					$charge_flow_data['charge_time'] = time();

					M('eaterplanet_ecommerce_member_charge_flow')->where( array('id' => $out_trade_no) )->save( $charge_flow_data );

					if( !empty($member_charge_flow_info['give_money']) && $member_charge_flow_info['give_money'] > 0 )
					{
						$member_charge_flow_info['money'] += $member_charge_flow_info['give_money'];
					}


					M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_charge_flow_info['member_id'] ) )->setInc('account_money', $member_charge_flow_info['money'] );


					$mb_info = M('eaterplanet_ecommerce_member')->field('account_money')->where( array('member_id' => $member_charge_flow_info['member_id']) )->find();
					if( empty($mb_info['account_money']) )
					{
						$mb_info['account_money'] = 0;
					}
					M('eaterplanet_ecommerce_member_charge_flow')->where( array('id' => $out_trade_no) )->save(  array('operate_end_yuer' => $mb_info['account_money']) );


					$mb_info = M('eaterplanet_ecommerce_member')->field('account_money')->where( array('member_id' => $member_charge_flow_info['member_id']) )->find();

					M('eaterplanet_ecommerce_member_charge_flow')->where( array('id' => $out_trade_no) )->save( array('operate_end_yuer' => $mb_info['account_money']) );


					for($i=0;$i<3;$i++)
					{
						$member_formid_data = array();
						$member_formid_data['member_id'] = $member_charge_flow_info['member_id'];
						$member_formid_data['state'] = 0;
						$member_formid_data['formid'] = $member_charge_flow_info['formid'];
						$member_formid_data['addtime'] = time();

						M('eaterplanet_ecommerce_member_formid')->add($member_formid_data);
					}

				}

			}
			else if(  isset($out_trade_no_arr[2]) && $out_trade_no_arr[2] == 'buycard' )
			{
				//购买会员卡代码
				$member_charge_flow_info = M('eaterplanet_ecommerce_member_card_order')->where( array('id' => $out_trade_no ) )->find();

				$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_charge_flow_info['member_id'] ) )->find();

				if(!empty($member_charge_flow_info) && $member_charge_flow_info['state'] == 0)
				{
					$begin_time = 0;
					$end_time = 0;

					if($member_charge_flow_info['order_type'] == 1)
					{
						//首次购买
						$begin_time = time();
						$end_time = $begin_time + 86400 * $member_charge_flow_info['expire_day'];

					}else if($member_charge_flow_info['order_type'] == 2)
					{
						//有效期内续期
						$begin_time = $member_info['card_end_time'];
						$end_time = $begin_time + 86400 * $member_charge_flow_info['expire_day'];
					}else if($member_charge_flow_info['order_type'] == 3)
					{
						//过期后续费
						$begin_time = time();
						$end_time = $begin_time + 86400 * $member_charge_flow_info['expire_day'];
					}

					$charge_flow_data = array();
					$charge_flow_data['trans_id'] = $transaction_id;
					$charge_flow_data['state'] = 1;
					$charge_flow_data['pay_time'] = time();
					$charge_flow_data['begin_time'] = $begin_time;
					$charge_flow_data['end_time'] = $end_time;
					$charge_flow_data['state'] = 1;

					M('eaterplanet_ecommerce_member_card_order')->where( array('id' => $out_trade_no ) )->save( $charge_flow_data );

					$mb_up_data = array();
					$mb_up_data['card_id'] = $member_charge_flow_info['car_id'];
					$mb_up_data['card_begin_time'] = $begin_time;
					$mb_up_data['card_end_time'] = $end_time;

					M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_charge_flow_info['member_id']  ) )->save( $mb_up_data );

					for($i=0;$i<3;$i++)
					{
						$member_formid_data = array();
						$member_formid_data['member_id'] = $member_charge_flow_info['member_id'];
						$member_formid_data['state'] = 0;
						$member_formid_data['formid'] = $member_charge_flow_info['formid'];
						$member_formid_data['addtime'] = time();

						M('eaterplanet_ecommerce_member_formid')->add($member_formid_data);
					}


				}
			}
			else{
				//

				$order_all = M('eaterplanet_ecommerce_order_all')->where( array('id' => $out_trade_no ) )->find();

				if( in_array($order_all['order_status_id'], array(1,2)) ){

					$stop_pay = false;

					$order_relate_list = M('eaterplanet_ecommerce_order_relate')->where( array('order_all_id' => $order_all['id'] ) )->select();

					foreach($order_relate_list as $order_relate)
					{
						$order = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_relate['order_id'] ) )->find();

						if( $order && ($order['order_status_id'] != 3 && $order['order_status_id'] == 5) )
						{
							$stop_pay = true;
						}
					}

					if( $stop_pay )
					{
						$msg = "付款成功";
						return true;
					}
				}

				$o = array();
				$o['order_status_id'] =  $order_all['is_pin'] == 1 ? 2:1;
				$o['paytime']=time();
				$o['transaction_id'] = $transaction_id;

				M('eaterplanet_ecommerce_order_all')->where( array('id' => $out_trade_no) )->save($o);

				$order_relate_list = M('eaterplanet_ecommerce_order_relate')->where( array('order_all_id' => $order_all['id']) )->select();

				//1
					$data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
					RecursiveMkdir($data_path);

					$file = $data_path.date('Y-m-d').'.txt';
					$handl = fopen($file,'a');
					fwrite($handl,"关联--");
					fwrite($handl,"关联");
					fwrite($handl,":".json_encode($order_relate_list));
					fclose($handl);


				foreach($order_relate_list as $order_relate)
				{
					$order = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_relate['order_id'] ) )->find();

					if( $order && ($order['order_status_id'] == 3 || $order['order_status_id'] == 5) )
					{
						$o = array();
						$o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;
						$o['date_modified']=time();
						$o['pay_time']=time();
						$o['payment_code']='weixin';
						$o['transaction_id'] = $transaction_id;

						if($order['delivery'] == 'hexiao'){//核销订单 支付完成状态改成  已发货待收货
							$o['order_status_id'] =  4;
						}

						M('eaterplanet_ecommerce_order')->where( array('order_id' => $order['order_id'] ) )->save( $o );

						//暂时屏蔽库存代码

						$kucun_method =  D('Home/Front')->get_config_by_name('kucun_method');

						if( empty($kucun_method) )
						{
							$kucun_method = 0;
						}

						//kucun_method $_W['uniacid']

						if($kucun_method == 1)
						{//支付完减库存，增加销量

							$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order['order_id']) )->select();

							foreach($order_goods_list as $order_goods)
							{
								D('Home/Pingoods')->del_goods_mult_option_quantity($order['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],1);


							}
						}

						$oh = array();
						$oh['order_id']=$order['order_id'];
						$oh['uniacid']=$order['uniacid'];
						$oh['order_status_id']= $order['is_pin'] == 1 ? 2:1;

						$oh['comment']='买家已付款';
						$oh['date_added']=time();
						$oh['notify']=1;

						if($order['delivery'] == 'hexiao'){//核销订单 支付完成状态改成  已发货待收货
							$oh['order_status_id'] =  4;
						}

						M('eaterplanet_ecommerce_order_history')->add($oh);


						//订单自动配送
						D('Home/Order')->order_auto_delivery($order);

						//$weixin_nofity->orderBuy($order['order_id']);

						//$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
						//$order_id = $hashids->encode($order['order_id']);
						//group_order_id

						if($order['is_pin'] == 1)
						{
							$pin_order = M('eaterplanet_ecommerce_pin_order')->where( array('order_id' => $order['order_id'] ) )->find();

							D('Home/Pin')->insertNotifyOrder($order['order_id']);

							//检测拼团是否已经成功了。如果已经成功了。那么重新开团，并且迁移掉目前的这个拼团订单到新的团去
							//state

							$pin_info = M('eaterplanet_ecommerce_pin')->where(array('pin_id' => $pin_order['pin_id'])  )->find();//加锁查询

							$pin_buy_count = D('Home/Pin')->get_tuan_buy_count($pin_order['pin_id']);


							$res = D('Seller/Redisorder')->add_pintuan_user( $pin_order['pin_id'] );


							if( $pin_info['state']  == 1 && !$res )
							{
							    $order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order['order_id']) )->find();

							    M('eaterplanet_ecommerce_pin_order')->where( array('pin_id' =>$pin_order['pin_id'],'order_id' => $order['order_id'] ) )->delete();

							    $newpin_id = D('Home/Pin')->openNewTuan($order['order_id'],$order_goods_info['goods_id'],$order['member_id']);
							    //插入拼团订单
							    D('Home/Pin')->insertTuanOrder($newpin_id,$order['order_id']);
							    unset($pin_info);

							    $is_pin_success = D('Home/Pin')->checkPinSuccess($newpin_id);

							    if($is_pin_success) {
							        D('Home/Pin')->updatePintuanSuccess($newpin_id);
							    }
							}else{
							    $is_pin_success = D('Home/Pin')->checkPinSuccess($pin_order['pin_id']);

							    if($is_pin_success) {
							        D('Home/Pin')->updatePintuanSuccess($pin_order['pin_id']);
							    }
							}



						}

						//发送购买通知
						D('Home/Weixinnotify')->orderBuy($order['order_id']);


					}

				}


			}






			//$order=M('Order')->getByOrderNumAlias($out_trade_no);




			return true;
		}
	}
}

