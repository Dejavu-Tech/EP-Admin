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

class PublicController extends CommonController {

	public $weixin_config;
	protected function _initialize()
    {
    	parent::_initialize();
  		$appid_info 	=  M('config')->where( array('name' => 'APPID') )->find();
  		$appsecret_info =  M('config')->where( array('name' => 'APPSECRET') )->find();
  		$mchid_info =  M('config')->where( array('name' => 'MCHID') )->find();

  		$weixin_config = array();
  		$weixin_config['appid'] = $appid_info['value'];
  		$weixin_config['appscert'] = $appsecret_info['value'];
  		$weixin_config['mchid'] = $mchid_info['value'];

  		$this->weixin_config = $weixin_config;
    }

	public function test(){
		$not = D('Home/Weixinnotify');
		$not->sendBuyMsg();

	}
	/**
	 * 微信授权登陆
	 */
	public function wxlogin()
	{
		$jssdk = new \Lib\Weixin\Jssdk( $this->weixin_config['appid'], $this->weixin_config['appscert']);
		$jssdk->getsnsapi_userinfo();
	}

	/**
	 * 微信授权登陆回调
	 */
	public function wxauthcallback()
	{
		$jssdk = new \Lib\Weixin\Jssdk( $this->weixin_config['appid'], $this->weixin_config['appscert']);

		$code = $_GET['code'];
		$state = $_GET['state'];

		if(is_login() && false)
		{
			$redirct_url = cookie('redirct_url');
			if( empty($redirct_url) )
			{
				$redirct_url = U('Index/index');
			}

			if( empty($redirct_url) )
			{
				$redirct_url = U('Index/index');
			}
			$head_http = 'http://';

			$url = C('SITE_URL');
			if( strpos($url,'https:') !== false )
			{
				$head_http = 'https://';
			}

			$url = str_replace('http://','',$url);
			$url = str_replace('https://','',$url);
			$url_arr = explode('/',$url);

			$domain_site = $head_http.$url_arr[0];

			header('Location: '.$domain_site.urldecode($redirct_url));

		}

		$auth_accsss_info = $jssdk->getAutoAccessToken($code);
		$user_info = $jssdk->getSnsapiUserinfo($auth_accsss_info);


		$member_info = M('member')->where( array('openid' =>$user_info['openid']) )->find();
		if( empty($member_info) && !empty($user_info['unionid']) )
		{
			$member_info = M('member')->where( array('unionid' =>$user_info['unionid']) )->find();
		}

		if(!empty($member_info) )
		{
			 $data = array();
	         $data['member_id']	=	$member_info['member_id'];
	         $data['last_login_time']	=	time();
	         $data['login_count']		=	array('exp','login_count+1');
			 $data['last_login_ip']	=	get_client_ip();
			 $data['openid'] = trim($user_info['openid']);

	         M('Member')->save($data);

			$auth = array(
	            'uid'             => $member_info['member_id'],
	            'username'        => $member_info['uname'],
			 );
	   		session('user_auth', $auth);
    		session('user_auth_sign', data_auth_sign($auth));
			cookie('auth_rp_string',think_ucenter_encrypt($user_info['openid'],C('PWD_KEY')),86400*7);
			cookie('rmid','',-86400*7);
		} else {
			$data = array();
		   	$data['email']= time().mt_rand(1,9999).'@lf.com';
			$data['uname']=trim($user_info['nickname']);
			$data['name']=trim($user_info['nickname']);
			$data['avatar']=trim($user_info['headimgurl']);
			$data['openid'] = trim($user_info['openid']);
			$data['unionid'] = trim($user_info['unionid']);


			$data['pwd']  = think_ucenter_encrypt($user_info['nickname'],C('PWD_KEY'));
		    $data['status']=1;
	        $data['create_time']	=	time();
			$data['last_login_ip']	=	get_client_ip();

	       $re= M('Member')->add($data);
		   if($re){
	   			$auth = array(
		            'uid'             => $re,
		            'username'        => $data['uname'],
				 );

				$rmid = cookie('rmid');
				if( !empty($rmid) )
				{
					$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
					$re_member_id = $hashids->decode($rmid);
					$re_member_id = $re_member_id[0];
					$fenxiao_model = D('Home/Fenxiao');
					$fenxiao_model->relation_fenxiao($re_member_id,$re);
					cookie('rmid','',-86400*7);
				}
		   		session('user_auth', $auth);
	    		session('user_auth_sign', data_auth_sign($auth));
				cookie('auth_rp_string',think_ucenter_encrypt($user_info['openid'],C('PWD_KEY')),86400*7);

			   }
		   }

		 $redirct_url = cookie('redirct_url');
		 //
		 /**
		 if($user_info['openid'] == 'o0n_HwcGIfwf5b8PN8-gmNfsJBLA')
		 {
			 var_dump($redirct_url);die();
		 }
		 **/

	    if( empty($redirct_url) )
	    {
	   		$redirct_url = U('Index/index');
	    }
		$head_http = 'http://';

		$url = C('SITE_URL');
		if( strpos($url,'https:') !== false )
		{
			$head_http = 'https://';
		}

		$url = str_replace('http://','',$url);
		$url = str_replace('https://','',$url);
		$url_arr = explode('/',$url);

		$domain_site = $head_http.$url_arr[0];

		//var_dump($domain_site.urldecode($redirct_url));
		//die();
		header('Location: '.$domain_site.urldecode($redirct_url));
		/**
		$url = C('SITE_URL');
		$url = str_replace('http://','',$url);
		$url = str_replace('https://','',$url);
		$url_arr = explode('/',$url);
		$domain_site = 'http://'.$url_arr[0].'/';

		header('Location: '.$domain_site.urldecode($redirct_url));
		**/
	}

