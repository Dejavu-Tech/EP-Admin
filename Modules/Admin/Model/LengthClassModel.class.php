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
class LengthClassModel{
	/**
	 *显示长度单位分页
	 */
	public function show_length_class_page(){

		$count=M('LengthClass')->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('LengthClass')->order('length_class_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function validate($data,$status='update'){

		$error=array();
		if(empty($data['title'])){
			$error='长度名称必填';
		}
		if($status=='add'){
			if(M('LengthClass')->getByTitle($data['title'])){
				$error='该长度名称已经存在';
			}
		}else{
			if(M('LengthClass')->where('length_class_id!='.$data['length_class_id']." AND title='".$data['title']."'")->find()){
				$error='该长度名称已经存在';
			}
		}
		if(empty($data['unit'])){
			$error='长度单位必填';
		}
		if(empty($data['value'])){
			$error='长度值 必填';
		}

		if($error){

			return array(
				'status'=>'back',
				'message'=>$error
			);

		}
	}


	public function add_length_class($data){

			$error=$this->validate($data,'add');

			if($error){
				return $error;
			}

			$r=M('LengthClass')->add($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('LengthClass/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('LengthClass/index')
				);
			}


	}

	public function edit_length_class($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('LengthClass')->save($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('LengthClass/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('LengthClass/index')
				);
			}


	}

	public function del_length_class(){
		$r=M('LengthClass')->where(array('length_class_id'=>I('id')))->delete();

		if($r){
				return array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('LengthClass/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'删除失败',
				'jump'=>U('LengthClass/index')
				);
			}
	}

}
?>
