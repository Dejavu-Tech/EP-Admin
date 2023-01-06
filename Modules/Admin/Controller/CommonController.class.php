<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.io/
 * @copyright Copyright (c) 2019-2023 Dejavu Tech.
 * @license   https://e-p.io/license
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller{

     /* 初始化,权限控制,菜单显示 */
     protected function _initialize(){
        // 获取当前用户ID
        define('UID',is_login());
        if(!UID){// 还没登录 跳转到登录页面
            $this->redirect('Public/login');
        }
		/* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置

    	 //菜单分配
        $notLoginModules = explode(',', C('NOT_LOGIN_MODULES'));
        if (!in_array(CONTROLLER_NAME, $notLoginModules)) {
              // 权限过滤
      		  $this->filterAccess();
        }
     }

	/**
     * 权限过滤
     * @return
     */
    protected function filterAccess() {

        if (!C('USER_AUTH_ON')) {
            return ;
        }

        //Admin
        //var_dump( \Org\Util\Rbac::AccessDecision(C('GROUP_AUTH_NAME')) );die();

        if (\Org\Util\Rbac::AccessDecision(C('GROUP_AUTH_NAME'))) {
            return ;
        }

        if (!$_SESSION [C('USER_AUTH_KEY')]) {
            // 登录认证号不存在
            return $this->redirect(C('USER_AUTH_GATEWAY'));
        }

        if ('Index' === CONTROLLER_NAME && 'index' === ACTION_NAME) {
            // 首页无法进入，则登出帐号
            D('Admin', 'Service')->logout();
        }

        return $this->error('您没有权限执行该操作！');
    }

	/* 空操作，用于输出404页面 */
	public function _empty(){
		// $this->display('Public:404');die();
		die('空操作');
	}

	/**
	 *跳转控制
	 */
	public function osc_alert($status){

		if($status['status']=='back'){
			$this->error($status['message']);
			die;
		}elseif($status['status']=='success'){
			$this->success($status['message'],$status['jump']);
			die;
		}elseif($status['status']=='fail'){
			$this->error($status['message'],$status['jump']);
			die;
		}
	}

}
?>
