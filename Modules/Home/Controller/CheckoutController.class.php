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

class CheckoutController extends CommonController {
	//步骤1：结算选项
	function login(){

		$this->display();
	}
	//验证登录
	function validate_login(){
		//是否已经登录
		$json=array();
		if (is_login()) {
			$json['redirect'] = U('/checkout');
		}

		if (!$json) {
			$d=I('post.');
			$user=M('Member')->getByUname($d['uname']);
			if(!$user){
				$user=M('Member')->getByEmail($d['uname']);
			}
			//用户存在且可用
			if(!($user&&$user['status']==1)){
				$json['error']['warning']='用户不存在或被禁用！！';
			}

			if(think_ucenter_encrypt($d['password'],C('PWD_KEY'))!=$user['pwd']){
				$json['error']['warning']='密码错误！！';
			}
		}
		if (!$json) {
			 $auth = array(
			            'uid'             => $user['member_id'],
			            'username'        => $user['uname'],
						);

		    session('user_auth', $auth);

    		session('user_auth_sign', data_auth_sign($auth));

	        $data = array();
	        $data['member_id']	=	$user['member_id'];
	        $data['last_login_time']	=	time();

	        $data['login_count']		=	array('exp','login_count+1');
			$data['last_login_ip']	=	get_client_ip();

	        M('Member')->save($data);

			storage_user_action($user['member_id'],$user['uname'],C('FRONTEND_USER'),'登录了网站');

			if($user['address_id']!=0){
				session('shipping_address_id',$user['address_id']);
			}

				//是否有货
			$cart=new \Lib\Cart();
			if ((!$cart->has_goods()) ) {
				$json['redirect'] = U('/cart');
				$this->ajaxReturn($json);
				die;
			}

			$json['redirect'] = U('/checkout');
		}

		$this->ajaxReturn($json);
		die();
	}

