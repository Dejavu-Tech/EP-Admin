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
class GoodsModel extends Model{


	/**
		获取商品数量
	**/
	public function get_goods_count($where = '')
	{
		$total = M('eaterplanet_ecommerce_goods')->where(' 1=1 '.$where)->count();
		return $total;
	}

    public function show_bargain_page($search)
    {
        $sql='SELECT p.goods_id,p.name,p.quantity,p.type as goods_type,pg.begin_time,pg.end_time,p.status,pg.id,p.price,p.image,pg.bargain_price,pg.bargain_count FROM '
            .C('DB_PREFIX').'bargain_goods as pg left join '.C('DB_PREFIX').'goods as p on  pg.goods_id=p.goods_id where 1=1  ';

        if(isset($search['customer_id'])){
            $sql.=" and  p.store_id = ".$search['customer_id'];
        }
		//name
		if(isset($search['name'])){
            $sql.=" and  p.name like  '%".$search['name']."%'";
        }

        //'customer_id' => UID
        $count=count(M()->query($sql));

        $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

        $show  = $Page->show();// 分页显示输出

        $sql.=' order by pg.id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

        $list=M()->query($sql);

        foreach ($list as $key => $value) {

            $list[$key]['image']=resize($value['image'], 50, 50);
        }

        return array(
            'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
            'list'=>$list,
            'page'=>$show
        );
    }

    public function show_pingoods_page($search)
    {
        $sql='SELECT p.goods_id,p.name,p.quantity,p.type as goods_type,pg.type,pg.begin_time,pg.end_time,p.status,pg.id,p.price,p.image,pg.pin_price,pg.pin_count FROM '
            .C('DB_PREFIX').'pin_goods as pg left join '.C('DB_PREFIX').'goods as p on  pg.goods_id=p.goods_id where 1=1  ';

        if(isset($search['customer_id'])){
            $sql.=" and  p.store_id = ".$search['customer_id'];
        }
		//name
		if(isset($search['name'])){
            $sql.=" and  p.name like  '%".$search['name']."%'";
        }

        //'customer_id' => UID
        $count=count(M()->query($sql));

        $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

        $show  = $Page->show();// 分页显示输出

        $sql.=' order by pg.id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

        $list=M()->query($sql);

        foreach ($list as $key => $value) {

            $list[$key]['image']=resize($value['image'], 50, 50);
        }

        return array(
            'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
            'list'=>$list,
            'page'=>$show
        );
    }

	function copy_goods($goods_id){
		$query = M()->query("SELECT DISTINCT * FROM " . C('DB_PREFIX') . "goods p LEFT JOIN " . C('DB_PREFIX') . "goods_description pd ON (p.goods_id = pd.goods_id) WHERE p.goods_id =" . (int)$goods_id);

		if ($query) {
			$data = $query[0];

			$data['viewed'] = '0';
			$data['image']='';

			$data['goods_description'] =M('goods_description')->where(array('goods_id'=>$goods_id))->find();

			$data['goods_description']['name']=$data['name'];

			$data['goods_discount'] = M('goods_discount')->where(array('goods_id'=>$goods_id))->select();

			$category = M('goods_to_category')->where(array('goods_id'=>$goods_id))->select();

			foreach ($category as $k => $v) {
				$data['goods_category'][]=$v['category_id'];
			}

			$this->add_Goods($data);
		}
	}


	public function del_Goods($id){
		try{

			$image=M('goods')->where(array('goods_id'=>$id))->field('image')->find();
			if(!empty($image)){
				A('Image')->del_image('goods',$image['image'],'goods');
			}

			$gallery=M('goods_image')->where(array('goods_id'=>$id))->field('image')->select();

			if(!empty($gallery)){
				foreach ($gallery as $key => $value) {
					A('Image')->del_image('gallery',$value['image'],'gallery');
				}
			}


			M('goods')->where(array('goods_id'=>$id))->delete();
			M('goods_description')->where(array('goods_id'=>$id))->delete();
			M('goods_image')->where(array('goods_id'=>$id))->delete();

			M('goods_to_category')->where(array('goods_id'=>$id))->delete();
			M('goods_discount')->where(array('goods_id'=>$id))->delete();
			M('goods_option')->where(array('goods_id'=>$id))->delete();
			M('goods_option_value')->where(array('goods_id'=>$id))->delete();
			M('goods_area')->where(array('goods_id'=>$id))->delete();

			return array(
				'status'=>'success',
				'message'=>'删除成功',
				'jump'=>U('Goods/index')
				);

		}catch(Exception $e){
			return array(
				'status'=>'fail',
				'message'=>'删除失败,未知异常',
				'jump'=>U('Goods/index')
			);
		}
	}

	public function get_goods_data($id){

		$d=M('Goods')->find($id);

		$d['thumb_image']=resize($d['image'], 100, 100);
		if(!empty($d['fan_image']))
		{
			$d['fan_image_thumb']=resize($d['fan_image'], 100, 100);
		}

		return $d;

	}

