<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      http://www.ch871.com/
 * @copyright Copyright (c) 2019-2021 ch871.com.
 * @license   http://www.ch871.com/license.html License
 * ==========================================================================
 * 拼团模块
 * @author    Albert.Z
 *
 */
namespace Home\Controller;

class BonusController extends CommonController {

	protected function _initialize(){
		parent::_initialize();
		define('UID',is_login());
		$this->member_id = UID;
	}

	function index(){
		$id = I('get.id', 0);
		$this->id = $id;

		$quan_info = $this->get_voucher_info_do($id,0);

		if($quan_info['can_get'] == 0 )
		{
			$url = C('SITE_URL')."index.php?s=/Bonus/bonus/id/{$id}.html";//U('Bonus/bonus', array('id' => $id));

			header('Location: '.$url);

			//$this->redirect($url);
		}else{
			$this->display();
		}

	}

	/**
	 * 优惠券活动页面
	 */
	function bonus(){
		$id = I('get.id', 0);
		$type = I('get.type', 0);
		$this->id = $id;
		$this->type = $type;
		$this->display();
	}

	/**
	 * 优惠券过期页面
	 */
	function overdue(){
		$this->display();
	}


	private function get_voucher_info_do($voucher_id,$is_ajax = 1)
	{
		$voucher_info = M('voucher')->where( array('id' => $voucher_id) )->find();

		if( empty($voucher_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		} else{

			$voucher_info['share_logo'] = C('SITE_URL').'Uploads/image/'.$voucher_info['share_logo'];
			unset($voucher_info['store_id']);
			unset($voucher_info['type']);
			unset($voucher_info['is_index_show']);
			unset($voucher_info['is_limit_goods_buy']);
			unset($voucher_info['add_time']);

			$activity_over = 0; //券的活动时间是否结束了
			$now_time = time(); //end_time

			if( $voucher_info['end_time'] < $now_time)
			{
				$activity_over =1;
			}

			$has_get_voucher = 0;

			//0、计算已经领取了几张
			$has_get_count = M('voucher_list')->where( array('voucher_id' =>$voucher_id, 'user_id' => UID) )->count();


			//获取限制商品的分类:如果是全场商品。那么就调用出推荐的分类，如果是部分商品，就调用出部分商品的分类

			$category_list = array();
			$cate_ids = array();

			if( $voucher_info['is_limit_goods_buy'] == 1)
			{
				//部分商品
				$goods_list = M('voucher_goods')->where( array('voucher_id' => $voucher_id) )->select();

				foreach( $goods_list as $goods )
				{
					$cate_tmp = M('goods_to_category')->where( array('goods_id' => $goods['goods_id']) )->find();
					if( !empty($cate_tmp) )
					{
						if( empty($cate_ids) || !in_array($cate_tmp['class_id1'], $cate_ids) )
						{
							$cate_ids[] = $cate_tmp['class_id1'];
						}
					}
				}
				if( !empty( $cate_ids ) )
				{
					$category_list = M('goods_category')->field('id,name')->where( array('id'=> array('in',$cate_ids ) ) )->order('sort_order desc')->select();
				}

			}else{
				//全场商品
				$category_list = M('goods_category')->field('id,name')->where( array('is_hot'=> 1 ) )->order('sort_order desc')->select();
			}

			$result = array();
			$result['code'] = 0;
			$result['activity_over'] = $activity_over;//活动是否结束，1已结束，0 未结束
			$result['can_get'] = $activity_over == 1 ? 0 : 1;
			//$result['has_get_count'] = $has_get_count;//这个券，该会员获取了几张，这个数字大于0表示会员有券，如果活动已经结束，还需要借助这个字段判断是否还有要展示的领取过的券
			$result['voucher_info'] = $voucher_info;//券的详细信息
			$result['category_list'] = $category_list;
			$result['cur_time'] = time();//服务器的当前时间戳，倒计时的时候可能会用到
			$result['get_voucher_info'] = array();//需要展示的券信息

			if( $has_get_count == 0 )
			{
				//一张都没有领过, 活动已经结束

			}else{
				//检测是否还有未使用的券 end_time
				$get_voucher_info = M('voucher_list')->where( array('voucher_id' =>$voucher_id, 'user_id' => UID,'consume' => 'N') )->order('id desc')->find();

				if( !empty($get_voucher_info) )
				{
					$result['get_voucher_info'] = $get_voucher_info;
					$result['can_get'] = 0;
				}else{
					//判断一个人可以领几张
					if($activity_over == 0 && ( $voucher_info['person_limit_count'] ==0 || $voucher_info['person_limit_count'] > $has_get_count) )
					{
						$result['can_get'] = 1;
					}else if($activity_over == 0){
						$result['can_get'] = 0;
						$get_voucher_info = M('voucher_list')->where( array('voucher_id' =>$voucher_id, 'user_id' => UID,'consume' => 'Y') )->order('id desc')->find();
						$result['get_voucher_info'] = $get_voucher_info;//已经使用过了。领取的券数量已经大于等于 可领的券数量，
					}
				}
			}

			//total_count send_count
			if( $voucher_info['total_count'] <= $voucher_info['send_count'] )
			{
				$result['activity_over'] =2;//优惠券已经被抢光
				$result['can_get'] = 0;
			}
			if($is_ajax == 1)
			{
				echo json_encode($result);
				die();
			}else{
				return $result;
			}

		}
	}

	/**
		获取优惠券活动信息
	**/
	public function load_voucher_info($is_ajax = 1)
	{
		$voucher_id = I('get.voucher_id', 0);
		$this->get_voucher_info_do($voucher_id,1);
	}

	/**
		会员抢券
	**/
	public function get_bonus_voucher()
	{
		$voucher_id = I('get.voucher_id', 0);
		$is_double = I('get.is_double', 0);

		if($is_double == 1)
		{
			$is_double = true;
		}else{
			$is_double = false;
		}

		$voucher_model = D('Home/Voucher');

		$vocher_detail_id = $voucher_model->send_user_voucher_byId_frombonus($voucher_id,UID,true,$is_double);

		//-1 被抢光了， -2 已领过
		$result = array('code' => $vocher_detail_id,'cur_time' => time() );
		if($vocher_detail_id > 0)
		{
			$get_voucher_info = M('voucher_list')->where( array('id' =>$vocher_detail_id, 'user_id' => UID) )->find();

			if( empty($get_voucher_info) )
			{
				$get_voucher_info = array();
			}
			$result['code'] = 0;
			$result['get_voucher_info'] = $get_voucher_info;//已经使用过了。领取的券数量已经大于等于 可领的券数量，
		}

		echo json_encode( $result );
		die();

	}

	/**
		搜索优惠券商品
	**/
	public function get_voucher_goods_list()
	{

		$pre_page = 10;
		$voucher_id = I('get.voucher_id', 0);
		$page = I('get.page',1);
		$id = I('get.gid',0);

		$voucher_info = M('voucher')->where( array('id' => $voucher_id) )->find();

		if( empty($voucher_info) )
		{
			echo json_encode( array('code' =>1) );
			die();
		}

		if( $id > 0 )
		{
			//is_limit_goods_buy
			if( $voucher_info['is_limit_goods_buy'] == 1)
			{
				$goods_list_tmp = M('voucher_goods')->where( array('voucher_id' => $voucher_id) )->order('goods_id desc')->select();

				$need_goods_ids = array();
				foreach( $goods_list_tmp as $val )
				{
					$need_goods_ids[] = $val['goods_id'];
				}
				$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$id} or class_id2 ={$id} or class_id3 = {$id}  ")->field('goods_id')->select();
				$ids_arr = array();
				foreach($goods_ids_arr as $val){
					if( in_array($val['goods_id'], $need_goods_ids) )
					{
						$ids_arr[] = $val['goods_id'];
					}
				}
				if( empty($ids_arr) )
				{
					$ids_arr = array('a');
				}
				$ids_str = implode(',',$ids_arr);


				$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'quantity' =>array('gt',0) );

				$condition['_string'] = ' type="normal" or type="pintuan" ';



			}else{
				$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$id} or class_id2 ={$id} or class_id3 = {$id}  ")->field('goods_id')->select();

				$ids_arr = array();
				foreach($goods_ids_arr as $val){
					$ids_arr[] = $val['goods_id'];
				}
				$ids_str = implode(',',$ids_arr);

				$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'quantity' =>array('gt',0) );

