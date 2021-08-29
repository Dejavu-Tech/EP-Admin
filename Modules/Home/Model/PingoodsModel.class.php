<?php
namespace Home\Model;

/**
 * 拼团模型模型
 * @author Albert.Z
 *
 */
class PingoodsModel {


	public function get_community_index_goods($fields='*', $where='1=1',$offset=0,$perpage=10,$order='g.istop DESC, g.settoptime DESC,g.index_sort desc,g.id desc ' )
	{
		//index_sort_method  0 置顶排序  1 排序大小排序(从大到小)

		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method' );

		if(empty($index_sort_method)){
			$sql_pingoods = "select {$fields} from "
                        .C('DB_PREFIX')."eaterplanet_ecommerce_goods as g,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc
        	           where  {$where}   and g.id=gc.goods_id   order by  g.istop DESC, g.settoptime DESC,g.id desc  limit {$offset},{$perpage} ";
		}else{
			$sql_pingoods = "select {$fields} from "
                        .C('DB_PREFIX')."eaterplanet_ecommerce_goods as g,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc
        	           where  {$where}   and g.id=gc.goods_id   order by  g.index_sort desc,g.id desc  limit {$offset},{$perpage} ";
		}

		$list_pingoods = M()->query($sql_pingoods);


        return $list_pingoods;


	}



	public function get_new_community_index_goods($head_id=0,$gid='', $fields='*', $where='1=1',$offset=0,$perpage=10,$order='g.istop DESC, g.settoptime DESC,g.index_sort desc,g.id desc ')
	{

		$inner_join ="";

		if( $head_id > 0 )
		{
			$where .= " and (g.is_all_sale = 1 or g.id in (SELECT goods_id from ".C('DB_PREFIX')."eaterplanet_community_head_goods where head_id = {$head_id}) ) ";
		}

		if( !empty($gid) )
		{
			$gd_cate_sql  = " select goods_id from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods_to_category where cate_id in ({$gid}) ";

			$cate_all = M()->query($gd_cate_sql  );

			$cate_goods_ids = array();

			if( !empty($cate_all) )
			{
				foreach($cate_all as $val)
				{
					$cate_goods_ids[] = $val['goods_id'];
				}
				$cate_goods_str = implode(',', $cate_goods_ids);

				$where .= " and g.id  in ({$cate_goods_str}) ";

			}else{

				$where .= " and g.id  in (0) ";
			}

		}

		$sql_pingoods = "select {$fields} from "
                        .C('DB_PREFIX')."eaterplanet_ecommerce_goods as g,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc {$inner_join}
        	           where  {$where}   and g.id=gc.goods_id  order by {$order} limit {$offset},{$perpage} ";



		$list_pingoods = M()->query($sql_pingoods);

		return $list_pingoods;
	}

	//begin index_share_qrcode

	/**
		分销商海报
	**/
	public function get_commission_index_share_image($community_id,$wepro_qrcode,$avatar,$nickname)
	{


		$send_path = "Uploads/image/".date('Y-m-d')."/haibao_goods/";

		$image_dir = ROOT_PATH.$send_path; //上传文件路径


		RecursiveMkdir($image_dir);


		$bg_img = ROOT_PATH."/assets/ep/images/index_share_bg.jpg";


		//index_share_qrcode_bg
		$index_share_qrcode_bg =  D('Home/Front')->get_config_by_name('distribution_img_src');

		if( !empty($index_share_qrcode_bg) )
		{

			$bg_img = ROOT_PATH.'Uploads/image/' . $index_share_qrcode_bg;
		}

		$dst = imagecreatefromstring(file_get_contents($bg_img));

		list($dst_w, $dst_h, $dst_type) = getimagesize($bg_img);


		$ttf_path = ROOT_PATH."/assets/ep/fonts/simhei.ttf";
		$msyh_path = ROOT_PATH."/assets/ep/fonts/msyh.ttf";
		$pingfang_path = ROOT_PATH."/assets/ep/fonts/PingFang_Bold.ttf";
		$pingfang_med_path = ROOT_PATH."/assets/ep/fonts/PingFang_Medium.ttf";


		//打上文字

		$black = imagecolorallocate($dst, 20,20,20);//黑色
		$a1a1a1 = imagecolorallocate($dst, 26,26,26);//黑色
		$red = imagecolorallocate($dst, 237, 48, 43); //红色 201 55 49
		$huise = imagecolorallocate($dst, 159, 159, 159); //灰色 159 159 159
		$fense = imagecolorallocate($dst, 248, 136, 161); //粉色 248 136 161
		$gray1 = imagecolorallocate($dst, 51, 51, 51); //#333 51, 51, 51
		$gray2 = imagecolorallocate($dst, 102, 102, 6); //#666 102, 102, 6
		$gray3 = imagecolorallocate($dst, 153, 153, 153); //#999 153, 153, 153

		$gray4 = imagecolorallocate($dst, 116, 116, 116); //#999 116, 116, 116
		$red_2 = imagecolorallocate($dst, 223, 21, 21); //#999 223, 21, 21

		$chengse = imagecolorallocate($dst, 252, 74, 74); //#999


		$distribution_username_left = D('Home/Front')->get_config_by_name('distribution_username_left');
		$distribution_username_top =  D('Home/Front')->get_config_by_name('distribution_username_top');

		$distribution_username_left = empty($distribution_username_left) ? 0: $distribution_username_left * 2;
		$distribution_username_top  = empty($distribution_username_top) ? 0: $distribution_username_top * 2 ;

		$avatar = 'Uploads/image/'.$avatar;

		$commiss_nickname_rgb = D('Home/Front')->get_config_by_name('commiss_nickname_rgb');
		$rgb_arr = array('r' => 248,'g' => 136,'b' => 161);
		if( !empty($commiss_nickname_rgb) )
		{
			$rgb_arr = $this->hex2rgb($commiss_nickname_rgb);
		}

		$col = imagecolorallocate($dst,$rgb_arr['r'], $rgb_arr['g'], $rgb_arr['b']);

		//$col = imagecolorallocate($dst,248, 136, 161);


		imagefttext($dst, 20, 0, $distribution_username_left, $distribution_username_top, $col, $pingfang_med_path, $nickname );

		list($avatar_img_img_w, $avatar_img_img_h, $avatar_img_img_type) = getimagesize(ROOT_PATH.$avatar);



		$avatar_img_src = imagecreatefromstring(file_get_contents(ROOT_PATH.$avatar));


		if (imageistruecolor($avatar_img_src))
				imagetruecolortopalette($avatar_img_src, false, 65535);

		$distribution_avatar_left = D('Home/Front')->get_config_by_name('distribution_avatar_left');
		$distribution_avatar_top =  D('Home/Front')->get_config_by_name('distribution_avatar_top');

		if( empty($distribution_avatar_left) )
		{
			$distribution_avatar_left = 0;
		}else{
			$distribution_avatar_left = $distribution_avatar_left * 2;
		}
		if( empty($distribution_avatar_top) )
		{
			$distribution_avatar_top = 0;
		}else{
			$distribution_avatar_top = $distribution_avatar_top * 2;
		}

		imagecopy($dst, $avatar_img_src, $distribution_avatar_left, $distribution_avatar_top, 0, 0, $avatar_img_img_w, $avatar_img_img_h);


		//wepro_qrcode

		$distribution_qrcodes_left = D('Home/Front')->get_config_by_name('distribution_qrcodes_left');
		$distribution_qrcodes_top = D('Home/Front')->get_config_by_name('distribution_qrcodes_top');

		if( empty($distribution_qrcodes_left) )
		{
			$distribution_qrcodes_left = 0;
		}else{
			$distribution_qrcodes_left = $distribution_qrcodes_left  * 2;
		}
		if( empty($distribution_qrcodes_top) )
		{
			$distribution_qrcodes_top = 0;
		}else{
			$distribution_qrcodes_top = $distribution_qrcodes_top  * 2;
		}

		$thumb_goods_img = $wepro_qrcode;

		$wepro_qrcode_new = str_replace('Uploads/image/','', $thumb_goods_img);

		$thumb_goods_img = resize($wepro_qrcode_new,180,180);


		list($thumb_goods_img_w, $thumb_goods_img_h, $thumb_goods_img_type) = getimagesize(ROOT_PATH.$thumb_goods_img);


		$goods_src = imagecreatefromstring(file_get_contents(ROOT_PATH.$thumb_goods_img));


		$thumb_goods_img_src = imagecreatefromstring(file_get_contents(ROOT_PATH.$thumb_goods_img));
		if (imageistruecolor($thumb_goods_img_src))
				imagetruecolortopalette($thumb_goods_img_src, false, 65535);



		imagecopy($dst, $goods_src, $distribution_qrcodes_left, $distribution_qrcodes_top, 0, 0, $thumb_goods_img_w, $thumb_goods_img_h);

		/**
		**/

		$send_path = "Uploads/image/".date('Y-m-d')."/haibao_goods/";

        $image_dir = ROOT_PATH.$send_path; //上传文件路径

		RecursiveMkdir($image_dir);

		$last_img = $image_dir;
		$last_img_name = "last_index_".md5( time().$community_id.$wepro_qrcode.mt_rand(1,999)).'';

		switch ($dst_type) {
			case 1://GIF
				$last_img_name .= '.gif';
				//header('Content-Type: image/gif');
				imagegif($dst, $last_img.$last_img_name);
				break;
			case 2://JPG
				$last_img_name .= '.jpg';
				//header('Content-Type: image/jpeg');
				imagejpeg($dst, $last_img.$last_img_name);
				break;
			case 3://PNG
				$last_img_name .= '.png';
				//header('Content-Type: image/png');
				imagepng($dst, $last_img.$last_img_name);
				break;
			default:
				break;
		}
		imagedestroy($dst);

		$fullname = ROOT_PATH.$send_path.$last_img_name;

		$attachment_type_arr =  M('eaterplanet_ecommerce_config')->where( array('name' => 'attachment_type') )->find();

		if( $attachment_type_arr['value'] == 1 )
		{
			save_image_to_qiniu($fullname,$send_path.$last_img_name);
		}else if( $attachment_type_arr['value'] == 2 ){
			save_image_to_alioss($fullname,$send_path.$last_img_name);

		}else if( $attachment_type_arr['value'] == 3 ){

		  save_image_to_txyun($fullname,$send_path.$last_img_name);

		}

		$result = array('full_path' => date('Y-m-d')."/haibao_goods/".$last_img_name,'need_path' => date('Y-m-d')."/haibao_goods/".$last_img_name);



		return $result;
	}



