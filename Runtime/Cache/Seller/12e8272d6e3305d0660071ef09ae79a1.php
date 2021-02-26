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
                            <h2>后台管理员<span>列表</span></h2>
                        </div>
                        <div class="col-lg-6 breadcrumb-right" style="float: right">
                            <ol class="breadcrumb">
                                <a href="<?php echo U('perm/adduser', array('ok' => 1));?>"><button class="btn btn-info btn-air-info" style=""><i class="fa fa-plus"></i>添加后台管理员</button></a>
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
                                    <input type="hidden" name="c" value="perm" />
                                    <input type="hidden" name="a" value="user" />

                                    <div class="form-row">
                                        <div class="p-r-5">
                                            <select name="roleid" class='form-control'>
                                                <option value="" <?php if($gpc['roleid']==''){ ?> selected<?php } ?>>权限组</option>
                                                <option value="0" <?php if($gpc['roleid']=='0'){ ?> selected<?php } ?>>无权限组</option>
                                                <?php foreach($roles as $role){ ?>
                                                <option value="<?php echo ($role['id']); ?>" <?php if($gpc['roleid']== $role['id']){ ?> selected<?php } ?>><?php echo ($role['rolename']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="p-r-5">
                                            <select name="status" class='form-control '>
                                                <option value="" <?php if($gpc['status'] == ''){ ?> selected<?php } ?>>状态</option>
                                                <option value="1" <?php if($gpc['status']== '1'){ ?> selected<?php } ?>>启用</option>
                                                <option value="0" <?php if($gpc['status'] == '0'){ ?> selected<?php } ?>>禁用</option>
                                            </select>
                                        </div>
                                        <div class="p-r-5">
                                            <input type="text" class="form-control" name='keyword' value="<?php echo ($gpc['keyword']); ?>" placeholder="请输入关键词">
                                        </div>
                                        <div class="p-r-5">
                                            <button class="btn btn-primary" type="submit"> 搜索</button>
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
                                                <button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch' data-href="<?php echo U('perm/userstatus',array('s_status'=>1));?>">启用</button>
                                                <button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch' data-href="<?php echo U('perm/userstatus',array('s_status'=>0));?>">禁用</button>
                                                <button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch-remove' data-confirm="确认要删除吗?" data-href="<?php echo U('perm/userdelete');?>">删除</button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="display dataTable text-center" lay-even lay-skin="line" lay-size="lg">
                                                <thead>
                                                <tr>
                                                    <th>选择</th>
                                                    <th>登录ID</th>
                                                    <th>用户名</th>
                                                    <th>权限组</th>
                                                    <th>手机</th>
                                                    <th>禁用|启用</th>
                                                    <th>操作</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($list as $row){ ?>
                                                <tr>
                                                    <td>
                                                        <?php if($row['s_id'] != 1){ ?>
                                                        <div class="checkbox checkbox-primary m-l-10" >
                                                            <input type='checkbox' name="item_checkbox" lay-skin="primary" value="<?php echo ($row['s_id']); ?>" style="display: none"/>
                                                        </div>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo ($row['s_id']); ?></td>
                                                    <td><?php echo ($row['s_uname']); if($row['s_id'] == 1){ ?><div class="txt-warning">(平台管理员)</div><div class="txt-danger">不可禁用</div><?php } ?></td>
                                                    <td><?php echo !empty($row['rolename'])?$row['rolename']:'无'; ?></td>
                                                    <td><?php echo ($row['s_mobile']); ?></td>
                                                    <td>
                                                        <label class="switch" style="margin-bottom: 0">
                                                            <input type="checkbox" name="" <?php if($row['s_id'] == 1){ ?> disabled="disabled" <?php } ?>  lay-filter="statewsitch" data-href="<?php echo U('perm/userstatus',array('id'=>$row['s_id']));?>" <?php if( $row['s_status']==1){ ?>checked<?php  }else{ } ?> lay-skin="switch" >
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-pill btn-primary btn-sm" href="<?php echo U('perm/adduser', array('id' => $row['s_id']));?>" >
                                                            编辑
                                                        </a>
                                                        <?php if($row['s_id'] != 1){ ?>
                                                        <a class="btn btn-pill btn-primary btn-sm deldom" href="javascript:;" data-href="<?php echo U('perm/userdelete', array('id' => $row['s_id']));?>" data-confirm='确认要删除吗?'>
                                                            删除
                                                        </a>
                                                        <?php } ?>
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
                                                <button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch' data-href="<?php echo U('perm/userstatus',array('s_status'=>1));?>">启用</button>
                                                <button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch' data-href="<?php echo U('perm/userstatus',array('s_status'=>0));?>">禁用</button>
                                                <button class="btn btn-pill btn-primary btn-sm  btn-operation"  type="button" data-toggle='batch-remove' data-confirm="确认要删除吗?" data-href="<?php echo U('perm/userdelete');?>">删除</button>
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
                data:{s_status:s_value},
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
<script language='javascript'>
    function search_users() {
        $("#module-menus1").html("正在搜索....")
        $.get('<?php echo U('perm/user',array('op'=>'query'));;?>', {
            keyword: $.trim($('#search-kwd1').val())
        }, function(dat){
            $('#module-menus1').html(dat);
        });
    }
    function select_user(o) {
        $("#userid").val(o.id);
        $("#user").val( o.username );
        var perms = o.perms.split(',');
        $(':checkbox')
        $(':checkbox').removeAttr('disabled').removeAttr('checked').each(function(){

            var _this = $(this);
            var perm = '';
            if( _this.data('group') ){
                perm+=_this.data('group');
            }
            if( _this.data('child') ){
                perm+="." +_this.data('child');
            }
            if( _this.data('op') ){
                perm+="." +_this.data('op');
            }
            if( $.arrayIndexOf(perms,perm)!=-1){
                $(this).attr('disabled',true).get(0).checked =true;
            }

        });
        $(".close").click();
    }
</script>
</body>
</html>