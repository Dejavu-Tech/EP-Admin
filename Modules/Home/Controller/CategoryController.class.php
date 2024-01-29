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
namespace Home\Controller;

class CategoryController extends CommonController {
    public function info(){
		$pre_page = 10;
		$page = I('post.page',1);
		$id = I('get.gid',0);
		if(empty($id))
		{
			$id = I('post.gid',0);
		}


		$goods_ids_arr = M('goods_to_category')->where("class_id1 ={$id} or class_id2 ={$id} or class_id3 = {$id}  ")->field('goods_id')->select();

		$ids_arr = array();
		foreach($goods_ids_arr as $val){
			$ids_arr[] = $val['goods_id'];
		}
		$ids_str = implode(',',$ids_arr);

		$condition = array('goods_id' => array('in',$ids_str), 'status'=>1,'quantity' =>array('gt',0) );
		$condition['type'] = 'normal';
		$condition['lock_type'] = 'normal';

		$offset = ($page -1) * $pre_page;
		$list = M('goods')->where($condition)->order('seller_count desc,goods_id asc')->limit($offset,$pre_page)->select();
		$goods_model = D('Home/goods');

		if(!empty($list)) {
			foreach($list as $key => $v){



				if(empty($v['fan_image'])){
					$list[$key]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}

			}
		}
		foreach($list as $key => $val)
		{

		    $val['seller_count'] += $val['virtual_count'];

			$price_arr = $goods_model->get_goods_price($val['goods_id']);

			$val['pinprice'] = $price_arr['price'];


		    $list[$key] = $val;
		}
		$this->list = $list;
		$this->gid = $id;
		if($page > 1) {
			$result = array('code' => 0);
			if(!empty($list)) {
				$result['code'] = 1;
				$result['html'] = $this->fetch('Widget:category_ajax_goods_list_fetch');
			}
			echo json_encode($result);
			die();
		}

		//pid
		$goods_category = M('goods_category')->field('pid')->where(array('id' => $id)  )->find();

		$child_category = array();
		if($goods_category['pid'] == 0)
		{
			$goods_category_list = M('goods_category')->field('id,name,logo')->where( array('pid' => $id) )->limit(8)->select();

			if( !empty($goods_category_list) )
			{
				foreach($goods_category_list as $key => $val)
				{
					$val['logo']=resize($val['logo'], C('common_image_thumb_width'), C('common_image_thumb_height'));
					$goods_category_list[$key] = $val;
				}
				$child_category = $goods_category_list;
			}
		}

		$this->child_category = $child_category;
        $this->display();

    }

    public function index(){


        $pre_page = 10;
        $page = I('post.page',1);
        $sort = I('get.sort','default');

        $condition = array('status'=>1,'quantity' =>array('gt',0) );

        $keyword = '';
        if( !empty($_GET['keyword']) || !empty($_GET['keyword']))
        {
            //condition["FromAddress"] = array(“like”, “%”.$rname);
            //name
            $keyword = htmlspecialchars($_POST['keyword']);
			if(empty($keyword))
			{
			$keyword = htmlspecialchars($_GET['keyword']);
			}


            $condition['name'] = array( 'like',"%".$keyword.'%' );
        }else{
			if(isset($_POST['keyword']))
			{
				$keyword = htmlspecialchars($_POST['keyword']);
				$condition['name'] = array( 'like',"%".$keyword.'%' );
			}
		}

        $this->keyword = $keyword;

        $offset = ($page -1) * $pre_page;

        $orderby = '';
        switch($sort)
        {
            case 'default' :
                        $orderby = 'seller_count desc,goods_id asc';
                        break;
            case 'new':
                        $orderby = 'goods_id desc';
                        break;
            case 'hot':
                        $orderby = 'seller_count desc';
                        break;
        }

        $goods_model = D('Home/goods');
        $list = M('goods')->where($condition)->order('seller_count desc,goods_id asc')->limit($offset,$pre_page)->select();

        if(!empty($list)) {
            foreach($list as $key => $v){
				$list[$key]['seller_count'] = $v['seller_count'] + $v['virtual_count'];


				$price_arr = $goods_model->get_goods_price($v['goods_id']);

				$list[$key]['pinprice'] = $price_arr['price'];

				//
				if(!empty($v['fan_image'])){
					$list[$key]['image']=resize($v['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}else {
					$list[$key]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
				}

            }
        }
        $this->list = $list;

        if($page > 1) {
            $result = array('code' => 0);
            if(!empty($list)) {
                $result['code'] = 1;
                $result['html'] = $this->fetch('Widget:category_ajax_goods_list_fetch');
            }
            echo json_encode($result);
            die();
        }
        $this->display();

    }

}
