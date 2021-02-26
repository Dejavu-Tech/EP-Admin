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
namespace Seller\Controller;

class CronController extends \Think\Controller {

	/**
		检测退款失败订单
	**/
	public function check_refund_faild_pintuan()
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
		\Log::DEBUG("检测退款失败订单开始:" );

		$weixin_model = D('Home/Weixin');
		$refund_orderlist = M('Order')->where( array('order_status_id' => 10, 'total' => array('gt',0)) )->order('pay_time asc')->limit(10)->select();


		foreach($refund_orderlist as $order)
		{
			$input = new \WxPayRefund();
			$input->SetTransaction_id($order['transaction_id']);
			$res = \WxPayApi::refundQuery($input);
			if($res['return_code'] == 'SUCCESS')
			{
				//查询得到退款记录
				$refund_count = $res['refund_count'];
				$flag = true;
				for($i=0;$i<$refund_count;$i++)
				{
					if($res['refund_status_'.$i] != 'SUCCESS')
					{
						$flag  = false;
					}
				}
				if($flag)
				{
					$member_info = M('member')->where( array('member_id' => $order['member_id']) )->find();
					//微信后台自动退款成功了。
					$weixin_model->refundOrder_success($order,$member_info['openid']);
					\Log::DEBUG("订单号：".$order['order_id'].'  微信后台自动退款成功，检测完，更新订单状态并发送消息' );
				}else {
					var_dump($res);die();
				}
			}
		}
		echo 'check weixin auto refund success';
	}


	public function test()
	{
		echo 3;
		die();
	}

	/**
		检测订单退款状态是否正常
	**/
	public function check_pintuan_refundstate()
	{
		$sql = "SELECT p.pin_id,o.order_id,o.total,o.transaction_id from ".C('DB_PREFIX')."pin as p, ".C('DB_PREFIX')."pin_order as po,
				".C('DB_PREFIX')."order as o where p.pin_id=po.pin_id
				and po.order_id = o.order_id and p.state =2 and  o.order_status_id=2 limit 30";

		$order_list = M()->query($sql);

		foreach($order_list as $val)
		{
			M('pin')->where( array('pin_id' => $val['pin_id']) )->save( array('state' => 0) );
		}

		//C('DB_PREFIX')
	}


	/**
	 * 退款正常商品时钟
	 */
	public function refundPintuan()
	{


		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
		$data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
		RecursiveMkdir($data_path);
		set_time_limit(0);

		require_once $lib_path."/Weixin/log.php";

		//初始化日志
		$logHandler= new \CLogFileHandler( $data_path .date('Y-m-d').'.log');

		\Log::Init($logHandler, 15);
		\Log::DEBUG("退款进入:" );

		$daytimenow_ev56_s = time();
		$condition = " state=0 and end_time < ".$daytimenow_ev56_s;

		$pin_list = M('pin')->where( $condition )->order('pin_id asc ')->limit(10)->select();



		$weixin_model = D('Home/Weixin');

		if(!empty($pin_list))  {
			foreach($pin_list as $pin)
			{
				M('pin')->where(array('pin_id' => $pin['pin_id']))->save( array('state' => 2) );

				$pin_order_list = M('pin_order')->where( array('pin_id' =>$pin['pin_id'] ) )->select();

				$order_ids = array();
				foreach($pin_order_list as $vv)
				{
					$order_ids[] = $vv['order_id'];
				}

				$order_list = M('order')->where( array('order_id' => array('in',$order_ids) ,'order_status_id' => 2) )->select();


				foreach($order_list as $order)
				{

				    \Log::DEBUG("退款订单ID: ".$order['order_id'] );
					$weixin_model->refundOrder($order['order_id']);
					//echo 333;die();
				}
			}

		}
		\Log::DEBUG("退款完毕:".date('Y-m-d H:i:s') );
		$this->auto_opne_lottery();
		template_msg_cron();//群发消息
		echo '退款成功';

		//$order_model = D('Home/Order');

		//$order_model->open_goods_lottery_order($goods_id,'',true);
	}

	/**
		活动过期商品自动下架
	**/
	public function auto_getdown_activity()
	{
		$now_time = time();

		/**
		$subject_goods_list = M('subject_goods')->where("end_time<".$now_time)->select();


		foreach($subject_goods_list as $subject_goods)
		{
			$up_data = array();
			$up_data['type'] = 'normal';
			$up_data['lock_type'] = 'normal';
			//$up_data['status'] = 0;//下架

			M('goods')->where( array('goods_id' => $subject_goods['goods_id']) )->save($up_data);
		}
		M('subject_goods')->where("end_time<".$now_time)->delete();

		$spike_goods_list = M('spike_goods')->where("end_time<".$now_time)->select();

		foreach($spike_goods_list as $spike_goods)
		{
			$up_data = array();
			$up_data['type'] = 'normal';
			$up_data['lock_type'] = 'normal';
			//$up_data['status'] = 0;//下架

			M('goods')->where( array('goods_id' => $spike_goods['goods_id']) )->save($up_data);
		}

		M('spike_goods')->where("end_time<".$now_time)->delete();



		$super_spike_goods_list = M('super_spike_goods')->where("end_time<".$now_time)->select();

		foreach($super_spike_goods_list as $super_spike_goods)
		{
			$up_data = array();
			$up_data['type'] = 'normal';
			$up_data['lock_type'] = 'normal';
			//$up_data['status'] = 0;//下架

			M('goods')->where( array('goods_id' => $super_spike_goods['goods_id']) )->save($up_data);
		}

		M('super_spike_goods')->where("end_time<".$now_time)->delete();

		**/
		$expr_day_info = M('config')->field('value')->where( array('name' => 'expr_day') )->find();

		$end_time = $now_time;
		if(!empty($expr_day_info) && $expr_day_info['value'] > 0)
		{
			$end_time += 86400 * $expr_day_info['value'];
		}else {

			$end_time += 86400 * 7;
		}

		$lottery_goods_list = M('lottery_goods')->where("is_open_lottery = 1 and  end_time>=".$end_time)->select();



		foreach($lottery_goods_list as $lottery_goods)
		{
			$goods_info = M('goods')->where( array('goods_id' => $lottery_goods['goods_id'] ) )->find();

			if($goods_info['type'] == 'lottery')
			{
				$up_data = array();
				$up_data['type'] = 'normal';
				$up_data['lock_type'] = 'normal';
				$up_data['status'] = 0;//下架

				M('goods')->where( array('goods_id' => $lottery_goods['goods_id']) )->save($up_data);
			}
		}

		M('lottery_goods')->where(" is_open_lottery = 1 and end_time>=".$end_time)->delete();


		echo '过期活动商品下架成功';

	}

	/**
	 * 自动开奖
	 */
	public function auto_opne_lottery()
	{
		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
	    $data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
	    RecursiveMkdir($data_path);
	    set_time_limit(0);

	    require_once $lib_path."/Weixin/log.php";
	    //初始化日志
	    $logHandler= new \CLogFileHandler( $data_path .date('Y-m-d').'.log');

	    \Log::Init($logHandler, 15);
	    \Log::DEBUG("自动开奖进入:" );

	   $order_model = D('Home/Order');

	   $lottery_goods_list =  M('lottery_goods')->where("is_open_lottery=0 and is_auto_open =  1 and end_time <".time())->limit(5)->select();

	   foreach($lottery_goods_list as $lottery_goods)
	   {

		    \Log::DEBUG("自动开奖商品id:".$lottery_goods['goods_id'] );
	       $order_model->open_goods_lottery_order($lottery_goods['goods_id'],'',true);
	   }

	    \Log::DEBUG("自动开奖结束:".date('Y-m-d H:i:s') );
		echo '自动开奖成功';
	}

	/**
	 * 退款抽奖商品时钟
	 */
	public function refundLotteryPintuan()
	{
	    $lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
	    $data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
	    RecursiveMkdir($data_path);
	    set_time_limit(0);

	    require_once $lib_path."/Weixin/log.php";
	    //初始化日志
	    $logHandler= new \CLogFileHandler( $data_path .date('Y-m-d').'.log');

	    \Log::Init($logHandler, 15);
	    \Log::DEBUG("抽奖退款进入:" );

	    $daytimenow_ev56_s = time();
	    $condition = " state=1 and is_lottery =1 and lottery_state = 1";

	    $pin_list = M('pin')->where( $condition )->order('pin_id asc ')->limit(10)->select();

	    $config_info = M('config')->where( array('name' => 'SITE_URL') )->find();

	    $weixin_model = D('Home/Weixin');
	    $voucher_model = D('Home/Voucher');
	    if(!empty($pin_list))  {
	        foreach($pin_list as $pin)
	        {


				$sql = "select o.* from ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."pin_order as p
				where o.order_id = p.order_id and o.order_status_id = 1 and p.pin_id = ".$pin['pin_id'];

				$order_list = M()->query($sql);

	            M('pin')->where(array('pin_id' => $pin['pin_id']))->save( array('lottery_state' => 2) );
	           // return false;
				//$order_list = M('order')->where( array('pin_id' =>$pin['pin_id'],'order_status_id' => 1) )->select();

	            foreach($order_list as $order)
	            {
	                if($order['lottery_win'] == 0)
	                {
	                    //中2等奖
	                    $weixin_model->refundOrder($order['order_id']);
	                    $order_goods = M('order_goods')->where( array('order_id' => $order['order_id']) )->find();
	                    if(!empty($order_goods)) {
	                        $lottery_goods = M('lottery_goods')->where( array('goods_id' => $order_goods['goods_id']) )->find();
	                        if(!empty($lottery_goods) && !empty($lottery_goods['voucher_id'])){

								//赠送指定的券
	                            $res = $voucher_model->send_user_voucher_byId($lottery_goods['voucher_id'],$order['member_id']);

	                           if($res == 3)
	                           {
	                               //赠送券成功，发送通知
	                               $member_info = M('member')->where( array('member_id' => $order['member_id']) )->find();

	                               $openId = $member_info['openid'];
	                               $url = $config_info['value'].'/'."/index.php?s=/User/index.html";

	                               $voucher = M('voucher')->where( array('id' => $lottery_goods['voucher_id']) )->find();
	                               $title = "恭喜您中了二等奖，获得{$voucher[credit]}元代金券";
	                               $msg = "亲爱的会员，恭喜您参加 ".$order_goods['name']." 拼团成功，获得{$voucher[credit]}元代金券，立即点击查收哈 ";

								    \Log::DEBUG("退款送券:".$openId.'  '.$msg.'  '.$title.'  '.$url );


								   M('order')->where( array('order_id' => $order['order_id']) )->save( array('order_status_id' => 9) );

									if( $order['from_type'] == 'wepro' )
									{
										$template_data = array();
										$template_data['keyword1'] = array('value' => $title, 'color' => '#030303');
										$template_data['keyword2'] = array('value' => $msg, 'color' => '#030303');

										$pay_order_msg_info =  M('config')->where( array('name' => 'weprogram_template_lottery_result') )->find();
										$template_id = $pay_order_msg_info['value'];

										$pagepath = 'pages/dan/quan';

										//$order['member_id']
										/**
										$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'], 'state' => 0) )->find();

										send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);
										//更新
										M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );

										$order_info['member_id']
										**/
										$member_formid_info = M('member_formid')->where( array('member_id' => $order['member_id'],'formid' =>array('neq',''), 'state' => 0) )->order('id desc')->find();

										if(!empty($member_formid_info))
										{
											$rs = send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);
											M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
										}


										if( $openId != '1')
										{
											notify_weixin_msg($openId,$msg,$title,$url);
										}
									} else {
										 notify_weixin_msg($openId,$msg,$title,$url);
									}
							   }
	                        }
	                    }
	                } else if($order['lottery_win'] == 1){
	                    //中了一等奖
	                    $order_goods = M('order_goods')->where( array('order_id' => $order['order_id']) )->find();
	                    \Log::DEBUG("发送一等奖中奖通知:".$openId.'  '.$msg.'  '.$title.'  '.$url );
	                    $member_info = M('member')->where( array('member_id' => $order['member_id']) )->find();

	                    $openId = $member_info['openid'];
	                    $url = $config_info['value'].'/'."/index.php?s=/Order/info/id/".$order['order_id'].".html";

	                    $title = "恭喜您中了一等奖";
	                    $msg = "亲爱的会员，恭喜您参加 ".$order_goods['name']." 拼团成功，您中奖了。请点击查看！ ";

						if( $order['from_type'] == 'wepro' )
						{
							$template_data = array();
							$template_data['keyword1'] = array('value' => $title, 'color' => '#030303');
							$template_data['keyword2'] = array('value' => $msg, 'color' => '#030303');

							$pay_order_msg_info =  M('config')->where( array('name' => 'weprogram_template_lottery_result') )->find();
							$template_id = $pay_order_msg_info['value'];


							$pagepath = 'pages/order/order?id='.$order['order_id'];

							/**
							$order['member_id']
								$member_formid_info = M('member_formid')->where( array('member_id' => $order_info['member_id'], 'state' => 0) )->find();

								send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);
								//更新
								M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );

								$order_info['member_id']
							**/
							$member_formid_info = M('member_formid')->where( array('member_id' => $order['member_id'],'formid' =>array('neq',''), 'state' => 0) )->order('id desc')->find();

							if(!empty($member_formid_info))
							{
								$rs = send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid']);

								M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
							}


							if( $openId != '1')
							{
								notify_weixin_msg($openId,$msg,$title,$url);
							}
						} else {
							notify_weixin_msg($openId,$msg,$title,$url);
						}

	                }
	            }
	        }
	    }
	    \Log::DEBUG("抽奖退款完毕:".date('Y-m-d H:i:s') );
	    echo '抽奖退款成功';
	}

	/**
	 * 商家结算
	 */
	public function balanceStore()
	{
	    $lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
	    $data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
	    RecursiveMkdir($data_path);
	    set_time_limit(0);

	    require_once $lib_path."/Weixin/log.php";
	    //初始化日志
	    $logHandler= new \CLogFileHandler( $data_path .date('Y-m-d').'.log');

	    \Log::Init($logHandler, 15);
	    \Log::DEBUG("商家结算进入:".date('Y-m-d H:i:s') );
	    // $data['balance_time'] = strtotime(date('Y-m-d'));

	    $has_date_info =  M('balance')->where( array('balance_time' => strtotime(date('Y-m-d')) ) )->find();


	    if($has_date_info) {
	        \Log::DEBUG("商家已结算强制结束:".date('Y-m-d H:i:s') );
	        die();
	    }
	    $seller_list = M('seller')->where(array('s_status' =>1) )->select();


	    foreach($seller_list as $seller)
	    {
	        $sql = "select o.order_id,o.total,gtc.class_id1,gtc.class_id2,gtc.class_id3 from ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og,
	            ".C('DB_PREFIX')."goods_to_category as gtc
	         where o.order_id = og.order_id and gtc.goods_id = og.goods_id and o.store_id = ".$seller['s_id']."
	             and o.is_balance = 0 and o.order_status_id in (6,11) ";
	        $order_list = M()->query($sql);



	        //获取商家所有绑定类目
	       $store_bind_class = M('store_bind_class')->where( array('seller_id' => $seller['s_id']) )->select();

	       $class_rate_arr = array();
	       foreach($store_bind_class as $bind_class)
	       {
	           $key = $bind_class['class_1'].'_'.$bind_class['class_2'].'_'.$bind_class['class_3'];
	           $class_rate_arr[$key] = $bind_class['commis_rate'];
	       }

	       $data = array();
	       $data['seller_id'] = $seller['s_id'];
	       $data['money'] = 0;
	       $data['redusmoney'] = 0;
	       $data['balance_time'] = strtotime(date('Y-m-d'));
	       $data['state'] = 2;
	       $data['addtime'] = time();

	       $res = M('balance')->add($data);
	       $bid = M('balance')->getLastInsID();
	       $tongji_money = 0;
	       $total_reduce_money = 0;

	       foreach($order_list as $order)
	       {
	           $reduce_money = 0;
	           $del_moeny = 0;
	           //$fkey = $order['class_id1'].'_'.$order['class_id2'].'_'.$order['class_id3'];
	           //只按照一级的类目进行计算
	           $fkey = $order['class_id1'].'_0_0';
	           $reduce_money = $order['total'] * $class_rate_arr[$fkey] * 0.01;


				$member_commiss_order_list = M('member_commiss_order')->where( array('order_id' =>$order['order_id'],'state' => 0 ) )->select();

				if(!empty($member_commiss_order_list))
				{
				   foreach($member_commiss_order_list as $member_commiss_order)
				   {
					   //分佣订单
					   $reduce_money += $member_commiss_order['money'];
					   M('member_commiss_order')->where( array('id' =>$member_commiss_order['id'] ) )->save( array('state' => 1) );
					   M('member_commiss')->where( array('member_id' => $member_commiss_order['member_id']) )->setInc('money',$member_commiss_order['money']);
					 }
				}


			   $del_moeny =  $order['total'] - $reduce_money;

	           $order_data = array();
	           $order_data['bid'] = $bid;
	           $order_data['order_id'] = $order['order_id'];
	           $order_data['seller_id'] = $seller['s_id'];
	           $order_data['money'] = $del_moeny;
	           $order_data['redusmoney'] = $reduce_money;
	           $order_data['addtime'] = time();

	           M('balance_order')->add($order_data);
	           M('order')->where( array('order_id' => $order['order_id']))->save( array('is_balance' => 1)  );

			    $order_history = array();
				$order_history['order_id'] = $order['order_id'];
				$order_history['order_status_id'] = 11;
				$order_history['notify'] = 0;
				$order_history['comment'] = '订单时钟结算';
				$order_history['date_added']=time();
				M('order_history')->add($order_history);

	           $tongji_money += $del_moeny;
	           $total_reduce_money += $reduce_money;
	       }

	       M('balance')->where( array('bid' => $bid) )->save( array('money' => $tongji_money,'redusmoney' => $total_reduce_money) );

	       $seller_balance = M('seller_balance')->where( array('seller_id' => $seller['s_id']) )->find();

	       if(empty($seller_balance)) {
	           $data = array();
	           $data['money']   =  $tongji_money;
	           $data['seller_id'] = $seller['s_id'];
	           $data['hasgetmoney'] = 0;
	           $data['dongmoney'] = 0;
	           M('seller_balance')->add($data);
	       } else {
	           $data = array();
	           $data['money']   =  $tongji_money+ $seller_balance['money'];
	           M('seller_balance')->where( array('seller_id' => $seller['s_id']) )->save($data);
	       }
	    }
	    \Log::DEBUG("商家结算完毕:".date('Y-m-d H:i:s') );
	    echo '商家结算完毕';

		$this->check_pintuan_refundstate();
	}

	/**
	申请退款的商品，如果5天，卖家没有处理，那么就退款
	**/
	public function auto_shenrefund_order()
	{
		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
	    $data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
	    RecursiveMkdir($data_path);
	    set_time_limit(0);

	    require_once $lib_path."/Weixin/log.php";
	    //初始化日志
	    $logHandler= new \CLogFileHandler( $data_path .date('Y-m-d').'.log');

	    \Log::Init($logHandler, 15);
	    \Log::DEBUG("申请退款自动退款进入:".date('Y-m-d H:i:s') );

		$default_day = 3;

		$config_info = M('config')->where( array('name' => 'autoshenrefundday') )->find();

		if(!empty($config_info) && intval($config_info['value']) > 0)
		{
			$default_day = intval($config_info['value']);
		}

	    $end_time = time() - $default_day * 86400;

		$sql = "select o.order_id,orf.ref_money,orf.state from ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_refund as orf
	         where o.order_id = orf.order_id and orf.state =0 and  o.order_status_id in (12) and orf.addtime <".$end_time;
	    $order_list = M()->query($sql);


		foreach($order_list as $order)
		{

			$order_refund_history = array();
			$order_refund_history['order_id'] = $order['order_id'];
			$order_refund_history['message'] = '商家未及时处理，自动退款';
			$order_refund_history['type'] = 3;
			$order_refund_history['addtime'] = time();

			M('order_refund_history')->add($order_refund_history);

			$order_history = array();
			$order_history['order_id'] = $order_id;
			$order_history['order_status_id'] = 7;
			$order_history['notify'] = 0;
			$order_history['comment'] = '商家未及时处理，自动退款';
			$order_history['date_added'] = time();
			M('order_history')->add($order_history);

			$weixin_model = D('Home/Weixin');
			//通过
			M('order_refund')->where( array('order_id' => $order['order_id']) )->save( array('state' => 3) );
			$weixin_model->refundOrder($order['order_id'],$order['ref_money']);
		}
		  \Log::DEBUG("申请退款自动退款完毕:".date('Y-m-d H:i:s') );
	    echo '申请退款自动退款成功';

	}

	/**
	 * 15天自动确认收货
	 */
	public function autoGetPack()
	{

	    $lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
	    $data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
	    RecursiveMkdir($data_path);
	    set_time_limit(0);

	    require_once $lib_path."/Weixin/log.php";
	    //初始化日志
	    $logHandler= new \CLogFileHandler( $data_path .date('Y-m-d').'.log');

	    \Log::Init($logHandler, 15);
	    \Log::DEBUG("自动确认收货进入:".date('Y-m-d H:i:s') );

		$default_day = 15;

		$config_info = M('config')->where( array('name' => 'autogetday') )->find();

		if(!empty($config_info) && intval($config_info['value']) > 0)
		{
			$default_day = intval($config_info['value']);
		}

		$ziti_shouhuo = 1;
		$ziti_config_info = M('config')->where( array('name' => 'ziti_shouhuo') )->find();

		if(!empty($ziti_config_info) )
		{
			$ziti_shouhuo = intval($ziti_config_info['value']);
		}

	    $end_time = time() - $default_day * 86400;
	    $condition = "";
	    $condition = "order_status_id=4 and pay_time <".$end_time;
		if($ziti_shouhuo == 0)
		{
			//自提不自动确认收货
			//delivery pickup
			$condition .= "  and delivery != 'pickup' ";

		}
	    $order_list = M('order')->where($condition)->select();


	    $oid_arr = array();
	    foreach($order_list as $order)
	    {
	        $oid_arr[] = $order['order_id'];
	    }

	    if(!empty($oid_arr)) {
	        $oid_str = implode(',',$oid_arr);

	        $res = M('order')->where( array('order_id' => array('in',$oid_str)) )->save( array('order_status_id' => 6) );

			$integral_model = D('Seller/Integral');
			$fenxiao_model = D('Home/Fenxiao');
			$share_model = D('Seller/Fissionsharing');


			foreach($oid_arr as $order_id)
			{
				$order_history = array();
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 6;
				$order_history['notify'] = 0;
				$order_history['comment'] = '自动确认收货,超时系统签';
				$order_history['date_added']=time();
				M('order_history')->add($order_history);

				$integral_model->send_order_score_dr($order_id);
				$fenxiao_model->send_order_commiss_money($order_id);
				$share_model->send_order_commiss_money_do( $order_id);
			}
	    }

	    \Log::DEBUG("用户自动收货完毕:".date('Y-m-d H:i:s') );
	    echo '自动收货成功';
		$this->auto_shenrefund_order();
		$this->auto_getdown_activity();
		$this->check_refund_faild_pintuan();
	}


}
