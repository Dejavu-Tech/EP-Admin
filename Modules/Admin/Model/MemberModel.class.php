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
namespace Admin\Model;
class MemberModel{
	/**
	 *显示分页
	 */
	public function show_member_page($search){

		$sql="select * from ".C('DB_PREFIX')."member where 1 ";

		if(isset($search['name'])){
			$sql.=" and uname like '%".$search['name']."%'";
		}
		if(isset($search['email'])){
			$sql.=" and email='".$search['email']."'";
		}
		if(isset($search['tel'])){
			$sql.=" and telephone='".$search['tel']."'";
		}

		if(isset($search['level_id'])){
			$sql.=" and level_id='".$search['level_id']."'";
		}

		//level_id
		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by member_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		$member_model = D('Home/Member');

		$level_data=$this->show_member_level();
		 $member_default_levelname_info = M('config')->where( array('name' => 'member_default_levelname') )->find();
		 $member_defualt_discount_info = M('config')->where( array('name' => 'member_defualt_discount') )->find();

		 $default = array('id'=>'0', 'level' => 0,'levelname' => $member_default_levelname_info['value'],'discount' => $member_defualt_discount_info['value']);

		 array_unshift($level_data['list'], $default );
		 $need_level = array();

		 foreach($level_data['list'] as $vv)
		 {
			 $need_level[ $vv['id'] ] = $vv['levelname'];
		 }

		foreach($list as $key => $val)
		{
			//comsiss_flag
			if($val['comsiss_flag'] == 1)
			{
				$member_commiss = M('member_commiss')->where( array('member_id' => $val['member_id'] ) )->find();
				$total_wait_where = array();
				$total_wait_where['member_id'] = $val['member_id'];
				$total_wait_where['state'] = 0;
				$total_wait_commiss = $member_model->sum_member_commiss($total_wait_where);

				$all_commiss_money = $total_wait_commiss + $member_commiss['money'] +$member_commiss['getmoney']+$member_commiss['dongmoney'];
				$member_commiss['all_commiss_money'] = $all_commiss_money;
				$member_commiss['wait_money'] = $total_wait_commiss;
				$val['member_commiss'] = $member_commiss;
			}
			$val['level_name'] = $need_level[ $val['level_id'] ];
			//

			$list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function levelconfig($data)
	{

		M('config')->where( array('name' => 'member_level_is_open') )->save( array('value' => $data['member_level_is_open']) );

		return array(
			'status'=>'success',
			'message'=>'操作成功',
			'jump'=>U('Member/levelconfig')
		);
	}

	public function check_updategrade( $member_id )
	{
		$pay_money = M('eaterplanet_ecommerce_order')->where("member_id={$member_id} and order_status_id in(6,11)")->sum('total+shipping_fare-voucher_credit-fullreduction_money');

		$pay_money = empty($pay_money) ? 0 : $pay_money;

		$mb_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();
		if( !empty($mb_info) )
        {
		$next_level = M('eaterplanet_ecommerce_member_level')->where( "id >".$mb_info['level_id']." and is_auto_grade =1 " )->order('id asc')->find();

		if( !empty($next_level) && $pay_money >= $next_level['level_money'] )
		{
			M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->save(  array('level_id' => $next_level['id']) );
		}
	}

	}
	/**
		更改客户余额
	**/
	public function sendMemberMoneyChange($member_id, $num, $changetype, $remark='')
	{
		$member_info = M('eaterplanet_ecommerce_member')->field('account_money')->where( array('member_id' => $member_id) )->find();

		$account_money = $member_info['account_money'];


		$flow_data = array();
		$flow_data['member_id'] = $member_id;
		$flow_data['trans_id'] = '';

		//0，未支付，1已支付,3余额付款，4退款到余额，5后台充值 6 后台扣款 11兑换卡兑换

		//增加 operate_end_yuer
		if($changetype == 0)
		{
			$up_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_member set account_money = account_money+ ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '5';
		}
		else if($changetype == 1)
		{
		//减少
			$up_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_member set account_money = account_money - ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '8';
		}else if($changetype == 2){
		//最终积分
			$up_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_member set account_money = ".$num ." where  member_id=".$member_id;
			if($account_money >= $num)
			{
				$flow_data['state'] = '8';
				$num = $account_money - $num;
			}else{
				$flow_data['state'] = '5';
				$num = $num - $account_money;
			}
		}else if( $changetype == 9 )
		{
			$up_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_member set account_money = account_money+ ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '9';
		}
		else if( $changetype == 10 )
		{
			$up_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_member set account_money = account_money+ ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '10';
		}
		else if( $changetype == 20 )
		{
            //11兑换卡兑换
			$up_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_member set account_money = account_money+ ".$num ." where  member_id=".$member_id;
			$flow_data['state'] = '20';
		}

		M()->execute($up_sql);


		$member_info = M('eaterplanet_ecommerce_member')->field('account_money')->where( array('member_id' => $member_id) )->find();
		$account_money = $member_info['account_money'];

		$flow_data['money'] = $num;

		$flow_data['operate_end_yuer'] = $account_money;

		$flow_data['remark'] = $remark;

		$flow_data['charge_time'] = time();
		$flow_data['add_time'] = time();


		M('eaterplanet_ecommerce_member_charge_flow')->add($flow_data);
	}

	/**
		更新客户积分
	**/
	public function sendMemberPointChange($member_id,$num, $changetype ,$remark ='',$type='system_add', $order_id =0 ,$order_goods_id = 0)
	{
		//$profile['member_id'], $num, $changetype, $remark

		$member_info = M('eaterplanet_ecommerce_member')->field('score')->where( array('member_id' => $member_id) )->find();
		$member_score = $member_info['score'];


		$flow_data = array();
		$flow_data['member_id'] = $member_id;
		$flow_data['type'] = $type;
		$flow_data['order_id'] = $order_id;
		$flow_data['order_goods_id'] = $order_goods_id;

		//增加
		if($changetype == 0)
		{
			$up_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_member set score = score+ ".$num ." where  member_id=".$member_id;
			$flow_data['in_out'] = 'in';
		}else if($changetype == 1)
		{
		//减少
			$up_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_member set score = score - ".$num ." where  member_id=".$member_id;
			$flow_data['in_out'] = 'out';
		}else if($changetype == 2){
		//最终积分
			$up_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_member set score = ".$num ." where  member_id=".$member_id;
			if($member_score >= $num)
			{
				$flow_data['in_out'] = 'out';
				$num = $member_score - $num;
			}else{
				$flow_data['in_out'] = 'in';
				$num = $num - $member_score;
			}
		}
		$flow_data['score'] = $num;
		$flow_data['state'] = 1;
		$flow_data['remark'] = $remark;
		$flow_data['addtime'] = time();

		M()->execute($up_sql);

		$member_score_info = M('eaterplanet_ecommerce_member')->field('score')->where( array('member_id' => $member_id) )->find();

		$flow_data['after_operate_score'] = $member_score_info['score'];



		M('eaterplanet_ecommerce_member_integral_flow')->add( $flow_data );
	}
	///end

	public function addlevel($data)
	{
		//member_level id
		/**
			array(4) { ["level"]=> string(1) "1" ["levelname"]=> string(7) "等级1" ["discount"]=> string(2) "10" ["send"]=> string(6) "提交" }
		**/

		// M('member_level')->where()->save();

		if( isset($data['id']) && !empty($data['id']) )
		{
			if($data['id'] == 'default')
			{

				M('config')->where( array('name' => 'member_default_levelname') )->save( array('value' => $data['levelname']) );
				 M('config')->where( array('name' => 'member_defualt_discount') )->save( array('value' => $data['discount']) );

			}else{

				M('member_level')->where( array('id' =>$data['id']) )->save($data);
			}

		}else{
			M('member_level')->add($data);
		}

		return array(
				'status'=>'success',
				'message'=>'操作成功',
				'jump'=>U('Member/level')
		);

	}

	public function show_member_level()
	{
		$sql="select * from ".C('DB_PREFIX')."member_level where 1 ";



		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by level asc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		/**
		foreach($list as $key => $val)
		{
			$list[$key] = $val;
		}
		**/

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}

	public function show_member_charge_page($search)
	{
		//member_id state

		$sql="select * from ".C('DB_PREFIX')."member_charge_flow where 1 ";


		if(isset($search['member_id'])){
			$sql.=" and member_id='".$search['member_id']."'";
		}
		if(isset($search['state'])){
			$sql.=" and state= ".$search['state'];
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		foreach( $list as $key =>$val )
		{
			if($val['state'] == 6)
			{
				$trans_id = $val['trans_id'];
				$flow_info =  M('fissionbonus_flow')->where( array('id' => $trans_id) )->find();
				//type
				if( $flow_info['type'] != 1)
				{
					//send_member_id
					$t_info =  M('member')->where( array('member_id' => $flow_info['send_member_id']) )->find();
					$val['tip'] = '(好友'.$t_info['uname'].'帮忙签到)';
				}
			}

			$list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}
	public function show_member_down_page($search)
	{
		$member_model = D('Home/Member');

		if($search['type'] == 1)
		{
			$sql="select m.*,a.telephone as tel from ".C('DB_PREFIX')."member as m left join ".C('DB_PREFIX')."address as a
						on m .member_id = a.member_id where 1 ";

			$sql .= " and m.share_id = ".$search['member_id']."";

			$sql .= " group by m.member_id";

			$count=count(M()->query($sql));

			$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
			$show  = $Page->show();// 分页显示输出

			$sql.=' order by m.member_id asc LIMIT '.$Page->firstRow.','.$Page->listRows;

			$list=M()->query($sql);

		}else if( $search['type'] ==2 ){

			$sql = 'select member_id from  '.C('DB_PREFIX')."member
				where share_id = ".$search['member_id'];
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


				$sql="select m.*,a.telephone as tel from ".C('DB_PREFIX')."member as m left join ".C('DB_PREFIX')."address as a
						on m .member_id = a.member_id where 1 ";

				$sql .= " and m.share_id in (".$ids_str.")";

				$sql .= " group by m.member_id";

				$count=count(M()->query($sql));

				$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
				$show  = $Page->show();// 分页显示输出

				$sql.=' order by m.member_id asc LIMIT '.$Page->firstRow.','.$Page->listRows;

				$list=M()->query($sql);

			}


		} else if( $search['type'] == 3 ){
			$sql = 'select member_id from  '.C('DB_PREFIX')."member
				where share_id = ".$search['member_id'];
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


					$sql="select m.*,a.telephone as tel from ".C('DB_PREFIX')."member as m left join ".C('DB_PREFIX')."address as a
						on m .member_id = a.member_id where 1 ";

					$sql .= " and m.share_id in (".$ids_str.")";

					$sql .= " group by m.member_id";

					$count=count(M()->query($sql));

					$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
					$show  = $Page->show();// 分页显示输出

					$sql.=' order by m.member_id asc LIMIT '.$Page->firstRow.','.$Page->listRows;

					$list=M()->query($sql);


				}

			}
		}

		foreach($list as $key => $val)
		{
			//comsiss_flag
			if($val['comsiss_flag'] == 1)
			{
				$member_commiss = M('member_commiss')->where( array('member_id' => $val['member_id'] ) )->find();
				$total_wait_where = array();
				$total_wait_where['member_id'] = $val['member_id'];
				$total_wait_where['state'] = 0;
				$total_wait_commiss = $member_model->sum_member_commiss($total_wait_where);

				$all_commiss_money = $total_wait_commiss + $member_commiss['money'] +$member_commiss['getmoney']+$member_commiss['dongmoney'];
				$member_commiss['all_commiss_money'] = $all_commiss_money;
				$member_commiss['wait_money'] = $total_wait_commiss;
				$val['member_commiss'] = $member_commiss;
			}
			$list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}
	public function show_jiamember_page($search){

		$sql="select * from ".C('DB_PREFIX')."jiauser where 1 ";

		if(isset($search['name'])){
			$sql.=" and username like '%".$search['name']."%'";
		}


		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function show_applymembercomiss_page($search){

		$sql="select m.uname,m.member_id,m.create_time,txo.state as state,txo.addtime,txo.money,txo.id from ".C('DB_PREFIX')."member as m,".C('DB_PREFIX')."tixian_order as txo  where  m.member_id = txo.member_id  ";

		if(isset($search['name'])){
			$sql.=" and m.uname like '%".$search['name']."%'";
		}
		if(isset($search['email'])){
			$sql.=" and m.email='".$search['email']."'";
		}
		if(isset($search['tel'])){
			$sql.=" and m.telephone='".$search['tel']."'";
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by txo.state asc, txo.addtime desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function show_fen_applymembercomiss_page($search){

		$sql="select m.uname,m.member_id,m.create_time,txo.state as state,txo.addtime,txo.money,txo.id from
		".C('DB_PREFIX')."member as m,".C('DB_PREFIX')."fen_tixian_order as txo  where  m.member_id = txo.member_id  ";

		if(isset($search['name'])){
			$sql.=" and m.uname like '%".$search['name']."%'";
		}
		if(isset($search['email'])){
			$sql.=" and m.email='".$search['email']."'";
		}
		if(isset($search['tel'])){
			$sql.=" and m.telephone='".$search['tel']."'";
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by txo.state asc, txo.addtime desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function show_applymember_page($search){

		$sql="select m.uname,m.member_id,m.create_time,mc.state as state,mc.addtime,mc.id from ".C('DB_PREFIX')."member as m,".C('DB_PREFIX')."member_commiss_apply as mc  where 1 and m.member_id = mc.member_id and mc.state =0 ";

		if(isset($search['name'])){
			$sql.=" and m.uname like '%".$search['name']."%'";
		}
		if(isset($search['email'])){
			$sql.=" and m.email='".$search['email']."'";
		}
		if(isset($search['tel'])){
			$sql.=" and m.telephone='".$search['tel']."'";
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by mc.state asc, mc.addtime desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	function add_member($data){
			if(empty($data['uname'])){
				$error="用户名不能为空！！";
			}elseif(M('Member')->getByUname(trim($data['uname']))){
				$error="用户名已经存在！！";
			}elseif(empty($data['email'])){
				$error="邮箱不能为空！！";
			}elseif(M('Member')->getByEmail($data['email'])){
				$error="邮箱已经存在！！";
			}elseif(empty($data['pwd'])){
				$error="密码不能为空！！";
			}

			if($error){
				return array(
					'status'=>'back',
					'message'=>$error
				);
			}

			$data['pwd']  =think_ucenter_encrypt($data['pwd'],C('PWD_KEY'));
			$data['create_time']  =time();
			$data['status']  =1;
			if(M('member')->add($data)){
				return array(
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('Member/index')
				);
			}else{
				return array(
				'status'=>'back',
				'message'=>'新增失败'

				);
			}
	}

	function info($id){
		$member=M('member')->find($id);
		$address=M('address')->where(array('member_id'=>$id))->select();

		return array(
			'info'=>$member,
			'address'=>$address
		);
	}

	function edit_info($d){
		$data=$d;
		if(empty($d['pwd']))
		{
			unset($data['pwd']);
		}else{
			$data['pwd']=think_ucenter_encrypt($d['pwd'],C('PWD_KEY'));
		}

		$integral_model =  D('Seller/Integral');

		$member_info = M('member')->field('score')->where(array('member_id'=>$d['member_id']))->find();
		if($d['score'] != $member_info['score'])
		{
			$del_score = $d['score'] - $member_info['score'];
			if($del_score < 0)
			{
				//系统扣除 system_add system_del
				//
				$integral_model->charge_member_score($d['member_id'], -$del_score,'out', 'system_del');
			}else{
				//系统奖励
				//$integral_model->charge_member_score( $member_id, $score,$in_out, $type, $order_id=0)
				$integral_model->charge_member_score($d['member_id'], $del_score,'in', 'system_add');
			}
		}
		unset($d['score']);

		$r=M('member')->where(array('member_id'=>$d['member_id']))->save($data);

		$http_refer = cookie('http_refer');
		if(empty($http_refer))
		{
			$http_refer = U('Member/index');
		}


		return array(
			'status'=>'success',
			'message'=>'修改成功',
			'jump'=>$http_refer
		);


	}

}
?>
