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

/*
 * 达达配送接口
 */
namespace Lib\Localtown;
class Imdada
{
    //正式地址
    private $reqUrl = "";
    //达达开发者app_key
    private $app_key = "";
    //达达开发者app_secret
    private $app_secret = "";
    //商户id
    private $source_id = "";
    //门店编号
    private $shop_no = "";
    //订单所在城市的code
    private $city_code = "";

    /**
     * api版本
     */
    public $v = "1.0";
    /**
     * 数据格式
     */
    public $format = "json";

    //新增配送单地址
    private $add_order_url = "/api/order/addOrder";
    //配送单重发地址
    private $re_add_order_url = "/api/order/reAddOrder";

    private $cancel_order_url = "/api/order/formalCancel";

    private $query_delivery_url = "/api/order/queryDeliverFee";

    private $notify_url = "";
    /**
     * 构造函数
     */
    public function __construct(){
        $merchant_id = D('Home/Front')->get_config_by_name('localtown_imdada_merchant_id');
        $this->app_key = D('Home/Front')->get_config_by_name('localtown_imdada_appkey');
        $this->app_secret = D('Home/Front')->get_config_by_name('localtown_imdada_appsecret');
        $this->shop_no = D('Home/Front')->get_config_by_name('localtown_imdada_shop_no');
        $this->source_id = $merchant_id;

        $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');
        $this->notify_url = $shop_domain.'delivery_notify.php';

        $city_name = D('Home/Front')->get_config_by_name('localtown_shop_city_id');
        $city_name = str_replace('市','',$city_name);
        $area_info = M('eaterplanet_ecommerce_imdada_area_code')->where( array('city_name' => $city_name) )->find();
        $this->city_code = $area_info['city_code'];

        if ($merchant_id == '73753') {//测试账号
            $this->shop_no = "11047059";//测试门店号
            $this->reqUrl = "http://newopen.qa.imdada.cn";
        } else {
            $this->reqUrl = "https://newopen.imdada.cn";
        }
    }

    /**
     * 新增配送单接口
     */
    public function addOrder($order_info){
        $order_data = array();
        //商户地址
        if($order_info['store_id'] > 0){
            $city_name = $order_info['store_data']['city'];
            $city_name = str_replace('市','',$city_name);
            $area_info = M('eaterplanet_ecommerce_imdada_area_code')->where( array('city_name' => $city_name) )->find();
            $this->city_code = $area_info['city_code'];
        }
        //门店编号
        $order_data['shop_no'] = $this->shop_no;
        //第三方订单ID
        $order_data['origin_id'] = $order_info['order_num_alias'];
        //订单所在城市的code
        $order_data['city_code'] = $this->city_code;

        //订单金额
        $order_data['cargo_price'] = $order_info['order_total'];
        //是否需要垫付 1:是 0:否 (垫付订单金额，非运费)
        $order_data['is_prepay'] = "0";
        //收货人姓名
        $order_data['receiver_name'] = $order_info['shipping_name'];
        //收货人地址
        $order_data['receiver_address'] = $order_info['shipping_address'];
        //收货人地址纬度
        $order_data['receiver_lat'] = $order_info['shipping_lat'];
        //收货人地址经度
        $order_data['receiver_lng'] = $order_info['shipping_lng'];
        //回调URL
        $order_data['callback'] = $this->notify_url;
        //收货人手机号
        $order_data['receiver_phone'] = $order_info['shipping_tel'];

        /**************非必填项*******************/
        //收货人座机号
        $order_data['receiver_tel'] = "";
        //小费
        $order_data['tips'] = "0";
        //订单备注
        $order_data['info'] = $order_info['note_content'];
        //订单商品类型：食品小吃-1,饮料-2,鲜花-3,文印票务-8,便利店-9,水果生鲜-13,同城电商-19, 医药-20,
        //蛋糕-21,酒品-24,小商品市场-25,服装-26,汽修零配-27,数码-28,小龙虾-29,火锅-51,其他-5
        $order_data['cargo_type'] = "19";
        //订单商品数量
        $order_data['cargo_num'] = $order_info['goods_count'];
        //订单重量（单位：Kg）
        $order_data['cargo_weight'] = round($order_info['goods_weight']/1000,2);
        //订单来源标示
        $order_data['origin_mark'] = "";
        //订单来源编号
        $order_data['origin_mark_no'] = "";
        //是否选择直拿直送（0：不需要；1：需要）
        $order_data['is_direct_delivery'] = "0";

        //订单商品明细
        $product_list = [];
        foreach($order_info['goods_list'] as $k=>$v){
            $goods_array = [];
            $goods_array['sku_name'] = $v['goods_name'];//商品名称
            $goods_array['src_product_no'] = $v['goods_id'];//商品编码
            $goods_array['count'] = $v['quantity'];//商品数量
            $product_list[] = $goods_array;
        }
        $order_data['product_list'] = $product_list;

        $body_data = json_encode($order_data);
        $reqParams = $this->bulidRequestParams($body_data);
        $resp = $this->getHttpRequestWithPost($this->reqUrl.$this->add_order_url,json_encode($reqParams));
        $result = $this->parseResponseData($resp);
        return $result;
    }

