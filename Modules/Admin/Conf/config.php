<?php
$appConfig =  array(

    'URL_HTML_SUFFIX'=>'',
    'SHOW_PAGE_TRACE' => false,
	'URL_MODEL'            => 3,
	'VIEW_PATH'	=>'./Themes/Admin/',
	'SELLER_PWD_KEY' => 'SDU%^@#%SD',
    'PWD_KEY' => 'SDU%^@#ee%SD',
	 'TMPL_PARSE_STRING'=>array(
	 '__PUBLIC__' => __ROOT__ . '/assets',
	'__RES__' => '/Themes/'.BIND_MODULE.'/Public',
    '__IMG__'=>'./Themes/'.BIND_MODULE.'/Public/img',
    '__CSS__'=>'./Themes/'.BIND_MODULE.'/Public/css',
    '__JS__'=> './Themes/'.BIND_MODULE.'/Public/js',
    ),

	'SESSION_PREFIX' => 'dejavutech_admin_s', //session前缀
    'COOKIE_PREFIX'  => 'dejavutech_admin_c', // Cookie前缀 避免冲突
    /* 后台错误页面模板 */
    'TMPL_ACTION_ERROR' => './Themes/'.BIND_MODULE . '/Public/error.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => './Themes/'.BIND_MODULE . '/Public/success.html', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE' => './Themes/'.BIND_MODULE . '/Public/exception.html',// 异常页面的模板文件

    'LANG_SWITCH_ON' => true,
    'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'        => 'zh-cn,en-us', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'     => 'l', // 默认语言切换变量

);
$sysConfig = include('Modules/Common/Conf/db.php');

$securityConfig = array(
    // 表单令牌
    'TOKEN_ON' => false,
    'TOKEN_NAME' => '__hash__',
    'TOKEN_TYPE' => 'md5',
    'TOKEN_RESET' => true,

    // 认证token
    'AUTH_TOKEN' => 'eaadmin',
    // 认证mask
    'AUTH_MASK' => 'nimdaae',
    // 登录超时
    'LOGIN_TIMEOUT' => 3600,

    // 不用认证登录的模块
    'NOT_LOGIN_MODULES' => 'Public,Index',

    // 开启权限认证
    'USER_AUTH_ON' => true,
    // 登录认证模式
    'USER_AUTH_TYPE' => 1,
    // 认证识别号
    'USER_AUTH_KEY' => 'mineaad',
    // 超级管理员认证号
    'ADMIN_AUTH_KEY' => 'lionadminad',
    // 游客识别号
    'GUEST_AUTH_ID' => 'guest',
    // 模块名称（不要修改）
    'GROUP_AUTH_NAME' => 'Admin',
    // 无需认证模块
    'NOT_AUTH_MODULE' => 'Public',
    // 需要认证模块
    'REQUIRE_AUTH_MODULE' => '',
    // 认证网关
    'USER_AUTH_GATEWAY' => 'Public/index',
    // 关闭游客授权访问
    'GUEST_AUTH_ON' => false,
    // 管理员模型
    'USER_AUTH_MODEL' => 'Admin',
    // 角色表
    'RBAC_ROLE_TABLE' => $sysConfig['DB_PREFIX'] . 'role',
    // 管理员-角色表
    'RBAC_USER_TABLE' => $sysConfig['DB_PREFIX'] . 'admin',
    // 节点表
    'RBAC_NODE_TABLE' => $sysConfig['DB_PREFIX'] . 'node',
    // 节点访问表
    'RBAC_ACCESS_TABLE' => $sysConfig['DB_PREFIX'] . 'access'
);

// 登录标记
$appConfig['LOGIN_MARKED'] = md5($securityConfig['AUTH_TOKEN']);
return array_merge($appConfig, $securityConfig);
