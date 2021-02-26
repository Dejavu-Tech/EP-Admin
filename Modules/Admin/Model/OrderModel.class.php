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

class OrderModel{

	/**
	 *显示订单状态单位分页
	 */
	public function show_order_page($search, $is_daochu = false){

		$sql="SELECT o.order_id,o.type,o.telephone,o.order_num_alias,o.total,o.lottery_win,o.delivery,o.date_added,o.is_zhuli,o.is_balance,og.member_disc,og.level_name,og.head_disc,og.is_pin,og.quantity,o.shipping_method,o.shipping_no,o.shipping_name,o.shipping_tel,o.shipping_province_id,o.shipping_city_id,o.shipping_country_id,o.shipping_address,og.pin_id,o.ip_region,o.payment_code,o.shipping_method,o.date_added,o.comment,o.date_modified,m.uname,os.order_status_id,os.name,og.store_id FROM "
		.C('DB_PREFIX').'order o,'.C('DB_PREFIX').'order_goods as og,'.C('DB_PREFIX').'member m,'.C('DB_PREFIX').'order_status os WHERE o.member_id=m.member_id AND '
		.'o.order_status_id=os.order_status_id  and og.order_id =o.order_id   ';

		if(isset($search['order_num'])){
			$sql.=" and o.order_num_alias='".$search['order_num']."'";
		}
		//member_id
		if(isset($search['member_id']) && !empty($search['member_id'])){
		    $sql.=" and m.member_id='".$search['member_id']."'";
		}
		//shipping_tel
		if(isset($search['shipping_tel']) && !empty($search['shipping_tel'])){
		    $sql.=" and o.shipping_tel like '%".$search['shipping_tel']."%'";
		}

		//telephone
		if(isset($search['telephone']) && !empty($search['telephone'])){
		    $sql.=" and o.telephone like '%".$search['telephone']."%'";
		}

		//shipping_no
		if(isset($search['shipping_no']) && !empty($search['shipping_no'])){
		    $sql.=" and o.shipping_no like '%".$search['shipping_no']."%'";
		}

		if(isset($search['transaction_id']) && !empty($search['transaction_id'])){
		    $sql.=" and o.transaction_id='".$search['transaction_id']."'";
		}


		//delivery
		if(isset($search['delivery']) && !empty($search['delivery'])){
		    $sql.=" and o.delivery='".$search['delivery']."'";
		}
		//shipping_no
		if(isset($search['goods_id']) && !empty($search['goods_id'])){
		    $sql.=" and og.goods_id='".$search['goods_id']."'";
		}
		//is_pin
		if(isset($search['is_pin']) && $search['is_pin'] >= 0){
		    $sql.=" and o.is_pin='".$search['is_pin']."'";
		}

		//date_added_begin
		if(isset($search['date_added_begin']) && $search['date_added_begin'] >= 0){
		    $sql.=" and o.date_added >=".$search['date_added_begin'];
		}

		if(isset($search['date_added_end']) && $search['date_added_end'] >= 0){
		    $sql.=" and o.date_added <=".$search['date_added_end'];
		}

		if(isset($search['shipping_name']) && !empty($search['shipping_name']) ){
			//
			$sql.=" and o.shipping_name like '%".$search['shipping_name']."%'";
		}

		if(isset($search['user_name']) && !empty($search['user_name'])){
			//
			$sql.=" and o.shipping_name like '%".$search['user_name']."%'";
		}
		if(isset($search['status'])){
			$sql.=" and os.order_status_id=".$search['status'];
		}

		if(isset($search['store_id']))
		{
		    $sql.=" and og.store_id=".$search['store_id'];
		}

		if(isset($search['order_status_id']))
		{
			if($search['order_status_id'] == 999)
			{
				$sql.=" and o.is_balance = 0 and o.order_status_id in (1,4,6,11)  ";
			} else
			{
				$sql.=" and o.order_status_id=".$search['order_status_id'];

				if( $search['order_status_id'] == 1)
				{
					$sql.=" and (o.type='normal' or o.type='pintuan' or ( o.type='lottery' and o.lottery_win =1 )  ) ";
				}

			}
		}


		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		if($is_daochu) {
		    //导出excel
		    $sql.=' group by o.order_id ORDER BY o.order_id DESC  ';
		} else {
		    $sql.=' group by o.order_id ORDER BY o.order_id DESC   LIMIT '.$Page->firstRow.','.$Page->listRows;
		}

		$list=M()->query($sql);

	    $pin_model = D('Home/Pin');

		foreach($list as $key => $val)
		{
			$val['pin_state'] = 0;

			$order_goods_info_list = M('order_goods')->field('order_goods_id,name')->where( array('order_id' => $val['order_id']) )->select();

			$name_arr = array();
			foreach($order_goods_info_list as $order_goods_info)
			{
				$order_goods_id = $order_goods_info['order_goods_id'];

				$option_list = M('order_option')->where( array('order_goods_id' =>$order_goods_id,'order_id'=> $val['order_id']) )->select();
				if(!empty($option_list))
				{
					$str = '规格：';
					foreach ($option_list as $option) {
						$str .= $option['name'].': '.$option['value'].'  ';
					}
					$name_arr[] = $order_goods_info['name'] .= $str;
				} else {
					$name_arr[] = $order_goods_info['name'];
				}

			}
			$val['goods_name'] = implode('<br/>',$name_arr);

			//store_id

			$seller_info = M('seller')->field('s_true_name')->where( array('s_id'=>$val['store_id']) )->find();

			$val['s_true_name']  = $seller_info['s_true_name'];
		    //ordertype
		    if($val['is_pin'] == 1)
		    {
				//pin_order $val['order_id']
				$pin_order_info =  M('pin_order')->where( array('order_id' => $val['order_id'] ) )->find();
				$pin_id = $pin_order_info['pin_id'];

		        $state = $pin_model->getNowPinState($pin_id);
		        $pin_info = M('pin')->where( array('pin_id' => $pin_id) )->find();
		        $str = '';
		        if($state == 1)
		        {
		            $str .='<a class="btn btn-xs btn-info" href='.U("Pin/show_order",array("pin_id"=>$pin_id)).'>';
		            $str .= $pin_info['need_count'].'人团 拼团id：'.$pin_id.'</a><br/><span class="label label-success ">已成团</span>';
		        } else if($state == 2)
		        {
					$str .='<a class="btn btn-xs btn-info" href='.U("Pin/show_order",array("pin_id"=>$pin_id)).'>';
		            $str .= '拼团';
					$str .= '</a>';
		        } else {
		            $str = '<span class="label label-success">进行中</span>';
		        }
		        //Pin/show_order/pin_id/484

				$val['pin_state'] = $state;
		        $val['ordertype'] = $str;
		        //已完成 拼团中，已失败
		        //<span class="label label-info arrowed-in-right arrowed">{$v.ordertype}</span>

		       // </a>
		    } else {
		        $val['ordertype'] = '<span class="badge badge-info">单独购买</span>';
		    }


			if($val['type'] == 'integral')
			{
				$integral_order = M('integral_order')->field('score')->where( array('order_id' => $val['order_id']) )->find();
				$val['score'] = intval($integral_order['score']);
			}
		    $list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}
	//订单信息
	public function order_info($id){
		//订单详情
		$order_sql="SELECT o.*,m.uname,m.email,o.shipping_tel,os.name,o.shipping_address as address FROM "
		.C('DB_PREFIX').'order o,'.C('DB_PREFIX').'member m, '
		.C('DB_PREFIX').'order_status os WHERE o.member_id=m.member_id and '
		.'o.order_status_id=os.order_status_id AND o.order_id='.$id;


		$order=M()->query($order_sql);
		//商品清单
		$order_product=M('order_goods')->where('order_id='.$id)->select();
		foreach($order_product as $key => $val)
		{
			$commiss_list = M('member_commiss_order')->where( array('order_goods_id' => $val['order_goods_id'], 'order_id' =>$val['order_id']) )->order()->select();
			if(!empty($commiss_list))
			{
				foreach($commiss_list as $kk => $vv)
				{
					//member_id
					$mb_info = M('member')->field('uname')->where( array('member_id' => $vv['member_id']) )->find();
					$vv['uname'] = $mb_info['uname'];
					$commiss_list[$kk] = $vv;
				}
			}
			$val['commiss_list'] = $commiss_list;

			$share_list = M('member_sharing_order')->where( array('order_goods_id' => $val['order_goods_id'], 'order_id' =>$val['order_id']) )->order()->select();
			if(!empty($share_list))
			{
				foreach($share_list as $kk => $vv)
				{
					//member_id
					$mb_info = M('member')->field('uname')->where( array('member_id' => $vv['member_id']) )->find();
					$vv['uname'] = $mb_info['uname'];
					$share_list[$kk] = $vv;
				}
			}
			$val['share_list'] = $share_list;
			$order_product[$key] = $val;
		}

		//价格、运费
		$order_total = M()->query("SELECT * FROM " .C('DB_PREFIX').
		 "order_total WHERE order_id =" .$id." ORDER BY sort_order");
		//订单状态
		$order_statuses=M('OrderStatus')->select();
		//订单历史
		$order_history=M('order_history')->where(array('order_id'=>$id))->select();

		return array(
			'order'=>$order[0],
			'order_product'=>$order_product,
			'order_total'=>$order_total,
			'order_statuses'=>$order_statuses,
			'order_history'=>$order_history
		);
	}

 	function addOrderHistory($order_id, $data) {

		$order['order_id']=$order_id;
		$order['date_modified']=time();

		if($data['order_status_id'] == 4)
		{
		    $order['shipping_method']=$data['shipping_method'];
			$order['shipping_no']=$data['shipping_no'];
		}

		$order['order_status_id']=$data['order_status_id'];

		M('Order')->save($order);

		if($data['order_status_id'] == 4)
		{
			$order_info = M('order')->where( array('order_id' => $order_id) )->find();
			$notify_model = D('Home/Weixinnotify');

			//$integral_model = D('Seller/Integral');
			//$integral_model->send_order_score_dr($order_id);

			if($order_info['delivery'] == 'pickup')
			{
				$notify_model->sendPickupMsg($order_id);
			} else {
				$notify_model->sendExpressMsg($order_id);

				$ebuss_info = M('config')->where( array('name' => 'EXPRESS_EBUSS_ID') )->find();


				if(!empty($ebuss_info['value']))
				{
					$w=new \Lib\Kuaidiniao();
					$rs = $w->subscribe($order_id);
				}
			}
		}
		if($data['order_status_id'] == 7)
		{
			$weixin_model = D('Home/Weixin');
			$weixin_model->refundOrder($order_id);
		}

		$oh['order_id']=$order_id;
		$oh['order_status_id']=$data['order_status_id'];
		$oh['notify']=(isset($data['notify']) ? (int)$data['notify'] : 0) ;
		$oh['comment']=strip_tags($data['comment']);
		$oh['date_added']=time();
		$oh_id=M('OrderHistory')->add($oh);

		return $oh_id;

	}

		public function getOrderHistories($order_id) {


		$query = M()->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM "
		. C('DB_PREFIX') . "order_history oh LEFT JOIN "
		. C('DB_PREFIX') . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id
	    . "' ORDER BY oh.date_added ASC");

		return $query;
	}

	function del_order($id){

		M('order')->where(array('order_id'=>$id))->delete();
		M('order_goods')->where(array('order_id'=>$id))->delete();
		M('order_history')->where(array('order_id'=>$id))->delete();
		M('order_total')->where(array('order_id'=>$id))->delete();

		return array(
			'status'=>'success',
			'message'=>'删除成功',
			'jump'=>U('Order/index')
		);
	}

}
?>
