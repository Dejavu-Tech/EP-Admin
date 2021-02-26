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
use Admin\Model\OrderModel;
class OrderController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='订单';
			$this->breadcrumb2='订单管理';
	}



     public function index(){
        $model=new OrderModel();



		//$w=new \Lib\Kuaidiniao();
		//$w->subscribe('470');
		//die();
		$filter=I('get.');
		$order_status_id = I('get.order_status_id', 0);
		$search=array();


        if(isset($filter['order_num'])){
			$search['order_num']=$filter['order_num'];

		}
		if(isset($filter['user_name'])){
			$search['user_name']=$filter['user_name'];
		}

    	 if(isset($filter['order_status_id']) && $filter['order_status_id'] > 0){
			$search['order_status_id']=$filter['order_status_id'];

		}
		if(isset($filter['status']) && $filter['status'] != 0){
			$search['status']=$filter['status'];
			$this->get_status=$search['status'];
		}
		$_POST = $_GET = array_merge($_GET,$_POST);

		$post_data = $_POST;

		if(isset($post_data['transaction_id'])){
			$search['transaction_id']=$post_data['transaction_id'];
		}

		//store_id
		if(isset($post_data['store_id']) && !empty($post_data['store_id']))
		{
			$search['store_id'] = $post_data['store_id'];
		}
		//order_num_alias
		if(isset($post_data['order_num_alias']) && !empty($post_data['order_num_alias']))
		{
			$search['order_num'] = $post_data['order_num_alias'];
		}
		//member_id
		if(isset($post_data['member_id']) && !empty($post_data['member_id']))
		{
			$search['member_id'] = $post_data['member_id'];
		}
		//name
		if(isset($post_data['name']) && !empty($post_data['name']))
		{
			$search['user_name'] = $post_data['name'];
		}
		//shipping_tel
		if(isset($post_data['shipping_tel']) && !empty($post_data['shipping_tel']))
		{
			$search['shipping_tel'] = $post_data['shipping_tel'];
		}
		//shipping_no
		if(isset($post_data['shipping_no']) && !empty($post_data['shipping_no']))
		{
			$search['shipping_no'] = $post_data['shipping_no'];
		}
		//goods_id
		if(isset($post_data['goods_id']) && !empty($post_data['goods_id']))
		{
			$search['goods_id'] = $post_data['goods_id'];
		}
		//date_added_begin
		if(isset($post_data['date_added_begin']) && !empty($post_data['date_added_begin']))
		{
			$search['date_added_begin'] = strtotime($post_data['date_added_begin']);
		}
		//date_added_end
		if(isset($post_data['date_added_end']) && !empty($post_data['date_added_end']))
		{
			$search['date_added_end'] = strtotime($post_data['date_added_end']);
		}
		//is_pin
		if(isset($post_data['is_pin'])  && $post_data['is_pin'] >=0)
		{
			$search['is_pin'] = $post_data['is_pin'];
		}



		if( isset($post_data['subtype']) && $post_data['subtype'] == 'export')  {
		    //导出
		    $data=$model->show_order_page($search,true);

		    $need_data = array();
		    foreach($data['list'] as $val)
		    {

		        if($val['pin_id'] > 0 && $val['lottery_win'] == 0){
		            $tmp_pin_info =  M('pin')->where( array('pin_id' => $val['pin_id']) )->find();
		            if($tmp_pin_info['is_lottery'] == 1)
		            {
		                continue;
		            }
		        }

		        $tmp_arr = array();
		        $tmp_arr['order_sn'] = $val['order_num_alias'].' ';
		        //$tmp_arr['goods_name'] = $val['goods_name'];
		        $tmp_arr['quantity'] = $val['quantity'];
		        $tmp_arr['total'] = $val['total'];
				$tmp_arr['delivery'] = ($val['delivery'] == 'express') ? '快递': '自提';
				$tmp_arr['pick_name'] = '';
				$tmp_arr['pick_telephone'] = '';
				$tmp_arr['pick_sn'] = '';
				$tmp_arr['pick_huo'] = '';

				if($val['delivery'] == 'pickup')
				{
					$pick_order_info = M('pick_order')->where( array('order_id' => $val['order_id']) )->find();
					$pick_up = M('pick_up')->where( array('id' => $pick_order_info['pick_id']) )->find();

					$tmp_arr['pick_name'] = $pick_up['pick_name'];
					$tmp_arr['pick_telephone'] = ' '.$pick_up['telephone'];
					$tmp_arr['pick_sn'] = ' '.$pick_order_info['pick_sn'];
					$tmp_arr['pick_huo'] = ($pick_order_info['state'] == 0) ? '未提货': '已提货';
				}

		        $tmp_arr['date_added'] = date('Y-m-d H:i:s', $val['date_added']);

		        if(!empty($val['shipping_method'])) {
		            $shipping_method = M('seller_express')->where( array('id' => $val['shipping_method']) )->find();
		            $tmp_arr['shipping_method'] = $shipping_method['express_name'];
		        }else {
		            $tmp_arr['shipping_method'] = '';
		        }
		        $tmp_arr['shipping_no'] = $val['shipping_no'];

				// $tmp_arr['goods_name'] = $val['goods_name'];

				$order_goods_info_list = M('order_goods')->field('order_goods_id,total,name,quantity')->where( array('order_id' => $val['order_id']) )->select();

		        $tmp_arr['shipping_name'] = $val['shipping_name'];
		        $tmp_arr['shipping_tel'] = $val['shipping_tel'];
		        $province_info = M('area')->where( array('area_id' => $val['shipping_province_id']) )->find();
		        $city_info = M('area')->where( array('area_id' => $val['shipping_city_id']) )->find();
		        $country_info = M('area')->where( array('area_id' => $val['shipping_country_id']) )->find();

		        $tmp_arr['shipping_province_id'] = $province_info['area_name'];
		        $tmp_arr['shipping_city_id'] = $city_info['area_name'];
		        $tmp_arr['shipping_country_id'] = $country_info['area_name'];
		        $tmp_arr['shipping_address'] = $val['shipping_address'];
		        $tmp_arr['all_address'] = $province_info['area_name'].$city_info['area_name'].$country_info['area_name'].$val['shipping_address'];

				foreach($order_goods_info_list as $order_goods_info)
				{
					$order_goods_id = $order_goods_info['order_goods_id'];


					$option_list = M('order_option')->where( array('order_goods_id' =>$order_goods_id,'order_id'=> $val['order_id']) )->select();
					if(!empty($option_list))
					{
						$str = '';
						foreach ($option_list as $option) {
							$str .= $option['name'].': '.$option['value'].'  ';
						}
						$tmp_arr['option'] = $str;
						$tmp_arr['goods_name'] = $order_goods_info['name'];
					} else {

						$tmp_arr['option'] = '无';
						$tmp_arr['goods_name'] = $order_goods_info['name'];
					}

					$tmp_arr['total'] = $order_goods_info['total'];

					$tmp_arr['quantity'] = $order_goods_info['quantity'];
					$tmp_arr['comment'] = $val['comment'];

					$need_data[] = $tmp_arr;
				}





		    }

		     $xlsCell  = array(
    		     array('order_sn','订单号'),
    		     array('goods_name','货物名称'),
    		     array('quantity','数量'),
		         array('total','订单金额'),
		         array('date_added','下单时间'),
    		     array('shipping_method','系统快递'),
    		     array('shipping_no','快递单号 '),
				  array('delivery','配送方式'),
				 array('pick_name','提货地点'),
				 array('pick_telephone','提货电话'),
				 array('pick_sn','提货单序号'),
				 array('pick_huo','是否提货'),
				 array('option','商品规格 '),
    		     array('shipping_name','姓名 '),
    		     array('shipping_tel','电话 '),
    		     array('shipping_province_id','省 '),
    		     array('shipping_city_id','市 '),
    		     array('shipping_country_id','区 '),
    		     array('shipping_address','街道 '),
    		     array('all_address','完整地址 '),
    		     array('comment','备注')
		     );


		     $expTitle = '订单信息_'.date('Y-m-d H:i:s');

		     export_excel($expTitle,$xlsCell,$need_data);


		} else {
		    $data=$model->show_order_page($search);
		}
		$seller_list = M('seller')->where( array('s_status' => 1) )->select();

		$this->seller_list = $seller_list;
		$this->search = $search;//搜索条件
		$this->order_status_id = $order_status_id;
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

		$this->status=M('order_status')->select();

    	$this->display();
	 }

	 function print_order(){
	 	$model=new OrderModel();

		$this->order=$model->order_info(I('id'));
		$this->print=true;
		$this->display('./Themes/Home/default/Mail/order.html');
	 }


	 public function refunddone()
	 {
		$id = I('get.id',0,'intval');
		$comment = I('post.comment');
		$order_refund_type = I('post.order_refund_type',0,'intval');
		$result = array('code' =>1);

		$order_refund_history = array();
		$order_refund_history['order_id'] = $id;
		$order_refund_history['message'] = htmlspecialchars($comment);
		$order_refund_history['type'] = 3;
		$order_refund_history['addtime'] = time();

		M('order_refund_history')->add($order_refund_history);

		if($order_refund_type ==1)
		{
			//拒绝
			M('order_refund')->where( array('order_id' => $id) )->save( array('state' => 1) );
		} else {
			$weixin_model = D('Home/Weixin');
			//通过
			M('order_refund')->where( array('order_id' => $id) )->save( array('state' => 3) );
			$order_refund = M('order_refund')->where( array('order_id' => $id) )->find();
			$weixin_model->refundOrder($id, $order_refund['ref_money']);
		}
		echo json_encode($result);
		die();

	 }
	 public function show_refund()
	 {
		$this->crumbs='订单退款详情';

		$model=new OrderModel();

		$data = $model->order_info(I('id'));

		$order_statuses = $data['order_statuses'];

		$need_status = array();
		foreach($order_statuses as $key => $val)
		{
			if( in_array($val['order_status_id'], array(4)) )
			{
				$need_status[$key] = $val;
			}
		}
		$data['order_statuses'] = $need_status;

		$refund_reason = array(
							'97' =>'商品有质量问题',
							'98' =>'没有收到货',
							'99' =>'商品少发漏发发错',
							'100' =>'商品与描述不一致',
							'101' =>'收到商品时有划痕或破损',
							'102' =>'质疑假货',
							'111' =>'其他',
						);
		$order_refund = M('order_refund')->where( array('order_id' =>I('id')) )->find();
		//ref_type
		$order_refund['ref_type'] = $order_refund['ref_type'] ==1 ? '仅退款': '退款退货';
		$order_refund['ref_name'] = $refund_reason[$order_refund['ref_name']];

		$refund_state = array(
							0 => '申请中',
							1 => '商家拒绝',
							2 => '平台介入',
							3 => '退款成功',
							4 => '退款失败',
							5 => '撤销申请',
						);
		$order_refund['state'] = $refund_state[$order_refund['state']];

		$this->order_refund = $order_refund;
		$order_refund_image = M('order_refund_image')->where( array('ref_id' => $order_refund['ref_id']) )->select();
		$refund_images = array();

		if(!empty($order_refund_image))
		{
			foreach($order_refund_image as $refund_image)
			{
				$refund_image['thumb_image'] = resize($refund_image['image'], 100, 100);
				$refund_images[] = $refund_image;
			}
		}

		$order_refund_history = M('order_refund_history')->where( array('order_id' => I('id')) )->order('addtime asc')->select();

		foreach($order_refund_history as $key => $val)
		{
			switch($val['type'])
			{
				case 1:
						$val['type'] = '用户反馈';
						break;
				case 2:
						$val['type'] = '商家反馈';
						break;
				case 3:
						$val['type'] = '平台反馈';
						break;

			}
			$order_refund_history_image = M('order_refund_history_image')->where( array('orh_id' => $val['id']) )->select();
			if(!empty($order_refund_history_image))
			{
				foreach($order_refund_history_image as $kk => $vv)
				{
					$vv['thumb_image'] = resize($vv['image'], 100, 100);
					$order_refund_history_image[$kk] = $vv;
				}
			}
			$val['order_refund_history_image'] = $order_refund_history_image;
			$order_refund_history[$key] = $val;
		}

		$this->order_refund_history = $order_refund_history;
		$this->refund_images = $refund_images;
		$this->data = $data;
		$this->display('refund');
	 }

	 public function show_order(){

	 	$this->crumbs='订单详情';

	 	$model=new OrderModel();
		$data = $model->order_info(I('id'));

		$this->data=$data;

		$pick_order_info = array();
		$pick_up = array();
		if($data['order']['delivery'] == 'pickup')
		{
			$pick_order_info = M('pick_order')->where( array('order_id' => $data['order']['order_id']) )->find();
			$pick_up = M('pick_up')->where( array('id' => $pick_order_info['pick_id']) )->find();
		}


		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));
	 	$order_id = $hashids->encode(I('id'));

	 	$config_info = M('config')->where( array('name' => 'SITE_URL') )->find();

	 	$pin_url = $config_info['value'].'/index.php?s=/group/info/group_order_id/'.$order_id.'.html';
	 	$this->pin_url = $pin_url;


		$this->pick_order_info = $pick_order_info;
		$this->pick_up = $pick_up;

		$sql="select s.* from ".C('DB_PREFIX')."seller_express as s";
		$express_list= M()->query($sql);

		$this->express_list = $express_list;
		$order_goods_haitao = M('order_goods_haitao')->where( array('order_id' => $data['order']['order_id']) )->find();
		$this->order_goods_haitao = $order_goods_haitao;
	 	$this->display('show');
	 }
	 function history(){
	 		$model=new OrderModel();

			if(IS_POST){

				if(I('order_status_id')==C('cancel_order_status_id')){
					$Order = new \Home\Model\OrderModel();
					$Order->cancel_order($_GET['id']);
					storage_user_action(session('user_auth.uid'),session('user_auth.username'),C('BACKEND_USER'),'取消了订单  '.$_GET['id']);
					$result=true;
				}else{

					$result=$model->addOrderHistory($_GET['id'],$_POST);
				}


				if($result){
					$this->success='新增成功！！';
				}else{
					$this->error='新增失败！！';
				}
			}

			$results = $model->getOrderHistories($_GET['id']);

			foreach ($results as $result) {
				$histories[] = array(
					'notify'     => $result['notify'] ? '是' : '否',
					'status'     => $result['status'],
					'comment'    => nl2br($result['comment']),
					'date_added' => date('Y/m/d H:i:s', $result['date_added'])
				);
			}

			$this->histories=$histories;

			$this->display();
	}

	function del(){
		$model=new OrderModel();
		$return=$model->del_order(I('get.id'));
		$this->osc_alert($return);
	}

}
?>
