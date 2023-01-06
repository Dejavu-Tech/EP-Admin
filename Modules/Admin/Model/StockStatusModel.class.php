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
namespace Admin\Model;
class StockStatusModel{
	/**
	 *显示库存状态单位分页
	 */
	public function show_stock_status_page(){

		$count=M('StockStatus')->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('StockStatus')->order('stock_status_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function validate($data,$status='update'){

		$error=array();
		if(empty($data['name'])){
			$error='库存状态名称必填';
		}

		if($status=='add'){
			if(M('StockStatus')->getByName($data['name'])){
				$error='该库存状态名称已经存在';
			}
		}else{
			if(M('StockStatus')->where('stock_status_id!='.$data['stock_status_id']." AND name='".$data['name']."'")->find()){
				$error='该库存状态名称已经存在';
			}
		}

		if($error){

			return array(
				'status'=>'back',
				'message'=>$error
			);

		}
	}


	public function add_stock_status($data){

			$error=$this->validate($data,'add');

			if($error){
				return $error;
			}

			$r=M('StockStatus')->add($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('StockStatus/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('StockStatus/index')
				);
			}


	}

	public function edit_stock_status($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('StockStatus')->save($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('StockStatus/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('StockStatus/index')
				);
			}


	}

	public function del_stock_status(){
		$r=M('StockStatus')->where(array('stock_status_id'=>I('id')))->delete();

		if($r){
				return array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('StockStatus/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'删除失败',
				'jump'=>U('StockStatus/index')
				);
			}
	}

}
?>
