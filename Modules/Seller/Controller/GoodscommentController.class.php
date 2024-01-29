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
use Seller\Model\GoodscommentModel;
class GoodscommentController extends CommonController{



	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='商品管理';
			$this->breadcrumb2='商品评价管理';
	}

	function index(){

		$name = I('get.name','');
		$order_num_alias = I('get.order_num_alias','1');


		$search = array();
		if( !empty($name) )
		{
			$search['goods_name'] = $name;
		}
		if( !empty($order_num_alias) )
		{
			$search['order_num_alias'] = $order_num_alias;
		}

		$model=new GoodscommentModel();

		$data=$model->show_comment_page($search);

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	}


	public function toggle_state_show()
	{
		$comment_id = I('post.gid');

		$order_comment_info = M('order_comment')->field('state')->where( array('comment_id' => $comment_id) )->find();
		$state = 1;
		if($order_comment_info['state'] == 1)
		{
			$state = 0;
		}
		M('order_comment')->where( array('comment_id' => $comment_id) )->save( array('state' => $state) );
		echo json_encode( array('code' => 0) );
		die();
	}
	public function backhuiche()
	{
		$comment_id = I('get.id');

		M('order_comment')->where(array('comment_id'=>$comment_id ))->delete();

		echo json_encode( array('code' => 0) );
		die();
		$ref_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']:U('Goodscomment/index');
		$result = array(
	        'status'=>'success',
	        'message'=>'删除成功',
	        'jump'=>$ref_url
	    );
	    $this->osc_alert($result);
	}


	function add(){

		if(IS_POST){


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

		$this->breadcrumb2='商品评价管理';
		$this->display('edit');
	}

	function get_member_ajax()
	{
		$keywords = I('post.keywords','');
		$page = I('post._pa',1);

		$perpage = 20;
		$offset = ( $page -1 ) * $perpage;

		$where = " 1=1 ";
		if( !empty($keywords) )
		{
			$where .= " and uname like '%".$keywords."%'";
		}
		$jia_list = M('member')->where( $where )->order('member_id desc')->limit($offset,20)->select();

		$this->page = $page;
		$this->jia_list = $jia_list;

		$html = $this->fetch('Goodscomment:fetch_comment_member_ajax_list');

		if( empty($jia_list) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}else{
			echo json_encode( array('code' => 0 , 'html' => $html) );
			die();
		}

	}
	function get_jiqi_ajax()
	{
		$keywords = I('post.keywords','');
		$where = " 1=1 ";
		if( !empty($keywords) )
		{
			$where .= " and username like '%".$keywords."%'";
		}
		$jia_list = M('jiauser')->where( $where )->order('id desc')->limit(20)->select();

		$this->jia_list = $jia_list;
		$html = $this->fetch('Goodscomment:fetch_comment_ajax_list');

		echo json_encode( array('code' => 0 , 'html' => $html) );
		die();
	}

	function fetch_comment_ajax()
	{
		//.htmlSeller\Goodscomment\
		$html = $this->fetch('Goodscomment:fetch_comment_ajax');
		echo json_encode( array('code' => 0, 'html' => $html) );
		die();
	}

	public function save_vir_comment()
	{
		//{goods_id_str:goods_id_str,jia_id:jia_id,star:star,star2:star2,star3:star3,content:content},

		$goods_id_str = I('post.goods_id_str','');
		if(empty($goods_id_str))
		{
			echo json_encode( array('code' => 1, 'msg' => '请选择评价的商品') );
			die();
		}
		$goods_id_arr = explode(',', $goods_id_str);
		$jia_id = I('post.jia_id',0);
		if( empty($jia_id) || $jia_id <= 0 )
		{
			echo json_encode( array('code' => 1, 'msg' => '请选择评价的虚拟客户') );
			die();
		}

		$begin_time = I('post.begin_time', '');
		$star = I('post.star', 0);
		$star2 = I('post.star2', 0);
		$star3 = I('post.star3', 0);
		$content = I('post.content', 0);
		$s_imgs_str = I('post.s_imgs_str', '');
		$s_imgs_arr = explode(',', $s_imgs_str);

		$is_picture = 0;
		if( !empty($s_imgs_arr) )
		{
			$is_picture = 1;
		}

		//order_comment,
		//order_id=0,goods_id state=1,member_id=jia_id,avatar=jia_avatar user_name=jia_name,
		//order_num_alias=0,//type=1,star,star3,star2, is_picture=0/1,content  ,add_time

		//goods_name,goods_image,
		//,
		$jia_info = M('jiauser')->where( array('id' => $jia_id) )->find();
		$commen_data = array();
		$commen_data['order_id'] = 0;
		$commen_data['state'] = 1;
		$commen_data['type'] = 1;
		$commen_data['member_id'] = $jia_id;
		$commen_data['avatar'] = $jia_info['avatar'];
		$commen_data['user_name'] = $jia_info['username'];
		$commen_data['order_num_alias'] = 1;
		$commen_data['star'] = $star;
		$commen_data['star3'] = $star3;
		$commen_data['star2'] = $star2;
		$commen_data['is_picture'] = $is_picture;
		$commen_data['content'] = $content;
		$commen_data['images'] = serialize($s_imgs_arr);

		$i =1;
		$quan_model = D('Home/Quan');

		foreach($goods_id_arr as $goods_id)
		{
			$commen_data['goods_id'] = $goods_id;
			$commen_data['add_time'] = strtotime($begin_time);

			$goods_info = M('goods')->field('name,image')->where( array('goods_id' => $goods_id) )->find();
			$commen_data['goods_name'] = $goods_info['name'];
			$commen_data['goods_image'] = $goods_info['image'];
			$rs = M('order_comment')->add($commen_data);


			$post_data = array();
			$post_data['member_id'] = $jia_id;
			$post_data['group_id'] = 1;
			$post_data['is_vir'] = 1;

			$post_data['avatar'] = $commen_data['avatar'];
			$post_data['user_name'] = $commen_data['user_name'];


			$post_data['goods_id'] = $goods_id;
			$post_data['title'] = $content;
			$post_data['is_share'] = 1;
			$post_data['content'] = $commen_data['images'];

			$rs =  $quan_model->send_group_post($post_data);

			$i++;
		}

		$ref_url = U('Goodscomment/index');

		echo json_encode( array('code' => 0, 'ref_url' => $ref_url) );
		die();
	}



	function get_goods_all()
	{
		$html = $this->fetch('Goodscomment:fetch_goods_ajax');
		echo json_encode( array('code' => 0 , 'html' => $html) );
		die();
	}
	function get_jiqi()
	{
		$html = $this->fetch('Goodscomment:fetch_comment_ajax');
		echo json_encode( array('code' => 0 , 'html' => $html) );
		die();
	}
	function get_member()
	{
		$html = $this->fetch('Goodscomment:fetch_comment_member_ajax');
		echo json_encode( array('code' => 0 , 'html' => $html) );
		die();
	}

	function edit(){

		if(IS_POST){

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
