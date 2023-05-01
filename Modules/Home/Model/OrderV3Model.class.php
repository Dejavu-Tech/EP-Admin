<?php
namespace Home\Model;
use Think\Model;

/**
 * @desc 重构前端订单模型
 * Class OrderV3Model
 * @package Home\Model
 */
class OrderV3Model {

    public static $payed_status_ids = '1,2,4,6,11,14,15';//已付款订单状态值
    public static $wait_send_status_ids = '1';//待发货订单状态
    public static $sending_status_ids = '4,14';//已发货，待收货的订单
    public static $wait_refund_status_ids = '12';//待退款订单数 ， 申请退款中的数量
    public static $wait_pay_status_ids = '3';//待付款订单数量


    /**
     * @desc 根据时间筛选出销售统计
     * @param $begin_time
     * @param $end_time
     */
    public function analySalesByTime( $begin_time , $end_time )
    {
        //1、支付订单数
        $condition_paycount = "order_status_id in (".self::$payed_status_ids.") and pay_time >= {$begin_time} and pay_time <= {$end_time} ";
        $pay_order_count = M('eaterplanet_ecommerce_order')->where($condition_paycount)->count();
        //2、支付订单金额
        $sum_exp = "total+packing_fare+shipping_fare-voucher_credit-fullreduction_money-score_for_money+localtown_add_shipping_fare-fare_shipping_free";
        $pay_order_money = M('eaterplanet_ecommerce_order')->where($condition_paycount)->sum( $sum_exp );

        //3、待发货订单数量
        $condition_wait_send = "order_status_id = ".self::$wait_send_status_ids." and pay_time >= {$begin_time} and pay_time <= {$end_time} ";
        $wait_send_count = M('eaterplanet_ecommerce_order')->where($condition_wait_send)->count();

        //4、配送中订单数量
        $condition_sending = "order_status_id in (".self::$sending_status_ids.") and pay_time >= {$begin_time} and pay_time <= {$end_time} ";
        $sending_order_count = M('eaterplanet_ecommerce_order')->where($condition_sending)->count();

        //5、待退款订单数量
        $condition_waitrefund = "order_status_id = ".self::$wait_refund_status_ids." and pay_time >= {$begin_time} and pay_time <= {$end_time} ";
        $waitrefund_count = M('eaterplanet_ecommerce_order')->where($condition_waitrefund)->count();

        $data = [];
        $data['pay_order_count'] = $pay_order_count;
        $data['pay_order_money'] = round($pay_order_money, 2);
        $data['wait_send_count'] = $wait_send_count;
        $data['sending_order_count'] = $sending_order_count;
        $data['waitrefund_count'] = $waitrefund_count;

        return $data;
    }

    /**
     * @desc 获取今日平台数据
     * @return array
     */
    public function getTodayOrderData()
    {
        //1、今日付款订单
        $begin_time = strtotime( date('Y-m-d ').' 00:00:00' );
        $end_time = $begin_time + 86400;

        $condition_paycount = "order_status_id in (".self::$payed_status_ids.") and pay_time >= {$begin_time} and pay_time < {$end_time} ";
        $todaypay_order_count = M('eaterplanet_ecommerce_order')->where($condition_paycount)->count();

        //2、今日付款总金额
        $sum_exp = "total+packing_fare+shipping_fare-voucher_credit-fullreduction_money-score_for_money+localtown_add_shipping_fare-fare_shipping_free";
        $todaypay_order_money = M('eaterplanet_ecommerce_order')->where($condition_paycount)->sum( $sum_exp );

        //3、今日待付款订单
        $condition_wait_send = "order_status_id = ".self::$wait_pay_status_ids." and date_added >= {$begin_time} and date_added < {$end_time} ";
        $todaywait_send_count = M('eaterplanet_ecommerce_order')->where($condition_wait_send)->count();



        $data = [];
        $data['todaypay_order_count'] = $todaypay_order_count;
        $data['todaypay_order_money'] = round($todaypay_order_money,2);
        $data['todaywait_send_count'] = $todaywait_send_count;

        return $data;
    }

    /**
     * @desc 获取平台用户数据
     */

