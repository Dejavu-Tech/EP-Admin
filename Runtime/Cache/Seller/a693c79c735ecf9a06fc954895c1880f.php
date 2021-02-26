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
							<h6 class="mb-0">当前位置</h6>
							<h2>小程序参数证书<span>设置</span></h2>
							<div class="alert alert-primary" role="alert">
								<p>说明：下列参数和证书与支付、退款、企业付款等接口有关，请严格按照腾讯提示前往微信小程序平台和商户支付平台：<a class="txt-danger" href="https://pay.weixin.qq.com" target="_blank">pay.weixin.qq.com</a>里的账户中心 -> api安全 -> 申请证书 -> 查看证书复制相应证书粘贴于此</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
				<ul class="nav nav-pills m-b-20" id="v-pills-tab" role="tablist" aria-orientation="vertical">
					<li class="nav-item"><a class="nav-link active show" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">微信支付</a></li>
					<li class="nav-item"><a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">微信特约商户支付</a></li>
				</ul>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<div class="card">
							<div class="tab-content" id="v-pills-tabContent">
								<div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
									<form action="" method="post" class="form theme-form layui-form" lay-filter="component-layui-form-item" enctype="multipart/form-data" >
										<div class="card-body">
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">小程序APPID</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_appid]" class="form-control" value="<?php echo ($data['wepro_appid']); ?>" />
													<small>mp.weixin.qq.com 开发-开发配置</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">小程序APPSECRET</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_appsecret]" class="form-control" value="<?php echo ($data['wepro_appsecret']); ?>" />
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">商户ID</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_partnerid]" class="form-control" value="<?php echo ($data['wepro_partnerid']); ?>" />
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">支付秘钥</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_key]" class="form-control" value="<?php echo ($data['wepro_key']); ?>" />
													<small>pay.weixin.qq.com 账户中心-api安全</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">CERT证书文件</label>
												<div class="col-sm-10">
													<textarea style="height:100px;" name="parameter[wechat_apiclient_cert_pem]" class="layui-textarea" rows="5" placeholder="<?php if($data[wechat_apiclient_cert_pem]){ ?>为保证安全性，不显示证书内容。若要修改请直接输入<?php } ?>"></textarea>
													<small>
														<?php if($data[wechat_apiclient_cert_pem]){ ?>
														<button class="btn btn-xs btn-success">已上传</button>
														<?php }else{ ?>
														<button class="btn btn-xs btn-danger">未上传</button>
														<?php } ?>
														从商户平台上下载支付证书，解压并取得其中的<span class="bg-danger">apiclient_cert.pem</span>用记事本打开并复制文件内容，填至此处
													</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">KEY证书秘钥</label>
												<div class="col-sm-10">
													<textarea style="height:100px;" name="parameter[wechat_apiclient_key_pem]" class="layui-textarea" rows="5" placeholder="<?php if($data[wechat_apiclient_key_pem]){ ?>为保证安全性，不显示证书内容。若要修改请直接输入<?php } ?>"></textarea>
													<small>
														<?php if($data[wechat_apiclient_key_pem]){ ?>
														<button class="btn btn-xs btn-success">已上传</button>
														<?php }else{ ?>
														<button class="btn btn-xs btn-danger">未上传</button>
														<?php } ?>从商户平台上下载支付证书，解压并取得其中的<span class="bg-danger">apiclient_key.pem</span>用记事本打开并复制文件内容，填至此处

													</small>
												</div>
											</div>
										</div>
										<div class="card-footer">
											<input type="submit" value="提交" lay-submit lay-filter="formDemo" class="btn btn-pill btn-primary"/>
										</div>
									</form>
								</div>

								<div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
									<form action="" method="post" class="form theme-form layui-form" lay-filter="component-layui-form-item" enctype="multipart/form-data" >
										<div class="card-body">
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">开启微信服务商</br>特约商户支付</label>
												<div class="col-sm-10">
													<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
														<div class="radio radio-primary"><input type="radio" name="parameter[is_open_yinpay]" value="0" title="关闭" <?php if(empty($data) || $data['is_open_yinpay'] ==0){ ?>checked <?php } ?> /></div>
														<div class="radio radio-primary"><input type="radio" name="parameter[is_open_yinpay]" value="3" title="开启" <?php if( !empty($data) && $data['is_open_yinpay'] ==3 ){ ?>checked <?php } ?> /></div>
													</div>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">特约商户商户号</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_sub_mch_id]" class="form-control" value="<?php echo ($data['wepro_sub_mch_id']); ?>" />
												</div>
											</div>

											<div class="form-group row">
												<label class="col-sm-2 col-form-label">小程序APPID</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_appid]" class="form-control" value="<?php echo ($data['wepro_appid']); ?>" />
													<small>mp.weixin.qq.com 开发-开发配置</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">小程序APPSECRET</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_appsecret]" class="form-control" value="<?php echo ($data['wepro_appsecret']); ?>" />
												</div>
											</div>

											<div class="form-group row">
												<label class="col-sm-2 col-form-label">服务商公众号AppID</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_fuwu_appid]" class="form-control" value="<?php echo ($data['wepro_fuwu_appid']); ?>" />
												</div>
											</div>

											<div class="form-group row">
												<label class="col-sm-2 col-form-label">服务商商户号</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_fuwu_partnerid]" class="form-control" value="<?php echo ($data['wepro_fuwu_partnerid']); ?>" />
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">服务商秘钥</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[wepro_key]" class="form-control" value="<?php echo ($data['wepro_key']); ?>" />
													<small>pay.weixin.qq.com 账户中心-api安全</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">特约商户秘钥</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[sup_wepro_key]" class="form-control" value="<?php echo ($data['sup_wepro_key']); ?>" />
													<small>pay.weixin.qq.com 账户中心-api安全，特约商户提现必须填写特约商户秘钥，CERT证书文件，KEY证书秘钥。</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">代理商父级商户</br>秘钥CERT证书文件</label>
												<div class="col-sm-10">
													<textarea style="height:100px;" name="parameter[sup_wechat_apiclient_cert_pem]" class="form-control" rows="5" placeholder="<?php if( $data[sup_wechat_apiclient_cert_pem]){ ?>为保证安全性，不显示证书内容。若要修改请直接输入<?php } ?>"></textarea>
													<div class="layui-form-mid">
														<?php if( $data[sup_wechat_apiclient_cert_pem]){ ?>
														<span class="btn btn-xs btn-success">已上传</span>
														<?php  }else{ ?>
														<span class="btn btn-xs btn-danger">未上传</span>
														<?php } ?>从特约商户商户平台上下载支付证书，解压并取得其中的<span class="bg-danger">apiclient_cert.pem</span>用记事本打开并复制文件内容，填至此处</div>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">代理商父级商户</br>秘钥KEY证书秘钥</label>
												<div class="col-sm-10">
													<textarea style="height:100px;" name="parameter[sup_wechat_apiclient_key_pem]" class="form-control" rows="5" placeholder="<?php if( $data[sup_wechat_apiclient_key_pem]){ ?>为保证安全性，不显示证书内容。若要修改请直接输入<?php } ?>"></textarea>
													<div class="layui-form-mid">
														<?php if( $data[sup_wechat_apiclient_key_pem]){ ?>
														<span class="btn btn-xs btn-success">已上传</span>
														<?php  }else{ ?>
														<span class="btn btn-xs btn-danger">未上传</span>
														<?php } ?>从特约商户商户平台上下载支付证书，解压并取得其中的<span class="bg-danger">apiclient_key.pem</span>用记事本打开并复制文件内容，填至此处
													</div>
												</div>
											</div>
										</div>
										<div class="card-footer">
											<input type="hidden" name="parameter[wepro_partnerid]" class="form-control" value="<?php echo ($data['wepro_partnerid']); ?>" />
											<input type="submit" lay-submit lay-filter="formDemo"  value="提交" class="btn btn-pill btn-primary"  />
										</div>
									</form>
								</div>
							</div>
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
<script src="/assets/js/jquery-3.5.1.min.js"></script>
<!-- Bootstrap js-->
<script src="/assets/js/bootstrap/popper.min.js"></script>
<script src="/assets/js/bootstrap/bootstrap.js"></script>
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
<script type="text/javascript" src="/resource/js/require.js?v=201903260001"></script>
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