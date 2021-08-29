<?php
namespace Home\Model;
use Think\Model;
/**
 * 门店模块
 * @author Albert.Z
 *
 */
class SalesroomModel{

	public $table = 'pin';
	/**
	 * 获取商品门店列表
	 * @param $goods_id 商品id
	 * @param $supply_id 商户id
	 * @return mixed
	 */
	public function get_goods_salesroom($goods_id,$supply_id,$field="*")
	{
		$list = array();

	    $salesroom_base = M('eaterplanet_ecommerce_goods_salesroombase')->where(array('goods_id'=>$goods_id))->find();
		if($salesroom_base['hx_assign_salesroom'] == 0){
			$list =  M('eaterplanet_ecommerce_salesroom')->where(array('supply_id'=>$supply_id,'state'=>1))->field($field)->select();
		}else{
			$goods_salesroom_list =   M('eaterplanet_ecommerce_goods_relative_salesroom')->where(array('goods_id'=>$goods_id))->field('salesroom_id')->select();
			if(!empty($goods_salesroom_list)){
				$salesroom_ids = "";
				foreach($goods_salesroom_list as $k=>$v){
					if(empty($salesroom_ids)){
						$salesroom_ids = $v['salesroom_id'];
					}else{
						$salesroom_ids = $salesroom_ids .','. $v['salesroom_id'];
					}
				}
				$list =  M('eaterplanet_ecommerce_salesroom')->where(array('supply_id'=>$supply_id,'state'=>1))->where("id in (".$salesroom_ids.")")->field($field)->select();
			}
		}
	    return $list;
	}

	/**
	 * 获取订单中所有门店信息
	 * @param $order_id
	 * @param string $field
	 * @return array|mixed
	 */
	public function get_order_salesroom($order_id,$page=0){
		$list = array();
		$all_salesroom_ids = "";
		$sql = "select gs.*,og.supply_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_saleshexiao as gs "
		     . " left join  " .C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og on og.goods_id = gs.goods_id "
			 . " where  og.order_id=".$order_id
	         . " order by gs.id asc";
		$saleshexiao_list =  M()->query($sql);
		if(!empty($saleshexiao_list)){
			foreach($saleshexiao_list as $sk=>$sv){
				$goods_id = $sv['goods_id'];
				$salesroom_base = $sv;
				$supply_id = $sv['supply_id'];
				if($salesroom_base['hx_assign_salesroom'] == 0){
					$salesroom_list =  M('eaterplanet_ecommerce_salesroom')->where(array('supply_id'=>$supply_id,'state'=>1))->field('id')->select();
					if(!empty($salesroom_list)) {
						foreach ($salesroom_list as $v) {
							if (empty($all_salesroom_ids)) {
								$all_salesroom_ids = $v['id'];
							} else {
								$all_salesroom_ids = $all_salesroom_ids . ',' . $v['id'];
							}
						}
					}
				}else{
					$goods_salesroom_list = M('eaterplanet_ecommerce_order_goods_relative_salesroom')->where(array('goods_id'=>$goods_id,'order_id'=>$order_id))->field('salesroom_id')->select();
					if(!empty($goods_salesroom_list)){
						foreach($goods_salesroom_list as $k=>$v){
							if(empty($salesroom_ids)){
								$all_salesroom_ids = $v['salesroom_id'];
							}else{
								$all_salesroom_ids = $all_salesroom_ids .','. $v['salesroom_id'];
							}
						}
					}
				}
			}
			if(!empty($all_salesroom_ids)){
				if(!empty($page)){
					$size = 10;
					$offset = ($page - 1) * $size;
					$list =  M('eaterplanet_ecommerce_salesroom')->where("id in (".$all_salesroom_ids.")")->limit($offset,$size)->select();
				}else{
					$list =  M('eaterplanet_ecommerce_salesroom')->where("id in (".$all_salesroom_ids.")")->select();
				}
			}
		}
		if(!empty($list)){
			foreach($list as $k=>$v){
				$list[$k]['room_logo'] = tomedia($v['room_logo']);
			}
		}
		return $list;
	}

