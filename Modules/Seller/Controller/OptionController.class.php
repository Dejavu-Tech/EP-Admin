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
use Admin\Model\OptionModel;
class OptionController extends CommonController{



	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='商品管理';
			$this->breadcrumb2='商品规格';
	}

	function index(){

		$model=new OptionModel();

		$data=$model->show_option_page('store_id='.SELLERUID);

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	}

	protected function validate_form() {

		$data=I('post.');

		$error=array();
		if ((utf8_strlen($data['name']) <1) || (utf8_strlen($data['name']) > 10)) {
			$error['error'] = '选项名称必须大于1小于10个字！！';
		}elseif (($data['type'] == 'select' || $data['type'] == 'radio' || $data['type'] == 'checkbox'|| $data['type'] == 'image') && !isset($data['option_value'])) {
			$error['error'] ='选项值必填！！';
		}elseif (isset($data['option_value'])) {
			foreach ($data['option_value'] as $option_value_id => $option_value) {
				if ((utf8_strlen($option_value['name']) < 1) || (utf8_strlen($option_value['name']) > 10)) {
					$error['error'] ='选项值必须大于0小于10个字！！';
				}
			}
		}
		if($error){
			$this->ajaxReturn($error);
			die;
		}

	}

	function add(){

		if(IS_POST){
			$this->validate_form();

			$model=new OptionModel();
			$data=I('post.');
			$data['store_id'] = SELLERUID;

			$return=$model->add_option($data);
			if($return){
				$r['redirect']=U('Option/index');
				$this->ajaxReturn($r);
				die;
			}else{
				$error['error']='新增失败';
				$this->ajaxReturn($error);
				die;
			}

		}

		$this->crumbs='新增';
		$this->action=U('Option/add');
		$this->display('edit');
	}

	function edit(){

		if(IS_POST){
			$this->validate_form();

			$model=new OptionModel();
			$data=I('post.');
			//dump($data);
			$return=$model->edit_option($data);
			if($return){
				$r['redirect']=U('Option/index');
				$this->ajaxReturn($r);
				die;
			}else{
				$error['error']='编辑失败';
				$this->ajaxReturn($error);
				die;
			}

		}
		$this->option=M('Option')->find(I('id'));
		$this->option_values=M('OptionValue')->where(array('option_id'=>I('id')))->select();

		$this->crumbs='编辑';
		$this->action=U('Option/edit');
		$this->display();
	}

	function del(){
		M('option')->delete(I('id'));
		M('option_value')->where(array('option_id'=>I('id')))->delete();
		$this->redirect('Option/index');
	}

	function get_goodsajax_option_value()
	{
		$goods_id = I('post.goods_id');
		$goods_option =   M('goods_option')->where( array('goods_id' => $goods_id) )->select();

		$result = array('code' => 0);
		if( !empty($goods_option) )
		{
			foreach($goods_option as $key => $val)
			{
				$goods_option_value_list = M('option_value')->where( array('option_id' => $val['option_id']) )->select();

				foreach($goods_option_value_list as $kk => $vv)
				{
					// $vv['option_value_id']

					$has_check_option_value_list = M('goods_option_value')->where( array('option_value_id' => $vv['option_value_id'],'goods_id' =>$goods_id ) )->select();

					if( !empty($has_check_option_value_list) )
					{
						$vv['selected'] = 'selected';
					} else{
						$vv['selected'] = '';
					}

					$goods_option_value_list[$kk] = $vv;
				}

				$val['goods_option_value_list'] = $goods_option_value_list;
				$goods_option[$key] = $val;
			}
			$result['code'] = 1;
			$result['data'] = $goods_option;
		}

		echo json_encode($result);
		die();
	}

	function get_ajax_option_value()
	{
		//option_value
		$option_id = I('post.option_id');

		$option_value_list = M('option_value')->where( array('option_id' =>$option_id) )->order('value_sort_order asc')->select();

		echo json_encode( array('code' =>1 , 'data' => $option_value_list) );
		die();
	}

		//获取选项
	function autocomplete(){
		$json = array();

		$filter_name=I('filter_name');

		if (isset($filter_name)) {
			//$m=D('Product');
			$m=new OptionModel();
			//getOptions
			$options = $m->getOptions($filter_name,SELLERUID);

			foreach ($options as $option) {
				$option_value_data = array();

				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox') {
					$option_values = $m->getOptionValues($option['option_id']);

					foreach ($option_values as $option_value) {

						$option_value_data[] = array(
							'option_value_id' => $option_value['option_value_id'],
							'name'            => html_entity_decode($option_value['value'], ENT_QUOTES, 'UTF-8'),
							'image'           => $option_value['image']
						);
					}

					$sort_order = array();

					foreach ($option_value_data as $key => $value) {
						$sort_order[$key] = $value['name'];
					}

					array_multisort($sort_order, SORT_ASC, $option_value_data);
				}

				$type = '';

				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' ) {
					$type = '选择';
				}

				$json[] = array(
					'option_id'    => $option['option_id'],
					'name'         => strip_tags(html_entity_decode($option['name'], ENT_QUOTES, 'UTF-8')),
					'category'     => $type,
					'type'         => $option['type'],
					'option_value' => $option_value_data
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		echo(json_encode($json));
	}


}
?>
