<?php
namespace Seller\Controller;
use Admin\Model\StatisticsModel;
class IndexController extends CommonController {
   	protected function _initialize(){
   	    parent::_initialize();

   	}

    public function index(){

		//cookie('http_refer',$_SERVER['HTTP_REFERER']);
		//cookie('last_login_page');

		$is_new = I('get.is_new', 0 );

		if( $is_new == 1 )
		{
			//切换到新后台，
			cookie('is_new_backadmin',1);

			$data = array();
			$data['is_new_backadmin'] = 1;

			D('Seller/Config')->update($data);

		}else if( $is_new == 2 ){
			//切换到旧后台，
			cookie('is_new_backadmin',2);

			$data = array();
			$data['is_new_backadmin'] = 2;

			D('Seller/Config')->update($data);
		}

		$is_show_notice = true;

		$is_show_notice001 = D('Home/Front')->get_config_by_name('is_show_notice001');

		if( !isset($is_show_notice001) )
		{
			$data = array();
			$data['is_show_notice001'] = 1;

			D('Seller/Config')->update($data);
		}

		$this->is_show_notice001 = $is_show_notice001;

		$is_new_backadmin = D('Home/Front')->get_config_by_name('is_new_backadmin');

		$is_can_update = 1;

		if(SELLERUID != 1)
		{

			$seller_info = M('seller')->field('s_role_id')->where( array('s_id' => SELLERUID ) )->find();

			$perm_role = M('eaterplanet_ecommerce_perm_role')->where( array('id' => $seller_info['s_role_id']) )->find();

			$perms_str = $perm_role['perms2'];


			if( strpos($perms_str, 'system') !== false)
			{
				$is_can_update = 1;
			}else{
				$is_can_update =  0;
			}

			$perms_arr = M('eaterplanet_ecommerce_perm_role')->where( array('id' => $seller_info['s_role_id']) )->find();

			//goods,goods/goods/index,goods.goods.goodscategory,goods.goods.goodsspec,goods.goods.goodstag,goods.goods.config,goods.goods.settime,goods.goods.industrial

			$perms1 = str_replace('.','/',$perms_arr['perms2']);

			$perms2 = explode(",", $perms1);

			$perms = explode("/",$perms2[1]);

			$perm_url = $perms[1]."/".$perms[2];
			$this->perm_url = $perm_url;

		}
		$this->is_can_update = $is_can_update;



		if( empty($is_new_backadmin) || $is_new_backadmin == 2 )
		{
			$this->display();
		}else{
			$this->display('new_index');
		}

		//$this->display();
        //$this->display('new_index');
    }

	public function analys ()
	{
		//今天时间
		$today_time = strtotime( date('Y-m-d '.'00:00:00') );

		//支付成功订单数
		$today_success_where = " and order_status_id in (1,4,6,7,11,14) and pay_time > {$today_time} and type <> 'integral' ";
		$today_success_order_count =  D('Seller/Order')->get_order_count($today_success_where);

		//支付取消订单数
		$today_cancel_where = " and order_status_id = 5 and date_added > {$today_time} and type <> 'integral' ";
		$today_cancel_order_count =  D('Seller/Order')->get_order_count($today_cancel_where);

		$this->today_success_order_count = $today_success_order_count;
		$this->today_cancel_order_count = $today_cancel_order_count;


		//余额支付   yuer_pay_money_info
		$yuer_pay_where = " and payment_code ='yuer' and pay_time > {$today_time} and type <> 'integral' ";
		$yuer_pay_money_info = D('Seller/Order')->get_order_sum(' sum(total+shipping_fare-voucher_credit-fullreduction_money) as total ' , $yuer_pay_where);
		$yuer_pay_money = empty($yuer_pay_money_info['total']) ? 0:$yuer_pay_money_info['total'];
		$yuer_pay_money = sprintf("%.2f",$yuer_pay_money);
		$this->yuer_pay_money = $yuer_pay_money;

		//在线付款   payment_code支付方式  1.weixin  2.admin   3.yuer   online_pay_money_info
		$online_pay_where = " and payment_code ='weixin' and pay_time > {$today_time} and type <> 'integral' ";
		$online_pay_money_info = D('Seller/Order')->get_order_sum(' sum(total+shipping_fare-voucher_credit-fullreduction_money) as total ' , $online_pay_where);
		$online_pay_money = empty($online_pay_money_info['total']) ? 0:$online_pay_money_info['total'];
		$online_pay_money = sprintf("%.2f",$online_pay_money);
		$this->online_pay_money = $online_pay_money;

		//积分抵现  score_for_money 积分抵现金额
		$score_for_money_where = " and score_for_money > 0 and pay_time > {$today_time} and type <> 'integral' ";
		$score_for_money_info = M('eaterplanet_ecommerce_order')->field(' sum(score_for_money) as score_for_money')->where("1 ".$score_for_money_where )->find();
		$score_for_money = empty($score_for_money_info['score_for_money']) ? 0:$score_for_money_info['score_for_money'];
		$score_for_money = sprintf("%.2f",$score_for_money);
		$this->score_for_money = $score_for_money;


		//使用积分
		
		$sum_score = 0;

		$sum_score = M('eaterplanet_ecommerce_member_integral_flow')->where(' addtime > '.$today_time.' and type = "orderbuy" and in_out = "out"  ')->sum('score');
		if(empty($sum_score)){
			$sum_score = 0;
		}
		$this->sum_score = $sum_score;

		$this->display();
	}

