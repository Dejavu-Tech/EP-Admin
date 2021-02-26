<?php
namespace Home\Controller;

/**
 * IndexController
 * 系统信息管理
 */
class IndexController extends CommonController {

	public function wl_jieshao()
	{
		$this->display();
	}
	public function wl_xlmy()
	{
		$this->display();
	}

	public function wl_baom()
	{
			$this->assign('kangurl', U('Index/wl_baom_ins'));
		$this->display();
	}
	public function wl_baom_ins()
	{
		$username = htmlspecialchars($_POST['username']);
		$mobile = htmlspecialchars($_POST['mobile']);
		$sex = intval($_POST['sex']) == 1 ? '男': '女';
		$area = htmlspecialchars($_POST['area']);
		$lbtype = htmlspecialchars($_POST['lbtype']);
		$note = htmlspecialchars($_POST['note']);

		$data = array('username' => $username,'mobile' => $mobile,'sex' => $sex,'area' => $area,'lbtype' => $lbtype,'note' => $note,'addtime' => time());
		M('wl_bao')->add($data);
		echo json_encode(array('code'=>1));
		die();
	}


	public function wl_cbsx()
	{
		$list = M('wl_comment')->order('id desc ')->select();
		$this->assign('list', $list);
		$this->assign('kangurl', U('Index/wl_cbsx_ins'));
		$this->display();
	}

	public function wl_cbsx_ins()
	{
		//mess
		$mess = htmlspecialchars($_POST['mess']);
		$data = array('content' => $mess,'addtime' => time());
		M('wl_comment')->add($data);
		echo json_encode(array('code'=>1));
		die();
	}

	public function subbaoview()
	{
		$ar = array('ret'=>0);

		$param = array();
		$param['username'] = htmlspecialchars($_POST['username']);
		$param['mobile'] = htmlspecialchars($_POST['mobile']);
		$param['yixiang'] = htmlspecialchars($_POST['yixiang']);
		$param['area'] = htmlspecialchars($_POST['area']);
		$param['addtime'] = time();

		$rs = M('baoming')->add($param);

		if($rs)
		{
			$ar['ret'] = 1;
		}

		echo json_encode($ar);
		die();
	}

	public function auth()
	{
		$secret = 'mnwjxpx20150826';
		$openid = trim($_GET['openid']);
		$key = trim($_GET['key']);
		$ch_key = md5($secret.$openid);
		if($ch_key != $key)
		{
			echo '<script>alert("请关注闽南网，并点击菜单中驾校评选，进入投票");</script>';
			die();//这里以后放一片文章地址
		}
		$user_info = M('user')->where("openid ='{$openid}'")->find();

		if(!$user_info) {
			$param = array();
			$param['openid'] = $openid;
			$param['addtime'] = time();
			M('user')->add($param);
		}
		$this->redirect(U('Index/index','openid='.$openid));
	}

