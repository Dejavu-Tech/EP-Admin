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
 * 顺丰同城配送接口
 */
namespace Lib\Localtown;
class Sfexpress
{
    //正式地址
    private $reqUrl = "https://commit-openic.sf-express.com";
    //顺丰同城开发者ID
    private $dev_id = "";
    //顺丰同城密钥
    private $dev_key = "";
    //顺丰同城店铺ID
    private $shop_id = "";

    /***********平台店铺信息************/
    //店铺名称
    private $shopname = "";
    //店铺电话
    private $shop_phone = "";
    //店铺地址
    private $shop_address = "";
    //店铺经度
    private $shop_lng = "";
    //店铺纬度
    private $shop_lat = "";
    //发单城市
    private $city_name = "";
    /**
     * 版本号
     */
    public $version = "17";

    //创建订单地址
    private $add_order_url = "/open/api/external/createorder?sign=";

    //取消订单地址
    private $cancel_order_url = "/open/api/external/cancelorder?sign=";

    private $query_delivery_url = "/open/api/external/precreateorder?sign=";

    private $notify_url = "";


    /**
     * 构造函数
     */
    public function __construct(){
        $localtown_sf_dev_id = D('Home/Front')->get_config_by_name('localtown_sf_dev_id');
        $localtown_sf_dev_key = D('Home/Front')->get_config_by_name('localtown_sf_dev_key');
        $localtown_sf_store_id = D('Home/Front')->get_config_by_name('localtown_sf_store_id');
        $localtown_shop_city_id = D('Home/Front')->get_config_by_name('localtown_shop_city_id');
        $localtown_shop_province_id = D('Home/Front')->get_config_by_name('localtown_shop_province_id');
        $localtown_shop_area_id = D('Home/Front')->get_config_by_name('localtown_shop_area_id');
        $localtown_shop_country_id = D('Home/Front')->get_config_by_name('localtown_shop_country_id');
        $localtown_shop_address = D('Home/Front')->get_config_by_name('localtown_shop_address');
        $localtown_shop_telephone = D('Home/Front')->get_config_by_name('localtown_shop_telephone');
        $localtown_shop_lon = D('Home/Front')->get_config_by_name('localtown_shop_lon');
        $localtown_shop_lat = D('Home/Front')->get_config_by_name('localtown_shop_lat');

        $this->dev_id = $localtown_sf_dev_id;
        $this->dev_key = $localtown_sf_dev_key;
        $this->shop_id = $localtown_sf_store_id;
        $this->city_name = $localtown_shop_city_id;
        $this->shop_phone = $localtown_shop_telephone;
        $this->shop_address = $localtown_shop_province_id.$localtown_shop_city_id.$localtown_shop_area_id.$localtown_shop_country_id.$localtown_shop_address;
        $this->shop_lng = $localtown_shop_lon;
        $this->shop_lat = $localtown_shop_lat;

        $shoname = D('Home/Front')->get_config_by_name('shoname');
        $this->shopname = $shoname;
        $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');
        $this->notify_url = $shop_domain.'delivery_notify.php';
    }

