<?php
namespace Home\Model;
use Think\Model;
/**
 * 拼团模型模型
 * @author Albert.Z
 *
 */
class VipcardModel {

    public function update($data)
	{

		$ins_data = array();

		$ins_data['cardname'] = $data['cardname'];
		$ins_data['orignprice'] = $data['orignprice'];
		$ins_data['price'] = $data['price'];
		$ins_data['expire_day'] = $data['expire_day'];
		$ins_data['addtime'] = time();

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);
			M('eaterplanet_ecommerce_member_card')->where( array('id' => $id) )->save( $ins_data );

			$id = $data['id'];
		}else{
			$id = M('eaterplanet_ecommerce_member_card')->add( $ins_data );
		}
	}

	public function updateequity($data)
	{

		$ins_data = array();

		$ins_data['equity_name'] = $data['equity_name'];
		$ins_data['image'] = $data['image'];
		$ins_data['addtime'] = time();

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);

			M('eaterplanet_ecommerce_member_card_equity')->where( array('id' => $id) )->save( $ins_data );
			$id = $data['id'];
		}else{
			$id = M('eaterplanet_ecommerce_member_card_equity')->add( $ins_data );
		}
	}

	/**
	 * 会员卡0元支付订单
	 * @param $order_id
	 */
	public function member_charge_zero_card($order_id){
		//购买会员卡代码
		$member_charge_flow_info = M('eaterplanet_ecommerce_member_card_order')->where( array('id' => $order_id ) )->find();

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_charge_flow_info['member_id'] ) )->find();

		if(!empty($member_charge_flow_info) && $member_charge_flow_info['state'] == 0)
		{
			$begin_time = 0;
			$end_time = 0;

			if($member_charge_flow_info['order_type'] == 1)
			{
				//首次购买
				$begin_time = time();
				$end_time = $begin_time + 86400 * $member_charge_flow_info['expire_day'];

			}else if($member_charge_flow_info['order_type'] == 2)
			{
				//有效期内续期
				$begin_time = $member_info['card_end_time'];
				$end_time = $begin_time + 86400 * $member_charge_flow_info['expire_day'];
			}else if($member_charge_flow_info['order_type'] == 3)
			{
				//过期后续费
				$begin_time = time();
				$end_time = $begin_time + 86400 * $member_charge_flow_info['expire_day'];
			}

			$charge_flow_data = array();
			$charge_flow_data['trans_id'] = "";
			$charge_flow_data['state'] = 1;
			$charge_flow_data['pay_time'] = time();
			$charge_flow_data['begin_time'] = $begin_time;
			$charge_flow_data['end_time'] = $end_time;
			$charge_flow_data['state'] = 1;

			M('eaterplanet_ecommerce_member_card_order')->where( array('id' => $order_id ) )->save( $charge_flow_data );

			$mb_up_data = array();
			$mb_up_data['card_id'] = $member_charge_flow_info['car_id'];
			$mb_up_data['card_begin_time'] = $begin_time;
			$mb_up_data['card_end_time'] = $end_time;

			M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_charge_flow_info['member_id']  ) )->save( $mb_up_data );

			for($i=0;$i<3;$i++)
			{
				$member_formid_data = array();
				$member_formid_data['member_id'] = $member_charge_flow_info['member_id'];
				$member_formid_data['state'] = 0;
				$member_formid_data['formid'] = $member_charge_flow_info['formid'];
				$member_formid_data['addtime'] = time();

				M('eaterplanet_ecommerce_member_formid')->add($member_formid_data);
			}
		}
	}

}
