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
namespace Home\Model;
use Think\Model;
class OrderModel extends Model{

    /**
     * 抽奖商品订单开奖
     * @param unknown $goods_id
     * @param string $oids
     * @param string $is_auto_open_lottery
     */
    function open_goods_lottery_order($goods_id,$oids='',$is_auto_open_lottery = false)
    {

		$sql_over = "select p.pin_id,p.state from ".C('DB_PREFIX')."pin as p,".C('DB_PREFIX')."pin_order as po,
	           ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og
	               where p.state = 0 and p.pin_id = po.pin_id and po.order_id = o.order_id
	                and o.order_id = og.order_id and og.goods_id  and og.goods_id = {$goods_id}
                    order by p.pin_id asc ";

        $list_over=M()->query($sql_over);


        foreach($list_over as $pin_over)
        {
            M('pin')->where( array('pin_id' => $pin_over['pin_id']) )->save( array('end_time' => time()) );
        }
		//begin_time
		$pin_goods = M('pin_goods')->where( array('goods_id' => $goods_id) )->find();

		$begin_time = $pin_goods['begin_time'];

		M('pin_goods')->where( array('goods_id' => $goods_id) )->save( array('end_time' => time()) );

        $sql = "select p.pin_id from ".C('DB_PREFIX')."pin as p,".C('DB_PREFIX')."pin_order as po,
	           ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og
	               where p.state = 1 and p.pin_id = po.pin_id and po.order_id = o.order_id
	                and o.order_id = og.order_id and og.goods_id  and og.goods_id = {$goods_id} and p.begin_time >= {$begin_time}
                    order by p.pin_id asc ";

        $list=M()->query($sql);
         //begin_time


        foreach($list as $pin)
        {
          M('pin')->where( array('pin_id' => $pin['pin_id']) )->save( array('lottery_state' => 1) );
        }

        $lottery_goods = M('lottery_goods')->where( array('goods_id' => $goods_id) )->find();


        if($is_auto_open_lottery) {
            //自动开奖
            $real_win_quantity = $lottery_goods['real_win_quantity'];
            $win_quantity = $lottery_goods['win_quantity'];
            $auto_jia_order = $win_quantity - $real_win_quantity;

            if($real_win_quantity > 0)
            {
                $sql = "select o.order_id from ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og
                	           where  o.order_id = og.order_id and og.goods_id and o.order_status_id =1 and o.date_added >{$begin_time} and og.goods_id = {$goods_id}
                                group by o.member_id order by o.order_id desc  limit {$win_quantity}";

                $list=M()->query($sql);


                if(!empty($list)) {
                    //有订单
                    $oids_arr = array();
                    foreach($list as $val)
                    {
                        $oids_arr[] = $val['order_id'];
                    }
                    $oids_count = count($oids_arr);
                    if($oids_count < $real_win_quantity)
                    {
                        $real_win_quantity = $oids_count;//实际取出的数量=订单数量
                        $auto_jia_order = $win_quantity - $real_win_quantity;//如果数据库中订单数量小于真实中奖人数
                    }
                    shuffle($oids_arr);

                    $need_order_ids_arr = array_rand($oids_arr, $real_win_quantity);

					$need_order_arr = array();//随机出需要的订单id
                    if($real_win_quantity == 1) {
						$need_order_arr[] = $oids_arr[$need_order_ids_arr];
					} else {
						 foreach($need_order_ids_arr as $vv)
						{
							$need_order_arr[] = $oids_arr[$vv];
						}
					}


                    M('order')->where( array('order_id' => array('in', $need_order_arr )) )->save( array('lottery_win' => 1) );

                    if($auto_jia_order > 0)
                    {
                        //作假人数大于0，那么就开假奖
                        $this->open_jia_lottery($goods_id,$auto_jia_order);
                    }

                }else {
                    //没有订单，全假开奖
                    $this->open_jia_lottery($goods_id,$win_quantity);
                }
            } else {
                //全假开奖
                $this->open_jia_lottery($goods_id,$win_quantity);
            }
        } else {
            //手动开奖
            $win_quantity = $lottery_goods['win_quantity'];

            $ids_arr = explode(',',$oids);

            if(!empty($ids_arr) && !empty($oids)) {
                M('order')->where( array('order_id' => array('in',$oids)) )->save( array('lottery_win' => 1) );
                $auto_jia_order = $win_quantity - count($ids_arr);
            }else {
                $auto_jia_order = $win_quantity;
            }
            if($auto_jia_order > 0) //作假人数大于0，那么就开假奖
                $this->open_jia_lottery($goods_id,$auto_jia_order);
        }
        M('lottery_goods')->where( array('goods_id' => $goods_id) )->save( array('is_open_lottery' => 1) );

    }

