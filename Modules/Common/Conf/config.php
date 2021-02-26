<?php
return array(

	'MODULE_DENY_LIST'   => array('Common','Admin'),
	'MODULE_ALLOW_LIST'  => array('Home'),
    'DEFAULT_MODULE'     => 'Home',        
	//加载扩展配置项
    'LOAD_EXT_CONFIG' => 'db',	
	
	'FRONTEND_USER'=>'网站会员',
	
	'BACKEND_USER'=>'后台系统用户',	
    'SELLER_USER'=>'网站卖家',
	
	'AUTOLOAD_NAMESPACE' => array(    
		'Lib'     => APP_PATH.'Lib'
	),	

);