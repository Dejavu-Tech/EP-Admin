<?php/** * eaterplanet 商城系统 * * ========================================================================== * @link      https://e-p.cloud/ * @copyright Copyright (c) 2019-2024 Dejavu Tech. * @license   https://github.com/Dejavu-Tech/EP-Admin/blob/main/LICENSE * ========================================================================== * * @author    Albert.Z * */namespace Seller\Model;class CommunityheadModel{	public function modify_head($data)    {        if($data['id'] > 0)        {            //update ims_            $id = $data['id'];            unset($data['id']);			M('eaterplanet_community_head')->where( array('id' => $id ) )->save( $data );        }else{            //insert			$id = M('eaterplanet_community_head')->add($data);        }        return $id;    }	//begin	/**		根据客户id获取团长id	**/	public function get_head_id_by_member_id($member_id)	{		$head_info = M('eaterplanet_community_head')->field('id')->where( array('member_id' => $member_id ) )->find();		return $head_info['id'];	}	/**		根据团长id获取客户id	**/	public function get_agent_member_id($agent_head_id = 0)	{		$head_info = M('eaterplanet_community_head')->field('member_id')->where( array('id' => $agent_head_id) )->find();		return $head_info['member_id'];	}	//end	public function insert_head_goods($goods_id, $head_id)    {    	global $_W;		global $_GPC;		$head_goods_info = M('eaterplanet_community_head_goods')->where( array('goods_id' => $goods_id,'head_id' => $head_id) )->find();    	if( empty($head_goods_info) )    	{    		$data = array();    		$data['head_id'] = $head_id;    		$data['goods_id'] = $goods_id;    		$data['addtime'] = time();			M('eaterplanet_community_head_goods')->add($data);    	}    }	//begin	/**		获取商品对应团长等级对应的分佣比例情况	**/	public function get_goods_head_level_bili( $goods_id )	{		$goods_common_info = M('eaterplanet_ecommerce_good_common')							->field('is_modify_head_commission,community_head_commission_modify')->where( array('goods_id' => $goods_id ) )->find();		$community_head_level = M('eaterplanet_ecommerce_community_head_level')->order('id asc')->select();		$head_commission_levelname = D('Home/Front')->get_config_by_name('head_commission_levelname');		$default_comunity_money = D('Home/Front')->get_config_by_name('default_comunity_money');		$list = array(			array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $default_comunity_money, )		);		$open_community_head_leve = D('Home/Front')->get_config_by_name('open_community_head_leve');		$head_commission_info_gd = D('Home/Front')->get_goods_common_field( $goods_id , 'community_head_commission');		if( !empty($head_commission_info_gd) && $head_commission_info_gd['community_head_commission'] > 0 )		{		    $list = array(		        array('id' => '0','level'=>0,'levelname' => empty($head_commission_levelname) ? '默认等级' : $head_commission_levelname, 'commission' => $head_commission_info_gd['community_head_commission'], )		    );		}		$is_head_takegoods = D('Home/Front')->get_config_by_name('is_head_takegoods');		$is_head_takegoods = isset($is_head_takegoods) && $is_head_takegoods == 1 ? 1 : 0;		if( $is_head_takegoods == 1 )		{			$community_head_level = array_merge($list, $community_head_level);		}else{			$community_head_level = $list ;		}		$result = array();		if( $goods_common_info['is_modify_head_commission'] == 1 && $is_head_takegoods == 1) {			$result = unserialize($goods_common_info['community_head_commission_modify']);			foreach ($community_head_level as $head_level) {				if (!isset($result['head_level' . $head_level['id']])) {					$result['head_level' . $head_level['id']] = $head_level['commission'];				}			}		}else{			if( !empty($community_head_level) )			{				foreach( $community_head_level as $head_level)				{					$result['head_level'.$head_level['id']] = $head_level['commission'];				}			}		}		return $result;	}	//end	public function show_communityhead_page()	{		$condition = '  ';		$keyword = I('get.keyword','','trim');		if (!empty($keyword)) {			$condition .= ' and ( m.username like "'.'%' . $keyword . '%'.'" or ch.community_name like "'.'%' . $keyword . '%'.'" or ch.head_name like "'.'%' . $keyword . '%'.'" or ch.head_mobile like "'.'%' . $keyword . '%'.'" or ch.address like "'.'%' . $keyword . '%'.'") ';		}		$start = I('get.start','');		$end = I('get.end','');		if (!empty($start) && !empty($end)) {			$starttime = strtotime($start);			$endtime = strtotime($end);			$condition .= ' AND ch.apptime >= '.$starttime.' AND ch.apptime <= '.$endtime;		}		$comsiss_state = I('get.comsiss_state','');		if ($comsiss_state != '') {			$condition .= ' and ch.state=' . intval($_GPC['comsiss_state']);		}		$sql_count = 'SELECT count(1) as count FROM ' .C('DB_PREFIX'). 'eaterplanet_community_head as ch ,'.C('DB_PREFIX').'member as m						WHERE ch.member_id = m.member_id ' . $condition;		$count_arr = M()->query($sql_count);		$count = $count_arr[0]['count'];	    $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));	    $show  = $Page->show();// 分页显示输出		$sql = 'SELECT ch.*,m.we_openid,m.username,m.avatar FROM ' . C('DB_PREFIX') . "eaterplanet_community_head as ch, ".C('DB_PREFIX')."member as m  \r\n						WHERE ch.member_id = m.member_id  " . $condition . ' order by ch.id desc  ';		$export = I('get.export','');		if (empty($export)) {			$sql .= ' limit  '.$Page->firstRow.','.$Page->listRows;		}		$list =  M()->query($sql);		$all_sell_count = M('eaterplanet_ecommerce_goods')->where( "is_all_sale=1" )->count();		foreach( $list as $key => $val )		{			//commission_info			$commission_info = M('eaterplanet_community_head_commiss')->where( array('head_id' => $val['id']) )->find();			$commission_info['commission_total'] = $commission_info['money']+ $commission_info['dongmoney'] + $commission_info['getmoney'];			$val['commission_info'] = $commission_info;			//普通等级			$member_info = M('eaterplanet_ecommerce_member')->where( array('member_id' => $val['member_id'] ) )->find();			//get_area_info($id=0)			$val['province_name'] = D('seller/area')->get_area_info($val['province_id']);			$val['city_name'] = D('seller/area')->get_area_info($val['city_id']);			$val['area_name'] = D('seller/area')->get_area_info($val['area_id']);			$val['country_name'] = D('seller/area')->get_area_info($val['country_id']);			$ct_arr = M()->query("select count(hg.id) as count from ".C('DB_PREFIX')."eaterplanet_community_head_goods as hg ,								".C('DB_PREFIX')."eaterplanet_ecommerce_good_common as gc								where hg.goods_id = gc.goods_id and hg.head_id =".$val['id']);			$val['goods_count'] = $ct_arr[0]['count'];			$val['goods_count'] += $val['all_sell_count'];			//$val['member_info'] = $member_info;			$list[$key] = $val;		}		if ($_GPC['export'] == '1') {			foreach ($list as &$row) {			    //$row['username'] = $val['member_info']['username'];			    //$row['we_openid'] = $val['member_info']['we_openid'];			    $row['commission_total'] = 0;			    $row['getmoney'] = 0;			    $row['fulladdress'] = $row['province_name'].$row['city_name'].$row['area_name'].$row['country_name'].$row['address'];			    $row['addtime'] = date('Y-m-d H:i:s', $row['addtime']);			    $row['apptime'] = date('Y-m-d H:i:s', $row['apptime']);			    $row['state'] = $row['state'] == 1 ? '已审核':'未审核';			}			unset($row);			$columns = array(				array('title' => 'ID', 'field' => 'member_id', 'width' => 12),				array('title' => '微信用户名', 'field' => 'username', 'width' => 12),			    array('title' => '团长名称', 'field' => 'head_name', 'width' => 12),				array('title' => '联系方式', 'field' => 'head_name', 'width' => 12),				array('title' => '在售商品数量', 'field' => 'goods_count', 'width' => 24),				array('title' => 'openid', 'field' => 'we_openid', 'width' => 24),				array('title' => '累计佣金', 'field' => 'commission_total', 'width' => 12),				array('title' => '打款佣金', 'field' => 'getmoney', 'width' => 12),			    array('title' => '省', 'field' => 'province_name', 'width' => 12),			    array('title' => '市', 'field' => 'city_name', 'width' => 12),			    array('title' => '区', 'field' => 'area_name', 'width' => 12),			    array('title' => '街道/镇', 'field' => 'country_name', 'width' => 12),			    array('title' => '提货地址', 'field' => 'address', 'width' => 24),			    array('title' => '完整提货地址', 'field' => 'fulladdress', 'width' => 24),				array('title' => '注册时间', 'field' => 'addtime', 'width' => 12),				array('title' => '成为团长时间', 'field' => 'apptime', 'width' => 12),				array('title' => '审核状态', 'field' => 'state', 'width' => 12)			);			//load_model_class('excel')->export($list, array('title' => '团长数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));		}		 return array(	        'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',	        'list'=>$list,	        'page'=>$show	    );	}	public function is_community($communityId){		// 社区团长是否存在 and state=1		$community = M('eaterplanet_community_head')->field('member_id')->where( array('id' => $communityId,'enable' => 1, 'state' => 1) )->find();		$is_community = true;		if(!empty($community) && !empty($community['member_id']) && $community['member_id'] !=0){			$communityInfo = M('eaterplanet_ecommerce_member')->field('member_id')->where( array('member_id' => $community['member_id'] ) )->find();			(!empty($communityInfo) && $communityInfo['member_id']) ? $is_community = true : $is_community = false;		} else {			$is_community = false;		}		return $is_community;	}	public function show_comment_page($search = array()){	    $sql='SELECT * FROM '.C('DB_PREFIX').'order_comment where 1= 1 ';	    if(isset($search['goods_id']))	    {	        $sql.=" and goods_id=".$search['goods_id'];	    }		if( isset($search['goods_name']) && !empty($search['goods_name']) )		{			$sql.=" and goods_name like '%".$search['goods_name']."%'";		}		if( isset($search['order_num_alias']) && !empty($search['order_num_alias']) )		{			$sql.=" and order_num_alias like '%".$search['order_num_alias']."%'";		}	    $count=count(M()->query($sql));	    $Page = new \Think\Page($count,C('BACK_PAGE_NUM'));	    $show  = $Page->show();// 分页显示输出	    $sql.=' order by state asc,add_time desc LIMIT '.$Page->firstRow.','.$Page->listRows;	    $list=M()->query($sql);	    foreach($list as $key => $val)	    {			//	goods_id name image			if(empty($val['goods_name']))			{				$goods_info = M('goods')->field('goods_id,name,image')->where( array('goods_id' => $val['goods_id']) )->find();				$val['goods_name'] = $goods_info['name'];				$val['goods_image'] = $goods_info['image'];				M('order_comment')->where( array('comment_id' => $val['comment_id']) )				->save( array('goods_name' =>$val['goods_name'],'goods_image' =>$val['goods_image']  ) );			}			//member_id order_comment order_id			if(empty($val['order_num_alias']))			{				$order_info = M('order')->field('order_num_alias')->where( array('order_id' => $val['order_id']) )->find();				$val['order_num_alias'] = $order_info['order_num_alias'];				M('order_comment')->where( array('comment_id' => $val['comment_id']) )				->save( array('order_num_alias' =>$val['order_num_alias']  ) );			}			if(empty($val['user_name']))			{				$member_info = M('member')->field('name,avatar')->where( array('member_id' => intval($val['member_id'])) )->find();				$val['user_name']  = $member_info['name'];				$val['avatar']     = $member_info['avatar'];				M('order_comment')->where( array('comment_id' => $val['comment_id']) )				->save( array('user_name' =>$val['user_name'], 'avatar' =>$val['avatar'] ) );			}	        $list[$key] = $val;	    }	    return array(	        'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',	        'list'=>$list,	        'page'=>$show	    );		/**	    $where = array();	    if(!empty($search) && isset($search['store_id'])) {	        $where['store_id'] = $search['store_id'];	    }		$count=M('pick_up')->where($where)->count();		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));		$show  = $Page->show();// 分页显示输出		$list = M('pick_up')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();		return array(			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',			'list'=>$list,			'page'=>$show		);		**/	}	public function show_pickup_member_page( $search = array() )	{		$where = array();	    if(!empty($search) && isset($search['store_id'])) {	        $where['store_id'] = $search['store_id'];	    }		if(!empty($search) && isset($search['pick_up_id'])) {	        $where['pick_up_id'] = $search['pick_up_id'];	    }		//		$count=M('pick_member')->where($where)->count();		$Page = new \Think\Page($count,C('BACK_PAGE_NUM'));		$show  = $Page->show();// 分页显示输出		$list = M('pick_member')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();		foreach( $list as $key => $val )		{			if( $val['pick_up_id'] == 0)			{				$val['pick_name'] = '<span class="red">所有店铺</span>';			}else{				$pick_up_info =  M('pick_up')->field('pick_name')->where( array('id' => $val['pick_up_id']) )->find();				$val['pick_name'] = $pick_up_info['pick_name'];			}			$pick_order_count =  M('pick_order')->where( array('pick_member_id' => $val['member_id']) )->count();			//name			$val['pick_order_count'] = $pick_order_count;			$val['member_info'] = M('member')->field('name,avatar')->where( array('member_id' => $val['member_id']) )->find();			$list[$key] = $val;		}		return array(			'empty'=>'<tr><td colspan="20">~~暂无数据</td></tr>',			'list'=>$list,			'page'=>$show		);	}	function GetDistance($lng1,$lat1,$lng2,$lat2){        $lng1 = floatval( $lng1 );        $lat1 = floatval( $lat1 );        $lng2 = floatval( $lng2 );        $lat2 = floatval( $lat2 );	    //将角度转为狐度	    $radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度	    $radLat2=deg2rad($lat2);	    $radLng1=deg2rad($lng1);	    $radLng2=deg2rad($lng2);	    $a=$radLat1-$radLat2;	    $b=$radLng1-$radLng2;	    $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;//计算出来的结果单位为米	    return floor($s);	    /**	     * 单位：km	     * SELECT              geo_id, `name`,(                6371 * acos (                  cos ( radians(33.958887) )                  * cos( radians( lat ) )                  * cos( radians( lng ) - radians(118.302416) )                  + sin ( radians(33.958887) )                  * sin( radians( lat ) )                )              ) AS distance            FROM geo            HAVING distance < 20            ORDER BY distance            LIMIT 0 , 20；	     */	    /**	     * select ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($latitude*PI()/180-latitude*PI()/180)/2),2)+COS($latitude*PI()/180)*COS(latitude*PI()/180)*POW(SIN(($longitude*PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance FROM shop having distance <= 5000 order by distance asc	     */	}	public function is_community_rest($communityId){		$community = M('eaterplanet_community_head')->field('rest')->where( array('enable' => 1,'state' => 1,'id' => $communityId ) )->find();		$is_community_rest = 0;		if(!empty($community) && !empty($community['rest']) && $community['rest'] !=0){			$is_community_rest = 1;		} else {			$is_community_rest = 0;		}		return $is_community_rest;	}	public function check_goods_can_community($goods_id, $community_id)	{		$goods_info = M('eaterplanet_ecommerce_goods')->field('is_all_sale,type')->where( array('id' => $goods_id ) )->find();		if( $goods_info['is_all_sale'] == 1 )		{			return true;		}else{    		if( $goods_info['type'] == 'integral' )    		{    			return true;    		}			$rel_info = M('eaterplanet_community_head_goods')->where( array('head_id' => $community_id, 'goods_id' =>$goods_id ) )->find();			if( !empty($rel_info) )			{				return true;			}else{				return false;			}		}	}}?>