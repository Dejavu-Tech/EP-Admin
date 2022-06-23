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

class OrderStatusModel{
	/**
	 *显示订单状态单位分页
	 */
	public function show_order_status_page(){

		$count=M('OrderStatus')->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('OrderStatus')->order('order_status_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function validate($data,$status='update'){

		$error=array();
		if(empty($data['name'])){
			$error='订单状态名称必填';
		}

		if($status=='add'){
			if(M('OrderStatus')->getByName($data['name'])){
				$error='该订单状态名称已经存在';
			}
		}else{
			if(M('OrderStatus')->where('order_status_id!='.$data['order_status_id']." AND name='".$data['name']."'")->find()){
				$error='该订单状态名称已经存在';
			}
		}

		if($error){

			return array(
				'status'=>'back',
				'message'=>$error
			);

		}
	}


	public function add_order_status($data){

			$error=$this->validate($data,'add');

			if($error){
				return $error;
			}

			$r=M('OrderStatus')->add($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('OrderStatus/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('OrderStatus/index')
				);
			}


	}

	public function edit_order_status($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('OrderStatus')->save($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('OrderStatus/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('OrderStatus/index')
				);
			}


	}

	public function del_order_status(){
		$r=M('OrderStatus')->where(array('order_status_id'=>I('id')))->delete();

		if($r){
				return array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('OrderStatus/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'删除失败',
				'jump'=>U('OrderStatus/index')
				);
			}
	}

}
?>
