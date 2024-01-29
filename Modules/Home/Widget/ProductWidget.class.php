<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.cloud/
 * @copyright Copyright (c) 2019-2024 Dejavu Tech.
 * @license   https://github.com/Dejavu-Tech/EP-Admin/blob/main/LICENSE
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Home\Widget;
use Think\Controller;
/**
 * 商品
 */
class ProductWidget extends Controller{


	/**
	 *
	 */
	function index_ajax_goods_list()
	{
		$page = I('get.page',1);
		$pre_page = 10;
		//'seller_count' =>
		$condition = array('status'=>1,'quantity' =>array('gt',0) );
		$offset = ($page -1) * $pre_page;

		$list = M('goods')->where($condition)->order('seller_count desc,goods_id asc')->limit($offset,$pre_page)->select();



		//M('')  $map['id']  = array('gt',100);
		$this->list = $list;
		//$content = $this->fetch('Widget:index_ajax_goods_list');

		$this->display('Widget:index_ajax_goods_list');
	}
	/**
	   商品详情底部状态
	**/
	public function goods_button_html()
	{
	    $id=I('get.id');
	    $pin_id = I('get.pin_id',0);
	    $this->id = $id;
	    $this->pin_id = $pin_id;
	    $this->display('Widget:goods_button_info');
	}
	/**
	 * 首页的商品
	 *
	 */
	function home_goods_list($title,$order_by,$limit){
		$key='home_goods_cache'.$order_by;

		if (!$home_goods_cache = S($key)) {
			$sql='SELECT goods_id,image,price,name FROM '.C('DB_PREFIX').'goods WHERE status=1 ORDER BY '.$order_by.' LIMIT 0,'.$limit;
			$list=M()->query($sql);

			$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
			foreach ($list as $k => $v) {

				$list[$k]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				$list[$k]['goods_id']=$hashids->encode($v['goods_id']);
			}
			S($key, $list);

			$home_goods_cache=$list;
		}
		$this->products=$home_goods_cache;
		$this->title=$title;
		$this->display('Widget:home_goods_list');
	}
	//详情页热门产品
	function hot_goods_list($title,$order_by,$limit){
		$key='hot_goods_cache'.$order_by;

		if (!$hot_goods_cache = S($key)) {
			$sql='SELECT goods_id,image,price,name FROM '.C('DB_PREFIX').'goods WHERE status=1 ORDER BY '.$order_by.' LIMIT 0,'.$limit;
			$list=M()->query($sql);

			$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
			foreach ($list as $k => $v) {

				$list[$k]['image']=resize($v['image'], C('goods_cart_thumb_width'), C('goods_cart_thumb_height'));
				$list[$k]['goods_id']=$hashids->encode($v['goods_id']);
			}
			S($key, $list);

			$hot_goods_cache=$list;
		}
		$this->products=$hot_goods_cache;
		$this->title=$title;
		$this->display('Widget:goods_show_hot_goods_list');
	}
	/**
		拼团详情页 关联拼团活动
	**/
	function relapin_goods_list($goods_id)
	{
		$goods_info = M('goods')->where( array('goods_id' =>$goods_id) )->find();
		$goods_model = D('Home/Goods');

		if(!empty($goods_info))
		{
			//begin_time  end_time
			$now_time = time();
			$where_time = " and pg.begin_time < {$now_time} and pg.end_time > {$now_time} ";

			$goods_cate = M('goods_to_category')->where( array('goods_id' =>$goods_id) )->find();

			$sql='SELECT g.goods_id,g.image,g.seller_count,g.virtual_count,g.fan_image,pg.pin_price,g.danprice,g.name FROM '.C('DB_PREFIX').'goods as g,
			'.C('DB_PREFIX').'pin_goods as pg,
			'.C('DB_PREFIX').'goods_to_category as gtc
			WHERE g.goods_id = pg.goods_id and  gtc.goods_id=g.goods_id and gtc.class_id1='.$goods_cate['class_id1'].'
			 and g.goods_id != '.$goods_id.' '.$where_time.' and  g.status=1 and g.quantity >0 and g.type="pintuan" ORDER BY rand() LIMIT 0,20';

		}else{
			$now_time = time();
			$where_time = " and pg.begin_time < {$now_time} and pg.end_time > {$now_time} ";

			$sql='SELECT g.goods_id,g.image,g.seller_count,g.virtual_count,g.fan_image,pg.pin_price,g.danprice,g.name FROM '.C('DB_PREFIX').'goods as g,
			'.C('DB_PREFIX').'pin_goods as pg
			 WHERE g.goods_id = pg.goods_id  '.$where_time.'
			 and g.status=1 and g.quantity >0  ORDER BY rand() LIMIT 0,20';
		}


		$list=M()->query($sql);
		foreach ($list as $k => $v) {

	       // $list[$k]['image']=resize($v['image'], C('goods_related_thumb_width'), C('goods_related_thumb_height'));
	        //pin_price
			$price_arr = $goods_model->get_goods_price($v['goods_id']);

			$list[$k]['pin_price'] = $price_arr['pin_price'];


			$list[$k]['seller_count'] = $list[$k]['seller_count'] + $list[$k]['virtual_count'];

			if(!empty($v['fan_image'])){
				$list[$k]['image']=resize($v['fan_image'], 480,480);
				//$list[$k]['image']='/Uploads/image/'.$v['fan_image'];
			}else {
				$list[$k]['image']=resize($v['image'], 480,480);
				//$list[$k]['image']='/Uploads/image/'.$v['image'];
			}
	    }

		$this->related_goods=$list;


		$this->display('Widget:relapin_goods_list');

	}
	//详情页推荐的关联产品
	function related_goods_list($goods_id ){

		//var_dump($goods_id);die(); type != "integral" and type != "bargain"
		$goods_model = D('Home/Goods');
		$goods_cate = M('goods_to_category')->where( array('goods_id' =>$goods_id) )->find();

		$sql='SELECT g.goods_id,g.image,g.fan_image,g.danprice,g.name FROM '.C('DB_PREFIX').'goods as g,'.C('DB_PREFIX').'goods_to_category as gtc WHERE gtc.goods_id=g.goods_id and gtc.class_id1='.$goods_cate['class_id1'].' and g.type != "integral" and g.type != "bargain" and  g.status=1 ORDER BY rand() LIMIT 0,20';
		$list=M()->query($sql);


		foreach($list as $k =>$v)
		{


			$price_arr = $goods_model->get_goods_price($v['goods_id']);

			$list[$k]['danprice'] = $price_arr['danprice'];

			//$val['danprice'] = $price_arr['price'];

		}


		$member_id = is_login();
		$goods_model = D('Home/Goods');



		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		foreach ($list as $k => $v) {

			//$list[$k]['image']=resize($v['image'], 468, 658);
			$list[$k]['image']=resize($v['image'], 400, 400);
		}

		$this->related_goods=$list;

		$this->display('Widget:related_goods_list');
	}

