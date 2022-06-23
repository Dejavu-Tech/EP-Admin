<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://www.eaterplanet.com/
 * @copyright Copyright (c) 2019-2022 Dejavu.Tech.
 * @license   https://www.eaterplanet.com/license.html License
 * ==========================================================================
 *拼团模块
 * @author    Albert.Z
 *
 */
namespace Home\Controller;

class SigninrewardController extends CommonController {

	 protected function _initialize()
    {

    	parent::_initialize();

    }

	public function get_signinreward_baseinfo()
	{
		$_GPC = I('request.');

		$token =  $_GPC['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();



		//以下是数据
		$isopen_signinreward = D('Home/Front')->get_config_by_name('isopen_signinreward');

		$isopen_signinreward = empty($isopen_signinreward) ? 0 : $isopen_signinreward;

		$signinreward_day1_score = D('Home/Front')->get_config_by_name('signinreward_day1_score');
		$signinreward_day1_score = empty($signinreward_day1_score) ? 0 : $signinreward_day1_score;

		$signinreward_day2_score = D('Home/Front')->get_config_by_name('signinreward_day2_score');
		$signinreward_day2_score = empty($signinreward_day2_score) ? 0 : $signinreward_day2_score;

		$signinreward_day3_score = D('Home/Front')->get_config_by_name('signinreward_day3_score');
		$signinreward_day3_score = empty($signinreward_day3_score) ? 0 : $signinreward_day3_score;

		$signinreward_day4_score = D('Home/Front')->get_config_by_name('signinreward_day4_score');
		$signinreward_day4_score = empty($signinreward_day4_score) ? 0 : $signinreward_day4_score;

		$signinreward_day5_score = D('Home/Front')->get_config_by_name('signinreward_day5_score');
		$signinreward_day5_score = empty($signinreward_day5_score) ? 0 : $signinreward_day5_score;

		$signinreward_day6_score = D('Home/Front')->get_config_by_name('signinreward_day6_score');
		$signinreward_day6_score = empty($signinreward_day6_score) ? 0 : $signinreward_day6_score;

		$signinreward_day7_score = D('Home/Front')->get_config_by_name('signinreward_day7_score');
		$signinreward_day7_score = empty($signinreward_day7_score) ? 0 : $signinreward_day7_score;

		$modify_signinreward_logo = D('Home/Front')->get_config_by_name('modify_signinreward_logo');

		if( !empty($modify_signinreward_logo) )
		{
			$modify_signinreward_logo = tomedia($modify_signinreward_logo);
		}

		$signinreward_share_title = D('Home/Front')->get_config_by_name('signinreward_share_title');
		$signinreward_share_title = empty($signinreward_share_title) ? '': $signinreward_share_title;

		$signinreward_share_image = D('Home/Front')->get_config_by_name('signinreward_share_image');
		if( !empty($signinreward_share_image) )
		{
			$signinreward_share_image = tomedia($signinreward_share_image);
		}

		$signinreward_pagenotice = D('Home/Front')->get_config_by_name('signinreward_pagenotice');
		$signinreward_pagenotice = empty($signinreward_pagenotice) ? '': htmlspecialchars_decode($signinreward_pagenotice);

		$result = array();
		$result['isopen_signinreward'] = $isopen_signinreward;
		$result['signinreward_day1_score'] = $signinreward_day1_score;
		$result['signinreward_day2_score'] = $signinreward_day2_score;
		$result['signinreward_day3_score'] = $signinreward_day3_score;
		$result['signinreward_day4_score'] = $signinreward_day4_score;
		$result['signinreward_day5_score'] = $signinreward_day5_score;
		$result['signinreward_day6_score'] = $signinreward_day6_score;
		$result['signinreward_day7_score'] = $signinreward_day7_score;
		$result['modify_signinreward_logo'] = $modify_signinreward_logo;
		$result['signinreward_share_title'] = $signinreward_share_title;
		$result['signinreward_share_image'] = $signinreward_share_image;
		$result['signinreward_pagenotice'] = $signinreward_pagenotice;

		//客户积分
		$score = $member_info['score'];
		//今日是否签到了。昨天是否连续签到。

		$today_signintime = strtotime( date('Y-m-d'). ' 00:00:00' );

		$today_signin_info = M('eaterplanet_ecommerce_signinreward_record')->where( array('member_id' =>$member_id , 'signin_time' => $today_signintime  ) )->find();


		$today_is_signin = 0;//今日是否签到过
		$has_continuity_day = 0;//已经连续签到几天
		$show_day_arr = array();

		if( !empty($today_signin_info)  )
		{
			$today_is_signin = 1;
			if( $today_signin_info['continuity_day'] > 0 )
			{
				$continuity_day = $today_signin_info['continuity_day'];
				$has_continuity_day = $continuity_day;

				for($i =1; $i <=$continuity_day;$i++ )
				{
					$tmp = array();
					$datetime =  $today_signintime - ($continuity_day - $i) * 86400;
					$tmp['is_signin'] = 1;
					$tmp['is_today'] = $datetime == $today_signintime ? 1 : 0;
					$tmp['date'] =  date('m月d日', $datetime);

					$show_day_arr[] = $tmp;
				}
				$del_day = 7 - $continuity_day;
				if( $del_day > 0 )
				{
					for($i =1; $i <=$del_day;$i++ )
					{
						$tmp = array();
						$datetime =  $today_signintime + ($i) * 86400;
						$tmp['is_signin'] = 0;
						$tmp['is_today'] = 0;
						$tmp['date'] =  date('m月d日', $datetime);

						$show_day_arr[] = $tmp;
					}
				}
			}

		}else{
			//今日未签到
			$yes_signintime = strtotime( date('Y-m-d'). ' 00:00:00' ) - 86400;

			$yes_signin_info = M('eaterplanet_ecommerce_signinreward_record')->where( array('member_id' =>$member_id , 'signin_time' => $yes_signintime  ) )->find();


			if( !empty($yes_signin_info) )
			{
				//昨日有签到
				$continuity_day = $yes_signin_info['continuity_day'];
				$has_continuity_day = $continuity_day;

				for($i =1; $i <=$continuity_day;$i++ )
				{
					$tmp = array();
					$datetime =  $yes_signintime - ($continuity_day - $i) * 86400;
					$tmp['is_signin'] = 1;
					$tmp['is_today'] = 0;
					$tmp['date'] =  date('m月d日', $datetime);

					$show_day_arr[] = $tmp;
				}

				$del_day = 7 - $continuity_day;
				if( $del_day > 0 )
				{
					for($i =1; $i <=$del_day;$i++ )
					{
						$tmp = array();
						$datetime =  $yes_signintime + ($i) * 86400;
						$tmp['is_signin'] = 0;
						$tmp['is_today'] = $datetime == $today_signintime ? 1 : 0;
						$tmp['date'] =  date('m月d日', $datetime);

						$show_day_arr[] = $tmp;
					}
				}

			}else{
				//昨日未签到
				$has_continuity_day = 0;
				for($i =1; $i <=7;$i++ )
				{
					$tmp = array();
					$datetime =  $yes_signintime + ($i) * 86400;
					$tmp['is_signin'] = 0;
					$tmp['is_today'] = $datetime == $today_signintime ? 1 : 0;
					$tmp['date'] =  date('m月d日', $datetime);

					$show_day_arr[] = $tmp;
				}

			}

		}


		$result['score'] = $score;
		$result['today_is_signin'] = $today_is_signin;
		$result['has_continuity_day'] = $has_continuity_day;
		$result['show_day_arr'] = $show_day_arr;

		echo json_encode( array('code' => 0, 'data' => $result) );
		die();

	}

	public function sub_signin()
	{
		$gpc = I('request.');

		$token =  $gpc['token'];


		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}

		$member_id = $weprogram_token['member_id'];

		$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();


		$isopen_signinreward = D('Home/Front')->get_config_by_name('isopen_signinreward');

		$isopen_signinreward = empty($isopen_signinreward) ? 0 : $isopen_signinreward;

		if( $isopen_signinreward != 1 )
		{
			echo json_encode( array('code' => 1,'msg' => '签到奖励功能未开启') );
			die();
		}

		$signinreward_day1_score = D('Home/Front')->get_config_by_name('signinreward_day1_score');
		$signinreward_day1_score = empty($signinreward_day1_score) ? 0 : $signinreward_day1_score;

		$signinreward_day2_score = D('Home/Front')->get_config_by_name('signinreward_day2_score');
		$signinreward_day2_score = empty($signinreward_day2_score) ? 0 : $signinreward_day2_score;

		$signinreward_day3_score = D('Home/Front')->get_config_by_name('signinreward_day3_score');
		$signinreward_day3_score = empty($signinreward_day3_score) ? 0 : $signinreward_day3_score;

		$signinreward_day4_score = D('Home/Front')->get_config_by_name('signinreward_day4_score');
		$signinreward_day4_score = empty($signinreward_day4_score) ? 0 : $signinreward_day4_score;

		$signinreward_day5_score = D('Home/Front')->get_config_by_name('signinreward_day5_score');
		$signinreward_day5_score = empty($signinreward_day5_score) ? 0 : $signinreward_day5_score;

		$signinreward_day6_score = D('Home/Front')->get_config_by_name('signinreward_day6_score');
		$signinreward_day6_score = empty($signinreward_day6_score) ? 0 : $signinreward_day6_score;

		$signinreward_day7_score = D('Home/Front')->get_config_by_name('signinreward_day7_score');
		$signinreward_day7_score = empty($signinreward_day7_score) ? 0 : $signinreward_day7_score;


		$result = array();
		$result['signinreward_day1_score'] = $signinreward_day1_score;
		$result['signinreward_day2_score'] = $signinreward_day2_score;
		$result['signinreward_day3_score'] = $signinreward_day3_score;
		$result['signinreward_day4_score'] = $signinreward_day4_score;
		$result['signinreward_day5_score'] = $signinreward_day5_score;
		$result['signinreward_day6_score'] = $signinreward_day6_score;
		$result['signinreward_day7_score'] = $signinreward_day7_score;


		$today_signintime = strtotime( date('Y-m-d'). ' 00:00:00' );

		$today_signin_info = M('eaterplanet_ecommerce_signinreward_record')->where( array('member_id' =>$member_id, 'signin_time' => $today_signintime ) )->find();


		if( !empty($today_signin_info) )
		{
			echo json_encode( array('code' => 1,'msg' => '今天已经签到过了') );
			die();
		}else{

			//查询昨天是否已经签到了
			$yes_signintime = strtotime( date('Y-m-d'). ' 00:00:00' ) - 86400;


			$yes_signin_info = M('eaterplanet_ecommerce_signinreward_record')->where( array('member_id' =>$member_id, 'signin_time' => $yes_signintime ) )->find();


			$has_qian = 1;

			if( !empty($yes_signin_info) )
			{
				$has_qian = $yes_signin_info['continuity_day'] +1;
				$has_qian = $has_qian > 7 ? 7 : $has_qian;
			}

			$get_score = $result["signinreward_day{$has_qian}_score"];

			$ins_data = array();

			$ins_data['member_id'] = $member_id;
			$ins_data['continuity_day'] = $has_qian;
			$ins_data['reward_socre'] = $get_score;
			$ins_data['signin_time'] = $today_signintime;
			$ins_data['addtime'] = time();



			M('eaterplanet_ecommerce_signinreward_record')->add($ins_data);

			D('Admin/Member')->sendMemberPointChange($member_id,$get_score, 0 , '连续签到'.$has_qian.'天赠送'.$get_score.'积分','signin_send');


			$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

			$score = $member_info['score'];

			echo json_encode( array('code' =>0, 'score' => $score, 'continuity_day' => $has_qian,'reward_socre' => $get_score ) );
			die();
		}


	}


