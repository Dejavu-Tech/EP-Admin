<?php
namespace Home\Model;
use Think\Model;

/**
 * @author Albert.Z
 * @desc 重构前端订单模型
 * Class OrderV2Model
 * @package Home\Model
 */
class OrderV2Model {


    /**
     * @desc 购物车结算 提交， 余额支付分拆出来方法
     * @param $order_all_id
     * @param $order
     * @param $pay_total
     * @param $ck_yupay
     * @param $buy_type
     * @param $is_integral
     * @param $is_spike
     * @param $is_just_1
     */
    public function carOrderYuerPay( $order_all_id, $order ,$pay_total , $ck_yupay ,$buy_type, $is_integral, $is_spike , $is_just_1 )
    {
        $member_id = $order['member_id'];
        $oid = $order['order_id'];

        $member_info = M('eaterplanet_ecommerce_member')->field('we_openid,account_money')->where( array('member_id' => $member_id ) )->find();


        $is_open_yinpay = D('Home/Front')->get_config_by_name('is_open_yinpay');

        if($ck_yupay == 1 && $pay_total >0 && $order['type'] != 'ignore')
        {
            //开始余额支付
            $member_charge_flow_data = array();
            $member_charge_flow_data['formid'] = '';
            $member_charge_flow_data['member_id'] = $member_id;
            $member_charge_flow_data['trans_id'] = $oid;
            $member_charge_flow_data['money'] = $pay_total;
            $member_charge_flow_data['state'] = 3;
            $member_charge_flow_data['charge_time'] = time();
            $member_charge_flow_data['remark'] = '客户前台余额支付';
            $member_charge_flow_data['add_time'] = time();

            M('eaterplanet_ecommerce_member_charge_flow')->add($member_charge_flow_data);

            $charge_flow_id = M('eaterplanet_ecommerce_member_charge_flow')->getLastInsID();

            //开始处理扣钱
            M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->setInc('account_money',-$pay_total);

            $mb_info = M('eaterplanet_ecommerce_member')->field('account_money')->where( array('member_id' =>$member_id ) )->find();

            M('eaterplanet_ecommerce_member_charge_flow')->where( array('id' => $charge_flow_id ) )->save( array('operate_end_yuer' => $mb_info['account_money']) );

        }

        $order_status_id = $order['is_pin'] == 1 ? 2:1;
        if( $buy_type == 'presale' )
        {
            //预售
            $order_status_id = 15;
        }else if( $buy_type == 'virtualcard' )
        {
            $order_status_id = 4;
        }

        //eaterplanet_ecommerce_order_all can_yupay

        //开始处理订单状态
        //$order_all = M('order_all')->where( array('id' => $order_all_id) )->find();

        $order_all = M('eaterplanet_ecommerce_order_all')->where( array('id' => $order_all_id) )->find();


        $out_trade_no = $order_all_id.'-'.time();

        if( !empty($order)  )
        {
			D('Home/Pin')->insertNotifyOrder($order['order_id']);
            //支付完成
            $o = array();
            $o['order_status_id'] =  $order_status_id;
            $o['paytime']=time();
            $o['transaction_id'] = 0;

            M('eaterplanet_ecommerce_order_all')->where( array('id' => $out_trade_no) )->save($o);

            $order_relate_list = M('eaterplanet_ecommerce_order_relate')->where( array('order_all_id' => $order_all['id']) )->select();

            foreach($order_relate_list as $order_relate)
            {
                $order = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_relate['order_id'] ) )->find();

                if( $order && $order['order_status_id'] == 3)
                {
                    $o = array();
                    $o['payment_code'] = 'yuer';
                    $o['order_id']=$order['order_id'];
                    $o['order_status_id'] =  $order_status_id;
                    $o['date_modified']=time();
                    $o['pay_time']=time();
                    $o['transaction_id'] = $is_integral ==1? '积分兑换':'余额支付';

                    if($order['delivery'] == 'hexiao' && $buy_type != 'presale' ){//核销订单 支付完成状态改成  已发货待收货
                        $o['order_status_id'] =  4;
                    }

                    M('eaterplanet_ecommerce_order')->where( array('order_id' => $order['order_id'] ) )->save($o);

                    //暂时屏蔽

                    $kucun_method = D('Home/Front')->get_config_by_name('kucun_method');

                    if( empty($kucun_method) )
                    {
                        $kucun_method = 0;
                    }

                    if($kucun_method == 1)
                    {//支付完减库存，增加销量

                        $order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order['order_id']) )->select();

                        foreach($order_goods_list as $order_goods)
                        {
                            D('Home/Pingoods')->del_goods_mult_option_quantity($order['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],1);

                        }
                    }

                    $oh = array();
                    $oh['order_id']=$order['order_id'];
                    $oh['order_status_id'] = $order_status_id;
                    $oh['comment']='买家已付款';
                    $oh['date_added']=time();
                    $oh['notify']=1;

                    //邀新有礼下单完成领取礼包
                    D('Home/Invitegift')->collectInvitegiftAfterOrder($order, 'orderpay');

                    if(($order['delivery'] == 'hexiao' || $buy_type == 'virtualcard') && $buy_type != 'presale'){//核销订单 支付完成状态改成  已发货待收货 排除预售
                        $oh['order_status_id'] =  4;
                    }

                    M('eaterplanet_ecommerce_order_history')->add($oh);

                    //订单自动配送
                    if($buy_type != 'presale')
                        D('Home/Order')->order_auto_delivery($order);

                    //发送购买通知
                    //TODO 先屏蔽，等待调试这个消息
                    D('Home/Weixinnotify')->orderBuy($order['order_id'], true);
                    if($order['is_pin'] == 1)
                    {
                        $pin_order = M('eaterplanet_ecommerce_pin_order')->where( array('order_id' =>$order['order_id'] ) )->find();

                        //D('Home/Pin')->insertNotifyOrder($order['order_id']);

                        $pin_info = M('eaterplanet_ecommerce_pin')->where(array('pin_id' => $pin_order['pin_id'])  )->find();//加锁查询

                        $pin_buy_count = D('Home/Pin')->get_tuan_buy_count($pin_order['pin_id']);

                        $res = $is_can_add = D('Seller/Redisorder')->add_pintuan_user( $pin_order['pin_id'] );

                        if( $pin_info['state']  == 1 && !$res )
                        {
                            $order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order['order_id']) )->find();

                            M('eaterplanet_ecommerce_pin_order')->where( array('pin_id' =>$pin_order['pin_id'],'order_id' => $order['order_id'] ) )->delete();

                            $newpin_id = D('Home/Pin')->openNewTuan($order['order_id'],$order_goods_info['goods_id'],$order['member_id']);
                            //插入拼团订单
                            D('Home/Pin')->insertTuanOrder($newpin_id,$order['order_id']);
                            unset($pin_info);

                            $is_pin_success = D('Home/Pin')->checkPinSuccess($newpin_id);

                            if($is_pin_success) {
                                D('Home/Pin')->updatePintuanSuccess($newpin_id);
                            }
                        }else{
                            $is_pin_success = D('Home/Pin')->checkPinSuccess($pin_order['pin_id']);

                            if($is_pin_success) {
                                D('Home/Pin')->updatePintuanSuccess($pin_order['pin_id']);
                            }
                        }

                    }

