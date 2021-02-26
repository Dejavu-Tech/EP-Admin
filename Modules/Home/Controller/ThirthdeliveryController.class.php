<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      http://www.ch871.com/
 * @copyright Copyright (c) 2019-2021 ch871.com.
 * @license   http://www.ch871.com/license.html License
 * ==========================================================================
 * 第三方配送回调地址
 * @author    Albert.Z  2020.08.06
 *
 */
namespace Home\Controller;

class ThirthdeliveryController extends CommonController {

	protected function _initialize()
    {
    	parent::_initialize();
	}

	public function notify(){
		$return_json_data = file_get_contents("php://input");
		$json_data = json_decode($return_json_data,true);
		//达达平台回调接口
		if(isset($json_data['client_id']) && isset($json_data['order_id']) && isset($json_data['order_status']) && isset($json_data['signature'])){
			//error_log($return_json_data.'///',3,'logs/error_imdada_'.date('Ymd').'.log');
			$other_data = array();
			$other_data['data_type'] = 'imdada';
			$other_data['order_status'] = $json_data['order_status'];
			if($json_data['order_status'] == 2){//待取货
				$other_data['dm_id'] = $json_data['dm_id'];
				$other_data['dm_name'] = $json_data['dm_name'];
				$other_data['dm_mobile'] = $json_data['dm_mobile'];
				$other_data['third_id'] = $json_data['client_id'];
				D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['order_id'],0,$other_data);
			}else if($json_data['order_status'] == 3){//配送中
				$other_data['dm_id'] = $json_data['dm_id'];
				$other_data['dm_name'] = $json_data['dm_name'];
				$other_data['dm_mobile'] = $json_data['dm_mobile'];
				$other_data['third_id'] = $json_data['client_id'];
				D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['order_id'],0,$other_data);
			}else if($json_data['order_status'] == 4){//已完成
				D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['order_id'],6,$other_data);
			}else if($json_data['order_status'] == 5){//已取消
				//订单取消原因
				$other_data['cancel_reason'] = $json_data['cancel_reason'];
				//订单取消原因来源
				$other_data['cancel_from'] = $json_data['cancel_from'];
				D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['order_id'],0,$other_data);
			}else if($json_data['order_status'] == 9){//妥投异常之物品返回中
				//订单取消原因
				$other_data['cancel_reason'] = "妥投异常-物品返回中";
				//订单取消原因来源
				$other_data['cancel_from'] = $json_data['cancel_from'];
				D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['order_id'],0,$other_data);
			}else if($json_data['order_status'] == 10){//妥投异常之物品返回完成
				//订单取消原因
				$other_data['cancel_reason'] = "妥投异常-物品返回完成";
				//订单取消原因来源
				$other_data['cancel_from'] = $json_data['cancel_from'];
				D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['order_id'],0,$other_data);
			}else if($json_data['order_status'] == 1000){//创建达达运单失败
				//订单取消原因
				$other_data['cancel_reason'] = $json_data['cancel_reason'];
				//订单取消原因来源
				$other_data['cancel_from'] = $json_data['cancel_from'];
				D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['order_id'],0,$other_data);
			}
			echo 'notify';
		}else if(isset($_GET['sign']) && isset($json_data['sf_order_id']) && isset($json_data['shop_order_id']) && isset($json_data['url_index'])  && isset($json_data['push_time'])){
			//顺丰同城回调接口
			$sign = $_GET['sign'];
			//error_log($sign.'==='.$return_json_data.'///',3,'logs/error_sf_'.date('Ymd').'.log');
			$dev_id = D('Home/Front')->get_config_by_name('localtown_sf_dev_id');
			$dev_key = D('Home/Front')->get_config_by_name('localtown_sf_dev_key');
			if ($sign && $sign == base64_encode(MD5("{$return_json_data}&{$dev_id}&{$dev_key}"))) {

				$other_data = array();
				$url_index = $json_data['url_index'];
				$other_data['data_type'] = 'sf';
				if($url_index == 'sf_cancel'){//取消订单
					//订单取消原因
					$other_data['cancel_reason'] = $json_data['cancel_reason'];
					//状态描述
					$other_data['status_desc'] = $json_data['status_desc'];
					//操作人
					$other_data['operator_name'] = $json_data['operator_name'];
					//操作状态 2:订单取消
					$other_data['order_status'] = $json_data['order_status'];
					D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['shop_order_id'],0,$other_data);
				}else if($url_index == 'rider_status'){//配送状态更改回调
					//配送员姓名
					$other_data['operator_name'] = $json_data['operator_name'];
					//配送员电话
					$other_data['operator_phone'] = $json_data['operator_phone'];
					//操作状态 10-配送员确认;12:配送员到店;15:配送员配送中
					$other_data['order_status'] = $json_data['order_status'];
					//备注信息
					$other_data['status_desc'] = $json_data['status_desc'];
					D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['shop_order_id'],0,$other_data);
				}else if($url_index == 'rider_exception'){//订单异常回调
					//异常ID
					$other_data['ex_id'] = $json_data['ex_id'];
					//异常详情
					$other_data['ex_content'] = $json_data['ex_content'];
					//异常状态 0:订单异常
					$other_data['order_status'] = $json_data['order_status'];
					D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['shop_order_id'],0,$other_data);
				}else if($url_index == 'order_complete'){//订单完成
					//操作状态 17-配送员点击完成
					$other_data['order_status'] = $json_data['order_status'];
					D('Seller/Order')->do_localtown_thirth_delivery_return($json_data['shop_order_id'],6,$other_data);
				}
				$result_array = array();
				$result_array['error_code'] = 0;
				$result_array['error_msg'] = "success";
				echo json_encode($result_array);
			}
		}else if(isset($json_data['messageType']) && isset($json_data['messageBody'])){
			//达达消息通知：骑士取消订单通知
			$messageBody = json_decode($json_data['messageBody'],true);
			$other_data = array();
			$other_data['data_type'] = 'imdada';
			$other_data['order_status'] = 5;
			//订单取消原因
			$other_data['cancel_reason'] = $messageBody['cancelReason'];
			//订单取消原因来源 11:达达骑手取消订单
			$other_data['cancel_from'] = 11;

			D('Seller/Order')->do_localtown_thirth_delivery_return($messageBody['orderId'],0,$other_data);

			$result_array = array();
			$result_array['status'] = "ok";
			echo  json_encode($result_array);
		}
	}
}