	//选择是注册购买还是不注册购买
	function user(){

		if(I('u')=='register'){

			$this->province=M('area')->where('area_parent_id=0')->select();

			$this->display('register');
		}
		if(I('u')=='guest'){

		}

	}
	//用户注册验证写入
	function register(){
		if(IS_POST){
			//未登录
			$json=array();
			if(!is_login()){

				//验证是否有货
				$cart=new \Lib\Cart();
				if ((!$cart->has_goods())) {
					$json['redirect'] = U('/cart');
				}
				//验证最小商品数量
				$products = $cart->get_all_goods();

				foreach ($products as $product) {
					$product_total = 0;

					foreach ($products as $product_2) {
						if ($product_2['goods_id'] == $product['goods_id']) {
							$product_total += $product_2['quantity'];
						}
					}

					if ($product['minimum'] > $product_total) {
						$json['redirect'] =U('/cart');

						break;
					}
				}

				if (!$json) {
					$d=I('post.');
					if ((utf8_strlen($d['uname']) <= 1) || (utf8_strlen($d['uname']) > 20)) {
						$json['error']['uname'] = '用户名长度必须大于1,小于20位！！';
					}

					if ((utf8_strlen($d['name']) <= 1) || (utf8_strlen($d['name']) > 20)) {
						$json['error']['name'] = '性名长度必须大于1,小于20位！！';
					}

					if (M('Member')->getByUname($d['uname'])) {
						$json['error']['uname'] = '用户名已经存在！！';
					}

					if(empty($d['email'])){
						$json['error']['email'] = 'email必填！！';
					}

					if(!empty($d['email'])){
						if ((utf8_strlen($d['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $d['email'])) {
							$json['error']['email'] = 'email格式错误！！';
						}
						if (M('Member')->getByEmail($d['email'])) {
							$json['error']['email'] = 'email已经存在！！';
						}
					}

					if ((utf8_strlen($d['telephone']) < 3) || (utf8_strlen($d['telephone']) > 32)) {
						$json['error']['telephone'] = '电话长度错误！！';
					}

					if ((utf8_strlen($d['address']) < 3) || (utf8_strlen($d['address']) > 128)) {
						$json['error']['address'] = '地址长度错误！！';
					}

					if ((utf8_strlen($d['password']) < 4) || (utf8_strlen($d['password']) > 20)) {
						$json['error']['password'] = '密码长度错误！！';
					}

					if ($d['confirm'] != $d['password']) {
						$json['error']['confirm'] = '两次密码输入不一致！！';
					}

					if($d['province_id']==-1){
						$json['error']['area'] = '请选择省份！！';
					}
					if($d['city_id']==-1){
						$json['error']['area'] = '请选择城市！！';
					}
				}

				if (!$json) {
					$uid=D('Member')->add_member();

					 $auth = array(
			            'uid'             => $uid,
			            'username'        => $d['uname'],
					 );

					storage_user_action($uid,$d['uname'],C('FRONTEND_USER'),'注册成为客户');

					$email_content='您好,感谢您注册成为'.C('SITE_NAME').'客户<br />'.
					'您的账号是 '.$d['uname'].'<br />'.
					'邮箱是 '.$d['email'].'<br />'.
					'密码是 '.$d['password'].'<br />'.
					'您可以使用账号或者邮箱来进行网站的登录<a href="'.C('SITE_URL').U('/login').'">点此进行登录</a>';

					//发送邮件
					think_send_mail($d['email'],$d['uname'],C('SITE_NAME').'客户注册成功',$email_content);


				    session('user_auth', $auth);
		    		session('user_auth_sign', data_auth_sign($auth));
					session('shipping_address_id', D('Member')->getAddress($uid));

				}

			}else{
				$json['redirect'] = U('/checkout');
			}

			$this->ajaxReturn($json);
			die();
		}
		$this->display();
	}
	//收货地址
	function shipping_address(){

		$s=session('shipping_address_id');

		if (isset($s)) {
			$this->address_id=$s;
		} else {
			$this->address_id=D('Member')->get_address_id(session('user_auth.uid'));
		}

		$this->province=M('area')->where('area_parent_id=0')->select();

		$this->addresses=D('Member')->getAddress(session('user_auth.uid'));

		$this->display();
	}

	function validate($cart,$json){

		if (!is_login()) {
			$json['redirect'] = U('/checkout');
		}

		//验证是否需要运送
		if (!$cart->has_shipping()) {
			$json['redirect'] = U('/checkout');
		}

		// 验证是否有货
		if ((!$cart->has_goods() ) ) {
			$json['redirect'] = U('/cart');
		}

		// 验证商品数量
		$products = $cart->get_all_goods();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['goods_id'] == $product['goods_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['redirect'] = U('/cart');

				break;
			}
		}
	}

	//验证收货地址
	function validate_shipping_address(){
		$cart=new \Lib\Cart();
		$json=array();
		$this->validate($cart,$json);

		$w=new \Lib\Weight();

		$weight=$w->format($cart->getWeight(), C('WEIGHT_ID'));

		session('weight',$weight['num']);

		if (!$json) {
			$d=I('post.');
			if (isset($d['shipping_address']) && $d['shipping_address'] == 'existing') {
				if (empty($d['address_id'])) {
					$json['error']['warning'] ='请选择送货地址！！';
				} elseif (!in_array($d['address_id'], array_keys(D('Member')->getAddress(session('user_auth.uid'))))) {

					$json['error']['warning'] = '无效地址！！';
				}
				if (!$json) {
					session('shipping_address_id',$d['address_id']);

					$address_info = M('Address')->where('address_id='.$d['address_id'])->find();

					if ($address_info) {
						session('shipping_city_id',$address_info['city_id']);
						//session('postcode',$address_info['postcode']);
						session('shipping_name',$address_info['name']);

					} else {
						session('shipping_city_id',null);
						//session('postcode',null);
					}
					session('shipping_method',null);
				}
			}

			if ($d['shipping_address'] == 'new') {


				if ((utf8_strlen($d['name']) < 1) || (utf8_strlen($d['name']) > 32)) {
					$json['error']['name'] = '姓名必须大于1位,小于32位！！';
				}

				if ((utf8_strlen($d['address']) < 3) || (utf8_strlen($d['address']) > 128)) {
					$json['error']['address'] = '地址必须大于3位小于128位！！';
				}

				if ((utf8_strlen($d['telephone']) < 3) || (utf8_strlen($d['telephone']) > 32)) {
						$json['error']['telephone'] = '电话长度错误！！';
				}

				if($d['province_id']==-1){
						$json['error']['area'] = '请选择省份！！';
				}
				if($d['city_id']==-1){
					$json['error']['area'] = '请选择城市！！';
				}

				if (!$json) {

					session('shipping_address_id',D('Member')->add_address());

					storage_user_action(session('user_auth.uid'),session('user_auth.username'),C('FRONTEND_USER'),'新增了收货地址');

					session('shipping_city_id',$d['city_id']);
					session('shipping_method',null);

				}
			}


		}

		$this->ajaxReturn($json);
		die();
	}

	function shipping_method_ajax()
	{

	}

	//货运方式
	function shipping_method(){

		$list=M('Transport')->select();

		if(isset($list)&&is_array($list)){
			foreach ($list as $k => $v) {
				$sm[$k]['id']=$v['id'];
				$sm[$k]['name']=$v['title'];
				$sm[$k]['info']=D('Transport')->calc_transport($v['id'], session('weight'), session('shipping_city_id') );
			}
		}

		$this->sm=$sm;

		$this->display();
	}
	//验证货运方式
	function validate_shipping_method(){
		$cart=new \Lib\Cart();
		$json=array();
		$this->validate($cart,$json);

		if (!$json) {
			$d=I('post.');
			if (!isset($d['shipping_method'])) {
				$json['error']['warning'] = '请选择货运方式！！';
			} else {

				if ($d['shipping_method']!=$d['shipping_method']) {
					$json['error']['warning'] ='非法操作！！';
				}
			}

			if (!$json) {

				session('shipping_method',$d['shipping_method']);
				session('comment',strip_tags($d['comment']));

			}
		}

		$this->ajaxReturn($json);
		die();
	}

	//支付方式
	function payment_method(){

		$this->list=M('payment')->where(array('payment_state'=>'1'))->select();

		$this->display();
	}

	function validate_payment_method(){
		$cart=new \Lib\Cart();
		$json=array();
		$this->validate($cart,$json);

		if (!$json) {
			$d=I('post.');
			if (!isset($d['payment_method'])) {
				$json['error']['warning'] = '请选择支付方式！！';
			} elseif (!M('Payment')->where(array('payment_code'=>$d['payment_method']))->find()) {
				//支付方式不存在
				$json['error']['warning'] = '非法操作！！';
			}

			if (!$json) {
				session('payment_method',$d['payment_method']);
			}

		}
		$this->ajaxReturn($json);
		die();
	}

	function confirm_done()
	{
	    $data = I('post.');
	    $cart=new \Lib\Cart();
	    $token=pay_token('token');

	    $goods=$cart->get_all_goods();
	    $goods_data = array_pop($goods);

	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    $goods_id=$hashids->decode($goods_data['goods_id']);


	    $result = array('code' => 0);

	    if (!is_login()) {
	       $result['msg'] = '登录失效';
	       echo json_encode($result);
	       die();
	    }

	    //需要送货
	    if ($cart->has_shipping()) {
	        $address_id=session('shipping_address_id');

	        $member_id = session('user_auth.uid');

	        $shipping_address = M('Address')->where(array('member_id'=>$member_id,'address_id' => $data['address_id']))->find();

	        if (empty($shipping_address)) {
    	           $result['msg'] = '请填写收货地址';
        	       echo json_encode($result);
        	       die();
	        } else {
	            $address_id = $shipping_address['address_id'];
	            session('shipping_address_id',$address_id);
	        }

	        //是否选定了配送方式
	        session('shipping_method',$data['transport_id']);
	        session('payment_method',$data['payment_method']);

	        //payment_method  shipping_method

	        $shipping_method=session('shipping_method');
	        if (!isset($shipping_method)) {
	            $result['msg'] = '请选择配送方式';
    	        echo json_encode($result);
    	        die();
	        }
	    }else{
	        session('shipping_method',null);
	    }


		session('remark',$data['remark']);
		session('shipping_method',$data['delivery']);
		if($data['delivery'] == 'pickup')
		{
			session('express_id',0);
		}else {
			session('express_id',$data['express_id']);
		}

		session('pick_up_id',$data['pick_up_id']);

		if($data['delivery'] == 'pickup' && $data['pick_up_id'] == 0)
		{
			$result['msg'] = '请选择自提地点';
	        echo json_encode($result);
	        die();
		}

	    session('shipping_address_id',$data['address_id']);

	    // 验证是否有货
	    if ((!$cart->has_goods() ) ) {
	        $result['msg'] = '购物车是空的';
	        echo json_encode($result);
	        die();
	    }

	    //商品规格库存状态
	    $goods_option_data = R('Goods/get_goods_options',$goods_id);
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    $max_quantity = $goods_info['quantity'];

	    if(!empty($goods_data['option'])) {
	        $opt_arr = array();
	        foreach($goods_data['option'] as $val){
	            $opt_arr[] = $val['option_value_id'].'_'.$val['goods_option_value_id'];
	        }
	        //判断规格库存是否比商品库存还小
	        foreach($goods_option_data['list'] as $vv)
	        {
	            foreach($vv['option_value'] as $option_value)
	            {
	                $tp_opt_str = $option_value['option_value_id'].'_'.$val['goods_option_value_id'];
	                if(in_array($tp_opt_str,$opt_arr)){
	                    if($max_quantity > $option_value['quantity']){
	                        $max_quantity = $option_value['quantity'];
	                    }
	                }
	            }
	        }

			$mul_opt_arr = array();
			foreach($goods_data['option'] as $val){
	            $mul_opt_arr[] = $val['option_value_id'];
	        }
			if(!empty($mul_opt_arr))
			{
				$rela_goodsoption_valueid = implode('_', $mul_opt_arr);
				$goods_option_mult_value = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $rela_goodsoption_valueid,'goods_id'=>$goods_id) )->find();

				if( !empty($goods_option_mult_value) )
				{
					if($goods_option_mult_value['quantity']<$data['num']){

						$result['msg'] = '商品数量不足，剩余'.$goods_option_mult_value['quantity'].'个！';
						echo json_encode($result);
						die();
					}
				}
			}
	    }

	    if($data['num'] > $max_quantity)
	    {
	        $result['msg'] = '库存不足';
	        echo json_encode($result);
	        die();
	    }
		$goods_model = D('Home/Goods');
		//检测商品限购
		$can_buy_count = $goods_model->check_goods_user_canbuy_count(is_login(), $goods_id[0]);

		if($can_buy_count == -1)
		{
			$result['msg'] = '该商品限购，您已经不能再买了。';
	        echo json_encode($result);
	        die();

		}else if($can_buy_count >0 && $data['num'] >$can_buy_count)
		{
			$result['msg'] = '该商品限购，您还能购买'.$can_buy_count.'份';
	        echo json_encode($result);
	        die();
		}

	    session('quantity',$data['num']);
	    session('payment_voucher_id', $data['voucher_id']);

	    //更新购物车数量
	    $cart->update($goods_data['key'], $data['num']);
	    $result['code'] = 1;

	    $pay_url = U('Payment/pay',array('token'=>$token));
	    $result['url'] = $pay_url;
	    echo json_encode($result);
	    die();
	}

