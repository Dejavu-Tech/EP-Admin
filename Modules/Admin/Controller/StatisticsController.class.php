<?php
namespace Admin\Controller;
use Admin\Model\StatisticsModel;
class StatisticsController extends CommonController {
   	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='首页';
			$this->breadcrumb2='访客IP';
	}
    public function show_ip(){
    	
		$model=new StatisticsModel();
		
		$type=I('type');
		if($type=='today'){
			$data=$model->show_visitors_ip(date('Y-m-d'));	
		}elseif($type=='all'){
			$data=$model->show_visitors_ip();
		}
		$this->c=$data['count'];
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		
        $this->display('ip');
	}
}