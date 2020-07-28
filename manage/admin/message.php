<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class message extends syController{
	public $pk = "id";
	public $pkn = "nid";
	public $table = "message";
	public $rtable = "message_reply";
	function __construct(){
		parent::__construct();
		$this->gopage=$this->syArgs('page',0,1);
		$this->id=$this->syArgs('id');
		$this->a='contents';
		$this->channels = 'message';
		$this->Class=syClass('c_'.$this->channels);
		$this->ClassN=syClass('c_navigators');
		$this->sy_class_type=syClass('synavigators');
		$this->navtree=$this->sy_class_type->type_txt();
		$this->cname=syDB("channels")->find(array('cmark'=>$this->channels));
		$this->db=$GLOBALS['WP']['db']['prefix'];
		$this->sqldb=$GLOBALS['WP']['db']['prefix'].$this->channels;
		$this->conditions=array(
			'nid' => $this->syArgs('message-schbynid'),
			'type' => $this->syArgs('message-schbytype'),
			'statu' => $this->syArgs('message-schbystatu'),
			'restatu' => $this->syArgs('message-schbyrestatu'),
			'title' =>	$this->syArgs('title',1)
		);
		$condition="";
		if($this->conditions['nid']!=0){
			$condition.="and `nid` in(".$this->sy_class_type->leafid($this->conditions['nid']).") ";
		}
		if($this->conditions['type']==1){
			$condition.="and `cmark`!='' ";
		}
		if($this->conditions['type']==2){
			$condition.="and `cmark`='' ";
		}
		if($this->conditions['statu']==1){
			$this->top_txt='<font color="#23C6C8">已审核</font>的';
			$condition.="and `statu`=1 ";
		}
		if($this->conditions['statu']==2){
			$this->top_txt='<font color="#F00">待审核</font>的';
			$condition.="and `statu`=0 ";
		}
		if($this->conditions['restatu']==1){
			$this->top_txt='<font color="#23C6C8">已回复</font>的';
			$condition.="and `restatu`=1 ";
		}
		if($this->conditions['restatu']==2){
			$this->top_txt='<font color="#F00">未回复</font>的';
			$condition.="and `restatu`=0 ";
		}
		if($this->conditions['title']!=''){
			$condition.="and `title` like '%".$this->conditions['title']."%' ";
		}
		if($condition!=''){$condition=' where '.substr($condition,3);}
		$sql='select * from '.$this->sqldb.$condition.' order by `order` desc,`addtime` desc,`id` desc';
		//获取文章总数
		$total_count=total_count($this->sqldb.$condition);
		//进行分页输出
		$this->lists = $this->Class->syPager($this->gopage,10,$total_count)->findSql($sql);
		$this->pages = pagetxt($this->Class->syPager()->getPager());
		$this->members=syDB('member')->findAll(null," `adminid` desc,`username`,`uid`");
		$this->message=$this->Class->find(array('id'=>$this->id));
		$this->message_user=syDB('member')->find(array('uid'=>$this->message['uid']));
		$this->reply=syDB($this->rtable)->findAll(array('mid'=>$this->message['id'])," `retime`,`upid` desc");
		$this->result=syDB($this->rtable)->findAll();
		if($this->syArgs('o',1)=='edit' && $this->syArgs('go')==1){
			$this->message_arr=array(
					'nid'=>$this->syArgs('message_nid'),
					'statu'=>$this->syArgs('message_statu'),
					'title'=>$this->syArgs('message_title',1),
					'addtime'=>strtotime($this->syArgs('message_addtime',1)),
					'order'=>$this->syArgs('message_order')
			);
			if($this->syArgs('message_username',1)=='' || $this->syArgs('message_username',1)=='游客'){
				$this->message_arr=array_merge($this->message_arr,array('uid'=>0));
			}else {
				if(!$user_info=syDB('member')->find(array('username'=>$this->syArgs('message_username',1)))){
					message('不存在该用户，请重新输入');
				}else {
					$this->message_arr=array_merge($this->message_arr,array('uid'=>$user_info['uid']));
				}
			}
		}
	}
	function index(){
		$this->display($this->channels.".html");
	}
	function view(){
		if($this->syArgs('go')==1){ //编辑留言内容
			$this->Class->update(array('id'=>$this->id),array('detail'=>$this->syArgs('message_reply',1)));
		}
		if($this->syArgs('go')==2){ //编辑已回复的留言
			syDB($this->rtable)->update(array('id'=>$this->syArgs('replyid')),array('reply'=>$this->syArgs('message_reply'.$this->syArgs('replyid'),1)));
		}
		if($this->syArgs('go')==3){ //删除留言
		    if(syDB($this->rtable)->delete(array('id'=>$this->syArgs('replyid')))){
				message('留言回复信息删除成功');
			}
		}
		if($this->syArgs('go')==4){ //回复主留言
		    
			$reply_arr0=array(
				'mid'=>$this->id,
				'retime'=>time(),
				'reply'=>$this->syArgs('replyuser_reply',1),
				'reuid'=>$this->user['uid'],
				'statu'=>1,
				'upid'=>0
			);
			if(syDB($this->rtable)->create($reply_arr0) && $this->Class->update(array('id'=>$this->id),array('restatu'=>1))){
				message('回复成功');
			}
		}
		if($this->syArgs('go')==5){ //回复二次留言
			$reply_arr1=array(
				'mid'=>$this->id,
				'retime'=>time(),
				'reply'=>$this->syArgs('reply_reply',1),
				'reuid'=>$this->user['uid'],
				'statu'=>1,
				'upid'=>$this->syArgs('replyid')
			);
			if(syDB($this->rtable)->create($reply_arr1)){
				message('回复成功');
			}
		}
		$this->display($this->channels."_view.html");
	}
	function edit(){
		if(!$this->userClass->checkgo('message_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		if($this->syArgs('go')==1){
			if($this->Class->update(array('id'=>$this->id),$this->message_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message('留言信息修改成功','?action='.$this->channels);
			}else {
				message('留言信息修改失败...','?action='.$this->channels,3,1);
			}
		}
		$this->display($this->channels."_edit.html");
	}
	function del(){
		if(!$this->userClass->checkgo('message_del')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		if($this->Class->delete(array('id'=>$this->id)) && syDB($this->rtable)->delete(array('mid'=>$this->id))){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message("删除留言[<font color='#F00'>id=".$this->id."</font>]成功",'?action='.$this->channels);
		}else {
			message("删除留言失败，请至数据库手动删除...",'?action='.$this->channels,3,1);
		}
	}
	function alledit(){
		if(!$this->userClass->checkgo('message_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$formnum=$this->syArgs('formnum');
		$types_arr=$this->syArgs('types',2);
		$this->operate_allmessage($formnum,$types_arr);
	}
	function operate_allmessage($formnum,$types_arr){
		$id_str=join(", ", $types_arr);
		$where_id_str = "`id` IN (".$id_str.")";
		$where_mid_str ="`mid` IN (".$id_str.")";
		switch ($formnum){
			case 1:
				if(!$types_arr){
					message('您尚未选择任何留言信息...','?action='.$this->channels,0,0);
				}
				$row['statu']=1;
				if($this->Class->update($where_id_str,$row)){
					message('批量审核成功','?action='.$this->channels);
				}else {
					message('批量审核失败...','?action='.$this->channels,3,1);
				}
				break;
			case 2:
				if(!$types_arr){
					message('您尚未选择任何留言信息...','?action='.$this->channels,0,0);
				}
				$row['statu']=0;
				if($this->Class->update($where_id_str,$row)){
					message('批量取消审核成功','?action='.$this->channels);
				}else {
					message('批量取消审核失败...','?action='.$this->channels,3,1);
				}
				break;
			case 3:
				if(!$types_arr){
					message('您尚未选择任何留言信息...','?action='.$this->channels,0,0);
				}
				if($this->Class->delete($where_id_str) && syDB($this->rtable)->delete($where_mid_str)){
					message('批量删除成功','?action='.$this->channels);
				}else {
					message('批量删除失败...','?action='.$this->channels,3,1);
				}
				break;
			case 4:
				$orders=$this->syArgs('orders',2);
				foreach ($orders as $id => $order){
				    $condition_id=$this->pk."=".$id;
					$row_id['order']=$order;
 					if(!$this->Class->update($condition_id,$row_id)){
 						message('留言信息['.$condition_id.']顺序更改失败...','?action='.$this->channels,3,1);
 					}
				}
				message('顺序更改成功','?action='.$this->channels);
				break;
			case 5:
				if(!$types_arr){
					message('您尚未选择任何留言信息...','?action='.$this->channels,0,0);
				}
				if($nid=$this->syArgs('rnid')){
					$row_nid['nid']=$nid;
					$resnid=$this->Class->update($where_id_str,$row_nid);
				}
				if($resnid){
					message("批量更改栏目成功",'?action='.$this->channels);
				}else {
					message("批量操作失败...",'?action='.$this->channels,3,1);
				}
				break;
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
	}
}