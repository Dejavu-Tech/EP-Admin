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

class OrderdistributionController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
	}

	public function index()
	{
		$pindex    = I('request.page', 1);
        $psize     = 20;

		$keyword = I('request.keyword');
		$this->keyword = $keyword;
        $condition = "";
        if (!empty($keyword)) {
            $condition .= ' and (a.username like "%'.$keyword.'%" or a.mobile like "%'.$keyword.'%" )';
        }

        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $condition .= ' and store_id='.$supper_info['id'];
        }

		$enabled = I('request.state',-1);

        if (isset($enabled) && $enabled >= 0) {

            $condition .= ' and a.state = ' . $enabled;
        } else {
            $enabled = -1;
        }
		$this->enabled = $enabled;



        $list = M()->query('SELECT a.*,b.storename FROM ' . C('DB_PREFIX'). "eaterplanet_ecommerce_orderdistribution a"
              . " left join ".C('DB_PREFIX')."eaterplanet_ecommerce_supply b on a.store_id=b.id "
              . " WHERE 1=1 "
              . $condition . ' order by a.id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);

		if( !empty($list) )
		{
			foreach( $list as $key => $val )
			{
				$mb_info = M('eaterplanet_ecommerce_member')->field('member_id, username as nickname,avatar')->where( array('member_id' => $val['member_id'] ) )->find();

				$val['mb_info'] = $mb_info;

				$list[$key] = $val;
			}
		}

		$total = M('eaterplanet_ecommerce_orderdistribution a')->where("1=1 ".$condition)->count();

        $pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;

		$this->display();
	}

    /**
     * 提现列表
     */
    public function withdrawallist()
    {
        $_GPC = I('request.');

        $condition = '  ';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (  o.id = '.intval($_GPC['keyword']).') ';
        }

        $store_id = 0;
        $is_supply_status = 0;
        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $store_id = $supper_info['id'];
            $condition .= ' and co.store_id='.$store_id;
            $is_supply_status = 1;
        }

        $starttime = strtotime( date('Y-m-d')." 00:00:00" );
        $endtime = $starttime + 86400;

        if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
            $starttime = strtotime($_GPC['time']['start']);
            $endtime = strtotime($_GPC['time']['end']);

            $condition .= ' AND o.addtime >= '.$starttime.' AND o.addtime <= '.$endtime.' ';
        }


        $this->starttime = $starttime;
        $this->endtime = $endtime;


        if ($_GPC['comsiss_state'] != '') {
            $condition .= ' and o.state=' . intval($_GPC['comsiss_state']);
        }

        $sql = 'SELECT o.*,co.username FROM ' . C('DB_PREFIX') . "eaterplanet_ecommerce_orderdistribution_tixian_order o left join ".C('DB_PREFIX')."eaterplanet_ecommerce_orderdistribution co on o.member_id=co.member_id "
			 . "WHERE 1 " . $condition . ' order by o.id desc  ';

        if (empty($_GPC['export'])) {
            $sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
        }

        $community_tixian_fee = D('Home/Front')->get_config_by_name('distribution_tixian_fee');
        //echo $sql.'<br/>';
        $list = M()->query($sql);
        $total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_orderdistribution_tixian_order o left join '.C("DB_PREFIX").'eaterplanet_ecommerce_orderdistribution co on o.member_id=co.member_id WHERE 1 ' . $condition );

        $total = $total_arr[0]['count'];


        foreach( $list as $key => $val )
        {
            //普通等级
            $member_info = M('eaterplanet_ecommerce_member')->field('username,avatar,we_openid,telephone')->where( array('member_id' => $val['member_id'] ) )->find();

            $val['member_info'] = $member_info;


            $list[$key] = $val;
        }
        if ($_GPC['export'] == '1') {

            foreach($list as $key =>&$row)
            {
                $row['username'] = $row['member_info']['username'];


                $row['telephone'] = $row['member_info']['telephone'];

                $row['bankname'] = $row['bankname'];

                if( $row['type'] == 1 )
                {
                    $row['bankname'] = '余额';
                }elseif( $row['type'] == 2 ){
                    $row['bankname'] =  '微信零钱';
                }elseif($row['type'] == 3){
                    $row['bankname'] =  '支付宝';
                }


                $row['bankaccount'] = "\t".$row['bankaccount'];
                $row['bankusername'] = $row['bankusername'];

                $row['get_money'] = $row['money']-$row['service_charge_money'];
                $row['addtime'] = date('Y-m-d H:i:s', $row['addtime']);
                if(!empty($row['shentime']))
                {
                    $row['shentime'] = date('Y-m-d H:i:s', $row['shentime']);
                }

                if($row['state'] ==0)
                {
                    $row['state'] = '待审核';
                }else if($row[state] ==1)
                {
                    $row['state'] = '已审核，打款';
                }else if($row[state] ==2){
                    $row['state'] = '已拒绝';
                }
            }
            unset($row);

            $columns = array(
                array('title' => 'ID', 'field' => 'id', 'width' => 12),
                array('title' => '用户名', 'field' => 'username', 'width' => 12),
                array('title' => '联系方式', 'field' => 'telephone', 'width' => 12),
                array('title' => '打款银行', 'field' => 'bankname', 'width' => 24),
                array('title' => '打款账户', 'field' => 'bankaccount', 'width' => 24),
                array('title' => '真实姓名', 'field' => 'bankusername', 'width' => 24),
                array('title' => '申请提现金额', 'field' => 'money', 'width' => 24),
                array('title' => '手续费', 'field' => 'service_charge_money', 'width' => 24),
                array('title' => '到账金额', 'field' => 'get_money', 'width' => 24),
                array('title' => '申请时间', 'field' => 'addtime', 'width' => 24),
                array('title' => '审核时间', 'field' => 'shentime', 'width' => 24),
                array('title' => '状态', 'field' => 'state', 'width' => 24)
            );

            D('Seller/Excel')->export($list, array('title' => '配送员佣金提现数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));

        }

        $pager = pagination2($total, $pindex, $psize);


        $this->list = $list;
        $this->pager = $pager;
        $this->_GPC = $_GPC;
        $this->is_supply_status = $is_supply_status;
        $this->display();

    }

    public function agent_check_apply()
    {
        $_GPC = I('request.');

        $commission_model = D('Home/LocaltownDelivery');


        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

        $comsiss_state = intval($_GPC['state']);

        $apply_list = M('eaterplanet_ecommerce_orderdistribution_tixian_order')->where('id in( ' . $id . ' )')->select();

        $time = time();

        //var_dump($members,$comsiss_state);die();
        foreach ($apply_list as $apply) {
            if ($apply['state'] == $comsiss_state || $apply['state'] == 1 || $apply['state'] == 2) {
                continue;
            }
            $money = $apply['money'];

            if ($comsiss_state == 1) {


                switch( $apply['type'] )
                {
                    case 1:
                        $result = $commission_model->send_apply_yuer( $apply['id'] );
                        break;
                    case 2:
                        $result = $commission_model->send_apply_weixin_yuer( $apply['id'] );
                        break;
                    case 3:
                        $result = $commission_model->send_apply_alipay_bank( $apply['id'] );
                        break;
                    case 4:
                        $result = $commission_model->send_apply_alipay_bank( $apply['id'] );
                        break;
                }

                if( $result['code'] == 1)
                {
                    show_json(0,  array('url' => $_SERVER['HTTP_REFERER'] ,'message'=>$result['msg'] ) );
                    die();
                }

                //检测是否存在账户，没有就新建
                //TODO....检测是否微信提现到零钱，如果是，那么就准备打款吧

                //$commission_model->send_apply_success_msg($apply['id']);
            }
            else if ($comsiss_state == 2) {

                M('eaterplanet_ecommerce_orderdistribution_tixian_order')->where( array('id' => $apply['id'] ) )->save( array('state' => 2, 'shentime' => $time) );
                //退回冻结的货款

                M('eaterplanet_ecommerce_orderdistribution_commiss')->where( array('member_id' => $apply['member_id'] ) )->setInc('money',$money);
                M('eaterplanet_ecommerce_orderdistribution_commiss')->where( array('member_id' => $apply['member_id'] ) )->setInc('dongmoney',-$money);

            }
            else {

                M('eaterplanet_ecommerce_orderdistribution_tixian_order')->where( array('id' => $apply['id']) )->save( array('state' => 0, 'shentime' => 0) );
            }
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
    }
	public function withdrawalconfig()
	{
		$_GPC = I('request.');

        $supply_id  = 0;
        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $supply_id = $supper_info['id'];
        }

	    if (IS_POST) {

	        $data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));


			$data['distribution_tixianway_yuer'] = isset($data['distribution_tixianway_yuer']) ? $data['distribution_tixianway_yuer']:1;
			$data['distribution_tixianway_weixin'] = isset($data['distribution_tixianway_weixin']) ? $data['distribution_tixianway_weixin']:1;
			$data['distribution_tixianway_alipay'] = isset($data['distribution_tixianway_alipay']) ? $data['distribution_tixianway_alipay']:1;
			$data['distribution_tixianway_bank'] = isset($data['distribution_tixianway_bank']) ? $data['distribution_tixianway_bank']:1;
			$data['distribution_tixian_publish'] = isset($data['distribution_tixian_publish']) ? $data['distribution_tixian_publish']:'';

			if($supply_id == 0){
                D('Seller/Config')->update($data);
            }else if($supply_id > 0){
                D('Seller/SupplyConfig')->update($data);
            }

	        show_json(1,  array('url' => $_SERVER['HTTP_REFERER']) );
			die();

	    }
        if($supply_id == 0){
            $data = D('Seller/Config')->get_all_config();
        }else if($supply_id > 0){
            $data = D('Seller/SupplyConfig')->get_all_config();
        }
        $this->supply_id = $supply_id;
		$this->data = $data;

	    $this->display();
	}

	/**
     * 改变状态
     */
    public function change()
    {

        $id = I('request.id');

        //ids
        if (empty($id)) {
			$ids = 	I('request.ids');
            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = I('request.type');
        $value = I('request.value');

        if (!(in_array($type, array('state')))) {
            show_json(0, array('message' => '参数错误'));
        }

		$items = M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => array('in', $id) ) )->select();

        foreach ($items as $item) {

			M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => $item['id']) )->save( array('state' => $value) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

	public function deletedistribution()
	{
		 $id = I('request.id');

        if (empty($id)) {
			$ids = I('request.ids');
            $id = (is_array($ids) ? implode(',', $ids) : 0);
        }

		$items = M('eaterplanet_ecommerce_orderdistribution')->field('id')->where( array('id' => array('in', $id) ) )->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
			if($item['has_send_count'] <= 0)
				M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}

	public function adddistribution()
	{

		if (IS_POST) {

			$id = I('post.id');

			if( empty($id) )
			{
				$id = 0;
			}

			$data = array();
			$data['username'] = I('post.username');
			$data['mobile'] = I('post.mobile');
			$data['member_id'] = I('post.member_id');
			$data['state'] = I('post.state');
			$data['always_address'] = I('post.always_address');

			if( empty($data['username']) )
			{
				show_json(0, array('message' => '请填写配送员姓名'));
			}
			if( empty($data['mobile']) )
			{
				show_json(0, array('message' => '请填写手机号'));
			}
			if( empty($data['member_id']) )
			{
				show_json(0, array('message' => '请选择关联客户'));
			}
            if (defined('ROLE') && ROLE == 'agenter' )
            {
                $supper_info = get_agent_logininfo();
                $data['store_id'] = $supper_info['id'];
            }

			//检测配送员是否关联过了

			$ck_pes = M('eaterplanet_ecommerce_orderdistribution')->where( "member_id=".$data['member_id']." and id != " .$id )->find();

			if( !empty($ck_pes) )
			{
				show_json(0, array('message' => '该客户已经关联配送员，请选择其他客户'));
			}


			if( $id > 0 )
			{
				M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => $id) )->save( $data );
			}else{

				$data['has_send_count'] = 0;
				$data['addtime'] = time();

				M('eaterplanet_ecommerce_orderdistribution')->add( $data );
			}

			show_json(1, array('url' => U('Orderdistribution/index')));
        }

		$id = I('get.id', 'intval',0);

		if( $id > 0 )
		{
            $store_id  = 0;
            $distribution  = array();
            if (defined('ROLE') && ROLE == 'agenter' )
            {
                $supper_info = get_agent_logininfo();
                $store_id = $supper_info['id'];
                $distribution = M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => $id,'store_id'=>$store_id) )->find();
            }else{
                $distribution = M('eaterplanet_ecommerce_orderdistribution')->where( array('id' => $id) )->find();
            }

			$this->distribution = $distribution;

			$saler = M('eaterplanet_ecommerce_member')->field('member_id, username as nickname,avatar')->where( array('member_id' => $distribution['member_id'] ) )->find();

			$saler['username'] = str_replace("'","",$saler['username']);
			$saler['nickname'] = str_replace("'","",$saler['nickname']);

			$this->saler = $saler;

		}

		$this->display();
	}


    /**
     *  @author Albert.Z
     *  同城配送配置方法
     */
    public function distributionconfig()
	{
        $supply_id  = 0;
        if (defined('ROLE') && ROLE == 'agenter' )
        {
            $supper_info = get_agent_logininfo();
            $supply_id = $supper_info['id'];
        }
        if (IS_POST) {

            $data = I('post.data', array());
            $data['goods_stock_notice'] = trim($data['goods_stock_notice']);


            $data['goods_details_title_bg'] = save_media($data['goods_details_title_bg']);
            if($supply_id > 0){
                D('Seller/SupplyConfig')->update($data);
            }else{
                D('Seller/Config')->update($data);
            }

            show_json(1, array('url'=> U('Orderdistribution/distributionconfig')));
        }
        if($supply_id > 0){
            $data = D('Seller/SupplyConfig')->get_all_config();
        }else{
            $data = D('Seller/Config')->get_all_config();
        }

        $this->data = $data;
        $this->display();


	}

    /**
     *  选择配送员
     */
	public function choosemember()
    {

        $_GPC = I('request.');
        $kwd = trim($_GPC['keyword']);

        $order_id = $_GPC['id'];

        $is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;

        $order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();

        $params = array();

        $condition = '  1 ';

        if (!empty($kwd)) {
            $condition .= ' and ( username LIKE "%'.$kwd .'%" or mobile like "%'.$kwd .'%" )';

        }

        if($order_info['store_id'] > 0){
            $condition .= ' and store_id='.$order_info['store_id'];
        }else{
            $condition .= ' and store_id=0';
        }

        /**
        分页开始
         **/
        $page =  isset($_GPC['page']) ? intval($_GPC['page']) : 1;
        $page = max(1, $page);
        $page_size = 10;
        /**
        分页结束
         **/

        $ds = M()->query('SELECT * FROM ' . C('DB_PREFIX') . 'eaterplanet_ecommerce_orderdistribution  where '
            . $condition .
            ' order by id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size );

        $total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') .
            'eaterplanet_ecommerce_orderdistribution  WHERE  ' . $condition );

        $total = $total_arr[0]['count'];

        foreach ($ds as &$value) {


            if($is_ajax == 1)
            {
                $ret_html .= '<tr>';
                $ret_html .= '	<td>'. $value['username'].'</td>';

                $ret_html .= '	<td>'.$value['mobile'].'</td>';


                $ret_html .= '	<td style="white-space:nowrap;text-align: right;"><a href="javascript:;" class="choose_dan_link btn-primary btn-sm" data-json=\''.json_encode($value).'\'>选择</a></td>';

                $ret_html .= '</tr>';

            }
        }

        $pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));

        if( $is_ajax == 1 )
        {
            echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
            die();
        }
        $this->ds = $ds;

        $this->id = $order_id;
        $this->pager = $pager;

        unset($value);

        if ($_GPC['suggest']) {
            exit(json_encode(array('value' => $ds)));
        }

        $this->display();

    }

    /**
     * 后台配送单选择配送员
     */
    public function sub_orderchoose_distribution()
    {

        $order_id = I('post.order_id', 0);
        $id = I('post.id', 0);

        if( $order_id <=0 || $id <=0  )
        {
            show_json(0 , array('message' => '数据不合法') );
        }

        $res = D('Home/LocaltownDelivery')-> distribution_get_order( $id , $order_id);

        if($res)
        {
            show_json(1 , array('message' => '分配成功') );
        }else{
            show_json(0 , array('message' => '已被分配，请刷新页面') );
        }


    }

    public function distribution_list()
    {
        $_GPC = I('request.');
        $pindex    = I('request.page', 1);
        $psize     = 20;

        $id = I('request.id');
        $condition = "";
        //$condition .= ' and a.orderdistribution_id = '.$id." and is_statement = 1";
        $condition .= ' and a.orderdistribution_id = '.$id." ";
        $order_no = trim($_GPC['order_no']);
        if (!empty($order_no)) {
            $condition .= " and o.order_num_alias='".$order_no."'";
        }
        $list = M()->query("SELECT a.*,o.order_num_alias,o.date_added FROM " . C('DB_PREFIX'). "eaterplanet_ecommerce_orderdistribution_order a  "
                         . " left join " . C('DB_PREFIX'). "eaterplanet_ecommerce_order o on a.order_id=o.order_id "
		                  . " WHERE 1=1 " . $condition . ' order by a.id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        if( !empty($list) )
        {
            foreach( $list as $key => $val )
            {
                //配送员名称
                $distribution_info = M('eaterplanet_ecommerce_orderdistribution')->field('username')->where( array('id' => $val['orderdistribution_id'] ) )->find();
                $val['username'] = $distribution_info['username'];
                //下单时间
                $val['date_added'] = date('Y-m-d H:i:s',$val['date_added']);
                //抢单时间
                $order_log = M('eaterplanet_ecommerce_orderdistribution_log')->field('addtime')->where( array('order_id' => $val['order_id'],'state'=> 2) )->find();
                if(!empty($order_log['addtime'])) {
                    $val['rob_time'] = date('Y-m-d H:i:s', $order_log['addtime']);
                }
                //取货时间
                $order_log = M('eaterplanet_ecommerce_orderdistribution_log')->field('addtime')->where( array('order_id' => $val['order_id'],'state'=> 3) )->find();
                if(!empty($order_log['addtime'])) {
                    $val['receive_time'] = date('Y-m-d H:i:s', $order_log['addtime']);
                }
                //完成时间
                $order_log = M('eaterplanet_ecommerce_orderdistribution_log')->field('addtime')->where( array('order_id' => $val['order_id'],'state'=> 4) )->find();
                if(!empty($order_log['addtime'])){
                    $val['finish_time'] = date('Y-m-d H:i:s',$order_log['addtime']);
                }
                $list[$key] = $val;
            }
        }
        $count_list = M()->query("SELECT count(1) as count FROM " . C('DB_PREFIX'). "eaterplanet_ecommerce_orderdistribution_order a  "
                . " left join " . C('DB_PREFIX'). "eaterplanet_ecommerce_order o on a.order_id=o.order_id "
                . " WHERE 1=1 " . $condition);
        $total = $count_list[0]['count'];
        $pager = pagination2($total, $pindex, $psize);
        $this->id = $id;
        $this->order_no = $order_no;
        $this->list = $list;
        $this->pager = $pager;
        $this->display();
    }

}
?>
