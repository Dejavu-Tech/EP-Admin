<?php
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Original Author <author@example.com>                        |
// |          Your Name <you@example.com>                                 |
// +----------------------------------------------------------------------+
//
// $Id:$

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use OSS\OssClient;
use OSS\Core\OssException;
function ihttp_get($url) {
    return ihttp_request($url);
}
function strexists($string, $find) {
    return !(strpos($string, $find) === FALSE);
}
function all_qiniu() {
    include_once ROOT_PATH . 'Modules/Lib/Qiniu/autoload.php';
    //qiniu_accesskey
    $accessKey_arr = M('eaterplanet_ecommerce_config')->where(array(
        'name' => 'qiniu_accesskey'
    ))->find();
    $secretKey_arr = M('eaterplanet_ecommerce_config')->where(array(
        'name' => 'qiniu_secretkey'
    ))->find();
    $bucket_arr = M('eaterplanet_ecommerce_config')->where(array(
        'name' => 'qiniu_bucket'
    ))->find();
    $accessKey = $accessKey_arr['value'];
    $secretKey = $secretKey_arr['value'];
    $bucket = $bucket_arr['value'];
    $auth = new Auth($accessKey, $secretKey);
    $bucketManager = new BucketManager($auth);
    $lat_market = '';
    for ($i = 1; $i < 5; $i++) {
        // 要列取文件的公共前缀
        $prefix = '';
        // 上次列举返回的位置标记，作为本次列举的起点信息。
        $marker = $lat_market;
        // 本次列举的条目数
        $limit = 1000;
        $delimiter = '';
        // 列举文件
        list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
        $keys = array();
        $keyPairs = array();
        foreach ($ret['items'] as $vv) {
            $keys[] = $vv['key'];
            $keyPairs[$vv['key']] = 'Uploads/image/' . $vv['key'];
        }
        $ops = $bucketManager->buildBatchCopy($bucket, $keyPairs, $bucket, true);
        list($ret, $err) = $bucketManager->batch($ops);
        $lat_market = $ret['marker'];
        echo '<br/><br/>===' . $i;
    }
}
if (!function_exists('array_column')) {
    function array_column($input, $columnKey, $indexKey = NULL) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
        $indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
        $result = array();
        foreach ((array)$input AS $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : NULL;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }
}
function ver_compare($version1, $version2) {
    $version1 = str_replace('.', '', $version1);
    $version2 = str_replace('.', '', $version2);
    $oldLength = istrlen($version1);
    $newLength = istrlen($version2);
    if (is_numeric($version1) && is_numeric($version2)) {
        if ($oldLength > $newLength) {
            $version2.= str_repeat('0', $oldLength - $newLength);
        }
        if ($newLength > $oldLength) {
            $version1.= str_repeat('0', $newLength - $oldLength);
        }
        $version1 = intval($version1);
        $version2 = intval($version2);
    }
    return version_compare($version1, $version2);
}
function tpl_form_field_video($name, $value = '', $options = array()) {
    if (!is_array($options)) {
        $options = array();
    }
    if (!is_array($options)) {
        $options = array();
    }
    $options['direct'] = true;
    $options['multi'] = false;
    $options['type'] = 'video';
    $options['fileSizeLimit'] = intval($GLOBALS['_W']['setting']['upload']['audio']['limit']) * 1024;
    $s = '';
    if (!defined('TPL_INIT_VIDEO')) {
        $s = '

<script type="text/javascript">

	function showVideoDialog(elm, options) {

		require(["util"], function(util){

			var btn = $(elm);

			var ipt = btn.parent().prev();

			var val = ipt.val();

			util.audio(val, function(url){

				if(url && url.attachment && url.url){

					btn.prev().show();

					ipt.val(url.attachment);

					ipt.attr("filename",url.filename);

					ipt.attr("url",url.url);

				}

				if(url && url.media_id){

					ipt.val(url.media_id);

				}

			}, ' . json_encode($options) . ');

		});

	}



</script>';
        echo $s;
        define('TPL_INIT_VIDEO', true);
    }
    $s.= '

	<div class="input-group">

		<input type="text" value="' . $value . '" name="' . $name . '" class="form-control" autocomplete="off" ' . ($options['extras']['text'] ? $options['extras']['text'] : '') . '>

		<div class="input-group-append">

			<button class="btn btn-pill btn-primary" type="button" onclick="showVideoDialog(this,' . str_replace('"', '\'', json_encode($options)) . ');">选择媒体文件</button>

		</div>

	</div>';
    return $s;
}
function tpl_form_field_multi_image3($name, $value = array() , $options = array()) {
    $options['multiple'] = true;
    $options['direct'] = false;
    $options['fileSizeLimit'] = 10 * 1024;
    if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
        if (!preg_match('/^\\w+([\\/]\\w+)?$/i', $options['dest_dir'])) {
            exit('图片上传目录错误,只能指定最多两级目录,如: "eaterplanet_ecommerce","eaterplanet_shop/a1"');
        }
    }
    $s = '';
    if (!defined('TPL_INIT_MULTI_IMAGE')) {
        $s = "\r\n<script type=\"text/javascript\">\r\n\tfunction uploadMultiImage(elm) {\r\n\t\tvar name = \$(elm).next().val();\r\n\t\tutil.image( \"\", function(urls){\r\n\t\t\t\$.each(urls, function(idx, url){\r\n\t\t\t\t\$(elm).parent().parent().next().append('<div class=\"multi-item m-r-10\"><img onerror=\"this.src=\\'/assets/ep/images/nopic.png\\'; this.title=\\'图片未找到.\\'\" src=\"'+url.url+'\" class=\"img-responsive img-thumbnail\"><input type=\"hidden\" name=\"'+name+'[]\" value=\"'+url.attachment+'\"><span class=\"close m-l-5\" style=\"height:10px;width:10px;cursor:pointer\" title=\"删除这张图片\" onclick=\"deleteMultiImage(this)\">×</span></div>');\r\n\t\t\t});\r\n\t\t}, " . json_encode($options) . ");\r\n\t}\r\n\tfunction deleteMultiImage(elm){\r\n\t\trequire([\"jquery\"], function(\$){\r\n\t\t\t\$(elm).parent().remove();\r\n\t\t});\r\n\t}\r\n</script>";
        define('TPL_INIT_MULTI_IMAGE', true);
    }
    $s.= "<div class=\"input-group\">\r\n\t<input type=\"text\" class=\"form-control\" readonly=\"readonly\" value=\"\" placeholder=\"批量上传图片\" autocomplete=\"off\">\r\n\t<div class=\"input-group-append\">\r\n\t\t<button class=\"btn btn-primary\" style=\"border-radius:0 60px 60px 0\" type=\"button\" onclick=\"uploadMultiImage(this);\">选择图片</button>\r\n\t\t<input type=\"hidden\" value=\"" . $name . "\" />\r\n\t</div>\r\n</div>\r\n<div class=\"input-group multi-img-details\" style=\"margin-top:.5em\">";
    if (is_array($value) && (0 < count($value))) {
        foreach ($value as $row) {
            $s.= "\r\n<div class=\"multi-item m-r-10\">\r\n\t<img src=\"" . ($row['thumb']) . "\" onerror=\"this.src='/assets/ep/images/nopic.png'; this.title='图片未找到.'\" class=\"img-responsive img-thumbnail\">\r\n\t<input type=\"hidden\" name=\"" . $name . '[]" value="' . $row['image'] . "\" >\r\n\t<span class=\"close m-l-5\" style=\"height:10px;width:10px;cursor:pointer\" title=\"删除这张图片\" onclick=\"deleteMultiImage(this)\">×</span>\r\n</div>";
        }
    }
    $s.= '</div>';
    return $s;
}
function xml($xml) {
    $p = xml_parser_create();
    xml_parse_into_struct($p, $xml, $vals, $index);
    xml_parser_free($p);
    $data = "";
    foreach ($index as $key => $value) {
        if ($key == 'xml' || $key == 'XML') continue;
        $tag = $vals[$value[0]]['tag'];
        $value = $vals[$value[0]]['value'];
        $data[$tag] = $value;
    }
    return $data;
}
function http_request($url, $data = null, $headers = array()) {
    $curl = curl_init();
    if (count($headers) >= 1) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
function is_array2($array) {
    if (is_array($array)) {
        foreach ($array as $k => $v) {
            return is_array($v);
        }
        return false;
    }
    return false;
}
function tpl_selector($name, $options = array()) {
    $options['multi'] = intval($options['multi']);
    $options['buttontext'] = isset($options['buttontext']) ? $options['buttontext'] : '请选择';
    $options['items'] = isset($options['items']) && $options['items'] ? $options['items'] : array();
    $options['readonly'] = isset($options['readonly']) ? $options['readonly'] : true;
    $options['callback'] = isset($options['callback']) ? $options['callback'] : '';
    $options['key'] = isset($options['key']) ? $options['key'] : 'id';
    $options['text'] = isset($options['text']) ? $options['text'] : 'title';
    $options['thumb'] = isset($options['thumb']) ? $options['thumb'] : 'thumb';
    $options['preview'] = isset($options['preview']) ? $options['preview'] : true;
    $options['type'] = isset($options['type']) ? $options['type'] : 'image';
    $options['input'] = isset($options['input']) ? $options['input'] : true;
    $options['required'] = isset($options['required']) ? $options['required'] : false;
    $options['nokeywords'] = isset($options['nokeywords']) ? $options['nokeywords'] : 0;
    $options['placeholder'] = isset($options['placeholder']) ? $options['placeholder'] : '请输入关键词';
    $options['autosearch'] = isset($options['autosearch']) ? $options['autosearch'] : 0;
    if (empty($options['items'])) {
        $options['items'] = array();
    } else {
        if (!is_array2($options['items'])) {
            $options['items'] = array(
                $options['items']
            );
        }
    }
    $options['name'] = $name;
    $titles = '';
    foreach ($options['items'] as $item) {
        $titles.= $item[$options['text']];
        if (1 < count($options['items'])) {
            $titles.= '; ';
        }
    }
    $options['value'] = isset($options['value']) ? $options['value'] : $titles;
    $readonly = ($options['readonly'] ? 'readonly' : '');
    $required = ($options['required'] ? ' data-rule-required="true"' : '');
    $callback = (!empty($options['callback']) ? ', ' . $options['callback'] : '');
    $id = ($options['multi'] ? $name . '[]' : $name);
    $html = '<div id=\'' . $name . "_selector' class='selector'\r\n                     data-type=\"" . $options['type'] . "\"\r\n                     data-key=\"" . $options['key'] . "\"\r\n                     data-text=\"" . $options['text'] . "\"\r\n                     data-thumb=\"" . $options['thumb'] . "\"\r\n                     data-multi=\"" . $options['multi'] . "\"\r\n                     data-callback=\"" . $options['callback'] . "\"\r\n                     data-url=\"" . $options['url'] . "\"\r\n                     data-nokeywords=\"" . $options['nokeywords'] . "\"\r\n                  data-autosearch=\"" . $options['autosearch'] . "\"\r\n\r\n                 >";
    if ($options['input']) {
        $html.= '<div class=\'input-group\'>' . '<input type=\'text\' id=\'' . $name . '\' name=\'' . $name . '\'  value=\'' . $options['value'] . '\' class=\'form-control text\'  ' . $readonly . '  ' . $required . '/>' . '<div class=\'input-group-btn\'>';
    }
    $html.= '<button class=\'btn btn-primary\' type=\'button\' onclick=\'biz.selector.select(' . json_encode($options) . ');\'>' . $options['buttontext'] . '</button>';
    if ($options['input']) {
        $html.= '</div>';
        $html.= '</div>';
    }
    $show = ($options['preview'] ? '' : ' style=\'display:none\'');
    if ($options['type'] == 'image') {
        $html.= '<div class=\'input-group multi-img-details container\' ' . $show . '>';
    } else if ($options['type'] == 'coupon') {
        $html.= '<div class=\'input-group multi-audio-details\' ' . $show . ">\r\n                        <table class='table'>\r\n                            <thead>\r\n                            <tr>\r\n                                <th style='width:100px;'>优惠券名称</th>\r\n                                <th style='width:200px;'></th>\r\n                                <th>优惠券总数</th>\r\n                                <th>每人限领数量</th>\r\n                                <th style='width:80px;'>操作</th>\r\n                            </tr>\r\n                            </thead>\r\n                            <tbody class='ui-sortable container'>";
    } else if ($options['type'] == 'coupon_cp') {
        $html.= '<div class=\'input-group multi-audio-details\' ' . $show . ">\r\n                        <table class='table'>\r\n                            <thead>\r\n                            <tr>\r\n                                <th style='width:100px;'>优惠券名称</th>\r\n                                <th style='width:200px;'></th>\r\n                                <th></th>\r\n                                <th></th>\r\n                                <th style='width:80px;'>操作</th>\r\n                            </tr>\r\n                            </thead>\r\n                            <tbody id='param-items' class='ui-sortable container'>";
    } else if ($options['type'] == 'coupon_share') {
        $html.= '<div class=\'input-group multi-audio-details\' ' . $show . ">\r\n                        <table class='table'>\r\n                            <thead>\r\n                            <tr>\r\n                                <th style='width:100px;'>优惠券名称</th>\r\n                                <th style='width:200px;'></th>\r\n                                <th></th>\r\n                                <th>每人领取数量</th>\r\n                                <th style='width:80px;'>操作</th>\r\n                            </tr>\r\n                            </thead>\r\n                            <tbody id='param-items' class='ui-sortable container'>";
    } else if ($options['type'] == 'coupon_shares') {
        $html.= '<div class=\'input-group multi-audio-details\' ' . $show . ">\r\n                        <table class='table'>\r\n                            <thead>\r\n                            <tr>\r\n                                <th style='width:100px;'>优惠券名称</th>\r\n                                <th style='width:200px;'></th>\r\n                                <th></th>\r\n                                <th>每人领取数量</th>\r\n                                <th style='width:80px;'>操作</th>\r\n                            </tr>\r\n                            </thead>\r\n                            <tbody id='param-items' class='ui-sortable container'>";
    } else {
        $html.= '<div class=\'input-group multi-audio-details container\' ' . $show . '>';
    }
    foreach ($options['items'] as $item) {
        if ($options['type'] == 'image') {
            $html.= '<div class=\'multi-item\' data-' . $options['key'] . '=\'' . $item[$options['key']] . '\' data-name=\'' . $name . "'>\r\n                                      <img class='img-responsive img-thumbnail' src='" . tomedia($item[$options['thumb']]) . "' onerror='this.src=\"/assets/ep/images/nopic.png\"'>\r\n                                      <div class='img-nickname'>" . $item[$options['text']] . "</div>\r\n                                     <input type='hidden' value='" . $item[$options['key']] . '\' name=\'' . $id . "'>\r\n                                     <em onclick='biz.selector.remove(this,\"" . $name . "\")'  class='close'>×</em>\r\n                            <div style='clear:both;'></div>\r\n                         </div>";
        } else if ($options['type'] == 'coupon') {
            $html.= "\r\n                <tr class='multi-product-item' data-" . $options['key'] . '=\'' . $item[$options['key']] . "'>\r\n                    <input type='hidden' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                    <input type='hidden' value='" . $item[$options['key']] . "' name='couponid[]'>\r\n                    <td style='width:80px;'>\r\n                        <img src='" . tomedia($item[$options['thumb']]) . "' style='width:70px;border:1px solid #ccc;padding:1px'>\r\n                    </td>\r\n                    <td style='width:220px;'>" . $item[$options['text']] . "</td>\r\n                    <td>\r\n                        <input class='form-control valid' type='text' value='" . $item['coupontotal'] . '\' name=\'coupontotal' . $item[$options['key']] . "'>\r\n                    </td>\r\n                    <td>\r\n                        <input class='form-control valid' type='text' value='" . $item['couponlimit'] . '\' name=\'couponlimit' . $item[$options['key']] . "'>\r\n                    </td>\r\n                    <td>\r\n                        <button class='btn btn-primary' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                    </td>\r\n                </tr>\r\n                ";
        } else if ($options['type'] == 'coupon_cp') {
            $html.= "\r\n                    <tr class='multi-product-item setticket' data-" . $options['key'] . '=\'' . $item[$options['key']] . "'>\r\n                        <input type='hidden' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                        <input type='hidden' value='" . $item[$options['key']] . "' name='couponid[]'>\r\n                        <td style='width:80px;'>\r\n                            <img src='" . tomedia($item[$options['thumb']]) . "' style='width:70px;border:1px solid #ccc;padding:1px'>\r\n                        </td>\r\n                        <td style='width:220px;'>" . $item[$options['text']] . "</td>\r\n                        <td>\r\n                        </td>\r\n                        <td>\r\n                        </td>\r\n                        <td>\r\n                            <button class='btn btn-primary' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                        </td>\r\n                    </tr>\r\n                    ";
        } else if ($options['type'] == 'coupon_share') {
            $html.= "\r\n                    <tr class='multi-product-item shareticket' data-" . $options['key'] . '=\'' . $item[$options['key']] . "'>\r\n                        <input type='hidden' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                        <input type='hidden' value='" . $item[$options['key']] . "' name='couponid[]'>\r\n                        <td style='width:80px;'>\r\n                            <img src='" . tomedia($item[$options['thumb']]) . "' style='width:70px;border:1px solid #ccc;padding:1px'>\r\n                        </td>\r\n                        <td style='width:220px;'>" . $item[$options['text']] . "</td>\r\n                        <td>\r\n                        </td>\r\n                        <td>\r\n                            <input class='form-control valid' type='text' value='" . $item['couponnum' . $item['id']] . '\' name=\'couponnum' . $item[$options['key']] . "'>\r\n                        </td>\r\n                        <td>\r\n                            <button class='btn btn-primary' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                        </td>\r\n                    </tr>\r\n                    ";
        } else if ($options['type'] == 'coupon_shares') {
            $html.= "\r\n                    <tr class='multi-product-item sharesticket' data-" . $options['key'] . '=\'' . $item[$options['key']] . "'>\r\n                        <input type='hidden' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                        <input type='hidden' value='" . $item[$options['key']] . "' name='couponids[]'>\r\n                        <td style='width:80px;'>\r\n                            <img src='" . tomedia($item[$options['thumb']]) . "' style='width:70px;border:1px solid #ccc;padding:1px'>\r\n                        </td>\r\n                        <td style='width:220px;'>" . $item[$options['text']] . "</td>\r\n                        <td>\r\n                        </td>\r\n                        <td>\r\n                            <input class='form-control valid' type='text' value='" . $item['couponsnum' . $item['id']] . '\' name=\'couponsnum' . $item[$options['key']] . "'>\r\n                        </td>\r\n                        <td>\r\n                            <button class='btn btn-primary' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                        </td>\r\n                    </tr>\r\n                    ";
        } else {
            $html.= '<div class=\'multi-audio-item \' data-' . $options['key'] . '=\'' . $item[$options['key']] . "' >\r\n                       <div class='input-group'>\r\n                       <input type='text' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                       <input type='hidden'  value='" . $item[$options['key']] . '\' name=\'' . $id . "'>\r\n                       <div class='input-group-btn'><button class='btn btn-primary' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                       </div></div></div>";
        }
    }
    if ($options['type'] == 'coupon') {
        $html.= '</tbody></table>';
    } else if ($options['type'] == 'coupon_cp') {
        $html.= '</tbody></table>';
    } else if ($options['type'] == 'coupon_share') {
        $html.= '</tbody></table>';
    } else if ($options['type'] == 'coupon_shares') {
        $html.= '</tbody></table>';
    } else {
        if ($options['type'] == 'coupon_sync') {
            $html.= '</tbody></table>';
        }
    }
    $html.= '</div></div>';
    return $html;
}
function tpl_form_field_date($name, $value = '', $withtime = false) {
    $s = '';
    $withtime = empty($withtime) ? false : true;
    if (!empty($value)) {
        $value = strexists($value, '-') ? strtotime($value) : $value;
    } else {
        $value = TIMESTAMP;
    }
    $value = ($withtime ? date('Y-m-d H:i:s', $value) : date('Y-m-d', $value));
    $s.= '<input type="text" name="' . $name . '"  value="' . $value . '" placeholder="请选择日期时间" readonly="readonly" class="datetimepicker form-control" style="padding-left:12px;" />';
    $s.= '

		<script type="text/javascript">

			require(["datetimepicker"], function(){

					var option = {

						lang : "zh",

						step : 5,

						timepicker : ' . (!empty($withtime) ? "true" : "false") . ',

						closeOnDateSelect : true,

						format : "Y-m-d' . (!empty($withtime) ? ' H:i"' : '"') . '

					};

				$(".datetimepicker[name = \'' . $name . '\']").datetimepicker(option);

			});

		</script>';
    return $s;
}
function tpl_form_field_daterange($name, $value = array() , $time = false) {
    $s = '';
    if (empty($time) && !defined('TPL_INIT_DATERANGE_DATE')) {
        $s = '

<script type="text/javascript">

	require(["daterangepicker"], function(){

		$(function(){

			$(".daterange.daterange-date").each(function(){

				var elm = this;

				$(this).daterangepicker({

					startDate: $(elm).prev().prev().val(),

					endDate: $(elm).prev().val(),

					format: "YYYY-MM-DD"

				}, function(start, end){

					$(elm).find(".date-title").html(start.toDateStr() + " 至 " + end.toDateStr());

					$(elm).prev().prev().val(start.toDateStr());

					$(elm).prev().val(end.toDateStr());

				});

			});

		});

	});

</script>

';
        define('TPL_INIT_DATERANGE_DATE', true);
    }
    if (!empty($time) && !defined('TPL_INIT_DATERANGE_TIME')) {
        $s = '

<script type="text/javascript">

	require(["daterangepicker"], function(){

		$(function(){

			$(".daterange.daterange-time").each(function(){

				var elm = this;

				$(this).daterangepicker({

					startDate: $(elm).prev().prev().val(),

					endDate: $(elm).prev().val(),

					format: "YYYY-MM-DD HH:mm",

					timePicker: true,

					timePicker12Hour : false,

					timePickerIncrement: 1,

					minuteStep: 1

				}, function(start, end){

					$(elm).find(".date-title").html(start.toDateTimeStr() + " 至 " + end.toDateTimeStr());

					$(elm).prev().prev().val(start.toDateTimeStr());

					$(elm).prev().val(end.toDateTimeStr());

				});

			});

		});

	});

</script>

';
        define('TPL_INIT_DATERANGE_TIME', true);
    }
    if ($value['starttime'] !== false && $value['start'] !== false) {
        if ($value['start']) {
            $value['starttime'] = empty($time) ? date('Y-m-d', strtotime($value['start'])) : date('Y-m-d H:i', strtotime($value['start']));
        }
        $value['starttime'] = empty($value['starttime']) ? (empty($time) ? date('Y-m-d') : date('Y-m-d H:i')) : $value['starttime'];
    } else {
        $value['starttime'] = '请选择';
    }
    if ($value['endtime'] !== false && $value['end'] !== false) {
        if ($value['end']) {
            $value['endtime'] = empty($time) ? date('Y-m-d', strtotime($value['end'])) : date('Y-m-d H:i', strtotime($value['end']));
        }
        $value['endtime'] = empty($value['endtime']) ? $value['starttime'] : $value['endtime'];
    } else {
        $value['endtime'] = '请选择';
    }
    $s.= '

	<input name="' . $name . '[start]' . '" type="hidden" value="' . $value['starttime'] . '" />

	<input name="' . $name . '[end]' . '" type="hidden" value="' . $value['endtime'] . '" />

	<button class="btn btn-primary daterange ' . (!empty($time) ? 'daterange-time' : 'daterange-date') . '" type="button"><span class="date-title">' . $value['starttime'] . ' 至 ' . $value['endtime'] . '</span> <i class="fa fa-calendar"></i></button>

	';
    return $s;
}
function save_media($file) {
    return $file;
}
/**
阿里云OSS上传方法
@param file_path 本地文件的绝对路径
@param new_file_name 上传到阿里云OSS上保存的文件名称,一般不要Uploads/image  下面的层次
 *
 */
function save_image_to_alioss($file_path, $new_file_name) {
    include_once ROOT_PATH . 'Modules/Lib/Aliyunoss/autoload.php';
    // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录 https://ram.console.aliyun.com 创建RAM账号。
    $accessKeyId = D('Home/Front')->get_config_by_name('alioss_key');
    $accessKeySecret = D('Home/Front')->get_config_by_name('alioss_secret');
    // Endpoint以杭州为例，其它Region请按实际情况填写。
    $endpoint = "http://oss-cn-beijing.aliyuncs.com";
    // 存储空间名称。
    $bucket_name = D('Home/Front')->get_config_by_name('alioss_bucket');
    $alioss_internal = D('Home/Front')->get_config_by_name('alioss_internal');
    $host_name = $alioss_internal ? '-internal.aliyuncs.com' : '.aliyuncs.com';
    try {
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $bucketlistinfo = $ossClient->listBuckets();
        $bucketlistinfo = $bucketlistinfo->getBucketList();
        $bucketlist = array();
        foreach ($bucketlistinfo as & $bucket) {
            $bucketlist[$bucket->getName() ] = array(
                'name' => $bucket->getName() ,
                'location' => $bucket->getLocation()
            );
        }
        $endpoint = 'http://' . $bucketlist[$bucket_name]['location'] . $host_name;
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $res = $ossClient->uploadFile($bucket_name, $new_file_name, $file_path);
        }
        catch(OssException $e) {
            return $e->getMessage();
        }
    }
    catch(OssException $e) {
        //printf(__FUNCTION__ . ": FAILED\n");
        //printf($e->getMessage() . "\n");
        return;
    }
}
/**
腾讯云上传方法
@param file_path 本地文件的绝对路径
@param new_file_name 上传到腾讯云上保存的文件名称,一般不要Uploads/image  下面的层次
 *
 */
