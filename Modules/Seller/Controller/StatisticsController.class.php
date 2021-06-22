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
use Admin\Model\OrderModel;
class StatisticsController extends CommonController{

	protected function _initialize(){
		parent::_initialize();

	}

    public function index_data()
	{
		global $_W;
		global $_GPC;

		$type = 'normal';

		$member_count = D('Seller/User')->get_member_count();

		$total_where = "";


		switch( $type )
		{
			case 'normal':
				$result = array();
				//今日
				$today_time = strtotime( date('Y-m-d').' 00:00:00' );
				$today_member_count = D('Seller/User')->get_member_count(" and create_time > ".$today_time );
				//今日客户数量
				$result['today_member_count'] = $today_member_count;


				$result['total_tixian_money'] = 0;
				$result['total_commiss_money'] = 0;
				$result['total_order_money'] = 0;


				//今日付款订单
				//--begin
				if (defined('ROLE') && ROLE == 'agenter' )
				{
					$supper_info = get_agent_logininfo();

					$total_where = " and supply_id= ".$supper_info['id'];

					$order_ids_list = M()->query("select og.order_id,o.total,o.packing_fare,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
										"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id  and og.supply_id = ".$supper_info['id']." and o.pay_time > {$today_time} and o.type <> 'integral'  ");
					//and o.order_status_id in (1,4,6,7,11,14)
					$order_ids_arr = array();

					foreach($order_ids_list as $vv)
					{
						if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
						{
							$order_ids_arr[$vv['order_id']] = $vv;
						}
					}

					$result['today_pay_order_count'] = count($order_ids_arr);

					$today_pay_money = 0;
					foreach($order_ids_arr as $vv)
					{
						$today_pay_money += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money']+$vv['packing_fare'];
					}

					$today_pay_money = empty($today_pay_money) ? 0:$today_pay_money;

					$result['today_pay_money'] = sprintf("%.2f",$today_pay_money);

				}else{
					$today_pay_where = " {$total_where} and pay_time > {$today_time} and type <> 'integral' ";
					//and order_status_id in (1,4,6,7,11,14)

					$today_pay_order_count =  D('Seller/Order')->get_order_count($today_pay_where);
					$result['today_pay_order_count'] = $today_pay_order_count;

					//get_order_sum($field=' sum(total) as total ' , $where = '',$uniacid = 0)
					//total
					$today_pay_money_info = D('Seller/Order')->get_order_sum(' sum(total+shipping_fare-voucher_credit-fullreduction_money-fare_shipping_free+packing_fare) as total ' , $today_pay_where);

					$today_pay_money = empty($today_pay_money_info['total']) ? 0:$today_pay_money_info['total'];

					$result['today_pay_money'] = sprintf("%.2f",$today_pay_money);
				}

				//--end


				//$result['total_order_money'] = 0;

				if (defined('ROLE') && ROLE == 'agenter' )
				{
					$supper_info = get_agent_logininfo();

					$goods_count = M('eaterplanet_ecommerce_good_common')->where( array('supply_id' => $supper_info['id'] ) )->count();


					$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".
							C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id'] );

					$order_ids_arr = array();
					$order_ids_arr_dan = array();

					$total_money = 0;
					$total_count = 0;
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
						$sql = 'SELECT count(o.order_id) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_order as o  where ' .  " o.order_id in (".implode(',', $order_ids_arr_dan).") " ;

						$total_arr = M()->query($sql);


						$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money,o.packing_fare from ".C('DB_PREFIX').
								"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where og.order_id =o.order_id and og.supply_id = ".$supper_info['id']."  ");
						$total_count = count($order_ids_list);

						if( !empty($order_ids_list) )
						{
							foreach($order_ids_list as $vv)
							{
								$total_money += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money']+$vv['packing_fare'];
							}
						}
					}
					$result['total_order_count'] = $total_count;
					$result['total_order_money'] = empty($total_money) ? 0: sprintf("%.2f",$total_money);
				}else{

					$total_tixian_money_all = M('eaterplanet_community_head_tixian_order')->where( "state = 0" )->sum('money');

					$total_tixian_money_service_fare = M('eaterplanet_community_head_tixian_order')->where("state = 0")->sum('service_charge');


					$result['total_tixian_money'] = sprintf("%.2f",$total_tixian_money_all - $total_tixian_money_service_fare);


					/**
					$total_commiss_money_all =  pdo_fetchcolumn('SELECT sum(money) as total_money FROM ' . tablename('eaterplanet_community_head_tixian_order') .
				    ' WHERE uniacid= '.$_W['uniacid'] . "  and state = 1 ");
					$total_commiss_money_service_fare =  pdo_fetchcolumn('SELECT sum(service_charge) as total_service_charge FROM ' . tablename('eaterplanet_community_head_tixian_order') .
				    ' WHERE uniacid= '.$_W['uniacid'] . "  and state = 1 ");

					$result['total_commiss_money'] = sprintf("%.2f",$total_commiss_money_all - $total_commiss_money_service_fare);
					**/

					$total_commiss_money_all = M('eaterplanet_community_head_commiss_order')->where( "state = 1 or state =0" )->sum('money');

					$result['total_commiss_money'] = sprintf("%.2f",$total_commiss_money_all);





					//C('DB_PREFIX')
					$sq_s = "SELECT sum(total+shipping_fare-voucher_credit-fullreduction_money-fare_shipping_free+packing_fare) as total FROM ".
							C('DB_PREFIX')."eaterplanet_ecommerce_order where order_status_id in (1,4,6,11,12,14) and type <> 'integral' ";

					$total_order_money_arr = M()->query($sq_s);

					$total_order_money = $total_order_money_arr[0]['total'];
					$result['total_order_money'] = empty($total_order_money) ? 0: sprintf("%.2f",$total_order_money);

				}



