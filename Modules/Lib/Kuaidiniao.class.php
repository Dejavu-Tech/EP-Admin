<?php
namespace Lib;
class Kuaidiniao {
	private $ebusinessid = '';
	private $appkey = '';
	private $requrl = 'http://api.kdniao.cc/api/dist';
	
	public function __construct() {		
			
			$ebuss_info = M('config')->where( array('name' => 'EXPRESS_EBUSS_ID') )->find();
			$exappkey = M('config')->where( array('name' => 'EXPRESS_APPKEY') )->find();
		 
			$this->ebusinessid = $ebuss_info['value'];
			$this->appkey = $exappkey['value'];

	}
	public function subscribe($order_id){
		
		//百世快递
		//70988266665060
		$order_info = M('order')->where( array('order_id' => $order_id) )->find();
		
		//shipping_method
		//shipping_no
		$seller_express = M('seller_express')->where( array('id' =>$order_info['shipping_method'] ) )->find();
		
		if(!empty($seller_express['jianma']))
		{
			$requestData_arr = array('ShipperCode' => $seller_express['jianma'],'OrderCode' => $order_id,'LogisticCode' => $order_info['shipping_no']);
			$requestData = json_encode($requestData_arr);
			
			$datas = array(
				'EBusinessID' => $this->ebusinessid,
				'RequestType' => '1008',
				'RequestData' => urlencode($requestData) ,
				'DataType' => '2',
			);
			$datas['DataSign'] = $this->encrypt($requestData, $this->appkey);
			$result=$this->sendPost($this->requrl, $datas);	
			
			//根据公司业务处理返回的信息......
			
			return $result;
		}
	}
	


//调用获取物流轨迹
//-------------------------------------------------------------


//-------------------------------------------------------------
 
/**
 * Json方式  物流信息订阅
 */
function orderTracesSubByJson(){
	
}

/**
 *  post提交数据 
 * @param  string $url 请求Url
 * @param  array $datas 提交的数据 
 * @return url响应返回的html
 */
function sendPost($url, $datas) {
    $temps = array();	
    foreach ($datas as $key => $value) {
        $temps[] = sprintf('%s=%s', $key, $value);		
    }	
    $post_data = implode('&', $temps);
    $url_info = parse_url($url);
	if(empty($url_info['port']))
	{
		$url_info['port']=80;	
	}
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader.= "Host:" . $url_info['host'] . "\r\n";
    $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
    $httpheader.= "Connection:close\r\n\r\n";
    $httpheader.= $post_data;
    $fd = fsockopen($url_info['host'], $url_info['port']);
    fwrite($fd, $httpheader);
    $gets = "";
	$headerFlag = true;
	while (!feof($fd)) {
		if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
			break;
		}
	}
    while (!feof($fd)) {
		$gets.= fread($fd, 128);
    }
    fclose($fd);  
    
    return $gets;
}

/**
 * 电商Sign签名生成
 * @param data 内容   
 * @param appkey Appkey
 * @return DataSign签名
 */
function encrypt($data, $appkey) {
    return urlencode(base64_encode(md5($data.$appkey)));
}
}
?>