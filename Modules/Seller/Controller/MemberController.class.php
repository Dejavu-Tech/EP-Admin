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
use Admin\Model\MemberModel;

class MemberController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='用户管理';
			$this->breadcrumb2='前台用户';
	}

     public function index(){

		$model=new MemberModel();

		$filter=I('get.');

		$search=array();

		if(isset($filter['name'])){
			$search['name']=$filter['name'];
		}
		if(isset($filter['share_id'])){
			$search['share_id']=$filter['share_id'];
		}
		if(isset($filter['level_id']) && $filter['level_id'] != -1){
			$search['level_id']=$filter['level_id'];
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

		$level_list = array();
		$level_list = M('member_level')->order('id asc')->select();

		$this->level_id = I('get.level_id',0);
		$this->level_list = $level_list;
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }


	 public function zhenquery()
	{

		$keyword = I('request.keyword');
		$params = array();

		$condition =" 1=1 ";

		$this->keyword = $keyword;

		if (!empty($keyword)) {
			$condition .= ' AND ( `username` LIKE '.'"%' . $keyword . '%"'.' or `telephone` like '.'"%' . $keyword . '%"'.' )';
		}


		$ds = M('eaterplanet_ecommerce_member')->where( $condition )->order('member_id asc')->select();

		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);

			$value['id'] = $value['member_id'];

		}

		unset($value);
		$suggest = I('request.suggest');
		if ( !empty($suggest)) {
			exit(json_encode(array('value' => $ds)));
		}

		$this->ds = $ds;
		$this->display('Member/query');
	}

	public function query()
	{
		$condition =" 1=1 ";

		$keyword = trim($_GPC['keyword']);
		$params = array();

		$this->keyword = $keyword;

		if (!empty($keyword)) {
			$condition .= ' AND ( `username` LIKE '.'"%' . $keyword . '%"'.' )';
		}

		$ds = M('eaterplanet_ecommerce_member')->where( $condition )->order('member_id asc')->select();


		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);
			$value['avatar'] = ($value['avatar']);

		}

		unset($value);

		$suggest = I('request.suggest');
		if ( !empty($suggest)) {
			exit(json_encode(array('value' => $ds)));
		}
