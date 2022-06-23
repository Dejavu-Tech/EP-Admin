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

    /**
     * @desc 微信小程序自定义版交易组件及开放接口
     */
namespace Seller\Model;

class MpModifyTradeCompontsModel extends UserModel {

    /**
     * @demo array(2) { ["errcode"]=> int(1040002) ["errmsg"]=> string(56) "该小程序已经接入 rid: 6078dde8-7955f8e0-309fe346" } errcode=0
     * @desc 接入申请
     */
	public function apply()
    {
        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/register/apply?access_token={$access_token}";

        $result_json = $this->sendhttps_post( $url , json_encode([]) );

        $result = json_decode( $result_json, true );

        return $result;
    }

    /**
     * @demo array(2) { ["errcode"]=> int(0) ["data"]=> array(3) { ["status"]=> int(2) ["reject_reason"]=> string(0) "" ["access_info"]=> array(2) { ["spu_audit_success"]=> int(0) ["pay_order_success"]=> int(0) } } }
     * @desc 如果账户未接入，将返回错误码1040003 查询接入结果
     * @return mixed
     */
    public function registerCheck()
    {
        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/register/check?access_token={$access_token}";
        $result_json = $this->sendhttps_post( $url , json_encode([]) );

        $result = json_decode( $result_json, true );
        return $result;
    }

    /**
     * @desc 获取运营类目
     * @demo
     * {
        "errcode": 0,
        "errmsg":"ok",
        "third_cat_list":
        [
        {
        "third_cat_id": 6493,
        "third_cat_name": "爬行垫/毯",
        "qualification": "",
        "qualification_type": 0,
        "product_qualification": "《国家强制性产品认证证书》（CCC安全认证证书）",
        "product_qualification_type": 1,
        "first_cat_id": 6472,
        "first_cat_name": "玩具乐器",
        "second_cat_id": 6489,
        "second_cat_name": "健身玩具"
        },
        ...
        ]
        }
        请求
     */
    public function shopCatList()
    {
        $wepro_appid = D('Home/Front')->get_config_by_name('wepro_appid');
        $key = "{$wepro_appid}mpweixin_shop_list";

        $data = json_decode( S($key) , true);

        if ( empty( $data ) || $data['expire_time'] < time() )
        {
            //重新获取
            $access_token = $this->getMpAccessToken();
            $url = "https://api.weixin.qq.com/shop/cat/get?access_token={$access_token}";
            $result_json = $this->sendhttps_post( $url , json_encode([]) );

            $result = json_decode( $result_json, true );
            if( $result['errcode'] == 0 )
            {
                $third_cat_list = $result['third_cat_list'];
                $need_data = [];
                $need_data['expire_time'] = time() + 86400;
                $need_data['third_cat_list'] = $third_cat_list;

                S( $key ,json_encode($need_data));

                return ['code' => 0, 'data' => $third_cat_list ];
            }else{
                return ['code' => 1, 'message' =>  $result['errmsg'].':'.$result['errcode'] ];
            }
        }
        else{
            return ['code' => 0, 'data' => $data['third_cat_list']];
        }
    }

