<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      http://www.ch871.com/
 * @copyright Copyright (c) 2019-2021 ch871.com.
 * @license   http://www.ch871.com/license.html License
 * ==========================================================================
 * 拼团模块
 * @author    Albert.Z
 *
 */
namespace Home\Controller;

class LocaltownController extends CommonController {

	 protected function _initialize()
    {

    	parent::_initialize();

    }

    /**
     *
     * 模拟数据填充
     */
    public function send_data()
    {

        $list = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('state' => 1) )->select();

        $open_redis_server = D('Home/Front')->get_config_by_name('open_redis_server');

        if($open_redis_server == 1) {
            $redis = D('Seller/Redisorder')->get_redis_object_do();

        }

        foreach( $list as $val )
        {
            $lon = $val['shop_lon'];
            $lat = $val['shop_lat'];

            $redis->getRedis()->rawCommand('geoadd', '_aashop', $lon, $lat, $val['id'] );

        }

        echo 'ok';
        die();
    }

	/**
		获取待抢的订单
	**/
    public function get_localtown_delivery()
    {
        $_GPC = I('request.');

        $token =  $_GPC['token'];

        $lon = isset($_GPC['lon']) ? $_GPC['lon'] : '106.611160';
        $lat = isset($_GPC['lat']) ? $_GPC['lat'] : '29.647202';

        $weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }
		$orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

		if( $orderdistribution_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
			die();
		}

        $orderdistribution_info = D('Home/LocaltownSnatch')->get_distribution_info_by_memberid( $member_id );
        //$member_id = $weprogram_token['member_id'];
        $open_redis_server = D('Home/Front')->get_config_by_name('open_redis_server');

        if($open_redis_server == 1) {
            $redis = D('Seller/Redisorder')->get_redis_object_do();

        }
        if(!empty($orderdistribution_info['store_id'])){//独立商户订单
            $order_list = $redis->getRedis()->rawCommand('georadius', '_distributionorder_'.$orderdistribution_info['store_id'], $lon, $lat, '10000', 'km', 'ASC');
        }else{
            $order_list = $redis->getRedis()->rawCommand('georadius', '_distributionorder', $lon, $lat, '10000', 'km', 'ASC');
        }


        if( count($order_list)  > 35 )
        {
            $order_list = array_slice( $order_list ,0,35);
        }

        if( empty($order_list) )
        {
            echo json_encode( array('code' => 2, 'msg' => '暂无订单可抢') );
            die();
        }else{

            $need_data = array();

            foreach( $order_list as $id  )
            {
                $need_data[] = D('Home/LocaltownSnatch')->get_localtown_orderinfo( $id );
            }
            $new_order_notice = 0;
            if($orderdistribution_info['is_new_notice'] == 1){
                $new_order_notice = 1;
            }
            echo json_encode( array('code' => 0, 'data' => $need_data,'new_order_notice'=>$new_order_notice ) );
            die();

        }

    }
    // ASC 根据圆心位置，从近到远的返回元素
    // DESC 根据圆心位置，从远到近的返回元素
    //var_dump($redis->rawCommand('georadius', 'citys', '114', '30', '100', 'km', 'ASC'));

	/**
		获取配送员信息
	**/
	public function get_orderdistribution_info()
	{
		$_GPC = I('request.');

        $token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }
		$orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

		if( $orderdistribution_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
			die();
		}

		//get_distribution_ordercount_bymember_id( $member_id, $state )  2 待取货 3 配送中 4 已送达

		$waite_get_count =  D('Home/LocaltownSnatch')->get_distribution_ordercount_bymember_id( $member_id, 2 );//2 待取货
		$sending_count =  D('Home/LocaltownSnatch')->get_distribution_ordercount_bymember_id( $member_id, 3 );//3 配送中
		$sended_count =  D('Home/LocaltownSnatch')->get_distribution_ordercount_bymember_id( $member_id, 4 );//4 已送达

		$waite_send_list = D('Home/LocaltownSnatch')->get_distribution_waitget_memberlist_by_memberid( $member_id );
		$sending_send_list = D('Home/LocaltownSnatch')->get_distribution_sending_memberlist_by_memberid( $member_id );

		$orderdistribution_info = D('Home/LocaltownSnatch')->get_distribution_info_by_memberid( $member_id );

		$need_data = array();
		$need_data['orderdistribution_info'] = $orderdistribution_info;
		$need_data['waite_get_count'] = $waite_get_count;
		$need_data['sending_count'] = $sending_count;
		$need_data['sended_count'] = $sended_count;
		$need_data['waite_send_list'] = $waite_send_list;
		$need_data['sending_send_list'] = $sending_send_list;

		echo json_encode( array('code' => 0 , 'data' => $need_data ) );
		die();
		//TODO...
	}

	/**
		获取配送员的相关类型的订单
	**/
	public function get_distribution_orderlist()
	{
		$_GPC = I('request.');

        $token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }
		$orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

		if( $orderdistribution_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
			die();
		}

		$type = isset($_GPC['type']) && $_GPC['type'] > 0 ? $_GPC['type'] : 2;
		$page = isset($_GPC['page']) && $_GPC['page'] > 0 ? $_GPC['page'] : 1;

		$perpage = 10;

		$offset = ($page - 1) * $perpage;

		$orderlist = D('Home/LocaltownSnatch')->get_distribution_orderlist_by_member_id( $member_id, $type, $offset, $perpage );

		if( empty($orderlist) )
		{
			echo json_encode( array('code' => 2, 'msg' => '没有更多数据') );
			die();
		}else{
			echo json_encode( array('code' => 0 , 'data' => $orderlist ) );
			die();
		}

	}

	/**
		配送员中心-统计
	**/
	public function get_distribution_statics()
	{
		$_GPC = I('request.');

        $token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }
		$orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

		if( $orderdistribution_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
			die();
		}

		//累计抢单数
		$total_getorder_count = 0;
		$wait_send_count = D('Home/LocaltownSnatch')->get_distribution_ordercount_bymember_id( $member_id, 2 );
		$sending_count = D('Home/LocaltownSnatch')->get_distribution_ordercount_bymember_id( $member_id, 3 );
		$sended_count = D('Home/LocaltownSnatch')->get_distribution_ordercount_bymember_id( $member_id, 4 );

		$total_getorder_count = $wait_send_count + $sending_count + $sended_count;

		//系统分配订单数
		$system_send_ordercount = D('Home/LocaltownSnatch')->get_system_send_ordercount_by_member_id( $member_id );

		//累计完成的订单数
		$distribution_info = D('Home/LocaltownSnatch')->get_distribution_info_by_memberid( $member_id );

		//配送费收入
		$commiss_info = D('Home/LocaltownSnatch')->get_commiss_by_orderdistribution_id( $orderdistribution_id );
		//return array('money' => 0, 'dongmoney' => 0, 'getmoney' => 0);

		$total_commiss_money = round( ($commiss_info['money']+$commiss_info['dongmoney']+$commiss_info['getmoney']), 2 );

		$need_data = array();
		$need_data['total_getorder_count'] = $total_getorder_count;
		$need_data['system_send_ordercount'] = $system_send_ordercount;
		$need_data['has_send_count'] = $distribution_info['has_send_count'];
		$need_data['total_commiss_money'] = sprintf("%.2f", $total_commiss_money);

		echo json_encode(  array('code' => 0, 'data' => $need_data) );
		die();

	}

	/**
		获取配送员中心资料

	**/
	public function get_distribution_center_info()
	{
		$_GPC = I('request.');

        $token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }
		$orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

		if( $orderdistribution_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
			die();
		}

		$orderdistribution_info = D('Home/LocaltownSnatch')->get_distribution_info_by_memberid( $member_id );
		//配送费收入
		$commiss_info = D('Home/LocaltownSnatch')->get_commiss_by_orderdistribution_id( $orderdistribution_id );

		$need_data = array();

		$need_data['orderdistribution_info'] = $orderdistribution_info;
		$need_data['can_tixian_money'] = !empty($commiss_info['money']) ? $commiss_info['money'] : 0;

		echo json_encode( array('code' => 0, 'data' => $need_data ) );
		die();
	}

    /**
        获取客户配送佣金基础数据
     **/
    public function get_distribution_commission_info()
    {
        $_GPC = I('request.');

        $token =  $_GPC['token'];

        $weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }
        $orderdistribution_info = M('eaterplanet_ecommerce_orderdistribution')->where( array('member_id' => $member_id) )->find();

        if( empty($orderdistribution_info) )
        {
            echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
            die();
        }

        $store_id = $orderdistribution_info['store_id'];


        //最小提现金额
        $commiss_min_tixian_money = D('Home/Front')->get_config_by_name('distribution_min_tixian_money');
        if($store_id > 0){
            $commiss_min_tixian_money = D('Home/Front')->get_supply_config_by_name('distribution_min_tixian_money',$store_id);
        }
        if( empty($commiss_min_tixian_money) )
        {
            $commiss_min_tixian_money = 0;
        }

        $commiss_tixian_bili = D('Home/Front')->get_config_by_name('distribution_tixian_bili');
        if($store_id > 0){
            $commiss_tixian_bili = D('Home/Front')->get_supply_config_by_name('distribution_tixian_bili',$store_id);
        }
        if( empty($commiss_tixian_bili) )
        {
            $commiss_tixian_bili = 0;
        }

        $member_commiss = M('eaterplanet_ecommerce_orderdistribution_commiss')->where( array('orderdistribution_id' => $orderdistribution_info['id'] ) )->find();

        $member_commiss['commiss_min_tixian_money'] = $commiss_min_tixian_money;//最小提现金额， 0标识不限制

        $member_commiss['commiss_tixian_bili'] = $commiss_tixian_bili;

        $member_commiss['total_commiss_money'] = $member_commiss['money'] + $member_commiss['dongmoney'] + $member_commiss['getmoney'];

        $commiss_tixianway_yuer  = D('Home/Front')->get_config_by_name('distribution_tixianway_yuer');
        if($store_id > 0){
            $commiss_tixianway_yuer = D('Home/Front')->get_supply_config_by_name('distribution_tixianway_yuer',$store_id);
        }
        $commiss_tixianway_weixin  = D('Home/Front')->get_config_by_name('distribution_tixianway_weixin');
        if($store_id > 0){
            $commiss_tixianway_weixin = D('Home/Front')->get_supply_config_by_name('distribution_tixianway_weixin',$store_id);
        }
        $commiss_tixianway_alipay  = D('Home/Front')->get_config_by_name('distribution_tixianway_alipay');
        if($store_id > 0){
            $commiss_tixianway_alipay = D('Home/Front')->get_supply_config_by_name('distribution_tixianway_alipay',$store_id);
        }
        $commiss_tixianway_bank  	= D('Home/Front')->get_config_by_name('distribution_tixianway_bank');
        if($store_id > 0){
            $commiss_tixianway_bank = D('Home/Front')->get_supply_config_by_name('distribution_tixianway_bank',$store_id);
        }

        if($store_id > 0){
            $member_commiss['commiss_tixianway_yuer'] = $commiss_tixianway_yuer == 2 ? 1:0;
            $member_commiss['commiss_tixianway_weixin'] = $commiss_tixianway_weixin == 2 ? 1:0;
            $member_commiss['commiss_tixianway_alipay'] = empty($commiss_tixianway_alipay) ? 1 : ($commiss_tixianway_alipay == 2 ? 1:0);
            $member_commiss['commiss_tixianway_bank'] = $commiss_tixianway_bank == 2 ? 1:0;
        }else{
            $member_commiss['commiss_tixianway_yuer'] = empty($commiss_tixianway_yuer) ? 1 : ($commiss_tixianway_yuer == 2 ? 1:0);
            $member_commiss['commiss_tixianway_weixin'] = empty($commiss_tixianway_weixin) ? 1 : ($commiss_tixianway_weixin == 2 ? 1:0);
            $member_commiss['commiss_tixianway_alipay'] = empty($commiss_tixianway_alipay) ? 1 : ($commiss_tixianway_alipay == 2 ? 1:0);
            $member_commiss['commiss_tixianway_bank'] = empty($commiss_tixianway_bank) ? 1 : ($commiss_tixianway_bank == 2 ? 1:0);
        }



        //上一微信真实姓名
        $last_weixin_realname = "";

        $last_weixin_info = M('eaterplanet_ecommerce_orderdistribution_tixian_order')->where( array('member_id' => $member_id, 'type' => 2 ) )->find();

        if( !empty($last_weixin_info) )
        {
            $last_weixin_realname = $last_weixin_info['bankusername'];
        }

        //上一支付宝账号
        $last_alipay_name = '';
        $last_alipay_account = '';

        $last_alipay_info = M('eaterplanet_ecommerce_orderdistribution_tixian_order')->where( array('member_id' => $member_id, 'type' => 3 ) )->find();

        if( !empty($last_alipay_info) )
        {
            $last_alipay_name = $last_alipay_info['bankusername'];
            $last_alipay_account = $last_alipay_info['bankaccount'];
        }

        //上一银行卡信息
        $last_bank_bankname = '';
        $last_bank_account = '';
        $last_bank_name = '';

        $last_bank_info = M('eaterplanet_ecommerce_orderdistribution_tixian_order')->where( array('member_id' => $member_id, 'type' => 4 ) )->find();

        if( !empty($last_bank_info) )
        {
            $last_bank_bankname = $last_bank_info['bankname'];
            $last_bank_account = $last_bank_info['bankaccount'];
            $last_bank_name = $last_bank_info['bankusername'];
        }

        $member_commiss['last_weixin_realname'] = $last_weixin_realname;
        $member_commiss['last_alipay_name'] = $last_alipay_name;
        $member_commiss['last_alipay_account'] = $last_alipay_account;

        $member_commiss['last_bank_bankname'] = $last_bank_bankname;
        $member_commiss['last_bank_account'] = $last_bank_account;
        $member_commiss['last_bank_name'] = $last_bank_name;

        $commiss_tixian_publish = D('Home/Front')->get_config_by_name('distribution_tixian_publish');
        if($store_id > 0){
            $commiss_tixian_publish = D('Home/Front')->get_supply_config_by_name('distribution_tixian_publish',$store_id);
        }
        $member_commiss['commiss_tixian_publish'] = htmlspecialchars_decode( $commiss_tixian_publish );
        if(empty($member_commiss['money'])){
            $member_commiss['money'] = 0;
        }

        $member_commiss['total_money'] = sprintf('%.2f', $member_commiss['money'] + $member_commiss['dongmoney'] + $member_commiss['getmoney']);

        echo json_encode( array('code' =>0,'data' => $member_commiss) );

        die();

    }

    //begin
    /**
    客户拼团佣金提现 提交接口
     **/
    public function tixian_sub()
    {
        $_GPC = I('request.');

        $token =  $_GPC['token'];

        $weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }

        $orderdistribution_info = M('eaterplanet_ecommerce_orderdistribution')->where( array('member_id' => $member_id) )->find();

        if( empty($orderdistribution_info) )
        {
            echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
            die();
        }

        $store_id = $orderdistribution_info['store_id'];


        $result = array('code' => 1,'msg' => '提现失败');

        $member_commiss = M('eaterplanet_ecommerce_orderdistribution_commiss')->where( array('member_id' => $member_id ) )->find();

        $datas = array();


        $datas['money'] = $_GPC['money'];

        $money = $datas['money'];//I('post.money',0,'floatval');


        $type = $_GPC['type'];// 1余额 2 微信 3 支付宝 4 银行

        $bankname = isset($_GPC['bankname']) ? $_GPC['bankname'] : ''; //银行名称

        $bankaccount = isset($_GPC['bankaccount']) ? $_GPC['bankaccount'] : '';//卡号，支付宝账号 使用该字段

        $bankusername = isset($_GPC['bankusername']) ? $_GPC['bankusername'] : '';//持卡人姓名，微信名称，支付宝名称， 使用该字段

        $commiss_money_limit =  D('Home/Front')->get_config_by_name('distribution_min_tixian_money');
        if($store_id > 0){
            $commiss_money_limit = D('Home/Front')->get_supply_config_by_name('distribution_min_tixian_money',$store_id);
        }

        if(!empty($commiss_money_limit) && $commiss_money_limit >0)
        {

            if($member_commiss['money'] < $commiss_money_limit)

            {

                $result['msg'] = '佣金满'.$commiss_money_limit.'才能提现';

                echo json_encode($result);

                die();

            }

        }

        if($money > 0 && $money <= $member_commiss['money'])

        {

            //判断提现手续费，+ 判断提现金额免审直接到账
            $service_charge = D('Home/Front')->get_config_by_name('distribution_tixian_bili');
            if($store_id > 0){
                $service_charge = D('Home/Front')->get_supply_config_by_name('distribution_tixian_bili',$store_id);
            }

            $data = array();

            $data['member_id'] = $member_id;

            $data['money'] = $money;
            $data['service_charge'] = $service_charge;
            $data['service_charge_money'] = round( ($money * $service_charge) /100 ,2);

            $data['state'] = 0;

            $data['shentime'] = 0;

            $data['type'] = $type;
            $data['bankname'] = $bankname;
            $data['bankaccount'] = $bankaccount;
            $data['bankusername'] = $bankusername;

            $data['addtime'] = time();

            M('eaterplanet_ecommerce_orderdistribution_tixian_order')->add($data);




            $com_arr = array();

            $com_arr['money'] = $member_commiss['money'] - $money;

            $com_arr['dongmoney'] = $member_commiss['dongmoney'] + $money;

            M('eaterplanet_ecommerce_orderdistribution_commiss')->where( array('member_id' => $member_id ) )->setInc('money',-$money);
            M('eaterplanet_ecommerce_orderdistribution_commiss')->where( array('member_id' => $member_id ) )->setInc('dongmoney',$money);



            $result = array('code' => 0,'msg' => '提现成功');

        }

        echo json_encode($result);

        die();

    }


    /**

    提现记录

     **/

    public function tixian_record()
    {
        $_GPC = I('request.');

        $token =  $_GPC['token'];

        $weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }
        $orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

        if( $orderdistribution_id <= 0 )
        {
            echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
            die();
        }

        $per_page = 10;

        $page =  isset($_GPC['page']) ? $_GPC['page']:1;


        $offset = ($page - 1) * $per_page;



        $list = array();

        $list = M('eaterplanet_ecommerce_orderdistribution_tixian_order')->where( array('member_id' =>$member_id ) )->order('addtime desc')->limit($offset,$per_page )->select();


        foreach($list as $key => $val)

        {

            $val['addtime'] = date('Y-m-d H:i', $val['addtime']);

            $list[$key] = $val;

        }



        if( !empty($list) )

        {

            echo json_encode( array('code' =>0, 'data'=>$list) );

            die();

        }else{

            echo json_encode( array('code' => 1) );

            die();

        }

    }

    //end


    /**
		确认抢单
	**/
	public function rob_distribution_order()
	{
		$_GPC = I('request.');

        $token =  $_GPC['token'];
        $order_id =  $_GPC['order_id'];
        $ps_lon = $_GPC['ps_lon'];//配送员经度
        $ps_lat = $_GPC['ps_lat'];//配送员纬度

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }

		$orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

		if( $orderdistribution_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
			die();
		}

        $order_info = M('eaterplanet_ecommerce_order')->where(array('order_id' => $order_id ) )->find();
        $localtown_grabbing_distance = D('Home/Front')->get_config_by_name('localtown_grabbing_distance');
        if($order_info['store_id'] > 0){
            $localtown_grabbing_distance = D('Home/Front')->get_supply_config_by_name('localtown_grabbing_distance',$order_info['store_id']);
        }

        if(!empty($localtown_grabbing_distance) && is_numeric($localtown_grabbing_distance)){
            $distribution_order =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
            // 计算配送员距离
            //$distince = D('Seller/Communityhead')->GetDistance($ps_lon,$ps_lat, $distribution_order['member_lon'], $distribution_order['member_lat']);
            $distince = D('Seller/Communityhead')->GetDistance($ps_lat,$ps_lon, $distribution_order['shop_lon'], $distribution_order['shop_lat']);
            if($distince > $localtown_grabbing_distance){
                $msg = "距离店铺地址还有".$distince."米,不能确认抢单,".$localtown_grabbing_distance."米内,可以确认抢单";
                echo json_encode( array('code' => 3, 'msg' => $msg ) );
                die();
            }
        }


		$res = D('Home/LocaltownDelivery')->distribution_get_order( $orderdistribution_id , $order_id );

		if( !$res )
		{
			echo json_encode( array('code' => 2, 'msg' => '抢单失败') );
			die();
		}else {
			echo json_encode( array('code' => 1 , 'msg' => '抢单成功' ) );
			die();
		}
	}
	/**
		确认取货
	**/
	public function distribution_deliverying_order()
	{
		$_GPC = I('request.');

        $token =  $_GPC['token'];
		$order_id =  $_GPC['order_id'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }

		$orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

		if( $orderdistribution_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
			die();
		}

		$res = D('Home/LocaltownDelivery')->distribution_deliverying_order( $order_id );

		if( !$res )
		{
			echo json_encode( array('code' => 2, 'msg' => '配送失败') );
			die();
		}else {
			echo json_encode( array('code' => 1 , 'msg' => '配送成功' ) );
			die();
		}

	}

	/**
		确认送达
	**/
	public function distribution_arrived_order(  )
	{
		$_GPC = I('request.');

        $token =  $_GPC['token'];
		$order_id =  $_GPC['order_id'];
        $ps_lon = $_GPC['ps_lon'];//配送员经度
        $ps_lat = $_GPC['ps_lat'];//配送员纬度



		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>1,'msg' =>'未登录') );
            die();
        }

		$orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

		if( $orderdistribution_id <= 0 )
		{
			echo json_encode( array('code' => 3, 'msg' => '不是配送员' ) );
			die();
		}
		$order_supplyid = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id) )->find();
		if($order_supplyid['supply_id'] > 0 ){

			$localtown_confirm_delivery_distance = D('Home/Front')->get_supply_config_by_name('localtown_confirm_delivery_distance',$order_supplyid['supply_id']);
			if(!empty($localtown_confirm_delivery_distance) && is_numeric($localtown_confirm_delivery_distance)){
				$distribution_order =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
				// 计算配送员距离
				//$distince = D('Seller/Communityhead')->GetDistance($ps_lon,$ps_lat, $distribution_order['member_lon'], $distribution_order['member_lat']);
				$distince = D('Seller/Communityhead')->GetDistance($ps_lat,$ps_lon, $distribution_order['member_lon'], $distribution_order['member_lat']);
				if($distince > $localtown_confirm_delivery_distance){
					$msg = "距离用户收货地址还有".$distince."米,不能完成订单,".$localtown_confirm_delivery_distance."米内,可以完成订单";
					echo json_encode( array('code' => 3, 'msg' => $msg ) );
					die();
				}
			}


		}else{

			$localtown_confirm_delivery_distance = D('Home/Front')->get_config_by_name('localtown_confirm_delivery_distance');
			if(!empty($localtown_confirm_delivery_distance) && is_numeric($localtown_confirm_delivery_distance)){
				$distribution_order =  M('eaterplanet_ecommerce_orderdistribution_order')->where( array('order_id' => $order_id ) )->find();
				// 计算配送员距离
				//$distince = D('Seller/Communityhead')->GetDistance($ps_lon,$ps_lat, $distribution_order['member_lon'], $distribution_order['member_lat']);
				$distince = D('Seller/Communityhead')->GetDistance($ps_lat,$ps_lon, $distribution_order['member_lon'], $distribution_order['member_lat']);
				if($distince > $localtown_confirm_delivery_distance){
					$msg = "距离用户收货地址还有".$distince."米,不能完成订单,".$localtown_confirm_delivery_distance."米内,可以完成订单";
					echo json_encode( array('code' => 3, 'msg' => $msg ) );
					die();
				}
			}

		}



		$res = D('Home/LocaltownDelivery')->distribution_arrived_order( $order_id , 1);

		if( !$res )
		{
			echo json_encode( array('code' => 2, 'msg' => '配送失败') );
			die();
		}else {
			echo json_encode( array('code' => 1 , 'msg' => '配送成功' ) );
			die();
		}


	}

    /**
     * 修改配送员语音通知状态
     */
    public function change_distribution_notice(){
        $_GPC = I('request.');
        $token =  $_GPC['token'];
        $is_new_notice =  $_GPC['is_new_notice'];

        $weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

        $member_id = $weprogram_token['member_id'];

        if( empty($member_id) )
        {
            echo json_encode( array('code' =>0,'msg' =>'未登录') );
            die();
        }

        $orderdistribution_id = D('Home/LocaltownSnatch')->get_distribution_id_by_member_id($member_id);

        if( $orderdistribution_id <= 0 )
        {
            echo json_encode( array('code' => 0, 'msg' => '不是配送员' ) );
            die();
        }

        $res = M('eaterplanet_ecommerce_orderdistribution')->where( array('member_id' => $member_id) )->save(array('is_new_notice'=>$is_new_notice));
        if($res !== false){
            echo json_encode( array('code' => 1, 'msg' => '设置成功' ) );
            die();
        }else{
            echo json_encode( array('code' => 0, 'msg' => '设置失败' ) );
            die();
        }
    }


}
