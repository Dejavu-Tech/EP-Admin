<?php
namespace Admin\Controller;
use Admin\Model\StatisticsModel;
class IndexController extends CommonController {
   	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='首页';
		$this->breadcrumb2='首页';
		$this->admin_domain = 'http://pinduoduo.eaterplanet.com';
		$this->host = base64_encode( strtolower(strval($_SERVER['HTTP_HOST'])));
	}
	function duoduo_action($action, $version='V1.0') {
	    $host = base64_encode( strtolower(strval($_SERVER['HTTP_HOST'])));
	    $url = $this->admin_domain."/seller.php?s=/Upgrade/req_version/version/{$version}/host/{$host}";

	    $r = sendhttp_get($url);
	    return json_decode($r, true);
	}

	function zuitu_upgrade($action, $version='V1.0') {
	    $result = $this->duoduo_action($action, $version);
	    return $result;
	}

	function duoduo_version($version) {
	    return $this->duoduo_action('version', $version);
	}
    public function index(){

        $config_arr = M('config')->where( array('name' => 'VERSION') )->find();
        $version = $config_arr['value'];
        $version_meta = $this->duoduo_version($version);


        $isnew = true;
        $newsubversion = '';

        if(!empty($version_meta))
        {
            $isnew = false;
            $newsubversion = end($version_meta);
            $newsubversion = $newsubversion['name'];
        }
        $this->version = $version;
        $this->newsubversion = $newsubversion;

        $version_meta = array_reverse($version_meta);
        $this->version_meta = $version_meta;

		$model=new StatisticsModel();

       	$this->total_ip=count($model->get_all_visitors_ip());
		$this->today_ip=count($model->get_visitors_ip_by_date(date('Y-m-d',time())));

		//$this->total_member=count($model->get_all_member());
		$this->total_member=($model->get_all_member_count());

		$this->today_member=count($model->get_today_register_member());

		$this->total_money=$model->get_total_sales();
		$this->today_money=$model->get_total_sales(array('date_added' => date('Y-m-d')));

		$this->total_order=$model->get_total_order();
		$this->today_order=$model->get_total_order(array('date_added' => date('Y-m-d')));

		$this->total_wait_goods = M('goods')->where(array('status' => 2) )->count();
		$this->total_wait_apply = M('apply')->where( array('state' => 0) )->count();

		//type normal   lock_type  'normal','spike','super_spike','','subject','niyuan','oneyuan','haitao','lottery'

		$this->subject_wait_count = M('goods')->where( array('type' => 'normal', 'lock_type' => 'subject') )->count();
		$this->niyuan_wait_count = M('goods')->where( array('type' => 'normal', 'lock_type' => 'niyuan') )->count();
		$this->oneyuan_wait_count = M('goods')->where( array('type' => 'normal', 'lock_type' => 'oneyuan') )->count();
		$this->haitao_wait_count = M('goods')->where( array('type' => 'normal', 'lock_type' => 'haitao') )->count();
		$this->lottery_wait_count = M('goods')->where( array('type' => 'normal', 'lock_type' => 'lottery') )->count();
		$this->spike_wait_count = M('goods')->where( array('type' => 'normal', 'lock_type' => 'spike') )->count();
		$this->super_spike_wait_count = M('goods')->where( array('type' => 'normal', 'lock_type' => 'super_spike') )->count();

		$order_model=new \Admin\Model\OrderModel();

		$data=$order_model->show_order_page();

		$this->empty=$data['empty'];
		$this->list=$data['list'];

		$this->uc_empty='~~暂无数据';
		$this->uc_list=$model->get_user_action();

        $this->display();
	}
}
