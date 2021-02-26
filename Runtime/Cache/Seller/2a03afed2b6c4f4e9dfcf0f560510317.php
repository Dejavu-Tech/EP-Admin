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
<body class="custom-scrollbar">
<div class="page-wrapper custom-scrollbar">
	<div class="page-body-wrapper">
		<div class="page-body" style="margin: 0;min-height: calc(100vh - 55px);">
			<div class="container-fluid">
				<div class="page-header">
					<div class="row">
						<div class="col-lg-6 main-header">
							<h6 class="mb-0">当前位置</h6>
							<h2>附件<span>设置</span></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<div class="card">
							<form action="" method="post" class="form theme-form layui-form" lay-filter="component-form-group row" enctype="multipart/form-data" >
								<div class="card-header">
									<h5>远程附件</h5>
								</div>
								<div class="card-body">
									<div class="form-group row">
										<div class="col-sm-2 attclass">
											<div class="radio radio-primary">
												<input id="radioinline1" type='radio' name='parameter[attachment_type]' value='0' <?php if( empty($data) || $data['attachment_type'] ==0){ ?>checked <?php } ?>/>
												<label class="mb-0" for="radioinline1">关闭</label>
											</div>
											<div class="radio radio-primary">
												<input id="radioinline2" type='radio' name='parameter[attachment_type]' value='1' <?php if(!empty($data) && $data['attachment_type'] ==1){ ?>checked <?php } ?>/>
												<label class="mb-0" for="radioinline2">七牛云存储</label>
											</div>
											<div class="radio radio-primary">
												<input id="radioinline3" type='radio' name='parameter[attachment_type]' value='2' <?php if(!empty($data) && $data['attachment_type'] ==2){ ?>checked <?php } ?>/>
												<label class="mb-0" for="radioinline3">阿里云OSS</label>
											</div>
											<div class="radio radio-primary">
												<input id="radioinline4" type='radio' name='parameter[attachment_type]' value='3' <?php if(!empty($data) && $data['attachment_type'] ==3){ ?>checked <?php } ?>/>
												<label class="mb-0" for="radioinline4">腾讯云</label>
											</div>
										</div>
										<div class="col-sm-10">
											<div class="row qiniu_row" style="<?php if(!empty($data) && $data['attachment_type'] ==1){ ?> <?php }else{ ?> display:none;<?php } ?>">
												<label class="col-sm-12 col-form-label">启用七牛云存储后，请把/Uploads目录（包括此目录）下的子文件及子目录上传至：
													<a href="https://portal.qiniu.com/signin" target="_blank">七牛云存储</a></label>
												<div class="col-sm-12 qiniu_class">
													<div class="form-group row qiniu_class">
														<label class="col-sm-2 col-form-label">Access Key</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[qiniu_accesskey]" class="form-control" value="<?php echo ($data['qiniu_accesskey']); ?>" />
															<small>用于签名的公钥</small>
														</div>
													</div>
													<div class="form-group row qiniu_class">
														<label class="col-sm-2 col-form-label">Secret Key</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[qiniu_secretkey]" class="form-control" value="<?php echo ($data['qiniu_secretkey']); ?>" />
															<small>用于签名的私钥</small>
														</div>
													</div>
													<div class="form-group row qiniu_class">
														<label class="col-sm-2 col-form-label">Bucket</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[qiniu_bucket]" class="form-control" value="<?php echo ($data['qiniu_bucket']); ?>" />
															<small>请保证bucket为可公共读取的</small>
														</div>
													</div>
													<div class="form-group row qiniu_class">
														<label class="col-sm-2 col-form-label">Url</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[qiniu_url]" class="form-control" value="<?php echo ($data['qiniu_url']); ?>" />
															<small>七牛支持用户自定义访问域名。开头必须加http://或https://，结尾必须加‘/’。例：http://abc.com/</small>
														</div>
													</div>
												</div>
											</div>

											<div class="row alioss_row" style="<?php if(!empty($data) && $data['attachment_type'] ==2){ ?> <?php }else{ ?> display:none;<?php } ?>">
												<label class="col-sm-12 col-form-label">启用阿里oss后，请把/Uploads目录（包括此目录）下的子文件及子目录上传至阿里云oss。
													<a href="http://bbs.aliyun.com/read/247023.html?spm=5176.383663.9.29.faitxp" target="_blank">官方推荐工具</a></label>
												<div class="col-sm-12 qiniu_class">
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">Access Key ID</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[alioss_key]" class="form-control" value="<?php echo ($data['alioss_key']); ?>" />
															<small>Access Key ID是您访问阿里云API的ID，具有该账户完全的权限，请您妥善保管。</small>
														</div>
													</div>
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">Access Key Secret</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[alioss_secret]" class="form-control" value="<?php echo ($data['alioss_secret']); ?>" />
															<small>Access Key Secret是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管。(填写完Access Key ID 和 Access Key Secret 后请选择bucket)</small>
														</div>
													</div>
													<div class="form-group row qiniu_class">
														<label class="col-sm-2 col-form-label">Bucket</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[alioss_bucket]" class="form-control" value="<?php echo ($data['alioss_bucket']); ?>" />
															<small>请保证bucket为可公共读取的</small>
														</div>
													</div>
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">内网上传</label>
														<div class="col-sm-10">
															<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
															<div class="radio radio-primary">
																<input id="radioinline5" type='radio' name='parameter[alioss_internal]' value='1' <?php if(!empty($data) && $data['alioss_internal'] ==1){ ?>checked <?php } ?>/>
																<label class="mb-0" for="radioinline5">是</label>
															</div>
															<div class="radio radio-primary">
																<input id="radioinline6" type='radio' name='parameter[alioss_internal]' value='0' <?php if( empty($data) || $data['alioss_internal'] ==0){ ?>checked <?php } ?>/>
																<label class="mb-0" for="radioinline6">否</label>
															</div>
																<br>
															<small>如果此站点使用的是阿里云ecs服务器，并且服务器与此附件的对象存储在同一地区（如：同在华北一区），您可以选择通过内网上传的方式上传附件，以加快上传速度、节省带宽。</small>
															</div>
														</div>
													</div>
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">自定义URL</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[alioss_url]" class="form-control" value="<?php echo ($data['alioss_url']); ?>" />
															<small>阿里云oss支持用户自定义访问域名，如果自定义了URL则用自定义，如果未自定义则用系统生成出来的URL。 开头必须加http://或https://，结尾必须加‘/’。例：http://abc.com/</small>
														</div>
													</div>
												</div>
											</div>
											<div class="row txyun_row" style="<?php if(!empty($data) && $data['attachment_type'] ==3){ ?> <?php }else{ ?> display:none;<?php } ?>">
												<label class="col-sm-12 col-form-label">启用腾讯云存储后，请把/Uploads目录（包括此目录）下的子文件及子目录上传至腾讯云存储。
													<a href="https://console.qcloud.com/cos/bucket" target="_blank">官方推荐工具</a></label>
												<div class="col-sm-12 qiniu_class">
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">APPID</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[tx_appid]" class="form-control" value="<?php echo ($data['tx_appid']); ?>" />
															<small>APPID 是您项目的唯一ID</small>
														</div>
													</div>
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">SecretID</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[tx_secretid]" class="form-control" value="<?php echo ($data['tx_secretid']); ?>" />
															<small>SecretID 是您项目的安全密钥，具有该账户完全的权限，请妥善保管</small>
														</div>
													</div>
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">SecretKEY</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[tx_secretkey]" class="form-control" value="<?php echo ($data['tx_secretkey']); ?>" />
															<small>SecretKEY 是您项目的安全密钥，具有该账户完全的权限，请妥善保管</small>
														</div>
													</div>
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">Bucket</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[tx_bucket]" class="form-control" value="<?php echo ($data['tx_bucket']); ?>" />
															<small>请保证bucket为可公共读取的</small>
														</div>
													</div>

													<?php
 $tx_area_list = array( 'ap-beijing-1' => '北京一区（已售罄）', 'ap-beijing' => '北京', 'ap-shanghai' => '上海（华东）', 'ap-guangzhou' => '广州（华南）', 'ap-chengdu' => '成都（西南）', 'ap-chongqing' => '重庆', 'ap-shenzhen-fsi' => '深圳金融', 'ap-shanghai-fsi' => '上海金融', 'ap-beijing-fsi' => '北京金融', 'ap-hongkong' => '中国香港', 'ap-singapore' => '新加坡', 'ap-mumbai' => '孟买', 'ap-seoul' => '首尔', 'ap-bangkok' => '曼谷', 'ap-tokyo' => '东京', 'na-siliconvalley' => '硅谷', 'na-ashburn' => '弗吉尼亚', 'na-toronto' => '多伦多', 'eu-frankfurt' => '法兰克福', 'eu-moscow' => '莫斯科', ); ?>
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">地区</label>
														<div class="col-sm-10">
															<select class="form-control digits" name="parameter[tx_area]">
																<?php foreach($tx_area_list as $key => $val){ ?>
																<option value="<?php echo ($key); ?>" <?php if( !empty($data['tx_area']) && $data['tx_area'] == $key ){ ?> selected<?php } ?> ><?php echo ($val); ?></option>
																<?php } ?>
															</select>
														</div>
													</div>
													<div class="form-group row">
														<label class="col-sm-2 col-form-label">Url</label>
														<div class="col-sm-10">
															<input type="text" name="parameter[tx_url]" class="form-control" value="<?php echo ($data['tx_url']); ?>" />
															<small>腾讯云支持用户自定义访问域名。开头必须加http://或https://，结尾必须加‘/’。例：http://abc.com/</small>
														</div>
													</div>
												</div>
											</div>
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
    colorpicker.render({
      elem: '#minicolors'
      ,color: '<?php echo ($data['nav_bg_color']); ?>'
      ,done: function(color){
        $('#test-colorpicker-form-input').val(color);
      }
    });

    colorpicker.render({
      elem: '#minicolors2'
      ,color: '<?php echo ($data['index_top_font_color']); ?>'
      ,done: function(color){
        $('#test-colorpicker-form-input2').val(color);
      }
    });

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

<script language="javascript">
	 $('.attclass input[type=radio]').click(function(){
		var s_r = $(this).val();

		if(s_r == 0)
		{
			$('.qiniu_row').hide();
			$('.alioss_row').hide();
			$('.txyun_row').hide();

		}else if(s_r == 1){

			$('.qiniu_row').show();
			$('.alioss_row').hide();
			$('.txyun_row').hide();

		}else if(s_r == 2){
			$('.qiniu_row').hide();
			$('.alioss_row').show();
			$('.txyun_row').hide();
		}else if( s_r == 3 )
		{
			$('.txyun_row').show();
			$('.qiniu_row').hide();
			$('.alioss_row').hide();
		}

	 })
</script>
</body>