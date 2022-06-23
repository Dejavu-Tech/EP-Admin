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
namespace Seller\Model;

class FissionsharingModel{

	/**
	 *显示订单状态单位分页
	 show_fission_page
	 */
	public function show_fission_page($search){

		$sql = "select so.*,og.name as goods_name,m.uname,m.avatar from ".C('DB_PREFIX')."member_sharing_order as so ,".C('DB_PREFIX')."order_goods as og ,".C('DB_PREFIX')."member as m
				where so.order_goods_id = og.order_goods_id and so.member_id = m.member_id   ";
		/**
		$sql_count = "select * from ".C('DB_PREFIX')."member_sharing_order as so ,".C('DB_PREFIX')."order_goods as og ,".C('DB_PREFIX')."member as m
				where so.order_goods_id = og.order_goods_id and so.member_id = m.member_id   ";
		**/
	   $sql_count = "select count(so.id) as count from ".C('DB_PREFIX')."member_sharing_order as so ,".C('DB_PREFIX')."order_goods as og ,".C('DB_PREFIX')."member as m
				where so.order_goods_id = og.order_goods_id and so.member_id = m.member_id   ";

		/**
		$sql_count  = "select count(f.id) as count  from ".C('DB_PREFIX')."fissionbonus as f ,".C('DB_PREFIX')."member as m
				where f.member_id = m.member_id ";
		**/

		if(isset($search['name']) && !empty($search['name']) ){
			$sql.=" and m.uname like '%".$search['name']."%'";
			$sql_count.=" and m.uname like '%".$search['name']."%'";
		}


		$count_arr = M()->query($sql_count);
		$count = $count_arr[0]['count'];


		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' ORDER BY so.id desc LIMIT '.$Page->firstRow.','.$Page->listRows;


		$list=M()->query($sql);

		foreach($list as $key => $val)
		{
			//child_member_id buy_name
			$child_info = M('member')->field('uname')->where( array('member_id' => $val['child_member_id']) )->find();
			$val['buy_name'] = $child_info['uname'];

		    $list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	/**
		获取分享的链接路径
		type = page/goods
	**/
	public function get_sharing_type_info($member_id,$type, $goods_id = 0)
	{
		$link_info = M('fissionsharing_link')->where( array('type' => $type, 'goods_id' => $goods_id, 'member_id' => $member_id) )->find();

		if(empty($link_info))
		{
			$link_data = array();
			$link_data['type'] = $type;
			$link_data['goods_id'] = $goods_id;
			$link_data['member_id'] = $member_id;
			$link_data['share_one_id'] = 0;
			$link_data['share_two_id'] = 0;
			$link_data['share_three_id'] = 0;
			$link_data['modify_time'] = time();

			M('fissionsharing_link')->add($link_data);
			return $link_info;
		}else{
			return $link_info;
		}
	}

	/**
		有新的分享参数
		is_login(),$share_rmid
	**/
	public function newmember_param_share($member_id, $share_rmid)
	{
		$share_param = base64_decode($share_rmid);

		$share_arr = explode('_', $share_param);

		$share_time = $share_arr[5];

		$now_time = time();

		$effect_time_info = M('config')->where( array('name' => 'fissionsharing_effecttime') )->find();

		$effect_hour = $effect_time_info['value'];

		$limit_time = $share_time + intval( 3600 * $effect_hour );

		if( $effect_hour == 0 || ( $limit_time > $now_time ) )
		{
			$new_arr = array($share_arr[2],$share_arr[3],$share_arr[4]);

			$is_find = false;
			foreach($new_arr as $key => $val)
			{
				if($val == $member_id)
				{
					$is_find = true;
				}

				if($is_find)
				{
					$val =0;
				}

				$new_arr[$key] = $val;
			}

			if( $share_arr[0] == 'page')
			{
				$share_member_one  = $new_arr[0];
				$share_member_two  = $new_arr[1];
				$share_member_three  = $new_arr[2];

				$this->get_sharing_type_info($member_id,'page', 0);

				$save_data = array();
				$save_data['share_one_id'] = $share_member_one;
				$save_data['share_two_id'] = $share_member_two;
				$save_data['share_three_id'] = $share_member_three;
				$save_data['modify_time'] = time();
				M('fissionsharing_link')->where( array('member_id' => $member_id,'type' => 'page') )->save($save_data);

			} else if( $share_arr[0] == 'goods' ){
				$goods_id = $share_arr[1];
				$share_member_one  = $new_arr[0];
				$share_member_two  = $new_arr[1];
				$share_member_three  = $new_arr[2];

				$this->get_sharing_type_info($member_id,'goods', $goods_id);

				$save_data = array();
				$save_data['share_one_id'] = $share_member_one;
				$save_data['share_two_id'] = $share_member_two;
				$save_data['share_three_id'] = $share_member_three;
				$save_data['modify_time'] = time();
				M('fissionsharing_link')->where( array('member_id' => $member_id,'goods_id' => $goods_id,'type' => 'goods') )->save($save_data);

				$this->get_sharing_type_info($member_id,'page', 0);

				$save_data = array();
				$save_data['share_one_id'] = $share_member_one;
				$save_data['share_two_id'] = $share_member_two;
				$save_data['share_three_id'] = $share_member_three;
				$save_data['modify_time'] = time();
				M('fissionsharing_link')->where( array('member_id' => $member_id,'type' => 'page') )->save($save_data);
			}
		}

	}

	/**
		开始检测是否需要加入分享订单分佣表
	**/
	public function add_sharing_order($order_id,$goods_id,$order_goods_id,$member_id,$store_id )
	{
		//检测活动是否开启着
		$is_open_fissionsharing_info = M('config')->where( array('name' => 'is_open_fissionsharing') )->find();

		if( $is_open_fissionsharing_info['value'] == 1 )
		{
			//开始检测是否有上级分享人 fissionsharing_type  1 全站链接 2 商品链接
			$fissionsharing_type_info = M('config')->where( array('name' => 'fissionsharing_type') )->find();

			$link_info = array();

			if($fissionsharing_type_info['value'] == 1)
			{
				$link_info = M('fissionsharing_link')->where( array('type' => 'page', 'member_id' => $member_id) )->find();
			}else if($fissionsharing_type_info['value'] == 2)
			{
				$link_info = M('fissionsharing_link')->where( array('type' => 'goods','goods_id' => $goods_id, 'member_id' => $member_id) )->find();
			}

			//var_dump($order_id,$goods_id,$order_goods_id,$member_id,$store_id, $link_info);die();
			if(!empty($link_info))
			{
				$fissionsharing_level_info = M('config')->where( array('name' => 'fissionsharing_level') )->find();
				$level = $fissionsharing_level_info['value'];
				//检测分佣有多少
				$order_goods = M('order_goods')->where( array('order_goods_id' => $order_goods_id) )->find();

				if($level >= 1 && $link_info['share_one_id'] >0 && $order_goods['commiss_fen_one_money'] > 0)
				{
					$member_sharing_order_data = array();
					$member_sharing_order_data['member_id'] = $link_info['share_one_id'];
					$member_sharing_order_data['child_member_id'] = $member_id;
					$member_sharing_order_data['order_id'] = $order_id;
					$member_sharing_order_data['order_goods_id'] = $order_goods_id;
					$member_sharing_order_data['level'] = 1;
					$member_sharing_order_data['store_id'] = $store_id;
					$member_sharing_order_data['state'] = 3;
					$member_sharing_order_data['money'] = $order_goods['commiss_fen_one_money'];
					$member_sharing_order_data['addtime'] = time();
					M('member_sharing_order')->add($member_sharing_order_data);
					$this->check_account_sharing($link_info['share_one_id']);
				}
				if($level >= 2 && $link_info['share_two_id'] >0 && $order_goods['commiss_fen_two_money'] > 0)
				{
					$member_sharing_order_data = array();
					$member_sharing_order_data['member_id'] = $link_info['share_two_id'];
					$member_sharing_order_data['child_member_id'] = $member_id;
					$member_sharing_order_data['order_id'] = $order_id;
					$member_sharing_order_data['order_goods_id'] = $order_goods_id;
					$member_sharing_order_data['level'] = 2;
					$member_sharing_order_data['store_id'] = $store_id;
					$member_sharing_order_data['state'] = 3;
					$member_sharing_order_data['money'] = $order_goods['commiss_fen_two_money'];
					$member_sharing_order_data['addtime'] = time();
					M('member_sharing_order')->add($member_sharing_order_data);
					$this->check_account_sharing($link_info['share_two_id']);
				}
				if($level >= 3 && $link_info['share_three_id'] >0 && $order_goods['commiss_fen_three_money'] > 0)
				{
					$member_sharing_order_data = array();
					$member_sharing_order_data['member_id'] = $link_info['share_three_id'];
					$member_sharing_order_data['child_member_id'] = $member_id;
					$member_sharing_order_data['order_id'] = $order_id;
					$member_sharing_order_data['order_goods_id'] = $order_goods_id;
					$member_sharing_order_data['level'] = 3;
					$member_sharing_order_data['store_id'] = $store_id;
					$member_sharing_order_data['state'] = 3;
					$member_sharing_order_data['money'] = $order_goods['commiss_fen_three_money'];
					$member_sharing_order_data['addtime'] = time();
					M('member_sharing_order')->add($member_sharing_order_data);
					$this->check_account_sharing($link_info['share_three_id']);
				}
			}
		}

	}


	/**
		只有拼团成功或者单独购买已经发货的 ， 订单退款取消佣金
	**/
	public function back_order_commiss_money($order_id)
	{
		$member_commiss_order_list = M('member_sharing_order')->where( array('order_id' =>$order_id,'state' => 1 ) )->select();

		if(!empty($member_commiss_order_list))
		{
		   foreach($member_commiss_order_list as $member_commiss_order)
		   {
			   //分佣订单
			   M('member_sharing_order')->where( array('id' =>$member_commiss_order['id'] ) )->save( array('state' => 2) );
			   M('member_sharing')->where( array('member_id' => $member_commiss_order['member_id']) )->setDec('money',$member_commiss_order['money']);
			 }
		}
	}

	/**
		赠送佣金订单
	**/
	public function send_order_commiss_money($order_id)
	{
		$member_commiss_order_list = M('member_sharing_order')->where( array('order_id' =>$order_id,'state' => 3 ) )->select();

		if(!empty($member_commiss_order_list))
		{
		   foreach($member_commiss_order_list as $member_commiss_order)
		   {
			   $this->check_account_sharing($member_commiss_order['member_id']);
			   //分佣订单
				M('member_sharing_order')->where( array('id' =>$member_commiss_order['id'] ) )->save( array('state' => 0) );

			  // M('member_sharing')->where( array('member_id' => $member_commiss_order['member_id']) )->setInc('money',$member_commiss_order['money']);
			 }
		}
	}

	/**
		赠送佣金 到账户
	**/
	public function send_order_commiss_money_do($order_id)
	{
		$member_commiss_order_list = M('member_sharing_order')->where( array('order_id' =>$order_id,'state' => 0 ) )->select();

		if(!empty($member_commiss_order_list))
		{
		   foreach($member_commiss_order_list as $member_commiss_order)
		   {
			   $this->check_account_sharing($member_commiss_order['member_id']);
			   //分佣订单
			   M('member_sharing_order')->where( array('id' =>$member_commiss_order['id'] ) )->save( array('state' => 1) );
			   M('member_sharing')->where( array('member_id' => $member_commiss_order['member_id']) )->setInc('money',$member_commiss_order['money']);
			 }
		}
	}

	/**
		检测是否已经有账户
	**/
	public function check_account_sharing($member_id)
	{
		$member_sharing_info = M('member_sharing')->where( array('member_id' => $member_id) )->find();

		if( empty($member_sharing_info) )
		{
			$bankname = '';
			$bankaccount = '';
			$bankusername = '';

			$commiss_info = M('member_commiss')->where( array('member_id' => $member_id) )->find();

			if( !empty($commiss_info) )
			{
				$bankname = $commiss_info['bankname'];
				$bankaccount = $commiss_info['bankaccount'];
				$bankusername = $commiss_info['bankusername'];
			}

			$member_sharing_data = array();
			$member_sharing_data['member_id'] = $member_id;
			$member_sharing_data['money'] = 0;
			$member_sharing_data['dongmoney'] = 0;
			$member_sharing_data['getmoney'] = 0;
			$member_sharing_data['bankname'] = $bankname;
			$member_sharing_data['bankaccount'] = $bankaccount;
			$member_sharing_data['bankusername'] = $bankusername;
			M('member_sharing')->add($member_sharing_data);
		}

	}

}
?>
