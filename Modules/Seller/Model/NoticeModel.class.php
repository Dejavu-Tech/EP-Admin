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
