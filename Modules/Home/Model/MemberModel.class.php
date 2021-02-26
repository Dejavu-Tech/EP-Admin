<?php
namespace Home\Model;
use Think\Model;
class MemberModel extends Model{
	//结算页面中新增用户
	function add_member(){
		$d['uname']=I('uname');
		$d['name']=I('name');
		$d['email']=I('email');
		$d['pwd']  =think_ucenter_encrypt($_POST['password'],C('PWD_KEY'));
		$d['telephone']=I('telephone');
		$d['status']=1;
		$d['create_time']=time();
		$id=$this->add($d);
		
		if($id){
		//写入地址表
			$a['member_id']=$id;
			$a['address']=I('address');
			$a['city_id']=I('city_id');
			$a['name']=I('name');
			$a['telephone']=I('telephone');

			$a['country_id']=I('country_id');
			$a['province_id']=I('province_id');
			$aid=M('Address')->add($a);		
			//会员表更新地址
			if($aid){
				$address['address_id']=$aid;
				$address['member_id']=$id;
				$this->save($address);
			}	
		}
		return $id;
	}
	
	function add_address(){
		//写入地址表
		$a['member_id']=session('user_auth.uid');
		$a['address']=I('address');
		$a['city_id']=I('city_id');
		$a['name']=I('name');
		$a['telephone']=I('telephone');
	
		$a['country_id']=I('country_id');
		$a['province_id']=I('province_id');
		$aid=M('Address')->add($a);		
		//会员表更新地址
		if($aid){
			$address['address_id']=$aid;
			$address['member_id']=session('user_auth.uid');
			M('Member')->save($address);
		}	
		return $aid;		
	}
	
	function get_address_id($uid){
		$aid=$this->field('address_id')->where('member_id='.$uid)->find();
		return $aid['address_id'];
	}
	
	/**
		给会员充值
	**/
	public function charge_member_account($member_id, $money, $type, $trans_id)
	{
		$member_charge_flow_data = array();
		$member_charge_flow_data['member_id'] = $member_id;
		$member_charge_flow_data['trans_id'] = $trans_id;
		$member_charge_flow_data['money'] = $money;
		$member_charge_flow_data['state'] = 6;
		$member_charge_flow_data['charge_time'] = time();
		$member_charge_flow_data['add_time'] = time();
		
		M('member_charge_flow')->add($member_charge_flow_data);
		M('member')->where( array('member_id' => $member_id) )->setInc('account_money', $money); 
	}
	
	
	
	/**
		计算用户佣金
	**/
	function sum_member_commiss($where = array())
	{
		$total_commiss = M('member_commiss_order')->where($where)->sum('money');
		return $total_commiss;
	}
	
	/**
		计算用户佣金
	**/
	function sum_member_fen_commiss($where = array())
	{
		$total_commiss = M('member_sharing_order')->where($where)->sum('money');
		return $total_commiss;
	}
	
	function getAddress($uid) {
		
		if(!isset($uid)){
			return false;
		}
		 
		$sql="SELECT DISTINCT province_id,city_id,country_id FROM ".C('DB_PREFIX')."address WHERE member_id=".$uid;
		
		$area_id=M()->query($sql);
		
		foreach ($area_id as $k => $v) {
			foreach ($v as $key => $value) {
				$area[]=$value;
			}
		}
		if(!isset($area)){
			return;
		}
	
		//地区的id,去除重复的
		$arr=array_unique($area);
		$aid=implode(',',$arr);
		
		$sql="SELECT area_name,area_id FROM ".C('DB_PREFIX')."area WHERE area_id IN (".$aid.")";
		//地区的名字
		$area_name=M()->query($sql);
	
		//取得会员的所有地址
		$address=M('Address')->where('member_id='.$uid)->select();
		
		foreach ($address as $key => $v) {
			$a[$v['address_id']]=$v;
		}
	
		foreach ($a as $k => $v) {
			
			foreach ($area_name as $key => $value) {
				if($v['province_id']==$value['area_id']){
					$a[$k]['province']=$value['area_name'];
				}
				if($v['city_id']==$value['area_id']){
					$a[$k]['city']=$value['area_name'];
				}
				if($v['country_id']==$value['area_id']){
					$a[$k]['country']=$value['area_name'];
				}
			}
			
		}
		return $a;
		
	} 
}