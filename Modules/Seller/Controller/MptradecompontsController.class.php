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

class MptradecompontsController extends CommonController{
	protected $menu;
	protected function _initialize(){
		parent::_initialize();

		$menu = array(
            'title'    => '交易组件',
            'subtitle' => '交易组件',
            'route' => 'mptradecomponts/index',
            'items'    => array(
                array('title' => '商品列表', 'route' => 'mptradecomponts/index'),
                array('title' => '设置', 'route' => 'mptradecomponts/config'),            )
        );

        $perm_url = strtolower(CONTROLLER_NAME) .'/'. strtolower(ACTION_NAME);
        $this->assign('perm_url', $perm_url );

		//组件权限方法===begin
        if(SELLERUID != 1)
        {
            $seller_info = M('seller')->field('s_role_id')->where( array('s_id' => SELLERUID ) )->find();

            $perm_role = M('eaterplanet_ecommerce_perm_role')->where( array('id' => $seller_info['s_role_id']) )->find();

            $perms_str = $perm_role['perms2'];

            $items = [];
            $can_use_routearr = [];

            foreach( $menu['items'] as $val )
            {
                $val_route =  str_replace('/','.', $val['route']);

                if( strpos($perms_str, '.'.$val_route) !== false )
                {
                    $items[] = $val;
                    $can_use_routearr[] = strtolower($val['route']);
                }
            }
            $menu['items'] = $items;
            if( empty($can_use_routearr) )
            {
                $this->redirect( 'application/index', [], 1,'您没有当前应用权限' );
            }else if( !in_array($perm_url , $can_use_routearr ) )
            {
                $this->redirect( $can_use_routearr[0]  );
            }
        }
        //组件方法end
		$this->menu = $menu;
		$this->assign('menu', $menu );
	}

    /**
     * @author yj
     * @desc 拉取已经提交给微信的商品
     * * 枚举-edit_status
        枚举值	描述 商品草稿状态
        0	初始值
        1	编辑中
        2	审核中
        3	审核失败
        4	审核成功
        * 枚举-status 商品线上状态
        枚举值	描述
        0	初始值
        5	上架
        11	自主下架
        13	违规下架/风控系统下架
     */
	public function index()
	{

        $_GPC = I('request.');

        $this->gpc = $_GPC;

        $condition = ' 1 ';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        $need_edit_spu = 0;

        if (!empty($_GPC['need_edit_spu'])) {
            $need_edit_spu = intval( $_GPC['need_edit_spu'] );
        }

        $result = D('Seller/MpModifyTradeComponts')->getTxGoodsList( $pindex , $psize , $need_edit_spu );

        $total = 0;
        $list = [];

        if( $result['errcode'] == 0 )
        {
            $total = $result['total_num'];
            $list = $result['spus'];
            if( !empty($list) )
            {
                foreach( $list as $key => $val )
                {
                    if( empty($val['title']) )
                    {
                        $val['title'] = '腾讯审核中，商品图片和标题审核成功后显示 ';
                    }else{
                        $val['title'] = $this->decodeUnicode ($val['title']);
                    }


                    switch( $val['status'] )
                    {
                        case 0:
                            $val['status_name'] = '初始值';
                            break;
                        case 5:
                            $val['status_name'] = '上架';
                            break;
                        case 11:
                            $val['status_name'] = '自主下架';
                            break;
                        case 13:
                            $val['status_name'] = '违规下架/风控系统下架';
                            break;
                    }

                    switch( $val['edit_status'] )
                    {
                        case 0:
                            $val['edit_status_name'] = '初始值';
                            break;
                        case 1:
                            $val['edit_status_name'] = '编辑中';
                            break;
                        case 2:
                            $val['edit_status_name'] = '审核中';
                            break;
                        case 3:
                            $val['edit_status_name'] = '审核失败:'.$val['audit_info']['reject_reason'];
                            break;
                        case 4:
                            $val['edit_status_name'] = '审核成功';
                            break;
                    }

                    $list[$key] = $val;
                }
            }
        }

        $pager = pagination2($total, $pindex, $psize);
        $this->list = $list;
        $this->pager = $pager;

		$this->display();
	}

    //把unicode转化成中文

    private function decodeUnicode($str)

    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',

            create_function(

                '$matches',

                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'

            ),

