<?php
namespace Home\Model;
use Think\Model;
/**
 * 拼团模型模型
 * @author Albert.Z
 *
 */
class WeixinnotifyModel {

	public function orderBuy($order_id,$is_admin=false)
	{


		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();



		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();



		$order_notify_switch = D('Home/Front')->get_config_by_name('order_notify_switch');

		if( isset($order_notify_switch) && $order_notify_switch == 1 )
		{
		    $notify_order_list = S('notify_order_list');

		    if( empty($notify_order_list) || count($notify_order_list) < 100 )
		    {
		        if(empty($notify_order_list))
		        {
		            $notify_order_list = array();
		        }

		        $miao = 1;
		        $result_data = array();

		        $result_data['code'] = 0;
		        $result_data['username'] = $notify_od_data['username'];
		        $result_data['avatar'] 	= $notify_od_data['avatar'];
		        $result_data['order_id'] 	= $notify_od_data['order_id'];

		        $result_data['order_url'] 	= '';
		        $result_data['miao'] 	= $miao;

		        $notify_order_list[] = $result_data;

		        S('notify_order_list', $notify_order_list );
		    }

		}

		$order = $order_info;



		if($order['is_pin'] == 0)
		{
			//$share_model = load_model_class('commission');
			//$share_model->send_order_commiss_money( $order['order_id'] );

			//单独购买分佣
			$fenxiao_model = D('Home/Commission');//D('Home/Fenxiao');
			$community_model = D('Seller/Community');
			$supply_model = D('Seller/Supply');


			$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$order['order_id'] ) )->select();


			$order_goods_name = "";

			$i_count = count($order_goods_list);
			$shipping_money = 0;
			if($order['delivery'] == 'tuanz_send')
			{
				$shipping_money = $order['shipping_fare'];
			}

			$i =1;

			//sendMemberPointChange($member_id,$num, $changetype ,$remark ='', $uniacid = 0,$type='system_add', $order_id =0 ,$order_goods_id = 0)

			$open_buy_send_score = D('Home/Front')->get_config_by_name('open_buy_send_score');
			if( empty($open_buy_send_score) )
			{
				$open_buy_send_score = 0;
			}


			$is_take_commiss = 1;
            //礼品卡不参与分销时

            $virtualcard_result = D('Seller/VirtualCard')->getVirtualCardOrderInfO( $order['order_id'] );
            if( $virtualcard_result['code'] == 0 ) {

                $virtualcard_is_takecommiss = D('Home/Front')->get_config_by_name('virtualcard_is_takecommiss');
                if( !isset($virtualcard_is_takecommiss)  || $virtualcard_is_takecommiss == 0)
                {
                    $is_take_commiss = 0;
                }
            }
            //礼品卡不参与分销


			foreach($order_goods_list as $order_goods)
			{
				$order_goods_name .= $order_goods['name']." \r\n";

				if( $order_info['type'] != 'integral' && $is_take_commiss == 1 )
				{
					$fenxiao_model->ins_member_commiss_order($order['member_id'],$order['order_id'],$order_goods['store_id'],$order_goods['order_goods_id'] );
					//$community_model->ins_head_commiss_order($order['order_id'],$order_goods['order_goods_id'] );

					if($i == $i_count)
					{
						$community_model->ins_head_commiss_order($order['order_id'],$order_goods['order_goods_id'], $shipping_money);
					}else{
						$community_model->ins_head_commiss_order($order['order_id'],$order_goods['order_goods_id'], 0);
					}

					$supply_model->ins_supply_commiss_order($order['order_id'],$order_goods['order_goods_id'], 0);
					//改成下单新增商品今日销量
					//D('Seller/Commonorder')->inc_daygoods_buy( $order_goods['goods_id'], $order_goods['quantity'] );
				}

				$i++;
			}

			//下单加入缓存S中
            $day_time = strtotime( date('Y-m-d '.'00:00:00') );
            $day_key = 'new_ordernotice_'.$day_time;
            $day_arr = S( $day_key );

            if( empty($day_arr) )
            {
             $day_arr = array();
             $day_arr[] = $order['order_id'];
            }else{
             $day_arr[] = $order['order_id'];
            }

            S($day_key, $day_arr );



