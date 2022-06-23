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
namespace Admin\Controller;

class GoodsCategoryController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='商品';
			$this->breadcrumb2='商品分类';
	}

	public function index(){

		$sql='SELECT id,pid,name,logo FROM '
		.C('DB_PREFIX').'goods_category';

		$cate = M()->query($sql);

		foreach($cate as $key => $val)
		{
		    $val['name'] = $val['name'].'( id = '.$val['id'].' )';
		    $cate[$key] = $val;
		}

		$list =list_to_tree($cate);
		$this->list=json_encode($list);

		$this->display();
	}
	function add(){

		if(IS_POST){

		    $d = array();
			$d['name']=I('name');
			$d['pid']=I('id');
			$d['sort_order']=I('sort_order');
			$d['c_sort_order']=I('c_sort_order');

			$d['is_search'] = I('is_search');
			$d['is_hot'] = I('is_hot');
			$d['is_haitao'] = I('is_haitao');
			$d['logo'] = I('image');


			$id=M('goods_category')->add($d);
			if($id){

				$data['name'] =$d['name'];
				$data['id']=$id;
				$this->ajaxReturn($data);

				die();
			}else{

				die();
			}
		}

	}

		function edit(){
		if(IS_POST){

			$d['id']=I('id');
			$d['name']=I('name');
			$d['sort_order']=I('sort_order');
			$d['c_sort_order']=I('c_sort_order');
			$d['logo'] = I('image');
			$d['is_search'] = I('is_search');
			$d['is_hot'] = I('is_hot');
			$d['is_haitao'] = I('is_haitao');

			$category=M('goods_category')->find($d['id']);

			$r=M('goods_category')->save($d);

			if($r){

				$data['success']='修改成功';
				$data['name']=$d['name'];
				$this->ajaxReturn($data);

				die();
			}else{

				$data['err']='修改失败';

				$this->ajaxReturn($data);

				die();
			}
		}
	}

	function get_info(){
		if(IS_POST){
			$id=I('id');
			$d=M('goods_category')->find($id);

			$data['name']=$d['name'];
			$data['logo']=$d['logo'];
			$data['is_search'] = $d['is_search'];
			$data['is_hot']=$d['is_hot'];
			$data['is_haitao']=$d['is_haitao'];
			$data['c_sort_order']=$d['c_sort_order'];

			if(!empty($d['logo']))
			{
			    $data['thumb_image'] = resize($d['logo'], 100, 100);
			}else {
			    $data['thumb_image'] = '';
			}

			$data['sort_order']=$d['sort_order'];

			$this->ajaxReturn($data);
		}
	}
	function del(){
		if(IS_POST){
			$id=I('post.id');

			if(M('goods_category')->where('pid='.$id)->find()){
				$data['err']='请先删除子节点！！';
				$this->ajaxReturn($data);
				die;
			}

			$res1 = M('goods_to_category')->where(array('class_id1'=>$id))->find();
			$res2 = M('goods_to_category')->where(array('class_id2'=>$id))->find();
			$res3 = M('goods_to_category')->where(array('class_id3'=>$id))->find();

			if( !empty($res1) || !empty($res2) || !empty($res3)){
				$data['err']='请先删除该分类下商品！！';
				$this->ajaxReturn($data);
				die;
			}

			if(M('goods_category')->where('id='.$id)->delete()){
				$data['success']='删除成功';
				$this->ajaxReturn($data);
				die();
			}
		}
	}

	function autocomplete(){
		$json = array();

		$filter_name=I('filter_name');

		if (isset($filter_name)) {
			$sql='SELECT id,name FROM '.c('DB_PREFIX')."goods_category where name LIKE'%".$filter_name."%' LIMIT 0,20";
		}else{
			$sql='SELECT id,name FROM '.c('DB_PREFIX')."goods_category LIMIT 0,20";

		}
			$results = M('goods_category')->query($sql);

		foreach ($results as $result) {
			$json[] = array(
				'category_id' => $result['id'],
				'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
			);
		}

		$this->ajaxReturn($json);
	}




}
?>