	/**
	 * 获取商品订单中所有门店信息
	 * @param $order_id
	 * @param string $field
	 * @return array|mixed
	 */
	public function get_order_goods_salesroom($order_goods_id,$member_lon,$member_lat,$page){
		$list = array();
		$all_salesroom_ids = "";
		$sql = "select gs.*,og.supply_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_saleshexiao as gs "
				. " left join  " .C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og on og.goods_id = gs.goods_id "
				. " where  og.order_goods_id=".$order_goods_id
				. " order by gs.id asc";
		$salesroom_base_list =  M()->query($sql);
		if(!empty($salesroom_base_list)) {
			foreach ($salesroom_base_list as $sk => $sv) {
				$goods_id = $sv['goods_id'];
				$salesroom_base = $sv;
				$supply_id = $sv['supply_id'];
				if ($salesroom_base['hx_assign_salesroom'] == 0) {
					$salesroom_list = M('eaterplanet_ecommerce_salesroom')->where(array('supply_id' => $supply_id, 'state' => 1))->field('id')->select();
					if (!empty($salesroom_list)) {
						foreach ($salesroom_list as $v) {
							if (empty($all_salesroom_ids)) {
								$all_salesroom_ids = $v['id'];
							} else {
								$all_salesroom_ids = $all_salesroom_ids . ',' . $v['id'];
							}
						}
					}
				} else {
					$goods_salesroom_list = M('eaterplanet_ecommerce_order_goods_relative_salesroom')->where(array('goods_id' => $goods_id,'order_goods_id'=>$order_goods_id))->field('salesroom_id')->select();
					if (!empty($goods_salesroom_list)) {
						foreach ($goods_salesroom_list as $k => $v) {
							if (empty($all_salesroom_ids)) {
								$all_salesroom_ids = $v['salesroom_id'];
							} else {
								$all_salesroom_ids = $all_salesroom_ids . ',' . $v['salesroom_id'];
							}
						}
					}
				}
			}
			if(!empty($all_salesroom_ids)){
				if (!empty($page)) {
					$size = 10;
					$offset = ($page - 1) * $size;
					$list = M('eaterplanet_ecommerce_salesroom')->where("id in (" . $all_salesroom_ids . ")")->limit($offset, $size)->select();
				} else {
					$list = M('eaterplanet_ecommerce_salesroom')->where("id in (" . $all_salesroom_ids . ")")->select();
				}
				foreach ($list as $slk => $slv) {
					$list[$slk]['distance'] = $this->cal_salesroom_distance($slv, $member_lon, $member_lat);
				}
				$distances = array_column($list, 'distance');
				array_multisort($distances, SORT_ASC, $list);
			}
		}
		return $list;
	}

	/**
	 * 获取客户与门店距离
	 * @param $salesroom_info 门店信息
	 * @param $member_lon	买家经度
	 * @param $member_lat  买家纬度
	 */
	public function cal_salesroom_distance($salesroom_info,$member_lon,$member_lat){
		//门店经度
		$room_lon = $salesroom_info['lon'];
		//门店纬度
		$room_lat = $salesroom_info['lat'];
		$distance = D('Seller/Communityhead')->GetDistance($room_lon, $room_lat, $member_lon, $member_lat);
		$distance = ceil($distance / 1000);//KM距离
		return $distance;
	}

	/**
	 * 获取订单最近一次核销记录
	 * @param $order_id
	 * @param string $order_goods_id
	 */
	public function get_last_ordergoods_hexiaorecord($order_id,$order_goods_id=""){
		$condition = array();
		$condition['order_id'] = $order_id;
		if(!empty($order_goods_id)){
			$condition['order_goods_id'] = $order_goods_id;
		}
		$hx_recode = M('eaterplanet_ecommerce_order_goods_saleshexiao_record')->where($condition)->order('addtime desc')->find();
		return $hx_recode;
	}

