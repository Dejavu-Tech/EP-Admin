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
namespace Admin\Controller;
use Think\Controller;
class MenuController extends CommonController {


	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='系统';
			$this->breadcrumb2='后台菜单配置';
	}

	function index(){

		$cate = M()->query('SELECT id,pid,title AS name FROM '.C('DB_PREFIX').'menu ORDER BY sort_order ASC');
		$list =list_to_tree($cate);
		$this->list=json_encode($list);

		$this->display();
	}



	function add(){

		if(IS_POST){

			$d['title']=I('title');
			$d['url']=I('url');
			$d['sort_order']=I('sort_order');
			$d['pid']=I('id');


			$id=M('Menu')->add($d);
			if($id){

				$data['name'] =$d['title'];
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
			$d['title']=I('title');
			$d['url']=I('url');
			$d['sort_order']=I('sort_order');

			$r=M('Menu')->save($d);

			if($r){

				$data['success']='修改成功';
				$data['name']=$d['title'];
				$this->ajaxReturn($data);

				die();
			}else{

				$data['err']='修改失败';

				$this->ajaxReturn($data);

				die();
			}
		}

	}

	function del(){
		if(IS_POST){
			$id=I('id');
			if(M('Menu')->where('id='.$id)->delete()){
				$this->ajaxReturn('删除成功');
				die();
			}
		}
	}

	function get_info(){
		if(IS_POST){
			$id=I('id');
			$d=M('Menu')->find($id);

			$data['title']=$d['title'];
			$data['url']=$d['url'];
			$data['sort_order']=$d['sort_order'];


			$this->ajaxReturn($data);
		}
	}

}
