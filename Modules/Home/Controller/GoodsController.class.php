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
namespace Home\Controller;


class GoodsController extends CommonController {

	/**
		获取商品规格数据
	**/
	public function get_goods_option_data()
	{
		$gpc = I('request.');

		$id = $gpc['id'];
		$token = $gpc['token'];
		$sku_str = $gpc['sku_str'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		$member_id = $weprogram_token['member_id'];


		$goods_option_mult_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $id) )->order('id asc')->select();

        $goods_option_mult_value_ref = array();
        foreach ($goods_option_mult_value as $key => $val) {

			$image_info = D('Home/Front')->get_goods_sku_image($val['id']);
			$val['image'] = isset($image_info['thumb']) ? tomedia($image_info['thumb']) : '';

			$val['pin_price'] = $val['pinprice'];
			$val['dan_price'] = $val['marketprice'];

            $goods_option_mult_value[$key] = $val;
            $goods_option_mult_value_ref[$val['option_item_ids']] = $val;
        }

		$need_data = array();

		//$level_info = $goods_model->get_member_level_info($member_id, $id);
		$level_info = array();
		$member_disc = 100;
		if( !empty($level_info) )
		{
			$member_disc = $level_info['member_discount'];
		}

		//$max_member_level = M('member_level')->order('level desc')->find();
		$max_member_level = array();


		$goods_option_mult_value_ref[$sku_str]['member_pin_price'] =  round( ($goods_option_mult_value_ref[$sku_str]['pin_price'] * $member_disc) / 100 ,2);
		$goods_option_mult_value_ref[$sku_str]['memberprice'] =  round( ($goods_option_mult_value_ref[$sku_str]['dan_price'] * $member_disc) / 100 ,2);

		$goods_option_mult_value_ref[$sku_str]['max_member_pin_price'] = 0;
		$goods_option_mult_value_ref[$sku_str]['max_memberprice'] = 0;

		if( !empty($max_member_level) )
		{
			$goods_option_mult_value_ref[$sku_str]['max_member_pin_price'] =  round( ($goods_option_mult_value_ref[$sku_str]['pin_price'] * (100 - $max_member_level['discount']) )  / 100 ,2);
			$goods_option_mult_value_ref[$sku_str]['max_memberprice'] =  round( ($goods_option_mult_value_ref[$sku_str]['dan_price'] * (100 - $max_member_level['discount']) )  / 100 ,2);
		}

        $need_data['value'] = $goods_option_mult_value_ref[$sku_str];

