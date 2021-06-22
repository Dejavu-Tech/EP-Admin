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

class PaymentController extends CommonController {

	protected function _initialize()
    {
    	parent::_initialize();
	}

	//客户中心页面，去付款
	function confirm_pay(){
		if(I('token')!=md5(session('pay_token'))){
			$url=U('/checkout');
			@header("Location: ".$url);
			die();
		}
		$order=M('order')->where(array('order_id'=>get_url_id('id')))->find();

		$data['notify_url']=C('SITE_URL').U('Payment/alipay_notify');
		$data['return_url']=C('SITE_URL').U('Payment/alipay_return');
		$data['order_type']='goods_buy';
		$data['subject']='购买商品';
		$data['name']=$order['shipping_name'];
		$data['pay_order_no']=$order['order_num_alias'];
		$data['pay_total']=(float)$order['total'];

		storage_user_action(session('user_auth.uid'),session('user_auth.username'),C('FRONTEND_USER'),'点击了去支付订单 '.$order['order_num_alias']);

		$url=$this->pay_api($order['payment_code'], $data);

		@header("Location: ".$url);

		die();
	}

	/**
		接受快递鸟通知
	**/
	function kuaidiniao()
	{

		echo json_encode(array('Success'=>true));

		$request_data = I('post.RequestData');

		$request_data = htmlspecialchars_decode($request_data);

		$request_data_arr = json_decode($request_data,true);

		foreach($request_data_arr['Data'] as $data)
		{
			$LogisticCode = $data['LogisticCode'];
			$trace = serialize($data['Traces']);
			M('order')->where( array('shipping_no' => $LogisticCode) )->save( array('shipping_traces' => $trace) );
		}


	}

	/**
	 * 提交订单支付
	 * @param unknown $order_id
	 */

	function order_pay()
	{
	   $order_id =  I('get.order_id',0);
	   if($order_id > 0)
	   {
	       $order = M('order')->where( array('order_id' => $order_id) )->find();


	       if($order['order_status_id'] == 3)
	       {
			   if($order['total'] <= 0)
			   {
				   $this->yuer_payreturn($order['order_num_alias']);
			   } else {
				    $order['payment_code'] = empty($order['payment_code']) ? 'wxpay': $order['payment_code'];

					//单独支付一个店铺的订单
					M('order_relate')->where( array('order_id' => $order_id) )->delete();

					$order_all_data = array();
					$order_all_data['member_id'] = session('user_auth.uid');
					$order_all_data['order_num_alias'] = build_order_no(session('user_auth.uid'));;
					$order_all_data['transaction_id'] = '';
					$order_all_data['order_status_id'] = 3;
					$order_all_data['is_pin'] = $order['is_pin'];
					$order_all_data['paytime'] = 0;
					$order_all_data['total_money'] = $order['total'];
					$order_all_data['addtime'] = time();

					$order_all_id = M('order_all')->add($order_all_data);

					$order_relate_data = array();
					$order_relate_data['order_all_id'] = $order_all_id;
					$order_relate_data['order_id'] = $order_id;
					$order_relate_data['addtime'] = time();
					M('order_relate')->add($order_relate_data);


					$wxpay_url = C('SITE_URL')."index.php?s=/Payment/wxpay_order/pay_order_no/{$order_all_data[order_num_alias]}";
					header('Location: '.$wxpay_url);
					die();
			   }

	       }
	   }
	}


	/**
	 * $pay_type 购买商品，还是预存款
	 * $order 订单信息
	 */
	function pay_api($payment_method,$order_all_id){

		$order_all = M('order_all')->where( array('id' => $order_all_id) )->find();

		if($payment_method=='alipay'){

			$alipay= new \Lib\Payment\Alipay(get_payment_config('alipay'),$order_all);
			return $alipay->get_payurl();
		}
		//wxpay
		if($payment_method == 'wxpay')
		{
			$wxpay_url = C('SITE_URL')."index.php?s=/Payment/wxpay_order/pay_order_no/{$order_all[order_num_alias]}";
			echo json_encode( array('code' =>1 ,'url' => $wxpay_url) );
			die();
			//header('Location: '.$wxpay_url);
			//$this->redirect( 'Payment/wxpay_order',array('pay_order_no' => $order['order_num_alias']) );
		}
	}

