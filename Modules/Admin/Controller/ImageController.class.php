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
namespace Admin\Controller;
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
		$dir .= '/'.date('Y-m-d');

		$this->del_old_image();

		$upload = new \Think\Upload();// 实例化上传类

	    $image_dir=ROOT_PATH.'Uploads/image/'.$dir;

	    RecursiveMkdir($image_dir);


	    $upload->autoSub   =	 false;
	    $upload->maxSize   =     3145728 ;// 设置附件上传大小
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
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
