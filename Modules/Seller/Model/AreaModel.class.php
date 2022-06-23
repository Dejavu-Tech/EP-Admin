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

class AreaModel{


	public function get_area_id_by_name($name='',$pid=0)
    {

		$area_info = M('eaterplanet_ecommerce_area')->where( array('name' => $name) )->find();

        if( empty($area_info) )
        {

			$max_code_info = M('eaterplanet_ecommerce_area')->field('code')->order('code desc')->find();
			$max_code = $max_code_info['code'];

            $max_code = $max_code +1;

            $data = array();
            $data['name'] = $name;
            $data['pid'] = $pid;
            $data['code'] = $max_code;

			$id = M('eaterplanet_ecommerce_area')->add($data);
            return $id;
        }else{
            return $area_info['id'];
        }

    }

	public function get_area_info($id=0)
    {
		$area_info = M('eaterplanet_ecommerce_area')->where( array('id' => $id ) )->find();

        return $area_info['name'];
    }

	public function getAreas( $is_parse = false)
	{
		global $_W;
		global $_GPC;


		//$result = load_class('cache')->getArray('areas', $uniacid);

		$result = S('areas_list');

		if (empty($result)) {
			$result = array();
			//@attributes

			$provinces = M('eaterplanet_ecommerce_area')->field('id,name,code')->where( array('pid' => 0) )->order('code asc')->select();

			$result['province'][] = array(
				'@attributes' => array(
									'name'=>'请选择省份',
									'city'=>array(
										'@attributes'=>array('name' =>'请选择城市','county' => array(
											'@attributes' => array('name' => '请选择区域')
										)) ) )
				);


			foreach($provinces as $key => $val)
			{
				$province_tmp = array();
				$province_tmp['@attributes']['name'] = $val['name'];
				$province_tmp['@attributes']['code'] = $val['code'];

				$province_tmp['city'] = array();

				$city_list = M('eaterplanet_ecommerce_area')->field('id,name,code')->where( array('pid' => $val['id']) )->order('code asc')->select();

				$city_tmp_list = array();

				foreach($city_list as $vv)
				{
					$city_tmp = array();
					$city_tmp['@attributes']['name'] = $vv['name'];
					$city_tmp['@attributes']['code'] = $vv['code'];
					$city_tmp['country'] = array();

					$country_list = M('eaterplanet_ecommerce_area')->field('id,name,code')->where( array('pid' => $vv['id']) )->order('code asc')->select();

					$country_tmp_list = array();

					if( !empty($country_list) )
					{
						foreach($country_list as $vvv)
						{
							$country_tmp = array();
							$country_tmp['@attributes']['name'] = $vvv['name'];
							$country_tmp['@attributes']['code'] = $vvv['code'];

							$country_tmp_list[] = $country_tmp;
						}
					}
					$city_tmp['country'] = $country_tmp_list;

					$city_tmp_list[] = $city_tmp;
				}

				$province_tmp['city'] = $city_tmp_list;
				$result['province'][] = $province_tmp;
			}
			//load_class('cache')->set('areas', $result, $uniacid);
			S('areas_list', $result);
		}


		return $result;
	}

}
?>
