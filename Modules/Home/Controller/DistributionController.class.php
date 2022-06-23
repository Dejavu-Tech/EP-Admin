<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://www.eaterplanet.com/
 * @copyright Copyright (c) 2019-2021 ch871.com.
 * @license   https://www.eaterplanet.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Home\Controller;

class DistributionController extends CommonController {

	public function main()
	{
		echo json_encode( array('code' =>0) );
		die();
	}

	public function get_instruct()
	{

		$communitymember_apply_page = D('Home/Front')->get_config_by_name('communitymember_apply_page');

		$communitymember_apply_page = htmlspecialchars_decode($communitymember_apply_page);

		echo json_encode( array('code' => 0, 'content' => $communitymember_apply_page) );
		die();
	}

	/**
		提交申请表单
		@param 注意，申请表单的时候，需要判断是否满足其他条件了。如果已经满足了，那么就可以直接申请了
		提交参数：{token:'xxx', data: [{type:input,name:'姓名',value="123"},{type:radio,name:'姓名',value="123"}] }

	**/
	public function sub_distribut_form()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$data = json_decode( htmlspecialchars_decode( $_GPC['data']) ,true);

		$commiss_formcontent =  serialize( $data );

		//M('eaterplanet_ecommerce_member')->where(  array('member_id' => $member_id ) )->save( array('is_writecommiss_form' => 1,'commiss_formcontent' => $commiss_formcontent ) );

		//判断是否需要审核
		$commiss_become_condition = D('Home/Front')->get_config_by_name('commiss_become_condition');

		if( empty($commiss_become_condition) || $commiss_become_condition == 0 )
		{
			//不需要审核，那么直接升级为分销了
		    //M('eaterplanet_ecommerce_member')->where( array('share_id' => $member_id, 'agentid' => 0 ) )->save( array('agentid' => $member_id ) );

			M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->save( array('is_writecommiss_form' => 1,'commiss_formcontent' => $commiss_formcontent ) );


			D('Home/Commission')->become_commiss_member($member_id);

			echo json_encode( array('code' =>0, 'msg' =>'提交成功') );
			die();
		}else{
			//需要审核，成为分销，待审核状态
			M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->save( array('comsiss_flag' => 1,'is_writecommiss_form' => 1,'commiss_formcontent' => $commiss_formcontent ) );

			//将未 挪动上级的客户归到当前客户的下级去
			M('eaterplanet_ecommerce_member')->where( array('share_id' => $member_id ) )->save( array('agentid' => $member_id ) );

			echo json_encode( array('code' =>0, 'msg' =>'申请成功，平台将尽快审核') );
			die();
		}


