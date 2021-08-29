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

class InvitegiftController extends CommonController{

	protected function _initialize(){
		parent::_initialize();

	}
	/**
	 * @author cy 2021-03-09
	 * @desc 邀新有礼配置
	 */
	public function config()
	{
		$_GPC = I('request.');

		if (IS_POST) {
			//邀新有礼开启/关闭状态
			$is_invite_open_status = isset($_GPC['is_invite_open_status']) ? $_GPC['is_invite_open_status'] : 0;
			//邀请者奖励类型
			$inviter_reward_type = isset($_GPC['inviter_reward_type']) ? $_GPC['inviter_reward_type'] : 0;
			$inviter_reward_couponid = isset($_GPC['inviter_reward_couponid']) ? $_GPC['inviter_reward_couponid'] : 0;
			$inviter_reward_point = isset($_GPC['inviter_reward_point']) ? $_GPC['inviter_reward_point'] : 0;

			//被邀请者奖励类型
			$invitees_reward_type = isset($_GPC['invitees_reward_type']) ? $_GPC['invitees_reward_type'] : 0;
			$invitees_register_reward_type = isset($_GPC['invitees_register_reward_type']) ? $_GPC['invitees_register_reward_type'] : 0;
			$invitees_register_reward_couponid = isset($_GPC['invitees_register_reward_couponid']) ? $_GPC['invitees_register_reward_couponid'] : 0;
			$invitees_register_reward_point = isset($_GPC['invitees_register_reward_point']) ? $_GPC['invitees_register_reward_point'] : 0;

			$invitees_order_reward_type = isset($_GPC['invitees_order_reward_type']) ? $_GPC['invitees_order_reward_type'] : 0;
			$invitees_order_reward_couponid = isset($_GPC['invitees_order_reward_couponid']) ? $_GPC['invitees_order_reward_couponid'] : 0;
			$invitees_order_reward_point = isset($_GPC['invitees_order_reward_point']) ? $_GPC['invitees_order_reward_point'] : 0;

			//活动奖励类型
			$invite_activity_reward_type = isset($_GPC['invite_activity_reward_type']) ? $_GPC['invite_activity_reward_type'] : 0;
			//邀请人数限制
			$is_open_invitation_limit = isset($_GPC['is_open_invitation_limit']) ? $_GPC['is_open_invitation_limit'] : 0;
			//邀请人数限制
			$invitation_limit_person = isset($_GPC['invitation_limit_person']) ? $_GPC['invitation_limit_person'] : 0;
			//邀新有礼活动顶部背景图
			$invite_activity_topback_img = isset($_GPC['invite_activity_topback_img']) ? $_GPC['invite_activity_topback_img'] : '';
			//活动打开页面顶部背景图
			$invite_activity_open_topback_img = isset($_GPC['invite_activity_open_topback_img']) ? $_GPC['invite_activity_open_topback_img'] : '';
			//活动积分/优惠卷使用规则
			$invite_activity_use_rules = isset($_GPC['invite_activity_use_rules']) ? $_GPC['invite_activity_use_rules'] : '';
			//分享标题
			$invite_share_title = isset($_GPC['invite_share_title']) ? $_GPC['invite_share_title'] : '';
			//分享图片
			$invite_share_img = isset($_GPC['invite_share_img']) ? $_GPC['invite_share_img'] : '';
			//邀请下单分享标题
			$invite_order_share_title = isset($_GPC['invite_order_share_title']) ? $_GPC['invite_order_share_title'] : '';
			//邀请下单分享图片
			$invite_order_share_img = isset($_GPC['invite_order_share_img']) ? $_GPC['invite_order_share_img'] : '';
			//活动规则
			$invite_activity_rules = isset($_GPC['invite_activity_rules']) ? $_GPC['invite_activity_rules'] : '';

			if($is_invite_open_status == 1){
				if($inviter_reward_type == 1){
					if(empty($inviter_reward_couponid)){
						show_json(0, array('message' => '请选择邀请者奖励赠送的优惠券'));
					}
				}else if($inviter_reward_type == 2){
					if(empty($inviter_reward_point) || !is_numeric($inviter_reward_point) || $inviter_reward_point < 0){
						show_json(0, array('message' => '邀请者奖励赠送的积分必填且为大于0'));
					}
				}else if($inviter_reward_type == 0){
					if(empty($inviter_reward_couponid)){
						show_json(0, array('message' => '请选择邀请者奖励赠送的优惠券'));
					}
					if(empty($inviter_reward_point) || !is_numeric($inviter_reward_point) || $inviter_reward_point < 0){
						show_json(0, array('message' => '邀请者奖励赠送的积分必填且为大于0'));
					}
				}

				if($invitees_reward_type == 1){//新人注册（授权登陆）
					if($invitees_register_reward_type == 1){
						if(empty($invitees_register_reward_couponid)){
							show_json(0, array('message' => '请选择被邀请者奖励新人注册赠送的优惠券'));
						}
					}else if($invitees_register_reward_type == 2){
						if(empty($invitees_register_reward_point) || !is_numeric($invitees_register_reward_point) || $invitees_register_reward_point < 0){
							show_json(0, array('message' => '被邀请者奖励新人注册赠送的积分必填且为大于0'));
						}
					}else{
						if(empty($invitees_register_reward_couponid)){
							show_json(0, array('message' => '请选择被邀请者奖励新人注册赠送的优惠券'));
						}
						if(empty($invitees_register_reward_point) || !is_numeric($invitees_register_reward_point) || $invitees_register_reward_point < 0){
							show_json(0, array('message' => '被邀请者奖励新人注册赠送的积分必填且为大于0'));
						}
					}
				}else if($invitees_reward_type == 2){//下单完成
					if($invitees_order_reward_type == 1){
						if(empty($invitees_order_reward_couponid)){
							show_json(0, array('message' => '请选择被邀请者奖励下单完成赠送的优惠券'));
						}
					}else if($invitees_order_reward_type == 2){
						if(empty($invitees_order_reward_point) || !is_numeric($invitees_order_reward_point) || $invitees_order_reward_point < 0){
							show_json(0, array('message' => '被邀请者奖励下单完成赠送的积分必填且为大于0'));
						}
					}else{
						if(empty($invitees_order_reward_couponid)){
							show_json(0, array('message' => '请选择被邀请者奖励下单完成赠送的优惠券'));
						}
						if(empty($invitees_order_reward_point) || !is_numeric($invitees_order_reward_point) || $invitees_order_reward_point < 0){
							show_json(0, array('message' => '被邀请者奖励下单完成赠送的积分必填且为大于0'));
						}
					}
				}else if($invitees_reward_type == 0){//新人注册和下单完成
					if($invitees_register_reward_type == 1){
						if(empty($invitees_register_reward_couponid)){
							show_json(0, array('message' => '请选择被邀请者奖励新人注册赠送的优惠券'));
						}
					}else if($invitees_register_reward_type == 2){
						if(empty($invitees_register_reward_point) || !is_numeric($invitees_register_reward_point) || $invitees_register_reward_point < 0){
							show_json(0, array('message' => '被邀请者奖励新人注册赠送的积分必填且为大于0'));
						}
					}else{
						if(empty($invitees_register_reward_couponid)){
							show_json(0, array('message' => '请选择被邀请者奖励新人注册赠送的优惠券'));
						}
						if(empty($invitees_register_reward_point) || !is_numeric($invitees_register_reward_point) || $invitees_register_reward_point < 0){
							show_json(0, array('message' => '被邀请者奖励新人注册赠送的积分必填且为大于0'));
						}
					}
					if($invitees_order_reward_type == 1){
						if(empty($invitees_order_reward_couponid)){
							show_json(0, array('message' => '请选择被邀请者奖励下单完成赠送的优惠券'));
						}
					}else if($invitees_order_reward_type == 2){
						if(empty($invitees_order_reward_point) || !is_numeric($invitees_order_reward_point) || $invitees_order_reward_point < 0){
							show_json(0, array('message' => '被邀请者奖励下单完成赠送的积分必填且为大于0'));
						}
					}else{
						if(empty($invitees_order_reward_couponid)){
							show_json(0, array('message' => '请选择被邀请者奖励下单完成赠送的优惠券'));
						}
						if(empty($invitees_order_reward_point) || !is_numeric($invitees_order_reward_point) || $invitees_order_reward_point < 0){
							show_json(0, array('message' => '被邀请者奖励下单完成赠送的积分必填且为大于0'));
						}
					}
				}
				if($is_open_invitation_limit == 1){
					if(empty($invitation_limit_person) || floor($invitation_limit_person) != $invitation_limit_person || $invitation_limit_person <= 0){
						show_json(0, array('message' => '请填写邀请人数且为大于0整数'));
					}
				}
			}
			$parameter = array();
			$parameter['is_invite_open_status'] = $is_invite_open_status;
			$parameter['inviter_reward_type'] = $inviter_reward_type;
			$parameter['inviter_reward_couponid'] = $inviter_reward_couponid;
			$parameter['inviter_reward_point'] = $inviter_reward_point;

			$parameter['invitees_reward_type'] = $invitees_reward_type;
			$parameter['invitees_register_reward_type'] = $invitees_register_reward_type;
			$parameter['invitees_register_reward_couponid'] = $invitees_register_reward_couponid;
			$parameter['invitees_register_reward_point'] = $invitees_register_reward_point;

			$parameter['invitees_order_reward_type'] = $invitees_order_reward_type;
			$parameter['invitees_order_reward_couponid'] = $invitees_order_reward_couponid;
			$parameter['invitees_order_reward_point'] = $invitees_order_reward_point;

			$parameter['invite_activity_reward_type'] = $invite_activity_reward_type;
			$parameter['is_open_invitation_limit'] = $is_open_invitation_limit;
			$parameter['invitation_limit_person'] = $invitation_limit_person;
			$parameter['invite_activity_topback_img'] = $invite_activity_topback_img;
			$parameter['invite_activity_open_topback_img'] = $invite_activity_open_topback_img;
			$parameter['invite_activity_use_rules'] = $invite_activity_use_rules;
			$parameter['invite_share_title'] = $invite_share_title;
			$parameter['invite_share_img'] = $invite_share_img;
			$parameter['invite_order_share_title'] = $invite_order_share_title;
			$parameter['invite_order_share_img'] = $invite_order_share_img;
			$parameter['invite_activity_rules'] = $invite_activity_rules;

			D('Seller/Config')->update($parameter);
			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
			die();
		}else{
			$data = D('Seller/Config')->get_all_config();
			$this->data = $data;

			$coupon_list = M('eaterplanet_ecommerce_coupon')->field('id,voucher_title,person_limit_count,total_count,send_count')->order('displayorder desc')->select();
			if( !empty($coupon_list) )
			{
				foreach($coupon_list as $k=> $v )
				{
					if($v['total_count'] != -1){
						//已发送张数
						$send_count = M('eaterplanet_ecommerce_coupon_list')->where( array('voucher_id' => $v['id'] ) )->count();
						$v['remain_count'] = $v['total_count'] - $send_count;
						$v['remain_count'] = '剩余'.$v['remain_count'].'张';
					}else{
						$v['remain_count'] = '无限制';
					}
					$coupon_list[$k] = $v;
				}
			}
			$this->coupon_list = $coupon_list;

			$this->display();
		}
	}