function save_image_to_txyun($file_path, $new_file_name) {
    include_once ROOT_PATH . 'Modules/Lib/Txyun/vendor/autoload.php';
    $secretId = D('Home/Front')->get_config_by_name('tx_secretid'); //"云 API 密钥 SecretId";
    $secretKey = D('Home/Front')->get_config_by_name('tx_secretkey'); //"云 API 密钥 SecretKey";
    $region = D('Home/Front')->get_config_by_name('tx_area'); //设置一个默认的存储桶地域
    $cosClient = new \Qcloud\Cos\Client(array(
        'region' => $region,
        'schema' => 'https', //协议头部，默认为http
        'credentials' => array(
            'secretId' => $secretId,
            'secretKey' => $secretKey
        )
    ));
    $local_path = $file_path;
    try {
        $result = $cosClient->upload($bucket = D('Home/Front')->get_config_by_name('tx_bucket') , //格式：BucketName-APPID
            $key = $new_file_name, $body = fopen($local_path, 'rb')
        /*

        $options = array(

        'ACL' => 'string',

        'CacheControl' => 'string',

        'ContentDisposition' => 'string',

        'ContentEncoding' => 'string',

        'ContentLanguage' => 'string',

        'ContentLength' => integer,

        'ContentType' => 'string',

        'Expires' => 'string',

        'GrantFullControl' => 'string',

        'GrantRead' => 'string',

        'GrantWrite' => 'string',

        'Metadata' => array(

        'string' => 'string',

        ),

        'ContentMD5' => 'string',

        'ServerSideEncryption' => 'string',

        'StorageClass' => 'string'

        )

        */);
        // 请求成功
        return array(
            'code' => 0,
            'key' => $ret['key']
        );
    }
    catch(\Exception $e) {
        // 请求失败
        echo ($e);
        return array(
            'code' => 1,
            'msg' => '上传失败' . $e
        );
    }
}
/**
七牛云上传方法
@param file_path 本地文件的绝对路径
@param new_file_name 上传到七牛上保存的文件名称,一般不要Uploads/image  下面的层次
 *
 */
