<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      http://www.ch871.com/
 * @copyright Copyright (c) 2019-2021 ch871.com.
 * @license   http://www.ch871.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Admin\Model;

class PinModel{

	/**
	 *显示订单状态单位分页
	 */
	public function show_order_page($search){

	    $sql = "select p.pin_id,og.goods_id,og.name,p.state,p.need_count,p.end_time,p.begin_time from ".C('DB_PREFIX')."pin as p,".C('DB_PREFIX')."pin_order as o,".C('DB_PREFIX')."order_goods as og
	           where p.order_id= o.order_id and p.order_id = og.order_id
	        ";

		if(isset($search['store_id'])){
			$sql.=" and og.store_id=".$search['store_id'];
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



		    if($val['state'] == 0 && $val['end_time'] <time()) {
		        $val['state'] = 2;
		    }
		    $val['buy_count'] = $pin_buy_count;
		    $list[$key] = $val;
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function pin_info()
	{

	}

}
?>
