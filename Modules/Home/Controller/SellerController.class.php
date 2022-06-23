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

class SellerController extends CommonController {
    public function info(){

		$pre_page = 10;
		$page = I('post.page',1);
		$seller_id = I('get.seller_id',0);
		$is_ajax = I('post.is_ajax',0);
		$gid = I('post.gid',0);

		$order_by = I('post.order_by','default');
		if(empty($seller_id))
		{
			$seller_id = I('post.seller_id',0);
		}

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
			case 'price':
					$order_by = 'danprice asc';
					break;
		}

		//default  hot  new
		$where = " and g.store_id = {$seller_id} and g.status =1 and g.quantity > 0 ";
		//$condition = array('store_id' => $seller_id, 'status'=>1,'quantity' =>array('gt',0) );

		$offset = ($page -1) * $pre_page;

		//$list = M('goods')->where($condition)->order($order_by)->limit($offset,$pre_page)->select();
		//goods_to_category
		if( $gid > 0)
		{
			$where .= " and  (gt.class_id1 = {$gid} or gt.class_id2 = {$gid} or gt.class_id3 = {$gid}) ";
		}

		$sql = "select g.* from ".C('DB_PREFIX')."goods as g ,".C('DB_PREFIX')."goods_to_category as gt
				where g.goods_id = gt.goods_id  {$where} order by {$order_by}  limit {$offset},{$pre_page} ";
		$list = M()->query($sql);

		$goods_model = D('Home/Goods');

