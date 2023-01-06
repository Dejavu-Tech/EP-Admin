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
namespace Seller\Controller;
use Admin\Model\VoucherModel;
class VoucherController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='优惠券中心';
			$this->breadcrumb2='优惠券管理';
	}

     public function index(){

		$model=new VoucherModel();

		$data=$model->show_voucher_class_page(SELLERUID);

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	function get_goods_voucher_all()
	{
		$html = $this->fetch('Voucher:fetch_goods_ajax');
		echo json_encode( array('code' => 0 , 'html' => $html) );
		die();
	}

	function add(){

		if(IS_POST){

			$model=new VoucherModel();
			$data=I('post.');

			$data['store_id'] = SELLERUID;
			if( empty($data['voucher_title']) ) {
				$status = array('status'=>'back','message'=>'优惠券名称不能为空');
	            $this->osc_alert($status);
			}

			if( empty($data['credit']) ) {
				$status = array('status'=>'back','message'=>'优惠券金额不能为空');
	            $this->osc_alert($status);
			}

			if( empty($data['total_count']) ) {
				$status = array('status'=>'back','message'=>'可领取人数不能为空');
	            $this->osc_alert($status);
			}


			$return=$model->add_voucher($data);

			$this->osc_alert($return);
			die();
		}

		$this->crumbs='新增';
		$this->action=U('Voucher/add');
		$this->display('edit');
	}



	public function voucherlist()
	{
		$id = I('get.id');

		$model=new VoucherModel();

		$data=$model->show_voucher_list_page($id);

		$voucher_info = M('voucher')->where( array('id' => $id) )->find();

		$this->voucher_info = $voucher_info;
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();

	}

	 public function del(){
		$id = I('get.id');

		$model=new VoucherModel();

		$return=$model->del_voucher($id);

		$this->osc_alert($return);
	 }

}
?>