				$condition['_string'] = ' type="normal" or type="pintuan" ';
			}
		}else{
			if( $voucher_info['is_limit_goods_buy'] == 1)
			{
				$goods_list_tmp = M('voucher_goods')->where( array('voucher_id' => $voucher_id) )->order('goods_id desc')->select();

				$need_goods_ids = array();
				foreach( $goods_list_tmp as $val )
				{
					$need_goods_ids[] = $val['goods_id'];
				}
				$ids_arr = $need_goods_ids;

				$ids_str = implode(',',$ids_arr);


				$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'quantity' =>array('gt',0) );

				$condition['_string'] = ' type="normal" or type="pintuan" ';
			}else{
				$condition = array( 'status'=>1,'quantity' =>array('gt',0) );
				$condition['_string'] = ' type="normal" or type="pintuan" ';
			}

		}

		$offset = ($page -1) * $pre_page;
		$list = M('goods')->field('goods_id,name,seller_count,virtual_count,quantity,image')->where($condition)->order('seller_count+virtual_count desc,goods_id asc')->limit($offset,$pre_page)->select();
		$goods_model = D('Home/goods');

		if(!empty($list)) {
			foreach($list as $key => $v){
				if(empty($v['fan_image'])){
					$list[$key]['image']= resize($v['image'], 480, 480);
				}
			}
		}
		foreach($list as $key => $val)
		{

			$val['seller_count'] += $val['virtual_count'];

			$price_arr = $goods_model->get_goods_price($val['goods_id']);

			$val['pinprice'] = $price_arr['price'];
			//credit
			$val['quan_after_price'] = $price_arr['price'] - $voucher_info['credit'];

			if($val['quan_after_price'] < 0)
			{
				$val['quan_after_price'] = 0;
			}

			$val['url'] = U('Goods/gshow', array('id' => $val['goods_id'], 'voucher_id' => $voucher_id));
			$list[$key] = $val;
		}

		if( !empty($list) )
		{
			echo json_encode( array('code' => 0, 'data' => $list) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}

	}

}