	//微信支付通知
	function weixin_notify()
	{
		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
		$data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";

		require_once $lib_path."/Weixin/PayNotifyCallBack.class.php";

		$notify = new \PayNotifyCallBack();
		$notify->Handle(false);

	}

	//开始微信支付订单
	function wxpay_order()
	{
		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';
		$data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";

		RecursiveMkdir($data_path);

		require_once $lib_path."/Weixin/JsApiPay.class.php";


		$pay_order_no = I('get.pay_order_no',0);

		$order = M('order_all')->where(array('order_num_alias'=>$pay_order_no, 'member_id' => is_login() ))->find();

		if(empty($order)) {
			$this->redirect( U('Index/index') );
		}

		//初始化日志
		$logHandler= new \CLogFileHandler( $data_path .date('Y-m-d').'.log');

		$log = \Log::Init($logHandler, 15);

		$member_info = M('member')->where( array('member_id' => $order['member_id']) )->find();

		//①、获取用户openid
		$tools = new  \JsApiPay();
		//$order['total'] = 0.01;
		//②、统一下单
		$input = new \WxPayUnifiedOrder();
		$input->SetBody(mb_substr('商品购买', 0, 30, 'utf-8'));
		$input->SetAttach(mb_substr('商品购买', 0, 30, 'utf-8'));
		$input->SetOut_trade_no($order['id'].'-'.time());
		$input->SetTotal_fee( ( $order['total_money'] *100) );
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("");

		$notify_url = C('SITE_URL').'notify.php';

		$input->SetNotify_url($notify_url);

		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($member_info['openid']);


		$order_str = \WxPayApi::unifiedOrder($input);

		$jsApiParameters = $tools->GetJsApiParameters($order_str);
		//var_dump($jsApiParameters);die();
		//获取共享收货地址js函数参数
		$editAddress = $tools->GetEditAddressParameters();

		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		//$order_id = $hashids->encode($order['id']);

		if($order['is_pin'] == 1)
		{
			$order_relate =  M('order_relate')->where( array('order_all_id' => $order['id']) )->find();
			$order_id = $hashids->encode($order_relate['order_id']);

			$refurl = C('SITE_URL')."index.php?s=/Group/info/group_order_id/{$order_id}/is_show/1";
		} else {
			$refurl = C('SITE_URL')."index.php?s=/order/order_all_info/id/{$order[id]}";
		}
		$backurl = C('SITE_URL')."index.php?s=/order/order_all_info/id/{$order[id]}";

		$this->order_id = $order['id'];


		$this->refurl = $refurl;
		$this->backurl = $backurl;
		$this->jsApiParameters = $jsApiParameters;
		$this->editAddress = $editAddress;

		$this->display();

	}

