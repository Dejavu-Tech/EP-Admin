<?php

/**
 * 保存用户行为，前台用户和后台用户
 * $type C('FRONTEND_USER')/C('BACKEND_USER')
 */
function storage_user_action($uid,$name,$type,$info){
	$data['type']=$type;
	$data['user_id']=$uid;
	$data['uname']=$name;
	$data['add_time']=date('Y-m-d H:i:s',time());
	$data['info']=$info;
	M('user_action')->add($data);
}

/**
 * 导出EXCEL
 * @param unknown $expTitle       标题
 * @param unknown $expCellName    字段名称
 * @param unknown $expTableData   字段内容
 */

function export_excel($expTitle,$expCellName,$expTableData)
{
    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
    $fileName = $xlsTitle;//or $xlsTitle 文件名称可根据自己情况设定
    $cellNum = count($expCellName);
    $dataNum = count($expTableData);

    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

    $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
    for($i=0;$i<$cellNum;$i++){
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
    }
    for($i=0;$i<$dataNum;$i++){
        for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
        }
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
    header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;

}
/**
 * 导入Excel
 * @param unknown $file
 */
function importExecl($file){
    // 判断文件是什么格式
    $type = pathinfo($file);
    $type = strtolower($type["extension"]);
    $type=$type==='csv' ? $type : 'Excel5';
    ini_set('max_execution_time', '0');
    vendor("PHPExcel.PHPExcel");
    // 判断使用哪种格式
    $objReader = \PHPExcel_IOFactory::createReader($type);
    $objPHPExcel = $objReader->load($file);
    $sheet = $objPHPExcel->getSheet(0);
    // 取得总行数
    $highestRow = $sheet->getHighestRow();
    // 取得总列数
    $highestColumn = $sheet->getHighestColumn();
    //循环读取excel文件,读取一条,插入一条
    $data=array();
    //从第一行开始读取数据
    for($j=1;$j<=$highestRow;$j++){
        if($j <=2) {
            continue;
        }
        //从A列读取数据
        for($k='A';$k<=$highestColumn;$k++){
            // 读取单元格
            $data[$j][]=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
        }
    }
    return $data;
}