	function related_usecenter_goods_list()
	{
		$sql='SELECT goods_id,image,fan_image,danprice,name FROM '.C('DB_PREFIX').'goods WHERE status=1 and type != "integral" and type != "bargain" and quantity>0 ORDER BY rand() LIMIT 0,12';
	    $list=M()->query($sql);

		$goods_model = D('Home/Goods');



	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    foreach ($list as $k => $v) {

	        //$list[$k]['image']=resize($v['image'], C('goods_related_thumb_width'), C('goods_related_thumb_height'));

			$price_arr = $goods_model->get_goods_price($v['goods_id']);

			$list[$k]['danprice'] = $price_arr['danprice'];

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

	    $this->display('Widget:related_usercenter_goods_list');
	}

	//拼团详情页推荐的关联产品
	function related_group_goods_list()
	{
	    $sql='SELECT goods_id,image,fan_image,price,name,pinprice FROM '.C('DB_PREFIX').'goods WHERE status=1 ORDER BY rand() LIMIT 0,9';
	    $list=M()->query($sql);

	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	    foreach ($list as $k => $v) {

	        $list[$k]['image']=resize($v['image'], C('goods_related_thumb_width'), C('goods_related_thumb_height'));

			if(!empty($v['fan_image'])){
				$list[$k]['image']=resize($v['fan_image'], C('goods_related_thumb_width'), C('goods_related_thumb_height'));
			}else {
				$list[$k]['image']=resize($v['image'], C('goods_related_thumb_width'), C('goods_related_thumb_height'));
			}
			//$list[$k]['goods_id']=$hashids->encode($v['goods_id']);
	    }

	    $this->related_goods=$list;

	    $this->display('Widget:related_group_goods_list');
	}

}
