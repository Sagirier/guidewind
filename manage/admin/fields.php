<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class fields extends syController{
	public $pk = "fid";
	public $table = "fields";
	function __construct(){
		parent::__construct();
		$this->Class=syClass("c_fields");
		$this->c=$this->syArgs("c",1);
		$this->nid=$this->syArgs('nid');
		$this->sqldb=$GLOBALS['WP']['db']['prefix'].$this->c;
		if(!$this->nid){
			if($this->c=='member'){
				$this->top_txt="会员";
				$this->a='member';
				$this->lists_full=$this->Class->findAll(array('cmark'=>'member'));
				$lists=$this->get_lists($this->lists_full);
				$this->lists=$lists[0];
				$this->groups=$lists[1];
			}else {
				$this->a="channels";
				$this->cname=syDB('channels')->find(array('cmark'=>$this->c));
				$this->top_txt=$this->cname['cname'];
				$this->lists_full=$this->Class->findAll(array('cmark'=>$this->c));
				$lists=$this->get_lists($this->lists_full);
				$this->lists=$lists[0];
			}
		}else{
			$this->a="navigators";
			$this->cnav=syDB('navigators')->find(array('nid'=>$this->nid));
			$this->top_txt=$this->cnav['nname'];
			$where_nid=" `cmark`='".$this->c."' and `navigators` like '%|".$this->nid."|%' ";
			$this->lists_full=$this->Class->findAll($where_nid,' `order` DESC,`fid` ');
			$lists=$this->get_lists($this->lists_full);
			$this->lists=$lists[0];
		}
		$this->navtree=syClass('synavigators')->type_txt();
	}
	function index(){
		$this->display("fields.html");
	}
	function get_lists($lists){
		$new_arr=$lists;
		$n_lists=array();
		for ($i=0;$i<count($lists);$i++){
			switch ($lists[$i]['ftype']){
				case "varchar":
					$new_arr[$i]['ftype']="中小型文本";
					break;
				case "text":
					$new_arr[$i]['ftype']="大型文本";
					break;
				case "int":
					$new_arr[$i]['ftype']="整数";
					break;
				case "money":
					$new_arr[$i]['ftype']="货币";
					break;
				case "date":
					$new_arr[$i]['ftype']="日期";
					break;
				case "radio":
					$new_arr[$i]['ftype']="单选菜单";
					if($lists[$i]['selects']!=''){
						$new_arr[$i]['selects']='';
						foreach(explode(',',$lists[$i]['selects']) as $v){
							$s=explode('=',$v);
							$new_arr[$i]['selects'].='<option hassubinfo="true" value="'.$s[1].'" ';
							$new_arr[$i]['selects'].='>'.$s[0].'</option>';
						}
					}
					break;
				case "select":
					$new_arr[$i]['ftype']="多选菜单";
					if($lists[$i]['selects']!=''){
						$new_arr[$i]['selects']='';
						foreach(explode(',',$lists[$i]['selects']) as $v){
							$s=explode('=',$v);
							$new_arr[$i]['selects'].='<option hassubinfo="true" value="'.$s[1].'" ';
							$new_arr[$i]['selects'].='>'.$s[0].'</option>';
						}
					}
					break;
				case "file":
					$new_arr[$i]['ftype']="单个附件";
					break;
				case "multifile":
					$new_arr[$i]['ftype']="多个附件";
					break;
				default:
					$new_arr[$i]['ftype']="";
					break;
			}
			if($lists[$i]['flength']==0){
				$new_arr[$i]['flength']="";
			}
			if($lists[$i]['navigators']!=''){
				$new_arr[$i]['navigators']='';
				foreach(explode('|',$lists[$i]['navigators']) as $v){
					if($v!=''){
						$kname_info=syDB("navigators")->findAll("nid=".$v);
						$kname_info=array_pop($kname_info);
						$new_arr[$i]['navigators'].='<option hassubinfo="true" value="'.$v.'" ';
						$new_arr[$i]['navigators'].='>'.$kname_info['nname'].'</option>';
					}
				}
			}
			$n_lists[$lists[$i]['fid']]=$lists[$i]['navigators'];
		}
		return array($new_arr,$n_lists);
	}
	function edit(){
		if(!$this->userClass->checkgo('fields_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->field=$this->Class->find(array('fid'=>$this->syArgs('fid')));
		if($this->syArgs('go')==1){
			$field_arr=array(
				'fname'=>$this->syArgs('field_fname',1),
				'fmark'=>$this->syArgs('field_fmark',1),
				'selects'=>$this->syArgs('field_selects',1),
				'ftype'=>$this->syArgs('field_ftype',1),
				'order'=>$this->syArgs('field_order'),
				'issubmit'=>$this->syArgs('field_issubmit'),
				'lists'=>$this->syArgs('field_lists'),
				'statu'=>$this->syArgs('field_statu'),
				'navigators'=>$this->syArgs('types',2),
			);
			switch($this->syArgs('field_ftype',1)){
				case 'varchar':
					$fl=$this->syArgs('varchar_flength');
					if($fl==0 || $fl>255){
						$fl=255;
					}
					$field_arr=array_merge($field_arr,array('flength'=>$fl,'selects'=>'','imgw'=>0,'imgh'=>0));
					break;
				case 'text':
					$field_arr=array_merge($field_arr,array('flength'=>0,'selects'=>'','imgw'=>$this->syArgs('text_imgw'),'imgh'=>$this->syArgs('text_imgh')));
					break;
				case 'int':
					$field_arr=array_merge($field_arr,array('flength'=>0,'selects'=>'','imgw'=>0,'imgh'=>0));
					break;
				case 'date':
					$field_arr=array_merge($field_arr,array('flength'=>0,'selects'=>'','imgw'=>0,'imgh'=>0));
					break;
				case 'file':
					$field_arr=array_merge($field_arr,array('flength'=>0,'selects'=>'','imgw'=>$this->syArgs('file_imgw'),'imgh'=>$this->syArgs('file_imgh')));
					break;
				case 'multifile':
					$field_arr=array_merge($field_arr,array('flength'=>0,'selects'=>'','imgw'=>$this->syArgs('multifile_imgw'),'imgh'=>$this->syArgs('multifile_imgh')));
					break;
				case 'radio':
					$field_arr=array_merge($field_arr,array('flength'=>0,'selects'=>$this->syArgs('field_selects',1),'imgw'=>0,'imgh'=>0));
					break;
				case 'select':
					$field_arr=array_merge($field_arr,array('flength'=>0,'selects'=>$this->syArgs('field_selects',1),'imgw'=>0,'imgh'=>0));
					break;
			}
			if(is_array($field_arr['navigators'])){
				$navigators='|';
				for ($i=0;$i<count($field_arr['navigators']);$i++){
					$navigators.=$field_arr['navigators'][$i]."|";
				}
				$field_arr['navigators']=$navigators;
			}else {
				$field_arr['navigators']='||';
			}
			if($this->c=='member'){
			    $type=$this->syArgs('types',2);
			    if(empty($type)){
			        message("请选择字段所属栏目",null,3,0);
			    }
			}
			if($this->Class->update(array('fid'=>$this->syArgs('fid')),$field_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message('字段修改成功');
			}else {
			    message("字段修改失败");
			}
		}
		$this->display("fields_edit.html");
	}
	function add(){
		if(!$this->userClass->checkgo('fields_add')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		if($this->syArgs('go')==1){
			$field_arr=array(
				'cmark'=>$this->c,
				'fname'=>$this->syArgs('field_fname',1),
				'fmark'=>$this->syArgs('field_fmark',1),
				'ftype'=>$this->syArgs('field_ftype',1),
				'selects'=>$this->syArgs('field_selects',1),
				'order'=>$this->syArgs('field_order'),
				'issubmit'=>$this->syArgs('field_issubmit'),
				'lists'=>$this->syArgs('field_lists'),
				'statu'=>$this->syArgs('field_statu'),
				'navigators'=>$this->syArgs('types',2)
			);
			$this->field_nav_arr=syDB('navigators')->findAll(array('cmark'=>$this->c));
			$field_temporary_info1=$this->Class->findSql('describe '.$this->sqldb.' '.$this->syArgs('field_fmark',1));
			$field_temporary_info2=$this->Class->findSql('describe '.$this->sqldb.'_field '.$this->syArgs('field_fmark',1));
			if($field_temporary_info1 || $field_temporary_info2){
				message("数据库中已经存在该字段，请修改字段标识，或者在后面加数字区别！");
			}
			$fsql="ALTER TABLE ".$this->sqldb."_field ADD ".$this->syArgs('field_fmark',1)." ";
			switch($this->syArgs('field_ftype',1)){
				case "varchar":
					$field_arr=array_merge($field_arr,array('flength'=>$this->syArgs('varchar_flength'),'imgw'=>0,'imgh'=>0,'selects'=>''));
					$fsql.="VARCHAR(".$field_arr['flength'].") CHARACTER SET utf8 default NULL";
					break;
				case "text":
					$field_arr=array_merge($field_arr,array('flength'=>'','imgw'=>$this->syArgs('text_imgw'),'imgh'=>$this->syArgs('text_imgh'),'selects'=>''));
					$fsql.="TEXT CHARACTER SET utf8 default NULL";
					break;
				case "int":
					$field_arr['flength']=null;
					$field_arr['imgw']=null;
					$field_arr['imgh']=null;
					$field_arr['selects']=null;
					$fsql.="INT(10) DEFAULT '0' NOT NULL";
					break;
				case "money":
					$field_arr['flength']=null;
					$field_arr['imgw']=null;
					$field_arr['imgh']=null;
					$field_arr['selects']=null;
					$fsql.="DECIMAL(10,2) UNSIGNED DEFAULT '0.00' NOT NULL";
					break;
				case "date":
					$field_arr['flength']=null;
					$field_arr['imgw']=null;
					$field_arr['imgh']=null;
					$field_arr['selects']=null;
					$fsql.="INT(10) DEFAULT '0' NOT NULL";
					break;
				case "radio":
					$field_arr['flength']=null;
					$field_arr['imgw']=null;
					$field_arr['imgh']=null;
					$field_arr['selects']=$this->syArgs('field_selects',1);
					$fsql.="CHAR(30) CHARACTER SET utf8 default NULL";
					break;
				case "select":
					$field_arr['flength']=null;
					$field_arr['imgw']=null;
					$field_arr['imgh']=null;
					$field_arr['selects']=$this->syArgs('field_selects',1);
					$fsql.="CHAR(200) CHARACTER SET utf8 default NULL";
					break;
				case "file":
					$field_arr['flength']=null;
					$field_arr['imgw']=null;
					$field_arr['imgh']=null;
					$field_arr['selects']=null;
					$fsql.="CHAR(255) CHARACTER SET utf8 default NULL";
					break;
				case "multifile":
					$field_arr['flength']=null;
					$field_arr['imgw']=null;
					$field_arr['imgh']=null;
					$field_arr['selects']=null;
					$fsql.="TEXT CHARACTER SET utf8 default NULL";
					break;
			}
			if(is_array($field_arr['navigators'])){
				$navigators='|';
				for ($i=0;$i<count($field_arr['navigators']);$i++){
					$navigators.=$field_arr['navigators'][$i]."|";
				}
				$field_arr['navigators']=$navigators;
			}else {
				$field_arr['navigators']=null;
			}
			if(!$this->Class->create($field_arr) || !$this->Class->runSql($fsql)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("字段添加失败，请检查数据库！");
			}else {
				message("字段添加成功","?action=fields&c={$this->c}");
			}
		}
		$this->display("fields_add.html");
	}
	function del(){
		if(!$this->userClass->checkgo('fields_del')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$field_info=$this->Class->find(array('fid'=>$this->syArgs('fid'))); 
		$sqldel = "ALTER TABLE ".$this->sqldb."_field DROP column ".$field_info['fmark'];
		$res=$this->Class->delete(array('fid'=>$this->syArgs('fid')));
		if($res && $this->Class->runSql($sqldel) ){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message("字段删除成功");
		}else {
			message("字段删除失败，请至数据库手动删除，并删除相关频道'_field'表中同名字段!");
		}
	}
	function get_fields_info(){
		$cmark=$this->syArgs('cmark',1);
		$sqlfn="SELECT * from ".$GLOBALS['WP']['db']['prefix'].$this->table." WHERE `statu`=1 AND `cmark`='".$cmark."' AND `navigators` like '%|".$this->syArgs('nid')."|%' ORDER BY `order` DESC,`fid`";
		$field_nav_arr=$this->Class->findsql($sqlfn);
		$add_field_info=syDB($cmark."_field")->find(array('aid'=>$this->syArgs('id',1)));
		$ret['html']="";
		$ret['script']='';
		if($field_nav_arr[0]['fid']){
			foreach ($field_nav_arr as $k=>$f){ 
				switch ($f['ftype']){
					case "varchar":
						if($add_field_info['aid']){
							$fmark=$add_field_info[$f['fmark']];
						}
						$ret['html'].='<tr><td><label>'.$f['fname'].'</label></td><td><label style="width:100%;"><input class="form-control" name="'.$cmark.'_'.$f['fmark'].'" value="'.$fmark.'" /></label></td><td>字段类型：<font color="#f00">中小型文本</font>，请输入，格式如：abc。</td></tr>';
						break;
					case "int":
						if($add_field_info['aid']){
							$fmark=$add_field_info[$f['fmark']];
						}
						$ret['html'].='<tr><td><label>'.$f['fname'].'</label></td><td><label style="width:100%;"><input type="text" class="form-control" name="'.$cmark.'_'.$f['fmark'].'" value="'.$fmark.'" /></label></td><td>字段类型：<font color="#f00">整数</font>，请输入，格式如：123。</td></tr>'; 
						break;
					case "money":
						if($add_field_info['aid']){
							$fmark=$add_field_info[$f['fmark']];
						}
						$ret['html'].='<tr><td><label>'.$f['fname'].'</label></td><td><label style="width:100%;"><span class="input-group-addon" style="display:inline-block;float:left;height:34px;line-height:34px; padding:0; width:10.6%; text-align:center;">¥</span><input class="form-control" style="display:inline-block;float:left;width:89.4%;" name="'.$cmark.'_'.$f['fmark'].'" value="'.$fmark.'" /></label></td><td>字段类型：<font color="#f00">货币</font>，请输入，格式如：1.23。</td></tr>';
						break;
					case "date":
						if($add_field_info['aid']){
							$fmark=date('Y-m-d H:i:s',$add_field_info[$f['fmark']]);
						}else{
							$fmark=date('Y-m-d H:i:s',time());
						}
						$on="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})";
						$ret['html'].='<tr><td><label>'.$f['fname'].'</label></td><td><label style="width:100%;"><i class="laydate-icon"></i><input class="form-control layer-date" placeholder="YYYY-MM-DD hh:mm:ss" onclick="'.$on.'" name="'.$cmark.'_'.$f['fmark'].'" value="'.$fmark.'" /></label></td><td>字段类型：<font color="#f00">日期</font>，请输入，格式如：2015-01-01 00:00:00。</td></tr>';
						break;
					case "file":
						if($add_field_info['aid']){
							$fmark=$add_field_info[$f['fmark']];
						}
						$ret['html'].='<tr><td><label>'.$f['fname'].'</label></td><td><label><input type="text" style="width: 191px;" class="form-control ex-lipic" name="'.$cmark.'_'.$f['fmark'].'" value="'.$fmark.'" /><button type="button" class="btn btn-primary ex-button" data-toggle="modal" data-target="#myModal2"><i class="fa fa-upload"></i> 上传文件</button></label></label></td><td>字段类型：<font color="#f00">单个附件</font>，请上传附件。</td></tr>';
						break;
					case "multifile":
						if($add_field_info['aid']){
							$fmark=$add_field_info[$f['fmark']];
						}
						$ret['html'].='<tr><td><label>'.$f['fname'].'</label></td><td><label><input type="text" style="width: 191px;" class="form-control ex-lipic" name="'.$cmark.'_'.$f['fmark'].'" value="'.$fmark.'" /><button type="file" class="btn btn-primary ex-button" data-toggle="modal" data-target="#myModal2"><i class="fa fa-upload"></i> 上传文件</button></label></label></td><td>字段类型：<font color="#f00">多个附件</font>，请上传附件。</td></tr>';
						break;
					case "select":
						$ret['html'].='<tr><td><label>'.$f['fname'].'</label></td><td><label style="width:100%;"><select class="ex-select" data-placeholder="添加/更改要求" multiple="" name="'.$cmark.'_'.$f['fmark'].'[]" tabindex="2" style="width: 100%;">';
						$ses=explode(",", $f['selects']);
						foreach ($ses as $n){
							$p=explode("=", $n);
							$ret['html'].='<option hassubinfo="true" value="'.$p[1].'">'.$p[0].'</option>';
						}
						$ret['html'].='</select></label></td><td>字段类型：<font color="#f00">多选菜单</font>，请选择。</td></tr>';
						break;
					case "radio":
						$ret['html'].='<tr><td><label>'.$f['fname'].'</label></td><td><label style="width:100%;"><select class="ex-select" name="'.$cmark.'_'.$f['fmark'].'" tabindex="2" style="width: 100%;">';
						$ses=explode(",", $f['selects']);
						foreach ($ses as $n){
							$p=explode("=", $n);
							if($add_field_info[$f['fmark']]==$p[1]){ 
								$ret['html'].='<option hassubinfo="true" value="'.$p[1].'" selected="selected">'.$p[0].'</option>';
							}else {
								$ret['html'].='<option hassubinfo="true" value="'.$p[1].'">'.$p[0].'</option>';
							}
						}
						$ret['html'].='</select></label></td><td>字段类型：<font color="#f00">单选菜单</font>，请选择。</td></tr>';
						break;
					case "text":
 						$ret['html'].='<tr><td><label>'.$f['fname'].'</label></td><td colspan="2"><textarea style="width:670px;height:400px;" name="'.$cmark.'_'.$f['fmark'].'" class="kindeditor">'.code_body($add_field_info[$f['fmark']],0).'</textarea></td></tr>';
						$ret['script']='var editor;KindEditor.ready(function(K){ editor=K.create(".kindeditor",{ cssPath : ["system/js/prettify.css"],fileManagerJson : "?action=uploads&o=editorupload&filesdir='.$cmark.'",allowFileManager:true,filePostName : "editor_KindEditor",filterMode:false,uploadJson:""});});';
						break;
				}
			}
		}else {
			$ret['html'].='<tr></tr>';
		}
		echo syClass('syjson')->encode($ret);
		exit();
	}			
}	