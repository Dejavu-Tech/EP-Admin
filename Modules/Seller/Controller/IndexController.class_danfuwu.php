<?php
namespace Seller\Controller;
use Admin\Model\StatisticsModel;
class IndexController extends CommonController {
   	
	
	protected function _initialize(){
   	    parent::_initialize();
   	    $this->breadcrumb1='首页';
   	    $this->breadcrumb2='首页';
		$this->type = 'mall';
		$this->admin_domain = '';
		$this->host = base64_encode( strtolower(strval($_SERVER['HTTP_HOST'])));
   	}
	function duoduo_action($action, $version='V1.7') {
	    $host = base64_encode( strtolower(strval($_SERVER['HTTP_HOST'])));
		$type = $this->type;
	    $url = $this->admin_domain."/seller.php?s=/Upgrade/req_version/type/{$type}/version/{$version}/host/{$host}";
		
	    $r = sendhttp_get($url);
		
	    return json_decode($r, true);
	}
	
	function zuitu_upgrade($action, $version='V1.0') {
	    $result = $this->duoduo_action($action, $version);
	    return $result;
	}
	
	function duoduo_version($version) {
	    return $this->duoduo_action('version', $version);
	}
    public function index(){
         
		 $config_arr = M('config')->where( array('name' => 'VERSION') )->find();
        $version = $config_arr['value'];
        $version_meta = $this->duoduo_version($version);
        
		
        $isnew = true;
        $newsubversion = '';
        
        if(!empty($version_meta))
        {
            $isnew = false;
            $newsubversion = end($version_meta);
            $newsubversion = $newsubversion['name'];
        }
        $this->version = $version;
        $this->newsubversion = $newsubversion;
        
        $version_meta = array_reverse($version_meta);
        $this->version_meta = $version_meta;
		
        $seller_info = M('seller')->where( array('s_id' => SELLERUID) )->find();
		if(empty($seller_info['we_hexiao_qrcode']) || isset($_GET['reflash']))
		{
			
			//qrcode
			$jssdk = new \Lib\Weixin\Jssdk( C('weprogram_appid'), C('weprogram_appscret') );
			//$weqrcode = $jssdk->getAllWeQrcode('pages/store/index','5');
			
			$weqrcode = $jssdk->getAllWeQrcode('pages/order/hexiao_bind',SELLERUID.'_0' );
			
			//var_dump($weqrcode);die();
			
			//保存图片
			
			$image_dir = ROOT_PATH.'Uploads/image/goods';
			$image_dir .= '/'.date('Y-m-d').'/';
			
			$file_path = C('SITE_URL').'Uploads/image/goods/'.date('Y-m-d').'/';
			$kufile_path = $dir.'/'.date('Y-m-d').'/';
			
			RecursiveMkdir($image_dir);
			$file_name = md5('qrcode_'.$pick_order_info['pick_sn'].time()).'.png';
			//qrcode
			file_put_contents($image_dir.$file_name, $weqrcode);
			
			
			
			M('seller')->where( array('s_id' => SELLERUID) )->save( array('we_hexiao_qrcode' => $file_path.$file_name) );
			
			$seller_info['we_hexiao_qrcode'] = $file_path.$file_name;
		}
        $this->seller_info = $seller_info;
        //SELLERUID
    	$model=new StatisticsModel();
    	
    	$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
    	
    	$has_seller_id = $hashids->encode(SELLERUID);
    	
    	
    	$bind_order_notify_link = C('SITE_URL')."/index.php?s=/seller/bind_order_notify/seller_id/{$has_seller_id}";
		$this->bind_order_notify_link = $bind_order_notify_link;
		
		$unbind_order_notify_link = C('SITE_URL')."/index.php?s=/seller/unbind_order_notify/seller_id/{$has_seller_id}";
		$this->unbind_order_notify_link = $unbind_order_notify_link;
		
		
		$bind_pickup_order_link = C('SITE_URL')."/index.php?s=/seller/bind_pickup_order/seller_id/{$has_seller_id}";
		$this->bind_pickup_order_link = ($bind_pickup_order_link);
		
		
		$unbind_pickup_order_link = C('SITE_URL')."/index.php?s=/seller/unbind_pickup_order/seller_id/{$has_seller_id}";
		$this->unbind_pickup_order_link = ($unbind_pickup_order_link);
		
		
		$bloglist = M('blog')->where( array('type' => 'seller') )->order('blog_id desc')->limit(10)->select();
		$this->bloglist = $bloglist;
		
		$seller_view_link = C('SITE_URL')."/index.php?s=/seller/info/seller_id/".SELLERUID.".html";
		$this->seller_view_link = $seller_view_link;
		
       	$this->total_money=$model->get_total_sales( array('store_id' => SELLERUID) );
       	$this->today_money=$model->get_total_sales(array('date_added' => date('Y-m-d') ,'store_id' => SELLERUID));

       	$this->total_order=$model->get_total_order( array('store_id' => SELLERUID) );
       	$this->today_order=$model->get_total_order(array('date_added' => date('Y-m-d'), 'store_id' => SELLERUID));

       	$order_model=new \Admin\Model\OrderModel();

       	//store_id
       	$data=$order_model->show_order_page( array('store_id' => SELLERUID) );

       	$this->empty=$data['empty'];
       	$this->list=$data['list'];

       	$this->uc_empty='~~暂无数据';
       	$this->uc_list=$model->get_user_action();

        $this->display();
    }
	
	
}