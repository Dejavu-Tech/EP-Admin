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
namespace Seller\Controller;

class SolitaireController extends CommonController{

	public function index()
	{
		$_GPC = I('request.');



        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and solitaire_name like "%'.$_GPC['keyword'].'%"';
        }

		$now_time = time();


		if( isset($_GPC['type']) && $_GPC['type'] > 0 )
		{
			switch( $_GPC['type'] )
			{
				case 1:
					//进行中 eaterplanet_ecommerce_solitaire state appstate  begin_time end_time
					$condition .= " and state =1 and appstate=1 and begin_time <= {$now_time} and  end_time > {$now_time} ";
				break;
				case 2:
					//未开始
					$condition .= " and state =1 and appstate=1 and begin_time > {$now_time} ";
				break;
				case 3:
					//已结束
					$condition .= " and state =1 and appstate=1  and  end_time < {$now_time} ";
				break;
				case 4:
					//未审核
					$condition .= " and appstate=0  ";
				break;
				case 5:
					//已拒绝
					$condition .= " and appstate=2  ";
				break;
			}
		}


        $list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_solitaire
			WHERE 1  " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize );

		//id,接龙名称， 社区接龙团长，参与接龙人数，浏览人数，接龙时间，接龙状态。

		foreach( $list as $key => $val )
		{
			//head_id

			$head_info = M('eaterplanet_community_head')->field('community_name,head_name')->where( array('id' => $val['head_id'] ) )->find();

			$val['head_info'] = $head_info;

			$order_count = M('eaterplanet_ecommerce_solitaire_order')->where( array('soli_id' => $val['id'] ) )->count();

			$val['order_count'] = $order_count;

			$invite_count = M('eaterplanet_ecommerce_solitaire_invite')->where( array('soli_id' => $val['id'] ) )->count();

			$val['invite_count'] = $invite_count;

			$goods_count = M('eaterplanet_ecommerce_solitaire_goods')->where( array('soli_id' => $val['id'] ) )->count();

			$val['goods_count'] = $goods_count;

			$list[$key] = $val;


		}


		$total = M('eaterplanet_ecommerce_solitaire')->where( '1 '.$condition )->count();

        $pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;
		$this->_GPC = $_GPC;

		//全部接龙

		$all_count = M('eaterplanet_ecommerce_solitaire')->count();

		if( empty($all_count) )
		{
			$all_count = 0;
		}

		$this->all_count = $all_count;

		//进行中的
		$count_status_1 = M('eaterplanet_ecommerce_solitaire')->where( "appstate=1 and state=1 and begin_time <={$now_time} and end_time >{$now_time} " )->count();

		if( empty($count_status_1) )
		{
			$count_status_1 = 0;
		}
		$this->count_status_1 = $count_status_1;

		//未开始（{$count_status_2}
		$count_status_2 = M('eaterplanet_ecommerce_solitaire')->where( "appstate=1 and state=1 and begin_time >{$now_time} " )->count();

		if( empty($count_status_2) )
		{
			$count_status_2 = 0;
		}
		$this->count_status_2 = $count_status_2;

		//已结束（{$count_status_3}）
		$count_status_3 = M('eaterplanet_ecommerce_solitaire')->where("appstate=1 and state=1 and end_time <={$now_time} ")->count();

		if( empty($count_status_3) )
		{
			$count_status_3 = 0;
		}
		$this->count_status_3 = $count_status_3;

		//未审核（{$count_status_4}
		$count_status_4 = M('eaterplanet_ecommerce_solitaire')->where("appstate=0")->count();

		if( empty($count_status_4) )
		{
			$count_status_4 = 0;
		}

		$this->count_status_4 = $count_status_4;

		//已拒绝（{$count_status_5}

		$count_status_5 = M('eaterplanet_ecommerce_solitaire')->where("appstate=2")->count();

		if( empty($count_status_5) )
		{
			$count_status_5 = 0;
		}

		$this->count_status_5 = $count_status_5;


