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

namespace Seller\Model;

/**
 * @desc 虚拟卡密
 * Class VirtualCardModel
 * @package Seller\Model
 */
class VirtualCardModel{

    /**
     * @desc 添加或插入时变更商品关联兑换码信息
     * @param $goods_id
     */
	public function modifyGoodsVirtualCard( $goods_id )
    {
        $virtual_code_id = I('post.virtual_code_id');

        $goods_code_info = $this->getGoodsVirtualCardInfoByGoodsId( $goods_id );

        if( empty($goods_code_info) )
        {
            //插入
            $ins_data = [];
            $ins_data['goods_id'] = $goods_id;
            $ins_data['code_id'] = $virtual_code_id;

            M('eaterplanet_ecommerce_goods_virturalcard')->add( $ins_data );
        }else{
            //更新
            $up_data = [];
            $up_data['code_id'] = $virtual_code_id;
            M('eaterplanet_ecommerce_goods_virturalcard')->where( ['goods_id' => $goods_id ] )->save( $up_data );
        }

    }

    /**
     * @desc 根据商品id获取商品关联的兑换码组
     * @param $goods_id
     * @return mixed
     */
    public function getGoodsVirtualCardInfoByGoodsId( $goods_id )
    {
        $info = M('eaterplanet_ecommerce_goods_virturalcard')->where( ['goods_id' => $goods_id ] )->find();
        return $info;
    }

    /**
     * @desc 根据code_id获取商品code信息
     * @param $code_id
     * @return mixed
     */
    public function getGoodsVirtualCardInfoByCodeId( $code_id )
    {
        $info = M('eaterplanet_ecommerce_goods_virturalcard')->where( ['code_id' => $code_id ] )->find();
        return $info;
    }

    /**
     * @desc 根据code_id获取商品数量
     * @param $code_id
     * @return mixed
     */
    public function getGoodsVirtualCardCountByCodeId( $code_id )
    {
        $info = M('eaterplanet_ecommerce_goods_virturalcard')->where( ['code_id' => $code_id ] )->count();
        return $info;
    }

    /**
     * @desc 获取有效的可用礼品兑换码
     * @return mixed
     */
    public function getCanUseVirtualcardCodes()
    {
        $list = M('eaterplanet_ecommerce_virtualcard_codes')->where(['state' => 1])->select();
        return $list;
    }

    //退款+取消订单时，需要将数量剔除，订单详情不允许部分退款，todo.....

    /**
     * @param $code_id
     * @return mixed
     */
    public function getCodeInfoByCodeId( $code_id )
    {
        $info = M('eaterplanet_ecommerce_virtualcard_codes')->where(['id' => $code_id ])->find();
        return $info;
    }
    /**
     * @desc 获取未使用的code数量
     * @param $code_id
     * @return mixed
     */
    public function getCodeUsedCount( $code_id )
    {
        $count = M('eaterplanet_ecommerce_order_virtualcard')->where( ['code_id' => $code_id, 'state' => 2 ] )->count();
        return $count;
    }

    /**
     * @desc 获取未使用的code数量
     * @param $code_id
     * @return mixed
     */
    public function getCodeUnUseCount( $code_id )
    {
        $count = M('eaterplanet_ecommerce_order_virtualcard')->where( ['code_id' => $code_id, 'state' => 1 ] )->count();
        return $count;
    }

    /**
     * @desc 获取已失效的code数量
     * @param $code_id
     */
    public function getCodeinvalidCount( $code_id )
    {
        $count = M('eaterplanet_ecommerce_order_virtualcard')->where( ['code_id' => $code_id, 'state' => 2 ] )->count();
        return $count;
    }


