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
namespace Home\Widget;
use Think\Controller;
/**
 * 微信分享
 */
class ShareWidget extends Controller{
	public $data;
	public $signPackage;

	protected function _initialize(){

		$data   = M('Config')->select();

        $config = array();
        if($data && is_array($data)){
            foreach ($data as $value) {
                $config[$value['name']] =$value['value'];
            }
        }
        $this->data =  $config;

        $appid_info 	=  M('config')->where( array('name' => 'APPID') )->find();
        $appsecret_info =  M('config')->where( array('name' => 'APPSECRET') )->find();
        $mchid_info =  M('config')->where( array('name' => 'MCHID') )->find();

        $weixin_config = array();
        $weixin_config['appid'] = $appid_info['value'];
        $weixin_config['appscert'] = $appsecret_info['value'];
        $weixin_config['mchid'] = $mchid_info['value'];

		$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);

		$signPackage = $jssdk->GetSignPackage();
		$this->signPackage = $signPackage;
     }

	 function common_special_share($special_id)
	 {
		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		$member_id = is_login();
		$hash_member_id = $hashids->encode($member_id);

		$special_info = M('mb_special')->where( array('special_id' => $special_id) )->find();

		$this->share_logo = $this->data['SITE_URL'].'Uploads/image/'.$this->data['SITE_ICON'];

		if(!empty($special_info['share_image']))
		{
			$this->share_logo = $this->data['SITE_URL'].'Uploads/image/'.$special_info['share_image'];
		}
		$this->indexsharetitle = $special_info['special_desc'];

		if(!empty($special_info['share_title']))
		{
			$this->indexsharetitle = $special_info['share_title'];
		}

		$this->url = $this->data['SITE_URL']."/index.php?s=/Special/index/special_id/{$special_id}/rmid/{$hash_member_id}";

		$this->indexsharesummary = $this->data['SITE_DESCRIPTION'];
		if(!empty($special_info['share_descript']))
		{
			$this->indexsharesummary = $special_info['share_descript'];
		}

		$this->assign('signPackage',$this->signPackage);

		$this->display('Widget:share_common_weixin');
	 }

	function common_bargain_share($id)
	 {
		 $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		$member_id = is_login();
		$hash_member_id = $hashids->encode($member_id);

		$bargain_order = M('bargain_order')->where( array('id' => $id) )->find();

		$goods_id = $bargain_order['goods_id'];

		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
       //"我正在参加拼多多官方砍价，砍到0元就可以免费拿啦，帮我砍一下吧"

	   $desc = "我正在参加".C('SITE_NAME')."砍价，砍到0元就可以免费拿啦，帮我砍一下吧";
	    $this->share_logo = $this->data['SITE_URL'].resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

		 if(!empty($goods_info['fan_image'])){
			$this->share_logo = $this->data['SITE_URL'].resize($goods_info['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}else {
			$this->share_logo = $this->data['SITE_URL'].resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}
		if(!empty($goods_description['water_image']))
		{
			$this->share_logo = $this->data['SITE_URL'].'/Uploads/image/'.$goods_description['water_image'];
		}
		//Subject/assist_bargain_coupon_detail/id/10.html
		$this->indexsharetitle = '['.C('SITE_NAME')."]我在砍价免费拿".$goods_info['name']."，帮我砍价，0元拿回家！";
		$this->url = $this->data['SITE_URL']."/index.php?s=/Subject/assist_bargain_coupon_detail/id/{$id}/rmid/{$hash_member_id}";
		 $this->indexsharesummary =  $desc;

		 //goods_id
		 $this->assign('signPackage',$this->signPackage);

         $this->display('Widget:share_common_weixin');
	 }
	 function common_bargain_index_share()
	{
		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		$member_id = is_login();
		$hash_member_id = $hashids->encode($member_id);

		$this->share_logo = $this->data['SITE_URL'].'Uploads/image/'.$this->data['SITE_ICON'];
		$this->indexsharetitle = '['.C('SITE_NAME')."]快来玩砍价，马上被抢完啦";
		$this->url = $this->data['SITE_URL']."/index.php?s=/Subject/assist_bargain_coupon/rmid/{$hash_member_id}";




		$this->indexsharesummary = "呼朋唤友来砍价，心仪好货免费拿回家";

		$this->assign('signPackage',$this->signPackage);

		$this->display('Widget:share_common_weixin');
	}
     function common_goods_share($goods_id)
     {

		 $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		 $member_id = is_login();
		 $hash_member_id = $hashids->encode($member_id);

		 $share_model = D('Seller/Fissionsharing');
		 $link_info = $share_model->get_sharing_type_info($member_id,'goods',$goods_id);
		 $share_param = "goods_{$goods_id}_{$member_id}_{$link_info[share_one_id]}_{$link_info[share_two_id]}_".time();
		 $hash_share_param = base64_encode($share_param);


         $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
         $goods_description = M('goods_description')->where( array('goods_id' => $goods_id) )->find();

         $this->share_logo = $this->data['SITE_URL'].resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

		 if(!empty($goods_info['fan_image'])){
			$this->share_logo = $this->data['SITE_URL'].resize($goods_info['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}else {
			$this->share_logo = $this->data['SITE_URL'].resize($goods_info['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}

		if(!empty($goods_description['water_image']))
		{
			$this->share_logo = $this->data['SITE_URL'].'/Uploads/image/'.$goods_description['water_image'];
		}

         $this->indexsharetitle = $goods_info['name'];

		 if(!empty($goods_description['share_title']))
		 {
			 $this->indexsharetitle = $goods_description['share_title'];
		 }

		 $this->url = $this->data['SITE_URL']."/index.php?s=/Goods/gshow/id/{$goods_id}/rmid/{$hash_member_id}/share_rmid/".$hash_share_param;;

		 $str = str_replace(array("/r/n", "/r", "/n"), "", $goods_description['summary']);

		 $qian=array("\t","\n","\r");
		 $hou=array("","","");
		 $goods_description['summary'] = str_replace($qian,$hou,$goods_description['summary']);
         $this->indexsharesummary =  $goods_description['summary'];
		 if(!empty($goods_description['share_descript']))
		 {
			 $this->indexsharesummary = $goods_description['share_descript'];
		 }

         $this->assign('signPackage',$this->signPackage);

         $this->display('Widget:share_common_weixin');
     }

	 function common_quan_share($voucher_id)
     {


		 $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		 $member_id = is_login();
		 $hash_member_id = $hashids->encode($member_id);


		 $share_model = D('Seller/Fissionsharing');
		 $link_info = $share_model->get_sharing_type_info($member_id,'page',0);
		 $share_param = "page_0_{$member_id}_{$link_info[share_one_id]}_{$link_info[share_two_id]}_".time();
		 $hash_share_param = base64_encode($share_param);


		 $voucher_info = M('voucher')->where( array('id' => $voucher_id) )->find();


         $this->share_logo = $this->data['SITE_URL'].resize($voucher_info['share_logo'], C('common_image_thumb_width'), C('common_image_thumb_height'));


         $this->indexsharetitle = $voucher_info['share_title'];


		 $this->url = $this->data['SITE_URL']."/index.php?s=/Bonus/index/id/{$voucher_id}/rmid/{$hash_member_id}/share_rmid/".$hash_share_param;;

		 $str = str_replace(array("/r/n", "/r", "/n"), "", $voucher_info['share_desc']);

		 $qian=array("\t","\n","\r");
		 $hou=array("","","");
		 $voucher_info['share_desc'] = str_replace($qian,$hou,$voucher_info['share_desc']);

		$this->indexsharesummary =  $voucher_info['share_desc'];

         $this->assign('signPackage',$this->signPackage);

         $this->display('Widget:share_common_weixin_quan');
     }

	 function common_pin_share()
	 {
		 $hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		$member_id = is_login();
		$hash_member_id = $hashids->encode($member_id);

		$share_model = D('Seller/Fissionsharing');
		$link_info = $share_model->get_sharing_type_info($member_id,'page',0);

		// pingoods/index.html

		$share_param = "page_0_{$member_id}_{$link_info[share_one_id]}_{$link_info[share_two_id]}_".time();

		$hash_share_param = base64_encode($share_param);

		$this->share_logo = $this->data['SITE_URL'].'Uploads/image/'.$this->data['SITE_ICON'];
		$this->indexsharetitle = $this->data['SITE_TITLE'];
		$this->url = $this->data['SITE_URL']."index.php?s=/pingoods/index/rmid/{$hash_member_id}/share_rmid/".$hash_share_param;


		$this->indexsharesummary = $this->data['SITE_DESCRIPTION'];

		$this->assign('signPackage',$this->signPackage);

		$this->display('Widget:share_common_weixin');
	 }

	function common_share(){


		/**
			dejavutech_fissionsharing_link
			id,type(page,goods),goods_id,member_id,share_one_id,share_two_id,share_three_id,modify_time
		**/


		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
		$member_id = is_login();
		$hash_member_id = $hashids->encode($member_id);

		$share_model = D('Seller/Fissionsharing');
		$link_info = $share_model->get_sharing_type_info($member_id,'page',0);

		// $share_param = "goods_{$goods_id}_{$member_id}_{$link_info[share_one_id]}_{$link_info[share_two_id]}";

		$share_param = "page_0_{$member_id}_{$link_info[share_one_id]}_{$link_info[share_two_id]}_".time();

		$hash_share_param = base64_encode($share_param);

		$this->share_logo = $this->data['SITE_URL'].'Uploads/image/'.$this->data['SITE_ICON'];
		$this->indexsharetitle = $this->data['SITE_TITLE'];
		$this->url = $this->data['SITE_URL']."index.php?s=/index/index/rmid/{$hash_member_id}/share_rmid/".$hash_share_param;


		$this->indexsharesummary = $this->data['SITE_DESCRIPTION'];

		$this->assign('signPackage',$this->signPackage);

		$this->display('Widget:share_common_weixin');
	}

}
