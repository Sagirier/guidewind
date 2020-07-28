<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class system extends syController{
	public $pk = "sid";
	public $table = "system";
	function __construct(){
		parent::__construct();	
		$this->a='system';	
		$this->o=$this->syArgs('o',1);
		//$this->Class=syClass("c_functions");
	}
	function uploads(){
		if(!$this->userClass->checkgo('uploads')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="文件上传设置";
		$this->uploads=array(
			'filetype' => $GLOBALS['WP']['ext']['filetype'],
			'filesize' => floor($GLOBALS['WP']['ext']['filesize']/1024),	
			'thumbnail' => $GLOBALS['WP']['ext']['thumbnail'],	
			'img_w' => $GLOBALS['WP']['ext']['img_w'],	
			'img_h' => $GLOBALS['WP']['ext']['img_h'],	
			'imgwater' => $GLOBALS['WP']['ext']['imgwater'],	
			'imgwater_type' => $GLOBALS['WP']['ext']['imgwater_type']
		);
		if($this->syArgs('go')==1){
			$uploads_config=array('filetype','filesize','thumbnail','img_w','img_h','imgwater','imgwater_type');
			$this->writeConfig($uploads_config);
			message("修改成功");
		}
		$this->display("system.html");
	}
	function statichtml(){
		if(!$this->userClass->checkgo('statichtml')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="静态html设置";
		$this->statichtml=array(
			'site_statichtml' => $GLOBALS['WP']['ext']['site_statichtml'],
			'site_statichtml_dir' => $GLOBALS['WP']['ext']['site_statichtml_dir'],
			'site_statichtml_navrules' => $GLOBALS['WP']['ext']['site_statichtml_navrules'],
			'site_statichtml_contentrules' => $GLOBALS['WP']['ext']['site_statichtml_contentrules'],
			'site_statichtml_rules' => $GLOBALS['WP']['ext']['site_statichtml_rules'],
			'site_statichtml_index' => $GLOBALS['WP']['ext']['site_statichtml_index'],
		);
		if($this->syArgs('go')==1){
			$statichtml_config=array('site_statichtml','site_statichtml_dir','site_statichtml_rules','site_statichtml_index');
			$this->writeConfig($statichtml_config);
			message("修改成功");
		}
		$this->display("system.html");
	}
	function rewrite(){
	    if(!$this->userClass->checkgo('rewrite')){
	        message("您没有该栏目的管理员权限",null,3,7);
	    }
	    $this->title="伪静态设置";
	    $this->rewrite=array(
	        'rewrite_open' => $GLOBALS['WP']['rewrite']['rewrite_open'],
			'rewrite_dir' => $GLOBALS['WP']['rewrite']['rewrite_dir'],
			'rewrite_article' => $GLOBALS['WP']['rewrite']['rewrite_article'],
			'rewrite_article_type' => $GLOBALS['WP']['rewrite']['rewrite_article_type'],
			'rewrite_product' => $GLOBALS['WP']['rewrite']['rewrite_product'],
			'rewrite_product_type' => $GLOBALS['WP']['rewrite']['rewrite_product_type'],
			'rewrite_recruitment' => $GLOBALS['WP']['rewrite']['rewrite_recruitment'],
			'rewrite_recruitment_type' => $GLOBALS['WP']['rewrite']['rewrite_recruitment_type'],
			'rewrite_message_type' => $GLOBALS['WP']['rewrite']['rewrite_message_type'],
	    );
	    if($this->syArgs('go')==1){
	        $rewrite_config=array('rewrite_open','rewrite_dir','rewrite_article','rewrite_article_type','rewrite_product','rewrite_product_type','rewrite_message_type','rewrite_recruitment','rewrite_recruitment_type');
	        $this->writeInclude($rewrite_config);
	        message("修改成功");
	    }
	    $this->display("system.html");
	}
	function rewriterules(){
	    $url_article=preg_match_all('/\{(.*?)\}/si',$this->syArgs('article',1),$r_article);
	    $url_article_type=preg_match_all('/\{(.*?)\}/si',$this->syArgs('article_type',1),$r_article_type);
	    $url_product=preg_match_all('/\{(.*?)\}/si',$this->syArgs('product',1),$r_product);
	    $url_product_type=preg_match_all('/\{(.*?)\}/si',$this->syArgs('product_type',1),$r_product_type);
	    $url_recruitment=preg_match_all('/\{(.*?)\}/si',$this->syArgs('recruitment',1),$r_recruitment);
	    $url_recruitment_type=preg_match_all('/\{(.*?)\}/si',$this->syArgs('recruitment_type',1),$r_recruitment_type);
	    $url_message_type=preg_match_all('/\{(.*?)\}/si',$this->syArgs('message_type',1),$r_message_type);
	    
	    $r_article=$this->rewrite_for($r_article,$this->syArgs('article',1));
	    $r_article_type=$this->rewrite_for($r_article_type,$this->syArgs('article_type',1));
	    $r_product=$this->rewrite_for($r_product,$this->syArgs('product',1));
	    $r_product_type=$this->rewrite_for($r_product_type,$this->syArgs('product_type',1));
	    $r_recruitment=$this->rewrite_for($r_recruitment,$this->syArgs('recruitment',1));
	    $r_recruitment_type=$this->rewrite_for($r_recruitment_type,$this->syArgs('recruitment_type',1));
	    $r_message_type=$this->rewrite_for($r_message_type,$this->syArgs('message_type',1));
	    
	    //apache独立主机规则
	    $at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article[1]));
	    $this->apache='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_article[0]).'$ $1/index.php?'.$at.'&%1'."\r\n";
	    
	    $at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article_type[1]));
	    $this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_article_type[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
	    
	    $at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product[1]));
	    $this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_product[0]).'$ $1/index.php?'.$at.'&%1'."\r\n";
	    
	    $at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product_type[1]));
	    $this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_product_type[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
	    
	    $at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_recruitment[1]));
	    $this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_recruitment[0]).'$ $1/index.php?'.$at.'&%1'."\r\n";
	    
	    $at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_recruitment_type[1]));
	    $this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_recruitment_type[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
	    
	    $at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_message_type[1]));
	    $this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_message_type[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
	    
	    //apache虚拟主机规则
	    $this->apache1='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_article[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_article[1]))).'&%1'."\r\n";
	    $this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_article_type[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_article_type[1]))).'%1'."\r\n";
	    $this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_product[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_product[1]))).'&%1'."\r\n";
	    $this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_product_type[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_product_type[1]))).'%1'."\r\n";
	    $this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_recruitment[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_recruitment[1]))).'&%1'."\r\n";
	    $this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_recruitment_type[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_recruitment_type[1]))).'%1'."\r\n";
	    $this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    $this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_message_type[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_message_type[1]))).'%1'."\r\n";
	    $this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
	    
	    
	    //iis规则
	    $n=$r_article[2]+1;
	    $this->iis='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_article[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article[1])).'&$'.$n."\r\n";
	    $n=$r_article_type[2]+1;
	    $this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_article_type[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article_type[1])).'&$'.$n."\r\n";
	    $n=$r_product[2]+1;
	    $this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_product[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product[1])).'&$'.$n."\r\n";
	    $n=$r_product_type[2]+1;
	    $this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_product_type[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product_type[1])).'&$'.$n."\r\n";
	    
	    $n=$r_recruitment[2]+1;
	    $this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_recruitment[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_recruitment[1])).'&$'.$n."\r\n";
	    $n=$r_recruitment_type[2]+1;
	    $this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_recruitment_type[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_recruitment_type[1])).'&$'.$n."\r\n";
	    
	    $n=$r_message_type[2]+1;
	    $this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_message_type[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_message_type[1])).'&$'.$n."\r\n";
	    
	    //iis7规则
	    $this->iis7='&lt;rule name="article"&gt;'."\r\n";
	    $this->iis7.='	&lt;match url="^(.*/)*'.$r_article[0].'\?*(.*)$" /&gt;'."\r\n";
	    $this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_article[1])).'&{R:'.$r_article[2].'}" /&gt;'."\r\n";
	    $this->iis7.='&lt;/rule&gt;'."\r\n";
	    
	    $this->iis7.='&lt;rule name="article_type"&gt;'."\r\n";
	    $this->iis7.='	&lt;match url="^(.*/)*'.$r_article_type[0].'\?*(.*)$" /&gt;'."\r\n";
	    $this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_article_type[1])).'&a=type&{R:'.$r_article_type[2].'}" /&gt;'."\r\n";
	    $this->iis7.='&lt;/rule&gt;'."\r\n";
	    
	    $this->iis7.='&lt;rule name="product"&gt;'."\r\n";
	    $this->iis7.='	&lt;match url="^(.*/)*'.$r_product[0].'\?*(.*)$" /&gt;'."\r\n";
	    $this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_product[1])).'&{R:'.$r_product[2].'}" /&gt;'."\r\n";
	    $this->iis7.='&lt;/rule&gt;'."\r\n";
	    
	    $this->iis7.='&lt;rule name="product_type"&gt;'."\r\n";
	    $this->iis7.='	&lt;match url="^(.*/)*'.$r_product_type[0].'\?*(.*)$" /&gt;'."\r\n";
	    $this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_product_type[1])).'&a=type&{R:'.$r_product_type[2].'}" /&gt;'."\r\n";
	    $this->iis7.='&lt;/rule&gt;'."\r\n";
	    
	    $this->iis7.='&lt;rule name="recruitment"&gt;'."\r\n";
	    $this->iis7.='	&lt;match url="^(.*/)*'.$r_recruitment[0].'\?*(.*)$" /&gt;'."\r\n";
	    $this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_recruitment[1])).'&{R:'.$r_recruitment[2].'}" /&gt;'."\r\n";
	    $this->iis7.='&lt;/rule&gt;'."\r\n";
	    
	    $this->iis7.='&lt;rule name="recruitment_type"&gt;'."\r\n";
	    $this->iis7.='	&lt;match url="^(.*/)*'.$r_recruitment_type[0].'\?*(.*)$" /&gt;'."\r\n";
	    $this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_recruitment_type[1])).'&a=type&{R:'.$r_recruitment_type[2].'}" /&gt;'."\r\n";
	    $this->iis7.='&lt;/rule&gt;'."\r\n";
	    
	    $this->iis7.='&lt;rule name="message_type"&gt;'."\r\n";
	    $this->iis7.='	&lt;match url="^(.*/)*'.$r_message_type[0].'\?*(.*)$" /&gt;'."\r\n";
	    $this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_message_type[1])).'&a=type&{R:'.$r_message_type[2].'}" /&gt;'."\r\n";
	    $this->iis7.='&lt;/rule&gt;'."\r\n";
	    
	    
	    //nginx规则
	    $this->nginx='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_article[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article[1])).' last;'."\r\n";
	    $this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_article_type[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article_type[1])).' last;'."\r\n";
	    $this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_product[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product[1])).' last;'."\r\n";
	    $this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_product_type[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product_type[1])).' last;'."\r\n";
	    
	    $this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_recruitment[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_recruitment[1])).' last;'."\r\n";
	    $this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_recruitment_type[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_recruitment_type[1])).' last;'."\r\n";
	    
	    $this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_message_type[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_message_type[1])).' last;'."\r\n";
	    
	    $this->display("rewriterules.html");
	}
	function other(){
	    if(!$this->userClass->checkgo('other')){
	        message("您没有该栏目的管理员权限",null,3,7);
	    }
	    $this->title="其他设置";
	    $this->other=array(
	        'enable_gzip' => $GLOBALS['WP']['ext']['enable_gzip'],
	        'enable_gzip_level' =>$GLOBALS['WP']['ext']['enable_gzip_level'],
	        'cache_auto' => $GLOBALS['WP']['ext']['cache_auto'],
	        'cache_time' => $GLOBALS['WP']['ext']['cache_time'],
	        'vercode' => $GLOBALS['WP']['vercode'],
	        'comment_audit' => $GLOBALS['WP']['ext']['comment_audit'],
	        'comment_user' => $GLOBALS['WP']['ext']['comment_user']
	    );
	    if($this->syArgs('go')==1){
	        $other_config=array('enable_gzip','enable_gzip_level','cache_time','comment_audit','comment_user');
	        $include_config=array('vercode');
	        $this->writeConfig($other_config);
	        $this->writeInclude($include_config);
	        message("修改成功");
	    }
	    $this->display("system.html");
	}
	function email(){
		if(!$this->userClass->checkgo('email')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="邮件收发设置";
		$this->email = array(
		    'smtp_server' => $GLOBALS['WP']['sendemail']['smtp_server'],
		    'smtp_serverport' => $GLOBALS['WP']['sendemail']['smtp_serverport'],
		    'smtp_usermail' => $GLOBALS['WP']['sendemail']['smtp_usermail'],
		    'smtp_user' => $GLOBALS['WP']['sendemail']['smtp_user'],
		    'smtp_pass' => $GLOBALS['WP']['sendemail']['smtp_pass'],
        );
		if($this->syArgs('go')==1){
            $email_config=array('smtp_server','smtp_serverport','smtp_usermail','smtp_user','smtp_pass');
            $this->writeConfig($email_config);
            message("修改成功");
		}
		$this->display("system.html");
	}
	function sys_ecache(){
		if(!$this->userClass->checkgo('sys_ecache')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="更新缓存";
		if($this->syArgs('go')==1){
			if($this->syArgs("tmp")==1)deleteDir($GLOBALS['WP']['sp_cache']);
			if($this->syArgs("tpl")==1)deleteDir($GLOBALS['WP']['view']['config']['template_tpl']);
			$this->checkdir('./template');
			$this->checkdir('config.php');
			message("缓存清理成功",'?action=system&o=sys_ecache');
		}
		$this->display("system.html");
	}
	private function thisnumjian($d){
	    $d=str_ireplace(array('[-|2|-]','[-|3|-]','[-|4|-]','[-|5|-]','[-|6|-]'),array('[-|1|-]','[-|2|-]','[-|3|-]','[-|4|-]','[-|5|-]'),$d);
	    return $d;
	}
	private function rewrite_for($d,$r){
	    $num=1;
	    foreach($d[1] as $k=>$v){
	        if(stripos(',,type,article,product,recruitment,message,,',','.$v.',')){
	            if(stripos(',,article,product,recruitment,message,,',','.$v.',')){
	                $u.='&action='.$v;
	            }else{
	                $u.='&o='.$v;
	            }
	            $r=str_ireplace(array('{'.$v.'}'),$v,$r);
	        }else{
	            $num++;
	            $u.='&'.$v.'=[-|'.$num.'|-]';
	            if(stripos(',,id,nid,sid,page,,',','.$v.',')){
	                $r=str_ireplace($d[0][$k],'([0-9]+)',$r);
	            }else{
	                $r=str_ireplace($d[0][$k],'(\w+)',$r);
	            }
	        }
	    }
	    return array($r,ltrim($u,'&'),$num+1);
	}
	private function checkdir($basedir){
		if (is_file($basedir)) {
			$this->checkBOM($basedir);
		}else{
			if ($dh = opendir($basedir)) {
				while (($file = readdir($dh)) !== false) {
					if ($file != '.' && $file != '..'){
						if (!is_dir($basedir."/".$file)) {
							$this->checkBOM("$basedir/$file");
						}else{
							$dirname = $basedir.'/'.$file;
							$this->checkdir($dirname);
						}
					}
				}
				closedir($dh);
			}
		}
	}
	private function checkBOM ($filename) {
		$contents = file_get_contents($filename);
		$charset[1] = substr($contents, 0, 1);
		$charset[2] = substr($contents, 1, 1);
		$charset[3] = substr($contents, 2, 1);
		if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
			$rest = substr($contents, 3);
			$this->rewr ($filename, $rest);
		}
	}
	private function rewr ($filename, $data) {
		$filenum = fopen($filename, "w");
		flock($filenum, LOCK_EX);
		fwrite($filenum, $data);
		fclose($filenum);
	}
	function writeInclude($include){
	    $incfile='system/include.php';
	    $inc_tp=@fopen($incfile,"r");
	    $inc_txt=@fread($inc_tp,filesize($incfile));
	    @fclose($inc_tp);
	    foreach($include as $v){
            $vt=strtolower(trim($this->syArgs($v,1),'/'));
            if(preg_match("/^[a-zA-Z0-9_\{\}\.\-\/]*$/",$vt)==0)message("伪静态规则只能包含英文、数字、下划线、点、中划线、{、}、/",null,1,0);
            if($v=='rewrite_article'){
                if((stripos($vt,'{id}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{article}')===false){
                    message("article规则中{id}与{file}必须至少包含一个，并且必须包含{article}",null,1,0);
                }
            }
            if($v=='rewrite_article_type'){
                if((stripos($vt,'{nid}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{page}')===false||stripos($vt,'{article}')===false||stripos($vt,'{type}')===false){
                    message("article栏目规则中{nid}与{file}必须至少包含一个，并且必须包含{page}与{article}与{type}",null,1,0);
                }
            }
            if($v=='rewrite_product'){
                if((stripos($vt,'{id}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{product}')===false){
                    message("product规则中{id}与{file}必须至少包含一个，并且必须包含{product}",null,1,0);
                }
            }
            if($v=='rewrite_product_type'){
                if((stripos($vt,'{nid}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{page}')===false||stripos($vt,'{product}')===false||stripos($vt,'{type}')===false){
                    message("product栏目规则中{nid}与{file}必须至少包含一个，并且必须包含{page}与{product}与{type}",null,1,0);
                }
            }
            if($v=='rewrite_recruitment'){
                if((stripos($vt,'{id}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{recruitment}')===false){
                    message("recruitment规则中{id}与{file}必须至少包含一个，并且必须包含{type}与{recruitment}",null,1,0);
                }
            }
            if($v=='rewrite_recruitment_type'){
                if((stripos($vt,'{nid}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{page}')===false||stripos($vt,'{recruitment}')===false||stripos($vt,'{type}')===false){
                    message("recruitment栏目规则中{nid}与{file}必须至少包含一个，并且必须包含{page}与{recruitment}与{type}",null,1,0);
                }
            }
            if($v=='rewrite_message_type'){
                if((stripos($vt,'{nid}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{page}')===false||stripos($vt,'{message}')===false||stripos($vt,'{type}')===false){
                    message("message规则中{nid}与{file}必须至少包含一个，并且必须包含{page}与{message}与{type}",null,1,0);
                }
            }
            if($v=='rewrite_open'){
                $inc_txt=preg_replace("/'".$v."' => .*?,/","'".$v."' => ".$vt.",",$inc_txt);
            }else{
                $inc_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".$vt."',",$inc_txt);
            }
            if($v=='rewrite_dir'){
                if($vt==''){$vt='/';}else{$vt='/'.$vt.'/';}
                $inc_txt=preg_replace("/'".$v."' => .*?,/","'".$v."' => '".$vt."',",$inc_txt);
            }
            if($v=='vercode'){
                $inc_txt=preg_replace("/'vercode' => .*?,/","'vercode' => ".$this->syArgs('vercode',1).",",$inc_txt);
            }
            if($v=='mode'){
	           $inc_txt=preg_replace("/'mode' => '.*?',/","'mode' => '".$this->syArgs('mode',1)."',",$inc_txt);
            }
	    }
		$inc_tpl=@fopen($incfile,"w");
		@fwrite($inc_tpl,$inc_txt);
		@fclose($inc_tpl);
	}
	function writeConfig($config){
		$configfile='config.php';
		$fp_tp=@fopen($configfile,"r");
		$fp_txt=@fread($fp_tp,filesize($configfile));
		@fclose($fp_tp);
		foreach($config as $v){
		    if (strpos(',site_statichtml,cache_auto,cache_time,filesize,imgwater,imgwater_type,thumbnail,img_w,img_h,comment_audit,comment_user,site_statichtml_index,enable_gzip,enable_gzip_level,',$v)){
		        $txt=$this->syArgs($v);if($v=='filesize'){$txt=$txt*1024;}
		        if($v=='site_statichtml'){
		            if($GLOBALS['WP']['ext']['site_statichtml']==1&&$txt==0)@unlink('index.html');
		        }
		        $fp_txt=preg_replace("/'".$v."' => .*?,/","'".$v."' => ".$txt.",",$fp_txt);
		    }else if($v=='filetype'){
		        $fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".strtolower($this->syArgs($v,1))."',",$fp_txt);
		    }else if($v=='site_statichtml_rules'){
		        if(stripos($this->syArgs($v,1),'[id]')===false&&stripos($this->syArgs($v,1),'[file]')===false){
		            message("静态规则中[id]与[file]必须至少包含一个",null,1,0);
		        }
		        if(stripos($this->syArgs($v,1),'.')===false){
		            message("静态规则中必须包含“.”和后缀名",null,1,0);
		        }
		        $fp_txt=preg_replace("/'site_statichtml_rules' => '.*?',/","'site_statichtml_rules' => '".$this->syArgs($v,1)."',",$fp_txt);
		    }else if($v=='site_statichtml_dir'){
		        if(preg_match("/^[a-zA-Z0-9_\/]*$/",$this->syArgs($v,1))==0)message("生成目录只能为英文、数字、下划线和/组成",null,1,0);
		        if($this->syArgs($v,1)=='/'){
		            $dir=$this->syArgs($v,1);
		        }else if($this->syArgs($v,1)==''){
		            $dir='html';
		        }else{
		            $dir=trim($this->syArgs($v,1),'/');
		        }
		        $fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".strtolower($dir)."',",$fp_txt);
		        	
		    }else if($v=='site_statichtml_rules'){
		        if(preg_match("/^[a-zA-Z0-9_\.\[\]\/]*$/",$this->syArgs($v,1))==0)message("生成规则只能为英文、数字、下划线、点和/组成",null,1,0);
		        if($this->syArgs($v,1)==''){
		            $dir='[y]_[m]_[id].html';
		        }else{
		            $dir=trim($this->syArgs($v,1),'/');
		        }
		        $fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".strtolower(trim($this->syArgs($v,1),'/'))."',",$fp_txt);
		    }else{
		        $fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".str_replace(array("\r\n","\n","\r"),'',$this->syArgs($v,1))."',",$fp_txt);
		    }
		}
		$fpt_tpl=@fopen($configfile,"w");
		@fwrite($fpt_tpl,$fp_txt);
		@fclose($fpt_tpl);
	}
}