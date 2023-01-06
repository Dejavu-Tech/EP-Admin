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
 * 轮播器
 */
class SliderWidget extends Controller{

	function slider_show(){
		if (!$slider_cache = S('slider_cache')) {
			$slider=M('plugins_slider')->where( array('type' => 'index_ad_head') )->field('image,url')->order('sort_order desc')->select();
			S('slider_cache', $slider);
			$slider_cache=$slider;
		}
		$this->slider=$slider_cache;
		$this->display('Widget:slider');
	}
	function slider_seller_show($seller_id)
	{
		if (!$slider_cache = S('slider_seller_cache_'.$seller_id) ) {

			$slider=M('seller_ad')->where( array('seller_id' => $seller_id) )->field('image,url')->order('ordersort desc,id asc')->select();
			S('slider_seller_cache_'.$seller_id, $slider);
			$slider_cache=$slider;
		}
		$this->slider=$slider_cache;
		$this->display('Widget:slider_seller');
	}
	/**
		首页横条广告位
	**/
	function slider_show_list(){
		if (!$slider_cache = S('slider_list_cache')) {
			$slider=M('plugins_slider')->where( array('type' => 'index_ad_list') )->field('image,url')->order('sort_order desc,slider_id desc')->select();
			S('slider_list_cache', $slider);
			$slider_cache=$slider;
		}
		$this->slider=$slider_cache;
		$this->display('Widget:slider_list');
	}

}