	/* 登录页面 */
	public function login(){

		if(IS_POST){

			if(!check_verify(I('code'))){
	            $this->error='验证码输入错误！';
				$this->display();
				die();
	        }

			if(empty($_POST['uname'])){
				$this->error="用户名 / email不能为空!!";
				$this->display();die();
			}elseif(empty($_POST['pwd'])){
				$this->error="密码不能为空!!";
				$this->display();die();
			}
			$user=M('Member')->getByUname($_POST['uname']);
			if(!$user){
				$user=M('Member')->getByEmail($_POST['uname']);
			}
			//用户存在且可用
			if($user&&$user['status']==1){
				//验证密码
				if(think_ucenter_encrypt($_POST['pwd'],C('PWD_KEY'))==$user['pwd']){

			        $auth = array(
			            'uid'             => $user['member_id'],
			            'username'        => $user['uname'],
			            'status'		  => $user['status']
					 );

				    session('user_auth', $auth);
		    		session('user_auth_sign', data_auth_sign($auth));

					if($user['address_id']!=0){
						session('shipping_address_id',$user['address_id']);
					}
					storage_user_action($user['member_id'],$user['uname'],C('FRONTEND_USER'),'登录了网站');

			        $data = array();
			        $data['member_id']	=	$user['member_id'];

			        $data['last_login_time']	=	time();
			        $data['login_count']		=	array('exp','login_count+1');
					$data['last_login_ip']	=	get_client_ip();
					$tip=new \Lib\Taobaoip();
					$ip_region=$tip->getLocation($data['last_login_ip']);

					$data['last_ip_region']=$ip_region['region'].'-'.$ip_region['city'];

			        M('Member')->save($data);

					$this->redirect('/order');

				}else{
					$this->error='密码错误！！';
					$this->display();die();
				}
			}else{
				$this->error="用户不存在或被禁用！！";
				$this->display();die();
			}

	        } else {

				$this->title='用户登录-';
				$this->meta_keywords=C('SITE_KEYWORDS');
				$this->meta_description=C('SITE_DESCRIPTION');

	            if(is_login()){
	                $this->redirect('/order');
	            }else{
	                $this->display();
	            }
        }
	}

	/* 退出登录 */
	public function logout(){


		session('[destroy]');


		session('user_auth', '');
		session('user_auth_sign', '');

		cookie('auth_rp_string','',-86400*7);
		die('ok');
     	//$this->redirect('/login');

	}

    public function verify(){
        $verify = new \Think\Verify();
		$verify->codeSet = '2345689';
		$verify->fontSize = 30;
		$verify->length   = 4;
		$verify->useCurve = false;
		$verify->useNoise = true;
        $verify->entry(1);
    }





}
