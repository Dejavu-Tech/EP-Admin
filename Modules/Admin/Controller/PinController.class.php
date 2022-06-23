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
namespace Admin\Controller;
use Admin\Model\PinModel;
class PinController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='订单';
			$this->breadcrumb2='拼团管理';
	}
    public function index(){
		$model=new PinModel();

		$filter=I('get.');
		$state = I('get.state', -1);
		//'store_id' => SELLERUID,
		$search=array('state' => $state);

		$data=$model->show_order_page($search);



		$this->state = $state;
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	 public function show_order(){

	 	$this->crumbs='拼团详情';
	    $pin_id = I('get.pin_id');

	    $pin_info = M('pin')->where( array('pin_id' => $pin_id) )->find();
	    if($pin_info['state'] == 0 && $pin_info['end_time'] <time()) {
	        $pin_info['state'] = 2;
	    }

	    $this->pin_info = $pin_info;


	    $sql = "select o.order_num_alias,o.total,o.order_id,o.name,o.telephone,o.shipping_name,o.shipping_tel,o.shipping_city_id,
	 	         o.shipping_country_id,o.shipping_province_id,o.shipping_address,o.date_added,o.order_status_id,
	        og.goods_id,og.name as goods_name,og.goods_images,og.name as goods_name,og.quantity,og.price,og.total as atotal,o.shipping_fare
	 	         from ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og,".C('DB_PREFIX')."pin_order as p
		 	         where  o.order_id = og.order_id and o.order_id = p.order_id and p.pin_id ={$pin_id}";
	    $sql.=' ORDER BY o.order_id desc ';

	    $list = M()->query($sql);
	    foreach($list as $key => $val)
	    {
	        $province_info =  M('area')->where( array('area_id' =>$val['shipping_province_id'] ) )->find();
	        $city_info =  M('area')->where( array('area_id' =>$val['shipping_city_id'] ) )->find();
	        $country_info =  M('area')->where( array('area_id' =>$val['shipping_country_id'] ) )->find();

	        $val['province_name'] = $province_info['area_name'];
	        $val['city_name'] = $city_info['area_name'];
	        $val['area_name'] = $country_info['area_name'];


	        $list[$key] = $val;
	    }

	    $pin_buy_sql = "select count(o.order_id) as count from ".C('DB_PREFIX')."pin_order as p,".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og
	    where p.order_id= o.order_id and p.order_id = og.order_id and p.pin_id = {$pin_id}  and o.order_status_id in(1,2,4,6,7,8,9,10)
	    ";
	    $pin_buy_count_arr = M()->query($pin_buy_sql);

	    $pin_buy_count = $pin_buy_count_arr[0]['count'];


	    $order = current($list);
	    //$goods_info = M('goods')->where( array('goods_id' => $order['goods_id']) )->find();

	    $goods_images=resize($order['goods_images'], 50,50);

	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    $order_id = $hashids->encode($order['order_id']);

	    $config_info = M('config')->where( array('name' => 'SITE_URL') )->find();

	    $pin_url = $config_info['value'].'/index.php?s=/group/info/group_order_id/'.$order_id.'.html';
	    $this->pin_url = $pin_url;

	    $order_status_list = M('order_status')->select();
	    $order_status_arr = array();
	    foreach($order_status_list as $val)
	    {
	        $order_status_arr[$val['order_status_id']] = $val['name'];
	    }

	    $this->order_status_arr = $order_status_arr;
	    $this->list = $list;
	    $this->pin_buy_count = $pin_buy_count;
	    $this->goods_images = $goods_images;
	    $this->order = $order;
	    $this->display('show');
	 }

}
?>
