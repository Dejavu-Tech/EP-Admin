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
 * 处理订单相关内容
 */
namespace Home\Controller;

class ApicheckoutController extends CommonController {
    protected function _initialize()
    {
    	parent::_initialize();
        $this->cur_page = 'apicheckout';
		$this->member_id = 1;

		$this->appid = C('weprogram_appid');
		$this->appsecret = C('weprogram_appscret');
		$this->pay_key = C('weprogram_pay_key');
		$this->mch_id = C('weprogram_mch_id');
    }
	/**
		获取团详情
	**/
	public function group_orders()
	{
		$token = I('get.token');
		$order_id = I('get.id');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

	    $member_id = $weprogram_token['member_id'];


		$order_goods_sql = "select og.name as goods_name,g.goods_id,g.pin_count,g.pinprice,g.image,g.fan_image,g.store_id,og.pin_id from ".C('DB_PREFIX').'order_goods as og ,'.C('DB_PREFIX')."goods as g
	                       where og.order_id = {$order_id} and g.goods_id = og.goods_id limit 1";

	    $order_goods_arr = M()->query($order_goods_sql);

		$order_goods = $order_goods_arr[0];


		if(!empty($order_goods['fan_image'])){
			$order_goods['image']=str_replace('http','https',C('SITE_URL')).resize($order_goods['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}else {
			$order_goods['image']=str_replace('http','https',C('SITE_URL')).resize($order_goods['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}

		$water_image = '';

		$goods_description = M('goods_description')->where( array('goods_id' => $order_goods['goods_id']) )->find();
		if(!empty($goods_description['water_image']) )
		{
			$water_image = $goods_description['water_image'];
		}

	    //获取拼团信息
	    $pin_info = M('pin')->where( array('pin_id' => $order_goods['pin_id']) )->find();

	    if($pin_info['state'] == 0 && $pin_info['end_time'] < time()){
	       $pin_info['state'] = 2;
	    }

	    $tuanzhang_info = M('member')->where( array('member_id' => $pin_info['user_id']) )->find();

	    $pin_order_sql = "select po.add_time,m.member_id,m.name,m.telephone,m.avatar from ".C('DB_PREFIX')."pin_order as po,".C('DB_PREFIX')."order as o,
	                      ".C('DB_PREFIX')."order_goods as og,".C('DB_PREFIX')."member as m
	                          where po.pin_id = ".$order_goods['pin_id']." and o.order_status_id in(1,2,4,6,7,8,9,10,11)
	                          and og.order_id = po.order_id and o.order_id = po.order_id and o.member_id= m.member_id order by po.add_time asc ";

	    $pin_order_arr = M()->query($pin_order_sql);

		$users = array();
		$member_arr = array();
		foreach($pin_order_arr as $pin_order) {
			//{$pin_order.avatar}  join_time = add_time
			$tmp = array();
			$tmp['name'] = $pin_order['name'];
			$tmp['avatar'] = $pin_order['avatar'];
			$tmp['join_time'] = $pin_order['add_time'];

			$users[] = $tmp;

			$member_arr[] = $pin_order['member_id'];
		}
	    $is_me = in_array($member_id,$member_arr);


	    $seller_info = M('seller')->field('s_id,s_true_name,s_logo')->where(array('s_id' => $order_goods['store_id']))->find();
	    $seller_model = D('Home/Seller');

	    $seller_info['seller_count'] = $seller_model->getStoreSellerCount($order_goods['store_id']);


	    $order_info = '';


		if(!empty($water_image))
		{
			$this->share_image = str_replace('http','https',C('SITE_URL')).'/Uploads/image/'.$water_image;
		} else{
			$this->share_image = str_replace('http','https',C('SITE_URL')).$order_goods['image'];
		}

		$pinjie = M('blog')->where( array('type' => 'pinjie') )->order('blog_id desc')->find();


		$result = array();
		$result['users'] = $users;
		$result['status'] = $pin_info['state'];
		$result['order'] = $order_goods;
		$result['require_num'] = $order_goods['pin_count'];
		$result['people'] = count($pin_order_arr);
		$result['expire_time'] = $pin_info['end_time'];
		$result['take_in'] = $is_me ? 1:0;
		$result['pin_id'] = $pin_info['pin_id'];

		//expire_time {$pin_info.end_time} is_me

		//require_num  people
		//$del_count = $order_goods['pin_count'] - count($pin_order_arr);

		echo json_encode( array('code'=>1, 'group_order' => $result) );
		die();

	}

	/**
		微信充值
	**/
	public function wxcharge()
	{
		$token = I('get.token');
		$id = I('get.money');

		$level_info = M('member_level')->where( array('id' => $id) )->find();
		$money = $level_info['level_money'];



		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];

		if( empty($member_id) )
		{
			echo json_encode( array('code' =>1,'msg' =>'未登录') );
			die();
		}
		$member_info = M('member')->field('we_openid')->where( array('member_id' => $member_id) )->find();

		$member_charge_flow_data = array();
		$member_charge_flow_data['member_id'] = $member_id;
		$member_charge_flow_data['money'] = $money;
		$member_charge_flow_data['state'] = 0;
		$member_charge_flow_data['charge_time'] = 0;
		$member_charge_flow_data['add_time'] = time();

		$order_id = M('member_charge_flow')->add($member_charge_flow_data);

		$fee = $money;
		$appid = $this->appid;
		$body =         '客户升级';
		$mch_id =       $this->mch_id;
		$nonce_str =    $this->nonce_str();
		$notify_url =   C('SITE_URL').'notify.php';
		$openid =       $member_info['we_openid'];
		$out_trade_no = $order_id.'-'.time().'-charge-'.$id;
		$spbill_create_ip = $_SERVER['REMOTE_ADDR'];
		$total_fee =    $fee*100;
		$trade_type = 'JSAPI';

		$post['appid'] = $appid;
		$post['body'] = $body;
		$post['mch_id'] = $mch_id;
		$post['nonce_str'] = $nonce_str;
		$post['notify_url'] = $notify_url;
		$post['openid'] = $openid;
		$post['out_trade_no'] = $out_trade_no;
		$post['spbill_create_ip'] = $spbill_create_ip;
		$post['total_fee'] = $total_fee;
		$post['trade_type'] = $trade_type;
		$sign = $this->sign($post);


		$post_xml = '<xml>
			   <appid>'.$appid.'</appid>
			   <body>'.$body.'</body>
			   <mch_id>'.$mch_id.'</mch_id>
			   <nonce_str>'.$nonce_str.'</nonce_str>
			   <notify_url>'.$notify_url.'</notify_url>
			   <openid>'.$openid.'</openid>
			   <out_trade_no>'.$out_trade_no.'</out_trade_no>
			   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
			   <total_fee>'.$total_fee.'</total_fee>
			   <trade_type>'.$trade_type.'</trade_type>
			   <sign>'.$sign.'</sign>
			</xml> ';
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$xml = $this->http_request($url,$post_xml);
		$array = $this->xml($xml);
		if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
			$time = time();
			$tmp='';
			$tmp['appId'] = $appid;
			$tmp['nonceStr'] = $nonce_str;
			$tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
			$tmp['signType'] = 'MD5';
			$tmp['timeStamp'] = "$time";


			for($i =0; $i <3; $i++)
			{
				$tmp_data = array();
				$tmp_data['member_id'] = $member_id;
				$tmp_data['state'] = 0;
				$tmp_data['formid'] = $array['PREPAY_ID'];
				$tmp_data['addtime'] = time();

				M('member_formid')->add( $tmp_data );
			}
			//M('order')->where( array('order_id' => $order_id ) )->save( array('perpay_id' => $array['PREPAY_ID']) );


			$data['code'] = 0;
			$data['timeStamp'] = "$time";
			$data['nonceStr'] = $nonce_str;
			$data['signType'] = 'MD5';
			$data['package'] = 'prepay_id='.$array['PREPAY_ID'];
			$data['paySign'] = $this->sign($tmp);
			$data['out_trade_no'] = $out_trade_no;

			$data['redirect_url'] = '../dan/me';



		}else{
			$data['code'] = 1;
			$data['text'] = "错误";
			$data['RETURN_CODE'] = $array['RETURN_CODE'];
			$data['RETURN_MSG'] = $array['RETURN_MSG'];
		}


		echo json_encode($data);
		die();

	}

	/**
		获取客户团列表
	**/
	public function groups()
	{
	  /**
		"offset" : offset,
		"size" : size,
		"token" : token
	  **/
	  $offset = I('get.offset');
	  $size = I('get.size');
	  $token = I('get.token');
	  $weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		//$member_info =  M('member')->field('name,avatar')->where( array('member_id' => $weprogram_token['member_id']) )->find();

	   $member_id = $weprogram_token['member_id'];

	    //$type = I('get.type','0');
	    //$this->type = $type;

	    $pre_page = $size;


	    $where = ' ';

		/**
	    if($type == 1)
	    {
	        $where .= ' and p.state = 0 and p.end_time >'.time();
	    } else if($type == 2){
	        $where .= ' and p.state = 1 ';
	    } else if($type == 3){
	        $where .= ' and (p.state = 2 or  (p.state =0 and p.end_time <'.time().')) ';
	    }
		**/

	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    $sql = "select g.name as goods_name,g.image,g.fan_image,p.need_count,p.state,p.is_lottery,p.lottery_state,p.end_time,o.order_id,og.price,o.is_pin,o.pin_id,o.order_status_id from ".C('DB_PREFIX')."order as o, ".C('DB_PREFIX')."order_goods as og,
	        ".C('DB_PREFIX')."pin as p,".C('DB_PREFIX')."goods as g
	            where o.is_pin = 1 and o.order_id = og.order_id and og.goods_id = g.goods_id and o.pin_id = p.pin_id
	            and o.member_id = ".$member_id."  {$where} order by o.date_added desc limit {$offset},{$pre_page}";

	    $list = M()->query($sql);


	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    //group_order_id goods_name image_url
	    foreach($list as $key => $val)
	    {
	        $val['price'] = round($val['price'],2);

			if(!empty($val['fan_image'])){
				$val['image_url'] = str_replace('http','https',C('SITE_URL')).resize($val['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			}else {
				$val['image_url'] = str_replace('http','https',C('SITE_URL')).resize($val['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			}

	        $val['hash_order_id'] = $hashids->encode($val['order_id']);

	        if($val['state'] == 0 && $val['end_time'] < time())
	        {
	            $val['state'] = 2;
	        }
			//order_status_id

			if($val['state'] == 0){
				$val['state_name'] = '拼团中';
			}else if($val['state'] == 1){
				$val['state_name'] = '拼团成功';
			}else if($val['state'] == 2){
				$val['state_name'] = '拼团失败';
			}
			if($val['order_status_id'] == 3)
			{
				$val['state_name'] .= ',未付款';
			}

	        $list[$key] = $val;
	    }


		echo json_encode( array('code' =>1, 'group_orders' => $list) );
		die();
	}

	/**
		客户的支付地址
	**/
	public function addresses()
	{
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		$member_id = 1;
		$address_list  = M('address')->where( array('member_id' => $member_id) )->select();


		foreach($address_list as $key => $val)
		{
			$province_info = M('area')->field('area_name')->where( array('area_id' => $val['province_id']) )->find();
			$val['province'] = $province_info['area_name'];

			$city_info = M('area')->field('area_name')->where( array('area_id' => $val['city_id']) )->find();
			$val['city'] = $city_info['area_name'];

			$country_info = M('area')->field('area_name')->where( array('area_id' => $val['country_id']) )->find();
			$val['country'] = $country_info['area_name'];

			if($val['is_default'] == 1)
			{
			//	$val['status'] = 'DEFAULT';
			}else {
				//$val['status'] = '';
			}

			$address_list[$key] = $val;
		}

		$result = array('code' => 0,'address_list' => $address_list);
		echo json_encode($result);
		die();
	}

	public function get_area_version()
	{
		$result = array('code' =>1, 'data_version' => array('version' => '1'));
		echo json_encode($result);
		die();
	}
	public function get_area()
	{
		$region_list = M('area')->field('area_id,area_name')->where(array('area_parent_id' =>0))->order('area_id asc')->select();

		$version = '1';

	   $region_arr = array();
	   foreach($region_list as $val)
	   {
		   $tmp_arr = array();
		   $tmp_arr['region_id'] = $val['area_id'];
		   $tmp_arr['region_name'] = $val['area_name'];
		    $tmp_arr['parent_id'] = $val['area_parent_id'];
		   $region_arr[] = $tmp_arr;
	   }

	   $area_list = M('area')->field('area_id,area_name,area_parent_id')->where(array('area_deep' =>2))->order('area_id asc')->select();
		$area_arr = array();
	   foreach($area_list as $val)
	   {
		   $tmp_arr = array();
		   $tmp_arr['region_id'] = $val['area_id'];
		   $tmp_arr['region_name'] = $val['area_name'];
		   $tmp_arr['parent_id'] = $val['area_parent_id'];
		   $area_arr[] = $tmp_arr;
	   }


		$stree_list = M('area')->field('area_id,area_name,area_parent_id')->where(array('area_deep' =>3))->order('area_id asc')->select();

		$stree_arr = array();
	   foreach($stree_list as $val)
	   {
		   $tmp_arr = array();
		   $tmp_arr['region_id'] = $val['area_id'];
		   $tmp_arr['region_name'] = $val['area_name'];
		   $tmp_arr['parent_id'] = $val['area_parent_id'];
		   $stree_arr[] = $tmp_arr;
	   }
		$result  = array('code' =>1, 'regions' => array(0=>$region_arr,1=>$area_arr,2=>$stree_arr) );
		echo json_encode($result);
		die();
	}

	public function address_set()
	{
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];

		$address_id = I('get.address_id');



	    if($address_id == 0)
	    {
	        die('xxx');
	    }
	    $data = array();
	    $data['is_default'] = 0;

	    M('address')->where( array('member_id' => $member_id) )->save( $data );

	    $data['is_default'] = 1;
	    M('address')->where( array('member_id' => $member_id ,'address_id' => $address_id) )->save( $data );

	    echo json_encode( array('code' => 0) );
	    die();
	}
	public function address_info()
	{
		$address_id = I('get.address_id');
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];


		$address_info = M('address')->where( array('address_id' => $address_id ,'member_id' => $member_id) )->find();

		//$address_info['district_id'] = $address_info['country_id'];


		$result = array('code' =>0, 'address' => $address_info);

		echo json_encode($result);
		die();
	}

	public function add_weixin_selftaddress()
	{
		$token = I('get.token');
		$data_json = file_get_contents('php://input');
		$data = json_decode($data_json, true);
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		//{province_name: province_name, city_name: city_name, area_name: area_name, addr_tel: addr_tel, addr_detail: addr_detail}

		$cityName = $data['city_name'];
		$countyName = $data['area_name'];
		$detailInfo = $data['addr_detail'];
		$provinceName = $data['province_name'];
		$telNumber = $data['addr_tel'];
		$userName = $data['addr_name'];

		$sub_address_id = isset($data['sub_address_id']) ? $data['sub_address_id'] : 0 ;


		$province_info = M('area')->where( " area_name Like '%{$provinceName}%' " )->find();
		//$province_id = 35;
		if( !empty($province_info))
		{
			$province_id = $province_info['area_id'];
		}else{
			$area_data = array();
			$area_data['area_name'] = $provinceName;
			$area_data['area_parent_id'] = 0;
			$area_data['area_sort'] = 0;
			$area_data['area_deep'] = 1;
			$province_id = M('area')->add($area_data);
		}

		$city_info = M('area')->where( " area_name Like '%{$cityName}%' " )->find();
		//$city_id = 35;
		if( !empty($city_info))
		{
			$city_id = $city_info['area_id'];
		}else{
			$area_data = array();
			$area_data['area_name'] = $cityName;
			$area_data['area_parent_id'] = $province_id;
			$area_data['area_sort'] = 0;
			$area_data['area_deep'] = 2;
			$city_id = M('area')->add($area_data);
		}

		$country_info = M('area')->where( " area_name Like '%{$countyName}%' " )->find();
		//$country_id = 35;
		if( !empty($country_info))
		{
			$country_id = $country_info['area_id'];
		}else{
			$area_data = array();
			$area_data['area_name'] = $cityName;
			$area_data['area_parent_id'] = $city_id;
			$area_data['area_sort'] = 0;
			$area_data['area_deep'] = 3;
			$country_id = M('area')->add($area_data);
		}




		$address_data = array();
		$address_data['member_id'] = $member_id;
		$address_data['name'] = $userName;
		$address_data['telephone'] = $telNumber;
		$address_data['address'] = $detailInfo;
		$address_data['address_name'] = empty($data['address_name']) ? 'HOME' : $data['address_name'];
		$address_data['is_default'] = 0;
		$address_data['city_id'] = $city_id;
		$address_data['country_id'] = $country_id;
		$address_data['province_id'] = $province_id;
		if($sub_address_id > 0 )
		{
			unset($address_data['is_default']);
			M('address')->where( array('address_id' => $sub_address_id, 'member_id' => $member_id) )->save($address_data);
			$res = $sub_address_id;
		}else{
			$res = M('address')->add($address_data);
		}


		echo json_encode( array('address_id' => $res, 'code' => 0) );
		die();

	}

	public function add_weixinaddress()
	{
		$token = I('get.token');
		$data_json = file_get_contents('php://input');
		$data = json_decode($data_json, true);

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];

		$cityName = $data['cityName'];
		$countyName = $data['countyName'];
		$detailInfo = $data['detailInfo'];
		$provinceName = $data['provinceName'];
		$telNumber = $data['telNumber'];
		$userName = $data['userName'];


		$province_info = M('area')->where( " area_name Like '%{$provinceName}%' " )->find();

		if( !empty($province_info))
		{
			$province_id = $province_info['area_id'];
		}else{
			$area_data = array();
			$area_data['area_name'] = $provinceName;
			$area_data['area_parent_id'] = 0;
			$area_data['area_sort'] = 0;
			$area_data['area_deep'] = 1;
			$province_id = M('area')->add($area_data);
		}

		$city_info = M('area')->where( " area_name Like '%{$cityName}%' " )->find();

		if( !empty($city_info))
		{
			$city_id = $city_info['area_id'];
		}else{
			$area_data = array();
			$area_data['area_name'] = $cityName;
			$area_data['area_parent_id'] = $province_id;
			$area_data['area_sort'] = 0;
			$area_data['area_deep'] = 2;
			$city_id = M('area')->add($area_data);
		}

		$country_info = M('area')->where( " area_name Like '%{$countyName}%' " )->find();

		if( !empty($country_info))
		{
			$country_id = $country_info['area_id'];
		}else{
			$area_data = array();
			$area_data['area_name'] = $cityName;
			$area_data['area_parent_id'] = $city_id;
			$area_data['area_sort'] = 0;
			$area_data['area_deep'] = 3;
			$country_id = M('area')->add($area_data);
		}



		$has_addre = M('address')->where( array('member_id' => $member_id,'province_id' => $province_id,'country_id' => $country_id,'city_id' => $city_id,'address' => $detailInfo,'name' => $userName, 'telephone' =>$telNumber  ) )->find();

		if(empty($has_addre))
		{
			$has_default_address = M('address')->where( array('member_id' => $member_id, 'is_default' => 1) )->find();

			$address_data = array();
			$address_data['member_id'] = $member_id;
			$address_data['name'] = $userName;
			$address_data['telephone'] = $telNumber;
			$address_data['address'] = $detailInfo;
			$address_data['address_name'] = empty($data['address_name']) ? 'HOME' : $data['address_name'];
			if(!empty($has_default_address))
			{
				$address_data['is_default'] = 0;
			}else{
				$data = array();
				$data['is_default'] = 0;
				M('address')->where( array('member_id' => $member_id) )->save( $data );
				$address_data['is_default'] = 1;
			}

			$address_data['city_id'] = $city_id;
			$address_data['country_id'] = $country_id;
			$address_data['province_id'] = $province_id;
			$res = M('address')->add($address_data);
		}



		echo json_encode( array('address_id' => $res, 'code' => 0) );
		die();

	}

	public function modifyaddress()
	{
		$token = I('get.token');
		$address_id = I('get.address_id',0);

		$data_json = file_get_contents('php://input');
		$data = json_decode($data_json, true);

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];

		$address_data = array();
		$address_data['member_id'] = $member_id;
		$address_data['name'] = $data['name'];
		$address_data['telephone'] = $data['telephone'];
		$address_data['address'] = $data['address'];
		$address_data['address_name'] = empty($data['address_name']) ? 'HOME' : $data['address_name'];
		$address_data['is_default'] = 0;
		$address_data['city_id'] = $data['city'];
		$address_data['country_id'] = $data['district'];
		$address_data['province_id'] = $data['province'];

		if( isset($address_id) && $address_id>0)
		{
			$res = M('address')->where( array('address_id' => $address_id ,'member_id' => $member_id) )->save($address_data);
			$res = $address_id;
		}else {
			$res = M('address')->add($address_data);
		}

		echo json_encode( array('address_id' => $res, 'code' => 0) );
		die();
	}

	public function address_cancle()
	{
		$token = I('get.token');
		$address_id = I('get.address_id',0);

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];


		$r=M('address')->where(array('address_id'=>$address_id, 'member_id' => $member_id))->delete();

		echo json_encode( array('code' => 0) );
		die();
	}

	public function goods_detail()
	{

		$goods_id = I('get.goods_id');

		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

		$goods_info =  M('goods')->field('goods_id,name,pinprice,danprice,image')->where( array('goods_id'=>$goods_id) )->find();

		$goods_info['image_url'] = str_replace('http','https',C('SITE_URL')).'/Uploads/image/'.$goods_info['image'];
		$goods_info['goods_name'] = $goods_info['name'];
		$goods_info['group_price'] = $goods_info['pinprice'];
		$goods_info['alone_price'] = $goods_info['danprice'];


		$options=$this->get_goods_options($goods_id);

		$goods_option_mult_value = M('goods_option_mult_value')->where( array('goods_id' =>$goods_id) )->select();

		foreach($goods_option_mult_value as $key => $val)
		{
			$val['image'] = str_replace('http','https',C('SITE_URL')).resize($val['image'],200,200);
			$goods_option_mult_value[$key] = $val;
		}
		$result = array('code' => 1, 'goods' => $goods_info, 'options' =>$options, 'goods_option_mult_value' =>$goods_option_mult_value);

		echo json_encode($result);
		die();
	}

	public function get_goods_options($goods_id) {

        $result = array();
        $goods_option_name = array();
		$goods_option_data = array();
		$goods_option_query = M()->query("SELECT * FROM " . C('DB_PREFIX') . "goods_option po LEFT JOIN "
		. C('DB_PREFIX') . "option o ON po.option_id = o.option_id WHERE po.goods_id =".(int)$goods_id);



		foreach ($goods_option_query as $goods_option) {
			$goods_option_value_data = array();
			$goods_option_value_query = M()->query("SELECT pov.*,ov.value_name FROM " . C('DB_PREFIX')
			. "goods_option_value pov LEFT JOIN ". C('DB_PREFIX')
			."option_value ov ON pov.option_value_id=ov.option_value_id"
			." WHERE pov.goods_option_id = '"
			. (int)$goods_option['goods_option_id'] . "'");


			foreach ($goods_option_value_query as $goods_option_value) {


				$goods_option_value_data[] = array(
					'goods_option_value_id' => $goods_option_value['goods_option_value_id'],
					'option_value_id'         => $goods_option_value['option_value_id'],
					'quantity'                 => $goods_option_value['quantity'],
				    'name'					  =>$goods_option_value['value_name'],
					'image'					  =>isset($goods_option_value['image'])?$goods_option_value['image']:'',
					'price'                   =>'¥'.$goods_option_value['price'],
					'price_prefix'            => $goods_option_value['price_prefix'],

				);
			}
			$goods_option_name[] = $goods_option['name'];
			$goods_option_data[] = array(
				'goods_option_id'      => $goods_option['goods_option_id'],
				'option_id'            => $goods_option['option_id'],
				'name'                 => $goods_option['name'],
				'type'                 => $goods_option['type'],
				'option_value'         => $goods_option_value_data,
				'required'             => $goods_option['required']
			);
		}
	    $result['list'] = $goods_option_data;
	    $result['name'] = $goods_option_name;
		return $result;
	}

	public function sub_order()
	{

		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];


		$data_json = file_get_contents('php://input');
		$data_s = json_decode($data_json, true);

		$json=array();

	    $pay_method = $data_s['pay_method'];//支付类型
	    $order_msg_str = $data_s['order_msg_str'];//商品订单留言
	    $comment = $data_s['comment'];//商品订单留言

		$pick_up_id = $data_s['pick_up_id'];
		$dispatching = $data_s['dispatching'];
		$ziti_name = $data_s['ziti_name'];
		$ziti_mobile = $data_s['ziti_mobile'];
		$ck_yupay = $data_s['ck_yupay'];
		/**

        pick_up_id: that.data.pick_up_id,
        dispatching: that.data.dispatching, //express  pickup
        ziti_name: t_ziti_name,
        ziti_mobile: t_ziti_mobile
		**/
	    $order_msg_arr = explode('@,@', $order_msg_str);

		$quan_arr = $data_s['quan_arr'];//商品订单留言

		$order_quan_arr = array();

		if( !empty($quan_arr) )
		{
			foreach($quan_arr as $q_val)
			{
				$tmp_q = array();
				$tmp_q = explode('_',$q_val);

				$voucher_info =  M('voucher_list')->where( array('id' =>$tmp_q[1],
				'store_id' =>$tmp_q[0], 'user_id' => $member_id,'consume' =>'N','end_time' => array('gt',time() ) ) )->find();

				if( !empty($voucher_info) )
				{
					$order_quan_arr[$tmp_q[0]] = $tmp_q[1];
				}
			}

		}

	    $msg_arr = array();
	    foreach($order_msg_arr as $val)
	    {
	        $tmp_val = explode('@_@', $val);
	        $msg_arr[ $tmp_val[0] ] = $tmp_val[1];
	    }


	    $cart=new \Lib\Cart();

	    // 验证商品数量
	    //buy_type:buy_type
	    $buy_type = $data_s['buy_type'];//I('post.buy_type');


	    $is_pin = 0;
	    if($buy_type == 'pin')
	    {
	        $is_pin = 1;
	    }
	    $goodss = $cart->get_all_goodswecar($buy_type,$token);
		//付款人
	    $payment=M('Member')->find($member_id);

	    //收货人
	    $add_where = array('member_id'=>$member_id );
	    $address = M('address')->where( $add_where )->order('is_default desc,address_id desc')->find();

		$seller_goodss = array();

		foreach($goodss as $key => $val)
		{
			$goods_store_field =  M('goods')->field('store_id')->where( array('goods_id' => $val['goods_id']) )->find();
			$seller_goodss[ $goods_store_field['store_id'] ][$key] = $val;
		}


		$pay_total = 0;
		//M('order_all')
		$order_all_data = array();
		$order_all_data['member_id'] = $member_id;
		$order_all_data['order_num_alias'] = build_order_no($member_id);
		$order_all_data['transaction_id'] = '';
		$order_all_data['order_status_id'] = 3;
		$order_all_data['is_pin'] = $is_pin;
		$order_all_data['paytime'] = 0;

		$order_all_data['addtime'] = time();

		$order_all_id = M('order_all')->add($order_all_data);
		$integral_model = D('Seller/Integral');
		$order_ids_arr = array();
		$del_integral = 0;

		foreach($seller_goodss as $kk => $vv)
		{

			$data = array();

			$data['member_id']=$member_id;
			$data['name']= $payment['uname'];

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

			//$pick_up_id = $data_s['pick_up_id'];
			//$dispatching = $data_s['dispatching'];
			//$ziti_name = $data_s['ziti_name'];
			//$ziti_mobile = $data_s['ziti_mobile'];

			$data['payment_method']=$pay_method;

			$data['address_id']= $address['address_id'];
			$data['voucher_id'] = isset($order_quan_arr[$kk]) ? $order_quan_arr[$kk]:0;


			$data['user_agent']=$_SERVER['HTTP_USER_AGENT'];
			$data['date_added']=time();

			$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
			$subject='';
			$fare = 0;


	        $trans_free_toal = 0;//运费

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
	           //sku_str


	            $order_total += $good['total'];

	            $tp_goods_info = M('goods')->field('store_id,type')->where( array('goods_id' =>$good['goods_id'] ) )->find();
	            //
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
	                'member_disc' => $good['member_disc'],
	                'level_name' => $good['level_name'],
	                'option'     => $good['sku_str'],
	                'quantity'   => $good['quantity'],
	                'shipping_fare' => $trans_free,
	                'price'      => $good['price'],
	                'total'      => $good['total'],
	                'comment' => htmlspecialchars($comment)
	            );

	        }

			//$is_pin; is_lottery
			//'pintuan', 'normal', 'lottery'
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


	        $data['goodss'] = $goods_data;
	        $data['order_num_alias']=build_order_no($member_id);

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

			$data['from_type'] = 'wepro';

			if($data['voucher_id'] > 0) {
				$voucher_info = M('voucher_list')->where( array('id' => $data['voucher_id']) )->find();
				$data['voucher_credit'] = $voucher_info['credit'];
				M('voucher_list')->where( array('id' => $data['voucher_id']) )->save( array('consume' => 'Y') );
			} else {
				$data['voucher_credit'] = 0;
			}

			$data['comment'] = htmlspecialchars($comment);

			//判断自提 dispatching:"pickup"
			//dispatching, //express  pickup

			if($dispatching == 'express')
			{
				$data['total']=($order_total+$fare - $data['voucher_credit']);
			}else{
				$data['total'] = ($order_total - $data['voucher_credit']);
			}
			//积分商城
			if($data['type'] == 'integral')
			{
				$del_integral += $order_total;//扣除积分
				$data['total'] = 0;
				$order_total = 0;
			}

			//$data['total']=($order_total+$trans_free_toal- $data['voucher_credit'] );

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
				$pick_data['pick_id'] = $pick_up_id;
				$pick_data['order_id'] = $oid;
				$pick_data['state'] = 0;

				$pick_data['ziti_name'] = $ziti_name;
				$pick_data['ziti_mobile'] = $ziti_mobile;


				$pick_data['addtime'] = time();
				M('pick_order')->add($pick_data);
			}


			$order_ids_arr[] = $oid;
			//$pay_total = $pay_total + $order_total+$trans_free_toal - $data['voucher_credit'];

			if($dispatching == 'express')
			{
				$pay_total = $pay_total + $order_total+$trans_free_toal - $data['voucher_credit'];
			}else{
				$pay_total = $pay_total + $order_total - $data['voucher_credit'];
			}



			$order_relate_data = array();
			$order_relate_data['order_all_id'] = $order_all_id;
			$order_relate_data['order_id'] = $oid;
			$order_relate_data['addtime'] = time();

			M('order_relate')->add($order_relate_data);
		}