	/**
	 * @author cy 2021-03-09
	 * @desc 邀新海报背景配置
	 */
	public function poster_background(){
		if (IS_POST) {
			$_GPC = I('request.');
			$invite_poster_back_type = isset($_GPC['invite_poster_back_type']) ? $_GPC['invite_poster_back_type'] : 0;
			$invite_poster_back_color = isset($_GPC['invite_poster_back_color']) ? $_GPC['invite_poster_back_color'] : '#ffffff';
			$invite_poster_back_img = isset($_GPC['invite_poster_back_img']) ? $_GPC['invite_poster_back_img'] : '';

			$parameter = array();
			$parameter['invite_poster_back_type'] = $invite_poster_back_type;
			$parameter['invite_poster_back_color'] = $invite_poster_back_color;
			$parameter['invite_poster_back_img'] = $invite_poster_back_img;

			D('Seller/Config')->update($parameter);
			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
			die();
		}else{
			$data = D('Seller/Config')->get_all_config();

			$need_data = array();
			$need_data['invite_poster_back_type'] = isset( $data['invite_poster_back_type'] ) ? $data['invite_poster_back_type']: 0;
			$need_data['invite_poster_back_color'] = isset( $data['invite_poster_back_color'] ) ? $data['invite_poster_back_color']: '#ffffff';
			$need_data['invite_poster_back_img'] = isset( $data['invite_poster_back_img'] ) ? $data['invite_poster_back_img']: '';
			$this->data = $need_data;
			$this->display();
		}
	}

