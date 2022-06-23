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

class PickupModel{

	public function show_pickup_page($search = array()){

	    $where = array();

	    if(!empty($search) && isset($search['store_id'])) {
	        $where['store_id'] = $search['store_id'];
	    }

		$count=M('pick_up')->where($where)->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('pick_up')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}



	public function show_pickup_member_page( $search = array() )
	{
		$where = array();

	    if(!empty($search) && isset($search['store_id'])) {
	        $where['store_id'] = $search['store_id'];
	    }

		if(!empty($search) && isset($search['pick_up_id'])) {
	        $where['pick_up_id'] = $search['pick_up_id'];
	    }
		//


		$count=M('pick_member')->where($where)->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('pick_member')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		foreach( $list as $key => $val )
		{
			if( $val['pick_up_id'] == 0)
			{
				$val['pick_name'] = '<span class="red">所有店铺</span>';
			}else{
				$pick_up_info =  M('pick_up')->field('pick_name')->where( array('id' => $val['pick_up_id']) )->find();
				$val['pick_name'] = $pick_up_info['pick_name'];
			}
			$pick_order_count =  M('pick_order')->where( array('pick_member_id' => $val['member_id']) )->count();
			//name
			$val['pick_order_count'] = $pick_order_count;

			$val['member_info'] = M('member')->field('name,avatar')->where( array('member_id' => $val['member_id']) )->find();
			$list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

}
?>
