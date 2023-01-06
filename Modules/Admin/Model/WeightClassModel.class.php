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

class WeightClassModel{
	/**
	 *显示重量单位分页
	 */
	public function show_weight_class_page(){

		$count=M('WeightClass')->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('WeightClass')->order('weight_class_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function validate($data,$status='update'){

		$error=array();
		if(empty($data['title'])){
			$error='重量名称必填';
		}elseif(empty($data['unit'])){
			$error='重量单位必填';
		}elseif(empty($data['value'])){
			$error='重量值 必填';
		}



		if($status=='add'){
			if(M('WeightClass')->getByTitle($data['title'])){
				$error='该重量名称已经存在';
			}
		}else{
			if(M('WeightClass')->where('weight_class_id!='.$data['weight_class_id']." AND title='".$data['title']."'")->find()){
				$error='该重量名称已经存在';
			}
		}


		if($error){

			return array(
				'status'=>'back',
				'message'=>$error
			);

		}
	}


	public function add_weight_class($data){

			$error=$this->validate($data,'add');

			if($error){
				return $error;
			}

			$r=M('WeightClass')->add($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('WeightClass/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('WeightClass/index')
				);
			}


	}

	public function edit_weight_class($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('WeightClass')->save($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('WeightClass/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('WeightClass/index')
				);
			}


	}

	public function del_weight_class(){
		$r=M('WeightClass')->where(array('weight_class_id'=>I('id')))->delete();

		if($r){
				return array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('WeightClass/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'删除失败',
				'jump'=>U('WeightClass/index')
				);
			}
	}

}
?>