    /**
     * 重新推送配送单接口
     */
    public function reAddOrder($order_info){
        $order_data = array();
        //商户地址
        if($order_info['store_id'] > 0){
            $city_name = $order_info['store_data']['city'];
            $city_name = str_replace('市','',$city_name);
            $area_info = M('eaterplanet_ecommerce_imdada_area_code')->where( array('city_name' => $city_name) )->find();
            $this->city_code = $area_info['city_code'];
        }
        //门店编号
        $order_data['shop_no'] = $this->shop_no;
        //第三方订单ID
        $order_data['origin_id'] = $order_info['order_num_alias'];
        //订单所在城市的code
        $order_data['city_code'] = $this->city_code;
        //订单金额
        $order_data['cargo_price'] = $order_info['order_total'];
        //是否需要垫付 1:是 0:否 (垫付订单金额，非运费)
        $order_data['is_prepay'] = "0";
        //收货人姓名
        $order_data['receiver_name'] = $order_info['shipping_name'];
        //收货人地址
        $order_data['receiver_address'] = $order_info['shipping_address'];
        //收货人地址纬度
        $order_data['receiver_lat'] = $order_info['shipping_lat'];
        //收货人地址经度
        $order_data['receiver_lng'] = $order_info['shipping_lng'];
        //回调URL
        $order_data['callback'] = $this->notify_url;
        //收货人手机号
        $order_data['receiver_phone'] = $order_info['shipping_tel'];

        /**************非必填项*******************/
        //收货人座机号
        $order_data['receiver_tel'] = "";
        //小费
        $order_data['tips'] = "0";
        //订单备注
        $order_data['info'] = $order_info['note_content'];
        //订单商品类型：食品小吃-1,饮料-2,鲜花-3,文印票务-8,便利店-9,水果生鲜-13,同城电商-19, 医药-20,
        //蛋糕-21,酒品-24,小商品市场-25,服装-26,汽修零配-27,数码-28,小龙虾-29,火锅-51,其他-5
        $order_data['cargo_type'] = "19";
        //订单商品数量
        $order_data['cargo_num'] = $order_info['goods_count'];
        //订单重量（单位：Kg）
        $order_data['cargo_weight'] = round($order_info['goods_weight']/1000,2);
        //订单来源标示
        $order_data['origin_mark'] = "";
        //订单来源编号
        $order_data['origin_mark_no'] = "";
        //是否选择直拿直送（0：不需要；1：需要）
        $order_data['is_direct_delivery'] = "0";

        //订单商品明细
        $product_list = [];
        foreach($order_info['goods_list'] as $k=>$v){
            $goods_array = [];
            $goods_array['sku_name'] = $v['goods_name'];//商品名称
            $goods_array['src_product_no'] = $v['goods_id'];//商品编码
            $goods_array['count'] = $v['quantity'];//商品数量
            $product_list[] = $goods_array;
        }
        $order_data['product_list'] = $product_list;

        $body_data = json_encode($order_data);
        $reqParams = $this->bulidRequestParams($body_data);
        $resp = $this->getHttpRequestWithPost($this->reqUrl.$this->re_add_order_url,json_encode($reqParams));
        $result = $this->parseResponseData($resp);
        return $result;
    }

