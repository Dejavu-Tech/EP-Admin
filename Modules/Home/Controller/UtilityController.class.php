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
namespace Home\Controller;


class UtilityController extends CommonController{



	//删除图片和缩略图
	public function del_image($dir,$image,$type){

		$image_dir=ROOT_PATH.'Uploads/image/'.$dir.'/';
		$thumb_dir=ROOT_PATH.'Uploads/image/cache/'.$dir.'/';

		list($base_name, $ext) = explode(".", $image);
		$image = explode("/", $base_name);
		$image_name=end($image);

		if($type=='product'){
			$del_image=array();
			$del_image[]=$image_dir.$image_name.'.'.$ext;//原图
			$del_image[]=$thumb_dir.$image_name.'-50x50.'.$ext;
			$del_image[]=$thumb_dir.$image_name.'-100x100.'.$ext;
			$del_image[]=$thumb_dir.$image_name.'-255x255.'.$ext;
		}elseif($type=='gallery'){
			$del_image=array();
			$del_image[]=$image_dir.$image_name.'.'.$ext;//原图
			$del_image[]=$thumb_dir.$image_name.'-100x100.'.$ext;
			$del_image[]=$thumb_dir.$image_name.'-127x127.'.$ext;
		}elseif($type=='blog'){
			$del_image=array();
			$del_image[]=$image_dir.$image_name.'.'.$ext;//原图
			$del_image[]=$thumb_dir.$image_name.'-100x100.'.$ext;
			$del_image[]=$thumb_dir.$image_name.'-280x140.'.$ext;
		}elseif($type=='blog_gallery'){
			$del_image=array();
			$del_image[]=$image_dir.$image_name.'.'.$ext;//原图
			$del_image[]=$thumb_dir.$image_name.'-100x100.'.$ext;

		}
		if(!empty($del_image)){
			foreach ($del_image as $k => $v) {
				if(is_file($v)){
					 @unlink($v);
				}
			}
		}
	}
	/**
	 * 二进制上传数据
	 */
	public function upload_binaryFile()
	{
	    $data = file_get_contents("php://input");
	    $get_data = I('get.');
	    $dir = I('get.dir','goods');
	    $type = I('get.type');
	    $name = I('get.name');

	    $image_dir = ROOT_PATH.'Uploads/image/'.$dir;
	    $image_dir .= '/'.date('Y-m-d').'/';

	    $file_path = C('SITE_URL').'/Uploads/image/'.$dir.'/'.date('Y-m-d').'/';
		$kufile_path = $dir.'/'.date('Y-m-d').'/';

	    RecursiveMkdir($image_dir);
	    $file_name = md5($name.time()).'.png';

	    switch($type)
	    {
	        case 'image/jpeg':
	            $file_name = md5($name.time()).'.jpg';
	            break;
	        case 'image/png':
	            $file_name = md5($name.time()).'.png';
	            break;
	    }

	    $thumb_arr = explode('.',$file_name);
	    $thumb_image_name = $thumb_arr[0].'_thumb.'.$thumb_arr[1];

	    file_put_contents($image_dir.$file_name, $data);

        //fileinfo 检测begin
        $fip = finfo_open(FILEINFO_MIME_TYPE);
        $min_result = finfo_file($fip , $image_dir.$file_name );
        fclose( $fip );
        $min_type_arr = array();
        $min_type_arr[] = 'image/jpeg';
        $min_type_arr[] = 'image/gif';
        $min_type_arr[] = 'image/jpg';
        $min_type_arr[] = 'image/png';
        $min_type_arr[] = 'video/mp4';
        if( !in_array($min_result , $min_type_arr ) )
        {
            die();
        }

	    $image = new \Think\Image();
	    $image->open($image_dir.$file_name);
	    //按照原图的比例生成一个最大为400*400的缩略图并保存为thumb.jpg, 实际会按比例自动缩放
	    $image->thumb(400, 400)->save($image_dir.$thumb_image_name);

		////{"filePath":"\/Uploads\/image\/goods\/2017-07-05\/","fileName":"7e414de26624c0a5ac7cd5b9bd5edfe3.jpg"}

	    $result = array('filePath' =>$file_path ,'kufile_path' => $kufile_path,'fileName' => $file_name);

	    echo json_encode($result);
	    die();
	}

