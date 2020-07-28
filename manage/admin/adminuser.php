<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class adminuser extends syController{
	public $pk = "auid";
	public $pkn = "gid";
	public $table = "admin";
	public $gtable= "admin_group";
	function __construct(){
		parent::__construct();
		$this->auid=$this->syArgs('auid');
		$this->a='member';
		$this->Class=syClass('c_admin');
		$this->db=$GLOBALS['WP']['db']['prefix'];
		$this->sqldb=$GLOBALS['WP']['db']['prefix'].$this->table;
		$imgtypes_arr=array('jpg','jpeg','png','gif','bmp');
		for ($i=0;$i<count($imgtypes_arr);$i++){
		    if(in_array($imgtypes_arr[$i],explode(",", $GLOBALS['WP']['ext']['filetype']))){
		        $member_type[]="*.".$imgtypes_arr[$i];
		    }
		}
		$this->member_type=join(";",$member_type);
		$this->member_typename='图片';
		$this->channels=syClass("c_channels");
		$this->authority0=syDB('admin_authority')->findAll(array('up'=>0,'no'=>1),' `order` desc,`authid` ');
		$this->authority1=syDB('admin_authority')->findAll(array('up'=>0,'no'=>0),' `order` desc,`authid` ');
		$this->conditions=array(
			'gid' => $this->syArgs('adminuser-schbygid')
		);
		$this->lists=$this->get_lists($this->conditions);
		$this->group=syDB($this->gtable)->findAll();
		if(($this->syArgs('o',1)=='add'||$this->syArgs('o',1)=='info') && $this->syArgs('go')==1){
			$this->adminuser_arr=array(
				'auser'=>$this->syArgs('adminuser_auser',1),	//登录名
				'user'=>$this->syArgs('adminuser_user',1),	//管理员名称
				'aname'=>$this->syArgs('adminuser_aname',1),	//真实姓名
				'aemail'=>$this->syArgs('adminuser_aemail',1),	//邮箱
				'avator'=>$this->syArgs('adminuser_avator',1),	//头像
			);
			if($this->syArgs('adminuser_avator_del',1)){
				$this->adminuser_arr=array_merge($this->adminuser_arr,array('avator'=>$GLOBALS['WP']['member']['default_avator']));
			}
			if($this->syArgs('adminuser_apass',1)!=''){
				$this->adminuser_arr=array_merge($this->adminuser_arr,array('apass'=>md5(md5($this->syArgs('adminuser_apass',1)).$this->syArgs('adminuser_auser',1))));
			}
			if($this->user['auid']==1){
				if($this->syArgs('adminuser_period',1)=='' || strtotime($this->syArgs('adminuser_period',1))==1924963199){
					$this->adminuser_arr=array_merge($this->adminuser_arr,array('period'=>''));
				}else {
					$this->adminuser_arr=array_merge($this->adminuser_arr,array('period'=>strtotime($this->syArgs('adminuser_period',1))));
				}
				$this->adminuser_arr=array_merge($this->adminuser_arr,array('gid'=>$this->syArgs('adminuser_group')));
			}
		}
	}
	function index(){
		$this->display("adminuser.html");
	}
	function info(){
		if($this->user['auid']!=1 && $this->user['auid']!=$this->syArgs('auid')){
			message("您不是超级管理员,无法更改其他管理员的信息",null,3,7);
		}
		$this->adminuser=$this->Class->find(array('auid'=>$this->auid));
		$member_arr=array( //同步修改会员表信息
			'username'=>$this->adminuser_arr['auser'],
			'portrait'=>$this->adminuser_arr['avator'],
			'nickname'=>$this->adminuser_arr['user'],
		);
		$member_contact_arr=array( //同步修改会员联系表信息
			'realname'=>$this->adminuser_arr['aname'],
			'email'=>$this->adminuser_arr['aemail']
		);
		if($this->adminuser_arr['apass']!=''){
			$member_arr=array_merge($member_arr,array('password'=>$this->adminuser_arr['apass']));
		}
		if($this->syArgs('go')==1){
			if($this->user['auid']==1){
				$group_authority=syDB($this->gtable)->find(array('gid'=>$this->adminuser['gid']));
				if($group_authority['gid']!=$this->syArgs('adminuser_group')){ //更改了分组
					$new_group_authority=syDB($this->gtable)->find(array('gid'=>$this->syArgs('adminuser_group')));
					$this->adminuser_arr=array_merge($this->adminuser_arr,array('authority'=>$new_group_authority['authority']));
				}else { //不改变分组的前提下
					if(!$this->syArgs('inherit_group',1)){ //不选择继承分组权限
						$authority_arr1=$this->syArgs('authority',2);
						if(!empty($authority_arr1)){
							$authority=",".join(",", $authority_arr1).",";
							$this->adminuser_arr=array_merge($this->adminuser_arr,array('authority'=>$authority));
						}else {
							$this->adminuser_arr=array_merge($this->adminuser_arr,array('authority'=>''));
						}
					}else { //继承分组权限
						$this->adminuser_arr=array_merge($this->adminuser_arr,array('authority'=>$group_authority['authority']));
					}
				}
			}
			if($this->Class->update(array('auid'=>$this->auid),$this->adminuser_arr) && syDB('member')->update(array('uid'=>$this->adminuser['uid']),$member_arr) && syDB('member_contact')->update(array('uid'=>$this->adminuser['uid']),$member_contact_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message('管理员信息修改成功','?action=adminuser');
			}else {
				message('管理员信息修改失败','?action=adminuser');
			}
		}
		$this->display("adminuser_info.html");
	}
	function del(){
		if(!$this->userClass->checkgo('adminuser_del')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->uid=$this->syArgs('uid');
		if($this->Class->delete(array('uid'=>$this->uid)) && syDB("member")->update(array('uid'=>$this->uid),array('adminid'=>0))){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message('管理员撤销成功','?action=adminuser');
		}else{
			message('管理员撤销失败','?action=adminuser');
		}
	}
	function group(){
		if($this->user['auid']!=1){
			message("您不是超级管理员，不能操作该栏目。",null,3,7);
		}
		$this->lists=syDB($this->gtable)->findAll();
		$this->op=$this->syArgs('op',1);
		$this->gid=$this->syArgs('gid');
		$this->adminusergroup=syDB($this->gtable)->find(array('gid'=>$this->gid));
		$this->adminusergroup_arr=array(
			'name'=>$this->syArgs('adminusergroup_name',1),
			'audit'=>$this->syArgs('adminusergroup_audit'),
			'oneself'=>$this->syArgs('adminusergroup_oneself')
		);
		$authority_arr=$this->syArgs('authority',2);
		if(!empty($authority_arr)){
			$authority=",".join(",", $authority_arr).",";
			$this->adminusergroup_arr=array_merge($this->adminusergroup_arr,array('authority'=>$authority));
		}else {
			$this->adminusergroup_arr=array_merge($this->adminusergroup_arr,array('authority'=>''));
		}
		if($this->syArgs('inhert_group_on',1)){ //勾选继承已有管理员分组
			if($this->syArgs('inhert_group')){
				$gauthority=syDB($this->gtable)->find(array('gid'=>$this->syArgs('inhert_group')));
				$this->adminusergroup_arr=array_merge($this->adminusergroup_arr,array('authority'=>$gauthority['authority']));
			}
		}
		switch ($this->op){
			case "edit":
				if($this->syArgs('go')==1){
					if(syDB($this->gtable)->update(array('gid'=>$this->gid),$this->adminusergroup_arr)){
						message("管理员分组[<font color='#1AB394'>".$this->adminusergroup['name']."</font>]修改成功","?action=adminuser&o=group");
					}else {
						message("管理员分组[<font color='#1AB394'>".$this->adminusergroup['name']."</font>]修改失败","?action=adminuser&o=group");
					}
				}
				break;
			case "add":
				if($this->syArgs('go')==1){
					if(syDB($this->gtable)->find(array('name'=>$this->syArgs('adminusergroup_name',1)))){
						message("对不起，该管理员分组已存在","?action=adminuser&o=group&op=add");
					}
					if(syDB($this->gtable)->create($this->adminusergroup_arr)){
						message("管理员分组添加成功","?action=adminuser&o=group");
					}else {
						message("管理员分组添加失败","?action=adminuser&o=group");
					}
				}
				break;
			case "del":
				$adminusers=$this->Class->findAll(array('gid'=>$this->gid),null,'`uid`');
				if($adminusers){
					message("该分组下尚有管理员，无法删除该管理员分组");
				}else {
					if(syDB($this->gtable)->delete(array('gid'=>$this->gid))){
						message("管理员分组删除成功","?action=adminuser&o=group");
					}else {
						message("管理员分组删除失败，请至数据库手动删除，并保证分组下无任何管理员","?action=adminuser&o=group");
					}
				}
				break;
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
		$this->display("adminuser_group.html");
	}
	function get_lists($conditions){
		if($conditions['gid']!=0){
			$condition.="and `gid`=".$conditions['gid']." ";
		}
		if($condition!=''){$condition=' where '.substr($condition,3);}
		$sql='select * from '.$this->sqldb.$condition.' order by `level` desc,`auid` desc';
		return $this->lists = $this->Class->findSql($sql);
	}
	function membertoadmin(){ //会员提升为管理员
		if(!$this->userClass->checkgo('adminuser_add')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		if($this->syArgs('go')==1){
			$member_info=syDB("member")->find(array('uid'=>$this->syArgs('uid')));
			$member_contact=syDB("member_contact")->find(array('uid'=>$this->syArgs('uid')));
			$membertoadmin_arr=array(
				'uid'=>$this->syArgs('uid'),
				'user'=>$member_info['nickname'],
				'auser'=>$member_info['username'],
				'apass'=>$member_info['password'],
				'aname'=>$member_contact['realname'],
				'aemail'=>$member_contact['email'],
				'avator'=>$member_info['portrait'],
				'gid'=>$this->syArgs('membertoadmin_group'),
				'addtime'=>time()
			);
			switch ($this->syArgs('membertoadmin_period')){
				case 0: //永久权限
					$membertoadmin_arr=array_merge($membertoadmin_arr,array('period'=>''));
					break;
				case 1:	//5天权限
					$period1=time()+5*24*3600;
					$membertoadmin_arr=array_merge($membertoadmin_arr,array('period'=>$period1));
					break;
				case 2:	//10天权限
					$period2=time()+10*24*3600;
					$membertoadmin_arr=array_merge($membertoadmin_arr,array('period'=>$period2));
					break;
				case 3:	//30天权限
					$period3=time()+30*24*3600;
					$membertoadmin_arr=array_merge($membertoadmin_arr,array('period'=>$period3));
					break;
				case 4:	//3个月权限
					$period4=time()+3*30*24*3600;
					$membertoadmin_arr=array_merge($membertoadmin_arr,array('period'=>$period4));
					break;
				case 5:	//1年权限
					$period5=time()+365*24*3600;
					$membertoadmin_arr=array_merge($membertoadmin_arr,array('period'=>$period5));
					break;
				case 6:	//2年权限
					$period6=time()+2*365*24*3600;
					$membertoadmin_arr=array_merge($membertoadmin_arr,array('period'=>$period6));
					break;
			}
			$authority=syDB($this->gtable)->find(array('gid'=>$this->syArgs('membertoadmin_group')));
			$membertoadmin_arr=array_merge($membertoadmin_arr,array('authority'=>$authority['authority']));
			if($this->Class->create($membertoadmin_arr) && syDB('member')->update(array('uid'=>$this->syArgs('uid')),array('adminid'=>1))){
				message("将会员[<font color='#337AB7'>".$member_info['nickname']."</font>]提升为<font color='#F00'>".$authority['name']."</font>成功",'?action=adminuser');
			}else {
				message("将会员[<font color='#337AB7'>".$member_info['nickname']."</font>]提升为管理员失败，请重新操作",'?action=member');
			}
		}
		$this->display("adminuser.html");
	}
	function __destruct() {
		$allAdminuser=$this->Class->findAll();
		foreach ($allAdminuser as $a){ //判断管理员有效期，如果时间到，则自动撤销该管理员
			if($a['period']!=0 && time()>=$a['period']){ //有效期等于当前时间，说明管理员已到期，自动撤销
				$this->Class->delete(array('uid'=>$a['uid']));
				syDB("member")->update(array('uid'=>$a['uid']),array('adminid'=>0));
				deleteDir($GLOBALS['WP']['sp_cache']);
			}
		}
	}
}	