	function confirm(){
		$cart=new \Lib\Cart();

		$this->token=pay_token('token');

		//需要送货
		if ($cart->has_shipping()) {
			$address_id=session('shipping_address_id');
			if (is_login()&& isset($address_id)) {
				$shipping_address = M('Address')->find($address_id);
			}

			if (empty($shipping_address)) {
				$redirect =U('/checkout');
			}

			//是否选定了配送
			$shipping_method=session('shipping_method');
			if (!isset($shipping_method)) {
				$redirect =U('/checkout');
			}
		}else{
			session('shipping_method',null);
		}
		//是否有选择支付方法
		$payment_method=session('payment_method');
		if (!isset($payment_method)) {
			$redirect =U('/checkout');
		}
		// 验证是否有货
		if ((!$cart->has_goods() ) ) {
			$redirect = U('/cart');
		}

		// 验证商品数量
		$products = $cart->get_all_goods();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['goods_id'] == $product['goods_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$redirect = U('/cart');

				break;
			}
		}

		if (!isset($redirect)) {
			if($products){

			//运费
			$sm=D('Transport')->calc_transport(session('shipping_method'),
			session('weight'),
			session('shipping_city_id'));

			$this->sm=$sm;

			foreach ($products as $product) {

				$p[] = array(
						'key'                 => $product['key'],
						'image'               => $product['image'],
						'name'                => $product['name'],
						'model'               => $product['model'],
						'quantity'            => $product['quantity'],
						'price'               => $product['price'],
						'total'               => $product['total'],
						'goods_id'		  		=>$product['goods_id'],
						'total_price'		  =>$product['total'],
						'option'			=>$product['option']
					);

			}

			$this->products=$p;

			}

		}

