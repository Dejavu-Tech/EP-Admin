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

class RecipeModel{

	public function update($data, $uniacid = 0)
	{


		$id = $data['data']['id'];

		$sp = $data['sp'];

		$ins_data = array();

		$ins_data['recipe_name'] = $data['data']['recipe_name'];
		$ins_data['sub_name'] = $data['sub_name'];
		$ins_data['images'] =  save_media($data['data']['images']);
		$ins_data['video'] =  save_media($data['data']['video']);
		$ins_data['video'] = D('Seller/Goods')->check_douyin_video($ins_data['video']);

		$ins_data['member_id'] =  save_media($data['data']['member_id']);
		$ins_data['cate_id'] =  $data['data']['cate_id'];
		$ins_data['make_time'] = $data['data']['make_time'];
		$ins_data['diff_type'] = $data['diff_type'];
		$ins_data['state'] =  isset( $data['state']) ? 1 : 0;
		$ins_data['content'] =  $data['data']['content'];
		$ins_data['addtime'] =  time();

		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);

			M('eaterplanet_ecommerce_recipe')->where( array('id' => $id) )->save( $ins_data );

			$limit_goods_list =  $data['limit_goods_list'];

			if( !empty($limit_goods_list) )
			{
				$save_ingredients_ids = array();


				foreach( $limit_goods_list as $val )
				{
					if($val['id'] <= 0 )
					{
						//新增 goods_ids
						$cai_data = array();

						$cai_data['recipe_id'] = $id;
						$cai_data['title'] = $val['cai_name'];
						$cai_data['addtime'] = time();
						$cai_data['goods_id'] = implode(',', $val['goods_ids']);

						$insid = M('eaterplanet_ecommerce_recipe_ingredients')->add( $cai_data );

						$save_ingredients_ids[] = $insid;
					}else{
						//更新
						$save_ingredients_ids[] = $val['id'];

						$cai_data = array();

						$cai_data['recipe_id'] = $id;
						$cai_data['title'] = $val['cai_name'];


						$cai_data['goods_id'] = array();

						if( !empty($val['goods_ids']) )
							$cai_data['goods_id'] = implode(',', $val['goods_ids']);


						M('eaterplanet_ecommerce_recipe_ingredients')->where( array('id' => $val['id'] ) )->save( $cai_data );

					}
				}

				if( !empty($save_ingredients_ids) )
				{
					$limit_goods_list_str = implode(',', $save_ingredients_ids );

					M('eaterplanet_ecommerce_recipe_ingredients')->where( 'id not  in (' . $limit_goods_list_str.') and recipe_id = '.$id  )->delete();
				}
			}


		}else{

			$id = M('eaterplanet_ecommerce_recipe')->add( $ins_data );

			//判断商品是否存在,先删除一次不存在的, limit_goods_list
			$limit_goods_list =  $data['limit_goods_list'];

			if( !empty($limit_goods_list) )
			{
				$save_ingredients_ids = array();

				foreach( $limit_goods_list as $val )
				{
					if($val['id'] <= 0 )
					{
						//新增
						$cai_data = array();

						$cai_data['recipe_id'] = $id;
						$cai_data['title'] = $val['cai_name'];
						$cai_data['addtime'] = time();

						$cai_data['goods_id'] = implode(',', $val['goods_ids']);

						M('eaterplanet_ecommerce_recipe_ingredients')->add( $cai_data );
					}else{
						//更新
						$save_ingredients_ids[] = $val['id'];

						$cai_data = array();

						$cai_data['recipe_id'] = $id;
						$cai_data['title'] = $val['cai_name'];
						$cai_data['goods_id'] = implode(',', $val['goods_ids']);

						M('eaterplanet_ecommerce_recipe_ingredients')->where( array('id' => $val['id'] ) )->save( $cai_data );

					}
				}

				if( !empty($save_ingredients_ids) )
				{
					$limit_goods_list_str = implode(',', $save_ingredients_ids );

					M('eaterplanet_ecommerce_recipe_ingredients')->where( 'id not  in (' . $limit_goods_list_str.') and recipe_id = '.$id )->delete();
				}
			}

		}
	}

}
?>
