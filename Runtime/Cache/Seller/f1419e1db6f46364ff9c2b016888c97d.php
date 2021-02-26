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
	<link rel="stylesheet" type="text/css" href="../assets/css/datatables.css">
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

<table id="demo" lay-filter="test"></table>

<div class="page-wrapper custom-scrollbar">
	<div class="page-body-wrapper">
		<div class="page-body" style="margin: 0;min-height: calc(100vh - 55px);">
			<div class="container-fluid">
				<div class="page-header">
					<div class="row">
						<div class="col-lg-6 main-header">
							<h6 class="mb-0">当前位置 </h6>
							<h2>团长<span>列表</span></h2>
						</div>
						<div class="col-lg-6 breadcrumb-right" style="float: right">
							<ol class="breadcrumb">
								<a href="<?php echo U('communityhead/addhead', array('ok' => 1));?>" class="btn btn-info btn-air-info"><i class="fa fa-plus"></i> 添加团长</a>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<div class="card">
							<div class="card-body">
								<form action="" method="get" class="form-horizontal form-search layui-form" role="form">
									<input type="hidden" name="c" value="communityhead" />
									<input type="hidden" name="a" value="index" />
									<input type="hidden" name="type" value="<?php echo ($type); ?>" />
									<div class="form-row">
										<div class="p-r-5" style="">
											<select name='comsiss_state' class="form-control">
												<option value=''>状态</option>
												<option value='0' <?php if($comsiss_state=='0'){ ?>selected<?php } ?>>未审核</option>
												<option value='1' <?php if($comsiss_state=='1'){ ?>selected<?php } ?>>已审核</option>
												<option value='2' <?php if($comsiss_state=='2'){ ?>selected<?php } ?>>拒绝通过</option>
											</select>
										</div>
										<div class="p-r-5" style="">
											<input type="text" class="form-control"  name="keyword" value="<?php echo ($_GPC['keyword']); ?>" placeholder="会员昵称/团长姓名/手机号/小区名称"/>

										</div>
										<div class="p-r-5" style="">
											<select name="level_id" class="form-control">
												<option value="">团长等级</option>
												<?php foreach( $community_head_level as $level ){ ?>
												<option value="<?php echo ($level['id']); ?>" <?php if($level_id == $level['id']){ ?>selected<?php } ?>><?php echo ($level['levelname']); ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="p-r-5" style="">
											<select name="group_id" class="form-control">
												<option value="">团长分组</option>
												<?php foreach( $community_head_group as $group ){ ?>
												<option value="<?php echo ($group['id']); ?>" <?php if($group_id == $group['id']){ ?>selected<?php } ?>><?php echo ($group['groupname']); ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="p-r-5" style="">
											<button class="btn btn-primary" type="submit"> 搜索</button>
											<button type="submit" name="export" value="1" class="btn btn-success">导出</button>
										</div>
									</div>
								</form>
								<form action="" class="form theme-form layui-form m-t-20" lay-filter="example" method="post" >
									<div class="dataTables_wrapper no-footer">
										<div class="page-table-header m-b-20 row">
											<div class="checkbox checkbox-primary m-l-20" >
												<input type='checkbox' name="checkall" lay-skin="primary" lay-filter="checkboxall"  style="display: none"/>
											</div>
											<div class="btn-group">
												<button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch' data-href="<?php echo U('communityhead/agent_check',array('state'=>1));?>"  data-confirm='确认要审核通过?'>
													审核通过
												</button>
												<button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch'  data-href="<?php echo U('communityhead/agent_check',array('state'=>2));?>" data-confirm='确认要拒绝通过?'>
													拒绝通过
												</button>
												<button class="btn btn-pill btn-primary btn-sm " type="button" data-toggle="batch-group" data-href="<?php echo U('communityhead/changelevel', array('toggle' => 'group'));?>" > <i class="icow icow-fenzuqunfa"></i>修改分组</button>
												<a class="btn btn-pill btn-primary btn-sm" data-toggle="batch-level" > 修改等级</a>
											</div>
										</div>
										<div class="table-responsive">
											<table class="display dataTable text-center" lay-even lay-skin="line" lay-size="lg">
												<thead>
												<tr>
													<th>选择</th>
													<th>ID</th>
													<th>小区名称</th>
													<th>头像</th>
													<th>团长</th>
													<th>下级数量</th>
													<?php if($open_danhead_model == 1){ ?>
													<th>默认团长</th>
													<?php } ?>
													<th>商品数量</th>
													<th>佣金情况</th>
													<th>团长地址</th>
													<th>审核时间</th>
													<th>是否休息</th>
													<th>是否审核</th>
													<th>是否启用</th>
													<th>操作</th>
												</tr>
												</thead>
												<tbody>
												<?php foreach( $list as $row ){ ?>
												<tr>
													<td>
														<div class="checkbox checkbox-primary m-t-10 " >
															<input type='checkbox' class="checkone" name="item_checkbox" lay-skin="primary" value="<?php echo ($row['id']); ?>"  style="display: none"/>
														</div>
													</td>
													<td>
														<?php echo ($row['id']); ?>
													</td>
													<td>
														<?php echo ($row['community_name']); ?>
														<br/>
														<a href="javascript:void(0)" class="tg_community" community_id="<?php echo ($row['id']); ?>" style="color:#428bca;">推广二维码</a>
													</td>
													<td>
														<img class="img-40" src="<?php echo ($row['avatar']); ?>" style='border-radius:50%;border:1px solid #efefef;' />
													</td>
													<td style="white-space:nowrap">
														姓名：<?php echo ($row['head_name']); ?>
														<br/>
														昵称：<?php echo ($row['username']); ?>
														<br/>当前团员数量： <span class="text-primary"><?php echo ($row['member_count']); ?></span>
														<br/>
														<span class="text-warning">等级：<?php echo ($level_id_to_name[$row['level_id']]); ?></span>
														<br/>
														<?php if(empty($row['groupname'])){ ?><span class="text-warning">默认分组<?php }else{ echo ($row['groupname']); ?></span><?php } ?>
													</td>
													<td>
														<?php if(!empty($row['agent_name'])){ ?>
														<?php echo ($row['agent_name']); ?>
														<?php }else{ ?>
														暂无上级
														<?php } ?>
														<br/>
														直推团长：<?php echo ($row['agent_count']); ?>
														<br/>
														电话：<?php echo ($row['head_mobile']); ?>
													</td>
													<?php if($open_danhead_model == 1){ ?>
													<td>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="defaultsitch" data-href="<?php echo U('communityhead/default_check',array('id'=>$row['id']));?>" <?php if( $row['is_default']==1 ){ ?>checked<?php } ?> lay-skin="switch" >
														</label>
													</td>
													<?php } ?>
													<td>
														<?php echo ($row['goods_count']); ?>
													</td>
													<td style="white-space:nowrap">
														待确认：<span class="text-warning"><?php echo ($row['commission_info']['pre_total_money']); ?></span><br/>
														可提现：<span class="text-warning"><?php echo empty($row['commission_info']['money']) ? 0: $row['commission_info']['money']; ?></span><br/>
														已打款：<span class="text-warning"><?php echo empty($row['commission_info']['getmoney']) ?0:$row['commission_info']['getmoney']; ?></span><br/>
														提现中：<span class="text-warning"><?php echo empty($row['commission_info']['dongmoney']) ? 0:$row['commission_info']['dongmoney']; ?></span><br/>
														总收入：<span class="text-danger"><?php echo empty($row['commission_info']['commission_total']) ? 0:$row['commission_info']['commission_total']; ?></span>
													</td>

													<td style="white-space:normal;">
														<?php echo ($row['province_name']); echo ($row['city_name']); ?><br/><?php echo ($row['area_name']); echo ($row['country_name']); ?><br/><?php echo ($row['address']); ?>
													</td>
													<td><?php echo date("Y-m-d",$row['addtime']);?><br/><?php echo date("H:i:s",$row['addtime']);?>
														<br/>
														<?php if(!empty($row['apptime'])){ ?>
														<?php echo date("Y-m-d",$row['apptime']);?><br/><?php echo date("H:i:s",$row['apptime']);?>
														<?php } ?>
													</td>

													<td>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="restwsitch" data-href="<?php echo U('communityhead/rest_check',array('id'=>$row['id']));?>" <?php if($row['rest']==1){ ?>checked<?php } ?> lay-skin="switch" >
														</label>
													</td>
													<td>
														<!--
														<?php if($row[state] ==2){ ?>
														已拒绝
														<?php }else{ ?>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="statewsitch" data-href="<?php echo U('communityhead/agent_check',array('id'=>$row['id']));?>" <?php if($row['state']==1){ ?>checked<?php }else{ } ?> lay-skin="switch" >
														</label>
														<?php } ?>
														-->
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="statewsitch" data-href="<?php echo U('communityhead/agent_check', array('id'=>$row['id']));?>" <?php if($row['state']==1){ ?>checked<?php } ?> lay-skin="switch" >
														</label>
														<?php if( $row['state'] == 0 ){ ?>
														<!--<a class='btn btn-primary btn-sm'  data-toggle="ajaxModal" href="javascript:;" data-href="<?php echo U('communityhead/agent_check_first', array('id' => $row['id']));?>" >

															<i class="icow icow-yibiaoji" style="color: #999;display: inline-block;vertical-align: middle" title="点击审核，选择供应商类型" ></i>
															通过

														</a><br/>
-->
														<?php }else if( $row['state'] == 1 ){ ?>
														<!--<p style="color: red; display:inline;padding-right: 15px;" >审核已通过</p>-->
														<?php }else if( $row['state'] == 2 ){ ?>
														<p style="color: red;">已拒绝</p>
														<a  data-toggle="ajaxModal" href="javascript:;" data-href="<?php echo U('communityhead/agent_check_first', array('id' => $row['id']));?>" >
															<p style="color: blue;">重新审核</p>
														</a>
														<?php } ?>
													</td>
													<td>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="enablewsitch" data-href="<?php echo U('communityhead/enable_check',array('id'=>$row['id']));?>" <?php if($row['enable']==1){ ?>checked<?php }else{ } ?> lay-skin="switch" >
														</label>
													</td>
													<td style="overflow:visible;text-align: center;">

														<a class="btn btn-primary btn-xs" href="<?php echo U('communityhead/addhead',array('id' => $row['id'] , 'ok' => 1));;?>" title="">编辑</a>

														<?php if( ($row['state'] == 0 || $row['state'] == 2) && $row['head_goods_count'] == 0 && $row['head_order_count'] == 0 ){ ?>
														<a class="btn btn-primary btn-xs deldom" href="javascript:;" data-href="<?php echo U('communityhead/deletehead',array('id' => $row['id']) );?>" data-confirm='确认要删除吗?'>
															删除
														</a>

														<?php } ?>

														<a class="btn btn-primary btn-xs" href="<?php echo U('communityhead/distributionorder',array('headid' => $row['id']));;?>">
															推广订单
														</a>
														<a class="btn btn-primary btn-xs" href="<?php echo U('communityhead/communityorder',array('head_id' => $row['id']));;?>" >
															收益明细
														</a>

														<a class="btn btn-primary btn-xs" href="<?php echo U('communityhead/goodslist',array('head_id' => $row['id'] , 'ok' => 1));;?>"  >
															在售商品
														</a>
														<a class="btn btn-primary btn-xs" href="<?php echo U('communityhead/lookcommunitymember',array('id' => $row['id'] , 'ok' => 1));;?>">
															核销人员
														</a>
													</td>
												</tr>
												<?php } ?>
												</tbody>
											</table>

										</div>
										<div class="page-table-header m-t-20 row">
											<div class="checkbox checkbox-primary m-l-20" >
												<input type='checkbox' name="checkall" lay-skin="primary" lay-filter="checkboxall"  style="display: none"/>
											</div>
											<div class="btn-group">
												<button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch' data-href="<?php echo U('communityhead/agent_check',array('state'=>1));?>"  data-confirm='确认要审核通过?'>
													审核通过
												</button>
												<button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch'  data-href="<?php echo U('communityhead/agent_check',array('state'=>2));?>" data-confirm='确认要拒绝通过?'>
													拒绝通过
												</button>
												<button class="btn btn-pill btn-primary btn-sm " type="button" data-toggle="batch-group" data-href="<?php echo U('communityhead/changelevel', array('toggle' => 'group'));?>" > <i class="icow icow-fenzuqunfa"></i>修改分组</button>
												<a class="btn btn-pill btn-primary btn-sm btn-op" data-toggle="batch-level" > 修改等级</a>
											</div>
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

<div id="ajaxModal" class="modal fade" style="display: none;">

</div>

<script src="/layuiadmin/layui/layui1.js"></script>
<script type="text/javascript" src="/assets/js/jquery-migrate-1.1.1.js"></script>
<script src="/assets/js/jquery-ui.min.js"></script>

<!-- Bootstrap js-->

<!-- Sidebar jquery-->
<script src="/assets/js/sidebar-menu.js"></script>

<!-- feather icon js-->

<!-- Plugins JS start-->
<script src="/assets/js/modal-animated.js"></script>
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

	form.on('switch(defaultsitch)', function(data){

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
			data:{value:rest},
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

	form.on('switch(statewsitchunable)', function(data){

		var s_url = $(this).attr('data-href')

		var s_value = 2;


		$.ajax({
			url:s_url,
			type:'post',
			dataType:'json',
			data:{state:s_value},
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


<div id="modal-change"  class="modal fade form-horizontal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered btn-showcase">
        <div class="modal-content">
            <div class="modal-header">
		<h5 class="modal-title"><?php if(!empty($group['id'])){ ?>编辑<?php }else{ ?>添加<?php } ?>标签组</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">

                <div class="form-group batch-level" style="display: none;">
                    <label class="col-form-label must">团长等级</label>

                        <select name="batch-level" class="form-control">
                            <?php foreach( $community_head_level as $level ){ ?>
                                <option value="<?php echo ($level['id']); ?>"><?php echo ($level['levelname']); ?></option>
                            <?php } ?>
                        </select>

                </div>
                <div class="form-group batch-group" style="display: none;">
                    <label class="col-form-label must">团长分组</label>

                        <select name="batch-group[]" class="form-control select2" placeholder="会员会被加入指定的分组中">
							<option value="0">默认分组</option>
                            <?php foreach( $group_list as $group ){ ?>
                                <option value="<?php echo ($group['id']); ?>"><?php echo ($group['groupname']); ?></option>
                            <?php } ?>
                        </select>

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn-order" type="submit" id="modal-change-btn">提交</button>
                <button data-dismiss="modal" class="btn btn-secondary" type="button">取消</button>
            </div>
        </div>
    </div>
</div>
<script language="javascript">

    $("[data-toggle='batch-group'], [data-toggle='batch-level']").click(function () {
        var toggle = $(this).data('toggle');
        $("#modal-change .modal-title").text(toggle=='batch-group'?"批量修改分组":"批量修改会员等级");
        $("#modal-change").find("."+toggle).show().siblings().hide();
        $("#modal-change-btn").attr('data-toggle', toggle=='batch-group'?'group':'level');
        $("#modal-change").modal();
    });
    $("#modal-change-btn").click(function () {
        var _this = $(this);
        if(_this.attr('stop')){
            return;
        }
        var toggle = $(this).data('toggle');
        var ids = [];
        $(".checkone").each(function () {
            var checked = $(this).is(":checked");
            var id = $(this).val();
            if(checked && id){
                ids.push(id);
            }
        });
        if(ids.length<1){
            alert("请选择要批量操作的团长");
            return;
        }
        var option = $("#modal-change .batch-"+toggle+" option:selected");
        level = '';
        if (toggle=='group'){
            for(i=0;i<option.length;i++){
                if (level == ''){
                    level += $(option[i]).val();
                }else{
                    level += ','+$(option[i]).val();
                }
            }
        }else{
            var level = option.val();
        }

        var levelname = option.text();
		if(  confirm("确定要将选中团长移动到 "+levelname+" 吗？") )
		{
			 _this.attr('stop', 1).text("操作中...");

			 $.ajax({
				url:"<?php echo U('communityhead/changelevel');?>",
				type:"post",
				dataType:'json',
				data:{
					level: level,
					ids: ids,
					toggle: toggle
					},
				success:function(ret){
					$("#modal-change").modal('hide');
					if(ret.status==1){
						alert("操作成功");
						setTimeout(function () {
							location.reload();
						},1000);
					}else{
						alert(ret.result.message);
					}
				}
			 })
		}
    });
	$('.tg_community').click(function(){
		var community_id = $(this).attr("community_id");
		layer.closeAll();
		layer.open({
			type: 2,
			title: false,
			closeBtn: 1,
			scrollbar: false,
			area: ['300px', '300px'],
			shadeClose: true,
			content: "<?php echo U('communityhead/community_qrcode');?>/community_id/"+community_id
		});
	});

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
</script>
</body>