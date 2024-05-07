<?php

namespace Seller\Model;

use App\Api\PrinterService;
use App\Oauth\YlyOauthClient;
use App\Config\YlyConfig;
use App\Api\PrintService;

class PrintactionModel
{
	private function _info()
    {

		$user = 'zhrrobert@hotmail.com';	//*必填*：飞鹅云后台注册账号
		$ukey = 'EX3x6QyxvdaNnZpH';	//*必填*: 飞鹅云注册账号后生成的UKEY
		$sn = '932597917';	//*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API

		//以下参数不需要修改
		$ip = 'api.feieyun.cn';//接口IP或域名
		$port = 80; //接口IP端口
		$path = '/Api/Open/';//接口路径

		$stime = time(); //公共参数，请求时间
		$sig = sha1($user.$ukey.$stime);  //公共参数，请求公钥

		return array(
					'user' => $user,
					'ukey' => $ukey,
					'ip' => $ip,
					'port' => $port,
					'path' => $path,
					'stime' => $stime,
					'sig' => $sig,
			);
    }

	//===========添加打印机接口（支持批量）=============
	//***接口返回值说明***
	//正确例子：{"msg":"ok","ret":0,"data":{"ok":["sn#key#remark#carnum","316500011#abcdefgh#快餐前台"],"no":["316500012#abcdefgh#快餐前台#13688889999  （错误：识别码不正确）"]},"serverExecutedTime":3}
	//错误：{"msg":"参数错误 : 该帐号未注册.","ret":-2,"data":null,"serverExecutedTime":37}

	//打开注释可测试
	//提示：打印机编号(必填) # 打印机识别码(必填) # 备注名称(选填) # 流量卡号码(选填)，多台打印机请换行（\n）添加新打印机信息，每次最多100行(台)。
	//$snlist = "sn1#key1#remark1#carnum1\nsn2#key2#remark2#carnum2";
	//addprinter($snlist);
	function addprinter($snlist)
	{
		$info = $this->_info();

		$content = array(
			'user'=>$info['user'],
			'stime'=>$info['stime'],
			'sig'=>$info['sig'],
			'apiname'=>'Open_printerAddlist',
			'printerContent'=>$snlist
		);

		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
		require_once $lib_path."/Weixin/Httpclient.class.php";
		$client = new \Httpclient($info['ip'],$info['port']);


		$res = $client->post($info['path'],$content);

		if(!$res){
			// var_dump($res);die();
		}
		else{
			$result_json = $client->getContent();

			$result = json_decode($result_json, true);

		}

	}


	//==================方法1.打印订单==================
		//***接口返回值说明***
		//正确例子：{"msg":"ok","ret":0,"data":"316500004_20160823165104_1853029628","serverExecutedTime":6}
		//错误：{"msg":"错误信息.","ret":非零错误码,"data":null,"serverExecutedTime":5}


		//标签说明：
		//单标签:
		//"<BR>"为换行,"<CUT>"为切刀指令(主动切纸,仅限切刀打印机使用才有效果)
		//"<LOGO>"为打印LOGO指令(前提是预先在机器内置LOGO图片),"<PLUGIN>"为钱箱或者外置音响指令
		//成对标签：
		//"<CB></CB>"为居中放大一倍,"<B></B>"为放大一倍,"<C></C>"为居中,<L></L>字体变高一倍
		//<W></W>字体变宽一倍,"<QR></QR>"为二维码,"<BOLD></BOLD>"为字体加粗,"<RIGHT></RIGHT>"为右对齐
	    //拼凑订单内容时可参考如下格式
		//根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式

		/**
		$orderInfo = '<CB>测试打印</CB><BR>';
		$orderInfo .= '名称　　　　　 单价  数量 金额<BR>';
		$orderInfo .= '--------------------------------<BR>';
		$orderInfo .= '饭　　　　　 　10.0   10  10.0<BR>';
		$orderInfo .= '炒饭　　　　　 10.0   10  10.0<BR>';
		$orderInfo .= '蛋炒饭　　　　 10.0   100 100.0<BR>';
		$orderInfo .= '鸡蛋炒饭　　　 100.0  100 100.0<BR>';
		$orderInfo .= '西红柿炒饭　　 1000.0 1   100.0<BR>';
		$orderInfo .= '西红柿蛋炒饭　 100.0  100 100.0<BR>';
		$orderInfo .= '西红柿鸡蛋炒饭西红柿鸡蛋炒饭西';
		$orderInfo .= '备注：加辣<BR>';
		$orderInfo .= '--------------------------------<BR>';
		$orderInfo .= '合计：xx.0元<BR>';
		$orderInfo .= '送货地点：广州市南沙区xx路xx号<BR>';
		$orderInfo .= '联系电话：13888888888888<BR>';
		$orderInfo .= '订餐时间：2014-08-08 08:08:08<BR>';
		$orderInfo .= '<QR>http://www.dzist.com</QR>';//把二维码字符串用标签套上即可自动生成二维码
		**/

		//打开注释可测试
		//wp_print(SN,$orderInfo,1);

		/*
		 *  方法1
			拼凑订单内容时可参考如下格式
			根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
		*/
		//旧版飞鹅打印机
		private function wp_print($orderInfo,$times=1 ,$printer_sn){
			//printer_sn
			$info = $this->_info();

			$content = array(
				'user'=>$info['user'],
				'stime'=>$info['stime'],
				'sig'=>$info['sig'],
				'apiname'=>'Open_printMsg',

				'sn'=>$printer_sn,
				'content'=>$orderInfo,
				'times'=>$times//打印次数
			);
			$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
			require_once $lib_path."/Weixin/Httpclient.class.php";
			$client = new \Httpclient($info['ip'],$info['port']);


			if(!$client->post($info['path'],$content)){

				return false;
			}
			else{
				//服务器返回的JSON字符串，建议要当做日志记录起来
				$pr_result = json_decode($client->getContent(), true);

				if( $pr_result['ret'] == 0 )
				{
					return  array( 'code' => 1,'msg' =>'' );
				}else{
					return  array( 'code' => 0,'msg' =>$pr_result['msg'] );
				}
			}
		}
		//标签说明：
		//单标签:
		//"<BR>"为换行,"<CUT>"为切刀指令(主动切纸,仅限切刀打印机使用才有效果)
		//"<LOGO>"为打印LOGO指令(前提是预先在机器内置LOGO图片),"<PLUGIN>"为钱箱或者外置音响指令
		//成对标签：
		//"<CB></CB>"为居中放大一倍,"<B></B>"为放大一倍,"<C></C>"为居中,<L></L>字体变高一倍
		//<W></W>字体变宽一倍,"<QR></QR>"为二维码,"<BOLD></BOLD>"为字体加粗,"<RIGHT></RIGHT>"为右对齐
	    //拼凑订单内容时可参考如下格式
		//根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式

			//获取字符串里的中文字数

		//-----------------begin---------

		/**
			添加易联云打印机
		**/
		public function addyilianyunprinter($yilian_client_id,$yilian_client_key,$yilian_machine_code, $yilian_msign)
		{
			$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
			require_once $lib_path."/Yilianyun/Lib/Autoloader.php";


			//授权打印机(自有型应用使用,开放型应用请跳过该步骤) $_W['uniacid']
			$token = $this->_get_yilian_access_token($yilian_client_id,$yilian_client_key);


			$config = new YlyConfig($yilian_client_id, $yilian_client_key);

			$printer = new PrinterService($token['access_token'], $config);

			$data = $printer->addPrinter($yilian_machine_code, $yilian_msign);


			return $data->error;
		}

		/**
			获取易联云access_token
		**/
		private function _get_yilian_access_token($yilian_client_id,$yilian_client_key)
		{
			$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
			require_once $lib_path."/Yilianyun/Lib/Autoloader.php";




			$config = new YlyConfig($yilian_client_id, $yilian_client_key);


			$token = D('Home/Front')->get_config_by_name('token_yilian_'.$yilian_client_id);

			//$yilian_client_id = D('Home/Front')->get_config_by_name('yilian_client_id');


			$client = new YlyOauthClient($config);


			if( empty($token) )
			{
				$new_token = $client->getToken();   //若是开放型应用请传授权码code

				$save_token = array();
				$save_token['access_token'] = $new_token->access_token;
				$save_token['refresh_token'] = $new_token->refresh_token;
				$save_token['machine_code'] = $new_token->machine_code;
				$save_token['expires_in'] = $new_token->expires_in;
				$save_token['scope'] = $new_token->scope;
				$save_token['expires_end'] = time() + $new_token->expires_in -86400;



				$cd_key = 'token_yilian_'.$yilian_client_id;
				D('Seller/Config')->update( array( $cd_key => serialize($save_token) ) );


				return $save_token;
			}else{
				$save_token = unserialize($token['value']);

				if( empty($save_token) )
				{
					$save_token = unserialize($token);
				}

				if($save_token['expires_end'] < time()  && false)
				{

					$save_token = $this->_relush_access_token($yilian_client_id,$yilian_client_key);


				}
				return $save_token;
			}
		}


		private function _relush_access_token($yilian_client_id,$yilian_client_key)
		{
			$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
			require_once $lib_path."/Yilianyun/Lib/Autoloader.php";


			$config = new YlyConfig($yilian_client_id, $yilian_client_key);


			$token_info = D('Home/Front')->get_config_by_name('token_yilian_'.$yilian_client_id);

			$token = unserialize($token_info);

			$client = new YlyOauthClient($config);
			//refresh_token


			$new_token = $client->refreshToken($token['refresh_token']);


			$save_token = array();
			$save_token['access_token'] = $new_token->access_token;
			$save_token['refresh_token'] = $new_token->refresh_token;
			$save_token['machine_code'] = $new_token->machine_code;
			$save_token['expires_in'] = $new_token->expires_in;
			$save_token['scope'] = $new_token->scope;
			$save_token['expires_end'] = time() + $new_token->expires_in -86400;


			$cd_key = 'token_yilian_'.$yilian_client_id;
			D('Seller/Config')->update( array( $cd_key => serialize($save_token) ) );


			return $save_token;
		}

		//-----------------end-----------


		//begin---------


