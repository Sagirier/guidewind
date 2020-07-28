<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class article extends syController{
	public $pk = "id";
	public $pkn = "nid";
	public $table = "article";
	public $traits = "traits";
	function __construct(){
		parent::__construct();
		$this->gopage=$this->syArgs('page',0,1);
		$this->id=$this->syArgs('id');
		$this->a='contents';
		$this->channels = 'article';
		$this->Class=syClass('c_'.$this->channels);
		$this->sy_class_type=syClass('synavigators');
		$this->navtree=$this->sy_class_type->type_txt();
		$imgtypes_arr=array('jpg','jpeg','png','gif','bmp');
		for ($i=0;$i<count($imgtypes_arr);$i++){
			if(in_array($imgtypes_arr[$i],explode(",", $GLOBALS['WP']['ext']['filetype']))){
				$article_type[]="*.".$imgtypes_arr[$i];
			}
		}
		$this->article_type=join(";",$article_type);
		$this->article_typename='图片';
		$this->cname=syDB("channels")->find(array('cmark'=>$this->channels));
		$this->sqldb=$GLOBALS['WP']['db']['prefix'].$this->channels;
		$this->conditions=array(
			'nid' => $this->syArgs('article-schbynid'),
			'statu' => $this->syArgs('article-schbystatu'),
			'traits' => $this->syArgs('article-schbytrait'),
			'lipic' => $this->syArgs('article-schbylipic'),
			'password' => $this->syArgs('article-schbypass'),
			'title' =>	$this->syArgs('title',1)
		);
		$condition="";
		if($this->conditions['nid']!=0){
			$condition.="and `nid` in(".$this->sy_class_type->leafid($this->conditions['nid']).") ";
		}
		if($this->conditions['statu']==1){
			$this->top_txt='<font color="#23C6C8">已审核</font>的';
			$condition.="and `statu`=1 ";
		}
		if($this->conditions['statu']==2){
			$this->top_txt='<font color="#F00">待审核</font>的';
			$condition.="and `statu`=0 ";
		}
		if($this->conditions['traits']!=''){
			$condition.="and `traits` like '%|".$this->conditions['traits']."|%' ";
		}
		if($this->conditions['lipic']==1){
			$condition.="and `lipic`!='' ";
		}
		if($this->conditions['lipic']==2){
			$condition.="and `lipic`='' ";
		}
		if($this->conditions['password']==1){
			$condition.="and `password`!='' ";
		}
		if($this->conditions['password']==2){
			$condition.="and `password`='' ";
		}
		if($this->conditions['title']!=''){
			$condition.="and `title` like '%".$this->conditions['title']."%' ";
		}
		if($condition!=''){$condition=' where '.substr($condition,3);}
		$sql='select * from '.$this->sqldb.$condition.' order by `order` desc,`addtime` desc,`id` desc';
		//获取文章总数
		$total_count=total_count($this->sqldb.$condition);
		//进行分页输出
		$this->lists = syClass('syModel')->syPager($this->gopage,10,$total_count)->findSql($sql);
		$this->pages = pagetxt(syClass('syModel')->syPager()->getPager());
		$this->traits_lists=$this->get_trait_lists();
		$this->traits_class=syClass('sytraits');
		if(($this->syArgs('o',1)=='add'||$this->syArgs('o',1)=='edit') && $this->syArgs('go')==1){
			$this->article_arr=array(
				'nid'=>$this->syArgs('article_nid'),
				'statu'=>$this->syArgs('article_statu'),
				'title'=>$this->syArgs('article_title',1),
				'gourl'=>$this->syArgs('article_gourl',1),
				'addtime'=>strtotime($this->syArgs('article_addtime',1)),
				'hints'=>$this->syArgs('article_hints'),
				'lipic'=>$this->syArgs('article_lipic',1),
				'order'=>$this->syArgs('article_order'),
				'keywords'=>$this->syArgs('article_keywords',1),
				'description'=>$this->syArgs('article_description',1),
			);
			if(is_array($this->syArgs('article_traits',2)) && $this->syArgs('article_traits',2)!=''){
				$this->article_arr=array_merge($this->article_arr,array('traits'=>'|'.implode('|',$this->syArgs('article_traits',2)).'|'));
			}
			if($this->syArgs('article_htmlfile',1)!=''){
				$this->article_arr=array_merge($this->article_arr,array('htmlfile'=>$this->syArgs('article_htmlfile',1),'htmlurl'=>"static/".$this->channels."/".$this->syArgs('article_htmlfile',1).".html"));
			}
			if($this->syArgs('article_password',1)!=''){
				$this->article_arr=array_merge($this->article_arr,array('password'=>syPass($this->syArgs('article_password',1),'INCODE')));
			}else {
				$this->article_arr=array_merge($this->article_arr,array('password'=>''));
			}
			if($this->syArgs('o',1)=='add'){$this->article_arr=array_merge($this->article_arr,array('user' => $this->user['auser']));}
			$this->field_row=array(
				'detail'=>code_body($this->syArgs('article_detail',4))	
			);
			$article_field_arr=syClass("c_fields")->findAll(" `cmark`='".$this->channels."' and `navigators` like '%|".$this->syArgs('article_nid')."|%' ");
			foreach ($article_field_arr as $v){
				$ns='';$n=array();
				if($v['ftype']=='varchar' || $v['ftype']=='file' || $v['ftype']=='radio'){ $ns=$this->syArgs('article_'.$v['fmark'],1);}
				if($v['ftype']=='int'){ $ns=$this->syArgs('article_'.$v['fmark']);}
				if($v['ftype']=='money'){ $ns=$this->syArgs('article_'.$v['fmark'],3);}
				if($v['ftype']=='text'){ $ns=$this->syArgs('article_'.$v['fmark'],4);}
				if($v['ftype']=='date'){ $ns=strtotime($this->syArgs('article_'.$v['fmark'],1));}
				if($v['ftype']=='multifile'){ 
					$files=$this->syArgs('article_'.$v['fmark'].'file',2);
					if($files){
						$num=$this->syArgs('article_'.$v['fmark'].'num',2);
						$txt=$this->syArgs('article_'.$v['fmark'].'txt',2);$ns='';
						natsort($num);
						foreach($num as $k=>$v){
							$ns.=$files[$k].'|'.$txt[$k];
						}
						$ns=substr($ns,3);
					}
				}
				if($v['ftype']=='select'){if($this->syArgs('article_'.$v['fmark'],2)){$ns='|'.implode('|',$this->syArgs('article_'.$v['fmark'],2)).'|';}else{$ns='';}}
				$n=array($v['fmark']=> $ns);
				$this->field_row=array_merge($this->field_row,$n);
			}
		}
	}
	function index(){
		$this->display($this->channels.".html");
	}
	function get_info($id){
		$this->article_info=$this->Class->findAll(array('id'=>$id));
		foreach ($this->article_info[0] as $key=> $val){
			if($key!='password'){
				$new_articleinfo[$key]=$val;
			}else {
				$new_articleinfo[$key]=syPass($val);
			}
		}
		$this->article_field=syDB($this->table."_field")->findAll(array('aid'=>$id));
		return array($new_articleinfo,$this->article_field);
	}
	function get_trait_lists(){
		return syDB($this->traits)->findALL(array('cmark'=>$this->channels));
	}
	function edit(){
		if(!$this->userClass->checkgo('article_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->article=$this->Class->find(array('id'=>$this->id));
		$this->article_detail=syDB($this->channels."_field")->find(array('aid'=>$this->id));
		$where_nid=" `cmark`='".$this->c."' and `navigators` like '%|".$this->nid."|%' ";
		$this->total_fields=total_count($GLOBALS['WP']['db']['prefix']."fields where `cmark`='article' and `navigators` like '%|".$this->article['nid']."|%' ");
		if($this->syArgs('go')==1){
			if($this->syArgs('article_htmlfile',1)==''){
				$this->article_arr=array_merge($this->article_arr,array('htmlfile'=>'','htmlurl'=>"static/".$this->channels."/".$this->id.".html"));
			}
			if($this->Class->update(array('id'=>$this->id),$this->article_arr)){
				if(!syDB($this->table."_field")->findAll(array('aid'=>$this->id))){
					$this->field_row=array_merge($this->field_row,array('aid'=>$this->id));
					syDB($this->table."_field")->create($this->field_row);
				}else{
					syDB($this->table."_field")->update(array('aid'=>$this->id),$this->field_row);
				}
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("文章[<font color='#1AB394'>".$this->article['title']."</font>]修改成功",'?action='.$this->channels);
			}else {
				message("文章[<font color='#1AB394'>".$this->article['title']."</font>]修改失败",'?action='.$this->channels);
			}
		}
		$this->display($this->channels."_edit.html");
	}
	function alledit(){
		if(!$this->userClass->checkgo('article_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$formnum=$this->syArgs('formnum');
		$types_arr=$this->syArgs('types',2);
		$this->operate_allArticle($formnum,$types_arr);
	}
	function add(){
		if(!$this->userClass->checkgo('article_add')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->nid=$this->syArgs('nid');
		$this->total_fields=total_count($GLOBALS['WP']['db']['prefix']."fields where `cmark`='article' and `navigators` like '%|".$this->nid."|%' ");
		if($this->syArgs('go')==1){
		    $resaid=$this->Class->create($this->article_arr);
			if($resaid){
				if($this->syArgs('article_htmlfile',1)==''){
					if(!$this->Class->update(array('id'=>$resaid),array('htmlurl'=>"static/".$this->channels."/".$resaid.".html"))){
						$this->Class->delete(array('id'=>$resaid));
						message("文章静态url设置失败，请重新发布...",'?action='.$this->channels.'&o=add');
					}
				}
				$this->field_row=array_merge($this->field_row,array('aid'=>$resaid));
				if(syDB($this->channels."_field")->create($this->field_row)){
					deleteDir($GLOBALS['WP']['sp_cache']);
					message_c('文章发布成功','?action='.$this->channels,'?action='.$this->channels.'&o=add&nid='.$this->article_arr['nid']);
				}
			}else {
				message("文章发布失败，请重新发布...",'?action='.$this->channels);
			}
		}
		$this->display($this->channels."_edit.html");
	}
	function del(){
		if(!$this->userClass->checkgo('article_del')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		if($this->Class->delete(array('id'=>$this->id)) && syDB($this->table."_field")->delete(array('aid'=>$this->id))){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message("删除文章[<font color='#1AB394'>id=".$this->id."</font>]成功",'?action='.$this->channels);
		}else {
			message("删除文章失败，请至数据库手动删除...",'?action='.$this->channels);
		}
	}
	function operate_allArticle($formnum,$types_arr){
		$id_str=join(", ", $types_arr);
		$where_id_str = "`id` IN (".$id_str.")";
		$where_aid_str ="`aid` IN (".$id_str.")";
		switch ($formnum){
			case 1:
				if(!$types_arr){
					message('您尚未选择任何文章...','?action='.$this->channels,0,0);
				}
				$row['statu']=1;
				if($this->Class->update($where_id_str,$row)){
					message('批量审核成功','?action='.$this->channels);
				}else {
					message('批量审核失败...','?action='.$this->channels);
				}
				break;
			case 2:
				if(!$types_arr){
					message('您尚未选择任何文章...','?action='.$this->channels,0,0);
				}
				$row['statu']=0;
				if($this->Class->update($where_id_str,$row)){
					message('批量取消审核成功','?action='.$this->channels);
				}else {
					message('批量取消审核失败...','?action='.$this->channels);
				}
				break;
			case 3:
				if(!$types_arr){
					message('您尚未选择任何文章...','?action='.$this->channels,0,0);
				}
				if($this->Class->delete($where_id_str) && syDB($this->table."_field")->delete($where_aid_str)){
					message('批量删除成功','?action='.$this->channels);
				}else {
					message('批量删除失败...','?action='.$this->channels);
				}
				break;
			case 4:
				$orders=$this->syArgs('orders',2);
				foreach ($orders as $id => $order){
				    $condition_id=$this->pk."=".$id;
					$row_id['order']=$order;
 					if(!$this->Class->update($condition_id,$row_id)){
 						message("文章[".$condition_id."]顺序更改失败...",'?action='.$this->channels);
 					}
				}
				message('批量更改顺序成功','?action='.$this->channels);
				break;
			case 5:
				if(!$types_arr){
					message('您尚未选择任何文章...','?action='.$this->channels,0,0);
				}
				if($this->syArgs('anid')){
					$row_nid['nid']=$this->syArgs('anid');
					$resnid=$this->Class->update($where_id_str,$row_nid);
				}
				if($this->syArgs('atid',2)){
					if(in_array('clear', $this->syArgs('atid',2))){
						$row_traits['traits']='';
					}else {
						$row_traits['traits']='|'.implode('|',$this->syArgs('atid',2)).'|'; 
					}
					$restraits=$this->Class->update($where_id_str,$row_traits);
				}
				if($resnid && !$restraits){
					message("批量更改栏目成功",'?action='.$this->channels);
				}elseif (!$resnid && $restraits){
					message("批量更改文章属性成功",'?action='.$this->channels);
				}elseif ($resnid && $restraits){
					message("批量更改栏目和文章属性成功",'?action='.$this->channels);
				}else {
					message("批量操作失败...",'?action='.$this->channels);
				}
				break;
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
	}
}	