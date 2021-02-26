define(['jquery', 'foxui', 'tpl'], function ($, FoxUI, tpl) {
    window.FoxUI = FoxUI;
    var defaults = {baseUrl: '', siteUrl: '', staticUrl: '../addons/ewei_shopv2/assets/ep/'};
    var core = {options: {}};
    core.init = function (options) {
        this.options = $.extend({}, defaults, options || {})
    };
    core.toQueryPair = function (key, value) {
        if (typeof value == 'undefined') {
            return key
        }
        return key + '=' + encodeURIComponent(value === null ? '' : String(value))
    };
    core.number_format = function (number, fix) {
        var fix = arguments[1] ? arguments[1] : 2;
        var fh = ',';
        var jg = 3;
        var str = '';
        number = number.toFixed(fix);
        zsw = number.split('.')[0];
        xsw = number.split('.')[1];
        zswarr = zsw.split('');
        for (var i = 1; i <= zswarr.length; i++) {
            str = zswarr[zswarr.length - i] + str;
            if (i % jg == 0) {
                str = fh + str;
            }
        }
        str = (zsw.length % jg == 0) ? str.substr(1) : str;
        zsw = str + '.' + xsw;
        return zsw
    };
    core.toQueryString = function (obj) {
        var ret = [];
        for (var key in obj) {
            key = encodeURIComponent(key);
            var values = obj[key];
            if (values && values.constructor == Array) {
                var queryValues = [];
                for (var i = 0, len = values.length, value; i < len; i++) {
                    value = values[i];
                    queryValues.push(this.toQueryPair(key, value))
                }
                ret = ret.concat(queryValues)
            } else {
                ret.push(this.toQueryPair(key, values))
            }
        }
        return ret.join('&')
    };
    core.getUrl = function (routes, params, full) {
        routes = routes.replace(/\//ig, ".");
        var url = this.options.baseUrl.replace('ROUTES', routes);
        if (params) {
            if (typeof(params) == 'object') {
                url += "&" + this.toQueryString(params)
            } else if (typeof(params) == 'string') {
                url += "&" + params
            }
        }
        return full ? this.options.siteUrl + 'app/' + url : url
    };
    core.json = function (routes, args, callback, hasloading, ispost) {
        var url = ispost ? this.getUrl(routes) : this.getUrl(routes, args);
        var op = {
            url: url, type: ispost ? 'post' : 'get', dataType: 'json', cache: false, beforeSend: function () {
                if (hasloading) {
                    FoxUI.loader.show('mini')
                }
            }, error: function (a) {
                /*alert(JSON.stringify(a));*/
                if (hasloading) {
                    FoxUI.loader.hide()
                }
            }
        };
        if (args && ispost) {
            op.data = args
        }
        if (callback) {
            op.success = function (data) {
                if (hasloading) {
                    FoxUI.loader.hide()
                }
                callback(data)
            }
        }
        $.ajax(op)
    };
    core.post = function (routes, args, callback, hasloading) {
        this.json(routes, args, callback, hasloading, true)
    };
    core.html = function (routes, args, callback, hasloading, async) {
        if (typeof async === undefined) {
            async = true
        }
        var op = {
            url: this.getUrl(routes, args),
            type: 'get',
            cache: false,
            dataType: 'html',
            async: async,
            beforeSend: function () {
                if (hasloading) {
                    FoxUI.loader.show('mini')
                }
            },
            error: function () {
                core.removeLoading();
                if (hasloading) {
                    FoxUI.loader.hide()
                }
            }
        };
        if (callback) {
            op.success = function (html) {
                if (hasloading) {
                    FoxUI.loader.hide()
                }
                callback(html)
            }
        }
        $.ajax(op)
    };
    core.tpl = function (containerid, templateid, data, append) {
        if (typeof append === undefined) {
            append = false
        }
        var html = tpl(templateid, data);
        if (append) {
            $(containerid).append(html)
        } else {
            $(containerid).html(html)
        }
        setTimeout(function () {
            $(containerid).closest('.fui-content').lazyload('render')
        }, 10)
    };
    core.getNumber = function (str) {
        str = $.trim(str);
        if (str == '') {
            return 0
        }
        return parseFloat(str.replace(',', ''))
    };
    core.showIframe = function (url) {
        var if_w = "100%";
        var if_h = $(document.body).height();
        $("<iframe width='" + if_w + "' height='" + if_h + "' id='mainFrame' name='mainFrame' style='position:absolute;z-index:4;'  frameborder='no' marginheight='0' marginwidth='0' ></iframe>").prependTo('body');
        var st = document.documentElement.scrollTop || document.body.scrollTop;
        var sl = document.documentElement.scrollLeft || document.body.scrollLeft;
        var ch = document.documentElement.clientHeight;
        var cw = document.documentElement.clientWidth;
        var objH = $("#mainFrame").height();
        var objW = $("#mainFrame").width();
        var objT = Number(st) + (Number(ch) - Number(objH)) / 2;
        var objL = Number(sl) + (Number(cw) - Number(objW)) / 2;
        $("#mainFrame").css('left', objL);
        $("#mainFrame").css('top', objT);
        $("#mainFrame").attr("src", url)
    };
    core.getDistanceByLnglat = function (lng1, lat1, lng2, lat2) {
        function rad(d) {
            return d * Math.PI / 180.0
        }

        var rad1 = rad(lat1), rad2 = rad(lat2);
        var a = rad1 - rad2, b = rad(lng1) - rad(lng2);
        var s = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin(a / 2), 2) + Math.cos(rad1) * Math.cos(rad2) * Math.pow(Math.sin(b / 2), 2)));
        s = s * 6378137.0;
        s = Math.round(s * 10000) / 10000000;
        return s
    };
    core.showImages = function(imgClass){
        var ua = navigator.userAgent.toLowerCase();
        var isWX = ua.match(/MicroMessenger/i) == "micromessenger";
        var z = [];
        $(imgClass).each(function() {
            var img = $(this).attr("data-lazy");
            z.push(img?img:$(this).attr("src"));
        });
        var current;
        if (isWX) {
            $(imgClass).unbind('click').click(function(e) {
                e.preventDefault();
                var startingIndex = $(imgClass).index($(e.currentTarget));
                var current = null;
                $(imgClass).each(function(B, A) {
                    if (B === startingIndex) {
                        current = $(A).attr("data-lazy")?$(A).attr("data-lazy"):$(A).attr("src");
                    }
                });
                WeixinJSBridge.invoke("imagePreview", {
                    current: current,
                    urls: z
                });
            });
        }
    };
    core.ish5app = function () {
        var userAgent = navigator.userAgent;
        if (userAgent.indexOf('CK 2.0') > -1){
            return true;
        }
        return false;
    };
    core.isWeixin = function () {
        var ua = navigator.userAgent.toLowerCase();
        var isWX = ua.match(/MicroMessenger/i) == "micromessenger";
        return isWX;
    };

    window.core = core;
    return core
});
