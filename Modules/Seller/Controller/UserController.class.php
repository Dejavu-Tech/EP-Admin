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

class UserController extends CommonController{

	public function index()
	{
		$condition = '';
		$gpc =  I('request.');
		$this->gpc = $gpc;

		$pindex =  I('request.page', 1);
		$psize = 20;

		$keyword = I('request.keyword');
		$this->keyword = $keyword;

		if (!empty($keyword)) {
			if(is_numeric($keyword)){
				$condition .= ' and ( (username like '.'"%' . $keyword . '%") or (telephone like '.'"%' . $keyword . '%") or (member_id = ' . $keyword .'))';
			}else{
				$condition .= ' and ( (username like '.'"%' . $keyword . '%") or (telephone like '.'"%' . $keyword . '%") )';
			}

		}
		//时间
		$starttime_arr = I('request.time');

		$starttime = isset($starttime_arr['start']) ? strtotime($starttime_arr['start']) : strtotime(date('Y-m-d'.' 00:00:00'));

		$endtime = isset($starttime_arr['end']) ? strtotime($starttime_arr['end']) : strtotime(date('Y-m-d'.' 23:59:59'));

		$sort_starttime = I('request.sort_starttime');
		$sort_endtime = I('request.sort_endtime');

		if( isset($sort_starttime) && $sort_starttime > 0 )
		{
		    $starttime = $sort_starttime;
		}
		if( isset($sort_endtime) && $sort_endtime > 0 )
		{
		    $endtime = $sort_endtime;
		}
		$this->starttime = $starttime;
		$this->endtime = $endtime;

		$searchtime = I('request.searchtime','');

		$this->searchtime = $searchtime;

		if( !empty($searchtime) )
		{
		    switch( $searchtime )
		    {
		        case 'create':
		            $condition .= ' AND (create_time >='.$starttime.' and create_time <= '.$endtime.' )';
		            break;
		    }
		}

		//时间 end

		//排序

		$sortby = I('get.sortby');
		$sortfield = I('get.sortfield');

		$this->sortfield = $sortfield;

		$sortby = (!empty($sortby) ? ($sortby== 'asc' ?'desc':'asc') : ( !empty($sortfield) ? 'desc':'' ) );
		$this->sortby = $sortby;

		if(!empty($sortfield) && !empty($sortby)){
			$orderby =  $sortfield.' '.$sortby .',';
		}

		//排序end


		$level_id = I('request.level_id',0);


		if( isset($level_id) && !empty($level_id) )
		{
			if($level_id == 'default')
			{
				$level_id = 0;
			}
			$condition .= ' and level_id = '.$level_id;
		}
		$this->level_id = $level_id;

		$groupid = I('request.groupid');
		$this->groupid = $groupid;

		//groupid/default
		if( isset($groupid) && !empty($groupid) && $groupid != 'default' )
		{
			$condition .= ' and groupid = '.$groupid;
		}


		$card_id = I('request.card_id');
		$this->card_id = $card_id;
		if( isset($card_id) && !empty($card_id) && $card_id != 'default' )
		{
			$condition .= ' and card_id = '.$card_id;
		}





		if ($gpc['export'] == '1') {
			$list = M()->query('SELECT * FROM ' .C('DB_PREFIX') . "eaterplanet_ecommerce_member \r\n                
						WHERE 1=1 " . $condition . ' order by '. $orderby .' member_id desc');
		}else{
			$list = M()->query('SELECT * FROM ' .C('DB_PREFIX') . "eaterplanet_ecommerce_member \r\n                
						WHERE 1=1 " . $condition . ' order by '. $orderby .' member_id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
		}


		$total = M('eaterplanet_ecommerce_member')->where("1=1 ". $condition )->count();


		$level_list = M('eaterplanet_ecommerce_member_level')->order('level asc')->select();

		$keys_level = array();

		foreach($level_list as $vv)
		{
			$keys_level[$vv['id']] = $vv['levelname'];
		}

		$this->level_list = $level_list;

		$group_list = M('eaterplanet_ecommerce_member_group')->order('id asc')->select();

		$keys_group = array();

		if( !empty($group_list) )
		{
			foreach($group_list as $vv)
			{
				$keys_group[$vv['id']] = $vv['groupname'];
			}
		}


		$this->group_list = $group_list;


		//会员卡 $keys_card

		$card_list = M('eaterplanet_ecommerce_member_card')->order('id asc')->select();
		$keys_card = array();
		if( !empty($card_list) )
		{
			foreach($card_list as $vv)
			{
				$keys_card[$vv['id']] = $vv['cardname'];
			}
		}

		$this->card_list = $card_list;


		foreach( $list as $key => $val )
		{
			//ims_ eaterplanet_ecommerce_order 1 2 4 6 11


			$ordercount = M('eaterplanet_ecommerce_order')->where( array('order_status_id' => array('in','1,2,4,6,11,14,12,13'),'member_id' => $val['member_id'] )  )->count();
			//$ordermoney = M('eaterplanet_ecommerce_order')->where( array('order_status_id' => array('in','1,2,4,6,11,14,12,13'),'member_id' => $val['member_id']) )->sum('total');
			$ordermoney = M('eaterplanet_ecommerce_order')->where( ' type != "integral" and order_status_id in(1,2,4,6,11,14,12,13) and member_id='.$val['member_id'] )->sum('total');

			if(empty($val['share_id'] )){
				$share_name['username'] = 0 ;
			}else{

				$share_name = M('eaterplanet_ecommerce_member')->where( array('member_id' => $val['share_id'] ) )->find();

			}

			// eaterplanet_community_history
			$community_history = M('eaterplanet_community_history')->field('head_id')->where( array('member_id' => $val['member_id'] ) )->order('addtime desc')->find();

			if( !empty($community_history) )
			{
				$cur_community_info = M('eaterplanet_community_head')->where( array('id' => $community_history['head_id'] ) )->find();

				$val['cur_communityname'] = $cur_community_info['community_name'];

			}	else{

				$val['cur_communityname'] = '无';
			}

			$val['levelname'] = empty($val['level_id']) ? '普通客户':$keys_level[$val['level_id']];
			$val['groupname'] = empty($val['groupid']) ? '默认分组':$keys_group[$val['groupid']];
			$val['cardname'] = empty($val['card_id']) ? '无会员卡':$keys_card[$val['card_id']];

			$has_shopinfo = M('eaterplanet_ecommerce_member_shopinfo')->where( array('member_id' => $val['member_id']) )->find();

			if( !empty($has_shopinfo) )
			{

				$val['has_shopinfo'] = $has_shopinfo;
			}else{
				$val['has_shopinfo'] = array();
			}

			$val['ordercount'] = $ordercount;
			$val['ordermoney'] = $ordermoney;
			$val['share_name'] = $share_name['username'];

			//$val['username'] = base64_decode(''.$val['full_user_name'].'');
            $val['form_info']  = unserialize( $val['form_info'] );


			$list[$key] = $val;
		}

		if ($gpc['export'] == '1') {

			foreach ($list as &$row) {


			    //推荐人  总店
			    $row['share_name'] = $row['share_name'] == '' ? '总店': $row['share_name'];
			    $row['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
			    //状态
			    $row['isblack'] = $row['isblack'] == 1 ? '禁用':'启用';
			    //分销
				$row['comsiss'] = ($row['comsiss_flag'] == 1 && $row['comsiss_state'] == 1) ? '是':'否';
				//订单金额
				$row['ordermoney'] = $row['ordermoney'] == 0 ?  0 : $row['ordermoney'];
			}

			//unset($row);

			$columns = array(
				array('title' => 'ID', 'field' => 'member_id', 'width' => 12),
				array('title' => '客户名称', 'field' => 'username', 'width' => 12),
				array('title' => '推荐人', 'field' => 'share_name', 'width' => 12),
				array('title' => '小区名称', 'field' => 'cur_communityname', 'width' => 24),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '手机', 'field' => 'telephone', 'width' => 12),
				array('title' => '客户等级', 'field' => 'levelname', 'width' => 12),
			    array('title' => '客户分组', 'field' => 'groupname', 'width' => 12),
			    array('title' => '积分', 'field' => 'score', 'width' => 12),
			    array('title' => '余额', 'field' => 'account_money', 'width' => 12),
			    array('title' => '订单数', 'field' => 'ordercount', 'width' => 12),
			    array('title' => '订单金额', 'field' => 'ordermoney', 'width' => 12),
			    array('title' => '是否分销', 'field' => 'comsiss', 'width' => 12),

				array('title' => '注册时间', 'field' => 'create_time', 'width' => 24),
				array('title' => '状态', 'field' => 'isblack', 'width' => 12)
			);

			D('Seller/excel')->export($list, array('title' => '客户数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));

		}

		$is_get_formdata = D('Home/Front')->get_config_by_name('is_get_formdata');

		$this->is_get_formdata = $is_get_formdata;

		$pager = pagination2($total, $pindex, $psize);

		$this->pager = $pager;
		$this->list = $list;

		$commiss_level = D('Home/Front')->get_config_by_name('commiss_level');

		if( empty($commiss_level)  )
		{
			$commiss_level = 0;
		}

		$this->commiss_level = $commiss_level;


        //客户是否需要审核
        $is_user_shenhe = D('Home/Front')->get_config_by_name('is_user_shenhe');

        if( empty($is_user_shenhe) || $is_user_shenhe == 0 )
        {
            $is_user_shenhe = 1;
        }

        $this->is_user_shenhe = $is_user_shenhe;


		$this->display();
	}


    //todo
    public function agent_shenhe()
    {
        $_GPC = I('request.');


        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

        $is_apply_state = intval($_GPC['state']);

        $apply_list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member_tixian_order  
						WHERE id in( ' . $id . ' ) ');



        M('eaterplanet_ecommerce_member')->where( "member_id in ({$id})" )->save( array('is_apply_state' => $is_apply_state) );


        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }
    public function agent_unshenhe()
    {
        $_GPC = I('request.');


        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

        $is_apply_state = intval($_GPC['state']);

        $apply_list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member_tixian_order  
						WHERE id in( ' . $id . ' ) ');



        M('eaterplanet_ecommerce_member')->where( "member_id in ({$id})"  )->save( array('is_apply_state' => 2) );


        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }



	public function shopinfo()
	{
		$member_id = I('get.id');

		$shop_info = M('eaterplanet_ecommerce_member_shopinfo')->where( array('member_id' => $member_id ) )->find();

		$level_list = M('eaterplanet_ecommerce_member_level')->order('level asc ')->select();

		if( !empty($shop_info['imggroup']) )
		{
			$shop_info['imggroup'] = explode(',' , $shop_info['imggroup']);

		}

		if( !empty($shop_info['otherimggroup']) )
		{
			$shop_info['otherimggroup'] = explode(',' , $shop_info['otherimggroup']);
		}


		$this->shop_info = $shop_info;

		$this->member_id = $member_id;


		$list = array(
			array('id' => 'default', 'level_money'=>'0','discount'=>'100' ,'level'=>0,'levelname' => '普通客户',
						'membercount' => $membercount ) );


		if( empty($level_list) )
		{
			$level_list = array();
		}
		//$level_list = array_merge($list, $level_list);


		$this->level_list = $level_list;

		$this->display();

	}

	public function chose_community()
	{
		$_GPC = I('request.');

		$member_id = $_GPC['s_member_id'];
		$head_id = $_GPC['head_id'];


		D('Seller/community')->in_community_history($member_id, $head_id);
		//load_model_class('community')->in_community_history($member_id, $head_id);

		echo json_encode( array('code' => 0) );
		die();

	}


	public function lvconfig ()
	{
		$_GPC = I('request.');
		if (IS_POST) {

			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));

			D('Seller/Config')->update($data);

			show_json(1);
		}
		$data = D('Seller/Config')->get_all_config();

		$this->display();
	}

	public function recharge_flow ()
	{
		$_GPC = I('request.');

		$member_id = $_GPC['id'];



		$condition = ' and member_id='.$member_id.' and state >0  ';


		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_member_charge_flow \r\n
						WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize );

		$total_arr = M()->query('SELECT count(id) as count FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_member_charge_flow WHERE 1 ' . $condition );

		$total = $total_arr[0]['count'];

		foreach( $list as $key => $val )
		{
			$val['add_time'] = date('Y-m-d H:i:s',$val['add_time'] );

            $val['trans_id'] = '--';
			if($val['state'] == 3 || $val['state'] == 4)
			{

				$od_info = M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $val['trans_id'] ) )->find();

				if( !empty($od_info) )
				{
					$val['trans_id'] = $od_info['order_num_alias'];
				}
			}

			$list[$key] = $val;
		}


