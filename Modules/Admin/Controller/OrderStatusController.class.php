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
use Admin\Model\OrderStatusModel;
class OrderStatusController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='订单';
			$this->breadcrumb2='订单状态';
	}

     public function index(){

		$model=new OrderStatusModel();

		$data=$model->show_order_status_page();

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	function add(){

		if(IS_POST){

			$model=new OrderStatusModel();
			$data=I('post.');
			$return=$model->add_order_status($data);
			$this->osc_alert($return);
		}

		$this->crumbs='新增';
		$this->action=U('OrderStatus/add');
		$this->display('edit');
	}

	function edit(){
		if(IS_POST){
			$model=new OrderStatusModel();
			$data=I('post.');
			$return=$model->edit_order_status($data);
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('OrderStatus/edit');
		$this->d=M('OrderStatus')->find(I('id'));
		$this->display('edit');
	}

	 public function del(){
		$model=new OrderStatusModel();
		$return=$model->del_order_status();
		$this->osc_alert($return);
	 }

}
?>
