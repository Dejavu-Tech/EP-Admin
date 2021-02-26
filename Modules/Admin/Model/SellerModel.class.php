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
class SellerModel{
	/**
	 *显示分页
	 */
	public function show_seller_user_page($search){

		$sql="select * from ".C('DB_PREFIX')."seller ";

		//s_true_name s_true_name
		if( !empty($search) && !empty($search['s_true_name']))
		{
			$sql .= " where s_true_name LIKE '%".$search['s_true_name']."%' ";
		}

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$sql.=' order by s_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);
		$site_c = M('config')->where( array('name' => 'SITE_URL') )->find();
		foreach($list as $key => $val)
		{
			$seller_view_link = $site_c['value']."/index.php?s=/seller/info/seller_id/".$val['s_id'];
			$apply_relship = M('apply_relship')->where( array('seller_id' => $val['s_id']) )->find();

			$list[$key]['seller_view_link'] = $seller_view_link;

		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}
	public function validate($data){

		$error=array();
		if(empty($data['image'])){
			$error='图片必须';
		}

		if($error){
			return array(
				'status'=>'back',
				'message'=>$error
			);

		}
	}

	public function edit_ad($data){

			$error=$this->validate($data);

			if($error){
				return $error;
			}

			$r=M('seller_ad')->save($data);

			if($r){
				return array(
				'status'=>'success',
				'message'=>'修改成功',
				'jump'=>U('Seller/adlist')
				);
			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('Seller/adlist')
				);
			}
	}


	public function add_ad($data)
	{
		$error=$this->validate($data);

		if($error){
			return $error;
		}

		$data['addtime'] = time();
		$r=M('seller_ad')->add($data);

		if($r){
			return array(
			'status'=>'success',
			'message'=>'新增成功',
			'jump'=>U('Seller/adlist')
			);
		}else{
			return array(
			'status'=>'fail',
			'message'=>'新增失败',
			'jump'=>U('Seller/adlist')
			);
		}
	}

	public function show_slider_page($seller_id){

		$count=M('seller_ad')->where( array('seller_id' => $seller_id) )->count();
		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
		$show  = $Page->show();// 分页显示输出

		$list = M('seller_ad')->where(array('seller_id' => $seller_id))->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();


		foreach ($list as $key => $value) {
			$list[$key]['image']=resize($value['image'], 100, 100);
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

    function show_apply_user_page($search = array())
    {
        $sql="select * from ".C('DB_PREFIX')."apply ";

		if(!empty($search))
		{
			if(isset($search['state']))
			{
				$where = ' where state = '.intval($search['state']);
				$sql .= $where;
			}
		}

        $count=count(M()->query($sql));

        $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));
        $show  = $Page->show();// 分页显示输出

        $sql.=' order by state asc,id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

        $list=M()->query($sql);
        foreach($list as $key => $val)
        {
            $goods_category = M('goods_category')->where( array('id' => $val['category_id']) )->find();
            $val['category_name'] = $goods_category['name'];
            $list[$key] = $val;
        }
        return array(
            'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
            'list'=>$list,
            'page'=>$show
        );
    }

	function add_seller_user($data){
			if(empty($data['s_uname'])){
				$error="卖家名不能为空！！";
			}elseif(empty($data['s_passwd'])){
				$error="密码不能为空！！";
			}

			if($error){
				return array(
					'status'=>'back',
					'message'=>$error
				);
			}

			$data['s_passwd']  =think_ucenter_encrypt($data['s_passwd'],C('SELLER_PWD_KEY'));
			$data['s_create_time']  =time();
			$data['s_status']  =1;
			$res = M('Seller')->add($data);
			$s_id = M('Seller')->getLastInsID();
			if($res){


				return array(
				's_id' => $s_id,
				'status'=>'success',
				'message'=>'新增成功',
				'jump'=>U('SellerManage/index')
				);
			}else{
				return array(
				'status'=>'back',
				'message'=>'新增失败'

				);
			}
	}


	function edit_seller_user($d){

		/**
		array(3) {
		  ["s_uname"]=>
		  string(9) "吃货星球"
		  ["s_passwd"]=>
		  string(1) "1"
		  ["s_id"]=>
		  string(1) "1"
		}
		**/
	    if(empty($d['s_passwd']))
	    {
	        unset($d['s_passwd']);
	    }else
			$d['s_passwd']=think_ucenter_encrypt($d['s_passwd'],C('SELLER_PWD_KEY'));


		$d['s_true_name'] = $d['s_uname'];

		$r=M('Seller')->where(array('s_id'=>$d['s_id']))->save($d);
		if($r){
		   return true;
		}else{
			 return false;
		}

	}

}
?>
