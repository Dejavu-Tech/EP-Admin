<?php

namespace Seller\Controller;

class LogisticsController extends CommonController{

	protected function _initialize(){
		parent::_initialize();

		//'pinjie' => '拼团介绍',
	}
	/**
	 * 电子面单列表
	 */
	public function index(){
		$_GPC = I('request.');

		$this->gpc = $_GPC;

		$condition = ' 1 ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and express_name like "%'.$_GPC['keyword'].'%" ';
		}

		$label = M('eaterplanet_ecommerce_kdniao_list')->where( $condition )->order(' id desc ')->limit( (($pindex - 1) * $psize) . ',' . $psize )->select();

		$total = M('eaterplanet_ecommerce_kdniao_list')->where( $condition )->count();

		$pager = pagination2($total, $pindex, $psize);

		$this->label = $label;
		$this->pager = $pager;

		$this->display("kdniao_index");
	}

	/**
	 * 添加快递
	 */
	public function add_kdniao(){
		$_GPC = I('request.');

		if (IS_POST) {
			$data = $_GPC['data'];

			if (empty($data['express_code'])) {
				show_json(0, array('message' => '快递公司不能为空'));
			}
			$kdn_info = M('eaterplanet_ecommerce_kdniao_list')->where( array('express_code' => $data['express_code'] ) )->find();
			if(!empty($kdn_info)){
				show_json(0, array('message' => '快递公司电子面单已配置'));
			}
			if (empty($data['sender_name'])) {
				show_json(0, array('message' => '发件人名称不能为空'));
			}
			if (empty($data['sender_tel'])) {
				show_json(0, array('message' => '发件人电话不能为空'));
			}
			if (empty($data['sender_province_name']) || $data['sender_province_name'] == '请选择省份') {
				show_json(0, array('message' => '发件人省市区不能为空'));
			}
			if (empty($data['sender_city_name']) || $data['sender_city_name'] == '请选择城市') {
				show_json(0, array('message' => '发件人省市区不能为空'));
			}
			if (empty($data['sender_area_name']) || $data['sender_area_name'] == '请选择区域') {
				show_json(0, array('message' => '发件人省市区不能为空'));
			}
			if (empty($data['sender_address'])) {
				show_json(0, array('message' => '发件人详细地址不能为空'));
			}
			$express_info = M('eaterplanet_ecommerce_express')->where( array('simplecode' => $data['express_code'] ) )->find();
			$data['express_name'] = $express_info['name'];
			D('Seller/Kdniao')->update($data);
			show_json(1, array('url' => U('logistics/index')));
		}
		$this->express_list = D('Seller/Express')->load_kdn_express();
		$this->display("kdniao_add");
	}

	/**
	 * 添加快递
	 */
	public function edit_kdniao(){
		$_GPC = I('request.');

		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$item = M('eaterplanet_ecommerce_kdniao_list')->where( array('id' =>$id ) )->find();
			$this->item = $item;
		}

		if (IS_POST) {
			$data = $_GPC['data'];

			if (empty($data['express_code'])) {
				show_json(0, array('message' => '快递公司不能为空'));
			}
			$kdn_info = M('eaterplanet_ecommerce_kdniao_list')->where("express_code='".$data['express_code']."' and id <> ".$id)->find();
			if(!empty($kdn_info)){
				show_json(0, array('message' => '快递公司电子面单已配置'));
			}
			if (empty($data['sender_name'])) {
				show_json(0, array('message' => '发件人名称不能为空'));
			}
			if (empty($data['sender_tel'])) {
				show_json(0, array('message' => '发件人电话不能为空'));
			}
			if (empty($data['sender_province_name']) || $data['sender_province_name'] == '请选择省份') {
				show_json(0, array('message' => '发件人省市区不能为空'));
			}
			if (empty($data['sender_city_name']) || $data['sender_city_name'] == '请选择城市') {
				show_json(0, array('message' => '发件人省市区不能为空'));
			}
			if (empty($data['sender_area_name']) || $data['sender_area_name'] == '请选择区域') {
				show_json(0, array('message' => '发件人省市区不能为空'));
			}
			if (empty($data['sender_address'])) {
				show_json(0, array('message' => '发件人详细地址不能为空'));
			}
			$express_info = M('eaterplanet_ecommerce_express')->where( array('simplecode' => $data['express_code'] ) )->find();
			$data['express_name'] = $express_info['name'];
			D('Seller/Kdniao')->update($data);
			show_json(1, array('url' => U('logistics/index')));
		}
		$this->express_list = D('Seller/Express')->load_kdn_express();
		$this->display("kdniao_add");
	}

	public function select_template(){
		$_GPC = I('request.');
		$code = $_GPC['code'];
		$list = M('eaterplanet_ecommerce_kdniao_template')->where(array('express_code'=>$code))->field('template_name,template_spec,template_size')->select();
		$result = array();
		if(empty($list)){
			$result['code'] = 0;
		}else{
			$result['code'] = 1;
			$result['list'] = $list;
		}
		echo json_encode($result);
	}

	public function delete_kdniao(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);
		if (empty($id)) {
			$id = $_GPC['ids'];
		}
		if( is_array($id) )
		{
			$items = M('eaterplanet_ecommerce_kdniao_list')->field('id')->where( array('id' => array('in', $id)) )->select();
		}else{
			$items = M('eaterplanet_ecommerce_kdniao_list')->field('id')->where( array('id' =>$id ) )->select();
		}
		if (empty($item)) {
			$item = array();
		}
		foreach ($items as $item) {
			M('eaterplanet_ecommerce_kdniao_list')->where( array('id' => $item['id']) )->delete();
		}
		show_json(1, array('url' => U('logistics/index')));
	}

	/**
	 * 电子面单设置
	 */
	public function inface()
	{
		$_GPC = I('request.');

		if (IS_POST) {

			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			$data['kdniao_id'] = trim($data['kdniao_id']);
			$data['kdniao_api_key'] = trim($data['kdniao_api_key']);
			$data['kdniao_status'] = trim($data['kdniao_status']);
			$data['kdniao_freestatus'] = trim($data['kdniao_freestatus']);
			//寄件人信息设置
			$data['kdn_sender_name'] = trim($data['kdn_sender_name']);
			$data['kdn_sender_mobile'] = trim($data['kdn_sender_mobile']);
			$data['kdn_province_id'] = trim($data['province_id']);
			$data['kdn_city_id'] = trim($data['city_id']);
			$data['kdn_area_id'] = trim($data['area_id']);
			$data['kdn_sender_address'] = trim($data['kdn_sender_address']);
			$data['kdn_sender_company'] = trim($data['kdn_sender_company']);
			$data['kdn_sender_postcode'] = trim($data['kdn_sender_postcode']);
			if($data['kdniao_status'] == 1){//开启快递鸟判断参数
				if(empty($data['kdniao_id'])){
					show_json(0,  array('msg' => '快递鸟商户ID不能为空' ) );
				}
				if(empty($data['kdniao_api_key'])){
					show_json(0,  array('msg' => '快递鸟API KEY不能为空' ) );
				}
				if(empty($data['kdn_sender_name'])){
					show_json(0,  array('msg' => '寄件人不能为空' ) );
				}
				if(empty($data['kdn_sender_mobile'])){
					show_json(0,  array('msg' => '寄件人联系电话不能为空' ) );
				}
				if(empty($data['kdn_province_id']) || $data['kdn_province_id']== '请选择省份'){
					show_json(0,  array('msg' => '寄件人省份不能为空' ) );
				}
				if(empty($data['kdn_city_id']) || $data['kdn_province_id']== '请选择城市'){
					show_json(0,  array('msg' => '寄件人城市不能为空' ) );
				}
				if(empty($data['kdn_area_id']) || $data['kdn_province_id']== '请选择区域'){
					show_json(0,  array('msg' => '寄件人区域不能为空' ) );
				}
				if(empty($data['kdn_sender_address'])){
					show_json(0,  array('msg' => '寄件人详细地址不能为空' ) );
				}
			}

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();
		$this->data = $data;

		$this->display();
	}


}

?>
