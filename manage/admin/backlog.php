<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class backlog extends syController{
	public $pk = "id";
	public $pkn = "nid";
	public $table = "backlog";
	function __construct(){
		parent::__construct();
		$this->a="contents";
		if(!$this->userClass->checkgo('backlog')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->Class=syClass("c_backlog");
		$this->conditions=array(
			'cmark'=>$this->syArgs('backlog-cmark',1),
			'title'=>$this->syArgs('title',1)
		);
		$this->sqldb=$GLOBALS['WP']['db']['prefix'];
		$this->lists=syClass("sybacklog")->get_unaudit_lists($this->conditions);
		$this->id=$this->syArgs('id');
		$this->nid=$this->syArgs('nid');
	}
	function index(){
		$this->display("backlog.html");
	}
	function view(){
		if(!$this->Class->find(array('id'=>$this->id,'nid'=>$this->nid))){
			$this->Class->create(array('id'=>$this->id,'nid'=>$this->nid));
			deleteDir($GLOBALS['WP']['sp_cache']);
		}
	}
	function audit() {
		$remessage=syDB("message")->find(array('id'=>$this->id,'nid'=>$this->nid)); //验证是否为在线招聘留言信息
		if($remessage && $remessage['cmark']!=''){
			syDB("message")->update(array('id'=>$this->id),array('statu'=>1));
		}else{
			syDB(navinfo($this->nid, 'cmark'))->update(array('id'=>$this->id),array('statu'=>1));
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
		echo "审核成功";
		exit();
	}
	function alledit(){
		$formnum=$this->syArgs('formnum');
		$types_arr=$this->syArgs('types',2);
		$this->operate_allBacklog($formnum,$types_arr);
	}
	function operate_allBacklog($formnum,$types_arr){
		switch ($formnum){
			case 1:  //批量设为已读
				if(!$types_arr){
					message('您尚未选择任何内容...','?action=backlog',0,0);
				}
				$viewSql="INSERT INTO ".$this->sqldb.$this->table." (`id` , `nid`) VALUES ";
				for ($i=0;$i<count($types_arr);$i++){
					$f=explode("|", $types_arr[$i]);
					if(!$this->Class->find(array('id'=>$f[0],'nid'=>$f[1]))){
						$insert_arr[]="(".str_replace("|", ",", $types_arr[$i]).")";
					}
				}
				$viewSql.=join(",", $insert_arr);
				if($this->Class->runSql($viewSql)){
					message("批量设为已读成功","?action=backlog");
				}
				break;
			case 2:
				if(!$types_arr){
					message('您尚未选择任何内容...','?action=backlog',0,0);
				}
				for ($j=0;$j<count($types_arr);$j++){
					$f=explode("|", $types_arr[$j]);
					$auditSql="UPDATE ".$this->sqldb.navinfo($f[1], 'cmark')." SET `statu`=1 WHERE `id`=".$f[0];
					syClass("syModel")->runSql($auditSql);
				}
				message("批量审核成功","?action=backlog");
				break;
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
	}
	function __destruct(){  //析构用于清理数据库中已审核的待办事项
		$backlogs=syDB('backlog')->findAll();
		foreach ($backlogs as $v){
			$ct=syDB(navinfo($v['nid'], 'cmark'))->find(array('id'=>$v['id'])); //查找信息
			if($ct['statu']==1){
				syDB('backlog')->delete(array('id'=>$v['id'],'nid'=>$v['nid'])); //删除查看记录
			}
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
	}
}	