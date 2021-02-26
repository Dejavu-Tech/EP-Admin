<?php
namespace Home\Model;
use Think\Model;
/**
 * 拼团模型模型
 * @author Albert.Z
 *
 */
class IntegralModel {

   /**
		检测会员积分是否足够支付订单
	**/
	public function check_user_score_can_pay($member_id, $sku_str ='', $goods_id)
	{

		$member_info = M('eaterplanet_ecommerce_member')->field('score')->where( array('member_id' => $member_id ) )->find();

		if( !empty($sku_str) )
		{

			$mult_value_info = $goods_option_mult_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' =>$goods_id,'option_item_ids' => $sku_str ) )->find();


			//marketprice
			if($mult_value_info['marketprice'] > $member_info['score'])
			{
				return array('code' => 1,'cur_score' => $member_info['score'],'pay_score' => $mult_value_info['marketprice']);
			}else{
				return array('code' => 0);
			}
		}else{
			//price

			$intgral_goods_info = M('eaterplanet_ecommerce_goods')->field('price')->where( array('id' => $goods_id ) )->find();

			if($intgral_goods_info['price'] > $member_info['score'])
			{
				return array('code' => 1,'cur_score' => $member_info['score'],'pay_score' => $intgral_goods_info['price']);
			}else{
				return array('code' => 0);
			}
		}
	}

	/**
	检测会员积分是否足够支付订单
	 **/
	public function check_user_score_quantity_can_pay($member_id, $sku_str ='', $goods_id, $quantity)
	{

		$member_info = M('eaterplanet_ecommerce_member')->field('score')->where( array('member_id' => $member_id ) )->find();

		if( !empty($sku_str) )
		{

			$mult_value_info = $goods_option_mult_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' =>$goods_id,'option_item_ids' => $sku_str ) )->find();


			//marketprice
			if($mult_value_info['marketprice']*$quantity > $member_info['score'])
			{
				return array('code' => 1,'cur_score' => $member_info['score'],'pay_score' => $mult_value_info['marketprice']*$quantity);
			}else{
				return array('code' => 0);
			}
		}else{
			//price

			$intgral_goods_info = M('eaterplanet_ecommerce_goods')->field('price')->where( array('id' => $goods_id ) )->find();

			if($intgral_goods_info['price']*$quantity > $member_info['score'])
			{
				return array('code' => 1,'cur_score' => $member_info['score'],'pay_score' => $intgral_goods_info['price']*$quantity);
			}else{
				return array('code' => 0);
			}
		}
	}

}
