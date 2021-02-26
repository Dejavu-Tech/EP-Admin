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
namespace Home\Controller;

class RankController extends CommonController {


    protected function _initialize()
    {
        parent::_initialize();
        $this->cur_page = 'rank';
    }

    public function info()
    {
        //subject
        $per_page = 10;
        $page = I('post.page',1);
        $offset = ($page - 1) * $per_page;

        $sql = 'select * from '.C('DB_PREFIX')."subject where type='normal' order by add_time desc limit {$offset},{$per_page}";

        $list = M()->query($sql);
        $result = array();
        if(!empty($list))
        {
            foreach($list as $key =>$subject)
            {
                $subsql = 'select sg.state,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.danprice,g.pin_count,g.image,g.fan_image,g.store_id,g.seller_count,g.virtual_count from  '.C('DB_PREFIX')."subject_goods as sg , ".C('DB_PREFIX')."goods as g
	              where  subject_id = ".$subject['id']." and  sg.state =1 and sg.goods_id = g.goods_id and g.status =1 and g.quantity >0  order by sg.id asc limit 10 ";

                $sub_goods = M()->query($subsql);
                if( empty($sub_goods)) {
                    continue;
                }
                foreach($sub_goods as $k => $v){

					$sub_goods[$k]['seller_count'] += $v['virtual_count'];
                   // $sub_goods[$k]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

					if(!empty($v['fan_image'])){
						$sub_goods[$k]['image']=resize($v['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
					}else {
						$sub_goods[$k]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
					}


                }
                $subject['list'] = $sub_goods;
                $result[$key] = $subject;
                //$list[$key] = $subject;
            }
        }
        $this->list = $result;

        if($page > 1) {
            $result = array('code' => 0);
            if(!empty($list)) {
                $result['code'] = 1;
                $result['html'] = $this->fetch('Rank:rank_ajax_info_fetch');
            }
            echo json_encode($result);
            die();
        }
        return $result;
        /**
        if($subject){
            $sql = 'select sg.state,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.danprice,g.pin_count,g.image,g.store_id,g.seller_count from  '.C('DB_PREFIX')."subject_goods as sg , ".C('DB_PREFIX')."goods as g
	        where  subject_id = ".$subject['id']." and  sg.state =1 and sg.goods_id = g.goods_id and g.status =1 and g.quantity >0  order by sg.id asc limit {$offset},{$per_page}";

            $list = M()->query($sql);

            $this->list = $list;
        }
        **/
    }

	//进行中
	public function index(){

		//type='normal' and lock_type='normal' and
	    $hot_where = " status =1 and quantity >0  ";
		$hot_goods_list = M('goods')->where( $hot_where )->order('seller_count desc ')->limit(10)->select();

		foreach($hot_goods_list as $key => $val)
		{
			$hot_goods_list[$key]['seller_count'] += $val['virtual_count'];
		    //$hot_goods_list[$key]['image']=resize($val['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

			if(!empty($val['fan_image'])){
				$hot_goods_list[$key]['image']=resize($val['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			}else {
				$hot_goods_list[$key]['image']=resize($val['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			}

		}

		$this->hot_goods_list = $hot_goods_list;

		//type='normal' and lock_type='normal' and
		$new_where = " status =1 and quantity >0  ";
		$new_goods_list = M('goods')->where( $new_where )->order('goods_id desc ')->limit(10)->select();

		foreach($new_goods_list as $key => $val)
		{
			$new_goods_list[$key]['seller_count'] += $val['virtual_count'];
		    //$new_goods_list[$key]['image']=resize($val['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));

			if(!empty($val['fan_image'])){
				$new_goods_list[$key]['image']=resize($val['fan_image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			}else {
				$new_goods_list[$key]['image']=resize($val['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
			}
		}
		$this->new_goods_list = $new_goods_list;

		$list = $this->info();

		$this->list = $list;


		$this->display();
	}



}
