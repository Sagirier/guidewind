<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class website extends syController{
	function __construct(){
		parent::__construct();	
		$this->a='website';	
	}
	function basic(){
		if(!$this->userClass->checkgo('website_basic')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		if($_REQUEST['submit']==1){
            $basic_config=array('http_path','site_title','site_keywords','site_description','cache_auto');
            $include_config=array('mode');
            $this->writeConfig($basic_config);
            $this->writeInclude($include_config);
            message("修改成功");
		}
		$this->basic=$GLOBALS['WP']['ext'];
		$this->include = array(
		    'mode' => $GLOBALS['WP']['mode']
        );
		//$s=syDB('sysconfig')->findAll();
		//foreach($s as $v){$sysconfig[$v['name']]=$v['sets'];}
//		$this->sysconfig=$sysconfig;
		$this->display("website_basic.html");
	}
	function template(){
		if(!$this->userClass->checkgo('website_template')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->type=$this->syArgs('type');
		if(!$this->type){
			$lists=array();
			$i=0;
			if($dp=@opendir($this->indir)){
				while(false!==($file = readdir($dp))) {
					if($file!='.' && $file!='..' && is_dir($this->indir.$file)) {
						$lists[$i]['dir']=$file;
						if(file_exists($this->indir.$file.'/thumb.jpg'))$lists[$i]['thumb']=1;
						if(file_exists($this->indir.$file.'/sql.txt'))$lists[$i]['sql']=1;
						$i=$i+1;
					}
				}
				@closedir($dp);
			}
			$this->lists=$lists;
		}
		$this->display("template.html");
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