<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://www.eaterplanet.com/
 * @copyright Copyright (c) 2019-2022 Dejavu.Tech.
 * @license   https://www.eaterplanet.com/license.html License
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

header( "Content-Type:text/html; charset=utf-8");

define('APP_DEBUG', true);

define('BIND_MODULE','Seller');

define ('APP_PATH', './Modules/' );


define('ROOT_PATH',str_replace('\\','/',dirname(__FILE__)) . '/');

define ('RUNTIME_PATH','./Runtime/');

require './ThinkPHP/ThinkPHP.php';
