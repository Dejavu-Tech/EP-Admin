<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.cloud/
 * @copyright Copyright (c) 2019-2024 Dejavu Tech.
 * @license   https://github.com/Dejavu-Tech/EP-Admin/blob/main/LICENSE
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
			//订单取消原因来源 11:达达快送员取消订单
			$other_data['cancel_from'] = 11;

			D('Seller/Order')->do_localtown_thirth_delivery_return($messageBody['orderId'],0,$other_data);

			$result_array = array();
			$result_array['status'] = "ok";
			echo  json_encode($result_array);
		}else if(isset($json_data['app_id']) && isset($json_data['data']) && isset($json_data['salt']) && isset($json_data['signature'])){
			//蜂鸟即配 订单状态变更回调
			$app_id = D('Home/Front')->get_config_by_name('localtown_ele_app_id');
			$secret_key = D('Home/Front')->get_config_by_name('localtown_ele_secret_key');
			// 获取签名
			$sig = $this->eleGenerateSign($app_id, $json_data['salt'], $secret_key);
			if($sig == $json_data['signature']){
				$data = urldecode($json_data['data']);
				//商户自己的订单号
				$partner_order_code = $data['partner_order_code'];
				//状态码
				$order_status = $data['order_status'];

				$order_sn = $this->getOrderSnByThirdOrderId($partner_order_code);
				if(!empty($order_sn)){
					$other_data = [];
					$other_data['data_type'] = 'ele';
					if($order_status == 1){//系统已接单
						$other_data['desc'] = '已接单';
						$other_data['order_status'] = 2;
						D('Seller/Order')->do_localtown_thirth_delivery_return($order_sn,0,$other_data);
					}else if($order_status == 20){//已分配配送员
						//配送员姓名
						$other_data['operator_name'] = $data['carrier_driver_name'];
						//配送员电话
						$other_data['operator_phone'] = $data['carrier_driver_phone'];
						$other_data['desc'] = "已分配配送员：".$other_data['operator_name'];
						$other_data['order_status'] = 3;
						D('Seller/Order')->do_localtown_thirth_delivery_return($order_sn,0,$other_data);
					}else if($order_status == 80){//配送员已到店
						//配送员姓名
						$other_data['operator_name'] = $data['carrier_driver_name'];
						//配送员电话
						$other_data['operator_phone'] = $data['carrier_driver_phone'];
						$other_data['desc'] = "配送员".$other_data['operator_name']."已到店";
						$other_data['order_status'] = 3;
						D('Seller/Order')->do_localtown_thirth_delivery_return($order_sn,0,$other_data);
					}else if($order_status == 2){//配送中
						//配送员姓名
						$other_data['operator_name'] = $data['carrier_driver_name'];
						//配送员电话
						$other_data['operator_phone'] = $data['carrier_driver_phone'];
						$other_data['desc'] = "配送员".$other_data['operator_name']."配送中";
						$other_data['order_status'] = 3;
						D('Seller/Order')->do_localtown_thirth_delivery_return($order_sn,0,$other_data);
					}else if($order_status == 3){//已送达
						//配送员姓名
						$other_data['operator_name'] = $data['carrier_driver_name'];
						//配送员电话
						$other_data['operator_phone'] = $data['carrier_driver_phone'];
						$other_data['desc'] = "配送员".$data['carrier_driver_name']."已送达";
						$other_data['order_status'] = 4;
						D('Seller/Order')->do_localtown_thirth_delivery_return($order_sn,6,$other_data);
					}else if($order_status == 5){//异常
						$other_data['error_code'] = $data['error_code'];
						$other_data['desc'] = $data['detail_description'];
						$other_data['order_status'] = 100;
						D('Seller/Order')->do_localtown_thirth_delivery_return($order_sn,0,$other_data);
					}
				}
			}
		}
	}

	/**
	 * @author Albert.Z
	 * @desc 通过第三方商户获取订单号
	 * @param $third_order_id
	 * @return string
	 */
	public function getOrderSnByThirdOrderId($third_order_id){
		$orders = M('eaterplanet_ecommerce_orderdistribution_order')->field('order_id')->where( array('third_order_id' => $third_order_id) )->find();
		if(!empty($orders)){
			$order_info = M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $orders['order_id']) )->find();
			if(!empty($order_info)){
				return $order_info['order_num_alias'];
			}else{
				return '';
			}
		}else{
			return '';
		}
	}
	/**
	 * @author Albert.Z
	 * @desc 蜂鸟即配签名
	 * @param $appId
	 * @param $salt
	 * @param $secretKey
	 * @return string
	 */
	public function eleGenerateSign($appId, $salt, $secretKey) {
		$seed = 'app_id=' . $appId . '&salt=' . $salt . '&secret_key=' . $secretKey;
		return md5(urlencode($seed));
	}
}
