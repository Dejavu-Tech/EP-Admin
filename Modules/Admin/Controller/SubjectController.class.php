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
namespace Admin\Controller;
use Admin\Model\SubjectModel;
class SubjectController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='营销活动';
		$this->breadcrumb2='主题活动';
		$this->subjecttype = array('normal' => '正常活动',
		//'zeyuan' => '0元试用','niyuan' => '9.9元','oneyuan' => '1元购','haitao' => '海淘'
		);
	}

	public function index(){
		$model=new SubjectModel();
		$type = I('get.type','normal');

		$search = " where type = '{$type}'";
		$data=$model->show_subject_page($search);

		foreach($data['list'] as $key => $val)
		{
			$wait_goods = M('subject_goods')->where( array('subject_id' => $val['id'], 'state' => 0) )->count();
			$on_goods = M('subject_goods')->where( array('subject_id' => $val['id'], 'state' => 1) )->count();

			$val['wait_goods'] = $wait_goods;
			$val['on_goods'] = $on_goods;
			$data['list'][$key] = $val;
		}

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->state = $state;
		$this->type = $type;
		$this->display();
	}

	function toggle_statues_show()
	{
		$id = I('post.gid',0);
        $goods_info =M('subject')->where( array('id' => $id) )->find();

        $is_hot = $goods_info['is_hot'] == 1 ? 0: 1;

        $res = M('subject')->where( array('id' => $id) )->save( array('is_hot' => $is_hot) );
        echo json_encode( array('code' => 1) );
        die();
	}


	public function add()
	{
		if(IS_POST){

			$data = array();
			$data['name'] = trim(I('post.name'));
			$data['type'] = I('post.type');
			$data['logo'] = trim(I('post.logo'));
			$data['banner'] = trim(I('post.banner'));
			$data['price'] =floatval(I('post.price'));
			$data['begin_time'] = strtotime( I('post.begin_time') );
			$data['end_time'] = strtotime( I('post.end_time') );
			$data['add_time'] = time();

			$rs = M('subject')->add($data);
			 if (!$rs) {
		        	$status = array('status'=>'back','message'=>'添加失败');
		            $this->osc_alert($status);
		     }
		     $status = array('status'=>'success','message'=>'添加活动成功！','jump'=>U('Subject/index'));
		     $this->osc_alert($status);
		}
		$this->display();
	}

	/**
		报名主题活动
	**/
	public function take_subject()
	{
		$id = I('get.id','0');

		$subject = M('subject')->where( array('id' => $id) )->find();

		$this->subject = $subject;

		$this->display();
	}
	/**
		提交主题活动申请
	**/
	public function sub_subject()
	{
		$subject_id = I('get.id');
		$data = I('post.goods_ids_arr');
		$result = array('code' => 0);

		if( empty($data))
		{
			$result['msg'] = '未选中商品';
			echo json_encode($result);
			die();
		}

		$subject = M('subject')->where( array('id' => $subject_id) )->find();

		//type  begin_time end_time  price

		foreach($data as $goods_id)
		{
			$super_data  = array();

			$goods_info = M('goods')->field('store_id')->where( array('goods_id' => $goods_id) )->find();

			$super_data['subject_id'] = $subject_id;
			$super_data['goods_id'] = $goods_id;
			$super_data['state'] = 1;
			$super_data['begin_time'] = $subject['begin_time'];
			$super_data['end_time'] = $subject['end_time'];
			$super_data['seller_id'] = $goods_info['store_id'];
			$super_data['addtime'] = time();

			$rs = M('subject_goods')->add($super_data);

			if($rs) {
				if($subject['type'] =='normal')
				{
					$subject['type'] = 'subject';
				}
				$up_data = array('type' =>$subject['type'],'status' => 1);


				M('goods')->where( array('goods_id' => $goods_id) )->save( $up_data );
			}
		}
		$result['code'] = 1;
		echo json_encode($result);
		die();
	}

	public function shenhe()
	{
	    $result = array('code' => 0);
	    $id = I('get.id',0);
	    $subject_goods = M('subject_goods')->where( array('id' => $id) )->find();

	    if($subject_goods){
	        $subject = M('subject')->where( array('id' => $subject_goods['subject_id']) )->find();

	        //`type` enum('normal','zeyuan','niyuan','oneyuan') DEFAULT NULL COMMENT
	        //'活动类型，normal正常活动，zeyuan0元试用，niyuan9.9元，oneyuan1元购',
	        if($subject['type'] =='normal'){
	            $subject['type'] = 'subject';
	        }
	        $rs = M('subject_goods')->where(array('id' => $id) )->save( array('state' => 1) );
	        if($rs) {
	            $goods_arr = array('type' => $subject['type']);
				$goods_arr['status'] = 1;
	            if($subject['price'] != -1){
	                $goods_arr['lock_price'] = 1;
	                $goods_arr['pinprice'] = $subject['price'];
	            }
	            M('goods')->where( array('goods_id' => $subject_goods['goods_id']) )->save( $goods_arr );
	           $result['code'] = 1;
	           echo json_encode($result);
	           die();
	        }

	    }else {
	        $result['msg'] = '非法操作';
	        echo json_encode($result);
	        die();
	    }
	}

	public function activity_goods()
	{
	    $id = I('get.id');


	    $model=new SubjectModel();
	    $data=$model->show_subjectgoods_page($id);

	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出

	    $this->display();
	}
	public function update()
	{
		$id = I('post.id');

		$data = array();
		$data['name'] = trim(I('post.name'));
		$data['logo'] = trim(I('post.logo'));
		$data['banner'] = trim(I('post.banner'));
		$data['type'] = I('post.type');
		$data['price'] =floatval(I('post.price'));
		$data['begin_time'] = strtotime( I('post.begin_time') );
		$data['end_time'] = strtotime( I('post.end_time') );

		$rs = M('subject')->where( array('id' => $id) )->save($data);

		 if (!$rs) {
	        	$status = array('status'=>'back','message'=>'编辑失败');
	            $this->osc_alert($status);
	     }
		 //subject_goods
		 $up_data = array();
		 $up_data['begin_time'] = strtotime( I('post.begin_time') );
		 $up_data['end_time'] = strtotime( I('post.end_time') );
		 //state
		 $now_time = time();
		 $state = 0;
		 if( $now_time> $up_data['begin_time'] && $now_time < $up_data['end_time'])
		 {
			 $state  = 1;
		 }
		 $up_data['state'] = $state;

		 M('subject_goods')->where( array('subject_id' => $id) )->save($up_data);

		 if($state == 0)
		 {
			 //下架
			$subject_goods = M('subject_goods')->where( array('subject_id' => $id) )->select();
			foreach($subject_goods as $sub_goods)
			{
				 M('goods')->where( array('goods_id' => $sub_goods['goods_id']) )->save( array('status' => 0) );
			}
		 }

		 if($data['price'] != -1){
			$goods_arr = array();
			$goods_arr['lock_price'] = 1;
			$goods_arr['pinprice'] = $subject['price'];

			$subject_goods = M('subject_goods')->where( array('subject_id' => $id) )->select();
			foreach($subject_goods as $sub_goods)
			{
				 M('goods')->where( array('goods_id' => $sub_goods['goods_id']) )->save( $goods_arr );
			}
		}


	     $status = array('status'=>'success','message'=>'编辑活动成功！','jump'=>U('Subject/index', array('type' =>$data['type'])));
	     $this->osc_alert($status);

	}
	public function edit()
	{
		$id = I('get.id');
		$subject = M('subject')->where( array('id' => $id) )->find();

		$this->subject = $subject;

		$this->display();
	}



}
?>