	//写入订单
	function pay(){
	    $json=array();

	//pickup express
	    $pay_method = I('post.pay_method');//支付类型
	    $order_msg_str = I('post.order_msg_str');//商品订单留言
		$quan_arr = I('post.quan_arr');//使用优惠券

		//express_method:express_method,pick_mobile:pick_mobile,pick_id:pick_id,pick_name:pick_name
		$pick_up_id = I('post.pick_id');//$data_s['pick_up_id'];
		$dispatching = I('post.dispatching');//$data_s['dispatching'];
		$ziti_name = I('post.pick_name');//$data_s['ziti_name'];
		$ziti_mobile = I('post.pick_mobile');//$data_s['ziti_mobile'];

	    $order_msg_arr = explode('@,@', $order_msg_str);

	    $msg_arr = array();
	    foreach($order_msg_arr as $val)
	    {
	        $tmp_val = explode('@_@', $val);
	        $msg_arr[ $tmp_val[0] ] = $tmp_val[1];
	    }

		$member_id = session('user_auth.uid');
		$order_quan_arr = array();

		if( !empty($quan_arr) )
		{
			foreach($quan_arr as $q_val)
			{
				$tmp_q = array();
				$tmp_q = explode('_',$q_val);

				$voucher_info =  M('voucher_list')->where( array('id' =>$tmp_q[1],
				'store_id' =>$tmp_q[0], 'user_id' => session('user_auth.uid'),'consume' =>'N','end_time' => array('gt',time() ) ) )->find();

				if( !empty($voucher_info) )
				{
					$order_quan_arr[$tmp_q[0]] = $tmp_q[1];
				}
			}

		}



	    $cart=new \Lib\Cart();

	    // 验证商品数量

	    $buy_type = I('post.buy_type');

	    $is_pin = 0;
	    if($buy_type == 'pin')
	    {
	        $is_pin = 1;
	    }
	    $goodss = $cart->get_all_goods($buy_type,1);

		$seller_goodss = array();
		$del_integral = 0;

		foreach($goodss as $key => $val)
		{
			if($buy_type == 'dan')
			{
				$new_key = 'cart.'.$key;
				$s = session($new_key);


				if( isset($s['can_del']) && $s['can_del'] == 1)
				{
					$cart->remove($key);
				}else if(isset($s['old_quantity'])){

					$s['quantity'] = $s['old_quantity'];

					session($new_key,$s);
				}
			}
			$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();
			$seller_goodss[ $goods_store_field['store_id'] ][$key] = $val;
		}


	    //付款人
	    $payment=M('Member')->find(session('user_auth.uid'));

	    //收货人
	    $add_where = array('member_id'=>session('user_auth.uid'));
	    $address = M('address')->where( $add_where )->order('is_default desc,address_id desc')->find();

		$pay_total = 0;
		//M('order_all')
		$order_all_data = array();
		$order_all_data['member_id'] = session('user_auth.uid');
		$order_all_data['order_num_alias'] = build_order_no(session('user_auth.uid'));;
		$order_all_data['transaction_id'] = '';
		$order_all_data['order_status_id'] = 3;
		$order_all_data['is_pin'] = $is_pin;
		$order_all_data['paytime'] = 0;

		$order_all_data['addtime'] = time();

		$order_all_id = M('order_all')->add($order_all_data);


		$integral_model = D('Seller/Integral');

		foreach($seller_goodss as $kk => $vv)
		{
			$data = array();

			$data['member_id']=session('user_auth.uid');
			$data['name']=session('user_auth.username');

			$data['telephone']=$address['telephone'];

			$data['shipping_name']=$address['name'];
			$data['shipping_address']=$address['address'];
			$data['shipping_tel']=$address['telephone'];

			$data['shipping_province_id']=$address['province_id'];
			$data['shipping_city_id']=$address['city_id'];
			$data['shipping_country_id']=$address['country_id'];

			$data['shipping_method'] = 0;

			$data['delivery']=$dispatching;
			$data['pick_up_id']=$pick_up_id;
			$data['ziti_name']=$ziti_name;
			$data['ziti_mobile']=$ziti_mobile;


			$data['payment_method']=$pay_method;

			$data['address_id']= $address['address_id'];
			//quan_arr
			$data['voucher_id'] = isset($order_quan_arr[$kk]) ? $order_quan_arr[$kk]:0;

			$data['user_agent']=$_SERVER['HTTP_USER_AGENT'];
			$data['date_added']=time();

			$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
			$subject='';
			$fare = 0;

			$goodss = $vv;

			$trans_free_toal = 0;//运费

			$goods_data = array();
			$order_total = 0;
			$is_lottery = 0;
			$is_integral = 0;

			foreach($goodss as $key => $good)
			{
				if($good['shipping']==1)
				{
					//统一运费
					$trans_free_toal += $good['goods_freight'];
					$trans_free = $good['goods_freight'];
				}else {
					//运费模板
					$trans_free = D('Home/Transport')->calc_transport($good['transport_id'], $good['quantity']*$good['weight'], $address['city_id'] );
					$trans_free_toal +=$trans_free;
				}
				$goods_info = explode(':', $key);

				$goods_id = $goods_info[0];



				if (!empty($goods_info[1])) {
					$options = base64_decode($goods_info[1]);
				} else {
					$options = '';
				}
				$order_total += $good['total'];


				$tp_goods_info = M('goods')->field('store_id,type')->where( array('goods_id' =>$good['goods_id'] ) )->find();

				if($tp_goods_info['type'] == 'lottery')
				{
					$is_lottery = 1;
				}

				if($tp_goods_info['type'] == 'integral')
				{
					$is_integral = 1;
					$is_pin = 0;
					$check_result = $integral_model->check_user_score_can_pay($member_id, $good['sku_str'], $good['goods_id'] );
					if($check_result['code'] == 1)
					{
						die();
					}
				}

				$goods_data[] = array(
					'goods_id'   => $good['goods_id'],
					'store_id' => $tp_goods_info['store_id'],
					'name'       => $good['name'],
					'model'      => $good['model'],
					'is_pin' => $is_pin,
					'pin_id' => $good['pin_id'],
					'header_disc' => $good['header_disc'],
					'option'     => $options,
					'quantity'   => $good['quantity'],
					'shipping_fare' => $trans_free,
					'price'      => $good['price'],
					'total'      => $good['total'],
					'comment' => htmlspecialchars($msg_arr[$key])
				);

			}
			$data['type'] = 'normal';
			if($is_pin == 1)
			{
				$data['type'] = 'pintuan';
				if($is_lottery == 1)
				{
					$data['type'] = 'lottery';
				}
			}
			if($is_integral == 1)
			{
				$data['type'] = 'integral';
				$is_pin = 0;
			}

			$data['shipping_fare'] = floatval($trans_free_toal);

			$data['store_id']= $kk;

		   // $tp_goods_info['store_id'],




			$data['goodss'] = $goods_data;
			$data['order_num_alias']=build_order_no($kk);

			$data['totals'][0]=array(
				'code'=>'sub_total',
				'title'=>'商品价格',
				'text'=>'¥'.$order_total,
				'value'=>$order_total
			);
			$data['totals'][1]=array(
				'code'=>'shipping',
				'title'=>'运费',
				'text'=>'¥'.$trans_free_toal,
				'value'=>$trans_free_toal
			);

			$data['totals'][2]=array(
				'code'=>'total',
				'title'=>'总价',
				'text'=>'¥'.($order_total+$trans_free_toal),
				'value'=>($order_total+$trans_free_toal)
			);


			if($data['voucher_id'] > 0) {
				$voucher_info = M('voucher_list')->where( array('id' => $data['voucher_id']) )->find();
				$data['voucher_credit'] = $voucher_info['credit'];
				M('voucher_list')->where( array('id' => $data['voucher_id']) )->save( array('consume' => 'Y') );
			} else {
				$data['voucher_credit'] = 0;
			}

			$data['total']=($order_total+$trans_free_toal- $data['voucher_credit'] );

			//积分商城
			if($data['type'] == 'integral')
			{
				$del_integral += $order_total;//扣除积分
				$data['total'] = 0;
				$order_total = 0;
			}
			$oid=D('Order')->addOrder($data);

			if($data['delivery'] == 'pickup')
			{
				$verify_bool = true;
				$verifycode = 0;
				while($verify_bool)
				{
					$code  = (ceil(time()/100)+rand(10000000,40000000)).rand(1000,9999);
					$verifycode = $code ? $code : rand(100000,999999);
					$verifycode = str_replace('1989','9819',$verifycode);
					$verifycode = str_replace('1259','9521',$verifycode);
					$verifycode = str_replace('12590','95210',$verifycode);
					$verifycode = str_replace('10086','68001',$verifycode);

					$pick_order = M('pick_order')->where( array('pick_sn' => $verifycode) )->find();
					if(empty($pick_order))
					{
						$verify_bool = false;
					}
				}
				$pick_data = array();
				$pick_data['pick_sn'] = $verifycode;
				$pick_data['pick_id'] = $pick_up_id;
				$pick_data['order_id'] = $oid;
				$pick_data['state'] = 0;

				$pick_data['ziti_name'] = $ziti_name;
				$pick_data['ziti_mobile'] = $ziti_mobile;


				$pick_data['addtime'] = time();
				M('pick_order')->add($pick_data);
			}
			$pay_total = $pay_total + $order_total+$trans_free_toal- $data['voucher_credit'];


			$order_relate_data = array();
			$order_relate_data['order_all_id'] = $order_all_id;
			$order_relate_data['order_id'] = $oid;
			$order_relate_data['addtime'] = time();

			M('order_relate')->add($order_relate_data);
		}
		M('order_all')->where( array('id' => $order_all_id) )->save( array('total_money' => $pay_total) );



	        if($order_all_id){
	            //session('cart_total',null);
	            $order['notify_url']=C('SITE_URL').U('Payment/alipay_notify');
	            $order['return_url']=C('SITE_URL').U('Payment/alipay_return');
	            $order['order_type']='goods_buy';
	            $order['subject']=$subject;
	            $order['name']=session('shipping_name');
	            $order['order_num_alias']=$data['order_num_alias'];
	            $order['pay_total']=($order_total+$trans_free_toal);
	            //free_tuan

	            //session('back_cart_address_id',null);
	            //session('cart',null);
	            //session('total',null);
	            //session('shipping_address_id',null);
	            //session('back_order_id',$oid);

	            if($data['total']<=0 )
	            {
					//检测是否需要扣除积分
					//var_dump($del_integral,$is_integral );die();
					if($del_integral> 0 && $is_integral == 1)
					{
						//
						$integral_model->charge_member_score( $member_id, $del_integral,'out', 'orderbuy', $oid);
					}
	                $this->yuer_payreturn($oid); //测试使用
	            }else {
	                $url=$this->pay_api('wxpay', $order_all_id);
	            }

	            die();
	        }else{

	            echo 9999;die();
	            $url=U('/checkout');
	            @header("Location: ".$url);

	            die();
	        }



	}
	//写入订单
	function pay2(){
		$json=array();
		if(I('token')!=md5(session('token'))){
			$url=U('/checkout');
			@header("Location: ".$url);
			die();
		}

		$cart=new \Lib\Cart();

		// 验证商品数量
		$goodss = $cart->get_all_goods();

		//付款人
		$payment=M('Member')->find(session('user_auth.uid'));

		//收货人
		$shipping=M('Address')->find(session('shipping_address_id'));

		$data['member_id']=session('user_auth.uid');
		$data['name']=session('user_auth.username');

		$data['telephone']=$payment['telephone'];

		$data['shipping_name']=$shipping['name'];
		$data['shipping_address']=$shipping['address'];
		$data['shipping_tel']=$shipping['telephone'];

		$data['shipping_province_id']=$shipping['province_id'];
		$data['shipping_city_id']=$shipping['city_id'];
		$data['shipping_country_id']=$shipping['country_id'];

		$data['shipping_method'] = session('express_id');
		$data['delivery']=session('shipping_method');


		$data['payment_method']=session('payment_method');

		$data['address_id']=session('shipping_address_id');

		$data['voucher_id']=session('payment_voucher_id');
		//payment_voucher_id

		$data['user_agent']=$_SERVER['HTTP_USER_AGENT'];
		$data['date_added']=time();
		$data['comment']=session('remark');
		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		$subject='';
		$fare = 0;
		if($goodss){

				/**
				$sm=D('Transport')->calc_transport(session('shipping_method'),
				session('quantity'),
				$shipping['city_id']
				);
				**/

				$sm = D('Transport')->calc_transport(null,
				session('quantity'),
				$shipping['city_id']
				);

				$t=0;
				foreach ($goodss as $goods) {

					$option_data = array();

					foreach ($goods['option'] as $option) {

						$value = $option['value'];

						$option_data[] = array(
							'goods_option_id'       => $option['goods_option_id'],
							'goods_option_value_id' => $option['goods_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $value,
							'type'                    => $option['type']
						);
					}

					$tp_goods_info = M('goods')->field('store_id,express_list')->where( array('goods_id' =>$hashids->decode($goods['goods_id'])) )->find();

					$express_list_arr = unserialize($tp_goods_info['express_list']);

					if($data['delivery'] == 'express')
					{
						$fare = isset($express_list_arr[$data['shipping_method']]) ? $express_list_arr[$data['shipping_method']]['price'] : 0;
					}

					$t+=$goods['total'];

					$goods['total'] = $goods['total'];

					$goods_data[] = array(
						'goods_id'   => $goods['goods_id'],
						'name'       => $goods['name'],
					    'is_header_disc' => $goods['is_header_disc'],
					    'store_id'   => $tp_goods_info['store_id'],
						'model'      => $goods['model'],
						'option'     => $option_data,
						'quantity'   => $goods['quantity'],
						'pin_type'   => $goods['pin_type'],
					    'pin_id'     => $goods['pin_id'],
						'price'      => $goods['price'],
						'total'      => $goods['total']
					);

					$subject.=$goods['name'].' ';

					}

					if($data['voucher_id'] > 0) {
						$voucher_info = M('voucher_list')->where( array('id' => $data['voucher_id']) )->find();
						$data['voucher_credit'] = $voucher_info['credit'];
						M('voucher_list')->where( array('id' => $data['voucher_id']) )->save( array('consume' => 'Y') );
					} else {
						$data['voucher_credit'] = 0;
					}

					$data['shipping_fare'] = floatval($fare);

					$data['total']=($t+$fare - $data['voucher_credit']);
					$data['goodss'] = $goods_data;
					$data['order_num_alias']=build_order_no($data['member_id']);

					$data['totals'][0]=array(
						'code'=>'sub_total',
						'title'=>'商品价格',
						'text'=>'¥'.$t,
						'value'=>$t
					);
					$data['totals'][1]=array(
						'code'=>'shipping',
						'title'=>'运费',
						'text'=>'¥'.$fare,
						'value'=>$fare
					);
					$data['totals'][2]=array(
						'code'=>'voucher',
						'title'=>'优惠券',
						'text'=>'¥'.$data['voucher_credit'],
						'value'=>$data['voucher_credit']
					);
					$data['totals'][3]=array(
						'code'=>'total',
						'title'=>'总价',
						'text'=>'¥'.($t+$fare- $data['voucher_credit']),
						'value'=>($t+$fare- $data['voucher_credit'])
					);


				$oid=D('Order')->addOrder($data);

				//delivery  pickup pick_up_id
				if($data['delivery'] == 'pickup')
				{
					$verify_bool = true;
					$verifycode = 0;
					while($verify_bool)
					{
						$code  = (ceil(time()/100)+rand(10000000,40000000)).rand(1000,9999);
						$verifycode = $code ? $code : rand(100000,999999);
						$verifycode = str_replace('1989','9819',$verifycode);
						$verifycode = str_replace('1259','9521',$verifycode);
						$verifycode = str_replace('12590','95210',$verifycode);
						$verifycode = str_replace('10086','68001',$verifycode);

						$pick_order = M('pick_order')->where( array('pick_sn' => $verifycode) )->find();
						if(empty($pick_order))
						{
							$verify_bool = false;
						}
					}
					$pick_data = array();
					$pick_data['pick_sn'] = $verifycode;
					$pick_data['pick_id'] = session('pick_up_id');
					$pick_data['order_id'] = $oid;
					$pick_data['state'] = 0;
					$pick_data['addtime'] = time();
					M('pick_order')->add($pick_data);
				}
				if($oid){
					session('cart_total',null);
					$order['notify_url']=C('SITE_URL').U('Payment/alipay_notify');
					$order['return_url']=C('SITE_URL').U('Payment/alipay_return');
					$order['order_type']='goods_buy';
					$order['subject']=$subject;
					$order['name']=session('shipping_name');
					$order['order_num_alias']=$data['order_num_alias'];
					$order['pay_total']=($t+$sm['price']);
					//free_tuan
					$order_goods_info = M('order_goods')->where( array('order_id' => $oid) )->find();

					session('back_cart_address_id',null);
					session('cart',null);
					session('total',null);
					session('shipping_address_id',null);
					session('back_order_id',$oid);

					if($data['total']<=0 || $order_goods_info['free_tuan'] ==1)
					{
					    $this->yuer_payreturn($data['order_num_alias']); //测试使用
					}else {
					    $url=$this->pay_api('wxpay', $order);
					}

					die();
				}else{

				    echo 9999;die();
					$url=U('/checkout');
					@header("Location: ".$url);

					die();
				}

			}

	}

	public function success()
	{
	    $order_id = I('get.order_id');
	    //removeAll
	    $cart=new \Lib\Cart();
	    $cart->removeAll();
	    session('total',null);

	    $pin_order = M('pin_order')->field('pin_id')->where( array('order_id' =>$order_id) )->find();

	    //Order/info/id/14

	    $redir_url = U('Order/info', array('id' => $order_id));
	    if(!empty($pin_order))
	    {
	        $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	        $group_order_id = $hashids->encode($order_id);

	        $redir_url = U('Group/info', array('group_order_id' => $group_order_id,'is_show' =>1));
			redirect($redir_url,0);
	    }
	    $this->redir_url = $redir_url;

	    $this->order_id = $order_id;
	    $this->display();
	}

	function de_bug($content){
		$file = ROOT_PATH."/Tmp/wxpay_debug.php";
		file_put_contents($file,$content);
	}

	//数据以post方式返回
	function alipay_notify(){

		$alipay= new \Lib\Payment\Alipay(get_payment_config('alipay'));

		$verify_result = $alipay->verifyNotify();

		if($verify_result) {

			//$this->de_bug('success');

			//商户订单号
			//$out_trade_no = $_POST['out_trade_no'];
			//支付宝交易号
			//$trade_no = $_POST['trade_no'];
			//交易状态
			//$trade_status = $_POST['trade_status'];

			if($_POST['trade_status'] == 'TRADE_FINISHED') {
				//$this->de_bug('TRADE_FINISHED');

		    }
		    else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
				//$this->de_bug('TRADE_SUCCESS');

				$order=M('Order')->getByOrderNumAlias($_POST['out_trade_no']);

				if($order&&($order['order_status_id']!=C('paid_order_status_id'))){
						//支付完成
						$o['order_id']=$order['order_id'];
						$o['order_status_id']=C('paid_order_status_id');
						$o['date_modified']=time();
						$o['pay_time']=time();
						M('Order')->save($o);

						$oh['order_id']=$order['order_id'];
						$oh['order_status_id']=C('paid_order_status_id');

						$oh['comment']='买家已付款';
						$oh['date_added']=time();
						$oh['notify']=1;
						M('OrderHistory')->add($oh);

						$model=new \Admin\Model\OrderModel();
					    $this->order=$model->order_info($order['order_id']);
					    $html=$this->fetch('Mail:order');
					    think_send_mail($order['email'],$order['name'],'下单成功-'.C('SITE_NAME'),$html);

						storage_user_action(session('user_auth.uid'),session('user_auth.username'),C('FRONTEND_USER'),'支付了订单 '.$order['order_num_alias']);

						//@header("Location: ".U('/pay_success'));

				}


				echo "success";
		    }



		}else{
			//$this->de_bug('fail');
			echo "fail";
		}

	}

