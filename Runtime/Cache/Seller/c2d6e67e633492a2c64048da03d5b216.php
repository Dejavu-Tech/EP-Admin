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
   <link rel="stylesheet" type="text/css" href="/assets/css/ep/eaterplanet.css?v=4.0.0">
	<link href="/resource/components/colpick/colpick.css" rel="stylesheet">
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
							<h2>小程序<span>图片设置</span> </h2>
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
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">后台登录背景图片</label>
										<div class="col-sm-10" >
											<?php echo tpl_form_field_image2('parameter[seller_backimage]', $data['seller_backimage'], "Common/image/ep.png");?>
											<small>商家后台登录定制图（尺寸：200*230）。手动FTP替换地址：Common/image/ep-2.png</small>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">后台登录LOGO</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[admin_login_image]', $data['admin_login_image'], "Common/image/ep-2.png");?>
											<small>商家后台登录页面左上角LOGO（尺寸：高50，宽120~500）。</small>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">商品加载中图片</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[loading]', $data['loading']);?>
											<small>未加载图片时显示的背景图（小图模式为1:1图片，大图模式为670*400）</small>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">商品详情价格区域背景图</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[goods_details_price_bg]', $data['goods_details_price_bg'], "/assets/ep/placeholder/shareBottomBg.png");?>
											<small>未设置将使用默认，尺寸：710*100。</small>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">首页分享二维码背景图片</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[index_share_qrcode_bg]', $data['index_share_qrcode_bg'], "/assets/ep/images/index_share_bg.jpg");?>
											<small>不传则使用系统默认<b style="color:red">（请传jpg类型图片）</b>，尺寸：750*1334</small>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">二维码颜色</label>
										<div class="col-sm-6">
											<div class="col-sm-6" id="minicolors" style="display: flex;padding-left: 0px">
												<input type="text" name="parameter[qrcode_rgb]" value="<?php echo ($data['qrcode_rgb']); ?>" class="form-control" id="test-colorpicker-form-input1">
												<div class="form-control colorpicker"><span><span class="colorpicker-trigger-span" lay-type="" style="background: <?php echo ($data['qrcode_rgb']); ?>"></span></span></div>
											</div>
											<small>自定义二维码颜色，请输入HEX（十六进制颜色）码，不设置则默认<b style="color:red">红色</b></small>
										</div>
									</div>
									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">海报头像圆角填充颜色</label>
										<div class="col-sm-6">
											<div class="col-sm-6" id="avatar_rgb" style="display: flex;padding-left: 0px">
												<input type="text" name="parameter[avatar_rgb]" value="<?php echo ($data['avatar_rgb']); ?>" class="form-control" id="test-colorpicker-form-input2">
												<div class="form-control colorpicker"><span><span class="colorpicker-trigger-span" lay-type="" style="background: <?php echo ($data['avatar_rgb']); ?>"></span></span></div>
											</div>
											<small>自定义头像圆角填充颜色，请输入HEX（十六进制颜色）码，不设置则默认<b style="color:yellow">黄色</b></small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页二维码自定义时间</label>
										<div class="col-sm-10">
											<input type="text" name="parameter[modify_index_share_time]" class="form-control" value="<?php echo ($data['modify_index_share_time']); ?>" />
											<small>例如：08:00，用于首页海报显示的固定时分，日期随着每天改变</small>
										</div>
									</div>

									<div class="form-group row" style="">
										<label class="col-sm-2 col-form-label">首页底部图片</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[index_bottom_image]', $data['index_bottom_image'], "/assets/ep/placeholder/icon-index-slogan.png");?>
											<small>原尺寸：250*56</small>
										</div>
									</div>
									<div class="form-group row" style="">
										<label class="col-sm-2 col-form-label">首页商品列表顶部图</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[index_list_top_image]', $data['index_list_top_image'], "/assets/ep/placeholder/past-title.png");?>
											<small>原尺寸：332*88</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label" >是否显示顶部图</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline3" type='radio' name='parameter[index_list_top_image_on]' title="显示" value='0' <?php if(!empty($data) && $data['index_list_top_image_on'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline3">显示</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline4" type='radio' name='parameter[index_list_top_image_on]' title="隐藏" value='1' <?php if(empty($data) || $data['index_list_top_image_on'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline4">隐藏</label>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group row" style="">
										<label class="col-sm-2 col-form-label">商品详情页声明顶上图</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[goods_details_middle_image]', $data['goods_details_middle_image'], "/assets/ep/placeholder/past-title.png");?>
											<small>原尺寸：184*48</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">首页引导加入我的小程序</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[index_lead_image]', $data['index_lead_image']);?>
											<small>原尺寸：750*1208</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">是否显示引导</label>
										<div class="col-sm-10">
											<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
												<div class="radio radio-primary">
													<input id="radioinline1" type="radio" name='parameter[is_show_index_lead_image]' value='0' <?php if(!empty($data) && $data['is_show_index_lead_image'] ==0 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline1">否</label>
												</div>
												<div class="radio radio-primary">
													<input id="radioinline2" type="radio" name='parameter[is_show_index_lead_image]' value='1' <?php if(empty($data) || $data['is_show_index_lead_image'] ==1 ){ ?>checked <?php } ?> />
													<label class="mb-0" for="radioinline2">是</label>
												</div>
											</div>
										</div>
									</div>

									<div class="form-group row" style="display: none">
										<label class="col-sm-2 col-form-label">页头公共背景图</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[common_header_backgroundimage]', $data['common_header_backgroundimage'], "/assets/ep/placeholder/TOP_background@2x.png");?>
											<small>包括订单详情页头和社区选择页头，尺寸：750*340</small>
										</div>
									</div>
									<div class="form-group row" style="display: none">
										<label class="col-sm-2 col-form-label">首页页头背景图</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[index_header_backgroundimage]', $data['index_header_backgroundimage'], "/assets/ep/placeholder/TOP_background@2x.png");?>
											<small>尺寸：750*340</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">广告页背景图</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[user_header_backgroundimage]', $data['user_header_backgroundimage'], "/assets/ep/placeholder/TOP_background@2x.png");?>
											<small>建议尺寸：750*1500</small>
										</div>
									</div>

									<div class="form-group row" style="display: none">
										<label class="col-sm-2 col-form-label">授权登录背景</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[auth_bg_image]', $data['auth_bg_image']);?>
											<small>建议尺寸：750*1500</small>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-2 col-form-label">授权登录弹窗背景</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[newauth_bg_image]', $data['newauth_bg_image'], "/assets/ep/placeholder/auth-bg.png");?>
											<small>建议尺寸：宽度520，高度480~660之间</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">授权弹窗取消按钮</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[newauth_cancel_image]', $data['newauth_cancel_image']);?>
											<small>建议尺寸：180*70</small>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">授权弹窗确认按钮</label>
										<div class="col-sm-10">
											<?php echo tpl_form_field_image2('parameter[newauth_confirm_image]', $data['newauth_confirm_image']);?>
											<small>建议尺寸：180*70</small>
										</div>
									</div>

									<div class="form-group row" >
										<label class="col-sm-2 col-form-label">更新会员核销二维码</label>
										<div class="col-sm-10" style="padding-top:7px;">
											<a id="clear_qrcode" href="javascript:;"  data-href="<?php echo U('config/clearqrcode');?>"  class="text-primary">立即更新</a>
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
	layui.config({
		base: '/layuiadmin/' //静态资源所在路径
	}).extend({
		index: 'lib/index' //主入口模块
	}).use('index');

	var minicolors = '<?php echo ($data["qrcode_rgb"]); ?>';
	$('#minicolors').colpick({
		submit:true,
		color: minicolors,
		onSubmit: function(color,color2){
			$('#test-colorpicker-form-input1').val('#'+color2);
			$('#minicolors').find('.colorpicker-trigger-span').css('background','#'+color2);
			$('.colpick_full').hide();
		}
	});

	var avatar_rgb = '<?php echo ($data["avatar_rgb"]); ?>';
	$('#avatar_rgb').colpick({
		submit:true,
		color: avatar_rgb,
		onSubmit: function(color,color2){
			$('#test-colorpicker-form-input2').val('#'+color2);
			$('#avatar_rgb').find('.colorpicker-trigger-span').css('background','#'+color2);
			$('.colpick_full').hide();
		}
	});
</script>

<script>
//由于模块都一次性加载，因此不用执行 layui.use() 来加载对应模块，直接使用即可：
var layer = layui.layer;
var $;

layui.use(['jquery', 'layer','form','colorpicker'], function(){
  $ = layui.$;
  var form = layui.form;
  var colorpicker = layui.colorpicker;


	$('#clear_qrcode').click(function(){
		var s_url = $(this).attr('data-href');

		console.log(s_url);

		layer.confirm('确认更新会员核销二维码吗？', function(index){
		  //do something
		  $.ajax({
			url : s_url,
			dataType:'json',
			success:function(ret){
				layer.msg('操作成功',{time: 1000,
					end:function(){
					}
				});
			}
		  })
		  layer.close(index);
		});

	});
   //表单赋值
/*colorpicker.render({
  elem: '#minicolors'
  ,color: '<?php echo ($data['qrcode_rgb']); ?>'
  ,done: function(color){
    $('#test-colorpicker-form-input1').val(color);
  }
});

colorpicker.render({
  elem: '#avatar_rgb'
  ,color: '<?php echo ($data['avatar_rgb']); ?>'
  ,done: function(color){
    $('#test-colorpicker-form-input2').val(color);
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