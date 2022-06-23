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
 * 处理订单相关内容
 */
namespace Home\Controller;

class TuanbonusController extends CommonController {
    protected function _initialize()
    {
    	parent::_initialize();
        $this->cur_page = 'tuanbonus';
    }


	public function apply()
	{
		//token
		$token = I('get.token');

	    $member_id = is_login();

		$result = array('code' => 0);
		$member_commiss_apply = M('member_commiss_apply')->where(array('member_id' =>$member_id, 'state' =>0))->find();

		if(!empty($member_commiss_apply)) {
			$result['code'] = 1;
			$result['msg'] = '您已经申请，等待审核!';
		} else {
			$data = array();
			$data['member_id'] = $member_id;
			$data['state'] = 0;
			$data['addtime'] = time();
			$res = M('member_commiss_apply')->add($data);
			if($res){

			} else {
				$result['code'] = 1;
				$result['msg'] = '提交失败';
			}
		}
		echo json_encode($result);
		die();

	}


	/**
		groupleaderindex
		分销商资料获取
	**/
	public function groupleaderindex()
	{

	    $member_id = is_login();

		$member_info = M('member')->where( array('member_id' => $member_id) )->find();


		$this->commiss_level_num = C('commiss_level_num');
		//查找一级数量
		$tuanyuan_arr = M('member')->field('member_id')->where( array('share_id' => $member_id) )->select();

		$tuanyuan_count = count($tuanyuan_arr);// $tuanyuan_count;

		//查找二级数量
		$second_tuanyuan_count = 0;
		$second_arr = array();
		if( !empty($tuanyuan_arr) )
		{
			$ids_arr = array();
			foreach($tuanyuan_arr as $val)
			{
				$ids_arr[] = $val['member_id'];
			}
			$second_arr =  M('member')->field('member_id')->where( array('share_id' => array('in', $ids_arr) ) )->select();
			$second_tuanyuan_count = count($second_arr);
		}
		$second_tuanyuan_count = $second_tuanyuan_count;

		//查找三级数量
		$three_tuanyuan_count = 0;

		if( !empty($second_arr) )
		{
			$ids_arr = array();
			foreach($second_arr as $val)
			{
				$ids_arr[] = $val['member_id'];
			}
			$three_tuanyuan_count =  M('member')->field('member_id')->where( array('share_id' => array('in', $ids_arr) ) )->count();

		}

		$three_tuanyuan_count = $three_tuanyuan_count;


		$member_commiss = M('member_commiss')->where( array('member_id' => $member_id) )->find();
		//$this->member_commiss = $member_commiss;

		$opencommiss_arr = M('config')->where( array('name' => 'opencommiss') )->find();
		$is_open_commiss = $opencommiss_arr['value'];

		$tuijian_ren = '平台';

		if($member_info['share_id'] > 0)
		{
			$tui_member = M('member')->field('uname')->where( array('member_id' => $member_info['share_id']) )->find();
			$tuijian_ren = $tui_member['uname'];
		}
		//$this->tuijian_ren = $tuijian_ren;

		$this->tuanyuan_count = $tuanyuan_count;
		$this->second_tuanyuan_count = $second_tuanyuan_count;
		$this->three_tuanyuan_count = $three_tuanyuan_count;
		$this->member_commiss = $member_commiss;
		$this->is_open_commiss = $is_open_commiss;
		$this->tuijian_ren = $tuijian_ren;
		$this->member_info = $member_info;
		$this->commiss_level_num = C('commiss_level_num');

		$need_data = array();
		$need_data['tuanyuan_count'] = $tuanyuan_count;
		$need_data['second_tuanyuan_count'] = $second_tuanyuan_count;
		$need_data['three_tuanyuan_count'] = $three_tuanyuan_count;
		$need_data['member_commiss'] = $member_commiss;
		$need_data['is_open_commiss'] = $is_open_commiss;
		$need_data['tuijian_ren'] = $tuijian_ren;
		$need_data['member_info'] = $member_info;
		$need_data['commiss_level_num'] = C('commiss_level_num');


		$this->display();
		//echo json_encode( array('code' =>0 , 'data' => $need_data) );
		//die();
	}

