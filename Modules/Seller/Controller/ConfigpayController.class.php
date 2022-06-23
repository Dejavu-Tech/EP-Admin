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

class ConfigpayController extends CommonController{

	protected function _initialize(){
		parent::_initialize();

		//'pinjie' => '拼团介绍',
	}

	public function index()
	{
		if (IS_POST) {

			$data = I('request.parameter');
			$param = array();
			if(trim($data['wechat_apiclient_cert_pem'])) $param['wechat_apiclient_cert_pem'] = trim($data['wechat_apiclient_cert_pem']);
			if(trim($data['wechat_apiclient_key_pem'])) $param['wechat_apiclient_key_pem'] = trim($data['wechat_apiclient_key_pem']);
			if(trim($data['wechat_rootca_pem'])) $param['wechat_rootca_pem'] = trim($data['wechat_rootca_pem']);

			if(trim($data['weapp_apiclient_cert_pem'])) $param['weapp_apiclient_cert_pem'] = trim($data['weapp_apiclient_cert_pem']);
			if(trim($data['weapp_apiclient_key_pem'])) $param['weapp_apiclient_key_pem'] = trim($data['weapp_apiclient_key_pem']);
			if(trim($data['weapp_rootca_pem'])) $param['weapp_rootca_pem'] = trim($data['weapp_rootca_pem']);

			if(trim($data['app_apiclient_cert_pem'])) $param['app_apiclient_cert_pem'] = trim($data['app_apiclient_cert_pem']);
			if(trim($data['app_apiclient_key_pem'])) $param['app_apiclient_key_pem'] = trim($data['app_apiclient_key_pem']);
			if(trim($data['app_rootca_pem'])) $param['app_rootca_pem'] = trim($data['app_rootca_pem']);

			D('Seller/Config')->update($param);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;

		$this->display();
	}





}
?>
