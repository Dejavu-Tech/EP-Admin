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
namespace Seller\Model;

class ExpressModel{

	public function update($data)
	{

		$ins_data = array();
		$ins_data['name'] = $data['name'];
		$ins_data['simplecode'] = $data['simplecode'];
		$ins_data['customer_name'] = $data['customer_name'];
		$ins_data['customer_pwd'] = $data['customer_pwd'];

		$id = $data['id'];
		if( !empty($id) && $id > 0 )
		{
			M('eaterplanet_ecommerce_express')->where( array('id' => $id) )->save( $ins_data );

		}else{
			M('eaterplanet_ecommerce_express')->add( $ins_data );

		}
	}

	public function get_express_info($id)
	{
		$info = M('eaterplanet_ecommerce_express')->where( array('id' => $id ) )->find();

		return $info;
	}


	public function load_all_express()
	{
        $where = array();
		$list = M('eaterplanet_ecommerce_express')->where($where)->field('id, name,simplecode')->order('name')->select();
		return $list;
	}

    public function load_kdn_express()
    {
        $where = array();
		$code_array = array();
		$kdn_list = M('eaterplanet_ecommerce_kdniao_template')->field('distinct(express_code) as express_code')->select();
		foreach($kdn_list as $v){
			$code_array[] = $v['express_code'];
		}
        $where['simplecode'] = array('in', $code_array);
        $list = M('eaterplanet_ecommerce_express')->where($where)->field('id, name,simplecode')->select();
        return $list;
    }

	public function show_express_page($search = array()){

	    $where = array();

	    if(!empty($search) && isset($search['store_id'])) {
	        $where['store_id'] = $search['store_id'];
	    }

		$count=M('seller_express')->where($where)->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('seller_express')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}

}
?>
