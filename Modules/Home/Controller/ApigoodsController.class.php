<?php

// $Id:$
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
class ApigoodsController extends CommonController {
    protected function _initialize() {
        parent::_initialize();
        $this->cur_page = 'apigoods';
    }

	public function get_goods_option_data()
	{
		$id = I('get.id',28);
		$token = I('get.token');
		$sku_str = I('get.sku_str','45_53');
		//45_53

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];


		$goods_model = D('Home/Goods');

		$goods_option_mult_value = M('goods_option_mult_value')->where(array(
            'goods_id' => $id
        ))->order('id asc')->select();

        $goods_option_mult_value_ref = array();
        foreach ($goods_option_mult_value as $key => $val) {
            $val['image'] = C('SITE_URL') . resize($val['image'], 200, 200);
            $goods_option_mult_value[$key] = $val;
            $goods_option_mult_value_ref[$val['rela_goodsoption_valueid']] = $val;
        }

		$need_data = array();

		$level_info = $goods_model->get_member_level_info($member_id, $id);
		$member_disc = 100;
		if( !empty($level_info) )
		{
			$member_disc = $level_info['member_discount'];
		}

		$max_member_level = M('member_level')->order('level desc')->find();

		/**
			$max_member_level = M('member_level')->order('level desc')->find();

			$goods[0]['memberprice'] = round( ($goods[0]['danprice'] * $member_disc) / 100 ,2);
			$max_get_money = round( ($goods[0]['danprice'] * (100 - $max_member_level['discount']) ) / 100 ,2);
			if(!empty($pin_info))
			{
				$pin_info['member_pin_price'] = round( ($pin_info['pin_price'] * $member_disc) / 100 ,2);
				$max_get_money = round( ($pin_info['pin_price'] * (100 - $max_member_level['discount']) ) / 100 ,2);
			}
		**/


		$goods_option_mult_value_ref[$sku_str]['member_pin_price'] =  round( ($goods_option_mult_value_ref[$sku_str]['pin_price'] * $member_disc) / 100 ,2);
		$goods_option_mult_value_ref[$sku_str]['memberprice'] =  round( ($goods_option_mult_value_ref[$sku_str]['dan_price'] * $member_disc) / 100 ,2);

		$goods_option_mult_value_ref[$sku_str]['max_member_pin_price'] = 0;
		$goods_option_mult_value_ref[$sku_str]['max_memberprice'] = 0;

		if( !empty($max_member_level) )
		{
			$goods_option_mult_value_ref[$sku_str]['max_member_pin_price'] =  round( ($goods_option_mult_value_ref[$sku_str]['pin_price'] * (100 - $max_member_level['discount']) )  / 100 ,2);
			$goods_option_mult_value_ref[$sku_str]['max_memberprice'] =  round( ($goods_option_mult_value_ref[$sku_str]['dan_price'] * (100 - $max_member_level['discount']) )  / 100 ,2);
		}

        $need_data['value'] = $goods_option_mult_value_ref[$sku_str];

