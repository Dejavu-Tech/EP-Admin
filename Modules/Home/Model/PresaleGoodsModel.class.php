<?php
namespace Home\Model;
use Think\Model;
/**
 * 优惠券模块
 * @author Albert.Z
 *
 */
class PresaleGoodsModel {

    /**
     * @desc 取预售首页商品
     * @param int $limit
     */
	public function getIndexPresaleGoods($is_index_show_pa =1)
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

        $where = " g.grounding =1  and  g.type ='presale'   ";

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
                $tmp_data['spuName'] = '【预售】'.$val['goodsname'];
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

                $goods_presale = M('eaterplanet_ecommerce_goods_presale')->where(['goods_id' => $val['id'] ])->find();

                //增加预售时间：
                $tmp_data['presale_ding_time_start_int'] = $goods_presale['presale_ding_time_start'];
                $tmp_data['presale_ding_time_end_int']   = $goods_presale['presale_ding_time_end'];

                $tmp_data['presale_ding_time_start'] = date('m月d日', $goods_presale['presale_ding_time_start']);
                $tmp_data['presale_ding_time_end'] = date('m月d日', $goods_presale['presale_ding_time_end']);


                //增加预售定金可抵扣多少钱
                $tmp_data['presale_type'] = $goods_presale['presale_type']; //0定金，1全款
                $tmp_data['presale_ding_money'] = round($goods_presale['presale_ding_money'], 2); //定金
                $tmp_data['presale_deduction_money'] = round($goods_presale['presale_deduction_money'],2); //定金 可抵扣多少钱



