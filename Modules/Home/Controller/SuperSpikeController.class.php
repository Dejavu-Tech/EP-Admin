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

class SuperSpikeController extends CommonController {

	//进行中
	public function index(){


	    $per_page = 10;
	    $page = I('post.page',1);

	    $offset = ($page - 1) * $per_page;

	    $super_spike = M('super_spike')->where('begin_time<'.time().' and end_time > '.time())->order('begin_time desc')->find();

	   if($super_spike){
	       $sql = 'select sg.state,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.image,g.store_id,g.seller_count from  '.C('DB_PREFIX')."super_spike_goods as sg , ".C('DB_PREFIX')."goods as g
	        where  super_spike_id = ".$super_spike['id']." and  sg.state =1 and sg.goods_id = g.goods_id and g.status =1 and g.quantity >0  order by sg.id asc limit {$offset},{$per_page}";

	       $list = M()->query($sql);

	       $this->list = $list;
	   } else {
	       $this->list = array();
	   }


		if($page > 1) {
		    $result = array('code' => 0);
		    if(!empty($list)) {
		        $result['code'] = 1;
		        $result['html'] = $this->fetch('SuperSpike:superspike_ajax_fetch');
		    }
		    echo json_encode($result);
		    die();
		}
		$this->display('SuperSpike:index');
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
