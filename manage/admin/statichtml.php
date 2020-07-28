<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class statichtml extends syController
{
	function __construct(){	
		parent::__construct();
		set_time_limit(99999999);
		if(syExt('site_statichtml')!=1)message("系统静态html已<font color='red'>关闭</font><br/>请先在<font color='green'>系统设置</font>—<font color='green'>静态html设置</font>—<font color='green'>开启静态html</font>","?action=system&o=statichtml",7,0);	
		$this->a="system";
		$this->title="更新静态html";
		$this->synavigators=syClass('synavigators');
		$this->navigators=$this->synavigators->type_txt();
		$this->chtml=syClass('syhtml');
		$this->site_statichtml_dir=syExt("site_statichtml_dir");
		$this->site_statichtml_navrules=syExt("site_statichtml_navrules");
		$this->site_statichtml_contentrules=syExt("site_statichtml_contentrules");
		$this->site_statichtml_rules=syExt("site_statichtml_rules");
		$this->site_statichtml_suffix=syExt("site_statichtml_suffix");
	}
	function index(){
		$this->display("statichtml.html");
	}
	function htmllabel(){
		$this->channel=syDB('channels')->findAll(array('statu'=>1,'sys'=>0));
	    $this->display("html.html");
	}
	function clear(){
		ob_implicit_flush(1);
		ob_end_flush();
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link href="manage/admin/template/style/admin.css" rel="stylesheet" type="text/css" /><script src="system/js/jsmain.js" type="text/javascript"></script><script type="text/javascript">function goclear(ctxt){$("#clear").html(ctxt);}</script><div class="main"><div class="progress" id="clear">正在统计需更新数量...</div></div>';
		deleteDir($GLOBALS['WP']['sp_cache']);
		deleteDir($GLOBALS['WP']['view']['config']['template_tpl']);
		$ww='';$t=$this->syArgs('t',1);
		if($t==1||$t==0){  //更新文章
			if($t==0){
				$m=array('article','product');
				$channel=syDB('channels')->findAll(array('statu'=>1,'sys'=>0));
				foreach($channel as $cc){
					$m=array_merge($m,array($cc['cmark']));
				}
			}else{
			    $m=array($this->syArgs('cmark',1));
			}
			foreach($m as $mv){
				$this->t=channelsinfo($mv,'cname');;
				if($this->syArgs('nid',1)!='')$ww.=' and a.nid in ('.$this->synavigators->leafid($this->syArgs('nid')).')';
				if($this->syArgs('idmin',1)!=''&&$this->syArgs('idmax',1)!='')$ww.=' and a.id >'.$this->syArgs('idmin').' and  a.id < '.$this->syArgs('idmax').'';
				if($mv=='article'||$mv=='product'){
					$sql='select a.id,a.nid,a.addtime,a.htmlfile,b.detail from '.$GLOBALS["WP"]["db"]["prefix"].$mv.' a left join '.$GLOBALS["WP"]["db"]["prefix"].$mv.'_field b on (a.id=b.aid) where a.statu=1'.$ww;
				}else{
					$sql='select a.id,a.nid,a.addtime,a.htmlfile from '.$GLOBALS["WP"]["db"]["prefix"].$mv.' a left join '.$GLOBALS["WP"]["db"]["prefix"].$mv.'_field b on (a.id=b.aid) where a.statu=1'.$ww.' and a.mrank=0 and a.mgold=0';
				}
				$numall=syDB($mv)->findSql('select count(`id`) as ct from '.$GLOBALS["WP"]["db"]["prefix"].$mv.' where `statu`=1'.$ww);
				$i=0;$ii=1;$all=ceil($numall[0]['ct']/20);
				while($ii<= $all){
					$tosql=$sql.' limit '.$i.',20';$a='';
					$a=syDB($mv)->findSql($tosql);
					$this->chtml_content($mv,$a,$numall[0]['ct'],$i);
					$i=$i+20;
					$ii++;
				}
				$this->chtml_echo('['.$this->t.']更新完成');
			}
		}
		if($t==2||$t==0){
			$this->t='栏目';
			if($this->syArgs('nid',1)!='')$ww=' and `nid` in('.$this->synavigators->leafid($this->syArgs('nid')).') ';
			$a=syDB('navigators')->findAll(' `mrank`=0'.$ww,null,'`nid`,`htmldir`,`htmlfile`,`cmark`,`listno`,`mrank`');
			$this->chtml_navigators($a);
		}
// 		if($t==3||$t==0){
// 			$this->t='专题';
// 			if($this->syArgs('sid',1)!='')$ww=' sid='.$this->syArgs('sid').' ';
// 			$a=syDB('special')->findAll($ww,null,'sid,htmldir,htmlfile,molds,listnum');
// 			$this->chtml_special($a);
// 		}
// 		if($t==4||$t==0){
// 			$this->t='自定义页面';
// 			$a=syDB('custom')->findAll();
// 			$this->chtml_labelcus_custom($a);
// 		}
		if($t==99||$t==0){
			$this->t='首页';
			$this->chtml_index();
		}
		set_time_limit(30);
		message('['.$this->t.']静态html更新全部完成',null,0);
	}
	private function chtml_content($cmark,$a,$anum,$isnum) {
		foreach($a as $v){
		    $this->chtml_echo('正在更新：'.$this->t.'<br>总共需更新：<span>'.$anum.'</span><br>当前更新：<span>'.$isnum.'</span><br>总进度：<span>'.floor($isnum/$anum*100).'%</span>');
			$c_html_f=html_rules($cmark,$v['nid'],$v['addtime'],$v['id'],$v['htmlfile']);
			syDB($cmark)->updateField(array('id'=>$v['id']),'htmlurl',$c_html_f);
			$ms=syDB('channels')->find(array('cmark'=>$cmark),null,'sys');
			if($ms['sys']!=1){
				$this->chtml->c_content(array('id'=>$v['id'],'page'=>1,'cmark'=>$cmark),$c_html_f);
			}else{
				$this->chtml->c_content($cmark,array('id'=>$v['id']),$c_html_f);
				$detail=array_filter(explode("[guide|page]",$v['detail']));
				$allb=count($detail);
				if($allb>1){
					for ($i = 1; $i <= $allb; $i++) {
						if($i>1){
						$this->chtml->c_content($cmark,array('id'=>$v['id'],'page'=>$i),str_replace('.',$i.'.',$c_html_f));
						}
					}
				}
			}
			
			$isnum++;
		}
	}
	private function chtml_navigators($a) {
		$anum=count($a);
		$isnum=1;
		foreach($a as $v){
			$this->chtml_echo('正在更新：'.$this->t.'<br>总共需更新：<span>'.$anum.'</span><br>当前更新：<span>'.$isnum.'</span><br>总进度：<span>'.floor($isnum/$anum*100).'%</span>');
			if($v['htmldir']==''){
				$c_html_f='/navgator/'.$v['nid'].'/index/';
			}else{
				$c_html_f=$v['htmldir'].'/';
			}
			if($v['htmlfile']==''){
				$c_html_f.='index'.syExt('site_statichtml_suffix');
			}else{
				$c_html_f.=$v['htmlfile'].syExt('site_statichtml_suffix');
			}
			$ms=syDB('channels')->find(array('cmark'=>$v['cmark']),null,'sys');
			if($ms['sys']==1){
				$this->chtml->c_classtype($v['cmark'],array('nid'=>$v['nid']),$c_html_f);
			}else{
				$this->chtml->c_classtype('channel',array('nid'=>$v['nid']),$c_html_f);
			}
			$cl=syClass('c_'.$v['cmark']);
			$total_count=total_count($GLOBALS['WP']['db']['prefix'].$v['cmark'].' where `statu`=1 and `nid` in('.$this->synavigators->leafid($v['nid']).')');
			$alls=$cl->syPager(1,$v['listno'],$total_count)->findAll(' `statu`=1 and `nid` in('.$this->synavigators->leafid($v['nid']).') ',null,'`nid`,`statu`');
			$pages=$cl->syPager()->getPager();
			if($pages['total_page']>1){
				for ($i = 2; $i <= $pages['total_page']; $i++) {
					if($ms['sys']==1){
						$this->chtml->c_classtype($v['cmark'],array('nid'=>$v['nid'],'page'=>$i),str_replace('.',$i.'.',$c_html_f));
					}else{
						$this->chtml->c_classtype('cmark',array('nid'=>$v['nid'],'page'=>$i),str_replace('.',$i.'.',$c_html_f));
					}
				}	
			}		
			$isnum++;
		}
		$this->chtml_echo('['.$this->t.']更新完成');
	}
// 	private function chtml_labelcus_custom($a) {
// 		$anum=count($a);
// 		$isnum=1;
// 		foreach($a as $v){
// 			$this->chtml_echo('正在更新：'.$this->t.'<br>总共需更新：<span>'.$anum.'</span><br>当前更新：<span>'.$isnum.'</span><br>总进度：<span>'.floor($isnum/$anum*100).'%</span>');
// 			if($v['dir']==''){
// 				$c_html_f=$this->html_dir.'/';
// 			}else{
// 				$c_html_f=$v['dir'].'/';
// 			}
// 			$c_html_f.=$v['file'];
// 			$this->chtml->c_labelcus_custom(array('file'=>$v['file']),$c_html_f);
// 		}
// 		$this->chtml_echo('['.$this->t.']更新完成');
// 	}
	private function chtml_index() {
	    $this->chtml->c_index();
	    $this->chtml_echo('[首页]更新完成');
	}
	private function chtml_echo($msg) {
	    echo '<script type="text/javascript">goclear("'.$msg.'");</script>';
	}
}	