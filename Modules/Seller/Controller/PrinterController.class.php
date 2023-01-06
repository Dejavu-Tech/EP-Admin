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

class PrinterController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='打印机设置';
		$this->breadcrumb2='打印机列表';
		$this->sellerid = SELLERUID;
	}

	/**
	 * 打印机列表
	 */
	public function index()
	{
		$_GPC = I('request.');

		$this->gpc = $_GPC;

		$condition = ' 1 ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and printer_name like "%'.$_GPC['keyword'].'%" ';
		}

		$label = M('eaterplanet_ecommerce_printer')->where( $condition )->order(' id desc ')->limit( (($pindex - 1) * $psize) . ',' . $psize )->select();

		$total = M('eaterplanet_ecommerce_printer')->where( $condition )->count();

		$pager = pagination2($total, $pindex, $psize);

		$this->label = $label;
		$this->pager = $pager;

		$this->display("printer_index");
	}

	/**
	 * 添加打印机
	 */
	public function add_printer(){
		$_GPC = I('request.');

		if (IS_POST) {
			$data = $_GPC['data'];

			if (empty($data['printer_name'])) {
				show_json(0, array('message' => '打印机名称不能为空'));
			}
			if (empty($data['printer_type'])) {
				show_json(0, array('message' => '打印机类型不能为空'));
			}
			if($data['printer_type'] == 1){//飞鹅打印机
				if (empty($data['printer_sn'])) {
					show_json(0, array('message' => 'sn不能为空'));
				}
				if (empty($data['printer_key'])) {
					show_json(0, array('message' => 'key不能为空'));
				}
			}else if($data['printer_type'] == 2){//易联云打印机
				if (empty($data['api_id'])) {
					show_json(0, array('message' => '应用id不能为空'));
				}
				if (empty($data['api_key'])) {
					show_json(0, array('message' => '应用密钥key不能为空'));
				}
				if (empty($data['printer_yly_sn'])) {
					show_json(0, array('message' => '打印机终端号不能为空'));
				}
				if (empty($data['printer_yly_key'])) {
					show_json(0, array('message' => '终端密钥不能为空'));
				}
			}
			if (!empty($data['printer_num']) && !is_numeric($data['printer_num'])) {
				show_json(0, array('message' => '打印联数必须为数字'));
			}
			D('Seller/Printer')->update($data);
			show_json(1, array('url' => U('printer/index')));
		}

		$this->display("printer_add");
	}

	/**
	 * 更新打印机状态
	 */
	public function printer_status(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = $_GPC['ids'];
		}
		$is_printer_list = M('eaterplanet_ecommerce_config')->where( array('name' => 'is_printer_list') )->find();
		$printer_list = explode(',',$is_printer_list['value']);
		if( is_array($id) )
		{
			if($_GPC['status'] == 0){
				$all_list = array_merge($printer_list,$id);
				if(count($all_list) != count(array_unique($all_list))){
					show_json(0, array('message' => '请先取消默认打印机设置在进行操作'));
				}
			}
			$items = M('eaterplanet_ecommerce_printer')->field('id')->where( array('id' => array('in', $id)) )->select();
		}else{
			if($_GPC['status'] == 0) {
				if (in_array($id, $printer_list)) {
					show_json(0, array('message' => '请先取消默认打印机设置在进行操作'));
				}
			}
			$items = M('eaterplanet_ecommerce_printer')->field('id')->where( array('id' =>$id ) )->select();
		}
		if (empty($item)) {
			$item = array();
		}
		foreach ($items as $item) {
			M('eaterplanet_ecommerce_printer')->where( array('id' => $item['id']) )->save( array('status' => intval($_GPC['status'])) );
		}
		show_json(1, array('url' => U('printer/index')));
	}

	/**
	 * 删除打印机
	 */
	public function delete_printer(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);
		if (empty($id)) {
			$id = $_GPC['ids'];
		}
		$is_printer_list = M('eaterplanet_ecommerce_config')->where( array('name' => 'is_printer_list') )->find();
		$printer_list = explode(',',$is_printer_list['value']);
		if( is_array($id) )
		{
			$all_list = array_merge($printer_list,$id);
			if(count($all_list) != count(array_unique($all_list))){
				show_json(0, array('message' => '请先取消默认打印机设置在进行操作'));
			}
			$items = M('eaterplanet_ecommerce_printer')->field('id')->where( array('id' => array('in', $id)) )->select();
		}else{
			if(in_array($id,$printer_list)){
				show_json(0, array('message' => '请先取消默认打印机设置在进行操作'));
			}
			$items = M('eaterplanet_ecommerce_printer')->field('id')->where( array('id' =>$id ) )->select();
		}
		if (empty($item)) {
			$item = array();
		}
		foreach ($items as $item) {
			M('eaterplanet_ecommerce_printer')->where( array('id' => $item['id']) )->delete();
		}
		show_json(1, array('url' => U('printer/index')));
	}

	/**
	 * 编辑打印机
	 */
	public function edit_priner(){
		$_GPC = I('request.');

		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$item = M('eaterplanet_ecommerce_printer')->where( array('id' =>$id ) )->find();
			$this->item = $item;
		}

		if (IS_POST) {
			$data = $_GPC['data'];

			if (empty($data['printer_name'])) {
				show_json(0, array('message' => '打印机名称不能为空'));
			}
			if (empty($data['printer_type'])) {
				show_json(0, array('message' => '打印机类型不能为空'));
			}
			if($data['printer_type'] == 1){//飞鹅打印机
				if (empty($data['printer_sn'])) {
					show_json(0, array('message' => 'sn不能为空'));
				}
				if (empty($data['printer_key'])) {
					show_json(0, array('message' => 'key不能为空'));
				}
			}else if($data['printer_type'] == 2){//易联云打印机
				if (empty($data['api_id'])) {
					show_json(0, array('message' => '应用id不能为空'));
				}
				if (empty($data['api_key'])) {
					show_json(0, array('message' => '应用密钥key不能为空'));
				}
				if (empty($data['printer_yly_sn'])) {
					show_json(0, array('message' => '打印机终端号不能为空'));
				}
				if (empty($data['printer_yly_key'])) {
					show_json(0, array('message' => '终端密钥不能为空'));
				}
			}
			if (!empty($data['printer_num']) && !is_numeric($data['printer_num'])) {
				show_json(0, array('message' => '打印联数必须为数字'));
			}
			D('Seller/Printer')->update($data);
			show_json(1, array('url' => U('printer/index')));
		}

		$this->display("printer_add");
	}

	/**
	 * 打印机设置
	 */
	public function config(){
		$_GPC = I('request.');
		if (IS_POST) {
			$data = $_GPC['data'];
			$config_data = array();
			$config_data['is_print_cancleorder'] = isset($data['is_print_cancleorder']) ? $data['is_print_cancleorder'] : 0;
			$config_data['is_print_admin_cancleorder'] = isset($data['is_print_admin_cancleorder']) ? $data['is_print_admin_cancleorder'] : 0;
			$config_data['is_print_dansupply_order'] = isset($data['is_print_dansupply_order']) ? $data['is_print_dansupply_order'] : 0;
			$config_data['is_print_member_note'] = isset($data['is_print_member_note']) ? $data['is_print_member_note'] : 0;
			$config_data['is_print_order_note'] = isset($data['is_print_order_note']) ? $data['is_print_order_note'] : 0;
			$config_data['is_printer_list'] = isset($_GPC['is_printer_list']) ? $_GPC['is_printer_list'] : '';

			$config_data['open_feier_print'] = isset($data['open_feier_print']) ? $data['open_feier_print'] : 0;
			if($config_data['open_feier_print'] == 1){
				$feier_print_sn = isset($data['feier_print_sn']) ? $data['feier_print_sn']:'';
				$feier_print_key = isset($data['feier_print_key']) ? $data['feier_print_key']:'';
				$feier_print_lian = isset($data['feier_print_lian']) ? $data['feier_print_lian'] : 0;
				$config_data['feier_print_sn'] = $feier_print_sn;
				$config_data['feier_print_key'] = $feier_print_key;
				$config_data['feier_print_lian'] = $feier_print_lian;

				$feier_print_sn_old_arr = M('eaterplanet_ecommerce_config')->where( array('name' => 'feier_print_sn') )->find();

				$feier_print_sn_old = $feier_print_sn_old_arr['value'];

				$feier_print_key_old_arr = M('eaterplanet_ecommerce_config')->where( array('name' => 'feier_print_key') )->find();

				$feier_print_key_old = $feier_print_key_old_arr['value'];

				if($feier_print_sn_old != $feier_print_sn || $feier_print_key_old != $feier_print_key)
				{
					//开始添加打印机
					//printaction
					$print_model = D('Seller/Printaction');
					$snlist = "{$feier_print_sn}#{$feier_print_key}";
					$print_model->addprinter($snlist);
				}
			}else if($config_data['open_feier_print'] == 2){
				$yilian_machine_code = isset($data['yilian_machine_code']) ? $data['yilian_machine_code']:'';
				$yilian_msign = isset($data['yilian_msign']) ? $data['yilian_msign']:'';
				$yilian_client_id = isset($data['yilian_client_id']) ? $data['yilian_client_id']:'';
				$yilian_client_key = isset($data['yilian_client_key']) ? $data['yilian_client_key']:'';
				$yilian_print_lian = isset($data['yilian_print_lian']) ? $data['yilian_print_lian']:0;

				$config_data['yilian_machine_code'] = $yilian_machine_code;
				$config_data['yilian_msign'] = $yilian_msign;
				$config_data['yilian_client_id'] = $yilian_client_id;
				$config_data['yilian_client_key'] = $yilian_client_key;
				$config_data['yilian_print_lian'] = $yilian_print_lian;

				$yilian_client_id_old = D('Home/Front')->get_config_by_name('yilian_client_id');
				$yilian_machine_code_old = D('Home/Front')->get_config_by_name('yilian_machine_code');
				$yilian_msign_old = D('Home/Front')->get_config_by_name('yilian_msign');
				if(true || $yilian_client_id != $yilian_client_id_old || $yilian_machine_code_old != $yilian_machine_code || $yilian_msign_old != $yilian_msign)
				{
					//开始添加打印机
					//printaction
					$print_model =  D('Seller/Printaction');
					$print_model->addyilianyunprinter($yilian_client_id,$yilian_client_key,$yilian_machine_code, $yilian_msign );
				}
			}

			D('Seller/Config')->update($config_data);
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}else{
			$data = D('Seller/Config')->get_all_config();
			$this->data = $data;
			if(isset($data['is_printer_list']) && !empty($data['is_printer_list'])){
				$printer_list = M('eaterplanet_ecommerce_printer')->field('id,printer_name')->where( array('id' => array('in',$data['is_printer_list']) ) )->select();
				$this->printer_list = $printer_list;
			}
		}
		$this->display("printer_config");
	}

	/**
	 * 打印机选择
	 */
	//202012fix
	public function query_printer(){
		$_GPC = I('request.');
		$kwd = trim($_GPC['keyword']);
		$params = array();
		$condition = " 1 ";
		if (!empty($kwd)) {
			$condition .= ' AND `printer_name` LIKE "%' . $kwd . '%" ';
		}
		$condition .= ' AND status = 1 ';
		$ds = M('eaterplanet_ecommerce_printer')->field('id as printerid,printer_name')->where( $condition )->select();
		$s_html = "";
		foreach ($ds as &$d) {
			$s_html.= '<tr>';
			$s_html.="  <td>".$d['printer_name']."</td>";
			$s_html.='  <td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link_printers btn-primary btn-sm" data-json=\''.json_encode($d).'\'>选择</a></td>';
			$s_html.="</tr>";
		}

		unset($d);

		if( isset($_GPC['is_ajax']) )
		{
			echo json_encode( array('code' => 0, 'html' =>$s_html ) );
			die();
		}

		$this->ds = $ds;
		$this->_GPC = $_GPC;

		$this->display('printer_mult');
	}

}
?>
