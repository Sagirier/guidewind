<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class syView {
	public $engine = null;
	public $displayed = FALSE;
	public function __construct()
	{
		if(FALSE == $GLOBALS['WP']['view']['enabled'])return FALSE;
		if(FALSE != $GLOBALS['WP']['view']['auto_ob_start'])ob_start();
		$this->engine = syClass($GLOBALS['WP']['view']['engine_name'],null,$GLOBALS['WP']['view']['engine_path']);
		if( $GLOBALS['WP']['view']['config'] && is_array($GLOBALS['WP']['view']['config']) ){
			$engine_vars = get_class_vars(get_class($this->engine));
			foreach( $GLOBALS['WP']['view']['config'] as $key => $value ){
				if( array_key_exists($key,$engine_vars) )$this->engine->{$key} = $value;
			}
		}
		if( !empty($GLOBALS['WP']['sp_app_id']) && isset($this->engine->compile_id) )$this->engine->compile_id = $GLOBALS['WP']['sp_app_id'];
		if( empty($this->engine->no_compile_dir) && (!is_dir($this->engine->compile_dir) || !is_writable($this->engine->compile_dir)))__mkdirs($this->engine->compile_dir);
		spAddViewFunction('spUrl', array( 'syView', '__template_spUrl'));
	}

	public function display($tplname){
		try {
				$this->addfuncs();
				$this->displayed = TRUE;
				if($GLOBALS['WP']['view']['debugging'] && SP_DEBUG)$this->engine->debugging = TRUE;
				$this->engine->display($tplname);
		} catch (Exception $e) {
			syError( $GLOBALS['WP']['view']['engine_name']. ' Error: '.$e->getMessage() );
		}
	}

	public function addfuncs()
	{
		if( is_array($GLOBALS['WP']["view_registered_functions"]) ){
			foreach( $GLOBALS['WP']["view_registered_functions"] as $alias => $func ){
				if( is_array($func) && !is_object($func[0]) )$func = array(syClass($func[0]),$func[1]);
				$this->engine->registerPlugin("function", $alias, $func);
				unset($GLOBALS['WP']["view_registered_functions"][$alias]);
			}
		}
	}

	public function __template_spUrl($params)
	{
		$geturl = basename(__FILE__);
		$controller = $GLOBALS['WP']["default_controller"];
		$action = $GLOBALS['WP']["default_action"];
		$args = array();
		$anchor = null;
		foreach($params as $key => $param){
			if( $key == $GLOBALS['WP']["url_controller"] ){
				$controller = $param;
			}elseif( $key == $GLOBALS['WP']["url_action"] ){
				$action = $param;
			}elseif( $key == 'anchor' ){
				$anchor = $param;
			}else{
				$args[$key] = $param;
			}
		}
		return spUrl($geturl, $controller, $action, $args, $anchor);
	}
}