                    //检测预售
                    if( $buy_type == 'presale' )
                    {
                        D('Home/PresaleGoods')->payBackOrder( $order['order_id'] , 0 );
                    }
                    //检测礼品卡
                    if( $buy_type == 'virtualcard' )
                    {
                        D('Seller/VirtualCard')->payBackOrder( $order['order_id'] );
                    }

                }

            }


            //返回支付成功给app
            $data = array();
            $data['code'] = 0;
            $data['has_yupay'] = 1;
            $data['is_integral'] = $is_integral;
            $data['is_spike'] = $is_spike;
            $data['is_go_orderlist'] = $is_just_1;
            $data['order_id'] = $oid;
            $data['order_all_id'] = $order_all_id;

            echo json_encode($data);
            die();
        }

    }

    /**
     * @desc 取消预售过期订单， 但是不退款 不退库存
     * @param $order_ids_arr
     */
    public function canclePresaleOverOrder($order_ids_arr)
    {
        // 启动事务
        M()->startTrans();

        $kucun_method = D('Home/Front')->get_config_by_name('kucun_method');

        if( empty($kucun_method) )
        {
            $kucun_method = 0;
        }

        foreach( $order_ids_arr as $order_id )
        {
            //设置订单状态
            $up_order_data = array();
            $up_order_data['order_status_id'] = 5;

            $result = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id, 'order_status_id' => 15 ) )->lock(true)->save( $up_order_data );
            if( $result )
            {
                //写人订单历史
                $order_history = array();
                $order_history['order_id'] = $order_id;
                $order_history['order_status_id'] = 5;
                $order_history['notify'] = 0;
                $order_history['comment'] = '未在规定时间内支付尾款，订单取消';
                $order_history['date_added']=time();

                M('eaterplanet_ecommerce_order_history')->add($order_history);

                //订单商品
                $goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id) )->select();

                if(isset($goods) && $kucun_method == 0 ){
                    foreach ($goods as $key => $value) {
                        D('Home/Pingoods')->del_goods_mult_option_quantity($order_id,$value['rela_goodsoption_valueid'],$value['goods_id'],$value['quantity'],2);

                    }
                }
            }
        }

        // 提交事务
        M()->commit();

    }

    /**
     * @desc 预售核销订单--发货后变更一次核销时间---应用于购买后几日发货的情况
     * @param $order_goods_id
     * @param $goods_id
     */
    public  function modifyOrderGoodsHexiaoTime( $order_id ,$goods_id , $begin_time )
    {
        $goods_salesroombase = M('eaterplanet_ecommerce_goods_salesroombase')->where(array('goods_id'=>$goods_id))->find();

        $hx_data = [];

        $hx_data['effect_begin_time'] = $begin_time; //核销有效时间开始
        if($goods_salesroombase['hx_expire_type'] == 0){
            $hx_data['effect_end_time'] = $begin_time+$goods_salesroombase['hx_expire_day']*24*60*60;
        }

        M('eaterplanet_ecommerce_order_goods_saleshexiao')->where(['order_id' => $order_id , 'goods_id' => $goods_id ])->save($hx_data);
    }


    /**
     * @desc 购物车结算 提交， 货到付款订单结算方法
     * @param $order_all_id
     * @param $order
     * @param $pay_total
     * @param $cashon_delivery
     * @param $buy_type
     * @param $is_spike
     * @param $is_just_1
     */
    public function carOrderCashonPay( $order_all_id, $order ,$pay_total , $cashon_delivery ,$buy_type, $is_spike , $is_just_1 )
    {
        $member_id = $order['member_id'];
        $oid = $order['order_id'];

        $member_info = M('eaterplanet_ecommerce_member')->field('we_openid,account_money')->where( array('member_id' => $member_id ) )->find();

        $order_status_id = 1;

        //开始处理订单状态
        $order_all = M('eaterplanet_ecommerce_order_all')->where( array('id' => $order_all_id) )->find();


        $out_trade_no = $order_all_id.'-'.time();

        if( !empty($order)  )
        {
            //支付完成
            $o = array();
            $o['order_status_id'] =  $order_status_id;
            $o['paytime']=time();
            $o['transaction_id'] = 0;

            M('eaterplanet_ecommerce_order_all')->where( array('id' => $out_trade_no) )->save($o);

            $order_relate_list = M('eaterplanet_ecommerce_order_relate')->where( array('order_all_id' => $order_all['id']) )->select();

            foreach($order_relate_list as $order_relate)
            {
                $order = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_relate['order_id'] ) )->find();

                if( $order && $order['order_status_id'] == 3)
                {
                    $o = array();
                    $o['payment_code'] = 'cashon_delivery';
                    $o['order_id'] = $order['order_id'];
                    $o['order_status_id'] =  $order_status_id;
                    $o['date_modified'] = time();
                    $o['pay_time'] = time();
                    $o['transaction_id'] = '货到付款';

                    if($order['delivery'] == 'hexiao' && $buy_type != 'presale' ){//核销订单 支付完成状态改成  已发货待收货
                        $o['order_status_id'] =  4;
                    }

                    M('eaterplanet_ecommerce_order')->where( array('order_id' => $order['order_id'] ) )->save($o);

                    //暂时屏蔽
                    $kucun_method = D('Home/Front')->get_config_by_name('kucun_method');
                    if( empty($kucun_method) )
                    {
                        $kucun_method = 0;
                    }

                    if($kucun_method == 1)
                    {//支付完减库存，增加销量

                        $order_goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order['order_id']) )->select();

                        foreach($order_goods_list as $order_goods)
                        {
                            D('Home/Pingoods')->del_goods_mult_option_quantity($order['order_id'],$order_goods['rela_goodsoption_valueid'],$order_goods['goods_id'],$order_goods['quantity'],1);

                        }
                    }

                    $oh = array();
                    $oh['order_id'] = $order['order_id'];
                    $oh['order_status_id'] = $order_status_id;
                    $oh['comment'] = '买家已付款';
                    $oh['date_added'] = time();
                    $oh['notify'] = 1;

                    //邀新有礼下单完成领取礼包
                    D('Home/Invitegift')->collectInvitegiftAfterOrder($order, 'orderpay');

                    if(($order['delivery'] == 'hexiao' || $buy_type == 'virtualcard') && $buy_type != 'presale'){//核销订单 支付完成状态改成  已发货待收货 排除预售
                        $oh['order_status_id'] =  4;
                    }

                    M('eaterplanet_ecommerce_order_history')->add($oh);

                    //订单自动配送
                    if($buy_type != 'presale')
                        D('Home/Order')->order_auto_delivery($order);

                    //发送购买通知
                    //TODO 先屏蔽，等待调试这个消息
                    D('Home/Weixinnotify')->orderBuy($order['order_id'], true);
                    /**if($order['is_pin'] == 1)
                    {
                        $pin_order = M('eaterplanet_ecommerce_pin_order')->where( array('order_id' =>$order['order_id'] ) )->find();

                        D('Home/Pin')->insertNotifyOrder($order['order_id']);

                        $pin_info = M('eaterplanet_ecommerce_pin')->where(array('pin_id' => $pin_order['pin_id'])  )->find();//加锁查询

                        $pin_buy_count = D('Home/Pin')->get_tuan_buy_count($pin_order['pin_id']);

                        $res = $is_can_add = D('Seller/Redisorder')->add_pintuan_user( $pin_order['pin_id'] );

                        if( $pin_info['state']  == 1 && !$res )
                        {
                            $order_goods_info = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order['order_id']) )->find();

                            M('eaterplanet_ecommerce_pin_order')->where( array('pin_id' =>$pin_order['pin_id'],'order_id' => $order['order_id'] ) )->delete();

                            $newpin_id = D('Home/Pin')->openNewTuan($order['order_id'],$order_goods_info['goods_id'],$order['member_id']);
                            //插入拼团订单
                            D('Home/Pin')->insertTuanOrder($newpin_id,$order['order_id']);
                            unset($pin_info);

                            $is_pin_success = D('Home/Pin')->checkPinSuccess($newpin_id);

                            if($is_pin_success) {
                                D('Home/Pin')->updatePintuanSuccess($newpin_id);
                            }
                        }else{
                            $is_pin_success = D('Home/Pin')->checkPinSuccess($pin_order['pin_id']);

                            if($is_pin_success) {
                                D('Home/Pin')->updatePintuanSuccess($pin_order['pin_id']);
                            }
                        }

                    }

                    //检测预售
                    if( $buy_type == 'presale' )
                    {
                        D('Home/PresaleGoods')->payBackOrder( $order['order_id'] , 0 );
                    }
                    //检测礼品卡
                    if( $buy_type == 'virtualcard' )
                    {
                        D('Seller/VirtualCard')->payBackOrder( $order['order_id'] );
                    }**/

                }

            }

            //返回支付成功给app
            $data = array();
            $data['code'] = 0;
            $data['has_yupay'] = 1;
            $data['is_integral'] = 0;
            $data['is_spike'] = $is_spike;
            $data['is_go_orderlist'] = $is_just_1;
            $data['order_id'] = $oid;
            $data['order_all_id'] = $order_all_id;

            echo json_encode($data);
            die();
        }

    }

    public function  getOrderTotalMoney( $item )
    {
        $free_tongji = $item['total'] + $item['packing_fare'] +$item['shipping_fare']-$item['voucher_credit']-$item['fullreduction_money'] - $item['score_for_money'] + $item['localtown_add_shipping_fare']- $item['fare_shipping_free'];
        if($free_tongji < 0){
            $free_tongji = 0;
        }

        return round( $free_tongji ,2 );
    }

}
