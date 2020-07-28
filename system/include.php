<?php
return array(
		'mode' => 'debug', //debug 开启php错误提示
		'sp_core_path' => WIND_PATH,
		'sp_drivers_path' => WIND_PATH,
		'sp_include_path' => array( WIND_PATH.'/ext' ),

		'auto_load_controller' => array('syArgs'),
		'auto_load_model' => array('syPager','syVerifier','syCache'),
		'allow_trace_onrelease' => FALSE,

		'sp_error_show_source' => 5,
		'sp_error_throw_exception' => FALSE,
		'notice_php' => WIND_PATH."/notice.php",

		'inst_class' => array(),
		'import_file' => array(),
		'sp_access_store' => array(),
		'view_registered_functions' => array(),

		'default_controller' => 'index',
		'default_action' => 'index',
		'url_controller' => 'action',
		'url_action' => 'o',

		'auto_session' => TRUE,
		'dispatcher_error' => "syError('route Error');",
		'auto_sp_run' => FALSE,

		'sp_cache' => WIND_PATH.'/cache/tmp',
		'sp_session' => WIND_PATH.'/cache/ses',
		'sp_app_id' => 'sy',
		'controller_path' => APP_PATH.'/manage',
		'model_path' => WIND_PATH.'/class',

		'url' => array(
				'url_path_info' => FALSE,
				'url_path_base' => '',
		),

		'db' => array(
				'driver' => 'mysqli',
		),
		'db_driver_path' => '',
		'db_spdb_full_tblname' => FALSE,

		'view' => array(
				'enabled' => TRUE,
				'config' =>array(
						'template_dir' => APP_PATH.'/template',
						'template_tpl' => WIND_PATH.'/cache/tpl',
				),
				'engine_name' => 'template',
				'engine_path' => WIND_PATH.'/template.php',
		),


		'html' => array(
				'enabled' => TRUE,
				'safe_check_file_exists' => FALSE,
		),

		'lang' => array(),

		'include_path' => array(
				WIND_PATH.'/fun',
		),

		'vercode' => 0,

		'rewrite' => array(
				'rewrite_open' => 0,
				'rewrite_dir' => '/',
				'rewrite_article' => '{article}/ad{id}',
				'rewrite_article_type' => '{article}_{type}/{nid}',
				'rewrite_product' => '{product}/pd{id}',
				'rewrite_product_type' => '{product}_{type}/{nid}',
				'rewrite_message_type' => '{message}_{type}/{nid}',
				'rewrite_recruitment' => '{recruitment}/rd{id}',
				'rewrite_recruitment_type' => '{recruitment}_{type}/{nid}',
		),
		'logistics' => array('快递'=>10,'EMS'=>20,),
);
