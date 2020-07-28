<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class links extends syController{
	public $pk = "lid";
	public $table = "links";
	public $tabletype = "linkstype";
	function __construct(){
		parent::__construct();
		if(!$this->userClass->checkgo('website_links')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->a='website';
		$this->lid=$this->syArgs('lid');
		$this->gid=$this->syArgs('gid');
		$this->Class=syClass('c_links');
		$this->sqldb=$GLOBALS['WP']['db']['prefix'].'links';
		$imgtypes_arr=array('jpg','jpeg','png','gif','bmp');
		for ($i=0;$i<count($imgtypes_arr);$i++){
			if(in_array($imgtypes_arr[$i],explode(",", $GLOBALS['WP']['ext']['filetype']))){
				$links_type[]="*.".$imgtypes_arr[$i];
			}
		}
		$this->links_type=join(";",$links_type);
		$this->links_typename='图片';
		$this->conditions=array(
			'gid' => $this->syArgs('links-schbygid'),
			'statu' => $this->syArgs('links-schbystatu'),
			'name' =>	$this->syArgs('name',1)
		);
		$this->o=$this->syArgs('o',1);
		if(($this->o=='add'||$this->o=='edit') && $this->syArgs('go')==1){
			$this->links_arr=array(
				'gid'=>$this->syArgs('links_gid'),
				'name'=>$this->syArgs('links_name',1),
				'order'=>$this->syArgs('links_order'),
				'url'=>$this->syArgs('links_url',1),
				'statu'=>$this->syArgs('links_statu'),
				'lipic'=>$this->syArgs('links_lipic',1)
			);
			//$this->linkstype=syDB('linkstype')->find(array('gid'=>$this->syArgs('ads_taid')));
		}
		if(($this->o=='tadd'||$this->o=='tedit') && $this->syArgs('go')==1){
			$this->linkstype_arr=array(
				'name'=>$this->syArgs('linkstype_name'.$this->gid,1)
			);
		}
		$this->lists=$this->get_lists($this->conditions);
		$this->Classtype=syClass('c_linkstype');
		$this->linkstype=syDB('linkstype')->findAll();
	}
	function index(){
		$this->display("links.html");
	}
	function edit(){
		$this->links=$this->Class->find(array('lid'=>$this->lid));
		if($this->syArgs('go')==1){
			if($this->Class->update(array('lid'=>$this->lid),$this->links_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message('友链修改成功','?action=links');
			}else {
				message('友链修改失败','?action=links');
			}
		}
		$this->display("links_edit.html");
	}
	function add(){
		if($this->syArgs('go')==1){
			if($this->Class->create($this->links_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message('友链添加成功','?action=links');
			}else {
				message('友链添加失败','?action=links');
			}
		}
		$this->display("links_edit.html");
	}
	function del(){
		echo $this->lid;
		if($this->Class->delete(array('lid'=>$this->lid))){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message('删除友链[lid='.$this->lid.']成功','?action=links');
		}else {
			message("删除友链失败，请至数据库手动删除...",'?action=links');
		}
	}
	function get_lists($conditions){
		if($conditions['gid']!=''){
			$condition.="and `gid`=".$conditions['gid']." ";
		}
		if($conditions['statu']==1){
			$this->top_txt='<font color="#23C6C8">显示</font>的';
			$condition.="and `statu`=1 ";
		}
		if($conditions['statu']==2){
			$this->top_txt='<font color="#F00">隐藏</font>的';
			$condition.="and `statu`=0 ";
		}
		if($conditions['name']!=''){
			$condition.="and `name` like '%".$conditions['name']."%' ";
		}
		if($condition!=''){$condition=' where '.substr($condition,3);}
		$sql='select * from '.$this->sqldb.$condition.' order by `order` desc,`lid` desc';
		$this->lists = $this->Class->findSql($sql);
		return $this->lists;
	}
	function tedit(){
		if($this->syArgs('go')==1){
			if($this->Classtype->update(array('gid'=>$this->gid),$this->linkstype_arr)){
				message('分类修改成功','?action=links&o=tedit');
			}else {
				message('分类修改失败','?action=links&o=tedit');
			}
		}
		$this->display("links.html");
	}
	function tadd(){
		if ($this->syArgs('go')==1){
			if($this->Classtype->create($this->linkstype_arr)){
				message("分类添加成功","?action=links&o=tedit");
			}else{message("分类添加失败");}
		}
		$this->display("links.html");
	}
	function tdel(){
		$type_lids=$this->Class->findAll(array('gid'=>$this->gid));
		foreach ($type_lids as $d){
			$lids[]=$d['lid'];
		}
		$lid_str=join(",", $lids);
		$delSql="DELETE FROM ".$this->sqldb." WHERE `lid` IN (".$lid_str.")";
		if($this->Classtype->delete(array('gid'=>$this->gid)) && $this->Class->runSql($delSql)){
			message('友链分类删除成功，并且下属友链全部删除成功','?action=links&o=tedit');
		}else {
			message('友链分类删除失败，下属友链删除失败，请至数据库手动删除','?action=links&o=tedit');
		}
		$this->display("links.html");
	}
	function alledit(){
		$formnum=$this->syArgs('formnum');
		$types_arr=$this->syArgs('types',2);
		$this->operate_allLinks($formnum,$types_arr);
	}
	function operate_allLinks($formnum,$types_arr){
		$id_str=join(", ", $types_arr);
		$where_id_str = "`lid` IN (".$id_str.")";
		switch ($formnum){
			case 1:
				if(!$types_arr){
					message('您尚未选择任何友链...','?action=links',0,0);
				}
				$row['statu']=1;
				if($this->Class->update($where_id_str,$row)){
					message('批量显示友链成功','?action=links');
				}else {
					message('批量显示友链失败...','?action=links');
				}
				break;
			case 2:
				if(!$types_arr){
					message('您尚未选择任何友链...','?action=links',0,0);
				}
				$row['statu']=0;
				if($this->Class->update($where_id_str,$row)){
					message('批量隐藏友链成功','?action=links');
				}else {
					message('批量隐藏友链失败...','?action=links');
				}
				break;
			case 3:
				if(!$types_arr){
					message('您尚未选择任何友链...','?action=links',0,0);
				}
				if($this->Class->delete($where_id_str)){
					message('批量删除成功','?action=links');
				}else {
					message('批量删除失败...','?action=links');
				}
				break;
			case 4:
				$orders=$this->syArgs('orders',2);
				foreach ($orders as $id => $order){
					$condition_id=$this->pk."=".$id;
					$row_id['order']=$order;
					if(!$this->Class->update($condition_id,$row_id)){
						message("友链[".$condition_id."]顺序更改失败...",'?action=links');
					}
				}
				message('友链顺序更改成功','?action=links');
				break;
			case 5:
				if(!$types_arr){
					message('您尚未选择任何友链...','?action=links',0,0);
				}
				if($this->syArgs('agid')){
					$row_taid['gid']=$this->syArgs('agid');
					$restaid=$this->Class->update($where_id_str,$row_taid);
				}
				if($restaid){
					message("批量更改分类成功",'?action=links');
				}else {
					message("批量更改分类失败...",'?action=links');
				}
				break;
		}
	}
}