			if( $order_info['delivery'] == 'localtown_delivery' )
            {
                //开始启动配送D('Home/LocaltownDelivery')->change_distribution_order_state( $order['order_id'], 0, 1);
                $remark = '已付款，备货中';
                D('Home/LocaltownDelivery')->write_distribution_log( $order['order_id'], 0 , 0 ,$remark );

            }

			//发送小程序模板消息 : 订单  订单时间 商品名称  支付金额 温馨提示
			if( $order_info['from_type'] == 'wepro' )
			{
			    $this->sendOrderbuyMsg( $order_info , $order_goods_name , $is_admin ,$member_info);
			}

			//小票打印
			$is_print_auto = D('Home/Front')->get_config_by_name('is_print_auto');
			if(empty($is_print_auto) || $is_print_auto == 0 ){
				D('Seller/Printaction')->check_print_order($order['order_id']);
				D('Seller/Printaction')->check_print_order2($order['order_id']);

			}
			//send dan msg
		} else {

			$pin_model = D('Home/Pin');
			$is_tuanz = $pin_model->checkOrderIsTuanzhang($order['order_id']);

			$pin_order = M('eaterplanet_ecommerce_pin_order')->where( array('order_id' => $order_id ) )->find();

			$pin_info = M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id ) )->find();

			$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order['order_id'] ) )->find();

			if($is_tuanz) {
				//开团成功
				//member_id
				$opentuantitmsg = '开团成功';
				$opentuandescmsg = '恭喜开团成功!马上叫小伙伴来参团，组团成功才能享受优惠哦';

				$shop_domain = D('Home/Front')->get_config_by_name('shop_domain');

				$url =  $shop_domain;





			} else {
				//参团成功
				$tacktuantitmsg = '参团成功';
				$tacktuandescmsg = '恭喜参团成功!马上叫小伙伴来参团，组团成功才能享受优惠哦';

				$shop_domain = D('Home/Front')->get_config_by_name('shop_domain');


			}

		}



	}


    /**
     * @param $order_info
     * @param $order_goods_name
     * @param int $is_admin
     * @param $member_info
     */
	public function sendOrderbuyMsg( $order_info , $order_goods_name , $is_admin = 0, $member_info )
    {
        $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');

        $url =  $shop_domain;

        if( $order_info['from_type'] == 'wepro' )
        {

            $order = $order_info;

            $template_data = array();
            $template_data['keyword1'] = array('value' => $order_info['order_num_alias'], 'color' => '#030303');
            $template_data['keyword2'] = array('value' => date('Y-m-d H:i:s',$order_info['pay_time']), 'color' => '#030303');
            $template_data['keyword3'] = array('value' => $order_goods_name, 'color' => '#030303');

            if( $order_info['type'] == 'integral' )
            {
                $shipp_str  = "";
                if( $order_info['shipping_fare'] > 0 )
                {
                    $shipp_str = sprintf("%01.2f", $order_info['shipping_fare']);
                    $shipp_str .= '元+';

                    $shipp_str .=  sprintf("%01.2f", $order_info['total']);
                    $shipp_str .= '积分';
                }else{
                    $shipp_str =  sprintf("%01.2f", $order_info['total']);
                    $shipp_str .= '积分';
                }
                $template_data['keyword4'] = array('value' => $shipp_str , 'color' => '#030303');

            }else{
                if($order_info['is_localtown_free_shipping_fare'] == 1){
                    $order_info['total'] = $order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'] - $order_info['fare_shipping_free']+$order_info['packing_fare'];
                }else{
                    $order_info['total'] = $order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'] - $order_info['fare_shipping_free'] + $order_info['localtown_free_shipping_fare']+$order_info['packing_fare'];
                }

                //15 +0 -0 -0.55 -0
                if($order_info['total'] <= 0)
                {
                    $order_info['total'] = 0;
                }


                $template_data['keyword4'] = array('value' => sprintf("%01.2f", $order_info['total']), 'color' => '#030303');
            }

            $template_data['keyword5'] = array('value' => '你已支付成功，商家会尽快为你发货，请耐心等待哦', 'color' => '#030303');



            $template_id = D('Home/Front')->get_config_by_name('weprogram_template_pay_order' );

            if($order_info['delivery'] == 'hexiao'){
                $pagepath = 'eaterplanet_ecommerce/pages/order/order?id='.$order['order_id']."&delivery=hexiao";
            }
            else if( $order_info['delivery'] == 'pickup' )
            {
                $pagepath = 'eaterplanet_ecommerce/pages/order/order?id='.$order_info['order_id']."&delivery=pickup";
            }
            else
                {
                $pagepath = 'eaterplanet_ecommerce/pages/order/order?id='.$order['order_id'];
            }


            $mb_subscribe = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $order['member_id'] , 'type' => 'pay_order') )->find();


            if( !empty($mb_subscribe) )
            {
                $template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_pay_order');

                $order_goods_name2 = mb_substr($order_goods_name,0,20,'utf-8');

                $order_goods_name2 = mb_substr( $order_goods_name2,0,10,'utf-8');

                $template_data = array();
                $template_data['character_string1'] = array('value' => $order_info['order_num_alias'] );
                $template_data['date2'] = array('value' => date('Y-m-d H:i:s') );
                $template_data['thing3'] = array('value' => $order_goods_name2 );
                $template_data['amount4'] = array('value' => sprintf("%01.2f", $order_info['total']) );
                $template_data['thing7'] = array('value' => '商家会尽快为你发货，请耐心等待哦' );

                if( $order_info['order_status_id'] == 15 )
                {
                    //预售第一次付款
                    $template_data['thing7'] = array('value' => '预售订单下单成功，待支付尾款!' );
                }


                D('Seller/User')->send_subscript_msg( $template_data,$url,$pagepath,$member_info['we_openid'],$template_id );

                M('eaterplanet_ecommerce_subscribe')->where( array('id' => $mb_subscribe['id'] ) )->delete();

            }


            $wx_template_data = array();
            $weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
            $weixin_template_pay_order = D('Home/Front')->get_config_by_name('weixin_template_pay_order');

            if( !empty($weixin_appid) && !empty($weixin_template_pay_order) )
            {
                $remark = '商家会尽快为你发货，请耐心等待哦';

                if( $order_info['order_status_id'] == 15 )
                {
                    //预售第一次付款
                    $remark = '预售订单下单成功，待支付尾款!';
                }

                $wx_template_data = array(
                    'appid' => $weixin_appid,
                    'template_id' => $weixin_template_pay_order,
                    'pagepath' => $pagepath,
                    'data' => array(
                        'first' => array('value' => '你已支付成功.>>查看订单详情','color' => '#030303'),
                        'keyword1' => array('value' => $member_info['username'],'color' => '#030303'),
                        'keyword2' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),
                        'keyword3' => array('value' => sprintf("%01.2f", $order_info['total']),'color' => '#030303'),
                        'keyword4' => array('value' => $order_goods_name,'color' => '#030303'),
                        'remark' => array('value' => $remark ,'color' => '#030303'),
                    )
                );
            }


            $delay_time = 0;

            if( !$is_admin )
            {
                $delay_time = 1;
            }

            D('Seller/User')->send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,'',0,$wx_template_data,$delay_time);


            //客户下单成功发送公众号提醒给客户