		public function print_supply_order($order_id, $supply_goods_info,$title = '在线支付')
		{

			$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();


			$placeorder_trans_name = D('Home/Front')->get_config_by_name('placeorder_trans_name');//快递费
			if( !isset($placeorder_trans_name) || empty($placeorder_trans_name) )
			{
				$placeorder_trans_name = '快递费';
			}

			$placeorder_tuan_name = D('Home/Front')->get_config_by_name('placeorder_tuan_name');//配送费

			if( !isset($placeorder_tuan_name) || empty($placeorder_tuan_name) )
			{
				$placeorder_tuan_name = '配送费';
			}

			//打印隐藏客户手机号
			$is_printhide_membermobile = D('Home/Front')->get_config_by_name('is_printhide_membermobile');

			if( isset($is_printhide_membermobile) && $is_printhide_membermobile == 1 )
			{
				//隐藏
				$order_info['shipping_tel'] = substr($order_info['shipping_tel'],0,3).'*****'.substr($order_info['shipping_tel'],-3,3);
			}


			$owner_name =  D('Home/Front')->get_config_by_name('owner_name');

			if( empty($owner_name) )
			{
				$owner_name = '团长';
			}


			$delivery_tuanzshipping_name = D('Home/Front')->get_config_by_name('delivery_tuanzshipping_name');//团长配送

			if( empty($delivery_tuanzshipping_name) )
			{
				$delivery_tuanzshipping_name = '';
			}

			$delivery_express_name = D('Home/Front')->get_config_by_name('delivery_express_name');//快递配送

			if( empty($delivery_express_name) )
			{
				$delivery_express_name = '';
			}

			$delivery_ziti_name = D('Home/Front')->get_config_by_name('delivery_ziti_name');//到点自提

			if(  empty($delivery_ziti_name) )
			{
				$delivery_ziti_name = '';
			}



			$shoname = D('Home/Front')->get_config_by_name('shoname');


			foreach( $supply_goods_info as $supply_id => $order_goods )
			{
				$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print'.$supply_id);

				$feier_print_sn = D('Home/Front')->get_config_by_name('feier_print_sn'.$supply_id);


				$last_print_time  = D('Home/Front')->get_config_by_name('last_print_time'.$supply_id);
				$last_print_index = D('Home/Front')->get_config_by_name('last_print_index'.$supply_id);

				$now_time = strtotime( date('Y-m-d').' 00:00:00' );

				if( empty($last_print_time) || $last_print_time < $now_time )
				{
					$last_print_index = 1;
					$last_print_time = time();

					$sup_key = 'last_print_index'.$supply_id;
					$sup_key2 = 'last_print_time'.$supply_id;

					D('Seller/Config')->update( array( $sup_key => $last_print_index, $sup_key2 => $last_print_time) );
				}else if($last_print_time > $now_time) {
					$last_print_index = empty($last_print_index) ? 1: $last_print_index+1;

					$sup_key = 'last_print_index'.$supply_id;
					$sup_key2 = 'last_print_time'.$supply_id;

					D('Seller/Config')->update( array( $sup_key => $last_print_index, $sup_key2 => time() ) );
				}


				if( !empty($open_feier_print) && $open_feier_print == 2)
				{
					//易联云
					$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
					require_once $lib_path."/Yilianyun/Lib/Autoloader.php";

					$yilian_client_id = D('Home/Front')->get_config_by_name('yilian_client_id'.$supply_id);
					$yilian_client_key = D('Home/Front')->get_config_by_name('yilian_client_key'.$supply_id);

					$config = new YlyConfig($yilian_client_id, $yilian_client_key);

					$token = $this->_get_yilian_access_token($yilian_client_id,$yilian_client_key);

					$yilian_machine_code = D('Home/Front')->get_config_by_name('yilian_machine_code'.$supply_id);

					$print = new PrintService($token['access_token'], $config);

					$yilian_print_lian = D('Home/Front')->get_config_by_name('yilian_print_lian'.$supply_id);


					if( empty($yilian_print_lian) ||  $yilian_print_lian < 1)
					{
						$yilian_print_lian = 1;
					}

					$orderInfo = '<MN>'.$yilian_print_lian.'</MN>';
					$total_length = 32;

					$pay_time = date('Y-m-d H:i', $order_info['pay_time']);
					//printer_sn $content = "<FS2><center>**#1 美团**</center></FS2>";


					$orderInfo = '<FS2><center>--#'.$last_print_index.'#'.$title.'--</center></FS2>';
					$orderInfo .= '<FS2><center>'.$shoname.'</center></FS2>';
					$orderInfo .= '订单时间:'.$pay_time."\n";
					if( in_array($title, array('用户取消订单','后台操作取消订单','群接龙后台取消订单') ) )
					{
						$refund_time = date('Y-m-d H:i:s', time() );
						$orderInfo .= '取消时间:'.$refund_time."\n";
					}
					$orderInfo .= '订单编号:'.$order_info['order_num_alias']."\n";
					//head_id order_id
                    if( $order_info['payment_code'] == 'cashon_delivery'){
                        $orderInfo .= '支付方式:货到付款'."\n";
                    }

					$head_relative_line = M('eaterplanet_ecommerce_deliveryline_headrelative')->where( array('head_id' => $order_info['head_id'] ) )->find();

					if( !empty($head_relative_line) )
					{
						$line_id = $head_relative_line['line_id'];

						$line_info = M('eaterplanet_ecommerce_deliveryline')->where( array('id' => $line_id ) )->find();

						$orderInfo .= '线路名称:'.$line_info['name']."\n";
					}
					if($order_info['expected_delivery_time']){
						$orderInfo .= '--------------------------------'."\n";
						$orderInfo .= '配送时间段:'.$order_info['expected_delivery_time']."\n";
						$orderInfo .= '--------------------------------'."\n";
					}

					$head_info = M('eaterplanet_community_head')->where( array('id' => $order_info['head_id'] ) )->find();

					$orderInfo .= '收货小区:'.$head_info['community_name']."\n";
					$orderInfo .= $owner_name.'姓名:'.$head_info['head_name']."\n";
					$orderInfo .= $owner_name.'手机:'.$head_info['head_mobile']."\n";
					$member =  M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();
					if($member['card_id'] > 0 && $member['card_end_time'] >time() ){
						$orderInfo .= '<FS>姓   名:'.$order_info['shipping_name'].'(付费VIP)</FS>'."\n";
					}else{
						$orderInfo .= '<FS>姓   名:'.$order_info['shipping_name'].'</FS>'."\n";
					}
					$orderInfo .= '<FS>电   话:'.$order_info['shipping_tel'].'</FS>'."\n";



					//delivery   pickup  tuanz_send
					if( $order_info['delivery'] == 'pickup' )
					{
						if( $order_info['type'] == 'virtual' )
					    {
					        $orderInfo .= '收货地址:'.$order_info['shipping_address']."\n";
					        $orderInfo .= '配送方式:门店核销'."\n";//团长配送

					    }else{
					        $orderInfo .= '收货地址:'.$order_info['shipping_address']."\n";

							if( !empty($delivery_ziti_name) )
							{
								$orderInfo .= '配送方式:'.$delivery_ziti_name."\n";//团长配送
							}else{
								$orderInfo .= '配送方式:团员自提'."\n";//团长配送
							}
					    }



					}else if( $order_info['delivery'] == 'tuanz_send'){
						// address_id

						if($order_info['address_id'] > 0)
						{
							$ad_info = M('eaterplanet_ecommerce_address')->field('lou_meng_hao')->where( array('address_id' => $order_info['address_id'] ) )->find();

							if( !empty($ad_info) )
							{
								//$order_info['tuan_send_address'] .= $ad_info['lou_meng_hao'];
							}
						}

						$orderInfo .= '送货地址:'.$order_info['tuan_send_address']."\n";

						if( !empty($delivery_tuanzshipping_name) )
						{
							$orderInfo .= '配送方式:'.$delivery_tuanzshipping_name.''."\n";//团长配送
						}else{
							$orderInfo .= '配送方式:'.$owner_name.'送货上门'."\n";//团长配送
						}

					}else{

						$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
						$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
						$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);

						$sp_address = $province_info['name'].$city_info['name'].$area_info['name'];

						$orderInfo .= '收货地址:'.$sp_address.$order_info['shipping_address']."\n";


						if( !empty($delivery_express_name) )
						{
							$orderInfo .= '配送方式:'.$delivery_express_name."\n";
						}else{
							$orderInfo .= '配送方式:快递'."\n";
						}

					}


					$orderInfo .= '-------------商品---------------'."\n";
					$orderInfo .= '商品名称　　　　数量　      金额'."\n";

					$demo_str = '商品名称　　　　数量　      金额'."\n";


					$total_count = 0;

					$shipping_fare = $order_info['shipping_fare'];
					$man_e_money = $order_info['man_e_money'];
					$fare_shipping_free = $order_info['fare_shipping_free'];
					$is_free_shipping_fare = $order_info['is_free_shipping_fare'];

					$fullreduction_money = 0;
					$voucher_credit = 0;
					$comment = '';
					$total_money = 0;
					$score_for_money = 0;

					foreach($order_goods as $val )
					{
						$fullreduction_money += $val['fullreduction_money'];
						$voucher_credit += $val['voucher_credit'];
						$comment .= $val['comment'];
						$total_money += $val['total'];
						$score_for_money += $val['score_for_money'];

						$name = $val['name'];
						$total = $val['total'];
						$quantity = $val['quantity'];

						$goods_id = $val['goods_id'];

						$goods_common = M('eaterplanet_ecommerce_good_common')->field('print_sub_title')->where( array('goods_id' => $goods_id ) )->find();

						$goods_name_str = "";
						if( !empty($goods_common['print_sub_title']) )
						{
							$goods_name_str = $goods_common['print_sub_title'].'　'.$val['option_sku'];
						}else{
							$goods_name_str = $name.'　'.$val['option_sku'];
						}


						$orderInfo .= $goods_name_str."\n";;


						$newStr = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $goods_name_last);  //正则匹配中文
						$zw_length = mb_strlen($newStr,"utf-8");  //得到中汉字个数

						//$zw_length = $this->linyufan_get_cn_num($goods_name_last);
						$tt_length = mb_strlen($goods_name_last,'utf-8') - $zw_length;

						//mb_strlen($goods_name_last,'utf-8') -

						$zhongjian =  18;
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						//$orderInfo .= $zhognjian_ge;
						$orderInfo .= "\t\t\t\t";


						$quantity_str = 'x'.$quantity;
						$total_str = sprintf('%.2f',$total);

						$orderInfo .= $quantity_str;
						$right_gezi = 14 - strlen($quantity_str) -  strlen(sprintf('%.2f',$total));



						for( $i =1;$i<=$right_gezi;$i++ )
						{
							$orderInfo .= ' ';
						}
						$orderInfo .= sprintf('%.2f',$total)."\n";

						$total_count += $quantity;
					}


					$orderInfo .= '--------------------------------'."\n";


					//var_dump( strlen($demo_str), mb_strlen($demo_str,'utf-8') );

					$zhongjian = 32 - 10 - strlen($total_count);
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '商品总数：'.$zhognjian_ge.$total_count."\n";

					if( !empty($fullreduction_money) && $fullreduction_money >0)
					{
						$zhongjian = 32 - 9 - strlen(sprintf('%.2f',$fullreduction_money));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}

						$orderInfo .= '满减：'.$zhognjian_ge.'-'.sprintf('%.2f',$fullreduction_money).'元'."\n";
					}

