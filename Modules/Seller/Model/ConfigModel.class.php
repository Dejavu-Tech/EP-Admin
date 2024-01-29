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

class ConfigModel{


	public function update($data)
	{

		foreach($data as $name => $value)
		{

			$info = M('eaterplanet_ecommerce_config')->where( array('name' => $name) )->find();

			$value = htmlspecialchars($value);
			if( empty($info) )
			{
				$ins_data = array();
				$ins_data['name'] = $name;
				$ins_data['value'] = $value;
				M('eaterplanet_ecommerce_config')->add($ins_data);
			}else{

				$rs = M('eaterplanet_ecommerce_config')->where( array('id' => $info['id']) )->save( array('value' => $value) );

			}

		}
		$this->get_all_config(true);
	}

	public function get_all_config($is_parse = false)
	{

		$data = S('_get_all_config');

		if (empty($data) || $is_parse) {

			$all_list = M('eaterplanet_ecommerce_config')->select();

			if (empty($all_list)) {
				$data = array();
			}else{
				$data = array();
				foreach($all_list as $val)
				{
					$data[$val['name']] = htmlspecialchars_decode( $val['value'] );
				}
			}

			S('_get_all_config', $data);
		}
		return $data;
	}

	/**
	 * 删除满减配置项
	 * @param $data
	 */
	public function delete_config($data){
		foreach($data as $name => $value)
		{
			$info = M('eaterplanet_ecommerce_config')->where( array('name' => $name) )->find();
			$rs = M('eaterplanet_ecommerce_config')->where( array('id' => $info['id']) )->delete();
		}
		$this->get_all_config(true);
	}

}
?>
