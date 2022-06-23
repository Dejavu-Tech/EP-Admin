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

class SalesroomMemberModel{


	public function update($data)
	{
		$ins_data = array();
		$ins_data['supply_id'] = $data['supply_id'];
		$ins_data['username'] = $data['username'];
		$ins_data['mobile'] = $data['mobile'];
		$ins_data['member_id'] = $data['member_id'];
		$ins_data['state'] = $data['state'];

		$id = $data['id'];
		if($data['state'] == 0){
			$ins_data['disable_time'] = time();
		}
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['id']);
			M('eaterplanet_ecommerce_salesroom_member')->where( array('id' => $id) )->save( $ins_data );
		}else{
			$ins_data['addtime'] = time();
			$id = M('eaterplanet_ecommerce_salesroom_member')->add( $ins_data );
		}
		if(!empty($data['salesroom_ids'])){
			M('eaterplanet_ecommerce_salesroom_relative_member')->where( array('smember_id' => $id) )->delete();
			$salesroom_ids = explode(',',$data['salesroom_ids']);
			$salesroom_relative_member = array();
			$salesroom_relative_member['smember_id'] = $id;
			$salesroom_relative_member['member_id'] = $data['member_id'];
			$salesroom_relative_member['addtime'] = time();
			foreach($salesroom_ids as $v){
				$salesroom_relative_member['salesroom_id'] = $v;
				M('eaterplanet_ecommerce_salesroom_relative_member')->add( $salesroom_relative_member );
			}
		}
	}


}
?>
