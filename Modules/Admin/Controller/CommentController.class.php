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
use Admin\Model\CommentModel;
class CommentController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='系统';
		$this->breadcrumb2='访客留言';
	}

	public function index(){
		$model=new CommentModel();

		$data=$model->show_comment_page();

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		/**/
		$this->display();
	}

}
?>