	public function wan_notice()
	{
		$this->display();
	}
	/**
		从公众号进入的
	**/
	public function wan_index()
	{
		//$openid = 'DJKSDHUNCDNSDDSJDJH';
		$secret = 'mnwjxpx20150826';
		$openid = trim($_GET['openid']);
		$key = trim($_GET['key']);
		//$key = 'fa07413fdab048928525233486e78586';
		$ch_key = md5($secret.$openid);

		//cookie('name',array('name1','name2'));

		if($ch_key != $key)
		{
			$user_info = cookie('wan_userinfo');
			if(empty($user_info['openid']))
			{
				$this->redirect(U('Index/wan_notice'));
				die();//这里以后放一片文章地址
			}else {
				$openid = $user_info['openid'];
			}
		}

		$param = array(
			'openid' => $openid,
		);
		$info = M('mobile')->where(array('openid' => $openid))->find();
		if($info)
		{
			$param['mobile'] = $info['mobile'];
		} else {
			$data_arr = array();
			$data_arr['openid'] = $openid;
			$mobile = cookie('wan_other_mobile');
			if(!$mobile)
			{
				$mobile = '';
			}
			$data_arr['mobile'] = $mobile;
			$param['mobile'] = '';
			M('mobile')->add($data_arr);
		}

		cookie('wan_userinfo',$param,86400*100);
		$item_list = M('item')->select();

		$item_first = array_slice($item_list,0,1);
		$item_second = array_slice($item_list,1,1);
		$length = count($item_list);
		$del_item = array_slice($item_list,2,$length - 2);

		$this->assign('item_first', $item_first);
		$this->assign('item_second', $item_second);
		$this->assign('del_item', $del_item);

		$this->assign('item_list', $item_list);
		$this->assign('kangurl', U('Index/wan_openorder'));
		$this->assign('bindurl', U('Index/wan_bindmobile'));
		$this->display();
	}
	/**
		拆散情侣
		绑定手机号
	**/
	public function cai_bindmobile()
	{
		$username = htmlspecialchars($_POST['username']);
		$mobile = htmlspecialchars($_POST['mobile']);
		$user_info = M('cais_user')->where( array( 'username' => $username,'mobile' => $mobile ) )->find();
		$result = array('code' => 1);
		if($user_info)
		{
			$param = array();
			$param = array( 'mobile' => $mobile );
			cookie('cai_userinfo',$param,86400*100);
		} else {
			$data = array();
			$data['username'] = $username;
			$data['mobile'] = $mobile;
			$data['addtime'] = time();
			$rs = M('cais_user')->add($data);
			if(!$rs)
			{
				$result['code'] = 2;
			}else {
				
				$param = array();
				$param = array( 'mobile' => $mobile );
				cookie('cai_userinfo',$param,86400*100);
			}
			$param = array();
			$param = array( 'mobile' => $mobile );
		}
		echo json_encode($result);
		die();
	}
	/**
		拆散情侣
		比赛结果入库
	**/
	public function cai_addrecord()
	{
		$score = intval($_POST['score']);
		//score:45
		$result = array();
		$user_info = cookie('cai_userinfo');
		if(!empty($user_info['mobile']))
		{
			$mobile = $user_info['mobile'];
			//cais_records
			$record = M('cais_records')->where( array('mobile' => $mobile)  )->find();

			if($record) {
				if ($record['record'] <= $score) {
					$data = array();
					$data['record'] = $score;
					$data['addtime'] = time();
					$rs = M('cais_records')->where( array('mobile' => $mobile)  )->save($data);

					if($rs){
						$result['code'] = 2;//更新成功
					} else {
						$result['code'] = 3;//更新出错
					}
				}
			} else {
				$data = array();
				$data['mobile'] = $mobile;
				$data['record'] = $score;
				$data['addtime'] = time();
				$rs = M('cais_records')->add($data);
				if($rs){
					$result['code'] = 2;//更新成功
				} else {
					$result['code'] = 3;//更新出错
				}
			}

			echo json_encode($result);
			die();

		}else {
			$result['code'] = 1;//未注册过
			echo json_encode($result);
			die();
		}
	}

	/**
		拆散情侣
		排行榜
	**/
	public function cai_top()
	{
		$rank_list = M('cais_records')->order('record desc,addtime')->limit('100')->select();
		$mobile = '';
		$count = 100;
		$user_info = cookie('cai_userinfo');
		if(empty($user_info['mobile']))
		{
			$mobile = '';
		}else {
			$mobile = $user_info['mobile'];
		}
		
		if(!empty($mobile))
		{
			$info = M('cais_records')->where( array('mobile' => $mobile) )->find();
			$count = M('cais_records')->where(' record> '.$info['record'])->count();
		}
		foreach($rank_list as $key => $val)
		{
			$val['mobile'] = substr($val['mobile'],0,3).'****'.substr($val['mobile'],-3,3);
			$rank_list[$key] = $val;
		}
		$result = array('list' =>$rank_list,'count' => $count );
		echo json_encode($result);
		die();
	}

	/**
		拆散情侣
		检测是否注册过
	**/
	public function cai_checkmobile()
	{
		$result = array();
		$user_info = cookie('cai_userinfo');
		if(empty($user_info['mobile']))
		{
			$result['code'] = 2;//需要弹窗绑定手机号
		}else {
			$result['code'] = 1;//已经注册过了
		}
		echo json_encode($result);
		die();
	}

