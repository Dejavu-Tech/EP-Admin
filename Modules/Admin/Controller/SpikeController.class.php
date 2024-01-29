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
use Admin\Model\SpikeModel;
class SpikeController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='营销活动';
		$this->breadcrumb2='限时秒杀活动商品';
	}

	public function index(){
		$model=new SpikeModel();
		$data=$model->show_spike_page();


		foreach($data['list'] as $key => $val)
		{
			$wait_goods = M('spike_goods')->where( array('spike_id' => $val['id'], 'state' => 0) )->count();
			$on_goods = M('spike_goods')->where( array('spike_id' => $val['id'], 'state' => 1) )->count();

			$val['wait_goods'] = $wait_goods;
			$val['on_goods'] = $on_goods;
			$data['list'][$key] = $val;
		}

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

		$this->display();
	}

	/**
		限时秒杀
	**/
	public function take_spike()
	{

		$id = I('get.id','0');

		$subject = M('spike')->where( array('id' => $id) )->find();
		$this->subject = $subject;

		$this->display();
	}

	/**
	 * 切换审核状态
	 */
    function toggle_statues_show()
	{
		$id = I('post.gid',0);
        $spike_info =M('Spike')->where( array('id' => $id) )->find();
        $status = $spike_info['is_best'] == 1 ? 0: 1;

        $res = M('Spike')->where( array('id' => $id) )->save( array('is_best' => $status) );
        echo json_encode( array('code' => 1) );
        die();
	}

	/**
		提交限时秒杀
	**/
	public function sub_spike()
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

		$subject = M('spike')->where( array('id' => $subject_id) )->find();

		foreach($data as $goods_id)
		{
			$goods_info = M('goods')->field('store_id')->where( array('goods_id' => $goods_id) )->find();

			$super_data  = array();
	        $super_data['spike_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 1;
	        $super_data['begin_time'] = $subject['begin_time'];
	        $super_data['end_time'] = $subject['end_time'];
			$super_data['seller_id'] = $goods_info['store_id'];
	        $super_data['addtime'] = time();

	        $rs = M('spike_goods')->add($super_data);

			if($rs) {
				$up_data = array('type' =>'spike','status' => 1);
				M('goods')->where( array('goods_id' => $goods_id) )->save( $up_data );
			}
		}
		$result['code'] = 1;
		echo json_encode($result);
		die();

	}

	public function add()
	{
		if(IS_POST){

			$data = array();
			$data['name'] = trim(I('post.name'));
			$data['begin_time'] = strtotime( I('post.begin_time') );
			$data['end_time'] = strtotime( I('post.end_time') );
			$data['add_time'] = time();

			$rs = M('spike')->add($data);
			 if (!$rs) {
		        	$status = array('status'=>'back','message'=>'添加失败');
		            $this->osc_alert($status);
		     }
		     $status = array('status'=>'success','message'=>'添加活动成功！','jump'=>U('Spike/index'));
		     $this->osc_alert($status);
		}
		$this->display();
	}

	public function shenhe()
	{
	    $result = array('code' => 0);
	    $id = I('get.id',0);
	    $spike_goods = M('spike_goods')->where( array('id' => $id) )->find();
	    if($spike_goods){
	        $rs = M('spike_goods')->where(array('id' => $id) )->save( array('state' => 1) );
	        if($rs) {
	            M('goods')->where( array('goods_id' => $spike_goods['goods_id']) )->save( array('type' => 'spike','status' =>1 ) );
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
	    $model=new SpikeModel();
	    $data=$model->show_spikegoods_page($id);

	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出

	    $this->display();
	}
	public function update()
	{
		$id = I('post.id');

		$data['name'] = trim(I('post.name'));
		$data['begin_time'] = strtotime( I('post.begin_time') );
		$data['end_time'] = strtotime( I('post.end_time') );

		$rs = M('spike')->where( array('id' => $id) )->save($data);

		M('spike_goods')->where( array('spike_id' => $id) )->save( array('begin_time' => $data['begin_time'], 'end_time' => $data['end_time']) );
		 if (!$rs) {
	        	$status = array('status'=>'back','message'=>'编辑失败');
	            $this->osc_alert($status);
	     }
	     $status = array('status'=>'success','message'=>'编辑限时秒杀成功！','jump'=>U('Spike/index'));
	     $this->osc_alert($status);

	}
	public function edit()
	{
		$id = I('get.id');
		$spike = M('spike')->where( array('id' => $id) )->find();

		$this->spike = $spike;

		$this->display();
	}



}
?>
