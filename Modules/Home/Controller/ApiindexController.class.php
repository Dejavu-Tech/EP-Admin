<?php
namespace Home\Controller;

class ApiindexController extends CommonController {
    protected function _initialize()
    {
    	parent::_initialize();
        $this->cur_page = 'apiindex';  
		 
    }	
	/**
		获取当前前端小程序样式类型
	**/
	public function get_cur_theme_type()
	{
		$type = C('THEME_TYPE');
		$cpage = I('get.cpage','index');
		
		$title = C('SITE_NAME');
		
		switch($title)
		{
			case 'index': 
					$title = C('SITE_NAME'); 
					break;
		}
		
		
		echo json_encode( array('code' => 0 , 'type' => $type, 'title' => $title) );
		die();
	}
	/**		加载首页广告位	**/	
	public function load_index_addata()	{				
		$type = I('get.type','index_wepro_head');				
		//if (!$slider_cache = S('slider_'.$type.'_cache')) {			
			$slider=M('plugins_slider')->where( array('type' => $type) )->field('slider_id,slider_name,image,url')->order(' sort_order desc,slider_id desc')->select();			
			S('slider_'.$type.'_cache', $slider);			
			$slider_cache=$slider;		
		//}		
		//var_dump($slider, M('plugins_slider')->getLastSql());die();
		
	
		$need_data = array();		
		foreach($slider_cache as $key => $val)		
		{			
			$need_data[$key]['name'] = $val['slider_name'];	
			$need_data[$key]['slider_id'] = $val['slider_id'];			
			$need_data[$key]['image'] = C('SITE_URL').'Uploads/image/'.$val['image'];			
			$need_data[$key]['url'] = $val['url'];		
		}					
		echo json_encode( array('code' => 0, 'data' => $need_data) );		
		die();	
	}	
	