				//客户数量
				$result['member_count'] = $member_count;
				//商品数量

				if (defined('ROLE') && ROLE == 'agenter' ) {
					$supper_info = get_agent_logininfo();

					$goods_count = M('eaterplanet_ecommerce_good_common')->where( array('supply_id' => $supper_info['id'] ) )->count();

				}else{
					$goods_count = D('Seller/Goods')->get_goods_count();
				}


				$result['goods_count'] = $goods_count;

				//团长数量
				//ims_ eaterplanet_community_head

				$community_head_count =	M('eaterplanet_community_head')->count();

	            $result['community_head_count'] = $community_head_count;

				//待付款订单
				if (defined('ROLE') && ROLE == 'agenter' )
				{
					$supper_info = get_agent_logininfo();

					$total_where = " and supply_id= ".$supper_info['id'];

					$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
										"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id']." and o.order_status_id =3 ");
					$order_ids_arr = array();

					foreach($order_ids_list as $vv)
					{
						if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
						{
							$order_ids_arr[$vv['order_id']] = $vv;
						}
					}

					$result['wait_pay_order_count'] = count($order_ids_arr);

				}else{
					$wait_pay_order_count = D('Seller/Order')->get_order_count(" and order_status_id =3 {$total_where}");
					$result['wait_pay_order_count'] = $wait_pay_order_count;
				}

				//待发货订单 1
				if (defined('ROLE') && ROLE == 'agenter' )
				{

					$supper_info = get_agent_logininfo();

					$total_where = " and supply_id= ".$supper_info['id'];

					$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
										"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id']." and o.order_status_id =1 ");
					$order_ids_arr = array();
					foreach($order_ids_list as $vv)
					{
						if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
						{
							$order_ids_arr[$vv['order_id']] = $vv;
						}
					}
					$result['wait_order_count'] = count($order_ids_arr);
				}else{
					$wait_order_count = D('Seller/Order')->get_order_count(" and order_status_id =1 and type != 'ignore' ");

					$order_info = M('eaterplanet_ecommerce_order')->where( "order_status_id =1 and type != 'ignore'")->order('order_id desc')->find();
					$result['wait_order_type'] = $order_info['type'];
					$result['wait_order_count'] = $wait_order_count;
				}




