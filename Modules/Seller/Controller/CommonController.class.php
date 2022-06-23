<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://www.eaterplanet.com/
 * @copyright Copyright (c) 2019-2022 Dejavu.Tech.
 * @license   https://www.eaterplanet.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Seller\Controller;
use Think\Controller;
class CommonController extends Controller{

     /* 初始化,权限控制,菜单显示 */
     protected function _initialize(){
        // 获取当前用户ID
        define('SELLERUID',is_seller_login());


		//string(6) "Supply" string(5) "index"

		if( CONTROLLER_NAME == 'Supply' && (ACTION_NAME == 'login' || ACTION_NAME == 'login_do') )
		{

		}else{
			if(!SELLERUID){// 还没登录 跳转到登录页面
				if(is_agent_login())
				{
					define('ROLE','agenter');
				}else{
					//cookie('last_login_page', $rmid);

					$last_login_page = cookie('last_login_page');

						$this->redirect('Public/login');



				}
			}
		}





		/* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置

		$blog_seller_order_ids = M('blog_seller_order')->field('blog_id')->where( array('seller_id' =>SELLERUID) )->select();

		$blog_ids_arr = array();
		foreach($blog_seller_order_ids as $val)
		{
			array_push($blog_ids_arr, $val['blog_id']);
		}

		if(!empty($blog_ids_arr))
		{
			$blog_ids_str = '';
			$map = array();
			$map['status'] = 1;
			$map['type'] = 'seller';

			$map['seller_id'] = SELLERUID;
			$map['blog_id']= array('not in',$blog_ids_arr );

			$blog_not_count = M('blog')->where( $map )->count();

			$blog_not_list = M('blog')->field('blog_id,title')->where( $map )->limit(10)->select();
		} else{
			$blog_ids_str = '';
			$map = array();
			$map['type'] = 'seller';
			$map['status'] = 1;
			$blog_not_count = M('blog')->where( $map )->count();
			$blog_not_list = M('blog')->field('blog_id,title')->where( $map )->limit(10)->select();
		}


		$unsave_action_arr = array();
		$unsave_action_arr[] = 'Member/info/id';
		$can_save = true;

		foreach($unsave_action_arr as $val)
		{
			if( strpos($_SERVER['HTTP_REFERER'],$val) )
			{
				$can_save = false;
			}
		}
		if($can_save)
		{
			cookie('http_refer',$_SERVER['HTTP_REFERER']);
		}

		$this->blog_not_count = $blog_not_count;
		$this->blog_not_list = $blog_not_list;

		$this->system_hide_wepro = false;
		$this->system_hide_dan = true;


         // 权限过滤
       // $this->filterAccess();

	   $this->check_supply_access();
     }

	protected function check_supply_access()
	{

		if( defined('ROLE') && ROLE == 'agenter' )
		{

			$access_controller_action = array();
			$access_controller_action[] = 'index_index';
			$access_controller_action[] = 'index_analys';
			$access_controller_action[] = 'index_order_count';
			$access_controller_action[] = 'goods_index';
			$access_controller_action[] = 'goods_settime';
			$access_controller_action[] = 'order_index';
			$access_controller_action[] = 'order_ordersendall';
			$access_controller_action[] = 'order_orderaftersales';
			$access_controller_action[] = 'order_printconfig';
			$access_controller_action[] = 'supply_floworder';
			$access_controller_action[] = 'supply_tixianlist';
			$access_controller_action[] = 'goods_addgoods';
			$access_controller_action[] = 'index_order';
			$access_controller_action[] = 'order_oprefund';
			$access_controller_action[] = 'goods_labelquery';
			$access_controller_action[] = 'goods_edit';
			$access_controller_action[] = 'goods_change';
			$access_controller_action[] = 'goods_tpl';
			$access_controller_action[] = 'goods_mult_tpl';
			$access_controller_action[] = 'goods_settop';
			$access_controller_action[] = 'goods_ajax_batchcates';
			$access_controller_action[] = 'goods_copy';
			$access_controller_action[] = 'communityhead_query_head';
			$access_controller_action[] = 'goods_ajax_batchcates_headgroup';
			$access_controller_action[] = 'goods_ajax_batchtime';
			$access_controller_action[] = 'goods_ajax_batchheads';
			$access_controller_action[] = 'delivery_onekey_tosendallorder';
			$access_controller_action[] = 'order_do_order_quene';
			$access_controller_action[] = 'order_export_form';
			$access_controller_action[] = 'order_detail';
			$access_controller_action[] = 'order_opchangeaddress';
			$access_controller_action[] = 'order_order_print_dan';
			$access_controller_action[] = 'order_all_opprint';
			$access_controller_action[] = 'order_batchsend_import';
			$access_controller_action[] = 'order_check_order_data';
			$access_controller_action[] = 'order_commentstate';
			$access_controller_action[] = 'order_deletecomment';
			$access_controller_action[] = 'order_do_opprint_quene';
			$access_controller_action[] = 'order_do_order_quene';
			$access_controller_action[] = 'order_export_form';
			$access_controller_action[] = 'order_history';
			$access_controller_action[] = 'order_opchangeaddress';
			$access_controller_action[] = 'order_opchangeexpress';
			$access_controller_action[] = 'order_opclose';
			$access_controller_action[] = 'order_opfinish';
			$access_controller_action[] = 'order_oppay';
			$access_controller_action[] = 'order_opprint';
			$access_controller_action[] = 'order_opreceive';
			$access_controller_action[] = 'order_oprefund';
			$access_controller_action[] = 'order_oprefund_do';
			$access_controller_action[] = 'order_oprefund_doform';
			$access_controller_action[] = 'order_oprefund_goods_do';
			$access_controller_action[] = 'order_oprefund_submit';
			$access_controller_action[] = 'order_opremarksaler';
			$access_controller_action[] = 'order_opsend';
			$access_controller_action[] = 'order_opsend_tuanz';
			$access_controller_action[] = 'order_opsend_tuanz_over';
			$access_controller_action[] = 'order_opsendcancel';
			$access_controller_action[] = 'order_order_print_dan';
			$access_controller_action[] = 'order_orderaftersales';
			$access_controller_action[] = 'order_ordercomment';
			$access_controller_action[] = 'order_ordercomment_config';
			$access_controller_action[] = 'order_ordersendall';
			$access_controller_action[] = 'order_print_order';
			$access_controller_action[] = 'order_refund_mult';
			$access_controller_action[] = 'order_refund_mult_do';
			$access_controller_action[] = 'order_refunddone';
			$access_controller_action[] = 'order_sendexpress';
			$access_controller_action[] = 'order_sendexpress_excel_done';
			$access_controller_action[] = 'order_show_order';
			$access_controller_action[] = 'order_show_refund';

			$access_controller_action[] = 'order_check_delivery_config';
			$access_controller_action[] = 'order_thirth_delivery_order';
			$access_controller_action[] = 'order_third_delivery_log_list';
			$access_controller_action[] = 'order_third_cancel_reason';
			$access_controller_action[] = 'order_thirth_cancel_delivery_order';
			$access_controller_action[] = 'order_thirth_renew_delivery_order';

			$access_controller_action[] = 'supply_apply_money';
			$access_controller_action[] = 'supply_login';
			$access_controller_action[] = 'supply_login_do';
			$access_controller_action[] = 'statistics_load_echat_month_head_sales';
			$access_controller_action[] = 'statistics_load_echat_month_goods_sales';
			$access_controller_action[] = 'statistics_index_data';
			$access_controller_action[] = 'statistics_order_buy_data';
			$access_controller_action[] = 'statistics_load_goods_paihang';
			$access_controller_action[] = 'statistics_load_goods_chart';
			$access_controller_action[] = 'statistics_load_echat_member_incr';
			$access_controller_action[] = 'statistics_load_echat_head_incr';
			$access_controller_action[] = 'supply_modifypassword';
			$access_controller_action[] = 'goods_delete';
			$access_controller_action[] = 'express_localtownconfig';
			$access_controller_action[] = 'order_opsend_localtown';
			$access_controller_action[] = 'orderdistribution_choosemember';
			$access_controller_action[] = 'orderdistribution_sub_orderchoose_distribution';
			$access_controller_action[] = 'orderdistribution_index';
			$access_controller_action[] = 'orderdistribution_adddistribution';
			$access_controller_action[] = 'orderdistribution_deletedistribution';
			$access_controller_action[] = 'orderdistribution_change';
			$access_controller_action[] = 'orderdistribution_distribution_list';
			$access_controller_action[] = 'orderdistribution_distributionconfig';
			$access_controller_action[] = 'orderdistribution_withdrawallist';
			$access_controller_action[] = 'orderdistribution_withdrawalconfig';
			$access_controller_action[] = 'orderdistribution_agent_check_apply';

			$access_controller_action[] = 'user_zhenquery';

			$access_controller_action[] = 'salesroom_index';
			$access_controller_action[] = 'salesroom_add';
			$access_controller_action[] = 'salesroom_delete';
			$access_controller_action[] = 'salesroom_query';
			$access_controller_action[] = 'salesroom_member_index';
			$access_controller_action[] = 'salesroom_member_add';
			$access_controller_action[] = 'salesroom_member_delete';
			$access_controller_action[] = 'salesroom_member_query';
			$access_controller_action[] = 'salesroom_order_index';
			$access_controller_action[] = 'salesroom_order_member_orders';
			$access_controller_action[] = 'salesroom_order_member_orders';
			$access_controller_action[] = 'order_order_hexiao';
			$access_controller_action[] = 'order_hexiao_times';
			$access_controller_action[] = 'order_hexiao_goods';
			$access_controller_action[] = 'order_view_hexiao_history';
			$access_controller_action[] = 'order_hexiao_goods_assign_salesroom';
			$access_controller_action[] = 'salesroom_change';

			$c_controller = strtolower(CONTROLLER_NAME);
			$c_action = strtolower(ACTION_NAME);

			$cur_key = $c_controller.'_'.$c_action;

			if( !in_array($cur_key, $access_controller_action) )
			{
				die('您无此操作权限');
			}
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
		die('无权限查看此页面');
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
