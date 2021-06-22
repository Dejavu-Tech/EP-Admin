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
use Think\Model;
class GoodsModel extends Model{

	/**
		获取商品数量
	**/
	public function get_goods_count($where = '')
	{

		$total = M('eaterplanet_ecommerce_goods')->where('1 '.$where)->count();

		return $total;
	}

	/**
	 * 获取抖音真是地址
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	private function getrealurl($url){
	    $header = @get_headers($url,1);  //默认第二个参数0，可选1，返回关联数组
	    if(!$header){
	        return false;
	    }
	    if (strpos($header[0],'301') || strpos($header[0],'302')) {
	        if(is_array($header['location'])) {
	            return $header['location'][count($header['location'])-1];
	        }else{
	            return $header['location'];
	        }
	    }else {
	        return $url;
	    }
	}

	public function check_douyin_video( $url )
	{
		if( strpos($url,'douyin.com') !== false || strpos($url,'iesdouyin.com') !== false )
		{
			$realUrl = $this->getrealurl($url);
			if($realUrl) {
		        $itemIds = explode('/', $realUrl);
		        if(count($itemIds)>5) {
		            $itemId = $itemIds[5];
		            set_time_limit(0);
		            $data = file_get_contents('https://www.iesdouyin.com/web/api/v2/aweme/iteminfo/?item_ids='.$itemId);
		            if($data != '{}'){
		                $data = json_decode($data, true);
		                if($data['item_list'] && $data['item_list'][0] && $data['item_list'][0]['video']) {
		                    $vid = $data['item_list'][0]['video']['vid'];
		                    $ratio = $data['item_list'][0]['video']['ratio'];
		                    $vurl = 'https://aweme.snssdk.com/aweme/v1/playwm/?video_id=' . $vid . '&ratio=' . $ratio .'&line=0';
		                    return $vurl;
		                }
		            }
		        }
		    }
	    }
	    return $url;
	}


	public function _check_douyin_video( $url )
	{
		if( strpos($url,'douyin.com') !== false || strpos($url,'iesdouyin.com') !== false )
		{

			$UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			$data = curl_exec($curl);
			curl_close($curl);

			//获取
			preg_match('/<p class="desc">(?<desc>[^<>]*)<\/p>/i', $data, $name);
			preg_match('/playAddr: "(?<url>[^"]+)"/i', $data, $url_data);

			if( !empty($url_data) )
			{
				return $url_data[1];
			}else{
				return $url;
			}
		}else{
			return $url;
		}
	}

	public function addgoods($type = 'normal')
	{



		$post_data = array();
		$post_data_goods = array();
		$goodsname = I('post.goodsname','','trim');
		$post_data_goods['goodsname'] = addslashes($goodsname);
		$post_data_goods['subtitle'] = I('post.subtitle','','trim');
		$post_data_goods['grounding'] = I('post.grounding');
		$post_data_goods['type'] = $type;

		$post_data_goods['price'] = I('post.price');
		$post_data_goods['productprice'] = I('post.productprice');
		$post_data_goods['card_price'] = I('post.card_price');
		$post_data_goods['costprice'] = I('post.costprice');
		$post_data_goods['sales'] = I('post.sales');
		$post_data_goods['showsales'] = I('post.showsales');
		$post_data_goods['dispatchtype'] = I('post.dispatchtype');
		$post_data_goods['dispatchid'] = I('post.dispatchid');
		$post_data_goods['dispatchprice'] = I('post.dispatchprice');
		$post_data_goods['codes'] = I('post.codes','','trim');
		$post_data_goods['weight'] = I('post.weight','','trim');
		$post_data_goods['total'] = I('post.total','','trim');
		$post_data_goods['hasoption'] = I('post.hasoption','','intval');
		$post_data_goods['index_sort'] = I('post.index_sort','','intval');
		$post_data_goods['credit'] = I('post.credit','','trim');

		$post_data_goods['buyagain'] = I('post.buyagain','','trim');
		$post_data_goods['buyagain_condition'] = I('post.buyagain_condition','','intval');
		$post_data_goods['buyagain_sale'] = I('post.buyagain_sale','','intval');
		$post_data_goods['is_index_show'] = I('post.is_index_show','','intval');

		$post_data_goods['is_all_sale'] =  I('post.is_all_sale','0','intval');

		$post_data_goods['is_seckill'] =  I('post.is_seckill','0','intval');

		$post_data_goods['is_take_vipcard'] =  I('post.is_take_vipcard','0','intval');


		$post_data_goods['addtime'] = time();

		if (defined('ROLE') && ROLE == 'agenter' ) {

			$supply_add_goods_shenhe = D('Home/Front')->get_config_by_name('supply_add_goods_shenhe');
			if( empty($supply_add_goods_shenhe) )
			{
				$supply_add_goods_shenhe = 0;
			}

			if($supply_add_goods_shenhe)
			{
				$post_data_goods['grounding'] = 4;
			}
		}

		$goods_id = M('eaterplanet_ecommerce_goods')->add($post_data_goods);
		D('Seller/Operatelog')->addOperateLog('goods','添加商品--'.$post_data_goods['goodsname']);

		//find type ,modify somethings TODO...

		$pin_type_arr = array(
					'pin'=>'主流团',
					'lottery'=>'抽奖团',
					'oldman'=>'老人团',
					'newman'=>'新人团',
					'commiss'=>'佣金团',
					'ladder'=>'阶梯团',
					'flash'=>'快闪团',
				);

		$pin_type =  array_keys($pin_type_arr);

		if( in_array($type, $pin_type) )
		{
			//插入 拼团商品表 eaterplanet_ecommerce_good_pin $time = I('post.time');

			$time = I('post.time');

			$pin_data['goods_id'] = $goods_id;
			$pin_data['pinprice'] = I('post.pinprice');
			$pin_data['pin_count'] = I('post.pin_count');
			$pin_data['pin_hour'] = I('post.pin_hour');


			$pin_data['is_commiss_tuan'] = I('post.is_commiss_tuan',0);


			if($pin_data['is_commiss_tuan'] == 1)
			{
				$pin_data['is_zero_open'] = I('post.is_zero_open',0);
			}else{
				$pin_data['is_zero_open'] =0;
			}


			$pin_data['is_newman'] = I('post.is_newman',0);

			$commiss_tuan_money1 = I('post.commiss_tuan_money1',0);
			$commiss_tuan_money2 = I('post.commiss_tuan_money2',0);

			if( isset($commiss_tuan_money1) && $commiss_tuan_money1 >0 )
			{
				$pin_data['commiss_type'] = 0;
				$pin_data['commiss_money'] = $commiss_tuan_money1;

			}else{
				$pin_data['commiss_type'] = 1;
				$pin_data['commiss_money'] = $commiss_tuan_money2;
			}


			$time = I('post.time');

			$pin_data['begin_time'] = strtotime( $time['start'] );
			$pin_data['end_time'] = strtotime( $time['end'] );

			//拼团返利设置
			$pin_data['is_pintuan_rebate'] = I('post.is_pintuan_rebate',0);
			if($pin_data['is_pintuan_rebate'] == 1){
				$pin_data['random_delivery_count'] = I('post.random_delivery_count',0);
				$pin_data['rebate_reward'] = I('post.rebate_reward', 1);
				$pin_data['reward_point'] = I('post.reward_point', 0);
				$pin_data['reward_balance'] = I('post.reward_balance', 0);
			}

			M('eaterplanet_ecommerce_good_pin')->add( $pin_data );


		}
		//

			//find type ,modify somethings TODO...

			$cates = I('post.cate_mult');
			if( !empty($cates) )
			{
				foreach($cates as $cate_id)
				{
					$post_data_category = array();
					$post_data_category['cate_id'] = $cate_id;
					$post_data_category['goods_id'] = $goods_id;

					M('eaterplanet_ecommerce_goods_to_category')->add($post_data_category);
				}
			}
			//eaterplanet_ecommerce_goods_images
			$thumbs = I('post.thumbs');
			if( !empty($thumbs) )
			{
				foreach($thumbs as $thumbs)
				{
					$post_data_thumbs = array();
					$post_data_thumbs['goods_id'] = $goods_id;
					$post_data_thumbs['image'] = save_media($thumbs);
					$post_data_thumbs['thumb'] = save_media( resize($thumbs,200,200));

					M('eaterplanet_ecommerce_goods_images')->add($post_data_thumbs);
				}
			}



			//核销begin
			$is_only_hexiao = I('post.is_only_hexiao',0);
			if($is_only_hexiao == 1){
				$hx_data = array();
				$hx_time = time();
				$hx_data['goods_id'] = $goods_id;
				$hx_data['is_only_hexiao'] = $is_only_hexiao;
				$hx_data['hexiao_type'] = I('post.hexiao_type',0);
				$hx_data['hx_one_goods_time'] = I('post.hx_one_goods_time',0);
				$hx_data['hx_expire_type'] = I('post.hx_expire_type',0);
				$hx_data['hx_expire_day'] = I('post.hx_expire_day');
				$hx_data['hx_expire_begin_time'] = $hx_time;
				if($hx_data['hx_expire_type'] == 1){
					$hx_data['hx_expire_end_time'] = strtotime(I('post.hx_expire_end_time'));
				}else{
					if(empty($hx_data['hx_expire_day'])){
						$hx_data['hx_expire_day'] = 90;
					}
					$hx_data['hx_expire_end_time'] = $hx_time+$hx_data['hx_expire_day']*24*60*60;
				}
				$hx_data['hx_assign_salesroom'] = I('post.hx_assign_salesroom',0);
				$hx_data['hx_auto_off'] = I('post.hx_auto_off',0);
				$hx_data['hx_auto_off_time'] = I('post.hx_auto_off_time',0);
				$hx_data['addtime'] = $hx_time;
				M('eaterplanet_ecommerce_goods_salesroombase')->add($hx_data);
				if($hx_data['hx_assign_salesroom'] == 1){//指定门店
					$goods_room_ids = I('post.goods_room_ids','');
					$goods_is_hx_member = I('post.goods_is_hx_member','');
					$goods_room_smember = I('post.goods_room_smember','');
					if(!empty($goods_room_ids)){
						$goods_room_array = explode(',',$goods_room_ids);
						foreach($goods_room_array as $grv){
							$goods_room_smember_ids = $goods_room_smember[$grv];
							$goods_room_data = array();
							$goods_room_data['salesroom_id'] = $grv;
							$goods_room_data['goods_id'] = $goods_id;
							$goods_room_data['is_hx_member'] = $goods_is_hx_member[$grv];
							if($goods_is_hx_member[$grv] == 1 && empty($goods_room_smember_ids)){
								$goods_room_data['is_hx_member'] = 0;
							}
							$goods_room_data['addtime'] = $hx_time;
							$gr_id = M('eaterplanet_ecommerce_goods_relative_salesroom')->add($goods_room_data);
							if($gr_id !== false){
								$goods_room_smember_ids = $goods_room_smember[$grv];
								if($goods_is_hx_member[$grv] == 1 && !empty($goods_room_smember_ids)){
									$smember_ids = explode(',',$goods_room_smember_ids);
									foreach($smember_ids as $sv){
										$room_smember_data = array();
										$room_smember_data['salesroom_id'] = $grv;
										$room_smember_data['gr_id'] = $gr_id;
										$room_smember_data['smember_id'] = $sv;
										$room_smember_data['addtime'] = $hx_time;
										M('eaterplanet_ecommerce_goods_relative_smember')->add($room_smember_data);
									}
								}
							}
						}
					}
				}
			}
			//核销end


			//eaterplanet_ecommerce_good_common

			$post_data_common =  array();
			$post_data_common['goods_id'] = $goods_id;
			$post_data_common['quality'] = I('post.quality');
			$post_data_common['seven'] = I('post.seven');
			$post_data_common['repair'] = I('post.repair');

			$labelname = I('post.labelname');
			$post_data_common['labelname'] = serialize($labelname);

			$post_data_common['share_title'] = I('post.share_title');
			$post_data_common['share_description'] = I('post.share_description');

			$content = I('post.content');
			$post_data_common['content'] = htmlspecialchars($content);
			$post_data_common['pick_up_type'] = I('post.pick_up_type');
			$post_data_common['pick_up_modify'] = I('post.pick_up_modify');
			$post_data_common['one_limit_count'] = I('post.one_limit_count');
			$post_data_common['oneday_limit_count'] = I('post.oneday_limit_count');
			$post_data_common['total_limit_count'] = I('post.total_limit_count');
			$post_data_common['community_head_commission'] = I('post.community_head_commission');
			$is_community_head_commission = I('post.is_community_head_commission');
			$post_data_common['is_community_head_commission'] = $is_community_head_commission;

			$post_data_common['goods_start_count'] = I('post.goods_start_count');

			$post_data_common['is_show_arrive'] = I('post.is_show_arrive');
			$post_data_common['diy_arrive_switch'] = I('post.diy_arrive_switch');
			$post_data_common['diy_arrive_details'] = I('post.diy_arrive_details');


			$post_data_common['is_new_buy'] = I('post.is_new_buy');
			$post_data_common['is_spike_buy'] = I('post.is_spike_buy');

			if (defined('ROLE') && ROLE == 'agenter' )
			{
				$supply_can_goods_sendscore =  D('Home/Front')->get_config_by_name('supply_can_goods_sendscore');
				if($supply_can_goods_sendscore == 1){
					$post_data_common['is_modify_sendscore'] = I('post.is_modify_sendscore',0);
					$post_data_common['send_socre'] = I('post.send_socre');
				}
			}else{
				$post_data_common['is_modify_sendscore'] = I('post.is_modify_sendscore',0);
				$post_data_common['send_socre'] = I('post.send_socre');

			}

			$post_data_common['is_mb_level_buy'] = I('post.is_mb_level_buy', 1);

			//$post_data_common['supply_id'] = I('post.supply_id');

			if (defined('ROLE') && ROLE == 'agenter' )
			{
				$supper_info = get_agent_logininfo();

				$post_data_common['supply_id'] = $supper_info['id'];
			}else{
				$post_data_common['supply_id'] = I('post.supply_id');
			}

			$time = I('post.time');

			$post_data_common['begin_time'] = strtotime( $time['start'] );
			$post_data_common['end_time'] = strtotime( $time['end'] );

			$big_img =I('post.big_img');
			$goods_share_image =I('post.goods_share_image');

			$post_data_common['big_img'] = save_media($big_img);
			$post_data_common['goods_share_image'] = save_media($goods_share_image);
			$post_data_common['video'] = save_media(I('post.video'));

			$post_data_common['video'] = $this->check_douyin_video($post_data_common['video']);


			$post_data_common['print_sub_title'] = I('post.print_sub_title');


			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');

			$post_data_common['is_only_express'] =  I('post.is_only_express',0);

			$post_data_common['is_only_hexiao'] = $is_only_hexiao;

			$post_data_common['packing_free'] = I('post.packing_free',0);

			if( $post_data_common['is_only_express'] == 1 )
			{
				$post_data_common['is_only_distribution'] = 0;
			}else {
				$is_only_distribution = I('post.is_only_distribution');

				$post_data_common['is_only_distribution'] = $is_only_distribution;
			}


			$post_data_common['is_limit_levelunbuy'] = I('post.is_limit_levelunbuy',0);

			$post_data_common['is_limit_vipmember_buy'] = I('post.is_limit_vipmember_buy',0);

			if( empty($is_open_fullreduction) )
			{
				$post_data_common['is_take_fullreduction'] = 1;
			}else if( $is_open_fullreduction ==0 )
			{

			}else if($is_open_fullreduction ==1){
				$post_data_common['is_take_fullreduction'] =  I('post.is_take_fullreduction' ,1);
			}

			if($post_data_common['is_take_fullreduction'] == 1 && $post_data_common['supply_id'] > 0)
			{
				$supply_info = M('eaterplanet_ecommerce_supply')->field('type')->where( array('id' => $post_data_common['supply_id'] ) )->find();
				if( !empty($supply_info) && $supply_info['type'] == 1 )
				{
					$post_data_common['is_take_fullreduction'] = 0;
				}
			}

			//begin

			/**
				$is_modify_head_commission = I('post.is_modify_head_commission','0','intval');
				if( isset($is_modify_head_commission) )
				{
					$post_data_common['is_modify_head_commission'] = $is_modify_head_commission;

			**/

