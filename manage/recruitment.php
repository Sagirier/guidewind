<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class recruitment extends syController
{
	function __construct(){
		parent::__construct();
		$this->cmark = 'recruitment';
		$this->cname=channelsinfo("recruitment", "cname");
		$this->sy_class_type=syClass('synavigators');
		$this->Class=syClass('c_recruitment');
		$this->db=$GLOBALS['WP']['db']['prefix'].'recruitment';
		//文章评论开关
		$this->comment=funsinfo('comment_sys','statu');
	}
	function index(){
		if($this->syArgs('file',1)!=''){
			$this->recruitment=syDB('recruitment')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where (`htmlfile`="'.$this->syArgs('file',1).'" or `id`='.$this->syArgs('file').') and `statu`=1 limit 1');
			$id = $this->recruitment['id'];
		}else{
			$id = $this->syArgs('id');
			if(!$id){message("请指定内容id");}
			$this->recruitment=syDB('recruitment')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where `id`='.$id.' and `statu`=1 limit 1');
		}
		if(!$this->recruitment){message("指定内容不存在或未审核");}
		$this->recruitment=$this->recruitment[0];
		$this->recruitment=array_merge($this->recruitment,array('nid_leafid'=>$this->sy_class_type->leafid($this->recruitment['nid'])));
		if($this->recruitment['mrank']==1){
			syClass('symember')->islogin(1,1);
		}
		$this->fields=syDB('fields')->findAll('`cmark`="'.$this->cmark.'" and `navigators` like "%|'.$this->recruitment['nid'].'|%"',' `order` DESC,`fid` ');
		$prev_next_w=' and `nid` in('.$this->sy_class_type->leafid($this->recruitment['nid']).') ';
		$prev_next_f='`id`,`lipic`,`mrank`,`title`,`htmlurl`,`htmlfile`';
		$prev=syDB('recruitment')->find(' `id` < '.$this->recruitment['id'].$prev_next_w,'`id`',$prev_next_f);
		if($prev){
			$prev['url']=html_url('recruitment',$prev);
			$this->aprev=$prev;
		}
		$next=syDB('recruitment')->find(' `id` > '.$this->recruitment['id'].$prev_next_w,'`id`',$prev_next_f);
		if($next){
			$next['url']=html_url('recruitment',$next);
			$this->anext=$next;
		}
		
		$detail=array_filter(explode("[guide|page]",$this->recruitment['detail']));
		if(count($detail)>1){
			$pages=array(
						'total_page' => count($detail),    // 总页数
						'prev_page' => $this->syArgs('page',0,1)-1,     // 上一页的页码
						'next_page' => $this->syArgs('page',0,1)+1,     // 下一页的页码
						'last_page' => count($detail),      // 最后一页的页码
						'current_page' => $this->syArgs('page',0,1),   // 当前页码
					);
			$this->recruitment=array_merge($this->recruitment,array('detail'=>$detail[$this->syArgs('page',0,1)-1]));
			if($this->syArgs('page')>1){
				$this->recruitment=array_merge($this->recruitment,array('title'=>$this->recruitment['title'].'&nbsp;&nbsp;('.$this->syArgs('page').')'));
			}
			$this->pages=html_url('recruitment',$this->recruitment,$pages,$this->syArgs('page',0,1));
		}
		$this->type=syDB('navigators')->find(" `cmark`='recruitment' and `nid`=".$this->recruitment['nid']." ",null,'`nid`,`nname`,`lipic`,`seo_keywords`,`description`,`ct_content`,`htmldir`,`htmlfile`,`mrank`,`msubmit`,`password`');
		if($this->type['mrank']==1){
			syClass('symember')->p_v($this->type['mrank']);
		}
		$this->type=array_merge($this->type,array('nid_leafid'=>$this->sy_class_type->leafid($this->recruitment['nid'])));
		$this->positions='<a href="'.$GLOBALS["WWW"].'"><i class="fa fa-home"></i> 首页</a>';
		foreach($this->sy_class_type->navi($this->recruitment['nid']) as $v){
			$d_pos=syDB('navigators')->find(array('nid'=>$v['nid']),null,'`nid`,`cmark`,`htmldir`,`htmlfile`,`mrank`');
			$this->positions.='  <i class="fa fa-angle-double-right"></i>  <a href="'.html_url('navigators',$d_pos).'">'.$v['nname'].'</a>';
		}
		$this->positions.='  <i class="fa fa-angle-double-right"></i>  正文';
		$this->display('recruitment/'.$this->type['ct_content']);
	}
	function type(){
		if($this->syArgs('file',1)!=''){
			$this->type=syDB('navigators')->find(' `htmlfile`="'.$this->syArgs('file',1).'" or `nid`='.$this->syArgs('file').' ');
			$nid = $this->type['nid'];
		}else{
			$nid = $this->syArgs('nid');
			$this->type=syDB('navigators')->find(" `cmark`='recruitment' and `nid`=".$nid." ");
		}
		if(!$this->type){message("指定栏目不存在");}
	    if($this->type['mrank']==1){
			syClass('symember')->islogin(1,1);
		}
		if($this->type['mrank']==2 && $this->type['password']!=''){
			if(!$_SESSION["type_".$this->type['nid']]){
				message_pass($this->type['nid'],html_url('article', $this->type,'type'),'article',true);
			}
		}
		if($this->type['msubmit']==1 || $this->type['msubmit']==2){
		    $this->symember->p_v($this->type['msubmit']);
		}
		$this->type=array_merge($this->type,array('nid_leafid'=>$this->sy_class_type->leafid($nid)));
		if($this->type['isindex']==3)$t=$this->type['ct_listbody'];
		if($this->type['isindex']==2)$t=$this->type['ct_listimg'];
		if($this->type['isindex']==1)$t=$this->type['ct_list'];
		if($this->type['isindex']==1||$this->type['isindex']==2){
			$w.=" where `statu`=1 ";
			$w.="and `nid` in(".$this->type['nid_leafid'].") ";
			if($this->syArgs('traits'))$w.="and `traits` like '%|".$this->syArgs('traits')."|%' ";
			$order=' order by `order` desc,`addtime` desc,`id` desc';
			$f=syDB('fields')->findAll(" `cmark`='recruitment' and `navigators` like '%|".$nid."|%' and `lists`=1 ");
			if($f){
				foreach($f as $v){$fields.=',`'.$v['fmark'].'`';}
				$sql='select `id`,`nid`,`title`,`traits`,`gourl`,`addtime`,`hints`,`lipic`,`order`,`mrank`,`statu`,`description`,`htmlurl`,`htmlfile`,`user`'.$fields.' from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
			}else{
				$sql='select `id`,`nid`,`title`,`traits`,`gourl`,`addtime`,`hints`,`lipic`,`order`,`mrank`,`statu`,`description`,`htmlurl`,`htmlfile`,`user` from '.$this->db.$w.$order;
			}
			$total_count=total_count($this->db.$w);
			$this->lists = $this->Class->syPager($this->syArgs('page',0,1),$this->type['listno'],$total_count)->findSql($sql);
			$pages=$this->Class->syPager()->getPager();
			$this->pages=html_url('navigators',$this->type,$pages,$this->syArgs('page',0,1));
			$list_c=$this->lists;
			foreach($list_c as $k=>$v){
				if($_SESSION['recruitment_'.$list_c[$k]['id']]){
					$list_c[$k]=array_merge($list_c[$k],array('pass'=>1));
				}else {
					$list_c[$k]=array_merge($list_c[$k],array('pass'=>0));
				}
				$list_c[$k]['url']=html_url('recruitment',$v);
			}
			$this->lists=$list_c;
		}
		$this->positions='<a href="'.$GLOBALS["WWW"].'"><i class="fa fa-home"></i> 首页</a>';
		$type_pos=$this->sy_class_type->navi($nid);
		foreach($type_pos as $v){
			$d_pos=syDB('navigators')->find(array('nid'=>$v['nid']),null,'`nid`,`cmark`,`htmldir`,`htmlfile`,`mrank`');
			$this->positions.='  
<i class="fa fa-angle-double-right"></i>
			<a href="'.html_url('navigators',$d_pos).'">'.$v['nname'].'</a>';
		}
		$this->display('recruitment/'.$t);
	}
	function hints(){
		if($this->syArgs('id')){
			syDB('recruitment')->incrField(array('id'=>$this->syArgs('id')), 'hints');
			$hints=syDB('recruitment')->find(array('id'=>$this->syArgs('id')),null,'hints');
			echo 'document.write("'.$hints['hints'].'");';
		}
	}
}	