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
class SpikeModel extends Model{

	public function show_spike_page( $where = ''  ){

		$sql='SELECT * FROM '.C('DB_PREFIX').'spike ';

		if(!empty($where))
		{
			$sql .= $where;
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by add_time desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);


		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function show_spikegoods_page( $super_id =0){

	    //goods_id
	    $sql='SELECT g.name,g.image,g.quantity,g.type,g.lock_type,g.danprice,g.pinprice,g.seller_count,g.store_id,sg.id,sg.goods_id, sg.state,sg.addtime FROM '.C('DB_PREFIX').'spike_goods as sg,
	        '.C('DB_PREFIX').'goods as g where sg.goods_id = g.goods_id  and sg.spike_id = '.$super_id;

	    $count=count(M()->query($sql));

	    $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

	    $show  = $Page->show();// 分页显示输出

	    $sql.=' order by sg.addtime desc LIMIT '.$Page->firstRow.','.$Page->listRows;

	    $list=M()->query($sql);
	    foreach ($list as $key => $value) {
	        $list[$key]['image']=resize($value['image'], 100, 100);
	    }

	    return array(
	        'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
	        'list'=>$list,
	        'page'=>$show
	    );

	}


}
?>
