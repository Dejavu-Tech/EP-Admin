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
namespace Seller\Controller;

class ConfigController extends CommonController{

	protected function _initialize(){
		parent::_initialize();

		//'pinjie' => '拼团介绍',
	}

	public function index()
	{
		if (IS_POST) {

			$data = I('request.parameter');
			$data['shoname'] = trim($data['shoname']);
			$data['shoplogo'] = save_media($data['shoplogo']);
			$data['shop_summary'] = trim($data['shop_summary']);
			$data['shop_index_share_title'] = trim($data['shop_index_share_title']);
			$data['open_diy_index_page'] = intval($data['open_diy_index_page']);
			$data['index_list_theme_type'] = intval($data['index_list_theme_type']);






			$data['shop_index_share_image'] = save_media($data['shop_index_share_image']);
			$data['group_name'] = trim($data['group_name']);
			$data['owner_name'] = trim($data['owner_name']);
			$data['index_share_switch'] = intval($data['index_share_switch']);
			$data['index_change_cate_btn'] = intval($data['index_change_cate_btn']);
			$data['index_top_img_bg_open'] = intval($data['index_top_img_bg_open']);
			$data['index_top_font_color'] = trim($data['index_top_font_color']);
			$data['index_service_switch'] = intval($data['index_service_switch']);
			$data['index_switch_search'] = intval($data['index_switch_search']);
			$data['hide_community_change_btn'] = intval($data['hide_community_change_btn']);
			$data['hide_index_top_communityinfo'] = intval($data['hide_index_top_communityinfo']);
			$data['index_type_first_name'] = $data['index_type_first_name'];
			$data['ishow_index_copy_text'] = intval($data['ishow_index_copy_text']);
			$data['ishow_special_share_btn'] = intval($data['ishow_special_share_btn']);


			D('Seller/Config')->update($data);


			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;

		$this->display();
	}

	public function links()
	{
		$this->display();
	}


	public function clearqrcode()
	{
		M('eaterplanet_ecommerce_member')->where( "1>0" )->save( array('hexiao_qrcod' => '') );

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}

	/**
	 * 图片设置
	 */
	public function picture()
	{

		if (IS_POST) {

			$data = I('request.parameter');
			$data['admin_login_image'] = save_media($data['admin_login_image']);
			$data['seller_backimage'] = save_media($data['seller_backimage']);
			$data['saleout'] = save_media($data['saleout']);
			$data['loading'] = save_media($data['loading']);
			$data['kanjia_index_image'] = trim($data['shop_index_share_title']);
			$data['pintuan_index_image'] = save_media($data['pintuan_index_image']);


			$data['index_list_top_image'] = save_media($data['index_list_top_image']);
			$data['new_group_index_image'] = save_media($data['new_group_index_image']);
			$data['fenxiao_apply_index_image'] = save_media($data['fenxiao_apply_index_image']);

			$data['goods_details_middle_image'] = save_media($data['goods_details_middle_image']);
			$data['index_lead_image'] = save_media($data['index_lead_image']);
			$data['auth_bg_image'] = save_media($data['auth_bg_image']);

			$data['common_header_backgroundimage'] = save_media($data['common_header_backgroundimage']);
			$data['index_header_backgroundimage'] = save_media($data['index_header_backgroundimage']);
			$data['user_header_backgroundimage'] = save_media($data['user_header_backgroundimage']);



			$datas = D('Seller/Config')->get_all_config();

			$data['index_share_qrcode_bg'] = save_media($data['index_share_qrcode_bg']);

			if( $datas['index_share_qrcode_bg'] != $data['index_share_qrcode_bg'] )
			{
				//清理二维码 community_config_qrcode_40  uniacid
				M('eaterplanet_ecommerce_config')->where( " name like 'community_config_qrcode_%' " )->delete();
			}


			$data['is_show_index_lead_image'] = $data['is_show_index_lead_image'];
			$data['index_list_top_image_on'] = $data['index_list_top_image_on'];



			D('Seller/Config')->update($data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;

		$this->display('Config/picture');
	}
}
?>
