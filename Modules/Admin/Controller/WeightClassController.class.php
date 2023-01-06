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
namespace Admin\Controller;
use Admin\Model\WeightClassModel;
class WeightClassController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='商品';
			$this->breadcrumb2='重量单位';
	}

     public function index(){

		$model=new WeightClassModel();

		$data=$model->show_weight_class_page();

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	function add(){

		if(IS_POST){

			$model=new WeightClassModel();
			$data=I('post.');
			$return=$model->add_weight_class($data);
			$this->osc_alert($return);
		}

		$this->crumbs='新增';
		$this->action=U('WeightClass/add');
		$this->display('edit');
	}

	function edit(){
		if(IS_POST){
			$model=new WeightClassModel();
			$data=I('post.');
			$return=$model->edit_weight_class($data);
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('WeightClass/edit');
		$this->d=M('WeightClass')->find(I('id'));
		$this->display('edit');
	}

	 public function del(){
		$model=new WeightClassModel();

		$return=$model->del_weight_class();
		$this->osc_alert($return);
	 }

}
?>
