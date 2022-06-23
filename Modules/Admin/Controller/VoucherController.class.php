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
namespace Admin\Controller;
use Admin\Model\VoucherModel;
class VoucherController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='平台优惠券';
			$this->breadcrumb2='平台券管理';
	}

     public function index(){

		$model=new VoucherModel();

		$data=$model->show_voucher_class_page(0);

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

		$this->type = 0;
    	$this->display();
	 }

	function free_config()
	{
		$voucher_free_state_info = M('config')->where( array('name' => 'VOUCHER_FREE_STATE') )->find();
		$voucher_free_desc_info = M('config')->where( array('name' => 'VOUCHER_FREE_DESC') )->find();
		$voucher_free_image_info = M('config')->where( array('name' => 'VOUCHER_FREE_IMAGE') )->find();

		if(IS_POST){
			//value

			M('config')->where( array('name' => 'VOUCHER_FREE_STATE') )->save( array('value' => I('post.voucher_free_state')) );
			M('config')->where( array('name' => 'VOUCHER_FREE_DESC') )->save( array('value' => I('post.voucher_free_desc')) );
			M('config')->where( array('name' => 'VOUCHER_FREE_IMAGE') )->save( array('value' => I('post.voucher_free_image')) );

			$return = array(
				'status'=>'success',
				'message'=>'编辑成功',
				'jump'=>U('Voucher/free_config')
			);

			$this->osc_alert($return);
			die();
		}
		$this->voucher_free_state_info = $voucher_free_state_info;
		$this->voucher_free_desc_info = $voucher_free_desc_info;
		$this->voucher_free_image_info = $voucher_free_image_info;

		$this->type = 1;
		$this->crumbs='免单券配置';
		$this->action=U('Voucher/free_config');
		$this->display('free_config');
	}


	function add(){

		if(IS_POST){

			$model=new VoucherModel();
			$data=I('post.');

			$data['store_id'] = 0;
			$data['type'] = 1;
			if( empty($data['voucher_title']) ) {
				$status = array('status'=>'back','message'=>'优惠券名称不能为空');
	            $this->osc_alert($status);
			}

			if( empty($data['credit']) ) {
				$status = array('status'=>'back','message'=>'优惠券金额不能为空');
	            $this->osc_alert($status);
			}

			if( empty($data['total_count']) ) {
				$status = array('status'=>'back','message'=>'可领取人数不能为空');
	            $this->osc_alert($status);
			}


			$return=$model->add_voucher($data);

			$this->osc_alert($return);
			die();
		}

		$this->crumbs='新增';
		$this->action=U('Voucher/add');
		$this->display('edit');
	}

	public function membersend()
	{
	    $id = I('get.id');
	    $voucher_info = M('voucher')->where( array('id' => $id) )->find();

	    $this->id = $id;
	    $this->data = $voucher_info;
	    $this->display();
	}

	/**
	 * 上传客户Excel批量赠送优惠券
	 */
	function sendvoucher_tomember_excel_done()
	{
	    set_time_limit(0);
	    $voucher_id = I('get.voucher_id');
	    if(isset($_FILES["file"]) && ($_FILES["file"]["error"] == 0)){

	        $excel_dir = ROOT_PATH.'Uploads/image/'.date('Y-m-d');
	        $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
	        RecursiveMkdir( $excel_dir );

	        $path = $excel_dir.'/'.md5($_FILES['file']['name'].mt_rand(1, 999)).'.'.$extension;
	        $rs = move_uploaded_file($_FILES["file"]["tmp_name"],$path);

	        $voucher_model = D('Home/Voucher');
	        $notify_model = D('Home/Weixinnotify');
	        $result = importExecl($path);
	        if(!empty($result)) {
	            foreach($result as $member)
	            {
	                $member_id = $member[0];
	                $res = $voucher_model->send_user_voucher_byId($voucher_id,$member_id,$check_count =false);
	                if($res == 3)
	                {
	                    $member_info = M('member')->where( array('member_id' => $member_id) )->find();
	                    $notify_model->send_quan_template_msg($member_info['openid'],$voucher_id);

	                    //$quan_msg_info =  M('config')->where( array('name' => 'sendQuanNotice') )->find();
	                    //send_quan_template_msg($openid,$voucher_id)
	                    //发送成功，发送模板消息  Weixinnotify
	                    //send_template_msg($template_data,$url,$to_openid,$template_id)
	                }
	            }
	        }
	    }
	    echo json_encode( array('code' => 1) );
	    die();
	}
	public function voucherlist()
	{
		$id = I('get.id');

		$model=new VoucherModel();

		$data=$model->show_voucher_list_page($id);


		$voucher_info = M('voucher')->where( array('id' => $id) )->find();

		$this->voucher_info = $voucher_info;


		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();

	}

	 public function del(){
		$id = I('get.id');

		$model=new VoucherModel();

		$return=$model->del_voucher($id);

		$this->osc_alert($return);
	 }

}
?>
