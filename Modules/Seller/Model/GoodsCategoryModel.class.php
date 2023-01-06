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
use Think\Model;
class GoodsCategoryModel extends Model{


	public function update($data,$cate_type='normal')
	{

		$ins_data = array();
		$ins_data['is_hot'] = $data['is_hot'];
		$ins_data['is_show'] = intval($data['is_show']);

		if($data['is_show_topic']){
			$ins_data['is_show_topic'] = intval($data['is_show_topic']);
		}else{
			$ins_data['is_show_topic'] = 0;
		}

		$ins_data['is_type_show'] = intval($data['is_type_show']);
		$ins_data['is_express_show'] = intval($data['is_express_show']);
		$ins_data['name'] = $data['name'];
		$ins_data['logo'] = save_media($data['logo']);
		$ins_data['banner'] = save_media($data['banner']);
		$ins_data['sort_order'] = $data['sort_order'];
		$ins_data['cate_type'] = $cate_type;

		if(isset($data['id']) && !empty($data['id']))
		{
			//更新
			M('eaterplanet_ecommerce_goods_category')->where( array('id' => $data['id']) )->save($ins_data);
			D('Seller/Operatelog')->addOperateLog('goods','修改商品分类--'.$data['name']);
			$id = $data['id'];
		} else{
			$ins_data['pid'] = $data['pid'];
			//新增

			M('eaterplanet_ecommerce_goods_category')->add($ins_data);
			D('Seller/Operatelog')->addOperateLog('goods','新增商品分类--'.$data['name']);

		}


	}

	public function goodscategory_modify($datas)
	{

		$datas = json_decode(html_entity_decode($datas), true);

		if (!is_array($datas)) {
			show_json(0, '分类保存失败，请重试!');
		}

		$cateids = array();
		$displayorder = count($datas);

		foreach ($datas as $row) {
			$cateids[] = $row['id'];
			M('eaterplanet_ecommerce_goods_category')->where( array('id' => $row['id']) )->save(array('pid' => 0, 'sort_order' => $displayorder));

			if ($row['children'] && is_array($row['children'])) {
				$displayorder_child = count($row['children']);

				foreach ($row['children'] as $child) {
					$cateids[] = $child['id'];

					M('eaterplanet_ecommerce_goods_category')->where( array('id' => $child['id']) )->save( array('pid' => $row['id'], 'sort_order' => $displayorder_child) );

					--$displayorder_child;
					if ($child['children'] && is_array($child['children'])) {
						$displayorder_third = count($child['children']);

						foreach ($child['children'] as $third) {
							$cateids[] = $third['id'];

							M('eaterplanet_ecommerce_goods_category')->where( array('id' => $third['id']) )->save( array('pid' => $child['id'], 'sort_order' => $displayorder_third) );

							--$displayorder_third;
							if ($third['children'] && is_array($third['children'])) {
								$displayorder_fourth = count($third['children']);

								foreach ($child['children'] as $fourth) {
									$cateids[] = $fourth['id'];
									M('eaterplanet_ecommerce_goods_category')->where( array('id' => $fourth['id']) )->save( array('pid' => $third['id'], 'sort_order' => $displayorder_third) );

									--$displayorder_fourth;
								}
							}
						}
					}
				}
			}

			--$displayorder;
		}

		if (!empty($cateids)) {
			M('eaterplanet_ecommerce_goods_category')->where( 'id not in (' . implode(',', $cateids) . ')' )->delete();
		}


	}

	public function getFullCategory($fullname = false, $enabled = false,$cate_type = 'normal')
	{


		$allcategory = array();

		$category = M('eaterplanet_ecommerce_goods_category')->where(' cate_type="'.$cate_type.'" ')->order('pid ASC, sort_order DESC')->select();


		if (empty($category)) {
			return array();
		}

		foreach ($category as &$c) {
			if (empty($c['pid'])) {
				$allcategory[] = $c;

				foreach ($category as &$c1) {
					if ($c1['pid'] != $c['id']) {
						continue;
					}

					if ($fullname) {
						$c1['name'] = $c['name'] . '-' . $c1['name'];
					}

					$allcategory[] = $c1;

					foreach ($category as &$c2) {
						if ($c2['pid'] != $c1['id']) {
							continue;
						}

						if ($fullname) {
							$c2['name'] = $c1['name'] . '-' . $c2['name'];
						}

						$allcategory[] = $c2;

						foreach ($category as &$c3) {
							if ($c3['pid'] != $c2['id']) {
								continue;
							}

							if ($fullname) {
								$c3['name'] = $c2['name'] . '-' . $c3['name'];
							}

							$allcategory[] = $c3;
						}

						unset($c3);
					}

					unset($c2);
				}

				unset($c1);
			}

			unset($c);
		}

		return $allcategory;
	}


