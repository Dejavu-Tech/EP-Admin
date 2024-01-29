<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.cloud/
 * @copyright Copyright (c) 2019-2024 Dejavu Tech.
 * @license   https://github.com/Dejavu-Tech/EP-Admin/blob/main/LICENSE
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