    /**
     * 新增配送单接口
     */
    public function addOrder($order_info){
        $time = time();
        $order_data = array();

        //商户地址
        if($order_info['store_id'] > 0){
            $this->shop_phone = $order_info['store_data']['shop_telephone'];
            $this->shop_address = $order_info['store_data']['address'];
            $this->shop_lng = $order_info['store_data']['shop_lon'];
            $this->shop_lat = $order_info['store_data']['shop_lat'];

            $this->city_name = $order_info['store_data']['city'];
        }

        //同城开发者ID
        $order_data['dev_id'] = $this->dev_id;
        //店铺ID
        $order_data['shop_id'] = $this->shop_id;
        //店铺ID类型 1：顺丰店铺ID；2：接入方店铺ID
        $order_data['shop_type'] = 1;
        //商家订单号 不允许重复
        $order_data['shop_order_id'] = $order_info['order_num_alias'];
        //订单接入来源 1：美团；2：饿了么；3：百度；4：口碑；其他请直接填写中文字符串值
        $order_data['order_source'] = mb_substr($this->shopname,0,12,'utf-8');
        //取货序号
        $order_data['order_sequence'] = "";
        //坐标类型 1：百度坐标，2：高德坐标
        $order_data['lbs_type'] = "1";
        //用户支付方式 1：已付款 0：货到付款
        $order_data['pay_type'] = "1";
        //代收金额 单位：分
        $order_data['receive_user_money'] = "0";
        //用户下单时间  秒级时间戳
        $order_data['order_time'] = $order_info['date_added'];
        //是否是预约单 0：非预约单；1：预约单
        $order_data['is_appoint'] = "0";
        //用户期望送达时间
        //$order_data['expect_time'] = "";
        //用户期望取件时间
        //$order_data['expect_pickup_time'] = "";
        //是否保价 0：非保价；1：保价
        $order_data['is_insured'] = "0";
        //是否是专人直送订单，0：否；1：是
        $order_data['is_person_direct'] = "0";
        //保价金额，单位：分
        $order_data['declared_value'] = "0";
        //订单小费，不传或者传0为不加小费
        $order_data['gratuity_fee'] = "0";
        //订单备注
        $order_data['remark'] = $order_info['note_content'];
        //物流流向 1：从门店取件送至用户；2：从用户取件送至门店
        $order_data['rider_pick_method'] = "1";
        //返回字段控制标志位（二进制）
        $order_data['return_flag'] = "511";
        //推单时间
        $order_data['push_time'] = $time;
        //版本号
        $order_data['version'] = $this->version;

        //收货人信息 begin
        $receive_array = [
            'user_name'         => $order_info['shipping_name'],//用户姓名
            'user_phone'        => $order_info['shipping_tel'],//用户电话
            'user_address'      => $order_info['shipping_address'],//用户地址
            'user_lng'          =>  $order_info['shipping_lng'],//用户经度
            'user_lat'          =>  $order_info['shipping_lat'],//用户纬度
            'city_name'         =>  $this->city_name,//发单城市
        ];
        $order_data['receive'] = $receive_array;
        //收货人信息 end

        //发货店铺信息
        $shop_array = [
            "shop_name"        =>  $this->shopname,//店铺名称
            "shop_phone"       =>  $this->shop_phone,//店铺电话
            "shop_address"     =>  $this->shop_address,//店铺地址
            "shop_lng"          =>   $this->shop_lng,//店铺经度
            "shop_lat"          =>  $this->shop_lat,//店铺纬度
        ];
        $order_data['shop'] = $shop_array;

        //订单详情 begin
        $order_detail_array = array();
        $order_detail_array['total_price'] = $order_info['order_total']*100;//用户订单总金额（单位：分）
        //物品类型 1:快餐，2:送药，3:百货
        //，4:脏衣服收，5:干净衣服派，6:生鲜，7:保单，8:高端饮品，9:现场勘验，10:快递，12:文件
        //，13:蛋糕，14:鲜花，15:电子数码，16:服装鞋帽，17:汽车配件，18:珠宝，20:披萨，21:中餐
        //，22:水产，27:专人直送，32:中端饮品，33:便利店，34:面包糕点，35:火锅，36:证照，99:其他
        $order_detail_array['product_type'] = 1;
        $order_detail_array['user_money'] = $order_info['order_total']*100;//实收用户金额（单位：分）
        $order_detail_array['shop_money'] = $order_info['order_total']*100;//实付商户金额（单位：分）
        $order_detail_array['weight_gram'] = $order_info['goods_weight'];//物品重量（单位：克）
        $order_detail_array['volume_litre'] = 0;//物品体积（单位：升）

        $order_detail_array['delivery_money'] = 0;//商户收取的配送费（单位：分）
        $order_detail_array['product_num'] = $order_info['goods_count'];//物品个数
        $order_detail_array['product_type_num'] = $order_info['goods_type_count'];//物品种类个数

        $product_detail = [];
        foreach($order_info['goods_list'] as $k=>$v){
            $goods_array = [];
            $goods_array['product_id'] = $v['goods_id'];//物品ID
            $goods_array['product_name'] = $v['goods_name'];//物品名称
            $goods_array['product_num'] = $v['quantity'];//物品数量
            $product_detail[] = $goods_array;
        }
        $order_detail_array['product_detail'] = $product_detail;//物品详情
        $order_data['order_detail'] = $order_detail_array;
        //订单详情 end
        ksort($order_data);
        $body = json_encode($order_data);
        $sign = $this->bulidRequestParams($body);
        $resp = $this->curl_post($body,$this->reqUrl.$this->add_order_url.$sign);
        $result = $this->parseResponseData($resp);
        return $result;
    }
    //查询订单运费接口
    public function queryDeliverFee($order_info){
        $time = time();
        $order_data = array();

        //商户地址
        if($order_info['store_id'] > 0){
            $this->shop_phone = $order_info['store_data']['shop_telephone'];
            $this->shop_address = $order_info['store_data']['address'];
            $this->shop_lng = $order_info['store_data']['shop_lon'];
            $this->shop_lat = $order_info['store_data']['shop_lat'];
            $this->city_name = $order_info['store_data']['city'];
        }

        //同城开发者ID
        $order_data['dev_id'] = $this->dev_id;
        //店铺ID
        $order_data['shop_id'] = $this->shop_id;
        //店铺ID类型 1：顺丰店铺ID；2：接入方店铺ID
        $order_data['shop_type'] = 1;


        //用户地址经度
        $order_data['user_lng'] = $order_info['shipping_lng'];
        //用户地址纬度
        $order_data['user_lat'] = $order_info['shipping_lat'];
        //用户详细地址
        $order_data['user_address'] = $order_info['shipping_address'];
        //发单城市
        $order_data['city_name'] = $this->city_name;
        //物品重量（单位：克）
        $order_data['weight'] = $order_info['goods_weight'];
        //物品类型
        $order_data['product_type'] = 1;
        //用户订单总金额（单位：分）
        $order_data['total_price'] = $order_info['order_total']*100;
        //是否是预约单
        $order_data['is_appoint'] = 0;
        /*//预约单类型
        $order_data['appoint_type'] = 0;
        //用户期望送达时间
        $order_data['expect_time'] = 0;
        //用户期望上门时间
        $order_data['expect_pickup_time'] = 0;*/
        //坐标类型，1：百度坐标，2：高德坐标
        $order_data['lbs_type'] = 1;
        //用户支付方式：1、已支付 0、货到付款
        $order_data['pay_type'] = 1;
        //代收金额
        $order_data['receive_user_money'] = 0;
        //是否保价，0：非保价；1：保价
        $order_data['is_insured'] = 0;
        //是否是专人直送订单，0：否；1：是
        $order_data['is_person_direct'] = 0;
        //配送交通工具，0：否；1：电动车；2：小轿车
        $order_data['vehicle'] = 0;
        //保价金额
        $order_data['declared_value'] = 0;
        //订单小费，不传或者传0为不加小费
        $order_data['gratuity_fee'] = 0;
        //物流流向
        $order_data['rider_pick_method'] = 1;
        //返回字段控制标志位（二进制）
        $order_data['return_flag'] = 511;
        //推单时间
        $order_data['push_time'] = time();
        //发货店铺信息
        $shop_array = [
            "shop_name"        =>  $this->shopname,//店铺名称
            "shop_phone"       =>  $this->shop_phone,//店铺电话
            "shop_address"     =>  $this->shop_address,//店铺地址
            "shop_lng"          =>   $this->shop_lng,//店铺经度
            "shop_lat"          =>  $this->shop_lat,//店铺纬度
        ];
        $order_data['shop'] = $shop_array;

        //订单详情 end
        ksort($order_data);
        $body = json_encode($order_data);
        $sign = $this->bulidRequestParams($body);
        $resp = $this->curl_post($body,$this->reqUrl.$this->query_delivery_url.$sign);
        $result = $this->parseResponseData($resp);
        return $result;
    }