		M('order_all')->where( array('id' => $order_all_id) )->save( array('total_money' => $pay_total) );


	        if($order_all_id){
				//direct suborder

				$order = M('order')->where(array( 'order_id' => $oid ))->find();

				$member_info = M('member')->field('we_openid,account_money')->where( array('member_id' => $member_id) )->find();


				if( $pay_total<=0 || ($ck_yupay == 1 && $member_info['account_money'] >= $pay_total) )
				{
					//检测是否需要扣除积分
					//var_dump($del_integral,$is_integral );die();
					if($del_integral> 0 && $is_integral == 1)
					{
						//
						$integral_model->charge_member_score( $member_id, $del_integral,'out', 'orderbuy', $oid);
					}

					if($ck_yupay == 1 && $pay_total >0)
					{
						//开始余额支付
						$member_charge_flow_data = array();
						$member_charge_flow_data['member_id'] = $member_id;
						$member_charge_flow_data['trans_id'] = $oid;
						$member_charge_flow_data['money'] = $pay_total;
						$member_charge_flow_data['state'] = 3;
						$member_charge_flow_data['charge_time'] = time();
						$member_charge_flow_data['add_time'] = time();
						M('member_charge_flow')->add($member_charge_flow_data);
						//开始处理扣钱

						M('member')->where( array('member_id' => $member_id) )->setInc('account_money',-$pay_total);
					}

					//开始处理订单状态
					$order_all = M('order_all')->where( array('id' => $order_all_id) )->find();

					if($order&&($order['order_status_id']!=C('paid_order_status_id')))
					{
						//支付完成
						$o = array();
						$o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;
						$o['paytime']=time();
						$o['transaction_id'] = $transaction_id;
						M('order_all')->where( array('id' => $out_trade_no) )->save($o);

						$order_relate_list =  M('order_relate')->where( array('order_all_id' => $order_all['id']) )->select();

						foreach($order_relate_list as $order_relate)
						{

							$order=M('Order')->where( array('order_id' =>$order_relate['order_id']) )->find();

							if( $order && $order['order_status_id'] == 3)
							{
								$o = array();
								$o['payment_code'] = 'yuer';
								$o['order_id']=$order['order_id'];
								$o['order_status_id'] =  $order['is_pin'] == 1 ? 2:1;
								$o['date_modified']=time();
								$o['pay_time']=time();
								$o['transaction_id'] = $is_integral ==1? '积分兑换':'余额支付';

								M('Order')->save($o);

								$kucun_method = C('kucun_method');
								$kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);

								$goods_model = D('Home/Goods');
								if($kucun_method == 1)
								{//支付完减库存，增加销量
									$order_goods_list = M('order_goods')->where( array('order_id' => $order['order_id']) )->select();
									foreach($order_goods_list as $order_goods)
									{

										 $goods_model->del_goods_mult_option_quantity($order['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],1);
									}
								}

								$oh = array();
								$oh['order_id']=$order['order_id'];
								$oh['order_status_id']= $order['is_pin'] == 1 ? 2:1;

								$oh['comment']='买家已付款';
								$oh['date_added']=time();
								$oh['notify']=1;
								M('OrderHistory')->add($oh);

								//发送购买通知
								$weixin_nofity = D('Home/Weixinnotify');
								$weixin_nofity->orderBuy($order['order_id']);

								$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
								$order_id = $hashids->encode($order['order_id']);
								//group_order_id
								if($order['is_pin'] == 1)
								{
									$pin_order = M('pin_order')->where(array('order_id' => $order['order_id']) )->find();

									$pin_model = D('Home/Pin');
									$pin_model->insertNotifyOrder($order['order_id']);

									$is_pin_success = $pin_model->checkPinSuccess($pin_order['pin_id']);

									if($is_pin_success) {
										//todo send pintuan success notify
										$pin_model->updatePintuanSuccess($pin_order['pin_id']);
									}
								}

							}

						}
						//返回支付成功给app
						$data = array();
						$data['code'] = 0;
						$data['has_yupay'] = 1;
						$data['is_integral'] = $is_integral;
					}

				}else{
				$fee = $pay_total;
				$appid = $this->appid;
				$body =         '商品购买';
				$mch_id =       $this->mch_id;
				$nonce_str =    $this->nonce_str();
				$notify_url =   C('SITE_URL').'notify.php';
				$openid =       $member_info['we_openid'];
				$out_trade_no = $order_all_id.'-'.time();
				$spbill_create_ip = $_SERVER['REMOTE_ADDR'];
				$total_fee =    $fee*100;
				$trade_type = 'JSAPI';

				$post['appid'] = $appid;
				$post['body'] = $body;
				$post['mch_id'] = $mch_id;
				$post['nonce_str'] = $nonce_str;
				$post['notify_url'] = $notify_url;
				$post['openid'] = $openid;
				$post['out_trade_no'] = $out_trade_no;
				$post['spbill_create_ip'] = $spbill_create_ip;
				$post['total_fee'] = $total_fee;
				$post['trade_type'] = $trade_type;
				$sign = $this->sign($post);


				$post_xml = '<xml>
					   <appid>'.$appid.'</appid>
					   <body>'.$body.'</body>
					   <mch_id>'.$mch_id.'</mch_id>
					   <nonce_str>'.$nonce_str.'</nonce_str>
					   <notify_url>'.$notify_url.'</notify_url>
					   <openid>'.$openid.'</openid>
					   <out_trade_no>'.$out_trade_no.'</out_trade_no>
					   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
					   <total_fee>'.$total_fee.'</total_fee>
					   <trade_type>'.$trade_type.'</trade_type>
					   <sign>'.$sign.'</sign>
					</xml> ';
				$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
				$xml = $this->http_request($url,$post_xml);
				$array = $this->xml($xml);
				//var_dump($openid, $array);die();
				if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
					$time = time();
					$tmp='';
					$tmp['appId'] = $appid;
					$tmp['nonceStr'] = $nonce_str;
					$tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
					$tmp['signType'] = 'MD5';
					$tmp['timeStamp'] = "$time";

					M('order')->where( array('order_id' => array('in',$order_ids_arr) ) )->save( array('perpay_id' => (string)$array['PREPAY_ID']) );
					$data = array();
					$data['code'] = 0;
					$data['appid'] = $appid;
					$data['timeStamp'] = "$time";
					$data['nonceStr'] = $nonce_str;
					$data['signType'] = 'MD5';
					$data['package'] = 'prepay_id='.$array['PREPAY_ID'];
					$data['paySign'] = $this->sign($tmp);
					$data['out_trade_no'] = $out_trade_no;

					if($is_pin == 1)
					{
						$data['redirect_url'] = '../groups/group?id='.$oid.'&is_show=1';
					} else {
						$data['redirect_url'] = '../orders/order_show_all?order_all_id=' + $order_all_id;
					}

				}else{
					$data = array();
					$data['code'] = 1;
					$data['text'] = "错误";
					$data['RETURN_CODE'] = $array['RETURN_CODE'];
					$data['RETURN_MSG'] = $array['RETURN_MSG'];
					}
					$data['has_yupay'] = 0;
				}

				if($is_pin == 1)
				{
					$data['order_id'] = $oid;
					$data['order_all_id'] = $order_all_id;
				}else{
					$data['order_id'] = $oid;
					$data['order_all_id'] = $order_all_id;
				}



				echo json_encode($data);
				die();
	        }else{
				echo json_encode( array('code' =>1,'order_all_id' =>$order_all_id) );
				die();
	        }

	}

	public function orders2()
	{
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];



		$data_json = file_get_contents('php://input');
		$data = json_decode($data_json, true);


		$quantity = $data['quantity'];
		$goods_sku = $data['goods_sku'];

		if( !empty($goods_sku) )
		{
			$option = array_filter($goods_sku);

			$new_option = array();
			foreach($option as $heng_hua)
			{
				$tmp_a = explode('_',$heng_hua);
				$new_option[$tmp_a[0]] = $tmp_a[1];
			}
			$option = $new_option;
		}


		$goods_id = $data['goods_id'];
		$address_id = $data['address_id'];
		$group_order_id = $data['group_order_id'];
		$groupbuy = $data['groupbuy'] == 1 ? 'pin':'dan';

		$member_info = M('member')->field('name')->where( array('member_id' => $member_id) )->find();
		$shipping = M('address')->where( array('address_id'=>$address_id) )->find();


		$data['member_id']=$member_id;
		$data['name']=$member_info['name'];

		$data['telephone']=$shipping['telephone'];

		$data['shipping_name']=$shipping['name'];
		$data['shipping_address']=$shipping['address'];
		$data['shipping_tel']=$shipping['telephone'];

		$data['shipping_province_id']=$shipping['province_id'];
		$data['shipping_city_id']=$shipping['city_id'];
		$data['shipping_country_id']=$shipping['country_id'];

		$data['shipping_method'] = 0;
		$data['delivery']='express';


		$data['payment_method']='wxpay';

		$data['address_id']=$address_id;

		$data['voucher_id']=0;


		$data['user_agent']=$_SERVER['HTTP_USER_AGENT'];
		$data['date_added']=time();
		$data['comment']='';
		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

		$goodss = M('goods')->where( array('goods_id' =>$goods_id) )->select();
		$t=0;
		foreach ($goodss as $goods)
		{

			$option_data = array();

			//sku begin
			if( !empty($option) )
			{
				//$option
				$option_data = array();
				$options = $option;

				foreach ($options as $goods_option_id => $option_value) {
					$option_query = M()->query("SELECT po.goods_option_id, po.option_id, o.name, o.type FROM "
					 . C('DB_PREFIX') . "goods_option po LEFT JOIN `"
					 . C('DB_PREFIX') . "option` o ON (po.option_id = o.option_id)
					  WHERE po.goods_option_id = '"
					 . (int)$goods_option_id . "' AND po.goods_id = " . (int)$goods_id);


					if ($option_query) {
							$option_value_query = M()->query("SELECT pov.option_value_id,
			ov.value_name, pov.quantity, pov.subtract, pov.price, pov.price_prefix,pov.weight, pov.weight_prefix FROM "
			. C('DB_PREFIX') . "goods_option_value pov LEFT JOIN "
			. C('DB_PREFIX') . "option_value ov ON (pov.option_value_id = ov.option_value_id) WHERE pov.goods_option_value_id = '"
			. (int)$option_value . "' AND pov.goods_option_id = "
			. (int)$goods_option_id);

							if ($option_value_query) {
								$option_data[] = array(
									'goods_option_id'       => $goods_option_id,
									'goods_option_value_id' => $option_value,
									'option_id'               => $option_query[0]['option_id'],
									'option_value_id'         => $option_value_query[0]['option_value_id'],
									'name'                    => $option_query[0]['name'],
									'value'            => $option_value_query[0]['value_name'],
									'type'                    => $option_query[0]['type'],
									'quantity'                => $option_value_query[0]['quantity'],
									'subtract'                => $option_value_query[0]['subtract'],
									'price'                   => $option_value_query[0]['price'],
									'price_prefix'            => 0,
									'weight'                  => 0,
									'weight_prefix'           => 0
								);
							}

					}
				}
			}

			/**
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
			**/

			$tp_goods_info = M('goods')->field('store_id,express_list')->where( array('goods_id' =>$goods['goods_id']))->find();

			$express_list_arr = unserialize($tp_goods_info['express_list']);

			if($data['delivery'] == 'express')
			{
				$fare = isset($express_list_arr[$data['shipping_method']]) ? $express_list_arr[$data['shipping_method']]['price'] : 0;
			}

			$t+=$goods['pinprice'];

			$goods['pinprice'] = $goods['pinprice'];

			//$goods_id=$hashids->encode($goods['goods_id']);
			$goods_data[] = array(
				'goods_id'   => $hashids->encode($goods['goods_id']),//goods_id
				'name'       => $goods['name'],
				'store_id'   => $tp_goods_info['store_id'],
				'model'      => $goods['model'],
				'option'     => $option_data,
				'quantity'   => 1,
				'pin_type'   => $groupbuy,
				'pin_id'     => $group_order_id,
				'price'      => $goods['pinprice'],
				'total'      => $goods['pinprice']
			);

			$subject.=$goods['name'].' ';

		}

		$data['shipping_fare'] = floatval($fare);

		$data['total']=($t+$fare );
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
	//self.order_id = data.order_id;


	$result = array('code' =>1,'order_id' =>$oid);
	echo json_encode($result);
	die();
	}

	public function wxpay()
	{

		$token = I('get.token');
		$order_id = I('get.order_id');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];

		if( empty($member_id) )
		{
			echo json_encode( array('code' =>1,'msg' =>'未登录') );
			die();
		}

		$member_info = M('member')->field('we_openid')->where( array('member_id' => $member_id) )->find();

		$order = M('order')->where(array( 'order_id' => $order_id ))->find();
		//var_dump($order);die(); is_pin

		//$order['is_pin']
		$pin_order =  M('pin_order')->where( array('order_id' => $order_id) )->find();

		if( !empty($pin_order) )
		{
			$pin_model = D('Home/Pin');
			$is_pin_over = $pin_model->getNowPinState($pin_order['pin_id']);
			if($is_pin_over != 0)
			{
				 M('pin_order')->where( array('order_id' => $order_id) )->delete();
				 M('pin')->where( array('pin_id' => $pin_order['pin_id'], 'order_id' => $order_id) )->delete();

				$order_goods_info =  M('order_goods')->field('goods_id')->where( array('order_id'=>$order_id) )->find();
				//新开团

				$pin_id = $pin_model->openNewTuan($order_id,$order_goods_info['goods_id'],$member_id);
				//插入拼团订单
	            $pin_model->insertTuanOrder($pin_id,$order_id);

			}
		}


		//单独支付一个店铺的订单
		M('order_relate')->where( array('order_id' => $order_id) )->delete();

		$order_all_data = array();
		$order_all_data['member_id'] = $member_id;
		$order_all_data['order_num_alias'] = build_order_no($member_id);
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

		//$order_all_data[order_num_alias] shipping_fare

		//order $data['delivery'] == 'pickup'
		if( $order['delivery'] == 'pickup' )
		{
			$fee = $order['total'];//-$order['voucher_credit'];
		}else {
			$fee = $order['total'];//+$order['shipping_fare']-$order['voucher_credit'];
		}

		/**
		$pay_total = $pay_total + $order_total+$trans_free_toal - $data['voucher_credit'];

		**/

		$appid = $this->appid;
		$body =         '商品购买';
		$mch_id =       $this->mch_id;
		$nonce_str =    $this->nonce_str();
		$notify_url =   C('SITE_URL').'notify.php';
		$openid =       $member_info['we_openid'];
		$out_trade_no = $order_all_id.'-'.time();
		$spbill_create_ip = $_SERVER['REMOTE_ADDR'];
		$total_fee =    $fee*100;
		$trade_type = 'JSAPI';

		$post['appid'] = $appid;
		$post['body'] = $body;
		$post['mch_id'] = $mch_id;
		$post['nonce_str'] = $nonce_str;
		$post['notify_url'] = $notify_url;
		$post['openid'] = $openid;
		$post['out_trade_no'] = $out_trade_no;
		$post['spbill_create_ip'] = $spbill_create_ip;
		$post['total_fee'] = $total_fee;
		$post['trade_type'] = $trade_type;
		$sign = $this->sign($post);


		$post_xml = '<xml>
			   <appid>'.$appid.'</appid>
			   <body>'.$body.'</body>
			   <mch_id>'.$mch_id.'</mch_id>
			   <nonce_str>'.$nonce_str.'</nonce_str>
			   <notify_url>'.$notify_url.'</notify_url>
			   <openid>'.$openid.'</openid>
			   <out_trade_no>'.$out_trade_no.'</out_trade_no>
			   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
			   <total_fee>'.$total_fee.'</total_fee>
			   <trade_type>'.$trade_type.'</trade_type>
			   <sign>'.$sign.'</sign>
			</xml> ';
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$xml = $this->http_request($url,$post_xml);
		$array = $this->xml($xml);
		if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
			$time = time();
			$tmp='';
			$tmp['appId'] = $appid;
			$tmp['nonceStr'] = $nonce_str;
			$tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
			$tmp['signType'] = 'MD5';
			$tmp['timeStamp'] = "$time";

			M('order')->where( array('order_id' => $order_id ) )->save( array('perpay_id' => $array['PREPAY_ID']) );


			$data['code'] = 0;
			$data['timeStamp'] = "$time";
			$data['nonceStr'] = $nonce_str;
			$data['signType'] = 'MD5';
			$data['package'] = 'prepay_id='.$array['PREPAY_ID'];
			$data['paySign'] = $this->sign($tmp);
			$data['out_trade_no'] = $out_trade_no;
			$data['is_pin'] = $order['is_pin'];

			if($order['is_pin'] == 1)
			{
				$data['redirect_url'] = '../groups/group?id='.$order_id.'&is_show=1';
			} else {
				$data['redirect_url'] = '../orders/order?id=' + $order_id;
			}

		}else{
			$data['code'] = 1;
			$data['text'] = "错误";
			$data['RETURN_CODE'] = $array['RETURN_CODE'];
			$data['RETURN_MSG'] = $array['RETURN_MSG'];
		}


		echo json_encode($data);
		die();
	}

	public function getorder()
	{
		$token = I('get.token');
		$order_id = I('get.order_id',0);

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];


	    $order_info = M('order')->where( array('order_id' => $order_id) )->find();
	    $order_status_info = M('order_status')->where( array('order_status_id' => $order_info['order_status_id']) )->find();

	    $shipping_province = M('area')->where( array('area_id' => $order_info['shipping_province_id']) )->find();
	    $shipping_city = M('area')->where( array('area_id' => $order_info['shipping_city_id']) )->find();
	    $shipping_country = M('area')->where( array('area_id' => $order_info['shipping_country_id']) )->find();

	    $order_goods = M('order_goods')->where( array('order_id' => $order_id) )->find();

	    $store_info = M('seller')->where('s_id='.$order_goods['store_id'])->find();

	    $order_option_info = M('order_option')->where( array('order_id' =>$order_id) )->select();

	    $goods_info = M('goods')->where( array('goods_id' => $order_goods['goods_id']) )->find();

	    $voucher_info = array();
	    if($order_info['voucher_id'] > 0) {
	           $voucher_info =  M('voucher_list')->where( array('id' => $order_info['voucher_id']) )->find();

	    }

	    $pin_model = D('Home/Pin');
	    if($order_info['order_status_id'] == 2)
	    {
	        if($order_info['is_pin'] == 1 && $order_info['pin_id'] > 0)
	        {
	            $state = $pin_model->getNowPinState($order_info['pin_id']);
	            if($state == 2){
	                $order_status_info['name'] = '拼团失败，等待退款';
	            }
	        }
	    }
	    if($order_info['order_status_id'] == 1)
	    {

	        if($order_info['is_pin'] == 1 && $order_info['pin_id'] > 0 && $order_info['lottery_win'] ==0)
	        {
	            $pin_info = M('pin')->where( array('pin_id' =>$order_info['pin_id'] ) )->find();
	          if($pin_info['is_lottery'] == 1)
	          {
	              if($pin_info['lottery_state'] == 0){
	                 $order_status_info['name'] = '已成团，待抽奖';
	              }else if($pin_info['lottery_state'] == 1){
	                 $order_status_info['name'] = '二等奖，待退款并送券';
	              }
	          }
	        }else if($order_info['is_pin'] == 1 && $order_info['pin_id'] > 0 && $order_info['lottery_win'] ==1)
	        {
	            $order_status_info['name'] = '一等奖，待发货';
	        }
	    }

		$pick_order_info = array();
		$pick_up = array();
		if($order_info['delivery'] == 'pickup')
		{
			$pick_order_info = M('pick_order')->where( array('order_id' => $order_info['order_id']) )->find();
			$pick_up = M('pick_up')->where( array('id' => $pick_order_info['pick_id']) )->find();
		}

		$this->pick_order_info = $pick_order_info;
		$this->pick_up = $pick_up;

	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

		$order_info['hash_order_id']= $hashids->encode($order_info['order_id']);

		$order_send_history = M('order_history')->where( array('order_id' => $order_id,'order_status_id' =>4) )->find();
		$order_get_history = M('order_history')->where( array('order_id' => $order_id,'order_status_id' =>6) )->find();

		$order_infos = array();
		$order_infos = array_merge($order_infos,$order_info);

		$order_infos['order_time'] = $order_info['date_modified'];
		$order_infos['order_status'] = $order_info['order_status_id'];
		$order_infos['order_amount'] = round($order_info['total'],2);

		$order_infos['city_name'] = $shipping_province['area_name'].$shipping_city['area_name'].$shipping_country['area_name'];
		$order_infos['receive_name'] = $order_infos['shipping_name'];
		$order_infos['mobile'] = $order_infos['shipping_tel'];
		$order_infos['order_sn'] = $order_infos['order_num_alias'];
		$order_infos['order_option_info'] = $order_option_info;


		$order_infos['image_url'] = str_replace('http','https',C('SITE_URL')).'/'.resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

		$order_infos['goods_name'] = $goods_info['name'];
		$order_infos['market_price'] = $order_goods['total'];

		$order_infos['group_order_id'] = $order_info['pin_id'];


		$order_infos['goods_id'] = $order_goods['goods_id'];

		$result = array('code'=>1,'order' => $order_infos);

		echo json_encode($result);
		die();
	}

	public function orderlist()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];

		$page = I('get.page',1);

		$size = I('get.size',6);
		$offset = ($page - 1)* $size;

		$type = I('get.type','');

		$order_status = I('get.order_status',-1);


	    $where = ' and o.member_id = '.$member_id;

		/**
		if($order_status == 5)
	    {
	        $where .= ' and o.is_pin = 1  and  o.order_status_id = 7';
	    }
		else if($order_status == 4)
	    {
	        $where .= ' and o.is_pin = 1  and  o.order_status_id  in( 1,4,6,11) ';
	    }
	    else
		**/
		if($order_status > 0 && $order_status <12)
	    {
	        $where .= ' and o.order_status_id = '.$order_status;
	    }
		else if($order_status == 12)
		{
			 $where .= ' and o.order_status_id in(12,13)';
		}

		if( !empty($type) )
		{
			$where .= ' and o.type = "integral" ';
		}

	    $sql = "select o.order_id,o.delivery,o.is_pin,o.is_zhuli,o.shipping_fare,o.voucher_credit,o.store_id,o.total,o.order_status_id,o.lottery_win,o.type,os.name as status_name from ".C('DB_PREFIX')."order as o ,
                ".C('DB_PREFIX')."order_status as os
	                    where  o.order_status_id = os.order_status_id {$where}
	                    order by o.date_added desc limit {$offset},{$size}";

	    $list = M()->query($sql);
	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

	    foreach($list as $key => $val)
	    {
			//voucher_credit shipping_fare

			if($val['delivery'] == 'pickup')
			{
				//$val['total'] = round($val['total'],2) - round($val['voucher_credit'],2);
			}else{
				//$val['total'] = round($val['total'],2)+round($val['shipping_fare'],2) - round($val['voucher_credit'],2);
			}


			// $val['delivery'] =='pickup'

			if($val['shipping_fare']<=0.001 || $val['delivery'] == 'pickup')
			{
				$val['shipping_fare'] = '免运费';
			}else{
				$val['shipping_fare'] = '运费:'.$val['shipping_fare'];
			}


			if($val['order_status_id'] == 10)
			{
				$val['status_name'] = '等待退款';
			}
			else if($val['order_status_id'] == 4 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '待自提';
				//已自提
			}
			else if($val['order_status_id'] == 6 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '已自提';
				//已自提
			}
			else if($val['order_status_id'] == 1 && $val['type'] == 'lottery')
			{
				//等待开奖
				//一等奖
				if($val['lottery_win'] == 1)
				{
					$val['status_name'] = '一等奖';
				}else {
					$val['status_name'] = '等待开奖';
				}

			}
			else if($val['order_status_id'] == 2 && $val['type'] == 'lottery')
			{
				//等待开奖
				$val['status_name'] = '等待开奖';
			}


	        $val['hash_order_id']= $hashids->encode($val['order_id']);
	        $quantity = 0;

	        $goods_sql = "select order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid
	            from ".C('DB_PREFIX')."order_goods where order_id= ".$val['order_id']."";

	        $goods_list = M()->query($goods_sql);
	        foreach($goods_list as $kk => $vv)
	        {
	            $order_option_list = M('order_option')->where( array('order_goods_id' =>$vv['order_goods_id']) )->select();

	            $vv['goods_images']= C('SITE_URL') .resize($vv['goods_images'], C('common_image_thumb_width'), C('common_image_thumb_height'));
	            //price orign_price
				$goods_filed =  M('goods')->field('price')->where( array('goods_id' => $vv['goods_id']) )->find();
				$vv['orign_price'] = $goods_filed['price'];
	            $quantity += $vv['quantity'];
	            foreach($order_option_list as $option)
	            {
	                $vv['option_str'][] = $option['value'];
	            }
				if( !isset($vv['option_str']) )
				{
					$vv['option_str'] = '';
				}else{
					$vv['option_str'] = implode(',', $vv['option_str']);
				}
	            $vv['price'] = round($vv['price'],2);
	            $vv['orign_price'] = round($vv['orign_price'],2);
				//price orign_price

	            $goods_list[$kk] = $vv;
	        }
	        $val['quantity'] = $quantity;
	        if( empty($val['store_id']) )
			{
				$val['store_id'] = 1;
			}
			$store_info = M('seller')->field('s_true_name,s_logo')->where('s_id='.$val['store_id'])->find();

			$store_info['s_logo'] = C('SITE_URL').'/Uploads/image/'.$store_info['s_logo'];

			$val['store_info'] = $store_info;


	        $val['goods_list'] = $goods_list;

			if($val['type'] == 'integral')
			{
				//$order_id
				$integral_order = M('integral_order')->field('score')->where( array('order_id' => $val['order_id']) )->find();
				$val['score'] = intval($integral_order['score']);
			}

			$val['total'] = round($val['total'],2);
	        $list[$key] = $val;
	    }

		$need_data = array('code' => 0);

		if( !empty($list) )
		{
			$need_data['data'] = $list;

		}else {
			$need_data = array('code' => 1);
		}

		echo json_encode( $need_data );
		die();

	}


	public function order_info()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];

		$order_id = I('get.id',0);

	    $order_info = M('order')->where( array('order_id' => $order_id, 'member_id' => $member_id) )->find();

		$pick_up_info = array();
		$pick_order_info = array();

		if( $order_info['delivery'] == 'pickup' )
		{
			//查询自提点
			$pick_order_info = M('pick_order')->where( array('order_id' => $order_id) )->find();

			$pick_id = $pick_order_info['pick_id'];
			$pick_up_info = M('pick_up')->where( array('id' => $pick_id) )->find();

		}
		//$this->pick_up_info =  $pick_up_info;

	    $order_status_info = M('order_status')->where( array('order_status_id' => $order_info['order_status_id']) )->find();
	    //10 name
		if($order_info['order_status_id'] == 10)
		{
			$order_status_info['name'] = '等待退款';
		}
		else if($order_info['order_status_id'] == 4 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '待自提';
			//已自提
		}
		else if($order_info['order_status_id'] == 6 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '已自提';

		}
		else if($order_info['order_status_id'] == 1 && $order_info['type'] == 'lottery')
		{
			//等待开奖
			//一等奖
			if($order_info['lottery_win'] == 1)
			{
				$order_status_info['name'] = '一等奖';
			}else {
				$order_status_info['name'] = '等待开奖';
			}

		}

	    $shipping_province = M('area')->where( array('area_id' => $order_info['shipping_province_id']) )->find();
	    $shipping_city = M('area')->where( array('area_id' => $order_info['shipping_city_id']) )->find();
	    $shipping_country = M('area')->where( array('area_id' => $order_info['shipping_country_id']) )->find();

	    $order_goods_list = M('order_goods')->where( array('order_id' => $order_id) )->select();

		$shiji_total_money = 0;
		$member_youhui = 0.00;

	    foreach($order_goods_list as $key => $order_goods)
	    {
	        $order_option_info = M('order_option')->field('value')->where( array('order_id' =>$order_id,'order_goods_id' => $order_goods['order_goods_id']) )->select();

	        foreach($order_option_info as $option)
	        {
	            $vv['option_str'][] = $option['value'];
	        }
			if(empty($vv['option_str']))
			{
				//option_str
				 $order_goods['option_str'] = '';
			}else{
				 $order_goods['option_str'] = implode(',', $vv['option_str']);
			}
	       //
		    $order_goods['shipping_fare'] = round($order_goods['shipping_fare'],2);
		    $order_goods['price'] = round($order_goods['price'],2);
		    $order_goods['total'] = round($order_goods['total'],2);
		    $order_goods['real_total'] = round($order_goods['quantity'] * $order_goods['price'],2);

	        $order_goods['image']=C('SITE_URL').resize($order_goods['goods_images'], C('common_image_thumb_width'), C('common_image_thumb_height'));

			$order_goods['goods_images']= C('SITE_URL').'/Uploads/image/'.$order_goods['goods_images'];

			$goods_info =  M('goods')->field('price')->where( array('goods_id' => $order_goods['goods_id']) )->find();

			 $order_goods['shop_price'] = $goods_info['price'];

			$store_info = M('seller')->field('s_true_name,s_logo')->where('s_id='.$order_goods['store_id'])->find();

			$store_info['s_logo'] = C('SITE_URL').'/Uploads/image/'.$store_info['s_logo'];

			$order_goods['store_info'] = $store_info;

			unset($order_goods['model']);
			unset($order_goods['rela_goodsoption_valueid']);
			unset($order_goods['comment']);

	        $order_goods_list[$key] = $order_goods;
			$shiji_total_money += $order_goods['quantity'] * $order_goods['price'];

			$member_youhui += ($order_goods['real_total'] - $order_goods['total']);
	    }

		unset($order_info['store_id']);
		//unset($order_info['type']);
		unset($order_info['email']);
		unset($order_info['shipping_city_id']);
		unset($order_info['shipping_country_id']);
		unset($order_info['shipping_province_id']);
		unset($order_info['comment']);
		unset($order_info['voucher_id']);
		//unset($order_info['voucher_credit']);
		unset($order_info['is_balance']);
		unset($order_info['lottery_win']);
		unset($order_info['ip']);
		unset($order_info['ip_region']);
		unset($order_info['user_agent']);

		$order_info['shipping_fare'] = round($order_info['shipping_fare'],2) < 0.01 ? '0.00':round($order_info['shipping_fare'],2) ;
		$order_info['total'] = round($order_info['total'],2)< 0.01 ? '0.00':round($order_info['total'],2)	;
		$order_info['real_total'] = round($shiji_total_money,2)+$order_info['shipping_fare'];
		$order_info['price'] = round($order_info['price'],2);
		$order_info['member_youhui'] = round($member_youhui,2) < 0.01 ? '0.00':round($member_youhui,2);


		$order_info['date_added'] = date('Y-m-d H:i:s', $order_info['date_added']);
		$need_data = array();

		//{{order.order_info.total + order.order_info.shipping_fare -  order.order_info.voucher_credit}}

		if($order_info['delivery'] =='pickup')
		{
			//$order_info['total'] = $order_info['total']  - $order_info['voucher_credit'];

		}else{
			//$order_info['total'] = $order_info['total'] + $order_info['shipping_fare'] - $order_info['voucher_credit'];

		}

		if($order_info['type'] == 'integral')
		{
			//$order_id
			$integral_order = M('integral_order')->field('score')->where( array('order_id' => $order_id) )->find();
			$need_data['score'] = intval($integral_order['score']);

		}
		$need_data['order_info'] = $order_info;
		$need_data['order_status_info'] = $order_status_info;
		$need_data['shipping_province'] = $shipping_province;
		$need_data['shipping_city'] = $shipping_city;
		$need_data['shipping_country'] = $shipping_country;
		$need_data['order_goods_list'] = $order_goods_list;

		//$order_info['order_status_id'] 13  平台介入退款
		$order_refund_historylist = array();
		$pingtai_deal = 0;

		//判断是否已经平台处理完毕
		$order_refund_historylist = M('order_refund_history')->where( array('order_id' => $order_id) )->order('addtime asc')->select();

		foreach($order_refund_historylist as $key => $val)
		{
			if($val['type'] ==3)
			{
				$pingtai_deal = 1;
			}
		}

		//order_refund
		$order_refund = M('order_refund')->where( array('order_id' => $order_id) )->find();
		if(!empty($order_refund))
		{
			$order_refund['addtime'] = date('Y-m-d H:i:s', $order_refund['addtime']);
		}

		$need_data['pick_up'] = $pick_up_info;



		if( empty($pick_order_info['qrcode']) )
		{
			//qrcode
			$jssdk = new \Lib\Weixin\Jssdk( $this->appid, $this->appsecret);

			$weqrcode = $jssdk->getWeQrcode($pick_order_info['pick_sn']);
			//保存图片

			$image_dir = ROOT_PATH.'Uploads/image/goods';
			$image_dir .= '/'.date('Y-m-d').'/';

			$file_path = C('SITE_URL').'Uploads/image/goods/'.date('Y-m-d').'/';
			$kufile_path = $dir.'/'.date('Y-m-d').'/';

			RecursiveMkdir($image_dir);
			$file_name = md5('qrcode_'.$pick_order_info['pick_sn'].time()).'.png';
			//qrcode
			file_put_contents($image_dir.$file_name, $weqrcode);
			//pick_order_info
			M('pick_order')->where( array('id' => $pick_order_info['id'] ) )->save( array('qrcode' => $file_path.$file_name) );
			$pick_order_info['qrcode'] = $file_path.$file_name;
		}

		$need_data['pick_order_info'] = $pick_order_info;

		//https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET

		//https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=ACCESS_TOKEN


		echo json_encode( array('code' => 0,'data' => $need_data,'pingtai_deal' => $pingtai_deal,'order_refund' => $order_refund ) );
		die();
	}

	public function order_all_show()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

		$member_id = $weprogram_token['member_id'];


		$order_all_id = I('get.id',0);


		$order_all_info = M('order_all')->where( array('id' => $order_all_id) )->find();
		$order_relate_list = M('order_relate')->where( array('order_all_id' => $order_all_id) )->select();

		$need_data = array();

		foreach($order_relate_list as $relate_val)
		{
			$order_id = $relate_val['order_id'];

			$order_info = M('order')->where( array('order_id' => $order_id, 'member_id' => $member_id) )->find();

			$order_status_info = M('order_status')->where( array('order_status_id' => $order_info['order_status_id']) )->find();

			$shipping_province = M('area')->where( array('area_id' => $order_info['shipping_province_id']) )->find();
			$shipping_city = M('area')->where( array('area_id' => $order_info['shipping_city_id']) )->find();
			$shipping_country = M('area')->where( array('area_id' => $order_info['shipping_country_id']) )->find();

			$order_goods_list = M('order_goods')->where( array('order_id' => $order_id) )->select();

			foreach($order_goods_list as $key => $order_goods)
			{
				$order_option_info = M('order_option')->field('value')->where( array('order_id' =>$order_id,'order_goods_id' => $order_goods['order_goods_id']) )->select();

				foreach($order_option_info as $option)
				{
					$vv['option_str'][] = $option['value'];
				}
				$order_goods['option_str'] = implode(',', $vv['option_str']);

				$order_goods['image']=C('SITE_URL').resize($order_goods['goods_images'], C('common_image_thumb_width'), C('common_image_thumb_height'));

				$order_goods['goods_images']= C('SITE_URL').'/Uploads/image/'.$order_goods['goods_images'];



				$store_info = M('seller')->field('s_true_name,s_logo')->where('s_id='.$order_goods['store_id'])->find();

				$store_info['s_logo'] = C('SITE_URL').'/Uploads/image/'.$store_info['s_logo'];

				$order_goods['store_info'] = $store_info;

				unset($order_goods['model']);
				unset($order_goods['rela_goodsoption_valueid']);
				unset($order_goods['comment']);

				$order_goods_list[$key] = $order_goods;
			}

			$shipping_address = $order_info['shipping_address'];
			$shipping_name = $order_info['shipping_name'];
			$telephone = $order_info['telephone'];


			unset($order_info['store_id']);
			unset($order_info['type']);
			unset($order_info['email']);
			unset($order_info['shipping_city_id']);
			unset($order_info['shipping_country_id']);
			unset($order_info['shipping_province_id']);
			unset($order_info['comment']);
			unset($order_info['voucher_id']);
			unset($order_info['voucher_credit']);
			unset($order_info['is_balance']);
			unset($order_info['lottery_win']);
			unset($order_info['ip']);
			unset($order_info['ip_region']);
			unset($order_info['user_agent']);

			$tmp_arr = array();
			$tmp_arr['order_goods_list'] = $order_goods_list;
			$tmp_arr['order_info'] = $order_info;
			$need_data[] = $tmp_arr;
		}


		//$this->order_status_info = $order_status_info;

		$need_datas = array();

		$need_datas['order_status_info'] = $order_status_info;
		$need_datas['shipping_province'] = $shipping_province;
		$need_datas['shipping_city'] = $shipping_city;
		$need_datas['shipping_country'] = $shipping_country;
		$need_datas['shipping_name'] = $shipping_name;
		$need_datas['telephone'] = $telephone;
		$need_datas['shipping_address'] = $shipping_address;

		$need_datas['order_list'] = $need_data;


		echo json_encode( array('code' => 0,'data' => $need_datas ) );
		die();
	}

	public function applogin()
	{
		$code = I('get.code');

		$url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appid}&secret={$this->appsecret}&js_code={$code}&grant_type=authorization_code";

		$open_str = $this->http_request($url);

		//"unionid":"o0v630n0_IgASw5-k7RGFO-i8oeI"
		$data = json_decode($open_str, true);

		$expires_time = time() + $data['expires_in'];
		$token = md5($data['openid'].time());
		S('wepro_openid_'.$token, $data['openid']);
		S('wepro_expires_time_'.$token, $expires_time);
		S('wepro_session_key_'.$token, $data['session_key']);
		S('wepro_unionid_'.$token, $data['unionid']);


		$werp_data = array();
		$werp_data['token'] = $token;

		$result = array('code' => 1, 'token' => $token,'openid' =>$data['openid']);
		echo json_encode($result);
		die();
	}
	/**
		小程序授权登录
	**/
	public function applogin_do()
	{
		$token = I('get.token');
		$data_json = file_get_contents('php://input');
		$data = json_decode($data_json, true);
		$user_info = $data['userinfo'];
		$share_id = $data['share_id'];

		$openid = S('wepro_openid_'.$token);
		$expires_time = S('wepro_expires_time_'.$token);
		$session_key = S('wepro_session_key_'.$token);
		$unionid = S('wepro_unionid_'.$token);


		$user_info['nickName'] = \Lib\Weixin\WeChatEmoji::clear($user_info['nickName']);
		$user_info['nickName'] = trim($user_info['nickName']);


		$member_info = M('member')->where( array('we_openid' =>$openid) )->find();

		if( !empty($unionid) && empty($member_info) )
		{
			$member_info = M('member')->where( array('unionid' =>$unionid) )->find();
		}

		if(!empty($member_info) )
		{
			 $data = array();
	         $data['member_id']	=	$member_info['member_id'];
			 $data['we_openid'] = trim($openid);
			 $data['avatar'] = trim($user_info['avatarUrl']);
	         $data['last_login_time']	=	time();
	         $data['login_count']		=	array('exp','login_count+1');
			 $data['last_login_ip']	=	get_client_ip();

	         M('Member')->save($data);
			$member_id  = $member_info['member_id'];

			$weprogram_token_data = array();
			$weprogram_token_data['token'] = $token;
			$weprogram_token_data['member_id'] = $member_id;
			$weprogram_token_data['session_key'] = $session_key;
			$weprogram_token_data['expires_in'] = $expires_time;

			M('weprogram_token')->add($weprogram_token_data);
		}else {
			$data = array();
		   	$data['email']= time().mt_rand(1,9999).'@lf.com';
			$data['uname']=trim($user_info['nickName']);
			$data['name']=trim($user_info['nickName']);
			$data['avatar']=trim($user_info['avatarUrl']);
			$data['openid'] = $openid;
			$data['we_openid'] = trim($openid);
			$data['unionid'] = trim($unionid);
			//share_id
			$data['share_id'] = $share_id;
			$data['reg_type'] = 'weprogram';

			$data['pwd']  = think_ucenter_encrypt($user_info['nickName'],C('PWD_KEY'));
		    $data['status']=1;
	        $data['create_time']	=	time();
			$data['last_login_ip']	=	get_client_ip();

	        $member_id= M('Member')->add($data);

			$weprogram_token_data = array();
			$weprogram_token_data['token'] = $token;
			$weprogram_token_data['member_id'] = $member_id;
			$weprogram_token_data['session_key'] = $session_key;
			$weprogram_token_data['expires_in'] = $expires_time;

			M('weprogram_token')->add($weprogram_token_data);

			if($share_id > 0)
			{
				$share_member = M('member')->field('we_openid')->where( array('member' => $share_id) )->find();

				$member_formid_info = M('member_formid')->where( array('member_id' => $share_id, 'state' => 0) )->find();
				//更新
				if(!empty($member_formid_info))
				{
					$template_data['keyword1'] = array('value' => $data['name'], 'color' => '#030303');
					$template_data['keyword2'] = array('value' => '普通客户', 'color' => '#030303');
					$template_data['keyword3'] = array('value' => date('Y-m-d H:i:s'), 'color' => '#030303');
					$template_data['keyword4'] = array('value' => '恭喜你，获得一位新成员', 'color' => '#030303');

					$pay_order_msg_info =  M('config')->where( array('name' => 'wxprog_member_take_in') )->find();
					$template_id = $pay_order_msg_info['value'];
					$url =C('SITE_URL');
					$pagepath = 'pages/dan/me';
					send_wxtemplate_msg($template_data,$url,$pagepath,$share_member['we_openid'],$template_id,$member_formid_info['formid']);
					M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
				}

			}
		}

		echo json_encode(array('code' =>1,'member_id' => $member_id));
		die();
	}

	/**
		获取用户信息
	**/
	public function me()
	{
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		if(empty($weprogram_token))
		{
			$data = array('code' =>1);
		} else{
			$member_info =  M('member')->field('name,avatar')->where( array('member_id' => $weprogram_token['member_id']) )->find();

			$user_info = array();
			$user_info['headimgurl'] = $member_info['avatar'];
			$user_info['nickname'] = $member_info['name'];

			$data = array('code' =>0, 'user_info' => $user_info);
		}

		echo json_encode($data);
		die();
	}

	private function xml($xml){
		$p = xml_parser_create();
		xml_parse_into_struct($p, $xml, $vals, $index);
		xml_parser_free($p);
		$data = "";
		foreach ($index as $key=>$value) {
			if($key == 'xml' || $key == 'XML') continue;
			$tag = $vals[$value[0]]['tag'];
			$value = $vals[$value[0]]['value'];
			$data[$tag] = $value;
		}
		return $data;
	}

	function http_request($url,$data = null,$headers=array())
	{
		$curl = curl_init();
		if( count($headers) >= 1 ){
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($curl, CURLOPT_URL, $url);

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	private function sign($data){
		$stringA = '';
		foreach ($data as $key=>$value){
			if(!$value) continue;
			if($stringA) $stringA .= '&'.$key."=".$value;
			else $stringA = $key."=".$value;
		}
		$wx_key = $this->pay_key;
		$stringSignTemp = $stringA.'&key='.$wx_key;
		return strtoupper(md5($stringSignTemp));
	}

	private function nonce_str(){
		$result = '';
		$str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
		for ($i=0;$i<32;$i++){
			$result .= $str[rand(0,48)];
		}
		return $result;
	}



}
