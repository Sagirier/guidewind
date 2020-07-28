<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class login extends syController{	
	function __construct(){
		parent::__construct();
	}
	function index(){
	    if($_SESSION['auser']){
	        jump('?');
	    }
		$this->display("login.html");
	}
	function go(){
			$conditions = array('auser' => $this->syArgs("adminuser",1),'apass' => md5(md5($this->syArgs("adminpass",1)).$this->syArgs("adminuser",1)));
			$acookie=$this->syArgs("admincookie",1);
			//验证数据库
			$r = syDB('admin')->find($conditions);
			if(!$r){
				message("出现以下情况导致登陆失败：<br/>1.您的用户名或密码输入错误;<br/>2.您的管理员权限已到期，请联系超级管理员续期",null,8,2);
			}else{
				$_SESSION['auser'] = array(
					'auser' => $r['auser'],
					'auid' => $r['auid'],
				    'uid' => $r['uid'],
					'level' => $r['level'],
					'gid' => $r['gid'],
					'authority' => $r['authority'],
				);
				if($acookie){
					setcookie('auser',$r['auser'],time()+24*3600);
				}
				//记录最后一次登录时间
				syDB('admin')->update(array('auid'=>$_SESSION['auser']['auid']),array('logtime'=>time()));
				jump("?");
			}
	}
	function out(){
		//记录并更新登录时长
		$duration=time()-$this->user['logtime'];
		syDB('admin')->update(array('auid'=>$_SESSION['auser']['auid']),array('duration'=>$duration,'lastlogtime'=>$this->user['logtime']));
		$_SESSION['auser'] = array();
		if (isset($_COOKIE[session_name()])) {setcookie(session_name(), '', time()-42000, '/');}
		session_destroy();
		jump("?action=login");
	}
}	