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

class IntegralController extends CommonController {

	 protected function _initialize()
    {
    	parent::_initialize();
	}
    public function index(){

       $goods_model = D('Home/Goods');

		$pre_page = 10;
		$page = I('get.page',1);

		$condition = array('type' => 'integral', 'status'=>1,'quantity' =>array('gt',0) );

		$offset = ($page -1) * $pre_page;
		$list = M('goods')->where($condition)->order('seller_count+virtual_count desc,goods_id asc')->limit($offset,$pre_page)->select();

		if(!empty($list)) {
			foreach($list as $key => $v){

				$goods_price_arr = $goods_model->get_goods_price($v['goods_id']);
				$list[$key]['pinprice'] = $goods_price_arr['price'];

				$list[$key]['image']=C('SITE_URL'). resize($v['image'], 400, 400);
			}
		}
		foreach($list as $key => $val)
		{
		    $val['seller_count'] += $val['virtual_count'];
		    $list[$key] = $val;
		}




        $this->list = $list;


        if($page > 1) {
            $result = array('code' => 0);
            if(!empty($list)) {
                $result['code'] = 1;
                $result['html'] = $this->fetch('Widget:integral_ajax_goods_list_fetch');
            }
            echo json_encode($result);
            die();
        }

		$integral_rules = C('integral_description');

		$qian=array("\r\n");
		$hou=array("@F@");
		$integral_rules_str = str_replace($qian,$hou,$integral_rules);
		$integral_rules_str = explode('@F@',$integral_rules_str);

		$this->integral_rules_str = $integral_rules_str;

		$ad_info = M('plugins_slider')->where( array('type' => 'wepro_integral_mall') )->order('slider_id desc')->find();
		$this->ad_info = $ad_info;

        $this->display();

    }

}
