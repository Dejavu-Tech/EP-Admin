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

namespace Seller\Model;

class InvitegiftModel{
	/**
	 * @desc 邀请成功人数
	 * @param $user_id
	 * @return mixed
	 */
	public function getInviteSuccCount($user_id){
		$count = M('eaterplanet_ecommerce_invitegift_record')->where( array('user_id'=>$user_id,'invitee_status'=>1) )->count();
		return empty($count) ? 0 : $count;
	}

	/**
	 * @desc 送出优惠券数量
	 * @param $user_id
	 * @param $city_id
	 * @return mixed
	 */
	public function getInviteSuccSendCouponCount($user_id){
		$sql = "select count(1) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_coupon_list c "
			 . " where c.receive_id in (select id from ".C('DB_PREFIX')."eaterplanet_ecommerce_invitegift_record r where r.user_id = ".$user_id." ) "
			 . " and c.receive_type in ('invitegift_new')";
		$total_arr = M()->query($sql);
		$count = $total_arr[0]['count'];
		return empty($count) ? 0 : $count;
	}

	/**
	 * @desc 送出积分数
	 * @param $user_id
	 * @param $city_id
	 * @return mixed
	 */
	public function getInviteSuccSendPointTotal($user_id){
		$sql = "select sum(f.score) as score from ".C('DB_PREFIX')."eaterplanet_ecommerce_member_integral_flow as f "
				. " where f.order_id in (select id from ".C('DB_PREFIX')."eaterplanet_ecommerce_invitegift_record r where r.user_id = ".$user_id." ) "
				. " and f.type in ('invitegift_new') ";
		$total_arr = M()->query($sql);
		$score = $total_arr[0]['score'];
		return empty($score) ? 0 : $score;
	}

	/**
	 * @desc 总送出优惠券数量
	 * @param $city_id
	 * @return mixed
	 */
	public function getInviteSendCouponCount(){
		$sql = "select count(1) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_coupon_list c "
				. " where c.receive_id in (select id from ".C('DB_PREFIX')."eaterplanet_ecommerce_invitegift_record r ) "
				. " and c.receive_type in ('invitegift_new') ";
		$total_arr = M()->query($sql);
		$count = $total_arr[0]['count'];
		return empty($count) ? 0 : $count;
	}

	/**
	 * @desc 总获得优惠券数量
	 * @param $city_id
	 * @return mixed
	 */
	public function getInviteCouponCount(){
		$sql = "select count(1) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_coupon_list c "
				. " where c.receive_id in (select id from ".C('DB_PREFIX')."eaterplanet_ecommerce_invitegift_record r ) "
				. " and c.receive_type in ('invitegift') ";
		$total_arr = M()->query($sql);
		$count = $total_arr[0]['count'];
		return empty($count) ? 0 : $count;
	}

	/**
	 * @desc 总送出积分数量
	 * @param $city_id
	 * @return mixed
	 */
	public function getInviteSendPointTotal(){
		$sql = "select sum(f.score) as score from ".C('DB_PREFIX')."eaterplanet_ecommerce_member_integral_flow as f "
				. " where f.order_id in (select id from ".C('DB_PREFIX')."eaterplanet_ecommerce_invitegift_record r ) "
				. " and f.type in ('invitegift_new') ";
		$total_arr = M()->query($sql);
		$score = $total_arr[0]['score'];
		return empty($score) ? 0 : $score;
	}

	/**
	 * @desc 总获得积分数量
	 * @param $city_id
	 * @return mixed
	 */
	public function getInvitePointTotal(){
		$sql = "select sum(f.score) as score from ".C('DB_PREFIX')."eaterplanet_ecommerce_member_integral_flow as f "
				. " where f.order_id in (select id from ".C('DB_PREFIX')."eaterplanet_ecommerce_invitegift_record r ) "
				. " and f.type in ('invitegift') ";
		$total_arr = M()->query($sql);
		$score = $total_arr[0]['score'];
		return empty($score) ? 0 : $score;
	}

	/**
	 * @desc 获得优惠券数量
	 * @param $user_id
	 * @param $city_id
	 * @return mixed
	 */
	public function getInviteSuccCouponCount($user_id){
		$sql = "select count(1) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_coupon_list c "
				. " where c.receive_id in (select id from ".C('DB_PREFIX')."eaterplanet_ecommerce_invitegift_record r where r.user_id = ".$user_id." ) "
				. " and c.receive_type in ('invitegift')";
		$total_arr = M()->query($sql);
		$count = $total_arr[0]['count'];
		return empty($count) ? 0 : $count;
	}

	/**
	 * @desc 获得积分数
	 * @param $user_id
	 * @param $city_id
	 * @return mixed
	 */
	public function getInviteSuccPointTotal($user_id){
		$sql = "select sum(f.score) as score from ".C('DB_PREFIX')."eaterplanet_ecommerce_member_integral_flow as f "
				. " where f.order_id in (select id from ".C('DB_PREFIX')."eaterplanet_ecommerce_invitegift_record r where r.user_id = ".$user_id." ) "
				. " and f.type in ('invitegift') ";
		$total_arr = M()->query($sql);
		$score = $total_arr[0]['score'];
		return empty($score) ? 0 : $score;
	}
}
?>
