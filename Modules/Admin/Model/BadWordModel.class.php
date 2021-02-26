<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      http://www.ch871.com/
 * @copyright Copyright (c) 2019-2021 ch871.com.
 * @license   http://www.ch871.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Admin\Model;

class BadWordModel{
	/**
	 *显示库存状态单位分页
	 */
	public function show_bad_word_page(){

		$count=M('BadWord')->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('BadWord')->order('bad_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function validate($data,$status='update'){

		$error=array();
		if(empty($data['badword'])){
			$error='违禁词必填';
		}
		if($status=='add'){
			if(M('BadWord')->getByBadword($data['badword'])){
				$error='该违禁词已经存在';
			}
		}else{
			if(M('BadWord')->where('bad_id!='.$data['bad_id']." AND badword='".$data['badword']."'")->find()){
				$error='该违禁词已经存在';
			}
		}

		if($error){

			return array(
				'status'=>'back',
				'message'=>$error
			);

		}
	}


	public function add_bad_word($data){

			$error=$this->validate($data,'add');

			if($error){
				return $error;
			}

			$r=M('BadWord')->add($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('BadWord/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('BadWord/index')
				);
			}


	}

	public function edit_bad_word($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('BadWord')->save($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('BadWord/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('BadWord/index')
				);
			}


	}

		public function del_bad_word(){

		$r=M('BadWord')->where(array('bad_id'=>I('id')))->delete();

		if($r){
				return array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('BadWord/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'删除失败',
				'jump'=>U('BadWord/index')
				);
			}
	}

}
?>
