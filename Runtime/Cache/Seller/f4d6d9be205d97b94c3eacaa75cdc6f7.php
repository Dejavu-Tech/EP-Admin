<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<?php $shoname_name = D('Home/Front')->get_config_by_name('shoname'); ?>
	<title><?php echo $shoname; ?></title>
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
	<link rel="stylesheet" type="text/css" href="/assets/css/simple-line-icon.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/chartist.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/owlcarousel.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/tour.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/chartist.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/date-picker.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/prism.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/material-design-icon.css">
<link rel="stylesheet" type="text/css" href="/assets/css/datatables.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/pe7-icon.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/ionic-icon.css">
	<!-- Plugins css Ends-->
	<!-- Bootstrap css-->
	<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.css">
	<!-- App css-->
	<link rel="stylesheet" type="text/css" href="/assets/css/style.css">
	<link id="color" rel="stylesheet" href="/assets/css/color-1.css" media="screen">
	<!-- Responsive css-->
	<link rel="stylesheet" type="text/css" href="/assets/css/responsive.css">
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
							<h2>概况<span>统计</span></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid general-widget">
				<div class="row">
					<?php if (!defined('ROLE') || ROLE != 'agenter' ) {?>
					<?php if(D('Seller/Menu')->check_seller_perm('user/index')){ ?>
					<div class="col-xl-3 xl-50 col-md-6 box-col-6">
						<div class="card gradient-primary o-hidden" >
							<div class="card-body b-r-4">
								<div class="media static-top-widget">
									<div class="align-self-center text-center"><i data-feather="user-plus"></i></div>
									<a href="<?php echo U('user/index'); ?>">
										<div class="media-body">
											<span class="m-0 text-white"><h6>今日新增会员</h6></span>
											<h5 class="text-white num today_member_count"></h5><i class="icon-bg" data-feather="user-plus"></i>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
					<?php } ?>

					<?php if(D('Seller/Menu')->check_seller_perm('order/index')){ ?>
					<div class="col-xl-3 xl-50 col-md-6 box-col-6">
						<div class="card gradient-secondary o-hidden">
							<div class="card-body b-r-4">
								<div class="media static-top-widget">
									<div class="align-self-center text-center"><i data-feather="archive"></i></div>
									<a href="<?php echo U('order/index'); ?>">
										<div class="media-body"><span class="m-0 text-white"><h6>今日订单数</h6></span>
											<h5 class="text-white num today_pay_order_count"></h5><i class="icon-bg" data-feather="archive"></i>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>

					<?php } ?>
					<?php if(D('Seller/Menu')->check_seller_perm('order/index')){ ?>
					<div class="col-xl-3 xl-50 col-md-6 box-col-6">
						<div class="card gradient-warning o-hidden">
							<div class="card-body b-r-4">
								<div class="media static-top-widget">
									<div class="text-white align-self-center text-center"><i data-feather="dollar-sign"></i></div>
									<a href="<?php echo U('order/index'); ?>">
										<div class="media-body"><span class="m-0 text-white"><h6>今日销售额</h6></span>
											<h5 class="text-white num today_pay_money"></h5><i class="icon-bg" data-feather="dollar-sign"></i>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>

					<?php if (!defined('ROLE') || ROLE != 'agenter' ) {?>
					<div class="col-xl-3 xl-50 col-md-6 box-col-6" style="display: none">
						<div class="card gradient-info o-hidden">
							<div class="card-body b-r-4">
								<div class="media static-top-widget">
									<div class="align-self-center text-center"><i data-feather="pocket"></i></div>
									<a href="<?php echo U('communityhead/distribulist'); ?>">
										<div class="media-body"><span class="m-0 text-white"><h6>今日佣金提现</h6></span>
											<h5 class="text-white num total_tixian_money"></h5><i class="icon-bg" data-feather="pocket"></i>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-3 xl-50 col-md-6 box-col-6" style="display: none">
						<div class="card gradient-success o-hidden">
							<div class="card-body b-r-4">
								<div class="media static-top-widget">
									<div class="align-self-center text-center"><i data-feather="users"></i></div>
									<a title="团长总佣金=待结算佣金+已结算佣金" href="<?php echo U('communityhead/distribulist'); ?>">
										<div class="media-body"><span class="m-0 text-white"><h6>团长总佣金</h6></span>
											<h5 class="text-white num total_commiss_money"></h5><i class="icon-bg" data-feather="users"></i>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
					<div class="col-xl-3 xl-50 col-md-6 box-col-6">
						<div class="card gradient-info o-hidden">
							<div class="card-body b-r-4">
								<div class="media static-top-widget">
									<div class="text-white align-self-center text-center"><i data-feather="star"></i></div>
									<a href="<?php echo U('order/index'); ?>">
										<div class="media-body"><span class="m-0 text-white"><h6>订单总额</h6></span>
											<h5 class="text-white num total_order_money"></h5><i class="icon-bg" data-feather="star"></i>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
					<div class="row m-l-0 m-r-0">
						<div class="col-sm-12 col-md-2 col-xl-2 box-col-2">
							<div class="ribbon-wrapper card p-t-0 ">
								<div class="ribbon ribbon-primary ribbon-vertical-right "><i class="icofont icofont-love"></i></div>
								<div class="">
									<div class="card-header no-border" style="padding-bottom: 20px!important;"><h5 class="font-primary">常用功能</h5></div>

									<div class="card-body p-t-10 pl-10 p-b-40"  style="padding-left:10px;padding-right:10px">
										<?php if (!defined('ROLE') || ROLE != 'agenter' ) {?>
										<?php if(D('Seller/Menu')->check_seller_perm('goods/index')){ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('goods/addgoods'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700" style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">添加商品</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php if(D('Seller/Menu')->check_seller_perm('order/index')){ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('order/index'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700" style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">订单管理</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php if(D('Seller/Menu')->check_seller_perm('user/index')){ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('user/index'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">会员管理</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php if(D('Seller/Menu')->check_seller_perm('communityhead/index')){ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('communityhead/index'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">团长管理</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php if(D('Seller/Menu')->check_seller_perm('supply/index')){ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('supply/index'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">商户管理</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php if(D('Seller/Menu')->check_seller_perm('communityhead/distribulist')){ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('communityhead/distribulist'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">提现申请</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php if(D('Seller/Menu')->check_seller_perm('communityhead/distributionpostal')){ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('communityhead/distributionpostal'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">提现设置</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php if(D('Seller/Menu')->check_seller_perm('configindex/slider')){ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('configindex/slider'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">幻灯管理</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php if(D('Seller/Menu')->check_seller_perm('order/orderaftersales')){ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('order/orderaftersales'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">售后管理</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php if(D('Seller/Menu')->check_seller_perm('config/index')){ ?>
										<div class="btc-sell text-center" style="margin-left:-40px">
											<a href="<?php echo U('config/index'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">后台设置</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>

										<?php  }else{ ?>
										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('goods/addgoods'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">添加商品</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user"></i></button>
											</a>
										</div>

										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('order/index'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">订单管理</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>

										<div class="btc-sell text-center m-b-30" style="margin-left:-40px">
											<a href="<?php echo U('order/orderaftersales'); ?>">
												<button class="btn btn-pill btn-secondary font-white f-w-700 " style="padding-left:30px!important;padding-right:30px!important;font-size:18px ">售后管理</button>
												<button class="btn btn-pill font-secondary f-w-700" style="padding-right:20px!important "><i class="icon-user f-20"></i></button>
											</a>
										</div>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-10 col-xl-10 box-col-10 row m-l-0 m-r-0 p-0">
							<div class="col-xl-6 col-md-6 col-sm-12 box-col-6">
								<div class="card o-hidden card-bg">
									<div class="card-header no-border" style="padding-bottom: 30px!important;">
										<h5 class="font-primary">待处理事务</h5>

										<ul class="creative-dots">
											<li class="bg-primary big-dot"></li>
											<li class="bg-secondary semi-big-dot"></li>
											<li class="bg-warning medium-dot"></li>
											<li class="bg-info semi-medium-dot"></li>
											<li class="bg-secondary semi-small-dot"></li>
											<li class="bg-primary small-dot"></li>
										</ul>
									</div>
									<div class="row p-30 p-b-0 p-t-0">
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<?php if (!defined('ROLE') || ROLE != 'agenter' ) {?>
														<?php if(D('Seller/Menu')->check_seller_perm('goods/index')){ ?>
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('goods/index', array('type' => 'stock_notice')); ?>"> <h6 class="f-w-700 mb-0 default-text">库存预警</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num goods_stock_notice_count f-w-700 mb-0"></h4>
															</div>
														</div>
														<?php } ?>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<?php if(D('Seller/Menu')->check_seller_perm('order/index')){ ?>
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('order/index', array('order_status_id' => 3)); ?>"> <h6 class="f-w-700 mb-0 default-text">未付款订单</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num wait_pay_order_count f-w-700 mb-0"></h4>
															</div>
														</div>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<?php if(D('Seller/Menu')->check_seller_perm('order/index')){ ?>
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('order/index', array('order_status_id' => 1)); ?>"> <h6 class="f-w-700 mb-0 default-text">未发货订单</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num wait_order_count f-w-700 mb-0"></h4>
															</div>
														</div>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
										<?php if (!defined('ROLE') || ROLE != 'agenter' ) {?>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<?php if(D('Seller/Menu')->check_seller_perm('order/index')){ ?>
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('order/orderaftersales'); ?>"> <h6 class="f-w-700 mb-0 default-text">待处理退款</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num after_sale_order_count f-w-700 mb-0"></h4>
															</div>
														</div>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<?php if(D('Seller/Menu')->check_seller_perm('order/index')){ ?>
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('order/index', array('order_status_id' => 6)); ?>"> <h6 class="f-w-700 mb-0 default-text">待评价订单</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num  wai_comment_order_count f-w-700 mb-0"></h4>
															</div>
														</div>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<?php if(D('Seller/Menu')->check_seller_perm('order/index')){ ?>
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('order/ordercomment'); ?>"> <h6 class="f-w-700 mb-0 default-text">待审核评价</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num  wait_shen_order_comment_count f-w-700 mb-0"></h4>
															</div>
														</div>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<?php if(D('Seller/Menu')->check_seller_perm('goods/index')){ ?>
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('goods/index', array('type' => 'warehouse')); ?>"> <h6 class="f-w-700 mb-0 default-text">仓库中商品</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num stock_goods_count f-w-700 mb-0"></h4>
															</div>
														</div>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<?php if(D('Seller/Menu')->check_seller_perm('order/index')){ ?>
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('order/orderaftersales'); ?>"> <h6 class="f-w-700 mb-0 default-text">待处理退货</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num after_sale_order_count f-w-700 mb-0"></h4>
															</div>
														</div>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<?php if(D('Seller/Menu')->check_seller_perm('communityhead/index')){ ?>
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a href="<?php echo U('communityhead/distribulist'); ?>"> <h6 class="f-w-700 mb-0 default-text">待提现佣金</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num tixian_count f-w-700 mb-0"></h4>
															</div>
														</div>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
										<?php  }else{ ?>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('order/orderaftersales'); ?>"> <h6 class="f-w-700 mb-0 default-text">待处理退款</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num after_sale_order_count f-w-700 mb-0"></h4>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
											<div class="card o-hidden">
												<div class="cal-date-widget card-body p-0">
													<div class="row">
														<div class="col-xl-8 col-xs-8 col-md-8 col-sm-8 ">
															<div class="align-self-center text-center" style="padding:30px 0">
																<a  href="<?php echo U('order/orderaftersales'); ?>"> <h6 class="f-w-700 mb-0 default-text">待处理退货</h6></a>
															</div>
														</div>
														<div class="col-xl-4 col-xs-4 col-md-4 col-sm-4 gradient-primary p-l-0" >
															<div class=" align-self-center text-center" style="padding:25px 0">
																<h4 class="num after_sale_order_count f-w-700 mb-0"></h4>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<?php } ?>
									</div>
								</div>
							</div>
							<div class="col-xl-6 col-md-6 col-sm-12 box-col-6">
								<div class="card m-b-0">
									<div class="card-header no-border">
										<h5 class="font-primary">简略统计</h5>
										<ul class="creative-dots">
											<li class="bg-primary big-dot"></li>
											<li class="bg-secondary semi-big-dot"></li>
											<li class="bg-warning medium-dot"></li>
											<li class="bg-info semi-medium-dot"></li>
											<li class="bg-secondary semi-small-dot"></li>
											<li class="bg-primary small-dot"></li>
										</ul>
										<div class="card-header-right">
											<ul class="list-unstyled card-option">
												<li><i class="icofont icofont-gear fa fa-spin font-warning"></i></li>
												<li><i class="view-html fa fa-code font-warning"></i></li>
												<li><i class="icofont icofont-maximize full-card font-warning"></i></li>
												<li><i class="icofont icofont-minus minimize-card font-warning"></i></li>
												<li><i class="icofont icofont-refresh reload-card font-warning"></i></li>
												<li><i class="icofont icofont-error close-card font-warning"></i></li>
											</ul>
										</div>
									</div>
									<div class="card m-b-0">
										<div class="row">
											<?php if (!defined('ROLE') || ROLE != 'agenter' ) {?>
											<div class="col-sm-6 pr-0">
												<div class="media border-after-xs p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">总数</h6>
														<h3 class="txt-primary f-w-600 mb-0">会员</h3>
													</div>

													<div class="media-body text-right align-self-center">
														<h3 class="mb-0"><span class="num member_count"></span></h3>
													</div>
												</div>
											</div>
											<div class="col-sm-6 pl-0">
												<div class="media border-after-xs p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">总计</h6>
														<h3 class="txt-primary f-w-600 mb-0">商品数</h3>
													</div>
													<div class="media-body text-right align-self-center">
														<h3 class="mb-0"><span class="num goods_count"></span></h3>
													</div>
												</div>
											</div>
											<div class="col-sm-6 pr-0">
												<div class="media border-after-xs p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">总计</h6>
														<h3 class="txt-primary f-w-600 mb-0">团长数</h3>
													</div>
													<div class="media-body text-right align-self-center">
														<h3 class="mb-0"><span class="num community_head_count"></span></h3>
													</div>
												</div>
											</div>
											<div class="col-sm-6 pl-0 ">
												<div class="media p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">一周</h6>
														<h3 class="txt-primary f-w-600 mb-0">订单数</h3>
													</div>
													<div class="media-body text-right align-self-center">
														<h3 class="mb-0"><span class="num seven_order_count"></span></h3>
													</div>
												</div>
											</div>
											<div class="col-sm-6 pr-0">
												<div class="media border-after-xs p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">一周</h6>
														<h3 class="txt-primary f-w-600 mb-0">销售额</h3>
													</div>
													<div class="media-body text-right align-self-center">
														<h3 class="mb-0">¥<span class="num seven_pay_money"></span></h3>
													</div>
												</div>
											</div>
											<div class="col-sm-6 pl-0">
												<div class="media p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">一周</h6>
														<h3 class="txt-primary f-w-600 mb-0">退款额</h3>
													</div>
													<div class="media-body text-right align-self-center">
														<h3 class="mb-0">¥<span class="num seven_refund_money"></span></h3>
													</div>
												</div>
											</div>
											<?php  }else{ ?>
											<div class="col-sm-6 pl-0 ">
												<div class="media p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">一周</h6>
														<h3 class="txt-primary f-w-600 mb-0">订单数</h3>
													</div>
													<div class="media-body text-right align-self-center">
														<h3 class="mb-0"><span class="num seven_order_count"></span></h3>
													</div>
												</div>
											</div>
											<div class="col-sm-6 pr-0">
												<div class="media border-after-xs p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">一周</h6>
														<h3 class="txt-primary f-w-600 mb-0">销售额</h3>
													</div>
													<div class="media-body text-right align-self-center">
														<h3 class="mb-0">¥<span class="num seven_pay_money"></span></h3>
													</div>
												</div>
											</div>
											<div class="col-sm-6 pl-0">
												<div class="media p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">一周</h6>
														<h3 class="txt-primary f-w-600 mb-0">退款额</h3>
													</div>
													<div class="media-body text-right align-self-center">
														<h3 class="mb-0">¥<span class="num seven_refund_money"></span></h3>
													</div>
												</div>
											</div>
											<div class="col-sm-6 pl-0">
												<div class="media p-30">
													<div class="align-self-center mr-3 text-left">
														<h6 class="mb-1">商品</h6>
														<h3 class="txt-primary f-w-600 mb-0">总数</h3>
													</div>
													<div class="media-body text-right align-self-center">
														<h3 class="mb-0">¥<span class="num goods_count"></span></h3>
													</div>
												</div>
											</div>
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-4 col-md-4 col-sm-4 box-col-4">
								<div class="card gradient-primary o-hidden">
									<div class="p-30">
										<div class="setting-dot">
											<div class="setting-bg-primary date-picker-setting position-set pull-right"><i class="fa fa-spin fa-cog"></i></div>
										</div>
										<div class="default-datepicker">
											<div class="datepicker-here" data-language="zh_CN">
												<div class="datepicker-inline"><div class="datepicker"><i class="datepicker--pointer"></i><nav class="datepicker--nav"><div class="datepicker--nav-action" data-action="prev"><svg><path d="M 17,12 l -5,5 l 5,5"></path></svg></div><div class="datepicker--nav-action" data-action="next"><svg><path d="M 14,12 l 5,5 l -5,5"></path></svg></div></nav>						 </div><span class="default-dots-stay overview-dots full-width-dots"><span class="dots-group"><span class="dots dots1"></span><span class="dots dots2 dot-small"></span><span class="dots dots3 dot-small"></span><span class="dots dots4 dot-medium"></span><span class="dots dots5 dot-small"></span><span class="dots dots6 dot-small"></span><span class="dots dots7 dot-small-semi"></span><span class="dots dots8 dot-small-semi"></span><span class="dots dots9 dot-small">   </span></span></span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-8 col-md-8 col-sm-8 box-col-8">

								<div class="card year-overview">
									<div class="card-header no-border">
										<h5 class="font-primary">近十日会员增长</h5>
										<ul class="creative-dots">
											<li class="bg-primary big-dot"></li>
											<li class="bg-secondary semi-big-dot"></li>
											<li class="bg-warning medium-dot"></li>
											<li class="bg-info semi-medium-dot"></li>
											<li class="bg-secondary semi-small-dot"></li>
											<li class="bg-primary small-dot"></li>
										</ul>
										<div class="card-header-right">
											<ul class="list-unstyled card-option">
												<li><i class="icofont icofont-gear fa fa-spin font-warning"></i></li>
												<li><i class="view-html fa fa-code font-warning"></i></li>
												<li><i class="icofont icofont-maximize full-card font-warning"></i></li>
												<li><i class="icofont icofont-minus minimize-card font-warning"></i></li>
												<li><i class="icofont icofont-refresh reload-card font-warning"></i></li>
												<li><i class="icofont icofont-error close-card font-warning"></i></li>
											</ul>
										</div>
									</div>
									<div class="card-body pb-0 pt-0 pl-4">
										<div id="chartmember" ></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<?php if (!defined('ROLE') || ROLE != 'agenter' ) {?>
					<div class="col-xl-12 col-md-12 col-sm-12 box-col-12">
						<div class="card year-overview">
							<div class="card-header no-border pb-3">
								<h5 class="font-primary">近十日交易统计</h5>
								<ul class="creative-dots">
									<li class="bg-primary big-dot"></li>
									<li class="bg-secondary semi-big-dot"></li>
									<li class="bg-warning medium-dot"></li>
									<li class="bg-info semi-medium-dot"></li>
									<li class="bg-secondary semi-small-dot"></li>
									<li class="bg-primary small-dot"></li>
								</ul>
								<div class="card-header-right">
									<ul class="list-unstyled card-option">
										<li><i class="icofont icofont-gear fa fa-spin font-warning"></i></li>
										<li><i class="view-html fa fa-code font-warning"></i></li>
										<li><i class="icofont icofont-maximize full-card font-warning"></i></li>
										<li><i class="icofont icofont-minus minimize-card font-warning"></i></li>
										<li><i class="icofont icofont-refresh reload-card font-warning"></i></li>
										<li><i class="icofont icofont-error close-card font-warning"></i></li>
									</ul>
								</div>
							</div>
							<div class="card-body pb-0 pt-0 pl-5 pr-5">
								<div class="ibox-loading" id="echarts-line-chart-loading"></div>
								<div id="chartorder">

								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-5 col-md-5 col-sm-12 box-col-5">
						<div class="card year-overview">
							<div class="card-header no-border d-flex pb-3">
								<h5 class="font-primary">本月商品销售排行<span class="badge badge-pill pill-badge-secondary f-14 f-w-600 num month_count"><?php echo $date_month; ?></span></h5>
								<ul class="creative-dots">
									<li class="bg-primary big-dot"></li>
									<li class="bg-secondary semi-big-dot"></li>
									<li class="bg-warning medium-dot"></li>
									<li class="bg-info semi-medium-dot"></li>
									<li class="bg-secondary semi-small-dot"></li>
									<li class="bg-primary small-dot"></li>
								</ul>
								<div class="header-right pull-right text-right">
									<h5 class="mb-2" data-value="total">销售数量：<span class="num month_good_count"></span></h5>
									<h6 class="f-w-700 mb-0 default-text">销售金额：¥<span class="num month_good_money"></span></h6>
								</div>

							</div>
							<div class="card-body pb-0 pt-0">
								<div id="chartmonthgoods"></div>
							</div>
						</div>
					</div>
					<div class="col-xl-7 col-md-7 col-sm-12 box-col-7">
						<div class="card year-overview">
							<div class="card-header no-border">
								<h5 class="font-primary">本月团长销售排行</h5>
								<ul class="creative-dots">
									<li class="bg-primary big-dot"></li>
									<li class="bg-secondary semi-big-dot"></li>
									<li class="bg-warning medium-dot"></li>
									<li class="bg-info semi-medium-dot"></li>
									<li class="bg-secondary semi-small-dot"></li>
									<li class="bg-primary small-dot"></li>
								</ul>
								<div class="card-header-right">
									<ul class="list-unstyled card-option">
										<li><i class="icofont icofont-gear fa fa-spin font-warning"></i></li>
										<li><i class="view-html fa fa-code font-warning"></i></li>
										<li><i class="icofont icofont-maximize full-card font-warning"></i></li>
										<li><i class="icofont icofont-minus minimize-card font-warning"></i></li>
										<li><i class="icofont icofont-refresh reload-card font-warning"></i></li>
										<li><i class="icofont icofont-error close-card font-warning"></i></li>
									</ul>
								</div>
							</div>
							<div class="card-body pb-0 pt-0">
								<div id="echat_month_head_sales" style="height:460px;"></div>
							</div>
						</div>
					</div>

					<?php } ?>
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
<!-- feather icon js-->
<script src="/assets/js/icons/feather-icon/feather.min.js"></script>
<script src="/assets/js/icons/feather-icon/feather-icon.js"></script>
<!-- Sidebar jquery-->
<script src="/assets/js/sidebar-menu.js"></script>
<script src="/assets/js/config.js"></script>
<!-- Plugins JS start-->
<script src="/assets/js/chart/chartist/chartist.js"></script>
<script src="/assets/js/chart/chartist/chartist-plugin-tooltip.js"></script>
<script src="/assets/js/chart/apex-chart/apex-chart.js"></script>
<script src="/assets/js/chart/apex-chart/stock-prices.js"></script>
<script src="/assets/js/prism/prism.min.js"></script>
<script src="/assets/js/clipboard/clipboard.min.js"></script>
<script src="/assets/js/counter/jquery.counterup.min.js"></script>
<script src="/assets/js/counter/counter-custom.js"></script>
<script src="/assets/js/custom-card/custom-card.js"></script>
<script src="/assets/js/notify/bootstrap-notify.min.js"></script>
<script src="/assets/js/datepicker/date-picker/datepicker.js"></script>
<script src="/assets/js/datepicker/date-picker/datepicker.en.js"></script>
<script src="/assets/js/datepicker/date-picker/datepicker.custom.js"></script>
<script src="/assets/js/chat-menu.js"></script>
<script src="/assets/js/tooltip-init.js"></script>
<!-- Theme js-->
<script src="/assets/js/script.js"></script>
<script src="/assets/js/theme-customizer/customizer1.js"></script>
<script src="/layuiadmin/lib/extend/echarts.min.js"></script>
<script src="/assets/js/roll/jquery.waypoints.min.js"></script>
<script type="text/javascript" src="/assets/js/roll/jquery.countup.min.js"></script>
<!-- Theme js-->



<script>
	layui.config({
		base: '/layuiadmin/' //静态资源所在路径
	}).extend({
		index: 'lib/index' //主入口模块
	}).use('index');
</script>

<script>
 function overShow1() {
  var showDiv = document.getElementById('showDiv');
  //showDiv.style.left = event.clientX;
  //showDiv.style.top = event.clientY;
  showDiv.style.display = 'block';
  showDiv.innerHTML = '<div style="color:#FFF;margin-top:10px;">支付成功订单数：'+ <?php echo ($today_success_order_count); ?> +' </div><div style="color:#FFF;margin-top:10px;">支付取消订单数：'+ <?php echo ($today_cancel_order_count); ?> +'</div>';
 }

 function outHide1() {
  var showDiv = document.getElementById('showDiv');
  showDiv.style.display = 'none';
  showDiv.innerHTML = '';
 }
</script>

<script>
 function overShow2() {
  var showDiv = document.getElementById('showDiv2');
  //showDiv.style.left = event.clientX;
  //showDiv.style.top = event.clientY;
  showDiv.style.display = 'block';
  showDiv.innerHTML = '<div class="table"><div class="table-tr"><div class="table-td">余额付款：</div> <div class="table-td">¥'+ <?php echo ($yuer_pay_money); ?>+'</div>&nbsp&nbsp&nbsp&nbsp <div class="table-td">在线付款：</div><div class="table-td">¥'+ <?php echo ($online_pay_money); ?>+'</div></div><div class="table-tr"><div class="table-td">积分抵现：</div><div class="table-td">¥'+ <?php echo ($score_for_money); ?>+'</div>&nbsp&nbsp&nbsp&nbsp<div class="table-td">使用积分：</div><div class="table-td">'+ <?php echo ($sum_score); ?>+'积分</div></div>';
 }

 function outHide2() {
  var showDiv = document.getElementById('showDiv2');
  showDiv.style.display = 'none';
  showDiv.innerHTML = '';
 }

 function overShow3() {
	 var showDiv = document.getElementById('showDiv3');
	 //showDiv.style.left = event.clientX;
	 //showDiv.style.top = event.clientY;
	 showDiv.style.display = 'block';
	 showDiv.innerHTML = '<div class="table"><div class="table-tr"><div class="table-td">统计金额不包含退款金额和已取消订单金额</div></div></div>';
 }

 function outHide3() {
	 var showDiv = document.getElementById('showDiv3');
	 showDiv.style.display = 'none';
	 showDiv.innerHTML = '';
 }

 function overShow4() {
	 var showDiv = document.getElementById('showDiv4');
	 //showDiv.style.left = event.clientX;
	 //showDiv.style.top = event.clientY;
	 showDiv.style.display = 'block';
	 showDiv.innerHTML = '<div class="table"><div class="table-tr"><div class="table-td">统计金额不包含退款金额和已取消订单金额</div></div></div>';
 }

 function outHide4() {
	 var showDiv = document.getElementById('showDiv4');
	 showDiv.style.display = 'none';
	 showDiv.innerHTML = '';
 }

 function overShowWait() {
	 var showDiv = document.getElementById('showDivWait');
	 //showDiv.style.left = event.clientX;
	 //showDiv.style.top = event.clientY;
	 showDiv.style.display = 'block';
	 showDiv.innerHTML = '<div class="table"><div class="table-tr"><div class="table-td">备货中订单包含普通订单与拼团订单</div></div></div>';
 }

 function outHideWait() {
	 var showDiv = document.getElementById('showDivWait');
	 showDiv.style.display = 'none';
	 showDiv.innerHTML = '';
 }
</script>

<script>
//由于模块都一次性加载，因此不用执行 layui.use() 来加载对应模块，直接使用即可：
var layer = layui.layer;
var $;
var  cur_type = 'normal';

layui.use(['jquery', 'layer'], function(){
  $ = layui.$;
  load_data();
  //后面就跟你平时使用jQuery一样
  loadEchartsLine();
  load_echat_member_incr();
  load_echat_month_head_sales();
  load_echat_month_goods_sales();
	load_echat_month_goods_sales1();
});

function load_echat_member_incr()
{
	var hasLineChart = $("#echat_member_incr").length>0;
	if(hasLineChart){
		var lineChart = echarts.init(document.getElementById("echat_member_incr"));
	}
	window.onresize = function () {
		if(hasLineChart) {
			lineChart.resize();
		}
	};

	$.ajax({
		type: "GET",
		url: "<?php echo U('statistics/load_echat_member_incr');?>",
		dataType: "json",
		success: function (data) {
			var options = {

				series : [
					{
						name:'会员增长',

						data:data.member_count,
					}
				],
				chart: {
					height: 333.6,
					type: 'bar',
					events: {
						click: function(chart, w, e) {
// console.log(chart, w, e)
						}
					}
				},
				colors: [pocoAdminConfig.primary,pocoAdminConfig.primary,pocoAdminConfig.primary,pocoAdminConfig.primary,pocoAdminConfig.primary,pocoAdminConfig.secondary,pocoAdminConfig.secondary,pocoAdminConfig.secondary,pocoAdminConfig.secondary,pocoAdminConfig.secondary],
				plotOptions: {
					bar: {
						columnWidth: '45%',
						distributed: true,
						endingShape: 'rounded'
					}
				},

				stroke: {
					lineCap: "round",
				},
				dataLabels: {
					enabled: false
				},
				legend: {
					show: false
				},
				xaxis: {
					categories : data.date_arr,
				},
			};

			var chart = new ApexCharts(document.querySelector("#chartmember"), options);
			chart.render();
		}
	})


}

function load_echat_month_head_sales()
{
	var hasLineChart = $("#echat_month_head_sales").length>0;
	if(hasLineChart){
		var lineChart = echarts.init(document.getElementById("echat_month_head_sales"));
	}
	window.onresize = function () {
		if(hasLineChart) {
			lineChart.resize();
		}
	};

	$.ajax({
		type: "GET",
		url: "<?php echo U('statistics/load_echat_month_head_sales', array('type' => 1));?>",
		dataType: "json",
		success: function (data) {
			if(data.code == 0)
			{

				var le_arr = [];
				var data_obj_arr  = [];

				for( var i in data.list )
				{
					le_arr.push(  data.list[i].head_name  );
					data_obj_arr.push({value:data.list[i].total, name:data.list[i].head_name });
				}




				var option = null;
				option = {
					title : {
						text: '销售金额'+data.total,
						subtext: data.month,
						x:'center'
					},
					tooltip : {
						trigger: 'item',
						formatter: "{a} <br/>{b} : {c} ({d}%)"
					},
					legend: {
						orient: 'vertical',
						left: 'left',
						data: le_arr
					},
					series : [
						{
							name: '销售金额',
							type: 'pie',
							radius : '55%',
							center: ['50%', '60%'],
							data:data_obj_arr,
							itemStyle: {
								emphasis: {
									shadowBlur: 10,
									shadowOffsetX: 0,
									shadowColor: 'rgba(0, 0, 0, 0.5)'
								}
							}
						}
					]
				};


				if (option && typeof option === "object") {
					lineChart.setOption(option, true);
				}
			}
		}
	})


}
function load_echat_month_goods_sales1()
{
	var hasLineChart = $("#echat_month_goods_sales").length>0;
	if(hasLineChart){
		var lineChart = echarts.init(document.getElementById("echat_month_goods_sales"));
	}
	window.onresize = function () {
		if(hasLineChart) {
			lineChart.resize();
		}
	};

	$.ajax({
		type: "GET",
		url: "<?php echo U('statistics/load_echat_month_goods_sales', array('type' => 1));?>",
		dataType: "json",
		success: function (data) {
			if(data.code == 0)
			{

				var le_arr = [];
				var data_obj_arr  = [];

				for( var i in data.list )
				{
					le_arr.push(  data.list[i].name  );
					data_obj_arr.push({value:data.list[i].total, name:data.list[i].name });
				}




				var option = null;
				option = {
					title : {
						text: '销售金额'+data.total,
						subtext: '销售总数：'+data.total_quantity+' ('+data.month+')',
						x:'center'
					},
					tooltip : {
						trigger: 'item',
						formatter: "{a} <br/>{b} : {c} ({d}%)"
					},
					legend: {
						orient: 'vertical',
						left: 'left',
						data: le_arr
					},
					series : [
						{
							name: '销售金额',
							type: 'pie',
							radius : '55%',
							center: ['50%', '60%'],
							data:data_obj_arr,
							itemStyle: {
								emphasis: {
									shadowBlur: 10,
									shadowOffsetX: 0,
									shadowColor: 'rgba(0, 0, 0, 0.5)'
								}
							}
						}
					]
				};


				if (option && typeof option === "object") {
					lineChart.setOption(option, true);
				}
			}
		}
	})


}
function load_echat_month_goods_sales()
{
	var hasLineChart = $("#chartmonthgoods").length>0;
	if(hasLineChart){
		var lineChart = echarts.init(document.getElementById("chartmonthgoods"));
	}
	window.onresize = function () {
		if(hasLineChart) {
			lineChart.resize();
		}
	};

	$.ajax({
		type: "GET",
		url: "<?php echo U('statistics/load_echat_month_goods_sales', array('type' => 1));?>",
		dataType: "json",
		success: function (data) {
			var le_arr = [];
			var data_obj_arr  = [];
			var data_series_arr  = [];
			for( var i in data.list )
			{
				le_arr.push(  data.list[i].name  );
				data_obj_arr.push({value:data.list[i].total, name:data.list[i].name });
				data_series_arr.push( data.list[i].total_quantity );
			}

			var options4 = {
				chart: {
					height: 372,
					type: 'donut',
					fullWidth: true,
				},
				plotOptions: {
					padding: {
						left: 0,
						right: 0
					},
					radialBar: {
						hollow: {
							margin: 10,
							size: "55%",
							image: undefined,
							imageOffsetX: 0,
							imageOffsetY: 0,
							position: 'front',
						},
						track: {
							show: false,
						},
						dataLabels: {
							name: {
								fontSize: '22px',
							},
							value: {
								fontSize: '16px',
							},
							total: {
								show: true,
								label: '共计',
								formatter: function (w) {
									return data_obj_arr
								}
							},
							dropShadow: {
								enabled: true,
								top: 3,
								left: 0,
								blur: 4,
								opacity: 0.24
							}
						}
					}
				},
				fill: {
					//pocoAdminConfig.primary
					colors:[pocoAdminConfig.primary, pocoAdminConfig.secondary, '#168ef7'],
					type: 'gradient',
					gradient: {
						shade: 'light',
						type: 'horizontal',
						shadeIntensity: 0.2,
						inverseColors: true,
						opacityFrom: 1,
						opacityTo: 0.7,
						stops: [0, 100]
					}
				},
				colors: [pocoAdminConfig.primary, pocoAdminConfig.secondary, '#168ef7'],
				series: [4, 4, 3],
				labels: le_arr,
				stroke: {
					lineCap: "round",
				},
				tooltip : {
					trigger: 'item',
					formatter: "{a} <br/>{b} : {c} ({d}%)"
				},
				title : {
					//text:  ['销售金额'+data.total],
					text: ['销售总数：'+data.total_quantity+' ('+data.month+')'],
					x:'center'
				},
				legend: {
					show: true,
					floating: true,
					fontSize: '16px',
					position: 'left',
					offsetX: 0,
					offsetY: 0,
					labels: {
						useSeriesColors: true,
					},
				},
			}

			var chart4 = new ApexCharts(
					document.querySelector("#chartmonthgoods"),
					options4
			);

			chart4.render();
		}
	})


}
function loadEchartsLine()
{
	var hasLineChart = $("#chartorder").length>0;
	if(hasLineChart){
		var lineChart = echarts.init(document.getElementById("chartorder"));
	}
	window.onresize = function () {
		if(hasLineChart) {
			lineChart.resize();
		}
	};

	$.ajax({
	    type: "GET",
	    url: "<?php echo U('statistics/load_goods_chart'); ?>",
		data:{type:cur_type},
	    dataType: "json",
	    success: function (json) {
			var options = {

				chart: {
					height: 350,
					type: 'area',
					toolbar: {
						show: false
					},
				},
				dataLabels: {
					enabled: false
				},
				grid: {
					borderColor: '#f0f7fa',
				},
				stroke: {
					curve: 'smooth',
					width: 4
				},
				series: [{
					name: '付款金额',
					data: json.data.price_value,
				}, {
					name: '退款金额',
					data: json.data.price_value_2,
				}],


				xaxis: {
					low: 0,
					offsetX: 0,
					offsetY: 0,
					show: false,

					labels: {
						low: 0,
						offsetX: 0,
						show: true,
					},
					axisBorder: {
						low: 0,
						offsetX: 0,
						show: false,
					},
					axisTicks: {
						show: false,
					},
					categories: json.data.price_key,
				},

				yaxis: [
					{
						title: {
							text: '金额',
							style: {
								fontSize: '18px',
								fontWeight: 400,
								cssClass: 'font-primary',
							}
						},
					},
				],
				tooltip: {
					enabled: true,
					shared: true,
					followCursor: true,

					inverseOrder: true,
					fillSeriesColor: true,
					theme: true,
					style: {
						fontSize: '14px'
					},
				},
				colors: [pocoAdminConfig.primary,pocoAdminConfig.secondary],
				fill: {
					type: 'gradient',
					gradient: {
						shadeIntensity: 1,
						opacityFrom: 0.4,
						opacityTo: 0.8,
						stops: [0, 85, 100]
					}
				}
			}

			var chart = new ApexCharts(
					document.querySelector("#chartorder"),
					options
			);

			chart.render();
		}
	})
}
function buildLine2(name, data, yIndex) {
    return {
        name: name,
        type: "line",
        areaStyle: { normal: {} },
        smooth: true,
        data: data.seriesData[1].data,
        yAxisIndex: yIndex
    };
}

function buildLine(name, data, yIndex) {
    return {
        name: name,
        type: "line",
        areaStyle: { normal: {} },
        smooth: true,
        data: data.seriesData[0].data,
        yAxisIndex: yIndex
    };
}

function load_goods_paihang()
{
	var s_index = $('#sale li.active').index();

	$.ajax({
		type: "GET",
		url: "<?php echo U('statistics/load_goods_paihang'); ?>",
		data:{type:cur_type,s_index:s_index},
		dataType: "json",
		success: function (data) {
			$('#sale_div .ibox-loading').hide();
			if(data.code ==0 )
			{
				$('#goods_rank_0 tbody').html( data.html );
			}
		}
	})
}
function load_order_buy_data()
{
	var s_index = $('#orderinfo li.active').index();

	$.ajax({
		type: "GET",
		url: "<?php echo U('statistics/order_buy_data'); ?>",
		data:{type:cur_type,s_index:s_index},
		dataType: "json",
		success: function (data) {
			$('#order_info_div .ibox-loading').hide();
			if(data.code ==0 )
			{
				$('.order_count_0').html( data.data.count );
				$('.order_price_0').html( data.data.total );
				$('.order_avg_0').html( data.data.per_money );
			}
		}
	})
}
function load_data()
{
	cur_type = $('#select_type').val();

	$.ajax({
		type: "GET",
		url: "<?php echo U('statistics/index_data'); ?>",
		data:{type:cur_type},
		dataType: "json",
		success: function (data) {

			if(data.code ==0 )
			{
				$('.month_good_count').html( data.data.member_count );
				$('.today_member_count').html( data.data.today_member_count );
				$('.today_pay_order_count').html( data.data.today_pay_order_count );
				$('.today_pay_money').html( '<em>¥</em><span class="counter">'+data.data.today_pay_money +'</span>' );

				$('.total_tixian_money').html( '<em>¥</em><span class="counter">'+data.data.total_tixian_money +'</span>' );
				$('.total_commiss_money').html( '<em>¥</em><span class="counter">'+data.data.total_commiss_money +'</span>' );
				$('.total_order_money').html( '<em>¥</em><span class="counter">'+data.data.total_order_money +'</span>' );

				$('.counter').countUp();

				$('.goods_stock_notice_count').html( data.data.goods_stock_notice_count );
				$('.wait_pay_order_count').html( data.data.wait_pay_order_count );
				$('.wait_order_count').html( data.data.wait_order_count );
				$('.after_sale_order_count').html( data.data.after_sale_order_count );
				$('.wai_comment_order_count').html( data.data.wai_comment_order_count );

				$('.wait_shen_order_comment_count').html( data.data.wait_shen_order_comment_count );
				$('.stock_goods_count').html( data.data.stock_goods_count );
				$('.seven_order_count').html( data.data.seven_order_count );
				$('.seven_pay_money').html(data.data.seven_pay_money);
				$('.seven_refund_money').html(data.data.seven_refund_money);

				$('.community_head_count').html(data.data.community_head_count);
				$('.seven_order_count').html(data.data.seven_order_count);


				$('.member_count').html( data.data.member_count );
				$('.goods_count').html( data.data.goods_count );
				$('.order_count').html( data.data.order_count );
				$('.tixian_count').html( data.data.apply_count );

                if(data.data.wait_order_type == 'pintuan'){
                    var url = "<?php echo U('group/orderlist', array('order_status_id' => 1)); ?>";
                    $('.wait_order_count').attr('href',url);
                }else if(data.data.wait_order_type == 'integral'){
                    var url = "<?php echo U('points/order', array('order_status_id' => 1)); ?>";
                    $('.wait_order_count').attr('href',url);
                }

				$('.goods_totals').html( data.data.goods_stock_notice_count );
			}
		}
	})
	$.ajax({
		type: "GET",
		url: "<?php echo U('statistics/index_data'); ?>",
		data:{type:cur_type},
		dataType: "json",
		success: function (data) {

			if(data.code ==0 )
			{


				$('.month_good_money').html( '<span class="counter">'+data.data.total +'</span>' );

			}
		}
	})
	load_order_buy_data();
	load_goods_paihang();


}
</script>
<script type="text/javascript">
	$('.counter').countUp();
</script>


</body>
</html>