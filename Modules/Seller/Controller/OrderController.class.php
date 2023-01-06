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
use Admin\Model\OrderModel;
use Lib\KdApiEOrder;
use Lib\Localtown\EleDistribution;
use Lib\Localtown\Imdada;
use Lib\Localtown\Sfexpress;

class OrderController extends CommonController{

	protected function _initialize(){
		parent::_initialize();

	}

     public function index(){

		$time = I('request.time');

		$s_time_start = I('request.time_start');
		$s_time_end = I('request.time_end');

		//var_dump($s_time,2);die();

		if( isset($s_time_start) && !empty($s_time_start) )
		{
		    $time['start'] = $s_time_start;
		    $time['end']   = $s_time_end;
		}

		$starttime = isset($time['start']) ? strtotime($time['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($time['end']) ? strtotime($time['end']) : strtotime(date('Y-m-d'.' 23:59:59'));



		$this->searchfield = I('request.searchfield','');
		$this->keyword = I('request.keyword','');
		$this->searchtime = I('request.searchtime','');
		$this->delivery = I('request.delivery','');
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->time = $time;

		//重置缓存
		$day_time = strtotime( date('Y-m-d '.'00:00:00') );
		$day_key = 'new_ordernotice_'.$day_time;
		S(  $day_key, array() );

		$order_status_id = I('request.order_status_id',0);

		$order_status_arr = D('Seller/Order')->get_order_status_name();

		$this->order_status_arr = $order_status_arr;

		$is_soli_type = I('request.type', '');// isset($_GPC['type']) && $_GPC['type'] == 'soli' ? 1: 0;

		$is_soli = 0;
		if($is_soli_type == 'soli')
		{
			$is_soli = 1;
		}
		//soli
		$_GPC['is_soli'] = $is_soli;

		$_GET['type'] = 'normal';
		$is_presale_order = 0;
		if( isset($_GET['presale_order']) && $_GET['presale_order'] == 1 )
        {
            $is_presale_order = 1;
        }

		$is_virtualcard_order = 0;
		if( isset($_GET['virtualcard_order']) && $_GET['virtualcard_order'] == 1 )
        {
            $is_virtualcard_order = 1;
        }

		$this->is_soli = $is_soli;
		$this->is_presale_order = $is_presale_order;
		$this->is_virtualcard_order = $is_virtualcard_order;

		$need_data = D('Seller/Order')->load_order_list();

		$cur_controller = 'order/index';
		$total = $need_data['total'];
		$total_money = $need_data['total_money'];
		$list = $need_data['list'];
		$pager = $need_data['pager'];
		$all_count = $need_data['all_count'];
		$count_status_1 = $need_data['count_status_1'];
		$count_status_3 = $need_data['count_status_3'];
		$count_status_4 = $need_data['count_status_4'];
		$count_status_5 = $need_data['count_status_5'];
		$count_status_7 = $need_data['count_status_7'];
		$count_status_11 = $need_data['count_status_11'];
		$count_status_14 = $need_data['count_status_14'];
		$count_status_express = $need_data['count_status_express'];


		$this->cur_controller = $cur_controller;
		$this->total = $total;
		$this->total_money = $total_money;
		$this->list = $list;
		$this->pager = $pager;
		$this->all_count = $all_count;
		$this->count_status_1 = $count_status_1;
		$this->count_status_3 = $count_status_3;
		$this->count_status_4 = $count_status_4;
		$this->count_status_5 = $count_status_5;
		$this->count_status_7 = $count_status_7;
		$this->count_status_11 = $count_status_11;
		$this->count_status_14 = $count_status_14;
		 $this->count_status_express = $count_status_express;

		$this->order_status_id = $order_status_id;
		$this->is_community = I('request.is_community', 0);
		$this->headid = I('request.headid', 0);

		$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print');

		if( empty($open_feier_print) )
		{
			$open_feier_print = 0;
		}

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$is_open_supply_print = D('Home/Front')->get_config_by_name('is_open_supply_print' );
			if( empty($is_open_supply_print) )
			{
				$open_feier_print = 0;
			}else{
				$supper_info = get_agent_logininfo();
				$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print'.$supper_info['id'] );
				if( empty($open_feier_print) )
				{
					$open_feier_print = 0;
				}
			}

		}


		$this->open_feier_print = $open_feier_print;

		$is_can_look_headinfo = true;
		$is_can_nowrfund_order = true;

		$is_can_confirm_delivery = true;
		$is_can_confirm_receipt = true;

		 $is_can_third_delivery = true;


		$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');

		$supply_can_nowrfund_order = D('Home/Front')->get_config_by_name('supply_can_nowrfund_order');


		$supply_can_confirm_delivery = D('Home/Front')->get_config_by_name('supply_can_confirm_delivery');

		$supply_can_confirm_receipt = D('Home/Front')->get_config_by_name('supply_can_confirm_receipt');

		 $supply_third_delivery_service = D('Home/Front')->get_config_by_name('supply_third_delivery_service');


		if (defined('ROLE') && ROLE == 'agenter' )
		{
			if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
			{
				$is_can_look_headinfo = false;
			}
			if( isset($supply_can_nowrfund_order) && $supply_can_nowrfund_order == 2 )
			{
				$is_can_nowrfund_order = false;
			}


			if( isset($supply_can_confirm_delivery) && $supply_can_confirm_delivery == 2 )
			{
				$is_can_confirm_delivery = false;
			}
			if( isset($supply_can_confirm_receipt) && $supply_can_confirm_receipt == 2 )
			{
				$is_can_confirm_receipt = false;
			}

		}
		if( isset($supply_third_delivery_service) && $supply_third_delivery_service == 1){
		    $is_can_third_delivery = true;
		}else{
		    $is_can_third_delivery = false;
		}

		$s_id = 1 ;

		if(SELLERUID != 1)
		{
			$seller_info = M('seller')->field('s_role_id')->where( array('s_id' => SELLERUID ) )->find();

			$perms_arr = M('eaterplanet_ecommerce_perm_role')->where( array('id' => $seller_info['s_role_id']) )->find();

			$perms1 = str_replace('.','/',$perms_arr['perms2']);

			$perms2 = explode(",", $perms1);

			if(in_array("user/user/index", $perms2)){
				$s_id = 1 ;
			} else {
				$s_id = 0 ;
			}

		}
		$this->s_id = $s_id;

		$is_localtown_imdada_status = D('Home/Front')->get_config_by_name('is_localtown_imdada_status');
		$is_localtown_sf_status = D('Home/Front')->get_config_by_name('is_localtown_sf_status');
		//是否开启蜂鸟即配
		$is_localtown_ele_status = D('Home/Front')->get_config_by_name('is_localtown_ele_status');
		$is_localtown_ele_status = isset($is_localtown_ele_status) ? $is_localtown_ele_status : 0;

        //是否开启码科配送
        $is_localtown_mk_status = D('Home/Front')->get_config_by_name('is_localtown_mk_status');
        $is_localtown_mk_status = isset($is_localtown_mk_status) ? $is_localtown_mk_status : 0;

        //码科预览价格
		$is_make_prequery_status = D('Home/Front')->get_config_by_name('is_make_prequery_status');
		$is_make_prequery_status = isset($is_make_prequery_status) ? $is_make_prequery_status : 0;


		$is_imdada_prequery_status = D('Home/Front')->get_config_by_name('is_imdada_prequery_status');
		$is_sf_prequery_status = D('Home/Front')->get_config_by_name('is_sf_prequery_status');

		$localtown_modifypickingname = D('Home/Front')->get_config_by_name('localtown_modifypickingname');
		$localtown_modifypickingname = !empty($localtown_modifypickingname) ? $localtown_modifypickingname: '包装费';

		$this->is_can_look_headinfo = $is_can_look_headinfo;
		$this->is_can_nowrfund_order = $is_can_nowrfund_order;
		$this->is_can_confirm_delivery = $is_can_confirm_delivery;
		$this->is_can_confirm_receipt = $is_can_confirm_receipt;

		 $this->is_localtown_imdada_status = $is_localtown_imdada_status;
		 $this->is_localtown_sf_status = $is_localtown_sf_status;
		 $this->is_localtown_mk_status = $is_localtown_mk_status;
		 $this->is_localtown_ele_status = $is_localtown_ele_status;
		 $this->is_make_prequery_status = $is_make_prequery_status;
		 $this->localtown_modifypickingname = $localtown_modifypickingname;
		 $this->is_can_third_delivery = $is_can_third_delivery;

		 if($is_localtown_imdada_status == 1 && $is_imdada_prequery_status == 1){
			 $this->is_imdada_prequery_status = 1;
		 }else{
			 $this->is_imdada_prequery_status = 0;
		 }
		 if($is_localtown_sf_status == 1 && $is_sf_prequery_status == 1){
			 $this->is_sf_prequery_status = 1;
		 }else{
			 $this->is_sf_prequery_status = 0;
		 }

		$data = D('Seller/Config')->get_all_config();
		$this->data = $data;

		$this->display('Order/index');
	 }

	 public function opremarksaler()
	{

		$opdata = $this->check_order_data();
		extract($opdata);

		if (IS_POST) {
			$remark = I('request.remark');

			M('eaterplanet_ecommerce_order')->where( array('order_id' => $item['order_id']) )->save( array('remarksaler' => $remark) );
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$this->item = $item;
		$this->id = $item['order_id'];
		include $this->display();
	}
	private function check_order_data()
	{


		$id = I('request.id',0);

		$item = M('eaterplanet_ecommerce_order')->where( array('order_id' => $id) )->find();

		if (empty($item)) {

				show_json(0, '未找到订单!');

		}

		return array('id' => $id, 'item' => $item);
	}

	public function opsendcancel()
	{


		$opdata = $this->check_order_data();
		extract($opdata);


		$sendtype = I('request.sendtype','');
		$gpc = I('request.');

		if (($item['order_status_id'] != 4) ) {
			show_json(0, '订单未发货，不需取消发货！');
		}

		if (IS_POST) {

			$remark = trim($gpc['remark']);
			$data = array('express_time' => 0,'shipping_no' =>'','shipping_method' => 0);
			$data['order_status_id'] = 1;

			M('eaterplanet_ecommerce_order')->where( array('order_id' => $item['order_id']) )->save($data);

			$history_data = array();
			$history_data['order_id'] = $item['order_id'];
			$history_data['order_status_id'] = 1;
			$history_data['notify'] = 0;
			$history_data['comment'] = '订单取消发货 ID: ' . $item['order_id'] . ' 订单号: ' . $item['order_num_alias'];
			$history_data['date_added'] = time();

			M('eaterplanet_ecommerce_order_history')->add($history_data);


			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}


		$sendgoods = array();
		$bundles = array();

		$this->id = $item['order_id'];
		$this->item = $item;
		$this->sendtype = $sendtype;

		include $this->display();
	}


	public function opchangeaddress()
	{
		$_GPC = I('request.');
		$opdata = $this->check_order_data();
		extract($opdata);


		$new_area = 0;
		$address_street = 0;

		$province_info = D('Home/Front')->get_area_info($item['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($item['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($item['shipping_country_id']);

		if (IS_POST) {
			$realname = $_GPC['realname'];
			$mobile = $_GPC['mobile'];
			$province = $_GPC['province'];
			$city = $_GPC['city'];
			$area = $_GPC['area'];
			$street = $_GPC['street'];
			$changead = intval($_GPC['changead']);
			$address = trim($_GPC['address']);




			if (!(empty($id))) {
				if (empty($realname)) {
					$ret = '请填写收件人姓名！';
					//show_json(0,  array('msg' => $ret) );
					show_json(0, $ret);
				}


				if (empty($mobile)) {
					$ret = '请填写收件人手机！';
					show_json(0, $ret);
				}
				if ($changead) {
					if ($province == '请选择省份') {
						$ret = '请选择省份！';
						show_json(0, $ret);
					}
					if (empty($address)) {
						$ret = '请填写详细地址！';
						show_json(0, $ret);
					}
				}

				$address_array = array();
				$address_array['shipping_name'] = $realname;
				$address_array['shipping_tel'] = $mobile;

				if ($changead) {




					$province_info = M('eaterplanet_ecommerce_area')->where("name like '%{$province}%' ")->find();

					if( !empty($province_info))
					{
						$province_id = $province_info['id'];
					}else{
						$max_dp = M('eaterplanet_ecommerce_area')->order('code desc')->find();

						$area_data = array();
						$area_data['name'] = $province;
						$area_data['pid'] = 0;
						$area_data['code'] = $max_dp['code']+1;

						$province_id = M('eaterplanet_ecommerce_area')->add($area_data);

						$up_data = array();
						$up_data['code'] = $province_id;

						M('eaterplanet_ecommerce_area')->where( array('id' => $province_id ) )->save($up_data);

					}

					//$city_info = M('eaterplanet_ecommerce_area')->where("name like '%{$city}%'")->find();

                    $guding_arr = ['鞍山市'];

                    if( in_array($city , $guding_arr) )
                    {
                        $city_info = M('eaterplanet_ecommerce_area')->where("name = '{$city}'")->find();
                    }else{
                        $city_info = M('eaterplanet_ecommerce_area')->where("name like '%{$city}%'")->find();
                    }

					if( !empty($city_info))
					{
						$city_id = $city_info['id'];
					}else{

						$max_dp = M('eaterplanet_ecommerce_area')->order('code desc')->find();

						$area_data = array();
						$area_data['name'] = $city;
						$area_data['pid'] = $province_id;
						$area_data['code'] = $max_dp['code']+1;

						$city_id = M('eaterplanet_ecommerce_area')->add( $area_data);

						$up_data = array();
						$up_data['code'] = $city_id;

						M('eaterplanet_ecommerce_area')->where( array('id' => $city_id ) )->save($up_data);

					}

					//fixed 20221229
					if( empty($area)   )
					{
						if( $city == '东莞市' || '中山市' || '儋州市' || '嘉峪关市' )
						{
							$area = '市辖区';
						}
					}

					$country_info = M('eaterplanet_ecommerce_area')->where( "name like '%{$area}%' " )->find();

					if( $area == '中山' )
					{
						$country_info = M('eaterplanet_ecommerce_area')->where( array('id' => 453 ) )->find();

					}


					if( !empty($country_info))
					{
						$country_id = $country_info['id'];
					}else{

						$max_dp = M('eaterplanet_ecommerce_area')->order('code desc')->find();

						$area_data = array();
						$area_data['name'] = $area;
						$area_data['pid'] = $city_id;
						$area_data['code'] = $max_dp['code']+1;

						$country_id = M('eaterplanet_ecommerce_area')->add( $area_data );

						$up_data = array();
						$up_data['code'] = $country_id;

						M('eaterplanet_ecommerce_area')->where( array('id' => $country_id ) )->save( $up_data );

					}


					$address_array['shipping_province_id'] = $province_id;
					$address_array['shipping_city_id'] = $city_id;
					$address_array['shipping_country_id'] = $country_id;

					$address_array['shipping_address'] = $address;

					if( $item['delivery'] == 'tuanz_send' )
					{
						$address_array['tuan_send_address'] = $address;
					}
				}

				M('eaterplanet_ecommerce_order')->where( array('order_id' => $id) )->save( $address_array );

				show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
			}
		}

		$this->id = $item['order_id'];
		$this->item = $item;
		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;

		$this->display();
	}

	//确认送达团长
	public function opsend_tuanz_over()
	{

		$opdata = $this->check_order_data();
		extract($opdata);
		//express_tuanz_time D('Home/Frontorder')->send_order_operate($order_info['order_id']);

		D('Seller/Order')->do_tuanz_over($item['order_id']);
		//D('Seller/Frontorder')->send_order_operate($item['order_id']);



		D('Home/Frontorder')->send_order_operate($item['order_id']);


		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}


	public function all_opprint()
	{
		$_GPC = I('request.');

		$order_arr = $_GPC['order_arr'];

		$cache_key = md5(time().mt_rand(100,1000));

		S('_all_opprintquene_'.$cache_key, $order_arr);


		$this->cache_key = $cache_key;
		$this->_GPC = $_GPC;

		$this->display();
	}


	public function do_opprint_quene()
	{
		$_GPC = I('request.');


		$cache_key = $_GPC['cache_key'];

		$quene_order_list = S('_all_opprintquene_'.$cache_key);

		$order_id = array_shift($quene_order_list);

		S('_all_opprintquene_'.$cache_key, $quene_order_list);


		$order_info =	M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array( 'order_id' => $order_id ) )->find();


		$print_model = D('Seller/Printaction');

		$result = $print_model->check_print_order( $order_id );
		$result2 =$print_model->check_print_order2( $order_id );

		if( $result['code'] == 1 || $result2['code'] == 1 )
		{

			M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('is_print_suc' => 1) );

		}

		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}

		//清单编号

		echo json_encode( array('code' => 0, 'msg' => '订单编号：'.$order_info['order_num_alias']." 处理成功，还剩余".count($quene_order_list)."个订单未处理") );
		die();
	}


	public function opprint()
	{
		$_GPC = I('request.');

		$order_id = $_GPC['id'];

		$print_model = D('Seller/Printaction');

		$result = $print_model->check_print_order( $order_id );
		$result2 = $print_model->check_print_order2( $order_id );
		if( $result['code'] == 1 || $result2['code'] == 1 )
		{
			M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('is_print_suc' => 1) );

			show_json(1, array('msg' => '打印成功！' ) );
		}else{
			$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print');
			if(!empty($open_feier_print) && ( $open_feier_print == 2 || $open_feier_print == 1 ) )
			{
				show_json(0,  array('msg' => $result['msg'] ) );
			}else{
				show_json(0,  array('msg' => $result2['msg'] ) );
			}

		}

	}



