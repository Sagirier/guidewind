<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class ajax extends syController
{
	function __construct(){
		parent::__construct();
		$this->sy_class_type=syClass('synavigators');
		$this->db=$GLOBALS['WP']['db']['prefix'];
	}
	function vercode(){
		if(md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify']){echo 'false';}else{echo 'true';}
	}
	function mycart(){
		$my=syClass('symember')->islogin(0);
		if($my['id']!=0){
			$g=syDB('goodscart')->findAll(array('uid'=>$my['id']),'aid desc,id desc');
			$gs=array();$i=0;
			foreach($g as $v){
				$va=syDB('product')->find(array('id'=>$v['aid'],'isshow'=>1),null,'title,price,litpic');
				$gs[$i]= array(
					'cartid' => $v['id'],
					'aid' => $v['aid'],
					'quantity' => $v['num'],
					'title' => $va['title'],
					'img' => $va['litpic'],
					'price' => $va['price'],
				);
				$attribute=unserialize($v['attribute']);
				if($attribute){			
					$p_type=syDB('attribute_type')->findSql('select distinct a.tid,a.aid,b.tid,b.isshow,b.orders,b.name from '.$this->db.'product_attribute a left join '.$this->db.'attribute_type b on (a.tid=b.tid) where a.aid='.$gs[$i]['aid'].' and b.isshow=1 order by b.orders desc,b.tid desc');
					foreach($p_type as $vp){
						$p=syDB('product_attribute')->find(array('aid' => $gs[$i]['aid'],'tid' => $vp['tid'],'sid' => $attribute[$vp['tid']]),null,'price');
						$gs[$i]['price']=$gs[$i]['price']+$p['price'];
						$a=syDB('attribute')->find(array('sid' => $attribute[$vp['tid']]),null,'name');
						$gs[$i]['attribute_txt'].=$vp['name'].'('.$a['name'].') ';
					}
				}
				$i++;
			}
		}
		$this->cart=$gs;
		$this->display($this->syArgs('template',1));
	}
	function mycart_total(){
		$my=syClass('symember')->islogin(0);
		if($my['id']!=0){
			echo total_page($this->db.'goodscart where uid='.$my['id']);
		}
	}
	function fields_contingency(){
		$molds=$this->syArgs('molds',1);
		$word=$this->syArgs('word',1);
		$fields=$this->syArgs('fields',1);
		if($word&&$molds&&$fields){
			$w.=" where ";
			$str = explode(' ',$word);
			foreach($str as $s){
				if($s)$w.=" title like '%".$s."%' or";
			}
			$w=rtrim($w,'or')." ";
			$sql='select id,title,addtime,orders from '.$this->db.$molds.$w.' order by orders desc,addtime desc,id desc limit 0,10';
			$info=syDB($molds)->findSql($sql); 
			if($info){
				foreach($info as $v){
					echo '<li onMouseOver=contingency_id_'.$fields.'('.$v['id'].',"'.$v['title'].'");>·'.$v['title'].'</li>';
				}
			}else{
				echo '<li>没有找到任何内容</li>';
			}
		}
	}
	function member_login(){
		$this->member=syClass('symember')->islogin(0);
		$this->display($this->syArgs('template',1));
	}
	function regcheck(){
	    if(funsinfo('member_sys','statu')==1){
	        $username=$this->syArgs('username',1);
	        if($username==''){
	            $ret=102;
	        }else{
    	        $res1=syDB("member")->find(array('username'=>$username));
    	        $res2=syDB("admin")->find(array('auser'=>$username));
    	        if($res1 || $res2){
    	            $ret=101;
    	        }else{
    	            $ret=100;
    	        }
	        }
	        echo json_encode($ret);
		    exit();
	    }
	}
	function comment(){
		if(funsinfo('comment_sys','statu')==1){
			$this->re=false;
			$c=syClass('c_comment');
			$total_page=total_count($GLOBALS['WP']['db']['prefix'].'comment where `statu`=1 and `aid`='.$this->syArgs('aid').' and `cmark`="'.$this->syArgs('cmark',1).'"');
			$this->commentlists=$c->syPager($this->syArgs('comment_page',0,1),3,$total_page)->findAll(array('statu'=>1,'aid'=>$this->syArgs('aid'),'cmark'=>$this->syArgs('cmark',1)),' `addtime` desc ');
			$list_c=$this->commentlists;
			foreach ($list_c as $k=>$v){
				$list_c[$k]=array_merge($list_c[$k],array('user'=>memberoneinfo($list_c[$k]['uid'],'nickname') ? memberoneinfo($list_c[$k]['uid'],'nickname') : '游客'));
			}
			$this->commentlists=$list_c;
			$c_page=$c->syPager()->getPager();
			$this->comment_page=pagetxt_ajax($c_page,$GLOBALS['WP']['url']["url_path_base"].'?action='.$this->syArgs('cmark',1).'&id='.$this->syArgs('aid'),"ajax_comment('".$this->syArgs('id',1)."','".$this->syArgs('cmark',1)."',".$this->syArgs('aid').",[_page_],'".$this->syArgs('template',1)."');");
			$this->display($this->syArgs('template',1));
		}
	}
	function comment_reply(){
		$id=$this->syArgs('id');
		if(funsinfo('comment_sys','statu')==1){
			$this->re=true;	
			$this->commentinfo=syDB('comment')->find(array('id'=>$id));
			if($this->commentinfo['uid']!=0){
				$memberinfo=memberinfo($this->commentinfo['uid']);
				$this->commentinfo=array_merge($this->commentinfo,array('uname'=>$memberinfo['nickname'],'uuser'=>$memberinfo['username'],'uportrait'=>$memberinfo['portrait']));
			}
			$comment_from=syDB($this->commentinfo['cmark'])->find(array('id'=>$this->commentinfo['aid']));
			$aurl=html_url($this->commentinfo['cmark'],$comment_from);
			$this->commentinfo=array_merge($this->commentinfo,array('aurl'=>$aurl));
			$this->replyinfo=replyinfo($this->commentinfo['id'],'comment');
		}
		$this->display($this->syArgs('template',1));
	}
	function message_reply(){
	    $id=$this->syArgs('id');
	    if(channelsinfo('message','statu')==1){
	        $this->re=true;
	        $this->messageinfo=syDB('message')->find(array('id'=>$id));
	        if($this->messageinfo['uid']!=0){
	            $memberinfo=memberinfo($this->messageinfo['uid']);
	            $this->messageinfo=array_merge($this->messageinfo,array('uname'=>$memberinfo['nickname']!='' ? $memberinfo['nickname'] : $memberinfo['username'],'uportrait'=>$memberinfo['portrait']));
	        }
	        $this->replyinfo=replyinfo($this->messageinfo['id'],'message');
	    }
	    $this->display($this->syArgs('template',1));
	}
	function record(){
		$c=syClass("c_sales_record");
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'sales_record where aid='.$this->syArgs('aid'));
		$this->record=$c->syPager($this->syArgs('record_page',0,1),10,$total_page)->findAll(array('aid'=>$this->syArgs('aid')),' stime desc ');
		$c_page=$c->syPager()->getPager();
		$this->record_page=pagetxt_ajax($c_page,$GLOBALS['G_DY']['url']["url_path_base"].'?c=product&id='.$this->syArgs('aid'),"ajax_record('".$this->syArgs('id',1)."',".$this->syArgs('aid').",[_page_],'".$this->syArgs('template',1)."');");
		$this->display($this->syArgs('template',1));
	}
}