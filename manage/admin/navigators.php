<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class navigators extends syController{
	public $pk = "nid";
	public $pkn = "ngid";
	public $table = "navigators";
	function __construct(){
		parent::__construct();
		$this->nid=$this->syArgs('nid');
		$this->ngid=$this->syArgs('ngid');
		$this->a='navigators';
		$this->Class=syClass('c_navigators');
		$navigators=$this->Class->findAll(null,' `order` DESC,`nid` ','`nid`,`nname`,`ngid`,`cmark`');
		$this->ngid_info=$this->Class->find(array('nid'=>$this->ngid));
		$this->Classtype=syClass('synavigators',array($navigators));
		$this->navtree=$this->Classtype->type_txt();
		foreach ($this->navtree as $n){
			if($n['n']==0){
				$mainnav[]=$n;
			}
			if($n['n']==1){
				$secnav[]=$n;
			}
			if($n['n']==2){
				$trdnav[]=$n;
			}
		}
		$this->mainnav=$mainnav;
		$this->secnav=$secnav;
		$this->trdnav=$trdnav;
		$imgtypes_arr=array('jpg','jpeg','png','gif','bmp');
		for ($i=0;$i<count($imgtypes_arr);$i++){
			if(in_array($imgtypes_arr[$i],explode(",", $GLOBALS['WP']['ext']['filetype']))){
				$nav_type[]="*.".$imgtypes_arr[$i];
			}
		}
		$this->nav_type=join(";",$nav_type);
		$this->nav_typename='图片';
		$this->o=$this->syArgs('o',1);
		if(($this->o=='add'||$this->o=='edit') && $this->syArgs('go')==1){
			$this->nav_arr=array(
				'nname'=>$this->syArgs('nav_nname',1),
				'order'=>$this->syArgs('nav_order'),
				'cmark'=>$this->syArgs('nav_mark',1),
				'ngid'=>$this->syArgs('nav_ngid'),
				'description'=>$this->syArgs('nav_description',1),
				'isindex'=>$this->syArgs('nav_isindex'),
				'statu'=>$this->syArgs('nav_statu'),
				'detail'=>code_body($this->syArgs('nav_detail',4)),
				'gourl'=>$this->syArgs('nav_gourl',1),
			    'msubmit'=>$this->syArgs('nav_msubmit'),
				'mrank'=>$this->syArgs('nav_mrank'),
				'lipic'=>$this->syArgs('nav_lipic',1),
				'seo_title'=>$this->syArgs('nav_seo_title',1),
				'seo_keywords'=>$this->syArgs('nav_seo_keywords',1),
				'listno'=>$this->syArgs('nav_listno'),
				'htmldir'=>$this->syArgs('nav_htmldir',1),
				'htmlfile'=>$this->syArgs('nav_htmlfile',1),
				'imgw'=>$this->syArgs('nav_imgw'),
				'imgh'=>$this->syArgs('nav_imgh'),
				'ct_list'=>$this->syArgs('nav_ct_list',1),
				'ct_listimg'=>$this->syArgs('nav_ct_listimg',1),
				'ct_listbody'=>$this->syArgs('nav_ct_listbody',1),
				'ct_content'=>$this->syArgs('nav_ct_content',1)
			);
			if($this->syArgs('nav_seo_title',1)==''){
				$this->nav_arr=array_merge($this->nav_arr,array('seo_title'=>$this->syArgs('nav_nname',1)));
			}
			if($this->syArgs('nav_mrank')==2){
				$pass=syPass($this->syArgs('nav_password',1),'INCODE');
				$this->nav_arr=array_merge($this->nav_arr,array('password'=>$pass));
			}else{
			    $this->nav_arr=array_merge($this->nav_arr,array('password'=>''));
			}
		}
		$this->sqldb=$GLOBALS['WP']['db']['prefix'].$this->table;
		
	}
	function index(){
		$this->main_nav=$this->Class->findAll(array('ngid'=>0),' `order` DESC,`nid` ');
		$this->display('navigators.html');
	}
	function edit(){
		if(!$this->userClass->checkgo('navigators_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->navinfo=$this->Class->find(array('nid'=>$this->nid));
		if($this->syArgs('go')==1){
			deleteDir($GLOBALS['WP']['sp_cache']);
			if($this->syArgs('nav_all_temp',1)){
				$nid_arr=$this->Class->findAll(array('ngid'=>$this->nid),null,'`nid`');
				foreach ($nid_arr as $n){
					$nids_arr[]=$n['nid'];
				}
				$nids=join(",", $nids_arr);
				$temp_arr=array(
					'ct_list'=>$this->syArgs('nav_ct_list',1),
					'ct_listimg'=>$this->syArgs('nav_ct_listimg',1),
					'ct_listbody'=>$this->syArgs('nav_ct_listbody',1),
					'ct_content'=>$this->syArgs('nav_ct_content',1)
				);
				$where_nid_arr="`nid` IN (".$nids.")";
				if($this->Class->update(array('nid'=>$this->nid),$this->nav_arr) && $this->Class->update($where_nid_arr,$temp_arr)){
					message("栏目修改成功且一键覆盖下级栏目模板设置成功");
				}else{
					message("操作失败");
				}
			}else {
				if($this->Class->update(array('nid'=>$this->nid),$this->nav_arr)){
					message("栏目修改成功");
				}else{
					message("栏目修改失败");
				}
			}
		}
		$this->display('navigators_edit.html');
	}
	function add(){
		if(!$this->userClass->checkgo('navigators_add')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->ncmark=$this->syArgs('cmark',1);
		$this->nngid=$this->syArgs('ngid');
		if($this->syArgs('go')==1){
			deleteDir($GLOBALS['WP']['sp_cache']);
			$new_temp_arr=syDB("channels")->find(array('cmark'=>$this->syArgs('nav_mark',1)),null,'`ct_list`,`ct_listimg`,`ct_listbody`,`ct_content`');
			if($this->syArgs('nav_ct_list',1)==''){
				if($this->syArgs('nav_mark',1)!=$this->ngid_info['cmark']){ //添加子栏目时改变频道，继承频道模板
					$this->nav_arr=array_merge($this->nav_arr,array('ct_list'=>$new_temp_arr['ct_list']));
				}else{  //快捷添加子栏目，继承上级栏目模板
					$this->nav_arr=array_merge($this->nav_arr,array('ct_list'=>$this->ngid_info['ct_list']));
				}
			}
			if($this->syArgs('nav_ct_listimg',1)==''){
				if($this->syArgs('nav_mark',1)!=$this->ngid_info['cmark']){ //添加子栏目时改变频道，继承频道模板
					$this->nav_arr=array_merge($this->nav_arr,array('ct_listimg'=>$new_temp_arr['ct_listimg']));
				}else{  //快捷添加子栏目，继承上级栏目模板
					$this->nav_arr=array_merge($this->nav_arr,array('ct_listimg'=>$this->ngid_info['ct_listimg']));
				}
			}
			if($this->syArgs('nav_ct_listbody',1)==''){
				if($this->syArgs('nav_mark',1)!=$this->ngid_info['cmark']){ //添加子栏目时改变频道，继承频道模板
					$this->nav_arr=array_merge($this->nav_arr,array('ct_listbody'=>$new_temp_arr['ct_listbody']));
				}else{  //快捷添加子栏目，继承上级栏目模板
					$this->nav_arr=array_merge($this->nav_arr,array('ct_listbody'=>$this->ngid_info['ct_listbody']));
				}
			}
			if($this->syArgs('nav_ct_content',1)==''){
				if($this->syArgs('nav_mark',1)!=$this->ngid_info['cmark']){ //添加子栏目时改变频道，继承频道模板
					$this->nav_arr=array_merge($this->nav_arr,array('ct_content'=>$new_temp_arr['ct_content']));
				}else{  //快捷添加子栏目，继承上级栏目模板
					$this->nav_arr=array_merge($this->nav_arr,array('ct_content'=>$this->ngid_info['ct_content']));
				}
			}
			if($this->syArgs('nav_seo_title',1)==''){
				$this->nav_arr=array_merge($this->nav_arr,array('seo_title'=>$this->syArgs('nav_nname',1)));
			}
			if($this->Class->create($this->nav_arr)){
				message_c('栏目添加成功','?action=navigators','?action=navigators&o=add&cmark='.$this->nav_arr['cmark'].'&ngid='.$this->nav_arr['ngid']);
			}else {
				message('栏目添加失败，请重新添加...','?action=navigators');
			}
		}
		$this->display('navigators_add.html');
	}
	function del(){
		if(!$this->userClass->checkgo('navigators_del')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$info=$this->Class->find(array('nid'=>$this->nid));
		$nids=$this->Classtype->leafid($this->nid);
		foreach (explode(",", $nids) as $r){
			$types=$this->Class->find(array('nid'=>$r),null,'`nid`,`cmark`');
			$db=$GLOBALS['WP']['db']['prefix'].$types['cmark'];
			syDB($types['cmark'])->findSql('DELETE '.$db.','.$db.'_field FROM '.$db.','.$db.'_field WHERE '.$db.'.nid='.$r.' and '.$db.'.id='.$db.'_field.aid');
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
		if($this->Class->delete(' nid IN('.$nids.') ')){
			syAccess('c', 'navigators');
			syAccess('w', 'navigators',syDB('navigators')->findAll(null,null,'`nid`,`nname`,`ngid`,`cmark`'));
			message('删除栏目成功，同时子栏目及栏目下所有已发布的内容全部删除成功','?action=navigators');
		}else{
			message('删除栏目失败，请至数据库手动删除，并同时清空其子栏目及栏目下所有已发布内容','?action=navigators');
		}
		$this->display('navigators.html');
	}
	function alledit(){
		if(!$this->userClass->checkgo('navigators_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$formnum=$this->syArgs('formnum');
		$types_arr=$this->syArgs('types',2);
		$this->operate_allNav($formnum,$types_arr);
		deleteDir($GLOBALS['WP']['sp_cache']);
	}
	function operate_allNav($formnum,$types_arr){
		$nid_str=join(", ", $types_arr);
		$where_nid_str = "`nid` IN (".$nid_str.")";
		switch ($formnum){
			case 1:
				if(!$types_arr){
					message('您尚未选择任何栏目...','?action=navigators',0,0);
				}
				$row['statu']=1;
				if($this->Class->update($where_nid_str,$row)){
					message('批量显示成功','?action=navigators');
				}else {
					message('批量显示失败...','?action=navigators');
				}
				break;
			case 2:
				if(!$types_arr){
					message('您尚未选择任何栏目...','?action=navigators',0,0);
				}
				$row['statu']=0;
				if($this->Class->update($where_nid_str,$row)){
					message('批量隐藏成功','?action=navigators');
				}else {
					message('批量隐藏失败...','?action=navigators');
				}
				break;
			case 3:
				$orders=$this->syArgs('orders',2);
				foreach ($orders as $nid => $order){
					$condition_id=$this->pk."=".$nid;
					$row_nid['order']=$order;
					if(!$this->Class->update($condition_id,$row_nid)){
						message("栏目[".$condition_id."]顺序更改失败...",'?action=navigators');
					}
				}
				message('栏目顺序更改成功','?action=navigators');
				break;
			case 4:
				if(!$types_arr){
					message('您尚未选择任何栏目...','?action=navigators',0,0);
				}
				if($this->Class->delete($where_nid_str)){
					message('批量删除栏目成功','?action=navigators');
				}else {
					message('批量删除栏目失败,请至数据库手动删除...','?action=navigators');
				}
				break;
			case 5:
				if(!$types_arr){
					message('您尚未选择任何栏目...','?action=navigators',0,0);
				}
				foreach ($types_arr as $nid){
					if($this->syArgs('nnid')==$nid){
						message("您所选择的栏目包含<font color='#F00'>栏目本身</font>,无法操作...<br/>请重新选择栏目",'?action=navigators',3,0);
					}
				}
				if(!$this->syArgs('ncid',1) && !$this->syArgs('nnid',1)){
					message('请选择更改项目','?action=navigators',0,0);
				}
				if($this->syArgs('ncid',1)){
					$templ_arr=syDB("channels")->find(array('cmark'=>$this->syArgs('ncid',1)),null,'`ct_list`,`ct_listimg`,`ct_listbody`,`ct_content`',1);
					$row_cmark=array_merge(array('cmark'=>$this->syArgs('ncid',1)),$templ_arr);
					$rescmark=$this->Class->update($where_nid_str,$row_cmark);
				}
				if($this->syArgs('nnid',1)){
					if($this->syArgs('nnid',1)=='topnav'){
						$row_nid['ngid']=0;
					}else {
						$row_nid['ngid']=intval($this->syArgs('nnid',1));
					}
					$resnid=$this->Class->update($where_nid_str,$row_nid);
				}
				if($rescmark && !$resnid){
					message('批量更改所属频道成功','?action=navigators');
				}elseif(!$rescmark && $resnid){
					message('批量更改所属栏目成功','?action=navigators');
				}elseif($rescmark && $resnid){
					message('批量更改所属频道及所属栏目成功','?action=navigators');
				}else{
					message('批量操作失败...','?action=navigators');
				}
				break;
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
	}
}	