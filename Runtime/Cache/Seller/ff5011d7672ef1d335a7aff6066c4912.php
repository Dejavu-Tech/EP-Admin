<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<?php $shoname_name = D('Home/Front')->get_config_by_name('shoname'); ?>
	<title><?php echo $shoname_name; ?></title>
	<link rel="shortcut icon" href="" />

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="吃货星球，先进的电商拼团，小程序，APP，集成解决方案">
	<meta name="keywords" content="吃货星球，先进的电商拼团，小程序，APP，集成解决方案">
	<meta name="author" content="Dejavu871.Tech.">
	<link rel="icon" href="/assets/images/favicon.png" type="image/x-icon">
	<link rel="shortcut icon" href="/assets/images/favicon.png" type="image/x-icon">

	<!-- Google font-->
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
	<!-- Font Awesome-->
	<link rel="stylesheet" type="text/css" href="/assets/css/fontawesome.css">
	<!-- ico-font-->
	<link rel="stylesheet" type="text/css" href="/assets/css/icofont.css">
	<!-- Themify icon-->
	<link rel="stylesheet" type="text/css" href="/assets/css/themify.css">
	<!-- Flag icon-->
	<link rel="stylesheet" type="text/css" href="/assets/css/flag-icon.css">
	<!-- Feather icon-->
	<link rel="stylesheet" type="text/css" href="/assets/css/feather-icon.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/animate.css">
	<!-- Plugins css start-->
	<link rel="stylesheet" type="text/css" href="/assets/css/chartist.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/date-picker.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/prism.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/material-design-icon.css">

	<link rel="stylesheet" type="text/css" href="/assets/css/datatables.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/pe7-icon.css">
	<!-- Plugins css Ends-->
	<!-- Bootstrap css-->
	<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.css">
	<!-- App css-->
	<link rel="stylesheet" type="text/css" href="/assets/css/style.css">
	<link id="color" rel="stylesheet" href="/assets/css/color-1.css" media="screen">
	<!-- Responsive css-->
	<link rel="stylesheet" type="text/css" href="/assets/css/responsive.css">

<!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
<!--[if lt IE 9]>
  <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
  <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<link href="/assets/css/bootstrap.min1.css?v=201903260001" rel="stylesheet">

<script type="text/javascript">
            window.sysinfo = {
            <?php if(!empty($_W['uniacid'])){ ?>'uniacid': '<?php echo ($_W['uniacid']); ?>',<?php } ?>

            <?php if(!empty($_W['acid'])){ ?>'acid': '<?php echo ($_W['acid']); ?>',<?php } ?>

            <?php if(!empty($_W['openid'])){ ?>'openid': '<?php echo ($_W['openid']); ?>',<?php } ?>

            <?php if(!empty($_W['uid'])){ ?>'uid': '<?php echo ($_W['uid']); ?>',<?php } ?>

            'isfounder': <?php if(!empty($_W['isfounder'])){ ?>1<?php }else{ ?>0<?php } ?>,

            'siteroot': '<?php echo ($_W['siteroot']); ?>',
                    'siteurl': '<?php echo ($_W['siteurl']); ?>',
                    'attachurl': '<?php echo ($_W['attachurl']); ?>',
                    'attachurl_local': '<?php echo ($_W['attachurl_local']); ?>',
                    'attachurl_remote': '<?php echo ($_W['attachurl_remote']); ?>',
                    'module' : {'url' : '<?php if( defined('MODULE_URL') ) { ?>{MODULE_URL}<?php } ?>', 'name' : '<?php if (defined('IN_MODULE') ) { ?>{IN_MODULE}<?php } ?>'},
            'cookie' : {'pre': ''},
            'account' : <?php echo json_encode($_W['account']);?>,
            };
        </script>

<script type="text/javascript" src="/resource/js/lib/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="/resource/js/lib/bootstrap.min.js"></script>
<script type="text/javascript" src="/resource/js/app/util.js?v=201903260001"></script>
<script type="text/javascript" src="/resource/js/app/common.min.js?v=201903260001"></script>
<script type="text/javascript" src="/resource/js/require.js?v=201903260001"></script>
<script type="text/javascript" src="/resource/js/lib/jquery.nice-select.js?v=201903260001"></script>

   <link rel="stylesheet" type="text/css" href="/assets/css/ep/eaterplanet.css?v=4.0.0">
