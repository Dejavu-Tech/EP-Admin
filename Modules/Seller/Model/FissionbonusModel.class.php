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
namespace Seller\Model;

class FissionbonusModel{

	/**
	 *显示订单状态单位分页
	 */
	public function show_fission_page($search){

	    $sql = "select * from ".C('DB_PREFIX')."fissionbonus as f ,".C('DB_PREFIX')."member as m
				where f.member_id = m.member_id ";

		$sql_count  = "select count(f.id) as count  from ".C('DB_PREFIX')."fissionbonus as f ,".C('DB_PREFIX')."member as m
				where f.member_id = m.member_id ";

		if(isset($search['name']) && !empty($search['name']) ){
			$sql.=" and m.uname like '%".$search['name']."%'";
			$sql_count.=" and m.uname like '%".$search['name']."%'";
		}


		$count_arr = M()->query($sql_count);
		$count = $count_arr[0]['count'];


		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' ORDER BY f.id desc LIMIT '.$Page->firstRow.','.$Page->listRows;


		$list=M()->query($sql);

		foreach($list as $key => $val)
		{
			$money = M('fissionbonus_order')->where( array('fissionbonus_id' => $val['id']) )->sum('money');
			$order_list = M('fissionbonus_order')->where( array('fissionbonus_id' => $val['id']) )->order('id asc')->select();
			foreach($order_list as $kk => $vv)
			{
				$mb_info =  M('member')->field('uname,avatar')->where( array('member_id' => $vv['member_id']) )->find();
				$vv['mb_info'] = $mb_info;
				$order_list[$kk] = $vv;
			}

		    $val['money'] = $money;
		    $val['order_list'] = $order_list;

		    $list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	/**
		开启一个今日签到
	**/
	public function open_new_bonus($member_id)
	{
		$end_time = strtotime( date('Y-m-d').' 00:00:00' ) + 86400;

		$bonus_game_time_arr =  M('config')->where( array('name' =>'bonus_game_time') )->find();
		$bonus_count_arr =  M('config')->where( array('name' =>'bonus_count') )->find();
		$bonus_money_arr =  M('config')->where( array('name' =>'bonus_money') )->find();


		$bonus_ser_arr = $this->_rand_fen_bonus($bonus_money_arr['value'], $bonus_count_arr['value']);

		$fissionbonus_data = array();
		$fissionbonus_data['member_id'] = $member_id;
		$fissionbonus_data['count'] = $bonus_count_arr['value'];
		$fissionbonus_data['bonus_ser'] = serialize($bonus_ser_arr);
		$fissionbonus_data['state'] = 0;
		$fissionbonus_data['begin_time'] = time();
		$fissionbonus_data['end_time'] = $end_time;

		//$fissionbonus_data['end_time'] = time()+3600 * $bonus_game_time_arr['value'];

		$res =  M('fissionbonus')->add($fissionbonus_data);
		return $res;
	}

	/**
		将签到金分拆成等分
	**/
	private function _rand_fen_bonus($money_total = 1, $personal_num = 2)
	{
		//$money_total=100;
		//$personal_num=10;
		$min_money=0.01;
		$money_right=$money_total;
		$randMoney=[];
		for($i=1;$i<=$personal_num;$i++){
			if($i== $personal_num){
				$money=$money_right;
			}else{
				$max=$money_right*100 - ($personal_num - $i ) * $min_money *100;
				$money= rand($min_money*100,$max) /100;
				$money=sprintf("%.2f",$money);
				}
				$randMoney[]=$money;
				$money_right=$money_right - $money;
				$money_right=sprintf("%.2f",$money_right);
		}
		sort($randMoney);
		return $randMoney;
	}

	/**
		对订单开始分红包
	**/
	public function get_fissionbonus_order($bonus_id, $member_id)
	{

		$fissionbonus_info = M('fissionbonus')->where( array('id' => $bonus_id) )->find();

		if($fissionbonus_info['state'] == 1)
		{
			return 0;
		}else{
			$bonus_ser = unserialize($fissionbonus_info['bonus_ser']);

			$is_self = 0;
			if($fissionbonus_info['member_id'] == $member_id)
			{
				$is_self = 1;
			}

			$count_order = M('fissionbonus_order')->where( array('fissionbonus_id' => $bonus_id) )->count();

			$money = $bonus_ser[ $count_order ];

			$is_notify = 0;

			if($is_self == 1)
			{
				$is_notify = 1;
			}

			//判断是否两次

			$fissionbonus_order_info = M('fissionbonus_order')->where( array('fissionbonus_id' =>$bonus_id,'member_id' =>$member_id ) )->find();

			if( !empty($fissionbonus_order_info) )
			{
				return 0;
			}else{
				$fissionbonus_order_data = array();
				$fissionbonus_order_data['fissionbonus_id'] = $bonus_id;
				$fissionbonus_order_data['member_id'] = $member_id;
				$fissionbonus_order_data['is_self'] = $is_self;
				$fissionbonus_order_data['is_notify'] = $is_notify;

				$fissionbonus_order_data['money'] = $money;
				$fissionbonus_order_data['addtime'] = time();

				M('fissionbonus_order')->add($fissionbonus_order_data);

				//开始赠送金额
				if( $is_self == 1 )
				{
					$this->send_member_fissionbonus($member_id,$member_id, $bonus_id, $money,1);
				}else{
					//$this->send_member_fissionbonus($member_id, $bonus_id, $money,3); send_member_id


					$this->send_member_fissionbonus($fissionbonus_info['member_id'],$member_id, $bonus_id, $money,2);
				}

				$count_order = M('fissionbonus_order')->where( array('fissionbonus_id' => $bonus_id) )->count();
				if($fissionbonus_info['count']<=$count_order)
				{
					M('fissionbonus')->where( array('id' => $bonus_id) )->save( array('state' => 1) );
				}
				return $money;
			}
		}
	}

	/**
		给客户赠送红包金额
		1:自己签到，2：别人帮忙金额得到 3，给别人点赞得到金额
	**/
	private function send_member_fissionbonus($member_id =0,$send_member_id=0,$fissionbonus_id =0, $money = 0,$type=1)
	{
		//fissionbonus_flow
		$fissionbonus_flow_data = array();
		$fissionbonus_flow_data['fissionbonus_id'] = $fissionbonus_id;
		$fissionbonus_flow_data['member_id'] = $member_id;
		$fissionbonus_flow_data['send_member_id'] = $send_member_id;
		$fissionbonus_flow_data['money'] = $money;
		$fissionbonus_flow_data['type'] = $type;
		$fissionbonus_flow_data['addtime'] = time();
		$flow_id = M('fissionbonus_flow')->add($fissionbonus_flow_data);


		D('Home/member')->charge_member_account($member_id, $money, 6, $flow_id);



	}





}
?>
