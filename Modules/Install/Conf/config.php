<?php
/**
 * 安装程序配置文件
 */

define('INSTALL_APP_PATH', realpath('./') . '/');

return array(

    'ORIGINAL_TABLE_PREFIX' => 'dejavutech_', //默认表前缀

	'DEFAULT_THEME'			 => 'default',
	'TMPL_TEMPLATE_SUFFIX'	 => '.html',
	'VIEW_PATH'				 => './Themes/Install/',
    'SELLER_PWD_KEY' => 'IA=~(D9-|.^k7JsP1p*nVa,W/t6O#KY:$05q3G;)',
	'TMPL_PARSE_STRING'=>array(
	'__PUBLIC__' => __ROOT__ . '/assets',
	'__RES__' => __ROOT__.'/assets/theme',
    '__IMG__'=>__ROOT__.'/Themes/'.MODULE_NAME.'/default/Public/images',
    '__CSS__'=>__ROOT__.'/Themes/'.MODULE_NAME.'/default/Public/css',
    '__JS__'=> __ROOT__.'/Themes/'.MODULE_NAME.'/default/Public/js',
    '__NAME__'=>'吃货星球S2B2C商城系统',
    '__COMPANY__'=>'蒂佳芙科技（云南）有限公司',
    '__YEARS__'=>'2019-2023',
    '__VERSION__'=>'吃货星球v5.5.1',
    '__WEBSITE__'=>'e-p.io',
    '__COMPANY_WEBSITE__'=>'www.rexotech.cn'
    ),

    /* URL配置 */
    'URL_MODEL' => 3, //URL模式
    'SESSION_PREFIX' => 'dejavutech', //session前缀
    'COOKIE_PREFIX' => 'dejavutech_', // Cookie前缀 避免冲突

);
