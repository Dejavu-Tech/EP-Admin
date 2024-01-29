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

class GoodsController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='商品管理';
		$this->breadcrumb2='普通商品信息';
		$this->sellerid = SELLERUID;
	}

	public function index(){

		$pindex = I('get.page', 1);
		$psize = 20;



		$starttime_arr = I('get.time');

		$starttime = isset($starttime_arr['start']) ? strtotime($starttime_arr['start']) : strtotime(date('Y-m-d'.' 00:00:00'));

		$endtime = isset($starttime_arr['end']) ? strtotime($starttime_arr['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


		$sort_starttime = I('get.sort_starttime');
		$sort_endtime = I('get.sort_endtime');

		if( isset($sort_starttime) && $sort_starttime > 0 )
		{
		    $starttime = $sort_starttime;
		}

		if( isset($sort_endtime) && $sort_endtime > 0 )
		{
		    $endtime = $sort_endtime;
		}


		$this->starttime = $starttime;
		$this->endtime = $endtime;

		$searchtime = I('get.searchtime','');

		$this->searchtime = $searchtime;
		$shop_data = array();

		$type =  I('get.type','all');

        $goods_type = I('get.goods_type', 'normal');

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

        $goods_stock_notice = D('Home/Front')->get_config_by_name('goods_stock_notice');
        $goods_stock_notice = intval($goods_stock_notice);
        if( empty($goods_stock_notice) )
        {
            $goods_stock_notice = 0;
        }



        if($goods_type != 'normal' && $goods_type == 'presale')
        {
            $all_count =  D('Seller/Goods')->get_goods_count(" and type = 'presale' {$count_common_where}");//全部商品数量
            $onsale_count = D('Seller/Goods')->get_goods_count(" and grounding = 1 and type = 'presale' {$count_common_where}");//出售中商品数量
            $getdown_count = D('Seller/Goods')->get_goods_count(" and grounding = 0 and type = 'presale' {$count_common_where}");//已下架商品数量
            $warehouse_count = D('Seller/Goods')->get_goods_count(" and grounding = 2 and type = 'presale' {$count_common_where}");//仓库商品数量
            $recycle_count = D('Seller/Goods')->get_goods_count(" and grounding = 3 and type = 'presale' {$count_common_where}");//回收站商品数量
            $waishen_count = D('Seller/Goods')->get_goods_count(" and grounding = 4 and type = 'presale' {$count_common_where}");//审核商品数量
            $unsuccshen_count = D('Seller/Goods')->get_goods_count(" and grounding = 5 and type = 'presale' {$count_common_where}");//拒绝审核商品数量
            $stock_notice_count = D('Admin/Goods')->get_goods_count(" and grounding = 1 and total<= {$goods_stock_notice} and type = 'presale' {$count_common_where}  ");//回收站商品数量

        }
        else if($goods_type != 'normal' && $goods_type == 'virtualcard')
        {
            //礼品卡
            $all_count =  D('Seller/Goods')->get_goods_count(" and type = 'virtualcard' {$count_common_where}");//全部商品数量
            $onsale_count = D('Seller/Goods')->get_goods_count(" and grounding = 1 and type = 'virtualcard' {$count_common_where}");//出售中商品数量
            $getdown_count = D('Seller/Goods')->get_goods_count(" and grounding = 0 and type = 'virtualcard' {$count_common_where}");//已下架商品数量
            $warehouse_count = D('Seller/Goods')->get_goods_count(" and grounding = 2 and type = 'virtualcard' {$count_common_where}");//仓库商品数量
            $recycle_count = D('Seller/Goods')->get_goods_count(" and grounding = 3 and type = 'virtualcard' {$count_common_where}");//回收站商品数量
            $waishen_count = D('Seller/Goods')->get_goods_count(" and grounding = 4 and type = 'virtualcard' {$count_common_where}");//审核商品数量
            $unsuccshen_count = D('Seller/Goods')->get_goods_count(" and grounding = 5 and type = 'virtualcard' {$count_common_where}");//拒绝审核商品数量
            $stock_notice_count = D('Admin/Goods')->get_goods_count(" and grounding = 1 and total<= {$goods_stock_notice} and type = 'virtualcard' {$count_common_where}  ");//回收站商品数量

        }
        else{
            $all_count =  D('Seller/Goods')->get_goods_count(" and type = 'normal' {$count_common_where}");//全部商品数量
            $onsale_count = D('Seller/Goods')->get_goods_count(" and grounding = 1 and type = 'normal' {$count_common_where}");//出售中商品数量
            $getdown_count = D('Seller/Goods')->get_goods_count(" and grounding = 0 and type = 'normal' {$count_common_where}");//已下架商品数量
            $warehouse_count = D('Seller/Goods')->get_goods_count(" and grounding = 2 and type = 'normal' {$count_common_where}");//仓库商品数量
            $recycle_count = D('Seller/Goods')->get_goods_count(" and grounding = 3 and type = 'normal' {$count_common_where}");//回收站商品数量
            $waishen_count = D('Seller/Goods')->get_goods_count(" and grounding = 4 and type = 'normal' {$count_common_where}");//审核商品数量
            $unsuccshen_count = D('Seller/Goods')->get_goods_count(" and grounding = 5 and type = 'normal' {$count_common_where}");//拒绝审核商品数量
            $stock_notice_count = D('Admin/Goods')->get_goods_count(" and grounding = 1 and total<= {$goods_stock_notice} and type = 'normal' {$count_common_where}  ");//回收站商品数量

        }



		$this->assign('waishen_count',$waishen_count);
		$this->assign('unsuccshen_count',$unsuccshen_count);


		//goods_stock_notice


		//grounding 1

		//type all  全部

		//saleon 1 出售中
		//getdown 0 已下架
		//warehouse 2 仓库中
		//recycle 3 回收站


		$psize = 20;

		$condition = ' WHERE  g.type = "normal" ';

        //预售
        if($goods_type != 'normal' && $goods_type == 'presale')
        {
            $condition = ' WHERE  g.type = "presale" ';
        }else if( $goods_type != 'normal' && $goods_type == 'virtualcard' )
        {
            $condition = ' WHERE  g.type = "virtualcard" ';
        }

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
		            $condition .= ' AND (gm.begin_time >='.$starttime.' and gm.end_time <= '.$endtime.' )';

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
			$cat_ids = D('Seller/GoodsCategory')->getChildCategorys($cate,'id');
			$cate_list = M('eaterplanet_ecommerce_goods_to_category')->field('goods_id')->where(array('cate_id' => array('in', $cat_ids)))->select();

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


		//'sortby' =>$sortby,'sortfield' => 'day_salescount',

		$sortby = I('get.sortby');
		$sortfield = I('get.sortfield');

		$this->sortfield = $sortfield;

		$sortby = (!empty($sortby) ? ($sortby== 'asc' ?'desc':'asc') : ( !empty($sortfield) ? 'desc':'' ) );

		$this->sortby = $sortby;

		$pager = pagination2($total, $pindex, $psize);

		if (!(empty($total))) {

			$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');

			if( empty($index_sort_method) || $index_sort_method == 0 )
			{
			    $sort_way = 'g.istop DESC, g.settoptime DESC, g.`id` DESC';

			    if( !empty($sortfield) )
			    {
			        $sort_way = ' g.'.$sortfield.' '.$sortby.' , '.$sort_way;
			    }

				$sql = 'SELECT g.* FROM ' .C('DB_PREFIX'). 'eaterplanet_ecommerce_goods g '  .$sqlcondition . $condition . '
					ORDER BY   '.$sort_way.' ';
			}else{
			    $sort_way = 'g.index_sort DESC, g.`id` DESC';

			    if( !empty($sortfield) )
			    {
			        $sort_way = ' g.'.$sortfield.' '.$sortby.' ,'.$sort_way;
			    }
				$sql = 'SELECT g.* FROM ' .C('DB_PREFIX'). 'eaterplanet_ecommerce_goods g '  .$sqlcondition . $condition . '
					ORDER BY  '.$sort_way.'  ';
			}

            if(I('export') != 1) { // 如果导出excel 就显示全部
                $sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
            }
			//$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;


			$list = M()->query($sql);

			$open_redis_server = D('Home/Front')->get_config_by_name('open_redis_server');

			if( isset($open_redis_server) && $open_redis_server >0 )
			{
			    $open_redis_server = 1;
			}else{
			    $open_redis_server = 0;
			}
			$this->open_redis_server = $open_redis_server;

			foreach ($list as $key => &$value ) {

				$price_arr = D('Home/pingoods')->get_goods_price($value['id'], -888 );
                $value['codes'] = "\t".$value['codes']."\t";
				$value['price_arr'] = $price_arr;

				$thumb = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $value['id']) )->order('id asc')->find();


				if( empty($thumb['thumb']) )
				{
				    $value['thumb'] =  $thumb['image'];
				}else{
				    $value['thumb'] =  $thumb['thumb'];
				}

				//is_take_fullreduction print_sub_title
				$gd_common = M('eaterplanet_ecommerce_good_common')->field('is_take_fullreduction,supply_id,is_only_distribution,is_spike_buy,is_only_express,is_only_hexiao,print_sub_title')->where( array('goods_id' => $value['id']) )->find();

				//print_sub_title
				$value['print_sub_title'] =  $gd_common['print_sub_title'];

				//判断是否开启redis
				if( $open_redis_server == 1 )
				{
				    $value['redis_total'] = D('Seller/Redisorder')->get_goods_total_quantity( $value['id'] );
				}


				$value['is_take_fullreduction'] =  $gd_common['is_take_fullreduction'];
				$value['is_only_hexiao'] =  $gd_common['is_only_hexiao'];
				$value['is_only_distribution'] =  $gd_common['is_only_distribution'];

				$value['is_spike_buy'] =  $gd_common['is_spike_buy'];
				$value['is_only_express'] =  $gd_common['is_only_express'];


				$value['supply_name'] = '';
				$value['supply_type'] = '0';

				if( empty($gd_common['supply_id']) || $gd_common['supply_id'] ==0 )
				{
					$value['supply_id'] = 0;
				}else{
					$value['supply_id'] = $gd_common['supply_id'];

					$sub_info = M('eaterplanet_ecommerce_supply')->field('name,type')->where( array('id' => $gd_common['supply_id'] ) )->find();

					$value['supply_name'] = $sub_info['name'];
					$value['supply_type'] = $sub_info['type'];

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
                $value['option'] = [];
                if(I('get.export') > 0) {// 商品列表到处excel时要导出规格
                    $value['option'] = M('eaterplanet_ecommerce_goods_option_item_value')->where('goods_id = "'.$value['id'].'"')->field('id,title,productprice,marketprice,card_price,stock,title,goodssn,costprice,weight')->select();
                    if($value['option'] === null){
                        $value['option'] = [];
                    }

                    foreach($categorys as $categorysKey => $categorysVal){
                        $categorysInfo = M('eaterplanet_ecommerce_goods_category')->where( array('id' => $categorysVal['cate_id']) )->find();
                        $value['cate'.($categorysKey+1).'_id'] = $categorysVal['cate_id'];
                        $value['cate'.($categorysKey+1).'_name'] = $categorysInfo['name'];
                    }

                    $goodsCommon = M('eaterplanet_ecommerce_good_common')->where('goods_id = "'.$value['id'].'"')->field('is_spike_buy,is_new_buy,one_limit_count,oneday_limit_count,total_limit_count,begin_time,end_time')->find();
                    $value['oneday_limit_count'] = $goodsCommon['oneday_limit_count'];
                    $value['one_limit_count'] = $goodsCommon['one_limit_count'];
                    $value['total_limit_count'] = $goodsCommon['total_limit_count'];
                    $value['is_spike_buy'] = $goodsCommon['is_spike_buy'];
                    $value['is_new_buy'] = $goodsCommon['is_new_buy'];
					$value['pin_begin_time'] = date('Y-m-d H:i:s',$goodsCommon['begin_time']);
					$value['pin_end_time'] = date('Y-m-d H:i:s',$goodsCommon['end_time']);

                    $goodsPin = M('eaterplanet_ecommerce_good_pin')->where('goods_id = "'.$value['id'].'"')->field('begin_time,end_time')->find();
                    if($goodsPin){
                        $value['pin_begin_time'] = date('Y-m-d H:i:s',$goodsPin['begin_time']);
                        $value['pin_end_time'] = date('Y-m-d H:i:s',$goodsPin['end_time']);
                    }

                    if($value['is_all_sale'] == 1){
                        $value['is_all_sale_str'] = '[所有团长'.$head_count.']';
                    }else if($head_count == 0){
                        $value['is_all_sale_str'] = '[无团长0]';
                    }else if($head_count > 0){
                        $value['is_all_sale_str'] = '[部分团长'.$head_count.']';
                    }
                }

			}

		}
		unset($value);


		$categorys = D('Seller/GoodsCategory')->getFullCategory(true);
		$category = array();

		foreach ($categorys as $cate ) {
			$category[$cate['id']] = $cate;
		}

		$this->category =$category;

		$this->type = $type;
		$this->all_count = $all_count;
		$this->onsale_count = $onsale_count;
		$this->getdown_count = $getdown_count;
		$this->warehouse_count = $warehouse_count;
		$this->recycle_count = $recycle_count;
		$this->stock_notice_count = $stock_notice_count;


		$this->assign('list',$list);// 赋值数据集
		$this->assign('pager',$pager);// 赋值分页输出
		$is_open_fullreduction = D('Home/Front')->get_config_by_name('is_open_fullreduction');
		$this->assign('is_open_fullreduction',$is_open_fullreduction);

		//商户同城配送  0关 1开
		$supply_is_open_localtown_distribution = D('Home/Front')->get_config_by_name('supply_is_open_localtown_distribution');
		$this->assign('supply_is_open_localtown_distribution',$supply_is_open_localtown_distribution);

		//总后台同城配送  0关 1开
		$isopen_localtown_delivery = D('Home/Front')->get_config_by_name('isopen_localtown_delivery');
		$this->assign('isopen_localtown_delivery',$isopen_localtown_delivery);

		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');

		if( empty($index_sort_method) || $index_sort_method == 0 )
		{
			$index_sort_method = 0;
		}
		$this->index_sort_method = $index_sort_method;

		//---
		$supply_add_goods_shenhe = D('Home/Front')->get_config_by_name('supply_add_goods_shenhe');
		$supply_edit_goods_shenhe = D('Home/Front')->get_config_by_name('supply_edit_goods_shenhe');

		if(empty($supply_add_goods_shenhe))
		{
			$supply_add_goods_shenhe = 0;
		}
		if(empty($supply_edit_goods_shenhe))
		{
			$supply_edit_goods_shenhe = 0;
		}

		$is_open_shenhe = 0;

		if($supply_add_goods_shenhe ==1 || $supply_edit_goods_shenhe == 1)
		{
			$is_open_shenhe = 1;
		}

		$this->supply_add_goods_shenhe = $supply_add_goods_shenhe;
		$this->supply_edit_goods_shenhe = $supply_edit_goods_shenhe;

		$this->is_open_shenhe = $is_open_shenhe;
		//--

		//团长分组
		$group_default_list = array(
			array('id' => 'default', 'groupname' => '默认分组')
		);


		$group_list = M('eaterplanet_community_head_group')->field('id,groupname')->order('id asc')->select();

		$group_list = array_merge($group_default_list, $group_list);

		$this->group_list = $group_list;


		$config_data = D('Seller/Config')->get_all_config();

		$is_index = true;
		$is_top = true;
		$is_updown  = true;
		$is_fullreduce  = true;
		$is_vir_count = true;
		$is_newbuy = true;
		$is_goodsspike = true;
		$is_distributionsale = true;
		$is_supply_add_goods_shenhe = true;
		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$is_fullreduce = false;
			if( isset($config_data['supply_can_goods_isindex']) && $config_data['supply_can_goods_isindex'] == 2 )
			{
				$is_index = false;
			}
			if( isset($config_data['supply_can_goods_istop']) && $config_data['supply_can_goods_istop'] == 2 )
			{
				$is_top = false;
			}
			if( isset($config_data['supply_can_goods_updown']) && $config_data['supply_can_goods_updown'] == 2 )
			{
				$is_updown = false;
			}
			if( isset($config_data['supply_can_vir_count']) && $config_data['supply_can_vir_count'] == 2 )
			{
				$is_vir_count = false;
			}
			if( isset($config_data['supply_can_goods_newbuy']) && $config_data['supply_can_goods_newbuy'] == 2 )
			{
				$is_newbuy = false;
			}
			if( isset($config_data['supply_can_goods_spike']) && $config_data['supply_can_goods_spike'] == 2 )
			{
				$is_goodsspike = false;
			}
			if( isset($config_data['supply_can_distribution_sale']) && $config_data['supply_can_distribution_sale'] == 2 )
			{
				$is_distributionsale = false;
			}
			if( isset($config_data['supply_add_goods_shenhe']) && $config_data['supply_add_goods_shenhe'] == 0 )
			{
				$is_supply_add_goods_shenhe = false;
			}
		}
		$seckill_is_open =  D('Home/Front')->get_config_by_name('seckill_is_open');
		$this->seckill_is_open = $seckill_is_open;

        if(I('get.export') > 0) {// 商品列表到处excel
            $this->goods_listexcel($list);
        }
		$this->config_data = $config_data;

		$this->is_index = $is_index;
		$this->is_top = $is_top;
		$this->is_updown  = $is_updown;
		$this->is_fullreduce  = $is_fullreduce;
		$this->is_vir_count = $is_vir_count;
		$this->is_newbuy = $is_newbuy;
		$this->is_goodsspike = $is_goodsspike;
		$this->is_distributionsale = $is_distributionsale;
		$this->is_supply_add_goods_shenhe = $is_supply_add_goods_shenhe;

		$this->display();
	}

    /**
     * 商品列表到处excel
     * @param $list 商品列表集合数据 全部-根据where 条件搜索的
     * */
    public function goods_listexcel($list) {
//        dump($list);die;
        $printList = $list;
        foreach($list as $key => $value) {
            $printList[$key] = array(
                'id' => $value['id']
            );
        }
        $columns = array(
            array(
                'title' => '商品ID(禁止修改)',
                'field' => 'id',
                'width' => 24
            ) ,
            array(
                'title' => '商品名称',
                'field' => 'goodsname',
                'width' => 24
            ) ,
			array(
                'title' => '商品简称',
                'field' => 'print_sub_title',
                'width' => 24
            ) ,
            array(
                'title' => '商品编码',
                'field' => 'codes',
                'width' => 24
            ) ,
            array(
                'title' => '一级分类ID',
                'field' => 'cate1_id',
                'width' => 24
            ) ,
            array(
                'title' => '一级分类名称',
                'field' => 'cate1_name',
                'width' => 24
            ) ,
            array(
                'title' => '二级分类ID',
                'field' => 'cate2_id',
                'width' => 24
            ) ,
            array(
                'title' => '二级分类名称',
                'field' => 'cate2_name',
                'width' => 24
            ) ,

            array(
                'title' => '商品价格',
                'field' => 'price',
                'width' => 24
            ) ,
            array(
                'title' => '商品成本价',
                'field' => 'costprice',
                'width' => 24
            ) ,
            array(
                'title' => '会员卡价格',
                'field' => 'card_price',
                'width' => 24
            ) ,
            array(
                'title' => '商品原价',
                'field' => 'productprice',
                'width' => 24
            ) ,
            array(
                'title' => '商品库存',
                'field' => 'total',
                'width' => 24
            ) ,
            array(
                'title' => '每日销量',
                'field' => 'day_salescount',
                'width' => 24
            ) ,
			array(
                'title' => '总销量',
                'field' => 'seller_count',
                'width' => 24
            ) ,
            array(
                'title' => '1上架/0下架',
                'field' => 'grounding',
                'width' => 24
            ) ,
            array(
                'title' => '首页推荐(0:取消/1:是)',
                'field' => 'is_index_show',
                'width' => 24
            ) ,
             array(
                 'title' => '限时秒杀(0:取消/1:是)',
                 'field' => 'is_spike_buy',
                 'width' => 24
             ) ,
            array(
                'title' => '所有团长',
                'field' => 'is_all_sale_str',
                'width' => 24
            ) ,
            array(
                'title' => '新人专享',
                'field' => 'is_new_buy',
                'width' => 24
            ) ,

            array(
                'title' => '商品排序(数字)',
                'field' => 'index_sort',
                'width' => 24
            ) ,
            array(
                'title' => '每天限购',
                'field' => 'oneday_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '单次限购',
                'field' => 'one_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '历史限购',
                'field' => 'total_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '开始时间',
                'field' => 'pin_begin_time',
                'width' => 24
            ) ,
            array(
                'title' => '结束时间',
                'field' => 'pin_end_time',
                'width' => 24
            ) ,
            array(
                'title' => '商品重量(单位:g)',
                'field' => 'weight',
                'width' => 24
            ) ,
            array(
                'title' => '规格(1:开启/0:关闭)',
                'field' => 'hasoption',
                'width' => 24
            ) ,
            array(
                'title' => '规格id(禁止修改)',
                'field' => 'option_id',
                'width' => 24
            ) ,
            array(
                'title' => '规格名称(禁止修改)',
                'field' => 'option_title',
                'width' => 24
            ) ,
            array(
                'title' => '规格库存',
                'field' => 'option_stock',
                'width' => 24
            ) ,
            array(
                'title' => '规格现价',
                'field' => 'option_marketprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格原价',
                'field' => 'option_productprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格会员价',
                'field' => 'option_card_price',
                'width' => 24
            ) ,
            array(
                'title' => '规格成本价',
                'field' => 'option_costprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格编码',
                'field' => 'option_goodssn',
                'width' => 24
            ) ,
            array(
                'title' => '规格重量(单位:g)',
                'field' => 'option_weight',
                'width' => 24
            )
        );
        sellerLog('导出商品excel', 3);
         D('Seller/Excel')->export_goods_list_pi(array(
            'title' => '商品列表',
            'columns' => $columns
        ), $list);
    }

	public function edittags()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);
		if (!empty($id)) {

			$item = M('eaterplanet_ecommerce_goods_tags')->field('id,type,tagname,tagcontent,state,sort_order')->where( array('id' =>$id ) )->find();

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

			D('Seller/Tags')->update($data);

			show_json(1, array('url' => U('goods/goodstag') ));
		}

		$this->display('Goods/addtags');
	}

	public function addtags()
	{
		$_GPC = I('request.');

		if (IS_POST) {

			$data = $_GPC['data'];

			D('Seller/Tags')->update($data);

			show_json(1, array('url' => U('goods/goodstag')));
		}

		$this->display();
	}


	public function show_logs()
	{
	    $goods_id = I('get.goods_id');

	    D('Seller/Redisorder')->show_logs($goods_id);

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
			M('eaterplanet_ecommerce_goods_tags')->where( array('id' => $item['id']) )->save( array('state' => intval($_GPC['state'])) );
			D('Seller/Operatelog')->addOperateLog('goods','修改商品标签状态--'.$item['tagname']);
		}

		show_json(1, array('url' => U('goods/goodstag')));
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
			D('Seller/Operatelog')->addOperateLog('goods','删除商品标签--'.$item['tagname']);
		}

		show_json(1, array('url' => U('goods/goodstag')));
	}


	public function labelquery()
	{
		$_GPC = I('request.');

		$kwd = trim($_GPC['keyword']);
		$type = isset($_GPC['type']) ? $_GPC['type'] : 'normal';


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


	public function deletecomment()
	{
		$_GPC = I('request.');


		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = M()->query('SELECT comment_id FROM ' . C('DB_PREFIX') .
					'eaterplanet_ecommerce_order_comment WHERE comment_id in( ' . $id . ' ) ');



		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('eaterplanet_ecommerce_order_comment')->where( array('comment_id' => $item['comment_id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));

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


	public function change_cm()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		//ids
		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}


		if (empty($id)) {
			show_json(0, array('message' => '参数错误'));
		}


		$type = trim($_GPC['type']);
		$value = trim($_GPC['value']);

		if (!(in_array($type, array('is_take_fullreduction')))) {
			show_json(0, array('message' => '参数错误'));
		}

		$items = M('eaterplanet_ecommerce_goods')->field('id')->where( 'id in( ' . $id . ' )' )->select();
		foreach ($items as $item ) {

			//--
			if($type == 'is_take_fullreduction' && $value == 1)
			{
				$gd_common = M('eaterplanet_ecommerce_good_common')->field('supply_id')->where( array('goods_id' => $item['id'] ) )->find();

				if( !empty($gd_common['supply_id']) && $gd_common['supply_id'] > 0)
				{
					$supply_info = M('eaterplanet_ecommerce_supply')->field('type')->where( array('id' => $gd_common['supply_id'] ) )->find();


					if( !empty($supply_info) && $supply_info['type'] == 1 )
					{
						continue;
					}
				}
			}
			//---

			M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $item['id']) )->save( array($type => $value) );
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}


	public function commentstate()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = M('eaterplanet_ecommerce_order_comment')->field('comment_id,member_id,is_send_point')->where( "comment_id in ({$id})")->select();

		if (empty($item)) {
			$item = array();
		}

		foreach ($items as $item) {
			M('eaterplanet_ecommerce_order_comment')->where( array('comment_id' => $item['comment_id']) )->save( array('state' => intval($_GPC['state'])) );
			if(intval($_GPC['state']) == 1 && $item['is_send_point'] == 0){
			    //好评有礼送积分
			    D('Seller/Order')->sendCommentGift($item['comment_id']);
			    M('eaterplanet_ecommerce_order_comment')->where(array('comment_id'=>$item['comment_id'],'member_id'=>$item['member_id']))->save(array('is_send_point'=>1));
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}


	/**
	 * 一键设置商品时间
	 */
	public function settime()
	{

		if (IS_POST) {

			$data = I('request.time', array());

			$param = array();
			$param['goods_same_starttime'] = strtotime(trim($data['start'])) ? strtotime(trim($data['start'])) : time();
			$param['goods_same_endtime'] = strtotime(trim($data['end'])) ? strtotime(trim($data['end'])) : time();


			$is_samedefault_now = I('request.is_samedefault_now');
			$is_sametihuo_time = I('request.is_sametihuo_time');
			$pick_up_type = I('request.pick_up_type');
			$pick_up_modify = I('request.pick_up_modify');


			if( $pick_up_type == 4 )
			{
				$pick_up_type = 0;
			}

			$param['is_samedefault_now'] = $is_samedefault_now;
			$param['is_sametihuo_time'] = $is_sametihuo_time;
			$param['pick_up_type'] = $pick_up_type;
			$param['pick_up_modify'] = $pick_up_modify;

			// eaterplanet_ecommerce_good_common begin_time end_time
			D('Seller/Config')->update($param);

			$param1 = array();
			$param1['begin_time'] = $param['goods_same_starttime'];
			$param1['end_time'] = $param['goods_same_endtime'];


			//--begin

			if (defined('ROLE') && ROLE == 'agenter' )
			{
				$supper_info = get_agent_logininfo();


				if( $is_samedefault_now == 2 )
				{
					$sql = " update  ".C('DB_PREFIX')."eaterplanet_ecommerce_good_common set end_time = ".$param['goods_same_endtime'].", begin_time=".$param['goods_same_starttime']."
						where ".'goods_id in (select id from '.C('DB_PREFIX').'eaterplanet_ecommerce_goods where `grounding` =1 and type != "pin") and supply_id='.$supper_info['id'];

					M()->execute($sql);
				}

				if( $is_sametihuo_time == 2 )
				{
					$sql = " update ".C('DB_PREFIX')."eaterplanet_ecommerce_good_common set pick_up_type = '".$pick_up_type."', pick_up_modify='".$pick_up_modify."'"
					    . " where goods_id in (select id from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods where `grounding` =1 and type != 'pin'  ) and supply_id=".$supper_info['id'];
					M()->execute($sql);
				}

			}else{



				//取出所有独立商户
				$all_du_supply = M('eaterplanet_ecommerce_supply')->where( array('type' => 1) )->select();

				$all_du_sids = array();

				if( !empty($all_du_supply) )
				{
					foreach( $all_du_supply as $val )
					{
						$all_du_sids[] = $val['id'];
					}
				}

				if( $is_samedefault_now == 1 )
				{
					//仅平台
					$where = "";
					if( !empty($all_du_sids) )
					{
						$where = " and supply_id not in(".implode(',', $all_du_sids ).") ";
					}

					$sql = " update  ".C('DB_PREFIX')."eaterplanet_ecommerce_good_common set end_time = ".$param['goods_same_endtime'].", begin_time=".$param['goods_same_starttime']."
						where ".'goods_id in (select id from '.C('DB_PREFIX').'eaterplanet_ecommerce_goods where `grounding` =1 and type != "pin" '.$where.' ) ';

					M()->execute($sql);

				}else if( $is_samedefault_now == 2 )
				{
					//所有商品除了拼团
					$sql = " update  ".C('DB_PREFIX')."eaterplanet_ecommerce_good_common set end_time = ".$param['goods_same_endtime'].", begin_time=".$param['goods_same_starttime']."
						where ".'goods_id in (select id from '.C('DB_PREFIX').'eaterplanet_ecommerce_goods where `grounding` =1 and type != "pin") ';

					M()->execute($sql);
				}

				if( $is_sametihuo_time == 1 )
				{
					//仅平台
					$where = "";
					if( !empty($all_du_sids) )
					{
						$where = " and supply_id not in(".implode(',', $all_du_sids ).") ";
					}

					$sql = " update ".C('DB_PREFIX')."eaterplanet_ecommerce_good_common set pick_up_type = ".$pick_up_type.", pick_up_modify='".$pick_up_modify."'
						where ".'goods_id in (select id from '.C('DB_PREFIX').'eaterplanet_ecommerce_goods where `grounding` =1 and type != "pin" '.$where.' ) ';

					M()->execute($sql);


				}else if( $is_sametihuo_time == 2 )
				{
					$sql = " update ".C('DB_PREFIX')."eaterplanet_ecommerce_good_common set pick_up_type = ".$pick_up_type.", pick_up_modify='".$pick_up_modify."'
						where ".'goods_id in (select id from '.C('DB_PREFIX').'eaterplanet_ecommerce_goods where `grounding` =1 and type != "pin"  ) ';

					M()->execute($sql);
				}

				/**
				$sql = 'UPDATE '.C('DB_PREFIX'). 'eaterplanet_ecommerce_good_pin SET begin_time = '.$param['goods_same_starttime'].
						',end_time='.$param['goods_same_endtime'].'  where goods_id in (select id from '.C('DB_PREFIX').'eaterplanet_ecommerce_goods where `grounding` =1) ';
				M()->execute($sql);
				**/

			}

			D('Seller/Operatelog')->addOperateLog('goods','修改商品设置--统一时间');

			//--end

			//M('eaterplanet_ecommerce_good_common')->where("1")->save($param1);

			show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
		}

		$data = D('Seller/Config')->get_all_config();

		if(empty($data['goods_same_starttime']))
		{
			$data['goods_same_starttime'] = time();
		}

		if(empty($data['goods_same_endtime']))
		{
			$data['goods_same_endtime'] = time()+86400;
		}



		$this->data = $data;

		$this->display();
	}

	public function addgoods()
	{
		if (IS_POST) {
            $_GPC = I('request.');
            $type = isset($_GPC['type']) ? $_GPC['type'] : 'normal';
            if (!isset($_GPC['thumbs']) || empty($_GPC['thumbs'])) {
                show_json(0, array('message' => '商品图片必须上传', 'url' => $_SERVER['HTTP_REFERER']));
                die();
            }
            if (!empty($_GPC['has_mb_level_buy'])) {

                foreach ($_GPC['discount'] as $val) {
                    if (preg_match("/^(([1-9]\d?)|100)$/", $val)) {

						}else{
							show_json(0,  array('message' => '请按照提示设置独立客户等级折扣' ,'url' => $_SERVER['HTTP_REFERER']) );
							die();
						}

					}
			}


			if($_GPC['is_only_distribution'] == 1 && !empty($_GPC['supply_id']) ){
				//商户开启同城
				$sup_isopen_localtown_delivery =  D('Home/Front')->get_supply_config_by_name('isopen_localtown_delivery',$_GPC['supply_id']);

                if (empty($sup_isopen_localtown_delivery)) {

					$supply_name  = M('eaterplanet_ecommerce_supply')->field('shopname,type')->where( array('id' => $_GPC['supply_id'] ) )->find();
					if($supply_name["type"] == 1 ){
							show_json(0, array('message' => '独立商户('.$supply_name["shopname"].')没有开启同城配送，此商品无法开启同城配送' ,'url' => $_SERVER['HTTP_REFERER']) );
							die();
					}


				}


            }
            $supply_id = $_GPC['supply_id'];
            if (defined('ROLE') && ROLE == 'agenter') {
                $supper_info = get_agent_logininfo();
                $supply_id = $supper_info['id'];
            }

            if ($_GPC['is_only_hexiao'] == 1) {
                if (!empty($supply_id)) {
                    $salesroom_count = M('eaterplanet_ecommerce_salesroom')->where(array('supply_id' => $supply_id, 'state' => 1))->count();
                    if ($salesroom_count == 0) {
                        $supply_name = M('eaterplanet_ecommerce_supply')->field('shopname,type')->where(array('id' => $supply_id))->find();
                        if ($supply_name["type"] == 1) {
                            show_json(0, array('message' => '独立商户(' . $supply_name["shopname"] . ')没有创建门店信息，此商品无法开启到店核销', 'url' => $_SERVER['HTTP_REFERER']));
                            die();
                        }
                    }
                }
                if ($_GPC['grounding'] == 1) {//上架商品
                    if ($_GPC['hx_expire_type'] == 1) {//指定过期时间
                        $hx_expire_end_time = $_GPC['hx_expire_end_time'];
                        if (strtotime($hx_expire_end_time) < time()) {
                            show_json(0, array('message' => '核销过期时间小于当前时间，商品不能上架', 'url' => $_SERVER['HTTP_REFERER']));
                            die();
                        }
                    }
                }
                if ($_GPC['hx_expire_type'] == 1) {//指定过期时间
                    if ($_GPC['hx_auto_off'] == 1) {//设置自动下架时间
                        if (empty($_GPC['hx_auto_off_time']) || !is_numeric($_GPC['hx_auto_off_time']) || floatval($_GPC['hx_auto_off_time']) <= 0) {
                            show_json(0, array('message' => '提前自动下架商品时间不能为空且必须为大于0数字', 'url' => $_SERVER['HTTP_REFERER']));
                            die();
                        }
                    }
                }

            }

            //礼品卡
            if (isset($_GET['goods_type']) && $_GET['goods_type'] == 'virtualcard')
            {
                $type = 'virtualcard';
                $virtual_code_id = I('post.virtual_code_id');
                if( !isset($virtual_code_id) || $virtual_code_id <= 0 )
                {
                    show_json(0, array('message' => '礼品卡商品必须关联兑换码' ,'url' => $_SERVER['HTTP_REFERER']) );
                    die();
                }
            }

            //预售时间判断
            if( isset($_GET['goods_type']) && $_GET['goods_type'] == 'presale' )
            {
				$type = 'presale';
                //预售时间限制判断
                $time = I('post.time');

                $post_begin_time = strtotime( $time['start'] );
                $post_end_time = strtotime( $time['end'] );

                $presale_ding_time = I('post.presale_ding_time');//定金支付时间
                $presale_ding_time_start = strtotime( $presale_ding_time['start'] ); //开始
                $presale_ding_time_end = strtotime( $presale_ding_time['end'] );//结束

                if($presale_ding_time_start < $post_begin_time )
                {
                    show_json(0, array('message' => '预售定金支付开始时间不能小于团购开始时间' ,'url' => $_SERVER['HTTP_REFERER']) );
                    die();
                }
                if($presale_ding_time_end > $post_end_time  )
                {
                    show_json(0, array('message' => '预售定金支付结束时间不能大于团购结束时间' ,'url' => $_SERVER['HTTP_REFERER']) );
                    die();
                }

                $presale_sendorder_type = I('post.presale_sendorder_type');
                if($presale_sendorder_type  == 1)
                {
                    $presale_sendorder_afterday = I('post.presale_sendorder_afterday');
                    if( $presale_sendorder_afterday < 1 )
                    {
                        show_json(0, array('message' => '预售购买后几日发货必须是大于0的整数' ,'url' => $_SERVER['HTTP_REFERER']) );
                        die();
                    }
                }else if( $presale_sendorder_type == 0 )
                {
                    //预计发货时间 必须大于 定金尾款付款时间
                    $presale_sendorder_datetime = I('post.presale_sendorder_datetime');
                    $presale_sendorder_datetime = strtotime($presale_sendorder_datetime.' 00:00:00');
                    if( $presale_sendorder_datetime < $presale_ding_time_start )
                    {
                        show_json(0, array('message' => '固定发货日期必须大于定金支付开始时间' ,'url' => $_SERVER['HTTP_REFERER']) );
                        die();
                    }

                }
            }

			//判断预售价格是否合理设置，不是bug价格
            $this->checkPresaleAddGoods();

            D('Seller/Goods')->addgoods($type);

			$http_refer = S('HTTP_REFERER');

			$http_refer = empty($http_refer) ? $_SERVER['HTTP_REFERER'] : $http_refer;
            sellerLog('添加新增商品', 3);

            show_json(1, array('message' => '添加商品成功！','url' => $http_refer ));
		}
		S('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
		$category = D('Seller/GoodsCategory')->getFullCategory(true, true);
		$this->category = $category;

		$spec_list = D('Seller/Spec')->get_all_spec();
		$this->spec_list = $spec_list;




		$dispatch_data = M('eaterplanet_ecommerce_shipping')->where( array('enabled' => 1,'isdefault' =>1) )->order('sort_order desc')->select();
		$this->dispatch_data = $dispatch_data;

		$set =  D('Seller/Config')->get_all_config();
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

		//赠送积分开关
		$open_buy_send_score = empty($set['open_buy_send_score']) ? 0: $set['open_buy_send_score'];
		$this->open_buy_send_score = $open_buy_send_score;

		$index_sort_method = empty($set['index_sort_method']) ? 0: $set['index_sort_method'];
		$this->index_sort_method = $index_sort_method;

		$item = array();
		$item['begin_time'] = time();
		$item['community_head_commission'] = $communityhead_commission;

		$item['end_time'] = time() + 86400;

		$item['hexiao_effect_begin_time'] = time();
		$item['hexiao_effect_end_time'] = time() + 86400;

		//预售begin
		if( isset($_GET['goods_type']) && $_GET['goods_type'] == 'presale' )
        {
            $item['presale_ding_time_start'] = time();
            $item['presale_ding_time_end'] = time() + 86400;
        }
        //预售end

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

		$supply_can_goods_sendscore = true;
		$supply_can_distribution_sale = true ;
		//核销
		$sales_supply_id = 0;


		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$supply_can_goods_sendscore = empty($config_data['supply_can_goods_sendscore']) ? 0: $config_data['supply_can_goods_sendscore'];

			$supply_can_distribution_sale = empty($config_data['supply_can_distribution_sale']) ? 0: $config_data['supply_can_distribution_sale'];

			$is_fullreduce = false;
			if( isset($config_data['supply_can_goods_isindex']) && $config_data['supply_can_goods_isindex'] == 2 )
			{
				$is_index = false;
			}
			if( isset($config_data['supply_can_goods_istop']) && $config_data['supply_can_goods_istop'] == 2 )
			{
				$is_top = false;
			}
			if( isset($config_data['supply_can_goods_updown']) && $config_data['supply_can_goods_updown'] == 2 )
			{
				$is_updown = false;
			}
			if( isset($config_data['supply_can_vir_count']) && $config_data['supply_can_vir_count'] == 2 )
			{
				$is_vir_count = false;
			}
			if( isset($config_data['supply_can_goods_newbuy']) && $config_data['supply_can_goods_newbuy'] == 2 )
			{
				$is_newbuy = false;
			}
			if( isset($config_data['supply_can_goods_spike']) && $config_data['supply_can_goods_spike'] == 2 )
			{
				$is_goodsspike = false;
			}
			$sales_supply_id = $supper_info = get_agent_logininfo();
			$sales_supply_id = $supper_info['id'];
		}
		$salesroom_count = M('eaterplanet_ecommerce_salesroom')->where(array('supply_id' => $sales_supply_id,'state'=>1))->count();
		if($salesroom_count > 0){
			$this->is_exist_salesroom = 1;
		}

		$is_open_vipcard_buy = $config_data['is_open_vipcard_buy'];

		//商户权限end
		$this->is_index = $is_index;
		$this->supply_can_goods_sendscore = $supply_can_goods_sendscore;
		$this->supply_can_distribution_sale = $supply_can_distribution_sale;
		$this->is_top = $is_top;
		$this->is_updown  = $is_updown;
		$this->is_fullreduce  = $is_fullreduce;
		$this->is_vir_count = $is_vir_count;
		$this->is_newbuy = $is_newbuy;
		$this->is_goodsspike = $is_goodsspike;
		$this->is_open_vipcard_buy = $is_open_vipcard_buy;

		$seckill_is_open = $config_data['seckill_is_open'];
		$this->seckill_is_open = $seckill_is_open;

		// $is_head_takegoods
		$is_head_takegoods = isset($config_data['is_head_takegoods']) && $config_data['is_head_takegoods'] == 1 ? 1 : 0;

		$this->is_head_takegoods = $is_head_takegoods;


        //判断是否兑换卡 begin
        if( isset($_GET['goods_type']) && $_GET['goods_type'] == 'virtualcard' )
        {
            //商品选中的兑换编组
            $goods_virtualcode_id = 0;
            $this->goods_virtualcode_id = $goods_virtualcode_id;

            //可用的兑换编组列表
            $virtualcard_code_list = D('Seller/VirtualCard')->getCanUseVirtualcardCodes(  );
            $this->virtualcard_code_list = $virtualcard_code_list;
        }
        //end

        $isopen_localtown_delivery = isset($config_data['isopen_localtown_delivery']) && $config_data['isopen_localtown_delivery'] == 1 ? 1 : 0;
		$this->isopen_localtown_delivery = $isopen_localtown_delivery;

		$supply_is_open_localtown_distribution = isset($config_data['supply_is_open_localtown_distribution']) && $config_data['supply_is_open_localtown_distribution'] == 1 ? 1 : 0;
		$this->supply_is_open_localtown_distribution = $supply_is_open_localtown_distribution;

		$this->delivery_type_express = isset($config_data['delivery_type_express']) ? $config_data['delivery_type_express'] : 0;
		//$is_default_levellimit_buy = isset($config_data['is_default_levellimit_buy']) && $config_data['is_default_levellimit_buy'] == 1 ? 1 : 0;
		//$this->is_default_levellimit_buy = $is_default_levellimit_buy;

		//$is_default_vipmember_buy = isset($config_data['is_default_vipmember_buy']) && $config_data['is_default_vipmember_buy'] == 1 ? 1 : 0;
		//$this->is_default_vipmember_buy = $is_default_vipmember_buy;

		//获取客户等级列表
		$member_level_ist = M('eaterplanet_ecommerce_member_level')->order('id asc')->select();
		$this->member_level_ist = $member_level_ist;
		$this->display();
	}


    public function checkPresaleAddGoods()
    {
        if( isset($_POST['presale_type']) )
        {
            $presale_type = I('post.presale_type', 0);//预售方式 0 定金
            $presale_ding_money = I('post.presale_ding_money', 0);//定金金额，判断定金金额不能大于商品价格
            $presale_deduction_money = I('post.presale_deduction_money');//定金可抵扣金额

            if( $presale_type == 0)
            {
                $goods_price = I('post.price', 0);
                $hasoption = I('post.hasoption');
                if( intval($hasoption) == 1 )
                {
                    //取规格最低价格
                    $option_ids_arr = I('post.option_ids');

                    foreach($option_ids_arr as $val)
                    {
                        $productprice = I('post.option_productprice_' . $val);
                        if( $goods_price > $productprice )
                        {
                            $goods_price = $productprice;
                        }
                    }
                }
                if( $presale_ding_money > $goods_price )
                {
                    show_json(0 , ['message' => '预售方式为定金，定金金额不能大于商品单价' ] );
                }
                if( $presale_deduction_money > $goods_price )
                {
                    show_json(0 , ['message' => '预售方式为定金，定金膨胀金不能大于商品单价' ] );
                }
				if($presale_deduction_money != 0){
					if( $presale_deduction_money < $presale_ding_money )
					{
						show_json(0 , ['message' => '定金膨胀金不能小于定金金额' ] );
					}
				}

				//付费价格
				$is_open_vipcard_buy = D('Home/Front')->get_config_by_name('is_open_vipcard_buy');
				$is_take_vipcard = I('post.is_take_vipcard', 0);
				$card_price =I ('post.card_price'); //付费会员价
				if( !empty($is_open_vipcard_buy) && $is_open_vipcard_buy == 1 && $is_take_vipcard == 1){
					if( $presale_ding_money > $card_price )
					{
						show_json(0 , ['message' => '预售方式为定金，定金金额不能大于付费会员价' ] );
					}
					if( $presale_deduction_money > $card_price )
					{
						show_json(0 , ['message' => '预售方式为定金，定金膨胀金不能大于付费会员价' ] );
					}
				}


            }

        }
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
			D('Seller/Operatelog')->addOperateLog('goods','修改分类首页显示--'.$item['name']);
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}


	public function category_typeenabled()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = M('eaterplanet_ecommerce_goods_category')->field('id,name')->where( 'id in( ' . $id . ' )' )->select();

		foreach ($items as $item) {

			M('eaterplanet_ecommerce_goods_category')->where( array('id' => $item['id']) )->save( array('is_type_show' => intval($_GPC['enabled']))  );
			D('Seller/Operatelog')->addOperateLog('goods','修改分类独立分类页显示--'.$item['name']);
		}
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}


	public function category_expressenabled()
	{
		$_GPC = I('request.');

		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$items = M('eaterplanet_ecommerce_goods_category')->field('id,name')->where( 'id in( ' . $id . ' )' )->select();

		foreach ($items as $item) {
			M('eaterplanet_ecommerce_goods_category')->where( array('id' => $item['id']) )->save( array('is_express_show' => intval($_GPC['enabled']))  );
			D('Seller/Operatelog')->addOperateLog('goods','修改分类网商模式商品页显示--'.$item['name']);
		}
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}


	public function mult_tpl()
	{

		$tpl = I('get.tpl','','trim');
		$spec_str = I('post.spec_str', '', 'trim');
		$options_name = I('post.options_name','','trim');
		$cur_cate_id = I('post.cur_cate_id',0);


		if ($tpl == 'spec') {
			$spec = array('id' => random(32), 'title' => $options_name);

			$need_items = array();
			$spec_list = explode('@', $spec_str);
			foreach($spec_list as $itemname)
			{
				$tmp_item = array('id' =>random(32),'title' => $itemname, 'show' => 1);
				$need_items[] = $tmp_item;
			}
			$spec['items'] = $need_items;

			$this->spec = $spec;
			$this->tmp_item = $tmp_item;

			$this->tpl = $tpl;
			$this->spec_str = $spec_str;
			$this->options_name = $options_name;
			$this->cur_cate_id = $cur_cate_id;

			$this->display('Goods/tpl/spec');
		}
	}
	public function ajax_batchtime_pintuan()
	{
		$begin_time = I('request.begin_time');
		$goodsids = I('request.goodsids');
		$end_time = I('request.end_time');

		foreach ($goodsids as $goods_id ) {
			if($begin_time && $end_time){
				$param = array();
				$param['begin_time'] = strtotime($begin_time);
				$param['end_time'] = strtotime($end_time);

				M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $goods_id) )->save( $param );

				M('eaterplanet_ecommerce_good_pin')->where( array('goods_id' => $goods_id) )->save( $param );
			}
		}
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function ajax_batchtime()
	{

		$begin_time = I('request.begin_time');
		$goodsids = I('request.goodsids');
		$end_time = I('request.end_time');

		foreach ($goodsids as $goods_id ) {
			if($begin_time && $end_time){
				$param = array();
				$param['begin_time'] = strtotime($begin_time);
				$param['end_time'] = strtotime($end_time);

				M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $goods_id) )->save( $param );
			}
		}
		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function tpl()
	{

		$tpl = I('get.tpl');
		$title = I('get.title','');

		 if ($tpl == 'spec') {
			$spec = array('id' => random(32), 'title' => $title);

			$this->title = $title;
			$this->spec = $spec;
			$this->display('Goods/tpl/spec');
		}else  if($tpl == 'specitem')
		{
			$specid = I('get.specid');
			$spec = array('id' => $specid);
			$specitem = array('id' => random(32), 'title' => $title, 'show' => 1);

			$this->specid = $specid;
			$this->spec = $spec;
			$this->specitem = $specitem;

			$this->display('Goods/tpl/spec_item');
		}


	}


	public function config()
	{

		if (IS_POST) {

			$data = I('post.parameter', array());
			$data['goods_stock_notice'] = trim($data['goods_stock_notice']);
			$data['instructions'] = trim($data['instructions']);
			$data['is_show_buy_record'] = trim($data['is_show_buy_record']);
			$data['is_show_list_timer'] = intval($data['is_show_list_timer']);
			$data['is_show_list_count'] = intval($data['is_show_list_count']);
			$data['is_show_comment_list'] = intval($data['is_show_comment_list']);
			$data['is_show_new_buy'] = intval($data['is_show_new_buy']);
			$data['is_new_buy_limit'] = intval($data['is_new_buy_limit']);
			$new_buy_limit_num = !empty(intval($data['new_buy_limit_num'])) ? $data['new_buy_limit_num'] : 1;
			$data['new_buy_limit_num'] = intval($new_buy_limit_num);
			$data['is_show_ziti_time'] = intval($data['is_show_ziti_time']);


			$data['is_show_spike_buy'] = intval($data['is_show_spike_buy']);
			$data['goodsdetails_addcart_bg_color'] = $data['goodsdetails_addcart_bg_color'];
			$data['goodsdetails_buy_bg_color'] = $data['goodsdetails_buy_bg_color'];
			$data['is_show_guess_like'] = $data['is_show_guess_like'];

			$data['show_goods_guess_like'] = $data['show_goods_guess_like'];
			if(!empty($data['num_guess_like'])){
				$data['num_guess_like'] = $data['num_guess_like'];
			}else{
				$data['num_guess_like'] = 8;
			}



			$data['isopen_community_group_share'] = intval($data['isopen_community_group_share']);

			$data['group_share_avatar'] = save_media($data['group_share_avatar']);
			$data['group_share_title'] = trim($data['group_share_title']);
			$data['group_share_desc'] = trim($data['group_share_desc']);
			$data['is_close_details_time'] = intval($data['is_close_details_time']);

			$data['videolist_nav_title'] = trim($data['videolist_nav_title']);
			$data['videolist_share_title'] = trim($data['videolist_share_title']);
			$data['videolist_share_poster'] = save_media($data['videolist_share_poster']);

			$data['goods_details_title_bg'] = save_media($data['goods_details_title_bg']);

			D('Seller/Config')->update($data);
			D('Seller/Operatelog')->addOperateLog('goods','修改商品设置--基本设置');
			//旧的的域名
			$present_realm_name = I('post.present_realm_name');
			//修改商品详情域名
			$new_realm_name = I('post.new_realm_name');

			if(!empty($new_realm_name)){

				$str="/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
				if (!preg_match($str,$present_realm_name)){
					show_json(0, array('message' => '旧的域名格式不正确'));
				}

				if (!preg_match($str,$new_realm_name)){
					show_json(0, array('message' => '新的域名格式不正确'));
				}
				$sql = " update ". C('DB_PREFIX') ."eaterplanet_ecommerce_good_common set content = replace( content , '".$present_realm_name."' , '".$new_realm_name."' ) ";
				$list = M()->execute($sql);
				if(empty($list)){
					show_json(0, array('message' => '商品详情中不存在该域名，或者不能填写相同的域名，请检查后重新填写'));
				}else{
					D('Seller/Operatelog')->addOperateLog('goods','替换商品详情域名--原域名：'.$present_realm_name.'新域名：'.$new_realm_name );
				}
			}

			show_json(1, array('url'=> U('goods/config')));
		}
		$data = D('Seller/Config')->get_all_config();
		$this->data = $data;
		$this->display();
	}

	function addspec()
	{
		global $_W;
		global $_GPC;

		if (IS_POST) {

			$data = I('post.data');

			D('Seller/Spec')->update($data);

			show_json(1, array('url' => U('goods/goodsspec')));
		}

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

			D('Seller/Spec')->update($data);

			show_json(1, array('url' => U('goods/goodsspec')));
		}
		$this->item = $item;
      $this->labelname = $labelname;
		$this->display('Goods/addspec');
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
			D('Seller/Operatelog')->addOperateLog('goods','删除商品规格--'.$item['name']);
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

	}

	public function addcategory()
	{

		$data = array();
		$pid = I('get.pid', 0);
		$id = I('get.id', 0);

		if (IS_POST) {

			$data = I('post.data');
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
			$where['cate_type'] = 'normal';
			$cate_info = M('eaterplanet_ecommerce_goods_category')->where($where)->find();

			if(!empty($cate_info) && (($id == 0) || ($id > 0  && $id != $cate_info['id']))){
				show_json(0, array('message' => '分类排序已存在，请重新填写'));
			}

			D('Seller/GoodsCategory')->update($data);

			show_json(1, array('url' => U('goods/goodscategory')));
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
			$where['cate_type'] = 'normal';
			$where['id'] = array('neq',$cate_id);
			$cate_info = M('eaterplanet_ecommerce_goods_category')->where($where)->find();
			if(!empty($cate_info)){
				show_json(0, array('message' => '分类排序已存在，请重新填写'));
			}
			M('eaterplanet_ecommerce_goods_category')->where( array('id' => $cate_id) )->save(array('sort_order'=>$sort_order));
			D('Seller/Operatelog')->addOperateLog('goods','修改商品分类排序--'.$item['name']);
			show_json(1, array('url' => U('goods/goodscategory')));
		}else{
			show_json(0, array('message' => '分类信息错误'));
		}
	}

	public function category_delete()
	{

		$id = I('get.id');

		$item = M('eaterplanet_ecommerce_goods_category')->field('id, name, pid')->where( array('id' => $id) )->find();


		M('eaterplanet_ecommerce_goods_category')->where( "id={$id} or pid={$id}" )->delete();
		D('Seller/Operatelog')->addOperateLog('goods','删除商品分类--'.$item['name']);

		//m('shop')->getCategory(true);
		show_json(1, array('url' => U('goods/goodscategory')));
	}

	public function goodscategory()
	{

		if (IS_POST) {
			$datas = I('post.datas');
			if (!empty($datas)) {
				D('Seller/GoodsCategory')->goodscategory_modify($datas);
				show_json(1 , array('url' => U('goods/goodscategory') ));
			}

			$parameter = I('post.parameter');

			if (!empty($parameter)) {
				$data = ((is_array($parameter) ? $parameter : array()));
				D('Seller/Config')->update($data);
				show_json(1);
			}
		}

		$children = array();


		$category = M('eaterplanet_ecommerce_goods_category')->where(' cate_type="normal" ')->order('pid ASC, sort_order DESC')->select();


		foreach ($category as $index => $row) {
			if (!empty($row['pid'])) {
				$children[$row['pid']][] = $row;
				unset($category[$index]);
			}
		}


		$data = D('Seller/Config')->get_all_config();

		$this->data = $data;

		$this->children = $children;
		$this->category = $category;
		$this->display();
	}

	public function goodsspec()
	{
	    $condition = ' 1=1 and spec_type="normal" ';
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

	    $this->display();
	}
	/**
		搜索全部商品，可添加虚拟评价
	**/
	public function goods_search_all()
	{
		$goods_name = I('post.goods_name','');

	    $where = "   status=1 and quantity>0 and store_id = " . $this->sellerid;
	    if(!empty($goods_name))
	    {
	        $where .=  "  and name like '%".$goods_name."%' ";
	    }


	    $goods_list = M('goods')->where($where)->limit(20)->select();

	    $this->goods_list = $goods_list;
	    $result = array();

		$result['html'] = $this->fetch('Goods:goods_list_fetch');


	    echo json_encode($result);
	    die();
	}

	function toggle_statues_show()	{
		$goods_id = I('post.gid',0);
		$goods_info =M('Goods')->where( array('goods_id' => $goods_id) )->find();
		$status = $goods_info['status'] == 1 ? 0: 1;
		$res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('status' => $status) );
		echo json_encode( array('code' => 1) );
		die();
	}

	/**
	 搜索可报名的商品
	 **/
	public function goods_search_voucher()
	{
		$goods_name = I('post.goods_name','');

	    $where = "  (type='normal' or type ='pintuan')  and status=1 and quantity>0 and store_id = " . $this->sellerid;
	    if(!empty($goods_name))
	    {
	        $where .=  "  and ( name like '%".$goods_name."%'  or goods_id like '%".$goods_name."%') ";
	    }

	    $goods_list = M('goods')->where($where)->limit(10)->select();

	    $this->goods_list = $goods_list;
	    $result = array();

		$result['html'] = $this->fetch('Goods:goods_list_fetch');


	    echo json_encode($result);
	    die();
	}
	/**
	 搜索可报名的商品
	 **/
	public function goods_search()
	{

	    $goods_name = I('post.goods_name','');

	    $where = "  type='normal'  and status=1 and quantity>0 and store_id = " . $this->sellerid;
	    if(!empty($goods_name))
	    {
	        $where .=  "  and name like '%".$goods_name."%' ";
	    }


	    $goods_list = M('goods')->where($where)->limit(20)->select();

	    $this->goods_list = $goods_list;
	    $result = array();

		$result['html'] = $this->fetch('Goods:goods_list_fetch');


	    echo json_encode($result);
	    die();
	}

	public function query_normal()
	{
		$_GPC = I('request.');
		$kwd = trim($_GPC['keyword']);
		$is_recipe = isset($_GPC['is_recipe']) ? intval($_GPC['is_recipe']) : 0 ;

		$is_soli = isset($_GPC['is_soli']) ? intval($_GPC['is_soli']) : 0 ;


		$params = array();


		$type = isset($_GPC['type']) ? $_GPC['type']:'normal';

		$condition = '  type = "'.$type.'" and grounding = 1 and is_seckill =0 ';


		if (!empty($kwd)) {
			$condition .= ' AND `goodsname` LIKE "%' . $kwd . '%" ';
		}

		if( isset($_GPC['unselect_goodsid']) && $_GPC['unselect_goodsid'] > 0 )
		{
			$condition .= ' AND `id` != '.$_GPC['unselect_goodsid'];
		}

		if( $is_soli == 1 )
		{
			$head_id = $_GPC['head_id'];

			$goods_ids_arr = M('eaterplanet_community_head_goods')->field('goods_id')->where(  "head_id in ({$head_id})" )->order('id desc')->select();

			$ids_arr = array();
			foreach($goods_ids_arr as $val){
				$ids_arr[] = $val['goods_id'];
			}
			if( !empty($ids_arr) )
			{
				$ids_str = implode(',',$ids_arr);

				$condition .= "  and ( is_all_sale = 1 or id in ({$ids_str}) )   ";
			}else{
				$condition .= "  and ( is_all_sale = 1  )  ";
			}
			//is_all_sale
		}
		//todo....
		$time = time();
		$ds = M('eaterplanet_ecommerce_goods')->field('id as gid,goodsname,price,productprice,total')->where( $condition )->select();
		$s_html = "";
		foreach ($ds as &$d) {
			//thumb
			$thumb = M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' =>$d['gid'] ) )->order('id asc')->find();
			$d['thumb'] =  tomedia($thumb['image']);

			$goods_common_info = M('eaterplanet_ecommerce_good_common')->field("begin_time,end_time")->where( array('goods_id' =>$d['gid'] ) )->order('id asc')->find();
			if($time >= $goods_common_info['begin_time'] && $time <= $goods_common_info['end_time']){
				$d['is_finished'] = 0;
			}else{
				$d['is_finished'] = 1;//已结束
			}

			if($d['total'] <= 0){
				$d['is_grab'] = 1;//已抢光
			}else{
				$d['is_grab'] = 0;
			}

			$s_html.= '<tr>';
			$s_html.="  <td><img src='".tomedia($d['thumb'])."' class=\"img-responsive img-thumbnail\" style=\"width:40px;height:40px;\" /> ".$d['goodsname']."</td>";


			if (  isset($_GPC['template'])  && $_GPC['template'] == 'mult' ) {
				if( $is_recipe == 1 )
				{
					$s_html.='  <td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link_recipe btn-primary btn-sm" data-json=\''.json_encode($d).'\'>选择</a></td>';
				}else{
					$s_html.='  <td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link_goods btn-primary btn-sm" data-json=\''.json_encode($d).'\'>选择</a></td>';
				}

			}else{
				$s_html.='  <td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link btn-primary btn-sm" data-json=\''.json_encode($d).'\'>选择</a></td>';
			}



			$s_html.="</tr>";
		}

		unset($d);


		if( isset($_GPC['is_ajax']) )
		{
			echo json_encode( array('code' => 0, 'html' =>$s_html ) );
			die();
		}

		$this->ds = $ds;
		$this->_GPC = $_GPC;

		if (  isset($_GPC['template'])  && $_GPC['template'] == 'mult' ) {

			if( $is_recipe == 1 )
			{
				$this->display('Goods/query_normal_mult_recipe');
			}else{
				$this->display('Goods/query_normal_mult');
			}
		}else{
			$this->display();
		}

	}

	/**
	 * 获取商品规格情况
	 */

	function get_ajax_search_goods_info()
	{
	    $goods_id = I('get.goods_id');
		$is_hide = I('get.is_hide',0);
		$type = I('get.type','pin');
		//'type' => 'bargain'

	     $this->is_hide = $is_hide;
	    $goods_info = M('goods')->field('name,goods_id,price,danprice')->where( array('goods_id' => $goods_id) )->find();

	    $model=new GoodsModel();
	    $this->goods_options=$model->get_goods_options($goods_id, UID);

	    $goods_option_mult_value = M('goods_option_mult_value')->where( array('goods_id' => $goods_id ) )->select();
	    $goods_option_mult_str = '';

	    if( !empty($goods_option_mult_value) )
	    {
	        $goods_option_mult_arr = array();
	        foreach($goods_option_mult_value as $key => $val)
	        {
	            $goods_option_mult_arr[] = 'mult_id:'.$val['rela_goodsoption_valueid'].'@@mult_qu:'.$val['quantity'].'@@mult_image:'.$val['image'];
	            //option_value  option_value_id  value_name
	            $option_name_arr = explode('_', $val['rela_goodsoption_valueid']);
	            $option_name_list = array();


	            foreach($option_name_arr as $option_value_id_tp)
	            {
	                $tp_op_val_info =M('option_value')->where( array('option_value_id' => $option_value_id_tp) )->find();
	                $option_name_list[] = $tp_op_val_info['value_name'];
	            }

	            $val['option_name_list'] = $option_name_list;
	            $goods_option_mult_value[$key] = $val;
	        }
	        $goods_option_mult_str = implode(',', $goods_option_mult_arr);
	    }

	    $this->goods_option_mult_value = $goods_option_mult_value;
	    $this->goods_option_mult_str = $goods_option_mult_str;
	    $this->goods_info = $goods_info;

	    $result = array();
		if($type == 'bargain')
		{
			$result['html'] = $this->fetch('Goods:goods_option_fetch_bargain');
		}
		else if($type == 'integral'){
			$result['html'] = $this->fetch('Goods:goods_option_fetch_integral');
		}else{
			$result['html'] = $this->fetch('Goods:goods_option_fetch');
		}

	    echo json_encode($result);
	    die();
	}
	function toggle_index_sort()
	{
	    $goods_id = I('post.gid',0);
	    $index_sort = I('post.index_sort',0,'intval');
	    $res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('index_sort' => $index_sort) );
	    echo json_encode( array('code' => 1) );
	    die();
	}
	function toggle_index_show()
    {
        $goods_id = I('post.gid',0);
        $goods_info =M('Goods')->where( array('goods_id' => $goods_id) )->find();
        $is_index_show = $goods_info['is_index_show'] == 1 ? 0: 1;

        $res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('is_index_show' => $is_index_show) );
        echo json_encode( array('code' => 1) );
        die();
    }
	/**
	 * 活动商品
	 */
	public function activity()
	{
	    $this->breadcrumb2='活动商品信息';

	    $model=new GoodsModel();

	    $filter=I('get.');


	    $search=array('store_id' => SELLERUID);

	    if(isset($filter['name'])){
	        $search['name']=$filter['name'];
	    }
	    if(isset($filter['category'])){
	        $search['category']=$filter['category'];
	        $this->get_category=$search['category'];
	    }
	    if(isset($filter['status'])){
	        $search['status']=$filter['status'];
	        $this->get_status=$search['status'];
	    }

	    if(isset($filter['type'])){
	        $search['type']=$filter['type'];
	        $this->type=$search['type'];
	    }else {
	        $search['type']='activity';
	        $this->type=$search['type'];
	    }
	    //type

	    $data=$model->show_goods_page($search);

	    $store_bind_class = M('store_bind_class')->where( array('seller_id' => SELLERUID) )->select();

	    $cate_ids = array();
	    foreach($store_bind_class as $val)
	    {
	        if( !empty($val['class_1'])) {
	            $cate_ids[] = $val['class_1'];
	        }
	        if( !empty($val['class_2'])) {
	            $cate_ids[] = $val['class_2'];
	        }
	        if( !empty($val['class_3'])) {
	            $cate_ids[] = $val['class_3'];
	        }
	    }
	    if(empty($cate_ids)) {
	        $this->category = array();
	    } else {
	        $cate_ids_str = implode(',', $cate_ids);
	        $category=M('goods_category')->where( array('id' => array('in',$cate_ids_str)) )->select();
	        $category_tree =list_to_tree($category);
	        $this->category = $category_tree;
	    }

	    foreach($data['list'] as $key => $goods)
	    {
	        $all_comment  =  M('order_comment')->where( array('goods_id' => $goods['goods_id']) )->count();
	        $wait_comment =  M('order_comment')->where( array('state' => 0 ,'goods_id' => $goods['goods_id']) )->count();
	        $goods['all_comment']  = $all_comment;
	        $goods['wait_comment'] = $wait_comment;
	        $data['list'][$key] = $goods;
	    }

	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出

	    $this->display();
	}

	/**
		回收站商品重新上架
	**/
	public function goback()
	{
		$goods_id = I('get.id',0,'intval');
		$result = array('code' => 0);
		$goods_info = M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->find();
		if(empty($goods_info))
		{
			$result['msg'] = '非法操作';
			echo json_encode($result);
			die();
		}


		$up_data = array();
		$up_data['lock_type'] = 'normal';
		$up_data['status'] = 2;//下架

		M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->save($up_data);

		$result['code'] = 1;
		echo json_encode($result);
		die();
	}

	public function get_weshare_image()
	{
		$goods_id = I('get.id',0,'intval');

		//400*400 fan_image
		//get_goods_price($goods_id)
		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

		$goods_img = ROOT_PATH.'Uploads/image/'.$goods_info['image'];
		if( !empty($goods_info['fan_image']) )
		{
			$goods_img = ROOT_PATH.'Uploads/image/'.$goods_info['fan_image'];
		}
		$goods_model = D('Home/Goods');
		$goods_price = $goods_model->get_goods_price($goods_id);
		$goods_price['market_price'] = $goods_info['price'];
		//price
		$goods_title = $goods_info['name'];


		$need_img = $goods_model->_get_compare_zan_img($goods_img,$goods_title,$goods_price);

		//贴上二维码图
		//$rocede_path = $goods_model->_get_goods_user_wxqrcode($goods_id,$member_id);
		//$res = $goods_model->_get_compare_qrcode_bgimg($need_img['need_path'], $rocede_path);

		M('goods_description')->where( array('goods_id' =>$goods_id) )->save( array('wepro_qrcode_image' =>$need_img['need_path']) );

		echo json_encode(array('code' =>1));
		die();
	}

	/**
	加入回车站
	**/
	public function backhuiche()
	{
		$goods_id = I('get.id',0,'intval');
		$result = array('code' => 0);
		$goods_info = M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->find();
		if(empty($goods_info))
		{
			$result['msg'] = '非法操作';
			echo json_encode($result);
			die();
		}
		$lock_type = $goods_info['lock_type'];

		switch($lock_type)
		{
			case 'lottery':
				M('lottery_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
			case 'super_spike':
				M('super_spike_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
			case 'spike':
				M('spike_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
			case 'subject':
			case 'free_trial':
			case 'niyuan':
			case 'oneyuan':
			case 'haitao':
				M('subject_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
		}

		$up_data = array();
		$up_data['type'] = 'normal';
		$up_data['lock_type'] = 'normal';
		$up_data['status'] = 4;//下架

		M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->save($up_data);

		$result['code'] = 1;
		echo json_encode($result);
		die();
	}


	/**
		撤回活动申请
	**/
	public function backshenqing()
	{
		$goods_id = I('get.id',0,'intval');
		$result = array('code' => 0);
		$goods_info = M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->find();
		if(empty($goods_info))
		{
			$result['msg'] = '非法操作';
			echo json_encode($result);
			die();
		}
		$lock_type = $goods_info['lock_type'];

		switch($lock_type)
		{
			case 'lottery':
				M('lottery_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
			case 'super_spike':
				M('super_spike_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
			case 'spike':
				M('spike_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
			case 'subject':
			case 'free_trial':
			case 'niyuan':
			case 'oneyuan':
			case 'haitao':
				M('subject_goods')->where( array('goods_id' => $goods_id) )->delete();
				break;
		}

		$up_data = array();
		$up_data['lock_type'] = 'normal';
		$up_data['status'] = 0;//下架

		M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->save($up_data);

		$result['code'] = 1;
		echo json_encode($result);
		die();
	}

	///Goods/delcomment/id/1
	/**
	 * 删除评论
	 */
	public function delcomment()
	{
	    $id = I('get.id');
	    $goods_id = I('get.goods_id');
	    M('order_comment')->where( array('comment_id' => $id) )->delete();
	    //echo
	    $result = array(
	        'status'=>'success',
	        'message'=>'删除成功',
	        'jump'=>U('Goods/comment_info', array('id' =>  $goods_id))
	    );
	    $this->osc_alert($result);
	}
	/**
	 * 审核评论
	 */
	public function toggle_comment_state()
	{
	    $comment_id = I('post.comment_id');
	    $order_comment = M('order_comment')->where( array('comment_id' => $comment_id) )->find();
	    //state
	    $state = $order_comment['state'] == 1 ? 0: 1;
	    M('order_comment')->where( array('comment_id' => $comment_id) )->save( array('state' => $state) );
	   echo json_encode( array('code' => 1) );
	   die();
	}
	/**
	 * 商品评论信息
	 */
	public function comment_info()
	{
	    $goods_id = I('get.id');
	    $model=new GoodsModel();
	    $search = array();
	    $search['goods_id'] = $goods_id;
	    $data=$model->show_comment_page($search);


	    $this->assign('empty',$data['empty']);// 赋值数据集
	    $this->assign('list',$data['list']);// 赋值数据集
	    $this->assign('page',$data['page']);// 赋值分页输出
	    $this->display();
	}

	public function ajax_batchheads()
	{

		$goodsids = I('request.goodsids');
		$head_id_arr = I('request.head_id_arr');


		$is_clear_old = I('request.is_clear_old');

		$is_cancle_allhead = I('request.is_cancle_allhead');


		if( $is_clear_old == 1 )
		{
			foreach($goodsids as $goods_id)
			{
				M('eaterplanet_community_head_goods')->where( array('goods_id' => $goods_id) )->delete();
			}
		}

		if( isset($is_cancle_allhead) && $is_cancle_allhead == 1 )
		{
			foreach($goodsids as $goods_id)
			{
				M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id) )->save( array('is_all_sale' => 0) );
			}
		}


		foreach($head_id_arr as $head_id)
		{
			foreach($goodsids as $goods_id)
			{
				D('Seller/Communityhead')->insert_head_goods($goods_id, $head_id);
			}
		}
		show_json(1);
	}


	public function ajax_batchcates_headgroup()
	{
		$_GPC = I('request.');

		$goodsids = $_GPC['goodsids'];
		$groupid = $_GPC['groupid'];

		if( $groupid == 'default')
		{
			$groupid = 0;
		}


		$head_list = M('eaterplanet_community_head')->field('id')->where( array("groupid" => $groupid, 'state' => 1 ) )->select();

		$is_clear_old = $_GPC['is_clear_old'];
		$is_cancle_allhead = isset($_GPC['is_cancle_allhead']) ? $_GPC['is_cancle_allhead'] : 0;

		if($is_cancle_allhead == 1)
		{
			foreach($goodsids as $goods_id)
			{
				M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id) )->save( array('is_all_sale' => 0) );
			}
		}

		if( $is_clear_old == 1 )
		{
			foreach($goodsids as $goods_id)
			{

				M('eaterplanet_community_head_goods')->where( array('goods_id' => $goods_id ) )->delete();
			}
		}



		if( !empty($head_list) )
		{
			foreach($head_list as $val)
			{
				foreach($goodsids as $goods_id)
				{
					D('Seller/Communityhead')->insert_head_goods($goods_id, $val['id']);
				}
			}
		}

		show_json(1);
	}


	public function ajax_batchcates()
	{

		$iscover =  I('request.iscover');
		$goodsids = I('request.goodsids');
		$cates = I('request.cates');

		if( !is_array($cates) )
		{
			$cates = array($cates);
		}

		foreach ($goodsids as $goods_id ) {

			if( $iscover == 1)
			{
				//覆盖，即删除原有的分类

				M('eaterplanet_ecommerce_goods_to_category')->where( array('goods_id' => $goods_id) )->delete();

				foreach($cates as $cate_id)
				{
					$post_data_cate = array();
					$post_data_cate['cate_id'] = $cate_id;
					$post_data_cate['goods_id'] = $goods_id;
					M('eaterplanet_ecommerce_goods_to_category')->add($post_data_cate);
				}
			}else{
				foreach($cates as $cate_id)
				{
					//仅更新

					$item = M('eaterplanet_ecommerce_goods_to_category')->where( array('goods_id' => $goods_id,'cate_id' => $cate_id) )->find();

					if(empty($item))
					{
						$post_data_cate = array();
						$post_data_cate['cate_id'] = $cate_id;
						$post_data_cate['goods_id'] = $goods_id;
						M('eaterplanet_ecommerce_goods_to_category')->add($post_data_cate);
					}
				}

			}
		}
		show_json(1);
	}

	public function lotteryinfo()
	{
	    $goods_id = I('get.id',0);
	    $lottery_goods = M('lottery_goods')->where( array('goods_id' =>$goods_id) )->find();

	    if(empty($lottery_goods)){
	        die('非法操作');
	    }//store_id
	    $page = I('get.page',1);
	    $per_page = 4;
	    $offset = ($page - 1) * $per_page;

	    $sql = "select m.uname,m.avatar,p.pin_id,p.lottery_state,o.lottery_win,o.order_id,o.pay_time from ".C('DB_PREFIX')."pin as p,".C('DB_PREFIX')."pin_order as po,
	           ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og,".C('DB_PREFIX')."member as m
	               where p.state = 1 and p.pin_id = po.pin_id and po.order_id = o.order_id
	                and o.order_id = og.order_id and og.goods_id and o.member_id = m.member_id and og.store_id =".SELLERUID." and og.goods_id = {$goods_id}
	                    and o.date_added >= ".$lottery_goods['begin_time']."   order by p.pin_id asc limit {$offset},{$per_page}";

		//begin_time date_added

	    $list=M()->query($sql);
	    $this->list = $list;
	    $this->goods_id = $goods_id;
	    $this->lottery_goods = $lottery_goods;

	    if($page>1){
	        $result = array();
	        $result['code'] = 0;
	        if(!empty($list)) {
	            $content = $this->fetch('Goods:lottery_info_fetch');
	            $result['code'] = 1;
	            $result['html'] = $content;
	        }
	       echo json_encode($result);
	       die();
	    }

	    $this->display();
	}

	public function openlottery()
	{
	    $goods_id = I('get.id',0);
	    $oids = I('post.oids');
	    $order_model = D('Home/Order');

	    $order_model->open_goods_lottery_order($goods_id,$oids,false);

	    //$order_model->open_goods_lottery_order($goods_id,'',true);
	    //$map['id'] = array('in','1,3,8')

	    echo json_encode( array('code' => 1) );
	    die();
	}

	public function lottery_shenqing()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        $spike_data = array();
	        $spike_data['goods_id'] = $goods_id;
	        $spike_data['state'] = 0;
	        $spike_data['quantity'] = $goods_info['quantity'];
	        $spike_data['begin_time'] = 0;
	        $spike_data['end_time'] = 0;
	        $spike_data['addtime'] = time();
	        $rs = M('lottery_goods')->add($spike_data);
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'lottery') );
	        }
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else{
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}

	public function xianshimiaosha_shenqing()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }
	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        $spike_data = array();
	        $spike_data['goods_id'] = $goods_id;
	        $spike_data['state'] = 0;
	        $spike_data['quantity'] = $goods_info['quantity'];
	        $spike_data['begin_time'] = 0;
	        $spike_data['end_time'] = 0;
	        $spike_data['addtime'] = time();
	        $rs = M('spike_goods')->add($spike_data);
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'spike') );
	        }
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else{
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }

	}

	public function spike_sub()
	{
	    $spike_id = I('post.spike',0);
	    $goods_id = I('post.goods_id',0);

	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    $spike_info = M('spike')->where( array('id' => $spike_id) )->find();

	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        $super_data  = array();
	        $super_data['spike_id'] = $spike_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	        $super_data['begin_time'] = $spike_info['begin_time'];
	        $super_data['end_time'] = $spike_info['end_time'];
	        $super_data['addtime'] = time();

	        $rs = M('spike_goods')->add($super_data);

	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'spike') );
	        }

	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}

	public function chaozhidapai_sub()
	{
		$super_spike_id = I('post.super_spike',0);
		$goods_id = I('post.goods_id',0);

		if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

		$goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

		if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	    	$super_data  = array();
	    	$super_data['super_spike_id'] = $super_spike_id;
	    	$super_data['goods_id'] = $goods_id;
	    	$super_data['state'] = 0;
	    	$super_data['begin_time'] = 0;
	    	$super_data['end_time'] = 0;
			$super_data['addtime'] = time();

			$rs = M('super_spike_goods')->add($super_data);

			if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'super_spike') );
	        }

			$result['code'] = 1;
	    	echo json_encode($result);
	    	die();
	    } else {
	    	 $result['msg'] = '已存在其他活动中';
	         echo json_encode($result);
	         die();
	    }
	}
	public function oneyuansubject_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);

	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;

	        $super_data['addtime'] = time();

	        $rs = M('subject_goods')->add($super_data);

	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'oneyuan') );
	        }

	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function niyuansubject_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);

	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;

	        $super_data['addtime'] = time();

	        $rs = M('subject_goods')->add($super_data);

	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'niyuan') );
	        }

	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function mianfei_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);

	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;

	        $super_data['addtime'] = time();

	        $rs = M('subject_goods')->add($super_data);

	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'zeyuan') );
	        }

	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function putongsubject_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);

	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	        $super_data['addtime'] = time();

	        $rs = M('subject_goods')->add($super_data);

	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'subject') );
	        }

	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}

	public function haitaosubject_sub()
	{
	    $subject_id = I('post.subject',0);
	    $goods_id = I('post.goods_id',0);

	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $super_data  = array();
	        $super_data['subject_id'] = $subject_id;
	        $super_data['goods_id'] = $goods_id;
	        $super_data['state'] = 0;
	        $super_data['addtime'] = time();

	        $rs = M('subject_goods')->add($super_data);

	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'haitao') );
	        }

	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function yiyuan_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $subject = M('subject')->where('can_shenqing=1 and type="oneyuan"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;

	        $content = $this->fetch('Goods:goods_oneyuansubject_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function jiukuaijiu_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $subject = M('subject')->where('can_shenqing=1 and type="niyuan"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;

	        $content = $this->fetch('Goods:goods_niyuansubject_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}

	public function lottery_form()
	{
		$result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $voucher_list = M('voucher')->where( "store_id=".SELLERUID." and begin_time>".time() )->select();
	        $this->voucher_list = $voucher_list;
	        $this->goods_id = $goods_id;

	        $content = $this->fetch('Goods:goods_lottery_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}

	public function lottery_sub()
	{
		$voucher_id = I('post.voucher_id',0);
	    $goods_id = I('post.goods_id',0);
	    $win_quantity = I('post.win_quantity',0);
	    $is_auto_open = I('post.is_auto_open',0);
	    $real_win_quantity = I('post.real_win_quantity',0);

	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

		if($voucher_id == 0){
	        $result['msg'] = '请选择退款时赠送的优惠券';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $spike_data = array();
	        $spike_data['goods_id'] = $goods_id;
	        $spike_data['state'] = 0;
	        $spike_data['is_open_lottery'] = 0;
	        $spike_data['voucher_id'] = $voucher_id;
	        $spike_data['win_quantity'] = $win_quantity;
	        $spike_data['is_auto_open'] = $is_auto_open;
	        $spike_data['real_win_quantity'] = $real_win_quantity;
	        $spike_data['quantity'] = $goods_info['quantity'];
	        $spike_data['begin_time'] = 0;
	        $spike_data['end_time'] = 0;
	        $spike_data['addtime'] = time();
	        $rs = M('lottery_goods')->add($spike_data);
	        if($rs) {
	            M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'lottery') );
	        }
	        $result['code'] = 1;
	        echo json_encode($result);
	        die();
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }

	}
	public function putongsubject_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $subject = M('subject')->where('can_shenqing=1 and type="normal"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;

	        $content = $this->fetch('Goods:goods_putongsubject_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function haitaosubject_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $subject = M('subject')->where('can_shenqing=1 and type="haitao"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;

	        $content = $this->fetch('Goods:goods_haitaosubject_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function mianfeishiyong_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {

	        $subject = M('subject')->where('can_shenqing=1 and type="zeyuan"')->select();
	        $this->subject = $subject;
	        $this->goods_id = $goods_id;

	        $content = $this->fetch('Goods:goods_mianfeishiyong_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }
	}
	public function chaozhidapai_form()
	{
		$result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
		if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	    	$super_spike_list = M('super_spike')->where('begin_time>'.time())->select();
	    	$this->super_spike_list = $super_spike_list;
	    	$this->goods_id = $goods_id;

	    	$content = $this->fetch('Goods:goods_chaozhidapai_fetch');
	    	$result['code'] = 1;
	    	$result['html'] = $content;
	    	echo json_encode($result);
	    	die();
	    } else {
	    	 $result['msg'] = '已存在其他活动中';
	         echo json_encode($result);
	         die();
	    }

	}
	public function spike_form()
	{
	    $result = array('code' => 0);
	    $goods_id = I('post.goods_id',0);
	    if($goods_id == 0){
	        $result['msg'] = '商品不存在';
	        echo json_encode($result);
	        die();
	    }

	    $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
	    if($goods_info['type'] == 'normal' && !empty($goods_info)) {
	        $spike_list = M('spike')->where()->select();
	        //$spike_list = M('spike')->where('begin_time>'.time())->select();

	        $this->spike_list = $spike_list;
	        $this->goods_id = $goods_id;

	        $content = $this->fetch('Goods:goods_spike_fetch');
	        $result['code'] = 1;
	        $result['html'] = $content;
	        echo json_encode($result);
	        die();
	    } else {
	        $result['msg'] = '已存在其他活动中';
	        echo json_encode($result);
	        die();
	    }

	}

	public function get_json_category_tree($pid,$is_ajax=0)
	{
	   // {pid:pid,is_ajax:1}
	   $pid = empty($_GET['pid']) ? 0: intval($_GET['pid']);
	   $is_ajax = empty($_GET['is_ajax']) ? 0:intval($_GET['is_ajax']);
	   $goods_cate_model = D('Seller/GoodsCategory');
	   //$list = $goods_cate_model->get_parent_cateory($pid,SELLERUID);

	   $list = M('goods_category')->field('id,pid,name')->where( array('pid'=>$pid) )->order('sort_order asc')->select();

	   if($pid > 0)
	   {
		   $list = M('goods_category')->field('id,pid,name')->where( array('pid'=>$pid) )->order('sort_order asc')->select();
	   }


	   $result = array();
	   if($is_ajax ==0)
	   {
	       return $list;
	   } else {
	       if(empty($list)){
	           $result['code'] = 0;
	       } else {
	           $result['code'] = 1;
	           $result['list'] = $list;
	       }
	       echo json_encode($result);
	       die();
	   }

	}
	function add(){


	    $model=new GoodsModel();
		if(IS_POST){

			$data=I('post.');
			$data['goods_description']['tag'] = str_replace('，', ',', $data['goods_description']['tag']);

			$data['store_id']=SELLERUID;

			if($this->goods_is_shenhe()) {
				$data['status'] = 2;
			}

			$return=$model->add_goods($data);
			$this->osc_alert($return);
		}


		$m=new \Admin\Model\OptionModel();
			//getOptions
		$options_list = $m->getOptions('',SELLERUID);

		$this->options_list = $options_list;
		$pick_list =  M('pick_up')->where( array('store_id' => SELLERUID) )->select();

		$this->pick_list = $pick_list;

		$member_model= D('Admin/Member');
		 $level_list = $member_model->show_member_level();

		 $member_default_levelname_info = D('Home/Front')->get_config_by_name('member_default_levelname');

		 $member_defualt_discount_info = D('Home/Front')->get_config_by_name('member_defualt_discount');

		 $default = array('id'=>'default', 'level' => 0,'levelname' => $member_default_levelname_info,'discount' => $member_defualt_discount_info);

		 array_unshift($level_list['list'], $default );

		$need_level_list = $level_list['list'];

		$set = D('Seller/Config')->get_all_config();
		$this->set = $set;

		/***
			是否开启 分享等级佣金 begin
		**/

		$index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');

		if( empty($index_sort_method) || $index_sort_method == 0 )
		{
			$index_sort_method = 0;
		}
		$this->index_sort_method = $index_sort_method;

		$show_fissionsharing_level = 1;

		$is_open_sharing = D('Home/Front')->get_config_by_name('is_open_fissionsharing');
		$show_fissionsharing_level =  D('Home/Front')->get_config_by_name('show_fissionsharing_level');

		$this->show_fissionsharing_level = $show_fissionsharing_level;
		$this->is_open_sharing = $is_open_sharing;
		/***
			是否开启 分享等级佣金 end
		**/

		$this->member_level_is_open_info = D('Home/Front')->get_config_by_name('member_level_is_open');
		$this->need_level_list = $need_level_list;


		$this->cate_data = $this->get_json_category_tree(0);
		$this->action=U('Goods/add');
		$this->crumbs='新增';
		$this->display('edit');
	}

	/**
	商品是否需要审核
	**/
	function goods_is_shenhe()
	{
		$shenhegoods = M('config')->where( array('name' => 'shenhegoods') )->find();

		$is_need_shen = 0;

		if(!empty($shenhegoods)) {
			$is_need_shen = $shenhegoods['value'];
		}
		return $is_need_shen;
	}

	public function change()
	{

		$id = I('request.id',0);



		//ids
		if (empty($id)) {
			$ids = I('request.ids');

			$id = ((is_array($ids) ? implode(',', $ids) : 0));
		}



		if (empty($id)) {
			show_json(0, array('message' => '参数错误'));
		}


		$type = I('request.type');
		$value = I('request.value');

		//type/grounding/

		$is_can_do =  D('Seller/Supply')->checksupply_pri( $type );

		if( !$is_can_do )
		{
			show_json(0, array('message' => '无此操作权限','url' => $_SERVER['HTTP_REFERER'] ));
		}

		if (!(in_array($type, array('goodsname', 'price','index_sort','is_index_show', 'total','grounding', 'goodssn', 'productsn', 'displayorder')))) {
			show_json(0, array('message' => '参数错误','url' => $_SERVER['HTTP_REFERER'] ));
		}

		if($type =='total'){
			$value=str_replace('.00','',$value);
			if(preg_match("/^[1-9][0-9]*$/" ,$value) || $value == 0 ) {

			}else{
				show_json(3);

			}
		}

		$items = M('eaterplanet_ecommerce_goods')->field('id,goodsname')->where( array('id' => array('in', $id)) )->select();

		foreach ($items as $item ) {
			if($type == 'grounding' && $value == 1){//上架判断核销商品过期时间
				$goods_hexiao = M('eaterplanet_ecommerce_goods_salesroombase')->field('is_only_hexiao,hexiao_type,hx_expire_type,hx_expire_end_time')->where( array('goods_id' => $item['id']) )->find();
				if(!empty($goods_hexiao) && $goods_hexiao['is_only_hexiao'] == 1 && $goods_hexiao['hx_expire_type'] == 1){
					if($goods_hexiao['hx_expire_end_time'] < time()){
						show_json(0, array('message' => '核销过期时间小于当前时间，商品不能上架','url' => $_SERVER['HTTP_REFERER'] ));
					}
				}
			}

			M('eaterplanet_ecommerce_goods')->where( array('id' => $item['id']) )->save( array($type => $value) );

			if($type == 'goodsname'){
				D('Seller/Operatelog')->addOperateLog('goods','商品修改名称--'.$item['goodsname']);
			}elseif($type == 'price'){
				D('Seller/Operatelog')->addOperateLog('goods','商品修改价格--'.$item['goodsname']);
			}elseif($type == 'index_sort'){
				D('Seller/Operatelog')->addOperateLog('goods','商品修改排序序号--'.$item['goodsname']);
			}elseif($type == 'is_index_show'){
				D('Seller/Operatelog')->addOperateLog('goods','商品修改首页推荐--'.$item['goodsname']);
			}elseif($type == 'total'){
				D('Seller/Operatelog')->addOperateLog('goods','商品修改库存--'.$item['goodsname']);
			}elseif($type == 'grounding'){
				if($value == 1){
					if (defined('ROLE') && ROLE == 'agenter' )
					{
						$supply_add_goods_shenhe = D('Home/Front')->get_config_by_name('supply_add_goods_shenhe');
						if($supply_add_goods_shenhe == 1 )
						{
							M('eaterplanet_ecommerce_goods')->where( array('id' => $item['id']) )->save(  array('grounding' => 4) );
						}
					}
					//D('Seller/Operatelog')->addOperateLog('goods','商品上架--'.$item['goodsname']);
				}
				if($value == 0){
					D('Seller/Operatelog')->addOperateLog('goods','商品下架--'.$item['goodsname']);
				}
				if($value == 2){
					D('Seller/Operatelog')->addOperateLog('goods','商品放入仓库中--'.$item['goodsname']);
				}
				if($value == 3){
					D('Seller/Operatelog')->addOperateLog('goods','商品放入回收站--'.$item['goodsname']);
				}
			}elseif($type == 'goodssn'){

			}elseif($type == 'productsn'){

			}elseif($type == 'displayorder'){

			}

			if($type == 'total')
			{
				D('Seller/Redisorder')->sysnc_goods_total($item['id']);
			}
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function delete()
	{

		$id = I('get.id');

		//ids
		if (empty($id)) {
			$ids = I('post.ids');

			$id = ((is_array($ids) ? implode(',', $ids) : 0));
		}

		if (empty($id)) {
			show_json(0, array('message' => '参数错误'));
		}


		$items = M('eaterplanet_ecommerce_goods')->field('id,goodsname')->where( array('id' => array('in', $id)) )->select();

		foreach ($items as $item ) {
			//pdo_update('eaterplanet_ecommerce_goods', array($type => $value), array('id' => $item['id'])); //ims_eaterplanet_ecommerce_goods

			M('eaterplanet_ecommerce_goods')->where( array('id' => $item['id']) )->delete();
			M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $item['id']) )->delete();
			M('eaterplanet_ecommerce_goods_option')->where( array('goods_id' => $item['id']) )->delete();
			M('eaterplanet_ecommerce_goods_option_item')->where( array('goods_id' => $item['id']) )->delete();
			M('eaterplanet_ecommerce_goods_option_item_value')->where( array('goods_id' => $item['id']) )->delete();
			M('eaterplanet_ecommerce_goods_to_category')->where( array('goods_id' => $item['id']) )->delete();
			M('eaterplanet_ecommerce_good_common')->where( array('goods_id' => $item['id']) )->delete();
			D('Seller/Operatelog')->addOperateLog('goods','永久删除商品--'.$item['goodsname']);
		}
        sellerLog('删除了['.$items['goodsname'].']商品', 3);

        show_json(1);
	}

	function edit(){


		$id =  I('get.id');

		if (IS_POST) {
			$_GPC = I('post.');

			if( !isset($_GPC['thumbs']) || empty($_GPC['thumbs']) )
			{
				show_json(0,  array('message' => '商品图片必须上传' ,'url' => $_SERVER['HTTP_REFERER']) );
				die();
			}

			if(!empty($_GPC['has_mb_level_buy'])){

				foreach($_GPC['discount'] as $val)
					{
						if(preg_match("/^(([1-9]\d?)|100)$/", $val)){

						}else{
							show_json(0,  array('message' => '请按照提示设置独立客户等级折扣' ,'url' => $_SERVER['HTTP_REFERER']) );
							die();
						}

					}
			}

			if($_GPC['is_only_distribution'] == 1 && !empty($_GPC['supply_id']) ){
				//商户开启同城
				$sup_isopen_localtown_delivery =  D('Home/Front')->get_supply_config_by_name('isopen_localtown_delivery',$_GPC['supply_id']);

				if(  empty($sup_isopen_localtown_delivery) )
				{
					$supply_name  = M('eaterplanet_ecommerce_supply')->field('shopname,type')->where( array('id' => $_GPC['supply_id'] ) )->find();
					if($supply_name["type"] == 1 ){
							show_json(0, array('message' => '独立商户('.$supply_name["shopname"].')没有开启同城配送，此商品无法开启同城配送' ,'url' => $_SERVER['HTTP_REFERER']) );
							die();
					}


				}


			}
			$supply_id = $_GPC['supply_id'];
			if (defined('ROLE') && ROLE == 'agenter' )
			{
				$supper_info = get_agent_logininfo();
				$supply_id = $supper_info['id'];
			}

			if($_GPC['is_only_hexiao'] == 1){
				if(!empty($supply_id)){
					$salesroom_count  = M('eaterplanet_ecommerce_salesroom')->where( array('supply_id' => $supply_id,'state'=>1 ) )->count();
					if($salesroom_count == 0){
						$supply_name  = M('eaterplanet_ecommerce_supply')->field('shopname,type')->where( array('id' => $supply_id ) )->find();
						if($supply_name["type"] == 1 ){
							show_json(0, array('message' => '独立商户('.$supply_name["shopname"].')没有创建门店信息，此商品无法开启到店核销' ,'url' => $_SERVER['HTTP_REFERER']) );
							die();
						}
					}
				}
				if($_GPC['grounding'] == 1){//上架商品
					if($_GPC['hx_expire_type'] == 1){//指定过期时间
						$hx_expire_end_time = $_GPC['hx_expire_end_time'];
						if(strtotime($hx_expire_end_time) < time()){
							show_json(0, array('message' => '核销过期时间小于当前时间，商品不能上架' ,'url' => $_SERVER['HTTP_REFERER']) );
							die();
						}
					}
				}
				if($_GPC['hx_expire_type'] == 1){//指定过期时间
					if($_GPC['hx_auto_off'] == 1){//设置自动下架时间
						if(empty($_GPC['hx_auto_off_time']) || !is_numeric($_GPC['hx_auto_off_time']) || floatval($_GPC['hx_auto_off_time']) <= 0){
							show_json(0, array('message' => '提前自动下架商品时间不能为空且必须为大于0数字' ,'url' => $_SERVER['HTTP_REFERER']) );
							die();
						}
					}
				}
			}

            //礼品卡
            if (isset($_GET['goods_type']) && $_GET['goods_type'] == 'virtualcard')
            {
                $type = 'virtualcard';
                $virtual_code_id = I('post.virtual_code_id');
                if( !isset($virtual_code_id) || $virtual_code_id <= 0 )
                {
                    show_json(0, array('message' => '礼品卡商品必须关联兑换码' ,'url' => $_SERVER['HTTP_REFERER']) );
                    die();
                }
            }

			//预售时间判断
			if( isset($_GET['goods_type']) && $_GET['goods_type'] == 'presale' )
            {
                //预售时间限制判断
                $time = I('post.time');

                $post_begin_time = strtotime( $time['start'] );
                $post_end_time = strtotime( $time['end'] );

                $presale_ding_time = I('post.presale_ding_time');//定金支付时间
                $presale_ding_time_start = strtotime( $presale_ding_time['start'] ); //开始
                $presale_ding_time_end = strtotime( $presale_ding_time['end'] );//结束

                if($presale_ding_time_start < $post_begin_time )
                {
                    show_json(0, array('message' => '预售定金支付开始时间不能小于团购开始时间' ,'url' => $_SERVER['HTTP_REFERER']) );
                    die();
                }
                if($presale_ding_time_end > $post_end_time  )
                {
                    show_json(0, array('message' => '预售定金支付结束时间不能大于团购结束时间' ,'url' => $_SERVER['HTTP_REFERER']) );
                    die();
                }

                $presale_sendorder_type = I('post.presale_sendorder_type');
                if($presale_sendorder_type  == 1)
                {
                    $presale_sendorder_afterday = I('post.presale_sendorder_afterday');
                    if( $presale_sendorder_afterday < 1 )
                    {
                        show_json(0, array('message' => '预售购买后几日发货必须是大于0的整数' ,'url' => $_SERVER['HTTP_REFERER']) );
                        die();
                    }
                }else if( $presale_sendorder_type == 0 )
                {
                    //预计发货时间 必须大于 定金尾款付款时间
                    $presale_sendorder_datetime = I('post.presale_sendorder_datetime');
                    $presale_sendorder_datetime = strtotime($presale_sendorder_datetime.' 00:00:00');
                    if( $presale_sendorder_datetime < $presale_ding_time_start )
                    {
                        show_json(0, array('message' => '固定发货日期必须大于定金支付开始时间' ,'url' => $_SERVER['HTTP_REFERER']) );
                        die();
                    }

                }

            }

			//判断预售价格是否合理设置，不是bug价格
            $this->checkPresaleAddGoods();

			D('Seller/Goods')->modify_goods();

			$http_refer = S('HTTP_REFERER');

			$http_refer = empty($http_refer) ? $_SERVER['HTTP_REFERER'] : $http_refer;
			$goods = M('eaterplanet_ecommerce_goods')->where('id = "'.I('get.id').'"')->field('goodsname')->find();
            sellerLog('编辑了['.$goods['goodsname'].']商品', 3);


			D('Seller/Operatelog')->addOperateLog('goods','编辑商品--'.$goods['goodsname']);

            show_json(1, array('message'=>'修改商品成功！','url' => $http_refer ));
		}
		//sss
		S('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
		$this->id = $id;
		$item = D('Seller/Goods')->get_edit_goods_info($id);

		//-------------------------以上是获取资料

		$limit_goods = array();

		//limit_goods_list
		if( !empty($item['relative_goods_list']) )
		{
			$item['relative_goods_list'] = unserialize($item['relative_goods_list']);

			if( !empty($item['relative_goods_list']) )
			{
				$relative_goods_list_str = implode(',', $item['relative_goods_list']);

				$limit_goods = M()->query("SELECT id as gid,goodsname,subtitle FROM " . C('DB_PREFIX') .
				'eaterplanet_ecommerce_goods WHERE id in('.$relative_goods_list_str.') order by id desc' );

				foreach($limit_goods as $kk => $vv)
				{
					$thumb =  M('eaterplanet_ecommerce_goods_images')->where( array('goods_id' => $vv['gid'] ) )->order('id asc')->find();

					$vv['image'] =  tomedia($thumb['image']);

					$limit_goods[$kk] = $vv;
				}
			}
		}

		$this->limit_goods = $limit_goods;
		$mb_level_discount_list = array();
		if(!empty($item['mb_level_buy_list'])){
			$item['mb_level_buy_list'] = unserialize($item['mb_level_buy_list']);
			foreach($item['mb_level_buy_list'] as $k=>$v){
				$mb_level_discount_list[$v['level_id']] = $v['discount'];
			}
		}
		$this->mb_level_discount_list = $mb_level_discount_list;
		$category = D('Seller/GoodsCategory')->getFullCategory(true, true);

		$this->category = $category;


		$spec_list = D('Seller/Spec')->get_all_spec();

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

		//判断是否预售 begin
        if( $item['type'] == 'presale' )
        {
            $goods_presale = M('eaterplanet_ecommerce_goods_presale')->where(['goods_id' => $id ])->find();
            if( !empty($goods_presale) )
            {
                unset( $goods_presale['id'] );
                unset( $goods_presale['goods_id'] );
                unset( $goods_presale['addtime'] );
                foreach( $goods_presale as $k => $val )
                {
                    $item[ $k ] = $val;
                }
            }
        }
        //end
        //判断是否兑换卡 begin
        if( $item['type'] == 'virtualcard' )
        {
            //商品选中的兑换编组
            $goods_virtualcode_info = D('Seller/VirtualCard')->getGoodsVirtualCardInfoByGoodsId( $id );

            $goods_virtualcode_id = $goods_virtualcode_info['code_id'];
            $this->goods_virtualcode_id = $goods_virtualcode_id;

            //可用的兑换编组列表
            $virtualcard_code_list = D('Seller/VirtualCard')->getCanUseVirtualcardCodes(  );
            $this->virtualcard_code_list = $virtualcard_code_list;
        }
        //end

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

		//获取客户等级列表
		$member_level_ist = M('eaterplanet_ecommerce_member_level')->order('id asc')->select();

		//商户权限begin

		$is_index = true;
		$is_top = true;
		$is_updown  = true;
		$is_fullreduce  = true;
		$is_vir_count = true;
		$is_newbuy = true;
		$is_goodsspike = true;

		$supply_can_goods_sendscore = true;
		$supply_can_distribution_sale = 1;
		$sales_supply_id = 0;

		if (defined('ROLE') && ROLE == 'agenter' )
		{
			$supply_can_goods_sendscore = empty($config_data['supply_can_goods_sendscore']) ? 0: $config_data['supply_can_goods_sendscore'];

			$supply_can_distribution_sale = empty($config_data['supply_can_distribution_sale']) ? 0: $config_data['supply_can_distribution_sale'];
			$is_fullreduce = false;
			if( isset($config_data['supply_can_goods_isindex']) && $config_data['supply_can_goods_isindex'] == 2 )
			{
				$is_index = false;
			}
			if( isset($config_data['supply_can_goods_istop']) && $config_data['supply_can_goods_istop'] == 2 )
			{
				$is_top = false;
			}
			if( isset($config_data['supply_can_goods_updown']) && $config_data['supply_can_goods_updown'] == 2 )
			{
				$is_updown = false;
			}
			if( isset($config_data['supply_can_vir_count']) && $config_data['supply_can_vir_count'] == 2 )
			{
				$is_vir_count = false;
			}
			if( isset($config_data['supply_can_goods_newbuy']) && $config_data['supply_can_goods_newbuy'] == 2 )
			{
				$is_newbuy = false;
			}
			if( isset($config_data['supply_can_goods_spike']) && $config_data['supply_can_goods_spike'] == 2 )
			{
				$is_goodsspike = false;
			}
			$sales_supply_id = $supper_info = get_agent_logininfo();
			$sales_supply_id = $supper_info['id'];
		}
		$salesroom_count = M('eaterplanet_ecommerce_salesroom')->where(array('supply_id' => $sales_supply_id,'state'=>1))->count();
		if($salesroom_count > 0){
			$this->is_exist_salesroom = 1;
		}

		$is_open_vipcard_buy = $config_data['is_open_vipcard_buy'];

		$seckill_is_open = $config_data['seckill_is_open'];

		$is_head_takegoods = isset($config_data['is_head_takegoods']) && $config_data['is_head_takegoods'] == 1 ? 1 : 0;


        $isopen_localtown_delivery = isset($config_data['isopen_localtown_delivery']) && $config_data['isopen_localtown_delivery'] == 1 ? 1 : 0;
        $this->isopen_localtown_delivery = $isopen_localtown_delivery;

		$salesroom_count =  0;
		$this->salesroom_count = $salesroom_count;

		//商户权限end
		$this->supply_can_goods_sendscore = $supply_can_goods_sendscore;
		$this->supply_can_distribution_sale = $supply_can_distribution_sale;
		$this->is_index = $is_index;
		$this->is_top = $is_top;
		$this->is_updown  = $is_updown;
		$this->is_fullreduce  = $is_fullreduce;
		$this->is_vir_count = $is_vir_count;
		$this->is_newbuy = $is_newbuy;
		$this->is_goodsspike = $is_goodsspike;
		$this->is_open_vipcard_buy = $is_open_vipcard_buy;
		$this->seckill_is_open = $seckill_is_open;
		$this->is_head_takegoods = $is_head_takegoods;

		$this->member_level_is_open_info = D('Home/Front')->get_config_by_name('member_level_is_open');
		$this->member_level_ist = $member_level_ist;
		//$is_default_levellimit_buy = isset($config_data['is_default_levellimit_buy']) && $config_data['is_default_levellimit_buy'] == 1 ? 1 : 0;
		//$this->is_default_levellimit_buy = $is_default_levellimit_buy;

		//$is_default_vipmember_buy = isset($config_data['is_default_vipmember_buy']) && $config_data['is_default_vipmember_buy'] == 1 ? 1 : 0;
		//$this->is_default_vipmember_buy = $is_default_vipmember_buy;

		$isopen_localtown_delivery = isset($config_data['isopen_localtown_delivery']) && $config_data['isopen_localtown_delivery'] == 1 ? 1 : 0;
		$this->isopen_localtown_delivery = $isopen_localtown_delivery;

		$supply_is_open_localtown_distribution = isset($config_data['supply_is_open_localtown_distribution']) && $config_data['supply_is_open_localtown_distribution'] == 1 ? 1 : 0;
		$this->supply_is_open_localtown_distribution = $supply_is_open_localtown_distribution;


        if( $item['type'] == 'presale' )
        {
            $this->display('Presale/addgoods');
        }
        else if( $item['type'] == 'virtualcard' )
        {
            $this->display('Virtualcard/addgoods');
        }
        else{
            $this->display('Goods/addgoods');
        }

	}

	public function labelfile()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			show_json(0, array() );
			die();
		}

		$condition = '  id = '.$id.' and state = 1 ';

		$labels = M('eaterplanet_ecommerce_goods_tags')->field('id,tagname,type,tagcontent')->where($condition)->find();

		if (empty($labels)) {
			$labels = array();
			show_json(0, array('msg' => '您查找的标签不存在或已删除！') );
			die();
		}

		show_json(1, array('label' => $labels['tagname'], 'id' => $labels['id']));
	}

	public function goodstag()
	{
		$_GPC = I('request.');

		$this->gpc = $_GPC;

		$condition = ' 1 and tag_type="normal" ';
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

	function copy_goods(){
		$id =I('id');
		$model=new GoodsModel();
		if($id){
			foreach ($id as $k => $v) {
				$model->copy_goods($v);
			}
			$data['redirect']=U('Goods/index');
			$this->ajaxReturn($data);
			die;
		}
	}

	function del(){
		$model=new GoodsModel();
		$return=$model->del_goods(I('get.id'));
		$this->osc_alert($return);
	}

/**
	 * 置顶
	 * @return [json] 0 失败 1 成功
	 */
	public function settop()
	{

		$id =  I('request.id');

		//ids
		if (empty($id)) {
			$ids = I('request.ids');

			$id = ((is_array($ids) ? implode(',', $ids) : 0));
		}

		if (empty($id)) {
			show_json(0, array('message' => '参数错误'));
		}

		$type = I('request.type');
		$value = I('request.value');

		if ($type != 'istop') {
			show_json(0, array('message' => '参数错误'));
		}

		$items = M('eaterplanet_ecommerce_goods')->field('id')->where( 'id in( ' . $id . ' )' )->select();

		foreach ($items as $item ) {
			$settoptime = $value ? time() : '';

			M('eaterplanet_ecommerce_goods')->where( array('id' => $item['id'])  )->save( array($type => $value, 'settoptime' => $settoptime) );
		}


		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}

	public function industrial()
    {
      $_GPC = I('request.');

        if ( IS_POST ) {
            $data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			$data['goods_industrial'] = serialize($data['goods_industrial']);


           D('Seller/Config')->update($data);
            D('Seller/Operatelog')->addOperateLog('goods','修改商品设置--工商资质');
           show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
        }

		$data = D('Seller/Config')->get_all_config();
		$data['goods_industrial'] = unserialize($data['goods_industrial']);
		$piclist = array();
		if( !empty($data['goods_industrial']) )
		{
			foreach($data['goods_industrial'] as $val)
			{
				$piclist[] = array('image' =>$val, 'thumb' => tomedia($val) ); //$val['image'];
			}
		}

		$this->piclist = $piclist;
		$this->data = $data;
		$this->display();
    }

    /**
     * excel商品导入编辑
     * */
    public function excel_goodslist_edit()
    {
        $columns = array(
            array(
                'title' => '商品ID(禁止修改)',
                'field' => 'id',
                'width' => 24
            ) ,
            array(
                'title' => '商品名称',
                'field' => 'goodsname',
                'width' => 24
            ) ,
            array(
                'title' => '一级分类ID',
                'field' => 'cate1_id',
                'width' => 24
            ) ,
            array(
                'title' => '一级分类名称',
                'field' => 'cate1_name',
                'width' => 24
            ) ,
            array(
                'title' => '二级分类ID',
                'field' => 'cate2_id',
                'width' => 24
            ) ,
            array(
                'title' => '二级分类名称',
                'field' => 'cate2_name',
                'width' => 24
            ) ,

            array(
                'title' => '商品价格',
                'field' => 'price',
                'width' => 24
            ) ,
            array(
                'title' => '商品成本价',
                'field' => 'costprice',
                'width' => 24
            ) ,
            array(
                'title' => '会员卡价格',
                'field' => 'card_price',
                'width' => 24
            ) ,
            array(
                'title' => '商品原价',
                'field' => 'productprice',
                'width' => 24
            ) ,
            array(
                'title' => '商品库存',
                'field' => 'total',
                'width' => 24
            ) ,
            array(
                'title' => '1上架/0下架',
                'field' => 'grounding',
                'width' => 24
            ) ,
            array(
                'title' => '首页推荐(0:取消/1:是)',
                'field' => 'is_index_show',
                'width' => 24
            ) ,
            array(
                'title' => '限时秒杀(0:取消/1:是)',
                'field' => 'is_spike_buy',
                'width' => 24
            ) ,
            array(
                'title' => '所有团长',
                'field' => 'is_all_sale_str',
                'width' => 24
            ) ,
            array(
                'title' => '新人专享',
                'field' => 'is_new_buy',
                'width' => 24
            ) ,

            array(
                'title' => '商品排序(数字)',
                'field' => 'index_sort',
                'width' => 24
            ) ,
            array(
                'title' => '每天限购',
                'field' => 'oneday_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '单次限购',
                'field' => 'one_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '历史限购',
                'field' => 'total_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '开始时间',
                'field' => 'pin_begin_time',
                'width' => 24
            ) ,
            array(
                'title' => '结束时间',
                'field' => 'pin_end_time',
                'width' => 24
            ) ,
            array(
                'title' => '商品重量(单位:g)',
                'field' => 'weight',
                'width' => 24
            ) ,
            array(
                'title' => '规格(1:开启/0:关闭)',
                'field' => 'hasoption',
                'width' => 24
            ) ,
            array(
                'title' => '规格id(禁止修改)',
                'field' => 'option_id',
                'width' => 24
            ) ,
            array(
                'title' => '规格名称(禁止修改)',
                'field' => 'option_title',
                'width' => 24
            ) ,
            array(
                'title' => '规格库存',
                'field' => 'option_stock',
                'width' => 24
            ) ,
            array(
                'title' => '规格现价',
                'field' => 'option_marketprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格原价',
                'field' => 'option_productprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格会员价',
                'field' => 'option_card_price',
                'width' => 24
            ) ,
            array(
                'title' => '规格成本价',
                'field' => 'option_costprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格编码',
                'field' => 'option_goodssn',
                'width' => 24
            ) ,
            array(
                'title' => '规格重量(单位:g)',
                'field' => 'option_weight',
                'width' => 24
            )
        );
        sellerLog('导入商品excel编辑', 3);
        $rows = D('Seller/Excel')->import('excel');
        $row_count = count($rows);
        $field_arr = [];
        if($row_count <= 1){
            $this->error('失败','goods/index');

            die;
        }
        foreach($rows[0] as $key => $value) {
            foreach($columns as $k => $val) {
                if($val['title'] == $value){
                    $field_arr[$key]= $val['field'];
                }
            }
        }
        unset($rows[0]);
        foreach($rows as $key => $value){
            $this->excelGoodsUpdate($value, $field_arr);
        }
        $this->success('成功','goods/index');

    }
    public function excelGoodsUpdate($data, $field_arr)
    {
        $optionId = 0;
        $goodsId  = 0;
        $goodsData = [];
        $optionData = [];
        foreach($data as $key => $value){
            $field = $field_arr[$key];

            if(strstr($field, 'option_') > -1 && $value){// 规格
                if($field == 'option_id'){// 规格id
                    $optionId = $value;
                    $goodsData = [];
                }
                $optionData[str_replace('option_','',$field)] = $value;
                $goodsId  = 0;
            }else{ // 普通商品数据
                if(strstr($field, 'option_') > -1 ) {// 规格
                    continue;
                }

                    if($field == 'id'){// 规格id
                    $goodsId = $value;
                    $optionData = [];
                }
                $optionId  = 0;
                $goodsData[$field] = $value;

            }
        }
        if($optionId > 0 && count($optionData) > 0){
            unset($optionData['title']);
            unset($optionData['id']);
            M('eaterplanet_ecommerce_goods_option_item_value')->where('id = "'.$optionId.'"')->save($optionData);

            unset($optionData);
        }else if($goodsId > 0 && count($goodsData) > 0){
            unset($goodsData['is_all_sale_str']);
            unset($goodsData['cate1_id']);
            unset($goodsData['cate1_name']);
            unset($goodsData['cate2_id']);
            unset($goodsData['cate2_name']);
            unset($goodsData['id']);

            $goodsCommon = M('eaterplanet_ecommerce_good_common')->where('goods_id = "'.$goodsId.'"')->field('id')->find();
            if($goodsCommon['id']){
                M('eaterplanet_ecommerce_good_common')
                    ->where('id = "'.$goodsCommon['id'].'"')
                    ->save([
                        'one_limit_count' => $goodsData['one_limit_count'],
                        'total_limit_count' => $goodsData['total_limit_count'],
                        'is_spike_buy' => $goodsData['is_spike_buy'],
                        'is_new_buy' => $goodsData['is_new_buy'],
                    ]);
                unset($goodsData['one_limit_count']);
                unset($goodsData['total_limit_count']);
                unset($goodsData['is_spike_buy']);
                unset($goodsData['is_new_buy']);
            }

            $goodsPin = M('eaterplanet_ecommerce_good_pin')->where('goods_id = "'.$goodsId.'"')->field('id')->find();
            if($goodsPin['id']){
                M('eaterplanet_ecommerce_good_pin')
                    ->where('id = "'.$goodsPin['id'].'"')
                    ->save([
                        'begin_time' => $goodsData['begin_time'],
                        'end_time' => $goodsData['end_time'],
                    ]);
                unset($goodsData['begin_time']);
                unset($goodsData['end_time']);
            }
            M('eaterplanet_ecommerce_goods')
                ->where('id = "'.$goodsId.'"')
                ->save($goodsData);
            unset($goodsData);
        }
     }

	public function check_express(){
		$result = array();
		$pintuan_model_buy = D('Home/Front')->get_config_by_name('pintuan_model_buy');
		$count = 0;
        $sql = " select count(1) as count  from ".C('DB_PREFIX')."eaterplanet_ecommerce_goods as g left join ".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc "
             . " on g.id = gc.goods_id where g.type = 'normal' AND g.grounding =1 AND gc.is_only_express =1";

        $goods_list = M()->query( $sql );
        if(!empty($goods_list)){
            $count = $goods_list[0]['count'];
        }

        if($count > 0 || $pintuan_model_buy == 0){
			$result['status'] = 1;
		}else{
			$result['status'] = 0;
		}
		echo json_encode($result);
	}
	//部分团长列表
	public function goods_head(){
		$condition = '  ';
		$pindex = I('get.page',1);
		$psize = 20;
		$goods_id =  I('get.id');

		$goods_info = M('eaterplanet_ecommerce_goods')->where(array('id'=>$goods_id))->field('id,goodsname,is_all_sale')->find();

		$keyword = I('get.keyword','');
		$this->keyword = $keyword;

		if (!empty($keyword)) {
			$condition .= ' and ( m.username like '.'"%' . $keyword . '%"'.' or ch.community_name like '.'"%' . $keyword . '%"'.' or ch.head_name like '.'"%' . $keyword . '%"'.' or ch.head_mobile like '.'"%' . $keyword . '%"'.' or ch.address like '.'"%' . $keyword . '%"'.') ';
		}
		$condition .= ' and hg.goods_id='.$goods_id;
		$sql = "SELECT ch.*,m.we_openid,m.username,m.avatar,hg.id as hg_id FROM " . C('DB_PREFIX') . "eaterplanet_community_head as ch "
		      . " left join ".C('DB_PREFIX')."eaterplanet_ecommerce_member as m on  ch.member_id = m.member_id "
			  . ' left join '.C('DB_PREFIX')."eaterplanet_community_head_goods as hg on hg.head_id = ch.id "
			  . " WHERE 1  " . $condition . " order by ch.id desc  ";

		if (empty($export)) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}

		$list = M()->query($sql);

		$sql_count = 'SELECT count(1) as total FROM ' . C('DB_PREFIX') . 'eaterplanet_community_head as ch '
		           . ' left join '.C('DB_PREFIX').'eaterplanet_ecommerce_member as m on ch.member_id = m.member_id '
				   . ' left join '. C('DB_PREFIX').'eaterplanet_community_head_goods as hg on hg.head_id = ch.id '
		           . ' WHERE 1  ' . $condition;

		$total_arr = M()->query($sql_count);

		$total = $total_arr[0]['total'];

		$pager = pagination2($total, $pindex, $psize);



		$this->goods_info = $goods_info;

		$this->pager = $pager;
		$this->list = $list;
		$this->display('goods_communityhead');
	}

	public function cancel_goods_head(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);
		$goods_id = intval($_GPC['goods_id']);
		M('eaterplanet_community_head_goods')->where( array('id' => $id) )->delete();

		show_json(1, array('url' => U('goods/goods_head/id/'.$goods_id)));
	}

	public function all_goods_head(){
		$_GPC = I('request.');
		$goods_id = intval($_GPC['goods_id']);

		M('eaterplanet_ecommerce_goods')->where( array('id' => $goods_id) )->save(array('is_all_sale'=>1));

		show_json(1, array('url' => U('goods/index/ok/1')));
	}

    public function copy(){
        // echo 7777;die;
        $data = I('request.');
        $id = $data['id'];
        $res = M('eaterplanet_ecommerce_goods')->where(['id'=>$id])->find();
        isset($res['copy_times'])?$res['copy_times']:0;
        $copy_times = $res['copy_times'];
        M('eaterplanet_ecommerce_goods')->where(['id'=>$id])->setInc('copy_times');
        //dump($copy_times);

        if ($copy_times>0){
            $copy_times = $copy_times+1;
            $str = '00'.$copy_times;
        }else{
            $str = '001';
        }
        unset($res['id']);
        unset($res['copy_times']);

        $res['goodsname'] = $res['goodsname'].$str;
		//销量设置0
		$res['sales'] = 0;
        $res['seller_count'] = 0;
        $res['day_salescount'] = 0;
        $goods_id =  M('eaterplanet_ecommerce_goods')->add($res);
        // $goods_id = 385; //to delete
        $cate = M('eaterplanet_ecommerce_goods_to_category')->where(['goods_id'=>$id])->select();
        //dump($cate);
        foreach ($cate as $k=>$v){
            $tmp['cate_id'] = $v['cate_id'];
            $tmp['goods_id'] = $goods_id;
            M('eaterplanet_ecommerce_goods_to_category')->add($tmp);
        }
        $goods_common = M('eaterplanet_ecommerce_good_common')->where(['goods_id'=>$id])->find();
        unset($goods_common['id']);
        $goods_common['goods_id'] = $goods_id;
        // $goods_common['goods_id'] = 385;
        M('eaterplanet_ecommerce_good_common')->add($goods_common);
        $option = M('eaterplanet_ecommerce_goods_option')->where(['goods_id'=>$id])->select();

        if ( count($option)) {
            foreach ($option as $k => $v) {//循环规格： 颜色  尺寸
                $front_goods_option_id = $v['id'];
                unset($v['id']);

                $v['goods_id'] = $goods_id;
                //dump($v);
                $goods_option_id = M('eaterplanet_ecommerce_goods_option')->add($v);
                $option_item = M('eaterplanet_ecommerce_goods_option_item')->where(['goods_option_id' => $front_goods_option_id])->select();
//              dump($option_item);die;
                static $i = 0;
                foreach ($option_item as $kk => $vv) { //循环单个规格的选项，颜色有蓝色，绿色。。。

                    unset($vv['id']);
                    $vv['goods_id'] = $goods_id;
                    $vv['goods_option_id'] = $goods_option_id;
                    $ids[$i][] = M('eaterplanet_ecommerce_goods_option_item')->add($vv);
                }
                $i++;

            }
            $ids = $this->resver_arr($ids);
            $ids = $this->zuhe($ids);
            //dump($ids);die;
            $j = 0;
            $option_item_value = M('eaterplanet_ecommerce_goods_option_item_value')->where(['goods_id' => $id])->select();
            foreach ($option_item_value as $key => $value) {
                unset($value['id']);
                $value['option_item_ids'] = $ids[0][$j++];
                $value['goods_id'] = $goods_id;
                M('eaterplanet_ecommerce_goods_option_item_value')->add($value);

            }
        }
        $imgs = M('eaterplanet_ecommerce_goods_images')->where(['goods_id'=>$id])->select();
        foreach ($imgs as $v){
            unset($v['id']);
            $v['goods_id'] = $goods_id;
            M('eaterplanet_ecommerce_goods_images')->add($v);
        }

        D('Seller/Redisorder')->sysnc_goods_total($goods_id);
        $url = U("goods/edit/id/$goods_id");
        echo json_encode(['code'=>1,'url'=>$url]);
    }

    function zuhe($arr){
        if(count($arr) >= 2){
            $tmparr = array();
            $arr1 = array_shift($arr);
            $arr2 = array_shift($arr);
            foreach($arr1 as $k1 => $v1){
                foreach($arr2 as $k2 => $v2){
                    $tmparr[] = $v2.'_'.$v1;
                }
            }
            array_unshift($arr, $tmparr);
            $arr = $this->zuhe($arr);
        }else{
            return $arr;
        }
        return $arr;
    }

    function resver_arr($arr){
        $len = count($arr);
        $i = $len - 1;
        foreach($arr as $k=>$v){
            $tmp[$i--] = $v;

        }
        ksort($tmp);
        return $tmp;

    }
}
?>
