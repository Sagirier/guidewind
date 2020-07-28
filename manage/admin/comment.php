<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class comment extends syController{
	public $pk = "id";
	public $pkn = "nid";
	public $table = "comment";
	public $rtable = "comment_reply";
	function __construct(){
		parent::__construct();
		$this->gopage=$this->syArgs('page',0,1);
		$this->id=$this->syArgs('id');
		$this->a='contents';
		$this->Class=syClass('c_comment');
		$this->db=$GLOBALS['WP']['db']['prefix'];
		$this->sqldb=$GLOBALS['WP']['db']['prefix'].$this->table;
		$this->conditions=array(
			'nid' => $this->syArgs('comment-schbynid'),
			'type' => $this->syArgs('comment-schbytype'),
			'statu' => $this->syArgs('comment-schbystatu'),
			'restatu' => $this->syArgs('comment-schbyrestatu'),
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
			$condition.="and `detail` like '%".$this->conditions['title']."%' ";
		}
		if($condition!=''){$condition=' where '.substr($condition,3);}
		$sql='select * from '.$this->sqldb.$condition.' order by `order` desc,`addtime` desc,`id` desc';
		//获取评论总数
		$total_count=total_count($this->sqldb.$condition);
		//进行分页输出
		$this->lists = $this->Class->syPager($this->gopage,10,$total_count)->findSql($sql);
		$this->pages = pagetxt($this->Class->syPager()->getPager());
		$this->members=syDB('member')->findAll(null," `adminid` desc,`username`,`uid`");
		$this->comment=$this->Class->find(array('id'=>$this->id));
		$this->comment_user=syDB('member')->find(array('uid'=>$this->comment['uid']));
		$this->reply=syDB($this->rtable)->findAll(array('mid'=>$this->comment['id'])," `retime`,`upid` desc");
		$this->result=syDB($this->rtable)->findAll();
		if($this->syArgs('o',1)=='edit' && $this->syArgs('go')==1){
			$this->comment_arr=array(
					'statu'=>$this->syArgs('comment_statu'),
					'addtime'=>strtotime($this->syArgs('comment_addtime',1)),
					'order'=>$this->syArgs('comment_order')
			);
			if($this->syArgs('comment_username',1)=='' || $this->syArgs('comment_username',1)=='游客'){
				$this->comment_arr=array_merge($this->comment_arr,array('uid'=>0));
			}else {
				if(!$user_info=syDB('member')->find(array('username'=>$this->syArgs('comment_username',1)))){
					message('不存在该用户，请重新输入');
				}else {
					$this->comment_arr=array_merge($this->comment_arr,array('uid'=>$user_info['uid']));
				}
			}
		}
	}
	function index(){
		$this->display("comment.html");
	}
	function view(){
		if($this->syArgs('go')==1){ //编辑评论内容
			$this->Class->update(array('id'=>$this->id),array('detail'=>$this->syArgs('comment_reply',1)));
		}
		if($this->syArgs('go')==2){ //编辑已回复的评论
			syDB($this->rtable)->update(array('id'=>$this->syArgs('replyid')),array('reply'=>$this->syArgs('comment_reply'.$this->syArgs('replyid'),1)));
		}
		if($this->syArgs('go')==3){ //删除评论
			if(syDB($this->rtable)->delete(array('id'=>$this->syArgs('replyid')))){
				message('评论回复信息删除成功');
			}
		}
		if($this->syArgs('go')==4){ //回复主评论
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
		if($this->syArgs('go')==5){ //回复二次评论
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
		$this->display("comment_view.html");
	}
	function edit(){
		if(!$this->userClass->checkgo('comment_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		if($this->syArgs('go')==1){
			if($this->Class->update(array('id'=>$this->id),$this->comment_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message('评论信息修改成功','?action=comment');
			}else {
				message('评论信息修改失败...','?action=comment',3,1);
			}
		}
		$this->display("comment_edit.html");
	}
	function del(){
		if(!$this->userClass->checkgo('comment_del')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		if($this->Class->delete(array('id'=>$this->id)) && syDB($this->rtable)->delete(array('mid'=>$this->id))){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message("删除评论[<font color='#F00'>id=".$this->id."</font>]成功",'?action=comment');
		}else {
			message("删除评论失败，请至数据库手动删除...",'?action=comment',3,1);
		}
	}
	function alledit(){
		if(!$this->userClass->checkgo('comment_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$formnum=$this->syArgs('formnum');
		$types_arr=$this->syArgs('types',2);
		$this->operate_allcomment($formnum,$types_arr);
	}
	function operate_allcomment($formnum,$types_arr){
		$id_str=join(", ", $types_arr);
		$where_id_str = "`id` IN (".$id_str.")";
		$where_mid_str ="`mid` IN (".$id_str.")";
		switch ($formnum){
			case 1:
				if(!$types_arr){
					message('您尚未选择任何评论信息...','?action=comment',0,0);
				}
				$row['statu']=1;
				if($this->Class->update($where_id_str,$row)){
					message('批量审核成功','?action=comment');
				}else {
					message('批量审核失败...','?action=comment',3,1);
				}
				break;
			case 2:
				if(!$types_arr){
					message('您尚未选择任何评论信息...','?action=comment',0,0);
				}
				$row['statu']=0;
				if($this->Class->update($where_id_str,$row)){
					message('批量取消审核成功','?action=comment');
				}else {
					message('批量取消审核失败...','?action=comment',3,1);
				}
				break;
			case 3:
				if(!$types_arr){
					message('您尚未选择任何评论信息...','?action=comment',0,0);
				}
				if($this->Class->delete($where_id_str) && syDB($this->rtable)->delete($where_mid_str)){
					message('批量删除成功','?action=comment');
				}else {
					message('批量删除失败...','?action=comment',3,1);
				}
				break;
			case 4:
				$orders=$this->syArgs('orders',2);
				foreach ($orders as $id => $order){
				    $condition_id=$this->pk."=".$id;
					$row_id['order']=$order;
 					if(!$this->Class->update($condition_id,$row_id)){
 						message('评论信息['.$condition_id.']顺序更改失败...','?action=comment',3,1);
 					}
				}
				message('顺序更改成功','?action=comment');
				break;
			case 5:
				if(!$types_arr){
					message('您尚未选择任何评论信息...','?action=comment',0,0);
				}
				if($nid=$this->syArgs('rnid')){
					$row_nid['nid']=$nid;
					$resnid=$this->Class->update($where_id_str,$row_nid);
				}
				if($resnid){
					message("批量更改栏目成功",'?action=comment');
				}else {
					message("批量操作失败...",'?action=comment',3,1);
				}
				break;
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
	}
}