		if(!empty($list)) {
			foreach($list as $key => $v){
				$list[$key]['seller_count'] += $v['virtual_count'];

				$price_arr = $goods_model->get_goods_price($v['goods_id']);

				$list[$key]['danprice'] = $price_arr['danprice'];

				if(empty($v['fan_image'])){

					$list[$key]['image']=resize($v['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}else{
					$list[$key]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}

			}
		}

		$this->list = $list;
		$this->seller_id = $seller_id;

		$seller_info = M('seller')->where( array('s_id' => $seller_id) )->find();
		$this->seller_info = $seller_info;
		$where = "total_count>send_count and store_id ={$seller_id} and is_index_show=1 and end_time>".time();


		$quan_list = M('voucher')->where($where)->order('add_time desc')->limit(5)->select();
		$this->quan_list = $quan_list;

		$seller_model = D('Home/Seller');

		$seller_count = $seller_model->getStoreSellerCount($seller_id);

		$goods_count = M('goods')->where( array('store_id' => $seller_id, 'status' => 1) )->count();
		$this->goods_count = $goods_count;

		$this->seller_count = $seller_count;

		$store_class_list =  M('store_bind_class')->where( array('seller_id' => $seller_id) )->order('bid asc')->select();

		$store_class_ids = array();
		foreach($store_class_list as $class_val)
		{
			$store_class_ids[] = $class_val['class_1'];
		}

		$category_list = array();

		if( !empty($store_class_ids) )
		{
			//$store_class_ids_str = implode(',', $store_class_ids);
			$category_list = M('goods_category')->where( array('id' => array('in', $store_class_ids) ) )->select();
			//var_dump($category_list);die();

		}
		$this->category_list = $category_list;

		if($page > 1 || $is_ajax == 1) {
			$result = array('code' => 0);
			if(!empty($list)) {
				$result['code'] = 1;
				$result['html'] = $this->fetch('Widget:seller_ajax_goods_list_fetch');
			}
			echo json_encode($result);
			die();
		}
		$goods_model = D('Home/Goods');
		$member_id = is_login();
		$fans_count = M('user_favstore')->where( array('store_id' => $seller_id) )->count();
		$this->fans_count  =  $fans_count;
		$is_fav_seller =  $goods_model->check_store_fav($seller_id, $member_id);

		$goods_count =  M('goods')->where( array('store_id' => $seller_id) )->count();

		$this->is_fav_seller = $is_fav_seller;
		$this->open_web_kefu = C('open_duokefu');
       $this->display('index');

    }
	/**
		连接后台客服
		@param room_id 卖家id
	**/
	public function kefu_seller()
	{
		//login_data = '{"type":"login","domain":"'+domain+'","uid":"2","room_id":1}';
		$room_id = I('get.store_id');
		$goods_id = I('get.goods_id',0);

		$site_url_arr = M('config')->field('value')->where( array('name' => 'SITE_URL') )->find();
		$site_url = $site_url_arr['value'];

		$goods_info = array();
		if($goods_id >0)
		{
			$goods_info = M('goods')->field('name,type,image,danprice')->where( array('goods_id' => $goods_id) )->find();
			$goods_info['image']=$site_url. resize($goods_info['image'], 200, 200);
			$goods_info['pinprice'] = $goods_info['danprice'];
			//type  pintuan
			if( $goods_info['type'] ==  'pintuan' || $goods_info['type'] ==  'lottery' )
			{
				$pin_goods =  M('pin_goods')->where( array('goods_id' => $goods_id) )->find();

				$goods_info['pinprice'] = $pin_goods['pin_price'];
				$goods_info['pin_count'] = $pin_goods['pin_count'];
			}
		}
		$this->goods_info = $goods_info;


		$member_id = is_login();
		$member_info = M('member')->field('name,avatar')->where( array('member_id' => $member_id) )->find();



		$seller_info = M('seller')->field('s_id,s_true_name,s_logo')->where( array('s_id' => $room_id) )->find();
		$seller_info['s_logo'] = $site_url.'Uploads/image/'.$seller_info['s_logo'];


		$this->seller_info = $seller_info;
		$this->member_id = $member_id;
		$this->member_info = $member_info;
		$this->display();
	}

	public function pickup_pickage()
	{

		$member_id = is_login();
		//pick_id/2.

		//$member_id = is_login();
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();
		$pick_member_info = M('pick_member')->where( array('member_id' => $member_id) )->find();

		if(empty($pick_member_info))
		{
			echo json_encode( array('code' => 0,'msg' => '您不是核销人员') );
			die();
		}


		$pick_id = I('get.pick_id');

		$pick_order = M('pick_order')->where( array('id' => $pick_id) )->find();

		//$pick_member_info = M('pick_member')->where( array('member_id' => $member_id) )->find();

		if($pick_member_info['pick_up_id'] > 0 && $pick_member_info['pick_up_id'] != $pick_order['pick_id'])
		{
			echo json_encode( array('code' => 0,'msg' => '您无此门店核销权限') );
			die();
		}


		//order_id  pick_id
		$order_id = $pick_order['order_id'];

		$order_goods = M('order_goods')->where( array('order_id' => $pick_order['order_id']) )->find();



		if($order_goods['store_id'] != $pick_member_info['store_id'])
		{
			echo json_encode( array('code' => 0,'msg' => '您无此商家核销权限') );
			die();
		}

		$pick_id = $pick_order['id'];

		//$pick_id = I('get.pick_id');
		$pick_order = M('pick_order')->where( array('id' => $pick_id) )->find();
		$member_id = is_login();
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();
		$result = array('code' => 1,'msg' => '');


		$order_goods = M('order_goods')->where( array('order_id' => $pick_order['order_id']) )->find();

		//pick_member_id

		$res = M('pick_order')->where( array('id' => $pick_id) )->save( array('pick_member_id' => $member_id, 'state' => 1, 'tihuo_time' => time()) );
		if($res)
		{
			M('order')->where( array('order_id' => $pick_order['order_id']) )->save( array('order_status_id' => 6) );

			$history_data = array();
			$history_data['order_id']  = $pick_order['order_id'];
			$history_data['order_status_id']  = 6;
			$history_data['notify']  = 0;
			$history_data['comment']  = '用户提货，核销';
			$history_data['date_added']  = time();
			M('order_history')->add($history_data);

			$notify_model = D('Home/Weixinnotify');
			$notify_model->sendPickupsuccessMsg($pick_order['order_id']);
			$result['code'] = 1;
		}
		echo json_encode($result);
		die();
	}
	public function pickup_pickage2()
	{
		$pick_id = I('get.pick_id');
		$pick_order = M('pick_order')->where( array('id' => $pick_id) )->find();
		$member_id = is_login();
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();
		$result = array('code' => 0,'msg' => '');

		if($member_info['bind_seller_pickup'] <= 0)
		{
			$result['msg'] = '您不是核销管理员';
			echo json_encode($result);
			die();
		}

		$order_goods = M('order_goods')->where( array('order_id' => $pick_order['order_id']) )->find();
		if($order_goods['store_id'] != $member_info['bind_seller_pickup'])
		{
			$result['msg'] = '您不是该店铺的核销管理员';
			echo json_encode($result);
			die();
		}


		$res = M('pick_order')->where( array('id' => $pick_id) )->save( array('state' => 1, 'tihuo_time' => time()) );
		if($res)
		{
			M('order')->where( array('order_id' => $pick_order['order_id']) )->save( array('order_status_id' => 6) );

			$history_data = array();
			$history_data['order_id']  = $pick_order['order_id'];
			$history_data['order_status_id']  = 6;
			$history_data['notify']  = 0;
			$history_data['comment']  = '用户提货，核销';
			$history_data['date_added']  = time();
			M('order_history')->add($history_data);

			$notify_model = D('Home/Weixinnotify');
			$notify_model->sendPickupsuccessMsg($pick_order['order_id']);
			$result['code'] = 1;
		}
		echo json_encode($result);
		die();
	}

	public function hexiao_pickup()
	{



		$pick_sn = I('get.pick_sn');

		$pick_order = M('pick_order')->where( array('pick_sn' => $pick_sn) )->find();
		//order_id  pick_id
		$order_id = $pick_order['order_id'];

		$order_goods = M('order_goods')->where( array('order_id' => $pick_order['order_id']) )->find();


		$order_info = M('order')->where( array('order_id' => $order_id) )->find();
	    $order_status_info = M('order_status')->where( array('order_status_id' => $order_info['order_status_id']) )->find();

		$order_option_info = M('order_option')->where( array('order_id' =>$order_id,'order_goods_id' => $order_goods['goods_id']) )->select();

	    $goods_info = M('goods')->where( array('goods_id' => $order_goods['goods_id']) )->find();

		$voucher_info = array();
	    if($order_info['voucher_id'] > 0) {
	           $voucher_info =  M('voucher_list')->where( array('id' => $order_info['voucher_id']) )->find();

	    }

	    $goods_info['image']=resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
	    $pin_model = D('Home/Pin');
	    if($order_info['order_status_id'] == 2)
	    {
	        //判断拼团是否失败
	        if($order_info['is_pin'] == 1 && $order_info['pin_id'] > 0)
	        {
	            $state = $pin_model->getNowPinState($order_info['pin_id']);
	            if($state == 2){
	                $order_status_info['name'] = '拼团失败，等待退款';
	            }
	        }
	    }
	    //判断是否抽奖订单
	    //组团成功，等待抽奖    已成团，待抽奖   组团成功一等奖   已成团，一等奖
	    if($order_info['order_status_id'] == 1)
	    {
	        //待发货订单,并且未中奖
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
		//$order_id order_id  $val['hash_order_id']= $hashids->encode($val['order_id']);
		$order_info['hash_order_id']= $hashids->encode($order_info['order_id']);

	    $this->order_info = $order_info;
	    $this->order_status_info = $order_status_info;
	    $this->shipping_province = $shipping_province;
	    $this->shipping_city = $shipping_city;
	    $this->shipping_country = $shipping_country;
	    $this->order_goods = $order_goods;
	    $this->store_info = $store_info;
	    $this->order_option_info = $order_option_info;
	    $this->goods_info = $goods_info;
	    $this->voucher_info = $voucher_info;

		$this->display();
	}
	/**
	  绑定自提订单核销管理员
	**/
	public function bind_pickup_order()
	{
		$seller_id = I('get.seller_id');
		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

		$seller_arr=$hashids->decode($seller_id);
		$seller_id = $seller_arr[0];

		$pick_up_id = I('get.pick_up_id',0);

		$pick_up_info = array();
		if( $pick_up_id > 0 )
		{
			//id
			$pick_up_info = M('pick_up')->where( array('id' => $pick_up_id,'store_id' => $seller_id) )->find();
		}

		$this->pick_up_info = $pick_up_info;
		$this->pick_up_id = $pick_up_id;
        $this->seller_id = $seller_id;
        $this->display();

	}
	/**
	  解除绑定自提订单核销管理员
	**/
	public function unbind_pickup_order()
	{
		$seller_id = get_url_id('seller_id');
        $member_id = is_login();

		$data = array();
        $data['bind_seller_pickup'] = 0;

		$res = M('member')->where( array('member_id' => $member_id) )->save($data);

        $this->display();
	}
	public function bind_pickup_post()
	{
		$seller_id = I('get.seller_id');
		$pick_up_id = I('get.pick_up_id',0);

        $member_id = is_login();
        //'pick_up_id' =>$pick_up_id
        //$data = array();
        //$data['bind_seller_pickup'] = $seller_id;


		$pick_member_info =  M('pick_member')->where( array('member_id' =>$member_id) )->find();
		$data = array();
		$data['member_id'] = $member_id;
		$data['pick_up_id'] = $pick_up_id;
		$data['state'] = 1;
		$data['store_id'] = $seller_id;
		$data['addtime'] = time();

		if( !empty($pick_member_info) )
		{
			$res = M('pick_member')->where( array('member_id' =>$member_id) )->save($data);
		}else{
			$res = M('pick_member')->where( array('member_id' =>$member_id) )->add($data);
		}


        //$res = M('member')->where( array('member_id' => $member_id) )->save($data);

        $result = array('code' => 100,'msg' => '绑定失败');
        if($res) {
            $result['code'] = 200;
        }
        echo json_encode($result);
        die();
	}


    /**
     * 绑定发送消息管理员
     */
    public function bind_order_notify()
    {
        $seller_id = get_url_id('seller_id');

        $this->seller_id = $seller_id;
        $this->display();
    }

	/**
		解除管理员消息绑定
	**/
	public function unbind_order_notify()
	{
		$seller_id = get_url_id('seller_id');
        $member_id = is_login();

		$data = array();
        $data['bind_seller_id'] = 0;

		$res = M('member')->where( array('member_id' => $member_id) )->save($data);

        $this->display();
	}

    public function bind_post()
    {
        $seller_id = I('get.seller_id');
        $member_id = is_login();

        $data = array();
        $data['bind_seller_id'] = $seller_id;

        $res = M('member')->where( array('member_id' => $member_id) )->save($data);

        $result = array('code' => 100,'msg' => '绑定失败');
        if($res) {
            $result['code'] = 200;
        }
        echo json_encode($result);
        die();
    }
    public function apply()
    {
        $goods_category = M('goods_category')->where( array('pid' => 0) )->select();

        $this->goods_category = $goods_category;
        $this->display();
    }
    public function apply_post()
    {
        $member_id = is_login();
        //result.code=='200'
       $result = array('code' => 0,'data'=>'');
       $count = M('apply')->where( array('member_id' => $member_id) )->count();


		 $ck_apply_name = M('apply')->where( array('store_name' => I('post.businessName','','htmlspecialchars')) )->find();
		 if(!empty($ck_apply_name))
		 {
			$result['data'] = '该店铺名称已经被申请';
			echo json_encode($result);
			die();
		 }

		 $ck_apply_mobile = M('apply')->where( array('mobile' => I('post.managerMobile','','htmlspecialchars')) )->find();
		 if(!empty($ck_apply_mobile))
		 {
			$result['data'] = '该手机号已经申请入驻';
			echo json_encode($result);
			die();
		 }

		 $ckname = M('seller')->where( array('s_true_name' =>I('post.businessName','','htmlspecialchars') ) )->find();
		 if(!empty($ckname))
		 {
			$result['data'] = '该店铺名称已经存在';
			echo json_encode($result);
			die();
		 }
		 $ckmobile = M('seller')->where( array('s_mobile' =>I('post.managerMobile','','htmlspecialchars')) )->find();
		 if(!empty($ckname))
		 {
			$result['data'] = '该手机号已经被店铺注册';
			echo json_encode($result);
			die();
		 }

       if($count >= 3)
       {
           $result['data'] = '您已申请过了!';
           echo json_encode($result);
           die();
       }

       $data = array();
       $data['username'] = I('post.managerName','','htmlspecialchars');
       $data['member_id']  = $member_id;
       $data['mobile'] = I('post.managerMobile','','htmlspecialchars');
       $data['email'] = I('post.managerEmail','','htmlspecialchars');
       $data['store_name'] = I('post.businessName','','htmlspecialchars');
       $data['city'] = I('post.businessCity','','htmlspecialchars');
       $data['category_id'] = I('post.businessMainCategory','','htmlspecialchars');
       $data['addtime'] = time();

       M('apply')->add($data);
       $result['code'] = 200;
       echo json_encode($result);
       die();
    }
    public function getQuan()
    {
        $result = array('code' => 0,'msg' => '被抢光啦');
        $quan_id = I('post.quan_id',0);
        if($quan_id >0){
            $quan_model = D('Home/Voucher');
           $res =  $quan_model->send_user_voucher_byId($quan_id,is_login(),true);

           //1 被抢光了 2 已领过  3  领取成功
           $mes_arr = array(1 => '被抢光了',2 => '已领过', 3 => '领取成功');

           $result['msg'] = $mes_arr[$res];
        }
        echo json_encode($result);
        die();
    }

}