            $str);
    }

    /**
     * @author yj
     * @desc 撤销审核
     */
    public function del_audit()
    {
        $out_product_id = I('get.out_product_id');

        $result = D('Seller/MpModifyTradeComponts')->delAudit( $out_product_id );

        if( $result['errcode'] == 0 )
        {
            show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
            die();
        }else{
            show_json(0,  array('message' => $result['errmsg']) );
            die();
        }
    }

    /**
     * @author yj
     * @desc 删除商品
     */
    public function del()
    {
        $out_product_id = I('get.out_product_id');

        $result = D('Seller/MpModifyTradeComponts')->del( $out_product_id );

        if( $result['errcode'] == 0 )
        {
            show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
            die();
        }else{
            show_json(0,  array('message' => $result['errmsg']) );
            die();
        }
    }

    /**
     * @author yj
     * @desc 上架商品
     */
    public function listing()
    {
        $out_product_id = I('get.out_product_id');

        $result = D('Seller/MpModifyTradeComponts')->listing( $out_product_id );

        if( $result['errcode'] == 0 )
        {
            show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
            die();
        }else{
            show_json(0,  array('message' => $result['errmsg']) );
            die();
        }
    }

    public function delisting()
    {
        $out_product_id = I('get.out_product_id');

        $result = D('Seller/MpModifyTradeComponts')->delisting( $out_product_id );

        if( $result['errcode'] == 0 )
        {
            show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
            die();
        }else{
            show_json(0,  array('message' => $result['errmsg']) );
            die();
        }
    }


    /**
     * @author yj
     * @desc 添加商品
     */
	public function addGoods()
    {
        if (IS_POST) {

            $result = D('Seller/MpModifyTradeComponts')->addGoods();

            if( $result['code'] == 0 )
            {
                show_json(1,  array('url' => U('mptradecomponts/index')) );
                die();
            }else if( $result['code'] == 1 ){
                show_json(0,  array('message' => $result['message']) );
                die();
            }
        }

        //获取类目数据
        $catelist = D('Seller/MpModifyTradeComponts')->shopCatList();
        $last_third_cate_id = 0;

        $this->assign('last_third_cate_id', $last_third_cate_id);
        $this->assign('catelist', $catelist['data'] );
		$this->display('Mptradecomponts/addGoods');
    }

    public function update()
    {
        if (IS_POST) {

            $result = D('Seller/MpModifyTradeComponts')->addGoods();

            if( $result['code'] == 0 )
            {
                show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
                die();
            }else if( $result['code'] == 1 ){
                show_json(0,  array('message' => $result['message']) );
                die();
            }
        }

        $goods_id = I('get.goods_id');
        $goods_info = M('eaterplanet_ecommerce_goods')->field('goodsname')->where(['id' => $goods_id ])->find();

        //获取类目数据
        $catelist = D('Seller/MpModifyTradeComponts')->shopCatList();
        $last_third_cate_id = 0;

        $this->assign('last_third_cate_id', $last_third_cate_id);
        $this->assign('catelist', $catelist['data'] );
        $this->assign('goods_info', $goods_info );
        $this->assign('goods_id', $goods_id );
        $this->display();
    }

	public function config()
    {
        $_GPC = I('request.');

        if (IS_POST) {

            //提交前，获取一次是否接入请求，接入不允许使用了
            $check_result = D('Seller/MpModifyTradeComponts')->registerCheck();

            if( $check_result['errcode'] == '1040003' )
            {
                show_json(0,  array('message' => '该小程序还没接入，请前往微信小程序后台接入交易组件' ) );
                die();
            }
            if( $check_result['errcode'] == 0 && $check_result['data']['status'] == 3 )
            {
                show_json(0,  array('message' => '小程序封禁中' ) );
                die();
            }


            $data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));

            $data['isopen_tradecomponts'] = isset($data['isopen_tradecomponts']) ? $data['isopen_tradecomponts']:0;
            $data['tradecomponts_token'] = isset($data['tradecomponts_token']) ? $data['tradecomponts_token']:'';
            $data['tradecomponts_encodeingaeskey'] = isset($data['tradecomponts_encodeingaeskey']) ? $data['tradecomponts_encodeingaeskey']:'';

            D('Seller/Config')->update($data);

            show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
            die();
        }

        $data = D('Seller/Config')->get_all_config();
        $this->data = $data;

        $this->display();
    }

}
?>