    /**
     * @desc 添加修改礼品卡兑换码
     * @param $data
     */
    public function updateCode($data)
    {

        $ins_data = array();
        $ins_data['code_name'] = $data['code_name'];

        $ins_data['effect_type'] = $data['effect_type'];

        $ins_data['effect_days'] = $data['effect_days'];

        $ins_data['code_money'] = $data['code_money'];

        $ins_data['addtime'] = time();
        $ins_data['state'] = $data['state'];

        $id = $data['id'];
        if( !empty($id) && $id > 0 )
        {
            unset($ins_data['addtime']);
            M('eaterplanet_ecommerce_virtualcard_codes')->where( array('id' => $id) )->save( $ins_data );
            $id = $data['id'];
        }else{
            $id = M('eaterplanet_ecommerce_virtualcard_codes')->add( $ins_data );
        }


    }
    /**

     * @desc 添加修改礼品卡兑换码
     * @param $data
     */
    public function updateofflineCode($data)
    {

        $ins_data = array();
        $ins_data['code_name'] = $data['code_name'];

        $ins_data['effect_type'] = $data['effect_type'];

        $ins_data['effect_end_time'] = strtotime( $data['effect_end_time'].':00' );

        $ins_data['code_money'] = $data['code_money'];

        $ins_data['state'] = $data['state'];

        $ins_data['addtime'] = time();

        $id = $data['id'];
        if( !empty($id) && $id > 0 )
        {
            unset($ins_data['addtime']);
            M('eaterplanet_ecommerce_virtualcard_offlinecodes')->where( array('id' => $id) )->save( $ins_data );
            $id = $data['id'];
        }else{
            //开启事务
            M()->startTrans();
            $id = M('eaterplanet_ecommerce_virtualcard_offlinecodes')->add( $ins_data );
            //还要开始生成code...todo.
            $code_quantity = $data['code_quantity'];
            while( true )
            {
                $code_num = $this->generateCode(1);
                $res = M('eaterplanet_ecommerce_virtualcard_offlineusercode')->where( "code = '{$code_num}' " )->find();
                if( empty($res) )
                {
                    $code_quantity--;
                    $ins_user_code_data = [];
                    $ins_user_code_data['offlinecode_id'] = $id;
                    $ins_user_code_data['code'] = $code_num;
                    $ins_user_code_data['state'] = 0;
                    $ins_user_code_data['user_id'] = 0;
                    $ins_user_code_data['usedtime'] = 0;
                    $ins_user_code_data['addtime'] = time();
                    M('eaterplanet_ecommerce_virtualcard_offlineusercode')->add( $ins_user_code_data );
                }
                if( $code_quantity <= 0 )
                {
                    break;
                }


            }
            M()->commit();
        }
    }


    /**
     * 生成vip激活码
     * @param int $nums             生成多少个优惠码
     * @param array $exist_array     排除指定数组中的优惠码
     * @param int $code_length         生成优惠码的长度
     * @param int $prefix              生成指定前缀
     * @return array                 返回优惠码数组
     */
    public function generateCode( $nums,$exist_array='',$code_length = 8,$prefix = '' )
    {

        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
        $promotion_codes = array();//这个数组用来接收生成的优惠码

        for($j = 0 ; $j < $nums; $j++) {

            $code = '';

            for ($i = 0; $i < $code_length; $i++) {

                $code .= $characters[mt_rand(0, strlen($characters)-1)];

            }

            //如果生成的4位随机数不再我们定义的$promotion_codes数组里面
            if( !in_array($code,$promotion_codes) ) {

                if( is_array($exist_array) ) {

                    if( !in_array($code,$exist_array) ) {//排除已经使用的优惠码

                        $promotion_codes[$j] = $prefix.$code; //将生成的新优惠码赋值给promotion_codes数组

                    } else {

                        $j--;

                    }

                } else {

                    $promotion_codes[$j] = $prefix.$code;//将优惠码赋值给数组

                }

            } else {
                $j--;
            }
        }

        return $promotion_codes[0];
    }

