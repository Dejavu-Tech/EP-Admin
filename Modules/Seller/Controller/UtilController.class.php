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
                'list' => array(
                    array('name' => '商城首页', 'url' => '/eaterplanet_ecommerce/pages/index/index', 'url_wxapp' => '/eaterplanet_ecommerce/pages/index/index'),
                    array('name' => '购物车', 'url' => '/eaterplanet_ecommerce/pages/order/shopCart', 'url_wxapp' => '/eaterplanet_ecommerce/pages/order/shopCart'),
                    array('name' => '团长申请页面', 'url' => '/eaterplanet_ecommerce/moduleA/groupCenter/apply', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/apply'),
					array('name' => '团长申请介绍页面', 'url' => '/eaterplanet_ecommerce/moduleA/groupCenter/recruit', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/groupCenter/recruit'),

					array('name' => '商户申请页面', 'url' => '/eaterplanet_ecommerce/pages/supply/apply', 'url_wxapp' => '/eaterplanet_ecommerce/pages/supply/apply'),
					array('name' => '商户介绍页面地址', 'url' => '/eaterplanet_ecommerce/pages/supply/recruit', 'url_wxapp' => '/eaterplanet_ecommerce/pages/supply/recruit'),
					// array('name' => '会员表单信息收集页面', 'url' => '/eaterplanet_ecommerce/pages/form/apply', 'url_wxapp' => '/eaterplanet_ecommerce/pages/form/apply'),

					array('name' => '分类页', 'url' => '/eaterplanet_ecommerce/pages/type/index', 'url_wxapp' => '/eaterplanet_ecommerce/pages/type/index'),
                    array('name' => '余额充值', 'url' => '/eaterplanet_ecommerce/pages/user/charge', 'url_wxapp' => '/eaterplanet_ecommerce/pages/user/charge'),

					array('name' => '视频商品列表', 'url' => '/eaterplanet_ecommerce/moduleA/video/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/video/index'),

					array('name' => '群接龙', 'url' => '/eaterplanet_ecommerce/moduleA/solitaire/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/solitaire/index'),

                    array('name' => '仅快递商品列表页', 'url' => '/eaterplanet_ecommerce/moduleB/generalmall/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleB/generalmall/index'),

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
                'name' => '会员中心',
                'list' => array(
                    array('name' => '会员中心', 'url' => '/eaterplanet_ecommerce/pages/user/me', 'url_wxapp' => '/eaterplanet_ecommerce/pages/user/me'),
                    array('name' => '订单列表', 'url' => '/eaterplanet_ecommerce/pages/order/index', 'url_wxapp' => '/eaterplanet_ecommerce/pages/order/index'),

					array('name' => '关于我们', 'url' => '/eaterplanet_ecommerce/pages/user/articleProtocol?about=1', 'url_wxapp' => '/eaterplanet_ecommerce/pages/user/articleProtocol?about=1'),
                    array('name' => '常见帮助', 'url' => '/eaterplanet_ecommerce/pages/user/protocol', 'url_wxapp' => '/eaterplanet_ecommerce/pages/user/protocol'),

				   // array('name' => '订单列表', 'url' => '/eaterplanet_ecommerce/pages/order/pintuan', 'url_wxapp' => '/eaterplanet_ecommerce/pages/order/pintuan'),
                   // array('name' => '拼团列表', 'url' => '/eaterplanet_ecommerce/pages/order/pintuan', 'url_wxapp' => '/eaterplanet_ecommerce/pages/order/pintuan'),
                   // array('name' => '我的收藏', 'url' => '/eaterplanet_ecommerce/pages/dan/myfav', 'url_wxapp' => '/eaterplanet_ecommerce/pages/dan/myfav'),
                   // array('name' => '我的优惠券', 'url' => '/eaterplanet_ecommerce/pages/dan/quan', 'url_wxapp' => '/eaterplanet_ecommerce/pages/dan/quan'),

                ),
            ),
			array(
                'name' => '其他',
                'list' => array(
                    array('name' => '商户列表', 'url' => '/eaterplanet_ecommerce/pages/supply/index', 'url_wxapp' => '/eaterplanet_ecommerce/pages/supply/index'),
                    array('name' => '专题列表', 'url' => '/eaterplanet_ecommerce/moduleA/special/list', 'url_wxapp' => '/eaterplanet_ecommerce/pages/special/list'),
                    array('name' => '拼团首页', 'url' => '/eaterplanet_ecommerce/moduleA/pin/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/pin/index'),
					array('name' => '付费会员首页', 'url' => '/eaterplanet_ecommerce/moduleA/vip/upgrade', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/vip/upgrade'),
					array('name' => '积分/签到/兑换', 'url' => '/eaterplanet_ecommerce/moduleA/score/signin', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/score/signin'),
					array('name' => '菜谱', 'url' => '/eaterplanet_ecommerce/moduleA/menu/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/menu/index'),
                    array('name' => '整点秒杀', 'url' => '/eaterplanet_ecommerce/moduleA/seckill/list', 'url_wxapp' => '/eaterplanet_ecommerce/moduleA/seckill/list'),
					array('name' => '直播列表', 'url' => '/eaterplanet_ecommerce/moduleB/live/index', 'url_wxapp' => '/eaterplanet_ecommerce/moduleB/live/index'),
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



}
?>
