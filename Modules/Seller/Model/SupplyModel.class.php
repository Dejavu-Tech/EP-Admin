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

class SupplyModel{


	public function modify_supply($data)
    {
        global $_W;
        global $_GPC;



        if($data['id'] > 0)
        {
            //update ims_
            $id = $data['id'];
            unset($data['id']);

			if( empty($data['login_password']) )
			{
				unset($data['login_password']);
			}else{
				$slat = mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9);

				$data['login_password'] = md5( $slat.$data['login_password'] );
				$data['login_slat'] = $slat;
			}

			M('eaterplanet_ecommerce_supply')->where( array('id' => $id ) )->save( $data );
        }else{
            //insert
			$slat = mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9);

			$data['login_password'] = md5( $slat.$data['login_password'] );
			$data['login_slat'] = $slat;

			M('eaterplanet_ecommerce_supply')->add($data);
        }
        return true;
    }


	//----begin


	/**
		检测商户权限的方法
	**/
	public function checksupply_pri( $action_do )
	{
		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$config_data = D('Seller/Config')->get_all_config();

			$is_can = true;
			switch($action_do)
			{
				case 'grounding':
					if( isset($config_data['supply_can_goods_updown']) && $config_data['supply_can_goods_updown'] == 2 )
					{
						$is_can = false;
					}
				break;
				case 'is_index_show':
					if( isset($config_data['supply_can_goods_isindex']) && $config_data['supply_can_goods_isindex'] == 2 )
					{
						$is_can = false;
					}
				case 'istop':
					if( isset($config_data['supply_can_goods_istop']) && $config_data['supply_can_goods_istop'] == 2 )
					{
						$is_can = false;
					}
				break;
			}

			return $is_can;


		}else{
			return true;
		}
	}



	public function ins_supply_commiss_order($order_id,$order_goods_id, $add_money)
	{

		$add_money = 0;

		$order_goods_info = M('eaterplanet_ecommerce_order_goods')->field('goods_id,supply_id,total,shipping_fare,fullreduction_money,voucher_credit,packing_fare,quantity')
							->where( array('order_goods_id' => $order_goods_id ) )->find();


		$order_info = M('eaterplanet_ecommerce_order')->field('delivery')->where( array('order_id' => $order_id ) )->find();

		if( empty($order_goods_info) )
		{
			return true;
		}else {
			//head_id commiss_bili

			$supply_info = D('Home/Front')->get_supply_info($order_goods_info['supply_id']);

			//...begin
			$head_commiss_info_list = M('eaterplanet_community_head_commiss_order')->field('money,add_shipping_fare')
									  ->where( array('order_goods_id' => $order_goods_id ) )->select();

			$head_commiss_money = 0;

			if( !empty($head_commiss_info_list) )
			{
				foreach( $head_commiss_info_list  as $val)
				{
					$head_commiss_money += $val['money'] - $val['add_shipping_fare'];
				}
			}

			//order_id
			$member_commiss_list = M('eaterplanet_ecommerce_member_commiss_order')->field('money')->where( array('order_goods_id' => $order_goods_id,'order_id' => $order_id) )->select();

			$member_commiss_money = 0;

			if( !empty($member_commiss_list) )
			{
				foreach( $member_commiss_list  as $val)
				{
					$member_commiss_money += $val['money'];
				}
			}


			/**
			商品 100  满减2  优惠券3  团长配送费 4 。 实付  100-2-3+4.。。那么

			团长分佣：  10% *（100-2-3）+4（即配送费）

			商户得 （100-2-3）*90%

			**/
			//独立商户
			//$order_goods_info['packing_fare'] 包装费 - 配送员费用
			$money = round( ( (100 - $supply_info['commiss_bili']) * ($order_goods_info['total']  -$order_goods_info['fullreduction_money']-$order_goods_info['voucher_credit']))/100 + $order_goods_info['packing_fare']*$order_goods_info['quantity'] ,2 ) ;

			$total_money = round(  ($order_goods_info['total']  -$order_goods_info['fullreduction_money']-$order_goods_info['voucher_credit']) ,2 );


			$money = $money - $head_commiss_money - $member_commiss_money;

			$shipping_fare = 0;
			//如果是团长配送 商户不计算 运费, 只有快递配送，才把运费算给团长

			if( $order_goods_info['supply_id'] > 0 && $order_info['delivery'] != 'tuanz_send' )
			{
				$su_info = M('eaterplanet_ecommerce_supply')->where( array('id' => $order_goods_info['supply_id'] ) )->find();

				if( $su_info['type'] == 1 )
				{
					$shipping_fare = $order_goods_info['shipping_fare'];

					$money +=  $shipping_fare;
				}
				//$order_goods_info['supply_id']
			}

			//end

			if($money <=0)
			{
				$money = 0;
			}
			//退款才能取消的
			$ins_data = array();
			$ins_data['supply_id'] = $order_goods_info['supply_id'];
			$ins_data['order_id'] = $order_id;
			$ins_data['order_goods_id'] = $order_goods_id;
			$ins_data['state'] = 0;
			$ins_data['total_money'] = $total_money;
			$ins_data['comunity_blili'] = $supply_info['commiss_bili'];

			$ins_data['member_commiss_money'] = $member_commiss_money;
			$ins_data['head_commiss_money'] = $head_commiss_money;
			$ins_data['money'] = $money;
			$ins_data['shipping_fare'] = $shipping_fare;
			$ins_data['addtime'] = time();

			M('eaterplanet_supply_commiss_order')->add( $ins_data );

			return true;
		}

	}


	public function update_supply_commission($order_id,$shipping_money){

		$list = M()->query("select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods where  order_id={$order_id} ");
		foreach($list as $var)
		{
			$goods_shippingmoney = $shipping_money * $var['fenbi_li'];
			M('eaterplanet_supply_commiss_order')->where( array('order_goods_id' => $var['order_goods_id'] ) )->setDec('money', $goods_shippingmoney);
		}

	}

	public function thirth_cancel_supply_commission($order_id,$shipping_money){

		$list = M()->query("select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods where  order_id={$order_id} ");
		foreach($list as $var)
		{
			$goods_shippingmoney = $shipping_money * $var['fenbi_li'];
			M('eaterplanet_supply_commiss_order')->where( array('order_goods_id' => $var['order_goods_id'] ) )->setInc('money', $goods_shippingmoney);
		}

	}

	public function send_supply_commission($order_id)
	{

		$list = M()->query("select * from ".C('DB_PREFIX')."eaterplanet_supply_commiss_order where  order_id={$order_id} ");

		foreach($list as $commiss)
		{
			if( $commiss['state'] == 0)
			{
				//supply_id
				M('eaterplanet_supply_commiss_order')->where( array('id' => $commiss['id'] ) )->save( array('state' => 1) );

				$comiss_info = M('eaterplanet_supply_commiss')->where( array('supply_id' => $commiss['supply_id'] ) )->find();

				if( empty($comiss_info) )
				{
					$ins_data = array();
					$ins_data['supply_id'] = $commiss['supply_id'];
					$ins_data['money'] = 0;
					$ins_data['dongmoney'] = 0;
					$ins_data['getmoney'] = 0;

					M('eaterplanet_supply_commiss')->add($ins_data);
				}

				M('eaterplanet_supply_commiss')->where( array('supply_id' => $commiss['supply_id'] ) )->setInc('money', $commiss['money']);

			}
		}



	}
	//----end

}
?>
