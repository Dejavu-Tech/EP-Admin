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

class SpikeController extends CommonController {

	//进行中
	public function index(){

	    $per_page = 10;
	    $page = I('post.page',1);
	    $last_btime = I('post.last_btime',0);

	    $offset = ($page - 1) * $per_page;

	    $begin_hour_time = strtotime(date('Y-m-d H'.':00:00'));
	    $begin_hour_time = time();

	    $sql = 'select s.begin_time as btime,sg.state,sg.begin_time,sg.end_time,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.image from  '.C('DB_PREFIX')."spike_goods as sg ,
	        ".C('DB_PREFIX')."goods as g ,".C('DB_PREFIX')."spike as s
	        where sg.state =1 and s.id=sg.spike_id and sg.goods_id = g.goods_id and g.status =1 and g.quantity > 0  and sg.begin_time <= ".$begin_hour_time." and sg.end_time > ".$begin_hour_time." order by sg.end_time asc ,sg.begin_time asc limit {$offset},{$per_page}";

		$list = M()->query($sql);

		$fc_last_end_time = 0;
		foreach ($list as $k => $v) {
		    $v['image']=resize($v['image'], C('spike_thumb_width'), C('spike_thumb_height'));
		    $fc_last_end_time = $v['begin_time'];
		    $list[$k] = $v;
		}

		$this->last_btime = $last_btime;
		$this->cur_btime = $last_btime;
		$this->list = $list;

		if($page > 1) {
		    $result = array('code' => 0);
		    if(!empty($list)) {
		        $result['code'] = 1;
		        $result['fc_last_end_time'] = $fc_last_end_time;
		        $result['html'] = $this->fetch('Widget:spike_ajax_on_fetch');
		    }
		    echo json_encode($result);
		    die();
		}

		$this->display('index');
	}

	//未开始
	public function wait()
	{
	    $per_page = 10;
	    $page = I('post.page',1);

	    $offset = ($page - 1) * $per_page;

	    $sql = 'select s.begin_time as btime,sg.state,sg.begin_time,sg.end_time,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.image from  '.C('DB_PREFIX')."spike_goods as sg ,
	        ".C('DB_PREFIX')."goods as g ,".C('DB_PREFIX')."spike as s
	        where sg.state =1 and s.id=sg.spike_id and sg.goods_id = g.goods_id and g.status =1 and g.quantity >0  and sg.begin_time > ".time()."  order by s.begin_time asc ,sg.begin_time asc limit {$offset},{$per_page}";


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


	    $sql = 'select s.begin_time as btime,sg.state,sg.begin_time,sg.end_time,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.image from  '.C('DB_PREFIX')."spike_goods as sg ,
	        ".C('DB_PREFIX')."goods as g ,".C('DB_PREFIX')."spike as s
	        where sg.state =1 and s.id=sg.spike_id and sg.goods_id = g.goods_id and g.status =1  and  ( g.quantity =0 or sg.end_time < ".time().")  order by s.begin_time asc ,sg.begin_time asc limit {$offset},{$per_page}";


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