    /**
     * 开假奖励
     * @param unknown $goods_id
     */
    function open_jia_lottery($goods_id,$need_count)
    {
        $sql = "select * from ".C('DB_PREFIX')."jiauser order by Rand() desc limit {$need_count}";
        $list=M()->query($sql);
        $order_no = build_order_no($goods_id);
        $mobile_arr = array('31','58','51','52','80','55','32','82','35');

        foreach($list as $key => $val)
        {
            //build_order_no
            $order_no = substr($order_no, 0, strlen($order_no) -4).mt_rand(0,9).mt_rand(1,8).mt_rand(0,9).mt_rand(0,9);
            $val['order_no'] = $order_no;
            if(empty($val['mobile'])) {
                 $val['mobile'] = '1'.$mobile_arr[array_rand($mobile_arr)].'*****'.mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9);
            } else {
                $val['mobile'] = substr($val['mobile'],0,3).'*****'.substr($val['mobile'],-3,3);
            }

            $data = array();
            $data['goods_id'] = $goods_id;
            $data['avatar'] = $val['avatar'];
            $data['uname'] = $val['username'];

            $data['order_sn'] = $val['order_no'];
            $data['mobile'] =  $val['mobile'];
            $data['addtime'] =  time();

           M('jiaorder')->add($data);
        }

    }

	function get_all_address($uid){

		$list=M('address')->where(array('member_id'=>$uid))->order('address_id desc ')->select();

		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

		if(!empty($list)){
			foreach ($list as $k => $v) {
				$list[$k]['id']=$v['address_id'];
				$list[$k]['address_id']=$hashids->encode($v['address_id']);

			}
		}

		return $list;
	}


	function order_info($order_id){
		//订单信息
		$order_sql='select t.title,o.* from '.C('DB_PREFIX').'order o,'.C('DB_PREFIX').'transport t where o.shipping_method=t.id and o.order_id='.$order_id;
		$order=M()->query($order_sql);
		//收货地址
		$address=M('Address')->find($order[0]['address_id']);
		//商品详情
		$goods=M('OrderGoods')->where(array('order_id'=>$order_id))->select();
		//商品选项
		//$option=M('OrderOption')->where(array('order_id'=>$order_id))->select();
		//总计
		$total=M('OrderTotal')->where(array('order_id'=>$order_id))->select();
		//订单历史
		$history=M('OrderHistory')->where(array('order_id'=>$order_id))->select();

		return array(
			'order'=>$order,
			'address'=>$address,
			//'option'=>$option,
			'goods'=>$goods,
			'total'=>$total,
			'history'=>$history
		);
	}


	function show_order_page($member_id){

		$count=M('order')->where(array('member_id'=>$member_id))->count();

		$Page = new \Think\Page($count,C('FRONT_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$show=str_replace("/user/order/p/","/order/", $show);

		$sql='SELECT o.order_num_alias as alias,o.order_id,o.name,o.date_added,o.total,os.name as status FROM '.C('DB_PREFIX').'order o,'.C('DB_PREFIX')
		."order_status os where o.order_status_id=os.order_status_id and o.member_id=".$member_id.' order by o.order_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		if(!empty($list)){
			foreach ($list as $k => $v) {
				$list[$k]['order_id']=$hashids->encode($v['order_id']);
			}
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}

	function addOrder($data) {

		$integral_model = D('Seller/Integral');

	    $order = array();
	    $order['member_id']=$data['member_id'];
	    $order['order_num_alias']=$data['order_num_alias'];
	    $order['name']=$data['name'];

		if( isset($data['from_type']) )
		{
			$order['from_type']=$data['from_type'];
		}


		if($data['delivery'] == 'pickup')
		{
			$order['telephone']=$data['ziti_mobile'];
			$order['shipping_name']=$data['ziti_name'];
		}else{
			$order['telephone']=$data['telephone'];
			$order['shipping_name']=$data['shipping_name'];
		}


	    $order['type']=$data['type'];


	    $order['shipping_address']=$data['shipping_address'];
	    $order['shipping_city_id']=$data['shipping_city_id'];

	    $order['shipping_country_id']=$data['shipping_country_id'];
	    $order['shipping_province_id']=$data['shipping_province_id'];
	    $order['shipping_tel']=$data['shipping_tel'];
	    $order['order_status_id']=C('default_order_status_id');
		$order['voucher_id']=$data['voucher_id'];
		$order['voucher_credit']=$data['voucher_credit'];

	    $order['ip']=get_client_ip();

	    $order['shipping_fare'] = $data['shipping_fare'];

	    $order['ip_region'] = '';
	    if($data['total'] <0)
	    {
	        $data['total'] = 0;
	    }
	    $order['date_added'] =time();
	    $order['total'] =$data['total'];
	    $order['user_agent']=$data['user_agent'];

	    $order['shipping_method']=0;//快递id
	    $order['delivery']=$data['delivery'];
	//$data['delivery']

	    $order['payment_code']=$data['payment_method'];

	    $order['address_id']=$data['address_id'];
	    $order['comment']=$data['comment'];


	    $order['store_id'] = $data['store_id'];

	    $order_id=M('Order')->add($order);

	    //Model $data['member_id']
	    $goods_model = D('Home/Goods');

	    $member_info = M('member')->where( array('member_id' => $data['member_id']) )->find();

	    $is_pin = 0;
	    $pin_id = 0;

	    $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

		$share_model = D('Seller/Fissionsharing');

	    $kucun_method  = C('kucun_method');
	    $kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);
	    $free_tuan = 0;
	    if(isset($data['goodss'])){
	        foreach ($data['goodss'] as $goods) {
	            //$goods_id=$hashids->decode($goods['goods_id']);

				$goods_id = $goods['goods_id'];

	            $pin_id = $goods['pin_id'];

	            $commiss_one_money = 0;
	            $goods_info = M('goods')->field('points,commiss_fen_one_disc,commiss_fen_two_disc,commiss_fen_three_disc,commiss_three_dan_disc,commiss_two_dan_disc,commiss_one_dan_disc,store_id,type,model,image')->where( array('goods_id' => $goods_id) )->find();
				//points buy_send_score type
				if( !empty($goods_info['points']) && $goods_info['points'] > 0 && $goods_info['type'] != 'integral')
				{
					//$goods['quantity']
					//integral_model
					$score = $goods_info['points'] * $goods['quantity'];
					$integral_model->charge_member_score( $data['member_id'] , $score,'in', 'goodsbuy', $order_id);

				}else if( C('buy_send_score') > 0 && $goods_info['type'] != 'integral')
				{
					$score = C('buy_send_score') * $goods['quantity'];
					$integral_model->charge_member_score( $data['member_id'] , $score,'in', 'goodsbuy', $order_id);
				}

	            //'is_pin' => $is_pin,
	            $is_pin = $goods['is_pin'];

	            //判断是否拼团开始
				$commiss_one_money = 0;
				$commiss_two_money = 0;
				$commiss_three_money = 0;

				$commiss_fen_one_money = 0;
				$commiss_fen_two_money = 0;
				$commiss_fen_three_money = 0;
				//<?php if( C('opencommiss') == 1){
				//C('commiss_level_num') >= 1
	            if($is_pin == 1)
	            {
				 	$pin_goods = M('pin_goods')->field('commiss_one_pin_disc,commiss_two_pin_disc,commiss_three_pin_disc')->where( array('goods_id' => $goods_id) )->find();

					//$goods['total']
					if(C('opencommiss') == 1)
					{
						if(C('commiss_level_num') >= 1)
						{
							$commiss_one_money = round( ($pin_goods['commiss_one_pin_disc'] * $goods['total'])/100 , 2);
						}
						if(C('commiss_level_num') >= 2)
						{
							$commiss_two_money = round( ($pin_goods['commiss_two_pin_disc'] * $goods['total'])/100 , 2);
						}
						if(C('commiss_level_num') >= 3)
						{
							$commiss_three_money = round( ($pin_goods['commiss_three_pin_disc'] * $goods['total'])/100 , 2);
						}
					}

					if(C('is_open_fissionsharing') == 1)
					{
						if(C('fissionsharing_level') >= 1)
						{
							$commiss_fen_one_money = round( ($goods_info['commiss_fen_one_disc'] * $goods['total'])/100 , 2);
						}
						if(C('fissionsharing_level') >= 2)
						{
							$commiss_fen_two_money = round( ($goods_info['commiss_fen_two_disc'] * $goods['total'])/100 , 2);
						}
						if(C('fissionsharing_level') >= 3)
						{
							$commiss_fen_three_money = round( ($goods_info['commiss_fen_three_disc'] * $goods['total'])/100 , 2);
						}
					}

	                $goods_info['type'] = 'pintuan';
	                $pin_model =  D('Home/Pin');
	                $pin_id = $pin_model->checkPinState($goods['pin_id']);
					//addOrder
					$is_pin_over = $pin_model->getNowPinState($goods['pin_id']);
					if($is_pin_over == 1 || $is_pin_over == 2)
					{
						$pin_id = 0;
					}
	                if($pin_id ==0) {
	                    //新开团
	                    $pin_id = $pin_model->openNewTuan($order_id,$goods_id,$data['member_id']);
	                    $is_new_tuan = true;
	                }
	                //插入拼团订单
	                $pin_model->insertTuanOrder($pin_id,$order_id);
	            }else{
					if(C('opencommiss') == 1)
					{
						if(C('commiss_level_num') >= 1)
						{
							$commiss_one_money = round( ($goods_info['commiss_one_dan_disc'] * $goods['total'])/100 , 2);
						}
						if(C('commiss_level_num') >= 2)
						{
							$commiss_two_money = round( ($goods_info['commiss_two_dan_disc'] * $goods['total'])/100 , 2);
						}
						if(C('commiss_level_num') >= 3)
						{
							$commiss_three_money = round( ($goods_info['commiss_three_dan_disc'] * $goods['total'])/100 , 2);
						}
					}

					if(C('is_open_fissionsharing') == 1)
					{
						if(C('fissionsharing_level') >= 1)
						{
							$commiss_fen_one_money = round( ($goods_info['commiss_fen_one_disc'] * $goods['total'])/100 , 2);
						}
						if(C('fissionsharing_level') >= 2)
						{
							$commiss_fen_two_money = round( ($goods_info['commiss_fen_two_disc'] * $goods['total'])/100 , 2);
						}
						if(C('fissionsharing_level') >= 3)
						{
							$commiss_fen_three_money = round( ($goods_info['commiss_fen_three_disc'] * $goods['total'])/100 , 2);
						}
					}
				}
				//var_dump($goods_info,$goods['total']);die();

				$goods['member_disc'] = isset($goods['member_disc']) ? $goods['member_disc'] : 100;

	            //判断是否拼团结束
	            $type = ($is_pin == 1) ? 'pintuan': 'normal';
				//header_disc
	            $this->execute("INSERT INTO ".C('DB_PREFIX')."order_goods SET order_id = '" .$order_id
	                ."',goods_id='".$goods_id."'"
	                .",store_id='".$goods_info['store_id']."'"
	                .",name='".addslashes($goods['name'])."'"
	                .",model='".$goods['model']."'"
					.",commiss_one_money='".$commiss_one_money."'"
					.",commiss_two_money='".$commiss_two_money."'"
					.",commiss_three_money='".$commiss_three_money."'"
					.",commiss_fen_one_money='".$commiss_fen_one_money."'"
					.",commiss_fen_two_money='".$commiss_fen_two_money."'"
					.",commiss_fen_three_money='".$commiss_fen_three_money."'"
	                .",head_disc='".$goods['header_disc']."'"
					.",member_disc='".$goods['member_disc']."'"
	                .",level_name='".$goods['level_name']."'"
	                .",is_pin='".$is_pin."'"
	                .",goods_images='".$goods_info['image']."'"
	                .",goods_type='".$type."'"
	                .",shipping_fare='".$goods['shipping_fare']."'"
	                .",quantity='".(int)$goods['quantity']."'"
	                .",price='".(float)$goods['price']."'"
	                .",rela_goodsoption_valueid='".$goods['option']."'"
	                .",comment='".$goods['comment']."'"
	                .",total='".(float)$goods['total']."'"
	            );


	            $order_goods_id=$this->getLastInsID();

				//检测是否需要将订单放入分佣里面
				if(C('is_open_fissionsharing') == 1)
				{
					$share_model->add_sharing_order($order_id,$goods_id,$order_goods_id,$data['member_id'],$goods_info['store_id'] );
				}

	            if(!empty($goods['option']))
	            {
	                $options_arr = array();
	                $option_value_id_arr = explode('_',$goods['option']);


	                foreach($option_value_id_arr as $id_val)
	                {
	                    $goods_option_value = M('goods_option_value')->where( array('option_value_id' => $id_val,'goods_id' =>$goods_id) )->find();

	                    $options_arr[$goods_option_value['goods_option_id']] = $goods_option_value['goods_option_value_id'];

	                    $goods_option = M('goods_option')->where( array('goods_option_id' =>$goods_option_value['goods_option_id']) )->find();

	                    $option_value =  M('option_value')->where( array('option_value_id' =>$goods_option_value['option_value_id']) )->find();

	                    $this->execute("INSERT INTO ".C('DB_PREFIX')."order_option SET order_id = '" .$order_id
	                        ."',order_goods_id='".$order_goods_id."'"
	                        .",goods_option_id='".(int)$goods_option_value['goods_option_id']."'"
	                        .",goods_option_value_id='".(int)$goods_option_value['goods_option_value_id']."'"
	                        .",name='".$goods_option['option_name']."'"
	                        .",value='".$option_value['value_name']."'"
	                    );

	                }
	            }


	            if($kucun_method == 0)
	            {
	                $goods_model->del_goods_mult_option_quantity($order_id,$goods['option'],$goods_id,$goods['quantity'],1);
	            }

	        }
	    }

		//type normal pintuan is_pin
		$order_type = $is_pin == 1 ? 'pintuan': 'normal';

		M('order')->where( array('order_id' => $order_id) )->save( array('is_pin' => $is_pin, 'order_type' =>$order_type) );


	    if(isset($data['totals'])){
	        foreach ($data['totals'] as $total) {
	            $this->execute("INSERT INTO ".C('DB_PREFIX')."order_total SET order_id = '" .$order_id
	                ."',code='".$total['code']."'"
	                .",title='".$total['title']."'"
	                .",text='".$total['text']."'"
	                .",value='".(float)$total['value']."'");
	        }
	    }

	    $oh = array();
	    $oh['order_id']=$order_id;
	    $oh['order_status_id']=C('default_order_status_id');
	    $oh['comment']='创建订单';
	    $oh['date_added']=time();
	    $oh_id=M('OrderHistory')->add($oh);

	    //storage_user_action(session('user_auth.uid'),session('user_auth.username'),C('FRONTEND_USER'),'下了订单 '.$data['order_num_alias'].' 未支付');

	    return $order_id;
	}


	function addOrder2($data) {

		$order['member_id']=$data['member_id'];
		$order['order_num_alias']=$data['order_num_alias'];
		$order['name']=$data['name'];
		$order['email']=$data['email'];
		$order['telephone']=$data['telephone'];
		$order['shipping_name']=$data['shipping_name'];
		$order['shipping_address']=$data['shipping_address'];
		$order['shipping_city_id']=$data['shipping_city_id'];

		$order['shipping_country_id']=$data['shipping_country_id'];
		$order['shipping_province_id']=$data['shipping_province_id'];
		$order['shipping_tel']=$data['shipping_tel'];
		$order['comment']=$data['comment'];
		$order['order_status_id']=C('default_order_status_id');
		$order['ip']=get_client_ip();
		$order['voucher_id']=$data['voucher_id'];
		$order['voucher_credit']=$data['voucher_credit'];
		$order['shipping_fare'] = $data['shipping_fare'];

		$order['ip_region'] = '';
		if($data['total'] <0)
		{
			$data['total'] = 0;
		}
		$order['date_added'] =time();
		$order['total'] =$data['total'];
		$order['user_agent']=$data['user_agent'];

		$order['shipping_method']=$data['shipping_method'];
		$order['delivery']=$data['delivery'];

		$order['payment_code']=$data['payment_method'];

		$order['address_id']=$data['address_id'];

		$order_id=M('Order')->add($order);

		//Model $data['member_id']
		$goods_model = D('Home/Goods');

		$member_info = M('member')->where( array('member_id' => $data['member_id']) )->find();

		$is_pin = 0;
		$pin_id = 0;

		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

		$kucun_method  = C('kucun_method');
		$kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);
		$free_tuan = 0;
		if(isset($data['goodss'])){
			foreach ($data['goodss'] as $goods) {
				$goods_id=$hashids->decode($goods['goods_id']);

				$pin_id = $goods['pin_id'];

				$commiss_one_money = 0;
				$goods_info = M('goods')->field('type,is_free_in,commiss_one_money,commiss_one_pin_disc,commiss_one_dan_disc,pinprice,danprice')->where( array('goods_id' => $goods_id[0]) )->find();

				$is_new_tuan = false;


				if($goods['pin_type'] == 'pin'){
				   $is_pin = 1;
				  $pin_model =  D('Home/Pin');
				  $pin_id = $pin_model->checkPinState($goods['pin_id']);

				  if($pin_id ==0) {
				      //新开团
				      $pin_id = $pin_model->openNewTuan($order_id,$goods_id,$data['member_id']);
					  $is_new_tuan = true;
				  }
				  //插入拼团订单
				  $pin_model->insertTuanOrder($pin_id,$order_id);
				  //is_header_disc 记录团长折扣 header_disc  commiss_one_money

					$commiss_one_money = round( ($goods_info['pinprice'] * $goods_info['commiss_one_pin_disc'])/100,2);
				} else {
				    $commiss_one_money = round( ($goods_info['danprice'] * $goods_info['commiss_one_dan_disc'])/100,2);
				}


				if($is_new_tuan && $goods_info['is_free_in'] == 1)
				{
					//团长可以免单开团，需要判断是否拥有免单券
					//$data['member_id']
					$voucher_free_info = M('voucher_free')->where( array('user_id' =>$data['member_id'],'state'=>0) )->find();
					if(!empty($voucher_free_info))
					{
						//可以免单开团
						M('voucher_free')->where( array('id' =>$voucher_free_info['id']) )->save( array('state' => 1) );
						$free_tuan = 1;
					}

				}

				if($free_tuan == 1)
				{
					$goods['total'] = 0;
				}


				$this->execute("INSERT INTO ".C('DB_PREFIX')."order_goods SET order_id = '" .$order_id
				."',goods_id='".$goods_id[0]."'"
				 .",store_id='".$goods['store_id']."'"
				.",name='".addslashes($goods['name'])."'"
				.",model='".$goods['model']."'"
				.",is_pin='".$is_pin."'"
				.",pin_id='".$pin_id."'"
				.",free_tuan='".$free_tuan."'"
				.",head_disc='".$goods['header_disc']."'"
				.",commiss_one_money='".$commiss_one_money."'"
				.",quantity='".(int)$goods['quantity']."'"
				.",price='".(float)$goods['price']."'"
				.",total='".(float)$goods['total']."'"
				);

				if($goods_info['type'] =='haitao')
				{
					$og_haitao_data = array();
					$og_haitao_data['order_id'] = $order_id;
					$og_haitao_data['real_name'] = $member_info['id_cardreal_name'];
					$og_haitao_data['id_card'] = $member_info['id_card'];
					$og_haitao_data['add_time'] = time();
					M('order_goods_haitao')->add($og_haitao_data);
				}

				$order_goods_id=$this->getLastInsID();

				foreach ($goods['option'] as $option) {
					$this->execute("INSERT INTO ".C('DB_PREFIX')."order_option SET order_id = '" .$order_id
					."',order_goods_id='".$order_goods_id."'"
					.",goods_option_id='".(int)$option['goods_option_id']."'"
					.",goods_option_value_id='".(int)$option['goods_option_value_id']."'"
					.",name='".$option['name']."'"
					.",value='".$option['value']."'"
					.",type='".$option['type']."'"
					);
				}

				if($kucun_method == 0)
				{

					$goods_model->del_goods_mult_option_quantity($order_id,$goods['quantity'],1);

				}

			}
		}
		//pintuan
		M('order')->where( array('order_id' => $order_id) )->save( array('is_pin' => $is_pin, 'pin_id' =>$pin_id) );

		//免单开团
		if($free_tuan == 1)
		{
			M('order')->where( array('order_id' => $order_id) )->save( array('total' => 0) );
		}


		if(isset($data['totals'])){
			foreach ($data['totals'] as $total) {
				$this->execute("INSERT INTO ".C('DB_PREFIX')."order_total SET order_id = '" .$order_id
				."',code='".$total['code']."'"
				.",title='".$total['title']."'"
				.",text='".$total['text']."'"
				.",value='".(float)$total['value']."'");
			}
		}

		$oh['order_id']=$order_id;
		$oh['order_status_id']=C('default_order_status_id');
		$oh['comment']=$data['comment'];
		$oh['date_added']=time();
		$oh_id=M('OrderHistory')->add($oh);

		//storage_user_action(session('user_auth.uid'),session('user_auth.username'),C('FRONTEND_USER'),'下了订单 '.$data['order_num_alias'].' 未支付');

		return $order_id;
	}

	/**
	 * 通用设置订单状态
	 * 一般拼团成功时使用
	 */
	function change_order_status($order_id,$order_status_id)
	{
	    //设置订单状态
	    $this->execute("UPDATE " . C('DB_PREFIX') . "order SET 	order_status_id = {$order_status_id} where order_id=".$order_id);

	}

	/**
	 * 确认收货
	 * @param unknown $order_id
	 */
	function receive_order($order_id)
	{

	    //设置订单状态
	    $this->execute("UPDATE " . C('DB_PREFIX') . "order SET 	order_status_id = 6 where order_id=".$order_id);

		$order_history = array();
		$order_history['order_id'] = $order_id;
		$order_history['order_status_id'] = 6;
		$order_history['notify'] = 0;
		$order_history['comment'] = '用户确认收货';
		$order_history['date_added']=time();
		M('order_history')->add($order_history);

		$fenxiao_model = D('Home/Fenxiao');
		$fenxiao_model->send_order_commiss_money($order_id);

		$share_model = D('Seller/Fissionsharing');
		$share_model->send_order_commiss_money_do( $order_id);

		$integral_model = D('Seller/Integral');
		$integral_model->send_order_score_dr($order_id);
	}

	function cancel_order($order_id){
		//设置订单状态
		$this->execute("UPDATE " . C('DB_PREFIX') . "order SET 	order_status_id = 5 where order_id=".$order_id);
		//写人订单历史
		$this->execute("INSERT INTO " . C('DB_PREFIX') . 'order_history SET order_status_id = 5,order_id='.$order_id.",comment='用户取消了订单',date_added=".time());
		//订单商品
		$goods=M('order_goods')->where(array('order_id'=>$order_id))->select();

		$goods_model = D('Home/Goods');

		$kucun_method  = C('kucun_method');
		$kucun_method  = empty($kucun_method) ? 0 : intval($kucun_method);

		if(isset($goods) && $kucun_method == 0 ){

			foreach ($goods as $key => $value) {
				//$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET quantity = (quantity + " . (int)$value['quantity'] . ") WHERE goods_id = '" . $value['goods_id'] . "' ");

				//del_goods_mult_option_quantity($order_id,$option,$goods_id,$quantity,$type='1')
				$goods_model->del_goods_mult_option_quantity($order_id,$goods['rela_goodsoption_valueid'],$value['goods_id'],$value['quantity'],2);
				//$goods_model->del_goods_mult_option_quantity($order_id,$value['quantity'],2);
				//销量回退
				//$this->execute("UPDATE " . C('DB_PREFIX') . "goods SET seller_count = (seller_count - " . (int)$value['quantity'] . ") WHERE goods_id = '" . $value['goods_id'] . "' ");
			}
		}

	}
	/**
	 * 订单自动配送操作
	 * @param unknown $order
	 */
	public function order_auto_delivery($order){
	    if($order['delivery'] == 'localtown_delivery'){//同城配送订单
	        $is_localtown_auto_delivery = D('Home/Front')->get_config_by_name('is_localtown_auto_delivery');
	        if($is_localtown_auto_delivery == 1){//订单自动确认配送
	            //同城配送
	            D('Seller/Order')->do_send_localtown($order['order_id'],'后台自动确认发货，开始配送货物');
	        }
	    }else if($order['delivery'] == 'tuanz_send'){//团长配送订单
	        $is_communityhead_auto_delivery = D('Home/Front')->get_config_by_name('is_communityhead_auto_delivery');
	        if($is_communityhead_auto_delivery == 1){//团长订单自动确认配送
	            D('Seller/Order')->do_send_tuanz($order['order_id'],'后台自动确认发货，开始配送货物');
	            $is_communityhead_auto_service = D('Home/Front')->get_config_by_name('is_communityhead_auto_service');
	            if($is_communityhead_auto_service == 1){//团长订单自动确认送达团长
	                D('Seller/Order')->do_tuanz_over($order['order_id'],'后台自动确认送达团长');
	                D('Home/Frontorder')->send_order_operate($order['order_id']);
	            }
	        }
	    }else if($order['delivery'] == 'pickup'){//到点自提订单
	        $is_ziti_auto_delivery = D('Home/Front')->get_config_by_name('is_ziti_auto_delivery');
	        if($is_ziti_auto_delivery == 1){//到店自提订单自动确认配送
	            D('Seller/Order')->do_send_tuanz($order['order_id'],'后台自动确认发货，开始配送货物');
	            $is_ziti_auto_service = D('Home/Front')->get_config_by_name('is_ziti_auto_service');
	            if($is_ziti_auto_service == 1){//到店自提订单自动确认送达团长
	                D('Seller/Order')->do_tuanz_over($order['order_id'],'后台自动确认送达团长');
	                D('Home/Frontorder')->send_order_operate($order['order_id']);
	            }
	        }
	    }
	}
}