	private function yuer_payreturn($order_id){
	    // $order=M('Order')->getByOrderNumAlias($out_trade_no);
	    $order=M('Order')->where( array('order_id' => $order_id) )->find();
	    $out_trade_no = $order['order_num_alias'];

	    if($order['order_status_id']==C('paid_order_status_id')){
	        @header("Location: ".U('/pay_success'));
	        die;
	    }

	    if($order&&($order['order_status_id']!=C('paid_order_status_id'))){
	        //支付完成
	        if(true){



	            $goods_model = D('Home/Goods');

	            $kucun_method = C('kucun_method');
	            $kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);
	            if($kucun_method == 1)
	            {//支付完减库存，增加销量
	                $order_goods_list = M('order_goods')->where( array('order_id' => $order['order_id']) )->select();
	                foreach($order_goods_list as $order_goods)
	                {
	                    //销量增加 del_goods_mult_option_quantity($order_id,$option,$goods_id,$quantity,$type='1')
	                    $goods_model->del_goods_mult_option_quantity($order['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],1);
	                    //扣除库存
	                }
	            }

	            $oh['order_id']=$order['order_id'];
	            $oh['order_status_id'] = 1;
	            $oh['comment']='买家已付款';
	            $oh['date_added']=time();
	            $oh['notify']=0;
	            M('OrderHistory')->add($oh);

	            $pin_order = M('pin_order')->where( array('order_id' =>$order['order_id'] ) )->find();

	            if(!empty($pin_order))
	            {
					$o = array();
					$o['order_id']=$order['order_id'];
					$o['order_status_id']= 2;
					$o['date_modified']=time();
					$o['pay_time']=time();
					M('Order')->save($o);
	                //group_order_id
	                $pin_model = D('Home/Pin');
	                $is_pin_success = $pin_model->checkPinSuccess($pin_order['pin_id']);

	                if($is_pin_success) {
	                    //todo send pintuan success notify
	                    $pin_model->updatePintuanSuccess($pin_order['pin_id']);

	                }else{

					}
	            }else{
					$o = array();
					$o['order_id']=$order['order_id'];
					$o['order_status_id']= 1;
					$o['date_modified']=time();
					$o['pay_time']=time();
					M('Order')->save($o);
					$share_model = D('Seller/Fissionsharing');
					$share_model->send_order_commiss_money( $order['order_id'] );
				}

	            $result = array('code' => 1, 'url' => U('Payment/success',array('order_id' => $order['order_id'])));

	            echo json_encode($result);
	            die();

	        }
	    }
	}


	private function yuer_payreturn2($out_trade_no){
	    $order=M('Order')->getByOrderNumAlias($out_trade_no);

	    if($order['order_status_id']==C('paid_order_status_id')){
	        @header("Location: ".U('/pay_success'));
	        die;
	    }

	    if($order&&($order['order_status_id']!=C('paid_order_status_id'))){
	        //支付完成
	        if(true){


	            $o['order_id']=$order['order_id'];
	            $o['order_status_id']= $order['is_pin'] == 1 ? 2:1;
	            $o['date_modified']=time();
	            $o['pay_time']=time();
	            M('Order')->save($o);

				$goods_model = D('Home/Goods');

				$kucun_method = C('kucun_method');
				$kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);
				if($kucun_method == 1)
				{//支付完减库存，增加销量
					$order_goods_list = M('order_goods')->where( array('order_id' => $order['order_id']) )->select();
					foreach($order_goods_list as $order_goods)
					{
						//销量增加 rela_goodsoption_valueid
						$goods_model->del_goods_mult_option_quantity($order['order_id'],$order_goods['quantity'],1);
						//扣除库存
					}
				}

	            $oh['order_id']=$order['order_id'];
	            $oh['order_status_id']= $order['is_pin'] == 1 ? 2:1;
	            $oh['comment']='买家已付款';
	            $oh['date_added']=time();
	            $oh['notify']=1;
	            M('OrderHistory')->add($oh);

	            //发送购买通知
	            $weixin_nofity = D('Home/Weixinnotify');
	            $weixin_nofity->orderBuy($order);

	            $model=new \Admin\Model\OrderModel();
	            $this->order=$model->order_info($order['order_id']);

	            $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	            $order_id = $hashids->encode($order['order_id']);

	            if($order['is_pin'] == 1)
	            {
	                $pin_model = D('Home/Pin');
	                $is_pin_success = $pin_model->checkPinSuccess($order['pin_id']);

	                if($is_pin_success) {
	                    //todo send pintuan success notify
	                    $pin_model->updatePintuanSuccess($order['pin_id']);
	                }
	                $refurl = C('SITE_URL')."index.php?s=/Group/info/group_order_id/{$order_id}/is_show/1";
	                @header("Location: ".$refurl);
	                die();
	            } else {
	                @header("Location: ".U('Order/info',array('id' =>$order['order_id'] )));
	                die();
	            }

	        }
	    }
	}

