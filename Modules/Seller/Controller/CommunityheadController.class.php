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
use Seller\Model\CommunityheadModel;
class CommunityheadController extends CommonController {

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='团长管理';
		$this->breadcrumb2='团长列表';
		$this->blog_type = array('question' => '常见帮助');
		//'pinjie' => '拼团介绍',
	}

	public function index(){


		$params[':uniacid'] = $uniacid;
		$condition = '  ';
		$pindex = I('get.page',1);
		$psize = 20;

		$keyword = I('get.keyword','');
		$this->keyword = $keyword;

		if (!empty($keyword)) {
			$condition .= ' and ( m.username like '.'"%' . $keyword . '%"'.' or ch.community_name like '.'"%' . $keyword . '%"'.' or ch.head_name like '.'"%' . $keyword . '%"'.' or ch.head_mobile like '.'"%' . $keyword . '%"'.' or ch.address like '.'"%' . $keyword . '%"'.') ';
		}

		$time = I('get.time');

		$this->time = $time;

		if (!empty($time['start']) && !empty($time['end'])) {
			$starttime = strtotime($time['start']);
			$endtime = strtotime($time['end']);
			$condition .= ' AND ch.apptime >= '.$starttime.' AND ch.apptime <= '.$endtime;
		}

		$comsiss_state = I('get.comsiss_state',-1);
		$this->comsiss_state = $comsiss_state;

		if ($comsiss_state != '' && $comsiss_state >= 0) {
			$condition .= ' and ch.state=' . intval($comsiss_state);
		}

		$level_id = I('get.level_id','');

		if( $level_id != '' )
		{
			$condition .= ' and ch.level_id=' . intval($level_id);
		}

		$this->level_id = $level_id;


		$group_id = I('get.group_id','');

		if( $group_id != '' )
		{
			$condition .= ' and ch.groupid=' . intval($group_id);
		}

		$this->group_id = $group_id;

		$sql = 'SELECT ch.*,m.we_openid,m.username,m.avatar FROM ' . C('DB_PREFIX') . "eaterplanet_community_head as ch left join
					".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id
						WHERE 1  " . $condition . ' order by ch.id desc  ';

		$export = I('get.export',0);

		if (empty($export)) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}


		$list = M()->query($sql);

		$sql_count = 'SELECT count(1) as total FROM ' . C('DB_PREFIX') . 'eaterplanet_community_head as ch
						left join '.C('DB_PREFIX').'eaterplanet_ecommerce_member as m on ch.member_id = m.member_id
						WHERE 1  ' . $condition;

		$total_arr = M()->query($sql_count);

		$total = $total_arr[0]['total'];



		$all_sell_count = M('eaterplanet_ecommerce_goods')->where( array('is_all_sale' => 1,'type' => 'normal') )->count();




		//---------等级

		$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();

		$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');
		$default_comunity_money = D('Home/Front')->get_config_by_name('default_comunity_money');

		$list_default = array(
			array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);
		if(empty($community_head_level)){
			$community_head_level = array();
		}
		$community_head_level = array_merge($list_default, $community_head_level);

		$level_id_to_name = array();

		foreach($community_head_level as $kk => $vv)
		{
			$level_id_to_name[$vv['id']] = $vv['levelname'];
		}
		//---------等级

		$this->level_id_to_name = $level_id_to_name;
		$this->community_head_level = $community_head_level;


		$group_list = M('eaterplanet_community_head_group')->order('id asc')->select();

		foreach($group_list as $vv)
		{
			$keys_group[$vv['id']] = $vv['groupname'];
		}

		$this->group_list = $group_list;



		foreach( $list as $key => $val )
		{
			//commission_info pre_total_money

			$commission_info = M('eaterplanet_community_head_commiss')->where( array('head_id' => $val['id'],'member_id' => $val['member_id'] ) )->find();


			//预计佣金 commission_total
			//$pre_total_money = M('eaterplanet_community_head_commiss_order')->where( array('state' => 0, 'head_id' => $val['id'] ) )->sum('money');


			$sql = "select sum( co.money ) as money from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
	                    where  co.order_goods_id = og.order_goods_id and  og.is_refund_state = 0 and co.state = 0 and co.head_id = ".$val['id']." order by co.id desc ";


			$pre_total_money_list = M()->query($sql);

			$pre_total_money = $pre_total_money_list[0]['money'];

			if( empty($pre_total_money) )
			{
				$pre_total_money = 0;
			}

			$commission_info['pre_total_money'] = $pre_total_money;


			$commission_info['commission_total'] = $commission_info['money']+ $commission_info['dongmoney'] + $commission_info['getmoney'] + $pre_total_money;


			$val['groupname'] = $keys_group[ $val['groupid'] ];

			$val['pre_total_money'] = $commission_info['pre_total_money'];
			$val['commission_total'] = $commission_info['commission_total'];

			if( empty($commission_info['money']) )
			{
				$commission_info['money'] = 0;
			}
			$val['money'] = $commission_info['money'];

			if( empty($commission_info['dongmoney']) )
			{
				$commission_info['dongmoney'] = 0;
			}
			$val['dongmoney']= $commission_info['dongmoney'];

			if( empty($commission_info['getmoney']) )
			{
				$commission_info['getmoney'] = 0;
			}
			$val['getmoney'] = $commission_info['getmoney'];



			$val['commission_info'] = $commission_info;
			//普通等级

			$val['agent_name'] = '';
			if( !empty($val['agent_id']) && $val['agent_id'] > 0 )
			{
				$parent_community_head = M('eaterplanet_community_head')->field('head_name')->where( array('id' => $val['agent_id'] ) )->find();


				$val['agent_name'] = $parent_community_head['head_name'];
			}

			$member_info = M('eaterplanet_ecommerce_member')->field('username,avatar,we_openid')->where( array('member_id' => $val['member_id'] ) )->find();

			$val['province_name'] = D('Seller/Area')->get_area_info($val['province_id']);
			$val['city_name'] = D('Seller/Area')->get_area_info($val['city_id']);
			$val['area_name'] = D('Seller/Area')->get_area_info($val['area_id']);
			$val['country_name'] = D('Seller/Area')->get_area_info($val['country_id']);



			//团长商品
			$head_goods_count_arr = M()->query("select g.id from ".C('DB_PREFIX')."eaterplanet_community_head_goods as hg ,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc ,".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g
								where hg.goods_id = gc.goods_id and gc.goods_id = g.id and g.type ='normal' and g.is_all_sale=0  and hg.head_id = ". $val['id']  );



			$val['head_goods_count'] = 	count($head_goods_count_arr);
			//所有团长可售商品
			$val['all_sell_count'] = $all_sell_count;

			//总商品数
			$val['goods_count'] =$val['head_goods_count'] + $val['all_sell_count'] ;




			//团长订单
			$val['head_order_count'] = M('eaterplanet_ecommerce_order')->where( array('head_id' => $val['id'] ) )->count();



			$member_count_arr = M()->query("SELECT count(DISTINCT(member_id) ) as count  FROM ".C('DB_PREFIX')."eaterplanet_community_history WHERE head_id =". $val['id']);

			$val['member_count'] = $member_count_arr[0]['count'];

			$val['agent_count'] = M('eaterplanet_community_head')->where( array('agent_id' => $val['id'] ) )->count();
			//$val['member_info'] = $member_info;
			$list[$key] = $val;
		}



		if ($export == '1') {


			foreach ($list as &$row) {

			    //$row['commission_total'] = 0;
			    //$row['getmoney'] = 0;
				$row['commission_total'] = $row['commission_total'];
			    $row['pre_total_money'] = $row['pre_total_money'];

				$row['money']=$row['money'];
			    $row['dongmoney']=$row['dongmoney'];
				$row['getmoney']=$row['getmoney'];


			    $row['fulladdress'] = $row['province_name'].$row['city_name'].$row['area_name'].$row['country_name'].$row['address'];
			    $row['addtime'] = date('Y-m-d H:i:s', $row['addtime']);
			    $row['apptime'] = date('Y-m-d H:i:s', $row['apptime']);

			    $row['state'] = $row['state'] == 1 ? '已审核':'未审核';

			}

			unset($row);

			$columns = array(
				array('title' => 'ID', 'field' => 'member_id', 'width' => 12),
				array('title' => '微信用户名', 'field' => 'username', 'width' => 12),
			    array('title' => '团长名称', 'field' => 'head_name', 'width' => 12),
			    array('title' => '小区名称', 'field' => 'community_name', 'width' => 12),
				array('title' => '联系方式', 'field' => 'head_mobile', 'width' => 12),
				array('title' => '在售商品数量', 'field' => 'goods_count', 'width' => 24),
				array('title' => 'openid', 'field' => 'we_openid', 'width' => 24),
				array('title' => '累计佣金', 'field' => 'commission_total', 'width' => 12),
				array('title' => '打款佣金', 'field' => 'getmoney', 'width' => 12),

				array('title' => '待确认', 'field' => 'pre_total_money', 'width' => 12),
				array('title' => '可提现', 'field' => 'money', 'width' => 12),
				array('title' => '已打款', 'field' => 'getmoney', 'width' => 12),
				array('title' => '提现中', 'field' => 'dongmoney', 'width' => 12),
				array('title' => '总收入', 'field' => 'commission_total', 'width' => 12),

			    array('title' => '省', 'field' => 'province_name', 'width' => 12),
			    array('title' => '市', 'field' => 'city_name', 'width' => 12),
			    array('title' => '区', 'field' => 'area_name', 'width' => 12),
			    array('title' => '街道/镇', 'field' => 'country_name', 'width' => 12),
			    array('title' => '提货地址', 'field' => 'address', 'width' => 24),
			    array('title' => '完整提货地址', 'field' => 'fulladdress', 'width' => 24),
				array('title' => '申请时间', 'field' => 'addtime', 'width' => 12),
				array('title' => '成为团长时间', 'field' => 'apptime', 'width' => 12),
				array('title' => '审核状态', 'field' => 'state', 'width' => 12)
			);

			D('Seller/Excel')->export($list, array('title' => '团长数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));

		}

		$pager = pagination2($total, $pindex, $psize);

		$this->pager = $pager;
		$this->list = $list;

		$open_danhead_model = D('Home/Front')->get_config_by_name('open_danhead_model');

		if( empty($open_danhead_model) )
		{
			$open_danhead_model = 0;
		}

		$this->open_danhead_model = $open_danhead_model;
		$this->display('Communityhead/communityhead');
	}


	public function lineheadquery()
	{
		$_GPC = I('request.');
		$kwd = trim($_GPC['keyword']);

		$is_soli = isset($_GPC['is_soli']) ? $_GPC['is_soli'] : 0;

		$is_just_line = isset($_GPC['is_just_line']) ? $_GPC['is_just_line'] : 0;

		$is_memberlist = isset($_GPC['is_memberlist']) ? $_GPC['is_memberlist'] : 0;

		$is_delivery = isset($_GPC['is_delivery']) ? $_GPC['is_delivery'] : 0;


		$is_member_choose = isset($_GPC['is_member_choose']) ? $_GPC['is_member_choose'] : 0;

		$is_mult = isset($_GPC['is_mult']) ? $_GPC['is_mult'] : 0;

		$s_member_id = isset($_GPC['s_member_id']) ? $_GPC['s_member_id'] : 0;


		$params = array();

		$condition = ' and state=1 and enable=1 ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `community_name` LIKE "%'.$kwd.'%" or `head_name` LIKE "%'.$kwd.'%"  or `head_mobile` LIKE "%'.$kwd.'%" )';

		}




		if($is_delivery == 0 && $is_soli ==0 && $is_memberlist == 0)
		{
			$had_head_list = M()->query('select head_id from '.C('DB_PREFIX')."eaterplanet_ecommerce_deliveryline_headrelative ");
		}

		//is_just_line
		if( $is_just_line == 1 )
		{
			$had_head_list = M()->query('select head_id from '.C('DB_PREFIX')."eaterplanet_ecommerce_deliveryline_headrelative ");

			$un_slhead_arr = array();

			foreach($had_head_list as $val)
			{
				$un_slhead_arr[] = $val['head_id'];
			}

			$un_slhead_str = "";

			if( !empty($un_slhead_arr) )
			{
				$condition .= " and id not in( ".implode(',', $un_slhead_arr )." ) ";
			}else{
				$un_slhead_str = ' 1<>1 ';
			}

		}

		$ds = M()->query('SELECT * FROM ' . C('DB_PREFIX') . 'eaterplanet_community_head WHERE 1 ' . $condition . ' order by id asc');

		$need_data = array();

		if( !empty($had_head_list) )
		{
			$ids_list = array();
			foreach($had_head_list as $vv)
			{
				$ids_list[]  =  $vv['head_id'];
			}

			foreach($ds as $key => $val)
			{
				if( !in_array($val['head_id'], $ids_list) )
				{
					$need_data[$key] = $val;
				}
			}
		}else{
			$need_data = $ds;
		}

		$s_html = '';

		foreach ($need_data as &$value) {

			$province = D('Home/Front')->get_area_info($value['province_id']);
			$city = D('Home/Front')->get_area_info($value['city_id']);
			$area = D('Home/Front')->get_area_info($value['area_id']);
			$country = D('Home/Front')->get_area_info($value['country_id']);
			//address
			$full_name = $province['name'].$city['name'].$area['name'].$country['name'].$value['address'];

			$value['fullAddress'] = $full_name;

			$s_html.="<tr>";

			$s_html.='<td>'.$value['community_name'].'</td>';
			$s_html.='<td>'.$value['head_name'].'</td>';
			$s_html.='<td>'.$value['head_mobile'].'</td>';
            $s_html.='<td>'.$value['fullAddress'].'</td>';


			if( $is_member_choose == 1 )
			{
				$s_html.='<td><a href="javascript:;" class="choose_dan_head_mb btn-primary btn-sm" data-json=\''.json_encode($value).'\'>选择</a></td>';
			}
			else{
				$s_html.='<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_head btn-primary btn-sm" data-json=\''.json_encode($value).'\'>选择</a></td>';
			}



			$s_html.="</tr>";
		}

		if( isset($_GPC['is_ajax']) && $_GPC['is_ajax'] == 1 )
		{
			echo json_encode( array('code' => 0, 'html' =>$s_html ) );
			die();
		}

		unset($value);

		$this->gpc = $_GPC;
		$this->need_data = $need_data;
		$this->had_head_list = $had_head_list;
		$this->s_member_id = $s_member_id;

		$this->is_just_line = $is_just_line;

		$this->is_mult = $is_mult;

		if( $is_soli == 1 )
		{
			$this->display('Communityhead/lineheadquery_soli');
		}
		else if( $is_delivery == 1 )
		{
			$this->display('Communityhead/lineheadquery_delivery');
		}
		else if( $is_member_choose == 1 )
		{
			include $this->display('Communityhead/lineheadquery_mb_choose');
		}
		else{
			$this->display('Communityhead/lineheadquery');
		}
	}

	//------begin-------

	public function usergroup()
	{

		$_GPC = I('request.');

		$membercount = M('eaterplanet_community_head')->where("groupid=0")->count();

		$list = array(
			array('id' => 'default', 'groupname' => '默认分组', 'membercount' => $membercount )
			);

		$condition = ' ';
		$params = array(':uniacid' => $_W['uniacid']);

		if (!(empty($_GPC['keyword']))) {
			$_GPC['keyword'] = trim($_GPC['keyword']);

			$condition .= ' and ( groupname like "%'.$_GPC['keyword'].'%")';

		}

		$alllist = M('eaterplanet_community_head_group')->where( "1 ". $condition  )->order('id asc')->select();

		foreach ($alllist as &$row ) {

			$sql = 'select count(*) as count from ' . C('DB_PREFIX') .'eaterplanet_community_head where  find_in_set('.$row['id'].',groupid) limit 1';

			$membercount_arr = M()->query($sql);

			$row['membercount'] = $membercount_arr[0]['count'];
		}

		unset($row);

		if (empty($_GPC['keyword'])) {
			$list = array_merge($list, $alllist);
		}
		 else {
			$list = $alllist;
		}

		$this->gpc = $_GPC;
		$this->list = $list;

		$this->display();
	}

	public function deleteusergroup()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('eaterplanet_community_head_group')->field('id,groupname')->where( 'id in( ' . $id . ' )' )->select();

		foreach ($items as $item ) {

			M('eaterplanet_community_head')->where( array('groupid' => $item['id'] ) )->save( array('groupid' => 0) );

			M('eaterplanet_community_head_group')->where( array('id' => $item['id']) )->delete();

		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function addusergroup()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);


		$group = M('eaterplanet_community_head_group')->where( array('id' => $id ) )->find();

		if (IS_POST) {
			$data = array( 'groupname' => trim($_GPC['groupname']) );

			if (!(empty($id))) {
				M('eaterplanet_community_head_group')->where( array('id' => $id) )->save( $data );
			}
			 else {
				$id = M('eaterplanet_community_head_group')->add($data);
			}

			show_json(1, array('url' => U('communityhead/usergroup', array('op' => 'display'))));
		}

		$this->id = $id;
		$this->group = $group;

		$this->display();
	}

	//------end-------

	/**
	 * 禁用状态切换
	 */
	public function enable_check()
	{

		$id = I('request.id');

		if (empty($id)) {
			$ids = I('request.ids');

			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}

		$comsiss_state = I('request.enable');

		$members = M('eaterplanet_community_head')->field('id,member_id,enable')->where( array('id' =>array('in', $id)) )->select();

		$time = time();

		foreach ($members as $member) {
			if ($member['enable'] === $comsiss_state) {
				continue;
			}

			if ($comsiss_state == 1) {

				M('eaterplanet_community_head')->where( array('id' => $member['id']) )->save( array('enable' => 1) );
			}
			else {
				M('eaterplanet_community_head')->where( array('id' => $member['id']) )->save( array('enable' => 0) );
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function distributionorder()
	{

		$gpc = I('request.');

		$starttime = isset($gpc['time']['start']) ? strtotime($gpc['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($gpc['time']['end']) ? strtotime($gpc['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));

		$this->starttime = $starttime;
		$this->endtime = $endtime;

		$this->time = $gpc['time'];


		$order_status_arr = D('Seller/Order')->get_order_status_name();
		$_GET['is_community'] = 1;//分销订单

		$this->is_community = 1;

		//$_GPC['type'] = 'community';

		$cur_controller = 'communityhead/distributionorder';

		$this->cur_controller = $cur_controller;

		$need_data = D('Seller/Order')->load_order_list();


		$total = $need_data['total'];
		$total_money = $need_data['total_money'];


		$list = $need_data['list'];
		$pager = $need_data['pager'];
		$all_count = $need_data['all_count'];
		$count_status_1 = $need_data['count_status_1'];
		$count_status_3 = $need_data['count_status_3'];
		$count_status_4 = $need_data['count_status_4'];
		$count_status_5 = $need_data['count_status_5'];
		$count_status_7 = $need_data['count_status_7'];
		$count_status_11 = $need_data['count_status_11'];
		$count_status_14 = $need_data['count_status_14'];

		$this->total = $total;
		$this->total_money = $total_money;
		$this->list = $list;
		$this->pager = $pager;
		$this->all_count = $all_count;
		$this->count_status_1 = $count_status_1;
		$this->count_status_3 = $count_status_3;
		$this->count_status_4 = $count_status_4;
		$this->count_status_5 = $count_status_5;
		$this->count_status_7 = $count_status_7;
		$this->count_status_11 = $count_status_11;
		$this->count_status_14 = $count_status_14;

		$this->headid = I('get.headid');

		$this->order_status_id = I('get.order_status_id');

		$this->display('Order/index');
	}

	//---begin
	public function deletecommunitymember()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);


		$apply_info = M('eaterplanet_ecommerce_community_pickup_member')->where( array('id' => $id ) )->find();

		M('eaterplanet_ecommerce_member')->where( array('member_id' => $apply_info['member_id']) )->save( array('pickup_id' => 0 ) );

		M('eaterplanet_ecommerce_community_pickup_member')->where( array('id' => $id ) )->delete();

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function agent_check_communitymember()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);
		$state = intval($_GPC['state']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}


		$apply_list = M('eaterplanet_ecommerce_community_pickup_member')->where( 'id in( ' . $id . ' )' )->select();


		foreach ($apply_list as $apply) {

			M('eaterplanet_ecommerce_community_pickup_member')->where( array('id' => $apply['id'])  )->save( array('state' => $state ) );

		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}


	//---end
	public function communityhead()
	{

		$_GPC = I('request.');

		$this->gpc = $_GPC;

		$condition = '  ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( m.username like "%'.$_GPC['keyword'].'%" or ch.community_name like "%'.$_GPC['keyword'].'%" or ch.head_name like "%'.$_GPC['keyword'].'%" or ch.head_mobile like "%'.$_GPC['keyword'].'%" or ch.address like "%'.$_GPC['keyword'].'%" ) ';

		}

		if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);

			$this->starttime = $starttime;
			$this->endtime = $endtime;

			$condition .= ' AND ch.apptime >= '.$starttime.' AND ch.apptime <= '.$endtime.' ';

		}

		if ($_GPC['comsiss_state'] != '') {
			$condition .= ' and ch.state=' . intval($_GPC['comsiss_state']);
		}


		if( $_GPC['level_id'] != '' )
		{
			$condition .= ' and ch.level_id=' . intval($_GPC['level_id']);
		}

		if( $_GPC['group_id'] != '' )
		{
			$condition .= ' and ch.groupid=' . intval($_GPC['group_id']);
		}



		$sql = 'SELECT ch.*,m.we_openid,m.username,m.avatar FROM ' . C('DB_PREFIX') . "eaterplanet_community_head as ch left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id
						WHERE  1 " . $condition . ' order by ch.id desc  ';

		if (empty($_GPC['export'])) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}


		$list = M()->query($sql);

		$sql_count = 'SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_community_head as ch  left join  '.C('DB_PREFIX').'eaterplanet_ecommerce_member as m  on ch.member_id = m.member_id
						WHERE  1 ' . $condition;

		$total_arr = M()->query($sql_count);

		$total = $total_arr[0]['count'];


		$all_sell_count = M('eaterplanet_ecommerce_goods')->where( array('is_all_sale' => 1) )->count();

		//---------等级


		$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();


		$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');
		$default_comunity_money = D('Home/Front')->get_config_by_name('default_comunity_money');

		$list_default = array(
			array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);

		$community_head_level = array_merge($list_default, $community_head_level);

		$level_id_to_name = array();

		foreach($community_head_level as $kk => $vv)
		{
			$level_id_to_name[$vv['id']] = $vv['levelname'];
		}
		//---------等级

		//---------分组

		$community_head_group = M('eaterplanet_community_head_group')->order('id asc')->select();

		$list_default_group = array(
			array('id' => '0','groupname' => '默认等级',)
		);

		$community_head_group = array_merge($list_default_group, $community_head_group);

		$group_id_to_name = array();

		foreach($community_head_group as $kk => $vv)
		{
			$group_id_to_name[$vv['id']] = $vv['groupname'];
		}
		//---------分组

		$this->group_id_to_name = $group_id_to_name;
		$this->list_default_group = $list_default_group;
		$this->community_head_group = $community_head_group;

		$group_list = M('eaterplanet_community_head_group')->order('id asc')->select();

		foreach($group_list as $vv)
		{
			$keys_group[$vv['id']] = $vv['groupname'];
		}


		$this->group_list = $group_list;
		$this->keys_group = $keys_group;


		foreach( $list as $key => $val )
		{
			//commission_info
			$commission_info = M('eaterplanet_community_head_commiss')->where( array('head_id' => $val['id'],'member_id' => $val['member_id']) )->find();

			$commission_info['commission_total'] = $commission_info['money']+ $commission_info['dongmoney'] + $commission_info['getmoney'];

			//预计佣金

			$pre_total_money = M('eaterplanet_community_head_commiss_order')->where( array('state' =>0, 'head_id' => $val['id']) )->sum('money');

			if( empty($pre_total_money) )
			{
				$pre_total_money = 0;
			}
			$val['groupname'] = empty($val['groupid']) ? '默认分组':$keys_group[$val['groupid']];



			$commission_info['pre_total_money'] = $pre_total_money;


			$commission_info['commission_total'] = $commission_info['money']+ $commission_info['dongmoney'] + $commission_info['getmoney'] +$pre_total_money;


			$val['commission_info'] = $commission_info;
			//普通等级

			$val['agent_name'] = '';
			if( !empty($val['agent_id']) && $val['agent_id'] > 0 )
			{
				$parent_community_head = M('eaterplanet_community_head')->field('head_name')->where( array('id' => $val['agent_id'] ) )->find();
				$val['agent_name'] = $parent_community_head['head_name'];
			}

			$val['province_name'] = D('Seller/Area')->get_area_info($val['province_id']);
			$val['city_name'] = D('Seller/Area')->get_area_info($val['city_id']);
			$val['area_name'] = D('Seller/Area')->get_area_info($val['area_id']);
			$val['country_name'] = D('Seller/Area')->get_area_info($val['country_id']);


			//团长商品

			$head_goods_count_arr = M()->query("select count(hg.id) as count from ".C('DB_PREFIX')."eaterplanet_community_head_goods as hg ,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc ,".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g
								where hg.goods_id = gc.goods_id and gc.goods_id = g.id and g.is_all_sale=0 and hg.head_id =".$val['id']  );

			$val['head_goods_count'] = $head_goods_count_arr[0]['count'];

			//所有团长可售商品
			$val['all_sell_count'] = $all_sell_count;

			//总商品数
			$val['goods_count'] =$val['head_goods_count'] + $val['all_sell_count'] ;


			//$val['member_info'] = $member_info;

			$member_count_arr = M()->query("SELECT count(DISTINCT(member_id) ) as count  FROM ".
									C('DB_PREFIX')."eaterplanet_community_history WHERE head_id =".$val['id']);

			$val['member_count'] =	$member_count_arr[0]['count'];

			$val['agent_count'] = M('eaterplanet_community_head')->where( array('agent_id' => $val['id'] ) )->count();

			$list[$key] = $val;
		}
		if ($_GPC['export'] == '1') {

			foreach ($list as &$row) {

			    //$row['username'] = $val['member_info']['username'];
			    //$row['we_openid'] = $val['member_info']['we_openid'];
			   $row['commission_total'] = $row['commission_info']['commission_total'];
			    $row['getmoney'] = $row['commission_info']['getmoney'];
			    $row['fulladdress'] = $row['province_name'].$row['city_name'].$row['area_name'].$row['country_name'].$row['address'];
			    $row['addtime'] = date('Y-m-d H:i:s', $row['addtime']);
			    $row['apptime'] = date('Y-m-d H:i:s', $row['apptime']);

			    $row['state'] = $row['state'] == 1 ? '已审核':'未审核';

			}

			unset($row);

			$columns = array(
				array('title' => 'ID', 'field' => 'member_id', 'width' => 12),
				array('title' => '微信用户名', 'field' => 'username', 'width' => 12),
			    array('title' => '团长名称', 'field' => 'head_name', 'width' => 12),
				array('title' => '联系方式', 'field' => 'head_mobile', 'width' => 12),
				array('title' => '在售商品数量', 'field' => 'goods_count', 'width' => 24),
				array('title' => 'openid', 'field' => 'we_openid', 'width' => 24),
				array('title' => '累计佣金', 'field' => 'commission_total', 'width' => 12),
				array('title' => '打款佣金', 'field' => 'getmoney', 'width' => 12),
			    array('title' => '省', 'field' => 'province_name', 'width' => 12),
			    array('title' => '市', 'field' => 'city_name', 'width' => 12),
			    array('title' => '区', 'field' => 'area_name', 'width' => 12),
			    array('title' => '街道/镇', 'field' => 'country_name', 'width' => 12),
			    array('title' => '提货地址', 'field' => 'address', 'width' => 24),
			    array('title' => '完整提货地址', 'field' => 'fulladdress', 'width' => 24),

				array('title' => '注册时间', 'field' => 'addtime', 'width' => 12),
				array('title' => '成为团长时间', 'field' => 'apptime', 'width' => 12),
				array('title' => '审核状态', 'field' => 'state', 'width' => 12)
			);

			load_model_class('excel')->export($list, array('title' => '团长数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));

		}

		$pager = pagination2($total, $pindex, $psize);


		$this->pager = $pager;
		$this->list = $list;


		$open_danhead_model = D('Home/Front')->get_config_by_name('open_danhead_model');

		if( empty($open_danhead_model) )
		{
			$open_danhead_model = 0;
		}

		$this->open_danhead_model = $open_danhead_model;

		$this->display('communityhead/communityhead');
	}


	public function changelevel()
	{
		$_GPC = I('request.');

		$level = $_GPC['level'];
		$ids_arr = $_GPC['ids'];
		$toggle = $_GPC['toggle'];

		$ids = implode(',', $ids_arr);



		if($toggle == 'group')
		{
			M('eaterplanet_community_head')->where( "id in ({$ids})" )->save( array('groupid' => $level) );
		}else if($toggle == 'level'){
			M('eaterplanet_community_head')->where("id in ({$ids})")->save( array('level_id' => $level ) );
		}


		show_json(1);
	}
	//--begin

	public function deletehead()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('eaterplanet_community_head')->field('id')->where( 'id in( ' . $id . ' )' )->select();

		foreach ($items as $item ) {
			M('eaterplanet_community_head')->where( array('id' => $item['id'] ) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}



	//look_piup_record

	public function look_piup_record()
	{
		$_GPC = I('request.');

		$member_id = $_GPC['member_id'];
		$keyword = trim($_GPC['keyword']);

		$condition = ' member_id = '.$member_id;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if( !empty($keyword) )
		{

		}

		$sql = 'SELECT * FROM ' . C('DB_PREFIX') ."eaterplanet_ecommerce_community_pickup_member_record
						WHERE  " . $condition . ' order by id desc  ';

		$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;

		$list = M()->query($sql);


		$total = M('eaterplanet_ecommerce_community_pickup_member_record')->where( $condition )->count();

		$pager = pagination2($total, $pindex, $psize);


		$this->list = $list;
		$this->pager = $pager;
		$this->gpc = $_GPC;

		$this->display();

	}

	public function lookcommunitymember()
	{
		$_GPC = I('request.');

		//id=272
		$community_id = $_GPC['id'];
		$keyword = trim($_GPC['keyword']);


		$condition = ' and pm.community_id= '.$community_id;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if( !empty($keyword) )
		{
			$condition .= " and m.username like '%".$keyword."%' ";
		}

		$sql = 'SELECT pm.*, m.username FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_community_pickup_member as pm ,  ".
						 C('DB_PREFIX')."eaterplanet_ecommerce_member as m
						WHERE pm.member_id = m.member_id  " . $condition . ' order by pm.id desc  ';

		$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;



		$list = M()->query($sql);

		foreach($list as $key => $val)
		{

			$he_count = M('eaterplanet_ecommerce_community_pickup_member_record')->where( array('member_id' => $val['member_id'] ) )->count();

			$val['he_count'] = $he_count;
			$list[$key] = $val;
		}


		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_community_pickup_member as pm , '.
					C('DB_PREFIX').'eaterplanet_ecommerce_member as m  WHERE pm.member_id = m.member_id  ' . $condition);

		$total = $total_arr[0]['count'];

		$pager = pagination2($total, $pindex, $psize);

		$this->community_id = $community_id;
		$this->keyword = $keyword;
		$this->list = $list;
		$this->pager = $pager;
		$this->gpc = $_GPC;


		$this->display();
	}

	public function addcommunitymember()
	{
		$_GPC = I('request.');

		$community_id = $_GPC['community_id'];

		//
		if (IS_POST) {

			$member_id = $_GPC['member_id'];

			$ins_data = array();
			$ins_data['community_id'] = $community_id;
			$ins_data['member_id'] = $member_id;
			$ins_data['state'] = 1;
			$ins_data['remark'] = '后台添加';
			$ins_data['addtime'] = time();

			$pickup_id = M('eaterplanet_ecommerce_community_pickup_member')->add( $ins_data );

			M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->save( array('pickup_id' => $pickup_id) );

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

		}

		$this->community_id = $community_id;
		$this->display();
	}

	//---end


	public function goodslist()
	{

		$head_id = I('request.head_id');

		$this->head_id = $head_id;
		$pindex = I('request.page', 1);
		$psize = 20;

		$params = array();

		$where = " 1=1  ";

		$all_sales_goods = M('eaterplanet_ecommerce_goods')->field('id')->where( array('is_all_sale' => 1) )->select();


		$all_goods_ids = array();
		if( !empty($all_sales_goods) )
		{
			foreach($all_sales_goods as $val)
			{
				$all_goods_ids[]  = $val['id'];
			}
		}
		$this->all_goods_ids = $all_goods_ids;

		$ch_goods_list = M('eaterplanet_community_head_goods')->field('goods_id')->where( array('head_id' => $head_id) )->select();



		$ch_goods_arr = array();

		if( !empty($ch_goods_list) )
		{
			foreach($ch_goods_list as $val)
			{
				$ch_goods_arr[] = $val['goods_id'];
			}
		}

		$in_goods_ids  = array_merge($ch_goods_arr, $all_goods_ids);


		$keyword = I('request.keyword');
		$this->keyword = $keyword;

		if (!(empty($keyword))) {

			$where .= ' AND (g.`id` = "'.$keyword.'" or g.`goodsname` LIKE '.'"%' . $keyword . '%"'.' or g.`codes` LIKE '.'"%' . $keyword . '%"'.' )';


		}

		$cate = I('request.cate');

		$this->cate = $cate;

		if( !empty($cate) )
		{

			$cate_list = M('eaterplanet_ecommerce_goods_to_category')->field('goods_id')->where( array('cate_id' => $cate) )->select();

			$catids_arr = array();

			foreach($cate_list as $val)
			{
				if( in_array($val['goods_id'], $in_goods_ids) )
				{
					$catids_arr[] = $val['goods_id'];
				}
			}

			if( !empty($catids_arr) )
			{
				$catids_str = implode(',', $catids_arr);
				$where .= ' and g.id in ('.$catids_str.')';
			}else{
				$where .= " and 1=0 ";
			}

		}else{

			if( !empty($in_goods_ids) )
			{
				$catids_str = implode(',', $in_goods_ids);
				$where .= ' and g.id in ('.$catids_str.')';
			}else{
				$where .= " and 1=0 ";
			}
		}


		$sql = 'SELECT COUNT(g.id) as count FROM ' .C('DB_PREFIX') . 'eaterplanet_ecommerce_goods as g   '
				." where {$where}  " ;

		$total_arr = M()->query($sql);

		$total = $total_arr[0]['count'];

		//tablename('eaterplanet_community_head_goods')." as hg " //. tablename('eaterplanet_community_head_goods')." as hg "

		if (!(empty($total))) {

			$sql = 'SELECT g.* FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_goods as g ' .
			 " where {$where}  " . ' ORDER BY  g.`id` DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;

			$list = M()->query($sql);

			foreach ($list as $key => &$value ) {


				$thumb = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $value['id']) )->order('id asc')->find();

				$value['thumb'] =  $thumb['thumb'];

				$categorys = M('eaterplanet_ecommerce_goods_to_category')->where( array('goods_id' => $value['id']) )->order('id asc')->select();

				$value['cate'] = $categorys;

				$desc_info = D('Home/Front')->get_goods_common_field($value['id'] , 'community_head_commission');


				$price_arr = D('Home/Pingoods')->get_goods_price($value['id']);

				$value['price_arr'] = $price_arr;

				$value['community_head_commission'] = $desc_info['community_head_commission'];

			}
			$pager = pagination2($total, $pindex, $psize);
		}

		$categorys = D('Seller/GoodsCategory')->getFullCategory(true);
		$category = array();

		foreach ($categorys as $cate ) {
			$category[$cate['id']] = $cate;
		}

		$this->pager = $pager;
		$this->category = $category;
		$this->list = $list;
		$this->display();
	}

	public function down_sales()
	{
		$id = I('request.id', 0);

		if (empty($id)) {
			$ids = I('request.ids');
			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}

		$head_id = I('request.head_id', 0);

		if(!empty($id))
		{
			M('eaterplanet_community_head_goods')->where( array('head_id' => $head_id, 'goods_id' => array('in', $id)) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function config()
	{
		global $_W;
		global $_GPC;
		if (IS_POST) {
			$data = I('request.data');

			if($data['community_money_type'] ==0){

					if( intval($data['default_comunity_money']) < 0 || intval($data['default_comunity_money']) > 100){
							show_json(0, array('message' => '团长提成比例应为0~100之间'));
					}
			}

			if( !empty($data) && $data['open_community_head_leve'] >= 1 ){

					if( intval($data['community_head_commiss1']) < 0 || intval($data['community_head_commiss1']) > 100){
							show_json(0, array('message' => '团长分销1级提成比例应为0~100之间'));
					}


			}

			if( !empty($data) && $data['open_community_head_leve'] >= 2 ){

					if( intval($data['community_head_commiss1']) < 0 || intval($data['community_head_commiss1']) > 100){
							show_json(0, array('message' => '团长分销1级提成比例应为0~100之间'));
					}
					if( intval($data['community_head_commiss2']) < 0 || intval($data['community_head_commiss2']) > 100){
							show_json(0, array('message' => '团长分销2级提成比例应为0~100之间'));
					}

			}

			if( !empty($data) && $data['open_community_head_leve'] >= 3 ){

					if( intval($data['community_head_commiss1']) < 0 || intval($data['community_head_commiss1']) > 100){
							show_json(0, array('message' => '团长分销1级提成比例应为0~100之间'));
					}
					if( intval($data['community_head_commiss2']) < 0 || intval($data['community_head_commiss2']) > 100){
							show_json(0, array('message' => '团长分销2级提成比例应为0~100之间'));
					}
					if( intval($data['community_head_commiss3']) < 0 || intval($data['community_head_commiss3']) > 100){
							show_json(0, array('message' => '团长分销3级提成比例应为0~100之间'));
					}

			}




			D('Seller/Config')->update($data);


			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
		}

		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;
		$this->display();
	}

	public function distributionpostal()
	{

		if (IS_POST) {

			$data = I('request.data');


			$data['head_commiss_tixianway_yuer'] = isset($data['head_commiss_tixianway_yuer']) ? $data['head_commiss_tixianway_yuer'] : 1;
			$data['head_commiss_tixianway_weixin'] = isset($data['head_commiss_tixianway_weixin']) ? $data['head_commiss_tixianway_weixin'] : 1;
			$data['head_commiss_tixianway_alipay'] = isset($data['head_commiss_tixianway_alipay']) ? $data['head_commiss_tixianway_alipay'] : 1;
			$data['head_commiss_tixianway_bank'] = isset($data['head_commiss_tixianway_bank']) ? $data['head_commiss_tixianway_bank'] : 1;


			D('Seller/Config')->update($data);


			show_json(1, array('url' => $_SERVER['HTTP_REFERER']) );
		}

		$data = D('Seller/Config')->get_all_config();
		$this->data = $data;
		$this->display();
	}


	/**
	 * 禁用状态切换
	 */
	public function rest_check()
	{
		$_GPC = I('request.');


		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$comsiss_state = intval($_GPC['rest']);

		$members = M('eaterplanet_community_head')->field('id,member_id,enable')->where(  'id in( ' . $id . ' )' )->select();

		$time = time();

		foreach ($members as $member) {
			if ($member['rest'] === $comsiss_state) { continue; }
			if ($comsiss_state == 1) {
				M('eaterplanet_community_head')->where(  array('id' => $member['id']) )->save( array('rest' => 1) );
			}
			else {
				M('eaterplanet_community_head')->where(  array('id' => $member['id']) )->save( array('rest' => 0) );
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function default_check()
	{
		$_GPC = I('request.');

		$open_danhead_model = D('Home/Front')->get_config_by_name('open_danhead_model');

		if( empty($open_danhead_model) )
		{
			$open_danhead_model = 0;
		}

		if( $open_danhead_model == 0 )
		{
			show_json(0, array('message' => '请先开启单团长模式') );
			die();
		}

		$community_model = D('Seller/Community');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$is_default = intval($_GPC['value']);

		$members = M('eaterplanet_community_head')->field('id,member_id,enable')->where( 'id in( ' . $id . ' )' )->select();

		$time = time();

		foreach ($members as $member) {
			if ($member['is_default'] === $is_default) { continue; }
			if ($is_default == 1) {

				M('eaterplanet_community_head')->where( 'id>0' )->save( array('is_default' => 0) );

				M('eaterplanet_community_head')->where( array('id' => $member['id'] ) )->save( array('is_default' => 1)  );

			}
			else {

				M('eaterplanet_community_head')->where( array('id' => $member['id']  )  )->save(  array('is_default' => 0) );
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}


	public function distribulist()
	{

		$condition = '  ';
		$pindex = I('request.page', 1);
		$psize = 20;

		$keyword = I('request.keyword');
		$this->keyword = $keyword;

		if (!empty($keyword)) {

            $condition.="and (id like '%{$keyword}%' or username like '%{$keyword}%' or realname like  '%{$keyword}%'  or bankaccount like  '%{$keyword}%')";
		}


		$time = I('request.time');
		$this->time = $time;

		$starttime = strtotime( date('Y-m-d').' 00:00:00' );
		$endtime = $starttime + 86400;

		$searchtime = I('request.searchtime', '');

		if( !empty($searchtime) )
		{
			if (!empty($time['start']) && !empty($time['end'])) {

				if (!empty($time['start']) && !empty($time['end'])) {
					$starttime = strtotime($time['start']);
					$endtime = strtotime($time['end']);

					$condition .= ' AND addtime >= '.$starttime.' AND addtime <= '.$endtime;
				}
			}
		}

		$this->starttime = $starttime;
		$this->endtime = $endtime;

		$comsiss_state = I('request.comsiss_state');
		$this->comsiss_state = $comsiss_state;
		if ($comsiss_state != '') {
			$condition .= ' and state=' . intval($comsiss_state);
		}

        $sql = 'SELECT * FROM ' . C('DB_PREFIX'). "eaterplanet_community_head_tixian_order o left join " .C('DB_PREFIX')."eaterplanet_ecommerce_member  m
        on o.member_id = m.member_id WHERE 1 " . $condition . ' order by id desc  ';

		$export = I('request.export', 0);

		if (empty($export)) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}

		$community_tixian_fee = D('Home/Front')->get_config_by_name('community_tixian_fee');


		$list = M()->query($sql);


        $total = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX').
            'eaterplanet_community_head_tixian_order o left join '.C('DB_PREFIX').'eaterplanet_ecommerce_member m on o.member_id = m.member_id WHERE 1 ' . $condition);


		foreach( $list as $key => $val )
		{
			$member_info = M('eaterplanet_ecommerce_member')->field('username,avatar,we_openid')->where( array('member_id' => $val['member_id']) )->find();

			//get_area_info($id=0)

			$service_charge = 0;
			if(!empty($community_tixian_fee) && $community_tixian_fee > 0)
			{
				$service_charge = round( ($val['money'] * $community_tixian_fee) /100,2);
			}

			if($val['service_charge'] <= 0)
			{
				$val['service_charge'] = $service_charge;
			}

			$val['community_head_commiss'] = M('eaterplanet_community_head_commiss')->where( array('head_id' =>$val['head_id'] ) )->find();

			$val['community_head_commiss']['commission_total'] = $val['community_head_commiss']['money']+$val['community_head_commiss']['getmoney']+$val['community_head_commiss']['dongmoney'];


			$val['community_head'] = M('eaterplanet_community_head')->where( array('id' => $val['head_id'] ) )->find();



			$val['member_info'] = $member_info;


			$list[$key] = $val;
		}

		$this->list = $list;

		if ($export == '1') {

			foreach($list as $key =>&$row)
			{
				$row['community_name'] = $row['community_head']['community_name'];
				$row['head_name'] = $row['community_head']['head_name'];
				$row['head_mobile'] = $row['community_head']['head_mobile'];
				//$row['bankname'] = $row['community_head_commiss']['bankname'];
				//$row['bankaccount'] = $row['community_head_commiss']['bankaccount']."\t";
				//$row['bankusername'] = $row['community_head_commiss']['bankusername'];

				if($row['type'] > 0){

					if( $row['type'] == 1 ){
							$row['bankname'] = "客户余额";
					}else if($row['type'] == 2){
							$row['bankname'] = "微信零钱";

							$row['bankusername'] = $row['bankusername'];
					 }else if($row['type'] == 3){
							$row['bankname'] = "支付宝";
							$row['bankusername'] = $row['bankusername'];
							$row['bankaccount'] = "\t".$row['bankaccount'];
					}else if($row['type'] == 4){
							$row['bankname'] = $row['bankname'];
							$row['bankusername'] = $row['bankusername'];
							$row['bankaccount'] = "\t".$row['bankaccount'];
					}
				}else{
						$row['bankname'] = $row['bankname'];
						$row['bankusername'] = $row['bankusername'];
						$row['bankaccount'] = "\t".$row['bankaccount'];
				}

				$row['get_money'] = $row['money']-$row['service_charge'];
				$row['addtime'] = date('Y-m-d H:i:s', $row['addtime']);
				if(!empty($row['shentime']))
				{
					$row['shentime'] = date('Y-m-d H:i:s', $row['shentime']);
				}

				if($row['state'] ==0)
				{
					$row['state'] = '待审核';
				}else if($row[state] ==1)
				{
					$row['state'] = '已审核，打款';
				}else if($row[state] ==2){
					$row['state'] = '已拒绝';
				}
			}
			unset($row);

			$columns = array(
				array('title' => 'ID', 'field' => 'id', 'width' => 12),
				array('title' => '小区名称', 'field' => 'community_name', 'width' => 12),
			    array('title' => '团长名称', 'field' => 'head_name', 'width' => 12),
				array('title' => '联系方式', 'field' => 'head_mobile', 'width' => 12),
				array('title' => '打款银行', 'field' => 'bankname', 'width' => 24),
				array('title' => '打款账户', 'field' => 'bankaccount', 'width' => 24),
				array('title' => '真实姓名', 'field' => 'bankusername', 'width' => 24),
				array('title' => '申请提现金额', 'field' => 'money', 'width' => 24),
				array('title' => '手续费', 'field' => 'service_charge', 'width' => 24),
				array('title' => '到账金额', 'field' => 'get_money', 'width' => 24),
				array('title' => '申请时间', 'field' => 'addtime', 'width' => 24),
				array('title' => '审核时间', 'field' => 'shentime', 'width' => 24),
				array('title' => '状态', 'field' => 'state', 'width' => 24)
			);

			D('Seller/Excel')->export($list, array('title' => '团长提现数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns) );

		}

		$pager = pagination2($total[0]['count'], $pindex, $psize);

		$this->pager = $pager;

		$this->display();
	}


	public function agent_check_apply()
	{


		$community_model = D('Seller/Community');


		$id = I('request.id');


		if (empty($id)) {
			$ids = I('request.ids');
			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}

		$comsiss_state = I('request.state');


		$apply_list = M('eaterplanet_community_head_tixian_order')->where( array('id'=> array('in', $id) ) )->select();

		$time = time();


		$community_tixian_fee = D('Seller/Front')->get_config_by_name('community_tixian_fee');

		$open_weixin_qiye_pay = D('Home/Front')->get_config_by_name('open_weixin_qiye_pay');


		$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';

		require_once $lib_path."/Weixin/lib/WxPay.Api.php";



		foreach ($apply_list as $apply) {
			if ($apply['state'] == $comsiss_state || $apply['state'] == 1 || $apply['state'] == 2) {
				continue;
			}
			$money = $apply['money'];
			$head_id = $apply['head_id'];

			if ($comsiss_state == 1) {

				$service_charge = 0;

				if(!empty($community_tixian_fee) && $community_tixian_fee > 0)
				{
					$service_charge = round( ($money * $community_tixian_fee) /100,2);
				}

				if( $apply['service_charge'] > 0 )
				{
					$service_charge = $apply['service_charge'];
				}

				if( $apply['type'] > 0 )
				{
					if( $apply['type'] == 1 )
					{
						//到客户余额
						$del_money = $money-$service_charge;
						if( $del_money >0 )
						{
							D('Admin/Member')->sendMemberMoneyChange($apply['member_id'], $del_money, 10, '团长提现到余额,提现id:'.$apply['id']);
						}

					}else if( $apply['type'] == 2 ){
						//到微信零钱
						//member_id

						$commiss_head_info = M('eaterplanet_community_head_commiss')->where( array('head_id' => $head_id,'member_id' =>$apply['member_id'] ) )->find();

						//bankname
						if( !empty($open_weixin_qiye_pay) && $open_weixin_qiye_pay == 1 )
						{


								$mb_info = M('eaterplanet_ecommerce_member')->field('we_openid')->where( array('member_id' =>$apply['member_id'] ) )->find();

								$partner_trade_no = build_order_no($apply['id']);
								$desc = date('Y-m-d H:i:s', $apply['addtime']).'申请的提现已到账';
								$username = $apply['bankusername'];
								$amount = ($money-$service_charge) * 100;

								$openid = $mb_info['we_openid'];


								$res = \WxPayApi::payToUser($openid,$amount,$username,$desc,$partner_trade_no,$_W['uniacid']);

								if(empty($res) || $res['result_code'] =='FAIL')
								{
									show_json(0, array('msg' => $res['err_code_des']) );
								}

						}else{
							show_json(0, array('msg' => '请开通微信零钱企业支付') );
						}
					}


				}else{
					//member_id
					$commiss_head_info = M('eaterplanet_community_head_commiss')->where( array('member_id' => $apply['member_id'],'head_id' => $head_id ) )->find();

					//bankname
					if( !empty($open_weixin_qiye_pay) && $open_weixin_qiye_pay == 1 )
					{
						if( strpos($commiss_head_info['bankname'], '微信') !== false )
						{
							$mb_info = M('eaterplanet_ecommerce_member')->field('we_openid')->where( array('member_id' => $apply['member_id'] ) )->find();

							$partner_trade_no = build_order_no($apply['id']);
							$desc = date('Y-m-d H:i:s', $apply['addtime']).'申请的提现已到账';
							$username = $commiss_head_info['bankusername'];
							$amount = ($money-$service_charge) * 100;

							$openid = $mb_info['we_openid'];

							$res = \WxPayApi::payToUser($openid,$amount,$username,$desc,$partner_trade_no,$_W['uniacid']);

							if(empty($res) || $res['result_code'] =='FAIL')
							{
								show_json(0,  array('msg' => $res['err_code_des']) );
							}
						}
					}

				}

				M('eaterplanet_community_head_tixian_order')->where( array('id' => $apply['id']) )->save(  array('state' => 1,'service_charge' => $service_charge, 'shentime' => $time));

				//将冻结的钱划一部分到已提现的里面
				M()->execute("update ".C('DB_PREFIX')."eaterplanet_community_head_commiss set getmoney=getmoney+{$money},dongmoney=dongmoney-{$money}
							where  head_id={$head_id} ");

				//检测是否存在账户，没有就新建
				//TODO....sendmsg  发送成为佣金提现成功
				$community_model->send_apply_success_msg($apply['id']);
			}
			else if ($comsiss_state == 2) {

				M('eaterplanet_community_head_tixian_order')->where( array('id' => $apply['id']) )->save( array('state' => 2, 'shentime' => $time) );

				//退回冻结的货款
				M()->execute("update ".C('DB_PREFIX')."eaterplanet_community_head_commiss set money=money+{$money},dongmoney=dongmoney-{$money}
							where  head_id={$head_id} ");
			}
			else {
				M('eaterplanet_community_head_tixian_order')->where( array('id' => $member['id']) )->save( array('state' => 0, 'shentime' => 0) );
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}


	//begin .....
	public function addlevel()
	{
		$this->modifylevel();
	}

	public function editlevel()
	{
		$this->modifylevel();
	}

	private function modifylevel()
	{
		$_GPC = I('request.');

		$id = trim($_GPC['id']);

		$set =  D('Seller/Config')->get_all_config();

		if ($id == 'default') {
			$level = array('id' => 'default', 'levelname' => empty($set['head_commission_levelname']) ? '默认等级' : $set['head_commission_levelname'], 'commission' => $set['default_comunity_money'] );
			$has_notice = 1;
		}
		else {

			$level = M('eaterplanet_ecommerce_community_head_level')->where( array('id' => intval($id) ) )->find();

			$has_notice = $set['open_community_head_has_notice'];
			if(empty($has_notice))
			{
				$has_notice = 0;
			}
		}

		if (IS_POST) {

			$data = array(
				'levelname' => trim($_GPC['levelname']),
				'commission' => trim(trim($_GPC['commission']), '%')
			);

			$data['auto_upgrade'] = $_GPC['auto_upgrade'];
			$data['condition_type'] = $_GPC['condition_type'];
			$data['condition_one'] = $_GPC['condition_one'];
			$data['condition_two'] = $_GPC['condition_two'];
			$data['condition_order_total'] = $_GPC['condition_order_total'];

			$community_money_type = $set['community_money_type'];

			if($community_money_type ==0){
				if( intval($data['commission']) < 0 || intval($data['commission']) > 100){
					show_json(0, array('message' => '团长提成比例应为0~100之间'));
				}

			}

			if($id != 'default')
			{
				if( isset($_GPC['auto_upgrade']) && $_GPC['auto_upgrade'] == 1 )
				{
					if($data['condition_type'] == 0)
					{
						if( empty($data['condition_one']) || $data['condition_one'] <=0 )
						{

							show_json(0,  array('msg' => '订单总金额不能为空' ) );
						}
					}else if( $data['condition_type'] == 1 )
					{
						if( empty($data['condition_two']) || $data['condition_two'] <=0 )
						{
							show_json(0,  array('msg' => '累计社区用户不能为空' ) );
						}
					}
				}
			}



			D('Seller/Config')->update( array('open_community_head_has_notice' => 1) );


			if (!empty($id)) {
				if ($id == 'default') {

					$set_data = array();
					$set_data['head_commission_levelname'] = $data['levelname'];
					$set_data['default_comunity_money'] = $data['commission'];

					D('Seller/Config')->update($set_data);
				}
				else {


					M('eaterplanet_ecommerce_community_head_level')->where( array('id' => $id) )->save( $data );
				}
			}
			else {
				$id = M('eaterplanet_ecommerce_community_head_level')->add( $data );
			}

			show_json(1, array('url' => U('Communityhead/headlevel')));
		}


		//此操作将启用等级全局提成，原商品比例失效，可到商品编辑“等级/分销”单独设置

		$open_community_head_leve = $set['open_community_head_leve'];

		if( empty($open_community_head_leve) )
		{
			$open_community_head_leve = 0;
		}
		$community_money_type = $set['community_money_type'];

		$this->community_money_type = $community_money_type;
		$this->level = $level;
		$this->open_community_head_leve = $open_community_head_leve;

		$this->display('Communityhead/modifylevel');
	}

	public function headlevel()
	{
		$_GPC = I('request.');

		$set = D('Seller/Config')->get_all_config();

		//open_community_head_leve
		$list = array(
			array('id' => 'default','level'=>0,'levelname' => empty($set['head_commission_levelname']) ? '默认等级' : $set['head_commission_levelname'], 'commission' => $set['default_comunity_money'], )
		);

		$condition = ' ';
		$params = array();

		if (!(empty($_GPC['keyword']))) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( levelname like :levelname)';
			$params[':levelname'] = '%' . $_GPC['keyword'] . '%';
		}

		$alllist = M('eaterplanet_ecommerce_community_head_level')->where( "1 ".$condition) ->order('id asc')->select();

		foreach ($alllist as &$row ) {
			//$row['membercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('eaterplanet_ecommerce_member') . ' where uniacid=:uniacid and find_in_set(:level_id,level_id) limit 1', array(':uniacid' => $_W['uniacid'], ':level_id' => $row['id']));
		}

		unset($row);
		if( !empty($alllist) )
		{
			if (empty($_GPC['keyword'])) {
				$list = array_merge($list, $alllist);
			}
			 else {
				$list = $alllist;
			}
		}

		$this->list = $list;

		$open_community_head_leve = $set['open_community_head_leve'];
		$community_money_type = $set['community_money_type'];
		$this->community_money_type = $community_money_type;

		if( empty($open_community_head_leve) )
		{
			$open_community_head_leve = 0;
		}

		$this->open_community_head_leve = $open_community_head_leve;

		$this->gpc = $_GPC;

		$this->display();
	}

	//----------- begin

	public function query_head_user_agent()
	{
		$_GPC = I('request.');


		$kwd = trim($_GPC['keyword']);

		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;

		$condition = ' ';

		if (!empty($kwd)) {

			$condition .= ' and ( m.username LIKE "%'.$kwd.'%" or m.telephone like "%'.$kwd.'%" )';

		}

		/**
			分页开始
		**/
		$page =  isset($_GPC['page']) ? intval($_GPC['page']) : 1;
		$page = max(1, $page);
		$page_size = 10;
		/**
			分页结束
		**/

		$ds = M()->query('SELECT m.*,ch.id  as head_id FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member as m,'.C('DB_PREFIX').'eaterplanet_community_head as ch WHERE m.member_id=ch.member_id ' . $condition .
				' order by m.member_id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size );

		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') .
				'eaterplanet_ecommerce_member as m, '.C('DB_PREFIX').'eaterplanet_community_head as ch WHERE m.member_id=ch.member_id ' . $condition );

		$total = $total_arr[0]['count'];

		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);

			$value['id'] = $value['id'];

			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" class="img-responsive img-thumbnail m-r-20" style="width:40px;height:40px;" />'. $value['nickname'].'</td>';

				$ret_html .= '	<td>'.$value['mobile'].'</td>';


				$ret_html .= '	<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link btn-primary btn-sm" data-json=\''.json_encode($value).'\'>选择</a></td>';

				$ret_html .= '</tr>';

			}
		}

		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));

		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
			die();
		}


		unset($value);

		if ($_GPC['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}

		$this->ds = $ds;
		$this->pager = $pager;

		$this->display('Communityhead/query_head_user_agent');
	}

	//--- end
	public function query_head_user()
	{
		$_GPC = I('request.');
		$kwd = trim($_GPC['keyword']);

		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;

		$params = array();

		$condition = '  ';

		if (!empty($kwd)) {
			$condition .= ' and ( m.username LIKE "%'.$kwd .'%" or m.telephone like "%'.$kwd .'%" )';

		}

		/**
			分页开始
		**/
		$page =  isset($_GPC['page']) ? intval($_GPC['page']) : 1;
		$page = max(1, $page);
		$page_size = 10;
		/**
			分页结束
		**/

		$ds = M()->query('SELECT m.*,ch.id as head_id FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_member as m,'.C('DB_PREFIX').'eaterplanet_community_head as ch WHERE m.member_id=ch.member_id '
				. $condition .
				' order by m.member_id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size );

		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') .
			'eaterplanet_ecommerce_member as m, '.C('DB_PREFIX').'eaterplanet_community_head as ch WHERE m.member_id=ch.member_id ' . $condition );

		$total = $total_arr[0]['count'];

		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);

			$value['id'] = $value['member_id'];

			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" class="img-responsive img-thumbnail m-r-20" style="width:40px;height:40px;" />'. $value['nickname'].'</td>';

				$ret_html .= '	<td>'.$value['mobile'].'</td>';


				$ret_html .= '	<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link btn-primary btn-sm" data-json=\''.json_encode($value).'\'>选择</a></td>';

				$ret_html .= '</tr>';

			}
		}

		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));

		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
			die();
		}


		unset($value);

		if ($_GPC['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}

		$this->display();
	}



	public function deletelevel()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}


		$items = M()->query('SELECT id FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_community_head_level WHERE id in( ' . $id . ' ) ' );

		foreach ($items as $item ) {

			M('eaterplanet_ecommerce_community_head_level')->where( array('id' => $item['id']) )->delete();

			M('eaterplanet_community_head')->where( array('level_id' => $item['id']) )->save( array('level_id' => 0 ) );

		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	//end ....

	public function communityorder()
	{
		$_GPC = I('request.');

		$head_id = $_GPC['head_id'];


		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$where = " and co.head_id = {$head_id} ";

		$starttime = strtotime( date('Y-m-d')." 00:00:00" );
		$endtime = $starttime + 86400;


		if( isset($_GPC['searchtime']) && $_GPC['searchtime'] == 'create_time' )
		{
			if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
				$starttime = strtotime($_GPC['time']['start']);
				$endtime = strtotime($_GPC['time']['end']);

				$where .= ' AND co.addtime >= '.$starttime.' AND co.addtime <= '.$endtime ;

			}
		}

		$this->starttime = $starttime;
		$this->endtime = $endtime;

		/*
		$order_status = isset($_GPC['order_status']) ? $_GPC['order_status'] : -1;


		if($order_status == 1)
		{
			$where .= " and co.state = 1 ";
		} else if($order_status == 2){
			$where .= " and co.state = 2 ";
		} else if($order_status == 0){
			$where .= " and co.state = 0 ";
		}
		*/
		if ($_GPC['order_status'] != '') {
			$where .= ' and co.state=' . intval($_GPC['order_status']);
		}

		/*$sql = "select co.order_id,co.state,co.money,co.type,co.addtime ,og.total,og.name,og.total,og.is_refund_state
				from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
	                    where  co.order_goods_id = og.order_goods_id {$where}
	                      order by co.id desc ".' limit ' . (($pindex - 1) * $psize) . ',' . $psize;*/
		$sql = "select co.order_id,co.state,co.money,co.type,co.addtime ,og.total,og.name,og.total,og.is_refund_state
				from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co left join
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og on co.order_goods_id = og.order_goods_id
	                    where 1=1  {$where}
	                      order by co.id desc ".' limit ' . (($pindex - 1) * $psize) . ',' . $psize;


		$list = M()->query($sql);

		if( !empty($list) )
		{
			foreach($list as $key => $val)
			{
				$val['total'] = sprintf("%.2f",$val['total']);
				$val['money'] = sprintf("%.2f",$val['money']);

				$val['addtime'] = date('Y-m-d H:i:s',$val['addtime']);

				$order_info= M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $val['order_id'] ) )->find();

				$val['order_num_alias'] = $order_info['order_num_alias'];
				$list[$key] = $val;
			}
		}

		/*$sql_count = "select count(1) as count
				from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
	                    where   co.order_goods_id = og.order_goods_id {$where}  ";*/

		$sql_count = "select count(1) as count
				from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co left join
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og  on co.order_goods_id = og.order_goods_id
	                    where 1=1 {$where}  ";

		$total_arr = M()->query($sql_count );
		$total = $total_arr[0]['count'];


		if ( isset($_GPC['export']) && $_GPC['export'] == '1') {

			$export_sql = "select co.order_id,co.state,co.money,co.addtime ,og.total,og.name,og.total,og.is_refund_state
				from ".C('DB_PREFIX')."eaterplanet_community_head_commiss_order as co ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
	                    where   co.order_goods_id = og.order_goods_id {$where}
	                      order by co.id desc ";

			$export_list = M()->query($export_sql);

			if( !empty($export_list) )
			{
				foreach($export_list as $key => $val)
				{
					$val['total'] = sprintf("%.2f",$val['total']);
					$val['money'] = sprintf("%.2f",$val['money']);

					$val['addtime'] = date('Y-m-d H:i:s',$val['addtime']);

					$order_info= M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $val['order_id'] ) )->find();

					$val['order_num_alias'] = $order_info['order_num_alias'];
					$export_list[$key] = $val;
				}
			}



			foreach($export_list as $key =>&$row)
			{
				$row['order_num_alias'] =  "\t".$row['order_num_alias'];
				$row['name'] = $row['name'];
				$row['total'] = $row['total'];
				$row['money'] = $row['money'];

				if($row['state'] == 0)
				{

					if($row['is_refund_state'] == 1 ){
						$row['state'] = '已失效';
					}else{
						$row['state'] = '待结算';
					}
				}else if($row['state'] == 1)
				{
					$row['state'] = '已结算';
				}else if($row['state'] == 2){
					$row['state'] = '订单取消或退款';
				}

				$row['addtime'] =  $row['addtime'];

			}

			unset($row);

			$columns = array(
				array('title' => '订单编号', 'field' => 'order_num_alias', 'width' => 24),
			    array('title' => '商品标题', 'field' => 'name', 'width' => 24),
				array('title' => '订单金额', 'field' => 'total', 'width' => 12),
				array('title' => '佣金金额', 'field' => 'money', 'width' => 12),
				array('title' => '状态', 'field' => 'state', 'width' => 24),
				array('title' => '下单时间', 'field' => 'addtime', 'width' => 24),
			);

			D('Seller/Excel')->export($export_list, array('title' => '收益明细-' . date('Y-m-d-H-i', time()), 'columns' => $columns));

		}



		$pager = pagination2($total, $pindex, $psize);

		$this->head_id = $head_id;
		$this->_GPC = $_GPC;
		$this->list = $list;
		$this->pager = $pager;

		include $this->display();
	}

	public function query_head()
	{

		$province_name = I('request.province_name');
		$city_name = I('request.city_name');
		$area_name = I('request.area_name');
		$country_name = I('request.country_name');
		$keyword = I('request.keyword');

		//page
		/**
			分页开始
		**/
		$page =  I('request.page',1,'intval');
		$page = max(1, $page);
		$page_size = 10;
		/**
			分页结束
		**/


		//ims_eaterplanet_community_head
		$param = array(':uniacid' => $_W['uniacid']);

		$where = " 1=1 ";

		$province_id =0;
		if( $province_name != '请选择省份' )
		{
			$province_id = D('Seller/Area')->get_area_id_by_name($province_name);
			$where .= " and province_id={$province_id} ";
		}

		$city_id = 0;
		if( $city_name != '请选择城市' )
		{

			$city_id = D('Seller/Area')->get_area_id_by_name($city_name,$province_id);
			$where .= " and city_id={$city_id} ";
		}

		if( $area_name != '请选择区域' )
		{
			$area_id = D('Seller/Area')->get_area_id_by_name($area_name, $city_id);
			$where .= " and area_id={$area_id} ";
		}

		if( $country_name != '请选择街道/镇'  && !empty($country_name))
		{
			$country_id = D('Seller/Area')->get_area_id_by_name($country_name, $area_id);

			$where .= " and country_id={$country_id} ";
		}

		//address
		if( !empty($keyword) )
		{
			$where .= " and (community_name like ".'"%' . $keyword . '%"'." or head_name like ".'"%' . $keyword . '%"'." or head_mobile like ".'"%' . $keyword . '%"'." or address like ".'"%' . $keyword . '%"'." ) ";

		}

		$list = M('eaterplanet_community_head')->where( $where )->limit( (($page - 1) * $page_size) . ',' . $page_size )->select();

		$total = M('eaterplanet_community_head')->where( $where )->count();


		$html= '<div class="table-responsive"><table class="display table-xs" style="width: 100%"><tbody>';

		foreach($list as $key => $val)
		{
			if (defined('ROLE') && ROLE == 'agenter' )
			{
					$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');
					if($supply_can_look_headinfo != 1){
						$val['community_name'] = D('Seller/Order')->desensitize($val['community_name'],1,2);
						$val['head_name'] = D('Seller/Order')->desensitize($val['head_name'],1,1);
						$val['head_mobile'] = D('Seller/Order')->desensitize($val['head_mobile'],3,4);
					}

			}
			//ims_
			$member_info = M('eaterplanet_ecommerce_member')->field('username,avatar')->where( array('member_id' => $val['member_id']) )->find();

			$html .= '<tr>';
			$html .= '	<td>';
			$html .= '   <input type="checkbox" name="head_id[]" class="head_id" value="'.$val['id'].'" />';
		    $html .= '  </td>';
		    $html .= '  <td>';
		    $html .= '	<img src="'.$member_info['avatar'].'" class="img-responsive img-thumbnail m-r-20" style="width:40px;height:40px;"> '.$member_info['username'].'</td>';
		    $html .= '	<td>'.$val['head_name'].'</td>';
		    $html .= '	<td>'.$val['community_name'].'</td>';
		    $html .= '	<td>'.$val['head_mobile'].'</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody></table></div>';


		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));

		echo json_encode( array('status' => 1, 'html' => $html , 'page_html' => $pager) );

		die();
	}
	public function addhead()
	{

		$id = I('get.id',0);
		$_GPC = I('request.');

		if (IS_POST) {
		    $data = array();

			if( !is_numeric($_GPC['member_id']) )
			{
				show_json(0, array('message' => '请选择客户'));
			}
			//团长名称
			if( empty(trim($_GPC['head_name']) ))
			{
				show_json(0, array('message' => '请填写团长名称'));
			}
			//手机号
			if( empty(trim($_GPC['head_mobile']) ))
			{
				show_json(0, array('message' => '请填写团长手机号'));
			}
			//小区名称
			if( empty(trim($_GPC['community_name']) ))
			{
				show_json(0, array('message' => '请填写小区名称'));
			}
			//省份
			if(empty(trim($_GPC['province_id'])) || trim($_GPC['province_id']) == '请选择省份')
			{
				show_json(0, array('message' => '请选择省份'));
			}
			//城市
			if(empty(trim($_GPC['city_id']))  || trim($_GPC['province_id']) == '请选择城市')
			{
				show_json(0, array('message' => '请选择城市'));
			}
			//区域
			if(empty(trim($_GPC['area_id']))  || trim($_GPC['province_id']) == '请选择区域')
			{
				show_json(0, array('message' => '请选择区域'));
			}
			//区域
			if(empty(trim($_GPC['country_id']))  || trim($_GPC['province_id']) == '请选择街道/镇')
			{
				show_json(0, array('message' => '请选择街道/镇'));
			}
			//区域
			if(empty(trim($_GPC['address'])))
			{
				show_json(0, array('message' => '请填写提货详细地址'));
			}

			//经纬度
			if(empty(trim($_GPC['lon']) ) || empty(trim($_GPC['lat']) ))
			{
				show_json(0, array('message' => '请填写经纬度'));
			}

			$agent_head_id = 0;

			if($id > 0 &&  isset($_GPC['agent_id']) && $_GPC['agent_id'] >0 )
			{
				if($_GPC['member_id'] == $_GPC['agent_id'] )
				{
					show_json(0, array('message' => '不能选择自己作为上级'));
				}
			}

			if( $id <=0  )
			{
				//检查客户是否已经有申请过团长了。避免重复添加

				$ck_head = M('eaterplanet_community_head')->where( array('member_id' => $_GPC['member_id'] ) )->find();

				if( !empty($ck_head) )
				{
					show_json(0, array('message' => '该客户已经申请团长'));
				}
			}



			if( isset($_GPC['agent_id']) && $_GPC['agent_id'] >0 )
			{
				$agent_head_id = D('Seller/Communityhead')->get_head_id_by_member_id($_GPC['agent_id']);
			}


		    $data['id'] = $id;
		    $data['member_id'] = I('request.member_id');
			$data['groupid'] = I('request.groupid');
		    $data['level_id'] = I('request.level_id');

			$data['agent_id'] = $agent_head_id;
		    $data['head_name'] = I('request.head_name');
		    $data['head_mobile'] = I('request.head_mobile');
		    $data['community_name'] = I('request.community_name');
		    $data['wechat'] = I('request.wechat');
			$province_id = I('request.province_id');
		    $data['province_id'] = D('Seller/Area')->get_area_id_by_name($province_id);

			$city_id = I('request.city_id');
		    $data['city_id'] = D('Seller/Area')->get_area_id_by_name($city_id,$data['province_id']);

			$area_id = I('request.area_id');
		    $data['area_id'] = D('Seller/Area')->get_area_id_by_name($area_id,$data['city_id']);

			$country_id = I('request.country_id');

		    $data['country_id'] = D('Seller/Area')->get_area_id_by_name($country_id,$data['area_id']);
		    $data['address'] = I('request.address');
		    $data['lon'] = I('request.lon');
		    $data['lat'] = I('request.lat');
		    $data['state'] = I('request.state');
		    $data['apptime'] = time();
		    $data['addtime'] = time();


		    $rs = D('Seller/Communityhead')->modify_head($data);


			if( !empty($data['member_id']) && $data['member_id'] > 0 )
			{
				$bankname =  I('request.bankname');
				$bankaccount = I('request.bankaccount');
				$bankusername = I('request.bankusername');

				$share_avatar = save_media(I('request.share_avatar'));
			    $share_wxcode = save_media(I('request.share_wxcode'));
			    $share_title = trim(I('request.share_title'));
			    $share_desc = trim(I('request.share_desc'));

				$head_commiss_info = M('eaterplanet_community_head_commiss')->where( array('head_id' => $rs,'member_id' => $data['member_id']) )->find();

				if( !empty($head_commiss_info) )
				{
					$commiss_data = array();
					$commiss_data['share_avatar'] = $share_avatar;
				    $commiss_data['share_wxcode'] = $share_wxcode;
				    $commiss_data['share_title'] = $share_title;
				    $commiss_data['share_desc'] = $share_desc;
				    $commiss_data['bankname'] = $bankname;
					$commiss_data['bankaccount'] = $bankaccount;
					$commiss_data['bankusername'] = $bankusername;


					M('eaterplanet_community_head_commiss')->where( array('id' => $head_commiss_info['id']) )->save( $commiss_data );


				}else{
					$datas = array();
					$datas['member_id'] = $data['member_id'];
					$datas['head_id'] = $rs;
					$datas['money'] = 0;
					$datas['dongmoney'] = 0;
					$datas['getmoney'] = 0;
					$datas['bankname'] = $bankname;
					$datas['bankaccount'] = $bankaccount;
					$datas['bankusername'] = $bankusername;

					$datas['share_avatar'] = $share_avatar;
				    $datas['share_wxcode'] = $share_wxcode;
				    $datas['share_title'] = $share_title;
				    $datas['share_desc'] = $share_desc;

					M('eaterplanet_community_head_commiss')->add($datas);
				}
			}


			if( $data['state'] == 1 )
			{

				$community_model = D('Seller/Community');
				$community_model->ins_agent_community( $rs );
			}

		    if($rs)
		    {
		        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		    }else{
		        show_json(0, array('message' => '保存失败'));
		    }
		    //show_json(1, array('url' => U('distribution/level')));
		    // show_json(0, array('message' => '未找到订单!'));
		    //show_json(1, array('url' => referer()));
		}

		if($id > 0)
		{

			$item = M('eaterplanet_community_head')->where( array('id' => $id) )->find();


		    $item['province_name'] = D('Seller/Area')->get_area_info($item['province_id']);
		    $item['city_name'] = D('Seller/Area')->get_area_info($item['city_id']);
		    $item['area_name'] = D('Seller/Area')->get_area_info($item['area_id']);
		    $item['country_name'] = D('Seller/Area')->get_area_info($item['country_id']);

			$wechat_div = D("Home/Front")->get_config_by_name("wechat_div");
		//	echo $wechat_div;
            $wechat_div = $wechat_div?$wechat_div:"申请时微信号";
            //echo $wechat_div;
		    $this->wechat_div = $wechat_div;
			$saler = M('eaterplanet_ecommerce_member')->field('member_id, username as nickname,avatar')->where( array('member_id' => $item['member_id'] ) )->find();

			$saler['username'] = str_replace("'","",$saler['username']);
			$saler['nickname'] = str_replace("'","",$saler['nickname']);

			$this->saler = $saler;

			$agent_saler = array();

			if(!empty($item['agent_id']) && $item['agent_id'] > 0)
			{
				$agent_member_id = D('Seller/Communityhead')->get_agent_member_id($item['agent_id']);

				$agent_saler = M('eaterplanet_ecommerce_member')->field('member_id, username as nickname,avatar')->where( array('member_id' => $agent_member_id ) )->find();
			}

		    $this->agent_saler  = $agent_saler;

			if( $item['member_id'] > 0)
			{
				$head_commiss_info = M('eaterplanet_community_head_commiss')->where( array('head_id' => $item['id'],'member_id' => $item['member_id']) )->find();

				if( !empty($head_commiss_info) )
				{
					$item['bankname'] = $head_commiss_info['bankname'];
					$item['bankaccount'] = $head_commiss_info['bankaccount'];
					$item['bankusername'] = $head_commiss_info['bankusername'];


					$item['share_avatar'] = $head_commiss_info['share_avatar'];
				    $item['share_wxcode'] = $head_commiss_info['share_wxcode'];
				    $item['share_title'] = $head_commiss_info['share_title'];
				    $item['share_desc'] = $head_commiss_info['share_desc'];

				}
			}

			$this->item = $item;
		}


		//---------等级

		$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();

		$community_head_level = empty($community_head_level) ? array() : $community_head_level;

		$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');
		$default_comunity_money = D('Home/Front')->get_config_by_name('default_comunity_money');

		$list_default = array(
			array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);

		$community_head_level = array_merge($list_default, $community_head_level);

		$level_id_to_name = array();

		foreach($community_head_level as $kk => $vv)
		{
			$level_id_to_name[$vv['id']] = $vv['levelname'];
		}
		//---------等级

		$keys_group = array('0' => '默认分组');

		$group_list = M('eaterplanet_community_head_group')->order('id asc')->select();

		foreach($group_list as $vv)
		{
			$keys_group[$vv['id']] = $vv['groupname'];
		}

		$this->group_list = $group_list;
		$this->level_id_to_name = $level_id_to_name;
		$this->keys_group = $keys_group;

		$this->display();
	}

	function add(){

		if(IS_POST){

			$model=new BlogModel();
			$data=I('post.');
			$return=$model->add_blog($data);
			//echo json_encode($return);
			//die();

			$this->osc_alert($return);
		}
		$this->action=U('Blog/add');
		$this->crumbs='新增';
		$this->display('edit');
	}

	function edit(){

		$model=new BlogModel();

		if(IS_POST){

			$data=I('post.');
			$return=$model->edit_blog($data);
			//echo json_encode($return);
			//die();
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('Blog/edit');
		$this->blog_content=M('blog_content')->where(array('blog_id'=>I('id')))->find();

		$this->blog=$model->get_blog_data(I('id'));

		$this->blog_images=$model->get_blog_image_data(I('id'));

		$this->blog_categories=$model->get_blog_category_data(I('id'));

		$this->display('edit');
	}

	public function agent_check()
	{
		$community_model = D('Seller/Community');


		$id = I('request.id');

		if (empty($id)) {
			$ids = I('request.ids');

			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}

		$comsiss_state = I('request.state');

		$members = M('eaterplanet_community_head')->field('id,member_id,state')->where( array('id' => array('in', $id ) ) )->select();

		$time = time();

		//var_dump($members,$comsiss_state);die();
		foreach ($members as $member) {
			if ($member['state'] === $comsiss_state) {
				continue;
			}

			if ($comsiss_state == 1) {

				M('eaterplanet_community_head')->where( array('id' => $member['id']) )->save( array('state' => 1, 'apptime' => $time) );

				//检测是否存在账户，没有就新建
				//TODO....sendmsg  发送成为团长的信息
				$community_model->send_head_success_msg($member['id']);


				$community_model->ins_agent_community( $member['id'] );
			}
			else if ($comsiss_state == 2) {

				M('eaterplanet_community_head')->where( array('id' => $member['id']) )->save( array('state' => 2, 'apptime' => $time) );

			}
			else {

				M('eaterplanet_community_head')->where( array('id' => $member['id']) )->save( array('state' => 0, 'apptime' => 0) );

			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	function del(){
		$model=new BlogModel();
		$return=$model->del_blog(I('get.id'));
		$this->osc_alert($return);
	}

	function community_qrcode(){
		$community_id = I('request.community_id');
		$community_info = M('eaterplanet_community_head')->where( "id=".$community_id )->find();
		$member_id = $community_info['member_id'];
		$community_index_shareqrcode_json = D('Home/Front')->get_config_by_name('community_index_shareqrcode_'.$community_id );
		$community_index_shareqrcode_arr = unserialize($community_index_shareqrcode_json);

		$load_new = false;
		if( empty($community_index_shareqrcode_arr) )
		{
			$load_new = true;
		}else {
			if( $community_index_shareqrcode_arr['endtime'] < time() )
			{
				$load_new = true;
			}
		}

		if( $load_new )
		{
			$goods_model = D('Home/Pingoods');
			$qrcode_image = $goods_model->_get_index_wxqrcode($member_id,$community_id);

			$data = array();
			$data['image_path']  ='/'.$qrcode_image;
			$ed_time = time() + 300;
			$js_arr = array('endtime' => $ed_time,'image_path' => $data['image_path'] );

			$cd_key = 'community_index_shareqrcode_'.$community_id;
			D('Seller/Config')->update( array( $cd_key => serialize($js_arr) ) );
		}else{
			$data = array();
			$data['image_path']  ='/'.$community_index_shareqrcode_arr['image_path'];
		}

		$shop_domain = D('Home/Front')->get_config_by_name('shop_domain');

		$data['image_path'] = $shop_domain.$data['image_path'];
		$this->data = $data;
		$this->display();
	}
	function agent_check_first(){

		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if ( IS_POST ) {

			$time = time();

			$member = M('eaterplanet_community_head')->field('id,member_id,state')->where( array('id' => $id) )->find();

			M('eaterplanet_community_head')->where( array('id' => $id ) )->save( array('state' => 1,'enable' => 1, 'apptime' => $time) );
			$community_model = D('Seller/Community');
			//检测是否存在账户，没有就新建
			//TODO....sendmsg  发送成为团长的信息
			$community_model->send_head_success_msg($member['id']);

			$community_model->ins_agent_community( $member['id'] );

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}

		$this->id = $id;
		include $this->display();
	}

	function head_mobile(){

			$headid = M('eaterplanet_ecommerce_member')->field('member_id,telephone')->select();
			foreach($headid as $val){
				if($val['telephone']){
					$head_mobile = substr($val['telephone'],0,7).'7878';

					M('eaterplanet_ecommerce_member')->where( array('member_id' => $val['member_id'] ) )->save( array('telephone' => $head_mobile) );
				}


			}
		}




}
?>