	/**
		开始绑定手机号
	**/
	public function wan_bindmobile()
	{
		$result = array('code' => 0);
		$user_info = cookie('wan_userinfo');
		if(empty($user_info['openid']))
		{
			$result['code'] = 2;//需要跳转到授权页面
			$result['url'] = U('Index/wan_notice');
			echo json_encode($result);
			die();
		}
		$mobile = htmlspecialchars($_POST['mobile']);
		$param = array( 'mobile' => $mobile );
		M('mobile')->where( array('openid' => $user_info['openid'] ) )->save($param);

		$user_info['mobile'] = $mobile;
		cookie('wan_userinfo',$param,86400*100);
		$result['code'] = 1;
		echo json_encode($result);
		die();
	}

	/**
		帮忙别人砍价,绑定手机号
	**/
	public function wan_other_bindmobile()
	{
		$result = array( 'code' => 0);
		$mobile = htmlspecialchars($_POST['mobile']);

		cookie('wan_other_mobile',$mobile,86400*100);

		$user_info = cookie('wan_userinfo');
		if(!empty($user_info) && empty($user_info['mobile']))
		{
			$user_info['mobile'] =$mobile;

			cookie('wan_userinfo',$user_info,86400*100);
		}

		$result['code'] = 1;
		echo json_encode($result);
		die();
	}

	/**
		帮忙别人砍价
	**/
	public function wan_other_kang()
	{
		$result = array('code' => 0);
		$mobile  = cookie('wan_other_mobile');
		if(empty($mobile))
		{
			$user_info = cookie('wan_userinfo');
			if(!empty($user_info['mobile']))
			{
				$mobile = $user_info['mobile'];
			} else {
				$result['code'] = 2;//需要绑定一下手机号
				echo json_encode($result);
				die();
			}
		}
		$order_id = intval($_POST['order_id']);
		$order_info = M('order')->where('id='.$order_id)->find();
		if(empty($order_info))
		{
			$result['code'] = 3;
			echo json_encode($result);//订单不存在，非法操作
			die();
		}
		if($order_info['sy_price']<=0)
		{
			$result['code'] = 4;
			echo json_encode($result);//已成功
			die();
		}
		$item = M('item')->where('id='.$order_info['item_id'].' and num >0 ')->find();
		if(!$item)
		{
			$result['code'] = 5;
			echo json_encode($result);//商品已经抢光了
			die();
		}

		$order_kang = M('order_kang')->where( array('mobile' => $mobile, 'order_id' => $order_id ) )->find();

		if($order_kang)
		{
			$result['code'] = 6;
			echo json_encode($result);//您已经砍过了
			die();
		}

		//$order_kang
		$kang_arr  = array();
		$kang_arr['order_id'] = $order_id;
		$kang_arr['money'] = 1;
		if($order_info['item_id'] == 1)
		{
			$kang_arr['money'] = 3;
		}
		$kang_arr['mobile'] = $mobile;
		$kang_arr['addtime'] = time();
		M('order_kang')->add($kang_arr);

		M('order')->where('id='.$order_id)->setDec('sy_price'); //

		if($order_info['item_id'] == 1)
		{
			M('order')->where('id='.$order_id)->setDec('sy_price'); //
			M('order')->where('id='.$order_id)->setDec('sy_price'); //
		}

		$order_info = M('order')->where('id='.$order_id)->find();

		if($order_info['sy_price']<=0)
		{
			M('order')->where('id='.$order_id)->save( array('state' =>1) );

			M('item')->where('id='.$order_info['item_id'])->setDec('num'); //

		}

		$result['code'] = 1;//砍价成功
		$result['sy_price'] = $order_info['sy_price'];
		echo json_encode($result);
		die();
	}
	//http://baby2015.mnw.cn/index.php?m=&c=Index&a=wan_detail&item_id=1