    /**
     * @desc 添加商品到腾讯接口
     * @return array
     */
    public function addGoods()
    {
        M()->startTrans();

        $goods_id = I('post.goods_id', 0);
        $tx_cate_id = I('post.tx_cate_id', 0);
        $thumbs = I('post.thumbs', [] );
        $is_need_qualification = I('post.is_need_qualification', 0);

        if( $goods_id <= 0 )
        {
            M()->rollback();
            return ['code' => 1, 'message' => '请选择商品'];
        }
        if( $tx_cate_id == 0 )
        {
            M()->rollback();
            return ['code' => 1, 'message' => '请选择分类'];
        }

        if( $is_need_qualification == 1 )
        {
            if( empty($thumbs) )
            {
                M()->rollback();
                return ['code' => 1, 'message' => '需要上传资料，请上传后提交'];
            }
        }
        //判断商品状态是否正常，需要提交去审核的
        $goods_info = M('eaterplanet_ecommerce_goods')->where(['id' => $goods_id ])->find();
        if( $goods_info['grounding'] != 1 )
        {
            M()->rollback();
            return ['code' => 1, 'message' => '商品必须是已上架状态'];
        }

        $goods_option = M('eaterplanet_ecommerce_goods_option')->where(['goods_id' => $goods_id ])->find();
        if( empty($goods_option) )
        {
            //M()->rollback();
           // return ['code' => 1, 'message' => '腾讯要求商品必须有规格，您可以仅设置1个规格'];
        }

        //0、入库商品的申请资料
        //判断是否存在
        $tradecomponts_info = M('eaterplanet_ecommerce_goods_tradecomponts')->where(['goods_id' => $goods_id ])->find();

        $ins_data = [];
        $ins_data['goods_id'] = $goods_id;
        $ins_data['state'] = 0;
        $ins_data['qualification_imagelist'] = serialize( $thumbs );
        $ins_data['third_cate_id'] = $tx_cate_id;
        $ins_data['addtime'] = time();

        if( empty($tradecomponts_info) )
        {
            $tradecomponts_id = M('eaterplanet_ecommerce_goods_tradecomponts')->add( $ins_data );

            // //1、开始构造商品数据结构 2、提交给腾讯审核

            $result = $this->sendTradeGoodsToApply( $tradecomponts_id , 0 );
        }else{
            unset( $ins_data['addtime'] );
            M('eaterplanet_ecommerce_goods_tradecomponts')->where( ['id' => $tradecomponts_info['id']] )->save( $ins_data );
            $tradecomponts_id = $tradecomponts_info['id'];
            // //1、开始构造商品数据结构 2、提交给腾讯审核

            $result = $this->sendTradeGoodsToApply( $tradecomponts_id , 1 );
        }




        //3、判断审核状态
        //提交给腾讯审核时报错。
        if( $result['errcode'] != 0 )
        {
            M()->rollback();
            return ['code' => 1, 'message' => $result['errmsg'].',errcode'.$result['errcode'] ];
        }

        $tx_product_id = $result['data']['product_id'];

        M('eaterplanet_ecommerce_goods_tradecomponts')->where( ['goods_id' =>$goods_id ] )->save( ['tx_product_id' => $tx_product_id ] );

        M()->commit();
        return ['code' => 0];

    }


