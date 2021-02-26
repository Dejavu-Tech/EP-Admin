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
	<style>
		.img-40{width:40px;height:40px;}
	</style>
</head>
<body class="custom-scrollbar" >

<table id="demo" lay-filter="test"></table>


<div class="page-wrapper custom-scrollbar">
	<div class="page-body-wrapper">
		<div class="page-body" style="margin: 0;min-height: calc(100vh - 55px);">
			<div class="container-fluid">
				<div class="page-header">
					<div class="row">
						<div class="col-lg-6 main-header">
							<h6 class="mb-0">当前位置 </h6>
							<h2>评价<span>有礼</span></h2>
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
										<label class="col-sm-2 col-form-label">评价有礼</label>
										<div class="col-sm-10">
											<div class="form-group m-t-5 m-checkbox-inline mb-0 custom-radio-ml">
												<div class="radio radio-primary"><input type='radio' name='data[open_comment_gift]' value='1' <?php if( !empty($data) && $data['open_comment_gift'] == '1'){ ?>checked<?php } ?> title="开启" /> </div>
												<div class="radio radio-primary"><input type='radio' name='data[open_comment_gift]' value='0' <?php if( empty($data['open_comment_gift']) || $data['open_comment_gift'] == 0 ){ ?>checked<?php } ?> title="关闭"  /> </div>
											</div>
											<small>活动开启后，“评价审核”开启的状态下，后台评价审核通过后赠送用户积分，“评价审核”关闭的状态下，用户评价成功直接赠送积分</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">评价奖励</label>
										<div class="col-sm-6">
											<div class="input-group pill-input-group">
												<input type="text" name="data[comment_gift_score]" class="form-control" value="<?php echo $data['comment_gift_score']; ?>">
												<div class="input-group-append"><span class="input-group-text">积分</span></div>
											</div>
											<small>订单中每种商品评价后赠送积分（订单结算后，具体结算时间参考订单设置中的售后期下方的小字）</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">总额上限</label>
										<div class="col-sm-10">
											<div style="display: flex">
											<select name='data[comment_gift_time]'  class="col-sm-1 select" id="comment_gift_time">
												<option value='1' <?php if(!isset($data[comment_gift_time]) && $data[comment_gift_time]=='1'){ ?>selected<?php } ?>>每日</option>
												<option value='2' <?php if(isset($data[comment_gift_time]) && $data[comment_gift_time]=='2'){ ?>selected <?php } ?>>每周</option>
												<option value='3' <?php if(isset($data[comment_gift_time]) && $data[comment_gift_time]=='3'){ ?>selected <?php } ?>>每月</option>
											</select>

											<div class="input-group pill-input-group col-sm-6" style="">
												<input type="text" name="data[comment_gift_max_score]" class="form-control" value="<?php echo $data['comment_gift_max_score']; ?>">
												<div class="input-group-append"><span class="input-group-text">积分</span></div>
											</div>
											</div>
											<small>每个用户评价成功以后每日，每周，每月奖励积分领取总额上限（0表示为不限领取数量）</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">活动说明</label>
										<div class="col-sm-10">
											<?php echo tpl_ueditor('data[comment_gift_publish]',$data['comment_gift_publish'],array('height'=>'300'));?>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<input type="submit" value="提交" lay-submit lay-filter="formDemo" class="btn btn-pill btn-primary"  />
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




<script src="/layuiadmin/layui/layui2.js"></script>
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