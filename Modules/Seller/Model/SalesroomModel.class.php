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

class SalesroomModel{


	public function update($data)
	{
		$ins_data = array();
		$ins_data['supply_id'] = $data['supply_id'];
		$ins_data['room_name'] = $data['room_name'];

		$ins_data['room_logo'] = $data['room_logo'];
		$ins_data['province_id'] = $data['province_id'];
		$ins_data['city_id'] = $data['city_id'];
		$ins_data['country_id'] = $data['country_id'];
		$ins_data['address'] = $data['address'];
		$ins_data['lon'] = $data['lon'];
		$ins_data['lat'] = $data['lat'];
		$ins_data['mobile'] = $data['mobile'];
		$ins_data['business_hours_begin'] = $data['business_hours_begin'];
		$ins_data['business_hours_end'] = $data['business_hours_end'];
		$ins_data['contacts'] = $data['contacts'];
		$ins_data['introduction'] = $data['introduction'];
		$ins_data['displayorder'] = $data['displayorder'];
		$ins_data['state'] = $data['state'];

		$room_address = $data['province_id'].$data['city_id'].$data['country_id'].$data['address'];
		$ins_data['room_address'] = $room_address;

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['id']);
			M('eaterplanet_ecommerce_salesroom')->where( array('id' => $id) )->save( $ins_data );
			if($data['state'] == 0){
				D('Seller/Salesroom')->update_hxgoods($id);
			}
		}else{
			$ins_data['addtime'] = time();
			$id = M('eaterplanet_ecommerce_salesroom')->add( $ins_data );
		}
	}

	public function querySalesRoom(){
	    $list = M('eaterplanet_ecommerce_salesroom')->field('id,room_name,supply_id')->where( array('state' => 1 ) )->order('displayorder desc')->select();
	    foreach ($list as $k=>$v) {
	        if(empty($v['supply_id'])){
	            $list[$k]['supply_name'] = "平台";
	        }else{
	            $supply_info = M('eaterplanet_ecommerce_supply')->where( array('id' => $v['supply_id']) )->field('shopname')->find();
	            $list[$k]['supply_name'] = $supply_info['shopname'];
	        }
	    }
	    return $list;
	}

	/**
	 * 删除门店后商品恢复成普通商品(如果指定门店且只有一个门店的时候)
	 * @param $salesroom_id
	 */
	public function update_hxgoods($salesroom_id){
		$salesroom_info = M('eaterplanet_ecommerce_salesroom')->where( array('id' => $salesroom_id) )->find();
		$supply_id = $salesroom_info['supply_id'];
		$salesroom_count = M('eaterplanet_ecommerce_salesroom')->where( array('supply_id' => $supply_id,'state'=>1) )->count();
		if($salesroom_count > 0){
			$this->update_salesroom_goods($salesroom_id);
		}else{
			$goods_list = M('eaterplanet_ecommerce_good_common')->where( array('supply_id' => $supply_id,'is_only_hexiao'=>1) )->select();
			foreach($goods_list as $k=>$v){
				$goods_id = $v['goods_id'];
				$goods_salesroom_list = M('eaterplanet_ecommerce_goods_relative_salesroom')->where( array('goods_id' => $goods_id) )->select();
				//删除商品关联门店信息
				M('eaterplanet_ecommerce_goods_relative_salesroom')->where( array('goods_id'=>$goods_id) )->delete();
				foreach($goods_salesroom_list as $gk=>$gv){
					//删除商品关联门店核销员信息
					M('eaterplanet_ecommerce_goods_relative_smember')->where( array('gr_id'=>$gv['id']) )->delete();
				}
				//删除商品核销信息表
				M('eaterplanet_ecommerce_goods_salesroombase')->where( array('goods_id' => $goods_id) )->delete();
				//更新商品为无核销状态
				M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $goods_id) )->save(array('is_only_hexiao'=>0));
			}
		}
	}

	public function update_salesroom_goods($salesroom_id){
		$goods_salesroom_list = M('eaterplanet_ecommerce_goods_relative_salesroom')->where( array('salesroom_id' => $salesroom_id) )->select();
		foreach($goods_salesroom_list as $k=>$v){
			$goods_id = $v['goods_id'];
			//删除商品关联门店信息
			M('eaterplanet_ecommerce_goods_relative_salesroom')->where( array('salesroom_id' => $salesroom_id,'goods_id'=>$goods_id) )->delete();
			//删除商品关联门店核销员信息
			M('eaterplanet_ecommerce_goods_relative_smember')->where( array('salesroom_id' => $salesroom_id,'gr_id'=>$v['id']) )->delete();

			$goods_salesroombase = M('eaterplanet_ecommerce_goods_salesroombase')->where( array('goods_id' => $goods_id) )->find();
			if(!empty($goods_salesroombase)){
				//如果商品是指定核销员
				if($goods_salesroombase['hx_assign_salesroom'] == 1){
					$count = M('eaterplanet_ecommerce_goods_relative_salesroom')->where( array('goods_id'=>$goods_id) )->count();
					//商品无关联门店信息
					if($count == 0){
						//删除商品核销信息表
						M('eaterplanet_ecommerce_goods_salesroombase')->where( array('goods_id' => $goods_id) )->delete();
						//更新商品为无核销状态
						M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $goods_id) )->save(array('is_only_hexiao'=>0));
					}
				}
			}
		}
	}
}
?>
