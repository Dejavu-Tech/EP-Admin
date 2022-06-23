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

class ShippingModel{


	public function update($data)
	{

		$ins_data = array();
		$ins_data['name'] = $data['name'];
		$ins_data['type'] = $data['type'];
		$ins_data['sort_order'] = $data['sort_order'];
		$ins_data['firstprice'] = $data['default_firstprice'];
		$ins_data['secondprice'] = $data['default_secondprice'];
		$ins_data['firstweight'] = $data['default_firstweight'];
		$ins_data['secondweight'] = $data['default_secondweight'];
		$ins_data['areas'] = serialize($data['areas']);
		$ins_data['firstnum'] = $data['default_firstnum'];
		$ins_data['secondnum'] = $data['default_secondnum'];
		$ins_data['firstnumprice'] = $data['default_firstnumprice'];
		$ins_data['secondnumprice'] = $data['default_secondnumprice'];
		$ins_data['isdefault'] = $data['isdefault'];

		if ($data['isdefault']) {
			M('eaterplanet_ecommerce_shipping')->save( array('isdefault' => 0) );
		}

		if (!empty($data['id'])) {
			M('eaterplanet_ecommerce_shipping')->where( array('id' => $data['id'] ) )->save( $ins_data );
		}
		else {
			$id = M('eaterplanet_ecommerce_shipping')->add( $ins_data );
		}

	}


}
?>