	public function wan_detailover()
	{
		$item_id = intval($_GET['item_id']);
		$item_info = M('item')->where('id='.$item_id)->find();

		$this->assign('kangurl', U('Index/wan_other_kang'));
		$this->assign('bindurl', U('Index/wan_other_bindmobile'));
		$pross = 100;
		$is_me = 1;
		$has_kang = 1;

		$jianglist = M('order')->where('item_id='.$item_id)->order('state desc,sy_price asc')->limit(20)->select();

		$this->assign('is_me', $is_me);
		$this->assign('jianglist', $jianglist);
		$this->assign('has_kang', $has_kang);

		//$this->assign('kangurl', U('Index/wan_openorder'));
		$this->assign('bangurl', U('Index/wan_other_kang'));
		$this->assign('bindmobileurl', U('Index/wan_other_bindmobile'));

		$this->assign('pross', $pross);
		$this->assign('item_info', $item_info);

		$this->assign('is_over', 1);


		$this->display('wan_detail');

	}
	public function wan_detail()
	{
		$item_id = intval($_GET['item_id']);
		$order_id = intval($_GET['order_id']);


		$item_info = M('item')->where('id='.$item_id)->find();
		$order_info = M('order')->where('id='.$order_id)->find();
		if(empty($order_info))
		{
			$this->redirect(U('Index/wan_index'));
			die();//这里以后放一片文章地址
		}
		$this->assign('kangurl', U('Index/wan_other_kang'));
		$this->assign('bindurl', U('Index/wan_other_bindmobile'));
		$pross = round(($item_info['price']-$order_info['sy_price']) / $item_info['price']  *100,2);


		if($pross > 100)
		{
			$pross = 100;
		}
		$is_me = 0;//是否是当前的人的
		$mobile  = cookie('wan_other_mobile');
		$user_info = cookie('wan_userinfo');
		if(!empty($user_info['openid']))
		{
			$order_me = M('order')->where( array('openid'=>$user_info['openid'],'id' =>$order_id,'item_id' =>$item_id ) )->find();

			if($order_me)
			{
				$is_me = 1;
			}
			if(empty($mobile))
				$mobile = $user_info['mobile'];
		}
		//var_dump($is_me,M('order')->getLastSql());die();
		//是否已经砍过
		//$user_info['mobile']
		$has_kang = 0;

		if(!empty($mobile))
		{
			$order_kang = M('order_kang')->where( array('order_id' => $order_id,'mobile' => $mobile) )->find();
			if($order_kang)
			{
				$has_kang = 1;
			}
		}
		//var_dump($has_kang,$mobile);die();
		$jianglist = M('order')->where('item_id='.$item_id)->order('state desc,sy_price asc')->limit(20)->select();


		$this->assign('is_me', $is_me);
		$this->assign('jianglist', $jianglist);
		$this->assign('has_kang', $has_kang);

		//$this->assign('kangurl', U('Index/wan_openorder'));
		$this->assign('bangurl', U('Index/wan_other_kang'));
		$this->assign('bindmobileurl', U('Index/wan_other_bindmobile'));

		$this->assign('pross', $pross);
		$this->assign('item_info', $item_info);
		$this->assign('order_info', $order_info);
		$this->assign('is_over', 0);

		$this->display();

	}

	/**
		开启一个砍价链接
	**/
	public function wan_openorder()
	{
		$result =array('code' => 0);

		$user_info = cookie('wan_userinfo');
		if(empty($user_info) || empty($user_info['mobile']))
		{
			//需要绑定手机号
			$result['code'] = 1;
			echo json_encode($result);
			die();
		}
		$item_id = trim($_POST['item_id']);

		$item = M('item')->where('id='.$item_id.' and num >0 ')->find();

		if($item)
		{
			$in_order = M('order')->where( array('item_id' => $item_id,'openid' => $user_info['openid'],'state' => 0) )->find();
			if($in_order)
			{
				$result['code'] = 3;//当前商品正在砍价中
				$result['order_id'] = $in_order['id'];
				echo json_encode($result);
				die();
			}else {
				$order_param = array();
				$order_param['openid'] = $user_info['openid'];
				$order_param['item_id'] = $item_id;
				if($item_id == 1)
				{
					$order_param['sy_price'] = $item['price'] - 3;
				}else
					$order_param['sy_price'] = $item['price'] - 1;

				$order_param['mobile'] = $user_info['mobile'];
				$order_param['state'] = 0;
				$order_param['stattime'] = time();
				$order_param['endtime'] = time();
				$insrt_id = M('order')->add($order_param);

				//$order_kang
				$kang_arr  = array();
				$kang_arr['order_id'] = $insrt_id;
				$kang_arr['mobile'] = $user_info['mobile'];

				if($item_id == 1)
				{
					$kang_arr['money'] = 3;
				}else
					$kang_arr['money'] = 1;


				$kang_arr['addtime'] = time();
				$rs = M('order_kang')->add($kang_arr);


				$result['code'] = 5;//自己砍价成功
				$result['order_id'] = $insrt_id;
				echo json_encode($result);
				die();
			}
		} else {
			$result['code'] = 2;//商品数量已经抢光
			echo json_encode($result);
			die();
		}

	}


