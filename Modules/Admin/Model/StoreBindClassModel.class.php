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
class StoreBindClassModel{
	/**
	 *显示分页
	 */
	public function show_store_bind_class_page($seller_id){

		$sql="select * from ".C('DB_PREFIX')."store_bind_class where seller_id={$seller_id}";

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by bid desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}
	function add_bind_class($id)
	{
	    return M('store_bind_class')->where( array('bid' => $id) )->delete();
	}

	function add_store_bind_class($data){

	   return  M('store_bind_class')->add($data);
	}


	function edit_seller_user($d){

		$d['s_passwd']=think_ucenter_encrypt($d['s_passwd'],C('SELLER_PWD_KEY'));

		$r=M('Seller')->where(array('s_id'=>$d['s_id']))->save($d);
		if($r){
			return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('SellerManage/index')
				);
		}else{
			return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('SellerManage/index')
			);
		}

	}

}
?>
