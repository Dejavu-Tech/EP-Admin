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
use Admin\Model\MemberModel;

class MemberController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='用户';
			$this->breadcrumb2='前台用户';
	}

     public function index(){

		$model=new MemberModel();

		$filter=I('get.');

		$search=array();

		if(isset($filter['name'])){
			$search['name']=$filter['name'];
		}

		$data=$model->show_member_page($search);

		foreach($data['list'] as $key => $val)
		{
			$address_info = M('address')->where( array('member_id' => $val['member_id']) )->order('is_default desc')->find();

			if(!empty($address_info)) {
				$val['telephone'] = $address_info['telephone'];
			}
			$data['list'][$key] = $val;
		}


		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	 /**
		分销提现申请
	 **/
	 public function commissapply()
	 {
		 $this->breadcrumb2='分销提现申请';

		 $model=new MemberModel();

		$filter=I('get.');

		$search=array();

		if(isset($filter['name'])){
			$search['name']=$filter['name'];
		}

		$data=$model->show_applymembercomiss_page($search);

		foreach($data['list'] as $key => $val)
		{
			$address_info = M('address')->where( array('member_id' => $val['member_id']) )->order('is_default desc')->find();

			if(!empty($address_info)) {
				$val['telephone'] = $address_info['telephone'];
			}
			$data['list'][$key] = $val;
		}


		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->display();
	 }
	 /**
	 分销申请
	 **/
	 public function apply()
	 {
		 $this->breadcrumb2='分销商申请';

		$model=new MemberModel();

		$filter=I('get.');

		$search=array();

		if(isset($filter['name'])){
			$search['name']=$filter['name'];
		}

		$data=$model->show_applymember_page($search);

		foreach($data['list'] as $key => $val)
		{
			$address_info = M('address')->where( array('member_id' => $val['member_id']) )->order('is_default desc')->find();

			if(!empty($address_info)) {
				$val['telephone'] = $address_info['telephone'];
			}
			$data['list'][$key] = $val;
		}


		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	 /**
	  * 导出客户名单
	  */
	function export()
	{
	    $filter=I('get.');

	    $search=array();

	    if(isset($filter['name'])){
	        $search['name']=$filter['name'];
	    }

	    if(isset($filter['tel'])){
	        $search['tel']=$filter['tel'];
	    }

	    $sql="select * from ".C('DB_PREFIX')."member where 1 ";

	    if(isset($search['name'])){
	        $sql.=" and uname like '%".$search['name']."%'";
	    }

	    if(isset($search['tel'])){
	        $sql.=" and telephone='".$search['tel']."'";
	    }

	    $sql.=' order by member_id desc LIMIT 100000 ';
	    $list = M()->query($sql);

	    $need_data = array();
	    foreach($list as $val)
	    {
	        $tmp_data = array();
	        $tmp_data['member_id'] = $val['member_id'];
	        $tmp_data['openid'] = $val['openid'];
	        $tmp_data['name'] = $val['name'];
	        $tmp_data['telephone'] = ' ';

	        $tmp_data['address_name'] = '未填写';
	        $tmp_data['province'] = '未填写';
	        $tmp_data['city'] = '未填写';
	        $tmp_data['country'] = '未填写';
	        $tmp_data['address'] = '未填写';


            $address_info = M('address')->where( array('member_id' => $val['member_id']) )->order('is_default desc')->find();

            if(!empty($address_info)) {
                $province_info = M('area')->where( array('area_id' => $address_info['province_id']) )->find();
                if(!empty($province_info)) {
                    $tmp_data['province'] = $province_info['area_name'];
                }
                $city_info = M('area')->where( array('area_id' => $address_info['city_id']) )->find();
                if(!empty($city_info)) {
                    $tmp_data['city'] = $city_info['area_name'];
                }

                $country_info = M('area')->where( array('area_id' => $address_info['country_id']) )->find();
                if(!empty($country_info)) {
                    $tmp_data['country'] = $country_info['area_name'];
                }
                $tmp_data['telephone'] = ' '.$address_info['telephone'];
                $tmp_data['address_name'] = $address_info['name'];
                $tmp_data['address'] = $address_info['address'];
            }

	        $need_data[] = $tmp_data;
	    }

	    $xlsCell  = array(
	        array('member_id','客户ID'),
	        array('openid','OPENID'),
	        array('name','昵称'),
	        array('address_name','收件人姓名'),
	        array('telephone','联系电话'),
	        array('province','省份'),
	        array('city','城市'),
	        array('country','区 '),
	        array('address','详细地址 ')
	    );
	    $expTitle = '客户信息_'.date('Y-m-d H:i:s');
	    export_excel($expTitle,$xlsCell,$need_data);

	}
	/**
	升级成为分销商
	**/
	function fencommiss()
	{
		$id = I('get.id');

		M('member')->where( array('member_id' => $id) )->save( array('comsiss_flag' => 1) );

		$member_commiss = M('member_commiss')->where( array('member_id' => $id) )->find();
		if(empty($member_commiss))
		{
			$data = array();
			$data['member_id'] = $id;
			$data['money'] = 0;
			$data['dongmoney'] = 0;
			$data['getmoney'] = 0;
			M('member_commiss')->add($data);
		}
		$return = array();
		$return['status'] = 'success';
		$return['message'] = '操作成功';
		$return['jump']  = U('Member/apply');

		$this->osc_alert($return);
	}

	//'{:U("Member/commissmoneyapply",array("id"=>$m["member_id"],"aid" => $m["id"], "state" => 1))}' >
	/**
		分佣提现申请
	**/
	function commissmoneyapply()
	{
		$aid = I('get.aid',0);
		$id = I('get.id',0);
		$state = I('get.state',0,'intval');

		$member_commiss = M('member_commiss')->where( array('member_id' => $id) )->find();
		$tixian_order = M('tixian_order')->where( array('id' => $aid) )->find();

		if($state == 1)
		{
			//money dongmoney  getmoney
			$data = array();
			$data['getmoney'] = $member_commiss['getmoney'] + $tixian_order['money'];
			$data['dongmoney'] = $member_commiss['dongmoney'] - $tixian_order['money'];

			M('member_commiss')->where( array('member_id' => $id) )->save($data);

			M('tixian_order')->where( array('id' => $aid) )->save( array('state' => 1,'shentime' => time()) );

		} else if($state == 2){

			$data = array();
			$data['money'] = $member_commiss['money'] + $tixian_order['money'];
			$data['dongmoney'] = $member_commiss['dongmoney'] - $tixian_order['money'];
			M('member_commiss')->where( array('member_id' => $id) )->save($data);

			M('tixian_order')->where( array('id' => $aid) )->save( array('state' => 2,'shentime' => time()) );
		}

		$return = array();
		$return['status'] = 'success';
		$return['message'] = '操作成功';
		$return['jump']  = U('Member/commissapply');

		$this->osc_alert($return);
	}
	function fencommissapply()
	{
		$aid = I('get.aid',0);
		$id = I('get.id',0);

		M('member_commiss_apply')->where( array('id' => $aid) )->save( array('state' => 1) );
		M('member')->where( array('member_id' => $id) )->save( array('comsiss_flag' => 1) );

		$member_commiss = M('member_commiss')->where( array('member_id' => $id) )->find();
		if(empty($member_commiss))
		{
			$data = array();
			$data['member_id'] = $id;
			$data['money'] = 0;
			$data['dongmoney'] = 0;
			$data['getmoney'] = 0;
			M('member_commiss')->add($data);
		}
		$return = array();
		$return['status'] = 'success';
		$return['message'] = '操作成功';
		$return['jump']  = U('Member/apply');

		$this->osc_alert($return);
	}
	function add(){
		$model=new MemberModel();
		if(IS_POST){
			$data=I('post.');
			$return=$model->add_member($data);
			$this->osc_alert($return);
		}
		$this->crumbs='新增';

		$this->display();
	}

	function info(){
		$model=new MemberModel();
		if(IS_POST){
			$data=I('post.');
			$return=$model->edit_info($data);
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('Member/info');
		$this->data=$model->info(I('id'));

		$this->display();
	}

}
?>
