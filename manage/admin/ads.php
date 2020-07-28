<?php
if (!$_SESSION['auser']){jump("?action=login");}
class ads extends syController{
	function __construct(){
		parent::__construct();	
		if(!$this->userClass->checkgo('website_ads')){
			message("您没有该栏目的管理员权限",null,3,7);
		}
		$this->a='website';
		$this->id=$this->syArgs('id');
		$this->taid=$this->syArgs('taid');
		$this->Class=syClass('c_ads');
		$this->sqldb=$GLOBALS['WP']['db']['prefix'].'ads';
		$this->conditions=array(
			'taid' => $this->syArgs('ads-schbytaid'),
			'statu' => $this->syArgs('ads-schbystatu'),
			'type' => $this->syArgs('ads-schbytype'),
			'name' =>	$this->syArgs('name',1)
		);
		$this->o=$this->syArgs('o',1);
		if(($this->o=='add'||$this->o=='edit') && $this->syArgs('go')==1){
			$this->ads_arr=array(
				'taid'=>$this->syArgs('ads_taid'),
				'name'=>$this->syArgs('ads_name',1),
				'order'=>$this->syArgs('ads_order'),
				'adsw'=>$this->syArgs('ads_adsw'),
				'adsh'=>$this->syArgs('ads_adsh'),
				'type'=>$this->syArgs('ads_type'),
				'target'=>$this->syArgs('ads_target',1),
				'gourl'=>$this->syArgs('ads_gourl',1),
				'adfile'=>$this->syArgs('ads_adfile',1),
				'statu'=>$this->syArgs('ads_statu')
			);
			$ads_adstype=syDB('adstype')->find(array('taid'=>$this->syArgs('ads_taid')));
			if($this->syArgs('ads_adsw')==0){
				$this->ads_arr=array_merge($this->ads_arr,array('adsw'=>$ads_adstype['adsw']));
			}
			if($this->syArgs('ads_adsh')==0){
				$this->ads_arr=array_merge($this->ads_arr,array('adsh'=>$ads_adstype['adsh']));
			}
			switch($this->syArgs('ads_type')){
				case 1:  //文字广告
					$body='<a href="'.$this->syArgs('ads_gourl',1).'" target="_'.$this->syArgs('ads_target',1).'">'.$this->syArgs('ads_name',1).'</a>';
					break;
				case 2:  //图片广告
					$body='<a href="'.$this->syArgs('ads_gourl',1).'" target="_'.$this->syArgs('ads_target',1).'"><img src="'.$this->syArgs('adfile',1).'" width="'.$this->syArgs('ads_adsw').'" height="'.$this->syArgs('ads_adsh').'" /></a>';
					break;
				case 3:  //视频广告
					$body='<embed height="'.$this->syArgs('ads_adsh').'" type="application/x-mplayer2" width="'.$this->syArgs('ads_adsw').'" src="'.$this->syArgs('adfile',1).'" autostart="false" enablecontextmenu="false" classid="clsid:6bf52a52-394a-11d3-b153-00c04f79faa6" />';
					break;
				case 4:  //Flash广告
					$body='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="'.$this->syArgs('ads_adsw').'" height="'.$this->syArgs('ads_adsh').'"><param name="movie" value="'.$this->syArgs('ads_adfile',1).'" /><param name="quality" value="high" /><embed src="'.$this->syArgs('ads_adfile',1).'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$this->syArgs('ads_adsw').'" height="'.$this->syArgs('ads_adsh').'"></embed></object>';
					break;
				case 5:  //自定义广告
					$body=$this->syArgs('ads_body',1);
					break;
			}
			$this->ads_arr=array_merge($this->ads_arr,array('body'=>$body));
		}
		if(($this->o=='tadd'||$this->o=='tedit') && $this->syArgs('go')==1){
			$this->adstype_arr=array(
				'name'=>$this->syArgs('adstype_name'.$this->taid,1),
				'adsw'=>$this->syArgs('adstype_adsw'.$this->taid),
				'adsh'=>$this->syArgs('adstype_adsh'.$this->taid)
			);
		}
		$this->lists=$this->get_lists($this->conditions);
		$this->Classtype=syClass('c_adstype');
		$this->adstype=syDB('adstype')->findAll();
	}
	function index(){
		$this->display("ads.html");
	}
	function edit(){
		$this->ads=$this->Class->find(array('id'=>$this->id));
		$ads_sys_setting=syDB("functions")->find(array('fmark'=>'ads_sys'));
		$ads_sys_arr=json_decode($ads_sys_setting['fvalue'],true);
		$this->filesize=$ads_sys_arr['filemaxsize'];
		$filetype_arr=explode(",", $ads_sys_arr['filetype']);
		switch ($this->ads['type']){
			case 1: //文字广告
				$this->ads_type='';
				$this->ads_typename='';
				break;
			case 2:  //图片广告
				$imgtypes_arr=array('jpg','jpeg','png','gif','bmp');
				for ($i=0;$i<count($imgtypes_arr);$i++){
					if(in_array($imgtypes_arr[$i],$filetype_arr)){
						$ads_type[]="*.".$imgtypes_arr[$i];
					}
				}
				$this->ads_type=join(";",$ads_type);
				$this->ads_typename='图片';
				break;
			case 3:  //flash广告
				$flashtypes_arr=array('flv','swf');
				for ($j=0;$j<count($flashtypes_arr);$j++){
					if(in_array($flashtypes_arr[$j],$filetype_arr)){
						$ads_type[]="*.".$flashtypes_arr[$j];
					}
				}
				$this->ads_type=join(";", $ads_type);
				$this->ads_typename='Flash';
				break;
			case 4:  //视频广告
				if(in_array("mp4",$filetype_arr)) $this->ads_type='*.mp4';
				$this->ads_typename='视频';
				break;
			case 5:	 //自定义广告
				$pertypes_arr=array('jpg','jpeg','png','gif','bmp','flv','swf','mp4');
				for ($k=0;$k<count($pertypes_arr);$k++){
					if(in_array($pertypes_arr[$k],$filetype_arr)){
						$ads_type[]="*.".$pertypes_arr[$k];
					}
				}
				$this->ads_type=join(";", $ads_type);
				$this->ads_typename='全部';
				break;
		}
		if($this->syArgs('go')==1){
			if($this->Class->update(array('id'=>$this->id),$this->ads_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message('广告修改成功','?action=ads');
			}else {
				message('广告修改失败');
			}
		}
		$this->display("ads_edit.html");
	}
	function add(){
		if($this->syArgs('go')==1){
			if($this->Class->create($this->ads_arr)){
				deleteDir($GLOBALS['WP']['sp_cache']);
				message('广告添加成功','?action=ads');
			}else {
				message('广告添加失败','?action=ads');
			}
		}
		$this->display("ads_edit.html");
	}
	function del(){
		if($this->Class->delete(array('id'=>$this->id))){
			deleteDir($GLOBALS['WP']['sp_cache']);
			message('删除广告[id='.$this->id.']成功','?action=ads');
		}else {
			message("删除广告失败，请至数据库手动删除...",'?action=ads');
		}
	}
	function get_lists($conditions){
		if($conditions['taid']!=''){
			$condition.="and `taid`=".$conditions['taid']." ";
		}
		if($conditions['statu']==1){
			$this->top_txt='<font color="#23C6C8">显示</font>的';
			$condition.="and `statu`=1 ";
		}
		if($conditions['statu']==2){
			$this->top_txt='<font color="#F00">隐藏</font>的';
			$condition.="and `statu`=0 ";
		}
		if($conditions['type']!=''){
			$condition.="and `type`=".$conditions['type']." ";
		}
		if($conditions['name']!=''){
			$condition.="and `name` like '%".$conditions['name']."%' ";
		}
		if($condition!=''){$condition=' where '.substr($condition,3);}
		$sql='select * from '.$this->sqldb.$condition.' order by `order` desc,`id` desc';
		$this->lists = $this->Class->findSql($sql);
		return $this->lists;
	}
	function tedit(){
		if($this->syArgs('go')==1){
			if($this->Classtype->update(array('taid'=>$this->taid),$this->adstype_arr)){
				message('广告位修改成功','?action=ads&o=tedit');
			}else {
				message('广告位修改失败','?action=ads&o=tedit');
			}
		}
		$this->display("ads.html");
	}
	function tadd(){
		if ($this->syArgs('go')==1){
			if($this->adstype_arr['adsw']<=0 || $this->adstype_arr['adsh']<=0){
				$this->adstype_arr=array_merge($this->adstype_arr,array('adsw' => 100,'adsh' => 100,));
			}
			deleteDir($GLOBALS['WP']['sp_cache']);
			if(syDB('adstype')->create($this->adstype_arr)){
				message("广告位创建成功","?action=ads&o=tedit");
			}else{message("广告位创建失败，请重新提交","?action=ads&o=tedit");}
		}
		$this->display("ads.html");
	}
	function tdel(){
		$type_ids=$this->Class->findAll(array('taid'=>$this->taid));
		foreach ($type_ids as $d){
			$ids[]=$d['id'];
		}
		$id_str=join(",", $ids);
		$delSql="DELETE FROM ".$this->sqldb." WHERE `id` IN (".$id_str.")";
		deleteDir($GLOBALS['WP']['sp_cache']);
		if($this->Classtype->delete(array('taid'=>$this->taid)) && $this->Class->runSql($delSql)){
			message('广告位删除成功，并且下属广告全部删除成功','?action=ads&o=tedit');
		}else {
			message('广告位删除失败，下属广告删除失败，请至数据库手动删除','?action=ads&o=tedit');
		}
		$this->display("ads.html");
	}
	function alledit(){
		$formnum=$this->syArgs('formnum');
		$types_arr=$this->syArgs('types',2);
		$this->operate_allAds($formnum,$types_arr);
	}
	function operate_allAds($formnum,$types_arr){
		$id_str=join(", ", $types_arr);
		$where_id_str = "`id` IN (".$id_str.")";
		switch ($formnum){
			case 1:
				if(!$types_arr){
					message('您尚未选择任何广告...','?action=ads',0,0);
				}
				$row['statu']=1;
				deleteDir($GLOBALS['WP']['sp_cache']);
				if($this->Class->update($where_id_str,$row)){
					message('批量显示广告成功','?action=ads');
				}else {
					message('批量显示广告失败...','?action=ads');
				}
				break;
			case 2:
				if(!$types_arr){
					message('您尚未选择任何广告...','?action=ads',0,0);
				}
				$row['statu']=0;
				deleteDir($GLOBALS['WP']['sp_cache']);
				if($this->Class->update($where_id_str,$row)){
					message('批量隐藏广告成功','?action=ads');
				}else {
					message('批量隐藏广告失败...','?action=ads');
				}
				break;
			case 3:
				if(!$types_arr){
					message('您尚未选择任何广告...','?action=ads',0,0);
				}
				deleteDir($GLOBALS['WP']['sp_cache']);
				if($this->Class->delete($where_id_str)){
					message('批量删除成功','?action=ads');
				}else {
					message('批量删除失败...','?action=ads');
				}
				break;
			case 4:
				$orders=$this->syArgs('orders',2);
				deleteDir($GLOBALS['WP']['sp_cache']);
				foreach ($orders as $id => $order){
					$condition_id=$this->pk."=".$id;
					$row_id['order']=$order;
					if(!$this->Class->update($condition_id,$row_id)){
						message("广告[".$condition_id."]顺序更改失败...",'?action=ads');
					}
				}
				message('广告顺序更改成功','?action=ads' );
				break;
			case 5:
				if(!$types_arr){
					message('您尚未选择任何广告...','?action=ads',0,0);
				}
				if($this->syArgs('ataid')){
					$row_taid['taid']=$this->syArgs('ataid');
					deleteDir($GLOBALS['WP']['sp_cache']);
					$restaid=$this->Class->update($where_id_str,$row_taid);
				}
				if($restaid){
					message("批量更改广告位成功",'?action=ads');
				}else {
					message("批量更改广告位失败...",'?action=ads');
				}
				break;
		}
	}
}