function save_image_to_qiniu($file_path, $new_file_name) {
    include_once ROOT_PATH . 'Modules/Lib/Qiniu/autoload.php';
    //qiniu_accesskey
    $accessKey_arr = M('eaterplanet_ecommerce_config')->where(array(
        'name' => 'qiniu_accesskey'
    ))->find();
    $secretKey_arr = M('eaterplanet_ecommerce_config')->where(array(
        'name' => 'qiniu_secretkey'
    ))->find();
    $bucket_arr = M('eaterplanet_ecommerce_config')->where(array(
        'name' => 'qiniu_bucket'
    ))->find();
    $accessKey = $accessKey_arr['value'];
    $secretKey = $secretKey_arr['value'];
    $bucket = $bucket_arr['value'];
    $auth = new Auth($accessKey, $secretKey);
    // 生成上传 Token
    $token = $auth->uploadToken($bucket);
    $filePath = $file_path;
    // 上传到七牛后保存的文件名
    $key = $new_file_name; //'Uploads/2.jpg';
    // 初始化 UploadManager 对象并进行文件的上传。
    $uploadMgr = new UploadManager();
    // 调用 UploadManager 的 putFile 方法进行文件的上传。
    list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
    if ($err !== null) {
        return array(
            'code' => 1,
            'msg' => '上传失败'
        );
    } else {
        return array(
            'code' => 0,
            'key' => $ret['key']
        );
    }
}
function check_db_field_exist($table_name, $column) {
    $result = false;
    $exist = M()->query("select * from INFORMATION_SCHEMA.COLUMNS where table_name='{$table_name}' ");
    if (!empty($exist)) {
        $arr = array();
        foreach ($exist as $val) {
            if ($val['column_name'] == $column) {
                $result = true;
                break;
            }
        }
    }
    return $result;
}
function tpl_form_field_image2($name, $value = '', $default = '', $options = array()) {
    if (empty($default)) {
        $default = '/assets/ep/images/nopic.png';
    }
    $val = $default;
    if (!empty($value)) {
        $val = tomedia($value);
    } else {
        $val = $val ? $val : '/assets/ep/images/default-pic.jpg';
    }
    if (!empty($options['global'])) {
        $options['global'] = true;
    } else {
        $options['global'] = false;
    }
    if (empty($options['class_extra'])) {
        $options['class_extra'] = '';
    }
    if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
        if (!preg_match('/^\\w+([\\/]\\w+)?$/i', $options['dest_dir'])) {
            exit('图片上传目录错误,只能指定最多两级目录,如: "we7_images","we7_images/a1"');
        }
    }
    $options['direct'] = true;
    $options['multiple'] = false;
    if (isset($options['thumb'])) {
        $options['thumb'] = !empty($options['thumb']);
    }
    $options['fileSizeLimit'] = intval(10) * 1024;
    $s = '';
    if (!defined('TPL_INIT_IMAGE')) {
        $s = "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\tfunction showImageDialog(elm, opts, options) {\r\n\t\t\t\trequire([\"util\"], function(util){\r\n\t\t\t\t\tvar btn = \$(elm);\r\n\t\t\t\t\tvar ipt = btn.parent().prev();\r\n\t\t\t\t\tvar val = ipt.val();\r\n\t\t\t\t\tvar img = ipt.parent().next().children();\r\n\t\t\t\t\toptions = " . str_replace('"', '\'', json_encode($options)) . ";\r\n\t\t\t\t\tutil.image(val, function(url){\r\n\t\t\t\t\t\tif(url.url){\r\n\t\t\t\t\t\t\tif(img.length > 0){\r\n\t\t\t\t\t\t\t\timg.get(0).src = url.url;\r\n\t\t\t\t\t\t\t\timg.closest(\".input-group\").show();\r\n\t\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\tipt.val(url.attachment);\r\n\t\t\t\t\t\t\tipt.attr(\"filename\",url.filename);\r\n\t\t\t\t\t\t\tipt.attr(\"url\",url.url);\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t\tif(url.media_id){\r\n\t\t\t\t\t\t\tif(img.length > 0){\r\n\t\t\t\t\t\t\t\timg.get(0).src = \"\";\r\n\t\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\tipt.val(url.media_id);\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t}, options);\r\n\t\t\t\t});\r\n\t\t\t}\r\n\t\t\tfunction deleteImage(elm){\r\n\t\t\t\trequire([\"jquery\"], function(\$){\r\n\t\t\t\t\t\$(elm).prev().attr(\"src\", \"/assets/ep/images/nopic.png\");\r\n\t\t\t\t\t\$(elm).parent().prev().find(\"input\").val(\"\");\r\n\t\t\t\t});\r\n\t\t\t}\r\n\t\t</script>";
        define('TPL_INIT_IMAGE', true);
    }
    $s.= "\r\n\t\t<div class=\"input-group " . $options['class_extra'] . "\">\r\n\t\t\t<input type=\"text\" name=\"" . $name . '" value="' . $value . '"' . ($options['extras']['text'] ? $options['extras']['text'] : '') . " class=\"form-control\" autocomplete=\"off\">\r\n\t\t\t<div class=\"input-group-append\">\r\n\t\t\t\t<button class=\"btn btn-pill btn-primary\" type=\"button\" onclick=\"showImageDialog(this);\">选择图片</button>\r\n\t\t\t</div>\r\n\t\t</div>";
    $s.= '<div class="input-group ' . $options['class_extra'] . '" style="margin-top:.5em;"><img src="' . $val . '" onerror="this.src=\'' . $default . '\'; this.title=\'图片未找到.\'" class="img-responsive img-thumbnail" ' . ($options['extras']['image'] ? $options['extras']['image'] : '') . " width=\"150\" />\r\n                <button type=\"button\" class=\"close m-l-5\" style=\"height:10px;width:10px;cursor:pointer\" data-dismiss=\"modal\" aria-label=\"Close\" onclick=\"deleteImage(this)\" title=\"删除这张图片\"><span aria-hidden=\"true\">×</span></button>\r\n            </div>";
    return $s;
}
function pagination($total, $pageIndex, $pageSize = 15, $url = '', $context = array(
    'before' => 3,
    'after' => 2,
    'ajaxcallback' => '',
    'callbackfuncname' => ''
)) {
    global $_W;
    $pdata = array(
        'tcount' => 0,
        'tpage' => 0,
        'cindex' => 0,
        'findex' => 0,
        'pindex' => 0,
        'nindex' => 0,
        'lindex' => 0,
        'options' => ''
    );
    if (empty($context['before'])) {
        $context['before'] = 3;
    }
    if (empty($context['after'])) {
        $context['after'] = 2;
    }
    if ($context['ajaxcallback']) {
        $context['isajax'] = true;
    }
    if ($context['callbackfuncname']) {
        $callbackfunc = $context['callbackfuncname'];
    }
    $pdata['tcount'] = $total;
    $pdata['tpage'] = (empty($pageSize) || $pageSize < 0) ? 1 : ceil($total / $pageSize);
    if ($pdata['tpage'] <= 1) {
        return '';
    }
    $cindex = $pageIndex;
    $cindex = min($cindex, $pdata['tpage']);
    $cindex = max($cindex, 1);
    $pdata['cindex'] = $cindex;
    $pdata['findex'] = 1;
    $pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
    $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
    $pdata['lindex'] = $pdata['tpage'];
    if ($context['isajax']) {
        if (empty($url)) {
            $url = $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($_GET);
        }
        $pdata['faa'] = 'href="javascript:;" page="' . $pdata['findex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['findex'] . '\', this);"' : '');
        $pdata['paa'] = 'href="javascript:;" page="' . $pdata['pindex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['pindex'] . '\', this);"' : '');
        $pdata['naa'] = 'href="javascript:;" page="' . $pdata['nindex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['nindex'] . '\', this);"' : '');
        $pdata['laa'] = 'href="javascript:;" page="' . $pdata['lindex'] . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['lindex'] . '\', this);"' : '');
    } else {
        if ($url) {
            $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
            $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
            $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
            $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
        } else {
            $_GET['page'] = $pdata['findex'];
            $pdata['faa'] = 'href="' . $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['pindex'];
            $pdata['paa'] = 'href="' . $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['nindex'];
            $pdata['naa'] = 'href="' . $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['lindex'];
            $pdata['laa'] = 'href="' . $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($_GET) . '"';
        }
    }
    $html = '<div class="pagination justify-content-center dataTables_paginate paging_simple_numbers" style="margin-left:0px!important;">';
    $html.= "<span><a {$pdata['faa']} class=\"paginate_button previous\">首页</a></span>";
    empty($callbackfunc) && $html.= "<span><a {$pdata['paa']} class=\"paginate_button next\">&lt;</a></span>";
    if (!$context['before'] && $context['before'] != 0) {
        $context['before'] = 3;
    }
    if (!$context['after'] && $context['after'] != 0) {
        $context['after'] = 2;
    }
    if ($context['after'] != 0 && $context['before'] != 0) {
        $range = array();
        $range['start'] = max(1, $pdata['cindex'] - $context['before']);
        $range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
        if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
            $range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
            $range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
        }
        for ($i = $range['start']; $i <= $range['end']; $i++) {
            if ($context['isajax']) {
                $aa = 'href="javascript:;" page="' . $i . '" ' . ($callbackfunc ? 'ng-click="' . $callbackfunc . '(\'' . $url . '\', \'' . $i . '\', this);"' : '');
            } else {
                if ($url) {
                    $aa = 'href="?' . str_replace('*', $i, $url) . '"';
                } else {
                    $_GET['page'] = $i;
                    $aa = 'href="?' . http_build_query($_GET) . '"';
                }
            }
            if (!empty($context['isajax'])) {
                $html.= ($i == $pdata['cindex'] ? '<a class="paginate_button current" href="javascript:;">' . $i . '</a>' : '<a class="paginate_button"' . $aa . '>' . $i . '</a>');
            } else {
                $html.= ($i == $pdata['cindex'] ? '<a class="paginate_button current" href="javascript:;">' . $i . '</a>' : "<a {$aa}>" . $i . '</a>');
            }
        }
    }
    if ($pdata['cindex'] < $pdata['tpage']) {
        empty($callbackfunc) && $html.= "<span><a {$pdata['naa']} class=\"paginate_button next\">&gt;</a></span>";
        $html.= "<span><a {$pdata['laa']} class=\"paginate_button\">尾页</a></span>";
    }
    $html.= '</div>';
    return $html;
}
function pagination2($total, $pageIndex, $pageSize = 15, $url = '', $context = array(
    'before' => 3,
    'after' => 2,
    'ajaxcallback' => '',
    'callbackfuncname' => ''
)) {
    $pdata = array(
        'tcount' => 0,
        'tpage' => 0,
        'cindex' => 0,
        'findex' => 0,
        'pindex' => 0,
        'nindex' => 0,
        'lindex' => 0,
        'options' => ''
    );
    $pageNum = ceil($total / $pageSize);
    if ($context['ajaxcallback']) {
        $context['isajax'] = true;
    }
    if ($context['callbackfuncname']) {
        $callbackfunc = $context['callbackfuncname'];
    }
    $html = '<div class="dataTables_info">共' . $total . '条记录<span class="paginate_button next">&nbsp;共' . $pageNum . '页</span></div>';
    if (!empty($total)) {
        $pdata['tcount'] = $total;
        $pdata['tpage'] = empty($pageSize) || ($pageSize < 0) ? 1 : ceil($total / $pageSize);
        if (1 < $pdata['tpage']) {
            $html.= '<div class="dataTables_paginate paging_simple_numbers">';
            $cindex = $pageIndex;
            $cindex = min($cindex, $pdata['tpage']);
            $cindex = max($cindex, 1);
            $pdata['cindex'] = $cindex;
            $pdata['findex'] = 1;
            $pdata['pindex'] = 1 < $cindex ? $cindex - 1 : 1;
            $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
            $pdata['lindex'] = $pdata['tpage'];
            if ($context['isajax']) {
                if (empty($url)) {
                    $url = $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($_GET);
                }
                $pdata['faa'] = 'href="javascript:;" page="' . $pdata['findex'] . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['findex'] . '\', this);return false;"' : '');
                $pdata['paa'] = 'href="javascript:;" page="' . $pdata['pindex'] . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['pindex'] . '\', this);return false;"' : '');
                $pdata['naa'] = 'href="javascript:;" page="' . $pdata['nindex'] . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['nindex'] . '\', this);return false;"' : '');
                $pdata['laa'] = 'href="javascript:;" page="' . $pdata['lindex'] . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $url . '\', \'' . $pdata['lindex'] . '\', this);return false;"' : '');
            } else if ($url) {
                $pdata['jump'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
                $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
                $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
                $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
                $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
            } else {
                $url = $_SERVER["REQUEST_URI"] . (strpos($_SERVER["REQUEST_URI"], '?') ? '' : "?") . $pa;
                $parse = parse_url($url);
                $url = preg_replace('/&page=(\d+)[\/]/', '', $url);
                $url = preg_replace('/&page=(\d+)/', '', $url);
                $jump_get = $_GET;
                $jump_get['page'] = '';
                $pdata['jump'] = 'href="' . $url . '&page=' . $pdata['cindex'] . '" data-href="' . $url . '&page=' . $pdata['cindex'] . '"';
                $pdata['faa'] = 'href="' . $url . '&page=' . $pdata['findex'] . '"';
                $pdata['paa'] = 'href="' . $url . '&page=' . $pdata['pindex'] . '"';
                $pdata['naa'] = 'href="' . $url . '&page=' . $pdata['nindex'] . '"';
                $pdata['laa'] = 'href="' . $url . '&page=' . $pdata['lindex'] . '"';
            }
            if (1 < $pdata['cindex']) {
                $html.= '<a ' . $pdata['faa'] . ' class="paginate_button previous">首页</a>';
                $html.= '<a ' . $pdata['paa'] . ' class="paginate_button next">&lt;上一页</a>';
            }
            if (!$context['before'] && ($context['before'] != 0)) {
                $context['before'] = 3;
            }
            if (!$context['after'] && ($context['after'] != 0)) {
                $context['after'] = 2;
            }
            ///page/2
            if (($context['after'] != 0) && ($context['before'] != 0)) {
                $range = array();
                $range['start'] = max(1, $pdata['cindex'] - $context['before']);
                $range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
                if (($range['end'] - $range['start']) < ($context['before'] + $context['after'])) {
                    $range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
                    $range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
                }
                $url = $_SERVER["REQUEST_URI"] . (strpos($_SERVER["REQUEST_URI"], '?') ? '' : "?") . $pa;
                $url = preg_replace('/&page=(\d+)[\/]/', '', $url);
                $url = preg_replace('/&page=(\d+)/', '', $url);
                $url = $url . '&page=*';
                $i = $range['start'];
                while ($i <= $range['end']) {
                    if ($context['isajax']) {
                        $aa = 'href="javascript:;" page="' . $i . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $url . '\', \'' . $i . '\', this);return false;"' : '');
                    } else if ($url) {
                        $aa = 'href="' . str_replace('*', $i, $url) . '"';
                    } else {
                        $_GET['page'] = $i;
                        $aa = 'href="?' . http_build_query($_GET) . '"';
                    }
                    $html.= ($i == $pdata['cindex'] ? '<span><a class="paginate_button current" href="javascript:;">' . $i . '</a>' : '<a class="paginate_button"' . $aa . '>' . $i . '</a></span>');
                    ++$i;
                }
            }
            if ($pdata['cindex'] < $pdata['tpage']) {
                $html.= '<a ' . $pdata['naa'] . ' class="paginate_button next">下一页&gt;</a>';
                $html.= '<a ' . $pdata['laa'] . ' class="paginate_button">尾页</a>';
            }
            $html.= '</div>';
            if (5 < $pdata['tpage']) {
                $html.= '<div class="dataTables_paginate paging_simple_numbers row">';
                $html.= '<span><input size="3" class="form-control" value=\'' . $pdata['cindex'] . '\' type=\'tel\'/></span>';
                $html.= '<a ' . $pdata['jump'] . ' class="paginate_button pager-nav-jump">跳转</a>';
                $html.= '</div>';
                $html.= '<script>$(function() {$(".dataTables_paginate  input").bind("input propertychange", function() {var val=$(this).val(),elm=$(this).closest("div").find(".pager-nav-jump"),href=elm.data("href");console.log(href);href=href.replace(/&page=(\d+)/, "&page=");elm.attr("href", href+val)}).on("keydown", function(e) {if (e.keyCode == "13") {var val=$(this).val(),elm=$(this).closest("div").find(".pager-nav-jump"),href=elm.data("href");href=href.replace(/&page=(\d+)/, "&page="); location.href=href+val;}});})</script>';
            }
        }
    }
    $html.= '';
    return $html;
}
function show_json($status = 1, $return = NULL) {
    $ret = array(
        'status' => $status,
        'result' => $status == 1 ? array(
            'url' => ''
        ) : array()
    );
    if (!is_array($return)) {
        if ($return) {
            $ret['result']['message'] = $return;
        }
        exit(json_encode($ret));
    } else {
        $ret['result'] = $return;
    }
    if (isset($return['url'])) {
        $ret['result']['url'] = $return['url'];
    } else {
        if ($status == 1) {
            $ret['result']['url'] = '';
        }
    }
    exit(json_encode($ret));
}
function tpl_ueditor($id, $value = '', $options = array()) {
    $s = '';
    $options['height'] = empty($options['height']) ? 200 : $options['height'];
    $options['allow_upload_video'] = isset($options['allow_upload_video']) ? $options['allow_upload_video'] : true;
    $s.= !empty($id) ? "<textarea id=\"{$id}\" name=\"{$id}\" type=\"text/plain\" style=\"height:{$options['height']}px;\">{$value}</textarea>" : '';
    $s.= "

	<script type=\"text/javascript\">

		require(['util'], function(util){

			util.editor('" . ($id ? $id : "") . "', {

			height : {$options['height']},

			dest_dir : '" . ($options['dest_dir'] ? $options['dest_dir'] : "") . "',

			image_limit : " . (intval($GLOBALS['_W']['setting']['upload']['image']['limit']) * 1024) . ",

			allow_upload_video : " . ($options['allow_upload_video'] ? 'true' : 'false') . ",

			audio_limit : " . (intval($GLOBALS['_W']['setting']['upload']['audio']['limit']) * 1024) . ",

			callback : ''

			});

		});

	</script>";
    return $s;
}
function tomedia($image) {
    if (strpos($image, 'http:') !== false || strpos($image, 'https:') !== false) {
        return $image;
    } else {
        $domain = D('Seller/Front')->get_config_by_name('shop_domain');
        $attachment_type = D('Seller/Front')->get_config_by_name('attachment_type');
        //save_image_to_alioss
        if ($attachment_type == 1) {
            $qiniu_url = D('Seller/Front')->get_config_by_name('qiniu_url');
            return $qiniu_url . 'Uploads/image/' . $image;
        } else if ($attachment_type == 2) {
            $alioss_url = D('Seller/Front')->get_config_by_name('alioss_url');
            return $alioss_url . 'Uploads/image/' . $image;
        } else if ($attachment_type == 3) {
            $txyun_url = D('Seller/Front')->get_config_by_name('tx_url');
            return $txyun_url . 'Uploads/image/' . $image;
        } else {
            return $domain . '/Uploads/image/' . $image;
        }
    }
}
function istrlen($string, $charset = '') {
    global $_W;
    if (empty($charset)) {
        $charset = $_W['charset'];
    }
    if (strtolower($charset) == 'gbk') {
        $charset = 'gbk';
    } else {
        $charset = 'utf8';
    }
    if (function_exists('mb_strlen')) {
        return mb_strlen($string, $charset);
    } else {
        $n = $noc = 0;
        $strlen = strlen($string);
        if ($charset == 'utf8') {
            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $n++;
                    $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $n+= 2;
                    $noc++;
                } elseif (224 <= $t && $t <= 239) {
                    $n+= 3;
                    $noc++;
                } elseif (240 <= $t && $t <= 247) {
                    $n+= 4;
                    $noc++;
                } elseif (248 <= $t && $t <= 251) {
                    $n+= 5;
                    $noc++;
                } elseif ($t == 252 || $t == 253) {
                    $n+= 6;
                    $noc++;
                } else {
                    $n++;
                }
            }
        } else {
            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t > 127) {
                    $n+= 2;
                    $noc++;
                } else {
                    $n++;
                    $noc++;
                }
            }
        }
        return $noc;
    }
}
function ihttp_request($url, $post = '', $extra = array() , $timeout = 60) {
    $urlset = parse_url($url);
    if (empty($urlset['path'])) {
        $urlset['path'] = '/';
    }
    if (!empty($urlset['query'])) {
        $urlset['query'] = "?{$urlset['query']}";
    }
    if (empty($urlset['port'])) {
        $urlset['port'] = $urlset['scheme'] == 'https' ? '443' : '80';
    }
    if (strexists($url, 'https://') && !extension_loaded('openssl')) {
        if (!extension_loaded("openssl")) {
            message('请开启您PHP环境的openssl');
        }
    }
    if (function_exists('curl_init') && function_exists('curl_exec')) {
        $ch = curl_init();
        if (ver_compare(phpversion() , '5.6') >= 0) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        }
        if (!empty($extra['ip'])) {
            $extra['Host'] = $urlset['host'];
            $urlset['host'] = $extra['ip'];
            unset($extra['ip']);
        }
        curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . ($urlset['port'] == '80' ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        @curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        if ($post) {
            if (is_array($post)) {
                $filepost = false;
                foreach ($post as $name => $value) {
                    if ((is_string($value) && substr($value, 0, 1) == '@') || (class_exists('CURLFile') && $value instanceof CURLFile)) {
                        $filepost = true;
                        break;
                    }
                }
                if (!$filepost) {
                    $post = http_build_query($post);
                }
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        if (defined('CURL_SSLVERSION_TLSv1')) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
        if (!empty($extra) && is_array($extra)) {
            $headers = array();
            foreach ($extra as $opt => $value) {
                if (strexists($opt, 'CURLOPT_')) {
                    curl_setopt($ch, constant($opt) , $value);
                } elseif (is_numeric($opt)) {
                    curl_setopt($ch, $opt, $value);
                } else {
                    $headers[] = "{$opt}: {$value}";
                }
            }
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
        }
        $data = curl_exec($ch);
        $status = curl_getinfo($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($errno || empty($data)) {
            return $error;
        } else {
            return ihttp_response_parse($data);
        }
    }
    $method = empty($post) ? 'GET' : 'POST';
    $fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
    $fdata.= "Host: {$urlset['host']}\r\n";
    if (function_exists('gzdecode')) {
        $fdata.= "Accept-Encoding: gzip, deflate\r\n";
    }
    $fdata.= "Connection: close\r\n";
    if (!empty($extra) && is_array($extra)) {
        foreach ($extra as $opt => $value) {
            if (!strexists($opt, 'CURLOPT_')) {
                $fdata.= "{$opt}: {$value}\r\n";
            }
        }
    }
    $body = '';
    if ($post) {
        if (is_array($post)) {
            $body = http_build_query($post);
        } else {
            $body = urlencode($post);
        }
        $fdata.= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
    } else {
        $fdata.= "\r\n";
    }
    if ($urlset['scheme'] == 'https') {
        $fp = fsockopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
    } else {
        $fp = fsockopen($urlset['host'], $urlset['port'], $errno, $error);
    }
    stream_set_blocking($fp, true);
    stream_set_timeout($fp, $timeout);
    if (!$fp) {
        return error(1, $error);
    } else {
        fwrite($fp, $fdata);
        $content = '';
        while (!feof($fp)) $content.= fgets($fp, 512);
        fclose($fp);
        return ihttp_response_parse($content, true);
    }
}
function ihttp_response_parse($data, $chunked = false) {
    $rlt = array();
    $headermeta = explode('HTTP/', $data);
    if (count($headermeta) > 2) {
        $data = 'HTTP/' . array_pop($headermeta);
    }
    $pos = strpos($data, "\r\n\r\n");
    $split1[0] = substr($data, 0, $pos);
    $split1[1] = substr($data, $pos + 4, strlen($data));
    $split2 = explode("\r\n", $split1[0], 2);
    preg_match('/^(\S+) (\S+) (\S+)$/', $split2[0], $matches);
    $rlt['code'] = $matches[2];
    $rlt['status'] = $matches[3];
    $rlt['responseline'] = $split2[0];
    $header = explode("\r\n", $split2[1]);
    $isgzip = false;
    $ischunk = false;
    foreach ($header as $v) {
        $pos = strpos($v, ':');
        $key = substr($v, 0, $pos);
        $value = trim(substr($v, $pos + 1));
        if (is_array($rlt['headers'][$key])) {
            $rlt['headers'][$key][] = $value;
        } elseif (!empty($rlt['headers'][$key])) {
            $temp = $rlt['headers'][$key];
            unset($rlt['headers'][$key]);
            $rlt['headers'][$key][] = $temp;
            $rlt['headers'][$key][] = $value;
        } else {
            $rlt['headers'][$key] = $value;
        }
        if (!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
            $isgzip = true;
        }
        if (!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
            $ischunk = true;
        }
    }
    if ($chunked && $ischunk) {
        $rlt['content'] = ihttp_response_parse_unchunk($split1[1]);
    } else {
        $rlt['content'] = $split1[1];
    }
    if ($isgzip && function_exists('gzdecode')) {
        $rlt['content'] = gzdecode($rlt['content']);
    }
    $rlt['meta'] = $data;
    if ($rlt['code'] == '100') {
        return ihttp_response_parse($rlt['content']);
    }
    return $rlt;
}
function ihttp_response_parse_unchunk($str = null) {
    if (!is_string($str) or strlen($str) < 1) {
        return false;
    }
    $eol = "\r\n";
    $add = strlen($eol);
    $tmp = $str;
    $str = '';
    do {
        $tmp = ltrim($tmp);
        $pos = strpos($tmp, $eol);
        if ($pos === false) {
            return false;
        }
        $len = hexdec(substr($tmp, 0, $pos));
        if (!is_numeric($len) or $len < 0) {
            return false;
        }
        $str.= substr($tmp, ($pos + $add) , $len);
        $tmp = substr($tmp, ($len + $pos + $add));
        $check = trim($tmp);
    } while (!empty($check));
    unset($tmp);
    return $str;
}
/**
 * 保存用户行为，前台用户和后台用户
 * $type C('FRONTEND_USER')/C('BACKEND_USER')
 */
function storage_user_action($uid, $name, $type, $info) {
    $data['type'] = $type;
    $data['user_id'] = $uid;
    $data['uname'] = $name;
    $data['add_time'] = date('Y-m-d H:i:s', time());
    $data['info'] = $info;
    M('user_action')->add($data);
}
/**
 * 导出EXCEL
 * @param unknown $expTitle       标题
 * @param unknown $expCellName    字段名称
 * @param unknown $expTableData   字段内容
 */
function export_excel($expTitle, $expCellName, $expTableData) {
    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle); //文件名称
    $fileName = $xlsTitle; //or $xlsTitle 文件名称可根据自己情况设定
    $cellNum = count($expCellName);
    $dataNum = count($expTableData);
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $cellName = array(
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ'
    );
    $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1'); //合并单元格
    for ($i = 0; $i < $cellNum; $i++) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
    }
    for ($i = 0; $i < $dataNum; $i++) {
        for ($j = 0; $j < $cellNum; $j++) {
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3) , $expTableData[$i][$expCellName[$j][0]]);
        }
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
    header("Content-Disposition:attachment;filename=$fileName.xls"); //attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}
/**
 * 导入Excel
 * @param unknown $file
 */
function importExecl($file) {
    // 判断文件是什么格式
    $type = pathinfo($file);
    $type = strtolower($type["extension"]);
    $type = $type === 'csv' ? $type : 'Excel5';
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
    $data = array();
    //从第一行开始读取数据
    for ($j = 1; $j <= $highestRow; $j++) {
        if ($j <= 2) {
            continue;
        }
        //从A列读取数据
        for ($k = 'A'; $k <= $highestColumn; $k++) {
            // 读取单元格
            $data[$j][] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
        }
    }
    return $data;
}
function get_qrcode_text_png($data) {
    vendor("phpqrcode.phpqrcode");
    \QRcode::png($data);
}
/**
获取打水印的商品图
@author Albert.Z
 *
 */
function get_team_water_image($team_img) {
    $water_info = M('config')->where(array(
        'name' => 'WATER_BG'
    ))->find();
    if (empty($water_info['value'])) {
        return '';
    }
    $year = date('Y');
    $day = date('md');
    $n = time() . rand(1, 9999) . 'water.png';
    $type = 'goods';
    RecursiveMkdir(ROOT_PATH . '/Uploads/image/' . "{$type}/{$year}/{$day}");
    $image = "{$type}/{$year}/{$day}/";
    $path = ROOT_PATH . '/Uploads/image/' . $image;
    $waterImg = ROOT_PATH . '/Uploads/image/' . $water_info['value'];
    $srcImg = ROOT_PATH . '/Uploads/image/' . $team_img;
    $savepath = $path;
    $savename = $n;
    $new_image = img_water_mark($srcImg, $waterImg, $savepath, $savename);
    if ($new_image < 0) {
        return '';
    } else {
        return ($image . $savename);
    }
}
/**
 * 图片加水印（适用于png/jpg/gif格式）
 *
 * @author Albert.Z
 *
 * @param $srcImg 原图片
 * @param $waterImg 水印图片
 * @param $savepath 保存路径
 * @param $savename 保存名字
 * @param $positon 水印位置
 * 1:顶部居左, 2:顶部居右, 3:居中, 4:底部局左, 5:底部居右
 * @param $alpha 透明度 -- 0:完全透明, 100:完全不透明
 *
 * @return 成功 -- 加水印后的新图片地址
 *          失败 -- -1:原文件不存在, -2:水印图片不存在, -3:原文件图像对象建立失败
 *          -4:水印文件图像对象建立失败 -5:加水印后的新图片保存失败
 */
function img_water_mark($srcImg, $waterImg, $savepath = null, $savename = null, $positon = 5, $alpha = 100) {
    $temp = pathinfo($srcImg);
    $name = $temp['basename'];
    $path = $temp['dirname'];
    $exte = $temp['extension'];
    $savename = $savename ? $savename : $name;
    $savepath = $savepath ? $savepath : $path;
    $savefile = $savepath . '/' . $savename;
    $srcinfo = @getimagesize($srcImg);
    if (!$srcinfo) {
        return -1; //原文件不存在

    }
    $waterinfo = @getimagesize($waterImg);
    if (!$waterinfo) {
        return -2; //水印图片不存在

    }
    $srcImgObj = image_create_from_ext($srcImg);
    if (!$srcImgObj) {
        return -3; //原文件图像对象建立失败

    }
    $waterImgObj = image_create_from_ext($waterImg);
    if (!$waterImgObj) {
        return -4; //水印文件图像对象建立失败

    }
    switch ($positon) {
        //1顶部居左

        case 1:
            $x = $y = 0;
            break;
        //2顶部居右

        case 2:
            $x = $srcinfo[0] - $waterinfo[0];
            $y = 0;
            break;
        //3居中

        case 3:
            $x = ($srcinfo[0] - $waterinfo[0]) / 2;
            $y = ($srcinfo[1] - $waterinfo[1]) / 2;
            break;
        //4底部居左

        case 4:
            $x = 0;
            $y = $srcinfo[1] - $waterinfo[1];
            break;
        //5底部居右

        case 5:
            $x = $srcinfo[0] - $waterinfo[0];
            $y = $srcinfo[1] - $waterinfo[1];
            break;

        default:
            $x = $y = 0;
    }
    imagecopymerge($srcImgObj, $waterImgObj, $x, $y, 0, 0, $waterinfo[0], $waterinfo[1], $alpha);
    switch ($srcinfo[2]) {
        case 1:
            imagegif($srcImgObj, $savefile);
            break;

        case 2:
            imagejpeg($srcImgObj, $savefile);
            break;

        case 3:
            imagepng($srcImgObj, $savefile);
            break;

        default:
            return -5; //保存失败

    }
    imagedestroy($srcImgObj);
    imagedestroy($waterImgObj);
    return $savefile;
}
function image_create_from_ext($imgfile) {
    $info = getimagesize($imgfile);
    $im = null;
    switch ($info[2]) {
        case 1:
            $im = imagecreatefromgif($imgfile);
            break;

        case 2:
            $im = imagecreatefromjpeg($imgfile);
            break;

        case 3:
            $im = imagecreatefrompng($imgfile);
            break;
    }
    return $im;
}
function get_qrcode_link($data, $file_name) {
    $image_dir = ROOT_PATH . 'Uploads/image/qrcode/' . date('Y-m-d');
    RecursiveMkdir($image_dir);
    vendor("phpqrcode.phpqrcode");
    // 纠错级别：L、M、Q、H
    $level = 'L';
    // 点的大小：1到10,用于手机端4就可以了
    $size = 4;
    // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
    //$path = "images/";
    // 生成的文件名
    $fileName = $image_dir . '/' . $file_name;
    \QRcode::png($data, $fileName, $level, $size);
    return 'Uploads/image/qrcode/' . date('Y-m-d') . '/' . $file_name;
}
function get_compare_qrcode($content, $target, $tmp = '', $tmpy = '') {
    $year = date('Y');
    $day = date('md');
    $n = time() . rand(1, 9999) . 'qr.png';
    $type = 'team';
    //begin
    $image = 'Uploads/image/qrcode/' . date('Y-m-d');
    $path = ROOT_PATH . $image;
    RecursiveMkdir($path);
    vendor("phpqrcode.phpqrcode");
    $qr_image = $path . '/' . $n;
    $level = 'L';
    $size = 6;
    $rs = \QRcode::png($content, $qr_image, $level, $size);
    $new_image = $image . "/" . md5($n) . '.png';
    $new_file = ROOT_PATH . '/' . $new_image;
    $img = $qr_image;
    //背景图片
    $target_img = imagecreatefromstring(file_get_contents($target));
    $source = array();
    $source['source'] = imagecreatefromstring(file_get_contents($img));
    $source['size'] = getimagesize($img);
    $num1 = 0;
    //337 501
    $tmp = empty($tmp) ? 461 : $tmp;
    $tmpy = empty($tmpy) ? 763 : $tmpy; //图片之间的间距
    imagecopy($target_img, $source['source'], $tmp, $tmpy, 0, 0, $source['size'][0], $source['size'][1]);
    //$black = imagecolorallocate($target_img, 0x00, 0x00, 0x00);
    //imagefttext($target_img, 14, 0, 320-(strlen($name)/4 * 14), 675, $black, './simhei.ttf', $name);
    Imagejpeg($target_img, $new_file);
    return $new_image;
}
//记录访问ip
function visitors_ip() {
    if (!isset($_SESSION[C('SESSION_PREFIX') ]['visitors_ip'])) {
        $ip = get_client_ip();
        $taobao_ip = new \Lib\Taobaoip();
        $region = $taobao_ip->getLocation($ip);
        //首次访问
        if (!M('visitors_ip')->where(array(
            'ip' => $ip
        ))->find()) {
            $ip_data['first_visit_time'] = date('Y-m-d H:i:s', time());
        }
        $ip_data['province'] = $region['region'];
        $ip_data['city'] = $region['city'];
        $ip_data['ip'] = $ip;
        $ip_data['last_visit_time'] = date('Y-m-d', time());
        $ip_data['add_time'] = date('Y-m-d H:i:s', time());
        $ip_data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        if (M('visitors_ip')->add($ip_data)) {
            session('visitors_ip', $ip);
        }
    }
}
//生成唯一订单号
function build_order_no($user_id = 0) {
    return date('Ymd') . $user_id . substr(implode(NULL, array_map('ord', str_split(substr(uniqid() , 7, 13) , 1))) , 0, 8);
}
function sign($data, $pay_key) {
    $stringA = '';
    foreach ($data as $key => $value) {
        if (!$value) continue;
        if ($stringA) $stringA.= '&' . $key . "=" . $value;
        else $stringA = $key . "=" . $value;
    }
    $wx_key = $pay_key;
    $stringSignTemp = $stringA . '&key=' . $wx_key;
    return strtoupper(md5($stringSignTemp));
}
function nonce_str() {
    $result = '';
    $str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
    for ($i = 0; $i < 32; $i++) {
        $result.= $str[rand(0, 48) ];
    }
    return $result;
}
//取得url中加密的id
function get_url_id($id) {
    $hashids = new \Lib\Hashids(C('PWD_KEY') , C('URL_ID'));
    $get_id = $hashids->decode(I($id));
    return $get_id[0];
}
//付款时生成的token
function pay_token($key_name) {
    $key = 'eaterplanet' . rand(100000, 999999);
    $token = md5($key);
    session($key_name, $key);
    return $token;
}
//取得支付方式名称
function get_payment_name($code) {
    if (!$payment_list = S('payment_list')) {
        $list = M('payment')->select();
        foreach ($list as $k => $v) {
            $payment[$v['payment_code']] = $v;
        }
        S('payment_list', $payment);
        $payment_list = $payment;
    }
    return $payment_list[$code]['payment_name'];
}
//取得货运方式名称
function get_goods_category_name($id) {
    if (!$goods_category = S('goods_category')) {
        $list = M('goods_category')->select();
        foreach ($list as $k => $v) {
            $category[$v['id']] = $v;
        }
        S('goods_category', $category);
        $goods_category = $category;
    }
    return $goods_category[$id]['name'];
}
function get_shipping_name($id) {
    $express_info = M('seller_express')->where(array(
        'id' => $id
    ))->find();
    return $express_info['express_name'];
}
//取得货运方式名称
function get_shipping_name2($id) {
    if (!$shipping_list = S('shipping_list')) {
        $list = M('transport')->select();
        foreach ($list as $k => $v) {
            $shipping[$v['id']] = $v;
        }
        S('shipping_list', $shipping);
        $shipping_list = $shipping;
    }
    return $shipping_list[$id]['title'];
}
//取得支付宝方式配置信息
function get_payment_config($code) {
    $list = M('payment')->where(array(
        'payment_code' => $code
    ))->find();
    if (is_array($list) && !empty($list)) {
        $config = unserialize($list['payment_config']);
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
function api($name, $vars = array()) {
    $array = explode('/', $name);
    $method = array_pop($array);
    $classname = array_pop($array);
    $module = $array ? array_pop($array) : 'Common';
    $callback = $module . '\\Api\\' . $classname . 'Api::' . $method;
    if (is_string($vars)) {
        parse_str($vars, $vars);
    }
    return call_user_func_array($callback, $vars);
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
function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null) {
    $mail = new \Lib\PHPMailer\Phpmailer();
    $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP(); // 设定使用SMTP服务
    $mail->SMTPDebug = 0; // 关闭SMTP调试功能
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth = true; // 启用 SMTP 验证功能
    //  $mail->SMTPSecure = 'ssl';                 // 使用安全协议
    $mail->Host = C('SMTP_HOST'); // SMTP 服务器
    $mail->Port = C('SMTP_PORT'); // SMTP服务器的端口号
    $mail->Username = C('SMTP_USER'); // SMTP服务器用户名
    $mail->Password = C('SMTP_PASS'); // SMTP服务器密码
    $mail->SetFrom(C('FROM_EMAIL') , C('FROM_NAME'));
    $replyEmail = C('REPLY_EMAIL') ? C('REPLY_EMAIL') : C('FROM_EMAIL');
    $replyName = C('REPLY_NAME') ? C('REPLY_NAME') : C('FROM_NAME');
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}
//通过id取重量的名称
function get_weight_name($weight_id) {
    if (!$weight_list = S('weight_list')) {
        $list = M('weight_class')->select();
        foreach ($list as $k => $v) {
            $weight[$v['weight_class_id']] = $v;
        }
        S('weight_list', $weight);
        $weight_list = $weight;
    }
    return $weight_list[$weight_id]['title'];
}
//取得重量信息列表
function get_weight_list() {
    if (!$weight = S('weight')) {
        $list = M('weight_class')->select();
        S('weight', $list);
        $weight = $list;
    }
    return $weight;
}
//通过id取长度的名称
function get_length_name($length_id) {
    if (!$length_list = S('length_list')) {
        $list = M('length_class')->select();
        foreach ($list as $k => $v) {
            $length[$v['length_class_id']] = $v;
        }
        S('length_list', $length);
        $length_list = $length;
    }
    return $length_list[$length_id]['title'];
}
//取得长度信息列表
function get_length_list() {
    if (!$length_list = S('length')) {
        $list = M('length_class')->select();
        S('length', $list);
        $length_list = $list;
    }
    return $length_list;
}
//通过id取得订单状态名称
function get_order_status_name($order_status_id) {
    if (!$order_status = S('order_status_list')) {
        $list = M('order_status')->select();
        foreach ($list as $k => $v) {
            $o_status[$v['order_status_id']] = $v;
        }
        S('order_status_list', $o_status);
        $order_status = $o_status;
    }
    return $order_status[$order_status_id]['name'];
}
//取得订单状态信息列表
function get_order_status_list() {
    if (!$order_status = S('order_status')) {
        $status = M('order_status')->select();
        S('order_status', $status);
        $order_status = $status;
    }
    return $order_status;
}
//通过地区的id取地区的名称
function get_area_name($area_id) {
    if (!$area_list = S('area_list')) {
        $list = M('Area')->field('area_id,area_name')->select();
        foreach ($list as $k => $v) {
            $area[$v['area_id']] = $v;
        }
        S('area_list', $area);
        $area_list = $area;
    }
    return $area_list[$area_id]['area_name'];
}
/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1) {
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}
//字符串截取
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = false) {
    if (function_exists("mb_substr")) {
        if ($suffix) return mb_substr($str, $start, $length, $charset) . "…";
        else return mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        if ($suffix) return iconv_substr($str, $start, $length, $charset) . "…";
        else return iconv_substr($str, $start, $length, $charset);
    }
    $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix) return $slice . "…";
    return $slice;
}
/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login() {
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
function is_seller_login() {
    $user = session('seller_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('seller_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}
function is_agent_login() {
    $user = session('agent_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('agent_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}
function get_agent_logininfo() {
    $user = session('agent_auth');
    $user['id'] = $user['uid'];
    return $user;
}
/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}
/**
时钟群发模板消息
 *
 */
function template_msg_cron() {
    $template_msg_order = M('template_msg_order')->where(array(
        'state' => 0
    ))->order('addtime asc')->limit(20)->select();
    if (!empty($template_msg_order)) {
        foreach ($template_msg_order as $msg_order) {
            $template_data = unserialize($msg_order['template_data']);
            if ($msg_order['type'] == 0) {
                send_template_msg($template_data, $msg_order['url'], $msg_order['open_id'], $msg_order['template_id']);
            } else if ($msg_order['type'] == 2) {
                //$form_id
                $member_info = M('member')->field('member_id')->where(array(
                    'we_openid' => $msg_order['open_id']
                ))->find();
                $member_formid_info = M('member_formid')->where(array(
                    'member_id' => $member_info['member_id'],
                    'state' => 0
                ))->find();
                if (!empty($member_formid_info)) {
                    $form_id = $member_formid_info['formid'];
                    $res = send_wxtemplate_msg($template_data, C('SITE_URL') , $msg_order['url'], $msg_order['open_id'], $msg_order['template_id'], $form_id);
                    M('member_formid')->where(array(
                        'id' => $member_formid_info['id']
                    ))->save(array(
                        'state' => 1
                    ));
                    //var_dump($res);
                    //die();

                }
            } else if ($msg_order['type'] == 1) {
                notify_weixin_msg($msg_order['open_id'], $template_data['descript'], $template_data['title'], $msg_order['url'], $template_data['image']);
            }
            M('template_msg_order')->where(array(
                'id' => $msg_order['id']
            ))->save(array(
                'state' => 1
            ));
        }
    }
}
/**
发送客服消息
 *
 */
/**'

 * 发送模板消息

 * @param unknown $template_data

 * @param unknown $url

 * @param unknown $to_openid

 * @param unknown $template_id

 * @return mixed

 */
function send_template_msg($template_data, $url, $to_openid, $template_id) {
    $appid_info = M('config')->where(array(
        'name' => 'APPID'
    ))->find();
    $appsecret_info = M('config')->where(array(
        'name' => 'APPSECRET'
    ))->find();
    $mchid_info = M('config')->where(array(
        'name' => 'MCHID'
    ))->find();
    $weixin_config = array();
    $weixin_config['appid'] = $appid_info['value'];
    $weixin_config['appscert'] = $appsecret_info['value'];
    $weixin_config['mchid'] = $mchid_info['value'];
    $jssdk = new \Lib\Weixin\Jssdk($weixin_config['appid'], $weixin_config['appscert']);
    $re_access_token = $jssdk->getAccessToken();
    $template = array(
        'touser' => $to_openid,
        'template_id' => $template_id,
        'url' => $url,
        'topcolor' => '#FF0000',
        'data' => $template_data
    );
    $send_url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$re_access_token}";
    $result = sendhttps_post($send_url, json_encode($template));
    return json_decode($result, true);
}
/**
发送小程序模板消息
 *
 */
function send_wxtemplate_msg($template_data, $url, $pagepath, $to_openid, $template_id, $form_id = '1') {
    //$appid_info 	=  M('config')->where( array('name' => 'APPID') )->find();
    $weprogram_appid_info = M('config')->where(array(
        'name' => 'weprogram_appid'
    ))->find();
    $appsecret_info = M('config')->where(array(
        'name' => 'weprogram_appscret'
    ))->find();
    //$mchid_info =  M('config')->where( array('name' => 'MCHID') )->find();
    $weixin_config = array();
    $weixin_config['appid'] = $weprogram_appid_info['value'];
    $weixin_config['appscert'] = $appsecret_info['value'];
    //$weixin_config['mchid'] = $mchid_info['value'];
    $we_appid = $weprogram_appid_info['value'];
    $jssdk = new \Lib\Weixin\Jssdk($weixin_config['appid'], $weixin_config['appscert']);
    $re_access_token = $jssdk->getweAccessToken();
    $template = array(
        'touser' => $to_openid,
        'template_id' => $template_id,
        'form_id' => $form_id,
        'page' => $pagepath,
        'data' => $template_data
    );
    $send_url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$re_access_token}";
    $result = sendhttps_post($send_url, json_encode($template));
    //var_dump($form_id, json_decode($result,true));die();
    return json_decode($result, true);
}
function notify_weixin_msg($to_openid, $msg, $title = '消息提醒', $url = '', $image = '') {
    $appid_info = M('config')->where(array(
        'name' => 'APPID'
    ))->find();
    $appsecret_info = M('config')->where(array(
        'name' => 'APPSECRET'
    ))->find();
    $mchid_info = M('config')->where(array(
        'name' => 'MCHID'
    ))->find();
    $weixin_config = array();
    $weixin_config['appid'] = $appid_info['value'];
    $weixin_config['appscert'] = $appsecret_info['value'];
    $weixin_config['mchid'] = $mchid_info['value'];
    $jssdk = new \Lib\Weixin\Jssdk($weixin_config['appid'], $weixin_config['appscert']);
    $re_access_token = $jssdk->getAccessToken();
    $openId = $to_openid;
    $txt = '{

			"touser":"' . $openId . '",

			"msgtype":"news",

			"news":{

				"articles": [

				 {

					 "title":"' . $title . '",

					 "description":"' . $msg . '",

					 "url":"' . $url . '",

					 "picurl":"' . $image . '"

				 }

				 ]

			}

		}';
    $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $re_access_token;
    $result = sendhttps_post($url, $txt);
    return true;
}
function sendhttp_get($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, array());
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
function sendhttps_post($url, $data) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        return 'Errno' . curl_error($curl);
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
function time_format($time = NULL, $format = 'Y-m-d H:i') {
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}
/**
 * 清空缓存
 */
