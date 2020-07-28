<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
function systemRun(){
	GLOBAL $__controller, $__action;
	if($__controller=='syaccount'){
		syError('route Error');
		exit;
	}
	syClass('sysession');
	spLaunch("router_prefilter");
	$handle_controller = syClass($__controller, null, $GLOBALS['WP']["controller_path"].'/'.$__controller.".php");
	if(!is_object($handle_controller) || !method_exists($handle_controller, $__action)){
		syError('route Error');
		exit;
	}
	$handle_controller->$__action();
	if(FALSE != $GLOBALS['WP']['view']['auto_display']){
		$__tplname = $__controller.$GLOBALS['WP']['view']['auto_display_sep'].
				$__action.$GLOBALS['WP']['view']['auto_display_suffix']; 
		$handle_controller->auto_display($__tplname);
	}
	spLaunch("router_postfilter");
}

function dump($vars, $output = TRUE, $show_trace = FALSE){
	if(TRUE != SP_DEBUG && TRUE != $GLOBALS['WP']['allow_trace_onrelease'])return;
	if( TRUE == $show_trace ){ 
		$content = syError(htmlspecialchars(print_r($vars, true)), TRUE, FALSE);
	}else{
		$content = "<div align=left><pre>\n" . htmlspecialchars(print_r($vars, true)) . "\n</pre></div>\n";
	}
    if(TRUE != $output) { return $content; } 
       echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>{$content}</body></html>"; 
	   return;
}

function import($sfilename, $auto_search = TRUE, $auto_error = FALSE){
	if(isset($GLOBALS['WP']["import_file"][md5($sfilename)]))return TRUE;
	if( TRUE == @is_readable($sfilename) ){
		require($sfilename); 
		$GLOBALS['WP']['import_file'][md5($sfilename)] = TRUE; 
		return TRUE;
	}else{
		if(TRUE == $auto_search){
			foreach(array_merge( $GLOBALS['WP']['include_path'], array($GLOBALS['WP']['model_path']), $GLOBALS['WP']['sp_include_path'] ) as $sp_include_path){
				if(isset($GLOBALS['WP']["import_file"][md5($sp_include_path.'/'.$sfilename)]))return TRUE;
				if( is_readable( $sp_include_path.'/'.$sfilename ) ){
					require($sp_include_path.'/'.$sfilename);
					$GLOBALS['WP']['import_file'][md5($sp_include_path.'/'.$sfilename)] = TRUE;
					return TRUE;
				}
			}
		}
	}
	if( TRUE == $auto_error )syError("未能找到名为：{$sfilename}的文件");
	return FALSE;
}
function syAccess($method, $name, $value = NULL, $life_time = -1){
	if( $launch = spLaunch("function_access", array('method'=>$method, 'name'=>$name, 'value'=>$value, 'life_time'=>$life_time), TRUE) )return $launch;
	if(!is_dir($GLOBALS['WP']['sp_cache']))__mkdirs($GLOBALS['WP']['sp_cache']);
	$sfile = $GLOBALS['WP']['sp_cache'].'/'.$GLOBALS['WP']['sp_app_id'].md5($name).".php";
	if('w' == $method){ 
		$life_time = ( -1 == $life_time ) ? '300000000' : $life_time;
		$value = '<?php die();?>'.( time() + $life_time ).serialize($value);
		return file_put_contents($sfile, $value);
	}elseif('c' == $method){
		return @unlink($sfile);
	}else{
		if( !is_readable($sfile) )return FALSE;
		$arg_data = file_get_contents($sfile);
		if( substr($arg_data, 14, 10) < time() ){
			@unlink($sfile); 
			return FALSE;
		}
		return unserialize(substr($arg_data, 24)); 
	}
}

function syClass($class_name, $args = null, $sdir = null, $force_inst = FALSE){
	if(preg_match("/^[a-zA-Z0-9_\-]*$/",$class_name)==0)syError("类定义不存在，请检查。");
	if(TRUE != $force_inst)if(isset($GLOBALS['WP']["inst_class"][$class_name]))return $GLOBALS['WP']["inst_class"][$class_name];
	if(null != $sdir && !import($sdir) && !import($sdir.'/'.$class_name.'.php'))return FALSE;
	$has_define = FALSE;
	if(class_exists($class_name, false) || interface_exists($class_name, false)){
		$has_define = TRUE;
	}else{
		if( TRUE == import($class_name.'.php')){
			$has_define = TRUE;
		}
	}
	if(FALSE != $has_define){
		$argString = '';$comma = ''; 
		if(null != $args)for ($i = 0; $i < count($args); $i ++) { $argString .= $comma . "\$args[$i]"; $comma = ', ';}
		eval("\$GLOBALS['WP']['inst_class'][\$class_name]= new \$class_name($argString);"); 
		return $GLOBALS['WP']["inst_class"][$class_name];
	}
	syError($class_name."类定义不存在，请检查。");
}


/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function getClientIp($type = 0, $adv = false)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

function curl_get($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	$arr = explode("?", $url);
	if(count($arr) >= 2) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $arr[0]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $arr[1]);
	}else{
		curl_setopt($ch, CURLOPT_URL, $url);
	}
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}


/**
 * get请求
 * @param $url
 * @return mixed
 */
function getHttp($url){
    $services = array(
        'api_host' => 'https://api.91gxy.com',
        'client_id' => 'ty45Dew34ddsqw',
        'base_host' => 'https://www.91gxy.com',
    );
    $url = $services['api_host'] . $url . "&client_id=" . $services['client_id'] . "&origin=" . $services['base_host'];
    $ch=curl_init();
    //设置传输地址
    curl_setopt($ch, CURLOPT_URL, $url);
    //设置以文件流形式输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //接收返回数据
    $data=curl_exec($ch);
    curl_close($ch);
    $jsonInfo=json_decode($data,true);
    return $jsonInfo;
}

/**
 * post请求
 * @param $url
 * @param $post_data
 * @return mixed
 */
