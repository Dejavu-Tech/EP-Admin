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
	<link rel="stylesheet" type="text/css" href="/assets/css/datatable-extension.css">
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
<table  id="demo" lay-filter="test"></table>
<div class="page-wrapper custom-scrollbar">
	<div class="page-body-wrapper">
		<div class="page-body" style="margin: 0;min-height: calc(100vh - 55px);">
			<div class="container-fluid">
				<div class="page-header">
					<div class="row">
						<div class="col-lg-6 main-header">
							<h6 class="mb-0">当前位置</h6>
							<h2>商品<span>列表</span></h2>
						</div>
						<div class="col-lg-6 breadcrumb-right" style="float: right">
							<ol class="breadcrumb">
								<a href="<?php echo U('goods/addgoods', array('ok' =>1));?>"><button class="btn btn-info btn-air-info" style=""><i class="fa fa-plus"></i> 添加商品</button></a>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid" style="margin-top: 0px">
				<ul class="nav nav-pills m-b-20" id="pills-tab" role="tablist">
					<li class="nav-item <?php if(empty($type) || $type=='all'){ ?>active show<?php } ?>"><a class="nav-link" href="<?php echo U('goods/index');?>">全部商品（<?php echo ($all_count); ?>）</a></li>
					<li class="nav-item <?php if($type=='saleon'){ ?>active show<?php } ?>"><a class="nav-link" href="<?php echo U('goods/index',array('type'=>'saleon'));?>" >出售中（<?php echo ($onsale_count); ?>）</a></li>
					<li class="nav-item <?php if($type=='stock_notice'){ ?>active show<?php } ?>"><a class="nav-link"  href="<?php echo U('goods/index',array('type'=>'stock_notice'));?>">库存预警（<?php echo ($stock_notice_count); ?>）</a></li>
					<li class="nav-item <?php if($type=='getdown'){ ?>active show<?php } ?>"><a class="nav-link" href="<?php echo U('goods/index',array('type'=>'getdown'));?>">已下架（<?php echo ($getdown_count); ?>）</a></li>
					<li class="nav-item <?php if($type=='wait_shen'){ ?>active show<?php } ?>"><a class="nav-link" href="<?php echo U('goods/index',array('type'=>'wait_shen'));?>">待审核（<?php echo ($waishen_count); ?>）</a></li>
					<li class="nav-item <?php if($type=='refuse'){ ?>active show<?php } ?>"><a class="nav-link" href="<?php echo U('goods/index',array('type'=>'refuse'));?>">已拒绝（<?php echo ($unsuccshen_count); ?>）</a></li>
					<!--<?php if($is_open_shenhe == 1){ ?>
                    <li class="nav-item <?php if($type=='warehouse'){ ?>active show<?php } ?>" ><a class="nav-link" href="<?php echo U('goods/index',array('type'=>'warehouse'));?>">仓库（<?php echo ($warehouse_count); ?>）</a></li>
                    <?php } ?>-->
					<li class="nav-item <?php if($type=='recycle'){ ?>active show<?php } ?>"><a class="nav-link" href="<?php echo U('goods/index',array('type'=>'recycle'));?>">回收站（<?php echo ($recycle_count); ?>）</a></li>
				</ul>
			</div>
            <div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<div class="card">
							<div class="card-body" >
								<form action="" id="searchform" class="form-horizontal form-search layui-form" method="get"  role="form">
									<input type="hidden" name="c" value="goods" />
									<input type="hidden" name="a" value="index" />
									<input type="hidden" name="type" value="<?php echo ($type); ?>" />
									<input type="hidden" name="sortfield" id="sortfield" value="<?php echo ($sortfield); ?>" />
									<input type="hidden" name="sortby" id="sortby" value="<?php echo ($sortby); ?>" />

									<div class="form-row">
										<div class="p-r-5">
											<input type="text" class="form-control"  name="keyword" value="<?php echo ($keyword); ?>" placeholder="输入商品编码或者名称"/>
										</div>
										<div class="p-r-5">
											<select name='searchtime'  class="form-control" >
												<option value=''>不按时间</option>
												<option value='create' <?php if($searchtime=='create'){ ?>selected<?php } ?>>团购时间</option>
											</select>
										</div>
										<div class="p-r-5">
											<select name="cate" class="form-control" >
												<option value="" <?php if( empty($cate) ){ ?>selected<?php } ?> >商品分类</option>
												<?php foreach($category as $c){ ?>
												<option value="<?php echo ($c['id']); ?>" <?php if( $cate==$c['id'] ){ ?>selected<?php } ?> ><?php echo ($c['name']); ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="p-r-5">
											<button class="btn btn-primary" type="submit"> 搜索</button>
											<button type="submit" name="export" value="1" class="btn btn-primary">导出</button>
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
												<?php if($is_index){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch'  data-href="<?php echo U('goods/change',array('type'=>'is_index_show', 'value' => 1));?>">首页推荐</button>
												<?php } ?>
												<?php if($is_updown){ ?>
												<?php if( defined('ROLE') && ROLE == 'agenter'){ ?>
												<?php if($is_supply_add_goods_shenhe){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch' data-href="<?php echo U('goods/change',array('type'=>'grounding','value'=>1));?>">上架</button>
												<?php } ?>
												<?php }else{ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch' data-href="<?php echo U('goods/change',array('type'=>'grounding','value'=>1));?>">上架</button>
												<?php } ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch'  data-href="<?php echo U('goods/change',array('type'=>'grounding','value'=>0));?>">下架</button>
												<?php } ?>
												<?php if($is_open_fullreduction == 1 && $is_fullreduce ){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch' data-href="<?php echo U('goods/change_cm',array('type'=>'is_take_fullreduction','value'=>1));?>">参加满减</button>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch'  data-href="<?php echo U('goods/change_cm',array('type'=>'is_take_fullreduction','value'=>0));?>">不参加满减</button>
												<?php } ?>

												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-group'  id="batchcatesbut" >商品分类</button>
												<?php if( defined('ROLE') && ROLE == 'agenter'){ ?>
												<?php if($is_distributionsale){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation"  type="button" data-toggle='batch-group'  id="batch_head" >分配售卖团长</button>
												<button class="btn btn-pill btn-primary btn-sm btn-operation"  type="button" data-toggle='batch-group'  id="batch_head_group" >分配售卖团长分组</button>
												<?php  }else{ ?>
												<?php } ?>
												<?php  }else{ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation"  type="button" data-toggle='batch-group'  id="batch_head" >分配售卖团长</button>
												<button class="btn btn-pill btn-primary btn-sm btn-operation"  type="button" data-toggle='batch-group'  id="batch_head_group" >分配售卖团长分组</button>
												<?php } ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-group'  id="batchtime" >设置活动时间</button>

												<?php if($is_index){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch'  data-href="<?php echo U('goods/change',array('type'=>'is_index_show', 'value' => 0));?>">取消首页推荐</button>
												<?php } ?>

												<?php if($type!='recycle'){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要删除吗？删除后商品将进入回收站。" data-href="<?php echo U('goods/change',array('type'=>'grounding','value'=>3));?>">删除</button>
												<?php } ?>
												<?php if($type=='recycle'){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要彻底删除吗?" data-href="<?php echo U('goods/delete');?>">彻底删除</button>
												<?php } ?>
											</div>
										</div>
										<div class="table-responsive">
											<table class="display dataTable text-center" lay-even lay-skin="line" lay-size="lg">
												<thead>
												<tr>
													<th>选择</th>
													<th>ID</th>
													<th>商品图片</th>
													<th>商品名称</th>
													<th>活动时间</th>
													<th>价格</th>
													<th>
														<a href="<?php echo U('goods/index', array('sortby' =>$sortby,'sortfield' => 'seller_count','keyword'=>$keyword,'cate' =>$cate,'searchtime'=>$searchtime,'sort_starttime'=>$starttime,'sort_endtime'=>$endtime, 'type' =>$type, ) );?>" >
															<span>总销量</span>
															<span class="layui-table-sort layui-inline" lay-sort="<?php echo ($sortfield == 'seller_count' ? $sortby :''); ?>">
																<i class="layui-edge layui-table-sort-asc" title="升序"></i>
																<i class="layui-edge layui-table-sort-desc" title="降序"></i>
										                    </span>
														</a>
													</th>
													<th style="">
														<a href="<?php echo U('goods/index', array('sortby' =>$sortby,'sortfield' => 'total','keyword'=>$keyword,'cate' =>$cate,'searchtime'=>$searchtime,'sort_starttime'=>$starttime,'sort_endtime'=>$endtime, 'type' =>$type, ) );?>" >
															<span>库存</span>
															<span class="layui-table-sort layui-inline" lay-sort="<?php echo ($sortfield == 'total' ? $sortby :''); ?>">
											                     <i class="layui-edge layui-table-sort-asc" title="升序"></i>
											                     <i class="layui-edge layui-table-sort-desc" title="降序"></i>
										                    </span>
														</a>
													</th>
													<th style="">
														<a href="<?php echo U('goods/index', array('sortby' =>$sortby,'sortfield' => 'day_salescount','keyword'=>$keyword,'cate' =>$cate,'searchtime'=>$searchtime,'sort_starttime'=>$starttime,'sort_endtime'=>$endtime, 'type' =>$type, ) );?>" >
															<span>本日销量</span>
															<span class="layui-table-sort layui-inline" lay-sort="<?php echo ($sortfield == 'day_salescount' ? $sortby :''); ?>" >
											                     <i class="layui-edge layui-table-sort-asc" title="升序"></i>
											                     <i class="layui-edge layui-table-sort-desc" title="降序"></i>
															</span>
														</a>
													</th>

													<?php if($is_open_fullreduction == 1 && $is_fullreduce){ ?>
													<th>是否满减</th>
													<?php } ?>
													<?php if($is_updown == 1 && $is_fullreduce){ ?>
													<th>是否上架<?php if($is_open_shenhe==1){ ?><br/>审核<?php } ?></th>
													<?php }else{ ?>
													<th></th>
													<?php } ?>

													<?php if( defined('ROLE') && ROLE == 'agenter'){ ?>
													<?php if($is_top){ ?>
													<?php if($index_sort_method == 1){ ?>

													<?php }else{ ?>
													<th>置顶</th>
													<?php } ?>
													<?php } ?>
													<?php  }else{ ?>

													<?php if($is_top){ ?>
													<?php if($index_sort_method == 1){ ?>
													<th>首页排序</th>
													<?php }else{ ?>
													<th>置顶</th>
													<?php } ?>
													<?php } ?>
													<?php } ?>

													<?php if($is_index){ ?>
													<th>首页推荐</th>
													<?php } ?>
													<th>操作</th>
												</tr>
												</thead>
												<tbody >
												<?php foreach( $list as $item ){ ?>
												<tr style="vertical-align:middle">
													<td>
														<div class="checkbox checkbox-primary m-l-10">
															<input type='checkbox' id="checkbox-primary-2" name="item_checkbox" data_is_all_sale="<?php echo ($item['is_all_sale']); ?>" class="checkone" lay-skin="primary"  value="<?php echo ($item['id']); ?>" style="display: none"/>
														</div>
													</td>
													<td style="text-align:center;">
														<?php echo ($item['id']); ?>
													</td>
													<td>
														<a href="<?php echo U('goods/edit', array('id' => $item['id'],'goodsfrom'=>$goodsfrom,'page'=>$page));?>">
															<img src="<?php echo ($item['thumb']); ?>" style="width:60px;height:60px;border-radius:5px;margin:0"  />
														</a>
													</td>
													<td>
														<a href='javascript:;' data-toggle='ajaxEdit' data-href="<?php echo U('goods/change',array('type'=>'goodsname','id'=>$item['id']));?>" >
															<span class="txt-secondary" ><?php echo ($item['goodsname']); ?></span>
														</a>
														<br/>
														<?php if($item['is_only_hexiao'] == 1){ ?>
														<span class="text-danger">[核销]</span>
														<?php } ?>

														<?php if($isopen_localtown_delivery == 1 ){ ?>
														<?php if($item['supply_id'] <=0){ ?>
														<?php if( $item['is_only_distribution'] == 1 ){ ?>
														<span class="line-text">[同城配送]</span>
														<?php } ?>
														<?php }else{ ?>
														<?php if( $item['is_only_distribution'] == 1 && $supply_is_open_localtown_distribution == 1 ){ ?>
														<span class="line-text">[同城配送]</span>
														<?php } ?>
														<?php } ?>
														<?php } ?>

														<?php if($seckill_is_open == 1){ ?>
														<?php if( $item['is_seckill'] == 1 ){ ?>
														<span class="text-danger">[整点秒杀]</span>
														<?php } ?>
														<?php } ?>

														<?php if($item['is_new_buy'] == 1){ ?>
														<span class="txt-secondary">[新人专享]</span>
														<?php } ?>

														<?php if($item['is_only_express'] == 1){ ?>
														<span class="txt-secondary">[仅快递]</span>
														<?php } ?>

														<?php if($item['is_spike_buy'] == 1){ ?>
														<span class="text-danger">[限时秒杀]</span>
														<?php } ?>

														<?php if($item['supply_id'] <=0){ ?><span class="txt-primary">[自营<?php if(!empty($item['supply_name'])){ echo ($item['supply_name']); } ?>]</span><?php }else if( !empty($item['supply_name']) ){ ?><span class="txt-primary">[<?php echo ($item['supply_name']); ?>]</span><?php } ?>

														<?php if(!empty($item['cate']) ){ ?>
														<?php foreach( $item['cate'] as $g_cate ){ ?>
														<span class="txt-info">[<?php echo isset($category[$g_cate['cate_id']]) ? $category[$g_cate['cate_id']]['name']: '无分类';?>]</span>
														<?php } ?>
														<?php }else{ ?>
														<span class="txt-info">[无分类]</span>
														<?php } ?>

														<?php if( $item['is_all_sale'] == 1 ){ ?>
														<span >[所有团长<?php echo ($item['head_count']); ?>]</span>
														<?php }else if( $item['head_count'] >0 ){ ?>
														<!--<span class="text-green">[部分团长<?php echo ($item['head_count']); ?>]</span>-->
														<a href="<?php echo U('goods/goods_head',array('id'=>$item['id']));?>" style="color:#428bca;"><span class="text-green">[部分团长可售 <?php echo ($item['head_count']); ?>]</span></a>
														<?php }else if( $item['head_count'] == 0 ){ ?>
														<span>[无团长0]</span>
														<?php } ?>
													</td>
													<td>
														<?php echo date("Y-m-d H:i:s",$item['begin_time']);?>
														<br/>
														<?php echo date("Y-m-d H:i:s",$item['end_time']);?>
														<br/>
														<?php if($item['grounding']==1){ ?>
														<?php if($item['end_time'] <= time() ){ ?>
														<span class="text-danger">活动已结束</span>
														<?php } ?>
														<?php if($item['begin_time'] <= time() && $item['end_time'] > time()){ ?>
														<span class="txt-primary">正在进行中</span>
														<?php } ?>
														<?php if($item['begin_time'] > time() ){ ?>
														<span class="text-danger">活动未开始</span>
														<?php } ?>
														<?php }else{ ?>
														<?php if($item['end_time'] <= time() ){ ?>
														<span class="text-danger">活动已结束</span>
														<?php } ?>
														<?php if($item['begin_time'] <= time() && $item['end_time'] > time()){ ?>
														<span class="text-danger">未上架</span>
														<?php } ?>
														<?php if($item['begin_time'] > time() ){ ?>
														<span class="text-danger">活动未开始</span>
														<?php } ?>
														<?php } ?>
													</td>
													<td >&yen;
														<?php if($item['hasoption']==1){ ?>
														<?php echo ($item['price_arr']['price']); ?> <?php if(isset($item['price_arr']['max_danprice'])){ ?>~&yen;<?php echo ($item['price_arr']['max_danprice']); } ?>
														<?php }else{ ?>
														<a href='javascript:;' data-toggle='ajaxEdit' data-href="<?php echo U('goods/change',array('type'=>'price','id'=>$item['id']));?>" >

															<?php echo ($item['price']); ?>
														</a>
														<?php } ?>

													</td>
													<td><?php echo ($item['seller_count']); ?></td>
													<td>
														<?php if($item['hasoption']==1){ ?>
														<?php echo ($item['total']); ?>
														<?php }else{ ?>
														<a href='javascript:;' data-toggle='ajaxEdit' data-href="<?php echo U('goods/change',array('type'=>'total','id'=>$item['id']));?>" >

															<span class="text-danger"><?php echo ($item['total']); ?></span>
														</a>
														<?php } ?>


														<?php if($open_redis_server == 1){ ?>
														<br/>
														redis库存<?php echo ($item['redis_total']); ?>
														<a href="<?php echo U('goods/show_logs', array('goods_id' => $item['id'] ));?>" target="_blank"></a>
														<?php } ?>
													</td>

													<td><?php echo ($item['day_salescount']); ?></td>
													<?php if($is_open_fullreduction == 1 && $is_fullreduce){ ?>
													<td >
														<?php if($item['supply_type'] == 1){ ?>
														商户不参与平台满减
														<?php }else{ ?>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="cmwsitch" data-href="<?php echo U('goods/change_cm',array('type'=>'is_take_fullreduction','id'=>$item['id']));?>" <?php if($item['is_take_fullreduction']==1){ ?>checked<?php }else{ } ?> lay-skin="switch">
														</label>
														<?php } ?>

													</td>
													<?php } ?>

													<td>
														<?php if($item['grounding']==4 || $item['grounding']==5){ ?>

														<?php if( defined('ROLE') && ROLE == 'agenter' && $is_open_shenhe == 1){ ?>
														<?php if($item['grounding']==4){ ?>等待审核<?php }else{ ?>拒绝审核<?php } ?>
														<?php }else{ ?>


														<?php if($item['grounding']==4){ ?>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="engroundingsitch" data-href="<?php echo U('goods/change',array('type'=>'grounding','id'=>$item['id']));?>" <?php if($item['grounding']==4){ }else{ } ?> lay-skin="switch" lay-text="审核通过|审核通过">
														</label>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="unengroundingsitch" data-href="<?php echo U('goods/change',array('type'=>'grounding','id'=>$item['id']));?>" <?php if($item['grounding']==4){ }else{ } ?> lay-skin="switch" lay-text="拒绝审核|拒绝审核">
														</label>
														<?php } ?>

														<?php if($item['grounding']==5){ ?>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name="" lay-filter="engroundingsitch" data-href="<?php echo U('goods/change',array('type'=>'grounding','id'=>$item['id']));?>" <?php if($item['grounding']==4){ }else{ } ?> lay-skin="switch" lay-text="审核通过|审核通过">
															<br/>&nbsp;拒绝审核
														</label>
														<?php } ?>
														<?php } ?>
														<?php }else{ ?>
														<?php if( defined('ROLE') && ROLE == 'agenter' && $is_open_shenhe == 1 && $is_updown ){ ?>
														<?php if($item['grounding']==1){ ?>上架<?php }else{ ?>下架<?php } ?>
														<?php }else{ ?>
														<?php if($is_updown == 1 ){ ?>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox"  name="" lay-filter="undowngroundingsitch" data-href="<?php echo U('goods/change',array('type'=>'grounding','id'=>$item['id']));?>" <?php if($item['grounding']==1){ ?>checked<?php }else{ } ?> lay-skin="switch" >
														</label>
														<?php } ?>
														<?php } ?>
														<?php } ?>
													</td>
													<?php if( defined('ROLE') && ROLE == 'agenter'){ ?>

													<?php if($is_top){ ?>

													<?php if($index_sort_method == 1){ ?>

													<?php }else{ ?>
													<td>
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name=""   lay-filter="is_index_showsitch" data-href="<?php echo U('goods/change',array('type'=>'is_index_show','id'=>$item['id']));?>" <?php if($item['is_index_show']==1){ ?>checked<?php }else{ } ?> lay-skin="switch" >
														</label>

													</td>
													<?php } ?>

													<?php } ?>

													<?php  }else{ ?>

													<?php if($is_top){ ?>

													<?php if($index_sort_method == 1){ ?>
													<td style="text-align:center;">
														<a href='javascript:;' data-toggle='ajaxEdit' data-href="<?php echo U('goods/change',array('type'=>'index_sort','id'=>$item['id']));?>" >
															<span class="text-danger"><?php echo ($item['index_sort']); ?></span>
														</a>
													</td>
													<?php }else{ ?>
													<td >
														<label class="switch" style="margin-bottom: 0">
															<input type="checkbox" name=""  lay-filter="istop_showsitch" data-href="<?php echo U('goods/settop',array('type'=>'istop','id'=>$item['id']));?>" <?php if($item['istop']==1){ ?>checked<?php }else{ } ?> lay-skin="switch" >
														</label>
													</td>
													<?php } ?>
													<?php } ?>
													<?php } ?>

													<?php if($is_index){ ?>
													<td >
														<label class="switch" style="margin-bottom: 0">
														<input type="checkbox" name="" lay-filter="is_index_showsitch" data-href="<?php echo U('goods/change',array('type'=>'is_index_show','id'=>$item['id']));?>" <?php if( $item['is_index_show']==1 ){ ?>checked<?php  }else{ } ?> lay-skin="switch" >
														</label>
													</td>
													<?php } ?>
													<td  style="overflow:visible;position:relative">

														<a class="btn btn-primary btn-xs" href="<?php echo U('goods/edit', array('id' => $item['id'],'ok'=>1,'page'=>$page));?>"  >
															编辑
														</a>
														<?php if($type!='recycle'){ ?>
														<a class="btn btn-primary btn-xs deldom" href="javascript:;" data-href="<?php echo U('goods/change',array('id' => $item['id'],'type'=>'grounding','value'=>3));?>" data-confirm='确认要删除吗，删除后商品将进入回收站?'>
															删除
														</a>
														<?php } ?>
														<?php if($type=='recycle'){ ?>
														<a class="btn btn-primary btn-xs deldom" href="javascript:;" data-href="<?php echo U('goods/delete', array('id' => $item['id']));?>" data-confirm='确认要彻底删除吗?'>
															彻底删除
														</a>
														<?php } ?>
														<a class="btn btn-primary btn-xs js-clip"href="javascript:;"  data-url="/eaterplanet_ecommerce/pages/goods/goodsDetail?id=<?php echo ($item['id']); ?>">小程序链接</a>
														<a class="btn btn-primary btn-xs copy" href="javascript:;" data-href="<?php echo U('goods/copy',array('id' => $item['id']));?>" data-confirm='确认复制商品吗?'>
															复制商品
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
												<?php if($is_index){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch'  data-href="<?php echo U('goods/change',array('type'=>'is_index_show', 'value' => 1));?>">首页推荐</button>
												<?php } ?>
												<?php if($is_updown){ ?>
												<?php if( defined('ROLE') && ROLE == 'agenter'){ ?>
												<?php if($is_supply_add_goods_shenhe){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch' data-href="<?php echo U('goods/change',array('type'=>'grounding','value'=>1));?>">上架</button>
												<?php } ?>
												<?php }else{ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch' data-href="<?php echo U('goods/change',array('type'=>'grounding','value'=>1));?>">上架</button>
												<?php } ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch'  data-href="<?php echo U('goods/change',array('type'=>'grounding','value'=>0));?>">下架</button>
												<?php } ?>
												<?php if($is_open_fullreduction == 1 && $is_fullreduce ){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch' data-href="<?php echo U('goods/change_cm',array('type'=>'is_take_fullreduction','value'=>1));?>">参加满减</button>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch'  data-href="<?php echo U('goods/change_cm',array('type'=>'is_take_fullreduction','value'=>0));?>">不参加满减</button>
												<?php } ?>

												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-group'  id="batchcatesbut" >商品分类</button>
												<?php if( defined('ROLE') && ROLE == 'agenter'){ ?>
												<?php if($is_distributionsale){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation"  type="button" data-toggle='batch-group'  id="batch_head" >分配售卖团长</button>
												<button class="btn btn-pill btn-primary btn-sm btn-operation"  type="button" data-toggle='batch-group'  id="batch_head_group" >分配售卖团长分组</button>
												<?php  }else{ ?>
												<?php } ?>
												<?php  }else{ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation"  type="button" data-toggle='batch-group'  id="batch_head" >分配售卖团长</button>
												<button class="btn btn-pill btn-primary btn-sm btn-operation"  type="button" data-toggle='batch-group'  id="batch_head_group" >分配售卖团长分组</button>
												<?php } ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-group'  id="batchtime" >设置活动时间</button>

												<?php if($is_index){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch'  data-href="<?php echo U('goods/change',array('type'=>'is_index_show', 'value' => 0));?>">取消首页推荐</button>
												<?php } ?>

												<?php if($type!='recycle'){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要删除吗？删除后商品将进入回收站。" data-href="<?php echo U('goods/change',array('type'=>'grounding','value'=>3));?>">删除</button>
												<?php } ?>
												<?php if($type=='recycle'){ ?>
												<button class="btn btn-pill btn-primary btn-sm btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要彻底删除吗?" data-href="<?php echo U('goods/delete');?>">彻底删除</button>
												<?php } ?>
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


<div id="batchcates_html" style="display:none;">
	<div class="form-group row">
		<div class="col-sm-10">
			<div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">

				<input id="radioinline3" type="radio" name="iscover" value="0" <?php if($iscover ==0){ ?> checked="checked"<?php } ?>>
				<label class="mb-0" for="radioinline3">保留原有分类</label>


				<input id="radioinline4" type="radio" name="iscover" value="1" <?php if($iscover ==1){ ?> checked="checked"<?php } ?>>
				<label class="mb-0" for="radioinline4">覆盖原有分类</label>

			</div>

		</div>
	</div>
	<div class="form-group row">
		<label class="col-sm-2 col-form-label">商品分类</label>
		<div class="col-sm-10">
			<select id="cates2" lay-verify="cates_sel" name='cates' class="form-control " style='' >
				<?php foreach( $category as $c ){ ?>
				<option value="<?php echo ($c['id']); ?>" <?php if(is_array($cates) && in_array($c['id'],$cates)){ ?>selected<?php } ?> ><?php echo ($c['name']); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>

	<div class="modal-footer">

		<button class="btn btn-primary modal-fenlei">确认</button>
		<button class="btn btn-secondary cancle" >取消</button>

	</div>
</div>

<div id="batchcates_headgroup_html" style="display:none;">
	<div class="modal-body" >
		<div class="form-group row">
			<label class="col-sm-3 col-form-label">团长分组</label>
			<div class="col-sm-9">
				<select id="group_heads" lay-verify="group_heads" name='group_heads' class="form-control " style='' >
					<?php foreach( $group_list as $c ){ ?>
					<option value="<?php echo ($c['id']); ?>" <?php if(is_array($cates) && in_array($c['id'],$cates)){ ?>selected<?php } ?> ><?php echo ($c['groupname']); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label">仅这个分组可售</label>
			<div class="col-sm-9 m-t-10">
				<input type="checkbox" class="is_cancle_old2" name="is_cancle_old2" id="is_cancle_old2" >
				<div class="btn-group m-r-20">取消以往所有分配</div>
				<input type="checkbox" class="is_cancle_allhead2" id="is_cancle_allhead2" name="is_cancle_allhead2">
				<div class="btn-group">取消商品“所有团长可售”</div>
				<div style="display: none;" id="all_headgroup_tip">所有商品中包含“所有团长可售”商品，建议取消以免设置失效</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary modal-group-head" type="button">确认</button>
		<button class="btn btn-secondary cancle" type="button">取消</button>
	</div>
</div>


<div id="batchheads" style="z-index: 999;display: none;position: fixed;top: 0;left: 0;right: 0;bottom: 0;background: rgba(0,0,0,0.5)" class="form-horizontal form-validate batchcates"  >
	<div class="modal-dialog modal-dialog-centered btn-showcase" style="max-width: 800px;">
		<div class="modal-content" >
			<div class="modal-header">
				<h5 class="modal-title">选取团长</h5>
				<button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="form-group row">
					<label class="col-sm-2 col-form-label">社区位置</label>
					<div class="col-sm-10">
						<select id="sel-provance" name="province_id" onChange="selectCity();" class="select form-control" style="width:135px;display:inline;">
							<option value="" selected="true">省/直辖市</option>
						</select>
						<select id="sel-city" name="city_id" onChange="selectcounty(0)" class="select form-control" style="width:135px;display:inline;">
							<option value="" selected="true">请选择</option>
						</select>
						<select id="sel-area" name="area_id" onChange="selectstreet(0)" class="select form-control" style="width:135px;display:inline;">
							<option value="" selected="true">请选择</option>
						</select>
						<select id="sel-street" name="country_id" class="select form-control" style="width:150px;display:inline;">
							<option value="" selected="true">请选择</option>
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-2 col-form-label">团长名称</label>
					<div class="col-sm-10">
						<div class="input-group">
							<input type="text" class="form-control" name="keyword" id="supply_id_input" placeholder="团长名称/团长手机号/社区地址">
							<div class="input-group-append">
								<button type="button" class="btn btn-primary" onclick="search_heads()">搜索</button>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group row m-l-5">
					<div class="col-sm-12">
						<div class="page-table-header">
							<input type="checkbox" class="check_heads_all" style="vertical-align: text-bottom;">
							<div class="btn-group m-r-20">全选/反选</div>
							<input type="checkbox" class="is_cancle_old" id="is_cancle_old" style="vertical-align: text-bottom;">
							<div class="btn-group m-r-20">同时取消以前所有分配</div>
							<input type="checkbox" class="is_cancle_allhead" id="is_cancle_allhead" style="vertical-align: text-bottom;">
							<div class="btn-group m-r-20">同时取消商品 “所有团长可售”</div>
						</div>
						<div class="all_heads_tip" style="display: none;">
							<span class="text-warning">所有商品中包含“所有团长可售”商品，建议取消以免设置失效</span>
						</div>
					</div>
				</div>

				<div class="form-group row b-t-light m-b-0">
					<div class="col-sm-12 col-xs-12">
						<div class="content m-t-10"  data-name="supply_id">
							<div style="max-height:400px;overflow:auto;" class="custom-scrollbar" id="batchheads_content">

							</div>
							<div id="batchheads_page">

							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary model_heads">确认</button>
				<button class="btn btn-secondary" >取消</button>
			</div>
		</div>
	</div>
</div>

<div id="excel_goods_edit" style="display:none;">
	<form action="<?php echo U('goods/excel_goodslist_edit');?>" method="post"  enctype="multipart/form-data" >
		<div class="layui-card">
			<div class="layui-card-body">
				<div class="modal-body" >
					<div class="form-group row">
						<label class="col-sm-2 col-form-label">excel文件</label>
						<div class="col-sm-10">
							<input type="file" name="excel">
						</div>
					</div>

				</div>
			</div>
		</div>
	</form>
</div>


<div id="batch_time" style="z-index: 8;display: none;position: fixed;top: 0;left: 0;right: 0;bottom: 0;background: rgba(0,0,0,0.5)" class="form-horizontal form-validate batchtime"  enctype="multipart/form-data">

	<div class="modal-dialog modal-dialog-centered btn-showcase" style="min-width: 550px">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">设置活动时间</h5>
				<button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="form-group row m-b-0">
					<label class="col-sm-3 col-form-label">活动时间</label>
					<div class="col-sm-9">
						<?php echo tpl_form_field_daterange('setsametime', array('starttime'=>date('Y-m-d H:i', $starttime),'endtime'=>date('Y-m-d H:i', $endtime)),true);;?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary modal-time">确认</button>
				<button class="btn btn-secondary cancle" >取消</button>
			</div>
		</div>
	</div>
</div>

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

<script src="/layuiadmin/layui/layui1.js"></script>

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

		$('.exceledit').click(function(){
			//页面层
			layer.open({
				type: 1,
				title:'excel导入编辑商品',
				area: ['520px', '240px'], //宽高
				content: $('#excel_goods_edit'),
				btn:['提交','取消'],
				btn1:function(){
					$('#excel_goods_edit').find('form').submit();
				}
			});
		})

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
		//copy_goods
		$('.copy').click(function(){
			var s_url = $(this).attr('data-href');
			index = layer.confirm($(this).attr('data-confirm'), function(index){
				layer.close(index);
				$.ajax({
					url:s_url,
					type:'post',
					dataType:'json',
					success:function(res){
						layer.msg('复制成功',{
							time:1000
							,end:function(){
								location.href = res.url;
							}

						});

					},error:function(XMLHttpRequest, textStatus, errorThrown){
						alert(XMLHttpRequest.status);
						alert(XMLHttpRequest.readyState);
						alert(textStatus);
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
				return false;
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
									layer.msg(info.result.message,{time: 1000,
										end:function(){
											location.href = info.result.url;
										}
									});

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
								layer.msg(info.result.message,{time: 1000,
									end:function(){
										location.href = info.result.url;
									}
								});
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



		form.on('switch(cmwsitch)', function(data){

			var s_url = $(this).attr('data-href')

			var is_take_fullreduction = 1;
			if(data.elem.checked)
			{
				is_take_fullreduction = 1;
			}else{
				is_take_fullreduction = 0;
			}

			$.ajax({
				url:s_url,
				type:'post',
				dataType:'json',
				data:{value:is_take_fullreduction},
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

		form.on('switch(groundingsitch)', function(data){

			var s_url = $(this).attr('data-href')

			var grounding = 1;
			if(data.elem.checked)
			{
				grounding = 1;
			}else{
				grounding = 0;
			}

			$.ajax({
				url:s_url,
				type:'post',
				dataType:'json',
				data:{value:grounding},
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



		form.on('switch(unengroundingsitch)', function(data){

			var s_url = $(this).attr('data-href')

			var grounding = 1;
			if(data.elem.checked)
			{
				grounding = 5;
			}

			$.ajax({
				url:s_url,
				type:'post',
				dataType:'json',
				data:{value:grounding},
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
		form.on('switch(engroundingsitch)', function(data){

			var s_url = $(this).attr('data-href')

			var grounding = 1;
			if(data.elem.checked)
			{
				grounding = 1;
			}else{
				grounding = 5;
			}

			$.ajax({
				url:s_url,
				type:'post',
				dataType:'json',
				data:{value:grounding},
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


		form.on('switch(undowngroundingsitch)', function(data){

			var s_url = $(this).attr('data-href')

			var grounding = 1;
			if(data.elem.checked)
			{
				grounding = 1;
			}else{
				grounding = 0;
			}

			$.ajax({
				url:s_url,
				type:'post',
				dataType:'json',
				data:{value:grounding},
				success:function(info){

					if(info.status == 0)
					{
						layer.msg(info.result.message,{time: 1000,
							end:function(){
								location.href = info.result.url;
							}
						});

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


		form.on('switch(is_index_showsitch)', function(data){

			var s_url = $(this).attr('data-href')

			var is_index_show = 1;
			if(data.elem.checked)
			{
				is_index_show = 1;
			}else{
				is_index_show = 0;
			}

			$.ajax({
				url:s_url,
				type:'post',
				dataType:'json',
				data:{value:is_index_show},
				success:function(info){

					if(info.status == 0)
					{
						layer.msg(info.result.message,{time: 1000,
							end:function(){
								location.href = info.result.url;
							}
						});
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



		form.on('switch(istop_showsitch)', function(data){

			var s_url = $(this).attr('data-href')

			var istop = 1;
			if(data.elem.checked)
			{
				istop = 1;
			}else{
				istop = 0;
			}

			$.ajax({
				url:s_url,
				type:'post',
				dataType:'json',
				data:{value:istop},
				success:function(info){

					if(info.status == 0)
					{
						layer.msg(info.result.message,{time: 1000,
							end:function(){
								location.href = info.result.url;
							}
						});
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
						layer.msg(info.result.message,{time: 1000,
							end:function(){
								location.href = info.result.url;
							}
						});
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
<script type="text/javascript" src="/assets/js/dist/area/cascade.js"></script>
<script>
	var heads_page = 1;

	$("body").delegate("#batchheads_page .pagination a","click",function(){
		heads_page = $(this).attr('page');
		search_heads_do();
	})
	function search_heads()
	{
		heads_page = 1;
		search_heads_do();
	}
	function search_heads_do()
	{
		var province_name = $('#sel-provance').val();
		var city_name = $('#sel-city').val();
		var area_name = $('#sel-area').val();
		var country_name = $('#sel-street').val();
		var keyword = $('#supply_id_input').val();

		$.post("<?php echo U('communityhead/query_head');?>",{page:heads_page,'province_name':province_name,'city_name': city_name,'area_name':area_name,'country_name':country_name,'keyword':keyword},
				function (ret) {
					if (ret.status == 1) {
						$('#batchheads_content').html(ret.html);
						$('#batchheads_page').html(ret.page_html);
						return
					} else {
						layer.msg('修改失败');
					}
				}, 'json');
	}
	//显示批量分类
	$('#batchcatesbut').click(function () {
		//  var index = layer.load(1);
		var index = layer.open({
			type: 1,
			area: '600px',
			title: '选取分类'
			,content: $('#batchcates_html').html(),
			yes: function(index, layero){
				//do something
				layer.close(index); //如果设定了yes回调，需进行手工关闭
			}
		});
	})

	$('#batch_head_group').click(function () {
		//  var index = layer.load(1);
		var index = layer.open({
			type: 1,
			area: '700px',
			title: '选取团长分组'
			,content: $('#batchcates_headgroup_html').html(),
			yes: function(index, layero){
				//do something
				layer.close(index); //如果设定了yes回调，需进行手工关闭
			}
		});
	})

	$('#batch_head_group2').click(function () {
		//  var index = layer.load(1);
		var index = layer.open({
			type: 1,
			area: '700px',
			title: '选取团长分组'
			,content: $('#batchcates_headgroup_html').html(),
			yes: function(index, layero){
				//do something
				layer.close(index); //如果设定了yes回调，需进行手工关闭
			}
		});
	})




	$('.check_heads_all').click(function(){
		//head_id
		if($(this).is(':checked')){
			$('.head_id').prop('checked',true);
		}else{
			$('.head_id').prop('checked',false);
		}
	})
	$('#batch_head,#batch_head2').click(function(){

		cascdeInit("1","1","","","","");
		search_heads_do();


		var offs_lf = ( $(window).width() -720 )/2;
		var offs_ht = ( $(window).height() -690 )/2;


		$('#batchheads .modal-dialog').css('top','0px');
		$('#batchheads .modal-dialog').css('margin-top','0px');

		$('#batchheads .modal-dialog').css('left',offs_lf+'px');
		$('#batchheads .modal-dialog').css('margin-left','0px');

		$('#batchheads').show();
		var is_tip_notice = 0;
		var selected_checkboxs = $('.table-responsive tbody tr td:first-child [type="checkbox"]:checked');
		var goodsids = selected_checkboxs.map(function () {
			if( $(this).attr('data_is_all_sale') == 1 )
			{
				is_tip_notice = 1;
			}
			return $(this).val()
		}).get();
		if(is_tip_notice == 1){
			$('#batchheads .all_heads_tip').show();
		}else{
			$('#batchheads .all_heads_tip').hide();
		}
	})


	$('#batchcatesbut2').click(function () {
		var index = layer.open({
			type: 1,
			area: '700px',
			title: '选取分类'
			,content: $('#batchcates_html').html(),
			yes: function(index, layero){
				//do something
				layer.close(index); //如果设定了yes回调，需进行手工关闭
			}
		});
	})

	//关闭批量分类
	$('.modal-header .close').click(function () {
		$('#batchcates').hide();
		$('#batchheads').hide();
		$('#batch_time').hide();

	})

	// 取消批量分类
	$('.modal-footer .btn.btn-secondary').click(function () {
		$('#batchcates').hide();
		$('#batchheads').hide();
		$('#batch_time').hide();
	})

	$('.model_heads').click(function(){
		var head_id_arr = [];
		$('.head_id').each(function(){
			if($(this).is(':checked')) {
				head_id_arr.push( $(this).val() )
			}
		})
		//modal-group-head
		var is_clear_old = 0;

		if( $('#is_cancle_old').is(':checked') )
		{
			is_clear_old = 1;
		}
		var is_cancle_allhead2 = 0;

		if( $('#is_cancle_allhead').is(':checked') )
		{
			is_cancle_allhead2 = 1;
		}

		//is_cancle_allhead2
		if(head_id_arr.length > 0)
		{
			var is_tip_notice = 0;
			var selected_checkboxs = $('.table-responsive tbody tr td:first-child [type="checkbox"]:checked');
			var goodsids = selected_checkboxs.map(function () {
				if( $(this).attr('data_is_all_sale') == 1 )
				{
					is_tip_notice = 1;
				}
				return $(this).val()
			}).get();

			if(is_tip_notice  == 1)
			{
				layer.confirm('分配售卖团中的商品中有部分商品开启了“<span style="color:red;">所有团长可售</span>”，请确认是否已经取消了“所有团长可售”，未取消的情况下部分商品分配设置可能无效！', function(index){
					$.post("<?php echo U('goods/ajax_batchheads');?>",{'goodsids':goodsids,'head_id_arr': head_id_arr,'is_cancle_allhead':is_cancle_allhead2,'is_clear_old':is_clear_old}, function (ret) {
						if (ret.status == 1) {
							$('#batchheads').hide();
							layer.msg('分配成功', {
								time: 1000
							}, function(){
								window.location.reload();
							});

							return
						} else {
							layer.msg('修改失败');
						}
					}, 'json');
				});
			}else{

				$.post("<?php echo U('goods/ajax_batchheads');?>",{'goodsids':goodsids,'head_id_arr': head_id_arr,'is_cancle_allhead':is_cancle_allhead2,'is_clear_old':is_clear_old}, function (ret) {
					if (ret.status == 1) {
						$('#batchheads').hide();

						layer.msg('分配成功', {
							time: 1000
						}, function(){
							window.location.reload();
						});

						return
					} else {
						layer.msg('修改失败');
					}
				}, 'json');
			}
		}else{
			layer.msg('请选择团长');
		}
	})
	//确认
	var cates2 = 0;
	$("body").delegate("#cates2","click",function(){

		cates2 =  $(this).val() ;
	})

	var group_heads2 = 'default';
	$("body").delegate("#group_heads","click",function(){
		group_heads2 =  $(this).val() ;
	})


	$("body").delegate(".cancle","click",function(){
		layer.closeAll();
	})



	$("body").delegate(".modal-group-head","click",function(){

		var group_heads=$('#group_heads').val();
		if(group_heads2 != 'default')
		{
			group_heads = group_heads2;
		}
		var is_tip_notice = 0;
		var selected_checkboxs = $('.table-responsive tbody tr td:first-child [type="checkbox"]:checked');
		var goodsids = selected_checkboxs.map(function () {
			if( $(this).attr('data_is_all_sale') == 1 )
			{
				is_tip_notice = 1;
			}
			return $(this).val()
		}).get();

		if(goodsids.length <=0 )
		{
			layer.msg('请先选择商品');
			return false;
		}

		var is_clear_old = 0;

		$('.is_cancle_old2').each(function(){
			if( $(this).is(':checked') )
			{
				is_clear_old = 1;
			}
		})

		var is_cancle_allhead = 0;

		$('.is_cancle_allhead2').each(function(){
			if( $(this).is(':checked') )
			{
				is_cancle_allhead = 1;
			}
		})


		var iscover=$('input[name="iscover"]:checked').val();
		if(is_tip_notice == 1){
			layer.confirm('分配售卖团长分组中的商品中有部分商品开启了“<span style="color:red;">所有团长可售</span>”，请确认是否已经取消了“所有团长可售”，未取消的情况下部分商品分配设置可能无效！', function(index){
				$.post("<?php echo U('goods/ajax_batchcates_headgroup');?>",{'goodsids':goodsids,'groupid': group_heads,'is_cancle_allhead':is_cancle_allhead,'is_clear_old' : is_clear_old }, function (ret) {			if (ret.status == 1) {

				layer.msg('分配成功', {
					time: 1000
				}, function(){
					window.location.reload();
				});

					return
				} else {
					layer.msg('分配失败');
				}
				}, 'json');
			});
		}else{
			$.post("<?php echo U('goods/ajax_batchcates_headgroup');?>",{'goodsids':goodsids,'groupid': group_heads,'is_cancle_allhead':is_cancle_allhead,'is_clear_old' : is_clear_old }, function (ret) {
				if (ret.status == 1) {

					layer.msg('分配成功', {
						time: 1000
					}, function(){
						window.location.reload();
					});

					return
				} else {
					layer.msg('分配失败');
				}
			}, 'json');
		}
	})

	$('.layui-table-sort').click(function(){
		$(this).attr('lay-sort','asc');
	})

	$("body").delegate(".modal-fenlei","click",function(){

		var cates=$('#cates2').val();
		if(cates2 != 0)
		{
			cates = cates2;
		}


		var selected_checkboxs = $('.table-responsive tbody tr td:first-child [type="checkbox"]:checked');
		var goodsids = selected_checkboxs.map(function () {
			return $(this).val()
		}).get();
		//id="cates"
		var iscover=$('input[name="iscover"]:checked').val();
		$.post("<?php echo U('goods/ajax_batchcates');?>",{'goodsids':goodsids,'cates': cates,'iscover':iscover}, function (ret) {
			if (ret.status == 1) {
				$('#batchcates').hide();

				layer.msg('修改成功', {
					time: 1000
				}, function(){
					window.location.reload();
				});

				return
			} else {
				layer.msg('修改失败');
			}
		}, 'json');
	})

	//显示时间设置
	$('#batchtime,#batchtime2').click(function () {


		var offs_lf = ( $(window).width() -540 )/2;
		var offs_ht = ( $(window).height() -690 )/2;


		$('#batch_time .modal-dialog').css('top','0px');
		$('#batch_time .modal-dialog').css('margin-top','0px');

		$('#batch_time .modal-dialog').css('left',offs_lf+'px');
		$('#batch_time .modal-dialog').css('margin-left','0px');



		$('#batch_time').show();
	})

	$('.modal-time').click(function () {
		var selected_checkboxs = $('.table-responsive tbody tr td:first-child [type="checkbox"]:checked');
		var goodsids = selected_checkboxs.map(function () {
			return $(this).val()
		}).get();

		var begin_time=$('#batch_time input[name="setsametime[start]"]').val();
		var end_time=$('#batch_time input[name="setsametime[end]"]').val();
		$.post("<?php echo U('goods/ajax_batchtime');?>",{'goodsids':goodsids,'begin_time': begin_time,'end_time':end_time}, function (ret) {
			if (ret.status == 1) {
				$('#batch_time').hide();
				layer.msg('设置成功');
				window.location.reload();
				return
			} else {
				layer.msg('设置失败');
			}
		}, 'json');
	})


	$(document).on("click", '[data-toggle="ajaxEdit"]', function(e) {
		var obj = $(this),
				url = obj.data('href') || obj.attr('href'),
				data = obj.data('set') || {},
				html = $.trim(obj.text()),
				required = obj.data('required') || true,
				edit = obj.data('edit') || 'input';
		var oldval = $.trim($(this).text());
		e.preventDefault();
		submit = function() {
			e.preventDefault();
			var val = $.trim(input.val());
			if (required) {
				if (val == '') {
					layer.msg(tip.lang.empty);
					return
				}
			}
			if (val == html) {
				input.remove(), obj.html(val).show();
				return
			}
			if (url) {
				$.post(url, {
					value: val
				}, function(ret) {
					ret = eval("(" + ret + ")");
					if (ret.status == 1) {
						obj.html(val).show()
					} else {
						layer.msg(ret.result.message, ret.result.url)
					}
					input.remove();
					location.reload();
				}).fail(function() {
					input.remove(),  layer.msg(tip.lang.exception)
				})
			} else {
				input.remove();
				obj.html(val).show()
			}
			obj.trigger('valueChange', [val, oldval])
		}, obj.hide().html('<i class="fa fa-spinner fa-spin"></i>');
		var input = $('<input type="text" class="form-control input-sm" style="width: 80%;display: inline;" />');
		if (edit == 'textarea') {
			input = $('<textarea type="text" class="form-control" style="resize:none" rows=3 ></textarea>')
		}
		obj.after(input);
		input.val(html).select().blur(function() {
			submit(input)
		}).keypress(function(e) {
			if (e.which == 13) {
				submit(input)
			}
		})
	})

</script>
</body>