function clear_cache() {
    $dirs = array();
    $noneed_clear = array(
        ".",
        ".."
    );
    $rootdirs = array_diff(scandir(RUNTIME_PATH) , $noneed_clear);
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
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'children', $root = 0) {
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = & $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] = & $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = & $refer[$parentId];
                    $parent[$child][] = & $list[$key];
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
    $key = md5($key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char.= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $len; $i++) {
        $str.= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace('=', '', base64_encode($str));
}
/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string
 */
function think_ucenter_decrypt($data, $key) {
    $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);
    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char.= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str.= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str.= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}
//数字转ip
function ntoip($n) {
    $iphex = dechex($n); //将10进制数字转换成16进制
    $len = strlen($iphex); //得到16进制字符串的长度
    if (strlen($iphex) < 8) {
        $iphex = '0' . $iphex; //如果长度小于8，在最前面加0
        $len = strlen($iphex); //重新得到16进制字符串的长度

    }
    //这是因为ipton函数得到的16进制字符串，如果第一位为0，在转换成数字后，是不会显示的
    //所以，如果长度小于8，肯定要把第一位的0加上去
    //为什么一定是第一位的0呢，因为在ipton函数中，后面各段加的'0'都在中间，转换成数字后，不会消失
    for ($i = 0, $j = 0; $j < $len; $i = $i + 1, $j = $j + 2) { //循环截取16进制字符串，每次截取2个长度
        $ippart = substr($iphex, $j, 2); //得到每段IP所对应的16进制数
        $fipart = substr($ippart, 0, 1); //截取16进制数的第一位
        if ($fipart == '0') { //如果第一位为0，说明原数只有1位
            $ippart = substr($ippart, 1, 1); //将0截取掉

        }
        $ip[] = hexdec($ippart); //将每段16进制数转换成对应的10进制数，即IP各段的值

    }
    $ip = array_reverse($ip);
    return implode('.', $ip); //连接各段，返回原IP值

}
//显示时间
function toDate($time, $format = 'Y-m-d H:i:s') {
    if (empty($time)) {
        return '无';
    }
    $format = str_replace('#', ':', $format);
    return date($format, $time);
}
//验证字符串长度
function checkLength($str, $min, $max) {
    preg_match_all("/./u", $str, $matches);
    $len = count($matches[0]);
    if ($len < $min || $len > $max) {
        return false;
    } else {
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
        $Oy = $offset % 65535;
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
            $Ly = (-$length) % 65535;
            // negative length requires ... capture everything
            // except a group of  -length characters
            // anchored at the tail-end of the string
            if ($Lx) {
                $Lp = '(?:.{65535}){' . $Lx . '}';
            }
            $Lp = '(.*)(?:' . $Lp . '.{' . $Ly . '})$';
        }
    }
    if (!preg_match('#' . $Op . $Lp . '#us', $string, $match)) {
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
function random($length, $numeric = FALSE) {
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']) , 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash.= $seed{mt_rand(0, $max) };
    }
    return $hash;
}
function tpl_daterange($name, $value = array() , $time = false) {
    $placeholder = (isset($value['placeholder']) ? $value['placeholder'] : '');
    $s = '';
    if (empty($time) && !defined('TPL_INIT_DATERANGE_DATE')) {
        $s = "\r\n<script type=\"text/javascript\">\r\n\trequire([\"daterangepicker\"], function(){\r\n\t\t\$(function(){\r\n\t\t\t\$(\".daterange.daterange-date\").each(function(){\r\n\t\t\t\tvar elm = this;\r\n                var container =\$(elm).parent().prev();\r\n\t\t\t\t\$(this).daterangepicker({\r\n\t\t\t\t\tformat: \"YYYY-MM-DD\"\r\n\t\t\t\t}, function(start, end){\r\n\t\t\t\t\t\$(elm).find(\".date-title\").html(start.toDateStr() + \" 至 \" + end.toDateStr());\r\n\t\t\t\t\tcontainer.find(\":input:first\").val(start.toDateTimeStr());\r\n\t\t\t\t\tcontainer.find(\":input:last\").val(end.toDateTimeStr());\r\n\t\t\t\t});\r\n\t\t\t});\r\n\t\t});\r\n\t});\r\n</script> \r\n";
        define('TPL_INIT_DATERANGE_DATE', true);
    }
    if (!empty($time) && !defined('TPL_INIT_DATERANGE_TIME')) {
        $s = "\r\n<script type=\"text/javascript\">\r\n\trequire([\"daterangepicker\"], function(){\r\n\t\t\$(function(){\r\n\t\t\t\$(\".daterange.daterange-time\").each(function(){\r\n\t\t\t\tvar elm = this;\r\n                 var container =\$(elm).parent().prev();\r\n\t\t\t\t\$(this).daterangepicker({\r\n\t\t\t\t\tformat: \"YYYY-MM-DD HH:mm\",\r\n\t\t\t\t\ttimePicker: true,\r\n\t\t\t\t\ttimePicker12Hour : false,\r\n\t\t\t\t\ttimePickerIncrement: 1,\r\n\t\t\t\t\tminuteStep: 1\r\n\t\t\t\t}, function(start, end){\r\n\t\t\t\t\t\$(elm).find(\".date-title\").html(start.toDateTimeStr() + \" 至 \" + end.toDateTimeStr());\r\n\t\t\t\t\tcontainer.find(\":input:first\").val(start.toDateTimeStr());\r\n\t\t\t\t\tcontainer.find(\":input:last\").val(end.toDateTimeStr());\r\n\t\t\t\t});\r\n\t\t\t});\r\n\t\t});\r\n\t});\r\n     function clearTime(obj){\r\n              \$(obj).prev().html(\"<span class=date-title>\" + \$(obj).attr(\"placeholder\") + \"</span>\");\r\n              \$(obj).parent().prev().find(\"input\").val(\"\");\r\n    }\r\n</script>\r\n";
        define('TPL_INIT_DATERANGE_TIME', true);
    }
    $str = $placeholder;
    $small = (isset($value['sm']) ? $value['sm'] : true);
    $value['starttime'] = isset($value['starttime']) ? $value['starttime'] : ($_GET[$name]['start'] ? $_GET[$name]['start'] : '');
    $value['endtime'] = isset($value['endtime']) ? $value['endtime'] : ($_GET[$name]['end'] ? $_GET[$name]['end'] : '');
    if ($value['starttime'] && $value['endtime']) {
        if (empty($time)) {
            $str = date('Y-m-d', strtotime($value['starttime'])) . '至 ' . date('Y-m-d', strtotime($value['endtime']));
        } else {
            $str = date('Y-m-d H:i', strtotime($value['starttime'])) . ' 至 ' . date('Y-m-d  H:i', strtotime($value['endtime']));
        }
    }
    $s.= "<div style=\"float:left\">\r\n\t<input name=\"" . $name . '[start]' . '" type="hidden" value="' . $value['starttime'] . "\" />\r\n\t<input name=\"" . $name . '[end]' . '" type="hidden" value="' . $value['endtime'] . "\" />\r\n           </div>\r\n          <div class=\"btn-group " . ($small ? 'btn-group-sm' : '') . '" style="' . $value['style'] . "padding-right:0;\"  >\r\n          \r\n\t<button style=\"width:240px\" class=\"btn btn-primary daterange " . (!empty($time) ? 'daterange-time' : 'daterange-date') . '"  type="button"><span class="date-title">' . $str . "</span></button>\r\n        <button class=\"btn btn-primary " . ($small ? 'btn-sm' : '') . '" " type="button" onclick="clearTime(this)" placeholder="' . $placeholder . "\"><i class=\"fa fa-remove\"></i></button>\r\n         </div>\r\n\t";
    return $s;
}
/**
 * 单按钮图片上传
 */
