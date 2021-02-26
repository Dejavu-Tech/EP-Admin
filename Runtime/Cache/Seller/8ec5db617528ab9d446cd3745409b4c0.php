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
<link href="/assets/css/ep/minapp.css?v=1" rel="stylesheet">
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
							<h2><span>个人中心图标设置</span></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<div class="card">
							<form action="" method="post" class="form theme-form layui-form" lay-filter="component-layui-form-item" enctype="multipart/form-data" >
								<div class="card-body">
									<div class="clearfix">
										<div class="col-xs-6 col-md-5">
											<div class="minapp">
												<div class="titlebar"><img src="/assets/ep/images/minappbar.jpg"></div>
												<div class="user">
													<div class="user-top">
														<div class="avatar">头像</div>
														<div class="username">xxx</div>
													</div>
													<div class="order">
														<div class="order-title">我的订单</div>
														<div class="orderTab">
															<div class="order_status">
																<?php echo tpl_form_field_image_sin('parameter[user_order_menu_icon1]', $data['user_order_menu_icons']['i1'], '', array('class_extra'=>'order_menu_icon'));?>
																<div class="order_status_name">待付款</div>
															</div>
															<div class="order_status">
																<?php echo tpl_form_field_image_sin('parameter[user_order_menu_icon2]', $data['user_order_menu_icons']['i2'], '', array('class_extra'=>'order_menu_icon'));?>
																<div class="order_status_name">待配送</div>
															</div>
															<div class="order_status">
																<?php echo tpl_form_field_image_sin('parameter[user_order_menu_icon3]', $data['user_order_menu_icons']['i3'], '', array('class_extra'=>'order_menu_icon'));?>
																<div class="order_status_name">待提货</div>
															</div>
															<div class="order_status">
																<?php echo tpl_form_field_image_sin('parameter[user_order_menu_icon4]', $data['user_order_menu_icons']['i4'], '', array('class_extra'=>'order_menu_icon'));?>
																<div class="order_status_name">已提货</div>
															</div>
															<div class="order_status">
																<?php echo tpl_form_field_image_sin('parameter[user_order_menu_icon5]', $data['user_order_menu_icons']['i5'], '', array('class_extra'=>'order_menu_icon'));?>
																<div class="order_status_name">售后服务</div>
															</div>
														</div>
													</div>
													<div class="form-group row" style="margin-top: 20px;">
														<label class="col-sm-2 col-form-label" style="width: auto;">工具栏显示</label>
														<div class="col-sm-10" style="margin-left: 100px;">
															<input type="radio" name="parameter[user_tool_showtype]" value="0" lay-filter="toollisttype" title="列表模式" <?php if( empty($data) || $data['user_tool_showtype'] ==0 ){ ?>checked <?php } ?> />
															<input type="radio" name="parameter[user_tool_showtype]" value="1" lay-filter="toollisttype" title="宫格模式" <?php if( !empty($data) && $data['user_tool_showtype'] ==1 ){ ?>checked <?php } ?> />
														</div>
													</div>
													<div class="tool">
														<div class="toolList <?php if($data['user_tool_showtype'] ==1) { echo 'grad'; } ?>">
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon1]', $data['user_tool_icons']['i1'], '', array('class_extra'=>'tool_icon'));?>
																<span>余额</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon10]', $data['user_tool_icons']['i10'], '', array('class_extra'=>'tool_icon'));?>
																<span>我的接龙</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon11]', $data['user_tool_icons']['i11'], '', array('class_extra'=>'tool_icon'));?>
																<span>商品核销</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon2]', $data['user_tool_icons']['i2'], '', array('class_extra'=>'tool_icon'));?>
																<span>积分</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon3]', $data['user_tool_icons']['i3'], '', array('class_extra'=>'tool_icon'));?>
																<span>优惠券</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon4]', $data['user_tool_icons']['i4'], '', array('class_extra'=>'tool_icon'));?>
																<span>核销管理</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon5]', $data['user_tool_icons']['i5'], '', array('class_extra'=>'tool_icon'));?>
																<span>团长中心</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon6]', $data['user_tool_icons']['i6'], '', array('class_extra'=>'tool_icon'));?>
																<span>商户</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon7]', $data['user_tool_icons']['i7'], '', array('class_extra'=>'tool_icon'));?>
																<span>常见帮助</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon8]', $data['user_tool_icons']['i8'], '', array('class_extra'=>'tool_icon'));?>
																<span>联系客服</span>
															</div>
															<div class="item-main">
																<?php echo tpl_form_field_image_sin('parameter[user_tool_icon9]', $data['user_tool_icons']['i9'], '', array('class_extra'=>'tool_icon'));?>
																<span>关于我们</span>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xs-6 col-md-7 user-details">
											<div class="details-title">备注：</div>
											<p>1.订单图标大小为56*56；</p>
											<p>2.工具栏菜单图标大小为40*40,宫格模式建议尺寸100*100。</p>
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

layui.use(['jquery', 'layer','form'], function(){
  	$ = layui.$;
  	var form = layui.form;
	form.on('radio(toollisttype)', function(data){
		if (data.value == 1) {
			$('.toolList').addClass('grad');
		} else {
			$('.toolList').removeClass('grad');
		}
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
</body>