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
use Admin\Model\LotteryModel;
class LotteryController extends CommonController{

	protected function _initialize(){

		parent::_initialize();

		$this->breadcrumb1='营销活动';
		$this->breadcrumb2='抽奖活动';
	}

	public function index(){
		$model=new LotteryModel();

		$state = I('get.state',0);
		$where = ' and sg.state='.$state;
		if($state ==  1) {
		  //  $where .= ' and end_time > '.time();
		}
		$data=$model->show_lottery_page($where);

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->state = $state;
		$this->display();
	}

	public function addGoods()
	{
		$voucher_list = M('voucher')->where( "store_id=0 and end_time>".time() )->select();
	    $this->voucher_list = $voucher_list;
		$this->display('lottery');
	}

	/**
		提交抽奖活动申请
	**/
	public function sub_lottery()
	{
		$voucher_id = I('post.voucher_id',0);
	    $win_quantity = I('post.win_quantity',0);
	    $is_auto_open = I('post.is_auto_open',0);
	    $real_win_quantity = I('post.real_win_quantity',0);

		$result = array('code' => 0);
		$data = I('post.goods_ids_arr');
		if($voucher_id == 0){
	        $result['msg'] = '请选择退款时赠送的优惠券';
	        echo json_encode($result);
	        die();
	    }


		foreach($data as $goods_id)
		{
			$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

			//seller_id store_id

			$spike_data = array();
	        $spike_data['goods_id'] = $goods_id;
	        $spike_data['state'] = 0;
	        $spike_data['is_open_lottery'] = 0;
	        $spike_data['voucher_id'] = $voucher_id;
	        $spike_data['win_quantity'] = $win_quantity;
	        $spike_data['is_auto_open'] = $is_auto_open;
	        $spike_data['real_win_quantity'] = $real_win_quantity;
	        $spike_data['quantity'] = $goods_info['quantity'];
	        $spike_data['begin_time'] = 0;
	        $spike_data['end_time'] = 0;
	        $spike_data['seller_id'] = $goods_info['store_id'];
	        $spike_data['addtime'] = time();
	        $rs = M('lottery_goods')->add($spike_data);

	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'lottery', 'status' => 0) );
	        }
		}
		$result['code'] = 1;
		echo json_encode($result);
		die();

	}

	public function shenhe()
	{
	    $result = array('code' => 0);
	    $goods_id = I('get.goods_id',0);
	    $id = I('get.id',0);

	    $begin_time = I('post.begin_time');
	    $end_time = I('post.end_time');



	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if(empty($goods_info)) {
	        $result['msg'] = '该商品已删除';
	        echo json_encode($result);
	        die();
	    }  else if($goods_info['quantity'] ==0){
	        $result['msg'] = '该商品库存不足';
	        echo json_encode($result);
	        die();
	    } else{

	        $data = array();
	        $data['state'] = 1;
	        $data['begin_time'] = strtotime($begin_time);
	        $data['end_time'] = strtotime($end_time);

	        $rs = M('lottery_goods')->where( array('id' => $id) )->save($data);

	        if($rs) {
				//begin_time  end_time
				M('pin_goods')->where( array('goods_id' =>$goods_id) )
					->save( array('begin_time' =>strtotime($begin_time),'end_time' => strtotime($end_time) ) );

	            $goods_data = array();
	            $goods_data['type'] = 'lottery';
	            $goods_data['lock_type'] = 'lottery';
	            $goods_data['status'] = 1;
	            $grs = M('goods')->where( array('goods_id' => $goods_id) )->save($goods_data);

	            if($grs) {
	                $result['code'] = 1;
	                echo json_encode($result);
	                die();
	            }
	        }

	    }
	}

}
?>
