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
use Admin\Model\SellerModel;
use Seller\Model\ExpressModel;

class SellerManageController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='商家管理';
			$this->breadcrumb2='卖家管理';
	}

     public function index(){

		//name=ddd
		$name = I('get.name','','htmlspecialchars');
		$search = array();
		$search['s_true_name'] = $name;

		$model=new SellerModel();
		$data=$model->show_seller_user_page($search);
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
    	$this->display();
	 }

	 public function express()
	 {
		 $this->breadcrumb1='快递配置';

	     $this->breadcrumb2='快递配置';

	     $model=new ExpressModel();

	     $search = array('store_id' => 0);

	     $data=$model->show_express_page($search);

	     $this->assign('empty',$data['empty']);// 赋值数据集
	     $this->assign('list',$data['list']);// 赋值数据集
	     $this->assign('page',$data['page']);// 赋值分页输出
	     $this->type = 0;

	     $this->display();
	 }
	 function config()
	 {
		 $this->breadcrumb1='快递配置';

	     $this->breadcrumb2='快递配置';
		 $open_info = M('config')->where( array('name' => 'EXPRESS_OPEN') )->find();
		 $ebuss_info = M('config')->where( array('name' => 'EXPRESS_EBUSS_ID') )->find();
		 $exappkey = M('config')->where( array('name' => 'EXPRESS_APPKEY') )->find();

		$is_open = $open_info['value'];
		$ebuss_id = $ebuss_info['value'];
		$express_appkey = $exappkey['value'];

		$this->is_open = $is_open;
		$this->ebuss_id = $ebuss_id;
		$this->express_appkey = $express_appkey;

		 $this->type = 1;
		 $this->display();
	 }

	 function configadd()
	 {
		 $data = I('post.');
		 /**
		 array(4) { ["is_open"]=> string(1) "1" ["ebuss_id"]=> string(7) "1276098" ["express_appkey"]=> string(36) "9933541f-2d17-4312-8250-a9cecdbe633d" ["send"]=> string(6) "提交" }
		 **/
		 M('config')->where( array('name' => 'EXPRESS_OPEN') )->save( array('value' => $data['is_open']) );
		 M('config')->where( array('name' => 'EXPRESS_EBUSS_ID') )->save( array('value' => $data['ebuss_id']) );
		 M('config')->where( array('name' => 'EXPRESS_APPKEY') )->save( array('value' => $data['express_appkey']) );
		 $return = array(
        			        'status'=>'success',
        			        'message'=>'保存成功',
        			        'jump'=>U('SellerManage/config')
        			     );
		$this->osc_alert($return);
	 }
    function addexpress(){
        $this->breadcrumb2='快递配置';
		if(IS_POST){
			$data=I('post.');
			$data['store_id'] = 0;
			$data['addtime'] = time();
			$res = M('seller_express')->add($data);

			if($res) {
			   $return = array(
        			        'status'=>'success',
        			        'message'=>'新增成功',
        			        'jump'=>U('SellerManage/express')
        			     );
			} else {
			    $return = array(
        			        'status'=>'fail',
        			        'message'=>'新增失败',
        			        'jump'=>U('SellerManage/express')
        			    );
			}

			$this->osc_alert($return);
		}

		$this->crumbs='新增';
		$this->action=U('SellerManage/addexpress');
		$this->display('editexpress');
	}

	function editexpress(){
	    $this->breadcrumb2='快递配置';
		if(IS_POST){

		    $data=I('post.');

			$data['addtime'] = time();

			$res = M('seller_express')->save($data);

			if($res) {
			   $return = array(
        			        'status'=>'success',
        			        'message'=>'编辑成功',
        			        'jump'=>U('SellerManage/express')
        			     );
			} else {
			    $return = array(
        			        'status'=>'fail',
        			        'message'=>'编辑失败',
        			        'jump'=>U('SellerManage/express')
        			    );
			}
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('SellerManage/editexpress');
		$this->d=M('seller_express')->find(I('id'));
		$this->display('editexpress');
	}

	 public function delexpress(){

	    $id = I('get.id', 0);
	    $res = M('seller_express')->where( array('id' => $id) )->delete();

	    if($res) {
	        $return = array(
	            'status'=>'success',
	            'message'=>'删除成功',
	            'jump'=>U('SellerManage/express')
	        );
	    } else {
	        $return = array(
	            'status'=>'fail',
	            'message'=>'删除失败',
	            'jump'=>U('SellerManage/express')
	        );
	    }
		$this->osc_alert($return);
	 }

	 public function apply()
	 {
	     $this->breadcrumb1='入驻中心';
	     $this->breadcrumb2='申请管理';

	     $model=new SellerModel();

		 $state = I('get.state',-1);

		 $search = array();
		 if($state >= 0)
		 {
			 $search['state'] = intval($state);
		 }

	     $data=$model->show_apply_user_page($search);

		 $this->state = $state;
	     $this->assign('empty',$data['empty']);// 赋值数据集
	     $this->assign('list',$data['list']);// 赋值数据集
	     $this->assign('page',$data['page']);// 赋值分页输出
	     $this->display();
	 }

	 public function apply_detail()
	 {
		$this->breadcrumb1='入驻中心';
	    $this->breadcrumb2='申请管理';

		$aid = I('get.id',0);
		$apply_info = M('apply')->where( array('id' => $aid) )->find();

		$apply_relship = M('apply_relship')->where( array('aid' => $aid) )->find();
		if(!empty($apply_relship))
		{
			$apply_relship['rel_data'] = unserialize( $apply_relship['rel_data'] );
		}
		$this->apply_relship = $apply_relship;
		if($apply_info['type'] == 1)
		{
			$this->display('apply_detailbus');
		}else
			$this->display();
	 }

	 function certif_chang()
	 {
		 $data = I('post.');
		 $s_id = $data['s_id'];
		 $certif = $data['certif'];

		 M('seller')->where( array('s_id' => $s_id) )->save( array('certification' => $certif) );
		 $result = array();
		 $result['code'] = 1;
		 echo json_encode($result);
		 die();
	 }

	 function shenhe()
	 {
	     $id = I('get.id');
	     $state = I('get.state');

		 if($state == 2)
		 {
			M('apply')->where( array('id' => $id) )->save( array('state' => intval($state)) );
			$return = array('status' => 'success', 'message' => '操作成功,新增商家成功', 'jump' => U('SellerManage/apply'));
			$this->osc_alert($return);
			die();
		 }



		 $ckname = M('seller')->where( array('s_true_name' =>$apply_info['store_name']) )->find();
		 if(!empty($ckname))
		 {
			$return = array('status' => 'fail', 'message' => '该店铺名称已经存在', 'jump' => U('SellerManage/apply'));
			$this->osc_alert($return);
			die();
		 }
		 $ckmobile = M('seller')->where( array('s_mobile' =>$apply_info['mobile']) )->find();
		 if(!empty($ckname))
		 {
			$return = array('status' => 'fail', 'message' => '该手机号已经被店铺注册', 'jump' => U('SellerManage/apply'));
			$this->osc_alert($return);
			die();
		 }

		 $apply_info = M('apply')->where( array('id' => $id) )->find();
		 //type =0 1 个人 企业
		 //0普通，1个人认证，2企业认证，3平台自营'
		 $certification = 0;
		 if($apply_info['type'] == 0)
		 {
			$certification = 1;
		 } else {
			$certification = 2;
		 }
		 $data = array();
		 $data['s_uname'] = $apply_info['mobile'];
		 $data['s_true_name'] = $apply_info['store_name'];
		 $data['s_logo'] = '';
		 $data['s_qrcode'] = '';
		 $data['certification'] = $certification;
		 $data['s_telephone'] = $apply_info['mobile'];
		 $data['s_mobile'] = $apply_info['mobile'];
		 $data['s_qq'] = '';
		 $data['s_weixin'] = '';
		 $data['s_alipay'] = '';
		 $data['s_cardname'] = '';
		 $data['s_cardnumber'] = '';
		 $data['s_email'] = $apply_info['email'];
		 $data['s_passwd'] =  $apply_info['mobile'];
		 $data['s_is_super'] =  0;
		 $data['s_role_id'] =  0;
		 $data['s_login_count'] =  0;
		 $data['s_last_login_ip'] =  '';
		 $data['s_last_ip_region'] =  '';
		 $data['s_create_time'] =  time();
		 $data['s_last_login_time'] =  0;
		 $data['s_status'] =  1;

		$apply_relship = M('apply_relship')->where( array('aid' => $id) )->find();
		if(!empty($apply_relship))
		{
			$apply_relship['rel_data'] = unserialize( $apply_relship['rel_data'] );
			$data['s_logo'] = $apply_relship['rel_data']['store_logo'];

		}
		$model=new SellerModel();
		$res=$model->add_seller_user($data);
		if(!empty($apply_relship))
		{
			M('apply_relship')->where( array('aid' => $id) )->save( array('seller_id' => $res['s_id']) );
		}

	    M('apply')->where( array('id' => $id) )->save( array('state' => intval($state)) );

		//检测是否开启短信，发送短信

		$open_sms_info = M('config')->where( array('name' => 'open_sms') )->find();
		$is_open = $open_sms_info['value'];

		if(!empty($is_open)  && $is_open == 1)
		{
			//发送短信
			$this->send_sms_sh($apply_info['store_name'],$data['s_mobile']);
		}

	    $return = array('status' => 'success', 'message' => '操作成功,新增商家成功', 'jump' => U('SellerManage/apply'));
	    $this->osc_alert($return);
	}

	/**
		发送短信
	**/
	private function send_sms_sh($store_name, $mobile)
	{

		$sms_userid_info = M('config')->where( array('name' => 'sms_userid') )->find();
		$sms_userid = $sms_userid_info['value'];

		$sms_username_info = M('config')->where( array('name' => 'sms_username') )->find();
		$sms_username = $sms_username_info['value'];

		$sms_password_info = M('config')->where( array('name' => 'sms_password') )->find();
		$sms_password = $sms_password_info['value'];

		$sms_template_info = M('config')->where( array('name' => 'sms_template') )->find();
		$sms_template = $sms_template_info['value'];

		$sms_template =  str_replace("{uname}",$store_name,$sms_template);


		$post_data = array();
		$post_data['userid'] = $sms_userid;
		$post_data['account'] = $sms_username;
		$post_data['password'] = $sms_password;
		$post_data['content'] = $sms_template;

		$post_data['mobile'] = $mobile;
		$url='http://sms.kingtto.com:9999/sms.aspx?action=send';
		$o='';
		foreach ($post_data as $k=>$v)
		{
		//短信内容需要用urlencode编码，否则可能收到乱码
		$o.="$k=".urlencode($v).'&';
		}
		$post_data=substr($o,0,-1);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
		$result = curl_exec($ch);
	}


	function add(){
		$model=new SellerModel();
		if(IS_POST){
			$data=I('post.');
			$return=$model->add_seller_user($data);
			$this->osc_alert($return);
		}
		$this->crumbs='新增';
		$this->action=U('SellerManage/add');

		$this->display();
	}

	function info(){
		$model=new SellerModel();
		if(IS_POST){
			$data=I('post.');

			$info = M('Seller')->find($data['s_id']);

			$return=$model->edit_seller_user($data);
			$this->osc_alert($return);
		}
		$data = M('Seller')->find(I('id'));
		if(!empty($data['s_logo']))
		  $data['thumb_logo'] = resize($data['s_logo'], 100, 100);


		$this->crumbs='编辑';
		$this->action=U('SellerManage/info');
		$this->data=$data;

		$this->display();
	}

	function store_bind_class()
	{
	    $seller_id = intval($_GET['id']);
	    $model= D('Admin/StoreBindClass');
	    $data=$model->show_store_bind_class_page($seller_id);
	    $goods_cate_model = D('Admin/GoodsCategory');
	    foreach($data['list'] as $key=>$val)
	    {
	        if(!empty($val['class_1']))
	        {
	            $cate_info = $goods_cate_model->getInfoById($val['class_1'],"name");
	            $val['cate1_name'] = empty($cate_info) ? '' : $cate_info['name'];
	        }
	        if(!empty($val['class_2']))
	        {
	            $cate_info = $goods_cate_model->getInfoById($val['class_2'],"name");
	            $val['cate2_name'] = empty($cate_info) ? '' : $cate_info['name'];
	        }
	        if(!empty($val['class_3']))
	        {
	            $cate_info = $goods_cate_model->getInfoById($val['class_3'],"name");
	            $val['cate3_name'] = empty($cate_info) ? '' : $cate_info['name'];
	        }
	        $data['list'][$key] = $val;
	    }
	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出


	    $cate_data = $this->get_json_category_tree(0);
	    $this->assign('cate_data',$cate_data);// 赋值数据集
	    $this->assign('seller_id',$seller_id);// 赋值数据集
	    $this->display();
	}
	function add_store_bind_class()
	{
	    $id = intval($_POST['seller_id']);
	    if(empty($_POST['class_1']))
	    {

	        $status = array('status'=>'fail','message'=>'请选择需要绑定的类目','jump'=>U('SellerManage/store_bind_class','id='.$id));
	        $this->osc_alert($status);
	    }
	    if(empty($_POST['commis_rate']))
	    {
	        $status = array('status'=>'fail','message'=>'请填写佣金比例','jump'=>U('SellerManage/store_bind_class','id='.$id));
	        $this->osc_alert($status);
	    }
	    $store_bind_class = D('Admin/StoreBindClass');
	    $res = $store_bind_class->add_store_bind_class($_POST);
	    if($res)
	    {
	        $status = array('status'=>'success','message'=>'添加成功','jump'=>U('SellerManage/store_bind_class','id='.$id));
	        $this->osc_alert($status);
	    } else {
	        $status = array('status'=>'fail','message'=>'添加失败','jump'=>U('SellerManage/store_bind_class','id='.$id));
	        $this->osc_alert($status);
	    }
	}
	function delstorebind()
	{
	    $id = intval($_GET['id']);
	    $bid = intval($_GET['bid']);
	    $store_bind_class = D('Admin/StoreBindClass');

	    if($store_bind_class->add_bind_class($bid))
	    {
	        $status = array('status'=>'success','message'=>'删除成功','jump'=>U('SellerManage/store_bind_class','id='.$id));
	        $this->osc_alert($status);
	    } else {
	        $status = array('status'=>'fail','message'=>'删除失败','jump'=>U('SellerManage/store_bind_class','id='.$id));
	        $this->osc_alert($status);
	    }
	}

	/**
	 * Ajax 获取分类数据，pid一层层获取
	 * @param number $pid
	 */
	function get_json_category_tree($pid=0,$is_ajax=0)
	{
	    if(isset($_GET['pid']) && isset($_GET['is_ajax']))
	    {
	        $is_ajax = intval($_GET['is_ajax']);
	        $pid = intval($_GET['pid']);
	    }
	     $goods_category = D('Admin/GoodsCategory');
	     $data = $goods_category->get_parent_cateory($pid);
	    if($is_ajax == 0)
	    {
	        return $data;
	    } else {
	        $result = array();
	        $result['code'] = empty($data) ?  0 : 1;
	        $result['list'] = $data;
	        echo json_encode($result);
	        die();
	    }
	}

}
?>
