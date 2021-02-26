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
                            <h6 class="mb-0">当前位置</h6><h2>商品<span>设置</span></h2>
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
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">商品库存预警</label>
                                        <div class="col-sm-4">
                                            <input type="text" name="parameter[goods_stock_notice]" class="form-control" value="<?php echo ($data['goods_stock_notice']); ?>" placeholder="请填写数字"/>
                                            <small>商品库存小于此数时将在首页和商品列表中提示</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">购买记录</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline1" type="radio" name="parameter[is_show_buy_record]" value="0" <?php if(!empty($data) && $data['is_show_buy_record'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline1">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline2" type="radio" name="parameter[is_show_buy_record]" value="1" <?php if(empty($data) || $data['is_show_buy_record'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline2">显示</label>
                                                </div>
                                            </div>
                                            <small>商品详情页显示购买记录开关</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">商品评价</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline3" type="radio" name='parameter[is_show_comment_list]' value='0' <?php if(!empty($data) && $data['is_show_comment_list'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline3">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline4" type="radio" name='parameter[is_show_comment_list]' value='1' <?php if(empty($data) || $data['is_show_comment_list'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline4">显示</label>
                                                </div>
                                            </div>
                                            <small>商品详情页显示商品评价开关</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">首页商品倒计时</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline5" type="radio" name='parameter[is_show_list_timer]' value='0' <?php if(empty($data) || $data['is_show_list_timer'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline5">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline6" type="radio" name='parameter[is_show_list_timer]' value='1' <?php if(!empty($data) && $data['is_show_list_timer'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline6">显示</label>
                                                </div>
                                            </div>
                                            <small>首页商品卡片显示商品售卖倒计时</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">详情页商品倒计时</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline42" type="radio" name='parameter[is_close_details_time]' value='1' <?php if(!empty($data) && $data['is_close_details_time'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline42">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline41" type="radio" name='parameter[is_close_details_time]' value='0' <?php if(empty($data) || $data['is_close_details_time'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline41">显示</label>
                                                </div>
                                            </div>
                                            <small>商品详情页显示商品售卖倒计时</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">首页商品排序方式</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline21" type='radio' name='parameter[index_sort_method]' value='0' <?php if( empty($data) || $data['index_sort_method'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline21">使用置顶排序</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline22" type='radio' name='parameter[index_sort_method]' value='1' <?php if( !empty($data) && $data['index_sort_method'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline22">使用序号排序</label>
                                                </div>
                                            </div>
                                            <small>使用置顶排序时，商品列表里的打开置顶开关的商品将置顶，如有多个置顶商品，置顶时间越晚的越靠前<br/>使用序号排序时，数字越大的越靠前</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">首页商品销量</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline7" type="radio" name='parameter[is_show_list_count]' value='0' <?php if(!empty($data) && $data['is_show_list_count'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline7">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline8" type="radio" name='parameter[is_show_list_count]' value='1' <?php if(empty($data) || $data['is_show_list_count'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline8">显示</label>
                                                </div>
                                            </div>
                                            <small>首页商品卡片显示商品已售数量</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">详情页商品销量</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline9" type="radio" name='parameter[is_hide_details_count]' value='1' <?php if( !empty($data) && $data['is_hide_details_count']==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline9">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline10" type="radio" name='parameter[is_hide_details_count]' value='0' <?php if( empty($data) || $data['is_hide_details_count']==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline10">显示</label>
                                                </div>
                                            </div>
                                            <small>商品详情页显示商品已售数量</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">新人专享</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline11" type="radio" name='parameter[is_show_new_buy]' value='0' <?php if(!empty($data) && $data['is_show_new_buy'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline11">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline12" type="radio" name='parameter[is_show_new_buy]' value='1' <?php if(empty($data) || $data['is_show_new_buy'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline12">显示</label>
                                                </div>
                                            </div>
                                            <small>首页显示新人专享模块</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">限时秒杀</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline13" type="radio" name='parameter[is_show_spike_buy]' value='0' <?php if(!empty($data) && $data['is_show_spike_buy'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline13">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline14" type="radio" name='parameter[is_show_spike_buy]' value='1' <?php if(empty($data) || $data['is_show_spike_buy'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline14">显示</label>
                                                </div>
                                            </div>
                                            <small>首页显示限时秒杀模块</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">限时秒杀倒计时</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline43" type='radio' name='parameter[is_show_spike_buy_time]' value='0' <?php if(!empty($data) && $data['is_show_spike_buy_time']==0){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline43">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline44" type='radio' name='parameter[is_show_spike_buy_time]' value='1' <?php if(empty($data) || $data['is_show_spike_buy_time']==1){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline44">显示</label>
                                                </div>
                                            </div>
                                            <small>首页显示限时秒杀倒计时</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">大家常买</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline15" type="radio" name='parameter[is_show_guess_like]' value='0' <?php if(!empty($data) && $data['is_show_guess_like'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline15">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline16" type="radio" name='parameter[is_show_guess_like]' value='1' <?php if(empty($data) || $data['is_show_guess_like'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline16">显示</label>
                                                </div>
                                            </div>
                                            <small>购物车页面底部显示大家常买商品</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">猜你喜欢</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline17" type="radio" name='parameter[show_goods_guess_like]' value='0' <?php if(!empty($data) && $data['show_goods_guess_like'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline17">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline18" type="radio" name='parameter[show_goods_guess_like]' value='1' <?php if(empty($data) || $data['show_goods_guess_like'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline18">显示</label>
                                                </div>
                                            </div>
                                            <small>商品详情页底部显示猜你喜欢商品</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">猜你喜欢展示数量</label>
                                        <div class="col-sm-4">
                                            <input type="text"  name="parameter[num_guess_like]" class="form-control valid" value="<?php echo ($data['num_guess_like']); ?>" placeholder="请填写数字"/>
                                            <small>默认展示数量：8个</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">自提时间</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline19" type="radio" name='parameter[is_show_ziti_time]' value='0' <?php if(!empty($data) && $data['is_show_ziti_time'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline19">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline20" type="radio" name='parameter[is_show_ziti_time]' value='1' <?php if(empty($data) || $data['is_show_ziti_time'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline20">显示</label>
                                                </div>
                                            </div>
                                            <small>商品详情页底部显示猜你喜欢商品</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">商品仅快递</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline23" type="radio" name='parameter[is_open_only_express]' value='0' <?php if(empty($data) || $data['is_open_only_express'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline23">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline24" type="radio" name='parameter[is_open_only_express]' value='1' <?php if(!empty($data) && $data['is_open_only_express'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline24">显示</label>
                                                </div>
                                            </div>
                                            <small>后台添加商品页显示商品仅快递模块</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">推荐商品</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline25" type="radio" name='parameter[is_open_goods_relative_goods]' value='0' <?php if(empty($data) || $data['is_open_goods_relative_goods'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline25">不显示</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline26" type="radio" name='parameter[is_open_goods_relative_goods]' value='1' <?php if(!empty($data) && $data['is_open_goods_relative_goods'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline26">显示</label>
                                                </div>
                                            </div>
                                            <small>显示推荐商品模块</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">加入购物车背景色</label>
                                        <div class="col-sm-6">
                                            <div class="col-sm-6" id="minicolors" style="display: flex;padding-left: 0px">
                                                <input type="text" name="parameter[goodsdetails_addcart_bg_color]" value="<?php echo ($data['goodsdetails_addcart_bg_color']); ?>" placeholder="请选择颜色" class="form-control" id="test-colorpicker-form-input">
                                                <div  class="form-control colorpicker"><span class="colorpicker-trigger-span" lay-type="" style="background: <?php echo ($data['goodsdetails_addcart_bg_color']); ?>"></span></div>
                                            </div>
                                            <small>加入购物车背景颜色值，有效值为十六进制颜色。默认色值：<font color="#f9c706">#f9c706</font></small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">立即购买背景色</label>
                                        <div class="col-sm-6">
                                            <div class="col-sm-6" id="minicolors1" style="display: flex;padding-left: 0px">
                                                <input type="text" name="parameter[goodsdetails_buy_bg_color]" value="<?php echo ($data['goodsdetails_buy_bg_color']); ?>" placeholder="请选择颜色" class="form-control" id="test-colorpicker-form-input1">
                                                <div  class="form-control colorpicker"><span class="colorpicker-trigger-span" lay-type="" style="background: <?php echo ($data['goodsdetails_buy_bg_color']); ?>"></span></div>
                                            </div>
                                            <small>立即购买背景颜色值，有效值为十六进制颜色。默认色值：<font color="#ff5041">#ff5041</font></small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">海报头像圆角填充颜色</label>
                                        <div class="col-sm-6">
                                            <div class="col-sm-6" id="goods_avatar_rgb" style="display: flex;padding-left: 0px">
                                                <input type="text" name="parameter[goods_avatar_rgb]" value="<?php echo ($data['goods_avatar_rgb']); ?>" placeholder="请选择颜色" class="form-control" id="goods_avatar_rgb_colorpicker">
                                                <div  class="form-control colorpicker"><span class="colorpicker-trigger-span" lay-type="" style="background: <?php echo ($data['goods_avatar_rgb']); ?>"></span></div>
                                            </div>
                                            <small>商品详情页海报头像颜色值，有效值为十六进制颜色。默认色值：<font color="yellow">#ffff00</font></small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">服务说明</label>
                                        <div class="col-sm-10">
                                            <div class="m-b-10">
                                                <?php echo tpl_ueditor('parameter[instructions]',$data['instructions'],array('height'=>'300'));?>
                                            </div>
                                            <small>商品详情页底部服务说明</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">详情页团长信息</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline29" type="radio" name='parameter[is_show_goodsdetails_communityinfo]' value='0' <?php if(empty($data) || $data['is_show_goodsdetails_communityinfo'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline29">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline30" type="radio" name='parameter[is_show_goodsdetails_communityinfo]' value='1' <?php if(!empty($data) && $data['is_show_goodsdetails_communityinfo'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline30">显示</label>
                                                </div>
                                            </div>
                                            <small>商品详情页显示团长信息</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">详情页预计佣金</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline31" type="radio" name='parameter[is_show_goodsdetails_commiss_money]' value='0' <?php if(empty($data) || $data['is_show_goodsdetails_commiss_money'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline31">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline32" type="radio" name='parameter[is_show_goodsdetails_commiss_money]' value='1' <?php if(!empty($data) && $data['is_show_goodsdetails_commiss_money'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline32">显示</label>
                                                </div>
                                            </div>
                                            <small>商品详情页显示预计佣金，普通会员不显示，团长或分销身份时显示</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">预售提货时间显示</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline33" type="radio" name="parameter[ishow_index_pickup_time]" value="0" <?php if(empty($data) || $data['ishow_index_pickup_time'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline33">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline34" type="radio" name="parameter[ishow_index_pickup_time]" value="1" <?php if(!empty($data) && $data['ishow_index_pickup_time'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline34">显示</label>
                                                </div>
                                            </div>
                                            <small>首页商品列表预售、提货时间显示，默认隐藏。</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">会员专享商品<br>弹出图片提示</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline35" type="radio" name="parameter[is_pop_vipmember_buytip]" value="0" <?php if(empty($data) || $data['is_pop_vipmember_buytip'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline35">关闭</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline36" type="radio" name="parameter[is_pop_vipmember_buytip]" value="1" <?php if(!empty($data) && $data['is_pop_vipmember_buytip'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline36">开启</label>
                                                </div>
                                            </div>
                                            <small>开启后，非付费会员购买会员专享商品时弹出图片，此功能需在营销设置中开启付费会员卡功能</small>
                                            <div class="">
                                                <?php echo tpl_form_field_image2('parameter[pop_vipmember_buyimage]', $data['pop_vipmember_buyimage']);?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">详情页全屏视频</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline37" type="radio" name='parameter[is_open_goods_full_video]' value='0' <?php if(empty($data) || $data['is_open_goods_full_video'] ==0){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline37">关闭</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline38" type="radio" name='parameter[is_open_goods_full_video]' value='1' <?php if(!empty($data) && $data['is_open_goods_full_video'] ==1){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline38">开启</label>
                                                </div>
                                            </div>
                                            <small>开启后，若有此商品带有视频，则进入商品详情页面先全屏展示视频，播放结束后自动关闭。</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">商品海报背景</label>
                                        <div class="col-sm-10">
                                            <?php echo tpl_form_field_image2('parameter[haibao_gooods_bg2]', $data['haibao_gooods_bg2'], "/assets/ep/images/bg2.jpg");?>
                                            <small>系统默认海报背景，上传更改背景图片尺寸为750*1364，图片格式为jpg。</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">商品详情</label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline49" type='radio' name='parameter[ishide_details_desc]' value='1' <?php if( !empty($data) && $data['ishide_details_desc'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline49">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline50" type='radio' name='parameter[ishide_details_desc]' value='0' <?php if( empty($data) || $data['ishide_details_desc'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline50">显示</label>
                                                </div>
                                            </div>
                                            <small>默认显示，隐藏则包括“商品详情头部”一起隐藏。</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">商品详情头部</label>
                                        <div class="col-sm-10">
                                            <?php echo tpl_form_field_image2('parameter[goods_details_title_bg]', $data['goods_details_title_bg']);?>
                                            <small>商品详情页详情内容头部标题图片，建议尺寸:336*60。</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">销量单位</label>
                                        <div class="col-sm-10 fixmore-input-group">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline39" lay-filter="isnull_unit" type="radio" name='parameter[isnull_goods_sale_unit]' value='1' <?php if(!empty($data) && $data['isnull_goods_sale_unit']==1){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline39">默认（件）</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline40" lay-filter="isnull_unit" type="radio" name='parameter[isnull_goods_sale_unit]' value='0' <?php if(empty($data) || $data['isnull_goods_sale_unit']==0){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline40">自定义</label>
                                                </div>
                                                <div class="radio radio-primary" >
                                                    <input  type="text" name="parameter[goods_sale_unit]" class="form-control valid" value="<?php echo ($data['goods_sale_unit']); ?>"  />
                                                </div>
                                            </div>
                                            <small>前端展示如：已售**件，仅剩**件。默认单位：件</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">商品详情页商品简介<br/></label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline45" type='radio' name='parameter[show_goods_subtitle]' value='0' <?php if( empty($data) || $data['show_goods_subtitle'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline45">隐藏</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline46" type='radio' name='parameter[show_goods_subtitle]' value='1' <?php if( !empty($data) && $data['show_goods_subtitle'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline46">显示</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">商品详情页顶部商品图片单击放大<br/></label>
                                        <div class="col-sm-10">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline47" type='radio' name='parameter[show_goods_preview]' value='0' <?php if( empty($data) || $data['show_goods_preview'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline47">开启</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline48" type='radio' name='parameter[show_goods_preview]' value='1' <?php if( !empty($data) && $data['show_goods_preview'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline48">关闭</label>
                                                </div>
                                            </div>
                                            <small>商品详情页顶部商品幻灯片，默认开启。</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">旧的域名</label>
                                        <div class="col-sm-10">
                                            <input type="text"  name="present_realm_name"  class="form-control" /> <!--readonly="readonly"-->
                                            域名格式为 http://域名 或者 https://域名 ， 如 https://www.xxx.com </br>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">新的域名</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="new_realm_name" class="form-control"  />
                                            填写‘旧的域名’与‘新的域名’后开始替换域名，不填写无效
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">分享群信息</label>
                                        <div class="col-sm-10 fixmore-input-group">
                                            <div class="form-group m-checkbox-inline custom-radio-ml m-t-10 m-l-5 mb-0">
                                                <div class="radio radio-primary">
                                                    <input id="radioinline27"  type="radio" name='parameter[isopen_community_group_share]' value='1' <?php if(!empty($data) && $data['isopen_community_group_share'] ==1 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline27">开启</label>
                                                </div>
                                                <div class="radio radio-primary">
                                                    <input id="radioinline28"  type="radio" name='parameter[isopen_community_group_share]' value='0' <?php if(empty($data) || $data['isopen_community_group_share'] ==0 ){ ?>checked <?php } ?>/>
                                                    <label class="mb-0" for="radioinline28">关闭</label>
                                                </div>
                                            </div>
                                            <small>商品详情页显示团长群信息</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">团长群头像</label>
                                        <div class="col-sm-10">
                                            <?php echo tpl_form_field_image2('parameter[group_share_avatar]', $data['group_share_avatar']);?>
                                            <small>建议上传正方型图片</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">团长群标题</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="parameter[group_share_title]" class="form-control" value="<?php echo ($data['group_share_title']); ?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">团长群描述</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="parameter[group_share_desc]" class="form-control" value="<?php echo ($data['group_share_desc']); ?>" />
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

    var goodsdetails_addcart_bg_color = '<?php echo ($data["goodsdetails_addcart_bg_color"]); ?>';
    $('#minicolors').colpick({
        submit:true,
        color: goodsdetails_addcart_bg_color,
        onSubmit: function(color,color2){
            $('#test-colorpicker-form-input').val('#'+color2);
            $('#minicolors').find('.colorpicker-trigger-span').css('background','#'+color2);
            $('.colpick_full').hide();
        }
    });

    var goods_avatar_rgb = '<?php echo ($data["goods_avatar_rgb"]); ?>';
    $('#goods_avatar_rgb').colpick({
        submit:true,
        color: goods_avatar_rgb,
        onSubmit: function(color,color2){
            $('#goods_avatar_rgb_colorpicker').val('#'+color2);
            $('#goods_avatar_rgb').find('.colorpicker-trigger-span').css('background','#'+color2);
            $('.colpick_full').hide();
        }
    });

    var goodsdetails_buy_bg_color = '<?php echo ($data["goodsdetails_buy_bg_color"]); ?>';
    $('#minicolors1').colpick({
        submit:true,
        color: goodsdetails_buy_bg_color,
        onSubmit: function(color,color2){
            $('#test-colorpicker-form-input1').val('#'+color2);
            $('#minicolors1').find('.colorpicker-trigger-span').css('background','#'+color2);
            $('.colpick_full').hide();
        }
    });
</script>

<script>
//由于模块都一次性加载，因此不用执行 layui.use() 来加载对应模块，直接使用即可：
var layer = layui.layer;
var $;

var cur_open_div;

layui.use(['jquery', 'layer','form','colorpicker'], function(){
    $ = layui.$;
    var form = layui.form;
    var colorpicker = layui.colorpicker;

	form.on('radio(linktype)', function(data){
		if (data.value == 2) {
			$('#typeGroup').show();
		} else {
			$('#typeGroup').hide();
		}
	});

    //表单赋值
    /*var goodsdetails_addcart_bg_color = '<?php echo ($data["goodsdetails_addcart_bg_color"]); ?>';
    colorpicker.render({
      elem: '#minicolors'
      ,color: goodsdetails_addcart_bg_color ? goodsdetails_addcart_bg_color : '#f9c706'
      ,done: function(color){
        $('#test-colorpicker-form-input').val(color);
      }
    });

    colorpicker.render({
      elem: '#goods_avatar_rgb'
      ,color: '<?php echo ($data['goods_avatar_rgb']); ?>'
      ,done: function(color){
        $('#goods_avatar_rgb_colorpicker').val(color);
      }
    });

    //表单赋值
    var goodsdetails_buy_bg_color = '<?php echo ($data["goodsdetails_buy_bg_color"]); ?>';
    colorpicker.render({
      elem: '#minicolors1'
      ,color: goodsdetails_buy_bg_color ? goodsdetails_buy_bg_color : '#ff5041'
      ,done: function(color){
        $('#test-colorpicker-form-input1').val(color);
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

	form.on('radio(isnull_unit)', function(data){
	  	if (data.value==1) {
			$("#goodsSaleUnit").hide();
		} else {
			$("#goodsSaleUnit").css('display','inline');
		}
		form.render('checkbox');
	});
})



</script>
</body>