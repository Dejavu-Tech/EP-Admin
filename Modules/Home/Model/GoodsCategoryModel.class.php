<?php
namespace Home\Model;
use Think\Model;
/**
 * 商品分类模型
 * @author Albert.Z
 *
 */
class GoodsCategoryModel {


	/**
		获取首页的商品分类
	**/
	public function get_index_goods_category($pid = 0 ,$cate_type = 'normal', $is_show=1, $is_type_show=0, $show_all=0)
	{
		// and pid = {$pid}
		if( empty($pid) )
		{
			$pid = 0;
		}

		$where = '';
		if($show_all==1) {
			$cate_list = M('eaterplanet_ecommerce_goods_category')->where( array('pid' =>$pid,'cate_type' => $cate_type ) )->order('sort_order desc, id desc')->select();
		} else if($is_type_show==1) {
	    	$cate_list = M('eaterplanet_ecommerce_goods_category')->where( array('is_type_show' => 1,'pid' =>$pid,'cate_type' => $cate_type ) )
			->order('sort_order desc, id desc')->select();
	    } else {
	    	$cate_list = M('eaterplanet_ecommerce_goods_category')->where( array('is_show' => 1,'pid' =>$pid,'cate_type' => $cate_type ) )
			->order('sort_order desc, id desc')->select();
	    }

		$need_data = array();

		foreach($cate_list as $key => $cate)
		{
			$need_data[$key]['id'] = $cate['id'];
			$need_data[$key]['name'] = $cate['name'];
			$need_data[$key]['banner'] = $cate['banner'] && !empty($cate['banner']) ? tomedia($cate['banner']) : '';
			$need_data[$key]['logo'] = $cate['logo'] && !empty($cate['logo']) ? tomedia($cate['logo']) : '';
			$need_data[$key]['sort_order'] = $cate['sort_order'];

			$params = array();
			$params['pid'] = $cate['id'];
			if($show_all==1) {
				// 显示所有
			} else if($is_type_show==1) {
				$params['is_type_show'] = 1;
			} else {
				$params['is_show'] = 1;
			}

			$sub_cate = M('eaterplanet_ecommerce_goods_category')->field('id,name,sort_order')
						->where($params)->order('sort_order desc, id desc')->select();

			$need_data[$key]['sub'] = $sub_cate;
		}


		return $need_data;
	}

	/**
	 * 获取所有分类包括子分类
	 * @param  string  $cate_type    [description]
	 * @param  integer $is_show      [description]
	 * @param  integer $is_type_show [description]
	 * @return [type]                [description]
	 */
	public function get_all_goods_category($cate_type = 'normal', $is_show=1, $is_type_show=0)
	{
		// and pid = {$pid}
		if( empty($pid) )
		{
			$pid = 0;
		}

		$where = '';
	    if($is_type_show==1) {
	    	$cate_list = M('eaterplanet_ecommerce_goods_category')->where( array('is_type_show' => 1, 'cate_type' => $cate_type ) )
			->order('sort_order desc, id desc')->select();
	    } else {
	    	$params = array();
	    	$params['cate_type'] = $cate_type;
	    	if($is_show!=-1) {
	    		$params['is_show'] = $is_show;
	    	}
	    	$cate_list = M('eaterplanet_ecommerce_goods_category')->where( $params )->order('sort_order desc, id desc')->select();
	    }

		$need_data = array();
		foreach($cate_list as $key => $cate)
		{
			$need_data[$key]['id'] = $cate['id'];
			$need_data[$key]['pid'] = $cate['pid'];
			$need_data[$key]['name'] = $cate['name'];
			$need_data[$key]['banner'] = $cate['banner'] && !empty($cate['banner']) ? tomedia($cate['banner']) : '';
			$need_data[$key]['logo'] = $cate['logo'] && !empty($cate['logo']) ? tomedia($cate['logo']) : '';
			$need_data[$key]['sort_order'] = $cate['sort_order'];
			$need_data[$key]['is_show'] = $cate['is_show'];
		}

		return $need_data;
	}
}
