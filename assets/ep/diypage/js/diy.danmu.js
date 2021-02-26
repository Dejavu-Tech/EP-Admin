define(['jquery.ui'], function (ui) {
    var modal = {
        default: {
            params: {
                'isopen': '0',
                'style': '0',
                'datatype': '0',
                'starttime': '5',
                'endtime': '60'
            },
            style: {
                'background': '#000000',
                'color': '#ffffff',
                'opacity': '0.7'
            },
            data: [
                {
                    'imgurl': '../addons/ewei_shopv2/assets/ep/images/nopic100.jpg',
                    'nickname': '用户昵称',
                    'time': '5'
                }
            ]
        }
    };
    modal.init = function (params) {
        window.tpl = params.tpl;
        modal.attachurl = params.attachurl;
        modal.danmu = params.danmu;
        modal.merch = params.merch;

        if (!modal.danmu) {
            modal.danmu = modal.default
        }
        tpl.helper("imgsrc", function (src) {
            if (typeof src != 'string') {
                return ''
            }
            if (src.indexOf('http://') == 0 || src.indexOf('https://') == 0 || src.indexOf('../addons') == 0) {
                return src
            } else if (src.indexOf('images/') == 0) {
                return modal.attachurl + src
            }
        });
        modal.initItems();
        modal.initEditor();
        $(".btn-save").unbind('click').click(function () {
            var status = $(this).data('status');
            if (status) {
                tip.msgbox.err("正在保存，请稍候。。。");
                return
            }
            modal.save()
        });
    }; 
    modal.initItems = function () {
        var danmu = modal.danmu;
        danmu.merch = modal.merch;
        var html = tpl("tpl_show_danmu", modal.danmu);
        $("#phone").html(html).show();
    };

    modal.initSortable = function () {
        $(".diy-editor .form-items .inner").sortable({
            opacity: 0.8,
            placeholder: "highlight",
            items: '.item',
            revert: 100,
            scroll: false,
            cancel: '.goods-selector,input,select,.btn,.btn-del,.three',
            start: function (event, ui) {
                var height = ui.item.height();
                $(".highlight").css({"height": height + 22 + "px"});
                $(".highlight").html('<div><i class="fa fa-plus"></i> 放置此处</div>');
                $(".highlight div").css({"line-height": height + 16 + "px"})
            },
            update: function (event, ui) {
                var  childType = ui.item.closest(".form-items").data('type');
                modal.sortChildItems(childType)
            }
        })
    };
    modal.sortChildItems = function () {

        var newArr = [];
        $("#form-items .item").each(function (i) {
            var index = $(this).data('index');
            var item = modal.danmu.data[index];
            if(item){
                newArr[i] = item;
            }
        });
        modal.danmu.data = newArr;
        modal.initItems();
        modal.initEditor()
    };

    modal.initEditor = function () {
        var html = tpl("tpl_edit_danmu", modal.danmu);
        $("#diy-editor .inner").html(html);
        $("#diy-editor .slider").each(function () {
            var decimal = $(this).data('decimal');
            var multiply = $(this).data('multiply');
            var defaultValue = $(this).data("value");
            if (decimal) {
                defaultValue = defaultValue * decimal
            }
            $(this).slider({
                slide: function (event, ui) {
                    var sliderValue = ui.value;
                    if (decimal) {
                        sliderValue = sliderValue / decimal
                    }
                    $(this).siblings(".input").val(sliderValue).trigger("propertychange");
                    $(this).siblings(".count").find("span").text(sliderValue)
                }, value: defaultValue, min: $(this).data("min"), max: $(this).data("max")
            })
        });
        $("#diy-editor").find(".diy-bind").bind('input propertychange change', function () {
            var _this = $(this);
            var bind = _this.data("bind");
            var bindchild = _this.data('bind-child');
            var bindparent = _this.data('bind-parent');
            var bindthree = _this.data('bind-three');
            var initEditor = _this.data('bind-init');
            var value = '';
            var tag = this.tagName;
            if (tag == 'INPUT') {
                var placeholder = _this.data('placeholder');
                value = _this.val();
                value = value == '' ? placeholder : value
            } else if (tag == 'SELECT') {
                value = _this.find('option:selected').val()
            } else if (tag == 'TEXTAREA') {
                value = _this.val()
            }
            value = $.trim(value);
            if (bindchild) {
                if (bindparent || bindparent==0) {
                    if (bindthree) {
                        modal.danmu[bindchild][bindparent].child[bindthree][bind] = value
                    } else {
                        modal.danmu[bindchild][bindparent][bind] = value
                    }
                } else {
                    modal.danmu[bindchild][bind] = value
                }
            } else {
                modal.danmu[bind] = value
            }
            modal.initItems();
            if (initEditor) {
                modal.initEditor()
            }
        });
        $("#phone").mouseenter(function () {
            $("#diy-editor").find('.diy-bind').blur()
        });

        $("#addChild").unbind('click').click(function () {
            var max = $(this).closest(".form-items").data('max');
            if(max){
                if(modal.danmu.data.length>=max){
                    tip.msgbox.err("最大添加 "+max+" 个！");
                    return
                }
            }
            var newChild = $.extend(true, {}, modal.default.data[0]);
            modal.danmu.data.push(newChild);
            modal.initItems();
            modal.initEditor();
        });

        $("#diy-editor .form-items .item .btn-del").unbind('click').click(function () {
            var index = $(this).closest(".item").data('index');
            var min = $(this).closest(".form-items").data("min");
            if (min) {
                if(!modal.danmu.data){
                    modal.danmu.data = [];
                }
                var length = modal.danmu.data.length;
                if (length <= min) {
                    tip.msgbox.err("至少保留 " + min + " 个！");
                    return
                }
            }
            tip.confirm("确定删除吗", function () {
                delete modal.danmu.data.splice(index, 1);
                modal.initItems();
                modal.initEditor()
            })
        });

        $("#diy-editor").show();
        modal.initSortable();
    };
    modal.save = function () {
        if (!modal.danmu) {
            tip.msgbox.err("数据错误，请刷新页面重试！");
            return
        }
        $(".btn-save").data('status', 1).text("保存中...");
        var posturl = biz.url("diypage/shop/danmu", null, modal.merch);
        $.post(posturl, {data: modal.danmu}, function (ret) {
            if (ret.status == 0) {
                tip.msgbox.err(ret.result.message);
                $(".btn-save").text("保存并设置").data("status", 0);
                return
            }
            tip.msgbox.suc("操作成功！");
            $(".btn-save").text("保存并设置").data("status", 0)
        }, 'json')
    };
    return modal
});
