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
namespace Home\Controller;

/**
 * 核销员核销
 * Class HexiaoController
 * @package Home\Controller
 */
class HexiaoController extends CommonController {

	protected function _initialize(){
		parent::_initialize();
	}

	/**
	 * 核销员管理页面
	 */
	public function hexiao_manage()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$member_id = $weprogram_token['member_id'];

		$salesmember_id = D('Home/Salesroom')->get_salesmember_id_by_member_id($member_id);

		if( $salesmember_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是核销员' ) );
			die();
		}

		$data = array();

		//核销员门店信息
		$salesroom_list = D('Home/Salesroom')->get_salesrooms_by_smember_id($salesmember_id);
		$data['salesroom_list'] = $salesroom_list;
		//核销员今日核销记录
		$saleshexiao_record_list = D('Home/Salesroom')->get_today_hexiao_record_by_smember_id($salesmember_id);
		//核销员信息
		$salesroom_member = M('eaterplanet_ecommerce_salesroom_member')->where( array('id' => $salesmember_id) )->find();
		if(!empty($salesroom_member['last_salesroom_id'])){
			$salesroom = M('eaterplanet_ecommerce_salesroom')->where( array('id' => $salesroom_member['last_salesroom_id']) )->find();
			$salesroom_member['last_salesroom'] = $salesroom;
		}
		$data['salesroom_member'] = $salesroom_member;
		$data['saleshexiao_record_list'] = $saleshexiao_record_list;
		$data['today_saleshexiao_count'] = count($saleshexiao_record_list);
		echo json_encode( array('code' => 0, 'data' => $data) );
	}
	/**
	 * 核销订单页面
	 */
	public function hexiao_order_info(){
		$_GPC = I('request.');
		$token =  $_GPC['token'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$member_id = $weprogram_token['member_id'];
		$salesmember_id = D('Home/Salesroom')->get_salesmember_id_by_member_id($member_id);
		if( $salesmember_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是核销员' ) );
			die();
		}
		$hexiao_volume_code = $_GPC['hexiao_volume_code'];
		if( empty($hexiao_volume_code) )
		{
			echo json_encode( array('code' => 3, 'msg' => '核销码不存在' ) );
			die();
		}
		$hexiao_info = D('Home/Salesroom')->get_hexiao_order_by_code($hexiao_volume_code,$salesmember_id);
		if($hexiao_info['is_exist'] == 0){
			echo json_encode( array('code' => 3, 'msg' => '请输入正确的券码' ) );
			die();
		}
		if($hexiao_info['is_exist'] == 2){
			echo json_encode( array('code' => 3, 'msg' => '核销员无核销权限' ) );
			die();
		}
		echo json_encode( array('code' => 0, 'data' => $hexiao_info) );
	}

	/**
	 * 核销整个订单
	 */
	public function all_hx_order(){
		$_GPC = I('request.');
		$token =  $_GPC['token'];
		$order_id = $_GPC['order_id'];
		$salesroom_id = $_GPC['salesroom_id'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$member_id = $weprogram_token['member_id'];
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();
		if( empty($order_info) )
		{
			echo json_encode( array('code' => 3, 'msg' => '订单信息不存在' ) );
			die();
		}
		$salesmember_id = D('Home/Salesroom')->get_salesmember_id_by_member_id($member_id);
		if( $salesmember_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是核销员' ) );
			die();
		}
		$hx_result = D('Home/Salesroom')->hexiao_all_orders($order_id,$salesmember_id,$salesroom_id);
		if($hx_result['hx_goods_count'] > 0){
			echo json_encode( array('code' => 0, 'data' => $hx_result) );
		}else{
			echo json_encode( array('code' => 3,  'msg' => '无核销商品') );
		}
	}

	/**
	 * 核销商品（按订单核销的商品）
	 */
	public function hx_order_goods(){
		$_GPC = I('request.');
		$token =  $_GPC['token'];
		$hexiao_id = $_GPC['hexiao_id'];
		$salesroom_id = $_GPC['salesroom_id'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$member_id = $weprogram_token['member_id'];
		$saleshexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('id' => $hexiao_id) )->find();
		if( empty($saleshexiao_info) )
		{
			echo json_encode( array('code' => 3, 'msg' => '订单核销信息不存在' ) );
			die();
		}
		$salesmember_id = D('Home/Salesroom')->get_salesmember_id_by_member_id($member_id);
		if( $salesmember_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是核销员' ) );
			die();
		}
		$hx_result = D('Home/Salesroom')->saleshexiao_order_goods($saleshexiao_info,$salesmember_id,$salesroom_id, 0);
		if($hx_result == 1){
			echo json_encode( array('code' => 0) );
		}else if($hx_result == 0){
			echo json_encode( array('code' => 3,  'msg' => '核销商品失败') );
		}else{
			echo json_encode( array('code' => 3,  'msg' => '无核销权限') );
		}
	}

	/**
	 * 按次核销点击弹窗（按次核销商品）
	 */
	public function get_hxgoods_bytimes(){
		$_GPC = I('request.');
		$token =  $_GPC['token'];
		$hexiao_id = $_GPC['hexiao_id'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$member_id = $weprogram_token['member_id'];
		$saleshexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('id' => $hexiao_id) )->find();
		if( empty($saleshexiao_info) )
		{
			echo json_encode( array('code' => 3, 'msg' => '订单核销信息不存在' ) );
			die();
		}
		$salesmember_id = D('Home/Salesroom')->get_salesmember_id_by_member_id($member_id);
		if( $salesmember_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是核销员' ) );
			die();
		}
		$hx_info = D('Home/Salesroom')->get_hxgoods_bytimes($saleshexiao_info,$salesmember_id);
		if($hx_info['status'] == -1){
			echo json_encode( array('code' => 3, 'msg' => '无核销权限' ) );
			die();
		}
		echo json_encode( array('code' => 0, 'data' => $hx_info) );
	}

	/**
	 * 核销商品（按次数核销的商品）
	 */
	public function hx_order_goods_bytimes(){
		$_GPC = I('request.');
		$token =  $_GPC['token'];
		//核销商品订单表id
		$hexiao_id = $_GPC['hexiao_id'];
		//门店id
		$salesroom_id = $_GPC['salesroom_id'];
		//核销次数
		$hx_times = $_GPC['hx_times'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		if(  empty($hx_times) || intval($hx_times) < 1 )
		{
			echo json_encode( array('code' => 3, 'msg' => '核销次数错误' ) );
			die();
		}
		$member_id = $weprogram_token['member_id'];
		$saleshexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('id' => $hexiao_id) )->find();
		if( empty($saleshexiao_info) )
		{
			echo json_encode( array('code' => 3, 'msg' => '订单核销信息不存在' ) );
			die();
		}
		$salesmember_id = D('Home/Salesroom')->get_salesmember_id_by_member_id($member_id);
		if( $salesmember_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是核销员' ) );
			die();
		}
		$hx_result = D('Home/Salesroom')->saleshexiao_order_goods($saleshexiao_info,$salesmember_id,$salesroom_id,$hx_times);
		if($hx_result == 1){
			echo json_encode( array('code' => 0) );
		}else if($hx_result == 0){
			echo json_encode( array('code' => 3,  'msg' => '核销商品失败') );
		}else{
			echo json_encode( array('code' => 3,  'msg' => '无核销权限') );
		}
	}

	/**
	 * 核销员切换门店
	 */
	public function hexiao_change_salesroom(){
		$_GPC = I('request.');
		$token =  $_GPC['token'];
		$salesroom_id = $_GPC['salesroom_id'];
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$member_id = $weprogram_token['member_id'];
		$salesmember_id = D('Home/Salesroom')->get_salesmember_id_by_member_id($member_id);
		if( $salesmember_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是核销员' ) );
			die();
		}
		M('eaterplanet_ecommerce_salesroom_member')->where( array('id' => $salesmember_id ) )->save( array('last_salesroom_id' => $salesroom_id) );
		echo json_encode( array('code' => 0) );
	}
}
