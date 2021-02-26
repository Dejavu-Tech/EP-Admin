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
namespace Admin\Controller;

class PublicController extends \Think\Controller {


    public function login($username = null, $password = null, $verify = null){

		$config =   S('DB_CONFIG_DATA');
	    if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
	    }
        C($config); //添加配置


        if(IS_POST){

		if(empty($username)){
			$this->error('用户名不能为空！');
		}elseif(empty($password)){
			$this->error('密码不能为空！');
		}
		$user=M('Admin')->getByAUname($username);

		//用户存在且可用
		if($user&&$user['a_status']==1){
			//验证密码
			if(think_ucenter_encrypt($password,C('PWD_KEY'))==$user['a_passwd']){

		        $auth = array(
		            'uid'             => $user['a_id'],
		            'username'        => $user['a_uname'],
		        	'is_super'	  	  => $user['a_is_super'],
		        	'role_id'	  	  => $user['a_role_id'],
		            'last_login_time' => $user['a_last_login_time'],
				 );

			    session('user_auth', $auth);
	    		session('user_auth_sign', data_auth_sign($auth));
				$_SESSION[C('ADMIN_AUTH_KEY')] = false;

				if (C('USER_AUTH_ON')) {
		            $_SESSION[C('USER_AUTH_KEY')] = $user['a_id'];
		            if ($user['a_is_super']) {
		                // 超级管理员无需认证
		                $_SESSION[C('ADMIN_AUTH_KEY')] = true;
		            }

		            // 缓存访问权限
		            \Org\Util\Rbac::saveAccessList();
		        }

		        $data = array();
		        $data['a_id']	=	$user['a_id'];
		        $data['a_last_login_time']	=	time();
		        $data['a_login_count']		=	array('exp','a_login_count+1');
				$data['a_last_login_ip']	=	get_client_ip();
		        M('Admin')->save($data);
                sellerLog('登录后台管理系统',1);

                storage_user_action($user['a_id'],$user['a_uname'],C('BACKEND_USER'),'登录了后台系统');

				$this->success('登录成功！', U('Index/index'));
			}else{
				$this->error('密码错误！');
			}
		}else{
			$this->error('用户不存在或被禁用！');
		}

        } else {
				$this->admin_xxximage = C('admin_xxximage');
           		$this->admin_backimage = C('admin_backimage');
                $this->display();

        }
    }


    public function logout(){
        sellerLog('退出了后台管理系统',0);

        if (C('USER_AUTH_ON')) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION[C('ADMIN_AUTH_KEY')]);
        }
        session('[destroy]');
        $this->redirect('login');

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
