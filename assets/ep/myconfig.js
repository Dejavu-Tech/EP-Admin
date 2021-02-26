var version = +new Date();
var myconfig = {
    path: '/assets/js/',
    alias: {
        'jquery': 'dist/jquery/jquery-1.11.1.min',
        'jquery.form': 'dist/jquery/jquery.form',
        'jquery.gcjs': 'dist/jquery/jquery.gcjs',
        'jquery.validate': 'dist/jquery/jquery.validate.min',
        'jquery.nestable': 'dist/jquery/nestable/jquery.nestable',
        'jquery.qrcode':'../dist/jquery/jquery.qrcode.min',
        'bootstrap': 'dist/bootstrap/bootstrap.min',
        'bootstrap.suggest': 'dist/bootstrap/bootstrap-suggest.min',
        'bootbox': 'dist/bootbox/bootbox.min',
        'sweet': 'dist/sweetalert/sweetalert.min',
        'select2': 'dist/select2/select2.min',
        'jquery.confirm': 'dist/jquery/confirm/jquery-confirm',
        'jquery.contextMenu': 'dist/jquery/contextMenu/jquery.contextMenu',
        'switchery': 'dist/switchery/switchery',
        'echarts': 'dist/echarts/echarts-all',
        'echarts.min': 'dist/echarts/echarts.min',
        'toast': 'dist/jquery/toastr.min',
        'clipboard': 'dist/clipboard.min',
        'tpl': 'dist/tmodjs',
        'daterangepicker': 'dist/daterangepicker/daterangepicker',
        'datetimepicker': 'dist/datetimepicker/jquery.datetimepicker',
        'ueditor': 'dist/ueditor/ueditor.parse.min',
        'tooltipbox': 'dist/tooltipbox',

    },
    map: {
        'js': '.js?v=' + version,
        'css': '.css?v=' + version
    },
    css: {
        'jquery.confirm': 'dist/jquery/confirm/jquery-confirm',
        'sweet': 'dist/sweetalert/sweetalert',
        'select2': 'dist/select2/select2,dist/select2/select2-bootstrap',
        'jquery.nestable': 'dist/jquery/nestable/nestable',
        'jquery.contextMenu': 'dist/jquery/contextMenu/jquery.contextMenu',
        // 'daterangepicker': 'dist/daterangepicker/daterangepicker',
        // 'datetimepicker': 'dist/datetimepicker/jquery.datetimepicker',
        'ueditor': 'dist/ueditor/themes/default/css/ueditor.min',
        'switchery': 'dist/switchery/switchery'
    }
    , preload: ['jquery']

};

var myrequire = function (arr, callback) {
    var newarr = [];
    $.each(arr, function () {
        var js = this;

        if (myconfig.css[js]) {
            var css = myconfig.css[js].split(',');
            $.each(css, function () {
                if(typeof myrequire.systemVersion !== 'undefined'){
                    if (myrequire.systemVersion === '1.0.0' || myrequire.systemVersion <= '0.8')
                    {
                        newarr.push("css!" + myconfig.path + this + myconfig.map['css']);
                    }
                    else
                    {
                        newarr.push("loadcss!" + myconfig.path + this + myconfig.map['css']);
                    }
                }else{
                    newarr.push("css!" + myconfig.path + this + myconfig.map['css']);
                }
            });


        }

        var jsitem = this;
        if (myconfig.alias[js]) {
            jsitem = myconfig.alias[js];

        }
        newarr.push(myconfig.path + jsitem + myconfig.map['js']);
    });
    require(newarr, callback);
}
