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

class PaymentController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='系统';
			$this->breadcrumb2='支付方式';
	}

     public function index(){

		$this->list=M('payment')->field('payment_code,payment_name,payment_state')->select();

    	$this->display();
	 }


	function edit(){

		$code=I('code');

		if(IS_POST){
			$data = array();
			$data['payment_state'] = intval($_POST["payment_state"]);
			$payment_id = intval($_POST["payment_id"]);
			$payment_config	= '';
			$config_array = explode(',',$_POST["config_name"]);//配置参数
			if(is_array($config_array) && !empty($config_array)) {
				$config_info = array();
				foreach ($config_array as $k) {
					$config_info[$k] = trim($_POST[$k]);
				}

				$payment_config	= serialize($config_info);
			}
			$data['payment_config'] = $payment_config;

			$r=M('payment')->where(array('payment_id'=>$payment_id))->save($data);

			if($r){
				$this->success('编辑成功',U('Payment/index'));
			}else{
				$this->error('编辑失败');
			}
			die;
		}
		$this->action=U('Payment/edit');

		$payment = M('payment')->where(array('payment_code'=>$code))->find();

		$this->config_array=array(
			'id'=>$payment['payment_id'],
			'config'=>empty($payment['payment_config'])?'':unserialize($payment['payment_config']),
			'payment_state'=>$payment['payment_state']
		);

		switch ($code) {
			case 'alipay':
				$crumbs='支付宝';
			break;
		}

		$this->crumbs=$crumbs;

	 	$this->display($code);
	}



}
?>
