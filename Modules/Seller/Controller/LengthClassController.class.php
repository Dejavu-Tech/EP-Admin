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
namespace Seller\Controller;
use Admin\Model\LengthClassModel;

class LengthClassController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='商品';
			$this->breadcrumb2='长度单位';
	}

     public function index(){

		$model=new LengthClassModel();

		$data=$model->show_length_class_page();

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	function add(){

		if(IS_POST){

			$model=new LengthClassModel();
			$data=I('post.');
			$return=$model->add_length_class($data);
			$this->osc_alert($return);
		}

		$this->crumbs='新增';
		$this->action=U('LengthClass/add');
		$this->display('edit');
	}

	function edit(){
		if(IS_POST){
			$model=new LengthClassModel();
			$data=I('post.');
			$return=$model->edit_length_class($data);
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('LengthClass/edit');
		$this->d=M('LengthClass')->find(I('id'));
		$this->display('edit');
	}

	 public function del(){
		$model=new LengthClassModel();
		$return=$model->del_length_class();
		$this->osc_alert($return);
	 }

}
?>
