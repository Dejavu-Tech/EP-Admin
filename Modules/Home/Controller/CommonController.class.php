<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.cloud/
 * @copyright Copyright (c) 2019-2024 Dejavu Tech.
 * @license   https://github.com/Dejavu-Tech/EP-Admin/blob/main/LICENSE
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller{

     /* 初始化,权限控制,菜单显示 */
     protected function _initialize(){

		/* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
        if(!C('WEB_SITE_CLOSE')){
           die('站点已经关闭，请稍后访问~');
        }

		//模拟进入电脑端
		if(isset($_GET['front']))
		{
			session('user_auth', null);
			session('user_auth_sign', null);
			cookie('auth_rp_string','', -1);
		}

        if(!$this->is_weixin() && !isset($_GET['ok']) && !is_login())
		{
			$this->site_in = 'computer';
		}else {
			$this->site_in = 'weixin';

			$rmid = I('get.rmid',0);
			if( !empty($rmid) )
			{
				cookie('rmid', $rmid);
			}

			if(!is_login() && !in_array(CONTROLLER_NAME,array('Public','Platform','Utility','Payment','Image','Apiindex','Apiquan','Apicart','Apigoods','Apiuser','Api','Apicheckout') ))
			{

			}
		}

		//share_rmid/NjAwXzBfMA==


     }


	 public function is_weixin(){
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
				return true;
		}
		return false;
	}

	/* 空操作，用于输出404页面 */
	public function _empty(){
		// $this->display('Public:404');die();
		//die('空操作');


		 header('Location: ep.php');
 die();

		var_dump($_SERVER['REQUEST_URI']);die();
		$data = array();
		$data['domain'] = $_SERVER['REQUEST_URI'];
		$data['add_time'] = time();
		M('bad_domain')->add($data);
	    $this->redirect('Index/index');
	}



}
?>
