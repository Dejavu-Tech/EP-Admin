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
use Think\Model;
class LotteryModel extends Model{
   protected  $tableName ='spike_goods';

	public function show_lottery_page($where=' and sg.state=0' ){

		$sql='SELECT sg.*,g.name,g.image,pg.pin_price as pinprice,g.danprice,g.store_id,g.quantity,pg.pin_count,g.seller_count FROM
		'.C('DB_PREFIX').'lottery_goods as sg left join '.C('DB_PREFIX').'goods as g on sg.goods_id=g.goods_id
		left join '.C('DB_PREFIX').'pin_goods as pg on  pg.goods_id = g.goods_id
		where  1=1  '.$where;


		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by sg.addtime desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);


		foreach ($list as $key => $value) {
			$list[$key]['image']=resize($value['image'], 100, 100);

			if( !empty($value['voucher_id']) )
			{
				$voucher_info = M('voucher')->where( array('id' => $value['voucher_id']) )->find();
				$list[$key]['voucher_title'] = '<span class="blue">'.$voucher_info['voucher_title'].'<br/> 剩余数量： '.( $voucher_info['total_count'] - $voucher_info['send_count'] )."</span>";
			} else {
				$list[$key]['voucher_title'] = '<span class="red">未指定券</span>';
			}

			//index.html voucher_title
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}
}
?>