if (!function_exists('tpl_form_field_image_sin')) {
    function tpl_form_field_image_sin($name, $value = '', $default = '', $options = array()) {
        global $_W;
        if (empty($default)) {
            $default = './resource/images/nopic.jpg';
        }
        $val = $default;
        if (!empty($value)) {
            $val = tomedia($value);
        }
        if (defined('SYSTEM_WELCOME_MODULE')) {
            $options['uniacid'] = 0;
        }
        if (!empty($options['global'])) {
            $options['global'] = true;
            //$val = to_global_media(empty($value) ? $default : $value);

        } else {
            $options['global'] = false;
        }
        if (empty($options['class_extra'])) {
            $options['class_extra'] = '';
        }
        if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
            if (!preg_match('/^\w+([\/]\w+)?$/i', $options['dest_dir'])) {
                exit('图片上传目录错误,只能指定最多两级目录,如: "we7_store","we7_store/d1"');
            }
        }
        $options['direct'] = true;
        $options['multiple'] = false;
        if (isset($options['thumb'])) {
            $options['thumb'] = !empty($options['thumb']);
        }
        $options['fileSizeLimit'] = 10 * 1024;
        $s = '';
        if (!defined('TPL_INIT_IMAGE')) {
            $s = '

			<script type="text/javascript">

				function showImageDialog(elm, opts, options) {

					require(["util"], function(util){

						var ipt = $(elm).prev();

						var val = ipt.val();

						var img = $(elm);

						options = ' . str_replace('"', '\'', json_encode($options)) . ';

						util.image(val, function(url){

							if(url.url){

								if(img.length > 0){

									img.get(0).src = url.url;

								}

								ipt.val(url.attachment);

								ipt.attr("filename",url.filename);

								ipt.attr("url",url.url);

							}

							if(url.media_id){

								if(img.length > 0){

									img.get(0).src = url.url;

								}

								ipt.val(url.media_id);

							}

						}, options);

					});

				}

				function deleteImage(elm){

					$(elm).prev().attr("src", "./resource/images/nopic.jpg");

					$(elm).parent().find("input").val("");

				}

			</script>';
            define('TPL_INIT_IMAGE', true);
        }
        $s.= '

			<div class="' . $options['class_extra'] . '" style="position:relative;">

				<input type="hidden" name="' . $name . '" value="' . $value . '"' . ($options['extras']['text'] ? $options['extras']['text'] : '') . ' class="form-control" autocomplete="off">

				<img src="' . $val . '" onerror="this.src=\'' . $default . '\'; this.title=\'图片未找到.\'" class="img-responsive" ' . ($options['extras']['image'] ? $options['extras']['image'] : '') . ' width="150" onclick="showImageDialog(this);" />

				<em class="close" style="position:absolute; top: 0px;right: -14px" title="删除这张图片" onclick="deleteImage(this)">×</em>

			</div>';
        return $s;
    }
}
/**
 * 自动生成新尺寸 的图片
 */