	public function get_parent_cateory($pid,$store_id)
	{
	   $bind_list = M('store_bind_class')->where(array('seller_id' => $store_id) )->select();
	   $list = array();
	   if(!empty($bind_list))
	   {
	       $cate_ids = array();
	       $cate_ids_str = '';
	       foreach($bind_list as $val)
	       {
	           if(!empty($val['class_1']))
	           {
	               $cate_ids[] = $val['class_1'];
	           }
	           if(!empty($val['class_2']))
	           {
	               $cate_ids[] = $val['class_2'];
	           }
	           if(!empty($val['class_3']))
	           {
	               $cate_ids[] = $val['class_3'];
	           }
	       }
	       $cate_ids_str = implode(',',$cate_ids);

	       $list = M('goods_category')->field('id,pid,name')->where( array('pid'=>$pid,'id' => array('in',$cate_ids_str)) )->order('sort_order asc')->select();

	       if($pid > 0)
	       {
	           $list = M('goods_category')->field('id,pid,name')->where( array('pid'=>$pid) )->order('sort_order asc')->select();
	       }
	   }

	   return $list;
	}

	public function getInfoById($id,$field="*")
	{
	    return M('goods_category')->field($field)->where( array('id'=>$id) )->find();
	}

	/**
	 * 获取所有商品分类的子分类编号
	 * @param $id
	 */
	public function getChildCategorys($id,$field="id"){
		$cate_list = array();
		$cate_list[] = $id;
		$list = M('eaterplanet_ecommerce_goods_category')->field($field)->where( array('pid'=>$id) )->select();
		foreach($list as $k=>$v){
			$cate_list[] = $v['id'];
			$c_list = M('eaterplanet_ecommerce_goods_category')->field($field)->where( array('pid'=>$v['id']) )->select();
			foreach($c_list as $ck=>$cv){
				$cate_list[] = $cv['id'];
			}
		}
		return implode(',',$cate_list);
	}

	/**
	 * 全部分类树形结构
	 */
	public function getThreeCategory($enabled = false,$cate_type = 'normal')
	{
		$allcategory = array();
		$category = M('eaterplanet_ecommerce_goods_category')->where(' cate_type="'.$cate_type.'" ')->order('pid ASC, sort_order DESC')->select();

		if (empty($category)) {
			return array();
		}

		foreach ($category as $pk=>&$c) {
			if (empty($c['pid'])) {
				$c['level'] = 1;
				$c['category_id_1'] = $c['id'];
				$c['category_id_2'] = 0;
				$c['category_id_3'] = 0;
				if (!empty($c['logo'])) { $c['logo'] = tomedia($c['logo']);}
				$allcategory[$pk] = $c;

				foreach ($category as $sec_k=>&$c1) {
					if ($c1['pid'] != $c['id']) {
						continue;
					}

					$c1['level'] = 2;
					$c1['category_id_1'] = $c['id'];
					$c1['category_id_2'] = $c1['id'];
					$c1['category_id_3'] = 0;
					if (!empty($c1['logo'])) { $c1['logo'] = tomedia($c1['logo']);}
					$allcategory[$pk]['child_list'][$sec_k] = $c1;

					foreach ($category as $three_k=>&$c2) {
						if ($c2['pid'] != $c1['id']) {
							continue;
						}

						$c2['level'] = 3;
						$c2['category_id_1'] = $c['id'];
						$c2['category_id_2'] = $c1['id'];
						$c2['category_id_3'] = $c2['id'];
						if (!empty($c2['logo'])) { $c2['logo'] = tomedia($c2['logo']);}
						$allcategory[$pk]['child_list'][$sec_k]['child_list'][$three_k] = $c2;
					}

					unset($c2);
				}

				unset($c1);
			}

			unset($c);
		}

		return $allcategory;
	}
}
?>