    /**
     * @desc 取预售首页商品
     * @param int $limit
     */
    public function getIndexVirturalCardGoods($is_index_show_pa =1)
    {
        $gpc = I('request.');

        $head_id = isset($gpc['head_id']) ? $gpc['head_id'] : 0;
        if( isset($gpc['communityId']) && $head_id == 0 )
        {
            $head_id = $gpc['communityId'];
        }
        $pageNum = isset($gpc['pageNum']) ? $gpc['pageNum'] : 1;

        $per_page = isset($gpc['pre_page']) && !empty($gpc['pre_page']) ? $gpc['pre_page'] : 5;
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

        $where = " g.grounding =1  and  g.type ='virtualcard'   ";

        $is_index_show = isset($gpc['is_index_show']) ? $gpc['is_index_show'] : $is_index_show_pa;
        if($is_index_show==1) {
            $where .= " and g.is_index_show = 1 ";
        }

        if($is_open_only_express==1 && $is_only_express==1) {
            $where .= " and gc.is_only_express =1 ";
        }

        $where .= "and gc.begin_time < {$now_time} ";

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

                $goods_virturalcard = $this->getGoodsVirtualCardInfoByGoodsId( $val['id'] );
                $virturalcard_info = $this->getCodeInfoByCodeId( $goods_virturalcard['code_id'] );
                $code_money = $virturalcard_info['code_money'];

                //增加预售时间：
                $tmp_data['code_money'] = round($code_money, 2);

                $list[] = $tmp_data;
            }
            return ['code' =>0 ,'list' => $list ];
        }else{
            return ['code' => 1];
        }
    }

    /**
     * @desc 取预售首页商品
     * @param int $limit
     */
    public function getUserUserecord( $user_id )
    {

        $gpc = I('request.');
        $pageNum = isset($gpc['pageNum']) ? $gpc['pageNum'] : 1;

        $per_page = isset($gpc['pre_page']) && !empty($gpc['pre_page']) ? $gpc['pre_page'] : 20;

        $offset = ($pageNum - 1) * $per_page;
        $limit = "{$offset},{$per_page}";

        $list = M('eaterplanet_ecommerce_virtualcard_userecord')->where(['use_user_id' => $user_id ])->order('id desc')->limit($limit )->select();

        foreach( $list as &$val )
        {
            $val['adddate'] = date('Y-m-d H:i:s', $val['addtime'] );
            $val['money_format'] = round( $val['money'], 2 );
        }

        if( !empty($list) )
        {
            return ['code' => 0, 'data' => $list ];
        }
        else {
            return ['code' => 2, 'message' => 'no more'];
        }
    }

    /**
     * @desc 插入礼品兑换订单
     * @param $order_id
     */
    public function addVirtualCardOrder( $order_id )
    {
        //1、找到code_id code_sn动态生成 user_id

        $order_info = M('eaterplanet_ecommerce_order')->where(['order_id' => $order_id ])->find();

        $order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( ['order_id' => $order_id ] )->find();

        $goods_virtualcard_info = M('eaterplanet_ecommerce_goods_virturalcard')->where( ['goods_id' => $order_goods_info['goods_id'] ] )->find();

        $code_id = $goods_virtualcard_info['code_id'];

        $code_info = M('eaterplanet_ecommerce_virtualcard_codes')->where( ['id' => $code_id ] )->find();
        $code_money = $code_info['code_money'] * $order_goods_info['quantity'];
        //effect_type  effect_days

        //开始插入数据。。。。。
        $ins_data = [];
        $ins_data['code_id'] = $code_id;
        $ins_data['code_sn'] = md5($order_id.time().$order_info['user_id']);
        $ins_data['state'] = 0;
        $ins_data['order_id'] = $order_id;
        $ins_data['user_user_id'] = 0;
        $ins_data['buy_user_id'] = $order_info['member_id'];
        $ins_data['code_money'] = $code_money;
        $ins_data['effect_type'] = $code_info['effect_type'];
        $ins_data['effect_endtime'] = time() + 86400 * $code_info['effect_days'];
        $ins_data['addtime'] = time();

        M('eaterplanet_ecommerce_order_virtualcard')->add( $ins_data );
    }

    /**
     * @desc 礼品卡支付回调处理
     * @param $order_id
     */
    public function payBackOrder( $order_id )
    {
        $o = array();
        $o['order_status_id'] =  4;
        $o['pay_time']=time();
        $o['express_time']=time();

        M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->save( $o );

        M('eaterplanet_ecommerce_order_virtualcard')->where(['order_id' => $order_id ])->save( ['state' => 1] );
    }

    /**
     * @desc 根据订单id获取
     * @param $order_id
     * @return mixed
     */
    public function getOrderVirtualCardByOrderId( $order_id )
    {
        $info = M('eaterplanet_ecommerce_order_virtualcard')->where(['order_id' => $order_id ])->find();

        return $info;
    }

    /**
     * @desc 获取订单详情所需要的信息
     * @param $order_id
     * @return array
     */
    public function getVirtualCardOrderInfO( $order_id )
    {
        $info = $this->getOrderVirtualCardByOrderId( $order_id );

        if( empty($info) )
        {
            return ['code' =>1, 'message' => 'no record'];
        }

        $now_time  = time();
        $info['is_effect'] = 1;
        $info['effect_enddate'] = date('Y-m-d H:i:s', $info['effect_endtime'] );//已过期日期
        if( $info['effect_type'] == 1 && $info['effect_endtime'] < $now_time )
        {
            $info['is_effect'] = 0;//已过期

            $info['state'] = 3;
        }

        //use_member_name
        $info['use_member_name'] = '--';
        $info['use_date'] = '';
        if( !empty($info['user_user_id']) && $info['user_user_id'] > 0 )
        {
            $user_info = M('eaterplanet_ecommerce_member')->where(['member_id' => $info['user_user_id']])->find();
            if( !empty($user_info) )
            {
                $info['use_member_name'] = $user_info['username'];
            }

            $virtualcard_userecord = M('eaterplanet_ecommerce_virtualcard_userecord')->where(['order_id' => $order_id ])->find();
            if( !empty($virtualcard_userecord) )
            {
                $info['use_date'] = date('Y-m-d H:i:s', $virtualcard_userecord['addtime'] );
            }

        }

        return ['code' =>0 , 'data' => $info ];
    }

    /**
     * @desc 未支付的订单，取消
     * @param $order_id
     */
    public function cancleOrder( $order_id )
    {
        $info = $this->getOrderVirtualCardByOrderId( $order_id );
        if( !empty($info) && $info['state'] == 0 )
        {
            M('eaterplanet_ecommerce_order_virtualcard')->where(['order_id' => $order_id ])->save( ['state' => 3 ] );
        }
    }

    /**
     * @desc 退款的订单，取消
     * @param $order_id
     */
    public function refundOrder( $order_id )
    {
        $info = $this->getOrderVirtualCardByOrderId( $order_id );
        if( !empty($info) && $info['state'] == 1 )
        {
            M('eaterplanet_ecommerce_order_virtualcard')->where(['order_id' => $order_id ])->save( ['state' => 3 ] );
        }
    }

    /**
     * @desc兑换线下核销码
     * @param $code_sn
     * @param $member_id
     * @return array
     */
    public function subOfflineCodeSn( $code_sn , $member_id )
    {
        M()->startTrans();

        $check_info = M('eaterplanet_ecommerce_virtualcard_offlineusercode')->where( ['code' => $code_sn ] )->find();

        if( empty($check_info) )
        {
            M()->rollback();
            return ['code' => 2, 'message' => '该兑换码不存在'];
        }
        else if( $check_info['state'] == 1 )
        {
            M()->rollback();
            return ['code' => 2, 'message' => '该兑换码已被使用'];
        }

        $unuse_info = M('eaterplanet_ecommerce_virtualcard_offlineusercode')->where( ['code' => $code_sn , 'state' => 0 ] )->lock(true)->find();

        if( empty($unuse_info) )
        {
            M()->rollback();
            return ['code' => 2, 'message' => '该兑换码已不存在'];
        }

        //判断这个兑换码是否禁用 code_id
        $code_info = M('eaterplanet_ecommerce_virtualcard_offlinecodes')->where(['id' => $unuse_info['offlinecode_id']])->find();
        if( !empty($code_info) && $code_info['state'] == 0 )
        {
            M()->rollback();
            return ['code' => 2, 'message' => '该兑换码已被禁用'];
        }

        //开始充钱
        D('Admin/Member')->sendMemberMoneyChange($member_id, $code_info['code_money'], 20, '线下礼品卡:'.$code_sn.'兑换余额');

        M('eaterplanet_ecommerce_virtualcard_offlineusercode')->where( ['code' => $code_sn , 'state' => 0 ] )->save(['state' => 1, 'user_id' => $member_id , 'usedtime' => time() ]);


        M()->commit();
        return ['code' => 0 , 'money' => round($code_info['code_money'], 2) ];

    }

    /**
     * @desc 使用礼品卡
     * @param $code_sn
     * @param $member_id
     * @return array
     */
    public function subCodeSn( $code_sn ,$member_id )
    {
        M()->startTrans();

        $check_info = M('eaterplanet_ecommerce_order_virtualcard')->where( ['code_sn' => $code_sn ] )->find();

        if( empty($check_info) )
        {
            M()->rollback();
            return ['code' => 2, 'message' => '该兑换码不存在'];
        }
        else if( $check_info['state'] == 2 )
        {
            M()->rollback();
            return ['code' => 2, 'message' => '该兑换码已被使用'];
        }

        $unuse_info = M('eaterplanet_ecommerce_order_virtualcard')->where( ['code_sn' => $code_sn , 'state' => 1 ] )->lock(true)->find();

        if( empty($unuse_info) )
        {
            M()->rollback();
            return ['code' => 2, 'message' => '该兑换码已失效'];
        }

        $now_time = time();
        //订单确认收货的问题，
        if( $unuse_info['effect_type'] == 1 && $unuse_info['effect_endtime'] < $now_time )
        {
            M()->rollback();
            return ['code' => 2, 'message' => '该兑换码已过期'];
        }

        //判断这个兑换码是否禁用 code_id
        $code_info = M('eaterplanet_ecommerce_virtualcard_codes')->where(['id' => $unuse_info['code_id']])->find();
        if( !empty($code_info) && $code_info['state'] == 0 )
        {
            M()->rollback();
            return ['code' => 2, 'message' => '该兑换码已被禁用'];
        }

        //开始充钱
        D('Admin/Member')->sendMemberMoneyChange($member_id, $unuse_info['code_money'], 20, '礼品卡:'.$code_sn.'兑换余额');

        M('eaterplanet_ecommerce_order_virtualcard')->where( ['code_sn' => $code_sn , 'state' => 1 ] )->save(['state' => 2, 'user_user_id' => $member_id  ]);

        //更改订单为已收货
        D('Home/Frontorder')->receive_order($unuse_info['order_id']);

        //增加兑换码使用记录
        $ins_data = [];
        $ins_data['code_sn'] = $code_sn;
        $ins_data['code_id'] = $unuse_info['code_id'];
        $ins_data['order_id'] = $unuse_info['order_id'];
        $ins_data['money'] = $unuse_info['code_money'];
        $ins_data['use_user_id'] = $member_id;
        $ins_data['addtime'] = time();

        M('eaterplanet_ecommerce_virtualcard_userecord')->add( $ins_data );

        M()->commit();
        return ['code' => 0 ];

    }

    /**
     * @desc 检测是否被禁用
     * @param $user_id
     * @return array
     */
    public function checkUserIsLock( $user_id )
    {
        $info = M('eaterplanet_ecommerce_virtualcard_limit_user')->where(['user_id' => $user_id ])->find();

        $now_time = time();
        if( !empty($info) )
        {
            if( $info['limit_endtime'] > $now_time )
            {
                $min = ceil( ($info['limit_endtime'] - $now_time) / 60  );
                return ['code' => 0 ,'min' => $min ];
            }else{
                M('eaterplanet_ecommerce_virtualcard_limit_user')->where(['user_id' => $user_id ])->delete();
            }
        }

        return ['code' => 1];
    }

    /**
     * @desc 插入错误的兑换码用户
     * @param $user_id
     */
    public function insErrorSubCodeSnUserId( $user_id )
    {
        $ins_data = [];
        $ins_data['user_id'] = $user_id;
        $ins_data['addtime'] = time();

        M('eaterplanet_ecommerce_virtualcard_limituser_error')->add( $ins_data );
    }

    /**
     * 检测是否需要锁定，需要的话，锁定入库
     * @param $user_id
     */
    public function checkIsNeedLockUser( $user_id )
    {
        //几分钟
        $virtcard_flush_limit_miniter = D('Home/Front')->get_config_by_name('virtcard_flush_limit_miniter');

        $virtcard_flush_limit_miniter = empty($virtcard_flush_limit_miniter) ? 1 : $virtcard_flush_limit_miniter;

        //错误几次
        $virtcard_flush_error_timers = D('Home/Front')->get_config_by_name('virtcard_flush_error_timers');
        //冻结多久 小时
        $virtcard_flush_error_hours = D('Home/Front')->get_config_by_name('virtcard_flush_error_hours');

        $now_time = time();
        $begin_time = $now_time - 60 * $virtcard_flush_limit_miniter;

        $counts = M('eaterplanet_ecommerce_virtualcard_limituser_error')->where("user_id={$user_id} and addtime >= {$begin_time} and addtime <= {$now_time} ")->count();

        if( $counts >= $virtcard_flush_error_timers )
        {
            //需要被封
            $info = M('eaterplanet_ecommerce_virtualcard_limit_user')->where(['user_id' => $user_id ])->find();

            $lock_endtime = $now_time + 3600 * $virtcard_flush_error_hours;

            if( !empty($info) )
            {
                M('eaterplanet_ecommerce_virtualcard_limit_user')->where(['user_id' => $user_id ])->save(['limit_endtime' => $lock_endtime ]);
            }else{
                $ins_data = [];
                $ins_data['user_id'] = $user_id;
                $ins_data['limit_endtime'] = $lock_endtime;
                $ins_data['addtime'] = time();

                M('eaterplanet_ecommerce_virtualcard_limit_user')->add( $ins_data );
            }
        }


    }

}
?>