	/**
	 * @author cy 2021-03-10
	 * @desc 邀新海报二维码配置
	 */
	public function poster_qrcode(){
		if (IS_POST) {
			$_GPC = I('request.');
			$invite_poster_qrcode_backcolor = isset($_GPC['invite_poster_qrcode_backcolor']) ? $_GPC['invite_poster_qrcode_backcolor'] : 0;
			$invite_poster_qrcode_linecolor = isset($_GPC['invite_poster_qrcode_linecolor']) ? $_GPC['invite_poster_qrcode_linecolor'] : '';
			$invite_poster_qrcode_corner_type = isset($_GPC['invite_poster_qrcode_corner_type']) ? $_GPC['invite_poster_qrcode_corner_type'] : '';

			$invite_poster_qrcode_size = isset($_GPC['invite_poster_qrcode_size']) ? $_GPC['invite_poster_qrcode_size'] : 0;
			$invite_poster_qrcode_border_status = isset($_GPC['invite_poster_qrcode_border_status']) ? $_GPC['invite_poster_qrcode_border_status'] : '';
			$invite_poster_qrcode_bordercolor = isset($_GPC['invite_poster_qrcode_bordercolor']) ? $_GPC['invite_poster_qrcode_bordercolor'] : '';
			$invite_poster_qrcode_img = isset($_GPC['invite_poster_qrcode_img']) ? $_GPC['invite_poster_qrcode_img'] : '';
			$invite_poster_qrcode_top = isset($_GPC['invite_poster_qrcode_top']) ? $_GPC['invite_poster_qrcode_top'] : '';
			$invite_poster_qrcode_left = isset($_GPC['invite_poster_qrcode_left']) ? $_GPC['invite_poster_qrcode_left'] : '';

			$parameter = array();
			$parameter['invite_poster_qrcode_img'] = empty($invite_poster_qrcode_img) ? '' : $invite_poster_qrcode_img;
			$parameter['invite_poster_qrcode_backcolor'] = empty($invite_poster_qrcode_backcolor) ? '#323233' : $invite_poster_qrcode_backcolor;
			$parameter['invite_poster_qrcode_linecolor'] = empty($invite_poster_qrcode_linecolor) ? '#323233' : $invite_poster_qrcode_linecolor;
			$parameter['invite_poster_qrcode_corner_type'] = empty($invite_poster_qrcode_corner_type) ? '0' : $invite_poster_qrcode_corner_type;

			$parameter['invite_poster_qrcode_size'] = empty($invite_poster_qrcode_size) ? '100' : $invite_poster_qrcode_size;
			$parameter['invite_poster_qrcode_border_status'] = empty($invite_poster_qrcode_border_status) ? '0' : $invite_poster_qrcode_border_status;
			$parameter['invite_poster_qrcode_bordercolor'] = empty($invite_poster_qrcode_bordercolor) ? '#323233' : $invite_poster_qrcode_bordercolor;;

			$parameter['invite_poster_qrcode_top'] = empty($invite_poster_qrcode_top) ? '0' : $invite_poster_qrcode_top;
			$parameter['invite_poster_qrcode_left'] = empty($invite_poster_qrcode_left) ? '0' : $invite_poster_qrcode_left;
			$parameter['invite_poster_update_time'] = time();
			D('Seller/Config')->update($parameter);
			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
			die();
		}else{
			$config_data = D('Seller/Config')->get_all_config();

			$need_data = [];
			$need_data['invite_poster_qrcode_backcolor'] = isset( $config_data['invite_poster_qrcode_backcolor'] ) ? $config_data['invite_poster_qrcode_backcolor'] : '#ffffff';
			$need_data['invite_poster_qrcode_linecolor'] = isset( $config_data['invite_poster_qrcode_linecolor'] ) ? $config_data['invite_poster_qrcode_linecolor'] : '#000000';
			$need_data['invite_poster_qrcode_corner_type'] = isset( $config_data['invite_poster_qrcode_corner_type'] ) ? $config_data['invite_poster_qrcode_corner_type']: 0;
			$need_data['invite_poster_qrcode_size'] = isset( $config_data['invite_poster_qrcode_size'] ) ? $config_data['invite_poster_qrcode_size'] : '300';
			$need_data['invite_poster_qrcode_border_status'] = isset( $config_data['invite_poster_qrcode_border_status'] ) ? $config_data['invite_poster_qrcode_border_status'] : 0;
			$need_data['invite_poster_qrcode_bordercolor'] = isset( $config_data['invite_poster_qrcode_bordercolor'] ) ? $config_data['invite_poster_qrcode_bordercolor'] : '#000000';
			$need_data['invite_poster_qrcode_top'] = isset( $config_data['invite_poster_qrcode_top'] ) ? $config_data['invite_poster_qrcode_top'] : '0';
			$need_data['invite_poster_qrcode_left'] = isset( $config_data['invite_poster_qrcode_left'] ) ? $config_data['invite_poster_qrcode_left'] : '0';
			if(empty($config_data['invite_poster_qrcode_img'])){
				$invite_qrcode = D('Home/Pingoods')->_get_invite_wxqrcode("eaterplanet_ecommerce/moduleB/invite/share", 0 ,$need_data['invite_poster_qrcode_backcolor'],$need_data['invite_poster_qrcode_linecolor']);

				$need_data['invite_poster_qrcode_img_yuan'] = $invite_qrcode;
				$need_data['invite_poster_qrcode_img'] = tomedia($invite_qrcode);

				$parameter['invite_poster_qrcode_img'] = empty($invite_qrcode) ? '' : $invite_qrcode;
				$parameter['invite_poster_qrcode_backcolor'] = empty($need_data['invite_poster_qrcode_backcolor']) ? '' : $need_data['invite_poster_qrcode_backcolor'];
				$parameter['invite_poster_qrcode_linecolor'] = empty($need_data['invite_poster_qrcode_linecolor']) ? '' : $need_data['invite_poster_qrcode_linecolor'];
				$parameter['invite_poster_update_time'] = time();
				D('Seller/Config')->update($parameter);
			}else{
				$need_data['invite_poster_qrcode_img_yuan'] = $config_data['invite_poster_qrcode_img'];
				$need_data['invite_poster_qrcode_img'] = tomedia($config_data['invite_poster_qrcode_img']);
			}
			$need_data['invite_poster_back_type'] = isset( $config_data['invite_poster_back_type'] ) ? $config_data['invite_poster_back_type']: 0;
			$need_data['invite_poster_back_color'] = isset( $config_data['invite_poster_back_color'] ) ? $config_data['invite_poster_back_color']: '#ffffff';
			$need_data['invite_poster_back_img'] = isset( $config_data['invite_poster_back_img'] ) ? $config_data['invite_poster_back_img']: '';
			$need_data['qrcode_width'] = round($need_data['invite_poster_qrcode_size'] / 2);
			$need_data['qrcode_top'] = $need_data['invite_poster_qrcode_top'] / 2;
			$need_data['qrcode_left'] = $need_data['invite_poster_qrcode_left'] / 2;

			$this->data = $need_data;
			$this->display();
		}
	}
	/**
	 * @author cy 2021-03-10
	 * @desc 修改二维码背景色
	 */
	public function changeQrcodeBackground(){
		$_GPC = I('request.');
		$config_data = D('Seller/Config')->get_all_config();

		$back_color = !empty($_GPC['back_color']) ? $_GPC['back_color'] : '#ffffff';
		$line_color = !empty($_GPC['line_color']) ? $_GPC['line_color'] : '#000000';
		$need_data = [];
		//生成二维码
		$invite_qrcode = D('Home/Pingoods')->_get_invite_wxqrcode("eaterplanet_ecommerce/moduleB/invite/share", 0 , $back_color, $line_color);

		$need_data['invite_poster_qrcode_img_yuan'] = $invite_qrcode;
		$need_data['invite_poster_qrcode_img'] = tomedia($invite_qrcode);

		$parameter['invite_poster_qrcode_img'] = empty($invite_qrcode) ? '' : $invite_qrcode;
		$parameter['invite_poster_qrcode_backcolor'] = empty($back_color) ? '' : $back_color;
		$parameter['invite_poster_qrcode_linecolor'] = empty($line_color) ? '' : $line_color;
		$parameter['invite_poster_update_time'] = time();
		D('Seller/Config')->update($parameter);
		//删除二维码图片
		$upload_path = ROOT_PATH.'Uploads/image/';
		if(file_exists($upload_path.$config_data['invite_poster_qrcode_img'])){
			unlink($upload_path.$config_data['invite_poster_qrcode_img']);
		}
		show_json(1,  $need_data);
	}

