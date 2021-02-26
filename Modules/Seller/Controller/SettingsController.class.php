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
namespace Seller\Controller;
class SettingsController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='系统';
		$this->breadcrumb2='站点设置';
	}

	function other(){
		$this->breadcrumb2='其他选项';
		$this->other=$this->get_config_by_group('other');
		$this->display();
	}

	function image(){
		$this->breadcrumb2='图片尺寸';
		$this->image=$this->get_config_by_group('image');
		$this->display();
	}

	function general(){
		$this->breadcrumb2='站点设置';
		$this->site=$this->get_config_by_group('site');
		$this->display();
	}
	function wxprogram()
	{
		$this->breadcrumb2='微信小程序配置';
		$this->site=$this->get_config_by_group('weixin');
		$this->type = 1;
	    $this->display();
	}
	function wxprogram_template()
	{
		$this->breadcrumb2='微信小程序配置';
		$this->site=$this->get_config_by_group('weixin');
		$this->type = 2;
	    $this->display();
	}

	function wxtempmsg()
	{
	    $this->breadcrumb2='微信模板消息';
	    $this->site=$this->get_config_by_group('weixin');
		$this->type = 1;
	    $this->display();
	}
	function sendallwxmsg()
	{
	    $this->breadcrumb2='微信模板消息';
		$this->type = 2;
	    $this->display();
	}
	function sendallcuswxmsg()
	{
		$this->breadcrumb3='微信客服消息';
		$this->type = 3;
	    $this->display();
	}
	function clearuserqrcode()
	{
		$result = array('code' =>1);

		$sql = "update ".C('DB_PREFIX')."member_common set qrcode_img =''  where member_id>0 ";
		M()->execute($sql);

		$sql = "update ".C('DB_PREFIX')."member set wepro_qrcode =''  where member_id>0 ";
		M()->execute($sql);

		echo json_encode($result);
		die();
	}

	function sendallcuswxmsg_save()
	{
		$data = I('post.');

		@set_time_limit(0);

		$template_data = array();

		if( empty($data['title']) )
		{
			$this->error('请填写标题');
		}

		if( empty($data['image']) )
		{
			$this->error('请上传封面图片');
		}

		if( empty($data['url_link']) )
		{
			$this->error('请填写链接');
		}
		$data['image'] = C('SITE_URL').'/Uploads/image/'.$data['image'];

		$template_data['title'] = $data['title'];
		$template_data['image'] = $data['image'];
		$template_data['descript'] = $data['descript'];

		$user_list = M('member')->select();

		foreach($user_list as $user)
		{
			if(empty($user['openid']))
			{
				continue;
			}

			$msg_order = array();
			$msg_order['template_data'] = serialize($template_data);
			$msg_order['url'] = $data['url_link'];
			$msg_order['type'] = 1;
			$msg_order['open_id'] = $user['openid'];
			$msg_order['template_id'] = 8888;
			$msg_order['state'] = 0;
			$msg_order['addtime'] = time();

			M('template_msg_order')->add($msg_order);
		}
		 $this->success('保存成功，客服消息将会陆续发出！');
	}

	/**
		群发自定义模板消息
	**/
	function sendallmodifywxmsg()
	{
		$this->type =4;
		$this->display();
	}
	/**
		分析模板消息模板
	**/
	function sendallmodifywxmsg_analys()
	{
		$this->type =4;
		$moban = I('post.moban');
		$str = str_replace(array("\r\n", "\r", "\n"), "", $moban);
		preg_match_all('/{{(.*?)}}/',$str,$contain_arr);


		$data = $contain_arr[1];
		if(empty($data))
		{
			$this->error('模板格式错误');
		}
		$this->moban = $moban;
		$this->data = $data;
		$this->display();
	}
	/**
		自定义模板消息保存
	**/
	public function sendallmodifywxmsg_save()
	{
		@set_time_limit(0);
		$data = I('post.');
		$url_link = $data['url_link'];
		$moban_id = $data['moban_id'];
		unset($data['url_link']);
		unset($data['moban_id']);

		$template_data = array();

		$page = empty($data['page']) ? 1: intval($data['page']);
		unset($data['page']);
		$per_count = 100;
		$offset = ($page-1)*$per_count;

		$total_count =  M('member')->count();

		foreach($data as $key => $val)
		{
			if( strpos($key,'_color') === false )
			{
				$real_key = explode('_',$key);
				$tp_key = $real_key[0];
				$tp_color = empty($data[$key.'_color']) ? '#173177': trim($data[$key.'_color']);
				$template_data[$tp_key] = array('value' => ($val), 'color' => $tp_color);
			}
		}
		$template_id = $moban_id;

		$has_next = ( $total_count > ($offset+$per_count) ) ? 1:0;

		$del_count = $total_count - ($offset+$per_count);


		$user_list = M('member')->order('member_id asc')->limit($offset,$per_count)->select();

		foreach($user_list as $user)
		{
			if(empty($user['openid']))
			{
				continue;
			}
			$member_formid_info = M('member_formid')->where( array('state' =>0, 'member_id' =>$user['member_id']) )->find();

			if(!empty($member_formid_info))
			{
				$msg_order = array();
				$msg_order['template_data'] = serialize($template_data);
				$msg_order['url'] = $url_link;
				$msg_order['open_id'] = $user['we_openid'];
				$msg_order['template_id'] = $template_id;
				$msg_order['type'] = 2;
				$msg_order['state'] = 0;
				$msg_order['addtime'] = time();
				M('template_msg_order')->add($msg_order);
			}
		}
		$result = array();
		$result['code'] = 1;
		$result['has_next'] = $has_next;
		$result['del_count'] = $del_count;
		$result['offset'] = $offset;
		$result['per_count'] = $per_count;

		echo json_encode($result);
		die();
		//$this->success('保存成功，消息将会陆续发出！','/Settings/sendallmodifywxmsg');
	}

	function sendallwxmsg_save()
	{
		$data = I('post.');


		@set_time_limit(0);

		$title_color = empty($data['title_color']) ? '#173177':trim($data['title_color']);
		$goods_name_color = empty($data['goods_name_color']) ? '#173177':trim($data['goods_name_color']);
		$lian_man_color = empty($data['lian_man_color']) ? '#173177':trim($data['lian_man_color']);
		$lian_mobile_color = empty($data['lian_mobile_color']) ? '#173177':trim($data['lian_mobile_color']);
		$qv_address_color = empty($data['qv_address_color']) ? '#173177':trim($data['qv_address_color']);
		$description_color = empty($data['description_color']) ? '#173177':trim($data['description_color']);

		$template_data = array();
		$template_data['first'] = array('value' => ($data['title']), 'color' => $title_color);
		$template_data['keyword1'] = array('value' => ($data['goods_name']), 'color' => $goods_name_color);
		$template_data['keyword2'] = array('value' => ($data['lian_man']), 'color' => $lian_man_color);
		$template_data['keyword3'] = array('value' => ($data['lian_mobile']), 'color' => $lian_mobile_color);
		$template_data['keyword4'] = array('value' => ($data['qv_address']), 'color' => $qv_address_color);
		$template_data['remark'] = array('value' => ($data['description']), 'color' => $description_color);

		$url = $data['url_link'];

		$config = M('config')->where( array('name' => 'GoodsOnNoticeId') )->find();

		$template_id = $config['value'];

		$user_list = M('member')->select();


		foreach($user_list as $user)
		{
			if(empty($user['openid']))
			{
				continue;
			}

			$msg_order = array();
			$msg_order['template_data'] = serialize($template_data);
			$msg_order['url'] = $url;
			$msg_order['open_id'] = $user['openid'];
			$msg_order['template_id'] = $template_id;
			$msg_order['state'] = 0;
			$msg_order['addtime'] = time();

			M('template_msg_order')->add($msg_order);
		}
		 $this->success('保存成功，消息将会陆续发出！');
	}

	function smtp_mail(){
		$this->breadcrumb2='邮件配置';
		$this->smtp=$this->get_config_by_group('smtp');

		$this->display();
	}

	function get_config_by_group($group){

		$list=M('config')->where(array('config_group'=>$group))->select();
		if(isset($list)){
			foreach ($list as $k => $v) {
				$config[$v['name']]=$v;
			}
		}
		return $config;
	}

	function save(){
		if(IS_POST){
			$config=I('post.');

			if($config && is_array($config)){
				$c=M('Config');
	            foreach ($config as $name => $value) {
	                $map = array('name' => $name);
					$c->where($map)->setField('value', $value);
	            }

	        }
	        S('DB_CONFIG_DATA',null);
	        $this->success('保存成功！');
		}
	}


}
?>