function resize($filename, $width, $height) {
    $image_dir = ROOT_PATH . 'Uploads/image/';
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
        //
        $attachment_type_arr = M('eaterplanet_ecommerce_config')->where(array(
            'name' => 'attachment_type'
        ))->find();
        if ($attachment_type_arr['value'] == 1) {
            $rs = save_image_to_qiniu(ROOT_PATH . 'Uploads/image/' . $new_image, 'Uploads/image/' . $new_image);
        } elseif ($attachment_type_arr['value'] == 2) {
            $rs = save_image_to_alioss(ROOT_PATH . 'Uploads/image/' . $new_image, 'Uploads/image/' . $new_image);
        }
        //attachment_type

    }
    return 'Uploads/image/' . $new_image;
}

if (!function_exists('load_make_plug')) {
    function load_make_plug($name = '')
    {

        static $_modules = array();

        if (isset($_modules[$name])) {
            return $_modules[$name];
        }
        $model = ROOT_PATH."/addons/eaterplanet_ecommerce_plugin_make/" . 'model/' . strtolower($name) . 'ModelClass.php';
        if (!is_file($model)) {
            exit(' Model ' . $name . ' Not Found!');
        }
        require_once $model;
        $class_name = ucfirst($name) . '_EaterplanetShopModel';
        $_modules[$name] = new $class_name();
        return $_modules[$name];
    }
}

/**
 * 商家账号操作日志
 * @author Albert.Z 2020-03-02
 * @param $content 操作内容
 * @param $type 0退出 1登录 2订单 3商品
 * */
function sellerLog($content='default', $type=-1)
{
    $data['type'] = $type;
    $data['s_id'] = $_SESSION['dejavutech_seller_s']['seller_auth']['uid'];
    $data['content'] = $content;
    $data['add_time'] = time();
     M('seller_log')->add($data);
}
?>
