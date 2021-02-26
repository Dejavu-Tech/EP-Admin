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

class CronController extends CommonController {
    protected function _initialize()
    {
    	parent::_initialize();

    }

	public function index()
	{
		ignore_user_abort();
		set_time_limit(0);

		//------------------系统未支付订单超时关闭
		$lasttime = S('closeorder_lasttime');

		$lasttime = strtotime($lasttime);

		$interval = 3;//1分钟


		$interval *= 60;
		$current = time();

		//shop_domain get_config_by_name($name)

		$url = D('Home/Front')->get_config_by_name('shop_domain');

		$url = $url."/index.php?s=/Cron/close";




		if (($lasttime + $interval) <= $current) {

			S('closeorder_lasttime',  date('Y-m-d H:i:s', $current));
			$url = $url."/index.php?s=/Cron/close";

			ihttp_request($url, NULL, NULL, 1);

			//ihttp_request($url, $post = '', $extra = array(), $timeout = 60)
		}

		$url = D('Home/Front')->get_config_by_name('shop_domain');

		$url = $url."/index.php?s=/Cron/statement";

		//begin
		$today_zero_time = strtotime( date('Y-m-d').' 00:00:00' );

		$clear_goodsdaysales = D('Home/Front')->get_config_by_name('clear_goodsdaysales');
		//$clear_goodsdaysales  = S('clear_goodsdaysales');
		if( !isset($today_zero_time) || $clear_goodsdaysales != $today_zero_time )
		{
		    D('Seller/Commonorder')->clear_goods_daysales();
		    //S('clear_goodsdaysales', $today_zero_time );

			$config_data = array();
			$config_data['clear_goodsdaysales'] = $today_zero_time;
			D('Seller/Config')->update($config_data);
		}
		//end


		$lasttimestatement  = S('statementorder');

		if( empty($lasttimestatement) )
		{
			$lasttimestatement = 0;
		}

		$intervalstatement = 1;//1分钟


		$intervalstatement *= 60;
		$currentstatement = time();


		if (($lasttimestatement + $intervalstatement) <= $currentstatement) {

			S('statementorder', $currentstatement);

		    ihttp_request($url, NULL, NULL, 1);
		}

		//---
		$lasttimeautoreciveorder = $resultstatement = S('autoreciveorder');

		$intervalstatement = 1;//1分钟

		$intervalstatement *= 60;
		$currentstatement = time();

		$url = D('Home/Front')->get_config_by_name('shop_domain');
		$url = $url."/index.php?s=/Cron/receive";

		if (($lasttimeautoreciveorder + $intervalstatement) <= $currentstatement) {

			S('autoreciveorder', $currentstatement);

			ihttp_request($url, NULL, NULL, 1);
		}



		echo 3;
	}

	/**

	**/
	public function receive()
	{
		ignore_user_abort();
		set_time_limit(0);

		$shop_list = M('eaterplanet_ecommerce_config')->field('value')->where( array('name' => 'open_auto_recive_order' ) )->select();

		foreach($shop_list  as $shop)
		{

			$open_auto_recive_order = $shop['value'];

			if($open_auto_recive_order == 1)
			{

				$receive_day = D('Home/Front')->get_config_by_name('auto_recive_order_time');

				$receive_hour_time = time() - 86400 * $receive_day;


				$order_list = M('eaterplanet_ecommerce_order')->field('order_id,delivery')->where( "express_time <={$receive_hour_time} and order_status_id =4" )->select();

				foreach($order_list as $order )
				{

					//检查是否有部分退款

					$order_refund_info = M('eaterplanet_ecommerce_order_refund')->field('ref_id')->where( array('order_id' => $order['order_id'], 'state' => 0 ) )->find();

					if( !empty($order_refund_info) )
					{
						continue;
					}
					if($order['delivery'] != 'hexiao'){
						D('Home/Frontorder')->receive_order($order['order_id'], true);
					}
				}
			}
		}

	}

