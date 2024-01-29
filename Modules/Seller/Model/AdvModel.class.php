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

class AdvModel{


	public function update($data,$type='slider')
	{

		$ins_data = array();
		$ins_data['advname'] = $data['advname'];
		$ins_data['thumb'] = save_media($data['thumb']);
		$ins_data['link'] = $data['link'];
		$ins_data['displayorder'] = $data['displayorder'];
		$ins_data['enabled'] = $data['enabled'];
		$ins_data['addtime'] = time();
		$ins_data['type'] = $type;
		$ins_data['linktype'] = $data['linktype'];
		$ins_data['appid'] = $data['appid'];

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);

			M('eaterplanet_ecommerce_adv')->where( array('id' => $id) )->save( $ins_data );

			$id = $data['id'];

		}else{
			$id = M('eaterplanet_ecommerce_adv')->add( $ins_data );
		}


	}

    public function updatecagetory($data,$type='slider')
    {

        $ins_data = array();
        $ins_data['advname'] = $data['advname'];
        $ins_data['thumb'] = save_media($data['thumb']);
        $ins_data['link'] = $data['link'];
        $ins_data['cate_id'] = $data['cate_id'];
        $ins_data['displayorder'] = $data['displayorder'];
        $ins_data['enabled'] = $data['enabled'];
        $ins_data['addtime'] = time();
        $ins_data['type'] = $type;
        $ins_data['linktype'] = $data['linktype'];
        $ins_data['appid'] = $data['appid'];

        $id = $data['id'];
        if( !empty($id) && $id > 0 )
        {
            //show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
            $check_res = M('eaterplanet_ecommerce_adv_category')->where("cate_id=".$data['cate_id']." and id != {$id} ")->find();
            if( !empty($check_res) )
            {
                show_json(0, array('message' => '该分类下已经存在广告' ));
            }
            unset($ins_data['addtime']);

            M('eaterplanet_ecommerce_adv_category')->where( array('id' => $id) )->save( $ins_data );

            $id = $data['id'];

        }else{
            $check_res = M('eaterplanet_ecommerce_adv_category')->where("cate_id=".$data['cate_id']."  ")->find();
            if( !empty($check_res) )
            {
                show_json(0, array('message' => '该分类下已经存在广告' ));
            }

            $id = M('eaterplanet_ecommerce_adv_category')->add( $ins_data );
        }


	}


	// 导航图标更新
	public function navigat_update($data)
	{

		$ins_data = array();
		$ins_data['navname'] = $data['navname'];
		$ins_data['appid'] = $data['appid'];
		$ins_data['thumb'] = save_media($data['thumb']);
		$ins_data['link'] = $data['link'];
		$ins_data['displayorder'] = $data['displayorder'];
		$ins_data['enabled'] = $data['enabled'];
		$ins_data['addtime'] = time();
		$ins_data['type'] = $data['type'];

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			unset($ins_data['addtime']);
			M('eaterplanet_ecommerce_navigat')->where( array('id' => $id) )->save( $ins_data );

			$id = $data['id'];
		}else{
			$id = M('eaterplanet_ecommerce_navigat')->add( $ins_data );
		}


	}


}
?>
