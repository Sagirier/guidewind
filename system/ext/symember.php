<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class symember{
	private $member;
	public function __construct(){
		if(!$_SESSION['auser'] && $_SESSION['member']){
			$member = syDB('member')->find(array('uid'=>$_SESSION['member']['uid']),null,'`uid`,`gid`,`adminid`,`username`,`money`,`credit`,`portrait`,`regtime`,`lastlogtime`,`sexuality`,`nickname`');
			$member_contact=syDB("member_contact")->find(array('uid'=>$_SESSION['member']['uid']));
			$member=array_merge($member,$member_contact);
			$member['group']=syDB('member_group')->find(array('gid'=>$member['gid']));
		}else{
			$member['uid'] = 0;
			$member['group']=syDB('member_group')->find(array('weight'=>0));
		}
		$this->member=$member;
	}
	public function islogin($login=1,$url=0){
		if($login&&$this->member['uid']==0){
			GLOBAL $__controller, $__action;
			if($__action!='login'){
				if($url==1)$url=$this->backurl();
				jump($GLOBALS['WWW'].'index.php?action=member&o=login&url='.$url);
			}
		}
		return $this->member;
	}
	public function p_v(){
	   $this->islogin(1,1);
	}
	public function p_r($msubmit){
		if($this->member['group']['submit']==0||$msubmit==0||membergroup($msubmit,'weight')>$this->member['group']['weight'])message("本栏目无权发布");
	}
	public function backurl(){
		$c=is_escape($_GET['c']);
		$id=is_escape($_GET['id']);
		if($c=='pay'&&$id>0){
			$url=$_SERVER["HTTP_REFERER"];
		}else{
			$url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}
		return urlencode($url);
	}

}