	public function load_sign_goodslist()
	{
		$_GPC = I('request.');

		$pageNum = isset($_GPC['pageNum']) ? $_GPC['pageNum'] : 1;
		$is_random = 0;
		$per_page = isset($_GPC['per_page']) ? $_GPC['per_page'] : 10;

		$cate_info = '';
		$gid = 0;

		$offset = ($pageNum - 1) * $per_page;
		$limit = "{$offset},{$per_page}";

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();


		$is_member_level_buy = 0;
		$is_vip_card_member = 0;
		$is_open_vipcard_buy = 0;

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
		//	echo json_encode( array('code' => 2) );
		//	die();
		}else{
			$member_id = $weprogram_token['member_id'];

		}

		//整点秒杀begin
		$is_seckill = 0;
		$seckill_time = 0;
		//整点秒杀end


	    $now_time = time();
		$where = " g.grounding =1 and g.is_seckill =0 and  g.type ='integral'   ";
		$goods_ids_nohead_arr = M('eaterplanet_ecommerce_goods')->field('id')->where( array('type' => 'integral' ) )->select();

		$ids_arr = array();
		if( !empty($goods_ids_nohead_arr) )
		{
			foreach($goods_ids_nohead_arr as $val){
				$ids_arr[] = $val['id'];
			}
		}