			$is_modify_head_commission = I('post.is_modify_head_commission','0','intval');
			if( isset($is_modify_head_commission) )
			{
				$post_data_common['is_modify_head_commission'] = $is_modify_head_commission;

				if( $post_data_common['is_modify_head_commission'] == 1 )
				{
					$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();

					$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');
					$default_comunity_money = D('Home/Front')->get_config_by_name('head_commission_levelname');

					$list_default = array(
						array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
					);

					$community_head_level = array_merge($list_default, $community_head_level);

					$community_head_commission_modify = array();

					foreach($community_head_level as $kk => $vv)
					{
						$community_head_commission_modify['head_level'.$vv['id']] = I('post.head_level'.$vv['id']);
					}
					if( !isset($is_community_head_commission)){
						$post_data_common['community_head_commission'] = $community_head_commission_modify['head_level0'];
					}
					$post_data_common['community_head_commission_modify'] = serialize($community_head_commission_modify);
				}

			}else{
				$post_data_common['is_modify_head_commission'] = 0;
			}

			$relative_goods_list = array();

			$limit_goods_list = I('post.limit_goods_list');

			if( isset($limit_goods_list) && !empty($limit_goods_list) )
			{
				$limit_goods_list =  explode(',', $limit_goods_list);
				$relative_goods_list = $limit_goods_list;
			}
			$post_data_common['relative_goods_list'] = serialize($relative_goods_list);

			$post_data_common['has_mb_level_buy'] = I('post.has_mb_level_buy',0,'intval');
			$level_id_list = I('post.level_id');
			$discount_list = I('post.discount');
			$mb_level_buy_list = array();
			if(isset($level_id_list) && !empty($level_id_list)){
				for($i = 0;$i < count($level_id_list);$i++){
					$level_list = array();
					$level_list['level_id'] = $level_id_list[$i];
					if(!is_numeric($discount_list[$i])){
						$level_list['discount'] = 0;
					}else{
						if($discount_list[$i] < 0 && $discount_list[$i] > 100){
							$level_list['discount'] = 0;
						}else{
							$level_list['discount'] = $discount_list[$i];
						}
					}
					$mb_level_buy_list[] = $level_list;
				}
			}
			$post_data_common['mb_level_buy_list'] = serialize($mb_level_buy_list);

			//end
			M('eaterplanet_ecommerce_good_common')->add($post_data_common);


			$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');


