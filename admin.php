<?php
	require("config.php");
	$wpConfig['ext']['view_admin']= 'admin';
	$wpConfig['view']['config']['template_dir'] = APP_PATH.'/manage/admin/template';
	$wpConfig['controller_path'] = APP_PATH.'/manage/admin';

	require(WIND_PATH."/system.php");
	import(APP_PATH.'/system/fun/fun_admin.php');
	systemRun();