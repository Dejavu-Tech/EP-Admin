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
namespace Seller\Controller;

class PopadvController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='弹窗广告';
		$this->breadcrumb2='广告列表';
		$this->sellerid = SELLERUID;
	}

	/**
	 * 弹窗广告列表
	 */
	public function index()
	{
		$_GPC = I('request.');

		$this->gpc = $_GPC;

		$condition = ' 1 ';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
        $time = $_GPC['time'];
		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and adv_name like "%'.$_GPC['keyword'].'%" ';
		}
		if (isset($_GPC['status']) && trim($_GPC['status']) != '') {
			$_GPC['status'] = trim($_GPC['status']);
			$condition .= ' and status = ' . $_GPC['status'];
		}
		if(!empty($time['start'])){
            /*$condition .= ' and ((begin_time <= ' . strtotime($time['start']). "  and end_time >= ".strtotime($time['start'])." ) ";
            $condition .= ' or (begin_time <= ' . strtotime($time['end']). "  and end_time >= ".strtotime($time['end'])." )) ";*/
			$condition .= " and begin_time <= ".strtotime($time['end'])." and end_time >=".strtotime($time['start'])." ";
            $this->starttime = strtotime($time['start']);
            $this->endtime = strtotime($time['end']);
		}else{
            $this->starttime = strtotime(date('Y-m-d').' 00:00');
            $this->endtime = strtotime(date('Y-m-d').' 23:59');
        }
		$label = M('eaterplanet_ecommerce_pop_adv')->where( $condition )->order(' sort_order desc ')->limit( (($pindex - 1) * $psize) . ',' . $psize )->select();

		$total = M('eaterplanet_ecommerce_pop_adv')->where( $condition )->count();

		$pager = pagination2($total, $pindex, $psize);
		$time = time();
		foreach($label as $k=>$v){
            if($v['begin_time'] >= $time){
                $label[$k]['adv_status'] = '未开始';
            }else if($v['begin_time'] < $time && $v['end_time'] >= $time){
                $label[$k]['adv_status'] = '正在进行中';
            }else if($v['end_time'] < $time){
                $label[$k]['adv_status'] = '已结束';
            }
        }
		$this->label = $label;
		$this->pager = $pager;

		$this->display("popadv_index");
	}

	/**
	 * 添加弹窗广告
	 */
	public function add_popadv(){
		$_GPC = I('request.');

		if (IS_POST) {
			$data = $_GPC['data'];
            $time = $_GPC['time'];
			$open_redis_server = D('Home/Front')->get_config_by_name('open_redis_server');
			if($open_redis_server != 1){
				show_json(0, array('message' => '请开启redis服务'));
			}

			if (empty($data['adv_name'])) {
				show_json(0, array('message' => '活动名称不能为空'));
			}

			$aid = $_GPC['aid'];
			$thumb = $_GPC['thumb'];
			for($i = 0;$i < count($aid);$i++){
				if(empty($thumb[$i])){
					show_json(0, array('message' => '广告'.$aid[$i].'图片不能为空'));
				}
				if(empty($data['link_'.$aid[$i]])){
					show_json(0, array('message' => '广告'.$aid[$i].'链接不能为空'));
				}
			}

            $begin_time = strtotime($time['start']);
            $end_time = strtotime($time['end']);
            $count = M('eaterplanet_ecommerce_pop_adv')->where(" begin_time <= ".$end_time." and end_time >=".$begin_time)->count();
            if($count > 0){
                show_json(0, array('message' => '广告时间与其他广告重叠'));
            }
            if($data['is_index_show'] == 1){
                $time = $end_time-$begin_time;
                $show_hour = floatval($data['show_hour'])*60*60;

                if($show_hour > $time){
                    show_json(0, array('message' => '出现频次时间间隔不能大于投放时间'));
                }
            }

			D('Seller/Popadv')->update($_GPC);
			D('Seller/Redisorder')->sysnc_popadv_list();
			show_json(1, array('url' => U('popadv/index')));
		}
		$item = array();
		$item['begin_time'] = time();
		$item['end_time'] = time() + 24*60*60;
		$this->item = $item;

		$membercount = M('eaterplanet_ecommerce_member')->where( array('groupid' => 0 ) )->count();
		$list = array(
				array('id' => 'default', 'groupname' => '默认分组', 'membercount' => $membercount )
		);
		$condition = '  ';
		$alllist = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member_group WHERE 1 ' . $condition . ' ORDER BY id asc' );
		foreach ($alllist as &$row ) {
			$row['membercount'] = M('eaterplanet_ecommerce_member')->where("find_in_set(".$row['id'].",groupid)")->count();
		}
		$list = array_merge($list, $alllist);
		$this->list = $list;
		$this->membercount = $membercount;

		$this->display("popadv_add");
	}

	public function add_adv(){
		$_GPC = I('request.');
		$this->num = $_GPC['num'];
		$html = $this->fetch('Popadv/popadv_one');
		echo json_encode(array(
				'code' => 0,
				'html' => $html
		));
		die();
	}

	/**
	 * 更新弹窗广告状态
	 */
	public function change_status(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = $_GPC['ids'];
		}
		if( is_array($id) )
		{
			$items = M('eaterplanet_ecommerce_pop_adv')->field('id')->where( array('id' => array('in', $id)) )->select();
		}else{
			$items = M('eaterplanet_ecommerce_pop_adv')->field('id')->where( array('id' =>$id ) )->select();
		}
		if (empty($items)) {
			$items = array();
		}
		foreach ($items as $item) {
			M('eaterplanet_ecommerce_pop_adv')->where( array('id' => $item['id']) )->save( array('status' => intval($_GPC['status'])) );
		}
		D('Seller/Redisorder')->sysnc_popadv_list();
		show_json(1, array('url' => U('popadv/index')));
	}

	/**
	 * 删除弹窗广告
	 */
	public function delete_popadv(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);
		if (empty($id)) {
			$id = $_GPC['ids'];
		}
		if( is_array($id) )
		{
			$items = M('eaterplanet_ecommerce_pop_adv')->field('id')->where( array('id' => array('in', $id)) )->select();
		}else{
			$items = M('eaterplanet_ecommerce_pop_adv')->field('id')->where( array('id' =>$id ) )->select();
		}
		if (empty($item)) {
			$item = array();
		}
		foreach ($items as $item) {
			M('eaterplanet_ecommerce_pop_adv')->where( array('id' => $item['id']) )->delete();
			M('eaterplanet_ecommerce_pop_adv_list')->where( array('ad_id' => $item['id']) )->delete();
		}
		D('Seller/Redisorder')->sysnc_popadv_list();
		show_json(1, array('url' => U('popadv/index')));
	}

	/**
	 * 编辑弹窗广告
	 */
	public function edit_popadv(){
		$_GPC = I('request.');

		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$item = M('eaterplanet_ecommerce_pop_adv')->where( array('id' =>$id ) )->find();
			$adv_list = M('eaterplanet_ecommerce_pop_adv_list')->where( array('ad_id' => $id) )->select();
			$this->item = $item;

			$this->adv_list = $adv_list;

			$membercount = M('eaterplanet_ecommerce_member')->where( array('groupid' => 0 ) )->count();
			$list = array(
					array('id' => 'default', 'groupname' => '默认分组', 'membercount' => $membercount )
			);
			$condition = '  ';
			$alllist = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'eaterplanet_ecommerce_member_group WHERE 1 ' . $condition . ' ORDER BY id asc' );
			foreach ($alllist as &$row ) {
				$row['membercount'] = M('eaterplanet_ecommerce_member')->where("find_in_set(".$row['id'].",groupid)")->count();
			}
			$list = array_merge($list, $alllist);
			$this->list = $list;
			$this->membercount = $membercount;

			if(!empty($item['member_id'])){
				$user_list = M('eaterplanet_ecommerce_member')->where("member_id in (".$item['member_id'].")")->select();
				$this->user_list = $user_list;
			}
		}

		if (IS_POST) {
			$data = $_GPC['data'];
			$time = $_GPC['time'];
			$open_redis_server = D('Home/Front')->get_config_by_name('open_redis_server');
			if($open_redis_server != 1){
				show_json(0, array('message' => '请开启redis服务'));
			}
			if (empty($data['adv_name'])) {
				show_json(0, array('message' => '活动名称不能为空'));
			}
			$aid = $_GPC['aid'];
			$thumb = $_GPC['thumb'];
			for($i = 0;$i < count($aid);$i++){
				if(empty($thumb[$i])){
					show_json(0, array('message' => '广告'.$aid[$i].'图片不能为空'));
				}
				if(empty($data['link_'.$aid[$i]])){
					show_json(0, array('message' => '广告'.$aid[$i].'链接不能为空'));
				}
			}

			$begin_time = strtotime($time['start']);
			$end_time = strtotime($time['end']);
			$count = M('eaterplanet_ecommerce_pop_adv')->where(" begin_time <= ".$end_time." and end_time >=".$begin_time." and id <> ".$data['id'])->count();
			if($count > 0){
				show_json(0, array('message' => '广告时间与其他广告重叠'));
			}
			if($data['is_index_show'] == 1){
			    $time = $end_time-$begin_time;
			    $show_hour = floatval($data['show_hour'])*60*60;

			    if($show_hour > $time){
			        show_json(0, array('message' => '出现频次时间间隔不能大于投放时间'));
			    }
			}

			D('Seller/Popadv')->update($_GPC);

			D('Seller/Redisorder')->sysnc_popadv_list();

			show_json(1, array('url' => U('popadv/index')));
		}

		$this->display("popadv_edit");
	}
	//查看活动统计
	public function popadv_click(){
		$_GPC = I('request.');
		$id = intval($_GPC['id']);
		$item = M('eaterplanet_ecommerce_pop_adv')->where( array('id' =>$id ) )->find();
		$this->item = $item;
		$this->display("popadv_click");
	}
}
?>
