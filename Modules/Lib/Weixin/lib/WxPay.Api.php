<?php
require_once "WxPay.Exception.php";
require_once "WxPay.Config.php";
require_once "WxPay.Data.php";

/**
 *
 * 接口访问类，包含所有微信支付API列表的封装，类中方法为static方法，
 * 每个接口有默认超时时间（除提交被扫支付为10s，上报超时时间为1s外，其他均为6s）
 * @author widyhu
 *
 */
class WxPayApi
{
	/**
	 *
	 * 统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayUnifiedOrder $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function unifiedOrder($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet()) {
			throw new WxPayException("缺少统一支付接口必填参数out_trade_no！");
		}else if(!$inputObj->IsBodySet()){
			throw new WxPayException("缺少统一支付接口必填参数body！");
		}else if(!$inputObj->IsTotal_feeSet()) {
			throw new WxPayException("缺少统一支付接口必填参数total_fee！");
		}else if(!$inputObj->IsTrade_typeSet()) {
			throw new WxPayException("缺少统一支付接口必填参数trade_type！");
		}

		//关联参数
		if($inputObj->GetTrade_type() == "JSAPI" && !$inputObj->IsOpenidSet()){
			throw new WxPayException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！");
		}
		if($inputObj->GetTrade_type() == "NATIVE" && !$inputObj->IsProduct_idSet()){
			throw new WxPayException("统一支付接口中，缺少必填参数product_id！trade_type为JSAPI时，product_id为必填参数！");
		}

		//异步通知url未设置，则使用配置文件中的url
		if(!$inputObj->IsNotify_urlSet()){
			$inputObj->SetNotify_url(WxPayConfig::NOTIFY_URL);//异步通知url
		}

		$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		$inputObj->SetMch_id(WxPayConfig::MCHID);//商户号
		$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
		//$inputObj->SetSpbill_create_ip("1.1.1.1");
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		//签名
		$inputObj->SetSign();
		$xml = $inputObj->ToXml();


		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);

		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 *
	 * 查询订单，WxPayOrderQuery中out_trade_no、transaction_id至少填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayOrderQuery $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function orderQuery($inputObj, $timeOut = 6)
	{


		$wepro_appid = D('Home/Front')->get_config_by_name('wepro_appid');
		$wepro_partnerid = D('Home/Front')->get_config_by_name('wepro_partnerid');


		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WxPayException("订单查询接口中，out_trade_no、transaction_id至少填一个！");
		}
		$inputObj->SetAppid($wepro_appid);//公众账号ID
		$inputObj->SetMch_id($wepro_partnerid);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 *
	 * 关闭订单，WxPayCloseOrder中out_trade_no必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayCloseOrder $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function closeOrder($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/closeorder";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet()) {
			throw new WxPayException("订单查询接口中，out_trade_no必填！");
		}
		$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		$inputObj->SetMch_id(WxPayConfig::MCHID);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}


	/**
		发送红包接口
	**/
	public static function bonuspay($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
		$param = array();

		$param['mch_billno'] = $inputObj['mch_billno'];
		$param['mch_id'] = WxPayConfig::MCHID;
		$param['wxappid'] = WxPayConfig::APPID;
		$param['nick_name'] = $inputObj['nick_name'];// urlencode($inputObj['nick_name']);//$inputObj['nick_name'];
		$param['send_name'] = $inputObj['nick_name'];//urlencode($inputObj['nick_name']);//$inputObj['send_name'];
		$param['re_openid'] = $inputObj['openid'];
		$param['total_amount'] = $inputObj['total_amount'];
		$param['min_value'] = $inputObj['total_amount'];
		$param['max_value'] = $inputObj['total_amount'];
		$param['total_num'] = 1;
		$param['wishing'] = '恭喜发财';//urlencode('恭喜发财');//'恭喜发财';
		$param['client_ip'] = $_SERVER['REMOTE_ADDR'];
		$param['act_name'] =  '组团成功';//urlencode('组团成功');//'组团成功';
		//$param['act_id'] = '';
		$param['remark'] = '团长红包';//urlencode('团长红包');//'团长红包';
		$param['nonce_str'] = self::getNonceStr();
		$param['sign'] = self::MakeSignfh($param);

		$xml = self::arrdatetoxml($param);
		//$xml = iconv("UTF-8", "ISO8859-1", $xml);

		$startTimeStamp = self::getMillisecond();//请求开始时间
		//var_dump($startTimeStamp);die(); ISO8859-1
		$response = self::postXmlCurl($xml, $url, true, $timeOut);

		libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		$res_data = array();
		if($result['return_code']=='SUCCESS')
		{
			$res_data['ret'] = 1;
			$res_data['mes'] = $result['return_msg'];
		} else {
			$res_data['ret'] = 0;
			$res_data['mes'] = $result['return_msg'];
		}

		return $res_data;
	}