					if( !empty($score_for_money) && $score_for_money >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$score_for_money));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '积分抵：'.$zhognjian_ge.'-'.sprintf('%.2f',$score_for_money).'元'."\n";
					}


					if( !empty($voucher_credit) && $voucher_credit >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$voucher_credit));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '优惠券：'.$zhognjian_ge.'-'.sprintf('%.2f',$voucher_credit).'元'."\n";
					}


					/**
					if( !empty($shipping_fare) && $shipping_fare >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '运费：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元'."\n";
					}
					**/
					if($is_free_shipping_fare == 1 && $fare_shipping_free > 0)
					{
						if( !empty($fare_shipping_free) && $fare_shipping_free >0)
						{
							//满$man_e_money免运费    -7 man_e_money
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}

							if( $order_info['delivery'] == 'tuanz_send')
							{
								$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."\n";
							}else if( $order_info['delivery'] == 'express')
							{
								$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."\n";
							}


						}
						if( !empty($fare_shipping_free) && $fare_shipping_free >0)
						{
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}
							$man_e_money = floor($man_e_money * 100) / 100;
							$orderInfo .= '满'.$man_e_money.'免运费：'.$zhognjian_ge.'-'.sprintf('%.2f',$fare_shipping_free).'元'."\n";
							$shipping_fare = 0;
						}
					}else{
						if( !empty($shipping_fare) && $shipping_fare >0)
						{
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}

							if( $order_info['delivery'] == 'tuanz_send')
							{
								$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元'."\n";
							}else if( $order_info['delivery'] == 'express')
							{
								$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元'."\n";
							}

						}
					}





					$zhongjian = 32 - 10 - strlen(sprintf('%.2f',$total_money));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

					$order_type = $order_info['type'];
                    $orderInfo .= '总金额：'.$zhognjian_ge.sprintf('%.2f',$order_info['total']);
                    if ($order_type == "integral"){
                        $orderInfo .= "积分"."\n";
                    }else{
                        $orderInfo .= "元"."\n";
                    }
					$orderInfo .= '********************************'."\n";
					$real_price = $total_money+$shipping_fare-$voucher_credit-$fullreduction_money-$score_for_money;
					if($real_price < 0)
					{
						$real_price = 0;
					}
					$real_price = sprintf('%.2f',$real_price);

					$zhongjian = 32 - 12 - strlen($real_price);
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

					if ($order_type == "integral"){
    				    if($real_price- $order_info['total'] == 0){
    				        $orderInfo .= '实付金额：'.sprintf('%.2f',$order_info['total']).'积分'."\n";
    				    }else{
    				        $orderInfo .= '实付金额：'.$zhognjian_ge.($real_price- $order_info['total']).'元+'.sprintf('%.2f',$order_info['total']).'积分'."\n";
    				    }
                    }else{
                       $orderInfo .= '实付金额：'.$zhognjian_ge.$real_price.'元'."\n";
                    }
					//comment

					$orderInfo .= '--------------------------------'."\n";
					//order_info  <BR>

					if( !empty($comment) )
					{
						$orderInfo .= '备注：'.$comment.''."\n";
					}

					//是否打印客户备注：is_print_member_note

					//是否打印订单备注: is_print_order_note
					//begin pr
					$is_print_member_note = D('Home/Front')->get_config_by_name('is_print_member_note');
					if( isset($is_print_member_note) && $is_print_member_note == 1 )
					{
						$mb_info = M('eaterplanet_ecommerce_member')->field('content')->where( array('member_id' => $order_info['member_id'] ) )->find();

						if( !empty($mb_info['content']) )
						{
							$orderInfo .= '客户备注：'.$mb_info['content'].''."\n";
						}
					}

					$is_print_order_note = D('Home/Front')->get_config_by_name('is_print_order_note');
					$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');
					$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');
					if( !empty($is_print_order_note) && $is_print_order_note == 1 )
					{
						if( !empty($order_info['note_content']) )
						{
							if( !empty($order_note_open) &&  !empty($order_note_name))
							{
								$orderInfo .= $order_note_name.'：'.$order_info['note_content'].''."<BR>";
							}else{
								$orderInfo .= '自定义备注：'.$order_info['note_content'].''."<BR>";
							}
						}
					}
					//end pr
					if($order_info['order_status_id'] == 7){
						$orderInfo .= '--------------------------------<BR>';
						$orderInfo .= '<FS>订单已退款</<FS><BR>';

				    }
					$orderInfo .= '<FS2><center>**#'.$last_print_index.' 完**</center></FS2>';

					$data = $print->index($yilian_machine_code,$orderInfo,$order_id);

					///......待查看格式
				}

				if( !empty($open_feier_print) && $open_feier_print == 1)
				{
					//飞蛾
					$total_length = 32;

					$pay_time = date('Y-m-d H:i', $order_info['pay_time']);
					//printer_sn
					$orderInfo = '<CB>--#'.$last_print_index.'#'.$title.'--</CB><BR>';
					$orderInfo .= '<C><L>'.$shoname.'</L></C><BR>';
					$orderInfo .= '订单时间:'.$pay_time.'<BR>';

					if( in_array($title, array('用户取消订单','后台操作取消订单','群接龙后台取消订单') ) )
					{
						$refund_time = date('Y-m-d H:i:s', time() );
						$orderInfo .= '取消时间:'.$refund_time."<BR>";
					}

					$orderInfo .= '订单编号:'.$order_info['order_num_alias'].'<BR>';
                    if( $order_info['payment_code'] == 'cashon_delivery'){
                        $orderInfo .= '支付方式:货到付款'.'<BR>';
                    }

					$head_relative_line = M('eaterplanet_ecommerce_deliveryline_headrelative')->where( array('head_id' =>$order_info['head_id'] ) )->find();

					if( !empty($head_relative_line) )
					{
						$line_id = $head_relative_line['line_id'];

						$line_info = M('eaterplanet_ecommerce_deliveryline')->where( array('id' => $line_id ) )->find();

						$orderInfo .= '线路名称:'.$line_info['name'].'<BR>';
					}
					if($order_info['expected_delivery_time']){
						$orderInfo .= '--------------------------------<BR>';
						$orderInfo .= '配送时间段:'.$order_info['expected_delivery_time'].'<BR>';
						$orderInfo .= '--------------------------------<BR>';
					}
					$head_info = M('eaterplanet_community_head')->where( array('id' => $order_info['head_id'] ) )->find();


					$orderInfo .= '收货小区:'.$head_info['community_name'].'<BR>';
					$orderInfo .= $owner_name.'姓名:'.$head_info['head_name'].'<BR>';
					$orderInfo .= $owner_name.'手机:'.$head_info['head_mobile'].'<BR>';
					$member =  M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();
					if($member['card_id'] > 0 && $member['card_end_time'] >time() ){
						$orderInfo .= '<L>姓   名:'.$order_info['shipping_name'].'(付费VIP)</L><BR>';
					}else{
						$orderInfo .= '<L>姓   名:'.$order_info['shipping_name'].'</L><BR>';
					}
					$orderInfo .= '<L>电   话:'.$order_info['shipping_tel'].'</L><BR>';


					if( $order_info['delivery'] == 'pickup' )
					{
						if( $order_info['type'] == 'virtual' )
					    {
					        $orderInfo .= '收货地址:'.$order_info['shipping_address'].'<BR>';
					        $orderInfo .= '配送方式:门店核销<BR>';//团长配送
					    }else{
					       $orderInfo .= '收货地址:'.$order_info['shipping_address'].'<BR>';

							if( !empty($delivery_ziti_name) )
							{
								$orderInfo .= '配送方式:'.$delivery_ziti_name.'<BR>';//团长配送
							}else{
								$orderInfo .= '配送方式:团员自提<BR>';//团长配送
							}
					    }

					}else if( $order_info['delivery'] == 'tuanz_send'){

						if($order_info['address_id'] > 0)
						{
							$ad_info = M('eaterplanet_ecommerce_address')->field('lou_meng_hao')->where( array('address_id' => $order_info['address_id'] ) )->find();

							if( !empty($ad_info) )
							{
								//$order_info['tuan_send_address'] .= $ad_info['lou_meng_hao'];
							}
						}

						$orderInfo .= '送货地址:'.$order_info['tuan_send_address'].'<BR>';

						if( !empty($delivery_tuanzshipping_name) )
						{
							$orderInfo .= '配送方式:'.$delivery_tuanzshipping_name.'<BR>';//团长配送
						}else{
							$orderInfo .= '配送方式:'.$owner_name.'送货上门<BR>';//团长配送
						}

					}else{

						$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
						$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
						$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);

						$sp_address = $province_info['name'].$city_info['name'].$area_info['name'];

						$orderInfo .= '收货地址:'.$sp_address.$order_info['shipping_address']."<BR>";

						if( !empty($delivery_express_name) )
						{
							$orderInfo .= '配送方式:'.$delivery_express_name.'<BR>';
						}else{
							$orderInfo .= '配送方式:快递<BR>';
						}
					}

					$orderInfo .= '-------------商品---------------<BR>';
					$orderInfo .= '商品名称　　　　数量　      金额<BR>';

					$demo_str = '商品名称　　　　数量　      金额';


					$total_count = 0;
					$shipping_fare = $order_info['shipping_fare'];

					$man_e_money = $order_info['man_e_money'];
					$fare_shipping_free = $order_info['fare_shipping_free'];

					$fullreduction_money = 0;
					$voucher_credit = 0;
					$comment = '';
					$total_money = 0;
					$score_for_money = 0;

					foreach($order_goods as $val )
					{
						$fullreduction_money += $val['fullreduction_money'];
						$voucher_credit += $val['voucher_credit'];
						$score_for_money += $val['score_for_money'];
						$comment .= $val['comment'];
						$total_money += $val['total'];



						$name = $val['name'];
						$total = $val['total'];
						$quantity = $val['quantity'];

						$goods_id = $val['goods_id'];

						$goods_common = M('eaterplanet_ecommerce_good_common')->field('print_sub_title')->where( array('goods_id' => $goods_id) )->find();

						$goods_name_str = "";
						if( !empty($goods_common['print_sub_title']) )
						{
							$goods_name_str = $goods_common['print_sub_title'].'　'.$val['option_sku'];
						}else{
							$goods_name_str = $name.'　'.$val['option_sku'];
						}

						//17
						//$goods_name_last =  mb_substr($goods_name_str,0,7,'utf-8');
						//$orderInfo .= $goods_name_last;

						$orderInfo .= $goods_name_str.'<BR>';


						$newStr = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $goods_name_last);  //正则匹配中文
						$zw_length = mb_strlen($newStr,"utf-8");  //得到中汉字个数

						//$zw_length = $this->linyufan_get_cn_num($goods_name_last);
						$tt_length = mb_strlen($goods_name_last,'utf-8') - $zw_length;

						//mb_strlen($goods_name_last,'utf-8') -

						$zhongjian =  18;
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= $zhognjian_ge;


						$quantity_str = 'x'.$quantity;
						$total_str = sprintf('%.2f',$total);

						$orderInfo .= $quantity_str;
						$right_gezi = 14 - strlen($quantity_str) -  strlen(sprintf('%.2f',$total));



						for( $i =1;$i<=$right_gezi;$i++ )
						{
							$orderInfo .= ' ';
						}
						$orderInfo .= sprintf('%.2f',$total).'<BR>';

						$total_count += $quantity;
					}


					$orderInfo .= '--------------------------------<BR>';

					$zhongjian = 32 - 10 - strlen($total_count);
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '商品总数：'.$zhognjian_ge.$total_count.'<BR>';

					if( !empty($fullreduction_money) && $fullreduction_money >0)
					{
						$zhongjian = 32 - 9 - strlen(sprintf('%.2f',$fullreduction_money));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}

						$orderInfo .= '满减：'.$zhognjian_ge.'-'.sprintf('%.2f',$fullreduction_money).'元<BR>';
					}

					if( !empty($score_for_money) && $score_for_money >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$score_for_money));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '积分抵：'.$zhognjian_ge.'-'.sprintf('%.2f',$score_for_money).'元'."<BR>";
					}


					if( !empty($voucher_credit) && $voucher_credit >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$voucher_credit));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '优惠券：'.$zhognjian_ge.'-'.sprintf('%.2f',$voucher_credit).'元<BR>';
					}


					/**
					if( !empty($shipping_fare) && $shipping_fare >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '配送费：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元<BR>';
					}
					**/
					if($is_free_shipping_fare == 1 && $fare_shipping_free > 0)
					{
						if( !empty($fare_shipping_free) && $fare_shipping_free >0)
						{
							//满$man_e_money免运费    -7 man_e_money
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}

							if( $order_info['delivery'] == 'tuanz_send')
							{
								$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."<BR>";
							}else if( $order_info['delivery'] == 'express')
							{
								$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."<BR>";
							}
						}
						if( !empty($fare_shipping_free) && $fare_shipping_free >0)
						{
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}
							$man_e_money = floor($man_e_money * 100) / 100;
							$orderInfo .= '满'.$man_e_money.'免运费：'.$zhognjian_ge.'-'.sprintf('%.2f',$fare_shipping_free).'元'."<BR>";
							$shipping_fare = 0;
						}
					}else{
						if( !empty($shipping_fare) && $shipping_fare >0)
						{
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}

							if( $order_info['delivery'] == 'tuanz_send')
							{
								$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元<BR>';
							}else if( $order_info['delivery'] == 'express')
							{
								$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元<BR>';
							}

						}
					}


					$zhongjian = 32 - 10 - strlen(sprintf('%.2f',$total_money));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

					$order_type = $order_info['type'];
                    $orderInfo .= '总金额：'.$zhognjian_ge.sprintf('%.2f',$order_info['total']);
                    if ($order_type == "integral"){
                        $orderInfo .= "积分"."<BR>";
                    }else{
                        $orderInfo .= "元"."<BR>";
                    }
					$orderInfo .= '********************************<BR>';
					$real_price = $total_money + $shipping_fare -$voucher_credit-$fullreduction_money-$score_for_money;
					if($real_price < 0)
					{
						$real_price = 0;
					}
					$real_price = sprintf('%.2f',$real_price);

					$zhongjian = 32 - 12 - strlen($real_price);
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

					if ($order_type == "integral"){
       				    if($real_price- $order_info['total'] == 0){
    				        $orderInfo .= '实付金额：'.sprintf('%.2f',$order_info['total']).'积分<BR>';
    				    }else{
    				        $orderInfo .= '实付金额：'.$zhognjian_ge.($real_price- $order_info['total']).'元+'.sprintf('%.2f',$order_info['total']).'积分<BR>';
    				    }
                    }else{
                       $orderInfo .= '实付金额：'.$zhognjian_ge.$real_price.'元<BR>';
                    }

					//comment

					$orderInfo .= '--------------------------------<BR>';
					//order_info  <BR>

					if( !empty($comment) )
					{
						$orderInfo .= '备注：'.$comment.'<BR>';
					}


					//begin pr
					$is_print_member_note = D('Home/Front')->get_config_by_name('is_print_member_note');
					if( isset($is_print_member_note) && $is_print_member_note == 1 )
					{
						$mb_info = M('eaterplanet_ecommerce_member')->field('content')->where( array('member_id' => $order_info['member_id'] ) )->find();

						if( !empty($mb_info['content']) )
						{
							$orderInfo .= '客户备注：'.$mb_info['content'].''."<BR>";
						}
					}

					$is_print_order_note = D('Home/Front')->get_config_by_name('is_print_order_note');
					$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');
					$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');
					if( !empty($is_print_order_note) && $is_print_order_note == 1 )
					{
						if( !empty($order_info['note_content']) )
						{
							if( !empty($order_note_open) &&  !empty($order_note_name))
							{
								$orderInfo .= $order_note_name.'：'.$order_info['note_content'].''."<BR>";
							}else{
								$orderInfo .= '自定义备注：'.$order_info['note_content'].''."<BR>";
							}
						}
					}
					//end pr
					if($order_info['order_status_id'] == 7){
						$orderInfo .= '--------------------------------<BR>';
						$orderInfo .= '<L>订单已退款</<L><BR>';

					}
					$orderInfo .= '<CB>**#'.$last_print_index.'  完**</CB><BR>';

					//$orderInfo .= '<QR>http://www.dzist.com</QR>';//把二维码字符串用标签套上即可自动生成二维码

					//feier_print_lian
					$feier_print_lian = D('Home/Front')->get_config_by_name('feier_print_lian'.$supply_id);

					if( empty($feier_print_lian) ||  $feier_print_lian < 1)
					{
						$feier_print_lian = 1;
					}

					$this->wp_print($orderInfo, $feier_print_lian, $feier_print_sn);

				}


			}

		}

		//end

	    //打印小票
		public function check_print_order($order_id,$title='在线支付')
		{

			$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

			$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->select();

			foreach($order_goods as &$value)
			{
				$value['option_sku'] = D('Seller/Order')->get_order_option_sku($order_id, $value['order_goods_id']);
			}

			$owner_name = D('Home/Front')->get_config_by_name('owner_name');

			if( empty($owner_name) )
			{
				$owner_name = '团长';
			}





			$delivery_tuanzshipping_name = D('Home/Front')->get_config_by_name('delivery_tuanzshipping_name');//团长配送

			if( empty($delivery_tuanzshipping_name) )
			{
				$delivery_tuanzshipping_name = '';
			}

			$delivery_express_name = D('Home/Front')->get_config_by_name('delivery_express_name');//快递配送

			if( empty($delivery_express_name) )
			{
				$delivery_express_name = '';
			}

			$delivery_ziti_name = D('Home/Front')->get_config_by_name('delivery_ziti_name');//到点自提

			if(  empty($delivery_ziti_name) )
			{
				$delivery_ziti_name = '';
			}


			$placeorder_trans_name = D('Home/Front')->get_config_by_name('placeorder_trans_name');//快递费

			if( !isset($placeorder_trans_name) || empty($placeorder_trans_name) )
			{
				$placeorder_trans_name = '快递费';
			}


			$placeorder_tuan_name = D('Home/Front')->get_config_by_name('placeorder_tuan_name');//配送费

			if( !isset($placeorder_tuan_name) || empty($placeorder_tuan_name) )
			{
				$placeorder_tuan_name = '配送费';
			}

			/***
				商户订单商品集合
			**/
			$supply_goods_info = array();

			/***
				商户订单商品集合
			**/
			$is_print_dansupply_order = D('Home/Front')->get_config_by_name('is_print_dansupply_order');

			if( isset($is_print_dansupply_order) && $is_print_dansupply_order == 1 )
			{
				$is_print_dansupply_order = 1;
			}else if( !isset($is_print_dansupply_order) || $is_print_dansupply_order == 0 )
			{
				$is_print_dansupply_order = 0;
			}

			//打印隐藏客户手机号
			$is_printhide_membermobile = D('Home/Front')->get_config_by_name('is_printhide_membermobile');

			if( isset($is_printhide_membermobile) && $is_printhide_membermobile == 1 )
			{
				//隐藏
				$order_info['shipping_tel'] = substr($order_info['shipping_tel'],0,3).'*****'.substr($order_info['shipping_tel'],-3,3);
			}

			$is_print = true;

			foreach($order_goods as &$value)
			{
				$value['option_sku'] = D('Seller/Order')->get_order_option_sku($value['order_id'], $value['order_goods_id']);

				if( $value['supply_id'] > 0 )
				{
					if( isset($supply_goods_info[ $value['supply_id'] ]) )
					{
						$supply_goods_info[ $value['supply_id'] ][] = $value;
					}else{
						$supply_goods_info[ $value['supply_id'] ] = array();
						$supply_goods_info[ $value['supply_id'] ][] = $value;
					}
				}
			}

			$shoname = D('Home/Front')->get_config_by_name('shoname');

			$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print');

			//打印联数
			$feier_print_sn = D('Home/Front')->get_config_by_name('feier_print_sn');

			$last_print_time  = D('Home/Front')->get_config_by_name('last_print_time');
			$last_print_index = D('Home/Front')->get_config_by_name('last_print_index');

			$now_time = strtotime( date('Y-m-d').' 00:00:00' );

			if( empty($last_print_time) || $last_print_time < $now_time )
			{
				$last_print_index = 1;
				$last_print_time = time();

				D('Seller/Config')->update( array('last_print_index' => $last_print_index, 'last_print_time' => $last_print_time) );
			}else if($last_print_time > $now_time) {
				$last_print_index = empty($last_print_index) ? 1: $last_print_index+1;

				D('Seller/Config')->update( array('last_print_index' => $last_print_index, 'last_print_time' => time() ) );
			}

			//继续使用原有的打印机设置，旧版打印机与“默认订单打印机”一起打印小票，也可以关闭旧版打印机设置使用新版本“默认打印机”
			//默认打印机的参数
			$data = D('Seller/Config')->get_all_config();
			if(isset($data['is_printer_list']) && !empty($data['is_printer_list'])){
				$printer_list = M('eaterplanet_ecommerce_printer')->where( array('id' => array('in',$data['is_printer_list']) ) )->select();
			}


				//旧版
				//易联云
				if( !empty($open_feier_print) && $open_feier_print == 2)
				{
					$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
					require_once $lib_path."/Yilianyun/Lib/Autoloader.php";


					$yilian_client_id = D('Home/Front')->get_config_by_name('yilian_client_id');
					$yilian_client_key = D('Home/Front')->get_config_by_name('yilian_client_key' );

					$config = new YlyConfig($yilian_client_id, $yilian_client_key);

					$token = $this->_get_yilian_access_token($yilian_client_id,$yilian_client_key);

					$yilian_machine_code = D('Home/Front')->get_config_by_name('yilian_machine_code' );

					$print = new PrintService($token['access_token'], $config);

					//object(stdClass)#14 (3) { ["error"]=> string(1) "0" ["error_description"]=> string(7) "success" ["body"]=> object(stdClass)#15 (2) { ["id"]=> string(9) "221354299" ["origin_id"]=> string(3) "558" } }

					//<MN>1</MN>
					//$data = $print->index($yilian_machine_code,'打印内容排版可看Demo下的callback.php','558');
					//-------------------------------------------------------------------------------------------
					$yilian_print_lian = D('Home/Front')->get_config_by_name('yilian_print_lian');

					if( empty($yilian_print_lian) ||  $yilian_print_lian < 1)
					{
						$yilian_print_lian = 1;
					}

					$orderInfo = '<MN>'.$yilian_print_lian.'</MN>';
					$total_length = 32;

					$pay_time = date('Y-m-d H:i', $order_info['pay_time']);
					$orderInfo = '<FS2><center>--#'.$last_print_index.$title.'--</center></FS2>';
					$orderInfo .= '<FS2><center>'.$shoname.'</center></FS2>';
					$orderInfo .= '订单时间:'.$pay_time."\n";
					if( in_array($title, array('用户取消订单','后台操作取消订单','群接龙后台取消订单') ) )
					{
						$refund_time = date('Y-m-d H:i:s', time() );
						$orderInfo .= '取消时间:'.$refund_time."\n";
					}

					$orderInfo .= '订单编号:'.$order_info['order_num_alias']."\n";
                    if( $order_info['payment_code'] == 'cashon_delivery'){
                        $orderInfo .= '支付方式:货到付款'."\n";
                    }
					//head_id order_id


					$head_relative_line = M('eaterplanet_ecommerce_deliveryline_headrelative')->where( array('head_id' => $order_info['head_id'] ) )->find();


					if( !empty($head_relative_line) )
					{
						$line_id = $head_relative_line['line_id'];

						$line_info = M('eaterplanet_ecommerce_deliveryline')->where( array('id' => $line_id ) )->find();

						$orderInfo .= '线路名称:'.$line_info['name']."\n";
					}
					if($order_info['expected_delivery_time']){
						$orderInfo .= '--------------------------------'."\n";
						$orderInfo .= '配送时间段:'.$order_info['expected_delivery_time']."\n";
						$orderInfo .= '--------------------------------'."\n";
					}
					$head_info = M('eaterplanet_community_head')->where( array('id' => $order_info['head_id'] ) )->find();

					$orderInfo .= '收货小区:'.$head_info['community_name']."\n";
					$orderInfo .= $owner_name.'姓名:'.$head_info['head_name']."\n";
					$orderInfo .= $owner_name.'手机:'.$head_info['head_mobile']."\n";
					$member =  M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();
					if($member['card_id'] > 0 && $member['card_end_time'] >time() ){
						$orderInfo .= '<FS>姓   名:'.$order_info['shipping_name'].'(付费VIP)</FS>'."\n";
					}else{
						$orderInfo .= '<FS>姓   名:'.$order_info['shipping_name'].'</FS>'."\n";
					}
					$orderInfo .= '<FS>电   话:'.$order_info['shipping_tel'].'</FS>'."\n";



					//delivery   pickup  tuanz_send
					if( $order_info['delivery'] == 'pickup' )
					{
						if( $order_info['type'] == 'virtual' )
						{
							$orderInfo .= '收货地址:'.$order_info['shipping_address']."\n";
							$orderInfo .= '配送方式:门店核销'."\n";//团长配送
						}else{
							$orderInfo .= '收货地址:'.$order_info['shipping_address']."\n";
							if( !empty($delivery_ziti_name) )
							{
								$orderInfo .= '配送方式:'.$delivery_ziti_name."\n";//团长配送
							}else{
								$orderInfo .= '配送方式:团员自提'."\n";//团长配送
							}
						}

					}else if( $order_info['delivery'] == 'tuanz_send'){
						// address_id

						if($order_info['address_id'] > 0)
						{

							$ad_info =  M('eaterplanet_ecommerce_address')->field('lou_meng_hao')->where( array('address_id' => $order_info['address_id'] ) )->find();

							if( !empty($ad_info) )
							{
								//$order_info['tuan_send_address'] .= $ad_info['lou_meng_hao'];
							}
						}

						$orderInfo .= '送货地址:'.$order_info['tuan_send_address']."\n";

						if( !empty($delivery_tuanzshipping_name) )
						{
							$orderInfo .= '配送方式:'.$delivery_tuanzshipping_name.''."\n";//团长配送
						}else{
							$orderInfo .= '配送方式:'.$owner_name.'送货上门'."\n";//团长配送
						}

					}else{
						$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
						$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
						$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);

						$sp_address = $province_info['name'].$city_info['name'].$area_info['name'];

						$orderInfo .= '收货地址:'.$sp_address.$order_info['shipping_address']."\n";
						if( $order_info['delivery'] == 'localtown_delivery'){
								$orderInfo .= '配送方式:同城配送'."\n";//同城配送
						}else if( $order_info['delivery'] == 'hexiao'){
							$orderInfo .= '<L>配送方式:到店核销</L><BR>';//到店核销
						}else{
							if( !empty($delivery_express_name) )
							{
								$orderInfo .= '配送方式:'.$delivery_express_name."\n";
							}else{
								$orderInfo .= '配送方式:快递'."\n";
							}
						}
					}


					$orderInfo .= '-------------商品---------------'."\n";
					$orderInfo .= '商品名称　　　　数量　      金额'."\n";

					$demo_str = '商品名称　　　　数量　      金额'."\n";


					$total_count = 0;

					foreach($order_goods as $val )
					{
						$name = $val['name'];
						$total = $val['total'];
						$quantity = $val['quantity'];

						$goods_id = $val['goods_id'];

						$goods_common = M('eaterplanet_ecommerce_good_common')->field('print_sub_title')->where( array('goods_id' => $goods_id ) )->find();

						$goods_name_str = "";
						if( !empty($goods_common['print_sub_title']) )
						{
							$goods_name_str = $goods_common['print_sub_title'].'　'.$val['option_sku'];
						}else{
							$goods_name_str = $name.'　'.$val['option_sku'];
						}

						//17

						$orderInfo .= $goods_name_str."\n";;


						$newStr = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $goods_name_last);  //正则匹配中文
						$zw_length = mb_strlen($newStr,"utf-8");  //得到中汉字个数

						//$zw_length = $this->linyufan_get_cn_num($goods_name_last);
						$tt_length = mb_strlen($goods_name_last,'utf-8') - $zw_length;

						//mb_strlen($goods_name_last,'utf-8') -

						$zhongjian =  18;
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						//$orderInfo .= $zhognjian_ge;
						$orderInfo .= "\t\t\t\t";


						$quantity_str = 'x'.$quantity;
						$total_str = sprintf('%.2f',$total);

						$orderInfo .= $quantity_str;
						$right_gezi = 14 - strlen($quantity_str) -  strlen(sprintf('%.2f',$total));



						for( $i =1;$i<=$right_gezi;$i++ )
						{
							$orderInfo .= ' ';
						}
						$orderInfo .= sprintf('%.2f',$total)."\n";

						$total_count += $quantity;
					}


					$orderInfo .= '--------------------------------'."\n";


					//var_dump( strlen($demo_str), mb_strlen($demo_str,'utf-8') );

					$zhongjian = 32 - 10 - strlen($total_count);
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '商品总数：'.$zhognjian_ge.$total_count."\n";

					if( !empty($order_info['fullreduction_money']) && $order_info['fullreduction_money'] >0)
					{
						$zhongjian = 32 - 9 - strlen(sprintf('%.2f',$order_info['fullreduction_money']));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}

						$orderInfo .= '满减：'.$zhognjian_ge.'-'.sprintf('%.2f',$order_info['fullreduction_money']).'元'."\n";
					}



					if( !empty($order_info['voucher_credit']) && $order_info['voucher_credit'] >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$order_info['voucher_credit']));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '优惠券：'.$zhognjian_ge.'-'.sprintf('%.2f',$order_info['voucher_credit']).'元'."\n";
					}

					$score_for_money = $order_info['score_for_money'];
					if( !empty($score_for_money) && $score_for_money >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$score_for_money));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '积分抵：'.$zhognjian_ge.'-'.sprintf('%.2f',$score_for_money).'元'."\n";
					}


					/**
					$shipping_fare = $order_info['shipping_fare'];
					if( !empty($shipping_fare) && $shipping_fare >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '配送费：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元'."\n";
					}
					**/

					$man_e_money = $order_info['man_e_money'];
					$fare_shipping_free = $order_info['fare_shipping_free'];
					$is_free_shipping_fare = $order_info['is_free_shipping_fare'];
					$shipping_fare = $order_info['shipping_fare'];

					if($is_free_shipping_fare == 1 && $fare_shipping_free > 0)
					{
						if( !empty($fare_shipping_free) && $fare_shipping_free >0)
						{
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}
							if( $order_info['delivery'] == 'tuanz_send')
							{
								$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."\n";
							}else if( $order_info['delivery'] == 'express')
							{
								$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."\n";
							}

						}
						if( !empty($fare_shipping_free) && $fare_shipping_free >0)
						{
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}
							$man_e_money = floor($man_e_money * 100) / 100;
							$orderInfo .= '满'.$man_e_money.'免运费：'.$zhognjian_ge.'-'.sprintf('%.2f',$fare_shipping_free).'元'."\n";
							$shipping_fare = 0;
						}
					}else{
						if( !empty($shipping_fare) && $shipping_fare >0)
						{
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}

							if( $order_info['delivery'] == 'tuanz_send')
							{
								$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元'."\n";
							}else if( $order_info['delivery'] == 'express')
							{
								$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元'."\n";
							}

						}
					}

					$zhongjian = 32 - 10 - strlen(sprintf('%.2f',$order_info['total']));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