				//售后中订单
				if (defined('ROLE') && ROLE == 'agenter' )
				{

					$supper_info = get_agent_logininfo();

					$total_where = " and supply_id= ".$supper_info['id'];

					$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
										"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id']." and o.order_status_id =12 ");
					$order_ids_arr = array();
					foreach($order_ids_list as $vv)
					{
						if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
						{
							$order_ids_arr[$vv['order_id']] = $vv;
						}
					}
					$result['after_sale_order_count'] = count($order_ids_arr);
				}else{
					$after_sale_order_count = D('Seller/Order')->get_order_count(" and order_status_id = 12 ");
					$result['after_sale_order_count'] = $after_sale_order_count;
				}



				if (defined('ROLE') && ROLE == 'agenter' )
				{
					$supper_info = get_agent_logininfo();

					$total_where = " and supply_id= ".$supper_info['id'];

					$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
										"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id  and og.supply_id = ".$supper_info['id']." and o.order_status_id =6 ");
					$order_ids_arr = array();
					foreach($order_ids_list as $vv)
					{
						if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
						{
							$order_ids_arr[$vv['order_id']] = $vv;
						}
					}
					$result['wai_comment_order_count'] = count($order_ids_arr);
				}else{
					$wai_comment_order_count = D('Seller/Order')->get_order_count(" and order_status_id = 6 ");
					$result['wai_comment_order_count'] = $wai_comment_order_count;
				}


				$result['wait_shen_order_comment_count'] = D('Seller/Order')->get_wait_shen_order_comment();

				$result['stock_goods_count'] = D('Seller/Goods')->get_goods_count('  and grounding =2 ');


				$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - 7 * 86400;
				$end_time = time();

				//7天订单数量
				if (defined('ROLE') && ROLE == 'agenter' ) {

					$supper_info = get_agent_logininfo();

					$total_where = " and supply_id= ".$supper_info['id'];

					$order_ids_list = M()->query("select og.order_id,o.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
										"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id']." and o.date_added>={$begin_time} and o.date_added< {$end_time} ");
					$order_ids_arr = array();
					foreach($order_ids_list as $vv)
					{
						if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
						{
							$order_ids_arr[$vv['order_id']] = $vv;
						}
					}
					$result['seven_order_count'] = count($order_ids_arr);
				}else{
					$seven_order_count = D('Seller/Order')->get_order_count(" and date_added>={$begin_time} and date_added< {$end_time} {$total_where} ");
					$result['seven_order_count'] = $seven_order_count;
				}

				//7天的订单总金额
				if (defined('ROLE') && ROLE == 'agenter' ) {

					$supper_info = get_agent_logininfo();

					$total_where = " and supply_id= ".$supper_info['id'];

					$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
										"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id and og.supply_id = ".$supper_info['id']." and o.pay_time > {$begin_time}  ");
					//and o.order_status_id in (1,4,6,11,14)

					$order_ids_arr = array();

					$seven_pay_money= 0;

					foreach($order_ids_list as $vv)
					{
						if( empty($order_ids_arr) || !isset($order_ids_arr[$vv['order_id']]) )
						{
							$order_ids_arr[$vv['order_id']] = $vv;
							$seven_pay_money += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money'];
						}
					}

					$seven_pay_money = empty($seven_pay_money) ? 0:$seven_pay_money;
					$result['seven_pay_money'] = $seven_pay_money;
				}else{
					$seven_pay_where = "  pay_time > {$begin_time} ";
					//and order_status_id in (1,4,6,11,14)
					$seven_pay_money = M('eaterplanet_ecommerce_order')->where($seven_pay_where)->sum('total+shipping_fare-voucher_credit-fullreduction_money-fare_shipping_free');

					//->get_order_sum(' sum() as total ' , $seven_pay_where);

					$seven_pay_money = empty($seven_pay_money) ? 0:$seven_pay_money;

					$result['seven_pay_money'] = sprintf("%.2f",$seven_pay_money);
				}




				//7天订单总退款金额

				if (defined('ROLE') && ROLE == 'agenter' ) {
					$supper_info = get_agent_logininfo();

					$total_where = " and supply_id= ".$supper_info['id'];

					$seven_refund_money_arr = M()->query("select sum(money) as money from ".C('DB_PREFIX').
										"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund as ogr where og.order_goods_id = ogr.order_goods_id  and og.supply_id = ".$supper_info['id']." and ogr.addtime > {$begin_time}  ");

					$seven_refund_money= $seven_refund_money_arr[0]['money'];


					$seven_refund_money = empty($seven_refund_money) ? 0:$seven_refund_money;
					$result['seven_refund_money'] = sprintf("%.2f",$seven_refund_money);
				}else{
					$seven_refund_pay_where = " addtime > {$begin_time}  ";

					$seven_refund_money = M('eaterplanet_ecommerce_order_goods_refund')->where($seven_refund_pay_where)->sum('money');


					$seven_refund_money = empty($seven_refund_money) ? 0:$seven_refund_money;

					$result['seven_refund_money'] = sprintf("%.2f",$seven_refund_money);
				}


				$goods_stock_notice = D('Home/Front')->get_config_by_name('goods_stock_notice');
				if( empty($goods_stock_notice) )
				{
					$goods_stock_notice = 0;
				}
				//库存预警商品数量
				$goods_stock_notice_count = D('Seller/Goods')->get_goods_count(" and grounding = 1 and total <= ".$goods_stock_notice." and grounding = 1  and type = 'normal' ");
				$result['goods_stock_notice_count'] = $goods_stock_notice_count;



				$apply_count = M('eaterplanet_community_head_tixian_order')->where("state=0")->count();

				$result['apply_count'] = $apply_count;

				echo json_encode( array('code' => 0, 'data' => $result) );
				die();
				break;

			case 'pintuan':
				$result = array();

				echo json_encode( array('code' => 0, 'data' => $result) );
				die();
				break;
		}

	}




	function order_buy_data()
	{
		//成交量（件） //成交额（元） 人均消费
		$gpc = I('request.');

		$type = isset($gpc['type']) ? $gpc['type']:'normal';
		$s_index = isset($gpc['s_index']) ? $gpc['s_index']:2;

		$begin_time = 0;
		$end_time = 0;

		//var json = {"success":true,"data":{"date":"0001-01-01 00:00:00","visits":3080,"orderUsers":24,"orderCount":52,"orderProducts":69,"orderAmount":14986.83,"payUsers":16,"payOrders":25,"payProducts":36,"payAmount":4380.31,"refundProducts":4,"refundOrderCounts":8,"refundAmount":1899.04,"refundRate":32.00,"preOrderRate":175.21,"preProductRate":121.68,"jointRate":1.44,"orderRate":1.69,"payRate":48.08,"tradeRate":0.81,"payAmountRank":1,"brokerageAmount":0.00,"lines":{"payAmountLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0.00,3196.00,0.20,162.14,379.01,642.96,0.00,0.0]}]},"payUserLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0,1,2,2,6,5,0,0]}]},"payProductLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0,3,2,13,10,8,0,0]}]},"orderRateLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0.0,4.00,1.22,1.53,2.74,1.52,1.00,0.0]}]},"payRateLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0.0,75.00,33.33,77.78,35.00,50.0,0.0,0.0]}]},"tradeRateLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0.0,3.00,0.41,1.19,0.96,0.76,0.0,0.0]}]}}}};

		switch( $s_index )
		{
			case 0:
				//今日
				$begin_time = strtotime( date('Y-m-d').' 00:00:00' );
				$end_time = $begin_time + 86400;
			break;
			case 1:
				//昨日
				$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - 86400;
				$end_time = $begin_time + 86400;
			break;
			case 2:
				//最近七日
				$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - 7 * 86400;
				$end_time = time();
			break;
			case 3:
			//本月

				$begin_time = strtotime( date('Y-m').'-01 00:00:00' );
				$end_time = time();
			break;
		}



		switch( $type )
		{
			case 'normal':
				$result = array();


				if (!defined('ROLE') && ROLE != 'agenter' )
				{

					$where = " and type = 'normal' and date_added >= {$begin_time} and date_added <={$end_time} ";

					$count = D('Seller/Order')->get_order_count($where);

					$sum_info = D('Seller/Order')->get_order_sum(' sum(total+shipping_fare-voucher_credit-fullreduction_money-fare_shipping_free) as total ' , $where);
					$total = $sum_info['total'];


				}else{
					$supper_info = get_agent_logininfo();

					$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
										"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id  and og.supply_id = ".$supper_info['id']." and  og.addtime >= {$begin_time} and og.addtime <={$end_time}  ");


					$count = count($order_ids_list);
					$order_ids_arr = array();

					$sum_info = array('total' => 0);
					foreach($order_ids_list as $vv)
					{
						$sum_info['total'] += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money'];
					}
					$total = $sum_info['total'];

				}


				if($count > 0)
				{
					$per_money = $total / $count;
				} else{
					$per_money = 0;
				}
				$result['count'] = $count;
				$result['total'] = round($total,2);
				$result['per_money'] = round($per_money,2);




				echo json_encode( array('code' => 0, 'data' => $result) );
				die();
				break;
			case 'pintuan':
				$result = array();
				$where = " and type = 'pintuan' and date_added >= {$begin_time} and date_added <={$end_time} ";
				$count = load_model_class('order')->get_order_count($where);

				//total
				$sum_info = load_model_class('order')->get_order_sum(' sum(total) as total ' , $where);
				$total = $sum_info['total'];

				if($count > 0)
				{
					$per_money = $total / $count;
				} else{
					$per_money = 0;
				}
				$result['count'] = $count;
				$result['total'] = round($total,2);
				$result['per_money'] = round($per_money,2);

				echo json_encode( array('code' => 0, 'data' => $result) );
				die();

				break;
		}
		//s_index
	}

	//----


	public function load_echat_member_incr()
	{

		$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - 10 * 86400;
		$end_time = time();

		$date_arr = array();
		$member_count_arr = array();

		for($i =10; $i>=1; $i--)
		{
			$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - $i * 86400;
			$end_time = $begin_time + 86400;

			$member_count = M('eaterplanet_ecommerce_member')->where("create_time >= ".$begin_time." and create_time < ".$end_time)->count();

			$date_arr[] = date('m-d', $begin_time);

			$member_count_arr[] = $member_count;
		}

		echo json_encode( array('code' => 0, 'date_arr' => $date_arr, 'member_count' => $member_count_arr ) );
		die();
	}

	public function load_echat_head_incr()
	{

		$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - 7 * 86400;
		$end_time = time();

		$date_arr = array();
		$member_count_arr = array();

		for($i =7; $i>=1; $i--)
		{
			$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - $i * 86400;
			$end_time = $begin_time + 86400;

			$member_count = M('eaterplanet_community_head')->where( "state = 1 and addtime >= ".$begin_time." and addtime < ".$end_time )->count();

			$date_arr[] = date('m-d', $begin_time);

			$member_count_arr[] = $member_count;
		}

		echo json_encode( array('code' => 0, 'date_arr' => $date_arr, 'member_count' => $member_count_arr ) );
		die();
	}

	public function load_echat_month_head_sales()
	{


		$type =  I('request.type');

		if( $type == 1)
		{
			$begin_time = strtotime( date('Y-m').'-01 00:00:00' );
			$date_month =date('Y-m',$begin_time);
		}else{
			//$begin_time= strtotime( date("Y-m", strtotime("-1 month")) .'-01 00:00:00' );

			$begin_time= strtotime( "first day of last month" ) ;

			$date_month =date('Y-m',$begin_time);
		}


		$end_time = time();

		//eaterplanet_ecommerce_order  1 4 6 11 14  date_added

		$sql = " select sum(total+shipping_fare-voucher_credit-fullreduction_money-fare_shipping_free) as total,head_id from ".C('DB_PREFIX').
				"eaterplanet_ecommerce_order where  date_added >= {$begin_time} and date_added <={$end_time} and order_status_id in(6,11)
				group by head_id order by total desc limit 10 ";

		$list = M()->query($sql);

		$total = 0;

		foreach( $list as $key => $val )
		{
			$hd_info = M('eaterplanet_community_head')->field('community_name,head_name')->where( array('id' => $val['head_id'] ) )->find();


			$val['community_name'] = $hd_info['community_name'];
			$val['head_name'] = $hd_info['head_name'];

			$val['total'] = sprintf('%.2f',$val['total'] );


			$total += $val['total'];

			$list[$key] = $val;
		}

		$total = sprintf('%.2f',$total);

		echo json_encode( array('code' => 0, 'list' => $list, 'total' => $total,'month' => $date_month) );
		die();

	}

	//--

	public function load_echat_month_goods_sales()
	{
		$type =  I('request.type');


		if( $type == 1)
		{
			$begin_time = strtotime( date('Y-m').'-01 00:00:00' );
			$date_month =date('Y-m',$begin_time);

			$end_time = time();
		}else{
			//$begin_time= strtotime( date("Y-m", strtotime("-1 month")) .'-01 00:00:00' );
			//2020-02-01 10:32:28

			$month_begin_time = strtotime( date('Y-m').'-01 00:00:00' );

			$begin_time= strtotime( "first day of last month" ) ;

			$date_month =date('Y-m',$begin_time);

			$begin_time = strtotime( $date_month.'-01 00:00:00' );

			$end_time = $month_begin_time -1;


		}




		//eaterplanet_ecommerce_order  1 4 6 11 14  date_added

		$sql = "SELECT og.goods_id,og.name ,sum(og.quantity) as total_quantity,sum( og.total + og.shipping_fare - og.voucher_credit - og.fullreduction_money - og.score_for_money ) as total
				FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og ,".C('DB_PREFIX')."eaterplanet_ecommerce_order as o
				where og.order_id = o.order_id  and o.type != 'integral' and o.date_added >= {$begin_time} and o.date_added <={$end_time}  and o.order_status_id in(6,11) group by og.goods_id order by total desc limit 10 ";

		$list = M()->query($sql);

		$total = 0;
		$total_quantity = 0;
		$total_money = 0;
		$result['total'] = 0;
		$result['total_quantity'] = 0;

		foreach( $list as $key => $val )
		{

			$val['community_name'] = $hd_info['name'];
			$val['head_name'] = $hd_info['name'];
			$val['name'] = mb_substr($val['name'],0,8,'utf-8').'  '.'销量:'. $val['total_quantity'];
			$val['total_quantity'] = sprintf('%.0f',$val['total_quantity'] );
			$val['total'] = sprintf('%.2f',$val['total'] );

			$total_money += $val['total_money'];
			$total += $val['total'];
			$total_quantity += $val['total_quantity'];
			$list[$key] = $val;

			$result['total'] = sprintf('%.0f',$val['total'] );
			$result['total_quantity'] =$total_quantity;
			$result['total_money'] = sprintf('%.0f',$val['total_money'] );
		}

		 $last_index_sort = array_column($list, 'total_quantity');
         array_multisort($last_index_sort, SORT_DESC, $list);



		echo json_encode( array('code' => 0, 'list' => $list, 'total_quantity' => $total_quantity,'total' => $total,'month' => $date_month) );
		die();



	}


	//---

	function load_goods_chart()
	{
		//成交量（件） //成交额（元） 人均消费


		$type = 'normal';

		$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - 10 * 86400;
		$end_time = time();


		switch( $type )
		{
			case 'normal':
				$result = array();


				$count_value = array();

				$price_key = array();
				$price_value = array();
				$price_value_2 = array();


				for($i =15; $i>=1; $i--)
				{
					$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - $i * 86400;
					$end_time = $begin_time + 86400;
					//supply_id

					if (!defined('ROLE') || ROLE != 'agenter' ) {


						$where = " and  date_added >= {$begin_time} and date_added <={$end_time} and order_status_id in(1,4,6,11,14) ";
						$where_refund = " and  date_added >= {$begin_time} and date_added <={$end_time} and order_status_id in(7) ";

						$count = D('Seller/Order')->get_order_count($where);

						$sum_info = D('Seller/Order')->get_order_sum(' sum(total+shipping_fare-voucher_credit-fullreduction_money-fare_shipping_free) as total ' , $where);
						$refund_sum_info = D('Seller/Order')->get_order_sum(' sum(total+shipping_fare-voucher_credit-fullreduction_money-fare_shipping_free) as total ' , $where_refund);

					}else{
						$supper_info = get_agent_logininfo();

						$order_ids_list = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
											"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id  and og.supply_id = ".$supper_info['id']." and  og.addtime >= {$begin_time} and og.addtime <={$end_time} and o.order_status_id in(1,4,6,11,14) ");

						$order_ids_list2 = M()->query("select og.order_id,og.total,og.shipping_fare,og.voucher_credit,og.fullreduction_money from ".C('DB_PREFIX').
											"eaterplanet_ecommerce_order_goods as og , ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  where  og.order_id =o.order_id  and og.supply_id = ".$supper_info['id']." and  og.addtime >= {$begin_time} and og.addtime <={$end_time} and o.order_status_id in(7) ");

						if( empty($order_ids_list2) )
						{
							$refund_sum_info = 0;
						}else{

							$order_ids_arr = array();

							$refund_sum_info = array('total' => 0);
							foreach($order_ids_list2 as $vv)
							{
								$refund_sum_info['total'] += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money'];
							}

						}



						if( empty($order_ids_list) )
						{
							$count = 0;
							$sum_info = 0;
						}else{
							$count = count($order_ids_list);
							$order_ids_arr = array();

							$sum_info = array('total' => 0);
							foreach($order_ids_list as $vv)
							{
								$sum_info['total'] += $vv['total']+$vv['shipping_fare']-$vv['voucher_credit']-$vv['fullreduction_money'];
							}

						}


					}


					if( empty($sum_info) || empty($sum_info['total']) || $sum_info['total'] < 0)
					{
						$total = 0;
					}else{
						$total = $sum_info['total'];
					}

					if( empty($refund_sum_info) || empty($refund_sum_info['total']) || $refund_sum_info['total'] < 0)
					{
						$refund_total = 0;
					}else{
						$refund_total = $refund_sum_info['total'];
					}


					$price_key[]  = date('m-d', $begin_time);
					$count_value[] = $count;
					$price_value[] = sprintf('%.2f',$total);
					$price_value_2[] = sprintf('%.2f',$refund_total);

				}

				//$json = '{"success":true,"data":{"date":"0001-01-01 00:00:00","visits":3080,"orderUsers":24,"orderCount":52,"orderProducts":69,"orderAmount":14986.83,"payUsers":16,"payOrders":25,"payProducts":36,"payAmount":4380.31,"refundProducts":4,"refundOrderCounts":8,"refundAmount":1899.04,"refundRate":32.00,"preOrderRate":175.21,"preProductRate":121.68,"jointRate":1.44,"orderRate":1.69,"payRate":48.08,"tradeRate":0.81,"payAmountRank":1,"brokerageAmount":0.00,"lines":{"payAmountLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0.00,3196.00,0.20,162.14,379.01,642.96,0.00,0.0]}]},"payUserLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0,1,2,2,6,5,0,0]}]},"payProductLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0,3,2,13,10,8,0,0]}]},"orderRateLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0.0,4.00,1.22,1.53,2.74,1.52,1.00,0.0]}]},"payRateLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0.0,75.00,33.33,77.78,35.00,50.0,0.0,0.0]}]},"tradeRateLine":{"xAxisData":["12-01","12-02","12-03","12-04","12-05","12-06","12-07","12-08"],"seriesData":[{"data":[0.0,3.00,0.41,1.19,0.96,0.76,0.0,0.0]}]}}}}';

				$result['lines'] = array();
				$result['lines']['payAmountLine']['xAxisData'] = $price_key;
				$result['lines']['payAmountLine']['seriesData'][0]['data'] = $price_value;
				$result['lines']['payAmountLine']['seriesData'][1]['data'] = $price_value_2;





				$result['price_key'] =$price_key;
				$result['count_value'] =$count_value;
				$result['price_value'] =$price_value;
				$result['price_value_2'] =$price_value_2;
				echo json_encode( array('code' => 0, 'data' => $result) );
				die();
				break;

			case 'pintuan':
				$result = array();


				$count_value = array();

				$price_key = array();
				$price_value = array();

				for($i =7; $i>=1; $i--)
				{
					$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - $i * 86400;
					$end_time = $begin_time + 86400;
					$where = " and type = 'pintuan' and date_added >= {$begin_time} and date_added <={$end_time} ";
					$count = load_model_class('order')->get_order_count($where);

					$sum_info = load_model_class('order')->get_order_sum(' sum(total) as total ' , $where);

					if( empty($sum_info) || empty($sum_info['total']))
					{
						$total = 0;
					}else{
						$total = $sum_info['total'];
					}

					$price_key[]  = date('Y-m-d', $begin_time);
					$count_value[] = $count;
					$price_value[] = sprintf('%.2f',$total);
				}
				//{"price_key":["2018-10-26","2018-10-27","2018-10-28","2018-10-29","2018-10-30","2018-10-31","2018-11-01"],"price_value":[0,0,0,0,0,0,0],"count_value":[0,0,0,0,0,0,0]}

				$result['price_key'] =$price_key;
				$result['count_value'] =$count_value;
				$result['price_value'] =$price_value;

				echo json_encode( array('code' => 0, 'data' => $result) );
				die();
				break;

		}
		//s_index
	}

	function load_goods_paihang()
	{
		//成交量（件） //成交额（元） 人均消费
		$gpc = I('request.');

		$type = $gpc['type'];
		$s_index = $gpc['s_index'];

		$begin_time = 0;
		$end_time = 0;

		switch( $s_index )
		{
			case 0:
				//今日
				$begin_time = strtotime( date('Y-m-d').' 00:00:00' );
				$end_time = $begin_time + 86400;
			break;
			case 1:
				//昨日
				$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - 86400;
				$end_time = $begin_time + 86400;
			break;
			case 2:
				//最近七日
				$begin_time = strtotime( date('Y-m-d').' 00:00:00' ) - 7 * 86400;
				$end_time = time();
			break;

		}

		$list = array();
		switch( $type )
		{
			case 'normal':
				$result = array();

				if (!defined('ROLE') || ROLE != 'agenter' )
				{
					$where = " and  addtime >= {$begin_time} and addtime <={$end_time} ";
				}else{
					$supper_info = get_agent_logininfo();

					$where = " and supply_id= ".$supper_info['id']." and  addtime >= {$begin_time} and addtime <={$end_time} ";

				}


				$list = D('Seller/Order')->get_order_goods_group_paihang($where );


				break;
			case 'pintuan':
				$result = array();
				$where = " and goods_type='pintuan' and  addtime >= {$begin_time} and addtime <={$end_time} ";

				$list = D('Seller/Order')->get_order_goods_group_paihang($where );


				break;
		}

		$html = '';
		if( !empty($list) )
		{
			$i =1;
			foreach($list as $val)
			{
				$html .= "<tr>";
				$html .= "	<td>{$i}</td>";
				$html .= "		<td><a href='#'>".$val['name']."</a></td>";
				$html .= "		<td>".$val['total_quantity']."</td>";
				$html .= "		<td class='text-warning'>".$val['m_total']."</td>";
				$html .= "	</tr>";
				$i++;
			}
		}

		echo json_encode( array('code' => 0, 'html' => $html) );
		die();

	}

}
?>