	public static  function arrdatetoxml($values)
	{
		$xml = "<xml>";
			foreach ($values as $key=>$val)
			{
				if (is_numeric($val)){
					$xml.="<".$key.">".$val."</".$key.">";
				}else{
					$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
				}
			}
			$xml.="</xml>";
			return $xml;
	}
	/**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
	 */
	public static  function MakeSignfh($values)
	{


		$pay_key = D('Home/Front')->get_config_by_name('wepro_key');

		//签名步骤一：按字典序排序参数
		ksort($values);

		$string = self::ToUrlParamsfh($values);
		//var_dump($string);die();
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".$pay_key;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
	/**
	 * 格式化参数格式化成url参数
	 */
	public static function ToUrlParamsfh($values)
	{
		$buff = "";
		foreach ($values as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = trim($buff, "&");
		return $buff;
	}

    public static function singleProfitSharing($transaction_id, $out_order_no, $receivers, $uniacid=0)
    {
        $wepro_appid = D('Home/Front')->get_config_by_name('wepro_appid');
        $mchid = D('Home/Front')->get_config_by_name('wepro_partnerid');
        $wepro_key = D('Home/Front')->get_config_by_name('wepro_key');

        $url = 'https://api.mch.weixin.qq.com/secapi/pay/profitsharing';

        $params = array();
        $params['appid'] = $wepro_appid;
        $params['mch_id'] = $mchid;
        $params['nonce_str'] = self::getNonceStr();
        $params['sign_type'] = 'HMAC-SHA256';
        $params['transaction_id'] = $transaction_id;
        $params['out_order_no'] = $out_order_no;
        $params['receivers'] = json_encode($receivers);

        $strs = "";
        if(!empty($params)){
            $p =  ksort($params);
            if($p){
                $str = '';
                foreach ($params as $k=>$val){
                    $str .= $k .'=' . $val . '&';
                }
                $strs = rtrim($str, '&');
            }
        }
        error_log("123123123");
        $str = $strs.'&key='.$wepro_key;
        $sign = strtoupper(hash_hmac('sha256', $str, $wepro_key));
        $params['sign'] = strtoupper($sign);
        $xml = self::arrdatetoxml($params);

        $response = self::postXmlCurl($xml, $url, true ,6 );

        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        if ($result) {
            $result['return_code'] = 'SUCCESS';
        } else {
            $result = array();
            $result['return_code'] = 'FAIL';
        }

        // 将结果写入支付日志 /Data/wxpaylogs/
        $data = array();
        $data['result'] = $result;
        $data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
        RecursiveMkdir($data_path);

        $log_name = $data_path.'Receive_'.date('Y-m-d').'.txt';
        $handl = fopen($log_name,'a');
        fwrite($handl,"分账");
        fwrite($handl, json_encode($data));
        fwrite($handl,"\n");
        fclose($handl);

        return $result;
    }
	public static function payToUser($openid,$amount,$username,$desc,$partner_trade_no,$uniacid=0)
	{



		$is_open_yinpay = D('Home/Front')->get_config_by_name('is_open_yinpay');
		if( isset($is_open_yinpay) && $is_open_yinpay == 3 )
		{
			//特约商户
			//小程序appid
			$wepro_appid = D('Home/Front')->get_config_by_name('wepro_appid');
			//特约商户商户id
			$mchid = D('Home/Front')->get_config_by_name('wepro_sub_mch_id');
			//特约商户秘钥
			$wepro_key = D('Home/Front')->get_config_by_name('sup_wepro_key');
		}else{
			$wepro_appid = D('Home/Front')->get_config_by_name('wepro_appid');
			$mchid = D('Home/Front')->get_config_by_name('wepro_partnerid');
			$wepro_key = D('Home/Front')->get_config_by_name('wepro_key');
		}
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

		$params = array();
		$params['amount'] = $amount;
		$params['desc'] = $desc;
		$params['mch_appid'] = $wepro_appid;
		$params['mchid'] = $mchid;
		$params['nonce_str'] = self::getNonceStr();
		$params['partner_trade_no'] = $partner_trade_no;
		$params['openid'] = $openid;
		$params['check_name'] = 'FORCE_CHECK';
		$params['re_user_name'] = $username;
		$params['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];


		$strs = "";

		if(!empty($params)){
		   $p =  ksort($params);
		   if($p){
			   $str = '';
			   foreach ($params as $k=>$val){
				   $str .= $k .'=' . $val . '&';
			   }
			   $strs = rtrim($str, '&');
		   }
		}


		//$str = 'amount='.$params["amount"].'&check_name='.$params["check_name"].'&desc='.$params["desc"].'&mch_appid='.$params["mch_appid"].'&mchid='.$params["mchid"].'&nonce_str='.$params["nonce_str"].'&openid='.$params["openid"].'&partner_trade_no='.$params["partner_trade_no"].'&spbill_create_ip='.$params['spbill_create_ip'].'&key='.$wepro_key;
		$str = $strs.'&key='.$wepro_key;

		$sign = strtoupper(md5($str));
		$params['sign'] = $sign;
		$xml = self::arrdatetoxml($params);

		$is_open_yinpay = D('Home/Front')->get_config_by_name('is_open_yinpay');


				$response = self::postXmlCurl($xml, $url, true ,6 );





		libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

		return $result;
	}

	/**
	 *
	 * 申请退款，WxPayRefund中out_trade_no、transaction_id至少填一个且
	 * out_refund_no、total_fee、refund_fee、op_user_id为必填参数
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayRefund $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function refund($inputObj, $timeOut = 6,$from_type='weixin',$uniacid = 0)
	{

		$url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
		      //https://api.mch.weixin.qq.com/secapi/pay/refund
		$is_pro = false;
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			die("退款申请接口中，out_trade_no、transaction_id至少填一个！");
			throw new WxPayException("退款申请接口中，out_trade_no、transaction_id至少填一个！");
		}else if(!$inputObj->IsOut_refund_noSet()){
			die("退款申请接口中，缺少必填参数out_refund_no！");
			throw new WxPayException("退款申请接口中，缺少必填参数out_refund_no！");
		}else if(!$inputObj->IsTotal_feeSet()){
			die("退款申请接口中，缺少必填参数total_fee！");
			throw new WxPayException("退款申请接口中，缺少必填参数total_fee！");
		}else if(!$inputObj->IsRefund_feeSet()){
			die("退款申请接口中，缺少必填参数refund_fee！");
			throw new WxPayException("退款申请接口中，缺少必填参数refund_fee！");
		}else if(!$inputObj->IsOp_user_idSet()){
			die("退款申请接口中，缺少必填参数op_user_id！");
			throw new WxPayException("退款申请接口中，缺少必填参数op_user_id！");
		}

		//$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		if($from_type =='wepro')
		{
			$wepro_appid = D('Home/Front')->get_config_by_name('wepro_appid');

			$inputObj->SetAppid($wepro_appid);//公众账号ID
		}
		else if( $from_type == 'teweixin' )
		{
		    //begin
		    $wepro_appid = D('Home/Front')->get_config_by_name('wepro_fuwu_appid');

		    $inputObj->SetAppid($wepro_appid);
		    //end
		}
		else{
			$wepro_appid = D('Home/Front')->get_config_by_name('wepro_appid');
			$inputObj->SetAppid($wepro_appid);//公众账号ID
		}
		$mchid = D('Home/Front')->get_config_by_name('wepro_partnerid');

		if( $from_type == 'teweixin' )
		{
		    //begin
		    $mchid = D('Home/Front')->get_config_by_name('wepro_fuwu_partnerid');
		    //end
		}

		$inputObj->SetMch_id($mchid);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		if( $from_type == 'teweixin' )
		{
		    $sub_appid = D('Home/Front')->get_config_by_name('wepro_appid');
		    $sub_mch_id = D('Home/Front')->get_config_by_name('wepro_sub_mch_id');

		    //begin
		    $inputObj->SetSub_appid($sub_appid);//商户号
		    $inputObj->SetSub_mch_id($sub_mch_id);//商户号
		    $is_pro = true;
		    //end
		}


		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		//var_dump($startTimeStamp);die();
// 		$response = self::postXmlCurl($xml, $url, true, $timeOut);
				$response = self::postXmlCurl($xml, $url, true, $timeOut,$uniacid,$is_pro);//

		//var_dump($response);die();
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 *
	 * 查询退款
	 * 提交退款申请后，通过调用该接口查询退款状态。退款有一定延时，
	 * 用零钱支付的退款20分钟内到账，银行卡支付的退款3个工作日后重新查询退款状态。
	 * WxPayRefundQuery中out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayRefundQuery $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function refundQuery($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/refundquery";
		//检测必填参数
		if(!$inputObj->IsOut_refund_noSet() &&
			!$inputObj->IsOut_trade_noSet() &&
			!$inputObj->IsTransaction_idSet() &&
			!$inputObj->IsRefund_idSet()) {
			throw new WxPayException("退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！");
		}
		$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		$inputObj->SetMch_id(WxPayConfig::MCHID);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 * 下载对账单，WxPayDownloadBill中bill_date为必填参数
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayDownloadBill $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function downloadBill($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/downloadbill";
		//检测必填参数
		if(!$inputObj->IsBill_dateSet()) {
			throw new WxPayException("对账单接口中，缺少必填参数bill_date！");
		}
		$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		$inputObj->SetMch_id(WxPayConfig::MCHID);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();

		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		if(substr($response, 0 , 5) == "<xml>"){
			return "";
		}
		return $response;
	}

	/**
	 * 提交被扫支付API
	 * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，
	 * 由商户收银台或者商户后台调用该接口发起支付。
	 * WxPayWxPayMicroPay中body、out_trade_no、total_fee、auth_code参数必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayWxPayMicroPay $inputObj
	 * @param int $timeOut
	 */
	public static function micropay($inputObj, $timeOut = 10)
	{
		$url = "https://api.mch.weixin.qq.com/pay/micropay";
		//检测必填参数
		if(!$inputObj->IsBodySet()) {
			throw new WxPayException("提交被扫支付API接口中，缺少必填参数body！");
		} else if(!$inputObj->IsOut_trade_noSet()) {
			throw new WxPayException("提交被扫支付API接口中，缺少必填参数out_trade_no！");
		} else if(!$inputObj->IsTotal_feeSet()) {
			throw new WxPayException("提交被扫支付API接口中，缺少必填参数total_fee！");
		} else if(!$inputObj->IsAuth_codeSet()) {
			throw new WxPayException("提交被扫支付API接口中，缺少必填参数auth_code！");
		}

		$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
		$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		$inputObj->SetMch_id(WxPayConfig::MCHID);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 *
	 * 撤销订单API接口，WxPayReverse中参数out_trade_no和transaction_id必须填写一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayReverse $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 */
	public static function reverse($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/secapi/pay/reverse";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WxPayException("撤销订单API接口中，参数out_trade_no和transaction_id必须填写一个！");
		}

		$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		$inputObj->SetMch_id(WxPayConfig::MCHID);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, true, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 *
	 * 测速上报，该方法内部封装在report中，使用时请注意异常流程
	 * WxPayReport中interface_url、return_code、result_code、user_ip、execute_time_必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayReport $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function report($inputObj, $timeOut = 1)
	{
		$url = "https://api.mch.weixin.qq.com/payitil/report";
		//检测必填参数
		if(!$inputObj->IsInterface_urlSet()) {
			throw new WxPayException("接口URL，缺少必填参数interface_url！");
		} if(!$inputObj->IsReturn_codeSet()) {
			throw new WxPayException("返回状态码，缺少必填参数return_code！");
		} if(!$inputObj->IsResult_codeSet()) {
			throw new WxPayException("业务结果，缺少必填参数result_code！");
		} if(!$inputObj->IsUser_ipSet()) {
			throw new WxPayException("访问接口IP，缺少必填参数user_ip！");
		} if(!$inputObj->IsExecute_time_Set()) {
			throw new WxPayException("接口耗时，缺少必填参数execute_time_！");
		}
		$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		$inputObj->SetMch_id(WxPayConfig::MCHID);//商户号
		$inputObj->SetUser_ip($_SERVER['REMOTE_ADDR']);//终端ip
		$inputObj->SetTime(date("YmdHis"));//商户上报时间
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		return $response;
	}

	/**
	 *
	 * 生成二维码规则,模式一生成支付二维码
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayBizPayUrl $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function bizpayurl($inputObj, $timeOut = 6)
	{
		if(!$inputObj->IsProduct_idSet()){
			throw new WxPayException("生成二维码，缺少必填参数product_id！");
		}

		$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		$inputObj->SetMch_id(WxPayConfig::MCHID);//商户号
		$inputObj->SetTime_stamp(time());//时间戳
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign();//签名

		return $inputObj->GetValues();
	}

	/**
	 *
	 * 转换短链接
	 * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，
	 * 减小二维码数据量，提升扫描速度和精确度。
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayShortUrl $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function shorturl($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/tools/shorturl";
		//检测必填参数
		if(!$inputObj->IsLong_urlSet()) {
			throw new WxPayException("需要转换的URL，签名用原串，传输需URL encode！");
		}
		$inputObj->SetAppid(WxPayConfig::APPID);//公众账号ID
		$inputObj->SetMch_id(WxPayConfig::MCHID);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

 	/**
 	 *
 	 * 支付结果通用通知
 	 * @param function $callback
 	 * 直接回调函数使用方法: notify(you_function);
 	 * 回调类成员函数方法:notify(array($this, you_function));
 	 * $callback  原型为：function function_name($data){}
 	 */
	public static function notify($callback, &$msg)
	{
		//获取通知的数据

		if( !isset($GLOBALS['HTTP_RAW_POST_DATA']) )
		{
			$xml = file_get_contents('php://input');
		}else{
			$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		}

		//如果返回成功则验证签名
		try {
			$result = WxPayResults::Init($xml);
		} catch (WxPayException $e){
			$msg = $e->errorMessage();

			return false;
		}



		return call_user_func($callback, $result);
	}

	/**
	 *
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
		}
		return $str;
	}

	/**
	 * 直接输出xml
	 * @param string $xml
	 */
	public static function replyNotify($xml)
	{
		echo $xml;
	}

	/**
	 *
	 * 上报数据， 上报的时候将屏蔽所有异常流程
	 * @param string $usrl
	 * @param int $startTimeStamp
	 * @param array $data
	 */
	private static function reportCostTime($url, $startTimeStamp, $data)
	{
		//如果不需要上报数据
		if(WxPayConfig::REPORT_LEVENL == 0){
			return;
		}
		//如果仅失败上报
		if(WxPayConfig::REPORT_LEVENL == 1 &&
			 array_key_exists("return_code", $data) &&
			 $data["return_code"] == "SUCCESS" &&
			 array_key_exists("result_code", $data) &&
			 $data["result_code"] == "SUCCESS")
		 {
		 	return;
		 }

		//上报逻辑
		$endTimeStamp = self::getMillisecond();
		$objInput = new WxPayReport();
		$objInput->SetInterface_url($url);
		$objInput->SetExecute_time_($endTimeStamp - $startTimeStamp);
		//返回状态码
		if(array_key_exists("return_code", $data)){
			$objInput->SetReturn_code($data["return_code"]);
		}
		//返回信息
		if(array_key_exists("return_msg", $data)){
			$objInput->SetReturn_msg($data["return_msg"]);
		}
		//业务结果
		if(array_key_exists("result_code", $data)){
			$objInput->SetResult_code($data["result_code"]);
		}
		//错误代码
		if(array_key_exists("err_code", $data)){
			$objInput->SetErr_code($data["err_code"]);
		}
		//错误代码描述
		if(array_key_exists("err_code_des", $data)){
			$objInput->SetErr_code_des($data["err_code_des"]);
		}
		//商户订单号
		if(array_key_exists("out_trade_no", $data)){
			$objInput->SetOut_trade_no($data["out_trade_no"]);
		}
		//设备号
		if(array_key_exists("device_info", $data)){
			$objInput->SetDevice_info($data["device_info"]);
		}

		try{
			self::report($objInput);
		} catch (WxPayException $e){
			//不做任何处理
		}
	}

	/**
	 * 以post方式提交xml到对应的接口url
	 *
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws WxPayException
	 */
	private static function postXmlCurl($xml, $url, $useCert = false, $second = 30, $uniacid=0,$is_pro = false)
	{

		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		//如果有配置代理这里就设置代理
		/**
		if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
			&& WxPayConfig::CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
		}
		**/
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);//严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		if($useCert == true){
			$path = ROOT_PATH.'/Data/wxpaylogs/';

				// $wechat_apiclient_cert_pem = D('Home/Front')->get_config_by_name('wechat_apiclient_cert_pem');
				// $wechat_apiclient_key_pem = D('Home/Front')->get_config_by_name('wechat_apiclient_key_pem');
				if($is_pro){
		    	$wechat_apiclient_cert_pem = D('Home/Front')->get_config_by_name('sup_wechat_apiclient_cert_pem');
				$wechat_apiclient_key_pem = D('Home/Front')->get_config_by_name('sup_wechat_apiclient_key_pem');
		//	dump($wechat_apiclient_key_pem);die;

			}else{
		    	$wechat_apiclient_cert_pem = D('Home/Front')->get_config_by_name('wechat_apiclient_cert_pem');
				$wechat_apiclient_key_pem = D('Home/Front')->get_config_by_name('wechat_apiclient_key_pem');
			}


				$file_cert_name = mt_rand(1,99).'_cert.pem';
				$file_key_name = mt_rand(100,999).'_key.pem';



			file_put_contents($path.$file_cert_name,$wechat_apiclient_cert_pem);
			file_put_contents($path.$file_key_name,$wechat_apiclient_key_pem);

			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $path.$file_cert_name );
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $path.$file_key_name );

		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//var_dump( $data);die();
		//返回结果
		if($data){
			if($useCert == true)
			{
				unlink($path.$file_cert_name);
				unlink($path.$file_key_name);
			}
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			curl_close($ch);
			throw new WxPayException("curl出错，错误码:$error");
		}
	}

    // 添加分账接收方
    public static function addReceiver()
    {
        error_log("addReceiver method called");
        $wepro_appid = D('Home/Front')->get_config_by_name('wepro_appid');
        $mchid = D('Home/Front')->get_config_by_name('wepro_partnerid');
        $wepro_key = D('Home/Front')->get_config_by_name('wepro_key');

        $receiver = array();
        $receiver['type'] = 'MERCHANT_ID';
        $receiver['account'] = '1612576436';
        $receiver['name'] = '蒂佳芙科技（云南）有限公司';
        $receiver['relation_type'] = 'CUSTOM';
        $receiver['custom_relation'] = '服务商';

        $url = 'https://api.mch.weixin.qq.com/secapi/pay/profitsharingaddreceiver';
        $params = array();
        $params['appid'] = $wepro_appid;
        $params['mch_id'] = $mchid;
        $params['nonce_str'] = self::getNonceStr();
        $params['sign_type'] = 'HMAC-SHA256';
        $params['receiver'] = json_encode($receiver);

        $strs = "";
        if(!empty($params)){
            $p =  ksort($params);
            if($p){
                $str = '';
                foreach ($params as $k=>$val){
                    $str .= $k .'=' . $val . '&';
                }
                $strs = rtrim($str, '&');
            }
        }

        $str = $strs.'&key='.$wepro_key;
        $sign = hash_hmac('sha256', $str, $wepro_key);
        $params['sign'] = strtoupper($sign);
        $xml = self::arrdatetoxml($params);

        $response = self::postXmlCurl($xml, $url, true ,6 );

        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        if ($result) {
            $result['return_code'] = 'SUCCESS';
        } else {
            $result = array();
            $result['return_code'] = 'FAIL';
        }

        // 将结果写入支付日志 /Data/wxpaylogs/
        $data = array();
        $data['result'] = $result;
        $data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";
        RecursiveMkdir($data_path);

        $log_name = $data_path.'addReceiver_'.date('Y-m-d').'.txt';
        $handl = fopen($log_name,'a');
        fwrite($handl,"关联分账接收方：");
        fwrite($handl, json_encode($data));
        fwrite($handl,"\n");
        fclose($handl);

        return $result;
    }

    private static function sup_postXmlCurl($xml, $url, $useCert = false, $second = 30, $uniacid=0)
	{

		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		//如果有配置代理这里就设置代理
		/**
		if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
			&& WxPayConfig::CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
		}
		**/
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);//严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		if($useCert == true){
			$path = ROOT_PATH.'/Data/wxpaylogs/';

			$wechat_apiclient_cert_pem = D('Home/Front')->get_config_by_name('sup_wechat_apiclient_cert_pem');
			$wechat_apiclient_key_pem = D('Home/Front')->get_config_by_name('sup_wechat_apiclient_key_pem');
			$file_cert_name = mt_rand(1,99).'_cert.pem';
			$file_key_name = mt_rand(100,999).'_key.pem';



			file_put_contents($path.$file_cert_name,$wechat_apiclient_cert_pem);
			file_put_contents($path.$file_key_name,$wechat_apiclient_key_pem);

			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $path.$file_cert_name );
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $path.$file_key_name );

		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//var_dump( $data);die();
		//返回结果
		$error = curl_errno($ch);

		if($data){
			if($useCert == true)
			{
				unlink($path.$file_cert_name);
				unlink($path.$file_key_name);
			}
			curl_close($ch);
			return $data;
		} else {

			$error = curl_errno($ch);
			curl_close($ch);
			throw new WxPayException("curl出错，错误码:$error");
		}
	}



	/**
	 * 获取毫秒级别的时间戳
	 */
	private static function getMillisecond()
	{
		//获取毫秒的时间戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}
}

