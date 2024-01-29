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

class OptionModel{


	public function show_option_page($search=''){

		$count=M('option')->where($search)->count();

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();

		$list = M('option')->where($search)->order('option_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function add_option($data){


			$option['name']=$data['name'];
			$option['type']=$data['type'];
			$option['update_time']=date('Y-m-d H:i:s',time());
			if(isset($data['store_id']) ) {
				$option['store_id'] = $data['store_id'];
			}
			foreach ($data['option_value'] as $k=> $v) {
				if(!empty($v)){
					if($v!=end($data['option_value'])){
						$option['value'].=$v['name'].',';
					}else{
						$option['value'].=$v['name'];
					}
				}
			}

			$option_id=M('option')->add($option);

			if($option_id){

				foreach ($data['option_value'] as $k => $v) {
						if(!empty($v)){

							$value['option_id']=$option_id;
							$value['value_name']=$v['name'];
							$value['value_sort_order']=$v['sort_order'];

							M('OptionValue')->add($value);
						}
					}

				return true;
			}else{
				return false;
			}
	}

		public function edit_option($data){

			$option['option_id']=$data['id'];
			$option['name']=$data['name'];
			$option['type']=$data['type'];
			$option['update_time']=date('Y-m-d H:i:s',time());
			foreach ($data['option_value'] as $k=> $v) {
				if(!empty($v)){
					if($v!=end($data['option_value'])){
						$option['value'].=$v['name'].',';
					}else{
						$option['value'].=$v['name'];
					}
				}
			}

			$r=M('option')->save($option);

			if($r){


				$all_option_value =  M('option_value')->where(array('option_id'=>$data['id']))->select();
				$all_option_ids = array();
				foreach($all_option_value as  $val)
				{
					$all_option_ids[$val['option_value_id']] = $val['option_value_id'];
				}

				//M('option_value')->where(array('option_id'=>$data['id']))->delete();

				foreach ($data['option_value'] as $k => $v) {
						if(!empty($v)){
							//option_value_id

							$value['option_id']=$data['id'];
							$value['value_name']=$v['name'];
							$value['value_sort_order']=$v['sort_order'];
							if(isset($v['option_value_id']) && $v['option_value_id'] >0)
							{
								unset($all_option_ids[$v['option_value_id']]);
								M('OptionValue')->where(array('option_value_id' => $v['option_value_id']))->save($value);

							}else{
								M('OptionValue')->add($value);
							}

						}
					}

				if(!empty($all_option_ids))
				{
					M('option_value')->where(array('option_value_id'=>array('in',$all_option_ids )))->delete();

				}

				return true;
			}else{
				return false;
			}
	}


	function getOptions($filter_name,$store_id = 0) {

			$sql = "SELECT * FROM ".C('DB_PREFIX') . "option";
			$sql .= ' WHERE 1= 1 ';
			if (isset($filter_name) && !is_null($filter_name)) {
				$sql .= "  and name LIKE '" . $filter_name . "%'";
			}
			if($store_id > 0)
			{
				$sql .= "  and store_id = {$store_id} ";
			}

			$query = M()->query($sql);

			return $query;
	}
	function getOptionValues($option_id) {
		$option_value_data = array();

		$option_value_query = M()->query("SELECT * FROM "
		. C('DB_PREFIX') . "option_value ov LEFT JOIN "
		. C('DB_PREFIX') . "option o ON (ov.option_id = o.option_id) WHERE ov.option_id ="
		. (int)$option_id);

		foreach ($option_value_query as $option_value) {
			$option_value_data[] = array(
				'option_value_id' => $option_value['option_value_id'],
				'name'            => $option_value['name'],
				'value'           => $option_value['value_name'],
				'sort_order'      => $option_value['value_sort_order']
			);
		}

		return $option_value_data;
	}

}
?>
