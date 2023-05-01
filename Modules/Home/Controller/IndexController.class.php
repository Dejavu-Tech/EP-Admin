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


class IndexController extends CommonController {
    protected function _initialize()
    {

    	parent::_initialize();

    }
	public function index_share()
	{
		$item = M('eaterplanet_ecommerce_config')->where( array('name' => 'shop_index_share_title') )->find();

		echo json_encode( array('code'=>0, 'title' =>$item['value']) );
		die();
	}

	public function test_sql()
	{
		include ROOT_PATH .'Data/sql.php';
		/**
		$res =  check_db_field_exist( 'eaterplanet_community_head_commiss_order','fen_type' );
		if( !$res )
		{
			$sql_content ="
			 ALTER TABLE `eaterplanet_community_head_commiss_order` ADD `fen_type` TINYINT(1) NULL DEFAULT '0' COMMENT '0,按照比例计算的，1按照实际金额计算的' AFTER `type`;
			";
			M()->execute($sql_content);
		}
		**/
		echo 3;die();
	}

	//$filePath = ROOT_PATH.'64df107ec8374b0bc0d8cb1c9a06d819.jpeg';

	public function test()
	{
	    echo get_client_ip();
	    die();
		$order_info = D('Home/EpOrder')->getOrderGoodsByOrderId( $order_id );

		var_dump( $order_info );
		die();

	}
	public function diy_page()
	{

		$startadv = array();
		return $startadv;

	}

	public function spike_index()
	{

	}

	public function load_spike_data()
	{

	}

	public function index_info()
	{

		$gpc = I('request.');

		$item = M('eaterplanet_ecommerce_config')->where( array('name' => 'shop_index_share_title') )->find();


		$communityId = $gpc['communityId'];
		$is_community = D('Seller/Communityhead')->is_community($communityId);

		$postion = M('eaterplanet_community_head')->field('lat,lon')->where( array('id' => $communityId) )->find();

		//...eaterplanet_ecommerce_adv
		$params = array();
	    $params[':uniacid'] = $uniacid;
	    $params[':type'] = 'slider';
	    $params[':enabled'] = 1;

		/**  调用滑动广告 **/
		$slider_list = M('eaterplanet_ecommerce_adv')->where( array('enabled' => 1,'type' => 'slider') )->order('displayorder desc, id desc')->select();

		if(!empty($slider_list))
		{
			foreach($slider_list as $key => $val)
			{
				$val['image'] = tomedia($val['thumb']);
				$val['link'] = htmlspecialchars_decode( $val['link'] );
				$slider_list[$key] = $val;
			}
		}else{
			$slider_list = array();
		}

		// 公告列表

		$notice_list = M('eaterplanet_ecommerce_notice')->where( array('enabled' => 1) )->order('displayorder desc, addtime desc')->select();

		/**  调用导航图标  **/

		$nav_list = M('eaterplanet_ecommerce_adv')->where( array('enabled' => 1,'type' => 'nav') )->order('displayorder desc, id desc')->select();


		if(!empty($nav_list))
		{
			foreach($nav_list as $key => $val)
			{
				$val['image'] = tomedia($val['thumb']);
				$val['name'] = $val['advname'];
				$val['link'] = htmlspecialchars_decode($val['link']);
				$nav_list[$key] = $val;
			}
		}else{
			$nav_list = array();
		}

		/**
			调用分类
		**/
		$gid = isset($gpc['gid']) ? intval($gpc['gid']) : 0;
		$category_list = D('Home/GoodsCategory')->get_all_goods_category('normal', -1);
		// 过滤分类
		$cateidArr = array();
		foreach ($category_list as $k => $v) {
			$cateidArr[] = $v['id'];
		}
		$new_category_list = array();
		foreach ($category_list as $k => $v) {
			if($v['is_show']==1) {
				if($v['pid']==0) {
					$new_category_list[] = $v;
				} else {
					if(in_array($v['pid'], $cateidArr)) {
						$new_category_list[] = $v;
					}
				}
			}
		}
		$category_list = $new_category_list;

		$shop_index_share_image = D('Home/Front')->get_config_by_name('shop_index_share_image');

		if( !empty($shop_index_share_image) )
		{
			$shop_index_share_image = tomedia($shop_index_share_image);
		}

		$index_loading_image = D('Home/Front')->get_config_by_name('loading');

		if( !empty($index_loading_image) )
		{
			$index_loading_image = tomedia($index_loading_image);
		}

		$index_bottom_image = D('Home/Front')->get_config_by_name('index_bottom_image');

		if( !empty($index_bottom_image) )
		{
			$index_bottom_image = tomedia($index_bottom_image);
		}

		$index_list_top_image = D('Home/Front')->get_config_by_name('index_list_top_image');

		if( !empty($index_list_top_image) )
		{
			$index_list_top_image = tomedia($index_list_top_image);
		}

		$index_list_top_image_on = D('Home/Front')->get_config_by_name('index_list_top_image_on');


		$common_header_backgroundimage = D('Home/Front')->get_config_by_name('index_header_backgroundimage');
		if( !empty($common_header_backgroundimage) )
		{
			$common_header_backgroundimage = tomedia($common_header_backgroundimage);
		}


		$open_diy_index_page = D('Home/Front')->get_config_by_name('open_diy_index_page');


		$is_show_index_lead_image = D('Home/Front')->get_config_by_name('is_show_index_lead_image');
		$index_lead_image = '';
		if($is_show_index_lead_image == 1){
			$index_lead_image = D('Home/Front')->get_config_by_name('index_lead_image');
			if( !empty($index_lead_image) )
			{
				$index_lead_image = tomedia($index_lead_image);
			}
		}


		$diypage = array();
		if($open_diy_index_page == 1)
		{
			 $diypage = $this->diy_page();
		}
		$shoname = D('Home/Front')->get_config_by_name('shoname');

		$theme = D('Home/Front')->get_config_by_name('index_list_theme_type');


		$spike_data = array();
		$nav_bg_color = D('Home/Front')->get_config_by_name('nav_bg_color');
		$order_notify_switch = D('Home/Front')->get_config_by_name('order_notify_switch');





		$rushtime =  D('Home/Pingoods')->get_min_time();

		$comming_goods_total = D('Home/Pingoods')->get_comming_goods_count();

		$index_share_switch = D('Home/Front')->get_config_by_name('index_share_switch');
		$is_show_list_count = D('Home/Front')->get_config_by_name('is_show_list_count');
		$is_show_list_timer = D('Home/Front')->get_config_by_name('is_show_list_timer');
		$index_change_cate_btn = D('Home/Front')->get_config_by_name('index_change_cate_btn');


		$is_comunity_rest = D('Seller/Communityhead')->is_community_rest($communityId);



		$index_top_img_bg_open = D('Home/Front')->get_config_by_name('index_top_img_bg_open');
		$index_top_font_color = D('Home/Front')->get_config_by_name('index_top_font_color');
		$index_service_switch = D('Home/Front')->get_config_by_name('index_service_switch');
		$index_switch_search = D('Home/Front')->get_config_by_name('index_switch_search');
		$is_show_new_buy = D('Home/Front')->get_config_by_name('is_show_new_buy');


		//抢购自定义
		$index_qgtab_bottom_color = D('Home/Front')->get_config_by_name('index_qgtab_bottom_color');
		$index_qgtab_one_select = D('Home/Front')->get_config_by_name('index_qgtab_one_select');
		$index_qgtab_one_selected = D('Home/Front')->get_config_by_name('index_qgtab_one_selected');
		$index_qgtab_two_select = D('Home/Front')->get_config_by_name('index_qgtab_two_select');
		$index_qgtab_two_selected = D('Home/Front')->get_config_by_name('index_qgtab_two_selected');

		$qgtab = array();
		$qgtab['bottom_color'] = $index_qgtab_bottom_color;
		if(!empty($index_qgtab_one_select)) {
			$qgtab['one_select'] = tomedia($index_qgtab_one_select);
		}
		if(!empty($index_qgtab_one_selected)) {
			$qgtab['one_selected'] = tomedia($index_qgtab_one_selected);
		}
		if(!empty($index_qgtab_two_select)) {
			$qgtab['two_select'] = tomedia($index_qgtab_two_select);
		}
		if(!empty($index_qgtab_two_selected)) {
			$qgtab['two_selected'] = tomedia($index_qgtab_two_selected);
		}


		$notice_setting = array();
		$index_notice_horn_image = D('Home/Front')->get_config_by_name('index_notice_horn_image');
		if(!empty($index_notice_horn_image)) {
			$notice_setting['horn'] = tomedia($index_notice_horn_image);
		}
		$notice_setting['font_color'] = D('Home/Front')->get_config_by_name('index_notice_font_color');
		$notice_setting['background_color'] = D('Home/Front')->get_config_by_name('index_notice_background_color');

		//前端隐藏 团长信息
		$index_hide_headdetail_address = D('Home/Front')->get_config_by_name('index_hide_headdetail_address');

		if( empty($index_hide_headdetail_address) )
		{
			$index_hide_headdetail_address = 0;
		}

		$is_show_spike_buy = D('Home/Front')->get_config_by_name('is_show_spike_buy');
		$hide_community_change_btn = D('Home/Front')->get_config_by_name('hide_community_change_btn');
		$hide_top_community = D('Home/Front')->get_config_by_name('hide_index_top_communityinfo');
		$index_type_first_name = D('Home/Front')->get_config_by_name('index_type_first_name');


		$index_qgtab_text = array();
		$index_qgtab_text[] = D('Home/Front')->get_config_by_name('index_qgtab_text_going');
		$index_qgtab_text[] = D('Home/Front')->get_config_by_name('index_qgtab_text_future');

		$ishow_index_copy_text = D('Home/Front')->get_config_by_name('ishow_index_copy_text');

		$index_communityinfo_showtype = D('Home/Front')->get_config_by_name('index_communityinfo_showtype');

		// 魔方图
        $cube = array();

		$cube =  M('eaterplanet_ecommerce_cube')->where( array('enabled' => 1) )->order('displayorder desc, addtime desc')->select();

        if(!empty($cube)) {
            foreach ($cube as $k => $cubeItem) {
                $thumb = unserialize($cubeItem['thumb']);
                if(!empty($thumb['cover']) && is_array($thumb['cover'])) {
                    foreach ($thumb['cover'] as &$coverItem) {
                        if($coverItem) $coverItem = tomedia($coverItem);
                    }
                }
		if( !empty($thumb['link']) )
		{
			foreach($thumb['link'] as $kk => $links)
			{
				$thumb['link'][$kk] = htmlspecialchars_decode($links);
			}
		}
		if( !empty($thumb['webview']) )
		{
			foreach($thumb['webview'] as $kkk => $webview)
			{
				$thumb['webview'][$kkk] = htmlspecialchars_decode($webview);
			}
		}

		if( !empty($thumb['outlink']) )
		{
			foreach($thumb['outlink'] as $kkkk => $outlink)
			{
				$thumb['outlink'][$kkkk] = htmlspecialchars_decode($outlink);
			}
		}
                $cubeItem['thumb'] = $thumb;
                $cube[$k] = $cubeItem;
            }
        }


		//秒杀设置begin
		$seckill_is_open 		= D('Home/Front')->get_config_by_name('seckill_is_open');
		$seckill_is_show_index 	= D('Home/Front')->get_config_by_name('seckill_is_show_index');
		$scekill_show_time 	= D('Home/Front')->get_config_by_name('scekill_show_time');
		$seckill_bg_color 	= D('Home/Front')->get_config_by_name('seckill_bg_color');

		$hide_community_change_word 	= D('Home/Front')->get_config_by_name('hide_community_change_word');

		if( empty($seckill_bg_color) )
		{
			$seckill_bg_color = '#ea404b';
		}

		if( empty($seckill_is_open) )
		{
			$seckill_is_open = 0;
		}
		if( empty($seckill_is_show_index) )
		{
			$seckill_is_show_index = 0;
		}

		$scekill_time_arr = array();

		if( $seckill_is_open == 1 )
		{
			if( $seckill_is_show_index == 1 )
			{
				if( isset($scekill_show_time) && !empty($scekill_show_time) )
				{
					$scekill_show_time_arr = unserialize($scekill_show_time);

					foreach($scekill_show_time_arr as $vv)
					{
						if( $vv != 25 )
						{
							$scekill_time_arr[] = $vv;
						}
					}
				}
			}
		}else{
			$seckill_is_show_index = 0;
		}
		//整点秒杀结束

		//返回顶部按钮
		$ishow_index_gotop 	= D('Home/Front')->get_config_by_name('ishow_index_gotop');

		$ishow_index_pickup_time = D('Home/Front')->get_config_by_name('ishow_index_pickup_time');

		// 视频
		$index_video_arr = array();
		$index_video_enabled = D('Home/Front')->get_config_by_name('index_video_enabled');
		$index_video_arr['enabled'] = $index_video_enabled;
		if($index_video_enabled==1) {
			$index_video_poster = D('Home/Front')->get_config_by_name('index_video_poster');
			$index_video_url = D('Home/Front')->get_config_by_name('index_video_url');
			if($index_video_poster){
				$index_video_arr['poster'] = tomedia($index_video_poster);
			}
			if($index_video_url){
				$index_video_arr['url'] = tomedia($index_video_url);
			}
		}

		// 抢购时间显示
		$index_qgtab_counttime 	= D('Home/Front')->get_config_by_name('index_qgtab_counttime');

		$hide_index_type = D('Home/Front')->get_config_by_name('hide_index_type');

		// 公众号关注组件
		$show_index_wechat_oa =  D('Home/Front')->get_config_by_name('show_index_wechat_oa');

		$ishide_index_goodslist = D('Home/Front')->get_config_by_name('ishide_index_goodslist');

		$can_index_notice_alert = D('Home/Front')->get_config_by_name('can_index_notice_alert');

		//是否开启预售活动==begin
        $show_presale_index_goods = D('Home/Front')->get_config_by_name('isopen_presale');
        $show_presale_index_goods = !isset($show_presale_index_goods) || empty($show_presale_index_goods) ? 0 : 1;

        $presale_index_info = [];
        $presale_index_info['show_presale_index_goods'] = $show_presale_index_goods;
        $presale_index_info['goods_list'] = [];

        if( $show_presale_index_goods == 1 )
        {
            //1、获取封面
            $presale_index_coming_img = D('Home/Front')->get_config_by_name('presale_index_coming_img');
            if( isset($presale_index_coming_img) && !empty($presale_index_coming_img) )
            {
                $presale_index_info['presale_index_coming_img'] = tomedia($presale_index_coming_img);
            }
            //2、获取首页显示的商品。后台没有定义就显示5条最多
            $presale_result = D('Home/PresaleGoods')->getIndexPresaleGoods(1);
            if( $presale_result['code'] == 0 && !empty($presale_result['list']) )
            {
                $presale_index_info['goods_list'] = $presale_result['list'];
            }
        }
        //首页预售活动end

        //是否开启礼品卡 begin
        $virtualcard_info = [];
        $isopen_virtualcard = D('Home/Front')->get_config_by_name('isopen_virtualcard');
        $isopen_virtualcard = !isset($isopen_virtualcard) ? 0 : $isopen_virtualcard;
        $virtualcard_info['isopen_virtualcard'] = $isopen_virtualcard;
        $virtualcard_info['goods_list'] = [];
        if( $isopen_virtualcard == 1 )
        {
            //入口封面图
            $virtualcard_index_coming_img = D('Home/Front')->get_config_by_name('virtualcard_index_coming_img');
            $virtualcard_info['virtualcard_index_coming_img'] = empty($virtualcard_index_coming_img) ? '' : tomedia( tomedia($virtualcard_index_coming_img) );

            //礼品卡商品
            $virtualcard_result = D('Seller/VirtualCard')->getIndexVirturalCardGoods(1);
            if( $virtualcard_result['code'] == 0 && !empty($virtualcard_result['list']) )
            {
                $virtualcard_info['goods_list'] = $virtualcard_result['list'];
            }
        }
        //是否开启礼品卡 end
		echo json_encode(array('code'=>0,
						'category_list' =>$category_list,
						'spike_data' => array(),
						'shop_index_share_image' => $shop_index_share_image,
						'index_loading_image' => $index_loading_image,
						'index_bottom_image' => $index_bottom_image,
						'title' =>$item['value'],'shoname'=>$shoname,
						'slider_list' => $slider_list,
						'nav_list' => $nav_list,
						'open_diy_index_page' => $open_diy_index_page,
						'diypage' => $diypage,
						'notice_list' => $notice_list ,
						'index_list_top_image' => $index_list_top_image,
						'is_community' => $is_community,
						'index_lead_image' => $index_lead_image,
						'theme' => $theme,
						'common_header_backgroundimage' => $common_header_backgroundimage,
						'nav_bg_color' => $nav_bg_color,
						'order_notify_switch' => $order_notify_switch,
						'is_quan' => $is_quan,
						'index_list_top_image_on' => $index_list_top_image_on,
						'postion' => $postion,
						'rushtime' => $rushtime,
						'comming_goods_total' => $comming_goods_total,
						'index_share_switch' => $index_share_switch,
						'is_show_list_count' => $is_show_list_count,
						'is_show_list_timer' => $is_show_list_timer,
						'is_comunity_rest' => $is_comunity_rest,
						'index_change_cate_btn' => $index_change_cate_btn,
						'index_top_img_bg_open' => $index_top_img_bg_open,
						'index_top_font_color' => $index_top_font_color,
						'index_service_switch' => $index_service_switch,
						'index_switch_search' => $index_switch_search,
						'is_show_new_buy' => $is_show_new_buy,
						'qgtab' => $qgtab,
						'notice_setting' => $notice_setting,
						'index_hide_headdetail_address' => $index_hide_headdetail_address,
						'is_show_spike_buy' => $is_show_spike_buy,
						'hide_community_change_btn' => $hide_community_change_btn,
						'hide_top_community' => $hide_top_community,
						'index_type_first_name' => $index_type_first_name,
						'index_qgtab_text' => $index_qgtab_text,
						'ishow_index_copy_text' => $ishow_index_copy_text,
						'index_communityinfo_showtype' => $index_communityinfo_showtype,
						'cube' => $cube,
						'seckill_is_open'=> $seckill_is_open,//是否开启整点秒杀功能
						'seckill_is_show_index' => $seckill_is_show_index,//是否显示再首页上
						'scekill_time_arr' => $scekill_time_arr,//整点秒杀的时间点数组， 0 点表示：0:00-0:59， 1 表示：1:00-1:59
						'seckill_bg_color' => $seckill_bg_color,
						'hide_community_change_word' => $hide_community_change_word,
						'ishow_index_gotop' => $ishow_index_gotop,
						'ishow_index_pickup_time' => $ishow_index_pickup_time,
						'index_video_arr' => $index_video_arr,
						'index_qgtab_counttime' => $index_qgtab_counttime,
						'hide_index_type' => $hide_index_type,
						'show_index_wechat_oa' => $show_index_wechat_oa,
						'ishide_index_goodslist' => $ishide_index_goodslist,
						'can_index_notice_alert' => $can_index_notice_alert,
						'presale_index_info' => $presale_index_info,//首页预售商品信息
						'virtualcard_info' => $virtualcard_info,//礼品卡首页信息
					)
			);
		die();
	}

