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

class ArticleModel{


	public function update($data)
	{


		$ins_data = array();
		$ins_data['title'] = $data['title'];
		$ins_data['content'] = $data['content'];
		$ins_data['displayorder'] = $data['displayorder'];
		$ins_data['enabled'] = $data['enabled'];
		$ins_data['addtime'] = time();

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);

			M('eaterplanet_ecommerce_article')->where( array('id' => $id) )->save( $ins_data );
			$id = $data['id'];

		}else{

			$id = M('eaterplanet_ecommerce_article')->where( array('id' => $id) )->add( $ins_data );


		}


	}


}
?>
