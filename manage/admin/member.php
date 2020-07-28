<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class member extends syController{
	public $pk = "uid";
	public $pkn = "gid";
	public $table = "member";
	public $gtable= "member_group";
	function __construct(){
		parent::__construct();
		$this->gopage=$this->syArgs('page',0,1);
		$this->uid=$this->syArgs('uid');
		$this->a='member';
		$this->Class=syClass('c_member');
		$this->ClassC=syClass('c_member_contact');
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
		$this->conditions=array(
			'gid' => $this->syArgs('member-schbygid'),
			'sexuality' => $this->syArgs('member-schbysexuality'),
			'nickname' => $this->syArgs('nickname',1)
		);
		$lists=$this->get_lists($this->conditions);
		$this->lists=$lists[0];
		$this->pages=$lists[1];
		$this->group=syDB($this->gtable)->findAll(array('sys'=>1));
		$this->admingroup=syDB("admin_group")->findAll();
		$this->field=syDB("member_field")->find(array('uid'=>$this->uid));
		$this->fields=syDB('fields')->findAll(array('cmark'=>'member','statu'=>1)," `order` DESC,`fid`");
		$field_input=array();
		foreach ($this->fields as $f){
			switch ($f['ftype']){
				case "varchar":
					if($f['flength']!=0 && $f['flength']!=255 ){
						$length=$f['flength']."px";
					}else {
						$length="100%";
					}
					$input='<input class="form-control" style="width:'.$length.'" type="text" value="'.$this->field[$f['fmark']].'" name="member_'.$f['fmark'].'" />';
					$field_input[$f['fname']]['input']=$input;
					break;
				case "int":
					$input='<input type="text" class="form-control" style="width:100%" type="text" value="'.$this->field[$f['fmark']].'" name="member_'.$f['fmark'].'" />';
					$field_input[$f['fname']]['input']=$input;
					break;
				case "money":
					$input='<span class="input-group-addon" style="display:inline-block;float:left;height:34px;line-height:34px; padding:0; width:10.6%; text-align:center;">¥</span><input type="text" class="form-control" style="display:inline-block;float:left;width:89.4%;" name="member_'.$f['fmark'].'" value="'.$this->field[$f['fmark']].'" />';
					$field_input[$f['fname']]['input']=$input;
					break;
				case "date":
					$input='<input class="form-control layer-date" type="text" name="member_'.$f['fmark'].'" value="'.date('Y-m-d H:i:s',$this->field[$f['fmark']]).'" />';
					$field_input[$f['fname']]['input']=$input;
					break;
				case "file";
				$input='<input type="file" style="width: 191px;" class="form-control ex-lipic" name="member_'.$f['fmark'].'" value="'.$this->field[$f['fmark']].'" />';
				$field_input[$f['fname']]['input']=$input;
				break;
				case "multifile":
					$input='<input type="file" style="width: 191px;" class="form-control ex-lipic" name="member_'.$f['fmark'].'" value="'.$this->field[$f['fmark']].'" multiple="multiple" />';
					$field_input[$f['fname']]['input']=$input;
					break;
				case "select":
					$input='<select class="chosen-select" data-placeholder="添加/更改要求" multiple="" name="member_'.$f['fmark'].'" tabindex="2" style="width: 100%;">';
					foreach (explode(",", $f['selects']) as $n){
						$p=explode("=", $n);
						if($this->field[$f['fmark']]==$p[1]){
							$input.='<option hassubinfo="true" value="'.$p[1].'" selected="selected">'.$p[0].'</option>';
						}else {
							$input.='<option hassubinfo="true" value="<?php echo $p[1]; ?>"><?php echo $p[0]; ?></option>';
						}
					}
					$input.='</select>';
					$field_input[$f['fname']]['input']=$input;
					break;
				case "radio":
					$input='<select class="chosen-select" name="member_'.$f['fmark'].'" tabindex="2" style="width: 100%;">';
					foreach (explode(",", $f['selects']) as $n){
						$p=explode("=", $n);
						if($this->field[$f['fmark']]==$p[1]){
							$input.='<option hassubinfo="true" value="'.$p[1].'" selected="selected">'.$p[0].'</option>';
						}else {
							$input.='<option hassubinfo="true" value="'.$p[1].'">'.$p[0].'</option>';
						}
					}
					$input.='</select>';
					$field_input[$f['fname']]['input']=$input;
					break;
				case "text":
					$input='<textarea style="width:'.$f['imgw'].'px; height:'.$f['imgh'].'px" name="member_'.$f['fmark'].'" class="summernote">'.code_body($this->field[$f['fmark']],0).'</textarea>';
					$field_input[$f['fname']]['input']=$input;
					break;
			}
			$field_input[$f['fname']]['group']=trim($f['navigators'],"|");
		}
		$this->field_input=$field_input;
		//省级地区列表
		$this->province=syDB("district")->findAll(array('level'=>1));
		if(($this->syArgs('o',1)=='add'||$this->syArgs('o',1)=='info') && $this->syArgs('go')==1){
			$this->member_arr=array(
				'username'=>$this->syArgs('member_username',1),
				'gid'=>$this->syArgs('member_group'),
				'sexuality'=>$this->syArgs('member_sexuality',1),
				'money'=>$this->syArgs('member_money',3),
				'credit'=>$this->syArgs('member_credit'),
				'nickname'=>$this->syArgs('member_nickname',1),
				'portrait' =>	$this->syArgs('member_portrait',1),
			);
			if($this->syArgs('member_portrait_del',1)){
				$this->member_arr=array_merge($this->member_arr,array('portrait'=>$GLOBALS['WP']['member']['default_portrait']));
			}
			if($this->syArgs('member_password',1)!=''){
				$this->member_arr=array_merge($this->member_arr,array('password'=>md5(md5($this->syArgs('member_password',1)).$this->syArgs('member_username',1))));
			}
			$this->member_contact_arr=array(
				'realname'=>$this->syArgs('member_realname',1),
				'email'=>$this->syArgs('member_email',1),
				'telephone'=>$this->syArgs('member_telephone',1),
				'mobile'=>$this->syArgs('member_mobile',1),
				'resideprovince'=>$this->syArgs('member_resideprovince'),
				'residecity'=>$this->syArgs('member_residecity'),
				'residedist'=>$this->syArgs('member_residedist'),
				'address'=>$this->syArgs('member_address',1),
				'zipcode'=>$this->syArgs('member_zipcode',1),
				'birthyear'=>$this->syArgs('member_birthyear'),
				'birthmonth'=>$this->syArgs('member_birthmonth'),
				'birthday'=>$this->syArgs('member_birthday'),
				'company'=>$this->syArgs('member_company',1)
			);
			$resideprovince_name=syDB("district")->find(array('id'=>$this->syArgs('member_resideprovince')));
			$residecity_name=syDB("district")->find(array('id'=>$this->syArgs('member_residecity')));
			$residedist_name=syDB("district")->find(array('id'=>$this->syArgs('member_residedist')));
			$this->member_contact_arr=array_merge($this->member_contact_arr,array('resideprovince'=>$resideprovince_name['name']));
			$this->member_contact_arr=array_merge($this->member_contact_arr,array('residecity'=>$residecity_name['name']));
			$this->member_contact_arr=array_merge($this->member_contact_arr,array('residedist'=>$residedist_name['name']));
			$this->field_row=array(
				'introduction'=>code_body($this->syArgs('member_introduction',4))
			);
			$member_field_arr=syClass("c_fields")->findAll(array('cmark'=>'member','statu'=>1));
			foreach ($member_field_arr as $v){
				$ns='';$n=array();
				if($v['ftype']=='varchar' || $v['ftype']=='file' || $v['ftype']=='radio'){ $ns=$this->syArgs('member_'.$v['fmark'],1);}
				if($v['ftype']=='int'){ $ns=$this->syArgs('member_'.$v['fmark']);}
				if($v['ftype']=='money'){ $ns=$this->syArgs('member_'.$v['fmark'],3);}
				if($v['ftype']=='text'){ $ns=$this->syArgs('member_'.$v['fmark'],4);}
				if($v['ftype']=='date'){ $ns=strtotime($this->syArgs('member_'.$v['fmark'],1));}
				if($v['ftype']=='multifile'){
					$files=$this->syArgs('member_'.$v['fmark'].'file',2);
					if($files){
						$num=$this->syArgs('member_'.$v['fmark'].'num',2);
						$txt=$this->syArgs('member_'.$v['fmark'].'txt',2);$ns='';
						natsort($num);
						foreach($num as $k=>$v){
							$ns.=$files[$k].'|'.$txt[$k];
						}
						$ns=substr($ns,3);
					}
				}
				if($v['ftype']=='select'){if($this->syArgs('member_'.$v['fmark'],2)){$ns='|'.implode('|',$this->syArgs('member_'.$v['fmark'],2)).'|';}else{$ns='';}}
				$n=array($v['fmark']=> $ns);
				$this->field_row=array_merge($this->field_row,$n);
			}
		}
	}
	function index(){
		$this->display("member.html");
	}
	function info(){
		if(!$this->userClass->checkgo('member_info')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->member=$this->Class->find(array('uid'=>$this->uid));
		$member_article_num=syDB("article")->findCount(array('user'=>$this->member['username']));
		$this->member=array_merge($this->member,array('total_article'=>$member_article_num));
		$this->contactinfo=syDB($this->table."_contact")->find(array('uid'=>$this->uid));
		//该会员所在省信息
		$this->province_info=syDB("district")->find(array('name'=>$this->contactinfo['resideprovince']));
		//所在省下级市级地区列表
		$this->city_lists=syDB("district")->findAll(array('upid'=>$this->province_info['id']));
		//该会员所在市信息
		$this->city_info=syDB("district")->find(array('name'=>$this->contactinfo['residecity']));
		//所在市下级区/乡镇级地区列表
		$this->dist_lists=syDB("district")->findAll(array('upid'=>$this->city_info['id']));
		if($this->member['adminid']==1){
			$adminuser_arr=array(
				'auser'=>$this->member_arr['username'],
				'aemail'=>$this->member_contact_arr['email'],
				'aname'=>$this->member_contact_arr['realname'],
				'avator'=> $this->member_arr['portrait']
			);
			if($this->member_arr['password']!=''){
				$adminuser_arr=array_merge($adminuser_arr,array('apass'=>$this->member_arr['password']));
			}
		}
		if($this->syArgs('go')==1){
		    if($this->member_arr['username']!=$this->member['username']){
    		    if($this->Class->find(array('username'=>$this->member_arr['username']))){
    		        message("修改失败<br/>可能原因是：您输入的用户名与其他用户名重复，请重新输入",null,3,2);
    		    }
		    }
			if($this->member['adminid']==1){ //修改的会员是管理员
				if($this->Class->update(array('uid'=>$this->uid),$this->member_arr) && $this->ClassC->update(array('uid'=>$this->uid),$this->member_contact_arr) && syDB("member_field")->update(array('uid'=>$this->uid),$this->field_row) && syDB('admin')->update(array('uid'=>$this->uid),$adminuser_arr)){
					deleteDir($GLOBALS['WP']['sp_cache']);
					message('会员信息修改成功','?action=member');
				}else {
					message('会员信息修改失败','?action=member');
				}
			}else {
				if($this->Class->update(array('uid'=>$this->uid),$this->member_arr) && $this->ClassC->update(array('uid'=>$this->uid),$this->member_contact_arr) && syDB("member_field")->update(array('uid'=>$this->uid),$this->field_row)){
					deleteDir($GLOBALS['WP']['sp_cache']);
					message('会员信息修改成功','?action=member');
				}else {
					message('会员信息修改失败','?action=member');
				}
			}
		}
		$this->display("member_info.html");
	}
	function add(){
		if(!$this->userClass->checkgo('member_add')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->city_beijing=syDB("district")->findAll(array('level'=>2,'upid'=>1));
		$this->dist_dongcheng =syDB("district")->findAll(array('level'=>3,'upid'=>$this->city_beijing[0]['id']));
		if($this->syArgs('go')==1){ //验证用户名
			if($this->Class->find(array('username'=>$this->member_arr['username'])) || syDB("admin")->find(array('auser'=>$this->member_arr['username']))){
				message('该用户名已存在，请重新输入','?action=member&o=add');
			}
			if($resuid=$this->Class->create($this->member_arr)){
				$this->member_contact_arr=array_merge($this->member_contact_arr,array('uid'=>$resuid));
				$this->field_row=array_merge($this->field_row,array('uid'=>$resuid));
				if($this->ClassC->create($this->member_contact_arr) && syDB("member_field")->create($this->field_row)){ //添加成功
					deleteDir($GLOBALS['WP']['sp_cache']);
					message('会员（用户名： '.$this->member_arr['username'].'）添加成功','?action=member');
				}else { //添加失败，删除会员表信息
					$this->Class->delete(array('uid'=>$resuid));
					message('会员（用户名： '.$this->member_arr['username'].'）附表信息加入失败，会员添加失败，请重新添加','?action=member');
				}
			}else {
				message('会员（用户名： '.$this->member_arr['username'].'）添加失败，请重新添加','?action=member');
			}
		}
		$this->display("member_add.html");
	}
	function del(){
		if(!$this->userClass->checkgo('member_del')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$memberinfo=$this->Class->find(array('uid'=>$this->uid));
		if($this->Class->delete(array('uid'=>$this->uid)) && $this->ClassC->delete(array('uid'=>$this->uid)) && syDB("member_field")->delete(array('uid'=>$this->uid))){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message('会员删除成功，且该会员的所有相关数据删除成功（待丰富）','?action=member');
		}else{
			message('会员删除失败','?action=member');
		}
	}
	function group(){
		if(!$this->userClass->checkgo('member_group')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->lists=syDB($this->gtable)->findAll();
		$this->op=$this->syArgs('op',1);
		$gid=$this->syArgs('gid');
		$group_info=syDB($this->gtable)->find(array('gid'=>$gid));
		switch ($this->op){
			case "edit":
				if($this->syArgs('go')==1){
					$membergroup_arr=array(
						'name'=>$this->syArgs('membergroup_name'.$gid,1),
						'weight'=>$this->syArgs('membergroup_weight'.$gid),
						'audit'=>$this->syArgs('membergroup_audit'.$gid),
						'submit'=>$this->syArgs('membergroup_submit'.$gid),
						'filetype'=>$this->syArgs('membergroup_filetype'.$gid,1),
						'filesize'=>$this->syArgs('membergroup_filesize'.$gid),
						'fileallsize'=>$this->syArgs('membergroup_fileallsize'.$gid),
						'discount_type'=>$this->syArgs('membergroup_discount_type'.$gid),
						'discount'=>$this->syArgs('membergroup_discount'.$gid,3)
					);
					if($this->syArgs('membergroup_discount_type'.$gid)==0){
						$membergroup_arr=array_merge($membergroup_arr,array('discount_type'=>0,'discount'=>0.00));
					}
					if(syDB($this->gtable)->update(array('gid'=>$gid),$membergroup_arr)){
						message("会员分组[<font color='#1AB394'>".$group_info['name']."</font>]修改成功","?action=member&o=group");
					}else {
						message("会员分组[<font color='#1AB394'>".$group_info['name']."</font>]修改失败","?action=member&o=group");
					}
				}
				break;
			case "add":
				if($this->syArgs('go')==1){
					if(syDB($this->gtable)->find(array('name'=>$this->syArgs('membergroup_name',1)))){
						message("对不起，该会员分组已存在","?action=member&o=group&op=add");
					}
					$membergroup_add_arr=array(
						'name'=>$this->syArgs('membergroup_name',1),
						'weight'=>$this->syArgs('membergroup_weight'),
						'audit'=>$this->syArgs('membergroup_audit'),
						'submit'=>$this->syArgs('membergroup_submit'),
						'filetype'=>$this->syArgs('membergroup_filetype',1),
						'filesize'=>$this->syArgs('membergroup_filesize'),
						'fileallsize'=>$this->syArgs('membergroup_fileallsize'),
						'discount_type'=>$this->syArgs('membergroup_discount_type'),
						'discount'=>$this->syArgs('membergroup_discount',3)
					);
					if($this->syArgs('membergroup_filetype',1)==''){
						$membergroup_add_arr=array_merge($membergroup_add_arr,array('filetype'=>$GLOBALS['WP']['ext']['filetype']));
					}
					if($this->syArgs('membergroup_filesize')==0){
						$membergroup_add_arr=array_merge($membergroup_add_arr,array('filesize'=>$GLOBALS['WP']['ext']['filesize']));
					}
					if($this->syArgs('membergroup_fileallsize')==0){
						$membergroup_add_arr=array_merge($membergroup_add_arr,array('fileallsize'=>$GLOBALS['WP']['ext']['filesize']*2.5));
					}
					if($this->syArgs('membergroup_discount_type')==0){
						$membergroup_add_arr=array_merge($membergroup_add_arr,array('discount_type'=>0,'discount'=>0.00));
					}
					if(syDB($this->gtable)->create($membergroup_add_arr)){
						message("会员分组添加成功","?action=member&o=group");
					}else {
						message("会员分组添加失败","?action=member&o=group");
					}
				}
				break;
			case "del":
				$members=$this->Class->findAll(array('gid'=>$gid),null,'`uid`');
				if($members){
					message("该分组下尚有会员，无法删除该会员分组");
				}else {
					if(syDB($this->gtable)->delete(array('gid'=>$gid))){
						message("会员分组删除成功","?action=member&o=group");
					}else {
						message("会员分组删除失败，请至数据库手动删除，并保证分组下无任何会员","?action=member&o=group");
					}
				}
				break;
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
		$this->display("member_group.html");
	}
	function alledit(){
		if(!$this->userClass->checkgo('member_info')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$formnum=$this->syArgs('formnum');
		$types_arr=$this->syArgs('types',2);
		$this->operate_allMember($formnum,$types_arr);
	}
	function get_lists($conditions){
		if($conditions['gid']!=0){
			$condition.="and `gid`=".$conditions['gid']." and `adminid`=0 ";
		}
		if($conditions['sexuality']==1){
			$condition.="and `sexuality`=1 and `adminid`=0 ";
		}
		if($conditions['sexuality']==2){
			$condition.="and `sexuality`=2 and `adminid`=0 ";
		}
		if($conditions['nickname']!=''){
			$condition.="and `nickname` like '%".$conditions['nickname']."%' and `adminid`=0 ";
		}
		if($condition!=''){$condition=' where '.substr($condition,3);}
		$sql='select * from '.$this->sqldb.$condition.' order by `nickname` desc,`uid` desc';
		//获取文章总数
		$total_count=total_count($this->sqldb.$condition);
		//进行分页输出
		$this->lists = $this->Class->syPager($this->gopage,9,$total_count)->findSql($sql);
		$this->pages = pagetxt($this->Class->syPager()->getPager());
		return array($this->lists,$this->pages);
	}
	function operate_allMember($formnum,$types_arr){
		$uid_str=join(", ", $types_arr);
		$where_uid_str = "`uid` IN (".$uid_str.")";
		switch ($formnum){
			case 1:
				if(!$types_arr){
					message('您尚未选择任何会员...','?action=member',0,0);
				}
				if($this->Class->delete($where_uid_str)){
					message('批量删除成功','?action=member');
				}else {
					message('批量删除失败...','?action=member');
				}
				break;
			case 5:
				if(!$types_arr){
					message('您尚未选择任何会员...','?action=member',0,0);
				}
				if($gid=$this->syArgs('anid')){
					$row_nid['gid']=$gid;
					$resnid=$this->Class->update($where_id_str,$row_nid);
				}
				if($resnid){
					message("批量更改栏目成功",'?action=member');
				}else {
					message("批量操作失败...",'?action=member');
				}
				break;
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
	}
}	