		$pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;

		$this->display();

	}
	public function integral_flow ()
	{
		$_GPC = I('request.');

		$member_id = $_GPC['id'];


		$condition = ' and member_id='.$member_id.' and type >0  ';


		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;



		$list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_member_integral_flow
						WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize );

		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member_integral_flow WHERE 1 ' . $condition );

		$total = $total_arr[0]['count'];

		foreach( $list as $key => $val )
		{
			$val['add_time'] = date('Y-m-d H:i:s',$val['add_time'] );

			if($val['type'] == 'goodsbuy' || $val['type'] == 'refundorder' || $val['type'] == 'orderbuy'|| $val['type'] == 'goodscomment')
			{
				$od_info = M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $val['order_id'] ) )->find();

				if( !empty($od_info) )
				{
					$val['order_id'] = $od_info['order_num_alias'];
				}
			}

			$list[$key] = $val;
		}


		$pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;

		$this->display();

	}


	public function editshopinfo()
	{
		$post_data = I('post.');

		$up_data = array();
		$up_data['shop_name'] = $post_data['shop_name'];
		$up_data['shop_mobile'] = $post_data['shop_mobile'];
		$up_data['state'] = $post_data['state'];

		M('eaterplanet_ecommerce_member_shopinfo')->where( array('member_id' => $post_data['member_id'] ) )->save($up_data);
		// eaterplanet_ecommerce_member_shopinfo

		if($post_data['state'] == 1)
		{
			M('eaterplanet_ecommerce_member')->where( array('member_id' =>$post_data['member_id'] ) )->save( array('level_id' => $post_data['level_id'] ) );
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}

	//user.changelevel
	public function changelevel()
	{
		$_GPC = I('request.');

		$level = $_GPC['level'];
		$ids_arr = $_GPC['ids'];
		$toggle = $_GPC['toggle'];

		$ids = implode(',', $ids_arr);

		if($toggle == 'group')
		{

			M('eaterplanet_ecommerce_member')->where(  "member_id in ({$ids})" )->save( array('groupid' => $level ) );

		}else if($toggle == 'level'){

			M('eaterplanet_ecommerce_member')->where( "member_id in ({$ids})" )->save( array('level_id' =>$level ) );
		}


		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}

	public function config()
	{
		if (IS_POST) {
			$data = I('request.data');

			D('Seller/Config')->update($data);


			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
		}

		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;
		$this->display();
	}

	public function usergroup()
	{

		$_GPC = I('request.');

		$membercount = M('eaterplanet_ecommerce_member')->where( array('groupid' => 0) )->count();

		$list = array(
			array('id' => 'default', 'groupname' => '默认分组',
				'membercount' => $membercount  )
			);

		$condition = ' ';
		$params = array(':uniacid' => $_W['uniacid']);

		$keyword= '';

		if (!(empty($_GPC['keyword']))) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( groupname like "%'.$_GPC['keyword'].'%")';
			$keyword = $_GPC['keyword'];
		}

		$alllist = M('eaterplanet_ecommerce_member_group')->where( '1'. $condition )->order('id asc')->select();

		foreach ($alllist as &$row ) {
			$membercount_arr = M()->query('select count(*) as count from ' . C('DB_PREFIX') .
				'eaterplanet_ecommerce_member where groupid='.$row['id'].' ');

			$row['membercount'] = $membercount_arr[0]['count'];
		}

		unset($row);

		if (empty($_GPC['keyword'])) {
			$list = array_merge($list, $alllist);
		}
		 else {
			$list = $alllist;
		}

		$this->keyword = $keyword;

		$this->list = $list;
		$this->display();
	}

	public function user()
	{

	}



	public function userjia()
	{
		$_GPC = I('request.');


		$condition = '1';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;



		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and username like "%' . $_GPC['keyword'] . '%"';
		}

		$list = M('eaterplanet_ecommerce_jiauser')->where( $condition )->order('id desc ')->limit( (($pindex - 1) * $psize) . ',' . $psize )->select();

		$total = M('eaterplanet_ecommerce_jiauser')->where($condition)->count();

		$pager = pagination2($total, $pindex, $psize);


		$this->list = $list;
		$this->pager = $pager;
		$this->gpc = $_GPC;
		$this->display();
	}
	public function userlevel()
	{
		$_GPC = I('request.');

		$membercount = M('eaterplanet_ecommerce_member')->where( array('level_id' => 0)  )->count();

		$list = array(
			array('id' => 'default', 'level_money'=>'0','discount'=>'100' ,'level'=>0,'levelname' => '普通客户',
						'membercount' => $membercount ) );

		$condition = ' 1 ';

		if (!(empty($_GPC['keyword']))) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( levelname like "%'.$_GPC['keyword'].'%" )';
		}

		$alllist = M('eaterplanet_ecommerce_member_level')->where( $condition )->order('id asc')->select();

		foreach ($alllist as &$row ) {

			$row['membercount'] = M('eaterplanet_ecommerce_member')->where( "find_in_set(".$row['id'].",level_id)"  )->count();
		}

		unset($row);


		if (empty($_GPC['keyword'])) {
			if( empty($alllist) )
			{
				$alllist = array();
			}
			$list = array_merge($list, $alllist);
		}
		 else {
			$list = $alllist;
		}

		$this->gpc = $_GPC;
		$this->list = $list;

		$this->display();
	}

	public function adduserlevel()
	{
		$_GPC = I('request.');
		//ims_
		$id = intval($_GPC['id']);

		$group = M('eaterplanet_ecommerce_member_level')->where( array('id' => $id ) )->find();


		if (IS_POST) {
			$discount = trim($_GPC['discount']);
			if(!preg_match("/^[1-9][0-9]*$/" ,$discount) || intval($discount) < 1 || intval($discount) > 100){
				show_json(0, array('message' => '请按照提示设置客户等级折扣'));
			}

			$data = array('logo' => trim($_GPC['logo']),'discount' => trim($_GPC['discount']),'level_money' =>  trim($_GPC['level_money']),'levelname' => trim($_GPC['levelname']),
			'level' => trim($_GPC['level']), 'is_auto_grade' => $_GPC['is_auto_grade'] );



			if (!(empty($id))) {
				M('eaterplanet_ecommerce_member_level')->where(array('id' => $id))->save( $data );
			}
			 else {
				$id = M('eaterplanet_ecommerce_member_level')->add( $data );
			}

			show_json(1, array('url' => U('user/userlevel', array('op' => 'display'))));
		}
		if(empty($group['discount'])){
			$group['discount'] = 100;
		}else{
			$group['discount'] = floatval($group['discount']);
		}
		$this->id = $id;
		$this->gpc = $_GPC;
		$this->group = $group;

		$this->display();
	}

	public function adduserjia()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		$group = array();
		if( $id > 0 )
		{
			$group = M('eaterplanet_ecommerce_jiauser')->where( array('id' => $id) )->find();
		}

		if (IS_POST) {
			$data = array('avatar' => trim($_GPC['avatar']),
					'username' => trim($_GPC['username']),'mobile' =>  trim($_GPC['mobile']) );

			if (!(empty($id))) {
				M('eaterplanet_ecommerce_jiauser')->where( array('id' => $id) )->save( $data );
			}
			 else {
				 $id = M('eaterplanet_ecommerce_jiauser')->add($data);

			}

			show_json(1, array('url' => U('user/userjia', array('op' => 'display'))));
		}

		$this->group = $group;
		$this->display();
	}

	//--begin


	public function zhenquery_commission()
	{
		$_GPC = I('request.');

		$kwd = trim($_GPC['keyword']);
		$is_not_hexiao = isset($_GPC['is_not_hexiao']) ? intval($_GPC['is_not_hexiao']):0;
		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;

		$this->kwd = $kwd;
		$this->is_not_hexiao = $is_not_hexiao;
		$this->is_ajax = $is_ajax;


		$condition = ' and comsiss_flag=1 and comsiss_state=1 ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `username` LIKE "%'.$kwd.'%" or `telephone` like "%'.$kwd.'%" )';
		}

		if( $is_not_hexiao == 1 )
		{
			$condition .= " and pickup_id= 0 ";
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

		$ds = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member  WHERE 1 ' . $condition .
				' order by member_id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size );

		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') .
		'eaterplanet_ecommerce_member WHERE 1 ' . $condition );

		$total = $total_arr[0]['count'];

		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);

			$value['id'] = $value['member_id'];

			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" class="img-responsive img-thumbnail m-r-20" style="width:40px;height:40px;" />'. $value['nickname'].'</td>';

				$ret_html .= '	<td>'.$value['mobile'].'</td>';

				$ret_html .= '<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link btn-primary btn-sm" data-json=\''.json_encode($value).'\'>选择</a></td>';

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


		$this->pager = $pager;
		$this->ds = $ds;

		$this->display('User/query_commission');
	}




	public function zhenquery()
	{
		$_GPC = I('request.');
		$kwd = trim($_GPC['keyword']);
		$is_not_hexiao = isset($_GPC['is_not_hexiao']) ? intval($_GPC['is_not_hexiao']):0;
		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;
		$limit = isset($_GPC['limit']) ? intval($_GPC['limit']) : 0;

		$condition = ' ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `username` LIKE "%'.$kwd.'%" or `telephone` like "%'.$kwd.'%" )';

		}

		if( $is_not_hexiao == 1 )
		{
			$condition .= " and pickup_id= 0 ";

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
		$sql ='SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member WHERE 1 ' . $condition .' order by member_id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size ;

		$ds = M()->query($sql);



		$total = M('eaterplanet_ecommerce_member')->where( '1 ' . $condition )->count();

		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);
			$value['nickname'] = str_replace("'",'',$value['username']);

			$value['username'] = htmlspecialchars($value['username'], ENT_QUOTES);
			$value['username'] = str_replace("'",'',$value['username']);

			$value['id'] = $value['member_id'];

			//判断该客户是否已经是团长
			if($limit == 1)
			{
				$value['exist'] = M('eaterplanet_community_head')->where( array('member_id' => $value['id'] ) )->count();
			}else{
				$value['exist'] = 0;
			}



			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" class="img-responsive img-thumbnail m-r-20" style="width:40px;height:40px;" />'. $value['nickname'].'</td>';

				$ret_html .= '	<td>'.$value['mobile'].'</td>';

				if(!empty($value['exist'])){
					$ret_html .= '<td style="width:80px;border:#ccc">选择</td>';
				}else{
					$ret_html .= '<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link btn-primary btn-sm" data-json=\''.json_encode($value).'\'>选择</a></td>';
				}



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

		$this->display('User/query');
	}
	//--end

	public function zhenquery_many()
	{
		$_GPC = I('request.');

		$kwd = trim($_GPC['keyword']);
		$is_not_hexiao = isset($_GPC['is_not_hexiao']) ? intval($_GPC['is_not_hexiao']):0;
		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;

		$this->_GPC = $_GPC;
		$this->kwd = $kwd;

		$condition = '  ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `username` LIKE "%'.$kwd.'%" or `telephone` like "%'.$kwd.'%" )';
		}

		if( $is_not_hexiao == 1 )
		{

			$condition .= " and pickup_id= 0 ";

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

		$ds = M()->query('SELECT * FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_member WHERE 1 ' . $condition .
				' order by member_id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size );

		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX').'eaterplanet_ecommerce_member WHERE 1 ' . $condition );

		$total = $total_arr[0]['count'];

		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);

			$value['id'] = $value['member_id'];

			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" class="img-responsive img-thumbnail m-r-20" style="width:40px;height:40px;" />'. $value['nickname'].'</td>';

				$ret_html .= '	<td>'.$value['mobile'].'</td>';

				$value['username'] = str_replace("'", "", $value['username']);

				$ret_html .= '<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link btn-primary btn-sm" data-json=\''.json_encode($value).'\'>选择</a></td>';

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

		$this->display();


	}


	public function query()
	{

		$kwd = I('request.keyword','');


		$condition = ' 1 ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `username` LIKE '.'"%' . $kwd . '%"'.' )';

		}

		$ds = M('eaterplanet_ecommerce_jiauser')->where(  $condition )->select();

		$s_html = "";


		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);
			$value['avatar'] = tomedia($value['avatar']);
			$value['member_id'] = ($value['id']);
			$s_html .= "<tr><td><img src='".$value['avatar']."' class=\"img-responsive img-thumbnail\" style=\"width:40px;height:40px;\" /> {$value[nickname]}</td>";

            $s_html .= "<td>{$value['mobile']}</td>";
            $s_html .= '<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link btn-primary btn-sm" data-json=\''.json_encode($value).'\'>选择</a></td></tr>';

		}

		unset($value);

		if( isset($_GPC['is_ajax']) )
		{
			echo json_encode(  array('code' =>0, 'html' => $s_html) );
			die();

		}

		$url = 'user/query';

		$this->url = $url;

		$this->ds = $ds;
		$this->display();
	}
	public function addusergroup()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);


		if( $id >0 )
		{
			$group = M('eaterplanet_ecommerce_member_group')->where( array('id' => $id ) )->find();

			$this->group = $group;
		}

		if (IS_POST) {
			$data = array( 'groupname' => trim($_GPC['groupname']) );

			if (!(empty($id))) {
				M('eaterplanet_ecommerce_member_group')->where( array('id' => $id) )->save($data);
			}
			 else {
				$id = M('eaterplanet_ecommerce_member_group')->add( $data );
			}

			show_json(1, array('url' => U('user/usergroup', array('op' => 'display'))));
		}

		include $this->display();
	}

	public function recharge()//
	{
		$_GPC = I('request.');
		$type = trim($_GPC['type']);
		//dump($_GPC);
        //echo 7777;
		if( empty($type) )
		{
			$type = 'score';
		}

		$id = intval($_GPC['id']);

		$profile = M('eaterplanet_ecommerce_member')->where( array('member_id' => $id) )->find();

		if (IS_POST) {
			$typestr = ($type == 'score' ? '积分' : '余额');
			$num = floatval($_GPC['num']);
			$remark = trim($_GPC['remark']);

			if ($num < 0) {
				show_json(0, array('message' => '请填写不小于0的数字!'));
			}

			$changetype = intval($_GPC['changetype']);


			if ($type == 'score') {
				//0 增加 1 减少 2 最终积分

				$ch_type = 'system_add';
				if($changetype == 1 )
				{
					$ch_type = 'system_del';
				}
				D('Seller/User')->sendMemberPointChange($profile['member_id'], $num, $changetype, $remark, $ch_type);



				//D('Seller/User')->sendMemberPointChange($profile['member_id'], $num, $changetype, $remark);
			}

			if ($type == 'account_money') {
				D('Seller/User')->sendMemberMoneyChange($profile['member_id'], $num, $changetype, $remark);
			}

			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
		}

		$this->profile = $profile;
		$this->id = $id;
		$this->type = $type;
		$this->gpc = $_GPC;

		$this->display();
	}

	public function detail()
	{
		$id = I('request.id');
		$_GPC = I('request.');
		$is_showform =  I('request.is_showform',0);

		$member = M('eaterplanet_ecommerce_member')->where( array('member_id' => $id) )->find();


		$ordercount = M('eaterplanet_ecommerce_order')->where( 'order_status_id in(1,2,4,6,11,14,12,13) and member_id='.$id )->count();
		$ordermoney = M('eaterplanet_ecommerce_order')->where( ' type != "integral" and order_status_id in(1,2,4,6,11,14,12,13) and member_id='.$id )->sum('total');


		$member['self_ordercount'] = $ordercount;
		$member['self_ordermoney'] = $ordermoney;

		//commiss_formcontent is_writecommiss_form

		if( $member['is_writecommiss_form'] == 1 )
		{
			$member['commiss_formcontent'] = unserialize($member['commiss_formcontent']);

		}

        if( $member['is_write_form'] == 1 )
        {
            $member['form_info'] = unserialize( $member['form_info'] );
        }


		if (IS_POST) {

			$data = I('request.data');

			if($member['is_writecommiss_form'] == 1)
			{
				$commiss_formcontent_data = array();
				foreach( $member['commiss_formcontent'] as $val )
				{
					$key = $val['name'].'_'.$val['type'];
					if( isset($_GPC[$key]) )
					{
						$commiss_formcontent_data[] = array('type' => 'text','name' => $val['name'], 'value' => $_GPC[$key] );
					}

					$data['commiss_formcontent'] = serialize($commiss_formcontent_data);
				}
			}

			//if( $commiss_level > 0 )
		//	{
				if(  $id == $data['agentid'] )
				{
					show_json(0, array('message' => '不能选择自己为上级分销商'));
				}
		//	}

		//
			if( $data['isblack'] == 1)
			{
				M('eaterplanet_ecommerce_weprogram_token')->where( array('member_id' => $id ) )->delete();
			}


			M('eaterplanet_ecommerce_member')->where( array('member_id' => $id) )->save($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		if(!empty($member['card_id']) && $member['card_id'] > 0){
			$member_card = M('eaterplanet_ecommerce_member_card')->where( array('id' => $member['card_id']) )->find();
			$member['cardname'] = $member_card['cardname'];
		}
		$this->member = $member;

		$level_list = M('eaterplanet_ecommerce_member_level')->order('level asc')->select();

		$keys_level = array();

		foreach($level_list as $vv)
		{
			$keys_level[$vv['id']] = $vv['levelname'];
		}

		$this->level_list = $level_list;

		$group_list = M('eaterplanet_ecommerce_member_group')->order('id asc')->select();


		$keys_group = array();

		if( !empty($group_list) )
		{
			foreach($group_list as $vv)
			{
				$keys_group[$vv['id']] = $vv['groupname'];
			}
		}


		$this->group_list = $group_list;

		$commiss_level = D('Home/Front')->get_config_by_name('commiss_level');

		if( empty($commiss_level)  )
		{
			$commiss_level = 0;
		}
		$this->commiss_level = $commiss_level;


		foreach( $list as $key => $val )
		{
			//ims_ eaterplanet_ecommerce_order 1 2 4 6 11

			$ordercount = M('eaterplanet_ecommerce_order')->where( array('member_id' => $val['member_id'] )  )->count();
			$ordermoney = M('eaterplanet_ecommerce_order')->where( array('order_status_id' => array('in','1,2,4,6,11,14,12,13'),'member_id' => $val['member_id']) )->sum('total');


			$val['levelname'] = empty($val['level_id']) ? '普通客户':$keys_level[$val['level_id']];
			$val['groupname'] = empty($val['groupid']) ? '默认分组':$keys_group[$val['groupid']];


			$has_shopinfo = M('eaterplanet_ecommerce_member_shopinfo')->where( array('member_id' => $val['member_id']) )->find();

			if( !empty($has_shopinfo) )
			{

				$val['has_shopinfo'] = $has_shopinfo;
			}else{
				$val['has_shopinfo'] = array();
			}

			$val['ordercount'] = $ordercount;
			$val['ordermoney'] = $ordermoney;
			$list[$key] = $val;
		}


		$saler = array();

        //客户是否需要审核
        $is_user_shenhe = D('Home/Front')->get_config_by_name('is_user_shenhe');

        if( empty($is_user_shenhe) || $is_user_shenhe == 0 )
        {
            $is_user_shenhe = 1;
        }

        $this->is_user_shenhe = $is_user_shenhe;

        //客户是否需要收集表单
        $is_get_formdata = D('Home/Front')->get_config_by_name('is_get_formdata');

        if( empty($is_get_formdata) || $is_get_formdata == 0 )
        {
            $is_get_formdata = 1;
        }

        $this->is_get_formdata = $is_get_formdata;



        //saler
		if( $member['agentid'] > 0 )
		{
			$saler = M('eaterplanet_ecommerce_member')->field('avatar,username as nickname,member_id')->where( array('member_id' => $member['agentid'] ) )->find();
		}
		$this->saler = $saler;
		$this->is_showform = $is_showform;
		$this->display();
	}

	public function deleteuserlevel()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('eaterplanet_ecommerce_member_level')->field('id')->where( ' id in( ' . $id . ' ) '  )->select();

		foreach ($items as $item ) {

			M('eaterplanet_ecommerce_member')->where(  array('level_id' => $item['id']) )->save( array('level_id' => 0) );

			M('eaterplanet_ecommerce_member_level')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function deleteuser()
	{
		$id = I('request.id');

		if (empty($id)) {
			$ids = I('request.ids');

			$id = ((is_array($ids) ? implode(',', $ids) : 0));
		}


		$items = M('eaterplanet_ecommerce_member')->field('member_id')->where( array('member_id' => array('in', $id)) )->select();

		foreach ($items as $item ) {
			M('eaterplanet_ecommerce_member')->where( array('member_id' => $item['member_id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function deleteuserjia()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('eaterplanet_ecommerce_jiauser')->field('id')->where( 'id in( ' . $id . ' )' )->select();


		foreach ($items as $item ) {
			M('eaterplanet_ecommerce_jiauser')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function deleteusergroup()
	{

		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('eaterplanet_ecommerce_member_group')->where( "id in (".$id.")" )->select();

		foreach ($items as $item ) {

			M('eaterplanet_ecommerce_member')->where( array('groupid' => $item['id'] ) )->save( array('groupid' => 0) );

			M('eaterplanet_ecommerce_member_group')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));

	}

	/**
	 * 修改会员卡到期时间
	 */
	public function edit_expiretime(){
		$_GPC = I('request.');
		$id = $_GPC['id'];
		$is_post = $_GPC['is_post'];
		$member = M('eaterplanet_ecommerce_member')->where( array('member_id' => $id) )->find();
		$this->member = $member;
		if($is_post){
			$expire_time = strtotime($_GPC['expire_time']);
			$days = (strtotime(date('Y-m-d',$expire_time)) - strtotime(date('Y-m-d',$member['card_end_time']))) / 86400;
			M('eaterplanet_ecommerce_member')->where( array('member_id' => $id) )->save(array('card_end_time'=>$expire_time));

			$member_card = M('eaterplanet_ecommerce_member_card')->where( array('id' => $member['card_id']) )->find();
			$begin_time = time();
			$member_charge_flow_data = array();
			$member_charge_flow_data['order_sn'] = build_order_no($id);
			$member_charge_flow_data['member_id'] = $id;
			$member_charge_flow_data['pay_type'] = 'admin';
			$member_charge_flow_data['state'] = 1;
			$member_charge_flow_data['car_id'] = $member['card_id'];
			$member_charge_flow_data['expire_day'] = $days;
			$member_charge_flow_data['price'] = $member_card['price'];
			$member_charge_flow_data['order_type'] = 5; // 后台修改
			$member_charge_flow_data['begin_time'] = $begin_time;
			$member_charge_flow_data['end_time'] = $expire_time;
			$member_charge_flow_data['pay_time'] = $begin_time;
			$member_charge_flow_data['addtime'] = time();
			$order_id = M('eaterplanet_ecommerce_member_card_order')->add( $member_charge_flow_data );

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$this->display();
	}

	public function query_card(){
		$_GPC = I('request.');
		$id = $_GPC['id'];
		$member = M('eaterplanet_ecommerce_member')->where( array('member_id' => $id) )->find();
		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;
		$kwd = $_GPC['keyword'];
		$condition = ' 1=1 ';
		if (!empty($kwd)) {
			$condition .= ' AND ( `cardname` LIKE '.'"%' . $kwd . '%"'.' )';
		}
		$page =  isset($_GPC['page']) ? intval($_GPC['page']) : 1;
		$page = max(1, $page);
		$page_size = 10;
		$list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member_card  WHERE ' . $condition .
				' order by id desc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size );
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') .
				'eaterplanet_ecommerce_member_card WHERE ' . $condition );
		$total = $total_arr[0]['count'];
		$s_html = "<tr>"
				. "		<th>会员卡名称</th>"
				. "		<th>原价</th>"
				. "		<th>现价</th>"
				. "		<th>有效天数</th>"
				. "		<th>操作</th>"
				. "	</tr>";
		foreach ($list as &$value) {
			$s_html .= "<td>{$value['cardname']}</td>";
			$s_html .= "<td>{$value['orignprice']}</td>";
			$s_html .= "<td>{$value['price']}</td>";
			$s_html .= "<td>{$value['expire_day']}天</td>";
			$s_html .= '<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="btn-primary btn-sm choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td></tr>';
		}
		unset($value);
		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $s_html,'pager' => $pager) );
			die();
		}
		$url = 'user/query_card';
		$this->url = $url;
		$this->list = $list;
		$this->pager = $pager;
		$this->member = $member;
		$this->display();
	}

	/**
	 * 后台开通会员卡
	 */
	public function open_ucard(){
		$_GPC = I('request.');
		$card_id = $_GPC['card_id'];
		$id = $_GPC['member_id'];
		if(empty($id) || empty($card_id)){
			show_json(0, array('message' => '客户或会员卡数据错误'));
		}
		$member_card = M('eaterplanet_ecommerce_member_card')->where( array('id' => $card_id) )->find();
		$begin_time = time();
		$end_time = $begin_time + 86400 * $member_card['expire_day'];

		$member_charge_flow_data = array();
		$member_charge_flow_data['order_sn'] = build_order_no($id);
		$member_charge_flow_data['member_id'] = $id;
		$member_charge_flow_data['pay_type'] = 'admin';
		$member_charge_flow_data['state'] = 1;
		$member_charge_flow_data['car_id'] = $member_card['id'];
		$member_charge_flow_data['expire_day'] = $member_card['expire_day'];
		$member_charge_flow_data['price'] = $member_card['price'];
		$member_charge_flow_data['order_type'] = 4; // 后台购买
		$member_charge_flow_data['begin_time'] = $begin_time;
		$member_charge_flow_data['end_time'] = $end_time;
		$member_charge_flow_data['pay_time'] = $begin_time;
		$member_charge_flow_data['addtime'] = time();
		$order_id = M('eaterplanet_ecommerce_member_card_order')->add( $member_charge_flow_data );

		$mb_up_data = array();
		$mb_up_data['card_id'] = $member_card['id'];
		$mb_up_data['card_begin_time'] = $begin_time;
		$mb_up_data['card_end_time'] = $end_time;

		M('eaterplanet_ecommerce_member')->where( array('member_id' => $id ) )->save( $mb_up_data );

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}


	public function user_shenhe()
	{
		$_GPC = I('request.');

		$member_id = $_GPC['id'];
		$state = $_GPC['state'];


		if( $state == 1 )
		{
			M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->save( array('is_comsiss_audit' => 1) );

		}else{

			M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->save( array('is_comsiss_audit' => 0) );
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}


}
?>
