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

namespace Home\Controller;

class PresalegoodsController extends CommonController {

    protected function _initialize()
    {
    	parent::_initialize();
	}
    public function index(){

        $presale_index_info = [];
        //1、顶部幻灯片
        $slider_list = M('eaterplanet_ecommerce_adv')->where( array('enabled' => 1,'type' => 'presale') )->order('displayorder desc, id desc')->select();

        if(!empty($slider_list))
        {
            foreach($slider_list as $key => $val)
            {
                $val['image'] = tomedia($val['thumb']);
                $slider_list[$key] = $val;
            }
            $presale_index_info['has_slider'] = 1;
        }else{
            $slider_list = array();
            $presale_index_info['has_slider'] = 0;
        }

        $presale_index_info['slider_list'] = $slider_list;
        //2、分享标题 分享图片
        $presale_share_title = D('Home/Front')->get_config_by_name('presale_share_title');
        $presale_share_img = D('Home/Front')->get_config_by_name('presale_share_img');
        $presale_publish = D('Home/Front')->get_config_by_name('presale_publish');

        $presale_index_info['presale_share_title'] = empty($presale_share_title) ? '' : $presale_share_title;
        $presale_index_info['presale_share_img'] = empty($presale_share_img) ? '' : tomedia($presale_share_img);
        $presale_index_info['presale_publish'] = empty($presale_publish) ? '' : htmlspecialchars_decode($presale_publish);

        //presale_layout 布局 0 左右布局， 1 横向布局
        $presale_layout = D('Home/Front')->get_config_by_name('presale_layout');

        $presale_layout = empty($presale_layout) ? 0 : 1;
        $presale_index_info['presale_layout'] = $presale_layout;


        echo json_encode( ['code' => 0, 'data' => $presale_index_info ] );
        die();
    }

    /**
     * @author yj
     * @desc 获取商品列表有分页
     */
    public function load_goods_list()
    {
        $result = D('Home/PresaleGoods')->getIndexPresaleGoods(0);

        echo json_encode( $result );
        die();
    }



}