    /**
     * 取消达达配送订单
     * @param $orderdistribution_info
     */
    public function cancelOrder($order_info,$cancel_reason_id,$cancel_reason){
        $order_data = array();
        //第三方订单ID
        $order_data['order_id'] = $order_info['order_num_alias'];
        //取消原因ID
        $order_data['cancel_reason_id'] = $cancel_reason_id;
        //取消原因
        $order_data['cancel_reason'] = $cancel_reason;
        $body_data = json_encode($order_data);
        $reqParams = $this->bulidRequestParams($body_data);
        $resp = $this->getHttpRequestWithPost($this->reqUrl.$this->cancel_order_url,json_encode($reqParams));
        $result = $this->parseResponseData($resp);
        return $result;
    }


    /**
     * 查询订单运费接口
     */
    public function queryDeliverFee($order_info){
        $order_data = array();
        //商户地址
        if($order_info['store_id'] > 0){
            $city_name = $order_info['store_data']['city'];
            $city_name = str_replace('市','',$city_name);
            $area_info = M('eaterplanet_ecommerce_imdada_area_code')->where( array('city_name' => $city_name) )->find();
            $this->city_code = $area_info['city_code'];
        }
        //门店编号
        $order_data['shop_no'] = $this->shop_no;
        //第三方订单ID
        $order_data['origin_id'] = $order_info['order_num_alias'];
        //订单所在城市的code
        $order_data['city_code'] = $this->city_code;
        //订单金额
        $order_data['cargo_price'] = $order_info['order_total'];
        //是否需要垫付 1:是 0:否 (垫付订单金额，非运费)
        $order_data['is_prepay'] = "0";
        //收货人姓名
        $order_data['receiver_name'] = $order_info['shipping_name'];
        //收货人地址
        $order_data['receiver_address'] = $order_info['shipping_address'];
        //收货人地址纬度
        $order_data['receiver_lat'] = $order_info['shipping_lat'];
        //收货人地址经度
        $order_data['receiver_lng'] = $order_info['shipping_lng'];
        //回调URL
        $order_data['callback'] = $this->notify_url;
        //收货人手机号
        $order_data['receiver_phone'] = $order_info['shipping_tel'];

        /**************非必填项*******************/
        //收货人座机号
        $order_data['receiver_tel'] = "";
        //小费
        $order_data['tips'] = "0";
        //订单备注
        $order_data['info'] = $order_info['note_content'];
        //订单商品类型：食品小吃-1,饮料-2,鲜花-3,文印票务-8,便利店-9,水果生鲜-13,同城电商-19, 医药-20,
        //蛋糕-21,酒品-24,小商品市场-25,服装-26,汽修零配-27,数码-28,小龙虾-29,火锅-51,其他-5
        $order_data['cargo_type'] = "19";
        //订单商品数量
        $order_data['cargo_num'] = $order_info['goods_count'];
        //订单重量（单位：Kg）
        $order_data['cargo_weight'] = round($order_info['goods_weight']/1000,2);
        //订单来源标示
        $order_data['origin_mark'] = "";
        //订单来源编号
        $order_data['origin_mark_no'] = "";
        //是否选择直拿直送（0：不需要；1：需要）
        $order_data['is_direct_delivery'] = "0";

        //订单商品明细
        $product_list = [];
        foreach($order_info['goods_list'] as $k=>$v){
            $goods_array = [];
            $goods_array['sku_name'] = $v['goods_name'];//商品名称
            $goods_array['src_product_no'] = $v['goods_id'];//商品编码
            $goods_array['count'] = $v['quantity'];//商品数量
            $product_list[] = $goods_array;
        }
        $order_data['product_list'] = $product_list;

        $body_data = json_encode($order_data);
        $reqParams = $this->bulidRequestParams($body_data);
        $resp = $this->getHttpRequestWithPost($this->reqUrl.$this->query_delivery_url,json_encode($reqParams));
        $result = $this->parseResponseData($resp);
        return $result;
    }

