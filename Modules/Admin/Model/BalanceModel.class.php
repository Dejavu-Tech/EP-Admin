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
namespace Admin\Model;
use Think\Model;
class BalanceModel extends Model{

	public function show_balance_order_page($bid)
	{
	    $sql='SELECT * FROM '.C('DB_PREFIX').'balance_order where bid='.$bid;

	    $count=count(M()->query($sql));

	    $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

	    $show  = $Page->show();// 分页显示输出

	    $sql.=' order by boid asc LIMIT '.$Page->firstRow.','.$Page->listRows;

	    $list=M()->query($sql);
	    foreach($list as $key => $val)
	    {
	        $seller_info = M('seller')->where( array('s_id' =>$val['seller_id']) )->find();
	        $val['seller'] = $seller_info;
			$order_info = M('order')->field('order_num_alias')->where( array('order_id' => $val['order_id']) )->find();
	        //order_id
	        $order_goods = M('order_goods')->field('goods_id')->where(array('order_id' => $val['order_id']) )->find();

	        $goods_to_category = M('goods_to_category')->where( array('goods_id' => $order_goods['goods_id']) )->find();

	        $store_bind_class = M('store_bind_class')->where( array('seller_id' => $val['seller_id'],
	            'class_1' =>$goods_to_category['class_id1'],'class_2' => 0,
	            'class_3' => 0
	        ) )->find();
			$val['order_sn'] = $order_info['order_num_alias'];
	        $val['store_bind_class'] = $store_bind_class;

			$member_commiss_order = M('member_commiss_order')->where( array('order_id' => $val['order_id']) )->find();
			$val['commiss_money'] = 0;
			if( !empty($member_commiss_order) )
			{
				$val['commiss_money'] = $member_commiss_order['money'];
			}

	        $list[$key] = $val;
	    }
	    return array(
	        'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
	        'list'=>$list,
	        'page'=>$show
	    );
	}
	/**
		商家等待提现金额
	**/
	public function wait_balance_order($seller_id)
	{
		$sql = "select o.order_id,o.total,gtc.class_id1,gtc.class_id2,gtc.class_id3 from ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og,
	            ".C('DB_PREFIX')."goods_to_category as gtc
	         where o.order_id = og.order_id and gtc.goods_id = og.goods_id and og.store_id = ".$seller_id."
	             and o.is_balance = 0 and o.order_status_id in (1,4,6,11) ";
	     $order_list = M()->query($sql);

	     $seller = M('seller')->where(array('s_id' =>$seller_id) )->select();
	     //获取商家所有绑定类目
	     $store_bind_class = M('store_bind_class')->where( array('seller_id' => $seller['s_id']) )->select();

	     $class_rate_arr = array();
	     foreach($store_bind_class as $bind_class)
	     {
	         $key = $bind_class['class_1'].'_'.$bind_class['class_2'].'_'.$bind_class['class_3'];
	         $class_rate_arr[$key] = $bind_class['commis_rate'];
	     }

	     $tongji_money = 0;
	     $total_reduce_money = 0;

	     foreach($order_list as $order)
	     {
	         $reduce_money = 0;
	         $del_moeny = 0;
	         //只按照一级的类目进行计算
	         $fkey = $order['class_id1'].'_0_0';
	         $reduce_money = $order['total'] * $class_rate_arr[$fkey] * 0.01;
	         $del_moeny =  $order['total'] - $reduce_money;
	         $tongji_money += $del_moeny;
	         $total_reduce_money += $reduce_money;
	     }
	     return $tongji_money;
	}

	/**
	 * 获取提现记录
	 */
	public function show_balance_assets_page($search='')
	{
	    $where =' where st.seller_id=s.s_id ';
	    if(!empty($search)) {
	        $where .= $search;
	    }
	    $sql='SELECT st.* FROM '.C('DB_PREFIX').'seller_tixian as st,'.C('DB_PREFIX').'seller as s  '.$where;

	    $count=count(M()->query($sql));

	    $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

	    $show  = $Page->show();// 分页显示输出

	    $sql.=' order by st.state asc ,st.addtime desc LIMIT '.$Page->firstRow.','.$Page->listRows;

	    $list=M()->query($sql);
	    foreach($list as $key => $val)
	    {
	        $seller_info = M('seller')->where( array('s_id' =>$val['seller_id']) )->find();
	        $val['seller'] = $seller_info;
	        $list[$key] = $val;
	    }

	    return array(
	        'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
	        'list'=>$list,
	        'page'=>$show
	    );
	}

	public function show_balance_page($search=''){

	    $where ='';
		if(!empty($search)) {
		    $where = 'where '.$search;
		}
	    $sql='SELECT * FROM '.C('DB_PREFIX').'balance '.$where;

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by state asc ,addtime desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);
        foreach($list as $key => $val)
        {
            $seller_info = M('seller')->where( array('s_id' =>$val['seller_id']) )->find();
            $val['seller'] = $seller_info;


            $list[$key] = $val;
        }
		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

}
?>
