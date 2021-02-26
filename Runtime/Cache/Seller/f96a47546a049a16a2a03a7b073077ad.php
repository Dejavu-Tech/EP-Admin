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
<link href="/assets/css/ep/eaterplanet.css?v=32" rel="stylesheet">

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
							<h6 class="mb-0">当前位置</h6>
							<h2>营业<span>数据</span></h2>
						</div>
						<div class="col-lg-6 breadcrumb-right" style="float: right">
							<ol class="breadcrumb">
								<a href="<?php echo U($cur_controller, array('reports_index' => $_GPC['reports_index'],'reports_index' => $_GPC['reports_index'], 'is_export' => 1 ));?>"><button class="btn btn-info btn-air-info" style="">数据导出</button></a>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid m-t-0" style="margin-top: 0px">
				<ul class="nav nav-pills" id="pills-tab" role="tablist" style="margin-bottom:20px ">
					<li class="nav-item <?php if(empty($tabid) || $tabid=='0'){ ?>active show<?php } ?>"><a class="nav-link" href="<?php echo U($cur_controller, array('reports_index' => 0));?>">本周</a></li>
					<li class="nav-item <?php if($tabid=='1'){ ?>active show<?php } ?>"  ><a class="nav-link" href="<?php echo U($cur_controller, array('reports_index' => 1));?>">上周</a></li>
					<li class="nav-item <?php if($tabid=='2'){ ?>active show<?php } ?>" ><a class="nav-link"  href="<?php echo U($cur_controller, array('reports_index' => 2));?>">本月</a></li>
					<li class="nav-item <?php if($tabid=='3'){ ?>active show<?php } ?>" ><a class="nav-link" href="<?php echo U($cur_controller, array('reports_index' => 3));?>">上月</a></li>
				</ul>
			</div>
			<div class="container-fluid general-widget">

				<div class="row">

					<div class="col-xl-2 col-md-4 box-col-4">
						<div class="card gradient-primary o-hidden">
							<div class="card-body b-r-4" style="padding:20px ">
								<div class="media-body"><span class="m-0 text-white"><h6>订单笔数</h6></span>
									<h2 class="text-center"><?php echo ($zongdanshu); ?></h2>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-md-4 box-col-4">
						<div class="card gradient-secondary o-hidden ">
							<div class="card-body b-r-4" style="padding:20px ">
								<div class="media-body"><span class="m-0 text-white"><h6>取消笔数</h6></span>
									<h2 class="text-center"><?php if( empty($quxiaoshu)){ ?>0<?php }else{ echo ($quxiaoshu); } ?></h2>
								</div>
							</div>
						</div>
					</div>

					<div class="col-xl-2 col-md-4 box-col-4">
						<div class="card gradient-success o-hidden ">
							<div class="card-body b-r-4" style="padding:20px ">
								<?php
 $zongxiadan = sprintf("%.2f",$zongxiadan); ?>
								<div class="media-body"><span class="m-0 text-white"><h6>下单金额</h6></span>
									<h2 class="text-center"><?php echo ($zongxiadan); ?></h2>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-md-4 box-col-4">
						<div class="card gradient-info o-hidden ">
							<div class="card-body b-r-4" style="padding:20px ">
								<div class="media-body"><span class="m-0 text-white"><h6>申请退款笔数</h6></span>
									<h2 class="text-center"><?php if( empty($wait_refund_count)){ ?>0<?php }else{ echo ($wait_refund_count); } ?></h2>
								</div>
							</div>
						</div>
					</div>

					<div class="col-xl-2 col-md-4 box-col-4">
						<div class="card gradient-secondary o-hidden ">
							<div class="card-body b-r-4" style="padding:20px ">
								<?php
 $wait_refund_money = sprintf("%.2f",$wait_refund_money); ?>
								<div class="media-body"><span class="m-0 text-white"><h6>申请退款金额</h6></span>
									<h2 class="text-center"><?php if( empty($wait_refund_money)){ ?>0<?php }else{ echo ($wait_refund_money); } ?></h2>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-md-4 box-col-4">
						<div class="card gradient-warning o-hidden ">
							<div class="card-body b-r-4" style="padding:20px ">
								<div class="media-body"><span class="m-0 text-white"><h6>已退款笔数</h6></span>
									<h2 class="text-center"><?php if( empty($has_refund_count)){ ?>0<?php }else{ echo ($has_refund_count); } ?></h2>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-md-4 box-col-4">
						<div class="card gradient-warning o-hidden ">
							<div class="card-body b-r-4" style="padding:20px ">
								<div class="media-body"><span class="m-0 text-white"><h6>已退款金额</h6></span>
									<h2 class="text-center"><?php if( empty($has_refund_money)){ ?>0<?php }else{ echo ($has_refund_money); } ?></h2>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-md-4 box-col-4">
						<div class="card gradient-success o-hidden ">
							<div class="card-body b-r-4" style="padding:20px ">
								<?php
 $quxiao = sprintf("%.2f",$quxiao); ?>
								<div class="media-body"><span class="m-0 text-white"><h6>取消金额</h6></span>
									<h2 class="text-center"><?php echo ($quxiao); ?></h2>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-md-4 box-col-4">
						<div class="card gradient-primary o-hidden ">
							<div class="card-body b-r-4" style="padding:20px ">
								<?php
 $xaioji = sprintf("%.2f",$xaioji); ?>
								<div class="media-body"><span class="m-0 text-white"><h6>小计金额</h6></span>
									<h2 class="text-center"><?php if( empty($xaioji)){ ?>0<?php }else{ echo ($xaioji); } ?></h2>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<div class="card">
							<div class="card-body">
								<form action="" class="form theme-form layui-form" lay-filter="example" method="post" >
									<div class="dataTables_wrapper no-footer">


										<div class="table-responsive">

											<table class="display dataTable text-center" cellpadding="0" cellspacing="0" border="0" >
												<thead>
												<tr>
													<th>下单日期</th>
													<th>订单数</th>
													<th>下单金额</th>
													<th>等待退款笔数</th>
													<th>等待退款金额</th>
													<th>已退款笔数</th>
													<th>已退款金额</th>
													<th>取消笔数</th>
													<th>取消金额</th>
													<th>小计</th>
												<tr>
												</thead>

												<tbody>
												<?php foreach($list2 as $k => $w){ ?>
												<tr>
													<th><?php echo ($w["date"]); ?></th>

													<th><?php echo ($w["count"]); ?></th>

													<?php
 $order_amount = $w['total']+$w['shipping_fare']-$w['voucher_credit']-$w['fullreduction_money']-$w['score_for_money']-$w['fare_shipping_free']; $order_amount = sprintf("%.2f",$order_amount); ?>
													<th><?php echo ($order_amount); ?></th>

													<th><?php echo ($w["daywait_refund_count"]); ?></th>
													<?php
 $w["daywait_refund_money"] = sprintf("%.2f",$w["daywait_refund_money"]); ?>
													<th><?php echo ($w["daywait_refund_money"]); ?></th>

													<th><?php echo ($w["dayhas_refund_count"]); ?></th>
													<?php
 $w["dayhas_refund_money"] = sprintf("%.2f",$w["dayhas_refund_money"]); ?>
													<th><?php echo ($w["dayhas_refund_money"]); ?></th>

													<th><?php echo ($w["dayqu"]); ?></th>

													<?php
 $w["dayquxiao"] = sprintf("%.2f",$w["dayquxiao"]); ?>

													<th><?php echo ($w["dayquxiao"]); ?></th>

													<?php
 $order_ji = $order_amount - $w["dayquxiao"]; $order_ji = sprintf("%.2f",$order_ji); ?>
													<th><?php echo ($order_ji); ?></th>
												</tr>
												<?php } ?>
												</tbody>
											</table>
										</div>
									</div>



								</form>
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


	$('.deldom').click(function(){
		var s_url = $(this).attr('data-href');
		layer.confirm($(this).attr('data-confirm'), function(index){

					 $.ajax({
						url:s_url,
						type:'post',
						dataType:'json',
						success:function(info){
						$('#ajaxModal').removeClass('in');
						$('.modal-backdrop').removeClass('in');
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
					})
				},function(){
					console.log(232323);
					$('#ajaxModal').removeClass('in');
					$('.modal-backdrop').removeClass('in');
				});
		return	false;
	})

	$('.btn-operation').click(function(){
		var ids_arr = [];
		var obj = $(this);
		var s_toggle = $(this).attr('data-toggle');
		var s_url = $(this).attr('data-href');


		$("input[name=item_checkbox]").each(function() {

			if( $(this).prop('checked') )
			{
				ids_arr.push( $(this).val() );
			}
		})
		if(ids_arr.length < 1)
		{
			layer.msg('请选择要操作的内容');
		}else{
			var can_sub = true;
			if( s_toggle == 'batch-remove' )
			{
				can_sub = false;

				layer.confirm($(obj).attr('data-confirm'), function(index){
					 $.ajax({
						url:s_url,
						type:'post',
						dataType:'json',
						data:{ids:ids_arr},
						success:function(info){

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
					})
				});
			}else{
				$.ajax({
					url:s_url,
					type:'post',
					dataType:'json',
					data:{ids:ids_arr},
					success:function(info){

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
				})
			}
		}
	})

	form.on('switch(restwsitch)', function(data){

	  var s_url = $(this).attr('data-href')

	  var rest = 1;
	  if(data.elem.checked)
	  {
		rest = 1;
	  }else{
		rest = 0;
	  }

	  $.ajax({
			url:s_url,
			type:'post',
			dataType:'json',
			data:{rest:rest},
			success:function(info){

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
		})
	});
	form.on('switch(enablewsitch)', function(data){

	  var s_url = $(this).attr('data-href')

	  var enable = 1;
	  if(data.elem.checked)
	  {
		enable = 1;
	  }else{
		enable = 0;
	  }

	  $.ajax({
			url:s_url,
			type:'post',
			dataType:'json',
			data:{enable:enable},
			success:function(info){

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
		})
	});

	form.on('switch(statewsitch)', function(data){

	  var s_url = $(this).attr('data-href')

	  var state = 1;
	  if(data.elem.checked)
	  {
		state = 1;
	  }else{
		state = 0;
	  }

	  $.ajax({
			url:s_url,
			type:'post',
			dataType:'json',
			data:{state:state},
			success:function(info){

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
		})
	});
	form.on('checkbox(checkboxall)', function(data){

	  if(data.elem.checked)
	  {
		$("input[name=item_checkbox]").each(function() {
			$(this).prop("checked", true);
		});
		$("input[name=checkall]").each(function() {
			$(this).prop("checked", true);
		});

	  }else{
		$("input[name=item_checkbox]").each(function() {
			$(this).prop("checked", false);
		});
		$("input[name=checkall]").each(function() {
			$(this).prop("checked", false);
		});
	  }

	  form.render('checkbox');
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
<script>
var ajax_url = "";
$(function(){

	$("[data-toggle='ajaxModal']").click(function () {
        var s_url = $(this).attr('data-href');
		ajax_url = s_url;
		console.log(23);
       $.ajax({
				url:s_url,
				type:"get",
				success:function(shtml){
					$('#ajaxModal').html(shtml);
					$("#ajaxModal").modal();
				}
		})
    });
	$(document).delegate(".modal-footer .btn-order","click",function(){
		var s_data = $('#ajaxModal form').serialize();
		$.ajax({
			url:ajax_url,
			type:'post',
			dataType:'json',
			data:s_data,
			success:function(info){
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
		})
		return false;
	})


})
</script>
<div id="ajaxModal" class="modal fade" style="display: none;">

</div>

<script>
    //没有选中时间段不能导出
    $(function () {
        $('.btn-submit').click(function () {
            var e = $(this).data('export');
            if(e==1 ){
                if($('#keyword').val() !='' ){
                    $('#export').val(1);
                    $('#search').submit();
                }else if($('#searchtime').val()!=''){
                    $('#export').val(1);
                    $('#search').submit();
                }else{
                   $('#export').val(1);
                    $('#search').submit();
                    return;
                }
            }else{
                $('#export').val(0);
                $('#search').submit();
            }
        })
    })
</script>
</body>