	public function oprefund_goods_do()
	{
		$_GPC = I('request.');

		$opdata = $this->check_order_data();
		extract($opdata);

		$weixin_model = D('Home/Weixin');

		$id = $_GPC['id'];

		$order_goods_id = $_GPC['order_goods_id'];
		/**
			id	: 	3864
			order_goods_id	: 4442
		**/
		//付款总额

		$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id ) )->find();

		$this->order_goods_info = $order_goods_info;

		$goods_images = tomedia( $order_goods_info['goods_images'] );

		$this->goods_images = $goods_images;

		$free_tongji = $order_goods_info['total']-$order_goods_info['voucher_credit']-$order_goods_info['fullreduction_money'] - $order_goods_info['score_for_money'] - $order_goods_info['has_refund_money'];

		$integral_flow = M('eaterplanet_ecommerce_member_integral_flow')->where( array('type' => 'orderbuy', 'order_goods_id' => $order_goods_id ) )->find();

		$use_score = 0;

		if( !empty($integral_flow) )
		{
			$use_score = $integral_flow['score'];
		}

		$this->use_score = $use_score;

		$total_quantity = D('Seller/Commonorder')->get_order_goods_quantity($id,$order_goods_id);

		$this->total_quantity = $total_quantity;

		$has_refund_quantity = D('Seller/Commonorder')->refund_order_goods_quantity($id,$order_goods_id);

		$this->has_refund_quantity = $has_refund_quantity;

		$has_refund_money = D('Seller/Commonorder')->get_order_goods_refund_money($id,$order_goods_id);

		$this->has_refund_money = $has_refund_money;

		$shipping_fare = $order_goods_info['shipping_fare'];

		$delivery = $opdata['item']['delivery'];

		$this->delivery = $delivery;

		//预售订单begin
        $presale_result = D('Home/PresaleGoods')->getOrderPresaleInfo( $id );
        $presale_info = [];
        if( $presale_result['code'] == 0 )
        {
            $presale_info = $presale_result['data'];
        }
        $this->presale_info = $presale_info;
        //end

		$is_has_refund_deliveryfree = D('Home/Front')->get_config_by_name('is_has_refund_deliveryfree');

		$is_has_refund_deliveryfree = !isset($is_has_refund_deliveryfree) || $is_has_refund_deliveryfree == 1 ? 1:0;

		if( $is_has_refund_deliveryfree == 1 && $item['type'] != 'integral')
		{
			//后台设置退运费了
			//退回运费，导致佣金出错问题修改
			//$free_tongji += $shipping_fare;
		}

		if($item['delivery'] == 'hexiao'){
			$used_total = D('Home/Salesroom')->get_hexiao_order_goods_used_total($id,$order_goods_id);
			$free_tongji = $free_tongji - $used_total;
		}

		$this->shipping_fare = $shipping_fare;
		$this->is_has_refund_deliveryfree = $is_has_refund_deliveryfree;
		$this->free_tongji = $free_tongji;
		$this->_GPC = $_GPC;

		$commiss_state = '未结算';

		$commiss_info = M('eaterplanet_community_head_commiss_order')->where( array('order_id' => $id, 'order_goods_id' =>$order_goods_id,'type' => 'orderbuy' ) )->find();

		if( !empty($commiss_info) && $commiss_info['state'] == 1 )
		{
			$commiss_state = '已结算';
		}

		$this->commiss_info  =$commiss_info;
		$this->commiss_state = $commiss_state;
		$this->payment_code = $item['payment_code'];

		if ( IS_POST ) {

			if(!$this->checkLocalTownOrderCanRefund($item)){
				show_json(0, array('message' => '同城配送订单配送中不能退款') );
			}

			$refund_money = isset($_GPC['refund_money']) && $_GPC['refund_money'] >0  ? floatval($_GPC['refund_money']) : 0;

			$is_refund_shippingfare = isset($_GPC['is_refund_shippingfare']) ? $_GPC['is_refund_shippingfare'] : 0;//退运费
			$is_back_sellcount = isset($_GPC['is_back_sellcount']) ? $_GPC['is_back_sellcount'] : 0;//退库存
			$is_back_scorecount = isset($_GPC['is_back_scorecount']) ? $_GPC['is_back_scorecount'] : 0;//退积分

			//$free_tongji = $opdata['item']['total']-$opdata['item']['voucher_credit']-$opdata['item']['fullreduction_money'] - $opdata['item']['score_for_money'];
			$free_tongji = $order_goods_info['total']-$order_goods_info['voucher_credit']-$order_goods_info['fullreduction_money'] - $order_goods_info['score_for_money'] - $order_goods_info['has_refund_money'];

			$refund_quantity =  isset($_GPC['refund_quantity']) && $_GPC['refund_quantity'] >0  ? floatval($_GPC['refund_quantity']) : 0;

			$real_refund_quantity = isset($_GPC['real_refund_quantity']) && $_GPC['real_refund_quantity'] >0  ? intval($_GPC['real_refund_quantity']) : 0;

			//已退商品数量
			$refund_total_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( "order_id={$id} and order_goods_id={$order_goods_id} " )->sum('quantity');



			//部分退款计算运费
			if($refund_quantity + $refund_total_quantity < $order_goods_info['quantity']){
			    $shipping_fare = round($refund_quantity/$order_goods_info['quantity']*$shipping_fare,2);
			}else{
				//$shipping_fare = round($refund_quantity/$order_goods_info['quantity']*$shipping_fare,2);

				$shipping_fare = $shipping_fare - $refund_total_quantity * round( 1/$order_goods_info['quantity'] * $shipping_fare,2);
			}




			$pay_total_money = $free_tongji ;
			$refund_shipping_fare = 0;
			$wx_refund_money = $refund_money;
			if( $is_refund_shippingfare == 1)
			{
				$free_tongji += $shipping_fare;
				$refund_shipping_fare = $shipping_fare;
				$wx_refund_money = $wx_refund_money + $shipping_fare;
			}



			$can_free_tongji = $opdata['item']['total'] + $opdata['item']['shipping_fare'] -$opdata['item']['voucher_credit']-$opdata['item']['fullreduction_money'] - $opdata['item']['score_for_money'];
			if( $item['type'] == 'integral' )
			{
			    $can_free_tongji = $opdata['item']['total']  -$opdata['item']['voucher_credit']-$opdata['item']['fullreduction_money'] - $opdata['item']['score_for_money'];

			}
			$can_refund_quantity = 0;
			if($item['delivery'] == 'hexiao'){
			    $can_refund_quantity = D('Home/Salesroom')->get_hexiao_order_goods_can_refund_quantity($id,$order_goods_id);
			}

			//以及退款了多少钱了 has_refund_money

			if($refund_money + $has_refund_money > $can_free_tongji){
					show_json(0, array('message' => '总退款金额大于可退款金额') );
			}
			else if( $refund_money < 0 )
			{
				show_json(0, array('message' => '填写正确的退款金额') );
			}
			else if( $is_back_sellcount == 1 && $refund_quantity > $total_quantity )
			{
				show_json(0, array('message' => '填写正确的退库存数量，最大'.$total_quantity.'个' ) );
			}else if($item['delivery'] == 'hexiao' && $can_refund_quantity < $refund_quantity){
				show_json(0, array('message' => '核销商品退款数量大于可退款数量'.$refund_quantity.'个' ) );
			}
			else{

			    if( $is_refund_shippingfare == 0 && $opdata['item']['type'] == 'integral' )
			    {
			        M('eaterplanet_ecommerce_order')->where( array('order_id' => $id) )->save( array('shipping_fare' => 0) );
			       M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' =>$order_goods_id ) )->save( array('shipping_fare' => 0) );
			    }

			    //is_back_sellcount

				//$res = $weixin_model->refundOrder($id,$refund_money,0,$order_goods_id,$is_back_sellcount, $refund_quantity,1);
				$res = $weixin_model->refundOrder($id,$wx_refund_money,0,$order_goods_id,$is_back_sellcount, $refund_quantity,1);

			    if( $is_refund_shippingfare == 0 && $opdata['item']['type'] == 'integral' )
			    {
			        M('eaterplanet_ecommerce_order')->where( array('order_id' => $id) )->save( array('shipping_fare' => $opdata['item']['shipping_fare'] ) );
			        M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' =>$order_goods_id ) )->save( array('shipping_fare' => $order_goods_info['shipping_fare']) );

			    }




				if( $res['code'] == 0 )
				{
					show_json(0, array('message' => $res['msg'] ) );
				}else{

					//开始插入本次退款的情况
					//存储当前这笔退款 影响到的以后佣金情况



					//is_back_scorecount
					if( $is_back_scorecount == 1 )
					{
						$refund_score = 0;
						$refund_score = intval( ($use_score / $order_goods_info['quantity']) * $refund_quantity);


						D('Seller/Commonorder')->refund_order_goods_intrgral( $id, $order_goods_id ,$refund_score );

					}else{
						$refund_score = 0;

						D('Seller/Commonorder')->refund_order_goods_intrgral( $id, $order_goods_id ,0 );
					}
					//退回运费，导致佣金出错问题修改
					$order_goods_refundid =  D('Seller/Commonorder')->ins_order_goods_refund($id, $order_goods_id,$pay_total_money,$real_refund_quantity, $refund_quantity,$refund_money,$is_back_sellcount,$refund_shipping_fare);
					//$order_goods_refundid =  D('Seller/Commonorder')->ins_order_goods_refund($id, $order_goods_id,$pay_total_money,$real_refund_quantity, $refund_quantity,$refund_money,$is_back_sellcount);



					//如果这个商品没有数量了。就改变他的状态为已退款的状态
					$new_total_quantity = D('Seller/Commonorder')->get_order_goods_quantity($id,$order_goods_id);
					if( $new_total_quantity <=0 )
					{
						D('Seller/Commonorder')->check_refund_order_goods_status($id, $order_goods_id, $refund_money,$is_back_sellcount,$real_refund_quantity,  $refund_quantity,$is_refund_shippingfare);

						$order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $id ) )->select();
						$is_all_refund = true;

						foreach($order_goods_list as $val )
						{
							if($val['is_refund_state'] != 1)
							{
								$is_all_refund = false;
							}
						}

						$or_gd_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id ) )->find();

						if(($or_gd_info['is_refund_state'] == 1) && ($is_all_refund == false) ){

							$or_gd_info = M('eaterplanet_ecommerce_order_goods')->field('name')->where( array('order_goods_id' => $order_goods_id )  )->find();
							$order_history = array();
							$order_history['order_id'] = $id;
							$order_history['order_status_id'] = 19;
							$order_history['notify'] = 0;

							if( $item['type'] == 'integral' ){
							$order_history['comment'] =  '后台子订单退款，退款商品：'.$or_gd_info['name'].'，'.$real_refund_quantity.'个，退库存/销量数量'.$refund_quantity.'个，退款积分'.$refund_money.'积分';
							}else{
							$order_history['comment'] =  '后台子订单退款，退款商品：'.$or_gd_info['name'].'，'.$real_refund_quantity.'个，退库存/销量数量'.$refund_quantity.'个，退款金额'.$refund_money.'元';
							}
							$order_history['date_added'] = time();

							M('eaterplanet_ecommerce_order_history')->add( $order_history );

						}

					}else{

						$or_gd_info = M('eaterplanet_ecommerce_order_goods')->field('name')->where( array('order_goods_id' => $order_goods_id )  )->find();

						$order_history = array();
						$order_history['order_id'] = $id;
						$order_history['order_status_id'] = 19;
						$order_history['notify'] = 0;
						if( $item['type'] == 'integral' ){
							$order_history['comment'] =  '后台子订单退款，退款商品：'.$or_gd_info['name'].'，'.$real_refund_quantity.'个，退库存/销量数量'.$refund_quantity.'个，退款积分'.$refund_money.'积分，';
						}else{
							$order_history['comment'] =  '后台子订单退款，退款商品：'.$or_gd_info['name'].'，'.$real_refund_quantity.'个，退库存/销量数量'.$refund_quantity.'个，退款金额'.$refund_money.'元，';
						}
						if($is_refund_shippingfare == 1)
						{
							$order_history['comment'] .= '. 退配送费：'.$refund_shipping_fare.'元';
						}

						$order_history['date_added'] = time();

						M('eaterplanet_ecommerce_order_history')->add( $order_history );
					}
					//核销订单操作
					if($item['delivery'] == 'hexiao'){
						//核销商品订单退款
					    D('Home/Salesroom')->hexiao_refund_action($id,$order_goods_id,$refund_quantity);
						//操作核销订单是否完成
						D('Seller/Order')->hexiao_finished($id, '未核销商品退款，订单完成');
					}

                    //礼品卡退款
                    D('Seller/VirtualCard')->refundOrder($id);

					show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
				}
			}
		}

		if( $item['type'] == 'integral' )
		{
		    include  $this->display('Order/oprefund_goods_dointegral');
		}else{
		    include  $this->display();
		}


	}


	public function oprefund_do()
	{
		$_GPC = I('request.');

		$opdata = $this->check_order_data();
		extract($opdata);


		$id = $_GPC['id'];

		//付款总额
		$free_tongji = $opdata['item']['total']-$opdata['item']['voucher_credit']-$opdata['item']['fullreduction_money'] - $opdata['item']['score_for_money'];


		$total_quantity = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $id ) )->sum('quantity');

		$has_refud_money = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $id ) )->sum('money');

		$this->has_refud_money = $has_refud_money;

		$has_refund_quantity = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $id ) )->sum('quantity');

		$total_quantity = $total_quantity - $has_refund_quantity;

		$buy_score_total = M('eaterplanet_ecommerce_member_integral_flow')->where( array('type' => 'orderbuy' , 'order_id' => $id ) )->sum('score');

		$has_refund_buy_score_total = M('eaterplanet_ecommerce_member_integral_flow')->where(array('type' => 'refundorder' , 'order_id' => $id ))->sum('score');

		if(!empty($has_refund_buy_score_total) && $has_refund_buy_score_total > 0)
		  $buy_score_total = $buy_score_total - $has_refund_buy_score_total;

		$this->buy_score_total = $buy_score_total;

		$shipping_fare = $opdata['item']['shipping_fare'];

		$delivery = $opdata['item']['delivery'];

		$is_has_refund_deliveryfree = D('Home/Front')->get_config_by_name('is_has_refund_deliveryfree');

		$is_has_refund_deliveryfree = !isset($is_has_refund_deliveryfree) || $is_has_refund_deliveryfree == 1 ? 1:0;

		if( $is_has_refund_deliveryfree == 1)
		{
			//后台设置退运费了
			//退回运费，导致佣金出错问题修改
			$free_tongji += $opdata['item']['shipping_fare'];
		}
		$hx_used_total = 0;
		if($item['delivery'] == 'hexiao'){
		    $used_quantity = D('Home/Salesroom')->get_hexiao_order_used_quantity($id);
		    $hx_used_total = D('Home/Salesroom')->get_hexiao_order_used_total($id);
		    $total_quantity = $total_quantity - $used_quantity;
		    $free_tongji = $free_tongji - $hx_used_total;
		}
		//判断是否有预售 begin
        $presale_result = D('Home/PresaleGoods')->getOrderPresaleInfo( $id );
		$presale_info = [];
		if( $presale_result['code'] == 0 )
        {
            $presale_info = $presale_result['data'];
            $free_tongji = $free_tongji - $presale_info['presale_for_ordermoney'];
        }
        //end

		$this->presale_info = $presale_info;
		$this->is_has_refund_deliveryfree = $is_has_refund_deliveryfree;
		$this->shipping_fare = $shipping_fare;
		$this->total_quantity = $total_quantity;
		$this->delivery = $delivery;
		$this->hx_used_total = $hx_used_total;
		if( IS_POST )
		{
			if(!$this->checkLocalTownOrderCanRefund($item)){
				show_json(0, array('message' => '同城配送订单配送中不能退款') );
			}
			$refund_money = isset($_GPC['refund_money']) && $_GPC['refund_money'] >0  ? floatval($_GPC['refund_money']) : 0;

			$is_refund_shippingfare = isset($_GPC['is_refund_shippingfare']) ? $_GPC['is_refund_shippingfare'] : 0;//退运费
			$is_back_sellcount = isset($_GPC['is_back_sellcount']) ? $_GPC['is_back_sellcount'] : 0;//退库存
			$is_back_buyscore = isset($_GPC['is_back_buyscore']) ? $_GPC['is_back_buyscore'] : 0;//退积分


			$wx_refund_money = $refund_money;
			$free_tongji = $opdata['item']['total']-$opdata['item']['voucher_credit']-$opdata['item']['fullreduction_money'] - $opdata['item']['score_for_money'];

			//已经退款运费
			$refund_shipping_fare_amount = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' =>$id ) )->sum('refund_shipping_fare');
			if( empty($refund_shipping_fare_amount) )
			{
				$refund_shipping_fare_amount = 0;
			}
			if( $is_refund_shippingfare == 1 && $opdata['item']['type'] != 'integral' )
			{
				if($refund_shipping_fare_amount < $shipping_fare) {
                    $shipping_fare = $shipping_fare - $refund_shipping_fare_amount;
                    $this->shipping_fare = $shipping_fare;
					//$free_tongji += $shipping_fare;
					$wx_refund_money += $shipping_fare - $opdata['item']['fare_shipping_free'];
					$is_back_buyscore = 2;//退运费
				}
			}

			$free_tongji = $free_tongji  - $has_refud_money;

			if( $refund_money - $free_tongji >= 0.01 ){
					show_json(0, array('message' => '填写金额大于总退款金额' ) );
			}
			else if( $refund_money < 0 )
			{
				show_json(0, array('message' => '填写正确的退款金额' ) );
			}
			else{

				$weixin_model = D('Home/Weixin');

				$id = $_GPC['id'];

				$model = M('eaterplanet_ecommerce_order');
				//$model->startTrans();  // 开启事务
				$order_info = $model->where(array('order_id'=>$id))->find();

				if( $order_info['order_status_id'] != 7 )
				{
					if( $is_refund_shippingfare == 0 && $opdata['item']['type'] == 'integral' )
					{
						M('eaterplanet_ecommerce_order')->where( array('order_id' => $id) )->save( array('shipping_fare' => 0) );
					}

					//$res = $weixin_model->refundOrder($id,$refund_money,0,0,$is_back_sellcount);
					$res = $weixin_model->refundOrder($id,$wx_refund_money,0,0,$is_back_sellcount);

					if( $is_refund_shippingfare == 0 && $opdata['item']['type'] == 'integral' )
					{
						M('eaterplanet_ecommerce_order')->where( array('order_id' => $id) )->save( array('shipping_fare' => $opdata['item']['shipping_fare'] ) );
					}
					$model->commit();  // 开启事务
					if( $res['code'] == 0 )
					{
						show_json(0, array('message' => $res['msg']) );
					}else{


						//integral
						$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $id ) )->find();

						$comment = '后台操作立即退款,退款金额:'.$refund_money.'元';

						if( $is_refund_shippingfare == 0 && $opdata['item']['type'] == 'integral' )
						{
							$comment = '后台操作立即退款,退款积分:'.$refund_money.'积分';
						}


						if( $order_info['type'] == 'integral' )
						{
							$comment = '后台操作立即退款,退还积分:'.$refund_money.'积分';
							//$comment = '后台操作立即退款,退款金额:'.$refund_money.'元';
						}

						if($is_refund_shippingfare == 1)
						{
							if( $order_info['type'] == 'integral' ){

								$refund_shipping_fare_amount2 = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' =>$id ) )->sum('refund_shipping_fare');
								if( empty($refund_shipping_fare_amount2) )
								{
									$refund_shipping_fare_amount2 = 0;
								}

								$new_shipping_fare = $opdata['item']['shipping_fare'] - $refund_shipping_fare_amount2;

								$comment .= '. 退配送费：'.$new_shipping_fare.'元';
							}else{
								$comment .= '. 退配送费：'.$opdata['item']['shipping_fare'].'元';
							}

						}

						if($is_back_sellcount == 1)
						{
							$comment .= '. 退库存：'.$total_quantity;
						}else{
							$comment .= '. 不退库存，不减销量';
						}

						$history_data = array();
						$history_data['order_id'] = $id;
						$history_data['order_status_id'] = 7;
						$history_data['notify'] = 0;
						$history_data['comment'] = $comment;
						$history_data['date_added'] = time();

						M('eaterplanet_ecommerce_order_history')->add($history_data);

						M('eaterplanet_ecommerce_order')->where( array('order_id' => $id) )->save( array('order_status_id' => 7) );
						//将所有在退款中的状态，全部重置成已退款成功
						M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $id, 'state' => 0) )->save( array('state' => 3) );

						//将退款中的 申请订单，全部改成已退款

						$refund_all = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' =>$id, 'state' => 0 ) )->select();

						if( !empty($refund_all) )
						{
							foreach( $refund_all as $val )
							{
								$ins_data = array();

								$ins_data['ref_id'] = $val['ref_id'];
								$ins_data['order_id'] = $val['order_id'];
								$ins_data['order_goods_id'] = $val['order_goods_id'];
								$ins_data['message'] = '平台同意退款   ,退款成功';
								$ins_data['type'] = 2;
								$ins_data['addtime'] = time();

								M('eaterplanet_ecommerce_order_refund_history')->add( $ins_data );

								M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $val['ref_id']) )->save( array('state' => 3) );

							}
						}


						//$data[''] = isset($data['is_print_admin_cancleorder']) ? $data['is_print_admin_cancleorder'] : 0;
						$is_print_admin_cancleorder = D('Home/Front')->get_config_by_name('is_print_admin_cancleorder');

						D('Seller/Commonorder')->refund_one_order( $id , $is_back_buyscore);


						if( isset($is_print_admin_cancleorder) && $is_print_admin_cancleorder == 1 )
						{
							D('Seller/Printaction')->check_print_order($id,'后台操作取消订单');
							D('Seller/Printaction')->check_print_order2($id,'后台操作取消订单');
						}
                        sellerLog('订单['.$order_info['order_num_alias'].']退款成功操作', 2);

                        //核销订单操作
                        if($item['delivery'] == 'hexiao'){
                            D('Home/Salesroom')->hexiao_order_refund_action($id);
                        }
                        //礼品卡退款
                        D('Seller/VirtualCard')->refundOrder($id);

                        show_json(1, array('message' => '退款成功！') );
					}

				}else{
					//$model->rollback();  // 回滚
					show_json(0,  array('message' => '请勿重复提交'));
				}

			}

		}

		$this->id = $id;
		$this->free_tongji = $free_tongji;
		$this->item = $item;

		$commiss_state = '未结算';

		$commiss_info = M('eaterplanet_community_head_commiss_order')->where( array('order_id' => $id, 'type' => 'orderbuy' )  )->find();

		if( !empty($commiss_info) && $commiss_info['state'] == 1 )
		{
			$commiss_state = '已结算';
		}

		$this->commiss_state = $commiss_state;

		if( $item['type'] == 'integral' )
		{
			$this->display('Order/oprefund_do_integral');
		}else{
			$this->display();
		}



	}


	function test()
	{
		//D('Seller/Printaction')->check_print_order(63);
		// eaterplanet_ecommerce_order

		$shop_name = D('Home/Front')->get_config_by_name('shoname');


		$this->shop_name = $shop_name;
		$order_id = 174;

		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();


		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id) )->select();

		$need_order_goods = array();


		foreach($order_goods as $key => $value)
		{
			$value['option_sku'] = D('Seller/Order')->get_order_option_sku($order_id, $value['order_goods_id']);

			$need_order_goods[$key] = $value;
		}

		$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);
		$member = M('eaterplanet_ecommerce_member')->where( array('member_id' => $item['member_id'] ) )->find();


		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		$this->member = $member;

		$this->order_info = $order_info;
		$this->need_order_goods = $need_order_goods;
		$this->display('forms');
	}

	/**
		打印配送单
	**/
	public function order_print_dan()
	{
		$order_id = I('get.id');

		$shop_name = D('Home/Front')->get_config_by_name('shoname');


		$this->shop_name = $shop_name;

		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();


		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id) )->select();

		$need_order_goods = array();

		$total = 0;
		foreach($order_goods as $key => $value)
		{
			$value['option_sku'] = D('Seller/Order')->get_order_option_sku($order_id, $value['order_goods_id']);

			$need_order_goods[$key] = $value;
		}
		$total = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id) )->sum('total');
		$legal_amount = D('Seller/Order')->num_to_rmb($total);

		$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);
		$member = M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();
        sellerLog('订单['.$order_info['order_num_alias'].']打印配送单', 2);

		$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');

		$order_note_name = isset($order_note_name) && !empty($order_note_name) ? $order_note_name :'店名';
		$this->order_note_name = $order_note_name;

		$this->legal_amount = $legal_amount;
        $this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		$this->member = $member;

		$this->order_info = $order_info;
		$this->need_order_goods = $need_order_goods;
		$this->display('forms');
	}

	//配送团长
	public function opsend_tuanz()
	{

		$opdata = $this->check_order_data();
		extract($opdata);

		D('Seller/Order')->do_send_tuanz($item['order_id']);
		$order_num_alias = M('eaterplanet_ecommerce_order')->where(  array('order_id' => $item['order_id'] ) )->find();
		D('Seller/Operatelog')->addOperateLog('detailed_list','配送团长--订单编号'.$order_num_alias['order_num_alias']);
		//后台自动送达团长
		D('Seller/Order')->order_auto_service($item,'后台自动确认送达团长');

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	//同城配送，确认开始配送流程
    public function opsend_localtown()
    {
        $opdata = $this->check_order_data();
        extract($opdata);

        $count = D('Seller/Order')->do_send_localtown($item['order_id']);

        show_json(1, array('url' => $_SERVER['HTTP_REFERER'], 'count' => $count));

    }


	public function opsend()
	{
		$_GPC = I('request.');

		$opdata = $this->check_order_data();
		extract($opdata);

		if (empty($item['address_id'])) {
			show_json(0,  array('message' => '无收货地址，无法发货！'));
		}


		if ($item['order_status_id'] == 3) {
			show_json(0, array('message' => '订单未付款，无法发货！'));
		}


		if (IS_POST) {

			//express shipping_no  $express_info['name'] dispatchname

			if (!(empty($_GPC['shipping_no'])) && empty($_GPC['shipping_no'])) {
				show_json(0, array('message' => '请输入快递单号！')  );
			}

			if ( empty($_GPC['express']) ) {
				show_json(0, array('message' => '请选择快递公司！'));
			}


			if (!(empty($item['transid']))) {
			}

			$express_info = D('Seller/Express')->get_express_info($_GPC['express']);

			$time = time();
			$data = array(
				'shipping_method' => trim($_GPC['express']),
				'shipping_no' => trim($_GPC['shipping_no']),
				'dispatchname' => $express_info['name'],
				'express_time' => $time
			);

			$data['order_status_id'] = 4;


			M('eaterplanet_ecommerce_order')->where( array('order_id' => $item['order_id']) )->save( $data );



			$history_data = array();
			$history_data['order_id'] = $item['order_id'];
			$history_data['order_status_id'] = 4;
			$history_data['notify'] = 0;
			$history_data['comment'] = '订单发货 ID: ' . $item['order_id'] . ' 订单号: ' . $item['order_num_alias'] . ' <br/>快递公司: ' . $express_info['name'] . ' 快递单号: ' . $_GPC['shipping_no'];
			$history_data['date_added'] = time();

			M('eaterplanet_ecommerce_order_history')->add($history_data);

			D('Home/Frontorder')->send_order_operate($item['order_id']);
            sellerLog('订单['.$item['order_num_alias'].']订单发货操作', 2);

            //TODO...发送已经发货的消息通知
			//m('notice')->sendOrderMessage($item['id']);
			//plog('order.op.send', '订单发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br/>快递公司: ' . $_GPC['expresscom'] . ' 快递单号: ' . $_GPC['expresssn']);
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$noshipped = array();
		$shipped = array();

		$province_info = D('Home/Front')->get_area_info($item['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($item['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($item['shipping_country_id']);


		$order_goods = M()->query('select og.order_goods_id as id,og.name as title,og.goods_images as thumb from ' .
						C('DB_PREFIX'). 'eaterplanet_ecommerce_order_goods as og  where og.order_id= '.$item['order_id']);



		$express_list = D('Seller/Express')->load_all_express();


		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		$this->order_goods = $order_goods;
		$this->item = $item;
		$this->express_list = $express_list;
		$this->id = $item['order_id'];

		$this->display();
	}

	// 11  已完成
	public function opfinish()
	{

		$opdata = $this->check_order_data();
		extract($opdata);


		M('eaterplanet_ecommerce_order')->where(array('order_id' => $item['order_id']) )->save( array('order_status_id' => 11,
			'finishtime' => time()) );

		$history_data = array();
		$history_data['order_id'] = $item['order_id'];
		$history_data['order_status_id'] = 11;
		$history_data['notify'] = 0;
		$history_data['comment'] = '后台操作，已完成' ;
		$history_data['date_added'] = time();
        sellerLog('订单['.$item['order_num_alias'].']订单完成操作', 2);

        M('eaterplanet_ecommerce_order_history')->add($history_data);

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function export_form()
	{
		$_GPC = I('request.');


		/**
		order_status_id:
		type: normal
		searchtime:
		time[start]: 2019-12-23 00:00:00
		time[end]: 2019-12-23 23:59:59
		delivery:
		searchfield: ordersn
		keyword:
		export: 0
		**/
		unset($_GPC['controller']);
		unset($_GPC['export']);

		$post_data = array();
		$post_data['order_status_id'] = $_GPC['order_status_id'];
		$post_data['type'] = $_GPC['type'];
		$post_data['searchtime'] = $_GPC['searchtime'];
		$post_data['time[start]'] = $_GPC['time']['start'];
		$post_data['time[end]'] = $_GPC['time']['end'];

		$post_data['headid'] = $_GPC['headid'];
		$post_data['delivery'] = $_GPC['delivery'];
		$post_data['searchfield'] = $_GPC['searchfield'];
		$post_data['keyword'] = $_GPC['keyword'];

		$post_data['export'] = 1;
        $note_content = D('Home/Front')->get_config_by_name('order_note_name');
		$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');

        $columns = array(
				array('title' => '订单流水号', 'field' => 'day_paixu', 'width' => 24, 'sort' => 0, 'is_check' => 1),
				array('title' => '订单编号', 'field' => 'order_num_alias', 'width' => 24, 'sort' => 0, 'is_check' => 1),
				array('title' => '客户昵称', 'field' => 'name', 'width' => 12, 'sort' => 0, 'is_check' => 1),
                                array('title' => '客户手机号', 'field' => 'telephone', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '客户备注', 'field' => 'member_content', 'width' => 24, 'sort' => 0, 'is_check' => 1),
				array('title' => '收货姓名(或自提人)', 'field' => 'shipping_name', 'width' => 12, 'sort' => 0, 'is_check' => 1),

				array('title' => '联系电话', 'field' => 'shipping_tel', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '收货地址', 'field' => 'address_province_city_area', 'width' => 12, 'sort' => 0, 'is_check' => 1),

				array('title' => '商户名称/类型', 'field' => 'supply_name', 'width' => 12, 'sort' => 0, 'is_check' => 1),

				array('title' => '提货详细地址', 'field' => 'address_address', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '商品名称', 'field' => 'goods_title', 'width' => 24, 'sort' => 0, 'is_check' => 1),
				array('title' => '商品分类', 'field' => 'goods_category', 'width' => 24, 'sort' => 0, 'is_check' => 1),
				array('title' => '商品价格', 'field' => 'goods_rprice2', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '商品数量', 'field' => 'quantity', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '支付方式', 'field' => 'paytype', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '配送方式', 'field' => 'delivery', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '预计送达时间', 'field' => 'expected_delivery_time', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '团长配送送货详细地址', 'field' => 'tuan_send_address', 'width' => 22, 'sort' => 0, 'is_check' => 1),

				array('title' => '商品规格', 'field' => 'goods_optiontitle', 'width' => 12, 'sort' => 0, 'is_check' => 1),

				array('title' => '商品单价', 'field' => 'goods_price1', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '收货时间', 'field' => 'receive_time', 'width' => 24, 'sort' => 0, 'is_check' => 1),
				array('title' => '快递单号', 'field' => 'expresssn', 'width' => 24, 'sort' => 0, 'is_check' => 1),
				array('title' => '下单时间', 'field' => 'createtime', 'width' => 24, 'sort' => 0, 'is_check' => 1),
				array('title' => '小区名称', 'field' => 'community_name', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '团长姓名', 'field' => 'head_name', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '团长电话', 'field' => 'head_mobile', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '退款商品数量', 'field' => 'has_refund_quantity', 'width' => 12, 'sort' => 0, 'is_check' => 1),
				array('title' => '退款金额', 'field' => 'has_refund_money', 'width' => 12, 'sort' => 0, 'is_check' => 1),

				array('title' => '微信支付交易单号', 'field' => 'transaction_id', 'width' => 24, 'sort' => 0, 'is_check' => 0),

				array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 12, 'sort' => 0, 'is_check' => 0),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24, 'sort' => 0, 'is_check' => 0),
				array('title' => '运费', 'field' => 'dispatchprice', 'width' => 12, 'sort' => 0, 'is_check' => 0),
				array('title' => '积分抵扣', 'field' => 'score_for_money', 'width' => 12, 'sort' => 0, 'is_check' => 0),
				array('title' => '满额立减', 'field' => 'fullreduction_money', 'width' => 12, 'sort' => 0, 'is_check' => 0),
				array('title' => '优惠券优惠', 'field' => 'voucher_credit', 'width' => 12, 'sort' => 0, 'is_check' => 0),
				array('title' => '应收款(该笔订单总款)', 'field' => 'price', 'width' => 12, 'sort' => 0, 'is_check' => 0),
				array('title' => '状态', 'field' => 'status', 'width' => 12, 'sort' => 0, 'is_check' => 0),
				array('title' => '团长佣金', 'field' => 'head_money', 'width' => 12, 'sort' => 0, 'is_check' => 0),
				array('title' => '客户佣金', 'field' => 'member_commissmoney', 'width' => 12, 'sort' => 0, 'is_check' => 0),
				array('title' => '付款时间', 'field' => 'paytime', 'width' => 24, 'sort' => 0, 'is_check' => 0),
				array('title' => '发货时间', 'field' => 'sendtime', 'width' => 24, 'sort' => 0, 'is_check' => 0),
				array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24, 'sort' => 0, 'is_check' => 0),
				array('title' => '快递公司', 'field' => 'expresscom', 'width' => 24, 'sort' => 0, 'is_check' => 0),
				array('title' => '完整地址', 'field' => 'fullAddress', 'width' => 24, 'sort' => 0, 'is_check' => 0),
				array('title' => '订单备注', 'field' => 'remark', 'width' => 36, 'sort' => 0, 'is_check' => 0),
				array('title' => '卖家订单备注', 'field' => 'remarksaler', 'width' => 36, 'sort' => 0, 'is_check' => 0),
				array('title' => '商品成本价', 'field' => 'costprice', 'width' => 36, 'sort' => 0, 'is_check' => 0),
				array('title' => '省市区', 'field' => 'province', 'width' => 24, 'sort' => 0, 'is_check' => 0),
				array('title' => '商品重量', 'field' => 'goods_weight', 'width' => 24, 'sort' => 0, 'is_check' => 0),
			);

			if($order_note_open == 1){
				if(empty($note_content)){
					$note_content=  '店名';
				}
				$columns[] = array('title' => $note_content, 'field' => 'note_content', 'width' => 24, 'sort' => 0, 'is_check' => 0);

			}


		//load_model_class('config')->update( array('modify_export_fields' => $modify_explode_arr ) );
        sellerLog('订单excel导出操作', 2);

        $modify_explode_json = D('Home/Front')->get_config_by_name('modify_export_fields');

		if( !empty($modify_explode_json) )
		{
			$modify_explode_arr = json_decode($modify_explode_json, true);

			foreach( $columns as $key => $val )
			{
				if( in_array($val['field'], array_keys($modify_explode_arr) ) )
				{
					$val['is_check'] =1;
					$val['sort'] = $modify_explode_arr[$val['field']];
				}else{

					$val['is_check'] =0;
					$val['sort'] = 0;
				}
				$columns[$key] = $val;
			}

			$last_index_sort = array_column($columns,'sort');
			array_multisort($last_index_sort,SORT_DESC,$columns);

		}

		$this->post_data = $post_data;
		$this->columns = $columns;
		$this->_GPC = $_GPC;


		$this->display();
	}


	public function oppay()
	{

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			show_json(0, array('message' => '您无此权限') );
		}

		$order_id = I('request.id');


		$res_arr = D('Seller/Order')->admin_pay_order($order_id);


		if( $res_arr['code'] == 0)
		{
			show_json(0, array('message' => $res_arr['msg']) );
		}else{
			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
		}
	}

	public function detail()
	{
		$id = I('get.id');


		$item = M('eaterplanet_ecommerce_order')->where( array('order_id' => $id ) )->find();

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
			if($supply_can_look_headinfo != 1){
					$item['ziti_name'] = D('Seller/Order')->desensitize($item['ziti_name'],1,1);
					$item['ziti_mobile'] = D('Seller/Order')->desensitize($item['ziti_mobile'],3,4);
			}

		}

        switch ($item['delivery']){
            case 'pickup':$item['delivery_text'] = '社区自提';break;
            case 'express':$item['delivery_text'] = '快递';break;
            case 'tuanz_send':$item['delivery_text'] = '团长配送';break;
			case 'hexiao':$item['delivery_text'] = '到店核销';break;
        }

        //预收订单信息begin
        $presale_result = D('Home/PresaleGoods')->getOrderPresaleInfo( $id );
		if( $presale_result['code'] == 0 )
        {
            $item['presale_info'] = $presale_result['data'];
        }else{
		    $item['presale_info'] = [];
        }
        //预收订单信息end

		if($item['type'] == 'pintuan'){

			$pin_order = M('eaterplanet_ecommerce_pin_order')->field('pin_id')->where( array('order_id' => $id ) )->find();

			$pin_id = $pin_order['pin_id'];

			$this->pin_id = $pin_id;
		}

		$order_goods = array();



		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $id) )->select();

		$need_order_goods = array();

		$shipping_fare = 0;
		$fullreduction_money = 0;
		$voucher_credit = 0;
		$total = 0;
	    $order_head_status = 0 ;
		foreach($order_goods as $key => $value)
		{
			// eaterplanet_community_head_commiss_order
			$value['name'] = htmlspecialchars_decode(stripslashes($value['name']));
			$head_commission_order_info = M('eaterplanet_community_head_commiss_order')->where( array('order_goods_id' => $value['order_goods_id'],'order_id' => $item['order_id']) )->select();

			if( !empty($head_commission_order_info) )
			{
				foreach( $head_commission_order_info  as  &$vv)
				{
					if($vv['state'] == 1){
						$order_head_status =1;
					}
					$head_info_tp = M('eaterplanet_community_head')->field('head_name')->where( array('id' => $vv['head_id'] ) )->find();

					$vv['head_name'] = $head_info_tp['head_name'];

					if (defined('ROLE') && ROLE == 'agenter' )
					{
						$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
						if($supply_can_look_headinfo != 1){
							$vv['head_name'] = D('Seller/Order')->desensitize($vv['head_name'],1,1);
						}

					}


					//退配送费
					$refund_shipping_fare = 0;
					if($vv['type'] == 'orderbuy' && $vv['add_shipping_fare'] > 0){
						$refund_shipping_sql = "SELECT sum(refund_shipping_fare) as refund_shipping_fare "
								. " FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods_refund "
								. " WHERE order_id=".$vv['order_id'].' and order_goods_id='.$vv['order_goods_id'];
						$refund_shippings = M()->query($refund_shipping_sql);

						if(!empty($refund_shippings)){
							$refund_shipping_fare = $refund_shippings[0]['refund_shipping_fare'];
						}
						$vv['add_shipping_fare'] = $vv['add_shipping_fare'] - $refund_shipping_fare;
						$vv['money'] = $vv['money'];
					}else{
						$vv['add_shipping_fare'] = $vv['add_shipping_fare'];
					}
					if($vv['state'] == 2){//分佣订单已取消，分佣金额为0
						$vv['money'] = 0;
					}
				}
				unset($vv);
			}

			if( $value['is_refund_state'] == 1 || $value['has_refund_money'] > 0)
			{
				$refund_info = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $id,'order_goods_id' => $value['order_goods_id'] ) )->order('ref_id desc')->find();

				$value['refund_info'] = $refund_info;

				$goods_refund_shipping_fare = M('eaterplanet_ecommerce_order_goods_refund')->where( array('order_id' => $id,'order_goods_id' => $value['order_goods_id'] ) )->sum('refund_shipping_fare');

				$value['goods_refund_shipping_fare'] = $goods_refund_shipping_fare;
			}

			if( $item['is_commission'] == 1 )
			{
				$member_commission_list = M('eaterplanet_ecommerce_member_commiss_order')->where( array('order_goods_id' => $value['order_goods_id'],'order_id' => $item['order_id']) )->order('id asc')->select();

				if( !empty($member_commission_list) )
				{
					foreach( $member_commission_list as $kk => $vv )
					{
						$tmp_if = M('eaterplanet_ecommerce_member')->field('username')->where( array('member_id' => $vv['member_id'] ) )->find();

						$vv['username'] = $tmp_if['username'];

						$member_commission_list[$kk] = $vv;
					}

				}



				$value['member_commission_list'] = $member_commission_list;
			}


			$value['head_commission_order_info'] = $head_commission_order_info;


			$value['option_sku'] = D('Seller/Order')->get_order_option_sku($item['order_id'], $value['order_goods_id']);

			//退款商品价格

			$has_refund_money = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $id,'order_goods_id' => $value['order_goods_id'] ) )->find();

			$value['has_refund_money'] = $has_refund_money['has_refund_money'];

			if (defined('ROLE') && ROLE == 'agenter' )
			{
				$supper_info = get_agent_logininfo();
				if($supper_info['id'] != $value['supply_id'])
				{
					continue;
				}
			}

			if( $value['supply_id'] > 0 )
			{
				$supply_info = D('Home/Front')->get_supply_info($value['supply_id']);
				$value['supply_name'] = $supply_info['shopname'];
				$value['supply_type'] = $supply_info['type'] == 1 ? '独立' :'自营';
			}else{
				$value['supply_name'] = '平台自营';
				$value['supply_type'] = '自营';
			}

			$shipping_fare += $value['shipping_fare'];
			$fullreduction_money += $value['fullreduction_money'];
			$voucher_credit += $value['voucher_credit'];
			$total += $value['total'];

			if($item['delivery'] == 'hexiao'){
			    $value['hx_info'] = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('order_id' => $id,'order_goods_id' => $value['order_goods_id'] ) )->find();
			    if(!empty($value['hx_info'])){
			        if($value['hx_info']['is_hexiao_over'] == 1 || $value['hx_info']['is_hexiao_over'] == 2){
						$value['hx_info']['is_can_refund'] = 0;
					}else if($value['hx_info']['is_hexiao_over'] == 0){
						$can_refund_quantity = floor($value['hx_info']['remain_hexiao_count']/$value['hx_info']['one_hexiao_count']);
						if($can_refund_quantity >= 1){
							$value['hx_info']['is_can_refund'] = 1;
						}else{
							$value['hx_info']['is_can_refund'] = 0;
						}
					}
					$record_count = M('eaterplanet_ecommerce_order_goods_saleshexiao_record')->where(array('order_id' => $id,'order_goods_id' => $value['order_goods_id'] ) )->count();
					$value['hx_info']['record_count'] = $record_count;
				}
			}

			$need_order_goods[$key] = $value;
		}

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$item['shipping_fare'] = $shipping_fare;
			$item['fullreduction_money'] = $fullreduction_money;
			$item['voucher_credit'] = $voucher_credit;
			$item['total'] = $total;
		}

		$order_goods = $need_order_goods;



		if (empty($item)) {
			$this->message('抱歉，订单不存在!', $_SERVER['HTTP_REFERER'], 'error');
		}


		$member = M('eaterplanet_ecommerce_member')->where( array('member_id' => $item['member_id'] ) )->find();


		$province_info = D('Home/Front')->get_area_info($item['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($item['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($item['shipping_country_id']);

		$express_info = array();
		if( !empty($item['shipping_method']) )
		{
			$express_info = D('Seller/Express')->get_express_info($item['shipping_method']);
		}
		$this->express_info = $express_info;

		$coupon = array();
		//voucher_id voucher_credit
		//ims_
		if( $item['voucher_id'] > 0 )
		{
			$coupon = array();

			//$coupon = pdo_fetch("select * from ".tablename('eaterplanet_ecommerce_coupon_list')." where uniacid=:uniacid and id=:id ",
			//array(':uniacid' => $_W['uniacid'], ':id' => $item['voucher_id']));
		}

		if( $item['delivery'] == 'localtown_delivery' && ($item['order_status_id'] != 3 && $item['order_status_id'] != 5 ) )
		{
			$item['orderdistribution_order'] = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $item['order_id'] ) )->find();
			if( !empty($item['orderdistribution_order']) && $item['orderdistribution_order'] > 0 )
			{
				if($item['orderdistribution_order']['orderdistribution_id'] > 0){
					$orderdistribution = M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => $item['orderdistribution_order']['orderdistribution_id']  ) )->find();

					$item['orderdistribution_order']['username'] = $orderdistribution['username'];
				}else{
					/*if($item['orderdistribution_order']['orderdistribution_id'] == -1){
						$item['orderdistribution_order']['username'] = "系统默认配送员";
					}else{*/
						if($item['orderdistribution_order']['third_distribution_type'] == 'imdada'){
							$item['orderdistribution_order']['username'] = "达达平台配送";
						}else if($item['orderdistribution_order']['third_distribution_type'] == 'sf'){
							$item['orderdistribution_order']['username'] = "顺丰同城配送";
						}else if($item['orderdistribution_order']['third_distribution_type'] == 'uupt'){
							$item['orderdistribution_order']['username'] = "UU跑腿配送";
						}else if($item['orderdistribution_order']['third_distribution_type'] == 'dianwoda'){
							$item['orderdistribution_order']['username'] = "点我达配送";
						}
					//}
				}
			}
		}

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
			if($supply_can_look_headinfo != 1){
				if( $item['delivery'] == 'pickup' ){

					$shipping_stree = D('Home/Front')->get_area_info($item['shipping_stree_id']);
					$tuan_address = $province_info['name'].$city_info['name'].$area_info['name'];

					$tuan_address2= str_replace($tuan_address, '',$item['shipping_address']);
                    $str = $tuan_address2;
				    strlen($str);
					$i =  mb_strlen($str,"UTF-8");
					$tuan_address2 = D('Seller/Order')->desensitize($tuan_address2,1,$i-2);
					$item['shipping_address'] = $province_info['name'].$city_info['name'].$area_info['name'].$tuan_address2;
				}
			}

		}

		//售后期
		$open_aftersale = D('Home/Front')->get_config_by_name('open_aftersale');
		//天数
		$open_aftersale_time = D('Home/Front')->get_config_by_name('open_aftersale_time');
		$order_settlement =" ";

		//订单状态
		if($item['order_status_id'] ==5 || $item['order_status_id'] ==7 ){
			//已失效
			$order_status = 2;

		}else{
			//待结算
			$order_status = $order_head_status;
			if(($item['order_status_id'] ==6 || $item['order_status_id'] ==11) ){

					if($item['order_status_id'] ==6){
						$order_settlement = $item['receive_time'] + $open_aftersale_time*86400;
					}

					if($item['order_status_id'] ==11){
						$order_settlement = $item['finishtime'] + $open_aftersale_time*86400;
					}
				}



		}
		$this->open_aftersale = $open_aftersale;
		$this->order_status = $order_status;
		$this->open_aftersale_time = $open_aftersale_time;
		$this->order_settlement = $order_settlement;

		$this->id = $id;
		$this->item = $item;
		$this->order_goods = $order_goods;
		$this->member = $member;
		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;


		$history_list = M('eaterplanet_ecommerce_order_history')->where( array('order_id' => $id ) )->order('order_history_id asc')->select();

		$order_status_arr = D('Seller/Order')->get_order_status_name();

		//$order_history['order_status_id'] = 18;

		$order_status_arr[18] = '已结算';
		$order_status_arr[19] = '商品部分退款';
		$order_status_arr[20] = '拒绝商品部分退款';
		$order_status_arr[15] = '预售支付定金';

		foreach( $history_list as  &$val )
		{
		    if( $val[''] == '订单改价' )
            {
                $val['order_status_name'] = 'comment';
            }
			else{
                $val['order_status_name'] = $order_status_arr[$val['order_status_id']];
            }
		}
		unset($val);

		$this->history_list = $history_list;
		$this->order_status_arr = $order_status_arr;

		$is_can_look_headinfo = true;
		$is_can_nowrfund_order = true;
		$is_can_confirm_delivery = true;
		$is_can_confirm_receipt = true;

		$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');

		$supply_can_nowrfund_order = D('Home/Front')->get_config_by_name('supply_can_nowrfund_order');

		$supply_can_confirm_delivery = D('Home/Front')->get_config_by_name('supply_can_confirm_delivery');

		$supply_can_confirm_receipt = D('Home/Front')->get_config_by_name('supply_can_confirm_receipt');

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
			{
				$is_can_look_headinfo = false;
			}
			if( isset($supply_can_nowrfund_order) && $supply_can_nowrfund_order == 2 )
			{
				$is_can_nowrfund_order = false;
			}
			if( isset($supply_can_confirm_delivery) && $supply_can_confirm_delivery == 2 )
			{
				$is_can_confirm_delivery = false;
			}
			if( isset($supply_can_confirm_receipt) && $supply_can_confirm_receipt == 2 )
			{
				$is_can_confirm_receipt = false;
			}
		}


		$s_id = 1 ;

		if(SELLERUID != 1)
		{
			$seller_info = M('seller')->field('s_role_id')->where( array('s_id' => SELLERUID ) )->find();

			$perms_arr = M('eaterplanet_ecommerce_perm_role')->where( array('id' => $seller_info['s_role_id']) )->find();

			$perms1 = str_replace('.','/',$perms_arr['perms2']);

			$perms2 = explode(",", $perms1);

			if(in_array("user/user/index", $perms2)){
				$s_id = 1 ;
			} else {
				$s_id = 0 ;
			}

		}
		$this->s_id = $s_id;

		//预售判断 begin
        $is_presale = 0;
        $presale_result = D('Home/PresaleGoods')->getOrderPresaleInfo( $id );

        $presale_for_ordermoney = 0;
        if( $presale_result['code'] == 0 )
        {
            if( $presale_result['data']['presale_deduction_money'] > 0 )
            {
                $presale_for_ordermoney = $presale_result['data']['presale_for_ordermoney'];
            }
            $is_presale = 1;
        }
        $this->is_presale = $is_presale;
        $this->presale_for_ordermoney = $presale_for_ordermoney;
        //预售end

        //礼品卡begin
        $is_virtualcard = 0;
        $virtualcard_result = D('Seller/VirtualCard')->getVirtualCardOrderInfO( $id );
        if( $virtualcard_result['code'] == 0 )
        {
            $is_virtualcard = 1;
            $this->virtualcard_info = $virtualcard_result['data'];
        }
        $this->is_virtualcard = $is_virtualcard;

        //礼品卡end

		//订单表单信息
		$order_form_data = D('Home/Allform')->getOrderFormInfo($id, $item['member_id']);
		$this->order_form_data = $order_form_data;

		$is_order_note_open = D('Home/Front')->get_config_by_name('order_note_open');

		$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');

		$order_note_name = isset($order_note_name) && !empty($order_note_name) ? $order_note_name :'店名';

		$open_admin_payment = D('Home/Front')->get_config_by_name('open_admin_payment');
		$this->open_admin_payment = $open_admin_payment;

		$localtown_modifypickingname = D('Home/Front')->get_config_by_name('localtown_modifypickingname');
		$localtown_modifypickingname = !empty($localtown_modifypickingname) ? $localtown_modifypickingname: '包装费';

		$this->is_order_note_open = $is_order_note_open;
		$this->order_note_name = $order_note_name;
		$this->is_can_look_headinfo = $is_can_look_headinfo;
		$this->is_can_nowrfund_order = $is_can_nowrfund_order;
		$this->is_can_confirm_delivery = $is_can_confirm_delivery;
		$this->is_can_confirm_receipt = $is_can_confirm_receipt;
		$this->localtown_modifypickingname = $localtown_modifypickingname;
		$this->display();
	}

	//查看物流
	public function express(){
		$_GPC = I('request.');
		$order_id = $_GPC['order_id'];

		$express_list = D('Seller/Order')->goods_express($order_id);

		if($express_list['code'] == 2){
			$code = $express_list['code'];
			$reason = $express_list['Reason'];
			$this->code = $code;
			$this->reason = $reason;
		}


		$new_array = json_decode(json_encode($express_list['order_info']['shipping_traces']), true);
		$new_array = array_reverse($new_array);
		$this->list = $new_array;
		$this->display();
	}

	public function refund_mult()
	{
		$_GPC = I('request.');

		$ids_arr = $_GPC['ids_arr'];

		$cache_key = md5(time().count($ids_arr).'_sendmulutrefund');

		$quene_order_list = array();

		//限定配送数组
		S('_multrefund_'.$cache_key, $ids_arr);

		$this->_GPC = $_GPC;
		$this->cache_key = $cache_key;

		$this->display();
	}


	public function refund_mult_do()
	{
		$_GPC = I('request.');


		$cache_key = $_GPC['cache_key'];

		$quene_order_list = S('_multrefund_'.$cache_key);

		$order_id = array_shift($quene_order_list);

		S('_multrefund_'.$cache_key, $quene_order_list);


		//...
		$order_info =  M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

		$weixin_model = D('Home/Weixin');

		$title = '订单编号：'.$order_info['order_num_alias']." 处理成功，还剩余".count($quene_order_list)."个订单未处理";

		//order_status_id
		if( in_array($order_info['order_status_id'], array(1,4,6,10,11,12,14)) ){

			$res = $weixin_model->refundOrder($order_id);

			if( $res['code'] == 0 )
			{
				$title = '订单编号：'.$order_info['order_num_alias']." 退款失败，还剩余".count($quene_order_list)."个清单未处理";
			}else{

				$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

				$comment = '群接龙批量退款,全额退款';

				$order_history = array();

				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 7;
				$order_history['notify'] = 0;
				$order_history['comment'] =  $comment;
				$order_history['date_added'] = time();

				M('eaterplanet_ecommerce_order_history')->add( $order_history );

				M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( array('order_status_id' => 7)  );

				$refund_all = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $order_id, 'state' => 0 ) )->select();

				if( !empty($refund_all) )
				{
					foreach( $refund_all as $val )
					{
						$ins_data = array();

						$ins_data['ref_id'] = $val['ref_id'];
						$ins_data['order_id'] = $val['order_id'];
						$ins_data['order_goods_id'] = $val['order_goods_id'];
						$ins_data['message'] = '群接龙批量退款 ,退款成功';
						$ins_data['type'] = 2;
						$ins_data['addtime'] = time();

						M('eaterplanet_ecommerce_order_refund_history')->add( $ins_data );

						M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $val['ref_id']) )->save( array('state' => 3) );
					}
				}

				$is_print_admin_cancleorder = D('Home/Front')->get_config_by_name('is_print_admin_cancleorder');

				if( isset($is_print_admin_cancleorder) && $is_print_admin_cancleorder == 1 )
				{
                    sellerLog('群接龙后台取消订单', 2);

                    D('Seller/Printaction')->check_print_order($id,'群接龙后台取消订单');
					D('Seller/Printaction')->check_print_order2($id,'群接龙后台取消订单');
				}
			}

		}

		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}

		//清单编号

		echo json_encode( array('code' => 0, 'msg' => $title ) );
		die();
	}




	public function opreceive()
	{

		$opdata = $this->check_order_data();
		extract($opdata);
		//检查订单是否能确认收货（存在售后未完成无法确认收货）
		$result = D('Seller/Order')->check_order_receive($item['order_id']);
		if($result['status'] == 0){
			show_json(0, array('msg'=>"该订单处于售后处理中，请处理后再确认收货！"));
		}
		if($item['delivery'] == 'localtown_delivery') {
			$result = D('Seller/Order')->check_localtown_order_receive($item['order_id']);
			if ($result['status'] == 0) {
				show_json(0, array('msg' => "该订单未有配送员接单或还未指定配送员，无法确认收货！"));
			}
		}
		//pdo_update('eaterplanet_ecommerce_order', array('order_status_id' => 6, 'receive_time' => time()), array('order_id' => $item['order_id'], 'uniacid' => $_W['uniacid']));
		if( $item['delivery'] == 'localtown_delivery' )
		{
			M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $item['order_id']) )->save( array('state' => 4) );
			D('Home/LocaltownDelivery')->write_distribution_log( $item['order_id'], 0 , 4 , "后台操作，确认收货" );
		}
		D('Seller/Order')->receive_order($item['order_id']);

		M('eaterplanet_ecommerce_order_history')->where( array('order_id' => $item['order_id'],'order_status_id' => 6) )->save( array( 'comment' => '后台操作，确认收货') );
		if( $item['delivery'] == 'localtown_delivery' )
        {
            //D('Home/LocaltownDelivery')->distribution_deliverying_order( $item['order_id'] );
            D('Home/LocaltownDelivery')->distribution_arrived_order( $item['order_id'] ,0 );
        }

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	 public function orderaftersales()
	{
		//$_GPC['order_status_id'] = 12;

		$gpc = I('request.');
		$this->gpc = $gpc;
		$this->_GPC = $gpc;
		$time = I('request.time');

		$starttime = isset($time['start']) ? strtotime($time['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($time['end']) ? strtotime($time['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


		$this->searchfield = I('request.searchfield','');
		$this->keyword = I('request.keyword','');
		$this->searchtime = I('request.searchtime','');
		$this->delivery = I('request.delivery','');
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->time = $time;


		$order_status_arr =  D('Seller/Order')->get_order_status_name();
		$this->order_status_arr = $order_status_arr;

		$need_data = D('Seller/Order')->load_afterorder_list();//改造原来的加载方法

		$this->need_data = $need_data;

		$cur_controller = 'order/order';
		$total = $need_data['total'];
		$total_money = $need_data['total_money'];
		$list = $need_data['list'];
		$pager = $need_data['pager'];
		$all_count = $need_data['all_count'];
		$count_status_1 = $need_data['count_status_1'];
		$count_status_3 = $need_data['count_status_3'];
		$count_status_4 = $need_data['count_status_4'];
		$count_status_5 = $need_data['count_status_5'];
		$count_status_7 = $need_data['count_status_7'];
		$count_status_11 = $need_data['count_status_11'];
		$count_status_14 = $need_data['count_status_14'];

		$this->cur_controller = $cur_controller;
		$this->total = $total;
		$this->total_money = $total_money;
		$this->list = $list;
		$this->pager = $pager;
		$this->all_count = $all_count;
		$this->count_status_1 = $count_status_1;
		$this->count_status_3 = $count_status_3;
		$this->count_status_4 = $count_status_4;
		$this->count_status_5 = $count_status_5;
		$this->count_status_7 = $count_status_7;
		$this->count_status_11 = $count_status_11;
		$this->count_status_14 = $count_status_14;


		$s_id = 1 ;

		if(SELLERUID != 1)
		{
			$seller_info = M('seller')->field('s_role_id')->where( array('s_id' => SELLERUID ) )->find();

			$perms_arr = M('eaterplanet_ecommerce_perm_role')->where( array('id' => $seller_info['s_role_id']) )->find();

			$perms1 = str_replace('.','/',$perms_arr['perms2']);

			$perms2 = explode(",", $perms1);

			if(in_array("user/user/index", $perms2)){
				$s_id = 1 ;
			} else {
				$s_id = 0 ;
			}

		}
		$this->s_id = $s_id;


		$open_feier_print =  D('Home/Front')->get_config_by_name('open_feier_print');

		if( empty($open_feier_print) )
		{
			$open_feier_print = 0;
		}

		$this->open_feier_print = $open_feier_print;

		//退款状态：0申请中，1商家拒绝，2平台介入，3退款成功，4退款失败,5:撤销申请
		$order_refund_state = array(0=>'申请中',1=>'商家拒绝', 2=>'平台介入',3=>'退款成功',4=>'退款失败',5=>'撤销申请');

		$this->order_refund_state = $order_refund_state;

		$is_can_look_headinfo = true;
		$is_can_nowrfund_order = true;

		$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');

		$supply_can_nowrfund_order = D('Home/Front')->get_config_by_name('supply_can_nowrfund_order');

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
			{
				$is_can_look_headinfo = false;
			}
			if( isset($supply_can_nowrfund_order) && $supply_can_nowrfund_order == 2 )
			{
				$is_can_nowrfund_order = false;
			}
		}

		$this->is_can_look_headinfo = $is_can_look_headinfo;
		$this->is_can_nowrfund_order = $is_can_nowrfund_order;

		$this->display();
	}

	public function oprefund_doform()
	{
		$_GPC = I('request.');

		$ref_id = $_GPC['ref_id'];

		$refund_info = M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id ) )->find();

		$this->ref_id = $ref_id;
		$this->refund_info = $refund_info;

		if (IS_POST) {

			$order_history = array();
			$order_history['order_id'] = $refund_info['order_id'];

			$order_history['order_status_id'] = 0;
			$order_history['notify'] = 0;
			$order_history['comment'] = '';
			$order_history['date_added'] = time();


			$remarkrefund = $_GPC['remarkrefund'];
			$is_forbidden = $_GPC['is_forbidden'];

			$cansub = $_GPC['cansub'];

			if( isset($_GPC['is_forbidden']) && $_GPC['is_forbidden'] > 0 )
			{
				//添加
				$refund_disable = M('eaterplanet_ecommerce_order_refund_disable')->where( array('ref_id' => $ref_id ) )->find();

				if( empty($refund_disable) )
				{
					//插入
					$ins_data = array();
					$ins_data['ref_id'] = $ref_id;
					$ins_data['order_id'] = $refund_info['order_id'];
					$ins_data['order_goods_id'] = $refund_info['order_goods_id'];
					$ins_data['addtime'] = time();

					M('eaterplanet_ecommerce_order_refund_disable')->add($ins_data);
				}

			}else{
				//删除
				M('eaterplanet_ecommerce_order_refund_disable')->where( array('ref_id' => $ref_id ) )->delete();

			}

			if($cansub == 1)
			{
				//确认退款 remarkrefund

				$weixin_model = D('Home/Weixin');

				$order_refund = M('eaterplanet_ecommerce_order_refund')->field('ref_money,real_refund_quantity,ref_shipping_fare')->where( array('ref_id' => $ref_id ) )->find();


				//$order_info['total']

 				$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $refund_info['order_id'] ) )->find();

				if( $order_info['type'] == 'integral' || $order_info['type'] == 'normal' )
				{
					//M('eaterplanet_ecommerce_order')->where( array('order_id' => $refund_info['order_id'] ) )->save( array('shipping_fare' => $order_refund['ref_shipping_fare'] ) );

					if( !empty($refund_info['order_goods_id']) && $refund_info['order_goods_id'] > 0 )
					{
					    $order_goods =  M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' =>$refund_info['order_goods_id'] ) )->find();

					    M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' =>$refund_info['order_goods_id'] ) )->save( array('shipping_fare' => $order_refund['ref_shipping_fare'] ) );
					}
				}

				if( $order_refund['real_refund_quantity'] > 0 )
				{
					//部分商品退款
					$res = $weixin_model->refundOrder($refund_info['order_id'], $order_refund['ref_money'],0,$refund_info['order_goods_id'],1, $order_refund['real_refund_quantity'],1);
				}else{

					$res = $weixin_model->refundOrder($refund_info['order_id'], $order_refund['ref_money'],0,$refund_info['order_goods_id']);

				}
				if( $order_info['type'] == 'integral' || $order_info['type'] == 'normal')
				{
					//M('eaterplanet_ecommerce_order')->where( array('order_id' => $refund_info['order_id'] ) )->save( array('shipping_fare' => $order_info['shipping_fare'] ) );

					if( !empty($refund_info['order_goods_id']) && $refund_info['order_goods_id'] > 0 )
					{
					    M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' =>$refund_info['order_goods_id'] ) )->save( array('shipping_fare' => $order_goods['shipping_fare'] ) );
					}

				}

				if($res['code'] == 1)
				{

					$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $refund_info['order_goods_id'] , 'order_id' => $refund_info['order_id'] ) )->find();

					if( $order_refund['real_refund_quantity'] <= 0 )
					{
						$order_refund['real_refund_quantity'] =  $order_goods['quantity'] - $order_goods['has_refund_quantity'];

					}

					$pay_total_money = $order_goods['total']-$order_goods['voucher_credit']-$order_goods['fullreduction_money'] - $order_goods['score_for_money'];

					$buy_usescore = D('Seller/Commonorder')->get_order_goods_buyscore($refund_info['order_id'], $refund_info['order_goods_id']);
					if( !empty($buy_usescore) && $buy_usescore > 0 )
					{
					    $res_score=  ceil( $buy_usescore * ( $order_refund['real_refund_quantity']/ $order_goods['quantity']) );

					    D('Seller/Commonorder')->refund_order_goods_intrgral( $refund_info['order_id'], $refund_info['order_goods_id'] ,$res_score );
					}

					if($order_refund['ref_money']-$order_refund['ref_shipping_fare'] <= 0){
						$refund_money = 0;
					}else{
						$refund_money = $order_refund['ref_money']-$order_refund['ref_shipping_fare'];
					}

					if( $order_info['type'] == 'integral'){
						$refund_money = $order_refund['ref_money'];
					}

					//退款配送费问题
					  D('Seller/Commonorder')->ins_order_goods_refund($refund_info['order_id'], $refund_info['order_goods_id'],$pay_total_money,$order_refund['real_refund_quantity'], $order_refund['real_refund_quantity'],$refund_money,1,$order_refund['ref_shipping_fare']);

					//如果这个商品没有数量了。就改变他的状态为已退款的状态
					$new_total_quantity = D('Seller/Commonorder')->get_order_goods_quantity($refund_info['order_id'],$refund_info['order_goods_id'] );
					$ref_count = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $refund_info['order_id'], 'state' => 0 ) )->count();

					if( $new_total_quantity <=0 && $ref_count <= 1)
					{
						D('Seller/Commonorder')->check_refund_order_goods_status($refund_info['order_id'],$refund_info['order_goods_id'], $order_refund['ref_money'],1,$order_refund['real_refund_quantity'],  $order_refund['real_refund_quantity'],1,'平台同意退款,');

					}else{

						$order_history = array();
						$order_history['uniacid'] = 0;
						$order_history['order_id'] = $refund_info['order_id'];
						$order_history['order_goods_id'] = $refund_info['order_goods_id'];
						$order_history['notify'] = 0;
						$order_history['order_status_id'] = 19;

						if( $order_info['type'] == 'integral' )
						{
							$order_history['comment'] =  '平台同意退款，子订单退款，退款商品：'.$order_refund['real_refund_quantity'] .'个，退库存/销量数量'.$order_refund['real_refund_quantity'].'个，退款积分'.$order_refund['ref_money'].'积分';

							if( !empty($order_refund['ref_shipping_fare']) ){
								$order_history['comment'] .= '. 退配送费：'.$order_refund['ref_shipping_fare'].'元';

							}


						}else{
							$order_history['comment'] =  '平台同意退款，子订单退款，退款商品：'.$order_refund['real_refund_quantity'] .'个，退库存/销量数量'.$order_refund['real_refund_quantity'].'个，退款金额'.$order_refund['ref_money'].'元';
						}
						$order_history['date_added'] = time();

						M('eaterplanet_ecommerce_order_history')->add( $order_history );

						M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $refund_info['order_goods_id'] ) )->save( array('is_refund_state' => 0 ) );
					}


					//D('Seller/Commonorder')->ins_order_goods_refund($order_id, $order_goods_id,$pay_total_money,$real_refund_quantity, $refund_quantity,$refund_money, $is_back_sellcount);


					$order_refund_history = array();
					$order_refund_history['ref_id'] = $ref_id;
					$order_refund_history['order_id'] = $refund_info['order_id'];
					$order_refund_history['order_goods_id'] = $refund_info['order_goods_id'];

					$order_refund_history['message'] = '平台同意退款'.' '.$remarkrefund.'  ,退款成功';
					$order_refund_history['type'] = 2;
					$order_refund_history['addtime'] = time();

					M('eaterplanet_ecommerce_order_refund_history')->add( $order_refund_history );

					//通过 eaterplanet_ecommerce_order_refund
                    sellerLog('同意'.$remarkrefund.'退款成功操作', 2);

                    M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id) )->save( array('state' => 3,'modify_time' => time(),'remarkrefund' => $remarkrefund) );

					show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
				}else{

					if( empty($res['msg']) )
					{
						$res['msg'] = '请检查商户号与cert证书';
					}

					$order_refund_history = array();
					$order_refund_history['ref_id'] = $ref_id;
					$order_refund_history['order_id'] = $refund_info['order_id'];
					$order_refund_history['order_goods_id'] = $refund_info['order_goods_id'];

					$order_refund_history['message'] = '平台同意退款'.' '.$remarkrefund.'  ,但是退款失败：'.$res['msg'];
					$order_refund_history['type'] = 2;
					$order_refund_history['addtime'] = time();

					M('eaterplanet_ecommerce_order_refund_history')->add( $order_refund_history );

					show_json(0, array('message' => $res['msg']) );
				}

			}else if($cansub == 2){
				//拒绝退款
				$order_refund_history = array();
				$order_refund_history['ref_id'] = $ref_id;
				$order_refund_history['order_id'] = $refund_info['order_id'];
				$order_refund_history['order_goods_id'] = $refund_info['order_goods_id'];

				$order_refund_history['message'] = '平台拒绝退款'.' '.$remarkrefund;
				$order_refund_history['type'] = 2;
				$order_refund_history['addtime'] = time();

				M('eaterplanet_ecommerce_order_refund_history')->add( $order_refund_history );

				M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id) )->save( array('state' => 1,'modify_time' => time(),'remarkrefund' => $remarkrefund ) );

				$item = M('eaterplanet_ecommerce_order')->field('order_status_id,last_refund_order_status_id')->where( array('order_id' => $refund_info['order_id'] ) )->find();

				$order_history['order_status_id'] = 20;

				//如果是部分退款，那么就不是12了
				if( $item['order_status_id'] == 12)
				{
					$order_history['order_status_id'] = 12;
					if( $item['last_refund_order_status_id'] > 0 )
					{
						$order_history['order_status_id'] = $item['last_refund_order_status_id'];

						M('eaterplanet_ecommerce_order')->where( array('order_id' => $refund_info['order_id'] ) )->save( array('order_status_id' => $item['last_refund_order_status_id']) );

						$order_history['order_status_id'] = $item['last_refund_order_status_id'];
						$order_history['comment'] = '商家拒绝退款，订单回退上一状态';
					}else{
						$order_history['comment'] = '商家拒绝退款';
					}
				}

				if( !empty($refund_info['order_goods_id']) && $refund_info['order_goods_id'] > 0 )
				{

					M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $refund_info['order_goods_id'] ) )->save( array('is_refund_state' => 0) );

				}

				//拒绝  order_status_id

                sellerLog($order_history['comment'].'操作', 2);

                M('eaterplanet_ecommerce_order_history')->add( $order_history );

				show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

			}

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}



		$this->display();
	}


	public function oprefund()
	{
		$_GPC = I('request.');

		$opdata = $this->check_order_data();
		extract($opdata);


		$ref_id = $_GPC['ref_id'];

		$ref_info = M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id ) )->find();


		$step_array = array();
		$step_array[1]['step'] = 1;
		$step_array[1]['title'] = '客户发起退款';
		$step_array[1]['time'] = $ref_info['addtime'];
		$step_array[1]['done'] = 1;
		$step_array[2]['step'] = 2;
		$step_array[2]['title'] = '平台处理维权申请';
		$step_array[2]['done'] = 0;
		$step_array[2]['time'] = '';
		$step_array[3]['step'] = 3;
		$step_array[3]['done'] = 0;
		$step_array[3]['title'] = '商家处理退款完成';
		$step_array[3]['time'] = '';


		$ref_id = I('request.ref_id');
		$this->ref_id = $ref_id;

		$this->ref_info = $ref_info;

		$order_goods_id = $ref_info['order_goods_id'];

		if( !empty($order_goods_id) && $order_goods_id > 0 )
		{
			$goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $order_goods_id, 'order_id' => $id ) )->select();
		}else{

			$goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $id ) )->select();
		}


		$total_fare = 0;

		$total_shipping_fare = 0;
		$total_voucher_credit =0;
		$total_fullreduction_money = 0;
		$total_total_fare = 0;

		$total_score_for_money = 0;

		/**
			php echo number_format($item['total']+$item['shipping_fare']-$item['voucher_credit']-$item['fullreduction_money'],2)
		**/


		foreach($goods as &$value)
		{
			$value['option_sku'] = D('Seller/Order')->get_order_option_sku($item['order_id'], $value['order_goods_id']);


			$total_fare += $value['total'];
			$total_shipping_fare += $value['shipping_fare'];
			$total_voucher_credit += $value['voucher_credit'];
			$total_fullreduction_money += $value['fullreduction_money'];
			$total_score_for_money += $value['score_for_money'];

			$total_total_fare += $value['total']+$value['shipping_fare']-$value['voucher_credit']-$value['fullreduction_money']-$value['score_for_money'];

		}
		$this->goods = $goods;

		$this->total_fare = $total_fare;
		$this->total_shipping_fare = $total_shipping_fare;
		$this->total_voucher_credit = $total_voucher_credit;
		$this->total_fullreduction_money = $total_fullreduction_money;
		$this->total_score_for_money = $total_score_for_money;
		$this->total_total_fare = $total_total_fare;

		unset($r);
		$item['goods'] = $goods;

		$member = M('eaterplanet_ecommerce_member')->where( array('member_id' => $item['member_id']) )->find();


		$this->member = $member;


		$express_list = array();

		$r_type = array(1=>'仅退款',2 => '退款退货');
		//ims_
		$this->r_type =$r_type;

		$order_refund = M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id) )->find();


		$this->order_refund = $order_refund;

		if($order_refund['modify_time'] != 0 && $order_refund['state']== 3)
		{
			$step_array[3]['done'] = 1;
			$step_array[3]['time'] = $order_refund['modify_time'];
		}



		$refund_imgs = M('eaterplanet_ecommerce_order_refund_image')->where( array('ref_id' => $order_refund['ref_id'] ) )->select();

		$this->refund_imgs = $refund_imgs;

		/**
		if( !empty($order_goods_id) && $order_goods_id > 0 )
		{
			$order_refund_history = M('eaterplanet_ecommerce_order_refund_history')->where( array('order_id' =>$id,'order_goods_id' => $order_goods_id ) )->order('addtime asc')->select();
		}
		else{

			$order_refund_history = M('eaterplanet_ecommerce_order_refund_history')->where( array('order_id' =>$id ) )->order('addtime asc')->select();
		}
		**/
		$order_refund_history = M('eaterplanet_ecommerce_order_refund_history')->where( array('ref_id' => $ref_id ) )->order('addtime asc')->select();

		$i = 1;

		foreach($order_refund_history as $key => $val)
		{
			if( $i == 1 && $val['type'] == 2 )
			{
				$step_array[2]['done'] = 1;
				$step_array[2]['time'] = time();
				$i++;
			}
			$val['type'] = $val['type'] == 1 ?'用户反馈':'商家反馈';


			switch($val['type'])
			{
				case 1:
						$val['type'] = '用户反馈';
						break;
				case 2:
						$val['type'] = '商家反馈';
						break;
				case 3:
						$val['type'] = '平台反馈';
						break;

			}


			$order_refund_history_image = M('eaterplanet_ecommerce_order_refund_history_image')->where( array('orh_id' => $val['id']) )->select();

			if(!empty($order_refund_history_image))
			{
				foreach($order_refund_history_image as $kk => $vv)
				{
					$vv['thumb_image'] =  resize ($vv['image'], 200,200);
					$order_refund_history_image[$kk] = $vv;
				}
			}
			$val['order_refund_history_image'] = $order_refund_history_image;
			$order_refund_history[$key] = $val;
		}

		$this->step_array = $step_array;

		$this->order_refund_history = $order_refund_history;
		$this->item = $item;
		$this->display();
	}


	public function oprefund_submit()
	{
		$gpc = I('request.');

		$opdata = $this->check_order_data();
		extract($opdata);

		if (IS_POST) {

			$id = $gpc['id'];
			//refundstatus  message refundcontent

			$refundstatus = $gpc['refundstatus'];
			$message = $gpc['message'];
			$refundcontent = $gpc['refundcontent'];

			$ref_id = $gpc['refundid'];

			/**
			int(37)
			string(1) "1"
			string(0) ""
			string(15) "天天来退货"
			**/
			//1 ,3

			$comment = '';

			switch( $refundstatus )
			{
				case 1:
					$comment = $refundcontent;
					break;
				case 3:
					$comment = $message;
					break;
			}

			$ref_info = M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id ) )->find();

			$result = array('code' =>1);

			$order_refund_history = array();
			$order_refund_history['order_id'] = $ref_info['order_id'];
			$order_refund_history['order_goods_id'] = $ref_info['order_goods_id'];
			$order_refund_history['message'] = htmlspecialchars($comment);
			$order_refund_history['type'] = 2;
			$order_refund_history['addtime'] = time();

			M('eaterplanet_ecommerce_order_refund_history')->add($order_refund_history);



			$order_history = array();
			$order_history['order_id'] = $id;
			$order_history['order_status_id'] = 0;
			$order_history['notify'] = 0;
			$order_history['comment'] = '';
			$order_history['date_added'] = time();

			if($refundstatus ==1)
			{
				M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id) )->save( array('state' => 1) );

				//id item $order_info  $item

				$item = M('eaterplanet_ecommerce_order')->field('order_status_id,last_refund_order_status_id')->where( array('order_id' => $id ) )->find();


				//如果是部分退款，那么就不是12了
				if( $item['order_status_id'] == 12)
				{
					$order_history['order_status_id'] = 12;
					if( $item['last_refund_order_status_id'] > 0 )
					{
						$order_history['order_status_id'] = $item['last_refund_order_status_id'];

						M('eaterplanet_ecommerce_order')->where( array('order_id' => $id) )->save(  array('order_status_id' =>$item['last_refund_order_status_id'] ) );

						$order_history['order_status_id'] = $item['last_refund_order_status_id'];
					}

				}

				if( !empty($ref_info['order_goods_id']) && $ref_info['order_goods_id'] > 0 )
				{
						M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $ref_info['order_goods_id'] ) )->save( array('is_refund_state' =>0 ) );
				}

				//拒绝  order_status_id
				$order_history['comment'] = '商家拒绝退款，订单回退上一状态';

				M('eaterplanet_ecommerce_order_history')->add( $order_history );

				show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
			} else {

				$weixin_model = D('Home/Weixin');

				$order_refund = M('eaterplanet_ecommerce_order_refund')->field('ref_money')->where( array('ref_id' => $ref_id) )->find();


				$res = $weixin_model->refundOrder($id, $order_refund['ref_money'],0,$ref_info['order_goods_id']);


				//array('code' => 0, 'msg' => $res['err_code_des']);
				if($res['code'] == 1)
				{
					$order_history['order_status_id'] = 7;
					$order_history['comment'] = '商家同意退款';

					M('eaterplanet_ecommerce_order_history')->add( $order_history );

					//通过 eaterplanet_ecommerce_order_refund

					M('eaterplanet_ecommerce_order_refund')->where( array('ref_id' => $ref_id) )->save( array('state' => 3) );

					show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
				}else{
					if( empty($res['msg']) )
					{
						$res['msg'] = '请检查商户号与cert证书';
					}

					show_json(0, array('message' => $res['msg']) );
				}
			}


		}

		$r_type = array(1=>'仅退款',2 => '退款退货');
		//ims_

		$order_refund = M('eaterplanet_ecommerce_order_refund')->where( array('order_id' => $id) )->find();

		$refund_imgs = M('eaterplanet_ecommerce_order_refund_image')->where( array('ref_id' => $order_refund['ref_id']) )->find();

		$order_refund_history = M('eaterplanet_ecommerce_order_refund_history')->where( array('order_id' => $id)  )->order('addtime asc')->select();

		foreach($order_refund_history as $key => $val)
		{
			$val['type'] = $val['type'] == 1 ?'用户反馈':'商家反馈';
			switch($val['type'])
			{
				case 1:
						$val['type'] = '用户反馈';
						break;
				case 2:
						$val['type'] = '商家反馈';
						break;
				case 3:
						$val['type'] = '平台反馈';
						break;

			}


			$order_refund_history_image = M('eaterplanet_ecommerce_order_refund_history_image')->where( array('orh_id' => $val['id']) )->select();

			if(!empty($order_refund_history_image))
			{
				foreach($order_refund_history_image as $kk => $vv)
				{
					$vv['thumb_image'] =  resize ($vv['image'], 200,200);
					$order_refund_history_image[$kk] = $vv;
				}
			}
			$val['order_refund_history_image'] = $order_refund_history_image;
			$order_refund_history[$key] = $val;
		}

		$this->order_refund = $order_refund;
		$this->r_type = $r_type;
		$this->item = $item;
		$this->display();
	}

	public function ordercomment_gift()
	{


		if (IS_POST) {
			$data = array();

			$data = I('request.data');
			$data['open_comment_gift'] = trim($data['open_comment_gift']);
			$data['comment_gift_score'] = trim($data['comment_gift_score']);
			$data['comment_gift_time'] = trim($data['comment_gift_time']);
			$data['comment_gift_max_score'] = trim($data['comment_gift_max_score']);
			$data['comment_gift_publish'] = trim($data['comment_gift_publish']);

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();
		if(empty($data['comment_gift_publish'])){
			$data['comment_gift_publish'] = "​参与好评有礼活动，好评成功并且后台审核通过以后可获得获得活动积分";
		}
		$this->data = $data;
		$this->display();
	}

	public function ordercomment_config()
	{


		if (IS_POST) {
			$data = array();

			$data = I('request.data');
			$data['open_comment_shenhe'] = trim($data['open_comment_shenhe']);

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;
		$this->display();
	}

	public function ordercomment()
	{

		$condition = ' 1 ';//0
		$pindex = I('request.page', 1);
		$psize = 20;


		$keyword = I('request.keyword');
		$this->keyword = $keyword;

		if (!empty($keyword)) {
			$condition .= ' and content like '.'"%' . $keyword . '%" ';
		}



		$label = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_order_comment
				WHERE  " . $condition . ' order by comment_id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);


		$total = M('eaterplanet_ecommerce_order_comment')->where($condition)->count();

		$pager = pagination2($total, $pindex, $psize);

		$this->pager = $pager;
		$this->total = $total;
		$this->label = $label;
		$this->display();
	}

	public function deletecomment()
	{

		$id = I('request.id');

		if (empty($id)) {
			$ids = I('request.ids');
			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}

		$items = M('eaterplanet_ecommerce_order_comment')->field('comment_id')->where( array('comment_id' => array('in',$id) ) )->select();

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('eaterplanet_ecommerce_order_comment')->where( array('comment_id' => $item['comment_id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function commentstate()
	{

		$id =  I('request.id');

		if (empty($id)) {
			$ids = I('request.ids');
			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}

		$items = pdo_fetchall('SELECT comment_id FROM ' . tablename('eaterplanet_ecommerce_order_comment') . '
					WHERE comment_id in( ' . $id . ' ) ' );

		$items = M('eaterplanet_ecommerce_order_comment')->where( array() )->select();

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			pdo_update('eaterplanet_ecommerce_order_comment', array('state' => intval($_GPC['state'])), array('comment_id' => $item['comment_id']));
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function config()
	{
		if (IS_POST) {
			$data = array();

			$data = I('request.data');

			$data['open_admin_payment'] = trim($data['open_admin_payment']);
			$data['open_auto_receive'] = trim($data['open_auto_receive']);
			$data['shop_limit_buy_distance'] = trim($data['shop_limit_buy_distance']);
			$data['is_open_order_message'] = trim($data['is_open_order_message']);
			$data['is_hidden_orderlist_phone'] = trim($data['is_hidden_orderlist_phone']);
			$data['order_pay_after_share_img'] = save_media($data['order_pay_after_share_img']);

			//-----begin

			if( $data['shop_limit_buy_distance'] == 1)
			{
				if( empty($data['shop_buy_distance']) || $data['shop_buy_distance'] <= 0  )
				{
					show_json(0, array('msg' => '开启限制购买距离，购买距离不能为空') );
				}
			}
			if( $data['open_auto_delete'] == 1)
			{
				if( empty($data['auto_cancle_order_time']) || $data['auto_cancle_order_time'] <= 0  )
				{
					show_json(0, array('msg' => '开启自动取消订单，自动取消订单时间不能为空') );
				}
			}

			//open_aftersale
			if( $data['open_auto_recive_order'] == 1)
			{
				if( empty($data['auto_recive_order_time']) || $data['auto_recive_order_time'] <= 0  )
				{
					show_json(0, array('msg' => '开启系统自动签收，自动签收天数不能为空') );
				}
			}


			$open_aftersale = isset($data['open_aftersale']) ? $data['open_aftersale']:0;

			$open_aftersale_time = isset($data['open_aftersale_time']) ? $data['open_aftersale_time']:0;

			if( $open_aftersale == 1 && ($open_aftersale_time ==0 || empty($open_aftersale_time) ) )
			{
				show_json(0, array('msg' => '开启售后期，请填写售后期天数') );
			}


			if( $data['open_redis_server'] == 1)
			{
				if( empty($data['redis_host']))
				{
					show_json(0, array('msg' => '开启redis服务，redis-host不能为空') );
				}
				if( empty($data['redis_port']))
				{
					show_json(0, array('msg' => '开启redis服务，redis-port不能为空') );
				}
			}


			$open_feier_print = isset($data['open_feier_print']) ? $data['open_feier_print']:0;
			if(empty($open_feier_print) || $open_feier_print == 0)
			{
				$data['open_feier_print'] = $open_feier_print;

			}else if($open_feier_print == 1){
				$feier_print_sn = isset($data['feier_print_sn']) ? $data['feier_print_sn']:'';
				$feier_print_key = isset($data['feier_print_key']) ? $data['feier_print_key']:'';

				$data['open_feier_print'] = $open_feier_print;
				$data['feier_print_sn'] = $feier_print_sn;
				$data['feier_print_key'] = $feier_print_key;

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
				//...todo测试订单自动打印
			}else if($open_feier_print == 2){
				$yilian_machine_code = isset($data['yilian_machine_code']) ? $data['yilian_machine_code']:'';

				$yilian_msign = isset($data['yilian_msign']) ? $data['yilian_msign']:'';
				$yilian_client_id = isset($data['yilian_client_id']) ? $data['yilian_client_id']:'';
				$yilian_client_key = isset($data['yilian_client_key']) ? $data['yilian_client_key']:'';


				$data['open_feier_print'] = $open_feier_print;
				$data['yilian_machine_code'] = $yilian_machine_code;
				$data['yilian_msign'] = $yilian_msign;
				$data['yilian_client_id'] = $yilian_client_id;
				$data['yilian_client_key'] = $yilian_client_key;

				$yilian_client_id_old = D('Home/Front')->get_config_by_name('yilian_client_id');

				$yilian_machine_code_old = D('Home/Front')->get_config_by_name('yilian_machine_code');

				$yilian_msign_old = D('Home/Front')->get_config_by_name('yilian_msign');



				if(true || $yilian_client_id != $yilian_client_id_old || $yilian_machine_code_old != $yilian_machine_code || $yilian_msign_old != $yilian_msign)
				{

					//开始添加打印机
					//printaction
					$print_model =  D('Seller/Printaction');

					$res = $print_model->addyilianyunprinter($yilian_client_id,$yilian_client_key,$yilian_machine_code, $yilian_msign );

					if($res != 0)
					{
						show_json(0, array('msg' => '添加易联云打印机失败！'));
					}
				}

				//...todo测试订单自动打印

			}
			//----end

			$data['is_print_auto'] = $data['is_print_auto'] == 1 ? 0 : 1;
			$data['is_print_cancleorder'] = isset($data['is_print_cancleorder']) ? $data['is_print_cancleorder'] : 0;
			$data['is_print_admin_cancleorder'] = isset($data['is_print_admin_cancleorder']) ? $data['is_print_admin_cancleorder'] : 0;
			$data['is_print_dansupply_order'] = isset($data['is_print_dansupply_order']) ? $data['is_print_dansupply_order'] : 0;




			//----------redis begin
			$data['open_redis_server'] = intval($data['open_redis_server']);
			if($data['open_redis_server'] == 1 && !class_exists('Redis')){
				$data['open_redis_server'] = 0;
			}
			//----------redis end

			$data['is_has_refund_deliveryfree'] = isset($data['is_has_refund_deliveryfree']) ? $data['is_has_refund_deliveryfree'] : 0;


			//订单自动发货
			$data['is_localtown_auto_delivery'] = isset($data['is_localtown_auto_delivery']) ? $data['is_localtown_auto_delivery'] : 0;
			$data['is_communityhead_auto_delivery'] = isset($data['is_communityhead_auto_delivery']) ? $data['is_communityhead_auto_delivery'] : 0;
			$data['is_communityhead_auto_service'] = isset($data['is_communityhead_auto_service']) ? $data['is_communityhead_auto_service'] : 0;
			$data['is_ziti_auto_delivery'] = isset($data['is_ziti_auto_delivery']) ? $data['is_ziti_auto_delivery'] : 0;
			$data['is_ziti_auto_service'] = isset($data['is_ziti_auto_service']) ? $data['is_ziti_auto_service'] : 0;

			D('Seller/Config')->update($data);

			//将商品库存写入redis
			if($data['open_redis_server'] == 1 )
				D('Seller/Redisorder')->sysnc_allgoods_total();

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;

		$this->display();

	}

	 public function opclose()
	{

		$opdata = $this->check_order_data();
		extract($opdata);

		if ($item['order_status_id'] == 5) {
			show_json(0, '订单已关闭，无需重复关闭！');
		}
		 else if (3 != $item['order_status_id']) {
			show_json(0, '订单已付款，不能关闭！');
		}


		if (IS_POST) {


			//load_model_class('frontorder')->cancel_order($item['order_id']);
			D('Home/Frontorder')->cancel_order($item['order_id'], false, '后台操作，取消订单');


			/**

			$time = time();

			pdo_update('eaterplanet_ecommerce_order', array('order_status_id' => 5, 'canceltime' => $time),  array('order_id' => $item['order_id'], 'uniacid' => $_W['uniacid'])) ;

			//'remarkclose' => $_GPC['remark']),

			$history_data = array();
			$history_data['uniacid'] = $_W['uniacid'];
			$history_data['order_id'] = $item['order_id'];
			$history_data['order_status_id'] = 5;
			$history_data['notify'] = 0;
			$history_data['comment'] = '后台操作，取消订单' ;
			$history_data['date_added'] = time();

			pdo_insert('eaterplanet_ecommerce_order_history', $history_data);

			**/

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

		}

		$this->item = $item;
		 $this->display();
	}

	//--begin
	public function ordersendall()
	{
		$_GPC = I('request.');

		$express_list = D('Seller/Express')->load_all_express();

		$this->_GPC = $_GPC;

		if( IS_POST )
		{

			$type = isset($_GPC['type']) && !empty($_GPC['type']) ? $_GPC['type']:'normal';


			$fext = substr($_FILES['excelfile']['name'], strrpos($_FILES['excelfile']['name'], '.') + 1);




			$express = trim($_GPC['express']);
			$expresscom = trim($_GPC['expresscom']);


			if( $fext == 'csv' )
			{
				$file_name = $_FILES['excelfile']['tmp_name'];
				$file = fopen($file_name,'r');

				$rows = array();
				$i =0;
				while ($data = fgetcsv($file)) {

					$rows[] = eval('return '.iconv('gbk','utf-8',var_export($data,true)).';');

				}

				//var_dump( $rows );
				//die();
			}else{

				$rows = D('Seller/Excel')->import('excelfile');
			}

			$num = count($rows);
			$time = time();

			$express_arr = array();

			foreach($express_list as $val)
			{
				$express_arr[ $val['id'] ] = $val['name'];
			}

			$i = 0;
			$err_array = array();

			$quene_order_list = array();

			$cache_key = md5(time().count($rows));

			$j =0;
			foreach ($rows as $rownum => $col)
			{
				$order_id = trim($col[0]);

				if (empty($order_id)) {
					$err_array[] = $order_id;
					continue;
				}
				if($j == 0)
				{
					$j++;
					continue;
				}

				$quene_order_list[]  = array('order_num_alias' => $order_id , 'shipping_no' => $col[1], 'express' => $express,'expresscom' => $expresscom );

			}

			S('_orderquene_'.$cache_key, $quene_order_list);

			$this->cache_key = $cache_key;
			$this->type = $type;

			$this->display('Order/oploadexcelorder');
			die();
		}




		$this->express_list = $express_list;
		$this->type = I('request.type');
		$this->display();
	}

	public function do_order_quene()
	{
		$_GPC = I('request.');

		$type = $_GPC['type'];
		$cache_key = $_GPC['cache_key'];

		$quene_order_list = S('_orderquene_'.$cache_key);

		$tmp_info = array_shift($quene_order_list);

		S('_orderquene_'.$cache_key, $quene_order_list);

		$express = $tmp_info['express'];
		$expresscom = $tmp_info['expresscom'];
		$shipping_no = $tmp_info['shipping_no'];

		$tmp_info['order_num_alias'] = trim($tmp_info['order_num_alias']);


		$tmp_info['order_num_alias'] =  preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$tmp_info['order_num_alias']);



		//$rows = D('Seller/Excel')->import('excelfile');

		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_num_alias' => $tmp_info['order_num_alias'] ) )->find();


		if(!empty($order_info) && $order_info['order_status_id'] == 1)
		{
			if( $type == 'mult' && $order_info['delivery'] == 'express' )
			{
				$ex_info = D('Seller/Express')-> get_express_info($express);


				$data = array();

				$data['express_time'] = time();

				$data['order_status_id'] = 4;
				$data['shipping_no'] = $shipping_no;
				$data['shipping_method'] = $express;
				$data['dispatchname'] = $ex_info['name'];

				M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id'] ) )->save( $data );

				$history_data = array();

				$history_data['order_id'] = $order_info['order_id'];
				$history_data['order_status_id'] = 4;
				$history_data['notify'] = 0;
				$history_data['comment'] = '订单快递已发货，后台导入批量发货';
				$history_data['date_added'] = time();

				M('eaterplanet_ecommerce_order_history')->add($history_data);
				//TODO..发送已发货的模板消息
				D('Home/Frontorder')->send_order_operate($order_info['order_id']);
			}
			else {

				if($order_info['delivery'] != 'express')
				{


					/*if($order_info['delivery'] == 'localtown_delivery'){

						M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id']) )->save( array('order_status_id' => 4,'express_time' => time(),  'express_tuanz_time' => time()) );

						//todo ... send member msg goods is ing

						$history_data = array();
						$history_data['order_id'] = $order_info['order_id'];
						$history_data['order_status_id'] = 4;
						$history_data['notify'] = 0;
						$history_data['comment'] = '订单配送中，使用表格发货';
						$history_data['date_added'] = time();

						M('eaterplanet_ecommerce_order_history')->add( $history_data );

						D('Home/LocaltownDelivery')->change_distribution_order_state( $order_info['order_id'], 0, 1);

						M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_info['order_id']) )->save( array('delivery_type' => 1) );

						D('Home/Frontorder')->send_order_operate($order_info['order_id']);
						//给配送员发送公众号消息
						$count = D('Seller/Redisorder')->set_distribution_delivery_message($order_info['order_id']);

					}else{*/
					if($order_info['delivery'] != 'localtown_delivery' && $order_info['delivery'] != 'hexiao') {
						$data = array();

					$data['express_time'] = time();

					$data['order_status_id'] = 14;

					M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id'] ) )->save($data);

					$history_data = array();
					$history_data['order_id'] = $order_info['order_id'];
					$history_data['order_status_id'] = 14;
					$history_data['notify'] = 0;
					$history_data['comment'] = '订单配送中，使用表格发货';
					$history_data['date_added'] = time();

						M('eaterplanet_ecommerce_order_history')->add($history_data);
					}
					//}
				}
			}
			//TODO...发送已经发货给团长的消息通知

		}
		if($type =='mult_send_tuanz' && $order_info['delivery'] != 'express' && $order_info['order_status_id'] == 14  )
		{
			//订单批量团长签收  2019012749451499751

			$history_data = array();
			$history_data['order_id'] = $order_info['order_id'];
			$history_data['order_status_id'] = 4;
			$history_data['notify'] = 0;
			$history_data['comment'] = '后台批量导入发货到团长';
			$history_data['date_added'] = time();

			M('eaterplanet_ecommerce_order_history')->add( $history_data );

			D('Home/Frontorder')->send_order_operate($order_info['order_id']);

		}

		if($type =='mult_member_receive_order' && $order_info['order_status_id'] == 4  )
		{
			/*if($order_info['delivery'] == 'localtown_delivery'){
				M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_info['order_id']) )->save( array('state' => 4,'orderdistribution_id'=> -1) );

				D('Seller/Order')->receive_order($order_info['order_id']);

				M('eaterplanet_ecommerce_order_history')->where( array('order_id' => $order_info['order_id'],'order_status_id' => 6) )->save( array( 'comment' => '系统自动收货，等待结算佣金') );

			}else{*/
			if($order_info['delivery'] != 'localtown_delivery' && $order_info['delivery'] != 'hexiao') {
				//批量用户确认收货
				D('Home/Frontorder')->receive_order($order_info['order_id'], true);
			}
			//}
		}

		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}

		echo json_encode( array('code' => 0, 'msg' => '订单号：'.$tmp_info['order_num_alias']." 处理成功，还剩余".count($quene_order_list)."个订单未处理") );
		die();

	}

	//--end

	 public function ordersendall2()
	{


		if( IS_POST )
		{

			$type =  I('request.type', 'normal');
			$express =  I('request.express', '');
			$expresscom = I('request.expresscom', '');

			$rows = D('Seller/Excel')->import('excelfile');

			$num = count($rows);
			$time = time();

			$express_arr = array();

			foreach($express_list as $val)
			{
				$express_arr[ $val['id'] ] = $val['name'];
			}

			$i = 0;
			$err_array = array();

			$j =0;
			foreach ($rows as $rownum => $col) {
				$order_id = trim($col[0]);

				if (empty($order_id)) {
					$err_array[] = $order_id;
					continue;
				}
				if($j == 0)
				{
					$j++;
					continue;
				}


				$order_info = M('eaterplanet_ecommerce_order')->where( array('order_num_alias' => $order_id ) )->find();

				if(!empty($order_info) && $order_info['order_status_id'] == 1)
				{

					//判断是否快递类型 type  //normal  mult


					if( $type == 'mult' && $order_info['delivery'] == 'express' )
					{


						$data = array();

						$data['express_time'] = time();

						$data['order_status_id'] = 4;
						$data['shipping_no'] = $col[1];
						$data['shipping_method'] = $express;
						$data['dispatchname'] = $expresscom;

						M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id'] ) )->save( $data );

						$history_data = array();

						$history_data['order_id'] = $order_info['order_id'];
						$history_data['order_status_id'] = 4;
						$history_data['notify'] = 0;
						$history_data['comment'] = '订单快递已发货';
						$history_data['date_added'] = time();

						M('eaterplanet_ecommerce_order_history')->add($history_data);
						//TODO..发送已发货的模板消息
						D('Home/Frontorder')->send_order_operate($order_info['order_id']);

					}else {

						if($order_info['delivery'] != 'express')
						{
							$data = array();

							$data['express_time'] = time();

							$data['order_status_id'] = 14;

							M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_info['order_id'] ) )->save($data);

							$history_data = array();
							$history_data['order_id'] = $order_info['order_id'];
							$history_data['order_status_id'] = 14;
							$history_data['notify'] = 0;
							$history_data['comment'] = '订单配送中';
							$history_data['date_added'] = time();

							M('eaterplanet_ecommerce_order_history')->add($history_data);
						}

					}

					//TODO...发送已经发货给团长的消息通知


					++$i;
				}

				if($type =='mult_send_tuanz' && $order_info['delivery'] != 'express' && $order_info['order_status_id'] == 14  )
				{
					//订单批量团长签收  2019012749451499751
					D('Home/Frontorder')->send_order_operate($order_info['order_id']);
					++$i;
				}

				if($type =='mult_member_receive_order' && $order_info['order_status_id'] == 4  )
				{
					//批量用户确认收货

					D('Home/Frontorder')->receive_order($order_info['order_id'], true);

					++$i;
				}

			}

			$tip = '';

			if($type =='mult_send_tuanz')
			{
				$msg = $i . '个订单批量送达团长成功！';
			}
			else if($type == 'mult_member_receive_order')
			{
				$msg = $i . '个订单批量用户确认收货！';
			}
			else{
				$msg = $i . '个订单发货成功！';
			}

			if ($i < $num) {
				$url = '';

				if (!empty($err_array)) {
					$j = 1;


					if($type =='mult_send_tuanz')
					{
						$tip .= '<br>' . count($err_array) . '个订单批量送达团长失败,失败的订单编号: <br>';
					}else if($type == 'mult_member_receive_order'){
						$tip .= '<br>' . count($err_array) . '个订单批量用户确认收货,失败的订单编号: <br>';
					}
					else{
						$tip .= '<br>' . count($err_array) . '个订单发货失败,失败的订单编号: <br>';
					}


					foreach ($err_array as $k => $v) {
						$tip .= $v . ' ';

						if (($j % 2) == 0) {
							$tip .= '<br>';
						}

						++$j;
					}
				}
			}
			else {
				$url = U('order/ordersendall', array('type' => $type) );
			}

			$redirect = $url;

			$this->message = $msg.$tip;
			$this->redirect = $redirect;
			$this->display('Public/_message');
			die();

		}

		$express_list = D('Seller/Express')->load_all_express();


		$this->express_list = $express_list;
		$this->type = I('request.type');
		$this->display();
	}

	public function opchangeexpress()
	{
		$_GPC = I('request.');

		$opdata = $this->check_order_data();
		extract($opdata);

		$changeexpress = 1;
		$sendtype = intval($_GPC['sendtype']);
		$edit_flag = 1;

		if (IS_POST) {

			if (!(empty($_GPC['shipping_no'])) && empty($_GPC['shipping_no'])) {
				show_json(0,  array('msg' => '请输入快递单号！') );
			}

			if (!(empty($item['transid']))) {
			}

			$express_info = D('Seller/Express')->get_express_info($_GPC['express']);

			$time = time();
			$data = array(
				'shipping_method' => trim($_GPC['express']),
				'dispatchname' => $express_info['name'],
				'shipping_no' => trim($_GPC['shipping_no']),
				'express_time' => $time
			);

			M('eaterplanet_ecommerce_order')->where( array('order_id' => $item['order_id']) )->save( $data );

			$history_data = array();
			$history_data['order_id'] = $item['order_id'];
			$history_data['order_status_id'] = 4;
			$history_data['notify'] = 0;
			$history_data['comment'] = '修改发货物流，订单发货 ID: ' . $item['order_id'] . ' 订单号: ' . $item['order_num_alias'] . ' <br/>快递公司: ' . $express_info['name'] . ' 快递单号: ' . $_GPC['shipping_no'];
			$history_data['date_added'] = time();

			M('eaterplanet_ecommerce_order_history')->add( $history_data );

			//TODO...发送已经发货的消息通知
			//m('notice')->sendOrderMessage($item['id']);
			//plog('order.op.send', '订单发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br/>快递公司: ' . $_GPC['expresscom'] . ' 快递单号: ' . $_GPC['expresssn']);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$noshipped = array();
		$shipped = array();

		$province_info = D('Home/Front')->get_area_info($item['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($item['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($item['shipping_country_id']);

		$order_goods = M('eaterplanet_ecommerce_order_goods')->field('order_goods_id as id,name as title,goods_images as thumb')->where( array('order_id' => $item['order_id']) )->select();

		$express_list = D('Seller/Express')->load_all_express();

		$this->id = $item['order_id'];
		$this->item = $item;
		$this->province_info = $province_info;
		$this->city_info = $city_info;
		$this->area_info = $area_info;
		$this->order_goods = $order_goods;
		$this->express_list = $express_list;


		$this->display('Order/opsend');
	}

	public function batchsend_import()
	{
		global $_W;
		global $_GPC;

		$type = I('request.type','normal');

		$this->type = $type;

		$columns = array();
		$columns[] = array('title' => '订单编号', 'field' => '', 'width' => 32);
		//$columns[] = array('title' => '快递单号', 'field' => '', 'width' => 32);

		if($type == 'normal')
		{
			D('Seller/Excel')->temp('批量发货数据模板', $columns);
		}else{
			$columns[] = array('title' => '快递单号', 'field' => '', 'width' => 32);

			D('Seller/Excel')->temp('批量发货数据模板', $columns);
		}


	}

	 /**
	  * 上传订单Excel批量发货
	  */
	 function sendexpress_excel_done()
	 {
	      set_time_limit(0);
    	  if(isset($_FILES["file"]) && ($_FILES["file"]["error"] == 0)){

    	      $excel_dir = ROOT_PATH.'Uploads/image/'.date('Y-m-d');
    	      $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    	      RecursiveMkdir( $excel_dir );

    	      $path = $excel_dir.'/'.md5($_FILES['file']['name'].mt_rand(1, 999)).'.'.$extension;
    	      $rs = move_uploaded_file($_FILES["file"]["tmp_name"],$path);


    	    $notify_model = D('Home/Weixinnotify');
    	    //

            $result = importExecl($path);
            if(!empty($result)) {
                $order_ids = array();
                //开始导入数据库，并发货
                foreach($result as $val){
                   if(empty($val[0]))
                   {
                       continue;
                   }
                    $order_info = M('order')->field('order_id,order_status_id,delivery')->where( array('order_num_alias' => trim($val[0])) )->find();
					//order_status_id  1
					if($order_info['order_status_id'] != 1)
					{
						continue;
					}
					//['delivery'] == 'pickup'
                    $order_ids[] = $order_info['order_id'];
					if($order_info['delivery'] == 'pickup')
					{
						M('order')->where( array('order_id' => $order_info['order_id'] ) )->save( array('order_status_id' => 4) );
					} else {
						M('order')->where( array('order_id' => $order_info['order_id'] ) )->save( array('shipping_method' =>$val[5],'shipping_no' =>$val[6],'order_status_id' => 4) );
					}

					$oh = array();
					$oh['order_id']=$order_info['order_id'];
					$oh['order_status_id']=4;
					$oh['notify'] = 0;
					$oh['comment']='导入excel批量发货';
					$oh['date_added']=time();
					$oh_id=M('OrderHistory')->add($oh);

                }
                foreach($order_ids as $order_id) {

					$order_info = M('order')->field('delivery')->where( array('order_id' => $order_id) )->find();

					if($order_info['delivery'] == 'pickup')
					{
						$notify_model->sendPickupMsg($order_id);
					} else {
						$notify_model->sendExpressMsg($order_id);
					}

                }
            }
          }
          echo json_encode( array('code' => 1) );
          die();
	 }



	 function sendexpress()
	 {
		 $this->breadcrumb2='批量发货';
	     $this->display();
	 }
	 function print_order(){
	 	$model=new OrderModel();

		$this->order=$model->order_info(I('id'));
		$this->print=true;
		$this->display('./Themes/Home/default/Mail/order.html');
	 }

	 public function refunddone()
	 {
		$id = I('get.id',0,'intval');
		$comment = I('post.comment');
		$order_refund_type = I('post.order_refund_type',0,'intval');
		$result = array('code' =>1);

		$order_refund_history = array();
		$order_refund_history['order_id'] = $id;
		$order_refund_history['message'] = htmlspecialchars($comment);
		$order_refund_history['type'] = 2;
		$order_refund_history['addtime'] = time();

		M('order_refund_history')->add($order_refund_history);


		$order_history = array();
		$order_history['order_id'] = $id;
		$order_history['order_status_id'] = 0;
		$order_history['notify'] = 0;
		$order_history['comment'] = '';
		$order_history['date_added'] = time();

		if($order_refund_type ==1)
		{
			//拒绝
			M('order_refund')->where( array('order_id' => $id) )->save( array('state' => 1) );
			$order_history['order_status_id'] = 12;
			$order_history['comment'] = '商家拒绝退款';
			M('order_history')->add($order_history);

		} else {

			$order_history['order_status_id'] = 12;
			$order_history['comment'] = '商家统一退款';
			M('order_history')->add($order_history);

			$weixin_model = D('Home/Weixin');
			//通过
			M('order_refund')->where( array('order_id' => $id) )->save( array('state' => 3) );
			$order_refund = M('order_refund')->where( array('order_id' => $id) )->find();
			$weixin_model->refundOrder($id, $order_refund['ref_money']);


		}
		echo json_encode($result);
		die();

	 }
	 public function show_refund()
	 {
		$this->crumbs='订单退款详情';

		$model=new OrderModel();

		$data = $model->order_info(I('id'));

		$order_statuses = $data['order_statuses'];

		$need_status = array();
		foreach($order_statuses as $key => $val)
		{
			if( in_array($val['order_status_id'], array(4)) )
			{
				$need_status[$key] = $val;
			}
		}
		$data['order_statuses'] = $need_status;

		$refund_reason = array(
							'97' =>'商品有质量问题',
							'98' =>'没有收到货',
							'99' =>'商品少发漏发发错',
							'100' =>'商品与描述不一致',
							'101' =>'收到商品时有划痕或破损',
							'102' =>'质疑假货',
							'111' =>'其他',
						);
		$order_refund = M('order_refund')->where( array('order_id' =>I('id')) )->find();
		//ref_type
		$order_refund['ref_type'] = $order_refund['ref_type'] ==1 ? '仅退款': '退款退货';
		$order_refund['ref_name'] = empty($refund_reason[$order_refund['ref_name']]) ? $order_refund['ref_name']: $refund_reason[$order_refund['ref_name']] ;

		$refund_state = array(
							0 => '申请中',
							1 => '商家拒绝',
							2 => '平台介入',
							3 => '退款成功',
							4 => '退款失败',
							5 => '撤销申请',
						);
		$order_refund['state'] = $refund_state[$order_refund['state']];

		$this->order_refund = $order_refund;
		$order_refund_image = M('order_refund_image')->where( array('ref_id' => $order_refund['ref_id']) )->select();
		$refund_images = array();

		if(!empty($order_refund_image))
		{
			foreach($order_refund_image as $refund_image)
			{
				$refund_image['thumb_image'] = resize($refund_image['image'], 100, 100);
				$refund_images[] = $refund_image;
			}
		}

		$order_refund_history = M('order_refund_history')->where( array('order_id' => I('id')) )->order('addtime asc')->select();

		foreach($order_refund_history as $key => $val)
		{
			$val['type'] = $val['type'] == 1 ?'用户反馈':'商家反馈';
			switch($val['type'])
			{
				case 1:
						$val['type'] = '用户反馈';
						break;
				case 2:
						$val['type'] = '商家反馈';
						break;
				case 3:
						$val['type'] = '平台反馈';
						break;

			}
			$order_refund_history_image = M('order_refund_history_image')->where( array('orh_id' => $val['id']) )->select();
			if(!empty($order_refund_history_image))
			{
				foreach($order_refund_history_image as $kk => $vv)
				{
					$vv['thumb_image'] = resize($vv['image'], 100, 100);
					$order_refund_history_image[$kk] = $vv;
				}
			}
			$val['order_refund_history_image'] = $order_refund_history_image;
			$order_refund_history[$key] = $val;
		}

		$this->order_refund_history = $order_refund_history;
		$this->refund_images = $refund_images;
		$this->data = $data;
		$this->display('refund');
	 }
	 public function show_order(){

	 	$this->crumbs='订单详情';
		//is_ziti/1 commiss_list
		$is_ziti = I('get.is_ziti', 0);
		if($is_ziti == 1)
		{
			$this->breadcrumb1='自提管理';
			$this->breadcrumb2='自提管理';
		}
	 	$model=new OrderModel();

		$data = $model->order_info(I('id'));

		$order_statuses = $data['order_statuses'];

		$need_status = array();
		foreach($order_statuses as $key => $val)
		{
			if( in_array($val['order_status_id'], array(1,2,3,4,5,6,7,8,11,12,13)) )
			{
				$need_status[$key] = $val;
			}
		}
		$data['order_statuses'] = $need_status;

		//$data['order']
		if($data['order']['type'] == 'integral')
		{
			$integral_order =  M('integral_order')->where( array('order_id' => I('id') ) )->find();
			$this->integral_order = $integral_order;
		}

		$this->data = $data;

		$pick_order_info = array();
		$pick_up = array();
		if($data['order']['delivery'] == 'pickup')
		{
			$pick_order_info = M('pick_order')->where( array('order_id' => $data['order']['order_id']) )->find();
			$pick_up = M('pick_up')->where( array('id' => $pick_order_info['pick_id']) )->find();
		}

		$this->pick_order_info = $pick_order_info;
		$this->pick_up = $pick_up;

		$sql="select s.* from ".C('DB_PREFIX')."seller_express as s, ".C('DB_PREFIX')."seller_express_relat as ser
		      where s.id = ser.express_id and ser.store_id = ".SELLERUID;
		$express_list= M()->query($sql);

		$this->express_list = $express_list;

		//$data['order']['order_id']
		$order_goods_haitao = M('order_goods_haitao')->where( array('order_id' => $data['order']['order_id']) )->find();
		$this->order_goods_haitao = $order_goods_haitao;
	 	$this->display('show');
	 }
	 function history(){
	 		$model=new OrderModel();

			if(IS_POST){

				if(I('order_status_id')==C('cancel_order_status_id')){
					$Order = new \Home\Model\OrderModel();
					$Order->cancel_order($_GET['id']);
					storage_user_action(session('user_auth.uid'),session('user_auth.username'),C('BACKEND_USER'),'取消了订单  '.$_GET['id']);
					$result=true;
				}else{
					//order_status_id 4

					if($_POST['order_status_id'] != 4)
					{
						unset($_POST['shipping_no']);
						unset($_POST['shipping_method']);
						die();
					}else {

					}
					$result=$model->addOrderHistory($_GET['id'],$_POST);
				}
				if($result){
					$this->success='新增成功！！';
				}else{
					$this->error='新增失败！！';
				}
			}

			$results = $model->getOrderHistories($_GET['id']);

			foreach ($results as $result) {
				$histories[] = array(
					'notify'     => $result['notify'] ? '是' : '否',
					'status'     => $result['status'],
					'comment'    => nl2br($result['comment']),
					'date_added' => date('Y/m/d H:i:s', $result['date_added'])
				);
			}

			$this->histories=$histories;

			$this->display();
	}

	function del(){
		$model=new OrderModel();
		$return=$model->del_order(I('get.id'));
		$this->osc_alert($return);
	}

	//begin
	public function printconfig()
	{

		if (defined('ROLE') && ROLE == 'agenter' )
		{

			$supper_info = get_agent_logininfo();


			if (IS_POST) {

				$_GPC = I('request.');

				$data = array();

				$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));

				//$supper_info['id']
				$open_feier_print = isset($data['open_feier_print'.$supper_info['id']]) ? $data['open_feier_print'.$supper_info['id']]:0;
				if(empty($open_feier_print) || $open_feier_print == 0)
				{
					$data['open_feier_print'.$supper_info['id']] = $open_feier_print;

				}else if($open_feier_print == 1){
					$feier_print_sn = isset($data['feier_print_sn'.$supper_info['id']]) ? $data['feier_print_sn'.$supper_info['id']]:'';
					$feier_print_key = isset($data['feier_print_key'.$supper_info['id']]) ? $data['feier_print_key'.$supper_info['id']]:'';

					$data['open_feier_print'.$supper_info['id']] = $open_feier_print;
					$data['feier_print_sn'.$supper_info['id']] = $feier_print_sn;
					$data['feier_print_key'.$supper_info['id']] = $feier_print_key;

					$feier_print_sn_old_arr =  M('eaterplanet_ecommerce_config')->where( array('name' => 'feier_print_sn'.$supper_info['id'] ) )->find();

					$feier_print_sn_old = $feier_print_sn_old_arr['value'];

					$feier_print_key_old_arr = M('eaterplanet_ecommerce_config')->where(  array('name' => 'feier_print_key'.$supper_info['id'] ) )->find();

					$feier_print_key_old = $feier_print_key_old_arr['value'];

					if($feier_print_sn_old != $feier_print_sn || $feier_print_key_old != $feier_print_key)
					{
						//开始添加打印机
						//printaction
						$print_model = D('Seller/Printaction');
						$snlist = "{$feier_print_sn}#{$feier_print_key}";

						$print_model->addprinter($snlist);

					}

					//...todo测试订单自动打印
				}else if($open_feier_print == 2){
					$yilian_machine_code = isset($data['yilian_machine_code'.$supper_info['id']]) ? $data['yilian_machine_code'.$supper_info['id']]:'';

					$yilian_msign = isset($data['yilian_msign'.$supper_info['id']]) ? $data['yilian_msign'.$supper_info['id']]:'';
					$yilian_client_id = isset($data['yilian_client_id'.$supper_info['id']]) ? $data['yilian_client_id'.$supper_info['id']]:'';
					$yilian_client_key = isset($data['yilian_client_key'.$supper_info['id']]) ? $data['yilian_client_key'.$supper_info['id']]:'';


					$data['open_feier_print'.$supper_info['id']] = $open_feier_print;
					$data['yilian_machine_code'.$supper_info['id']] = $yilian_machine_code;
					$data['yilian_msign'.$supper_info['id']] = $yilian_msign;
					$data['yilian_client_id'.$supper_info['id']] = $yilian_client_id;
					$data['yilian_client_key'.$supper_info['id']] = $yilian_client_key;

					$yilian_client_id_old_arr =  M('eaterplanet_ecommerce_config')->where( array('name' => 'yilian_client_id'.$supper_info['id'] ) )->find();

					$yilian_client_id_old = $yilian_client_id_old_arr['value'];

					$yilian_machine_code_old_arr = M('eaterplanet_ecommerce_config')->where( array('name' => 'yilian_machine_code'.$supper_info['id'] ) )->find();

					$yilian_machine_code_old = $yilian_machine_code_old_arr['value'];

					$yilian_msign_old_arr = M('eaterplanet_ecommerce_config')->where( array('name' => 'yilian_msign'.$supper_info['id'] ) )->find();

					$yilian_msign_old = $yilian_msign_old_arr['value'];


					if(true || $yilian_client_id != $yilian_client_id_old || $yilian_machine_code_old != $yilian_machine_code || $yilian_msign_old != $yilian_msign)
					{

						//开始添加打印机
						//printaction
						$print_model = D('Seller/Printaction');

						$res = $print_model->addyilianyunprinter($yilian_client_id,$yilian_client_key,$yilian_machine_code, $yilian_msign );

						if($res != 0)
						{
							show_json(0,  array('msg' => '添加易联云打印机失败！' ) );
						}
					}

					//...todo测试订单自动打印
				}

				D('Seller/Config')->update($data);

				show_json(1);
			}
			$data = D('Seller/Config')->get_all_config();

			$this->supper_info = $supper_info;
			$this->data = $data;

			include $this->display();
		}
	}

	public function express_list(){
		$this->type = I('request.type','');
		$this->order_id = I('request.order_id','');
		$express_list = D('Seller/Express')->load_kdn_express();
		$this->express_list = $express_list;
		$this->display();
	}

	public function kdn_print_log_list(){
		$log_list = M('eaterplanet_ecommerce_order_kdniao_print_log')->order('id desc')->limit(5)->select();
		$this->log_list = $log_list;
		$this->display();
	}

	/**
	 * 判断快递鸟寄件人信息
	 * @param $express_code
	 */
	public function check_kdn_sender($express_code){
		$result = array();
		$result['status'] = 1;
		$data = D('Seller/Config')->get_all_config();
		if(empty($data['kdn_sender_name'])){
			$result['status'] = 0;
			$result['msg'] = '寄件人未配置';
			return $result;
		}
		if(empty($data['kdn_sender_mobile'])){
			$result['status'] = 0;
			$result['msg'] = '寄件人联系电话未配置';
			return $result;
		}
		if(empty($data['kdn_province_id'])){
			$result['status'] = 0;
			$result['msg'] = '寄件人省份未配置';
			return $result;
		}
		if(empty($data['kdn_city_id'])){
			$result['status'] = 0;
			$result['msg'] = '寄件人城市未配置';
			return $result;
		}
		if(empty($data['kdn_area_id'])){
			$result['status'] = 0;
			$result['msg'] = '寄件人区域未配置';
			return $result;
		}
		if(empty($data['kdn_sender_address'])){
			$result['status'] = 0;
			$result['msg'] = '寄件人详细地址未配置';
			return $result;
		}
		$code_list = array('EMS','YZPY','YZBK');
		if(in_array($express_code,$code_list)){
			if(empty($data['kdn_sender_postcode'])){
				$result['status'] = 0;
				$result['msg'] = '寄件人邮编未配置';
				return $result;
			}
		}
		return $result;
	}

	//打印面单
	public function kdniao_order(){
        $result = array();
		$order_id = I('request.order_id','');
        $express_code = I('request.express_code','');
        if(empty($order_id)){
            $result['status'] = 1;
            $result['message'] = "订单号不存在";
        }else if(empty($express_code)){
            $result['status'] = 1;
            $result['message'] = "快递公司不存在";
        }else{
			$sender_result = $this->check_kdn_sender($express_code);
			if($sender_result['status'] == 1){
				$kdNiao = new KdApiEOrder();
				$config_data = D('Seller/Config')->get_all_config();
				$result = $kdNiao->printOrder($order_id,$express_code,$config_data);
			}else{
				$result['status'] = 3;
				$result['message'] = $sender_result['msg'];
			}
        }
        echo json_encode($result);
	}
	//打印多条订单面单
	public function kdniao_print_orders(){
		$print_type = I('request.print_type','');
		$order_id = I('request.order_id','');
		$express_code = I('request.express_code','');
		$order_id_list = array();
		$order_list = array();
		$order_kdn_status = array();
		if($print_type == "select"){
			$order_list = M('eaterplanet_ecommerce_order')->field('order_id,is_kdn_print')->where("order_status_id = 1 and delivery = 'express' and order_id in (".$order_id.")")->select();
		}else if($print_type == "all"){
			$order_list = M('eaterplanet_ecommerce_order')->field('order_id,is_kdn_print')->where("order_status_id = 1 and delivery = 'express' ")->select();
		}
		foreach($order_list as $k=>$v){
			$order_id_list[] = $v['order_id'];
			$order_kdn_status[$v['order_id']] = $v['is_kdn_print'];
		}
		$time = time();
		$print_count = count($order_id_list);
		//保存快递鸟打印日志
		$kd_data = array();
		$kd_data['addtime'] = $time;
		$kd_data['print_count'] = $print_count;
		$kdn_id = M('eaterplanet_ecommerce_order_kdniao_print_log')->add( $kd_data );
		$succ_count = 0;
		$fail_count = 0;
		$fail_result = array();
		$kdNiao = new KdApiEOrder();
		$config_data = D('Seller/Config')->get_all_config();
		$sender_result = $this->check_kdn_sender($express_code);
		if($sender_result['status'] == 1){
			foreach($order_id_list as $v){
				if($order_kdn_status[$v] == 1){
					$succ_count++;
				}else{
					$result = $kdNiao->printOrder($v,$express_code,$config_data);
					if($result['status'] == 0){//打印成功
						$succ_count++;
					}else{//打印失败
						$fail_count++;
						$fail_result[] = $result['message'];
					}
				}
			}
		}else{
			$result['status'] = 3;
			$result['message'] = $sender_result['msg'];
		}
		$fail_reason = json_encode(array_unique($fail_result));
		//打印完成更新快递鸟打印日志
		$kdn_data = array();
		$kdn_data['succ_count'] = $succ_count;
		$kdn_data['fail_count'] = $fail_count;
		$kdn_data['fail_reason'] = $fail_reason;
		$kdn_data['status'] = 1;
		M('eaterplanet_ecommerce_order_kdniao_print_log')->where(array('id' => $kdn_id))->save($kdn_data);
		$kdn_order_ids = implode(",",$order_id_list);
		echo json_encode(array('status'=>0,'order_ids'=>$kdn_order_ids));
	}
	//检查是否配置快递鸟接口参数
	public function check_kdniao(){
		$config_data = D('Seller/Config')->get_all_config();
		if(isset($config_data['kdniao_id']) && !empty($config_data['kdniao_id']) && isset($config_data['kdniao_api_key']) && !empty($config_data['kdniao_api_key'])){
			show_json(1);
		}else{
			show_json(0,  array('msg' => '快递鸟未配置！' ) );
		}
	}

	public function kdn_info(){
		$this->order_id = I('request.order_id','');
		$kdn_info = M('eaterplanet_ecommerce_order_kdniao_info')->where( array('order_id' => $this->order_id,'status'=>1) )->find();
		$this->kdn_info = $kdn_info;
		$this->display();
	}

	public function kdn_orders_info(){
		$this->order_ids = I('request.order_ids','');
		$kdn_list_info = M('eaterplanet_ecommerce_order_kdniao_info')->where("order_id in (".$this->order_ids.") and status = 1")->select();
		$this->kdn_list_info = $kdn_list_info;
		$this->display();
	}

	/**
	 * 快递鸟打印成功订单发货
	 */
	public function kdn_send(){
		$order_id = I('request.id','');
		$order_ids = I('request.ids','');
		if(!empty($order_id)){
			$item = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();
			if (empty($item)) {
				show_json(0, '未找到订单!');
			}
			$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();
			if(!empty($order_info)) {
				if ($order_info['order_status_id'] == 1 && $order_info['delivery'] == 'express' && $order_info['is_kdn_print'] == 1) {
					$express_info = D('Seller/Express')->get_express_info($order_info['shipping_method']);
					$data = array();
					$data['order_status_id'] = 4;
					M('eaterplanet_ecommerce_order')->where(array('order_id' => $order_id))->save($data);

					$history_data = array();
					$history_data['order_id'] = $order_id;
					$history_data['order_status_id'] = 4;
					$history_data['notify'] = 0;
					$history_data['comment'] = '订单发货 ID: ' . $order_id . ' 订单号: ' . $item['order_num_alias'] . ' <br/>快递公司: ' . $express_info['name'] . ' 快递单号: ' . $item['shipping_no'];
					$history_data['date_added'] = time();
					M('eaterplanet_ecommerce_order_history')->add($history_data);

					D('Home/Frontorder')->send_order_operate($item['order_id']);
				} else {
					show_json(0, '待发货快递订单才能发货成功!');
				}
			}else{
				show_json(0, '未找到订单!');
			}
		}else if(!empty($order_ids)){
			$ids = explode(",",$order_ids);
			foreach($ids as $k=>$v){
				$order_id = $v;
				$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();
				if (empty($order_info)) {
					show_json(0, '未找到订单!');
				}
				if(!empty($order_info)) {
					if ($order_info['order_status_id'] == 1 && $order_info['delivery'] == 'express' && $order_info['is_kdn_print'] == 1) {
						$express_info = D('Seller/Express')->get_express_info($order_info['shipping_method']);
						$data = array();
						$data['order_status_id'] = 4;
						M('eaterplanet_ecommerce_order')->where(array('order_id' => $order_id))->save($data);

						$history_data = array();
						$history_data['order_id'] = $order_id;
						$history_data['order_status_id'] = 4;
						$history_data['notify'] = 0;
						$history_data['comment'] = '订单发货 ID: ' . $order_id . ' 订单号: ' . $item['order_num_alias'] . ' <br/>快递公司: ' . $express_info['name'] . ' 快递单号: ' . $item['shipping_no'];
						$history_data['date_added'] = time();
						M('eaterplanet_ecommerce_order_history')->add($history_data);

						D('Home/Frontorder')->send_order_operate($item['order_id']);
					} else {
						show_json(0, '待发货快递订单才能发货成功!');
					}
				}else{
					show_json(0, '未找到订单!');
				}
			}
		}else{
			show_json(0, '未找到订单!');
		}
		show_json(1);
	}

	/**
	 * 修改订单价格
	 */
	public function change_order(){
		$_GPC = I('request.');

		$opdata = $this->check_order_data();
		extract($opdata);
		$id = I('request.id',0);
		if( IS_POST )
		{
			$_GPC = I('request.');
			$order_id = $_GPC['id'];
			$order_goods_ids = $_GPC['order_goods_id'];
			$change_prices = $_GPC['change_price'];
			$count = 0;
			foreach($change_prices as $v){
				if(empty(trim($v))){
					$count++;
				}
			}
			if($count == count($change_prices)){
				show_json(0,  array('message' => '未填写涨价或减价金额！'));
			}
			//改价后的商品实付价格不能低于0.1元
            foreach($order_goods_ids as $k=>$order_goods_id){
                $order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$id,'order_goods_id' =>$order_goods_id  ) )->find();
                $total = $order_goods_info['total'];
                $fullreduction_money = $order_goods_info['fullreduction_money'];
                $voucher_credit = $order_goods_info['voucher_credit'];
                $score_for_money = $order_goods_info['score_for_money'];
                $total = round($total - $fullreduction_money - $voucher_credit - $score_for_money,2);
                if(!empty($change_prices[$k])){
                    $change_price = $change_prices[$k];
                    $total = floatval($total) + floatval($change_price);
                    if(bccomp($total, '0.1', 2) == -1){
                        show_json(0,  array('message' => '改价后的商品实付价格不能低于0.1元'));
                    }
                }
            }
			$change_amount = 0;
			foreach($order_goods_ids as $k=>$order_goods_id){
				$order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$id,'order_goods_id' =>$order_goods_id  ) )->find();
				$total = $order_goods_info['total'];
				$update_data = array();
				if(!empty($change_prices[$k])){
					$change_amount = $change_amount + $change_prices[$k];
					$change_price = $change_prices[$k];
					$update_data['total'] = round(floatval($total) + floatval($change_price),2);
					$update_data['is_change_price'] = 1;
					M('eaterplanet_ecommerce_order_goods')->where( array('order_id' =>$id,'order_goods_id' => $order_goods_id  ) )->save( $update_data);
				}
			}
			$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' =>$id) )->find();
			$order_total = $order_info['total'];
			$order_data = array();
			$order_data['total'] = round(floatval($order_total) + floatval($change_amount),2);
			$order_data['is_change_price'] = 1;
			M('eaterplanet_ecommerce_order')->where( array('order_id' =>$id) )->save($order_data);

			$oh = array();
			$oh['order_id'] = $id;
			$oh['order_status_id']=15;
			$oh['comment']='订单改价';
			$oh['date_added']=time();

			M('eaterplanet_ecommerce_order_history')->add($oh);

			show_json(1);
		}
		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $id) )->select();
		foreach($order_goods as $k=>$v){
			$order_goods[$k]['goods_img_url'] = tomedia($v['goods_images']);
		}
		$this->item = $opdata['item'];
		$this->order_goods = $order_goods;

		$this->display();
	}


	//检查是否配置第三方配送接口参数
	public function check_delivery_config(){
		$_GPC = I('request.');
		$config_data = D('Seller/Config')->get_all_config();
		$data_type = $_GPC['data_type'];
		if($data_type == 'imdada'){
			if(isset($config_data['localtown_imdada_merchant_id']) && !empty($config_data['localtown_imdada_shop_no']) && isset($config_data['localtown_imdada_appkey']) && !empty($config_data['localtown_imdada_appsecret'])){
				show_json(1);
			}else{
				show_json(0,  array('msg' => '达达配送平台参数未配置！' ) );
			}
		}else if($data_type == 'sf'){
			if(isset($config_data['localtown_sf_dev_id']) && !empty($config_data['localtown_sf_dev_key']) && isset($config_data['localtown_sf_store_id'])){
				show_json(1);
			}else{
				show_json(0,  array('msg' => '顺丰同城参数未配置！' ) );
			}
		}else if($data_type == 'make'){
            if(isset($config_data['localtown_mk_token']) && !empty($config_data['localtown_mk_token']) ){
                show_json(1);
            }else{
                show_json(0,  array('msg' => '码科配送参数未配置！' ) );
            }
        }else if($data_type == 'ele'){
			if(isset($config_data['localtown_ele_app_id']) && !empty($config_data['localtown_ele_secret_key']) && isset($config_data['localtown_ele_store_code']) && !empty($config_data['localtown_ele_transport_name'])
					&& !empty($config_data['localtown_ele_transport_address']) && !empty($config_data['localtown_ele_transport_longitude'])  && !empty($config_data['localtown_ele_transport_latitude'])
					&& !empty($config_data['localtown_ele_transport_tel']) && !empty($config_data['localtown_ele_position_source'])
			){
				show_json(1);
			}else{
				show_json(0,  array('msg' => '蜂鸟即配平台参数未配置！' ) );
			}
		}
	}

	/**
	 * 将订单推送给第三方配送
	 */
	public function thirth_delivery_order(){
		$_GPC = I('request.');
		$data_type = $_GPC['data_type'];
		$order_id = $_GPC['order_id'];
		if(empty($data_type)){
			show_json(0,  array('msg' => '未选择第三方配送' ) );
		}
		if(empty($order_id)){
			show_json(0,  array('msg' => '订单号不存在' ) );
		}
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' =>$order_id) )->find();
		if(empty($order_info)){
			show_json(0,  array('msg' => '订单不存在' ) );
		}
		//店铺地址
		if($order_info['store_id'] > 0) {
			$store_data = D('Home/Order')->getOrderStoreAddress($order_info);
			$order_info['store_data'] = $store_data;
		}

		//商品信息
		$sql = "select og.goods_id,og.name as goods_name,og.quantity,og.price,og.total,og.rela_goodsoption_valueid,g.weight as goods_weight  from "
				. C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og left join  ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g on og.goods_id=g.id "
				." where og.order_id = ".$order_id;
		$goods_list = M()->query($sql);
		$goods_count = 0;
		$goods_weight = 0;
		$goods_type_count = 0;
		foreach($goods_list as $k=>$v){
			$goods_count = $goods_count + $v['quantity'];
			if(!empty($v['rela_goodsoption_valueid'])){
				$goods_option_mult_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('option_item_ids' => $v['rela_goodsoption_valueid'],'goods_id' => $v['goods_id']) )->find();
				if(!empty($goods_option_mult_value)){
					$v['goods_weight'] = $goods_option_mult_value['weight'];
				}
			}
			$goods_weight = $goods_weight + $v['quantity'] * $v['goods_weight'];
			$goods_type_count = $goods_type_count + 1;
		}
		if(empty($goods_weight)){
			$goods_weight = 100;//默认100克
		}
		$order_info['goods_list'] = $goods_list;
		//商品种类
		$order_info['goods_type_count'] = $goods_type_count;
		//商品数量
		$order_info['goods_count'] = $goods_count;
		//商品重量
		$order_info['goods_weight'] = $goods_weight;
		//订单总金额
		$order_info['order_total'] = $order_info['total']+$order_info['packing_fare']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']+$order_info['localtown_add_shipping_fare']-$order_info['fare_shipping_free'];
		//收货人地址
		$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);
		$order_info['shipping_address'] = $province_info['name'].$city_info['name'].$area_info['name'].$order_info['shipping_address'];
		//收货人经纬度
		$order_distribution_info = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
		//收货人地址纬度
		$order_info['shipping_lat'] = $order_distribution_info['member_lat'];
		//收货人地址经度
		$order_info['shipping_lng'] = $order_distribution_info['member_lon'];
		//商城名称
		$shoname = D('Home/Front')->get_config_by_name('shoname');
		$order_info['shoname'] = $shoname;
		if($data_type == 'imdada'){
			$imdada = new Imdada();
			$result = $imdada->addOrder($order_info);
			if($result['status'] == 1){//成功
				//配送费用
				$delivery_fee = $result['result']['fee'];
				$express_info = array();
				$express_info['delivery_fee'] = $delivery_fee;
				D('Seller/Order')->do_send_localtown_thirth_delivery($order_id,$data_type,$express_info);
				$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
				D('Seller/Supply')->update_supply_commission($order_id,$shipping_money["shipping_money"]);
				show_json(1);
			}else{//失败
				show_json(0,  array('msg' => $result['code'].':'.$result['message'] ) );
			}
		}else if($data_type == 'sf'){
			$sfexpress = new Sfexpress();
			$result = $sfexpress->addOrder($order_info);
			if($result['status'] == 1){//成功
				//配送费用
				$delivery_fee = round($result['result']['total_price']/100,2);
				//顺丰订单号
				$delivery_order_id = $result['result']['sf_order_id'];
				//顺丰运单号
				$delivery_bill_id = $result['result']['sf_bill_id'];

				$express_info = array();
				$express_info['delivery_fee'] = $delivery_fee;
				$express_info['delivery_order_id'] = $delivery_order_id;
				$express_info['delivery_bill_id'] = $delivery_bill_id;
				D('Seller/Order')->do_send_localtown_thirth_delivery($order_id,$data_type,$express_info);

				$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
				D('Seller/Supply')->update_supply_commission($order_id,$shipping_money["shipping_money"]);
				show_json(1);
			}else{//失败
				show_json(0,  array('msg' => $result['code'].':'.$result['message'] ) );
			}
		}else if( $data_type == 'make' )
		{
			//$store_data = D('Home/Make')->getOrderStoreAddress($order_info);
            $result = D('Home/Make')->addOrder($order_info);

            if( $result['code'] == 0 )
			{

               //码科订单号
                $delivery_order_id = $result['order_number'];

                $express_info = array();
                $express_info['delivery_fee'] = $order_distribution_info['shipping_money'];
                $express_info['delivery_order_id'] = $delivery_order_id;
                $express_info['delivery_bill_id'] = '';

                D('Seller/Order')->do_send_localtown_thirth_delivery($order_id,$data_type,$express_info);
				$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
                D('Seller/Supply')->update_supply_commission($order_id,$shipping_money["shipping_money"]);

                show_json(1);


			}else{
                show_json(0,  array('msg' => $result['message'] ) );
			}

		}else if($data_type == 'ele'){//蜂鸟即配
			$eleDistribution = new EleDistribution();

			$order_code = build_order_no(session('user_auth.uid'));
			//保存蜂鸟即配商户订单号
			M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->save(['order_code'=>$order_code]);
			$order_info['order_num_alias'] = $order_code;
			$result = $eleDistribution->addOrder($order_info);
			if($result['status'] == 1){//成功
				//配送费用
				$delivery_fee = round($result['result']['total_price']/100,2);
				//蜂鸟即配订单号
				$delivery_order_id = $result['result']['sf_order_id'];
				//蜂鸟即配运单号
				$delivery_bill_id = $result['result']['sf_bill_id'];

				$express_info = array();
				$express_info['delivery_fee'] = $delivery_fee;
				$express_info['delivery_order_id'] = $delivery_order_id;
				$express_info['delivery_bill_id'] = $delivery_bill_id;
				D('Seller/Order')->do_send_localtown_thirth_delivery($order_id,$data_type,$express_info);

				$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
				D('Seller/Supply')->update_supply_commission($order_id,$shipping_money["shipping_money"]);
				show_json(1);
			}else{//失败
				show_json(0,  array('msg' => $result['code'].':'.$result['message'] ) );
			}
		}else{
			show_json(0,  array('msg' => '不存在第三方配送' ) );
		}
	}
	/**
	 * 将订单重新推送给第三方配送
	 */
	public function thirth_renew_delivery_order(){
		$_GPC = I('request.');
		$data_type = $_GPC['data_type'];
		$order_id = $_GPC['order_id'];
		if(empty($data_type)){
			show_json(0,  array('msg' => '未选择第三方配送' ) );
		}
		if(empty($order_id)){
			show_json(0,  array('msg' => '订单号不存在' ) );
		}
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' =>$order_id) )->find();
		if(empty($order_info)){
			show_json(0,  array('msg' => '订单不存在' ) );
		}

		//店铺地址
		if($order_info['store_id'] > 0) {
			$store_data = D('Home/Order')->getOrderStoreAddress($order_info);
			$order_info['store_data'] = $store_data;
		}

		//商品信息
		$sql = "select og.goods_id,og.name as goods_name,og.quantity,og.rela_goodsoption_valueid,g.weight as goods_weight  from "
				. C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og left join  ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g on og.goods_id=g.id "
				." where og.order_id = ".$order_id;
		$goods_list = M()->query($sql);
		$goods_count = 0;
		$goods_weight = 0;
		$goods_type_count = 0;
		foreach($goods_list as $k=>$v){
			$goods_count = $goods_count + $v['quantity'];
			if(!empty($v['rela_goodsoption_valueid'])){
				$goods_option_mult_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('option_item_ids' => $v['rela_goodsoption_valueid'],'goods_id' => $v['goods_id']) )->find();
				if(!empty($goods_option_mult_value)){
					$v['goods_weight'] = $goods_option_mult_value['weight'];
				}
			}
			$goods_weight = $goods_weight + $v['quantity'] * $v['goods_weight'];
			$goods_type_count = $goods_type_count + 1;
		}
		if(empty($goods_weight)){
			$goods_weight = 100;//默认100克
		}
		$order_info['goods_list'] = $goods_list;
		//商品种类
		$order_info['goods_type_count'] = $goods_type_count;
		//商品数量
		$order_info['goods_count'] = $goods_count;
		//商品重量
		$order_info['goods_weight'] = $goods_weight;
		//订单总金额
		$order_info['order_total'] = $order_info['total']+$order_info['packing_fare']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']+$order_info['localtown_add_shipping_fare']-$order_info['fare_shipping_free'];
		//收货人地址
		$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
		$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
		$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);
		$order_info['shipping_address'] = $province_info['name'].$city_info['name'].$area_info['name'].$order_info['shipping_address'];
		//收货人经纬度
		$order_distribution_info = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
		//收货人地址纬度
		$order_info['shipping_lat'] = $order_distribution_info['member_lat'];
		//收货人地址经度
		$order_info['shipping_lng'] = $order_distribution_info['member_lon'];
		//商城名称
		$shoname = D('Home/Front')->get_config_by_name('shoname');
		$order_info['shoname'] = $shoname;
		if($data_type == 'imdada'){
			$imdada = new Imdada();
			$result = $imdada->reAddOrder($order_info);
			if($result['status'] == 1){//成功
				//配送费用
				$delivery_fee = array();
				$delivery_fee['delivery_fee'] = $result['result']['fee'];
				D('Seller/Order')->do_send_localtown_thirth_delivery($order_id,$data_type,$delivery_fee);
				$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
				D('Seller/Supply')->update_supply_commission($order_id,$shipping_money["shipping_money"]);
				show_json(1);
			}else{//失败
				show_json(0,  array('msg' => $result['code'].':'.$result['message'] ) );
			}
		}else if($data_type == 'sf'){
			$sfexpress = new Sfexpress();
			$result = $sfexpress->addOrder($order_info);
			if($result['status'] == 1){//成功
				//配送费用
				$delivery_fee = round($result['result']['total_price']/100,2);
				//顺丰订单号
				$delivery_order_id = $result['result']['sf_order_id'];
				//顺丰运单号
				$delivery_bill_id = $result['result']['sf_bill_id'];

				$express_info = array();
				$express_info['delivery_fee'] = $delivery_fee;
				$express_info['delivery_order_id'] = $delivery_order_id;
				$express_info['delivery_bill_id'] = $delivery_bill_id;
				D('Seller/Order')->do_send_localtown_thirth_delivery($order_id,$data_type,$express_info);
				$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
				D('Seller/Supply')->update_supply_commission($order_id,$shipping_money["shipping_money"]);
				show_json(1);
			}else{//失败
				show_json(0,  array('msg' => $result['code'].':'.$result['message'] ) );
			}
		}else if($data_type == 'make')
		{
            $result = D('Home/Make')->addOrder($order_info);

            if( $result['code'] == 0 )
            {
                //码科订单号
                $delivery_order_id = $result['order_number'];

                $express_info = array();
                $express_info['delivery_fee'] = $order_distribution_info['shipping_money'];
                $express_info['delivery_order_id'] = $delivery_order_id;
                $express_info['delivery_bill_id'] = '';

                D('Seller/Order')->do_send_localtown_thirth_delivery($order_id,$data_type,$express_info);
				$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
                D('Seller/Supply')->update_supply_commission($order_id,$shipping_money["shipping_money"]);

                show_json(1);
            }else{
                show_json(0,  array('msg' => $result['message'] ) );
            }
		}else if($data_type == 'ele'){//蜂鸟即配
			$eleDistribution = new EleDistribution();
			$order_code = build_order_no(session('user_auth.uid'));
			//保存蜂鸟即配商户订单号
			M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->save(['order_code'=>$order_code]);
			$order_info['order_num_alias'] = $order_code;
			$result = $eleDistribution->addOrder($order_info);
			if($result['status'] == 1){//成功
				//配送费用
				$delivery_fee = round($result['result']['total_price']/100,2);
				//蜂鸟即配订单号
				$delivery_order_id = $result['result']['sf_order_id'];
				//蜂鸟即配运单号
				$delivery_bill_id = $result['result']['sf_bill_id'];

				$express_info = array();
				$express_info['delivery_fee'] = $delivery_fee;
				$express_info['delivery_order_id'] = $delivery_order_id;
				$express_info['delivery_bill_id'] = $delivery_bill_id;
				D('Seller/Order')->do_send_localtown_thirth_delivery($order_id,$data_type,$express_info);

				$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
				D('Seller/Supply')->update_supply_commission($order_id,$shipping_money["shipping_money"]);
				show_json(1);
			}else{//失败
				show_json(0,  array('msg' => $result['code'].':'.$result['message'] ) );
			}
		}
		else{
			show_json(0,  array('msg' => '不存在第三方配送' ) );
		}
	}

	public function third_cancel_reason(){
		$order_id = I('request.order_id','');
		$third_distribution_type = I('request.third_distribution_type','');
		$this->order_id = $order_id;
		$this->third_distribution_type = $third_distribution_type;
		$this->display();
	}

	/**
	 * 取消第三方配送订单
	 */
	public function thirth_cancel_delivery_order(){
		$_GPC = I('request.');
		$order_id = $_GPC['order_id'];
		if(empty($order_id)){
			show_json(0,  array('msg' => '订单号不存在' ) );
		}
		$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' =>$order_id) )->find();
		if(empty($order_info)){
			show_json(0,  array('msg' => '订单不存在' ) );
		}
		$orderdistribution_info = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' =>$order_id) )->find();
		if(empty($orderdistribution_info)){
			show_json(0,  array('msg' => '配送订单不存在' ) );
		}
		$cancel_reason_id = $_GPC['cancel_reason_id'];
		$cancel_reason = $_GPC['cancel_reason'];
		$data_type = $orderdistribution_info['third_distribution_type'];
		if($orderdistribution_info['state'] == 0 || $orderdistribution_info['state'] == 1 || $orderdistribution_info['state'] == 2){
			if($data_type == 'imdada'){
				$imdada = new Imdada();
				$result = $imdada->cancelOrder($order_info,$cancel_reason_id,$cancel_reason);
				if($result['status'] == 1){//成功
					$deduct_fee = $result['result']['deduct_fee'];
					$other_data = array();
					$other_data['deduct_fee'] = $deduct_fee+$orderdistribution_info['deduct_fee'];
					$other_data['cancel_reason'] = $cancel_reason;
					$other_data['now_deduct_fee'] = $deduct_fee;
					D('Seller/Order')->do_cancel_thirth_delivery_order($order_id,$data_type,$other_data);

					$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
					D('Seller/Supply')->thirth_cancel_supply_commission($order_id,$shipping_money["shipping_money"]);
					show_json(1);
				}else{//失败
					show_json(0,  array('msg' => $result['code'].':'.$result['message'] ) );
				}
			}else if($data_type == 'sf'){
				$sfexpress = new Sfexpress();
				$result = $sfexpress->cancelOrder($orderdistribution_info,$cancel_reason);
				if($result['status'] == 1){//成功
					$other_data = array();
					if(empty($cancel_reason)){
						$cancel_reason = "商家取消";
					}
					$other_data['cancel_reason'] = $cancel_reason;

					$deduct_fee = round($result['result']['deduction_detail']['deduction_fee']/100,2);
					$other_data['deduct_fee'] = $deduct_fee+$orderdistribution_info['deduct_fee'];
					$other_data['now_deduct_fee'] = $deduct_fee;

					D('Seller/Order')->do_cancel_thirth_delivery_order($order_id,$data_type,$other_data);
					$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
					D('Seller/Supply')->thirth_cancel_supply_commission($order_id,$shipping_money["shipping_money"]);

					show_json(1);
				}else{//失败
					show_json(0,  array('msg' => $result['code'].':'.$result['message'] ) );
				}
			}else if( $data_type == 'make' )
			{
				//$orderdistribution_info
				$third_order_id = $orderdistribution_info['third_order_id'];
				$result = D('Home/Make')->cancelOrder( $third_order_id );

				if( $result['code'] == 0 )
				{
                    $other_data = array();
                    if(empty($cancel_reason)){
                        $cancel_reason = "商家取消";
                    }
                    $other_data['cancel_reason'] = $cancel_reason;

                    $deduct_fee = 0;
                    $other_data['deduct_fee'] = $deduct_fee+$orderdistribution_info['deduct_fee'];
                    $other_data['now_deduct_fee'] = $deduct_fee;

                    D('Seller/Order')->do_cancel_thirth_delivery_order($order_id,$data_type,$other_data);
                    $shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
					//D('Seller/Supply')->thirth_cancel_supply_commission($order_id,$shipping_money["shipping_money"]);
					//load_model_class('supply')->thirth_cancel_supply_commission($order_id,$shipping_money["shipping_money"]);
                    show_json(1);
				}else{
                    show_json(0,  array('msg' => $result['message'] ) );
				}
			}else if($data_type == 'ele'){
				$eleDistribution = new EleDistribution();
				$result = $eleDistribution->cancelOrder($orderdistribution_info,$cancel_reason_id,$cancel_reason);
				if($result['status'] == 1){//成功
					$other_data = array();
					if(empty($cancel_reason)){
						$cancel_reason = "商家取消";
					}
					$other_data['cancel_reason'] = $cancel_reason;

					$deduct_fee = round($result['result']['deduction_detail']['deduction_fee']/100,2);
					$other_data['deduct_fee'] = $deduct_fee+$orderdistribution_info['deduct_fee'];
					$other_data['now_deduct_fee'] = $deduct_fee;

					D('Seller/Order')->do_cancel_thirth_delivery_order($order_id,$data_type,$other_data);
					$shipping_money =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
					D('Seller/Supply')->thirth_cancel_supply_commission($order_id,$shipping_money["shipping_money"]);

					show_json(1);
				}else{//失败
					show_json(0,  array('msg' => $result['code'].':'.$result['message'] ) );
				}
			}
		}else{
			show_json(0,  array('msg' => '配送订单无法取消' ) );
		}
	}


	/**
	 * 达达平台模拟取货
	 */
	public function orderFetch(){
		$_GPC = I('request.');
		$order_sn = $_GPC['order_sn'];
		$type = $_GPC['type'];
		$imdada = new Imdada();
		if($type == 1){
			$imdada->orderFetch($order_sn);//模拟取货
		}else if($type == 2){
			$imdada->orderFinish($order_sn);//完成订单
		}else if($type == 3){
			$imdada->orderCancel($order_sn);//取消订单
		}else if($type == 4){
			$imdada->orderAbnormal($order_sn);//异常妥投物品返还中
		}
	}

	public function third_delivery_log_list(){
		$order_id = I('request.order_id','');

		$orderdistribution_info = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' =>$order_id) )->find();
		$distribution_type = $orderdistribution_info['third_distribution_type'];
		$log_list = M('eaterplanet_ecommerce_orderdistribution_thirth_log')->where( array('order_id' =>$order_id,'third_distribution_type'=>$distribution_type) )->order('addtime desc')->select();

		$third_name = "";
		if($distribution_type == 'imdada'){
			$third_name = '达达平台';
		}else if($distribution_type == 'sf'){
			$third_name = '顺丰同城';
		}else if($distribution_type == 'make'){
            $third_name = '码科配送';
		}else if($distribution_type == 'uupt'){
			$third_name = 'UU跑腿';
		}else if($distribution_type == 'dianwoda'){
			$third_name = '点我达';
		}
		$this->third_name = $third_name;
		$this->log_list = $log_list;
		$this->orderdistribution_info = $orderdistribution_info;
		$this->display();
	}

	//end
	/**
	 * 订单核销
	 */
	public function order_hexiao()
	{

	    $opdata = $this->check_order_data();
	    extract($opdata);

	    if( $item['delivery'] == 'hexiao' )
	    {
	        D('Seller/Order')->hexiao_all_orders($item['order_id']);
	    }

	    show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	/**
	 * 商品按订单核销
	 */
	public function order_goods_hexiao(){
	    $opdata = $this->check_order_data();
	    extract($opdata);
	    $order_goods_id = I('request.order_goods_id','');

	    $order_hexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('order_id' => $id, 'order_goods_id' => $order_goods_id) )->find();
	    if (empty($order_hexiao_info)) {
	        show_json(0, '未找到核销订单!');
	    }
	    D('Seller/Order')->hexiao_order_goods($order_hexiao_info,0);
	    D('Seller/Order')->hexiao_finished($id,'后台确认使用，订单完成');
	    show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	/**
	 * 查看核销记录
	 */
	public function view_hexiao_history(){
	    $opdata = $this->get_goods_saleshexiao();
	    extract($opdata);

	    $data = D('Seller/Order')->get_goods_hexiao_record();

	    $this->order_hexiao_info = $order_hexiao_info;
	    $this->hx_list = $data['hx_list'];
	    $this->display("hexiao_history");
	}
	/**
	 * 按次数核销商品
	 */
	public function hexiao_times(){
	    $opdata = $this->get_goods_saleshexiao();
	    extract($opdata);

	    $data = D('Seller/Order')->get_goods_hexiao_record();

	    $this->order_hexiao_info = $order_hexiao_info;
	    $this->hx_list = $data['hx_list'];
	    $this->hx_count = $data['hx_count'];
	    $this->display();
	}

	/**
	 * 获取商品核销信息
	 * @return
	 */
	public function get_goods_saleshexiao(){
	    $opdata = $this->check_order_data();
	    extract($opdata);
	    $order_goods_id = I('request.order_goods_id','');

	    $order_hexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('order_id' => $id, 'order_goods_id' => $order_goods_id) )->find();
	    if (empty($order_hexiao_info)) {
	        show_json(0, '未找到核销订单!');
	    }
	    $order_goods_info = M('eaterplanet_ecommerce_order_goods')->where(array('order_goods_id'=>$order_hexiao_info['order_goods_id']))->field('goods_id,name as goods_name,goods_images,quantity')->find();
	    $order_hexiao_info['goods_id'] = $order_goods_info['goods_id'];
	    $order_hexiao_info['goods_name'] = $order_goods_info['goods_name'];
	    $order_hexiao_info['goods_images'] = tomedia($order_goods_info['goods_images']);
	    $order_hexiao_info['quantity'] = $order_goods_info['quantity'];
	    return array('order_hexiao_info' => $order_hexiao_info);
	}
	/**
	 * 核销商品使用
	 */
	public function hexiao_goods(){
	    $hx_id = I('request.hx_id','');
	    $hx_count = I('request.hx_count','');
	    if(empty($hx_id)){
	        show_json(0, '核销商品数据错误！');
	    }
	    if(empty($hx_count)){
	        show_json(0, '核销商品数量错误！');
	    }
	    $order_hexiao_info = M('eaterplanet_ecommerce_order_goods_saleshexiao')->where( array('id' => $hx_id) )->find();
	    if (empty($order_hexiao_info)) {
	        show_json(0, '未找到核销订单!');
	    }
	    D('Seller/Order')->hexiao_order_goods($order_hexiao_info,$hx_count);
	    D('Seller/Order')->hexiao_finished($order_hexiao_info['order_id'],'后台确认使用，订单完成');
	    show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	/**
	 * 查看商品指定核销信息
	 */
	public function hexiao_goods_assign_salesroom(){
	    $goods_id = I('request.goods_id','');
	    $order_goods_id = I('request.order_goods_id','');
	    if(empty($goods_id)){
	        show_json(0, '商品信息错误！');
	    }
	    if(empty($order_goods_id)){
	        show_json(0, '核销商品信息错误！');
	    }
	    $order_goods_info = M('eaterplanet_ecommerce_order_goods')->where(array('order_goods_id'=>$order_goods_id))->field('goods_id,name as goods_name,goods_images,quantity')->find();

	    $goods_salesroom_info = D('Seller/Order')->get_goods_assign_salesroom($goods_id);
	    $this->salesroom_list = $goods_salesroom_info['salesroom_list'];
	    $this->smember_list = $goods_salesroom_info['smember_list'];
	    $this->goods_info = $order_goods_info;
	    $this->display();
	}


	public function checkLocalTownOrderCanRefund($item){
		if($item['delivery'] == 'localtown_delivery'){//同城配送订单
			if($item['order_status_id'] == 4){//已发货不能退款
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}

}
?>
