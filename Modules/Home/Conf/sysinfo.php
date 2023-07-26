<?php
return array(
    'uniacid' => $_W['uniacid'],
    'acid' => $_W['acid'],
    'openid' => $_W['openid'],
    'uid' => $_W['uid'],
    'isfounder' => $_W['isfounder'] ? 1 : 0,
    'siteroot' => $_W['siteroot'],
    'siteurl' => $_W['siteurl'],
    'attachurl' => $_W['attachurl'],
    'attachurl_local' => $_W['attachurl_local'],
    'attachurl_remote' => $_W['attachurl_remote'],
    'module' => array(
        'url' => defined('MODULE_URL') ? MODULE_URL : '',
        'name' => defined('IN_MODULE') ? IN_MODULE : '',
    ),
    'cookie' => array(
        'pre' => $_W['config']['cookie']['pre'],
    ),
    'account' => $_W['account'],
    'window' => array(
        'sysinfo' => array(
            'cookie' => array(
                'pre' => $_W['config']['cookie']['pre'],
            ),
        ),
    ),
);
