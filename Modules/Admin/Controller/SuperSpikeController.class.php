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
use Admin\Model\SuperSpikeModel;
class SuperSpikeController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='营销活动';
		$this->breadcrumb2='超值大牌活动';
	}

	public function index(){
		$model=new SuperSpikeModel();
		$data=$model->show_superspike_page();

		foreach($data['list'] as $key => $val)
		{
			$wait_goods = M('super_spike_goods')->where( array('super_spike_id' => $val['id'], 'state' => 0) )->count();
			$on_goods = M('super_spike_goods')->where( array('super_spike_id' => $val['id'], 'state' => 1) )->count();

			$val['wait_goods'] = $wait_goods;
			$val['on_goods'] = $on_goods;
			$data['list'][$key] = $val;
		}

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->state = $state;
		$this->display();
	}
	public function add()
	{
		if(IS_POST){

			$data = array();
			$data['name'] = trim(I('post.name'));
			$data['begin_time'] = strtotime( I('post.begin_time') );
			$data['end_time'] = strtotime( I('post.end_time') );
			$data['add_time'] = time();

			$rs = M('super_spike')->add($data);
			 if (!$rs) {
		        	$status = array('status'=>'back','message'=>'添加失败');
		            $this->osc_alert($status);
		     }
		     $status = array('status'=>'success','message'=>'添加活动成功！','jump'=>U('SuperSpike/index'));
		     $this->osc_alert($status);
		}
		$this->display();
	}

	public function shenhe()
	{
	    $result = array('code' => 0);
	    $id = I('get.id',0);
	    $super_spike_goods = M('super_spike_goods')->where( array('id' => $id) )->find();
	    if($super_spike_goods){
	        $rs = M('super_spike_goods')->where(array('id' => $id) )->save( array('state' => 1) );
	        if($rs) {
	            M('goods')->where( array('goods_id' => $super_spike_goods['goods_id']) )->save( array('type' => 'super_spike','status' => 1) );
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


	    $model=new SuperSpikeModel();
	    $data=$model->show_superspikegoods_page($id);

	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出

	    $this->display();
	}

	/**
		报名超值大牌活动
	**/
	public function take_superspike()
	{
		$id = I('get.id','0');

		$subject = M('super_spike')->where( array('id' => $id) )->find();

		$this->subject = $subject;

		$this->display();
	}

	/**
		提交超值大牌
	**/
	public function sub_superspike()
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

		$subject = M('super_spike')->where( array('id' => $subject_id) )->find();

		foreach($data as $goods_id)
		{
			$goods_info = M('goods')->field('store_id')->where( array('goods_id' => $goods_id) )->find();


			$super_data  = array();
			$super_data['super_spike_id'] = $subject_id;
			$super_data['goods_id'] = $goods_id;
			$super_data['state'] = 1;
			$super_data['begin_time'] = $subject['begin_time'];
			$super_data['end_time']   = $subject['end_time'];
			$super_data['seller_id'] = $goods_info['store_id'];
			$super_data['addtime'] = time();

			$rs = M('super_spike_goods')->add($super_data);

			if($rs) {
				$up_data = array('type' =>'super_spike','status' => 1);
				M('goods')->where( array('goods_id' => $goods_id) )->save( $up_data );
			}
		}
		$result['code'] = 1;
		echo json_encode($result);
		die();

	}

	public function update()
	{
		$id = I('post.id');

		$data['name'] = trim(I('post.name'));
		$data['begin_time'] = strtotime( I('post.begin_time') );
		$data['end_time'] = strtotime( I('post.end_time') );

		$rs = M('super_spike')->where( array('id' => $id) )->save($data);

		M('super_spike_goods')->where( array('super_spike_id' => $id) )->save( array('begin_time' => $data['begin_time'], 'end_time' => $data['end_time']) );


		 if (!$rs) {
	        	$status = array('status'=>'back','message'=>'编辑失败');
	            $this->osc_alert($status);
	     }
	     $status = array('status'=>'success','message'=>'编辑活动成功！','jump'=>U('SuperSpike/index'));
	     $this->osc_alert($status);

	}
	public function edit()
	{
		$id = I('get.id');
		$super_spike = M('super_spike')->where( array('id' => $id) )->find();

		$this->super_spike = $super_spike;

		$this->display();
	}



}
?>