//					$orderInfo .= '总金额：'.$zhognjian_ge.sprintf('%.2f',$order_info['total']).'元'."\n";

                    $order_type = $order_info['type'];
                    $orderInfo .= '总金额：'.$zhognjian_ge.sprintf('%.2f',$order_info['total']);
                    if ($order_type == "integral"){
                        $orderInfo .= "积分"."\n";
                    }else{
                        $orderInfo .= "元"."\n";
                    }

                    $orderInfo .= '********************************'."\n";
					if($shipping_fare == 0){
						$real_price = $order_info['total'] + $shipping_fare -$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']+$order_info['localtown_add_shipping_fare'];

					}else{
						$real_price = $order_info['total'] + $shipping_fare -$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']-$order_info['fare_shipping_free']+$order_info['localtown_add_shipping_fare'];

					}
					if($real_price < 0)
					{
						$real_price = 0;
					}
					$real_price = sprintf('%.2f',$real_price);

					$zhongjian = 32 - 12 - strlen($real_price);
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

					if ($order_type == "integral"){
    				    if($real_price- $order_info['total'] == 0){
    				        $orderInfo .= '实付金额：'.sprintf('%.2f',$order_info['total']).'积分'."\n";
    				    }else{
    				        $orderInfo .= '实付金额：'.$zhognjian_ge.($real_price- $order_info['total']).'元+'.sprintf('%.2f',$order_info['total']).'积分'."\n";
    				    }
                    }else{
                       $orderInfo .= '实付金额：'.$zhognjian_ge.$real_price.'元'."\n";
                    }
					//comment

					$orderInfo .= '--------------------------------'."\n";
					//order_info  <BR>

					if( !empty($order_info['comment']) )
					{
						$orderInfo .= '<FS>备注：'.$order_info['comment'].'</FS>'."\n";
					}

					//begin pr
					$is_print_member_note = D('Home/Front')->get_config_by_name('is_print_member_note');
					if( isset($is_print_member_note) && $is_print_member_note == 1 )
					{
						$mb_info = M('eaterplanet_ecommerce_member')->field('content')->where( array('member_id' => $order_info['member_id'] ) )->find();

						if( !empty($mb_info['content']) )
						{
							$orderInfo .= '客户备注：'.$mb_info['content'].''."\n";
						}
					}

					$is_print_order_note = D('Home/Front')->get_config_by_name('is_print_order_note');
					$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');
					$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');
					if( !empty($is_print_order_note) && $is_print_order_note == 1 )
					{
						if( !empty($order_info['note_content']) )
						{
							if( !empty($order_note_open) &&  !empty($order_note_name))
							{
								$orderInfo .= $order_note_name.'：'.$order_info['note_content'].''."<BR>";
							}else{
								$orderInfo .= '自定义备注：'.$order_info['note_content'].''."<BR>";
							}
						}
					}
					//end pr
					if($order_info['order_status_id'] == 7){
						$orderInfo .= '--------------------------------<BR>';
						$orderInfo .= '<FS>订单已退款</<FS><BR>';

					}
					$orderInfo .= '<FS2><center>**#'.$last_print_index.' 完**</center></FS2>';

					$data = $print->index($yilian_machine_code,$orderInfo,$order_id);


					$print_result = array('code' => 1);

					///......待查看格式
				}

				//飞鹅
				if( !empty($open_feier_print) && $open_feier_print == 1)
				{
					$total_length = 32;

					$pay_time = date('Y-m-d H:i', $order_info['pay_time']);
					//printer_sn
					$orderInfo = '<CB>--#'.$last_print_index.$title.'--</CB><BR>';
					$orderInfo .= '<C><L>'.$shoname.'</L></C><BR>';
					$orderInfo .= '订单时间:'.$pay_time.'<BR>';

					if( in_array($title, array('用户取消订单','后台操作取消订单','群接龙后台取消订单') ) )
					{
						$refund_time = date('Y-m-d H:i:s', time() );
						$orderInfo .= '取消时间:'.$refund_time."<BR>";
					}
					$orderInfo .= '订单编号:'.$order_info['order_num_alias'].'<BR>';
                    if( $order_info['payment_code'] == 'cashon_delivery'){
                        $orderInfo .= '支付方式:货到付款'.'<BR>';
                    }
					//head_id order_id

					$head_relative_line = M('eaterplanet_ecommerce_deliveryline_headrelative')->where( array('head_id' => $order_info['head_id']) )->find();


					if( !empty($head_relative_line) )
					{
						$line_id = $head_relative_line['line_id'];

						$line_info = M('eaterplanet_ecommerce_deliveryline')->where( array('id' => $line_id ) )->find();

						$orderInfo .= '线路名称:'.$line_info['name'].'<BR>';
					}
					if($order_info['expected_delivery_time']){
						$orderInfo .= '--------------------------------<BR>';
						$orderInfo .= '配送时间段:'.$order_info['expected_delivery_time'].'<BR>';
						$orderInfo .= '--------------------------------<BR>';
					}

					$head_info = M('eaterplanet_community_head')->where( array('id' => $order_info['head_id'] ) )->find();


					$orderInfo .= '收货小区:'.$head_info['community_name'].'<BR>';
					$orderInfo .= $owner_name.'姓名:'.$head_info['head_name'].'<BR>';
					$orderInfo .= $owner_name.'手机:'.$head_info['head_mobile'].'<BR>';
					$member =  M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();
					if($member['card_id'] > 0 && $member['card_end_time'] >time() ){
						$orderInfo .= '<L>姓   名:'.$order_info['shipping_name'].'(付费VIP)</L><BR>';
					}else{
						$orderInfo .= '<L>姓   名:'.$order_info['shipping_name'].'</L><BR>';
					}
					$orderInfo .= '<L>电   话:'.$order_info['shipping_tel'].'</L><BR>';



					//delivery   pickup  tuanz_send
					if( $order_info['delivery'] == 'pickup' )
					{
						 if( $order_info['type'] == 'virtual' )
						{
							$orderInfo .= '收货地址:'.$order_info['shipping_address'].'<BR>';
							$orderInfo .= '配送方式:门店核销<BR>';//团长配送
						}else{
						   $orderInfo .= '收货地址:'.$order_info['shipping_address'].'<BR>';

						   if( !empty($delivery_ziti_name) )
						   {
							   $orderInfo .= '配送方式:'.$delivery_ziti_name.'<BR>';//团长配送
						   }else{
								$orderInfo .= '配送方式:团员自提<BR>';//团长配送
						   }

						}



					}else if( $order_info['delivery'] == 'tuanz_send'){
						// address_id

						$orderInfo .= '送货地址:'.$order_info['tuan_send_address'].'<BR>';

						if( !empty($delivery_tuanzshipping_name) )
						{
							$orderInfo .= '<L>配送方式:'.$delivery_tuanzshipping_name.'</L><BR>';//团长配送
						}else{
							$orderInfo .= '<L>配送方式:'.$owner_name.'送货上门</L><BR>';//团长配送
						}

					}else{

						$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
						$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
						$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);
						//name
						$sp_address = $province_info['name'].$city_info['name'].$area_info['name'];
						$orderInfo .= '收货地址:'.$sp_address.$order_info['shipping_address']."<BR>";


						if( $order_info['delivery'] == 'localtown_delivery'){
							$orderInfo .= '<L>配送方式:同城配送</L><BR>';//同城配送
						}else if( $order_info['delivery'] == 'hexiao'){
							$orderInfo .= '<L>配送方式:到店核销</L><BR>';//到店核销
						}else{
							if( !empty($delivery_express_name) )
							{
								$orderInfo .= '<L>配送方式:'.$delivery_express_name.'</L><BR>';
							}else{
								$orderInfo .= '<L>配送方式:快递</L><BR>';
							}
						}


					}


					$orderInfo .= '-------------商品---------------<BR>';
					$orderInfo .= '商品名称　　　　数量　      金额<BR>';

					$demo_str = '商品名称　　　　数量　      金额';


					$total_count = 0;

					foreach($order_goods as $val )
					{
						$name = $val['name'];
						$total = $val['total'];
						$quantity = $val['quantity'];

						$goods_id = $val['goods_id'];

						$goods_common = M('eaterplanet_ecommerce_good_common')->field('print_sub_title')->where( array('goods_id' => $goods_id) )->find();

						$goods_name_str = "";
						if( !empty($goods_common['print_sub_title']) )
						{
							$goods_name_str = $goods_common['print_sub_title'].'　'.$val['option_sku'];
						}else{
							$goods_name_str = $name.'　'.$val['option_sku'];
						}

							//17
						//$goods_name_last =  mb_substr($goods_name_str,0,7,'utf-8');//20190221
						$orderInfo .= $goods_name_str.'<BR>';


						$newStr = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $goods_name_last);  //正则匹配中文
						$zw_length = mb_strlen($newStr,"utf-8");  //得到中汉字个数



						$tt_length = mb_strlen($goods_name_last,'utf-8') - $zw_length;


						//mb_strlen($goods_name_last,'utf-8') -


						$zhongjian =  18;

						if($zw_length <= 0)
						{
							$zhongjian = $zhongjian -1;
						}

						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}

						$orderInfo .= $zhognjian_ge;


						$quantity_str = 'x'.$quantity;
						$total_str = sprintf('%.2f',$total);

						$orderInfo .= $quantity_str;
						$right_gezi = 14 - strlen($quantity_str) -  strlen(sprintf('%.2f',$total));


						$ggg_zi = '';
						for( $i =1;$i<=$right_gezi;$i++ )
						{
							 $ggg_zi .= ' ';
						}

						$orderInfo.= $ggg_zi;

						$orderInfo .= sprintf('%.2f',$total).'<BR>';


						$total_count += $quantity;
					}


					$orderInfo .= '--------------------------------<BR>';


					$zhongjian = 32 - 10 - strlen($total_count);
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '商品总数：'.$zhognjian_ge.$total_count.'<BR>';

					if( !empty($order_info['fullreduction_money']) && $order_info['fullreduction_money'] >0)
					{
						$zhongjian = 32 - 9 - strlen(sprintf('%.2f',$order_info['fullreduction_money']));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}

						$orderInfo .= '满减：'.$zhognjian_ge.'-'.sprintf('%.2f',$order_info['fullreduction_money']).'元<BR>';
					}
					if( !empty($order_info['voucher_credit']) && $order_info['voucher_credit'] >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$order_info['voucher_credit']));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '优惠券：'.$zhognjian_ge.'-'.sprintf('%.2f',$order_info['voucher_credit']).'元<BR>';
					}

					//score_for_money
					$score_for_money = $order_info['score_for_money'];
					if( !empty($score_for_money) && $score_for_money >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$score_for_money));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '积分抵：'.$zhognjian_ge.'-'.sprintf('%.2f',$score_for_money).'元<BR>';
					}

					/**
					$shipping_fare = $order_info['shipping_fare'];
					if( !empty($shipping_fare) && $shipping_fare >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$orderInfo .= '配送费：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元<BR>';
					}
					**/

					$shipping_fare = $order_info['shipping_fare'];
					$man_e_money = $order_info['man_e_money'];
					$fare_shipping_free = $order_info['fare_shipping_free'];
					$is_free_shipping_fare = $order_info['is_free_shipping_fare'];
					if($is_free_shipping_fare == 1 && $fare_shipping_free > 0)
					{
						if( !empty($fare_shipping_free) && $fare_shipping_free >0)
						{
							//满$man_e_money免运费    -7 man_e_money
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}

							if( $order_info['delivery'] == 'tuanz_send')
							{
								$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."<BR>";
							}else if( $order_info['delivery'] == 'express')
							{
								$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."<BR>";
							}

						}
						if( !empty($fare_shipping_free) && $fare_shipping_free >0)
						{
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}
							$man_e_money = floor($man_e_money * 100) / 100;
							$orderInfo .= '满'.$man_e_money.'免运费：'.$zhognjian_ge.'-'.sprintf('%.2f',$fare_shipping_free).'元'."<BR>";
							$shipping_fare = 0;
						}
					}else{
						if( !empty($shipping_fare) && $shipping_fare >0)
						{
							$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
							$zhognjian_ge = '';

							for($i =1;$i<=$zhongjian;$i++)
							{
								$zhognjian_ge .= ' ';
							}

							if( $order_info['delivery'] == 'tuanz_send')
							{
								$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元<BR>';
							}else if( $order_info['delivery'] == 'express')
							{
								$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元<BR>';
							}
						}
					}

					$zhongjian = 32 - 10 - strlen(sprintf('%.2f',$order_info['total']));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