	public function statement()
	{
		ignore_user_abort();
		set_time_limit(0);

		//S('closeorder_lasttime');

		$statementorder_flag = S('statementorder_flag');
		if( !empty($statementorder_flag) && $statementorder_flag == 1 )
		{
         	 S('statementorder_flag', 0);
			die();
		}
		 S('statementorder_flag', 1);


		$shop_list = M('eaterplanet_ecommerce_config')->field('value')->where( array('name' => 'open_aftersale' ) )->select();


		foreach($shop_list  as $shop)
		{

			$open_aftersale = $shop['value'];

			if($open_aftersale == 1)
			{

				$time = time();

				$sql = "SELECT o.order_id , og.order_goods_id  FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o , ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
					WHERE  o.order_id = og.order_id and o.order_status_id in(6,11) and  og.is_statements_state = 0 and og.statements_end_time<{$time} order by o.order_id desc ";

				$order_list = M()->query($sql);


				foreach($order_list as $order )
				{
					D('Home/Frontorder')->settlement_order($order['order_id']);
				}
			}else{
				$time = time()-5*60;

				$sql = "SELECT o.order_id , og.order_goods_id  FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o , ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
					WHERE  o.order_id = og.order_id and o.order_status_id in(6,11) and  og.is_statements_state = 0 and o.receive_time < $time  order by o.order_id desc ";

				$order_list = M()->query($sql);

				foreach($order_list as $order )
				{
					D('Home/Frontorder')->settlement_order($order['order_id']);
				}
			}
		}


		S('statementorder_flag', 0);

		$runtimelog_flag = S('runtimelog_flag');
		$next_time = time()+86400;

		if( empty($runtimelog_flag) || $runtimelog_flag > $next_time )
		{
			$hour = date('H');

			$hour = intval($hour);

			if( $hour == 2 )
			{
				$this->clear_runtimelog();

				S('statementorder_flag', time() );
			}
		}

	}

	private function clear_runtimelog()
	{
		$logs_path = ROOT_PATH.'Runtime/Logs';
		$cache_path = ROOT_PATH.'Runtime/Cache';
		$temp_path = ROOT_PATH.'Runtime/Temp';


		$this->removeDir($logs_path);
		$this->removeDir($cache_path);
		$this->removeDir($temp_path);

		echo 'ok';
	}

		/**
	   * removeDir 删除文件夹下所有文件
	   * @param $path
	   */
	private  function removeDir($path)
	{
		if(is_dir($path)){
			if($handle = opendir($path)){
				while($file = readdir($handle)){
					if(is_dir($path.'/'.$file.'/') && $file!='.' && $file!='..'){
						$this->removeDir($path.'/'.$file.'/');
					} else {
						if($file!='.' && $file!='..'){
							@unlink($path.'/'.$file);
						}
					}
				}
				closedir($handle);
			}
		}
	}

