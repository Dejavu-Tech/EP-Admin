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
 *
 */
namespace Seller\Controller;

class MarketingController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
	}


	public function index()
	{
		$this->user();
	}


	public function points()
	{
		$_GPC = I('request.');


		if (IS_POST) {

			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));

			D('Seller/Config')->update($data);

			show_json(1);
		}
		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;
		$this->display();
	}



	public function coupon()
	{
		$_GPC = I('request.');

		$this->gpc = $_GPC;

		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = ' 1 ';
		$params = array(':uniacid' => $_W['uniacid']);

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' AND voucher_title LIKE "%'.$_GPC['keyword'].'%"';

		}

		if (!empty($_GPC['catid'])) {
			$_GPC['catid'] = trim($_GPC['catid']);
			$condition .= ' AND catid = '. $_GPC['catid'];
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);

			$this->starttime = $starttime;
			$this->endtime = $endtime;

			if (!empty($starttime)) {
				$condition .= ' AND add_time >= '.$starttime;
			}

			if (!empty($endtime)) {
				$condition .= ' AND add_time <= '.$endtime;
			}
		}

		if ($_GPC['gettype'] != '') {
			$condition .= ' AND is_index_show = '.intval($_GPC['gettype']);
		}

		if ($_GPC['type'] != '') {
		//	$condition .= ' AND coupontype = '.intval($_GPC['type']);
		}



		$sql = 'SELECT * FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_coupon ' . ' where  1 and ' . $condition . ' ORDER BY displayorder DESC,id DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
		$list = M()->query($sql);

		foreach ($list as &$row) {


			$send_count = M('eaterplanet_ecommerce_coupon_list')->where( array('voucher_id' => $row['id'] ) )->count();


			$usetotal = M('eaterplanet_ecommerce_coupon_list')->where( array('voucher_id' => $row['id'], 'consume' => 'Y') )->count();

			$row['usetotal'] = $usetotal;
			$row['send_count'] = $send_count;

			//usetotal
		}

		unset($row);
		$total_arr = M()->query('SELECT COUNT(*) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_coupon where 1 and ' . $condition);
		$total = $total_arr[0]['count'];

		$pager = pagination2($total, $pindex, $psize);
		$category_arr = M()->query('select * from ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_coupon_category   order by id desc');

		$category = array();
		foreach($category_arr as $vv)
		{
			$category[$vv['id']] = $vv['name'];
		}

		$this->list = $list;
		$this->pager = $pager;
		$this->category = $category;


		$this->display();
	}


	public function couponsend()
	{
		$_GPC = I('request.');


		$where = "";

		$where = " and (total_count=-1 or  total_count>send_count)   and (end_time>".time()." or timelimit =0 ) ";

		$quan_list = M()->query("select * from ".C('DB_PREFIX').
						"eaterplanet_ecommerce_coupon where 1 {$where} order by displayorder desc ,id asc limit 1000 ");

		$membercount = M('eaterplanet_ecommerce_member')->where( array('groupid' => 0 ) )->count();


		$list = array(
			array('id' => 'default', 'groupname' => '默认分组', 'membercount' => $membercount )
		);

		$condition = '  ';

		$alllist = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member_group WHERE 1 ' . $condition . ' ORDER BY id asc' );

		foreach ($alllist as &$row ) {
			$row['membercount'] = M('eaterplanet_ecommerce_member')->where("find_in_set(".$row['id'].",groupid)")->count();
		}

		$list = array_merge($list, $alllist);


		$this->quan_list = $quan_list;
		$this->list = $list;
		$this->membercount = $membercount;


		include $this->display();
	}


	public function send()
	{
		$_GPC = I('request.');


		$where = "";

		$where = " and (total_count=-1 or  total_count>send_count)   and (end_time>".time()." or timelimit =0 ) ";

		$quan_list = M()->query("select * from ".C('DB_PREFIX').
						"eaterplanet_ecommerce_coupon where 1 {$where} order by displayorder desc ,id asc limit 1000 ");

		$membercount = M('eaterplanet_ecommerce_member')->where( array('groupid' => 0 ) )->count();


		$list = array(
			array('id' => 'default', 'groupname' => '默认分组', 'membercount' => $membercount )
		);

		$condition = '  ';

		$alllist = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member_group WHERE 1 ' . $condition . ' ORDER BY id asc' );

		foreach ($alllist as &$row ) {
			$row['membercount'] = M('eaterplanet_ecommerce_member')->where("find_in_set(".$row['id'].",groupid)")->count();
		}

		$list = array_merge($list, $alllist);


		$this->quan_list = $quan_list;
		$this->list = $list;
		$this->membercount = $membercount;

		include $this->display('Marketing/couponsend');
	}

	public function couponsend_do()
	{
		$_GPC = I('request.');


		$voucher_id = $_GPC['voucher_id'];
		$send_count = $_GPC['send_count'];
		$send_person = $_GPC['send_person'];

		$member_group_id = $_GPC['member_group_id'];

		$limit_user_list = $_GPC['limit_user_list'];


		$cache_key = md5(time().$voucher_id.$send_person);

		$ids_arr = array();

		if( $send_person == 1)
		{
			//送给部分人
			$ids_arr = explode(',', $limit_user_list);
		}else if( $send_person == 2 )
		{
			//送给分组
			$member_group_id = $member_group_id == 'default' ? 0 : $member_group_id;

			$mb_list = M('eaterplanet_ecommerce_member')->field('member_id')->where( array('groupid' => $member_group_id ) )->select();

			foreach( $mb_list as $val )
			{
				$ids_arr[] = $val['member_id'];
			}

		}else if( $send_person == 3 ){
			//送给所有人
			$mb_list = M()->query("select member_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_member where 1  " );

			foreach( $mb_list as $val )
			{
				$ids_arr[] = $val['member_id'];
			}

		}

		S('_send_quan_'.$cache_key, $ids_arr);

		$this->cache_key = $cache_key;
		$this->voucher_id = $voucher_id;
		$this->send_count = $send_count;
		$this->send_person = $send_person;
		$this->member_group_id = $member_group_id;
		$this->limit_user_list = $limit_user_list;


		include $this->display();
	}

	public function do_coupon_quene()
	{
		$_GPC = I('request.');

		$voucher_id = $_GPC['voucher_id'];
		$send_count = $_GPC['send_count'];

		$cache_key = $_GPC['cache_key'];

		$quene_order_list = S('_send_quan_'.$cache_key);

		$member_id = array_shift($quene_order_list);

		S('_send_quan_'.$cache_key, $quene_order_list);


		$error_msg = "";

		//send quan
		for( $i =0; $i< $send_count; $i++ )
		{
			$res =  D('Home/Voucher')->send_user_voucher_byId($voucher_id,$member_id,false, true );
			//int(4)
			if( $res == 1 )
			{
				$error_msg .= '(券id：'.$voucher_id.' 已送光)';
			}
		}


		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}


		echo json_encode( array('code' => 0, 'msg' => '客户id：'.$member_id." 处理成功{$error_msg}，还剩余".count($quene_order_list)."个客户未处理") );
		die();
	}


	 public function change()
    {

		$_GPC = I('request.');
        $id = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = trim($_GPC['type']);
        $value = trim($_GPC['value']);

        if (!(in_array($type, array('is_index_show', 'displayorder','is_index_alert')))) {
            show_json(0, array('message' => '参数错误'));
        }

		$coupon_collection_method = array();
		/*if($type == 'is_index_show' && $value == 0 ){
			$coupon_collection_method = array(
						"is_index_show"  => "0" ,
						"is_index_alert" => "1"
			);
		}else{
			$coupon_collection_method = array(
						"is_index_show"  => "1" ,
						"is_index_alert" => "0"
			);
		}*/
		if($type == 'is_index_show'){
			$coupon_collection_method = array("is_index_show"  => $value);
		}else if($type == 'is_index_alert'){
			$coupon_collection_method = array("is_index_alert"  => $value);
		}

        $items = M()->query('SELECT id FROM ' .  C('DB_PREFIX') . 'eaterplanet_ecommerce_coupon WHERE id in( ' . $id . ' ) ' );

        foreach ($items as $item) {
			M('eaterplanet_ecommerce_coupon')->where( array('id' => $item['id']) )->save( $coupon_collection_method );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }


	public function seckill()
	{
		$_GPC = I('request.');


		if ( IS_POST ) {

			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));

			$scekill_show_time_arr = $_GPC['scekill_show_time'];

			if( !empty($scekill_show_time_arr) )
			{
				$data['scekill_show_time'] = serialize($scekill_show_time_arr);
			}

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$data = D('Seller/Config')->get_all_config();

		if( isset($data['scekill_show_time']) && !empty($data['scekill_show_time']) )
		{
			$data['scekill_show_time_arr'] = unserialize($data['scekill_show_time']);
		}

		$this->data = $data;

		$this->display();
	}


	public function photoadv()
	{
		$_GPC = I('request.');

		if ( IS_POST ) {

			$data = $_GPC['data'];
			if($data['type']==3 || $data['type']==4){
                $data['photoadv_link'] = $data['photoadv_cid'];
            }

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$data = D('Seller/Config')->get_all_config();
		$this->data = $data;

		$category = D('Seller/GoodsCategory')->getFullCategory(false, true);
		$this->category = $category;

		$this->display();
	}





	public function explain()
	{
		$_GPC = I('request.');

		if (IS_POST) {

			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;

		$this->display();
	}

	//logcoupon&couponid=3
	public function logcoupon()
	{
		$_GPC = I('request.');

		$this->gpc = $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = ' 1 ';

		$couponid = intval($_GPC['couponid']);

		if (!empty($couponid)) {

			$coupon = M('eaterplanet_ecommerce_coupon')->where( array('id' => $couponid ) )->find();

			$this->coupon = $coupon;
			$condition .= ' AND c.voucher_id=' . intval($couponid);
		}

		$searchfield = strtolower(trim($_GPC['searchfield']));
		$keyword = trim($_GPC['keyword']);
		if (!empty($searchfield) && !empty($keyword)) {
			if ($searchfield == 'member') {
				$condition .= ' and ( m.realname like "%'.$keyword.'%" or m.nickname like "%'.$keyword.'%" or m.mobile like "%'.$keyword.'%" )';
			}
			else {
				if ($searchfield == 'coupon') {
					$condition .= ' and c.voucher_title like "%'.$keyword.'%" ';
				}
			}
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (empty($starttime1) || empty($endtime1)) {
			$starttime1 = strtotime('-1 month');
			$endtime1 = time();
		}

		if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);

			$this->starttime = $starttime;
			$this->endtime = $endtime;

			$condition .= ' AND c.add_time >= '.$starttime.' AND c.add_time <= '.$endtime;

		}

		if (!empty($_GPC['time1']['start']) && !empty($_GPC['time1']['end'])) {
			$starttime1 = strtotime($_GPC['time1']['start']);
			$endtime1 = strtotime($_GPC['time1']['end']);

			$this->starttime1 = $starttime1;
			$this->endtime1 = $endtime1;

			$condition .= ' AND c.usetime >= '.$starttime1.' AND c.add_time <= '.$endtime1;
		}

		if ($_GPC['type'] != '') {
			$condition .= ' AND c.coupontype = '.intval($_GPC['type']);
		}

		if ($_GPC['used'] != '') {
			$condition .= ' AND c.consume = "' . trim($_GPC['used']).'" ';
		}

		if ($_GPC['gettype'] != '') {
			$condition .= ' AND c.gettype ='.intval($_GPC['gettype']);
		}

		$sql = 'SELECT c.*,m.username,m.avatar,m.openid,m.telephone FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_coupon_list  c ' .
				' left join ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member m on m.member_id = c.user_id  ' . ' where  1 and ' . $condition . ' ORDER BY c.add_time DESC';

		if (empty($_GPC['export'])) {
			$sql .= ' LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
		}

		$list = M()->query($sql);

		foreach ($list as &$row) {
			$couponstr = '消费';


			$row['couponstr'] = $couponstr;

			if ($row['gettype'] == 0) {
				$row['gettypestr'] = '后台发放';
			}
			else if ($row['gettype'] == 1) {
				$row['gettypestr'] = '首页领取';
			}
			else if ($row['gettype'] == 2) {
				$row['gettypestr'] = '积分商城';
			}
			else if ($row['gettype'] == 14) {
				$row['gettypestr'] = '新人领券';
			}
			else {
				if ($row['gettype'] == 15) {
					$row['gettypestr'] = '发券分享';
				}
			}
		}

		unset($row);

		if ($_GPC['export'] == 1) {


			foreach ($list as &$row) {
				$row['gettime'] = date('Y-m-d H:i', $row['add_time']);

				if (!empty($row['usetime'])) {
					$row['usetime'] = date('Y-m-d H:i', $row['usetime']);
				}
				else {
					$row['usetime'] = '---';
				}
			}

			$columns = array(
				array('title' => 'ID', 'field' => 'id', 'width' => 12),
				array('title' => '优惠券', 'field' => 'voucher_title', 'width' => 24),
				array('title' => '类型', 'field' => 'couponstr', 'width' => 12),
				array('title' => '客户信息', 'field' => 'username', 'width' => 12),
				array('title' => '姓名', 'field' => 'realname', 'width' => 12),
				array('title' => '手机号', 'field' => 'telephone', 'width' => 12),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '获取方式', 'field' => 'gettypestr', 'width' => 12),
				array('title' => '获取时间', 'field' => 'gettime', 'width' => 12),
				array('title' => '使用时间', 'field' => 'usetime', 'width' => 12),
				array('title' => '使用单号', 'field' => 'ordersn', 'width' => 12)
				);

			D('Seller/Excel')->export($list, array('title' => '优惠券数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
			//m('excel')->export($list, array('title' => '优惠券数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
			//plog('sale.coupon.log.export', '导出优惠券发放记录');
		}

		$total_arr = M()->query('SELECT COUNT(*) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_coupon_list c ' . ' left join ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_member m on m.member_id = c.user_id  ' . 'where 1 and ' . $condition);
		$total = $total_arr[0]['count'];

		$pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;

		$this->display();
	}

	public function recharge_diary()
	{
		$_GPC = I('request.');

		$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));

		$keyword = I('get.keyword','','addslashes');
		$keyword2 = stripslashes($keyword);
		$this->keyword = $keyword2;

		$state = $_GPC['state'];

		$this->starttime = $starttime;
		$this->endtime = $endtime;

		$this->state = $state;


		$condition = '   ';

		if( !empty($state) && $state > 0 )
		{
			$condition .= " and cf.state ={$state} ";
		}else{
			$condition .= " and cf.state <> 0 ";
		}

		if( isset($_GPC['time']) )
		{
			if($_GPC['time']['start'])
			{
				$condition .= " and cf.add_time >= {$starttime} ";
			}
			if($_GPC['time']['end'])
			{
				$condition .= " and cf.add_time <= {$endtime} ";
			}
		}

		if( !empty($keyword) )
		{
			$condition .= " and m.username like '%{$keyword}%' ";
		}

		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$sql = "select cf.* ,m.username,m.avatar  from ".C('DB_PREFIX')."eaterplanet_ecommerce_member_charge_flow as cf , ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m
				where cf.member_id = m.member_id {$condition} order by cf.id desc limit ". (($pindex - 1) * $psize) . ',' . $psize;

		$sql_count = "select count(1) as count  from ".C('DB_PREFIX')."eaterplanet_ecommerce_member_charge_flow as cf , ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m
				where cf.member_id = m.member_id {$condition} ";


		$list = M()->query($sql);

		$total_arr = M()->query($sql_count );

		$total = $total_arr[0]['count'];


		foreach( $list as $key => $val )
		{
			$val['add_time'] = date('Y-m-d H:i:s',$val['add_time'] );
            $val['trans_id'] = '-';
			if($val['state'] == 3 || $val['state'] == 4)
			{
				$od_info =	M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $val['trans_id'] ) )->find();

				if( !empty($od_info) )
				{
					$val['trans_id'] = $od_info['order_num_alias'];
				}
			}

			$list[$key] = $val;
		}


		$pager = pagination2($total, $pindex, $psize);


		$all_count = M('eaterplanet_ecommerce_member_charge_flow')->where( "state != 0" )->count();

		$count_status_1 = M('eaterplanet_ecommerce_member_charge_flow')->where( array('state' => 1) )->count();

		$count_status_3 = M('eaterplanet_ecommerce_member_charge_flow')->where( array('state' => 3) )->count();

		$count_status_4 = M('eaterplanet_ecommerce_member_charge_flow')->where( array('state' => 4) )->count();

		$count_status_5 = M('eaterplanet_ecommerce_member_charge_flow')->where( array('state' => 5) )->count();

		$count_status_8 = M('eaterplanet_ecommerce_member_charge_flow')->where( array('state' => 8) )->count();

		$count_status_9 = M('eaterplanet_ecommerce_member_charge_flow')->where( array('state' => 9) )->count();

		$count_status_10 = M('eaterplanet_ecommerce_member_charge_flow')->where( array('state' => 10) )->count();


		$this->list = $list;
		$this->pager = $pager;
		$this->all_count = $all_count;
		$this->count_status_1 = $count_status_1;
		$this->count_status_3 = $count_status_3;
		$this->count_status_4 = $count_status_4;
		$this->count_status_5 = $count_status_5;
		$this->count_status_8 = $count_status_8;
		$this->count_status_9 = $count_status_9;
		$this->count_status_10 = $count_status_10;

		$this->display();
	}




	public function displayordercoupon()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$displayorder = intval($_GPC['value']);
		$items = M()->query('SELECT id FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_coupon WHERE id in( ' . $id . ' ) ' );

		foreach ($items as $item) {
			M('eaterplanet_ecommerce_coupon')->where( array('id' => $id) )->save( array('displayorder' => $displayorder) );
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function addcoupon()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (IS_POST) {
			if(empty($_GPC['displayorder'])){
				show_json(0, array('message' => '排序必须填写'));
			}
			if(!is_numeric($_GPC['displayorder'])){
				show_json(0, array('message' => '排序必须在1-999之间的整数'));
			}
			if(floor($_GPC['displayorder']) != $_GPC['displayorder']){
				show_json(0, array('message' => '排序必须在1-999之间的整数'));
			}
			if($_GPC['displayorder'] < 1 || $_GPC['displayorder'] > 999){
				show_json(0, array('message' => '排序必须在1-999之间的整数'));
			}
			if(empty($_GPC['voucher_title'])){
				show_json(0, array('message' => '优惠券名称必须填写'));
			}
			if(empty($_GPC['catid'])){
				show_json(0, array('message' => '分类必须选择'));
			}
			if(!empty($_GPC['limit_money']) && $_GPC['limit_money'] != 0 ){
				if($_GPC['credit'] > $_GPC['limit_money']){
					show_json(0, array('message' => '立减金额应小于等于使用条件金额'));
				}
			}

			$data = array();
			$data['catid'] = $_GPC['catid'];
			$data['voucher_title'] = $_GPC['voucher_title'];
			$data['thumb'] = $_GPC['thumb'];
			$data['credit'] = $_GPC['credit'];
			$data['type'] = 1;

			/*if(empty($_GPC['coupon_collection_method'])){
				$data['is_index_show'] = 0;
				$data['is_index_alert'] =1;
			}else{
				$data['is_index_show'] = 1;
				$data['is_index_alert'] =0;
			}*/
			$data['is_index_show'] = $_GPC['is_index_show'];
			$data['is_index_alert'] = $_GPC['is_index_alert'];

			$data['is_share_doubling'] = 0;
			$data['get_over_hour'] = $_GPC['get_over_hour'] * 24;
			$data['is_limit_goods_buy'] = $_GPC['is_limit_goodsbuy'];
			$data['is_new_man'] = $_GPC['is_new_man'];
			$data['share_title'] = '';
			$data['share_desc'] = '';
			$data['share_logo'] = '';
			$data['timelimit'] = $_GPC['timelimit'];
			$data['person_limit_count'] = $_GPC['person_limit_count'];
			$data['limit_goods_list'] = $_GPC['limit_goods_list'];
			$data['goodscates'] = $_GPC['goodscates'];
			$data['limit_money'] = $_GPC['limit_money'];
			$data['total_count'] = $_GPC['total_count'];
			$data['send_count'] = $_GPC['send_count'];
			$data['add_time'] = time();
			$data['displayorder'] = $_GPC['displayorder'];
			$data['begin_time'] = strtotime($_GPC['time']['start']);
			$data['end_time'] = strtotime($_GPC['time']['end']) + 86399;

			if($id > 0)
			{
				M('eaterplanet_ecommerce_coupon')->where( array('id' => $id) )->save( $data );
			}else{
				M('eaterplanet_ecommerce_coupon')->add($data);
			}

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$category = M()->query('select * from ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_coupon_category where  merchid=0 order by id desc');

		$goods_category = D('Seller/GoodsCategory')->getFullCategory(true, true);

		$this->goods_category = $goods_category;

		$new_category = array();
		foreach($category as $key =>$val)
		{
			$new_category[$val['id']] = $val;
		}

		$this->category = $new_category;

		if (empty($id)) {
			$starttime = time();
			$endtime = strtotime(date('Y-m-d H:i:s', $starttime) . '+7 days');
		}else{

			$item = M('eaterplanet_ecommerce_coupon')->where( array('id' => $id) )->find();

			$item['get_over_hour'] = $item['get_over_hour'] / 24;
			$starttime = $item['begin_time'];
			$endtime = $item['end_time'];

			$limit_goods = array();

			if( !empty($item['limit_goods_list']) )
			{
				$limit_goods = M('eaterplanet_ecommerce_goods')->field('id as gid,goodsname,subtitle')->where('id in('.$item['limit_goods_list'].')')->order('id desc')->select();

				foreach($limit_goods as $kk => $vv)
				{
					$thumb = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $vv['gid'] ) )->order('id asc')->find();

					$vv['image'] =  tomedia($thumb['image']);

					$limit_goods[$kk] = $vv;
				}
			}

			if($item['is_index_show'] == 0 && $item['is_index_alert'] == 1){
				$item['coupon_collection_method']=0;
			}else{
				$item['coupon_collection_method']=1;
			}

			$this->limit_goods = $limit_goods;

			$this->item = $item;

		}
		$this->starttime = $starttime;
		$this->endtime = $endtime;


		$this->display();
	}

	public function fullreduction()
	{
		$_GPC = I('request.');

		$this->gpc = $_GPC;
		if (IS_POST) {
			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			$data['is_open_fullreduction'] = intval($data['is_open_fullreduction']);
			//验证优惠金额
			if($data['is_open_fullreduction'] == 1){
				$count = 1;
				if(!is_numeric($data['full_money']) || !is_numeric($data['full_reducemoney'])){
					show_json(0, array('message' => '1级金额必须1~10w的数字'));
				}
				$data['full_money'] = round(floatval($data['full_money']),2);
				$data['full_reducemoney'] = round(floatval($data['full_reducemoney']),2);
				if($data['full_money'] < 0 || $data['full_money'] > 100000 || $data['full_reducemoney'] < 0 || $data['full_reducemoney'] > 100000 ){
					show_json(0, array('message' => '1级金额必须1~10w的数字'));
				}
				if($data['full_money'] == 0 || $data['full_reducemoney'] == 0){
					show_json(0, array('message' => '1级优惠金额不能为0'));
				}
				$full_delete = array();
				for($i = 2;$i <= 5;$i++ ){
					if(isset($data['full_money_'.$i])){
						if(!is_numeric($data['full_money_'.$i]) || !is_numeric($data['full_reducemoney_'.$i])){
							show_json(0, array('message' => $i.'级金额必须1~10w的数字'));
						}
						$data['full_money_'.$i] = floatval(round($data['full_money_'.$i],2));
						$data['full_reducemoney_'.$i] = floatval(round($data['full_reducemoney_'.$i],2));
						if($data['full_money_'.$i] < 0 || $data['full_money_'.$i] > 100000 || $data['full_reducemoney_'.$i] < 0 || $data['full_reducemoney_'.$i] > 100000 ){
							show_json(0, array('message' => $i.'级金额必须1~10w的数字'));
						}
						if($data['full_money_'.$i] == 0 || $data['full_reducemoney_'.$i] == 0){
							show_json(0, array('message' => $i.'级优惠金额不能为0'));
						}
						if($i == 2){
							if($data['full_money_'.$i] < $data['full_money']){
								show_json(0, array('message' => $i.'级优惠金额不能低于上级金额'));
							}
							if($data['full_reducemoney_'.$i] < $data['full_reducemoney']){
								show_json(0, array('message' => $i.'级优惠金额不能低于上级金额'));
							}
						}else{
							if($data['full_money_'.$i] < $data['full_money_'.($i-1)]){
								show_json(0, array('message' => $i.'级优惠金额不能低于上级金额'));
							}
							if($data['full_reducemoney_'.$i] < $data['full_reducemoney_'.($i-1)]){
								show_json(0, array('message' => $i.'级优惠金额不能低于上级金额'));
							}
						}
						$count++;
					}else{
						$full_delete['full_money_'.$i] = 0;
						$full_delete['full_reducemoney_'.$i] = 0;
					}
				}
			}
			D('Seller/Config')->delete_config($full_delete);
			D('Seller/Config')->update($data);
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;

		$this->display();
	}

	public function deletecategory()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		M('eaterplanet_ecommerce_coupon_category')->where( array('id' => $id) )->delete();

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function deletecoupon()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('eaterplanet_ecommerce_coupon')->field('id')->where( 'id in( ' . $id . ' )' )->select();

		foreach ($items as $item ) {
			M('eaterplanet_ecommerce_coupon')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function category()
	{
		$_GPC = I('request.');

		if (!empty($_GPC['catid'])) {
			foreach ($_GPC['catid'] as $k => $v) {
				$data = array('name' => trim($_GPC['catname'][$k]), 'displayorder' => $k, 'status' => intval($_GPC['status'][$k]));

				if (empty($v)) {
					M('eaterplanet_ecommerce_coupon_category')->add($data);
				}
				else {
					M('eaterplanet_ecommerce_coupon_category')->where( array('id' => $v) )->save( $data );
				}
			}

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_coupon_category WHERE  merchid=0 ORDER BY displayorder asc');

		$this->list = $list;

		$this->display();
	}

	public function querycoupon()
	{
		$_GPC = I('request.');
		$this->gpc = $_GPC;

		$kwd = trim($_GPC['keyword']);
		$diy = intval($_GPC['diy']);
		$live = intval($_GPC['live']);

		$condition = ' and ( (timelimit = 1 and end_time > '.time().' ) or timelimit =0 )';
		if (!(empty($kwd)))
		{
			$condition .= ' AND voucher_title like "%'.$kwd.'%"';
		}
		$time = time();
		$ds = M()->query('SELECT * FROM ' . C('DB_PREFIX').
				'eaterplanet_ecommerce_coupon  WHERE 1 ' . $condition . ' ORDER BY id asc');

		$this->ds = $ds;
		$this->time = $time;

		include $this->display();
	}


	/**
		签到奖励
	**/
	public function signinreward()
	{
		$_GPC = I('request.');

		if (IS_POST) {

			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));

			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;

		include $this->display();
	}



	public function delrecharge()
	{
		$id = I('request.id');

		M('eaterplanet_ecommerce_chargetype')->where( array('id' => $id ) )->delete();

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	/**
	 * 充值设置
	 * @return [type] [description]
	 */
	public function recharge ()
	{
		$_GPC = I('request.');

		if (IS_POST) {

			$catid = $_GPC['catid'];
			$money = $_GPC['money'];
			$give = $_GPC['give'];

			$need_ids = array();

			foreach( $catid as $id )
			{
				if( $id > 0 )
				{
					$need_ids[] = $id;
				}
			}

			$list = M('eaterplanet_ecommerce_chargetype')->field('id')->order('id asc')->select();

			foreach($list as $vv )
			{
				if( empty($need_ids) || !in_array($vv['id'], $need_ids) )
				{
					M('eaterplanet_ecommerce_chargetype')->where( array('id' => $vv['id']) )->delete();
				}
			}
			//以上清理历史数据

			foreach( $catid as $key => $id )
			{
				if( $id > 0 )
				{
					M('eaterplanet_ecommerce_chargetype')->where( array('id' => $id) )->save( array('money' => $money[$key], 'send_money' => $give[$key]) );
				}else{
					$data = array();
					$data['money'] = $money[$key];
					$data['send_money'] = $give[$key];
					$data['addtime'] = time();
					$data['uniacid'] = $_W['uniacid'];

					M('eaterplanet_ecommerce_chargetype')->add($data);
				}
			}
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$list = M('eaterplanet_ecommerce_chargetype')->order( 'id asc' )->select();

		$this->list = $list;

		$this->display();
	}

	public function recharge_config ()
	{
		$_GPC = I('request.');

		if (IS_POST) {

			$catid = $_GPC['catid'];
			$money = $_GPC['money'];
			$give = $_GPC['give'];

			$need_ids = array();

			foreach( $catid as $id )
			{
				if( $id > 0 )
				{
					$need_ids[] = $id;
				}
			}

			$list = M('eaterplanet_ecommerce_chargetype')->field('id')->order('id asc')->select();

			foreach($list as $vv )
			{
				if( empty($need_ids) || !in_array($vv['id'], $need_ids) )
				{
					M('eaterplanet_ecommerce_chargetype')->where( array('id' => $vv['id']) )->delete();
				}
			}
			//以上清理历史数据

			foreach( $catid as $key => $id )
			{
				if( $id > 0 )
				{
					M('eaterplanet_ecommerce_chargetype')->where( array('id' => $id) )->save( array('money' => $money[$key], 'send_money' => $give[$key]) );
				}else{
					$data = array();
					$data['money'] = $money[$key];
					$data['send_money'] = $give[$key];
					$data['addtime'] = time();
					$data['uniacid'] = $_W['uniacid'];

					M('eaterplanet_ecommerce_chargetype')->add($data);
				}
			}
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$list = M('eaterplanet_ecommerce_chargetype')->order( 'id asc' )->select();

		$this->list = $list;

		$this->display('Marketing/recharge');
	}



	public function special ()
	{
		$_GPC = I('request.');

		$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		$searchtime = isset($_GPC['searchtime']) && !empty($_GPC['searchtime']) ? $_GPC['searchtime'] : '';

		$this->starttime = $starttime;
		$this->endtime = $endtime;


		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = ' 1 ';

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' AND name LIKE "%'.trim($_GPC['keyword']).'%" ';
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);

			if (!empty($starttime)) {
				$condition .= ' AND addtime >= '.$starttime;
			}

			if (!empty($endtime)) {
				$condition .= ' AND addtime <= '.$endtime;
			}
		}

		if ($_GPC['gettype'] != '') {
			$condition .= ' AND enabled = '.intval($_GPC['gettype']);
		}

		$sql = 'SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_special ' . ' where 1 and ' . $condition . ' ORDER BY displayorder DESC,id DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;

		$list = M()->query($sql);


		$total_arr = M()->query('SELECT COUNT(*) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_special where 1 and ' . $condition );

		$total = $total_arr[0]['count'];

		$pager = pagination2($total, $pindex, $psize);


		$this->list = $list;
		$this->pager = $pager;

		$this->_GPC = $_GPC;

		$this->display();
	}

	public function addspecial ()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (IS_POST) {

			$data = array();

			$data['name'] = $_GPC['name'];
			$data['cover'] = $_GPC['cover'];
			$data['type'] = intval($_GPC['type']);
			$data['enabled'] = intval($_GPC['enabled']);

			$data['is_index'] = intval($_GPC['is_index']);
			$data['show_type'] = intval($_GPC['show_type']);
			$data['index_pos'] = intval($_GPC['index_pos']);

			$data['special_title'] = $_GPC['special_title'];
			$data['special_cover'] = $_GPC['special_cover'];
			$data['displayorder'] = $_GPC['displayorder'];
			$data['goodsids'] = $_GPC['limit_goods_list'];
			$data['bg_color'] = trim($_GPC['bg_color']);
			$data['begin_time'] = strtotime($_GPC['time']['start']);
			$data['end_time'] = strtotime($_GPC['time']['end']) ;
			$data['addtime'] = time();

			if($id > 0)
			{
				M('eaterplanet_ecommerce_special')->where( array('id' => $id) )->save( $data );
			}else{
				$id = M('eaterplanet_ecommerce_special')->add($data);
			}

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}


		$item = M('eaterplanet_ecommerce_special')->where( array('id' => $id ) )->find();

		if (empty($item)) {
			$starttime = time();
			$endtime = strtotime(date('Y-m-d H:i:s', $starttime) . '+7 days');
		}else{
			$item['get_over_hour'] = $item['get_over_hour'] / 24;
			$starttime = $item['begin_time'];
			$endtime = $item['end_time'];

			$limit_goods = array();
			$time = time();
			//goodsids
			if( !empty($item['goodsids']) )
			{

				 $goodsids_arr = explode(',',  $item['goodsids'] );


				//$limit_goods = M('eaterplanet_ecommerce_goods')->field('id as gid,goodsname,subtitle,total,')->where(  array('id' => array('in', $goodsids_arr )  ) )->order('id desc')->select();

				$sql_goods = "select g.id as gid,g.goodsname,g.subtitle,g.total,g.grounding,gc.begin_time,gc.end_time from "
						.C('DB_PREFIX')."eaterplanet_ecommerce_goods as g,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc "
        	           ." where  g.id in (".$item['goodsids'].") and g.id=gc.goods_id  order by g.id desc ";
				///echo $sql_goods;
				$limit_goods = M()->query($sql_goods);
				foreach($limit_goods as $kk => $vv)
				{
					$thumb = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $vv['gid'] ) )->order('id asc')->find();

					$vv['image'] =  tomedia($thumb['image']);

					if($time >= $vv['begin_time'] && $time <= $vv['end_time']){
						$vv['is_finished'] = 0;
					}else{
						$vv['is_finished'] = 1;//已结束
					}

					if($vv['total'] <= 0){
						$vv['is_grab'] = 1;//已抢光
					}else{
						$vv['is_grab'] = 0;
					}

					$limit_goods[$kk] = $vv;
				}
			}
			$this->limit_goods = $limit_goods;
		}

		$this->starttime = $starttime;
		$this->endtime = $endtime;

		$this->item = $item;
		$this->_GPC = $_GPC;
		$this->display();
	}

	public function changespecial()
    {

		$_GPC = I('request.');
        $id = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = trim($_GPC['type']);
        $value = trim($_GPC['value']);

        if (!(in_array($type, array('enabled', 'displayorder' , 'is_index')))) {
            show_json(0, array('message' => '参数错误'));
        }

        $items = M()->query('SELECT id FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_special WHERE id in( ' . $id . ' ) ' );

        foreach ($items as $item) {

			M('eaterplanet_ecommerce_special')->where( array('id' => $item['id']) )->save( array($type => $value) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

    public function deletespecial()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}


		$items = M()->query('SELECT id FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_special WHERE id in( ' . $id . ' ) ');

		foreach ($items as $item ) {
			M('eaterplanet_ecommerce_special')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	/**
	 * 弹窗选择
	 * @return [type] [description]
	 */
	public function couponquery()
	{

	    $kwd =  I('request.keyword','','trim');
		$is_ajax =  I('request.is_ajax',0,'intval');
		$coupon_ids = I('request.coupon_ids');
		$condition = ' ( (timelimit = 1 and end_time > '.time().' ) or timelimit =0 )';
	    if (!empty($kwd)) {
			$condition .= ' AND voucher_title LIKE "%'.$kwd.'%"';

		}
	    /**
			分页开始
		**/
		$page =  I('request.page',1,'intval');
		$page = max(1, $page);
		$page_size = 10;
		/**
			分页结束
		**/

		$ds = M('eaterplanet_ecommerce_coupon')->where( $condition )->order('id desc')->limit( (($page - 1) * $page_size) . ',' . $page_size )->select();
		$total = M('eaterplanet_ecommerce_coupon')->where( $condition )->count();

		$category_arr = M()->query('select * from ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_coupon_category   order by id desc');
		$category = array();
		foreach($category_arr as $vv)
		{
			$category[$vv['id']] = $vv['name'];
		}

		$ret_html = '';
	    foreach ($ds as &$value) {
			if(in_array($value['id'],$coupon_ids)){
				$value['is_exist'] = 1;
			}else{
				$value['is_exist'] = 0;
			}
			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '<td style="width:50px;">'.$value['id'].'</td>';
				$ret_html .= '<td style="width:200px;">'.$value['voucher_title'].'<label class="label label-success">购物</label>';
				if($value['catid']) {
					$ret_html .= '<label class="label label-primary">'.$category[$value['catid']].'</label>';
				}
				if($value['is_new_man']==1) {
					$ret_html .= '<label class="layui-badge">新人券</label>';
				}
				$ret_html .= '</td>';
				$ret_html .= '<td>';
				$ret_html .= $value['credit']. '元 ';
				if( $value['limit_money']>0) {
					$ret_html .= '<span class="text-danger">满'.$value['limit_money'].'.可用</span>';
				}else{
					$ret_html .= '<span class="text-warning">不限</span>';
				}
				$ret_html .= '</td>';
				$ret_html .=  '<td>';
				if($value['total_count']==-1){
					$ret_html .= '无限';
				}else{
					$ret_html .= '剩余：'.($value['total_count']-$value['send_count'])<0?0:$value['total_count']-$value['send_count'];
				}
				$ret_html .= '</td>';
				if($value['is_exist'] == 1){
					$ret_html .=  '<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="btn-primary btn-sm choose_dan_head" disabled="disabled" style="color:#999;">选择</a></td>';
				}else{
					$ret_html .=  '<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="btn-primary btn-sm choose_dan_head" data-json=\''.json_encode($value).'\'>选择</a></td>';
				}
				$ret_html .=  '</tr>';
			}
	    }

		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
	    unset($value);

		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
			die();
		}

	   	$this->ds = $ds;
	   	$this->pager = $pager;
	   	$this->category = $category;

	    $this->display();
	}


	/**
	 * 弹窗专题选择
	 */
	public function special_select ()
	{
		$_GPC = I('request.');

		if(IS_AJAX) {
			$page = I('request.page', 1);
        	$page_size = I('request.page_size', 10);

			$condition = "1";

			if (!empty($_GPC['promotion_name'])) {
				$_GPC['promotion_name'] = trim($_GPC['promotion_name']);
				$condition .= ' AND name LIKE "%'.trim($_GPC['promotion_name']).'%" ';
			}

			$sql = 'SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_special ' . ' where ' . $condition ;

			$count=count(M()->query($sql));

			$Page = new \Think\Page($count, $page_size);

			$list = M()->query('SELECT * FROM '.C('DB_PREFIX').'eaterplanet_ecommerce_special WHERE '.$condition.' ORDER BY displayorder DESC,id DESC LIMIT '.($page-1)*$page_size.','.$Page->listRows);

			if(count($list)) {
				foreach($list as $kk => $vv)
				{
					$vv['image'] =  tomedia($vv['cover']);
					$list[$kk] = $vv;
				}
			}

			$page_count = ceil($count/$page_size);


			$res = array( "code" => 0, "data" => array('list'=>$list, 'count'=>$count, 'page_count'=>$page_count) );
			echo json_encode($res);
			die();
		} else {
			$this->display();
		}
	}
}
?>
