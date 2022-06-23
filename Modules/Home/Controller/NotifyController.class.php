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

class NotifyController extends CommonController {

	protected function _initialize()
    {
    	parent::_initialize();
	}

	public function orderNotify()
    {

        $json_data =  file_get_contents("php://input");

        //log

        $oh = [];
        $oh['type'] = 'make';
        $oh['log'] = $json_data;
		M('log')->add( $oh );

        $data = json_decode( $json_data, true );

        if( !empty($data) && $data['return_code'] == 'success' )
        {
            $token = strtolower( $data['token'] );
            $order_no = $data['order_no'];

            $time = $data['time'];
            $status = $data['status'];
            $rider_name = $data['rider_name'];
            $rider_mobile = $data['rider_mobile'];

			$orderdistribution_order = M('eaterplanet_ecommerce_orderdistribution_order')->where( array('third_order_id' => $order_no ) )->find();
            $oh = [];
            $oh['type'] = 'step1';
            $oh['log'] = !empty($orderdistribution_order) ? 1 : 0;
            M('log')->add( $oh );


            if( !empty($orderdistribution_order) )
            {

                $appid = D('Home/Front')->get_config_by_name('wepro_appid' );
                $localtown_mk_token = D('Home/Front')->get_config_by_name('localtown_mk_token' );

                $new_token = strtolower( md5( $appid . $localtown_mk_token ) );

                $order_id = $orderdistribution_order['order_id'];

				$order_info = M('eaterplanet_ecommerce_order')->where( array('order_id' => $order_id ) )->find();
                $order_num_alias = $order_info['order_num_alias'];

                if( $token == $new_token )
                {
                    //校验正常开始处理逻辑
                    switch( $status )
                    {
                        case 'accepted': //抢单了：


                            //码科平台回调接口
                            $other_data = array();
                            $other_data['data_type'] = 'make';
                            $other_data['order_status'] = 2;
                            $other_data['dm_id'] = '';
                            $other_data['dm_name'] = $rider_name;
                            $other_data['dm_mobile'] = $rider_mobile;
                            $other_data['third_id'] = '';

                            //$order_sn, $order_status_id, $other_data

                            D('Seller/Order')->do_localtown_thirth_delivery_return( $order_num_alias,0,$other_data);
                            break;
                        case 'wait_to_shop':
                            //确认前往：+ 前往的日志

                            $oh = array();

                            $oh['order_id']= $order_id;

                            $oh['order_status_id']= 4;
                            $oh['comment']='配送员'.$rider_name.', 手机  '.$rider_mobile.':确认前往';

                            $oh['date_added']=time();
                            $oh['notify']=1;

							M('eaterplanet_ecommerce_order_history')->add( $oh );
                            break;
                        case 'payed':
                            //取消订单：+ 前往的日志

                            $oh = array();
                            $oh['order_id']= $order_id;

                            $oh['order_status_id']= 4;
                            $oh['comment']='配送员'.$rider_name.', 手机  '.$rider_mobile.':取消订单';

                            $oh['date_added']=time();
                            $oh['notify']=1;

                            M('eaterplanet_ecommerce_order_history')->add( $oh );

                            break;
                        case 'geted':
                            //确认取件  //码科平台回调接口

                            $oh = array();
                            $oh['order_id']= $order_id;

                            $oh['order_status_id']= 4;
                            $oh['comment']='配送员'.$rider_name.', 手机  '.$rider_mobile.':确认取件';

                            $oh['date_added']=time();
                            $oh['notify']=1;

                            M('eaterplanet_ecommerce_order_history')->add( $oh );


                            $other_data = array();
                            $other_data['data_type'] = 'make';
                            $other_data['order_status'] = 3;
                            $other_data['dm_id'] = '';
                            $other_data['dm_name'] = $rider_name;
                            $other_data['dm_mobile'] = $rider_mobile;
                            $other_data['third_id'] = '';

                            D('Seller/Order')->do_localtown_thirth_delivery_return( $order_num_alias,0, $other_data );
                            break;
                        case 'gotoed':
                            //确认完成
                            $oh = array();
                            $oh['order_id']= $order_id;

                            $oh['order_status_id']= 4;
                            $oh['comment']='配送员'.$rider_name.', 手机  '.$rider_mobile.':确认送达';

                            $oh['date_added']=time();
                            $oh['notify']=1;

                            M('eaterplanet_ecommerce_order_history')->add( $oh );

                            $other_data = array();
                            $other_data['data_type'] = 'make';
                            $other_data['order_status'] = 4;

                            D('Seller/Order')->do_localtown_thirth_delivery_return( $order_num_alias,6,$other_data);

                            break;
                    }

                }
            }

        }


        echo 'success';

        //Token加密为模块appid+token md5

        //抢单了：
        //{"token":"507e9dfa06df88b7655cb2671abe3ccb","order_no":"A20210118440401921","return_code":"success",
        //"time":1610959924,"status":"accepted","rider_name":"\u5c0f\u72ee","rider_mobile":"15159513836"}

        //确认前往：
        //{"token":"507e9dfa06df88b7655cb2671abe3ccb","order_no":"A20210118440401921","return_code":"success",
        //"time":1610959983,"status":"wait_to_shop","rider_name":"\u5c0f\u72ee","rider_mobile":"15159513836"}

        //确认取件
        //{"token":"507e9dfa06df88b7655cb2671abe3ccb","order_no":"A20210118440401921","return_code":"success",
        //"time":1610960013,"status":"geted","rider_name":"\u5c0f\u72ee","rider_mobile":"15159513836"}

        //确认完成
        //{"token":"507e9dfa06df88b7655cb2671abe3ccb","order_no":"A20210118440401921","return_code":"success",
        //"time":1610960073,"status":"gotoed","rider_name":"\u5c0f\u72ee","rider_mobile":"15159513836"}


        /**
         *
         * {"token":"24ea86c9748d8a476a7e0519f5ce32c7","order_no":"A20200424875992271",
         * "return_code":"success","time":1587698827,"status":"gotoed",
         * "rider_name":"\u6881\u534e\u6587","rider_mobile":"18178110414"}
         *
         */


    }
}