	/**
     * 网站，服务器基本信息
     * @return
     */
    public function index(){
        $openid = $_GET['openid'];
		$user_info = M('user')->where("openid ='{$openid}'")->find();
		if(!$user_info) {
			echo '<script>alert("请关注闽南网，并点击菜单中驾校评选，进入投票");</script>';
			die();//这里以后放一片文章地址
		}
		$cate_id = isset($_GET['cid']) ? intval($_GET['cid']) : 1;
        $p = isset($_GET['p']) ? intval($_GET['p']) : 1;
		$size = 10;
		$off_set = ($p -1) * $size;
		$is_ajax = isset($_GET['is_ajax']) ? true: false;
		$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
		$now_time = time();
		$where = 'begin_time<'.$now_time.' and end_time > '.$now_time;

		if(!empty($keyword))
		{
			$where .= ' and  (username LIKE "%'.$keyword.'%" or id Like "%'.$keyword.'%")  ';
		}

		if($cate_id > 0)
		{
			$where .= '  and cid ='.$cate_id;
		}
		$tou_list = array();
		$tou_list = M('toupiao')->where($where)->order('vote_count desc')->limit($off_set,$size)->select();

		$tou_list_left = array();
		$tou_list_right = array();
		$i = 1;

		$result_list = array();
		if(!empty($tou_list))
		{
			foreach($tou_list as $tou)
			{
				if($i % 2 == 1)
				{
					$tou_list_left[] = $tou;
				} else {
					$tou_list_right[] = $tou;
				}
				$i++;
			}
			$result_list['tou_list_left'] = $tou_list_left;
			$result_list['tou_list_right'] = $tou_list_right;
		}

		if($is_ajax) {
			$result = array('ret' =>1);
			if(!empty($result_list)) {
				$result = array('ret' =>0);
				$result['list'] = $result_list;
			}
			echo json_encode($result);
			die();
		}

		$this->assign('cate_id', $cate_id);

		$this->assign('result_list', $result_list);
        $this->assign('toup', U('Index/toupsub'));
		$this->assign('s_url', U('Index/index'));
		$this->assign('openid', $openid);


		$this->assign('siteurl', 'http://'.$_SERVER['HTTP_HOST']);

		$this->display();
    }

	public function touview()
	{
		$openid = htmlspecialchars($_GET['openid']);
		$tid = intval($_GET['id']);
		$tou_info = M('toupiao')->where("id ='{$tid}'")->find();
		$this->assign('toupsuburl', U('Index/toupsub','openid='.$openid));
		$this->assign('tou_info', $tou_info);
		$this->display();
	}

	public function weixinshow()
	{
		$cate_id = isset($_GET['cid']) ? intval($_GET['cid']) : 0;

        $p = isset($_GET['p']) ? intval($_GET['p']) : 1;
		$size = 10;
		$off_set = ($p -1) * $size;
		$is_ajax = isset($_GET['is_ajax']) ? true: false;
		$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
		$where = '1=1';
		if(!empty($keyword))
		{
			$where .= ' and  username LIKE "%'.$keyword.'%"';
		}

		if($cate_id > 0)
		{
			//$cate_arr = array($cate_id=>$cate_arr[$cate_id]);
			$where .= '  and cid ='.$key;
		}
		$tou_list = array();

		$tou_list = M('toupiao')->where($where)->order('id asc')->limit($off_set,$size)->select();
		$tou_list_left = array();
		$tou_list_right = array();
		$i = 1;

		$result_list = array();
		if(!empty($tou_list))
		{
			foreach($tou_list as $tou)
			{
				if($i % 2 == 1)
				{
					$tou_list_left[] = $tou;
				} else {
					$tou_list_right[] = $tou;
				}
				$i++;
			}
			$result_list['tou_list_left'] = $tou_list_left;
			$result_list['tou_list_right'] = $tou_list_right;
		}




		if($is_ajax) {
			$result = array('ret' =>1);
			if(!empty($result_list)) {
				$result = array('ret' =>0);
				$result['list'] = $result_list;
			}
			echo json_encode($result);
			die();
		}

		$this->assign('result_list', $result_list);
        $this->assign('toup', U('Index/toup'));
		$this->assign('s_url', U('Index/weixinshow'));



		$this->assign('siteurl', 'http://'.$_SERVER['HTTP_HOST']);

		$this->display();
	}
	public function baommote()
	{
		$this->assign('baom', U('Index/baom'));
        $this->assign('toup', U('Index/toup'));

		$this->assign('siteurl', $_SERVER['HTTP_HOST']);
		 $this->assign('suburl', U('Index/baosubmote'));
		 $this->assign('toupurl', U('Index/toup'));
		$this->assign('uppicurl', U('Index/uppic'));
		$this->display();
	}

