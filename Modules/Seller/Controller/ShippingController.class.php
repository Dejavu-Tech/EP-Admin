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
namespace Seller\Controller;

class ShippingController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
	}

	public function templates()
	{
		$_GPC = I('request.');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '  ';

		$this->gpc = $_GPC;
		if ($_GPC['enabled'] != '') {
			$condition .= ' and enabled=' . intval($_GPC['enabled']);
		}

		if (!empty($_GPC['keyword'])) {
			$condition .= ' and name  like "%'.$_GPC['keyword'].'%" ';
		}

		$list = M()->query('SELECT * FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_shipping WHERE 1 ' .
				$condition . '  ORDER BY sort_order DESC limit ' . (($pindex - 1) * $psize) . ',' . $psize);

		$total = M('eaterplanet_ecommerce_shipping')->where( "1 ". $condition )->count();

		$pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;

		$this->display();
	}

	public function setdefault()
	{
		$_GPC = I('request.');


		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		if ($_GPC['isdefault'] == 1) {
			M('eaterplanet_ecommerce_shipping')->where( 1 )->save( array('isdefault' => 0) );
		}

		$items = M('eaterplanet_ecommerce_shipping')->field('id,name')->where( 'id in( ' . $id . ' )' )->select();

		foreach ($items as $item) {

			M('eaterplanet_ecommerce_shipping')->where( array('id' => $item['id']) )->save( array('isdefault' => intval($_GPC['isdefault'])) );

		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}

	public function enabled()
	{

		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = M('eaterplanet_ecommerce_shipping')->where( ' id in( ' . $id . ' ) ' )->select();

		foreach ($items as $item) {

			M('eaterplanet_ecommerce_shipping')->where( array('id' => $item['id'])  )->save( array('enabled' => intval($_GPC['enabled'])) );
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}

	public function deleteshipping()
	{

		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = M('eaterplanet_ecommerce_shipping')->field('id,name')->where( 'id in( ' . $id . ' )' )->select();

		foreach ($items as $item) {

			M('eaterplanet_ecommerce_shipping')->where( array('id' => $item['id']) )->delete();

		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}

	public function editshipping()
	{
		$_GPC = I('request.');

		if (IS_POST) {

			$data = array();
			$data['id'] = $_GPC['data']['id'];
			$data['name'] = $_GPC['data']['name'];
			$data['sort_order'] = $_GPC['sort_order'];
			$data['isdefault'] = $_GPC['isdefault'];

			$data['isdefault'] = $_GPC['isdefault'];
			$data['type'] = $_GPC['type'];
			$data['default_firstweight'] = $_GPC['default_firstweight'];
			$data['default_firstprice'] = $_GPC['default_firstprice'];
			$data['default_secondweight'] = $_GPC['default_secondweight'];
			$data['default_secondprice'] = $_GPC['default_secondprice'];
			$data['default_firstnum'] = $_GPC['default_firstnum'];

			$data['default_firstnumprice'] = $_GPC['default_firstnumprice'];
			$data['default_secondnum'] = $_GPC['default_secondnum'];
			$data['default_secondnumprice'] = $_GPC['default_secondnumprice'];
			$data['default_freeprice'] = $_GPC['default_freeprice'];

			$areas = array();
			$randoms = $_GPC['random'];

			$areas = array();
			$randoms = $_GPC['random'];
			//detail[PU0fIwE9052ZqWAb][frist]
			if (is_array($randoms)) {
				foreach ($randoms as $random) {
					$citys = trim($_GPC['citys'][$random]);
					if (empty($citys)) {
						continue;
					}
					if ($_GPC['firstnum'][$random] < 1) {
						$_GPC['firstnum'][$random] = 1;
					}
					if ($_GPC['secondnum'][$random] < 1) {
						$_GPC['secondnum'][$random] = 1;
					}
					$areas[] = array('citys' => $_GPC['citys'][$random], 'citys_code' => $_GPC['citys_code'][$random], 'frist' => $_GPC['detail'][$random]['frist'], 'frist_price' => max(0, $_GPC['detail'][$random]['frist_price']), 'second' => $_GPC['detail'][$random]['second'],'second_price' => $_GPC['detail'][$random]['second_price'] );
				}
			}

			$data['areas'] = $areas;

			D('Seller/Shipping')->update($data);

			show_json(1, array('url' => U('shipping/templates')));
		}
		$id = intval($_GPC['id']);

		$this->id =$id;

		$item = M('eaterplanet_ecommerce_shipping')->where( array('id' => $id) )->find();

		$this->item = $item;

		if (!empty($item)) {
			$dispatch_areas = unserialize($item['areas']);

			$this->dispatch_areas = $dispatch_areas;
		}
		//getAreas

		$areas = D('Seller/Area')->getAreas();

		$this->areas = $areas;

		$this->display('Shipping/addshipping');
	}
	public function addshipping()
	{

		$_GPC = I('request.');

		//show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));


		if (IS_POST) {

			$data = array();
			$data['id'] = $_GPC['data']['id'];
			$data['name'] = $_GPC['data']['name'];
			$data['sort_order'] = $_GPC['sort_order'];
			$data['isdefault'] = $_GPC['isdefault'];

			$data['isdefault'] = $_GPC['isdefault'];
			$data['type'] = $_GPC['type'];
			$data['default_firstweight'] = $_GPC['default_firstweight'];
			$data['default_firstprice'] = $_GPC['default_firstprice'];
			$data['default_secondweight'] = $_GPC['default_secondweight'];
			$data['default_secondprice'] = $_GPC['default_secondprice'];
			$data['default_firstnum'] = $_GPC['default_firstnum'];

			$data['default_firstnumprice'] = $_GPC['default_firstnumprice'];
			$data['default_secondnum'] = $_GPC['default_secondnum'];
			$data['default_secondnumprice'] = $_GPC['default_secondnumprice'];
			$data['default_freeprice'] = $_GPC['default_freeprice'];

			$areas = array();
			$randoms = $_GPC['random'];

			$areas = array();
			$randoms = $_GPC['random'];
			if (is_array($randoms)) {
				foreach ($randoms as $random) {
					$citys = trim($_GPC['citys'][$random]);
					if (empty($citys)) {
						continue;
					}
					if ($_GPC['firstnum'][$random] < 1) {
						$_GPC['firstnum'][$random] = 1;
					}
					if ($_GPC['secondnum'][$random] < 1) {
						$_GPC['secondnum'][$random] = 1;
					}
					//$areas[] = array('citys' => $_GPC['citys'][$random], 'citys_code' => $_GPC['citys_code'][$random], 'firstprice' => $_GPC['firstprice'][$random], 'firstweight' => max(0, $_GPC['firstweight'][$random]), 'secondprice' => $_GPC['secondprice'][$random], 'secondweight' => $_GPC['secondweight'][$random] <= 0 ? 1000 : $_GPC['secondweight'][$random], 'firstnumprice' => $_GPC['firstnumprice'][$random], 'firstnum' => $_GPC['firstnum'][$random], 'secondnumprice' => $_GPC['secondnumprice'][$random], 'secondnum' => $_GPC['secondnum'][$random], 'freeprice' => $_GPC['freeprice'][$random]);
					$areas[] = array('citys' => $_GPC['citys'][$random], 'citys_code' => $_GPC['citys_code'][$random], 'frist' => $_GPC['detail'][$random]['frist'], 'frist_price' => max(0, $_GPC['detail'][$random]['frist_price']), 'second' => $_GPC['detail'][$random]['second'],'second_price' => $_GPC['detail'][$random]['second_price'] );

				}
			}

			$data['areas'] = $areas;

			D('Seller/Shipping')->update($data);

			show_json(1, array('url' => U('shipping/templates')));
		}

		$areas = D('Seller/Area')->getAreas();

		$this->areas = $areas;

		$this->display();
	}

	public function tpl()
	{
		global $_W;
		global $_GPC;


		$random = random(16);
		$this->random = $random;

		$contents = $this->fetch('Shipping:tpl');
		exit(json_encode(array('random' => $random, 'html' => $contents)));
	}

	public function editexpress()
	{

		$_GPC = I('request.');

		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$item = M('eaterplanet_ecommerce_express')->field('id,name,simplecode')->where( array('id' => $id ) )->find();

			$this->item = $item;
		}

		if (IS_POST) {

			$data = $_GPC['data'];

			D('Seller/Express')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
		}

		$this->display('Express/addexpress');
	}

	public function delexpress()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = M('eaterplanet_ecommerce_express')->field('id,name')->where( 'id in( ' . $id . ' )' )->select();

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('eaterplanet_ecommerce_express')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}


}
?>
