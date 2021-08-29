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

namespace Seller\Model;

class GoodsPresaleModel{


    /**
     * @desc 更新商品预售价格
     * @param $goods_id
     */
	public function modifyGoodsPresale( $goods_id )
    {

        $presale_type = I('post.presale_type', 0);//预售方式， 0 定金， 1 全款
        $presale_ding_money = I('post.presale_ding_money', 0 );//定金金额
        $presale_deduction_money = I('post.presale_deduction_money', 0);//定金可抵扣金额

        $presale_ding_time = I('post.presale_ding_time');//定金支付时间
        $presale_ding_time_start = strtotime( $presale_ding_time['start'] ); //开始
        $presale_ding_time_end = strtotime( $presale_ding_time['end'] );//结束

        $presale_limit_balancepaytime = I('post.presale_limit_balancepaytime', 0);//限制尾款支付时间 0 不限制，1 显示
        $presale_balance_paytime = I('post.presale_balance_paytime', 0 );//几天内要支付完尾款
        $presale_sendorder_type = I('post.presale_sendorder_type', 0);//预计发货时间 固定时间 0 固定日期， 1购买后几日发货
        $presale_sendorder_datetime = I('post.presale_sendorder_datetime');//尾款支付后，指定开始发货日期
        $presale_sendorder_afterday = I('post.presale_sendorder_afterday', 0);//尾款支付后，几日发货

        $ins_data = [];
        $ins_data['goods_id'] = $goods_id;
        $ins_data['presale_type'] = $presale_type;
        $ins_data['presale_ding_money'] = $presale_ding_money;
        $ins_data['presale_deduction_money'] = $presale_deduction_money;
        $ins_data['presale_ding_time_start'] = $presale_ding_time_start;
        $ins_data['presale_ding_time_end'] = $presale_ding_time_end;
        $ins_data['presale_limit_balancepaytime'] = $presale_limit_balancepaytime;
        $ins_data['presale_balance_paytime'] = $presale_balance_paytime;
        $ins_data['presale_sendorder_type'] = $presale_sendorder_type;
        $ins_data['presale_sendorder_datetime'] = strtotime( $presale_sendorder_datetime.' 00:00:00' );
        $ins_data['presale_sendorder_afterday'] = $presale_sendorder_afterday;

        $record = M('eaterplanet_ecommerce_goods_presale')->where( ['goods_id' => $goods_id ] )->find();

        if( empty($record) )
        {
            //添加记录
            $ins_data['addtime'] = time();
            M('eaterplanet_ecommerce_goods_presale')->add( $ins_data );
        }else{
            //修改记录
            M('eaterplanet_ecommerce_goods_presale')->where(['id' => $record['id']])->save( $ins_data );
        }

    }
	
	
}
?>
