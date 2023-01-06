<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.io/
 * @copyright Copyright (c) 2019-2023 Dejavu Tech.
 * @license   https://e-p.io/license
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Seller\Controller;
class ImageController extends CommonController{

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

	    $image = new \Think\Image();
	    $image->open($image_dir.$file_name);
	    //按照原图的比例生成一个最大为400*400的缩略图并保存为thumb.jpg, 实际会按比例自动缩放
	    $image->thumb(400, 400)->save($image_dir.$thumb_image_name);

		////{"filePath":"\/Uploads\/image\/goods\/2017-07-05\/","fileName":"7e414de26624c0a5ac7cd5b9bd5edfe3.jpg"}

	    $result = array('filePath' =>$file_path ,'fileName' => $file_name);

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
	/**
	 *上传图片
	 */
	public function upload_image(){

		$dir=I('get.dir');
		$org_dir = $dir;

		$dir .= '/'.date('Y-m-d');


		$this->del_old_image();

		$upload = new \Think\Upload();// 实例化上传类

	    $image_dir=ROOT_PATH.'Uploads/image/'.$dir;

	    RecursiveMkdir($image_dir);
	   //1048576 1M

	    $upload->autoSub   =	 false;
	    $upload->maxSize   =     0;// 设置附件上传大小
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg','mp4');// 设置附件上传类型
	    $upload->rootPath  =	 $image_dir.'/';

	    $info   =   $upload->upload();


		if(!$info) {
			$data['code'] = 1;
		 	$this->ajaxReturn($data);
    	 }else if($org_dir == 'video')
		 {
			$filename=$dir.'/'.$info['file']['savepath'].$info['file']['savename'];
			$data['image_thumb'] = '/assets/images/video.jpg';
			$data['image'] = $filename;
			$data['code'] = 0;
    	    $this->ajaxReturn($data);
		 }
		 else{// 上传成功

    	 	$filename=$dir.'/'.$info['file']['savepath'].$info['file']['savename'];
			$data['image_thumb'] = resize($filename, 100, 100);
			$data['image'] = $filename;
			$data['code'] = 0;
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