		echo json_encode( array('code' =>0, 'msg' =>'提交成功') );
		die();
	}

	/**
		客户申请分销按钮确认
	**/
	public function sub_commission_info()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		//开始判断
		$share_member_count = M('eaterplanet_ecommerce_member')->where( "share_id={$member_id} and (agentid =0 or agentid={$member_id})" )->count();

		//1、是否需要分享
		$commiss_sharemember_need = D('Home/Front')->get_config_by_name('commiss_sharemember_need');

		if( !empty($commiss_sharemember_need) && $commiss_sharemember_need == 1 )
		{
			// 2、分享多少人才能成为分销
			$commiss_share_member_update = D('Home/Front')->get_config_by_name('commiss_share_member_update');

			if( !empty($commiss_share_member_update) && $commiss_share_member_update > 0 )
			{
				if(  $share_member_count < $commiss_share_member_update )
				{
					$del = $commiss_share_member_update - $share_member_count;
					echo json_encode( array('code' =>1 , 'msg' => '分享人数还差'.$del.'人','del_count' => $del ) );
					die();
				}
			}
		}

		$member_info = M('eaterplanet_ecommerce_member')->field('is_writecommiss_form,comsiss_flag,comsiss_state')->where( array('member_id' =>$member_id ) )->find();

		// 3、commiss_biaodan_need 是否需要表单

		$commiss_biaodan_need = D('Home/Front')->get_config_by_name('commiss_biaodan_need');

		if( !empty($commiss_biaodan_need) && $commiss_biaodan_need == 1 )
		{
			if( $member_info['is_writecommiss_form'] != 1)
			{
				echo json_encode( array('code' =>1 , 'msg' => '您未填写表单！' ) );
				die();
			}
		}


		//4判断是否需要审核
		$commiss_become_condition = D('Home/Front')->get_config_by_name('commiss_become_condition');

		if( empty($commiss_become_condition) || $commiss_become_condition == 0 )
		{
			//不需要审核，那么直接升级为分销了
			D('Home/Commission')->become_commiss_member($member_id);
			echo json_encode( array('code' =>0, 'msg' =>'申请成功!') );
			die();
		}else{
			//需要审核，成为分销，待审核状态
			D('Home/Commission')->become_wait_commiss_member($member_id);
			echo json_encode( array('code' =>0, 'msg' =>'申请成功，平台将尽快审核') );
			die();
		}


	}



	public function get_parent_agent_info_bymemberid()
	{
		$_GPC = I('request.');

		$member_id =  $_GPC['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '客户不存在') );
			die();
		}



		$data_result = array('parent_username' => '','parent_telephone' => '','share_username' => '','share_telephone' => '' );

		if( $member_info['agentid'] > 0 )
		{
			$parent_mb = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_info['agentid'] ) )->find();

			$data_result['parent_username']  = $parent_mb['username'];//上级姓名
			$data_result['parent_telephone'] = $parent_mb['telephone'];//上级电话
		}
		if( $member_info['share_id'] > 0 )
		{

			$share_mb = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_info['share_id'] ) )->find();

			$data_result['share_username']  = $share_mb['username'];//上级姓名
			$data_result['share_telephone'] = $share_mb['telephone'];//上级电话
		}

		echo json_encode( array('code' => 0, 'data' => $data_result) );
		die();

	}




	public function get_parent_agent_info()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '客户不存在') );
			die();
		}


		$data_result = array('parent_username' => '','parent_telephone' => '','share_username' => '','share_telephone' => '' );

		if( $member_info['agentid'] > 0 )
		{
			$parent_mb = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_info['agentid'] ) )->find();

			$data_result['parent_username']  = $parent_mb['username'];//上级姓名
			$data_result['parent_telephone'] = $parent_mb['telephone'];//上级电话
		}
		if( $member_info['share_id'] > 0 )
		{
			$share_mb = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_info['share_id'] ) )->find();

			$data_result['share_username']  = $share_mb['username'];//上级姓名
			$data_result['share_telephone'] = $share_mb['telephone'];//上级电话
		}

		echo json_encode( array('code' => 0, 'data' => $data_result) );
		die();

	}


	/**
		客户分销提现 提交接口
	**/
	public function tixian_sub()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '客户不存在') );
			die();
		}


		if($member_info['comsiss_flag'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '您还不是分销') );
			die();
		}
		if($member_info['comsiss_state'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '等待管理员审核') );
			die();
		}

		$result = array('code' => 1,'msg' => '提现失败');

		$member_commiss = M('eaterplanet_ecommerce_member_commiss')->where( array('member_id' => $member_id ) )->find();

		$datas = array();


		$datas['money'] = $_GPC['money'];

		$money = $datas['money'];


		$type = $_GPC['type'];// 1余额 2 微信 3 支付宝 4 银行

		$bankname = isset($_GPC['bankname']) ? $_GPC['bankname'] : ''; //银行名称

		$bankaccount = isset($_GPC['bankaccount']) ? $_GPC['bankaccount'] : '';//卡号，支付宝账号 使用该字段

		$bankusername = isset($_GPC['bankusername']) ? $_GPC['bankusername'] : '';//持卡人姓名，微信名称，支付宝名称， 使用该字段

		$commiss_money_limit =  D('Home/Front')->get_config_by_name('commiss_min_tixian_money');


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

			//判断提现手续费，+ 判断提现金额免审直接到账
			$service_charge = D('Home/Front')->get_config_by_name('commiss_tixian_bili');

			$data = array();

			$data['member_id'] = $member_id;
			$data['uniacid'] = 0;

			$data['money'] = $money;
			$data['service_charge'] = $service_charge;
			$data['service_charge_money'] = round( ($money * $service_charge) /100 ,2);

			$data['state'] = 0;

			$data['shentime'] = 0;

			$data['type'] = $type;
			$data['bankname'] = $bankname;
			$data['bankaccount'] = $bankaccount;
			$data['bankusername'] = $bankusername;

			$data['addtime'] = time();

			M('eaterplanet_ecommerce_member_tixian_order')->add( $data );


			$com_arr = array();

			$com_arr['money'] = $member_commiss['money'] - $money;

			$com_arr['dongmoney'] = $member_commiss['dongmoney'] + $money;


			M('eaterplanet_ecommerce_member_commiss')->where( array('member_id' => $member_id ) )->setInc('money',-$money );
			M('eaterplanet_ecommerce_member_commiss')->where( array('member_id' => $member_id ) )->setInc('dongmoney',$money );



			$result['code'] = 0;
			//commiss_tixian_reviewed 0 , 1
			$commiss_tixian_reviewed = D('Home/Front')->get_config_by_name('commiss_tixian_reviewed');

			if(empty($commiss_tixian_reviewed) || $commiss_tixian_reviewed == 0)
			{
				//手动
			} else if( !empty($commiss_tixian_reviewed) && $commiss_tixian_reviewed == 1 ){
				//自动
			}

		}

		echo json_encode($result);

		die();

	}

	/**

		提现记录

	**/

	public function tixian_record()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '客户不存在') );
			die();
		}


		if($member_info['comsiss_flag'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '您还不是分销') );
			die();
		}
		if($member_info['comsiss_state'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '等待管理员审核') );
			die();
		}

		$per_page = 10;

		$page =  isset($_GPC['page']) ? $_GPC['page']:1;


		$offset = ($page - 1) * $per_page;



		$list = array();

		$list = M('eaterplanet_ecommerce_member_tixian_order')->where( array('member_id' => $member_id ) )->order( 'addtime desc' )->limit($offset,$per_page )->select();

		foreach($list as $key => $val)
		{

			$val['addtime'] = date('Y-m-d H:i', $val['addtime']);

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


	private function get_member_next_child($member_id)
	{

		$level =  D('Home/Front')->get_config_by_name('commiss_level');// isset($_GPC['level']) ? $_GPC['level']: 1;



		$level_1_ids = array();
		$level_2_ids = array();
		$level_3_ids = array();

		$member_id_arr = array($member_id);

		$where = "";

		$need_count = 0;

		//commiss_level
		if( $level == 1 )
		{
			$list = array();


			$need_count = M('eaterplanet_ecommerce_member')->where( "agentid in (".implode(',', $member_id_arr).")" )->count();


		}else if( $level == 2 )
		{
			$list = array();

			$list1 = M('eaterplanet_ecommerce_member')->field('member_id')->where( "agentid in (".implode(',', $member_id_arr).")" )->order('member_id desc')->select();

			if( !empty($list1) )
			{
				foreach( $list1 as $vv )
				{
					$level_1_ids[] = $vv['member_id'];
				}

				$level_sql2 =" select member_id from ".C('DB_PREFIX').
							"eaterplanet_ecommerce_member  where
								agentid in (select member_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_member
								where agentid ={$member_id}  order by member_id desc )  order by member_id desc ";

				$list2 =  M()->query($level_sql2);

					if( !empty($list2) )
					{
						foreach( $list2 as $vv )
						{
							$level_2_ids[] = $vv['member_id'];
						}
					}
					$need_ids = empty($level_1_ids) ? array() : $level_1_ids;
					if(!empty($level_2_ids))
					{
						foreach($level_2_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
					if(!empty($need_ids))
					{
						$sql =" select count(member_id) as count from ".C('DB_PREFIX').
								"eaterplanet_ecommerce_member  where 1 {$where} and
									member_id in (".implode(',', $need_ids ).")  ";

						$need_count_arr =  M()->query($sql);

						$need_count = $need_count_arr[0]['count'];


					}



			}

		}else if( $level == 3 ){
			$sql = "select member_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_member
	                    where   agentid in (".implode(',', $member_id_arr).")   order by member_id desc ";

			$list1 =  M()->query($sql);

			if( !empty($list1) )
			{
				foreach( $list1 as $vv )
				{
					$level_1_ids[] = $vv['member_id'];
				}
				$need_ids = empty($level_1_ids) ? array() : $level_1_ids;

				$level_sql2 =" select member_id from ".C('DB_PREFIX').
							"eaterplanet_ecommerce_member  where
								agentid in (select member_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_member
								where agentid ={$member_id} order by member_id desc )  order by member_id desc ";

				$list2 =  M()->query($level_sql2);

				if( !empty($list2) )
				{
					foreach( $list2 as $vv )
					{
						$level_2_ids[] = $vv['member_id'];
					}

					if(!empty($level_2_ids))
					{
						foreach($level_2_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
				}


				$level_sql3 =" select * from ".C('DB_PREFIX').
							"eaterplanet_ecommerce_member  where
								agentid in (".implode(',', $need_ids).")  order by member_id desc ";

				$list3 =  M()->query($level_sql3);

				if( !empty($list3) )
				{
					foreach( $list3 as $vv )
					{
						$level_3_ids[] = $vv['member_id'];
					}

					if(!empty($level_3_ids))
					{
						foreach($level_3_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
				}

				$level_sql3 =" select count(member_id) as count from ".C('DB_PREFIX').
						"eaterplanet_ecommerce_member where 1 {$where} and member_id in (".implode(',',$need_ids).") ";

				$need_count_arr =  M()->query($level_sql3);

				$need_count = $need_count_arr[0]['count'];
			}

		}


		return $need_count;

	}


	/**
		获取客户粉丝列表接口
	**/
	public function get_member_fanslist()
	{

		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$keyword =  $_GPC['keyword'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '客户不存在') );
			die();
		}


		//...

		$page = isset($_GPC['page']) ? $_GPC['page']:'1';

		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;

		//begin select  keyword

		$level =  D('Home/Front')->get_config_by_name('commiss_level');// isset($_GPC['level']) ? $_GPC['level']: 1;



		$level_1_ids = array();
		$level_2_ids = array();
		$level_3_ids = array();

		$member_id_arr = array($member_id);

		$where = "";

		if( !empty($keyword) )
		{
			$where .= " and ( username like '%{$keyword}%' or telephone like '%{$keyword}%' ) ";
		}



		//commiss_level ( "share_id={$member_id} and (agentid =0 or agentid={$member_id} )" )

			$list = array();

			$sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_member
	                    where  1 {$where} and (share_id = {$member_id} and (agentid =0 or agentid={$member_id} ) )  order by member_id desc limit {$offset},{$size}";

			$list =  M()->query($sql);

			foreach( $list as $vv )
			{
				$level_1_ids[$vv['id']] = $vv['id'];
			}



		$level_list = array();
		$need_list = array();


		if( !empty($list) )
		{
			foreach($list as $key => $val)
			{

				$val['child_level'] = 1;

				$val['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
				$need_list[$key] = $val;
			}
		}

		$bg_time = strtotime( date('Y-m-d').' 00:00:00');
		$yes_time = $bg_time - 86400;


		if( !empty($need_list) )
		{
			echo json_encode( array('code' => 0, 'data' => $need_list ) );
			die();
		}else {
			echo json_encode( array('code' => 1 ) );
			die();
		}

	}




	/**
		获取团长的下级列表接口
	**/
	public function get_head_child_headlist()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$keyword =  $_GPC['keyword'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '客户不存在') );
			die();
		}

		if($member_info['comsiss_flag'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '您还不是分销') );
			die();
		}
		if($member_info['comsiss_state'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '等待管理员审核') );
			die();
		}

		//...

		$page = isset($_GPC['page']) ? $_GPC['page']:'1';

		$size = isset($_GPC['size']) ? $_GPC['size']:'20';
		$offset = ($page - 1)* $size;

		$level =  D('Home/Front')->get_config_by_name('commiss_level');

		$level_1_ids = array();
		$level_2_ids = array();
		$level_3_ids = array();

		$member_id_arr = array($member_id);

		$where = "";

		if( !empty($keyword) )
		{
			$where .= " and ( username like '%{$keyword}%' or telephone like '%{$keyword}%' ) ";
		}

		//commiss_level
		if( $level == 1 )
		{
			$list = array();

			$sql = "select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_member
	                    where  1 {$where} and agentid in (".implode(',', $member_id_arr).")   order by member_id desc limit {$offset},{$size}";

			$list =  M()->query($sql);

			foreach( $list as $vv )
			{
				$level_1_ids[$vv['id']] = $vv['id'];
			}

		}else if( $level == 2 )
		{
			$list = array();

			$sql = "select member_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_member
	                    where   agentid in (".implode(',', $member_id_arr).")   order by member_id desc ";

			$list1 =  M()->query($sql);


			if( !empty($list1) )
			{
				foreach( $list1 as $vv )
				{
					$level_1_ids[$vv['member_id']] = $vv['member_id'];
				}

				$level_sql2 =" select member_id from ".C('DB_PREFIX').
							"eaterplanet_ecommerce_member  where
								agentid in (select member_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_member
								where agentid = {$member_id}  order by member_id desc )  order by member_id desc ";

				$list2 =  M()->query($level_sql2);



				if( !empty($list2) || !empty($list1) )
				{
					foreach( $list2 as $vv )
					{
						$level_2_ids[$vv['member_id']] = $vv['member_id'];
					}
				}
					$need_ids = empty($level_1_ids) ? array() : $level_1_ids;
					if(!empty($level_2_ids))
					{
						foreach($level_2_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}

					if(!empty($need_ids))
					{
						$sql =" select * from ".C('DB_PREFIX').
								"eaterplanet_ecommerce_member  where 1 {$where} and
									member_id in (".implode(',', $need_ids ).")  order by member_id desc limit {$offset},{$size}";

						$list =  M()->query($sql);
					}
				}


		}else if( $level == 3 ){
			$sql = "select member_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_member
	                    where  agentid in (".implode(',', $member_id_arr).")   order by member_id desc ";

			$list1 =  M()->query($sql);

			if( !empty($list1) )
			{
				foreach( $list1 as $vv )
				{
					$level_1_ids[$vv['member_id']] = $vv['member_id'];
				}
				$need_ids = empty($level_1_ids) ? array() : $level_1_ids;

				$level_sql2 =" select * from ".C('DB_PREFIX').
							"eaterplanet_ecommerce_member  where
								agentid in (select member_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_member
								where agentid ={$member_id}  order by member_id desc )  order by member_id desc ";

				$list2 =  M()->query($level_sql2);

				if( !empty($list2) )
				{
					foreach( $list2 as $vv )
					{
						$level_2_ids[$vv['member_id']] = $vv['member_id'];
					}

					if(!empty($level_2_ids))
					{
						foreach($level_2_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
				}


				$level_sql3 =" select member_id from ".C('DB_PREFIX').
							"eaterplanet_ecommerce_member  where
								agentid in (".implode(',', $need_ids).")  order by member_id desc ";

				$list3 =  M()->query($level_sql3);

				if( !empty($list3) )
				{
					foreach( $list3 as $vv )
					{
						$level_3_ids[$vv['member_id']] = $vv['member_id'];
					}

					if(!empty($level_3_ids))
					{
						foreach($level_3_ids as $vv)
						{
							$need_ids[] = $vv;
						}
					}
				}

				$level_sql3 =" select * from ".C('DB_PREFIX').
						"eaterplanet_ecommerce_member where 1 {$where} and member_id in (".implode(',',$need_ids).") order by member_id desc limit {$offset},{$size}";

				$list =  M()->query($level_sql3);

			}

		}

		$level_list = array();
		$need_list = array();

		if( !empty($list) )
		{
			foreach($list as $key => $val)
			{
				//member_id
				$val['child_level'] = 1;

				if( isset($level_2_ids[$val['member_id']]) )
				{
					$val['child_level'] = 2;
				}
				else if( isset($level_3_ids[$val['member_id']]) )
				{
					$val['child_level'] = 3;
				}


				$val['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
				$need_list[$key] = $val;
			}
		}

		$bg_time = strtotime( date('Y-m-d').' 00:00:00');
		$yes_time = $bg_time - 86400;

		$today_member_count = M('eaterplanet_ecommerce_member')->where( "agentid={$member_id} and create_time>={$bg_time}" )->count();

		$yes_member_count = M('eaterplanet_ecommerce_member')->where( "agentid={$member_id} and create_time>={$yes_time} and  create_time< {$bg_time}" )->count();

		if( !empty($need_list) )
		{
			echo json_encode( array('code' => 0, 'data' => $need_list , 'today_member_count'=>$today_member_count,'yes_member_count'=>$yes_member_count) );
			die();
		}else {
			echo json_encode( array('code' => 1, 'today_member_count'=>$today_member_count,'yes_member_count'=>$yes_member_count) );
			die();
		}

	}

	/**
		获取客户分销基础数据
	**/
	public function get_commission_info()
	{

		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '客户不存在') );
			die();
		}

		if($member_info['comsiss_flag'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '您还不是分销') );
			die();
		}
		if($member_info['comsiss_state'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '等待管理员审核') );
			die();
		}

		$parent_info = '';
		//上级信息
		if($member_info['agentid'] > 0 ) {
			$parent_res = D('Home/Commission')->get_parent_info($member_info['agentid']);
			$parent_info['member_id'] = $parent_res['member_id'];
			$parent_info['username'] = $parent_res['username'];
		}

		//最小提现金额
		$commiss_min_tixian_money = D('Home/Front')->get_config_by_name('commiss_min_tixian_money');

		if( empty($commiss_min_tixian_money) )
		{
			$commiss_min_tixian_money = 0;
		}

		$commiss_tixian_bili = D('Home/Front')->get_config_by_name('commiss_tixian_bili');

		if( empty($commiss_tixian_bili) )
		{
			$commiss_tixian_bili = 0;
		}

		//C('DB_PREFIX')

		$member_commiss = M('eaterplanet_ecommerce_member_commiss')->where( array('member_id' =>$member_id ) )->find();

		$member_commiss['commiss_min_tixian_money'] = $commiss_min_tixian_money;//最小提现金额， 0标识不限制

		$member_commiss['commiss_tixian_bili'] = $commiss_tixian_bili;

		$member_commiss['total_commiss_money'] = $member_commiss['money'] + $member_commiss['dongmoney'] + $member_commiss['getmoney'];

		//订单数量
		$order_count = M('eaterplanet_ecommerce_member_commiss_order')->where( array('member_id' =>$member_id ) )->count();
		//客户数量


		$member_commiss['order_count'] = $order_count;
		$member_commiss['member_count'] = $this->get_member_next_child($member_id);

		$commiss_tixianway_yuer  = D('Home/Front')->get_config_by_name('commiss_tixianway_yuer');


		$commiss_tixianway_weixin  = D('Home/Front')->get_config_by_name('commiss_tixianway_weixin');
		$commiss_tixianway_alipay  = D('Home/Front')->get_config_by_name('commiss_tixianway_alipay');
		$commiss_tixianway_bank  = D('Home/Front')->get_config_by_name('commiss_tixianway_bank');


		$member_commiss['commiss_tixianway_yuer'] = empty($commiss_tixianway_yuer) ? 1 : ($commiss_tixianway_yuer == 2 ? 1:0);
		$member_commiss['commiss_tixianway_weixin'] = empty($commiss_tixianway_weixin) ? 1 : ($commiss_tixianway_weixin == 2 ? 1:0);
		$member_commiss['commiss_tixianway_alipay'] = empty($commiss_tixianway_alipay) ? 1 : ($commiss_tixianway_alipay == 2 ? 1:0);
		$member_commiss['commiss_tixianway_bank'] = empty($commiss_tixianway_bank) ? 1 : ($commiss_tixianway_bank == 2 ? 1:0);


		//share_id  agentid
		$member_commiss['share_name'] = '';
		if( $member_info['share_id'] > 0  )
		{
			$mbshare_info = M('eaterplanet_ecommerce_member')->field('username')->where( array('member_id' => $member_info['share_id']) )->find();

			$member_commiss['share_name'] = $mbshare_info['username'];
		}


		//上一微信真实姓名
		$last_weixin_realname = "";

		$last_weixin_info = M('eaterplanet_ecommerce_member_tixian_order')->where( "member_id={$member_id} and type=2" )->find();


		if( !empty($last_weixin_info) )
		{
			$last_weixin_realname = $last_weixin_info['bankusername'];
		}

		//上一支付宝账号
		$last_alipay_name = '';
		$last_alipay_account = '';

		$last_alipay_info = M('eaterplanet_ecommerce_member_tixian_order')->where( "member_id={$member_id} and type=3" )->find();

		if( !empty($last_alipay_info) )
		{
			$last_alipay_name = $last_alipay_info['bankusername'];
			$last_alipay_account = $last_alipay_info['bankaccount'];
		}

		//上一银行卡信息
		$last_bank_bankname = '';
		$last_bank_account = '';
		$last_bank_name = '';

		$last_bank_info = M('eaterplanet_ecommerce_member_tixian_order')->where( "member_id={$member_id} and type=4" )->find();

		if( !empty($last_bank_info) )
		{
			$last_bank_bankname = $last_bank_info['bankname'];
			$last_bank_account = $last_bank_info['bankaccount'];
			$last_bank_name = $last_bank_info['bankusername'];
		}

		$member_commiss['last_weixin_realname'] = $last_weixin_realname;
		$member_commiss['last_alipay_name'] = $last_alipay_name;
		$member_commiss['last_alipay_account'] = $last_alipay_account;

		$member_commiss['last_bank_bankname'] = $last_bank_bankname;
		$member_commiss['last_bank_account'] = $last_bank_account;
		$member_commiss['last_bank_name'] = $last_bank_name;

		$commiss_tixian_publish = D('Home/Front')->get_config_by_name('commiss_tixian_publish');

		$member_commiss['commiss_tixian_publish'] = htmlspecialchars_decode( $commiss_tixian_publish );


		$member_commiss['total_money'] = sprintf('%.2f', $member_commiss['money'] + $member_commiss['dongmoney'] + $member_commiss['getmoney']);

		$is_need_subscript = 0;
		$need_subscript_template = array();


		$apply_tixian_info = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $member_id , 'type' => 'apply_tixian') )->find();

		if( empty($apply_tixian_info) )
		{
			$weprogram_subtemplate_apply_tixian = D('Home/Front')->get_config_by_name('weprogram_subtemplate_apply_tixian');

			if( !empty($weprogram_subtemplate_apply_tixian) )
			{
				$need_subscript_template['apply_tixian'] = $weprogram_subtemplate_apply_tixian;
			}
		}

		if( !empty($need_subscript_template) )
		{
			$is_need_subscript = 1;
		}


		echo json_encode( array('code' =>0,'data' => $member_commiss ,'is_need_subscript' => $is_need_subscript, 'need_subscript_template' => $need_subscript_template, 'parent_info'=>$parent_info  ) );

		die();

	}

	/**
		获取分销订单
	**/
	public function listorder_list()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

	    $member_id = $weprogram_token['member_id'];


		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '客户不存在') );
			die();
		}

		if($member_info['comsiss_flag'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '您还不是分销') );
			die();
		}
		if($member_info['comsiss_state'] != 1 )
		{
			echo json_encode( array('code' => 1, 'msg' => '等待管理员审核') );
			die();
		}


		$per_page = 6;

	    $page =  isset($_GPC['page']) ? $_GPC['page']:1;

	    $offset = ($page - 1) * $per_page;

	    $list = array();

		$where = '';

		$state = isset($_GPC['state']) ? $_GPC['state']: -1;

		//state

		if($state >=0)
		{
			$where .= ' and mco.state = '.$state;
		}


		$commiss_level_num = D('Home/Front')->get_config_by_name('commiss_level');

		$where .= ' and mco.level <= '.$commiss_level_num;


		$url = D('Home/Front')->get_config_by_name('shop_domain');

		//$this->state = $state;

		$sql = 'select mco.level, mco.money,mco.child_member_id,mco.addtime,mco.state,o.order_id,o.order_num_alias,o.order_status_id,o.order_num_alias,o.total,og.goods_id,og.quantity,og.has_refund_money,og.has_refund_quantity, og.name,og.price,og.goods_images,og.order_goods_id,mco.store_id,m.username as uname from  '
				.C('DB_PREFIX')."eaterplanet_ecommerce_member_commiss_order as mco , ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og,
				".C('DB_PREFIX')."eaterplanet_ecommerce_order as o  ,
				".C('DB_PREFIX')."eaterplanet_ecommerce_member as m

			where    mco.order_id=og.order_id and mco.order_id = o.order_id and mco.order_goods_id=og.order_goods_id and m.member_id=mco.child_member_id and mco.member_id=".$member_id." {$where} order by mco.id desc limit {$offset},{$per_page}";


		$list = M()->query($sql);



		$status_arr = D('Seller/Order')->get_order_status_name();

		//rela_goodsoption_valueid

		foreach($list as $key =>$val)
		{

			$val['total'] = round($val['total'],2);

			$val['money'] = round($val['money'],2);

			$val['price'] = sprintf("%.2f", $val['price']);

			$val['status_name'] = $status_arr[$val['order_status_id']];

			$val['addtime'] = date('Y-m-d', $val['addtime']);


				if( !empty($val['goods_images']))
				{

					$goods_images = $url. '/'.resize($val['goods_images'],400,400);
					if(is_array($goods_images))
					{
						$val['goods_images'] = $val['goods_images'];
					}else{
						 $val['goods_images']= $url.'/'.resize($val['goods_images'],400,400) ;
					}

				}else{
					 $val['goods_images']= '';
				}



			$order_option_list = M('eaterplanet_ecommerce_order_option')->where( array('order_goods_id' => $val['order_goods_id'] ) )->select();

	        foreach($order_option_list as $option)
			{
				$val['option_str'][] = $option['value'];
			}
			if( !isset($val['option_str']) )
			{
				$val['option_str'] = '';
			}else{
				$val['option_str'] = implode(',', $val['option_str']);
			}

			$val['old_commision'] = $val['money'];
			$val['del_commision'] = 0;

			$order_goods_refund_list = M('eaterplanet_ecommerce_order_goods_refund')->where( "order_goods_id=".$val['order_goods_id'] )->select();
			//level

			if( !empty($order_goods_refund_list) )
			{
				$kvbal_total_back_head_orderbuycommiss = 0;//合计退掉佣金

				foreach( $order_goods_refund_list as $kvval )
				{
					if($val['level'] == 1)
						$kvbal_total_back_head_orderbuycommiss += $kvval['back_member_commiss_1'];

					if($val['level'] == 2)
						$kvbal_total_back_head_orderbuycommiss += $kvval['back_member_commiss_2'];

					if($val['level'] == 3)
						$kvbal_total_back_head_orderbuycommiss += $kvval['back_member_commiss_3'];
				}
				$val['del_commision'] = $kvbal_total_back_head_orderbuycommiss;

				$val['old_commision'] += $val['del_commision'];
			}


			$list[$key] = $val;

		}




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
		获取客户分享海报
	**/
	public function get_haibao()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1,'msg' =>'请先登录') );
			die();
		}

		$goods_model = D('Home/Pingoods');
	    $member_id = $weprogram_token['member_id'];

		$last_community = M('eaterplanet_community_history')->field('head_id')->where( array('member_id' => $member_id) )->order('addtime desc')->find();

		if( empty($last_community) )
		{
			$last_community = M('eaterplanet_community_head')->field('id as head_id')->where(" state=1 and enable=1 and rest=0 ")->order('id desc ')->find();
		}

		$head_id =0;

		if( !empty($last_community) )
		{
			$head_id = $last_community['head_id'];
		}
		//TODO....寻找上一个社区，生成海报。测试png 跟jpg背景的情况，反过来解决 首页 跟商品海报的问题。

		$member_info = M('eaterplanet_ecommerce_member')->field('commiss_qrcode,avatar,username')->where( array('member_id' => $member_id ) )->find();

		$commiss_qrcode = '';

		if( empty($member_info['commiss_qrcode']))
		{
			$commiss_qrcode = $goods_model->_get_index_wxqrcode($member_id,$head_id);

			$avatar = $goods_model->get_commission_user_avatar($member_info['avatar'], $member_id,5);



			$result =  $goods_model->get_commission_index_share_image($head_id,$commiss_qrcode,$avatar, $member_info['username']);

			M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->save( array('commiss_qrcode' => $result['full_path']) );

			echo json_encode( array('code' => 0, 'commiss_qrcode' => tomedia($result['full_path'] ) ) );
			die();
		}else{
			$commiss_qrcode = $member_info['commiss_qrcode'];

			echo json_encode( array('code' => 0, 'commiss_qrcode' => tomedia($commiss_qrcode) ) );
			die();
		}

	}


}