	public function baosubmote()
	{
		$data = $_POST;
		foreach($data as $key => $val)
		{
			if($key == 'team')
			{
				$data[$key] = $val == 1 ? '单人' : '组队';
			}
			if($key == 'sex')
			{
				$data[$key] = $val == 1 ? '男' : '女';
			}
			if($key == 'thumb_url' || $key == 'image_url' )
			{
				$data[$key] ='http://baby2015.mnw.cn/'.$val;
			}
			$data[$key] = htmlspecialchars($data[$key]);
		}



		$result = array('code'=>1,'mes' => '');

		$param = $data;
		$param['addtime'] = time();
		$insrt_id = M('mote')->add($param);


		if($insrt_id) {
			$result['code'] = 0;
			$result['insrt_id'] = $insrt_id;
			$result['mes'] = '报名成功.';
			echo json_encode($result);
			die();
		} else {
			$result['mes'] = '插入数据库失败.';
			echo json_encode($result);
			die();
		}

	}

	public function baom2017()
	{
		$this->assign('baom', U('Index/baom'));
        $this->assign('toup', U('Index/toup'));

		$this->assign('siteurl', $_SERVER['HTTP_HOST']);
		 $this->assign('suburl', U('Index/baosub2017'));
		 $this->assign('toupurl', U('Index/toup'));
		$this->assign('uppicurl', U('Index/uppic'));
		$this->display();
	}
	public function baosub2017()
	{
		
		$username = trim($_POST['babyname']);
		$sex = intval($_POST['sex']);
		$parname = trim($_POST['parname']);
		$mobile = trim($_POST['mobile']);
		$area = trim($_POST['area']);
		$age = 0;
		$birthday = trim($_POST['birthday']);
		$weixin = trim($_POST['weixin']);

		$parship = trim($_POST['parship']);
		$cid = intval($_POST['cid']);
		$thumb_url = trim($_POST['thumb_url']);
		$image_url = trim($_POST['image_url']);


		$result = array('code'=>1,'mes' => '');

		//$result['mes'] = '报名已结束';
		//echo json_encode($result);
		//die();
		if(empty($username)) {
			$result['mes'] = '姓名不能为空';
			echo json_encode($result);
			die();
		}
		if(empty($parname)) {
			$result['mes'] = '联系人姓名不能为空';
			echo json_encode($result);
			die();
		}
		if(empty($mobile)) {
			$result['mes'] = '手机号不能为空';
			echo json_encode($result);
			die();
		}

		if(empty($thumb_url)) {
			$result['mes'] = '请上传图片';
			echo json_encode($result);
			die();
		}
		$where_param = array();
		$where_param['parname'] = $parname;
		$where_param['username'] = $username;
		$where_param['mobile'] = $mobile;

		$idcard_info = M('toupiao2017')->where($where_param)->find();
		if($idcard_info) {
			$result['mes'] = '该宝宝已经报名，请勿重复报名.';
			echo json_encode($result);
			die();
		}
		$param = array();
		$param['created_at'] = time();
		$param['updated_at'] = time();

		$param['username'] = htmlspecialchars($username);
		$param['mobile'] = htmlspecialchars($mobile);

		$param['parname'] = htmlspecialchars($parname);
		$param['area'] = htmlspecialchars($area);
		$param['sex'] = htmlspecialchars($sex);
		$param['parship'] = htmlspecialchars($parship);
		$param['cid'] = htmlspecialchars($cid);
		
		$param['age'] = htmlspecialchars($age);
		$param['birthday'] = htmlspecialchars($birthday);
		$param['weixin'] = htmlspecialchars($weixin);
		
		$param['statu'] = 1;

		$param['idcard'] = time();
		$param['image_url'] = htmlspecialchars($image_url);
		$param['thumb_url'] = htmlspecialchars($thumb_url);
		$insrt_id = M('toupiao2017')->add($param);


		if($insrt_id) {
			$result['code'] = 0;
			$result['insrt_id'] = $insrt_id;
			$result['mes'] = '报名成功.';
			echo json_encode($result);
			die();
		} else {
			$result['mes'] = '插入数据库失败.';
			echo json_encode($result);
			die();
		}

	}
	
