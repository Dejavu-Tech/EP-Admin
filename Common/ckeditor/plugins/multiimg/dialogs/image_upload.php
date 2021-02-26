<?php
 define ( 'IN_BAMBOO', true );
 // 取得根目录
define ( 'ROOT_PATH', '../../../../' );  // back to your root path

$arrType = array (
		'image/jpg',
		'image/gif',
		'image/png',
		'image/bmp',
		'image/pjpeg',
		'image/jpeg' 
);
$max_size = 500 * 1024; // 最大文件限制（单位：byte）
$upfile = ROOT_PATH.'image/uploads'; // 图片目录路径
if (!isset($_FILES ['files'])){
	echo '{"result":"400","msg":"未能找到图片，请确认图片是否过大"}';
	exit ();
}
$file = $_FILES ['files'];

if ($_SERVER ['REQUEST_METHOD'] == 'POST') { // 判断提交方式是否为POST
	if (! is_uploaded_file ( $file ['tmp_name'] )) { // 判断上传文件是否存在
		echo '{"result":"400","msg":"图片不存在"}';
		exit ();
	}
	
	if ($file ['size'] > $max_size) { // 判断文件大小是否大于500000字节
		echo '{"result":"400","msg":"上传图片太大，最大支持：'.($max_size/1024).'KB"}';
		exit ();
	}
	if (! in_array ( $file ['type'], $arrType )) { // 判断图片文件的格式
		echo '{"result":"400","msg":"上传图片格式不对"}';
		exit ();
	}
	if (! file_exists ( $upfile )) { // 判断存放文件目录是否存在
		mkdir ( $upfile, 0777, true );
	}
	$imageSize = getimagesize ( $file ['tmp_name'] );
	$img = $imageSize [0] . '*' . $imageSize [1];
	$fname = $file ['name'];
	$ftype = explode ( '.', $fname );
	$time = explode ( " ", microtime () );
	$time = $time [1] . ($time [0] * 1000);
	$time2 = explode ( ".", $time );  
	$time = $time2 [0];
	$returnName=$time."." .end($ftype);
	$picName = $upfile . "/" . $returnName ;
	
	if (! move_uploaded_file ( $file ['tmp_name'], $picName )) {
		echo '{"result":"400","msg":"从:'.$file ['tmp_name'].'移动图片到:'.$picName.'出错"}';
		exit ();
	} else {
		echo '{"result":"200","imgurl":"image/uploads/' . $returnName . '"}';
	}
}

?>
