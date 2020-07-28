<?php
	class Template{
		public $template_dir = null;
		public $template_tpl = null;
		public $no_compile_dir = true;
		private $_vars = array();
		public function assign($key, $value = null){
			if (is_array($key)){
				foreach($key as $var => $val)if($var != "")$this->_vars[$var] = $val;
			}else{
				if ($key != "")$this->_vars[$key] = $value;
			}
		}
		public function templateExists($tplname){
			if (is_readable(realpath($this->template_dir).'/'.$tplname))return TRUE;
			if (is_readable($tplname))return TRUE;
			return FALSE;
		}
		public function template_err($msg){
			exit('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.$msg);
		}
		public function template_err_check($a,$b,$msg){
			if($a!=$b)$this->template_err($this->template_err_tpl.'模板中存在不完整'.$msg.'标签，请检查是否遗漏{'.$msg.'}开始或结束符');
		}
		public function template_exists($tplname){return $this->templateExists($tplname);}
		public function registerPlugin(){}
		public function display($tplname){
			if(is_readable(realpath($this->template_dir.'/'.$tplname))){
				$tplpath = realpath($this->template_dir.'/'.$tplname);
			}else{
				$this->template_err('无法找到模板 '.$tplname);
			}
			$templateverify=stripos($tplpath,realpath($this->template_dir));
			if($templateverify===false||$templateverify!==0)$this->template_err('路径超出模板文件夹');
			$this->template_err_tpl=$tplname;
			extract($this->_vars);
			if(syExt("view_admin")=='admin'||$tplname==$GLOBALS['WWW'].'system/uploads.php'){
				$template_tpl=$tplpath;
				include $template_tpl;
			}else{
				$cache_time=syExt("cache_time");
				if($cache_time>0)$this->sycache='syCache('.$cache_time.')->';
				$template_tpl=str_replace('/','_',$tplname);
				$template_tpl=str_replace('.html','.php',$template_tpl);
				$template_tpl=realpath($this->template_tpl).'/'.$template_tpl;
				if(syExt("cache_auto")==1 || !is_readable($template_tpl) || filemtime($tplpath)>filemtime($template_tpl)){
					if(!is_dir($GLOBALS['WP']['view']['config']['template_tpl'].'/'))__mkdirs($GLOBALS['WP']['view']['config']['template_tpl'].'/');
					$fp_tp=@fopen($tplpath,"r");
					$fp_txt=@fread($fp_tp,filesize($tplpath));
					@fclose($fp_tp);
					$fp_txt=$this->template_html($fp_txt);
					$fpt_tpl=@fopen($template_tpl,"w");
					@fwrite($fpt_tpl,$fp_txt);
					@fclose($fpt_tpl);
					if(is_readable($template_tpl)!==true)$this->template_err('无法找到模板缓存，请刷新后重试，或者检查系统文件夹权限');
				}
				$enable_gzip=syExt('enable_gzip');
				if( $enable_gzip==1 ){
					GLOBAL $__template_compression_level;
					$__template_compression_level=syExt('enable_gzip_level');
					ob_start('template_ob_gzip');
				}
				include $template_tpl;
			}
		}
		private function template_html($content){
			preg_match_all('/\{include=\"(.*?)\"\}/si',$content,$i);
			foreach($i[0] as $k=>$v){
				$content=str_ireplace($v,$this->template_html_include(strtolower($i[1][$k])),$content);
			}
			preg_match_all('/\{getdata (.*?)\}/si',$content,$i);
			$this->template_err_check(substr_count($content, '{/getdata}'),count($i[0]),'getdata');
			foreach($i[0] as $k=>$v){
				$content=str_ireplace($v,$this->template_html_loop(strtolower($i[1][$k])),$content);
			}
			$content=str_ireplace('{/getdata}','<?php } ?>',$content);
		
			preg_match_all('/\{sql (.*?)\}/si',$content,$i);
			$this->template_err_check(substr_count($content, '{/sql}'),count($i[0]),'sql');
			foreach($i[0] as $k=>$v){
				$content=str_ireplace($v,$this->template_html_sql(strtolower($i[1][$k])),$content);
			}
			$content=str_ireplace('{/sql}','<?php } ?>',$content);
		
			preg_match_all('/\{if(.*?)\}/si',$content,$i);
			$this->template_err_check(substr_count($content, '{/if}'),count($i[0]),'if');
			foreach($i[0] as $k=>$v){
				$content=str_ireplace($v,'<?php if'.$i[1][$k].'{ ?>',$content);
			}
			$content=str_ireplace('{else}','<?php }else{ ?>',$content);
			$content=str_ireplace('{/if}','<?php } ?>',$content);
		
			preg_match_all('/\{foreach(.*?)\}/si',$content,$i);
			$this->template_err_check(substr_count($content, '{/foreach}'),count($i[0]),'foreach');
			foreach($i[0] as $k=>$v){
				$content=str_ireplace($v,'<?php foreach('.$i[1][$k].'){ ?>',$content);
			}
			$content=str_ireplace('{/foreach}','<?php } ?>',$content);
		
			preg_match_all('/\{\$(.*?)\}/si',$content,$i);
			foreach($i[0] as $k=>$v){
				$content=str_ireplace($v,'<?php echo $'.$i[1][$k].' ?>',$content);
			}
			preg_match_all('/\{fun (.*?)\}/si',$content,$i);
			foreach($i[0] as $k=>$v){
				$content=str_ireplace($v,'<?php echo '.$i[1][$k].' ?>',$content);
			}
			return $content;
		}
		private function template_html_include($filename){
			$filename=realpath($this->template_dir).'/'.trim($filename);
			$txt=@fread(@fopen($filename,"r"),filesize($filename));
			$txt=$this->template_html($txt);
			@fclose($fp_tpl);
			return $txt;
		}
		private function template_html_loop($f){
			preg_match_all('/.*?(\s*.*?=.*?[\"|\'].*?[\"|\']\s).*?/si',' '.$f.' ',$aa);
			$a=array();foreach($aa[1] as $v){$t=explode('=',trim(str_replace(array('"',"'"),'',$v)));$a=array_merge($a,array(trim($t[0]) => trim($t[1])));}
			$dbleft=$GLOBALS['WP']['db']['prefix'];
			if(strpos($a['data_table'],'$')!==FALSE){$a['data_table']='".'.$a['data_table'].'."';}
			if($a['data_table']=='channel'){$db='channel';$molds=$a['cmark'];}else{$db=$dbleft.$a['data_table'];}
			if($a['data_limit']!=''){$limit=' limit '.$a['data_limit'];}else{$limit='';}
			if($a['mark']!=''){$mark=$a['mark'];}else{$mark='v';}
			if($a['data_order']!=''){
				$os=explode(",", $a['data_order']);
				for ($i=0;$i<count($os);$i++){
					$od=explode("|", $os[$i]);
					$nos[]="`".$od[0]."` ".$od[1];
				}
				$order='order by '.join(",", $nos);
			}else{$order='';}
			unset($a['data_table']);unset($a['data_cmark']);unset($a['data_order']);unset($a['orderway']);unset($a['data_limit']);unset($a['mark']);
			$pages='';
            $fields = '';
            $ts = '';
            $w = '';
            $trait = '';
			switch($db){
				case $dbleft.'article':
					$fielddb=syDB('fields')->findAll(array('cmark'=>'article','lists'=>1),' order DESC,fid ','fmark');
					foreach($fielddb as $v){$fields.=','.$v['fmark'];}
					$field_all='id,nid,sid,title,traits,gourl,addtime,hints,htmlurl,htmlfile,lipic,order,mrank,mgold,statu,keywords,description,password'.$fields;
					foreach($a as $k=>$v){
						if($k=='data_nid'){
							foreach(explode(',',$v) as $t){
								if(strpos($t,'$')!==FALSE){
									$t=preg_replace('/\[.*?\]/', '["nid_leafid"]', $t);
									$ts.=',".'.$t.'."';
								}else{
									$ts.=','.syClass("synavigators",array("article"))->leafid($t);
								}
							}
							$w.='and `nid` in('.substr($ts,1).') ';
                        }else if($k=='data_traits'){
							$w.="and ";
							if($v!=''){
								$w.="`traits` like '%|".$v."|%' ";
							}
						}else if($k=='data_idrange'){
							$w.="and ";
							$idrange=explode("-", $v);
							$w.=' `id` > '.$idrange[0].' and `id` < '.$idrange[1].' ';
						}else if($k=='data_lipic'){
							$w.="and ";
							if($v==1){$w.="`lipic`!='' ";}if($v==2){$w.="`lipic`='' ";}
						}else if($k=='data_password'){
							$w.="and ";
							if($v==1){$w.="`password`!='' ";}if($v==2){$w.="`password`='' ";}
						}else if($k=='data_page'){
							$page=explode(',',$v);
							$limit='';
						}else{
							$k=str_ireplace("data_", null, $k);
							if(strpos($field_all,$k)!==FALSE){
								if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
								$w.="and `".$k."`='".$v."' ";
							}
						}
					}$w=' where `statu`=1 '.$w;
					if($order==''){$order=' order by `order` desc,`addtime` desc,`id` desc';}
					if($fielddb){
						$sql='select '.$field_all.' from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order.$limit;
					}else{
                        $sql='select * from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order.$limit;
					}
					$txt='<?php $'.$mark.'n=0;';
					if($page){
						$total_count=total_count($db.$w);
						$txt.='$'.$page[0].'_class=syClass("c_article");$table'.$mark.'= $'.$page[0].'_class->syPager(syClass("syController")->syArgs("'.$page[0].'_page",0,1),'.$page[1].','.$total_count.')->findSql("'.$sql.'");$'.$page[0].'=pagetxt($'.$page[0].'_class->syPager()->getPager(),3,"'.$page[0].'_page");';
					}else{
						$txt.='$table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
					}
					$txt.='foreach($table'.$mark.' as $'.$mark.'){ $'.$mark.'["nid_leafid"]=$sy_class_type->leafid($'.$mark.'["nid"]);$'.$mark.'["n"]=$'.$mark.'n=$'.$mark.'n+1; $'.$mark.'["url"]=html_url("article",$'.$mark.'); $'.$mark.'["title"]=stripslashes($'.$mark.'["title"]); $'.$mark.'["description"]=stripslashes($'.$mark.'["description"]); ?>';
					break;
				case $dbleft.'product':
					$fielddb=syDB('fields')->findAll(array('cmark'=>'product','lists'=>1),' order DESC,fid ','fmark');
					foreach($fielddb as $v){$fields.=','.$v['fmark'];}
					$field_all='id,nid,sid,title,traits,gourl,addtime,inventory,record,hints,htmlurl,htmlfile,lipic,order,price,mrank,mgold,statu,keywords,description,password'.$fields;
					foreach($a as $k=>$v){
						if($k=='data_nid'){
							foreach(explode(',',$v) as $t){
								if(strpos($t,'$')!==FALSE){
									$t=preg_replace('/\[.*?\]/', '["nid_leafid"]', $t);
									$ts.=',".'.$t.'."';
								}else{
									$ts.=','.syClass("synavigators",array("product"))->leafid($t);
								}
							}
							$w.='and `nid` in('.substr($ts,1).') ';
						}else if($k=='data_traits'){
							$w.="and ";
							if($v!=''){
								$w.="`traits` like '%|".$v."|%' ";
							}
						}else if($k=='data_idrange'){
							$w.="and ";
							$idrange=explode("-", $v);
							$w.=' `id` > '.$idrange[0].' and `id` < '.$idrange[1].' ';
						}else if($k=='data_lipic'){
							$w.="and ";
							if($v==1){$w.="`lipic`!='' ";}if($v==2){$w.="`lipic`='' ";}
						}else if($k=='data_password'){
							$w.="and ";
							if($v==1){$w.="`password`!='' ";}if($v==2){$w.="`password`='' ";}
						}else if($k=='data_page'){
							$page=explode(',',$v);
							$limit='';
						}else{
							$k=str_ireplace("data_", null, $k);
							if(strpos($field_all,$k)!==FALSE){
								if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
								$w.="and `".$k."`='".$v."' ";
							}
						}
					}$w=' where `statu`=1 '.$w;
					if($order==''){$order=' order by `order` desc,`addtime` desc,`id` desc';}
					if($fielddb){
						$sql='select '.$field_all.' from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order.$limit;
					}else{
						$sql='select * from '.$db.$w.$order.$limit;
					}
					$txt='<?php $'.$mark.'n=0;';
					if($page){
						$total_count=total_count($db.$w);
						$txt.='$'.$page[0].'_class=syClass("c_product");$table'.$mark.'= $'.$page[0].'_class->syPager(syClass("syController")->syArgs("'.$page[0].'_page",0,1),'.$page[1].','.$total_count.')->findSql("'.$sql.'");$'.$page[0].'=pagetxt($'.$page[0].'_class->syPager()->getPager(),3,"'.$page[0].'_page");';
					}else{
						$txt.='$table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
					}
					$txt.='foreach($table'.$mark.' as $'.$mark.'){ $'.$mark.'["nid_leafid"]=$sy_class_type->leafid($'.$mark.'["nid"]);$'.$mark.'["n"]=$'.$mark.'n=$'.$mark.'n+1; $'.$mark.'["url"]=html_url("product",$'.$mark.'); $'.$mark.'["title"]=stripslashes($'.$mark.'["title"]); $'.$mark.'["description"]=stripslashes($'.$mark.'["description"]); ?>';
					break;
				case $dbleft.'message':
					$field_all='id,nid,cmark,rid,title,addtime,order,statu,detail,restatu,uid';
					foreach($a as $k=>$v){
						if($k=='data_nid'){
							foreach(explode(',',$v) as $t){
								if(strpos($t,'$')!==FALSE){
									$t=preg_replace('/\[.*?\]/', '["nid_leafid"]', $t);
									$ts.=',".'.$t.'."';
								}else{
									$ts.=','.syClass("synavigators",array("message"))->leafid($t);
								}
							}
							$w.='and `nid` in('.substr($ts,1).') ';
						}else if($k=='data_statu'){
							$w.="and ";
							if($v==1){$w.="`statu`=1 ";}if($v==2){$w.="`statu`=0 ";}
						}else if($k=='data_idrange'){
							$w.="and ";
							$idrange=explode("-", $v);
							$w.=' `id` > '.$idrange[0].' and `id` < '.$idrange[1].' ';
						}else if($k=='data_restatu'){
							$w.="and ";
							if($v==1){$w.="`restatu`=1 ";}if($v==2){$w.="`restatu`=0 ";}
						}else if($k=='data_page'){
							$page=explode(',',$v);
							$limit='';
						}else{
							$k=str_ireplace("data_", null, $k);
							if(strpos($field_all,$k)!==FALSE){
								if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
								$w.="and ".$k."='".$v."' ";
							}
						}
					}
					if($w!=''){$w=' where '.substr($w,3);}
					if($order==''){$order=' order by `order` desc,`addtime` desc,`id` desc';}
					$sql='select * from '.$db.$w.$order.$limit;
					$txt='<?php ';
					if($page){
						$total_count=total_count($db.$w);
						$txt.='$'.$page[0].'_class=syClass("c_message");$table'.$mark.'= $'.$page[0].'_class->syPager(syClass("syController")->syArgs("'.$page[0].'_page",0,1),'.$page[1].','.$total_count.')->findSql("'.$sql.'");$'.$page[0].'=pagetxt($'.$page[0].'_class->syPager()->getPager(),3,"'.$page[0].'_page");';
					}else{
						$txt.='$table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
					}
					$txt.='foreach($table'.$mark.' as $'.$mark.'){?>';
					break;
				case 'channel':
					if(!syDB('molds')->find(array('molds'=>$molds),null,'molds'))return '';
					$db=$dbleft.$molds;
					$fielddb=syDB('fields')->findAll(array('molds'=>$molds,'lists'=>1),' fieldorder DESC,fid ','fields');
					foreach($fielddb as $v){$fields.=','.$v['fields'];}
					$field_all='id,tid,sid,title,style,trait,gourl,addtime,hits,htmlurl,htmlfile,orders,mrank,mgold,isshow,keywords,description'.$fields;
					foreach($a as $k=>$v){
						if($k=='data_nid'){
							foreach(explode(',',$v) as $t){
								if(strpos($t,'$')!==FALSE){
									$t=preg_replace('/\[.*?\]/', '["nid_leafid"]', $t);
									$ts.=',".'.$t.'."';
								}else{
									$ts.=','.syClass("syclasstype",array("article"))->leafid($t);
								}
							}
							$w.='and `nid` in('.substr($ts,1).') ';
						}else if($k=='trait'){$w.="and ";
						if(strpos($v,',')!==FALSE){
							foreach(explode(',',$v) as $tt){$trait.="or trait like '%,".$tt.",%' ";}
							$w.="(".substr($trait,3).")";
						}else{$w.="trait like '%,".$v.",%' ";}
						}else if($k=='notrait'){$w.="and ";
						if(strpos($v,',')!==FALSE){
							foreach(explode(',',$v) as $tt){$trait.="or trait not like '%,".$tt.",%' ";}
							$w.="(".substr($trait,3).")";
						}else{$w.="trait not like '%,".$v.",%' ";}
						}else if($k=='keywords'){
							$w.="and (`title` like '%".$v."%' or keywords like '%".$v."%')";
						}else if($k=='page'){
							$page=explode(',',$v);
							$limit='';
						}else{
							$k=str_ireplace("data_", null, $k);
							if(strpos($field_all,$k)!==FALSE){
								if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
								$w.="and ".$k."='".$v."' ";
							}
						}
					}$w=' where isshow=1 '.$w;
					if($order==''){$order=' order by orders desc,addtime desc,id desc';}
					if($fielddb){
						$sql='select '.$field_all.' from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order.$limit;
					}else{
						$sql='select * from '.$db.$w.$order.$limit;
					}
					$txt='<?php $'.$mark.'n=0;';
					if($page){
						$total_count=total_count($db.$w);
						$txt.='$'.$page[0].'_class=syClass("c_'.$molds.'");$table'.$mark.'= $'.$page[0].'_class->syPager(syClass("syController")->syArgs("'.$page[0].'_page",0,1),'.$page[1].','.$total_count.')->findSql("'.$sql.'");$'.$page[0].'=pagetxt($'.$page[0].'_class->syPager()->getPager(),3,"'.$page[0].'_page");';
					}else{
						$txt.='$table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
					}
					$txt.='foreach($table'.$mark.' as $'.$mark.'){ $'.$mark.'["tid_leafid"]=$sy_class_type->leafid($'.$mark.'["tid"]);$'.$mark.'["n"]=$'.$mark.'n=$'.$mark.'n+1; $'.$mark.'["url"]=html_url("channel",$'.$mark.',0,0,'.$molds.'); $'.$mark.'["title"]=stripslashes($'.$mark.'["title"]); $'.$mark.'["description"]=stripslashes($'.$mark.'["description"]); ?>';
					break;
				case $dbleft.'navigators':
					$field_all='`nid`,`cmark`,`ngid`,`nname`,`gourl`,`lipic`,`seo_title`,`seo_keywords`,`description`,`order`,`mrank`,`htmldir`,`htmlfile`,`statu`,`password`';
					foreach($a as $k=>$v){
						if($k=='data_sister' && $a['data_ngid']){
							$sister=1;
						}else if($k=='data_nid'){
							$w.='and `nid` in('.$v.') ';
						}else{
							if($k!='data_sister' && strpos($field_all,str_ireplace("data_", null, $k))!==FALSE){
								if($k=='data_ngid'){$p=$v;}
								if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
								$k=str_ireplace("data_", null, $k);
								$w.="and `".$k."`=".$v." ";
							}else if($k=='data_detail'){
								if($v==1)$field_all=$field_all.',detail';
							}
						}
					}
					if(!$a['data_ngid'] && !$a['data_nid']){$w.='and `ngid`=0 ';}
					if($w)$w=" where ".substr($w,3);
					if($order=='')$order=' order by `order` desc,`nid`';
					$sql='select '.$field_all.' from '.$db.$w.$order.$limit;
					if($sister==1){
						if($a['data_statu'])$notarr=',"statu"=>'.$v;
						$txt='<?php $ynid=$type[nid];if(!syDB("navigators")->find(array("ngid"=>'.$p.$notarr.'),null,"nid")){ $yngid=syDB("navigators")->find(array("nid"=>'.$p.$notarr.'),null,"ngid");$type[nid]=$yngid[ngid];} ?>';
					}
					$txt.='<?php $'.$mark.'n=0;$table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
					if($sister==1){$txt.='$type[nid]=$ynid;';}
					$txt.='foreach($table'.$mark.' as $'.$mark.'){ $'.$mark.'["nid_leafid"]=$sy_class_type->leafid($'.$mark.'["nid"]);$'.$mark.'["n"]=$'.$mark.'n=$'.$mark.'n+1; $'.$mark.'["nname"]=stripslashes($'.$mark.'["nname"]);$'.$mark.'["description"]=stripslashes($'.$mark.'["description"]); $'.$mark.'["url"]=html_url("navigators",$'.$mark.'); ?>';
					break;
				case $dbleft.'ads':
					$field_all='`id`,`taid`,`order`,`name`,`type`,`adsw`,`adsh`,`adfile`,`body`,`gourl`,`target`,`statu`';
					foreach($a as $k=>$v){
						if(strpos($field_all,str_ireplace("data_", null, $k))!==FALSE){
							if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
							$k=str_ireplace("data_", null, $k);
							$w.="and ".$k."='".$v."' ";
						}
					}
					$w=" where `statu`=1 ".$w;
					if($order=='')$order=' order by `order` desc,`id` desc';
					$sql='select * from '.$db.$w.$order.$limit;
					$txt='<?php $'.$mark.'n=0;$table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");foreach($table'.$mark.' as $'.$mark.'){ $'.$mark.'["n"]=$'.$mark.'n=$'.$mark.'n+1; $'.$mark.'["name"]=stripslashes($'.$mark.'["name"]); ?>';
					break;
				case $dbleft.'links':
					$field_all='`lid`,`gid`,`order`,`name`,`lipic`,`url`,`statu`';
					foreach($a as $k=>$v){
						if($k=='data_type'){
							if($v=='lipic'){
								$w.="and `lipic`!='' ";
							}
							if($v=='text'){
								$w.="and `lipic`='' ";
							}
						}else{
							if(strpos($field_all,str_ireplace("data_","",$k))!==FALSE){
								if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
								$w.="and ".str_ireplace("data_","",$k)."='".$v."' ";
							}
						}
					}
					$w=" where `statu`=1 ".$w;
					if($order=='')$order=' order by `order` desc,`lid` desc';
					$sql='select * from '.$db.$w.$order.$limit;
					$txt='<?php $table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");foreach($table'.$mark.' as $'.$mark.'){ $'.$mark.'["name"]=stripslashes($'.$mark.'["name"]); ?>';
					break;
				default:
					foreach($a as $k=>$v){
						if($k=='nid'){
							$leafid='$leafid=syClass("synavigators")->leafid('.$v.');';
							$w.='and `nid` in(".$leafid.") ';
						}else{
							if(strpos($v,'$')!==FALSE)$v='".'.$v.'."';
							$w.="and `".$k."`='".$v."' ";
						}
					}
					if($w!=''){$w=' where '.substr($w,3);}
					$sql='select * from '.$db.$w.$order.$limit;
					$txt='<?php '.$leafid.'$table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");foreach($table'.$mark.' as $'.$mark.'){ ';
					if(strpos($db,'[cmark]')!==FALSE){
						$molds=explode('.',$db);
						$txt.='$'.$mark.'["url"]=html_url('.$molds[1].',$'.$mark.');';
					}
					$txt.='?>';
					break;
			}
			return $txt;
		}
		private function template_html_sql($aa){
			preg_match_all('/sql=\"(.*?)\"/si',$aa,$sql);
			preg_match_all('/\sas=\"(.*?)\"/si',$aa,$mark);
			preg_match_all('/\spage=\"(.*?)\"/si',$aa,$pg);
		
			if($mark[1][0]!=''){$mark=$mark[1][0];}else{$mark='v';}
			if($pg[1][0]!=''){$page=explode(',',$pg[1][0]);}
			if($sql[1][0]!=''){
				$sql=$sql[1][0];
				preg_match_all('/\$(.*?)\]/',$sql,$f);
				foreach($f[0] as $k=>$v){
					$sql=str_replace($v,'".$'.$f[1][$k].']."',$sql);
				}
				$sql=str_replace('[pre]',$GLOBALS['G_WP']['db']['prefix'],$sql);
				$txt='<?php ';
				if($page){
					$txt.='$total_count = syClass("syModel")->findSql("SELECT count(*) from ('.$sql.') '.$page[0].'_total_count");$total_count = ceil( $total_count[0]["count(*)"] / '.$page[1].' );$'.$page[0].'_page_on=(int)$_GET["'.$page[0].'_page"];if($'.$page[0].'_page_on<=1){ $'.$page[0].'_page_on=1;}if($'.$page[0].'_page_on>$total_count){ $'.$page[0].'_page_on=$total_count;}if($'.$page[0].'_page_on<=1){ $'.$page[0].'_limit_left=0;}else{ $'.$page[0].'_limit_left='.$page[1].'*($'.$page[0].'_page_on-1);}$'.$page[0].'=pagetxt(array("total_count" => $total_count[0]["count(*)"],"total_count"  => $total_count,"prev_page"   => $'.$page[0].'_page_on - 1,"next_page"   => $'.$page[0].'_page_on + 1,"last_page"   => $total_count,"current_page"=> $'.$page[0].'_page_on,),3,"'.$page[0].'_page");';
					$txt.='$table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("SELECT * FROM ('.$sql.') '.$page[0].'_all_sql limit ".$'.$page[0].'_limit_left.",'.$page[1].'");';
				}else{
					$txt.='$table'.$mark.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
				}
				$txt.='foreach($table'.$mark.' as $'.$mark.'){?>';
			}else{$txt='';}
			return $txt;
		}
}
		
function template_ob_gzip($content){
	if( !headers_sent() && extension_loaded("zlib") && strstr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip") ){
		GLOBAL $__template_compression_level;
		$content = gzencode($content,$__template_compression_level);
		header("Content-Encoding: gzip");
		header("Vary: Accept-Encoding");
		header("Content-Length: ".strlen($content));
	}
	return $content;
}