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
namespace Seller\Controller;

class PublicController extends \Think\Controller {


    public function login($username = null, $password = null, $verify = null){

		$config =   S('DB_CONFIG_DATA');
	    if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
	    }
        C($config); //添加配置

		$data = D('Seller/Config')->get_all_config();

		$this->technical_support = $data['technical_support'];
		$this->record_number = $data['record_number'];

        if(IS_POST){

		if(empty($username)){
			$this->error('用户名不能为空！');
		}elseif(empty($password)){
			$this->error('密码不能为空！');
		}

		//孕育小主
		$seller=M('Seller')->where( array('s_uname' => $username) )->find();




		//用户存在且可用|| true
		if($seller&&$seller['s_status']==1){
		   //验证密码
			if(think_ucenter_encrypt($password,C('SELLER_PWD_KEY'))==$seller['s_passwd'] ){

		        $auth = array(
		            'uid'             => $seller['s_id'],
		            'username'        => $seller['s_uname'],
		        	'is_super'	  	  => $seller['s_is_super'],
		        	'role_id'	  	  => $seller['s_role_id'],
		            'last_login_time' => $seller['s_last_login_time'],
				 );

			    session('seller_auth', $auth);
	    		session('seller_auth_sign', data_auth_sign($auth));
				$_SESSION[C('Seller_AUTH_KEY')] = '';

				if (C('USER_AUTH_ON')) {
		            $_SESSION[C('USER_AUTH_KEY')] = $seller['s_id'];
		            if ($seller['s_is_super']) {
		                // 超级管理员无需认证
		                $_SESSION[C('Seller_AUTH_KEY')] = true;
		            }

		            // 缓存访问权限
		           // \Org\Util\Rbac::saveAccessList();
		        }

		        $data = array();
		        $data['s_id']	=	$seller['s_id'];
		        $data['s_last_login_time']	=	time();
		        $data['s_login_count']		=	array('exp','s_login_count+1');
				$data['s_last_login_ip']	=	get_client_ip();
		        M('Seller')->save($data);

				storage_user_action($seller['s_id'],$seller['s_uname'],C('SELLER_USER'),'登录了卖家后台');

				cookie('last_login_page',1);

				$this->success('登录成功！', U('Index/index'));
			}else{
				$this->error('密码错误！');
			}
		}else{
			$this->error('用户不存在或被禁用！');
		}

        } else {
			$xxximage_arr = M('eaterplanet_ecommerce_config')->where( array('name' => 'admin_login_image') )->find();
			$seller_backimage_arr = M('eaterplanet_ecommerce_config')->where( array('name' => 'seller_backimage') )->find();


			//seller_backimage admin_login_image

			$this->admin_xxximage = $xxximage_arr['value'];
			$this->seller_backimage = $seller_backimage_arr['value'];
            $this->display();
        }
    }


    public function logout(){
    	 if (C('USER_AUTH_ON')) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION[C('ADMIN_AUTH_KEY')]);
        }
        session('[destroy]');
		$last_login_page = cookie('last_login_page');
		if( empty($last_login_page) || $last_login_page == 1 )
		{
			$this->redirect('Public/login');
		}else{
			$this->redirect('Supply/login');
		}
       // $this->redirect('login');

    }

    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }

    public function clear(){
        clear_cache();
        $this->success('缓存清理完毕');
    }

}
