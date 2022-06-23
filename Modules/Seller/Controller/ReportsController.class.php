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
namespace Seller\Controller;

class ReportsController extends CommonController{

	protected function _initialize(){
		parent::_initialize();

	}

    public function index()
	{
		$_GPC = I('request.');

		$condition = " and type !=  'integral' ";

		$cur_controller = 'reports/index';
		//今天开始时间

			$today = array();
			$today['egt'] = strtotime(date('Y-m-d 00:00:00'));
			$today['lt'] = strtotime(date('Y-m-d 23:59:59'));

		//本周时间
			$arr=array();
			$thisweek = array();
			$arr=getdate();
			$num=$arr['wday'];
			if(empty($num)){
				$num =7;
			}
			$thisweek['egt'] = $today['egt']-($num-1)*24*60*60;

			//$thisweek['lt'] = $today['lt']+(7-$num)*24*60*60;
			$thisweek['lt'] = strtotime(date('Y-m-d H:i:s'));

		if (empty($_GPC['reports_index']) || $_GPC['reports_index']=='0'){

			//每天所有订单
			$day_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money , sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$thisweek['egt']." and date_added < ".$thisweek['lt']." ".$condition." group by date order by date asc");

			for($i=0;$i<$num;$i++){

				$day_start = date("Y-m-d" , $today['egt']-($num-1-$i)*24*60*60);

				$isin = $this->deep_in_array($day_start,$day_info);
				if($isin){

				}else{

					$day_info[] =array(
						'date' => $day_start,
						'count' => 0,
						'total' => 0,
						'shipping_fare' => 0,
						'voucher_credit' => 0,
						'fullreduction_money' => 0,
						'score_for_money' => 0,
						'fare_shipping_free' => 0,
						'packing_fare' => 0,
					);
				}

			}

			foreach($day_info as $val){
				$key_arrays[]=$val['date'];
			}
			array_multisort($key_arrays,SORT_ASC,SORT_NUMERIC,$day_info);


			//总单数和下单金额
			$zongdanshu = 0;
			$zongxiadan = 0;
			foreach($day_info as $val1) {

				$zongdanshu += $val1['count'];
				$zongxiadan += $val1['total']+$val1['shipping_fare']+$val1['packing_fare']-$val1['voucher_credit']-$val1['fullreduction_money'] - $val1['score_for_money'] - $val1['fare_shipping_free'];

			}

			//有订单的所有日期
			//$day_info2 = M()->query("select from_unixtime( pay_time, '%Y-%m-%d' ) as date from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where pay_time > ".$thisweek['egt']." and pay_time < ".$thisweek['lt']." ".$condition." group by date order by date asc");
			for($i=0;$i<=$num;$i++){
				$day_info2[$i] = array(
					 "date" => date("Y-m-d" , $today['egt']-($num-1-$i)*24*60*60 ),
				);
			}

			foreach($day_info2 as $key =>$day) {

				 $day["egt"]=strtotime(date($day["date"],time()));
				 $day["lt"]=$day["egt"]+(60*60*24)-1;

				//每天退款单数
				$day_info3 = M()->query("select count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money  from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$day['egt']." and date_added < ".$day['lt']." ".$condition." and (order_status_id in (7,8,9,10,12,13))");

					if($day_info3){
						$daytui = 0;
						$daytuikuan =0;
					}


					//每天取消单数
					$day_info4 = M()->query("select count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money , sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order
							where date_added > ".$day['egt']." and date_added < ".$day['lt']." ".$condition." and (order_status_id  = 5)");

					if($day_info4){
						$dayqu = 0;
						$dayquxiao =0;
					}

					//取消单数
				    $dayqu = $day_info4[0]['count'];
					//取消金额
					$dayquxiao = $day_info4[0]['total']+$day_info4[0]['shipping_fare']+$day_info4[0]['packing_fare']-$day_info4[0]['voucher_credit']-$day_info4[0]['fullreduction_money']-$day_info4[0]['score_for_money']-$day_info4[0]['fare_shipping_free'];


					//退款中 金额
					$daywait_refund_money_arr = M()->query("select sum(ref_money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund
							where state =0 and addtime >= ".$day['egt']." and addtime < ".$day['lt']."  ");

					$daywait_refund_money = $daywait_refund_money_arr[0]['total_money'];

					//退款中  订单数量
					$daywait_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund where state =0 and addtime >= ".$day['egt']."
						and addtime < ".$day['lt']."   ");

					$daywait_refund_count = count($daywait_refund_arr);

					//已退款 金额
					$dayhas_refund_money_arr = M()->query("select sum(money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime >= ".$day['egt']."
						and addtime < ".$day['lt']."  ");

					$dayhas_refund_money = $dayhas_refund_money_arr[0]['total_money'];

					//已退款 订单数量
					$dayhas_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime >= ".$day['egt']."
						and addtime < ".$day['lt']."  group by order_id ");

					$dayhas_refund_count = count($dayhas_refund_arr);



					$daylist[$key] = array(
						'daywait_refund_money' => $daywait_refund_money,
						'daywait_refund_count' => $daywait_refund_count,
						'dayhas_refund_money' => $dayhas_refund_money,
						'dayhas_refund_count' => $dayhas_refund_count,
						'dayqu' => $dayqu,
						'dayquxiao' => $dayquxiao,

					);
			}

			$list = array();
			$list['day_info'] = $day_info;
			//合并两个数组
			$list2 = array();
			foreach($list['day_info'] as $k=>$v){
				$list2[] = array_merge($v,$daylist[$k]);
			}


			//退款中 金额
			$wait_refund_money_arr = M()->query("select sum(ref_money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund
					where state =0 and addtime > ".$thisweek['egt']." and addtime < ".$thisweek['lt']."  ");

			$wait_refund_money = $wait_refund_money_arr[0]['total_money'];

			//退款中  订单数量
			$wait_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund where state =0 and addtime > ".$thisweek['egt']."
				and addtime < ".$thisweek['lt']."   ");

			$wait_refund_count = count($wait_refund_arr);

			//已退款 金额
			$has_refund_money_arr = M()->query("select sum(money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime > ".$thisweek['egt']."
				and addtime < ".$thisweek['lt']."  ");

			$has_refund_money = $has_refund_money_arr[0]['total_money'];

			//已退款 订单数量
			$has_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime > ".$thisweek['egt']."
				and addtime < ".$thisweek['lt']."  group by order_id ");

			$has_refund_count = count($has_refund_arr);

			$this->wait_refund_money = $wait_refund_money;
			$this->wait_refund_count = $wait_refund_count;
			$this->has_refund_money = $has_refund_money;
			$this->has_refund_count = $has_refund_count;





			//小计
			/*$subtotal_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where pay_time > ".$thisweek['egt']." and pay_time < ".$thisweek['lt']." ".$condition." and (order_status_id in (1,2,3,4,6,11,14)) group by date order by date asc" );
			$xaiojishu =0;
			$xaioji =0;
			foreach($subtotal_info as $val3) {

					$xaiojishu += $val3['count'];
					$xaioji += $val3['total']+$val3['shipping_fare']-$val3['voucher_credit']-$val3['fullreduction_money']- $val3['score_for_money'];
			}*/

			//取消订单
			$quxiao_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money , sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$thisweek['egt']." and date_added < ".$thisweek['lt']." ".$condition." and (order_status_id = 5) group by date order by date asc");


			$quxiaoshu =0;
			$quxiao =0;
			foreach($quxiao_info as $val4) {

				    $quxiaoshu += $val4['count'];
					$quxiao += $val4['total']+$val4['shipping_fare']+$val4['packing_fare']-$val4['voucher_credit']-$val4['fullreduction_money']- $val4['score_for_money']- $val4['fare_shipping_free'];
			}

			//小计 = 下单金额-取消金额
			$xaiojishu = $zongdanshu - $quxiaoshu;
			$xaioji = $zongxiadan - $quxiao;

			$tabid = 0;

		}


		//上周时间
		if($_GPC['reports_index'] == 1){

			$lastweek['egt'] = $thisweek['egt']-7*24*60*60;
			$lastweek['lt'] = $thisweek['egt']-1;


			//每天所有订单
			$day_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money , sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$lastweek['egt']." and date_added < ".$lastweek['lt']." ".$condition." group by date order by date asc");

			for($i=0;$i<7;$i++){

				$day_start = date("Y-m-d" , $thisweek['egt']-(7-$i)*24*60*60);

				$isin = $this->deep_in_array($day_start,$day_info);
				if($isin){

				}else{

					$day_info[] =array(
						'date' => $day_start,
						'count' => 0,
						'total' => 0,
						'shipping_fare' => 0,
						'voucher_credit' => 0,
						'fullreduction_money' => 0,
						'score_for_money' => 0,
						'fare_shipping_free' => 0,
						'packing_fare' => 0,
					);
				}

			}

			foreach($day_info as $val){
				$key_arrays[]=$val['date'];
			}
			array_multisort($key_arrays,SORT_ASC,SORT_NUMERIC,$day_info);


			//总单数和下单金额
			$zongdanshu = 0;
			$zongxiadan = 0;
			foreach($day_info as $val1) {

					$zongdanshu += $val1['count'];
					$zongxiadan += $val1['total']+$val1['shipping_fare']+$val1['packing_fare']-$val1['voucher_credit']-$val1['fullreduction_money'] - $val1['score_for_money']- $val1['fare_shipping_free'];

			}

			//有订单的所有日期
			//$day_info2 = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$lastweek['egt']." and date_added < ".$lastweek['lt']." ".$condition." group by date order by date asc" );

			// date("Y-m-d H:i",$todaytime)
			for($i=0;$i<7;$i++){
				$day_info2[$i] = array(
					 "date" => date("Y-m-d" , $thisweek['egt']-(7-$i)*24*60*60 ),
				);
			}





			foreach($day_info2 as $key =>$day) {
					 $day["egt"]=strtotime(date($day["date"],time()));
					 $day["lt"]=$day["egt"]+(60*60*24)-1;

					//每天退款单数
					$day_info3 = M()->query("select count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$day['egt']." and date_added < ".$day['lt']." ".$condition." and (order_status_id in (7,8,9,10,12,13))" );

						if($day_info3){
							$daytui = 0;
							$daytuikuan =0;
						}



					//每天取消单数
					$day_info4 = M()->query("select count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money , sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$day['egt']." and date_added < ".$day['lt']." ".$condition." and (order_status_id  = 5)" );

						if($day_info4){
							$dayqu = 0;
							$dayquxiao =0;
						}

						//取消单数
						$dayqu = $day_info4[0]['count'];
						//取消金额
						$dayquxiao = $day_info4[0]['total']+$day_info4[0]['shipping_fare']+$day_info4[0]['packing_fare']-$day_info4[0]['voucher_credit']-$day_info4[0]['fullreduction_money']-$day_info4[0]['score_for_money']-$day_info4[0]['fare_shipping_free'];


						//退款中 金额
						$daywait_refund_money_arr = M()->query("select sum(ref_money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund
								where state =0 and addtime >= ".$day['egt']." and addtime < ".$day['lt']."  ");

						$daywait_refund_money = $daywait_refund_money_arr[0]['total_money'];

						//退款中  订单数量
						$daywait_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund where state =0 and addtime >= ".$day['egt']."
							and addtime < ".$day['lt']."   ");

						$daywait_refund_count = count($daywait_refund_arr);

						//已退款 金额
						$dayhas_refund_money_arr = M()->query("select sum(money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime >= ".$day['egt']."
							and addtime < ".$day['lt']."  ");

						$dayhas_refund_money = $dayhas_refund_money_arr[0]['total_money'];

						//已退款 订单数量
						$dayhas_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime >= ".$day['egt']."
							and addtime < ".$day['lt']."  group by order_id ");

						$dayhas_refund_count = count($dayhas_refund_arr);

						$daylist[$key] = array(
						'daywait_refund_money' => $daywait_refund_money,
						'daywait_refund_count' => $daywait_refund_count,
						'dayhas_refund_money' => $dayhas_refund_money,
						'dayhas_refund_count' => $dayhas_refund_count,
						'dayqu' => $dayqu,
						'dayquxiao' => $dayquxiao,

					);
			}

			$list = array();
			$list['day_info'] = $day_info;
			//合并两个数组
			$list2 = array();
			foreach($list['day_info'] as $k=>$v){
				$list2[] = array_merge($v,$daylist[$k]);
			}

			//退款中 金额
			$wait_refund_money_arr = M()->query("select sum(ref_money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund
					where state =0 and addtime > ".$lastweek['egt']." and addtime < ".$lastweek['lt']."  ");

			$wait_refund_money = $wait_refund_money_arr[0]['total_money'];

			//退款中  订单数量
			$wait_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund where state =0 and addtime > ".$lastweek['egt']."
				and addtime < ".$lastweek['lt']."   ");

			$wait_refund_count = count($wait_refund_arr);

			//已退款 金额
			$has_refund_money_arr = M()->query("select sum(money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime > ".$lastweek['egt']."
				and addtime < ".$lastweek['lt']."  ");

			$has_refund_money = $has_refund_money_arr[0]['total_money'];

			//已退款 订单数量
			$has_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime > ".$lastweek['egt']."
				and addtime < ".$lastweek['lt']."  group by order_id ");

			$has_refund_count = count($has_refund_arr);

			$this->wait_refund_money = $wait_refund_money;
			$this->wait_refund_count = $wait_refund_count;
			$this->has_refund_money = $has_refund_money;
			$this->has_refund_count = $has_refund_count;


			//退款
			$cancel_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money  , sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order
			where date_added > ".$lastweek['egt']." and date_added < ".$lastweek['lt']." ".$condition." and (order_status_id in (7,8,9,10,12,13)) group by date order by date asc");


			$zongtuishu =0;
			$tuikuan =0;
			foreach($cancel_info as $val2) {

				    $zongtuishu += $val2['count'];
					$tuikuan += $val2['total']+$val2['shipping_fare']+$val2['packing_fare']-$val2['voucher_credit']-$val2['fullreduction_money']-$val2['score_for_money']-$val2['fare_shipping_free'];
			}


			//小计
			/*$subtotal_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$lastweek['egt']." and date_added < ".$lastweek['lt']." ".$condition." and (order_status_id in (1,2,3,4,6,11,14)) group by date order by date asc");
			$xaiojishu =0;
			$xaioji =0;
			foreach($subtotal_info as $val3) {

					$xaiojishu += $val3['count'];
					$xaioji += $val3['total']+$val3['shipping_fare']-$val3['voucher_credit']-$val3['fullreduction_money']- $val3['score_for_money'];
			}*/

			//取消订单
			$quxiao_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money , sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$lastweek['egt']." and date_added < ".$lastweek['lt']." ".$condition." and (order_status_id = 5) group by date order by date asc");


			$quxiaoshu =0;
			$quxiao =0;
			foreach($quxiao_info as $val4) {

				    $quxiaoshu += $val4['count'];
					$quxiao += $val4['total']+$val4['shipping_fare']+$val4['packing_fare']-$val4['voucher_credit']-$val4['fullreduction_money']- $val4['score_for_money']- $val4['fare_shipping_free'];
			}

			//小计 = 下单金额-取消金额
			$xaiojishu = $zongdanshu - $quxiaoshu;
			$xaioji = $zongxiadan - $quxiao;

			$tabid = 1;
		}




		//本月时间
		if($_GPC['reports_index'] == 2){
			$thismonth = array();
			$thismonth['egt']=strtotime(date('Y-m-01 00:00:00'));
			$thismonth['lt'] = strtotime(date('Y-m-d H:i:s'));

			//每天所有订单
			$day_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money , sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$thismonth['egt']." and date_added < ".$thismonth['lt']." ".$condition." group by date order by date asc");

			$j = date("d");

			for($i=0;$i<$j;$i++){

				$day_start = date("Y-m-d" , strtotime(date('Y-m-d 00:00:00'))-($j-$i-1)*24*60*60);

				$isin = $this->deep_in_array($day_start,$day_info);
				if($isin){

				}else{

					$day_info[] =array(
						'date' => $day_start,
						'count' => 0,
						'total' => 0,
						'shipping_fare' => 0,
						'voucher_credit' => 0,
						'fullreduction_money' => 0,
						'score_for_money' => 0,
						'fare_shipping_free' => 0,
						'packing_fare' => 0,
					);
				}

			}

			foreach($day_info as $val){
				$key_arrays[]=$val['date'];
			}
			array_multisort($key_arrays,SORT_ASC,SORT_NUMERIC,$day_info);

			//总单数和下单金额
			$zongdanshu = 0;
			$zongxiadan = 0;
			foreach($day_info as $val1) {

					$zongdanshu += $val1['count'];
					$zongxiadan += $val1['total']+$val1['shipping_fare']+$val1['packing_fare']-$val1['voucher_credit']-$val1['fullreduction_money'] - $val1['score_for_money']- $val1['fare_shipping_free'];

			}

			//有订单的所有日期
			//$day_info2 = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where pay_time > ".$thismonth['egt']." and pay_time < ".$thismonth['lt']." ".$condition." group by date order by date asc");

			for($i=0;$i<$j;$i++){
				$day_info2[$i] = array(
					 "date" => date("Y-m-d" , strtotime(date('Y-m-d 00:00:00'))-($j-$i-1)*24*60*60),
				);
			}

			foreach($day_info2 as $key =>$day) {

				 $day["egt"]=strtotime(date($day["date"],time()));
				 $day["lt"]=$day["egt"]+(60*60*24)-1;

				//每天退款单数
				$day_info3 = M()->query("select count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$day['egt']." and date_added < ".$day['lt']." ".$condition." and (order_status_id in (7,8,9,10,12,13))");

					if($day_info3){
						$daytui = 0;
						$daytuikuan =0;
					}



				//每天取消单数
				$day_info4 = M()->query("select count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money, sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$day['egt']." and date_added < ".$day['lt']." ".$condition." and (order_status_id  = 5)");

					if($day_info4){
						$dayqu = 0;
						$dayquxiao =0;
					}

					//取消单数
				    $dayqu = $day_info4[0]['count'];
					//取消金额
					$dayquxiao = $day_info4[0]['total']+$day_info4[0]['shipping_fare']+$day_info4[0]['packing_fare']-$day_info4[0]['voucher_credit']-$day_info4[0]['fullreduction_money']-$day_info4[0]['score_for_money']-$day_info4[0]['fare_shipping_free'];


					//退款中 金额
					$daywait_refund_money_arr = M()->query("select sum(ref_money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund
							where state =0 and addtime >= ".$day['egt']." and addtime < ".$day['lt']."  ");

					$daywait_refund_money = $daywait_refund_money_arr[0]['total_money'];

					//退款中  订单数量
					$daywait_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund where state =0 and addtime >= ".$day['egt']."
						and addtime < ".$day['lt']."   ");

					$daywait_refund_count = count($daywait_refund_arr);

					//已退款 金额
					$dayhas_refund_money_arr = M()->query("select sum(money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime >= ".$day['egt']."
						and addtime < ".$day['lt']."  ");

					$dayhas_refund_money = $dayhas_refund_money_arr[0]['total_money'];

					//已退款 订单数量
					$dayhas_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime >= ".$day['egt']."
						and addtime < ".$day['lt']."  group by order_id ");

					$dayhas_refund_count = count($dayhas_refund_arr);



					$daylist[$key] = array(
						'daywait_refund_money' => $daywait_refund_money,
						'daywait_refund_count' => $daywait_refund_count,
						'dayhas_refund_money' => $dayhas_refund_money,
						'dayhas_refund_count' => $dayhas_refund_count,
						'dayqu' => $dayqu,
						'dayquxiao' => $dayquxiao,

					);
			}

			$list = array();
			$list['day_info'] = $day_info;
			//合并两个数组
			$list2 = array();
			foreach($list['day_info'] as $k=>$v){
				$list2[] = array_merge($v,$daylist[$k]);
			}


			//退款中 金额
			$wait_refund_money_arr = M()->query("select sum(ref_money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund
					where state =0 and addtime > ".$thismonth['egt']." and addtime < ".$thismonth['lt']."  ");

			$wait_refund_money = $wait_refund_money_arr[0]['total_money'];

			//退款中  订单数量
			$wait_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund where state =0 and addtime > ".$thismonth['egt']."
				and addtime < ".$thismonth['lt']."   ");

			$wait_refund_count = count($wait_refund_arr);

			//已退款 金额
			$has_refund_money_arr = M()->query("select sum(money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime > ".$thismonth['egt']."
				and addtime < ".$thismonth['lt']."  ");

			$has_refund_money = $has_refund_money_arr[0]['total_money'];

			//已退款 订单数量
			$has_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime > ".$thismonth['egt']."
				and addtime < ".$thismonth['lt']."  group by order_id ");

			$has_refund_count = count($has_refund_arr);

			$this->wait_refund_money = $wait_refund_money;
			$this->wait_refund_count = $wait_refund_count;
			$this->has_refund_money = $has_refund_money;
			$this->has_refund_count = $has_refund_count;


			//退款
			$cancel_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money, sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from
			".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$thismonth['egt']." and date_added < ".$thismonth['lt']." ".$condition." and (order_status_id in (7,8,9,10,12,13)) group by date order by date asc");


			$zongtuishu =0;
			$tuikuan =0;
			foreach($cancel_info as $val2) {

				    $zongtuishu += $val2['count'];
					$tuikuan += $val2['total']+$val2['shipping_fare']+$val2['packing_fare']-$val2['voucher_credit']-$val2['fullreduction_money']-$val2['score_for_money']-$val2['fare_shipping_free'];
			}


			//小计
			/*$subtotal_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$thismonth['egt']." and date_added < ".$thismonth['lt']." ".$condition." and (order_status_id in (1,2,3,4,6,11,14)) group by date order by date asc");
			$xaiojishu =0;
			$xaioji =0;
			foreach($subtotal_info as $val3) {

					$xaiojishu += $val3['count'];
					$xaioji += $val3['total']+$val3['shipping_fare']-$val3['voucher_credit']-$val3['fullreduction_money']- $val3['score_for_money'];
			}*/

			//取消订单
			$quxiao_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money, sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$thismonth['egt']." and date_added < ".$thismonth['lt']." ".$condition." and (order_status_id = 5) group by date order by date asc");


			$quxiaoshu =0;
			$quxiao =0;
			foreach($quxiao_info as $val4) {

				    $quxiaoshu += $val4['count'];
					$quxiao += $val4['total']+$val4['shipping_fare']+$val4['packing_fare']-$val4['voucher_credit']-$val4['fullreduction_money']- $val4['score_for_money']- $val4['fare_shipping_free'];
			}

			//小计 = 下单金额-取消金额
			$xaiojishu = $zongdanshu - $quxiaoshu;
			$xaioji = $zongxiadan - $quxiao;

			$tabid = 2;

		}

		//上月时间
		if($_GPC['reports_index'] == 3){
		$lastmonth['lt'] = strtotime(date('Y-m-01 00:00:00')) - 1;

		$month=date('m') - 1;
		$year=date('Y');

		if($month==1 || $month==3 || $month==5|| $month==7 ||$month==8 || $month==10 ||$month==12 ){
			//31天
			$lastmonth['egt'] = strtotime(date('Y-m-01 00:00:00')) - 31*24*60*60;
			$j = 31;

		}elseif($month==4 || $month==6 ||$month==9 ||$month==11){
			//30天
			$lastmonth['egt'] = strtotime(date('Y-m-01 00:00:00')) - 30*24*60*60;
			$j = 30;
		}else{
			 if($year%4 == 0){
				//29天
				$lastmonth['egt'] = strtotime(date('Y-m-01 00:00:00')) - 29*24*60*60;
				$j = 29;
			 }else{
				//28天
				$lastmonth['egt'] = strtotime(date('Y-m-01 00:00:00')) - 28*24*60*60;
				$j = 28;
			 }
		}

			//每天所有订单
			$day_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money, sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$lastmonth['egt']." and date_added < ".$lastmonth['lt']." ".$condition." group by date order by date asc");


			for($i=0;$i<$j;$i++){

				$day_start = date("Y-m-d" , strtotime(date('Y-m-01 00:00:00')) - ($j-$i)*24*60*60 );

				$isin = $this->deep_in_array($day_start,$day_info);
				if($isin){

				}else{

					$day_info[] =array(
						'date' => $day_start,
						'count' => 0,
						'total' => 0,
						'shipping_fare' => 0,
						'voucher_credit' => 0,
						'fullreduction_money' => 0,
						'score_for_money' => 0,
						'fare_shipping_free' => 0,
						'packing_fare' => 0,
					);
				}

			}

			foreach($day_info as $val){
				$key_arrays[]=$val['date'];
			}
			array_multisort($key_arrays,SORT_ASC,SORT_NUMERIC,$day_info);


			//总单数和下单金额
			$zongdanshu = 0;
			$zongxiadan = 0;
			foreach($day_info as $val1) {

					$zongdanshu += $val1['count'];
					$zongxiadan += $val1['total']+$val1['shipping_fare']+$val1['packing_fare']-$val1['voucher_credit']-$val1['fullreduction_money'] - $val1['score_for_money']- $val1['fare_shipping_free'];

			}

			//有订单的所有日期
			$day_info2 = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where pay_time > ".$lastmonth['egt']." and pay_time < ".$lastmonth['lt']." ".$condition." group by date order by date asc");

			for($i=0;$i<$j;$i++){
				$day_info2[$i] = array(
					 "date" => date("Y-m-d" , strtotime(date('Y-m-01 00:00:00')) - ($j-$i)*24*60*60 ),
				);
			}

			foreach($day_info2 as $key =>$day) {
				 $day["egt"]=strtotime(date($day["date"],time()));
				 $day["lt"]=$day["egt"]+(60*60*24)-1;

				//每天退款单数
				$day_info3 = M()->query("select count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money, sum(fare_shipping_free) as fare_shipping_free from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$day['egt']." and date_added < ".$day['lt']." ".$condition." and (order_status_id in (7,8,9,10,12,13))");

					if($day_info3){
						$daytui = 0;
						$daytuikuan =0;
					}

					//退款单数
				    $daytui = $day_info3[0]['count'];
					//退款金额
					$daytuikuan = $day_info3[0]['total']+$day_info3[0]['shipping_fare']-$day_info3[0]['voucher_credit']-$day_info3[0]['fullreduction_money']-$day_info3[0]['score_for_money']-$day_info3[0]['fare_shipping_free'];


				//每天取消单数
				$day_info4 = M()->query("select count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money, sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$day['egt']." and date_added < ".$day['lt']." ".$condition." and (order_status_id  = 5)");

					if(empty($day_info4)){
						$dayqu = 0;
						$dayquxiao =0;
					}else{
						$dayqu = $day_info4[0]['count'];
						$dayquxiao =$day_info4[0]['total']+$day_info4[0]['shipping_fare']+$day_info4[0]['packing_fare']-$day_info4[0]['voucher_credit']-$day_info4[0]['fullreduction_money']-$day_info4[0]['score_for_money']-$day_info4[0]['fare_shipping_free'];
					}


					//退款中 金额
					$daywait_refund_money_arr = M()->query("select sum(ref_money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund
							where state =0 and addtime >= ".$day['egt']." and addtime < ".$day['lt']."  ");

					$daywait_refund_money = $daywait_refund_money_arr[0]['total_money'];

					//退款中  订单数量
					$daywait_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund where state =0 and addtime >= ".$day['egt']."
						and addtime < ".$day['lt']."   ");

					$daywait_refund_count = count($daywait_refund_arr);

					//已退款 金额
					$dayhas_refund_money_arr = M()->query("select sum(money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime >= ".$day['egt']."
						and addtime < ".$day['lt']."  ");

					$dayhas_refund_money = $dayhas_refund_money_arr[0]['total_money'];

					//已退款 订单数量
					$dayhas_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime >= ".$day['egt']."
						and addtime < ".$day['lt']."  group by order_id ");

					$dayhas_refund_count = count($dayhas_refund_arr);



					$daylist[$key] = array(
						'daywait_refund_money' => $daywait_refund_money,
						'daywait_refund_count' => $daywait_refund_count,
						'dayhas_refund_money' => $dayhas_refund_money,
						'dayhas_refund_count' => $dayhas_refund_count,
						'dayqu' => $dayqu,
						'dayquxiao' => $dayquxiao,

					);
			}

			$list = array();
			$list['day_info'] = $day_info;
			//合并两个数组
			$list2 = array();
			foreach($list['day_info'] as $k=>$v){
				$list2[] = array_merge($v,$daylist[$k]);
			}


			//退款中 金额
			$wait_refund_money_arr = M()->query("select sum(ref_money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund
					where state =0 and addtime > ".$lastmonth['egt']." and addtime < ".$lastmonth['lt']."  ");

			$wait_refund_money = $wait_refund_money_arr[0]['total_money'];

			//退款中  订单数量
			$wait_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_refund where state =0 and addtime > ".$lastmonth['egt']."
				and addtime < ".$lastmonth['lt']."   ");

			$wait_refund_count = count($wait_refund_arr);

			//已退款 金额
			$has_refund_money_arr = M()->query("select sum(money) as total_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime > ".$lastmonth['egt']."
				and addtime < ".$lastmonth['lt']."  ");

			$has_refund_money = $has_refund_money_arr[0]['total_money'];

			//已退款 订单数量
			$has_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where addtime > ".$lastmonth['egt']."
				and addtime < ".$lastmonth['lt']."  group by order_id ");

			$has_refund_count = count($has_refund_arr);

			$this->wait_refund_money = $wait_refund_money;
			$this->wait_refund_count = $wait_refund_count;
			$this->has_refund_money = $has_refund_money;
			$this->has_refund_count = $has_refund_count;


			//退款
			$cancel_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money, sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order
					where date_added > ".$lastmonth['egt']." and date_added < ".$lastmonth['lt']." ".$condition." and (order_status_id in (7,8,9,10,12,13)) group by date order by date asc");


			$zongtuishu =0;
			$tuikuan =0;
			foreach($cancel_info as $val2) {

				    $zongtuishu += $val2['count'];
					$tuikuan += $val2['total']+$val2['shipping_fare']+$val2['packing_fare']-$val2['voucher_credit']-$val2['fullreduction_money']-$val2['score_for_money']-$val2['fare_shipping_free'];
			}


			/*//小计
			$subtotal_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$lastmonth['egt']." and date_added < ".$lastmonth['lt']." ".$condition." and (order_status_id in (1,2,3,4,6,11,14)) group by date order by date asc");
			$xaiojishu =0;
			$xaioji =0;
			foreach($subtotal_info as $val3) {

					$xaiojishu += $val3['count'];
					$xaioji += $val3['total']+$val3['shipping_fare']-$val3['voucher_credit']-$val3['fullreduction_money']- $val3['score_for_money'];
			}*/

			//取消订单
			$quxiao_info = M()->query("select from_unixtime( date_added, '%Y-%m-%d' ) as date, count( * ) as count,sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(score_for_money) as score_for_money, sum(fare_shipping_free) as fare_shipping_free, sum(packing_fare) as packing_fare from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$lastmonth['egt']." and date_added < ".$lastmonth['lt']." ".$condition." and (order_status_id = 5) group by date order by date asc");


			$quxiaoshu =0;
			$quxiao =0;
			foreach($quxiao_info as $val4) {

				    $quxiaoshu += $val4['count'];
					$quxiao += $val4['total']+$val4['shipping_fare']+$val4['packing_fare']-$val4['voucher_credit']-$val4['fullreduction_money']- $val4['score_for_money']- $val4['fare_shipping_free'];
			}

			//小计 = 下单金额-取消金额
			$xaiojishu = $zongdanshu - $quxiaoshu;
			$xaioji = $zongxiadan - $quxiao;

			$tabid = 3;
		}


		if( isset($_GPC['is_export']) && $_GPC['is_export'] == 1 )
		{
			$columns = array(
					array('title' => '下单日期', 'field' => 'date', 'width' => 32),
					array('title' => '订单数', 'field' => 'count', 'width' => 32),
					array('title' => '下单金额', 'field' => 'order_amount', 'width' => 32),

					array('title' => '等待退款笔数', 'field' => 'daywait_refund_count', 'width' => 32),
					array('title' => '等待退款金额', 'field' => 'daywait_refund_money', 'width' => 32),
					array('title' => '已退款笔数', 'field' => 'dayhas_refund_count', 'width' => 32),
					array('title' => '已退款金额', 'field' => 'dayhas_refund_money', 'width' => 32),

					array('title' => '取消笔数', 'field' => 'dayqu', 'width' => 32),
					array('title' => '取消金额', 'field' => 'dayquxiao', 'width' => 32),
					array('title' => '小计', 'field' => 'order_ji', 'width' => 32),
			);



			$exportlist = array();

			foreach($list2 as $k => $w){
				$tmp_exval = array();
				$tmp_exval['date'] = $w['date'];
				$tmp_exval['count'] = $w["count"];

				$order_amount = $w['total']+$w['shipping_fare']-$w['voucher_credit']-$w['fullreduction_money']-$W['score_for_money']-$w['fare_shipping_free'];
				$order_amount = sprintf("%.2f",$order_amount);

				$tmp_exval['order_amount'] = $order_amount;

				$tmp_exval['daywait_refund_count'] = $w['daywait_refund_count'];
				$tmp_exval['daywait_refund_money'] = $w['daywait_refund_money'];
				$tmp_exval['dayhas_refund_count'] = $w['dayhas_refund_count'];
				$tmp_exval['dayhas_refund_money'] = $w['dayhas_refund_money'];

				$tmp_exval['dayqu'] = $w['dayqu'];

				$w["dayquxiao"] = sprintf("%.2f",$w["dayquxiao"]);
				$tmp_exval['dayquxiao'] = $w['dayquxiao'];

				$order_ji = $order_amount - $w["daytuikuan"]-$w["dayquxiao"];
				$order_ji = sprintf("%.2f",$order_ji);

				$tmp_exval['order_ji'] = $order_ji;

				$exportlist[] = $tmp_exval;
			}

			$title = '本周营业数据';

			if( isset($_GPC['reports_index']) && $_GPC['reports_index'] == 0)
			{
				$title = '本周营业数据';
			}else if( isset($_GPC['reports_index']) && $_GPC['reports_index'] == 1 ){
				$title = '上周营业数据';
			}else if( isset($_GPC['reports_index']) && $_GPC['reports_index'] == 2 ){
				$title = '本月营业数据';
			}else if( isset($_GPC['reports_index']) && $_GPC['reports_index'] == 3 ){
				$title = '上月营业数据';
			}


			D('Seller/Excel')->export($exportlist, array('title' => $title, 'columns' => $columns));
		}


		$this->lastmonth = $lastmonth;
		$this->zongdanshu = $zongdanshu;
		$this->zongxiadan = $zongxiadan;
		$this->day_info = $day_info;

		$this->day_info2 = $day_info2;
		$this->list = $list;
		$this->list2 = $list2;
		$this->zongtuishu = $zongtuishu;
		$this->tuikuan = $tuikuan;
		$this->cancel_info = $cancel_info;

		$this->subtotal_info = $subtotal_info;
		$this->xaiojishu = $xaiojishu;
		$this->xaioji = $xaioji;


		$this->quxiao_info = $quxiao_info;
		$this->quxiaoshu = $quxiaoshu;
		$this->quxiao = $quxiao;

		$this->tabid = $tabid;



		$this->_GPC = $_GPC;


		$this->display();
	}


	public function datastatics()
	{
		$_GPC = I('request.');
		$condition = ' 1 ';
		//$pindex = max(1, intval($_GPC['page']));
		//$psize = 10;

		//下单金额（元）    sum_money
		//下单客户数        sum_member
		//下单量			sum_order
		//下单商品数		sum_goods
		//平均价格		    ave_money
		//新增客户		    add_member
		//客户数量	        member_num
		//新增供货商   		add_supplier
		//新增团长     		add_head
		//新增商品     		add_goods

		//今天开始时间

		$today = array();
		$today['egt'] = strtotime(date('Y-m-d 00:00:00'));
		$today['lt'] = strtotime(date('Y-m-d 23:59:59'));

		//今天所有订单
		$day_info = M()->query("select count( * ) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$today['egt']." and date_added < ".$today['lt']." and type != 'integral' and ".$condition );

		$day_info2 = M()->query("select total as total,member_id as member_id,shipping_fare as shipping_fare,voucher_credit as voucher_credit,fullreduction_money as fullreduction_money,packing_fare,fare_shipping_free from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$today['egt']." and date_added < ".$today['lt']." and type != 'integral' and ".$condition);

		$list = array();
		$sum_money = 0;
		foreach($day_info2 as $key =>$val1) {

			//下单金额（元）sum_money
			$sum_money += $val1['total']+$val1['shipping_fare']+$val1['packing_fare']-$val1['fare_shipping_free']-$val1['voucher_credit']-$val1['fullreduction_money'];

			$list[$key] = array(
				'member_id' => $val1['member_id'],
			);
		}

		//下单量sum_order
		$sum_order = $day_info[0]['count'];

		//下单客户数sum_member
		$result = array_unique($list, SORT_REGULAR);
		$sum_member = sizeof($result,0);

		//下单商品数sum_goods
		$goods = M()->query("select goods_id as goods_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods where addtime > ".$today['egt']." and addtime < ".$today['lt']." and ".$condition);
		$list1 = array_unique($goods, SORT_REGULAR);
		$sum_goods = sizeof($list1,0);

		//平均价格 ave_money   下单金额/下单量
		if(empty($sum_order)){
			$ave_money = 0;
		}else{
			$ave_money =($sum_money)/($sum_order);
			$ave_money = sprintf("%.3f",$ave_money);
		}

		//新增客户add_member

		$add_member = M()->query("select count( * ) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_member where create_time > ".$today['egt']." and create_time < ".$today['lt']." and ".$condition);
		$add_member = $add_member[0]['count'];

		//客户数量member_num
		$member_num = M()->query("select count( * ) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_member where ".$condition,
				array(':uniacid' => $_W['uniacid'] ));
		$member_num = $member_num[0]['count'];

		//新增供货商add_supplier
		$add_supplier = M()->query("select count( * ) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_supply where addtime > ".$today['egt']." and addtime < ".$today['lt']."  and ".$condition );
		$add_supplier = $add_supplier[0]['count'];

		//新增团长add_head

		$add_head = M()->query("select count( * ) as count from ".C('DB_PREFIX')."eaterplanet_community_head where addtime > ".$today['egt']." and addtime < ".$today['lt']." and  ".$condition );
		$add_head = $add_head[0]['count'];

		//新增商品add_goods

		$add_goods = M()->query("select count( * ) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods where addtime > ".$today['egt']." and addtime < ".$today['lt']." and  ".$condition );
		$add_goods = $add_goods[0]['count'];



		//今日销售走势

		$todaytime = array();
		$todaytime['egt'] = strtotime(date('Y-m-d 00:00:00'));
		$todaytime['lt'] = strtotime(date('Y-m-d 23:59:59'));

		$today_sales = array();

		//当前小时
		$h = date("H");

		for($i = 0;$i <= $h; $i++){

			$todaytime['egt'] = strtotime(date('Y-m-d 00:00:00'));

			$todaytime['egt'] = $todaytime['egt']+$i*60*60;
			$todaytime['lt'] = $todaytime['egt']+60*60-1;


			//有效销售额
			$list = M()->query("select sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(packing_fare) as packing_fare,sum(fare_shipping_free) as fare_shipping_free from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$todaytime['egt']." and date_added < ".$todaytime['lt']." and type != 'integral' and ".$condition." and (order_status_id in (1,2,3,4,6,11,14))" );

			if(empty($list[0]['total'])){
				$val = 0;
			}else{
				$val = $list[0]['total']+$list[0]['shipping_fare']+$list[0]['packing_fare']-$list[0]['fare_shipping_free']-$list[0]['voucher_credit']-$list[0]['fullreduction_money'];

			}

			$today_sales[$i] =  $val;

		}




		//昨日销售走势

		$yestertime = array();
		$yestertime['egt'] = strtotime(date('Y-m-d 00:00:00')) - 24*60*60;
		$yestertime['lt'] = strtotime(date('Y-m-d 00:00:00')) - 1;

		$yesterday_sales = array();

		for($i = 0;$i <= 23; $i++){

			$yestertime['egt'] = strtotime(date('Y-m-d 00:00:00')) - 24*60*60;

			$yestertime['egt'] = $yestertime['egt']+$i*60*60;
			$yestertime['lt'] = $yestertime['egt']+60*60-1;


			//有效销售额
			$list1 = M()->query("select sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(packing_fare) as packing_fare,sum(fare_shipping_free) as fare_shipping_free from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$yestertime['egt']." and date_added < ".$yestertime['lt']." and ".$condition."  and type != 'integral' and (order_status_id in (1,2,3,4,6,11,14))" );

			if(empty($list1[0]['total'])){
				$val1 = 0;
			}else{
				$val1 = $list1[0]['total']+$list1[0]['shipping_fare']+$list1[0]['packing_fare']-$list1[0]['fare_shipping_free']-$list1[0]['voucher_credit']-$list1[0]['fullreduction_money'];

			}

			$yesterday_sales[$i] =  $val1;

		}







		//七天的时间
		$sevenday = array();

			$sevenday['egt'] = strtotime(date('Y-m-d 00:00:00'))-6*24*60*60;
			$sevenday['lt'] = strtotime(date('Y-m-d 23:59:59'));
		//7日内团长销量top10

		$sevenday_sale = M()->query("select head_id as head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where date_added > ".$sevenday['egt']." and date_added < ".$sevenday['lt']." and type != 'integral' and ".$condition);
		//所有团长id
		$sale = array();
		foreach($sevenday_sale as $key =>$v) {
				$sale[$key] = array(
					'head_id' => $v['head_id'],
				);
		}
		//合并数据，唯一团长id
		$sale = array_unique($sale, SORT_REGULAR);

		//var_dump($sale);

		//var_dump($sale);
		//获取供应信息
		$sale_list = array();
		foreach($sale as $key =>$v) {

				//社区店名称
				$sale1 =  M()->query("select community_name as community_name ,head_name as head_name from ".C('DB_PREFIX')."eaterplanet_community_head where id = ".$v['head_id']." and ".$condition);
				//var_dump($sale1);
				//团长
				//订单数量
				$sale2 = M()->query("select count( * ) as count  from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where(head_id = ".$v['head_id'].") and date_added > ".$sevenday['egt']." and date_added < ".$sevenday['lt']." and type != 'integral' and ".$condition);
				//var_dump($sale2);
				//有效订单金额（元）

				$sale3 = M()->query("select sum(total) as total,sum(shipping_fare) as shipping_fare,sum(voucher_credit) as voucher_credit,sum(fullreduction_money) as fullreduction_money,sum(packing_fare) as packing_fare,sum(fare_shipping_free) as fare_shipping_free from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where (head_id = ".$v['head_id'].") and date_added > ".$sevenday['egt']." and date_added < ".$sevenday['lt']." and type != 'integral' and ".$condition );


					//有效订单金额（元）sum_money
				$sale_money = $sale3[0]['total']+$sale3[0]['shipping_fare']+$sale3[0]['packing_fare']-$sale3[0]['fare_shipping_free']-$sale3[0]['voucher_credit']-$sale3[0]['fullreduction_money'];


				$sale_list[$key] = array(
					'community_name' => $sale1[0]['community_name'],
					'head_name' => $sale1[0]['head_name'],
					'count' => $sale2[0]['count'],
					'sale_money' => $sale_money,

				);

		}


		//数组重新排序
		$count = array_column($sale_list,'count');
		array_multisort($count,SORT_DESC,$sale_list);

		$this->sale_list = $sale_list;

		//7日内商品销量top10
		$sevenday_info = M()->query("select goods_id as goods_id,name as name,quantity as quantity from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods where addtime > ".$sevenday['egt']." and addtime < ".$sevenday['lt']."  and ".$condition." order by quantity desc ");
		//所有商品id
		$info = array();
		foreach($sevenday_info as $key =>$v) {
				$info[$key] = array(
					'goods_id' => $v['goods_id'],
					//'quantity' => $v['quantity'],
				);
		}


		//合并数据，唯一商品id
		$info = array_unique($info, SORT_REGULAR);

		//唯一商品id获取对应信息
		$goods_statistic = array();
		foreach($info as $key =>$v) {
				$g_sql = "select sum(og.quantity) as quantity ,og.name as name from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods og,".C('DB_PREFIX')."eaterplanet_ecommerce_order o where( og.goods_id = ".$v['goods_id'].") "
						." and og.addtime > ".$sevenday['egt']." and og.addtime < ".$sevenday['lt']." and ".$condition." and og.order_id=o.order_id and o.type != 'integral' and o.order_status_id in(6,11)";
				$info2 = M()->query($g_sql);
				if( empty($info2[0]['name']) && empty($info2[0]['quantity'])){

				}else{
					$goods_statistic[$key]=array(
						'goods_id' => $v['goods_id'],
						'name' => $info2[0]['name'],
						'quantity' => $info2[0]['quantity'],
					);
				}
		}
		//序号
		$gid = 0;
		//数组重新排序
		$quantity = array_column($goods_statistic,'quantity');
		array_multisort($quantity,SORT_DESC,$goods_statistic);

		$this->day_info = $day_info;

		$this->day_info2 = $day_info2;
		$this->sum_money = $sum_money;
		$this->sum_order = $sum_order;

		$this->sum_member = $sum_member;
		$this->list1 = $list1;
		$this->sum_goods = $sum_goods;
		$this->ave_money = $ave_money;
		$this->add_member = $add_member;
		$this->member_num = $member_num;

		$this->add_supplier = $add_supplier;
		$this->add_head = $add_head;
		$this->add_goods = $add_goods;

		$this->todaytime =  $todaytime;

		$this->today_sales = $today_sales;

		$this->yestertime = $yestertime;
		$this->yesterday_sales = $yesterday_sales;

		$this->sevenday = $sevenday;
		$this->sevenday_sale = $sevenday_sale;

		$this->sale = $sale;
		$this->count = $count;
		$this->sevenday_info = $sevenday_info;
		$this->info = $info;
		$this->goods_statistic = $goods_statistic;


		$this->_GPC = $_GPC;
		include $this->display();
	}

	public function communitystatics()
	{
		 $_GPC = I('request.');

		$starttime = strtotime( date('Y-m-d').' 00:00:00' );
		$endtime   = $starttime + 86400;

		$pindex    = I('request.page', 1);
		$psize     = 20;

		$searchtime = $_GPC['searchtime'];
		$keyword = I('get.keyword','','addslashes');
		$keyword2 = stripslashes($keyword);
		$this->keyword = $keyword2;

		if( !empty($searchtime) )
		{
			$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
			$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		}
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->searchtime = $searchtime;


		//0 3
		$type = isset($_GPC['type']) ? $_GPC['type'] : 0;

		$data = array();

		$data = $this->head_sale_analys($keyword, $searchtime , $starttime , $endtime, $pindex , $psize);

		$this->pager = $data['pager'];
		unset($data['pager']);
		$this->type = $type;

		$this->data = $data;
		$this->_GPC = $_GPC;

		$this->display();
	}

	public  function communitystatics_commiss()
	{
		$_GPC = I('request.');

		$pindex    = I('request.page', 1);
		$psize     = 20;

		$starttime = strtotime( date('Y-m-d').' 00:00:00' );
		$endtime   = $starttime + 86400;

		$searchtime = $_GPC['searchtime'];
		$keyword = $_GPC['keyword'];

		if( !empty($searchtime) )
		{
			$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
			$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		}
		//0 3
		$type = isset($_GPC['type']) ? $_GPC['type'] : 0;

		$data = array();



		$data = $this->head_commiss_analys($keyword,$searchtime , $starttime , $endtime , $pindex , $psize);

		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->searchtime = $searchtime;
		$this->keyword = $keyword;

		$this->pager = $data['pager'];
		unset($data['pager']);

		$this->type = $type;
		$this->data = $data;
		$this->_GPC = $_GPC;

		include $this->display();
	}

	public function communitystatics_order()
	{
		$_GPC = I('request.');

		$starttime = strtotime( date('Y-m-d').' 00:00:00' );
		$endtime   = $starttime + 86400;

		$searchtime = $_GPC['searchtime'];
		$keyword = $_GPC['keyword'];

		if( !empty($searchtime) )
		{
			$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
			$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		}
		//0 3
		$type = isset($_GPC['type']) ? $_GPC['type'] : 0;

		$data = array();

		$pindex    = I('request.page', 1);
		$psize     = 20;

		$data = $this->head_order_analys($keyword,$searchtime , $starttime , $endtime  , $pindex , $psize);


		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->searchtime = $searchtime;
		$this->keyword = $keyword;

		$this->pager = $data['pager'];
		unset($data['pager']);

		$this->type = $type;
		$this->data = $data;
		$this->_GPC = $_GPC;

		include $this->display();
	}


	private function head_order_analys($keyword,$searchtime , $starttime , $endtime, $pindex , $psize)
	{
		$_GPC = I('request.');

		$data = array();
		//1、寻找团长

		$where = " and type !=  'integral' ";
		if( !empty($searchtime) )
		{
			$where .= " and date_added >= {$starttime} and date_added <= {$endtime} ";
		}

		/*$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where 1 {$where} group by head_id ";

		$order_ids_all = M()->query($sql);

		$head_ids_arr = array();

		if( !empty($order_ids_all) )
		{
			foreach( $order_ids_all as $val )
			{
				$head_ids_arr[] = $val['head_id'];
			}
		}

		$search_head_list = array();

		if( !empty($keyword) )
		{
			$sql = " SELECT ch.id  FROM " . C('DB_PREFIX') . "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id
						WHERE    (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%'  ) ";


			$community_head_list = M()->query( $sql );

			if( !empty($community_head_list) )
			{
				foreach( $community_head_list as $val )
				{
					$search_head_list[] = $val['id'];
				}
			}
			//交集
			$head_ids_arr = array_intersect($head_ids_arr, $search_head_list);
		}*/

		//进行分页操作
		/*$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order co left join " . C('DB_PREFIX'). "eaterplanet_community_head as ch on co.head_id=ch.id left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id where 1 {$where} and ch.member_id > 0 group by head_id ";
		if( !empty($keyword) ){
			$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order co left join " . C('DB_PREFIX'). "eaterplanet_community_head as ch on co.head_id=ch.id left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id"
					." WHERE  (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%')  {$where} group by head_id ";
		}
		$sql2 = "";
		if( !empty($keyword) ){
			$sql = " SELECT DISTINCT(ch.id) as head_id  FROM " . C('DB_PREFIX'). "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id
						WHERE   (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%'  )  ";
		}
		if(!empty($sql2)){
			$sql = $sql . " union all ".$sql2;
		}
		$sql = 'select distinct(head_id) as head_id from ('.$sql . ') t where head_id > 0';*/
		$sql = " SELECT ch.id as head_id,ch.level_id  FROM " . C('DB_PREFIX'). "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id where 1=1 ";
		if( !empty($keyword) ){
			$sql = $sql." and (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%'  ) ";
		}
		if( isset($_GPC['export']) && $_GPC['export'] == 1 ) {
			$sql_list = 'select distinct(head_id) as head_id from (' . $sql . ') t where head_id > 0  ';
		}else{

			$sql_list = 'select distinct(head_id) as head_id from (' . $sql . ') t where head_id > 0 order by head_id asc limit ' . (($pindex - 1) * $psize) . ',' . $psize;

			$total_list = M()->query("select count(1) as count from ($sql) t where head_id > 0");
			$total = 0;
			if(!empty($total_list) && count($total_list) > 0){
				$total = $total_list[0]['count'];
			}
			$pager = pagination2($total, $pindex, $psize);
			$data['pager'] = $pager;
		}

		$order_ids_all = M()->query($sql_list);

		$head_ids_arr = array();

		if( !empty($order_ids_all) )
		{
			foreach( $order_ids_all as $val )
			{
				$head_ids_arr[] = $val['head_id'];
			}
		}

		//----------------以上是搜索团长的代码

		//---------------团长等级begin-------------
		$level_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_commission_level where 1 ";

		$level_list = M()->query($level_sql);

		$level_arr = array(0 => '默认等级');

		foreach( $level_list as $vv )
		{
			$level_arr[ $v['id'] ] = $vv['levelname'];
		}

		//---------------团长等级end---------------


		if( empty($head_ids_arr) )
		{
			return $data;
		}else{

			foreach($head_ids_arr as $head_id)
			{
				$tmp = array();

				$head_info = M('eaterplanet_community_head')->where( array('id' => $head_id ) )->find();

				if( empty($head_info['member_id']) )
				{
					continue;
				}

				$mb_info = M('eaterplanet_ecommerce_member')->field('username')->where( array('member_id' => $head_info['member_id'] ) )->find();

				$tmp['head_id'] = $head_id;
				$tmp['username'] = $mb_info['username'];


				$tmp['community_name'] = $head_info['community_name'];
				$tmp['head_name'] = $head_info['head_name'];
				$tmp['head_mobile'] = $head_info['head_mobile'];
				$tmp['head_levelname'] = $level_arr[ $head_info['level_id'] ];

				//总订单量 in(1,4,6,11,14)  退款： 7,

				$all_order_count = M('eaterplanet_ecommerce_order')->where( "head_id = {$head_id}  {$where}" )->count();

				$tmp['all_order_count'] = $all_order_count;
				//有效订单量
				$effect_order_count = M('eaterplanet_ecommerce_order')->where( " head_id = {$head_id} and order_status_id in (1,4,6,11,12,14)  {$where}" )->count();

				$tmp['effect_order_count'] = $effect_order_count;
				//已关闭订单量

				$close_order_count =  M('eaterplanet_ecommerce_order')->where( " head_id = {$head_id} and order_status_id =5  {$where} " )->count();

				$tmp['close_order_count'] = $close_order_count;
				//订单总金额（元）

				$all_order_paymoney = M('eaterplanet_ecommerce_order')->where("head_id = {$head_id} {$where}")->sum("total+shipping_fare-voucher_credit-fullreduction_money+packing_fare");
				$all_order_paymoney = sprintf('%.2f',$all_order_paymoney);
				$tmp['all_order_paymoney'] = $all_order_paymoney;

				//有效订单金额（元）
				$effect_order_paymoney = 	M('eaterplanet_ecommerce_order')->where("head_id = {$head_id} and order_status_id in(1,4,6,11,12,14) {$where}")->sum("total+shipping_fare-voucher_credit-fullreduction_money+packing_fare");
                $effect_order_paymoney = sprintf('%.2f',$effect_order_paymoney);

                $tmp['effect_order_paymoney'] = $effect_order_paymoney;

				//待付款量
				$pending_order_count =	M('eaterplanet_ecommerce_order')->where( " head_id = {$head_id} and order_status_id =3  {$where} " )->count();

				$tmp['pending_order_count'] = $pending_order_count;



				//退款量
				$refund_order_count = M('eaterplanet_ecommerce_order')->where(" head_id = {$head_id} and order_status_id =7  {$where} ")->count();

				$tmp['refund_order_count'] = $refund_order_count;
				//退款总金额（元）
				$refund_order_paymoney = M('eaterplanet_ecommerce_order')->where(" head_id = {$head_id} and order_status_id =7 {$where} ")->sum("total+shipping_fare-voucher_credit-fullreduction_money+packing_fare");
				$refund_order_paymoney = sprintf('%.2f',$refund_order_paymoney);
				$tmp['refund_order_paymoney'] = $refund_order_paymoney;


				$data[] = $tmp;
			}

			if( isset($_GPC['export']) && $_GPC['export'] == 1 )
			{
				$columns = array(
						array('title' => 'ID', 'field' => 'head_id', 'width' => 32),
						array('title' => '团长昵称', 'field' => 'username', 'width' => 32),
						array('title' => '团长姓名', 'field' => 'head_name', 'width' => 32),
						array('title' => '团长手机号', 'field' => 'head_mobile', 'width' => 32),
						array('title' => '小区信息', 'field' => 'community_name', 'width' => 32),
						array('title' => '总订单量', 'field' => 'all_order_count', 'width' => 32),
						array('title' => '有效订单量', 'field' => 'effect_order_count', 'width' => 32),
						array('title' => '已关闭订单量', 'field' => 'close_order_count', 'width' => 32),
						array('title' => '订单总金额（元）', 'field' => 'all_order_paymoney', 'width' => 32),
						array('title' => '有效订单金额（元）', 'field' => 'effect_order_paymoney', 'width' => 32),
						array('title' => '待付款量', 'field' => 'pending_order_count', 'width' => 32),
						array('title' => '退款量', 'field' => 'refund_order_count', 'width' => 32),
						array('title' => '退款总金额（元）', 'field' => 'refund_order_paymoney', 'width' => 32),
				);

				$title = '团长销售额统计';

				D('Seller/Excel')->export($data, array('title' => $title, 'columns' => $columns));

			}
		}

		return $data;


	}


	private function head_commiss_analys( $keyword,$searchtime , $starttime , $endtime , $pindex , $psize )
	{
		$_GPC = I('request.');

		$data = array();
		//1、寻找团长

		$where = " 1 ";
		$tj_where = "  ";
		if( !empty($searchtime) )
		{
			$where .= " and date_added >= {$starttime} and date_added <= {$endtime} ";

			$tj_where .= " and addtime >= {$starttime} and addtime <= {$endtime} ";
		}
		//原先获取数据
		/*$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where {$where} group by head_id ";

		$order_ids_all = M()->query($sql);



		$head_ids_arr = array();

		if( !empty($order_ids_all) )
		{
			foreach( $order_ids_all as $val )
			{
				$head_ids_arr[] = $val['head_id'];
			}
		}

		$search_head_list = array();

		if( !empty($keyword) )
		{

			$sql = " SELECT ch.id  FROM " . C('DB_PREFIX'). "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id
						WHERE   (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%'  ) ";


			$community_head_list = M()->query( $sql );

			//var_dump($community_head_list,$sql);die();

			if( !empty($community_head_list) )
			{
				foreach( $community_head_list as $val )
				{
					$search_head_list[] = $val['id'];
				}
			}
			//交集
			$head_ids_arr = array_intersect($head_ids_arr, $search_head_list);
		}*/


		//进行分页操作
		/*$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order co left join " . C('DB_PREFIX'). "eaterplanet_community_head as ch on co.head_id=ch.id left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id where {$where} and ch.member_id > 0 group by head_id ";
		if( !empty($keyword) ){
			$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order co left join " . C('DB_PREFIX'). "eaterplanet_community_head as ch on co.head_id=ch.id left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id"
					." WHERE  (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%')  {$where} group by head_id ";
		}
		$sql2 = "";
		if( !empty($keyword) ){
			$sql = " SELECT DISTINCT(ch.id) as head_id  FROM " . C('DB_PREFIX'). "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id
						WHERE   (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%'  )  ";
		}
		if(!empty($sql2)){
			$sql = $sql . " union all ".$sql2;
		}
		$sql = 'select distinct(head_id) as head_id from ('.$sql . ') t where head_id > 0';*/

		$sql = " SELECT ch.id as head_id,ch.level_id  FROM " . C('DB_PREFIX'). "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id where 1=1 ";
		if( !empty($keyword) ){
			$sql = $sql." and (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%'  ) ";
		}
		if( isset($_GPC['export']) && $_GPC['export'] == 1 ) {
			$sql_list = 'select distinct(head_id) as head_id from (' . $sql . ') t where head_id > 0  ';
		}else{

			$sql_list = 'select distinct(head_id) as head_id from (' . $sql . ') t where head_id > 0 order by head_id asc limit ' . (($pindex - 1) * $psize) . ',' . $psize;
			$total_list = M()->query("select count(1) as count from (" . $sql . ") ct where head_id > 0");
			$total = 0;
			if(!empty($total_list) && count($total_list) > 0){
				$total = $total_list[0]['count'];
			}
			$pager = pagination2($total, $pindex, $psize);
			$data['pager'] = $pager;
		}

		$order_ids_all = M()->query($sql_list);

		$head_ids_arr = array();

		if( !empty($order_ids_all) )
		{
			foreach( $order_ids_all as $val )
			{
				$head_ids_arr[] = $val['head_id'];
			}
		}


		//----------------以上是搜索团长的代码

		//---------------团长等级begin-------------
		/*$level_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_commission_level ";

		$level_list = M()->query($level_sql);

		$level_arr = array(0 => '默认等级');

		foreach( $level_list as $vv )
		{
			$level_arr[ $v['id'] ] = $vv['levelname'];
		}*/

		$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();

		$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');
		$default_comunity_money = D('Home/Front')->get_config_by_name('default_comunity_money');

		$list_default = array(
				array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);
		if(empty($community_head_level)){
			$community_head_level = array();
		}
		$community_head_level = array_merge($list_default, $community_head_level);

		$level_arr = array();

		foreach($community_head_level as $kk => $vv)
		{
			$level_arr[$vv['id']] = $vv['levelname'];
		}

		//---------------团长等级end---------------


		if( empty($head_ids_arr) )
		{
			return $data;
		}else{

			foreach($head_ids_arr as $head_id)
			{
				$tmp = array();

				$head_info = M('eaterplanet_community_head')->where( array('id' => $head_id ) )->find();

				if( empty($head_info['member_id']) )
				{
					continue;
				}
				//ims_eaterplanet_ecommerce_member

				$mb_info = M('eaterplanet_ecommerce_member')->field('username')->where( array('member_id' => $head_info['member_id'] ) )->find();

				$tmp['head_id'] = $head_id;
				$tmp['username'] = $mb_info['username'];
				$tmp['community_name'] = $head_info['community_name'];
				$tmp['head_name'] = $head_info['head_name'];
				$tmp['head_mobile'] = $head_info['head_mobile'];
				$tmp['head_levelname'] = $level_arr[ $head_info['level_id'] ];

				$head_commiss = M('eaterplanet_community_head_commiss')->where( array('head_id' =>$head_id ) )->find();

				//下单佣金(元) orderbuy (1,2)
				$sum_order_commiss = M('eaterplanet_community_head_commiss_order')->where("head_id = {$head_id} and state in (1,2) and type = 'orderbuy'  {$tj_where}")->sum('money');

				$tmp['sum_order_commiss'] = $sum_order_commiss;
				//退款佣金(元) orderbuy(2)
				$sum_order_refundcommiss = M('eaterplanet_community_head_commiss_order')->where(" head_id = {$head_id} and state = 2 and type = 'orderbuy'  {$tj_where} ")->sum('money');

				$tmp['sum_order_refundcommiss'] = $sum_order_refundcommiss;

				//下级下单佣金(元) commiss tuijian (1,2)
				$childsum_order_commiss = M('eaterplanet_community_head_commiss_order')->where(" head_id = {$head_id} and state in (1,2) and type in('commiss', 'tuijian') {$tj_where} ")->sum('money');

				$tmp['childsum_order_commiss'] = $childsum_order_commiss;

				//下级退款佣金(元) commiss tuijian (2)
				$childsum_order_refundcommiss = M('eaterplanet_community_head_commiss_order')->where("head_id = {$head_id} and state = 2 and type in('commiss', 'tuijian')  {$tj_where}")->sum('money');

				$tmp['childsum_order_refundcommiss'] = $childsum_order_refundcommiss;

				//净佣金(元)
				$real_commiss_money = $sum_order_commiss + $childsum_order_commiss - $sum_order_refundcommiss - $childsum_order_refundcommiss;
				$tmp['real_commiss_money'] = $real_commiss_money;

				//申请提现佣金(元)
				$tmp['dongmoney'] = $head_commiss['dongmoney'];
				//提现到帐佣金(元)
				$tmp['getmoney'] = $head_commiss['getmoney'];

				$data[] = $tmp;
			}

			if( isset($_GPC['export']) && $_GPC['export'] == 1 )
			{
				$columns = array(
						array('title' => 'ID', 'field' => 'head_id', 'width' => 32),
						array('title' => '团长昵称', 'field' => 'username', 'width' => 32),
						array('title' => '团长姓名', 'field' => 'head_name', 'width' => 32),
						array('title' => '团长手机号', 'field' => 'head_mobile', 'width' => 32),
						array('title' => '小区信息', 'field' => 'community_name', 'width' => 32),
						array('title' => '团长等级', 'field' => 'head_levelname', 'width' => 32),
						array('title' => '下单佣金(元)', 'field' => 'sum_order_commiss', 'width' => 32),
						array('title' => '退款佣金(元)', 'field' => 'sum_order_refundcommiss', 'width' => 32),
						array('title' => '下级下单佣金(元)', 'field' => 'childsum_order_commiss', 'width' => 32),
						array('title' => '下级退款佣金(元)', 'field' => 'childsum_order_refundcommiss', 'width' => 32),
						array('title' => '净佣金(元)', 'field' => 'real_commiss_money', 'width' => 32),
						array('title' => '申请提现佣金(元)', 'field' => 'dongmoney', 'width' => 32),
						array('title' => '提现到帐佣金(元)', 'field' => 'getmoney', 'width' => 32),
				);

				$title = '团长佣金金额统计';

				D('Seller/Excel')->export($data, array('title' => $title, 'columns' => $columns));

			}
			return $data;
		}
	}

	//团长销售额统计
	private function head_sale_analys( $keyword,$searchtime , $starttime , $endtime , $pindex , $psize)
	{
		$_GPC = I('request.');

		$data = array();
		//1、寻找团长

		$where = " and type !=  'integral' ";
		$refund_where = " ";
		if( !empty($searchtime) )
		{
			$where .= " and date_added >= {$starttime} and date_added <= {$endtime} ";

			$refund_where .= " and addtime >= {$starttime} and addtime <= {$endtime} ";
		}

		/*$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where 1 {$where} group by head_id ";

		$order_ids_all = M()->query($sql);

		$head_ids_arr = array();

		if( !empty($order_ids_all) )
		{
			foreach( $order_ids_all as $val )
			{
				$head_ids_arr[] = $val['head_id'];
			}
		}

		$search_head_list = array();

		if( !empty($keyword) )
		{
			$sql = " SELECT ch.id  FROM " . C('DB_PREFIX'). "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id
						WHERE  (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%'  ) ";


			$community_head_list = M()->query( $sql );

			if( !empty($community_head_list) )
			{
				foreach( $community_head_list as $val )
				{
					$search_head_list[] = $val['id'];
				}
			}
			//交集
			$head_ids_arr = array_intersect($head_ids_arr, $search_head_list);
		}*/

		//进行分页操作
		/*$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order co left join " . C('DB_PREFIX'). "eaterplanet_community_head as ch on co.head_id=ch.id left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id where 1 {$where} and ch.member_id > 0 group by head_id ";
		if( !empty($keyword) ){
			$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order co left join " . C('DB_PREFIX'). "eaterplanet_community_head as ch on co.head_id=ch.id left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id"
					." WHERE  (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%')  {$where} group by head_id ";
		}
		$sql2 = "";
		if( !empty($keyword) ){
			$sql2 = " SELECT DISTINCT (ch.id) as head_id  FROM " . C('DB_PREFIX'). "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id
					WHERE  (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%'  ) ";
		}
		if(!empty($sql2)){
			$sql = $sql . " union all ".$sql2;
		}*/
		$sql = " SELECT ch.id as head_id,ch.level_id  FROM " . C('DB_PREFIX'). "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id where 1=1 ";
		if( !empty($keyword) ){
			$sql = $sql." and (m.username like '%{$keyword}%' or ch.head_name like '%{$keyword}%' or ch.community_name like '%{$keyword}%'  ) ";
		}
		//$sql = 'select distinct(head_id) as head_id from ('.$sql . ') t where head_id > 0';
		if( isset($_GPC['export']) && $_GPC['export'] == 1 ) {
			$sql_list = 'select distinct(head_id) as head_id from (' . $sql . ') t where head_id > 0  ';
		}else{

			$sql_list = 'select distinct(head_id) as head_id from (' . $sql . ') t where head_id > 0 order by head_id asc limit ' . (($pindex - 1) * $psize) . ',' . $psize;

			$total_list = M()->query("select count(1) as count from ($sql) t where head_id > 0");

			$total = 0;
			if(!empty($total_list) && count($total_list) > 0){
				$total = $total_list[0]['count'];
			}
			$pager = pagination2($total, $pindex, $psize);
			$data['pager'] = $pager;
		}

		$order_ids_all = M()->query($sql_list);

		$head_ids_arr = array();

		if( !empty($order_ids_all) )
		{
			foreach( $order_ids_all as $val )
			{
				$head_ids_arr[] = $val['head_id'];
			}
		}

		//----------------以上是搜索团长的代码

		//---------------团长等级begin-------------
		/*$level_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_commission_level ";

		$level_list = M()->query($level_sql );

		$level_arr = array(0 => '默认等级');

		foreach( $level_list as $vv )
		{
			$level_arr[ $v['id'] ] = $vv['levelname'];
		}*/

		$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();

		$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');
		$default_comunity_money = D('Home/Front')->get_config_by_name('default_comunity_money');

		$list_default = array(
				array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);
		if(empty($community_head_level)){
			$community_head_level = array();
		}
		$community_head_level = array_merge($list_default, $community_head_level);

		$level_arr = array();

		foreach($community_head_level as $kk => $vv)
		{
			$level_arr[$vv['id']] = $vv['levelname'];
		}

		//---------------团长等级end---------------


		if( empty($head_ids_arr) )
		{
			return $data;
		}else{

			foreach($head_ids_arr as $head_id)
			{
				$tmp = array();

				$head_info = M('eaterplanet_community_head')->where( array('id' => $head_id ) )->find();

				if( empty($head_info['member_id']) )
				{
					continue;
				}

				$mb_info = M('eaterplanet_ecommerce_member')->field('username')->where( array('member_id' => $head_info['member_id'] ) )->find();

				$tmp['head_id'] = $head_id;
				$tmp['username'] = $mb_info['username'];
				$tmp['community_name'] = $head_info['community_name'];
				$tmp['head_name'] = $head_info['head_name'];
				$tmp['head_mobile'] = $head_info['head_mobile'];
				$tmp['head_levelname'] = $level_arr[ $head_info['level_id'] ];

				//下单客户数(支付的+退款的)
				$buy_mb_count_arr = M()->query("SELECT  count( DISTINCT(member_id) ) as count FROM ".C('DB_PREFIX').
								"eaterplanet_ecommerce_order WHERE 1 and head_id = {$head_id} and order_status_id in(1,4,5,6,7,11,12,14) {$where} ");

				$buy_mb_count = $buy_mb_count_arr[0]['count'];

				$tmp['buy_mb_count'] = $buy_mb_count;

				//下单数量(支付的+退款的)

				$buy_order_count = M('eaterplanet_ecommerce_order')->where(" head_id = {$head_id} and order_status_id in(1,4,5,6,7,11,12,14) {$where} ")->count();


				$tmp['buy_order_count'] = $buy_order_count;
				//销售额(支付的+退款的)

				$sum_order_paymoney = M('eaterplanet_ecommerce_order')->where("head_id = {$head_id} and order_status_id in(1,4,5,6,7,11,12,14) {$where}")->sum('total+shipping_fare-fare_shipping_free-voucher_credit-fullreduction_money+packing_fare');
				$sum_order_paymoney = sprintf('%.2f',$sum_order_paymoney);
				$tmp['sum_order_paymoney'] = $sum_order_paymoney;


				$tp_od_list = M('eaterplanet_ecommerce_order')->field('order_id')->where(" head_id = {$head_id}  {$where} ")->select();


				if( !empty($tp_od_list) )
				{
					$tp_od_arr = array();

					foreach( $tp_od_list as $tp_val )
					{
						$tp_od_arr[] = $tp_val['order_id'];
					}


					$has_refund_arr = M()->query("select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund where order_id in( ".implode(',', $tp_od_arr )." )   group by order_id ");

					$refund_order_count = count($has_refund_arr);

					//退款额(元)
					$refund_order_money = M('eaterplanet_ecommerce_order_goods_refund')->where(array('order_id' => array('in', $tp_od_arr) ))->sum('money+refund_shipping_fare');


				}else{
					$refund_order_count = 0;
					$refund_order_money = 0;

				}


				//$where .= " and date_added >= {$starttime} and date_added <= {$endtime} ";


				$tmp['refund_order_count'] = $refund_order_count;
				$tmp['refund_order_money'] = $refund_order_money;
				//净销售额(元) 销售额  -   退款额  =  净销售额


				$real_sale_money = round(($sum_order_paymoney - $refund_order_money) ,2);

				$tmp['real_sale_money'] = $real_sale_money;

				$data[] = $tmp;
			}

			if( isset($_GPC['export']) && $_GPC['export'] == 1 )
			{
				$columns = array(
						array('title' => 'ID', 'field' => 'head_id', 'width' => 32),
						array('title' => '团长昵称', 'field' => 'username', 'width' => 32),
						array('title' => '团长姓名', 'field' => 'head_name', 'width' => 32),
						array('title' => '团长手机号', 'field' => 'head_mobile', 'width' => 32),
						array('title' => '小区信息', 'field' => 'community_name', 'width' => 32),
						array('title' => '团长等级', 'field' => 'head_levelname', 'width' => 32),
						array('title' => '下单客户数', 'field' => 'buy_mb_count', 'width' => 32),
						array('title' => '下单数量', 'field' => 'buy_order_count', 'width' => 32),
						array('title' => '销售额(元)', 'field' => 'sum_order_paymoney', 'width' => 32),
						array('title' => '退款量', 'field' => 'refund_order_count', 'width' => 32),
						array('title' => '退款额(元)', 'field' => 'refund_order_money', 'width' => 32),
						array('title' => '净销售额(元)', 'field' => 'real_sale_money', 'width' => 32),
				);

				$title = '团长销售额统计';

				D('Seller/Excel')->export($data, array('title' => $title, 'columns' => $columns));

			}

		}

		return $data;
	}

	//找出这段时间团长的方法
	private function head_sale_analys_back( $keyword,$searchtime , $starttime , $endtime )
	{
		$_GPC = I('request.');

		$data = array();
		//1、寻找团长

		$where = " ";
		if( !empty($searchtime) )
		{
			$where .= " and date_added >= {$starttime} and date_added <= {$endtime} ";
		}

		$sql = "select head_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order where 1 {$where} group by head_id ";

		$order_ids_all = M()->query($sql );

		$head_ids_arr = array();

		if( !empty($order_ids_all) )
		{
			foreach( $order_ids_all as $val )
			{
				$head_ids_arr[] = $val['head_id'];
			}
		}

		$search_head_list = array();

		if( !empty($keyword) )
		{
			$sql = "select id from ".C('DB_PREFIX').
					"eaterplanet_community_head where  (head_name like '%{$keyword}%' or community_name like '%{$keyword}%' )  ";

			$community_head_list = M()->query( $sql );

			if( !empty($community_head_list) )
			{
				foreach( $community_head_list as $val )
				{
					$search_head_list[] = $val['id'];
				}
			}
			//交集
			$head_ids_arr = array_intersect($head_ids_arr, $search_head_list);
		}

		//----------------以上是搜索团长的代码

		//---------------团长等级begin-------------
		$level_sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_commission_level  ";

		$level_list = M()->query($level_sql);

		$level_arr = array(0 => '默认等级');

		foreach( $level_list as $vv )
		{
			$level_arr[ $v['id'] ] = $vv['levelname'];
		}

		//---------------团长等级end---------------


		if( empty($head_ids_arr) )
		{
			return $data;
		}else{

			foreach($head_ids_arr as $head_id)
			{
				$tmp = array();

				$head_info = M('eaterplanet_community_head')->where( array('id' => $head_id ) )->find();

				$tmp['community_name'] = $head_info['community_name'];
				$tmp['head_name'] = $head_info['head_name'];
				$tmp['head_mobile'] = $head_info['head_mobile'];
				$tmp['head_levelname'] = $level_arr[ $head_info['level_id'] ];

				//下单客户数
				//下单数量
				//销售额

				//level_id

				$data[] = $tmp;
			}
		}

		return $data;
	}

	private function deep_in_array($value, $array) {
		foreach($array as $item) {
			if(!is_array($item)) {
				if ($item == $value) {
					return true;
				} else {
					continue;
				}
			}
			if(in_array($value, $item)) {
				return true;
			} else if($this->deep_in_array($value, $item)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 毛利统计
	 */
	public function goodstatics(){
		$_GPC = I('request.');
		$pindex    = I('request.page', 1);
		$psize     = 20;
		//商品名称
		$keyword = I('get.keyword','','addslashes');
		$keyword2 = stripslashes($keyword);
		$this->keyword = $keyword2;

		$condition = " and og.cost_price is not NULL ";
		if (!empty($keyword)) {
			$condition .= " and og.name like '%".$keyword."%' ";
		}
		$starttime = strtotime( date('Y-m-d').' 00:00:00' );
		$endtime   = $starttime + 86400;
		$searchtime = $_GPC['searchtime'];

		if( !empty($searchtime) )
		{
			$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
			$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
			$condition .= " and og.addtime >= {$starttime} and og.addtime <= {$endtime} ";
		}
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->searchtime = $searchtime;
		$sql = " SELECT og.goods_id,og.name,og.rela_goodsoption_valueid,og.price,og.cost_price,SUM( og.quantity ) as quantity, "
				. " SUM( og.quantity * og.price ) as total_price,SUM( og.quantity * og.cost_price ) as cost_total_price,sum(og.profit_total) as profit_total "
				. " FROM ". C('DB_PREFIX'). "eaterplanet_ecommerce_order_goods as og "
				. " left join ". C('DB_PREFIX'). "eaterplanet_ecommerce_order as o on og.order_id=o.order_id "
				. " left join ". C('DB_PREFIX'). "eaterplanet_ecommerce_goods as g on g.id=og.goods_id "
				. " WHERE 1=1 and o.order_status_id in (6,11) and g.type != 'integral' "
				. $condition . " group by og.goods_id, og.name, og.rela_goodsoption_valueid,og.price,og.cost_price ";
		if( isset($_GPC['export']) && $_GPC['export'] == 1 )
		{
			$t_sql = " select * from ( " .$sql. " ) t "
					. " order by profit_total desc ";
			$list = M()->query($t_sql);
			$i = 1;
			foreach ($list as $k=>$v) {
				$list[$k]['rank'] = $i;
				$list[$k]['profit_fee'] = round($v['profit_total']/$v['total_price']*100,2);
				$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where(array('goods_id'=>$v['goods_id'],'rela_goodsoption_valueid'=>$v['rela_goodsoption_valueid']))->find();
				$list[$k]['option_sku'] = D('Seller/Order')->get_order_option_sku($order_goods_info['order_id'], $order_goods_info['order_goods_id']);
				//商品名称
				/*$goods_info = M('eaterplanet_ecommerce_goods')->where(array('id'=>$v['goods_id']))->field('goodsname')->find();
				if(!empty($goods_info)){
					$list[$k]['name'] = $goods_info['goodsname'];
				}*/
				$i++;
			}
			$columns = array(
					array('title' => '排名', 'field' => 'rank', 'width' => 32),
					array('title' => '商品名称', 'field' => 'name', 'width' => 32),
					array('title' => '规格', 'field' => 'option_sku', 'width' => 32),
					array('title' => '商城售价(元)', 'field' => 'price', 'width' => 32),
					array('title' => '成本价(元)', 'field' => 'cost_price', 'width' => 32),
					array('title' => '商品销量', 'field' => 'quantity', 'width' => 32),
					array('title' => '商品销量总价(元) ', 'field' => 'total_price', 'width' => 32),
					array('title' => '商品销量成本总价(元)', 'field' => 'cost_total_price', 'width' => 32),
					array('title' => '毛利额', 'field' => 'profit_total', 'width' => 32),
					array('title' => '毛利率(%)', 'field' => 'profit_fee', 'width' => 32),
			);
			$data = array();
			foreach($list as $k=>$v){
				$tmp = array();
				$tmp['rank'] = $v['rank'];
				$tmp['name'] = $v['name'];
				$tmp['option_sku'] = $v['option_sku'];
				$tmp['price'] = $v['price'];
				$tmp['cost_price'] = $v['cost_price'];
				$tmp['quantity'] = $v['quantity'];
				$tmp['total_price'] = $v['total_price'];
				$tmp['cost_total_price'] = $v['cost_total_price'];
				$tmp['profit_total'] = $v['profit_total'];
				$tmp['profit_fee'] = $v['profit_fee'];
				$data[] = $tmp;
			}
			$title = '商品毛利统计';
			D('Seller/Excel')->export($data, array('title' => $title, 'columns' => $columns));
		}else{
			$t_sql = " select * from ( " .$sql. " ) t "
					. " order by profit_total desc limit " . (($pindex - 1) * $psize) . "," . $psize;

			$list = M()->query($t_sql);
			$i = ($pindex-1)*$psize + 1;
			foreach ($list as $k=>$v) {
				$list[$k]['rank'] = $i;
				$list[$k]['profit_fee'] = round($v['profit_total']/$v['total_price']*100,2);
				$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where(array('goods_id'=>$v['goods_id'],'rela_goodsoption_valueid'=>$v['rela_goodsoption_valueid']))->find();
				$list[$k]['option_sku'] = D('Seller/Order')->get_order_option_sku($order_goods_info['order_id'], $order_goods_info['order_goods_id']);
				//商品名称
				/*$goods_info = M('eaterplanet_ecommerce_goods')->where(array('id'=>$v['goods_id']))->field('goodsname')->find();
				if(!empty($goods_info)){
					$list[$k]['name'] = $goods_info['goodsname'];
				}*/
				$i++;
			}
			$total_list = M()->query("select count(1) as count from (".$sql.") as t");
			$total = $total_list[0]['count'];
			$pager = pagination2($total, $pindex, $psize);

			//商品总销量，商品总销售额
			$all_sql = "select sum(og.quantity) as quantity,sum(og.quantity * og.price) as total_price "
					. " from ". C('DB_PREFIX'). "eaterplanet_ecommerce_order_goods as og "
					. " left join ". C('DB_PREFIX'). "eaterplanet_ecommerce_order as o on og.order_id=o.order_id "
					. " left join ". C('DB_PREFIX'). "eaterplanet_ecommerce_goods as g on g.id=og.goods_id "
					. " WHERE 1=1 and o.order_status_id  in (6,11) and g.type != 'integral' "
					. $condition;
			$all_list = M()->query($all_sql);


			$this->all_quantity = !empty($all_list[0]['quantity']) ? $all_list[0]['quantity'] : 0;
			$this->all_total = !empty($all_list[0]['total_price']) ? $all_list[0]['total_price'] : 0;
			$this->list = $list;
			$this->pager = $pager;
			$this->gpc = $_GPC;
			$this->display();
		}
	}
}
?>
