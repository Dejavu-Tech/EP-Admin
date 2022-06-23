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
namespace Admin\Model;
use Think\Model;
class GoodsCategoryModel extends Model{
	public function get_parent_cateory($pid)
	{
	   $list = M('goods_category')->field('id,pid,name')->where( array('pid'=>$pid) )->order('sort_order asc')->select();
	   return $list;
	}

	public function getInfoById($id,$field="*")
	{
	    return M('goods_category')->field($field)->where( array('id'=>$id) )->find();
	}
}
?>