	/**
		获取佣金首页
	**/
	function tuanbonus_index()
    {

	    $member_id = is_login();

		$member_info = M('member')->where( array('member_id' => $member_id) )->find();



        //sum_member_commiss($where = array())
		$member_model = D('Home/Member');
		//今日收入
		$today_begin_time = strtotime( date('Y-m-d'.' 00:00:00') );
		$today_end_time = $today_begin_time + 86400;
		//$map['id'] = array('between','1,8');  $map['id'] = array('neq',100);
		$today_where = array();
		$today_where['member_id'] = $member_id;
		$today_where['state'] = array('neq',2);
		$today_where['addtime'] = array('between',$today_begin_time.','.$today_end_time);
		$today_commiss = $member_model->sum_member_commiss($today_where);

		//本月收入
		$month_begin_time = strtotime( date("Y-m-d ",mktime(0, 0 , 0,date("m"),1,date("Y"))).' 00:00:00' );
		$month_end_time = strtotime( date("Y-m-d ",mktime(23,59,59,date("m"),date("t"),date("Y"))).' 00:00:00' ) +86400;

		$month_where = array();
		$month_where['member_id'] = $member_id;
		$month_where['state'] = array('neq',2);
		$month_where['addtime'] = array('between',$month_begin_time.','.$month_end_time);
		$month_commiss = $member_model->sum_member_commiss($month_where);

		//累计收入
		$total_where = array();
		$total_where['member_id'] = $member_id;
		$total_where['state'] = array('neq',2);
		$total_commiss = $member_model->sum_member_commiss($total_where);

		//待确认收入
		$total_wait_where = array();
		$total_wait_where['member_id'] = $member_id;
		$total_wait_where['state'] = 0;
		$total_wait_commiss = $member_model->sum_member_commiss($total_wait_where);

		//可提现金额
		$member_commiss = M('member_commiss')->where( array('member_id' => $member_id) )->find();


		$this->today_commiss = round($today_commiss, 2);
		$this->month_commiss = round($month_commiss, 2);
		$this->total_commiss = round($total_commiss, 2);
		$this->total_wait_commiss = round($total_wait_commiss, 2);
		$can_tixian_money = 0;
		if(!empty($member_commiss)) {
			$can_tixian_money = $member_commiss['money'];
		}
		$this->can_tixian_money = round($can_tixian_money, 2);

		$comsiss_flag = $member_info['comsiss_flag'];
		if($member_info['comsiss_flag'] == 0)
		{
			//state
			$member_commiss_apply = M('member_commiss_apply')->where( array('member_id' =>$member_id, 'state' =>0) )
			->find();

			if(!empty($member_commiss_apply)) {
				$comsiss_flag = 2;
			}
		}
		$this->comsiss_flag = $comsiss_flag;

		$need_data = array();
		$need_data['today_commiss'] = round($today_commiss, 2);
		$need_data['month_commiss'] = round($month_commiss, 2);
		$need_data['total_commiss'] = round($total_commiss, 2);
		$need_data['total_wait_commiss'] = round($total_wait_commiss, 2);
		$need_data['can_tixian_money'] = round($can_tixian_money, 2);
		$need_data['comsiss_flag'] = $comsiss_flag;

		//echo json_encode( array('code' =>0, 'data' => $need_data ) );
		//die();

        $this->display('index');
    }

