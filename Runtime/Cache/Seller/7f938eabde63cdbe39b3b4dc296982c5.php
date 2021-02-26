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
	<script type="text/javascript" src="/resource/components/colpick/colpick.js"></script>
	<link href="/resource/components/colpick/colpick.css" rel="stylesheet">
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
							<h6 class="mb-0">当前位置</h6>
							<h2>基本<span>设置</span> </h2>
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
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">商城名称</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[shoname]" class="form-control" value="<?php echo ($data['shoname']); ?>" />
											<small>后台名称及小程序导航栏显示名称，定制版页面不显示</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">商城LOGO</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[shoplogo]', $data['shoplogo']);?>
											<small>正方型图片</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">本站网址</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[shop_domain]" class="form-control" value="<?php echo ($data['shop_domain']); ?>" />
											<small>示例：https://域名/ ，网址最后的“/”必填，未配置https的，需要配置https</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页分享标题</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[shop_index_share_title]" class="form-control" value="<?php echo ($data['shop_index_share_title']); ?>" />
											<small>未填写将默认使用商城名称作为分享标题</small>
										</div>
									</div>

									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">首页分享图片</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[shop_index_share_image]', $data['shop_index_share_image']);?>
											<small>支持PNG及JPG，显示图片长宽比是 5:4。</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页商品显示模式</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline1" type="radio" name="parameter[index_list_theme_type]" value="0" <?php if(!empty($data) && $data['index_list_theme_type'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline1">小图</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline2" type="radio" name="parameter[index_list_theme_type]" value="1" <?php if(empty($data) || $data['index_list_theme_type'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline2">大图</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline3" type="radio" name="parameter[index_list_theme_type]" value="3" <?php if(empty($data) || $data['index_list_theme_type'] ==3 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline3">瀑布流（一行两个商品）</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline41" type="radio" name="parameter[index_list_theme_type]" value="2" <?php if(empty($data) || $data['index_list_theme_type'] ==2 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline41">三乘三（一行三个商品）</label>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group row" style="display:none;">
										<label class="col-sm-2 col-form-label">页面导航条背景颜色</label>
										<div class="col-sm-10">
											<div class="" style="margin:0px;">
												<div class="layui-input-inline" style="width: 120px;">
													<input type="text" name="parameter[nav_bg_color]" value="<?php echo ($data['nav_bg_color']); ?>" placeholder="请选择颜色" class="form-control" id="test-colorpicker-form-input">
												</div>
												<div class="layui-inline" style="left: -11px;">
													<div id="minicolors"></div>
												</div>
											</div>
											<small>背景颜色值，有效值为十六进制颜色。默认色值：<font color="#F75451">#F75451</font></small>
										</div>
									</div>

									<div class="form-group row" style="display:none;">
										<label class="col-sm-2 col-form-label">页面标题文字颜色</label>
										<div class="col-sm-10">
											<input type="radio" name="parameter[nav_font_color]" value="#ffffff" title="白色" <?php if(!empty($data) && $data['nav_font_color'] =='#ffffff' ){ ?>checked <?php } ?> />
											<input type="radio" name="parameter[nav_font_color]" value="#000000" title="黑色" <?php if(empty($data) || $data['nav_font_color'] =='#000000' ){ ?>checked <?php } ?> />
											<hr >
											<small>前景颜色值，包括按钮、标题、状态栏的颜色，仅支持 #ffffff 和 #000000 <br/>为避免重复请求，采用缓存机制，有效时长为十分钟，如需马上生效可删除小程序重新进入</small>
										</div>

									</div>

									<div class="form-group row" style="display:none;">
										<label class="col-sm-2 col-form-label">主题颜色</label>
										<div class="col-sm-10">
											<div class="" style="margin:0px;">
												<div class="layui-input-inline" style="width: 120px;">
													<input type="text" name="parameter[skin]" value="<?php echo ($data['skin']); ?>" placeholder="请选择颜色" class="form-control" id="skin-colorpicker-form-input">
												</div>
												<div class="layui-inline" style="left: -11px;">
													<div id="skincolors"></div>
												</div>
											</div>
											<small>全局主题颜色(页面导航条背景颜色,<a lay-href="<?php echo U('config.weprogram.tabbar');?>" title="去设置" class="text-primary">底部菜单</a>需单独设置)，有效值为十六进制颜色。默认色值：<font color="#F75451">#F75451</font></small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">腾讯地图AppKey</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[tx_map_key]" class="form-control" value="<?php echo ($data['tx_map_key']); ?>" />
											<small><a href="https://lbs.qq.com/console/key.html" class="text-primary" target="_blank">点击申请</a>&nbsp;用于地图定位、显示社区团长位置

												<a href="/assets/ep/images/tx_key_demo.png" class="text-primary" title="点击查看" target="_balnk">地图申请示例</a>
												<a href="/assets/ep/images/tx_map_request_demo.png" class="text-primary" title="点击查看" target="_balnk">小程序域名设置示例</a>
											</small>
										</div>

									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">群体名称</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[group_name]" class="form-control" value="<?php echo ($data['group_name']); ?>" />
											<small>默认：社区</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">群主名称</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[owner_name]" class="form-control" value="<?php echo ($data['owner_name']); ?>" />
											<small>默认：团长</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">小区团长：</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[haibao_group_name]" class="form-control" value="<?php echo ($data['haibao_group_name']); ?>" />
											<small>默认：小区团长，首页海报上文字</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页顶部背景</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline4" type="radio" name="parameter[index_top_img_bg_open]" value="1" <?php if(!empty($data) && $data['index_top_img_bg_open'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline4">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline5" type="radio" name="parameter[index_top_img_bg_open]" value="0" <?php if(empty($data) || $data['index_top_img_bg_open'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline5">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页分享按钮</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline6" type="radio" name="parameter[index_share_switch]" value="0" <?php if(!empty($data) && $data['index_share_switch'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline6">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline7" type="radio" name="parameter[index_share_switch]" value="1" <?php if(empty($data) || $data['index_share_switch'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline7">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页抢购切换</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline8" type="radio" name="parameter[index_change_cate_btn]" value="1" <?php if(empty($data) || $data['index_change_cate_btn'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline8">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline9" type="radio" name="parameter[index_change_cate_btn]" value="0" <?php if(!empty($data) && $data['index_change_cate_btn'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline9">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页客服按钮</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline10" type="radio" name="parameter[index_service_switch]" value="0" <?php if(empty($data) || $data['index_service_switch'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline10">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline11" type="radio" name="parameter[index_service_switch]" value="1" <?php if(!empty($data) && $data['index_service_switch'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline11">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">其他页面客服按钮</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline12" type="radio" name="parameter[user_service_switch]" value="0" <?php if(isset($data['user_service_switch']) && $data['user_service_switch']==0){ ?>checked<?php } ?> />
													<label class="mb-0" for="radioinline12">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline13" type="radio" name="parameter[user_service_switch]" value="1" <?php if(!isset($data['user_service_switch']) || $data['user_service_switch']==1){ ?>checked<?php } ?> />
													<label class="mb-0" for="radioinline13">显示</label>
												</div>
											</div>
											<br/>
											<small>商品详情、个人中心、订单中心等页面联系客服按钮</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页搜索框</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline14" type="radio" name="parameter[index_switch_search]" value="0" <?php if(empty($data) || $data['index_switch_search'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline14">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline15" type="radio" name="parameter[index_switch_search]" value="1" <?php if(!empty($data) && $data['index_switch_search'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline15">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row" style="display:none;">
										<label class="col-sm-2 col-form-label">首页页头文字颜色</label>
										<div class="col-sm-10">
											<div class="" style="margin:0px;">
												<div class="layui-input-inline" style="width: 120px;">
													<input type="text" name="parameter[index_top_font_color]" value="<?php echo ($data['index_top_font_color']); ?>" placeholder="请选择颜色" class="form-control" id="test-colorpicker-form-input2">
												</div>
												<div class="layui-inline" style="left: -11px;">
													<div id="minicolors2"></div>
												</div>
											</div>
											<small>默认：#ffffff</small>
										</div>
									</div>

									<div class="form-group row" style="display:none;">
										<label class="col-sm-2 col-form-label">个人中心页头文字颜色</label>
										<div class="col-sm-10">
											<div class="" style="margin:0px;">
												<div class="layui-input-inline" style="width: 120px;">
													<input type="text" name="parameter[user_top_font_color]" value="<?php echo ($data['user_top_font_color']); ?>" placeholder="请选择颜色" class="form-control" id="test-colorpicker-form-input3">
												</div>
												<div class="layui-inline" style="left: -11px;">
													<div id="minicolors3"></div>
												</div>
											</div>
											<small>默认：#ffffff</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页显示“切换”二字</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline16" type="radio" name="parameter[hide_community_change_word]" value="1" <?php if(!empty($data) && $data['hide_community_change_word'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline16">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline17" type="radio" name="parameter[hide_community_change_word]" value="0" <?php if(empty($data) || $data['hide_community_change_word'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline17">显示</label>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">切换小区</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline18" type="radio" name="parameter[hide_community_change_btn]" value="1" <?php if(!empty($data) && $data['hide_community_change_btn'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline18">禁止</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline19" type="radio" name="parameter[hide_community_change_btn]" value="0" <?php if(empty($data) || $data['hide_community_change_btn'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline19">允许</label>
												</div>
											</div>
											<br>
											<small>备注：选择“禁止”，用户无论点击哪个团长分享出来的链接，都是直接进入原绑定小区，无提示 。</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页团长信息开关</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline20" type="radio" name="parameter[hide_index_top_communityinfo]" value="1" <?php if(!empty($data) && $data['hide_index_top_communityinfo'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline20">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline21" type="radio" name="parameter[hide_index_top_communityinfo]" value="0" <?php if(empty($data) || $data['hide_index_top_communityinfo'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline21">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">简洁模式团长与搜索</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline22" type="radio" name="parameter[index_communityinfo_showtype]" value="0" <?php if(empty($data) || $data['index_communityinfo_showtype'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline22">关闭</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline23" type="radio" name="parameter[index_communityinfo_showtype]" value="1" <?php if(!empty($data) && $data['index_communityinfo_showtype'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline23">开启</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">分类栏所有商品项文字</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[index_type_first_name]" class="form-control" value="<?php echo ($data['index_type_first_name']); ?>" />
											<small>默认：“全部”</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">一键复制开关</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline24" type="radio" name="parameter[ishow_index_copy_text]" value="0" <?php if(empty($data) || $data['ishow_index_copy_text'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline24">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline25" type="radio" name="parameter[ishow_index_copy_text]" value="1" <?php if(!empty($data) && $data['ishow_index_copy_text'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline25">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">返回顶部开关</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline26" type="radio" name="parameter[ishow_index_gotop]" value="0" <?php if(empty($data) || $data['ishow_index_gotop'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline26">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline27" type="radio" name="parameter[ishow_index_gotop]" value="1" <?php if(!empty($data) && $data['ishow_index_gotop'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline27">显示</label>
												</div>
											</div>
											<br>
											<small>首页返回顶部浮动按钮，默认：隐藏。</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">专题分享按钮</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline28" type="radio" name="parameter[ishow_special_share_btn]" value="0" <?php if(empty($data) || $data['ishow_special_share_btn'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline28">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline29" type="radio" name="parameter[ishow_special_share_btn]" value="1" <?php if(!empty($data) && $data['ishow_special_share_btn'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline29">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">个人中心退出登录按钮</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline30" type="radio" name="parameter[ishow_user_loginout_btn]" value="0" <?php if(empty($data) || $data['ishow_user_loginout_btn'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline30">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline31" type="radio" name="parameter[ishow_user_loginout_btn]" value="1" <?php if(!empty($data) && $data['ishow_user_loginout_btn'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline31">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">个人中心提货码显示方式</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline32" type="radio" name="parameter[fetch_coder_type]" value="0" <?php if(empty($data) || $data['fetch_coder_type'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline32">底部</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline33" type="radio" name="parameter[fetch_coder_type]" value="1" <?php if(!empty($data) && $data['fetch_coder_type'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline33">弹窗</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline34" type="radio" name="parameter[fetch_coder_type]" value="2" <?php if(!empty($data) && $data['fetch_coder_type'] ==2 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline34">关闭</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">个人中心拼团</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline35" type="radio" name="parameter[show_user_pin]" value="0" <?php if(empty($data) || $data['show_user_pin'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline35">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline36" type="radio" name="parameter[show_user_pin]" value="1" <?php if(!empty($data) && $data['show_user_pin'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline36">显示</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">个人中心自提点</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline37" type="radio" name="parameter[show_user_change_comunity]" value="0" <?php if(empty($data) || $data['show_user_change_comunity'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline37">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline38" type="radio" name="parameter[show_user_change_comunity]" value="1" <?php if(!empty($data) && $data['show_user_change_comunity'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline38">显示</label>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">个人中心团长电话保护</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline42" type="radio" name="parameter[show_user_tuan_mobile]" value="0" <?php if( empty($data) || $data['show_user_tuan_mobile'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline42">开启</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline43" type="radio" name="parameter[show_user_tuan_mobile]" value="1" <?php if( !empty($data) && $data['show_user_tuan_mobile'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline43">关闭</label>
												</div>
												<small>开启保护则隐藏团长手机后四位，默认开启。</small>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页公众号关注组件</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline39" type="radio" name="parameter[show_index_wechat_oa]" value="0" <?php if(empty($data) || $data['show_index_wechat_oa'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline39">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline40" type="radio" name="parameter[show_index_wechat_oa]" value="1" <?php if(!empty($data) && $data['show_index_wechat_oa'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline40">显示</label>
												</div>
											</div>
											<small>1.使用组件前，需前往小程序后台，在“设置”->“关注公众号”中设置要展示的公众号。设置的公众号需与小程序主体一致。</small>
											<br>
											<small>2.只有从“扫小程序码”、“聊天顶部场景”、“其他小程序返回小程序”进入时才会显示。</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页分类列表</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline44" type="radio" name="parameter[ishide_index_goodslist]" value="1" <?php if( !empty($data) || $data['ishide_index_goodslist'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline44">隐藏</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline45" type="radio" name="parameter[ishide_index_goodslist]" value="0" <?php if( !empty($data) && $data['ishide_index_goodslist'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline45">显示</label>
												</div>
												<br />
												<small>首页分类商品列表，开启隐藏则分类商品列表、切换按钮和分类导航一并隐藏，默认显示。</small>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">公告点击弹窗</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline46" type="radio" name="parameter[can_index_notice_alert]" value="1" title="" <?php if( !empty($data) && $data['can_index_notice_alert']==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline46">开启</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline47" type="radio" name="parameter[can_index_notice_alert]" value="0" title="" <?php if( empty($data) || $data['can_index_notice_alert']==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline47">关闭</label>
												</div>
												<br />
												<small>首页公告点击弹窗显示详情，默认关闭。</small>
											</div>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">技术支持</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[technical_support]" class="form-control" value="<?php echo ($data['technical_support']); ?>" />
											<small>可填写 "***版权所有"。与备案号一起显示在网站登录界面下方</small>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">备案号</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[record_number]" class="form-control" value="<?php echo ($data['record_number']); ?>" />
											<small>点击备案号会链接到工信部官网首页（beian.miit.gov.cn）</small>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">充值名称自定义</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[excharge_nav_name]" class="form-control" value="<?php echo ($data['excharge_nav_name']); ?>" />
											<small>充值页面顶部导航名称和个人中心余额“查看”文字</small>
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
<!-- latest jquery-->

<!-- Bootstrap js-->

<!-- Sidebar jquery-->
<script src="/assets/js/sidebar-menu.js"></script>
<script src="/assets/js/config.js"></script>
<!-- Plugins JS start-->
<script src="/assets/js/form-wizard/form-wizard-two.js"></script>
<script src="/assets/js/chat-menu.js"></script>
<script src="/assets/js/form-validation-custom.js"></script>

<script src="/assets/js/datepicker/date-picker/datepicker.js"></script>
<script src="/assets/js/datepicker/date-picker/datepicker.en.js"></script>
<script src="/assets/js/datepicker/date-picker/datepicker.custom.js"></script>


<!-- Plugins JS Ends-->
<!-- Theme js-->
<script src="/assets/js/script.js"></script>
<script src="/assets/js/theme-customizer/customizer1.js"></script>
<script>
	var nav_bg_color = '<?php echo ($data["nav_bg_color"]); ?>';
	$('#minicolors').colpick({
		submit:true,
		color: nav_bg_color,
		onSubmit: function(color,color2){
			$('#test-colorpicker-form-input').val('#'+color2);
			$('#minicolors').find('.colorpicker-trigger-span').css('background','#'+color2);
			$('.colpick_full').hide();
		}
	});

	var skin_color = '<?php echo ($data["skin"]); ?>';
	$('#skincolors').colpick({
		submit:true,
		color: skin_color,
		onSubmit: function(color,color2){
			$('#skin-colorpicker-form-input').val('#'+color2);
			$('#skincolors').find('.colorpicker-trigger-span').css('background','#'+color2);
			$('.colpick_full').hide();
		}
	});

	var minicolors2 = '<?php echo ($data["index_top_font_color"]); ?>';
	$('#minicolors2').colpick({
		submit:true,
		color: minicolors2,
		onSubmit: function(color,color2){
			$('#test-colorpicker-form-input2').val('#'+color2);
			$('#minicolors2').find('.colorpicker-trigger-span').css('background','#'+color2);
			$('.colpick_full').hide();
		}
	});

	var minicolors3 = '<?php echo ($data["user_top_font_color"]); ?>';
	$('#minicolors3').colpick({
		submit:true,
		color: minicolors3,
		onSubmit: function(color,color2){
			$('#test-colorpicker-form-input3').val('#'+color2);
			$('#minicolors3').find('.colorpicker-trigger-span').css('background','#'+color2);
			$('.colpick_full').hide();
		}
	});
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

layui.use(['jquery', 'layer','form','colorpicker'], function(){
  $ = layui.$;
  var form = layui.form;
  var colorpicker = layui.colorpicker;


    //表单赋值
    var nav_bg_color = '<?php echo ($data["nav_bg_color"]); ?>';
	var nav_bg_color = '<?php echo ($data["nav_bg_color"]); ?>';
	//表单赋值
	/*colorpicker.render({
      elem: '#minicolors'
       ,color: nav_bg_color ? nav_bg_color : '#F75451'
      ,done: function(color){
        $('#test-colorpicker-form-input').val(color);
      }
    });*/

	/*var user_top_font_color = '<?php echo ($data["user_top_font_color"]); ?>';
    colorpicker.render({
      elem: '#minicolors3'
      ,color: user_top_font_color ? user_top_font_color : '#FFFFFF'
      ,done: function(color){
        $('#test-colorpicker-form-input3').val(color);
      }
    });

    var index_top_font_color = '<?php echo ($data["index_top_font_color"]); ?>';
    colorpicker.render({
      elem: '#minicolors2'
      ,color: index_top_font_color ? index_top_font_color : '#FFFFFF'
      ,done: function(color){
        $('#test-colorpicker-form-input2').val(color);
      }
    });*/

	/*var skin_color = '<?php echo ($data["skin"]); ?>';
    colorpicker.render({
      elem: '#skincolors'
      ,color: skin_color ? skin_color : '#F75451'
      ,done: function(color){
        $('#skin-colorpicker-form-input').val(color);
      }
    });*/

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