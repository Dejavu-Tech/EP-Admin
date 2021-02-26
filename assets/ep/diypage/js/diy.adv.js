define(['jquery.ui'], function (ui) {
    var modal = {itemid: ''};
    modal.init = function (params) {
        window.tpl = params.tpl;
        modal.attachurl = params.attachurl;
        modal.advs = params.menu;
        modal.id = params.id;
        modal.merch = params.merch;
        if (!modal.advs) {
            modal.advs = {
                name: '未命名启动广告',
                status: 0,
                params: {
                    'style': 'small-bot',
                    'showtype': '1',
                    'showtime': '60',
                    'autoclose': '10',
                    'canclose': '1'
                },
                style: {
                    'background': '#000000',
                    'opacity': '0.6'
                },
                data: {
                    M0123456789101: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/assets/ep/images/default/adv-1.jpg',
                        linkurl: '',
                        click: '0'
                    },
                    M0123456789102: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/assets/ep/images/default/adv-2.jpg',
                        linkurl: '',
                        click: '0'
                    },
                    M0123456789103: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/assets/ep/images/default/adv-3.jpg',
                        linkurl: '',
                        click: '0'
                    }
                }
            }
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
        tpl.helper("count", function (data) {
            return modal.length(data)
        });
        tpl.helper("link", function (link) {
            if (!link) {
                return
            }
            return '../app/' + link
        });
        tpl.helper("px", function (num) {
            return num / 20
        });
        modal.initItems();
        modal.initEditor();
        modal.initGotop();
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
        var html = tpl("tpl_show_menu", modal.advs);
        $("#phone").html(html).show();
        var len = $(".diymenu .child").length;
        $(".diymenu .child").each(function (i) {
            var width = $(this).outerWidth();
            var margin = -(width / 2);
            var left = '50%';
            var pleft = $(this).position().left - width / 2;
            if(i==0 && pleft<2){
                left = 2;
                margin = 0;
                var pwidth = $(this).closest('.item').width();
                var arrowleft = pwidth / 2;
                var oldleft = parseFloat($(this).find('.arrow').css('left').replace('px', ''));
                $(this).find('.arrow').css({'left': arrowleft - 10, 'margin-left': 0})
            } else if (i + 1 == len) {
                var pwidth = $(this).closest('.item').width();
                if(width>pwidth){
                    var left =  - (width - pwidth) - 2;
                    margin = 0;
                    var c = $(this).closest('.item').width() / 2;
                    var arrowleft = width - c;
                    $(this).find('.arrow').css({'left': arrowleft - 8, 'margin-left': 0})
                }
            }
            $(this).css({'position': 'absolute', 'left': left, 'margin-left': margin, 'z-index': 0})
        })
    };
    modal.initSortable = function () {
        $("#diy-editor .inner").sortable({
            opacity: 0.8,
            placeholder: "highlight",
            items: '.item',
            revert: 100,
            scroll: false,
            cancel: '.goods-selector,input,.btn',
            start: function (event, ui) {
                var height = ui.item.height();
                $(".highlight").css({"height": height + 22 + "px"});
                $(".highlight").html('<div><i class="fa fa-plus"></i> 放置此处</div>');
                $(".highlight div").css({"line-height": height + 16 + "px"})
            },
            update: function (event, ui) {
                modal.sortItems()
            }
        });
        $("#diy-editor .inner .item-child").sortable({
            opacity: 0.8,
            placeholder: "highlight",
            items: '.item-body',
            revert: 100,
            scroll: false,
            cancel: '.goods-selector,input,.btn',
            start: function (event, ui) {
                var height = ui.item.height();
                $(".highlight").css({"height": height + "px"});
                $(".highlight").html('<div><i class="fa fa-plus"></i> 放置此处</div>');
                $(".highlight div").css({"line-height": height + 16 + "px"})
            },
            update: function (event, ui) {
                modal.sortChild()
            }
        })
    };
    modal.sortItems = function () {
        var newItems = {};
        $("#diy-editor .inner .item").each(function () {
            var thisid = $(this).data('id');
            newItems[thisid] = modal.advs.data[thisid]
        });
        modal.advs.data = newItems;
        modal.initItems()
    };
    modal.sortChild = function () {
        var newChild = {};
        var itemid = modal.itemid;
        $("#diy-editor .inner").find(".item[data-id='" + itemid + "'] .item-child .child").each(function () {
            var thisid = $(this).data('id');
            newChild[thisid] = modal.advs.data[itemid].child[thisid]
        });
        modal.advs.data[itemid].child = newChild;
        modal.initItems()
    };
    modal.initEditor = function () {
        var html = tpl("tpl_edit_menu", modal.advs);
        $("#diy-editor .inner").html(html);
        $("#diy-editor #addChild").unbind('click').click(function () {
            var itemid = $(this).closest('.item').data('id');
            var childid = modal.getId('C', 0);
            if (!modal.advs.data[itemid].child) {
                modal.advs.data[itemid].child = {}
            }
            modal.advs.data[itemid].child[childid] = {linkurl: '', text: '二级菜单'};
            modal.initItems();
            modal.initEditor()
        });
        $("#diy-editor #addItem").unbind('click').click(function () {
            var itemid = modal.getId('M', 0);
            var max = $(this).closest('.form-items').data('max');
            var num = modal.length(modal.advs.data);
            if (num >= max) {
                tip.msgbox.err("最大添加 " + max + " 个！");
                return
            }
            modal.advs.data[itemid] = {
                imgurl: '../addons/ewei_shopv2/plugin/diypage/assets/ep/images/default/menu-1.png',
                linkurl: '',
                iconclass: 'icon-home',
                text: '菜单文字'
            };
            modal.initItems();
            modal.initEditor()
        });
        $("#diy-editor .del-item").unbind('click').click(function () {
            var min = $(this).closest('.form-items').data('min');
            var itemid = $(this).closest('.item').data('id');
            if (min) {
                var length = modal.length(modal.advs.data);
                if (length <= min) {
                    tip.msgbox.err("至少保留 " + min + " 个！");
                    return
                }
            }
            tip.confirm("确定删除吗", function () {
                delete modal.advs.data[itemid];
                modal.initItems();
                modal.initEditor()
            })
        });
        $("#diy-editor .del-child").unbind('click').click(function () {
            var itemid = $(this).closest('.item').data('id');
            var childid = $(this).closest('.child').data('id');
            var item = modal.advs.data[itemid];
            if (item) {
                var child = modal.advs.data[itemid].child[childid];
                if (child) {
                    tip.confirm("确定删除吗", function () {
                        delete modal.advs.data[itemid].child[childid];
                        modal.initItems();
                        modal.initEditor()
                    })
                }
            }
        });
        $("#diy-editor .fold").unbind('click').click(function () {
            var type = $(this).data('type');
            if (type == 1) {
                $(this).text('收起').data('type', 0).closest('.item').find('.item-child').show()
            } else {
                $(this).text('展开').data('type', 1).closest('.item').find('.item-child').hide()
            }
        });
        $(document).on('mousedown', "#diy-editor .item-child .child", function () {
            var itemid = $(this).closest('.item').data('id');
            modal.itemid = itemid
        });
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
                        modal.advs[bindchild][bindparent].child[bindthree][bind] = value
                    } else {
                        modal.advs[bindchild][bindparent][bind] = value
                    }
                } else {
                    modal.advs[bindchild][bind] = value
                }
            } else {
                modal.advs[bind] = value
            }
            modal.initItems();
            if (initEditor) {
                modal.initEditor()
            }
        });
        $("#phone").mouseenter(function () {
            $("#diy-editor").find('.diy-bind').blur()
        });
        $("#diy-editor").show();
        modal.initSortable()
    };
    modal.initGotop = function () {
        $(window).bind('scroll resize', function () {
            var scrolltop = $(window).scrollTop();
            if (scrolltop > 100) {
                $("#gotop").show()
            } else {
                $("#gotop").hide()
            }
            $("#gotop").unbind('click').click(function () {
                $('body').animate({scrollTop: "0px"}, 1000)
            })
        })
    };
    modal.length = function (json) {
        if (typeof(json) === 'undefined') {
            return 0
        }
        var jsonlen = 0;
        for (var item in json) {
            jsonlen++
        }
        return jsonlen
    };
    modal.getId = function (S, N) {
        var date = +new Date();
        var id = S + (date + N);
        return id
    };
    modal.save = function () {
        if (!modal.advs.data) {
            tip.msgbox.err("广告内容为空！");
            return
        }
        $(".btn-save").data('status', 1).text("保存中...");
        if (modal.id) {
            var posturl = biz.url("diypage/shop/adv/edit", null, modal.merch)
        } else {
            var posturl = biz.url("diypage/shop/adv/add", null, modal.merch)
        }
        $.post(posturl, {id: modal.id, advs: modal.advs}, function (ret) {
            if (ret.status == 0) {
                tip.msgbox.err(ret.result.message);
                $(".btn-save").text("保存广告").data("status", 0);
                return
            }
            tip.msgbox.suc("保存成功！");
            $(".btn-save").text("保存广告").data("status", 0);
            var menuid = ret.result.id;
            if (menuid != modal.id) {
                location.href = ret.result.url + '&id=' + menuid
            }
        }, 'json')
    };
    return modal
});
