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

class DeliveryModel{

	public function adddelivery_clerk($data, $uniacid = 0)
	{
		$ins_data = array();

		$ins_data['name'] = $data['name'];
		$ins_data['logo'] = $data['logo'];
		$ins_data['mobile'] = $data['mobile'];
		$ins_data['addtime'] = time();

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);
			M('eaterplanet_ecommerce_deliveryclerk')->where( array('id' => $id) )->save( $ins_data );
			$id = $data['id'];
		}else{
			$ins_data['line_id'] = 0;

			$id = M('eaterplanet_ecommerce_deliveryclerk')->add($ins_data);
		}

	}


	public function adddeliverylist($data, $uniacid = 0)
	{




		$head_id_arr = $data['head_id'];

		$ins_data = array();
		$ins_data['name'] = $data['name'];
		$ins_data['clerk_id'] = $data['clerk_id'];
		$ins_data['addtime'] = time();

		$id = $data['id'];

		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);

			M('eaterplanet_ecommerce_deliveryline')->where( array('id' => $id) )->save($ins_data);

			$id = $data['id'];
		}else{

			$id = M('eaterplanet_ecommerce_deliveryline')->add( $ins_data );
		}

		M('eaterplanet_ecommerce_deliveryclerk')->where( array('line_id' => $id ) )->save( array('line_id' => 0 ) );
		//修改配送员的线路

		M('eaterplanet_ecommerce_deliveryclerk')->where( array('id' => $data['clerk_id'] ) )->save( array('line_id' => $id) );

		//修改配送员的线路todo....
		M('eaterplanet_ecommerce_deliveryline_headrelative')->where( array('line_id' => $id) )->delete();

		$rel_data = array();
		$rel_data['line_id'] = $id;
		$rel_data['uniacid'] = $uniacid;
		$rel_data['addtime'] = time();
		if(!empty($head_id_arr))
		{
			if(is_array($head_id_arr) )
			{
				foreach($head_id_arr as $vv)
				{
					if(!is_numeric($vv))
					{
						continue;
					}
					$rel_data['head_id'] = $vv;

					M('eaterplanet_ecommerce_deliveryline_headrelative')->add($rel_data);
				}
			}

		}

	}
	public function update($data, $uniacid = 0)
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

			$id = M('eaterplanet_ecommerce_article')->add( $ins_data );
		}


	}


}
?>
