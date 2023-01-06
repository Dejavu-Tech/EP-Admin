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
use Admin\Model\BalanceModel;
class BalanceController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='结算中心';
			$this->breadcrumb2='结算管理';
	}

     public function index(){

		$model=new BalanceModel();

		$search = ' 1= 1 ';
		$post_data = I('get.');

		if( isset($post_data['seller_id']) && intval($post_data['seller_id']) > 0)
		{
			$search .= ' and  seller_id = '.$post_data['seller_id'];
		}

		if( isset($post_data['begin_time']) && !empty($post_data['begin_time']))
		{
			$search .= ' and  balance_time >= '.strtotime($post_data['begin_time']);
		}

		if( isset($post_data['end_time']) && !empty($post_data['end_time']))
		{
			$search .= ' and  balance_time <= '.strtotime($post_data['end_time']);
		}
		$seller_list = M('seller')->where( array('s_status' => 1) )->select();

		//$seller_balance =  M('seller_balance')->where( array('seller_id' => $seller_id) )->find();
		$seller_money_arr  = array();
		foreach($seller_list as $key => $val)
		{
			//s_id
			$seller_balance =  M('seller_balance')->where( array('seller_id' => $val['s_id']) )->find();

			if(empty($seller_balance))
			{
				$seller_money_arr[$val['s_id']] = 0;
			} else {
				$seller_money_arr[$val['s_id']] = $seller_balance['money'];
			}

		}

		$this->seller_money_arr = $seller_money_arr;
		$this->seller_list = $seller_list;

		$this->post_data = $post_data;

		$data=$model->show_balance_page($search);

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->display();
	 }


	 public function suretixianmoney()
	 {
	     $id = I('get.id');
	     $seller_tixian = M('seller_tixian')->where( array('id' => $id) )->find();
	     if(!empty($seller_tixian)) {
	         $seller_balance = M('seller_balance')->where( array('seller_id' => $seller_tixian['seller_id']) )->find();

	         M('seller_tixian')->where( array('id' => $id) )->save( array('state' => 1) );

	         $data = array();
	         $data['dongmoney'] =  $seller_balance['dongmoney']- $seller_tixian['money'];
	         $data['hasgetmoney'] =  $seller_balance['hasgetmoney'] + $seller_tixian['money'];

	         M('seller_balance')->where( array('seller_id' => $seller_balance['seller_id']) )->save($data);


	         $return = array(
	             'status'=>'success',
	             'message'=>'确认成功',
	             'jump'=>U('Balance/assets')
	         );
	         $this->osc_alert($return);

	     }else {

	         $return = array(
	             'status'=>'fail',
	             'message'=>'确认失败',
	             'jump'=>U('Balance/assets')
	         );
	         $this->osc_alert($return);

	     }

	 }
	 public function suremoney()
	 {
	     $bid =  I('get.bid');
	     M('balance')->where( array('bid' => $bid) )->save( array('state' => 2) );

	     $balance_info = M('balance')->where( array('bid' => $bid) )->find();//seller_id
	     $seller_balance_info = M('seller_balance')->where( array('seller_id' => $balance_info['seller_id']) )->find();

	     if(empty($seller_balance_info)) {
	         $data = array();
	         $data['money']   =  $balance_info['money'];
	         $data['seller_id'] = $balance_info['seller_id'];
	         $data['hasgetmoney'] = 0;
	         $data['dongmoney'] = 0;
	         M('seller_balance')->add($data);
	     } else {
	         $data = array();
	         $data['money']   =  $balance_info['money']+ $seller_balance_info['money'];
	         M('seller_balance')->where( array('seller_id' => $balance_info['seller_id']) )->save($data);
	     }

	     $return = array(
	         'status'=>'success',
	         'message'=>'确认成功',
	         'jump'=>U('Balance/index')
	     );
	     $this->osc_alert($return);
	 }

	 public function assets()
	 {

	     $this->breadcrumb2='申请提现';
	     $model=new BalanceModel();
		 $name = I('get.name','');

	     $search = '';
		 if(!empty($name))
		 {
			 $search = ' and s.s_true_name like "%'.$name.'%" ';
		 }
	     $data=$model->show_balance_assets_page($search);

	     $this->assign('empty',$data['empty']);// 赋值数据集
	     $this->assign('list',$data['list']);// 赋值数据集
	     $this->assign('page',$data['page']);// 赋值分页输出

	     $seller_balance =  M('seller_balance')->where( array('seller_id' => SELLERUID) )->find();
	     if( empty($seller_balance) ) {
	         $seller_balance = array();
	         $seller_balance['money'] = 0;
	         $seller_balance['hasgetmoney'] = 0;
	         $seller_balance['dongmoney'] = 0;
	     }

	     $this->seller_balance = $seller_balance;
	     $this->display();
	 }

	 public function orderlook()
	 {
	     $model=new BalanceModel();
	     $bid = I('get.bid');
	     $data=$model->show_balance_order_page($bid);
		 $balance = M('balance')->where( array('bid' => $bid) )->find();
		 $seller_id = $balance['seller_id'];

		  $seller_balance =  M('seller_balance')->where( array('seller_id' => $seller_id) )->find();
	     if( empty($seller_balance) ) {
	         $seller_balance = array();
	         $seller_balance['money'] = 0;
	         $seller_balance['hasgetmoney'] = 0;
	         $seller_balance['dongmoney'] = 0;
	     }

	     $wait_balance_money = $model->wait_balance_order($seller_id);

	     $this->wait_balance_money = $wait_balance_money;
	     $this->seller_balance = $seller_balance;


	     $this->assign('empty',$data['empty']);// 赋值数据集
	     $this->assign('list',$data['list']);// 赋值数据集
	     $this->assign('page',$data['page']);// 赋值分页输出
	     $this->display();
	 }


}
?>