	public function get_goods_image_data($id){

		$d=M('goods_image')->where(array('goods_id'=>$id))->select();

		foreach ($d as $k => $v) {
			$d[$k]['thumb']=resize($v['image'], 100, 100);
		}

		return $d;

	}
		public function get_goods_category_data($id){

		$sql='SELECT pc.name FROM '.C('DB_PREFIX').'goods_to_category ptc,'
		.C('DB_PREFIX').'goods_category pc WHERE (pc.id=ptc.class_id1 or pc.id=ptc.class_id2 or pc.id=ptc.class_id3) AND ptc.goods_id='.$id.' order by pc.pid asc,sort_order asc';

		$d=M()->query($sql);

		return $d;

	}

	public function show_comment_page($search){


	    $sql='SELECT * FROM '.C('DB_PREFIX').'order_comment where 1= 1 ';


	    if(isset($search['goods_id']))
	    {
	        $sql.=" and goods_id=".$search['goods_id'];
	    }

	    $count=count(M()->query($sql));

	    $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

	    $show  = $Page->show();// 分页显示输出

	    $sql.=' order by state asc,add_time desc LIMIT '.$Page->firstRow.','.$Page->listRows;

	    $list=M()->query($sql);

	    foreach($list as $key => $val)
	    {
	        //member_id
	        $member_info = M('member')->field('name,avatar')->where( array('member_id' => intval($val['member_id'])) )->find();
	        $val['user_name']  = $member_info['name'];
	        $val['avatar']     = $member_info['avatar'];

	        $list[$key] = $val;
	    }

	    return array(
	        'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
	        'list'=>$list,
	        'page'=>$show
	    );

	}

