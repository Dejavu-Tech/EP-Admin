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

class SpecModel{


	public function update($data,$spec_type = 'normal')
	{

		$ins_data = array();
		$ins_data['name'] = $data['name'];
		$ins_data['spec_type'] = $spec_type;
		$ins_data['value'] = serialize(array_filter($data['value']));
		$ins_data['addtime'] = time();

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			M('eaterplanet_ecommerce_spec')->where( array('id' => $id) )->save($ins_data);
			$id = $data['id'];

		}else{

			M('eaterplanet_ecommerce_spec')->add($ins_data);
			$id = M('eaterplanet_ecommerce_spec')->getLastInsID();
		}

	}

	public function get_all_spec($spec_type = 'normal')
	{

		$specs = M('eaterplanet_ecommerce_spec')->where(' spec_type="'.$spec_type.'"')->order('id asc')->select();

		foreach($specs as $key => $val)
		{
			$val['value'] = unserialize($val['value']);
			if( !empty($val['value']) )
			{
				$val['value_str'] = implode('@', $val['value']);
			}else{
				$val['value_str'] = '';
			}

			$specs[$key] = $val;
		}

		return $specs;
	}


}
?>