		$ids_str = implode(',',$ids_arr);

		if( !empty($ids_str) )
		{
			$where .= " and g.id in ({$ids_str})";
		} else{
			$where .= " and 0 ";
		}

		$where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
		$where .= " and gc.is_new_buy=0 and gc.is_spike_buy = 0 ";

		$community_goods = D('Home/Pingoods')->get_new_community_index_goods('','','g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video ', $where,$offset,$per_page);

		if( !empty($community_goods) )
		{
			$full_money = 0;
			$full_reducemoney = 0;

			$is_open_vipcard_buy = 0;

			$is_open_vipcard_buy = 0;

			$is_open_fullreduction = 0;


			$cart= D('Home/Car');

			$list = array();
			$copy_text_arr = array();
			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$tmp_data['spuName'] = $val['goodsname'];

				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['is_take_vipcard'] = $val['is_take_vipcard'];
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
				$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);


				$price_arr['price'] = round($price_arr['price'],0);
				$price_arr['card_price'] = round($price_arr['card_price'],0);
				$price_arr['levelprice'] = round($price_arr['levelprice'],0);

				$price = $price_arr['price'];

				if( $pageNum == 1 )
				{
					$copy_text_arr[] = array('goods_name' => $val['goodsname'], 'price' => $price);
				}

