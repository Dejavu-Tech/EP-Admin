<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.io/
 * @copyright Copyright (c) 2019-2023 Dejavu Tech.
 * @license   https://e-p.io/license
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Seller\Model;

class TagsModel{


	public function update($data,$tag_type='normal')
	{

		$ins_data = array();
		$ins_data['tagname'] = $data['tagname'];
		$ins_data['type'] = $data['type'];
		$ins_data['tag_type'] = $tag_type;

		if($data['type']==0){
			$ins_data['tagcontent'] = $data['tagcontent'];
		} else {
			$ins_data['tagcontent'] = save_media($data['tagimg']);
		}
		$ins_data['state'] = $data['state'];
		$ins_data['sort_order'] = $data['sort_order'];

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			M('eaterplanet_ecommerce_goods_tags')->where( array('id' => $id) )->save( $ins_data );

		}else{
			M('eaterplanet_ecommerce_goods_tags')->add($ins_data);
		}



	}


}
?>
