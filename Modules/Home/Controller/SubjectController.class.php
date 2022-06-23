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
namespace Home\Controller;

class SubjectController extends CommonController {


	/**

		kangjia
	**/
	public function assist_bargain_coupon()
	{
		$free_ad_list =  M('plugins_slider')->where( array('type' => 'wepro_bargain_ad') )->order('slider_id desc')->find();

		$goods_model = D('Home/goods');

		$per_page = 10;
	    $page = I('post.page',1);

	    $offset = ($page - 1) * $per_page;

		$now_time = time();
		$where = " g.status =1 and g.type != 'normal' and g.quantity >0  ";
		$where .= " and pg.begin_time < {$now_time} and pg.end_time >{$now_time} ";


		/**
		$sql = 'select g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.danprice,g.pin_count,g.image,g.fan_image,g.store_id,g.seller_count,g.virtual_count from  '.C('DB_PREFIX')."goods as g
			where    g.status =1 and g.quantity >0 and g.type = 'bargain'  order by g.sort_order desc limit {$offset},{$per_page}";
		**/

		$sortby = ' pg.id desc ';
		$list = D('Home/Pingoods')->get_bargaingoods_list('*', $where,$sortby,$offset,$per_page);

		/**
		$sql = 'select z.id,z.need_person,z.total_money as pinprice,z.deal_money,z.end_time,z.state,g.goods_id,g.name,g.quantity,g.price,g.danprice,g.image,g.fan_image,g.store_id,g.seller_count,g.virtual_count
				from  '.C('DB_PREFIX').'goods as g ,'.C('DB_PREFIX').'bargain_order as z
				where  z.member_id = '.$member_id.' and z.goods_id = g.goods_id   order by z.end_time  desc limit '.$offset.','.$per_page;

		$list = M()->query($sql);
		**/

		foreach($list as $key => $v){
			$list[$key]['seller_count'] += $v['virtual_count'];
			if(!empty($v['fan_image'])){
				$list[$key]['image']= $v['fan_image'];
			}
		}
		$this->list = $list;


		if($page > 1) {
		    $result = array('code' => 0);
		    if(!empty($list)) {
		        $result['code'] = 1;
		        $result['html'] = $this->fetch('Subject:assist_bargain_fetch');
		    }
		    echo json_encode($result);
		    die();
		}

		$success_order_list = M('bargain_order')->where( array( 'state' => 1 ) )->order('id desc')->limit(10)->select();

		if( !empty($success_order_list) )
		{
			foreach($success_order_list as $key => $val)
			{
				$mem_info = M('member')->field('uname,avatar')->where( array('member_id' => $val['member_id']) )->find();
				$gd_info = M('goods')->field('name')->where( array('goods_id' => $val['goods_id']) )->find();

				$val['uname']  = $mem_info['uname'];
				$val['avatar'] = $mem_info['avatar'];
				$val['goods_name'] = $gd_info['name'];
				$success_order_list[$key] = $val;
			}
		}
		$this->success_order_list = $success_order_list;

		$zan_notice = C('zan_notice');

		 $qian=array("\r\n");
		 $hou=array("@F@");
		 $zan_notice_str = str_replace($qian,$hou,$zan_notice);
		 $zan_notice_arr = explode('@F@',$zan_notice_str);

		$this->zan_notice_arr = $zan_notice_arr;

		$this->free_ad_list = $free_ad_list;
		$this->display();
	}
	/**
		砍价详情
	**/
	public function assist_bargain_coupon_detail()
	{
		//判断是否自己的，
		//判断是否砍过
		$is_me = false;
		$has_kan = false;

		$member_id = session('user_auth.uid');

		$id = I('get.id');

		$bargain_info = M('bargain_order')->where( array('id' => $id) )->find();

		if($bargain_info['member_id'] == $member_id)
		{
			$is_me = true;
		}

		$has_kan_count =  M('bargain_order_detail')->where( array('member_id' => $member_id,'bargain_order_id' => $id) )->count();
		if($has_kan_count >0)
		{
			$has_kan = true;
		}

		//end_time
		$now_time = time();

		$goods_info = M('goods')->field('name,price,seller_count,virtual_count,image,fan_image')->where( array('goods_id' => $bargain_info['goods_id']) )->find();

		$goods_info['image'] = resize($goods_info['image'],300,300);
		//fan_image
		if(!empty($goods_info['fan_image']))
		{
			$goods_info['image'] = resize($goods_info['fan_image'],300,300);
		}
		$goods_info['seller_count'] += $goods_info['virtual_count'];


		$kan_order_list = M('bargain_order_detail')->where( array('bargain_order_id' =>$id) )->order('addtime desc')->limit(15)->select();

		$this->kan_order_list = $kan_order_list;
		$this->bargain_info = $bargain_info;
		$this->goods_info = $goods_info;
		$this->is_me = $is_me;
		$this->has_kan = $has_kan;

		//member_id
		$member_info = M('member')->where( array('member_id' => $bargain_info['member_id']) )->find();

		$this->member_info = $member_info;


		$kan_description = C('kan_description');

		 $qian=array("\r\n");
		 $hou=array("@F@");
		 $kan_description_str = str_replace($qian,$hou,$kan_description);
		 $kan_description_str = explode('@F@',$kan_description_str);

		$this->kan_description_str = $kan_description_str;

		$kan_rules = C('kan_rules');

		 $qian=array("\r\n");
		 $hou=array("@F@");
		 $kan_rules_str = str_replace($qian,$hou,$kan_rules);
		 $kan_rules_str = explode('@F@',$kan_rules_str);

		$this->kan_rules_str = $kan_rules_str;

		$kan_person_count = C('kan_person_count');

		$this->kan_person_count = $kan_person_count;


		//随机商品 8 个

		$sql='SELECT goods_id,image,fan_image,seller_count,virtual_count,danprice,name FROM '.C('DB_PREFIX').'goods WHERE status=1 and type != "integral" and type != "bargain" and quantity>0 ORDER BY rand() LIMIT 0,12';
	    $list=M()->query($sql);

		$goods_model = D('Home/Goods');



	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    foreach ($list as $k => $v) {

	        //$list[$k]['image']=resize($v['image'], C('goods_related_thumb_width'), C('goods_related_thumb_height'));

			$price_arr = $goods_model->get_goods_price($v['goods_id']);

			$list[$k]['price'] = $price_arr['price'];

			$list[$k]['seller_count'] += $list[$k]['virtual_count'];


			//$val['danprice'] = $price_arr['price'];

			if(!empty($v['fan_image'])){
				$list[$k]['image']=resize($v['fan_image'], 480,480);
				//$list[$k]['image']='/Uploads/image/'.$v['fan_image'];
			}else {
				$list[$k]['image']=resize($v['image'], 480,480);
				//$list[$k]['image']='/Uploads/image/'.$v['image'];
			}
	    }

	    $this->related_goods=$list;



		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		$member_id = is_login();
		$hash_member_id = $hashids->encode($member_id);

		$bargain_order = M('bargain_order')->where( array('id' => $id) )->find();

		$goods_id = $bargain_order['goods_id'];

		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
       //"我正在参加拼多多官方砍价，砍到0元就可以免费拿啦，帮我砍一下吧"

	   $desc = "我正在参加".C('SITE_NAME')."砍价，砍到0元就可以免费拿啦，帮我砍一下吧";
	    $this->share_logo = C('SITE_URL').resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

		 if(!empty($goods_info['fan_image'])){
			$this->share_logo = C('SITE_URL').resize($goods_info['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}else {
			$this->share_logo = C('SITE_URL').resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}
		if(!empty($goods_description['water_image']))
		{
			$this->share_logo = C('SITE_URL').'/Uploads/image/'.$goods_description['water_image'];
		}
		//Subject/assist_bargain_coupon_detail/id/10.html
		$this->indexsharetitle = '['.C('SITE_NAME')."]我在砍价免费拿".$goods_info['name']."，帮我砍价，0元拿回家！";
		$this->url = C('SITE_URL')."/index.php?s=/Subject/assist_bargain_coupon_detail/id/{$id}/rmid/{$hash_member_id}";
		 $this->indexsharesummary =  $desc;

		  $appid_info 	=  M('config')->where( array('name' => 'APPID') )->find();
        $appsecret_info =  M('config')->where( array('name' => 'APPSECRET') )->find();
        $mchid_info =  M('config')->where( array('name' => 'MCHID') )->find();

        $weixin_config = array();
        $weixin_config['appid'] = $appid_info['value'];
        $weixin_config['appscert'] = $appsecret_info['value'];

		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);

		$signPackage = $jssdk->GetSignPackage();
		$this->signPackage = $signPackage;

		 //goods_id
		 $this->assign('signPackage',$this->signPackage);



		$this->display();
	}

	/**
		我的砍价
	**/
	public function assist_bargain_coupon_me()
	{
		$free_ad_list =  M('plugins_slider')->where( array('type' => 'free_ad') )->order('slider_id desc')->find();

		$member_id = session('user_auth.uid');

		$per_page = 10;
	    $page = I('post.page',1);

	    $offset = ($page - 1) * $per_page;

		$sql = 'select z.id,z.need_person,z.total_money as pinprice,z.deal_money,z.end_time,z.state,g.goods_id,g.name,g.quantity,g.price,g.danprice,g.image,g.fan_image,g.store_id,g.seller_count,g.virtual_count
				from  '.C('DB_PREFIX').'goods as g ,'.C('DB_PREFIX').'bargain_order as z
				where  z.member_id = '.$member_id.' and z.goods_id = g.goods_id   order by z.end_time  desc limit '.$offset.','.$per_page;

		$list = M()->query($sql);

		foreach($list as $key => $v){

			//$has_zan_count = M('zan_order_detail')->where( array('zan_order_id' => $v['id']) )->count();
			//$list[$key]['has_zan_count'] = $has_zan_count;

			$list[$key]['seller_count'] += $v['virtual_count'];
			if(!empty($v['fan_image'])){
				$list[$key]['image']= $v['fan_image'];
			}
		}
		$this->list = $list;

		if($page > 1) {
		    $result = array('code' => 0);
		    if(!empty($list)) {
		        $result['code'] = 1;
		        $result['html'] = $this->fetch('Subject:assist_bargain_me_fetch');
		    }
		    echo json_encode($result);
		    die();
		}


		$this->free_ad_list = $free_ad_list;
		$this->display();
	}
	public function get_mult_sku()
	{
		$goods_id = I('get.goods_id');
		$sku_arr_str = I('get.sku_arr_str');
		//goods_id
		$goods_option_mult_value_info = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $sku_arr_str,'goods_id' => $goods_id) )->find();

