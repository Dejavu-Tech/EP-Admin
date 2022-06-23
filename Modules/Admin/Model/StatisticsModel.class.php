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
namespace Admin\Model;
class StatisticsModel{

	function show_visitors_ip($date=''){

		$sql='SELECT * FROM '.C('DB_PREFIX').'visitors_ip';

		if(!empty($date)){
			$sql.=" where last_visit_time='".$date."'";
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by vi_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		return array(
			'count'=>$count,
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	//取得所有访问IP量
	function get_all_visitors_ip(){
		return M()->query('SELECT DISTINCT ip from '.C('DB_PREFIX').'visitors_ip');
	}
	//取某一天访问IP量
	function get_visitors_ip_by_date($date){
		return M()->query("SELECT DISTINCT ip from ".C('DB_PREFIX')."visitors_ip where last_visit_time='".$date."'");
	}
	//取得所有客户资料
	function get_all_member(){
		return M('member')->select();
	}

	function get_all_member_count(){

		$sql = "SELECT count(member_id) AS total FROM " . C('DB_PREFIX') . "member ";

		$total=M()->query($sql);


		return $total[0]['total'];

		//return M('member')->count();
	}

	//今日注册客户
	function get_today_register_member(){
		//时间大于零点时间戳
		return M()->query("SELECT * from ".C('DB_PREFIX')."member where create_time>=".strtotime(date('Y-m-d')));
	}
	public function get_seller_sales($data=array())
	{

	}

	public function get_total_sales($data=array()) {

		$sql = "SELECT SUM(o.total) AS total FROM " . C('DB_PREFIX') . "order as o," . C('DB_PREFIX') . "order_goods as og  WHERE  o.order_id=og.order_id and o.order_status_id IN (1,2,4,6,11)";

		if (!empty($data['date_added'])) {
			$sql .= " AND o.date_added>=".strtotime(date($data['date_added']))." AND o.date_added<=".(strtotime(date($data['date_added']))+86400);

		}

		if(isset($data['store_id']) && !empty($data['store_id']) )
		{
		    $sql .= " and  og.store_id = ".intval($data['store_id']);

		}


		$total=M()->query($sql);

		$sale_total=$total[0]['total'];

		if($sale_total){

			if ($sale_total > 1000000000000) {
				$data = round($sale_total / 1000000000000, 1) . 'T';
			} elseif ($sale_total > 1000000000) {
				$data = round($sale_total / 1000000000, 1) . 'B';
			} elseif ($sale_total > 1000000) {
				$data = round($sale_total / 1000000, 1) . 'M';
			} elseif ($sale_total > 1000) {
				$data = round($sale_total / 1000, 1) . 'K';
			} else {
				$data = round($sale_total,2);
			}
		}else{

			return 0;
		}
		return $data;
	}

	public function get_total_order($data=array()) {

		$sql = "SELECT count(*) AS total FROM " . C('DB_PREFIX') . "order as o," . C('DB_PREFIX') . "order_goods as og where o.order_id=og.order_id ";



		if(isset($data['store_id']) && !empty($data['store_id']) )
		{
		    $sql .= " and  og.store_id = ".intval($data['store_id']);

		}
		if (!empty($data['date_added'])) {
			$sql .= " and  o.date_added>=".strtotime(date($data['date_added']))." AND o.date_added<=".(strtotime(date($data['date_added']))+86400);
		}

		$total=M()->query($sql);


		return $total[0]['total'];
	}

	function get_user_action(){
		return M('user_action')->order('ua_id desc')->limit(C('BACK_PAGE_NUM'))->select();
	}

}
?>
