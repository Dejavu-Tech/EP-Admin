/**
 * Created by Administrator on 2021/2/27.
 */
var layer = layui.layer;
var $;
var cur_open_div;
layui.use(['jquery', 'layer','form'], function(){
    $ = layui.$;
    var form = layui.form;


    form.on('radio(invite_poster_qrcode_corner_type)',function(data){
        if(data.value == 1){//直角
            $('.qrcode').css('border-radius','0px');
            $('.qrcode img').css('border-radius','0px');
        }else if(data.value == 0){//圆角
            $('.qrcode').css('border-radius','10px');
            $('.qrcode img').css('border-radius','10px');
        }
    });

    form.on('radio(invite_poster_qrcode_border_status)',function(data){
        if(data.value == 1){//开启
            var border_color = $('input[name="invite_poster_qrcode_bordercolor"]').val();
            $('.qrcode').css('border','1px solid '+border_color);
        }else if(data.value == 0){//关闭
            $('.qrcode').css('border','none');
        }
    });
    //监听提交
    form.on('submit(formDemo)', function(data){
        var loadingIndex = layer.load();
        $.ajax({
            url: data.form.action,
            type: data.form.method,
            data: data.field,
            dataType:'json',
            success: function (info) {
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
        });
        return false;
    });
})
$('#invitePosterQrcodeBackColors').colpick({
    submit:true,
    color: invite_poster_qrcode_backcolor,
    onSubmit: function(color,color2){
        $('#invite_poster_qrcode_backcolor-input').val('#'+color2);
        $('#invitePosterQrcodeBackColors').find('.colorpicker-trigger-span').css('background','#'+color2);
        $('.colpick_full').hide();

        changeQrcode();
    }
});

$('#invitePosterQrcodeLineColors').colpick({
    submit:true,
    color: invite_poster_qrcode_linecolor,
    onSubmit: function(color,color2){
        $('#invite_poster_qrcode_linecolor-input').val('#'+color2);
        $('#invitePosterQrcodeLineColors').find('.colorpicker-trigger-span').css('background','#'+color2);
        $('.colpick_full').hide();

        changeQrcode();
    }
});

$('#invitePosterQrcodeBorderColors').colpick({
    submit:true,
    color: invite_poster_qrcode_bordercolor,
    onSubmit: function(color,color2){
        $('#invite_poster_qrcode_bordercolor-input').val('#'+color2);
        $('#invitePosterQrcodeBorderColors').find('.colorpicker-trigger-span').css('background','#'+color2);
        $('.colpick_full').hide();
        var invite_poster_qrcode_border_status = $('input[name="invite_poster_qrcode_border_status"]:checked').val();
        if(invite_poster_qrcode_border_status == 1){
            $('.qrcode').css('border','1px solid #'+color2);
        }
    }
});

$('input[name="invite_poster_qrcode_size"]').on('input propertychange', function() {
    var size = $(this).val();
    if(size != ''){
        var r = /^\+?[1-9][0-9]*$/;　　//判断是否为正整数
        if(!r.test(size)){
            $('input[name="invite_poster_qrcode_size"]').val('');
        }
        if(size > 1280){
            $('input[name="invite_poster_qrcode_size"]').val(1280);
        }
        size = Math.round(size/2);
        $('.qrcode').css('width', size+"px");
        $('.qrcode').css('height', size+"px");
        var top = $('input[name="invite_poster_qrcode_top"]').val();
        var left = $('input[name="invite_poster_qrcode_left"]').val();
        if(!r.test(top)){
            top = 0;
        }
        if(!r.test(left)){
            left = 0;
        }
        if(size > 375){
            left = 0;
        }else{
            if(Math.round(left/2)+size > 375){
                left = Math.round(375 - size);
            }else{
                left = Math.round(left/2);
            }
        }
        if(size > 667){
            top = 0;
        }else{
            if(Math.round(top/2)+size > 667){
                top = Math.round(667 - size);
            }else{
                top = Math.round(top/2);
            }
        }
        $('.back_body .qrcode').each(function(index){
            $(this).myDrag({
                parent: '.back_body',
                randomPosition: false, //初始位置是否随机
                direction:'all', //方向
                handler:false, //把手
                dragStart:function(x,y){

                }, //拖动开始 x,y为当前坐标
                dragEnd:function(x,y){
                    $("input[name='invite_poster_qrcode_top']").val(y*2);
                    $("input[name='invite_poster_qrcode_left']").val(x*2);
                }, //拖动停止 x,y为当前坐标
                dragMove:function(x,y){

                } //拖动进行中 x,y为当前坐标
            });
        });
        $('.qrcode').css('top', top+'px');
        $('.qrcode').css('left', left+'px');
        $('input[name="invite_poster_qrcode_top"]').val(top*2);
        $('input[name="invite_poster_qrcode_left"]').val(left*2);
    }
});

var default_color = "#ffffff";
$('.resetQrcodeBackColor').click(function(){
    $('#invite_poster_qrcode_backcolor-input').val(default_color);
    $('#invitePosterQrcodeBackColors').find('.colorpicker-trigger-span').css('background',default_color);
    $('.colpick_full').hide();
    changeQrcode();
});

var line_color = "#000000";
$('.resetQrcodeLineColor').click(function(){
    $('#invite_poster_qrcode_linecolor-input').val(line_color);
    $('#invitePosterQrcodeLineColors').find('.colorpicker-trigger-span').css('background',line_color);
    $('.colpick_full').hide();
    changeQrcode();
});

var border_color = "#000000";
$('.resetQrcodeBorderColor').click(function(){
    $('#invite_poster_qrcode_bordercolor-input').val(border_color);
    $('#invitePosterQrcodeBorderColors').find('.colorpicker-trigger-span').css('background',border_color);
    $('.colpick_full').hide();
    var invite_poster_qrcode_border_status = $('input[name="invite_poster_qrcode_border_status"]:checked').val();
    if(invite_poster_qrcode_border_status == 1){
        $('.qrcode').css('border','1px solid '+border_color);
    }
});

$('.back_body .qrcode').each(function(index){
    $(this).myDrag({
        parent: '.back_body',
        randomPosition: false, //初始位置是否随机
        direction:'all', //方向
        handler:false, //把手
        dragStart:function(x,y){

        }, //拖动开始 x,y为当前坐标
        dragEnd:function(x,y){
            $("input[name='invite_poster_qrcode_top']").val(y*2);
            $("input[name='invite_poster_qrcode_left']").val(x*2);
        }, //拖动停止 x,y为当前坐标
        dragMove:function(x,y){

        } //拖动进行中 x,y为当前坐标
    });
    $('.qrcode').css('top',qrcode_top+'px');
    $('.qrcode').css('left',qrcode_left+'px');
});

function changeQrcode(){
    var qrcode_backcolor = $('input[name="invite_poster_qrcode_backcolor"]').val();
    var qrcode_linecolor = $('input[name="invite_poster_qrcode_linecolor"]').val();
    $.ajax({
        url: change_url,
        type: "post",
        data: {back_color: qrcode_backcolor, line_color : qrcode_linecolor},
        dataType:'json',
        success: function (result) {
            $('.qrcode img').attr('src', result.result.invite_poster_qrcode_img);
            $('input[name="invite_poster_qrcode_img"]').val(result.result.invite_poster_qrcode_img_yuan);
        }
    });
}
