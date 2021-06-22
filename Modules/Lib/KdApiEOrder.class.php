<?php
namespace Lib;
class KdApiEOrder
{
    //正式地址
    private $reqUrl = "http://api.kdniao.com/api/Eorderservice";

    private $eBusinessID = "";
    private $appKey = "";

    public function __construct() {
        $ebuss_info = M('eaterplanet_ecommerce_config')->where( array('name' => 'kdniao_id') )->find();
        $exappkey = M('eaterplanet_ecommerce_config')->where( array('name' => 'kdniao_api_key') )->find();
        $this->eBusinessID = $ebuss_info['value'];
        $this->appKey = $exappkey['value'];
    }

    /**
     * @param $order_id 订单编号
     * @param $express_no   快递编号
     * @param $config_data   配置信息
     * @return array
     */
    public function printOrder($order_id,$express_no,$config_data){
        $kd_result = array();

        $status = 0;
        $order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->find();
        $express_info = M('eaterplanet_ecommerce_express')->where( array('simplecode' => $express_no) )->find();
        $kdn_info = M('eaterplanet_ecommerce_kdniao_list')->where( array('express_code' => $express_no) )->find();

        $customer_name = "";
        $customer_pwd = "";
        $month_code = "";
        $send_site = "";
        $send_staff = "";
        $template_size= "";
        $is_send_message = 0;
        $is_send_goods = 0;
        $sender_company = "";
        $sender_name = "";
        $sender_tel = "";
        $sender_mobile = "";
        $sender_postcode = "";
        $sender_address = "";
        $sender_province_name = "";
        $sender_city_name = "";
        $sender_area_name = "";
        if(!empty($kdn_info)){
            $customer_name = $kdn_info['customer_name'];
            $customer_pwd = $kdn_info['customer_pwd'];
            $month_code = $kdn_info['month_code'];
            $send_site = $kdn_info['send_site'];
            $send_staff = $kdn_info['send_staff'];
            $template_size= $kdn_info['template_size'];
            $is_send_message = $kdn_info['is_send_message'];
            $is_send_goods = $kdn_info['is_send_goods'];
            $sender_company = $kdn_info['sender_company'];
            $sender_name = $kdn_info['sender_name'];
            $sender_tel = $kdn_info['sender_tel'];
            $sender_mobile = $kdn_info['sender_mobile'];
            $sender_postcode = $kdn_info['sender_postcode'];
            $sender_address = $kdn_info['sender_address'];
            $sender_province_name = $kdn_info['sender_province_name'];
            $sender_city_name = $kdn_info['sender_city_name'];
            $sender_area_name = $kdn_info['sender_area_name'];
        }else{
            if(!empty($express_info)){
                $customer_name = $express_info['customer_name'];
                $customer_pwd = $express_info['customer_pwd'];
            }
            $sender_company = $config_data['kdn_sender_company'];
            $sender_name = $config_data['kdn_sender_name'];
            $sender_mobile = $config_data['kdn_sender_mobile'];
            $sender_postcode = $config_data['kdn_sender_postcode'];
            $sender_address = $config_data['kdn_sender_address'];
            $sender_province_name = $config_data['kdn_province_id'];
            $sender_city_name = $config_data['kdn_city_id'];
            $sender_area_name =  $config_data['kdn_area_id'];

            //圆通快递 TemplateSize字段传值18001，拿到最新的二联单模板发货使用。
            //这是100*180的模板，打印机必须设置为100*180规格，有边距要设置为0
            if($express_no == 'YTO'){
                $template_size = "18001";
            }
        }
        $customername_list = array('SURE','KYSY','PJ','CND','JTSD','DNWL','SNWL','ZTO','YD','HTKY','YTO','YCWL','UC','ANE','DBLKY',
                    'ANEKY','JDKY','LB','HTKYKY','ZTOKY','CNEX','YDKY','SX');
        $customerpwd_list = array('PJ','CND','JTSD','DNWL','SNWL','ZTO','YD','HTKY','UC','ANEKY'
                     ,'JDKY','LB','HTKYKY','ZTOKY','YDKY');
        $monthcode_list = array('KYSY','YTO');
        $sendsite_list = array('SURE','YCWL','ANE','ZTOKY','SX');
        $sendstaff_list = array('SURE','JDKY');
        $postcode_list = array('EMS','YZPY','YZBK');
        //判断客户账号
        if(in_array($express_no,$customername_list)){
            if(empty($customer_name)){
                $status = 2;
            }
        }
        //判断客户密码
        if(in_array($express_no,$customerpwd_list)){
            if(empty($customer_pwd)){
                $status = 2;
            }
        }
        //判断月结编码
        if(in_array($express_no,$monthcode_list)){
            if(empty($month_code)){
                $status = 2;
            }
        }
        //判断网点名称
        if(in_array($express_no,$sendsite_list)){
            if(empty($send_site)){
                $status = 2;
            }
        }
        //判断网点编码
        if(in_array($express_no,$sendstaff_list)){
            if(empty($send_staff)){
                $status = 2;
            }
        }
        //判断发件人信息
        if(empty($sender_name) || empty($sender_address) || empty($sender_province_name) || empty($sender_city_name)
            || empty($sender_area_name)){
            $status = 3;
        }
        if(empty($sender_mobile) && empty($sender_tel)){
            $status = 3;
        }
        if(in_array($express_no,$postcode_list)){
            if(empty($sender_postcode)){
                $status = 3;
            }
        }
        if($status == 0){
            if(!empty($order_info)){
                if($order_info['order_status_id'] == 1 && $order_info['delivery'] == 'express' && $order_info['is_kdn_print'] != 1){

                    //构造电子面单提交信息
                    $eorder = [];
                    $eorder["ShipperCode"] = $express_no;
                    $eorder["OrderCode"] = $order_info['order_num_alias'];
                    //邮费支付方式:1-现付，2-到付，3-月结，4-第三方支付(仅SF支持)
                    if(in_array($express_no,$monthcode_list)){
                        if(!empty($month_code)){
                            $eorder["PayType"] = 3;
                        }else{
                            $eorder["PayType"] = 1;
                        }
                    }else{
                        $eorder["PayType"] = 1;
                    }

                    //快递类型：1-标准快件
                    $eorder["ExpType"] = 1;
                    //部分快递必须使用
                    //客户账号
                    $eorder["CustomerName"] = $customer_name;
                    //客户密码
                    $eorder["CustomerPwd"] = $customer_pwd;
                    //网点名称
                    $eorder["SendSite"] = $send_site;
                    //网点编码
                    $eorder["SendStaff"] = $send_staff;
                    //月结编码
                    $eorder["MonthCode"] = $month_code;
                    //ERP系统、电商平台等系统或平台类型用户的客户ID或店铺账号等唯一性标识，用于区分其用户
                    $eorder["MemberID"] = "";
                    //发货仓编码
                    $eorder["WareHouseID"] = "";
                    //运输方式 1-陆运 2-空运 不填默认为1
                    $eorder["TransType"] = 1;
                    //快递单号(仅宅急送可用)
                    $eorder["LogisticCode"] = "";
                    //第三方订单号 (ShipperCode为JD且ExpType为1时必填)
                    $eorder["LogisticCode"] = "";
                    //是否要求签回单 1-要求 0-不要求
                    $eorder["IsReturnSignBill"] = 0;
                    //签回单操作要求(如：签名、盖章、身份证复印件等)
                    $eorder["OperateRequire"] = "";
                    //快递运费
                    $eorder["Cost"] = "";
                    //其他费用
                    $eorder["OtherCost"] = "";
                    //是否通知快递员上门揽件 0-通知 1-不通知 不填则默认为1
                    $eorder["IsNotice"] = "";
                    //返回电子面单模板：0-不需要；1-需要
                    $eorder["IsReturnPrintTemplate"] = 1;
                    //是否订阅短信：0-不需要；1-需要
                    $eorder["IsSendMessage"] = $is_send_message;
                    //包装类型(快运字段)默认为0； 0-纸 1-纤 2-木 3-托膜 4-木托 99-其他
                    $eorder["PackingType"] = "";
                    //送货方式(快运字段)默认为0； 0-自提 1-送货上门（不含上楼） 2-送货上楼
                    $eorder["DeliveryMethod"] = "";

                    $sender = [];
                    $sender["Name"] = $sender_name;
                    $sender["Tel"] = $sender_tel;
                    $sender["Mobile"] = $sender_mobile;
                    $sender["ProvinceName"] = $sender_province_name;
                    $sender["CityName"] = $sender_city_name;
                    $sender["ExpAreaName"] = $sender_area_name;
                    $sender["Address"] = $sender_address;
                    $sender["Company"] = $sender_company;
                    $sender["PostCode"] = $sender_postcode;

                    //收货人地址信息 begin
                    $address_id = $order_info['address_id'];
                    $address_info = M('eaterplanet_ecommerce_address')->where( "address_id=".$order_info['address_id'] )->find();
                    $shipping_cityname = D('Seller/Area')->get_area_info($address_info['city_id']);
                    $shipping_areaname = D('Seller/Area')->get_area_info($address_info['country_id']);
                    $area_info = M('eaterplanet_ecommerce_area')->where( array('id' => $address_info['country_id'] ) )->find();

                    $receiver = [];
                    $receiver["Name"] = $order_info['shipping_name'];
                    $receiver["Mobile"] = $order_info['shipping_tel'];
                    $receiver["ProvinceName"] = $order_info['shipping_name'];
                    $receiver["CityName"] = $shipping_cityname;
                    $receiver["ExpAreaName"] = $shipping_areaname;
                    $receiver["PostCode"] = $area_info['code'];
                    $receiver["Address"] = $order_info['shipping_address'];

                    //收货人地址信息 end

                    //商品信息 begin
                    $commodity = [];
                    $goods_list = M('eaterplanet_ecommerce_order_goods')->where( array('order_id' => $order_id) )->select();
                    $quantity_count = 0;
                    $customArea = "";
                    foreach($goods_list as $k=>$v){
                        $commodityOne = [];
                        $commodityOne["GoodsName"] = mb_substr($v['name'],0,16,'utf-8');
                        $commodityOne["Goodsquantity"] = $v['quantity'];
                        $quantity_count = $quantity_count + $v['quantity'];
                        $customArea = $customArea . $v['name']." ".$v['quantity'].",";
                        $commodity[] = $commodityOne;
                    }
                    //商品信息 end

                    $eorder["Sender"] = $sender;
                    $eorder["Receiver"] = $receiver;
                    $eorder["Commodity"] = $commodity;
                    $eorder["Quantity"] = $quantity_count;
                    if($is_send_goods == 1){
                        $eorder['CustomArea'] = $customArea;
                    }
                    //调用电子面单
                    $jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);

                    //$jsonParam = JSON($eorder);//兼容php5.2（含）以下
                    //echo "电子面单接口提交内容：<br/>".$jsonParam;
                    //error_log($jsonParam,3,'error.log');
                    $jsonResult = $this->submitEOrder($jsonParam);
                    //echo "<br/><br/>电子面单提交结果:<br/>".$jsonResult;
                    //解析电子面单返回结果
                    $result = json_decode($jsonResult, true);
                    $kd_result['jsonResult'] = $jsonResult;
                    if($result["ResultCode"] == "100" && $result['Success']) {
                        //快递单号
                        $shipping_no = $result["Order"]['LogisticCode'];
                        //快递公司编码
                        $shipperCode = $result["Order"]['ShipperCode'];
                        //面单打印模板内容（html格式）
                        $PrintTemplate = $result['PrintTemplate'];
                        //打印结果
                        $Reason = $result['Reason'];
                        //用户ID
                        $EBusinessID = $result['EBusinessID'];
                        //订单编号
                        $OrderCode = $result["Order"]['OrderCode'];
                        //快递鸟编号
                        $KDNOrderCode = $result["Order"]['KDNOrderCode'];
                        //唯一标识
                        $UniquerRequestNumber = $result["UniquerRequestNumber"];
                        //打印结果编码
                        $ResultCode = $result["ResultCode"];
                        $kd_result['status'] = 0;
                        //打印面单数据获取成功，更新订单打印状态，快递公司
                        M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save(array('is_kdn_print'=>1,'shipping_no'=>$shipping_no));
                        //保存打印结果信息
                        $kdn_result_data = array();
                        $kdn_result_data['order_id'] = $order_id;
                        $kdn_result_data['order_code'] = $OrderCode;
                        $kdn_result_data['e_business_id'] = $EBusinessID;
                        $kdn_result_data['result_code'] = $ResultCode;
                        $kdn_result_data['status'] = 1;
                        $kdn_result_data['print_template'] = $PrintTemplate;
                        $kdn_result_data['reason'] = $Reason;
                        $kdn_result_data['uniquerRequestNumber'] = $UniquerRequestNumber;
                        $kdn_result_data['kdnOrderCode'] = $KDNOrderCode;

                        $kdn_info = M('eaterplanet_ecommerce_order_kdniao_info')->where( array('order_id' => $order_id,'status'=>0) )->find();
                        if(empty($kdn_info)){
                            M('eaterplanet_ecommerce_order_kdniao_info')->add($kdn_result_data);
                        }else{//有失败记录更新
                            M('eaterplanet_ecommerce_order_kdniao_info')->where( array('id' => $kdn_info['id']) )->save($kdn_result_data);
                        }
                        //更新订单快递信息
                        $express_info = M('eaterplanet_ecommerce_express')->where( array('simplecode' => $express_no ) )->find();
                        $time = time();
                        $data = array(
                            'shipping_method' => $express_info['id'],
                            'shipping_no' => $shipping_no,
                            'dispatchname' => $express_info['name'],
                            'express_time' => $time
                        );
                        M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save( $data );

                    }else {
                        //打印失败 更新订单 打印状态
                        M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id) )->save(array('is_kdn_print'=>2));
                        $kd_result['status'] = 1;

                        //快递公司编码
                        $shipperCode = $express_no;
                        //打印结果
                        $Reason = $result['Reason'];
                        //用户ID
                        $EBusinessID = $result['EBusinessID'];
                        //打印结果编码
                        $ResultCode = $result["ResultCode"];
                        //订单编号
                        $OrderCode = $order_info['order_num_alias'];
                        //唯一标识
                        $UniquerRequestNumber = $result["UniquerRequestNumber"];

                        //保存打印结果信息
                        $kdn_result_data = array();
                        $kdn_result_data['order_id'] = $order_id;
                        $kdn_result_data['order_code'] = $OrderCode;
                        $kdn_result_data['e_business_id'] = $EBusinessID;
                        $kdn_result_data['result_code'] = $ResultCode;
                        $kdn_result_data['status'] = 0;
                        $kdn_result_data['print_template'] = "";
                        $kdn_result_data['reason'] = $Reason;
                        $kdn_result_data['uniquerRequestNumber'] = $UniquerRequestNumber;

                        $kdn_info = M('eaterplanet_ecommerce_order_kdniao_info')->where( array('order_id' => $order_id,'status'=>0) )->find();
                        if(empty($kdn_info)){
                            M('eaterplanet_ecommerce_order_kdniao_info')->add($kdn_result_data);
                        }else{//有失败记录更新
                            M('eaterplanet_ecommerce_order_kdniao_info')->where( array('id' => $kdn_info['id']) )->save($kdn_result_data);
                        }
                    }
                    $kd_result['code'] = $result["ResultCode"];
                    $kd_result['message'] = $result["Reason"];
                }else{
                    $kd_result['status'] = 1;
                    $kd_result['message'] = "待发货未打印快递订单才能打印面单";
                }
            }else{
                $kd_result['status'] = 1;
                $kd_result['message'] = "订单不存在";
            }
        }else{
            if($status == 2){
                $kd_result['message'] = "快递信息未配置";
            }else if($status == 3){
                $kd_result['message'] = "发件人信息未配置";
            }
            $kd_result['status'] = $status;
        }
        $kd_result['order_id'] = $order_id;
        return $kd_result;
    }

    /**
     * Json方式 调用电子面单接口
     */
    function submitEOrder($requestData){
        $datas = array(
            'EBusinessID' => $this->eBusinessID,
            'RequestType' => '1007',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->appKey);
        $result = $this->sendPost($this->reqUrl, $datas);
        //根据公司业务处理返回的信息......

        return $result;
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
    /**************************************************************
     *
     *  使用特定function对数组中所有元素做处理
     *  @param  string  &$array     要处理的字符串
     *  @param  string  $function   要执行的函数
     *  @return boolean $apply_to_keys_also     是否也应用到key上
     *  @access public
     *
     *************************************************************/
    function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }


    /**************************************************************
     *
     *  将数组转换为JSON字符串（兼容中文）
     *  @param  array   $array      要转换的数组
     *  @return string      转换得到的json字符串
     *  @access public
     *
     *************************************************************/
    public function JSON($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
}
?>