	function alipay_return(){

		$alipay= new \Lib\Payment\Alipay(get_payment_config('alipay'));

		//对进入的参数进行远程数据判断
		$verify = $alipay->return_verify();

		if($verify){
			$order=M('Order')->getByOrderNumAlias($_GET['out_trade_no']);

			if($order['order_status_id']==C('paid_order_status_id')){
				@header("Location: ".U('/pay_success'));
				die;
			}

			if($order&&($order['order_status_id']!=C('paid_order_status_id'))){
				//支付完成
				if($_GET['trade_status']=='TRADE_SUCCESS'){

					$o['order_id']=$order['order_id'];
					$o['order_status_id']=C('paid_order_status_id');
					$o['date_modified']=time();
					$o['pay_time']=time();
					M('Order')->save($o);

					$oh['order_id']=$order['order_id'];
					$oh['order_status_id']=C('paid_order_status_id');

					$oh['comment']='买家已付款';
					$oh['date_added']=time();
					$oh['notify']=1;
					M('OrderHistory')->add($oh);

					$model=new \Admin\Model\OrderModel();
				    $this->order=$model->order_info($order['order_id']);
				    $html=$this->fetch('Mail:order');
				    think_send_mail($order['email'],$order['name'],'下单成功-'.C('SITE_NAME'),$html);

					storage_user_action(session('user_auth.uid'),session('user_auth.username'),C('FRONTEND_USER'),'支付了订单 '.$order['order_num_alias']);

					@header("Location: ".U('/pay_success'));
				}
			}else{
				die('订单不存在');
			}

		}else{
			die('支付失败');
		}

	}
}
