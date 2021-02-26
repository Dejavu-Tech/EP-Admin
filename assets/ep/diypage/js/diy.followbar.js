define(['jquery.ui'], function (ui) {
    var modal = {};
    modal.init = function (params) {
        window.tpl = params.tpl;
        modal.attachurl = params.attachurl;
        modal.followbar = params.followbar;
        modal.merch = params.merch;

        if (!modal.followbar) {
            modal.followbar = {
                params: {
                    'logo': '',

                    'isopen': '0',
                    'icontype': '1',
                    'iconurl': '',
                    'iconstyle': '',
                    'defaulttext': '',
                    'sharetext': '',
                    'btntext': '点击关注',
                    'btnicon':'',
                    'btnclick': '0',
                    'btnlinktype': '0',
                    'btnlink': '',
                    'qrcodetype': '0',
                    'qrcodeurl': ''
                },
                style: {
                    'background': '#444444',
                    'textcolor': '#ffffff',
                    'btnbgcolor': '#04be02',
                    'btncolor': '#ffffff',
                    'highlight': '#ffffff'
                }
            }
        }

        modal.followbar.params.logo = params.logo;

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
        })
    };
    modal.initItems = function () {
        var followbar = modal.followbar;
        followbar.merch = modal.merch;
        var html = tpl("tpl_show_followbar", modal.followbar);
        $("#phone").html(html).show()
    };
    modal.initEditor = function () {
        var html = tpl("tpl_edit_followbar", modal.followbar);
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
                if (bindparent) {
                    if (bindthree) {
                        modal.followbar[bindchild][bindparent].child[bindthree][bind] = value
                    } else {
                        modal.followbar[bindchild][bindparent][bind] = value
                    }
                } else {
                    modal.followbar[bindchild][bind] = value
                }
            } else {
                modal.followbar[bind] = value
            }
            modal.initItems();
            if (initEditor) {
                modal.initEditor()
            }
        });
        $("#phone").mouseenter(function () {
            $("#diy-editor").find('.diy-bind').blur()
        });
        $("#diy-editor").show()
    };
    modal.save = function () {
        if (!modal.followbar) {
            tip.msgbox.err("数据错误，请刷新页面重试！");
            return
        }
        $(".btn-save").data('status', 1).text("保存中...");
        var posturl = biz.url("diypage/shop/followbar", null, modal.merch);
        $.post(posturl, {data: modal.followbar}, function (ret) {
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