	public function baom()
	{
		$this->assign('baom', U('Index/baom'));
        $this->assign('toup', U('Index/toup'));

		$this->assign('siteurl', $_SERVER['HTTP_HOST']);
		 $this->assign('suburl', U('Index/baosub'));
		 $this->assign('toupurl', U('Index/toup'));
		$this->assign('uppicurl', U('Index/uppic'));
		$this->display();
	}
	public function baosub()
	{
		$username = trim($_POST['babyname']);
		$sex = intval($_POST['sex']);
		$parname = trim($_POST['parname']);
		$mobile = trim($_POST['mobile']);
		$area = trim($_POST['area']);

		$parship = trim($_POST['parship']);
		$cid = intval($_POST['cid']);
		$thumb_url = trim($_POST['thumb_url']);
		$image_url = trim($_POST['image_url']);


		$result = array('code'=>1,'mes' => '');

		//$result['mes'] = '报名已结束';
		//echo json_encode($result);
		//die();
		if(empty($username)) {
			$result['mes'] = '姓名不能为空';
			echo json_encode($result);
			die();
		}
		if(empty($parname)) {
			$result['mes'] = '家长姓名不能为空';
			echo json_encode($result);
			die();
		}
		if(empty($mobile)) {
			$result['mes'] = '手机号不能为空';
			echo json_encode($result);
			die();
		}

		if(empty($thumb_url)) {
			$result['mes'] = '请上传图片';
			echo json_encode($result);
			die();
		}
		$where_param = array();
		$where_param['parname'] = $parname;
		$where_param['username'] = $username;
		$where_param['mobile'] = $mobile;

		$idcard_info = M('toupiao')->where($where_param)->find();
		if($idcard_info) {
			$result['mes'] = '该宝宝已经报名，请勿重复报名.';
			echo json_encode($result);
			die();
		}
		$param = array();
		$param['created_at'] = time();
		$param['updated_at'] = time();

		$param['username'] = $username;
		$param['mobile'] = $mobile;

		$param['parname'] = $parname;
		$param['area'] = $area;
		$param['sex'] = $sex;
		$param['parship'] = $parship;
		$param['cid'] = $cid;
		$param['statu'] = 1;

		$param['idcard'] = time();
		$param['image_url'] = $image_url;
		$param['thumb_url'] = $thumb_url;
		$insrt_id = M('toupiao')->add($param);


		if($insrt_id) {
			$result['code'] = 0;
			$result['insrt_id'] = $insrt_id;
			$result['mes'] = '报名成功.';
			echo json_encode($result);
			die();
		} else {
			$result['mes'] = '插入数据库失败.';
			echo json_encode($result);
			die();
		}

	}