function postHttp($url,$post_data){
    $services = array(
        'api_host' => 'https://api.91gxy.com',
        'client_id' => 'ty45Dew34ddsqw',
        'base_host' => 'https://www.91gxy.com',
    );
    $url = $services['api_host'] . $url;
    $post_data['client_id'] = $services['client_id'];
    $post_data['origin'] = $services['base_host'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
    // post数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // post的变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $output = curl_exec($ch);
    curl_close($ch);
    return is_array($output)?$output:json_decode($output,true);
}

function xml_array($content,$url='') {
	if($url){$xml = simplexml_load_file($url);}else{$xml = simplexml_load_string($content);}
	$x=array();
	if($xml && $xml->children()) {
		foreach ($xml->children() as $node){
			if($node->children()) {
				$k = $node->getName();
				$nodeXml = $node->asXML();
				$v = substr($nodeXml, strlen($k)+2, strlen($nodeXml)-2*strlen($k)-5);
			} else {
				$k = $node->getName();
				$v = (string)$node;
			}
			$x[$k]=$v;		
		}
	}
	return $x;
}
function syError($msg, $output = TRUE, $stop = TRUE){
	if($GLOBALS['WP']['sp_error_throw_exception'])throw new Exception($msg);
	if(TRUE != SP_DEBUG){
		//error_log($msg);
		echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
		echo "<body>程序出错！</body></html>";
		if(TRUE == $stop)exit;
	}
	$traces = debug_backtrace();
	$bufferabove = ob_get_clean();
	require_once($GLOBALS['WP']['notice_php']);
	if(TRUE == $stop)exit;
}


function spLaunch($configname, $launchargs = null, $returns = FALSE ){
	if( isset($GLOBALS['WP']['launch'][$configname]) && is_array($GLOBALS['WP']['launch'][$configname]) ){
		foreach( $GLOBALS['WP']['launch'][$configname] as $launch ){
			if( is_array($launch) ){
				$reval = syClass($launch[0])->{$launch[1]}($launchargs);
			}else{
				$reval = call_user_func_array($launch, $launchargs);
			}
			if( TRUE == $returns )return $reval;
		}
	}
	return false;
}

function spUrl($geturl = null, $controller = null, $action = null, $args = null, $anchor = null, $no_sphtml = FALSE) {
	if(TRUE == $GLOBALS['WP']['html']["enabled"] && TRUE != $no_sphtml){
		$realhtml = syhtml::getUrl($geturl, $controller, $action, $args, $anchor);if(isset($realhtml[0]))return $realhtml[0];
	}
	$geturl = ( null != $geturl ) ? $geturl :  basename(__FILE__);
	$controller = ( null != $controller ) ? $controller : $GLOBALS['WP']["default_controller"];
	$action = ( null != $action ) ? $action : $GLOBALS['WP']["default_action"];
	if( $launch = spLaunch("function_url", array('controller'=>$controller, 'action'=>$action, 'args'=>$args, 'anchor'=>$anchor, 'no_sphtml'=>$no_sphtml), TRUE ))return $launch;
	if( TRUE == $GLOBALS['WP']['url']["url_path_info"] ){
		$url = "{$controller}/{$controller}/{$action}";
		if(null != $args)foreach($args as $key => $arg) $url .= "/{$key}/{$arg}";
	}else{
		$url = $geturl."?". $GLOBALS['WP']["url_controller"]. "={$controller}&";
		$url .= $GLOBALS['WP']["url_action"]. "={$action}";
		if(null != $args)foreach($args as $key => $arg) $url .= "&{$key}={$arg}";
	}
	if(null != $anchor) $url .= "#".$anchor;
	return $url;
}

function __mkdirs($dir, $mode = 0755)
{
	if (!is_dir($dir)) {
		__mkdirs(dirname($dir), $mode);
		return @mkdir($dir, $mode);
	}
	return true;
}
function syExt($ext_node_name)
{
	return (empty($GLOBALS['WP']['ext'][$ext_node_name])) ? FALSE : $GLOBALS['WP']['ext'][$ext_node_name];
}
function syCus($ext_node_name)
{
	return (empty($GLOBALS['WP']['cus'][$ext_node_name])) ? FALSE : $GLOBALS['WP']['cus'][$ext_node_name];
}
function spAddViewFunction($alias, $callback_function)
{
	return $GLOBALS['WP']["view_registered_functions"][$alias] = $callback_function;
}

function syDB($tbl_name, $pk = null){
	$modelObj = syClass("syModel");
	$modelObj->tbl_name = (TRUE == $GLOBALS['WP']["db_spdb_full_tblname"]) ? $tbl_name :	$GLOBALS['WP']['db']['prefix'] . $tbl_name;
	if( !$pk ){
		@list($pk) = $modelObj->_db->getTable($modelObj->tbl_name);
		$pk = $pk['Field'];
	}
	$modelObj->pk = $pk;
	return $modelObj;
}
function syPass($string, $operation = 'DECODE', $key = '') {
	// 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
	$ckey_length = 4;
	 
	// 密匙
	$key = md5($key ? $key : $GLOBALS['WP']['ext']['secret_key']);
	 
	// 密匙a会参与加解密
	$keya = md5(substr($key, 0, 16));
	// 密匙b会用来做数据完整性验证
	$keyb = md5(substr($key, 16, 16));
	// 密匙c用于变化生成的密文
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	// 参与运算的密匙
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	// 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
	// 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d',  0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	// 产生密匙簿
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	// 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	// 核心加解密部分
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		// 从密匙簿得出密匙进行异或，再转成字符
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		// substr($result, 0, 10) == 0 验证数据有效性
		// substr($result, 0, 10) - time() > 0 验证数据有效性
		// substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
		// 验证数据有效性，请看未加密明文的格式
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		// 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
		// 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}
function spConfigReady( $preconfig, $useconfig = null){
	$nowconfig = $preconfig;
	if (is_array($useconfig)){
		foreach ($useconfig as $key => $val){
			if (is_array($useconfig[$key])){
				@$nowconfig[$key] = is_array($nowconfig[$key]) ? spConfigReady($nowconfig[$key], $useconfig[$key]) : $useconfig[$key];
			}else{
				@$nowconfig[$key] = $val;
			}
		}
	}
	return $nowconfig;
}
function jump($url, $delay = 0){
	echo '<html><head><meta http-equiv="refresh" content="'.$delay.';url='.$url.'"></head><body><script type=text/javascript>window.location.href='.$url.'"</script></body></html>';
	exit;
}
function filemanager_list($a, $b){
	$order=is_escape($_GET['order']);
	$order=strtolower($order);
	if ($a['is_dir'] && !$b['is_dir']) {
		return -1;
	} else if (!$a['is_dir'] && $b['is_dir']) {
		return 1;
	} else {
		if ($order == 'size') {
			if ($a['filesize'] > $b['filesize']) {
				return 1;
			} else if ($a['filesize'] < $b['filesize']) {
				return -1;
			} else {
				return 0;
			}
		} else if ($order == 'type') {
			return strcmp($a['filetype'], $b['filetype']);
		} else {
			return strcmp($a['filename'], $b['filename']);
		}
	}
}
//提示信息
function message($info,$gurl=null,$time=3,$type=1){
	echo '<html>
			<head>
			<title>系统提示-'.$GLOBALS['WP']['ext']['site_title'].'</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<script src="manage/admin/template/js/jquery-2.1.1.min.js" type="text/javascript"></script>
			<script src="manage/admin/template/js/plugins/layer/layer.min.js" type="text/javascript"></script>
			<script language="javascript" type="text/javascript"> 
				var i = '.$time.'; 
				var intervalid; 
				intervalid = setInterval("fun()", 1000); 
				function fun() { 
					if (i == 0) { 
						clearInterval(intervalid);';

				if (!$gurl){
				  echo 'javascript:history.go(-1);';
						
				}else{
				  echo 'window.location.href="'.$gurl.'"';
				}
		      echo '} 
				    document.getElementById("mes").innerHTML = i; 
				    i--; 
				} 
			</script> 
			</head>
			<body style="background:#efefef">
		      	<div style="height:100%;"></div>
				<script>
					var mes="mes";';
		      if (!$gurl){
		      	echo 'layer.msg("'.$info.'<br/>将在<span id="+mes+">'.$time.'</span>s后跳转，<a href=javascript:history.go(-1);>如未跳转，请点此跳转</a>", 0,'.$type.');';
		      
		      }else{
		      	echo 'layer.msg("'.$info.'<br/>将在<span id="+mes+">'.$time.'</span>s后跳转，<a href='.$gurl.'>如未跳转，请点此跳转</a>", 0, '.$type.');';
		      }
		  echo '</script>
			</body>
		</html>';
		exit();
}
function message_err($newerrors){
	foreach($newerrors as $errortxt){
		$error_txt1=$errortxt;
		foreach($error_txt1 as $msg){
			$error_txt=$msg;
		}
	}
	message($error_txt);
}
function message_c($info,$lurl,$curl,$time=8,$type=1){
	?><html>
			<head>
			<title>系统提示-<?php echo $GLOBALS['WP']['ext']['site_title']; ?></title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<link href="manage/admin/template/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    		<link href="manage/admin/template/font-awesome/css/font-awesome.css?v=4.3.0" rel="stylesheet">
    		<link href="manage/admin/template/css/animate.css" rel="stylesheet">
    		<link href="manage/admin/template/css/style.css?v=2.2.0" rel="stylesheet">
			<script src="manage/admin/template/js/jquery-2.1.1.min.js" type="text/javascript"></script>
			<script src="manage/admin/template/js/plugins/layer/layer.min.js" type="text/javascript"></script>
			<script language="javascript" type="text/javascript">
				var i = <?php echo $time; ?>;
				var intervalid;
				intervalid = setInterval("fun()", 1000);
				function fun() {
					if (i == 0) {
						clearInterval(intervalid);
						window.location.href="<?php echo $lurl; ?>";
					}
				    document.getElementById("mes").innerHTML = i;
				    i--;
				}
			</script>
		</head>
		<body style="background:#efefef">
			<div style="height:100%;"></div>
			<script>
				var mes="mes";
				layer.msg("<?php echo $info; ?><br/><a class='btn btn-success' href='<?php echo $lurl; ?>'><i class='fa fa-mail-reply'></i> 返回列表</a><a style='margin-left: 20px' class='btn btn-success' href='<?php echo $curl; ?>'><i class='fa fa-plus-square'></i> 继续添加</a><br/>将在<span id='"+mes+"'><?php echo $time; ?></span>s后跳转，<a href='<?php echo $lurl; ?>';>如未跳转，请点此跳转</a>", 0,<?php echo $type; ?>);
			</script>
			</body>
		</html>
	<?php 
	exit();
}
function message_member($requestno,$lurl,$curl,$time=8,$type=1){
    echo '<html>
			<head>
			<title>系统提示-'.$GLOBALS['WP']['ext']['site_title'].'</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<link href="manage/admin/template/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    		<link href="manage/admin/template/font-awesome/css/font-awesome.css?v=4.3.0" rel="stylesheet">
    		<link href="manage/admin/template/css/animate.css" rel="stylesheet">
    		<link href="manage/admin/template/css/style.css?v=2.2.0" rel="stylesheet">
			<script src="manage/admin/template/js/jquery-2.1.1.min.js" type="text/javascript"></script>
			<script src="manage/admin/template/js/plugins/layer/layer.min.js" type="text/javascript"></script>
			<script language="javascript" type="text/javascript">
				var i = '.$time.';
				var intervalid;
				intervalid = setInterval("fun()", 1000);
				function fun() {
					if (i == 0) {
						clearInterval(intervalid);';
                        if($requestno==1){
						  echo 'window.location.href="'.$lurl.'";';
                        }else if($requestno==2){
                            echo 'window.location.href="'.$curl.'";';
                        }
			  echo '}
				    document.getElementById("mes").innerHTML = i;
				    i--;
				}
		    </script>
		    </head>';
    echo '<body style="background:#efefef"><div style="height:100%;"></div>';
    echo '<script>var mes="mes";';
    //分别处理注册结果
    if($requestno==1){ //用户名已存在，注册失败
       $msg1="该用户名已经注册，您可以";
       $msg2="<br/><a class='btn btn-success' href='".$lurl."'><i class='fa fa-gavel'></i> 重新注册</a><a style='margin-left: 20px' class='btn btn-success' href='".$curl."'><i class='fa fa-user'></i> 立即登录</a>";
       $msg3="<br/>将在<span id='";$msg3.='"+mes+"';$msg3.="'>";$msg3.=$time."</span>s后返回注册页面。";
    }elseif($requestno==2){ //注册成功
        $msg1="恭喜您，注册成功！您可以";
        $msg2="<br/><a class='btn btn-info' href='".$lurl."'><i class='fa fa-mail-reply-all'></i> 返回上级页面</a><a style='margin-left: 20px' class='btn btn-success' href='".$curl."'><i class='fa fa-rebel'></i> 进入个人中心</a>";
        $msg3="<br/>将在<span id='";$msg3.='"+mes+"';$msg3.="'>";$msg3.=$time."</span>s后进入<a href='".$curl."'>个人中心</a>。";
    }
    
    $msg=$msg1.$msg2.$msg3;
    echo 'layer.msg("'.$msg.'", 0,'.$type.');';
    echo '</script></body></html>';
	exit();
}
function message_pass($id,$url,$cmark,$type=false){
	?>
	<html>
	<head>
	<title>系统提示-<?php echo $GLOBALS['WP']['ext']['site_title']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script src="manage/admin/template/js/jquery-2.1.1.min.js" type="text/javascript"></script>
	<script src="manage/admin/template/js/plugins/layer/layer.min.js" type="text/javascript"></script>
	<link href="manage/admin/template/css/style.css" rel="stylesheet" type="text/css" />
	</head>
	<body style="background:#efefef">
      	<div style="height:100%;"></div>
		<script>
			var pagei=$.layer({
		    type: 1,
		    title: "您需要输入密码才能继续访问",
		    area: ['auto', 'auto'],
		    shadeClose: false ,
		    closeBtn: false,
		    btns: 2,
		    btn:["提交","取消"],
		    page: {
		        html: '<div style="width:400px;padding:20px; height:70px; border:1px solid #ccc; border-radius:5px; box-shadow:0 0 5px #999; background-color:#eee;"><input placeholder="请输入访问密码" class="form-control" id="pass<?php echo $id; ?>" /></div>'
		    },
		    yes:function(){
		    	var inp=document.getElementById("pass<?php echo $id; ?>");
		    	$.ajax({
		    		type:"post",
		    		url:'index.php?action=<?php echo $cmark; ?>&o=<?php if($type==false){echo "passcheck";}else{ echo "passcheck_type";} ?>',
		    		async:true,
		    		cache: false,
    				dataType: "json",
		    		data:{"id":<?php echo $id; ?>,"pass":inp.value},
		    		success:function(msg){
		    			if(msg.result_code==101){ //密码错误
		    				layer.msg(msg.result_des,2,2);
		    			}else if(msg.result_code==102){ //密码为空
		    				layer.msg(msg.result_des,2,0);
		    			}else{ //密码正确
		    				layer.msg(msg.result_des,0,1);
		    				window.location.href="<?php echo $url; ?>";
		    			}
		    		}
		    	});
		    },
		    no:function(){
		    	layer.close(pagei);
		    	window.location.href="javascript:history.go(-1);";
		    }
		});
		</script>
	</body>
	</html>
	<?php 
	exit();
}
/**
 * 建立文件夹
 *
 * @param string $aimUrl
 * @return viod
 */
function createDir($aimUrl) {
	$aimUrl = str_replace('', '/', $aimUrl);
	$aimDir = '';
	$arr = explode('/', $aimUrl);
	$result = true;
	foreach ($arr as $str) {
		$aimDir .= $str . '/';
		if (!file_exists($aimDir)) {
			$result = mkdir($aimDir);
		}
	}
	return $result;
}

/**
 * 建立文件
 *
 * @param string $aimUrl
 * @param boolean $overWrite 该参数控制是否覆盖原文件
 * @return boolean
 */
function createFile($aimUrl, $overWrite = false) {
	if (file_exists($aimUrl) && $overWrite == false) {
		return false;
	} elseif (file_exists($aimUrl) && $overWrite == true) {
		unlinkFile($aimUrl);
	}
	$aimDir = dirname($aimUrl);
	createDir($aimDir);
	touch($aimUrl);
	return true;
}

/**
 * 移动文件夹
 *
 * @param string $oldDir
 * @param string $aimDir
 * @param boolean $overWrite 该参数控制是否覆盖原文件
 * @return boolean
 */
function moveDir($oldDir, $aimDir, $overWrite = false) {
	$aimDir = str_replace('', '/', $aimDir);
	$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
	$oldDir = str_replace('', '/', $oldDir);
	$oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
	if (!is_dir($oldDir)) {
		return false;
	}
	if (!file_exists($aimDir)) {
		createDir($aimDir);
	}
	@ $dirHandle = opendir($oldDir);
	if (!$dirHandle) {
		return false;
	}
	while (false !== ($file = readdir($dirHandle))) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		if (!is_dir($oldDir . $file)) {
			moveFile($oldDir . $file, $aimDir . $file, $overWrite);
		} else {
			moveDir($oldDir . $file, $aimDir . $file, $overWrite);
		}
	}
	closedir($dirHandle);
	return rmdir($oldDir);
}

/**
 * 移动文件
 *
 * @param string $fileUrl
 * @param string $aimUrl
 * @param boolean $overWrite 该参数控制是否覆盖原文件
 * @return boolean
 */
function moveFile($fileUrl, $aimUrl, $overWrite = false) {
	if (!file_exists($fileUrl)) {
		return false;
	}
	if (file_exists($aimUrl) && $overWrite = false) {
		return false;
	} elseif (file_exists($aimUrl) && $overWrite = true) {
		unlinkFile($aimUrl);
	}
	$aimDir = dirname($aimUrl);
	createDir($aimDir);
	rename($fileUrl, $aimUrl);
	return true;
}

/**
 * 删除文件夹
 *
 * @param string $aimDir
 * @return boolean
 */
function unlinkDir($aimDir) {
	$aimDir = str_replace('', '/', $aimDir);
	$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
	if (!is_dir($aimDir)) {
		return false;
	}
	$dirHandle = opendir($aimDir);
	while (false !== ($file = readdir($dirHandle))) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		if (!is_dir($aimDir . $file)) {
			unlinkFile($aimDir . $file);
		} else {
			unlinkDir($aimDir . $file);
		}
	}
	closedir($dirHandle);
	return rmdir($aimDir);
}

/**
 * 删除文件
 *
 * @param string $aimUrl
 * @return boolean
 */
function unlinkFile($aimUrl) {
	if (file_exists($aimUrl)) {
		unlink($aimUrl);
		return true;
	} else {
		return false;
	}
}

/**
 * 复制文件夹
 *
 * @param string $oldDir
 * @param string $aimDir
 * @param boolean $overWrite 该参数控制是否覆盖原文件
 * @return boolean
 */
function copyDir($oldDir, $aimDir, $overWrite = false) {
	$aimDir = str_replace('', '/', $aimDir);
	$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
	$oldDir = str_replace('', '/', $oldDir);
	$oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
	if (!is_dir($oldDir)) {
		return false;
	}
	if (!file_exists($aimDir)) {
		createDir($aimDir);
	}
	$dirHandle = opendir($oldDir);
	while (false !== ($file = readdir($dirHandle))) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		if (!is_dir($oldDir . $file)) {
			copyFile($oldDir . $file, $aimDir . $file, $overWrite);
		} else {
			copyDir($oldDir . $file, $aimDir . $file, $overWrite);
		}
	}
	return closedir($dirHandle);
}
function copyFile($fileUrl, $aimUrl, $overWrite = false) {
	if (!file_exists($fileUrl)) {
		return false;
	}
	if (file_exists($aimUrl) && $overWrite == false) {
		return false;
	} elseif (file_exists($aimUrl) && $overWrite == true) {
		unlinkFile($aimUrl);
	}
	$aimDir = dirname($aimUrl);
	createDir($aimDir);
	copy($fileUrl, $aimUrl);
	return true;
}

function getCity( $userip, $dat_path = '' ) {
        //IP数据库路径，这里用的是QQ IP数据库 20110405 纯真版
        empty( $dat_path ) && $dat_path = WIND_PATH.'/qqwry.dat';
        //判断IP地址是否有效
        if ( preg_match( "/^([0-9]{1,3}.){3}[0-9]{1,3}$/", $userip ) == 0 ) {
            return 'IP Address Invalid';
        }
        //打开IP数据库
        if ( !$fd = @fopen( $dat_path, 'rb' ) ) {
            return 'IP data file not exists or access denied';
        }
        //explode函数分解IP地址，运算得出整数形结果
        $userip = explode( '.', $userip );
        $useripNum = $userip[0] * 16777216 + $userip[1] * 65536 + $userip[2] * 256 + $userip[3];
        //获取IP地址索引开始和结束位置
        $DataBegin = fread( $fd, 4 );
        $DataEnd = fread( $fd, 4 );
        $useripbegin = implode( '', unpack( 'L', $DataBegin ) );
        if ( $useripbegin < 0 )
            $useripbegin += pow( 2, 32 );
        $useripend = implode( '', unpack( 'L', $DataEnd ) );
        if ( $useripend < 0 )
            $useripend += pow( 2, 32 );
        $useripAllNum = ($useripend - $useripbegin) / 7 + 1;
        $BeginNum = 0;
        $EndNum = $useripAllNum;
        //使用二分查找法从索引记录中搜索匹配的IP地址记录
        while ( $userip1num > $useripNum || $userip2num < $useripNum ) {
            $Middle = intval( ($EndNum + $BeginNum) / 2 );
            //偏移指针到索引位置读取4个字节
            fseek( $fd, $useripbegin + 7 * $Middle );
            $useripData1 = fread( $fd, 4 );
            if ( strlen( $useripData1 ) < 4 ) {
                fclose( $fd );
                return 'File Error';
            }
            //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
            $userip1num = implode( '', unpack( 'L', $useripData1 ) );
            if ( $userip1num < 0 )
                $userip1num += pow( 2, 32 );
            //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
            if ( $userip1num > $useripNum ) {
                $EndNum = $Middle;
                continue;
            }
            //取完上一个索引后取下一个索引
            $DataSeek = fread( $fd, 3 );
            if ( strlen( $DataSeek ) < 3 ) {
                fclose( $fd );
                return 'File Error';
            }
            $DataSeek = implode( '', unpack( 'L', $DataSeek . chr( 0 ) ) );
            fseek( $fd, $DataSeek );
            $useripData2 = fread( $fd, 4 );
            if ( strlen( $useripData2 ) < 4 ) {
                fclose( $fd );
                return 'File Error';
            }
            $userip2num = implode( '', unpack( 'L', $useripData2 ) );
            if ( $userip2num < 0 )
                $userip2num += pow( 2, 32 );
            //找不到IP地址对应城市
            if ( $userip2num < $useripNum ) {
                if ( $Middle == $BeginNum ) {
                    fclose( $fd );
                    return 'No Data';
                }
                $BeginNum = $Middle;
            }
        }
        $useripFlag = fread( $fd, 1 );
        if ( $useripFlag == chr( 1 ) ) {
            $useripSeek = fread( $fd, 3 );
            if ( strlen( $useripSeek ) < 3 ) {
                fclose( $fd );
                return 'System Error';
            }
            $useripSeek = implode( '', unpack( 'L', $useripSeek . chr( 0 ) ) );
            fseek( $fd, $useripSeek );
            $useripFlag = fread( $fd, 1 );
        }
        if ( $useripFlag == chr( 2 ) ) {
            $AddrSeek = fread( $fd, 3 );
            if ( strlen( $AddrSeek ) < 3 ) {
                fclose( $fd );
                return 'System Error';
            }
            $useripFlag = fread( $fd, 1 );
            if ( $useripFlag == chr( 2 ) ) {
                $AddrSeek2 = fread( $fd, 3 );
                if ( strlen( $AddrSeek2 ) < 3 ) {
                    fclose( $fd );
                    return 'System Error';
                }
                $AddrSeek2 = implode( '', unpack( 'L', $AddrSeek2 . chr( 0 ) ) );
                fseek( $fd, $AddrSeek2 );
            } else {
                fseek( $fd, -1, SEEK_CUR );
            }
            while ( ($char = fread( $fd, 1 )) != chr( 0 ) )
                $useripAddr2 .= $char;
            $AddrSeek = implode( '', unpack( 'L', $AddrSeek . chr( 0 ) ) );
            fseek( $fd, $AddrSeek );
            while ( ($char = fread( $fd, 1 )) != chr( 0 ) )
                $useripAddr1 .= $char;
        } else {
            fseek( $fd, -1, SEEK_CUR );
            while ( ($char = fread( $fd, 1 )) != chr( 0 ) )
                $useripAddr1.=$char;
            $useripFlag = fread( $fd, 1 );
            if ( $useripFlag == chr( 2 ) ) {
                $AddrSeek2 = fread( $fd, 3 );
                if ( strlen( $AddrSeek2 ) < 3 ) {
                    fclose( $fd );
                    return 'System Error';
                }
                $AddrSeek2 = implode( '', unpack( 'L', $AddrSeek2 . chr( 0 ) ) );
                fseek( $fd, $AddrSeek2 );
            } else {
                fseek( $fd, -1, SEEK_CUR );
            }
            while ( ($char = fread( $fd, 1 )) != chr( 0 ) ) {
                $useripAddr2 .= $char;
            }
        }
        fclose( $fd );
        //返回IP地址对应的城市结果
        if ( preg_match( '/http/i', $useripAddr2 ) ) {
            $useripAddr2 = '';
        }
        $useripaddr = "$useripAddr1 $useripAddr2";
        $useripaddr = preg_replace( '/CZ88.Net/is', '', $useripaddr );
        $useripaddr = preg_replace( '/^s*/is', '', $useripaddr );
        $useripaddr = preg_replace( '/s*$/is', '', $useripaddr );
        if ( preg_match( '/http/i', $useripaddr ) || $useripaddr == '' ) {
            $useripaddr = 'No Data';
        } elseif ( !is_utf8( $useripaddr ) ) {
            $useripaddr = iconv( 'GBK', 'UTF-8', $useripaddr );
        }
        return $useripaddr;
    }

	function is_utf8( $string ) {
        if ( preg_match( "/^([" . chr( 228 ) . "-" . chr( 233 ) . "]{1}[" . chr( 128 ) . "-" . chr( 191 ) . "]{1}[" . chr( 128 ) . "-" . chr( 191 ) . "]{1}){1}/", $string ) == true || preg_match( "/([" . chr( 228 ) . "-" . chr( 233 ) . "]{1}[" . chr( 128 ) . "-" . chr( 191 ) . "]{1}[" . chr( 128 ) . "-" . chr( 191 ) . "]{1}){1}$/", $string ) == true || preg_match( "/([" . chr( 228 ) . "-" . chr( 233 ) . "]{1}[" . chr( 128 ) . "-" . chr( 191 ) . "]{1}[" . chr( 128 ) . "-" . chr( 191 ) . "]{1}){2,}/", $string ) == true ) {
            return true;
        } else {
            return false;
        }
    }
//获取IP
function GetIP(){ 
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) 
	$ip = getenv("HTTP_CLIENT_IP"); 
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) 
	$ip = getenv("HTTP_X_FORWARDED_FOR"); 
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) 
	$ip = getenv("REMOTE_ADDR"); 
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) 
	$ip = $_SERVER['REMOTE_ADDR']; 
	else 
	$ip = "unknown"; 
	$ip=htmlspecialchars($ip, ENT_QUOTES);
	if(!get_magic_quotes_gpc())$ip = addslashes($ip);
	return($ip); 
}
//获取域名
function get_domain(){
    $protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
    if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }elseif (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    }else{
        if(isset($_SERVER['SERVER_PORT'])) {
            $port = ':' . $_SERVER['SERVER_PORT'];
            if((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
                $port = '';
            }
        }else{
            $port = '';
        }
        if(isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'].$port;
        }else if(isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'].$port;
        }
    }
    return $protocol.$host;
}
//字符截断,中文算2个字符
function newstr($string, $length, $dot="...") {
	if(strlen($string) <= $length) {return $string;}
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&','"','<','>'), $string);
	$strcut = '';$n = $tn = $noc = $noct = $nc = $tnc =0;
	while($n < strlen($string)) {
		$t = ord($string[$n]);
		if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
			$tn = 1; $n++; $noct++;
		} elseif(194 <= $t && $t <= 223) {
			$tn = 2; $n += 2; $noct += 2;
		} elseif(224 <= $t && $t <= 239) {
			$tn = 3; $n += 3; $noct += 2;
		} elseif(240 <= $t && $t <= 247) {
			$tn = 4; $n += 4; $noct += 2;
		} elseif(248 <= $t && $t <= 251) {
			$tn = 5; $n += 5; $noct += 2;
		} elseif($t == 252 || $t == 253) {
			$tn = 6; $n += 6; $noct += 2;
		} else {$n++;}
		if($noct >= $length){if($noct==0)$noc=$noct;if($nc==0)$nc=$n;if($tnc==0)$tnc=$tn;}
	}
	if($noct<=$length){return str_replace(array('&','"','<','>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);}
	if($noc > $length) {$nc -= $tnc;}
	$strcut = substr($string, 0, $nc);
	$strcut = str_replace(array('&','"','<','>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
	return $strcut.$dot;
}
//编辑器代码
function code_body($value,$type=1){
	if($type!=1){
		$ma='/<pre class=\"prettyprint\s.*?\">(.*?)<\/pre>/si';
	}else{
		$ma='/<pre class=\\\"prettyprint\s.*?\\\">(.*?)<\/pre>/si';
	}
	preg_match_all($ma,$value,$newbody);
	$newbody=array_unique($newbody[1]);
	foreach($newbody as $v){
		if($type!=1){
			$s=str_replace(array('&lt;','&gt;','&quot;'),array('&amp;lt;','&amp;gt;','&amp;quot;'),$v);
		}else{
			$s=str_replace(array('&amp;lt;','&amp;gt;','&amp;quot;'),array('&lt;','&gt;','&quot;'),$v);
		}
		if($s){
			$value=str_ireplace($v,$s,$value);
		}
	}
	return $value;
}
//数据操作过滤
function is_escape($value) {
	if(is_null($value))return 'NULL';
	if(is_bool($value))return $value ? 1 : 0;
	if(is_int($value))return (int)$value;
	if(is_float($value))return (float)$value;
	$value=htmlspecialchars(trim($value));
	if(!get_magic_quotes_gpc())$value = addslashes($value);
	return $value;
}
//替换url参数
function url_set_value($url,$key,$value) { 
	parse_str($url,$arr); 
	$arr[$key]=$value;
	return '?'.http_build_query($arr); 
}
//价格计算 1+,2-
function calculate($v1,$v2,$type=1) {
	if($type==1){
		$value=$v1+$v2;
	}else{
		$value=$v1-$v2;
	}
	$value=floor($value*100);
	return $value/100;
}
//内容推荐名称
function traitinfo($traitid){
	$traitid=trim($traitid, ',');
	$traitinfo=syDB('traits')->findAll(' id in ('.$traitid.') ',null,'id,name');
	foreach($traitinfo as $v){
		$trait.=' '.$v['name'];
	}
	return $trait;
}
//栏目名称
function navname($nid){
	$n=syDB('navigators')->find(array('nid' => $nid),null,'nname');
	return $n['nname'];
}
//栏目信息
function navinfo($nid,$q){
	$t=syDB('navigators')->find(array('nid' => $nid),null,$q);
	return $t[$q];
}

//频道信息获取
function channelsinfo($cmark,$q){
	$m=syDB('channels')->find(array('cmark' => $cmark),null,$q);
	return $m[$q];
}
//内容信息获取
function contentinfo($molds,$id,$q,$newstr=FALSE,$length=0){
	$c=syDB($molds)->find(array('id' => $id),null,$q);
	if(true==$newstr){
	    return newstr($c[$q], $length);
	}
	return $c[$q];
}
//内容获取（文章内容、商品简介）
function detailinfo($molds,$id,$q,$newstr=FALSE,$length=0,$dot="..."){
    $c=syDB($molds."_field")->find(array('aid' => $id),null,$q);
    if(true==$newstr){
        return newstr($c[$q], $length,$dot);
    }
    return $c[$q];
}
//内容总数获取
function content_number($molds,$nid){
    $num=syDB($molds)->findCount(array('nid' => $nid,'statu'=>1));
    return $num;
}
function caseTags($tags){
    $strPattern = "/(?<=#)[^#]+/";
    $arrMatches = array();
    preg_match_all($strPattern, $tags, $arrMatches);
    $newTags = array();
    foreach ( $arrMatches[0] as $arrMatch ){
        $altPattern = "/(?<=@)[^@]+/";
        $tagInfo = array();
        preg_match_all($altPattern, $arrMatch,$tagInfo);
        if( $tagInfo[0] ){
            $newTags[] = array(
                'text' => $tagInfo[0][0],
                'class' => ''
            );
        }else{
            $newTags[] = array(
                'text' => $arrMatch,
                'class' => 'gray'
            );
        }
    }
    return $newTags;
}

//获取回复详情
function replyinfo($mid,$table){
	$table_r=$table.'_reply';
	$r=syDB($table_r)->findAll(array('mid'=>$mid)," `retime`,`upid` desc");
	foreach ($r as $v){
		if($v['adminid']==1){ //管理员回复
			$admininfo=adminuser_info($v['reuid'],' `user`,`avator` ');
			if($v['upid']==0){ //首次回复
				$messageinfo=syDB($table)->find(array('id'=>$v['mid']));
				$re_memberinfo=memberinfo($messageinfo,' `nickname`,`portrait` ');
				$v=array_merge($v,array('reply_detail'=>$messageinfo['detail'],'reply_name'=>$re_memberinfo['nickname'],'reply_portrait'=>$re_memberinfo['portrait']));
			}else{ //其他回复
				$replyinfo=$r=syDB($table_r)->find(array('id'=>$v['upid']));
				$re_memberinfo=memberinfo($replyinfo['reuid'],' `nickname`,`portrait` ');
				$v=array_merge($v,array('reply_detail'=>$replyinfo['reply'],'reply_name'=>$re_memberinfo['nickname'],'reply_portrait'=>$re_memberinfo['portrait']));
			}
			$v=array_merge($v,array('admin_user'=>$admininfo['user'],'admin_avator'=>$admininfo['avator']));
			$re[]=$v;
		}else{ //会员回复
			$memberinfo=memberinfo($v['reuid'],' `nickname`,`portrait` ');
			$v=array_merge($v,array('member_nickname'=>$memberinfo['nickname'],'member_portrait'=>$memberinfo['portrait']));
			$re[]=$v;
		}
	}
	return $re;
}

//回复内容获取
function replydetail($table,$upid,$q){
	$table_r=$table.'_reply';
	$r=syDB($table_r)->find(array('id'=>$upid));
	if($q){
		
	}
	return is_string($r[$q]) ? newstr($r[$q], 40) : $r[$q];
}
//管理员信息获取
function adminuser_info($uid,$q){
	$m=syDB('admin')->find(array('uid' => $uid),null,$q);
	return $m;
}
//管理员单个字段获取
function adminuser_oneinfo($uid,$q){
    $m=syDB('admin')->find(array('uid' => $uid),null,$q);
    return $m[$q];
}
//会员信息获取
function memberinfo($uid,$q){
	$m=syDB('member')->find(array('uid' => $uid),null,$q);
	return $m;
}
//会员单个字段获取
function memberoneinfo($uid,$q){
	$m=syDB('member')->find(array('uid' => $uid));
	if($q=="nickname"){ //返回会员昵称/用户名
	    return $m[$q]!='' ? $m[$q] : $m['username'];
	}
	return $m[$q];
}
//会员组信息获取
function membergroup($gid,$q){
	$m=syDB('member_group')->find(array('gid ' => $gid),null,$q);
	return $m[$q];
}
//计算时间差
function timediff($timediff){
     $days = intval($timediff/86400);
     $remain = $timediff%86400;
     $hours = intval($remain/3600);
     $remain = $remain%3600;
     $mins = intval($remain/60);
     $secs = $remain%60;
     $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
     return $res;
}

//高亮搜索结果
function highLight($str,$sstr){
	$str=preg_replace("/($sstr)/i","<font color=\"red\">\\1</font>",$str);
	return $str;
}

//选择排序数组
function selectSort($arr,$style,$pk)
{
	$temp=0;
	$flag=false;
	for($i=0;$i<count($arr)-1;$i++)
	{
		for($j=$i+1;$j<count($arr);$j++)
		{
			if($style=='bts'){
				$op=$arr[$i][$pk]<$arr[$j][$pk];
			}elseif($style=='stb') {
				$op=$arr[$i][$pk]>$arr[$j][$pk];
			}
			if($op)
			{
				$temp=$arr[$i];
				$arr[$i]=$arr[$j];
				$arr[$j]=$temp;
				$flag=true;
			}
		}
		if($flag==false)
		{
			break;
		}
	}
	return $arr;
}

//判断是否浏览过待办事项
function viewStatu($id,$nid){
	return syDB('backlog')->find(array('id' => $id,'nid'=>$nid,'view'=>0));
}

function get_traits_image($traits){
	$traits=join(",",explode("|", trim($traits,"|")));
	$condition="`tid` IN (".$traits.") ";
	$traits_lists=syDB('traits')->findAll($condition);
	foreach ($traits_lists as $t){
		if($t['icon']!=''){
			$img_str.='<img src="'.$GLOBALS['WWW'].$t['icon'].'" class="trait-img" />';
		}else {
			$img_str.='';
		}
	}
	return $img_str;
}

//获取评论
function getComment($condition){
	return syDB("comment")->findAll($condition);
}

//获取评论总数
function total_comment($aid,$cmark){
	return syDB("comment")->findCount(array('aid'=>$aid,'cmark'=>$cmark,'statu'=>1));
}

//获取在线报备
function getReport($audit){
    if($audit){
        $condition=array('nid'=>29,'statu'=>0);
    }else{
        $condition=array('nid'=>29);
    }
    return syDB("message")->findAll($condition);
}

//插件信息获取
function funsinfo($funs,$q){
	$f=syDB('functions')->find(array('fmark' => $funs),null);
	$fvalue=json_decode($f['fvalue'],true);
	return $fvalue[$q];
}
//规格获取
function attributetype($tid,$v=''){
	$type=syDB('attribute_type')->find(array('tid' => $tid));
	if($v==''){return $type;}else{return $type[$v];}
}
//规格选项获取
function attribute($id,$is=0,$v=''){
	if($is==0){$a=array('tid' => $id);}else{$a=array('sid' => $id);}
	$attribute=syDB('attribute')->find($a);
	if($v==''){return $attribute;}else{return $attribute[$v];}
}
//规格选项列表获取
function product_attribute($tid,$aid){
	$db=$GLOBALS['WP']['db']['prefix'];
	return syDB('product_attribute')->findSql('select * from '.$db.'product_attribute a left join '.$db.'attribute b on (a.sid=b.sid and a.aid='.$aid.') where a.tid='.$tid.' and b.isshow=1 order by b.orders desc,b.sid desc');
}
//自定义字段，单选多选项名获取
function fieldsinfo($fmark,$key,$cmark='article'){
	$f=syDB('fields')->find(array('fmark' => $fmark,'cmark' => $cmark));
	return $f[$key];
}
//返回多附件字段数组
function fileall($fileall){
	if($fileall!=''){
		$fileall=explode(',',$fileall);
		$f=array();
		foreach($fileall as $v){
			$v=explode('|',$v);
			$f=array_merge($f,array(array($v[0],$v[1])));
		}
		return $f;
	}
}
//头像截取配置信息
function resize( $ori ){
	if( preg_match('/^http:\/\/[a-zA-Z0-9]+/', $ori ) ){
		return $ori;
	}
	$info = getImageInfo($ori );
	if( $info ){
		//上传图片后切割的最大宽度和高度
		$width = 500;
		$height = 500;
		$scrimg = $ori;
		if( $info['type']=='jpg' || $info['type']=='jpeg' ){
			$im = imagecreatefromjpeg( $scrimg );
		}
		if( $info['type']=='gif' ){
			$im = imagecreatefromgif( $scrimg );
		}
		if( $info['type']=='png' ){
			$im = imagecreatefrompng( $scrimg );
		}
		if( $info['width']<=$width && $info['height']<=$height ){
			return;
		} else {
			if( $info['width'] > $info['height'] ){
				$height = intval( $info['height']/($info['width']/$width) );
			} else {
				$width = intval( $info['width']/($info['height']/$height) );
			}
		}
		$newimg = imagecreatetruecolor( $width, $height );
		imagecopyresampled( $newimg, $im, 0, 0, 0, 0, $width, $height, $info['width'], $info['height'] );
		imagejpeg( $newimg, $ori );
		imagedestroy( $im );
	}
	return;
}

function getImageInfo( $img ){
	$imageInfo = getimagesize($img);
	if( $imageInfo!== false) {
		$imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
		$info = array(
				"width"		=>$imageInfo[0],
				"height"	=>$imageInfo[1],
				"type"		=>$imageType,
				"mime"		=>$imageInfo['mime'],
		);
		return $info;
	}else {
		return false;
	}
}
//自定义字段列表
function fields_info($nid,$cmark='',$lists=0,$c=array()){
	GLOBAL $__controller;
	$hand=date('His').mt_rand(1000,9999);
	$allfields=array();
	$fieldswhere=" `statu`=1 and `issubmit`=1 ";
	if($cmark){$fieldswhere.=" and `cmark`='".$cmark."'";}
	if($nid!=0){$fieldswhere.=" and `navigators` like '%|".$nid."|%' ";}
	if($lists){$fieldswhere.=" and `lists`=1 ";}
	$v=syDB('fields')->findAll($fieldswhere,' `order` DESC,`fid` ');
	foreach($v as $f){
		$m='';
		switch ($f['ftype']){
			case 'varchar':
			    if($nid!=0){
    				$t='<input name="'.$f['fmark'].'" id="'.$f['fmark'].'" type="text" class="form-control" value="'.$c[$f['fmark']].'" placeholder="'.$f['fname'].'" />';
    				$m='最多'.$f['flength'].'个字';
			    }else {
			        $t='<input name="'.$f['fmark'].'" id="'.$f['fmark'].'" type="text" class="form-control" value="'.$c[$f['fmark']].'" placeholder="'.$f['fname'].'(不超过'.$f['flength'].'个字符)" />';
			    }
			break;
			case 'text':
				$fw=$f['imgw']!=0 ? $f['imgw'].'px' : '100%';
				$fh=$f['imgh']!=0 ? $f['imgh'].'px' : '100%';
				$t='<script type="text/javascript">$(function(){KindEditor.create("#'.$f['fmark'].'",{resizeType : 1,allowPreviewEmoticons : false,allowImageUpload : false,items : ["fontname", "fontsize", "|", "forecolor", "hilitecolor", "bold", "italic", "underline","removeformat", "|", "justifyleft", "justifycenter", "justifyright", "insertorderedlist","insertunorderedlist", "|", "emoticons", "image", "link"]})});</script>';
				$t.='<textarea placeholder="'.$f['fname'].'" name="'.$f['fmark'].'" id="'.$f['fmark'].'" style="width:'.$fw.';height:'.$fh.';">'.$c[$f['fmark']].'</textarea>';
			break;
			case 'int':
				$t='<input placeholder="'.$f['fname'].'" name="'.$f['fmark'].'" id="'.$f['fmark'].'" type="text" class="form-control" value="'.$c[$f['fmark']].'" />';
				$m='请输入整数格式，可为负数';
			break;
			case 'money':
				$t='<input placeholder="'.$f['fname'].'" name="'.$f['fmark'].'" id="'.$f['fmark'].'" type="text" class="form-control" value="'.$c[$f['fmark']].'" />';
				$m='请输入货币格式，如2.03';
			break;
			case 'date':
				if($c[$f['fmark']]!=''){$time=date('Y-m-d H:i',$c[$f['fmark']]);}else{$time=date('Y-m-d H:i');}
				$t='<input placeholder="'.$f['fname'].'" name="'.$f['fmark'].'" id="'.$f['fmark'].'" type="text" class="form-control" value="'.$time.'" onClick="WdatePicker({dateFmt:';$t.="'yyyy-MM-dd HH:mm'";$t.='})" />';
			break;
			case 'file':
			$t='<table border="0" cellspacing="0" cellpadding="0"><tr><td><input name="'.$f['fmark'].'" id="'.$f['fmark'].'" type="text" class="form-control" value="'.$c[$f['fmark']].'" /></td><td width="5"></td><td><iframe frameborder="0" width="300" height="26" scrolling="No" src="'.$GLOBALS["WWW"].'index.php?action='.$__controller.'&o=m_upload_load&inputid='.$f['fmark'].'&hand='.$hand.'&cmark='.$cmark.'&nid='.$nid.'&aid='.$c['id'].'" style="float:left;"></iframe><input name="hand" type="hidden" value="'.$hand.'"></td></tr></table>';
			break;
			case 'multifile':
			$t='<table border="0" cellspacing="0" cellpadding="0"><tr><td><input name="'.$f['fmark'].'" id="'.$f['fmark'].'" type="text" class="form-control" value="'.$c[$f['fmark']].'" /></td><td width="5"></td><td><iframe frameborder="0" width="300" height="26" scrolling="No" src="'.$GLOBALS["WWW"].'index.php?action='.$__controller.'&o=m_upload_load&inputid='.$f['fmark'].'&hand='.$hand.'&cmark='.$cmark.'&nid='.$nid.'&aid='.$c['id'].'" style="float:left;"></iframe><input name="hand" type="hidden" value="'.$hand.'"></td></tr></table>';
			break;
			case 'radio':
				$t='<label  style="width:120px"><select class="chosen-select" name="'.$f['fmark'].'" id="'.$f['fmark'].'">';
				foreach(explode(',',$f['selects']) as $v){
					$s=explode('=',$v);
					$t.='<option hassubinfo="true" value="'.$s[1].'" ';
					if($c[$f['fmark']]==$s[1])$t.='selected="selected"';
					$t.='>'.$s[0].'</option>';
				}
				$t.='</select></label>';
			break;
			case 'select':
				$t='<label  style="width:120px"><select class="chosen-select" name="'.$f['fmark'].'" id="'.$f['fmark'].'" multiple="">';
				foreach(explode(',',$f['selects']) as $v){
					$s=explode('=',$v);
					$t.='<option hassubinfo="true" value="'.$s[1].'" ';
					if($c[$f['fmark']]==$s[1])$t.='selected="selected"';
					$t.='>'.$s[0].'</option>';
				}
				$t.='</select></label>';
			break;
		}
		$allfields=array_merge($allfields,array(array('name'=>$f['fname'],'input'=>$t,'fmark'=>$f['fmark'],'m'=>$m)));
	}
	return $allfields;
}
//获取分页总数
function  total_count($sql,$v='wp_total_page'){
	$a=syDB('channels')->findSql('select count(*) as '.$v.' from '.$sql);
	return $a[0][$v];
}
//支付平台
function payment($pay){
	$payment=syDB('payment')->find(array('pay' => $pay),null,'name');
	return $payment['name'];
}
//订单状态
function order_state($state,$type){
	$a=array(
		0=>'未支付',
		1=>'已支付待发货',
		2=>'已发货待确认',
		9=>'已完成',
		3=>'换货',
		4=>'退货',
	);
	switch($type){
		case 1:
			foreach($a as $k=>$v){
				$t.='<input name="state" type="radio" value="'.$k.'"';
				if($state==$k){$t.=' checked="checked"';$v='<strong>'.$v.'</strong>';}
				$t.=' />'.$v.'&nbsp;';
			}
		break;
		case 2:
			foreach($a as $k=>$v){
				$t.='<option value="'.$k.'"';
				if($state==$k)$t.=' selected="selected"';
				$t.='>'.$v.'</option>';
			}
		break;
		default:$t=$a[$state];break;
	}
	echo $t;
}
//获取订单内容
function order_goods($d,$logistics){
	foreach($d as $k=>$v){
		$va=syDB('product')->find(array('id'=>$v['aid'],'isshow'=>1),null,'title,tid,price,logistics');
		$goods[$k]['aid']=$v['aid'];
		$goods[$k]['attribute']=$v['attribute'];
		$goods[$k]['quantity']=$v['quantity'];
		$goods[$k]['title']=$va['title'];
		$goods[$k]['tid']=$va['tid'];
		$logistics_price=unserialize($va['logistics']);
		$goods[$k]['logistics_price']=$logistics_price[$logistics]*$v['quantity'];
		$p_type=syDB('attribute_type')->findSql('select distinct a.tid,a.aid,b.tid,b.isshow,b.orders,b.name from '.$GLOBALS['WP']['db']['prefix'].'product_attribute a left join '.$GLOBALS['WP']['db']['prefix'].'attribute_type b on (a.tid=b.tid) where a.aid='.$v['aid'].' and b.isshow=1 order by b.orders desc,b.tid desc');
		$ov['price']=$va['price'];$ov['txt']='';
		foreach($p_type as $s){
			$p=syDB('product_attribute')->find(array('aid' => $v['aid'],'tid' => $s['tid'],'sid' => $v['attribute'][$s['tid']]),null,'price');
			$ov['price']=$ov['price']+$p['price'];
			$a=syDB('attribute')->find(array('sid' => $v['attribute'][$s['tid']]),null,'name');
			$ov['txt'].=$s['name'].'('.$a['name'].') ';
		}			
		$goods[$k]['attribute_txt']=$ov['txt'];
		$goods[$k]['price']=$ov['price'];
		$goods[$k]['total']=$ov['price']*$v['quantity'];
		$aggregate+=$goods[$k]['total']+$goods[$k]['logistics_price'];
	}
	$t[0]=$goods;$t[1]=$aggregate;
	return $t;
}

/**
 * 计算给定时间戳与当前时间相差的时间，并以一种比较友好的方式输出
 * @param  [int] $timestamp [给定的时间戳]
 * @param  [int] $current_time [要与之相减的时间戳，默认为当前时间]
 * @return [string]            [相差天数]
 */
function tmspan($timestamp,$current_time=0){
    if(!$current_time) $current_time=time();
    $span=$current_time-$timestamp;
    if($span<60){
        return "刚刚";
    }else if($span<3600){
        return intval($span/60)."分钟前";
    }else if($span<24*3600){
        if( $span>3*3600 && $timestamp > strtotime(date('Y-m-d 00:00:00')) ) {
            return '今天'.date('H:i',$timestamp);
        }
        return intval($span/3600)."小时前";
    }else if($span<(2*24*3600)){
        return '昨天'.date('H:i',$timestamp);
    }else{
        return date('m-d H:i',$timestamp);
    }
}

//时间比较
function newest($t,$h){
	$t=(time()-$t)/3600;
	if($t < $h){return true;}else{return false;}
}
//替换内容静态规则
function html_rules($cmark,$nid,$d,$id='',$f=''){
	if($f=='')$f=$id;
	if(strpos(','.syExt('site_statichtml_rules'),'[type]')!==FALSE){
		$type=syDB('navigators')->find(array('nid'=>$nid),null,'htmldir');
		if($type['htmldir']!=''){
			$typedir=$type['htmldir'];
		}else{$typedir='c/'.$nid;}
	}
	$u=syExt('site_statichtml_dir').'/'.str_replace(array('[y]','[m]','[d]','[id]','[file]','[channel]','[type]'),array(date('Y',$d),date('m',$d),date('d',$d),$id,$f,$cmark,$typedir),syExt('site_statichtml_rules'));
	return str_replace(array('///','//'),'/',$u);
}
//页面URL判断
function html_url($type,$c,$pages=0,$ispage,$molds){
	if($c['gourl']!='')return $c['gourl'];
	$sh=syExt("site_statichtml");
	$sr=$GLOBALS['WP']['url']["url_path_base"];
	$sg=$GLOBALS["WWW"];
	$re=$GLOBALS['WP']['rewrite']["rewrite_open"];
	switch($type){
		case 'channel':
			if($re==1){
				$re_url=$sg.$GLOBALS['WP']['rewrite']["rewrite_channel"];
				if($c['htmlfile']=='')$c['htmlfile']=$c['id'];
				$go_url=str_replace(array('{id}','{file}','{molds}'),array($c['id'],$c['htmlfile'],$molds),$re_url);
				$go_url=str_replace(array('{channel}'),'channel',$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0&&$c['mgold']==0&&$c['htmlurl']!=''){
				$go_url=$sg.$c['htmlurl'];
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url=$sr.'?c=channel&molds='.$molds.'&id='.$c['id'];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		case 'article':
			if($re==1){
				if($molds!='type'){
					$re_url=$sg.$GLOBALS['WP']['rewrite']["rewrite_article"];
					if($c['htmlfile']=='')$c['htmlfile']=$c['id'];
					$go_url=str_replace(array('{id}','{file}'),array($c['id'],$c['htmlfile']),$re_url);
					$go_url=str_replace(array('{article}'),'article',$go_url);
					if($pages!==0){
						$go_url=str_replace('{page}','[p]',$go_url);
						$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
					}else{$go_url=str_replace('{page}',1,$go_url);}
				}else{
					
				}
			}else if($sh==1&&$c['mrank']==0&&$c['htmlurl']!=''){
				$go_url=$sg.$c['htmlurl'];
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				if($molds!='type'){
					$go_url=$sr.'?action=article&id='.$c['id'];
					if($pages!==0)$go_url=pagetxt($pages);
				}else{
					$go_url=$sr.'?action=article&o=type&nid='.$c['nid'];
				}
			}
		break;
		case 'product':
			if($re==1){
				$re_url=$sg.$GLOBALS['WP']['rewrite']["rewrite_product"];
				if($c['htmlfile']=='')$c['htmlfile']=$c['id'];
				$go_url=str_replace(array('{id}','{file}'),array($c['id'],$c['htmlfile']),$re_url);
				$go_url=str_replace(array('{product}'),'product',$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0&&$c['htmlurl']!=''){
				$go_url=$sg.$c['htmlurl'];
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url=$sr.'?action=product&id='.$c['id'];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		case 'recruitment':
			if($re==1){
				$re_url=$sg.$GLOBALS['WP']['rewrite']["rewrite_recruitment"];
				if($c['htmlfile']=='')$c['htmlfile']=$c['id'];
				$go_url=str_replace(array('{id}','{file}'),array($c['id'],$c['htmlfile']),$re_url);
				$go_url=str_replace(array('{recruitment}'),'recruitment',$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0&&$c['htmlurl']!=''){
				$go_url=$sg.$c['htmlurl'];
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url=$sr.'?action=recruitment&id='.$c['id'];
				if($pages!==0)$go_url=pagetxt($pages);
			}
			break;
		case 'message':
		break;
		case 'navigators':
			if($re==1){
                if($c["cmark"]!='article'&&$c["cmark"]!='product'&&$c["cmark"]!='message'&&$c["cmark"]!='recruitment'){
					$re_url=$sg.$GLOBALS['WP']['rewrite']['rewrite_channel_type'];
				}else{
					$re_url=$sg.$GLOBALS['WP']['rewrite']['rewrite_'.$c['cmark'].'_type'];
				}
				$go_url=str_replace(array('{nid}','{file}'),array($c['nid'],$c['htmlfile']),$re_url);
				$go_url=str_replace(array('{'.$c['cmark'].'}','{type}','{channel}'),array($c['cmark'],type,'channel'),$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0){
				$noindex=syExt("site_html_index");
				if($noindex==1&&$pages==0){$html_file=='';}else{
					if($c["htmlfile"]!=''){$html_file=$c["htmlfile"].syExt("site_html_suffix");}
					else{$html_file='index'.syExt("site_html_suffix");}
				}
				if($c["htmldir"]==''){
					$go_url=$sg.syExt("site_html_dir")."/navgator/".$c["nid"]."/".$html_file;
				}else{ 
					$go_url=$sg.$c["htmldir"]."/".$html_file;
				}
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{ 
				if($c["cmark"]!='article'&&$c["cmark"]!='product'&&$c["cmark"]!='message'&&$c["cmark"]!='recruitment'){
					$go_url=$sr."?action=channel&o=type&nid=".$c["nid"];
				}else{
					$go_url=$sr."?action=".$c["cmark"]."&o=type&nid=".$c["nid"];
				}
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		case 'labelcus_custom':
			if($re==1){
				$re_url=$sg.$GLOBALS['WP']['rewrite']["rewrite_labelcus_custom"];
				$go_url=str_replace(array('{file}'),array($c['file']),$re_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($c["html"]==1){ 
				$html_file=$c["file"];
				if($c["dir"]==''){ 
					$go_url=$sg.syExt("site_html_dir")."/".$html_file;
				}else{
					$go_url=$sg.$c["dir"]."/".$html_file;
				}
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url="index.php?file=".$c["file"];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		default:
			if($re==1){
				$re_url=$sg.$GLOBALS['WP']['rewrite']["rewrite_channel"];
				if($c['htmlfile']=='')$c['htmlfile']=$c['id'];
				$go_url=str_replace(array('{id}','{file}','{molds}'),array($c['id'],$c['htmlfile'],$type),$re_url);
				$go_url=str_replace(array('{channel}'),'channel',$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0&&$c['mgold']==0&&$c['htmlurl']!=''){
				$go_url=$sg.$c['htmlurl'];
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url=$sr.'?c=channel&molds='.$type.'&id='.$c['id'];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
	}
	return $go_url;
}
//分页代码
function pagetxt($pagearray,$pageno=3,$vp='page'){
	//$page=$_POST['page'];
	if($_POST['page']>$pagearray['total_page']){
		message('不存在该页',null ,0);
	}
	if($pagearray['total_count']>0)$pagetxt.='<form action="" method="post"><a class="btn btn-white">第 '.$pagearray['current_page'].'/'.$pagearray['total_page'].' 页</span></a>';
	$pageurl=$_SERVER["QUERY_STRING"];
	if($pagearray['current_page']>1){
		$pagetxt.='<a class="btn btn-white" href="'.url_set_value($pageurl,$vp,1).'"><i class="fa fa-fast-backward"></i></a><a class="btn btn-white" href="'.url_set_value($pageurl,$vp,$pagearray['prev_page']).'"><i class="fa fa-chevron-left"></i></a>';
	}
	$pageno1=$pagearray['current_page']-$pageno;if($pageno1<1)$pageno1=1;
	$pageno2=$pagearray['current_page']+$pageno;if($pageno2>$pagearray['total_page']){$pageno2=$pagearray['total_page'];}
	while($pageno1<=$pageno2){
		if($pagearray['current_page']==$pageno1){$pagetxt.='<a class="btn btn-white active">'.$pageno1.'</a>';}else{$pagetxt.='<a class="btn btn-white" href="'.url_set_value($pageurl,$vp,$pageno1).'">'.$pageno1.'</a>';}
		$pageno1++;
	}
	if($pagearray['current_page'] < $pagearray['last_page']){
		$pagetxt.='<a class="btn btn-white" href="'.url_set_value($pageurl,$vp,$pagearray['next_page']).'"><i class="fa fa-chevron-right"></i></a><a class="btn btn-white" href="'.url_set_value($pageurl,$vp,$pagearray['last_page']).'"><i class="fa fa-fast-forward"></i></a>';
	}
	if($pagearray['total_count']>0)$pagetxt.='<a class="btn btn-white">共 '.$pagearray['total_count'].'篇 </a><input type="text" name="page" class="form-control W-20 H-34" /><button class="btn btn-white" type="submit">前往</button></form>';
	return $pagetxt;
}
//静态html分页代码
function pagetxt_html($url,$total_page,$current_page,$pageno=3){
	if($GLOBALS['WP']['rewrite']["rewrite_open"]==1){$is_p=1;}else{$is_p='';}
	$pagetxt='';
	if($total_page>0)$pagetxt.='<li><a>共'.$total_page.'篇</a></li>';
	$n=$current_page+1;$p=$current_page-1;
	if($current_page>1){
		$pagetxt.='<li><a href="'.str_replace('[p]',$is_p,$url).'">首页</a></li>';
		if($current_page==2){$pagetxt.='<li><a href="'.str_replace('[p]',$is_p,$url).'">上一页</a></li>';
		}else{$pagetxt.='<li><a href="'.str_replace('[p]',$p,$url).'">上一页</a></li>';}
	}
	$pageno1=$current_page-$pageno;if($pageno1<1)$pageno1=1;
	$pageno2=$current_page+$pageno;if($pageno2>$total_page)$pageno2=$total_page;
	while($pageno1<=$pageno2){
		if($current_page==$pageno1){$pagetxt.='<li class="c">'.$pageno1.'</li>';}else{
			if($pageno1==1){$pagetxt.='<li><a href="'.str_replace('[p]',$is_p,$url).'">'.$pageno1.'</a></li>';
			}else{$pagetxt.='<li><a href="'.str_replace('[p]',$pageno1,$url).'">'.$pageno1.'</a></li>';}
		}
		$pageno1++;
	}
	if($current_page < $total_page){
		$pagetxt.='<li><a href="'.str_replace('[p]',$n,$url).'">下一页</a></li><li><a href="'.str_replace('[p]',$total_page,$url).'">尾页</a></li>';
	}
	return $pagetxt;
}
//其他分页
function pagetxt_other($pagearray,$url,$syarg,$pageno=3){
	$pagetxt='';
	if($pagearray['total_count']>0)$pagetxt.='<li><a>共'.$pagearray['total_count'].'条</a></li>';
	if($pagearray['current_page']>1){
		$pagetxt.='<li><a href="'.$url.'&'.$syarg.'=1">首页</a></li><li><a href="'.$url.'&'.$syarg.'='.$pagearray['prev_page'].'">上一页</a></li>';
	}
	$pageno1=$pagearray['current_page']-$pageno;if($pageno1<1){$pageno1=1;}
	$pageno2=$pagearray['current_page']+$pageno;if($pageno2>$pagearray['total_page']){$pageno2=$pagearray['total_page'];}
	while($pageno1<=$pageno2){
		if($pagearray['current_page']==$pageno1){$pagetxt.='<li class="c">'.$pageno1.'</li>';}else{$pagetxt.='<li><a href="'.$url.'&'.$syarg.'='.$pageno1.'">'.$pageno1.'</a></li>';}
		$pageno1++;
	}
	if($pagearray['current_page'] < $pagearray['last_page']){
		$pagetxt.='<li><a href="'.$url.'&'.$syarg.'='.$pagearray['next_page'].'">下一页</a></li><li><a href="'.$url.'&'.$syarg.'='.$pagearray['last_page'].'">尾页</a></li>';
	}
	return $pagetxt;
}
//ajax分页
function pagetxt_ajax($pagearray,$url,$ajax,$pageno=3){
	$pagetxt='';
	if($pagearray['total_count']>0)$pagetxt.='<a class="btn btn-white">共'.$pagearray['total_count'].'条</a>';
	if($pagearray['current_page']>1){
		$pagetxt.='<a class="btn btn-white" onClick="'.str_replace('[_page_]',1,$ajax).'"><i class="fa fa-fast-backward"></i></a><a class="btn btn-white" onClick="'.str_replace('[_page_]',$pagearray['prev_page'],$ajax).'"><i class="fa fa-chevron-left"></i></a>';
	}
	$pageno1=$pagearray['current_page']-$pageno;if($pageno1<1){$pageno1=1;}
	$pageno2=$pagearray['current_page']+$pageno;if($pageno2>$pagearray['total_page']){$pageno2=$pagearray['total_page'];}
	while($pageno1<=$pageno2){
		if($pagearray['current_page']==$pageno1){
			$pagetxt.='<a class="btn btn-white active">'.$pageno1.'</a>';
		}else{
			$pagetxt.='<a class="btn btn-white" onClick="'.str_replace('[_page_]',$pageno1,$ajax).'">'.$pageno1.'</a>';
		}
		$pageno1++;
	}
	if($pagearray['current_page'] < $pagearray['last_page']){
		$pagetxt.='<a class="btn btn-white" onClick="'.str_replace('[_page_]',$pagearray['next_page'],$ajax).'"><i class="fa fa-chevron-right"></i></a><a class="btn btn-white" onClick="'.str_replace('[_page_]',$pagearray['last_page'],$ajax).'">尾页</a>';
	}
	return $pagetxt;
}
