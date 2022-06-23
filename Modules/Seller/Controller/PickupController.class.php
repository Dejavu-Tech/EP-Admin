<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://www.eaterplanet.com/
 * @copyright Copyright (c) 2019-2022 Dejavu.Tech.
 * @license   https://www.eaterplanet.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Seller\Controller;
use Seller\Model\PickupModel;
use Admin\Model\OrderModel;
class PickupController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='自提管理';
			$this->breadcrumb2='自提管理';
	}

	public function manage()
	{
		$model=new OrderModel();

		$filter=I('get.');
		$order_status_id = I('get.order_status_id', 0);

		$search=array('store_id' => SELLERUID);
		$search['delivery'] = 'pickup';

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

		$post_data = array();

		if(IS_POST){
		    $post_data = I('post.');


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
			if(isset($post_data['shipping_name']) && !empty($post_data['shipping_name']))
		    {
		        $search['shipping_name'] = $post_data['shipping_name'];
		    }
			if(isset($post_data['telephone']) && !empty($post_data['telephone']))
		    {
		        $search['telephone'] = $post_data['telephone'];
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

		}

		if( isset($post_data['subtype']) && $post_data['subtype'] == 'export')  {
		    //导出
		    $data=$model->show_order_page($search,true);
		   //order_status_id/1
		    $need_data = array();

		    $sql="select s.* from ".C('DB_PREFIX')."seller_express as s, ".C('DB_PREFIX')."seller_express_relat as ser
		      where s.id = ser.express_id and ser.store_id = ".SELLERUID;
		    $express_list= M()->query($sql);
		    $express_str = '   请填写以下快递对应的编号： ';
		    foreach($express_list as $express)
		    {
		        $express_str .= $express['express_name'].":".$express['id'].'  ';
		    }

		    foreach($data['list'] as $val)
		    {
		        if($val['pin_id'] > 0 && $val['lottery_win'] == 0){
		           $tmp_pin_info =  M('pin')->where( array('pin_id' => $val['pin_id']) )->find();
		           if($tmp_pin_info['is_lottery'] == 1)
		           {
		              continue;
		           }
		        }

				if($val['is_zhuli'] == 2)
				{
					$val['goods_name'] = '[砍价]'.$val['goods_name'];
				}

				if($val['type'] == 'lottery')
				{
					$val['goods_name'] = '[抽奖]'.$val['goods_name'];
				}

		        $tmp_arr = array();
		        $tmp_arr['order_sn'] = ' '.$val['order_num_alias'].' ';

				if($val['type'] == 'lottery')
				{
					$val['goods_name'] = '[抽奖]'.$val['goods_name'];
				}
				if($val['is_zhuli'] == 2)
				{
					$val['goods_name'] = '[砍价]'.$val['goods_name'];
				}
				if($val['head_disc'] < 100)
				{
					if($val['head_disc'] == 0)
					{
						$val['goods_name'] = '[团长免单]'.$val['goods_name'];
					}else{
						$val['goods_name'] = '[团长'.($val['head_disc']/10).'折]'.$val['goods_name'];
					}
				}

		        $tmp_arr['goods_name'] = $val['goods_name'];
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
		            $tmp_arr['shipping_method'] = $val['shipping_method'];
		        }else {
		            $tmp_arr['shipping_method'] = '';
		        }

		        $tmp_arr['shipping_no'] = $val['shipping_no'];

				$order_goods_info = M('order_goods')->field('order_goods_id')->where( array('order_id' => $val['order_id']) )->find();
				$order_goods_id = $order_goods_info['order_goods_id'];

				$option_list = M('order_option')->where( array('order_goods_id' =>$order_goods_id,'order_id'=> $val['order_id']) )->select();
				if(!empty($option_list))
				{
					$str = '';
					foreach ($option_list as $option) {
						$str .= $option['name'].': '.$option['value'].'  ';
					}
					$tmp_arr['option'] = $str;
				} else {

					$tmp_arr['option'] = '无';
				}

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
		        $tmp_arr['comment'] = $val['comment'];
				$tmp_arr['telephone'] = ' '. $val['telephone'];
		        $need_data[] = $tmp_arr;
		    }

		     $xlsCell  = array(
    		     array('order_sn','订单号'),
    		     array('goods_name','货物名称'),
    		     array('quantity','数量'),
		         array('total','订单金额'),
		         array('date_added','下单时间'),
    		     array('shipping_method','系统快递'.$express_str),
    		     array('shipping_no','快递单号 '),
				 array('delivery','配送方式'),
				 array('pick_name','提货地点'),
				 array('pick_telephone','提货电话'),
				 array('pick_sn','提货单序号'),
				 array('pick_huo','是否提货'),
				 array('option','商品规格 '),
    		     array('shipping_name','提货人姓名 '),
    		     array('telephone','提货人电话 '),
    		     array('comment','备注')
		     );
		     $expTitle = '订单信息_'.date('Y-m-d H:i:s');

		     export_excel($expTitle,$xlsCell,$need_data);


		} else {
			//var_dump($search);
			//die();
		    $data=$model->show_order_page($search);
		}

		$this->type = -1;
		$this->search = $search;//搜索条件
		$this->order_status_id = $order_status_id;
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

		$this->status=M('order_status')->select();

    	$this->display();
	 }

     public function index(){

		$model=new PickupModel();

		$search = array();
		$search['store_id']  = SELLERUID;

		$hashids = new \Lib\Hashids(C('PWD_KEY'), C('URL_ID'));

    	$has_seller_id = $hashids->encode(SELLERUID);

		//qrcode
		$jssdk = new \Lib\Weixin\Jssdk( C('weprogram_appid'), C('weprogram_appscret') );

		//保存图片

		$data=$model->show_pickup_page($search);

		foreach($data['list'] as $key => $val)
		{
			$pick_link = C('SITE_URL')."/index.php?s=/seller/bind_pickup_order/pick_up_id/{$val[id]}/seller_id/{$has_seller_id}";
		    $val['pick_link'] = $pick_link;

			$val['member_count'] = M('pick_member')->where( array('store_id' => SELLERUID,'pick_up_id' => $val['id']) )->count();


			if( empty($val['we_qrcode']) || isset($_GET['reflush']) )
			{
				$weqrcode = $jssdk->getAllWeQrcode('pages/order/hexiao_bind',SELLERUID.'_'.$val['id'] );
				//$weqrcode = $jssdk->getAllWeQrcode('pages/dan/index','5' );

				$image_dir = ROOT_PATH.'Uploads/image/goods';
				$image_dir .= '/'.date('Y-m-d').'/';

				$file_path = C('SITE_URL').'Uploads/image/goods/'.date('Y-m-d').'/';
				$kufile_path = $dir.'/'.date('Y-m-d').'/';

				RecursiveMkdir($image_dir);
				$file_name = md5('qrcode_'.$pick_order_info['pick_sn'].time()).'.png';
				//qrcode
				file_put_contents($image_dir.$file_name, $weqrcode);

				M('pick_up')->where( array('id' => $val['id']) )->save( array('we_qrcode' => $file_path.$file_name) );
				$val['we_qrcode'] = $file_path.$file_name;
			}


			//member_count pick_member

			$data['list'][$key] = $val;
		}
		$this->assign('seller_id',SELLERUID);
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	 public function toggle_statues_show()
	 {
		 $gid = I('post.gid');

		 $pick_member = M('pick_member')->where( array('id' => $gid ) )->find();

		 if($pick_member['state'] ==0)
		 {
			 M('pick_member')->where( array('id' => $gid ) )->save( array('state' => 1) );
		 }else{
			 M('pick_member')->where( array('id' => $gid ) )->save( array('state' => 0) );
		 }

		 echo json_encode( array('code'=>1) );
		 die();
	 }
	 public function member()
	 {
		$model=new PickupModel();

		//pick_up_id/5
		$pick_up_id = I('get.pick_up_id',0);

		$search = array();
		$search['store_id']  = SELLERUID;
		if($pick_up_id >0)
		{
			$search['pick_up_id']  = $pick_up_id;
		}


		$data=$model->show_pickup_member_page($search);


		$this->type = 2;
		$this->assign('seller_id',SELLERUID);
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	 public function hexiao_sub()
	 {
		$id = I('get.id',0,'intval');

		$result = array( 'code' => 0,'msg' => '提货失败');
		$res = M('pick_order')->where( array('id' => $id) )->save( array('state' => 1, 'tihuo_time' => time()) );
		if($res)
		{
			$pick_order = M('pick_order')->where( array('id' => $id) )->find();
			M('order')->where( array('order_id' => $pick_order['order_id']) )->save( array('order_status_id' => 6) );

			$history_data = array();
			$history_data['order_id']  = $pick_order['order_id'];
			$history_data['order_status_id']  = 6;
			$history_data['notify']  = 0;
			$history_data['comment']  = '商家后台：用户提货，核销';
			$history_data['date_added']  = time();
			M('order_history')->add($history_data);

			$notify_model = D('Home/Weixinnotify');
			$notify_model->sendPickupsuccessMsg($pick_order['order_id']);
			$result['code'] = 1;
		}
		echo json_encode($result);
		die();
	 }
	 public function hexiao()
	 {
		$pick_order = array();
		 if(IS_POST){
			 $pick_sn = I('post.pick_sn','','trim');

			 $pick_order = M('pick_order')->where( array('pick_sn' => $pick_sn) )->find();
			 //pick_id  order_id  state addtime
			 $pick_up = M('pick_up')->where( array('id' => $pick_order['id']) )->find();

			 $order_goods = M('order_goods')->where( array('order_id' => $pick_order['order_id']) )->find();
			 $is_cur_dian  = (SELLERUID == $order_goods['store_id']) ? true : false;

			 $option_list = M('order_option')->where( array('order_goods_id' =>$order_goods['goods_id'],'order_id'=> $pick_order['order_id']) )->select();
			$option_str = '';
			if(!empty($option_list))
			{
				$str = '';
				foreach ($option_list as $option) {
					$str .= $option['name'].': '.$option['value'].'  ';
				}
				$option_str = $str;
			} else {
				$option_str = '无';
			}

			 $this->option_str = $option_str;
			 $this->pick_up = $pick_up;
			 $this->order_goods = $order_goods;
			 $this->pick_sn = $pick_sn;
			 $this->is_cur_dian = $is_cur_dian;
		 }
		 $this->pick_order = $pick_order;
		 $this->type = 1;
		 $this->display();
	 }

	function add(){

		if(IS_POST){

			$data=I('post.');
			$data['store_id'] = SELLERUID;
			$data['addtime'] = time();

			$res = M('pick_up')->add($data);

			if($res) {
			   $return = array(
        			        'status'=>'success',
        			        'message'=>'新增成功',
        			        'jump'=>U('Pickup/index')
        			     );
			} else {
			    $return = array(
        			        'status'=>'fail',
        			        'message'=>'新增失败',
        			        'jump'=>U('Pickup/index')
        			    );
			}

			$this->osc_alert($return);
		}

		$this->crumbs='新增';
		$this->action=U('Pickup/add');
		$this->display('edit');
	}

	function edit(){
		if(IS_POST){

		    $data=I('post.');

			$data['addtime'] = time();

			$ck_info = M('pick_up')->where(array('id' =>$data['id'],'store_id' =>SELLERUID))->find();
			if(empty($ck_info)) {
				$return = array(
        			        'status'=>'fail',
        			        'message'=>'非法操作',
        			        'jump'=>U('Pickup/index')
        			    );
				$this->osc_alert($return);
			}
			$res = M('pick_up')->save($data);

			if($res) {
			   $return = array(
        			        'status'=>'success',
        			        'message'=>'编辑成功',
        			        'jump'=>U('Pickup/index')
        			     );
			} else {
			    $return = array(
        			        'status'=>'fail',
        			        'message'=>'编辑失败',
        			        'jump'=>U('Pickup/index')
        			    );
			}
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('Pickup/edit');
		$this->d=M('pick_up')->find(I('id'));
		$this->display('edit');
	}

	public function del_member()
	{

		//id
		$id = I('get.id', 0);
		$res =  M('pick_member')->where( array('id' => $id) )->delete();
		if($res) {
	        $return = array(
	            'status'=>'success',
	            'message'=>'删除成功',
	            'jump'=>$_SERVER['HTTP_REFERER']
	        );
	    } else {
	        $return = array(
	            'status'=>'fail',
	            'message'=>'删除失败',
	            'jump'=>$_SERVER['HTTP_REFERER']
	        );
	    }
		$this->osc_alert($return);
	}

	public function del(){

	    $id = I('get.id', 0);
	    $res = M('pick_up')->where( array('id' => $id) )->delete();

	    if($res) {
	        $return = array(
	            'status'=>'success',
	            'message'=>'删除成功',
	            'jump'=>U('Pickup/index')
	        );
	    } else {
	        $return = array(
	            'status'=>'fail',
	            'message'=>'删除失败',
	            'jump'=>U('Pickup/index')
	        );
	    }
		$this->osc_alert($return);
	 }

}
?>
