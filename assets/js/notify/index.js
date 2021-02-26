'use strict';
var notify = $.notify('<img src="/assets/images/alert.png" alt=""><strong>加载</strong>请勿关闭此页...', {
    type: 'theme',
    allow_dismiss: true,
    delay: 2000,
    // showProgressbar: true,
    timer: 300
});

setTimeout(function() {
    notify.update('message', '<img src="/assets/images/alert.png" alt=""><strong>加载</strong>数据完毕.');
}, 1000);
