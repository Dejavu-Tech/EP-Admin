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
    <link href="/assets/css/bootstrap.min1.css?v=201903260001" rel="stylesheet">
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
    <script type="text/javascript" src="/resource/js/require.js?v=201903260001"></script>
    <script type="text/javascript" src="/resource/js/lib/jquery.nice-select.js?v=201903260001"></script>
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
                            <h2>小程序直播间<span>管理</span></h2>
                            <div class="alert alert-primary" role="alert">
                                <p>小程序直播运营操作说明：<p>
                                <p>1、登录微信小程序后台，在左侧功能栏 直播-><a href="https://mp.weixin.qq.com/" class="txt-warning" target="_blank">点击创建直播间</a>。</p>
                                <p>2、在小程序后台成功创建直播间后，点击列表中的”同步直播间“按钮。同步直播间后直播列表页面中会显示。</p>
                                <p class="txt-danger">3、切勿重复点击”同步直播间“按钮。小程序每天有请求次数限制。</p>
                            </div>
                        </div>
                        <div class="col-lg-6 breadcrumb-right" style="float: right">
                            <ol class="breadcrumb">
                                <a class="m-r-5" href="javascript:;" id="refresh" data-href="<?php echo U('wxlive/sync');?>"><button class="btn btn-info btn-air-secondary" style=""><i class="fa fa-refresh"></i> 同步直播间</button></a>
                                <a href="https://mp.weixin.qq.com/" target="_blank"><button class="btn btn-info btn-air-success" style=""><i class="fa fa-plus"></i> 添加新直播间</button></a>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body" >
                                <form action="" method="get" class="form-horizontal form-search layui-form" role="form">
                                    <input type="hidden" name="c" value="wxlive" />
                                    <input type="hidden" name="a" value="index" />
                                    <div class="form-row">
                                        <div class="p-r-5">
                                            <input type="text" class="form-control" name='keywords' value="<?php echo ($keyword); ?>" placeholder="请输入关键词">
                                        </div>
                                        <div class="p-r-5">
                                            <button class="btn btn-primary  type="submit"> 搜索</button>
                                        </div>
                                    </div>
                                </form>
                                <form action="" method="post" class="form theme-form layui-form m-t-20" lay-filter="component-layui-form-item" enctype="multipart/form-data">
                                    <div class="dataTables_wrapper no-footer">
                                        <?php if(count($list)>0){ ?>
                                        <div class="table-responsive">
                                            <table class="display dataTable text-center" lay-even lay-skin="line" lay-size="lg">
                                                <thead>
                                                <tr>
                                                    <th>直播间ID</th>
                                                    <th>直播标题</th>
                                                    <th>主播昵称</th>
                                                    <th>直播间背景</th>
                                                    <th>分享封面</th>
                                                    <th>开播时间</th>
                                                    <th>结束时间</th>
                                                    <th>隐藏|显示</th>
                                                    <th>回放</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach( $list as $item ){ ?>
                                                <tr>
                                                    <td><?php echo ($item['roomid']); ?></td>
                                                    <td><?php echo ($item['name']); ?></td>
                                                    <td><?php echo ($item['anchor_name']); ?></td>
                                                    <td>
                                                        <img src="<?php echo tomedia($item['cover_img']);?>" style="width:40px;height:40px;border-radius: 5px"/>
                                                    </td>
                                                    <td>
                                                        <img src="<?php echo tomedia($item['share_img']);?>" style="width:40px;height:40px;border-radius: 5px"/>
                                                    </td>
                                                    <td><?php echo date('Y-m-d H:i:s', $item['start_time']);?></td>
                                                    <td><?php echo date('Y-m-d H:i:s', $item['end_time']);?></td>
                                                    <td>
                                                        <label class="switch" style="margin-bottom: 0">
                                                        <input type="checkbox" name="" lay-filter="statewsitch" data-href="<?php echo U('wxlive/change',array('type'=>'is_show','id'=>$item['id']));?>" <?php if( $item['is_show']==1){ ?>checked<?php  }else{ } ?> lay-skin="switch" lay-text="">
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-pill btn-primary" href="<?php echo U('wxlive/replay', array('id'=>$item['id'],'roomid'=>$item['roomid']));?>">
                                                            回放
                                                        </a>
                                                        <!-- <a class="btn btn-op btn-operation js-clip" data-href="/eaterplanet_ecommerce/moduleB/__plugin__/wx2b03c6e691cd7370/pages/live-player-plugin?room_id=<?php echo ($item['roomid']); ?>">
                                                            <span data-toggle="tooltip" data-placement="top"  data-original-title="复制直播间地址">
                                                               复制直播间地址
                                                            </span>
                                                        </a> -->
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php echo ($pager); ?>
                                        <?php  }else{ ?>
                                        <div class="text-center">暂时没有任何直播间!</div>
                                        <?php } ?>
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

        $("#refresh").click(function(){
            var s_url = $(this).data('href');
            var loading = layer.load(0, { shade: false });
            $.ajax({
                url:s_url,
                type:'post',
                dataType:'json',
                data: '',
                success:function(info){
                    layer.close(loading);
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
                                location.href = go_url;
                            }
                        });
                    }
                }
            })
        });

        form.on('switch(statewsitch)', function(data){
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
                data:{value:s_value},
                success: function(info){
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



</script>
</body>
</html>