	/**
	 *删除 旧的原图和缩略图，修改的情况下使用
	 *
	 */
	public function del_old_image(){

		$old_gallery_image=I('post.old_gallery_image');
		$old_product_image=I('post.old_product_image');

		if(!empty($old_gallery_image)){
			$old_image=I('post.old_gallery_image');

			$thumb_dir=ROOT_PATH.'Uploads/image/cache/gallery/';
			$image_dir=ROOT_PATH.'Uploads/image/gallery/';

		}elseif(!empty($old_product_image)){
			$old_image=I('post.old_product_image');

			$thumb_dir=ROOT_PATH.'Uploads/image/cache/product/';
			$image_dir=ROOT_PATH.'Uploads/image/product/';

		}
		if(!empty($old_image)){

			list($base_name, $ext) = explode(".", $old_image);
			$image = explode("/", $base_name);
			$image_name=end($image);

			$del_image=array();
			$del_image[]=$image_dir.$image_name.'.'.$ext;//原图
			$del_image[]=$thumb_dir.$image_name.'-100x100.'.$ext;//100x100

			foreach ($del_image as $k => $v) {
				if(is_file($v)){
					 @unlink($v);
				}
			}
		}
	}


	public function file()
	{

		$do = I('get.do');

		if('group_list' == $do)
		{
			$uid = 1;

			if(  is_agent_login() )
			{
				$uid = is_agent_login();
				$uid = $uid +1;
			}

		    $group_list = M('core_attachment_group')->where( array('uid' => $uid) )->order('id asc')->select();

		    $res = array(
		          'message' => array(
		                  'errno' => 0,
		                  'message' => $group_list
		              ),
		          'redirect' => '',
		          'type' => 'ajax'
		    );

			echo json_encode($res);
			die();
		}


		if( 'change_group' == $do )
		{
		    $name = I('request.name');
		    $id = I('request.id');

		    M('core_attachment_group')->where( array('id' => $id) )->save( array('name' => $name) );

		    //{"message":{"errno":0,"message":"\u66f4\u65b0\u6210\u529f"},"redirect":"","type":"ajax"}

		    $res =  array('message' => array('errno' => 0, 'message' => '更新成功'),
		        'redirect' =>'','type' => 'ajax'
		     );

		    echo json_encode( $res  );
		    die();

		}

		if('add_group' == $do)
		{

		    $core_attachment_group_data = array();
		    $core_attachment_group_data['name'] = '未命名';
		    $core_attachment_group_data['uniacid'] = 0;
		    $core_attachment_group_data['uid'] = 1;

		    $id = M('core_attachment_group')->add( $core_attachment_group_data );

		    $res =  array('message' => array('errno' => 0, 'message' => array('id'=>$id),
                                'redirect' =>'','type' => 'ajax'
		                  ) );
		    echo json_encode($res);

		    //{"message":{"errno":0,"message":{"id":"27"}},"redirect":"","type":"ajax"}

			die();
		}

		if ($do == 'move_to_group') {

		    $group_id = I('request.id');
		    $ids = I('request.keys');

		    M('core_attachment')->where( array('id' => array('in', $ids ) ) )->save( array('group_id' => $group_id)  );

		    $res =  array('message' => array('errno' => 0, 'message' => '删除成功'),
		        'redirect' =>'','type' => 'ajax'
		    );

		    echo json_encode( $res  );
		    die();

		}



		if( 'del_group' == $do )
		{
			$id = I('request.id');

		    M('core_attachment_group')->where( array('id' => $id) )->delete();

		    $res =  array('message' => array('errno' => 0, 'message' => '删除成功'),
		        'redirect' =>'','type' => 'ajax'
		    );

		    echo json_encode( $res  );
		    die();
		}

		if ($do == 'video') {

			$year = I('get.year');
			$month = I('get.month');
			$page = I('get.page',1);
			$groupid = I('get.groupid',1);
			$page_size = 10;
			$page = max(1, $page);

			$offset = ($page -1) * $page_size;

			$where = " type=2 ";


			if ($year || $month) {
				$start_time = strtotime("{$year}-{$month}-01");
				$end_time = strtotime('+1 month', $start_time);

				//createtime
				$where .= " createtime >= {$start_time} and createtime < {$end_time} ";
			}

			$total = M('core_attachment')->where($where)->count();

			$list = M('core_attachment')->where( $where )->order('id desc ')->limit($offset, $page_size)->select();


			if (!empty($list)) {
				foreach ($list as &$meterial) {
					$meterial['url'] = tomedia($meterial['attachment']);
					unset($meterial['uid']);
				}
			}

			$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
			$result = array('items' => $list, 'pager' => $pager);

			$json_data = array();
			$json_data['message'] = array(
									'errno' =>0,
									'message' => $result
								);
			echo json_encode( $json_data );
			die();
		}



		if ($do == 'delete') {
			$ids_arr = I('request.id');

			foreach($ids_arr as $material_id)
			{
				M('core_attachment')->where( array('id' => $material_id) )->delete();
			}

			echo '{"message":{"errno":"0","message":"\u5220\u9664\u7d20\u6750\u6210\u529f"},"redirect":"","type":"ajax"}';
			die();

		}

		if ($do == 'image') {

			$year = I('get.year');
			$month = I('get.month');
			$page = I('get.page',1);
			$groupid = I('get.groupid',0);


			$page_size = 10;
			$page = max(1, $page);

			$offset = ($page -1) * $page_size;

			$where = " type=1 ";

			if( !empty($groupid) && $groupid > 0 )
			{
			    $where .= " and group_id = {$groupid} ";
			}
			if($groupid == 0 ){
				$where .= " and group_id = -1 ";
			}

			if ($year || $month) {
				$start_time = strtotime("{$year}-{$month}-01");
				$end_time = strtotime('+1 month', $start_time);

				//createtime
				$where .= " and createtime >= {$start_time} and createtime < {$end_time} ";
			}

			$uid = 1;

			if(  is_agent_login() )
			{
				$uid = is_agent_login();
				$uid = $uid +1;
			}

			$where .= " and uid = {$uid} ";
			$total = M('core_attachment')->where($where)->count();

			$list = M('core_attachment')->where( $where )->order('id desc ')->limit($offset, $page_size)->select();


			if (!empty($list)) {
				foreach ($list as &$meterial) {
					$meterial['url'] = tomedia($meterial['attachment']);
					unset($meterial['uid']);
					//$core_data['filename'] = base64_encode($originname);
					if( $meterial['filename'] == base64_encode(base64_decode($meterial['filename'])) )
					{
						$meterial['filename'] = base64_decode($meterial['filename']);
					}
				}
			}

			$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
			$result = array('items' => $list, 'pager' => $pager);

			$json_data = array();
			$json_data['message'] = array(
									'errno' =>0,
									'message' => $result
								);
			echo json_encode( $json_data );
			die();
		}

		if('upload' == $do)
		{
			$dir='goods/';


			$type  =  I('get.upload_type');
			$type = in_array($type, array('image','audio','video')) ? $type : 'image';

			if (empty($_FILES['file']['name'])) {
				$result['message'] = '上传失败, 请选择要上传的文件！';
				die(json_encode($result));
			}
			if ($_FILES['file']['error'] != 0) {
				$result['message'] = '上传失败, 请重试.';
				die(json_encode($result));
			}
			$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			$ext = strtolower($ext);
			$size = intval($_FILES['file']['size']);
			$originname = $_FILES['file']['name'];
            // fix 20210825
			$upload = new \Think\Upload();// 实例化上传类

			$upload->maxSize   =     31457280 ;// 设置附件上传大小
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg','mp4');// 设置附件上传类型
			$upload->rootPath  =	 ATTACHMENT_ROOT.$dir;
			RecursiveMkdir($upload->rootPath);

			$info   =   $upload->upload();

			if(!$info) {
				$result['message'] = $file['message'];
				die(json_encode($result));
			}
			$filename = $dir.date('Y-m-d').'/'.$info['file']['savename'];

			$fullname = ATTACHMENT_ROOT . $filename;
			//ext
			if($ext == 'mp4'){
				if(filesize($fullname)>31457280){
					$result['message'] = '上传失败, 上传的视频应不大于30M！';
					die(json_encode($result));
				}
			}else{
				if(filesize($fullname)>10485760){
					$result['message'] = '上传失败, 上传的图片应不大于10M！';
					die(json_encode($result));
				}
			}


			//attachment_type

			$attachment_type_arr =  M('eaterplanet_ecommerce_config')->where( array('name' => 'attachment_type') )->find();

			if( $attachment_type_arr['value'] == 1 )
			{
				save_image_to_qiniu($fullname,'Uploads/image/'.$filename);
			}else if( $attachment_type_arr['value'] == 2 ){
				save_image_to_alioss($fullname,'Uploads/image/'.$filename);

			}else if( $attachment_type_arr['value'] == 3 )
			{
				save_image_to_txyun($fullname,'Uploads/image/'.$filename);
			}


			$group_id  =  I('get.group_id');


			$info = array(
				'name' => $originname,
				'ext' => $ext,
				'filename' => $filename,
				'attachment' => $filename,
				'url' => tomedia($filename),
				'is_image' => $type == 'image' ? 1 : 2,
				'filesize' => filesize($fullname),
				'group_id' => $group_id
			);


			$uid = 1;

			if(  is_agent_login() )
			{
				$uid = is_agent_login();
				$uid = $uid +1;
			}

			$core_data = array();
			$core_data['uniacid'] = 0;
			$core_data['uid'] = $uid;
			$core_data['filename'] = base64_encode($originname);
			$core_data['attachment'] = $filename;
			$core_data['type'] = $type == 'image' ? 1 : 2;
			$core_data['createtime'] = time();
			$core_data['module_upload_dir'] = '';
			$core_data['group_id'] = $group_id;

			M('core_attachment')->add($core_data);


			$size = getimagesize($fullname);
			$info['width'] = $size[0];
			$info['height'] = $size[1];

			$info['state'] = 'SUCCESS';
			die(json_encode($info));

		}

		if('image' == $do)
		{

		}



	}

