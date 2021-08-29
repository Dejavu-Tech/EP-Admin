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
namespace Seller\Model;

class TransportModel {

	public function __construct()
	{
	}

	/**
	 * 计算某地区某运费模板ID下的商品总运费，如果运费模板不存在或，按免运费处理
	 *
	 * @param int $transport_id 运费模版id
	 * @param int $quantity 商品件数
	 * @param int $buy_num 商品重量
	 * @param int $area_id 地区id
	 * @return number/boolean
	 */
    public function calc_transport($transport_id, $quantity, $buy_num, $area_id) {


        //$good['transport_id'], $good['quantity'], $address
		if (empty($transport_id)  || empty($area_id)) return 0;


		$extend_list = M('eaterplanet_ecommerce_shipping')->where( array('id' => $transport_id ) )->select();


		// eaterplanet_ecommerce_shipping

		if (empty($extend_list)) {
		    return 0;
		} else {
		    return $this->calc_unit($area_id,$quantity, $buy_num,$extend_list);
		}
    }

	/**
	 * 计算某个具单元的运费
	 *
	 * @param 配送地区 $area_id
	 * @param 购买数量 $quantity
	 * @param 购买重量 $weight
	 * @param 运费模板内容 $extend
	 * @return number 总运费
	 ($area_id,$quantity, $buy_num,$extend_list);
	 */
	private function calc_unit($area_id, $quantity, $weight, $extend){


		$area_info = M('eaterplanet_ecommerce_area')->where( array('id' => $area_id ) )->find();

		if (!empty($extend) && is_array($extend)){

			 $calc_total=array(
				'error'=>'该地区不配送！！'
			);

			$defult_extend = array();

			foreach ($extend as $v) {
				/**
				 * strpos函数返回字符串在另一个字符串中第一次出现的位置，没有该字符返回false
				 * 参数1，字符串
				 * 参数2，要查找的字符
				 */
				$area_price = unserialize($v['areas']);

				if( !empty($area_info['code']) && !empty($area_price['citys_code']) && in_array($area_info['code'], $area_price['citys_code']) )
				{
					unset($calc_total['error']);

					$frist = $area_price['frist'];
					$frist_price = $area_price['frist_price'];
					$second = $area_price['second'];
					$second_price = $area_price['second_price'];

					//按照重量
					if($v['type'] == 1)
					{
						if ($weight <= $frist){
							//在首重数量范围内
							$calc_total['price'] = $frist_price;
						}else{
							//超出首重数量范围，需要计算续重
							$calc_total['price'] = sprintf('%.2f',($frist_price + ceil(($weight-$frist)/$second)*$second_price));
						}
						return $calc_total['price'];
					}else if($v['type'] == 2){
						//按照件数  firstnum firstnumprice  secondnum  secondnumprice
						if ($quantity <= $frist){
							//在首重数量范围内
							$calc_total['price'] = $frist_price;
						}else{
							//超出首重数量范围，需要计算续重
							$calc_total['price'] = sprintf('%.2f',($frist_price + ceil(($quantity-$frist)/$second)*$second_price));
						}
						return $calc_total['price'];
					}

				}else{
					//使用默认的
					unset($calc_total['error']);

					//按照重量
					if($v['type'] == 1)
					{
						if ($weight <= $v['firstweight']){
							//在首重数量范围内
							$calc_total['price'] = $v['firstprice'];
						}else{
							//超出首重数量范围，需要计算续重
							$calc_total['price'] = sprintf('%.2f',($v['firstprice'] + ceil(($weight-$v['firstweight'])/$v['secondweight'])*$v['secondprice']));
						}
						return $calc_total['price'];
					}else if($v['type'] == 2){
						//按照件数  firstnum firstnumprice  secondnum  secondnumprice
						if ($quantity <= $v['firstnum']){
							//在首重数量范围内
							$calc_total['price'] = $v['firstnumprice'];
						}else{
							//超出首重数量范围，需要计算续重
							$calc_total['price'] = sprintf('%.2f',($v['firstnumprice'] + ceil(($quantity-$v['firstnum'])/$v['secondnum'])* $v['secondnumprice']));
						}
						return $calc_total['price'];
					}
				}

				if (strpos($v['area_id'],",".$area_id.",") !== false){

					unset($calc_total['error']);


					if ($num <= $v['snum']){
						//在首重数量范围内
						$calc_total['price'] = $v['sprice'];
					}else{
						//超出首重数量范围，需要计算续重
						$calc_total['price'] = sprintf('%.2f',($v['sprice'] + ceil(($num-$v['snum'])/$v['xnum'])*$v['xprice']));
					}

					return $calc_total['price'];
				}

			}
			return 0;
		}

	}
}