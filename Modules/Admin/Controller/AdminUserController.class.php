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
use Admin\Model\AdminUserModel;

class AdminUserController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='权限管理';
			$this->breadcrumb2='管理员信息';
	}

     public function index(){

		$model=new AdminUserModel();

		$data=$model->show_admin_user_page();

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	function add(){
		$model=new AdminUserModel();
		if(IS_POST){
			$data=I('post.');
			$return=$model->add_admin_user($data);
			$this->osc_alert($return);
		}
		$this->crumbs='新增';
		$this->action=U('AdminUser/add');

		$this->assign('roles', D('Role', 'Service')->getRoles());

		$this->display();
	}

	function info(){
		$model=new AdminUserModel();
		if(IS_POST){
			$data=I('post.');
			$return=$model->edit_admin_user($data);
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('AdminUser/info');
		$this->data=M('Admin')->find(I('id'));
		$this->assign('roles', D('Role', 'Service')->getRoles());

		$this->display();
	}

}
?>