		$goods_option_mult_value_info['image'] = resize($goods_option_mult_value_info['image'],200,200);

		echo json_encode( array('code' => 0 , 'data' => $goods_option_mult_value_info) );
		die();

	}

	public function get_user_addresslist()
	{

		$model= D('Home/order');

		$list=$model->get_all_address(session('user_auth.uid'));

		if(!empty($list))
		{
			foreach($list as &$address)
			{

					$province_info = M('area')->where( array('area_id' => $address['province_id']) )->find();
					$city_info = M('area')->where( array('area_id' => $address['city_id']) )->find();
					$country_info = M('area')->where( array('area_id' => $address['country_id']) )->find();
					$address['province_name'] = $province_info['area_name'];
					$address['city_name'] = $city_info['area_name'];
					$address['country'] = $country_info['area_name'];

			}
		}
		//var_dump($list);die();
		$this->address_list = $list;


		$html = $this->fetch('Subject:assist_free_addresslist');

		echo json_encode( array('code' => 1,'html' => $html) );
		die();
	}

	public function get_option_sku()
	{
		$goods_id = I('get.goods_id');

		//判断当前商品是否有进行中的二维码，如果有，那么直接返回出来
		$member_id = session('user_auth.uid');
		$now_time = time();

		$old_zan_order = M('bargain_order')->where("member_id={$member_id} and goods_id={$goods_id} and state =0 and begin_time < {$now_time} and end_time > {$now_time}")->find();


		if( !empty($old_zan_order) )
		{
			//一个客户当前一个商品 只能发出去一个二维码
			echo json_encode( array('code' => 3, 'need_person' => $old_zan_order['need_person']) );
			die();
		}

		$goods_contr = A('Home/Goods');
		$this->options=$goods_contr->get_goods_options($goods_id);

		$goods_info =  M('goods')->field('price,image,fan_image')->where( array('goods_id' => $goods_id) )->find();

		$goods_info['image'] = resize($goods_info['image'],200,200);
		if( !empty($goods_info['fan_image']) )
		{
			$goods_info['image'] = resize($goods_info['fan_image'],200,200);
		}

		$this->goods_info = $goods_info;

		$goods_option_mult_value = M('goods_option_mult_value')->order('pin_price asc')->where( array('goods_id' =>$goods_id) )->select();

		$html = '';
		if( !empty($goods_option_mult_value) )
		{
			foreach($goods_option_mult_value as $key => $val)
			{
				$val['image'] = resize($val['image'],200,200);
				//
				if($val['pin_price'] < 0.01)
				{
					$val['pin_price'] = $goods[0]['pinprice'];
				}
				if($val['dan_price'] < 0.01)
				{
					$val['dan_price'] = $goods[0]['danprice'];
				}
				$goods_option_mult_value[$key] = $val;
			}
			$this->goods_option_mult_value = $goods_option_mult_value;
			$html = $this->fetch('Subject:assist_free_option');

			echo json_encode( array('code' => 1,'html' => $html) );
			die();
		}else{
			echo json_encode( array('code' => 0) );
			die();
		}

	}

	private function _curl_get_avatar($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	/**
		开始砍价表_bang_kan_order($bargain_order_id,$member_id,$is_self = false, $just_update = false )
	**/
	private function _bang_kan_order($bargain_order_id,$is_self = false, $just_update = false )
	{
		$member_id = session('user_auth.uid');
		//检测是否砍过了
		$has_order = M('bargain_order_detail')->where( array('member_id'=>$member_id, 'bargain_order_id' =>$bargain_order_id) )->find();

		if(!empty($has_order) && !$just_update)
		{
			return 1;//已经帮忙砍过
		}

		//活动是否正常
		$bargain_order_info = M('bargain_order')->where( array('id' => $bargain_order_id) )->find();
		if( $bargain_order_info['state'] != 0 )
		{
			return 2; //活动已经成功，或者已经结束
		}

		$now_time = strtotime( date('Y-m-d').' 00:00:00' );
		$end_time = $now_time + 86400;

		$kan_person_count = C('kan_person_count');

		if(!$is_self)
		{
			//检测今天帮别人砍价的次数
			//addtime
			//bargain_order_detail
			$kan_count = M('bargain_order_detail')->where( array('addtime' =>array('between', array($now_time,$end_time) ), 'member_id'=>$member_id,'is_sleft' =>0 ) )->count();

			//$condition['id'] = array(between,array('1','8'));
			if($kan_person_count >0 && $kan_count >= $kan_person_count)
			{
				return 3;//今天已经不能再帮别人砍价
			}
		}

		//开始砍价流程
		if($bargain_order_info['need_person'] > 0)
		{
			$per_money = $bargain_order_info['deal_money'] / $bargain_order_info['need_person'];
			if($bargain_order_info['need_person'] >2)
			{
				$per_money = $per_money * 2;
			}
		}else{
			$per_money = round($bargain_order_info['deal_money'] ,2);
		}

		$min = 1;
		$max = $per_money * 100;

		$ran_money = round( mt_rand($min,$max) / 100 , 2) ;

		if($bargain_order_info['need_person'] < 0)
		{
			$win_rd = mt_rand(1,9);
			if($win_rd >=5)
			{
				$ran_money = $bargain_order_info['deal_money'];
			}
		}




		$member_info = M('member')->field('uname,avatar')->where( array('member_id' => $member_id) )->find();

		//TODO 插入砍价详细订单
		if($just_update)
		{
			$res = M('bargain_order_detail')->where( array('bargain_order_id' => $bargain_order_id,'member_id' =>$member_id ) )->setInc('kan_money',$ran_money);
			//setInc
			M('bargain_order')->where( array('id' => $bargain_order_id) )->save( array('is_share' => 1) );

		} else{
			$ins_data = array();
			$ins_data['member_id'] = $member_id;
			$ins_data['kan_money'] = $ran_money;
			$ins_data['bargain_order_id'] = $bargain_order_id;
			$ins_data['addtime'] = time();
			$ins_data['is_sleft'] = $is_self ? 1: 0;
			$ins_data['avatar'] = $member_info['avatar'];
			$ins_data['nickname'] = $member_info['uname'];

			$res = M('bargain_order_detail')->add($ins_data);
		}



		if($res)
		{
			if(!$just_update)
			{
				M('bargain_order')->where( array('id' => $bargain_order_id) )->setDec('need_person',1);
			}
			M('bargain_order')->where( array('id' => $bargain_order_id) )->setDec('deal_money',$ran_money);


			$bargain_order_info = M('bargain_order')->where( array('id' => $bargain_order_id) )->find();

			if($bargain_order_info['deal_money'] <=0 )
			{

				//TODO send msg
				$model = D('Home/Goods');
				$order_id =  $model->_zan_bargain_order($bargain_order_id);

				//M('zan_order')->where( array('id' => $zan_order_id) )->save( array('order_id' => $order_id,'state' => 1) );
				M('bargain_order')->where( array('id' => $bargain_order_id) )->save( array('order_id' => $order_id,'state' => 1) );

				//add order

				$or_member_info = M('member')->field('openid')->where( array('member_id' => $bargain_order_info['member_id']) )->find();
				$or_open_id = $or_member_info['openid'];
				//$or_open_id = 'o0n_HwcGIfwf5b8PN8-gmNfsJBLA';

				$goods_info = M('goods')->field('name')->where( array('goods_id' => $bargain_order_info['goods_id']) )->find();

				$first_title = "您参与：".$goods_info['name'];
				$keyword1 = '您的活动已砍价成功';
				$template_data = array();
				$template_data['first'] = array('value' => $first_title, 'color' => '#173177');
				$template_data['keyword1'] = array('value' => $keyword1, 'color' => '#173177');
				$template_data['keyword2'] = array('value' => '点击查看订单详情', 'color' => '#173177');
				$template_data['remark'] = array('value' => '当前活动已成功，等待发货', 'color' => '#173177');

				$quan_msg_info =  M('config')->where( array('name' => 'zanSuccessNotice') )->find();
				$template_id = $quan_msg_info['value'];

				//$url = C('SITE_URL')."/index.php?s=/user/myvoucherlist.html";
				$url = C('SITE_URL')."/index.php?s=/Order/info/id/{$order_id}.html";
				send_template_msg($template_data,$url,$or_open_id,$template_id);

			}
			return 0;
		}else{
			return 4;//砍价失败
		}

	}

	/**
		分享成功后，砍
	**/
	public function share_success_kan()
	{
		$id = I('get.id');
		$member_id = session('user_auth.uid');
		$bargain_order =  M('bargain_order')->where( array('id' => $id) )->find();
		//is_share
		if($bargain_order['is_share'] == 0 && $member_id == $bargain_order['member_id'] )
		{
			$order_detail = M('bargain_order_detail')->where( array('member_id' => $member_id, 'bargain_order_id' => $id) )->find();
			$old = $order_detail['kan_money'];

			$state =  $this->_bang_kan_order($id,true,true );
			$bargain_order =  M('bargain_order')->field('total_money,deal_money')->where( array('id' => $id) )->find();

			$bargain_order['kan_money'] = round($bargain_order['total_money'] - $bargain_order['deal_money'],2);
			$bargain_order['deal_money'] = round($bargain_order['deal_money'],2);

			$order_detail = M('bargain_order_detail')->where( array('member_id' => $member_id, 'bargain_order_id' => $id) )->find();
			$order_detail['kan_money'] = round($order_detail['kan_money'] - $old, 2);

			$li_html = "";
			$li_html.= '<li class="user-item">';
			$li_html.= '	<img src="'.$order_detail['avatar'].'" />';
			$li_html.= '	<div class="text">';
			$li_html.= '	 <p class="nickname">'.$order_detail['nickname'].'</p>';
			$li_html.= '	 <p class="hint">看我青龙偃月刀</p>';
			$li_html.= '	</div>';
			$li_html.= '	<div class="amount">';
			$li_html.= '	 <div class="knives knives-1">';
			$li_html.= '	  <div class="knife"></div>';
			$li_html.= '	 </div>';
			$li_html.= '	 砍掉';
			$li_html.= '	 <i class="detail ">'.$order_detail['kan_money'].'</i>';
			$li_html.= '	 元';
			$li_html.= '	</div>';
			$li_html.= '</li>';

			echo json_encode( array('code' => 0,'li_html' => $li_html, 'order_detail' => $order_detail, 'bargain_order' => $bargain_order) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}
	}

	/**
		帮忙别人砍价
	**/
	public function kan_others_bargain()
	{
		$id = I('get.id',0);
		$member_id = session('user_auth.uid');
		$state =  $this->_bang_kan_order($id,false);
		//1;//已经帮忙砍过
		//2; //活动已经成功，或者已经结束
		//3;//今天已经不能再帮别人砍价
		//4 砍价失败
		//0成功
		$appid_info 	=  M('config')->where( array('name' => 'APPID') )->find();
		$appsecret_info =  M('config')->where( array('name' => 'APPSECRET') )->find();
		$weixin_config = array();
		$weixin_config['appid'] = $appid_info['value'];
		$weixin_config['appscert'] = $appsecret_info['value'];

		$member_info = M('member')->where( array('member_id' => $member_id) )->find();

		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);
		$is_sub = $jssdk->cgigetinfo($member_info['openid']);

		//echo json_encode();
		//die();
		if(!$is_sub)
		{
			echo json_encode( array('code' => 5) );
			die();
		}
		$this->is_sub = $is_sub;


		if($state > 0)
		{
			$msg = '';
			switch($state)
			{
				case 1 :
					$msg = '已经帮忙砍过了';
					break;
				case 2:
					$msg = '活动已经结束';
					break;
				case 3:
					$msg = '今天已经不能再帮别人砍价了';
					break;
				case 4:
					$msg = '砍价失败';
					break;
			}
			echo json_encode( array('code' => $state, 'msg' => $msg) );
			die();
		}else{
			$bargain_order =  M('bargain_order')->field('total_money,deal_money')->where( array('id' => $id) )->find();

			$bargain_order['kan_money'] = round($bargain_order['total_money'] - $bargain_order['deal_money'],2);
			$bargain_order['deal_money'] = round($bargain_order['deal_money'],2);

			$order_detail = M('bargain_order_detail')->where( array('member_id' => $member_id, 'bargain_order_id' => $id) )->find();
			$order_detail['kan_money'] = round($order_detail['kan_money'], 2);

			$li_html = "";
			$li_html.= '<li class="user-item">';
			$li_html.= '	<img src="'.$order_detail['avatar'].'" />';
			$li_html.= '	<div class="text">';
			$li_html.= '	 <p class="nickname">'.$order_detail['nickname'].'</p>';
			$li_html.= '	 <p class="hint">看我青龙偃月刀</p>';
			$li_html.= '	</div>';
			$li_html.= '	<div class="amount">';
			$li_html.= '	 <div class="knives knives-1">';
			$li_html.= '	  <div class="knife"></div>';
			$li_html.= '	 </div>';
			$li_html.= '	 砍掉';
			$li_html.= '	 <i class="detail ">'.$order_detail['kan_money'].'</i>';
			$li_html.= '	 元';
			$li_html.= '	</div>';
			$li_html.= '</li>';

			echo json_encode( array('code' => 0,'li_html' => $li_html, 'order_detail' => $order_detail, 'bargain_order' => $bargain_order) );
			die();
		}
	}

	/**
		自己开启一个砍价
	**/
	public function get_user_goods_bargain()
	{

		$member_id = session('user_auth.uid');
		$address_id = I('post.address_id');
		$goods_id = I('post.goods_id');
		$need_optionc = I('post.need_optionc');



		//寻找现在是否有未完成的订单，如果有的话，那么就不要继续生成了
		//$goods_info = M('goods')->field('pin_count,bargain_count,pin_hour,image,price,name')->where( array('goods_id' => $goods_id) )->find();
		$goods_model = D('Home/Goods');

		//寻找现在是否有未完成的订单，如果有的话，那么就不要继续生成了
		$goods_info = M('goods')->field('image,name')->where( array('goods_id' => $goods_id) )->find();
		$bargain_goods_info = M('bargain_goods')->where( array('goods_id' => $goods_id) )->find();

		//pin_count,pin_hour,
		//pin_price

		$now_time = time();

		$old_zan_order = M('bargain_order')->where("member_id={$member_id} and goods_id={$goods_id} and state =0 and begin_time < {$now_time} and end_time > {$now_time}")->find();

		if( !empty($old_zan_order) )
		{
			//一个客户当前一个商品 只能发出去一个二维码
			echo json_encode( array('code' => 0, 'id' => $old_zan_order['id']) );
			die();
		}
		//zan_order
		$zan_order_data = array();
		$zan_order_data['member_id'] = $member_id;
		$zan_order_data['goods_id'] = $goods_id;
		$zan_order_data['sku_str'] = $need_optionc;
		$zan_order_data['state'] = 0;

		$price = $bargain_goods_info['bargain_price'];
		 //goods_option_mult_value
		 if( !empty($need_optionc) )
		 {
			$goods_option_mult_value = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $need_optionc,'goods_id' =>$goods_id) )->find();

			if(!empty($goods_option_mult_value))
			{
				$price = $goods_option_mult_value['pin_price'];
			}
		 }

		$zan_order_data['need_person'] = empty($bargain_goods_info['bargain_count']) ? 1: $bargain_goods_info['bargain_count'];
		$zan_order_data['total_person'] = empty($bargain_goods_info['bargain_count']) ? 1: $bargain_goods_info['bargain_count'];
		$zan_order_data['deal_money'] = $price;
		$zan_order_data['total_money'] = $price;
		$zan_order_data['begin_time'] = $now_time;
		$zan_order_data['end_time'] = $now_time  + 3600 * $bargain_goods_info['hour'];
		$zan_order_data['add_time'] = time();
		$zan_order_data['order_id'] = 0;
		$zan_order_data['address_id'] = $address_id;

		$zan_order_id =  M('bargain_order')->add($zan_order_data);

		//bargain_order_detail
		//member_id kan_money  bargain_order_id  addtime  avatar nickname
		$state =  $this->_bang_kan_order($zan_order_id,true);


		//subject/assist_bargain_coupon_detail/id/5.html
		$redir_url = U('subject/assist_bargain_coupon_detail' , array('id' => $zan_order_id));



		echo json_encode( array('code' =>0, 'state' => $state,'gourl' => $redir_url,'bargain_order_id' => $zan_order_id) );
		die();
	}


	//进行中
	public function index(){
	    $per_page = 10;
	    $page = I('post.page',1);

	    $offset = ($page - 1) * $per_page;

	    $subject_id =  I('get.id',0);
	    $this->subject_id = $subject_id;
	    $this->fromindex = I('get.fromindex',0);

	    $subject = M('subject')->where( array('id' => $subject_id) )->find();

		$this->subject = $subject;
	    if($subject['type'] == 'niyuan' && $this->fromindex == 1)
	    {
	        $niyuansubjects = M('subject')->where( 'id != '.$subject_id.' and type="niyuan" and begin_time<'.time().' and end_time > '.time() )->order('id asc')->limit(4)->select();
	        $this->niyuansubjects = $niyuansubjects;
	    }

	   if($subject){
	       $sql = 'select sg.state,g.goods_id,g.name,g.quantity,g.price,g.danprice,g.image,g.fan_image,g.store_id,g.seller_count from  '.C('DB_PREFIX')."subject_goods as sg , ".C('DB_PREFIX')."goods as g
	        where  subject_id = ".$subject['id']." and  sg.state =1 and sg.goods_id = g.goods_id and g.status =1 and g.quantity >0  order by sg.id asc limit {$offset},{$per_page}";

	       $list = M()->query($sql);

		   foreach($list as $key => $v){
			   if(!empty($v['fan_image'])){
					$list[$key]['image']= $v['fan_image'];
				}
			}
	       $this->list = $list;
	   } else {
	       $this->list = array();
	   }

	   $type_template = array();
	   $type_template['normal'] = array('html' => 'normal_index',
	       'fetch_html' => 'Subject:normal_ajax_fetch');
	   $type_template['zeyuan'] = array(
	       'html' => 'zeyuan_index',
	       'fetch_html' => 'Subject:zeyuan_ajax_fetch'
	   );
	   $type_template['niyuan'] = array(
	       'html' => 'niyuan_index',
	       'fetch_html' => 'Subject:niyuan_ajax_fetch'
	   );
	   $type_template['oneyuan'] = array(
	       'html' => 'normal_index',
	       'fetch_html' => 'Subject:normal_ajax_fetch'
	   );

		if($page > 1) {
		    $result = array('code' => 0);
		    if(!empty($list)) {
		        $result['code'] = 1;
		        $result['html'] = $this->fetch($type_template[$subject['type']]['fetch_html']);
		    }
		    echo json_encode($result);
		    die();
		}

		$this->display($type_template[$subject['type']]['html']);
	}

	//未开始
	public function wait()
	{
	    $per_page = 10;
	    $page = I('post.page',1);

	    $offset = ($page - 1) * $per_page;

	    $sql = 'select sg.state,sg.begin_time,sg.end_time,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.image from  '.C('DB_PREFIX')."spike_goods as sg , ".C('DB_PREFIX')."goods as g
	    where sg.state =1 and sg.goods_id = g.goods_id and g.status =1 and g.quantity >0 and sg.begin_time > ".time()." order by sg.begin_time asc limit {$offset},{$per_page}";

	    $list = M()->query($sql);

	    foreach ($list as $k => $v) {
	        $list[$k]['image']=resize($v['image'], C('spike_thumb_width'), C('spike_thumb_height'));
	    }


	    $this->list = $list;


        $result = array('code' => 0);
        if(!empty($list)) {
            $result['code'] = 1;
            $result['html'] = $this->fetch('Widget:spike_ajax_wait_fetch');
        }
        echo json_encode($result);
        die();

	}
	public function over()
	{
	    $per_page = 10;
	    $page = I('post.page',1);

	    $offset = ($page - 1) * $per_page;

	    $sql = 'select sg.state,sg.begin_time,sg.end_time,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.image from  '.C('DB_PREFIX')."spike_goods as sg , ".C('DB_PREFIX')."goods as g
	    where sg.state =1 and sg.goods_id = g.goods_id  and g.quantity =0  order by sg.begin_time asc limit {$offset},{$per_page}";

	    $list = M()->query($sql);

	    foreach ($list as $k => $v) {
	        $list[$k]['image']=resize($v['image'], C('spike_thumb_width'), C('spike_thumb_height'));
	    }


	    $this->list = $list;


	    $result = array('code' => 0);
	    if(!empty($list)) {
	        $result['code'] = 1;
	        $result['html'] = $this->fetch('Widget:spike_ajax_over_fetch');
	    }
	    echo json_encode($result);
	    die();
	}



}
