var version = +new Date();
require.config({
    urlArgs: 'v=' + version, 
    baseUrl: '/assets/js/app',
    paths: {
        'jquery': '../dist/jquery/jquery-1.11.1.min',
        'jquery.gcjs': '../dist/jquery/jquery.gcjs',
        'tpl':'../dist/tmodjs',
        'foxui':'../dist/foxui/js/foxui.min',
        'foxui.picker':'../dist/foxui/js/foxui.picker.min',
        'foxui.citydata':'../dist/foxui/js/foxui.citydata.min',
        'foxui.citydatanew':'../dist/foxui/js/foxui.citydatanew.min',
        'foxui.street':'../dist/foxui/js/foxui.street.min',
        'jquery.qrcode':'../dist/jquery/jquery.qrcode.min',
        'ydb':'../dist/Ydb/YdbOnline',
        'swiper':'../dist/swiper/swiper.min',
        'jquery.fly': '../dist/jquery/jquery.fly',

    },
    shim: {
        'foxui':{
            deps:['jquery']
        },
        'foxui.picker': {
            exports: "foxui",
            deps: ['foxui','foxui.citydata']
        },
		'jquery.gcjs': {
	                 deps:['jquery']
		},
		'jquery.fly': {
	                 deps:['jquery']
		}
    },
    waitSeconds: 0
});
