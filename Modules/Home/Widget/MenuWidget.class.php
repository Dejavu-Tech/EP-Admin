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
 * 导航
 */
class MenuWidget extends Controller{

	function menu_show($type){
		if (!$menu_cache = S('menu_cache')) {
			$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
			$menu=M('goods_category')->select();
			foreach ($menu as $k => $v) {
				$menu[$k]['id']=$hashids->encode($v['id']);
			}
			S('menu_cache', $menu);
			$menu_cache=$menu;
		}

		$this->menu=$menu_cache;
		$this->type=$type;
		$this->display('Widget:menu');
	}

}
