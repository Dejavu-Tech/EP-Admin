<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
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
							<h6 class="mb-0">当前位置</h6><h2>一键设置<span>商品时间</span></h2>
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
								<?php
 if (defined('ROLE') && ROLE == 'agenter' ) { ?>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">一键设置商品售卖时间</label>
										<div class="col-sm-10">
											<div class="form-group m-t-5 m-checkbox-inline mb-0 custom-radio-ml">
												<div class="radio radio-primary"><input type="radio" lay-filter="is_samedefault_now" name="is_samedefault_now" value="0" <?php if( !isset($data['is_samedefault_now']) || $data['is_samedefault_now'] == 0){ ?>checked="true"<?php } ?> title="默认当前" /> </div>
												<div class="radio radio-primary"><input type="radio" lay-filter="is_samedefault_now" name="is_samedefault_now" value="2" <?php if( isset($data['is_samedefault_now']) && $data['is_samedefault_now'] == 2){ ?>checked="true"<?php } ?> title="统一商户售卖时间" /> </div>
											</div>
											<small id="same_form">保持目前售卖时间不变</small>
										</div>
									</div>
									<div class="form-group row" id="samedefault_now" <?php if( !isset($data['is_samedefault_now']) || $data['is_samedefault_now'] == 0 ){ ?> style="display:none;"<?php } ?>>
									<label class="col-sm-2 col-form-label">一键设置售卖时间</label>
									<div class="col-sm-10">
										<?php echo tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d H:i', $data[goods_same_starttime]),'endtime'=>date('Y-m-d H:i', $data[goods_same_endtime])),true);;?>
										<br>
										<small>未上架商品不在一键设置范围，提交后如果商品列表里未更新时间请刷新页面。</small>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">一键设置商品提货时间</label>
									<div class="col-sm-10">
										<div class="form-group m-t-5 m-checkbox-inline mb-0 custom-radio-ml">
											<div class="radio radio-primary"><input type="radio" lay-filter="is_sametihuo_time" name="is_sametihuo_time" value="0" <?php if( !isset($data['is_sametihuo_time']) || $data['is_sametihuo_time'] == 0){ ?>checked="true"<?php } ?> title="默认当前" /> </div>
											<div class="radio radio-primary"><input type="radio" lay-filter="is_sametihuo_time" name="is_sametihuo_time" value="2" <?php if( isset($data['is_sametihuo_time']) && $data['is_sametihuo_time'] == 2){ ?>checked="true"<?php } ?> title="商户售卖时间" /> </div>
										</div>
										<small id="same_tihuo">保持目前提货时间不变</small>
									</div>
								</div>

								<div class="form-group row" id="sametihuo_time" <?php if( !isset($data['is_sametihuo_time']) || $data['is_sametihuo_time'] == 0){ ?>style="display:none;"<?php } ?>>
								<label class="col-sm-2 col-form-label">提货时间</label>
								<div class="col-sm-10 col-xs-12" id="radPickupDateTip">
									<div class="form-group m-t-5 m-checkbox-inline mb-0 custom-radio-ml">
										<div class="radio radio-primary"><input type="radio"  name="pick_up_type" <?php if( !isset($data['pick_up_type']) || $data['pick_up_type'] ==0 ){ ?> checked <?php } ?> value="4" title="当日达" /><span class="fake-radio"></span></div>
										<div class="radio radio-primary"><input type="radio"  name="pick_up_type" <?php if( isset($data['pick_up_type']) && $data['pick_up_type'] ==1 ){ ?> checked <?php } ?> value="1" title="次日达" /><span class="fake-radio"></span></div>
										<div class="radio radio-primary"><input type="radio"  name="pick_up_type" <?php if( isset($data['pick_up_type']) && $data['pick_up_type'] ==2 ){ ?> checked <?php } ?> value="2" title="隔日达" /><span class="fake-radio"></span></div>
										<div class="radio radio-primary"><input type="radio"  name="pick_up_type" <?php if( isset($data['pick_up_type']) && $data['pick_up_type'] ==3 ){ ?> checked <?php } ?> value="3" title="自定义" /><span class="fake-radio"></span></div>
										<input class="form-control " id="txtPickupDateTip" name="pick_up_modify" style="vertical-align: sub; display:inline-block;width: 120px;" type="text" value="<?php echo ($data['pick_up_modify']); ?>">
									</div>
									<div style="clear:both;"></div>
								</div>
						</div>
						<?php }else{ ?>
						<div class="form-group row">
							<label class="col-sm-2 col-form-label">一键设置商品售卖时间</label>
							<div class="col-sm-10">
								<div class="form-group m-t-5 m-checkbox-inline mb-0 custom-radio-ml">
									<div class="radio radio-primary"><input type="radio" lay-filter="is_samedefault_now" name="is_samedefault_now" value="0" <?php if( !isset($data['is_samedefault_now']) || $data['is_samedefault_now'] == 0){ ?>checked="true"<?php } ?> title="默认当前" /> </div>
									<div class="radio radio-primary"><input type="radio" lay-filter="is_samedefault_now" name="is_samedefault_now" value="1" <?php if( isset($data['is_samedefault_now']) && $data['is_samedefault_now'] == 1){ ?>checked="true"<?php } ?> title="统一平台售卖时间" /> </div>
									<div class="radio radio-primary"><input type="radio" lay-filter="is_samedefault_now" name="is_samedefault_now" value="2" <?php if( isset($data['is_samedefault_now']) && $data['is_samedefault_now'] == 2){ ?>checked="true"<?php } ?> title="统一平台及商户售卖时间" /> </div>
								</div>
								<small id="same_form">保持目前售卖时间不变</small>
							</div>
						</div>
						<div class="form-group row" id="samedefault_now" <?php if( !isset($data['is_samedefault_now']) || $data['is_samedefault_now'] == 0 ){ ?> style="display:none;"<?php } ?> >
						<label class="col-sm-2 col-form-label">售卖时间</label>
						<div class="col-sm-10">
							<?php echo tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d H:i', $data[goods_same_starttime]),'endtime'=>date('Y-m-d H:i', $data[goods_same_endtime])),true);;?>
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-2 col-form-label">一键设置商品提货时间</label>
						<div class="col-sm-10">
							<div class="form-group m-t-5 m-checkbox-inline mb-0 custom-radio-ml">
								<div class="radio radio-primary"><input type="radio" lay-filter="is_sametihuo_time" name="is_sametihuo_time" value="0" <?php if( !isset($data['is_sametihuo_time']) || $data['is_sametihuo_time'] == 0){ ?>checked="true"<?php } ?> title="默认当前" /> </div>
								<div class="radio radio-primary"><input type="radio" lay-filter="is_sametihuo_time" name="is_sametihuo_time" value="1" <?php if( isset($data['is_sametihuo_time']) && $data['is_sametihuo_time'] == 1){ ?>checked="true"<?php } ?> title="统一平台提货时间" /> </div>
								<div class="radio radio-primary"><input type="radio" lay-filter="is_sametihuo_time" name="is_sametihuo_time" value="2" <?php if( isset($data['is_sametihuo_time']) && $data['is_sametihuo_time'] == 2){ ?>checked="true"<?php } ?> title="统一平台及商户提货时间" /> </div>
							</div>
							<small id="same_tihuo">保持目前提货时间不变</small>
						</div>
					</div>

					<div class="form-group row" id="sametihuo_time" <?php if( !isset($data['is_sametihuo_time']) || $data['is_sametihuo_time'] == 0){ ?>style="display:none;"<?php } ?> >
					<label class="col-sm-2 col-form-label">提货时间</label>
					<div class="col-sm-10" id="radPickupDateTip">
						<div class="form-group m-t-5 m-checkbox-inline mb-0 custom-radio-ml">
							<div class="radio radio-primary"><input type="radio"  name="pick_up_type" <?php if( !isset($data['pick_up_type']) || $data['pick_up_type'] ==0 ){ ?> checked <?php } ?> value="4" title="当日达" /><span class="fake-radio"></span></div>
							<div class="radio radio-primary"><input type="radio"  name="pick_up_type" <?php if( isset($data['pick_up_type']) && $data['pick_up_type'] ==1 ){ ?> checked <?php } ?> value="1" title="次日达" /><span class="fake-radio"></span></div>
							<div class="radio radio-primary"><input type="radio"  name="pick_up_type" <?php if( isset($data['pick_up_type']) && $data['pick_up_type'] ==2 ){ ?> checked <?php } ?> value="2" title="隔日达" /><span class="fake-radio"></span></div>
							<div class="radio radio-primary"><input type="radio"  name="pick_up_type" <?php if( isset($data['pick_up_type']) && $data['pick_up_type'] ==3 ){ ?> checked <?php } ?> value="3" title="自定义" /><span class="fake-radio"></span></div>
							<input class="form-control" id="txtPickupDateTip" name="pick_up_modify" style="vertical-align: sub;display:inline-block;width: 120px;" type="text" value="<?php echo ($data['pick_up_modify']); ?>">
						</div>
						<div style="clear:both;"></div>
					</div>
				</div>
				<?php } ?>
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
<script src="/layuiadmin/layui/layui2.js"></script>
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

		form.on('radio(is_samedefault_now)', function(data){
		<?php
 if (defined('ROLE') && ROLE == 'agenter' ) { ?>
				if (data.value == 0) {
					$('#samedefault_now').hide();
					$('#same_form').html('保持目前售卖时间不变');
				}
				else if( data.value == 2 )
				{
					$('#samedefault_now').show();
					$('#same_form').html('此操作会变更独立商户商品的售卖时间(不包含拼团)，请谨慎操作。');
				}

			<?php }else{ ?>

				if (data.value == 0) {
					$('#samedefault_now').hide();
					$('#same_form').html('保持目前售卖时间不变');

				}else if( data.value == 1 )
				{
					$('#samedefault_now').show();
					$('#same_form').html('此操作会变更所有平台商品的售卖时间(不包含拼团)，请谨慎操作。');
				}
				else if( data.value == 2 )
				{
					$('#samedefault_now').show();
					$('#same_form').html('此操作会变更平台+独立商户商品的售卖时间(不包含拼团)，请谨慎操作。');
				}

			<?php } ?>

		});

		form.on('radio(is_sametihuo_time)', function(data){

		<?php
 if (defined('ROLE') && ROLE == 'agenter' ) { ?>
				if (data.value == 0) {
					$('#sametihuo_time').hide();
					$('#same_tihuo').html('保持目前提货时间不变');
				}
				else if( data.value == 2 )
				{
					$('#sametihuo_time').show();
					$('#same_tihuo').html('此操作会变更独立商户商品的提货时间(不包含拼团)，请谨慎操作。');
				}
			<?php }else{ ?>
				if (data.value == 0) {

					$('#sametihuo_time').hide();
					$('#same_tihuo').html('保持目前提货时间不变');
				}else if( data.value == 1 )
				{
					$('#sametihuo_time').show();
					$('#same_tihuo').html('此操作会变更所有平台商品的提货时间(不包含拼团)，请谨慎操作。');
				}
				else if( data.value == 2 )
				{
					$('#sametihuo_time').show();
					$('#same_tihuo').html('此操作会变更平台+独立商户商品的提货时间(不包含拼团)，请谨慎操作。');
				}
			<?php } ?>


		});




		$('#radPickupDateTip input[type=radio]').click(function(){
			var s_val = $(this).val();
			if(s_val == 3)
			{
				$('#txtPickupDateTip').css('display','inline-block');
			}else{
				$('#txtPickupDateTip').css('display', 'none');
			}
		})

		$('#chose_link').click(function(){
			cur_open_div = $(this).attr('data-input');
			$.post("<?php echo U('util.selecturl', array('ok' => 1));?>", {}, function(shtml){
				layer.open({
					type: 1,
					area: '930px',
					content: shtml //注意，如果str是object，那么需要字符拼接。
				});
			});
		})

		//监听提交
		form.on('submit(formDemo)', function(data){

			layer.confirm('是否确认统一修改售卖时间和提货时间？', function(index){

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

			});

			return false;
		});
	})

</script>
</body>