		$this->display();
	}


	//
	/**
	 * 删除群接龙
	 */
    public function delete()
    {
        $_GPC = I('request.');
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

		$items = M('eaterplanet_ecommerce_solitaire')->field('id')->where('id in( ' . $id . ' )')->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
			M('eaterplanet_ecommerce_solitaire')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }


	public function detail()
	{
		$_GPC = I('request.');
		//id=11

		$id = $_GPC['id'];

		$solitaire_info = M('eaterplanet_ecommerce_solitaire')->where( array('id' => $id ) )->find();

		$now_time = time();
		$state_str = "";

		if( $solitaire_info['appstate'] == 0 )
		{
			$state_str = '待审核';
		}else if( $solitaire_info['appstate'] == 2 )
		{
			$state_str = '已拒绝';
		} else if( $solitaire_info['appstate'] == 1 )
		{
			//
			if( $solitaire_info['state'] == 1 )
			{
				if( $solitaire_info['begin_time'] >  $now_time )
				{
					$state_str = '未开始';
				}else if( $solitaire_info['begin_time'] <= $now_time &&  $solitaire_info['end_time'] > $now_time )
				{
					$state_str = '进行中';
				}else if( $solitaire_info['end_time'] < $now_time ){
					$state_str = '已结束';
				}


			}else if( $solitaire_info['state'] == 0 )
			{
				$state_str = '已禁用';
			}
		}

		$this->solitaire_info = $solitaire_info;
		$this->state_str = $state_str;
		$this->id = $id;
		$this->_GPC = $_GPC;

		$head_info = M('eaterplanet_community_head')->where( array('id' => $solitaire_info['head_id'] ) )->find();

		$this->head_info = $head_info;

		$soli_goods = M('eaterplanet_ecommerce_solitaire_goods')->field('goods_id')->where( array('soli_id' => $id ) )->select();

		$goods_arr = array();
		$goods_ids = array();

		if( !empty($soli_goods) )
		{
			foreach($soli_goods as $val)
			{
				$goods_ids[] = $val['goods_id'];
			}

			$goods_ids_str = implode(',', $goods_ids);

			$sql = "select g.id,g.goodsname,g.codes,g.price,productprice,total from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g , ".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc
					where   g.id=gc.goods_id and g.id in ({$goods_ids_str}) ";

			$goods_arr = M()->query($sql);

			foreach( $goods_arr as $k => $v )
			{
				$image_s = D('Home/Pingoods')->get_goods_images($v['id']);

				$v['image'] = $image_s['image'];

				$goods_arr[$k] = $v;
			}

			//goods_images $image  = load_model_class('pingoods')->get_goods_images($goods_id);
		}
		// eaterplanet_ecommerce_solitaire_order
		//团长昵称
		$this->goods_arr = $goods_arr;

		$order_count = M('eaterplanet_ecommerce_solitaire_order')->where( array('soli_id' => $id ) )->count();

		$this->order_count = $order_count;
		//soli_id  order_id
		$order_sql = "select o.* from ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o, ".C('DB_PREFIX')."eaterplanet_ecommerce_solitaire_order as so
						where  o.order_id = so.order_id and so.soli_id ={$id}  order by o.order_id asc ";

		$order_list = M()->query($order_sql);


		foreach( $order_list as $key => $val )
		{

			$order_goods = M('eaterplanet_ecommerce_order_goods')->field('quantity')->where( array('order_id' => $val['order_id'] ) )->select();

			$val['order_goods'] = $order_goods;
			/****/
			$buy_quantity = 0;

			$buy_quantity = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $val['order_id'] ) )->sum('quantity');

			$mb_info = M('eaterplanet_ecommerce_member')->field('username,avatar')->where( array('member_id' => $val['member_id'] ) )->find();

			$val['mb_info'] =  $mb_info;
			$val['buy_quantity'] =  $buy_quantity;

			$order_list[$key] = $val;
		}

		$this->order_list = $order_list;

		// eaterplanet_ecommerce_order_status
		$order_status_all = M('eaterplanet_ecommerce_order_status')->select();

		$order_status_arr = array();

		foreach( $order_status_all as $val )
		{
			$order_status_arr[$val['order_status_id']] = $val['name'];
		}

		$this->order_status_arr = $order_status_arr;

		$this->display();

	}

	public function add()
	{
		$_GPC = I('request.');


        $id = intval($_GPC['id']);

		$is_mult = 1;

        if (!empty($id)) {

			$item = M('eaterplanet_ecommerce_solitaire')->where( array('id' => $id ) )->find();

			$limit_goods = array();
			$item['piclist'] = array();

			$piclist = array();

			$images_list = unserialize($item['images_list']);

			if( !empty($images_list) )
			{
				foreach( $images_list as $key => $val )
				{
					$med_image = tomedia( $val );

					$piclist[] = array('image' => $med_image, 'thumb' => $med_image ); //$val['image'];
				}
				$item['piclist'] = $piclist;
			}



			$item['content'] = htmlspecialchars_decode( $item['content'] );

			$headinfo = M('eaterplanet_community_head')->where( array('id' => $item['head_id'] ) )->find();

			$this->headinfo = $headinfo;

			$limit_goods = array();
			//ims_   soli_id  goods_id

			$sql = "select g.id as gid, g.goodsname  from ".C('DB_PREFIX')."eaterplanet_ecommerce_solitaire_goods as gs  left join ".C('DB_PREFIX')."eaterplanet_ecommerce_goods  as g on gs.goods_id = g.id where gs.soli_id={$id}";

            $goods_list = M()->query($sql);

			$limit_goods = array();

			if( !empty($goods_list) )
			{
				foreach( $goods_list as $key => $val )
				{

					$thumb =  D('Home/Pingoods')->get_goods_images($val['gid']);


					if( empty($thumb['thumb']) )
					{
						$val['image'] =  tomedia($thumb['image']);
					}else{
						$val['image'] =  $thumb['thumb'];
					}

					$goods_list[$key] = $val;
				}

				$limit_goods = $goods_list;
			}

			$this->limit_goods = $limit_goods;

			//ims_eaterplanet_ecommerce_goods_images

			$is_mult = 0;
        }else{
			$item = array();
			$item['begin_time'] = time();
			$item['end_time'] = $item['begin_time'] +  86400 *2;

		}

		$this->is_mult = $is_mult;

		$this->item = $item;

        if (IS_POST) {

			$data = $_GPC['data'];

			$goods_list = $_GPC['goods_list'];

			$images_list = $_GPC['images_list'];
			$head_dan_id = $_GPC['head_dan_id'];

			$time = $_GPC['time'];//start end

			if( empty($head_dan_id) )
			{
				show_json(0, array('message' => '请选择团长') );
			}

			if( empty($goods_list) )
			{
				show_json(0, array('message' => '请选择商品') );
			}

						//bmp,jpg,png,tif,gif,pcx,tga,exif,fpx,svg,psd,cdr,pcd,dxf,ufo,eps,ai,raw,WMF,webp

			$img = array('.bmp','.png','.tif','.gif','.pcx','.tga','.exif','.fpx','.svg','.psd','.cdr','.pcd','.dxf','.ufo','.eps','.ai','.raw','.WMF','.webp');
			$count = 0;
			foreach($img as $var){
				$content_img = strstr($data['content'], $var);
				if($content_img){
					$count++ ;
				}
			}

			if( !empty($count) )
			{

					show_json(0, '图片类型必须为JPG格式');
					die();
			}


			$need_data = array();
			$need_data['data'] = $data;
			$need_data['goods_list'] = $goods_list;

			$need_data['images_list'] = $images_list;
			$need_data['head_dan_id'] = $head_dan_id;
			$need_data['head_id_list'] = $_GPC['head_id'];

			$need_data['time'] = $time;
//			print_r($goods_list);die;

            D('Seller/Solitaire')->updatedo($need_data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
//        die;

		$this->display();
	}


	public function changestate()
	{
		$_GPC = I('request.');

		$value = $_GPC['value'];
		$id = $_GPC['id'];

		M('eaterplanet_ecommerce_solitaire')->where( array('id' => $id ) )->save( array('state' => $value ) );

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function change()
	{
		$_GPC = I('request.');

		$value = $_GPC['value'];
		$id = $_GPC['id'];

		M('eaterplanet_ecommerce_solitaire')->where( array('id' => $id ) )->save( array('appstate' => $value ) );

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function config()
	{
		$_GPC = I('request.');
		if ( IS_POST ) {

			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));

			$data['solitaire_target'] = isset($data['solitaire_target']) ? $data['solitaire_target'] : 0;

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;

		$this->display();

	}

}
?>
