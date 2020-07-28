<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class help extends syController
{
	function __construct(){
		parent::__construct();
		$this->cmark = 'product';
		$this->cname=channelsinfo("product", "cname");
		$this->sy_class_type=syClass('synavigators');
		$this->Class=syClass('c_product');
		$this->ip=GetIP();
		$this->db=$GLOBALS['WP']['db']['prefix'].'product';
		//评论开关
		$this->comment=funsinfo('comment_sys','statu');
	}
	function index(){
		if($this->syArgs('file',1)!=''){
			$this->product=syDB('product')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where (`htmlfile`="'.$this->syArgs('file',1).'" or `id`='.$this->syArgs('file').') and `statu`=1 limit 1');
			$id = $this->product['id'];
		}else{
			$id = $this->syArgs('id');
			if(!$id){message("请指定内容id");}
			$this->product=syDB('product')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where `id`='.$id.' and `statu`=1 limit 1');
		}
		if(!$this->product){message("指定内容不存在或未审核");}
		$this->product=$this->product[0];
		if($this->product['mrank']==1){
			syClass('symember')->islogin(1,1);
		}
		if($this->product['password']!=''){
			$cmark=navinfo($this->product['nid'], 'cmark');
			if(!$_SESSION[$cmark."_".$this->product['id']]){
				message_pass($this->product['id'],html_url('product',$this->product),'product');
			}
		}
		$this->product=array_merge($this->product,array('nid_leafid'=>$this->sy_class_type->leafid($this->product['nid'])));
		$prev_next_w=' and `nid` in('.$this->sy_class_type->leafid($this->product['nid']).') ';
		$prev_next_f='`id`,`lipic`,`mrank`,`title`,`htmlurl`,`htmlfile`,`password`';
		$prev=syDB('product')->find(' `id` < '.$this->product['id'].$prev_next_w,'`id`',$prev_next_f);
		if($prev){
			$prev['url']=html_url('product',$prev);
			$this->aprev=$prev;
		}
		$next=syDB('product')->find(' `id` > '.$this->product['id'].$prev_next_w,'`id`',$prev_next_f);
		if($next){
			$next['url']=html_url('product',$next);
			$this->anext=$next;
		}
		
		$detail=array_filter(explode("[guide|page]",$this->product['detail']));
		if(count($detail)>1){
			$pages=array(
						'total_page' => count($detail),    // 总页数
						'prev_page' => $this->syArgs('page',0,1)-1,     // 上一页的页码
						'next_page' => $this->syArgs('page',0,1)+1,     // 下一页的页码
						'last_page' => count($detail),      // 最后一页的页码
						'current_page' => $this->syArgs('page',0,1),   // 当前页码
					);
			$this->product=array_merge($this->product,array('detail'=>$detail[$this->syArgs('page',0,1)-1]));
			if($this->syArgs('page')>1){
				$this->product=array_merge($this->product,array('title'=>$this->product['title'].'&nbsp;&nbsp;('.$this->syArgs('page').')'));
			}
			$this->pages=html_url('product',$this->product,$pages,$this->syArgs('page',0,1));
		}
		$this->type=syDB('navigators')->find(" `cmark`='product' and `nid`=".$this->product['nid']." ",null,'`nid`,`nname`,`lipic`,`seo_keywords`,`description`,`ct_content`,`htmldir`,`htmlfile`,`mrank`,`msubmit`,`password`');
		if($this->type['mrank']==1){
			syClass('symember')->p_v($this->type['mrank']);
		}
		$this->type=array_merge($this->type,array('nid_leafid'=>$this->sy_class_type->leafid($this->product['nid'])));
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>';
		foreach($this->sy_class_type->navi($this->product['nid']) as $v){
			$d_pos=syDB('navigators')->find(array('nid'=>$v['nid']),null,'`nid`,`cmark`,`htmldir`,`htmlfile`,`mrank`');
			$this->positions.='<a href="'.html_url('navigators',$d_pos).'">_'.$v['nname'].'</a>';
		}
		//$this->positions.='  <i class="fa fa-angle-double-right"></i>  正文';
		$this->display('product/'.$this->type['ct_content']);
	}
	function type(){
		if($this->syArgs('file',1)!=''){
			$this->type=syDB('navigators')->find(' `htmlfile`="'.$this->syArgs('file',1).'" or `nid`='.$this->syArgs('file').' ');
			$nid = $this->type['nid'];
		}else{
			$nid = $this->syArgs('nid');
			$this->type=syDB('navigators')->find(" `cmark`='product' and `nid`=".$nid." ");
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
			$f=syDB('fields')->findAll(" `cmark`='product' and `navigators` like '%|".$nid."|%' and `lists`=1 ");
			if($f){
				foreach($f as $v){$fields.=',`'.$v['fmark'].'`';}
				$sql='select `id`,`nid`,`title`,`traits`,`gourl`,`addtime`,`hints`,`record`,`price`,`lipic`,`order`,`mrank`,`statu`,`description`,`htmlurl`,`htmlfile`,`user`,`password`'.$fields.' from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
			}else{
				$sql='select `id`,`nid`,`title`,`traits`,`gourl`,`addtime`,`hints`,`record`,`price`,`lipic`,`order`,`mrank`,`statu`,`description`,`htmlurl`,`htmlfile`,`user`,`password` from '.$this->db.$w.$order;
			}
			$total_count=total_count($this->db.$w);
			$this->lists = $this->Class->syPager($this->syArgs('page',0,1),$this->type['listno'],$total_count)->findSql($sql);
			$pages=$this->Class->syPager()->getPager();
			$this->pages=html_url('navigators',$this->type,$pages,$this->syArgs('page',0,1));
			$list_c=$this->lists;
			foreach($list_c as $k=>$v){
				if($_SESSION['product_'.$list_c[$k]['id']]){
					$list_c[$k]=array_merge($list_c[$k],array('pass'=>1));
				}else {
					$list_c[$k]=array_merge($list_c[$k],array('pass'=>0));
				}
				$list_c[$k]['url']=html_url('product',$v);
			}
			$this->lists=$list_c;
		}
		$this->positions='<a href="'.$GLOBALS["WWW"].'"><i class="fa fa-home"></i> 首页</a>';
		$type_pos=$this->sy_class_type->navi($nid);
		foreach($type_pos as $v){
			$d_pos=syDB('navigators')->find(array('nid'=>$v['nid']),null,'`nid`,`cmark`,`htmldir`,`htmlfile`,`mrank`');
			$this->positions.='<a href="'.html_url('navigators',$d_pos).'">_'.$v['nname'].'</a>';
		}
		$this->display('product/'.$t);
	}
	function hints(){
		if($this->syArgs('id')){
			syDB('product')->incrField(array('id'=>$this->syArgs('id')), 'hints');
			$hints=syDB('product')->find(array('id'=>$this->syArgs('id')),null,'hints');
			echo 'document.write("'.$hints['hints'].'");';
		}
	}
	function search(){
		$this->type=array('title'=>'站内搜索','keywords'=>$GLOBALS['SITE']['keywords'],'description'=>$GLOBALS['SITE']['description'],'nname'=>'站内搜索',);
		$this->type=array_merge($this->type,array('nid_leafid'=>$this->sy_class_type->leafid()));
		$w.=" where `statu`=1 ";
		$keyword=$this->syArgs('keyword',1);
		if($keyword){
			$w.=" and (";
			$str = explode(' ',$keyword);
			foreach($str as $s){
				if($s)$w.=" `title` like '%".$s."%' or `keywords` like '%".$s."%' or";
			}
			$w=rtrim($w,'or').") ";
		}
		$order=' order by `order` desc,`addtime` desc,`id` desc';
		$sql='select `id`,`nid`,`title`,`traits`,`gourl`,`addtime`,`hints`,`record`,`price`,`lipic`,`order`,`mrank`,`statu`,`description`,`htmlurl`,`htmlfile`,`user`,`password` from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
		$total_count=total_count($this->db.$w);
		$this->lists = $this->Class->syPager($this->syArgs('page',0,1),10,$total_page)->findSql($sql); 
		$pages=$this->Class->syPager()->getPager();
		$this->pages=pagetxt($pages);
		$list_c=$this->lists;
		foreach($list_c as $k=>$v){
			$list_c[$k]['title']=str_ireplace($this->syArgs('keyword',1),'<b style="color:red;">'.$this->syArgs('keyword',1).'</b>',$v['title']);
			$list_c[$k]['url']=html_url('product',$v);
		}		
		$this->lists=$list_c;
		$this->positions='<a href="'.$GLOBALS["WWW"].'"><i class="fa fa-home"></i> 首页</a> &gt; '.$this->cname.'搜索“'.$this->syArgs('keyword',1).'” <span style="float:right">共找到<b style="color:red">'.count($this->lists).'</b>件符合条件的'.$this->cname.'</span>';
		$this->display('product/search.html');
	}
	function passcheck() {
		$id=$this->syArgs('id');
		$pass=$this->syArgs('pass',1);
		$c=$this->Class->find(array('id'=>$id));
		$incodepass=$c['password'];
		$cmark=navinfo($c['nid'], 'cmark');
		$decodepass=syPass($incodepass);
		if($pass==''){
			$ret['result_code']=102;
			$ret['result_des']="密码不能为空！";
		}else {
			$lifeTime = 24 * 3600;  // 保存一天
			session_set_cookie_params($lifeTime);
			session_start();
			if($pass!=$decodepass){
				$ret['result_code']=101;
				$ret['result_des']="密码输入错误！";
			}else{
				$ret['result_code']=100;
				$ret['result_des']="密码输入正确！";
				$_SESSION[$cmark."_".$id]=$incodepass;
			}
		}
		echo json_encode($ret);
		exit();
	}
	function passcheck_type() {
		$nid=$this->syArgs('id');
		$pass=$this->syArgs('pass',1);
		$c=syDB('navigators')->find(array('nid'=>$nid));
		$incodepass=$c['password'];
		$decodepass=syPass($incodepass);
		if($pass==''){
			$ret['result_code']=102;
			$ret['result_des']="密码不能为空！";
		}else {
			$lifeTime = 24 * 3600;  // 保存一天
			session_set_cookie_params($lifeTime);
			session_start();
			if($pass!=$decodepass){
				$ret['result_code']=101;
				$ret['result_des']="密码输入错误！";
			}else{
				$ret['result_code']=100;
				$ret['result_des']="密码输入正确！";
				$_SESSION["type_".$nid]=$incodepass;
			}
		}
		echo json_encode($ret);
		exit();
	}
}	