	public function get_group_info()
	{


		$group_name = D('Home/Front')->get_config_by_name('group_name');
		$owner_name = D('Home/Front')->get_config_by_name('owner_name');
		$commiss_diy_name = D('Home/Front')->get_config_by_name('commiss_diy_name');

		$delivery_ziti_name = D('Home/Front')->get_config_by_name('delivery_ziti_name');
		$delivery_tuanzshipping_name = D('Home/Front')->get_config_by_name('delivery_tuanzshipping_name');
		$delivery_express_name = D('Home/Front')->get_config_by_name('delivery_express_name');


		// 下单页面
		$placeorder_tuan_name = D('Home/Front')->get_config_by_name('placeorder_tuan_name');
		$placeorder_trans_name = D('Home/Front')->get_config_by_name('placeorder_trans_name');
		$localtown_modifypickingname = D('Home/Front')->get_config_by_name('localtown_modifypickingname');

		$placeorder_tuan_name = $placeorder_tuan_name?$placeorder_tuan_name:"配送费";
		$placeorder_trans_name = $placeorder_trans_name?$placeorder_trans_name:"快递费";
		$localtown_modifypickingname = $localtown_modifypickingname?$localtown_modifypickingname:"包装费";



		$data = array(
			'group_name'=>$group_name,
			'owner_name'=>$owner_name,
			'commiss_diy_name'=>$commiss_diy_name,
			'delivery_ziti_name'=>$delivery_ziti_name,
			'delivery_tuanzshipping_name'=>$delivery_tuanzshipping_name,
			'delivery_express_name'=>$delivery_express_name,
			'placeorder_tuan_name'=>$placeorder_tuan_name,
			'placeorder_trans_name'=>$placeorder_trans_name,
			'localtown_modifypickingname'=>$localtown_modifypickingname
		);

		echo json_encode(array('code'=>0, 'data' =>$data));
	}


	public function get_nav_bg_color()
	{

		$nav_bg_color = D('Home/Front')->get_config_by_name('nav_bg_color');
		$nav_font_color = D('Home/Front')->get_config_by_name('nav_font_color');
		echo json_encode(array('code'=>0, 'data' => $nav_bg_color, 'nav_font_color' => $nav_font_color));
	}

	public function get_auth_bg()
	{
		$auth_bg_image = D('Home/Front')->get_config_by_name('auth_bg_image');
		if( !empty($auth_bg_image) )
		{
			$auth_bg_image = tomedia($auth_bg_image);
		}
		echo json_encode(array('code'=>0, 'data' =>$auth_bg_image));
	}