	public function ad_detail()
	{
		$slider_id = I('get.slider_id');
		$info = M('plugins_slider')->where( array('slider_id' => $slider_id) )->find();
		
		$info['webview_url'] = htmlspecialchars_decode($info['webview_url']);
		echo json_encode( array('code' => 0 ,'info' =>$info ) );
		die();
	}
	/**
		检测今天是否需要弹窗
	**/
	public function check_index_bonus_window()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];
		
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();
		
		if( empty($member_info) )
		{
			echo json_encode( array('code' =>1) );
			die();
		}
		$tan_info = M('config')->where( array('name' => 'fissionbonus_index_tan') )->find();
		
		if( $tan_info['value'] ==1)
		{
			$begin_time = strtotime( date('Y-m-d').' 00:00:00' );
			$where = " member_id = {$member_id} and is_self = 1 and addtime > {$begin_time} ";
			
			$order_info =  M('fissionbonus_order')->where($where)->find();
			
			if( !empty($order_info) )
			{
				//今日已经签到过了
				echo json_encode( array('code' =>1) );
				die();
			}else{
				//今日未签到，要弹出窗口
				//检测是否有图片
				$fissionbonus_index_image_info = M('config')->where( array('name' => 'fissionbonus_index_image') )->find();
				
				if( !empty($fissionbonus_index_image_info['value']) )
				{
					$image = C('SITE_URL').'Uploads/image/'.$fissionbonus_index_image_info['value'];
					echo json_encode( array('code' =>0,'image' => $image) );
					die();
				}else{
					echo json_encode( array('code' =>1) );
					die();
				}
				
			}
		}else{
			echo json_encode( array('code' =>1) );
			die();
		}
	}
	
	
	public function index_share()
	{
		$index_share_titile = C('index_share_titile');
		
		
		echo json_encode( array('code' => 0, 'title' => $index_share_titile) );
		die();
	}
		/**		加载分页的推荐拼团数据	**/	
	public function load_index_pintuan()	
	{				
		$page = I('get.page',1,'intval');		
		$per_page = I('get.per_page', 10, 'intval');	
		
		$gid = I('get.gid', 0, 'intval');
		$store_id = I('get.store_id', 0, 'intval');
		$orderby = I('get.orderby', 'default');
		
		$is_index_show = I('get.is_index_show', 1, 'intval');
		
		
		$type = I('get.type', 'normal');
		//begin_time  end_time
		$now_time = time();
		$offset = ($page -1) * $per_page;		

		$where = "g.status =1 and (g.type != 'normal' and g.type != 'bargain' and g.type != 'integral') and g.quantity >0 and pg.type='{$type}' ";
		if( $type =='all' )
		{
			$where = "g.status =1 and g.quantity >0  ";
		}
		if( !empty($gid) && $gid >0 )
		{
			$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$gid} or class_id2 ={$gid} or class_id3 = {$gid}  ")->field('goods_id')->select();
			
			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}
			$ids_str = implode(',',$ids_arr);
			
			//$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'quantity' =>array('gt',0) );
			if( !empty($ids_str) )
			{
				$where .= " and g.goods_id in ({$ids_str})";
			} else{
				$where .= " and 0 ";
			}
		}
		
		if($store_id > 0)
		{
			$where .= " and g.store_id = {$store_id} ";
		}
		
		if($is_index_show == 1)
		{
			$where .= " and g.is_index_show = 1 ";
		}else if($is_index_show == 0){
			$where .= " and g.is_index_show = 0 ";
		}
		
		$sortby = ' pg.id desc ';
		if($orderby  == 'default')
		{
			$sortby = ' g.index_sort desc,g.goods_id desc ';
		}
		else if($orderby == 'new')
		{
			$sortby = ' g.goods_id desc ';
		}
		else if($orderby == 'seller_count')
		{
			$sortby = ' (g.seller_count + g.virtual_count) desc ';
		}
		else if($orderby  == 'rand'){
			$sortby = ' rand() ';
		}
		$goods_model = D('Home/goods');
		if($type != 'lottery')
		{
			$where .= " and pg.begin_time < {$now_time} and pg.end_time >{$now_time} ";
		
		}
		
		$ping_goods = D('Home/Pingoods')->get_pingoods_list('*', $where,$sortby,$offset,$per_page);						
		$need_data = array();				
		if( !empty($ping_goods) )		
		{				
			foreach($ping_goods as $key => $val)			
			{				
				if( !empty($val['fan_image']) )				
				{					
					$fan_image  = C('SITE_URL').resize($val['fan_image'], 300, 300);					
					$val['fan_image'] = $fan_image;				
				}else {					
					$val['fan_image'] = C('SITE_URL').resize($val['image'], 300, 300);				
				}				
				$val['url'] = U('/goods/'.$val['goods_id']);
				
				 //["type"]=> ,'oneyuan','haitao','normal','bargain','integral'
				//	string(7) "lottery"
				$val['is_open'] = -1;
				if($val['type'] == 'lottery')
				{
					//is_open_lottery
					$lottery_goods =  M('lottery_goods')->where( array('goods_id' => $val['goods_id']) )->find();
					if($lottery_goods['is_open_lottery'] == 1)
					{
						$val['is_open'] = 1;
					}else if($lottery_goods['end_time'] < time() )
					{
						$val['is_open'] = 2;
					}
				}
				
				$desc_info = D('Home/Pingoods')->get_goods_description($val['goods_id'],'summary');								
				$need_data[$key]['goods_id'] = $val['goods_id'];	

				$need_data[$key]['is_open'] = $val['is_open'];
				$need_data[$key]['type'] = $val['type'];
				
				$need_data[$key]['image'] = $val['fan_image'];
				$need_data[$key]['orign_image'] = C('SITE_URL').'Uploads/image/'.$val['image'];				
				$need_data[$key]['pin_price'] = $val['pin_price'];				
				$need_data[$key]['danprice'] = $val['danprice'];	
				
				$price_arr = $goods_model->get_goods_price($val['goods_id']);
				$need_data[$key]['pin_price'] = $price_arr['pin_price'];
				$need_data[$key]['danprice'] = $price_arr['danprice'];
				$need_data[$key]['price'] = $val['price'];
				
						
				$need_data[$key]['pin_hour'] = $val['pin_hour'];				
				$need_data[$key]['pin_count'] = $val['pin_count'];				
				$need_data[$key]['name'] = $val['name'];				
				$need_data[$key]['seller_count'] = $val['seller_count']+ $val['virtual_count'];	

				$fav_goods =  M('user_favgoods')->where( array('goods_id' => $val['goods_id']) )->count();
				
				$need_data[$key]['fav_goods'] = $fav_goods;
				
				//summary
				$need_data[$key]['quantity'] = $val['quantity'];				
				$need_data[$key]['summary'] = htmlspecialchars_decode($desc_info['summary']);
				$need_data[$key]['url'] = $val['url'];					
			}		
		}				
		if( !empty($need_data) )
		{
			echo json_encode( array('code' =>0, 'data' => $need_data) );		
			die();	
		} else{
			
			echo json_encode( array('code' =>1) );		
			die();	
		}
		
	}	
	
	public function load_user_qrcode()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();
		$member_id = $weprogram_token['member_id'];
		
		$member_info = M('member')->field('wepro_qrcode')->where( array('member_id' => $member_id) )->find();
		
		if(!empty($member_info['wepro_qrcode']))
		{
			$result = array('code' => 0, 'image_path' => $member_info['wepro_qrcode']);
			echo json_encode($result);
			die();
		}else{
			$goods_model = D('Home/Goods');
			$rocede_path = $goods_model->_get_index_user_wxqrcode(0,$member_id);
			$res = $goods_model->_get_compare_qrcode_bgimg(C('user_qrcode_image'), $rocede_path,C('user_qrcode_x'), C('user_qrcode_y'));
			
			M('member')->field('wepro_qrcode')->where( array('member_id' => $member_id) )->save( array('wepro_qrcode' => $res['full_path']) );
			
			$result = array('code' => 0, 'image_path' => $res['full_path']);
			echo json_encode($result);
			die();
		}
		
	}
	
	public function load_index_bargain_pintuan()	
	{				
		$page = I('get.page',1,'intval');		
		$per_page = I('get.per_page', 5, 'intval');	
		
		$gid = I('get.gid', 0, 'intval');
		$store_id = I('get.store_id', 0, 'intval');
		$orderby = I('get.orderby', 'default');
		
		$is_index_show = I('get.is_index_show', 1, 'intval');
		
		
		 $kan_rules = C('kan_rules');
		
		 $qian=array("\r\n");
		 $hou=array("@F@");
		 $kan_rules_str = str_replace($qian,$hou,$kan_rules); 
		 $kan_rules_str = explode('@F@',$kan_rules_str);
		 
		$type = I('get.type', 'normal');
		//begin_time  end_time
		$now_time = time();
		$offset = ($page -1) * $per_page;		

		$where = "g.status =1 and g.type != 'normal' and g.quantity >0  ";
		if( $type =='all' )
		{
			$where = "g.status =1 and g.quantity >0  ";
		}
		if( !empty($gid) && $gid >0 )
		{
			$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$gid} or class_id2 ={$gid} or class_id3 = {$gid}  ")->field('goods_id')->select();
			
			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}
			$ids_str = implode(',',$ids_arr);
			
			//$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'quantity' =>array('gt',0) );
			if( !empty($ids_str) )
			{
				$where .= " and g.goods_id in ({$ids_str})";
			} else{
				$where .= " and 0 ";
			}
		}
		
		if($store_id > 0)
		{
			$where .= " and g.store_id = {$store_id} ";
		}
		
		if($is_index_show == 1)
		{
			$where .= " and g.is_index_show = 1 ";
		}else if($is_index_show == 0){
			$where .= " and g.is_index_show = 0 ";
		}
		
		$sortby = ' pg.id desc ';
		if($orderby  == 'default')
		{
			$sortby = ' g.index_sort desc,g.goods_id desc ';
		}
		else if($orderby == 'new')
		{
			$sortby = ' g.goods_id desc ';
		}
		else if($orderby == 'seller_count')
		{
			$sortby = ' (g.seller_count + g.virtual_count) desc ';
		}
		else if($orderby  == 'rand'){
			$sortby = ' rand() ';
		}
		$goods_model = D('Home/goods');
		if($type != 'lottery')
		{
			$where .= " and pg.begin_time < {$now_time} and pg.end_time >{$now_time} ";
		
		}
		
		$ping_goods = D('Home/Pingoods')->get_bargaingoods_list('*', $where,$sortby,$offset,$per_page);						
		$need_data = array();				
		if( !empty($ping_goods) )		
		{				
			foreach($ping_goods as $key => $val)			
			{				
				if( !empty($val['fan_image']) )				
				{					
					$fan_image  = C('SITE_URL').resize($val['fan_image'], 300, 300);					
					$val['fan_image'] = $fan_image;				
				}else {					
					$val['fan_image'] = C('SITE_URL').resize($val['image'], 300, 300);				
				}				
				$val['url'] = U('/goods/'.$val['goods_id']);
				
				 //["type"]=>
				//	string(7) "lottery"
				$val['is_open'] = -1;
				
				
				$desc_info = D('Home/Pingoods')->get_goods_description($val['goods_id'],'summary');								
				$need_data[$key]['goods_id'] = $val['goods_id'];	

				$need_data[$key]['is_open'] = $val['is_open'];
				
				$need_data[$key]['image'] = $val['fan_image'];
				$need_data[$key]['orign_image'] = C('SITE_URL').'Uploads/image/'.$val['image'];				
				$need_data[$key]['pin_price'] = $val['pin_price'];				
				$need_data[$key]['danprice'] = $val['danprice'];	
				
				$price_arr = $goods_model->get_goods_price($val['goods_id']);
				$need_data[$key]['pin_price'] = $price_arr['pin_price'];
				$need_data[$key]['danprice'] = $price_arr['danprice'];
				$need_data[$key]['price'] = $val['price'];
				
				$need_data[$key]['options'] = $goods_model->get_goods_options($val['goods_id']);
				
				$need_data[$key]['pin_hour'] = $val['hour'];				
				$need_data[$key]['pin_count'] = $val['bargain_count'];				
				$need_data[$key]['name'] = $val['name'];				
				$need_data[$key]['seller_count'] = $val['seller_count']+ $val['virtual_count'];	

				$fav_goods =  M('user_favgoods')->where( array('goods_id' => $val['goods_id']) )->count();
				
				$need_data[$key]['fav_goods'] = $fav_goods;
				
				
				$need_data[$key]['quantity'] = $val['quantity'];				
				$need_data[$key]['summary'] = $desc_info['summary'];
				$need_data[$key]['url'] = $val['url'];					
			}		
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
		
		
		if( !empty($need_data) )
		{
			echo json_encode( array('code' =>0,'kan_rules_str' => $kan_rules_str, 'data' => $need_data,'success_order_list' => $success_order_list) );		
			die();	
		} else{
			
			echo json_encode( array('code' =>1,'kan_rules_str' => $kan_rules_str,'success_order_list' => $success_order_list) );		
			die();	
		}
		
	}
	/**
	 * 加载推荐拼团数据
	 */
    public function load_best_pintuan()
    {
        //if (!$api_best_pingoods_cache = S('api_best_pingoods_cache')) {
           $now_time = time();
		   	$where = "g.status =1 and g.type != 'normal' and g.quantity >0 and g.is_index_show =1 ";
		
			$where .= " and pg.begin_time < {$now_time} and pg.end_time >{$now_time} ";
		
            $ping_goods = D('Home/Pingoods')->get_pingoods_list('*', $where );
            
			$goods_model = D('Home/goods');
			
            if( !empty($ping_goods) )
            {
                foreach($ping_goods as $key => $val)
                {
                    //goods
                   
                    if( !empty($val['fan_image']) )
                    {
                        $fan_image  = C('SITE_URL').resize($val['fan_image'], 300, 300);
                        $val['fan_image'] = $fan_image;
                    }else {
                        $val['fan_image'] = C('SITE_URL').resize($val['image'], 300, 300);
                    }
                    $val['url'] = U('/goods/'.$val['goods_id']);
					// $price_arr = array('price' =>$pin_goods_info['pin_price'],'danprice' =>$goods_info['danprice'],  'pin_price' =>$pin_goods_info['pin_price'],'pin_count' => $pin_goods_info['pin_count']);
                   
                    $price_arr = $goods_model->get_goods_price($val['goods_id']);
					$val['pin_price'] = $price_arr['pin_price'];
					
                    $ping_goods[$key] = $val;
                }
          //  }
            S('api_best_pingoods_cache', $ping_goods);
            $api_best_pingoods_cache=$ping_goods;
        }
        
      //  var_dump($api_best_pingoods_cache);die();
        echo json_encode( array('code' => 1, 'list' => $api_best_pingoods_cache) );
        die();
    }			
	/**		加载首页推荐分类数据	**/
	
    public function get_index_category()	
	{
		
		$gid = I('get.gid',0);		
		$key='index_goodscategory_cache';		
		if (!$hot_list = S($key)) {		    
			$hot_list = M('goods_category')->where( array('is_hot' => 1) )->order('sort_order desc')->select();		    
			S($key, $hot_list);		
		}				
		$need_data = array();		
		foreach($hot_list as $key => $cate)		
		{			
			$need_data[$key]['id'] = $cate['id'];			
			$need_data[$key]['name'] = $cate['name'];			
			$need_data[$key]['c_sort_order'] = $cate['c_sort_order'];		
		}				
		$result = array('code' =>0,'data' => $need_data);		
		echo json_encode($result);		
		die();	
	}
	
	
	/** 
		获取分类下普通商品数量
	**/
	public function get_category_normal_goods()
	{
		$data_json = file_get_contents('php://input');		
		$data = json_decode($data_json, true);
		
		$pre_page = !empty($data['pre_page']) ? $data['pre_page'] : 10;
		$page = !empty($data['page']) ? $data['page'] : 1;
		$id = !empty($data['gid']) ? $data['gid'] : 9;
		
		
		$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$id} or class_id2 ={$id} or class_id3 = {$id}  ")->field('goods_id')->select();
	
		$ids_arr = array();
		foreach($goods_ids_arr as $val){
			$ids_arr[] = $val['goods_id'];
		}
		$ids_str = implode(',',$ids_arr);
		
		$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'quantity' =>array('gt',0) );
		$condition['type'] = 'normal';
		$condition['lock_type'] = 'normal';
		
		$offset = ($page -1) * $pre_page;
		$list = M('goods')->field('goods_id,name,seller_count,virtual_count,quantity,image,fan_image,danprice,price,type')->where($condition)->order('seller_count desc,goods_id asc')->limit($offset,$pre_page)->select();
	
		if(!empty($list)) {
			foreach($list as $key => $v){
				
				if(empty($v['fan_image'])){
					$list[$key]['image']=C('SITE_URL'). resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}else{
					$list[$key]['image']=C('SITE_URL'). resize($v['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}
				
			}
		}
		foreach($list as $key => $val)
		{
			unset($val['fan_image']);
			
		    $val['seller_count'] += $val['virtual_count'];
			unset($val['virtual_count']);
			
		    $list[$key] = $val;
		}
		
		$need_data = array();
		$need_data['code'] = 1;
		
		if( !empty($list) )
		{
			$need_data['code'] = 0;
			$need_data['data'] = $list;
		}
		echo json_encode($need_data);
		die();
	}
	/**
		获取分类下拼团商品数量
	**/
	public function get_category_pintuan_goods()
	{
		$data_json = file_get_contents('php://input');		
		$data = json_decode($data_json, true);
		
		$pre_page = !empty($data['pre_page']) ? $data['pre_page'] : 10;
		$page = !empty($data['page']) ? $data['page'] : 1;
		$id = !empty($data['gid']) ? $data['gid'] : 9;
		
		
		$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$id} or class_id2 ={$id} or class_id3 = {$id}  ")->field('goods_id')->select();
	
		$ids_arr = array();
		foreach($goods_ids_arr as $val){
			$ids_arr[] = $val['goods_id'];
		}
		$ids_str = implode(',',$ids_arr);
		
		$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'quantity' =>array('gt',0) );
		$condition['type'] = 'pintuan';
		
		
		$offset = ($page -1) * $pre_page;
		$list = M('goods')->field('goods_id,name,seller_count,virtual_count,quantity,image,fan_image,danprice,price,type')->where($condition)->order('seller_count desc,goods_id asc')->limit($offset,$pre_page)->select();
	
		if(!empty($list)) {
			foreach($list as $key => $v){
				
				if(empty($v['fan_image'])){
					$list[$key]['image']=C('SITE_URL'). resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}else{
					$list[$key]['image']=C('SITE_URL'). resize($v['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}
				
			}
		}
		foreach($list as $key => $val)
		{
			unset($val['fan_image']);
			
		    $val['seller_count'] += $val['virtual_count'];
			unset($val['virtual_count']);
			$pin_goods =  M('pin_goods')->where( array('goods_id' =>$val['goods_id']) )->find();
			
			$val['pin_price'] = $pin_goods['pin_price'];
			$val['pintype'] = $pin_goods['type'];
			$val['pin_hour'] = $pin_goods['pin_hour'];
			$val['pin_count'] = $pin_goods['pin_count'];
			
		    $list[$key] = $val;
		}
		
		$need_data = array();
		$need_data['code'] = 1;
		
		if( !empty($list) )
		{
			$need_data['code'] = 0;
			$need_data['data'] = $list;
		}
		echo json_encode($need_data);
		die();
	}
	
    /**
		加载首页随机商品
	**/
	public function get_index_suijigoods()
	{
		$order_sort = '  rand() ';
		
		$goods_model = D('Home/goods');
		//get_goods_price($goods_id)
		
		$list = D('Home/goods')->get_goods_list(' * ', " type='normal' and status =1 and is_index_show =0 ",$order_sort,0,20);
        
		foreach($list as $key => $val)
        {
            if( !empty($val['fan_image']) )
            {
                $fan_image  = C('SITE_URL').resize($val['fan_image'], 300, 300);
                $val['fan_image'] = $fan_image;
            }else {
                $val['fan_image'] = C('SITE_URL').resize($val['image'], 300, 300);
            }
            $price_arr = $goods_model->get_goods_price($val['goods_id']);
			
			$val['danprice'] = $price_arr['price'];
			
            $val['url'] = U('/goods/'.$val['goods_id']);
            
            $list[$key] = $val;
        }
		$this->list = $list;
		
		$html = $this->fetch('Index:suiji_goods_fetch');
		
		echo json_encode( array('code' => 1,'html' =>$html) );
        die();
	}
	
	/**
		加载小程序首页普通商品
	**/
	public function wepro_index_goods()
	{
		$page = I('get.page',1);
		$type = I('get.type','normal');
		//orderby/rand
		$is_index_show = I('get.is_index_show',1);
		$pre_page = I('get.per_page',4);
		
		$orderby = I('get.orderby','');
		//$orderby  == 'rand'
		//$sortby = ' rand() ';
		
		
        $condition = array( );
        $offset = ($page -1) * $pre_page;
		$order_sort = 'index_sort desc ,seller_count desc,goods_id asc';
		
		if( !empty($orderby) )
		{
			$order_sort = ' rand() ';
		}
		
		$where = " store_id = 1 ";
		
		if($type != 'normal')
		{
			$where .= ' and status =1 and (type="normal" or type ="pintuan") ';
		}else {
			$where .= ' and status =1 and type="normal" ';
		}
		
		//gid
		$gid = I('get.gid', 0, 'intval');
		if( !empty($gid) && $gid >0 )
		{
			$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$gid} or class_id2 ={$gid} or class_id3 = {$gid}  ")->field('goods_id')->select();
			
			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}
			$ids_str = implode(',',$ids_arr);
			
			
			//$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'quantity' =>array('gt',0) );
			if( !empty($ids_str) )
			{
				$where .= " and goods_id in ({$ids_str})";
			} else{
				$where .= " and 0 ";
			}
		}
		
		if($is_index_show == 1)
		{
			$where .= " and is_index_show =1 "; 
		}
		
		$list = D('Home/goods')->get_goods_list(' * ', $where ,$order_sort,$offset,$pre_page);
        
		
		
		$goods_model = D('Home/goods');
		
		$need_data = array();
        foreach($list as $key => $val)
        {
			if($val['type'] == 'pintuan')
			{
				$pin_info = M('pin_goods')->where( array('goods_id' => $val['goods_id']) )->find();
				if($pin_info['end_time'] < time())
				{
					continue;
				}
			}
            if( !empty($val['fan_image']) )
            {
                $fan_image  = C('SITE_URL').resize($val['fan_image'], 400, 400);
                $val['image'] = $fan_image;
            }else {
                $val['image'] = C('SITE_URL').resize($val['image'], 400, 400);
            }
            
			$price_arr = $goods_model->get_goods_price($val['goods_id']);
			
			$val['seller_count'] += $val['virtual_count'];
			
			$val['danprice'] = $price_arr['price'];
			$fav_goods =  M('user_favgoods')->where( array('goods_id' => $val['goods_id']) )->count();
				
			$val['fav_goods'] = $fav_goods;
				
				
            //$val['url'] = U('/goods/'.$val['goods_id']);
            $need_data[$key] = $val;
            $list[$key] = $val;
        }
        if(!empty($need_data))
		{
			  echo json_encode( array('code' => 0,'list' =>$need_data) );
			die();
		}else{
			  echo json_encode( array('code' => 1,'list' =>$need_data) );
			die();
		}
	}
	
    /**
     * 加载首页商品数据列表
     */
    public function get_index_bestgoods()
    {
        $goods_rid = I('post.goods_rid',3);
        $page = I('post.page',1);
		
		
        $pre_page = 4;
        $condition = array( );
        $offset = ($page -1) * $pre_page;
        
	
		$order_sort = 'seller_count desc,goods_id asc';
        if($goods_rid == 1)
		{
			$order_sort = 'seller_count desc,goods_id asc';
		}else if($goods_rid == 2){
			$order_sort = 'goods_id asc';
			
		}else if($goods_rid == 3){
			$order_sort = 'index_sort desc ,seller_count desc,goods_id asc';
		}
		$where = ' status =1 and type="normal" ';
		
		
		
        $list = D('Home/goods')->get_goods_list(' * ', $where ,$order_sort,$offset,$pre_page);
        $goods_model = D('Home/goods');
		
        foreach($list as $key => $val)
        {
            if( !empty($val['fan_image']) )
            {
                $fan_image  = C('SITE_URL').resize($val['fan_image'], 300, 300);
                $val['fan_image'] = $fan_image;
            }else {
                $val['fan_image'] = C('SITE_URL').resize($val['image'], 300, 300);
            }
            
			$price_arr = $goods_model->get_goods_price($val['goods_id']);
			
			$val['danprice'] = $price_arr['price'];
			
            $val['url'] = U('/goods/'.$val['goods_id']);
            
            $list[$key] = $val;
        }
        if(empty($list))
		{
			  echo json_encode( array('code' => 0,'list' =>$list) );
			die();
		}else{
			  echo json_encode( array('code' => 1,'list' =>$list) );
			die();
		}
     
    }
    
   
	
}
?>