                $list[] = $tmp_data;
            }
            return ['code' =>0 ,'list' => $list ];
        }else{
            return ['code' => 1];
        }
    }

    /**
     * @desc 获取购物车结算页 预售信息
     * @param $goods_id
     */
    public function getCheckOutPresaleGoodsInfo( $goods_id , $presale_goods_total )
    {
        $result = [];

        $goods_presale_info = M('eaterplanet_ecommerce_goods_presale')->where(['goods_id' => $goods_id ])->find();

        if( empty($goods_presale_info) )
        {
            return ['code' => 1, 'message' => '该商品不是预售类型'];
        }

        //1、预售价格
        $result['goods_price'] = $presale_goods_total;
        $result['presale_type'] = $goods_presale_info['presale_type'];//预售方式， 0 定金， 1全款
        //2、定金抵扣
        $deduction_money = 0;
        if( $goods_presale_info['presale_type'] == 0 )
        {
            //定金抵扣
            //$deduction_money =  $goods_presale_info['presale_deduction_money'] + $goods_presale_info['presale_ding_money'];
            if( empty($goods_presale_info['presale_deduction_money']) )
            {
                $deduction_money = $goods_presale_info['presale_ding_money'];
            }else{
                $deduction_money = $goods_presale_info['presale_deduction_money'];
            }
            $result['presale_ding_money'] = round($goods_presale_info['presale_ding_money'],2);//定金金额


        }else if( $goods_presale_info['presale_type'] == 1 )
        {
            //定金全款
            $deduction_money = $presale_goods_total;
            $result['presale_ding_money'] = round($presale_goods_total,2);//定金金额
        }

        $result['deduction_money'] = round($deduction_money,2); //定金抵扣金额 = 定金金额 + 定金抵扣金额 / 如果是全款抵扣，这里等于全款金额

        //定金支付结束时间 = 尾款支付开始时间
        $result['balance_pay_begintime'] = date('m-d H:i', $goods_presale_info['presale_ding_time_end'] );
        if( $goods_presale_info['presale_limit_balancepaytime'] == 0 )
        {
            //0不限尾款支付时间
            $result['balance_pay_endtime'] = '不限尾款支付时间';
        }else if( $goods_presale_info['presale_limit_balancepaytime'] == 1 )
        {
            //限制尾款支付时间
            $result['balance_pay_endtime'] = $goods_presale_info['presale_ding_time_end'] + 86400 * $goods_presale_info['presale_balance_paytime'] ;
            $result['balance_pay_endtime'] = date('m-d H:i', $result['balance_pay_endtime'] );
        }

        //3、尾款支付时间
        $result['presale_limit_balancepaytime'] = $goods_presale_info['presale_limit_balancepaytime']; //0 不限制尾款支付时间， 1限制时间
        $result['presale_balance_paytime'] = $goods_presale_info['presale_balance_paytime'];//尾款限制 几天后 支付

        //协议说明：
        $presale_agreement = D('Home/Front')->get_config_by_name('presale_agreement');
        if( !empty($presale_agreement) )
        {
            $qian=array("\r\n");
            $hou=array("<br/>");
            $presale_agreement = str_replace($qian,$hou,$presale_agreement );
        }
        $result['presale_agreement'] = $presale_agreement;//已经将换行替换成了<br/>

        //4、预计发货时间
        $result['presale_sendorder_type'] = $goods_presale_info['presale_sendorder_type'];//0 固定时间， 1 购买后几天发货
        $result['presale_sendorder_datetime'] = date('Y-m-d', $goods_presale_info['presale_sendorder_datetime']);//固定的发货日期
        if( $goods_presale_info['presale_sendorder_afterday'] == 0)
        {
            $result['presale_sendorder_afterday'] = '当';//支付尾款后几天发货
        }else{
            $result['presale_sendorder_afterday'] = $goods_presale_info['presale_sendorder_afterday'];//支付尾款后几天发货
        }


        return ['code' => 0 , 'data' => $result ];
    }

    /**
     * @desc 添加订单预售记录表
     * @param $order_id
     * @return bool
     */
    public function addOrderPresale( $order_id )
    {
        $order_goods = M('eaterplanet_ecommerce_order_goods')->where(['order_id' => $order_id ])->find();

        $goods_id = $order_goods['goods_id'];

        $goods_presale = M('eaterplanet_ecommerce_goods_presale')->where(['goods_id' => $goods_id ])->find();

        //开始分析插入数据
        $ins_data = [];
        $ins_data['goods_id'] = $goods_id;
        $ins_data['order_id'] = $order_id;
        $ins_data['addtime'] = time();
        $ins_data['state'] = 0;
        $ins_data['presale_type'] = $goods_presale['presale_type'];
        $ins_data['presale_ding_money'] = $goods_presale['presale_ding_money'] * $order_goods['quantity'];
        $ins_data['presale_deduction_money'] = $goods_presale['presale_type'] == 1 ? 0 : $goods_presale['presale_deduction_money']* $order_goods['quantity'];
        $ins_data['presale_limit_balancepaytime'] = $goods_presale['presale_limit_balancepaytime'];
        //限制尾款支付时间， 0 限制
        if( $goods_presale['presale_limit_balancepaytime'] == 1 )
        {
            $ins_data['presale_balance_beginpaytime'] = $goods_presale['presale_ding_time_end'];
            $ins_data['presale_balance_paytime'] = $goods_presale['presale_ding_time_end']  + 86400 * $goods_presale['presale_balance_paytime'];
        }else{
            $ins_data['presale_balance_paytime'] = 0;
            $ins_data['presale_balance_beginpaytime'] = 0;
        }

        if( $goods_presale['presale_sendorder_type'] == 0)
        {
			$ins_data['presale_sendorder_datetime'] = $goods_presale['presale_sendorder_datetime'];//固定的发货日期
        }else{
			$ins_data['presale_sendorder_datetime'] = 0;
        }


        M('eaterplanet_ecommerce_order_presale')->add( $ins_data );

        return true;
    }

    /**
     * @desc 回调支付 预售
     * @param $order_id
     */
    public function payBackOrder( $order_id , $transaction_id )
    {
        //开始事务 M()->startTrans();  M()->commit();    M()->rollback();
        //0 判断是否存在这个
        M()->startTrans();
        $order_presale = M('eaterplanet_ecommerce_order_presale')->where(['order_id' => $order_id ])->find();

        $order_info = M('eaterplanet_ecommerce_order')->where(['order_id' => $order_id ])->find();

        if( empty($order_presale) )
        {
            M()->rollback();
            return ['code' => 1, 'message' => 'no presale_order record'];
        }
        //不是在待支付状态
        if( !in_array( $order_presale['state'], [0,1] ) )
        {
            M()->rollback();
            return ['code' => 1, 'message' => '预售订单不可再支付'];
        }

        $order_relate = M('eaterplanet_ecommerce_order_relate')->where(['order_id' => $order_id ])->find();
        $order_all_id = $order_relate['order_all_id'];
        $goods_presale = M('eaterplanet_ecommerce_goods_presale')->where(['goods_id' => $order_presale['goods_id'] ])->find();

        if( $order_presale['state'] == 0 )
        {
            //1、判断是首次支付，还是二次支付
            $update_date = [];
            $update_date['state'] = 1;
            $update_date['transaction_id_first'] = $transaction_id;
            $update_date['first_paytime'] = time();
            if( $goods_presale['presale_sendorder_type'] == 0 )
            {
                //固定时间发货
                $update_date['presale_sendorder_datetime'] = $goods_presale['presale_sendorder_datetime'];
            }
            M('eaterplanet_ecommerce_order_presale')->where(['id' => $order_presale['id']])->save( $update_date );

            //2、更改 总订单+ 订单状态为15  已付定金  注意如果是到店自提逻辑，这里的处理
            $o = array();
            $o['order_status_id'] =  15;
            $o['paytime']=time();
            $o['transaction_id'] = $transaction_id;
            M('eaterplanet_ecommerce_order_all')->where( array('id' => $order_all_id) )->save($o);

            //3、如果是全款预售的，那就变更状态
            if( $order_presale['presale_type'] == 1 )
            {
                $update_date = [];
                $update_date['state'] = 2;
                $update_date['transaction_id_second'] = $transaction_id;
                $update_date['second_paytime'] = time();

                if( $goods_presale['presale_sendorder_type'] == 1 ){
                    //购买后几天发货
                    $update_date['presale_sendorder_datetime'] = time() + 86400 * $goods_presale['presale_sendorder_afterday'];
                    D('Home/OrderV2')->modifyOrderGoodsHexiaoTime( $order_id ,$order_presale['goods_id'] , $update_date['presale_sendorder_datetime'] );
                }

                M('eaterplanet_ecommerce_order_presale')->where(['id' => $order_presale['id']])->save( $update_date );


                $o = array();
                $o['order_status_id'] =  1;

                if( $order_info['delivery'] == 'hexiao' )
                {
                    $o['order_status_id'] =  4;
                }

                $o['paytime']=time();
                $o['transaction_id'] = $transaction_id;
                M('eaterplanet_ecommerce_order_all')->where( array('id' => $order_all_id) )->save($o);

                $o = array();
                $o['order_status_id'] =  1;
                if( $order_info['delivery'] == 'hexiao' )
                {
                    $o['order_status_id'] =  4;
                }
                M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save($o);

                //4、更改订单状态为 待发货， 注意如果是到店自提逻辑，这里的处理。
                //订单自动配送
                D('Home/Order')->order_auto_delivery($order_info);
            }

        }else if( $order_presale['state'] == 1 ){
            //2/首次支付处理 二次支付处理
            $update_date = [];
            $update_date['state'] = 2;
            $update_date['transaction_id_second'] = $transaction_id;
            $update_date['second_paytime'] = time();
            if( $goods_presale['presale_sendorder_type'] == 1 )
            {
                //购买后几天发货
                $update_date['presale_sendorder_datetime'] = time() + 86400 * $goods_presale['presale_sendorder_afterday'];
                D('Home/OrderV2')->modifyOrderGoodsHexiaoTime( $order_id ,$order_presale['goods_id'] , $update_date['presale_sendorder_datetime'] );
            }

            M('eaterplanet_ecommerce_order_presale')->where(['id' => $order_presale['id']])->save( $update_date );


            $o = array();
            $o['order_status_id'] = 1;
            if( $order_info['delivery'] == 'hexiao' )
            {
                $o['order_status_id'] =  4;
            }
            $o['paytime']=time();
            $o['transaction_id'] = $transaction_id;
            M('eaterplanet_ecommerce_order_all')->where( array('id' => $order_all_id) )->save($o);


            $o = array();
            if( $order_info['delivery'] == 'hexiao' )
            {
                $o['order_status_id'] =  4;
            }
            M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save($o);

            //2、更改订单状态为 待发货， 注意如果是到店自提逻辑，这里的处理。
            //订单自动配送
            D('Home/Order')->order_auto_delivery($order_info);

        }
        M()->commit();

    }

    /**
     * @desc 获取预售订单信息
     * @param $order_id
     * @return array
     */
    public function getOrderPresaleInfo( $order_id )
    {
        $order_presale = M('eaterplanet_ecommerce_order_presale')->where(['order_id' => $order_id ])->find();

        if( empty($order_presale) )
        {
            return ['code' => 1, 'message' => 'no presale_order'];
        }
        /**
         * presale_type 0 定金 1全款
         * presale_ding_money 支付定金金额
         * presale_deduction_money 定金抵扣金额
         * presale_limit_balancepaytime 限制尾款支付时间： 0 限制， 1 不限制
         * presale_balance_paydate  如果限制尾款支付时间， 该参数表示， 最晚尾款支付时间
         * presale_sendorder_date 尾款支付后发货日期
         * state 状态： 0未支付定金， 1已支付定金，未支付尾款， 2已支付尾款
         * transaction_id_first 定金微信交易号
         * transaction_id_second 尾款微信交易号
         * first_paytime 定金支付时间
         * second_paytime 尾款支付时间
         * need_repay  是否需要再支付， 1 需要支付定金， 2需要支付尾款， 0 不需要支付
         *
         */
        $order_presale['adddate'] = date('Y-m-d H:i:s', $order_presale['addtime']);
        $order_presale['presale_balance_paydate'] = date('Y-m-d H:i:s', $order_presale['presale_balance_paytime']);

        if($order_presale['presale_sendorder_datetime'] == 0)
        {   $order_presale['presale_sendorder_date'] = '';


            $order_goods = M('eaterplanet_ecommerce_order_goods')->where(['order_id' => $order_id])->find();
            $goods_id = $order_goods['goods_id'];
            $goods_presale =  M('eaterplanet_ecommerce_goods_presale')->where(['goods_id' => $goods_id ])->find();

            $order_presale['presale_sendorder_afterday'] = $goods_presale['presale_sendorder_afterday'];
        }
        else{
            $order_presale['presale_sendorder_date'] = date('Y-m-d H:i:s', $order_presale['presale_sendorder_datetime']);
        }

        //判断是否已取消 begin
        $order_presale['is_unpay_ding_cancle'] = 0;

        $order_info = M('eaterplanet_ecommerce_order')->where( ['order_id' => $order_id ] )->find();
        if( $order_info['order_status_id'] == 5 && $order_presale['state'] == 1 )
        {
            $order_presale['is_unpay_ding_cancle'] = 1;
        }
        //end

        //定金支付结束时间 = 尾款支付开始时间
        $order_presale['balance_pay_begintime'] = date('m-d H:i', $order_presale['presale_balance_beginpaytime'] );
        if( $order_presale['presale_limit_balancepaytime'] == 0 )
        {
            //0不限尾款支付时间
            $order_presale['balance_pay_endtime'] = '不限尾款支付时间';
        }else if( $order_presale['presale_limit_balancepaytime'] == 1 )
        {
            //限制尾款支付时间
            $order_presale['balance_pay_endtime'] = date('m-d H:i', $order_presale['presale_balance_paytime'] );
            //$order_presale['balance_pay_endtime'] = date('m-d H:i', $order_presale['balance_pay_endtime'] );
        }



        $order_presale['need_repay'] = 0;//是否需要重新发起支付
        $now_time = time();
        //未付款+预售没有结束
        if( $order_presale['state'] == 0 )
        {
            $order_presale['need_repay'] = 1;
        }

        if( $order_presale['state'] == 1   )
        {
            if(  ($order_presale['presale_limit_balancepaytime'] == 1 && ( $order_presale['presale_balance_beginpaytime'] < $now_time && $order_presale['presale_balance_paytime'] > $now_time ) ) || $order_presale['presale_limit_balancepaytime'] == 0 )
            {
                $order_presale['need_repay'] = 2;
            }


        }

        //协议说明：
        $presale_agreement = D('Home/Front')->get_config_by_name('presale_agreement');
        if( !empty($presale_agreement) )
        {
            $qian=array("\r\n");
            $hou=array("<br/>");
            $presale_agreement = str_replace($qian,$hou,$presale_agreement );
        }
        $order_presale['presale_agreement'] = $presale_agreement;//已经将换行替换成了<br/>

        //$order_presale['presale_for_ordermoney'] = empty($order_presale['presale_deduction_money']) && $order_presale['presale_deduction_money'] < 0.01 ? $order_presale['presale_ding_money'] : $order_presale['presale_deduction_money'];
        if( $order_presale['presale_deduction_money'] >= 0.01)
        {
            $order_presale['presale_for_ordermoney'] = $order_presale['presale_deduction_money'];
        }else{
            $order_presale['presale_for_ordermoney'] = $order_presale['presale_ding_money'] ;
        }



        return ['code' => 0, 'data' => $order_presale ];
    }


    /**
     * @desc 时钟运行通知
     */
    public function cronPresaleMsg()
    {
        $now_time  = time();
        $notify_time = $now_time - 86400;

        $sql = "SELECT * FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_order_presale where state = 1 and is_notify = 0 and presale_balance_beginpaytime <= {$now_time} order by id asc limit 1000 ";

        $presale_list = M()->query($sql);

        if( !empty($presale_list) )
        {
            foreach( $presale_list as $val  )
            {
                $this->sendPresaleOrderMsg( $val['order_id'] );
                M('eaterplanet_ecommerce_order_presale')->where(['id' => $val['id'] ])->save( ['is_notify' => 1] );
            }
        }
        //回收预售订单过期的
        $sql = "SELECT order_id FROM ".C('DB_PREFIX')."eaterplanet_ecommerce_order_presale where state = 1 and presale_limit_balancepaytime = 1 and presale_balance_paytime <= {$now_time} ";

        $over_presale_list = M()->query($sql);
        if( !empty($over_presale_list) )
        {
            $over_order_ids = [];
            foreach( $over_presale_list as $val )
            {
                $over_order_ids[] = $val['order_id'];
            }

            D('Home/OrderV2')->canclePresaleOverOrder($over_order_ids);
        }
    }

    /**
     * @desc 发送预售订单订阅消息
     * @param $order_id
     * @return array
     */
    public function sendPresaleOrderMsg( $order_id )
    {
        $order_info = M('eaterplanet_ecommerce_order')->where(['order_id' => $order_id ])->find();

        $member_id  = $order_info['member_id'];

        $member_info = M('eaterplanet_ecommerce_member')->where(['member_id' => $member_id])->find();

        $presale_info = $this->getOrderPresaleInfo( $order_id );

        if( $presale_info['code'] == 1 )
        {
            return ['code' => 1, 'message' => '未找到'];
        }

        $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');

        $url =  $shop_domain;

        $template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_presale_ordercan_continuepay' );

        if($order_info['delivery'] == 'hexiao'){
            $pagepath = 'eaterplanet_ecommerce/pages/order/order?id='.$order_info['order_id']."&delivery=hexiao";
        }else if( $order_info['delivery'] == 'pickup' )
        {
            $pagepath = 'eaterplanet_ecommerce/pages/order/order?id='.$order_info['order_id']."&delivery=pickup";
        }
        //id=7853&delivery=pickup
        else{
            $pagepath = 'eaterplanet_ecommerce/pages/order/order?id='.$order_info['order_id'];
        }

        $order_goods_name = D('Home/OrderGoodsV2')->getOrderGoodsName( $order_id );

        $mb_subscribe = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $member_id, 'type' => 'presale_ordercan_continuepay') )->find();

        if( !empty($mb_subscribe) )
        {

            $order_goods_name2 = mb_substr($order_goods_name,0,20,'utf-8');
            $order_goods_name2 = mb_substr( $order_goods_name2,0,10,'utf-8');

            $template_data = array();
            $template_data['character_string2'] = array('value' => $order_info['order_num_alias'] );
            $template_data['thing3'] = array('value' => $order_goods_name2 );
            $template_data['thing7'] = array('value' => '商品已到货，请尽快支付尾款' );

            D('Seller/User')->send_subscript_msg( $template_data,$url,$pagepath,$member_info['we_openid'],$template_id );

            M('eaterplanet_ecommerce_subscribe')->where( array('id' => $mb_subscribe['id'] ) )->delete();

        }

    }


}
