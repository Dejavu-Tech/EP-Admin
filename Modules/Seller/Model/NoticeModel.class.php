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

class NoticeModel{


	public function update($data)
	{


		$ins_data = array();
		$ins_data['content'] = $data['content'];
		$ins_data['displayorder'] = $data['displayorder'];
		$ins_data['enabled'] = $data['enabled'];
		$ins_data['addtime'] = time();

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);

			M('eaterplanet_ecommerce_notice')->where( array('id' => $id) )->save( $ins_data );

			$id = $data['id'];

		}else{
			$id = M('eaterplanet_ecommerce_notice')->add($ins_data);

		}
	}
}
?>