    /**
     * 模拟取货
     */
    public function orderFetch($order_sn){
        $order_data = array();
        //第三方订单ID
        $order_data['order_id'] = $order_sn;

        $body_data = json_encode($order_data);
        $reqParams = $this->bulidRequestParams($body_data);
        $resp = $this->getHttpRequestWithPost($this->reqUrl."/api/order/fetch",json_encode($reqParams));
        echo $resp.'<br/>';
        $result = $this->parseResponseData($resp);
        print_r($result);
    }

    /**
     * 模拟完成订单
     */
    public function orderFinish($order_sn){
        $order_data = array();
        //第三方订单ID
        $order_data['order_id'] = $order_sn;

        $body_data = json_encode($order_data);
        $reqParams = $this->bulidRequestParams($body_data);
        $resp = $this->getHttpRequestWithPost($this->reqUrl."/api/order/finish",json_encode($reqParams));
        echo $resp.'<br/>';
        $result = $this->parseResponseData($resp);
        print_r($result);
    }

    /**
     * 模拟取消订单
     */
    public function orderCancel($order_sn){
        $order_data = array();
        //第三方订单ID
        $order_data['order_id'] = $order_sn;

        $body_data = json_encode($order_data);
        $reqParams = $this->bulidRequestParams($body_data);
        $resp = $this->getHttpRequestWithPost($this->reqUrl."/api/order/cancel",json_encode($reqParams));
        echo $resp.'<br/>';
        $result = $this->parseResponseData($resp);
        print_r($result);
    }

    /**
     * 模拟异常妥投物品返还中
     */
    public function orderAbnormal($order_sn){
        $order_data = array();
        //第三方订单ID
        $order_data['order_id'] = $order_sn;

        $body_data = json_encode($order_data);
        $reqParams = $this->bulidRequestParams($body_data);
        $resp = $this->getHttpRequestWithPost($this->reqUrl."/api/order/delivery/abnormal/back",json_encode($reqParams));
        echo $resp.'<br/>';
        $result = $this->parseResponseData($resp);
        print_r($result);
    }


    /**
     * 构造请求数据
     * data:业务参数，json字符串
     */
    public function bulidRequestParams($body_data){
        $requestParams = array();
        $requestParams['app_key'] = $this->app_key;
        $requestParams['body'] = $body_data;
        $requestParams['format'] = $this->format;
        $requestParams['v'] = $this->v;
        $requestParams['source_id'] = $this->source_id;
        $requestParams['timestamp'] = time();
        $requestParams['signature'] = $this->_sign($requestParams);
        return $requestParams;
    }

    /**
     * 签名生成signature
     */
    public function _sign($data){
        //1.升序排序
        ksort($data);

        //2.字符串拼接
        $args = "";
        foreach ($data as $key => $value) {
            $args.=$key.$value;
        }
        $args = $this->app_secret . $args . $this->app_secret;
        //3.MD5签名,转为大写
        $sign = strtoupper(md5($args));

        return $sign;
    }


    /**
     * 发送请求,POST
     * @param $url 指定URL完整路径地址
     * @param $data 请求的数据
     */
    public function getHttpRequestWithPost($url,$data){
        // json
        $headers = array(
            'Content-Type: application/json',
        );
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $resp = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果在执行curl的过程中出现异常，可以打开此开关查看异常内容。
        $info = curl_getinfo($curl);
        curl_close($curl);
        if (isset($info['http_code']) && $info['http_code'] == 200) {
            return $resp;
        }else{
            return '';
        }
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
            if($data['status'] == 'success' && $data['code'] == 0){//成功
                $result['status'] = 1;
                $result['message'] = $data['msg'];
                $result['code'] = $data['code'];
                $result['result'] = $data['result'];
            }else{//失败
                $result['status'] = 0;
                $result['message'] = $data['msg'];
                $result['code'] = $data['code'];
                $result['result'] = $data['result'];
            }
        }
        return $result;
    }
}
?>
