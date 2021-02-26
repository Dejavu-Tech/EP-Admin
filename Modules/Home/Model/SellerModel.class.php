<?php
namespace Home\Model;
use Think\Model;
/**
 * 商家模块
 * @author Albert.Z
 *
 */
class SellerModel extends Model{

	public function getStoreSellerCount($store_id)
	{
	   $vir_count = M('goods')->where( array('store_id' => $store_id) )->sum('virtual_count');
	   $seller_count = M('goods')->where( array('store_id' => $store_id) )->sum('seller_count');

	    return ($vir_count+$seller_count);
	   /**
		$sql = "select sum(quantity) as total_quantiry from ".C('DB_PREFIX')."order as o ,".C('DB_PREFIX')."order_goods as og
				where o.order_id=og.order_id and o.order_status_id in (1,2,4,6) and og.store_id = ".$store_id;
		$total_quantiry = M()->query($sql);

		return intval($total_quantiry['total_quantiry']);
		**/
	}
	/**
		获取商家评价
	*/
	public function get_pingjia($store_id)
	{
		$sql=" select oc.* from ".C('DB_PREFIX')."order_goods as o ,".C('DB_PREFIX')."order_comment as oc
				where o.order_id =oc.order_id and o.store_id = {$store_id} ";

		$result = array('miaoshu' =>5,'jiage' => 5,'zhiliang' => 5);

		$comment_list = M()->query($sql);

		if( !empty($comment_list) )
		{
			$total_count = count($comment_list);
			$total_miaoshu = 0;
			$total_jiage = 0;
			$total_zhiliang = 0;

			foreach($comment_list as $comment)
			{
				$total_miaoshu += $comment['star'];
				$total_jiage += $comment['star2'];
				$total_zhiliang += $comment['star3'];
			}
			$total_miaoshu = round($total_miaoshu / $total_count,2);
			$total_jiage = round($total_jiage / $total_count,2);
			$total_zhiliang = round($total_zhiliang / $total_count,2);

			$result = array('miaoshu' => $total_miaoshu,'jiage' =>$total_jiage,'zhiliang'=>$total_zhiliang);
		}

		return $result;
	}
}


