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

class ApiController extends CommonController {
    protected function _initialize()
    {
    	parent::_initialize();
        $this->cur_page = 'api';
		$site_logo = M('config')->where( array('name'=>'SITE_ICON') )->find();
		$this->site_logo = $site_logo['value'];
    }

	/**
		今日签到红包情况
	**/
	public function get_fissionbunus()
	{
		$token = I('get.token');
		$other_bonus_id = I('get.other_bonus_id', 0);

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		if( empty($member_id) )
		{
			echo json_encode( array('code' => 3) );
			die();
		}

		$bonus_share_title_arr =  M('config')->where( array('name' =>'bonus_share_title') )->find();
		$bonus_rule_arr =  M('config')->where( array('name' =>'bonus_rule') )->find();


		$bonus_game_time_arr =  M('config')->where( array('name' =>'bonus_game_time') )->find();
		$bonus_money_arr =  M('config')->where( array('name' =>'bonus_money') )->find();
		$bonus_count_arr =  M('config')->where( array('name' =>'bonus_count') )->find();
		$is_open_fissionbonus_arr =  M('config')->where( array('name' =>'is_open_fissionbonus') )->find();

		$share_image_arr = M('config')->where( array('name' =>'fissionbonus_share_image') )->find();

		$share_image = '';

		if(!empty($share_image_arr['value']))
		{
			$share_image = C('SITE_URL').'Uploads/image/'.$share_image_arr['value'];
		}


		$need_data = array();

		$qian=array("\r\n");
		$hou=array("@F@");
		$bonus_rule_str = str_replace($qian,$hou,$bonus_rule_arr['value']);
		$bonus_rule_str = explode('@F@',$bonus_rule_str);

		$need_data['kan_description_str'] = $bonus_rule_str;
		$need_data['bonus_share_title'] = $bonus_share_title_arr['value'];
		$need_data['bonus_game_time'] = $bonus_game_time_arr['value'];

		$need_data['bonus_count'] = $bonus_count_arr['value'];
		$need_data['is_open_fissionbonus'] = $is_open_fissionbonus_arr['value'];

		if($is_open_fissionbonus_arr['value'] == 0)
		{
			echo json_encode(array('code'=>1));
			die();
		}


		//检测是否签到过了。
		$begin_time = strtotime( date('Y-m-d').' 00:00:00' );
		$now_time = time();

		$over_where = " member_id = {$member_id} and (state = 0 and end_time < {$now_time} ) ";
		M('fissionbonus')->where($over_where)->save( array('state' =>2) );

		$where = " member_id = {$member_id} and (state = 0 or state = 1 ) and begin_time > {$begin_time} ";

		$fissionbonus_info = M('fissionbonus')->where( $where )->find();

		$bonus_id = $fissionbonus_info['id'];

		if(empty($fissionbonus_info) && $is_open_fissionbonus_arr['value'] == 1)
		{
			//开启一个签到
			$bonus_id = D('Seller/Fissionbonus')->open_new_bonus($member_id);
			//给自己插入今天的bonus金额
			//D('Seller/Fissionbonus')->get_fissionbonus_order($bonus_id, $member_id);

			$fissionbonus_info = M('fissionbonus')->where( array('id' => $bonus_id) )->find();
		}

		//is_me , has_click
		$is_me = 0;
		$has_click = 0;

		if($member_id == $fissionbonus_info['member_id'])
		{
			$is_me = 1;
		}

		$has_click_info =  M('fissionbonus_order')->where( array('fissionbonus_id' => $bonus_id, 'member_id' => $member_id) )->find();
		if( !empty($has_click_info) )
		{
			$has_click =1;
		}

		//order_list":null,"bonus_money":null,

		//获取今日签到的朋友名单
		$order_list = M('fissionbonus_order')->where( array('fissionbonus_id' => $bonus_id) )->order('id asc')->select();
		if( empty($order_list) )
		{
			$order_list = array();
		}else{
			foreach($order_list as  $key => $val)
			{
				// array('avatar' =>)
				$mb_info = M('member')->field('avatar')->where( array('member_id' => $val['member_id']) )->find();
				$val['avatar'] = $mb_info['avatar'];
				$order_list[$key] = $val;
			}
		}

		//统计已经领走金额
		$bonus_money = M('fissionbonus_order')->where( array('fissionbonus_id' => $bonus_id) )->sum('money');

		$need_data['bonus_id'] = $bonus_id;
		$need_data['is_me'] = $is_me;
		$need_data['is_over'] = $fissionbonus_info['state'];
		$need_data['has_click'] = $has_click;
		$need_data['order_list'] = $order_list;
		$need_data['bonus_money'] = empty($bonus_money) ? 0 : $bonus_money;
		$need_data['cur_time'] = time();
		$need_data['share_image'] = $share_image;
		$need_data['end_time'] = $fissionbonus_info['end_time'];
		$need_data['del_count'] = $fissionbonus_info['count'] - count($order_list);
		//del_count
		$go_for = array();
		for($i =0; $i < $need_data['del_count']; $i++)
		{
			if($i == $need_data['del_count'] -1 )
			{
				$go_for[] = 88;
			}else{
				$go_for[] = 1;
			}
		}
		$need_data['go_for'] = $go_for;

		echo json_encode( array('code' =>0,'data' =>$need_data) );
		die();
	}

