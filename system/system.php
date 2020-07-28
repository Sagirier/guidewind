<?php
if (substr(PHP_VERSION, 0, 1) < 5)exit("本系统运行环境要求PHP版本5及以上！");
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
require(WIND_PATH."/Functions.php");
$GLOBALS['WP'] = spConfigReady(require(WIND_PATH."/include.php"),$wpConfig);
if('debug' == $GLOBALS['WP']['mode']){
	define("SP_DEBUG",TRUE);
}else{
	define("SP_DEBUG",FALSE);
}
if (SP_DEBUG) {
	if( substr(PHP_VERSION, 0, 3) == "5.3" ){
		error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
	}else{
		error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
	}
} else {
	error_reporting(0);
}
@set_magic_quotes_runtime(0);

import($GLOBALS['WP']["sp_core_path"]."/syController.php", FALSE, TRUE);
import($GLOBALS['WP']["sp_core_path"]."/syModel.php", FALSE, TRUE);
import($GLOBALS['WP']["sp_core_path"]."/syView.php", FALSE, TRUE);

if('' == $GLOBALS['WP']['url']["url_path_base"]){
	if(basename($_SERVER['SCRIPT_NAME']) === basename($_SERVER['SCRIPT_FILENAME']))
		$GLOBALS['WP']['url']["url_path_base"] = $_SERVER['SCRIPT_NAME'];
	elseif (basename($_SERVER['PHP_SELF']) === basename($_SERVER['SCRIPT_FILENAME']))
	$GLOBALS['WP']['url']["url_path_base"] = $_SERVER['PHP_SELF'];
	elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === basename($_SERVER['SCRIPT_FILENAME']))
	$GLOBALS['WP']['url']["url_path_base"] = $_SERVER['ORIG_SCRIPT_NAME'];
}
$GLOBALS['WWW']=pathinfo($GLOBALS['WP']['url']["url_path_base"]);
$GLOBALS['WWW']=str_replace($GLOBALS['WWW']["basename"],'',$GLOBALS['WP']['url']["url_path_base"]);
$GLOBALS['styles']=$GLOBALS['WWW'].'styles/'.$wpConfig['ext']['view_themes'].'/';
$GLOBALS['member']=$GLOBALS['WP']['member'];

if(TRUE == $GLOBALS['WP']['url']["url_path_info"] && !empty($_SERVER['PATH_INFO'])){
	$url_args = explode("/", $_SERVER['PATH_INFO']);$url_sort = array();
	for($u = 1; $u < count($url_args); $u++){
		if($u == 1)$url_sort[$GLOBALS['WP']["url_controller"]] = $url_args[$u];
		elseif($u == 2)$url_sort[$GLOBALS['WP']["url_action"]] = $url_args[$u];
		else {$url_sort[$url_args[$u]] = isset($url_args[$u+1]) ? $url_args[$u+1] : "";$u+=1;}}
		if("POST" == strtoupper($_SERVER['REQUEST_METHOD'])){$_REQUEST = $_POST =  $_POST + $url_sort;
		}else{$_REQUEST = $_GET = $_GET + $url_sort;}
}

$__controller = isset($_REQUEST[$GLOBALS['WP']["url_controller"]]) ?$_REQUEST[$GLOBALS['WP']["url_controller"]] :$GLOBALS['WP']["default_controller"];
$__action = isset($_REQUEST[$GLOBALS['WP']["url_action"]]) ?$_REQUEST[$GLOBALS['WP']["url_action"]] :$GLOBALS['WP']["default_action"];
$GLOBALS['SITE']=array('http'=>syExt('http_path'),'title'=>syExt('site_title'),'keywords'=>syExt('site_keywords'),'description'=>syExt('site_description'));
