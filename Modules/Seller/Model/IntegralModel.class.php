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
namespace Seller\Model;

class IntegralModel{

	/**
	 *显示订单状态单位分页
	 */
	public function show_order_page($search){

	    $sql = "select p.pin_id,p.is_jiqi,og.goods_id,og.name,p.state,p.need_count,p.end_time,p.begin_time from ".C('DB_PREFIX')."pin as p,".C('DB_PREFIX')."pin_order as o,".C('DB_PREFIX')."order_goods as og
	           where p.order_id= o.order_id and p.order_id = og.order_id
	        ";

		if(isset($search['store_id'])){
			$sql.=" and og.store_id=".$search['store_id'];
		}

		if(isset($search['name']) && !empty($search['name']) ){
			$sql.=" and og.name like '%".$search['name']."%'";
		}
		if(isset($search['state'])){
		    if($search['state'] == -1)
		    {
		    } else if($search['state'] == 0) {
		        $sql.=" and p.state=0 and p.end_time > ".time();
		    } else if($search['state'] == 1) {
		        $sql.=" and p.state=1";
		    } else if($search['state'] == 2) {
		        $sql.=" and (p.state=2 or (p.state = 0 and p.end_time <".time()." ) )";
		    }
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' ORDER BY p.pin_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;


		$list=M()->query($sql);

		foreach($list as $key => $val)
		{

			$sql = "select count(o.order_id) as count from ".C('DB_PREFIX')."pin_order as po,".C('DB_PREFIX')."order as o
	           where po.order_id= o.order_id and po.pin_id = ".$val['pin_id']." and o.order_status_id in(1,2,4,6,7,8,9,10) ";

			$count_arr = M()->query($sql);

			$pin_buy_count = $count_arr[0]['count'];

			$pin_jia_count =  M('jiapinorder')->where( array('pin_id' => $val['pin_id']) )->count();

		    if($val['state'] == 0 && $val['end_time'] <time()) {
		        $val['state'] = 2;
		    }
		    $val['buy_count'] = $pin_buy_count + $pin_jia_count;
		    $list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	/**
		检测客户积分是否足够支付订单
	**/
	public function check_user_score_can_pay($member_id, $sku_str ='', $goods_id)
	{
		$member_info = M('member')->field('score')->where( array('member_id' => $member_id) )->find();

		if( !empty($sku_str) )
		{
			$mult_value_info = $goods_option_mult_value = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' => $sku_str,'goods_id'=>$goods_id) )->find();

			//pin_price
			if($mult_value_info['pin_price'] > $member_info['score'])
			{
				return array('code' => 1,'cur_score' => $member_info['score'],'pay_score' => $mult_value_info['pin_price']);
			}else{
				return array('code' => 0);
			}
		}else{
			$intgral_goods_info = M('intgral_goods')->field('score')->where( array('goods_id' => $goods_id) )->find();
			if($intgral_goods_info['score'] > $member_info['score'])
			{
				return array('code' => 1,'cur_score' => $member_info['score'],'pay_score' => $intgral_goods_info['score']);
			}else{
				return array('code' => 0);
			}
		}
	}


	/**
		积分兑换记录
	**/
	public function show_exchange_integral_page($search)
	{
		//out' orderbuy order_id
		$sql = "select i.*,og.name,og.goods_images,og.quantity from ".C('DB_PREFIX')."integral_flow as i ,".C('DB_PREFIX')."order_goods as og
	           where  i.order_id=og.order_id and i.state = 1 and i.type ='orderbuy' ";

		$sql_count = "select count(i.id) as count from ".C('DB_PREFIX')."integral_flow as i ,".C('DB_PREFIX')."order_goods as og
	           where  i.order_id=og.order_id and i.state = 1 and i.type ='orderbuy' ";

		if(isset($search['goods_name'])){
			$sql.= " and og.name  like  '%".$search['goods_name']."%' ";
			$sql_count .= " and og.name like  '%".$search['goods_name']."%' ";
		}

		//$count = count(M()->query($sql)); array(1) { [0]=> array(1) { ["count"]=> string(2) "21" } }
		$count_arr = M()->query($sql_count);
		$count = $count_arr[0]['count'];


		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' ORDER BY i.id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		foreach($list as $key => $val)
		{
			$member_info = M('member')->where( array('member_id' => $val['member_id']) )->find();
			$val['uname'] = $member_info['uname'];
			$val['avatar'] = $member_info['avatar'];
		    $list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}

	public function show_integra_list_page($search)
	{
		$sql = "select * from ".C('DB_PREFIX')."integral_flow
	           where 1=1 and state = 1 ";

		$sql_count = "select count(id) as count from ".C('DB_PREFIX')."integral_flow
					 where 1=1 and state = 1 ";

		if(isset($search['member_id'])){
			$sql.= " and member_id= ".$search['member_id'];
			$sql_count .= " and member_id= ".$search['member_id'];
		}

		//$count = count(M()->query($sql)); array(1) { [0]=> array(1) { ["count"]=> string(2) "21" } }
		$count_arr = M()->query($sql_count);
		$count = $count_arr[0]['count'];


		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' ORDER BY id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		foreach($list as $key => $val)
		{
		    //$val['buy_count'] = $pin_buy_count + $pin_jia_count;
		    $list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}


	/**
		系统奖励或者扣除客户积分
		CREATE TABLE IF NOT EXISTS `integral_flow` (
		  `id` int(10) NOT NULL COMMENT '自增id',
		  `member_id` int(10) NOT NULL COMMENT '客户id',
		  `in_out` enum('in','out') NOT NULL COMMENT '增加积分，还是减少积分',
		  `type` enum('goodsbuy','refundorder','system_add','system_del') NOT NULL COMMENT '积分获赠/减少 类型',
		  `order_id` int(10) DEFAULT NULL COMMENT '订单id',
		  `addtime` int(10) NOT NULL COMMENT '添加时间'
		)
		2  $User->where('id=5')->setInc('score',3); // 用户的积分加3
		3  $User->where('id=5')->setInc('score'); // 用户的积分加1
		4  $User->where('id=5')->setDec('score',5); // 用户的积分减5
	**/
	private function _do_charge_score($member_id, $score,$type=0)
	{
		if($type ==0 )
		{
			//增加
			M('member')->where( array('member_id' => $member_id) )->setInc('score',$score);
		}else if($type == 1){
			//减少
			M('member')->where( array('member_id' => $member_id) )->setDec('score',$score);
		}
	}

	/**
		检测当前订单是否送过积分，退回积分
	**/
	public function check_refund_order_score($order_id)
	{
		//type  orderbuy
		$flow_info = M('integral_flow')->where( array('order_id' => $order_id, 'state' => 1,'type' => 'orderbuy') )->find();

		$refund_flow_info = M('integral_flow')->where( array('order_id' => $order_id, 'state' => 1,'type' => 'refundorder') )->find();

		if( !empty($flow_info) && empty($refund_flow_info))
		{
			$this->charge_member_score( $flow_info['member_id'], $flow_info['score'],'out', 'refundorder', $order_id );
		}
	}
	//6
	/**
		赠送订单积分
	**/
	public function send_order_score_dr($order_id)
	{
		$integral_flow_info = M('integral_flow')->where( array('order_id' => $order_id, 'state' => 0,'type' =>'goodsbuy') )->find();
		if( !empty($integral_flow_info) )
		{
			$this->_do_charge_score($integral_flow_info['member_id'], $integral_flow_info['score'],0);
			M('integral_flow')->where( array('id' => $integral_flow_info['id']) )->save( array('state' => 1) );
		}
	}
	 public function show_integral_page($search)
    {
        $sql='SELECT p.goods_id,p.name,p.seller_count,p.quantity,p.type as goods_type,p.status,pg.id,p.price,p.image,pg.score FROM '
            .C('DB_PREFIX').'intgral_goods as pg left join '.C('DB_PREFIX').'goods as p on  pg.goods_id=p.goods_id where 1=1  ';

        if(isset($search['customer_id'])){
            $sql.=" and  p.store_id = ".$search['customer_id'];
        }
		//name
		if(isset($search['name'])){
            $sql.=" and  p.name like  '%".$search['name']."%'";
        }

        //'customer_id' => UID
        $count=count(M()->query($sql));

        $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

        $show  = $Page->show();// 分页显示输出

        $sql.=' order by pg.id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

        $list=M()->query($sql);

        foreach ($list as $key => $value) {

            $list[$key]['image']=resize($value['image'], 50, 50);
        }

        return array(
            'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
            'list'=>$list,
            'page'=>$show
        );
    }

	public function charge_member_score( $member_id, $score,$in_out, $type, $order_id=0)
	{
		$log_data = array();
		$log_data['member_id'] = $member_id;
		$log_data['in_out'] = $in_out;
		$log_data['score'] = $score;
		$log_data['type'] = $type;
		$log_data['order_id'] = $order_id;
		$log_data['addtime'] = time();

		$member_score_info = M('member')->field('score')->where( array('member_id' => $member_id) )->find();

		$last_score = ' 原积分：'.$member_score_info['score'];

		if($in_out == 'in')
		{
			if($type == 'goodsbuy')
			{
				//增加积分
				$log_data['state'] = 0;
				$log_data['remark'] = "商品购买，增加积分";
			}else if($type == 'system_add'){
				//系统奖励
				//增加积分
				$log_data['state'] = 1;
				$log_data['remark'] = "系统奖励，增加积分".$last_score;

				$this->_do_charge_score($member_id, $score,0);
			}else if($type == 'invitegift'){
				//邀请者赠送积分
				$log_data['state'] = 1;
				$log_data['remark'] = "邀请者邀请成功，增加积分".$score;

				$this->_do_charge_score($member_id, $score,0);
			}else if($type == 'invitegift_new'){
				//被邀请者赠送积分
				$log_data['state'] = 1;
				$log_data['remark'] = "被邀请者邀请成功，增加积分".$score;

				$this->_do_charge_score($member_id, $score,0);
			}else if($type == 'pintuan_rebate'){
				//拼团返利
				$log_data['state'] = 1;
				$log_data['remark'] = "拼团返利，赠送积分".$score;

				$this->_do_charge_score($member_id, $score,0);
			}
		}else if($in_out == 'out'){
			if( $type =='refundorder' )
			{
				$log_data['state'] = 1;
				$log_data['remark'] = "订单退款，扣除积分".$last_score;
				$this->_do_charge_score($member_id, $score,1);
			}else if($type == 'system_del')
			{
				$log_data['state'] = 1;
				$log_data['remark'] = "系统扣除积分".$last_score;
				$this->_do_charge_score($member_id, $score,1);
			}else if($type == 'orderbuy')
			{
				$log_data['state'] = 1;
				$log_data['remark'] = "支付订单".$last_score;
				$this->_do_charge_score($member_id, $score,1);
				////integral_order
				$integral_order_data = array();
				$integral_order_data['order_id'] = $order_id;
				$integral_order_data['score'] = $score;
				$integral_order_data['addtime'] = time();
				M('integral_order')->add($integral_order_data);
			}
		}
		M('integral_flow')->add($log_data);
		return true;
	}


}
?>
