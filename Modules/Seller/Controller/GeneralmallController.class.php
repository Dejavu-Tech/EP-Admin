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

class GeneralmallController extends CommonController
{

    protected function _initialize()
    {
        parent::_initialize();
    }
//202011fix
    public function slider()
    {
        $_GPC = I('request.');
        $condition = '  type="slider" ';
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;


        if (!empty($_GPC['keyword'])) {
	        $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and advname like "%'.trim($_GPC['keyword']).'%"';
        }

        if (isset($_GPC['enabled']) && $_GPC['enabled'] >= 0) {
            $_GPC['enabled'] = trim($_GPC['enabled']);
            $condition .= ' and enabled = ' . $_GPC['enabled'];
        } else {
            $_GPC['enabled'] = -1;
        }

        $list = M()->query('SELECT id,advname,thumb,link,type,displayorder,enabled FROM ' .
            C('DB_PREFIX') . "eaterplanet_ecommerce_generalmall_adv  \r\n
		WHERE  " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);

        $total = M('eaterplanet_ecommerce_generalmall_adv')->where($condition)->count();

        $pager = pagination2($total, $pindex, $psize);

        $this->list  = $list;
        $this->pager = $pager;
        $this->_GPC =$_GPC;
        $this->display();
    }

    public function addslider()
    {

        $id = I('request.id');
        if (!empty($id)) {
            $item       = M('eaterplanet_ecommerce_generalmall_adv')->where(array('id' => $id))->find();
            $this->item = $item;
        }

        if (IS_POST) {
            $data = I('request.data');

            D('Seller/Generalmall')->update($data);
            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }

        $this->display();
    }

    public function changeslider()
    {
        $id = I('request.id');

        //ids
        if (empty($id)) {
            $ids = I('request.ids');
            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = I('request.type');
        $value = I('request.value');

        if (!(in_array($type, array('enabled', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }

        $items = M('eaterplanet_ecommerce_generalmall_adv')->where(array('id' => array('in', $id)))->select();

        foreach ($items as $item) {
            M('eaterplanet_ecommerce_generalmall_adv')->where(array('id' => $item['id']))->save(array($type => $value));
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }

    public function delete()
    {
        $id = I('request.id');
        //ids
        if (empty($id)) {
            $ids = I('request.ids');
            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $items = M('eaterplanet_ecommerce_generalmall_adv')->where(array('id' => array('in', $id)))->select();

        foreach ($items as $item) {
            M('eaterplanet_ecommerce_generalmall_adv')->where(array('id' => $item['id']))->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }
//202202011fix
    public function navigat()
    {
        $_GPC = I('request.');

        $pindex = max(1, intval($_GPC['page']));
        $psize  = 20;

        $condition = '';

        if (!empty($_GPC['keyword'])) {
	        $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and advname like "%'.trim($_GPC['keyword']).'%"';
        }

        if (isset($_GPC['enabled']) && $_GPC['enabled'] >= 0) {
            $_GPC['enabled'] = trim($_GPC['enabled']);
            $condition .= ' and enabled = ' . $_GPC['enabled'];
        } else {
            $_GPC['enabled'] = -1;
        }

        $list = M()->query('SELECT * FROM ' . C('DB_PREFIX') . "eaterplanet_ecommerce_generalmall_navigat
				WHERE 1   " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);

        $total = M('eaterplanet_ecommerce_generalmall_navigat')->where('1 ' . $condition)->count();

        $pager = pagination2($total, $pindex, $psize);

        $this->list  = $list;
        $this->pager = $pager;
        $this->_GPC =$_GPC;
        $this->display();
    }

    public function addnavigat()
    {
        $_GPC = I('request.');

        $id = intval($_GPC['id']);

        $category       = D('Seller/GoodsCategory')->getFullCategory(false, true);
        $this->category = $category;

        if (!empty($id)) {
            $item       = M('eaterplanet_ecommerce_generalmall_navigat')->where(array('id' => $id))->find();
            $this->item = $item;
        }

        if (IS_POST) {
            $data = $_GPC['data'];
            if ($data['type'] == 3 || $data['type'] == 4) {
                $data['link'] = $data['cid'];
            }
            D('Seller/Generalmall')->navigat_update($data);
            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
        $this->display();
    }

    public function changenavigat()
    {
        $_GPC = I('request.');
        $id   = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = trim($_GPC['type']);
        $value = trim($_GPC['value']);

        if (!(in_array($type, array('enabled', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }

        $items = M('eaterplanet_ecommerce_generalmall_navigat')->field('id')->where('id in( ' . $id . ' ) ')->select();

        foreach ($items as $item) {
            M('eaterplanet_ecommerce_generalmall_navigat')->where(array('id' => $item['id']))->save(array($type => $value));
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

    public function deletenavigat()
    {

        $_GPC = I('request.');
        $id   = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $items = M('eaterplanet_ecommerce_generalmall_navigat')->field('id')->where('id in( ' . $id . ' )')->select();

        foreach ($items as $item) {
            M('eaterplanet_ecommerce_generalmall_navigat')->where(array('id' => $item['id']))->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }

}