	public function order_count()
	{

		//语音播报
		$voice_notice = D('Home/Front')->get_config_by_name('is_open_order_voice_notice');
		//商户语音播报
		$is_open_supply_voice = D('Home/Front')->get_config_by_name('is_open_supply_voice');



		//获取有多少条的通知
		$day_time = strtotime( date('Y-m-d '.'00:00:00') );

		$day_key = 'new_ordernotice_'.$day_time;

		$day_arr = S( $day_key );
		$supply_arr = array();

		foreach( $day_arr as $key => $val )
		{
			$order_goods = M('eaterplanet_ecommerce_order_goods')->field('supply_id')->where( array('order_id' => $val ) )->find();
		    $supply_arr[$val]['supply_id'] = $order_goods['supply_id'] ;
		}

		foreach( $supply_arr as $key1 => $val1 )
		{
			$a[$key1] = $val1['supply_id'];

		}
		//每个商户对应订单数
		$supply_order_count = array_count_values($a);
		$order_count = 0;

		$info =array();

		if (!defined('ROLE') || ROLE != 'agenter' ){
			if( !empty($day_arr) )
			{
				//总订单数
				$order_count = count($day_arr);
			}
			$order_info = M('eaterplanet_ecommerce_order')->where( "order_status_id =1 and type != 'ignore'")->order('order_id desc')->find();
			//非商户
			if(!empty($order_count)){
					$info =array(
							"resultCode"=>200,
							"message"=>"success",
							"data"=>$order_count,
							"voice_notice"=>$voice_notice,
							"order_type"=>$order_info['type']
					);

				}

		}else{
			//商户
			if(!empty($is_open_supply_voice)){
				//获取商户id
				$supply_id = $_SESSION["dejavutech_seller_s"]["agent_auth"]["uid"];
				foreach($supply_order_count as $key2=>$value){
					if( $supply_id == $key2 ) {
						$order_count = $value;
					}
				}
				if(!empty($order_count)){

					$info =array(
							"resultCode"=>200,
							"message"=>"success",
							"data"=>$order_count,
							"voice_notice"=>$voice_notice
					);

				}
			}else{

			}

		}




		echo json_encode($info);
		die();
	}

	public function updatelog()
	{
		$auth_url ="http://pintuan.ch871.com/upgrade_dan.php";

		$version_info = M('eaterplanet_ecommerce_config')->where( array('name' => 'site_version') )->find();

		$version = $version_info['value'];

		$cur_release_info = M('eaterplanet_ecommerce_config')->where( array('name' => 'site_version') )->find();

		$cur_release = $cur_release_info['value'];

		$url = D('Home/Front')->get_config_by_name('shop_domain');
		$release = $cur_release;


		$modname = 'eaterplanet_ecommerce';
		$domain = trim(preg_replace('/http(s)?:\\/\\//', '', rtrim($url, '/')));
		$ip = gethostbyname($_SERVER['HTTP_HOST']);

		$resp = http_request($auth_url, array('action' => 'update_log','ip' => $ip,'release' => $release,'version' => $version, 'domain' => $domain) );

		$banben_list = @json_decode($resp, true);
		//version 版本号  release 时间 => 201901082100   desc 日志

		if($banben_list["result"]["version_desc"]){

				$banben_list['cur_version'] = $version;
				$banben_list['cur_release'] = $cur_release;

				$this->banben_list = $banben_list;
				include $this->display('Index/auth_updatelog');


		}else{
			$i =0 ;
			foreach( $banben_list as $key => $var){


				$data1[$i]['version'] = $var['version'];

				$year = substr($var['release'],2,2);
				$month =substr($var['release'],4,2);
				$day = substr($var['release'],6,2);
				$data1[$i]['release'] = "20".$year."-".$month."-".$day;

				$data1[$i]['desc'] = $var['desc'];

				$i++;
			}

			$data = array_reverse($data1);
			$this->data = $data;
			$this->display();
		}
	}

	/**
	 * 配送消息发送
	 */
	public function order_distribution(){
		$result = array();
		$result['no_send'] = D('Seller/Redisorder')->send_distribution_delivery_message();
		echo json_encode($result);
		die();
	}


}