</head>
<body class="custom-scrollbar" >

<div class="page-wrapper custom-scrollbar">
	<div class="page-body-wrapper">
		<div class="page-body" style="margin: 0;min-height: calc(100vh - 55px);">
			<div class="container-fluid">
				<div class="page-header">
					<div class="row">
						<div class="col-lg-6 main-header">
							<h6 class="mb-0">当前位置 </h6>
							<h2>订单<span>设置</span></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12 btc-buy-sell">
						<div class="card">
							<form action="" method="post" class="form theme-form layui-form" lay-filter="component-layui-form-item" enctype="multipart/form-data" >
								<div class="card-body">
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">限制购买距离</label>
										<div class="col-sm-10" id="radPickupDateTip">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary"><input id="radioinline1" type="radio" name="data[shop_limit_buy_distance]" value="0" <?php if(!isset($data['shop_limit_buy_distance']) || $data['shop_limit_buy_distance'] ==0 ){ ?>checked<?php } ?> title="" /><label class="mb-0" for="radioinline1">不限制</label></div>
												<div class="radio radio-primary"><input id="radioinline2" type="radio" name="data[shop_limit_buy_distance]" value="1" <?php if(isset($data['shop_limit_buy_distance']) && $data['shop_limit_buy_distance'] ==1 ){ ?>checked<?php } ?> title="" /><label class="mb-0" for="radioinline2">限制</label></div>
											</div>
											<small>只对团长配送方式有效</small>
										</div>
									</div>
									<div class="form-group row" id="txtPickupDateTip" style="<?php if(isset($data['shop_buy_distance']) && $data['shop_limit_buy_distance'] ==1){ ?>display:flex;<?php }else{ ?>display: none;<?php } ?>">
										<label class="col-sm-2 col-form-label"></label>
										<div class="col-sm-10">
											<div class="input-group pill-input-group">
												<input class="form-control" name="data[shop_buy_distance]" type="text" placeholder="填写限制距离" value="<?php echo ($data['shop_buy_distance']); ?>">
												<div class="input-group-append"><span class="input-group-text">KM</span></div>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">下单最低金额</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline7" name='data[open_man_orderbuy]' value='1' <?php if($data['open_man_orderbuy']=='1'){ ?>checked<?php } ?> onclick="$('#manbuy_order').show()" title="开启" /><label class="mb-0" for="radioinline7">开启</label> </div>
												<div class='radio radio-primary'><input type='radio' id="radioinline8" name='data[open_man_orderbuy]' value='0' <?php if(empty($data['open_man_orderbuy'])){ ?>checked<?php } ?>  onclick="$('#manbuy_order').hide()" title="关闭" /><label class="mb-0" for="radioinline8">关闭</label> </div>
											</div>
											<small>购物车满多少才可以下单购买</small>
										</div>
									</div>
									<div class="form-group row" id="manbuy_order" style="<?php if( empty($data['open_man_orderbuy']) ){ ?>display:none;<?php } ?>">
										<label class="col-sm-2 col-form-label"></label>
										<div class="col-sm-10">
											<div class="input-group pill-input-group">
												<input type="text" name="data[man_orderbuy_money]" class="form-control" value="<?php echo ($data['man_orderbuy_money']); ?>" />
												<div class="input-group-append"><span class="input-group-text">元</span></div>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">后台付款</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary"><input id="radioinline41" type="radio" name="data[open_admin_payment]" value="1" <?php if( !isset($data['open_admin_payment']) || $data['open_admin_payment']=='1'){ ?>checked<?php } ?> title="" /> <label class="mb-0" for="radioinline41">开启</label></div>
												<div class="radio radio-primary"><input id="radioinline42" type="radio" name="data[open_admin_payment]" value="0" <?php if( $data['open_admin_payment']== '0'){ ?>checked<?php } ?> title="" /><label class="mb-0" for="radioinline42">关闭</label> </div>
											</div>
											<small>允许后台对“待付款订单”进行付款操作</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">自动取消未支付订单</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline3" name='data[open_auto_delete]' value='1' <?php if($data['open_auto_delete']=='1'){ ?>checked<?php } ?> onclick="$('#cancle_order').show()" title="开启" /> <label class="mb-0" for="radioinline3">开启</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline4" name='data[open_auto_delete]' value='0' <?php if(empty($data['open_auto_delete'])){ ?>checked<?php } ?>  onclick="$('#cancle_order').hide()" title="关闭" /><label class="mb-0" for="radioinline4">关闭</label> </div>
											</div>
											<small>订单生成后 ，用户在x小时内未支付，系统自动取消</small>
										</div>
									</div>
									<div class="form-group row" id="cancle_order" style="<?php if( empty($data['open_auto_delete']) ){ ?>display:none;<?php } ?>">
										<label class="col-sm-2 col-form-label"></label>
										<div class="col-sm-10">
											<div class="input-group pill-input-group">
												<input type="text" name="data[auto_cancle_order_time]" class="form-control" value="<?php echo ($data['auto_cancle_order_time']); ?>" />
												<div class="input-group-append"><span class="input-group-text">小时</span></div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">减库存方式</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline5" name='data[kucun_method]' value='0' <?php if(empty($data['kucun_method']) || $data['kucun_method'] ==0){ ?>checked<?php } ?> title="下单减库存" /> <label class="mb-0" for="radioinline5">下单减库存</label></div>
												<div class='radio radio-primary' style="display:none;"><input type='radio' id="radioinline6" name='data[kucun_method]' value='1' <?php if($data['kucun_method']=='1'){ ?>checked<?php } ?> title="付款后减库存" /> <label class="mb-0" for="radioinline6">付款后减库存</label></div>
											</div>
											<small>用户下单减库存，取消订单将返还库存数量</small>
										</div>
									</div>




									<div class="form-group row" style="display:none;">
										<label class="col-sm-2 col-form-label">满多少免运费</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline9" name='data[open_man_free_shipping]' value='1' <?php if($data['open_man_free_shipping']=='1'){ ?>checked<?php } ?> onclick="$('#freeshipping_order').show()" title="开启" /> <label class="mb-0" for="radioinline9">开启</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline10" name='data[open_man_free_shipping]' value='0' <?php if(empty($data['open_man_free_shipping'])){ ?>checked<?php } ?>  onclick="$('#freeshipping_order').hide()" title="关闭" /> <label class="mb-0" for="radioinline10">关闭</label></div>
												<small>团长配送或快递模式下，满多少免运费</small>
											</div>
										</div>
									</div>
									<div class="form-group row" id="freeshipping_order" style="<?php if( empty($data['open_man_free_shipping']) ){ ?>display:none;<?php } ?>">
										<label class="col-sm-2 col-form-label"></label>
										<div class="col-sm-10">
											<div class="input-group pill-input-group">
												<input type="text" name="data[man_free_shipping_money]" class="form-control" value="<?php echo ($data['man_free_shipping_money']); ?>" />
												<div class="input-group-append"><span class="input-group-text">元</span></div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">开启redis服务</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0" id="redis_server">
												<div class='radio radio-primary'><input type='radio' id="radioinline11" name='data[open_redis_server]' value='0' <?php if(empty($data['open_redis_server']) || $data['open_redis_server'] ==0){ ?>checked<?php } ?> title=""  /> <label class="mb-0" for="radioinline11">关闭</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline12" name='data[open_redis_server]' value='1' <?php if($data['open_redis_server']=='1'){ ?>checked<?php } ?>  title="" /> <label class="mb-0" for="radioinline12">开启</label></div>
											</div>
											<small>更精确控制高并发下，商品超库存问题，请确保安装php_redis控制，否则无法启用。redis不需要验证可以不填写auth。<span class="txt-danger">开启redis,建议使用下单减库存方式</span></small>

										</div>
									</div>

									<div class="redis_div" id="redis_div" style="<?php if( !empty($data['open_redis_server']) && $data['open_redis_server']=='1' ){ ?> display:block;<?php }else{ ?> display:none; <?php } ?> ">
										<div class="form-group row" >
											<label class="col-sm-2 col-form-label">redis-host:</label>
											<div class="col-sm-10">
												<div class="input-group pill-input-group">
													<input type="text" name="data[redis_host]" class="form-control" value="<?php echo ($data['redis_host']); ?>" />
												</div>
											</div>
										</div>
										<div class="form-group row" >
											<label class="col-sm-2 col-form-label">redis-port:</label>
											<div class="col-sm-10">
												<div class="input-group pill-input-group">
													<input type="text" name="data[redis_port]" class="form-control" value="<?php echo ($data['redis_port']); ?>" />
												</div>
											</div>
										</div>
										<div class="form-group row" >
											<label class="col-sm-2 col-form-label">redis-auth:</label>
											<div class="col-sm-10">
												<div class="input-group pill-input-group">
													<input type="text" name="data[redis_auth]" class="form-control" value="<?php echo ($data['redis_auth']); ?>" />
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">订单提醒开关</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline14" name='data[order_notify_switch]' value='0' <?php if(empty($data['order_notify_switch']) || $data['order_notify_switch'] ==0){ ?>checked<?php } ?>  title="关闭" /> <label class="mb-0" for="radioinline14">关闭</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline15" name='data[order_notify_switch]' value='1' <?php if($data['order_notify_switch']=='1'){ ?>checked<?php } ?> title="开启" /> <label class="mb-0" for="radioinline15">开启</label></div>
											</div>
											<small>首页、商品详情页订单提醒开关</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">付款后分享</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline16" name='data[order_pay_after_share]' value='0' <?php if(empty($data['order_pay_after_share']) || $data['order_pay_after_share'] ==0){ ?>checked<?php } ?>  title="关闭" /> <label class="mb-0" for="radioinline16">关闭</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline17" name='data[order_pay_after_share]' value='1' <?php if($data['order_pay_after_share']=='1'){ ?>checked<?php } ?> title="开启" /> <label class="mb-0" for="radioinline17">开启</label></div>
											</div>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">付款后分享图片</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('data[order_pay_after_share_img]', $data['order_pay_after_share_img']);?>
											<small>不传则使用默认商品分享图，比列：5:4</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">待发货“取消订单”</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline18" name='data[order_can_del_cancle]' value='0' <?php if(empty($data['order_can_del_cancle']) || $data['order_can_del_cancle'] =='0'){ ?>checked<?php } ?>  title="开启" /> <label class="mb-0" for="radioinline18">开启</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline19" name='data[order_can_del_cancle]' value='1' <?php if($data['order_can_del_cancle']=='1'){ ?>checked<?php } ?> title="关闭" /> <label class="mb-0" for="radioinline19">关闭</label></div>
											</div>
											<small>（备注：用户取消订单后，取消订单并全额退款）</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">后台订单语音提醒</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline27" name='data[is_open_order_voice_notice]' value='0' <?php if(empty($data['is_open_order_voice_notice']) || $data['is_open_order_voice_notice']==0){ ?>checked<?php } ?> title="关闭" /> <label class="mb-0" for="radioinline27">关闭</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline28" name='data[is_open_order_voice_notice]' value='1' <?php if($data['is_open_order_voice_notice']=='1'){ ?>checked<?php } ?> title="开启"  /> <label class="mb-0" for="radioinline28">开启</label></div>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">小票打印</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline20" name='data[open_feier_print]' value='1' <?php if($data['open_feier_print']=='1'){ ?>checked<?php } ?> onclick="$('#feier_print').show();$('#yilianyun_print').hide();" title="开启飞鹅打印机" /> <label class="mb-0" for="radioinline20">开启飞鹅打印机</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline21" name='data[open_feier_print]' value='2' <?php if($data['open_feier_print']=='2'){ ?>checked<?php } ?> onclick="$('#yilianyun_print').show();$('#feier_print').hide();" title="开启易联云打印机" /> <label class="mb-0" for="radioinline21">开启易联云打印机</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline22" name='data[open_feier_print]' value='0' <?php if(empty($data['open_feier_print'])){ ?>checked<?php } ?>  onclick="$('#feier_print').hide();$('#yilianyun_print').hide();" title="关闭" /> <label class="mb-0" for="radioinline22">关闭</label></div>
											</div>
											<small>开启后，系统将自动打印新订单</small>
										</div>
									</div>
									<div class="form-group row" id="feier_print" style="<?php if( empty($data['open_feier_print']) || $data['open_feier_print'] ==2 ){ ?>display:none;<?php } ?>">
										<label class="col-sm-2 col-form-label"></label>
										<div class="col-sm-10">
											<div class="input-group pill-input-group">
												<div class="input-group-prepend"><span class="input-group-text">sn:</span></div>
												<input type="text" name="data[feier_print_sn]" class="form-control" value="<?php echo ($data['feier_print_sn']); ?>" />
												<div class="input-group-append"><span class="input-group-text">key:</span></div>
												<input type="text" name="data[feier_print_key]" class="form-control" value="<?php echo ($data['feier_print_key']); ?>" />
												<div class="input-group-append"><span class="input-group-text">打印联数:</span></div>
												<input type="text" name="data[feier_print_lian]" class="form-control" value="<?php echo !isset($data['feier_print_lian']) ? 1 : $data['feier_print_lian']; ?>" />
											</div>
											<small>支持飞鹅打印机 (sn,key在机子底部)，打印联数最小为1</small>
										</div>
									</div>
									<div class="form-group row" id="yilianyun_print" style="<?php if( empty($data['open_feier_print']) || $data['open_feier_print'] == 1 ){ ?>display:none;<?php } ?>">
										<label class="col-sm-2 col-form-label"></label>
										<div class="col-sm-10">
											<div class="input-group pill-input-group m-b-10">
												<div class="input-group-prepend"><span class="input-group-text">应用id:</span></div>
												<input type="text" name="data[yilian_client_id]" class="form-control" value="<?php echo ($data['yilian_client_id']); ?>" />
												<div class="input-group-append"><span class="input-group-text">应用密钥:</span></div>
												<input type="text" name="data[yilian_client_key]" class="form-control" value="<?php echo ($data['yilian_client_key']); ?>" />
											</div>
											<div class="input-group pill-input-group">
												<div class="input-group-prepend"><span class="input-group-text">打印机终端号:</span></div>
												<input type="text" name="data[yilian_machine_code]" class="form-control" value="<?php echo ($data['yilian_machine_code']); ?>" />
												<div class="input-group-append"><span class="input-group-text">终端密钥:</span></div>
												<input type="text" name="data[yilian_msign]" class="form-control" value="<?php echo ($data['yilian_msign']); ?>" />
												<div class="input-group-append"><span class="input-group-text">打印联数:</span></div>
												<input type="text" name="data[yilian_print_lian]" class="form-control" value="<?php echo !isset($data['yilian_print_lian']) ? 1 : $data['yilian_print_lian']; ?>" />
											</div>
											<small>支持易联云K4、K5、K6打印机 (打印机终端号,终端密钥在机子底部,开发者中心应用类型选择： 自有型应用)，没有开发者账号<a href="https://dev.yilianyun.net/" target="_blank">请点击申请</a></small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">打印隐藏会员手机</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class='radio radio-primary'><input type='radio' id="radioinline23" name='data[is_printhide_membermobile]' value='0' <?php if( empty($data['is_printhide_membermobile']) || $data['is_printhide_membermobile'] ==0){ ?>checked<?php } ?>  title="否" /> <label class="mb-0" for="radioinline23">否</label></div>
												<div class='radio radio-primary'><input type='radio' id="radioinline24" name='data[is_printhide_membermobile]' value='1' <?php if( $data['is_printhide_membermobile']=='1'){ ?>checked<?php } ?> title="是" /> <label class="mb-0" for="radioinline24">是</label></div>
											</div>
											<small>小票打印时，隐藏会员手机号，默认不隐藏</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">打印内容</label>
										<div class="col-sm-10">
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_print_cancleorder]" class="form-control valid" <?php if(!empty($data) && $data['is_print_cancleorder'] ==1 ){ ?>checked<?php } ?> value="1"  style="display: none" /><h7>取消订单打印</h7>
											</div>
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_print_admin_cancleorder]" class="form-control valid" <?php if(!empty($data) && $data['is_print_admin_cancleorder'] ==1 ){ ?>checked<?php } ?> value="1" title="" style="display: none" /><h7>后台整单立即退款打印</h7>
											</div>
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_print_dansupply_order]" class="form-control valid" <?php if(!empty($data) && $data['is_print_dansupply_order'] ==1 ){ ?>checked<?php } ?> value="1" title="" style="display: none" /><h7>独立商户订单打印</h7>
											</div>
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_print_member_note]" class="form-control valid" <?php if( !empty($data) && $data['is_print_member_note'] ==1 ){ ?>checked<?php } ?> value="1" title=""  style="display: none"/><h7>小票打印内容包含“会员详情-备注信息”</h7>
											</div>
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_print_order_note]" class="form-control valid" <?php if( !empty($data) && $data['is_print_order_note'] ==1 ){ ?>checked<?php } ?> value="1" title="" style="display: none"/><h7>小票打印内容包含“订单提交页自定义备注信息”</h7>
											</div>
											<small>默认付款后自动打印</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">订单留言</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary"><input id="radioinline25" type='radio' name='data[is_open_order_message]' value='0' <?php if(empty($data['is_open_order_message']) || $data['is_open_order_message']==0){ ?>checked<?php } ?> title="" /><label class="mb-0" for="radioinline25">关闭</label></div>
												<div class="radio radio-primary"><input id="radioinline26" type='radio' name='data[is_open_order_message]' value='1' <?php if($data['is_open_order_message']=='1'){ ?>checked<?php } ?> title="开启"/><label class="mb-0" for="radioinline26">开启</label></div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">开启余额支付</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary"><input id="radioinline29" type='radio' name='data[is_open_yue_pay]' value='0' <?php if(empty($data['is_open_yue_pay']) || $data['is_open_yue_pay']==0){ ?>checked<?php } ?> title="关闭" /><label class="mb-0" for="radioinline29">关闭</label></div>
												<div class="radio radio-primary"><input id="radioinline30" type='radio' name='data[is_open_yue_pay]' value='1' <?php if($data['is_open_yue_pay']=='1'){ ?>checked<?php } ?> title="开启" /><label class="mb-0" for="radioinline30">开启</label></div>
											</div>
											<small>开启后会员中心默认开启余额充值</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">售后申请可退包含</label>
										<div class="col-sm-10">
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_has_refund_deliveryfree]" class="form-control valid" <?php if( !empty($data) && ( !isset($data['is_has_refund_deliveryfree']) || $data['is_has_refund_deliveryfree'] ==1 ) ){ ?>checked<?php } ?> value="1" title="" style="display: none"/><h7>配送费（团长配送费/快递配送费）</h7>
											</div>

										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">开启售后期</label>
										<div class="col-sm-10" id="open_aftersale">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
											<div class="radio radio-primary"><input type="radio" id="radioinline31" name="data[open_aftersale]" <?php if(!isset($data['open_aftersale']) || $data['open_aftersale'] ==0 ){ ?> checked <?php } ?> value="0" title="关闭" /><label class="mb-0" for="radioinline31">关闭</label></div>
											<div class="radio radio-primary"><input type="radio" id="radioinline32" name="data[open_aftersale]" <?php if(isset($data['open_aftersale']) && $data['open_aftersale'] ==1 ){ ?> checked <?php } ?> value="1" title="开启" /><label class="mb-0" for="radioinline32">开启</label></div>
											</div>
											<small>售后期开启以后”确认收货“商品订单不马上结算订单金额给团长或商户，需要等售后期结束，商品订单佣金也需要等售后期结束才进行结算。（售后期包含商品售后期，以及商品订单的佣金结算期）</small>
										</div>
									</div>
									<div class="form-group row" id="open_aftersale_time" style="<?php if(isset($data['open_aftersale']) && $data['open_aftersale'] ==1){ ?>display:flex;<?php }else{ ?>display: none;<?php } ?>">
										<label class="col-sm-2 col-form-label"></label>
										<div class="col-sm-10">
											<div class="input-group pill-input-group">
												<input class="form-control" name="data[open_aftersale_time]" type="text" placeholder="填写售后天数" value="<?php echo ($data['open_aftersale_time']); ?>">
												<div class="input-group-append"><span class="input-group-text">天</span></div>
											</div>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">开启系统自动签收</label>
										<div class="col-sm-10 col-xs-12" id="open_autosale">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
											<div class="radio radio-primary"><input type="radio" id="radioinline33" name="data[open_auto_recive_order]" id="radioinline39"<?php if(!isset($data['open_auto_recive_order']) || $data['open_auto_recive_order'] ==0 ){ ?> checked <?php } ?> value="0" title="关闭" /><label class="mb-0" for="radioinline33">关闭</label></div>
											<div class="radio radio-primary"><input type="radio" id="radioinline34" name="data[open_auto_recive_order]" id="radioinline40"<?php if(isset($data['open_auto_recive_order']) && $data['open_auto_recive_order'] ==1 ){ ?> checked <?php } ?> value="1" title="开启" /><label class="mb-0" for="radioinline34">开启</label></div>
											</div>
											<small>订单自动签收默认关闭状态，如果需要勾选进行设置，建议默认为7天。（当订单待收货或待核销时间超过所设置时间）</small>
										</div>
									</div>
									<div class="form-group row" id="auto_recive_order_time" style="<?php if(isset($data['open_auto_recive_order']) && $data['open_auto_recive_order'] ==1){ ?>display:flex;<?php }else{ ?>display: none;<?php } ?>">
										<label class="col-sm-2 col-form-label"></label>
										<div class="col-sm-10">
											<div class="input-group pill-input-group">
												<input class="form-control" name="data[auto_recive_order_time]" type="text" placeholder="填写自动签收天数" value="<?php echo ($data['auto_recive_order_time']); ?>">
												<div class="input-group-append"><span class="input-group-text">天</span></div>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">显示团长电话</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary"><input id="radioinline35" type='radio' name='data[is_hidden_orderlist_phone]' value='0' <?php if(empty($data['is_hidden_orderlist_phone']) || $data['is_hidden_orderlist_phone']==0){ ?>checked<?php } ?> title="显示" /><label class="mb-0" for="radioinline35">显示</label></div>
												<div class="radio radio-primary"><input id="radioinline36" type='radio' name='data[is_hidden_orderlist_phone]' value='1' <?php if( $data['is_hidden_orderlist_phone']=='1' ){ ?>checked <?php } ?> title="关闭"  /><label class="mb-0" for="radioinline36">关闭</label></div>
											</div>
											<small>订单详情是否显示团长电话</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">猜你喜欢</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary"><input id="radioinline37" type='radio' name='data[is_show_order_guess_like]' value='0' <?php if( empty($data['is_show_order_guess_like']) || $data['is_show_order_guess_like']==0 ){ ?>checked<?php } ?> title="关闭" /><label class="mb-0" for="radioinline37">关闭</label></div>
												<div class="radio radio-primary"><input id="radioinline38" type='radio' name='data[is_show_order_guess_like]' value='1' <?php if( $data['is_show_order_guess_like']=='1' ){ ?>checked<?php } ?> title="开启"  /><label class="mb-0" for="radioinline38">开启</label></div>
											</div>
											<small>订单详情是否显示猜你喜欢商品列表</small>
										</div>
									</div>
									<div class="col-sm-2 btc-table p-l-0 m-t-20">
										<div class="btc-amount font-primary f-w-700 f-18 text-center">订单自动发货</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">同城配送订单</label>
										<div class="col-sm-10">
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_localtown_auto_delivery]" class="form-control valid" <?php if( !empty($data) && $data['is_localtown_auto_delivery'] == 1 ){ ?>checked<?php } ?> value="1" title="" style="display: none"/><h7>自动确认配送</h7>
											</div>
											<small>勾选自动确认配送以后，前后台订单状态在用户下单以后自动变更为“已发货，待收货”状态，不需要后台进行配送操作，配送员可以直接进行抢单配送。</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">团长配送订单</label>
										<div class="col-sm-10">
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_communityhead_auto_delivery]" class="form-control valid" <?php if( !empty($data) && $data['is_communityhead_auto_delivery'] == 1 ){ ?>checked<?php } ?> value="1" title="" style="display: none"/><h7>自动确认配送</h7>
											</div>
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_communityhead_auto_service]" class="form-control valid" <?php if( !empty($data) && $data['is_communityhead_auto_service'] == 1){ ?>checked<?php } ?> value="1" title="" style="display: none"/><h7>自动确认送达团长</h7>
											</div>
											<small>勾选自动确认配送/自动确认送达团长以后，前后台订单状态在用户下单以后自动变更为“配送中”状态，不需要后台进行配送操作,勾选其中一项的时候，订单还需要进行发货操作。</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">社区自提订单</label>
										<div class="col-sm-10">
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_ziti_auto_delivery]" class="form-control valid" <?php if( !empty($data) && $data['is_ziti_auto_delivery'] == 1 ){ ?>checked<?php } ?> value="1" title="" style="display: none"/><h7>自动确认配送</h7>
											</div>
											<div class="checkbox checkbox-primary m-l-0 row" >
												<input type="checkbox" lay-skin="primary" name="data[is_ziti_auto_service]" class="form-control valid" <?php if( !empty($data) && $data['is_ziti_auto_service'] == 1 ){ ?>checked<?php } ?> value="1" title="" style="display: none"/><h7>自动确认送达团长</h7>
											</div>
											<small>勾选自动确认配送/自动确认送达团长以后，前后台订单状态在用户下单以后自动变更为“配送中”，不需要后台进行配送操作，勾选其中一项的时候，订单还需要进行发货操作。</small>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<input type="submit" value="提交" lay-submit lay-filter="formDemo" class="btn btn-pill btn-primary"/>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<footer class="footer">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-6 footer-copyright">
						<p class="mb-0">Copyright © 2019-2021 Dejavu.Tech. All rights reserved.</p>
					</div>
					<div class="col-md-6">
						<p class="pull-right mb-0">吃货星球v4.0.1<i class="fa fa-heart"></i></p>
					</div>
				</div>
			</div>
		</footer>
	</div>
