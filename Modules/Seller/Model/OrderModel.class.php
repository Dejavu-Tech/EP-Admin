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
namespace Seller\Model;

class OrderModel{


	public function do_tuanz_over($order_id, $title = '后台操作，确认送达团长')
	{
		//express_time

		M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 4, 'express_tuanz_time' => time()) );

		//todo ... send member msg goods is ing

		$history_data = array();
		$history_data['order_id'] = $order_id;
		$history_data['order_status_id'] = 4;
		$history_data['notify'] = 0;
		$history_data['comment'] = $title;
		$history_data['date_added'] = time();


		M('eaterplanet_ecommerce_order_history')->add( $history_data );

		D('Home/Frontorder')->send_order_operate($order_id);
	}


    /**
     * @param $order_id
     * @param string 同城配送发货
     */
	public function do_send_localtown( $order_id, $title = '后台操作，开始配送货物' )
    {

        //express_time

        M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 4,'express_time' => time(),  'express_tuanz_time' => time()) );

        //todo ... send member msg goods is ing

        $history_data = array();
        $history_data['order_id'] = $order_id;
        $history_data['order_status_id'] = 4;
        $history_data['notify'] = 0;
        $history_data['comment'] = $title;
        $history_data['date_added'] = time();

        M('eaterplanet_ecommerce_order_history')->add( $history_data );

        D('Home/LocaltownDelivery')->change_distribution_order_state( $order_id, 0, 1);

		M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id) )->save( array('delivery_type' => 1) );

        D('Home/Frontorder')->send_order_operate($order_id);
		//给配送员发送公众号消息
		$count = D('Seller/Redisorder')->set_distribution_delivery_message($order_id);
		return $count;
    }

	public function do_send_tuanz($order_id, $title = '后台操作，确认开始配送货物')
	{

		//express_time

		M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 14, 'express_time' => time()) );

		//todo ... send tuanz msg

		$history_data = array();
		$history_data['order_id'] = $order_id;
		$history_data['order_status_id'] = 14;
		$history_data['notify'] = 0;
		$history_data['comment'] = $title;
		$history_data['date_added'] = time();

		M('eaterplanet_ecommerce_order_history')->add($history_data);

	}

	/**
	 * 后台自动送达团长
	 * @param unknown $order
	 * @param string $title
	 */
	public function order_auto_service($order,$title="后台自动确认送达团长"){
	    if($order['delivery'] == 'tuanz_send'){//团长配送订单
	        $is_communityhead_auto_service = D('Home/Front')->get_config_by_name('is_communityhead_auto_service');
	        if($is_communityhead_auto_service == 1){//团长订单自动确认送达团长
	            D('Seller/Order')->do_tuanz_over($order['order_id'],'后台自动确认送达团长');
	            D('Home/Frontorder')->send_order_operate($order['order_id']);
	        }
	    }else if($order['delivery'] == 'pickup'){//到点自提订单
	        $is_ziti_auto_service = D('Home/Front')->get_config_by_name('is_ziti_auto_service');
	        if($is_ziti_auto_service == 1){//到店自提订单自动确认送达团长
	            D('Seller/Order')->do_tuanz_over($order['order_id'],'后台自动确认送达团长');
	            D('Home/Frontorder')->send_order_operate($order['order_id']);
	        }
	    }
	}

	public function update($data)
	{

		$ins_data = array();
		$ins_data['tagname'] = $data['tagname'];
		$ins_data['tagcontent'] = serialize(array_filter($data['tagcontent']));
		$ins_data['state'] = $data['state'];
		$ins_data['sort_order'] = $data['sort_order'];

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			M('eaterplanet_ecommerce_goods_tags')->where( array('id' => $id) )->save( $ins_data );

		}else{
			M('eaterplanet_ecommerce_goods_tags')->add( $ins_data );
		}
	}

	public function load_order_list($reorder_status_id = 0,$is_fenxiao =0,$is_pin =0,$integral =0,$is_soli=0)
	{

		$time = I('request.time');

		$s_time_start = I('request.time_start');
		$s_time_end = I('request.time_end');

		if( !isset($time['start']) )
		{
		    $time['start'] = $s_time_start;
		    $time['end']   = $s_time_end;
		}

		$starttime = isset($time['start']) ? strtotime($time['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($time['end']) ? strtotime($time['end']) : strtotime(date('Y-m-d'.' 23:59:59'));

		$order_status_id =  I('request.order_status_id', 0);

		if($reorder_status_id >0)
		{
			$order_status_id = $reorder_status_id;
		}

		$searchtime = I('request.searchtime','');
		$searchfield = I('request.searchfield', '');

		$searchtype = I('request.type', 'normal');

		if( $is_pin == 1  )
		{
			$searchtype = 'pintuan';
		}

		if( $integral == 1 )
		{
			$searchtype = 'integral';
		}
		if(empty($searchtype)){
		    $searchtype = 'normal';
		}
		$delivery = I('request.delivery', '');


		$count_where = "";
		$agentid = I('request.agentid', '');


		$head_id = I('request.headid', '');
		//$is_fenxiao = I('request.is_fenxiao', 0);

		$pindex = I('request.page', 1);
		$psize = 20;


		$paras =array();

		$sqlcondition = "";

		$condition = " 1 ";

		$is_soli_type = I('request.type', '');
		if($is_soli_type == 'soli')
		{
			$is_soli = 1;
		}

		if($is_soli > 0)
		{
			$condition .= " and o.soli_id > 0 ";
		}

		//begin 预售
        if( isset($_GET['presale_order']) && $_GET['presale_order'] == 1 )
        {
            $condition .= " and opr.order_id=o.order_id ";
            $sqlcondition .= ' inner join ' . C('DB_PREFIX').'eaterplanet_ecommerce_order_presale opr ';
        }
        //end  预售

        //begin 礼品卡
        if( isset($_GET['virtualcard_order']) && $_GET['virtualcard_order'] == 1 )
        {
            $condition .= " and vco.order_id=o.order_id ";
            $sqlcondition .= ' inner join ' . C('DB_PREFIX').'eaterplanet_ecommerce_order_virtualcard vco ';
        }
        //end 礼品卡

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$supper_info = get_agent_logininfo();

			$order_ids_list_tmp = M('eaterplanet_ecommerce_order_goods')->field('order_id')->where( array('supply_id' => $supper_info['id'] ) )->select();

			if( !empty($order_ids_list_tmp) )
			{
				$order_ids_tmp_arr = array();
				foreach($order_ids_list_tmp as  $vv)
				{
					$order_ids_tmp_arr[] = $vv['order_id'];
				}
				$order_ids_tmp_str = implode(',', $order_ids_tmp_arr);

				$condition .= " and o.order_id in({$order_ids_tmp_str}) ";
			}
			else{
				$condition .= " and o.order_id in(0) ";
			}
		}

		if( $is_fenxiao == 1)
		{
			//分销订单

			$condition .= " and o.is_commission = 1  ";
			$count_where .= " and is_commission = 1 ";

			$commiss_member_id = I('request.commiss_member_id', '');

			if( $commiss_member_id > 0 )
			{
				$order_ids = M('eaterplanet_ecommerce_member_commiss_order')->field('order_id')->where( array('member_id' => $commiss_member_id ) )->select();

				if(!empty($order_ids))
				{
					$order_ids_arr = array();
					foreach($order_ids as $vv)
					{
						$order_ids_arr[] = $vv['order_id'];
					}
					$order_ids_str = implode(",",$order_ids_arr);
					$condition .= ' AND ( o.order_id in('.$order_ids_str.') ) ';
					$count_where .= ' AND ( order_id in('.$order_ids_str.') ) ';
				}else{
					$condition .= " and o.order_id in(0) ";
					$count_where .= ' AND order_id in(0)  ';
				}
			}



		}

		if( !empty($searchtype) && in_array($searchtype, array('normal','pintuan','integral'))  )
		{
			$condition .= " and o.type ='{$searchtype}'  ";
		}

		if( !empty($delivery) )
		{
			if($delivery == 'cashon_delivery'){
				$condition .= " and o.payment_code = '{$delivery}'  ";
			}else{
				$condition .= " and o.delivery ='{$delivery}'  ";
			}
		}


		if( !empty($head_id) && $head_id >0 )
		{
			$condition .= " and o.head_id ='{$head_id}'  ";

			$count_where .= " and head_id ='{$head_id}'  ";
		}

		if($order_status_id > 0)
		{
			//$condition .= " and o.order_status_id={$order_status_id} ";

			if($order_status_id ==12 )
			{
				$condition .= " and (o.order_status_id={$order_status_id} or o.order_status_id=10 ) ";

			}else if($order_status_id ==11)
			{
				$condition .= " and (o.order_status_id={$order_status_id} or o.order_status_id=6 ) ";
			}
			else{
				$condition .= " and o.order_status_id={$order_status_id} ";
			}

		}

		//$is_fenxiao = I('request.is_fenxiao','intval',0);

		$keyword = I('request.keyword');
		if( !empty($searchfield) && !empty($keyword))
		{
			$keyword = trim($keyword);

			$keyword = htmlspecialchars_decode($keyword, ENT_QUOTES);

			switch($searchfield)
			{
				case 'ordersn':
					$condition .= ' AND locate("'.$keyword.'",o.order_num_alias)>0';
				break;
				case 'member':
					$condition .= ' AND (locate("'.$keyword.'",m.username)>0 or locate("'.$keyword.'",m.telephone)>0 or "'.$keyword.'"=o.member_id ) and o.member_id >0 ';
					$sqlcondition .= ' left join ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_member m on m.member_id = o.member_id ';
				break;
				case 'member_id':
					$keyword = intval($keyword);
					$condition .= ' AND o.member_id = '.$keyword.' ';
				break;

				case 'address':
					$condition .= ' AND ( locate("'.$keyword.'",o.shipping_name)>0 )';
					//shipping_address
				break;
				case 'mobile':
					$condition .= ' AND ( locate("'.$keyword.'",o.shipping_tel)>0 )';
					//shipping_address
				break;
				case 'location':
					$condition .= ' AND (locate("'.$keyword.'",o.shipping_address)>0 )';
				break;
				case 'shipping_no':
					$condition .= ' AND (locate("'.$keyword.'",o.shipping_no)>0 )';
				break;

				case 'head_address':
					$head_ids = M('eaterplanet_community_head')->field('id')->where( 'community_name like "%'.$keyword.'%"' )->select();


					if(!empty($head_ids))
					{
						$head_ids_arr = array();
						foreach($head_ids as $vv)
						{
							$head_ids_arr[] = $vv['id'];
						}
						$head_ids_str = implode(",",$head_ids_arr);
						$condition .= ' AND ( o.head_id in('.$head_ids_str.') )';
					}else{
						$condition .= " and o.order_id in(0) ";
					}

				break;
				case 'head_name':
					// SELECT * FROM `ims_eaterplanet_community_head` WHERE `head_name` LIKE '%黄%'

					$head_ids = M('eaterplanet_community_head')->field('id')->where( 'head_name like "%'.$keyword.'%"' )->select();

					if(!empty($head_ids))
					{
						$head_ids_arr = array();
						foreach($head_ids as $vv)
						{
							$head_ids_arr[] = $vv['id'];
						}
						$head_ids_str = implode(",",$head_ids_arr);
						$condition .= ' AND ( o.head_id in('.$head_ids_str.') )';
					}else{

						$condition .= " and o.order_id in(0) ";

					}

				break;
				case 'goodstitle':
					$sqlcondition = ' inner join ( select DISTINCT(og.order_id) from ' . C('DB_PREFIX').'eaterplanet_ecommerce_order_goods og  where  (locate("'.$keyword.'",og.name)>0)) gs on gs.order_id=o.order_id';
				//var_dump($sqlcondition);
				//die();

				break;
				case 'supply_name':

					$supply_name_sql = 'SELECT id FROM ' . C('DB_PREFIX').
										'eaterplanet_ecommerce_supply where shopname like "%'.$keyword.'%"';
					$supply_ids = M()->query($supply_name_sql);


					if(!empty($supply_ids))
					{
						$supply_ids_arr = array();
						foreach($supply_ids as $vv)
						{
							$supply_ids_arr[] = $vv['id'];
						}
						$supply_ids_str = implode(",",$supply_ids_arr);

						$order_ids_list_tmp = M('eaterplanet_ecommerce_order_goods')->field('order_id')->where( "supply_id in ({$supply_ids_str})" )->select();

						if( !empty($order_ids_list_tmp) )
						{
							$order_ids_tmp_arr = array();
							foreach($order_ids_list_tmp as  $vv)
							{
								$order_ids_tmp_arr[] = $vv['order_id'];
							}
							$order_ids_tmp_str = implode(',', $order_ids_tmp_arr);

							$condition .= " and o.order_id in({$order_ids_tmp_str}) ";
						}else{
							$condition .= " and o.order_id in(0) ";
						}
					}else{
						$condition .= " and o.order_id in(0) ";
					}
				break;
				case 'trans_id':
					$condition .= ' AND (locate('.$keyword.',o.transaction_id)>0 )';
				break;

			}
		}

		if( !empty($searchtime) )
		{
			switch( $searchtime )
			{
				case 'create':
					//下单时间 date_added
					$condition .= " and o.date_added>={$starttime} and o.date_added <= {$endtime}";
				break;
				case 'pay':
					//付款时间
					$condition .= " and o.pay_time>={$starttime} and o.pay_time <= {$endtime}";
				break;
				case 'send':
					//发货时间
					$condition .= " and o.express_time>={$starttime} and o.express_time <= {$endtime}";
				break;
				case 'finish':
					//完成时间
					$condition .= " and o.receive_time>={$starttime} and o.receive_time <= {$endtime}";
				break;
			}
		}


		//----begin----

		if (defined('ROLE') && ROLE == 'agenter' ) {

			$supper_info = get_agent_logininfo();


			$total_where = " and supply_id= ".$supper_info['id'];

			$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".
								C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id'] );

			$order_ids_arr = array();
			$order_ids_arr_dan = array();

			$total_money = 0;
			foreach($order_ids_list as $vv)
			{
				if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
				{
					$order_ids_arr[$vv['order_id']] = $vv;
					$order_ids_arr_dan[] = $vv['order_id'];
				}
			}

			if( !empty($order_ids_arr_dan) )
			{
				$sql = 'SELECT count(o.order_id) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_order as o  '.$sqlcondition.' where ' .  $condition." and o.order_id in (".implode(',', $order_ids_arr_dan).") " ;

				$total_arr = M()->query($sql);

				$total = $total_arr[0]['count'];


				$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
								"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where {$condition} and og.order_id =o.order_id and og.supply_id = ".$supper_info['id']."  ");

				if( !empty($order_ids_list) )
				{
					foreach($order_ids_list as $vv)
					{
						$total_money += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money'];
					}
				}
			}else{
				$total = 0;
			}


		}else{
			$sql = 'SELECT count(o.order_id) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_order as o  '.$sqlcondition.' where ' .  $condition ;

			$total_arr = M()->query( $sql );

			$total = $total_arr[0]['count'];




			$sql = 'SELECT sum(o.total+o.shipping_fare-o.voucher_credit-o.fullreduction_money) as total_money FROM ' .  C('DB_PREFIX') . 'eaterplanet_ecommerce_order as o '.$sqlcondition.' where ' .  $condition ;

			$total_money_arr = M()->query($sql);

			$total_money = $total_money_arr[0]['total_money'];
		}
		//---------end----

		if($total_money < 0)
		{
			$total_money = 0;
		}


		$total_money =  number_format($total_money,2);

		$order_status_arr = $this->get_order_status_name();

		$export = I('request.export', 0);


		if ($export == 1 || $export == 2)
		{
			@set_time_limit(0);

			$is_can_look_headinfo = true;
			$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');


			if (defined('ROLE') && ROLE == 'agenter' )
			{
				if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
				{
					$is_can_look_headinfo = false;
				}
			}
            $note_content = D('Home/Front')->get_config_by_name('order_note_name');
			$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');

            $columns = array(
				array('title' => '订单流水号', 'field' => 'day_paixu', 'width' => 24),
				array('title' => '订单编号', 'field' => 'order_num_alias', 'width' => 36),
				array('title' => '昵称', 'field' => 'name', 'width' => 12),
				//array('title' => '客户姓名', 'field' => 'mrealname', 'width' => 12),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '客户手机号', 'field' => 'telephone', 'width' => 12),
				array('title' => '客户备注', 'field' => 'member_content', 'width' => 24),
				array('title' => '收货姓名(或自提人)', 'field' => 'shipping_name', 'width' => 12),
				array('title' => '联系电话', 'field' => 'shipping_tel', 'width' => 12),
				array('title' => '商户名称/类型', 'field' => 'supply_name', 'width' => 12),
				//array('title' => '收货地址', 'field' => 'address_province', 'width' => 12),
				//array('title' => '', 'field' => 'address_city', 'width' => 12),
				//array('title' => '', 'field' => 'address_area', 'width' => 12),
                array('title' => '微信支付交易单号', 'field' => 'transaction_id', 'width' => 24),

                array('title' => '完整收货地址', 'field' => 'address_province_city_area', 'width' => 12),
				//array('title' => '', 'field' => 'address_street', 'width' => 12),
				array('title' => '提货详细地址', 'field' => 'address_address', 'width' => 12),
				array('title' => '团长配送送货详细地址', 'field' => 'tuan_send_address', 'width' => 22),
				array('title' => '商品名称', 'field' => 'goods_title', 'width' => 24),
				array('title' => '商品分类', 'field' => 'goods_category', 'width' => 24),
				array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 12),
				array('title' => '商品规格', 'field' => 'goods_optiontitle', 'width' => 12),
				array('title' => '商品数量', 'field' => 'quantity', 'width' => 12),
				array('title' => '商品单价', 'field' => 'goods_price1', 'width' => 12),
				//array('title' => '商品单价(折扣后)', 'field' => 'goods_price2', 'width' => 12),
				//array('title' => '商品价格(折扣前)', 'field' => 'goods_rprice1', 'width' => 12),
				array('title' => '商品价格', 'field' => 'goods_rprice2', 'width' => 12),
				array('title' => '支付方式', 'field' => 'paytype', 'width' => 12),
				array('title' => '配送方式', 'field' => 'delivery', 'width' => 12),
				array('title' => '预计送达时间', 'field' => 'expected_delivery_time', 'width' => 24),
				//array('title' => '自提门店', 'field' => 'pickname', 'width' => 24),
				//array('title' => '商品小计', 'field' => 'goodsprice', 'width' => 12),
				array('title' => '运费', 'field' => 'dispatchprice', 'width' => 12),
				array('title' => '积分抵扣', 'field' => 'score_for_money', 'width' => 12),
				//array('title' => '余额抵扣', 'field' => 'deductcredit2', 'width' => 12),
				array('title' => '满额立减', 'field' => 'fullreduction_money', 'width' => 12),
				array('title' => '优惠券优惠', 'field' => 'voucher_credit', 'width' => 12),
				array('title' => '客户佣金', 'field' => 'member_commissmoney', 'width' => 12),
				//array('title' => '订单改价', 'field' => 'changeprice', 'width' => 12),
				//array('title' => '运费改价', 'field' => 'changedispatchprice', 'width' => 12),
				array('title' => '应收款(该笔订单总款)', 'field' => 'price', 'width' => 12),
				array('title' => '状态', 'field' => 'status', 'width' => 12),
				array('title' => '团长佣金', 'field' => 'head_money', 'width' => 12),
				array('title' => '下单时间', 'field' => 'createtime', 'width' => 24),
				array('title' => '付款时间', 'field' => 'paytime', 'width' => 24),
				array('title' => '发货时间', 'field' => 'sendtime', 'width' => 24),

				array('title' => '收货时间', 'field' => 'receive_time', 'width' => 24),

				array('title' => '退款商品数量', 'field' => 'has_refund_quantity', 'width' => 12),
				array('title' => '退款金额', 'field' => 'has_refund_money', 'width' => 12),

				array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24),
				array('title' => '快递公司', 'field' => 'expresscom', 'width' => 24),
				array('title' => '快递单号', 'field' => 'expresssn', 'width' => 24),

				array('title' => '小区名称', 'field' => 'community_name', 'width' => 12),
				array('title' => '团长姓名', 'field' => 'head_name', 'width' => 12),
				array('title' => '团长电话', 'field' => 'head_mobile', 'width' => 12),
				array('title' => '团长完整地址', 'field' => 'fullAddress', 'width' => 24),

				array('title' => '省市区', 'field' => 'province', 'width' => 24),

				array('title' => '订单备注', 'field' => 'remark', 'width' => 36),
				array('title' => '卖家订单备注', 'field' => 'remarksaler', 'width' => 36),
                array('title' => '商品成本价', 'field' => 'costprice', 'width' => 36),

                //array('title' => '核销员', 'field' => 'salerinfo', 'width' => 24),
				//array('title' => '核销门店', 'field' => 'storeinfo', 'width' => 36),
				//array('title' => '订单自定义信息', 'field' => 'order_diyformdata', 'width' => 36),
				//array('title' => '商品自定义信息', 'field' => 'goods_diyformdata', 'width' => 36)

				array('title' => '省', 'field' => 'address_province', 'width' => 36),
				array('title' => '市', 'field' => 'address_city', 'width' => 36),
				array('title' => '区', 'field' => 'address_country', 'width' => 36),
				array('title' => '商品重量（克）', 'field' => 'goods_weight', 'width' => 36),
			);

			if($order_note_open == 1){
				if(empty($note_content)){
					$note_content=  '店名';
				}
				$columns[] = array('title' => $note_content, 'field' => 'note_content', 'width' => 24, 'sort' => 0, 'is_check' => 0);

			}

			//modify_explode_arr

			$modify_explode_arr = I('request.modify_explode_arr', '');
			$columns_keys = array();

			foreach($columns as  $val)
			{
				$columns_keys[ $val['field'] ] = array('title' => $val['title'],'width' => $val['width'] );

			}

			if( !empty($modify_explode_arr) )
			{
				/**
					order_num_alias,
					name,telephone,member_content,shipping_name,shipping_tel,
					address_province_city_area,address_address,goods_title,goods_rprice2,quantity,paytype,
					delivery,tuan_send_address,goods_optiontitle,goods_price1,receive_time,expresssn,createtime,
					community_name,head_name,head_mobile
				**/


				$ziduan_arr = explode(',', $modify_explode_arr);

				$length = count($ziduan_arr);

				$columns = array();

				$save_columns = array();

				foreach( $ziduan_arr as $fields )
				{
					if($fields == 'province'){
						$columns[] = array('title' => $columns_keys['address_province']['title'], 'field' => 'address_province', 'width' => $columns_keys['address_province']['width'] );
						$columns[] = array('title' => $columns_keys['address_city']['title'], 'field' => 'address_city', 'width' => $columns_keys['address_city']['width'] );
						$columns[] = array('title' => $columns_keys['address_country']['title'], 'field' => 'address_country', 'width' => $columns_keys['address_country']['width'] );
					}else{
						$columns[] = array('title' => $columns_keys[$fields]['title'], 'field' => $fields, 'width' => $columns_keys[$fields]['width'] );
					}

					$save_columns[$fields] = $length;
					$length--;
				}
				//dump(777);die;
				D('Seller/Config')->update( array('modify_export_fields' => json_encode($save_columns) ) );
			}


			$exportlist = array();




			if (!(empty($total))) {

					//begin
					set_time_limit(0);

					$fileName = date('YmdHis', time());
					header('Content-Type: application/vnd.ms-execl');
					header('Content-Disposition: attachment;filename="订单数据' . $fileName . '.csv"');

					$begin = microtime(true);

					$fp = fopen('php://output', 'a');

					$step = 100;
					$nums = 10000;

					//设置标题
					//$title = array('ID', '用户名', '用户年龄', '用户描述', '用户手机', '用户QQ', '用户邮箱', '用户地址');

					$title  = array();

					foreach($columns as $key => $item) {
						$title[$item['field']] = iconv('UTF-8', 'GBK', $item['title']);
					}

					fputcsv($fp, $title);

					//$page = ceil($total / 500);


					$sqlcondition .= ' left join ' .C('DB_PREFIX') . 'eaterplanet_ecommerce_order_goods ogc on ogc.order_id = o.order_id ';


					$sql_count = 'SELECT count(o.order_id) as count
								FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_order as o  '.$sqlcondition.' where '  . $condition ;

					$total_arr = M()->query($sql_count);

					$total = $total_arr[0]['count'];

					$page = ceil($total / 500);



					for($s = 1; $s <= $page; $s++) {
						$offset = ($s-1)* 500;




						if ($_GPC['export'] == 1 ) {
						$sql = 'SELECT o.*,ogc.name as goods_title,ogc.supply_id,ogc.goods_id,ogc.order_goods_id ,ogc.quantity as ogc_quantity,ogc.price,ogc.statements_end_time,
									ogc.total as goods_total ,ogc.score_for_money as g_score_for_money, ogc.fullreduction_money as g_fullreduction_money,ogc.voucher_credit as g_voucher_credit ,ogc.has_refund_money,ogc.has_refund_quantity , ogc.shipping_fare as g_shipping_fare,ogc.model as model ,ogc.cost_price
								FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_order as o  '.$sqlcondition.' where '  . $condition . ' ORDER BY o.head_id asc,ogc.goods_id desc,  o.`order_id` DESC  limit '."{$offset},500";

						}else{
						$sql = 'SELECT o.*,ogc.name as goods_title,ogc.supply_id,ogc.order_goods_id,ogc.goods_id,ogc.quantity as ogc_quantity,ogc.price,ogc.is_refund_state,ogc.statements_end_time,
									ogc.total as goods_total ,ogc.score_for_money as g_score_for_money, ogc.fullreduction_money as g_fullreduction_money,ogc.voucher_credit as g_voucher_credit ,ogc.has_refund_money,ogc.has_refund_quantity ,ogc.shipping_fare as g_shipping_fare,ogc.model as model ,ogc.cost_price
								FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_order as o  '.$sqlcondition.' where '  . $condition . ' ORDER BY o.`order_id` DESC  limit '."{$offset},500";
						}


						$list = M()->query( $sql );
					  //  var_dump($list);


						$look_member_arr = array();
						$area_arr = array();

						if( !empty($list) )
						{
							foreach($list as $val)
							{
								if (defined('ROLE') && ROLE == 'agenter' )
								{
									$supper_info = get_agent_logininfo();
									if($supper_info['id'] != $val['supply_id'])
									{
										continue;
									}
								}


								if( empty($look_member_arr) || !isset($look_member_arr[$val['member_id']]) )
								{
									$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' =>  $val['member_id']) )->find();

									$look_member_arr[$val['member_id']] = $member_info;
								}


								$category_name = "";

								$cate_list = M('eaterplanet_ecommerce_goods_to_category')->where( array('goods_id' => $val['goods_id'] ) )->select();

								$cate_arr = array();

								if( !empty($cate_list) )
								{
									foreach( $cate_list as $c_val )
									{
										$ct_info = M('eaterplanet_ecommerce_goods_category')->field('name')->where( array('id' => $c_val['cate_id'] ) )->find();
										if( !empty($ct_info) )
										{
											$cate_arr[] = $ct_info['name'];
										}
									}
									$category_name = implode('、', $cate_arr);
								}


								$tmp_exval= array();
								$tmp_exval['order_num_alias'] = $val['order_num_alias']."\t";
								$tmp_exval['day_paixu'] = '#'.$val['day_paixu'];
								$tmp_exval['name'] = $look_member_arr[$val['member_id']]['username'];

								$tmp_exval['goods_category'] = $category_name;

								//from_type
								if($val['from_type'] == 'wepro')
								{
									$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['we_openid'];
								}else{
									$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['openid'];
								}
								$tmp_exval['telephone'] = $look_member_arr[$val['member_id']]['telephone'];
								$tmp_exval['member_content'] = $look_member_arr[$val['member_id']]['content'];

								$tmp_exval['shipping_name'] = $val['shipping_name'];
								$tmp_exval['shipping_tel'] = $val['shipping_tel'];

								//area_arr
								if( empty($area_arr) || !isset($area_arr[$val['shipping_province_id']]) )
								{
									$area_arr[$val['shipping_province_id']] = D('Seller/Front')->get_area_info($val['shipping_province_id']);
								}

								if( empty($area_arr) || !isset($area_arr[$val['shipping_city_id']]) )
								{
									$area_arr[$val['shipping_city_id']] = D('Seller/Front')->get_area_info($val['shipping_city_id']);
								}

								if( empty($area_arr) || !isset($area_arr[$val['shipping_country_id']]) )
								{
									$area_arr[$val['shipping_country_id']] = D('Seller/Front')->get_area_info($val['shipping_country_id']);
								}

								$province_info = $area_arr[$val['shipping_province_id']];
								$city_info = $area_arr[$val['shipping_city_id']];
								$area_info = $area_arr[$val['shipping_country_id']];


								$tmp_exval['address_province_city_area'] = $province_info['name'].$city_info['name'].$area_info['name'].$val['shipping_address'];

								$tmp_exval['address_province'] = $province_info['name'];
								$tmp_exval['address_city'] = $city_info['name'];
								$tmp_exval['address_country'] = $area_info['name'];

								$tmp_exval['goods_goodssn'] = $val['model'];


								$tmp_exval['address_address'] = $val['shipping_address'];

								if( $val['delivery'] == 'tuanz_send'){
								//	$tmp_exval['address_address'] = $val['tuan_send_address'];
								}

								$tmp_exval['tuan_send_address'] = $val['tuan_send_address'];

								$tmp_exval['goods_title'] = htmlspecialchars_decode(stripslashes($val['goods_title']));
								if($val['supply_id'] == 0){
									$tmp_exval['supply_name'] = '平台自营(自营)';
								}else{
									$supply_list = M('eaterplanet_ecommerce_supply')->where( array('id' => $val['supply_id'] ) )->find();
									$tmp_exval['supply_name'] = $supply_list['shopname'].'(独立商户)';
								}
								$goods_optiontitle = $this->get_order_option_sku($val['order_id'], $val['order_goods_id']);
								$tmp_exval['goods_optiontitle'] = $goods_optiontitle;
								$tmp_exval['quantity'] = $val['ogc_quantity'];
								$tmp_exval['goods_price1'] = $val['price'];
								$tmp_exval['goods_rprice2'] = $val['goods_total'];

								$tmp_exval['has_refund_money'] = $val['has_refund_money'];
								$tmp_exval['has_refund_quantity'] = $val['has_refund_quantity'];

								$goods_weight = $this->get_order_option_weight($val['order_id'], $val['order_goods_id']);
								$tmp_exval['goods_weight'] = $goods_weight;

								$paytype = $val['payment_code'];
								switch($paytype)
								{
									case 'admin':
										$paytype='后台支付';
										break;
									case 'yuer':
										$paytype='余额支付';
										break;
									case 'weixin':
										$paytype='微信支付';
									break;
									default:
										$paytype = '未支付';

								}

								//has_refund_quantity has_refund_money
								//$val['order_id'], $val['order_goods_id']

								$has_refund_quantity = D('Seller/Commonorder')->refund_order_goods_quantity( $val['order_id'], $val['order_goods_id'] );

								$tmp_exval['has_refund_quantity'] = $has_refund_quantity;

								$has_refund_money = D('Seller/Commonorder')->get_order_goods_refund_money( $val['order_id'], $val['order_goods_id'] );

								$tmp_exval['has_refund_money'] = $has_refund_money;


								if(!empty($val['head_id'])){

									$community_info = D('Seller/Front')->get_community_byid($val['head_id']);
									$tmp_exval['community_name'] = $community_info['communityName'];

									if( $is_can_look_headinfo )
									{
										$tmp_exval['fullAddress'] = $community_info['fullAddress'];
										$tmp_exval['head_name'] = $community_info['disUserName'];
										$tmp_exval['head_mobile'] = $community_info['head_mobile'];
									}else{
										$tmp_exval['fullAddress'] = '';
										$tmp_exval['head_name'] = '';
										$tmp_exval['head_mobile'] = '';
									}
								}else{
										$tmp_exval['community_name'] = '';
										$tmp_exval['fullAddress'] = '';
										$tmp_exval['head_name'] = '';
										$tmp_exval['head_mobile'] = '';
								}



								$tmp_exval['paytype'] = $paytype;

								//express 快递, pickup 自提, tuanz_send 团长配送
								//$tmp_exval['delivery'] =  $val['delivery'] == 'express'? '快递':'自提';
								if($val['delivery'] == 'express'){
									$tmp_exval['delivery'] = '快递';
								}elseif($val['delivery'] == 'pickup'){
									$tmp_exval['delivery'] = '自提';
								}elseif($val['delivery'] == 'tuanz_send'){
									$tmp_exval['delivery'] = '团长配送';
								}

								$tmp_exval['expected_delivery_time'] =$val['expected_delivery_time'];

								if ($_GPC['export'] == 1 ) {

										$tmp_exval['dispatchprice'] = $val['g_shipping_fare'];
										$val['total'] = $val['goods_total']+$val['g_shipping_fare']-$val['g_score_for_money']-$val['g_fullreduction_money'] - $val['g_voucher_credit'];

										if($val['total'] < 0)
										{
											$val['total'] = 0;
										}
										$tmp_exval['price'] = $val['total'];
								}else{
									//总运费
									$shipping_fare_sum = array();

									$shipping_fare_sum = M('eaterplanet_ecommerce_order')->where( array('order_id' => $val['order_id']) )->count('shipping_fare');

									$total = M('eaterplanet_ecommerce_order')->where( array('order_id' => $val['order_id']) )->field('total, shipping_fare, score_for_money,fullreduction_money, voucher_credit')->find();
									//
									$order_goods_id = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $val['order_id']) )->field('order_goods_id')->order('order_goods_id asc')->find();

									 $eaterplanet_ecommerce_order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('goods_id' => $val['goods_id'],'order_id' => $val['order_id']) )->find();

									if($order_goods_id['order_goods_id'] == $val['order_goods_id']  ){

										$tmp_exval['dispatchprice'] = $shipping_fare_sum;
										$tmp_exval['price'] = $total['total']+$total['shipping_fare']-$total['score_for_money']-$total['fullreduction_money'] - $total['voucher_credit'];
									}else{
										$tmp_exval['dispatchprice'] = 0;
										$tmp_exval['price'] = 0;
									}


								}
                                $tmp_exval['transaction_id'] = $val['transaction_id']."\t";
                                $tmp_exval['note_content'] = $val['note_content'];
								$tmp_exval['score_for_money'] = $val['g_score_for_money'];
								$tmp_exval['fullreduction_money'] = $val['g_fullreduction_money'];
								$tmp_exval['voucher_credit'] = $val['g_voucher_credit'];



								$tmp_exval['changeprice'] = $val['changedtotal'];
								$tmp_exval['changedispatchprice'] = $val['changedshipping_fare'];


								$val['total'] = $val['goods_total']+$val['g_shipping_fare']-$val['g_score_for_money']-$val['g_fullreduction_money'] - $val['g_voucher_credit'];


								if($val['total'] < 0)
								{
									$val['total'] = 0;
								}


								$tmp_exval['price'] = $val['total'];


								$tmp_exval['head_money'] = 0;


								$head_commiss_order = M('eaterplanet_community_head_commiss_order')->where( array('order_id' => $val['order_id'],'order_goods_id' => $val['order_goods_id'],'type' => 'orderbuy') )->select();

								if( !empty($head_commiss_order) )
								{
									$head_money = 0;
									foreach($head_commiss_order as $k=>$v){
										$head_money = $head_money + $v['money'];
									}
									$tmp_exval['head_money'] = $head_money;
									//$tmp_exval['head_money'] = $head_commiss_order['money'];
								}

								$tmp_exval['member_commissmoney'] = 0;
								//array('title' => '客户佣金', 'field' => 'member_commissmoney', 'width' => 12),
								$member_commissmoney = M('eaterplanet_ecommerce_member_commiss_order')->where(  array('order_id' => $val['order_id'], 'order_goods_id' => $val['order_goods_id'] ) )->sum('money');
								if( !empty($member_commissmoney) && $member_commissmoney > 0 )
								{
									$tmp_exval['member_commissmoney'] = $member_commissmoney;
								}


								if($val['has_refund_quantity'] > 0){
									$tmp_exval['status'] = $order_status_arr[7];
								}else{
									$tmp_exval['status'] = $order_status_arr[$val['order_status_id']];
								}

								$tmp_exval['createtime'] = date('Y-m-d H:i:s', $val['date_added']);


								$tmp_exval['paytime'] = empty($val['pay_time']) ? '' : date('Y-m-d H:i:s', $val['pay_time']);
								$tmp_exval['sendtime'] = empty($val['express_time']) ? '': date('Y-m-d H:i:s', $val['express_time']);
								$tmp_exval['finishtime'] =  empty($val['finishtime']) ? '' : date('Y-m-d H:i:s', $val['finishtime']);

								$tmp_exval['receive_time'] =  empty($val['receive_time']) ? '' : date('Y-m-d H:i:s', $val['receive_time']);

								$tmp_exval['expresscom'] = $val['dispatchname'];
								$tmp_exval['expresssn'] = $val['shipping_no'];
								$tmp_exval['remark'] = $val['comment'];
								$tmp_exval['remarksaler'] = $val['remarksaler'];
                                $tmp_exval['costprice'] = 0;
                                $eaterplanet_ecommerce_goods = M('eaterplanet_ecommerce_goods')->where( array('id' => $val['goods_id']) )->find();
                                if(!empty($eaterplanet_ecommerce_goods)){
                                    if ($eaterplanet_ecommerce_goods['hasoption'] == 1){

                                        $eaterplanet_ecommerce_order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('goods_id' => $val['goods_id'],'order_id' => $val['order_id']) )->find();
                                        if (!empty($eaterplanet_ecommerce_order_goods)){
                                            $eaterplanet_ecommerce_goods_option_item_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('option_item_ids' => $eaterplanet_ecommerce_order_goods['rela_goodsoption_valueid'],'goods_id' => $val['goods_id']) )->find();
                                            $tmp_exval['costprice'] = isset($eaterplanet_ecommerce_goods_option_item_value['costprice'])?$eaterplanet_ecommerce_goods_option_item_value['costprice'] : 0;
                                        }

                                    }else{
                                        $tmp_exval['costprice'] = $eaterplanet_ecommerce_goods['costprice'];
                                    }

                                }
								$tmp_exval['costprice'] = isset($val['cost_price'])?$val['cost_price'] : 0;
								$exportlist[] = $tmp_exval;

								$row_arr = array();

								foreach($columns as $key => $item) {

									$row_arr[$item['field']] = iconv('UTF-8', 'GBK//IGNORE', $tmp_exval[$item['field']]);
								}
								//var_dump($row_arr);die;
								fputcsv($fp, $row_arr);
							}

							ob_flush();
							flush();

							unset($list);
						}

					}

					die();

				//D('Seller/Excel')->export($exportlist, array('title' => '订单数据', 'columns' => $columns));
			}

		}


		if (!(empty($total))) {

			$sql = 'SELECT o.* FROM ' .C('DB_PREFIX'). 'eaterplanet_ecommerce_order as o  '.$sqlcondition.' where '  . $condition . ' ORDER BY  o.`order_id` DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;


			$list = M()->query($sql);
			$need_list = array();


			foreach ($list as $key => &$value ) {
				$sql_goods = "select og.* from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
								where og.order_id = {$value[order_id]} ";

				$goods = M()->query($sql_goods);

                $value['is_virtualcard'] = 0;
				if( $value['delivery'] == 'express' )
                {
                    $virtualcard_result = D('Seller/VirtualCard')->getVirtualCardOrderInfO( $value['order_id'] );
                    if( $virtualcard_result['code'] == 0 ) {
                        $value['is_virtualcard'] = 1;
                    }
                }

				$need_goods = array();

				$shipping_fare = 0;
				$fullreduction_money = 0;
				$voucher_credit = 0;
				$totals = 0;

				if( $value['delivery'] == 'localtown_delivery' && ($value['order_status_id'] != 3 && $value['order_status_id'] != 5 ) )
                {
                    $value['orderdistribution_order'] = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $value['order_id'] ) )->find();
                    if( !empty($value['orderdistribution_order']) && $value['orderdistribution_order'] > 0 )
                    {
                        $orderdistribution = M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => $value['orderdistribution_order']['orderdistribution_id']  ) )->find();

                        $value['orderdistribution_order']['username'] = $orderdistribution['username'];
                    }

					$imdada_data =  M('eaterplanet_ecommerce_orderdistribution_thirth_query')->where( array('order_id' => $value['order_id'], 'third_distribution_type'=>'imdada' ) )->find();
					if($imdada_data['status'] == 1){
						$value['pre_imdada_delivery_fee'] = "¥".$imdada_data['shipping_fee'];
					}else{
						$value['pre_imdada_delivery_fee'] = $imdada_data['message'];
					}

					$mk_data =  M('eaterplanet_ecommerce_orderdistribution_thirth_query')->where( array('order_id' => $value['order_id'], 'third_distribution_type'=>'mk' ) )->find();
					if($mk_data['status'] == 1){
                        $value['pre_mk_delivery_fee'] = "¥".$mk_data['shipping_fee'];
                    }else{
                        $value['pre_mk_delivery_fee'] = $mk_data['message'];
                    }

					$sf_data =  M('eaterplanet_ecommerce_orderdistribution_thirth_query')->where( array('order_id' => $value['order_id'], 'third_distribution_type'=>'sf' ) )->find();
					if($sf_data['status'] == 1){
						$value['pre_sf_delivery_fee'] = "¥".$sf_data['shipping_fee'];
					}else{
						$value['pre_sf_delivery_fee'] = $sf_data['message'];
					}

					//达达配送是否已发过
					$imdada_count = M('eaterplanet_ecommerce_orderdistribution_thirth_log')->where(array('order_id'=>$value['order_id'],'third_distribution_type'=>'imdada'))->count();
					$imdada_has_send = 0;
					if($imdada_count > 0){
						$imdada_has_send = 1;
					}
					$value['imdada_has_send'] = $imdada_has_send;
					//顺丰配送是否已发过
					$sf_count = M('eaterplanet_ecommerce_orderdistribution_thirth_log')->where(array('order_id'=>$value['order_id'],'third_distribution_type'=>'sf'))->count();
					$sf_has_send = 0;
					if($sf_count > 0){
						$sf_has_send = 1;
					}
					$value['sf_has_send'] = $sf_has_send;
                }

				foreach($goods as $key =>$goods_val)
				{
					$goods_val['name'] = htmlspecialchars_decode(stripslashes($goods_val['name']));
					if( $goods_val['is_statements_state'] == 1 )
					{
						$value['is_statements_state'] = 1;
					}

					$goods_val['option_sku'] = $this->get_order_option_sku($value['order_id'], $goods_val['order_goods_id']);

					$goods_val['commisson_info'] = array();//load_model_class('commission')->get_order_goods_commission( $value['order_id'], $goods_val['order_goods_id']);

					//商户名称
					$goods_val['shopname'] =  M('eaterplanet_ecommerce_supply')->field('shopname,type')->where( array('id' => $goods_val['supply_id'] ) )->find();

					//商品类型
					$goods_val['goods_type'] =  M('eaterplanet_ecommerce_goods')->field('type')->where( array('id' => $goods_val['goods_id'] ) )->find();

					if( $goods_val['is_refund_state'] == 1 )
					{

						$refund_info = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $value['order_id'] ,'order_goods_id' => $goods_val['order_goods_id']) )->order('ref_id desc')->find();

						$goods_val['refund_info'] = $refund_info;
					}

					if (defined('ROLE') && ROLE == 'agenter' )
					{
						$supper_info = get_agent_logininfo();

						if($supper_info['id'] != $goods_val['supply_id'])
						{
							continue;
						}
					}
					$shipping_fare += $goods_val['shipping_fare'];
					$fullreduction_money += $goods_val['fullreduction_money'];
					$voucher_credit += $goods_val['voucher_credit'];
					$totals += $goods_val['total'];

					if($value['delivery'] == 'hexiao'){
						$goods_val['hexiao_info'] = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('order_id' => $value['order_id'] ,'order_goods_id' => $goods_val['order_goods_id']) )->find();
					}

					$need_goods[$key] = $goods_val;
				}

				if (defined('ROLE') && ROLE == 'agenter' )
				{
					$value['shipping_fare'] = $shipping_fare;
					$value['fullreduction_money'] = $fullreduction_money;
					$value['voucher_credit'] = $voucher_credit;
					$value['total'] = $totals;
				}

				//member_id ims_  nickname

				$nickname_info = M('eaterplanet_ecommerce_member')->field('username as nickname,content')->where( array('member_id' =>  $value['member_id']) )->find();

				$nickname = $nickname_info['nickname'];

				$value['nickname'] = $nickname;
				$value['member_content'] = $nickname_info['content'];


				$value['goods'] = $need_goods;

				if($value['head_id'] <=0 )
				{
					$value['community_name'] = '';
					$value['head_name'] = '';
					$value['head_mobile'] = '';

					$value['province'] = '';
					$value['city'] = '';

				}else{
					$community_info = D('Seller/Front')->get_community_byid($value['head_id']);


					$value['community_name'] = $community_info['communityName'];
					$value['head_name'] = $community_info['disUserName'];
					$value['head_mobile'] = $community_info['head_mobile'];

					if (defined('ROLE') && ROLE == 'agenter' )
					{
						$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
						if($supply_can_look_headinfo != 1){
							$value['community_name'] = D('Seller/Order')->desensitize($community_info['communityName'],0,-1);
							$value['head_name'] = D('Seller/Order')->desensitize($community_info['disUserName'],1,1);
							$value['head_mobile'] = D('Seller/Order')->desensitize($community_info['head_mobile'],3,4);
						}

					}

					$value['province'] = $community_info['province'];
					$value['city'] = $community_info['city'];
				}




			}
			$pager = pagination2($total, $pindex, $psize);
		}

		//get_order_count($where = '',$uniacid = 0)

		if( !empty($searchtype) )
		{
			$count_where .= " and type = '{$searchtype}' ";
		}


		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$supper_info = get_agent_logininfo();

			$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
								"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id  and og.supply_id = ".$supper_info['id']."  ");
			$order_ids_arr = array();

			$seven_refund_money= 0;

			foreach($order_ids_list as $vv)
			{
				if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
				{
					$order_ids_arr[$vv['order_id']] = $vv['order_id'];
				}
			}
			if( !empty($order_ids_arr) )
			{
				$count_where .= " and order_id in (".implode(',', $order_ids_arr).")";
			}else{
				$count_where .= " and order_id in (0)";
			}

		}


		$all_count = $this->get_order_count($count_where);
		$count_status_1 = $this->get_order_count(" {$count_where} and order_status_id = 1 ");
		$count_status_3 = $this->get_order_count(" {$count_where} and order_status_id = 3 ");
		$count_status_4 = $this->get_order_count(" {$count_where} and order_status_id = 4 ");
		$count_status_5 = $this->get_order_count(" {$count_where} and order_status_id = 5 ");
		$count_status_7 = $this->get_order_count(" {$count_where} and order_status_id = 7 ");
		$count_status_11 = $this->get_order_count(" {$count_where} and (order_status_id = 11 or order_status_id = 6) ");
		$count_status_14 = $this->get_order_count(" {$count_where} and order_status_id = 14 ");

		$count_status_express = $this->get_order_count(" {$count_where} and order_status_id = 1 and delivery = 'express'");


		return array('total' => $total, 'total_money' => $total_money,'pager' => $pager, 'all_count' => $all_count,
				'list' =>$list,
				'count_status_1' => $count_status_1,'count_status_3' => $count_status_3,'count_status_4' => $count_status_4,
				'count_status_5' => $count_status_5, 'count_status_7' => $count_status_7, 'count_status_11' => $count_status_11,
				'count_status_14' => $count_status_14,'count_status_express'=>$count_status_express
				);
	}

	//---copy begin

	public function load_afterorder_list($is_pintuan = 0)
	{
		$time = I('request.time');

		$starttime = isset($time['start']) ? strtotime($time['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($time['end']) ? strtotime($time['end']) : strtotime(date('Y-m-d'.' 23:59:59'));

		$order_status_id =  I('request.order_status_id', 0);
		$state = I('request.state',  -1);
		if($reorder_status_id >0)
		{
			$order_status_id = $reorder_status_id;
		}

		$searchtime = I('request.searchtime','');
		$searchfield = I('request.searchfield', '');

		$searchtype = I('request.type', '');

		if( $is_pintuan == 1 && empty($searchtype) )
		{
			$searchtype = 'pintuan';
		}

		$delivery = I('request.delivery', '');


		$count_where = "";
		$agentid = I('request.agentid', '');

		$head_id = I('request.headid', '');


		$pindex = I('request.page', 1);
		$psize = 20;


		$paras =array();

		$sqlcondition = "";

		$condition = " 1 ";

		if( defined('ROLE') && ROLE == 'agenter' )
		{
			$supper_info = session('agent_auth');

			$supper_info['id'] = $supper_info['uid'];

			$order_ids_list_tmp = M('eaterplanet_ecommerce_order_goods')->field('order_id')->where( array('supply_id' => $supper_info['id'] ) )->select();

			if( !empty($order_ids_list_tmp) )
			{
				$order_ids_tmp_arr = array();
				foreach($order_ids_list_tmp as  $vv)
				{
					$order_ids_tmp_arr[] = $vv['order_id'];
				}
				$order_ids_tmp_str = implode(',', $order_ids_tmp_arr);

				$condition .= " and o.order_id in({$order_ids_tmp_str}) ";
			}
			else{
				$condition .= " and o.order_id in(0) ";
			}
		}


		if( !empty($searchtype) )
		{
			$condition .= " and o.type ='{$searchtype}'  ";
		}

		if( !empty($delivery) )
		{
			$condition .= " and o.delivery ='{$delivery}'  ";
		}

		if( !empty($head_id) && $head_id >0 )
		{
			$condition .= " and o.head_id ='{$head_id}'  ";

			$count_where .= " and head_id ='{$head_id}'  ";
		}


		if( $state >= 0 )
		{
			$condition .= " and ore.state ='{$state}'  ";
		}


		if($order_status_id > 0)
		{
			if($order_status_id ==12 )
			{
				$condition .= " and (o.order_status_id={$order_status_id} or o.order_status_id=10 ) ";
			}else if($order_status_id ==11)
			{
				$condition .= " and (o.order_status_id={$order_status_id} or o.order_status_id=6 ) ";
			}
			else{
				$condition .= " and o.order_status_id={$order_status_id} ";
			}


		}
		if( $is_fenxiao == 1)
		{
			//分销订单

			$condition .= " and o.is_commission = 1  ";
			$count_where = " and is_commission = 1 ";

		}

		$keyword = I('request.keyword');
		if( !empty($searchfield) && !empty($keyword))
		{
			$keyword = trim($keyword);

			$keyword = htmlspecialchars_decode($keyword, ENT_QUOTES);

			switch($searchfield)
			{
				case 'ordersn':
					$condition .= ' AND locate("'.$keyword.'",o.order_num_alias)>0';
				break;
				case 'member':
					$condition .= ' AND (locate("'.$keyword.'",m.username)>0 or locate("'.$keyword.'",m.telephone)>0 or "'.$keyword.'"=o.member_id )';
					$sqlcondition .= ' left join ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_member m on m.member_id = o.member_id ';
				break;
				case 'address':
					$condition .= ' AND ( locate("'.$keyword.'",o.shipping_name)>0 )';
					//shipping_address
				break;
				case 'mobile':
					$condition .= ' AND ( locate("'.$keyword.'",o.shipping_tel)>0 )';
					//shipping_address
				break;
				case 'location':
					$condition .= ' AND (locate("'.$keyword.'",o.shipping_address)>0 )';
				break;
				case 'shipping_no':
					$condition .= ' AND (locate("'.$keyword.'",o.shipping_no)>0 )';
				break;

				case 'head_address':
					$head_ids = M('eaterplanet_community_head')->field('id')->where( 'community_name like "%'.$keyword.'%"' )->select();


					if(!empty($head_ids))
					{
						$head_ids_arr = array();
						foreach($head_ids as $vv)
						{
							$head_ids_arr[] = $vv['id'];
						}
						$head_ids_str = implode(",",$head_ids_arr);
						$condition .= ' AND ( o.head_id in('.$head_ids_str.') )';
					}else{
						$condition .= " and o.order_id in(0) ";
					}

				break;
				case 'head_name':
					// SELECT * FROM `ims_eaterplanet_community_head` WHERE `head_name` LIKE '%黄%'

					$head_ids = M('eaterplanet_community_head')->field('id')->where( 'head_name like "%'.$keyword.'%"' )->select();

					if(!empty($head_ids))
					{
						$head_ids_arr = array();
						foreach($head_ids as $vv)
						{
							$head_ids_arr[] = $vv['id'];
						}
						$head_ids_str = implode(",",$head_ids_arr);
						$condition .= ' AND ( o.head_id in('.$head_ids_str.') )';
					}else{

						$condition .= " and o.order_id in(0) ";

					}

				break;
				case 'goodstitle':
					$sqlcondition = ' inner join ( select DISTINCT(og.order_id) from ' . C('DB_PREFIX').'eaterplanet_ecommerce_order_goods og  where  (locate("'.$keyword.'",og.name)>0)) gs on gs.order_id=o.order_id';
				//var_dump($sqlcondition);
				//die();

				break;
				case 'supply_name':

					$supply_name_sql = 'SELECT id FROM ' . C('DB_PREFIX').
										'eaterplanet_ecommerce_supply where shopname like "%'.$keyword.'%"';
					$supply_ids = M()->query($supply_name_sql);


					if(!empty($supply_ids))
					{
						$supply_ids_arr = array();
						foreach($supply_ids as $vv)
						{
							$supply_ids_arr[] = $vv['id'];
						}
						$supply_ids_str = implode(",",$supply_ids_arr);

						$order_ids_list_tmp = M('eaterplanet_ecommerce_order_goods')->field('order_id')->where( "supply_id in ({$supply_ids_str})" )->select();

						if( !empty($order_ids_list_tmp) )
						{
							$order_ids_tmp_arr = array();
							foreach($order_ids_list_tmp as  $vv)
							{
								$order_ids_tmp_arr[] = $vv['order_id'];
							}
							$order_ids_tmp_str = implode(',', $order_ids_tmp_arr);

							$condition .= " and o.order_id in({$order_ids_tmp_str}) ";
						}else{
							$condition .= " and o.order_id in(0) ";
						}
					}else{
						$condition .= " and o.order_id in(0) ";
					}
				break;
				case 'trans_id':
					$condition .= ' AND (locate('.$keyword.',o.transaction_id)>0 )';
				break;

			}
		}

		if( !empty($searchtime) )
		{
			switch( $searchtime )
			{
				case 'create':
					//下单时间 date_added
					$condition .= " and o.date_added>={$starttime} and o.date_added <= {$endtime}";
				break;
				case 'pay':
					//付款时间
					$condition .= " and o.pay_time>={$starttime} and o.pay_time <= {$endtime}";
				break;
				case 'send':
					//发货时间
					$condition .= " and o.express_time>={$starttime} and o.express_time <= {$endtime}";
				break;
				case 'finish':
					//完成时间
					$condition .= " and o.finishtime>={$starttime} and o.finishtime <= {$endtime}";
				break;
			}
		}


		if (defined('ROLE') && ROLE == 'agenter' ) {

			$supper_info = get_agent_logininfo();


			$total_where = " and supply_id= ".$supper_info['id'];

			$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".
								C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id'] );

			$order_ids_arr = array();
			$order_ids_arr_dan = array();

			$total_money = 0;
			foreach($order_ids_list as $vv)
			{
				if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
				{
					$order_ids_arr[$vv['order_id']] = $vv;
					$order_ids_arr_dan[] = $vv['order_id'];
				}
			}

			if( !empty($order_ids_arr_dan) )
			{
				$sql = 'SELECT count(o.order_id) as count FROM '.C('DB_PREFIX')."eaterplanet_ecommerce_order_refund as ore, " . C('DB_PREFIX'). 'eaterplanet_ecommerce_order as o
						'.$sqlcondition.' where ' .  $condition." and ore.order_id = o.order_id and o.order_id in (".implode(',', $order_ids_arr_dan).") " ;

				$total_arr = M()->query($sql);

				$total = $total_arr[0]['count'];


				$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money
								from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o ,".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund as ore
								where {$condition} and ore.order_id = o.order_id and og.order_id =o.order_id and og.supply_id = ".$supper_info['id']."  ");

				if( !empty($order_ids_list) )
				{
					foreach($order_ids_list as $vv)
					{
						$total_money += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money'];
					}
				}
			}else{
				$total = 0;
			}


		}else{

			$sql = 'SELECT count(o.order_id) as count FROM '.C('DB_PREFIX')."eaterplanet_ecommerce_order_refund as ore,
					" . C('DB_PREFIX'). 'eaterplanet_ecommerce_order as o  '.$sqlcondition.' where ore.order_id = o.order_id and  ' .  $condition ;

			$total_arr =  M()->query($sql);


			$total = $total_arr[0]['count'];



		}




		$order_status_arr = $this->get_order_status_name();

		$export = I('request.export', 0);


		if ($export == 1)
		{
			$is_can_look_headinfo = true;
			$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');

			if (defined('ROLE') && ROLE == 'agenter' )
			{
				if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
				{
					$is_can_look_headinfo = false;
				}
			}


			@set_time_limit(0);
			$columns = array(

				array('title' => '订单编号', 'field' => 'order_num_alias', 'width' => 36),
				array('title' => '订单流水号', 'field' => 'day_paixu', 'width' => 24),
				array('title' => '昵称', 'field' => 'name', 'width' => 12),
				//array('title' => '客户姓名', 'field' => 'mrealname', 'width' => 12),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '客户手机号', 'field' => 'telephone', 'width' => 12),
				array('title' => '客户备注', 'field' => 'member_content', 'width' => 24),

				array('title' => '收货姓名(或自提人)', 'field' => 'shipping_name', 'width' => 12),
				array('title' => '联系电话', 'field' => 'shipping_tel', 'width' => 12),
				array('title' => '收货地址', 'field' => 'address_province', 'width' => 12),
				array('title' => '', 'field' => 'address_city', 'width' => 12),
				array('title' => '', 'field' => 'address_area', 'width' => 12),
				//array('title' => '', 'field' => 'address_street', 'width' => 12),
				array('title' => '提货详细地址', 'field' => 'address_address', 'width' => 12),
				array('title' => '团长配送送货详细地址', 'field' => 'tuan_send_address', 'width' => 22),
				array('title' => '商品名称', 'field' => 'goods_title', 'width' => 24),
				array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 12),
				array('title' => '商品规格', 'field' => 'goods_optiontitle', 'width' => 12),
				array('title' => '商品数量', 'field' => 'quantity', 'width' => 12),
				array('title' => '商品单价', 'field' => 'goods_price1', 'width' => 12),
				//array('title' => '商品单价(折扣后)', 'field' => 'goods_price2', 'width' => 12),
				//array('title' => '商品价格(折扣前)', 'field' => 'goods_rprice1', 'width' => 12),
				array('title' => '商品价格', 'field' => 'goods_rprice2', 'width' => 12),
				array('title' => '支付方式', 'field' => 'paytype', 'width' => 12),
				array('title' => '配送方式', 'field' => 'delivery', 'width' => 12),
				array('title' => '预计送达时间', 'field' => 'expected_delivery_time', 'width' => 24),

				//array('title' => '自提门店', 'field' => 'pickname', 'width' => 24),
				//array('title' => '商品小计', 'field' => 'goodsprice', 'width' => 12),
				array('title' => '运费', 'field' => 'dispatchprice', 'width' => 12),
				array('title' => '积分抵扣', 'field' => 'score_for_money', 'width' => 12),
				//array('title' => '余额抵扣', 'field' => 'deductcredit2', 'width' => 12),
				array('title' => '满额立减', 'field' => 'fullreduction_money', 'width' => 12),
				array('title' => '优惠券优惠', 'field' => 'voucher_credit', 'width' => 12),
				//array('title' => '订单改价', 'field' => 'changeprice', 'width' => 12),
				//array('title' => '运费改价', 'field' => 'changedispatchprice', 'width' => 12),
				array('title' => '应收款(该笔订单总款)', 'field' => 'price', 'width' => 12),
				array('title' => '状态', 'field' => 'status', 'width' => 12),
				array('title' => '团长佣金', 'field' => 'head_money', 'width' => 12),
				array('title' => '下单时间', 'field' => 'createtime', 'width' => 24),
				array('title' => '付款时间', 'field' => 'paytime', 'width' => 24),
				array('title' => '发货时间', 'field' => 'sendtime', 'width' => 24),
				array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24),
				array('title' => '快递公司', 'field' => 'expresscom', 'width' => 24),
				array('title' => '快递单号', 'field' => 'expresssn', 'width' => 24),

				array('title' => '小区名称', 'field' => 'community_name', 'width' => 12),
				array('title' => '团长姓名', 'field' => 'head_name', 'width' => 12),
				array('title' => '团长电话', 'field' => 'head_mobile', 'width' => 12),
				array('title' => '完整地址', 'field' => 'fullAddress', 'width' => 24),


				array('title' => '订单备注', 'field' => 'remark', 'width' => 36),
				array('title' => '卖家订单备注', 'field' => 'remarksaler', 'width' => 36),
				//array('title' => '核销员', 'field' => 'salerinfo', 'width' => 24),
				//array('title' => '核销门店', 'field' => 'storeinfo', 'width' => 36),
				//array('title' => '订单自定义信息', 'field' => 'order_diyformdata', 'width' => 36),
				//array('title' => '商品自定义信息', 'field' => 'goods_diyformdata', 'width' => 36)
			);
			$exportlist = array();


			set_time_limit(0);

			$fileName = date('YmdHis', time());
			header('Content-Type: application/vnd.ms-execl');
			header('Content-Disposition: attachment;filename="退款订单数据' . $fileName . '.csv"');

			$begin = microtime(true);

			$fp = fopen('php://output', 'a');

			$step = 100;
			$nums = 10000;

			//设置标题
			//$title = array('ID', '用户名', '用户年龄', '用户描述', '用户手机', '用户QQ', '用户邮箱', '用户地址');

			$title  = array();

			foreach($columns as $key => $item) {
				$title[$item['field']] = iconv('UTF-8', 'GBK', $item['title']);
			}

			fputcsv($fp, $title);


			$sql_count = 'SELECT count(o.order_id) as count FROM '.C('DB_PREFIX')."eaterplanet_ecommerce_order_refund as ore, "
					. C('DB_PREFIX'). 'eaterplanet_ecommerce_order as o  '.$sqlcondition.' where ore.order_id = o.order_id and '
					. $condition . ' ORDER BY  ore.`ref_id` DESC  ';

			$total_arr = M()->query($sql_count);

			$total = $total_arr[0]['count'];

			$sqlcondition .= ' left join ' .C('DB_PREFIX') . 'eaterplanet_ecommerce_order_goods ogc on ogc.order_id = o.order_id ';


			$page = ceil($total / 500);


			if (!(empty($total))) {

					//searchfield goodstitle goods_goodssn

					for($s = 1; $s <= $page; $s++) {

						$offset = ($s-1)* 500;

						$sql = 'SELECT o.*,ogc.name as goods_title,ogc.supply_id,ogc.order_goods_id ,ogc.quantity as ogc_quantity,ogc.price,ogc.model as model,
								ogc.total as goods_total ,ogc.score_for_money as g_score_for_money,ogc.fullreduction_money as g_fullreduction_money,ogc.voucher_credit as g_voucher_credit ,ogc.shipping_fare as g_shipping_fare FROM '.C('DB_PREFIX').
							"eaterplanet_ecommerce_order_refund as ore, " . C('DB_PREFIX'). 'eaterplanet_ecommerce_order as o  '.$sqlcondition.' where ore.order_id = o.order_id and '  .
							$condition . ' ORDER BY  ore.`ref_id` DESC limit  ' . "{$offset}, 500";

						$list = M()->query($sql);




						$look_member_arr = array();
						$area_arr = array();

						foreach($list as $val)
						{
							if (defined('ROLE') && ROLE == 'agenter' )
							{
								$supper_info = get_agent_logininfo();
								if($supper_info['id'] != $val['supply_id'])
								{
									continue;
								}
							}


							if( empty($look_member_arr) || !isset($look_member_arr[$val['member_id']]) )
							{
								$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' =>  $val['member_id']) )->find();

								$look_member_arr[$val['member_id']] = $member_info;
							}
							$tmp_exval= array();
							$tmp_exval['order_num_alias'] = $val['order_num_alias']."\t";
							$tmp_exval['day_paixu'] = $val['day_paixu'];


							$tmp_exval['name'] = $look_member_arr[$val['member_id']]['username'];
							//from_type
							if($val['from_type'] == 'wepro')
							{
								$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['we_openid'];
							}else{
								$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['openid'];
							}
							$tmp_exval['telephone'] = $look_member_arr[$val['member_id']]['telephone'];
							$tmp_exval['member_content'] = $look_member_arr[$val['member_id']]['content'];

							$tmp_exval['shipping_name'] = $val['shipping_name'];
							$tmp_exval['shipping_tel'] = $val['shipping_tel'];

							//area_arr
							if( empty($area_arr) || !isset($area_arr[$val['shipping_province_id']]) )
							{
								$area_arr[$val['shipping_province_id']] = D('Seller/Front')->get_area_info($val['shipping_province_id']);
							}

							if( empty($area_arr) || !isset($area_arr[$val['shipping_city_id']]) )
							{
								$area_arr[$val['shipping_city_id']] = D('Seller/Front')->get_area_info($val['shipping_city_id']);
							}

							if( empty($area_arr) || !isset($area_arr[$val['shipping_country_id']]) )
							{
								$area_arr[$val['shipping_country_id']] = D('Seller/Front')->get_area_info($val['shipping_country_id']);
							}

							$province_info = $area_arr[$val['shipping_province_id']];
							$city_info = $area_arr[$val['shipping_city_id']];
							$area_info = $area_arr[$val['shipping_country_id']];

							$tmp_exval['address_province'] = $province_info['name'];
							$tmp_exval['address_city'] = $city_info['name'];
							$tmp_exval['address_area'] = $area_info['name'];
							$tmp_exval['goods_goodssn'] = $val['model'];


							$tmp_exval['address_address'] = $val['shipping_address'];

							if( $val['delivery'] == 'tuanz_send'){
								//$tmp_exval['address_address'] = $val['tuan_send_address'];
							}
							$tmp_exval['tuan_send_address'] = $val['tuan_send_address'];

							$tmp_exval['goods_title'] = htmlspecialchars_decode(stripslashes($val['goods_title']));
							$goods_optiontitle = $this->get_order_option_sku($val['order_id'], $val['order_goods_id']);
							$tmp_exval['goods_optiontitle'] = $goods_optiontitle;
							$tmp_exval['quantity'] = $val['ogc_quantity'];
							$tmp_exval['goods_price1'] = $val['price'];
							$tmp_exval['goods_rprice2'] = $val['goods_total'];

							$paytype = $val['payment_code'];
							switch($paytype)
							{
								case 'admin':
									$paytype='后台支付';
									break;
								case 'yuer':
									$paytype='余额支付';
									break;
								case 'weixin':
									$paytype='微信支付';
								break;
								default:
									$paytype = '未支付';

							}

							$community_info = D('Seller/Front')->get_community_byid($val['head_id']);


							$tmp_exval['community_name'] = $community_info['communityName'];

							if($is_can_look_headinfo){
								$tmp_exval['fullAddress'] = $community_info['fullAddress'];
								$tmp_exval['head_name'] = $community_info['disUserName'];
								$tmp_exval['head_mobile'] = $community_info['head_mobile'];
							}else{
								$tmp_exval['fullAddress'] = '';
								$tmp_exval['head_name'] = '';
								$tmp_exval['head_mobile'] = '';
							}


							$tmp_exval['paytype'] = $paytype;

							if($val['delivery'] == 'express'){
								$tmp_exval['delivery'] = '快递';
							}elseif($val['delivery'] == 'pickup'){
								$tmp_exval['delivery'] = '自提';
							}elseif($val['delivery'] == 'tuanz_send'){
								$tmp_exval['delivery'] = '团长配送';
							}

							$tmp_exval['expected_delivery_time'] =$val['expected_delivery_time'];  //date("Y-m-d H:i:s",time())
							$tmp_exval['dispatchprice'] = $val['g_shipping_fare'];
							$tmp_exval['score_for_money'] = $val['g_score_for_money'];


							$tmp_exval['fullreduction_money'] = $val['g_fullreduction_money'];
							$tmp_exval['voucher_credit'] = $val['g_voucher_credit'];



							$tmp_exval['changeprice'] = $val['changedtotal'];
							$tmp_exval['changedispatchprice'] = $val['changedshipping_fare'];


							$val['total'] = $val['goods_total']+$val['g_shipping_fare']-$val['g_score_for_money']-$val['g_fullreduction_money'] - $val['g_voucher_credit'];


							if($val['total'] < 0)
							{
								$val['total'] = 0;
							}


							$tmp_exval['price'] = $val['total'];


							$tmp_exval['head_money'] = 0;


							$head_commiss_order = M('eaterplanet_community_head_commiss_order')->where( array('order_id' => $val['order_id'],'order_goods_id' => $val['order_goods_id']) )->find();

							if( !empty($head_commiss_order) )
							{
								$tmp_exval['head_money'] = $head_commiss_order['money'];
							}




							$tmp_exval['status'] = $order_status_arr[$val['order_status_id']];

							$tmp_exval['createtime'] = date('Y-m-d H:i:s', $val['date_added']);


							$tmp_exval['paytime'] = empty($val['pay_time']) ? '' : date('Y-m-d H:i:s', $val['pay_time']);
							$tmp_exval['sendtime'] = empty($val['express_time']) ? '': date('Y-m-d H:i:s', $val['express_time']);
							$tmp_exval['finishtime'] =  empty($val['finishtime']) ? '' : date('Y-m-d H:i:s', $val['finishtime']);


							$tmp_exval['expresscom'] = $val['dispatchname'];
							$tmp_exval['expresssn'] = $val['shipping_no'];
							$tmp_exval['remark'] = $val['comment'];
							$tmp_exval['remarksaler'] = $val['remarksaler'];

							$exportlist[] = $tmp_exval;

							$row_arr = array();

							foreach($columns as $key => $item) {

								$row_arr[$item['field']] = iconv('UTF-8', 'GBK//IGNORE', $tmp_exval[$item['field']]);
							}

							fputcsv($fp, $row_arr);
						}

						ob_flush();
						flush();

						unset($list);
					}

				die();
               // dump($exportlist);die;
				//D('Seller/Excel')->export($exportlist, array('title' => '订单数据', 'columns' => $columns));
			}

		}

		if (!(empty($total))) {

			$sql = 'SELECT ore.ref_id, ore.order_goods_id,ore.state as ore_state, o.* FROM '.
					C('DB_PREFIX')."eaterplanet_ecommerce_order_refund as ore, " . C('DB_PREFIX') . 'eaterplanet_ecommerce_order as o  '.
					$sqlcondition.' where ore.order_id = o.order_id and '  . $condition .
					' ORDER BY  ore.`ref_id` DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;

			$list = M()->query($sql);
			$need_list = array();
			foreach ($list as $key => &$value ) {

				$sql_goods = "select og.* from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
								where  og.order_id = {$value[order_id]} ";
				if( !empty($value['order_goods_id']) && $value['order_goods_id'] > 0 )
				{
					$sql_goods = "select og.* from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
								where  og.order_goods_id = ".$value['order_goods_id']." and og.order_id = {$value[order_id]} ";
				}

				$goods = M()->query($sql_goods);


				$need_goods = array();

				$shipping_fare = 0;
				$fullreduction_money = 0;
				$voucher_credit = 0;
				$totals = 0;


				//ref_id

				$refund_disable = M('eaterplanet_ecommerce_order_refund_disable')->where( array('ref_id' => $value['ref_id'] ) )->find();

				if( !empty($refund_disable) )
				{

					$value['is_forbidden'] = 1;
				}else{
					$value['is_forbidden'] = 0;
				}


				foreach($goods as $key =>$goods_val)
				{
					$goods_val['name'] = htmlspecialchars_decode(stripslashes($goods_val['name']));
					$goods_val['option_sku'] = $this->get_order_option_sku($value['order_id'], $goods_val['order_goods_id']);

					$goods_val['commisson_info'] = array();

					if (defined('ROLE') && ROLE == 'agenter' )
					{
						$supper_info = get_agent_logininfo();

						if($supper_info['id'] != $goods_val['supply_id'])
						{
							continue;
						}
					}
					$shipping_fare += $goods_val['shipping_fare'];
					$fullreduction_money += $goods_val['fullreduction_money'];
					$voucher_credit += $goods_val['voucher_credit'];
					$totals += $goods_val['total'];

					$need_goods[$key] = $goods_val;
				}

				//if( $_W['role'] == 'agenter' )
				//{
					$value['shipping_fare'] = $shipping_fare;
					$value['fullreduction_money'] = $fullreduction_money;
					$value['voucher_credit'] = $voucher_credit;
					$value['total'] = $totals;
			//	}
				//member_id ims_  nickname

				$nickname_row = M('eaterplanet_ecommerce_member')->field('username as nickname,content')->where( array('member_id' =>$value['member_id'] ) )->find();

				$value['nickname'] = $nickname_row['nickname'];
				$value['member_content'] = $nickname_row['content'];


				$value['goods'] = $need_goods;

				$community_info = D('Seller/Front')->get_community_byid($value['head_id']);




				$value['community_name'] = $community_info['communityName'];
				$value['head_name'] = $community_info['disUserName'];
				$value['head_mobile'] = $community_info['head_mobile'];

				if (defined('ROLE') && ROLE == 'agenter' )
				{
					$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
					if($supply_can_look_headinfo != 1){
						$value['community_name'] = D('Seller/Order')->desensitize($community_info['communityName'],0,-1);
						$value['head_name'] = D('Seller/Order')->desensitize($community_info['disUserName'],1,1);
						$value['head_mobile'] = D('Seller/Order')->desensitize($community_info['head_mobile'],3,4);
					}

				}


				$value['province'] = $community_info['province'];
				$value['city'] = $community_info['city'];


			}
			$pager = pagination2($total, $pindex, $psize);
		}

		//get_order_count($where = '',$uniacid = 0)

		if( !empty($searchtype) )
		{
			$count_where = " and type = '{$searchtype}' ";
		}

		if (defined('ROLE') && ROLE == 'agenter' )
		{

			$supper_info = get_agent_logininfo();

			$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
								"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id  and og.supply_id = ".$supper_info['id']."  ");
			$order_ids_arr = array();

			$seven_refund_money= 0;

			foreach($order_ids_list as $vv)
			{
				if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
				{
					$order_ids_arr[$vv['order_id']] = $vv['order_id'];
				}
			}
			if( !empty($order_ids_arr) )
			{
				$count_where .= " and order_id in (".implode(',', $order_ids_arr).")";
			}else{
				$count_where .= " and order_id in (0)";
			}

		}

		$all_count = $this->get_order_count($count_where);
		$count_status_1 = $this->get_order_count(" {$count_where} and order_status_id = 1 ");
		$count_status_3 = $this->get_order_count(" {$count_where} and order_status_id = 3 ");
		$count_status_4 = $this->get_order_count(" {$count_where} and order_status_id = 4 ");
		$count_status_5 = $this->get_order_count(" {$count_where} and order_status_id = 5 ");
		$count_status_7 = $this->get_order_count(" {$count_where} and order_status_id = 7 ");
		$count_status_11 = $this->get_order_count(" {$count_where} and (order_status_id = 11 or order_status_id = 6) ");
		$count_status_14 = $this->get_order_count(" {$count_where} and order_status_id = 14 ");


		return array('total' => $total, 'total_money' => $total_money,'pager' => $pager, 'all_count' => $all_count,
				'list' =>$list,
				'count_status_1' => $count_status_1,'count_status_3' => $count_status_3,'count_status_4' => $count_status_4,
				'count_status_5' => $count_status_5, 'count_status_7' => $count_status_7, 'count_status_11' => $count_status_11,
				'count_status_14' => $count_status_14
				);
	}


	//---copy end


	/**
	 * @param $order_id
	 * @param int $is_supply	1、商户手机付款，0、后台付款
	 * @return array
	 */
	public function admin_pay_order($order_id,$is_supply = 0)
	{
		$order = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();


		$member_id = $order['member_id'];

		//支付才减库存，才需要判断
		$kucun_method = D('Home/Front')->get_config_by_name('kucun_method');

		if( empty($kucun_method) )
		{
			$kucun_method = 0;
		}

		$error_msg = '';

		if($kucun_method == 1)
		{
			/*** 检测商品库存begin  **/
			$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order['order_id'] ) )->select();

			//goods_id
			foreach($order_goods_list as $val)
			{
				$quantity = $val['quantity'];

				$goods_id = $val['goods_id'];

				$can_buy_count = D('Home/Front')->check_goods_user_canbuy_count($member_id, $goods_id);


				//TODO.这里有问题
				$goods_description = D('Home/Front')->get_goods_common_field($goods_id , 'total_limit_count');

				if($can_buy_count == -1)
				{
					$error_msg = '每人最多购买'.$goods_description['total_limit_count'].'个哦';
				}else if($can_buy_count >0 && $quantity >$can_buy_count)
				{
					$error_msg = '您还能购买'.$can_buy_count.'份';
				}

				$goods_quantity= D('Home/Car')->get_goods_quantity($goods_id);

				if($goods_quantity<$quantity){

					if ($goods_quantity==0) {
						$error_msg ='已抢光';
					}else{
						$error_msg ='商品数量不足，剩余'.$goods_quantity.'个！！';
					}
				}

				//rela_goodsoption_valueid
				if(!empty($val['rela_goodsoption_valueid']))
				{
					$mul_opt_arr = array();

					$goods_option_mult_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('option_item_ids' => $val['rela_goodsoption_valueid'],'goods_id' => $goods_id) )->find();

					if( !empty($goods_option_mult_value) )
					{
						if($goods_option_mult_value['stock']<$quantity){

							$error_msg = '商品数量不足，剩余'.$goods_option_mult_value['stock'].'个！！';
						}
					}
				}

			}
			/*** 检测商品库存end **/
		}

		if( !empty($error_msg) )
		{
			return array('code' => 0,'msg' => $error_msg);
		}else{
			if( $order && $order['order_status_id'] == 3)
			{
				$o = array();
				if($is_supply == 1){
					$o['payment_code'] = 'supply_mobile';
					$o['transaction_id'] = '商户手机付款';
				}else{
					$o['payment_code'] = 'admin';
					$o['transaction_id'] ='后台付款';
				}
				$o['order_id']=$order['order_id'];
				$o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;
				$o['date_modified']=time();
				$o['pay_time']=time();

				if($order['delivery'] == 'hexiao'){//核销订单 支付完成状态改成  已发货待收货
					$o['order_status_id'] =  4;
				}

				//ims_
				M('eaterplanet_ecommerce_order')->where( array('order_id' => $order['order_id']) )->save($o);



				$kucun_method = D('Home/Front')->get_config_by_name('kucun_method', $_W['uniacid']);

				if( empty($kucun_method) )
				{
					$kucun_method = 0;
				}

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
				$oh['order_status_id']= $order['is_pin'] == 1 ? 2:1;
				if($is_supply == 1){
					$oh['comment']='商户手机端付款';
				}else{
					$oh['comment']='后台付款';
				}
				$oh['date_added']=time();
				$oh['notify']=1;

				M('eaterplanet_ecommerce_order_history')->add($oh);

				D('Home/Weixinnotify')->orderBuy($order['order_id'],true);

				//发送购买通知
				//TODO 先屏蔽，等待调试这个消息
				//$weixin_nofity = D('Home/Weixinnotify');
				//$weixin_nofity->orderBuy($order['order_id']);

				if($order['type'] == 'pintuan'){
					$pin_order = M('eaterplanet_ecommerce_pin_order')->where( array('order_id' => $order['order_id']) )->find();
					$pin_id = $pin_order['pin_id'];
					$pin_model = D('Home/Pin');
					$is_pin_success = $pin_model->checkPinSuccess($pin_id);
					if($is_pin_success) {
						//todo send pintuan success notify
						$pin_model->updatePintuanSuccess($pin_id);
					}
				}

				return array('code' => 1);
			}
		}


	}

	public function admin_pay_order2($order_id)
	{


		$order = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();


		if( $order && $order['order_status_id'] == 3)
		{
			$o = array();
			$o['payment_code'] = 'admin';
			$o['order_id']=$order['order_id'];
			$o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;
			$o['date_modified']=time();
			$o['pay_time']=time();
			$o['transaction_id'] = $is_integral ==1? '积分兑换':'余额支付';

			//ims_
			M('eaterplanet_ecommerce_order')->where( array('order_id' => $order['order_id']) )->save($o);


			//暂时屏蔽
			//$kucun_method = C('kucun_method');
			//$kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);
			$kucun_method = 0;

			//$goods_model = D('Home/Goods');

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
			$oh['order_status_id']= $order['is_pin'] == 1 ? 2:1;
			$oh['comment']='后台付款';
			$oh['date_added']=time();
			$oh['notify']=1;

			M('eaterplanet_ecommerce_order_history')->add($oh);


			//发送购买通知
			//TODO 先屏蔽，等待调试这个消息
			//$weixin_nofity = D('Home/Weixinnotify');
			//$weixin_nofity->orderBuy($order['order_id']);


		}
	}
	//检查订单是否能确认收货（存在售后未完成无法确认收货）
	public function check_order_receive($order_id){
		$result = array();
		$status = 1;//1、可以确认收货，0、不能确认收货
		$sql_goods = "select og.* from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
								where og.order_id = {$order_id} ";
		$goods = M()->query($sql_goods);
		foreach($goods as $key =>$goods_val)
		{
			if( $goods_val['is_refund_state'] == 1 )
			{
				$refund_info = M('eaterplanet_ecommerce_order_refund')->field('state')->where( array('order_id' => $order_id ,'order_goods_id' => $goods_val['order_goods_id']) )->find();
				if(!empty($refund_info)){
					if($refund_info['state'] == 0 || $refund_info['state'] == 2){
						$status = 0;
						break;
					}
				}
			}
		}
		$result['status'] = $status;
		return $result;
	}

	//检查订单是否能确认收货（同城配送订单） 该订单未有配送员接单或还未指定配送员，无法确认收货
	public function check_localtown_order_receive($order_id){
		$result = array();
		$status = 1;//1、可以确认收货，0、不能确认收货

		$order_distribution = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id) )->find();
		if(empty($order_distribution['orderdistribution_id']) && ($order_distribution['state'] == 1 || $order_distribution['state'] == 0)){
			$status = 0;
		}
		if(empty($order_distribution['third_distribution_type']) && ($order_distribution['state'] == 1 || $order_distribution['state'] == 0)){
			$status = 0;
		}
		$result['status'] = $status;
		return $result;
	}


	public function receive_order($order_id)
	{

		M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 6, 'receive_time' => time()) );

		D('Home/Frontorder')->receive_order($order_id);

	}

	/**
		获取订单规格值
	**/
	public function get_order_option_sku($order_id, $order_goods_id)
	{
		$option_list = M('eaterplanet_ecommerce_order_option')->field('name,value')->where( array('order_goods_id' => $order_goods_id,'order_id' => $order_id) )->select();

		$sku_str = "";

		if( !empty($option_list) )
		{
			$tmp_arr = array();
			foreach($option_list as $val)
			{
				$tmp_arr[] = $val['name'].",".$val['value'];
			}
			$sku_str = implode(' ', $tmp_arr);
		}
		return $sku_str;
	}

	/**
	获取订单商品重量
	 **/
	public function get_order_option_weight($order_id, $order_goods_id)
	{
		$weight_str = "";
		$order_goods_info = M('eaterplanet_ecommerce_order_goods')->field('goods_id,rela_goodsoption_valueid')->where( array('order_goods_id' => $order_goods_id,'order_id' => $order_id) )->find();

		if(!empty($order_goods_info) && !empty($order_goods_info['rela_goodsoption_valueid'])){
			$godos_option_info = M('eaterplanet_ecommerce_goods_option_item_value')->field('goods_id,weight')->where( array('goods_id' => $order_goods_info['goods_id'],'option_item_ids' => $order_goods_info['rela_goodsoption_valueid']) )->find();
			if(!empty($godos_option_info) && !empty($godos_option_info['weight'])){
				$weight_str = $godos_option_info['weight'];
			}
		}else{
			$goods_info = M('eaterplanet_ecommerce_goods')->field('id,weight')->where( array('id' => $order_goods_info['goods_id']) )->find();
			if(!empty($goods_info) && !empty($goods_info['weight'])){
				$weight_str = $goods_info['weight'];
			}
		}
		return $weight_str;
	}

	public function get_order_status_name()
	{

		$data = S('order_status_name');

		if (empty($data)) {

			$all_list = M('eaterplanet_ecommerce_order_status')->select();

			if (empty($all_list)) {
				$data = array();
			}else{
				$data = array();
				foreach($all_list as $val)
				{
					$data[$val['order_status_id']] = $val['name'];
				}
			}
			S('order_status_name', $data);
		}
		return $data;
	}

	/**
		获取商品数量
	**/
	public function get_order_count($where = '')
	{
        //begin 预售
        if( isset($_GET['presale_order']) && $_GET['presale_order'] == 1 )
        {

            $sql = "select count(1) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_order  inner join ".C('DB_PREFIX').'eaterplanet_ecommerce_order_presale opr on '.C("DB_PREFIX").'eaterplanet_ecommerce_order.order_id =opr.order_id  ';

            $sql .= " where 1 {$where} ";


            $count_arr = M()->query( $sql );

            $total = $count_arr[0]['count'];


        }else if( isset($_GET['virtualcard_order']) && $_GET['virtualcard_order'] == 1 )
        {
            //礼品卡订单
            $sql = "select count(1) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_order  inner join ".C('DB_PREFIX').'eaterplanet_ecommerce_order_virtualcard vco on '.C("DB_PREFIX").'eaterplanet_ecommerce_order.order_id =vco.order_id  ';

            $sql .= " where 1 {$where} ";


            $count_arr = M()->query( $sql );

            $total = $count_arr[0]['count'];
        }

        else{
            $total = M('eaterplanet_ecommerce_order')->where("1 ".$where)->count();
        }
        //end  预售

		return $total;
	}

	public function get_wait_shen_order_comment()
	{
		$total = M('eaterplanet_ecommerce_order_comment')->where( array('state' => 0, 'type' =>0) )->count();

	    return $total;
	}
	/**
		获取商品数量
	**/
	public function get_order_sum($field=' sum(total) as total ' , $where = '',$uniacid = 0)
	{

		$info = M('eaterplanet_ecommerce_order')->field($field)->where("1 ".$where )->find();

		return $info;
	}

	/**

	**/
	public function get_order_goods_group_paihang($where = '',$uniacid = 0)
	{

		//total
		//SELECT name , sum(`quantity`) as total_quantity , goods_id FROM `ims_eaterplanet_ecommerce_order_goods` GROUP by goods_id order by total_quantity desc
		$sql ="SELECT name , sum(`quantity`) as total_quantity, sum(`total`) as m_total , goods_id FROM ".
				C('DB_PREFIX') ."eaterplanet_ecommerce_order_goods where 1 {$where} GROUP by goods_id
				order by total_quantity desc limit 10 ";
		$list = M()->query($sql);


		return $list;
	}

	public function goods_express()
	{
		$_GPC = I('request.');

		$order_id = $_GPC['order_id'];// I('get.order_id',0);


		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

		$now_time = time();

		if($now_time - $order_info['shipping_cha_time'] >= 43200 || true)

		{

			//即时查询接口

			$seller_express = M('eaterplanet_ecommerce_express')->where( array('id' => $order_info['shipping_method'] ) )->find();


			if(!empty($seller_express['simplecode']))
			{

				//887406591556327434  YTO

				//TODO...

				$ebuss_info = D('Home/Front')->get_config_by_name('kdniao_id');

				$exappkey = D('Home/Front')->get_config_by_name('kdniao_api_key');


				$req_url = "http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx";

				//shipping_tel

				if($seller_express['simplecode'] == 'SF'){

						$shipping_tel =	substr($order_info['shipping_tel'],-4);
						$requestData= "{'OrderCode':'".$order_id."','ShipperCode':'".$seller_express['simplecode']."','CustomerName':'".$shipping_tel."','LogisticCode':'". $order_info['shipping_no']."'}";
				}else{

						$requestData= "{'OrderCode':'".$order_id."','ShipperCode':'".$seller_express['simplecode']."','LogisticCode':'". $order_info['shipping_no']."'}";

				}

                $customerName = $order_info['shipping_tel'];
                $customerName = substr($customerName,7);
				$datas = array(

					'EBusinessID' => $ebuss_info,

					'RequestType' => '1002',

					'RequestData' => urlencode($requestData) ,

					'DataType' => '2',
                    'CustomerName'=>$customerName

				);

				$kdniao_freestatus = D('Home/Front')->get_config_by_name('kdniao_freestatus');

				if( isset($kdniao_freestatus) && $kdniao_freestatus ==1 )
				{
					$datas['RequestType'] = '8001';
					//$datas['RequestType'] = '8002';
				}

				if($kdniao_freestatus == 0 ){
					//申通、中通、圆通
					if($seller_express['simplecode'] !='STO' && $seller_express['simplecode'] !='ZTO' && $seller_express['simplecode'] !='YTO' ){

							$order_express = array('code' => 2, 'Reason' => "物流接口(快递鸟)-'免费模式'只支持查询申通、中通、圆通物流轨迹");
							return	$order_express;
					}
				}


				$datas['DataSign'] = $this->encrypt($requestData, $exappkey);

				$result_old=$this->sendPost($req_url, $datas);


				$result = json_decode($result_old);

				//array(8) { ["LogisticCode"]=> string(14) "75395511326146" ["ShipperCode"]=> string(3) "ZTO" ["Traces"]=> array(0) { } ["State"]=> string(1) "0" ["OrderCode"]=> string(4) "5884" ["EBusinessID"]=> string(7) "1636513" ["Reason"]=> string(50) "业务错误[不支持当前快递公司的查询]" ["Success"]=> bool(false) }
				$result2 = json_decode($result_old,true);

				if($result2["Success"] == false){
				 	$order_express = array('code' => 2, 'Reason' => $result2["Reason"]);
					return	$order_express;
				}
				//根据公司业务处理返回的信息......

				//Traces

				if(!empty($result->Traces))
				{
					$order_info['shipping_traces'] = serialize($result->Traces);

					$up_data = array('shipping_cha_time' => time(), 'shipping_traces' => $order_info['shipping_traces']);
					M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->save( $up_data );
				}else{

					$order_express = array('code' => 2, 'Reason' => $result2["Reason"]);
					return	$order_express;
				}

			}

		}

		//ims_

		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->find();

		$goods_info = array();

		$goods_info = D('Home/Pingoods')->get_goods_images($order_goods['goods_id']);


		$goods_info['image'] = tomedia($goods_info['image']);

		$seller_express = M('eaterplanet_ecommerce_express')->where( array('id' => $order_info['shipping_method'] ) )->find();

		$order_info['shipping_traces'] =  unserialize($order_info['shipping_traces']) ;
		$order_express = array('code' => 0, 'seller_express' => $seller_express, 'goods_info' => $goods_info, 'order_info' => $order_info);
		return	$order_express;

	}

	function encrypt($data, $appkey) {

		return urlencode(base64_encode(md5($data.$appkey)));

	}

	function sendPost($url, $datas) {

		$temps = array();

		foreach ($datas as $key => $value) {

			$temps[] = sprintf('%s=%s', $key, $value);

		}

		$post_data = implode('&', $temps);

		$url_info = parse_url($url);

		if(empty($url_info['port']))

		{

			$url_info['port']=80;

		}

		$httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";

		$httpheader.= "Host:" . $url_info['host'] . "\r\n";

		$httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";

		$httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";

		$httpheader.= "Connection:close\r\n\r\n";

		$httpheader.= $post_data;

		$fd = fsockopen($url_info['host'], $url_info['port']);

		fwrite($fd, $httpheader);

		$gets = "";

		$headerFlag = true;

		while (!feof($fd)) {

			if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {

				break;

			}

		}

		while (!feof($fd)) {

			$gets.= fread($fd, 128);

		}

		fclose($fd);



		return $gets;

	}

	/**
	 * 好评有礼送积分
	 * @param array $comment_info 评价信息
	 */
	public function sendCommentGift($comment_id){
	    $comment_info = M('eaterplanet_ecommerce_order_comment')->where(array('comment_id'=>$comment_id))->find();
	    $open_comment_gift = D('Home/Front')->get_config_by_name('open_comment_gift');
	    //开启好评有礼
	    if($open_comment_gift == 1){
	        //评价奖励积分数
	        $comment_gift_score = D('Home/Front')->get_config_by_name('comment_gift_score');
			$type = "goodscomment";
			$result = D('Seller/Order')->check_comment_gift_score($comment_info['member_id']);
			if($result['is_comment_gift']){
				$this->charge_member_score( $comment_info['member_id'] , $comment_gift_score,'in', $type, $comment_info['order_id']);
			}
	        /*if($send_all_score < $comment_gift_max_score){//已赠送积分小于好评奖励积分上限
	            $this->charge_member_score( $comment_info['member_id'] , $comment_gift_score,'in', $type, $comment_info['order_id']);
	        }*/
	    }
	}

	/**
	 * 验证评价有礼积分是否达到上限
	 * 返回boolean  true 还没达到上限，false 已经达到上限
	 */
	public function check_comment_gift_score($member_id){
		$result = array();
		//好评奖励积分上限周期
		$comment_gift_time = D('Home/Front')->get_config_by_name('comment_gift_time');
		//好评奖励积分上限
		$comment_gift_max_score = D('Home/Front')->get_config_by_name('comment_gift_max_score');
		$type = "goodscomment";
		$where = " 1 and member_id=".$member_id;
		$where = $where. " and in_out='in' and type='".$type."' ";
		if($comment_gift_time == 1){//每天
			$begin_time = strtotime(date('Y-m-d 00:00:00'));
			$end_time = strtotime(date('Y-m-d 23:59:59'));
			$where = $where. " and addtime >= ".$begin_time." and addtime <= ".$end_time;
		}else if($comment_gift_time == 2){//每周
			$today = date('Y-m-d');
			$first = 1;
			$w = date('w',strtotime($today));
			$begin_time = strtotime(date('Y-m-d',strtotime("$today -" . ($w ? $w - $first : 6) . ' days')));
			$begin = date('Y-m-d',strtotime("$today -" . ($w ? $w - $first : 6) . ' days'));
			$end_time = strtotime(date('Y-m-d',strtotime("$begin +6 days")));
			$where = $where. " and addtime >= ".$begin_time." and addtime <= ".$end_time;
		}else if($comment_gift_time == 3){//每月
			$begin_time = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
			$begin = date('Y-m-01', strtotime(date("Y-m-d")));
			$end_time = strtotime(date('Y-m-d', strtotime("$begin +1 month -1 day")));
			$where = $where. " and addtime >= ".$begin_time." and addtime <= ".$end_time;
		}

		$send_all_score = M('eaterplanet_ecommerce_member_integral_flow')->where($where)->sum('score');
		/*$result['where'] = $where;
		$result['all_score'] = $send_all_score;*/
		if(empty($send_all_score)){
			$send_all_score = 0;
		}
		/*$result['send_all_score'] = $send_all_score;
		$result['comment_gift_max_score'] = $comment_gift_max_score;*/
		if($comment_gift_max_score > 0){
			if($send_all_score < $comment_gift_max_score){//已赠送积分小于好评奖励积分上限
				$result['is_comment_gift'] = true;
			}else{
				$result['is_comment_gift'] = false;
			}
		}else{
			$result['is_comment_gift'] = true;
		}
		return $result;
	}

	public function charge_member_score($member_id, $score,$in_out, $type, $order_id=0){
	    $log_data = array();
	    $log_data['member_id'] = $member_id;
	    $log_data['in_out'] = $in_out;
	    $log_data['score'] = $score;
	    $log_data['type'] = $type;
	    $log_data['order_id'] = $order_id;
	    $log_data['addtime'] = time();

	    $member_score_info = M('eaterplanet_ecommerce_member')->field('score')->where( array('member_id' => $member_id) )->find();
	    $member_score = $member_score_info['score'];
	    if(empty($member_score)){
	        $member_score = 0;
	    }
		if($in_out == 'in'){
			$log_data['after_operate_score'] = $member_score+$score;
			if($type == 'goodscomment'){
				//增加积分
				$log_data['state'] = 1;
				$log_data['remark'] = "评价有礼,增加积分";
				M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->setInc('score',$score);
			}else if($type == 'invitegift'){
				//邀请者赠送积分
				$log_data['state'] = 1;
				$log_data['remark'] = "邀请者邀请成功，增加积分".$score;
				M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->setInc('score',$score);
			}else if($type == 'invitegift_new'){
				//被邀请者赠送积分
				$log_data['state'] = 1;
				$log_data['remark'] = "被邀请者邀请成功，增加积分".$score;
				M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->setInc('score',$score);
			}else if($type == 'pintuan_rebate'){
				//拼团返利
				$log_data['state'] = 1;
				$log_data['remark'] = "拼团返利，赠送积分".$score;
				M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->setInc('score',$score);
			}
		}
	    M('eaterplanet_ecommerce_member_integral_flow')->add($log_data);
	}

	public function send_delivery_msg($order_info,$we_openid){
		//同城配送是否开启
		$isopen_localtown_delivery = D('Home/Front')->get_config_by_name('isopen_localtown_delivery' );

		if(!empty($isopen_localtown_delivery)){

			$weixin_template_order =array();
			$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
			$weixin_template_order_riders_receive = D('Home/Front')->get_config_by_name('weixin_template_order_riders_receive');
			$shop_domain = D('Home/Front')->get_config_by_name('shop_domain');

			$url =  $shop_domain;
			if( !empty($weixin_appid) && !empty($weixin_template_order_riders_receive) )
			{
				$template_id = $weixin_template_order_riders_receive;
				$head_pathinfo = "eaterplanet_ecommerce/moudleB/rider/grap";
				$weixin_template_order = array(
						'appid' => $weixin_appid,
						'template_id' => $weixin_template_order_riders_receive,
						'pagepath' => $head_pathinfo,
						'data' => array(
								//详细内容
								'first' => array('value' => '您好，有新的同城配送订单，请您尽快接单!','color' => '#030303'),
								//买家名称
								'keyword1' => array('value' => $order_info['shipping_name'],'color' => '#030303'),
								//买家电话
								'keyword2' => array('value' => $order_info['shipping_tel'],'color' => '#030303'),
								//配送地址
								'keyword3' => array('value' => $order_info['shipping_address'],'color' => '#030303'),
								//商品内容
								'keyword4' => array('value' => $order_info['goods_name'],'color' => '#030303'),
								//下单时间
								'keyword5' => array('value' => $order_info['create_time'],'color' => '#030303'),

								'remark' => array('value' => '请骑手们尽快接单','color' => '#030303'),
						)
				);
				$res =  D('Seller/User')->send_wxtemplate_msg(array() , $url ,$head_pathinfo,$we_openid,$template_id,"", 0,$weixin_template_order);
				//print_r($res);
			}
		}
	}

	/**
	 * 获取同城配送订单信息
	 * @param $order_id
	 */
	public function get_order_delivery_detail($order_id){
		$order_delivery = array();
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();
		//买家名称
		$order_delivery['shipping_name'] = $order_info['shipping_name'];
		//买家电话
		$order_delivery['shipping_tel'] = $order_info['shipping_tel'];
		//配送地址
		$province_info = D('Seller/Area')->get_area_info($order_info['shipping_province_id']);
		$city_info = D('Seller/Area')->get_area_info($order_info['shipping_city_id']);
		$country_info = D('Seller/Area')->get_area_info($order_info['shipping_country_id']);

		$shipping_address = $province_info.$city_info.$country_info.$order_info['shipping_address'];
		$order_delivery['shipping_address'] = $shipping_address;
		//商品内容
		$goods_name = "";
		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->select();
		foreach($order_goods as $k=>$v)
		{
			if(empty($goods_name)){
				$goods_name = $v['name'];
			}else{
				$goods_name = $goods_name.'；'.$v['name'];
			}
		}
		$order_delivery['goods_name'] = $goods_name;
		//下单时间
		$order_delivery['create_time'] = date('Y-m-d H:i:s',$order_info['date_added']);
		return $order_delivery;
	}

	/**
	 * 同城配送第三方公司配送
	 * @param $order_id 订单号
	 * @param $data_type	第三方公司
	 * @param $express_info	第三方公司返回数据
	 */
	public function do_send_localtown_thirth_delivery( $order_id, $data_type, $express_info)
	{
		$title = "，开始配送货物";
		$delivery_company = "";
		if($data_type == 'imdada'){
			$delivery_company = "第三方达达";
		}else if($data_type == 'sf'){
			$delivery_company = "第三方顺丰同城";
		}else if($data_type == 'make'){
            $delivery_company = "码科配送";
        }else if($data_type == 'ele'){
			$delivery_company = "蜂鸟即配";
		}
		$title = $delivery_company.$title;
		//express_time
		M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 4,'express_time' => time(),  'express_tuanz_time' => time(), 'third_distribution_type'=>$data_type) );

		//todo ... send member msg goods is ing
		$history_data = array();
		$history_data['order_id'] = $order_id;
		$history_data['order_status_id'] = 4;
		$history_data['notify'] = 0;
		$history_data['comment'] = $title;
		$history_data['date_added'] = time();

		M('eaterplanet_ecommerce_order_history')->add( $history_data );

		$other_data = array();
		$other_data['delivery_company'] = $delivery_company;
		$other_data['data_type'] = $data_type;
		if($data_type == 'imdada'){
			$other_data['delivery_fee'] = $express_info['delivery_fee'];
		}else if($data_type == 'sf'){
			$other_data['delivery_fee'] = $express_info['delivery_fee'];
			$other_data['delivery_order_id'] = $express_info['delivery_order_id'];
			$other_data['delivery_bill_id'] = $express_info['delivery_bill_id'];
		}else if( $data_type == 'make' ){
			$other_data['delivery_fee'] = $express_info['delivery_fee'];
            $other_data['delivery_order_id'] = $express_info['delivery_order_id'];
        }else if( $data_type == 'ele' ){
			$other_data['delivery_fee'] = $express_info['delivery_fee'];
			$other_data['delivery_order_id'] = $express_info['delivery_order_id'];
		}
		D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 1, $other_data);
		D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $data_type , 1 ,'已创建订单' );
		D('Home/Frontorder')->send_order_operate($order_id);
	}

	/**
	 * @param $order_id
	 * @param $order_status_id 0、状态未改变，4、已发货，待收货，6、已签收
	 * @param $other_data
	 */
	public function do_localtown_thirth_delivery_return( $order_sn, $order_status_id, $other_data)
	{
		$order_info = M('eaterplanet_ecommerce_order')->field('order_id,order_num_alias,total,member_id,order_status_id')->where( array('order_num_alias' => $order_sn) )->find();
		if(!empty($order_info)){
			$order_id = $order_info['order_id'];
			$delivery_company = "";
			if($other_data['data_type'] == 'imdada'){
				$delivery_company = "第三方达达";
			}else if($other_data['data_type'] == 'sf'){
				$delivery_company = "第三方顺丰同城";
			}else if( $other_data['data_type'] == 'make' )
            {
                $delivery_company = "第三方码科配送";
            }else if( $other_data['data_type'] == 'ele' )
			{
				$delivery_company = "第三方蜂鸟即配";
			}
			if($order_status_id > 0){
				if($order_status_id == 6 && $order_info['order_status_id'] == 4){
					M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 6, 'receive_time' => time()) );

					D('Home/Frontorder')->receive_order($order_id, 1, $delivery_company);
					//M('eaterplanet_ecommerce_order_history')->where( array('order_id' => $order_id,'order_status_id' => 6) )->save( array( 'comment' => $delivery_company.'，确认收货') );
				}
			}

			$other_data['delivery_company'] = $delivery_company;
			if($other_data['data_type'] == 'imdada'){//达达平台配送
				if($other_data['order_status'] == 2){//待取货
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 2, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['dm_name'].'已抢单，待取货' );
				}else if($other_data['order_status'] == 3){//配送中
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 3, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['dm_name'].'配送中' );
				}else if($other_data['order_status'] == 4){//已完成
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 4, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['dm_name'].'配送完成' );
				}else if($other_data['order_status'] == 5){//已取消
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 5, $other_data);
					$cancel_reason = "";
					if($other_data['cancel_from'] == 11){//达达骑手取消订单
						$cancel_reason = "骑士取消订单:".$other_data['cancel_reason'];
					}else{
						$cancel_reason = "订单取消原因:".$other_data['cancel_reason'];
					}
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] , $cancel_reason );
					$this->cancel_third_delviery_order_notice($order_info,$other_data);
				}else if($other_data['order_status'] == 9){//妥投异常之物品返回中
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 5, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['cancel_reason'] );
					$this->cancel_third_delviery_order_notice($order_info,$other_data);
				}else if($other_data['order_status'] == 10){//妥投异常之物品返回完成
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 5, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['cancel_reason'] );
					$this->cancel_third_delviery_order_notice($order_info,$other_data);
				}else if($other_data['order_status'] == 100){//创建达达运单失败
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 5, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['cancel_reason'] );
				}
			}else if( $other_data['data_type'] == 'make' ){
                //2 3/ 4
                if($other_data['order_status'] == 2){//待取货
                    D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 2, $other_data);
                    D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['dm_name'].'已抢单，待取货' );
                }else if($other_data['order_status'] == 3){//配送中
                    D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 3, $other_data);
                    D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['dm_name'].'配送中' );
                }else if($other_data['order_status'] == 4){//已完成
                    D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 4, $other_data);
                    D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['dm_name'].'配送完成' );
                }

            }else if($other_data['data_type'] == 'sf'){//顺丰同城配送
				if($other_data['order_status'] == 2){//已取消
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 5, $other_data);
					$cancel_reason = "订单取消，操作人：".$other_data['operator_name']."，取消原因：".$other_data['status_desc'];
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , 5 ,$cancel_reason );
					$other_data['cancel_reason'] = $other_data['status_desc'];
					$this->cancel_third_delviery_order_notice($order_info,$other_data);
				}else if($other_data['order_status'] == 10 || $other_data['order_status'] == 12 || $other_data['order_status'] == 15){
					//10-配送员确认;12:配送员到店;15:配送员配送中
					$operator_action = $other_data['status_desc'];
					$operator_txt = "";
					if(!empty($other_data['operator_name'])){
						$operator_txt = "(".$other_data['operator_name'].'，'.$other_data['operator_phone'].")";
					}
					if($other_data['order_status'] == 10){
						D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 2, $other_data);
						D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , 2 ,$operator_action.$operator_txt);
					}else if($other_data['order_status'] == 12){
						D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 2, $other_data);
						D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , 2 ,$operator_action.$operator_txt);
					}else if($other_data['order_status'] == 15){
						D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 3, $other_data);
						D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , 3 ,$operator_action.$operator_txt);
					}
				}else if($other_data['order_status'] == 0){
					//0-订单异常
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 5, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , 11 ,'订单异常,异常详情:'.$other_data['ex_content'] );
				}else if($other_data['order_status'] == 17){//已完成
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 4, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , 4 , '配送员点击完成' );
				}
			}else if( $other_data['data_type'] == 'ele' ){//蜂鸟即配
				if($other_data['order_status'] == 2){//待取货
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 2, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] , $other_data['desc'] );
				}else if($other_data['order_status'] == 3){//配送中
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 3, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] , $other_data['desc'] );
				}else if($other_data['order_status'] == 4){//已完成
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 4, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] , $other_data['desc'] );
				}else if($other_data['order_status'] == 100){//异常订单
					D('Home/LocaltownDelivery')->change_thirth_distribution_order_state( $order_id, 5, $other_data);
					D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $other_data['data_type'] , $other_data['order_status'] ,$other_data['desc'] );
				}
			}
		}
	}

	/**
	 * 商家取消第三方配送订单
	 * @param $order_id
	 * @param $data_type
	 * @param $other_data
	 */
	public function do_cancel_thirth_delivery_order($order_id,$data_type,$other_data){
		/*$delivery_company = "";
		if($other_data['data_type'] == 'imdada'){
			$delivery_company = "第三方达达";
		}else if($other_data['data_type'] == 'sf'){
			$delivery_company = "第三方顺丰同城";
		}*/
		M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->save(
				array('state' => 5,'cancel_reason'=>$other_data['cancel_reason'],'deduct_fee'=>$other_data['deduct_fee'])
		);
		//违约金
		if(!empty($other_data['now_deduct_fee']) && $other_data['now_deduct_fee'] > 0){
			$log_mgs = "";
			if($data_type == 'imdada'){
				$log_mgs = "第三方达达取消订单，产生违约费：¥".$other_data['now_deduct_fee'];
			}else if($data_type == 'sf'){
				$log_mgs = "第三方顺丰取消订单，产生违约费：¥".$other_data['now_deduct_fee'];
			}else if($data_type == 'make'){
                $log_mgs = "码科配送取消订单，产生违约费：¥".$other_data['now_deduct_fee'];
            }else if($data_type == 'ele'){
				$log_mgs = "蜂鸟即配取消订单，产生违约费：¥".$other_data['now_deduct_fee'];
			}
			$oh = array();
			$oh['order_id'] = $order_id;
			$oh['order_status_id'] = 4;
			$oh['comment'] = $log_mgs;
			$oh['date_added'] = time();
			$oh['notify'] = 1;
			M('eaterplanet_ecommerce_order_history')->add($oh);
			//扣除违约金
			D('Seller/Supply')->update_supply_commission($order_id,$other_data['now_deduct_fee']);
		}
		if($data_type == 'sf'){
			$delivery_company = "第三方顺丰同城";
			$remark = $delivery_company.'订单已取消，取消原因:'.$other_data['cancel_reason'];
			D('Home/LocaltownDelivery')->write_distribution_log( $order_id, 0 , 5 ,$remark );
			M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->save(
					array('delivery_type'=>0)
			);
			D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $data_type , 5 ,'订单已取消，取消原因:'.$other_data['cancel_reason'] );
		}if($data_type == 'make'){
            $delivery_company = "码科配送";
            $remark = $delivery_company.'订单已取消，取消原因:'.$other_data['cancel_reason'];
            D('Home/LocaltownDelivery')->write_distribution_log( $order_id, 0 , 5 ,$remark );
            D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $data_type , 5 ,'订单已取消，取消原因:'.$other_data['cancel_reason'] );
        }if($data_type == 'ele'){
			$delivery_company = "蜂鸟即配";
			$remark = $delivery_company.'订单已取消，取消原因:'.$other_data['cancel_reason'];
			D('Home/LocaltownDelivery')->write_distribution_log( $order_id, 0 , 5 ,$remark );
			D('Home/LocaltownDelivery')->save_orderdistribution_thirth_log( $order_id, $data_type , 5 ,'订单已取消，取消原因:'.$other_data['cancel_reason'] );
		}
	}

	/**
	 * 第三方配送取消订单通知
	 */
	public function cancel_third_delviery_order_notice($order_info,$other_data){
		$delivery_company = "";
		if($other_data['data_type'] == 'imdada'){
			$delivery_company = "达达";
		}else if($other_data['data_type'] == 'sf'){
			$delivery_company = "顺丰同城";
		}else if($other_data['data_type'] == 'ele'){
			$delivery_company = "蜂鸟即配";
		}if($other_data['data_type'] == 'make') {
			$delivery_company = "码科配送";
		}
		$order_member_name = M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();
		//6、发送取消通知订单给平台

		$weixin_template_cancle_order = D('Home/Front')->get_config_by_name('weixin_template_cancle_order');
		$platform_send_info_member_id = D('Home/Front')->get_config_by_name('platform_send_info_member');

		if( !empty($weixin_template_cancle_order) && !empty($platform_send_info_member_id) )
		{
			$weixin_template_order =array();
			$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid' );


			if( !empty($weixin_appid) && !empty($weixin_template_cancle_order) )
			{
				$head_pathinfo = "eaterplanet_ecommerce/pages/index/index";

				$pl_member_id =  explode(",", $platform_send_info_member_id);

				foreach($pl_member_id as $m_id){

					$weopenid = M('eaterplanet_ecommerce_member')->where( array('member_id' => $m_id ) )->find();

					$weixin_template_order = array(
							'appid' => $weixin_appid,
							'template_id' => $weixin_template_cancle_order,
							'pagepath' => $head_pathinfo,
							'data' => array(
									'first' => array('value' => '您好，您收到了一个'.$delivery_company.'配送取消订单，请尽快处理','color' => '#030303'),
									'keyword1' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),
									'keyword2' => array('value' => '取消订单','color' => '#030303'),
									'keyword3' => array('value' => sprintf("%01.2f", $order_info['total']),'color' => '#030303'),
									'keyword4' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
									'keyword5' => array('value' => $order_member_name['username'],'color' => '#030303'),
									'remark' => array('value' => '此订单已于'.date('Y-m-d H:i:s').'被'.$delivery_company.'配送取消，取消原因：'.$other_data['cancel_reason'].'，请尽快处理','color' => '#030303'),
							)
					);

					D('Seller/User')->just_send_wxtemplate($weopenid['we_openid'], 0, $weixin_template_order );

				}
			}
		}
	}

	/**
	 * 核销订单
	 * @param $order_id		订单号
	 * @return array
	 */
	public function hexiao_all_orders($order_id){
	    //订单信息
	    $field = "order_id,order_num_alias,member_id,ziti_name,ziti_mobile,shipping_name,shipping_tel";
	    $order_info = M('eaterplanet_ecommerce_order')->where(array('order_id'=>$order_id))->field($field)->find();
	    //订单要核销商品(未核销完，剩余核销数量大于0)
	    $order_goods_saleshexiao_list = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_info['order_id'],'is_hexiao_over'=>0))->select();

	    if(count($order_goods_saleshexiao_list) > 0){
	        foreach($order_goods_saleshexiao_list as $k=>$v){
	            //核销商品
	            $this->hexiao_order_goods($v,0);
	        }
	        $this->hexiao_finished($order_id,'后台整单点击“确认使用”，订单完成');
	    }
	}

	/**
	 * 核销完成
	 * @param $order_id
	 */
	public function hexiao_finished($order_id,$hx_msg){
	    $is_finished = true;
	    $hexiao_list = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_id))->select();
	    foreach($hexiao_list as $k=>$v){
			if($v['hexiao_type'] == 0 && $v['is_hexiao_over'] == 0){//按订单核销
				$is_finished = false;
			}
	        if($v['hexiao_type'] == 1 && $v['is_hexiao_over'] == 0){//按次核销
	            if($v['hexiao_count'] > 0 && $v['remain_hexiao_count'] > 0){
					$is_finished = false;
				}
				if($v['hexiao_count'] == 0){
					$is_finished = false;
				}
	        }
	    }
	    if($is_finished){
	        $order_history = array();
	        $order_history['order_id'] = $order_id;
	        $order_history['order_status_id'] = 11;
	        $order_history['notify'] = 0;
	        $order_history['comment'] = $hx_msg;
	        $order_history['date_added']=time();
	        M('eaterplanet_ecommerce_order_history')->add($order_history);
	        //ims_
			$time = time();
	        M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 11,'receive_time' => $time,'finishtime' => $time) );

			$open_aftersale = D('Home/Front')->get_config_by_name('open_aftersale');
			$open_aftersale_time = D('Home/Front')->get_config_by_name('open_aftersale_time');
			$statements_end_time = $time;
			if( !empty($open_aftersale) && !empty($open_aftersale_time) && $open_aftersale_time > 0  )
			{
				$statements_end_time = $statements_end_time + 86400 * $open_aftersale_time;
			}
			$up_order_data = array();
			$up_order_data['statements_end_time'] = $statements_end_time;
			M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id) )->save( $up_order_data );
		}
	}

	/**
	 * 核销商品
	 * @param $order_goods_saleshexiao	订单商品核销信息表
	 * @param $hx_time	0、按订单核销，大于0、按次数核销
	 * @return 1、核销商品成功，0、核销商品失败,-1 核销次数大于剩余次数
	 */
	public function hexiao_order_goods($order_goods_saleshexiao,$hx_time){
	    //订单核销信息表id
	    $hx_id = $order_goods_saleshexiao['id'];
	    //剩余核销数量
	    $remain_hexiao_count = $order_goods_saleshexiao['remain_hexiao_count'];
	    if($order_goods_saleshexiao['hexiao_count'] > 0){
			$hexiao_count = 0;
			if($hx_time > 0){
				if($hx_time > $remain_hexiao_count){
					return -1;
				}else{
					$hexiao_count = $hx_time;
				}
			}else{
				$hexiao_count = $remain_hexiao_count;
			}

			$hexiao_data = array();
			if($hexiao_count == $remain_hexiao_count){
				$hexiao_data['remain_hexiao_count'] = 0;
				$hexiao_data['is_hexiao_over'] = 1;
			}else{
				$hexiao_data['remain_hexiao_count'] = $remain_hexiao_count - $hexiao_count;
			}
			$hx_result = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('id'=>$hx_id))->save($hexiao_data);
		}else{
			if($hx_time == 0){
				$hexiao_data = array();
				$hexiao_data['is_hexiao_over'] = 1;
				$hexiao_count = 1;
				$hx_result = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('id'=>$hx_id))->save($hexiao_data);
			}else{
				$hx_result = 1;
				$hexiao_count = $hx_time;
			}
		}

	    if($hx_result !== false){
	        //添加订单核销记录表
	        $hexiao_record = array();
	        $hexiao_record['order_id'] = $order_goods_saleshexiao['order_id'];
	        $hexiao_record['order_goods_id'] = $order_goods_saleshexiao['order_goods_id'];
	        $hexiao_record['goods_id'] = $order_goods_saleshexiao['goods_id'];
	        $hexiao_record['hexiao_count'] = $hexiao_count;
	        $hexiao_record['smember_name'] = "后台操作";
	        $hexiao_record['is_admin'] = 1;
	        $hexiao_record['addtime'] = time();
	        M('eaterplanet_ecommerce_order_goods_saleshexiao_record')->add($hexiao_record);
	        return 1;
	    }else{
	        return 0;
	    }
	}
	/**
	 * 获取订单商品核销记录
	 */
	public function get_goods_hexiao_record(){
	    $data = array();
	    $order_id = I('request.id','');
	    $order_goods_id = I('request.order_goods_id','');
	    $hx_count = 0;
	    $list = M('eaterplanet_ecommerce_order_goods_saleshexiao_record')->where(array('order_id'=>$order_id,'order_goods_id'=>$order_goods_id))->order('addtime desc')->select();
	    foreach($list as $k=>$v){
	        $hx_count = $hx_count + $v['hexiao_count'];
	    }
	    $data['hx_count'] = $hx_count;
	    $data['hx_list'] = $list;
	    return $data;
	}
	/**
	 * 商品指定核销信息
	 * @param unknown $goods_id
	 */
	public function get_goods_assign_salesroom($goods_id){
	    $data = array();
	    //核销员
	    $item_smember = array();
	    //核销门店
	    $item_salesroom = M()->query("SELECT gs.*,sr.room_name,sr.room_logo FROM " . C('DB_PREFIX') .
	        "eaterplanet_ecommerce_goods_relative_salesroom as gs left join " . C('DB_PREFIX') ."eaterplanet_ecommerce_salesroom as sr on gs.salesroom_id=sr.id   WHERE gs.goods_id=".$goods_id." order by gs.id asc" );
	    foreach($item_salesroom as $k=>$v){
	        $item_salesroom_smember =  M()->query("SELECT grs.smember_id,sm.username FROM " . C('DB_PREFIX') .
	            "eaterplanet_ecommerce_goods_relative_smember as  grs left join " . C('DB_PREFIX') ."eaterplanet_ecommerce_salesroom_member as sm on grs.smember_id=sm.id ".
	            " left join " . C('DB_PREFIX') ."eaterplanet_ecommerce_member as m on sm.member_id=m.member_id ".
	            " WHERE grs.gr_id=".$v['id']." order by sm.id asc" );
	        foreach($item_salesroom_smember as $mk=>$mv){
	            $item_smember[$mv['smember_id']] = $mv['username'];
	        }
	    }
	    $data['salesroom_list'] = $item_salesroom;
	    $data['smember_list'] = $item_smember;
	    return $data;
	}

		//脱敏代码
	public function desensitize($string, $start = 0, $length = 0, $re = '*'){
		if(empty($string) || empty($length) || empty($re)) return $string;
		$end = $start + $length;
		$strlen = mb_strlen($string);
		$str_arr = array();
		for($i=0; $i<$strlen; $i++) {
			if($i>=$start && $i<$end)
				$str_arr[] = $re;
			else
				$str_arr[] = mb_substr($string, $i, 1);
		}
		return implode('',$str_arr);
	}

	public function num_to_rmb($num){
		$c1 = "零壹贰叁肆伍陆柒捌玖";
		$c2 = "分角元拾佰仟万拾佰仟亿";
		//精确到分后面就不要了，所以只留两个小数位
		$num = round($num, 2);
		//将数字转化为整数
		$num = $num * 100;
		if (strlen($num) > 10) {
			return "金额太大，请检查";
		}
		$i = 0;
		$c = "";
		while (1) {
			if ($i == 0) {
				//获取最后一位数字
				$n = substr($num, strlen($num)-1, 1);
			} else {
				$n = $num % 10;
			}
			//每次将最后一位数字转化为中文
			$p1 = substr($c1, 3 * $n, 3);
			$p2 = substr($c2, 3 * $i, 3);
			if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
				$c = $p1 . $p2 . $c;
			} else {
				$c = $p1 . $c;
			}
			$i = $i + 1;
			//去掉数字最后一位了
			$num = $num / 10;
			$num = (int)$num;
			//结束循环
			if ($num == 0) {
				break;
			}
		}
		$j = 0;
		$slen = strlen($c);
		while ($j < $slen) {
			//utf8一个汉字相当3个字符
			$m = substr($c, $j, 6);
			//处理数字中很多0的情况,每次循环去掉一个汉字“零”
			if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
				$left = substr($c, 0, $j);
				$right = substr($c, $j + 3);
				$c = $left . $right;
				$j = $j-3;
				$slen = $slen-3;
			}
			$j = $j + 3;
		}
		//这个是为了去掉类似23.0中最后一个“零”字
		if (substr($c, strlen($c)-3, 3) == '零') {
			$c = substr($c, 0, strlen($c)-3);
		}
		//将处理的汉字加上“整”
		if (empty($c)) {
			return "零元整";
		}else{
			return $c . "整";
		}
	}

}
?>