	public function refund()
	{
		ignore_user_abort();
		set_time_limit(0);

		$daytimenow_ev56_s = time();
		$condition = " state=0 and end_time < ".$daytimenow_ev56_s;

		$pin_list = M()->query("select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_pin
					where  state=0 and end_time <{$daytimenow_ev56_s} order by pin_id asc ");

		$weixin_model = D('Home/Weixin');

		$pintuan_isvirs_success = D('Home/Front')->get_config_by_name('pintuan_isvirs_success');



		if(!empty($pin_list))  {
			foreach($pin_list as $pin)
			{
				//暂时屏蔽


				if( isset($pintuan_isvirs_success) && $pintuan_isvirs_success == 1 )
				{
					$res = D('Home/Pin')->jia_over_order( $pin['pin_id'] );

					if( $res )
					{
						continue;
					}
				}


				M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin['pin_id'] ) )->save( array('state' => 2) );

				$pin_order_list = M('eaterplanet_ecommerce_pin_order')->where( array('pin_id' => $pin['pin_id'] ) )->select();

				$order_ids = array();
				foreach($pin_order_list as $vv)
				{
					$order_ids[] = $vv['order_id'];
				}

				$order_ids_str = implode(',', $order_ids);

				$order_list = M('eaterplanet_ecommerce_order')->field('order_id,type')->where(" order_id in ({$order_ids_str}) and order_status_id=2 ")->select();

				$can_cg_state = true;



				foreach($order_list as $order)
				{

					if( $order['type'] != 'ignore' )
					{
						$res = $weixin_model->refundOrder($order['order_id'], 0);


						if( $res['code'] == 0 )
						{
						    //拼团失败，订单退款
						    $history_data = array();
						    $history_data['order_id'] = $order['order_id'];
						    $history_data['order_status_id'] = 7;
						    $history_data['notify'] = 0;
						    $history_data['comment'] = '拼团失败，订单退款失败，错误原因： '.$res['msg'];
						    $history_data['date_added'] = time();

						    M('eaterplanet_ecommerce_order_history')->add( $history_data );

						}else{
						    M('eaterplanet_ecommerce_order')->where( array('order_id' => $order['order_id']) )->save( array('order_status_id' => 7) );

						    //拼团失败，订单退款
						    $history_data = array();
						    $history_data['order_id'] = $order['order_id'];
						    $history_data['order_status_id'] = 7;
						    $history_data['notify'] = 0;
						    $history_data['comment'] = '拼团失败，订单退款';
						    $history_data['date_added'] = time();

						    M('eaterplanet_ecommerce_order_history')->add( $history_data );

						    D('Seller/Commonorder')->refund_one_order( $order['order_id'] , 1);

						}
						//




					}else{
						M('eaterplanet_ecommerce_order')->where( array('order_id' => $order['order_id'] ) )->save( array('order_status_id' => 7) );
						$res = array('code' => 1);
					}


					if( $res['code'] == 1)
					{

					}else{
						$can_cg_state = false;
					}
				}
				if( !$can_cg_state )
				{

					M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin['pin_id'] ) )->save(  array('state' => 0) );
				}

			}
		}

		//--

		$infos = M('eaterplanet_ecommerce_config')->where( array('name' => 'statewaitorder') )->find();

		if( empty($infos) )
		{
			$lasttime = 0;
		}else{
			$lasttime = $infos['value'];
		}


		$interval = 3;

		$interval *= 60;
		$current = time();

		if (($lasttime + $interval) <= $current  ) {

			if( empty($infos) )
			{
				$ins_data = array();
				$ins_data['name'] = 'statewaitorder';
				$ins_data['value'] = $current;

				M('eaterplanet_ecommerce_config')->add( $ins_data );

			}else{
				M('eaterplanet_ecommerce_config')->where( array('id' => $infos['id']) )->save( array('value' => $current) );
			}

			$sql ="SELECT ho.order_id,o.order_num_alias,ho.order_goods_id FROM ".
					C('DB_PREFIX')."eaterplanet_community_head_commiss_order as ho left join ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o on ho.order_id = o.order_id	where  ho.state = 0 and o.order_status_id IN (6,11)";

			$xiufu_list = M()->query($sql);

			$need_order = array();

			//var_dump($xiufu_list);die();

		   foreach( $xiufu_list  as $vv )
		   {
				$open_aftersale = D('Home/Front')->get_config_by_name('open_aftersale');
				if( empty($open_aftersale) )
				{
					$open_aftersale = 0;
				}

				if( $open_aftersale == 1 )
				{
					$n_sql = "select hco.*, og.order_goods_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og left join  ".
							C('DB_PREFIX')."eaterplanet_community_head_commiss_order as hco on og.order_goods_id = hco.order_goods_id where hco.state = 0 and og.is_statements_state=1 and og.order_id = ".$vv['order_id'];

					$info_list = M()->query($n_sql);




					if( !empty($info_list) )
					{


						foreach($info_list as $info)
						{
							M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $info['order_goods_id'] ) )->save( array('is_statements_state' => 0) );
						}
					}

				}else{

					if( empty($need_order) || !in_array($vv['order_id'], $need_order)  )
					{

						if( $vv['order_goods_id'] > 0 )
						{
							M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $vv['order_goods_id'] ) )->save( array('is_statements_state' => 0) );
						}else{
							M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $vv['order_id'] ) )->save( array('is_statements_state' => 0) );
						}

						D('Home/Frontorder')->settlement_order($vv['order_id']);
					}
				}

		   }
		}



	    $sql ="SELECT ho.order_id,o.order_num_alias,ho.order_goods_id FROM ".C('DB_PREFIX').
				"eaterplanet_ecommerce_pintuan_commiss_order as ho left join ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o on ho.order_id = o.order_id
				where  ho.state = 0 and o.order_status_id IN (6,11)";

		$pintuan_xiufu_list = M()->query($sql);


	   $need_order = array();

	   foreach( $pintuan_xiufu_list  as $vv )
	   {
			$open_aftersale = D('Home/Front')->get_config_by_name('open_aftersale');
			if( empty($open_aftersale) )
			{
				$open_aftersale = 0;
			}

			if( $open_aftersale == 1 )
			{
				$n_sql = "select hco.*, og.order_goods_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og left join  ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as hco on og.order_goods_id = hco.order_goods_id where hco.state = 0 and og.is_statements_state=1 and og.order_id = ".$vv['order_id'];
				$info_list = M()->query($n_sql);

				if( !empty($info_list) )
				{
					foreach($info_list as $info)
					{
						M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $info['order_goods_id'] ) )->save( array('is_statements_state' => 0) );
					}
				}

			}else{

				if( empty($need_order) || !in_array($vv['order_id'], $need_order)  )
				{

					if( $vv['order_goods_id'] > 0 )
					{
						M('eaterplanet_ecommerce_order_goods')->where( array('order_goods_id' => $vv['order_goods_id'] ) )->save( array('is_statements_state' => 0) );
					}else{
						M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $vv['order_id'] ) )->save( array('is_statements_state' => 0) );
					}

					D('Home/Frontorder')->settlement_order($vv['order_id']);
				}
			}

	   }

		//核销订单过期处理
		D('Home/Salesroom')->hexiao_expire();
		//核销商品过期处理
		D('Home/Salesroom')->hexiao_goods_expire();

		echo 'ok';
		die();

	}

	public function close()
	{
		ignore_user_abort();
		set_time_limit(0);


		$redis_new_redis = D('Home/Front')->get_config_by_name('redis_new_redis');
		$open_redis_server = D('Home/Front')->get_config_by_name('open_redis_server');


		if( empty($redis_new_redis)  && !empty($open_redis_server) && $open_redis_server == 1 )
		{
			D('Seller/Redisorder')->sysnc_allgoods_total();
		}

		$shop_list = M('eaterplanet_ecommerce_config')->field('value')->where( array('name' => 'open_auto_delete') )->select();

		foreach($shop_list  as $shop)
		{

			$open_auto_delete = $shop['value'];

			if($open_auto_delete == 1)
			{
				//auto_cancle_order_time

				$cancle_hour = D('Home/Front')->get_config_by_name('auto_cancle_order_time');

				$cancle_hour_time = time() - 3600 * $cancle_hour;

				$sql = "select order_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_order
						where date_added <={$cancle_hour_time}  and order_status_id =3 ";

				$order_list =  M()->query($sql);


				foreach($order_list as $order )
				{
					D('Home/Frontorder')->cancel_order($order['order_id'], true);
				}
			}
		}


	}


	public function templatemsg()
	{

		$template_list = M('eaterplanet_ecommerce_templatemsg')->where( array('state' => 0) )->order('id asc')->limit( 100 )->select();


		foreach($template_list  as $template)
		{

			if( $template['type'] == 0 )
			{
				//发给个人
				$url = D('Home/Front')->get_config_by_name('shop_domain');

				$wx_template_data = array();

				$wx_template_data = unserialize($template['template_data']);

				$template_id = $template['template_id'];

				$pagepath = substr($template['url'],1);


				$member_info = M('eaterplanet_ecommerce_member')->field('member_id,we_openid')->where( array('we_openid' => $template['open_id'] ) )->find();

				$member_formid_info = M('eaterplanet_ecommerce_member_formid')->where( " member_id=".$member_info['member_id']." and formid != '' and state = 0 " )->order('id desc')->find();

				if( !empty($member_formid_info) )
				{
					$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
					$weixin_template_pay_order = D('Home/Front')->get_config_by_name('weixin_template_pay_order');

					$res = D('Seller/User')->send_wxtemplate_msg($wx_template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'],0,array() );

					M('eaterplanet_ecommerce_member_formid')->where( array('id' => $member_formid_info['id'] ) )->save( array('state' => 1) );

				}

				M('eaterplanet_ecommerce_templatemsg')->where( array('id' => $template['id'] ) )->save( array('state' => 1) );

			}else if( $template['type'] == 1 )
			{
				//发送给所有人

				$offset = $template['send_total_count'];

				$limit = 50;

				$member_info_list = M('eaterplanet_ecommerce_member')->field('member_id ,we_openid')->order('member_id asc')->limit( $offset,$limit )->select();

				$url = D('Home/Front')->get_config_by_name('shop_domain');

				$wx_template_data = array();

				$wx_template_data = unserialize($template['template_data']);

				$template_id = $template['template_id'];

				$pagepath = substr($template['url'],1);


				foreach($member_info_list as $member_info )
				{

					$member_formid_info = M('eaterplanet_ecommerce_member_formid')->where( " member_id=".$member_info['member_id']." and formid != '' and state = 0 " )->order('id desc')->find();


					if( !empty($member_formid_info) )
					{
						$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
						$weixin_template_pay_order = D('Home/Front')->get_config_by_name('weixin_template_pay_order');

						$res = D('Seller/User')->send_wxtemplate_msg($wx_template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'],0,array() );

						M('eaterplanet_ecommerce_member_formid')->where( array('id' => $member_formid_info['id'] ) )->save( array('state' => 1) );
					}
				}

				$new_f = $offset+$limit;

				M('eaterplanet_ecommerce_templatemsg')->where( array('id' => $template['id'] ) )->save( array('send_total_count' => $new_f ) );

				if( $offset+$limit >= $template['total_count'] )
				{
					M('eaterplanet_ecommerce_templatemsg')->where( array('id' => $template['id'] ) )->save( array('state' => 1) );
				}

			}

			/** ---end--- **/
			echo 'success <br/>';
		}


		echo 'ok';
		die();
	}


}