				$tmp_data['actPrice'] = explode(' ', $price);
				$tmp_data['card_price'] = $price_arr['card_price'];

				$tmp_data['levelprice'] = $price_arr['levelprice']; // 客户等级价格
				$tmp_data['is_mb_level_buy'] = $price_arr['is_mb_level_buy']; //是否 客户等级 可享受

				//card_price

				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'],$member_id);

				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{

					$car_count = $cart->get_wecart_goods($val['id'],"",$head_id ,$token);

					if( empty($car_count)  )
					{
						$tmp_data['car_count'] = 0;
					}else{
						$tmp_data['car_count'] = $car_count;
					}

				}

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

				$tmp_data['is_video'] = empty($val['video']) ? false : true;

				$list[] = $tmp_data;
			}

			$is_show_list_timer  =  D('Home/Front')->get_config_by_name('is_show_list_timer');
			$is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');

			echo json_encode(array('code' => 0,'now_time' => time(), 'list' => $list ,'is_vip_card_member' => $is_vip_card_member,'is_member_level_buy' => $is_member_level_buy ,'copy_text_arr' => $copy_text_arr, 'cur_time' => time() ,'full_reducemoney' => $full_reducemoney,'full_money' => $full_money,'is_open_vipcard_buy' => $is_open_vipcard_buy,'is_open_fullreduction' => $is_open_fullreduction,'is_show_list_timer'=>$is_show_list_timer, 'cate_info' => $cate_info, 'is_show_cate_tabbar'=>$is_show_cate_tabbar ));
			die();
		}else{
			$is_show_cate_tabbar = D('Home/Front')->get_config_by_name('is_show_cate_tabbar');
			echo json_encode( array('code' => 1, 'cate_info' => $cate_info, 'is_show_cate_tabbar'=>$is_show_cate_tabbar) );
			die();
		}

	}


}