	/**
	 * @author cy 2021-03-10
	 * @desc 邀新记录
	 * @return mixed
	 */
	public function record(){
		$_GPC = I('request.');

		$pindex    = max(1, intval($_GPC['page']));
		$psize     = 20;
		$condition = " 1 ";
		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= " and m.username like '%".$_GPC['keyword']."%' ";
		}
		$sql = 'SELECT distinct(r.user_id) as user_id,m.username,count(r.invitee_userid) as invite_count,m.share_status FROM '. C('DB_PREFIX'). "eaterplanet_ecommerce_invitegift_record r "
			 . " left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member m on r.user_id = m.member_id "
			 . " where ". $condition . ' group by r.user_id ';
		$query_sql = " select * from (".$sql.") as t order by invite_count desc limit ".(($pindex - 1) * $psize).",".$psize;
		$list = M()->query($query_sql);

		$total_sql = "select count(1) as count from (".$sql.") t";
		$total_arr = M()->query($total_sql);
		$total = $total_arr[0]['count'];
		$pager = pagination2($total, $pindex, $psize);

		$invitegift_model = D('Seller/Invitegift');
		if( $total > 0 )
		{
			foreach( $list as $k=>&$v )
			{
				$v['invite_succ_count'] = $invitegift_model->getInviteSuccCount($v['user_id']);
				$v['coupon_count'] = $invitegift_model->getInviteSuccSendCouponCount($v['user_id']);
				$v['point_count'] = $invitegift_model->getInviteSuccSendPointTotal($v['user_id']);

				$v['get_coupon_count'] = $invitegift_model->getInviteSuccCouponCount($v['user_id']);
				$v['get_point_count'] = $invitegift_model->getInviteSuccPointTotal($v['user_id']);
			}
		}

