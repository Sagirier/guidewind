<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class functions extends syController{
	public $pk = "fid";
	public $table = "functions";
	function __construct(){
		parent::__construct();	
		$this->a='functions';	
		$this->o=$this->syArgs('o',1);
		$this->Class=syClass("c_functions");
	}
	function member_sys(){
		if(!$this->userClass->checkgo('member_sys')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="会员系统";
		$member_sys_info=$this->Class->find(array('fmark'=>'member_sys'));
		$this->member_sys=json_decode($member_sys_info['fvalue'],true);
		if($this->syArgs('go')==1){
			$member_sys_setting=array('statu'=>$this->syArgs('member_sys_switch'),'tecent'=>$this->syArgs('member_sys_tencent'));
			if($this->Class->update(array('fmark'=>'member_sys'),array('fvalue'=>json_encode($member_sys_setting)))){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("修改成功");
			}else {
				message("修改失败");
			}
		}
		$this->basic=$GLOBALS['WP']['ext'];
		//$s=syDB('sysconfig')->findAll();
		//foreach($s as $v){$sysconfig[$v['name']]=$v['sets'];}
		$this->sysconfig=$sysconfig;
		$this->display("functions.html");
	}
	function transaction(){
		if(!$this->userClass->checkgo('transaction')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="购物系统";
		$transaction_info=$this->Class->find(array('fmark'=>'transaction'));
		$this->transaction=json_decode($transaction_info['fvalue'],true);
		if($this->syArgs('go')==1){
			$transaction_setting=array('statu'=>$this->syArgs('transaction_switch'));
			if($this->Class->update(array('fmark'=>'transaction'),array('fvalue'=>json_encode($transaction_setting)))){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("修改成功");
			}else {
				message("修改失败");
			}
		}
		$this->display("functions.html");
	}
	function payment() {
		if(!$this->userClass->checkgo('payment')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="支付系统";
		$payment_info=$this->Class->find(array('fmark'=>'payment'));
		$this->payment=json_decode($payment_info['fvalue'],true);
		if($this->syArgs('go')==1){
			$payment_setting=array('statu'=>$this->syArgs('payment_switch'));
			if($this->Class->update(array('fmark'=>'payment'),array('fvalue'=>json_encode($payment_setting)))){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("修改成功");
			}else {
				message("修改失败");
			}
		}
		$this->display("functions.html");
	}
	function ads_sys() {
		if(!$this->userClass->checkgo('ads_sys')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="广告投放";
		$ads_sys_info=$this->Class->find(array('fmark'=>'ads_sys'));
		$this->ads_sys=json_decode($ads_sys_info['fvalue'],true);
		if($this->syArgs('go')==1){
			$ads_sys_setting=array(
				'statu'=>$this->syArgs('ads_sys_switch')
			);
			if($this->syArgs('ads_sys_filetype',1)!=''){
				foreach (explode(",", $this->syArgs('ads_sys_filetype',1)) as $t){
					if(!in_array($t, explode(",",$GLOBALS['WP']['ext']['filetype']))){
						message("出现错误，可能原因为：<br/>1.您设置的文件类型不包含在系统默认文件内；<br/>2.您输入的文件类型有误，请重新输入",null,3,2);
					}
				}
				$ads_sys_setting=array_merge($ads_sys_setting,array('filetype'=>$this->syArgs('ads_sys_filetype',1)));
			}else{
				$ads_sys_setting=array_merge($ads_sys_setting,array('filetype'=>'jpg,png,bmp,jpeg,gif,flv,swf'));
			}
			if($this->syArgs('ads_sys_filemaxsize')*1024>$GLOBALS['WP']['ext']['filesize']){
				message("出现错误，您的设置值超过了系统默认上传文件大小",null,3,2);
			}
			if($this->syArgs('ads_sys_filemaxsize')=='' || $this->syArgs('ads_sys_filemaxsize')==0){
				$ads_sys_setting=array_merge($ads_sys_setting,array('filemaxsize'=>$GLOBALS['WP']['ext']['filesize']));
			}else{
				$ads_sys_setting=array_merge($ads_sys_setting,array('filemaxsize'=>$this->syArgs('ads_sys_filemaxsize')*1024));
			}
			if($this->Class->update(array('fmark'=>'ads_sys'),array('fvalue'=>json_encode($ads_sys_setting)))){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("修改成功");
			}else {
				message("修改失败");
			}
		}
		$this->display("functions.html");
	}
	function links_sys() {
		if(!$this->userClass->checkgo('comment_sys')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="友情链接";
		$links_sys_info=$this->Class->find(array('fmark'=>'links_sys'));
		$this->links_sys=json_decode($links_sys_info['fvalue'],true);
		if($this->syArgs('go')==1){
			$links_sys_setting=array(
					'statu'=>$this->syArgs('links_sys_switch')
			);
			if($this->Class->update(array('fmark'=>'links_sys'),array('fvalue'=>json_encode($links_sys_setting)))){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("修改成功");
			}else {
				message("修改失败");
			}
		}
		$this->display("functions.html");
	}
	function comment_sys() {
		if(!$this->userClass->checkgo('comment_sys')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->title="评论系统";
		$comment_sys_info=$this->Class->find(array('fmark'=>'comment_sys'));
		$this->comment_sys=json_decode($comment_sys_info['fvalue'],true);
		if($this->syArgs('go')==1){
			$comment_sys_setting=array(
					'statu'=>$this->syArgs('comment_sys_switch')
			);
			$configfile='config.php';
			$fp_tp=@fopen($configfile,"r");
			$fp_txt=@fread($fp_tp,filesize($configfile));
			@fclose($fp_tp);
			$basic_config=array('comment_audit','comment_user');
			foreach($basic_config as $v){
				if(strpos(',site_html,cache_auto,cache_time,filesize,imgwater,imgwater_t,imgcaling,img_w,img_h,comment_audit,comment_user,site_html_index,enable_gzip,enable_gzip_level,',$v)){
					$txt=$this->syArgs($v);
					$fp_txt=preg_replace("/'".$v."' => .*?,/","'".$v."' => ".$txt.",",$fp_txt);
				}else{
					$fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".str_replace(array("\r\n","\n","\r"),'',$this->syArgs($v,1))."',",$fp_txt);
				}
			}
			$fpt_tpl=@fopen($configfile,"w");
			@fwrite($fpt_tpl,$fp_txt);
			@fclose($fpt_tpl);
			if($this->Class->update(array('fmark'=>'comment_sys'),array('fvalue'=>json_encode($comment_sys_setting)))){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("修改成功");
			}else {
				message("修改失败");
			}
		}
		$this->display("functions.html");
	}
	function add(){
		message("该功能正在开发中，暂无法使用");
	}
}