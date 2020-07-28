<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class channels extends syController{
	public $pk = "cid";
	public $table = "channels";
	function __construct(){
		parent::__construct();
		$this->a='channels';
		$this->Class=syClass("c_channels");
		$this->lists=$this->Class->findAll();
		$this->default_lists=$this->Class->findAll(array('sys'=>1));
		$this->o=$this->syArgs('o',1);
		$this->cid=$this->syArgs('cid');
		$this->db=$GLOBALS['WP']['db']['prefix'];
	}
	function index(){
		$this->display("channels.html");
	}
	function edit(){
		if(!$this->userClass->checkgo('channels_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->channel=$this->Class->find(array('cid'=>$this->cid));
		if($this->syArgs('go')==1){
			$channel_arr=array(
				'cname'=>$this->syArgs('channel_name',1),
				'statu' => $this->syArgs('channel_statu'),
				'ct_list' => $this->syArgs('channel_ct_list',1),
				'ct_listimg' =>$this->syArgs('channel_ct_listimg',1),
				'ct_listbody' => $this->syArgs('channel_ct_listbody',1),
				'ct_content' => $this->syArgs('channel_ct_content',1)
			);
			if($this->Class->update(array('cid'=>$this->cid),$channel_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("频道信息修改成功");
			}else {
				message("频道信息修改失败...");
			}
		}
		$this->display("channels_edit.html");
	}
	function add(){
		message("该功能正在开发中，暂无法使用");
		if(!$this->userClass->checkgo('channels_add')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		if($this->syArgs('go')==1){
			$channel_arr=array(
					'cname'=>$this->syArgs('channel_name',1),
					'cmark'=>$this->syArgs('channel_mark',1),
					'statu' => $this->syArgs('channel_statu'),
					'ct_list' => $this->syArgs('channel_ct_list',1),
					'ct_listimg' =>$this->syArgs('channel_ct_listimg',1),
					'ct_listbody' => $this->syArgs('channel_ct_listbody',1),
					'ct_content' => $this->syArgs('channel_ct_content',1)
			);
// 			$f1=$this->Class->findSql('SHOW TABLES LIKE "'.$this->db.$channel_arr['cmark'].'"');
// 			$f2=$this->Class->findSql('SHOW TABLES LIKE "'.$this->db.$channel_arr['cmark'].'_field"');
// 			if($f1 || $f2){message("频道标识数据表已存在，请重新输入");}
// 			if($this->Class->find(array('cmark'=>$this->syArgs('channel_mark',1)))){
// 				message("您输入的频道标识已经存在，请修改后重新提交");
// 			}
// 			$txt='<?php
// class c_'.$channel_arr['cmark'].' extends syModel{	var $pk = "id";	var $table = "'.$channel_arr['cmark'].'";}';
// 			if(!write_file($txt,'system/class/c_'.$channel_arr['cmark'].'.php')){message("频道标识类文件[<font color='#F00'>system/class/c_".$channel_arr['cmark'].".php</font>]已存在，请重新输入");}
			switch($this->syArgs('upchannel')){ 
				case 1://继承文章
				$dbsql1="CREATE TABLE IF NOT EXISTS `".$this->db.$channel_arr['cmark']."` (
					`id` mediumint(8) unsigned NOT NULL auto_increment,
					`nid` smallint(5) unsigned NOT NULL default '0',
					`sid` smallint(5) unsigned NOT NULL default '0',
					`statu` tinyint(1) unsigned NOT NULL default '0',
					`title` varchar(100) NOT NULL,
					`traits` varchar(50) NOT NULL,
					`gourl` varchar(255) NOT NULL,
					`htmlfile` varchar(100) NOT NULL,
					`htmlurl` varchar(255) NOT NULL,
					`addtime` int(10) unsigned NOT NULL default '0',
					`hints` int(10) unsigned NOT NULL default '0',
					`lipic` varchar(255) NOT NULL,
					`order` int(10) NOT NULL default '0',
					`mrank` smallint(5) NOT NULL default '0',
					`mgold` decimal(10,2) unsigned NOT NULL default '0.00',
					`keywords` varchar(200) NOT NULL,`description` varchar(255) NOT NULL,
					`user` varchar(30) NOT NULL,
					`usertype` tinyint(2) unsigned NOT NULL default '0',
					`password` varchar(255) default NULL,PRIMARY KEY  (`id`),
					KEY `orbye` (`order`,`addtime`),
					KEY `".$channel_arr['cmark']."` (`statu`,`nid`,`traits`,`sid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
					$article_file='manage/admin/article.php';
					$new_class_file='manage/admin/'.$channel_arr['cmark'].'.php';
					if(!copyFile($article_file, $new_class_file)){
						message("频道类文件创建失败，请重新创建");
					}
					break;
				case 2: //继承产品
					$dbsql1="CREATE TABLE IF NOT EXISTS `".$this->db.$channel_arr['cmark']."` (
					`id` mediumint(8) unsigned NOT NULL auto_increment,
					`nid` smallint(5) unsigned NOT NULL default '0',
					`sid` smallint(5) unsigned NOT NULL default '0',
					`statu` tinyint(1) unsigned NOT NULL default '0',
					`title` varchar(100) NOT NULL,
					`traits` varchar(50) NOT NULL,
					`gourl` varchar(255) NOT NULL,
					`htmlfile` varchar(100) NOT NULL,
					`htmlurl` varchar(255) NOT NULL,
					`addtime` int(10) unsigned NOT NULL default '0',
					`inventory` int(10) unsigned NOT NULL default '0',
					`record` int(10) unsigned NOT NULL default '0',
					`hints` int(10) unsigned NOT NULL default '0',
					`lipic` varchar(255) NOT NULL,
					`picture` text NOT NULL,
					`order` int(10) NOT NULL default '0',
					`price` decimal(10,2) unsigned NOT NULL default '0.00',
					`virtual` tinyint(1) unsigned NOT NULL default '0',
					`logistics` varchar(255) NOT NULL,
					`mrank` smallint(5) NOT NULL default '0',
					`mgold` decimal(10,2) unsigned NOT NULL default '0.00',
					`keywords` varchar(200) NOT NULL,
					`description` varchar(255) NOT NULL,
					`user` varchar(30) NOT NULL,
					`usertype` tinyint(2) unsigned NOT NULL default '0',
					`password` varchar(255) default NULL,
					PRIMARY KEY  (`id`),
					KEY `orbye` (`order`,`addtime`),
					KEY `".$channel_arr['cmark']."` (`statu`,`nid`,`traits`,`sid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
					break;
				case 18: //继承人才招聘
					$dbsql1="CREATE TABLE IF NOT EXISTS `".$this->db.$channel_arr['cmark']."` (
					`id` mediumint(8) unsigned NOT NULL auto_increment,
					`nid` smallint(5) unsigned NOT NULL default '0',
					`sid` smallint(5) unsigned NOT NULL default '0',
					`statu` tinyint(1) unsigned NOT NULL default '0',
					`title` varchar(100) NOT NULL,
					`traits` varchar(50) NOT NULL,
					`gourl` varchar(255) NOT NULL,
					`htmlfile` varchar(100) NOT NULL,
					`htmlurl` varchar(255) NOT NULL,
					`addtime` int(10) unsigned NOT NULL default '0',
					`hints` int(10) unsigned NOT NULL default '0',
					`lipic` varchar(255) NOT NULL,
					`order` int(10) NOT NULL default '0',
					`mrank` smallint(5) NOT NULL default '0',
					`mgold` int(10) unsigned NOT NULL default '0',
					`keywords` varchar(200) NOT NULL,
					`description` varchar(255) NOT NULL,
					`user` varchar(30) NOT NULL,
					`usertype` tinyint(2) unsigned NOT NULL default '0',
					PRIMARY KEY  (`id`),
					KEY `orbye` (`order`,`addtime`),
					KEY `".$channel_arr['cmark']."` (`statu`,`nid`,`traits`,`sid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;";
					break;
			}
			exit();
			$dbsql2="CREATE TABLE IF NOT EXISTS `".$this->db.$channel_arr['cmark']."_field` (
				`aid` mediumint(8) unsigned NOT NULL default '0',
				`detail` mediumtext NOT NULL,
				PRIMARY KEY  (`aid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			if(!$this->Class->runSql($dbsql1)||!$this->Class->runSql($dbsql2)){
				message("频道数据库创建失败，请重新提交");
			}
			$pr=syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'], 'name'=>'管理','molds'=>$this->newrow['molds']));
			syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_add', 'name'=>'添加','molds'=>'','up'=>$pr));
			syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_edit', 'name'=>'编辑','molds'=>'','up'=>$pr));
			syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_del', 'name'=>'删除','molds'=>'','up'=>$pr));
			syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_audit', 'name'=>'审核','molds'=>'','up'=>$pr));
			syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_index', 'name'=>'列表','molds'=>'','no'=>1,'up'=>$pr));
			if($this->Class->create($channel_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("频道添加成功","?action=channels");
			}else {
				message("频道添加失败...","?action=channels");
			}
		}
		$this->display("channels_add.html","?action=channels");
	}
	function del() {
		if(!$this->userClass->checkgo('channels_del')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$channel_info=$this->Class->find(array('cid'=>$this->cid));
		$delchsql="DROP TABLE IF EXISTS ".$this->db.$channel_info['cmark'];
		$delchfsql="DROP TABLE IF EXISTS ".$this->db.$channel_info['cmark']."_field";
		if(syClass('syModel')->runSql($delchsql) && syClass('syModel')->runSql($delchfsql) && $this->Class->delete(array('cid'=>$this->cid))){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message("频道删除成功");
		}else {
			message("频道删除失败，请至数据库手动删除...");
		}
	}
}	