	public function get_weindex_share_image($community_id,$wepro_qrcode,$avatar , $member_id)
	{


		$community_info = D('Home/Front')->get_community_byid($community_id);


		$goods_ids_arr = M()->query('SELECT goods_id FROM ' . C('DB_PREFIX') . "eaterplanet_community_head_goods
					WHERE  head_id=".$community_id."  order by id desc ");

		$ids_arr = array();
		foreach($goods_ids_arr as $val){
			$ids_arr[] = $val['goods_id'];
		}

		$goods_ids_nolimit_arr = M()->query('SELECT id FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_goods
				WHERE  is_all_sale=1 ");


		if( !empty($goods_ids_nolimit_arr) )
		{
			foreach($goods_ids_nolimit_arr as $val){
				$ids_arr[] = $val['id'];
			}
		}


		$ids_str = implode(',',$ids_arr);

		$where = " g.grounding =1    ";

		if( !empty($ids_str) )
		{
			$where .= "  and g.id in ({$ids_str})";
		} else{
			$where .= " and 0 ";
		}

		$community_goods_list = $this->get_community_index_goods('g.*,gc.begin_time,gc.end_time,gc.big_img,gc.is_take_fullreduction ', $where,0,8);


		$send_path = "Uploads/image/".date('Y-m-d')."/";
        $image_dir = ROOT_PATH.$send_path; //上传文件路径

		RecursiveMkdir($image_dir);

		$bg_img = ROOT_PATH."/assets/ep/images/index_share_bg.jpg";

		$need_delete_image_arr = array();

		//index_share_qrcode_bg
		$index_share_qrcode_bg = D('Home/Front')->get_config_by_name('index_share_qrcode_bg');

		if( !empty($index_share_qrcode_bg) )
		{
			$bg_img = ROOT_PATH.'Uploads/image/' . $index_share_qrcode_bg;
		}

		$dst = imagecreatefromjpeg ($bg_img);


		list($dst_w, $dst_h, $dst_type) = getimagesize($bg_img);


		$ttf_path = ROOT_PATH."/assets/ep/fonts/simhei.ttf";
		$msyh_path = ROOT_PATH."/assets/ep/fonts/msyh.ttf";
		$pingfang_path = ROOT_PATH."/assets/ep/fonts/PingFang_Bold.ttf";
		$pingfang_med_path = ROOT_PATH."/assets/ep/fonts/PingFang_Medium.ttf";


		//打上文字

		$black = imagecolorallocate($dst, 20,20,20);//黑色
		$a1a1a1 = imagecolorallocate($dst, 26,26,26);//黑色
		$red = imagecolorallocate($dst, 237, 48, 43); //红色 201 55 49
		$huise = imagecolorallocate($dst, 159, 159, 159); //灰色 159 159 159
		$fense = imagecolorallocate($dst, 248, 136, 161); //粉色 248 136 161
		$gray1 = imagecolorallocate($dst, 51, 51, 51); //#333 51, 51, 51
		$gray2 = imagecolorallocate($dst, 102, 102, 6); //#666 102, 102, 6
		$gray3 = imagecolorallocate($dst, 153, 153, 153); //#999 153, 153, 153

		$gray4 = imagecolorallocate($dst, 116, 116, 116); //#999 116, 116, 116
		$red_2 = imagecolorallocate($dst, 223, 21, 21); //#999 223, 21, 21



		//开始在图上画物体
		imagefttext($dst, 29, 0, 254, 228, $chengse, $pingfang_med_path, date('m月d日').'爆款');

		// 小区名称
		$haibao_group_name = D('Home/Front')->get_config_by_name('haibao_group_name');
		if( empty($haibao_group_name) )
		{
			$haibao_group_name = '小区团长：';
		}
		imagefttext($dst, 20, 0, 32, 1130, $chengse, $pingfang_med_path, $haibao_group_name);
		$group_name_len = mb_strlen($haibao_group_name, 'utf-8');
		$group_left = 0;
		if($group_name_len>4) {
			$group_left = ($group_name_len-4) * 24;
		}

		// 头像
		$avatar = str_replace('Uploads/image/','',$avatar);
		$avatar_img =  resize($avatar,30,30);
		list($avatar_img_img_w, $avatar_img_img_h, $avatar_img_img_type) = getimagesize(ROOT_PATH.$avatar_img);
		$avatar_img_src = imagecreatefromstring(file_get_contents(ROOT_PATH.$avatar_img));
		if (imageistruecolor($avatar_img_src))
				imagetruecolortopalette($avatar_img_src, false, 65535);

		imagecopy($dst, $avatar_img_src, 162+$group_left, 1105, 0, 0, $avatar_img_img_w, $avatar_img_img_h);

		$count = mb_strlen($community_info['disUserName'],'utf-8');

		$xin_str = '';
		for($i=1;$i<$count;$i++)
		{
			$xin_str .="*";
		}
		if($count>2)
		{
			$xin_str = '*'.mb_substr($community_info['disUserName'],-1,1,'utf-8');
		}
		imagefttext($dst, 20, 0, 198+$group_left, 1130, $chengse, $pingfang_med_path, mb_substr($community_info['disUserName'],0,1,'utf-8').$xin_str );

		$modify_index_share_time = D('Home/Front')->get_config_by_name('modify_index_share_time');

		if(empty($modify_index_share_time))
		{
			$modify_index_share_time = date('H:00:00');
		}

		imagefttext($dst, 20, 0, 32, 1170, $chengse, $pingfang_med_path, '抢购时间：'.date('Y-m-d').' '.$modify_index_share_time);


		$open_danhead_model = D('Home/Front')->get_config_by_name('open_danhead_model');

		if ($open_danhead_model!=1) {
			// 团长地址
			$fullAddress = $community_info['fullAddress'];
			$need_fullAddress = mb_substr($fullAddress,0,12,'utf-8')."\r\n";
			$need_fullAddress2 = mb_substr($fullAddress,12,11,'utf-8');
			//.'...'mb_strlen(

			if( mb_strlen($fullAddress,'utf-8') > 23)
			{
				$need_fullAddress2 .= '...';
			}

			imagefttext($dst, 20, 0, 32, 1203, $chengse, $pingfang_med_path, '提货地址：'.$need_fullAddress);
			imagefttext($dst, 20, 0, 160, 1233, $chengse, $pingfang_med_path, $need_fullAddress2);
		}

		$i = 1;
		foreach($community_goods_list as $goods)
		{
			$skuImage = '';
			$good_image = $this->get_goods_images($goods['id']);
			if( empty($good_image) )
			{
				continue;
			}
			$skuImage = $good_image['image'];


			$thumb_goods_img = resize($skuImage,138,138);

			$need_delete_image_arr[] = ROOT_PATH.$thumb_goods_img;

			list($thumb_goods_img_w, $thumb_goods_img_h, $thumb_goods_img_type) = getimagesize(ROOT_PATH.$thumb_goods_img);


			$thumb_goods_img_src = $this->radius_img(ROOT_PATH.$thumb_goods_img, 138/2,3);

			if($thumb_goods_img_type == 'jpeg' || $thumb_goods_img_type == 'jpg')
			{
				$thumb_goods_img .= '.jpg';
				imagejpeg($thumb_goods_img_src, ROOT_PATH.$thumb_goods_img);
			}else{
				$thumb_goods_img .= '.png';
				imagepng($thumb_goods_img_src, ROOT_PATH.$thumb_goods_img);
			}

			imagedestroy($thumb_goods_img_src);

			$goods_src = imagecreatefromstring(file_get_contents(ROOT_PATH.$thumb_goods_img));

			if (imageistruecolor($goods_src))
				imagetruecolortopalette($goods_src, false, 65535);

			list($goods_src_w, $goods_src_h) = getimagesize(ROOT_PATH.$thumb_goods_img);

			$del_x = ($i % 2) == 0 ? 326 : 0;
			$del_y = ( ceil($i/2)-1) * 196;
			imagecopymerge($dst, $goods_src, 58+$del_x, 278+$del_y, 0, 0, $goods_src_w, $goods_src_h, 100);

			$price_arr = $this->get_goods_price($goods['id'] , $member_id);
			$price = $price_arr['price'];

			imagedestroy($goods_src);


			$goods_title = $goods['goodsname'];
			$need_goods_title = mb_substr($goods_title,0,6,'utf-8')."\r\n";
			$need_goods_title .= mb_substr($goods_title,6,5,'utf-8');
			//.'...'mb_strlen(

			if( mb_strlen($goods_title,'utf-8') > 11)
			{
				$need_goods_title .= '...';
			}
			imagefttext($dst, 18, 0, 208+$del_x, 315+$del_y, $gray1, $pingfang_med_path, $need_goods_title );

			imagefttext($dst, 14, 0, 208+$del_x, 375+$del_y, $gray4, $pingfang_med_path, '¥'.$goods['productprice'] );

			$size_12 = strlen($goods['productprice']);
			$pos = 225 + intval(13  * ($size_12 -1) -3 );

			imageline($dst, 225+$del_x, 368+$del_y, $pos+$del_x, 368+$del_y, $gray3); //画线


			imagefttext($dst, 18, 0, 208+$del_x, 410+$del_y, $red_2, $pingfang_path, '¥'.$price );

			//break;
			$i++;
		}

		$thumb_goods_img = $wepro_qrcode;

		$need_delete_image_arr[] = ROOT_PATH.$thumb_goods_img;


		$wepro_qrcode = str_replace('Uploads/image/','',$wepro_qrcode);

		$thumb_goods_img = resize($wepro_qrcode,180,180);

		list($thumb_goods_img_w, $thumb_goods_img_h, $thumb_goods_img_type) = getimagesize(ROOT_PATH.$thumb_goods_img);


		$goods_src = imagecreatefromstring(file_get_contents(ROOT_PATH.$thumb_goods_img));


		$thumb_goods_img_src = imagecreatefromstring(file_get_contents(ROOT_PATH.$thumb_goods_img));
		if (imageistruecolor($thumb_goods_img_src))
				imagetruecolortopalette($thumb_goods_img_src, false, 65535);

		imagecopy($dst, $goods_src, 516, 1098, 0, 0, $thumb_goods_img_w, $thumb_goods_img_h);
		//结束图上画物体

		$last_img = $image_dir;
		$last_img_name = "last_index_".md5( time().$community_id.mt_rand(1,999)).'';

		switch ($dst_type) {
			case 1://GIF
				$last_img_name .= '.gif';
				header('Content-Type: image/gif');
				imagegif($dst, $last_img.$last_img_name);
				break;
			case 2://JPG
				$last_img_name .= '.jpg';
				//header('Content-Type: image/jpeg');
				imagejpeg($dst, $last_img.$last_img_name);
				break;
			case 3://PNG
				$last_img_name .= '.png';
				header('Content-Type: image/png');
				imagepng($dst, $last_img.$last_img_name);
				break;
			default:
				break;
		}
		imagedestroy($dst);

		//imagedestroy($goods_src);

		$result = array('full_path' => $send_path.$last_img_name,'need_path' => $send_path.$last_img_name);

		foreach( $need_delete_image_arr as $del_img )
		{
			@unlink($del_img);
		}

		return $result;
	}


	public function check_qiniu_image($goods_img_info_image)
	{
		global $_W;
		global $_GPC;

		if (!empty($_W['setting']['remote']['type']))
		{
		    $header = array(
		        'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
		        'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
		        'Accept-Encoding: gzip, deflate',);

		    $goods_img = tomedia( $goods_img_info_image);

		    $curl = curl_init();
		    curl_setopt($curl, CURLOPT_URL, $goods_img);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		    curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		    $data = curl_exec($curl);
		    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		    curl_close($curl);




		    if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
		        $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
		    }
		    $img_content=$imgBase64Code;//图片内容
		    //echo $img_content;exit;
		    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result))
		    {
		        $type = $result[2];//得到图片类型png?jpg?gif?

		        $send_path = "images/".date('Y-m-d')."/";
		        $image_dir = ATTACHMENT_ROOT.$send_path; //上传文件路径

		        load()->func('file');
		        mkdirs($image_dir);


		        $new_file = md5($goods_img).".{$type}";

		        $res = file_put_contents($image_dir.$new_file, base64_decode(str_replace($result[1], '', $img_content)));


		        $goods_img_info_image = $send_path.$new_file;

		    }
		}