	/**
	 * 生成订单核销码
	 * @param $order_id
	 * @param $order_goods_id
	 * @param $hexiao_qr_code
	 * @return string
	 */
	public function _get_ordergoods_hxqrcode($order_id,$order_goods_id,$hexiao_qr_code)
	{
		$type = 1;
		if(!empty($order_goods_id)){
			$type = 2;
		}
		$hx_qrcode = $type.'_'.$hexiao_qr_code;
		//核销地址
		$hexiao_qrcode = D('Home/Pingoods')->_get_commmon_hxqrcode($hx_qrcode);

		if( empty($hexiao_qrcode) )
		{
			return '';
		}else{
			if($type == 1){
				M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->save( array('hexiao_qr_code' => $hexiao_qrcode) );
			}else if($type == 2){
				M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('order_id' => $order_id,'order_goods_id' => $order_goods_id ) )->save( array('hexiao_qr_code' => $hexiao_qrcode) );
			}
			return tomedia($hexiao_qrcode);
		}
	}

	/**
	 * 通过member_id获取核销员id
	 * @param $member_id
	 * @return int
	 */
	public function get_salesmember_id_by_member_id($member_id){
		$salesmember_info = M('eaterplanet_ecommerce_salesroom_member')->where(array('member_id'=>$member_id))->find();
		if(!empty($salesmember_info)){
			return $salesmember_info['id'];
		}else{
			return 0;
		}
	}
	/**
	 * 通过核销员id获取核销门店列表
	 * @param $smember_id
	 * @return array
	 */
	public function get_salesrooms_by_smember_id($smember_id){
		$sql = "select sr.* from ".C('DB_PREFIX')."eaterplanet_ecommerce_salesroom as sr "
				. " left join  " .C('DB_PREFIX')."eaterplanet_ecommerce_salesroom_relative_member as srm on sr.id = srm.salesroom_id "
				. " where  srm.smember_id=".$smember_id." and sr.state=1"
				. " order by sr.id asc";
		$salesrooms_list =  M()->query($sql);
		foreach($salesrooms_list as $k=>$v){
			$v['room_logo'] = $v['room_logo']?tomedia($v['room_logo']):$v['room_logo'];
			$salesrooms_list[$k] = $v;
		}
		return $salesrooms_list;
	}

	/**
	 * 通过核销员id获取今日核销记录
	 * @param $smember_id
	 */
	public function get_today_hexiao_record_by_smember_id($smember_id){
		$now_time = strtotime( date('Y-m-d').' 00:00:00' );
		$end_time = $now_time + 86400;
		$saleshexiao_record_list = M('eaterplanet_ecommerce_order_goods_saleshexiao_record')->where(array('salesmember_id'=>$smember_id))->where(array('addtime' => array('between', array($now_time,$end_time))))->select();
		if(!empty($saleshexiao_record_list)){
			foreach($saleshexiao_record_list as $k=>$v){
				//核销时间
				$saleshexiao_record_list[$k]['hx_time'] = date('Y-m-d H:i:s',$v['addtime']);
				$order_id = $v['order_id'];
				$order_goods_id = $v['order_goods_id'];
				$order_info = M('eaterplanet_ecommerce_order')->where(array('order_id'=>$order_id))->field('order_num_alias,shipping_tel')->find();
				//订单编号
				$saleshexiao_record_list[$k]['order_num_alias'] = $order_info['order_num_alias'];
				//用户手机号
				$saleshexiao_record_list[$k]['shipping_tel'] = substr_replace($order_info['shipping_tel'],'****',3,4);
				//商品信息
				$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where(array('order_goods_id'=>$order_goods_id))->field('goods_id,name as goods_name,goods_images,total,quantity')->find();
				$saleshexiao_record_list[$k]['goods_id'] = $order_goods_info['goods_id'];
				$saleshexiao_record_list[$k]['goods_name'] =htmlspecialchars_decode(stripslashes($order_goods_info['goods_name']));
				$saleshexiao_record_list[$k]['goods_images'] = tomedia($order_goods_info['goods_images']);
				$saleshexiao_record_list[$k]['hexiao_count2'] = $v['hexiao_count'];

				$saleshexiao_record_list[$k]['quantity'] = $order_goods_info['quantity'];
				$saleshexiao_record_list[$k]['total'] = sprintf("%.2f", $order_goods_info['total']);
				$saleshexiao_record_list[$k]['option_sku'] = D('Seller/Order')->get_order_option_sku($order_id, $order_goods_id);

				$order_goods_saleshexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_goods_id'=>$order_goods_id))->find();
				$saleshexiao_record_list[$k]['hexiao_type'] =$order_goods_saleshexiao_info['hexiao_type'];				
			}
		}
		return $saleshexiao_record_list;
	}

	/**
	 * 通过核销码获取订单信息
	 *
	 * return $order_result
	 * 			is_exist 1、存在，0、不存在，2无核销权限
	 */
	public function get_hexiao_order_by_code($hexiao_volume_code,$salesmember_id){
		$order_result = array();
		$field = "order_id,order_num_alias,member_id,ziti_name,ziti_mobile,shipping_name,shipping_tel,payment_code";
		//shipping_tel  收货号码
		$order_info = M('eaterplanet_ecommerce_order')->where(array('hexiao_volume_code'=>$hexiao_volume_code))->field($field)->find();
		$order_goods_saleshexiao_list = array();
		$order_result['is_exist'] = 1;
		if(empty($order_info)){
			
			$order_goods_saleshexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('hexiao_volume_code'=>$hexiao_volume_code))->find();
			if(!empty($order_goods_saleshexiao_info)){
				$state = 1;
				//扫码
				$order_info = M('eaterplanet_ecommerce_order')->where(array('order_id'=>$order_goods_saleshexiao_info['order_id']))->field($field)->find();
				$order_goods_saleshexiao_list[0] = $order_goods_saleshexiao_info;
			}else{
				$state = 3;
				//手机号  1 4 5  6 7  11 12 14
				$order_list = M('eaterplanet_ecommerce_order')->where(array('shipping_tel'=>$hexiao_volume_code , 'delivery' =>'hexiao'))->where(' order_status_id = 4 || order_status_id = 11 ')->field('order_id')->select();
				if(empty($order_list)){
					if(strlen($hexiao_volume_code) == 11){
						$order_result['is_exist'] = 4;
						return $order_result;
					}else{
						$order_result['is_exist'] = 0;
						return $order_result;
					}
				}
				$order_list2 = array_column($order_list, 'order_id');
				$order_id = implode(",",$order_list2);
				$order_goods_saleshexiao_list = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(' order_id in('.$order_id.') ')->order('is_hexiao_over asc,addtime asc')->select();
				
				if(empty($order_goods_saleshexiao_list)){
					if(strlen($hexiao_volume_code) == 11){
						$order_result['is_exist'] = 4;
						return $order_result;
					}else{
						$order_result['is_exist'] = 0;
						return $order_result;
					}
				}
				$order_info = M('eaterplanet_ecommerce_order')->where(array('order_id'=>$order_list[0]['order_id']))->field($field)->find();

			}
		}else{
			//核销码
			$state = 2;
			$order_goods_saleshexiao_list = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_info['order_id']))->select();	
		}
		$hexiao_goods_list = array();
		$hx_i = 0;
		if(!empty($order_info)){
			$member_info = M('eaterplanet_ecommerce_member')->where(array('member_id'=>$order_info['member_id']))->find();
			//用户昵称
			$order_info['username'] = $member_info['username'];
			foreach($order_goods_saleshexiao_list as $k=>$v){
				if($this->check_goods_relative_smember($v['order_id'],$v['order_goods_id'],$v['goods_id'],$salesmember_id) == 1){
					$order_num_alias = M('eaterplanet_ecommerce_order')->where(array('order_id'=>$v['order_id']))->field('order_num_alias,order_status_id')->find();
					$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where(array('order_goods_id'=>$v['order_goods_id']))->field('goods_id,name as goods_name,goods_images,quantity')->find();
					$order_goods_saleshexiao_list[$k]['goods_id'] = $order_goods_info['goods_id'];
					$order_goods_saleshexiao_list[$k]['goods_name'] = htmlspecialchars_decode(stripslashes($order_goods_info['goods_name']));
					$order_goods_saleshexiao_list[$k]['goods_images'] = tomedia($order_goods_info['goods_images']);
					$order_goods_saleshexiao_list[$k]['quantity'] = $order_goods_info['quantity'];
					
					$order_goods_saleshexiao_list[$k]['order_num_alias'] = $order_num_alias['order_num_alias'];

					$order_goods_saleshexiao_list[$k]['option_sku'] = D('Seller/Order')->get_order_option_sku($v['order_id'], $v['order_goods_id']);

					if($v['is_hexiao_over'] == 0 ){
						if($order_num_alias['order_status_id'] == 11){
							$order_goods_saleshexiao_list[$k]['is_hexiao_over'] = 1;
						}else{
							$order_goods_saleshexiao_list[$k]['is_hexiao_over'] = 0;
						}
					}else{
						$order_goods_saleshexiao_list[$k]['is_hexiao_over'] = 1;
					}
					

					//核销日期
					$order_goods_saleshexiao_list[$k]['effect_begin_time'] = date('Y-m-d',$v['effect_begin_time']);
					$order_goods_saleshexiao_list[$k]['effect_end_time'] = date('Y-m-d',$v['effect_end_time']);
					//已核销次数
					$order_goods_saleshexiao_list[$k]['has_hexiao_count'] = $v['hexiao_count']-$v['remain_hexiao_count'];
					if($v['hexiao_type'] == 1){
						if($v['hexiao_count'] == 0){
							$order_goods_saleshexiao_list[$k]['hexiao_count'] = '无限';
							$order_goods_saleshexiao_list[$k]['remain_hexiao_count'] = '无限';
							$has_hexiao_count = M('eaterplanet_ecommerce_order_goods_saleshexiao_record')->where(array('order_id'=>$v['order_id'],'order_goods_id'=>$v['order_goods_id']))->sum('hexiao_count');
							if(empty($has_hexiao_count)){
								$has_hexiao_count = 0;
							}
							$order_goods_saleshexiao_list[$k]['has_hexiao_count'] = $has_hexiao_count;
						}
					}
					$order_goods_saleshexiao_list[$k]['is_refund'] = 0;//未退款
					if( $order_goods_saleshexiao_list[$k]['refund_quantity'] > 0 ){
						if($order_goods_saleshexiao_list[$k]['refund_quantity'] == $order_goods_saleshexiao_list[$k]['quantity']){
							$order_goods_saleshexiao_list[$k]['is_refund'] = 1;//全部退款
						}else if($order_goods_saleshexiao_list[$k]['refund_quantity'] < $order_goods_saleshexiao_list[$k]['quantity']){
							$order_goods_saleshexiao_list[$k]['is_refund'] = 2;//部分退款
						}
					}

					$hexiao_goods_list[$hx_i] = $order_goods_saleshexiao_list[$k];
					$hx_i++;
				}
			}
		}
		
		//提取列数组；
		foreach($hexiao_goods_list as $val){
		$key_arrays[]=$val['is_hexiao_over'];
		}
		array_multisort($key_arrays,SORT_ASC,SORT_NUMERIC,$hexiao_goods_list);
		//货到付款收款码
		if($order_info['payment_code'] == 'cashon_delivery'){
			$order_info['cashondelivery_code_img'] = D('Home/Front')->getCashonDeliveryCode();
		}
		//核销订单用户信息
		$order_result['orders'] = $order_info;
		//核销订单商品列表
		$order_result['order_goods_saleshexiao_list'] = $hexiao_goods_list;
		//订单商品数量
		$order_result['order_goods_count'] = count($hexiao_goods_list);
		if(count($hexiao_goods_list) == 0){
			if($state == 3){
				$order_result['is_exist'] = 4;//无权限操作核销商品
			}else{
				$order_result['is_exist'] = 2;//无权限操作核销商品
			}
			
		}
		return $order_result;
	}

	/**
	 * 判断核销员是否能核销商品
	 * @param $order_id 订单id
	 * @param $order_goods_id 商品订单id
	 * @param $goods_id 商品id
	 * @param $smember_id	核销员id
	 * @return int 1、能核销，0、不能核销
	 */
	public function check_goods_relative_smember($order_id,$order_goods_id,$goods_id,$smember_id){
		$flag = 0;
		$goods_salesroombase = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_id,'order_goods_id'=>$order_goods_id,'goods_id'=>$goods_id))->find();
		$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where(array('order_id'=>$order_id,'order_goods_id'=>$order_goods_id))->find();
		if($goods_salesroombase['hx_assign_salesroom'] == 1){
			$salesroom_list = M('eaterplanet_ecommerce_order_goods_relative_salesroom')->where(array('order_id'=>$order_id,'order_goods_id'=>$order_goods_id,'goods_id'=>$goods_id))->select();
			foreach($salesroom_list as $k=>$v){
				$salesroom_id = $v['salesroom_id'];
				$is_hx_member = $v['is_hx_member'];//是否指定核销员，0、不指定，1、指定
				if($is_hx_member == 0){//0、不指定
					$salesroom_relative_member = M('eaterplanet_ecommerce_salesroom_relative_member')->where(array('salesroom_id'=>$salesroom_id,'smember_id'=>$smember_id))->find();
					if(!empty($salesroom_relative_member)){
						$flag = 1;
						break;
					}
				}else{//1、指定
					$goods_smember_id = $v['smember_id'];
					$smemberid_array = explode(',',$goods_smember_id);
					foreach($smemberid_array as $sk=>$sv){
						if($sv == $smember_id){
							$flag = 1;
							break;
						}
					}
				}
			}
		}else{
			$sql = " SELECT s.supply_id FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_salesroom_relative_member srm "
				 . " LEFT JOIN ".C('DB_PREFIX')."eaterplanet_ecommerce_salesroom s ON srm.salesroom_id = s.id "
				 . " WHERE smember_id = ".$smember_id." AND s.supply_id = ".$order_goods_info['supply_id'];
			$list = M()->query($sql);
			if(!empty($list) && count($list) > 0){
				$flag = 1;
			}
		}
		return $flag;
	}

	/**
	 * 核销订单
	 * @param $order_id		订单号
	 * @param $salesmember_id	核销员
	 * @param $salesroom_id	门店
	 * @return array
	 */
	public function hexiao_all_orders($order_id,$salesmember_id,$salesroom_id){
		$result = array();
		$hexiao_goods_list = array();
		$hx_goods_count = 0;
		$hx_i = 0;
		//订单信息
		$field = "order_id,order_num_alias,member_id,ziti_name,ziti_mobile,shipping_name,shipping_tel";
		$order_info = M('eaterplanet_ecommerce_order')->where(array('order_id'=>$order_id))->field($field)->find();
		//订单要核销商品(未核销完，剩余核销数量大于0)
		$order_goods_saleshexiao_list = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_info['order_id'],'is_hexiao_over'=>0))->select();
		foreach($order_goods_saleshexiao_list as $k=>$v){
			if($this->check_goods_relative_smember($v['order_id'],$v['order_goods_id'],$v['goods_id'],$salesmember_id) == 1){
				$hexiao_goods_list[$hx_i] = $v;
				$hx_i++;
			}
		}
		if(count($hexiao_goods_list) > 0){
			//门店信息
			$salesroom_info = M('eaterplanet_ecommerce_salesroom')->where(array('id'=>$salesroom_id))->find();
			//核销员信息
			$salesmember_info = M('eaterplanet_ecommerce_salesroom_member')->where(array('id'=>$salesmember_id))->find();
			$result['hx_goods_count'] = count($hexiao_goods_list);
			foreach($hexiao_goods_list as $k=>$v){
				//核销商品
				$hx_result = $this->hexiao_order_goods($v,$salesmember_info,$salesroom_info,0);
				if($hx_result == 1){
					$hx_goods_count++;
				}
			}
			$this->hexiao_finished($order_id);
		}
		$result['hx_goods_count'] = $hx_goods_count;
		return $result;
	}

	/**
	 * 全部核销商品
	 * @param $order_goods_saleshexiao	订单商品核销信息表
	 * @param $salesmember_info	核销员信息
	 * @param $salesroom_info	门店信息
	 * @param $hx_time	0、按订单核销，大于0、按次数核销
	 * @return 1、核销商品成功，0、核销商品失败,-1 核销次数大于剩余次数
	 */
	public function hexiao_order_goods($order_goods_saleshexiao,$salesmember_info,$salesroom_info,$hx_time){
		//订单核销信息表id
		$hx_id = $order_goods_saleshexiao['id'];
		//剩余核销数量
		$remain_hexiao_count = $order_goods_saleshexiao['remain_hexiao_count'];
		if($order_goods_saleshexiao['hexiao_count'] > 0) {
			$hexiao_count = 0;
			if ($hx_time > 0) {
				if ($hx_time > $remain_hexiao_count) {
					return -1;
				} else {
					$hexiao_count = $hx_time;
				}
			} else {
				$hexiao_count = $remain_hexiao_count;
			}

			$hexiao_data = array();
			if ($hexiao_count == $remain_hexiao_count) {
				$hexiao_data['remain_hexiao_count'] = 0;
				$hexiao_data['is_hexiao_over'] = 1;
			} else {
				$hexiao_data['remain_hexiao_count'] = $remain_hexiao_count - $hexiao_count;
			}
			$hx_result = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('id' => $hx_id))->save($hexiao_data);
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

			$hexiao_record['salesroom_id'] = $salesroom_info['id'];
			$hexiao_record['salesroom_name'] = $salesroom_info['room_name'];
			$hexiao_record['salesmember_id'] = $salesmember_info['id'];
			$hexiao_record['smember_name'] = $salesmember_info['username'];
			$hexiao_record['member_id'] = $salesmember_info['member_id'];
			$hexiao_record['addtime'] = time();
			M('eaterplanet_ecommerce_order_goods_saleshexiao_record')->add($hexiao_record);
			return 1;
		}else{
			return 0;
		}
	}

	/**
	 * 核销商品（按订单核销的商品）
	 * @param $saleshexiao_info
	 * @param $salesmember_id
	 * @param $salesroom_id
	 * @param $hx_time	0、按订单核销；大于0、按次核销次数
	 * @return status -1、无限期核销，0、核销失败，1、核销成功
	 */
	public function saleshexiao_order_goods($saleshexiao_info,$salesmember_id,$salesroom_id,$hx_time){
		$status = 0;
		if($this->check_goods_relative_smember($saleshexiao_info['order_id'],$saleshexiao_info['order_goods_id'],$saleshexiao_info['goods_id'],$salesmember_id) == 1){
			//门店信息
			$salesroom_info = M('eaterplanet_ecommerce_salesroom')->where(array('id'=>$salesroom_id))->find();
			//核销员信息
			$salesmember_info = M('eaterplanet_ecommerce_salesroom_member')->where(array('id'=>$salesmember_id))->find();
			//核销商品
			$hx_result = $this->hexiao_order_goods($saleshexiao_info,$salesmember_info,$salesroom_info,$hx_time);
			if($hx_result == 1){
				$this->hexiao_finished($saleshexiao_info['order_id']);
				$status = 1;
			}
		}else{
			$status = -1;
		}
		return $status;
	}

	/**
	 * 获取按次核销商品信息
	 * @param $saleshexiao_info
	 * @param $salesmember_id
	 * @return status -1、无限期核销，1、有核销数据
	 */
	public function get_hxgoods_bytimes($saleshexiao_info,$salesmember_id){
		$result = array();
		$status = 1;
		if($this->check_goods_relative_smember($saleshexiao_info['order_id'],$saleshexiao_info['order_goods_id'],$saleshexiao_info['goods_id'],$salesmember_id) == 1){
			//商品信息
			$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where(array('order_goods_id'=>$saleshexiao_info['order_goods_id']))->field('goods_id,name as goods_name,goods_images,quantity')->find();
			$order_goods_info['goods_images'] = tomedia($order_goods_info['goods_images']);
			$result['order_goods_info'] = $order_goods_info;

			$hexiao_record_list = M('eaterplanet_ecommerce_order_goods_saleshexiao_record')->where(array('order_id'=>$saleshexiao_info['order_id'],'order_goods_id'=>$saleshexiao_info['order_goods_id']))->select();
			if(!empty($hexiao_record_list)){
				foreach($hexiao_record_list as $k=>$v){
					$hexiao_record_list[$k]['goods_name'] = $order_goods_info['goods_name'];
					$hexiao_record_list[$k]['hx_time'] = date('Y-m-d H:i',$v['addtime']);
				}
			}
			$result['hexiao_record_list'] = $hexiao_record_list;

			//核销日期
			$saleshexiao_info['effect_begin_time'] = date('Y-m-d H:i',$saleshexiao_info['effect_begin_time']);
			$saleshexiao_info['effect_end_time'] = date('Y-m-d H:i',$saleshexiao_info['effect_end_time']);
			//已核销次数
			$saleshexiao_info['has_hexiao_count'] = $saleshexiao_info['hexiao_count']-$saleshexiao_info['remain_hexiao_count'];

			if($saleshexiao_info['hexiao_type'] == 1){
				if($saleshexiao_info['hexiao_count'] == 0){
					$saleshexiao_info['hexiao_count'] = '无限';
					$saleshexiao_info['remain_hexiao_count'] = '无限';
				}
			}
			$result['saleshexiao_info'] = $saleshexiao_info;
		}else{
			$status = -1;
		}
		$result['status'] = $status;
		return $result;
	}

	/**
	 * 核销完成
	 * @param $order_id
	 */
	public function hexiao_finished($order_id){
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
			$order_history['comment'] = '核销员核销，订单完成。';
			$order_history['date_added']=time();
			M('eaterplanet_ecommerce_order_history')->add($order_history);
			//ims_
			M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 11,'finishtime' => time()) );

			$time = time();

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
	 * 获取核销商品可退款数量
	 * @param $order_id	订单号
	 * @param $order_goods_id	商品订单号
	 * @return 可退款数量
	 */
	public function get_hexiao_order_goods_can_refund_quantity($order_id,$order_goods_id){
		$sales_hexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_id,'order_goods_id'=>$order_goods_id))->find();
		if($sales_hexiao_info['hexiao_type'] == 1){
			$can_refund_quantity = floor($sales_hexiao_info['remain_hexiao_count']/$sales_hexiao_info['one_hexiao_count']);
		}else{
			$can_refund_quantity = $sales_hexiao_info['goods_quantity']-$sales_hexiao_info['refund_quantity'];
		}
		return $can_refund_quantity;
	}

	/**
	 * 获取核销商品已使用数量
	 * @param $order_id	订单号
	 * @param $order_goods_id	商品订单号
	 * @return 已使用数量
	 */
	public function get_hexiao_order_goods_used_quantity($order_id,$order_goods_id){
	    $used_quantity  = 0;
	    $sales_hexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_id,'order_goods_id'=>$order_goods_id))->find();
	    if($sales_hexiao_info['hexiao_type'] == 1){
	        $used_quantity = ($sales_hexiao_info['goods_quantity'] - $sales_hexiao_info['refund_quantity']) - floor($sales_hexiao_info['remain_hexiao_count']/$sales_hexiao_info['one_hexiao_count']);
	    }else{
	        if($sales_hexiao_info['remain_hexiao_count'] == 0){
	            $used_quantity = $sales_hexiao_info['goods_quantity'] - $sales_hexiao_info['refund_quantity'];
	        }else{
	            $used_quantity = 0;
	        }
	    }
	    return $used_quantity;
	}


	/**
	 * 获取核销商品已使用金额
	 * @param $order_id	订单号
	 * @param $order_goods_id	商品订单号
	 * @param $refund_quantity	退款数量
	 * @return 已使用商品金额
	 */
	public function get_hexiao_order_goods_used_total($order_id,$order_goods_id){
		$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id ) )->find();
		$order_goods_total = $order_goods_info['total']-$order_goods_info['voucher_credit']-$order_goods_info['fullreduction_money'] - $order_goods_info['score_for_money'];
		//已使用数量
		$used_quantity = $this->get_hexiao_order_goods_used_quantity($order_id,$order_goods_id);
		$used_total = round($order_goods_total/$order_goods_info['quantity']*$used_quantity,2);
		return $used_total;
	}
	/**
	 * 获取核销订单已使用数量
	 * @param unknown $order_id
	 */
	public function get_hexiao_order_used_quantity($order_id){
	    $used_quantity = 0;
	    $order_goods_list = M('eaterplanet_ecommerce_order_goods')->where(array('order_id'=>$order_id))->select();
	    foreach ($order_goods_list as $k=>$v){
	        $used_quantity = $used_quantity + $this->get_hexiao_order_goods_used_quantity($order_id,$v['order_goods_id']);
	    }
	    return $used_quantity;
	}

	/**
	 * 获取核销订单已使用金额
	 * @param unknown $order_id
	 */
	public function get_hexiao_order_used_total($order_id){
	    $used_total = 0;
	    $order_goods_list = M('eaterplanet_ecommerce_order_goods')->where(array('order_id'=>$order_id))->select();
	    foreach ($order_goods_list as $k=>$v){
	        $used_total = round($used_total + $this->get_hexiao_order_goods_used_total($order_id,$v['order_goods_id']),2);
	    }
	    return $used_total;
	}

	/**
	 * 核销商品订单退款
	 * @param unknown $order_id
	 * @param unknown $order_goods_id
	 * @param unknown $refund_quantity
	 */
	public function hexiao_refund_action($order_id,$order_goods_id,$refund_quantity){
	   $sales_hexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_id,'order_goods_id'=>$order_goods_id))->find();
	   $data = array();
	   //退款数量
	   $data['refund_quantity'] = $sales_hexiao_info['refund_quantity']+$refund_quantity;
	   if($sales_hexiao_info['hexiao_type'] == 0){
	       if($sales_hexiao_info['goods_quantity'] == $data['refund_quantity']){
	           $data['remain_hexiao_count'] = 0;
			   $data['is_hexiao_over'] = 1;
	       }
	   }else{
	       //剩余核销数量
	       $data['remain_hexiao_count'] = $sales_hexiao_info['remain_hexiao_count']-$refund_quantity*$sales_hexiao_info['one_hexiao_count'];
		   if($sales_hexiao_info['goods_quantity'] == $data['refund_quantity']){
			   $data['is_hexiao_over'] = 1;
		   }
	   }
	   M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('id'=>$sales_hexiao_info['id']))->save($data);
	}
	/**
	 * 核销订单全部退款
	 * @param unknown $order_id
	 * @param unknown $order_goods_id
	 * @param unknown $refund_quantity
	 */
	public function hexiao_order_refund_action($order_id){
	    $order_goods_list = M('eaterplanet_ecommerce_order_goods')->where(array('order_id'=>$order_id))->select();
	    foreach ($order_goods_list as $k=>$v){
	        $sales_hexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_id,'order_goods_id'=>$v['order_goods_id']))->find();
	        $used_quantity = $this->get_hexiao_order_goods_used_quantity($order_id,$v['order_goods_id']);
	        $refund_quantity = $sales_hexiao_info['goods_quantity'] - $sales_hexiao_info['refund_quantity'] - $used_quantity;
	        if($refund_quantity > 0){
	            $this->hexiao_refund_action($order_id,$v['order_goods_id'],$refund_quantity);
	        }
	    }
	}

	/**
	 * 	定时执行
	 *  核销订单过期处理
	 */
	public function hexiao_expire(){
		$now_time = time();
		$condition = " is_hexiao_over = 0 and effect_end_time < ".$now_time;
		$order_goods_hx_list = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where($condition)->select();
		foreach($order_goods_hx_list as $k=>$v){
			$order_info = M('eaterplanet_ecommerce_order')->where(array('order_id'=>$v['order_id']))->field('order_status_id')->find();
			if($order_info['order_status_id'] == 5){
				$data = array();
				$data['is_hexiao_over'] = 3;//已取消
				$data['expire_act_time'] = time();//取消时间
				M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('id'=>$v['id']))->save($data);
			}else{
				$data = array();
				$data['is_hexiao_over'] = 2;//已过期
				$data['expire_act_time'] = time();//过期时间
				M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('id'=>$v['id']))->save($data);

				//添加订单核销记录表
				$hexiao_record = array();
				$hexiao_record['order_id'] = $v['order_id'];
				$hexiao_record['order_goods_id'] = $v['order_goods_id'];
				$hexiao_record['goods_id'] = $v['goods_id'];
				$hexiao_record['hexiao_count'] = $v['remain_hexiao_count'];
				$hexiao_record['smember_name'] = "商品过期自动使用";
				$hexiao_record['is_admin'] = 1;
				$hexiao_record['addtime'] = time();
				M('eaterplanet_ecommerce_order_goods_saleshexiao_record')->add($hexiao_record);

				//过期核销订单完成
				$this->hexiao_auto_expire_finished($v['order_id']);
			}
		}
	}

	/**
	 * 过期核销订单完成
	 * @param $order_id
	 */
	public function hexiao_auto_expire_finished($order_id){
		$is_finished = true;
		$hexiao_list = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(array('order_id'=>$order_id))->select();
		foreach($hexiao_list as $k=>$v){
			if($v['is_hexiao_over'] == 0){
				$is_finished = false;
			}
		}
		if($is_finished){
			$order_history = array();
			$order_history['order_id'] = $order_id;
			$order_history['order_status_id'] = 11;
			$order_history['notify'] = 0;
			$order_history['comment'] = '商品过期自动使用，订单完成。';
			$order_history['date_added']=time();
			M('eaterplanet_ecommerce_order_history')->add($order_history);
			//ims_
			M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 11,'finishtime' => time()) );
		}
	}

	/**
	 * 核销商品距离过期时间一个小时自动下架
	 */
	public function hexiao_goods_expire(){
		$now_time = time();
		$sql = "SELECT g.id, g.grounding, g.goodsname, gs.hx_expire_end_time, gs.hx_auto_off_time, gs.hx_auto_off, gs.hx_expire_type "
			 . " FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_goods_salesroombase gs,".C('DB_PREFIX')."eaterplanet_ecommerce_goods g "
			 . " WHERE g.grounding = 1 AND gs.hx_expire_type = 1 and gs.goods_id = g.id";
		$order_goods_hx_list = M()->query($sql);
		foreach($order_goods_hx_list as $k=>$v){
			$goods_id = $v['id'];
			$is_grounding = false;
			//商品过期时间
			$hx_expire_end_time = $v['hx_expire_end_time'];

			$hx_auto_off_time = $v['hx_auto_off_time'];
			if($v['hx_auto_off'] == 1){
				$hx_auto_time = $hx_auto_off_time*3600 + $now_time;
				if($hx_expire_end_time < $hx_auto_time){
					$is_grounding = true;
				}
			}else{
				if($hx_expire_end_time < $now_time){
					$is_grounding = true;
				}
			}
			if($is_grounding){
				$data = array();
				$data['grounding'] = 0;//下架
				M('eaterplanet_ecommerce_goods')->where(array('id'=>$goods_id))->save($data);
			}
		}
	}
}
