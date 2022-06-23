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
namespace Admin\Model;
class PluginsSliderModel{

	public function show_slider_page(){

		$count=M('plugins_slider')->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('plugins_slider')->where(array('status'=>1))->order('slider_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();


		foreach ($list as $key => $value) {
			$list[$key]['image']=resize($value['image'], 100, 100);
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function validate($data){

		$error=array();
		if(empty($data['image'])){
			$error='图片必须';
		}

		if($error){

			return array(
				'status'=>'back',
				'message'=>$error
			);

		}
	}

		public function add_slider($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('PluginsSlider')->add($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('PluginsSlider/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('PluginsSlider/index')
				);
			}


	}

	public function edit_slider($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('PluginsSlider')->save($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('PluginsSlider/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('PluginsSlider/index')
				);
			}


	}

}
?>
