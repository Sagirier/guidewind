<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class index extends syController{
	function __construct(){
		parent::__construct();
		$this->authority0=syDB('admin_authority')->findAll(array('up'=>0,'no'=>1),' `order` desc,`authid` ');
		$this->authority1=syDB('admin_authority')->findAll(array('up'=>0,'no'=>0),' `order` desc,`authid` ');
		$this->db=$GLOBALS['WP']['db']['prefix'];
		//$month_condition=' `addtime` > '.(time()-30*24*3600); //一个月内的时间戳
		$this->total_article=$this->total('article'); //文章总数
		$this->emonth_article=$this->get_month_total('article'); //每个月的文章总数
		//$this->month_article=$this->total('article',$month_condition); //最近一月文章总数
		$this->audit_article=$this->total('article',array('statu'=>1)); //审核文章总数
		
		$this->total_product=$this->total('product'); //商品总数
		$this->emonth_product=$this->get_month_total('product'); //每个月的商品总数
		//$this->month_product=$this->total('product',$month_condition); //最近一月商品总数
		$this->audit_product=$this->total('product',array('statu'=>1)); //审核商品总数
		
		$this->total_message=$this->total('message'); //留言总数
		$this->audit_message=$this->total('message',array('statu'=>1)); //审核留言总数
		
		$this->total_recruitment=$this->total('recruitment'); //招聘信息总数
		$this->audit_recruitment=$this->total('recruitment',array('statu'=>1)); //审核招聘信息总数
		
		$num=0;$audit="";
		if($this->audit_recruitment!=$this->total_recruitment || $this->audit_message!=$this->total_message || $this->audit_product!=$this->total_product || $this->audit_article!=$this->total_article){
			$num+=1;
		}
		if($num>0){
			$this->audit=true;
		}else {
			$this->audit=false;
		}
	}
	function index(){
		$this->display("index.html");
	}
	function total($table,$condition=null){
		return syDB($table)->findCount($condition);
	}
	function get_month_total($table){
		$year=date("Y");
		$month=array(  //12个月份的时间戳
				1=>strtotime($year."-1-1 0:0:0"),
				2=>strtotime($year."-2-1 0:0:0"),
				3=>strtotime($year."-3-1 0:0:0"),
				4=>strtotime($year."-4-1 0:0:0"),
				5=>strtotime($year."-5-1 0:0:0"),
				6=>strtotime($year."-6-1 0:0:0"),
				7=>strtotime($year."-7-1 0:0:0"),
				8=>strtotime($year."-8-1 0:0:0"),
				9=>strtotime($year."-9-1 0:0:0"),
				10=>strtotime($year."-10-1 0:0:0"),
				11=>strtotime($year."-11-1 0:0:0"),
				12=>strtotime($year."-12-1 0:0:0"),
				13=>(strtotime($year."-12-31 23:59:59")+1)
		);
		$month_total=array(
				1=>$this->total($table," `addtime` BETWEEN ".$month[1]." and ".$month[2]),
				2=>$this->total($table," `addtime` BETWEEN ".$month[2]." and ".$month[3]),
				3=>$this->total($table," `addtime` BETWEEN ".$month[3]." and ".$month[4]),
				4=>$this->total($table," `addtime` BETWEEN ".$month[4]." and ".$month[5]),
				5=>$this->total($table," `addtime` BETWEEN ".$month[5]." and ".$month[6]),
				6=>$this->total($table," `addtime` BETWEEN ".$month[6]." and ".$month[7]),
				7=>$this->total($table," `addtime` BETWEEN ".$month[7]." and ".$month[8]),
				8=>$this->total($table," `addtime` BETWEEN ".$month[8]." and ".$month[9]),
				9=>$this->total($table," `addtime` BETWEEN ".$month[9]." and ".$month[10]),
				10=>$this->total($table," `addtime` BETWEEN ".$month[10]." and ".$month[11]),
				11=>$this->total($table," `addtime` BETWEEN ".$month[11]." and ".$month[12]),
				12=>$this->total($table," `addtime` BETWEEN ".$month[12]." and ".$month[13])
		);
		return $month_total;
	}
	function template_cache(){
		$d='system/cache/log/';
		$f=date('Ym').'.txt';
		deleteDir($d);__mkdirs($d);
		$wt=@fopen($d.$f,"w");@fclose($wt);
		exit('true');
	}
	function href_session(){
		exit('true,'.date('Ym'));
	}
}	