$this->ds = $ds;
		$this->display('Member/query');
	}

	 /**
		资金流水
	 **/
	 public function charge_flow()
	 {
		//id/26
		$member_id = I('get.id');
		$state=I('get.state',0);

		$model=new MemberModel();

		$search=array('member_id' => $member_id);

		if(isset($state) && $state > 0){
			$search['state']=$state;
		}
		//member_charge_flow

		$data=$model->show_member_charge_page($search);

		$this->member_id = $member_id;
		$this->assign('state',$state);
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	 public function dellevel()
	 {
		 //id/4
		 $id = I('get.id');
		// $level_info = M('member_level')->where( array('id' => $id) )->find();

		 M('member')->where( array('level_id' => $id) )->save( array('level_id' => 0) );

		 M('member_level')->where( array('id' => $id) )->delete();

		 $return = array();
		 $return['status'] = 'success';
		 $return['message'] = '操作成功';
		 $return['jump']  = U('Member/level');

		 $this->osc_alert($return);
	 }
	 public function level()
	 {
		 $this->breadcrumb2='客户等级';

		 $model=new MemberModel();
		 $data=$model->show_member_level();

		 $member_default_levelname_info = M('config')->where( array('name' => 'member_default_levelname') )->find();
		 $member_defualt_discount_info = M('config')->where( array('name' => 'member_defualt_discount') )->find();

		 $default = array('id'=>'default', 'level' => 0,'levelname' => $member_default_levelname_info['value'],'discount' => $member_defualt_discount_info['value']);

		 array_unshift($data['list'], $default );

		 $this->assign('empty',$data['empty']);// 赋值数据集
		 $this->assign('list',$data['list']);// 赋值数据集
		 $this->assign('page',$data['page']);// 赋值分页输出

    	 $this->display();
	 }

	 public function editlevel()
	 {
		 $this->breadcrumb2='客户等级';
		 $model=new MemberModel();
		 $id = I('get.id');

		 $member_default_levelname_info = M('config')->where( array('name' => 'member_default_levelname') )->find();
		 $member_defualt_discount_info = M('config')->where( array('name' => 'member_defualt_discount') )->find();

		 $default = array('id'=>'default', 'level' => 0,'levelname' => $member_default_levelname_info['value'],'discount' => $member_defualt_discount_info['value']);
		 if( $id == 'default' )
		 {
			 $level = $default;
		 }else{
			 $level = M('member_level')->where( array('id' => $id) )->find();
		 }

		 if(IS_POST){
			$data=I('post.');
			$return=$model->addlevel($data);
			$this->osc_alert($return);
		}
		$this->level = $level;
		$this->display('addlevel');
	 }

	 public function levelconfig()
	 {
		 $this->breadcrumb2='客户等级';
		 $model=new MemberModel();
		 $member_level_is_open_info = M('config')->where( array('name' => 'member_level_is_open') )->find();
		  if(IS_POST){
			$data=I('post.');
			$return=$model->levelconfig($data);
			$this->osc_alert($return);
		}

		 $this->member_level_is_open_info = $member_level_is_open_info;
		 $this->display();
	 }

	 public function addlevel()
	 {
		 $this->breadcrumb2='客户等级';
		 $model=new MemberModel();

		 if(IS_POST){
			$data=I('post.');
			$return=$model->addlevel($data);
			$this->osc_alert($return);
		}
		 $this->display();
	 }

	 public function jiaindex(){

		$model=new MemberModel();

		$filter=I('get.');

		$search=array();

		if(isset($filter['name'])){
			$search['name']=$filter['name'];
		}

		$data=$model->show_jiamember_page($search);

		foreach($data['list'] as $key => $val)
		{

			$data['list'][$key] = $val;
		}

		$this->breadcrumb2='机器人管理';
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	 public function down()
	 {
		 //id/1964
		 $id = I('get.id');
		 $type = I('get.type',1);

		 $search =  array();
		 $search['member_id'] = $id;
		 $search['type'] = $type;


		 $model=new MemberModel();
		 $data=$model->show_member_down_page($search);

		foreach($data['list'] as $key => $val)
		{
			$address_info = M('address')->where( array('member_id' => $val['member_id']) )->order('is_default desc')->find();

			if(!empty($address_info)) {
				$val['telephone'] = $address_info['telephone'];
			}
			$data['list'][$key] = $val;
		}

		$level = C('commiss_level_num');


		$this->id = $id;
		$this->level = $level;
		$this->type = $type;
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

    	$this->display();
	 }

	 public function jiadel()
	 {
		 //id/1867
		 $id = I('get.id');
		 M('jiauser')->where( array('id' => $id) )->delete();

		$return = array();
		$return['status'] = 'success';
		$return['message'] = '操作成功';
		$return['jump']  = U('Member/jiaindex');

		$this->osc_alert($return);
	 }
	 public function jiaadd()
	 {
		 if(IS_POST){
			$data = I('post.');
			$data['avatar'] = C('SITE_URL').'Uploads/image/'.$data['image'];
			M('jiauser')->add($data);
			$return = array();
			$return['status'] = 'success';
			$return['message'] = '操作成功';
			$return['jump']  = U('Member/jiaindex');

			$this->osc_alert($return);
		 }
		 $this->breadcrumb2='机器人管理';
		 $this->action = U('Member/jiaadd');
		 $this->display('jiaedit');
	 }
	 public function jiaedit()
	 {
		 if(IS_POST){
			$data = I('post.');

			$jia_info = M('jiauser')->where( array('id' => $data['id']) )->find();

			if( $jia_info['avatar'] == $data['image'])
			{

			}else{
				$data['avatar'] = C('SITE_URL').'Uploads/image/'.$data['image'];
			}

			M('jiauser')->save($data);
			$return = array();
			$return['status'] = 'success';
			$return['message'] = '操作成功';
			$return['jump']  = U('Member/jiaindex');

			$this->osc_alert($return);
		 }

		 $id = I('get.id');
		 $jiauser =  M('jiauser')->where( array('id' => $id) )->find();
		 $this->breadcrumb2='机器人管理';
		 $this->jiauser = $jiauser;
		 $this->action = U('Member/jiaedit');
		 $this->display('jiaedit');
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
		客户积分列表
	 **/
	 public function integra_list()
	 {
		$id = I('get.id');

		$member_info = M('member')->where( array('member_id' => $id) )->find();

		$search = array();
		$search['member_id'] = $id;

		$model= D('Seller/Integral');
		$data=$model->show_integra_list_page($search);

		$this->member_info = $member_info;
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出
		$this->display();
	 }

	 /**
		充值积分
	 **/
	 public function charge_score()
	 {
		 $score = I('post.score');
		 $score = intval($score);

		 $member_id = I('post.member_id');

		$integral_model =  D('Seller/Integral');

		$member_info = M('member')->field('score')->where(array('member_id'=>$member_id))->find();

		if($score < 0)
		{
			//系统扣除 system_add system_del
			//
			$integral_model->charge_member_score($member_id, -$score,'out', 'system_del');
		}else{
			//系统奖励
			//$integral_model->charge_member_score( $member_id, $score,$in_out, $type, $order_id=0)
			$integral_model->charge_member_score($member_id, $score,'in', 'system_add');
		}

		echo json_encode( array('code' => 0) );
		die();
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


		 $level_list=$model->show_member_level();

		 $member_default_levelname_info = M('config')->where( array('name' => 'member_default_levelname') )->find();
		 $member_defualt_discount_info = M('config')->where( array('name' => 'member_defualt_discount') )->find();

		 $default = array('id'=>'0', 'level' => 0,'levelname' => $member_default_levelname_info['value'],'discount' => $member_defualt_discount_info['value']);

		 array_unshift($level_list['list'], $default );

		$this->level_list_data = $level_list['list'];
		$this->display();
	}

}
?>
