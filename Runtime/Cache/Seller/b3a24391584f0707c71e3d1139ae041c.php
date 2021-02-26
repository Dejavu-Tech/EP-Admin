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
	<link id="color" rel="stylesheet" href="/assets/css/color-2.css" media="screen">
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
   <script type="text/javascript" src="/assets/js/dist/jquery/nestable/jquery.nestable.js"></script>
<link href="/assets/js/dist/jquery/nestable/nestable.css" rel="stylesheet">

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
							<h2>商品<span>分类</span></h2>
						</div>
						<div class="col-lg-6 breadcrumb-right" style="float: right">
							<ol class="breadcrumb">
								<a href="<?php echo U('goods/addcategory', array('ok' => 1));?>"><button class="btn btn-info btn-air-info" style=""><i class="fa fa-plus"></i> 添加商品分类</button></a>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
			<ul class="nav nav-pills m-b-20" id="v-pills-tab" role="tablist" aria-orientation="vertical">
				<li class="nav-item"><a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">商品分类</a></li>
				<li class="nav-item"><a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">分类设置</a></li>
			</ul>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<div class="card">
							<div class="card-body">
								<div class="tab-content" id="v-pills-tabContent">
									<div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
										<form action="" method="post"  lay-filter="component-layui-form-item" class="form theme-form layui-form" enctype="multipart/form-data" >
											<div class="" id="div_nestable">
												<?php foreach( $category as $row ){ ?>
												<?php if(empty($row['pid'])){ ?>
												<div class="col-sm-12 ep-category full m-b-20" data-id="<?php echo ($row['id']); ?>">
													<div class="form-group row alert alert-primary outline" >
														<label class="col-form-label">一级分类：<?php echo ($row['name']); ?></label>

														<div class="media col justify-content-end">
															<label class="col-form-label">排序：</label>
															<div style="width: 70px">
																<input type="text" class="cate_sort form-control" name="cate_sort" id="cate_sort_<?php echo ($row['id']); ?>" cate_id="<?php echo ($row['id']); ?>" value="<?php echo ($row['sort_order']); ?>" onchange="updateSort(this)"/>
															</div>
															<label class="col-form-label m-l-10 m-r-10">首页显示</label>
															<label class="switch m-r-10">
																<input type="checkbox" name="" lay-filter="enabledwsitch" data-href="<?php echo U('goods/category_enabled',array('id'=>$row['id']));?>" <?php if($row['is_show']==1){ ?>checked<?php } ?> lay-skin="switch" >
															</label>
															<label class="col-form-label m-r-10">分类页显示</label>
															<label class="switch m-r-10">
																<input type="checkbox" class=" bg-success" name="" lay-filter="enabledtypewsitch" data-href="<?php echo U('goods/category_typeenabled',array('id'=>$row['id']));?>" <?php if($row['is_type_show']==1){ ?>checked<?php } ?> lay-skin="switch" >
															</label>
															<a class='btn btn-pill btn-primary btn-sm btn-operation btn-op m-r-5' data-toggle="ajaxModal" href="<?php echo U('goods/addcategory', array('pid' => $row['id'], 'ok' => 1));?>" >
																<span data-toggle="tooltip" data-placement="top" title="" data-original-title="添加子分类">添加子分类</span>
															</a>
															<a class='btn btn-pill btn-primary btn-sm btn-operation btn-op m-r-5' data-toggle="ajaxModal" href="<?php echo U('goods/addcategory', array('id' => $row['id'] , 'ok' => 1));?>" >
																<span data-toggle="tooltip" data-placement="top"  data-original-title="修改">修改</span>
															</a>
															<a class='btn btn-pill btn-primary btn-sm btn-operation btn-op deldom'  href="javascript:;" data-href="<?php echo U('goods/category_delete', array('id' => $row['id']));?>" data-confirm='确认删除此分类吗？'>删除</a>
														</div>
													</div>
												</div>

												<?php if( count($children[$row['id']])>0 ){ ?>
												<?php foreach( $children[$row['id']] as $child ){ ?>
												<div class="col-sm-11 offset-sm-1 ep-category full m-b-20" data-id="<?php echo ($child['id']); ?>">
													<div class="form-group row alert alert-secondary outline">
														<label class="col-form-label"><img src="<?php echo tomedia($child['thumb']);;?>" width='30' height="30" onerror="$(this).remove()" style='padding:1px;border: 1px solid #ccc;float:left;' />二级分类：<?php echo ($child['name']); ?></label>
														<div class="media col justify-content-end">
															<label class="col-form-label">排序：</label>
															<div style="width: 70px">
																<input type="text" class="cate_sort form-control" name="cate_sort" id="cate_sort_<?php echo ($child['id']); ?>" cate_id="<?php echo ($child['id']); ?>" value="<?php echo ($child['sort_order']); ?>" onchange="updateSort(this)"/>
															</div>
															<label class="col-form-label m-l-10 m-r-10">首页显示</label>
															<label class="switch m-r-10">
																<input type="checkbox" name="" lay-filter="enabledwsitch" data-href="<?php echo U('goods/category_enabled',array('id'=>$child['id']));?>" <?php if($child['is_show']==1){ ?>checked<?php } ?> lay-skin="switch" >
															</label>
															<label class="col-form-label m-r-10">分类页显示</label>
															<label class="switch m-r-10">
																<input type="checkbox" name="" lay-filter="enabledtypewsitch" data-href="<?php echo U('goods/category_typeenabled',array('id'=>$child['id']));?>" <?php if($child['is_type_show']==1){ ?>checked<?php } ?> lay-skin="switch" >
															</label>
															<a class='btn btn-pill btn-primary btn-sm btn-operation btn-op m-r-5' data-toggle="ajaxModal" href="<?php echo U('goods/addcategory', array('pid' => $child['id']));?>" title='添加子分类' >
																<span data-toggle="tooltip" data-placement="top" title="" data-original-title="添加子分类">添加子分类</span>
															</a>
															<a class='btn btn-pill btn-primary btn-sm btn-operation btn-op m-r-5' data-toggle="ajaxModal" href="<?php echo U('goods/addcategory', array('id' => $child['id']));?>" title="修改" >
																<span data-toggle="tooltip" data-placement="top" title="" data-original-title="修改">修改</span>
															</a>
															<a class='btn btn-pill btn-primary btn-sm btn-operation btn-op deldom'  href="javascript:;" data-href="<?php echo U('goods/category_delete', array('id' => $child['id']));?>" data-confirm="确认删除此分类吗？">
																<span data-toggle="tooltip" data-placement="top"  data-original-title="删除">删除</span>
															</a>
														</div>
													</div>
												</div>

												<?php if(count($children[$child['id']])>0 ){ ?>
												<?php foreach( $children[$child['id']] as $third ){ ?>
												<div class="col-sm-10 offset-sm-2 ep-category full m-b-20" data-id="<?php echo ($third['id']); ?>">
													<div class="form-group row alert alert-info outline">
														<label class="col-form-label"><img src="<?php echo tomedia($third['thumb']);;?>" width='30' height="30" onerror="$(this).remove()" style='padding:1px;border: 1px solid #ccc;float:left;' />三级分类：<?php echo ($third['name']); ?></label>
														<div class="media col justify-content-end">
															<label class="col-form-label">排序：</label>
															<div style="width: 70px">
																<input type="text" class="cate_sort form-control" name="cate_sort" id="cate_sort_<?php echo ($third['id']); ?>" cate_id="<?php echo ($third['id']); ?>" value="<?php echo ($third['sort_order']); ?>" onchange="updateSort(this)"/>
															</div>
															<label class="col-form-label m-l-10 m-r-10">首页显示</label>
															<label class="switch m-r-10">
																<input type="checkbox" name="" lay-filter="enabledwsitch" data-href="<?php echo U('goods/category_enabled',array('id'=>$third['id']));?>" <?php if($third['is_show']==1){ ?>checked<?php } ?> lay-skin="switch" >
															</label>
															<label class="col-form-label m-r-10">分类页显示</label>
															<label class="switch m-r-10">
																<input type="checkbox" name="" lay-filter="enabledtypewsitch" data-href="<?php echo U('goods/category_typeenabled',array('id'=>$third['id']));?>" <?php if($third['is_type_show']==1){ ?>checked<?php } ?> lay-skin="switch" >
															</label>
															<a class='btn btn-pill btn-primary btn-sm btn-operation btn-op m-r-5' href="<?php echo U('goods/addcategory', array('id' => $third['id']));?>" title="修改" >
																<span data-toggle="tooltip" data-placement="top" title="" data-original-title="修改">修改</span>
															</a>
															<a class='btn btn-pill btn-primary btn-sm btn-operation btn-op deldom'  href="javascript:;" data-href="<?php echo U('goods/category_delete', array('id' => $third['id']));?>" data-confirm="确认删除此分类吗？">
																删除
															</a>
														</div>
													</div>
												</div>
												<?php } ?>
												<?php } ?>

												<?php } ?>
												<?php } ?>

												<?php } ?>
												<?php } ?>


											</div>

										</form>

									</div>
									<div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
										<form action="" method="post" class="form theme-form layui-form" lay-filter="component-layui-form-item" enctype="multipart/form-data" >
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">显示底部菜单</label>
												<div class="col-sm-10">
													<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
														<div class="radio radio-primary">
															<input id="radioinline1" type="radio" name="parameter[is_show_cate_tabbar]" value="0" <?php if(empty($data) || $data['is_show_cate_tabbar'] ==0 ){ ?>checked <?php } ?>>
															<label class="mb-0" for="radioinline1">否</label>
														</div>
														<div class="radio radio-primary">
															<input id="radioinline2" type="radio" name="parameter[is_show_cate_tabbar]" value="1" <?php if(!empty($data) && $data['is_show_cate_tabbar'] ==1 ){ ?>checked <?php } ?>>
															<label class="mb-0" for="radioinline2">是</label>
														</div></br>
														<small>进入该子分类页面后是否显示底部导航栏</small>
													</div>
												</div>
											</div>
											<div class="form-group row">
												<label class="col-sm-2 col-form-label">首页文字分类</label>
												<div class="col-sm-10">
													<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
														<div class="radio radio-primary">
															<input id="radioinline3" type="radio" name="parameter[hide_index_type]" value="1" <?php if(!empty($data) && $data['hide_index_type']==1 ){ ?>checked <?php } ?>>
															<label class="mb-0" for="radioinline3">隐藏</label>
														</div>
														<div class="radio radio-primary">
															<input id="radioinline4" type="radio" name="parameter[hide_index_type]" value="0" <?php if(empty($data) || $data['hide_index_type']==0 ){ ?>checked <?php } ?>>
															<label class="mb-0" for="radioinline4">显示</label>
														</div>

													</div>
												</div>
											</div>
											<div class="form-group row">
												<div class="">
													<input type="submit" value="提交" lay-submit lay-filter="formDemo" class="btn btn-pill btn-primary" />
												</div>
											</div>
										</form>

									</div>

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
<script src="/layuiadmin/layui/layui1.js"></script>
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

