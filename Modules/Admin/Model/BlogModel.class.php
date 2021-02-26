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
namespace Admin\Model;
use Think\Model;
class BlogModel extends Model{

	public function del_blog($id){
		try{

			$image=M('Blog')->where(array('blog_id'=>$id))->field('image')->find();
			if(!empty($image)){
				A('Image')->del_image('blog',$image['image'],'blog');
			}

			$gallery=M('blog_image')->where(array('blog_id'=>$id))->field('image')->select();

			if(!empty($gallery)){
				foreach ($gallery as $key => $value) {
					A('Image')->del_image('blog_gallery',$value['image'],'blog_gallery');
				}
			}


			M('Blog')->where(array('blog_id'=>$id))->delete();
			M('blog_content')->where(array('blog_id'=>$id))->delete();
			M('blog_image')->where(array('blog_id'=>$id))->delete();


			return array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('Blog/index')
				);

		}catch(Exception $e){
			return array(
				'status'=>'fail',
				'message'=>'删除失败,未知异常',
				'jump'=>U('Blog/index')
			);
		}
	}
	//修改时，取得博客图片
	public function get_blog_data($id){

		$d=M('Blog')->find($id);

		$d['thumb_image']=resize($d['image'], 100, 100);

		return $d;

	}
	//修改时，取得博客图册图片
	public function get_blog_image_data($id){

		$d=M('blog_image')->where(array('blog_id'=>$id))->select();

		foreach ($d as $k => $v) {
			$d[$k]['thumb']=resize($v['image'], 100, 100);
		}

		return $d;

	}
	//修改时，取得博客分类
	public function get_blog_category_data($id){

		$sql='SELECT bc.title,bc.id FROM '.C('DB_PREFIX').'blog_category bc,'
		.C('DB_PREFIX').'blog b WHERE bc.id=b.category_id AND b.blog_id='.$id;

		$d=M()->query($sql);

		return $d[0];

	}

	public function show_blog_page($search = array()){

		$sql='SELECT * FROM '.C('DB_PREFIX').'blog ';

		if(!empty($search))
		{
			if( isset($search['type'])  && $search['type'] == 'seller')
			{
				$sql .= " where type='".$search['type']."' ";
			}
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by blog_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);


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
			if(empty($data['title'])){
				$error='标题必填';
			}

			if($error){

				return array(
					'status'=>'back',
					'message'=>$error
				);

			}
	}

	public function edit_blog($data){
		$error=$this->validate($data);

			if($error){
				return $error;
			}

			$blog_id=$data['blog_id'];

			$blog['blog_id']=$blog_id;
			$blog['title']=$data['title'];
			$blog['author']=$data['author'];
			$blog['image']=$data['image'];
			$blog['type']=$data['type'];
			$blog['category_id']=1;
			$blog['allow_reply']=$data['allow_reply'];
			$blog['meta_description']=$data['meta_description'];
			$blog['meta_keywords']=$data['meta_keywords'];
			$blog['status']=$data['status'];
			$blog['update_time']=date('Y-m-d H:i:s',time());

			$r=M('Blog')->save($blog);

			if($r){

				try{

					M('blog_content')->where(array('blog_id'=>$blog_id))->delete();

					$blog_content['blog_id']=$blog_id;
					$blog_content['summary']=$data['summary'];
					$blog_content['content']=$data['content'];
					M('blog_content')->add($blog_content);



					return array(
						'status'=>'success',
						'message'=>'修改成功',
						'blog_id' => $blog_id,
						'jump'=>U('Blog/index')
					);
				}catch(Exception $e){
					return array(
					'status'=>'fail',
					'message'=>'修改失败,未知异常',
					'jump'=>U('Blog/index')
					);
				}

			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('Blog/index')
				);
			}

	}

	public function show_quan_page($search = array())
	{

		//group_id
		$sql='SELECT * FROM '.C('DB_PREFIX').'group_post ';

		if(!empty($search))
		{
			if( isset($search['group_id'])  )
			{
				$sql .= " where group_id='".$search['group_id']."' ";
			}
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);


		foreach ($list as $key => $value) {
				//member_id uname avatar
			$list[$key]['title2'] =  htmlspecialchars_decode($value['title']);

			if($value['is_vir'] == 1)
			{
				$list[$key]['uname'] = 	$value['user_name'];
			}else{

				$member_info = M('member')->field('uname,avatar')->where( array('member_id' => $value['member_id']) )->find();
				$list[$key]['uname'] = 	$member_info['uname'];
				$list[$key]['avatar'] =  $member_info['avatar'];
			}

			$list[$key]['create_time'] =  date('Y-m-d H:i:s', $value['create_time']);
			$content_arr = unserialize( $value['content'] );

			$content_arr = unserialize( $value['content'] );
			$list[$key]['contents'] = $content_arr;
			//$list[$key]['contents']=resize($value['image'], 100, 100);
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	function show_quan_lzy_page($search)
	{
		//group_id
		$sql='SELECT * FROM '.C('DB_PREFIX').'group_lzl_reply ';

		if(!empty($search))
		{
			if( isset($search['post_id'])  )
			{
				$sql .= " where post_id='".$search['post_id']."' ";
			}
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by id desc LIMIT '.$Page->firstRow.','.$Page->listRows;


		$list=M()->query($sql);


		foreach ($list as $key => $value) {
				//member_id uname avatar
			$list[$key]['content'] =  htmlspecialchars_decode($value['content']);

			$member_info = M('member')->field('uname,avatar')->where( array('member_id' => $value['member_id']) )->find();
			$list[$key]['uname'] = 	$member_info['uname'];
			$list[$key]['avatar'] =  $member_info['avatar'];
			$list[$key]['create_time'] =  date('Y-m-d H:i:s', $value['create_time']);
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}

	 function add_blog($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$blog['title']=$data['title'];
			$blog['author']=$data['author'];
			$blog['image']=$data['image'];
			$blog['type']=$data['type'];

			$blog['category_id']=1;
			$blog['allow_reply']=1;
			$blog['meta_description']=$data['meta_description'];
			$blog['meta_keywords']=$data['meta_keywords'];
			$blog['status']=$data['status'];
			$blog['create_time']=date('Y-m-d H:i:s',time());

			$blog_id=M('Blog')->add($blog);


			if($blog_id){

				try{
					$blog_content['blog_id']=$blog_id;
					$blog_content['summary']=$data['summary'];
					$blog_content['content']=$data['content'];


					M('blog_content')->add($blog_content);


					return array(
						'status'=>'success',
						'message'=>'新增成功',
						'blog_id' => $blog_id,
						'jump'=>U('Blog/index')
					);
				}catch(Exception $e){
					return array(
					'status'=>'fail',
					'message'=>'新增失败',
					'jump'=>U('Blog/index')
					);
				}
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('Blog/index')
				);
			}


	}
}
?>
