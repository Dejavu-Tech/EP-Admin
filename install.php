<?php

if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

header("Content-Type:text/html; charset=utf-8");    

define('BIND_MODULE', 'Install');

define ('APP_DEBUG', true);

define ('APP_PATH', './Modules/');

define ('RUNTIME_PATH', './Runtime/');

if(!is_dir(RUNTIME_PATH)) {	
	
	if(!mkdir(RUNTIME_PATH, 0777)){
		header('Content-Type:text/html; charset=utf-8');
    	exit('目录 [ '.RUNTIME_PATH.' ] 不存在！');
	}	
	
}
if(!is_writeable(RUNTIME_PATH)) {	
     header('Content-Type:text/html; charset=utf-8');
     exit('目录 [ '.RUNTIME_PATH.' ] 不可写！');
}


require './ThinkPHP/ThinkPHP.php';


