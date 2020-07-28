<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class traits extends syController{
	public $pk = "tid";
	public $table = "traits";
	function __construct(){
		parent::__construct();
		$this->Class=syClass("c_traits");
		$this->lists=$this->Class->findAll(null," `order` DESC,`tid` ");
		$this->c=$this->syArgs('c',1);
		$this->tid=$this->syArgs('tid');
	}
	function index(){
		$this->display("traits.html");
	}
	function add(){
		if(!$this->userClass->checkgo('traits_add')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$newtraitname=$this->syArgs('trait_name',1);
		$newtraiticon=$this->syArgs('trait_icon',1);
		$traitcmark=$this->syArgs('cmark',1);
		if($this->Class->find(array('tname'=>$newtraitname))){
			$ret['result_code']=101;
			$ret['result_des']="标签/属性名重复，请重新添加";
		}elseif($newtraitname=='' || $newtraiticon=='') {
			$ret['result_code']=102;
			$ret['result_des']="标签/属性名或者图标文件名不能为空";
		}else{
			$traitid=$this->Class->create(array('cmark'=>$traitcmark,'tname'=>$newtraitname,'icon'=>$newtraiticon)); //添加成功，返回json数据
			$ret=array(
				'tid'=>$traitid,
				'tname'=>$newtraitname,
				'icon'=>$newtraiticon
			);
		}
		echo json_encode($ret);
		exit();
	}
	function edit() {
		if(!$this->userClass->checkgo('traits_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$traitname=$this->syArgs('trait_name',1);
		if($this->Class->find(array('tname'=>$traitname))){
			$ret['result_code']=101;
			$ret['result_des']="标签/属性名重复或未变更";
		}elseif($traitname==""){
			$ret['result_code']=102;
			$ret['result_des']="标签/属性名不能为空";
		}else{
			$this->Class->update(array('tid'=>$this->tid),array('tname'=>$traitname));
		}
		echo json_encode($ret);
		exit();
	}
	function del(){
		if(!$this->userClass->checkgo('traits_del')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->Class->delete(array('tid'=>$this->tid));
	}
}