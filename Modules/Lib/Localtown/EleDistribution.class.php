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


/*
 * 饿了么配送接口
 */
namespace Lib\Localtown;

class EleDistribution
{
    private $reqUrl = "";
    //正式地址
    private $ele_url = "https://open-anubis.ele.me/anubis-webapi";
    //联调地址
    private $test_ele_url = "https://exam-anubis.ele.me/anubis-webapi";
    //饿了么app_id
    private $app_id = "";
    //饿了么secret_key
    private $secret_key = "";
    //门店编号
    private $chain_store_code = "";
    //门店名称
    private $transport_name = "";
    //取货点地址
    private $transport_address = "";
	//取货点经度
    private $transport_longitude = "";
	//取货点纬度
    private $transport_latitude = "";
	//取货点经纬度来源
    private $position_source = "";
	//取货点联系方式
    private $transport_tel = "";
    //取货点备注
    private $transport_remark = "";


    //创建蜂鸟订单地址
    private $add_order_url = "/v2/order";
    //重发蜂鸟订单地址
    private $re_add_order_url = "/v2/order";
	//取消蜂鸟订单地址
    private $cancel_order_url = "/v2/order/cancel";
	//查询蜂鸟订单地址
    private $query_delivery_url = "/v2/order/query";

    private $token;

    private $notify_url = "";
    /**
     * 构造函数
     */
    public function __construct(){
        $this->app_id = D('Home/Front')->get_config_by_name('localtown_ele_app_id');
        $this->secret_key = D('Home/Front')->get_config_by_name('localtown_ele_secret_key');
        $this->chain_store_code = D('Home/Front')->get_config_by_name('localtown_ele_store_code');
        $this->transport_name = D('Home/Front')->get_config_by_name('localtown_ele_transport_name');
        $this->transport_address = D('Home/Front')->get_config_by_name('localtown_ele_transport_address');
        $this->transport_longitude = D('Home/Front')->get_config_by_name('localtown_ele_transport_longitude');
        $this->transport_latitude = D('Home/Front')->get_config_by_name('localtown_ele_transport_latitude');
        $this->position_source = D('Home/Front')->get_config_by_name('localtown_ele_position_source');
        $this->transport_tel = D('Home/Front')->get_config_by_name('localtown_ele_transport_tel');
        $this->transport_remark = D('Home/Front')->get_config_by_name('localtown_ele_transport_remark');
        if (strpos($this->transport_name,'测试') !== false || strpos($this->transport_name,'test') !== false) {//测试账号
            $this->reqUrl = $this->test_ele_url;
        } else {
            $this->reqUrl = $this->ele_url;
        }
        $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');
        $this->notify_url = $shop_domain.'delivery_notify.php';
    }

    public function requestToken() {
        $salt = mt_rand(1000, 9999);
        // 获取签名
        $sig = $this->generateSign($this->app_id, $salt, $this->secret_key);
        $url = $this->reqUrl . '/get_access_token';
        $tokenStr = $this->doGet($url, array('app_id' => $this->app_id, 'salt' => $salt, 'signature' => $sig));
        // echo $tokenStr;
        // 获取token
        $this->token = json_decode($tokenStr, true)['data']['access_token'];
    }

