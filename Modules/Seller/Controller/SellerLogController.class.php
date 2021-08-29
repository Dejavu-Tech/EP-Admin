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
use Admin\Model\StatisticsModel;
class SellerLogController extends CommonController {
   	protected function _initialize(){
   	    parent::_initialize();
   	    
   	}
	
    public function index(){
        $_GPC = I('request.');
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = '  ';

        $this->gpc = $_GPC;
        if ($_GPC['type'] != '') {
            $condition .= ' and l.type =' . intval($_GPC['type']);
        }

        if (!empty($_GPC['s_uname'])) {
            $condition .= ' and s.s_uname like "%'.$_GPC['s_uname'].'%" ';
        }

        $list = M()->query('SELECT l.*,s.s_uname FROM ' . C('DB_PREFIX') . 'seller_log as l
                    left join '.C('DB_PREFIX') .'seller as s on s.s_id = l.s_id
         WHERE 1 ' .
            $condition . '  ORDER BY id DESC limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        foreach($list as &$value){
            if($value['type'] == 1){
                $value['type'] = '登录平台';
            }else if($value['type'] == 0){
                $value['types'] = '退出平台';
            }else if($value['type'] == 2){
                $value['types'] = '订单操作';
            }else if($value['type'] == 3){
                $value['types'] = '商品操作';
            }
        }
         $total = M('seller_log l')->join( C('DB_PREFIX').'seller s on s.s_id = l.s_id ','left')->where( "1 ". $condition )->count();
         $pager = pagination2($total, $pindex, $psize);

        $this->list = $list;
        $this->pager = $pager;

        $this->display();
    }
	
	public function analys ()
	{
		$this->display();
	}
}