	/**
	 * 获取导航图标 eaterplanet_ecommerce_navigat
	 */
	public function get_navigat()
	{
		$list = M('eaterplanet_ecommerce_navigat')->field('id,navname,thumb,link,type,appid')->where("enabled=1")->order('displayorder desc')->select();

		if( !empty($list) )
		{
			foreach ($list as $key => &$val) {
				$val['thumb'] = tomedia($val['thumb']);
				$val['link'] = htmlspecialchars_decode($val['link']);
			}
		}

		$result = array('code' =>0,'data' => $list);
		echo json_encode($result);
		die();
	}

	/**
	 * 获取tabbar
	 */
	public function get_tabbar()
	{

		$list = D('Home/Front')->get_config_by_name('wepro_tabbar_list');

		/**

		$param['wepro_tabbar_list'] = array();
			$param['wepro_tabbar_list']['t1'] = trim($data['wepro_tabbar_text1']);
			$param['wepro_tabbar_list']['t2'] = trim($data['wepro_tabbar_text2']);
			$param['wepro_tabbar_list']['t3'] = trim($data['wepro_tabbar_text3']);
			$param['wepro_tabbar_list']['s1'] = save_media($data['wepro_tabbar_selectedIconPath1']);
			$param['wepro_tabbar_list']['s2'] = save_media($data['wepro_tabbar_selectedIconPath2']);
			$param['wepro_tabbar_list']['s3'] = save_media($data['wepro_tabbar_selectedIconPath3']);
			$param['wepro_tabbar_list']['i1'] = save_media($data['wepro_tabbar_iconPath1']);
			$param['wepro_tabbar_list']['i2'] = save_media($data['wepro_tabbar_iconPath2']);
			$param['wepro_tabbar_list']['i3'] = save_media($data['wepro_tabbar_iconPath3']);

			**/

		$list = unserialize(htmlspecialchars_decode($list));

		$open_tabbar_type = D('Home/Front')->get_config_by_name('open_tabbar_type');
		$open_tabbar_out_weapp = D('Home/Front')->get_config_by_name('open_tabbar_out_weapp');
		$tabbar_out_appid = D('Home/Front')->get_config_by_name('tabbar_out_appid');
		$tabbar_out_link = D('Home/Front')->get_config_by_name('tabbar_out_link');

		$tabbar_out_type = D('Home/Front')->get_config_by_name('tabbar_out_type');

		$wepro_tabbar_selectedColor = D('Home/Front')->get_config_by_name('wepro_tabbar_selectedColor');
		$wepro_tabbar_bgColor = D('Home/Front')->get_config_by_name('wepro_tabbar_bgColor');



		if( !empty($list) )
		{
			if(!empty($list['i1'])) $list['i1'] = tomedia($list['i1']);
			if(!empty($list['i2'])) $list['i2'] = tomedia($list['i2']);
			if(!empty($list['i3'])) $list['i3'] = tomedia($list['i3']);
			if(!empty($list['i4'])) $list['i4'] = tomedia($list['i4']);
			if(!empty($list['i5'])) $list['i5'] = tomedia($list['i5']);
			if(!empty($list['s1'])) $list['s1'] = tomedia($list['s1']);
			if(!empty($list['s2'])) $list['s2'] = tomedia($list['s2']);
			if(!empty($list['s3'])) $list['s3'] = tomedia($list['s3']);
			if(!empty($list['s4'])) $list['s4'] = tomedia($list['s4']);
			if(!empty($list['s5'])) $list['s5'] = tomedia($list['s5']);
		}


		$result = array(
			'code' => 0,
			'data' => $list,
			'open_tabbar_type' => $open_tabbar_type,
			'open_tabbar_out_weapp' => $open_tabbar_out_weapp,
			'tabbar_out_appid' => $tabbar_out_appid,
			'tabbar_out_link' => $tabbar_out_link,
			'tabbar_out_type' => $tabbar_out_type,
			'wepro_tabbar_selectedColor' => $wepro_tabbar_selectedColor,
			'wepro_tabbar_bgColor' => $wepro_tabbar_bgColor
		);
		echo json_encode($result);
		die();
	}


	public function get_community_config()
	{


		$tx_map_key = D('Home/Front')->get_config_by_name('tx_map_key');
		$shoname = D('Home/Front')->get_config_by_name('shoname');
		$shop_index_share_title = D('Home/Front')->get_config_by_name('shop_index_share_title');

		echo json_encode( array('code' => 0, 'tx_map_key' => $tx_map_key , 'shoname' => $shoname ,'shop_index_share_title' => $shop_index_share_title) );
		die();

	}


	public function load_history_community()
	{
		$gpc = I('request.');

		$token =  $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		$member_id = $weprogram_token['member_id'];

		$data = D('Home/Front')->get_history_community($member_id);


		if( empty($data) )
		{
			echo json_encode(array('code' => 1));
			die();
		}
		else {
			echo json_encode(array('code' => 0, 'list' => $data));
			die();
		}

	}

	/**
	 * 切换、添加历史社区
	 */
	public function switch_history_community(){
		$gpc = I('request.');

		$token =  $gpc['token'];
		$head_id =  $gpc['head_id'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		$member_id = $weprogram_token['member_id'];

		$data = D('Home/Front')->update_history_community($member_id, $head_id);

		//删除 community_id
		M('eaterplanet_ecommerce_car')->where( "token='{$token}' and community_id!='{$head_id}' " )->delete();

		if( empty($data) )
		{
			echo json_encode(array('code' => 1));
			die();
		}
		else {
			echo json_encode(array('code' => 0, 'list' => $data));
			die();
		}
	}

	public function get_community_info()
	{
		$gpc = I('request.');

		$where = " and state=1 and enable=1 ";

		$community_id =  $gpc['community_id'];
		if($community_id == 'undefined')
		{
			$community_id = 0;
		}

		$data = D('Home/Front')->get_community_byid($community_id , $where);

		$hide_community_change_btn = D('Home/Front')->get_config_by_name('hide_community_change_btn');


		$open_danhead_model = D('Home/Front')->get_config_by_name('open_danhead_model');

		if( empty($open_danhead_model) )
		{
			$open_danhead_model = 0;
		}

		$default_head_info = array();

		if( $open_danhead_model == 1 )
		{

			$default_head = M('eaterplanet_community_head')->field('id')->where(  array('is_default' => 1) )->find();

			if( !empty($default_head) )
			{
				$default_head_info = D('Home/Front')->get_community_byid($default_head['id'], $where);
			}
		}


		echo json_encode(array('code' => 0, 'data' => $data, 'open_danhead_model' => $open_danhead_model,'default_head_info' => $default_head_info, 'hide_community_change_btn' => $hide_community_change_btn));
		die();
	}

	public function load_gps_community()
	{
		$gpc = I('request.');

		$longitude = $gpc['longitude'];
		$latitude = $gpc['latitude'];
		$pageNum = $gpc['pageNum'];
		$city_id = $gpc['city_id'] ? $gpc['city_id'] : 0;


		$per_page = 10;
		$offset = ($pageNum - 1) * $per_page;

		$limit = "{$offset},{$per_page}";

		$token =  $gpc['token'];
		$keyword = $gpc['inputValue'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2) );
			//die();
		}
	    $member_id = $weprogram_token['member_id'];

	    $data = array();
		$open_danhead_model = D('Home/Front')->get_config_by_name('open_danhead_model');
		if( $open_danhead_model == 1 )
		{
			$default_head = M('eaterplanet_community_head')->field('id')->where(  array('is_default' => 1) )->find();
			if( !empty($default_head) )
			{
				$data = D('Home/Front')->get_community_byid($default_head['id'], $where);
			}
		} else {
			$data =  D('Home/Front')->get_gps_area_info($longitude,$latitude,$limit,$keyword,$city_id,0);
		}

		//前端隐藏 团长信息
		$index_hide_headdetail_address = D('Home/Front')->get_config_by_name('index_hide_headdetail_address');

		if( empty($index_hide_headdetail_address) )
		{
			$index_hide_headdetail_address = 0;
		}

