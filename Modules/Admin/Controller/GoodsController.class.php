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
use Admin\Model\GoodsModel;
class GoodsController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='商品';
		$this->breadcrumb2='商品管理';
	}

	public function index(){
		$model=new GoodsModel();

		$filter=I('get.');

		$search=array();
		//$map['_string'] = 'status=1 AND score>10';
		//'type' => 'normal'
		$search['_string'] = " 1=1 ";
		if(isset($filter['name'])){
			$search['name']=$filter['name'];
		}
		if(isset($filter['category'])){
			$search['category']=$filter['category'];
			$this->get_category=$search['category'];
		}
		if(isset($filter['store_id'])){
		    $search['store_id']=$filter['store_id'];
		    $this->get_store=$search['store_id'];
		}

		if(isset($filter['status']) && $filter['status'] != -1){

			$search['status']=$filter['status'];
			if($search['status'] == 2)
			{
				unset($search['_string']);
			}
			$this->get_status=$search['status'];
		}else {
			$this->get_status=-1;
		}

		$data=$model->show_goods_page($search);

		$category=M('goods_category')->select();
		$category_tree =list_to_tree($category);
		$this->category = $category_tree;
		$seller_list = M('seller')->field('s_id,s_true_name')->where( array('s_status' => 1) )->select();

		$seller_key_list = array();
		foreach($seller_list as $key => $val)
		{
			$seller_key_list[$val['s_id']] = $val['s_true_name'];
		}
		$this->seller_key_list = $seller_key_list;
		$this->seller_list = $seller_list;

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

		$this->display();
	}

	/**
	商品审核失败
	**/
	public function change_goods_shenhe()
	{
		//goods_id:goods_id,reason:reason
		$goods_id = I('post.goods_id');
		$reason = I('post.reason');

		M('goods_description')->where( array('goods_id' =>$goods_id) )->save( array('reason' =>$reason) );
		M('goods')->where( array('goods_id' => $goods_id) )->save( array('status' => 3) );

		echo json_encode( array('code' =>1) );
		die();
		//_goods_description

	}

	public function guobie()
	{
		$model=new GoodsModel();
		$data=$model->show_guobie_page();

		$this->breadcrumb2='海淘国别';
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->display();
	}

	public function editguobie()
	{
		$id = I('get.id');

		$guobie = M('guobie')->where( array('id' =>$id) )->find();

		$this->guobie = $guobie;
		$this->breadcrumb2='海淘国别';
		$this->display('addguobie');

	}
	public function addguobie()
	{
		$this->breadcrumb2='海淘国别';
		$this->display();
	}

	public function delguobie()
	{
		$id = I('get.id');

		M('guobie')->where( array('id' =>$id) )->delete();
		$return = array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('Goods/guobie')
				);
		M('goods')->where(array('guobie_id' => $id) )->save( array('type' => 'normal','lock_type' => 'normal','status' => 0) );
		$this->osc_alert($return);
	}

	/**
		存储国别
	**/
	public function saveguobie()
	{
		$data = I('post.');
		$data['add_time'] = time();


		if(isset($data['id']) && !empty($data['id']))
		{
			$res = M('guobie')->save($data);
			$return = array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('Goods/guobie')
				);
		} else {
			$res = M('guobie')->add($data);
			$return = array(
				'status'=>'success',
				'message'=>'添加成功',
				'jump'=>U('Goods/guobie')
				);
		}

		$this->osc_alert($return);
	}

	/**
		回收站商品重新上架
	**/
	public function goback()
	{
		$goods_id = I('get.id',0,'intval');
		$result = array('code' => 0);
		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
		if(empty($goods_info))
		{
			$result['msg'] = '非法操作';
			echo json_encode($result);
			die();
		}


		$up_data = array();
		$up_data['lock_type'] = 'normal';
		$up_data['status'] = 1;//下架

		M('goods')->where( array('goods_id' => $goods_id) )->save($up_data);

		$result['code'] = 1;
		echo json_encode($result);
		die();
	}

	/**
	加入回车站
	**/
	public function backhuiche()
	{
		$goods_id = I('get.id',0,'intval');
		$result = array('code' => 0);
		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
		if(empty($goods_info))
		{
			$result['msg'] = '非法操作';
			echo json_encode($result);
			die();
		}
		$lock_type = $goods_info['lock_type'];

		switch($lock_type)
		{
			case 'lottery':
				M('lottery_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
			case 'super_spike':
				M('super_spike_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
			case 'spike':
				M('spike_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
			case 'subject':
			case 'free_trial':
			case 'niyuan':
			case 'oneyuan':
			case 'haitao':
				M('subject_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
		}

		$up_data = array();
		$up_data['type'] = 'normal';
		$up_data['lock_type'] = 'normal';
		$up_data['status'] = 4;//下架

		M('goods')->where( array('goods_id' => $goods_id) )->save($up_data);

		$result['code'] = 1;
		echo json_encode($result);
		die();
	}

	/**
	 * 活动商品
	 */
	public function activity()
	{
	    $this->breadcrumb2='活动商品管理';

	    $model=new GoodsModel();

	    $filter=I('get.');


	    $search=array();


	    if(isset($filter['store_id'])){
	        $search['store_id']=$filter['store_id'];
	        $this->get_store=$search['store_id'];
	    }
	    if(isset($filter['name'])){
	        $search['name']=$filter['name'];
	    }
	    if(isset($filter['category'])){
	        $search['category']=$filter['category'];
	        $this->get_category=$search['category'];
	    }
	    if(isset($filter['status'])){
	        $search['status']=$filter['status'];
	        $this->get_status=$search['status'];
	    }

	    if(isset($filter['type'])){
	        $search['type']=$filter['type'];
	        $this->type=$search['type'];
	    }else {
	        $search['type']='activity';
	        $this->type=$search['type'];
	    }
	    //type

	    $data=$model->show_goods_page($search);

	    $store_bind_class = M('store_bind_class')->where( array('seller_id' => SELLERUID) )->select();

	    $cate_ids = array();
	    foreach($store_bind_class as $val)
	    {
	        if( !empty($val['class_1'])) {
	            $cate_ids[] = $val['class_1'];
	        }
	        if( !empty($val['class_2'])) {
	            $cate_ids[] = $val['class_2'];
	        }
	        if( !empty($val['class_3'])) {
	            $cate_ids[] = $val['class_3'];
	        }
	    }
	    if(empty($cate_ids)) {
	        $this->category = array();
	    } else {
	        $cate_ids_str = implode(',', $cate_ids);
	        $category=M('goods_category')->where( array('id' => array('in',$cate_ids_str)) )->select();
	        $category_tree =list_to_tree($category);
	        $this->category = $category_tree;
	    }

	    $category=M('goods_category')->select();
		$category_tree =list_to_tree($category);
		$this->category = $category_tree;
		$seller_list = M('seller')->field('s_id,s_true_name')->where( array('s_status' => 1) )->select();
		$this->seller_list = $seller_list;

		$seller_key_list = array();
		foreach($seller_list as $key => $val)
		{
			$seller_key_list[$val['s_id']] = $val['s_true_name'];
		}
		$this->seller_key_list = $seller_key_list;

	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出

	    $this->display();
	}

	function add(){

		if(IS_POST){

			$model=new GoodsModel();
			$data=I('post.');
			//dump($data);die;
			$return=$model->add_goods($data);
			$this->osc_alert($return);
		}

		//库存状态
		$this->stock_status=M('StockStatus')->select();
		//长度单位
		$this->length_class=M('LengthClass')->select();
		//重量单位
		$this->weight_class=M('WeightClass')->select();

		$this->action=U('Goods/add');
		$this->crumbs='新增';
		$this->display('edit');
	}

	public function get_json_category_tree($pid,$is_ajax=0)
	{
	   // {pid:pid,is_ajax:1}
	   $pid = empty($_GET['pid']) ? 0: intval($_GET['pid']);
	   $is_ajax = empty($_GET['is_ajax']) ? 0:intval($_GET['is_ajax']);

	   $list =  M('goods_category')->field('id,pid,name')->where( array('pid'=>$pid) )->order('sort_order asc')->select();
	   $result = array();
	   if($is_ajax ==0)
	   {
	       return $list;
	   } else {
	       if(empty($list)){
	           $result['code'] = 0;
	       } else {
	           $result['code'] = 1;
	           $result['list'] = $list;
	       }
	       echo json_encode($result);
	       die();
	   }

	}

	/**
		搜索可报名的商品
	**/
	public function goods_search()
	{
		$goods_name = I('post.goods_name','');
		$where = "  type='normal' and lock_type='normal' and status=1 and quantity>0 ";

		if(!empty($goods_name))
		{
			$where .=  "  and name like '%".$goods_name."%' ";
		}
		$goods_list = M('goods')->where($where)->limit(20)->select();

		$this->goods_list = $goods_list;
		$result = array();
		$result['html'] = $this->fetch('Goods:goods_list_fetch');
		echo json_encode($result);
		die();
	}

	function edit(){

		$model=new GoodsModel();
		$cate_data = $this->get_json_category_tree(0);

		$goods_info = $model->get_goods_data(I('id'));

		$seller_id = $goods_info['store_id'];



		if(IS_POST){

			$data=I('post.');
			$data['goods_description']['tag'] = str_replace('，', ',', $data['goods_description']['tag']);

			$return=$model->edit_goods($data);

			$this->osc_alert($return);
		}


		$relation_express = array();

		$seller_express_relat = M('seller_express_relat')->where( array('store_id' => $seller_id) )->select();

		if(!empty($seller_express_relat))
		{
			$exp_ids = array();
			foreach($seller_express_relat as $val)
			{
				$exp_ids[] = $val['express_id'];
			}
			if(!empty($exp_ids))
			{
				$exp_ids_str = implode(',', $exp_ids);
				$express_list = M('seller_express')->where( array('id' => array('in',$exp_ids_str) ) )->select();

				$relation_express = $express_list;
			}
		}
		$this->relation_express = $relation_express;

		$pick_up_list = M('pick_up')->where( array('store_id' => $seller_id) )->select();
		$this->pick_up_list = $pick_up_list;


		$goods_info['pick_up'] = unserialize($goods_info['pick_up']);
		$express_list =  unserialize($goods_info['express_list']);
		$express_ids = array_keys($express_list);

		$goods_info['express_ids'] = $express_ids;
		$goods_info['express_list'] = $express_list;


		$goods_area = M('goods_area')->where( array('goods_id' => I('id')) )->find();
		if(!empty($goods_area)) {
		    $goods_area['area_ids'] =unserialize( $goods_area['area_ids_text']);
		}
		$this->goods_area=$goods_area;

		$parent_area = M('area')->where( array('area_parent_id' => 0) )->order('area_sort asc ,area_id asc')->select();
		foreach($parent_area as $key => $val)
		{
		    $child_ren = M('area')->where( array('area_parent_id' => $val['area_id']) )->order('area_sort asc ,area_id asc')->select();
		    $val['child'] = $child_ren;
		    $parent_area[$key] = $val;
		}
		$this->parent_area = $parent_area;

		$this->crumbs='编辑';
		$this->action=U('Goods/edit');
		$this->description=M('goods_description')->find(I('id'));
		//库存状态
		$this->stock_status=M('StockStatus')->select();

		$this->goods=$goods_info;

		$guobie_list = M('guobie')->order('is_index desc,id asc')->select();

		$this->guobie_list = $guobie_list;

		$this->goods_images=$model->get_goods_image_data(I('id'));

		$this->goods_discount=M('goods_discount')->where(array('goods_id'=>I('id')))->order('quantity ASC')->select();

		$this->goods_categories=$model->get_goods_category_data(I('id'));
		//transport_id
		if($this->goods['transport_id'] > 0)
		{
		    $this->transport = D('Seller/Transport')->getTransportInfo(array('id' => $this->goods['transport_id']));
		}

		$this->goods_options=$model->get_goods_options(I('id'));
		$option_model=new \Admin\Model\OptionModel();
		//选项值
		foreach ($this->goods_options as $goods_option) {
				$option_values[$goods_option['option_id']] = $option_model->getOptionValues($goods_option['option_id']);
		}
		$this->option_values=$option_values;


		//dump($this->goods_options);die;


		$m=new \Admin\Model\OptionModel();
			//getOptions
		$options_list = $m->getOptions('',$goods_info['store_id']);

		$this->options_list = $options_list;



		$goods_option_mult_value = M('goods_option_mult_value')->where( array('goods_id' => I('id')) )->select();
		$goods_option_mult_str = '';

		if( !empty($goods_option_mult_value) )
		{
			$goods_option_mult_arr = array();
			foreach($goods_option_mult_value as $key => $val)
			{
				$goods_option_mult_arr[] = 'mult_id:'.$val['rela_goodsoption_valueid'].'@@mult_qu:'.$val['quantity'].'@@mult_image:'.$val['image'];
				//option_value  option_value_id  value_name
				$option_name_arr = explode('_', $val['rela_goodsoption_valueid']);
				$option_name_list = array();
				foreach($option_name_arr as $option_value_id_tp)
				{
					$tp_op_val_info =M('option_value')->where( array('option_value_id' => $option_value_id_tp) )->find();
					$option_name_list[] = $tp_op_val_info['value_name'];
				}
				$val['option_name_list'] = $option_name_list;
				$goods_option_mult_value[$key] = $val;
			}
			$goods_option_mult_str = implode(',', $goods_option_mult_arr);
		}

		$this->goods_option_mult_value = $goods_option_mult_value;
		$this->goods_option_mult_str = $goods_option_mult_str;

		$this->assign('cate_data',$cate_data);// 赋值数据集
		$this->display('edit');
	}

	function copy_goods(){
		$id =I('id');
		$model=new GoodsModel();
		if($id){
			foreach ($id as $k => $v) {
				$model->copy_goods($v);
			}
			$data['redirect']=U('Goods/index');
			$this->ajaxReturn($data);
			die;
		}
	}
	function toggle_index_sort()
	{
	    $goods_id = I('post.gid',0);
	    $index_sort = I('post.index_sort',0,'intval');
	    $res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('index_sort' => $index_sort) );
	    echo json_encode( array('code' => 1) );
	    die();
	}


	function toggle_quantity()
	{
		$goods_id = I('post.gid',0);
	    $quantity = I('post.quantity',0,'intval');

	    $res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('quantity' => $quantity) );
	    echo json_encode( array('code' => 1) );
	    die();
	}

	function toggle_guobie_show()
	{
		$id = I('post.gid',0);

        $guobie_info =M('guobie')->where( array('id' => $id) )->find();
        $is_index = $guobie_info['is_index'] == 1 ? 0: 1;

        $res = M('guobie')->where( array('id' => $id) )->save( array('is_index' => $is_index) );
        echo json_encode( array('code' => 1) );
        die();
	}
    function toggle_index_show()
    {
        $goods_id = I('post.gid',0);
        $goods_info =M('Goods')->where( array('goods_id' => $goods_id) )->find();
        $is_index_show = $goods_info['is_index_show'] == 1 ? 0: 1;

        $res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('is_index_show' => $is_index_show) );
        echo json_encode( array('code' => 1) );
        die();
    }
	function toggle_statues_show()
	{
		$goods_id = I('post.gid',0);
        $goods_info =M('Goods')->where( array('goods_id' => $goods_id) )->find();
        $status = $goods_info['status'] == 1 ? 2: 1;

        $res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('status' => $status) );
        echo json_encode( array('code' => 1) );
        die();
	}
	function del(){
		$model=new GoodsModel();
		$return=$model->del_goods(I('get.id'));
		$this->osc_alert($return);
	}
}
?>
