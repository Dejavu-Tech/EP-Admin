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
use Think\Model;
class SolitaireModel {

	public function updatedo($data, $uniacid = 0,$addtype=0, $appstate =1)
	{


		$id = $data['data']['id'];


		$ins_data = array();
		$ins_data['uniacid'] = 0;

		$ins_data['head_id'] = $data['head_dan_id'];
		$ins_data['solitaire_name'] = $data['data']['solitaire_name'];
		$ins_data['images_list'] = serialize( $data['images_list'] );
		$ins_data['addtype'] = $addtype;
		$ins_data['appstate'] = $appstate;
		$ins_data['state'] =  $data['data']['state'] ;
		$ins_data['begin_time'] = strtotime( $data['time']['start']);
		$ins_data['end_time'] = strtotime($data['time']['end']);
		$ins_data['content'] = htmlspecialchars( $data['data']['content'] );

		$ins_data['addtime'] = time();


		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);

			M('eaterplanet_ecommerce_solitaire')->where( array('id' => $id ))->save( $ins_data );

			//shagnp shuju
			$limit_goods_str =  $data['goods_list'];

			$limit_goods_list = explode(',', $limit_goods_str );

			if( !empty($limit_goods_list) )
			{

				M('eaterplanet_ecommerce_solitaire_goods')->where( 'id not  in (' . $limit_goods_str.') and soli_id = '.$id )->delete();

				foreach( $limit_goods_list as $goods_id )
				{
					//新增 goods_ids
					$cai_data = array();
					$cai_data['uniacid'] = $_W['uniacid'];
					$cai_data['soli_id'] = $id;

					$cai_data['goods_id'] = $goods_id;
					$cai_data['addtime'] = time();

					$insid = M('eaterplanet_ecommerce_solitaire_goods')->add( $cai_data );

				}
			}
		}else{

			foreach( $data['head_id_list'] as $head_dan_id )
			{
				$ins_data['head_id'] = $head_dan_id;

				$id = M('eaterplanet_ecommerce_solitaire')->add( $ins_data );

				//判断商品是否存在,先删除一次不存在的, limit_goods_list
				$limit_goods_str =  $data['goods_list'];

				$limit_goods_list = explode(',', $limit_goods_str );

				if( !empty($limit_goods_list) )
				{

					foreach( $limit_goods_list as $goods_id )
					{
						//新增 goods_ids
						$cai_data = array();
						$cai_data['uniacid'] = 0;
						$cai_data['soli_id'] = $id;

						$cai_data['goods_id'] = $goods_id;
						$cai_data['addtime'] = time();

						$insid = M('eaterplanet_ecommerce_solitaire_goods')->add( $cai_data );

					}
				}
			}

		}
	}

}
?>