    /**
     * @desc 发送审核给腾讯
     * @param $tradecomponts_id
     * @return mixed
     */
    public function sendTradeGoodsToApply( $tradecomponts_id , $is_update = 0)
    {
        $trade_info = M('eaterplanet_ecommerce_goods_tradecomponts')->where( ['id' => $tradecomponts_id ] )->find();
        if( empty($trade_info) )
        {
            return ['code' =>1, 'message' => '没有找到交易组件商品'];
        }

        $goods_option = M('eaterplanet_ecommerce_goods_option')->where(['goods_id' => $trade_info['goods_id'] ])->find();
        if( empty($goods_option) )
        {
            //return ['code' => 1, 'message' => '腾讯要求商品必须有规格，您的商品不符合审核要求'];
        }

        $goods_info = M('eaterplanet_ecommerce_goods')->where(['id' => $trade_info['goods_id'] ])->find();
        $goods_common_info = M('eaterplanet_ecommerce_good_common')->where(['goods_id' => $trade_info['goods_id'] ])->find();



        $piclist_arr = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $trade_info['goods_id'] ) )->order('id asc')->select();
        $piclist = [];
        foreach($piclist_arr as $val)
        {
            $piclist[] = tomedia($val['image']);
        }

        if( empty($goods_common_info['big_img']) )
        {
            $big_img = $piclist[0];
        }else{
            $big_img = tomedia($goods_common_info['big_img']);
        }


        $qualification_imagelist = unserialize( $trade_info['qualification_imagelist']);
        if( !empty($qualification_imagelist) )
        {
            foreach( $qualification_imagelist as $key => $val  )
            {
                $qualification_imagelist[$key] = tomedia( $val );
            }
        }
        //开始构造资料
        $data = [];
        $data['out_product_id'] = $trade_info['goods_id'];
        $data['title'] = $goods_info['goodsname'];
        $data['path'] = "eaterplanet_ecommerce/pages/goods/goodsDetail?id=".$trade_info['goods_id'];
        $data['head_img'] = $piclist;
        if( !empty($qualification_imagelist) )
        {
            $data['qualification_pics'] = $qualification_imagelist;
        }

        $data['third_cat_id'] = $trade_info['third_cate_id'];
        $data['brand_id'] = '2100000000';

        $goods_option_item_value = M('eaterplanet_ecommerce_goods_option_item_value')->where(['goods_id' => $trade_info['goods_id'] ])->order('id asc')->select();

        $sku_list = [];
        if( !empty($goods_option_item_value) )
        {
            foreach($goods_option_item_value as $value )
            {
                $option_item_ids = $value['option_item_ids'];
                $productprice = $value['productprice'];
                $marketprice = $value['marketprice'];
                $stock = $value['stock'];

                $option_item_ids_arr = explode('_', $option_item_ids );
                $sku_attr = [];
                $image = '';
                foreach( $option_item_ids_arr as $option_item_id )
                {
                    $option_item_value = M('eaterplanet_ecommerce_goods_option_item')->where(['id' => $option_item_id ])->find();
                    $title = $option_item_value['title'];
                    $goods_option_id = $option_item_value['goods_option_id'];
                    $thumb = empty($option_item_value['thumb']) ? '' : tomedia( $option_item_value['thumb'] );
                    if( !empty($thumb) )
                    {
                        $image = $thumb;
                    }

                    $goods_option_value = M('eaterplanet_ecommerce_goods_option')->where(['id' => $goods_option_id])->find();
                    $option_title = $goods_option_value['title'];

                    $sku_attr['attr_key'] = '请选择'.$option_title;
                    $sku_attr['attr_value'] = $title;

                }
                $sku = [];
                $sku['out_product_id'] = $trade_info['goods_id'];
                $sku['out_sku_id'] = $value['id'];
                $sku['thumb_img'] = empty($image) ? $big_img  : $image;
                $sku['sale_price'] = round($productprice, 2 );
                $sku['market_price'] = round($marketprice, 2 );
                $sku['stock_num'] = $stock;
                $sku['skus'] = $sku_attr;
                $sku_list[] = $sku;
            }
        }else{
            $sku = [];
            $sku['out_product_id'] = $trade_info['goods_id'];
            $sku['out_sku_id'] = $trade_info['goods_id'];
            $sku['thumb_img'] = empty($image) ? $big_img  : $image;
            $sku['sale_price'] = round($goods_info['price'], 2 );
            $sku['market_price'] = round($goods_info['productprice'], 2 );
            $sku['stock_num'] = $goods_info['total'];

            $sku_attr = [];
            $sku_attr[0]['attr_key'] = '请选择商品';
            $sku_attr[0]['attr_value'] = empty($goods_info['subtitle']) ? $goods_info['goodsname'] : $goods_info['subtitle'];

            $sku['skus'] = $sku_attr;
            $sku_list[] = $sku;
        }

        $data['skus'] = $sku_list;


        $access_token = $this->getMpAccessToken();



        if($is_update  == 1)
        {
            $url = "https://api.weixin.qq.com/shop/spu/update?access_token={$access_token}";
        }else{
            $url = "https://api.weixin.qq.com/shop/spu/add?access_token={$access_token}";
        }

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );

        $result = json_decode( $result_json, true );
        return $result;
    }

    /**
     * @desc 上传图片获取mediaid
     * @param $file_path
     * @demo array(2) { ["code"]=> int(0) ["media_id"]=> string(96) "wxashop_OwtQWWaTP5KqZ0Y6RECb5bcWKg2hI9W7E/vTIAPaNAvqxnibXr3xcD/KEowcvTzbSEDkD3Q6lvhTB6437AHymw==" }
     * @return mixed
     */
    public function uploadImg( $file_path )
    {

        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/img/upload?access_token={$access_token}";

        $data = [];
        $data['media'] = '@'.$file_path;

        $cfile = new \CURLFile($file_path );
        $imgdata = array('media' => $cfile);

        $result_json = $this->sendhttps_post( $url , $imgdata );

        $result = json_decode( $result_json, true );
        if( $result['errcode'] == 0 )
        {
            return ['code' => 0, 'media_id' => $result['img_info']['media_id'] ];
        }else{
            return ['code' => 1, 'message' => $result['errmsg'].':errcode='.$result['errmsg'] ];
        }
    }

    /**
     * @desc 上传品牌信息
     * @param $license 营业执照或组织机构代码证，图片url/media_id
     * @param $brand_audit_type  认证审核类型 RegisterType
     * @param $trademark_type  商标分类 TrademarkType
     * @param $brand_management_type  选择品牌经营类型 BrandManagementType
     * @param $commodity_origin_type  商品产地是否进口 CommodityOriginType
     * @param $brand_wording  商标/品牌词
     * @param $sale_authorization  销售授权书（如商持人为自然人，还需提供有其签名的身份证正反面扫描件)，图片url/media_id
     * @param $trademark_registration_certificate  商标注册证书，图片url/media_id
     * @param $trademark_change_certificate  商标变更证明，图片url/media_id
     * @param $trademark_registrant  商标注册人姓名
     * @param $trademark_registrant_nu  商标注册号/申请号
     * @param $trademark_authorization_period  商标有效期，yyyy-MM-dd HH:mm:ss
     * @param $trademark_registration_application  商标注册申请受理通知书，图片url/media_id
     * @param $trademark_applicant   商标申请人姓名
     * @param $trademark_application_time  商标申请时间, yyyy-MM-dd HH:mm:ss
     * @param string $imported_goods_form  中华人民共和国海关进口货物报关单，图片url/media_id
     * @说明：
     * 枚举-WxaAuditSourceType
        1	注册
        2	改名
        3	新增品牌
        4	新增类目
        5	新增品牌类目
        6	新增商品
        7	支付审核
     *枚举-RegisterType
     * 1	国内品牌申请-R标
        2	国内品牌申请-TM标
        3	海外品牌申请-R标
        4	海外品牌申请-TM标
     * 枚举-TrademarkType
        "1"	第1类
        "2"	第2类
        "3"	第3类
        ...	...
        "45"	第45类
     *
     * 枚举-BrandRegistrationType
        枚举值	描述
        1	R标
        2	TM标

        枚举-enum BrandManagementType

        枚举值	描述
        1	自有品牌
        2	代理品牌
        3	无品牌

        枚举-CommodityOriginType

        枚举值	描述
        1	是
        2	否
     */
    public function auditBrand( $license ,$brand_audit_type , $trademark_type , $brand_management_type , $commodity_origin_type , $brand_wording, $sale_authorization,
                                $trademark_registration_certificate, $trademark_change_certificate ,
                                $trademark_registrant, $trademark_registrant_nu , $trademark_authorization_period, $trademark_registration_application ,
                                $trademark_applicant, $trademark_application_time , $imported_goods_form = '')
    {

        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/audit/audit_brand?access_token={$access_token}";

        $data = [];
        $data['audit_req'] = [];
        $data['audit_req']['audit_req'] = $license;
        $data['audit_req']['brand_info'] = [];
        $data['audit_req']['brand_info']['brand_audit_type'] = $brand_audit_type;
        $data['audit_req']['brand_info']['trademark_type'] = $trademark_type;
        $data['audit_req']['brand_info']['brand_management_type'] = $brand_management_type;
        $data['audit_req']['brand_info']['commodity_origin_type'] = $commodity_origin_type;
        $data['audit_req']['brand_info']['brand_wording'] = $brand_wording;
        $data['audit_req']['brand_info']['sale_authorization'] = $sale_authorization;
        $data['audit_req']['brand_info']['trademark_registration_certificate'] = $trademark_registration_certificate;
        $data['audit_req']['brand_info']['trademark_change_certificate'] = $trademark_change_certificate;
        $data['audit_req']['brand_info']['trademark_registrant'] = $trademark_registrant;
        $data['audit_req']['brand_info']['trademark_registrant_nu'] = $trademark_registrant_nu;
        $data['audit_req']['brand_info']['trademark_authorization_period'] = $trademark_authorization_period;
        $data['audit_req']['brand_info']['trademark_registration_application'] = $trademark_registration_application;
        $data['audit_req']['brand_info']['trademark_applicant'] = $trademark_applicant;
        $data['audit_req']['brand_info']['trademark_application_time'] = $trademark_application_time;
        $data['audit_req']['brand_info']['imported_goods_form'] = $imported_goods_form;

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );

        $result = json_decode( $result_json, true );
        return $result;

    }

    /**
     * @desc 类目审核
     * @param $license  营业执照或组织机构代码证，图片url
     * @param $level1   一级类目
     * @param $level2   二级类目
     * @param $level3   三级类目
     * @param $certificate  资质材料，图片url
     * @return mixed
     */
    public function auditCategory($license , $level1 , $level2 , $level3 , $certificate )
    {
        $access_token = $this->getMpAccessToken();

        $url = "https://api.weixin.qq.com/shop/audit/audit_category?access_token={$access_token}";

        $data = [];
        $data['audit_req'] = [];
        $data['audit_req']['license'] = $license;
        $data['audit_req']['category_info'] = [];
        $data['audit_req']['category_info']['level1'] = $level1;
        $data['audit_req']['category_info']['level2'] = $level2;
        $data['audit_req']['category_info']['level3'] = $level3;
        $data['audit_req']['category_info']['certificate'] = $certificate;

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );

        $result = json_decode( $result_json, true );
        return $result;
    }

    /**
     * @desc 获取小程序资质 该接口返回的是曾经在小程序方提交过的审核，非组件的入驻审核！ 如果曾经没有提交，没有储存历史文件，或是获取失败，接口会返回1050006
     * @return mixed
     */
    public function getMiniappCertificate()
    {
        $access_token = $this->getMpAccessToken();

        $url = "https://api.weixin.qq.com/shop/audit/get_miniapp_certificate?access_token={$access_token}";

        $data = [];
        $data['req_type'] = 2;

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );

        $result = json_decode( $result_json, true );
        return $result;

    }


    /**
     * @desc 获取小程序accesstoken
     * @return mixed
     */
    public function getMpAccessToken()
    {
        $weixin_config = array();
        $weixin_config['appid'] = D('Home/Front')->get_config_by_name('wepro_appid');
        $weixin_config['appscert'] = D('Home/Front')->get_config_by_name('wepro_appsecret');

        $jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);

        $re_access_token = $jssdk->getweAccessToken();
        return $re_access_token;
    }

    /**
     * @desc 获取提交给腾讯的商品列表
     * @param int $page
     * @param int $perpage
     * @param int $need_edit_spu 默认0:获取线上数据, 1:获取草稿数据
     *
     * 枚举-edit_status
        枚举值	描述
        0	初始值
        1	编辑中
        2	审核中
        3	审核失败
        4	审核成功
     * 枚举-status
        枚举值	描述
        0	初始值
        5	上架
        11	自主下架
        13	违规下架/风控系统下架
     * @return mixed
     */
    public function getTxGoodsList( $page =1 , $perpage = 20 , $need_edit_spu = 0)
    {
        $access_token = $this->getMpAccessToken();

        $url = "https://api.weixin.qq.com/shop/spu/get_list?access_token={$access_token}";

        $data = [];
        $data['page'] = $page;
        $data['page_size'] = $perpage;
        $data['need_edit_spu'] = $need_edit_spu;

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );

        $result = json_decode( $result_json, true );


        return $result;

    }

    /**
     * @desc 撤销审核
     * @param $out_product_id
     * @return mixed
     */
    public function delAudit( $out_product_id )
    {
        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/spu/del_audit?access_token={$access_token}";

        $data = [];
        $data['out_product_id'] = $out_product_id;

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );

        $result = json_decode( $result_json, true );

        return $result;
    }

    /**
     * @desc 上架商品--自助下架的才能上架
     * @param $out_product_id
     * @return mixed
     */
    public function listing( $out_product_id )
    {
        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/spu/listing?access_token={$access_token}";

        $data = [];
        $data['out_product_id'] = $out_product_id;

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );

        $result = json_decode( $result_json, true );

        return $result;
    }

    public function delisting( $out_product_id )
    {
        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/spu/delisting?access_token={$access_token}";

        $data = [];
        $data['out_product_id'] = $out_product_id;

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );

        $result = json_decode( $result_json, true );

        return $result;
    }

    /**
     * @desc 删除商品
     * @param $out_product_id
     * @return mixed
     */
    public function del( $out_product_id )
    {
        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/spu/del?access_token={$access_token}";

        $data = [];
        $data['out_product_id'] = $out_product_id;

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );

        $result = json_decode( $result_json, true );

        if( $result['errcode'] == 0 )
        {
            M('eaterplanet_ecommerce_goods_tradecomponts')->where(['goods_id' => $out_product_id ])->delete();
        }

        return $result;
    }

    /**
     * @desc 检验场景是否需要校验支付
     * @param string $scene
     * 参数	类型	说明
        errcode	number	错误码
        errmsg	string	错误信息
        is_matched	number	0: 不在支付校验范围内，1: 在支付校验范围内
     * @return mixed
     */
    public function sceneCheck( $scene = '1175' )
    {
        $access_token = $this->getMpAccessToken();
        $url  = "https://api.weixin.qq.com/shop/scene/check?access_token={$access_token}";

        $data = [];
        $data['scene'] = $scene;

        $result_json = $this->sendhttps_post( $url , json_encode( $data ) );
        $result = json_decode( $result_json, true );


        if( $result['errcode'] == 0 && $result['is_matched'] == 1 )
        {
            return 1;
        }else{
            return 0;
        }
    }


    /**
     * @desc 获取交易订单信息
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/order/requestOrderPayment.html
     * @param $order_id
     */
    public function getTradeOrderInfo( $order_id , $time , $pay_total )
    {
        /**
            {
                "create_time": "2020-03-25 13:05:25",
                "type": 0,                                // 非必填，默认为0。0:普通场景, 1:合单支付
                "out_order_id": "xxxxx",                  // 必填，普通场景下的外部订单ID；合单支付（多订单合并支付一次）场景下是主外部订单ID
                "openid": "oTVP50O53a7jgmawAmxKukNlq3XI",
                "path": "/pages/order.html?out_order_id=xxxxx",     // 这里的path中的最好有一个参数的值能和out_order_id的值匹配上
                "out_user_id": "323232323",
                "order_detail":
                {
                "product_infos":
                [
                {
                "out_product_id": "12345",
                "out_sku_id":"23456",
                "product_cnt": 10,
                "sale_price": 100,   //生成这次订单时商品的售卖价，可以跟上传商品接口的价格不一致
                "path": "pages/productDetail/productDetail?productId=2176180",
                "title" : "洗洁精",
                "head_img": "http://img10.360buyimg.com/n1/s450x450_jfs/t1/85865/39/13611/488083/5e590a40E4bdf69c0/55c9bf645ea2b727.jpg",
                },
                ...
                ],
                "pay_info": {
                "pay_method": "微信支付",
                "prepay_id": "42526234625",
                "prepay_time": "2020-03-25 14:04:25"
                },
                "price_info": {
                "order_price": 1600,
                "freight": 500,
                "discounted_price": 100,
                "additional_price": 200,
                "additional_remarks": "税费"
                }
                },
                "delivery_detail": {
                "delivery_type": 1,     // 1: 正常快递, 2: 无需快递, 3: 线下配送, 4: 用户自提
                },
                "address_info": {
                "receiver_name": "张三",
                "detailed_address": "详细收货地址信息",
                "tel_number": "收货人手机号码",
                "country": "国家，选填",
                "province": "省份，选填",
                "city": "城市，选填",
                "town": "乡镇，选填"
                }
                }
         */
        $order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();
        $member_info = M('eaterplanet_ecommerce_member')->where(['member_id' => $order_info['member_id'] ])->find();
        //we_openid

        $need_data = [];

        $need_data['create_time'] = date('Y-m-d H:i:s', $order_info['date_added'] );
        $need_data['out_order_id'] = $order_id;
        $need_data['openid'] = $member_info['we_openid'];
        $need_data['path'] = 'eaterplanet_ecommerce/pages/order/order?id='.$order_id;

        //订单详情todo...
        $order_detail = [];

        $order_goods_list = M('eaterplanet_ecommerce_order_goods')->where(['order_id' => $order_id ])->select();

        foreach($order_goods_list as $order_goods )
        {
            $product_infos = [];
            $product_infos['out_product_id'] = $order_goods['goods_id'];
            $product_infos['out_sku_id'] = "";
            $product_infos['product_cnt'] = $order_goods['quantity'];
            $product_infos['sale_price'] = $order_goods['price'] * 100;
            $product_infos['path'] = "eaterplanet_ecommerce/pages/goods/goodsDetail?id=".$order_goods['goods_id'];
            $product_infos['title'] = $order_goods['name'];
            $product_infos['head_img'] = tomedia($order_goods['goods_images']);

            $order_detail['product_infos'][] = $product_infos;
        }
        $pay_info = [];
        $pay_info['pay_method'] = '微信支付';
        $pay_info['prepay_id'] = $order_info['perpay_id'];
        $pay_info['prepay_time'] = date('Y-m-d H:i:s', $time );

        $order_detail['pay_info'] = $pay_info;

        $price_info = [];
        $price_info['order_price'] = $pay_total * 100;
        $price_info['freight'] = 0;

        $order_detail['price_info'] = $price_info;

        $need_data['order_detail'] = $order_detail;
        $need_data['delivery_detail'] = ['delivery_type' => 3];

        $address_info = [];
        $address_info['receiver_name'] = $order_info['shipping_name'];
        $address_info['detailed_address'] = $order_info['shipping_address'];
        $address_info['tel_number'] = $order_info['shipping_tel'];

        $need_data['address_info'] = $address_info;

        M('eaterplanet_ecommerce_order')->where( ['order_id' => $order_id ] )->save( ['from_type' => 'tradecomponts '] );

        //同步增加订单数据
        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/order/add?access_token={$access_token}";


        $result_json = $this->sendhttps_post( $url , json_encode( $need_data ) );
        //$result = json_decode( $result_json, true );

        return ['code' => 0 , 'order_info' => $need_data ];
    }

    /**
     * @desc 微信交易组件，订单支付成功
     * @param $order_id
     * @param string $action_type
     */
    public function orderPay( $order_id, $action_type = '1' )
    {
        $access_token = $this->getMpAccessToken();
        $url = "https://api.weixin.qq.com/shop/order/pay?access_token={$access_token}";


        $order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();
        $member_info = M('eaterplanet_ecommerce_member')->where(['member_id' => $order_info['member_id'] ])->find();


        $need_data = [];
        $need_data['out_order_id'] = $order_id;
        $need_data['openid'] = $member_info['we_openid'];
        $need_data['action_type'] = $action_type;
        $need_data['action_remark'] = '';
        $need_data['transaction_id'] = $order_info['transaction_id'];
        $need_data['pay_time'] = date('Y-m-d H:i:s', $order_info['pay_time'] );

        $result_json = $this->sendhttps_post( $url , json_encode( $need_data ) );

    }

    /**
     * @desc 检测是否开启商品组件功能模块
     * @param $goods_id
     * @return int
     */
    public function checkGoodsIsTradeComponts( $goods_id )
    {
        $isopen_tradecomponts = D('Home/Front')->get_config_by_name('isopen_tradecomponts');
        $isopen_tradecomponts = !isset($isopen_tradecomponts) || $isopen_tradecomponts == 0 ? 0 : 1;
        if( $isopen_tradecomponts == 0 )
        {
            return 0;
        }

        $info = M('eaterplanet_ecommerce_goods_tradecomponts')->where( ['goods_id' => $goods_id] )->find();
        if( !empty($info) )
        {
            return 1;
        }else{
            return 0;
        }
    }

}
?>