		$this->display();
	}

	//获取地区
    function get_area(){

        $goods_id = I('post.goods_id', 0);

        $where['area_parent_id']=$_REQUEST['areaId'];
        $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		if(!empty($goods_id)) {
			$goods_id=$hashids->decode($goods_id );
			$goods_id = $goods_id[0];
		}

        if($where['area_parent_id'] == 0 && $goods_id > 0)
        {

            $goods_area_limit = M('goods_area')->where( array('goods_id' => $goods_id) )->find();
            if(!empty($goods_area_limit)) {
                $area_limit_ids = unserialize($goods_area_limit['area_ids_text']);
				if(empty($area_limit_ids)) {
					$par_list = M('area')->where()->select();
				} else {
					$par_list = M('area')->where(array('area_id' => array('in', implode(',',$area_limit_ids) ) ))->select();
				}


                $par_ids = array();
                foreach($par_list as $val)
                {
                    if(empty($par_ids) || !in_array($val['area_parent_id'], $par_ids))
                    {
                        $par_ids[] = $val['area_parent_id'];
                    }
                }
                if(!empty($par_ids))
                {
                    $where['area_id'] = array('in', implode(',',$par_ids));
                }
            }
        } else if($where['area_parent_id'] > 0 && $goods_id > 0) {

            $goods_area_limit = M('goods_area')->where( array('goods_id' => $goods_id) )->find();

            if(!empty($goods_area_limit)) {
                $area_limit_ids = unserialize($goods_area_limit['area_ids_text']);

                if(!empty($area_limit_ids))
                {
                    $where['area_id'] = array('in', implode(',',$area_limit_ids));
                }
            }
        }

        $area=M('area')->where($where)->select();
        $this->ajaxReturn($area);
    }
}