		return $goods_img_info_image;

	}


	public function get_goods_tags($label_id)
	{

		$tag_info = M('eaterplanet_ecommerce_goods_tags')->field('id,tagname,type,tagcontent')->where( array('id' => $label_id) )->find();

		return $tag_info;
	}

	public function get_min_time(){
		global $_W;
		global $_GPC;

		$now_time = time();

		$where = ' gc.begin_time <='. $now_time.' and gc.end_time > '.$now_time.' and g.id=gc.goods_id and g.grounding=1 and g.is_index_show=1 and gc.is_new_buy=0 and gc.is_spike_buy = 0 and g.type = "normal" ';
		$sql = "select min(gc.end_time) as rushtime from " .C('DB_PREFIX')."eaterplanet_ecommerce_goods as g,".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc where {$where}";


		$rushtime_arr = M()->query($sql);
		$rushtime = $rushtime_arr[0];

		return $rushtime['rushtime'];
	}


	/**
	 * 获取即将抢购商品总数
	 * @return [string] [总数]
	 */
	public function get_comming_goods_count(){

		$now_time = time();
		$where .= " begin_time > {$now_time} ";

		$count = M('eaterplanet_ecommerce_good_common')->where( " begin_time > {$now_time} " )->count();

		return $count;
	}


	//end index_share_qrcode

	public function get_weshare_image($goods_id , $member_id)
	{

		$goods_info = M('eaterplanet_ecommerce_goods')->field('goodsname,price,productprice,sales,seller_count,total,type')->where( array('id' => $goods_id) )->find();

		$goods_img_info = $this->get_goods_images($goods_id);

		$goods_img = ROOT_PATH.'Uploads/image/' . $goods_img_info['image'];


		$goods_price = $this->get_goods_price($goods_id , $member_id);
		$goods_price['market_price'] = $goods_info['productprice'];

		$goods_title = $goods_info['goodsname'];


		$seller_count = $goods_info['seller_count'] + $goods_info['sales'];
		$quantity = $goods_info['total'];

		if($goods_info['type']=='integral') {
		    $goods_price['price'] = round($goods_price['price'], 0);
		    $goods_price['market_price'] = round($goods_price['market_price'], 0);
		}

		$need_img = $this->_get_compare_zan_img($goods_img_info['image'], $goods_title, $goods_price,$seller_count,$quantity,$goods_info['type']);

		//贴上二维码图
		$up_data = array();
		$up_data['wepro_qrcode_image'] = $need_img['need_path'];

		M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $goods_id) )->save( $up_data );

		return true;
	}

	function radius_img($imgpath = './t.png', $radius = 15, $color=1) {
		$ext     = pathinfo($imgpath);
		$src_img = null;

		switch ($ext['extension']) {
		case 'jpg':
			$src_img = imagecreatefromjpeg($imgpath);
			break;
		case 'jpeg':
		$src_img = imagecreatefromjpeg($imgpath);
			break;
		case 'png':
			$src_img = imagecreatefrompng($imgpath);
			break;
		}
		$wh = getimagesize($imgpath);
		$w  = $wh[0];
		$h  = $wh[1];
		// $radius = $radius == 0 ? (min($w, $h) / 2) : $radius;
		$img = imagecreatetruecolor($w, $h);
		//这一句一定要有
		imagesavealpha($img, true);
		//拾取一个完全透明的颜色,最后一个参数127为全透明
		//拾取一个完全透明的颜色,最后一个参数127为全透明 int $red , int $green , int $blu

		if($color == 1)
		{
			$bg = imagecolorallocatealpha($img, 244, 91, 86, 127);
		}else if($color == 2){
			//
			$avatar_rgb = D('Home/Front')->get_config_by_name('avatar_rgb' );
			if( !empty($avatar_rgb) )
			{
				$rgb_arr = $this->hex2rgb($avatar_rgb);

				$bg = imagecolorallocatealpha($img, $rgb_arr['r'], $rgb_arr['g'], $rgb_arr['b'], 127);
			}else{
				$bg = imagecolorallocatealpha($img, 255, 245, 98, 127);
			}

		}else if($color == 3){
			$bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
		}else if($color == 4){
			$bg = imagecolorallocatealpha($img, 252, 243, 10, 127);
		}else if($color == 5){
			$avatar_rgb = D('Home/Front')->get_config_by_name('commiss_avatar_rgb' );
			if( !empty($avatar_rgb) )
			{
				$rgb_arr = $this->hex2rgb($avatar_rgb);

				$bg = imagecolorallocatealpha($img, $rgb_arr['r'], $rgb_arr['g'], $rgb_arr['b'], 127);
			}else{
				$bg = imagecolorallocatealpha($img, 255, 245, 98, 127);
			}
		} else if($color == 6){
			// 详情页海报
			$avatar_rgb = D('Home/Front')->get_config_by_name('goods_avatar_rgb' );
			if( !empty($avatar_rgb) )
			{
				$rgb_arr = $this->hex2rgb($avatar_rgb);
				$bg = imagecolorallocatealpha($img, $rgb_arr['r'], $rgb_arr['g'], $rgb_arr['b'], 127);
			}else{
				$bg = imagecolorallocatealpha($img, 255, 245, 98, 127);
			}
		}


		imagefill($img, 0, 0, $bg);
		$r = $radius; //圆 角半径
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				$rgbColor = imagecolorat($src_img, $x, $y);
				if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
					//不在四角的范围内,直接画
					imagesetpixel($img, $x, $y, $rgbColor);
				} else {
					//在四角的范围内选择画
					//上左
					$y_x = $r; //圆心X坐标
					$y_y = $r; //圆心Y坐标
					if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
						imagesetpixel($img, $x, $y, $rgbColor);
					}
					//上右
					$y_x = $w - $r; //圆心X坐标
					$y_y = $r; //圆心Y坐标
					if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
						imagesetpixel($img, $x, $y, $rgbColor);
					}
					//下左
					$y_x = $r; //圆心X坐标
					$y_y = $h - $r; //圆心Y坐标
					if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
						imagesetpixel($img, $x, $y, $rgbColor);
					}
					//下右
					$y_x = $w - $r; //圆心X坐标
					$y_y = $h - $r; //圆心Y坐标
					if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
						imagesetpixel($img, $x, $y, $rgbColor);
					}
				}
			}
		}
		return $img;
	}

	public function get_user_avatar($url, $member_id,$color=1)
	{

		//wepro_qrcode

		$header = array(
		 'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
		 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
		 'Accept-Encoding: gzip, deflate',);


		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_URL, $url);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // false for https
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		 curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		 curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
		 $data = curl_exec($curl);
		 $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		 curl_close($curl);


		 if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
			$imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
		 }
		 $img_content=$imgBase64Code;//图片内容
		 //echo $img_content;exit;
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result))
		{
			$type = $result[2];//得到图片类型png?jpg?gif?


			$image_dir = ROOT_PATH.'Uploads/image/';
			$send_path = 'goods/'.date('Y-m-d').'/';

			$image_dir .= $send_path;


			RecursiveMkdir($image_dir);

			$new_file = md5($url).".{$type}";



			$res = file_put_contents($image_dir.$new_file, base64_decode(str_replace($result[1], '', $img_content)));


			if ($res)
			{

				list($src_w, $src_h) = getimagesize($image_dir.$new_file);

				if($color != 1)
				{

					$thumb_image_name = resize($send_path.$new_file,  32,32);

					$new_file = $thumb_image_name;


					//$new_file = str_replace($send_path,'',$new_file);
					$imgg = $this->radius_img(ROOT_PATH.$new_file, 32/2,$color);
				}else{


					$imgg = $this->radius_img($image_dir.$new_file, $src_w/2,$color);

					$new_file = 'Uploads/image/'.$send_path.$new_file;

				}




				if($type == 'jpeg' || $type == 'jpg')
				{
					imagejpeg($imgg, ROOT_PATH.$new_file);
				}else{
					imagepng($imgg, ROOT_PATH.$new_file);
				}
				//imagepng($imgg);

				//imagegif($imgg)

				imagedestroy($imgg);


				M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->save( array('wepro_qrcode' => $new_file) );

				return $new_file;
			}else{
				return '';
			}
		}
	}

	/**
	 * 商品详情海报头像
	 * @param  [type]  $url       [description]
	 * @param  [type]  $member_id [description]
	 * @param  integer $color     [description]
	 * @return [type]             [description]
	 */
	public function get_goods_user_avatar($url, $member_id, $color=1)
	{
		$header = array(
			'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
			'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
			'Accept-Encoding: gzip, deflate',
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // false for https
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	 	curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
	 	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	 	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
	 	$data = curl_exec($curl);
	 	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	 	curl_close($curl);

	 	if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
			$imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
	 	}

		$img_content=$imgBase64Code;//图片内容
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result))
		{
			$type = $result[2];//得到图片类型png?jpg?gif?
			$image_dir = ROOT_PATH.'Uploads/image/';
			$send_path = 'goods/'.date('Y-m-d').'/';
			$image_dir .= $send_path;

			RecursiveMkdir($image_dir);
			$new_file = md5($url).".{$type}";
			$res = file_put_contents($image_dir.$new_file, base64_decode(str_replace($result[1], '', $img_content)));

			if ($res)
			{
				list($src_w, $src_h) = getimagesize($image_dir.$new_file);
				$imgg = $this->radius_img($image_dir.$new_file, $src_w/2,$color);
				$new_file = 'Uploads/image/'.$send_path.$new_file;

				if($type == 'jpeg' || $type == 'jpg')
				{
					imagejpeg($imgg, ROOT_PATH.$new_file);
				}else{
					imagepng($imgg, ROOT_PATH.$new_file);
				}

				imagedestroy($imgg);
				return $new_file;
			}else{
				return '';
			}
		}
	}

	public function get_commission_user_avatar($url, $member_id,$color=1)
	{
		global $_W;
		global $_GPC;

		//wepro_qrcode

		$header = array(
		 'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
		 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
		 'Accept-Encoding: gzip, deflate',);


		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_URL, $url);
		 if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
		     curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		 }
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // false for https
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		 curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		 curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		 $data = curl_exec($curl);
		 $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		 curl_close($curl);


		 if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
			$imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
		 }
		 $img_content=$imgBase64Code;//图片内容
		 //echo $img_content;exit;
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result))
		{

	$type = $result[2];//得到图片类型png?jpg?gif?


			$image_dir = ROOT_PATH.'Uploads/image/';
			$send_path = 'goods/'.date('Y-m-d').'/';

			$image_dir .= $send_path;


			RecursiveMkdir($image_dir);

			$new_file = md5($url).".{$type}";



			$res = file_put_contents($image_dir.$new_file, base64_decode(str_replace($result[1], '', $img_content)));




			if ($res)
			{
				list($src_w, $src_h) = getimagesize($image_dir.$new_file);

				if($color == 5)
				{
					$imgg = $this->radius_img($image_dir.$new_file, $src_w/2,5);
				}
				else if($color != 1)
				{
					$thumb_image_name = resize($send_path.$new_file,  100,100);

					$new_file = $thumb_image_name;

					$new_file = str_replace($send_path,'',$new_file);
					$imgg = $this->radius_img($image_dir.$new_file, 100/2,$color);
				}else{
					$imgg = $this->radius_img($image_dir.$new_file, $src_w/2,$color);
				}

				if($type == 'jpeg' || $type == 'jpg')
				{
					imagejpeg($imgg, $image_dir.$new_file);
				}else{
					imagepng($imgg, $image_dir.$new_file);
				}
				imagedestroy($imgg);

				return $send_path.$new_file;
			}else{
				return '';
			}
		}
	}


	public function _get_compare_qrcode_bgimg($bg_img, $qrcode_img,$avatar_image,$username, $s_x = '520',$s_y = '900')
	{
		//$image_dir = ROOT_PATH.'Uploads/image/';

		$send_path = "Uploads/image/".date('Y-m-d')."/";
        $image_dir = ROOT_PATH.$send_path; //上传文件路径

		RecursiveMkdir($image_dir);


		$thumb_image_name = resize($qrcode_img,  230,230);



		$thumb_qrcode_img  = ROOT_PATH.$thumb_image_name;

		$thumb_avatar_img  = ROOT_PATH.$avatar_image;

		$bg_img = ROOT_PATH.$bg_img;

		$dst = imagecreatefromjpeg ($bg_img);
		$src = imagecreatefromstring(file_get_contents($thumb_qrcode_img));



		$src_avatar = imagecreatefromstring(file_get_contents($thumb_avatar_img));

		if (imageistruecolor($src))
			imagetruecolortopalette($src, false, 65535);

		list($src_w, $src_h) = getimagesize($thumb_qrcode_img);
		list($dst_w, $dst_h, $dst_type) = getimagesize($bg_img);
		imagecopymerge($dst, $src, 442, 1020, 0, 0, $src_w, $src_h, 100);

		list($src_w, $src_h) = getimagesize($thumb_avatar_img);
		//list($dst_w, $dst_h, $dst_type) = getimagesize($bg_img);
		imagecopymerge($dst, $src_avatar, 65, 45, 0, 0, $src_w, $src_h, 100);

		$last_img = $image_dir;


		$pingfang_path = ROOT_PATH."/assets/ep/fonts/PingFang_Bold.ttf";
		$pingfang_med_path = ROOT_PATH."/assets/ep/fonts/PingFang_Medium.ttf";

		$gray1 = imagecolorallocate($dst, 23, 23, 23); //#333 51, 51, 51
		$white = imagecolorallocate($dst, 255, 255, 255); //#333 51, 51, 51
		$yellow = imagecolorallocate($dst, 255, 255, 0); //#333 51, 51, 51

		imagefttext($dst, 20, 0, 470, 1297, $gray1, $pingfang_med_path, '长按识别小程序');

		// $username = "试试我可以有多长，好长好长好长好长好长好长";
		$username = mb_substr($username,0,12,'utf-8');
		imagefttext($dst, 30, 0, 212, 94, $white, $pingfang_med_path, '@'.$username);

		$desc_txt = "分享了一个好东西";
		imagefttext($dst, 26, 0, 212, 148, $yellow, $pingfang_med_path, $desc_txt);



		$last_img_name = "last_qrcode".md5( time().$bg_img.$qrcode_img).'';

		switch ($dst_type) {
			case 1://GIF
				$last_img_name .= '.gif';
				header('Content-Type: image/gif');
				imagegif($dst, $last_img.$last_img_name);
				break;
			case 2://JPG
				$last_img_name .= '.jpg';
				//header('Content-Type: image/jpeg');
				imagejpeg($dst, $last_img.$last_img_name);
				break;
			case 3://PNG
				$last_img_name .= '.png';
				header('Content-Type: image/png');
				imagepng($dst, $last_img.$last_img_name);
				break;
			default:
				break;
		}
		imagedestroy($dst);
		imagedestroy($src);
		//imagedestroy($goods_src);
		//imagedestroy($avatar_src);

		//return_file_path
		$result = array('full_path' => $send_path.$last_img_name,'need_path' => $send_path.$last_img_name);


		return $result;
	}

	public function _get_commmon_wxqrcode($path='',$scene)
	{


		/**
			$jssdk = new \Lib\Weixin\Jssdk( C('weprogram_appid'), C('weprogram_appscret') );

			$weqrcode = $jssdk->getAllWeQrcode('pages/order/hexiao_bind',SELLERUID.'_0' );

			//保存图片

			$image_dir = ROOT_PATH.'Uploads/image/goods';
			$image_dir .= '/'.date('Y-m-d').'/';

			$file_path = C('SITE_URL').'Uploads/image/goods/'.date('Y-m-d').'/';
			$kufile_path = $dir.'/'.date('Y-m-d').'/';

			RecursiveMkdir($image_dir);
			$file_name = md5('qrcode_'.$pick_order_info['pick_sn'].time()).'.png';

			file_put_contents($image_dir.$file_name, $weqrcode);
		**/


		$weixin_config = array();
		$weixin_config['appid'] = D('Home/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Home/Front')->get_config_by_name('wepro_appsecret');

		$qrcode_rgb = D('Home/Front')->get_config_by_name('qrcode_rgb');
		if( !empty($qrcode_rgb) )
		{
			$qrcode_arr = $this->hex2rgb($qrcode_rgb);
		}

		//qrcode

		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'] ,$weixin_config['appscert'] );


		//$weqrcode = $jssdk->getAllWeQrcode($path,$scene);

		$weqrcode = $jssdk->getAllWeQrcode($path,$scene ,false,$qrcode_arr);


		$res_ck = json_decode($weqrcode, true);
		if( !empty($res_ck)  &&  isset($res_ck['errcode']) )
		{
			return '';
		}else {

			//保存图片

			$image_dir = ROOT_PATH.'Uploads/image/';
			$image_dir .= 'goods'.date('Y-m-d').'/';



			RecursiveMkdir($image_dir);
			$file_name = md5('qrcode_'.$weqrcode.time()).'.png';

			file_put_contents($image_dir.$file_name, $weqrcode);

			//.......

			$attachment_type_arr =  M('eaterplanet_ecommerce_config')->where( array('name' => 'attachment_type') )->find();

			if( $attachment_type_arr['value'] == 1 )
			{
				save_image_to_qiniu($image_dir.$file_name,'Uploads/image/'.'goods'.date('Y-m-d').'/'.$file_name);
			}else if( $attachment_type_arr['value'] == 2 ){
				save_image_to_alioss($image_dir.$file_name,'Uploads/image/'.'goods'.date('Y-m-d').'/'.$file_name);

			}else if( $attachment_type_arr['value'] == 3 ){

				$res =	save_image_to_txyun($image_dir.$file_name,'Uploads/image/'.'goods'.date('Y-m-d').'/'.$file_name);

			}

			return 'goods'.date('Y-m-d').'/'.$file_name;

		}

	}

	function hex2rgb( $colour ) {
		if ( $colour[0] == '#' ) {
			$colour = substr( $colour, 1 );
		}
		if ( strlen( $colour ) == 6 ) {
			list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
		} elseif ( strlen( $colour ) == 3 ) {
			list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
		} else {
			return false;
		}
		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );
		return array( 'r' => $r, 'g' => $g, 'b' => $b );
	}


	public function _get_index_wxqrcode($member_id,$community_id,$suffix="png")
	{


		$weixin_config = array();
		$weixin_config['appid'] = D('Home/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Home/Front')->get_config_by_name('wepro_appsecret');

		$qrcode_rgb = D('Home/Front')->get_config_by_name('qrcode_rgb');

		$qrcode_arr = array();

		if( !empty($qrcode_rgb) )
		{
			$qrcode_arr = $this->hex2rgb($qrcode_rgb);
		}

		//qrcode
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'] ,$weixin_config['appscert'] );
		$weqrcode = $jssdk->getAllWeQrcode('eaterplanet_ecommerce/pages/index/index',$community_id .'_'. $member_id,$suffix=="png",$qrcode_arr);
		//line_color


		//保存图片
		$send_path = "Uploads/image/".date('Y-m-d')."/";
        $image_dir = ROOT_PATH.$send_path; //上传文件路径
		RecursiveMkdir($image_dir);


		$file_name = md5('qrcode_'.$goods_id.'_'.$member_id.time()).'.'.$suffix;
		//qrcode

		file_put_contents($image_dir.$file_name, $weqrcode);
		return $send_path.$file_name;
	}


	public function _get_goods_user_wxqrcode($goods_id,$member_id,$community_id)
	{

		$weixin_config = array();
		$weixin_config['appid'] = D('Home/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Home/Front')->get_config_by_name('wepro_appsecret');


		//qrcode
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'] ,$weixin_config['appscert'] );

		$qrcode_rgb = D('Home/Front')->get_config_by_name('qrcode_rgb');
		if( !empty($qrcode_rgb) )
		{
			$qrcode_arr = $this->hex2rgb($qrcode_rgb);
		}


		//eaterplanet_ecommerce/pages/goods/goodsDetail
		if($goods_id == 0)
		{
			$weqrcode = $jssdk->getAllWeQrcode('eaterplanet_ecommerce/pages/index/index',$goods_id.'_'.$member_id.'_'.$community_id ,false,$qrcode_arr);
		}else{

			$gd_info = M('eaterplanet_ecommerce_goods')->field('type')->where( array('id'=>$goods_id) )->find();

			if( $gd_info['type'] == 'pin' )
			{
				$weqrcode = $jssdk->getAllWeQrcode('eaterplanet_ecommerce/moduleA/pin/goodsDetail',$goods_id.'_'.$member_id.'_'.$community_id ,false,$qrcode_arr);
			}else{

				$weqrcode = $jssdk->getAllWeQrcode('eaterplanet_ecommerce/pages/goods/goodsDetail',$goods_id.'_'.$member_id.'_'.$community_id ,false,$qrcode_arr);
			}

		}

		//line_color
		//var_dump($weqrcode);die();

		//保存图片


		$send_path = "Uploads/image/goods/".date('Y-m-d')."/";
        $image_dir = ROOT_PATH.$send_path; //上传文件路径

		$send_path_re = "goods/".date('Y-m-d')."/";

		RecursiveMkdir($image_dir);

		$file_name = md5('qrcode_'.$goods_id.'_'.$member_id.time()).'.jpg';
		//qrcode
		file_put_contents($image_dir.$file_name, $weqrcode);
		return $send_path_re.$file_name;
	}


	public function _get_compare_zan_img($goods_img,$goods_title,$goods_price, $seller_count,$quantity,$type="normal")
	{

		$send_path = "Uploads/image/goods/".date('Y-m-d')."/";
        $image_dir = ROOT_PATH.$send_path; //上传文件路径
		$send_path_re = "goods/".date('Y-m-d')."/";

		RecursiveMkdir($image_dir);
		//
		$bg_img = ROOT_PATH."/assets/ep/images/bg2.jpg";

		$haibao_gooods_bg2 = D('Home/Front')->get_config_by_name('haibao_gooods_bg2');

		if( isset($haibao_gooods_bg2) && !empty($haibao_gooods_bg2) )
		{
			$bg_img_path = ROOT_PATH."/Uploads/image/".$haibao_gooods_bg2;

			if( file_exists($bg_img_path) )
			{
				$bg_img = $bg_img_path;
			}
		}


		$thumb_goods_name = "thumb_img".md5($goods_img).'.png';
		$thumb_goods_img = resize($goods_img,700,700);



		$image_dir = ROOT_PATH.$send_path;


		$return_file_path = $send_path;


		//$image_dir.$thumb_avatar_name
		//商品图片 25 215
		//文字：65 955
		//长按二维码领取： 517 640
		//商品文字： 24  710
		//快和我一起领取吧： 24 817
		//市场价，单价 24 895

		//var_dump($thumb_goods_img);die();

		//$dst = imagecreatefromstring(file_get_contents($bg_img));


		$dst = imagecreatefromjpeg ($bg_img);
		$goods_src = imagecreatefromstring(file_get_contents(ROOT_PATH.$thumb_goods_img));

		if (imageistruecolor($goods_src))
			imagetruecolortopalette($goods_src, false, 65535);

		list($goods_src_w, $goods_src_h) = getimagesize(ROOT_PATH.$thumb_goods_img);
		list($dst_w, $dst_h, $dst_type) = getimagesize($bg_img);

		imagecopymerge($dst, $goods_src, 25, 215, 0, 0, $goods_src_w, $goods_src_h, 100);

		//imagecopymerge($dst, $avatar_src, 24, 615, 0, 0, $avatar_w, $avatar_h, 100);

		//IA_ROOT."/addons/eaterplanet_ecommerce/assets/ep/fonts/simhei.ttf"
		//$ttf_path = ROOT_PATH."Common/js/simhei.ttf";

		$ttf_path = ROOT_PATH."/assets/ep/fonts/simhei.ttf";
		$msyh_path = ROOT_PATH."/assets/ep/fonts/msyh.ttf";
		$pingfang_path = ROOT_PATH."/assets/ep/fonts/PingFang_Bold.ttf";
		$pingfang_med_path = ROOT_PATH."/assets/ep/fonts/PingFang_Medium.ttf";



		//打上文字

		$black = imagecolorallocate($dst, 20,20,20);//黑色
		$red = imagecolorallocate($dst, 237, 48, 43); //红色 201 55 49
		$huise = imagecolorallocate($dst, 159, 159, 159); //灰色 159 159 159
		$fense = imagecolorallocate($dst, 248, 136, 161); //粉色 248 136 161
		$gray1 = imagecolorallocate($dst, 51, 51, 51); //#333 51, 51, 51
		$gray2 = imagecolorallocate($dst, 102, 102, 6); //#666 102, 102, 6
		$gray3 = imagecolorallocate($dst, 153, 153, 153); //#999 153, 153, 153



		$chengse = imagecolorallocate($dst, 252, 74, 74); //#999
		//ffb7d7 248 136 161


		// $goods_title = "我免费领取了【大白兔奶糖果零食铁盒装114g】的所得税的色舞认太热太热太热";
		$goods_title = $goods_title;
		$need_goods_title = mb_substr($goods_title,0,18,'utf-8')."\r\n";
		$need_goods_title .= mb_substr($goods_title,18,9,'utf-8');
		//.'...'mb_strlen(

		if( mb_strlen($goods_title,'utf-8') > 28)
		{
			$need_goods_title .= '...';
		}


		//imagefttext($dst, 25, 0, 120, 660, $black, $ttf_path, $username);
		//imagefttext($dst, 15, 0, 518, 920, $huise, $ttf_path, '长按二维码领取'); 65 955
		imagefttext($dst, 26, 0, 64, 987, $gray1, $pingfang_med_path, $need_goods_title);
		// imagefttext($dst, 15, 0, 25, 1040, $fense, $ttf_path, "限时爆款价");

		if($type=='integral') {
			imagefttext($dst, 22, 0, 64, 1165, $chengse, $pingfang_path, "团购价:");
			imagefttext($dst, 32, 0, 178, 1168, $chengse, $pingfang_path, $goods_price['price'].'积分');

			$size_1 = sprintf('%.2f',$goods_price['market_price']);
			imagefttext($dst, 18, 0, 64, 1115, $gray3, $pingfang_med_path, "原价: ¥".$size_1 );
		} else {
			imagefttext($dst, 22, 0, 64, 1165, $chengse, $pingfang_path, "团购价:");
			imagefttext($dst, 32, 0, 178, 1168, $chengse, $pingfang_path, '¥'.$goods_price['price']);

			$size_1 = sprintf('%.2f',$goods_price['market_price']);
			imagefttext($dst, 18, 0, 64, 1115, $gray3, $pingfang_med_path, "原价:¥".$size_1 );
		}
		$size_12 = strlen($size_1);

		$pos = 145 + intval(15  * ($size_12-1)+5);

		imageline($dst, 122, 1105, $pos, 1105, $gray3); //画线
		//imageline($dst, 122, 1105, $pos, 1105, $gray3); //画线


		imagefttext($dst, 16, 0, 64, 1270, $chengse, $pingfang_med_path, "已售{$seller_count}件");
		imagefttext($dst, 16, 0, 212, 1270, $chengse, $pingfang_med_path, "仅剩{$quantity}件");

		//$seller_count,$quantity  已售10件


		$last_img = $image_dir;

		$last_img_name = "last_avatar".md5( time().$need_goods_title.$username).'';

		switch ($dst_type) {
			case 1://GIF
				$last_img_name .= '.gif';
				header('Content-Type: image/gif');
				imagegif($dst, $last_img.$last_img_name);
				break;
			case 2://JPG
				$last_img_name .= '.jpg';
				//header('Content-Type: image/jpeg');
				imagejpeg($dst, $last_img.$last_img_name);
				break;
			case 3://PNG
				$last_img_name .= '.png';
				header('Content-Type: image/png');
				imagepng($dst, $last_img.$last_img_name);
				break;
			default:
				break;
		}
		imagedestroy($dst);

		imagedestroy($goods_src);
		//imagedestroy($avatar_src); imageistruecolor

		//return_file_path
		$result = array('full_path' => $send_path.$last_img_name,'need_path' => $send_path_re.$last_img_name);





		return $result;
	}

	/**
		关注取消商品收藏
		删除返回1
	**/
	public function user_fav_goods_toggle($goods_id, $member_id)
	{

		$res = $this->check_goods_fav($goods_id, $member_id);

		if($res)
		{
			//删除
			M('eaterplanet_ecommerce_user_favgoods')->where( array('goods_id' => $goods_id,'member_id' => $member_id) )->delete();
			return 1;
		} else {
			//添加
			$data = array();
			$data['member_id'] = $member_id;
			$data['goods_id'] = $goods_id;
			$data['add_time'] = time();

			M('eaterplanet_ecommerce_user_favgoods')->add($data);
			return 2;
		}
	}
	public function check_goods_fav($goods_id, $member_id)
	{

		$user_favgoods = M('eaterplanet_ecommerce_user_favgoods')->where( array('member_id' => $member_id, 'goods_id' => $goods_id) )->find();


		if(!empty($user_favgoods))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
		获取商品的分佣金额
	**/
	public function get_goods_commission_info($goods_id,$member_id, $is_parse = false)
	{
		$result = array();
		//1 比例，2固定金额
		$result['commiss_one'] = array('money' => 0,'fen' => 0, 'type' => 1);
		$result['commiss_two'] =  array('money' => 0,'fen' => 0, 'type' => 1);
		$result['commiss_three'] = array('money' => 0,'fen' => 0, 'type' => 1);

		$goods_commiss = M('eaterplanet_ecommerce_good_commiss')->where( array('goods_id' => $goods_id ) )->find();


		$gd_info = M('eaterplanet_ecommerce_goods')->field('type')->where( array('id' => $goods_id ) )->find();


		if($goods_commiss['nocommission'] == 1 || $gd_info['type'] == 'integral' )
		{
			return $result;
		}else{

			//hascommission
			if($goods_commiss['hascommission'] == 1 || $is_parse)
			{
				//自定义商品分佣
				if( !empty($goods_commiss['commission1_rate']) && $goods_commiss['commission1_rate'] >0 )
				{
					$result['commiss_one'] = array('money' => 0,'fen' => $goods_commiss['commission1_rate'], 'type' => 1);
				}else{
					$result['commiss_one'] = array('money' => $goods_commiss['commission1_pay'],'fen' => 0, 'type' => 2);
				}

				if( !empty($goods_commiss['commission2_rate']) && $goods_commiss['commission2_rate'] >0 )
				{
					$result['commiss_two'] = array('money' => 0,'fen' => $goods_commiss['commission2_rate'], 'type' => 1);
				}else{
					$result['commiss_two'] = array('money' => $goods_commiss['commission2_pay'],'fen' => 0, 'type' => 2);
				}

				if( !empty($goods_commiss['commission3_rate']) && $goods_commiss['commission3_rate'] >0 )
				{
					$result['commiss_three'] = array('money' => 0,'fen' => $goods_commiss['commission3_rate'], 'type' => 1);
				}else{
					$result['commiss_three'] = array('money' => $goods_commiss['commission3_pay'],'fen' => 0, 'type' => 2);
				}
				$parent_info = D('Home/Commission')->get_member_parent_list($member_id);

				$result['parent_info'] = $parent_info;


			}else{

				//是否开启分销内购 commiss_selfbuy

				$commiss_level_info = D('Home/Commission')->get_commission_level();

				$commiss_selfbuy = D('Home/Front')->get_config_by_name('commiss_selfbuy');

				$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $member_id) )->find();

				$parent_info = D('Home/Commission')->get_member_parent_list($member_id);

				if($commiss_selfbuy == 1)
				{
					//开启分销内购
					if( $member_info['comsiss_state'] == 1 && $member_info['comsiss_flag'] == 1 )
					{
						$parent_info['self_go'] = array('member_id' =>$member_id, 'level_id' => $member_info['commission_level_id']);
					}
				}

				//开始分析分佣比例

				if( isset($parent_info['self_go']) && !empty($parent_info['self_go']['member_id']) )
				{
					$result['commiss_one'] = array('money' => 0,'fen' => $commiss_level_info[$parent_info['self_go']['level_id'] ]['commission'], 'type' => 1);
					$result['commiss_two'] =  array('money' => 0,'fen' => $commiss_level_info[$parent_info['one']['level_id']]['commission2'], 'type' => 1);
					$result['commiss_three'] = array('money' => 0,'fen' => $commiss_level_info[$parent_info['two']['level_id']]['commission3'], 'type' => 1);

				}else{
					$result['commiss_one'] = array('money' => 0,'fen' => $commiss_level_info[$parent_info['one']['level_id']]['commission'], 'type' => 1);
					$result['commiss_two'] =  array('money' => 0,'fen' => $commiss_level_info[$parent_info['two']['level_id']]['commission2'], 'type' => 1);
					$result['commiss_three'] = array('money' => 0,'fen' => $commiss_level_info[$parent_info['three']['level_id']]['commission3'], 'type' => 1);
				}
			}


			return $result;
		}
	}


	/**
	   获取商品列表
	**/
	public function get_goods_list($fields='*', $where='1=1',$order='index_sort desc ,seller_count desc,id asc',$offset=0,$perpage=10)
	{
		$list = M('eaterplanet_ecommerce_goods')->field($fields)->where($where)->order($order)->limit($offset,$perpage)->select();

	    return $list;
	}

	/**
		获取商品图片
	**/
	public function get_goods_images($goods_id, $limit =1)
	{

		if($limit == 1)
		{
			$image_info = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $goods_id) )->order('id asc')->find();

			return $image_info;

		}else{
			$image_list = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $goods_id) )->order('id asc')->select();

			return $image_list;
		}
	}

	/**
		商品喜欢的数量
	**/
	public function fav_goods_count($goods_id)
	{

		$total = M('eaterplanet_ecommerce_user_favgoods')->where( array('goods_id' => $goods_id) )->count();

		return $total;
	}


	/**
		客户喜欢商品状态
	**/
	public function fav_goods_state($goods_id, $member_id)
	{
		$fav_info = M('eaterplanet_ecommerce_user_favgoods')->where( array('goods_id' => $goods_id, 'member_id' => $member_id) )->find();

		return $fav_info;
	}

   /**
		获取商品价格
	**/
	public function get_goods_price($goods_id,$member_id = 0)
	{
		$price_arr = array();

		$goods_info = M('eaterplanet_ecommerce_goods')->field('is_take_vipcard,price as danprice,type,card_price')->where( array('id' => $goods_id))->find();

		if($goods_info['type'] =='pin')
		{

			$pin_goods_info = M('eaterplanet_ecommerce_good_pin')->field('pinprice,pin_count')->where( array('goods_id' => $goods_id ) )->find();

			if(!empty($pin_goods_info))
			{
				$price_arr = array('price' =>$pin_goods_info['pinprice'],'danprice' =>$goods_info['danprice'],  'pin_price' =>$pin_goods_info['pinprice'],'pin_count' => $pin_goods_info['pin_count']);

				$option_price_arr = M('eaterplanet_ecommerce_goods_option_item_value')->field('marketprice as dan_price')->where( array('goods_id' => $goods_id ) )->order('marketprice asc')->find();

				if( !empty($option_price_arr) )
				{
					$price_arr['danprice'] = $option_price_arr['dan_price'];
				}

				$option_pinprice_arr = M('eaterplanet_ecommerce_goods_option_item_value')->field('pinprice as pin_price')->where( array('goods_id' => $goods_id) )->order('pinprice asc')->find();

				$max_option_pinprice_arr = M('eaterplanet_ecommerce_goods_option_item_value')->field('pinprice as pin_price')->where( array('goods_id' => $goods_id) )->order('pinprice desc')->find();

				if( !empty($option_pinprice_arr) )
				{
					$price_arr['price'] = $option_pinprice_arr['pin_price'];
					$price_arr['pin_price'] = $option_pinprice_arr['pin_price'];
				}

				if( $max_option_pinprice_arr['pin_price'] >  $option_pinprice_arr['pin_price'])
				{
					$price_arr['max_pinprice'] = $max_option_pinprice_arr['pin_price'];
				}
			}


		}else{
			//获取最低价格
			$option_price_arr = M('eaterplanet_ecommerce_goods_option_item_value')->field('id,marketprice as dan_price')
								->where( array('goods_id' => $goods_id) )->order('marketprice asc')->find();

			$max_option_price_arr = M('eaterplanet_ecommerce_goods_option_item_value')
									->field('id,marketprice as dan_price')->where( array('goods_id' => $goods_id) )
									->order('marketprice desc')->find();

			if( !empty($option_price_arr) && $option_price_arr['dan_price'] >= 0.01)
			{
				$price_arr = array('price' => $option_price_arr['dan_price'],'danprice' => $option_price_arr['dan_price']);

				if( $max_option_price_arr['dan_price'] >  $option_price_arr['dan_price'])
				{

					$price_arr['max_danprice'] = $max_option_price_arr['dan_price'];
				}
			}else{

				$price_arr = array('price' => $goods_info['danprice'],'danprice' => $goods_info['danprice']);
			}

			$option_cardprice_arr = M('eaterplanet_ecommerce_goods_option_item_value')->field('id,card_price')->where( array('goods_id' => $goods_id ) )->order('card_price asc')->find();

			if( !empty($option_cardprice_arr) && $option_cardprice_arr['card_price'] >= 0.01)
			{
				$price_arr['card_price'] = $option_cardprice_arr['card_price'];
			}else{
				$price_arr['card_price'] = $goods_info['card_price'];
			}

		}
		//修改商品独立客户等级折扣设置 2020.05.11
		$goods_common = M('eaterplanet_ecommerce_good_common')->field('is_mb_level_buy,has_mb_level_buy,mb_level_buy_list')->where( array('goods_id' => $goods_id ) )->find();

		$price_arr['is_mb_level_buy'] = 0;
		//新增的客户折扣 begin
		 if($goods_info['type'] !='pin')
		 {
			 //商品独立客户等级折扣 begin
			 if($member_id > 0 && $goods_common['has_mb_level_buy'] == 1) {
				 $member_info = M('eaterplanet_ecommerce_member')->field('level_id')->where(array('member_id' => $member_id))->find();

				 if ($member_info['level_id'] > 0){
					 $mb_level_buy_list = unserialize($goods_common['mb_level_buy_list']);
					 $mb_level_discount_list = array();
					 foreach($mb_level_buy_list as $k=>$v){
						 $mb_level_discount_list[$v['level_id']] = $v['discount'];
					 }

					 if( $mb_level_discount_list[$member_info['level_id']]>0 && $mb_level_discount_list[$member_info['level_id']] <100 )
					 {
						 if(isset($mb_level_discount_list[$member_info['level_id']]) && !empty($mb_level_discount_list[$member_info['level_id']])){
							 $vipprice = round( ($price_arr['price'] *  $mb_level_discount_list[$member_info['level_id']]) /100 ,2);
							 $vaipdanprice = round( ($price_arr['danprice'] *  $mb_level_discount_list[$member_info['level_id']]) /100 ,2);

							 $price_arr['levelprice'] = sprintf('%.2f', $vipprice );
							 $price_arr['leveldanprice'] = sprintf('%.2f', $vaipdanprice );
						 }else{
							 $price_arr['levelprice'] = sprintf('%.2f', $price_arr['price'] );
							 $price_arr['leveldanprice'] = sprintf('%.2f', $price_arr['danprice'] );
						 }
						 $price_arr['is_mb_level_buy'] = 1;
					 }

				 }else{
					 $price_arr['levelprice'] = sprintf('%.2f', $price_arr['price'] );
					 $price_arr['leveldanprice'] = sprintf('%.2f', $price_arr['danprice'] );
					 $price_arr['is_mb_level_buy'] = 0;
				 }

				 //商品独立客户等级折扣 end
			 }else{
				 if($member_id >0 && $goods_common['is_mb_level_buy'] == 1 )
				 {

					 $member_info = M('eaterplanet_ecommerce_member')->field('level_id')->where( array('member_id' => $member_id ) )->find();

					 if( $member_info['level_id'] > 0)
					 {

						 $member_level_info = M('eaterplanet_ecommerce_member_level')->where( array('id' => $member_info['level_id'] ) )->find();

						 if( $member_level_info['discount']>0 && $member_level_info['discount']<100 )
						 {
							 $vipprice = round( ($price_arr['price'] *  $member_level_info['discount']) /100 ,2);
							 $vaipdanprice = round( ($price_arr['danprice'] *  $member_level_info['discount']) /100 ,2);

							 $price_arr['levelprice'] = sprintf('%.2f', $vipprice );
							 $price_arr['leveldanprice'] = sprintf('%.2f', $vaipdanprice );
							 $price_arr['is_mb_level_buy'] = 1;

						 }

					 }else{
						 $price_arr['levelprice'] = sprintf('%.2f', $price_arr['price'] );
						 $price_arr['leveldanprice'] = sprintf('%.2f', $price_arr['danprice'] );
						 $price_arr['is_mb_level_buy'] = 0;
					 }

				 }
			 }
		}


        //1、开启未登录不显示价格，
        $is_login_showprice = D('Home/Front')->get_config_by_name('is_login_showprice');

		 //-888给后台使用
        if( !empty($is_login_showprice) && $is_login_showprice == 1  && $member_id != -888)
        {


            $member_info = M('eaterplanet_ecommerce_member')->where(array('member_id' => $member_id))->find();

            if( empty($member_id) || $member_id <= 0  || $member_info['is_apply_state'] == 0 )
            {
                /**
                $price_arr = array(
                'price' =>$pin_goods_info['pinprice'],
                'danprice' =>$goods_info['danprice'],
                'pin_price' =>$pin_goods_info['pinprice'],
                );

                $price_arr['max_pinprice']
                $price_arr['card_price']
                $price_arr['levelprice'] = sprintf('%.2f', $vipprice );
                $price_arr['leveldanprice'] = sprintf('%.2f', $vaipdanprice );
                 **/
                if( isset($price_arr['price']) )
                {
                    $price_arr['price'] = '--';
                }
                if( isset($price_arr['danprice']) )
                {
                    $price_arr['danprice'] = '--';
                }
                if( isset($price_arr['pin_price']) )
                {
                    $price_arr['pin_price'] = '--';
                }
                if( isset($price_arr['max_pinprice']) )
                {
                    $price_arr['max_pinprice'] = '--';
                }
                if( isset($price_arr['card_price']) )
                {
                    $price_arr['card_price'] = '--';
                }
                if( isset($price_arr['levelprice']) )
                {
                    $price_arr['levelprice'] = '--';
                }
                if( isset($price_arr['leveldanprice']) )
                {
                    $price_arr['leveldanprice'] = '--';
                }
            }
        }


        return $price_arr;
    }


	public function get_goods_description($goods_id,$fields='*')	{
		$goods_info =  M('goods_description')->field($fields)->where( array('goods_id' => $goods_id) )->find();
		return $goods_info;
	}

	public function get_goods_options($goods_id,$member_id =0)
	{

		$result = array();
        $goods_option_name = array();
        $goods_option_data = array();

		$goods_info = M('eaterplanet_ecommerce_goods')->field('goodsname,productprice')->where( array('id' => $goods_id ) )->find();

		$goods_common = M('eaterplanet_ecommerce_good_common')->field('is_mb_level_buy')->where( array('goods_id' => $goods_id ) )->find();


		$goods_option_query = M('eaterplanet_ecommerce_goods_option')->where( array('goods_id' => $goods_id) )->select();

		$sku_goods_image = '';
		$good_image = $this->get_goods_images($goods_id);
		if( !empty($good_image) )
		{
			$sku_goods_image = tomedia($good_image['image']);
		}


    	if( !empty($goods_option_query) )
    	{
    		$option_item_image = array();
    		foreach ($goods_option_query as $goods_option) {
	            $goods_option_value_data = array();

				$goods_option_value_query = M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_option_id' => $goods_option['id']) )->order('displayorder desc,id desc')->select();

	            foreach ($goods_option_value_query as $goods_option_value) {


	                $goods_option_value_data[] = array(
	                    'goods_option_value_id' => $goods_option_value['id'],
	                    'option_value_id'         => $goods_option_value['id'],
	                    'name'					  =>$goods_option_value['title'],
	                    'image'					  =>isset($goods_option_value['thumb']) ? tomedia($goods_option_value['thumb']) : '',
	                );

	                if(!empty($goods_option_value['thumb']))
	                {
	                	$option_item_image[$goods_option_value['id']] = tomedia($goods_option_value['thumb']);
	                }
	            }

	            $goods_option_name[] = $goods_option['title'];
	            $goods_option_data[] = array(
	                'goods_option_id'      => $goods_option['id'],
	                'option_id'            => $goods_option['id'],
	                'name'                 => $goods_option['title'],
	                'option_value'         => $goods_option_value_data,

	            );
	        }



	        $result['list'] = $goods_option_data;
	        $result['name'] = $goods_option_name;

			if($member_id >0)
			{
				$member_info = M('eaterplanet_ecommerce_member')->field('level_id')->where( array('member_id' => $member_id ) )->find();

				if( $member_info['level_id'] > 0)
				{

					$member_level_info = M('eaterplanet_ecommerce_member_level')->where( array('id' => $member_info['level_id'] ) )->find();
				}
			}

			$mult_item_list = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $goods_id) )->select();

	        $sku_mu_list = array();
	        foreach($mult_item_list as $val)
	        {
	        	//goodsname,productprice
				$val['levelprice'] = $val['marketprice'];


				if($member_id >0 && $goods_common['is_mb_level_buy'] == 1)
				{
					if( $member_info['level_id'] > 0)
					{
						$val['levelprice'] = round( ($val['marketprice'] *  $member_level_info['discount']) /100 ,2);
						//$val['pinprice'] = round( ($val['pinprice'] *  $member_level_info['discount']) /100 ,2);
					}
				}

	        	$tmp_arr = array();
	        	$tmp_arr['spec'] = 	$val['title'];
	        	$tmp_arr['canBuyNum'] = $val['stock'];
	        	$tmp_arr['spuName'] = $goods_info['goodsname'];
	        	$tmp_arr['actPrice'] = explode('.', $val['marketprice']);
	        	$tmp_arr['marketPrice'] = explode('.', $val['productprice']);
				$tmp_arr['pinprice'] = explode('.', $val['pinprice']);
	        	$tmp_arr['card_price'] = explode('.', $val['card_price']);
				$tmp_arr['levelprice'] = explode('.', $val['levelprice']);

	        	$tmp_arr['option_item_ids'] = $val['option_item_ids'];
	        	$tmp_arr['stock'] = $val['stock'];
	        	$ids_option = explode('_', $val['option_item_ids']);
	        	$img = '';
	        	foreach($ids_option as $vv)
	        	{
	        		if(isset($option_item_image[$vv]))
	        		{
	        			$img = $option_item_image[$vv];
	        			break;
	        		}
	        	}
				if( empty($img) )
				{
					$img = $sku_goods_image;
				}
	        	$tmp_arr['skuImage'] = $img;
	        	$sku_mu_list[$val['option_item_ids']] = $tmp_arr;
	        }
	        $result['sku_mu_list'] = $sku_mu_list;

	        //array('spec' => 'xl','canBuyNum' => 100,'spuName' => 1, 'actPrice' => array(1,2), 'marketPrice' => array(2,3),'skuImage' => tomedia($good_image['image'])),
    	}

        return $result;


	}

	/**
	 * 获取商品规格信息及购物车数量
	 * @param $goods_id
	 * @param int $member_id
	 * @return array
	 */
	public function get_goods_options_carquantity($goods_id,$member_id =0,$head_id ,$token)
	{

		$result = array();
		$goods_option_name = array();
		$goods_option_data = array();

		$goods_info = M('eaterplanet_ecommerce_goods')->field('goodsname,productprice')->where( array('id' => $goods_id ) )->find();

		$goods_common = M('eaterplanet_ecommerce_good_common')->field('is_mb_level_buy')->where( array('goods_id' => $goods_id ) )->find();


		$goods_option_query = M('eaterplanet_ecommerce_goods_option')->where( array('goods_id' => $goods_id) )->select();

		$sku_goods_image = '';
		$good_image = $this->get_goods_images($goods_id);
		if( !empty($good_image) )
		{
			$sku_goods_image = tomedia($good_image['image']);
		}


		if( !empty($goods_option_query) )
		{
			$option_item_image = array();
			foreach ($goods_option_query as $goods_option) {
				$goods_option_value_data = array();

				$goods_option_value_query = M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_option_id' => $goods_option['id']) )->order('displayorder desc,id desc')->select();

				foreach ($goods_option_value_query as $goods_option_value) {
					$goods_option_value_data[] = array(
							'goods_option_value_id' => $goods_option_value['id'],
							'option_value_id'         => $goods_option_value['id'],
							'name'					  =>$goods_option_value['title'],
							'image'					  =>isset($goods_option_value['thumb']) ? tomedia($goods_option_value['thumb']) : '',
					);

					if(!empty($goods_option_value['thumb']))
					{
						$option_item_image[$goods_option_value['id']] = tomedia($goods_option_value['thumb']);
					}
				}

				$goods_option_name[] = $goods_option['title'];
				$goods_option_data[] = array(
						'goods_option_id'      => $goods_option['id'],
						'option_id'            => $goods_option['id'],
						'name'                 => $goods_option['title'],
						'option_value'         => $goods_option_value_data,

				);
			}



			$result['list'] = $goods_option_data;
			$result['name'] = $goods_option_name;

			if($member_id >0)
			{
				$member_info = M('eaterplanet_ecommerce_member')->field('level_id')->where( array('member_id' => $member_id ) )->find();

				if( $member_info['level_id'] > 0)
				{

					$member_level_info = M('eaterplanet_ecommerce_member_level')->where( array('id' => $member_info['level_id'] ) )->find();
				}
			}

			$mult_item_list = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $goods_id) )->select();

            $is_login_showprice = D('Home/Front')->get_config_by_name('is_login_showprice');

			$sku_mu_list = array();
			foreach($mult_item_list as $val)
			{
				//goodsname,productprice
				$val['levelprice'] = $val['marketprice'];


				if($member_id >0 && $goods_common['is_mb_level_buy'] == 1)
				{
					if( $member_info['level_id'] > 0)
					{
						$val['levelprice'] = round( ($val['marketprice'] *  $member_level_info['discount']) /100 ,2);
						//$val['pinprice'] = round( ($val['pinprice'] *  $member_level_info['discount']) /100 ,2);
					}
				}

				$tmp_arr = array();
				$tmp_arr['spec'] = 	$val['title'];
				$tmp_arr['canBuyNum'] = $val['stock'];
				$tmp_arr['spuName'] = $goods_info['goodsname'];
				$tmp_arr['actPrice'] = explode('.', $val['marketprice']);
				$tmp_arr['marketPrice'] = explode('.', $val['productprice']);
				$tmp_arr['pinprice'] = explode('.', $val['pinprice']);
				$tmp_arr['card_price'] = explode('.', $val['card_price']);
				$tmp_arr['levelprice'] = explode('.', $val['levelprice']);

                //1、开启未登录不显示价格，

                if( !empty($is_login_showprice) && $is_login_showprice == 1 )
                {
                    $member_info = M('eaterplanet_ecommerce_member')->where(array('member_id' => $member_id))->find();

                    if( empty($member_id) || $member_id <= 0  || $member_info['is_apply_state'] == 0 )
                    {

                        $tmp_arr['actPrice'] = '--';

                        $tmp_arr['pinprice'] = '--';
                        $tmp_arr['card_price'] = '--';
                        $tmp_arr['levelprice'] = '--';

                    }

                }


				$tmp_arr['option_item_ids'] = $val['option_item_ids'];
				$tmp_arr['stock'] = $val['stock'];
				$ids_option = explode('_', $val['option_item_ids']);

				$sku_carquantity = D('Home/Car')->get_wecart_goods($goods_id,$val['option_item_ids'],$head_id ,$token);
				$tmp_arr['car_quantity'] = !empty($sku_carquantity) ? $sku_carquantity : 0;

				$img = '';
				foreach($ids_option as $vv)
				{
					if(isset($option_item_image[$vv]))
					{
						$img = $option_item_image[$vv];
						break;
					}
				}
				if( empty($img) )
				{
					$img = $sku_goods_image;
				}
				$tmp_arr['skuImage'] = $img;
				$sku_mu_list[$val['option_item_ids']] = $tmp_arr;
			}
			$result['sku_mu_list'] = $sku_mu_list;

			//array('spec' => 'xl','canBuyNum' => 100,'spuName' => 1, 'actPrice' => array(1,2), 'marketPrice' => array(2,3),'skuImage' => tomedia($good_image['image'])),
		}

		return $result;


	}

	/**
		判断规格是否失效
	**/
	public function get_goods_option_can_buy( $goods_id, $sku_str )
	{
		if( empty($sku_str) )
		{
			return 1;
		}else{

			$goods_option_mult_value = M('eaterplanet_ecommerce_goods_option_item_value')->where( array('option_item_ids' =>$sku_str,'goods_id' => $goods_id ) )->find();

			if( empty($goods_option_mult_value) )
			{
				return 0;
			}else{
				return 1;
			}
		}
	}


	public function get_goods_time_can_buy($goods_id)
	{
		$goods_info = M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id ) )->find();

		if( $goods_info['total'] <= 0 || $goods_info['grounding'] != 1)
		{
			return 0;
		}

		$goods_info = M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $goods_id) )->find();

		$now_time = time();

		if( $now_time<$goods_info['begin_time']  || $now_time > $goods_info['end_time'])
		{
			return 0;
		}else{
			return 1;
		}


	}


	/**
		获取商品数量
	**/
	public function get_goods_count($where = '',$uniacid = 0)
	{

		$total = M('eaterplanet_ecommerce_goods')->where("1 ".$where)->count();

		return $total;
	}

	/**
		给商品扣除库存
	**/
	/**
	 扣除/增加商品多规格库存
	 1扣除， 2 增加
	 **/
	public function del_goods_mult_option_quantity($order_id,$option,$goods_id,$quantity,$type='1')
	{

		$tp_goods = M('eaterplanet_ecommerce_goods')->field('total as quantity')->where( array('id' => $goods_id) )->find();

		$order_goods = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id, 'goods_id' => $goods_id ) )->find();


		$option_list = M('eaterplanet_ecommerce_order_option')->where( array('order_id' => $order_id,'order_goods_id' => $goods_id) )->find();

	    if($type == 1)
	    {

			$quantity_order_data = array();
			$quantity_order_data['order_id'] = $order_id;
			$quantity_order_data['goods_id'] = $goods_id;
			$quantity_order_data['rela_goodsoption_value_id'] = $option;
			$quantity_order_data['quantity'] = $quantity;
			$quantity_order_data['type'] = 0;
			$quantity_order_data['last_quantity'] = $tp_goods['quantity'];
			$quantity_order_data['addtime'] = time();
			$quantity_order_data['adddate'] = date('Y-m-d H:i:s');

			M('eaterplanet_ecommerce_order_quantity_order')->add($quantity_order_data);

	        //扣除库存
			$up_total_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_goods SET total = (total - " . (int)$quantity . ") where id={$goods_id} ";

			M()->execute($up_total_sql);

	        //销量增加

		    $up_seller_count_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_goods SET seller_count = (seller_count + " . (int)$quantity . ") WHERE id = {$goods_id}";

			M()->execute($up_seller_count_sql);

			//释放出reids占位，还有取消订单也要释放出redis占位---begin
			$order_info = M('eaterplanet_ecommerce_order')->field('member_id')->where( array('order_id' => $order_id) )->find();
			$redis_has_add_list = array();

			$redis_has_add_list[]  = array('member_id' => $order_info['member_id'], 'goods_id' => $goods_id, 'sku_str' => $option );


			//D('Seller/Redisorder')->sysnc_goods_total($goods_id);
			//D('Seller/Redisorder')->cancle_goods_buy_user($redis_has_add_list);

			//--------end

	    } else if($type == 2){

			$quantity_order_data = array();
			$quantity_order_data['order_id'] = $order_id;
			$quantity_order_data['goods_id'] = $goods_id;
			$quantity_order_data['rela_goodsoption_value_id'] = $option;
			$quantity_order_data['quantity'] = $quantity;
			$quantity_order_data['type'] = 1;
			$quantity_order_data['last_quantity'] = $tp_goods['quantity'];
			$quantity_order_data['addtime'] = time();
			$quantity_order_data['adddate'] = date('Y-m-d H:i:s');

			M('eaterplanet_ecommerce_order_quantity_order')->add($quantity_order_data);


	        //增加库存
			$up_total_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_goods SET total = (total + " . (int)$quantity . ") where id={$goods_id} ";

			M()->execute($up_total_sql);


	        //销量减少
			$up_seller_count_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_goods SET seller_count = (seller_count - " . (int)$quantity . ") WHERE id = {$goods_id}";

			M()->execute($up_seller_count_sql);

			D('Seller/Redisorder')->bu_goods_quantity($goods_id,$quantity);
	    }

	    if(!empty($option))
	    {
	        if($type == 1)
	        {
				$up_sku_total_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_goods_option_item_value SET stock = (stock - " . (int)$quantity . ") where goods_id={$goods_id} and option_item_ids='{$option}' ";
				M()->execute($up_sku_total_sql);
	        } else if($type ==2){
				$up_sku_total_sql = "update ".C('DB_PREFIX')."eaterplanet_ecommerce_goods_option_item_value SET stock = (stock + " . (int)$quantity . ") where goods_id={$goods_id} and option_item_ids='{$option}' ";
				M()->execute($up_sku_total_sql);

				D('Seller/Redisorder')->bu_goods_sku_quantity($goods_id,$quantity, $option);
	        }
	    }

		//D('Seller/Redisorder')->sysnc_goods_total($goods_id);
	}

	/**
		获取比较详细混合的商品信息
		可能会包含到分佣的情况
	**/
	public function get_goods_mixinfo($goods_id)
	{

		$need_data = array();

		$goods_info = M('eaterplanet_ecommerce_goods')->field('credit as points,type,codes as model')->where( array('id' => $goods_id) )->find();

		$commiss_info = M('eaterplanet_ecommerce_good_commiss')->where( array('goods_id' => $goods_id) )->find();

		if( !empty($commiss_info) )
		{
			//涉及到客户分销等级，先放着
		}else{
			$goods_info['nocommission'] = 0;
			$goods_info['hascommission'] = 0;
		}



		/**M('goods')->field(
		'points,commiss_fen_one_disc,
		commiss_fen_two_disc,commiss_fen_three_disc,commiss_three_dan_disc,commiss_two_dan_disc,
		commiss_one_dan_disc,store_id,type,model,image'
		)->where( array('goods_id' => $goods_id) )->find();
		**/

	}

	/**
	 * 生成核销订单二维码
	 * @param $hx_qrcode 订单核销码
	 * @return string
	 */
	public function _get_commmon_hxqrcode($hx_qrcode)
	{
		$level = 3;
		$size = 4;
		Vendor('phpqrcode.phpqrcode');
		$errorCorrectionLevel =intval($level) ;//容错级别
		$matrixPointSize = intval($size);//生成图片大小

		//图片地址
		$image_dir = ROOT_PATH.'Uploads/image/';
		$image_dir .= 'goods'.date('Y-m-d').'/';
		RecursiveMkdir($image_dir);
		$file_name = md5('qrcode_'.$hx_qrcode.time()).'.png';
		//生成二维码图片
		$object = new \QRcode();
		$object->png($hx_qrcode, $image_dir.$file_name, $errorCorrectionLevel, $matrixPointSize, 2);

		$attachment_type_arr =  M('eaterplanet_ecommerce_config')->where( array('name' => 'attachment_type') )->find();

		if( $attachment_type_arr['value'] == 1 )
		{
			save_image_to_qiniu($image_dir.$file_name,'Uploads/image/'.'goods'.date('Y-m-d').'/'.$file_name);
		}else if( $attachment_type_arr['value'] == 2 ){
			save_image_to_alioss($image_dir.$file_name,'Uploads/image/'.'goods'.date('Y-m-d').'/'.$file_name);
		}else if( $attachment_type_arr['value'] == 3 ){
			$res =	save_image_to_txyun($image_dir.$file_name,'Uploads/image/'.'goods'.date('Y-m-d').'/'.$file_name);
		}
		return 'goods'.date('Y-m-d').'/'.$file_name;
	}

	/**
	 * @desc 生成邀新有礼二维码
	 * @param $invite_url
	 * @param string $back_color
	 * @param string $line_color
	 * @return string
	 */
	public function _get_invite_qrcode($invite_url, $back_color="#ffffff", $line_color = "#000000"){
		Vendor('phpqrcode.phpqrcode');
		$object = new \QRcode();
		$level = 3;
		$size = 10;
		$errorCorrectionLevel =intval($level) ;//容错级别
		$matrixPointSize = intval($size);//生成图片大小

		$time = date('Y-m-d');
		//图片地址
		$image_dir = ROOT_PATH.'Uploads/image/';
		$image_dir .= 'invite/'.$time.'/';
		RecursiveMkdir($image_dir);
		//图片名称
		$file_name = md5('invite_qrcode_'.time()).'.png';
		$back_array = $this->hex2rgb_array($back_color);
		$line_array = $this->hex2rgb_array($line_color);
		//二维码生成
		$object->pngcolor($invite_url, $image_dir.$file_name, $errorCorrectionLevel, $matrixPointSize, 2 , false, $back_array, $line_array);

		$attachment_type_arr =  M('eaterplanet_ecommerce_config')->where( array('name' => 'attachment_type') )->find();

		if( $attachment_type_arr['value'] == 1 )
		{
			save_image_to_qiniu($image_dir.$file_name,'Uploads/image/'.'invite/'.$time.'/'.$file_name);
		}else if( $attachment_type_arr['value'] == 2 ){
			save_image_to_alioss($image_dir.$file_name,'Uploads/image/'.'invite/'.$time.'/'.$file_name);
		}else if( $attachment_type_arr['value'] == 3 ){
			$res =	save_image_to_txyun($image_dir.$file_name,'Uploads/image/'.'invite/'.$time.'/'.$file_name);
		}
		return 'invite/'.$time.'/'.$file_name;
	}

	/**
	 * 十六进制转RGB
	 * @param string $color 16进制颜色值
	 * @return array
	 */
	function hex2rgb_array($color) {
		$hexColor = str_replace('#', '', $color);
		$lens = strlen($hexColor);
		if ($lens != 3 && $lens != 6) {
			return false;
		}
		$newcolor = '';
		if ($lens == 3) {
			for ($i = 0; $i < $lens; $i++) {
				$newcolor .= $hexColor[$i] . $hexColor[$i];
			}
		} else {
			$newcolor = $hexColor;
		}
		$hex = str_split($newcolor, 2);
		$rgb = [];
		foreach ($hex as $key => $vls) {
			$rgb[] = hexdec($vls);
		}
		return $rgb;
	}

	public function _get_invite_wxqrcode($invite_url, $share_id = 0, $back_color="#ffffff", $line_color = "#000000")
	{
		$suffix = 'png';
		$weixin_config = array();
		$weixin_config['appid'] = D('Home/Front')->get_config_by_name('wepro_appid');
		$weixin_config['appscert'] = D('Home/Front')->get_config_by_name('wepro_appsecret');

		$qrcode_rgb = $line_color;

		$qrcode_arr = array();

		if( !empty($qrcode_rgb) )
		{
			$qrcode_arr = $this->hex2rgb($qrcode_rgb);
		}

		//qrcode
		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'] ,$weixin_config['appscert'] );
		$weqrcode = $jssdk->getAllWeQrcode($invite_url, $share_id,$suffix=="png",$qrcode_arr);
		//line_color

		$time = date('Y-m-d');
		//保存图片
		$image_dir = ROOT_PATH.'Uploads/image/';
		$image_dir .= 'invite/'.$time.'/';

		RecursiveMkdir($image_dir);

		$file_name = md5('invite_wxqrcode_'.$share_id.time()).'.'.$suffix;
		//qrcode
		file_put_contents($image_dir.$file_name, $weqrcode);

		$attachment_type_arr =  M('eaterplanet_ecommerce_config')->where( array('name' => 'attachment_type') )->find();

		if( $attachment_type_arr['value'] == 1 )
		{
			save_image_to_qiniu($image_dir.$file_name,'Uploads/image/invite/'.$time.'/'.$file_name);
		}else if( $attachment_type_arr['value'] == 2 ){
			save_image_to_alioss($image_dir.$file_name,'Uploads/image/invite/'.$time.'/'.$file_name);
		}else if( $attachment_type_arr['value'] == 3 ){
			$res =	save_image_to_txyun($image_dir.$file_name,'Uploads/image/invite/'.$time.'/'.$file_name);
		}

		return 'invite/'.$time.'/'.$file_name;
	}
}
