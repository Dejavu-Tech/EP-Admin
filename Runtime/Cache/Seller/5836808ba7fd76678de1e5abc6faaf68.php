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

      <script type="text/javascript" src="/assets/js/dist/area/cascade.js"></script>
<script src="https://map.qq.com/api/js?v=2.exp&key=YFJBZ-WBMK4-INNUO-XCKSG-QSSXZ-VFBE2" type="text/javascript" charset="utf-8"></script>

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
							<h2>模板消息<span>设置</span></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
				<ul class="nav nav-pills m-b-20" id="v-pills-tab" role="tablist" aria-orientation="vertical">
					<li class="nav-item"  ><a class="nav-link active"  id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">公众号模板消息</a></li>
					<li class="nav-item"  ><a class="nav-link"  id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">平台订单通知</a></li>
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
												<label class="col-sm-2 col-form-label">公众号APPID</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_appid]" class="form-control" value="<?php echo ($data['weixin_appid']); ?>" />
													<small>登录微信公众号平台，在管理中心->应用详情中查看</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">订单支付成功通知</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_template_pay_order]" class="form-control" value="<?php echo ($data['weixin_template_pay_order']); ?>" />
													<small>用户订单支付成功，消息通知用户</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">订单发货通知</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_template_send_order]" class="form-control" value="<?php echo ($data['weixin_template_send_order']); ?>" />
													<small>商品订单发货成功，消息通知用户</small>
												</div>
											</div>
											<div class="form-group row" >
												<label class="col-sm-2 col-form-label">核销成功通知</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_template_hexiao_success]" class="form-control" value="<?php echo ($data['weixin_template_hexiao_success']); ?>" />
													<small>商品订单核销成功，消息通知用户</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">团长申请成功发送通知</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_template_apply_community]" class="form-control" value="<?php echo ($data['weixin_template_apply_community']); ?>" />
													<small>用户申请团长，平台通过审核以后，消息通知用户团长申请成功</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">售后订单申请通知(平台)</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_template_apply_refund]" class="form-control" value="<?php echo ($data['weixin_template_apply_refund']); ?>" />
													<small>消息通知平台管理人员，有“售后订单"需要处理</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">取消订单通知(平台)</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_template_cancle_order]" class="form-control" value="<?php echo ($data['weixin_template_cancle_order']); ?>" />
													<small>消息通知平台管理人员，有“取消订单需要处理</small>
												</div>
											</div>

											<div class="form-group row">
												<label class="col-sm-2 col-form-label">会员下单成功提醒团长</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_template_order_buy]" class="form-control" value="<?php echo ($data['weixin_template_order_buy']); ?>" />
													<small>用户下单成功以后，消息通知团长</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">同城配送骑手接单通知</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_template_order_riders_receive]" class="form-control" value="<?php echo ($data['weixin_template_order_riders_receive']); ?>" />
													<small>同城配送订单，消息通知骑手进行接单</small>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">团长提现到账提醒</label>
												<div class="col-sm-10">
													<input type="text" name="parameter[weixin_template_apply_tixian]" class="form-control" value="<?php echo ($data['weixin_template_apply_tixian']); ?>" />
													<small>团长申请提现，消息通知团长提现申请成功</small>
												</div>
											</div>
											<div class="alert alert-primary" role="alert">
												<p>注意，公众号需要是小程序的关联主体。(未填写appid 即不发送模板消息)<a href="https://wxapp.ch871.com/公众号模板消息.docx" class="txt-warning">点击下载设置教程</a></p>
											</div>
										</div>
										<div class="card-footer">
											<input type="submit" value="提交" lay-submit lay-filter="formDemo" class="btn btn-pill btn-primary"/>
										</div>
									</form>
								</div>
								<form style="display: none" action="<?php echo U('weprogram/templateconfig_fenxi', array('type' => 3));?>" method="post" class="form theme-form layui-form" lay-filter="component-layui-form-item" enctype="multipart/form-data" >
									<div class="card-body">
										<div class="form-group row">
											<label class="col-sm-2 col-form-label">示例：</label>
											<div class="col-sm-10">
												<div class="alert alert-primary" role="alert">
													<p>
														{{first.DATA}}<br/>
														产品名称：{{hotelName.DATA}}<br/>
														团购券号:{{voucher_number.DATA}}<br/>
														{{remark.DATA}}<br/>
														小程序后台——模板消息——我的模板——某模板详情——详细内容（复制粘粘到此处）</p>
												</div>
											</div>
										</div>
										<div class="form-group row">
											<label class="col-sm-2 col-form-label">模板详细内容</label>
											<div class="col-sm-10">
												<textarea  name="subtitle" id="subtitle" rows="8"  class="form-control"  ></textarea>
											</div>
										</div>
										<div id="analy_div"></div>
										<div class="form-group row">
											<label class="col-sm-2 col-form-label">发送会员类型</label>
											<div class="col-sm-10">
												<div class="form-group m-t-5 m-checkbox-inline mb-0 custom-radio-ml">
													<div class="radio radio-primary">
														<input type='radio' name='all_msg_send_type' lay-filter="all_msg_send_type"  title="指定会员" value=1 checked />
													</div>
													<div class="radio radio-primary">
														<input type='radio' name='all_msg_send_type' lay-filter="all_msg_send_type" title="某个会员组" value=2  />
													</div>
													<div class="radio radio-primary">
														<input type='radio' name='all_msg_send_type' lay-filter="all_msg_send_type"  title="全部会员" value=3  />
													</div>
												</div>
											</div>
										</div>
										<div class="form-group row" id="type_1">
											<label class="col-sm-2 col-form-label">关联会员</label>
											<div class="col-sm-10">
												<div class="input-group pill-input-group" style="margin: 0;">
													<input type="text" disabled value="" class="form-control valid" name="" placeholder="" id="agent_id">
													<span class="input-group-append">
														<span data-input="#agent_id" id="col-sm-10"  class="btn btn-pill btn-primary">选择会员</span>
													</span>
												</div>
											</div>
										</div>
										<div class="form-group row" id="type_2" style="display:none;">
											<label class="col-sm-2 col-form-label must">会员组</label>
											<div class="col-sm-10">
												<select name="member_group_id">
													<?php foreach($member_group_list as $val){ ?>
													<option value="<?php echo ($val['id']); ?>"><?php echo ($val['groupname']); ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="form-group row">
											<label class="col-sm-2 col-form-label">模板ID</label>
											<div class="col-sm-10">
												<input type="text" name="all_send_template_id" class="form-control" value="" />
												<small>开发者调用模板消息接口时需提供模板ID</small>
											</div>

										</div>
										<div class="form-group row">
											<label class="col-sm-2 col-form-label">点击链接</label>
											<div class="col-sm-10">
												<div class="input-group pill-input-group">
													<input type="text" value="" class="form-control valid" name="link" placeholder="" id="advlink">
													<span class="input-group-append">
														<span data-input="#advlink" id="chose_link"  class="btn btn-pill btn-primary">选择链接</span>
													</span>
												</div>
											</div>
										</div>
									</div>
									<div class="card-footer">
										<input type="submit" value="提交" lay-submit lay-filter="formDemo" class="btn btn-pill btn-primary"/>
									</div>
								</form>
								<div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
									<form action="" method="post" class="form theme-form layui-form" lay-filter="component-layui-form-item" enctype="multipart/form-data" >
										<div class="card-body">
											<div class="form-group row" id="user_form_item">
												<label class="col-sm-2 col-form-label">关联用户</label>
												<div class="col-sm-10">
													<div class="input-group pill-input-group" style="margin: 0;">
														<input type="text" disabled value="" class="form-control valid" name="" placeholder="" id="agent_id">
														<div class="input-group-append">
															<span data-input="#agent_id" id="chose_agent_id"  class="btn btn-pill btn-primary">选择用户</span>
														</div>
													</div>


													<?php if(!empty($user_list)){ ?>
													<?php foreach( $user_list as $a ){ ?>
													<div class="input-group mult_choose_member_id" data-member-id="<?php echo ($a['member_id']); ?>" style="border-radius: 0;float: left;margin: 10px;margin-left:0px;width: 22%;">
														<div class="layadmin-text-center choose_user">
															<img class="img-responsive img-thumbnail" src="<?php echo ($a['avatar']); ?>">
															<div class="layadmin-maillist-img" style=""><?php echo ($a['nickname']); ?></div>
															<button type="button" class="btn btn-primary" onclick="cancle_bind(this)">
																<i class="mdi mdi-delete-forever-outline"></i>
															</button>
														</div>
													</div>
													<?php }} ?>
													<small>订单信息提示，平台用户下单后通知关联会员进行接单处理</small>
												</div>

											</div>
										</div>
										<div class="card-footer">
											<?php if( $_GPC['type']!='0' && $_GPC['type']!='1' ){ ?>
											<input type="hidden" name="limit_user_list" value="" id="limit_user_list" />
											<?php } ?>
											<input type="submit" value="提交" lay-submit lay-filter="formDemo" class="btn btn-pill btn-primary"/>
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

var cur_open_div;

var can_sub = true;
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

	form.on('radio(all_msg_send_type)', function(data){
            if (data.value == 1) {

				$('#type_1').show();

            } else if( data.value == 2 )
			{
				$('#type_1').hide();
				$('#type_2').show();
			}
			else if( data.value == 3 )
			{
				$('#type_1').hide();
				$('#type_2').hide();
			}

        });



	//subtitle
	$(document).on("input propertychange","#subtitle",function(){

		//("\r|\n|\\s", "");
		var s_content = $('#subtitle').val();
		s_content.replace(/\r|\n|\\s/g,"");

		var regex3 = /\{{(.+?)}\}/g; // {}

		var new_arr = s_content.match(regex3);

		var s_html = "";

		for( var i in  new_arr )
		{
			s_html+='	<div class="form-group row">';
			s_html+='		<label class="col-sm-2 col-form-label">'+new_arr[i]+'内容</label>';
			s_html+='		<div class="col-sm-10">';
			s_html+='			<input type="text" name="datas['+new_arr[i]+']" class="form-control" lay-required="true" value="" />';
			s_html+='		</div>';
			s_html+='	</div>';

		}

		$('#analy_div').html(s_html);

    });


	$('#chose_member_id').click(function(){
		cur_open_div = $(this).attr('data-input');
		$.post("<?php echo U('user/zhenquery', array('ok' => 1));?>", {}, function(shtml){
		 layer.open({
			type: 1,
			area: '700px',
			content: shtml //注意，如果str是object，那么需要字符拼接。
		  });
		});
	})
		$('#chose_agent_id').click(function(){
		cur_open_div = $(this).attr('data-input');
		$.post("<?php echo U('user/zhenquery_many', array('template' => 'mult'));?>", {}, function(shtml){
		 layer.open({
			type: 1,
			area: '700px',
			content: shtml //注意，如果str是object，那么需要字符拼接。
		  });
		});
	})

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


  form.on('submit(auto_get)', function(data){
	var loadingIndex = layer.load(); // 加载中动画遮罩层（1）

	$.ajax({
		url: "<?php echo U('weprogram/autotemplateconfig',array('ok'=>'1'));?>",
		type: 'get',
		dataType:'json',
		success: function (info) {
		   layer.close(loadingIndex); // 提交成功失败都需要关闭
			if(info.status == 0)
			{
				layer.msg('请选择会员',{time: 1000});

			}else if(info.status == 1){

				layer.msg('操作成功',{time: 1000,
					end:function(){
						var backurl = "<?php echo U('weprogram/templateconfig');?>";
						location.href = backurl;
						// location.href = info.result.url;
					}
				});

				can_sub = true;
			}
		}
	});
	return false;

  })
  //监听提交
  form.on('submit(formDemo)', function(data){

	var gd_ar = [];
	var gd_str = '';
	$('.mult_choose_member_id').each(function(){
		gd_ar.push( $(this).attr('data-member-id') );
	})
	gd_str = gd_ar.join(',');

	data.field.limit_user_list = gd_str;

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


function cancle_bind(obj,sdiv)
{
	$('#'+sdiv).val('');
	$(obj).parent().parent().remove();
}


</script>
</body>