//					$orderInfo .= '总金额：'.$zhognjian_ge.sprintf('%.2f',$order_info['total']).'元<BR>';
                    $order_type = $order_info['type'];
                    $orderInfo .= '总金额：'.$zhognjian_ge.sprintf('%.2f',$order_info['total']);
                    if ($order_type == "integral"){
                        $orderInfo .= "积分"."<BR>";
                    }else{
                        $orderInfo .= "元"."<BR>";
                    }

                    $orderInfo .= '********************************<BR>';
					if($shipping_fare == 0){
						$real_price = $order_info['total'] + $shipping_fare -$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']+$order_info['localtown_add_shipping_fare'];

					}else{
						$real_price = $order_info['total'] + $shipping_fare -$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']-$order_info['fare_shipping_free']+$order_info['localtown_add_shipping_fare'];

					}
					if($real_price < 0)
					{
						$real_price = 0;
					}
					$real_price = sprintf('%.2f',$real_price);

					$zhongjian = 32 - 12 - strlen($real_price);
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}


					if ($order_type == "integral"){
    				    if($real_price- $order_info['total'] == 0){
    				        $orderInfo .= '实付金额：'.sprintf('%.2f',$order_info['total']).'积分<BR>';
    				    }else{
    				        $orderInfo .= '实付金额：'.$zhognjian_ge.($real_price- $order_info['total']).'元+'.sprintf('%.2f',$order_info['total']).'积分<BR>';
    				    }
                    }else{
                       $orderInfo .= '实付金额：'.$zhognjian_ge.$real_price.'元<BR>';
                    }
					//comment

					$orderInfo .= '--------------------------------<BR>';
					//order_info  <BR>

					if( !empty($order_info['comment']) )
					{
						$orderInfo .= '<B>备注：'.$order_info['comment'].'</B><BR>';
					}

					//begin pr
					$is_print_member_note = D('Home/Front')->get_config_by_name('is_print_member_note');
					if( isset($is_print_member_note) && $is_print_member_note == 1 )
					{
						$mb_info = M('eaterplanet_ecommerce_member')->field('content')->where( array('member_id' => $order_info['member_id'] ) )->find();

						if( !empty($mb_info['content']) )
						{
							$orderInfo .= '客户备注：'.$mb_info['content'].''."<BR>";
						}
					}

					$is_print_order_note = D('Home/Front')->get_config_by_name('is_print_order_note');
					$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');
					$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');
					if( !empty($is_print_order_note) && $is_print_order_note == 1 )
					{
						if( !empty($order_info['note_content']) )
						{
							if( !empty($order_note_open) &&  !empty($order_note_name))
							{
								$orderInfo .= $order_note_name.'：'.$order_info['note_content'].''."<BR>";
							}else{
								$orderInfo .= '自定义备注：'.$order_info['note_content'].''."<BR>";
							}
						}
					}
					//end pr


					if($order_info['order_status_id'] == 7){
						$orderInfo .= '--------------------------------<BR>';
						$orderInfo .= '<L>订单已退款</<L><BR>';

					}


					$orderInfo .= '<CB>**#'.$last_print_index.'  完**</CB><BR>';


					$feier_print_lian = D('Home/Front')->get_config_by_name('feier_print_lian');

					if( empty($feier_print_lian) ||  $feier_print_lian < 1)
					{
						$feier_print_lian = 1;
					}

					$print_result = $this->wp_print($orderInfo, $feier_print_lian, $feier_print_sn);


					if( $print_result['code'] == 0)
					{
						M('eaterplanet_ecommerce_order')->where( array('order_id' =>$order_id ) )->save( array('is_print_suc' => 0) );
					}

				}


			// $this->print_supply_order($order_id, $supply_goods_info);

			/**
				后台操作取消订单

				群接龙后台取消订单

				用户取消订单
			**/


			$print_result2 = $this->print_supply_order($order_id, $supply_goods_info, $title);

			if( !$is_print  )
			{
				$print_result = $print_result2;
				if( empty($print_result) )
				{
					$print_result = array('code' => 0, 'msg' => '独立商户订单不打印');
				}
			}

			return $print_result;

		}


	    //新版打印小票
		public function check_print_order2($order_id,$title='在线支付')
		{

			$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

			$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id ) )->select();

			foreach($order_goods as &$value)
			{
				$value['option_sku'] = D('Seller/Order')->get_order_option_sku($order_id, $value['order_goods_id']);
			}

			$owner_name = D('Home/Front')->get_config_by_name('owner_name');

			if( empty($owner_name) )
			{
				$owner_name = '团长';
			}





			$delivery_tuanzshipping_name = D('Home/Front')->get_config_by_name('delivery_tuanzshipping_name');//团长配送

			if( empty($delivery_tuanzshipping_name) )
			{
				$delivery_tuanzshipping_name = '';
			}

			$delivery_express_name = D('Home/Front')->get_config_by_name('delivery_express_name');//快递配送

			if( empty($delivery_express_name) )
			{
				$delivery_express_name = '';
			}

			$delivery_ziti_name = D('Home/Front')->get_config_by_name('delivery_ziti_name');//到点自提

			if(  empty($delivery_ziti_name) )
			{
				$delivery_ziti_name = '';
			}


			$placeorder_trans_name = D('Home/Front')->get_config_by_name('placeorder_trans_name');//快递费

			if( !isset($placeorder_trans_name) || empty($placeorder_trans_name) )
			{
				$placeorder_trans_name = '快递费';
			}


			$placeorder_tuan_name = D('Home/Front')->get_config_by_name('placeorder_tuan_name');//配送费

			if( !isset($placeorder_tuan_name) || empty($placeorder_tuan_name) )
			{
				$placeorder_tuan_name = '配送费';
			}

			/***
				商户订单商品集合
			**/
			$supply_goods_info = array();

			/***
				商户订单商品集合
			**/
			$is_print_dansupply_order = D('Home/Front')->get_config_by_name('is_print_dansupply_order');

			if( isset($is_print_dansupply_order) && $is_print_dansupply_order == 1 )
			{
				$is_print_dansupply_order = 1;
			}else if( !isset($is_print_dansupply_order) || $is_print_dansupply_order == 0 )
			{
				$is_print_dansupply_order = 0;
			}

			//打印隐藏客户手机号
			$is_printhide_membermobile = D('Home/Front')->get_config_by_name('is_printhide_membermobile');

			if( isset($is_printhide_membermobile) && $is_printhide_membermobile == 1 )
			{
				//隐藏
				$order_info['shipping_tel'] = substr($order_info['shipping_tel'],0,3).'*****'.substr($order_info['shipping_tel'],-3,3);
			}

			$is_print = true;

			foreach($order_goods as &$value)
			{
				$value['option_sku'] = D('Seller/Order')->get_order_option_sku($value['order_id'], $value['order_goods_id']);

				if( $value['supply_id'] > 0 )
				{
					if( isset($supply_goods_info[ $value['supply_id'] ]) )
					{
						$supply_goods_info[ $value['supply_id'] ][] = $value;
					}else{
						$supply_goods_info[ $value['supply_id'] ] = array();
						$supply_goods_info[ $value['supply_id'] ][] = $value;
					}
				}
			}

			$shoname = D('Home/Front')->get_config_by_name('shoname');

			$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print');

			//打印联数
			$feier_print_sn = D('Home/Front')->get_config_by_name('feier_print_sn');

			$last_print_time  = D('Home/Front')->get_config_by_name('last_print_time');
			$last_print_index = D('Home/Front')->get_config_by_name('last_print_index');

			$now_time = strtotime( date('Y-m-d').' 00:00:00' );

			/*
			if( empty($last_print_time) || $last_print_time < $now_time )
			{
				$last_print_index = 1;
				$last_print_time = time();

				D('Seller/Config')->update( array('last_print_index' => $last_print_index, 'last_print_time' => $last_print_time) );
			}else if($last_print_time > $now_time) {
				$last_print_index = empty($last_print_index) ? 1: $last_print_index+1;

				D('Seller/Config')->update( array('last_print_index' => $last_print_index, 'last_print_time' => time() ) );
			}
			*/
			//继续使用原有的打印机设置，旧版打印机与“默认订单打印机”一起打印小票，也可以关闭旧版打印机设置使用新版本“默认打印机”
			//默认打印机的参数
			$data = D('Seller/Config')->get_all_config();
			if(isset($data['is_printer_list']) && !empty($data['is_printer_list'])){
				$printer_list = M('eaterplanet_ecommerce_printer')->where( array('id' => array('in',$data['is_printer_list']) ) )->select();
			}

			//新版打印机 有绑定打印机
			if( !empty($printer_list) ){
						//“默认打印机”
						foreach($printer_list as $var){

							if(!empty($var["status"])){

								//判断打印机 1飞鹅 2易联云
								if($var["printer_type"] == 1){
									//$var["printer_sn"]-sn   $var["printer_key"]-key $var["printer_num"]-打印联数
									//                              订单信息                      标题  ，商城名称,运费，运费,sn,key,打印联数，
									$print_result = $this->newfeier($order_info,$order_id,$last_print_index,$title,$shoname,$order_goods,$placeorder_tuan_name,$placeorder_trans_name,$var["printer_sn"],$var["printer_key"],$var["printer_num"]);

								}
								if($var["printer_type"] == 2){
									//$var["api_id"]-应用id  $var["api_key"]-应用密钥key  $var["printer_sn"]-打印机终端号  $var["printer_key"]-终端密钥  $var["printer_num"]-打印联数
									//                              订单信息       标题  ，商城名称,订单商品，运费，运费,id key 终端号,终端密钥,打印联数，

									$print_result = $this->newyilian($order_info,$order_id,$last_print_index,$title,$shoname,$order_goods,$placeorder_tuan_name,$placeorder_trans_name,$var["api_id"],$var["api_key"],$var["printer_sn"],$var["printer_key"],$var["printer_num"]);


								}
							}
						}

			}



			// $this->print_supply_order($order_id, $supply_goods_info);

			/**
				后台操作取消订单

				群接龙后台取消订单

				用户取消订单
			**/




			return $print_result;

		}

		//===========方法2.查询某订单是否打印成功=============
		//***接口返回值说明***
		//正确例子：
		//已打印：{"msg":"ok","ret":0,"data":true,"serverExecutedTime":6}
		//未打印：{"msg":"ok","ret":0,"data":false,"serverExecutedTime":6}

		//打开注释可测试
		//$orderid = "xxxxxxxx_xxxxxxxxxx_xxxxxxxx";//订单ID，从方法1返回值中获取
		//queryOrderState($orderid);

		/*
		 *  方法2
			根据订单索引,去查询订单是否打印成功,订单索引由方法1返回
		*/
		function queryOrderState($index){
				$msgInfo = array(
					'user'=>$this->user,
					'stime'=>$this->stime,
					'sig'=>$this->sig,
					'apiname'=>'Open_queryOrderState',

					'orderid'=>$index
				);

			$http_model = load_model_class('httpclient');

			$client = new $http_model($this->ip,$this->port);
			if(!$client->post($this->path,$msgInfo)){
				var_dump('error');
				die();
			}
			else{
				$result = $client->getContent();
				var_dump($result);
				die();
			}
		}


		//===========方法3.查询指定打印机某天的订单详情============
		//***接口返回值说明***
		//正确例子：{"msg":"ok","ret":0,"data":{"print":6,"waiting":1},"serverExecutedTime":9}

		//打开注释可测试
		//$date = "2017-04-02";//注意时间格式为"yyyy-MM-dd",如2016-08-27
		//queryOrderInfoByDate(SN,$date);

		/*
		 *  方法3
			查询指定打印机某天的订单详情
		*/
		function queryOrderInfoByDate($printer_sn,$date){
			$msgInfo = array(
				'user'=>$this->user,
				'stime'=>$this->stime,
				'sig'=>$this->sig,
				'apiname'=>'Open_queryOrderInfoByDate',

				'sn'=>$printer_sn,
				'date'=>$date
			);

			$http_model = load_model_class('httpclient');

			$client = new $http_model($this->ip,$this->port);
			if(!$client->post($this->path,$msgInfo)){

				var_dump('error');
				die();
			}
			else{
				$result = $client->getContent();
				echo $result;
			}

		}



		//===========方法4.查询打印机的状态==========================
		//***接口返回值说明***
		//正确例子：
		//{"msg":"ok","ret":0,"data":"离线","serverExecutedTime":9}
		//{"msg":"ok","ret":0,"data":"在线，工作状态正常","serverExecutedTime":9}
		//{"msg":"ok","ret":0,"data":"在线，工作状态不正常","serverExecutedTime":9}

		//打开注释可测试
		//queryPrinterStatus(SN);
		/*
		 *  方法4
			查询打印机的状态
		*/
		function queryPrinterStatus($printer_sn){

				$msgInfo = array(
					'user'=>USER,
					'stime'=>STIME,
					'sig'=>SIG,
					'apiname'=>'Open_queryPrinterStatus',

					'sn'=>$printer_sn
				);

			$client = new HttpClient(IP,PORT);
			if(!$client->post(PATH,$msgInfo)){
				echo 'error';
			}
			else{
				$result = $client->getContent();
				echo $result;
			}
		}

		//默认打印机小票参数
		//易联云
		//$this->newyilian($order_info,$last_print_index,$title,$shoname,$order_goods,$placeorder_tuan_name,$placeorder_trans_name,$var["api_id"],$var["api_key"],$var["printer_sn"],$var["printer_key"],$var["printer_num"]);
		function newyilian($order_info,$order_id,$last_print_index,$title,$shoname,$order_goods,$placeorder_tuan_name,$placeorder_trans_name,$api_id,$api_key,$printer_sn,$printer_key,$printer_num ){

				$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
				require_once $lib_path."/Yilianyun/Lib/Autoloader.php";

				//$yilian_client_id = D('Home/Front')->get_config_by_name('yilian_client_id');
				//$yilian_client_key = D('Home/Front')->get_config_by_name('yilian_client_key' );

				$yilian_client_id = $api_id;
				$yilian_client_key = $api_key;


				$config = new YlyConfig($yilian_client_id, $yilian_client_key);

				$token = $this->_get_yilian_access_token($yilian_client_id,$yilian_client_key);

				//$yilian_machine_code = D('Home/Front')->get_config_by_name('yilian_machine_code' );
				$yilian_machine_code = $printer_sn;

				$print = new PrintService($token['access_token'], $config);

				//object(stdClass)#14 (3) { ["error"]=> string(1) "0" ["error_description"]=> string(7) "success" ["body"]=> object(stdClass)#15 (2) { ["id"]=> string(9) "221354299" ["origin_id"]=> string(3) "558" } }

				//<MN>1</MN>
				//$data = $print->index($yilian_machine_code,'打印内容排版可看Demo下的callback.php','558');
				//-------------------------------------------------------------------------------------------
				//$yilian_print_lian = D('Home/Front')->get_config_by_name('yilian_print_lian');
				$yilian_print_lian = $printer_num;
				if( empty($yilian_print_lian) ||  $yilian_print_lian < 1)
				{
					$yilian_print_lian = 1;
				}

				$orderInfo = '<MN>'.$yilian_print_lian.'</MN>';
				$total_length = 32;

				$pay_time = date('Y-m-d H:i', $order_info['pay_time']);
				$orderInfo = '<FS2><center>--#'.$last_print_index.$title.'--</center></FS2>';
				$orderInfo .= '<FS2><center>'.$shoname.'</center></FS2>';
				$orderInfo .= '订单时间:'.$pay_time."\n";
				if( in_array($title, array('用户取消订单','后台操作取消订单','群接龙后台取消订单') ) )
				{
					$refund_time = date('Y-m-d H:i:s', time() );
					$orderInfo .= '取消时间:'.$refund_time."\n";
				}

				$orderInfo .= '订单编号:'.$order_info['order_num_alias']."\n";
                if( $order_info['payment_code'] == 'cashon_delivery'){
                    $orderInfo .= '支付方式:货到付款'."\n";
                }
				//head_id order_id


				$head_relative_line = M('eaterplanet_ecommerce_deliveryline_headrelative')->where( array('head_id' => $order_info['head_id'] ) )->find();


				if( !empty($head_relative_line) )
				{
					$line_id = $head_relative_line['line_id'];

					$line_info = M('eaterplanet_ecommerce_deliveryline')->where( array('id' => $line_id ) )->find();

					$orderInfo .= '线路名称:'.$line_info['name']."\n";
				}
				if($order_info['expected_delivery_time']){
						$orderInfo .= '--------------------------------'."\n";
						$orderInfo .= '配送时间段:'.$order_info['expected_delivery_time']."\n";
						$orderInfo .= '--------------------------------'."\n";
					}
				$head_info = M('eaterplanet_community_head')->where( array('id' => $order_info['head_id'] ) )->find();

				$orderInfo .= '收货小区:'.$head_info['community_name']."\n";
				$orderInfo .= $owner_name.'姓名:'.$head_info['head_name']."\n";
				$orderInfo .= $owner_name.'手机:'.$head_info['head_mobile']."\n";
				$member =  M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();
				if($member['card_id'] > 0 && $member['card_end_time'] >time() ){
					$orderInfo .= '<FS>姓   名:'.$order_info['shipping_name'].'(付费VIP)</FS>'."\n";
				}else{
					$orderInfo .= '<FS>姓   名:'.$order_info['shipping_name'].'</FS>'."\n";
				}
				$orderInfo .= '<FS>电   话:'.$order_info['shipping_tel'].'</FS>'."\n";

				$delivery_tuanzshipping_name = D('Home/Front')->get_config_by_name('delivery_tuanzshipping_name');//团长配送

				if( empty($delivery_tuanzshipping_name) )
				{
					$delivery_tuanzshipping_name = '';
				}

				$delivery_express_name = D('Home/Front')->get_config_by_name('delivery_express_name');//快递配送

				if( empty($delivery_express_name) )
				{
					$delivery_express_name = '';
				}

				$delivery_ziti_name = D('Home/Front')->get_config_by_name('delivery_ziti_name');//到点自提

				if(  empty($delivery_ziti_name) )
				{
					$delivery_ziti_name = '';
				}


				$placeorder_trans_name = D('Home/Front')->get_config_by_name('placeorder_trans_name');//快递费

				if( !isset($placeorder_trans_name) || empty($placeorder_trans_name) )
				{
					$placeorder_trans_name = '快递费';
				}


				$placeorder_tuan_name = D('Home/Front')->get_config_by_name('placeorder_tuan_name');//配送费

				if( !isset($placeorder_tuan_name) || empty($placeorder_tuan_name) )
				{
					$placeorder_tuan_name = '配送费';
				}

				//delivery   pickup  tuanz_send
				if( $order_info['delivery'] == 'pickup' )
				{
					if( $order_info['type'] == 'virtual' )
				    {
				        $orderInfo .= '收货地址:'.$order_info['shipping_address']."\n";
				        $orderInfo .= '配送方式:门店核销'."\n";//团长配送
				    }else{
				        $orderInfo .= '收货地址:'.$order_info['shipping_address']."\n";
						if( !empty($delivery_ziti_name) )
						{
							$orderInfo .= '配送方式:'.$delivery_ziti_name."\n";//团长配送
						}else{
							$orderInfo .= '配送方式:团员自提'."\n";//团长配送
						}
				    }

				}else if( $order_info['delivery'] == 'tuanz_send'){
					// address_id

					if($order_info['address_id'] > 0)
					{

						$ad_info =  M('eaterplanet_ecommerce_address')->field('lou_meng_hao')->where( array('address_id' => $order_info['address_id'] ) )->find();

						if( !empty($ad_info) )
						{
							//$order_info['tuan_send_address'] .= $ad_info['lou_meng_hao'];
						}
					}

					$orderInfo .= '送货地址:'.$order_info['tuan_send_address']."\n";

					if( !empty($delivery_tuanzshipping_name) )
					{
						$orderInfo .= '配送方式:'.$delivery_tuanzshipping_name.''."\n";//团长配送
					}else{
						$orderInfo .= '配送方式:'.$owner_name.'送货上门'."\n";//团长配送
					}

				}else{
					$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
					$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
					$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);

					$sp_address = $province_info['name'].$city_info['name'].$area_info['name'];

					$orderInfo .= '收货地址:'.$sp_address.$order_info['shipping_address']."\n";

					if( $order_info['delivery'] == 'localtown_delivery'){
							$orderInfo .= '配送方式:同城配送'."\n";//同城配送
					}else if( $order_info['delivery'] == 'hexiao'){
							$orderInfo .= '<L>配送方式:到店核销</L><BR>';//到店核销
					}else{
						if( !empty($delivery_express_name) )
						{
							$orderInfo .= '配送方式:'.$delivery_express_name."\n";
						}else{
							$orderInfo .= '配送方式:快递'."\n";
						}
					}
				}


				$orderInfo .= '-------------商品---------------'."\n";
				$orderInfo .= '商品名称　　　　数量　      金额'."\n";

				$demo_str = '商品名称　　　　数量　      金额'."\n";


				$total_count = 0;

				foreach($order_goods as $val )
				{
					$name = $val['name'];
					$total = $val['total'];
					$quantity = $val['quantity'];

					$goods_id = $val['goods_id'];

					$goods_common = M('eaterplanet_ecommerce_good_common')->field('print_sub_title')->where( array('goods_id' => $goods_id ) )->find();

					$goods_name_str = "";
					if( !empty($goods_common['print_sub_title']) )
					{
						$goods_name_str = $goods_common['print_sub_title'].'　'.$val['option_sku'];
					}else{
						$goods_name_str = $name.'　'.$val['option_sku'];
					}

					//17

					$orderInfo .= $goods_name_str."\n";;


					$newStr = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $goods_name_last);  //正则匹配中文
					$zw_length = mb_strlen($newStr,"utf-8");  //得到中汉字个数

					//$zw_length = $this->linyufan_get_cn_num($goods_name_last);
					$tt_length = mb_strlen($goods_name_last,'utf-8') - $zw_length;

					//mb_strlen($goods_name_last,'utf-8') -

					$zhongjian =  18;
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					//$orderInfo .= $zhognjian_ge;
					$orderInfo .= "\t\t\t\t";


					$quantity_str = 'x'.$quantity;
					$total_str = sprintf('%.2f',$total);

					$orderInfo .= $quantity_str;
					$right_gezi = 14 - strlen($quantity_str) -  strlen(sprintf('%.2f',$total));



					for( $i =1;$i<=$right_gezi;$i++ )
					{
						$orderInfo .= ' ';
					}
					$orderInfo .= sprintf('%.2f',$total)."\n";

					$total_count += $quantity;
				}


				$orderInfo .= '--------------------------------'."\n";


				//var_dump( strlen($demo_str), mb_strlen($demo_str,'utf-8') );

				$zhongjian = 32 - 10 - strlen($total_count);
				$zhognjian_ge = '';

				for($i =1;$i<=$zhongjian;$i++)
				{
					$zhognjian_ge .= ' ';
				}
				$orderInfo .= '商品总数：'.$zhognjian_ge.$total_count."\n";

				if( !empty($order_info['fullreduction_money']) && $order_info['fullreduction_money'] >0)
				{
					$zhongjian = 32 - 9 - strlen(sprintf('%.2f',$order_info['fullreduction_money']));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

					$orderInfo .= '满减：'.$zhognjian_ge.'-'.sprintf('%.2f',$order_info['fullreduction_money']).'元'."\n";
				}



				if( !empty($order_info['voucher_credit']) && $order_info['voucher_credit'] >0)
				{
					$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$order_info['voucher_credit']));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '优惠券：'.$zhognjian_ge.'-'.sprintf('%.2f',$order_info['voucher_credit']).'元'."\n";
				}

				$score_for_money = $order_info['score_for_money'];
				if( !empty($score_for_money) && $score_for_money >0)
				{
					$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$score_for_money));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '积分抵：'.$zhognjian_ge.'-'.sprintf('%.2f',$score_for_money).'元'."\n";
				}


				/**
				$shipping_fare = $order_info['shipping_fare'];
				if( !empty($shipping_fare) && $shipping_fare >0)
				{
					$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '配送费：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元'."\n";
				}
				**/

				$man_e_money = $order_info['man_e_money'];
				$fare_shipping_free = $order_info['fare_shipping_free'];
				$is_free_shipping_fare = $order_info['is_free_shipping_fare'];
				$shipping_fare = $order_info['shipping_fare'];

				if($is_free_shipping_fare == 1 && $fare_shipping_free > 0)
				{
					if( !empty($fare_shipping_free) && $fare_shipping_free >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						if( $order_info['delivery'] == 'tuanz_send')
						{
							$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."\n";
						}else if( $order_info['delivery'] == 'express')
						{
							$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."\n";
						}

					}
					if( !empty($fare_shipping_free) && $fare_shipping_free >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$man_e_money = floor($man_e_money * 100) / 100;
						$orderInfo .= '满'.$man_e_money.'免运费：'.$zhognjian_ge.'-'.sprintf('%.2f',$fare_shipping_free).'元'."\n";
						$shipping_fare = 0;
					}
				}else{
					if( !empty($shipping_fare) && $shipping_fare >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}

						if( $order_info['delivery'] == 'tuanz_send')
						{
							$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元'."\n";
						}else if( $order_info['delivery'] == 'express')
						{
							$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元'."\n";
						}

					}
				}

				$zhongjian = 32 - 10 - strlen(sprintf('%.2f',$order_info['total']));
				$zhognjian_ge = '';

				for($i =1;$i<=$zhongjian;$i++)
				{
					$zhognjian_ge .= ' ';
				}

				$order_type = $order_info['type'];
                    $orderInfo .= '总金额：'.$zhognjian_ge.sprintf('%.2f',$order_info['total']);
                    if ($order_type == "integral"){
                        $orderInfo .= "积分"."\n";
                    }else{
                        $orderInfo .= "元"."\n";
                    }
				$orderInfo .= '********************************'."\n";
					if($shipping_fare == 0){
						$real_price = $order_info['total'] + $shipping_fare -$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']+$order_info['localtown_add_shipping_fare'];

					}else{
						$real_price = $order_info['total'] + $shipping_fare -$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']-$order_info['fare_shipping_free']+$order_info['localtown_add_shipping_fare'];

					}
				if($real_price < 0)
				{
					$real_price = 0;
				}
				$real_price = sprintf('%.2f',$real_price);

				$zhongjian = 32 - 12 - strlen($real_price);
				$zhognjian_ge = '';

				for($i =1;$i<=$zhongjian;$i++)
				{
					$zhognjian_ge .= ' ';
				}

				if ($order_type == "integral"){
    				    if($real_price- $order_info['total'] == 0){
    				        $orderInfo .= '实付金额：'.sprintf('%.2f',$order_info['total']).'积分'."\n";
    				    }else{
    				        $orderInfo .= '实付金额：'.$zhognjian_ge.($real_price- $order_info['total']).'元+'.sprintf('%.2f',$order_info['total']).'积分'."\n";
    				    }
                    }else{
                       $orderInfo .= '实付金额：'.$zhognjian_ge.$real_price.'元'."\n";
                    }
				//comment

				$orderInfo .= '--------------------------------'."\n";
				//order_info  <BR>

				if( !empty($order_info['comment']) )
				{
					$orderInfo .= '<FS>备注：'.$order_info['comment'].'</FS>'."\n";
				}

				//begin pr
				$is_print_member_note = D('Home/Front')->get_config_by_name('is_print_member_note');
				if( isset($is_print_member_note) && $is_print_member_note == 1 )
				{
					$mb_info = M('eaterplanet_ecommerce_member')->field('content')->where( array('member_id' => $order_info['member_id'] ) )->find();

					if( !empty($mb_info['content']) )
					{
						$orderInfo .= '客户备注：'.$mb_info['content'].''."\n";
					}
				}

					$is_print_order_note = D('Home/Front')->get_config_by_name('is_print_order_note');
					$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');
					$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');
					if( !empty($is_print_order_note) && $is_print_order_note == 1 )
					{
						if( !empty($order_info['note_content']) )
						{
							if( !empty($order_note_open) &&  !empty($order_note_name))
							{
								$orderInfo .= $order_note_name.'：'.$order_info['note_content'].''."<BR>";
							}else{
								$orderInfo .= '自定义备注：'.$order_info['note_content'].''."<BR>";
							}
						}
					}
				//end pr
				if($order_info['order_status_id'] == 7){
						$orderInfo .= '--------------------------------<BR>';
						$orderInfo .= '<FS>订单已退款</<FS><BR>';

				}
				$orderInfo .= '<FS2><center>**#'.$last_print_index.' 完**</center></FS2>';


				$data = $print->index($yilian_machine_code,$orderInfo,$order_id);



				$print_result = array('code' => 1);

				return $data;

				///......待查看格式
		}
		//$print_result = $this->newfeier($order_info,$last_print_index,$title,$shoname,$var["printer_sn"],$var["printer_key"],$var["printer_num"]);
		//飞鹅  //          订单信息                      标题  ，商城名称,订单商品      运费                运费                ,sn,key,打印联数，
		function newfeier($order_info,$order_id,$last_print_index,$title,$shoname,$order_goods,$placeorder_tuan_name,$placeorder_trans_name,$printer_sn,$printer_key,$printer_num){

				$total_length = 32;

				$pay_time = date('Y-m-d H:i', $order_info['pay_time']);
				//printer_sn
				$orderInfo = '<CB>--#'.$last_print_index.$title.'--</CB><BR>';
				$orderInfo .= '<C><L>'.$shoname.'</L></C><BR>';
				$orderInfo .= '订单时间:'.$pay_time.'<BR>';

				if( in_array($title, array('用户取消订单','后台操作取消订单','群接龙后台取消订单') ) )
				{
					$refund_time = date('Y-m-d H:i:s', time() );
					$orderInfo .= '取消时间:'.$refund_time."<BR>";
				}
				$orderInfo .= '订单编号:'.$order_info['order_num_alias'].'<BR>';
                if( $order_info['payment_code'] == 'cashon_delivery'){
                    $orderInfo .= '支付方式:货到付款'.'<BR>';
                }
				//head_id order_id

				$head_relative_line = M('eaterplanet_ecommerce_deliveryline_headrelative')->where( array('head_id' => $order_info['head_id']) )->find();


				if( !empty($head_relative_line) )
				{
					$line_id = $head_relative_line['line_id'];

					$line_info = M('eaterplanet_ecommerce_deliveryline')->where( array('id' => $line_id ) )->find();

					$orderInfo .= '线路名称:'.$line_info['name'].'<BR>';
				}
				if($order_info['expected_delivery_time']){
						$orderInfo .= '--------------------------------<BR>';
						$orderInfo .= '配送时间段:'.$order_info['expected_delivery_time'].'<BR>';
						$orderInfo .= '--------------------------------<BR>';
					}

				$head_info = M('eaterplanet_community_head')->where( array('id' => $order_info['head_id'] ) )->find();


				$orderInfo .= '收货小区:'.$head_info['community_name'].'<BR>';
				$orderInfo .= $owner_name.'姓名:'.$head_info['head_name'].'<BR>';
				$orderInfo .= $owner_name.'手机:'.$head_info['head_mobile'].'<BR>';
				$member =  M('eaterplanet_ecommerce_member')->where( array('member_id' => $order_info['member_id'] ) )->find();
				if($member['card_id'] > 0 && $member['card_end_time'] >time() ){
					$orderInfo .= '<L>姓   名:'.$order_info['shipping_name'].'(付费VIP)</L><BR>';
				}else{
					$orderInfo .= '<L>姓   名:'.$order_info['shipping_name'].'</L><BR>';
				}
				$orderInfo .= '<L>电   话:'.$order_info['shipping_tel'].'</L><BR>';

				$delivery_tuanzshipping_name = D('Home/Front')->get_config_by_name('delivery_tuanzshipping_name');//团长配送

				if( empty($delivery_tuanzshipping_name) )
				{
					$delivery_tuanzshipping_name = '';
				}

				$delivery_express_name = D('Home/Front')->get_config_by_name('delivery_express_name');//快递配送

				if( empty($delivery_express_name) )
				{
					$delivery_express_name = '';
				}

				$delivery_ziti_name = D('Home/Front')->get_config_by_name('delivery_ziti_name');//到点自提

				if(  empty($delivery_ziti_name) )
				{
					$delivery_ziti_name = '';
				}


				$placeorder_trans_name = D('Home/Front')->get_config_by_name('placeorder_trans_name');//快递费

				if( !isset($placeorder_trans_name) || empty($placeorder_trans_name) )
				{
					$placeorder_trans_name = '快递费';
				}


				$placeorder_tuan_name = D('Home/Front')->get_config_by_name('placeorder_tuan_name');//配送费

				if( !isset($placeorder_tuan_name) || empty($placeorder_tuan_name) )
				{
					$placeorder_tuan_name = '配送费';
				}

				//delivery   pickup  tuanz_send
				if( $order_info['delivery'] == 'pickup' )
				{
					 if( $order_info['type'] == 'virtual' )
				    {
				        $orderInfo .= '收货地址:'.$order_info['shipping_address'].'<BR>';
				        $orderInfo .= '配送方式:门店核销<BR>';//团长配送
				    }else{
				       $orderInfo .= '收货地址:'.$order_info['shipping_address'].'<BR>';

					   if( !empty($delivery_ziti_name) )
					   {
						   $orderInfo .= '配送方式:'.$delivery_ziti_name.'<BR>';//团长配送
					   }else{
						    $orderInfo .= '配送方式:团员自提<BR>';//团长配送
					   }

				    }



				}else if( $order_info['delivery'] == 'tuanz_send'){
					// address_id

					$orderInfo .= '送货地址:'.$order_info['tuan_send_address'].'<BR>';

					if( !empty($delivery_tuanzshipping_name) )
					{
						$orderInfo .= '<L>配送方式:'.$delivery_tuanzshipping_name.'</L><BR>';//团长配送
					}else{
						$orderInfo .= '<L>配送方式:'.$owner_name.'送货上门</L><BR>';//团长配送
					}

				}else{

					$province_info = D('Home/Front')->get_area_info($order_info['shipping_province_id']);
					$city_info = D('Home/Front')->get_area_info($order_info['shipping_city_id']);
					$area_info = D('Home/Front')->get_area_info($order_info['shipping_country_id']);
					//name
					$sp_address = $province_info['name'].$city_info['name'].$area_info['name'];
					$orderInfo .= '收货地址:'.$sp_address.$order_info['shipping_address']."<BR>";



					if( $order_info['delivery'] == 'localtown_delivery'){
							$orderInfo .= '<L>配送方式:同城配送</L><BR>';//同城配送
					}else if( $order_info['delivery'] == 'hexiao'){
							$orderInfo .= '<L>配送方式:到店核销</L><BR>';//到店核销
					}else{
							if( !empty($delivery_express_name) )
							{
								$orderInfo .= '<L>配送方式:'.$delivery_express_name.'</L><BR>';
							}else{
								$orderInfo .= '<L>配送方式:快递</L><BR>';
							}
					}
				}


				$orderInfo .= '-------------商品---------------<BR>';
				$orderInfo .= '商品名称　　　　数量　      金额<BR>';

				$demo_str = '商品名称　　　　数量　      金额';


				$total_count = 0;

				foreach($order_goods as $val )
				{
					$name = $val['name'];
					$total = $val['total'];
					$quantity = $val['quantity'];

					$goods_id = $val['goods_id'];

					$goods_common = M('eaterplanet_ecommerce_good_common')->field('print_sub_title')->where( array('goods_id' => $goods_id) )->find();

					$goods_name_str = "";
					if( !empty($goods_common['print_sub_title']) )
					{
						$goods_name_str = $goods_common['print_sub_title'].'　'.$val['option_sku'];
					}else{
						$goods_name_str = $name.'　'.$val['option_sku'];
					}

						//17
					//$goods_name_last =  mb_substr($goods_name_str,0,7,'utf-8');//20190221
					$orderInfo .= $goods_name_str.'<BR>';


					$newStr = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $goods_name_last);  //正则匹配中文
					$zw_length = mb_strlen($newStr,"utf-8");  //得到中汉字个数



					$tt_length = mb_strlen($goods_name_last,'utf-8') - $zw_length;


					//mb_strlen($goods_name_last,'utf-8') -


					$zhongjian =  18;

					if($zw_length <= 0)
					{
						$zhongjian = $zhongjian -1;
					}

					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

					$orderInfo .= $zhognjian_ge;


					$quantity_str = 'x'.$quantity;
					$total_str = sprintf('%.2f',$total);

					$orderInfo .= $quantity_str;
					$right_gezi = 14 - strlen($quantity_str) -  strlen(sprintf('%.2f',$total));


					$ggg_zi = '';
					for( $i =1;$i<=$right_gezi;$i++ )
					{
						 $ggg_zi .= ' ';
					}

					$orderInfo.= $ggg_zi;

					$orderInfo .= sprintf('%.2f',$total).'<BR>';


					$total_count += $quantity;
				}


				$orderInfo .= '--------------------------------<BR>';


				$zhongjian = 32 - 10 - strlen($total_count);
				$zhognjian_ge = '';

				for($i =1;$i<=$zhongjian;$i++)
				{
					$zhognjian_ge .= ' ';
				}
				$orderInfo .= '商品总数：'.$zhognjian_ge.$total_count.'<BR>';

				if( !empty($order_info['fullreduction_money']) && $order_info['fullreduction_money'] >0)
				{
					$zhongjian = 32 - 9 - strlen(sprintf('%.2f',$order_info['fullreduction_money']));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}

					$orderInfo .= '满减：'.$zhognjian_ge.'-'.sprintf('%.2f',$order_info['fullreduction_money']).'元<BR>';
				}
				if( !empty($order_info['voucher_credit']) && $order_info['voucher_credit'] >0)
				{
					$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$order_info['voucher_credit']));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '优惠券：'.$zhognjian_ge.'-'.sprintf('%.2f',$order_info['voucher_credit']).'元<BR>';
				}

				//score_for_money
				$score_for_money = $order_info['score_for_money'];
				if( !empty($score_for_money) && $score_for_money >0)
				{
					$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$score_for_money));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '积分抵：'.$zhognjian_ge.'-'.sprintf('%.2f',$score_for_money).'元<BR>';
				}

				/**
				$shipping_fare = $order_info['shipping_fare'];
				if( !empty($shipping_fare) && $shipping_fare >0)
				{
					$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
					$zhognjian_ge = '';

					for($i =1;$i<=$zhongjian;$i++)
					{
						$zhognjian_ge .= ' ';
					}
					$orderInfo .= '配送费：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元<BR>';
				}
				**/

				$shipping_fare = $order_info['shipping_fare'];
				$man_e_money = $order_info['man_e_money'];
				$fare_shipping_free = $order_info['fare_shipping_free'];
				$is_free_shipping_fare = $order_info['is_free_shipping_fare'];
				if($is_free_shipping_fare == 1 && $fare_shipping_free > 0)
				{
					if( !empty($fare_shipping_free) && $fare_shipping_free >0)
					{
						//满$man_e_money免运费    -7 man_e_money
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}

						if( $order_info['delivery'] == 'tuanz_send')
						{
							$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."<BR>";
						}else if( $order_info['delivery'] == 'express')
						{
							$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$fare_shipping_free).'元'."<BR>";
						}

					}
					if( !empty($fare_shipping_free) && $fare_shipping_free >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$fare_shipping_free));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}
						$man_e_money = floor($man_e_money * 100) / 100;
						$orderInfo .= '满'.$man_e_money.'免运费：'.$zhognjian_ge.'-'.sprintf('%.2f',$fare_shipping_free).'元'."<BR>";
						$shipping_fare = 0;
					}
				}else{
					if( !empty($shipping_fare) && $shipping_fare >0)
					{
						$zhongjian = 32 - 11 - strlen(sprintf('%.2f',$shipping_fare));
						$zhognjian_ge = '';

						for($i =1;$i<=$zhongjian;$i++)
						{
							$zhognjian_ge .= ' ';
						}

						if( $order_info['delivery'] == 'tuanz_send')
						{
							$orderInfo .= $placeorder_tuan_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元<BR>';
						}else if( $order_info['delivery'] == 'express')
						{
							$orderInfo .= $placeorder_trans_name.'：'.$zhognjian_ge.'+'.sprintf('%.2f',$shipping_fare).'元<BR>';
						}
					}
				}

				$zhongjian = 32 - 10 - strlen(sprintf('%.2f',$order_info['total']));
				$zhognjian_ge = '';

				for($i =1;$i<=$zhongjian;$i++)
				{
					$zhognjian_ge .= ' ';
				}

				$order_type = $order_info['type'];
                    $orderInfo .= '总金额：'.$zhognjian_ge.sprintf('%.2f',$order_info['total']);
                    if ($order_type == "integral"){
                        $orderInfo .= "积分"."<BR>";
                    }else{
                        $orderInfo .= "元"."<BR>";
                    }
				$orderInfo .= '********************************<BR>';
					if($shipping_fare == 0){
						$real_price = $order_info['total'] + $shipping_fare -$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']+$order_info['localtown_add_shipping_fare'];

					}else{
						$real_price = $order_info['total'] + $shipping_fare -$order_info['voucher_credit']-$order_info['fullreduction_money']-$order_info['score_for_money']-$order_info['fare_shipping_free']+$order_info['localtown_add_shipping_fare'];

					}
				if($real_price < 0)
				{
					$real_price = 0;
				}
				$real_price = sprintf('%.2f',$real_price);

				$zhongjian = 32 - 12 - strlen($real_price);
				$zhognjian_ge = '';

				for($i =1;$i<=$zhongjian;$i++)
				{
					$zhognjian_ge .= ' ';
				}

				if ($order_type == "integral"){
    				    if($real_price- $order_info['total'] == 0){
    				        $orderInfo .= '实付金额：'.sprintf('%.2f',$order_info['total']).'积分<BR>';
    				    }else{
    				        $orderInfo .= '实付金额：'.$zhognjian_ge.($real_price- $order_info['total']).'元+'.sprintf('%.2f',$order_info['total']).'积分<BR>';
    				    }

                    }else{
                       $orderInfo .= '实付金额：'.$zhognjian_ge.$real_price.'元<BR>';
                    }

				//comment

				$orderInfo .= '--------------------------------<BR>';
				//order_info  <BR>

				if( !empty($order_info['comment']) )
				{
					$orderInfo .= '<B>备注：'.$order_info['comment'].'</B><BR>';
				}

				//begin pr
				$is_print_member_note = D('Home/Front')->get_config_by_name('is_print_member_note');
				if( isset($is_print_member_note) && $is_print_member_note == 1 )
				{
					$mb_info = M('eaterplanet_ecommerce_member')->field('content')->where( array('member_id' => $order_info['member_id'] ) )->find();

					if( !empty($mb_info['content']) )
					{
						$orderInfo .= '客户备注：'.$mb_info['content'].''."<BR>";
					}
				}

					$is_print_order_note = D('Home/Front')->get_config_by_name('is_print_order_note');
					$order_note_open = D('Home/Front')->get_config_by_name('order_note_open');
					$order_note_name = D('Home/Front')->get_config_by_name('order_note_name');
					if( !empty($is_print_order_note) && $is_print_order_note == 1 )
					{
						if( !empty($order_info['note_content']) )
						{
							if( !empty($order_note_open) &&  !empty($order_note_name))
							{
								$orderInfo .= $order_note_name.'：'.$order_info['note_content'].''."<BR>";
							}else{
								$orderInfo .= '自定义备注：'.$order_info['note_content'].''."<BR>";
							}
						}
					}
				//end pr
				if($order_info['order_status_id'] == 7){
						$orderInfo .= '--------------------------------<BR>';
						$orderInfo .= '<L>订单已退款</<L><BR>';

				}
				$orderInfo .= '<CB>**#'.$last_print_index.'  完**</CB><BR>';


				//$feier_print_lian = D('Home/Front')->get_config_by_name('feier_print_lian');

				if( empty($printer_num) ||  $printer_num < 1)
				{
					$printer_num = 1;
				}

				$print_result = $this->wp_print($orderInfo, $printer_num, $printer_sn);


				if( $print_result['code'] == 0)
				{
					M('eaterplanet_ecommerce_order')->where( array('order_id' =>$order_id ) )->save( array('is_print_suc' => 0) );
				}

				return $print_result;
		}



}


?>
