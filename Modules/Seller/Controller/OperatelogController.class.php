<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://www.eaterplanet.com/
 * @copyright Copyright (c) 2019-2022 Dejavu.Tech.
 * @license   https://www.eaterplanet.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */

namespace Seller\Controller;

class OperatelogController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='系统日志';
		$this->breadcrumb2='系统日志列表';
		$this->sellerid = SELLERUID;
	}
	/**
	 * 系统日志列表
	 */
	public function index()
	{
		$_GPC = I('request.');
		$this->gpc = $_GPC;

		$userID = SELLERUID;
		$condition = ' 1 ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
        $time = $_GPC['time'];

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( operation_seller_name like "%'.$_GPC['keyword'].'%" or content like "%'.$_GPC['keyword'].'%" ) ';
		}
		if (isset($_GPC['operation_type']) && trim($_GPC['operation_type']) != '') {
			$_GPC['operation_type'] = trim($_GPC['operation_type']);
			$condition .= ' and operation_type = "'. $_GPC['operation_type'].'" ';
		}

		if($_GPC['searchtime'] == 'create_time'){
			if(!empty($time['start'])){
				$condition .= " and addtime <= ".strtotime($time['end'])." and addtime >=".strtotime($time['start'])." ";
				$this->starttime = strtotime($time['start']);
				$this->endtime = strtotime($time['end']);
			}else{
				$this->starttime = strtotime(date('Y-m-d').' 00:00');
				$this->endtime = strtotime(date('Y-m-d').' 23:59');
			}
		}else{
			$this->starttime = strtotime(date('Y-m-d').' 00:00');
			$this->endtime = strtotime(date('Y-m-d').' 23:59');
		}

		$label = M('eaterplanet_ecommerce_systemoperation_log')->where( $condition )->order(' id desc ')->limit( (($pindex - 1) * $psize) . ',' . $psize )->select();
		foreach($label as $key => $var){
			//$label[$key]['city'] = D('Seller/Operatelog')->get_ip_city( $var['ip'] );

		}

		$total = M('eaterplanet_ecommerce_systemoperation_log')->where( $condition )->count();

		$pager = pagination2($total, $pindex, $psize);
		$time = time();

		$this->label = $label;
		$this->pager = $pager;

		$this->display("Operatelog/index");
	}

	/**
	 * 删除系统日志
	 */
	public function delete_operatelog(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);
		if (empty($id)) {
			$id = $_GPC['ids'];
		}
		if( is_array($id) )
		{
			$items = M('eaterplanet_ecommerce_systemoperation_log')->field('id')->where( array('id' => array('in', $id)) )->select();
		}else{
			$items = M('eaterplanet_ecommerce_systemoperation_log')->field('id')->where( array('id' =>$id ) )->select();
		}
		if (empty($item)) {
			$item = array();
		}
		foreach ($items as $item) {
			M('eaterplanet_ecommerce_systemoperation_log')->where( array('id' => $item['id']) )->delete();
		}
		show_json(1, array('url' => U('operatelog/index')));
	}

}
?>
