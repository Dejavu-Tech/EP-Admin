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
namespace Seller\Controller;

class GroupController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='拼团管理';
		$this->breadcrumb2='商品信息';
		$this->sellerid = SELLERUID;
	}

	public function goods()
	{
		$pindex = I('get.page', 1);
		$psize = 20;



		$starttime_arr = I('get.time');



		$starttime = isset($starttime_arr['start']) ? strtotime($starttime_arr['start']) : strtotime(date('Y-m-d'.' 00:00:00'));

		$endtime = isset($starttime_arr['end']) ? strtotime($starttime_arr['end']) : strtotime(date('Y-m-d'.' 23:59:59'));




		$this->starttime = $starttime;
		$this->endtime = $endtime;

		$searchtime = I('get.searchtime','');

		$this->searchtime = $searchtime;
		$shop_data = array();

		$type =  I('get.type','all');
		$goods_type =  I('get.goods_type','0');

		//---begin

		$count_common_where ="";
		if (defined('ROLE') && ROLE == 'agenter' ) {

			$supper_info = get_agent_logininfo();

			$supper_goods_list = M('eaterplanet_ecommerce_good_common')->field('goods_id')->where( array('supply_id' =>$supper_info['id'] ) )->select();

			$gids_list = array();

			foreach($supper_goods_list as $vv)
			{
				$gids_list[] = $vv['goods_id'];
			}

			if( !empty($gids_list) )
			{
				$count_common_where = " and  id in ( ".implode(',', $gids_list )." )";
			}else{
				$count_common_where = " and id in (0)";
			}
		}


		$all_count =  D('Seller/Goods')->get_goods_count(" and type = 'pin' {$count_common_where}");//全部商品数量

		$onsale_count = D('Seller/Goods')->get_goods_count(" and grounding = 1 and type = 'pin' {$count_common_where}");//出售中商品数量
		$getdown_count = D('Seller/Goods')->get_goods_count(" and grounding = 0 and type = 'pin' {$count_common_where}");//已下架商品数量
		$warehouse_count = D('Seller/Goods')->get_goods_count(" and grounding = 2 and type = 'pin' {$count_common_where}");//仓库商品数量
		$recycle_count = D('Seller/Goods')->get_goods_count(" and grounding = 3 and type = 'pin' {$count_common_where}");//回收站商品数量
		$waishen_count = D('Seller/Goods')->get_goods_count(" and grounding = 4 and type = 'pin' {$count_common_where}");//审核商品数量
		$unsuccshen_count = D('Seller/Goods')->get_goods_count(" and grounding = 5 and type = 'pin' {$count_common_where}");//拒绝审核商品数量

		$this->assign('waishen_count',$waishen_count);
		$this->assign('unsuccshen_count',$unsuccshen_count);

		//recycle 仓库

		//--end
		//recycle 仓库 get_config_by_name($name)

		$goods_stock_notice = D('Home/Front')->get_config_by_name('goods_stock_notice');
		$goods_stock_notice = intval($goods_stock_notice);
		if( empty($goods_stock_notice) )
		{
			$goods_stock_notice = 0;
		}


		$stock_notice_count = D('Admin/Goods')->get_goods_count(" and grounding = 1 and total<= {$goods_stock_notice} and type = 'pin' {$count_common_where}  ");//库存预警数量
		//goods_stock_notice


		//grounding 1

		//type all  全部

		//saleon 1 出售中
		//getdown 0 已下架
		//warehouse 2 仓库中
		//recycle 3 回收站


		$psize = 20;

		$condition = ' WHERE  g.type = "pin" ';

		$sqlcondition = "";

		if( !empty($type) && $type != 'all')
		{
			switch($type)
			{
				case 'saleon':
					$condition .= " and g.grounding = 1";
				break;
				case 'getdown':
					$condition .= " and g.grounding = 0";
				break;
				case 'warehouse':
					$condition .= " and g.grounding = 2";
				break;
				case 'wait_shen':
					$condition .= " and g.grounding = 4";
				break;
				case 'refuse':
					$condition .= " and g.grounding = 5";
				break;
				case 'recycle':
					$condition .= " and g.grounding = 3";
				break;
				case 'stock_notice':
					$condition .= " and g.grounding = 1 and g.total<= {$goods_stock_notice} ";
					break;
			}

		}else{
			$condition .= " and g.grounding != 3 ";
		}
		//拼团返利是否开启
		$is_open_pintuan_rebate = D('Home/Front')->get_config_by_name('is_open_pintuan_rebate');
		if(!empty($goods_type) || empty($is_open_pintuan_rebate)){
		    $sqlcondition .= ' left join ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_good_pin as gp on gp.goods_id = g.id ';
		    if(!empty($goods_type)){
		        if($goods_type == 1){
		            $condition .= ' and gp.is_pintuan_rebate=0 ';
		        }else if($goods_type == 2){
		            $condition .= ' and gp.is_pintuan_rebate=1   ';
		        }
		    }
		    if(empty($is_open_pintuan_rebate)){
		        $condition .= ' and gp.is_pintuan_rebate = 0 ';
		    }
		}

		$keyword = I('get.keyword','','addslashes');
		$keyword2 = stripslashes($keyword);
		$this->keyword = $keyword2;

		if (!(empty($keyword))) {
			$condition .= " AND (g.`id` = '{$keyword}' or g.`goodsname` LIKE '%{$keyword}%' or g.`codes` LIKE '%{$keyword}%' ) ";
		}

		if (defined('ROLE') && ROLE == 'agenter' )
		{

			$supper_info = get_agent_logininfo();

			$sqlcondition .= ' , ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_good_common as gm  ';
			$condition .= ' and gm.goods_id =g.id  AND gm.supply_id ='.$supper_info['id'].'  ';

		}



		if( !empty($searchtime) )
		{
		    switch( $searchtime )
		    {
		        case 'create':
		            $condition .= ' AND (gm.begin_time >='.$starttime.' and gm.end_time < '.$endtime.' )';

					if (!defined('ROLE') && ROLE != 'agenter' )
					{
						$sqlcondition .= ' left join ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_good_common as gm on gm.goods_id = g.id ';
					}

		            break;
		    }
		}


		$cate = I('get.cate', '');
		$this->cate = $cate;
		if( !empty($cate) )
		{
			$cate_list = M('eaterplanet_ecommerce_goods_to_category')->field('goods_id')->where(array('cate_id' => $cate))->select();

			$catids_arr = array();

			foreach($cate_list as $val)
			{
				$catids_arr[] = $val['goods_id'];
			}

			if( !empty($catids_arr) )
			{
				$catids_str = implode(',', $catids_arr);
				$condition .= ' and g.id in ('.$catids_str.')';
			}else{
				$condition .= " and 1=0 ";
			}
		}


		$sql = 'SELECT COUNT(g.id) as count FROM ' .C('DB_PREFIX'). 'eaterplanet_ecommerce_goods g ' .$sqlcondition.  $condition ;


		$total_arr = M()->query($sql);

		$total = $total_arr[0]['count'];




		$pager = pagination2($total, $pindex, $psize);

		if (!(empty($total))) {

			$sql = 'SELECT g.* FROM ' .C('DB_PREFIX'). 'eaterplanet_ecommerce_goods g '  .$sqlcondition . $condition . '
					ORDER BY  g.istop DESC, g.settoptime DESC, g.`id` DESC  ';

			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;

			//echo $sql;
			$list = M()->query($sql);


			foreach ($list as $key => &$value ) {

				$price_arr = D('Home/Pingoods')->get_goods_price($value['id'],-888);

				$value['price_arr'] = $price_arr;

				$thumb = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $value['id']) )->order('id asc')->find();


				if( empty($thumb['thumb']) )
				{
				    $value['thumb'] =  $thumb['image'];
				}else{
				    $value['thumb'] =  $thumb['thumb'];
				}

				//is_take_fullreduction
				$gd_common = M('eaterplanet_ecommerce_good_common')->field('is_take_fullreduction,supply_id')->where( array('goods_id' => $value['id']) )->find();

				$value['is_take_fullreduction'] =  $gd_common['is_take_fullreduction'];

				$value['supply_name'] = '';

				if( empty($gd_common['supply_id']) || $gd_common['supply_id'] ==0 )
				{
					$value['supply_id'] = 0;
				}else{
					$value['supply_id'] = $gd_common['supply_id'];

					$sub_info = M('eaterplanet_ecommerce_supply')->field('name')->where( array('id' => $gd_common['supply_id'] ) )->find();

					$value['supply_name'] = $sub_info['name'];

				}


				$categorys = M('eaterplanet_ecommerce_goods_to_category')->where( array('goods_id' => $value['id']) )->order('id asc')->select();

				$value['cate'] = $categorys;

			 	$time_info = D('home/front')->get_goods_common_field($value['id'] , 'begin_time,end_time');
			 	$value['begin_time'] = $time_info['begin_time'];
			 	$value['end_time'] = $time_info['end_time'];


				//团长数量
				$head_count = 0;

				if( $value['is_all_sale'] == 1 )
				{
					$head_count = M('eaterplanet_community_head')->count();

				}else{

					$head_count = M('eaterplanet_community_head_goods')->where( array('goods_id' => $value['id'] ) )->count();

				}

				$value['head_count'] = $head_count;

				$rebate_common = M('eaterplanet_ecommerce_good_pin')->field('is_pintuan_rebate')->where( array('goods_id' => $value['id']) )->find();
				$value['is_pintuan_rebate'] = $rebate_common['is_pintuan_rebate'];

			}

		}

		$categorys = D('Seller/GoodsCategory')->getFullCategory(true,false,'pintuan');
		$category = array();

		foreach ($categorys as $cate ) {
			$category[$cate['id']] = $cate;
		}

		$this->category =$category;

		$this->type = $type;
		$this->goods_type = $goods_type;
		$this->all_count = $all_count;
		$this->onsale_count = $onsale_count;
		$this->getdown_count = $getdown_count;
		$this->warehouse_count = $warehouse_count;
		$this->recycle_count = $recycle_count;
		$this->stock_notice_count = $stock_notice_count;


		$this->assign('list',$list);// 赋值数据集
		$this->assign('pager',$pager);// 赋值分页输出
		$is_open_fullreduction = 0;
		$this->assign('is_open_fullreduction',$is_open_fullreduction);


		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');

		if( empty($index_sort_method) || $index_sort_method == 0 )
		{
			$index_sort_method = 0;
		}
		$this->index_sort_method = $index_sort_method;

		//---
		$supply_add_goods_shenhe = D('Home/Front')->get_config_by_name('supply_add_goods_shenhe');
		$supply_edit_goods_shenhe = D('Home/Front')->get_config_by_name('supply_edit_goods_shenhe');


			$supply_add_goods_shenhe = 0;

			$supply_edit_goods_shenhe = 0;


		$is_open_shenhe = 0;



		$this->supply_add_goods_shenhe = $supply_add_goods_shenhe;
		$this->supply_edit_goods_shenhe = $supply_edit_goods_shenhe;

		$this->assign('is_open_shenhe',$is_open_shenhe);
		//--

		//团长分组
		$group_default_list = array(
			array('id' => 'default', 'groupname' => '默认分组')
		);

		$this->group_list = array();


		$config_data = D('Seller/Config')->get_all_config();

		$pintuan_model_buy = isset($config_data['pintuan_model_buy']) ? intval( $config_data['pintuan_model_buy'] ) : 0;

		$this->pintuan_model_buy = $pintuan_model_buy;

		//团长分组
		$group_default_list = array(
			array('id' => 'default', 'groupname' => '默认分组')
		);

		$group_list = M('eaterplanet_community_head_group')->field('id,groupname')->order('id asc')->select();

		$group_list = array_merge($group_default_list, $group_list);

		$this->group_list = $group_list;

		$is_index = true;
		$is_top = true;
		$is_updown  = true;
		$is_fullreduce  = true;
		$is_vir_count = true;
		$is_newbuy = true;
		$is_goodsspike = true;

		$this->config_data = $config_data;

		$this->is_index = $is_index;
		$this->is_top = $is_top;
		$this->is_updown  = $is_updown;
		$this->is_fullreduce  = $is_fullreduce;
		$this->is_vir_count = $is_vir_count;
		$this->is_newbuy = $is_newbuy;
		$this->is_goodsspike = $is_goodsspike;

		$this->display();

	}


	public function editgoods()
	{
		$id =  I('get.id');

		if (IS_POST) {
			$_GPC = I('post.');

			if( !isset($_GPC['thumbs']) || empty($_GPC['thumbs']) )
			{
				show_json(0,  array('message' => '商品图片必须上传' ,'url' => $_SERVER['HTTP_REFERER']) );
				die();
			}

			D('Seller/Goods')->modify_goods('pin');

			$http_refer = S('HTTP_REFERER');

			$http_refer = empty($http_refer) ? $_SERVER['HTTP_REFERER'] : $http_refer;

			show_json(1, array('message'=>'修改商品成功！','url' => $http_refer ));
		}
		//sss
		S('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
		$this->id = $id;
		$item = D('Seller/Goods')->get_edit_goods_info($id,1);


		//-------------------------以上是获取资料

		$limit_goods = array();



		$this->limit_goods = $limit_goods;

		$category = D('Seller/GoodsCategory')->getFullCategory(true, true,'pintuan');

		$this->category = $category;


		$spec_list = D('Seller/Spec')->get_all_spec('pintuan');

		$this->spec_list = $spec_list;

		$dispatch_data = M('eaterplanet_ecommerce_shipping')->where( array('enabled' => 1, 'isdefault' => 1) )->order('sort_order desc')->select();

		$this->dispatch_data = $dispatch_data;

		$set = D('Seller/Config')->get_all_config();

		$this->set = $set;

		$commission_level = array();

		$config_data = $set;

		$this->config_data = $config_data;

		$default = array('id' => 'default', 'levelname' => empty($config_data['commission_levelname']) ? '默认等级' : $config_data['commission_levelname'], 'commission1' => $config_data['commission1'], 'commission2' => $config_data['commission2'], 'commission3' => $config_data['commission3']);
		//$others = pdo_fetchall('SELECT * FROM ' . tablename('eaterplanet_ecommerce_commission_level') . ' WHERE uniacid = \'' . $_W['uniacid'] . '\' ORDER BY commission1 asc');


		//$commission_level = array_merge(array($default), $others);

		$commission_level = array();

		//$level['key']
		foreach($commission_level as $key => $val)
		{
			$val['key'] = $val['id'];
			$commission_level[$key] = $val;
		}
		$shopset_level = empty($set['commiss_level']) ? 0: $set['commiss_level'];
		$this->shopset_level = $shopset_level;

		$open_buy_send_score = empty($set['open_buy_send_score']) ? 0: $set['open_buy_send_score'];

		$this->open_buy_send_score = $open_buy_send_score;

		$delivery_type_express = $config_data['delivery_type_express'];

		if( empty($delivery_type_express) )
		{
			$delivery_type_express = 2;
		}

		$this->delivery_type_express = $delivery_type_express;

		$is_open_fullreduction =  $config_data['is_open_fullreduction'];

		$this->is_open_fullreduction = $is_open_fullreduction;


		$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();


		$head_commission_levelname = $config_data['head_commission_levelname'];
		$default_comunity_money =  $config_data['default_comunity_money'];

		$list_default = array(
			array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);

		$community_head_level = array_merge($list_default, $community_head_level);


		$community_head_commission_info = D('Seller/Communityhead')->get_goods_head_level_bili( $id );


		$mb_level = M('eaterplanet_ecommerce_member_level')->count();

		$this->mb_level = $mb_level;


		if( !empty($community_head_commission_info) )
		{
			foreach( $community_head_commission_info as $kk => $vv)
			{
				$item[$kk] = $vv;
			}
		}

		$this->community_head_commission_info = $community_head_commission_info;
		$this->item = $item;
		$this->community_head_level = $community_head_level;
		//end

		$community_money_type =  $config_data['community_money_type'];

		$this->community_money_type = $community_money_type;

		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');

		if( empty($index_sort_method) || $index_sort_method == 0 )
		{
			$index_sort_method = 0;
		}
		$this->index_sort_method = $index_sort_method;


		$is_open_only_express =  $config_data['is_open_only_express'];

		$this->is_open_only_express = $is_open_only_express;

		$is_open_goods_relative_goods = $config_data['is_open_goods_relative_goods'];

		$this->is_open_goods_relative_goods = $is_open_goods_relative_goods;

		//商户权限begin

		$is_index = true;
		$is_top = true;
		$is_updown  = true;
		$is_fullreduce  = true;
		$is_vir_count = true;
		$is_newbuy = true;
		$is_goodsspike = true;

		//商户权限end
		$this->is_index = $is_index;
		$this->is_top = $is_top;
		$this->is_updown  = $is_updown;
		$this->is_fullreduce  = $is_fullreduce;
		$this->is_vir_count = $is_vir_count;
		$this->is_newbuy = $is_newbuy;
		$this->is_goodsspike = $is_goodsspike;

		$pintuan_model_buy = isset($config_data['pintuan_model_buy']) ? intval( $config_data['pintuan_model_buy'] ) : 0;
		//商户权限begin community_head_level
		$this->pintuan_model_buy  = $pintuan_model_buy;

		// $is_head_takegoods
		$is_head_takegoods = isset($config_data['is_head_takegoods']) && $config_data['is_head_takegoods'] == 1 ? 1 : 0;

		$this->is_head_takegoods = $is_head_takegoods;

		// $is_open_pintuan_rebate 拼团返利是否开启
		$is_open_pintuan_rebate = isset($config_data['is_open_pintuan_rebate']) && $config_data['is_open_pintuan_rebate'] == 1 ? 1 : 0;
		$this->is_open_pintuan_rebate = $is_open_pintuan_rebate;

		include $this->display('Group/addgoods');
	}




	public function addgoods()
	{
		if (IS_POST) {
			$_GPC = I('request.');

			if( !isset($_GPC['thumbs']) || empty($_GPC['thumbs']) )
			{
				show_json(0, array('message' => '商品图片必须上传' ,'url' => $_SERVER['HTTP_REFERER']) );
				die();
			}

			D('Seller/Goods')->addgoods('pin');

			$http_refer = S('HTTP_REFERER');

			$http_refer = empty($http_refer) ? $_SERVER['HTTP_REFERER'] : $http_refer;

			show_json(1, array('message' => '添加商品成功！','url' => $http_refer ));
		}
		S('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
		$category = D('Seller/GoodsCategory')->getFullCategory(true, true,'pintuan');
		$this->category = $category;

		$spec_list = D('Seller/Spec')->get_all_spec('pintuan');
		$this->spec_list = $spec_list;




		$dispatch_data = M('eaterplanet_ecommerce_shipping')->where( array('enabled' => 1,'isdefault' =>1) )->order('sort_order desc')->select();
		$this->dispatch_data = $dispatch_data;

		$set =  D('Seller/Config')->get_all_config();
        //202011fix
        $this->set = $set;
		$commission_level = array();

		$config_data = $set;
		$this->config_data = $config_data;

		$default = array('id' => 'default', 'levelname' => empty($config_data['commission_levelname']) ? '默认等级' : $config_data['commission_levelname'], 'commission1' => $config_data['commission1'], 'commission2' => $config_data['commission2'], 'commission3' => $config_data['commission3']);

		$others = M('eaterplanet_ecommerce_commission_level')->order('commission1 asc')->select();


		$commission_level = array_merge(array($default), $others);

		$communityhead_commission = $config_data['default_comunity_money'];
		$this->communityhead_commission = $communityhead_commission;




		//$level['key']
		foreach($commission_level as $key => $val)
		{
			$val['key'] = $val['id'];
			$commission_level[$key] = $val;
		}
		$this->commission_level = $commission_level;
		$shopset_level = empty($set['commiss_level']) ? 0: $set['commiss_level'];
		$this->shopset_level = $shopset_level;


		$open_buy_send_score = empty($set['open_buy_send_score']) ? 0: $set['open_buy_send_score'];
		$this->open_buy_send_score = $open_buy_send_score;

		$item = array();
		$item['begin_time'] = time();
		$item['community_head_commission'] = $communityhead_commission;

		$item['end_time'] = time() + 86400;

		$item['pin_count'] = 2;


		$delivery_type_express =  $config_data['delivery_type_express'];

		if( empty($delivery_type_express) )
		{
			$delivery_type_express = 2;
		}

		$this->delivery_type_express = $delivery_type_express;

		$is_open_fullreduction = $config_data['is_open_fullreduction'];

		$this->is_open_fullreduction = $is_open_fullreduction;


		$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();

		$head_commission_levelname = $config_data['head_commission_levelname'];
		$default_comunity_money = $config_data['default_comunity_money'];

		$list_default = array(
			array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )
		);

		$community_head_level = array_merge($list_default, $community_head_level);

		if( !empty($community_head_level) )
		{
			foreach( $community_head_level as $head_level)
			{
				$item['head_level'.$head_level['id']] = $head_level['commission'];
			}
		}

		$this->item = $item;
		$this->community_head_level = $community_head_level;

		$community_money_type =  $config_data['community_money_type'];

		$this->community_money_type = $community_money_type;


		$mb_level = M('eaterplanet_ecommerce_member_level')->count();

		$this->mb_level = $mb_level;

		$is_open_only_express = $config_data['is_open_only_express'];

		$this->is_open_only_express = $is_open_only_express;

		$is_open_goods_relative_goods = $config_data['is_open_goods_relative_goods'];

		$this->is_open_goods_relative_goods = $is_open_goods_relative_goods;

		//商户权限begin

		$is_index = true;
		$is_top = true;
		$is_updown  = true;
		$is_fullreduce  = true;
		$is_vir_count = true;
		$is_newbuy = true;
		$is_goodsspike = true;


		//商户权限end
		$this->is_index = $is_index;
		$this->is_top = $is_top;
		$this->is_updown  = $is_updown;
		$this->is_fullreduce  = $is_fullreduce;
		$this->is_vir_count = $is_vir_count;
		$this->is_newbuy = $is_newbuy;
		$this->is_goodsspike = $is_goodsspike;

		$pintuan_model_buy = isset($config_data['pintuan_model_buy']) ? intval( $config_data['pintuan_model_buy'] ) : 0;

		$this->pintuan_model_buy = $pintuan_model_buy;

		// $is_head_takegoods
		$is_head_takegoods = isset($config_data['is_head_takegoods']) && $config_data['is_head_takegoods'] == 1 ? 1 : 0;

		$this->is_head_takegoods = $is_head_takegoods;

		// $is_open_pintuan_rebate 拼团返利是否开启
		$is_open_pintuan_rebate = isset($config_data['is_open_pintuan_rebate']) && $config_data['is_open_pintuan_rebate'] == 1 ? 1 : 0;
		$this->is_open_pintuan_rebate = $is_open_pintuan_rebate;

		$this->display();

	}

	public function config()
	{

		if (IS_POST) {

			$data = I('post.parameter', array());

			$data['pintuan_stranger_zero'] = isset($data['pintuan_stranger_zero']) ? $data['pintuan_stranger_zero'] : 0;


			D('Seller/Config')->update($data);

			show_json(1, array('url'=> U('group/config')));
		}

		$data = D('Seller/Config')->get_all_config();
		$this->data = $data;

		$this->display();
	}

	public function slider ()
	{
		$_GPC = I('request.');

        $condition = ' and type="pintuan" ';
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and advname like "%'.$_GPC['keyword'].'%"';
        }

        if (isset($_GPC['enabled']) && $_GPC['enabled'] >= 0) {
            $_GPC['enabled'] = trim($_GPC['enabled']);
            $condition .= ' and enabled = ' . $_GPC['enabled'];
        } else {
            $_GPC['enabled'] = -1;
        }

        $list = M()->query('SELECT id,advname,thumb,link,type,displayorder,enabled FROM ' . C('DB_PREFIX') . "eaterplanet_ecommerce_adv
			WHERE 1  " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);


        $total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_adv WHERE 1  ' . $condition );

		$total = $total_arr[0]['count'];

        $pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;
		$this->_GPC = $_GPC;

        include $this->display();
	}

	public function pintuan()
	{
		$_GPC = I('request.');

		$state =   $_GPC['state'];
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

		$where = "";

		if( isset($state) && $state >=0 )
		{
			if($state == 0) {
				$where = " and p.state=0 and p.end_time > ".time();
		    } else if($state == 1) {
				$where = " and p.state=1 ";
		    } else if($state == 2) {
				$where = " and (p.state=2 or (p.state = 0 and p.end_time <".time()." ) )";
		    }


		}

		$sql = "select p.pin_id,p.is_jiqi,og.goods_id,og.name,p.state,p.need_count,p.end_time,p.begin_time from ".
				C('DB_PREFIX')."eaterplanet_ecommerce_pin as p,".C('DB_PREFIX')."eaterplanet_ecommerce_pin_order as o,".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
	           where p.order_id= o.order_id {$where} and p.order_id = og.order_id ".' order by p.pin_id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize;

	    $list = M()->query( $sql );

		$sql_count = "select count(1) as count from ".
				C('DB_PREFIX')."eaterplanet_ecommerce_pin as p,".C('DB_PREFIX')."eaterplanet_ecommerce_pin_order as o,".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
	           where p.order_id= o.order_id  {$where} and p.order_id = og.order_id ";

		$total_arr = M()->query( $sql_count );

		$total = $total_arr[0]['count'];

		foreach($list as $key => $val)
		{

			$sql = "select count(o.order_id) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_pin_order as po,".C('DB_PREFIX')."eaterplanet_ecommerce_order as o
	           where po.order_id= o.order_id and po.pin_id = ".$val['pin_id']." and o.order_status_id in(1,2,4,6,7,8,9,10,11,14) ";

			$count_arr = M()->query($sql);

			$pin_buy_count = $count_arr[0]['count'];

			$val['real_state'] = $val['state'];

		    if($val['state'] == 0 && $val['end_time'] <time()) {
		        $val['state'] = 2;
		    }
		    $val['buy_count'] = $pin_buy_count;
		    $list[$key] = $val;
		}

		$pager = pagination2($total, $pindex, $psize);

		$this->pager = $pager;
		$this->list  = $list;
		$this->_GPC  = $_GPC;


        $this->display();
	}

	public function pintuan_detail()
	{
		$_GPC = I('request.');

	    $pin_id = $_GPC['pin_id'];

		$pin_info = M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id ) )->find();

	    if($pin_info['state'] == 0 && $pin_info['end_time'] <time()) {
	        $pin_info['state'] = 2;
	    }

		if( empty($pin_info['qrcode']) )
		{
			$weqrcode = D('Home/Pingoods')->_get_commmon_wxqrcode('eaterplanet_ecommerce/moduleA/pin/share', $pin_info['order_id'] );

			M('eaterplanet_ecommerce_pin')->where( array('pin_id' => $pin_id ) )->save( array('qrcode' => $weqrcode ) );

			$qrcode = tomedia( $weqrcode );
		}else{
			$qrcode = tomedia( $pin_info['qrcode'] );
		}

		 $jiapinorder = array();

		if($pin_info['is_jiqi'] == 1)
		{
			$jiapinorder = M('eaterplanet_ecommerce_jiapinorder')->where( array('pin_id' => $pin_id) )->order('id asc')->select();

		}
		$this->pin_info = $pin_info;
		$this->pin_id = $pin_id;

		$this->jiapinorder = $jiapinorder;


	    $sql = "select o.order_num_alias,o.total,o.order_id,o.payment_code,o.name,o.telephone,o.shipping_name,o.shipping_tel,o.shipping_city_id,
	 	         o.shipping_country_id,o.shipping_province_id,o.shipping_address,o.date_added,o.order_status_id,
	        og.goods_id,og.name as goods_name,og.goods_images,og.name as goods_name,og.quantity,og.price,og.total as atotal,o.shipping_fare
	 	         from ".C('DB_PREFIX')."eaterplanet_ecommerce_order as o,".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og,".C('DB_PREFIX')."eaterplanet_ecommerce_pin_order as p
		 	         where o.order_status_id !=3 and  o.order_id = og.order_id and o.order_id = p.order_id and p.pin_id ={$pin_id} ";
	    $sql.=' ORDER BY o.order_id desc ';

	    $list = M()->query($sql);

	    foreach($list as $key => $val)
	    {
			/**
	        $province_info =  M('area')->where( array('area_id' =>$val['shipping_province_id'] ) )->find();
	        $city_info =  M('area')->where( array('area_id' =>$val['shipping_city_id'] ) )->find();
	        $country_info =  M('area')->where( array('area_id' =>$val['shipping_country_id'] ) )->find();

	        $val['province_name'] = $province_info['area_name'];
	        $val['city_name'] = $city_info['area_name'];
	        $val['area_name'] = $country_info['area_name'];
			**/
			$val['reward_content'] = "";
			if($pin_info['is_pintuan_rebate'] == 1){
				if($pin_info['rebate_reward'] == 1){//积分
					$integral_flow = M('eaterplanet_ecommerce_member_integral_flow')->where( array('order_id' => $val['order_id'],'type'=>'pintuan_rebate') )->find();
					if(!empty($integral_flow)){
						$val['reward_content'] = "积分：".$integral_flow['score'];
					}
				}else if($pin_info['rebate_reward'] == 2){//余额
					$charge_flow = M('eaterplanet_ecommerce_member_charge_flow')->where( array('trans_id' => $val['order_id'],'state'=>21) )->find();
					if(!empty($charge_flow)){
						$val['reward_content'] = "余额：".$charge_flow['money'];
					}
				}
			}

	        $list[$key] = $val;
	    }

		$this->list = $list;

	    $pin_buy_sql = "select count(o.order_id) as count from ".C('DB_PREFIX')."eaterplanet_ecommerce_pin_order as p,".C('DB_PREFIX')."eaterplanet_ecommerce_order as o,".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
			where p.order_id= o.order_id and p.order_id = og.order_id and p.pin_id = {$pin_id}  and o.order_status_id in(1,2,4,6,7,8,9,10,11,14)
	    ";

		$pin_buy_count_arr = M()->query($pin_buy_sql );

		$pin_buy_count = $pin_buy_count_arr[0]['count'];

		$this->pin_buy_count = $pin_buy_count;

		$pin_jia_count = 0;

	    $order = current($list);

		$this->order = $order;

	    $goods_images = tomedia($order['goods_images']);

		$this->goods_images = $goods_images;

		$order_status_list = M('eaterplanet_ecommerce_order_status')->select();

	    $order_status_arr = array();
	    foreach($order_status_list as $val)
	    {
	        $order_status_arr[$val['order_status_id']] = $val['name'];
	    }

		$this->order_status_arr = $order_status_arr;

	    include $this->display();

	}

	public function addslider()
    {

        $id = I('request.id');
        if (!empty($id)) {
			$item = M('eaterplanet_ecommerce_adv')->where( array('id' => $id) )->find();
			$this->item = $item;
        }

        if (IS_POST) {
            $data = I('request.data');

            D('Seller/Adv')->update($data ,'pintuan');
            show_json(1, array('url' => U('group.slider') ));
        }

        $this->display();
    }

	public function changeslider()
    {

        $id = I('request.id');

        //ids
        if (empty($id)) {
			$ids = I('request.ids');

            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = I('request.type');
        $value = I('request.value');

        if (!(in_array($type, array('enabled', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }

		$items = M('eaterplanet_ecommerce_adv')->where( array('id' => array('in', $id)) )->select();

        foreach ($items as $item) {

			M('eaterplanet_ecommerce_adv')->where( array('id' => $item['id']) )->save(  array($type => $value) );
        }

        show_json(1 , array('url' => $_SERVER['HTTP_REFERER']));

    }


    public function deleteslider()
    {
        $id = I('request.id');

        //ids
        if (empty($id)) {
            $ids = I('request.ids');

            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }


		$items = M('eaterplanet_ecommerce_adv')->where( array('id' => array('in', $id)) )->select();

        foreach ($items as $item) {
			M('eaterplanet_ecommerce_adv')->where( array('id' => $item['id']) )->delete();
        }

         show_json(1 , array('url' => $_SERVER['HTTP_REFERER']));
    }

	public function goodstag()
	{
		$_GPC = I('request.');

		$this->gpc = $_GPC;

		$condition = ' 1 and tag_type="pintuan" ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if ($_GPC['enabled'] != '') {
			$condition .= ' and state=' . intval($_GPC['enabled']);
		}

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and tagname like "%'.$_GPC['keyword'].'%" ';
		}

		$label = M('eaterplanet_ecommerce_goods_tags')->where( $condition )->order(' id asc ')->limit( (($pindex - 1) * $psize) . ',' . $psize )->select();

		$total = M('eaterplanet_ecommerce_goods_tags')->where( $condition )->count();

		$pager = pagination2($total, $pindex, $psize);

		$this->label = $label;
		$this->pager = $pager;


		$this->display();
	}

	public function addtags()
	{
		$_GPC = I('request.');

		if (IS_POST) {

			$data = $_GPC['data'];

			D('Seller/Tags')->update($data,'pintuan');

			show_json(1, array('url' => U('group/goodstag')));
		}

		$this->display();
	}

	public function edittags()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);
		if (!empty($id)) {

			$item = M('eaterplanet_ecommerce_goods_tags')->field('id,tagname,tagcontent,state,sort_order,type')->where( array('id' =>$id ) )->find();

			if (json_decode($item['tagcontent'], true)) {
				$labelname = json_decode($item['tagcontent'], true);
			}
			else {
				$labelname = unserialize($item['tagcontent']);
			}
			$this->item = $item;
			$this->labelname = $labelname;
		}

		if (IS_POST) {

			$data = $_GPC['data'];

			D('Seller/Tags')->update($data,'pintuan');

			show_json(1, array('url' => U('group/goodstag') ));
		}

		$this->display('Group/addtags');
	}

	public function deletetags()
	{

		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = $_GPC['ids'];
		}

		if( is_array($id) )
		{
			$items = M('eaterplanet_ecommerce_goods_tags')->field('id,tagname')->where( array('id' => array('in', $id)) )->select();

		}else{
			$items = M('eaterplanet_ecommerce_goods_tags')->field('id,tagname')->where( array('id' =>$id ) )->select();

		}
		//$items = M('eaterplanet_ecommerce_goods_tags')->field('id,tagname')->where( array('id' => array('in', $id )) )->select();

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('eaterplanet_ecommerce_goods_tags')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => U('group/goodstag')));

	}

	public function tagsstate()
	{

		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = $_GPC['ids'];
		}


		if( is_array($id) )
		{
			$items = M('eaterplanet_ecommerce_goods_tags')->field('id,tagname')->where( array('id' => array('in', $id)) )->select();

		}else{
			$items = M('eaterplanet_ecommerce_goods_tags')->field('id,tagname')->where( array('id' =>$id ) )->select();

		}

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('eaterplanet_ecommerce_goods_tags')->where( array('id' => $item['id']) )->save( array('state' => intval($_GPC['value'])) );
		}

		show_json(1, array('url' => U('group/goodstag')));

	}

	public function labelquery()
	{
		$_GPC = I('request.');

		$kwd = trim($_GPC['keyword']);
		$type = isset($_GPC['type']) ? $_GPC['type'] : 'pintuan';


		$condition = ' and state = 1 and tag_type="'.$type.'" ';

		if (!empty($kwd)) {
			$condition .= ' AND tagname LIKE "%'.$kwd.'%" ';
		}

		$labels = M('eaterplanet_ecommerce_goods_tags')->field('id,tagname,tagcontent')->where(  '1 '. $condition )->order('id desc')->select();

		if (empty($labels)) {
			$labels = array();
		}
		$html = '';

		foreach ($labels as $key => $value) {
			if (json_decode($value['tagcontent'], true)) {
				$labels[$key]['tagcontent'] = json_decode($value['tagcontent'], true);
			}
			else {
				$labels[$key]['tagcontent'] = unserialize($value['tagcontent']);
			}


			$html  .= '<nav class="btn btn-primary btn-sm choose_dan_link" data-id="'.$value['id'].'" data-json=\''.json_encode(array("id"=>$value["id"],"tagname"=>$value["tagname"])).'\'>';
			$html  .=	$value['tagname'];
			$html  .= '</nav>';
		}


		if( isset($_GPC['is_ajax']) )
		{
			echo json_encode( array('code' => 0, 'html' => $html) );
			die();
		}

		$this->labels = $labels;

		$this->display();

	}


	public function editspec()
	{
		$id =  I('request.id');
		if (!empty($id)) {

          $item = M('eaterplanet_ecommerce_spec')->where( array('id' => $id) )->find();

			if (json_decode($item['value'], true)) {
				$labelname = json_decode($item['value'], true);
			}
			else {
				$labelname = unserialize($item['value']);
			}
		}

		if (IS_POST) {

			$data = I('post.data');

			D('Seller/Spec')->update($data,'pintuan');

			show_json(1, array('url' => U('group/goodsspec')));
		}

		$this->item = $item;

		$this->labelname = $labelname;

		$this->display('Group/addspec');
	}

	public function addspec()
	{
		global $_W;
		global $_GPC;

		if (IS_POST) {

			$data = I('post.data');

			D('Seller/Spec')->update($data,'pintuan');

			show_json(1, array('url' => U('group/goodsspec')));
		}

		 $this->display();
	}

	public function deletespec()
	{
		$id = I('get.id');

		if (empty($id)) {
			$ids = I('post.ids');
			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}

		$items = M('eaterplanet_ecommerce_spec')->field('id,name')->where( array('id' => array('in', $id)) )->select();

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('eaterplanet_ecommerce_spec')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function goodsspec()
	{

		$condition = ' 1=1 and spec_type="pintuan" ';
	    $pindex =  I('get.page',1);
	    $psize = 20;

		$enabled = I('get.enabled');

	    if ($enabled != '') {
	        $condition .= ' and state=' . intval($enabled);
	    }

		$keyword = I('get.keyword','','trim');

	    if (!empty($keyword)) {
	        $condition .= ' and name like "%'.$keyword.'%" ';
	    }

		$offset = ($pindex - 1) * $psize;


		$label = M('eaterplanet_ecommerce_spec')->field('id,name,value')->where($condition)->order(' id desc ')->limit($offset, $psize)->select();

		$total = M('eaterplanet_ecommerce_spec')->where( $condition )->count();

		$cur_url = U('goods/goodsspec', array('enabled' => $enabled,'keyword' => $keyword));
	    $pager = pagination2($total, $pindex, $psize);
	    foreach( $label as &$val )
		{
			$val['value'] = unserialize($val['value']);
			$val['value_str'] = !empty($val['value']) ? implode(',', $val['value']) : '';
		}

		$this->keyword = $keyword;
		$this->label = $label;
		$this->total = $total;
		$this->pager = $pager;



	    include $this->display();
	}


	public function category_enabled()
	{
		$id = I('request.id');

		if (empty($id)) {
			$ids = I('request.ids');
			$id = (is_array($ids) ? implode(',', $ids) : 0);
		}


		$items = M('eaterplanet_ecommerce_goods_category')->field('id,name')->where( 'id in( ' . $id . ' )' )->select();

		$enabled = I('request.enabled');

		foreach ($items as $item) {

			M('eaterplanet_ecommerce_goods_category')->where( array('id' => $item['id']) )->save(  array('is_show' => intval($enabled)) );

		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function goodscategory()
	{
		if (IS_POST) {
			$datas = I('post.datas');
			if (!empty($datas)) {
				D('Seller/GoodsCategory')->goodscategory_modify($datas);
				show_json(1 , array('url' => U('group/goodscategory') ));
			}
		}

		$children = array();


		$category = M('eaterplanet_ecommerce_goods_category')->where(' cate_type="pintuan" ')->order('pid ASC, sort_order DESC')->select();


		foreach ($category as $index => $row) {
			if (!empty($row['pid'])) {
				$children[$row['pid']][] = $row;
				unset($category[$index]);
			}
		}

		$this->children = $children;
		$this->category = $category;


		include $this->display();
	}
	public function addcategory()
	{
		$data = array();
		$pid = I('get.pid', 0);
		$id = I('get.id', 0);

		if (IS_POST) {

			$data = I('post.data');
			if(empty($data['name'])){
				show_json(0, array('message' => '分类名称必须填写'));
			}
			if(!isset($data['sort_order'])){
				show_json(0, array('message' => '分类排序必须填写'));
			}
			if(!is_numeric($data['sort_order'])){
				show_json(0, array('message' => '分类排序必须在1-999之间的整数'));
			}
            if(floor($data['sort_order']) != $data['sort_order']){
                show_json(0, array('message' => '分类排序必须在1-999之间的整数'));
            }
            if($data['sort_order'] < 1 || $data['sort_order'] > 999){
                show_json(0, array('message' => '分类排序必须在1-999之间的整数'));
            }

			$where = array();
			$where['sort_order'] = $data['sort_order'];
			$where['cate_type'] = 'pintuan';
			$cate_info = M('eaterplanet_ecommerce_goods_category')->where($where)->find();

			if(!empty($cate_info) && (($id == 0) || ($id > 0  && $id != $cate_info['id'])) ){
				show_json(0, array('message' => '分类排序已存在，请重新填写'));
			}

			D('Seller/GoodsCategory')->update($data,'pintuan');

			show_json(1, array('url' => U('group/goodscategory')));
		}

		if($id >0 )
		{
			$data = M('eaterplanet_ecommerce_goods_category')->where( array('id' => $id) )->find();

			$this->data = $data;
		}

		$this->pid = $pid;
		$this->id = $id;

		$this->display();
	}
	public function category_delete()
	{
		$id = I('get.id');

		$item = M('eaterplanet_ecommerce_goods_category')->field('id, name, pid')->where( array('id' => $id) )->find();


		M('eaterplanet_ecommerce_goods_category')->where( "id={$id} or pid={$id}" )->delete();


		show_json(1, array('url' => U('group/goodscategory')));

	}

	public function goodsvircomment()
	{
		$_GPC = I('request.');

		$condition = ' and type = 1 and gd_type="pintuan" ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;



		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and content like "%'.$_GPC['keyword'].'%"';

		}

		$label = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_order_comment
					WHERE 1 " . $condition . ' order by comment_id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);


		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_order_comment WHERE 1 ' . $condition);

		$total = $total_arr[0]['count'];

		$pager = pagination2($total, $pindex, $psize);

		$this->pager = $pager;
		$this->label = $label;

		include $this->display();
	}

	public function addvircomment()
	{
		$_GPC = I('request.');

		if (IS_POST) {
			$data = $_GPC['data'];
			$jia_id = $_GPC['jiaid'];
			$goods_id = $_GPC['goods_id'];

			if( empty($goods_id) )
			{
				show_json(0, array('message' => '请选择评价商品!'));
			}

			if( empty($jia_id) )
			{
				show_json(0, array('message' => '请选择虚拟客户!'));
			}

			$goods_info = M('eaterplanet_ecommerce_goods')->field('goodsname')->where( array('id' => $goods_id) )->find();

			$goods_image = isset($_GPC['goods_image']) && !empty($_GPC['goods_image']) ? $_GPC['goods_image'] : array();
			$time = empty($_GPC['time']) ? time() : $_GPC['time'];

			$jia_info = M('eaterplanet_ecommerce_jiauser')->where( array('id' => $jia_id ) )->find();

			$commen_data = array();
			$commen_data['order_id'] = 0;
			$commen_data['state'] = 1;
			$commen_data['type'] = 1;
			$commen_data['gd_type'] = "pintuan";
			$commen_data['member_id'] = $jia_id;
			$commen_data['avatar'] = $jia_info['avatar'];
			$commen_data['user_name'] = $jia_info['username'];
			$commen_data['order_num_alias'] = 1;
			$commen_data['star'] = $data['star'];
			$commen_data['star3'] = $data['star3'];
			$commen_data['star2'] = $data['star2'];
			$commen_data['is_picture'] = !empty($goods_image) ? 1: 0;
			$commen_data['content'] = $data['content'];
			$commen_data['images'] = serialize(implode(',', $goods_image));


			$image  = D('Home/Pingoods')->get_goods_images($goods_id);

			$seller_id = 1;


			if(!empty($image))
			{
				$commen_data['goods_image'] = $image['image'];
			}else{
				$commen_data['goods_image'] = '';
			}

			$commen_data['goods_id'] = $goods_id;
			$commen_data['goods_name'] = $goods_info['goodsname'];
			$commen_data['add_time'] = strtotime($time);


			M('eaterplanet_ecommerce_order_comment')->add($commen_data);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));

		}




		$this->display();
	}

	public function orderlist()
	{
		$time = I('request.time');

		$starttime = isset($time['start']) ? strtotime($time['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($time['end']) ? strtotime($time['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


		$this->searchfield = I('request.searchfield','');
		$this->keyword = I('request.keyword','');
		$this->searchtime = I('request.searchtime','');
		$this->delivery = I('request.delivery','');
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->time = $time;

		$order_status_id = I('request.order_status_id',0);

		$order_status_arr = D('Seller/Order')->get_order_status_name();

		$this->order_status_arr = $order_status_arr;

		$need_data = D('Seller/Order')->load_order_list(0,0,1);

		$cur_controller = 'group/orderlist';

		$total = $need_data['total'];
		$total_money = $need_data['total_money'];
		$list = $need_data['list'];
		$pager = $need_data['pager'];
		$all_count = $need_data['all_count'];
		$count_status_1 = $need_data['count_status_1'];
		$count_status_3 = $need_data['count_status_3'];
		$count_status_4 = $need_data['count_status_4'];
		$count_status_5 = $need_data['count_status_5'];
		$count_status_7 = $need_data['count_status_7'];
		$count_status_11 = $need_data['count_status_11'];
		$count_status_14 = $need_data['count_status_14'];


		$this->cur_controller = $cur_controller;
		$this->total = $total;
		$this->total_money = $total_money;
		$this->list = $list;
		$this->pager = $pager;
		$this->all_count = $all_count;
		$this->count_status_1 = $count_status_1;
		$this->count_status_3 = $count_status_3;
		$this->count_status_4 = $count_status_4;
		$this->count_status_5 = $count_status_5;
		$this->count_status_7 = $count_status_7;
		$this->count_status_11 = $count_status_11;
		$this->count_status_14 = $count_status_14;

		$this->order_status_id = $order_status_id;
		$this->is_community = I('request.is_community', 0);
		$this->headid = I('request.headid', 0);

		$open_feier_print = D('Home/Front')->get_config_by_name('open_feier_print');

		if( empty($open_feier_print) )
		{
			$open_feier_print = 0;
		}

		$this->open_feier_print = $open_feier_print;

		$s_id = 1 ;

		if(SELLERUID != 1)
		{
			$seller_info = M('seller')->field('s_role_id')->where( array('s_id' => SELLERUID ) )->find();

			$perms_arr = M('eaterplanet_ecommerce_perm_role')->where( array('id' => $seller_info['s_role_id']) )->find();

			$perms1 = str_replace('.','/',$perms_arr['perms2']);

			$perms2 = explode(",", $perms1);

			if(in_array("user/user/index", $perms2)){
				$s_id = 1 ;
			} else {
				$s_id = 0 ;
			}

		}
		$this->s_id = $s_id;

		$is_can_look_headinfo = true;
		$is_can_nowrfund_order = true;


		$this->is_can_look_headinfo = $is_can_look_headinfo;
		$this->is_can_nowrfund_order = $is_can_nowrfund_order;




		include $this->display();
	}

	public function ordersendall()
	{
		$_GPC = I('request.');

		$express_list = D('Seller/Express')->load_all_express();



		if( IS_POST )
		{

			$type = isset($_GPC['type']) && !empty($_GPC['type']) ? $_GPC['type']:'normal';


			$fext = substr($_FILES['excelfile']['name'], strrpos($_FILES['excelfile']['name'], '.') + 1);




			$express = trim($_GPC['express']);
			$expresscom = trim($_GPC['expresscom']);


			if( $fext == 'csv' )
			{
				$file_name = $_FILES['excelfile']['tmp_name'];
				$file = fopen($file_name,'r');

				$rows = array();
				$i =0;
				while ($data = fgetcsv($file)) {

					$rows[] = eval('return '.iconv('gbk','utf-8',var_export($data,true)).';');

				}

				//var_dump( $rows );
				//die();
			}else{

				$rows = D('Seller/Excel')->import('excelfile');
			}

			$num = count($rows);
			$time = time();

			$express_arr = array();

			foreach($express_list as $val)
			{
				$express_arr[ $val['id'] ] = $val['name'];
			}

			$i = 0;
			$err_array = array();

			$quene_order_list = array();

			$cache_key = md5(time().count($rows));

			$j =0;
			foreach ($rows as $rownum => $col)
			{
				$order_id = trim($col[0]);

				if (empty($order_id)) {
					$err_array[] = $order_id;
					continue;
				}
				if($j == 0)
				{
					$j++;
					continue;
				}

				$quene_order_list[]  = array('order_num_alias' => $order_id , 'shipping_no' => $col[1], 'express' => $express,'expresscom' => $expresscom );

			}

			S('_orderquene_'.$cache_key, $quene_order_list);

			$this->cache_key = $cache_key;
			$this->type = $type;

			$this->display('Order/oploadexcelorder');
			die();
		}




		$this->express_list = $express_list;
		$this->type = I('request.type');
		$this->display();

	}

	public function orderaftersales()
	{

		//$_GPC['order_status_id'] = 12;


		$gpc = I('request.');
		$this->gpc = $gpc;
		$this->_GPC = $gpc;
		$time = I('request.time');

		$starttime = isset($time['start']) ? strtotime($time['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($time['end']) ? strtotime($time['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


		$this->searchfield = I('request.searchfield','');
		$this->keyword = I('request.keyword','');
		$this->searchtime = I('request.searchtime','');
		$this->delivery = I('request.delivery','');
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->time = $time;


		$order_status_arr =  D('Seller/Order')->get_order_status_name();
		$this->order_status_arr = $order_status_arr;

		$need_data = D('Seller/Order')->load_afterorder_list(1);//改造原来的加载方法

		$this->need_data = $need_data;

		$cur_controller = 'order/order';
		$total = $need_data['total'];
		$total_money = $need_data['total_money'];
		$list = $need_data['list'];
		$pager = $need_data['pager'];
		$all_count = $need_data['all_count'];
		$count_status_1 = $need_data['count_status_1'];
		$count_status_3 = $need_data['count_status_3'];
		$count_status_4 = $need_data['count_status_4'];
		$count_status_5 = $need_data['count_status_5'];
		$count_status_7 = $need_data['count_status_7'];
		$count_status_11 = $need_data['count_status_11'];
		$count_status_14 = $need_data['count_status_14'];

		$this->cur_controller = $cur_controller;
		$this->total = $total;
		$this->total_money = $total_money;
		$this->list = $list;
		$this->pager = $pager;
		$this->all_count = $all_count;
		$this->count_status_1 = $count_status_1;
		$this->count_status_3 = $count_status_3;
		$this->count_status_4 = $count_status_4;
		$this->count_status_5 = $count_status_5;
		$this->count_status_7 = $count_status_7;
		$this->count_status_11 = $count_status_11;
		$this->count_status_14 = $count_status_14;


		$open_feier_print =  D('Home/Front')->get_config_by_name('open_feier_print');

		if( empty($open_feier_print) )
		{
			$open_feier_print = 0;
		}

		$this->open_feier_print = $open_feier_print;

		$s_id = 1 ;

		if(SELLERUID != 1)
		{
			$seller_info = M('seller')->field('s_role_id')->where( array('s_id' => SELLERUID ) )->find();

			$perms_arr = M('eaterplanet_ecommerce_perm_role')->where( array('id' => $seller_info['s_role_id']) )->find();

			$perms1 = str_replace('.','/',$perms_arr['perms2']);

			$perms2 = explode(",", $perms1);

			if(in_array("user/user/index", $perms2)){
				$s_id = 1 ;
			} else {
				$s_id = 0 ;
			}

		}
		$this->s_id = $s_id;

		//退款状态：0申请中，1商家拒绝，2平台介入，3退款成功，4退款失败,5:撤销申请
		$order_refund_state = array(0=>'申请中',1=>'商家拒绝', 2=>'平台介入',3=>'退款成功',4=>'退款失败',5=>'撤销申请');

		$this->order_refund_state = $order_refund_state;

		$is_can_look_headinfo = true;
		$is_can_nowrfund_order = true;

		$supply_can_look_headinfo = D('Home/Front')->get_config_by_name('supply_can_look_headinfo');

		$supply_can_nowrfund_order = D('Home/Front')->get_config_by_name('supply_can_nowrfund_order');

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			if( isset($supply_can_look_headinfo) && $supply_can_look_headinfo == 2 )
			{
				$is_can_look_headinfo = false;
			}
			if( isset($supply_can_nowrfund_order) && $supply_can_nowrfund_order == 2 )
			{
				$is_can_nowrfund_order = false;
			}
		}

		$this->is_can_look_headinfo = $is_can_look_headinfo;
		$this->is_can_nowrfund_order = $is_can_nowrfund_order;

		$this->display();


	}

	public function pincommiss()
	{
		$_GPC = I('request.');


		$condition = '  ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$_GPC['keyword'] = I('get.keyword','','addslashes');
		if (!empty($_GPC['keyword'])) {
			$condition .= " and (  m.username like '%".$_GPC['keyword']."%' ) ";
			$_GPC['keyword'] = stripslashes($_GPC['keyword']);
		}


		$sql = 'SELECT m.*,pc.money as pcmoney,pc.dongmoney,pc.getmoney FROM ' . C('DB_PREFIX') . "eaterplanet_ecommerce_pintuan_commiss as pc left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on pc.member_id=m.member_id
						WHERE 1 " . $condition . ' order by pc.id asc  ';

		$sql_count = 'SELECT count(1) as count FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_pintuan_commiss as pc left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on pc.member_id=m.member_id
						WHERE 1 " . $condition . ' order by pc.id asc  ';

		$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;


		$list = M()->query($sql);


		$total_arr = M()->query($sql_count);

		$total = $total_arr[0]['count'];

		foreach( $list as $key => $val )
		{
			//普通等级

			//ims_ eaterplanet_ecommerce_order 1 2 4 6 11


			$list[$key] = $val;
		}


		$pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;
		$this->_GPC = $_GPC;

        $this->display();
	}

	public function pincommiss_list()
	{
		$_GPC = I('request.');

		$member_id = $_GPC['id'];
		$this->member_id = $member_id;

		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$where = " and co.member_id = {$member_id} ";


		$starttime = strtotime( date('Y-m-d')." 00:00:00" );
		$endtime = $starttime + 86400;


		if( isset($_GPC['searchtime']) && $_GPC['searchtime'] == 'create_time' )
		{
			if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
				$starttime = strtotime($_GPC['time']['start']);
				$endtime = strtotime($_GPC['time']['end']);

				$where .= ' AND co.addtime >= '.$starttime.' AND co.addtime <= '.$endtime;
			}
		}

		$this->starttime = $starttime;
		$this->endtime = $endtime;

		$order_status = isset($_GPC['order_status']) ? $_GPC['order_status'] : -1;

		if($order_status == 1)
		{
			$where .= " and co.state = 1 ";
		} else if($order_status == 2){
			$where .= " and co.state = 2 ";
		} else if($order_status == 0){
			$where .= " and co.state = 0 ";
		}

		$sql = "select co.order_id,co.state,co.money,co.addtime ,og.total,og.name,og.total
				from ".C('DB_PREFIX')."eaterplanet_ecommerce_pintuan_commiss_order as co ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
	                    where   co.order_goods_id = og.order_goods_id {$where}
	                      order by co.id desc ".' limit ' . (($pindex - 1) * $psize) . ',' . $psize;

		$list = M()->query($sql);

		if( !empty($list) )
		{
			foreach($list as $key => $val)
			{
				$val['total'] = sprintf("%.2f",$val['total']);
				$val['money'] = sprintf("%.2f",$val['money']);

				$val['addtime'] = date('Y-m-d H:i:s',$val['addtime']);

				$order_info = M('eaterplanet_ecommerce_order')->field('order_num_alias')->where( array('order_id' => $val['order_id'] ) )->find();

				$val['order_num_alias'] = $order_info['order_num_alias'];
				$list[$key] = $val;
			}
		}

		$sql_count = "select count(1) as count
				from ".C('DB_PREFIX')."eaterplanet_ecommerce_pintuan_commiss_order as co ,
                ".C('DB_PREFIX')."eaterplanet_ecommerce_order_goods as og
	                    where co.order_goods_id = og.order_goods_id {$where}  ";

		$total_arr = M()->query($sql_count);

		$total = $total_arr[0]['count'];


		$pager = pagination($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;
		$this->_GPC = $_GPC;

        $this->display();
	}


	public function withdrawallist()
	{
	    $_GPC = I('request.');


		$condition = '  ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and (  id = '.intval($_GPC['keyword']).') ';
		}

		$starttime = strtotime( date('Y-m-d')." 00:00:00" );
		$endtime = $starttime + 86400;

		if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);

			$condition .= ' AND addtime >= '.$starttime.' AND addtime <= '.$endtime.' ';
		}


		$this->starttime = $starttime;
		$this->endtime = $endtime;


		if ($_GPC['comsiss_state'] != '') {
			$condition .= ' and state=' . intval($_GPC['comsiss_state']);
		}

		$sql = 'SELECT * FROM ' . C('DB_PREFIX') . "eaterplanet_ecommerce_pintuan_tixian_order
						WHERE 1 " . $condition . ' order by id desc  ';

		if (empty($_GPC['export'])) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}

		$community_tixian_fee = D('Home/Front')->get_config_by_name('pintuan_tixian_fee', $_W['uniacid']);

		$list = M()->query($sql);
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_pintuan_tixian_order WHERE 1 ' . $condition );

		$total = $total_arr[0]['count'];


		foreach( $list as $key => $val )
		{
			//普通等级
			$member_info = M('eaterplanet_ecommerce_member')->field('username,avatar,we_openid,telephone')->where( array('member_id' => $val['member_id'] ) )->find();

			$val['member_info'] = $member_info;


			$list[$key] = $val;
		}
		if ($_GPC['export'] == '1') {

			foreach($list as $key =>&$row)
			{
				$row['username'] = $row['member_info']['username'];


				$row['telephone'] = $row['member_info']['telephone'];

				$row['bankname'] = $row['bankname'];

				if( $row['type'] == 1 )
				{
					$row['bankname'] = '余额';
				}elseif( $row['type'] == 2 ){
					$row['bankname'] =  '微信零钱';
				}elseif($row['type'] == 3){
					$row['bankname'] =  '支付宝';
				}


				$row['bankaccount'] = "\t".$row['bankaccount'];
				$row['bankusername'] = $row['bankusername'];

				$row['get_money'] = $row['money']-$row['service_charge_money'];
				$row['addtime'] = date('Y-m-d H:i:s', $row['addtime']);
				if(!empty($row['shentime']))
				{
					$row['shentime'] = date('Y-m-d H:i:s', $row['shentime']);
				}

				if($row['state'] ==0)
				{
					$row['state'] = '待审核';
				}else if($row[state] ==1)
				{
					$row['state'] = '已审核，打款';
				}else if($row[state] ==2){
					$row['state'] = '已拒绝';
				}
			}
			unset($row);

			$columns = array(
				array('title' => 'ID', 'field' => 'id', 'width' => 12),
				array('title' => '用户名', 'field' => 'username', 'width' => 12),
				array('title' => '联系方式', 'field' => 'telephone', 'width' => 12),
				array('title' => '打款银行', 'field' => 'bankname', 'width' => 24),
				array('title' => '打款账户', 'field' => 'bankaccount', 'width' => 24),
				array('title' => '真实姓名', 'field' => 'bankusername', 'width' => 24),
				array('title' => '申请提现金额', 'field' => 'money', 'width' => 24),
				array('title' => '手续费', 'field' => 'service_charge_money', 'width' => 24),
				array('title' => '到账金额', 'field' => 'get_money', 'width' => 24),
				array('title' => '申请时间', 'field' => 'addtime', 'width' => 24),
				array('title' => '审核时间', 'field' => 'shentime', 'width' => 24),
				array('title' => '状态', 'field' => 'state', 'width' => 24)
			);

			D('Seller/Excel')->export($list, array('title' => '拼团佣金提现数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));

		}

		$pager = pagination2($total, $pindex, $psize);


		$this->list = $list;
		$this->pager = $pager;
		$this->_GPC = $_GPC;

        $this->display();
	}


	public function withdraw_config()
	{
	    $_GPC = I('request.');

	    if (IS_POST) {

	        $data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));


			$data['pintuan_tixianway_yuer'] = isset($data['pintuan_tixianway_yuer']) ? $data['pintuan_tixianway_yuer']:1;
			$data['pintuan_tixianway_weixin'] = isset($data['pintuan_tixianway_weixin']) ? $data['pintuan_tixianway_weixin']:1;
			$data['pintuan_tixianway_alipay'] = isset($data['pintuan_tixianway_alipay']) ? $data['pintuan_tixianway_alipay']:1;
			$data['pintuan_tixianway_bank'] = isset($data['pintuan_tixianway_bank']) ? $data['pintuan_tixianway_bank']:1;
			$data['pintuan_tixian_publish'] = isset($data['pintuan_tixian_publish']) ? $data['pintuan_tixian_publish']:'';


	        D('Seller/Config')->update($data);

	        show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
			die();

	    }

	    $data = D('Seller/Config')->get_all_config();
		$this->data = $data;

	    $this->display();
	}

	public function jia_over_order()
	{
		$_GPC = I('request.');

		$pin_id = $_GPC['pin_id'];

		$res = D('Home/Pin')->jia_over_order( $pin_id );

		if( $res )
		{

			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']  ) );
		}else{
			show_json(0,  array('url' => $_SERVER['HTTP_REFERER']  ) );
		}
	}


	public function agent_check_apply()
	{
		 $_GPC = I('request.');

		$commission_model = D('Home/Pin');


		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$comsiss_state = intval($_GPC['state']);

		$apply_list = M('eaterplanet_ecommerce_pintuan_tixian_order')->where('id in( ' . $id . ' )')->select();

		$time = time();

		//var_dump($members,$comsiss_state);die();
		foreach ($apply_list as $apply) {
			if ($apply['state'] == $comsiss_state || $apply['state'] == 1 || $apply['state'] == 2) {
				continue;
			}
			$money = $apply['money'];

			if ($comsiss_state == 1) {


				switch( $apply['type'] )
				{
					case 1:
						$result = $commission_model->send_apply_yuer( $apply['id'] );
						break;
					case 2:
						$result = $commission_model->send_apply_weixin_yuer( $apply['id'] );
						break;
					case 3:
						$result = $commission_model->send_apply_alipay_bank( $apply['id'] );
						break;
					case 4:
						$result = $commission_model->send_apply_alipay_bank( $apply['id'] );
						break;
				}

				if( $result['code'] == 1)
				{
					show_json(0,  array('url' => $_SERVER['HTTP_REFERER'] ,'message'=>$result['msg'] ) );
					die();
				}

				//检测是否存在账户，没有就新建
				//TODO....检测是否微信提现到零钱，如果是，那么就准备打款吧

				$commission_model->send_apply_success_msg($apply['id']);
			}
			else if ($comsiss_state == 2) {

				M('eaterplanet_ecommerce_pintuan_tixian_order')->where( array('id' => $apply['id'] ) )->save( array('state' => 2, 'shentime' => $time) );
				//退回冻结的货款

				M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $apply['member_id'] ) )->setInc('money',$money);
				M('eaterplanet_ecommerce_pintuan_commiss')->where( array('member_id' => $apply['member_id'] ) )->setInc('dongmoney',-$money);

			}
			else {

				M('eaterplanet_ecommerce_pintuan_tixian_order')->where( array('id' => $apply['id']) )->save( array('state' => 0, 'shentime' => 0) );
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}

	/**
	 * 更新商品分类排序
	 */
	public function update_catesort(){
		$cate_id = I('post.cate_id', 0);
		$sort_order = I('post.sort_order', 0);
		if(!is_numeric($sort_order)){
			show_json(0, array('message' => '分类排序必须为数字'));
		}
		if($sort_order < 1 || $sort_order > 999){
			show_json(0, array('message' => '分类排序必须在1-999之间'));
		}
		if($cate_id > 0){
			$where = array();
			$where['sort_order'] = $sort_order;
			$where['cate_type'] = 'pintuan';
			$where['id'] = array('neq',$cate_id);
			$cate_info = M('eaterplanet_ecommerce_goods_category')->where($where)->find();
			if(!empty($cate_info)){
				show_json(0, array('message' => '分类排序已存在，请重新填写'));
			}
			M('eaterplanet_ecommerce_goods_category')->where( array('id' => $cate_id) )->save(array('sort_order'=>$sort_order));
			show_json(1, array('url' => U('group/goodscategory')));
		}else{
			show_json(0, array('message' => '分类信息错误'));
		}
	}

	/************************************** 拼团返利设置begin *************************************************/
	public function rebate_config()
	{
		if (IS_POST) {

			$data = I('post.parameter', array());

			D('Seller/Config')->update($data);

			if($data['is_open_pintuan_rebate'] == 0){
				$good_pin_list = M('eaterplanet_ecommerce_good_pin')->where( array('is_pintuan_rebate' => 1) )->select();
				$pin_ids = array();
				foreach($good_pin_list as $k=>$v){
					$pin_ids[] = $v['id'];
				}
				$ids = implode(',', $pin_ids);
				M('eaterplanet_ecommerce_good_pin')->where('id in( ' . $ids . ' )')->save( array('is_pintuan_rebate' => 0) );
			}
			show_json(1, array('url'=> U('group/rebate_config')));
		}

		$data = D('Seller/Config')->get_all_config();
		$this->data = $data;

		$this->display();
	}
	/************************************** 拼团返利设置end *************************************************/
}
?>
