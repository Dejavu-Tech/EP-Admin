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

namespace Seller\Model;

class OperatelogModel{


	/*
	 DROP TABLE IF EXISTS `eaterplanet_ecommerce_systemoperation_log`;
	CREATE TABLE `eaterplanet_ecommerce_systemoperation_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `operation_type` enum('detailed_list') NOT NULL COMMENT '操作类型,detailed_list表示清单',
  `operation_seller_id` int(10) NOT NULL COMMENT '管理员id',
  `operation_seller_name` varchar(50) NOT NULL COMMENT '管理员名称',
  `ip` varchar(50) NOT NULL COMMENT 'ip',
  `content` text NOT NULL COMMENT '操作内容',
  `addtime` int(10) NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='吃货星球商城系统操作日志表' AUTO_INCREMENT=1 ;


	*/


	public function addOperateLog($operation_type,$content)
	{
		$ip = get_client_ip();

		$operation_seller_id = SELLERUID;

		if (defined('ROLE') && ROLE == 'agenter' ) {
			$operation_seller_id = $_SESSION["dejavutech_seller_s"]["agent_auth"]["uid"];
			$items = M('eaterplanet_ecommerce_supply')->field('name')->where( array('id' =>$operation_seller_id ) )->find();
			$operation_seller_name ='商户--'.$items['name'];
		}else{
			$items = M('seller')->field('s_uname')->where( array('s_id' =>$operation_seller_id ) )->find();
			$operation_seller_name = '管理员--'.$items['s_uname'];
		}

		$ins_data = array();
		$ins_data['operation_type'] = $operation_type;
		$ins_data['operation_seller_id'] = $operation_seller_id;
		$ins_data['operation_seller_name'] = $operation_seller_name;
		$ins_data['ip'] = $ip;
		$ins_data['content'] = $content;
		$ins_data['addtime'] = time();


		M('eaterplanet_ecommerce_systemoperation_log')->add($ins_data);

	}



     function get_ip_city($ip){
	     $ch = curl_init();
	     $url = 'https://whois.pconline.com.cn/ipJson.jsp?ip='.$ip;
	     //用curl发送接收数据
	     curl_setopt($ch, CURLOPT_URL, $url);
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	     //请求为https
	     curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
	     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	     $location = curl_exec($ch);
	     curl_close($ch);
	     //转码
	     $location = mb_convert_encoding($location, 'utf-8','GB2312');
	     //var_dump($location);
	     //截取{}中的字符串
	     $location = substr($location, strlen('({')+strpos($location, '({'),(strlen($location) - strpos($location, '})'))*(-1));
	    //将截取的字符串$location中的‘，’替换成‘&’   将字符串中的‘：‘替换成‘=’
	     $location = str_replace('"',"",str_replace(":","=",str_replace(",","&",$location)));
	     //php内置函数，将处理成类似于url参数的格式的字符串  转换成数组
	     parse_str($location,$ip_location);
	     return $ip_location['addr'];
		 }


}
?>
