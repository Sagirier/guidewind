<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class comment extends syController
{
	function __construct(){
		parent::__construct();
	}
	function index(){
		if(syExt('comment_user')==1&&empty($_SESSION['member']))message("您必须登录后才能评论",null,3,0);
		if($this->syArgs('detail',1)==''||!$this->syArgs('aid'))message("您输入的内容为空，请重新输入",null,3,0);
// 		if($GLOBALS['WP']['vercode']==1){
// 		if(md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
// 		}
		if(syExt('comment_audit')==1){$statu=1;}else{$statu=0;}
		if(empty($_SESSION['member'])){$uid=0;}else{$uid=$_SESSION['member']['uid'];}
		$newrow = array(
			'cmark' => $this->syArgs('cmark',1),
			'aid' => $this->syArgs('aid'),
			'detail' => $this->syArgs('detail',1),
			'addtime' => time(),
			'statu' => $statu,
			'uid'=>$uid,
			'ip' => GetIP()
		);
		$newVerifier=syClass('c_comment')->syVerifier($newrow);
		if(false == $newVerifier){
			if(syClass('c_comment')->create($newrow)){
				if(syExt('comment_audit')==1){
					message("评论成功",$_SERVER['HTTP_REFERER']);
				}else {
					message("评论成功,请耐心等待管理员审核",$_SERVER['HTTP_REFERER']);
				}
			}else{message("评论失败，请重新提交");}
		}else{message_err($newVerifier);}
	}
}	