			//规格
			$hasoption = I('post.hasoption');
			if( intval($hasoption) == 1 )
			{
				$mult_option_item_dan_key = array();
				$replace_option_item_id_arr = array();//需要更替的option_item_id
				$save_goods_option_arr = array();

				$spec_id = I('post.spec_id');
				if( isset($spec_id) && !empty($spec_id) )
				{
					$option_order = 1;

					foreach($spec_id as $spec_id)
					{
						$spec_title = I('post.spec_title');
						//规格标题
						$cur_spec_title = $spec_title[$spec_id];

						$goods_option_data = array();
						$goods_option['goods_id'] = $goods_id;
						$goods_option['title'] = $cur_spec_title;
						$goods_option['displayorder'] = $option_order;


						$option_id = M('eaterplanet_ecommerce_goods_option')->add($goods_option);

						$save_goods_option_arr[] = $option_id;

						$spec_item_title_t = 'post.spec_item_title_'.$spec_id;
						$spec_item_title_arr =  I($spec_item_title_t);
						if(!empty($spec_item_title_arr))
						{
							$item_sort = 1;
							$i = 0;
							$j = 0;
							foreach($spec_item_title_arr as $key =>$item_title)
							{
								$goods_option_item_data = array();
								$goods_option_item_data['goods_id'] = $goods_id;
								$goods_option_item_data['goods_option_id'] = $option_id;
								$goods_option_item_data['title'] = $item_title;
								$thumb_t = I('post.spec_item_thumb_'.$spec_id);
								// $_GPC['spec_item_thumb_'.$spec_id]
								$goods_option_item_data['thumb'] = $thumb_t[$key];
								$goods_option_item_data['displayorder'] = $item_sort;


								$new_option_item_id = M('eaterplanet_ecommerce_goods_option_item')->add($goods_option_item_data);



								$replace_option_item_id_arr[$option_item_id] = $new_option_item_id;
								$option_item_id = $new_option_item_id;

								//从小到大的排序
								$t_k_arr = I('post.spec_item_id_'.$spec_id);

								$mult_option_item_dan_key[ $t_k_arr[$key] ] = $option_item_id;
								$item_sort++;
								$i++;
							}
						}else{
							M('eaterplanet_ecommerce_goods_option')->where( array('id' => $id) )->delete();
						}
						$option_order++;
					}
				}

				$option_ids_arr = I('post.option_ids');
				$total = 0;

				foreach($option_ids_arr as $val)
				{
					$option_item_ids = '';
					$option_item_ids_arr = array();

					$key_items = explode('_', $val);

					$new_val = array();
					foreach($key_items as $vv)
					{
						if( isset($replace_option_item_id_arr[$vv]) )
						{
							$option_item_ids_arr[] = $replace_option_item_id_arr[$vv];
						}else{
							$option_item_ids_arr[] = $mult_option_item_dan_key[$vv];
						}

						$new_val[] = $vv;
					}



					asort($option_item_ids_arr);
					$option_item_ids = implode('_', $option_item_ids_arr);

					$eaterplanet_goods_option_item_value_data = array();
					$eaterplanet_goods_option_item_value_data['goods_id'] = $goods_id;
					$eaterplanet_goods_option_item_value_data['option_item_ids'] = $option_item_ids;



					$productprice = I('post.option_productprice_'.$val);

					$eaterplanet_goods_option_item_value_data['productprice'] =  $productprice;

					$pinprice = I('post.option_presell_'.$val);


					$eaterplanet_goods_option_item_value_data['pinprice'] =  $pinprice;

					$marketprice = I('post.option_marketprice_'.$val);


					$eaterplanet_goods_option_item_value_data['marketprice'] = $marketprice;

					if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
					{
						$card_price = I('post.option_cardprice_'.$val);

						$eaterplanet_goods_option_item_value_data['card_price'] = $card_price;
					}

					$stock = I('post.option_stock_'.$val);

					$costprice = I('post.option_costprice_'.$val);

					$goodssn = I('post.option_goodssn_'.$val);

					$weight = I('post.option_weight_'.$val);


					$title = I('post.option_title_'.$val);


					$eaterplanet_goods_option_item_value_data['stock'] =  $stock;
					$eaterplanet_goods_option_item_value_data['costprice'] =  $costprice;
					$eaterplanet_goods_option_item_value_data['goodssn'] = $goodssn;
					$eaterplanet_goods_option_item_value_data['weight'] =  $weight;
					$eaterplanet_goods_option_item_value_data['title'] = $title ;
					$total += $eaterplanet_goods_option_item_value_data['stock'];

					M('eaterplanet_ecommerce_goods_option_item_value')->add($eaterplanet_goods_option_item_value_data);

				}

				//更新库存 total
				$up_goods_data = array();
				$up_goods_data['total'] = $total;
				M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id) )->save( $up_goods_data );

				if( !empty($save_goods_option_arr) )
				{
					foreach( $save_goods_option_arr as $k_option_id )
					{
						$tp_item_val = M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_option_id' => $k_option_id ) )->find();

						if( empty($tp_item_val) )
						{
							M('eaterplanet_ecommerce_goods_option')->where( array('id' => $k_option_id ) )->delete();
						}
					}
				}
			}
			if( isset($_POST['presale_type']) )
            {
                D('Seller/GoodsPresale')->modifyGoodsPresale( $goods_id );
            }

            //虚拟卡密
            if( isset($_POST['is_virtualcard_goods']) && $_POST['is_virtualcard_goods'] == 1 )
            {
                D('Seller/VirtualCard')->modifyGoodsVirtualCard( $goods_id );
            }

			//规格插入
			$post_data_commiss = array();

			$post_data_commiss['goods_id'] = $goods_id;
			$post_data_commiss['nocommission'] =  I('post.nocommission',0,'intval');
			$post_data_commiss['hascommission'] = I('post.hascommission',0,'intval');
			$post_data_commiss['commission_type'] = I('post.commission_type',0,'intval');
			$post_data_commiss['commission1_rate'] = I('post.commission1_rate');
			$post_data_commiss['commission1_pay'] = I('post.commission1_pay');
			$post_data_commiss['commission2_rate'] = I('post.commission2_rate');
			$post_data_commiss['commission2_pay'] = I('post.commission2_pay');
			$post_data_commiss['commission3_rate'] = I('post.commission3_rate');
			$post_data_commiss['commission3_pay'] = I('post.commission3_pay');

			M('eaterplanet_ecommerce_good_commiss')->add( $post_data_commiss );


			//售卖团长插入
			$head_id_arr = I('request.head_id_arr');
		    if(!empty($head_id_arr)){
				$head_ids = explode(",",$head_id_arr);
				foreach($head_ids as $head_id)
				{
					D('Seller/Communityhead')->insert_head_goods($goods_id, $head_id);
				}
			}

			D('Seller/Redisorder')->sysnc_goods_total($goods_id);
	}


	/**
		获取编辑的商品资料
	**/
	public function get_edit_goods_info($id,$is_pin =0)
	{


		$item = M('eaterplanet_ecommerce_goods')->where( array('id' => $id) )->find();

		$cates_arr = M('eaterplanet_ecommerce_goods_to_category')->where( array('goods_id' => $id) )->order('id asc')->select();


		$cates = array();
		foreach($cates_arr as $val)
		{
			$cates[] = $val['cate_id'];
		}
		$item['cates'] = $cates;


		$piclist = array();
		//ims_eaterplanet_ecommerce_goods_images labelname[]

		$piclist_arr = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $id ) )->order('id asc')->select();


		foreach($piclist_arr as $val)
		{
			if( empty($val['thumb'] ) )
		    {
		        $val['thumb'] = $val['image'];
		    }
			//image
			$piclist[] = array('image' =>$val['image'], 'thumb' => $val['thumb'] ); //$val['image'];
		}

		//$item['piclist']

		$item['piclist'] = $piclist;

		$item_common = M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $id) )->find();

		$item = array_merge($item,$item_common);

		if( $item['supply_id'] >0 )
		{

			$supply_info = M('eaterplanet_ecommerce_supply')->where( array('id' => $item['supply_id']) )->find();

		    if(!empty($supply_info) )
		    {
		        $supply_info['supply_id'] = $supply_info['id'];
		        $supply_info['logo'] = ($supply_info['logo']);
		    }
		     $item['supply_info'] = $supply_info;

		}

		//item
		$pin_type_arr = array(
					'pin'=>'主流团',
					'lottery'=>'抽奖团',
					'oldman'=>'老人团',
					'newman'=>'新人团',
					'commiss'=>'佣金团',
					'ladder'=>'阶梯团',
					'flash'=>'快闪团',
				);

		$pin_type =  array_keys($pin_type_arr);


		if( in_array($item['type'], $pin_type) )
		{

			$pin_item = M('eaterplanet_ecommerce_good_pin')->where( array('goods_id' => $id ) )->find();

			$item = array_merge($item,$pin_item);
		}


		//核销 begin TODO....




		//核销 end

		$label_id = unserialize($item['labelname']);
		$label = array();
		if($label_id){
			$label = D('Home/Pingoods')->get_goods_tags($label_id);
		}
		$item['label'] = $label;


		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');

		//TODO.20181219

		$allspecs = M('eaterplanet_ecommerce_goods_option')->where( array('goods_id' => $id) )->order('displayorder asc')->select();

		foreach ($allspecs as &$s ) {
			$s['items'] = M('eaterplanet_ecommerce_goods_option_item')->field('id,goods_option_id,title,thumb,displayorder')->where( array('goods_option_id' =>$s['id'] ) )->order('displayorder asc')->select();
		}

		$item['allspecs'] = $allspecs;
		//allspecs //html

		$html = '';

		$options = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $id) )->order('id asc')->select();

		$specs = array();

		if (0 < count($options)) {
			$specitemids = explode('_', $options[0]['option_item_ids']);

			foreach ($specitemids as $itemid ) {
				foreach ($allspecs as $ss ) {
					$items = $ss['items'];

					foreach ($items as $it ) {
						while ($it['id'] == $itemid) {
							$specs[] = $ss;
							break;
						}
					}
				}
			}

			$html = '';
			$html .= '<table class="table table-bordered table-condensed">';
			$html .= '<thead>';
			$html .= '<tr class="active">';
			$discounts_html .= '<table class="table table-bordered table-condensed">';
			$discounts_html .= '<thead>';
			$discounts_html .= '<tr class="active">';
			$commission_html .= '<table class="table table-bordered table-condensed">';
			$commission_html .= '<thead>';
			$commission_html .= '<tr class="active">';
			$isdiscount_discounts_html .= '<table class="table table-bordered table-condensed">';
			$isdiscount_discounts_html .= '<thead>';
			$isdiscount_discounts_html .= '<tr class="active">';
			$len = count($specs);
			$newlen = 1;
			$h = array();
			$rowspans = array();
			$i = 0;

			while ($i < $len) {
				$html .= '<th>' . $specs[$i]['title'] . '</th>';
				$discounts_html .= '<th>' . $specs[$i]['title'] . '</th>';
				$commission_html .= '<th>' . $specs[$i]['title'] . '</th>';
				$isdiscount_discounts_html .= '<th>' . $specs[$i]['title'] . '</th>';
				$itemlen = count($specs[$i]['items']);

				if ($itemlen <= 0) {
					$itemlen = 1;
				}


				$newlen *= $itemlen;
				$h = array();
				$j = 0;

				while ($j < $newlen) {
					$h[$i][$j] = array();
					++$j;
				}

				$l = count($specs[$i]['items']);
				$rowspans[$i] = 1;
				$j = $i + 1;

				while ($j < $len) {
					$rowspans[$i] *= count($specs[$j]['items']);
					++$j;
				}

				++$i;
			}

			$canedit = true;

			if ($canedit) {
				if(!empty($levels))
				{
					foreach ($levels as $level ) {
						$discounts_html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">' . $level['levelname'] . '</div><div class="input-group"><input type="text" class="form-control  input-sm discount_' . $level['key'] . '_all" VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'discount_' . $level['key'] . '\');"></a></span></div></div></div></th>';
						$isdiscount_discounts_html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">' . $level['levelname'] . '</div><div class="input-group"><input type="text" class="form-control  input-sm isdiscount_discounts_' . $level['key'] . '_all" VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'isdiscount_discounts_' . $level['key'] . '\');"></a></span></div></div></div></th>';
					}
				}

				if( !empty($commission_level) )
				{
					foreach ($commission_level as $level ) {
						$commission_html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">' . $level['levelname'] . '</div></div></th>';
					}
				}


				$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">库存<label style="color:red;font-size:16px;font-weight:900">*</label></div><div class="input-group"><input type="text" class="form-control input-sm option_stock_all"  VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_stock\');"></a></span></div></div></div></th>';
				if($is_pin == 1)
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">拼团价<label style="font-size:16px;font-weight:900;color: #ff0000;">*</label>    </div><div class="input-group"><input type="text" class="form-control  input-sm option_presell_all"  VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_presell\');"></a></span></div></div></div></th>';

				if( $item['type'] == 'integral' )
				{
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">兑换积分</div><div class="input-group"><input type="text" class="form-control  input-sm option_marketprice_all"  VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_marketprice\');"></a></span></div></div></div></th>';
				}else{
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">售价<label style="color:red;font-size:16px;font-weight:900">*</label></div><div class="input-group"><input type="text" class="form-control  input-sm option_marketprice_all"  VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_marketprice\');"></a></span></div></div></div></th>';
				}

				if($item['type'] != 'integral' && $is_pin == 0 && !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 && isset($item['is_take_vipcard']) && $item['is_take_vipcard'] == 1)
				{
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">付费会员专享价</div><div class="input-group"><input type="text" class="form-control input-sm option_cardprice_all"  VALUE=""/><div class="input-group-append"><span class="input-group-text"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_cardprice\');"></a></span></div></div></div></th>';

				}

				$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">原价<label style="color:red;font-size:16px;font-weight:900">*</label></div><div class="input-group"><input type="text" class="form-control input-sm option_productprice_all"  VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_productprice\');"></a></span></div></div></div></th>';

				$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">成本价</div><div class="input-group"><input type="text" class="form-control input-sm option_costprice_all"  VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_costprice\');"></a></span></div></div></div></th>';
				$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">编码</div><div class="input-group"><input type="text" class="form-control input-sm option_goodssn_all"  VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_goodssn\');"></a></span></div></div></div></th>';
				//$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">条码</div><div class="input-group"><input type="text" class="form-control input-sm option_productsn_all"  VALUE=""/><div class="input-group-append"><span class="input-group-text"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_productsn\');"></a></span></div></div></div></th>';
				$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">重量（克）</div><div class="input-group"><input type="text" class="form-control input-sm option_weight_all"  VALUE=""/><div class="input-group-append"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_weight\');"></a></span></div></div></div></th>';
			}

			$html .= '</tr></thead>';
			$discounts_html .= '</tr></thead>';
			$isdiscount_discounts_html .= '</tr></thead>';
			$commission_html .= '</tr></thead>';
			$m = 0;

			while ($m < $len) {
				$k = 0;
				$kid = 0;
				$n = 0;
				$j = 0;

				while ($j < $newlen) {
					$rowspan = $rowspans[$m];

					if (($j % $rowspan) == 0) {
						$h[$m][$j] = array('html' => '<td class=\'full\' rowspan=\'' . $rowspan . '\'>' . $specs[$m]['items'][$kid]['title'] . '</td>', 'id' => $specs[$m]['items'][$kid]['id']);
					}
					 else {
						$h[$m][$j] = array('html' => '', 'id' => $specs[$m]['items'][$kid]['id']);
					}

					++$n;

					if ($n == $rowspan) {
						++$kid;

						if ((count($specs[$m]['items']) - 1) < $kid) {
							$kid = 0;
						}


						$n = 0;
					}


					++$j;
				}

				++$m;
			}

			$hh = '';
			$dd = '';
			$isdd = '';
			$cc = '';
			$i = 0;

			while ($i < $newlen) {
				$hh .= '<tr>';
				$dd .= '<tr>';
				$isdd .= '<tr>';
				$cc .= '<tr>';
				$ids = array();
				$j = 0;

				while ($j < $len) {
					$hh .= $h[$j][$i]['html'];
					$dd .= $h[$j][$i]['html'];
					$isdd .= $h[$j][$i]['html'];
					$cc .= $h[$j][$i]['html'];
					$ids[] = $h[$j][$i]['id'];
					++$j;
				}


				asort($ids);
				$ids = implode('_', $ids);

				$val = array('id' => '', 'title' => '', 'stock' => '', 'presell' => '', 'costprice' => '','card_price' => '', 'productprice' => '', 'marketprice' => '', 'weight' => '', 'virtual' => '');

				$discounts_val = array('id' => '', 'title' => '', 'level' => '', 'costprice' => '', 'productprice' => '', 'marketprice' => '', 'weight' => '', 'virtual' => '');
				$isdiscounts_val = array('id' => '', 'title' => '', 'level' => '', 'costprice' => '', 'productprice' => '', 'marketprice' => '', 'weight' => '', 'virtual' => '');
				$commission_val = array('id' => '', 'title' => '', 'level' => '', 'costprice' => '', 'productprice' => '', 'marketprice' => '', 'weight' => '', 'virtual' => '');

				if(!empty($levels)) {
				foreach ($levels as $level ) {
					$discounts_val[$level['key']] = '';
					$isdiscounts_val[$level['key']] = '';
				}
				}

				if(!empty($commission_level)){
				foreach ($commission_level as $level ) {
					$commission_val[$level['key']] = '';
				}
				}

				foreach ($options as $o ) {
					while ($ids === $o['option_item_ids']) {

						$val = array('id' => $o['id'], 'title' => $o['title'], 'stock' => $o['stock'], 'costprice' => $o['costprice'],'card_price' => $o['card_price'] , 'productprice' => $o['productprice'], 'pinprice' => $o['pinprice'], 'marketprice' => $o['marketprice'], 'goodssn' => $o['goodssn'], 'productsn' => $o['productsn'], 'weight' => $o['weight'], 'virtual' => $o['virtual']);
						$discount_val = array('id' => $o['id']);
						if(!empty($levels))
						{
							foreach ($levels as $level ) {
								$discounts_val[$level['key']] = ((is_string($discounts[$level['key']]) ? '' : $discounts[$level['key']]['option' . $o['id']]));
								$isdiscounts_val[$level['key']] = ((is_string($isdiscount_discounts[$level['key']]) ? '' : $isdiscount_discounts[$level['key']]['option' . $o['id']]));
							}
						}

						$commission_val = array();

						if(!empty($commission_level))
						{
							foreach ($commission_level as $level ) {
								$temp = ((is_string($commission[$level['key']]) ? '' : $commission[$level['key']]['option' . $o['id']]));

								if (is_array($temp)) {
									foreach ($temp as $t_val ) {
										$commission_val[$level['key']][] = $t_val;
									}
								}

							}
						}

						unset($temp);
						break;
					}
				}

				if ($canedit) {
					if( !empty($levels) )
					{
						foreach ($levels as $level ) {
							$dd .= '<td>';
							$isdd .= '<td>';

							if ($level['key'] == 'default') {
								$dd .= '<input data-name="discount_level_' . $level['key'] . '_' . $ids . '"  type="text" class="form-control discount_' . $level['key'] . ' discount_' . $level['key'] . '_' . $ids . '" value="' . $discounts_val[$level['key']] . '"/> ';
								$isdd .= '<input data-name="isdiscount_discounts_level_' . $level['key'] . '_' . $ids . '"  type="text" class="form-control isdiscount_discounts_' . $level['key'] . ' isdiscount_discounts_' . $level['key'] . '_' . $ids . '" value="' . $isdiscounts_val[$level['key']] . '"/> ';
							}
							 else {
								$dd .= '<input data-name="discount_level_' . $level['id'] . '_' . $ids . '"  type="text" class="form-control discount_level' . $level['id'] . ' discount_level' . $level['id'] . '_' . $ids . '" value="' . $discounts_val['level' . $level['id']] . '"/> ';
								$isdd .= '<input data-name="isdiscount_discounts_level_' . $level['id'] . '_' . $ids . '"  type="text" class="form-control isdiscount_discounts_level' . $level['id'] . ' isdiscount_discounts_level' . $level['id'] . '_' . $ids . '" value="' . $isdiscounts_val['level' . $level['id']] . '"/> ';
							}

							$dd .= '</td>';
							$isdd .= '</td>';
						}
					}


					$dd .= '<input data-name="discount_id_' . $ids . '"  type="hidden" class="form-control discount_id discount_id_' . $ids . '" value="' . $discounts_val['id'] . '"/>';
					$dd .= '<input data-name="discount_ids"  type="hidden" class="form-control discount_ids discount_ids_' . $ids . '" value="' . $ids . '"/>';
					$dd .= '<input data-name="discount_title_' . $ids . '"  type="hidden" class="form-control discount_title discount_title_' . $ids . '" value="' . $discounts_val['title'] . '"/>';
					$dd .= '<input data-name="discount_virtual_' . $ids . '"  type="hidden" class="form-control discount_title discount_virtual_' . $ids . '" value="' . $discounts_val['virtual'] . '"/>';
					$dd .= '</tr>';
					$isdd .= '<input data-name="isdiscount_discounts_id_' . $ids . '"  type="hidden" class="form-control isdiscount_discounts_id isdiscount_discounts_id_' . $ids . '" value="' . $isdiscounts_val['id'] . '"/>';
					$isdd .= '<input data-name="isdiscount_discounts_ids"  type="hidden" class="form-control isdiscount_discounts_ids isdiscount_discounts_ids_' . $ids . '" value="' . $ids . '"/>';
					$isdd .= '<input data-name="isdiscount_discounts_title_' . $ids . '"  type="hidden" class="form-control isdiscount_discounts_title isdiscount_discounts_title_' . $ids . '" value="' . $isdiscounts_val['title'] . '"/>';
					$isdd .= '<input data-name="isdiscount_discounts_virtual_' . $ids . '"  type="hidden" class="form-control isdiscount_discounts_title isdiscount_discounts_virtual_' . $ids . '" value="' . $isdiscounts_val['virtual'] . '"/>';
					$isdd .= '</tr>';

					if(!empty($commission_level)){
						foreach ($commission_level as $level ) {
							$cc .= '<td>';

							if (!(empty($commission_val)) && isset($commission_val[$level['key']])) {
								foreach ($commission_val as $c_key => $c_val ) {
									if ($c_key == $level['key']) {
										if ($level['key'] == 'default') {
											$c_i = 0;

											while ($c_i < $shopset_level) {
												$cc .= '<input data-name="commission_level_' . $level['key'] . '_' . $ids . '"  type="text" class="form-control commission_' . $level['key'] . ' commission_' . $level['key'] . '_' . $ids . '" value="' . $c_val[$c_i] . '" style="display:inline;width: ' . (96 / $shopset_level) . '%;"/> ';
												++$c_i;
											}
										}
										 else {
											$c_i = 0;

											while ($c_i < $shopset_level) {
												$cc .= '<input data-name="commission_level_' . $level['id'] . '_' . $ids . '"  type="text" class="form-control commission_level' . $level['id'] . ' commission_level' . $level['id'] . '_' . $ids . '" value="' . $c_val[$c_i] . '" style="display:inline;width: ' . (96 / $shopset_level) . '%;"/> ';
												++$c_i;
											}
										}
									}

								}
							}
							 else if ($level['key'] == 'default') {
								$c_i = 0;

								while ($c_i < $shopset_level) {
									$cc .= '<input data-name="commission_level_' . $level['key'] . '_' . $ids . '"  type="text" class="form-control commission_' . $level['key'] . ' commission_' . $level['key'] . '_' . $ids . '" value="" style="display:inline;width: ' . (96 / $shopset_level) . '%;"/> ';
									++$c_i;
								}
							}
							 else {
								$c_i = 0;

								while ($c_i < $shopset_level) {
									$cc .= '<input data-name="commission_level_' . $level['id'] . '_' . $ids . '"  type="text" class="form-control commission_level' . $level['id'] . ' commission_level' . $level['id'] . '_' . $ids . '" value="" style="display:inline;width: ' . (96 / $shopset_level) . '%;"/> ';
									++$c_i;
								}
							}

							$cc .= '</td>';
						}
					}
					$cc .= '<input data-name="commission_id_' . $ids . '"  type="hidden" class="form-control commission_id commission_id_' . $ids . '" value="' . $commissions_val['id'] . '"/>';
					$cc .= '<input data-name="commission_ids"  type="hidden" class="form-control commission_ids commission_ids_' . $ids . '" value="' . $ids . '"/>';
					$cc .= '<input data-name="commission_title_' . $ids . '"  type="hidden" class="form-control commission_title commission_title_' . $ids . '" value="' . $commissions_val['title'] . '"/>';
					$cc .= '<input data-name="commission_virtual_' . $ids . '"  type="hidden" class="form-control commission_title commission_virtual_' . $ids . '" value="' . $commissions_val['virtual'] . '"/>';
					$cc .= '</tr>';



					$hh .= '<td>';
					$hh .= '<input name="option_stock_' . $ids . '"  type="text" class="form-control option_stock option_stock_' . $ids . '" value="' . $val['stock'] . '"/>';
					$hh .= '</td>';
					$hh .= '<input name="option_id_' . $ids . '"  type="hidden" class="form-control option_id option_id_' . $ids . '" value="' . $val['id'] . '"/>';
					$hh .= '<input name="option_ids[]"  type="hidden" class="form-control option_ids option_ids_' . $ids . '" value="' . $ids . '"/>';
					$hh .= '<input name="option_title_' . $ids . '"  type="hidden" class="form-control option_title option_title_' . $ids . '" value="' . $val['title'] . '"/>';
					$hh .= '<input name="option_virtual_' . $ids . '"  type="hidden" class="form-control option_virtual option_virtual_' . $ids . '" value="' . $val['virtual'] . '"/>';
					$hh .= '<input name="option_cardprice_hi_' . $ids . '"  type="hidden" class="form-control option_virtual option_cardprice_hi_' . $ids . '" value="' . $val['card_price'] . '"/>';
					if($is_pin == 1)
						$hh .= '<td><input name="option_presell_' . $ids . '" type="text" class="form-control option_presell option_presell_' . $ids . '" value="' . $val['pinprice'] . '"/></td>';

					$hh .= '<td><input name="option_marketprice_' . $ids . '" type="text" class="form-control option_marketprice option_marketprice_' . $ids . '" value="' . $val['marketprice'] . '"/></td>';

					if($item['type'] != 'integral' && $is_pin == 0 && !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 && isset($item['is_take_vipcard']) && $item['is_take_vipcard'] == 1)
					{
						$hh .= '<td><input name="option_cardprice_' . $ids . '" type="text" class="form-control option_cardprice option_cardprice_' . $ids . '" value="' . $val['card_price'] . '"/></td>';
					}
					$hh .= '<td><input name="option_productprice_' . $ids . '" type="text" class="form-control option_productprice option_productprice_' . $ids . '" " value="' . $val['productprice'] . '"/></td>';
					$hh .= '<td><input name="option_costprice_' . $ids . '" type="text" class="form-control option_costprice option_costprice_' . $ids . '" " value="' . $val['costprice'] . '"/></td>';
					$hh .= '<td><input name="option_goodssn_' . $ids . '" type="text" class="form-control option_goodssn option_goodssn_' . $ids . '" " value="' . $val['goodssn'] . '"/></td>';
					//$hh .= '<td><input data-name="option_productsn_' . $ids . '" type="text" class="form-control option_productsn option_productsn_' . $ids . '" " value="' . $val['productsn'] . '"/></td>';
					$hh .= '<td><input name="option_weight_' . $ids . '" type="text" class="form-control option_weight option_weight_' . $ids . '" " value="' . $val['weight'] . '"/></td>';
					$hh .= '</tr>';
				}

				++$i;
			}

			$discounts_html .= $dd;
			$discounts_html .= '</table>';
			$isdiscount_discounts_html .= $isdd;
			$isdiscount_discounts_html .= '</table>';
			$html .= $hh;
			$html .= '</table>';
			$commission_html .= $cc;
			$commission_html .= '</table>';

			$item['html'] = $html;
			//allspecs //html
		}



		$good_commiss_data = M('eaterplanet_ecommerce_good_commiss')->where( array('goods_id' => $id ) )->find();

		//$good_commiss_data = array();

		if( empty($good_commiss_data) )
		{
			$good_commiss_data = array();
		}


		$item = array_merge($item, $good_commiss_data);

		//核销数据
		$item_salesroombase = M('eaterplanet_ecommerce_goods_salesroombase')->where( array('goods_id' => $id) )->find();
		$item_salesroombase['hx_expire_end_time'] = date('Y-m-d H:i:s',$item_salesroombase['hx_expire_end_time']);
		$item = array_merge($item, $item_salesroombase);
		//核销门店
		$item_salesroom = M()->query("SELECT gs.*,sr.room_name,sr.room_logo FROM " . C('DB_PREFIX') .
				"eaterplanet_ecommerce_goods_relative_salesroom as gs left join " . C('DB_PREFIX') ."eaterplanet_ecommerce_salesroom as sr on gs.salesroom_id=sr.id   WHERE gs.goods_id=".$id." order by id asc" );
		foreach($item_salesroom as $k=>$v){
			$item_salesroom_smember =  M()->query("SELECT grs.*,sm.username,m.avatar FROM " . C('DB_PREFIX') .
					"eaterplanet_ecommerce_goods_relative_smember as  grs left join " . C('DB_PREFIX') ."eaterplanet_ecommerce_salesroom_member as sm on grs.smember_id=sm.id ".
					" left join " . C('DB_PREFIX') ."eaterplanet_ecommerce_member as m on sm.member_id=m.member_id ".
					" WHERE grs.gr_id=".$v['id']." order by id asc" );
			$item_salesroom[$k]['smember_list'] = $item_salesroom_smember;
		}
		$item['salesroom_list'] = $item_salesroom;
		return $item;
	}

	/**
     * @desc 获取商品规格值关联id数组
     * @param $goods_id
     * @return array
     */

	public function getGoodsOptionItemValueSpecIdsArray( $goods_id )
    {
		$option_item_value_collects = M('eaterplanet_ecommerce_goods_option_item_value')->where(array('goods_id' => $goods_id))->select();

        $data = array();
        if( !empty($option_item_value_collects) )
        {
            foreach( $option_item_value_collects as $collect )
            {
                $data[] =  $collect['option_item_ids'];
            }
        }
        return $data;
    }


	/**
     * @desc 删除不在规定数组中的商品规格值关联数据
     * @param $option_item_value_ids_arr
     * @param $spec_item_value_id_arr
     */
    public function deleteGoodsOptionValueSpecUninArray($option_item_value_ids_arr,$spec_item_value_id_arr , $goods_id)
    {
        $is_delete = 0;

        if( !empty($option_item_value_ids_arr) )
        {
            foreach( $option_item_value_ids_arr as $spec )
            {
                if(in_array( $spec , $spec_item_value_id_arr ) )
                {
                    $key = array_search($spec , $spec_item_value_id_arr);
                    if ($key !== false)
                        array_splice($spec_item_value_id_arr, $key, 1);

                }
            }
            if( !empty($spec_item_value_id_arr) )
            {
                $is_delete = 1;
            }
        }else{
            $is_delete = 1;
        }

        //如果可以删，要删除规格值 删除 规格项
        if( $is_delete )
        {
			M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $goods_id) )->delete();
			M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_id' => $goods_id) )->delete();
			M('eaterplanet_ecommerce_goods_option')->where( array('goods_id' => $goods_id) )->delete();
        }

    }


	public function modify_goods($type = 'normal')
	{
		global $_W;
		global $_GPC;

		$goods_id = I('get.id');
		$post_data = array();
		$post_data_goods = array();

		$post_data_goods['goodsname'] =  I('post.goodsname');
		$post_data_goods['subtitle'] = I('post.subtitle');
		$post_data_goods['grounding'] = I('post.grounding');
		$post_data_goods['is_index_show'] = I('post.is_index_show');
		$post_data_goods['price'] = I('post.price');
		$post_data_goods['productprice'] = I('post.productprice');
		$post_data_goods['card_price'] = I('post.card_price');
		$post_data_goods['costprice'] = I('post.costprice');
		$post_data_goods['sales'] = I('post.sales');
		$post_data_goods['showsales'] = I('post.showsales');
		$post_data_goods['dispatchtype'] = I('post.dispatchtype');
		$post_data_goods['dispatchid'] = I('post.dispatchid');
		$post_data_goods['index_sort'] = I('post.index_sort','','intval');
		$post_data_goods['dispatchprice'] = I('post.dispatchprice');
		$post_data_goods['codes'] = I('post.codes');
		$post_data_goods['weight'] = I('post.weight');
		$post_data_goods['total'] = I('post.total');
		$post_data_goods['hasoption'] = I('post.hasoption');
		$post_data_goods['credit'] = I('post.credit');
		$post_data_goods['buyagain'] = I('post.buyagain');
		$post_data_goods['buyagain_condition'] = I('post.buyagain_condition');
		$post_data_goods['buyagain_sale'] = I('post.buyagain_sale');

		if (defined('ROLE') && ROLE == 'agenter' ) {
			$supply_can_distribution_sale =  D('Home/Front')->get_config_by_name('supply_can_distribution_sale');
			if($supply_can_distribution_sale == 1){
				$post_data_goods['is_all_sale'] =  I('post.is_all_sale',0,'intval');
			}
		}else{
			$post_data_goods['is_all_sale'] =  I('post.is_all_sale',0,'intval');
		}

		$post_data_goods['is_seckill'] =  I('post.is_seckill',0,'intval');

		$post_data_goods['is_take_vipcard'] =  I('post.is_take_vipcard',0,'intval');


		if (defined('ROLE') && ROLE == 'agenter' ) {
			$supply_edit_goods_shenhe = D('Home/Front')->get_config_by_name('supply_edit_goods_shenhe');
			if( empty($supply_edit_goods_shenhe) )
			{
				$supply_edit_goods_shenhe = 0;
			}

			if($supply_edit_goods_shenhe)
			{
				$post_data_goods['grounding'] = 4;
			}

		}

		M('eaterplanet_ecommerce_goods')->where(array('id' => $goods_id))->save($post_data_goods);

		//find type ,modify somethings TODO...

		$pin_type_arr = array(
					'pin'=>'主流团',
					'lottery'=>'抽奖团',
					'oldman'=>'老人团',
					'newman'=>'新人团',
					'commiss'=>'佣金团',
					'ladder'=>'阶梯团',
					'flash'=>'快闪团',
				);

		$pin_type =  array_keys($pin_type_arr);

		if( in_array($type, $pin_type) )
		{
			//插入 拼团商品表 eaterplanet_ecommerce_good_pin
			$pin_data = array();
			$pin_data['pinprice'] = I('post.pinprice');
			$pin_data['pin_count'] = I('post.pin_count');
			$pin_data['pin_hour'] = I('post.pin_hour');

			$pin_data['is_commiss_tuan'] = I('post.is_commiss_tuan', 0);

			$pin_data['is_zero_open'] = 0;

			if($pin_data['is_commiss_tuan'] == 1)
			{
				$pin_data['is_zero_open'] = I('post.is_zero_open', 0);
			}



			$pin_data['is_newman'] = I('post.is_newman', 0);

			$commiss_tuan_money1 = I('post.commiss_tuan_money1', 0);
			$commiss_tuan_money2 = I('post.commiss_tuan_money2', 0);

			if( isset($commiss_tuan_money1) && $commiss_tuan_money1 >0 )
			{
				$pin_data['commiss_type'] = 0;
				$pin_data['commiss_money'] = $commiss_tuan_money1;

			}else{
				$pin_data['commiss_type'] = 1;
				$pin_data['commiss_money'] = $commiss_tuan_money2;
			}



			$time_st = I('post.time');
			$pin_data['begin_time'] = strtotime( $time_st['start'].':00' );
			$pin_data['end_time'] = strtotime( $time_st['end'].':00' );

			//拼团返利设置
			$pin_data['is_pintuan_rebate'] = I('post.is_pintuan_rebate',0);
			if($pin_data['is_pintuan_rebate'] == 1){
				$pin_data['random_delivery_count'] = I('post.random_delivery_count',0);
				$pin_data['rebate_reward'] = I('post.rebate_reward', 1);
				$pin_data['reward_point'] = I('post.reward_point', 0);
				$pin_data['reward_balance'] = I('post.reward_balance', 0);
			}

			M('eaterplanet_ecommerce_good_pin')->where( array('goods_id' => $goods_id) )->save( $pin_data );

		}


		$cates = I('post.cate_mult');

		if( !empty($cates) )
		{
			//删除商品的分类

			M('eaterplanet_ecommerce_goods_to_category')->where( array('goods_id' => $goods_id) )->delete();

			foreach($cates as $cate_id)
			{
				$post_data_category = array();
				$post_data_category['cate_id'] = $cate_id;
				$post_data_category['goods_id'] = $goods_id;

				M('eaterplanet_ecommerce_goods_to_category')->add($post_data_category);
			}
		}else{
			M('eaterplanet_ecommerce_goods_to_category')->where( array('goods_id' => $goods_id) )->delete();

		}

		//核销begin TODO.....
		$is_only_hexiao = I('post.is_only_hexiao',0);
		//if($is_only_hexiao == 1){
			$hx_time = time();
			$item_salesroombase = M('eaterplanet_ecommerce_goods_salesroombase')->where( array('goods_id' => $goods_id) )->find();
			if(!empty($item_salesroombase)){
				$hx_data = array();
				$hx_data['goods_id'] = $goods_id;
				$hx_data['is_only_hexiao'] = $is_only_hexiao;
				$hx_data['hexiao_type'] = I('post.hexiao_type',0);
				$hx_data['hx_one_goods_time'] = I('post.hx_one_goods_time',0);
				$hx_data['hx_expire_type'] = I('post.hx_expire_type',0);
				$hx_data['hx_expire_day'] = I('post.hx_expire_day');
				$hx_data['hx_expire_begin_time'] = $item_salesroombase['hx_expire_begin_time'];
				if($hx_data['hx_expire_type'] == 1){
					$hx_data['hx_expire_end_time'] = strtotime(I('post.hx_expire_end_time'));
				}else{
					if(empty($hx_data['hx_expire_day'])){
						$hx_data['hx_expire_day'] = 90;
					}
					$hx_data['hx_expire_end_time'] = $item_salesroombase['hx_expire_begin_time']+$hx_data['hx_expire_day']*24*60*60;
				}
				$hx_data['hx_assign_salesroom'] = I('post.hx_assign_salesroom',0);
				$hx_data['hx_auto_off'] = I('post.hx_auto_off',0);
				$hx_data['hx_auto_off_time'] = I('post.hx_auto_off_time',0);
				M('eaterplanet_ecommerce_goods_salesroombase')->where( array('id' => $item_salesroombase['id']) )->save($hx_data);
				//删除关联核销员
				$salesroom_list = M('eaterplanet_ecommerce_goods_relative_salesroom')->where( array('goods_id' => $goods_id) )->select();
				foreach($salesroom_list as $sk=>$sv){
					M('eaterplanet_ecommerce_goods_relative_smember')->where( array('gr_id' => $sv['id']) )->delete();
				}
				//删除关联门店
				M('eaterplanet_ecommerce_goods_relative_salesroom')->where( array('goods_id' => $goods_id) )->delete();

				if($hx_data['hx_assign_salesroom'] == 1){//指定门店
					$goods_room_ids = I('post.goods_room_ids','');
					$goods_is_hx_member = I('post.goods_is_hx_member','');
					$goods_room_smember = I('post.goods_room_smember','');
					if(!empty($goods_room_ids)){
						$goods_room_array = explode(',',$goods_room_ids);
						foreach($goods_room_array as $grv){
							$goods_room_smember_ids = $goods_room_smember[$grv];
							$goods_room_data = array();
							$goods_room_data['salesroom_id'] = $grv;
							$goods_room_data['goods_id'] = $goods_id;
							$goods_room_data['is_hx_member'] = $goods_is_hx_member[$grv];
							if($goods_is_hx_member[$grv] == 1 && empty($goods_room_smember_ids)){
								$goods_room_data['is_hx_member'] = 0;
							}
							$goods_room_data['addtime'] = $hx_time;
							$gr_id = M('eaterplanet_ecommerce_goods_relative_salesroom')->add($goods_room_data);
							if($gr_id !== false){
								$goods_room_smember_ids = $goods_room_smember[$grv];
								if($goods_is_hx_member[$grv] == 1 && !empty($goods_room_smember_ids)){
									$smember_ids = explode(',',$goods_room_smember_ids);
									foreach($smember_ids as $sv){
										$room_smember_data = array();
										$room_smember_data['salesroom_id'] = $grv;
										$room_smember_data['gr_id'] = $gr_id;
										$room_smember_data['smember_id'] = $sv;
										$room_smember_data['addtime'] = $hx_time;
										M('eaterplanet_ecommerce_goods_relative_smember')->add($room_smember_data);
									}
								}
							}
						}
					}
				}
			}else{
				$hx_data = array();
				$hx_time = time();
				$hx_data['goods_id'] = $goods_id;
				$hx_data['is_only_hexiao'] = $is_only_hexiao;
				$hx_data['hexiao_type'] = I('post.hexiao_type',0);
				$hx_data['hx_one_goods_time'] = I('post.hx_one_goods_time',0);
				$hx_data['hx_expire_type'] = I('post.hx_expire_type',0);
				$hx_data['hx_expire_day'] = I('post.hx_expire_day');
				$hx_data['hx_expire_begin_time'] = $hx_time;
				if($hx_data['hx_expire_type'] == 1){
					$hx_data['hx_expire_end_time'] = strtotime(I('post.hx_expire_end_time'));
				}else{
					if(empty($hx_data['hx_expire_day'])){
						$hx_data['hx_expire_day'] = 90;
					}
					$hx_data['hx_expire_end_time'] = $hx_time+$hx_data['hx_expire_day']*24*60*60;
				}
				$hx_data['hx_assign_salesroom'] = I('post.hx_assign_salesroom',0);
				$hx_data['addtime'] = $hx_time;
				M('eaterplanet_ecommerce_goods_salesroombase')->add($hx_data);
				if($hx_data['hx_assign_salesroom'] == 1){//指定门店
					$goods_room_ids = I('post.goods_room_ids','');
					$goods_is_hx_member = I('post.goods_is_hx_member','');
					$goods_room_smember = I('post.goods_room_smember','');
					if(!empty($goods_room_ids)){
						$goods_room_array = explode(',',$goods_room_ids);
						foreach($goods_room_array as $grv){
							$goods_room_smember_ids = $goods_room_smember[$grv];
							$goods_room_data = array();
							$goods_room_data['salesroom_id'] = $grv;
							$goods_room_data['goods_id'] = $goods_id;
							$goods_room_data['is_hx_member'] = $goods_is_hx_member[$grv];
							if($goods_is_hx_member[$grv] == 1 && empty($goods_room_smember_ids)){
								$goods_room_data['is_hx_member'] = 0;
							}
							$goods_room_data['addtime'] = $hx_time;
							$gr_id = M('eaterplanet_ecommerce_goods_relative_salesroom')->add($goods_room_data);
							if($gr_id !== false){
								$goods_room_smember_ids = $goods_room_smember[$grv];
								if($goods_is_hx_member[$grv] == 1 && !empty($goods_room_smember_ids)){
									$smember_ids = explode(',',$goods_room_smember_ids);
									foreach($smember_ids as $sv){
										$room_smember_data = array();
										$room_smember_data['salesroom_id'] = $grv;
										$room_smember_data['gr_id'] = $gr_id;
										$room_smember_data['smember_id'] = $sv;
										$room_smember_data['addtime'] = $hx_time;
										M('eaterplanet_ecommerce_goods_relative_smember')->add($room_smember_data);
									}
								}
							}
						}
					}
				}
			}
		//}
		//核销end


		//eaterplanet_ecommerce_goods_images
		$thumbs = I('post.thumbs');

		if( !empty($thumbs) )
		{
			M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $goods_id) )->delete();

			foreach($thumbs as $thumbs)
			{
				$post_data_thumbs = array();
				$post_data_thumbs['goods_id'] = $goods_id;
				$post_data_thumbs['image'] = save_media($thumbs);
				$post_data_thumbs['thumb'] = save_media( resize($thumbs,100,100));

				M('eaterplanet_ecommerce_goods_images')->add($post_data_thumbs);
			}
		}
		//eaterplanet_ecommerce_good_common

		$post_data_common =  array();
		$post_data_common['quality'] = I('post.quality');
		$post_data_common['seven'] =  I('post.seven');
		$post_data_common['repair'] = I('post.repair');

		$labelname = I('post.labelname');
		$post_data_common['labelname'] = serialize($labelname);
		$post_data_common['share_title'] = I('post.share_title');
		$post_data_common['share_description'] = I('post.share_description');


		$post_data_common['content'] = I('post.content','','htmlspecialchars');
		$post_data_common['pick_up_type'] = I('post.pick_up_type');
		$post_data_common['pick_up_modify'] = I('post.pick_up_modify');
		$post_data_common['one_limit_count'] = I('post.one_limit_count');
		$post_data_common['oneday_limit_count'] = I('post.oneday_limit_count');
		$post_data_common['total_limit_count'] = I('post.total_limit_count');
		$post_data_common['goods_start_count'] = I('post.goods_start_count');
		$community_head_commission = I('post.community_head_commission');
		$is_community_head_commission = I('post.is_community_head_commission');
		$post_data_common['is_community_head_commission'] = $is_community_head_commission;

		$post_data_common['is_show_arrive'] = I('post.is_show_arrive');
		$post_data_common['diy_arrive_switch'] = I('post.diy_arrive_switch');
		$post_data_common['diy_arrive_details'] = I('post.diy_arrive_details');

		$post_data_common['is_new_buy'] = I('post.is_new_buy');
		$post_data_common['is_spike_buy'] = I('post.is_spike_buy');

		if(isset($community_head_commission))
		{
			$post_data_common['community_head_commission'] = I('post.community_head_commission');
		}


		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$supply_can_goods_sendscore =  D('Home/Front')->get_config_by_name('supply_can_goods_sendscore');
			if($supply_can_goods_sendscore == 1){
				$post_data_common['is_modify_sendscore'] = I('post.is_modify_sendscore',0);
				$post_data_common['send_socre'] = I('post.send_socre');
			}
		}else{
			$post_data_common['is_modify_sendscore'] = I('post.is_modify_sendscore',0);
			$post_data_common['send_socre'] = I('post.send_socre');

		}
		$post_data_common['is_mb_level_buy'] = I('post.is_mb_level_buy',1);

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$supper_info = get_agent_logininfo();

			$post_data_common['supply_id'] = $supper_info['id'];
		}else{
			$post_data_common['supply_id'] = I('post.supply_id');
		}



		$time = I('post.time');

		$post_data_common['begin_time'] = strtotime( $time['start'] );
		$post_data_common['end_time'] = strtotime( $time['end'] );

		$big_img =I('post.big_img');
		$goods_share_image =I('post.goods_share_image');

		$post_data_common['big_img'] = save_media($big_img);

		$post_data_common['goods_share_image'] = save_media($goods_share_image);
		$post_data_common['video'] = save_media(I('post.video'));

		$post_data_common['video'] = $this->check_douyin_video($post_data_common['video']);


		$post_data_common['print_sub_title'] = I('post.print_sub_title');

		$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');

		if( empty($is_open_fullreduction) )
		{
			$post_data_common['is_take_fullreduction'] = 1;
		}else if( $is_open_fullreduction ==0 )
		{

		}else if($is_open_fullreduction ==1){
			$post_data_common['is_take_fullreduction'] =  I('post.is_take_fullreduction' ,1);
		}

		if($post_data_common['is_take_fullreduction'] == 1 && $post_data_common['supply_id'] > 0)
		{
			$supply_info = M('eaterplanet_ecommerce_supply')->field('type')->where( array('id' => $post_data_common['supply_id'] ) )->find();
			if( !empty($supply_info) && $supply_info['type'] == 1 )
			{
				$post_data_common['is_take_fullreduction'] = 0;
			}
		}

		//begin
		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$is_modify_head_commission = I('post.is_modify_head_commission','0','intval');
			if( isset($is_modify_head_commission) )
			{
				$post_data_common['is_modify_head_commission'] = $is_modify_head_commission;

				if($post_data_common['is_modify_head_commission'] == 1)
				{
					$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();

					$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');
					$default_comunity_money = D('Home/Front')->get_config_by_name('head_commission_levelname');

					$list_default = array(
							array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
					);

					$community_head_level = array_merge($list_default, $community_head_level);

					$community_head_commission_modify = array();

					foreach($community_head_level as $kk => $vv)
					{
						$community_head_commission_modify['head_level'.$vv['id']] = I('post.head_level'.$vv['id']);
					}
					if( !isset($is_community_head_commission)){
						$post_data_common['community_head_commission'] = $community_head_commission_modify['head_level0'];
					}

					$post_data_common['community_head_commission_modify'] = serialize($community_head_commission_modify);
				}

			}else{
				$post_data_common['is_modify_head_commission'] = 0;
			}
		}else{
			$is_modify_head_commission = I('post.is_modify_head_commission','0','intval');
			if( isset($is_modify_head_commission) )
			{
				$post_data_common['is_modify_head_commission'] = $is_modify_head_commission;

				if($post_data_common['is_modify_head_commission'] == 1)
				{
					$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();

					$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');
					$default_comunity_money = D('Home/Front')->get_config_by_name('head_commission_levelname');

					$list_default = array(
						array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
					);

					$community_head_level = array_merge($list_default, $community_head_level);

					$community_head_commission_modify = array();

					foreach($community_head_level as $kk => $vv)
					{
						$community_head_commission_modify['head_level'.$vv['id']] = I('post.head_level'.$vv['id']);
					}
					if( !isset($is_community_head_commission)){
						$post_data_common['community_head_commission'] = $community_head_commission_modify['head_level0'];
					}
					$post_data_common['community_head_commission_modify'] = serialize($community_head_commission_modify);
				}

			}else{
				$post_data_common['is_modify_head_commission'] = 0;
			}
		}

		//end

		$post_data_common['is_only_express'] = I('post.is_only_express',0);

		$post_data_common['is_only_hexiao']  = $is_only_hexiao;

		$post_data_common['is_limit_levelunbuy'] = I('post.is_limit_levelunbuy',0);

		$post_data_common['is_limit_vipmember_buy'] = I('post.is_limit_vipmember_buy',0);

		$post_data_common['packing_free'] = I('post.packing_free',0);

		if( $post_data_common['is_only_express'] == 1 )
		{
			$post_data_common['is_only_distribution'] = 0;
		}else{

			$is_only_distribution = I('post.is_only_distribution');

			$post_data_common['is_only_distribution'] = $is_only_distribution;
		}


		$relative_goods_list = array();

		$limit_goods_list = I('post.limit_goods_list');

		if( isset($limit_goods_list) && !empty($limit_goods_list) )
		{
			$limit_goods_list =  explode(',', $limit_goods_list);
			foreach($limit_goods_list as $tp_val )
			{
				if($tp_val != $goods_id)
				{
					$relative_goods_list[] = $tp_val;
				}
			}
		}
		$post_data_common['relative_goods_list'] = serialize($relative_goods_list);

		$post_data_common['has_mb_level_buy'] = I('post.has_mb_level_buy',0,'intval');
		$level_id_list = I('post.level_id');
		$discount_list = I('post.discount');
		$mb_level_buy_list = array();
		if(isset($level_id_list) && !empty($level_id_list)){
			for($i = 0;$i < count($level_id_list);$i++){
				$level_list = array();
				$level_list['level_id'] = $level_id_list[$i];
				if(!is_numeric($discount_list[$i])){
					$level_list['discount'] = '';
				}else{
					if($discount_list[$i] < 0 && $discount_list[$i] > 100){
						$level_list['discount'] = 0;
					}else{
						$level_list['discount'] = $discount_list[$i];
					}
				}
				$mb_level_buy_list[] = $level_list;
			}
		}
		$post_data_common['mb_level_buy_list'] = serialize($mb_level_buy_list);

		M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $goods_id) )->save($post_data_common);

		//M('eaterplanet_ecommerce_goods_option')->where( array('goods_id' => $goods_id) )->delete();
		//M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_id' => $goods_id) )->delete();
		//M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $goods_id) )->delete();



		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');

		$hasoption = I('post.hasoption',0,'intval');
		//规格
		if( intval($hasoption) == 1 )
		{
			$save_goods_option_arr = array();//有用的goods_option_id
			$save_goods_option_item_arr = array();// 有用的 goods_option_item
			$save_goods_option_item_value_arr = array();// 有用的 goods_option_item_value


			$mult_option_item_dan_key = array();
			$replace_option_item_id_arr = array();//需要更替的option_item_id



			$spec_id = I('post.spec_id');
			$option_ids_arr = I('post.option_ids');
			//begin 换算多规格算法

			$option_item_value_ids_arr = $this->getGoodsOptionItemValueSpecIdsArray( $goods_id );


			$this->deleteGoodsOptionValueSpecUninArray( $option_item_value_ids_arr, $option_ids_arr , $goods_id );

			//end  换算多规格算法


			if( !empty($spec_id)  )
			{
				$option_order = 1;

				$spec_title_arr = I('post.spec_title');
				foreach($spec_id as $spec_id)
				{
					//规格标题
					$cur_spec_title = $spec_title_arr[$spec_id];

					$goods_option_data = array();
					$goods_option['goods_id'] = $goods_id;
					$goods_option['title'] = $cur_spec_title;
					$goods_option['displayorder'] = $option_order;


					//查找是否存在这个规格

					$ck_goods_option = M('eaterplanet_ecommerce_goods_option')->where( array('id' => $spec_id ) )->find();

					if( !empty($ck_goods_option) )
					{
						M('eaterplanet_ecommerce_goods_option')->where( array('id' => $spec_id ) )->save($goods_option);
						$option_id = $spec_id;
					}else{
						$option_id = M('eaterplanet_ecommerce_goods_option')->add($goods_option);
					}

					$save_goods_option_arr[] = $option_id;


					$spec_item_title_arr =  I('post.spec_item_title_'.$spec_id);
					if(!empty($spec_item_title_arr))
					{
						$item_sort = 1;
						$i = 0;
						$j = 0;
						foreach($spec_item_title_arr as $key =>$item_title)
						{
							$spec_item_thumb_arr = I('post.spec_item_thumb_'.$spec_id);
							$goods_option_item_data = array();

							$goods_option_item_data['goods_id'] = $goods_id;
							$goods_option_item_data['goods_option_id'] = $option_id;
							$goods_option_item_data['title'] = $item_title;
							$goods_option_item_data['thumb'] = $spec_item_thumb_arr[$key];
							$goods_option_item_data['displayorder'] = $item_sort;

							//$option_item_id = M('eaterplanet_ecommerce_goods_option_item')->add($goods_option_item_data);

							$spec_item_id_kk = I('post.spec_item_id_'.$spec_id);

							$option_item_id = $spec_item_id_kk[$key];

							$ck_option_item = M('eaterplanet_ecommerce_goods_option_item')->where( array('id' => $option_item_id ) )->find();

							if( !empty($ck_option_item) )
							{
								M('eaterplanet_ecommerce_goods_option_item')->where( array('id' => $option_item_id ) )->save( $goods_option_item_data );

							}else{
								$new_option_item_id = M('eaterplanet_ecommerce_goods_option_item')->add( $goods_option_item_data );

								$replace_option_item_id_arr[$option_item_id] = $new_option_item_id;

								$option_item_id = $new_option_item_id;

							}
							$save_goods_option_item_arr[] = $option_item_id;


							//从小到大的排序
							$mult_option_item_dan_key[ $spec_item_id_kk[$key] ] = $option_item_id;
							$item_sort++;
							$i++;
						}
					}else{
						M('eaterplanet_ecommerce_goods_option')->where( array('id' => $id) )->delete();
					}
					$option_order++;
				}

				//开始清理无效的 规格 规格项
				if( empty($save_goods_option_arr) )
				{
					M('eaterplanet_ecommerce_goods_option')->where( array('goods_id' => $goods_id ) )->delete();

				}else{
					$save_goods_option_str = implode(',', $save_goods_option_arr );

					M('eaterplanet_ecommerce_goods_option')->where( 'goods_id=' . $goods_id.' and id not in('.$save_goods_option_str.')' )->delete();
				}

				if( empty($save_goods_option_item_arr) )
				{

					M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_id' => $goods_id ) )->delete();

				}else{

					$save_goods_option_item_str = implode(',', $save_goods_option_item_arr );

					M('eaterplanet_ecommerce_goods_option_item')->where('goods_id=' . $goods_id.' and id not in('.$save_goods_option_item_str.')')->delete();

				}
			}



			/**
            $option_ids_arr2 = $_REQUEST['option_ids'];

            var_dump($option_ids_arr);
            echo '<br/>';

            var_dump($option_ids_arr2);

            die();
             * **/

			/**
				array(9) {
				  [0]=>
				  string(9) "1022_1029"
				  [1]=>
				  string(9) "1022_1034"
				  [2]=>
				  string(9) "1022_1035"
				  [3]=>
				  string(9) "1023_1029"
				  [4]=>
				  string(9) "1023_1034"
				  [5]=>
				  string(9) "1023_1035"
				  [6]=>
				  string(37) "L7uW7b72wycNYcw7r7CimqwC77Nyywui _1029"
				  [7]=>
				  string(37) "L7uW7b72wycNYcw7r7CimqwC77Nyywui_1034"
				  [8]=>
				  string(37) "L7uW7b72wycNYcw7r7CimqwC77Nyywui_1035"
				}
				array(1) {
				  ["L7uW7b72wycNYcw7r7CimqwC77Nyywui"]=>
				  string(4) "1037"
				}

			**/

			$mdata = I('post.');
			$total = 0;
			foreach($option_ids_arr as $val)
			{
				$option_item_ids = '';
				$option_item_ids_arr = array();

				$key_items = explode('_', $val);

				$new_val = array();

				foreach($key_items as $vv)
				{

					if( isset($replace_option_item_id_arr[$vv]) )
					{
						$option_item_ids_arr[] = $replace_option_item_id_arr[$vv];
					}else{
						$option_item_ids_arr[] = $mult_option_item_dan_key[$vv];
					}

					$new_val[] = $vv;
				}

				//asort($new_val);
				//$val = implode('_', $new_val);


				asort($option_item_ids_arr);
				$option_item_ids = implode('_', $option_item_ids_arr);

				$eaterplanet_goods_option_item_value_data = array();
				$eaterplanet_goods_option_item_value_data['goods_id'] = $goods_id;
				$eaterplanet_goods_option_item_value_data['option_item_ids'] = $option_item_ids;

				$productprice = I('post.option_productprice_'.$val);

				$eaterplanet_goods_option_item_value_data['productprice'] =  $productprice;

				$pinprice = I('post.option_presell_'.$val);

				$eaterplanet_goods_option_item_value_data['pinprice'] =  $pinprice;

				$marketprice = I('post.option_marketprice_'.$val);

				$eaterplanet_goods_option_item_value_data['marketprice'] =  $marketprice;

				if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
				{
					$card_price = I('post.option_cardprice_'.$val);

					$eaterplanet_goods_option_item_value_data['card_price'] =  $card_price;
				}

				$stock = I('post.option_stock_'.$val);

				$eaterplanet_goods_option_item_value_data['stock'] =  $stock;

				$costprice = I('post.option_costprice_'.$val);

				$eaterplanet_goods_option_item_value_data['costprice'] = $costprice;

				$goodssn = I('post.option_goodssn_'.$val);

				$eaterplanet_goods_option_item_value_data['goodssn'] =  $goodssn;

				$weight = I('post.option_weight_'.$val);

				$eaterplanet_goods_option_item_value_data['weight'] =  $weight;

				$title = I('post.option_title_'.$val);

				$eaterplanet_goods_option_item_value_data['title'] =  $title;


				$total += $eaterplanet_goods_option_item_value_data['stock'];

				//option_id_1979 TODO

				$option_item_value_id = I('post.option_id_'.$val);


				$ck_option_item_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('id' => $option_item_value_id ) )->find();

				if( !empty($ck_option_item_value) )
				{

					M('eaterplanet_ecommerce_goods_option_item_value')->where( array('id' => $option_item_value_id ) )->save( $eaterplanet_goods_option_item_value_data );

				}else{

					$option_item_value_id = M('eaterplanet_ecommerce_goods_option_item_value')->add( $eaterplanet_goods_option_item_value_data );
				}
				$save_goods_option_item_value_arr[] = $option_item_value_id;


				//M('eaterplanet_ecommerce_goods_option_item_value')->add($eaterplanet_goods_option_item_value_data);
			}

			if( empty($save_goods_option_item_value_arr) )
			{
				M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $goods_id ) )->delete();

			}else{
				$save_goods_option_item_value_str = implode(',', $save_goods_option_item_value_arr );

				M('eaterplanet_ecommerce_goods_option_item_value')->where('goods_id=' . $goods_id.' and id not in('.$save_goods_option_item_value_str.')' )->delete();

			}

			//更新库存 total
			$up_goods_data = array();
			$up_goods_data['total'] = $total;

			M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id) )->save( $up_goods_data );
		}
		else{

			M('eaterplanet_ecommerce_goods_option')->where( array('goods_id' => $goods_id ) )->delete();

			M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_id' => $goods_id ) )->delete();

			M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $goods_id ) )->delete();
		}

		if( !empty($save_goods_option_arr) )
		{
			foreach( $save_goods_option_arr as $k_option_id )
			{
				$tp_item_val = M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_option_id' => $k_option_id ) )->find();

				if( empty($tp_item_val) )
				{
					M('eaterplanet_ecommerce_goods_option')->where( array('id' => $k_option_id ) )->delete();
				}
			}
		}



		//stock hascommission

		//规格插入
		//eaterplanet_ecommerce_good_commiss

		M('eaterplanet_ecommerce_good_commiss')->where( array('goods_id' => $goods_id ) )->delete();


		$post_data_commiss = array();
		$post_data_commiss['goods_id'] = $goods_id;
		$post_data_commiss['nocommission'] = I('post.nocommission',0,'intval');
		$post_data_commiss['hascommission'] = I('post.hascommission',0,'intval');
		$post_data_commiss['commission_type'] = I('post.commission_type',0,'intval');
		$post_data_commiss['commission1_rate'] = I('post.commission1_rate');
		$post_data_commiss['commission1_pay'] = I('post.commission1_pay');
		$post_data_commiss['commission2_rate'] = I('post.commission2_rate');
		$post_data_commiss['commission2_pay'] = I('post.commission2_pay');
		$post_data_commiss['commission3_rate'] = I('post.commission3_rate');
		$post_data_commiss['commission3_pay'] = I('post.commission3_pay');

		M('eaterplanet_ecommerce_good_commiss')->add( $post_data_commiss );

		//变更预售商品begin
        $new_goods_type_info = M('eaterplanet_ecommerce_goods')->field('type')->where( ['id' => $goods_id ] )->find();
        if($new_goods_type_info['type'] == 'presale' )
        {
            D('Seller/GoodsPresale')->modifyGoodsPresale( $goods_id );
        }
        //end
        //虚拟卡密
        if( isset($_POST['is_virtualcard_goods']) && $_POST['is_virtualcard_goods'] == 1 )
        {
            D('Seller/VirtualCard')->modifyGoodsVirtualCard( $goods_id );
        }

		D('Seller/Redisorder')->sysnc_goods_total($goods_id);

	}

}
?>