    public function getPlatformUserData()
    {
        $today_begin_time = strtotime( date('Y-m-d').' 00:00:00' );
        $today_end_time = $today_begin_time +86400;

        $yes_begin_time = $today_begin_time - 86400;
        $yes_end_time = $today_begin_time;

        //1、今日新增客户
        $condition = " create_time >= {$today_begin_time} and create_time < {$today_end_time} ";
        $today_user_count = M('eaterplanet_ecommerce_member')->where($condition)->count();

        //2、昨日新增客户
        $yes_condition = " create_time >= {$yes_begin_time} and create_time < {$yes_end_time} ";
        $yes_user_count = M('eaterplanet_ecommerce_member')->where($yes_condition)->count();
        //3、总客户数
        $total_user_count = M('eaterplanet_ecommerce_member')->count();
        //4、今日新增团长
        $condition_today_head = " addtime >= {$today_begin_time} and addtime < {$today_end_time} ";
        $today_addhead_count = M('eaterplanet_community_head')->where( $condition_today_head )->count();
        //5、昨日新增团长
        $condition_yes_head = " addtime >= {$yes_begin_time} and addtime < {$yes_end_time} ";
        $yes_addhead_count = M('eaterplanet_community_head')->where( $condition_yes_head )->count();
        //6、总团长数
        $total_head_count = M('eaterplanet_community_head')->count();

        $data = [];
        $data['today_user_count'] = $today_user_count;
        $data['yes_user_count'] = $yes_user_count;
        $data['total_user_count'] = $total_user_count;
        $data['today_addhead_count'] = $today_addhead_count;
        $data['yes_addhead_count'] = $yes_addhead_count;
        $data['total_head_count'] = $total_head_count;

        return $data;
    }

    /**
     * @desc 获取商品数量数据
     * @return array
     */
    public function  getPlatformGoodsData()
    {
        //1、商品总数
        $count = M('eaterplanet_ecommerce_goods')->count();
        //2、普通商品数量
        $normal_count = M('eaterplanet_ecommerce_goods')->where( array('type' => 'normal') )->count();
        //3、拼团商品
        $pingoods_count = M('eaterplanet_ecommerce_goods')->where( array('type' => 'pin') )->count();

        //4、积分商品
        $integral_goods_count = M('eaterplanet_ecommerce_goods')->where( array('type' => 'integral') )->count();

        $data = [];
        $data['count'] = $count;
        $data['normal_count'] = $normal_count;
        $data['pingoods_count'] = $pingoods_count;
        $data['integral_goods_count'] = $integral_goods_count;

        return $data;
    }

    /**
     * @desc 获取平台营业数据
     * @return array
     */
    public function getBusinessData()
    {
        //1、团长总提成
        $head_commiss_money = M('eaterplanet_community_head_commiss_order')->where( array( 'state' => 1 ) )->sum('money');

        //2、今日利润 （今日利润：今日商城商品销量的全部金额       -     今日商城商品销量的全部成本金额     =     今日商品利润  ）

        $begin_time = strtotime( date('Y-m-d ').' 00:00:00' );
        $end_time = $begin_time + 86400;
        $condition_paycount = "order_status_id in (".self::$payed_status_ids.") and pay_time >= {$begin_time} and pay_time < {$end_time} ";

        $sum_exp = "total+packing_fare+shipping_fare-voucher_credit-fullreduction_money-score_for_money+localtown_add_shipping_fare-fare_shipping_free";
        $todaypay_order_money = M('eaterplanet_ecommerce_order')->where($condition_paycount)->sum( $sum_exp );

        $sql = "select sum(og.cost_price) as total_cost_price from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og left join ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o    
                  on og.order_id = o.order_id  where o.order_status_id in (".self::$payed_status_ids.") and o.pay_time >= {$begin_time} and o.pay_time < {$end_time} ";


        $res = M()->query( $sql );
        $total_cost_price = $res[0]['total_cost_price'];

        $today_win_money = $todaypay_order_money - $total_cost_price;
        //3、付款人数
        $payed_member_count = M('eaterplanet_ecommerce_order')->where("order_status_id in (".self::$payed_status_ids.")")->count('distinct member_id');
        //4、下单人数
        $addorder_member_count = M('eaterplanet_ecommerce_order')->count('distinct member_id');

        $need_data = [];
        $need_data['head_commiss_money'] = round($head_commiss_money, 2);
        $need_data['today_win_money'] = round($today_win_money,2);
        $need_data['payed_member_count'] = $payed_member_count;
        $need_data['addorder_member_count'] = $addorder_member_count;

        return $need_data;
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