layui.use(['jquery', 'layer', 'form', 'element'], function(){
  $ = layui.$;
  var form = layui.form;

	form.on('radio(linktype)', function(data){
		if (data.value == 2) {
			$('#typeGroup').show();
		} else {
			$('#typeGroup').hide();
		}
	});

	form.on('switch(enabledwsitch)', function(data){

	  var s_url = $(this).attr('data-href')

	  var s_value = 1;
	  if(data.elem.checked)
	  {
		s_value = 1;
	  }else{
		s_value = 0;
	  }

	  $.ajax({
			url:s_url,
			type:'post',
			dataType:'json',
			data:{enabled:s_value},
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

	form.on('switch(enabledtypewsitch)', function(data){

	  var s_url = $(this).attr('data-href')

	  var s_value = 1;
	  if(data.elem.checked)
	  {
		s_value = 1;
	  }else{
		s_value = 0;
	  }

	  $.ajax({
			url:s_url,
			type:'post',
			dataType:'json',
			data:{enabled:s_value},
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

	form.on('switch(enabledexpresswsitch)', function(data){

		var s_url = $(this).attr('data-href')

		var s_value = 1;
		if(data.elem.checked)
		{
			s_value = 1;
		}else{
			s_value = 0;
		}

		$.ajax({
			url:s_url,
			type:'post',
			dataType:'json',
			data:{enabled:s_value},
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

 <script language='javascript'>
	$(function () {

		$('#btnExpand').click(function () {
			var action = $(this).data('action');
			if (action === 'expand') {
				$('#div_nestable').nestable('collapseAll');
				$(this).data('action', 'collapse').html('<i class="fa fa-angle-up"></i> 展开所有');

			} else {
				$('#div_nestable').nestable('expandAll');
				$(this).data('action', 'expand').html('<i class="fa fa-angle-down"></i> 折叠所有');
			}
		})
		var depth = <?php echo intval($_W['shopset']['category']['level']);?>;
		if (depth <= 0) {
			depth = 3;
		}


		$('.ep-category').addClass('full');

		$(".form-group a,.form-group div").mousedown(function (e) {

			e.stopPropagation();
		});
		var $expand = false;
		$('#nestableMenu').on('click', function (e)
		{
			if ($expand) {
				$expand = false;
				$('.dd').nestable('expandAll');
			} else {
				$expand = true;
				$('.dd').nestable('collapseAll');
			}
		});

		$('form').submit(function(){
			var json = window.JSON.stringify($('#div_nestable').nestable("serialize"));
			$(':input[name=datas]').val(json);
		});

	})
	function updateSort(obj){
		var cate_id = $(obj).attr('cate_id');
		var sort_order = $(obj).val();
		$.ajax({
			url: "<?php echo U('goods/update_catesort');?>",
			type: "post",
			data: {cate_id:cate_id,sort_order:sort_order},
			dataType:'json',
			success: function (info) {
				if(info.status == 1){
					var go_url = location.href;
					if( info.result.hasOwnProperty("url") )
					{
						go_url = info.result.url;
					}
					location.href = info.result.url;
				}else {
					layer.msg(info.result.message,{icon: 1,time: 2000});
				}
			}
		});
	}
</script>
</body>