	public function toup()
	{
		$p = isset($_POST['p']) ? intval($_POST['p']) : 1;
		$size = 10;
		$off_set = ($p -1) * $size;
		$is_ajax = isset($_POST['is_ajax']) ? true: false;

		$tou_list = M('toupiao')->where()->order('vote_count desc')->limit($off_set.','.$size)->select();
		if($is_ajax) {
			$result = array('ret' =>1);
			if(!empty($tou_list)) {
				$result = array('ret' =>0);
				$result['list'] = $tou_list;
			}
			echo json_encode($result);
			die();
		}
		$tou_left = array();
		$tou_right = array();

		$is_left = true;
		foreach($tou_list as $val)
		{
			if($is_left)
				$tou_left[] = $val;
			else
				$tou_right[] = $val;

			$is_left = $is_left ? false : true;
		}

		$baom_count = M('toupiao')->count();
		$toup_count = M('toupiao_record')->count();

		$this->assign('baom_count', $baom_count);
		$this->assign('toup_count', $toup_count);

		$this->assign('siteurl', 'http://'.$_SERVER['HTTP_HOST']);
		$this->assign('tou_left', $tou_left);
		$this->assign('tou_right', $tou_right);
		$this->assign('baom', U('Index/baom'));
        $this->assign('toup', U('Index/toup'));

		$this->assign('toupsuburl', U('Index/toupsub'));
		$this->assign('tou_list', $tou_list);
		$this->display();
	}

	public function toupsub()
	{
		//data:{bid_str:bid_str,cid:cid,openid:openid},
		/**
		array(3) {
		  ["bid_str"]=>
		  string(40) "26,36,60,64,69,66,136,76,156,106,146,160"
		  ["cid"]=>
		  string(1) "1"
		  ["openid"]=>
		  string(28) "o0Vb0juyDM56F6ds32qv_cjiuCc8"
		}
		**/
		$cid = intval($_POST['cid']);
		$bid_str = $_POST['bid_str'];
		$bid_arr = explode(',', $bid_str);

		$openid = htmlspecialchars($_POST['openid']);

		$user_info = M('user')->where("openid ='{$openid}'")->find();

		$result = array();
		$result['ret'] = 0;
		if(!$user_info) {
			$result['msg'] = '请关注闽南网，并点击菜单中宝宝投票，进入投票';
			echo json_encode($result);
			die();//
		}
		if(empty($bid_arr)) {
			$result['msg'] = '请选择要投票的宝贝';
			echo json_encode($result);
			die();//
		}
		if($cid != 1 && $cid !=2)
		{
			$result['msg'] = '非法操作';
			echo json_encode($result);
			die();//
		}

		$ck_info = M('toupiao_record')->where(array('openid'=>$openid,'cid'=>$cid))->find();
		if($ck_info){
			$result['msg'] = '该组别您已经投过票了。';
			echo json_encode($result);
			die();//
		}
		/**
		$end_time = '2015-09-30 23:59:39';
		if(time() >= strtotime($end_time))
		{
			$result['msg'] = '投票已结束，感谢您的参与。';
			echo json_encode($result);
			die();
		}
		**/

		$tip = get_client_ip();
		$ipkey = md5($tip.$_SERVER['HTTP_USER_AGENT']);

		$no_time = strtotime(date('Y-m-d'));


		$need_arr = array();
		foreach($bid_arr as $val)
		{
			if(empty($need_arr) || !in_array($val,$need_arr) )
			{
				$need_arr[] = $val;
				$tid = intval($val);

				$param = array();
				$param['tid']  = $tid;
				$param['tip'] = $tip;
				$param['cid'] = $cid;
				$param['openid'] = $openid;
				$param['ipkey'] = $ipkey;
				$param['createtime'] = $no_time;
				M('toupiao_record')->add($param);

				M('toupiao')->where('id='.$tid)->setInc('vote_count',1);
			}
		}
		$result['ret'] = 1;
		$result['msg'] = '投票成功';

		echo json_encode($result);
		die();
	}



	public function uppic(){
        $upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     4194304;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
		$upload->savePath  =     ''; // 设置附件上传（子）目录
		$upload->thumb  =     true; // 设置附件上传（子）目录
		$upload->thumbMaxWidth  =  640;
		$upload->thumbPath = './Uploads/';
		$info   =   $upload->upload();
		foreach($info as $key => &$val)
		{
				$image = new \Think\Image();
				$image->open('./Uploads/'.$val['savepath'].$val['savename']);
				// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
				$val['thumb_url'] = './Uploads/'.date('Y-m-d').'/'.md5(time()).'.jpg';
				$thumb = $image->thumb(640, 640)->save($val['thumb_url']);
		}

		if(!$info) {// 上传错误提示错误信息
			$this->error($upload->getError());
		}else{// 上传成功
			echo json_encode($info);
		}
		die();
    }

}


?>
