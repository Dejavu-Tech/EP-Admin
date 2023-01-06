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
class ConfigModel{
	/**
	 *显示分页
	 */
	public function show_config_page($search){

		$sql="select * from ".C('DB_PREFIX')."config where 1 ";

		if(isset($search['name'])){
			$sql.=" and name like '%".$search['name']."%'";
		}
		if(isset($search['config_group'])){
			$sql.=" and config_group='".$search['config_group']."'";
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by config_group desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}



	 function validate($data,$status='update'){

		$error=array();
		if(empty($data['name'])){
			$error='名称必填';
		}

		if($status=='add'){
			if(M('config')->getByName($data['name'])){
				$error='该名称已经存在';
			}
		}else{
			if(M('config')->where('id!='.$data['id']." AND name='".$data['name']."'")->find()){
				$error='该名称已经存在';
			}
		}

		if($error){

			return array(
				'status'=>'back',
				'message'=>$error
			);

		}
	}


	public function add_config($data){

			$error=$this->validate($data,'add');

			if($error){
				return $error;
			}

			$r=M('config')->add($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('config/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('config/index')
				);
			}


	}

	public function edit_config($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('config')->save($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('config/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('config/index')
				);
			}


	}

	public function del_config(){
		$r=M('config')->where(array('id'=>I('id')))->delete();

		if($r){
				return array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('config/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'删除失败',
				'jump'=>U('config/index')
				);
			}
	}

}
?>
