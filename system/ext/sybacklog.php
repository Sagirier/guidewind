<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class sybacklog {
 	public function __construct() {
 		
 	}
	function get_backlog_num(){
		return count($this->get_unaudit_lists());
	}
	public function get_unaudit_lists($condition) {
		$unaudit_arr=array();
		if(channelsinfo('article','statu')==1){
			$unaudit_article=$this->get_unaudit_total('article',array('statu'=>0)); //未审核文章列表
			if($condition['cmark']=='article'){
				if($condition['title']!=''){
					foreach ($unaudit_article as $v){
						if(stristr($v['title'], $condition['title'])){ //找到匹配字串
							$v['title']=highLight($v['title'], $condition['title']);
							$newunaudit_arr[]=$v;
						}
					}
					$newunaudit_arr=selectSort($newunaudit_arr,'bts','addtime');
					return $newunaudit_arr;
				}
				$unaudit_article=selectSort($unaudit_article,'bts','addtime');
				return $unaudit_article;
			}
			if(!empty($unaudit_article)){
				$unaudit_arr=array_merge($unaudit_arr,$unaudit_article);
			}
		}
		if(channelsinfo('product','statu')==1){
			$unaudit_product=$this->get_unaudit_total('product',array('statu'=>0)); //未上架商品列表
			if($condition['cmark']=='product'){
				if($condition['title']!=''){
					foreach ($unaudit_product as $v){
						if(stristr($v['title'], $condition['title'])){ //找到匹配字串
							$v['title']=highLight($v['title'], $condition['title']);
							$newunaudit_arr[]=$v;
						}
					}
					$newunaudit_arr=selectSort($newunaudit_arr,'bts','addtime');
					return $newunaudit_arr;
				}
				$unaudit_product=selectSort($unaudit_product,'bts','addtime');
				return $unaudit_product;
			}
			if(!empty($unaudit_product)){
				$unaudit_arr=array_merge($unaudit_arr,$unaudit_product);
			}
		}
		if(channelsinfo('message','statu')==1){
			$unaudit_message=$this->get_unaudit_total('message',array('statu'=>0)); //未审核留言列表
			if($condition['cmark']=='message'){
				if($condition['title']!=''){
					foreach ($unaudit_message as $v){
						if(stristr($v['title'], $condition['title'])){ //找到匹配字串
							$v['title']=highLight($v['title'], $condition['title']);
							$newunaudit_arr[]=$v;
						}
					}
					$newunaudit_arr=selectSort($newunaudit_arr,'bts','addtime');
					return $newunaudit_arr;
				}
				$unaudit_message=selectSort($unaudit_message,'bts','addtime');
				return $unaudit_message;
			}
			if(!empty($unaudit_message)){
				$unaudit_arr=array_merge($unaudit_arr,$unaudit_message);
			}
		}
		if(channelsinfo('recruitment','statu')==1){
			$unaudit_recruitment=$this->get_unaudit_total('recruitment',array('statu'=>0)); //未审核招聘信息列表
			if($condition['cmark']=='recruitment'){
				if($condition['title']!=''){
					foreach ($unaudit_recruitment as $v){
						if(stristr($v['title'], $condition['title'])){ //找到匹配字串
							$v['title']=highLight($v['title'], $condition['title']);
							$newunaudit_arr[]=$v;
						}
					}
					$newunaudit_arr=selectSort($newunaudit_arr,'bts','addtime');
					return $newunaudit_arr;
				}
				$unaudit_recruitment=selectSort($unaudit_recruitment,'bts','addtime');
				return $unaudit_recruitment;
			}
			if(!empty($unaudit_recruitment)){
				$unaudit_arr=array_merge($unaudit_arr,$unaudit_recruitment);
			}
			
		}
// 		if(funsinfo('comment_sys','statu')==1){
// 			$unaudit_comment=$this->get_unaudit_total('comment',array('statu'=>0)); //未审核评论列表
// 			if($condition['cmark']=='comment'){
// 				if($condition['title']!=''){
// 					foreach ($unaudit_comment as $v){
// 						if(stristr($v['title'], $condition['title'])){ //找到匹配字串
// 							$v['title']=highLight($v['title'], $condition['title']);
// 							$newunaudit_arr[]=$v;
// 						}
// 					}
// 					$newunaudit_arr=selectSort($newunaudit_arr,'bts','addtime');
// 					return $newunaudit_arr;
// 				}
// 				$unaudit_comment=selectSort($unaudit_comment,'bts','addtime');
// 				return $unaudit_comment;
// 			}
// 			if(!empty($unaudit_comment)){
// 				$unaudit_arr=array_merge($unaudit_arr,$unaudit_comment);
// 			}
// 		}
		$unaudit_arr=selectSort($unaudit_arr,'bts','addtime');
		if($condition['title']!=''){
			foreach ($unaudit_arr as $v){
				if(stristr($v['title'], $condition['title'])){ //找到匹配字串
					$v['title']=highLight($v['title'], $condition['title']);
					$newunaudit_arr[]=$v;
				}
			}
			$newunaudit_arr=selectSort($newunaudit_arr,'bts','addtime');
			return $newunaudit_arr;
		}
		return  $unaudit_arr;
	}
	private function get_unaudit_total($table,$conditions) {
		return syDB($table)->findAll($conditions,null);
	}
}