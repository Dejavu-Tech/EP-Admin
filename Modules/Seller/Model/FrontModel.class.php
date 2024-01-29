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
namespace Seller\Model;

class FrontModel{


	public function get_member_community_info($member_id)
	{
		$head_info = M('eaterplanet_community_head')->where( array('member_id' => $member_id ) )->find();

		return $head_info;
	}
	public function get_community_byid($community_id)
	{


		$data = array();
		$data['communityId'] = $community_id;


		$community_info = M('eaterplanet_community_head')->where( array('id' => $community_id) )->find();


		$data['communityName'] = $community_info['community_name'];

		$province = $this->get_area_info($community_info['province_id']);
		$city = $this->get_area_info($community_info['city_id']);
		$area = $this->get_area_info($community_info['area_id']);
		$country = $this->get_area_info($community_info['country_id']);
		//address
		$full_name = $province['name'].$city['name'].$area['name'].$country['name'].$community_info['address'];

		$data['fullAddress'] = $full_name;
		$data['communityAddress'] = '';


		$mb_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $community_info['member_id'] ) )->find();

		$data['headImg'] = $mb_info['avatar'];
		$data['disUserHeadImg'] = $mb_info['avatar'];
		$data['disUserName'] = $community_info['head_name'];
		$data['head_mobile'] = $community_info['head_mobile'];
		$data['province'] = $province['name'];
		$data['city'] = $city['name'];


		return $data;
	}

	/**
	 * 获取历史的社区
	 */
	public function get_history_community($member_id)
	{

		$history_community = M('eaterplanet_community_history')->where( "head_id>0 and member_id={$member_id}" )->order('id desc')->find();

		$data = array();


		if(!empty($history_community))
		{
			$data['communityId'] = $history_community['head_id'];

			$community_info = M('eaterplanet_community_head')->where(  array('id' => $history_community['head_id'] ) )->find();

			$data['communityName'] = $community_info['community_name'];

			$province = $this->get_area_info($community_info['province_id']);
			$city = $this->get_area_info($community_info['city_id']);
			$area = $this->get_area_info($community_info['area_id']);
			$country = $this->get_area_info($community_info['country_id']);
			//address
			$full_name = $province['name'].$city['name'].$area['name'].$country['name'].$community_info['address'];

			$data['fullAddress'] = $full_name;
			$data['communityAddress'] = '';

			$mb_info = M('eaterplanet_ecommerce_member')->field('avatar,username')->where( array('member_id' => $community_info['member_id'] ) )->find();

			$data['headImg'] = $mb_info['avatar'];
			$data['disUserHeadImg'] = $mb_info['avatar'];
			//$data['disUserName'] = $mb_info['username'];
			$data['disUserName'] = $community_info['head_name'];
		}
		return $data;
	}

	/**
	 * 切换历史社区
	 */
	public function update_history_community($member_id, $head_id){
		global $_W;
		global $_GPC;

		$uniacid = $_W['uniacid'];

		$history_community = M('eaterplanet_community_history')->where( array('member_id' => $member_id ) )->order('id desc')->find();

		if( empty($history_community) )
		{
			$ins_data = array();
			$ins_data['member_id'] = $member_id;
			$ins_data['head_id'] = $head_id;
			$ins_data['addtime'] = time();

			M('eaterplanet_community_history')->add($ins_data);

		}else{

			M('eaterplanet_community_history')->where( array('id' => $history_community['id']) )->save(array('head_id' => $head_id));
		}

		return "success";
	}

	/**
		根据经纬度获取位置信息
		get_gps_area_info($longitude,$latitude,$limit);
	**/
	public function get_gps_area_info($longitude,$latitude,$limit=10,$keyword='',$city_id=0)
	{
		global $_W;
		global $_GPC;

		$where = " and state =1 ";
		if( $city_id != 0 )
		{
			$where = " and city_id = ".$city_id;
		}
		if( !empty($keyword) )
		{
			$where = " and community_name like '%{$keyword}%' ";
		}

		$sql = "select *, ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$latitude}*PI()/180-lat*PI()/180)/2),2)+COS({$latitude}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$longitude}*PI()/180-lon*PI()/180)/2),2)))*1000) AS distance
		 FROM ".C('DB_PREFIX')."eaterplanet_community_head where member_id !=0  {$where} order by distance asc limit {$limit}";

		$list = M()->query($sql);

		$result = array();

		if( !empty($list) )
		{
			foreach($list as  $val)
			{
				$mb_info = M('eaterplanet_ecommerce_member')->field('avatar,username')->where( array('member_id' => $val['member_id']) )->find();


				if(empty($mb_info)) continue;

				$tmp_arr = array();
				$tmp_arr['communityId'] = $val['id'];
				$tmp_arr['communityName'] = $val['community_name'];
				$province = $this->get_area_info($val['province_id']);
				$city = $this->get_area_info($val['city_id']);
				$area = $this->get_area_info($val['area_id']);
				$country = $this->get_area_info($val['country_id']);
				//address
				$full_name = $province['name'].$city['name'].$area['name'].$country['name'].$val['address'];

				$tmp_arr['fullAddress'] = $full_name;
				$tmp_arr['communityAddress'] = '';
				$tmp_arr['disUserName'] = $val['head_name'];
				//ims_

				$tmp_arr['headImg'] = $mb_info['avatar'];
				$tmp_arr['disUserHeadImg'] = '';
				$distance = $val['distance'];

				if($distance >1000)
				{
					$distance = round($distance/1000,2).'公里';
				}else{
					$distance = $distance.'米';
				}
				$tmp_arr['distance'] = $distance;

				$result[] = $tmp_arr;
			}
		}
		return $result;

	}

	public function get_area_info($id)
	{

		$area_info = M('eaterplanet_ecommerce_area')->where( array('id' => $id ) )->find();

		return $area_info;
	}

	public function get_area_ninfo_by_name($name)
	{


		$area_info = M('eaterplanet_ecommerce_area')->where( array('name' => $name ) )->find();

		return $area_info;

		// eaterplanet_ecommerce_area
	}

	public function get_config_by_name($name)
	{

		$info = M('eaterplanet_ecommerce_config')->where( array('name' => $name) )->find();

		return $info['value'];
	}

	//$order_comment_count =  M('order_comment')->where( array('goods_id' => $id, 'state' => 1) )->count();


	public function get_goods_common_field($goods_id , $filed='*')
	{

		$info = M('eaterplanet_ecommerce_good_common')->field($filed)->where( array('goods_id' => $goods_id ) )->find();

		return $info;
	}

	/**
		检查商品限购数量
	**/
	public function check_goods_user_canbuy_count($member_id, $goods_id)
	{

		$goods_desc = $this->get_goods_common_field($goods_id , 'total_limit_count,one_limit_count');

		if($goods_desc['total_limit_count'] > 0 || $goods_desc['one_limit_count'] > 0)
		{
			$sql = "SELECT sum(og.quantity) as count  FROM " .C('DB_PREFIX'). "eaterplanet_ecommerce_order as o,
			" .C('DB_PREFIX'). "eaterplanet_ecommerce_order_goods as og where  o.order_id = og.order_id and  og.goods_id =" . (int)$goods_id ."
			 and o.member_id = {$member_id}  and  o.order_status_id in (1,2,3,4,6,7,9,11,12,13,14)";

			$total_arr = M()->query($sql);
			$buy_count = $total_arr[0]['count'];

			if(  $goods_desc['one_limit_count'] > 0 && $goods_desc['total_limit_count'] > 0)
			{
				if($buy_count >= $goods_desc['total_limit_count'])
				{
					return -1;
				}else{
					$total_max_count = $goods_desc['total_limit_count'] - $buy_count;
					$can_buy = $total_max_count < $goods_desc['one_limit_count'] ? $total_max_count : $goods_desc['one_limit_count'];
					return $can_buy;
				}

			}else if($goods_desc['one_limit_count'] > 0){
				return $goods_desc['one_limit_count'];
			}else if($goods_desc['total_limit_count'] > 0){
				if($buy_count >= $goods_desc['total_limit_count'])
				{
					return -1;
				} else {
					return ($goods_desc['total_limit_count'] - $buy_count);
				}
			}

		} else{
			return 0;
		}


	}

	/**
		获取规格图片
	**/
	public function get_goods_sku_item_image($option_item_ids)
	{
		global $_W;
		global $_GPC;

		$option_item_ids = explode('_', $option_item_ids);
		$ids_str = implode(',', $option_item_ids);

		$image_info = M('eaterplanet_ecommerce_goods_option_item')->field('thumb')->where("id in ({$ids_str}) and thumb != ''")->find();

		return $image_info;
	}

	/**
		获取商品规格图片
	**/
	public function get_goods_sku_image($eaterplanet_goods_option_item_value_id)
	{

		$info = M('eaterplanet_ecommerce_goods_option_item_value')->field('option_item_ids')->where( array('id' => $eaterplanet_goods_option_item_value_id) )->find();

		$option_item_ids = explode('_', $info['option_item_ids']);
		$ids_str = implode(',', $option_item_ids);

		$image_info = M('eaterplanet_ecommerce_goods_option_item')->field('thumb')->where("id in ({$ids_str}) and thumb != ''")->find();

		return $image_info;
	}



}
?>