/*
            $rember_send_info =array();
            $weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
            $weixin_template_order_buy = D('Home/Front')->get_config_by_name('weixin_template_order_buy');




            if( !empty($weixin_appid) && !empty($weixin_template_order_buy) )
            {
                $head_pathinfo = "eaterplanet_ecommerce/moduleA/groupCenter/groupDetail?groupOrderId=".$order['order_id'];

                $first = $member_info['username'].'您好，您已支付成功.商家会尽快为你发货，请耐心等待哦';

                if( $order_info['order_status_id'] == 15 )
                {
                    //预售第一次付款
                    $first = array('value' => '您好，您已支付成功!' );
                }

                $rember_send_info = array(
                    'appid' => $weixin_appid,
                    'template_id' => $weixin_template_order_buy,
                    'pagepath' => $head_pathinfo,
                    'data' => array(
                        'first' => array('value' => $first ,'color' => '#030303'),
                        'tradeDateTime' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
                        'orderType' => array('value' => '用户购买','color' => '#030303'),
                        'customerInfo' => array('value' => $member_info['username'],'color' => '#030303'),
                        'orderItemName' => array('value' => '订单编号','color' => '#030303'),

                        'orderItemData' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),

                        'remark' => array('value' => '点击查看订单详情','color' => '#030303'),
                    )
                );
            }


            $weopenid = M('eaterplanet_ecommerce_member')->field('we_openid')->where( array('member_id' => $member_info['member_id'] ) )->find();

            $mnzember_formid_info = M('eaterplanet_ecommerce_member_formid')->where( "member_id=". $member_info['member_id']." and formid != '' and state = 1 " )->order('id desc ')->find();

            $template_data['keyword5'] = array('value' => '您好,'.$member_info['username'].'用户购买了一个新订单', 'color' => '#030303');


            $res =  D('Seller/User')->send_wxtemplate_msg(array() ,$url,$head_pathinfo,$weopenid['we_openid'],$template_id,$mnzember_formid_info['formid'], 0,$rember_send_info);
*/

            //客户下单成功发送公众号提醒给团长  weixin_template_order_buy

            //通知开关状态 0为关，1为开
            $template_order_success_notice= D('Home/Front')->get_config_by_name('template_order_success_notice' );

            if(!empty($template_order_success_notice)){

                $weixin_template_order =array();
                $weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
                $weixin_template_order_buy = D('Home/Front')->get_config_by_name('weixin_template_order_buy');




                if( !empty($weixin_appid) && !empty($weixin_template_order_buy))
                {
                    $head_pathinfo = "eaterplanet_ecommerce/moduleA/groupCenter/groupDetail?groupOrderId=".$order['order_id'];

                    $first = '您好团长，您收到了一个新订单，请尽快接单处理';
                    if( $order_info['order_status_id'] == 15 )
                    {
                        $first = '您好,团长收到了一个新预售订单';
                    }

                    $weixin_template_order = array(
                        'appid' => $weixin_appid,
                        'template_id' => $weixin_template_order_buy,
                        'pagepath' => $head_pathinfo,
                        'data' => array(
                            'first' => array('value' => $first,'color' => '#030303'),
                            'tradeDateTime' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
                            'orderType' => array('value' => '用户购买','color' => '#030303'),
                            'customerInfo' => array('value' => $member_info['username'],'color' => '#030303'),
                            'orderItemName' => array('value' => '订单编号','color' => '#030303'),

                            'orderItemData' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),

                            'remark' => array('value' => '点击查看订单详情','color' => '#030303'),
                        )
                    );
                }


                    $headid = $order_info['head_id'];

                    $head_info = M('eaterplanet_community_head')->field('member_id')->where( array('id' => $headid ) )->find();

                    $weopenid = M('eaterplanet_ecommerce_member')->field('we_openid')->where( array('member_id' => $head_info['member_id'] ) )->find();

                    $mnzember_formid_info = M('eaterplanet_ecommerce_member_formid')->where( "member_id=". $head_info['member_id']." and formid != '' and state = 1 " )->order('id desc ')->find();

                    $template_data['keyword5'] = array('value' => '您好,团长收到了一个新订单', 'color' => '#030303');


                    $res =  D('Seller/User')->send_wxtemplate_msg(array() ,$url,$head_pathinfo,$weopenid['we_openid'],$template_id,$mnzember_formid_info['formid'], 0,$weixin_template_order);



            }

            //客户下单成功发送公众号提醒给商户  is_order_notice_supply

            $is_order_notice_supply= D('Home/Front')->get_config_by_name('is_order_notice_supply' );

            if( !empty($is_order_notice_supply)){

                $supply_send_info =array();
                $weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid' );
                $weixin_template_order_buy = D('Home/Front')->get_config_by_name('weixin_template_order_buy' );
                if( !empty($weixin_appid) && !empty($weixin_template_order_buy) )
                {
                    $head_pathinfo = "eaterplanet_ecommerce/moduleA/groupCenter/groupDetail?groupOrderId=".$order['order_id'];

                    $first = '您好,商户收到了一个新订单';
                    if( $order_info['order_status_id'] == 15 )
                    {
                        $first = '您好,商户收到了一个新预售订单';
                    }
                    $supply_send_info = array(
                        'appid' => $weixin_appid,
                        'template_id' => $weixin_template_order_buy,
                        'pagepath' => $head_pathinfo,
                        'data' => array(
                            'first' => array('value' => $first ,'color' => '#030303'),
                            'tradeDateTime' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
                            'orderType' => array('value' => '用户购买','color' => '#030303'),
                            'customerInfo' => array('value' => $member_info['username'],'color' => '#030303'),
                            'orderItemName' => array('value' => '订单编号','color' => '#030303'),

                            'orderItemData' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),

                            'remark' => array('value' => '点击查看订单详情','color' => '#030303'),
                        )
                    );
                }


                //商户id          订单id  $order_info['order_id']

                $order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_info['order_id'] ) )->find();


                if( !empty($order_goods['supply_id']) && $order_goods['supply_id'] > 0 )
                {
                    //关联客户id
                    $supply_info = M('eaterplanet_ecommerce_supply')->where( array('id' => $order_goods['supply_id'] ) )->find();

                    $first = '您好,商户收到了一个新订单';
                    if( $order_info['order_status_id'] == 15 )
                    {
                        $first = '您好,商户收到了一个新预售订单';
                    }

                    if( !empty($supply_info['member_id']) && $supply_info['member_id'] > 0 )
                    {
                        //客户openid
                        $weopenid = M('eaterplanet_ecommerce_member')->where( array('member_id' => $supply_info['member_id'] ) )->find();

                        $mnzember_formid_info = M('eaterplanet_ecommerce_member_formid')->where( "member_id=". $supply_info['member_id']." and formid != '' and state = 1 " )->order('id desc ')->find();

                        $template_data['keyword5'] = array('value' => $first , 'color' => '#030303');

                        $sd_result = D('Seller/User')->send_wxtemplate_msg( array() ,$url,$head_pathinfo,$weopenid['we_openid'],$template_id,$mnzember_formid_info['formid'], 0,$supply_send_info);

                    }
                }

            }

            //客户下单成功发送公众号提醒给平台  platform_send_info_member
            $platform_send_info_member= D('Home/Front')->get_config_by_name('platform_send_info_member' );

            if($platform_send_info_member){

                $platform_send_info =array();
                $weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
                $weixin_template_order_buy = D('Home/Front')->get_config_by_name('weixin_template_order_buy');
                if( !empty($weixin_appid) && !empty($weixin_template_order_buy) )
                {

                    $head_pathinfo = "eaterplanet_ecommerce/moduleA/groupCenter/groupDetail?groupOrderId=".$order['order_id'];

                    $first = '您好,平台收到了一个新订单';
                    if( $order_info['order_status_id'] == 15 )
                    {
                        $first = '您好,平台收到了一个新订单';
                    }

                    $platform_send_info = array(
                        'appid' => $weixin_appid,
                        'template_id' => $weixin_template_order_buy,
                        'pagepath' => $head_pathinfo,
                        'data' => array(
                            'first' => array('value' => $first ,'color' => '#030303'),
                            'tradeDateTime' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
                            'orderType' => array('value' => '用户购买','color' => '#030303'),
                            'customerInfo' => array('value' => $member_info['username'],'color' => '#030303'),
                            'orderItemName' => array('value' => '订单编号','color' => '#030303'),

                            'orderItemData' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),

                            'remark' => array('value' => '点击查看订单详情','color' => '#030303'),
                        )
                    );
                }



                $memberid = $platform_send_info_member;

                $result = explode(",", $memberid);

                foreach($result as $re){

                    $pingtai = M('eaterplanet_ecommerce_member')->where( array('member_id' => $re ) )->find();


                    $mnzember_formid_info = M('eaterplanet_ecommerce_member_formid')->where("member_id={$re} and formid != '' and state = 1 ")->order('id desc')->find();

                    //测试
                    $template_data['keyword5'] = array('value' => $first , 'color' => '#030303');

                    $sd_result = D('Seller/User')->send_wxtemplate_msg( array() ,$url,$head_pathinfo,$pingtai['we_openid'],$template_id,$mnzember_formid_info['formid'], 0,$platform_send_info);


                }

            }



            if( $member_info['openid'] != '1')
            {
                //购买成功通知 weixin_template_pay_order
                //send_template_msg($wx_template_data,$url,$member_info['openid'],C('weixin_template_pay_order'));
            }
        }

    }


}