		echo json_encode( array('code' =>0 , 'data' =>$need_data ) );
		die();

	}

	public function getQuan()
    {
		$_GPC = I('request.');

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];




        $result = array('code' => 0,'msg' => '被抢光啦');
        $quan_id = $_GPC['quan_id'];
        if($quan_id >0){

		   $res =   D('Home/Voucher')->send_user_voucher_byId($quan_id,$member_id,true);

           //1 被抢光了 2 已领过  3  领取成功
           $mes_arr = array(1 => '抢光了',2 => '已领过', 3 => '领取成功', 4 => '新人专享优惠券');

		   $result['code'] = $res;
           $result['msg'] = $mes_arr[$res];

		   $result['is_hide'] =  D('Home/Voucher')->checkUserCanGetOneVoucher($quan_id,$member_id,true);
        }
        echo json_encode($result);
        die();
    }

	public function get_seller_quan()
	{
		$_GPC = I('request.');

		$token =  isset( $_GPC['token'] ) && !empty($_GPC['token']) ? $_GPC['token']:'';

        $member_id = 0;

		if( !empty($token) )
        {
            $weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
            if( !empty($weprogram_token) )
            {
                $member_id = $weprogram_token['member_id'];
            }
        }




		$where = "";

		$where = " and (total_count =-1 or total_count>send_count) and is_index_alert=0 and is_index_show=1 and ((timelimit=1 and end_time>".time().") or timelimit=0 ) ";


		$quan_list = M()->query("select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_coupon where 1 {$where} order by displayorder desc ,id asc limit 100 ");

		$need_list = array();


		foreach($quan_list as  $key => $val )
		{
			$val['thumb'] = tomedia($val['thumb']);
			$voucher_id = $val['id'];

			$voucher_info = M('eaterplanet_ecommerce_coupon')->where( array('id' => $voucher_id ) )->find();

			if($val['total_count'] != -1 &&  $voucher_info['total_count'] <= $voucher_info['send_count']){
				continue;
			}else {

			  $get_count = M('eaterplanet_ecommerce_coupon_list')->where( array('user_id' => $member_id,'voucher_id' => $voucher_id ) )->count();

			  if($voucher_info['person_limit_count'] > 0 && $voucher_info['person_limit_count'] <= $get_count) {
				 continue;
			  }
			}

			//判断是否新人券
			if( $voucher_info['is_new_man'] == 1 )
			{
				//检测是否购买过
				$od_status = "1,2,4,6,7,8,9,10,11,12,14";
				$buy_count_sql = "select count(order_id) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_order
							where order_status_id in ({$od_status}) and member_id={$member_id} " ;

				$buy_count_arr = M()->query($buy_count_sql);

				$buy_count = $buy_count_arr[0]['count'];

				if( !empty($buy_count) && $buy_count >0 )
				{
					continue;
				}
			}

			$need_list[$key] = $val;
		}




		$where2 = "";

		$where2 = " and (total_count=-1 or  total_count>send_count)  and is_index_alert =1  and (end_time>".time()." or timelimit =0 ) ";

		$quan_list2 = M()->query("select * from ".C('DB_PREFIX')."eaterplanet_ecommerce_coupon where 1 {$where2} order by displayorder desc ,id asc limit 100 ");

		$need_list2 = array();


		//if( !empty($member_id) && $member_id > 0 )
		//{
			foreach($quan_list2 as  $key => $val )
			{
				$val['thumb'] = tomedia($val['thumb']);

				$voucher_id = $val['id'];

				$voucher_info = M('eaterplanet_ecommerce_coupon')->where( array('id' => $voucher_id ) )->find();

				if($voucher_info['total_count'] != -1 && $voucher_info['total_count'] <= $voucher_info['send_count']){
					continue;
				}else {

				  $get_count = M('eaterplanet_ecommerce_coupon_list')->where( array('user_id' => $member_id,'voucher_id' => $voucher_id ) )->count();

				  if($voucher_info['person_limit_count'] > 0 && $voucher_info['person_limit_count'] <= $get_count) {
					 continue;
				  }
				}

				if( $member_id > 0 )
				{	//判断是否新人券
					if( $voucher_info['is_new_man'] == 1 )
					{
						//检测是否购买过
						$od_status = "1,2,4,6,7,8,9,10,11,12,14";

						$buy_count_sql = "select count(order_id) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_order
									where order_status_id in ({$od_status}) and member_id={$member_id} " ;

						$buy_count_arr = M()->query($buy_count_sql);

						$buy_count = $buy_count_arr[0]['count'];


						if( !empty($buy_count) && $buy_count >0 )
						{
							continue;
						}
					}

					//如果有未使用完的就不送了吧
					if( $member_id > 0 )
					{
						 $get_unuse_count = M('eaterplanet_ecommerce_coupon_list')->where( array('consume'=>'N','user_id' => $member_id,'voucher_id' => $voucher_id ) )->count();

						// if( empty($get_unuse_count) || $get_unuse_count <=0 )
						// {
							 D('Home/Voucher')->send_user_voucher_byId($voucher_id,$member_id,true);
						// }
					}
				}

				$need_list2[$key] = $val;
			}
		//}


		echo json_encode( array('code' => 0, 'quan_list' => $need_list, 'alert_quan_list' => $need_list2) );
		die();
	}

	/**
		商品评价
	**/
	public function comment_info()
    {

		$_GPC = I('request.');

		$token = $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];


        $goods_id = $_GPC['goods_id'];

		$result = array('code' => 2);
		if( empty($member_id))
		{
			//$result['msg'] = '未登录';
			//echo json_encode($result);
			//die();
		}

		$goods_info = M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id ) )->find();

        if(empty($goods_info)) {
			$result = array('code' => 2);
			$result['msg'] = '没有此商品';
			echo json_encode($result);
            die();
        }

        $page =  isset($_GPC['page']) ? $_GPC['page'] : 1;
        $per_page = isset($_GPC['per_page']) ? $_GPC['per_page'] : 10;
       // $per_page = 4; C('DB_PREFIX')
        $offset = ($page - 1) * $per_page;

        $sql = "select o.*,m.username as name2,m.avatar as avatar2 from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_comment as o left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on o.member_id=m.member_id
			where  o.state =1  and o.goods_id = {$goods_id} order by o.add_time desc limit {$offset},{$per_page}";

		$list = M()->query($sql);


		foreach($list as $key => $val)
		{
			if( empty($val['user_name']) )
			{
				$val['name'] = $val['name2'];
				$val['avatar'] = tomedia($val['avatar2']);
			}else{
				$val['name'] = $val['user_name'];
				$val['avatar'] = tomedia($val['avatar']);
			}

			if($val['type'] == 0)
			{
				$order_goods_info = M('eaterplanet_ecommerce_order_goods')->field('order_goods_id')->where( array('order_id' => $val['order_id'],'goods_id' => $id) )->find();

				$order_option_info = M('eaterplanet_ecommerce_order_option')->field('value')->where( array('order_goods_id' => $order_goods_info['order_goods_id'],'order_id' => $val['order_id'] ) )->select();

				$option_arr = array();
				foreach($order_option_info as $option)
				{
					$option_arr[] = $option['value'];
				}
				$option_str = implode(',', $option_arr);
			}else{
				$option_str = '';
			}

			$img_str = unserialize($val['images']);
			if( !empty($val['images']) && $img_str != 'undefined' )
			{
				// $img_str = unserialize($val['images']);
				$img_list = explode(',', $img_str);

				if(!empty($img_list))
				{
					$need_img_list = array();
					foreach($img_list as $kk => $vv)
					{
						if( empty($vv) )
						{
							continue;
						}
						$vv =   tomedia($vv);
						$img_list[$kk] = $vv;
						$need_img_list[$kk] = $vv;
					}

					$val['images'] = $need_img_list;
				}else{
					$val['images'] = array();
				}
			} else {
				$val['images'] = array();
			}

			//<view class="time span">{{item.addtime}}</view>
			//		<view class="style span">{{item.option_str}} </view>
			$val['add_time'] = date('Y-m-d', $val['add_time']) ;
			$val['option_str'] = $option_str;
			$list[$key] = $val;
		}


        $result = array();
        $result['code'] = 0;
        $result['list'] = $list;

        if(!empty($list))
		{
			$result['code'] = 0;
		}else{
			$result['code'] = 1;
		}

        echo json_encode($result);
        die();

    }

	public function get_user_goods_qrcode()
	{
		$gpc = I('request.');

		$id = $gpc['goods_id'];
		$community_id = $gpc['community_id'];
		$token = $gpc['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		$member_id = $weprogram_token['member_id'];

		if(!empty($member_id)){

		$goods_share_image = M('eaterplanet_ecommerce_goods_share_image')->where( array('member_id' => $member_id, 'goods_id' => $id) )->find();

		if( !empty($goods_share_image) && false)
		{
			$result = array('code' => 0, 'image_path' => $goods_share_image['image_path']);
			echo json_encode($result);
			die();
		}else {

			$member_info = M('eaterplanet_ecommerce_member')->field('avatar,username,wepro_qrcode')->where( array('member_id' => $member_id) )->find();

			$goods_model = D('Home/Pingoods');

			if( !empty($member_info['wepro_qrcode']) && false)
			{
				$wepro_qrcode = $member_info['wepro_qrcode'];
			}else{
				$wepro_qrcode = $goods_model->get_goods_user_avatar($member_info['avatar'], $member_id, 6);
			}



			$goods_description = M('eaterplanet_ecommerce_good_common')->field('wepro_qrcode_image')->where(array('goods_id' => $id))->find();

			if( empty($goods_description['wepro_qrcode_image']) || true)
			{
				$goods_model->get_weshare_image($id , $member_id);

				$goods_description = M('eaterplanet_ecommerce_good_common')->field('wepro_qrcode_image')->where( array('goods_id' => $id) )->find();
			}


			$rocede_path = $goods_model->_get_goods_user_wxqrcode($id,$member_id,$community_id);

			$res = $goods_model->_get_compare_qrcode_bgimg('Uploads/image/'.$goods_description['wepro_qrcode_image'], $rocede_path,$wepro_qrcode,$member_info['username']);

			$url = D('Home/Front')->get_config_by_name('shop_domain').'/';


			$attachment_type_arr =  M('eaterplanet_ecommerce_config')->where( array('name' => 'attachment_type') )->find();

			$fullname = ROOT_PATH.$res['full_path'];

			$data = array();
			$data['member_id'] = $member_id;
			$data['goods_id']  = $id;

			if( $attachment_type_arr['value'] == 1 )
			{
				save_image_to_qiniu($fullname,$res['full_path']);
				$qiniu_url = D('Seller/Front')->get_config_by_name('qiniu_url');

				$data['image_path']  = $qiniu_url. $res['full_path'];
			}else if( $attachment_type_arr['value'] == 2 ){


				save_image_to_alioss($fullname,$res['full_path']);
				$alioss_url = D('Seller/Front')->get_config_by_name('alioss_url');

				$data['image_path']  = $alioss_url. $res['full_path'];
			}else if( $attachment_type_arr['value'] == 3 )
			{
				save_image_to_txyun($fullname,$res['full_path']);
				$txyun_url = D('Seller/Front')->get_config_by_name('tx_url');
				$data['image_path']  = $txyun_url. $res['full_path'];
			}else{
				$data['image_path']  = $url. $res['full_path'];
			}



			$data['addtime']  = time();

			M('eaterplanet_ecommerce_goods_share_image')->add($data);

			$result = array('code' => 0, 'image_path' => $data['image_path'] );
			echo json_encode($result);
			die();
		}

		}else{

			$result = array('code' => 1);
			echo json_encode($result);
			die();

		}
	}

	public function doPageUpload(){


		//$image_dir = ROOT_PATH.'Uploads/image/';
		//$send_path = 'goods'.date('Y-m-d').'/';
		//$image_dir .= $send_path;
		//RecursiveMkdir($image_dir);


		$uptypes = array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-png');
        $max_file_size = 10000000; //上传文件大小限制, 单位BYTE
		$send_path = "Uploads/image/goods/".date('Y-m-d')."/";
		$send_path_re = "goods/".date('Y-m-d')."/";
        $destination_folder = ROOT_PATH.$send_path; //上传文件路径

        $result = array();
        $result['code'] = 1;

		RecursiveMkdir($destination_folder);

        //fileinfo 检测begin
        $fip = finfo_open(FILEINFO_MIME_TYPE);
        $min_result = finfo_file($fip , $_FILES['upfile']['tmp_name']);
        fclose( $fip );
        $min_type_arr = array();
        $min_type_arr[] = 'image/jpeg';
        $min_type_arr[] = 'image/gif';
        $min_type_arr[] = 'image/jpg';
        $min_type_arr[] = 'image/png';
        $min_type_arr[] = 'video/mp4';


        if( !in_array($min_result , $min_type_arr ) )
        {
            die();
        }
        //fileinfo 检测end

        if (!is_uploaded_file($_FILES["upfile"]['tmp_name']))
        //是否存在文件
        {
        	$result['msg'] = "图片不存在!";
            echo json_encode($result);
            exit;
        }
        $file = $_FILES["upfile"];
        if ($max_file_size < $file["size"])
        //检查文件大小
        {
            $result['msg'] = "文件太大!";
            echo json_encode($result);
            exit;
        }
        if (!in_array($file["type"], $uptypes))
        //检查文件类型
        {
        	$result['msg'] = "文件类型不符!" . $file["type"];
            echo json_encode($result);
            exit;
        }


        $filename = $file["tmp_name"];
        $pinfo = pathinfo($file["name"]);
        $ftype = $pinfo['extension'];

		$file_name = str_shuffle(time() . rand(111111, 999999)) . "." . $ftype;
        $destination = $destination_folder . $file_name;

        if (!move_uploaded_file($filename, $destination)) {
            $result['msg'] = "移动文件出错!";
            echo json_encode($result);
            exit;
        }
        $pinfo = pathinfo($destination);
        $fname = $pinfo['basename'];

		//6956182894169131.png

		$thumb = resize($send_path_re.$file_name,  200,200);


		$image_thumb = $thumb ;
		$image_o = $send_path.$file_name;


		$url = D('Home/Front')->get_config_by_name('shop_domain').'/';

		echo json_encode( array('code' => 0,'image_thumb' =>$url.$image_thumb, 'image_o' => $url.$image_o , 'image_o_full' => $url.tomedia($send_path_re.$file_name) ) );
		die();

	}


	public function notify_order()
	{
		$notify_order_list_time = S('notify_order_list_time2');

		$now_time = time();

		if(  isset($notify_order_list_time) && $notify_order_list_time >0 && $now_time - $notify_order_list_time < 3600 )
		{
			$result_list = S('notify_order_list');
			if( !isset($result_list) || empty($result_list) )
			{
				echo json_encode( array('code' => 1) );
				die();
			}else{
				$result_key = array_rand($result_list,1);

				$result = $result_list[$result_key];

				echo json_encode( $result );
				die();
			}

		}else{
			$notify_order_list = M('eaterplanet_ecommerce_notify_order')->order('rand()')->group('member_id')->limit(100)->select();


			$result = array();

			if(!empty($notify_order_list) )
			{
				$need_data = array();

				foreach($notify_order_list as $notify_order)
				{

					$miao = (time() -$notify_order['order_time']) % 60;
					$result_data = array();

					$result_data['code'] = 0;
					$result_data['username'] = $notify_order['username'];
					$result_data['avatar'] 	= $notify_order['avatar'];
					$result_data['order_id'] 	= $notify_order['order_id'];

					$result_data['order_url'] 	= $notify_order['order_url'];
					$result_data['miao'] 	= $miao;

					$need_data[] = $result_data;
				}

				S('notify_order_list_time2', time() );
				S('notify_order_list', $need_data );


				$result_key = array_rand($need_data,1);

				$result = $need_data[$result_key];

			}

			if( empty($result) )
			{
				echo json_encode( array('code' => 1) );
				die();
			}else{
				echo json_encode( $result);
				die();
			}
		}

		die();


		//
		$notify_order = M('eaterplanet_ecommerce_notify_order')->order('state asc , id asc')->find();

		$result = array('code' => 1);
		if(empty($notify_order))
		{
			echo json_encode($result);
			die();
		}

		M()->execute("update ".C('DB_PREFIX')."eaterplanet_ecommerce_notify_order set state =state+1  where  id=".$notify_order['id'] );






		//->save( array('state' => 1) );


		echo json_encode($result);
		die();
	}



	public function load_buy_recordlist()
	{
		$gpc = I('request.');

		$goods_id = $gpc['goods_id'];
		$pageNum = $gpc['pageNum'];

		$per_page = 10;

		$offset = ($pageNum -1) * $per_page;
		$limit = "{$offset}, {$per_page}";

		$list = D('Home/Frontorder')->get_goods_buy_record($goods_id,$limit);


		if(!empty($list['list']))
		{
			echo json_encode( array('code' =>0, 'data' => $list['list']) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}

	public function get_goods_detail() {

		$gpc = I('request.');

		$id = $gpc['id'];
		$pin_id = isset($gpc['pin_id']) ? $gpc['pin_id'] : 0;
		$token = $gpc['token'];
		$head_id = $gpc['community_id'];

		$needauth = false;
		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			$needauth = true;
		}

		$member_id = $weprogram_token['member_id'];

        $need_data = array();

        $sql = "select g.*,gd.content,gd.begin_time,gd.end_time,gd.video,gd.is_take_fullreduction,gd.goods_share_image,gd.share_title,gd.quality,gd.pick_up_type,gd.pick_up_modify,gd.one_limit_count,gd.total_limit_count,gd.seven,gd.repair,gd.labelname,gd.share_title,gd.relative_goods_list,gd.is_show_arrive,gd.is_modify_sendscore,gd.send_socre,gd.diy_arrive_switch,gd.diy_arrive_details,gd.is_only_hexiao,gd.supply_id,gd.oneday_limit_count,gd.total_limit_count,gd.one_limit_count,gd.goods_start_count
				from " . C('DB_PREFIX') . "eaterplanet_ecommerce_goods g," . C('DB_PREFIX') . "eaterplanet_ecommerce_good_common gd
				where g.id=gd.goods_id and g.id=" . $id;



		$goods_arr =  M()->query($sql);
		$goods = $goods_arr[0];

		$is_only_hexiao = $goods['is_only_hexiao'];
		$goods['nogoods'] = false;
		if(empty($goods_arr[0])) { $goods['nogoods'] = true; }

		$goods['goods_id'] = $id;

		$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
		$full_money = D('Home/Front')->get_config_by_name('full_money');
		$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');

		if(empty($full_reducemoney) || $full_reducemoney <= 0)
		{
			$is_open_fullreduction = 0;
		}

		if($is_open_fullreduction == 0)
		{
			$goods['is_take_fullreduction'] = 0;
		}

		$goods['full_money'] = $full_money;
		$goods['full_reducemoney'] = $full_reducemoney;



		$goods['is_video'] = 0;
		$goods['video_size_width'] = 0;
		$goods['vedio_size_height'] = 0;
		$goods['video_src'] = '';

		//goods_share_image
		if( !empty($goods['goods_share_image']) )
		{
			$goods['goods_share_image'] = tomedia($goods['goods_share_image']);
		}

		//video
		if( !empty($goods['video']) )
		{
			$goods['video'] = tomedia($goods['video']);
		}

		$goods['goodsname'] = htmlspecialchars_decode($goods['goodsname']);
        $goods['description'] = htmlspecialchars_decode($goods['content']);
        $goods['description'] = htmlspecialchars_decode($goods['description']);
        $qian = array(
            "\r\n"
        );
        $hou = array(
            "<br/>"
        );
        $goods['subtitle'] = str_replace($qian, $hou, $goods['subtitle']);

		$hou = array(
            "@EOF@"
        );
        $today_time = strtotime( date('Y-m-d').' 00:00:00' );
        //pick_up_type
        //1、当日达，2、次日达，3隔日达，4 自定义
        if($goods['pick_up_type'] == 0)
        {
        	$goods['pick_up_modify'] = date('Y-m-d', $today_time);
        }else if( $goods['pick_up_type'] == 1 ){
        	$goods['pick_up_modify'] = date('Y-m-d', $today_time+86400);
        }else if( $goods['pick_up_type'] == 2 )
        {
        	$goods['pick_up_modify'] = date('Y-m-d', $today_time+86400*2);
        }

		//gd.begin_time,gd.end_time,
		//over_type =0 未开始，over_type =2已结束，over_type =1距结束

		$now_time = time();

		if($goods['begin_time'] > $now_time)
		{
			$goods['over_type'] = 0;
		}else if( $goods['begin_time'] <= $now_time &&  $goods['end_time'] > $now_time ){
			$goods['over_type'] = 1;
		}else if($goods['end_time'] < $now_time){
			$goods['over_type'] = 2;
			$goods['end_date'] = date('m/d H:i', $goods['end_time']);
		}

		$goods['activity_summary'] = '';


		$onegood_image = D('Home/Pingoods')->get_goods_images($id);
		if( !empty($onegood_image) )
		{
			$goods['image_thumb'] = tomedia($onegood_image['image']);
			$goods['image'] = tomedia($onegood_image['image']);
		}

        $buy_record_arr = D('Home/Frontorder')->get_goods_buy_record($id,9);


	   $goods_image = D('Home/Pingoods')->get_goods_images($id, 10);


        if (isset($goods_image)) {
            foreach ($goods_image as $k => $v) {
               $goods_image[$k]['image'] = tomedia($v['image']);
            }
        }



        $goods['seller_count']+= $goods['sales'];

        $goods_price_arr = D('Home/Pingoods')->get_goods_price($id ,$member_id);

		$goods['danprice'] = $goods_price_arr['danprice'];
		$goods['card_price'] = $goods_price_arr['card_price'];//会员卡价格

		$goods['levelprice'] = $goods_price_arr['levelprice']; // 客户等级价格
		$goods['is_mb_level_buy'] = $goods_price_arr['is_mb_level_buy']; //是否 客户等级 可享受

		if($goods['type'] == 'integral')
		{
		    $goods['price'] = round($goods_price_arr['price'], 0);
		    $goods['productprice'] = round($goods['productprice'], 0);

		    $price_dol = explode(' ', $goods_price_arr['price']);

			$price_dol[0] = round($price_dol[0], 0);

		}else{
		    $goods['price'] = $goods_price_arr['price'];
		    $price_dol = explode('.', $goods_price_arr['price']);
		}

		$open_buy_send_score = D('Home/Front')->get_config_by_name('open_buy_send_score');
		if( !isset($open_buy_send_score) || $open_buy_send_score == 0 )
		{
			$open_buy_send_score = 0;
		}
		$goods['open_buy_send_score'] = $open_buy_send_score;
		//open_buy_send_score send_socre

		if( $goods['is_modify_sendscore'] != 1 )
		{
			$open_buy_send_score = D('Home/Front')->get_config_by_name('open_buy_send_score');

			if( isset($open_buy_send_score) && $open_buy_send_score == 1 )
			{
				$money_for_score = D('Home/Front')->get_config_by_name('money_for_score');

				if( isset($money_for_score) && $money_for_score  > 0 )
				{
					$goods['is_modify_sendscore'] = 1;

					$goods['send_socre'] =  round($money_for_score*$goods['price'], 2);
				}

			}
		}
		$goods['price_front'] = $price_dol[0];
		$goods['price_after'] = isset($price_dol[1]) ? $price_dol[1] : '';


		$labelname_arr = unserialize( $goods['labelname'] );
		$tag_arr = array();

		if( !empty($labelname_arr) )
		{
			$goods['tag'] = $labelname_arr;
		}else{
			if( $goods['quality'] == 1)
			{
				$tag_arr[] = '正品保证';
			}
			if( $goods['seven'] == 1)
			{
				$tag_arr[] = '7天无理由退换';
			}
			if( $goods['repair'] == 1)
			{
				$tag_arr[] = '保修';
			}
			$goods['tag'] = $tag_arr;

		}


        $goods['fan_image'] = $goods['image'];

		$one_image = D('Home/Pingoods')->get_goods_images($id, 1);
		$goods['one_image'] = tomedia($one_image['image']);


        $pin_info = array();



		$user_favgoods =  D('Home/Pingoods')->fav_goods_state($id, $member_id);

		if( !empty($user_favgoods) )
		{
			$goods['favgoods'] = 2;
		}else{
			$goods['favgoods'] = 1;
		}
		$price = $goods['danprice'];


		$lottery_info = array();

		$need_data['lottery_info'] = $lottery_info;

		//$goods['share_title'] = $price.'元 '.$goods['goodsname'];


		if(empty($goods['share_title'])) {
			if($goods['type']=='integral') {
				$goods['share_title'] = intval($price).'积分 '.$goods['goodsname'];
			} else {
				$goods['share_title'] = $price.'元 '.$goods['goodsname'];
			}

            $is_login_showprice = D('Home/Front')->get_config_by_name('is_login_showprice');

            if( !empty($is_login_showprice) && $is_login_showprice == 1 )
            {
                $member_info = M('eaterplanet_ecommerce_member')->where(array('member_id' => $member_id))->find();

                if( empty($member_id) || $member_id <= 0  || $member_info['is_apply_state'] == 0 )
                {
                    if($goods['type']=='integral') {
                        $goods['share_title'] = '---积分 '.$goods['goodsname'];
                    } else {
                        $goods['share_title'] = '---元 '.$goods['goodsname'];
                    }
                }
            }
		}

		/** 商品客户折扣begin **/
		$is_show_member_disc = 0;

		$member_disc = 100;



		/** 商品客户折扣end **/

		$goods['memberprice'] = sprintf('%.2f', round( ($goods['danprice'] * $member_disc) / 100 ,2));
		$max_get_dan_money = round( ($goods['danprice'] * (100 - $max_member_level['discount']) ) / 100 ,2);
		$max_get_money = $max_get_dan_money;
		if(!empty($pin_info))
		{
			$pin_info['member_pin_price'] = sprintf('%.2f',round( ($pin_info['pin_price'] * $member_disc) / 100 ,2));
			$max_get_pin_money = round( ($pin_info['pin_price'] * (100 - $max_member_level['discount']) ) / 100 ,2);
			$max_get_money = $max_get_pin_money;
		}

		// 商品角标
		$label_id = unserialize($goods['labelname']);
		if($label_id){
			$label_info = D('Home/Pingoods')->get_goods_tags($label_id);
			if($label_info){
				if($label_info['type'] == 1){
					$label_info['tagcontent'] = tomedia($label_info['tagcontent']);
				} else {
					$label_info['len'] = mb_strlen($label_info['tagcontent'], 'utf-8');
				}
			}
			$goods['label_info'] = $label_info;
		}


		//查看客户身份，是否有佣金显示到商品详细页begin

		$is_commiss_mb = 0;
		$commiss_mb_money = 0;

		$is_goods_head_mb = 0;
		$goods_head_money = 0;

		$is_show_goodsdetails_commiss_money = D('Home/Front')->get_config_by_name('is_show_goodsdetails_commiss_money');

		if( !empty($is_show_goodsdetails_commiss_money) && $is_show_goodsdetails_commiss_money == 1 && $member_id > 0 )
		{
			//先判断是否有分销的佣金
			$commiss_level = D('Home/Front')->get_config_by_name('commiss_level');

			if( !empty($commiss_level) && $commiss_level > 0)
			{
				$mb_info = M('eaterplanet_ecommerce_member')->field('comsiss_flag')->where( array('member_id' => $member_id ) )->find();

				//判断是否分销  =1
				if( $mb_info['comsiss_flag'] == 1 )
				{
					$commission_info = D('Home/Pingoods')->get_goods_commission_info($id,$member_id );

					if( $commission_info['commiss_one']['type'] == 2 )
					{
						$commiss_one_money = $commission_info['commiss_one']['money'];
					}else{
						$commiss_one_money = round( ($commission_info['commiss_one']['fen'] * $goods['price'] )/100 , 2);
					}

					$is_commiss_mb = 1;
					$commiss_mb_money = $commiss_one_money;
				}
			}

			$is_community_hd = M('eaterplanet_community_head')->field('id')->where( array('member_id' => $member_id ) )->find();

			if( !empty($is_community_hd) )
			{
				//说明是团长，但是要确定是否这个商品的团长
				$is_commu_sale = D('Seller/Communityhead')->check_goods_can_community($id, $is_community_hd['id']);

				$community_money_type = D('Home/Front')->get_config_by_name('community_money_type');

				if( $is_commu_sale )
				{
					//计算团长佣金
					$head_commission_info = D('Home/Front')->get_goods_common_field($id , 'community_head_commission');

					$head_level_arr = D('Seller/Communityhead')->get_goods_head_level_bili( $id );

					$community_info = M('eaterplanet_community_head')->where( array('id' => $is_community_hd['id'] ) )->find();


					if(  $community_info['state'] == 1 && $community_info['enable'] == 1 )
					{

						$level = $community_info['level_id'];

						$is_head_takegoods = D('Home/Front')->get_config_by_name('is_head_takegoods');

						$is_head_takegoods = isset($is_head_takegoods) && $is_head_takegoods == 1 ? 1 : 0;



						//启用时 1，上方和商品中的“团长提成比例”失效，使用“团长等级”对应提成比例。不启用时 0，上方和商品中的“团长提成比例有效”。
						if($is_head_takegoods == 1)
						{
							//开启 团长等级

								//是否设置了团长等级
								$head_level = M('eaterplanet_ecommerce_community_head_level')->find();
								$is_modify_head_commission = M('eaterplanet_ecommerce_good_common')->field('is_modify_head_commission,is_community_head_commission,community_head_commission')->where( array('goods_id' => $goods['goods_id'] ) )->find();

								if(!empty($head_level) && $is_modify_head_commission["is_modify_head_commission"] == 1 ){
									//有等级 按商品或者等级比例
									$head_commission_info['community_head_commission'] = $head_level_arr['head_level'.$level];

				                }elseif(!empty($head_level) && $is_modify_head_commission["is_modify_head_commission"] == 0 ){
									if($level == 0 ){
										$head_commission_info['community_head_commission'] = D('Home/Front')->get_config_by_name('default_comunity_money');
									}else{
											$community_head_level = M('eaterplanet_ecommerce_community_head_level')->where(array('id' => $level ) )->find();
											$head_commission_info['community_head_commission'] =$community_head_level['commission'];
									}
								}else{

										if($is_modify_head_commission['is_community_head_commission'] == 1){
											$head_commission_info['community_head_commission'] =  $is_modify_head_commission['community_head_commission'];
										}else{
											$head_commission_info['community_head_commission'] = D('Home/Front')->get_config_by_name('default_comunity_money');
										}

								}

						}else{

								$is_community_head_commission = M('eaterplanet_ecommerce_good_common')->field('is_community_head_commission,community_head_commission')->where( array('goods_id' => $goods['goods_id'] ) )->find();

								if($is_community_head_commission['is_community_head_commission'] == 1){
										//商品单独设置

									$head_commission_info['community_head_commission'] =  $is_community_head_commission['community_head_commission'];

								}else{
									$head_commission_info['community_head_commission'] = D('Home/Front')->get_config_by_name('default_comunity_money');
								}

						}

						if( $community_money_type == 1 )
						{
							$goods_head_money = round( $head_commission_info['community_head_commission'] ,2);
						}else{

							$goods_head_money = round( ($head_commission_info['community_head_commission'] * $goods['price'] )/100,2);
						}


						$is_commiss_mb = 0;
						$commiss_mb_money = 0;

						$is_goods_head_mb = 1;
					}

				}
			}

		}
		//end


        $need_data['pin_info'] = $pin_info;

		$need_data['is_commiss_mb'] = $is_commiss_mb;//是否显示  客户分销 佣金 1 是，0否
		$need_data['commiss_mb_money'] = $commiss_mb_money;// 客户分销佣金 是多少
		$need_data['is_goods_head_mb'] = $is_goods_head_mb;// 是否团长 佣金， 1 是，0否
		$need_data['goods_head_money'] = $goods_head_money;// 团长佣金 金额

		/**
		if(!empty($member_id) && $member_id > 0 && $goods[0]['type'] == 'integral')
		{
			$member_info =  M('member')->field('score')->where( array('member_id' => $member_id) )->find();
			if($member_info['score'] < $goods[0]['score'])
			{
				$goods[0]['score_enough'] = 0;
			}else{
				$goods[0]['score_enough'] = 1;
			}
		}
		**/

		$need_data['member_level_info'] = $member_level_info;
		$need_data['member_level_list'] = $member_level_list;
		$need_data['max_member_level'] = $max_member_level;
		$need_data['max_get_money'] = sprintf('%.2f',$max_get_money);

		$need_data['max_get_pin_money'] = $max_get_pin_money;
		$need_data['max_get_dan_money'] = $max_get_dan_money;
		$need_data['buy_record_arr'] = $buy_record_arr;


		$need_data['is_show_max_level'] = $is_show_max_level;

		$goods['actPrice'] = explode('.', $goods['price']);
		$goods['marketPrice'] = explode('.', $goods['productprice']);


		$relative_goods_list = array();

		$is_open_goods_relative_goods = D('Home/Front')->get_config_by_name('is_open_goods_relative_goods');

		if( !empty($is_open_goods_relative_goods) && $is_open_goods_relative_goods == 1 )
		{
			$rel_unser = unserialize($goods['relative_goods_list']);

			if( !empty($rel_unser) )
			{
				$relative_goods_list_str = implode(',', $rel_unser);
				$now_time = time();

				$s_where = " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";

				$limit_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.oneday_limit_count,gc.total_limit_count,gc.one_limit_count,gc.goods_start_count ', ' g.id in('.$relative_goods_list_str.') and grounding =1 '.$s_where,0,100);

				$last_community_info = array();
				if( !empty($member_id) && $member_id > 0 )
				{
					$last_community_info = D('Home/Front')->get_history_community($member_id);
				}
				$cart= D('Home/Car');
				foreach($limit_goods as $kk => $val)
				{
					if( !empty($last_community_info) )
					{
						//communityId
						$is_canshow = D('Seller/Communityhead')->check_goods_can_community($val['id'], $last_community_info['communityId']);
						if( !$is_canshow )
						{
							continue;
						}
					}

					$tmp_data = array();
					$tmp_data['actId'] = $val['id'];
					$tmp_data['spuName'] = $val['goodsname'];

					$tmp_data['oneday_limit_count'] = $val['oneday_limit_count'];
					$tmp_data['total_limit_count'] = $val['total_limit_count'];
					$tmp_data['one_limit_count'] = $val['one_limit_count'];
					$tmp_data['goods_start_count'] = $val['goods_start_count'];

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

					$good_image_tp = D('Home/Pingoods')->get_goods_images($val['id']);
					if( !empty($good_image_tp) )
					{
						$tmp_data['skuImage'] = tomedia($good_image_tp['image']);
					}
					$price_arr = D('Home/Pingoods')->get_goods_price($val['id'], $member_id);
					$price = $price_arr['price'];

					if( $pageNum == 1 )
					{
						$copy_text_arr[] = array('goods_name' => $val['goodsname'], 'price' => $price);
					}

					$tmp_data['actPrice'] = explode('.', $price);

					//$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'],$member_id);
					$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options_carquantity($val['id'], $member_id, $head_id ,$token);
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

					$relative_goods_list[$kk] = $tmp_data;

				}
			}
		}

		 unset($goods['relative_goods_list']);

		$need_data['relative_goods_list'] = $relative_goods_list;

		$show_goods_subtitle = D('Home/Front')->get_config_by_name('show_goods_subtitle');

		if($show_goods_subtitle!=1) $goods['subtitle'] = '';

		if(empty($relative_goods_list)){
			$goods['cart_quantity'] = D('Home/Car')->get_wecart_goods($goods['id'],"",$head_id ,$token);
		}

        $need_data['goods'] = $goods;
        $need_data['goods_image'] = $goods_image;

		/**
        $seller_info = M('seller')->field('s_id,s_true_name,s_logo,s_qq,certification')->where(array(
            's_id' => $goods[0]['store_id']
        ))->find();
        $seller_model = D('Home/Seller');
        $seller_info['seller_count'] = $seller_model->getStoreSellerCount($goods[0]['store_id']);
        $seller_goods_count = M('goods')->where(array(
            'store_id' => $goods[0]['store_id']
        ))->count();
        $seller_info['goods_count'] = $seller_goods_count;
        $seller_info['s_logo'] = C('SITE_URL') . 'Uploads/image/' . $seller_info['s_logo'];
        $need_data['seller_info'] = $seller_info;
		**/

		$need_data['site_name'] = D('Home/Front')->get_config_by_name('shoname');

        //$need_data['options'] = D('Home/Pingoods')->get_goods_options($id, $member_id);  // $goods_model->get_goods_options($id);
		$need_data['options'] = D('Home/Pingoods')->get_goods_options_carquantity($id, $member_id, $head_id ,$token);


		$order_comment_count = M('eaterplanet_ecommerce_order_comment')->where( array('state' => 1, 'goods_id' => $id) )->count();

		$comment_list = array();

		if($order_comment_count > 0)
		{

			$sql = "select o.*,m.username as name2,m.avatar as avatar2 from ".C('DB_PREFIX')."eaterplanet_ecommerce_order_comment as o left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m
			on o.member_id=m.member_id
			where  o.state = 1 and o.goods_id = {$id}  order by o.add_time desc limit 1";

			$comment_list=  M()->query($sql);

			$order_comment_images = array();

			foreach($comment_list as $key => $val)
			{
				//user_name

				if( empty($val['user_name']) )
				{
					$val['name'] = $val['name2'];
					$val['avatar'] = tomedia($val['avatar2']);
				}else{
					$val['name'] = $val['user_name'];
					$val['avatar'] = tomedia($val['avatar']);
				}

				if($val['type'] == 0)
				{

					$order_goods_info = M('eaterplanet_ecommerce_order_goods')->field('order_goods_id')->where( array('goods_id' => $id,'order_id' => $val['order_id']) )->find();

					$order_option_info = M('eaterplanet_ecommerce_order_option')->field('value')->where( array('order_goods_id' => $order_goods_info['order_goods_id'],'order_id' => $val['order_id']) )->select();


					$option_arr = array();
					foreach($order_option_info as $option)
					{
						$option_arr[] = $option['value'];
					}
					$option_str = implode(',', $option_arr);
				}else{
					$option_str = '';
				}

				$img_str = unserialize($val['images']);
				if( !empty($img_str) && $img_str != 'undefined' )
				{
					//$img_str = unserialize($val['images']);
					$img_list = explode(',', $img_str);
					$need_img_list = array();

					foreach($img_list as $kk => $vv)
					{
						if(!empty($vv) )
						{
							// $vv = tomedia(resize($vv,400,400));
							$vv = tomedia($vv);
							$img_list[$kk] = $vv;
							$need_img_list[$kk] = $vv;
							if(count($order_comment_images) <= 4)
								$order_comment_images[] = $vv;
						}
					}
					$val['images'] = $need_img_list ;
				} else {
					$val['images'] = array();
				}
				$val['option_str'] = $option_str;
				$val['add_time'] = date('Y-m-d', $val['add_time']) ;
				$comment_list[$key] = $val;
			}
			//$this->comment_list = $comment_list;

		}

		$need_data['cur_time'] = time();
		$need_data['pin_id'] = $pin_id;

		$need_data['is_show_arrive'] = $goods['is_show_arrive'];
		$need_data['diy_arrive_switch'] = $goods['diy_arrive_switch'];
		$need_data['diy_arrive_details'] = $goods['diy_arrive_details'];


		$need_data['is_can_headsales'] = 1;


		//团长休息
		$community_id = $gpc['community_id'];

		if( isset($community_id) && $community_id > 0 )
		{
			$is_can_buy = D('Seller/Communityhead')-> check_goods_can_community($id, $community_id);

			if( !$is_can_buy )
			{
				$need_data['is_can_headsales'] = 0;
			}
			// is_all_sale
		}


		$is_comunity_rest = D('Seller/Communityhead')->is_community_rest($community_id);

		$open_man_orderbuy = D('Home/Front')->get_config_by_name('open_man_orderbuy');
		$man_orderbuy_money = D('Home/Front')->get_config_by_name('man_orderbuy_money');



		//同城配送商品的起卖价格

		$goods_supply_info = M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $goods['id'] ) )->find();

		$supply_id = $goods_supply_info["supply_id"];
		$is_only_distribution = $goods_supply_info["is_only_distribution"];
		//达到金额免配送费
		$localtown_moneytype_fixed_freemoney = 0;
		if($supply_id == 0){
				//配送费类型:0、固定金额，1、按距离收取
				$localtown_delivery_moneytype = D('Home/Front')->get_config_by_name('localtown_delivery_moneytype');
				if($localtown_delivery_moneytype == 0){

					$localtown_moneytype_fixed_freemoney = D('Home/Front')->get_config_by_name('localtown_moneytype_fixed_deliverymoney');

				}

		}else{
				$isopen_localtown_delivery = D('Home/Front')->get_supply_config_by_name('isopen_localtown_delivery',$supply_id);
				//配送费类型:0、固定金额，1、按距离收取
				$localtown_delivery_moneytype = D('Home/Front')->get_supply_config_by_name('localtown_delivery_moneytype',$supply_id);
				if($isopen_localtown_delivery == 1 && $localtown_delivery_moneytype == 0){

					$localtown_moneytype_fixed_freemoney = D('Home/Front')->get_supply_config_by_name('localtown_moneytype_fixed_deliverymoney',$supply_id);

				}
		}


		$goodsdetails_addcart_bg_color = D('Home/Front')->get_config_by_name('goodsdetails_addcart_bg_color');
		$goodsdetails_buy_bg_color = D('Home/Front')->get_config_by_name('goodsdetails_buy_bg_color');

		$is_close_details_time = D('Home/Front')->get_config_by_name('is_close_details_time');



		$isopen_community_group_share = D('Home/Front')->get_config_by_name('isopen_community_group_share');
		$group_share_info = '';
		if($isopen_community_group_share == 1) {

			$head_commiss_info = M('eaterplanet_community_head_commiss')->where( array('head_id' => $community_id ) )->find();

			if( !empty($head_commiss_info) )
			{
				$group_share_info = array();
			    $group_share_info['share_wxcode'] = $head_commiss_info['share_wxcode'] ? tomedia($head_commiss_info['share_wxcode']) : '';
			    $share_avatar = D('Home/Front')->get_config_by_name('group_share_avatar');

				$group_share_info['share_avatar'] = tomedia($share_avatar);
			    $group_share_info['share_title'] = D('Home/Front')->get_config_by_name('group_share_title');
			    $group_share_info['share_desc'] = D('Home/Front')->get_config_by_name('group_share_desc');

		    }
		}

		//.... card_price
		$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
		$modify_vipcard_name = D('Home/Front')->get_config_by_name('modify_vipcard_name');
		$modify_vipcard_logo = D('Home/Front')->get_config_by_name('modify_vipcard_logo');

		$modify_vipcard_name = empty($modify_vipcard_name) ? '吃货星球会员': $modify_vipcard_name;
		$is_open_vipcard_buy = !empty($is_open_vipcard_buy) && $is_open_vipcard_buy ==1 ? 1:0;
		if( !empty($modify_vipcard_logo) )
		{
			$modify_vipcard_logo = tomedia($modify_vipcard_logo);
		}

		$goods_details_title_bg = D('Home/Front')->get_config_by_name('goods_details_title_bg');
		if( !empty($goods_details_title_bg) )
		{
			$goods_details_title_bg = tomedia($goods_details_title_bg);
		}

		$is_vip_card_member = 0;
		$is_member_level_buy = 0;


		//member_id
		if( $member_id > 0 )
		{
			$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id ) )->find();

			if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 )
			{

				$now_time = time();

				if( $member_info['card_id'] >0 && $member_info['card_end_time'] > $now_time )
				{
					$is_vip_card_member = 1;//还是会员
				}else if( $member_info['card_id'] >0 && $member_info['card_end_time'] < $now_time ){
					$is_vip_card_member = 2;//已过期
				}
			}

			if( ($goods['is_take_vipcard'] != 1 ) && $member_info['level_id'] >0  && $goods['is_mb_level_buy'] == 1 )
			{
				$is_member_level_buy = 1;
			}
		}


		//$goods['type'] == 'pin' $member_id

		$is_need_subscript = 0;
		$need_subscript_template = array();


		if( $member_id >0  && $goods['type'] == 'pin')
		{
			//'pay_order','send_order','hexiao_success','apply_community','open_tuan','take_tuan','pin_tuansuccess','apply_tixian'

			$open_tuan_info = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $member_id, 'type' => 'open_tuan') )->find();

			if( empty($open_tuan_info) )
			{
				$weprogram_subtemplate_open_tuan = D('Home/Front')->get_config_by_name('weprogram_subtemplate_open_tuan');

				if( !empty($weprogram_subtemplate_open_tuan) )
				{
					$need_subscript_template['open_tuan'] = $weprogram_subtemplate_open_tuan;
				}
			}

			$take_tuan_info = M('eaterplanet_ecommerce_subscribe')->where( array('member_id' => $member_id, 'type' => 'take_tuan' ) )->find();

			if( empty($take_tuan_info) )
			{
				$weprogram_subtemplate_take_tuan = D('Home/Front')->get_config_by_name('weprogram_subtemplate_take_tuan');

				if( !empty($weprogram_subtemplate_take_tuan) )
				{
					$need_subscript_template['take_tuan'] = $weprogram_subtemplate_take_tuan;
				}
			}

			if( !empty($need_subscript_template) )
			{
				$is_need_subscript = 1;
			}
		}

		// 销量开关
		$is_hide_details_count = D('Home/Front')->get_config_by_name('is_hide_details_count');
		$is_open_goods_full_video = D('Home/Front')->get_config_by_name('is_open_goods_full_video');

		$ishide_details_desc = D('Home/Front')->get_config_by_name('ishide_details_desc');
		$delivery_type_ziti = D('Home/Front')->get_config_by_name('delivery_type_ziti');
		$show_goods_preview = D('Home/Front')->get_config_by_name('show_goods_preview');

		//核销门店
		$hx_salesroom_list = array();
		if($is_only_hexiao == 1){
			$hx_salesroom_list = D('Home/Salesroom')->get_goods_salesroom($id,$supply_id,'id,room_name,room_address');
		}
		//判断是否预售 begin
        $need_data['presale_goods_info'] = ['is_presale_goods' => 0 ];
        if($goods['type'] == 'presale')
        {
            $presale_goods_info = [];
            $goods_presale = M('eaterplanet_ecommerce_goods_presale')->where(['goods_id' => $id ])->find();

            $presale_goods_info['presale_type'] = $goods_presale['presale_type'];//0 定金， 1 全款支付
            $presale_goods_info['presale_ding_money'] = $goods_presale['presale_ding_money'];//定金金额
            $presale_goods_info['presale_deduction_money'] = $goods_presale['presale_deduction_money'];//定金抵扣金额
            $presale_goods_info['presale_ding_time_start'] = $goods_presale['presale_ding_time_start'];//定金开始支付时间
            $presale_goods_info['presale_ding_time_start_date'] = date('m月d日 H:i', $goods_presale['presale_ding_time_start'] );//定金支付格式化 开始时间
            $presale_goods_info['presale_ding_time_start_date'] = date('m月d日 H:i', $goods_presale['presale_ding_time_start'] );//定金支付格式化 开始时间

            $presale_goods_info['presale_ding_time_end'] =  $goods_presale['presale_ding_time_end'] ;//定金支付结束时间
            $presale_goods_info['presale_ding_time_end_date'] = date('m月d日 H:i', $goods_presale['presale_ding_time_end'] );//定金支付格式化 结束时间

            $presale_goods_info['presale_limit_balancepaytime'] = $goods_presale['presale_limit_balancepaytime'] ;//限制尾款支付时间，0不限制，1限制
            $presale_goods_info['presale_balance_paytime'] = intval($goods_presale['presale_balance_paytime']);//限制尾款支付时间， 几天内

            $presale_goods_info['presale_sendorder_type'] = ($goods_presale['presale_sendorder_type']);//预计发货时间，0 固定发货时间， 1购买后几日发货

            $presale_goods_info['presale_sendorder_datetime'] = date('Y-m-d',$goods_presale['presale_sendorder_datetime'] );//固定发货时间 ，具体的固定日期
            $presale_goods_info['presale_sendorder_afterday'] = intval( $goods_presale['presale_sendorder_afterday'] );//购买后几日发货

            $presale_goods_info['is_presale_goods'] = 1;

            $need_subscript_template = [];

            $weprogram_subtemplate_send_order = D('Home/Front')->get_config_by_name('weprogram_subtemplate_presale_ordercan_continuepay');

            if( !empty($weprogram_subtemplate_send_order) )
            {
                $need_subscript_template['presale_ordercan_continuepay'] = $weprogram_subtemplate_send_order;
                $is_need_subscript = 1;
            }

            $presale_goods_info['need_subscript_template'] = $need_subscript_template;//预售订阅消息

            $need_data['presale_goods_info'] = $presale_goods_info;

        }
        //预售end..
        $need_data['virtualcard_goods_info'] = ['is_virtualcard_goods' => 0 ];
        if($goods['type'] == 'virtualcard')
        {
            $goods_virturalcard = D('Seller/VirtualCard')->getGoodsVirtualCardInfoByGoodsId( $id );
            $virturalcard_info = D('Seller/VirtualCard')->getCodeInfoByCodeId( $goods_virturalcard['code_id'] );
            $code_money = $virturalcard_info['code_money'];

            $need_data['virtualcard_goods_info']['is_virtualcard_goods'] = 1; //是否礼品卡商品
            $need_data['virtualcard_goods_info']['code_money'] = round($code_money, 2); //兑换金额
        }
        //判断是否礼品卡 begin


        //礼品卡end

        echo json_encode(array(
            'code' => 1,
			'comment_list' => $comment_list,
			'is_only_hexiao' => $is_only_hexiao,
			'hexiao_arr' => $hx_salesroom_list,
			'order_comment_images' => $order_comment_images,
			'order_comment_count' => $order_comment_count,
			'data' => $need_data,
			'is_comunity_rest' => $is_comunity_rest,
			'open_man_orderbuy' => $open_man_orderbuy,
			'man_orderbuy_money' => $man_orderbuy_money,
			'localtown_moneytype_fixed_freemoney' => $localtown_moneytype_fixed_freemoney,
			'is_only_distribution' => $is_only_distribution,
			'goodsdetails_buy_bg_color' => $goodsdetails_buy_bg_color,
			'goodsdetails_addcart_bg_color' => $goodsdetails_addcart_bg_color,
			'isopen_community_group_share' => $isopen_community_group_share,
			'group_share_info' => $group_share_info,
			'is_close_details_time' => $is_close_details_time,
			'is_open_vipcard_buy' => $is_open_vipcard_buy,//是否开启会员卡
			'modify_vipcard_name' => $modify_vipcard_name,//会员卡名称
			'modify_vipcard_logo' => $modify_vipcard_logo,//会员卡图标
			'is_vip_card_member' => $is_vip_card_member,//是否会员卡会员， 0 不是，1是会员，2已过期的会员
			'is_member_level_buy' => $is_member_level_buy,
			'is_need_subscript' => $is_need_subscript,
			'need_subscript_template' => $need_subscript_template,
			'is_hide_details_count' => $is_hide_details_count,
			'is_open_goods_full_video' => $is_open_goods_full_video,
			'goods_details_title_bg' => $goods_details_title_bg,
			'needauth' => $needauth,
			'ishide_details_desc' => $ishide_details_desc,
			'delivery_type_ziti' => $delivery_type_ziti,
			'show_goods_preview' => $show_goods_preview
        ));
        die();
    }

    /**
     * 获取服务说明
     */
    public function get_instructions()
    {
    	$_GPC = I('request.');

		$list = M('eaterplanet_ecommerce_config')->field('value')->where( array('name' => 'instructions') )->find();

		$goods_id = isset($_GPC['goods_id']) ? $_GPC['goods_id'] : '';

		if(!empty($list['value']))
				$list['value'] = htmlspecialchars_decode($list['value']);


		$index_bottom_image = D('Home/Front')->get_config_by_name('index_bottom_image');
		if(!empty($index_bottom_image)) $index_bottom_image = tomedia($index_bottom_image);

		$goods_details_middle_image = D('Home/Front')->get_config_by_name('goods_details_middle_image');
		if(!empty($goods_details_middle_image)) $goods_details_middle_image = tomedia($goods_details_middle_image);

		$is_show_buy_record = D('Home/Front')->get_config_by_name('is_show_buy_record');
		$is_show_comment_list = D('Home/Front')->get_config_by_name('is_show_comment_list');
		$order_notify_switch = D('Home/Front')->get_config_by_name('order_notify_switch');


		$goods_details_price_bg = D('Home/Front')->get_config_by_name('goods_details_price_bg');
		if(!empty($goods_details_price_bg)) $goods_details_price_bg = tomedia($goods_details_price_bg);

		$user_service_switch = D('Home/Front')->get_config_by_name('user_service_switch');

		$goods_industrial_switch = D('Home/Front')->get_config_by_name('goods_industrial_switch');
		$goods_industrial = D('Home/Front')->get_config_by_name('goods_industrial');
		if(!empty($goods_industrial)) {
			$goods_industrial =  unserialize($goods_industrial) ;//tomedia($goods_industrial);

			foreach( $goods_industrial as $key => $val )
			{
				$goods_industrial[$key] = tomedia($val);
			}
		}

		//supply_id
		if( $goods_id > 0 )
		{

			$gd_info = M('eaterplanet_ecommerce_good_common')->field('supply_id')->where( array('goods_id' => $goods_id )  )->find();

			if( !empty($gd_info) && $gd_info['supply_id'] > 0 )
			{
				$su_info = M('eaterplanet_ecommerce_supply')->field('qualifications')->where( array('id' => $gd_info['supply_id'] ) )->find();

				$qualifications =  unserialize($su_info['qualifications']);

				if(!empty($qualifications)) {
					foreach ($qualifications as  $key => $cval) {
						$qualifications[$key] = tomedia($cval);
					}
				}

				$goods_industrial = $qualifications;
			}
		}

		$list['value'] = htmlspecialchars_decode(htmlspecialchars_decode($list['value']));

		$hide_community_change_btn = D('Home/Front')->get_config_by_name('hide_community_change_btn');

		$list['index_bottom_image'] = $index_bottom_image;
		$list['goods_details_middle_image'] = $goods_details_middle_image;

		$list['goods_details_price_bg'] = $goods_details_price_bg;
		$list['is_show_buy_record'] = $is_show_buy_record;
		$list['is_show_comment_list'] = $is_show_comment_list;
		$list['order_notify_switch'] = $order_notify_switch;
		$list['index_service_switch'] = $user_service_switch;
		$list['goods_industrial_switch'] = $goods_industrial_switch;
		$list['goods_industrial'] = $goods_industrial;
		$list['is_show_ziti_time'] = D('Home/Front')->get_config_by_name('is_show_ziti_time');

		$list['is_show_goodsdetails_communityinfo'] = D('Home/Front')->get_config_by_name('is_show_goodsdetails_communityinfo');
		$list['hide_community_change_btn'] = $hide_community_change_btn;

		$result = array('code' =>0,'data' => $list);
		echo json_encode($result);
		die();
    }


    /**
     * 获取分类列表
     * @return [type] [description]
     */
    public function get_category_list()
    {

		$is_type_show = I('request.is_type_show', 0);
    	$is_show = I('request.is_show', 0);

		$category_list = D('Home/GoodsCategory')->get_index_goods_category(0,'normal', $is_show, $is_type_show);


		$result = array('code' =>0,'data' => $category_list);
		echo json_encode($result);
		die();
    }

	/**
     * 首页3*3布局列表
     * @return [josn] [description]
     */
    public function get_category_col_list()
    {
    	$_GPC = I('request.');

		$head_id = $_GPC['head_id'];
		if($head_id == 'undefined') $head_id = '';

		$result = array();
		$result['code'] = 1;

		$cate_list = M('eaterplanet_ecommerce_goods_category')->field('id,name,banner')->where( "is_show = 1 and is_show_topic = 1  and cate_type='normal' " )->order('sort_order desc')->select();

		if(empty($cate_list)) {
			$result['msg'] = '无数据';
		} else {
			foreach ($cate_list as $key => &$val) {
				if( !empty($val['banner']) )
				{
					$val['banner'] = tomedia($val['banner']);
				}

				$item = $this->get_category_col_list_item($val['id'], $head_id);
				if($item){
					$val['list'] = empty($item['list']) ? array() : $item['list'];
					$val['full_reducemoney'] = $item['full_reducemoney'];
					$val['full_money'] = $item['full_money'];
					$val['is_open_fullreduction'] = $item['is_open_fullreduction'];
				}
			}
			$result['code'] = 0;
			$result['data'] = $cate_list;
		}

		echo json_encode($result);
		die();
    }

    /**
     * 获取3*3分类列表项目
     * @return [type] [description]
     */
    private function get_category_col_list_item($gid, $head_id, $is_random=0){

		$_GPC = I('request.');




		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			$member_id = 0;
		}else{
			$member_id = $weprogram_token['member_id'];
		}



    	$now_time = time();
	    $where = " g.grounding =1 ";

	    $gids = D('Home/GoodsCategory')->get_index_goods_category($gid);
		$gidArr = array();
		$gidArr[] = $gid;
		foreach ($gids as $key => $val) { $gidArr[] = $val['id']; }
		$gid = implode(',', $gidArr);

		if( !empty($head_id) && $head_id >0 )
		{
			$sql_goods_ids = "select pg.goods_id from ".C('DB_PREFIX')."eaterplanet_community_head_goods as pg," .C('DB_PREFIX')."eaterplanet_ecommerce_goods_to_category as g where  pg.goods_id = g.goods_id  and g.cate_id in ({$gid}) and pg.head_id = {$head_id}  order by pg.id desc ";

			$goods_ids_arr = M()->query($sql_goods_ids);

			$ids_arr = array();
			foreach($goods_ids_arr as $val){ $ids_arr[] = $val['goods_id']; }

			$goods_ids_nolimit_sql = "select pg.id from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as pg," .C('DB_PREFIX')."eaterplanet_ecommerce_goods_to_category as g where pg.id = g.goods_id and g.cate_id in ({$gid}) and pg.is_all_sale=1  ";
			$goods_ids_nolimit_arr = M()->query($goods_ids_nolimit_sql);

			if( !empty($goods_ids_nolimit_arr) )
			{
				foreach($goods_ids_nolimit_arr as $val){
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
		}else{
			$goods_ids_nohead_sql = "select pg.id from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as pg," .C('DB_PREFIX')."eaterplanet_ecommerce_goods_to_category as g where pg.id = g.goods_id and g.cate_id in ({$gid})  ";
			$goods_ids_nohead_arr = M()->query($goods_ids_nohead_sql);

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
				$where .= "  and g.id in ({$ids_str})";
			} else{
				$where .= " and 0 ";
			}
		}

		// $where .= " and gc.begin_time <={$now_time} and gc.end_time > {$now_time} ";
		$where .= " and gc.begin_time <={$now_time} ";
		$where .= " and gc.is_new_buy=0 and gc.is_spike_buy = 0 and g.is_seckill = 0 ";

		if($is_random == 1)
		{
			$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video,gc.oneday_limit_count,gc.total_limit_count,gc.one_limit_count,gc.goods_start_count ', $where, 0, 9,' rand() ');

		}else{
			$community_goods = D('Home/Pingoods')->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction,gc.labelname,gc.video,gc.oneday_limit_count,gc.total_limit_count,gc.one_limit_count,gc.goods_start_count ', $where, 0, 9);
		}

		if( !empty($community_goods) )
		{
			$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
			$full_money = D('Home/Front')->get_config_by_name('full_money');
			$full_reducemoney = D('Home/Front')->get_config_by_name('full_reducemoney');

			if(empty($full_reducemoney) || $full_reducemoney <= 0)
			{
				$is_open_fullreduction = 0;
			}

			$cart= D('Home/Car');

			$list = array();
			foreach($community_goods as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$goodsname = htmlspecialchars_decode($val['goodsname']);
				$tmp_data['spuName'] = $goodsname;

				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];
				$tmp_data['actEnd'] = time()>$val['end_time'];

				$tmp_data['oneday_limit_count'] = $val['oneday_limit_count'];
				$tmp_data['total_limit_count'] = $val['total_limit_count'];
				$tmp_data['one_limit_count'] = $val['one_limit_count'];
				$tmp_data['goods_start_count'] = $val['goods_start_count'];

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
				$price = $price_arr['price'];

				$tmp_data['actPrice'] = explode('.', $price);
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

			$res = array();
			$res['list'] = $list;
			$res['full_reducemoney'] = $full_reducemoney;
			$res['full_money'] = $full_money;
			$res['is_open_fullreduction'] = $is_open_fullreduction;
			return $res;
		} else {
			return false;
		}
    }

	public function check_goods_community_canbuy()
	{
		$_GPC = I('request.');

		$goods_id = $_GPC['goods_id'];
		$community_id = $_GPC['community_id'];

		$is_canshow = D('Seller/Communityhead')->check_goods_can_community($goods_id, $community_id);

		if( $is_canshow )
		{
			echo json_encode( array('code' => 0) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}

		//猜你喜欢
	public function goods_guess_like()
	{
		$_GPC = I('request.');

		//猜你喜欢开关
		$show_goods_guess_like= D('Home/Front')->get_config_by_name('show_goods_guess_like' );
		if( empty($show_goods_guess_like) )
		{
			$show_goods_guess_like = 0;
		}

		$token =  $_GPC['token'];

		$weprogram_token = M('eaterplanet_ecommerce_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			$member_id = 0;
		}else{
			$member_id = $weprogram_token['member_id'];
		}

		//显示数量
		$num_guess_like= D('Home/Front')->get_config_by_name('num_guess_like' );
		if( empty($num_guess_like) )
		{
			$num_guess_like = 8;
		}
        $goods_id = $_GPC['id'];
		$community_id = $_GPC['head_id'];
		$token = $_GPC['token'];

		$now_time = time();

		if(!empty($community_id)){
			//有社区
			$head_info = M('eaterplanet_community_head')->field('id')->where( array('id' => $community_id ) )->find();

			//团长商品和全部可售
			//eaterplanet_community_head_goods
			//&head_id=303&id=317

			$head_goods= M('eaterplanet_community_head_goods')->field('goods_id')->where( array('head_id' =>  $head_info['id'] ) )->select();


			foreach ($head_goods as $hg) {
				$hg = join(",",$hg);
				$temp_array[] = $hg;
			}
			//团长商品id
			 $goods_id_list = implode(",", $temp_array);


			 $likegoods_list = array();

			 if( !empty($goods_id_list) )
			 {
			     $sql_likegoods = "select g.*,gc.end_time,gc.begin_time,gc.oneday_limit_count,gc.total_limit_count,gc.one_limit_count,gc.goods_start_count from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc
			     where g.id = gc.goods_id and gc.begin_time <={$now_time} and gc.end_time > {$now_time}  and (g.grounding =1 or g.id in (".$goods_id_list.") and g.id <> ".$goods_id." ) and g.type = 'normal' and g.is_all_sale = 1  order by rand() limit ".$num_guess_like;

			     $likegoods_list = M()->query($sql_likegoods);
			 }else{
				 $sql_likegoods = "select g.*,gc.end_time,gc.begin_time,gc.oneday_limit_count,gc.total_limit_count,gc.one_limit_count,gc.goods_start_count from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc
			     where g.id = gc.goods_id and gc.begin_time <={$now_time} and gc.end_time > {$now_time}  and (g.grounding =1 and g.id <> ".$goods_id." ) and g.type = 'normal' and g.is_all_sale = 1  order by rand() limit ".$num_guess_like;

			     $likegoods_list = M()->query($sql_likegoods);
			 }


		}else{
			//无社区
			$sql_likegoods = "select g.*,gc.end_time,gc.begin_time,gc.oneday_limit_count,gc.total_limit_count,gc.one_limit_count,gc.goods_start_count from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc
							  where g.id = gc.goods_id and gc.begin_time <={$now_time} and gc.end_time > {$now_time}  and g.grounding =1 and g.type = 'normal' and g.id <> ".$goods_id." order by rand() limit ".$num_guess_like;

			$likegoods_list = M()->query($sql_likegoods);
		}





		if( !empty($likegoods_list) )
		{
			$list = array();
			foreach($likegoods_list as $val)
			{
				$tmp_data = array();
				$tmp_data['actId'] = $val['id'];
				$tmp_data['spuName'] = $val['goodsname'];

				$tmp_data['spuCanBuyNum'] = $val['total'];
				$tmp_data['spuDescribe'] = $val['subtitle'];
				$tmp_data['end_time'] = $val['end_time'];
				$tmp_data['soldNum'] = $val['seller_count'] + $val['sales'];

				$tmp_data['oneday_limit_count'] = $val['oneday_limit_count'];
				$tmp_data['total_limit_count'] = $val['total_limit_count'];
				$tmp_data['one_limit_count'] = $val['one_limit_count'];
				$tmp_data['goods_start_count'] = $val['goods_start_count'];

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
				$price = $price_arr['price'];

				if( $pageNum == 1 )
				{
					$copy_text_arr[] = array('goods_name' => $val['goodsname'], 'price' => $price);
				}

				$tmp_data['actPrice'] = explode('.', $price);
				$tmp_data['danPrice'] =  $price_arr['danprice'];

				//$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options($val['id'],$member_id);
				$tmp_data['skuList']= D('Home/Pingoods')->get_goods_options_carquantity($val['id'], $member_id, $community_id ,$token);
				if( !empty($tmp_data['skuList']) )
				{
					$tmp_data['car_count'] = 0;
				}else{

					//$car_count = $cart->get_wecart_goods($val['id'],"",$head_id ,$token);

					$car_count = 0;

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

		}

		echo json_encode(array('code'=>0,
				'show_goods_guess_like' => $show_goods_guess_like,
				'list' => $list,
				)
		);
		die();

	}


	/**
	 * 视频列表分享信息
	 * @return [type] [description]
	 */
	public function get_video_list_share()
	{
		$_GPC = I('request.');

		$res = array();
		$res['nav_title'] = D('Home/Front')->get_config_by_name('videolist_nav_title');
		$res['share_title'] = D('Home/Front')->get_config_by_name('videolist_share_title');
		$res['share_poster'] = '';
		$videolist_share_poster = D('Home/Front')->get_config_by_name('videolist_share_poster');

		if($videolist_share_poster)
			$res['share_poster'] = tomedia($videolist_share_poster);

		echo json_encode(array('code'=>0, 'data' => $res));
		die();
	}


}
