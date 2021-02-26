<?php

namespace Addons\LuojiangKaoping\Controller;
use Mp\Controller\AddonsController;

/**
 * 洛江考评微官网后台管理控制器
 * @author 大鱼
 */
class WebController extends AddonsController {
	public function test()
	{
		//luojiang_achievement
		$all_list = M('luojiang_article')->select();
		Vendor('HyperDown.Parser');
		$parser = new \Parser();

		foreach( $all_list as $val )
		{
			$content = $parser->makeHtml($val['content']);
			M('luojiang_article')->where( array('id' => $val['id']) )->save( array('content' => $content) );
			//id
		}
		var_dump('success');
		die();
		//LuojiangKaoping
	}
	public function eventreport()
	{
		/**
		Vendor('HyperDown.Parser');
		$parser = new \Parser();
		$markdown = $parser->makeHtml("![](http://wx.mnw.cn/Uploads/Pictures/20180411/5acd6d78033a2.jpg)");



		Vendor('Michelf.Markdown');
		$html = \Michelf\Markdown::defaultTransform("![](http://wx.mnw.cn/Uploads/Pictures/20180411/5acd6d78033a2.jpg)");

		var_dump($markdown,$html);
		die();
		**/

		$custom = array(
			'options' => array(
				'lool_to_material' => array(
					'title' => '处理上报事件',
					'url' => U('addon/LuojiangKaoping/web/editeventreport', array('id'=>'{id}')),
					'class' => 'btn btn-primary btn-sm icon-edit'
				),
				'edit_fans' => array(
					'title' => '查看用户信息',
					'url' => U('Mp/Fans/edit_fans_byid', array('fansid'=>'{fans_id}')),
					'class' => 'btn btn-primary btn-sm icon-edit'
				)
			)
		);
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('事件上报', '', 'active')
			 ->setModel('luojiang_eventreport')
			 ->setListOrder('addtime desc')
			 ->setListMap(array('mpid'=>get_mpid()))
             ->setTip('<p id="msg_flush">暂无新消息</p>')
             ->setListSearch(array('nickname' => '会员名称'))
			 ->addListItem('fans_id', '会员名称', 'callback', array('callback_name'=>'get_fans_name'))
			 ->addListItem('event_type', '事件类型', 'enum', array('options'=>array('井盖缺失'=>'井盖缺失','道路破损'=>'道路破损','道路淘空'=>'道路淘空','陈年垃圾'=>'陈年垃圾','卫生死角'=>'卫生死角','公共设施'=>'公共设施','市容环境'=>'市容环境','其他情况'=>'其他情况')))
			 ->addListItem('descript', '事件描述')
			 ->addListItem('image_list', '图片列表', 'callback', array('callback_name'=>'get_images_str'))
			 ->addListItem('video_file', '视频', 'callback', array('callback_name'=>'get_video_str'))
			 ->addListItem('jiangli', '奖励')
			 ->addListItem('id', '位置', 'callback', array('callback_name'=>'get_message_content'))
			 ->addListItem('addtime', '创建时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
			 ->addListItem('reply_info', '处理结果', '', array('placeholder'=>'<font color="red">未处理</font>'))
			 ->addListItem('id', '操作', 'custom', $custom)
             ->addButton('导出', U('addon/LuojiangKaoping/web/loadexcel'), 'btn btn-primary')
             ->common_lists();
			echo '<script src="/Public/Mp/js/checkflush.js"></script>';
			echo "<style>.table-striped > tbody > tr:nth-child(odd) > td, .table-striped > tbody > tr:nth-child(odd) > th{border-top:1px solid #000; ;border-bottom:1px solid #000;}</style>";

	}
	public function get_message_content($id) {
		$map['id'] = $id;
		$map['mpid'] = get_mpid();
		$message = M('luojiang_eventreport')->where($map)->find();
		if(!empty($message['location_addr'])){
			return $message['location_addr'].'<br/><button type="button" class="btn btn-sm btn-success"  onclick="lookMap('.$message['lat'].','.$message['lng'].')">查看【位置】</button>';
		}else{
			return "未提供";
		}
	}

	public function downfile()
	{
		//.jpg  mp4
		//$mp_message_info['msgtype'] == 'video'
		$file_url = urldecode( I('get.file_url') );
		$type = urldecode( I('get.type') );

		$file_url = str_replace('/Uploads/','Uploads/',$file_url);
		$file_name = $file_url.'.'.$type;

		$file_sub_path= "/data/web/wx.mnw.cn/";
		$file_path=$file_sub_path.$file_name;

		$filectime = filectime($file_path);

		if( strpos($file_name,'jpg') !== false )
		{
			$img_arrs = explode('.', $file_name);
			$file_name = '微信图片_'.date('Ymd',$filectime).'_'.$filectime.'.'.$img_arrs[1];
		}
		else if( strpos($file_name,'png') !== false  ){
			$img_arrs = explode('.', $file_name);
			$file_name = '微信图片_'.date('Ymd',$filectime).'_'.$filectime.'.'.$img_arrs[1];
		}
		else if( strpos($file_name,'gif') !== false  ){
			$img_arrs = explode('.', $file_name);
			$file_name = '微信图片_'.date('Ymd',$filectime).'_'.$filectime.'.'.$img_arrs[1];
		}
		else if( strpos($file_name,'jpeg') !== false  ){
			$img_arrs = explode('.', $file_name);
			$file_name = '微信图片_'.date('Ymd',$filectime).'_'.$filectime.'.'.$img_arrs[1];
		}
		else if( strpos($file_name,'mp4') !== false  ){
			$img_arrs = explode('.', $file_name);
			$file_name = '微信视频_'.date('Ymd',$filectime).'_'.$filectime.'.'.$img_arrs[1];
		}
		else if($mp_message_info['msgtype'] == 'voice' ){
			$img_arrs = explode('.', $file_name);
			$file_name = '微信语音_'.date('Ymd',$filectime).'_'.$filectime.'.'.$img_arrs[1];
		}

		$fp=fopen($file_path,"r");
        $file_size=filesize($file_path);

		//var_dump($file_size);die();
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file_name);
        $buffer=1024;
        $file_count=0;
        //向浏览器返回数据
        while(!feof($fp) && $file_count<$file_size){
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);

		echo $file_url;
		die();

	}
	/**
     * 获取视频
     * @author lyf
     */
    public function get_video_str($video_file)
    {
        if(empty($video_file))
        {
            return '暂无';
        }else{
            return '<button type="button" class="btn btn-sm btn-warning icon-video" data-src="'.$video_file.'" onclick="lookVideo(this)">查看视频</button><a href="'.U('addon/HuianKaoping/web/downfile', array('type' =>'mp4', 'file_url' => urlencode($video_file) )).'" title="点击下载视频" target=_blank">【下载】</a>';
        }
    }
	/*
     * 导出
     */
    public function loadexcel()
    {
        header("content-type:text/html; charset=uft-8");

        if(IS_POST)
        {
            //date_added_begin:2017-08-02 0:00:00
            //date_added_end:2017-08-19 0:00:00
            $data = I('post.');
            $where = array();

            if(!empty($data['date_added_begin']))
            {
                $where['addtime'] = array('gt',strtotime($data['date_added_begin']) );
            }
            if(!empty($data['date_added_end']))
            {
                $where['addtime'] = array('lt',strtotime($data['date_added_end']) );
            }



            $s_time = strtotime($data['date_added_begin']);
            $e_time = strtotime($data['date_added_end']);


            if($s_time && $e_time)
            {
                $where['addtime'] = array('between',array($s_time,$e_time));
            }


            $list = M('luojiang_eventreport')->where($where)->order('id desc')->select();

            //$need_data = array( array('order_sn'=>1,'get_image'=>'http://wx.mnw.cn/Uploads/Pictures/2/2017-08-07/2_oA7ocv8o0sk-tPmG87fjPD25nQWs_1502095440.jpg'),array('order_sn'=>1,'get_image'=>'http://wx.mnw.cn/Uploads/Pictures/2/2017-08-07/2_oA7ocv8o0sk-tPmG87fjPD25nQWs_1502095440.jpg')  );

            $need_data = array();

            $xlsCell  = array(
                array('id','序号'),
                array('addtime','日期'),
                array('fans_id','微信名'),
				array('mobile','联系人电话'),
				array('jifen','积分'),

                array('descript','事件描述'),
                array('image_list','图否'),
				array('jiangli','奖励'),
                array('is_reply','反馈情况'),
                array('reply_info','反馈内容'),
                array('video_file','视频')

                /*

                array('contact_tel','联系电话'),
                array('jifen','积分'),
                array('nickname','微信名'),
                array('content','问题描述'),
                array('get_img','图否'),
                array('fankui','反馈情况'),
                array('fankui_tu','反馈图片'),
                */
            );
            $i = 1;
            foreach($list as $val)
            {
                $tmp_data = array();
                $tmp_data['id'] = $i;
                $tmp_data['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
				//openid
                $mp_fans = M('mp_fans')->field('nickname')->where( array('id' => $val['fans_id']) )->find();
                $tmp_data['fans_id'] = $mp_fans['nickname'];
				$tmp_data['mobile'] = $mp_fans['mobile'];

				if(  empty($val['reply_info']) ){
                    $tmp_data['jifen'] = 0;
                }else{
                    $tmp_data['jifen'] = 2;
                }


                $tmp_data['jiangli'] = $val['jiangli'];
                //$tmp_data['title'] = $val['title'];
                $tmp_data['descript'] = $val['descript'];
                if(  empty($val['reply_info']) ){
                    $tmp_data['is_reply'] = '否';
                }else{
                    $tmp_data['is_reply'] = '是';
                }
                $tmp_data['reply_info'] = $val['reply_info'];
                if(empty($val['video_file']))
                {
                    $tmp_data['video_file'] ='否';

                }else{
					$tmp_data['video_file'] ='是';
                    //$tmp_data['video_file'] ='http://'.$_SERVER['SERVER_NAME'].str_replace('./','',$val['video_file']);
                }



                //http://wx.mnw.cn/Uploads/Pictures/2/2017-08-07/2_oA7ocv8o0sk-tPmG87fjPD25nQWs_1502095440.jpg
                if(!empty($val['image_list']))
                {
                    $img_arr = explode(',',$val['image_list']);
                    if($val['image_list'] == ',undefined/undefined' || $val['image_list'] == 'undefined/undefined')
                    {
                        $img_url = '';
						$img_urls = array();
                    }else{
						$img_urls = explode(',', $val['image_list']);

						foreach($img_urls as $kk => $vv)
						{
							$vv = str_replace('./','/',$vv );

							$vv = '/data/web/wx.mnw.cn'.$vv;
							$img_urls[$kk] = $vv;
						}

                        //$img_url = str_replace('./','http://'.$_SERVER['SERVER_NAME'],$val['image_list']);
                    }
                    $tmp_data['image_list'] = $img_urls;
                }
                $i++;
                $need_data[] = $tmp_data;
            }
            $expTitle = date('Y-m-d H:i:s');


            export_excel($expTitle,$xlsCell,$need_data);

        }
        $this->display();
    }
	public function editeventreport()
	{

		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
		 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
		 ->addCrumb('处理上报事件', '', 'active')

		 ->setModel('luojiang_eventreport')
		 ->addFormField('jiangli', '上报奖励', 'text')
		 ->addFormField('reply_info', '处理意见', 'text')

		 ->setFormData( M('luojiang_eventreport')->find(I('get.id')) )
		 ->setEditMap( array('id'=>I('get.id')) )
		 ->setEditSuccessUrl( U('addon/LuojiangKaoping/web/eventreport') )
		 ->common_edit();
	}
	public function opinionsuggestion()
	{
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('意见建议', '', 'active')
			 ->setModel('luojiang_opinionsuggestion')
			 ->setListOrder('addtime desc')
			 ->setListMap(array('mpid'=>get_mpid()))
			 ->addListItem('fans_id', '会员名称', 'callback', array('callback_name'=>'get_fans_name'))
			 ->addListItem('opinion_name', '建议人姓名')
			 ->addListItem('contact', '建议人联系电话')
			 ->addListItem('email', '邮箱')
			 ->addListItem('title', '建议标题')
			 ->addListItem('content', '建议内容')
			 ->addListItem('image_list', '图片列表', 'callback', array('callback_name'=>'get_images_str'))
			 ->addListItem('reply_method', '回复方式', 'enum', array('options'=>array('1'=>'网上',2=>'电话',3=>'当面')))
			 //->addListItem('is_reply', '是否回复', 'enum', array('options'=>array(0=>'未回复',1=>'已回复')))
			 ->addListItem('addtime', '创建时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
			->addListItem('id', '操作', 'custom', array('options'=>array('edit_eventreport'=>array('处理意见建议', U('addon/LuojiangKaoping/web/editopinionsuggestion', array('id'=>'{id}')),'btn btn-primary btn-sm icon-edit',''))))
			//->addListItem('id', '操作', 'custom', array('options'=>array('edit_fans'=>array('编辑粉丝资料', U('Mp/Fans/edit_fans', array('openid'=>'{openid}')),'btn btn-primary btn-sm icon-edit',''))))
		     ->common_lists();
	}
	public function editopinionsuggestion()
	{
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
		 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
		 ->addCrumb('处理意见建议', '', 'active')

		 ->setModel('luojiang_opinionsuggestion')
		 ->addFormField('reply_info', '处理意见', 'text')

		 ->setFormData( M('luojiang_opinionsuggestion')->find(I('get.id')) )
		 ->setEditMap( array('id'=>I('get.id')) )
		 ->setEditSuccessUrl( U('addon/LuojiangKaoping/web/opinionsuggestion') )
		 ->common_edit();

	}
	public function newsnenter_two()
	{
		$options = array(
			'edit_fans'=>array('编辑', U('addon/LuojiangKaoping/web/editarticle', array('id'=>'{id}') )
			,'btn btn-primary btn-sm icon-edit',''),
			'delete' =>	array(
				'title' => '删除',
				'url' => U('addon/LuojiangKaoping/web/deletearticle', array('id'=>'{id}')),
				'class' => 'btn btn-danger btn-sm icon-delete'
			)
		);
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('新闻中心', '', 'active')
			 ->setModel('luojiang_article')
			 ->setListMap(array('type'=>2))
			 ->setListOrder('addtime desc')

			 ->addListItem('title', '标题')
			// ->addListItem('logo', '图片', 'image', array('attr'=>'width=50 height=50','placeholder'=>__ROOT__ . '/Public/Admin/img/noname.jpg'))
			 ->addListItem('cate_id', '所属分类', 'callback',array('callback_name'=>'get_cate_name'))
			 ->addListItem('sendtime', '发布时间')
			 ->addListItem('addtime', '创建时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
			 ->addListItem('id', '操作', 'custom', array('options'=>$options))
			 //->addButton('新增文章分类', U('addon/HuianKaoping/web/addarticlecate'), 'btn btn-primary')
			 ->addButton('新增文章', U('addon/LuojiangKaoping/web/addarticle_two'), 'btn btn-primary')
			 ->common_lists();
	}
	public function newsnenter()
	{
		$options = array(
			'edit_fans'=>array('编辑', U('addon/LuojiangKaoping/web/editarticle', array('id'=>'{id}') )
			,'btn btn-primary btn-sm icon-edit',''),
			'delete' =>	array(
				'title' => '删除',
				'url' => U('addon/LuojiangKaoping/web/deletearticle', array('id'=>'{id}')),
				'class' => 'btn btn-danger btn-sm icon-delete'
			)
		);
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('新闻中心', '', 'active')
			 ->setModel('luojiang_article')
			 ->setListMap(array('type'=>1))
			 ->setListOrder('addtime desc')

			 ->addListItem('title', '标题')
			 ->addListItem('logo', '图片', 'image', array('attr'=>'width=50 height=50','placeholder'=>__ROOT__ . '/Public/Admin/img/noname.jpg'))
			 ->addListItem('cate_id', '所属分类', 'callback',array('callback_name'=>'get_cate_name'))
			 ->addListItem('sendtime', '发布时间')
			 ->addListItem('addtime', '创建时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
			 ->addListItem('id', '操作', 'custom', array('options'=>$options))
			 //->addButton('新增文章分类', U('addon/HuianKaoping/web/addarticlecate'), 'btn btn-primary')
			 ->addButton('新增文章', U('addon/LuojiangKaoping/web/addarticle'), 'btn btn-primary')
			 ->common_lists();
	}

	/**
	 * 删除关键词回复
	 * @author 艾逗笔<765532665@qq.com>
	 */
	public function deletearticle() {

		M('luojiang_article')->where( array('id' =>I('get.id') ) )->delete();
		$this->success('删除成功');

	}
	public function votecenter()
	{
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('投票专区', '', 'active')
			 ->setModel('vote')

			 ->addListItem('title', '标题')
			 ->addListItem('begin_time', '开始时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
			 ->addListItem('end_time', '结束时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
			 ->addListItem('addtime', '添加时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))

			 ->addListItem('vote_id', '操作', 'custom', array('options'=>array(
				'edit_fans'=>array('编辑', U('addon/LuojiangKaoping/web/editvote', array('vote_id'=>'{vote_id}') ) ,'btn btn-primary btn-sm icon-edit',''),
				'add_votesubject'=>array('添加投票选项', U('addon/LuojiangKaoping/web/addvotesubject', array('vote_id'=>'{vote_id}') ) ,'btn btn-primary btn-sm icon-edit',''),
			 )))
		     ->addButton('新增投票', U('addon/LuojiangKaoping/web/addvote'), 'btn btn-primary')
			 ->common_lists();
	}
	public function addvote()
	{
		if( IS_POST )
		{
			$data = I('post.');
			$vote_data = array();
			$vote_data['mpid'] = get_mpid();
			$vote_data['title'] = $data['name'];
			$vote_data['begin_time'] = strtotime( $data['date_added_begin'] );
			$vote_data['end_time'] = strtotime( $data['date_added_end'] );
			$vote_data['addtime'] = time();
			M('vote')->add($vote_data);
			$this->success('添加投票成功', U('addon/LuojiangKaoping/web/votecenter'));
			die();
		}

		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('新闻中心', '', 'active')
			 ->display();
	}
	public function addvotesubject()
	{
		$vote_id = I('get.vote_id');
		$this->vote_id = $vote_id;

		if( IS_POST )
		{
			$data = I('post.');

			if( isset($data['edit_votesubject']) )
			{
				$has_key = array();
				$del_key = array();

				foreach( $data['attr_title'] as $key => $val)
				{
					$has_key[] = $key;
				}
				$vote_sub_all =  M('vote_subject')->where( array('vote_id' => $data['vote_id']) )->select();

				foreach($vote_sub_all as $vote_sub)
				{
					if( !in_array($vote_sub['sub_id'],$has_key ) )
					{
						$del_key[] = $vote_sub['sub_id'];
					}
				}
				//删除多余键
				if( !empty($del_key) )
				{
					M('vote_subject')->where( array('sub_id' => array('in', $del_key ) ) )->delete();
					//删除投票记录
					M('vote_record')->where( array('sub_id' => array('in', $del_key ) ) )->delete();
				}
				//开始更新和新增选项

				foreach($data['attr_title'] as $key => $val)
				{
					if( strpos($key, 'new') !== false )
					{
						//新增的模块
						$tmp_vote_subject = array();
						$tmp_vote_subject['vote_id'] = $data['vote_id'];
						$tmp_vote_subject['title'] = $val;
						$tmp_vote_subject['type'] = $data['type'][$key];
						$tmp_vote_subject['addtime'] = time();
						M('vote_subject')->add($tmp_vote_subject);
						$sub_id = M('vote_subject')->getLastInsID();

						$extra = $data['extra'][$key];
						$extra_arr = explode('|', $extra);
						foreach($extra_arr as $vv)
						{
							$xun_data = array();
							$xun_data['sub_id'] = $sub_id;
							$xun_data['titile'] = $vv;
							$xun_data['addtime'] = time();
							M('vote_xuan')->add($xun_data);
						}
					} else {
						//需要更新板块
						$tmp_vote_subject = array();
						$tmp_vote_subject['title'] = $val;
						$tmp_vote_subject['type'] = $data['type'][$key];
						M('vote_subject')->where( array('sub_id' =>$key ) ) ->save($tmp_vote_subject);

						//更新子内容
						//先判断原来有几个
						$vote_xuan_list = M('vote_xuan')->where( array('sub_id' => $key) )->order('xu_id asc')->select();

						$extra = array();
						$extra = $data['extra'][$key];
						$extra_arr = explode('|', $extra);
						foreach($vote_xuan_list as $xun_vo)
						{
							if( !empty($extra_arr) )
							{
								$tmp_xun = array_shift( $extra_arr );
								M('vote_xuan')->where( array('xu_id' => $xun_vo['xu_id']) )->save( array('titile' => $tmp_xun) );
							}else {
								//需要删除的
								M('vote_xuan')->where( array('xu_id' => $xun_vo['xu_id']) )->delete();
							}
						}
						//判断是否可以新增
						if( !empty($extra_arr) )
						{
							foreach($extra_arr as $vv)
							{
								$xun_data = array();
								$xun_data['sub_id'] = $key;
								$xun_data['titile'] = $vv;
								$xun_data['addtime'] = time();
								M('vote_xuan')->add($xun_data);
							}
						}
					}
				}
				$this->success('编辑投票选项成功', U('addon/HuianKaoping/web/votecenter'));
				die();
			}



			foreach($data['attr_title'] as $key => $val)
			{
				if( !empty($val) )
				{
					$tmp_vote_subject = array();
					$tmp_vote_subject['vote_id'] = $data['vote_id'];
					$tmp_vote_subject['title'] = $val;
					$tmp_vote_subject['type'] = $data['type'][$key];
					$tmp_vote_subject['addtime'] = time();
					M('vote_subject')->add($tmp_vote_subject);
					$sub_id = M('vote_subject')->getLastInsID();

					$extra = $data['extra'][$key];
					//vote_xuan
					$extra_arr = explode('|', $extra);
					foreach($extra_arr as $vv)
					{
						$xun_data = array();
						$xun_data['sub_id'] = $sub_id;
						$xun_data['titile'] = $vv;
						$xun_data['addtime'] = time();
						M('vote_xuan')->add($xun_data);
					}
				}
			}

			$this->success('新增投票选项成功', U('addon/HuianKaoping/web/votecenter'));
			die();

		}

		$vote_subject = M('vote_subject')->where( array('vote_id' => $vote_id) )->order('sub_id asc')->select();

		if( !empty($vote_subject) )
		{
			foreach( $vote_subject as $key=> $val )
			{
				$vote_xuan_list = M('vote_xuan')->where( array('sub_id' => $val['sub_id']) )->order('xu_id asc')->select();
				$xun_arr = array();

				foreach( $vote_xuan_list as $vv )
				{
					$xun_arr[] = $vv['titile'];
				}

				$val['xun_title'] =  implode('|', $xun_arr);
				$val['vote_xuan_list'] = $vote_xuan_list;
				$vote_subject[$key] = $val;
			}

			$this->vote_subject = $vote_subject;



			$this->addCrumb('洛江考评微官网', U('addon/HuianKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/HuianKaoping/web/eventreport'), '')
			 ->addCrumb('新闻中心', '', 'active')
			 ->display('editvotesubject');
		} else {
			$this->addCrumb('洛江考评微官网', U('addon/HuianKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/HuianKaoping/web/eventreport'), '')
			 ->addCrumb('新闻中心', '', 'active')
			 ->display();
		}
	}
	public function loadachievement()
	{
        $type = I('type');
		if( IS_POST )
		{
            set_time_limit(0);
			  if(isset($_FILES["file"]) && ($_FILES["file"]["error"] == 0)){


				  $excel_dir = './Uploads/Pictures/' . date('Y-m-d') . '/';
					if (!file_exists($excel_dir)) {
						$dirs = explode('/', $excel_dir);
						$dir = $dirs[0] . '/';
						for ($i=1, $j=count($dirs)-1; $i<$j; $i++) {
							$dir .= $dirs[$i] . '/';
							if (!is_dir($dir)) {
								mkdir($dir, 0777);
							}
						}
					}

				  $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);


				  $path = $excel_dir.'/'.md5($_FILES['file']['name'].mt_rand(1, 999)).'.'.$extension;
				  $rs = move_uploaded_file($_FILES["file"]["tmp_name"],$path);



				$file = $path;
				$type = pathinfo($file);
				$type = strtolower($type["extension"]);
				$type=$type==='csv' ? $type : 'Excel5';
				ini_set('max_execution_time', '0');
				vendor("PHPExcel.PHPExcel");
				// 判断使用哪种格式
				$objReader = \PHPExcel_IOFactory::createReader($type);
				$objPHPExcel = $objReader->load($file);
				$sheet = $objPHPExcel->getSheet(0);
				// 取得总行数
				$highestRow = $sheet->getHighestRow();
				// 取得总列数
				$highestColumn = $sheet->getHighestColumn();
				//循环读取excel文件,读取一条,插入一条
				$data=array();
				//从第一行开始读取数据
				for($j=1;$j<=$highestRow;$j++){
					if($j <=2) {
						continue;
					}
					//从A列读取数据
					for($k='A';$k<=$highestColumn;$k++){
						// 读取单元格
						$data[$j][]=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
					}
				}

				$result = $data;
				$get_data = array();
				if(!empty($result)) {
					foreach($result as $key => $val){

						//array(5) { [0]=> string(6) "村庄" [1]=> string(18) "2017-8月上半月"
						//[2]=> string(9) "小岞镇" [3]=> string(9) "新桥村" [4]=> float(92.5) }
						if(empty($val[0]))
						{
							continue;
						}
						//$val[1] = str_replace('上半月','01',$val[1]);
						//$val[1] = str_replace('下半月','15',$val[1]);



						//$val[1] = $val[1]->__toString();
						$val[1] .= ' 00:00:00';
						//$val[1] = str_replace('年','-',$val[1]);
						//$val[1] = str_replace('月','',$val[1]);

						$need_data = array();
						$need_data['name'] = $val[0];
						$need_data['kao_time'] = strtotime($val[1]);
						$need_data['zeren_danwei'] = $val[2];
						$need_data['stree'] = $val[3];
						$need_data['kaoping_dian'] = '';
						$need_data['chengji'] = $val[4];
                        if(I('type') == 2)
                        {
                            $need_data['type'] = 2;
                        }else{
                            $need_data['type'] = 1;
                        }
						if(empty($need_data['stree']))
                        {
                            $need_data['stree'] = '';
                        }
						$need_data['paiming'] = 0;
						$need_data['addtime'] = time();
						M('luojiang_achievement')->add($need_data);
					}
                    if(I('type') == 2){
                        $this->success('导入成绩成功', U('addon/LuojiangKaoping/web/achievementmanage_two'));
                    }else{
                        $this->success('导入成绩成功', U('addon/LuojiangKaoping/web/achievementmanage'));
                    }
					die();
				}
			  }
		}
        $this->assign('type', $type);
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('成绩管理', '', 'active')
			 ->display();
	}

	public function editvote()
	{
		// /vote_id/1
		$vote_id = I('get.vote_id');

		if( IS_POST )
		{
			$data = I('post.');

			$data = I('post.');
			$vote_data = array();
			$vote_data['mpid'] = get_mpid();
			$vote_data['title'] = $data['name'];
			$vote_data['begin_time'] = strtotime( $data['date_added_begin'] );
			$vote_data['end_time'] = strtotime( $data['date_added_end'] );

			M('vote')->where( array('vote_id' => $data['vote_id']) )->save($vote_data);

			$this->success('编辑投票成功', U('addon/HuianKaoping/web/votecenter'));
			die();
		}

		$vote = M('vote')->where( array('vote_id' => $vote_id) )->find();

		$this->vote = $vote;

		$this->addCrumb('洛江考评微官网', U('addon/HuianKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/HuianKaoping/web/eventreport'), '')
			 ->addCrumb('新闻中心', '', 'active')
			 ->display('addvote');
	}

	public function editachievement()
	{
		$id = I('get.id');
		$type = I('get.type');
		if( IS_POST )
		{
			$data = I('post.');


			$vote_data = array();
			$vote_data['name'] = $data['name'];
			$vote_data['kao_time'] = strtotime( $data['kao_time'] );
			$vote_data['zeren_danwei'] = $data['zeren_danwei'];
			$vote_data['stree'] = $data['stree'];
			$vote_data['content'] = ($data['content']);
			//$vote_data['kaoping_dian'] = $data['kaoping_dian'];
			$vote_data['chengji'] = $data['chengji'];
			//$vote_data['paiming'] = $data['paiming'];
			M('luojiang_achievement')->where( array('id' => $id) )->save($vote_data);

            if($data['type'] == 2)
            {
                $this->success('编辑成功', U('addon/LuojiangKaoping/web/achievementmanage_two'));
            }else{
                $this->success('编辑成功', U('addon/LuojiangKaoping/web/achievementmanage'));
            }

			die();
		}

        if($type == 2)
        {
            $this->setEditSuccessUrl( U('addon/LuojiangKaoping/web/achievementmanage_two') );
        }else{
            $this->setEditSuccessUrl( U('addon/LuojiangKaoping/web/achievementmanage') );
        }
        $this->assign('type', $type);
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
		 ->addCrumb('成绩管理', U('addon/LuojiangKaoping/web/achievementmanage'), '')
		 ->addCrumb('成绩管理', '', 'active')
		 ->setModel('luojiang_achievement')
		 ->addFormField('name', '类别', 'text')
		 ->addFormField('kao_time', '考评时间', 'date')
		 ->addFormField('zeren_danwei', '街道（乡镇）/责任单位', 'text')
		 ->addFormField('stree', '抽评点', 'text')
		 ->addFormField('content', '内容', 'editor')
		 //->addFormField('kaoping_dian', '考评点', 'text')
		 ->addFormField('chengji', '成绩', 'text')
		 //->addFormField('paiming', '排名', 'text')

		 ->setEditMap( array('id'=>I('get.id')))
		 ->common_edit();

	}
	public function get_cate_name($cate_id)
	{
		$category_info =  M('luojiang_articlecategory')->where( array('id' => $cate_id) )->find();
		return $category_info['name'];
	}

	public function editarticle()
	{
		$cateinfos = M('luojiang_articlecategory')->where( array('pid' => 0) )->select();

		$cate_arr = array();
		foreach($cateinfos as $val)
		{
			$cate_arr[$val[id]] = $val['name'];
		}

		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
		 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
		 ->addCrumb('编辑文章', '', 'active')
		 ->setModel('huian_article')
		 ->addFormField('title', '标题', 'text')
		 ->addFormField('link', '外链', 'text')
		 ->addFormField('logo', '图片', 'image')
		 ->addFormField('sendtime', '发布时间', 'time')
		 ->addFormField('cate_id', '所属分类', 'select', array('options'=>$cate_arr))
		 ->addFormField('content', '内容', 'editor')
		 ->setValidate(array(
				array('name', 'require', '标题不能为空')//,
				//array('logo', 'require', '请上传图片')
		   ))
		 ->setFormData( M('luojiang_article')->find(I('get.id')) )
		 ->setEditMap( array('id'=>I('get.id')) )
		 ->setEditSuccessUrl( U('addon/LuojiangKaoping/web/newsnenter') )
		 ->common_edit();


	}
	public function addarticlecate()
	{
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('添加文章分类', '', 'active')
			 ->setModel('luojiang_articlecategory')
			 ->addFormField('name', '名称', 'text')
			 //->addFormField('type', '类型', 'radio', array('options'=>array(1=>'普通订阅号',2=>'认证订阅号',3=>'普通服务号',4=>'认证服务号',5=>'测试号'),'value'=>4,'is_must'=>1))
			// ->addFormField('pid', '原始ID', 'text', array('is_must'=>1))
			 //->addFormField('mp_number', '微信号', 'text')
			// ->addFormField('appid', 'APPID', 'text')
			// ->addFormField('appsecret', 'APPSECRET', 'text')
			 //->addFormField('headimg', '头像', 'image')
			 //->addFormField('qrcode', '二维码', 'image')
			 ->setValidate(array(
					array('name', 'require', '名称不能为空'),
			   ))
			 ->setAuto(array(
					array('pid', '0'),
					array('addtime', 'time', 1, 'function')
			 ))
			 ->setAddSuccessUrl( U('addon/LuojiangKaoping/web/newsnenter') )
			 ->common_add();
	}

	public function addarticle_two()
	{
		$cateinfos = M('luojiang_articlecategory')->where( array('pid' => 0) )->select();

		$cate_arr = array();
		foreach($cateinfos as $val)
		{
			$cate_arr[$val[id]] = $val['name'];
		}

		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('添加文章', '', 'active')
			 ->setModel('luojiang_article')
			 ->addFormField('title', '标题', 'text')
			 ->addFormField('type', '1', 'hidden',array('value'=>2))
			 //->addFormField('link', '外链', 'text')
			 //->addFormField('logo', '图片', 'image')
			 ->addFormField('sendtime', '发布时间', 'time')
			 ->addFormField('cate_id', '1', 'hidden',array('value'=>1))
			 //->addFormField('cate_id', '所属分类', 'select', array('options'=>$cate_arr))
			 ->addFormField('content', '内容', 'editor')
			 ->setValidate(array(
					array('name', 'require', '标题不能为空'),
					//array('logo', 'require', '请上传图片')
			   ))
			 ->setAuto(array(
					array('addtime', 'time', 1, 'function')
			   ))
			 ->setAddSuccessUrl( U('addon/LuojiangKaoping/web/newsnenter_two') )
			 ->common_add();
	}

	public function addarticle()
	{
		$cateinfos = M('luojiang_articlecategory')->where( array('pid' => 0) )->select();

		$cate_arr = array();
		foreach($cateinfos as $val)
		{
			$cate_arr[$val[id]] = $val['name'];
		}

		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('添加文章', '', 'active')
			 ->setModel('luojiang_article')
			 ->addFormField('title', '标题', 'text')
			 ->addFormField('type', '1', 'hidden',array('value'=>1))
			 ->addFormField('link', '外链', 'text')
			 ->addFormField('logo', '图片', 'image')
			 ->addFormField('sendtime', '发布时间', 'time')
			 ->addFormField('cate_id', '所属分类', 'select', array('options'=>$cate_arr))
			 ->addFormField('content', '内容', 'editor')
			 ->setValidate(array(
					array('name', 'require', '标题不能为空')//,
					//array('logo', 'require', '请上传图片')
			   ))
			 ->setAuto(array(
					array('addtime', 'time', 1, 'function')
			   ))
			 ->setAddSuccessUrl( U('addon/LuojiangKaoping/web/newsnenter') )
			 ->common_add();
	}
	public function achievementmanage()
	{
		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('乡镇成绩管理', '', 'active')
			 ->setModel('luojiang_achievement')
			 ->setListOrder('addtime desc')
			 ->addListItem('name', '类别')
            //->addListItem('kao_time', '考评时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
             ->setListMap(array('type'=>1))
             ->addListItem('kao_time', '考评时间', 'callback', array('callback_name'=>'get_date_hui'))
			 ->addListItem('zeren_danwei', '街道办事处')
			 ->addListItem('stree', '抽评点')
			 //->addListItem('kaoping_dian', '考评点')
			 ->addListItem('chengji', '成绩')
			 //->addListItem('paiming', '排名')
			 ->addListItem('addtime', '添加时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
			 ->addButton('导入考评成绩', U('addon/LuojiangKaoping/web/loadachievement'), 'btn btn-primary')
			 ->addListItem('id', '操作', 'custom', array('options'=>array('edit_fans'=>array('编辑成绩', U('addon/LuojiangKaoping/web/editachievement', array('id'=>'{id}','type'=>1)),'btn btn-primary btn-sm icon-edit',''))))
		     ->common_lists();

		$cjcount = M('luojiang_achievement')->where(array('paiming'=>0,'type'=>1))->count();
		if($cjcount>0)
		{
			$paiming = array();
			$pagecount = M('luojiang_achievement')->field('kao_time')->where(array('type'=>1))->group('kao_time')->order('kao_time desc')->select();
			foreach($pagecount as $val)
			{
				$data = M('luojiang_achievement')->field('id,chengji')->where(array("kao_time"=>$val['kao_time'],"type"=>1))->order('chengji desc, id asc')->select();
				$chengji = $data[0]['chengji'];
				$order = 1;$num = 0;
				foreach($data as $key=>$subval)
				{
					if($key != 0){
						if($subval["chengji"] < $chengji){
							$order += 1;
							$chengji = $subval['chengji'];
							$order += $num;
							$num = 0;
						}
						else {
							$num++;
						}
					}
					$paiming[] = array('id'=>$subval["id"],'paiming'=>$order);
				}
				krsort($data);
				$chengji = $data[count($data)-1]['chengji'];
				$order = -5;$num = 0;
				foreach($data as $key=>$subval)
				{
					if($key != count($data)-1){
						if($subval["chengji"] > $chengji){
							$order += 1;
							$chengji = $subval['chengji'];
							$order += $num;
							$num = 0;
						}
						else {
							$num++;
						}
					}
					if($order == 0) break;
					$paiming[] = array('id'=>$subval["id"],'paiming'=>$order);
				}
			}
			foreach($paiming as $val)
			{
				$data['paiming'] = $val['paiming'];
				M('luojiang_achievement')->where(array("id"=>$val['id']))->save($data);
			}
		}
	}

	 public function achievementmanage_two()
    {
        $this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
            ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
            ->addCrumb('部门成绩管理', '', 'active')
            ->setModel('luojiang_achievement')
            ->setListOrder('addtime desc')
            ->setListMap(array('type'=>2))
            ->addListItem('name', '类别')
            //->addListItem('kao_time', '考评时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
            ->addListItem('kao_time', '考评时间', 'callback', array('callback_name'=>'get_date_hui'))
            ->addListItem('zeren_danwei', '部门')
            ->addListItem('stree', '考评对象')
            //->addListItem('kaoping_dian', '考评点')
            ->addListItem('chengji', '成绩')
            //->addListItem('paiming', '排名')
            ->addListItem('addtime', '添加时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
            ->addButton('导入考评成绩', U('addon/LuojiangKaoping/web/loadachievement',array('type'=>2)), 'btn btn-primary')
            ->addListItem('id', '操作', 'custom', array('options'=>array('edit_fans'=>array('编辑成绩', U('addon/HuianKaoping/web/editachievement', array('id'=>'{id}','type'=>2)),'btn btn-primary btn-sm icon-edit',''))))
            ->common_lists();

        $cjcount = M('luojiang_achievement')->where(array('paiming'=>0,'type'=>2))->count();
        if($cjcount>0)
        {
            $paiming = array();
            $pagecount = M('luojiang_achievement')->field('kao_time')->where(array('type'=>2))->group('kao_time')->order('kao_time desc')->select();
            foreach($pagecount as $val)
            {
                $data = M('luojiang_achievement')->field('id,chengji')->where(array("kao_time"=>$val['kao_time'],'type'=>2))->order('chengji desc, id asc')->select();
                $chengji = $data[0]['chengji'];
                $order = 1;$num = 0;
                foreach($data as $key=>$subval)
                {
                    if($key != 0){
                        if($subval["chengji"] < $chengji){
                            $order += 1;
                            $chengji = $subval['chengji'];
                            $order += $num;
                            $num = 0;
                        }
                        else {
                            $num++;
                        }
                    }
                    $paiming[] = array('id'=>$subval["id"],'paiming'=>$order);
                }
                krsort($data);
                $chengji = $data[count($data)-1]['chengji'];
                $order = -5;$num = 0;
                foreach($data as $key=>$subval)
                {
                    if($key != count($data)-1){
                        if($subval["chengji"] > $chengji){
                            $order += 1;
                            $chengji = $subval['chengji'];
                            $order += $num;
                            $num = 0;
                        }
                        else {
                            $num++;
                        }
                    }
                    if($order == 0) break;
                    $paiming[] = array('id'=>$subval["id"],'paiming'=>$order);
                }
            }
            foreach($paiming as $val)
            {
                $data['paiming'] = $val['paiming'];
                M('luojiang_achievement')->where(array("id"=>$val['id']))->save($data);
            }
        }
    }

	public function get_date_hui($kao_time)
	{
		$d = date('d',$kao_time);
		/**
		if($d == '15')
		{
			$kao_time = date('Y-m',$kao_time).'-下半月';
		} else {
			$kao_time = date('Y-m',$kao_time).'-上半月';

		}
		**/
		$kao_time = date('Y-m-d',$kao_time);
		return $kao_time;
	}
	public function usermanage()
	{

		//->addListItem('msgid', '消息内容', 'callback', array('callback_name'=>'get_message_content'))

		$this->addCrumb('洛江考评微官网', U('addon/LuojiangKaoping/index'), '')
			 ->addCrumb('业务导航', U('addon/LuojiangKaoping/web/eventreport'), '')
			 ->addCrumb('用户管理', '', 'active')
			 ->setModel('luojiang_eventreport')
			 ->setListMap(array('mpid'=>get_mpid()))
			 ->addListItem('fans_id', '会员名称', 'callback', array('callback_name'=>'get_fans_name'))
			 ->addListItem('event_type', '事件类型', 'enum', array('options'=>array('0'=>'未知',1=>'事件类型1',2=>'事件类型2',3=>'事件类型3')))
			 ->addListItem('title', '标题')
			 ->addListItem('descript', '事件描述')
			 ->addListItem('image_list', '图片列表', 'callback', array('callback_name'=>'get_images_str'))
			 ->addListItem('is_reply', '是否回复', 'enum', array('options'=>array(0=>'未回复',1=>'已回复')))
			 ->addListItem('location_addr', '位置', '', array('placeholder'=>'未提供'))
			 ->addListItem('addtime', '创建时间', 'function', array('function_name'=>'date','params'=>'Y-m-d H:i:s,###'))
			 ->addListItem('id', '操作', 'custom', array('options'=>array('edit_fans'=>array('编辑粉丝资料', U('Mp/Fans/edit_fans', array('openid'=>'{openid}')),'btn btn-primary btn-sm icon-edit',''))))
		     ->common_lists();
	}
	function get_images_str($image_list)
	{
		$image_arr = explode(',', $image_list);
		$image_str = '';
		$image_need_arr = array();

		if( !empty($image_arr) )
		{
			foreach($image_arr as $img)
			{
				$img = str_replace('./Uploads/','/Uploads/',$img);
				$img = str_replace('//','/',$img);
				$imgtemp = explode('.',$img);
				//$thumb_img =  $this->resize($img,100,100);
				$image_need_arr[] = "<div style='float:left;width:100px;margin-right:5px;'><img src='".$img."' width=100 height=100 'placeholder'= '".__ROOT__ ."/Public/Admin/img/noname.jpg' /><br/>【<a href='".$img."' target='_blank'>预览</a> | <a href='".U('addon/HuianKaoping/web/downfile', array('type' =>$imgtemp[1], 'file_url' => urlencode($img) ))."' target='_blank'>下载】</a></div>";
			}
			$image_str = implode(' ', $image_need_arr);
		}
		return "<div style='width:220px;'> ".$image_str."</div>";

	}
	//字符串截取
function utf8_substr($string, $offset, $length = null) {
	// generates E_NOTICE
	// for PHP4 objects, but not PHP5 objects
	$string = (string)$string;
	$offset = (int)$offset;

	if (!is_null($length)) {
		$length = (int)$length;
	}

	// handle trivial cases
	if ($length === 0) {
		return '';
	}

	if ($offset < 0 && $length < 0 && $length < $offset) {
		return '';
	}

	// normalise negative offsets (we could use a tail
	// anchored pattern, but they are horribly slow!)
	if ($offset < 0) {
		$strlen = strlen(utf8_decode($string));
		$offset = $strlen + $offset;

		if ($offset < 0) {
			$offset = 0;
		}
	}

	$Op = '';
	$Lp = '';

	// establish a pattern for offset, a
	// non-captured group equal in length to offset
	if ($offset > 0) {
		$Ox = (int)($offset / 65535);
		$Oy = $offset%65535;

		if ($Ox) {
			$Op = '(?:.{65535}){' . $Ox . '}';
		}

		$Op = '^(?:' . $Op . '.{' . $Oy . '})';
	} else {
		$Op = '^';
	}

	// establish a pattern for length
	if (is_null($length)) {
		$Lp = '(.*)$';
	} else {
		if (!isset($strlen)) {
			$strlen = strlen(utf8_decode($string));
		}

		// another trivial case
		if ($offset > $strlen) {
			return '';
		}

		if ($length > 0) {
			$length = min($strlen - $offset, $length);

			$Lx = (int)($length / 65535);
			$Ly = $length % 65535;

			// negative length requires a captured group
			// of length characters
			if ($Lx) {
				$Lp = '(?:.{65535}){' . $Lx . '}';
			}

			$Lp = '(' . $Lp . '.{' . $Ly . '})';
		} elseif ($length < 0) {
			if ($length < ($offset - $strlen)) {
				return '';
			}

			$Lx = (int)((-$length) / 65535);
			$Ly = (-$length)%65535;

			// negative length requires ... capture everything
			// except a group of  -length characters
			// anchored at the tail-end of the string
			if ($Lx) {
				$Lp = '(?:.{65535}){' . $Lx . '}';
			}

			$Lp = '(.*)(?:' . $Lp . '.{' . $Ly . '})$';
		}
	}

	if (!preg_match( '#' . $Op . $Lp . '#us', $string, $match)) {
		return '';
	}

	return $match[1];

}
/**
 * 递归生成目录
 */
function RecursiveMkdir($path) {
	if (!file_exists($path)) {
		$this->RecursiveMkdir(dirname($path));
		@mkdir($path, 0777);
	}
}


//字符串长度计算
function utf8_strlen($string) {
	return strlen(utf8_decode($string));
}

function utf8_strrpos($string, $needle, $offset = null) {
	if (is_null($offset)) {
		$data = explode($needle, $string);

		if (count($data) > 1) {
			array_pop($data);

			$string = join($needle, $data);

			return $this->utf8_strlen($string);
		}

		return false;
	} else {
		if (!is_int($offset)) {
			trigger_error('utf8_strrpos expects parameter 3 to be long', E_USER_WARNING);

			return false;
		}

		$string = $this->utf8_substr($string, $offset);

		if (false !== ($position = utf8_strrpos($string, $needle))) {
			return $position + $offset;
		}

		return false;
	}
}

	/**
 * 自动生成新尺寸 的图片
 */
	function resize($filename, $width, $height) {
			define(ROOT_PATH,'/data/web/wx.mnw.cn/');
			///data/web/wx.mnw.cn/Addons/HuianKaoping/Controller

		$image_dir=ROOT_PATH;


		if (!is_file($image_dir . $filename)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$old_image = $filename;
		$new_image = 'cache/' . $this->utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

		if (!is_file($image_dir . $new_image) || (filectime($image_dir . $old_image) > filectime($image_dir . $new_image))) {
			$path = '';

			$directories = explode('/', dirname(str_replace('../', '', $new_image)));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir($image_dir . $path)) {
					@mkdir($image_dir . $path, 0777);
				}
			}

			list($width_orig, $height_orig) = getimagesize($image_dir . $old_image);

			if ($width_orig != $width || $height_orig != $height) {
				$image = new \Lib\Image($image_dir . $old_image);
			$image->resize($width, $height);
				$image->save($image_dir . $new_image);
			} else {
				copy($image_dir . $old_image, $image_dir . $new_image);
			}
		}

		return 'Uploads/image/' . $new_image;

		}

	public function get_fans_name($fans_id)
	{
		$fans_info = M('mp_fans')->where( array('id' => $fans_id) )->find();
		return $fans_info['nickname'];
		//return '<a href="'.U('Mp/Fans/edit_fans', array('openid'=>$fans_info['openid'])).'" target="_blank" title="点击查看用户信息">'.$fans_info['nickname'].'</a>';
	}

}

?>