		$this->list = $list;
		$this->pager = $pager;

		$this->coupon_total_count = $invitegift_model->getInviteSendCouponCount();
		$this->point_total_count = $invitegift_model->getInviteSendPointTotal();
		$this->get_coupon_total_count = $invitegift_model->getInviteCouponCount();
		$this->get_point_total_count = $invitegift_model->getInvitePointTotal();

		$this->_GPC = $_GPC;

		$this->display();
	}

	/**
	 * @author cy 2021-03-10
	 * @desc 更新客户邀新状态
	 * @return mixed
	 */
	public function changestatus(){
		$_GPC = I('request.');

		$user_id = $_GPC['user_id'];
		$type = $_GPC['type'];
		$status = $_GPC['value'];

		M('eaterplanet_ecommerce_member')->where( array('member_id' => $user_id) )->save( array($type => $status) );

		show_json(1, array('url' => U('invitegift/record')));
	}

	/**
	 * @author cy 2021-03-10
	 * @desc 被邀请者列表
	 */
	public function invite_list(){
		$_GPC = I('request.');

		$pindex    = max(1, intval($_GPC['page']));
		$psize     = 20;

		$keyword = $_GPC['keyword'];
		$user_id = $_GPC['user_id'];

		$condition = " 1 ";
		$condition .= " and r.user_id = ". $user_id;
		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= " and m.username like '%".$keyword."%' ";
		}
		$sql = "select r.invitee_userid,m.username,r.addtime,r.invitee_status from ".C('DB_PREFIX')."eaterplanet_ecommerce_invitegift_record r "
				. " left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member m on r.invitee_userid=m.member_id "
				. " where ".$condition;

		$query_sql = $sql." order by r.addtime desc limit ".(($pindex - 1) * $psize).",".$psize;
		$list = M()->query($query_sql);

		$total_sql = "select count(1) as count from (".$sql.") t";
		$total_arr = M()->query($total_sql);
		$total = $total_arr[0]['count'];
		$pager = pagination2($total, $pindex, $psize);

		if( $total > 0 )
		{
			foreach( $list as $k=>&$v )
			{
				$v['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
			}
		}

		$this->list = $list;
		$this->pager = $pager;

		$member_info = M('eaterplanet_ecommerce_member')->where(array('member_id'=>$user_id))->field('username')->find();
		$this->member = $member_info;
		$this->_GPC = $_GPC;

		$this->display();
	}
}
?>