	/**
		获取账单详情
	**/
	public function listorder()
	{

	    $member_id = is_login();
		$per_page = 6;
	    $page = I('post.page',1);

	    $offset = ($page - 1) * $per_page;

	    $list = array();
		$where = '';
		$state = I('get.state',-1);
		//state
		if($state >=0)
		{
			$where = ' and mco.state = '.$state;
		}
		$this->state = $state;
		$sql = 'select mco.money,mco.child_member_id,mco.addtime,mco.state,o.order_status_id,o.order_num_alias,o.total,og.goods_id,og.quantity,og.name,mco.store_id,m.uname from  '.C('DB_PREFIX')."member_commiss_order as mco , ".C('DB_PREFIX')."order_goods as og, ".C('DB_PREFIX')."order as o  , ".C('DB_PREFIX')."member as m
			where  mco.order_id=og.order_id and mco.order_id = o.order_id and m.member_id=mco.child_member_id and mco.member_id=".$member_id." {$where} order by mco.id desc limit {$offset},{$per_page}";

		$list = M()->query($sql);

		$order_status_list = M('order_status')->select();
		$status_arr = array();
		foreach($order_status_list as $vv)
		{
			$status_arr[$vv['order_status_id']] = $vv['name'];
		}

		foreach($list as $key =>$val)
		{
			$val['total'] = round($val['total'],2);
			$val['money'] = round($val['money'],2);
			$val['status_name'] = $status_arr[$val['order_status_id']];
			$val['addtime'] = date('Y-m-d', $val['addtime']);
			$goods_info = M('goods')->field('image')->where( array('goods_id' => $val['goods_id']) )->find();
			$val['image']=resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			$list[$key] = $val;
		}

	    $this->list = $list;
		if($page > 1) {
		    $result = array('code' => 0);
		    if(!empty($list)) {
		        $result['code'] = 1;
		        $result['html'] = $this->fetch('Tuanbonus:listorder_ajax_fetch');
		    }
		    echo json_encode($result);
		    die();
		}

		//今日新增
		$today_begin_time = strtotime( date('Y-m-d'.' 00:00:00') );
		$today_end_time = $today_begin_time + 86400;
		$today_where = array();
		$today_where['member_id'] = $member_id;

		$today_where['addtime'] = array('between',$today_begin_time.','.$today_end_time);
		$today_count = M('member_commiss_order')->where( $today_where )->count();


		//昨日新增
		$yes_begin_time = $today_begin_time - 86400;
		$yes_end_time = $today_begin_time;

		$yes_where = array();
		$yes_where['member_id'] = $member_id;

		$yes_where['addtime'] = array('between',$yes_begin_time.','.$yes_end_time);
		$yes_count = M('member_commiss_order')->where( $yes_where )->count();

		//总订单量
		$total_where = array();
		$total_where['member_id'] = $member_id;
		$total_count = M('member_commiss_order')->where( $total_where )->count();



		$need_data = array();
		$need_data['today_count'] = $today_count;
		$need_data['yes_count'] = $yes_count;
		$need_data['total_count'] = $total_count;


		$this->today_count = $today_count;
		$this->yes_count = $yes_count;
		$this->total_count = $total_count;

		$this->display();
	}

	/**
		获取账单详情列表
	**/
	public function listorder_list()
	{

	    $member_id = is_login();

		$member_info = M('member')->where( array('member_id' => $member_id) )->find();

		$per_page = 6;
	    $page = I('get.page',1);

	    $offset = ($page - 1) * $per_page;

	    $list = array();
		$where = '';
		$state = I('get.state',-1);
		//state
		if($state >=0)
		{
			$where = ' and mco.state = '.$state;
		}
		$commiss_level_num = C('commiss_level_num');

		$where = ' and mco.level <= '.$commiss_level_num;
		//$commiss_level_num = C('commiss_level_num'); level

		$this->state = $state;
		$sql = 'select mco.level, mco.money,mco.child_member_id,mco.addtime,mco.state,o.order_status_id,o.order_num_alias,o.total,og.goods_id,og.quantity,og.name,mco.store_id,m.uname from  '.C('DB_PREFIX')."member_commiss_order as mco , ".C('DB_PREFIX')."order_goods as og, ".C('DB_PREFIX')."order as o  , ".C('DB_PREFIX')."member as m
			where  mco.order_id=og.order_id and mco.order_id = o.order_id and m.member_id=mco.child_member_id and mco.member_id=".$member_id." {$where} order by mco.id desc limit {$offset},{$per_page}";

		$list = M()->query($sql);

		$order_status_list = M('order_status')->select();
		$status_arr = array();
		foreach($order_status_list as $vv)
		{
			$status_arr[$vv['order_status_id']] = $vv['name'];
		}

		foreach($list as $key =>$val)
		{
			$val['total'] = round($val['total'],2);
			$val['money'] = round($val['money'],2);
			$val['status_name'] = $status_arr[$val['order_status_id']];
			$val['addtime'] = date('Y-m-d', $val['addtime']);
			$goods_info = M('goods')->field('image')->where( array('goods_id' => $val['goods_id']) )->find();
			$val['image']=C('SITE_URL'). resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			$list[$key] = $val;
		}

	    $this->list = $list;

		if(empty($list))
		{
			echo json_encode( array('code' => 1) );
			die();
		}else {
			echo json_encode( array('code' => 0, 'data' => $list) );
			die();
		}
	}

