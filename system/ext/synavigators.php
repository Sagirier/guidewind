<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class synavigators {
        private $result;
        private $tmp;
        private $arr;
		private $arrp;
		private $arrt;
		private $n;
		public function __construct($result='') {
			if($result==''){
				if(syAccess('r','navigators')==FALSE){syAccess('w','navigators',syDB('navigators')->findAll(null,null,'`nid`,`statu`,`nname`,`ngid`,`cmark`'));}
				$this->result = syAccess('r','navigators');
			}else{$this->result=$result;}
        }
        public function navinfo($nid){
        	$navinfo=syDB('navigators')->findAll(array('nid'=>$nid),null,'`nid`,`statu`,`nname`,`ngid`,`cmark`',1);
        	return array_pop($navinfo);
        }
		private function find($nid){
			foreach ($this->result as $k => $v){
				if($v['ngid']== $nid){
					$childs[]=$v;
				}
			}
			return $childs;  
		}
        private function type_tree($nid=0,$ngid=0) {
			$childs=$this->find($nid);
			if(empty($childs)){
				return null;
			}
			foreach ($childs as $k => $v){
				$this->n= null;
				$this->n=count($this->navi($childs[$k]['nid'], $ngid));
				$childs[$k]['n']=$this->n;
				$rescurTree=$this->type_tree($v['nid'],$ngid);
				if( null !=   $rescurTree){ 
				$childs[$k]['child']=$rescurTree;
				}
			}
            $this->tmp = $childs;
			return $this->tmp;
        }

        private function recur_n($arr, $nid, $ngid) {
			foreach ($arr as $v) {
				if ($v['nid'] == $nid) {
					$this->arr[] = $v;
					if ($v['ngid'] != $ngid) $this->recur_n($arr, $v['ngid'], $ngid);
				}
			}
        }
        private function recur_p($arr) {
			foreach ($arr as $v) {
				if($v['statu']==1)$this->arrp = $this->arrp.','.$v['nid'];
				if ($v['child']) $this->recur_p($v['child']);
			}
        }
		private function type_txt_for($arr) {
			foreach ($arr as $v){
				$txt=array('nid'=>$v['nid'],'ngid'=>$v['ngid'],'name'=>$v['nname'],'n'=>$v['n']-1,'channels'=>$v['cmark']);
				$this->arrt[]=$txt;
				if(is_array($v['child'])){
					$this->type_txt_for($v['child']);
				}
			}
		}
        public function navi($nid, $ngid=0) {
			$this->arr = null;
			$this->recur_n($this->result, $nid, $ngid);
			return array_reverse($this->arr);
        }
        public function leafid($nid=0) {
			$this->arrp = $nid;
			$this->recur_p($this->type_tree($nid,$nid));
			return rtrim($this->arrp,",");
        }
		public function type_txt($nid=0) {
			$this->arrt = null;
			$this->type_txt_for($this->type_tree($nid,$nid));
			return $this->arrt;
		}
}