	/***
		获取今日帮忙点击签到的提示效果
	**/
	public function get_fissionbunus_notify()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		if( empty($member_id) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$now_time = strtotime( date('Y-m-d').' 00:00:00' );

		$where = " member_id = {$member_id} and begin_time > {$now_time} ";

		$fissionbonus_info = M('fissionbonus')->where( $where )->find();


		if( !empty($fissionbonus_info) )
		{
			$bonus_id = $fissionbonus_info['id'];

			$order_info = M('fissionbonus_order')->where( array('fissionbonus_id' =>$bonus_id,'is_notify' => 0 ) )->order('id desc')->find();

			if( !empty($order_info) )
			{
				$member_info = M('member')->where( array('member_id' => $order_info['member_id']) )->find();

				M('fissionbonus_order')->where( array('fissionbonus_id' =>$bonus_id,'is_notify' => 0 ) )->save( array('is_notify' => 1) );

				echo json_encode( array('code' => 0, 'money' => $order_info['money'],'member_name' => $member_info['uname']) );
				die();

			}else{
				echo json_encode( array('code' => 1) );
				die();
			}

		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}

	public function click_fissionbunus()
	{
		$token = I('get.token');
		///  Api/click_fissionbunus/token/fbd91bce7e444fda0a34ca479c590822/bonus_id/1/other_bonus_id/0

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		if( empty($member_id) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		$bonus_id = I('get.bonus_id', 0);
		$bonus_info = M('fissionbonus')->where( array('member_id' => $member_id,'id' =>$bonus_id ) )->find();

		if( empty($bonus_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$self_money =  D('Seller/Fissionbonus')->get_fissionbonus_order($bonus_id, $member_id);

		$other_bonus_id = I('get.other_bonus_id', 0);
		$other_member_name = '';
		$other_money = 0;

		if( $other_bonus_id > 0 )
		{
			$other_money = D('Seller/Fissionbonus')->get_fissionbonus_order($other_bonus_id, $member_id);

			if($other_money > 0)
			{
				$other_bonus_info =  M('fissionbonus')->where( array('id' => $other_bonus_id) )->find();
				$other_member_info = M('member')->where( array('member_id' => $other_bonus_info['member_id']) )->find();
				$other_member_name = $other_member_info['uname'];
			}

		}

		$need_data = array();
		$fissionbonus_info = M('fissionbonus')->where( array('id' => $bonus_id) )->find();

		//获取今日签到的朋友名单
		$order_list = M('fissionbonus_order')->where( array('fissionbonus_id' => $bonus_id) )->order('id asc')->select();
		foreach($order_list as  $key => $val)
		{
			$mb_info = M('member')->field('avatar')->where( array('member_id' => $val['member_id']) )->find();
			$val['avatar'] = $mb_info['avatar'];
			$order_list[$key] = $val;
		}
		//统计已经领走金额
		$bonus_money = M('fissionbonus_order')->where( array('fissionbonus_id' => $bonus_id) )->sum('money');

		$need_data['bonus_id'] = $bonus_id;
		$need_data['is_over'] = $fissionbonus_info['state'];
		$need_data['has_click'] = 1;
		$need_data['order_list'] = $order_list;
		$need_data['bonus_money'] = $bonus_money;
		$need_data['other_money'] = $other_money;
		$need_data['self_money'] = $self_money;
		$need_data['other_member_name'] = $other_member_name;
		//$need_data['other_member_name'] = $other_member_name;

		$bonus_image = "";
		$detai_image =  M('config')->where( array('name' => 'fissionbonus_detail_image') )->find();

		if( !empty($detai_image['value']) )
		{
			$bonus_image = C('SITE_URL').'Uploads/image/'.$detai_image['value'];
		}
		$need_data['bonus_image'] = $bonus_image;


		$need_data['del_count'] = $fissionbonus_info['count'] - count($order_list);
		//del_count
		$go_for = array();
		for($i =0; $i < $need_data['del_count']; $i++)
		{
			if($i == $need_data['del_count'] -1 )
			{
				$go_for[] = 88;
			}else{
				$go_for[] = 1;
			}
		}
		$need_data['go_for'] = $go_for;

		echo json_encode( array('code' =>0,'data' =>$need_data) );
		die();
	}

	public function get_fissionbunus_info()
	{
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

		if( empty($member_id) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$member_info = M('member')->field('account_money,avatar,uname')->where( array('member_id' => $member_id) )->find();

		//总获得的签到金额
		$total_get_money = M('fissionbonus_flow')->where( array('member_id' => $member_id ,'type' => array('in','1,2')) )->sum('money');

		$cur_get_money = $member_info['account_money'];


		$need_data = array();
		$need_data['total_get_money'] = $total_get_money;
		$need_data['cur_get_money'] = $cur_get_money;
		$need_data['avatar'] = $member_info['avatar'];
		$need_data['nickname'] = $member_info['uname'];


		echo json_encode( array('code'=>0, 'data' => $need_data) );
		die();

	}


	public function fissionbunus_order_list()
	{
        $token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];

        $page = I('get.page',1);
        $pre_page = I('get.pre_page',15);
        $offset = ($page -1) * $pre_page;




		//$member
        $condition = array('member_id' => $member_id);

        $list = M('fissionbonus_flow')->where($condition)->order('id desc')->limit($offset,$pre_page)->select();

        $now_time = time();

        foreach($list as $key => $val)
        {
			if($val['type'] != 1)
			{
				$tmp_member = M('member')->field('uname')->where( array('member_id' => $val['send_member_id']) )->find();
				$val['send_member_name'] = $tmp_member['uname'];
			}
			$val['create_time'] = date('Y.m.d ', $val['addtime']);


            $list[$key] = $val;
        }

		if( empty($list) )
		{
			echo json_encode( array('code' =>1) );
		}else {
			echo json_encode( array('code' =>0, 'list' => $list) );
		}

	}

	public function banner_list()
	{
		if (!$slider_cache = S('xiao_index_cache')) {
			$slider=M('plugins_slider')->where( array('type' => 'xiao_index') )->field('image,url')->order('sort_order desc')->select();
			S('xiao_index_cache', $slider);
			$slider_cache=$slider;
		}

		$banner_list = array();
		foreach($slider_cache as $ad)
		{
			$tmp = array();
			//banner_type target_url  image_url   imageWidth   imageheight
			$tmp['banner_type'] = $ad['type'];
			$tmp['target_url'] = $ad['url'];
			$tmp['image_url'] = str_replace('http','https',C('SITE_URL')).'/Uploads/image/'.$ad['image'];
			$img_info  =  getimagesize(ROOT_PATH.'/Uploads/image/'.$ad['image']);
			$tmp['imageWidth'] = $img_info[0];
			$tmp['imageheight'] = $img_info[1];
			$banner_list[] = $tmp;
		}

		$data = array('code' => 1, 'banners' => $banner_list);
		echo json_encode($data);
		die();
	}

	public function goodsCate()
	{
		$key='index_goodscategory_cache';
		if (!$hot_list = S($key)) {
		    $hot_list = M('goods_category')->where( array('is_hot' => 1) )->order('sort_order desc')->select();
		    S($key, $hot_list);
		}
		//cate_id  cate_name id

		$need_data = array();
		foreach($hot_list as $key => $cate)
		{
			$tmp = array();
			$tmp['cate_id'] = $cate['id'];
			$tmp['cate_name'] = $cate['name'];
			$need_data[] = $tmp;
		}

		$data = array('code' => 1,'cates' => $need_data);

		echo json_encode($data);
		die();
	}

	public function goods_detail()
	{
		$goods_id = I('get.goods_id');
		//gallery =>img_url
		//goods goods.goods_desc  goods_name group_price  market_price  sell_count group_number

		$sql="select g.*,gd.description,gd.summary,gd.tag from ".
		C('DB_PREFIX')."goods g,".C('DB_PREFIX')."goods_description gd where g.goods_id=gd.goods_id and g.goods_id=".$goods_id;

		$goods_arr=M()->query($sql);

		$qian=array("\r\n");
		$hou=array("<br/>");
		$goods_arr[0]['summary'] = str_replace($qian,$hou,$goods_arr[0]['summary']);

		$sql="select image from ".C('DB_PREFIX')."goods_image where goods_id=".$goods_id;
		$goods_image=M()->query($sql);

		$gallery = array();
		$default_image = '';
		foreach($goods_image as $val)
		{
			$val['img_url'] = str_replace('http','https',C('SITE_URL')).'/Uploads/image/'.$val['image'];

			if(empty($default_image))
			{
				$default_image = str_replace('http','https',C('SITE_URL')).resize($val['image'], C('goods_thumb_width'), C('goods_thumb_height'));
			}

			$gallery[] = array('img_url' => $val['img_url']);
		}

		$goods = $goods_arr[0];

		$need_goods = array();
		$need_goods['goods_id'] = $goods['goods_id'];
		$need_goods['goods_name'] = $goods['name'];
		$need_goods['group_price'] = $goods['pinprice'];
		$need_goods['alone_price'] = $goods['danprice'];
		$need_goods['market_price'] = $goods['price'];
		$need_goods['sell_count'] = $goods['seller_count'] + $goods['virtual_count'];
		$need_goods['group_number'] = $goods['pin_count'];
		$need_goods['goods_desc'] = $goods['description'];
		$need_goods['sell_type'] = 0;

		$options=$this->get_goods_options($goods_id);

		$goods_option_mult_value = M('goods_option_mult_value')->where( array('goods_id' =>$goods_id) )->select();

		foreach($goods_option_mult_value as $key => $val)
		{
			$val['image'] = str_replace('http','https',C('SITE_URL')).resize($val['image'],200,200);
			$goods_option_mult_value[$key] = $val;
		}


		$result = array('code' => 1,'default_image' =>$default_image,'goods' => $need_goods,'goods_option_mult_value' =>$goods_option_mult_value, 'sku_options' =>$options,'option_names'=>implode('、',$options['name']),  'gallery' => $gallery);
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
					'checked' => 0,

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

	public function getGoods_bycate()
	{
		//&offset=0&size=5&cate_id=48
		$offset = I('get.offset',0);
		$pre_page = I('get.size',10);
		$cate_id = I('get.cate_id',0);


		$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$cate_id} or class_id2 ={$cate_id} or class_id3 = {$cate_id}  ")->field('goods_id')->select();

		$ids_arr = array();
		foreach($goods_ids_arr as $val){
			$ids_arr[] = $val['goods_id'];
		}
		$ids_str = implode(',',$ids_arr);

		$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'type'=>array('neq','lottery'),'quantity' =>array('gt',0) );

		$list = M('goods')->field('goods_id,name,image,pinprice,danprice')->where($condition)->order('seller_count desc,goods_id asc')->limit($offset,$pre_page)->select();

		$need_data = array();
		foreach($list as $goods)
		{
			$tmp = array();
			$tmp['goods_id'] = $goods['goods_id'];
			$tmp['image_url'] = C('SITE_URL').'/Uploads/image/'.$goods['image'];
			$tmp['goods_name'] = $goods['name'];
			$tmp['group_price'] = $goods['pinprice'];
			$tmp['alone_price'] = $goods['danprice'];
			$need_data[] = $tmp;
		}

		$result = array('goods'  => $need_data);
		echo json_encode($result);
		die();
	}

	public function Goods_lists()
	{
		//data.goods.length
		// "offset" : offset,
		//"size" : size  C('SITE_URL').'/Uploads/image/'
		//goods => goods_id  image_url goods_name  group_price  group_number  alone_price

		$offset = I('get.offset',0);
		$pre_page = I('get.size',10);
		$condition = array('status'=>1,'is_index_show' => 1,'type'=>array('neq','lottery'), 'quantity' =>array('gt',0) );


		$list = M('goods')->field('goods_id,name,image,pinprice,danprice')->where($condition)->order('index_sort desc ,seller_count desc,goods_id asc')->limit($offset,$pre_page)->select();

		$need_data = array();
		foreach($list as $goods)
		{
			$tmp = array();
			$tmp['goods_id'] = $goods['goods_id'];
			$tmp['image_url'] = C('SITE_URL').'/Uploads/image/'.$goods['image'];
			$tmp['goods_name'] = $goods['name'];
			$tmp['group_price'] = $goods['pinprice'];
			$tmp['alone_price'] = $goods['danprice'];
			$need_data[] = $tmp;
		}

		$result = array('goods'  => $need_data);
		echo json_encode($result);
		die();
	}

    public function index(){

		$page = I('get.page',1);
		$pre_page = 4;
		$condition = array('status'=>1,'is_index_show' => 1, 'quantity' =>array('gt',0) );
		$offset = ($page -1) * $pre_page;

		$list = M('goods')->where($condition)->order('index_sort desc ,seller_count desc,goods_id asc')->limit($offset,$pre_page)->select();
		$goods_model = D('Home/Goods');

		if(!empty($list))
		{
			foreach($list as $key => $val)
			{
				$val['avatar_list'] = $goods_model->get_goods_pin_avatar($val['goods_id'],10);
				$list[$key] = $val;
			}
		}
		$this->list = $list;

		$subject_pre_page = 1;
		$sub_where = " begin_time <".time()." and end_time >".time()." and type != 'haitao' ";

		$subject_offset = ($page -1) * $subject_pre_page;
		$sublist_list = M('subject')->where( $sub_where )->order('begin_time desc')->limit($subject_offset,$subject_pre_page)->select();

		//quan_list

		$where = "total_count>send_count and store_id =0 and is_index_show=1 and end_time>".time();

		$quan_list = M('voucher')->where($where)->order('add_time desc')->limit(5)->select();

		$this->quan_list = $quan_list;

		if(!empty($sublist_list))
		{
			$can_subject_list = array();
			foreach($sublist_list as $key => $val)
			{
				$subject_goods = M('subject_goods')->where( array('state' => 1, 'subject_id' => $val['id']) )->order('id asc ')->limit(10)->select();

				$need_subject_goods = array();

				foreach($subject_goods as $kk => $vv)
				{
					$tp_goods = M('goods')->where( array('goods_id' => $vv['goods_id'],'status'=>1) )->find();
					if(empty($tp_goods))
					{
						continue;
					}
					if(!empty($tp_goods['fan_image'])){
						$tp_goods['image']=resize($tp_goods['fan_image'], 220, 220);
					}else {
						$tp_goods['image']=resize($tp_goods['image'], 220, 220);
					}
					$vv['goods'] = $tp_goods;

					//$subject_goods[$kk] = $vv;
					$need_subject_goods[$kk] = $vv;
				}
				if(!empty($subject_goods)) {

					$val['goods_list'] = $need_subject_goods;
					$can_subject_list[$key] = $val;
				}
			}
		}

		$this->sublist_list = $can_subject_list;

		if($page > 1) {
			$content = $this->fetch('Widget:index_ajax_goods_list_fetch');
			$result = array('code' => 0);
			if(!empty($list)) {
				$result['code'] = 1;
				$result['html'] = $this->fetch('Widget:index_ajax_goods_list_fetch');
			}
			echo json_encode($result);
			die();
		}


		$appid_info 	=  M('config')->where( array('name' => 'APPID') )->find();
		$appsecret_info =  M('config')->where( array('name' => 'APPSECRET') )->find();

		$weixin_config = array();
		$weixin_config['appid'] = $appid_info['value'];
		$weixin_config['appscert'] = $appsecret_info['value'];

		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);


		$uid = is_login();

		$member_info = M('member')->where( array('member_id' => $uid) )->find();

		$is_sub = $jssdk->cgigetinfo($member_info['openid']);

		$sub_url = C('SHORT_URL');
		$site_title = C('SITE_TITLE');
		$site_name = C('SITE_NAME');
		$site_logo = C('SITE_ICON');

		$this->is_sub = $is_sub;
		$this->sub_url = $sub_url;
		$this->site_title = $site_title;
		$this->site_name = $site_name;
		$this->site_logo = $site_logo;

	   $this->display();
    }



}
