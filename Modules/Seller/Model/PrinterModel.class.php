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

class PrinterModel{


	public function update($data)
	{
		$ins_data = array();
		$ins_data['printer_name'] = $data['printer_name'];
		$ins_data['printer_type'] = $data['printer_type'];
		$ins_data['api_id'] = $data['api_id'];
		$ins_data['api_key'] = $data['api_key'];
		if($data['printer_type'] == 1){
			$ins_data['printer_sn'] = $data['printer_sn'];
			$ins_data['printer_key'] = $data['printer_key'];
		}else if($data['printer_type'] == 2){
			$ins_data['printer_sn'] = $data['printer_yly_sn'];
			$ins_data['printer_key'] = $data['printer_yly_key'];
		}
		$ins_data['printer_num'] = $data['printer_num'];
		$ins_data['status'] = $data['status'];

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			M('eaterplanet_ecommerce_printer')->where( array('id' => $id) )->save( $ins_data );
		}else{
			$ins_data['addtime'] = time();
			M('eaterplanet_ecommerce_printer')->add($ins_data);
		}
	}


}
?>
