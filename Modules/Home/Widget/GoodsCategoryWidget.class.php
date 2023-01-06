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
namespace Home\Widget;
use Think\Controller;
/**
 * 商品分类
 */
class GoodsCategoryWidget extends Controller{

	public function goods_category_show()
	{
		$gid = I('get.gid',0);
		$key='index_goodscategory_cache';
		if (!$hot_list = S($key)) {
		    $hot_list = M('goods_category')->where( array('is_hot' => 1) )->order('sort_order desc')->select();
		    S($key, $hot_list);
		}
		$this->gid = $gid;

		$this->hot_list = $hot_list;
		$this->display('Widget:index_goods_category');
	}

	function blog_category_show(){
		if (!$blog_category = S('blog_category')) {

			$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
			$list=M('BlogCategory')->select();
			$category=list_to_tree($list);

			foreach ($category as $k => $v) {
				$category[$k]['id']=$hashids->encode($v['id']);
				if(isset($v['children']))
				foreach ($v['children'] as $k1 => $v1) {
					$category[$k]['children'][$k1]['id']=$hashids->encode($v1['id']);
				}
			}

			S('blog_category', $category);
			$blog_category=$category;
		}

		$this->blog_category=$blog_category;

		$this->display('Widget:blog_category');
	}

}
