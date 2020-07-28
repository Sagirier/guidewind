<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}

class databackup extends syController
{
	function __construct(){
		parent::__construct();
		$this->classa=syClass('syModel');
		$this->title="数据备份恢复";
		$this->a=$this->syArgs('a',1);
		$this->db=$GLOBALS['WP']['db']['dbname'];
		$this->bakdir='system/backup/';
	}
	function index(){
		$p=$GLOBALS['WP']['db']['prefix'];
		$pl=strlen($p);
		$dbs=array();$i=$ii=0;
		$ald=$this->classa->findSql('show table status from `'.$this->db.'`');
		foreach($ald as $v){
			if(substr($v['Name'], 0, $pl)==$p){
				$dbs['wp'][$i]=$v['Name'];$i++;
			}else{
				$dbs['other'][$ii]=$v['Name'];$ii++;
			}
		}
		$this->dbwp=$dbs['wp'];
		$this->dbother=$dbs['other'];
		$this->handle=opendir($this->bakdir);
		$this->display("databackup.html");
	}
	function backup(){
		if($this->syArgs('filesize')<=0){
		    $ret['errno']=100;
		    $ret['res']="未填写分卷文件大小";
		    echo json_encode($ret);
		    exit();
		}
		if(!$this->syArgs('tables',2)){
		    $ret['errno']=101;
		    $ret['res']="请选择需要备份的数据表";
		    echo json_encode($ret);
		    exit();
		}
		set_time_limit(99999999);
		ob_implicit_flush(1);
		ob_end_flush();
		$at=time();
		ini_set('memory_limit',-1);
		$tables=$this->syArgs('tables',2);
		$sql="<?php die();?>";$p=1;$dir=$this->bakdir.date("Y-m-d-H-i-s",time());
		$filename=$dir;
		foreach($tables as $t){
			$c=$this->classa->findSql('show create table '.$t);
			$sql.='DROP TABLE IF EXISTS `'.$t."`\r\n".preg_replace("/\n/","",$c[0]['Create Table'])."\r\n";
		}
		foreach($tables as $t){
			$num_fields=$this->classa->findSql('select * from '.$t);
			foreach($num_fields as $v){
				$tt='';
				$sql.= 'INSERT INTO `'.$t.'` VALUES(';
				foreach($v as $f){$tt.= "'".$f."'".",";}
				$sql.= rtrim($tt,',').')'."\r\n";
				if(strlen($sql)>=$this->syArgs('filesize')*1024){
					if($p==1){$filename.=".php";}else{$filename.="_v".$p.".php";}
					if(write_file($sql,$filename)){
						$ret['res']="<font color='green'>备份成功！</font>生成数据表卷：<br/><font color='red'>".$filename."</font>";
					}else{
					    $ret['errno']=103;
					    $ret['res']="<font color='red'>写入备份文件-".$filename."-失败</font>";
					    echo json_encode($ret);
					    exit();
					}
					$p++;
					$filename=$dir;
					$sql="<?php die();?>";
				}
			}
		}
		if($sql!="<?php die();?>"){
			if($p==1){$filename.=".php";}else{$filename.="_v".$p.".php";}
			if(write_file($sql,$filename)){
			    $ret['res']="<font color='green'>备份成功！</font>生成数据表卷：<br/><font color='red'>".$filename."</font>";
			}
		}
		$ret['errno']=102;
		$ret['time']=(time()-$at)<1 ? 1 : time()-$at;
		set_time_limit(30);
		echo json_encode($ret);
		exit();
	}
	function optimize(){
	    if(!$this->syArgs('tables',2)){
	        $ret['errno']=100;
	        $ret['res']="请选择需要优化的数据表";
	        echo json_encode($ret);
	        exit();
	    }
	    $at=time();
		set_time_limit(99999999);
		ob_implicit_flush(1);
		ob_end_flush();
		ini_set('memory_limit',-1);
		$tables=$this->syArgs('tables',2);
		foreach($tables as $t){
			if($this->classa->findSql('OPTIMIZE TABLE '.$t)){
			    $ret['resa'].='数据表：'.$t.'优化完成<br>';
			}else {
			    $ret['resa'].='数据表：'.$t.'优化失败<br>';
			}
		}
		set_time_limit(30);
		$ret['time']=time()-$at;
		$ret['res']='数据表优化全部完成';
		echo json_encode($ret);
	    exit();
	}
	function recovery(){
	    $at=time();
	    if(!$this->syArgs('serverfile',1)){
	        $ret['errno']=100;
	        $ret['res']="请选择需要恢复的备份";
	        echo json_encode($ret);
	        exit();
	    }
		set_time_limit(99999999);
		ob_implicit_flush(1);
		ob_end_flush();
		ini_set('memory_limit',-1);
		$serverfile=$this->syArgs('serverfile',1);
		$filename=$this->bakdir.$serverfile;
		$volnum=explode(".ph",$serverfile);
		$this->rfiles=array($filename);
		$this->dbbak_file($this->bakdir.$volnum[0].'_v',2);
		foreach($this->rfiles as $v){
			foreach(file($v) as $rsql){
				$sql=str_replace('<?php die();?>','',$rsql);
				$rgo=$this->classa->runSql($sql);
				if(!$rgo){
				    $ret['errno']=101;
        	        $ret['res']="<div class='flush'>".$v."导入失败".$rgo."</div>";
        	        echo json_encode($ret);
        	        exit();
				}
			}
		}
		set_time_limit(30);
		$ret['errno']=102;
		$ret['time']=time()-$at;
		$ret['res']="数据还原全部完成";
		echo json_encode($ret);
		exit();
	}
	private function dbbak_file($filename,$p){
		$file=$filename.$p.'.php';
		if(file_exists($file)){
			$this->rfiles=array_merge($this->rfiles,array($file));
			$p=$p+1;$this->dbbak_file($filename,$p);
		}
	}
}	