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

class DiydataModel{

	public function update($data)
	{
		$sql = 'TRUNCATE '. C('DB_PREFIX'). 'eaterplanet_ecommerce_diydata';
		$rs = M()->execute($sql);
		// $rs = M('eaterplanet_ecommerce_diydata')->where( 'id > 0' )->delete();
		foreach($data as $key => $value)
		{
			$name = $value->controller;
			$value = json_encode($value);
			$ins_data = array();
			$ins_data['name'] = $name;
			$ins_data['value'] = serialize($value);
			M('eaterplanet_ecommerce_diydata')->add($ins_data);
		}
		$this->get_all_config(true);
	}

	public function get_all_config($is_parse = false)
	{
		// $data = S('_get_all_diy_data');
		$data = array();
		if (empty($data) || $is_parse) {

			$all_list = M('eaterplanet_ecommerce_diydata')->select();

			if (empty($all_list)) {
				$data = array();
			}else{
				$data = array();
				foreach($all_list as $val)
				{
					$data[] = json_decode(unserialize($val['value']));
				}
			}

			S('_get_all_diy_data', $data);
		}
		return $data;
	}

}
?>
