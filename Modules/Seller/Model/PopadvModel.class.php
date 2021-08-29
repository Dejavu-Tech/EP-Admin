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

class PopadvModel{


	public function update($_GPC)
	{
	    $data = $_GPC['data'];
        $time = $_GPC['time'];
		$ins_data = array();
		$ins_data['adv_name'] = $data['adv_name'];
		$ins_data['begin_time'] = strtotime($time['start']);
		$ins_data['end_time'] = strtotime($time['end']);
		$ins_data['send_person'] = $data['send_person'];
		$ins_data['member_id'] = $_GPC['member_id'];
		$ins_data['member_group_id'] = $data['member_group_id'];
        $ins_data['pop_page'] = $data['pop_page'];
        $ins_data['is_index_show'] = $data['is_index_show'];
        $ins_data['show_hour'] = $data['show_hour'];
        $ins_data['sort_order'] = $data['sort_order'];
        $ins_data['status'] = $data['status'];
		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			$add_time = time();
			M('eaterplanet_ecommerce_pop_adv')->where( array('id' => $id) )->save( $ins_data );
			$aid = $_GPC['aid'];
			$thumb = $_GPC['thumb'];
			$adv_id = $_GPC['adv_id'];
			M('eaterplanet_ecommerce_pop_adv_list')->where( array('ad_id' => $id) )->delete();
			for($i = 0;$i < count($aid);$i++){
				$adv_data = array();
				$adv_data['ad_id'] = $id;
				$adv_data['thumb'] = $thumb[$i];
				$adv_data['link'] = $data['link_'.$aid[$i]];
				$adv_data['linktype'] = $data['linktype_'.$aid[$i]];
				$adv_data['appid'] = $data['appid_'.$aid[$i]];
				$adv_data['addtime'] = $add_time;
				M('eaterplanet_ecommerce_pop_adv_list')->add($adv_data);
			}
		}else{
		    $add_time = time();
			$ins_data['addtime'] = $add_time;
			$adv_id = M('eaterplanet_ecommerce_pop_adv')->add($ins_data);

			$aid = $_GPC['aid'];
			$thumb = $_GPC['thumb'];
			for($i = 0;$i < count($aid);$i++){
			    $adv_data = array();
                $adv_data['ad_id'] = $adv_id;
                $adv_data['thumb'] = $thumb[$i];
                $adv_data['link'] = $data['link_'.$aid[$i]];
                $adv_data['linktype'] = $data['linktype_'.$aid[$i]];
                $adv_data['appid'] = $data['appid_'.$aid[$i]];
                $adv_data['addtime'] = $add_time;
                M('eaterplanet_ecommerce_pop_adv_list')->add($adv_data);
            }
		}
	}


}
?>