		echo json_encode( array('code' =>0 , 'data' =>$need_data ) );
		die();

	}

	public function seller_info2()
	{
		$seller_id = I('get.id');
		$seller_info = M('seller')->field('s_true_name,s_logo')->where( array('s_id' => $seller_id) )->find();

		$seller_info['s_logo'] = C('SITE_URL').'Uploads/image/'.$seller_info['s_logo'];
		$seller_model = D('Home/Seller');

		$seller_count = $seller_model->getStoreSellerCount($seller_id);

		$goods_count = M('goods')->where( array('store_id' => $seller_id) )->count();

		$store_class_list =  M('store_bind_class')->where( array('seller_id' => $seller_id) )->order('bid asc')->select();

		$store_class_ids = array();
		foreach($store_class_list as $class_val)
		{
			$store_class_ids[] = $class_val['class_1'];
		}

		$category_list = array();

		if( !empty($store_class_ids) )
		{
			$category_list = M('goods_category')->field('id,name')->where( array('id' => array('in', $store_class_ids) ) )->select();
		}

		$seller_info['seller_count'] = $seller_count;
		$seller_info['goods_count'] = $goods_count;

		$need_data = array();
		$need_data['seller_info'] = $seller_info;
		$need_data['category_list'] = $category_list;

		echo json_encode( array('code' => 0 , 'data' => $need_data) );
		die();


	}
	public function search()
	{
		/**
		C('SITE_URLS').resize($val['fan_image'], 220, 220)
		**/

		$parent_list = M('goods_category')->where( array('pid' =>0, 'is_search' =>1) )->order('c_sort_order desc,sort_order desc')->select();
	    //logo
	    foreach($parent_list as $key => $val)
	    {
			if( !empty($val['logo']) )
			{
				$val['logo'] = C('SITE_URL').resize($val['logo'], 220, 220);
			}
	        $child_list = M('goods_category')->where( array('pid' => $val['id'], 'is_search' =>1) )->order('c_sort_order desc,sort_order desc')->select();
	        foreach($child_list as $kk => $vv)
			{
				if( !empty($vv['logo']) )
				{
					$vv['logo'] = C('SITE_URL').resize($vv['logo'], 220, 220);
				}
				$child_list[$kk] = $vv;
			}
			$val['child_list'] = $child_list;
	        $parent_list[$key] = $val;
	    }

		echo json_encode( array('code' => 0, 'data' => $parent_list) );
		die();

	    //$this->parent_list = $parent_list;
	}

	/**
		获取积分兑换商品
	**/
	public function get_integral_goods()
	{
		$goods_model = D('Home/Goods');

		$pre_page = 10;
		$page = I('get.page',1);

		$condition = array('type' => 'integral', 'status'=>1,'quantity' =>array('gt',0) );

		$offset = ($page -1) * $pre_page;
		$list = M('goods')->where($condition)->order('seller_count+virtual_count desc,goods_id asc')->limit($offset,$pre_page)->select();

		if(!empty($list)) {
			foreach($list as $key => $v){

				$goods_price_arr = $goods_model->get_goods_price($v['goods_id']);
				$list[$key]['pinprice'] = $goods_price_arr['price'];

				$list[$key]['image']=C('SITE_URL'). resize($v['image'], 400, 400);
			}
		}
		foreach($list as $key => $val)
		{
		    $val['seller_count'] += $val['virtual_count'];
		    $list[$key] = $val;
		}

		 $integral_rules = C('integral_description');

		 $qian=array("\r\n");
		 $hou=array("@F@");
		 $integral_rules_str = str_replace($qian,$hou,$integral_rules);
		 $integral_rules_str = explode('@F@',$integral_rules_str);

		if( !empty($list) )
		{
			echo json_encode( array('code' => 0, 'data' => $list , 'integral_rules_str' => $integral_rules_str ) );
			die();
		} else {
			echo json_encode( array('code' => 1 , 'integral_rules_str' => $integral_rules_str) );
			die();
		}
	}

	public function get_category_goods()
	{
		$goods_model = D('Home/Goods');

		$pre_page = 10;
		$page = I('get.page',1);
		$id = I('get.id',0);


		$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$id} or class_id2 ={$id} or class_id3 = {$id}  ")->field('goods_id')->select();

		$ids_arr = array();
		foreach($goods_ids_arr as $val){
			$ids_arr[] = $val['goods_id'];
		}
		$ids_str = implode(',',$ids_arr);

		//lottery  pintuan oneyuan normal
		$condition = array('goods_id' => array('in',$ids_str),'type' => array('in', array('normal','oneyuan','lottery','pintuan') ) , 'status'=>1,'quantity' =>array('gt',0) );

		$offset = ($page -1) * $pre_page;
		$list = M('goods')->where($condition)->order('seller_count+virtual_count desc,goods_id asc')->limit($offset,$pre_page)->select();

		if(!empty($list)) {
			foreach($list as $key => $v){

				$goods_price_arr = $goods_model->get_goods_price($v['goods_id']);
				$list[$key]['pinprice'] = $goods_price_arr['price'];
				//
				//$goods[0]['danprice'] = $goods_price_arr['danprice'];
				//$price_dol = explode('.', $goods_price_arr['pin_price']);

				$list[$key]['image']=C('SITE_URL'). resize($v['image'], 400, 400);
			}
		}
		foreach($list as $key => $val)
		{
		    $val['seller_count'] += $val['virtual_count'];
		    $list[$key] = $val;
		}
		if( !empty($list) )
		{
			echo json_encode( array('code' => 0, 'data' => $list ) );
			die();
		} else {
			echo json_encode( array('code' => 1) );
			die();
		}


	}

	/**
		我的助力
	**/
	public function assist_free_coupon_me()
	{
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		$free_ad_list =  M('plugins_slider')->where( array('type' => 'free_ad') )->order('slider_id desc')->find();


		$per_page = 10;
	    $page = I('get.page',1);

	    $offset = ($page - 1) * $per_page;

		$sql = 'select z.id,z.need_person,z.total_money as pinprice,z.deal_money,z.end_time,z.state,g.goods_id,g.name,g.quantity,g.price,g.danprice,g.image,g.fan_image,g.store_id,g.seller_count,g.virtual_count
				from  '.C('DB_PREFIX').'goods as g ,'.C('DB_PREFIX').'bargain_order as z
				where  z.member_id = '.$member_id.' and z.goods_id = g.goods_id   order by z.end_time  desc limit '.$offset.','.$per_page;

		$list = M()->query($sql);


		foreach($list as $key => $v){

			//$has_zan_count = M('zan_order_detail')->where( array('zan_order_id' => $v['id']) )->count();
			//$list[$key]['has_zan_count'] = $has_zan_count;


			if($v['state'] == 0 && $v['end_time'] <  time())
			{
				$list[$key]['state'] = 2;
			}

			$list[$key]['seller_count'] += $v['virtual_count'];

			$list[$key]['image']= C('SITE_URL').resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

		}


		if(!empty($list))
		{
			echo json_encode( array('code' => 0, 'data' => $list ) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}
	/**
		砍价详情
	**/

	public function bargain_detail()
	{
		$is_me = false;
		$has_kan = false;

		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		$id = I('get.id');

		$bargain_info = M('bargain_order')->where( array('id' => $id) )->find();

		$bargain_info['already_money'] = round($bargain_info['total_money']-$bargain_info['deal_money'],2);

		if($bargain_info['member_id'] == $member_id)
		{
			$is_me = true;
		}

		if($bargain_info['state'] != 1 && $bargain_info['end_time'] > time() )
		{
			$bargain_info['state'] = 3;
		}

		$has_kan_count =  M('bargain_order_detail')->where( array('member_id' => $member_id,'bargain_order_id' => $id) )->count();
		if($has_kan_count >0)
		{
			$has_kan = true;
		}

		//end_time
		$now_time = time();

		$goods_info = M('goods')->field('name,price,seller_count,virtual_count,image,fan_image')->where( array('goods_id' => $bargain_info['goods_id']) )->find();

		$goods_info['image'] = C('SITE_URL').resize($goods_info['image'],300,300);
		//fan_image
		if(!empty($goods_info['fan_image']))
		{
			$goods_info['image'] = C('SITE_URL').resize($goods_info['fan_image'],300,300);
		}
		$goods_info['seller_count'] += $goods_info['virtual_count'];

		$kan_order_list = M('bargain_order_detail')->where( array('bargain_order_id' =>$id) )->order('addtime desc')->limit(15)->select();

		$need_data = array();
		$need_data['kan_order_list'] = $kan_order_list;
		$need_data['bargain_info'] = $bargain_info;
		$need_data['goods_info'] = $goods_info;
		$need_data['is_me'] = $is_me;
		$need_data['has_kan'] = $has_kan;

		//$this->kan_order_list = $kan_order_list;
		//$this->bargain_info = $bargain_info;
		//$this->goods_info = $goods_info;
		//$this->is_me = $is_me;
		//$this->has_kan = $has_kan;

		//member_id
		$member_info = M('member')->where( array('member_id' => $bargain_info['member_id']) )->find();

		$need_data['member_info'] = $member_info;

		//$this->member_info = $member_info;


		$kan_description = C('kan_description');
		$qian=array("\r\n");
		$hou=array("@F@");
		$kan_description_str = str_replace($qian,$hou,$kan_description);
		$kan_description_str = explode('@F@',$kan_description_str);

		//$this->kan_description_str = $kan_description_str;
		//$this->kan_description_str = $kan_description_str;

		$need_data['kan_description_str'] = $kan_description_str;

		$kan_rules = C('kan_rules');

		 $qian=array("\r\n");
		 $hou=array("@F@");
		 $kan_rules_str = str_replace($qian,$hou,$kan_rules);
		 $kan_rules_str = explode('@F@',$kan_rules_str);

		//$this->kan_rules_str = $kan_rules_str;

		$need_data['kan_rules_str'] = $kan_rules_str;

		$kan_person_count = C('kan_person_count');

		//$this->kan_person_count = $kan_person_count;

		$need_data['kan_person_count'] = $kan_person_count;


		//随机商品 8 个
		$sql='SELECT goods_id,image,fan_image,price,danprice,name,seller_count,virtual_count FROM '.C('DB_PREFIX').'goods WHERE status=1 and type="normal" and quantity >0  ORDER BY rand() LIMIT 0,8';
	    $list=M()->query($sql);

	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    foreach ($list as $k => $v) {

			$list[$k]['seller_count'] += $list[$k]['virtual_count'];

	        $list[$k]['image']= C('SITE_URL').resize($v['image'], C('goods_related_thumb_width'), C('goods_related_thumb_height'));

			if(!empty($v['fan_image'])){
				$list[$k]['image']= C('SITE_URL').resize($v['fan_image'], C('goods_related_thumb_width'), C('goods_related_thumb_height'));
			}else {
				$list[$k]['image']= C('SITE_URL').resize($v['image'], C('goods_related_thumb_width'), C('goods_related_thumb_height'));
			}
	    }


	    //$this->related_goods=$list;

		$need_data['related_goods'] = $list;


		$bargain_order = M('bargain_order')->where( array('id' => $id) )->find();

		$goods_id = $bargain_order['goods_id'];

		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
       //"我正在参加拼多多官方砍价，砍到0元就可以免费拿啦，帮我砍一下吧"

	    $desc = "我正在参加".C('SITE_NAME')."砍价，砍到0元就可以免费拿啦，帮我砍一下吧";
	    //$this->share_logo = C('SITE_URL').resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

		$need_data['share_logo'] = C('SITE_URL').resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

		 $share_logo = '';

		 if(!empty($goods_info['fan_image'])){
			$share_logo = C('SITE_URL').resize($goods_info['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}else {
			$share_logo = C('SITE_URL').resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}

		if(!empty($goods_description['water_image']))
		{
			$share_logo = C('SITE_URL').'/Uploads/image/'.$goods_description['water_image'];
		}
		$need_data['share_logo'] = $share_logo;


		//Subject/assist_bargain_coupon_detail/id/10.html kan_rules_str
		$need_data['indexsharetitle'] = '['.C('SITE_NAME')."]我在砍价免费拿".$goods_info['name']."，帮我砍价，0元拿回家！";


		$need_data['url'] = C('SITE_URL')."/index.php?s=/Subject/assist_bargain_coupon_detail/id/{$id}/rmid/{$hash_member_id}";
		$need_data['indexsharesummary'] =  $desc;
		$need_data['cur_time'] = time();

		echo json_encode( array('code' => 0, 'data' => $need_data) );
		die();
	}

	/**
		分享成功后，砍
	**/
	public function share_success_kan()
	{
		$id = I('get.id');

		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		//$member_id = session('user_auth.uid');


		$bargain_order =  M('bargain_order')->where( array('id' => $id) )->find();
		//is_share
		if($bargain_order['is_share'] == 0 && $member_id == $bargain_order['member_id'] )
		{
			$order_detail = M('bargain_order_detail')->where( array('member_id' => $member_id, 'bargain_order_id' => $id) )->find();
			$old = $order_detail['kan_money'];


			$state =  $this->_bang_kan_order($id,$member_id,true,true );
			$bargain_order =  M('bargain_order')->where( array('id' => $id) )->find();

			$bargain_order['kan_money'] = round($bargain_order['total_money'] - $bargain_order['deal_money'],2);
			$bargain_order['deal_money'] = round($bargain_order['deal_money'],2);

			$bargain_order['already_money'] = round($bargain_order['total_money']-$bargain_order['deal_money'],2);

			if($bargain_order['state'] != 1 && $bargain_order['end_time'] > time() )
			{
				$bargain_order['state'] = 3;
			}
		//{"code":0,"li_html":"","order_detail":{"id":"45","member_id":"456","kan_money":0.28,"bargain_order_id":"26","addtime":"1531465747","is_sleft":"1","avatar":"https:\/\/wx.qlogo.cn\/mmopen\/vi_32\/DYAIOgq83eqiajGaVicwDKXSNwdia7lyovicLib6cMDWNdR9Ce6sA9icfqibYbPYbXSq8x8uMB8zArckA67icRzSjDsSFA\/132","nickname":"\u4f59\u5efa"},"bargain_order":{"total_money":"5.00","deal_money":3.37,"kan_money":1.63,"already_money":1.63}}

			$order_detail = M('bargain_order_detail')->where( array('member_id' => $member_id, 'bargain_order_id' => $id) )->find();
			$order_detail['kan_money'] = round($order_detail['kan_money'] - $old, 2);

			$li_html = "";

			$kan_order_list = M('bargain_order_detail')->where( array('bargain_order_id' =>$id) )->order('addtime desc')->limit(15)->select();


			echo json_encode( array('code' => 0,'li_html' => $li_html,'kan_order_list' => $kan_order_list, 'order_detail' => $order_detail, 'bargain_order' => $bargain_order) );
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
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		if(empty($member_id))
		{
			echo json_encode( array('code' => 5, 'msg' => '请先登录') );
			die();
		}

		$state =  $this->_bang_kan_order($id,$member_id,false);
		//1;//已经帮忙砍过
		//2; //活动已经成功，或者已经结束
		//3;//今天已经不能再帮别人砍价
		//4 砍价失败
		//0成功

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
			$bargain_order['already_money'] = round($bargain_order['total_money']-$bargain_order['deal_money'],2);
			$bargain_order['state'] = 3;

			$order_detail = M('bargain_order_detail')->where( array('member_id' => $member_id, 'bargain_order_id' => $id) )->find();
			$order_detail['kan_money'] = round($order_detail['kan_money'], 2);

			$li_html = "";


			echo json_encode( array('code' => 0,'li_html' => $li_html, 'order_detail' => $order_detail, 'bargain_order' => $bargain_order) );
			die();
		}
	}
	public function get_user_goods_qrcode2()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];
		$goods_id = I('get.goods_id');

		$goods_share_image = M('goods_share_image')->where( array('member_id' =>$member_id,'goods_id' => $goods_id ) )->find();

		if( !empty($goods_share_image) )
		{
			$result = array('code' => 0, 'image_path' => $goods_share_image['image_path']);
			echo json_encode($result);
			die();
		}else {
			$goods_model = D('Home/Goods');

			$goods_description = M('goods_description')->where( array('goods_id' =>$goods_id) )->find();
			if( empty($goods_description['wepro_qrcode_image']) || true)
			{
				$this->get_weshare_image($goods_id);

				$goods_description = M('goods_description')->where( array('goods_id' =>$goods_id) )->find();
			}


			$rocede_path = $goods_model->_get_goods_user_wxqrcode($goods_id,$member_id);
			$res = $goods_model->_get_compare_qrcode_bgimg($goods_description['wepro_qrcode_image'], $rocede_path);

			$data = array();
			$data['member_id'] = $member_id;
			$data['goods_id']  = $goods_id;
			$data['image_path']  = $res['full_path'];
			$data['addtime']  = time();

			M('goods_share_image')->add($data);

			$result = array('code' => 0, 'image_path' => $res['full_path']);
			echo json_encode($result);
			die();
		}
	}

	private function get_weshare_image($goods_id)
	{


		//400*400 fan_image
		//get_goods_price($goods_id)
		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

		$goods_img = ROOT_PATH.'Uploads/image/'.$goods_info['image'];
		if( !empty($goods_info['fan_image']) )
		{
			$goods_img = ROOT_PATH.'Uploads/image/'.$goods_info['fan_image'];
		}
		$goods_model = D('Home/Goods');
		$goods_price = $goods_model->get_goods_price($goods_id);
		$goods_price['market_price'] = $goods_info['price'];
		//price
		$goods_title = $goods_info['name'];


		$need_img = $goods_model->_get_compare_zan_img($goods_img,$goods_title,$goods_price);

		//贴上二维码图
		//$rocede_path = $goods_model->_get_goods_user_wxqrcode($goods_id,$member_id);
		//$res = $goods_model->_get_compare_qrcode_bgimg($need_img['need_path'], $rocede_path);

		M('goods_description')->where( array('goods_id' =>$goods_id) )->save( array('wepro_qrcode_image' =>$need_img['need_path']) );

		return true;
	}
	/**
		砍价开始
	**/
	public function get_user_goods_qrcode()
	{
		$token = I('get.token');

		//user_favgoods
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		$data_json = file_get_contents('php://input');
		$data = json_decode($data_json, true);
		if(empty($data))
		{
			$this->get_user_goods_qrcode2();
			die();
		}
		$address_id = $data['address_id'];
		$goods_id = $data['goods_id'];
		$need_optionc = $data['sku_str'];

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
		$state =  $this->_bang_kan_order($zan_order_id,$member_id,true);

		echo json_encode( array('code' =>0, 'id' => $zan_order_id) );
		die();
	}
	/**
		开始砍价表
	**/
	private function _bang_kan_order($bargain_order_id,$member_id,$is_self = false, $just_update = false )
	{
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




		$member_info = M('member')->field('uname,avatar,we_openid')->where( array('member_id' => $member_id) )->find();

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

				$or_member_info = M('member')->field('openid,we_openid')->where( array('member_id' => $bargain_order_info['member_id']) )->find();
				$or_open_id = $or_member_info['we_openid'];
				//$or_open_id = 'o0n_HwcGIfwf5b8PN8-gmNfsJBLA';

				$goods_info = M('goods')->field('name')->where( array('goods_id' => $bargain_order_info['goods_id']) )->find();

				//转小程序模板消息
				/**
					5zZUGZhK46PzcJeAIidv_cdsJ6OaCCXe0PtGen0yvOA
					复制
					标题
					砍价成功通知
					关键词
					商品名称
					{{keyword1.DATA}}
					砍价状态
					{{keyword2.DATA}}
					备注
					{{keyword3.DATA}}
				**/
				$first_title = "您参与：".$goods_info['name'];
				$keyword1 = '您的活动已砍价成功';

				$url = C('SITE_URL')."/index.php?s=/Order/info/id/{$order_id}.html";

				$template_data['keyword1'] = array('value' => $first_title, 'color' => '#030303');
				$template_data['keyword2'] = array('value' => '砍价成功', 'color' => '#030303');
				$template_data['keyword3'] = array('value' => '当前活动已成功，等待发货', 'color' => '#030303');


				$pay_order_msg_info =  M('config')->where( array('name' => 'weprogram_template_kan_msg') )->find();
				$template_id = $pay_order_msg_info['value'];

				$pagepath = 'pages/order/order?id='.$order_id;

				$member_formid_info = M('member_formid')->where( array('member_id' => $bargain_order_info['member_id'], 'formid' =>array('neq',''),'state' => 0) )->order('id desc')->find();

				if( !empty($member_formid_info) )
				{
					$rs = send_wxtemplate_msg($template_data,$url,$pagepath,$or_open_id,$template_id,$member_formid_info['formid']);
					//更新
					M('member_formid')->where( array('id' => $member_formid_info['id']) )->save( array('state' => 1) );
				}
			}
			return 0;
		}else{
			return 4;//砍价失败
		}

	}

	public function get_category_keyword_goods()
	{
		$pre_page = 10;
		$page = I('get.page',1);
		$keyword = I('get.keyword','');


		$sort = I('get.sort', 'desc');
		$cur_type = I('get.cur_type','default');//default seller_count price

		$cur_price_index = I('get.cur_price_index',0);
		$search_min_price = I('get.search_min_price',0);
		$search_max_price = I('get.search_max_price',0);


		$goods_model = D('Home/Goods');

		$condition = array('name' => array('like','%'.$keyword.'%'), 'status'=>1,'quantity' =>array('gt',0) );
		$condition['type'] = array('NEQ','assistance');//'normal';

		$orderby = "seller_count {$sort},goods_id {$sort}";

		if($cur_type == 'seller_count')
		{
			$orderby = "seller_count+virtual_count desc";
		}else if($cur_type == 'price'){
			$orderby = "danprice {$sort}";
		}

		if($cur_price_index > 0)
		{
			if($search_min_price > 0 && $search_max_price > 0)
			{
				//$sql['jobSalary'] = array('between','0,1000');
				$condition['danprice'] = array('between',"{$search_min_price},{$search_max_price}");
			}else if($search_min_price > 0)
			{
				//'quantity' =>array('gt',0)
				$condition['danprice'] = array('egt', $search_min_price);
			} else if($search_max_price > 0){
				$condition['danprice'] = array('elt', $search_max_price);
			}
			//$search_min_price = I('get.search_min_price');
			//$search_max_price = I('get.search_max_price');
		}

		//$condition['lock_type'] = 'normal';

		$offset = ($page -1) * $pre_page;
		$list = M('goods')->where($condition)->order($orderby)->limit($offset,$pre_page)->select();

		if(!empty($list)) {
			foreach($list as $key => $v){

				$goods_price_arr = $goods_model->get_goods_price($v['goods_id']);
				$list[$key]['pinprice'] = $goods_price_arr['price'];
				$list[$key]['image']=C('SITE_URL'). resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));


			}
		}
		foreach($list as $key => $val)
		{
		    $val['seller_count'] += $val['virtual_count'];
		    $list[$key] = $val;
		}

		if( !empty($list) )
		{
			echo json_encode( array('code' => 0, 'data' => $list ) );
			die();
		} else {
			echo json_encode( array('code' => 1) );
			die();
		}


	}
	public function get_goods_fujin_tuan()
	{
		$id = I('get.id');
		$count = I('get.count',2);

		$pin_model = D('Home/Pin');
		//正在进行中的商品团
		$fujin_tuan = $pin_model->get_goods_pintuan($id,$count);

		$fujin_list = $fujin_tuan['list'];

		echo json_encode( array('code' => 0, 'cur_time' => time(), 'data' => $fujin_list) );
		die();
		//$this->fujin_tuan = $fujin_tuan;

	}

	public function upload_image(){

		$dir=I('get.dir','goods');
		$dir .= '/'.date('Y-m-d');

		//$this->del_old_image();

		$upload = new \Think\Upload();// 实例化上传类

	    $image_dir=ROOT_PATH.'Uploads/image/'.$dir;

	    RecursiveMkdir($image_dir);


	    $upload->autoSub   =	 false;
	    $upload->maxSize   =     3145728 ;// 设置附件上传大小
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $upload->rootPath  =	 $image_dir.'/';

	    $info   =   $upload->upload();

		if(!$info) {
			$data['result'] = false;
		 	$this->ajaxReturn($data);
    	 }else{// 上传成功
    	 	//{"image_thumb":"Uploads\/image\/cache\/goods\/2017-11-01\/59f9460eac183-100x100.jpg",
			//"image":"goods\/2017-11-01\/59f9460eac183.jpg"}

    	 	$filename=$dir.'/'.$info['file']['savepath'].$info['file']['savename'];
			$data['image_thumb'] = C('SITE_URL').resize($filename, 300, 300);
			$data['image'] = C('SITE_URL').'Uploads/image/'.$filename;
			$data['image_o'] = $filename;
    	    $this->ajaxReturn($data);

 		 }
	}
	public function get_goods_simple() {
		 $id = I('get.id');

		$goods_info = M('goods')->where( array('goods_id' => $id) )->find();

		$goods_info['image'] = C('SITE_URL') . resize($goods_info['image'], 400 , 400);

		if ($goods[0]['type'] == 'pintuan' || $goods[0]['type'] == 'lottery') {
            $pin_info = M('pin_goods')->where(array(
                'goods_id' => $id
            ))->find();
        }

		$need_data = array();
		$need_data['goods_id'] = $id;
		$need_data['name'] = $goods_info['name'];
		$need_data['price'] = empty($pin_info) ? $goods_info['danprice'] : $pin_info['pin_price'];
		$need_data['image'] = $goods_info['image'];

		echo json_encode( array('code' =>0, 'data' => $need_data) );
		die();

	}



    public function get_goods_detail() {
        $id = I('get.id');
        $pin_id = I('get.pin_id', 0);

		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];




        $need_data = array();
        $sql = "select g.*,gd.description,gd.is_untake_level,level_discount,gd.video_src,gd.video_size_width,gd.vedio_size_height,gd.is_video,gd.summary,gd.share_title,gd.activity_summary,gd.tag from " . C('DB_PREFIX') . "goods g," . C('DB_PREFIX') . "goods_description gd where g.goods_id=gd.goods_id and g.goods_id=" . $id;
        $goods = M()->query($sql);
        $pin_model = D('Home/Pin');
        $goods_model = D('Home/Goods');
        $qian = array(
            "/Uploads/image"
        );
		$c_site_url = str_replace('/dan','',C('SITE_URL'));
        $hou = array(
            $c_site_url . "/Uploads/image"
        );
		$goods[0]['video_src'] = C('SITE_URL')."Uploads/image/".$goods[0]['video_src'];

        $goods[0]['description'] = str_replace($qian, $hou, $goods[0]['description']);
        $goods[0]['description'] = htmlspecialchars_decode($goods[0]['description']);
        $qian = array(
            "\r\n"
        );
        $hou = array(
            "<br/>"
        );
        $goods[0]['summary'] = str_replace($qian, $hou, $goods[0]['summary']);

		$hou = array(
            "@EOF@"
        );
		//string(469) "活动时间：@EOF@1.参与活动人人有奖，一等奖为本商品（1 人），参与奖退款并赠送专属优惠券 。@EOF@2.只有参加拼团，而且拼团成功的用户，才有资格参与抽奖@EOF@3.活动结束后24小时内，从有资格抽奖的用户中，随机抽取中奖者 。@EOF@4.抽奖活动结束后，中奖商品3天内发货，参与奖专属优惠券3天内发放。@EOF@5.本活动真实有效，最终解释权归吃货星球所有。"

		$goods[0]['activity_summary'] = str_replace($qian, $hou, $goods[0]['activity_summary']);
		$goods[0]['activity_summary'] = explode('@EOF@',$goods[0]['activity_summary']);
		//var_dump($goods[0]['activity_summary']);die();
        if (isset($goods)) {
            foreach ($goods as $k => $v) {
                $goods[$k]['image_thumb'] = C('SITE_URL') . resize($v['image'], 400, 400);
            }
        }
        $sql = "select image,is_video_click from " . C('DB_PREFIX') . "goods_image where goods_id=" . $id;
        $goods_image = M()->query($sql);
        if (isset($goods_image)) {
            foreach ($goods_image as $k => $v) {
                $goods_image[$k]['image_' . C('goods_thumb_width') . '_' . C('goods_thumb_height') ] = C('SITE_URL') . resize($v['image'], C('goods_thumb_width') , C('goods_thumb_height'));
                $goods_image[$k]['image'] = C('SITE_URL') . 'Uploads/image/' . $v['image'];
            }
        }
        $goods[0]['seller_count']+= $goods[0]['virtual_count'];
        $goods_price_arr = $goods_model->get_goods_price($id);

		$goods[0]['danprice'] = $goods_price_arr['danprice'];
		//danprice

        /** $need_data['goods_price_arr'] = $goods_price_arr; **/
        $price_dol = explode('.', $goods_price_arr['pin_price']);
        if (!empty($goods[0]['tag'])) $goods[0]['tag'] = explode(',', $goods[0]['tag']);
        $goods[0]['fan_image'] = C('SITE_URL') . 'Uploads/image/' . $goods[0]['fan_image'];
        $goods[0]['image'] = C('SITE_URL') . 'Uploads/image/' . $goods[0]['image'];
        unset($goods[0]['lock_type']);
        unset($goods[0]['lock_price']);
        unset($goods[0]['points']);
        unset($goods[0]['transport_id']);
        unset($goods[0]['express_list']);
        unset($goods[0]['is_free_in']);
        unset($goods[0]['pick_up']);
        unset($goods[0]['pick_just']);
        unset($goods[0]['shipping']);
        unset($goods[0]['stock_status_id']);
        unset($goods[0]['index_sort']);
        unset($goods[0]['is_index_show']);
        unset($goods[0]['sku']);
        unset($goods[0]['model']);
        $pin_info = array();

		$token = I('get.token');
		//user_favgoods


		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		$user_favgoods = M('user_favgoods')->where( array('member_id' => $member_id,'goods_id' =>$id) )->find();

		if( !empty($user_favgoods) )
		{
			$goods[0]['favgoods'] = 2;
		}else{
			$goods[0]['favgoods'] = 1;
		}
		$price = $goods[0]['danprice'];

        if ($goods[0]['type'] == 'pintuan' || $goods[0]['type'] == 'lottery') {
            $pin_info = M('pin_goods')->where(array(
                'goods_id' => $id
            ))->find();

			$pin_info['pin_price'] = $goods_price_arr['pin_price'];

			$price = $pin_info['pin_price'];

			if( $goods[0]['type'] == 'pintuan' && $pin_info['end_time']  < time() )
			{
				if( C('PIN_ADD_TIME') > 0)
				{
					$yan_time = time() + 86400 * C('PIN_ADD_TIME');

					M('pin_goods')->where(array(
					'id' => $pin_info['id']
					))->save( array('end_time' => $yan_time) );
					$pin_info['end_time'] = $yan_time;
				}
			}

			//pin_goods end_time
        }
		$lottery_info = array();
		if ($goods[0]['type'] == 'lottery') {
			$lottery_info = M('lottery_goods')->field('is_open_lottery,end_time')->where(array(
                'goods_id' => $id
            ))->find();
			//验证抽奖商品状态
			 //end_time

			 $lottery_is_end_open_status = 0;
			 //正常可以继续抽奖 0
			 //已结束，等待开奖 1
			//已开奖 活动已结束 2

			 $now_time = time();
			 if($lottery_info['end_time'] < $now_time)
			 {
				$lottery_is_end_open_status = 1;
				if($lottery_info['is_open_lottery'] == 1)
				{
					$lottery_is_end_open_status = 2;
				}
			 }
			 $lottery_info['lottery_is_end_open_status'] = $lottery_is_end_open_status;

		}
		$need_data['lottery_info'] = $lottery_info;
		//lottery_goods
		//gd.share_title

		if ($goods[0]['type'] == 'integral' ) {
			$goods[0]['score'] = intval($goods_price_arr['price']);
			if( empty($goods[0]['share_title']) )
			{
				$goods[0]['share_title'] = intval($price).'积分 '.$goods[0]['name'];
			}
		}else{
			if( empty($goods[0]['share_title']) )
			{
				$goods[0]['share_title'] = $price.'元 '.$goods[0]['name'];
			}
		}
		/** 商品客户折扣begin **/
		$is_show_member_disc = 0;

		$member_disc = 100;
		$level_info = M('config')->where( array('name' => 'member_level_is_open') )->find();
		$member_level_info = array('is_show_member_disc' => $is_show_member_disc);

		$member_level_list = array();//客户等级列表
		$max_level_logo = C('SITE_URL')."/resource/images/plus.png";
		$max_member_level = array('level' => 0,'logo'=>'');//最大等级

		$max_get_money = 0;//
		$max_get_pin_money = 0;//最大折扣拼团省钱
		$max_get_dan_money = 0;//最大折扣单独购买省钱

		$is_show_max_level = 0;

		if( !empty($member_id) && $member_id > 0 && $level_info['value'] == 1 && $goods[0]['is_untake_level'] == 0)
		{
			$member_info =  M('member')->field('level_id')->where( array('member_id' => $member_id) )->find();

			$member_level_info = $goods_model->get_member_level_info($member_id, $id);

			$member_level_info['is_show_member_disc'] = 1;
			$member_disc = $member_level_info['member_discount'];
			$max_member_level = M('member_level')->order('level desc')->find();

			//level
			if($max_member_level['level'] > $member_level_info['level'])
			{
				$is_show_max_level = 1;
				//max_level_logo
				if( !empty($max_member_level['logo']) )
				{
					$max_member_level['logo'] = C('SITE_URL').'Uploads/image/'.$max_member_level['logo'];
				}else{
					$max_member_level['logo'] = $max_level_logo;
				}
			}

			$member_level_list =  M('member_level')->order('id asc')->select();
			if( !empty($member_level_list) )
			{
				foreach( $member_level_list as $key => $val )
				{
					$val['discount'] = $val['discount'] / 10;
					$member_level_list[$key] = $val;
				}
			}
		}


		//$max_get_pin_money = 0;//最大折扣拼团省钱
		//$max_get_dan_money = 0;//最大折扣单独购买省钱

		/** 商品客户折扣end **/

		$goods[0]['memberprice'] = sprintf('%.2f', round( ($goods[0]['danprice'] * $member_disc) / 100 ,2));
		$max_get_dan_money = round( ($goods[0]['danprice'] * (100 - $max_member_level['discount']) ) / 100 ,2);
		$max_get_money = $max_get_dan_money;
		if(!empty($pin_info))
		{
			$pin_info['member_pin_price'] = sprintf('%.2f',round( ($pin_info['pin_price'] * $member_disc) / 100 ,2));
			$max_get_pin_money = round( ($pin_info['pin_price'] * (100 - $max_member_level['discount']) ) / 100 ,2);
			$max_get_money = $max_get_pin_money;
		}


        $need_data['pin_info'] = $pin_info; /**$need_data['price_dol'] = $price_dol;**/

		if(!empty($member_id) && $member_id > 0 && $goods[0]['type'] == 'integral')
		{
			$member_info =  M('member')->field('score')->where( array('member_id' => $member_id) )->find();
			if($member_info['score'] < $goods[0]['score'])
			{
				$goods[0]['score_enough'] = 0;
			}else{
				$goods[0]['score_enough'] = 1;
			}
		}

		$need_data['member_level_info'] = $member_level_info;
		$need_data['member_level_list'] = $member_level_list;
		$need_data['max_member_level'] = $max_member_level;
		$need_data['max_get_money'] = sprintf('%.2f',$max_get_money);

		$need_data['max_get_pin_money'] = $max_get_pin_money;
		$need_data['max_get_dan_money'] = $max_get_dan_money;

		//$max_get_pin_money = 0;//最大折扣拼团省钱
		//$max_get_dan_money = 0;//最大折扣单独购买省钱

		$need_data['is_show_max_level'] = $is_show_max_level;

        $need_data['goods'] = $goods[0];
        $need_data['goods_image'] = $goods_image;
        $seller_info = M('seller')->field('s_id,s_true_name,s_logo,s_qq,certification')->where(array(
            's_id' => $goods[0]['store_id']
        ))->find();
        $seller_model = D('Home/Seller');
        $seller_info['seller_count'] = $seller_model->getStoreSellerCount($goods[0]['store_id']);
        $seller_goods_count = M('goods')->where(array(
            'store_id' => $goods[0]['store_id']
        ))->count();
        $seller_info['goods_count'] = $seller_goods_count;
        $seller_info['s_logo'] = C('SITE_URL') . 'Uploads/image/' . $seller_info['s_logo'];
        $need_data['seller_info'] = $seller_info;

		$need_data['site_name'] = C('SITE_NAME');

        $need_data['options'] = $goods_model->get_goods_options($id);
        $goods_option_mult_value = M('goods_option_mult_value')->where(array(
            'goods_id' => $id
        ))->order('id asc')->select();


		$order_comment_count =  M('order_comment')->where( array('goods_id' => $id, 'state' => 1) )->count();
		$comment_list = array();

		if($order_comment_count > 0)
		{
			$sql = "select o.*,m.name as name2,m.avatar as avatar2 from ".C('DB_PREFIX')."order_comment as o left join ".C('DB_PREFIX')."member as m on o.member_id=m.member_id
			where  o.state = 1 and o.goods_id = {$id} order by o.add_time desc limit 2";

			$comment_list=M()->query($sql);
			foreach($comment_list as $key => $val)
			{
				//user_name

				if( empty($val['user_name']) )
				{
					$val['name'] = $val['name2'];
					$val['avatar'] = $val['avatar2'];
				}else{
					$val['name'] = $val['user_name'];
				}

				if($val['type'] == 0)
				{
					$order_goods_info =  M('order_goods')->field('order_goods_id')->where( array('order_id' => $val['order_id'],'goods_id' => $id) )->find();

					$order_option_info = M('order_option')->field('value')->where( array('order_id' =>$val['order_id'],'order_goods_id' => $order_goods_info['order_goods_id']) )->select();

					$option_arr = array();
					foreach($order_option_info as $option)
					{
						$option_arr[] = $option['value'];
					}
					$option_str = implode(',', $option_arr);
				}else{
					$option_str = '';
				}


				if( !empty($val['images']) )
				{
					$img_list = unserialize($val['images']);
					$need_img_list = array();

					foreach($img_list as $kk => $vv)
					{
						if(!empty($vv) )
						{
							$vv =  C('SITE_URL') .resize($vv, C('goods_thumb_width'), C('goods_thumb_height'));
							$img_list[$kk] = $vv;
							$need_img_list[$kk] = $vv;
						}
					}
					$val['images'] = $need_img_list ;
				}
				$val['option_str'] = $option_str;
				$val['add_time'] = date('Y-m-d', $val['add_time']) ;
				$comment_list[$key] = $val;
			}
			//$this->comment_list = $comment_list;

		}

		$need_data['cur_time'] = time();
		$need_data['pin_id'] = $pin_id;
        echo json_encode(array(
            'code' => 1,
				'comment_list' => $comment_list,
				'order_comment_count' => $order_comment_count,
            'data' => $need_data
        ));
        die();
    }

	/**
		商品评价
	**/
	public function comment_info()
    {
        $goods_id = I('get.goods_id',0);

		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		$result = array('code' =>0);

		if( empty($member_id))
		{
			$result['msg'] = '未登录';
			echo json_encode($result);
			die();
		}


        $goods_info = M('goods')->where( array('goods_id' => $goods_id ) )->find();
        if(empty($goods_info)) {

			$result['msg'] = '没有此商品';
			echo json_encode($result);
            die();
        }

        $page = I('get.page',1);
        $per_page = I('get.per_page',10);
       // $per_page = 4;
        $offset = ($page - 1) * $per_page;

        $sql = "select o.*,m.name as name2,m.avatar as avatar2 from ".C('DB_PREFIX')."order_comment as o left join ".C('DB_PREFIX')."member as m on o.member_id=m.member_id
			where  o.state =1 and o.goods_id = {$goods_id} order by o.add_time desc limit {$offset},{$per_page}";

        $list=M()->query($sql);

		foreach($list as $key => $val)
		{
			if( empty($val['user_name']) )
			{
				$val['name'] = $val['name2'];
				$val['avatar'] = $val['avatar2'];
			}else{
				$val['name'] = $val['user_name'];
			}

			if($val['type'] == 0)
			{
				$order_goods_info =  M('order_goods')->field('order_goods_id')->where( array('order_id' => $val['order_id'],'goods_id' => $id) )->find();

				$order_option_info = M('order_option')->field('value')->where( array('order_id' =>$val['order_id'],'order_goods_id' => $order_goods_info['order_goods_id']) )->select();

				$option_arr = array();
				foreach($order_option_info as $option)
				{
					$option_arr[] = $option['value'];
				}
				$option_str = implode(',', $option_arr);
			}else{
				$option_str = '';
			}




			if( !empty($val['images']) )
			{
				$img_list = unserialize($val['images']);
				if(!empty($img_list))
				{
					$need_img_list = array();
					foreach($img_list as $kk => $vv)
					{
						if( empty($vv) )
						{
							continue;
						}
						$vv =  C('SITE_URL'). resize($vv, C('goods_thumb_width'), C('goods_thumb_height'));
						$img_list[$kk] = $vv;
						$need_img_list[$kk] = $vv;
					}

					$val['images'] = $need_img_list;
				}else{
					$val['images'] = array();
				}

			}

			//<view class="time span">{{item.addtime}}</view>
			//		<view class="style span">{{item.option_str}} </view>
			$val['add_time'] = date('Y-m-d', $val['add_time']) ;
			$val['option_str'] = $option_str;
			$list[$key] = $val;
		}


        $result = array();
        $result['code'] = 1;
        $result['list'] = $list;

        echo json_encode($result);
        die();

    }

	public function get_lottery_info()
	{
		///goods_id/30
		$goods_id = I('get.goods_id',0);

       // $goods_info = M('goods')->where( array('goods_id' => $goods_id,'type' => 'lottery') )->find();
		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

		$pin_goods = M('pin_goods')->where( array('goods_id' => $goods_id) )->find();



        if(empty($goods_info)) {
            die('非法操作');
        }


        $page = I('get.page',1);
        $per_page = I('get.per_page',4);
		$per_page = 1000;
        $offset = ($page - 1) * $per_page;

        $goods_info['image'] = C('SITE_URL').resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

        $sql = "select m.avatar,m.uname,o.order_num_alias,o.shipping_tel from ".C('DB_PREFIX')."order as o ,".C('DB_PREFIX')."order_goods as og,".C('DB_PREFIX')."member as m
                where o.lottery_win = 1 and o.order_id = og.order_id and og.goods_id and o.member_id = m.member_id and og.goods_id = {$goods_id} and o.date_added > ".$pin_goods['begin_time']." order by o.order_id asc limit {$offset},{$per_page}";
        //begin_time

		$this->goods_info = $goods_info;

        $list=M()->query($sql);

        $count_sql = "select count(o.order_num_alias) as count from ".C('DB_PREFIX')."order as o ,".C('DB_PREFIX')."order_goods as og,".C('DB_PREFIX')."member as m
        where o.lottery_win = 1 and o.order_id = og.order_id and og.goods_id and o.member_id = m.member_id and og.goods_id = {$goods_id} ";
        $count=M()->query($count_sql);
        $count = $count[0]['count'];

        $max_page = ceil($count / $per_page);

		$this->pin_goods = $pin_goods;
        $this->list = $list;
        $this->goods_id = $goods_id;

        //if(empty($list)) {
           // $jia_page = $page - $max_page;
         //   $offset = ($jia_page - 1) * $per_page;

            $sql = "select avatar,uname,order_sn as order_num_alias,mobile as shipping_tel from
                ".C('DB_PREFIX')."jiaorder
                    where goods_id = {$goods_id} and addtime > ".$pin_goods['begin_time']." order by id asc limit {$offset},{$per_page}";
					// where goods_id = {$goods_id} order by id asc limit {$offset},{$per_page}";
            $lists=M()->query($sql);
			foreach($lists as $vv)
			{
				$list[] = $vv;
			}
           // $this->list = $list;
       // }


		if(!empty($list))
		{
			foreach($list as $key => $val)
			{
				$list[$key]['shipping_tel'] =  substr($val['shipping_tel'],0,3)."*****".substr($val['shipping_tel'],-3,3);
			}

			$need_data = array();
			$need_data['code'] = 0;
			$need_data['goods_info'] = $goods_info;
			$need_data['pin_goods'] = $pin_goods;
			$need_data['data'] = $list;
		}else {
			$need_data = array();
			$need_data['code'] = 0;
			$need_data['goods_info'] = $goods_info;
			$need_data['pin_goods'] = $pin_goods;
			$need_data['data'] = $list;
		}

		echo json_encode($need_data);
		die();

	}

	function get_subcategory()
	{
		// /id/'+cur_pid
		$cate_id = I('get.id',0);
		$level = 0;
		$cur_cate_name = '';
		//name

		if($cate_id > 0)
		{
			$parinfo = M('goods_category')->where( array('id' => $cate_id) )->find();
			$cur_cate_name = $parinfo['name'];

			if($parinfo['pid'] > 0)
			{
				$level = 2;
			}
		}


		$goods_category_list = M('goods_category')->where( array('pid' => $cate_id) )->order('sort_order desc, id asc')->select();

		foreach($goods_category_list as $key => $val)
		{
			$val['logo'] = C('SITE_URL').'Uploads/image/'.$val['logo'];

			$goods_category_list[$key] = $val;
		}

		if(!empty($goods_category_list))
		{
			echo json_encode( array('code' => 0,'level' => $level, 'cur_cate_name' => $cur_cate_name,'data' => $goods_category_list) );
			die();
		} else{
			echo json_encode( array('code' => 1,'level' => $level,'cur_cate_name' => $cur_cate_name) );
			die();
		}
	}
	/**
		获得商户优惠券
	**/
	public function get_seller_quan()
	{
		$seller_id = I('get.store_id',1);

		$where = "";
		$where = "total_count>send_count and store_id ={$seller_id} and is_index_show=1 and end_time>".time();


		$quan_list = M('voucher')->where($where)->order('add_time desc')->limit(4)->select();


		echo json_encode( array('code' => 0, 'quan_list' => $quan_list) );
		die();
	}

	public function seller_info()
	{
		$seller_id = I('get.id',0);

		//s_true_name  s_logo

		$seller_info = M('seller')->field('s_true_name,s_logo,s_banner')->where( array('s_id' => $seller_id) )->find();

		$seller_info['s_logo'] = C('SITE_URL').'Uploads/image/'.$seller_info['s_logo'];

		if( empty($seller_info['s_banner']) )
		{
			$seller_info['s_banner'] = C('SITE_URL').'resource/images/170923_1bk3970j2eb9jia57aa8k3i661ck5_750x270.jpg';
		} else{
			$seller_info['s_banner'] = C('SITE_URL').'Uploads/image/'.$seller_info['s_banner'];
		}

		//s_banner

		$seller_model = D('Home/Seller');

		$seller_count = $seller_model->getStoreSellerCount($seller_id);

		$goods_count = M('goods')->where( array('store_id' => $seller_id) )->count();

		$seller_info['seller_count'] = $seller_count;
		$seller_info['goods_count'] = $goods_count;

		//$this->goods_count = $goods_count;

		//$this->seller_count = $seller_count;

		$store_class_list =  M('store_bind_class')->where( array('seller_id' => $seller_id) )->order('bid asc')->select();

		$store_class_ids = array();
		foreach($store_class_list as $class_val)
		{
			$store_class_ids[] = $class_val['class_1'];
		}

		$category_list = array();

		if( !empty($store_class_ids) )
		{
			$category_list = M('goods_category')->field('id,name,logo')->where( array('id' => array('in', $store_class_ids) ) )->select();
			foreach($category_list as $key => $val)
			{
				$val['logo'] = C('SITE_URL').'Uploads/image/'.$val['logo'];
				$category_list[$key] = $val;
			}
		}
		//["name"]=> string(12) "食品食品" ["logo"]=>

		//$this->category_list = $category_list;

		$fans_count = M('user_favstore')->where( array('store_id' => $seller_id) )->count();

		$seller_info['fans_count'] = $fans_count;

		$seller_info['category_list'] = $category_list;

		$where = "";
		$where = "total_count>send_count and store_id ={$seller_id} and is_index_show=1 and end_time>".time();


		$quan_list = M('voucher')->where($where)->order('add_time desc')->limit(4)->select();

		$seller_info['quan_list'] = $quan_list;

		//$this->quan_list = $quan_list;


		echo json_encode( array('code' =>0, 'data' => $seller_info) );
		die();
	}

	public function getQuan()
    {
		$token = I('get.token');
		//user_favgoods


		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		$data_json = file_get_contents('php://input');
		$data = json_decode($data_json, true);


        $result = array('code' => 0,'msg' => '被抢光啦');
        $quan_id = $data['quan_id'];
        if($quan_id >0){
            $quan_model = D('Home/Voucher');
           $res =  $quan_model->send_user_voucher_byId($quan_id,$member_id,true);

           //1 被抢光了 2 已领过  3  领取成功
           $mes_arr = array(1 => '被抢光了',2 => '已领过', 3 => '领取成功');

		   $result['code'] = $res;
           $result['msg'] = $mes_arr[$res];
        }
        echo json_encode($result);
        die();
    }

	public function notify_order()
	{
		//$map['id'] = array('neq',100);
		$notify_order = M('notify_order')->where( array('state' => array('lt', 1)) )->order('state asc,id asc')->find();


		//username
		$result = array('ret' => 0);
		if(empty($notify_order))
		{

			echo json_encode($result);
			die();
		}
		M('notify_order')->where( array('id' => $notify_order['id']) )->setInc('state', 1);
		$miao = (time() -$notify_order['order_time']) % 60;

		$result['ret'] = 1;
		$result['username'] = $notify_order['username'];
		$result['avatar'] 	= $notify_order['avatar'];
		$result['order_id'] 	= $notify_order['order_id'];

		$result['order_url'] 	= $notify_order['order_url'];
		$result['miao'] 	= $miao;



		//->save( array('state' => 1) );


		echo json_encode($result);
		die();
	}

	public function seller_goods_list()
	{
		$pre_page = I('get.pre_page',2);
		$page = I('get.page',1);
		$seller_id = I('get.seller_id',0);
		$gid = I('get.gid',0);
		$keyword = I('get.keyword','');
		$order_by = I('get.order_by','default');

		switch($order_by)
		{
			case 'default':
					$order_by = 'sort_order asc';
					break;
			case 'hot':
					$order_by = 'seller_count desc';
					break;
			case 'new':
					$order_by = 'goods_id desc';
					break;
		}

		$where = " and (g.type='pintuan' or g.type='lottery') and  g.store_id = {$seller_id} and g.status =1 and g.quantity > 0 ";

		$offset = ($page -1) * $pre_page;

		if( $gid > 0)
		{
			$where .= " and  (gt.class_id1 = {$gid} or gt.class_id2 = {$gid} or gt.class_id3 = {$gid}) ";
		}

		if( !empty($keyword) )
		{
			$where .= " and g.name like '%{$keyword}%' ";
		}

		$sql = "select g.* from ".C('DB_PREFIX')."goods as g ,".C('DB_PREFIX')."goods_to_category as gt
				where g.goods_id = gt.goods_id  {$where} order by {$order_by}  limit {$offset},{$pre_page} ";
		$list = M()->query($sql);

		$goods_data = array();

		if(!empty($list)) {
			foreach($list as $key => $v){
				//$list[$key]['seller_count'] += $v['virtual_count'];

				$tmp_goods = array();
				$tmp_goods['goods_id'] = $v['goods_id'];
				$tmp_goods['name'] = $v['name'];
				$tmp_goods['quantity'] = $v['quantity'];
				$tmp_goods['image'] = C('SITE_URL').'Uploads/image/' .$v['image'];

				$tmp_goods['price'] = $v['price'];
				$tmp_goods['danprice'] = $v['danprice'];
				$tmp_goods['seller_count'] = $v['seller_count']+$v['virtual_count'];
				$tmp_goods['fav_count'] = M('user_favgoods')->where( array('goods_id' => $v['goods_id']) ) ->count();

				$goods_data[] = $tmp_goods;

				//$list[$key]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			}
		}

		if( empty($goods_data) )
		{
			echo json_encode( array('code' => 1) );
			die();
		} else {
			echo json_encode( array('code' => 0, 'data' => $goods_data) );
			die();
		}

	}


}