</div>

<script src="/layuiadmin/layui/layui1.js"></script>
<script type="text/javascript" src="/assets/js/jquery-migrate-1.1.1.js"></script>
<script src="/assets/js/jquery-ui.min.js"></script>

<!-- Bootstrap js-->

<!-- Sidebar jquery-->
<script src="/assets/js/sidebar-menu.js"></script>

<!-- feather icon js-->

<!-- Plugins JS start-->

<script src="/assets/js/chat-menu.js"></script>
<!-- Plugins JS Ends-->
<!-- Theme js-->
<script src="/assets/js/script.js"></script>

<!-- Theme js-->
<script src="/assets/js/theme-customizer/customizer1.js"></script>

<script>
	layui.config({
		base: '/layuiadmin/' //静态资源所在路径
	}).extend({
		index: 'lib/index' //主入口模块
	}).use('index');
</script>

<script>
//由于模块都一次性加载，因此不用执行 layui.use() 来加载对应模块，直接使用即可：
var layer = layui.layer;
var $;

var cur_open_div;

layui.use(['jquery', 'layer','form'], function(){
  $ = layui.$;
  var form = layui.form;

	form.on('radio(linktype)', function(data){
		if (data.value == 2) {
			$('#typeGroup').show();
		} else {
			$('#typeGroup').hide();
		}
	});


	$('#chose_link').click(function(){
		cur_open_div = $(this).attr('data-input');
		$.post("<?php echo U('util/selecturl', array('ok' => 1));?>", {}, function(shtml){
		 layer.open({
			type: 1,
			area: '700px',
			content: shtml //注意，如果str是object，那么需要字符拼接。
		  });
		});
	})

  //监听提交
	form.on('submit(formDemo)', function(data){
		$.ajax({
			url: data.form.action,
			type: data.form.method,
			data: data.field,
			dataType:'json',
			success: function (info) {

				if(info.status == 0)
				{
					layer.msg(info.result.message,{icon: 1,time: 2000});
				}else if(info.status == 1){
					var go_url = location.href;
					if( info.result.hasOwnProperty("url") )
					{
						go_url = info.result.url;
					}

					layer.msg('操作成功',{time: 1000,
						end:function(){
							location.href = info.result.url;
						}
					});
				}
			}
		});

		return false;
	});
})
</script>

<script type="text/javascript">
	$(document).ready(function(){
		$(".contact").mouseover(function(){
			$(".content").show("slow");
			$(".contact").mouseout(function(){
				$(".content").hide("slow");
			});
		});
	})
</script>

<script>

$(function(){
$('#radPickupDateTip input[type=radio]').click(function(){
	var s_val = $(this).val();
	if(s_val == 1)
	{
		$('#txtPickupDateTip').show();
	}else{
		$('#txtPickupDateTip').hide();
	}
 })

 $('#open_aftersale input[type=radio]').click(function(){
	var s_val = $(this).val();
	if(s_val == 1)
	{
		$('#open_aftersale_time').show();
	}else{
		$('#open_aftersale_time').hide();
	}
 })

  $('#open_autosale input[type=radio]').click(function(){
	var s_val = $(this).val();
	if(s_val == 1)
	{
		$('#auto_recive_order_time').show();
	}else{
		$('#auto_recive_order_time').hide();
	}
 })

 $('#redis_server input[type=radio]').click(function(){
	var s_val = $(this).val();
	 if(s_val == 1)
	{
		$('#redis_div').css('display','block');
	}else{
		$('#redis_div').css('display', 'none');
	}
 })

 })
</script>
</body>