function http_request($url,$data = null,$headers=array())
{
	$curl = curl_init();
	if( count($headers) >= 1 ){
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	}
	curl_setopt($curl, CURLOPT_URL, $url);

	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

	if (!empty($data)){
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($curl);
	curl_close($curl);
	return $output;
}
function get_qrcode_text_png($data)
{
	vendor("phpqrcode.phpqrcode");
	\QRcode::png($data);
}
function get_qrcode_link($data,$file_name)
{
	$image_dir=ROOT_PATH.'Uploads/image/qrcode/'.date('Y-m-d');
	RecursiveMkdir($image_dir);

	vendor("phpqrcode.phpqrcode");

	// 纠错级别：L、M、Q、H
	$level = 'L';
	// 点的大小：1到10,用于手机端4就可以了
	$size = 4;
	// 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
	//$path = "images/";
	// 生成的文件名

	$fileName = $image_dir.'/'.$file_name;
	\QRcode::png($data, $fileName, $level, $size);

	return 'Uploads/image/qrcode/'.date('Y-m-d').'/'.$file_name;

}


function get_compare_qrcode($content,$target,$tmp='',$tmpy='')
{

	$year = date('Y');
	$day = date('md');
	$n = time().rand(1,9999).'qr.png';
	$type='team';




	//begin
	$image = 'Uploads/image/qrcode/'.date('Y-m-d');

	$path=ROOT_PATH.$image;
	RecursiveMkdir($path);

	vendor("phpqrcode.phpqrcode");

	$qr_image = $path .'/'.$n;
	$level = 'L';
	$size = 6;
	$rs =\QRcode::png($content, $qr_image, $level, $size);

	$new_image = $image."/" .md5($n).'.png';

	$new_file = ROOT_PATH . '/'.$new_image;
	$img = $qr_image;

	//背景图片
	$target_img = imagecreatefromstring(file_get_contents($target));
	$source= array();
	$source['source'] = imagecreatefromstring(file_get_contents($img));


	$source['size'] = getimagesize($img);

	$num1=0;
	//337 501

	$tmp= empty($tmp) ?  461 : $tmp;
	$tmpy=empty($tmpy) ?  763 : $tmpy; //图片之间的间距

	imagecopy($target_img,$source['source'],$tmp,$tmpy,0,0,$source['size'][0],$source['size'][1]);
		//$black = imagecolorallocate($target_img, 0x00, 0x00, 0x00);
		//imagefttext($target_img, 14, 0, 320-(strlen($name)/4 * 14), 675, $black, './simhei.ttf', $name);

	Imagejpeg($target_img,$new_file);

	return $new_image;
}

//记录访问ip
function visitors_ip(){

	if(!isset($_SESSION[C('SESSION_PREFIX')]['visitors_ip'])){

		$ip=get_client_ip();

		$taobao_ip=new \Lib\Taobaoip();
		$region=$taobao_ip->getLocation($ip);
		//首次访问
		if(!M('visitors_ip')->where(array('ip'=>$ip))->find()){
			$ip_data['first_visit_time']=date('Y-m-d H:i:s',time());
		}

		$ip_data['province']=$region['region'];
		$ip_data['city']=$region['city'];
		$ip_data['ip']=$ip;
		$ip_data['last_visit_time']=date('Y-m-d',time());
		$ip_data['add_time']=date('Y-m-d H:i:s',time());
		$ip_data['user_agent']=$_SERVER['HTTP_USER_AGENT'];
		if(M('visitors_ip')->add($ip_data)){
			session('visitors_ip',$ip);
		}
	}

}

//生成唯一订单号
function build_order_no(){
    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

//取得url中加密的id
function get_url_id($id){
	$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	$get_id=$hashids->decode(I($id));
	return $get_id[0];
}

//付款时生成的token
function pay_token($key_name){
	$key='eaterplanet'.rand(100000, 999999);
	$token=md5($key);
	session($key_name,$key);
	return $token;
}

//取得支付方式名称
function get_payment_name($code){
	if (!$payment_list = S('payment_list')) {

		$list=M('payment')->select();

		foreach ($list as $k => $v) {
			$payment[$v['payment_code']]=$v;
		}
		S('payment_list', $payment);
		$payment_list=$payment;
	}
	return $payment_list[$code]['payment_name'];
}
//取得货运方式名称
function get_goods_category_name($id){
	if (!$goods_category = S('goods_category')) {

		$list=M('goods_category')->select();

		foreach ($list as $k => $v) {
			$category[$v['id']]=$v;
		}
		S('goods_category', $category);
		$goods_category=$category;
	}
	return $goods_category[$id]['name'];
}

function get_shipping_name($id)
{
	$express_info = M('seller_express')->where( array('id' => $id) )->find();
	return $express_info['express_name'];
}

//取得货运方式名称
function get_shipping_name2($id){
	if (!$shipping_list = S('shipping_list')) {

		$list=M('transport')->select();

		foreach ($list as $k => $v) {
			$shipping[$v['id']]=$v;
		}
		S('shipping_list', $shipping);
		$shipping_list=$shipping;
	}
	return $shipping_list[$id]['title'];
}
//取得支付宝方式配置信息
function get_payment_config($code){

	$list=M('payment')->where(array('payment_code'=>$code))->find();

	if(is_array($list) && !empty($list)){
		$config=unserialize($list['payment_config']);
	}

	return $config;
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string  $name 格式 [模块名]/接口名/方法名
 * @param  array|string  $vars 参数
 */
function api($name,$vars=array()){
    $array     = explode('/',$name);
    $method    = array_pop($array);
    $classname = array_pop($array);
    $module    = $array? array_pop($array) : 'Common';
    $callback  = $module.'\\Api\\'.$classname.'Api::'.$method;
    if(is_string($vars)) {
        parse_str($vars,$vars);
    }
    return call_user_func_array($callback,$vars);
}


/**
 * 2015-11-06
 * 系统邮件发送函数
 * @param string $to    接收邮件者邮箱
 * @param string $name  接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null){

    $mail = new \Lib\PHPMailer\Phpmailer();

    $mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();  // 设定使用SMTP服务
    $mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能
                                               // 1 = errors and messages
                                               // 2 = messages only
    $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
  //  $mail->SMTPSecure = 'ssl';                 // 使用安全协议
    $mail->Host       = C('SMTP_HOST');  // SMTP 服务器
    $mail->Port       = C('SMTP_PORT');  // SMTP服务器的端口号
    $mail->Username   = C('SMTP_USER');  // SMTP服务器用户名
    $mail->Password   = C('SMTP_PASS');  // SMTP服务器密码
    $mail->SetFrom(C('FROM_EMAIL'), C('FROM_NAME'));
    $replyEmail       = C('REPLY_EMAIL')?C('REPLY_EMAIL'):C('FROM_EMAIL');
    $replyName        = C('REPLY_NAME')?C('REPLY_NAME'):C('FROM_NAME');
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject    = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $name);
    if(is_array($attachment)){ // 添加附件
        foreach ($attachment as $file){
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}
//通过id取重量的名称
function get_weight_name($weight_id){
	if (!$weight_list = S('weight_list')) {

		$list=M('weight_class')->select();

		foreach ($list as $k => $v) {
			$weight[$v['weight_class_id']]=$v;
		}
		S('weight_list', $weight);

		$weight_list=$weight;
	}
	return $weight_list[$weight_id]['title'];
}
//取得重量信息列表
function get_weight_list(){
	if (!$weight = S('weight')) {

		$list=M('weight_class')->select();

		S('weight', $list);

		$weight=$list;
	}
	return $weight;
}

//通过id取长度的名称
function get_length_name($length_id){
	if (!$length_list = S('length_list')) {

		$list=M('length_class')->select();

		foreach ($list as $k => $v) {
			$length[$v['length_class_id']]=$v;
		}
		S('length_list', $length);

		$length_list=$length;
	}
	return $length_list[$length_id]['title'];
}
//取得长度信息列表
function get_length_list(){
	if (!$length_list = S('length')) {

		$list=M('length_class')->select();

		S('length', $list);

		$length_list=$list;
	}
	return $length_list;
}

//通过id取得订单状态名称
function get_order_status_name($order_status_id){
	if (!$order_status = S('order_status_list')) {

		$list=M('order_status')->select();

		foreach ($list as $k => $v) {
			$o_status[$v['order_status_id']]=$v;
		}
		S('order_status_list', $o_status);

		$order_status=$o_status;
	}
	return $order_status[$order_status_id]['name'];
}
//取得订单状态信息列表
function get_order_status_list(){
	if (!$order_status = S('order_status')) {

		$status=M('order_status')->select();

		S('order_status', $status);

		$order_status=$status;
	}
	return $order_status;
}
//通过地区的id取地区的名称
function get_area_name($area_id){

	if (!$area_list = S('area_list')) {

		$list=M('Area')->field('area_id,area_name')->select();

		foreach ($list as $k => $v) {
			$area[$v['area_id']]=$v;
		}
		S('area_list', $area);

		$area_list=$area;
	}
	return $area_list[$area_id]['area_name'];

}

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

//字符串截取
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=false)
{
    if(function_exists("mb_substr")){
        if($suffix)
             return mb_substr($str, $start, $length, $charset)."…";
        else
             return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
        if($suffix)
             return iconv_substr($str,$start,$length,$charset)."…";
        else
             return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login(){
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_seller_login(){
    $user = session('seller_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('seller_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}
/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
    //数据类型检测
    if(!is_array($data)){
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}
/**
时钟群发模板消息
**/
function template_msg_cron()
{
	$template_msg_order = M('template_msg_order')->where( array('state' => 0) )->order('addtime asc')->limit(20)->select();

	if(!empty($template_msg_order))
	{
		foreach($template_msg_order as $msg_order)
		{
			$template_data = unserialize($msg_order['template_data']);
			if($msg_order['type'] == 0)
			{
				send_template_msg($template_data,$msg_order['url'],$msg_order['open_id'],$msg_order['template_id']);
			} else if($msg_order['type'] == 1)
			{
				notify_weixin_msg($msg_order['open_id'],$template_data['descript'],$template_data['title'],$msg_order['url'],$template_data['image']);
			}
			M('template_msg_order')->where( array('id' => $msg_order['id']) )->save( array('state' => 1) );
		}
	}

}

/**
	发送客服消息
**/

/**'
 * 发送模板消息
 * @param unknown $template_data
 * @param unknown $url
 * @param unknown $to_openid
 * @param unknown $template_id
 * @return mixed
 */
function send_template_msg($template_data,$url,$to_openid,$template_id)
{
    $appid_info 	=  M('config')->where( array('name' => 'APPID') )->find();
    $appsecret_info =  M('config')->where( array('name' => 'APPSECRET') )->find();
    $mchid_info =  M('config')->where( array('name' => 'MCHID') )->find();

    $weixin_config = array();
    $weixin_config['appid'] = $appid_info['value'];
    $weixin_config['appscert'] = $appsecret_info['value'];
    $weixin_config['mchid'] = $mchid_info['value'];

    $jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);
    $re_access_token = $jssdk->getAccessToken();

    $template = array(
        'touser' => $to_openid,
        'template_id' => $template_id,
        'url' => $url,
        'topcolor' => '#FF0000',
        'data' => $template_data
    );
    $send_url ="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$re_access_token}";
    $result = sendhttps_post($send_url, json_encode($template));

    return json_decode($result,true);
}

function notify_weixin_msg($to_openid,$msg,$title='消息提醒',$url='',$image='')
{
	$appid_info 	=  M('config')->where( array('name' => 'APPID') )->find();
	$appsecret_info =  M('config')->where( array('name' => 'APPSECRET') )->find();
	$mchid_info =  M('config')->where( array('name' => 'MCHID') )->find();

	$weixin_config = array();
	$weixin_config['appid'] = $appid_info['value'];
	$weixin_config['appscert'] = $appsecret_info['value'];
	$weixin_config['mchid'] = $mchid_info['value'];

	$jssdk = new \Lib\Weixin\Jssdk( $weixin_config['appid'], $weixin_config['appscert']);

	$re_access_token = $jssdk->getAccessToken();

	$openId = $to_openid;

	$txt ='{
			"touser":"'.$openId.'",
			"msgtype":"news",
			"news":{
				"articles": [
				 {
					 "title":"'.$title.'",
					 "description":"'.$msg.'",
					 "url":"'.$url.'",
					 "picurl":"'.$image.'"
				 }
				 ]
			}
		}';

	$url ='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$re_access_token;
	$result = sendhttps_post($url,$txt);
	return true;
}

function sendhttp_get($url)
{

	$curl = curl_init();
	curl_setopt($curl,CURLOPT_URL,$url);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
	curl_setopt($curl,CURLOPT_POST,1);
	curl_setopt($curl,CURLOPT_POSTFIELDS,array());
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
	$result = curl_exec($curl);
	curl_close($curl);
	return $result;
}


function sendhttps_post($url,$data)
{
	$curl = curl_init();
	curl_setopt($curl,CURLOPT_URL,$url);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
	curl_setopt($curl,CURLOPT_POST,1);
	curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
	$result = curl_exec($curl);
	if(curl_errno($curl)){
	  return 'Errno'.curl_error($curl);
	}
	curl_close($curl);
	return $result;
}


/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL,$format='Y-m-d H:i'){
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

/**
 * 清空缓存
 */
function clear_cache() {
    $dirs = array();
    $noneed_clear = array(".", "..");
    $rootdirs = array_diff(scandir(RUNTIME_PATH), $noneed_clear);
    foreach ($rootdirs as $dir) {
        if ($dir != "." && $dir != "..") {
            $dir = RUNTIME_PATH . $dir;
            if (is_dir($dir)) {
                array_push($dirs, $dir);
                $tmprootdirs = scandir($dir);
                foreach ($tmprootdirs as $tdir) {
                    if ($tdir != "." && $tdir != "..") {
                        $tdir = $dir . '/' . $tdir;
                        if (is_dir($tdir)) {
                            array_push($dirs, $tdir);
                        }
                    }
                }
            }
        }
    }
    $dirtool = new \Lib\Dir();
    foreach ($dirs as $dir) {
        $dirtool->del($dir);
    }
}
/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = 'children', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 (单位:秒)
 * @return string
 */
function think_ucenter_encrypt($data, $key, $expire = 0) {
	$key  = md5($key);
	$data = base64_encode($data);
	$x    = 0;
	$len  = strlen($data);
	$l    = strlen($key);
	$char =  '';
	for ($i = 0; $i < $len; $i++) {
		if ($x == $l) $x=0;
		$char  .= substr($key, $x, 1);
		$x++;
	}
	$str = sprintf('%010d', $expire ? $expire + time() : 0);
	for ($i = 0; $i < $len; $i++) {
		$str .= chr(ord(substr($data,$i,1)) + (ord(substr($char,$i,1)))%256);
	}
	return str_replace('=', '', base64_encode($str));
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string
 */
function think_ucenter_decrypt($data, $key){
	$key    = md5($key);
	$x      = 0;
	$data   = base64_decode($data);
	$expire = substr($data, 0, 10);
	$data   = substr($data, 10);
	if($expire > 0 && $expire < time()) {
		return '';
	}
	$len  = strlen($data);
	$l    = strlen($key);
	$char = $str = '';
	for ($i = 0; $i < $len; $i++) {
		if ($x == $l) $x = 0;
		$char  .= substr($key, $x, 1);
		$x++;
	}
	for ($i = 0; $i < $len; $i++) {
		if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
			$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
		}else{
			$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
		}
	}
	return base64_decode($str);
}
//数字转ip
function ntoip($n)
{
    $iphex=dechex($n);//将10进制数字转换成16进制
    $len=strlen($iphex);//得到16进制字符串的长度
    if(strlen($iphex)<8)
    {
        $iphex='0'.$iphex;//如果长度小于8，在最前面加0
        $len=strlen($iphex); //重新得到16进制字符串的长度
    }
    //这是因为ipton函数得到的16进制字符串，如果第一位为0，在转换成数字后，是不会显示的
    //所以，如果长度小于8，肯定要把第一位的0加上去
    //为什么一定是第一位的0呢，因为在ipton函数中，后面各段加的'0'都在中间，转换成数字后，不会消失
    for($i=0,$j=0;$j<$len;$i=$i+1,$j=$j+2)
    {//循环截取16进制字符串，每次截取2个长度
        $ippart=substr($iphex,$j,2);//得到每段IP所对应的16进制数
        $fipart=substr($ippart,0,1);//截取16进制数的第一位
        if($fipart=='0')
        {//如果第一位为0，说明原数只有1位
            $ippart=substr($ippart,1,1);//将0截取掉
        }
        $ip[]=hexdec($ippart);//将每段16进制数转换成对应的10进制数，即IP各段的值
    }
    $ip = array_reverse($ip);

    return implode('.', $ip);//连接各段，返回原IP值
}
//显示时间
function toDate($time, $format = 'Y-m-d H:i:s') {
	if (empty($time)){
		return '无';
	}
	$format = str_replace ( '#', ':', $format );
	return date ($format, $time );
}

//验证字符串长度
function checkLength($str,$min,$max){

	preg_match_all("/./u",$str, $matches);

	$len=count($matches[0]);

	if($len<$min || $len>$max){
		return false;
	}else{
		return true;
	}

}

//字符串长度计算
function utf8_strlen($string) {
	return strlen(utf8_decode($string));
}

function utf8_strrpos($string, $needle, $offset = null) {
	if (is_null($offset)) {
		$data = explode($needle, $string);

		if (count($data) > 1) {
			array_pop($data);

			$string = join($needle, $data);

			return utf8_strlen($string);
		}

		return false;
	} else {
		if (!is_int($offset)) {
			trigger_error('utf8_strrpos expects parameter 3 to be long', E_USER_WARNING);

			return false;
		}

		$string = utf8_substr($string, $offset);

		if (false !== ($position = utf8_strrpos($string, $needle))) {
			return $position + $offset;
		}

		return false;
	}
}
//字符串截取
function utf8_substr($string, $offset, $length = null) {
	// generates E_NOTICE
	// for PHP4 objects, but not PHP5 objects
	$string = (string)$string;
	$offset = (int)$offset;

	if (!is_null($length)) {
		$length = (int)$length;
	}

	// handle trivial cases
	if ($length === 0) {
		return '';
	}

	if ($offset < 0 && $length < 0 && $length < $offset) {
		return '';
	}

	// normalise negative offsets (we could use a tail
	// anchored pattern, but they are horribly slow!)
	if ($offset < 0) {
		$strlen = strlen(utf8_decode($string));
		$offset = $strlen + $offset;

		if ($offset < 0) {
			$offset = 0;
		}
	}

	$Op = '';
	$Lp = '';

	// establish a pattern for offset, a
	// non-captured group equal in length to offset
	if ($offset > 0) {
		$Ox = (int)($offset / 65535);
		$Oy = $offset%65535;

		if ($Ox) {
			$Op = '(?:.{65535}){' . $Ox . '}';
		}

		$Op = '^(?:' . $Op . '.{' . $Oy . '})';
	} else {
		$Op = '^';
	}

	// establish a pattern for length
	if (is_null($length)) {
		$Lp = '(.*)$';
	} else {
		if (!isset($strlen)) {
			$strlen = strlen(utf8_decode($string));
		}

		// another trivial case
		if ($offset > $strlen) {
			return '';
		}

		if ($length > 0) {
			$length = min($strlen - $offset, $length);

			$Lx = (int)($length / 65535);
			$Ly = $length % 65535;

			// negative length requires a captured group
			// of length characters
			if ($Lx) {
				$Lp = '(?:.{65535}){' . $Lx . '}';
			}

			$Lp = '(' . $Lp . '.{' . $Ly . '})';
		} elseif ($length < 0) {
			if ($length < ($offset - $strlen)) {
				return '';
			}

			$Lx = (int)((-$length) / 65535);
			$Ly = (-$length)%65535;

			// negative length requires ... capture everything
			// except a group of  -length characters
			// anchored at the tail-end of the string
			if ($Lx) {
				$Lp = '(?:.{65535}){' . $Lx . '}';
			}

			$Lp = '(.*)(?:' . $Lp . '.{' . $Ly . '})$';
		}
	}

	if (!preg_match( '#' . $Op . $Lp . '#us', $string, $match)) {
		return '';
	}

	return $match[1];

}
/**
 * 递归生成目录
 */
function RecursiveMkdir($path) {
	if (!file_exists($path)) {
		RecursiveMkdir(dirname($path));
		@mkdir($path, 0777);
	}
}

/**
 * 自动生成新尺寸 的图片
 */
function resize($filename, $width, $height) {

	$image_dir=ROOT_PATH.'Uploads/image/';

	if (!is_file($image_dir . $filename)) {
		return;
	}

	$extension = pathinfo($filename, PATHINFO_EXTENSION);

	$old_image = $filename;
	$new_image = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

	if (!is_file($image_dir . $new_image) || (filectime($image_dir . $old_image) > filectime($image_dir . $new_image))) {
		$path = '';

		$directories = explode('/', dirname(str_replace('../', '', $new_image)));

		foreach ($directories as $directory) {
			$path = $path . '/' . $directory;

			if (!is_dir($image_dir . $path)) {
				@mkdir($image_dir . $path, 0777);
			}
		}

		list($width_orig, $height_orig) = getimagesize($image_dir . $old_image);

		if ($width_orig != $width || $height_orig != $height) {
			$image = new \Lib\Image($image_dir . $old_image);
			$image->resize($width, $height);
			$image->save($image_dir . $new_image);
		} else {
			copy($image_dir . $old_image, $image_dir . $new_image);
		}
	}

	return 'Uploads/image/' . $new_image;

	}
?>
