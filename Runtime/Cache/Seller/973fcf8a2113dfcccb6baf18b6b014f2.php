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
							<h2>售后订单<span>管理</span></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid" style="margin-top: 0px">
				<ul class="nav nav-pills" id="pills-tab" role="tablist" style="margin-bottom:20px ">
					<li class="nav-item <?php if( (empty($_GPC['state']) || $_GPC['state']=='-1' )&& $_GPC['state'] !='0' ){ ?>active show<?php } ?>"><a class="nav-link"  href="<?php echo U('order/orderaftersales', array('state' => -1));?>">全部申请</a></li>
					<li class="nav-item <?php if($_GPC['state']=='0'){ ?>active show<?php } ?>" ><a class="nav-link"  href="<?php echo U('order/orderaftersales', array('state' => 0));?>">待处理</a></li>
					<li class="nav-item <?php if($_GPC['state']=='6'){ ?>active show<?php } ?>" ><a class="nav-link"  href="<?php echo U('order/orderaftersales', array('state' => 6));?>">退货中</a></li>
					<li class="nav-item <?php if($_GPC['state']=='3'){ ?>active show<?php } ?>" ><a class="nav-link"  href="<?php echo U('order/orderaftersales', array('state' => 3));?>">已完成</a></li>
					<li class="nav-item <?php if($_GPC['state']=='1'){ ?>active show<?php } ?>" ><a class="nav-link"  href="<?php echo U('order/orderaftersales', array('state' => 1));?>">已拒绝</a></li>
				</ul>
			</div>

			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<div class="card">
							<div class="card-body" >
								<form action="" method="get" class="form-horizontal form-search layui-form" role="form">
									<input type="hidden" name="c" value="order" />
									<input type="hidden" name="a" value="orderaftersales" />
									<input type="hidden" name="order_status_id" value="<?php echo ($order_status_id); ?>" />

									<div class="form-row p-b-20">
										<div class="p-r-5" style="">
											<select name='searchtime' lay-ignore  class='form-control'   id="searchtime">
												<option value=''>不按时间</option>
												<option value='create' <?php if($searchtime=='create'){ ?>selected<?php } ?>>下单时间</option>
												<option value='pay' <?php if($searchtime=='pay'){ ?>selected<?php } ?>>付款时间</option>
												<option value='send' <?php if($searchtime=='send'){ ?>selected<?php } ?>>发货时间</option>
												<option value='finish' <?php if($searchtime=='finish'){ ?>selected<?php } ?>>完成时间</option>
											</select>
										</div>
										<div class="p-r-5" style="">
											<?php echo tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d H:i', $starttime),'endtime'=>date('Y-m-d H:i', $endtime)),true);;?>
										</div>
										<div class="p-r-5" style="">
											<select name='delivery' lay-ignore class='form-control'   id="type">
												<option value=''>配送方式</option>
												<option value='pickup' <?php if($delivery=='pickup'){ ?>selected<?php } ?>>自提</option>
												<option value='tuanz_send' <?php if($delivery=='tuanz_send'){ ?>selected<?php } ?>>团长配送</option>
												<option value='express' <?php if($delivery=='express'){ ?>selected<?php } ?>>快递</option>
											</select>
										</div>
										<div class="p-r-5" style="">
											<select name='searchfield' lay-ignore class='form-control'     >
												<option value='ordersn' <?php if($searchfield=='ordersn'){ ?>selected<?php } ?>>订单号</option>
												<option value='member' <?php if($searchfield=='member'){ ?>selected<?php } ?>>会员信息</option>
												<option value='mobile' <?php if($searchfield=='mobile'){ ?>selected<?php } ?>>手机号</option>
												<option value='address' <?php if($searchfield=='address'){ ?>selected<?php } ?>>收件人信息</option>
												<option value='location' <?php if($searchfield=='location'){ ?>selected<?php } ?>>地址信息</option>
												<option value='shipping_no' <?php if($searchfield=='shipping_no'){ ?>selected<?php } ?>>快递单号</option>
												<option value='goodstitle' <?php if($searchfield=='goodstitle'){ ?>selected<?php } ?>>商品名称</option>
												<option value='trans_id' <?php if($searchfield=='trans_id'){ ?>selected<?php } ?>>微信支付单号</option>
												<?php if($is_community != 1){ ?>
												<option value='head_name' <?php if($searchfield=='head_name'){ ?>selected<?php } ?>>团长姓名</option>
												<option value='head_address' <?php if($searchfield=='head_address'){ ?>selected<?php } ?>>小区名称</option>
												<?php } ?>
												<?php
 if (!defined('ROLE') || ROLE != 'agenter' ) { ?>
												<option value='supply_name' <?php if($searchfield=='supply_name'){ ?>selected<?php } ?>>商户名称</option>
												<?php } ?>
												<!--<option value='goodssn' <?php if($searchfield=='goodssn'){ ?>selected<?php } ?>>商品编码</option>-->
											</select>
										</div>
										<div class="p-r-5" style="">
											<input type="text" class="form-control"  name="keyword" value="<?php echo ($_GPC['keyword']); ?>" placeholder="请输入关键词"/>
										</div>
										<input type="hidden" name="export" id="export" value="0">

										<div class="p-r-5" style="">
											<button class="btn btn-primary" data-export="0" type="submit"> 搜索</button>
											<button type="submit" name="export" data-export="1" value="1" class="btn btn-primary">导出</button>
										</div>
									</div>
								</form>
								<form action="" class="form theme-form layui-form" lay-filter="example" method="post" >
									<div class="dataTables_wrapper no-footer">
										<div class="list-div list-tb-div table-responsive">
											<table class="display dataTable text-center" cellpadding="0" cellspacing="0" >
												<thead class="bg-primary">
												<tr>
													<th>订单商品</th>
													<th>单价/数量</th>
													<th>客户</th>
													<th>支付/配送</th>
													<th>小区/团长</th>
													<th>价格</th>
													<th>处理状态</th>
													<th>操作</th>
												</tr>
												</thead>
												<tbody>
												<?php foreach( $list as $item ){ ?>
												<tr class="tr-order-sn">
													<td colspan="8">
														<div style="display: flex">
															<div class="m-r-10">订单号： <?php echo ($item['order_num_alias']); ?></div>
															<div class="m-r-10">下单时间：<?php echo date('Y-m-d',$item['date_added']);?>&nbsp <?php echo date('H:i:s',$item['date_added']);?></div>
															<div class="m-r-10">
																<?php if(in_array($item['ore_state'], array(3))){ ?>&nbsp;<div class="badge badge-success"><?php echo ($order_refund_state[$item['ore_state']]); ?></div><?php } ?>

																<?php if(in_array($item['ore_state'], array(1,4))){ ?><div class="badge badge-info"><?php echo ($order_refund_state[$item['ore_state']]); ?></div><?php } ?>

																<?php if(in_array($item['ore_state'], array(0,5))){ ?><div class="badge badge-secondary"><?php echo ($order_refund_state[$item['ore_state']]); ?></div><?php } ?>
															</div>
														</div>
													</td>
												</tr>
												<tr>
													<td>
														<?php $i =1; foreach($item['goods'] as $k => $g){ ?>
														<div><img width="70" src="<?php echo tomedia($g['goods_images']);?>" alt="" /></div>
														<div>
															<div><?php echo ($g['name']); ?></div>
															<div><?php if(!empty($g['option_sku'])){ echo ($g['option_sku']); } ?></div>
														</div>
														<?php $i++; } ?>
													</td>
													<td class="td-price" style="vertical-align: top;">
														<?php $i =1; foreach($item['goods'] as $k => $g){ ?>
														<div class="tDiv tpinfo <?php if($i == count($item['goods'])){ ?>last<?php } ?>" style="display: flex;align-items: center;">¥<?php echo round($g['total']/$g['quantity'],2); ?>
															x <?php echo ($g['quantity']); ?> </div>
														<?php $i++; } ?>
													</td>

													<td>
														<div><?php echo ($item['shipping_name']); ?></div>
														<div><?php echo ($item['shipping_tel']); ?></div>
														<?php if (defined('ROLE') && ROLE == 'agenter' ){ ?>
														<div>客户名：<?php echo ($item['nickname']); ?></div>
														<?php if( !empty($item['member_content']) ){ ?>
														<div class="txt-danger"><?php echo ($item['member_content']); ?></div>
														<?php } ?>
														<?php }else{ ?>
														<div>客户名：<a class="txt-primary" href="<?php echo U('user/detail',array('id'=>$item['member_id']));?>"><?php echo ($item['nickname']); ?></a></div>
														<?php if( !empty($item['member_content']) ){ ?>
														<div class="text-danger"><?php echo ($item['member_content']); ?></div>
														<?php } ?>
														<?php } ?>
													</td>
													<td>
														<!-- 已支付 -->
														<?php if($item['order_status_id'] != 3 && $item['order_status_id'] != 5){ ?>
														<?php if($item['payment_code']=='yuer'){ ?>
														<div class="badge badge-primary">余额支付</div>
														<?php }else if( $item['payment_code']=='admin' ){ ?>
														<div class="badge badge-info">后台付款</div>
														<?php }else{ ?>
														<div class="badge badge-success">微信支付</div>
														<?php } ?>
														<?php }elseif( $item['order_status_id'] == 3 || $item['order_status_id'] == 5 ){ ?>
														<!-- 未支付 -->
														<?php if($item['paytypevalue']!=3){ ?>
														<div class='badge badge-transparent'>未支付</div>
														<?php }else{ ?>
														<div class="badge badge-info">货到付款</div>
														<?php } ?>
														<?php } ?>
														<br/>
														<?php if($item['delivery']=='pickup'){ ?><div class="text-danger">(自提)</div><?php } ?>
														<?php if($item['delivery']=='express'){ ?><div class="text-danger">(快递)</div><?php } ?>
														<?php if($item['delivery']=='tuanz_send'){ ?><div class="text-danger">(团长配送)</div><?php } ?>
													</td>

													<?php if($is_can_look_headinfo){ ?>
													<td>
														<div><?php echo ($item['head_name']); ?></div>
														<div>电话：<?php echo ($item['head_mobile']); ?></div>
														<div style='cursor: pointer;'>小区：<?php echo ($item['community_name']); ?>   </div>
														<div>(<?php echo ($item['province']); ?> <?php echo ($item['city']); ?>)</div>
													</td>
													<?php }else{ ?>
													<td>
														<?php echo ($item['head_name']); ?>
													</td>
													<?php } ?>
													<td>
														<?php if($item['is_free_shipping_fare'] == 1){ ?>
														<div>运　费：+<?php echo round( $item['fare_shipping_free'],2);?></div>
														<div>满<?php echo ($item['man_e_money']); ?>免运费：-<?php echo round( $item['fare_shipping_free'],2);?></div>
														<?php }else{ ?>
														<div>运　费：+<?php echo round( $item['shipping_fare'],2);?></div>
														<?php } ?>
														<?php if($item['fullreduction_money'] >0){ ?>
														<div>满　减：-<?php echo round( $item['fullreduction_money'],2);?></div>
														<?php } ?>
														<?php if($item['voucher_credit'] >0){ ?>
														<div>优惠券：-<?php echo round( $item['voucher_credit'],2);?></div>
														<?php } ?>
														<div>商品小计：<?php echo round($item['total'],2);?></div>
														<?php $free_tongji = $item['total']+$item['shipping_fare']-$item['voucher_credit']-$item['fullreduction_money']; if($free_tongji < 0){ $free_tongji = 0; } ?>
														<?php
 if($item['type'] == 'integral'){ ?>
														<div>应收总款：<?php echo round($item['total'] ,2);?>积分</div>
														<?php if( !empty( $item['shipping_fare'] ) ){ ?>
														<div>+运费:<?php echo round($item['shipping_fare'] ,2);?></div>
														<?php } ?>
														<?php }else{ ?>
														<div>应收总款：<?php echo round($free_tongji ,2);?></div>
														<?php } ?>
													</td>
													<td>
														<div class='text-<?php echo ($item[order_status_id]); ?>'>
															<?php
 if($item['ore_state'] == 0){ ?>
															待处理
															<?php }else if($item['ore_state'] == 1){ ?>
															已拒绝
															<?php }else if($item['ore_state'] == 3){ ?>
															已完成
															<?php }else if($item['ore_state'] == 5){ ?>
															已拒绝
															<?php }else if($item['ore_state'] == 6){ ?>
															退货中
															<?php } ?>
														</div>
													</td>
													<td>
														<a class="btn btn-primary btn-xs" href="<?php echo U('order/oprefund', array('id' => $item['order_id'],'ref_id' => $item['ref_id']));?>" >
															查看详情
														</a>
														<?php if($item['ore_state'] == 0){ ?>
														<a class="btn btn-primary btn-xs"  data-toggle="ajaxModal" href="javascript:;" data-href="<?php echo U('order/oprefund_doform', array('ref_id' => $item['ref_id']));?>" >
															平台审核
														</a>
														<?php } ?>
														<?php if($item['is_forbidden'] == 1){ ?>
														<p style="color:red;">禁止此用户再次申请</p>
														<?php } ?>
													</td>
												</tr>
												<tr class="border-bottom-primary">
													<td colspan="8">
														<div>订单状态：<?php echo ($order_status_arr[$item['order_status_id']]); ?></div>
													</td>
												</tr>
												<?php } ?>
												</tbody>
											</table>
										</div>
										<?php echo ($pager); ?>
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
		var loadingIndex = layer.load();
		$.ajax({
			url:ajax_url,
			type:'post',
			dataType:'json',
			data:s_data,
			success:function(info){
				if(info.status == 0)
				{
					layer.msg(info.result.message,{icon: 1,time: 2000});
					layer.close(loadingIndex);
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