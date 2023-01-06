<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.io/
 * @copyright Copyright (c) 2019-2023 Dejavu Tech.
 * @license   https://e-p.io/license
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Admin\Model;

class VoucherModel{
	/**
	 *显示重量单位分页
	 */
	public function show_voucher_class_page($store_id){

		$count=M('Voucher')->where( array('store_id' => $store_id) )->count();

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('Voucher')->where( array('store_id' => $store_id) )->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function show_voucher_list_page($voucher_id)
	{
		$count=M('voucher_list')->where( array('voucher_id' => $voucher_id) )->count();

		$Page = new \Think\Page($count, 20);
		$show  = $Page->show();// 分页显示输出

		$list = M('voucher_list')->where( array('voucher_id' => $voucher_id) )->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}

	public function validate($data,$status='update'){

		$error=array();
		if(empty($data['title'])){
			$error='重量名称必填';
		}elseif(empty($data['unit'])){
			$error='重量单位必填';
		}elseif(empty($data['value'])){
			$error='重量值 必填';
		}



		if($status=='add'){
			if(M('WeightClass')->getByTitle($data['title'])){
				$error='该重量名称已经存在';
			}
		}else{
			if(M('WeightClass')->where('weight_class_id!='.$data['weight_class_id']." AND title='".$data['title']."'")->find()){
				$error='该重量名称已经存在';
			}
		}


		if($error){

			return array(
				'status'=>'back',
				'message'=>$error
			);

		}
	}



	public function add_voucher($data){



			$data['begin_time'] = strtotime($data['begin_time']);
			$data['end_time'] = strtotime($data['end_time']);
			if(empty($data['type'])) {
			    $data['type'] = 0;
			}
			$data['is_limit_goods_buy'] = $data['limit_goods'];
			$voucher_id = M('Voucher')->add($data);


			//limit_goods 0 全场商品,1 部分商品 is_limit_goods_buy

			if( $data['limit_goods'] == 1)
			{
				$goods_ids = $data['goods_ids'];
				$goods_ids_arr = explode(',', $goods_ids);

				if( !empty($goods_ids_arr) )
				{
					foreach($goods_ids_arr as $goods_id)
					{
						$voucher_goods_data = array();
						$voucher_goods_data['voucher_id'] = $voucher_id;
						$voucher_goods_data['goods_id'] = $goods_id;

						M('voucher_goods')->add($voucher_goods_data);
					}
				}
			}

			if($voucher_id){
				$need = $data['total_count'];

				while(true) {
					$voucher = array(
							'voucher_id' => $voucher_id,
							'voucher_title' => $data['voucher_title'],

							'store_id' => $data['store_id'],
					        'type'     => $data['type'],
							'credit' => $data['credit'],
							'limit_money' => $data['limit_money'],
							'is_limit_goods_buy' => $data['limit_goods'],

							'consume' => 'N',
							'begin_time' => $data['begin_time'],
							'end_time' => $data['end_time'],
							'add_time'=>time(),
							);
					$need -= (  M('voucher_list')->add($voucher)) ? 1 : 0;
					if ( $need <= 0 ) break;
				}


				return array(
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('Voucher/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('Voucher/index')
				);
			}


	}

	public function edit_weight_class($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('WeightClass')->save($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('WeightClass/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('WeightClass/index')
				);
			}


	}

	public function del_voucher($id){


		M('voucher_list')->where( array('voucher_id' => $id,'user_id' => 0) )->delete();

		$r=M('voucher')->where(array('id'=>$id ))->delete();

		if($r){
				return array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('Voucher/index')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'删除失败',
				'jump'=>U('Voucher/index')
				);
			}
	}

}
?>