    public function cancelOrder($orderdistribution_info,$cancel_reason){
        $order_data = array();
        //api开发者ID
        $order_data['dev_id'] = $this->dev_id;
        //订单ID
        $order_data['order_id'] = $orderdistribution_info['third_order_id'];
        //查询订单ID类型  1、顺丰订单号 2、商家订单号
        $order_data['order_type'] = 1;
        //取消原因
        $order_data['cancel_reason'] = $cancel_reason;
        //取消时间；秒级时间戳
        $order_data['push_time'] = time();
        //店铺ID order_type=2时必传shop_id与shop_type
        $order_data['shop_id'] = 0;
        //店铺ID类型 1、顺丰店铺ID 2、接入方店铺ID
        $order_data['shop_type'] = 1;
            //订单详情 end
        ksort($order_data);
        $body = json_encode($order_data);
        $sign = $this->bulidRequestParams($body);
        $resp = $this->curl_post($body,$this->reqUrl.$this->cancel_order_url.$sign);
        $result = $this->parseResponseData($resp);
        return $result;
    }

    function bulidRequestParams($post_json){
        $signChar  = $post_json . "&".$this->dev_id."&".$this->dev_key;
        $sign      = base64_encode(MD5($signChar));
        return $sign;
    }

    function curl_post($jsonData, $url) {
        $ch = curl_init();
        // json
        $headers = array(
            'Content-Type: application/json,charset=UTF-8',
        );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
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
            if($data['error_code'] == 0){//成功
                $result['status'] = 1;
                $result['message'] = $data['error_msg'];
                $result['code'] = $data['error_code'];
                $result['result'] = $data['result'];
            }else{//失败
                $result['status'] = 0;
                $result['message'] = $data['error_msg'];
                $result['code'] = $data['error_code'];
                $result['result'] = $data['error_data'];
            }
        }
        return $result;
    }
}
?>
