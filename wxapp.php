<?php
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('require PHP > 5.3.0 !');
}
header("Content-Type:text/html; charset=utf-8");
define('APP_DEBUG', true);
define('BIND_MODULE', 'Home');
define('APP_PATH', './Modules/');
$wx_controller = $_REQUEST['controller'];
$wx_controller_arr = explode('.', $wx_controller);
if (!isset($wx_controller_arr[1])) {
    $wx_controller_arr[1] = 'index';
}
unset($_GET['i']);
unset($_GET['t']);
unset($_GET['v']);
unset($_GET['from']);
unset($_GET['do']);
$_GET['c'] = $wx_controller_arr[0];
$_GET['a'] = $wx_controller_arr[1];
unset($_GET['controller']);
unset($_GET['m']);
define('ROOT_PATH', str_replace('\\', '/', dirname(CCAFDBBFAED)) . '/');
define('RUNTIME_PATH', './Runtime/');
require './ThinkPHP/ThinkPHP.php';