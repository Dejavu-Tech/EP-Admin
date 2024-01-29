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

class UtilController extends CommonController{

	protected function _initialize(){
		parent::_initialize();

		$this->full       = intval($_GPC['full']);
        $this->platform   = trim($_GPC['platform']);
        $this->defaultUrl = trim($_GPC['url']);
        $this->allUrls    = array(
            array(
                'name' => '商城页面',
                'title' => "BASICS_LINK",
                'list' => array(
                    array('name' => '社区团购首页', 'title' => "INDEX", 'url' => '/eaterplanet_ecommerce/pages/index/index', 'url_wxapp' => '/eaterplanet_ecommerce/pages/index/index'),
                    array('name' => '购物车', 'title' => "CART", 'url' => '/eaterplanet_ecommerce/pages/order/shopCart', 'url_wxapp' => '/eaterplanet_ecommerce/pages/order/shopCart'),
                    array('name' => '团长申请页面', 'title' => "GROUPCENTER_APPLY", 'url' => '/eaterplanet_ecommerce/moduleA/groupCenter/apply', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/groupCenter/apply'),
					array('name' => '团长申请介绍页面', 'title' => "GROUPCENTER_RECRUIT", 'url' => '/eaterplanet_ecommerce/moduleA/groupCenter/recruit', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/groupCenter/recruit'),
					array('name' => '商户申请页面', 'title' => "SUPPLY_APPLY", 'url' => '/eaterplanet_ecommerce/pages/supply/apply', 'url_wxapp' => '/eaterplanet_ecommerce/pages/supply/apply'),
					array('name' => '商户介绍页面地址', 'title' => "SUPPLY_RECRUIT", 'url' => '/eaterplanet_ecommerce/pages/supply/recruit', 'url_wxapp' => '/eaterplanet_ecommerce/pages/supply/recruit'),
					// array('name' => '客户表单信息收集页面', 'title' => "FORM_APPLY", 'url' => '/eaterplanet_ecommerce/pages/form/apply', 'url_wxapp' => '/eaterplanet_ecommerce/pages/form/apply'),
					array('name' => '分类页', 'title' => "CATE", 'url' => '/eaterplanet_ecommerce/pages/type/index', 'url_wxapp' => '/eaterplanet_ecommerce/pages/type/index'),
                    array('name' => '余额充值', 'title' => "USER_CHARGE", 'url' => '/eaterplanet_ecommerce/pages/user/charge', 'url_wxapp' => '/eaterplanet_ecommerce/pages/user/charge'),
					array('name' => '视频商品列表', 'title' => "VIDEO", 'url' => '/eaterplanet_ecommerce/moduleA/video/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/video/index'),
					array('name' => '群接龙', 'title' => "SOLITAIRE", 'url' => '/eaterplanet_ecommerce/moduleA/solitaire/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/solitaire/index'),
                    array('name' => '网商模式商品列表页', 'title' => "GENERALMALL", 'url' => '/eaterplanet_ecommerce/moduleB/generalmall/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleB/generalmall/index'),

				),
            ),
			/**
            array(
                'name' => '商品属性',
                'list' => array(
                    array('name' => '分类搜索', 'url' => '/eaterplanet_ecommerce/pages/goods/search', 'url_wxapp' => '/eaterplanet_ecommerce/pages/goods/search'),
                ),
            ),
			**/
            array(
                'name' => '客户中心',
                'title' => "MEMBER_LINK",
                'list' => array(
                    array('name' => '客户中心', 'title' => "MEMBER", 'url' => '/eaterplanet_ecommerce/pages/user/me', 'url_wxapp' => '/eaterplanet_ecommerce/pages/user/me'),
                    array('name' => '订单列表', 'title' => "ORDER_LIST", 'url' => '/eaterplanet_ecommerce/pages/order/index', 'url_wxapp' => '/eaterplanet_ecommerce/pages/order/index'),
					array('name' => '关于我们', 'title' => "ABOUT_US", 'url' => '/eaterplanet_ecommerce/pages/user/articleProtocol?about=1', 'url_wxapp' => '/eaterplanet_ecommerce/pages/user/articleProtocol?about=1'),
                    array('name' => '常见帮助', 'title' => "HELP", 'url' => '/eaterplanet_ecommerce/pages/user/protocol', 'url_wxapp' => '/eaterplanet_ecommerce/pages/user/protocol'),

				   // array('name' => '订单列表', 'url' => '/eaterplanet_ecommerce/pages/order/pintuan', 'url_wxapp' => '/eaterplanet_ecommerce/pages/order/pintuan'),
                   // array('name' => '拼团列表', 'url' => '/eaterplanet_ecommerce/pages/order/pintuan', 'url_wxapp' => '/eaterplanet_ecommerce/pages/order/pintuan'),
                   // array('name' => '我的收藏', 'url' => '/eaterplanet_ecommerce/pages/dan/myfav', 'url_wxapp' => '/eaterplanet_ecommerce/pages/dan/myfav'),
                   // array('name' => '我的优惠券', 'url' => '/eaterplanet_ecommerce/pages/dan/quan', 'url_wxapp' => '/eaterplanet_ecommerce/pages/dan/quan'),

                ),
            ),
			array(
                'name' => '其他',
                'title' => "OTHER_LINK",
                'list' => array(
                    array('name' => '商户列表', 'title' => "SUPPLY_INDEX", 'url' => '/eaterplanet_ecommerce/pages/supply/index', 'url_wxapp' => '/eaterplanet_ecommerce/pages/supply/index'),
                    array('name' => '专题列表', 'title' => "SPECIAL_INDEX", 'url' => '/eaterplanet_ecommerce/moduleA/special/list', 'url_wxapp' => '/eaterplanet_ecommerce/pages/special/list'),
                    array('name' => '拼团首页', 'title' => "PIN_INDEX", 'url' => '/eaterplanet_ecommerce/moduleA/pin/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/pin/index'),
					array('name' => '付费会员首页', 'title' => "VIP_UPGRADE", 'url' => '/eaterplanet_ecommerce/moduleA/vip/upgrade', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/vip/upgrade'),
					array('name' => '积分/签到/兑换', 'title' => "SCORE_SIGNIN", 'url' => '/eaterplanet_ecommerce/moduleA/score/signin', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/score/signin'),
					array('name' => '菜谱', 'title' => "MENU_INDEX", 'url' => '/eaterplanet_ecommerce/moduleA/menu/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/menu/index'),
                    array('name' => '整点秒杀', 'title' => "DECKILL_INDEX", 'url' => '/eaterplanet_ecommerce/moduleA/seckill/list', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/seckill/list'),
					array('name' => '直播列表', 'title' => "LIVE_INDEX", 'url' => '/eaterplanet_ecommerce/moduleB/live/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleB/live/index'),
                    array('name' => '商品预售', 'title' => "PRESALE_INDEX", 'url' => '/eaterplanet_ecommerce/moduleB/presale/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleB/presale/index'),
                    array('name' => '礼品卡中心', 'title' => "VIRTUALCARD_INDEX", 'url' => '/eaterplanet_ecommerce/moduleB/virtualcard/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleB/virtualcard/index'),
                    array('name' => '分销中心', 'title' => "DISTRIBUTION_CENTER", 'url' => '/eaterplanet_ecommerce/distributionCenter/pages/me', 'url_wxapp' => '/eaterplanet_ecommerce/distributionCenter/pages/me'),
                    array('name' => '邀请有礼', 'title' => "INVITE_INDEX", 'url' => '/eaterplanet_ecommerce/moduleB/invite/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleB/invite/index'),
				)
            )
        );
	}


	public function selecturl()
    {

        $platform = $this->platform;
        $full     = $this->full;

        $allUrls = $this->allUrls;

         $this->display();

    }

	public function query()
    {

        $type     = I('request.type');
        $kw       = I('request.kw');
        $full     = I('request.full');
        $platform = I('request.platform');

		$this->type = $type;
		$this->kw = $kw;
		$this->full = $full;
		$this->platform = $platform;

        if (!empty($kw) && !empty($type)) {

            if ($type == 'good') {

                $list = M()->query('SELECT id,goodsname as title,productprice,price as marketprice,sales,type FROM ' .
                    C('DB_PREFIX') . 'eaterplanet_ecommerce_goods WHERE  grounding=1 and total > 0
					AND goodsname LIKE "'.'%' . $kw . '%'.'" ');

                if (!empty($list)) {
                    foreach ($list as &$val) {

						$thumb = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $val['id']) )->order('id asc')->find();

                        $val['thumb'] = tomedia($thumb['image']);
                    }
                }

                //$list = set_medias($list, 'thumb');
                //thumb
            } else if ($type == 'article') {

				$list = M('eaterplanet_ecommerce_article')->field('id,title')->where( " (title LIKE '%".$kw."%' or id like '%".$kw."%' ) and enabled=1" )->select();
            } else if ($type == 'coupon') {
                $list = M('eaterplanet_ecommerce_coupon')->field('id,voucher_title')->where( " (voucher_title LIKE '%".$kw."%' or id like '%".$kw."%' ) " )->select();
            } else if ($type == 'groups') {

            } else if ($type == 'sns') {

            } else if ($type == 'url') {
            	$list = $this->searchUrl($this->allUrls, "name", $kw);
			} else if ($type == 'special') {

				$list = M('eaterplanet_ecommerce_special')->field('id,name')->where("name LIKE '%{$kw}%' and enabled=1  ")->select();
            }
			else if ($type == 'category') {

				$list = M('eaterplanet_ecommerce_goods_category')->field('id,name')->where( " name LIKE '%{$kw}%' " )->where(['cate_type'=>'normal'])->select();
            }
            else if ($type == 'solitaire') {

                $list = M('eaterplanet_ecommerce_solitaire')->field('id,solitaire_name as name')->where( " solitaire_name LIKE '%{$kw}%' and state = 1 " )->select();
            }
            else if ($type == 'pintuan') {

                $list = M('eaterplanet_ecommerce_goods')->field('id,goodsname as name')->where( " goodsname LIKE '%{$kw}%' and type = 'pin' and grounding = 1 " )->select();

                $list = M()->query("SELECT id,goodsname as title,productprice,price as marketprice,sales,type FROM " .
                    C('DB_PREFIX') . "eaterplanet_ecommerce_goods WHERE  grounding=1 and total > 0 and type = 'pin' "
					. " AND goodsname LIKE '%" . $kw . "%'");

                if (!empty($list)) {
                    foreach ($list as &$val) {

                        $thumb = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $val['id']) )->order('id asc')->find();

                        $val['thumb'] = tomedia($thumb['image']);
                    }
                }
            }
			else {
                if ($type == 'creditshop') {

                }
            }
        }

		$this->list = $list;
       //dump($list);die;
        $this->display('Util/selecturl_tpl');
    }

	public function diygeturl() {
        $allUrls = $this->allUrls;

        $link = I('request.link', array());
        $name = I('request.name', array());
        $is_array = true;
        if (!empty($link)) {
            $link = htmlspecialchars_decode($link);
            $link = json_decode($link, true);
            $is_array = is_array($link);
        }

        $data = array();
        $temp_link = [];

        foreach($allUrls as $pk => $pitem) {
            $data[] = array(
                "addon_name" => "",
                "child_list" => array(),
                "name" => $pitem['title'],
                "title" => $pitem['name'],
                "parent" => "MALL_LINK",
                "wap_url" => "",
                "web_url" => ""
            );
            if(isset($pitem['list'])) {
                foreach($pitem['list'] as $ck => $citem) {
                    $tempArr = array(
                        "addon_name" => "",
                        "child_list" => array(),
                        "name" => $citem['title'],
                        "title" => $citem['name'],
                        "parent" => $pitem['title'],
                        "selected" => false,
                        "wap_url" => $citem['url_wxapp'],
                        "web_url" => ""
                    );
                    if ($value[ 'addon_name' ] == '') {
                        if (!empty($link) && $is_array && $link['name'] == $citem[ 'title' ]) {
                            $tempArr['selected'] = true;
                        } elseif (!empty($link) && !$is_array && strtolower($link) == strtolower($citem['url_wxapp'])) {
                            $tempArr['selected'] = true;
                            $temp_link = $tempArr;
                        }
                    }
                    $data[$pk]['child_list'][] = $tempArr;
                }
            }
        }

        if (!$is_array) {
            $link = $temp_link;
        }

        $res = array( "link" => $link, "list" => $data );
        echo json_encode($res);
        die();
    }

}
?>
