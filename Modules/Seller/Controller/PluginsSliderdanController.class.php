<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.cloud/
 * @copyright Copyright (c) 2019-2024 Dejavu Tech.
 * @license   https://github.com/Dejavu-Tech/EP-Admin/blob/main/LICENSE
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Seller\Controller;
use Admin\Model\PluginsSliderModel;
class PluginsSliderdanController extends CommonController {
   	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='插件';
		$this->breadcrumb2='广告位';
		$this->type_config = array('index_ad_nav' => '首页导航','index_ad_head' => '首页滚动广告',
		'index_ad_list' => '首页横条广告','pc_ad_head'=>'PC首页头部广告',
		'pc_ad_scroll'=>'PC首页滚动广告','pc_news' =>'PC关于我们广告图','index_wepro_head' =>'小程序首页滚动广告',
		'index_wepro_line' => '小程序首页横向广告', 'pin_index_ad' =>'拼团首页单图',
		'lottery_wepro_head' =>'小程序抽奖首页单图','newman_wepro_head' =>'小程序老带新首页单图',
		'index_wepro_iconnav' =>'小程序首页小图标导航',
		'index_wepro_ziying_line' => '小程序自营首页横条广告','paihang_wepro_head' => '小程序自营排行横条广告',
		'new_wepro_head' => '小程序自营新品横条广告'
		);
	}
    public function index(){
		$model=new PluginsSliderModel();

		$data=$model->show_slider_page();

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	}

	function add(){

		if(IS_POST){

			$model=new PluginsSliderModel();
			$data=I('post.');
			$return=$model->add_slider($data);
			$this->osc_alert($return);
		}

		$this->crumbs='新增';
		$this->action=U('PluginsSliderdan/add');
		$this->display('edit');
	}

	function edit(){
		if(IS_POST){
			$model=new PluginsSliderModel();
			$data=I('post.');

			$return=$model->edit_slider($data);

			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('PluginsSlider/edit');
		$this->slider=M('PluginsSlider')->find(I('id'));
		$this->thumb_image=resize($this->slider['image'], 100, 100);
		$this->display('edit');
	}
	public function del(){
		$r=M('PluginsSlider')->delete(I('id'));
		if($r){
			$this->redirect('PluginsSlider/index');
		}
	}
}
