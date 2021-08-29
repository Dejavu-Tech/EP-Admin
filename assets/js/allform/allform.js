/**
 * Created by Administrator on 2021/5/14.
 */
var cur_open_div;
var child_index;
var layer = layui.layer;
layui.use(['jquery', 'layer','form','laydate'], function(){
    $ = layui.$;
    var form = layui.form;
    var laydate = layui.laydate;

    form.on('submit(formDemo)', function(data){
        var loadingIndex = layer.load();
        $.ajax({
            url: data.form.action,
            type: data.form.method,
            data: data.field,
            dataType:'json',
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            success: function (info) {
                console.log(data.field);
                layer.close(loadingIndex);
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

    $('.form-config').delegate(".deleteBtn","click",function(){
        $(this).parent().parent().parent().remove();
        var random_code = $(this).attr('random_code');
        $('#phone_item_'+random_code).remove();
        if($('.phone-item').length > 0){
            $('.no_element').hide();
        }else{
            $('.no_element').show();
        }
    });

    $('.form-config').delegate(".delOptionBtn","click",function(){
        var obj = $(this).parent().parent().parent();
        $(this).parent().parent().remove();
        var random_code = $(this).attr('random_code');
        var num = $(this).attr('num');
        updateOptionSort(obj);
        $('#radio-item'+num+'_'+random_code).remove();
    });

    $('.form-config').delegate(".addOptionBtn","click",function(){
        $(this).parent().parent().find('.delOptionBtn').removeClass("disabled");
        var random_code = $(this).attr('random_code');
        var sort = $(this).parent().parent().find('.sort');
        var type = $(this).attr('type');
        var num = sort.length + 1;
        if(type == 'radio'){
            var html = '<div class="input-middle-item">'
                + '<div class="input-group">'
                + '<div class="input-group-addon">选项<span class="sort">'+num+'</span></div>'
                + '<input class="form-control" name="option_val_'+random_code+'[]" type="text" placeholder="请输入" value="选项'+num+'" style="width: 430px;" update_id="#radio-item'+num+'_'+random_code+'" update_type="radio">&nbsp;'
                + '<a class="delOptionBtn" href="javascript:;" num="'+num+'" random_code="'+random_code+'">删除</a></div></div>';
            var phone_html = '<div class="radio-item" id="radio-item'+num+'_'+random_code+'">'
                + '<input type="radio" class="radio_option" name="radio" value="选项'+num+'" title="选项'+num+'">'
                + '</div>';
            $(this).parent().before(html);
            $('#m-radio-box_'+random_code).append(phone_html);
        }if(type == 'select'){
            var html = '<div class="input-middle-item">'
                + '<div class="input-group">'
                + '<div class="input-group-addon">选项<span class="sort">'+num+'</span></div>'
                + '<input class="form-control" name="option_val_'+random_code+'[]" type="text" placeholder="请输入" value="选项'+num+'" style="width: 430px;" update_id="#radio-item'+num+'_'+random_code+'" update_type="radio">&nbsp;'
                + '<a class="delOptionBtn" href="javascript:;" num="'+num+'" random_code="'+random_code+'">删除</a></div></div>';

            $(this).parent().before(html);
        }else if(type == 'checkbox'){
            var html = '<div class="input-middle-item">'
                + '<div class="input-group">'
                + '<div class="input-group-addon">选项<span class="sort">'+num+'</span></div>'
                + '<input class="form-control" name="option_val_'+random_code+'[]" type="text" placeholder="请输入" value="选项'+num+'" style="width: 430px;" update_id="#radio-item'+num+'_'+random_code+'" update_type="radio">&nbsp;'
                + '<a class="delOptionBtn" href="javascript:;" num="'+num+'" random_code="'+random_code+'">删除</a></div></div>';
            var phone_html =  '<div class="radio-item" id="radio-item'+num+'_'+random_code+'">'
                + ' <input type="checkbox" class="radio_option" name="checkbox" title="选项'+num+'" value="选项'+num+'" lay-skin="primary">'
                + '</div>';
            $(this).parent().before(html);
            $('#m-radio-box_'+random_code).append(phone_html);
        }
        form.render();
    })

    $('.act_btn').click(function(){
        var type = $(this).attr('type');
        var loadingIndex = layer.load();
        $.ajax({
            url: add_item_url,
            data:{type : type},
            dataType: 'json',
            success:function(result)
            {
                var data = result.result.data;
                var random_code = data.random_code;
                var item_li = data.item_li;
                var phone_item = data.phone_item;
                layer.close(loadingIndex);
                $('#form_config_ul').append(item_li);
                $('.phone-main-content').append(phone_item);
                form.render(null, 'phone_item_'+random_code);
                form.render(null, 'action_li_'+random_code);
                if($('.phone-item').length > 0){
                    $('.no_element').hide();
                }else{
                    $('.no_element').show();
                }
            }
        })
    })

    $('.form-config').delegate(".action-items input","input propertychange",function(){
        var val = $(this).val();
        var update_id = $(this).attr('update_id');
        var type = $(this).attr('update_type');
        if(type == 'html'){
            $(update_id).html(val);
        }else if(type == 'value'){
            $(update_id).val(val);
        }else if(type == 'tip'){
            $(update_id).attr('placeholder', val);
        }else if(type == 'select_tip'){
            $(update_id).attr('placeholder', val);
            form.render();
            $(update_id).parent().find('.layui-select-title').find('.layui-input').attr('placeholder', val);
        }else if(type == 'select_area_tip'){
            var random_code = $(this).attr('random_code');
            var province_id = $('select[name="province_id_'+random_code+'"]').find("option:selected").val();
            if(province_id == '' || province_id.indexOf("请选择") >= 0){
                $(update_id).attr('placeholder', val);
                $(update_id).parent().find('.layui-select-title').find('.layui-input').attr('placeholder', val);
            }
        }else if(type == 'radio'){
            $(update_id).find('.radio_option').attr('title', val);
            $(update_id).find('.radio_option').val(val);
            form.render();
        }
    });
})

//更新选项排序
function updateOptionSort(obj){
    var sort = obj.find('.sort');
    var count = sort.length;
    var num = 1;
    obj.find('.sort').each(function(){
        $(this).html(num);
        num = num + 1;
        if(count == 1){
            $(this).parent().parent().find('.delOptionBtn').addClass("disabled");
        }
    })
}
//更新地址信息
function updateArea(random_code){
    var area_type = $('input[name="area_type_'+random_code+'"]:checked').val();
    var province_id = $('select[name="province_id_'+random_code+'"]').find("option:selected").val();
    var province_text = $('select[name="province_id_'+random_code+'"]').find("option:selected").text();
    var city_id = $('select[name="city_id_'+random_code+'"]').find("option:selected").val();
    var city_text = $('select[name="city_id_'+random_code+'"]').find("option:selected").text();
    var country_id = $('select[name="country_id_'+random_code+'"]').find("option:selected").val();
    var country_text = $('select[name="country_id_'+random_code+'"]').find("option:selected").text();
    var address = "";
    if(province_id != '' && province_id.indexOf("请选择") == -1){
        address = address + province_text;
    }
    if(city_id != '' && (area_type == 'city' || area_type == 'country')  && city_id.indexOf("请选择") == -1){
        address = address + city_text;
    }
    if(country_id != '' && (area_type == 'country') && country_id.indexOf("请选择") == -1){
        address = address + country_text;
    }
    if(address == ''){
        address = $('input[name="hint_'+random_code+'"]').val();
    }
    $('#address_'+random_code).attr('placeholder', address);
    //form.render(null, 'phone_item_'+random_code);
    //form.render(null, 'action_li_'+random_code);
    $('#address_'+random_code).parent().find('.layui-select-title').find('.layui-input').attr('placeholder', address);
}