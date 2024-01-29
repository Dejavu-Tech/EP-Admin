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

class AdvimgModel{


	public function update($_GPC)
	{
	    $data = $_GPC['data'];
	    $pos = $_GPC['pos'];
	    if($data['linktype']==3 || $data['linktype']==4){
            $data['link'] = $data['cid'];
        }

		$ins_data = array();
		$id = $data['id'];
		$ins_data['thumb'] = save_media($data['thumb']);
		$ins_data['link'] = $data['link'];
		$ins_data['linktype'] = $data['linktype'];
		$ins_data['appid'] = $data['appid'];
		$ins_data['displayorder'] = $data['displayorder'];
		$ins_data['enabled'] = $data['enabled'];
		$ins_data['pos'] = implode(',', $pos);
		$ins_data['addtime'] = time();

		if( !empty($id) && $id > 0 )
		{
			M('eaterplanet_ecommerce_advimg')->where( array('id' => $id) )->save( $ins_data );
		}else{
			M('eaterplanet_ecommerce_advimg')->add($ins_data);
		}
	}
}
?>