	/**
		检测是否绑定了银行卡
	**/
	public function check_tixian()
	{
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

	    $member_id = $weprogram_token['member_id'];

		$member_info = M('member')->where( array('member_id' => $member_id) )->find();

		$member_commiss = M('member_commiss')->where( array('member_id' => $member_id) )->find();


		if( empty($member_commiss['bankname']) || empty($member_commiss['bankaccount']) || empty($member_commiss['bankusername']))
		{

			echo json_encode( array('code' =>0) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}

	/**
		获取用户佣金信息
	**/
	public function get_tixian_info()
	{
		$token = I('get.token');

		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

	    $member_id = $weprogram_token['member_id'];

		$limit_money =  C('commiss_money_limit');
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();

		$member_commiss = M('member_commiss')->where( array('member_id' => $member_id) )->find();

		$member_commiss['limit_money'] = $limit_money;
		echo json_encode( array('code' =>0,'data' => $member_commiss) );
		die();
	}


	public function bindcard()
	{
		$bankusername = I('post.bankusername','','htmlspecialchars');
		$bankname = I('post.bankname','','htmlspecialchars');
		$bankaccount = I('post.bankaccount','','htmlspecialchars');

		$data = array();
		$data['bankusername'] = $bankusername;
		$data['bankname'] = $bankname;
		$data['bankaccount'] = $bankaccount;

		M('member_commiss')->where( array('member_id' => is_login() ) )->save($data);

		$this->redirect('Tuanbonus/tixian');
	}
	public function tixian()
	{
		$member_id = is_login();

		$member_info = M('member')->where( array('member_id' => $member_id) )->find();
		if($member_info['comsiss_flag'] != 1)
		{
			die();
		}
		$member_commiss = M('member_commiss')->where( array('member_id' => $member_id) )->find();

		$this->member_commiss = $member_commiss;
		if( empty($member_commiss['bankname']) || empty($member_commiss['bankaccount']) || empty($member_commiss['bankusername']))
		{
			$this->display();
		} else {

			$per_page = 5;
			$page = I('post.page',1);

			$offset = ($page - 1) * $per_page;

			$list = array();

			$sql = "select * from ".C('DB_PREFIX')."tixian_order
			where member_id=".$member_id." order by addtime desc limit {$offset},{$per_page}";
			$list = M()->query($sql);

			$this->list = $list;
			if($page > 1) {
				$result = array('code' => 0);
				if(!empty($list)) {
					$result['code'] = 1;
					$result['html'] = $this->fetch('Tuanbonus:tixianorder_ajax_fetch');
				}
				echo json_encode($result);
				die();
			}

			$commiss_money_limit = C('commiss_money_limit');
			$this->commiss_money_limit = $commiss_money_limit;

			$this->display('tixianorder');
		}
	}

	public function tixian_sub()
	{
		$member_id = is_login();

		$money = I('post.money',0,'floatval');
		$result = array('code' => 0,'msg' => '提现失败');
		$member_commiss = M('member_commiss')->where( array('member_id' => $member_id ) )->find();

		$member_info = M('member')->where( array('member_id' =>$member_id ) )->find();

		$commiss_money_limit = C('commiss_money_limit');

		if(!empty($commiss_money_limit) && $commiss_money_limit >0)
		{
			if($member_commiss['money'] < $commiss_money_limit)
			{
				$result['msg'] = '佣金满'.$commiss_money_limit.'才能提现';
				echo json_encode($result);
				die();
			}
		}

		if($money > 0 && $money <= $member_commiss['money'])
		{
			$data = array();
			$data['member_id'] = $member_id;
			$data['money'] = $money;
			$data['state'] = 0;
			$data['shentime'] = 0;
			$data['addtime'] = time();

			M('tixian_order')->add($data);

			$com_arr = array();
			$com_arr['money'] = $member_commiss['money'] - $money;
			$com_arr['dongmoney'] = $member_commiss['dongmoney'] + $money;

			M('member_commiss')->where( array('member_id' => $member_id) )->save($com_arr);

			$result['code'] = 1;
		}
		echo json_encode($result);
		die();
	}

	/**
		提现记录
	**/
	public function tixian_record()
	{
		$token = I('get.token');
		$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

	    $member_id = $weprogram_token['member_id'];
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();


		$per_page = 5;
		$page = I('get.page',1);

		$offset = ($page - 1) * $per_page;

		$list = array();

		$sql = "select * from ".C('DB_PREFIX')."tixian_order
		where member_id=".$member_id." order by addtime desc limit {$offset},{$per_page}";
		$list = M()->query($sql);

		foreach($list as $key => $val)
		{
			$val['addtime'] = date('Y-m-d', $val['addtime']);
			$list[$key] = $val;
		}

		if( !empty($list) )
		{
			echo json_encode( array('code' =>0, 'data'=>$list) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}
	}

	public function cons_member_common($member_id)
	{
		$member_common = array('member_id'=>$member_id,'qrcode_img' => '');
		M('member_common')->add($member_common);
		return $member_common;
	}
	/**
		团长二维码
	**/
	public function qrcode()
	{
		$member_id = is_login();
		$member_common = M('member_common')->where( array('member_id' => is_login()) )->find();


		if(empty($member_common))
		{
			$member_common  = $this->cons_member_common($member_id);
		}

		$is_tan = 1;

		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

		$hash_member_id = $hashids->encode($member_id);

		$url = C('SITE_URL')."index.php?s=/index/index/rmid/{$hash_member_id}";


		if(empty($member_common['qrcode_img']))
		{
			$content = $url;
			$target  = C('SITE_URL').'Uploads/image/'.C('user_qrcode_image');

			$user_qrcodebg_x = C('user_qrcode_x');
			$user_qrcodebg_y = C('user_qrcode_y');
			if(empty($user_qrcodebg_x))
			{
				$user_qrcodebg_x = 0;
			}
			if(empty($user_qrcodebg_y))
			{
				$user_qrcodebg_y = 0;
			}

			$new_image = get_compare_qrcode($content,$target,$user_qrcodebg_x,$user_qrcodebg_y);

			$uarray = array( 'qrcode_img'=>$new_image);

			M('member_common')->where( array('member_id' => $member_id) )->save($uarray);

			$member_common['qrcode_img'] = $new_image;
			cookie('qrcode_tan', 1);
		} else {
			$tan_time = cookie('qrcode_tan');
			if(empty($tan_time))
			{
				cookie('qrcode_tan', 1);
			}else
			{
				cookie('qrcode_tan', $tan_time+1);
				if($tan_time >=2)
				{
					$is_tan = 2;
				}
			}
			$new_image = $login_user['qrcode'];
		}

		$this->member_common = $member_common;
		$this->display();

	}

	function tuanyuan()
	{
		$member_id = is_login();
		//$member_id = 599;

		//$token = I('get.token');
		//$weprogram_token = M('weprogram_token')->field('member_id')->where( array('token' =>$token) )->find();

	   // $member_id = $weprogram_token['member_id'];
		$member_info = M('member')->where( array('member_id' => $member_id) )->find();

		$per_page = 6;
	    $page = I('get.page',1);

	    $offset = ($page - 1) * $per_page;
		//type 1 2 3
		$type = I('get.type',1);
	    //6

	    $list = array();

		if($type == 1)
		{
			$sql = 'select * from  '.C('DB_PREFIX')."member
				where share_id = ".$member_id." order by member_id desc limit {$offset},{$per_page}";


			$list = M()->query($sql);
			//var_dump($list, $sql);die();
		}else if( $type ==2 ){

			$sql = 'select member_id from  '.C('DB_PREFIX')."member
				where share_id = ".$member_id;
			$first_list = M()->query($sql);

			$list = array();
			if( !empty($first_list) )
			{
				$ids_arr = array();
				foreach( $first_list as $val )
				{
					$ids_arr[] = $val['member_id'];
				}
				$ids_str = implode(',', $ids_arr);

				$sql = 'select * from  '.C('DB_PREFIX')."member
					where share_id in (".$ids_str.") order by member_id desc limit {$offset},{$per_page}";
				$list = M()->query($sql);
			}


		} else if( $type ==3 ){
			$sql = 'select member_id from  '.C('DB_PREFIX')."member
				where share_id = ".$member_id;
			$first_list = M()->query($sql);

			$list = array();
			if( !empty($first_list) )
			{
				$ids_arr = array();
				foreach( $first_list as $val )
				{
					$ids_arr[] = $val['member_id'];
				}
				$ids_str = implode(',', $ids_arr);

				$sql = 'select member_id from  '.C('DB_PREFIX')."member
					where share_id in (".$ids_str.") ";
				$second_list = M()->query($sql);

				if( !empty($second_list) )
				{
					$ids_arr = array();
					foreach( $second_list as $val )
					{
						$ids_arr[] = $val['member_id'];
					}
					$ids_str = implode(',', $ids_arr);

					$sql = 'select * from  '.C('DB_PREFIX')."member
					where share_id in (".$ids_str.") order by member_id desc limit {$offset},{$per_page}";
					$list = M()->query($sql);
				}

			}
		}



		//{$member_info.uname}
		foreach($list as $key => $val)
		{
			$parent_name = M('member')->field('name')->where( array('member_id' => $val['share_id']) )->find();
			$val['parent_name'] = $parent_name['name'];
			$val['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
			$list[$key] = $val;
		}
		$this->type = $type;
		$this->list = $list;
		/**
		if( empty($list) )
		{
			echo json_encode( array('code' => 1) );
			die();
		} else{
			echo json_encode( array('code' =>0 , 'data' => $list) );
			die();
		}
		**/
		if($page > 1) {
		    $result = array('code' => 0);
		    if(!empty($list)) {
		        $result['code'] = 1;
		        $result['html'] = $this->fetch('Tuanbonus:tuanyuan_ajax_fetch');
		    }
		    echo json_encode($result);
		    die();
		}
		$this->display();

	}

	public function yongjing()
	{
		$per_page = 10;
	    $page = I('get.page',1);
		$gid = I('get.gid',0);

	    $offset = ($page - 1) * $per_page;

		$where = "";

		//danprice pin_price
		if( !empty($gid) && $gid >0 )
		{
			$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$gid} or class_id2 ={$gid} or class_id3 = {$gid}  ")->field('goods_id')->select();

			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}
			$ids_str = implode(',',$ids_arr);

			if( !empty($ids_str) )
			{
				$where .= " and a.goods_id in ({$ids_str}) ";
				//a.goods_id
			}
		}

		$sql = " SELECT a.goods_id,a.name,a.quantity,a.danprice,b.pin_price,a.commiss_one_dan_disc,( b.commiss_one_pin_disc * b.pin_price ) as pin_yong_money,( a.commiss_one_dan_disc * a.danprice ) as dan_yong_money, a.price,a.image
		FROM ".C('DB_PREFIX')."goods as a left join ".C('DB_PREFIX')."pin_goods as b on a.goods_id = b.goods_id  WHERE  (a.commiss_one_dan_disc >0 or b.commiss_one_pin_disc >0)  and a.status = 1 {$where} and a.quantity >0 order by dan_yong_money desc, pin_yong_money desc
		limit {$offset},{$per_page}";
	    /**
		$sql = 'select g.goods_id,g.name,g.quantity,g.pinprice,g.commiss_one_pin_disc,( g.pinprice * g.commiss_one_pin_disc ) as yong_money, g.price,g.image from  '.C('DB_PREFIX')."goods as g

	        where  g.status =1 and g.quantity > 0 and g.commiss_one_pin_disc >0   order by yong_money desc  limit {$offset},{$per_page}";
	    **/
		$list = M()->query($sql);

		foreach ($list as $k => $v) {
		    $v['image']=C('SITE_URL'). resize($v['image'], C('spike_thumb_width'), C('spike_thumb_height'));

			$pin_yong_money = round( $v['pin_yong_money'] /100 , 2);
			$dan_yong_money = round( $v['dan_yong_money'] /100 , 2);

			if( $pin_yong_money > $dan_yong_money )
			{
				$v['yong_money'] = $pin_yong_money;
				$v['price'] = $v['pin_price'];
			} else {
				$v['yong_money'] = $dan_yong_money;
				$v['price'] = $v['danprice'];
			}
			//"danprice":"0.04","pin_price":"0.01",
		    $list[$k] = $v;
		}

		if( empty($list) )
		{
			echo json_encode( array('code' => 1) );
			die();
		} else {
			echo json_encode( array('code' =>0, 'data' => $list) );
			die();
		}

		/**
		SELECT a.goods_id, a.commiss_one_dan_disc, b.commiss_one_pin_disc
		FROM `goods` as a left join pin_goods as b on a.goods_id = b.goods_id  WHERE  a.commiss_one_dan_disc >0  and a.status = 1 and a.quantity >0 order by a.goods_id desc
		**/
	}

}
