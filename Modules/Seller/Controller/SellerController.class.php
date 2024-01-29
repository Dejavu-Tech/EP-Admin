<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.cloud/
 * @copyright Copyright (c) 2019-2024 Dejavu Tech.
 * @license   https://github.com/Dejavu-Tech/EP-Admin/blob/main/LICENSE
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Seller\Controller;
use Admin\Model\SellerModel;
class SellerController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='店铺设置';
			$this->breadcrumb2='店铺信息';
	}

     public function info(){

		$data = M('seller')->where( array('s_id' => SELLERUID) )->find();
		if(!empty($data['s_logo']))
		  $data['thumb_logo'] = resize($data['s_logo'], 100, 100);

		$this->type = -1;
		$this->action=U('Seller/edit');
		$this->data=$data;
    	$this->display();
	}

	public function addad()
	{
		if(IS_POST){

			$model=new SellerModel();
			$data=I('post.');
			$data['seller_id'] = SELLERUID;
			$return=$model->add_ad($data);
			$this->osc_alert($return);
		}

		$this->crumbs='新增';
		$this->action=U('Seller/addad');
		$this->display('editad');
	}

	function editad(){
		if(IS_POST){
			$model=new SellerModel();
			$data=I('post.');
			$return=$model->edit_ad($data);

			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('Seller/editad');
		$this->slider=M('seller_ad')->find(I('id'));
		$this->thumb_image=resize($this->slider['image'], 100, 100);

		$this->display('editad');
	}
	public function delad(){
		$r=M('seller_ad')->delete(I('id'));
		if($r){
			$this->redirect('Seller/adlist');
		}
	}

	public function adlist()
	{
		$model= new SellerModel();

		$data=$model->show_slider_page(SELLERUID);

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->type = 1;
    	$this->display();
	}
	function edit(){
		$info = M('seller')->where( array('s_id' => SELLERUID) )->find();
		if(IS_POST){
			$model=new SellerModel();
			$data=I('post.');
			$data['s_status'] = $info['s_status'];
			if($data['s_id'] != SELLERUID)
			{
				die('非法操作');
			}

			$return=$model->edit_seller_user($data);

			$return['jump'] = U('Seller/info');

			$this->osc_alert($return);
		}
	}
}
?>
