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
namespace Home\Controller;
use Lib\Taobaoip;
class FormController extends CommonController {

	function reply(){

		if(IS_GET){
			$id=get_url_id('id');
			$data['title']=I('name');
			$data['content']=I('comment');
			$data['email']=I('email');
			$data['blog_id']=$id;

			$data['ip']=get_client_ip();
			$tip=new Taobaoip();
			$ip_region=$tip->getLocation($data['ip']);
			$data['ip_region']=$ip_region['region'].'-'.$ip_region['city'];

			$data['create_time']=date('Y-m-d H:i:s',time());
			$data['status']=1;
			$r['blog_id']=	$id;
			$r['reply']		=	array('exp','reply+1');
			M('blog')->save($r);


			$data['device']=$_SERVER['HTTP_USER_AGENT'];
			if(M('blog_reply')->add($data)){
				die('true');
			}
		}
	}

	//写入留言
	public function comment(){
		if(IS_POST){
			//留言时间间隔 2分钟
			$time=120;

			$comment=I('comment');
			if(empty($comment)){
				$this->error='留言内容必填！！！';
				$this->author=I('author');
				$this->email=I('email');
				$this->phone=I('phone');
				$this->display('Html:contact');
				die;
			}

			$data['name']=I('author');
			$data['email']=I('email');
			$data['tel']=I('phone');
			$data['content']=I('comment');

			$data['ip']=get_client_ip();

			if($last_comment=M('comment')->where(array('ip'=>$data['ip']))->order('comment_id desc')->limit(1)->find()){

				if((time()-(int)$last_comment['create_time'])<$time){

					$this->error='请2分钟后再留言！！！';
					$this->author=I('author');
					$this->email=I('email');
					$this->phone=I('phone');
					$this->comment=$comment;
					$this->display('Html:contact');
					die;
				}
			}


			$tip=new Taobaoip();
			$ip_region=$tip->getLocation($data['ip']);
			$data['ip_region']=$ip_region['region'].'-'.$ip_region['city'];

			$data['create_time']=time();
			$data['create_time_date']=date('Y-m-d H:i:s',time());
			$data['device']=$_SERVER['HTTP_USER_AGENT'];

			$id=M('comment')->add($data);

			if($id){
				$this->success='留言成功！';
				$this->display('Html:contact');
			}else{
				$this->error='留言失败！';
				$this->display('Html:contact');
			}
		}
	}


}
