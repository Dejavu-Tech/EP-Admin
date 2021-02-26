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
							<h2>会员<span>列表</span></h2>
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
									<input type="hidden" name="c" value="user" />
									<input type="hidden" name="a" value="index" />

									<div class="form-row">
										<div class="p-r-5">
											<input type="text" class="form-control"  name="keyword" value="<?php echo ($_GPC['keyword']); ?>" placeholder="请输入昵称"/>

										</div>
										<div class="p-r-5">
											<select name="level_id" class="form-control">
												<option value="">会员等级</option>
												<?php foreach( $level_list as $level ){ ?>
												<option value="<?php echo ($level['id']); ?>" <?php if($level_id == $level['id']){ ?>selected<?php } ?>><?php echo ($level['levelname']); ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="p-r-5">
											<select name="groupid" class="form-control">
												<option value="">会员分组</option>
												<?php foreach( $group_list as $group ){ ?>
												<option value="<?php echo ($group['id']); ?>" <?php if($groupid == $group['id']){ ?>selected<?php } ?>><?php echo ($group['groupname']); ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="p-r-5">
											<select name="card_id" class="form-control">
												<option value="">付费会员卡名称</option>
												<?php foreach( $card_list as $card ){ ?>
												<option value="<?php echo ($card['id']); ?>" <?php if( $gpc['card_id'] == $card['id']){ ?>selected<?php } ?>><?php echo ($card['cardname']); ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="p-r-5">
											<select name="searchtime" class="form-control">
												<option value=''>不按时间</option>
												<option value="create" <?php if($searchtime=='create'){ ?>selected<?php } ?>>注册时间</option>
											</select>
										</div>
										<div class="p-r-5">
											<?php echo tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d H:i', $starttime),'endtime'=>date('Y-m-d H:i', $endtime)),true);;?>
										</div>
										<div class="p-r-5">
											<button class="btn btn-primary" type="submit"> 搜索</button>
											<button type="submit" name="export" value="1" class="btn btn-success ">导出</button>
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
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要删除?" data-href="<?php echo U('user/deleteuser');?>">
													删除
												</button>
												<button class="btn btn-pill btn-primary btn-sm " type="button" data-toggle="batch-group" data-href="<?php echo U('user/changelevel', array('toggle' => 'group'));?>" >修改分组</button>
												<button class="btn btn-pill btn-primary btn-sm " type="button" data-toggle="batch-level" data-href="<?php echo U('user/changelevel', array('toggle' => 'level'));?>" >修改等级</button>
											</div>
										</div>
										<div class="table-responsive">
											<table class="display dataTable text-center" lay-even lay-skin="line" lay-size="lg">
												<thead>
												<tr>
													<th>选择</th>
													<th>ID</th>
													<th>头像</th>
													<th>用户名</th>
													<th>等级/分组/付费会员卡</th>
													<th>
														<a href="<?php echo U('user/index', array('sortby' =>$sortby,'sortfield' => 'score','keyword'=>$keyword,'level_id' =>$level_id,'groupid' => $groupid,'card_id' =>$card_id,'searchtime'=>$searchtime,'sort_starttime'=>$starttime,'sort_endtime'=>$endtime, 'type' =>$type, ) );?>" >
															<span>积分</span>
															<span class="layui-table-sort layui-inline" lay-sort="<?php echo ($sortfield == 'score' ? $sortby :''); ?>">
										<i class="layui-edge layui-table-sort-asc" title="升序"></i>
										<i class="layui-edge layui-table-sort-desc" title="降序"></i>
									</span>
														</a>
														/
														<a href="<?php echo U('user/index', array('sortby' =>$sortby,'sortfield' => 'account_money','keyword'=>$keyword,'level_id' =>$gpc['level_id'],'groupid' => $gpc['groupid'],'card_id' =>$gpc['card_id'],'searchtime'=>$searchtime,'sort_starttime'=>$starttime,'sort_endtime'=>$endtime, 'type' =>$type, ) );?>" >
															<span>余额</span>
															<span class="layui-table-sort layui-inline" lay-sort="<?php echo ($sortfield == 'account_money' ? $sortby :''); ?>">
										<i class="layui-edge layui-table-sort-asc" title="升序"></i>
										<i class="layui-edge layui-table-sort-desc" title="降序"></i>
									</span>
														</a>
													</th>
													<th>订单情况</th>
													<?php if($commiss_level > 0){ ?>
													<th>是否分销</th>
													<?php } ?>
													<?php if( $is_user_shenhe == 1 ){ ?>
													<th style="">是否审核</th>
													<?php } ?>

													<th>注册时间</th>
													<th>操作</th>
												</tr>
												</thead>
												<tbody>
												<?php foreach( $list as $row ){ ?>
												<tr>
													<td>
														<div class="checkbox checkbox-primary m-l-10" >
															<input type='checkbox' class="checkone" name="item_checkbox" lay-skin="primary" value="<?php echo ($row['member_id']); ?>" style="display: none"/>
														</div>
													</td>
													<td>
														<?php echo ($row['member_id']); ?>
													</td>
													<td>
														<img class="img-40" src="<?php echo tomedia($row['avatar']);?>" style='border-radius:50%;border:1px solid #efefef;' />
													</td>
													<td>
														<div rel="pop" style="" >
										   <span style="display: flex;flex-direction: column;justify-content: center;">
											   <span class="nickname">
												   <?php if(empty($row['username'])){ ?>未更新
												   <?php }else{ ?>
												   <?php echo ($row['username']); ?><br />
												   推荐人：<?php if(empty($row['share_name'])){ ?>总店<?php }else{ echo ($row['share_name']); } ?>
												   <?php } ?>
												   <br/>
												   当前小区:<?php echo ($row['cur_communityname']); ?>
												   <br/><a href="javascript:;"  class="chose_head_id line-text" member_id="<?php echo ($row['member_id']); ?>" >(点击切换)</a>
													 <br>
												   手机:<?php if( !empty($row['telephone']) ){ echo ($row['telephone']); ?> <?php } else {?>暂无<?php  }?>

											   </span>
											   <?php if($row['isblack']==1){ ?>
											   <span class="text-danger">未启用<i class="icow icow-heimingdan1"style="color: #db2228;margin-left: 2px;font-size: 13px;"></i></span>
											   <?php } ?>
										   </span>
														</div>
													</td>

													<td>
														<?php if(empty($row['levelname'])){ ?>普通会员<?php }else{ echo ($row['levelname']); } ?><br/>
														<?php if(empty($row['groupname'])){ ?>默认分组<?php }else{ echo ($row['groupname']); } ?><br/>
														<?php if(empty($row['cardname'])){ ?>默认会员卡<?php  }else{ echo ($row['cardname']); } ?>
													</td>

													<td>
														<span >积分:  <span style="color: #5097d3"><?php echo intval($row['score']);?></span> </span>
														<br/><span>余额: <span class="text-warning"><?php echo round($row['account_money'],2);?> </span></span>
													</td>

													<td>
														<span>订单: <?php echo intval($row['ordercount']);?></span>
														<br/><span> 金额: <span class="text-warning"><?php echo floatval($row['ordermoney']);?></span></span>
													</td>
													<?php if($commiss_level > 0){ ?>
													<td>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="statewsitch" data-href="<?php echo U('distribution/become_agent_check',array('id'=>$row['member_id']));?>" <?php if($row['comsiss_flag']==1 && $row['comsiss_state']==1){ ?>checked<?php } ?> lay-skin="switch" >
														</label>
													</td>
													<?php } ?>
													<?php if($is_user_shenhe == 1 ){ ?>
													<td>
														<?php if($row['is_apply_state'] == 0 ){ ?>
															<div style="display: flex"><div><label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="statewsitch" data-href="<?php echo U('user/user_shenhe',array('id'=>$row['member_id']));?>" <?php if($row['is_comsiss_audit']==1){ ?>checked<?php } ?> lay-skin="switch" >
														</label><br/>通过审核</div>
														<div><label class="switch" style="margin-bottom: 0">
														<input type="checkbox" name="" lay-filter="statewsitch" data-href="<?php echo U('User/agent_unshenhe',array('id'=>$row['member_id']));?>" <?php if($row['is_apply_state']==1 ){ ?>checked<?php } ?> lay-skin="switch" lay-text="">
														</label><br/>拒绝审核</div></div>
														<?php }else if( $row['is_apply_state'] == 2 ){ ?>
														<span>拒绝审核 </span>
														<?php }else{ ?>
														<span>审核通过 </span>
														<?php } ?>
													</td>
													<?php } ?>
													<td><?php echo date("Y-m-d",$row['create_time']);?><br/><?php echo date("H:i:s",$row['create_time']);?></td>
													<td style="overflow:visible;text-align: center;">


														<a class="btn btn-xs btn-primary" href="<?php echo U('user/detail',array('id' => $row['member_id'], 'ok' => 1));;?>" title="">

													会员详情

														</a>
														<a class="btn btn-xs btn-primary" href="<?php echo U('order/index', array('type' => 'all','searchfield'=>'member_id' ,'keyword' => $row['member_id'] ));?>"
														   title=''>
															会员订单
														</a>

														<a class="btn btn-xs btn-primary"
														   href="<?php echo U('user/recharge_flow', array('id'=>$row['member_id']));?>"
														   title=''>
															余额流水
														</a>

														<button class="btn btn-xs btn-primary deldom" type="button" data-toggle='ajaxRemove' data-href="<?php echo U('user/deleteuser',array('id' => $row['member_id']));;?>" data-confirm="确定要删除该会员吗？" style="padding: 0.05rem 0.4rem;font-size: 12px;letter-spacing: 0.5px; border: 1px solid transparent;font-weight: 700;border-radius: 0.5rem">

															删除会员
														</button>

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
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要删除?" data-href="<?php echo U('user/deleteuser');?>">
													删除
												</button>
												<button class="btn btn-pill btn-primary btn-sm " type="button" data-toggle="batch-group" data-href="<?php echo U('user/changelevel', array('toggle' => 'group'));?>" >修改分组</button>
												<button class="btn btn-pill btn-primary btn-sm " type="button" data-toggle="batch-level" data-href="<?php echo U('user/changelevel', array('toggle' => 'level'));?>" >修改等级</button>
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



<script src="/layuiadmin/layui/layui1.js"></script>
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
		<h5 class="modal-title"><?php if(!empty($group['id'])){ ?>编辑<?php }else{ ?>添加<?php } ?>标签组1</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">

                <div class="form-group batch-level" style="display: none;">
                    <label class="col-form-label must">团长等级</label>
                    <div class="col-sm-9 col-xs-12">
                        <select name="batch-level" class="form-control">
						<option value="0">普通等级</option>
                             <?php foreach( $level_list as $level ){ ?>
                                <option value="<?php echo ($level['id']); ?>"><?php echo ($level['levelname']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group batch-group" style="display: none;">
                    <label class="col-form-label must">会员分组</label>
                    <div class="col-sm-9 col-xs-12">
                        <select name="batch-group[]" class="form-control " placeholder="会员会被加入指定的分组中">
							<option value="0">默认分组</option>
                            <?php foreach( $group_list as $group ){ ?>
                                <option value="<?php echo ($group['id']); ?>"><?php echo ($group['groupname']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
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
    var modify_url = '';
    $("[data-toggle='batch-group'], [data-toggle='batch-level']").click(function () {
        var toggle = $(this).data('toggle');
        modify_url = $(this).attr('data-href');


        $("#modal-change .modal-title").text(toggle=='batch-group'?"批量修改分组":"批量修改会员等级");
        $("#modal-change").find("."+toggle).show().siblings().hide();
        $("#modal-change-btn").attr('data-toggle', toggle=='batch-group'?'group':'level');
        $("#modal-change").modal();
    });

	$('.chose_head_id').click(function(){
		var s_member_id = $(this).attr('member_id');

		$.post("<?php echo U('communityhead/lineheadquery', array('is_member_choose' => 1,'is_memberlist' =>1));?>", {s_member_id:s_member_id}, function(shtml){
		 layer.open({
			type: 1,
			area: '700px',
			content: shtml //注意，如果str是object，那么需要字符拼接。
		  });
		});
	})
	$('.layui-table-sort').click(function(){
		$(this).attr('lay-sort','asc');
	})
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
            alert("请选择要批量操作的会员");
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
		 console.log(modify_url);
		if(  confirm("确定要将选中会员移动到 "+levelname+" 吗？") )
		{
			 console.log(modify_url);
			 $.ajax({
				url:modify_url,
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
</script>
</body>