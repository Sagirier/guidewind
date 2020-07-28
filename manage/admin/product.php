<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class product extends syController{
	public $pk = "id";
	public $pkn = "nid";
	public $table = "product";
	public $traits = "traits";
	function __construct(){
		parent::__construct();
		$this->gopage=$this->syArgs('page',0,1);
		$this->id=$this->syArgs('id');
		$this->a='contents';
		$this->channels = 'product';
		$this->Class=syClass('c_'.$this->channels);
		$this->sy_class_type=syClass('synavigators');
		$this->navtree=$this->sy_class_type->type_txt();
		$imgtypes_arr=array('jpg','jpeg','png','gif');
		for ($i=0;$i<count($imgtypes_arr);$i++){
			if(in_array($imgtypes_arr[$i],explode(",", $GLOBALS['WP']['ext']['filetype']))){
				$product_type[]="*.".$imgtypes_arr[$i];
			}
		}
		$texttypes_arr=array('jpg','jpeg','png','gif','bmp','pdf','rar','zip','7z','txt','doc','docx','xls','xlsx');
		for ($i=0;$i<count($texttypes_arr);$i++){
			if(in_array($texttypes_arr[$i],explode(",", $GLOBALS['WP']['ext']['filetype']))){
				$text_type[]="*.".$texttypes_arr[$i];
			}
		}
		$this->product_type=join(";",$product_type);
		$this->text_type=join(";",$text_type);
		$this->product_typename='图片';
		$this->text_typename='文件';
		$this->member_group=syDB('member_group')->findAll(array('sys'=>1),' `weight` , `gid`');
		$this->cname=syDB("channels")->find(array('cmark'=>$this->channels));
		$this->sqldb=$GLOBALS['WP']['db']['prefix'].$this->channels;
		$this->conditions=array(
			'nid' => $this->syArgs('product-schbynid'),
			'statu' => $this->syArgs('product-schbystatu'),
			'traits' => $this->syArgs('product-schbytrait'),
			'password' => $this->syArgs('product-schbypass'),
			'title' =>	$this->syArgs('title',1)
		);
		$condition="";
		if($this->conditions['nid']!=0){
			$condition.="and `nid` in(".$this->sy_class_type->leafid($this->conditions['nid']).") ";
		}
		if($this->conditions['statu']==1){
			$this->top_txt='<font color="#23C6C8">已上架</font>的';
			$condition.="and `statu`=1 ";
		}
		if($this->conditions['statu']==2){
			$this->top_txt='<font color="#F00">未上架</font>的';
			$condition.="and `statu`=0 ";
		}
		if($this->conditions['traits']!=''){
			$condition.="and `traits` like '%|".$this->conditions['traits']."|%' ";
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
		$sql='select * from '.$this->sqldb.$condition.' order by `order` desc,`addtime` desc,`statu` desc,`id` desc';
		//获取总数
		$total_count=total_count($this->sqldb.$condition);
		//进行分页输出
		$this->lists = syClass('syModel')->syPager($this->gopage,10,$total_count)->findSql($sql);
		$this->pages = pagetxt(syClass('syModel')->syPager()->getPager());
		$this->traits_lists=$this->get_trait_lists();
		$this->traits_class=syClass('sytraits');
		
	}
	function index(){
		$this->display($this->channels.".html");
	}
	function edit(){
		if(!$this->userClass->checkgo('product_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->product=$this->Class->find(array('id'=>$this->id));
		$this->product_detail=syDB($this->channels."_field")->find(array('aid'=>$this->id));
		$logistics_arr=explode(",",$this->product['logistics']);
		for ($l=0;$l<count($logistics_arr);$l++){
			$logistics_info=explode("|", $logistics_arr[$l]);
			$product_logistics[$logistics_info[0]]=$logistics_info[1];
		}
		$this->product_logistics=$product_logistics;
		if($this->product['picture']!='')$this->pictures=explode(",", $this->product['picture']);
		$this->total_fields=total_count($GLOBALS['WP']['db']['prefix']."fields where `cmark`='product' and `navigators` like '%|".$this->product['nid']."|%' ");
		if($this->syArgs('go')==1){
            $product_arr=array(
                'nid'=>$this->syArgs('product_nid'),
                'statu'=>$this->syArgs('product_statu'),
                'title'=>$this->syArgs('product_title',1),
                'gourl'=>$this->syArgs('product_gourl',1),
                'addtime'=>strtotime($this->syArgs('product_addtime',1)),
                'inventory'=>$this->syArgs('product_inventory'),
                'hints'=>$this->syArgs('product_hints'),
                'lipic'=>$this->syArgs('product_lipic',1),
                'picture'=>$this->syArgs('product_picture',2),
                'order'=>$this->syArgs('product_order'),
                'price'=>$this->syArgs('product_price',3),
                'virtual'=>$this->syArgs('product_virtual'),
                'mrank'=>$this->syArgs('product_mrank'),
                'keywords'=>$this->syArgs('product_keywords',1),
                'description'=>$this->syArgs('product_description',1),
            );
	        if(is_array($this->syArgs('product_traits',2)) && $this->syArgs('product_traits',2)!=''){
	            $product_arr=array_merge($product_arr,array('traits'=>'|'.implode('|',$this->syArgs('product_traits',2)).'|'));
	        }
	        if(is_array($this->syArgs('product_logistics',2)) && $this->syArgs('product_logistics',2)!='' && $this->syArgs('product_virtual')!=1){
	            foreach ($this->syArgs('product_logistics',2) as $l){
	                $logistics_arr[]=$l['name']."|".$l['gold'];
	            }
	            $logistics=join(",", $logistics_arr);
	            $product_arr=array_merge($product_arr,array('logistics'=>$logistics));
	        }else{
	            $product_arr=array_merge($product_arr,array('logistics'=>''));
	        }
	        if(is_array($this->syArgs('product_picture',2)) && $this->syArgs('product_picture',2)!=''){
	            $pictures=$this->syArgs('product_picture',2);
	            $pictures_name=$this->syArgs('product_picturename',2);
	            $pictures_order=$this->syArgs('product_pictureorder',2);
	            $i=0;
	            while ($i<count($pictures)){
	                $pictures_str_arr[]=$pictures[$i]."|".$pictures_name[$i]."|".$pictures_order[$i];
	                $i++;
	            }
	            $pictures_str=join(",", $pictures_str_arr);
	            $product_arr=array_merge($product_arr,array('picture'=>$pictures_str));
	        }else {
	            $product_arr=array_merge($product_arr,array('picture'=>''));
	        }
	        if($this->syArgs('product_password',1)!=''){
	            $product_arr=array_merge($product_arr,array('password'=>syPass($this->syArgs('product_password',1),'INCODE')));
	        }else {
	            $product_arr=array_merge($product_arr,array('password'=>''));
	        }
	        $field_row=array(
	            'detail'=>code_body($this->syArgs('product_detail',4))
	        );
	        $product_field_arr=syClass("c_fields")->findAll(" `cmark`='".$this->channels."' and `navigators` like '%|".$this->syArgs('product_nid')."|%' ");
	        foreach ($product_field_arr as $v){
	            $ns='';$n=array();
	            if($v['ftype']=='varchar' || $v['ftype']=='file' || $v['ftype']=='radio'){ $ns=$this->syArgs('product_'.$v['fmark'],1);}
	            if($v['ftype']=='int'){ $ns=$this->syArgs('product_'.$v['fmark']);}
	            if($v['ftype']=='money'){ $ns=$this->syArgs('product_'.$v['fmark'],3);}
	            if($v['ftype']=='text'){ $ns=$this->syArgs('product_'.$v['fmark'],4);}
	            if($v['ftype']=='date'){ $ns=strtotime($this->syArgs('product_'.$v['fmark'],1));}
	            if($v['ftype']=='multifile'){
	                $files=$this->syArgs('product_'.$v['fmark'].'file',2);
	                if($files){
	                    $num=$this->syArgs('product_'.$v['fmark'].'num',2);
	                    $txt=$this->syArgs('product_'.$v['fmark'].'txt',2);$ns='';
	                    natsort($num);
	                    foreach($num as $k=>$v){
	                        $ns.=$files[$k].'|'.$txt[$k];
	                    }
	                    $ns=substr($ns,3);
	                }
	            }
	            if($v['ftype']=='select'){
	                if($this->syArgs('product_'.$v['fmark'],2)){
	                    $ns='|'.implode('|',$this->syArgs('product_'.$v['fmark'],2)).'|';
	                }else{
	                    $ns='';
	                }
	            }
	            $n=array($v['fmark']=> $ns);
	            $field_row=array_merge($field_row,$n);
	        }
			if($this->Class->update(array('id'=>$this->id),$product_arr)){
				if(!syDB($this->table."_field")->find(array('aid'=>$this->id))){
					$field_row=array_merge($field_row,array('aid'=>$this->id));
					syDB($this->table."_field")->create($field_row);
				}else{
					syDB($this->table."_field")->update(array('aid'=>$this->id),$field_row);
				}
				deleteDir($GLOBALS['WP']['sp_cache']);
				message("下载文件[<font color='#1AB394'>".$this->product['title']."</font>]修改成功",'?action='.$this->channels);
			}else {
				message("下载文件[<font color='#1AB394'>".$this->product['title']."</font>]修改失败",'?action='.$this->channels);
			}
		}
		$this->display($this->channels."_edit.html");
	}
	function add(){
		if(!$this->userClass->checkgo('product_add')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$this->nid=$this->syArgs('nid');
		$this->total_fields=total_count($GLOBALS['WP']['db']['prefix']."fields where `cmark`='product' and `navigators` like '%|".$this->nid."|%' ");
		if($this->syArgs('go')==1){
		    $product_arr=array(
		        'nid'=>$this->syArgs('product_nid'),
		        'statu'=>$this->syArgs('product_statu'),
		        'title'=>$this->syArgs('product_title',1),
		        'gourl'=>$this->syArgs('product_gourl',1),
		        'addtime'=>strtotime($this->syArgs('product_addtime',1)),
		        'inventory'=>$this->syArgs('product_inventory'),
		        'hints'=>$this->syArgs('product_hints'),
		        'lipic'=>$this->syArgs('product_lipic',1),
		        'picture'=>$this->syArgs('product_picture',2),
		        'order'=>$this->syArgs('product_order'),
		        'price'=>$this->syArgs('product_price',3),
		        'virtual'=>$this->syArgs('product_virtual'),
		        'mrank'=>$this->syArgs('product_mrank'),
		        'keywords'=>$this->syArgs('product_keywords',1),
		        'description'=>$this->syArgs('product_description',1),
		        'user' => $this->user['auser']
		    );
		    if(is_array($this->syArgs('product_traits',2)) && $this->syArgs('product_traits',2)!=''){
		        $product_arr=array_merge($product_arr,array('traits'=>'|'.implode('|',$this->syArgs('product_traits',2)).'|'));
		    }
		    if(is_array($this->syArgs('product_logistics',2)) && $this->syArgs('product_logistics',2)!='' && $this->syArgs('product_virtual')!=1){
		        foreach ($this->syArgs('product_logistics',2) as $l){
		            $logistics_arr[]=$l['name']."|".$l['gold'];
		        }
		        $logistics=join(",", $logistics_arr);
		        $product_arr=array_merge($product_arr,array('logistics'=>$logistics));
		    }else{
		        $product_arr=array_merge($product_arr,array('logistics'=>''));
		    }
		    if(is_array($this->syArgs('product_picture',2)) && $this->syArgs('product_picture',2)!=''){
		        $pictures=$this->syArgs('product_picture',2);
		        $pictures_name=$this->syArgs('product_picturename',2);
		        $pictures_order=$this->syArgs('product_pictureorder',2);
		        $i=0;
		        while ($i<count($pictures)){
		            $pictures_str_arr[]=$pictures[$i]."|".$pictures_name[$i]."|".$pictures_order[$i];
		            $i++;
		        }
		        $pictures_str=join(",", $pictures_str_arr);
		        $product_arr=array_merge($product_arr,array('picture'=>$pictures_str));
		    }else {
		        $product_arr=array_merge($product_arr,array('picture'=>''));
		    }
		    if($this->syArgs('product_password',1)!=''){
		        $product_arr=array_merge($product_arr,array('password'=>syPass($this->syArgs('product_password',1),'INCODE')));
		    }else {
		        $product_arr=array_merge($product_arr,array('password'=>''));
		    }
		    $field_row=array(
		        'detail'=>code_body($this->syArgs('product_detail',4))
		    );
		    $product_field_arr=syClass("c_fields")->findAll(" `cmark`='".$this->channels."' and `navigators` like '%|".$this->syArgs('product_nid')."|%' ");
		    foreach ($product_field_arr as $v){
		        $ns='';$n=array();
		        if($v['ftype']=='varchar' || $v['ftype']=='file' || $v['ftype']=='radio'){ $ns=$this->syArgs('product_'.$v['fmark'],1);}
		        if($v['ftype']=='int'){ $ns=$this->syArgs('product_'.$v['fmark']);}
		        if($v['ftype']=='money'){ $ns=$this->syArgs('product_'.$v['fmark'],3);}
		        if($v['ftype']=='text'){ $ns=$this->syArgs('product_'.$v['fmark'],4);}
		        if($v['ftype']=='date'){ $ns=strtotime($this->syArgs('product_'.$v['fmark'],1));}
		        if($v['ftype']=='multifile'){
		            $files=$this->syArgs('product_'.$v['fmark'].'file',2);
		            if($files){
		                $num=$this->syArgs('product_'.$v['fmark'].'num',2);
		                $txt=$this->syArgs('product_'.$v['fmark'].'txt',2);$ns='';
		                natsort($num);
		                foreach($num as $k=>$v){
		                    $ns.=$files[$k].'|'.$txt[$k];
		                }
		                $ns=substr($ns,3);
		            }
		        }
		        if($v['ftype']=='select'){
		            if($this->syArgs('product_'.$v['fmark'],2)){
		                $ns='|'.implode('|',$this->syArgs('product_'.$v['fmark'],2)).'|';
		            }else{
		                $ns='';
		            }
		        }
		        $n=array($v['fmark']=> $ns);
		        $field_row=array_merge($field_row,$n);
		    }
		    $resaid=$this->Class->create($product_arr);
			if($resaid){
				$field_row=array_merge($field_row,array('aid'=>$resaid));
				syDB($this->channels."_field")->create($field_row);
				deleteDir($GLOBALS['WP']['sp_cache']);
				message_c('下载文件添加成功','?action='.$this->channels,'?action='.$this->channels.'&o=add&nid='.$this->product_arr['nid']);
			}else {
				message("下载文件添加失败，请重新发布...",'?action='.$this->channels);
			}
		}
		$this->display($this->channels."_edit.html");
	}
	function del(){ //还有下载文件的订单...没有删除，之后再处理
		if(!$this->userClass->checkgo('product_del')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		if($this->Class->delete(array('id'=>$this->id)) && syDB($this->table."_field")->delete(array('aid'=>$this->id))){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message('删除下载文件[id='.$this->id.']成功','?action='.$this->channels);
		}else {
			message("删除下载文件失败，请至数据库手动删除...",'?action='.$this->channels);
		}
	}
	function get_trait_lists(){
		return syDB($this->traits)->findALL(array('cmark'=>$this->channels));
	}
	function alledit(){
		if(!$this->userClass->checkgo('product_edit')){
			message("您没有该操作的管理员权限",null,3,7);
		}
		$formnum=$this->syArgs('formnum');
		$types_arr=$this->syArgs('types',2);
		$this->operate_allProduct($formnum,$types_arr);
	}
	function operate_allProduct($formnum,$types_arr){
		$id_str=join(", ", $types_arr);
		$where_id_str="`id` IN (".$id_str.")";
		$where_aid_str="`aid` IN (".$id_str.")";
		switch ($formnum){
			case 1:
				if(!$types_arr){
					message('您尚未选择任何下载文件...','?action='.$this->channels,0,0);
				}
				$row['statu']=1;
				if(syDB($this->table)->update($where_id_str,$row)){
					message('批量审核成功','?action='.$this->channels);
				}else {
					message('批量审核失败...','?action='.$this->channels);
				}
				break;
			case 2:
				if(!$types_arr){
					message('您尚未选择任何下载文件...','?action='.$this->channels,0,0);
				}
				$row['statu']=0;
				if(syDB($this->table)->update($where_id_str,$row)){
					message('批量取消审核成功','?action='.$this->channels);
				}else {
					message('批量取消审核失败...','?action='.$this->channels);
				}
				break;
			case 3:
				if(!$types_arr){
					message('您尚未选择任何下载文件...','?action='.$this->channels,0,0);
				}
				if(syDB($this->table)->delete($where_id_str) && syDB($this->table."_field")->delete($where_aid_str)){
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
 						message("下载文件[".$condition_id."]顺序更改失败...",'?action='.$this->channels);
 					}
				}
				message('下载文件顺序更改成功','?action='.$this->channels);
				break;
			case 5:
				if(!$types_arr){
					message('您尚未选择任何下载文件...','?action='.$this->channels,0,0);
				}
				if($this->syArgs('pnid')){
					$row_nid['nid']=$this->syArgs('pnid');
					$resnid=$this->Class->update($where_id_str,$row_nid);
				}
				if($virt=$this->syArgs('pvid')){
					if($virt==1){
						$row_virtual['virtual']=0;
					}else{
						$row_virtual['virtual']=1;
					}
					$resvirtual=$this->Class->update($where_id_str,$row_virtual);
				}
				if($traits=$this->syArgs('ptid',2)){
					if(in_array('clear', $traits)){
						$row_traits['traits']='';
					}else {
						$row_traits['traits']=join(",", $traits);
					}
					$restrait=$this->Class->update($where_id_str,$row_traits);
				}
				if($resnid && !$resvirtual && !$restrait){
					message("批量更改栏目成功",'?action='.$this->channels);
				}elseif(!$resnid && $resvirtual && !$restrait){
					message("批量更改下载文件种类成功",'?action='.$this->channels);
				}elseif (!$resnid && !$resvirtual && $restrait){
					message("批量更改下载文件标签成功",'?action='.$this->channels);
				}elseif($resnid && $resvirtual && !$restrait){
					message("批量更改栏目和下载文件种类成功",'?action='.$this->channels);
				}elseif($resnid && !$resvirtual && $restrait){
					message("批量更改栏目和下载文件标签成功",'?action='.$this->channels);
				}elseif (!$resnid && $resvirtual && $restrait){
					message("批量更改下载文件种类和下载文件标签成功",'?action='.$this->channels);
				}elseif ($resnid && $resvirtual && $restrait){
					message("批量更改栏目和下载文件种类和下载文件标签成功",'?action='.$this->channels);
				}else {
					message("批量操作失败...",'?action='.$this->channels);
				}
				break;
		}
		deleteDir($GLOBALS['WP']['sp_cache']);
	}
}	