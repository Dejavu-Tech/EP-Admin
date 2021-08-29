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
namespace Seller\Controller;
use Admin\Model\BlogModel;
class QuanController extends CommonController{

	protected function _initialize(){
		parent::_initialize();

			$this->breadcrumb1='营销活动';
			$this->breadcrumb2='动态圈子';
	}

	public function del_post_comment(){

		$id = I('get.id', 0);
		$group_lzl_reply = M('group_lzl_reply')->where( array('id'=>$id) )->find();

		M('group_lzl_reply')->where(  array('id'=>$id) )->delete();
		$res = M('group_post')->where( array('id' => $group_lzl_reply['post_id']) )->setDec('reply_count',1);

		$http_refer = $_SERVER['HTTP_REFER'];
	    if($res) {
	        $return = array(
	            'status'=>'success',
	            'message'=>'删除成功',
	            'jump'=>$http_refer
	        );
	    } else {
	        $return = array(
	            'status'=>'fail',
	            'message'=>'删除失败',
	            'jump'=>$http_refer
	        );
	    }
		$this->osc_alert($return);

	}
	public function del_post()
	{

		$id = I('get.id', 0);


	    $rs = M('group_post_fav')->where(  array('post_id'=>$id) )->delete();


	    M('group_lzl_reply')->where(  array('post_id'=>$id) )->delete();
	    $res = M('group_post')->where(  array('id'=>$id) )->delete();

		$http_refer = $_SERVER['HTTP_REFER'];
	    if($res) {
	        $return = array(
	            'status'=>'success',
	            'message'=>'删除成功',
	            'jump'=>$http_refer
	        );
	    } else {
	        $return = array(
	            'status'=>'fail',
	            'message'=>'删除失败',
	            'jump'=>$http_refer
	        );
	    }
		$this->osc_alert($return);
	}

	public function coments()
	{
		//post_id/10
		$post_id = I('get.post_id');
		$model=new BlogModel();

		$search = array('post_id' => $post_id);

		$data=$model->show_quan_lzy_page($search);

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->display('comment');
	}
	public function index(){
		$model=new BlogModel();
		$group_info = M('group')->where( array('seller_id' => SELLERUID) )->find();

		$has_group = true;
		$data = array();

		if( empty($group_info) )
		{
			$search = array('group_id' => 0);
			$data=$model->show_quan_page($search);
		}else{
			$search = array('group_id' => $group_info['id']);
			$data=$model->show_quan_page($search);
		}


		$this->has_group = $has_group;

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->type = -1;
		$this->display();
	}

	public function config()
	{
		if(IS_POST){
			$save_data = array();
			$save_data['title'] = I('post.title');
			$save_data['quan_logo'] = I('post.quan_logo');
			$save_data['quan_banner'] = I('post.quan_banner');
			$save_data['quan_share'] = I('post.quan_share');
			$save_data['quan_share_desc'] = I('post.quan_share_desc');
			$save_data['status'] = I('post.status');
			$save_data['member_ids'] = I('post.member_ids');
			$save_data['is_synchro'] = I('post.is_synchro');
			$save_data['limit_send_member'] = I('post.limit_send_member');

			M('group')->where( array('seller_id' => SELLERUID) )->save($save_data);

			$return = array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('Quan/config')
			);
			$this->osc_alert($return);
		}

		$data = M('group')->where( array('seller_id' => SELLERUID) )->find();
		$member_data = array();
		if( empty($data) )
		{
			$this->_new_quanzi();
			$data = M('seller')->where( array('s_id' => SELLERUID) )->find();
		}else{
			$member_ids = $data['member_ids'];
			if( !empty($member_ids) )
			{
				$member_data = M('member')->field('member_id,uname')->where( array('member_id' => array('in', $member_ids) ) )->select();

			}
		}
		$this->member_data = $member_data;
		$this->type = 1;
		$this->data = $data;
		$this->display();
	}

	private function _new_quanzi()
	{
		$group_data = array();
		$group_data['seller_id'] = SELLERUID;
		$group_data['title'] = '';
		$group_data['post_count'] = 0;
		$group_data['status'] = 0;
		$group_data['member_count'] = 0;
		$group_data['quan_share'] = '';
		$group_data['quan_logo'] = '';
		$group_data['quan_banner'] = '';
		$group_data['quan_share_desc'] = '';
		$group_data['create_time'] = time();

		M('group')->add($group_data);
	}

	public function save_config()
	{
		$this->action=U('Seller/edit');
	}

	function showdetail(){
		$blog_id = I('get.blog_id',0,'intval');

		$blog_seller_order = M('blog_seller_order')->where( array('blog_id' =>$blog_id,'seller_id' =>SELLERUID) )->find();

		if( empty($blog_seller_order) )
		{
			$this->blog_not_count = $this->blog_not_count-1;
			M('blog_seller_order')->add(array('blog_id' =>$blog_id,'seller_id' =>SELLERUID,'addtime' =>time()));
		}

		$blog_info    = M('blog')->where( array('blog_id' => $blog_id) )->find();
		$blog_content = M('blog_content')->where( array('blog_id' => $blog_id) )->find();

		$this->blog_info = $blog_info;
		$this->blog_content = $blog_content;
		$this->display();
	}


}
?>