	/**
	 *上传图片
	 */
	public function upload_image(){

		$dir=I('get.dir');
		$dir .= '/'.date('Y-m-d');

		$this->del_old_image();

		$upload = new \Think\Upload();// 实例化上传类

	    $image_dir=ROOT_PATH.'Uploads/image/'.$dir;

	    RecursiveMkdir($image_dir);

        $min_type_arr = array();
        $min_type_arr[] = 'image/jpeg';
        $min_type_arr[] = 'image/gif';
        $min_type_arr[] = 'image/jpeg';
        $min_type_arr[] = 'image/png';
        $min_type_arr[] = 'video/mp4';


	    $upload->autoSub   =	 false;
	    $upload->maxSize   =     3145728 ;// 设置附件上传大小
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $upload->mimes      =     $min_type_arr;// 设置附件上传类型
	    $upload->rootPath  =	 $image_dir.'/';

	    $info   =   $upload->upload();

		if(!$info) {
			$data['result'] = false;

		 	$this->ajaxReturn($data);
    	 }else{// 上传成功

    	 	$filename=$dir.'/'.$info['file']['savepath'].$info['file']['savename'];
			$data['image_thumb'] = resize($filename, 100, 100);
			$data['image'] = $filename;
    	    $this->ajaxReturn($data);

 		 }
	}

		//用于ckeditor图片上传
	function ckupload(){
	    $upload = new \Think\Upload();// 实例化上传类

	    $upload->maxSize   =     3145728 ;// 设置附件上传大小
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $upload->rootPath  =	 ROOT_PATH.'Uploads/image/goods_description/';
	    RecursiveMkdir($upload->rootPath);

	    $info   =   $upload->upload();
		 if(!$info) {
    		// 上传错误提示错误信息
    		echo "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction(".$_GET['CKEditorFuncNum'].", '/', '上传失败," . $upload->getError() . "！');</script>";
		 }else{// 上传成功
			$n=$_GET['CKEditorFuncNum'];
		 	$savepath=C('SITE_URL').'/Uploads/image/goods_description/'. $info['upload']['savepath'].$info['upload']['savename'];
       		//下面的输出，会自动的将上传成功的文件路径，返回给编辑器。
        	echo "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction(".$n.",'$savepath','');</script>";
	 }

	}

}
