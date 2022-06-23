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
namespace Home\Controller;

class SearchController extends CommonController {


    protected function _initialize()
    {
        parent::_initialize();
        $this->cur_page = 'search';
    }


	//进行中
	public function index(){

	    $parent_list = M('goods_category')->where( array('pid' =>0, 'is_search' =>1) )->order('c_sort_order desc,sort_order desc')->select();

	    foreach($parent_list as $key => $val)
	    {
	        $child_list = M('goods_category')->where( array('pid' => $val['id'], 'is_search' =>1) )->order('c_sort_order desc,sort_order desc')->select();
	        $val['child_list'] = $child_list;
	        $parent_list[$key] = $val;
	    }
	    $this->parent_list = $parent_list;

		$this->display();
	}



}