	public function show_guobie_page()
	{
		$sql='SELECT * from  '.C('DB_PREFIX').'guobie ';

		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);
	}

	public function show_goods_page($search){


		$sql='SELECT p.goods_id,p.name,p.model,p.price,p.danprice,p.is_index_show,pd.reason,p.index_sort,p.quantity,p.store_id,p.status,p.model,p.image,p.type,p.lock_type,gtc.class_id1,gtc.class_id2,gtc.class_id3 FROM '.C('DB_PREFIX').'goods_description pd,'
		.C('DB_PREFIX').'goods p,'.C('DB_PREFIX').'goods_to_category gtc WHERE pd.goods_id=p.goods_id and p.goods_id=gtc.goods_id';

		if(isset($search['name'])){
			$sql.=" and p.name like '%".$search['name']."%'";
		}
		if(isset($search['category'])){
			$sql.=" and (gtc.class_id1=".$search['category']." or gtc.class_id2=".$search['category']." or gtc.class_id3=".$search['category'].")";
		}
		if(isset($search['status'])){
			$sql.=" and p.status=".$search['status'];
		}else {
			$sql.=" and p.status!=4 and p.status != 5";
		}
		if(isset($search['store_id']))
		{
		    $sql.=" and p.store_id=".$search['store_id'];
		}

		if(isset($search['type']))
		{
		    if($search['type'] == 'activity')
		    {
		        $sql.=" and p.type != 'normal'";
		    } else {
		        $sql.=" and p.type= '".$search['type']."'";
		    }
		}

		if(isset($search['_string']))
		{
			 $sql.=" and  ".$search['_string'];
		}


		$count=count(M()->query($sql));

		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));

		$show  = $Page->show();// 分页显示输出

		$sql.=' order by p.goods_id desc LIMIT '.$Page->firstRow.','.$Page->listRows;

		$list=M()->query($sql);

		foreach ($list as $key => $value) {
			$list[$key]['image']=resize($value['image'], 50, 50);
			$class_info1 = M('goods_category')->where( array('id' => $value['class_id1']) )->field('name')->find();
			$list[$key]['class_name1'] = $class_info1['name'];

			$class_info2 = M('goods_category')->where( array('id' => $value['class_id2']) )->field('name')->find();
			$list[$key]['class_name2'] = $class_info2['name'];

			$class_info3 = M('goods_category')->where( array('id' => $value['class_id3']) )->field('name')->find();
			$list[$key]['class_name3'] = $class_info3['name'];
		}

		return array(
			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',
			'list'=>$list,
			'page'=>$show
		);

	}

	public function validate($data){

			$error=array();
			if(empty($data['goods_description']['name'])){
				$error='产品名称必填';
			}
			if(!isset($data['class_1']) &&!isset($data['class_2'])&& !isset($data['class_3']) ){
				$error='产品分类必填';
			}else if( empty($data['class_1'])){
			    $error='产品分类必填';
			}
			if($error){
				return array(
					'status'=>'back',
					'message'=>$error
				);
			}
	}

	public function edit_Goods($data){

    	    $error=array();
    	    if(empty($data['goods_description']['name'])){
    	        $error='产品名称必填';
    	    }

    	    if($error){
    	        return array(
    	            'status'=>'back',
    	            'message'=>$error
    	        );
    	    }

			$id=$data['goods_id'];

			$goods['goods_id']=$id;
			$goods['name']=$data['goods_description']['name'];
			$goods['image']=$data['image'];
			$goods['fan_image']=$goods['image'];

			$water_image = '';
			if(!empty($goods['fan_image']))
			{
				$water_image = get_team_water_image($goods['fan_image']);
			}

			$goods['model']=$data['model'];


			$goods['danprice']=$data['danprice'];




			$goods['virtual_count'] = $data['virtual_count'];
			$goods['pick_just'] 	= $data['pick_just'];
			$goods['is_free_in'] 	= $data['is_free_in'];



			if( !empty($data['express']) )
			{
				$need_express = array();

				foreach($data['express'] as  $express)
				{
					$need_express[$express] = array('express_id' => $express,'price' => $data['express_price_'.$express]  );
				}

				$goods['express_list'] = serialize($need_express);
			} else if( empty($data['express'])  && $data['express'] > 0){
				$goods['express_list'] = '';
			}



			if( !empty($data['pick_just']) && $data['pick_just'] >0)
			{
				$goods['pick_up'] = serialize($data['pick_up']);
			} else if( empty($data['pick_up'])  && $data['store_id'] > 0){
				$goods['pick_up'] = '';
			}


			$goods['price']=$data['price'];
			$goods['quantity']=$data['quantity'];
			$goods['transport_id'] = $data['transport_id'];
			$goods['goods_freight'] = $data['goods_freight'];
			$goods['shipping']=$data['shipping'];
			$goods['weight']=$data['weight'];
			$goods['head_disc']=$data['head_disc'];

			$goods['commiss_three_dan_disc'] = $data['commiss_three_dan_disc'];
			$goods['commiss_two_dan_disc'] = $data['commiss_two_dan_disc'];
			$goods['commiss_one_dan_disc'] = $data['commiss_one_dan_disc'];

			$goods['commiss_fen_one_disc'] =  isset($data['commiss_fen_one_disc']) ? $data['commiss_fen_one_disc']:'';
			$goods['commiss_fen_two_disc'] =  isset($data['commiss_fen_two_disc']) ? $data['commiss_fen_two_disc']:'';
			$goods['commiss_fen_three_disc'] = isset($data['commiss_fen_three_disc']) ? $data['commiss_fen_three_disc']:'';
			//commiss_fen_one_disc  commiss_fen_two_disc  commiss_fen_three_disc

			$goods['points'] = $data['points'];


			$goods['sort_order']=$data['sort_order'];
			$goods['date_modified']=date('Y-m-d H:i:s',time());
			$goods['status']=$data['status'];
			if(isset($data['type']))
			{
				$goods['type'] = $data['type'];
			}
			if(isset($data['guobie_id']))
			{
				$goods['guobie_id'] = $data['guobie_id'];
			}

			$r=M('Goods')->save($goods);
			$quantity = $data['quantity'];
			if($r){

				$goods_description['summary']=$data['goods_description']['summary'];
				$goods_description['activity_summary']=$data['goods_description']['activity_summary'];
				$goods_description['description']=$data['goods_description']['description'];

				$goods_description['tag']=$data['goods_description']['tag'];
				$goods_description['share_title']=$data['goods_description']['share_title'];
				$goods_description['share_group_title']=$data['goods_description']['share_group_title'];
				$goods_description['share_descript']=$data['goods_description']['share_descript'];
				$goods_description['per_number']=$data['goods_description']['per_number'];

				$goods_description['is_video']=$data['goods_description']['is_video'];
				$goods_description['video_src']=$data['goods_description']['video_src'];
				$goods_description['video_size_width']=$data['goods_description']['video_size_width'];
				$goods_description['vedio_size_height']=$data['goods_description']['vedio_size_height'];

				//goods_description[is_video]
				//goods_description[video_src]
				//goods_description[video_src]
				//goods_description[video_src]

				$goods_description['water_image'] = $water_image;

				$member_model= D('Admin/Member');
				 $level_list = $member_model->show_member_level();

				 $member_default_levelname_info = M('config')->where( array('name' => 'member_default_levelname') )->find();
				 $member_defualt_discount_info = M('config')->where( array('name' => 'member_defualt_discount') )->find();

				 $default = array('id'=>'default', 'level' => 0,'levelname' => $member_default_levelname_info['value'],'discount' => $member_defualt_discount_info['value']);

				 array_unshift($level_list['list'], $default );
				 $need_level_list = $level_list['list'];

				$need_disc = array();

				foreach($need_level_list as $val)
				{
					$need_disc[$val['level']] = $data['level_'.$val['level']];
				}
				$goods_description['is_untake_level'] = isset($data['isuntake_in_level']) ? intval($data['isuntake_in_level']) : 0;

				$goods_description['level_discount'] = serialize($need_disc);

				M('goods_description')->where(array('goods_id'=>$id))->save($goods_description);




				//citychk
				M('goods_area')->where( array('goods_id' => $id) )->delete();
				M('goods_area')->add( array('goods_id' =>$id,'area_ids_text' => serialize($data['citychk']) ) );

				try{

					//商品分类
					if(isset($data['class_1']) && !empty($data['class_1']) ){

					    //M('goods_to_category')->where(array('goods_id'=>$id))->delete();
						$old_goods_info = M('goods_to_category')->where(array('goods_id'=>$id))->find();
						if( empty($old_goods_info) )
						{
							$this->execute("INSERT INTO " . C('DB_PREFIX') . "goods_to_category SET goods_id = '" . (int)$id . "', class_id1 = '" . (int)$data['class_1'] . "', class_id2 = '" . (int)$data['class_2'] . "', class_id3 = '" . (int)$data['class_3'] . "'");
						}else{
							//class_id1 = '" . (int)$data['class_1'] . "', class_id2 = '" . (int)$data['class_2'] . "', class_id3 = '" . (int)$data['class_3']
							M('goods_to_category')->where(array('goods_id'=>$id))->save( array('class_id1' => $data['class_1'], 'class_id2' => $data['class_2'], 'class_id3' => $data['class_3']) );
						}
					}
					M('GoodsImage')->where(array('goods_id'=>$id))->delete();
					if (isset($data['goods_image'])) {

						foreach ($data['goods_image'] as $goods_image) {
							$this->execute("INSERT INTO " . C('DB_PREFIX') . "goods_image SET goods_id = '" . (int)$id . "', image = '" . $goods_image['image'] . "', sort_order = '" . (int)$goods_image['sort_order'] . "',". " is_video_click = '" . (int)$goods_image['is_video_click'] . "'");
						}
					}

					//商品选项
					//商品选项


					//M('goods_option')->where(array('goods_id'=>$id))->delete();
					//M('goods_option_value')->where(array('goods_id'=>$id))->delete();

					$goods_option_old = M('goods_option')->where(array('goods_id'=>$id))->select();
					$goods_option_old_goodsid_optionid = array();

					foreach($goods_option_old as $old_val)
					{
						$goods_option_old_goodsid_optionid[$old_val['goods_id'].'_'.$old_val['option_id']] = $old_val['goods_id'].'_'.$old_val['option_id'];
					}

					$goods_option_value_old = M('goods_option_value')->where(array('goods_id'=>$id))->select();
					$goods_option_old_goodsid_optionvalueid = array();

					foreach($goods_option_value_old as $old_val)
					{
						$goods_option_old_goodsid_optionvalueid[$old_val['goods_id'].'_'.$old_val['option_value_id']] = $old_val['goods_id'].'_'.$old_val['option_value_id'];
					}

					$old_goods_option_mult_value =  M('goods_option_mult_value')->where(array('goods_id'=>$id))->select();
					$old_goods_option_mult_value_relagoodsoptionvalueid = array();
					foreach($old_goods_option_mult_value as $old_val)
					{
						$old_goods_option_mult_value_relagoodsoptionvalueid[$old_val['rela_goodsoption_valueid']] = $old_val['rela_goodsoption_valueid'];
					}



					$goods_option_value_arr = $data['goods_option_value'];
					$rela_option_value = array();
					if(!empty($goods_option_value_arr))
					{
						foreach($goods_option_value_arr as $val)
						{
							$res = M('option_value')->field('option_id,option_value_id')->where( array('option_value_id' =>$val) )->find();

							if( !isset($rela_option_value[$res['option_id']]) || !in_array())
							{
								$rela_option_value[$res['option_id']][] = $res;
							}
						}
					}

					if (isset($data['goods_option']) && !empty($data['goods_option'])) {


						$data['goods_option'] = explode(',', $data['goods_option']);

						foreach ($data['goods_option'] as $goods_option_id) {
							$goods_option = M('option')->where( array('option_id' =>$goods_option_id) )->find();


							$option['goods_id']=$id;
							$option['option_id']=(int)$goods_option['option_id'];
							$option['required']	=(int)$goods_option['required'];
							$option['option_name']	=$goods_option['name'];
							$option['type']	=$goods_option['type'];

							if( in_array($id.'_'.$goods_option['option_id'], $goods_option_old_goodsid_optionid) )
							{
								$old_goods_option = M('goods_option')->where( array('goods_id' =>$id,'option_id' =>$goods_option['option_id']) )->find();
								unset($goods_option_old_goodsid_optionid[$id.'_'.$goods_option['option_id']]);
								M('goods_option')->where( array('goods_id' =>$id,'option_id' =>$goods_option['option_id']) )->save($option);
								$option_id=$old_goods_option['goods_option_id'];
							} else {
								$option_id=M('goods_option')->add($option);
							}


							//$rela_option_value[$res['option_id']][] = $res;
							$option_value_list = $rela_option_value[$goods_option_id];

							if (isset($option_value_list) && count($option_value_list) > 0 ) {
									foreach ($option_value_list as $goods_option_value) {

										$option_value['goods_option_id']=(int)$option_id;
										$option_value['goods_id']=$id;
										$option_value['option_id']=(int)$goods_option['option_id'];

										$option_value['image']='';

										$option_value['option_value_id']=(int)$goods_option_value['option_value_id'];
										$option_value['quantity']=0;
										$option_value['subtract']='';
										$option_value['price']=0 ;
										$option_value['price_prefix']=0;

										$option_value['weight']=0;
										$option_value['weight_prefix']=0;


										if( in_array($id.'_'.$goods_option_value['option_value_id'], $goods_option_old_goodsid_optionvalueid) )
										{
											unset($goods_option_old_goodsid_optionvalueid[$id.'_'.$goods_option_value['option_value_id']]);

											M('goods_option_value')->where( array('option_value_id' =>$goods_option_value['option_value_id'], 'goods_id' => $id) )->save($option_value);

										} else {
											M('goods_option_value')->add($option_value);
										}

									}
							}



						}



						//M('goods_option')->where(array('goods_id'=>$id))->delete();
						//商品多选项目 $data['mult_option_zuhe']
						$min_dan_price = $data['danprice'];

						//$id=$data['goods_id'];

						//$goods['danprice']=$data['danprice'];
						if(isset($data['mult_option_zuhe']) && !empty($data['mult_option_zuhe']))
						{

							//M('goods_option_mult_value')->where(array('goods_id'=>$id))->delete();


							//rela_goodsoption_valueid

							if(!empty($data['mult_option_zuhe']))
							{
								$mult_option_arr = explode(',', $data['mult_option_zuhe']);
								$option_value_id_arr = array();
								$new_quantity = 0;

								foreach($mult_option_arr as $mult_option)
								{
									$dan_option = explode('@@',$mult_option);


									$mult_id_arr = explode(':',$dan_option[0]);
									$mult_quantity_arr = explode(':',$dan_option[1]);
									$mult_image_arr = explode(':',$dan_option[2]);
									$mult_price_arr = explode(':',$dan_option[3]);
									$mult_weight_arr = explode(':',$dan_option[4]);

									$tmp_option_value_id = explode('_',$mult_id_arr[1]);

									foreach($tmp_option_value_id as $vv)
									{
										if(empty($option_value_id_arr) || !isset($option_value_id_arr[$vv]))
										{
											$option_value_id_arr[$vv] = $mult_quantity_arr[1];
										} else{
											$option_value_id_arr[$vv] += $mult_quantity_arr[1];
										}
									}

									$mul_option_data = array();
									$mul_option_data['rela_goodsoption_valueid'] = $mult_id_arr[1];
									$mul_option_data['goods_id'] = $id;
									$mul_option_data['pin_price'] = 0;
									$mul_option_data['weight'] = $mult_weight_arr[1];

									if($min_dan_price > $mult_price_arr[1])
									{
										$min_dan_price = $mult_price_arr[1];
									}
									$mul_option_data['dan_price'] = $mult_price_arr[1];
									$mul_option_data['quantity'] = $mult_quantity_arr[1];
									$mul_option_data['image'] = $mult_image_arr[1];


									if( in_array($mult_id_arr[1],$old_goods_option_mult_value_relagoodsoptionvalueid ) )
									{
										unset($mul_option_data['pin_price']);
										unset($old_goods_option_mult_value_relagoodsoptionvalueid[$mult_id_arr[1]]);

										M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' =>$mult_id_arr[1] , 'goods_id' => $id) )->save($mul_option_data);
									}else {

										M('goods_option_mult_value')->add($mul_option_data);
									}


									$new_quantity += $mul_option_data['quantity'];
								}

								if($data['danprice'] > $min_dan_price)
								{
									M('goods')->where( array('goods_id' => $id) )->save( array('danprice' => $min_dan_price) );
								}

								//rela_goodsoption_valueid
								//var_dump($old_goods_option_mult_value_relagoodsoptionvalueid);die();


								if($new_quantity != $quantity)
								{
									//更新库存，以规格库存为依据
									M('goods')->where( array('goods_id' => $id) )->save( array('quantity' => $new_quantity) );
								}


								if(!empty($option_value_id_arr))
								{
									foreach($option_value_id_arr as $key => $id_quantity)
									{
										M('goods_option_value')->where( array('goods_id' => $id,'option_value_id' =>$key) )->save( array('quantity' => $id_quantity) );
									}

								}

							}


						}


					}

					if( !empty($goods_option_old_goodsid_optionid ))
					{
						foreach($goods_option_old_goodsid_optionid as $vv)
						{
							$tp_arr = explode('_',$vv);
							M('goods_option')->where( array('goods_id' =>$tp_arr[0],'option_id' =>$tp_arr[1]) )->delete();
						}
					}
					if(!empty($goods_option_old_goodsid_optionvalueid))
					{
						foreach($goods_option_old_goodsid_optionvalueid as $vv)
						{
							$tp_arr = explode('_',$vv);
							M('goods_option_value')->where( array('option_value_id' =>$tp_arr[1], 'goods_id' => $tp_arr[0]) )->delete();
						}
					}




					if( !empty($old_goods_option_mult_value_relagoodsoptionvalueid) )
					{

						foreach($old_goods_option_mult_value_relagoodsoptionvalueid as $vv)
						{
							//$tp_arr = explode('_',$vv);
							$rs = M('goods_option_mult_value')->where( array('rela_goodsoption_valueid' =>$vv , 'goods_id' => $id) )->delete();


						}

					}

					return array(
						'status'=>'success',
						'message'=>'修改成功',
						'jump'=>U('Goods/index')
					);
				}catch(Exception $e){
					return array(
					'status'=>'fail',
					'message'=>'修改失败,未知异常',
					'jump'=>U('Goods/index')
					);
				}

			}else{
				return array(
				'status'=>'fail',
				'message'=>'修改失败',
				'jump'=>U('Goods/index')
				);
			}

	}


	function add_caiji_Goods($data)
	{
		/**
		$goods_data['title'] = $item['title'];
		$goods_data['marketprice'] = $item['marketprice'];
		$goods_data['sales'] = $item['sales'];
		$goods_data['quantity'] = $item['total'];
		$goods_data['html'] = $html;
		**/
		$name = $data['title'];
		$image = '';
		if( !empty($data['image_lists']) )
		{
			$tmp_img = array_slice($data['image_lists'],0,1);
			$image = $tmp_img[0];
		}



		$goods['name']=$name;
		$goods['image']=$image;
		$goods['fan_image']=$image;

		$water_image = '';
		if(!empty($goods['fan_image']))
		{
			$water_image = get_team_water_image($goods['fan_image']);
		}

		$goods['model']='';

		$goods['pinprice']=$data['marketprice'];
		$goods['danprice']=$data['marketprice'];
		$goods['pin_count']=2;
		$goods['pin_hour']=24;
		$goods['head_disc'] = 100;
		$goods['commiss_one_money'] = 0;
		$goods['commiss_one_pin_disc'] = 0;
		$goods['commiss_one_dan_disc'] = 0;
		$goods['virtual_count'] = $data['sales'];

		$goods['pick_just'] 	= 0;
		$goods['is_free_in'] 	= 0;

		$goods['express_list'] = '';

		$goods['pick_up'] = '';

		$goods['sku']='';
		$goods['price']=$data['marketprice'];
		$goods['quantity']=$data['quantity'];
		$goods['transport_id'] = 0;
		$goods['goods_freight'] = 0;

		$goods['shipping']=$data['shipping'];
		$goods['store_id']=$data['store_id'];
		$goods['status']=5;
		$goods['sort_order']=0;
		$goods['date_added']=date('Y-m-d H:i:s',time());
		$goods['date_modified']=date('Y-m-d H:i:s',time());




		$goods_id=M('Goods')->add($goods);

		if($goods_id){

			try{
				$goods_description['goods_id']=$goods_id;

				$goods_description['summary']='';
				$goods_description['description']= htmlspecialchars($data['html']);
				$goods_description['tag']='';
				$goods_description['per_number']=0;
				$goods_description['water_image'] = $water_image;

				M('goods_description')->add($goods_description);

				M('goods_area')->add( array('goods_id' =>$goods_id,'area_ids_text' => serialize(array()) ) );

				//商品分类

				$this->execute("INSERT INTO " . C('DB_PREFIX') . "goods_to_category SET goods_id = '" . (int)$goods_id . "', class_id1 = '0', class_id2 = '0', class_id3 = '0'");

				if (isset($data['image_lists'])) {
					foreach ($data['image_lists'] as $goods_image) {
						$this->execute("INSERT INTO " . C('DB_PREFIX') . "goods_image SET goods_id = '" . (int)$goods_id . "', image = '" . $goods_image . "', sort_order = '0'");
					}
				}

				return true;
			}catch(Exception $e){
				return false;
			}
		}else{
			return false;
		}

	}

	 function add_Goods($data){





			$error=$this->validate($data);

			if($error){

				return $error;
			}



			$goods['name']=$data['goods_description']['name'];
			$goods['image']=$data['image'];
			$goods['fan_image']=$goods['image'];

			$water_image = '';
			if(!empty($goods['fan_image']))
			{
				$water_image = get_team_water_image($goods['fan_image']);
			}

			$goods['model']=$data['model'];


			$goods['danprice']=$data['danprice'];


			$goods['virtual_count'] = $data['virtual_count'];

			$goods['pick_just'] 	= $data['pick_just'];
			$goods['is_free_in'] 	= $data['is_free_in'];


			if( !empty($data['express']) )
			{
				$need_express = array();

				foreach($data['express'] as  $express)
				{
					$need_express[$express] = array('express_id' => $express,'price' => $data['express_price_'.$express]  );
				}

				$goods['express_list'] = serialize($need_express);
			} else if( empty($data['express'])  && $data['express'] > 0){
				$goods['express_list'] = '';
			}

			if( !empty($data['pick_just']) && $data['pick_just'] >0 )
			{
				$goods['pick_up'] = serialize($data['pick_up']);
			} else if( empty($data['pick_up'])  && $data['store_id'] > 0){
				$goods['pick_up'] = '';
			}

			$goods['sku']='';
			$goods['price']=$data['price'];
			$goods['quantity']= $data['quantity'];
			$goods['transport_id'] = $data['transport_id'];
			$goods['goods_freight'] = $data['goods_freight'];
			$goods['weight'] = $data['weight'];
			$goods['head_disc'] = $data['head_disc'];

			$goods['commiss_three_dan_disc'] = $data['commiss_three_dan_disc'];
			$goods['commiss_two_dan_disc'] = $data['commiss_two_dan_disc'];
			$goods['commiss_one_dan_disc'] = $data['commiss_one_dan_disc'];

			$goods['commiss_fen_one_disc'] =  isset($data['commiss_fen_one_disc']) ? $data['commiss_fen_one_disc']:'';
			$goods['commiss_fen_two_disc'] =  isset($data['commiss_fen_two_disc']) ? $data['commiss_fen_two_disc']:'';
			$goods['commiss_fen_three_disc'] = isset($data['commiss_fen_three_disc']) ? $data['commiss_fen_three_disc']:'';

			$goods['points'] = $data['points'];


			$goods['shipping']=$data['shipping'];
			$goods['store_id']=$data['store_id'];
			$goods['status']=$data['status'];
			$goods['sort_order']=$data['sort_order'];
			$goods['date_added']=date('Y-m-d H:i:s',time());
			$goods['date_modified']=date('Y-m-d H:i:s',time());

			if(isset($data['type']))
			{
				$goods['type'] = $data['type'];
			}

			//$goods['points'] = $data['points'];

			$goods_id=M('Goods')->add($goods);

			$quantity = $data['quantity'];

			if($goods_id){

				try{
					$goods_description['goods_id']=$goods_id;

					$goods_description['summary']=$data['goods_description']['summary'];

					$goods_description['activity_summary']=$data['goods_description']['activity_summary'];

					$goods_description['share_group_title']=$data['goods_description']['share_group_title'];
					$goods_description['share_title']=$data['goods_description']['share_title'];
					$goods_description['share_descript']=$data['goods_description']['share_descript'];
					$goods_description['description']=$data['goods_description']['description'];
				    $goods_description['tag']=$data['goods_description']['tag'];
				    $goods_description['per_number']=$data['goods_description']['per_number'];
					$goods_description['water_image'] = $water_image;
					$goods_description['is_video']=$data['goods_description']['is_video'];
					$goods_description['video_src']=$data['goods_description']['video_src'];
					$goods_description['video_size_width']=$data['goods_description']['video_size_width'];
					$goods_description['vedio_size_height']=$data['goods_description']['vedio_size_height'];



					$member_model= D('Admin/Member');
					 $level_list = $member_model->show_member_level();

					 $member_default_levelname_info = M('config')->where( array('name' => 'member_default_levelname') )->find();
					 $member_defualt_discount_info = M('config')->where( array('name' => 'member_defualt_discount') )->find();

					 $default = array('id'=>'default', 'level' => 0,'levelname' => $member_default_levelname_info['value'],'discount' => $member_defualt_discount_info['value']);

					 array_unshift($level_list['list'], $default );
					 $need_level_list = $level_list['list'];

					$need_disc = array();

					foreach($need_level_list as $val)
					{
						$need_disc[$val['level']] = $data['level_'.$val['level']];
					}
					$goods_description['is_untake_level'] = isset($data['isuntake_in_level']) ? intval($data['isuntake_in_level']) : 0;

					$goods_description['level_discount'] = serialize($need_disc);


					M('goods_description')->add($goods_description);

					M('goods_area')->add( array('goods_id' =>$goods_id,'area_ids_text' => serialize($data['citychk']) ) );

					//商品分类

					if(isset($data['class_1']) || isset($data['class_2']) || isset($data['class_3'])){
					    $this->execute("INSERT INTO " . C('DB_PREFIX') . "goods_to_category SET goods_id = '" . (int)$goods_id . "', class_id1 = '" . (int)$data['class_1'] . "', class_id2 = '" . (int)$data['class_2'] . "', class_id3 = '" . (int)$data['class_3'] . "'");
					}
					if (isset($data['goods_image'])) {

						foreach ($data['goods_image'] as $goods_image) {
							$this->execute("INSERT INTO " . C('DB_PREFIX') . "goods_image SET goods_id = '" . (int)$goods_id . "', image = '" . $goods_image['image'] . "', sort_order = '" . (int)$goods_image['sort_order'] . "',". " is_video_click = '" . (int)$goods_image['is_video_click'] . "'");

						}
					}

					//商品选项
					if (isset($data['goods_option']) && !empty($data['goods_option'])) {


						$goods_option_value_arr = $data['goods_option_value'];
						$rela_option_value = array();
						if(!empty($goods_option_value_arr))
						{
							foreach($goods_option_value_arr as $val)
							{
								$res = M('option_value')->field('option_id,option_value_id')->where( array('option_value_id' =>$val) )->find();

								if( !isset($rela_option_value[$res['option_id']]) || !in_array())
								{
									$rela_option_value[$res['option_id']][] = $res;
								}
							}
						}


						$data['goods_option'] = explode(',', $data['goods_option']);

						foreach ($data['goods_option'] as $goods_option_id) {


							$goods_option = M('option')->where( array('option_id' =>$goods_option_id) )->find();

							$option['goods_id']=$goods_id;
							$option['option_id']=(int)$goods_option['option_id'];
							$option['required']	=(int)$goods_option['required'];
							$option['option_name']	=$goods_option['name'];
							$option['type']	=$goods_option['type'];

							$option_id=M('goods_option')->add($option);

							$option_value_list = $rela_option_value[$goods_option_id];

							if (isset($option_value_list) && count($option_value_list) > 0 ) {
									foreach ($option_value_list as $goods_option_value) {

										$option_value['goods_option_id']=(int)$option_id;
										$option_value['goods_id']=$goods_id;
										$option_value['option_id']=(int)$goods_option['option_id'];

										$option_value['image']='';

										$option_value['option_value_id']=(int)$goods_option_value['option_value_id'];
										$option_value['quantity']=0;
										$option_value['subtract']='';
										$option_value['price']=0 ;
										$option_value['price_prefix']=0;

										$option_value['weight']=0;
										$option_value['weight_prefix']=0;
										M('goods_option_value')->add($option_value);
									}
							}
						}
					}

					$new_quantity = 0;
					$min_dan_price = $data['danprice'];



					if(isset($data['mult_option_zuhe']) && !empty($data['mult_option_zuhe']))
					{

						if(!empty($data['mult_option_zuhe']))
						{
							$mult_option_arr = explode(',', $data['mult_option_zuhe']);
							$option_value_id_arr = array();

							foreach($mult_option_arr as $mult_option)
							{
								$dan_option = explode('@@',$mult_option);
								$mult_id_arr = explode(':',$dan_option[0]);
								$mult_quantity_arr = explode(':',$dan_option[1]);
								$mult_image_arr = explode(':',$dan_option[2]);

								$mult_price_arr = explode(':',$dan_option[3]);
								$mult_weight_arr = explode(':',$dan_option[4]);

								$tmp_option_value_id = explode('_',$mult_id_arr[1]);

								foreach($tmp_option_value_id as $vv)
								{
									if(empty($option_value_id_arr) || !isset($option_value_id_arr[$vv]))
									{
										$option_value_id_arr[$vv] = $mult_quantity_arr[1];
									} else{
										$option_value_id_arr[$vv] += $mult_quantity_arr[1];
									}
								}

								$mul_option_data = array();
								$mul_option_data['rela_goodsoption_valueid'] = $mult_id_arr[1];
								$mul_option_data['goods_id'] = $goods_id;

								if($min_dan_price > $mult_price_arr[1] )
								{
									$min_dan_price = $mult_price_arr[1];
								}

								$mul_option_data['dan_price'] = $mult_price_arr[1];
								$mul_option_data['quantity'] = $mult_quantity_arr[1];
								$mul_option_data['weight'] = $mult_weight_arr[1];


								$mul_option_data['image'] = $mult_image_arr[1];

								M('goods_option_mult_value')->add($mul_option_data);
								$new_quantity += $mul_option_data['quantity'];
							}

							if($data['danprice'] > $min_dan_price)
							{
								M('goods')->where( array('goods_id' => $goods_id) )->save( array('danprice' => $min_dan_price) );
							}

							if(!empty($option_value_id_arr))
							{
								foreach($option_value_id_arr as $key => $id_quantity)
								{
									M('goods_option_value')->where( array('goods_id' => $goods_id,'option_value_id' =>$key) )->save( array('quantity' => $id_quantity) );
								}

							}

							if($new_quantity != $quantity)
							{
								//更新库存，以规格库存为依据
								M('goods')->where( array('goods_id' => $goods_id) )->save( array('quantity' => $new_quantity) );
							}
						}


					}
					return array(
						'status'=>'success',
						'message'=>'新增成功',
						'jump'=>U('Goods/index')
					);
				}catch(Exception $e){
					return array(
					'status'=>'fail',
					'message'=>'新增失败',
					'jump'=>U('Goods/index')
					);
				}
			}else{
				return array(
				'status'=>'fail',
				'message'=>'新增失败',
				'jump'=>U('Goods/index')
				);
			}


	}

	 function get_goods_options($goods_id) {
		$goods_option_data = array();

		$goods_option_query = M()->query("SELECT * FROM " . C('DB_PREFIX') . "goods_option po LEFT JOIN "
		. C('DB_PREFIX') . "option o ON po.option_id = o.option_id WHERE po.goods_id =".(int)$goods_id);

		foreach ($goods_option_query as $goods_option) {
			$goods_option_value_data = array();

			$goods_option_value_query = M()->query("SELECT * FROM " . C('DB_PREFIX')
			. "goods_option_value WHERE goods_option_id = '"
			. (int)$goods_option['goods_option_id'] . "'");

			foreach ($goods_option_value_query as $goods_option_value) {
				$goods_option_value_data[] = array(
					'goods_option_value_id' => $goods_option_value['goods_option_value_id'],
					'option_value_id'         => $goods_option_value['option_value_id'],
					'quantity'                => $goods_option_value['quantity'],
					'subtract'                => $goods_option_value['subtract'],
					'price'                   => $goods_option_value['price'],
					'price_prefix'            => $goods_option_value['price_prefix'],
					'image'				=> $goods_option_value['image'],
					'weight'                  => $goods_option_value['weight'],
					'weight_prefix'           => $goods_option_value['weight_prefix']
				);
			}

			$goods_option_data[] = array(
				'goods_option_id'    => $goods_option['goods_option_id'],
				'option_id'            => $goods_option['option_id'],
				'name'                 => $goods_option['name'],
				'type'                 => $goods_option['type'],
				'option_value'         => $goods_option['name'],
				'required'             => $goods_option['required'],
				'goods_option_value'   =>  $goods_option_value_data,
			);
		}

		return $goods_option_data;
	}





}
?>