    /**
     * 创建蜂鸟配送订单接口
     */
    public function addOrder($order_info){
        //门店信息
        $transport_info = array(
            'transport_name' => $this->transport_name,
            'transport_address' => $this->transport_address,
            'transport_longitude' => $this->transport_longitude,
            'transport_latitude' => $this->transport_latitude,
            'position_source' => $this->position_source,
            'transport_tel' => $this->transport_tel,
            'transport_remark' => $this->transport_remark
        );
        //收货人信息
        $receiver_info = array(
            'receiver_name' => $order_info['shipping_name'],
            'receiver_primary_phone' => $order_info['shipping_tel'],
            'receiver_address' => $order_info['shipping_address'],
            'receiver_longitude' => $order_info['shipping_lng'],
            'position_source' => $this->position_source,
            'receiver_latitude' => $order_info['shipping_lat']
        );
        //订单商品明细
        $product_list = [];
        foreach($order_info['goods_list'] as $k=>$v){
            $goods_array = [];
            $goods_array['item_id'] = $v['goods_id'];//商品编号
            $goods_array['item_name'] = $v['goods_name'];//商品名称
            $goods_array['item_quantity'] = $v['quantity'];//商品数量
            $goods_array['item_price'] = $v['price'];//商品原价
            $goods_array['item_actual_price'] = $v['total'];//商品实际支付金额，必须是乘以数量后的金额，否则影响售后环节的赔付标准
            $goods_array['is_need_package'] = 0;//是否需要ele打包 0:否 1:是
            $goods_array['is_agent_purchase'] = 0;//是否代购 0:否
            $product_list[] = $goods_array;
        }

        //拼装data数据
        $dataArray = array(
            'transport_info' => $transport_info,
            'receiver_info' => $receiver_info,
            'items_json' => $product_list,
            'partner_remark' => '',//商户备注信息
            'partner_order_code' => $order_info['order_num_alias'],     // 第三方订单号, 需唯一
            'notify_url' => $this->notify_url,     //第三方回调 url地址
            'order_type' => 1,//订单类型（1:即时单，3:预约单）
            'order_total_amount' => $order_info['order_total'],//订单总金额（不包含商家的任何活动以及折扣的金额）
            'order_actual_amount' => $order_info['order_total'],//客户需要支付的金额
            'order_weight'=> round($order_info['goods_weight']/1000,2),
            'is_invoiced' => 0,//是否需要发票, 0:不需要, 1:需要
            'invoice' => '',//发票抬头, 如果需要发票, 此项必填
            'order_payment_status' => 1,//订单支付状态 0:未支付 1:已支付
            'order_payment_method' => 1,//订单支付方式 1:在线支付
            'require_payment_pay' => 0,//需要代收时客户应付金额, 如果需要ele代收 此项必填
            'goods_count' => $order_info['goods_count'],//订单货物件数
            'is_agent_payment' => 0,
            'require_receive_time' => 0 //需要送达时间（毫秒）；
        );
        $this->requestToken();
        $salt = mt_rand(1000, 9999);
        $dataJson =  json_encode($dataArray, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        // $urlencodeData = urlencode($dataJson);
        $urlencodeData = urlencode($dataJson);
        $sig = $this->generateBusinessSign($this->app_id, $this->token, $urlencodeData, $salt);   //生成签名
        $requestJson = json_encode(array(
            'app_id' => $this->app_id,
            'salt' => $salt,
            'data' => $urlencodeData,
            'signature' => $sig
        ));
        $resp = $this->doPost($this->reqUrl.$this->add_order_url, $requestJson);
        $result = $this->parseResponseData($resp);
        return $result;
    }
    /**
     * 取消蜂鸟配送订单接口
     */
    public function cancelOrder($orderdistribution_info,$cancel_reason_id,$cancel_reason){
        $data = array(
            "partner_order_code" => $orderdistribution_info['third_order_id'],
            "order_cancel_code" => $cancel_reason_id,           //订单取消编码
            "order_cancel_reason_code" => 2,    //订单取消原因代码(1:用户取消,2:商家取消)
            "order_cancel_description" => $cancel_reason,
            "order_cancel_time" => time() * 1000
        );
        $this->requestToken();
        $dataJson = json_encode($data);
        $salt = mt_rand(1000, 9999);
        $urlencodeData = urlencode($dataJson);
        $sig = $this->generateBusinessSign($this->app_id, $this->token, $urlencodeData, $salt);   //生成签名

        $requestJson = json_encode(array(
            'app_id' => $this->app_id,
            'salt' => $salt,
            'data' => $urlencodeData,
            'signature' => $sig
        ));
        $resp = $this->doPost($this->reqUrl.$this->cancel_order_url, $requestJson);
        $result = $this->parseResponseData($resp);
        return $result;
    }


    public function generateSign($appId, $salt, $secretKey) {
        $seed = 'app_id=' . $appId . '&salt=' . $salt . '&secret_key=' . $secretKey;
        return md5(urlencode($seed));
    }

    public function generateBusinessSign($appId, $token, $urlencodeData, $salt) {
        $seed = 'app_id=' . $appId . '&access_token=' . $token
            . '&data=' . $urlencodeData . '&salt=' . $salt;
        return md5($seed);
    }

    /**
     * 发送GET请求
     * @param string $url
     * @param array $param
     * @return bool|mixed
     */
    public static function doGet($url, $param = null)
    {
        if (empty($url) or (!empty($param) and !is_array($param))) {
            throw new InvalidArgumentException('Params is not of the expected type');
        }
        // 验证url合法性
//        if (!filter_var($url, FILTER_VALIDATE_URL)) {
//            throw new InvalidArgumentException('Url is not valid');
//        }

        if (!empty($param)) {
            $url = trim($url, '?') . '?' . http_build_query($param);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     //  不进行ssl 认证
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!empty($result) and $code == 200) {
            return $result;
        }
        return false;
    }

    /**
     * POST请求
     * @param $url
     * @param $param
     * @return boolean|mixed
     */
    public static function doPost($url, $param, $method = "POST")
    {
        // echo 'Request url is ' . $url . PHP_EOL;
        if (empty($url) or empty($param)) {
            throw new InvalidArgumentException('Params is not of the expected type');
        }

        // 验证url合法性
//        if (!filter_var($url, FILTER_VALIDATE_URL)) {
//            throw new InvalidArgumentException('Url is not valid');
//        }

        if (!empty($param) and is_array($param)) {
            $param = urldecode(json_encode($param));
        } else {
            // $param = urldecode(strval($param));
            $param = strval($param);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     //  不进行ssl 认证

        if (strcmp($method, "POST") == 0) {  // POST 操作
            curl_setopt($ch, CURLOPT_POST, true);
        } else if (strcmp($method, "DELETE") == 0) { // DELETE操作
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        } else {
            throw new InvalidArgumentException('Please input correct http method, such as POST or DELETE');
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: Application/json'));
        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!empty($result) and $code == '200') {
            return $result;
        }
        return false;
    }

    /**
     * 解析响应数据
     * @param $arr返回的数据
     * 响应数据格式：{"status":"success","result":{},"code":0,"msg":"成功"}
     */
    public function parseResponseData($arr){
        $result = array();
        if (empty($arr)) {
            $result['status'] = 0;
            $result['message'] = "接口请求超时或失败";
            $result['code'] = "-2";
        }else{
            $data = json_decode($arr, true);
            if($data['code'] == 200){//成功
                $result['status'] = 1;
                $result['message'] = $data['msg'];
                $result['code'] = $data['code'];
                $result['result'] = $data['data'];
            }else{//失败
                $result['status'] = 0;
                $result['message'] = $data['msg'];
                $result['code'] = $data['code'];
                $result['result'] = $data['data'];
            }
        }
        return $result;
    }
}
?>