	    if( empty($data) )
	    {
	    	echo json_encode(array('code'=>1,'index_hide_headdetail_address' => $index_hide_headdetail_address));
	    	die();
	    }else{
	    	echo json_encode(array('code'=>0, 'list' => $data,'index_hide_headdetail_address' => $index_hide_headdetail_address));
	    	die();
	    }
	}

	public function addhistory_community()
	{
		$gpc = I('request.');

		$token =  $gpc['token'];
		$head_id = $gpc['community_id'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
	    $member_id = $weprogram_token['member_id'];

		D('Seller/Community')->in_community_history($member_id,$head_id);

		echo json_encode( array('code' => 0) );
		die();
	}

	/**
		获取已经过期的往期团购
	**/

	public function load_over_gps_goodslist()
	{
		$gpc = I('request.');

		$head_id = $gpc['head_id'];
		$pageNum = $gpc['pageNum'];
		$per_page = 10;
		$gid = $gpc['gid'];
		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";
		if($head_id == 'undefined') $head_id = '';

		$is_only_express = $gpc['is_only_express'];
		$is_open_only_express = 0;
		if($is_only_express==1) {
			$is_open_only_express = D('Home/Front')->get_config_by_name('is_open_only_express');
		}

		if($gid == 'undefined' || $gid =='' || $gid =='null'  || $gid ==0)
		{
			$gid = 0;
		}

		if( !empty($gid) && $gid > 0)
		{
			$gids = D('Home/GoodsCategory')->get_index_goods_category($gid,'normal','','',1);
			$gidArr = array();
			$gidArr[] = $gid;

			foreach ($gids as $key => $val) {
				$gidArr[] = $val['id'];
			}

			$gid = implode(',', $gidArr);
		}

		$token =  $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2) );
			//die();
		}
	    $member_id = $weprogram_token['member_id'];

	    $now_time = time();

	    $where = " g.grounding =1  and  g.type ='normal'   ";

		$is_index_show = isset($gpc['is_index_show']) ? $gpc['is_index_show'] : 0;
		if($is_index_show==1) {
			$where .= " and g.is_index_show = 1";
		}

		if($is_open_only_express==1 && $is_only_express==1) {
			$where .= " and gc.is_only_express =1 ";
		}

		$where .= " and gc.end_time <= {$now_time}  and gc.is_new_buy=0 ";


		$community_goods = D('Home/Pingoods')->get_new_community_index_goods($head_id, $gid, 'g.*,gc.begin_time,gc.end_time,gc.big_img,gc.labelname,gc.video,gc.pick_up_type,gc.pick_up_modify ', $where,$offset,$per_page);

		if( !empty($community_goods) )
		{
			$list = array();
			$today_time = time();
			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$goodsname = htmlspecialchars_decode($val['goodsname']);
				$tmp_data['spuName'] = $goodsname;
				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['actEnd'] = time()>$val['end_time'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];

				$tmp_data['begin_time'] = date('Y-m-d', $val['begin_time']);

				if($val['pick_up_type'] == 0)
				{
					$val['pick_up_modify'] = date('Y-m-d', $today_time);
				}else if( $val['pick_up_type'] == 1 ){
					$val['pick_up_modify'] = date('Y-m-d', $today_time+86400);
				}else if( $val['pick_up_type'] == 2 )
				{
					$val['pick_up_modify'] = date('Y-m-d', $today_time+86400*2);
				}

				$tmp_data['pick_up_modify'] = $val['pick_up_modify'];


				$productprice = $val['productprice'];
				$tmp_data['marketPrice'] = explode('.', $productprice);

				if( !empty($val['big_img']) )
				{
					$tmp_data['bigImg'] = tomedia($val['big_img']);
				}


				$good_image = D('Home/Pingoods')->get_goods_images($val['id']);
				if( !empty($good_image) )
				{
					$tmp_data['skuImage'] = tomedia($good_image['image']);
				}
				$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);
				$price = $price_arr['price'];

				$tmp_data['actPrice'] = explode('.', $price);
				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'], $member_id);


				// 商品角标
				$label_id = unserialize($val['labelname']);
				if($label_id){
					$label_info = D('Home/Pingoods')->get_goods_tags($label_id);
					if($label_info){
						if($label_info['type'] == 1){
							$label_info['tagcontent'] = tomedia($label_info['tagcontent']);
						} else {
							$label_info['len'] = mb_strlen($label_info['tagcontent'], 'utf-8');
						}
					}
					$tmp_data['label_info'] = $label_info;
				}
				$tmp_data['is_video'] = empty($val['video']) ? false : true;

				$list[] = $tmp_data;
			}

			$is_member_level_buy = 0;
			$is_vip_card_member = 0;
			$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
			$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;

			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');
			if(empty($full_reducemoney) || $full_reducemoney <= 0)
			{
				$is_open_fullreduction = 0;
			}

			echo json_encode(array('code' => 0, 'list' => $list , 'cur_time' => time(), 'is_vip_card_member' => $is_vip_card_member,'is_member_level_buy' => $is_member_level_buy, 'full_reducemoney' => $full_reducemoney, 'full_money' => $full_money,'is_open_vipcard_buy' => $is_open_vipcard_buy, 'is_open_fullreduction' => $is_open_fullreduction ));
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}

	public function load_gps_goodslist()
	{
		$gpc = I('request.');

		$token = $gpc['token'];
		$head_id = $gpc['head_id'];

		$is_only_express = $gpc['is_only_express'];
		$is_open_only_express = 0;
		if($is_only_express==1) {
			$is_open_only_express = D('Home/Front')->get_config_by_name('is_open_only_express');
		}

		if($head_id == 'undefined') $head_id = '';
		$pageNum = $gpc['pageNum'];
		$gid = $gpc['gid'];
		$keyword = $gpc['keyword'];

		$is_random = isset($gpc['is_random']) ? $gpc['is_random'] : 0;
		$is_video = isset($gpc['is_video']) ? $gpc['is_video'] : 0;
		$per_page = isset($gpc['per_page']) ? $gpc['per_page'] : 10;
		$cate_info = '';

		if($gid == 'undefined' || $gid =='' || $gid =='null'  || $gid ==0)
		{
			$gid = 0;
		} else {
			$cate_info = M('eaterplanet_ecommerce_goods_category')->field('banner,name')->where( array('id' => $gid ) )->find();
			if(!empty($cate_info['banner'])) $cate_info['banner'] = tomedia($cate_info['banner']);
		}

		if( !empty($gid) && $gid > 0)
		{
			$gids = D('Home/GoodsCategory')->get_index_goods_category($gid,'normal','','',1);
			$gidArr = array();
			$gidArr[] = $gid;

			foreach ($gids as $key => $val) {
				$gidArr[] = $val['id'];
			}

			$gid = implode(',', $gidArr);
		}

		//$per_page = 10;
		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$is_member_level_buy = 0;
		$is_vip_card_member = 0;
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2) );
			//die();
		}else{
			$member_id = $weprogram_token['member_id'];
			$is_vip_card_member = 0;

			//member_id
			if( $member_id > 0 )
			{
				$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

				if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
				{

					$now_time = time();

					if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
					{
						$is_vip_card_member = 1;//还是会员
					}else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){
						$is_vip_card_member = 2;//已过期
					}
				}

				if($is_vip_card_member != 1 && $member_info['level_id'] >0 )
				{
					$is_member_level_buy = 1;
				}
			}

		}

	    $member_id = $weprogram_token['member_id'];

		//整点秒杀begin
		$is_seckill = isset($gpc['is_seckill']) ? 1:0;
		$seckill_time = isset($gpc['seckill_time']) ? intval($gpc['seckill_time']):0;
		//整点秒杀end

	    $now_time = time();
		if($is_seckill ==1)
		{
			$where = " g.grounding =1 and g.is_seckill =1 and  g.type ='normal'   ";
		}else
		{
			$where = " g.grounding =1 and g.is_seckill =0 and  g.type ='normal'   ";
		}

		//head_id
		if( !empty($keyword) )
		{
			$where .= " and g.goodsname like '%{$keyword}%'  ";
		}

		//$where .= " and g.is_index_show = 1 and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";

		if($is_seckill ==1)
		{
			$bg_time = strtotime(  date('Y-m-d').' '.$seckill_time.':00:00' );

			$ed_time = $bg_time + 3600;

			if($gid == 0 && $keyword == ''){
				$where .= "  and gc.begin_time >={$bg_time} and gc.begin_time <{$ed_time}  ";
			} else {
				$where .= " and gc.begin_time >={$bg_time} and gc.begin_time <{$ed_time}  ";
			}

		} else {
			if($gid == 0 && $keyword == ''){
				$where .= " and g.is_index_show = 1 and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
			} else {
				$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
			}
		}

		if($is_seckill ==1)
		{

		}else{
			$where .= " and gc.is_new_buy=0 and gc.is_spike_buy = 0 ";
		}

		if( $is_video == 1 )
		{
			$where .= " and gc.video !=''  ";
		}

		if($is_open_only_express==1 && $is_only_express==1) {
			$where .= " and gc.is_only_express =1 ";
		}


		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');
		if( empty($index_sort_method) )
		{
			$order_sort = 'g.istop DESC, g.settoptime DESC,g.index_sort desc,g.id desc ';
		}

		if( $index_sort_method == 1 )
		{
			$order_sort = 'g.index_sort desc,g.id desc ';
		}

		if($is_random == 1)
		{
			$community_goods = D('Home/Pingoods')->get_new_community_index_goods($head_id,$gid,'g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video,gc.pick_up_type,gc.pick_up_modify,gc.oneday_limit_count, gc.total_limit_count, gc.one_limit_count,gc.goods_start_count ', $where,$offset,$per_page,$order_sort,' rand() ');
		}else{
			$community_goods = D('Home/Pingoods')->get_new_community_index_goods($head_id,$gid,'g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video,gc.pick_up_type,gc.pick_up_modify,gc.oneday_limit_count, gc.total_limit_count, gc.one_limit_count,gc.goods_start_count ', $where,$offset,$per_page,$order_sort);
		}

		if( !empty($community_goods) )
		{
			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');

			$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');

			$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 ? 1:0;


			if(empty($full_reducemoney) || $full_reducemoney <= 0)
			{
				$is_open_fullreduction = 0;
			}

			$cart= D('Home/Car');

			$list = array();
			$copy_text_arr = array();
			$today_time = strtotime( date('Y-m-d').' 00:00:00' );


			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$goodsname = htmlspecialchars_decode($val['goodsname']);
				$tmp_data['spuName'] = $goodsname;
				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['is_take_vipcard'] = $val['is_take_vipcard'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];

				$tmp_data['oneday_limit_count'] = $val['oneday_limit_count'];
				$tmp_data['total_limit_count'] = $val['total_limit_count'];
				$tmp_data['one_limit_count'] = $val['one_limit_count'];
				$tmp_data['goods_start_count'] = $val['goods_start_count'];


				$tmp_data['begin_time'] = date('Y-m-d', $val['begin_time']);

				if($val['pick_up_type'] == 0)
				{
					$val['pick_up_modify'] = date('Y-m-d', $today_time);
				}else if( $val['pick_up_type'] == 1 ){
					$val['pick_up_modify'] = date('Y-m-d', $today_time+86400);
				}else if( $val['pick_up_type'] == 2 )
				{
					$val['pick_up_modify'] = date('Y-m-d', $today_time+86400*2);
				}

				$tmp_data['pick_up_modify'] = $val['pick_up_modify'];


				$productprice = $val['productprice'];
				$tmp_data['marketPrice'] = explode('.', $productprice);

				if( !empty($val['big_img']) )
				{
					$tmp_data['bigImg'] = tomedia($val['big_img']);
				}


				$good_image = D('Home/Pingoods')->get_goods_images($val['id']);
				if( !empty($good_image) )
				{
					$tmp_data['skuImage'] = tomedia($good_image['image']);
				}
				$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);
				$price = $price_arr['price'];

				if( $pageNum == 1 )
				{
					$copy_text_arr[] = array('goods_name' => $val['goodsname'], 'price' => $price);
				}

				$tmp_data['actPrice'] = explode('.', $price);
				$tmp_data['card_price'] = $price_arr['card_price'];

				$tmp_data['levelprice'] = $price_arr['levelprice']; // 客户等级价格
				$tmp_data['is_mb_level_buy'] = $price_arr['is_mb_level_buy']; //是否 客户等级 可享受

				//$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'], $member_id);
				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options_carquantity($val['id'], $member_id, $head_id ,$token);
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{

					$car_count = $cart->get_wecart_goods($val['id'],"",$head_id ,$token);

					if( empty($car_count)  )
					{
						$tmp_data['car_count'] = 0;
					}else{
						$tmp_data['car_count'] = $car_count;
					}

				}

				if($is_open_fullreduction == 0)
				{
					$tmp_data['is_take_fullreduction'] = 0;
				}else if($is_open_fullreduction == 1){
					$tmp_data['is_take_fullreduction'] = $val['is_take_fullreduction'];
				}


				// 商品角标
				$label_id = unserialize($val['labelname']);
				if($label_id){
					$label_info = D('Home/Pingoods')->get_goods_tags($label_id);
					if($label_info){
						if($label_info['type'] == 1){
							$label_info['tagcontent'] = tomedia($label_info['tagcontent']);
						} else {
							$label_info['len'] = mb_strlen($label_info['tagcontent'], 'utf-8');
						}
					}
					$tmp_data['label_info'] = $label_info;
				}

				$tmp_data['is_video'] = empty($val['video']) ? false : true;

				$list[] = $tmp_data;
			}


			$is_show_list_timer = D('Home/Front')->get_config_by_name('is_show_list_timer');
			$index_list_theme_type = D('Home/Front')->get_config_by_name('index_list_theme_type');
			// 3*3是也关闭
			if($index_list_theme_type>=2) $is_show_list_timer==0;

			$is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');


			echo json_encode(array('code' => 0,'now_time' => time(),  'list' => $list ,'is_show_cate_tabbar' => $is_show_cate_tabbar,'is_vip_card_member' => $is_vip_card_member,'is_member_level_buy' => $is_member_level_buy , 'copy_text_arr' => $copy_text_arr, 'cur_time' => time() ,'full_reducemoney' => $full_reducemoney,'full_money' => $full_money,'is_open_vipcard_buy' => $is_open_vipcard_buy,'is_open_fullreduction' => $is_open_fullreduction,'is_show_list_timer'=>$is_show_list_timer , 'cate_info' => $cate_info, 'is_show_cate_tabbar'=>$is_show_cate_tabbar ));
			die();

		}else{
			$is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');

			echo json_encode( array('code' => 1 , 'cate_info' => $cate_info , 'is_show_cate_tabbar'=>$is_show_cate_tabbar ) );
			die();
		}
	}

	public function get_index_category()
	{
		$gpc = I('request.');

		$gid = $gpc['gid'];

		$hot_list = M('eaterplanet_ecommerce_goods_category')->where( array('is_hot' => 1) )->order('sort_order desc')->select();

		$need_data = array();
		foreach($hot_list as $key => $cate)
		{
			$need_data[$key]['id'] = $cate['id'];
			$need_data[$key]['name'] = $cate['name'];
			$need_data[$key]['sort_order'] = $cate['sort_order'];
		}
		$result = array('code' =>0,'data' => $need_data);
		echo json_encode($result);
		die();
	}

	/**
	 * 获取首页公告
	 */
	public function get_index_notice()
	{

		$list = M('eaterplanet_ecommerce_notice')->where( array('enabled' => 1) )->order('displayorder desc, addtime desc')->limit(1)->select();

		$result = array('code' =>0,'data' => $list);
		echo json_encode($result);
		die();
	}

	/**
	 * 即将抢购商品
	 */
	public function load_comming_goodslist()
	{
		$gpc = I('request.');

		$head_id = $gpc['head_id'];
		$pageNum = $gpc['pageNum'];
		$per_page = 10;
		$gid = $gpc['gid'];
		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";
		if($head_id == 'undefined') $head_id = '';

		$is_only_express = $gpc['is_only_express'];
		$is_open_only_express = 0;
		if($is_only_express==1) {
			$is_open_only_express = D('Home/Front')->get_config_by_name('is_open_only_express');
		}

		if($gid == 'undefined' || $gid =='' || $gid =='null'  || $gid ==0)
		{
			$gid = 0;
		}

		if( !empty($gid) && $gid > 0)
		{
			$gids = D('Home/GoodsCategory')->get_index_goods_category($gid,'normal','','',1);
			$gidArr = array();
			$gidArr[] = $gid;

			foreach ($gids as $key => $val) {
				$gidArr[] = $val['id'];
			}

			$gid = implode(',', $gidArr);
		}

		$token =  $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2) );
			//die();
		}
	    $member_id = $weprogram_token['member_id'];

	    $now_time = time();

	    $where = " g.grounding =1  and  g.type ='normal'   ";

		$is_index_show = isset($gpc['is_index_show']) ? $gpc['is_index_show'] : 0;
		if($is_index_show==1) {
			$where .= " and g.is_index_show = 1";
		}

		if($is_open_only_express==1 && $is_only_express==1) {
			$where .= " and gc.is_only_express =1 ";
		}

		$where .= "and gc.begin_time > {$now_time} ";


		$community_goods = D('Home/Pingoods')->get_new_community_index_goods($head_id, $gid, 'g.*,gc.begin_time,gc.end_time,gc.big_img,gc.labelname,gc.video,gc.pick_up_type,gc.pick_up_modify,gc.is_take_fullreduction ', $where,$offset,$per_page);

		if( !empty($community_goods) )
		{
			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');

			if(empty($full_reducemoney) || $full_reducemoney <= 0)
			{
				$is_open_fullreduction = 0;
			}

			$list = array();
			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$tmp_data['spuName'] = $val['goodsname'];
				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];

				$productprice = $val['productprice'];
				$tmp_data['marketPrice'] = explode('.', $productprice);

				if( !empty($val['big_img']) )
				{
					$tmp_data['bigImg'] = tomedia($val['big_img']);
				}

				$good_image = D('Home/Pingoods')->get_goods_images($val['id']);
				if( !empty($good_image) )
				{
					$tmp_data['skuImage'] = tomedia($good_image['image']);
				}
				$price_arr = D('Home/Pingoods')->get_goods_price($val['id'],$member_id);
				$price = $price_arr['price'];

				$tmp_data['actPrice'] = explode('.', $price);

				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'],$member_id);

				if($is_open_fullreduction == 0)
				{
					$tmp_data['is_take_fullreduction'] = 0;
				}else if($is_open_fullreduction == 1){
					$tmp_data['is_take_fullreduction'] = $val['is_take_fullreduction'];
				}

				$list[] = $tmp_data;
			}
			echo json_encode(array('code' => 0, 'list' => $list , 'cur_time' => time() ,'full_reducemoney' => $full_reducemoney,'full_money' => $full_money,'is_open_fullreduction' => $is_open_fullreduction ));
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}

	public function get_community_position()
	{
		$_GPC = I('request.');

		$communityId = $_GPC['communityId'];

		$postion = M('eaterplanet_community_head')->field('lat,lon')->where( array('id' => $communityId ) )->find();

		echo json_encode(array('code' => 0, 'postion' => $postion));
		die();
	}

	public function load_spikebuy_goodslist()
	{
		$_GPC = I('request.');

		$head_id = $_GPC['head_id'];
		$pageNum = $_GPC['pageNum'];

		$per_page = 10000;
		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2) );
			//die();
		}
		$member_id = $weprogram_token['member_id'];

	    $now_time = time();

	    $where = " g.grounding =1    ";
		//head_id

		if( !empty($head_id) && $head_id >0 )
		{
			$params = array();
			$params['uniacid'] = $_W['uniacid'];
			$params['head_id'] = $head_id;

			$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."eaterplanet_community_head_goods as pg,"
	                        .C('DB_PREFIX')."eaterplanet_ecommerce_good_common as g where pg.goods_id = g.goods_id and g.is_spike_buy = 1 and pg.head_id = {$head_id} order by pg.id desc ";
            $goods_ids_arr = M()->query($sql_goods_ids);

			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}


			$goods_ids_nolimit_ids = "select pg.id from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as pg,"
	                        .C('DB_PREFIX')."eaterplanet_ecommerce_good_common as g where pg.id = g.goods_id and g.is_spike_buy = 1 and pg.is_all_sale=1  order by pg.id desc ";
            $goods_ids_nolimit_arr = M()->query($goods_ids_nolimit_ids);

			if( !empty($goods_ids_nolimit_arr) )
			{
				foreach($goods_ids_nolimit_arr as $val){
					$ids_arr[] = $val['id'];
				}
			}

			$ids_str = implode(',',$ids_arr);

			if( !empty($ids_str) )
			{
				$where .= "  and g.id in ({$ids_str})";
			} else{
				$where .= " and 0 ";
			}
		}else{
			//echo json_encode( array('code' => 1) );
			//die();
			$where .= " and gc.is_spike_buy = 1";
		}

		$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";

		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');

		if( empty($index_sort_method) )
		{
			$index_sort_method = 0;
		}

		$order_sort = 'g.istop DESC, g.settoptime DESC,g.index_sort desc,g.id desc ';

		if( $index_sort_method == 1 )
		{
			$order_sort = 'g.index_sort desc,g.id desc ';
		}

		$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.oneday_limit_count, gc.total_limit_count, gc.one_limit_count,gc.goods_start_count ', $where,$offset,$per_page,$order_sort);


		if( !empty($community_goods) )
		{
			$is_show_spike_buy_time = D('Home/Front')->get_config_by_name('is_show_spike_buy_time');
			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');

			if(empty($full_reducemoney) || $full_reducemoney <= 0)
			{
				$is_open_fullreduction = 0;
			}

			$cart= D('Home/Car');

			$list = array();
			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$goodsname = htmlspecialchars_decode($val['goodsname']);
				$tmp_data['spuName'] = $goodsname;
				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];
				$tmp_data['oneday_limit_count'] = $val['oneday_limit_count'];
				$tmp_data['total_limit_count'] = $val['total_limit_count'];
				$tmp_data['one_limit_count'] = $val['one_limit_count'];
				$tmp_data['goods_start_count'] = $val['goods_start_count'];
				$productprice = $val['productprice'];
				$tmp_data['marketPrice'] = explode('.', $productprice);

				if( !empty($val['big_img']) )
				{
					$tmp_data['bigImg'] = tomedia($val['big_img']);
				}

				$good_image = D('Home/Pingoods')->get_goods_images($val['id']);
				if( !empty($good_image) )
				{
					$tmp_data['skuImage'] = tomedia($good_image['image']);
				}
				$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);
				$price = $price_arr['price'];

				$tmp_data['actPrice'] = explode('.', $price);

				//$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'], $member_id);
				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options_carquantity($val['id'], $member_id, $head_id ,$token);
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{

					$car_count = $cart->get_wecart_goods($val['id'],"",$head_id ,$token);

					if( empty($car_count)  )
					{
						$tmp_data['car_count'] = 0;
					}else{
						$tmp_data['car_count'] = $car_count;
					}
				}

				$list[] = $tmp_data;
			}

			echo json_encode(array('code' => 0, 'list' => $list, 'is_show_spike_buy_time'=>$is_show_spike_buy_time ));
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}


	public function load_new_buy_goodslist()
	{
		$_GPC = I('request.');

		$head_id = $_GPC['head_id'];
		$pageNum = $_GPC['pageNum'];

		$per_page = 10;
		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2) );
			//die();
		}
		$member_id = $weprogram_token['member_id'];

	    $now_time = time();

	    $where = " g.grounding =1    ";
		//head_id

		if( !empty($head_id) && $head_id >0 )
		{
			$params = array();
			$params['uniacid'] = $_W['uniacid'];
			$params['head_id'] = $head_id;

			$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."eaterplanet_community_head_goods as pg,"
	                        .C('DB_PREFIX')."eaterplanet_ecommerce_good_common as g where pg.goods_id = g.goods_id and g.is_new_buy = 1 and pg.head_id = {$head_id} order by pg.id desc ";
            $goods_ids_arr = M()->query($sql_goods_ids);

			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}


			$goods_ids_nolimit_ids = "select pg.id from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as pg,"
	                        .C('DB_PREFIX')."eaterplanet_ecommerce_good_common as g where pg.id = g.goods_id and g.is_new_buy = 1 and pg.is_all_sale=1  order by pg.id desc ";
            $goods_ids_nolimit_arr = M()->query($goods_ids_nolimit_ids);

			if( !empty($goods_ids_nolimit_arr) )
			{
				foreach($goods_ids_nolimit_arr as $val){
					$ids_arr[] = $val['id'];
				}
			}

			$ids_str = implode(',',$ids_arr);

			if( !empty($ids_str) )
			{
				$where .= "  and g.id in ({$ids_str})";
			} else{
				$where .= " and 0 ";
			}
		}else{
			//echo json_encode( array('code' => 1) );
			//die();
			$where .= " and gc.is_new_buy = 1";
		}

		$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";

		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');

		if( empty($index_sort_method) )
		{
			$index_sort_method = 0;
		}

		$order_sort = 'g.istop DESC, g.settoptime DESC,g.index_sort desc,g.id desc ';

		if( $index_sort_method == 1 )
		{
			$order_sort = 'g.index_sort desc,g.id desc ';
		}

		$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.oneday_limit_count, gc.total_limit_count, gc.one_limit_count,gc.goods_start_count ', $where,$offset,$per_page,$order_sort);


		if( !empty($community_goods) )
		{
			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');

			if(empty($full_reducemoney) || $full_reducemoney <= 0)
			{
				$is_open_fullreduction = 0;
			}

			$cart= D('Home/Car');

			$list = array();
			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$goodsname = htmlspecialchars_decode($val['goodsname']);
				$tmp_data['spuName'] = $goodsname;
				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];
				$tmp_data['oneday_limit_count'] = $val['oneday_limit_count'];
				$tmp_data['total_limit_count'] = $val['total_limit_count'];
				$tmp_data['one_limit_count'] = $val['one_limit_count'];
				$tmp_data['goods_start_count'] = $val['goods_start_count'];
				$productprice = $val['productprice'];
				$tmp_data['marketPrice'] = explode('.', $productprice);

				if( !empty($val['big_img']) )
				{
					$tmp_data['bigImg'] = tomedia($val['big_img']);
				}

				$good_image = D('Home/Pingoods')->get_goods_images($val['id']);
				if( !empty($good_image) )
				{
					$tmp_data['skuImage'] = tomedia($good_image['image']);
				}
				$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);
				$price = $price_arr['price'];

				$tmp_data['actPrice'] = explode('.', $price);

				//$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'], $member_id);
				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options_carquantity($val['id'], $member_id, $head_id ,$token);
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{

					$car_count = $cart->get_wecart_goods($val['id'],"",$head_id ,$token);

					if( empty($car_count)  )
					{
						$tmp_data['car_count'] = 0;
					}else{
						$tmp_data['car_count'] = $car_count;
					}
				}

				$list[] = $tmp_data;
			}

			echo json_encode(array('code' => 0, 'list' => $list ));
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}


	/**
	 * 条件搜索商品
	 */
	public function load_condition_goodslist()
	{
		$_GPC = I('request.');

		$head_id = $_GPC['head_id'];

		if($head_id == 'undefined')
			$head_id = '';

		$pageNum = $_GPC['pageNum'];
		$per_page = 10;
		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";
		$gid = $_GPC['gid']; //分类id
		$keyword = $_GPC['keyword'];
		$good_ids = $_GPC['good_ids'];
		$type = $_GPC['type']; //空：关键词搜索，1：指定分类，2：指定多个商品

		$token =  $_GPC['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2) );
			//die();
		}

	    $member_id = $weprogram_token['member_id'];

	    $now_time = time();


		$where = " g.grounding =1 and g.is_seckill =0 and  g.type ='normal'   ";

		if( !empty($head_id) && $head_id >0 )
		{
			$params = array();
			$params['uniacid'] = $_W['uniacid'];
			$params['head_id'] = $head_id;
			$goods_ids_arr = array();

			if(!empty($keyword) && $type==0) {
				$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."eaterplanet_community_head_goods as pg,"
	                        .C('DB_PREFIX')."eaterplanet_ecommerce_goods as g
	        	           where pg.goods_id = g.id and g.goodsname like '%{$keyword}%' and pg.head_id = {$head_id}  order by pg.id desc ";

				$goods_ids_arr = M()->query($sql_goods_ids);

			}

			if($type==1){
				$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."eaterplanet_community_head_goods as pg,"
                        .C('DB_PREFIX')."eaterplanet_ecommerce_goods_to_category as g where pg.goods_id = g.goods_id  and g.cate_id = {$gid} and pg.head_id = {$head_id} order by pg.id desc ";

				$goods_ids_arr = M()->query($sql_goods_ids);
			}

			if($type == 2){
				$goods_ids_arr = M()->query('SELECT goods_id FROM ' . C('DB_PREFIX'). "eaterplanet_community_head_goods
					WHERE  head_id={$head_id} order by id desc ");
			}

			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}

			if(!empty($keyword) && $type==0) {
				$goods_ids_nolimit_arr = M()->query('SELECT id FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_goods
					WHERE  is_all_sale=1 and goodsname like '%{$keyword}%' " );
			}

			if($type==1){
				$goods_ids_nolimit_sql = "select pg.id from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as pg, "
                        .C('DB_PREFIX')."eaterplanet_ecommerce_goods_to_category as g where pg.id = g.goods_id and g.cate_id = {$gid} and pg.is_all_sale=1 ";

				$goods_ids_nolimit_arr = M()->query($goods_ids_nolimit_sql);
			}

			if($type==2){
				$goods_ids_nolimit_arr = M()->query('SELECT id FROM ' . C('DB_PREFIX') . "eaterplanet_ecommerce_goods
				WHERE  is_all_sale=1  ");
			}

			if( !empty($goods_ids_nolimit_arr) )
			{
				foreach($goods_ids_nolimit_arr as $val){
					$ids_arr[] = $val['id'];
				}
			}

			if($type==2){
				$good_ids_arr = explode(',',$good_ids);
				$new_ids_arr = array();
				if(count($good_ids_arr)>0){
					foreach ($good_ids_arr as $val) {
						if(in_array($val, $ids_arr)){
							$new_ids_arr[] = $val;
						}
					}
				}
				$ids_arr = $new_ids_arr;
			}

			$ids_str = implode(',',$ids_arr);
			if( !empty($ids_str) )
			{
				$where .= "  and g.id in ({$ids_str})";
			} else{
				$where .= " and 0 ";
			}
		}else{
			//echo json_encode( array('code' => 1) );
			//die();
		}

		if(empty($head_id) && $type == 0 && !empty($keyword)) {
			$where .= " and g.goodsname like '%{$keyword}%'";
		}


		if($type==1) {
			$where .= " and gc.is_new_buy=0 and gc.is_spike_buy = 0 ";
		}

		//$where .= " and gc.begin_time <= {$now_time} and gc.end_time > {$now_time} and g.total > 0 ";

		$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.oneday_limit_count, gc.total_limit_count, gc.one_limit_count,gc.goods_start_count ', $where,$offset,$per_page);

		if( !empty($community_goods) )
		{
			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');

			if(empty($full_reducemoney) || $full_reducemoney <= 0)
			{
				$is_open_fullreduction = 0;
			}

			$list = array();
			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$goodsname = htmlspecialchars_decode($val['goodsname']);
				$tmp_data['spuName'] = $goodsname;
				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];

				$tmp_data['oneday_limit_count'] = $val['oneday_limit_count'];
				$tmp_data['total_limit_count'] = $val['total_limit_count'];
				$tmp_data['one_limit_count'] = $val['one_limit_count'];
				$tmp_data['goods_start_count'] = $val['goods_start_count'];
				$productprice = $val['productprice'];
				$tmp_data['marketPrice'] = explode('.', $productprice);

				if( !empty($val['big_img']) )
				{
					$tmp_data['bigImg'] = tomedia($val['big_img']);
				}

				$good_image = D('Home/Pingoods')->get_goods_images($val['id']);
				if( !empty($good_image) )
				{
					$tmp_data['skuImage'] = tomedia($good_image['image']);
				}
				$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);
				$price = $price_arr['price'];

				$tmp_data['actPrice'] = explode('.', $price);
				/**
				$tmp_data['skuList'] = array(
					array('spec' => 'xl','canBuyNum' => 100,'spuName' => 1, 'actPrice' => array(1,2), 'marketPrice' => array(2,3),'skuImage' => tomedia($good_image['image'])),
					array('spec' => 'x2','canBuyNum' => 200, 'spuName' => 2, 'actPrice' => array(1,2), 'marketPrice' => array(2,3),'skuImage' => tomedia($good_image['image']))
				);
				**/
				//$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'], $member_id);

				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options_carquantity($val['id'], $member_id, $head_id ,$token);
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{
					if(!empty($head_id) && $head_id > 0){
						$car_count = D('Home/Car')->get_wecart_goods($val['id'],"",$head_id ,$token);
						if( empty($car_count)  )
						{
							$tmp_data['car_count'] = 0;
						}else{
							$tmp_data['car_count'] = $car_count;
						}
					}
				}


				if($is_open_fullreduction == 0)
				{
					$tmp_data['is_take_fullreduction'] = 0;
				}else if($is_open_fullreduction == 1){
					$tmp_data['is_take_fullreduction'] = $val['is_take_fullreduction'];
				}

				$list[] = $tmp_data;
			}
			echo json_encode(array('code' => 0, 'list' => $list , 'cur_time' => time() ,'full_reducemoney' => $full_reducemoney,'full_money' => $full_money,'is_open_fullreduction' => $is_open_fullreduction ));
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}


	public function get_newauth_bg()
	{

		$newauth_bg_image = D('Home/Front')->get_config_by_name('newauth_bg_image');
		if( !empty($newauth_bg_image) )
		{
			$newauth_bg_image = tomedia($newauth_bg_image);
		}
		$newauth_cancel_image = D('Home/Front')->get_config_by_name('newauth_cancel_image');
		if( !empty($newauth_cancel_image) )
		{
			$newauth_cancel_image = tomedia($newauth_cancel_image);
		}
		$newauth_confirm_image = D('Home/Front')->get_config_by_name('newauth_confirm_image');
		if( !empty($newauth_confirm_image) )
		{
			$newauth_confirm_image = tomedia($newauth_confirm_image);
		}
		echo json_encode(
			array(
				'code'=>0,
				'data' => array(
					'newauth_bg_image'=>$newauth_bg_image,
					'newauth_confirm_image'=>$newauth_confirm_image,
					'newauth_cancel_image'=>$newauth_cancel_image
				)
			)
		);
	}

	/**
		进入小程序初始加载方法，
		以后有类似需要参数，都在这里初始化
	**/
	public function get_firstload_msg()
	{
		$_GPC = I('request.');
		$token =  isset($_GPC['token']) ? $_GPC['token'] : '';
		$new_head_id = 0;
		$default_head_info = array();

        //是否强制手机
        $isparse_formdata = 0;


		if( !empty($token) )
		{
			$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

			$community_history = M('eaterplanet_community_history')->field('head_id')->where( array('member_id' => $weprogram_token['member_id'] ) )->order('addtime desc')->find();


			if( !empty($community_history) )
			{
				$cur_community_info = M('eaterplanet_community_head')->field('id')->where( array('id' => $community_history['head_id'] ) )->find();

				if( !empty($cur_community_info) )
				{
					$new_head_id = $cur_community_info['id'];
					$default_head_info = D('Home/Front')->get_community_byid($new_head_id);
				}
			}



            //是否强制手机
            $is_get_formdata = D('Home/Front')->get_config_by_name('is_get_formdata');
            if( isset($is_get_formdata) && $is_get_formdata == 1 )
            {
                $now_member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $weprogram_token['member_id'] ))->find();
                if( $now_member_info['is_apply_state'] != 1 && $now_member_info['is_write_form'] != 1 )
                {
                    $isparse_formdata = 1;
                }
            }

        }

		$skin = D('Home/Front')->get_config_by_name('skin');

		$common_header_backgroundimage = D('Home/Front')->get_config_by_name('common_header_backgroundimage');
		if( !empty($common_header_backgroundimage) )
		{
			$common_header_backgroundimage = tomedia($common_header_backgroundimage);
		}

		$goods_sale_unit = D('Home/goods')->get_sale_unit();


        echo json_encode( array('code' => 0,'new_head_id' => $new_head_id,'isparse_formdata' => $isparse_formdata , 'default_head_info' => $default_head_info, 'skin'=>$skin, 'common_header_backgroundimage'=>$common_header_backgroundimage, 'goods_sale_unit'=>$goods_sale_unit ) );
        die();
	}

	/**
	 * 加载分类详情页
	 * @return [type] [description]
	 */
	public function load_cate_goodslist()
	{
		$_GPC = I('request.');

		$head_id = $_GPC['head_id'];
		if($head_id == 'undefined')
			$head_id = '';
		$pid = $_GPC['gid'];


		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

	    $member_id = $weprogram_token['member_id'];


		// vip身份
		$is_vip_card_member = 0;
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;

		$member_id = $weprogram_token['member_id'];
		$is_vip_card_member = 0;
		if( $member_id > 0 )
		{

			$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

			if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
			{
				$now_time = time();
				if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
				{
					$is_vip_card_member = 1;//还是客户
				}else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){
					$is_vip_card_member = 2;//已过期
				}
			}
		}

		if($pid == 'undefined' || $pid =='')
		{
			echo json_encode(
				array(
					'code' => 1,
					'data'=> array(),
					'msg' => '分类id错误'
				)
			);
			die();
		}

		$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
		$full_money = D('Home/Front')->get_config_by_name('full_money');
		$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 ? 1:0;
		$is_show_vipcard_price = D('Home/Front')->get_config_by_name('is_show_vipcard_price');
		if( $is_open_vipcard_buy == 1 )
		{
			if( !empty($is_show_vipcard_price) && $is_show_vipcard_price == 1 ) $is_open_vipcard_buy = 0;
		}

		if(empty($full_reducemoney) || $full_reducemoney <= 0) $is_open_fullreduction = 0;

		$cateList = $cateArr = array();

		$parent_cate = M('eaterplanet_ecommerce_goods_category')->field('id,banner,name')->where( array('cate_type' =>'normal','id' => $pid ) )->find();

	    if($parent_cate){

			$cate_info = M('eaterplanet_ecommerce_goods_category')->field('id,banner,name,logo')->where( array('cate_type' =>'normal','pid' => $parent_cate['id'] ) )->order('sort_order desc, id desc')->select();

			if($cate_info){
	    		$cateArr = array_merge(array($parent_cate), $cate_info);
	    	}else {
	    		$cateArr[] = $parent_cate;
	    	}

			foreach ($cateArr as $key => $val) {
				$gid = $val['id'];
				$cate_info = array();
				$cate_info['name'] = $val['name'];
				$cate_info['banner'] = $val['banner'] && !empty($val['banner']) ? tomedia($val['banner']) : '';
				$cate_info['logo'] = $val['logo'] && !empty($val['logo']) ? tomedia($val['logo']) : '';

				$now_time = time();
			    $where = " g.grounding =1 ";
				if( !empty($head_id) && $head_id >0 )
				{

					$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."eaterplanet_community_head_goods as pg,"
		                    .C('DB_PREFIX')."eaterplanet_ecommerce_goods_to_category as g
		    	           where  pg.goods_id = g.goods_id and g.cate_id={$gid} and pg.head_id = {$head_id}
						    order by pg.id desc ";
					$goods_ids_arr = M()->query($sql_goods_ids);


					$ids_arr = array();
					foreach($goods_ids_arr as $val){
						$ids_arr[] = $val['goods_id'];
					}

					$goods_ids_nolimit_sql = "select pg.id from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as pg,"
		                    .C('DB_PREFIX')."eaterplanet_ecommerce_goods_to_category as g where pg.id = g.goods_id and g.cate_id={$gid}
							and pg.is_all_sale=1 ";

					$goods_ids_nolimit_arr = M()->query($goods_ids_nolimit_sql);

					if( !empty($goods_ids_nolimit_arr) )
					{
						foreach($goods_ids_nolimit_arr as $val){
							$ids_arr[] = $val['id'];
						}
					}


					$ids_str = implode(',',$ids_arr);

					if( !empty($ids_str) )
					{
						$where .= "  and g.id in ({$ids_str})";
					} else{
						$where .= " and 0 ";
					}
				}else{
					$goods_ids_nohead_sql = "select pg.id from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as pg," .C('DB_PREFIX')."eaterplanet_ecommerce_goods_to_category as g
								where pg.id = g.goods_id and g.cate_id = {$gid} ";
					$goods_ids_nohead_arr = M()->query($goods_ids_nohead_sql);

					$ids_arr = array();
					if( !empty($goods_ids_nohead_arr) )
					{
						foreach($goods_ids_nohead_arr as $val){
							$ids_arr[] = $val['id'];
						}
					}

					$ids_str = implode(',',$ids_arr);

					if( !empty($ids_str) )
					{
						$where .= "  and g.id in ({$ids_str})";
					} else{
						$where .= " and 0 ";
					}
				}

				// $where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
				$where .= " and gc.begin_time <={$now_time} ";
				$where .= " and gc.is_new_buy=0 and gc.is_spike_buy = 0 and g.is_seckill = 0 ";

				$community_goods = '';
				$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video,gc.oneday_limit_count, gc.total_limit_count, gc.one_limit_count,gc.goods_start_count ', $where, 0, 10000);

				$list = $cart = array();
				if( !empty($community_goods) )
				{
					$cart= D('Home/Car');

					foreach($community_goods as $val)
					{
						$tmp_data = array();
						$tmp_data['actId'] = $val['id'];
						$goodsname = htmlspecialchars_decode($val['goodsname']);
						$tmp_data['spuName'] = $goodsname;
						$tmp_data['spuCanBuyNum'] = $val['total'];
						$tmp_data['spuDescribe'] = $val['subtitle'];
						$tmp_data['end_time'] = $val['end_time'];
						$tmp_data['is_take_vipcard'] = $val['is_take_vipcard'];
						$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];
						$tmp_data['actEnd'] = time()>$val['end_time'];

						$tmp_data['oneday_limit_count'] = $val['oneday_limit_count'];
						$tmp_data['total_limit_count'] = $val['total_limit_count'];
						$tmp_data['one_limit_count'] = $val['one_limit_count'];
						$tmp_data['goods_start_count'] = $val['goods_start_count'];

						$productprice = $val['productprice'];
						$tmp_data['marketPrice'] = explode('.', $productprice);

						if( !empty($val['big_img']) )
						{
							$tmp_data['bigImg'] = tomedia($val['big_img']);
						}

						$good_image = D('Home/Pingoods')->get_goods_images($val['id']);
						if( !empty($good_image) )
						{
							$tmp_data['skuImage'] = tomedia($good_image['image']);
						}
						$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);
						$price = $price_arr['price'];

						$tmp_data['actPrice'] = explode('.', $price);
						$tmp_data['card_price'] = $price_arr['card_price'];
						$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'],$member_id);

						if( !empty($tmp_data['skuList']) )
						{
							$tmp_data['car_count'] = 0;
						}else{
							$car_count = $cart->get_wecart_goods($val['id'],"",$head_id ,$token);
							if( empty($car_count)  )
							{
								$tmp_data['car_count'] = 0;
							}else{
								$tmp_data['car_count'] = $car_count;
							}
						}

						if($is_open_fullreduction == 0)
						{
							$tmp_data['is_take_fullreduction'] = 0;
						}else if($is_open_fullreduction == 1){
							$tmp_data['is_take_fullreduction'] = $val['is_take_fullreduction'];
						}

						// 商品角标
						$label_id = unserialize($val['labelname']);
						if($label_id){
							$label_info = D('Home/Pingoods')->get_goods_tags($label_id);
							if($label_info){
								if($label_info['type'] == 1){
									$label_info['tagcontent'] = tomedia($label_info['tagcontent']);
								} else {
									$label_info['len'] = mb_strlen($label_info['tagcontent'], 'utf-8');
								}
							}
							$tmp_data['label_info'] = $label_info;
						}
						$tmp_data['is_video'] = empty($val['video']) ? false : true;
						$list[] = $tmp_data;
					}
				}

				$cateList[] = array('cate_info'=>$cate_info, 'list'=>$list);
			}
		}


	    $is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');
	    // 客服按钮
	    $user_service_switch = D('Home/Front')->get_config_by_name('user_service_switch');
	    $theme = D('Home/Front')->get_config_by_name('index_list_theme_type');

	    echo json_encode(
	    	array(
	    		'code' => 0,
				'list' => $cateList,
				'is_vip_card_member' => $is_vip_card_member,
				'cur_time' => time(),
				'full_reducemoney' => $full_reducemoney,
				'full_money' => $full_money,
				'is_open_vipcard_buy' => $is_open_vipcard_buy,
				'is_open_fullreduction' => $is_open_fullreduction,
				'is_show_cate_tabbar' => $is_show_cate_tabbar,
				'user_service_switch' => $user_service_switch,
				'theme' => $theme
			)
	    );
		die();
	}


	public function load_gps_goodslist_new()
	{
		$gpc = I('request.');

		$token = $gpc['token'];
		$head_id = $gpc['head_id'];

		$is_only_express = $gpc['is_only_express'];
		$is_open_only_express = 0;
		if($is_only_express==1) {
			$is_open_only_express = D('Home/Front')->get_config_by_name('is_open_only_express');
		}

		if($head_id == 'undefined') $head_id = '';
		$pageNum = $gpc['pageNum'];
		$gid = $gpc['gid'];
		$keyword = $gpc['keyword'];

		$is_random = isset($gpc['is_random']) ? $gpc['is_random'] : 0;
		$is_video = isset($gpc['is_video']) ? $gpc['is_video'] : 0;
		$per_page = isset($gpc['per_page']) ? $gpc['per_page'] : 10;
		$cate_info = '';

		if($gid == 'undefined' || $gid =='' || $gid =='null'  || $gid ==0)
		{
			$gid = 0;
		} else {
			$cate_info = M('eaterplanet_ecommerce_goods_category')->field('banner,name')->where( array('id' => $gid ) )->find();
			if(!empty($cate_info['banner'])) $cate_info['banner'] = tomedia($cate_info['banner']);
		}

		if( !empty($gid) && $gid > 0)
		{
			$gids = D('Home/GoodsCategory')->get_index_goods_category($gid,'normal','','',1);
			$gidArr = array();
			$gidArr[] = $gid;

			foreach ($gids as $key => $val) {
				$gidArr[] = $val['id'];
			}

			$gid = implode(',', $gidArr);
		}

		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$is_member_level_buy = 0;
		$is_vip_card_member = 0;
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			//echo json_encode( array('code' => 2) );
			//die();
		}else{
			$member_id = $weprogram_token['member_id'];
			$is_vip_card_member = 0;

			//member_id
			if( $member_id > 0 )
			{
				$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

				if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
				{

					$now_time = time();

					if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
					{
						$is_vip_card_member = 1;//还是会员
					}else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){
						$is_vip_card_member = 2;//已过期
					}
				}

				if($is_vip_card_member != 1 && $member_info['level_id'] >0 )
				{
					$is_member_level_buy = 1;
				}
			}

		}

	    $member_id = $weprogram_token['member_id'];

		//整点秒杀begin
		$is_seckill = isset($gpc['is_seckill']) ? 1:0;
		$seckill_time = isset($gpc['seckill_time']) ? intval($gpc['seckill_time']):0;
		//整点秒杀end

	    $now_time = time();
		if($is_seckill ==1)
		{
			$where = " g.grounding =1 and g.is_seckill =1 and  g.type ='normal'   ";
		}else
		{
			$where = " g.grounding =1 and g.is_seckill =0 and  g.type ='normal'   ";
		}

		//head_id
		if( !empty($keyword) )
		{
			$where .= " and g.goodsname like '%{$keyword}%'  ";
		}

		if($is_seckill ==1)
		{
			$bg_time = strtotime(  date('Y-m-d').' '.$seckill_time.':00:00' );

			$ed_time = $bg_time + 3600;

			if($gid == 0 && $keyword == ''){
				$where .= "  and gc.begin_time >={$bg_time} and gc.begin_time <{$ed_time}  ";
			} else {
				$where .= " and gc.begin_time >={$bg_time} and gc.begin_time <{$ed_time}  ";
			}

		} else {
			if($gid == 0 && $keyword == ''){
				$where .= " and g.is_index_show = 1 ";
			}
		}

		if($is_seckill ==1)
		{

		}else{
			$where .= " and gc.is_new_buy=0 and gc.is_spike_buy = 0 ";
		}

		if( $is_video == 1 )
		{
			$where .= " and gc.video !=''  ";
		}

		if($is_open_only_express==1 && $is_only_express==1) {
			$where .= " and gc.is_only_express =1 ";
		}

		$now_time = time();
		$where .= " and gc.begin_time <={$now_time} ";

		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');
		if( empty($index_sort_method) )
		{
			$order_sort = 'g.istop DESC, g.settoptime DESC,g.index_sort desc,g.id desc ';
		}

		if( $index_sort_method == 1 )
		{
			$order_sort = 'g.index_sort desc,g.id desc ';
		}

		if($is_random == 1)
		{
			$community_goods = D('Home/Pingoods')->get_new_community_index_goods($head_id,$gid,'g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video,gc.pick_up_type,gc.pick_up_modify,gc.oneday_limit_count, gc.total_limit_count, gc.one_limit_count,gc.goods_start_count ', $where,$offset,$per_page,$order_sort,' rand() ');
		}else{
			$community_goods = D('Home/Pingoods')->get_new_community_index_goods($head_id,$gid,'g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video,gc.pick_up_type,gc.pick_up_modify,gc.oneday_limit_count, gc.total_limit_count, gc.one_limit_count,gc.goods_start_count ', $where,$offset,$per_page,$order_sort);
		}

		if( !empty($community_goods) )
		{
			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');

			$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');

			$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 ? 1:0;


			if(empty($full_reducemoney) || $full_reducemoney <= 0)
			{
				$is_open_fullreduction = 0;
			}

			$cart= D('Home/Car');

			$list = array();
			$copy_text_arr = array();
			$today_time = strtotime( date('Y-m-d').' 00:00:00' );


			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$goodsname = htmlspecialchars_decode($val['goodsname']);
				$tmp_data['spuName'] = $goodsname;
				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['actEnd'] = time()>$val['end_time'];
				$tmp_data['is_take_vipcard'] = $val['is_take_vipcard'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];

				$tmp_data['oneday_limit_count'] = $val['oneday_limit_count'];
				$tmp_data['total_limit_count'] = $val['total_limit_count'];
				$tmp_data['one_limit_count'] = $val['one_limit_count'];
				$tmp_data['goods_start_count'] = $val['goods_start_count'];
				$tmp_data['begin_time'] = date('Y-m-d', $val['begin_time']);

				if($val['pick_up_type'] == 0)
				{
					$val['pick_up_modify'] = date('Y-m-d', $today_time);
				}else if( $val['pick_up_type'] == 1 ){
					$val['pick_up_modify'] = date('Y-m-d', $today_time+86400);
				}else if( $val['pick_up_type'] == 2 )
				{
					$val['pick_up_modify'] = date('Y-m-d', $today_time+86400*2);
				}

				$tmp_data['pick_up_modify'] = $val['pick_up_modify'];


				$productprice = $val['productprice'];
				$tmp_data['marketPrice'] = explode('.', $productprice);

				if( !empty($val['big_img']) )
				{
					$tmp_data['bigImg'] = tomedia($val['big_img']);
				}


				$good_image = D('Home/Pingoods')->get_goods_images($val['id']);
				if( !empty($good_image) )
				{
					$tmp_data['skuImage'] = tomedia($good_image['image']);
				}
				$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);
				$price = $price_arr['price'];

				if( $pageNum == 1 )
				{
					$copy_text_arr[] = array('goods_name' => $val['goodsname'], 'price' => $price);
				}

				$tmp_data['actPrice'] = explode('.', $price);
				$tmp_data['card_price'] = $price_arr['card_price'];

				$tmp_data['levelprice'] = $price_arr['levelprice']; // 客户等级价格
				$tmp_data['is_mb_level_buy'] = $price_arr['is_mb_level_buy']; //是否 客户等级 可享受

				//$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'], $member_id);

				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options_carquantity($val['id'], $member_id, $head_id ,$token);
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{

					$car_count = $cart->get_wecart_goods($val['id'],"",$head_id ,$token);

					if( empty($car_count)  )
					{
						$tmp_data['car_count'] = 0;
					}else{
						$tmp_data['car_count'] = $car_count;
					}

				}

				if($is_open_fullreduction == 0)
				{
					$tmp_data['is_take_fullreduction'] = 0;
				}else if($is_open_fullreduction == 1){
					$tmp_data['is_take_fullreduction'] = $val['is_take_fullreduction'];
				}


				// 商品角标
				$label_id = unserialize($val['labelname']);
				if($label_id){
					$label_info = D('Home/Pingoods')->get_goods_tags($label_id);
					if($label_info){
						if($label_info['type'] == 1){
							$label_info['tagcontent'] = tomedia($label_info['tagcontent']);
						} else {
							$label_info['len'] = mb_strlen($label_info['tagcontent'], 'utf-8');
						}
					}
					$tmp_data['label_info'] = $label_info;
				}

				$tmp_data['is_video'] = empty($val['video']) ? false : true;

				$list[] = $tmp_data;
			}


			$is_show_list_timer = D('Home/Front')->get_config_by_name('is_show_list_timer');
			$is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');


			echo json_encode(array('code' => 0,'now_time' => time(),  'list' => $list ,'is_show_cate_tabbar' => $is_show_cate_tabbar,'is_vip_card_member' => $is_vip_card_member,'is_member_level_buy' => $is_member_level_buy , 'copy_text_arr' => $copy_text_arr, 'cur_time' => time() ,'full_reducemoney' => $full_reducemoney,'full_money' => $full_money,'is_open_vipcard_buy' => $is_open_vipcard_buy,'is_open_fullreduction' => $is_open_fullreduction,'is_show_list_timer'=>$is_show_list_timer , 'cate_info' => $cate_info, 'is_show_cate_tabbar'=>$is_show_cate_tabbar ));
			die();

		}else{
			$is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');

			echo json_encode( array('code' => 1 , 'cate_info' => $cate_info , 'is_show_cate_tabbar'=>$is_show_cate_tabbar ) );
			die();
		}
	}

	/**
	 * 图片广告
	 * @return [json] [description]
	 */
	public function get_advimg(){
		$data = M('eaterplanet_ecommerce_advimg')->order('id desc')->find();
		if(!empty($data)) {
			if(!empty($data['enabled'])){
				$data['thumb'] = tomedia($data['thumb']);
				$data['pos'] = explode(',', $data['pos']);
				echo json_encode( array('code' => 0, 'data' => $data ) );
				die();
			}else{
				echo json_encode( array('code' => 1, 'msg' => "广告图片已关闭" ) );
				die();
			}
		}

		echo json_encode( array('code' => 1, 'msg' => "无广告图片" ) );
		die();
	}

	public function get_diy_info()
	{
		$index_diy_json = D('Home/Front')->get_config_by_name('index_diy_json');

		if($index_diy_json) {
			$data = unserialize($index_diy_json);
			$diyData = htmlspecialchars_decode($data['value']);

			if(!$diyData) {
				$newdata = D('Seller/Diydata')->get_all_config();
				if(!empty($newdata)) $diyJson = $newdata;

				if(!empty($diyJson)) {
					foreach($diyJson as $k=>$v) {
						$v->host = $this->staticHost();
						if($v->type=="RICH_TEXT") {
							$v->html= $this->replaceRichtextImgSrc($v->html);
						}
						$diyJson[$k] = $v;
					}
				}

				if($data) {
					$global = json_decode(htmlspecialchars_decode($data["global"]));
					// var_dump($global);die();
				}
			} else {
				$diyDataJson = json_decode($diyData, true);

				$diyJson = $diyDataJson['value'];
				$global = $diyDataJson['global'];

				if(!empty($diyJson)) {
					foreach($diyJson as $k=>$v) {
						$v['host'] = $this->staticHost();
						if($v['type']=="RICH_TEXT") {
							$v['html'] = $this->replaceRichtextImgSrc($v['html']);
						}
						$diyJson[$k] = $v;
					}
				}
			}

			echo json_encode( array('code' => 0, 'global' => $global, 'diyJson' => $diyJson ) );
			die();
		} else {
			echo json_encode( array('code' => 1, 'msg' => 'DIY页面未设置' ) );
			die();
		}
	}

	public function staticHost() {
		$port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '80';
		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		return "https://" . $host;
	}

	public function replaceRichtextImgSrc($html) {
		$pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.jpeg]))[\'|\"].*?[\/]?>/i";
		$result = preg_replace_callback($pattern, function ($ma) {
			$newUrl = $ma[1];
			$port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '80';
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
			if (strpos($ma[1], 'http') == false) {
				$newUrl = "https://" . $host . $newUrl;
			}
			return str_replace($ma[1], tomedia($newUrl), $ma[0]);
		}, $html);
		return $result;
	}

}
