<!DOCTYPE html>
<html lang="zh">
	<head>
		<meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
	    <title>纱梦CMS - 系统安装</title>
        <meta name="description" content="">
        <meta name="author" content="wind power">
    	<link href="./css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    	<link href="./css/font-awesome.css?v=4.3.0" rel="stylesheet">
	    <link href="install.css" rel="stylesheet">
		<script type="text/javascript" src="./js/bootstrap.min.js?v=3.4.0"></script>
		<script type="text/javascript" src="./js/dialog.js"></script>
		<script type="text/javascript">
			function formCheck(){
				if(document.f.host.value==""){Dialog.alert("请填写Mysql地址");return false;}
				if(document.f.port.value==""){Dialog.alert("请填写Mysql端口");return false;}
				if(document.f.dbname.value==""){Dialog.alert("请填写数据库名");return false;}
				if(document.f.username.value==""){Dialog.alert("请填写数据库帐号");return false;}
				if(document.f.prefix.value==""){Dialog.alert("请填写表前缀");return false;}
				if(document.f.auser.value==""){Dialog.alert("请填写管理员帐号");return false;}
				if(document.f.apass.value==""){Dialog.alert("请填写管理员密码");return false;}
				if(/^[A-Za-z0-9_]+$/.test(document.f.dbname.value)==0){Dialog.alert("数据库名只能为英文、数字、下划线");return false;}
				$("#install_go").html('<strong style="color:#F00; font-size:14px;">正在执行安装，请稍后，安装完成前请勿关闭本页面...</strong>');
			}
		</script>
	</head>
	<body class="light-gray-bg">
		<div class="templatemo-content-widget templatemo-login-widget white-bg install">
			<header class="text-center">
	          <div class="square"></div>
	          <h1>系统安装</h1>
	        </header>
	        <?php
				error_reporting(0);
				if(is_file("install.txt")){echo '系统已经安装，如需重新安装请删除install目录下的install.txt文件';exit;}
				if((int)$_GET['backup']!=1){
					$backup_db=array();
					$fdir='../system/backup/';
					if($dp=@opendir($fdir)){
						while(false!==($file = readdir($dp))) {
							if($file!='.' && $file!='..' && is_file($fdir.$file)) {
								$backup_db=array_merge($backup_db,array($fdir.$file));
							}
						}
						@closedir($dp);
					}
				}
				$go=(int)$_GET['go'];
				if(!$go){
				function file_info($file){
					if (DIRECTORY_SEPARATOR == '/' and @ini_get("safe_mode") == FALSE){
						return is_writable($file);
					}
					if (is_dir($file)){
						$file = rtrim($file, '/').'/is_writable.html';
						if (($fp = @fopen($file,'w+')) === FALSE){
							return FALSE;
						}
				  		fclose($fp);
				  		@chmod($file,0755);
				  		@unlink($file);
						return TRUE;
					}else if ( ! is_file($file) or ($fp = @fopen($file, 'r+')) === FALSE){
						return FALSE;
					}
					fclose($fp);
					return TRUE;
				}
				if (substr(PHP_VERSION, 0, 1) < 5){
					$php="<em>检测未通过</em>运行环境要求PHP版本5！";
				}
				if (!extension_loaded('gd')){
					$gd="<em>检测未通过</em>运行环境要求安装GD库！";
				}else{
					$gd_info=gd_info();
					$gd_info=substr($gd_info['GD Version'], 9, 1);
					if ((int)$gd_info<'2'){
						$gd="<em>提示</em>GD库版本要求2以上，如您确认已安装GD2以上版本，可忽略本提示。";
					}
				}
				if (!file_info('../uploads')){
					$uploads="<em>检测未通过</em>此目录要求可读写";
				}
				if (!file_info('../system/backup')){
					$backup="<em>检测未通过</em>此目录要求可读写";
				}
				if (!file_info('../system/cache')){
					$cache="<em>检测未通过</em>此目录要求可读写";
				}
				if (!file_info('../config.php')){
					$config="<em>检测未通过</em>此文件要求可读写";
				}
				if (!file_info('../styles')){
					$styles="<em>检测未通过</em>此目录要求可读写";
				}
				if (!file_info('../template')){
					$template="<em>检测未通过</em>此目录要求可读写";
				}
				if (!file_info('../system/include.php')){
					$inc="<em>检测未通过</em>此文件要求可读写";
				}
			?>
			<form action="index.php?go=2" method="post" name="f" onsubmit="return formCheck()">
	        	<div class="form-group">
	        		<div class="input-group">
		        		<div class="input-group-addon"><i class="fa fa-link fa-fw"></i></div>
		              	<input name="host" type="text" class="form-control" value="127.0.0.1" />
		              	<div class="input-group-hint">Mysql地址，一般无需修改</div>
		          	</div>
	        	</div>
	        	<div class="form-group">
	        		<div class="input-group">
		        		<div class="input-group-addon"><i class="fa fa-qrcode fa-fw"></i></div>
		        		<input name="port" type="text" class="form-control" value="3306" />
		              	<div class="input-group-hint">Mysql端口，一般无需修改</div>
		          	</div>
	        	</div>
	        	<div class="form-group">
	        		<div class="input-group">
		        		<div class="input-group-addon"><i class="fa fa-table fa-fw"></i></div>
		              	<input name="dbname" type="text" class="form-control" />
		              	<div class="input-group-hint">数据库名，请输入</div>
		          	</div>
	        	</div>
	        	<div class="form-group">
	        		<div class="input-group">
		        		<div class="input-group-addon"><i class="fa fa-user-md fa-fw"></i></div>
		              	<input name="username" type="text" class="form-control" />
		              	<div class="input-group-hint">数据库帐号，请输入</div>
		          	</div>
	        	</div>
	        	<div class="form-group">
	        		<div class="input-group">
		        		<div class="input-group-addon"><i class="fa fa-key fa-fw"></i></div>
		              	<input name="password" type="text" class="form-control" /><div class="input-group-hint">数据库密码，请输入</div>
		          	</div>
	        	</div>
	        	<?php if(empty($backup_db)){?>
	        	<div class="form-group">
	        		<div class="input-group">
		        		<div class="input-group-addon"><i class="fa fa-user fa-fw"></i></div>
		              	<input name="auser" type="text" class="form-control" /><div class="input-group-hint">管理员账号，请输入</div>
		          	</div>
	        	</div>
	        	<div class="form-group">
	        		<div class="input-group">
		        		<div class="input-group-addon"><i class="fa fa-key fa-fw"></i></div>
		              	<input name="apass" type="text" class="form-control" /><div class="input-group-hint">管理员密码，请输入</div>
		          	</div>
	        	</div>
	        	<div class="form-group">
	        		<div class="input-group">
		        		<div class="input-group-addon"><i class="fa fa-th-list fa-fw"></i></div>
		              	<input name="prefix" type="text" class="form-control" value="wp_"/><div class="input-group-hint">数据表前缀，一般无需修改</div>
		          	</div>
	        	</div>
	        	<?php }?>
	        	<?php if(count($backup_db)>0){?>
					<input name="go_backup" type="hidden" value="1" />
	    			<div class="form-group">
		    			<div class="input-group"><h2>检测到您的系统存在数据库备份</h2></div>
		    			<div class="input-group">
		    			<p>选择数据备份：<select name="backup_db">
		        		<?php foreach($backup_db as $v){
							$v=str_replace('../system/backup/','',$v);
							if(false===strpos($v,'_v')){echo "<option value='".$v."'>".$v."</option>";}
						}?>
		    			</select></p>
		        		<p><a href="?backup=1">不恢复数据备份，执行全新安装</a></p>
		    			</div>
		    		</div>
				<?php }?>
	        	<div class="form-group check-group">
		        	<div class="input-group"><h2>环境检测</h2></div>
		        	<div class="input-group"><p>PHP版本：<?php if ($php){echo '<span class="t">'.$php.'</span>';}else{echo '<span class="tt">检测通过</span>';}?></p></div>
	    			<div class="input-group"><p>GD库：<?php if ($gd){echo '<span class="t">'.$gd.'</span>';}else{echo '<span class="tt">检测通过</span>';}?></p></div>
	                <div class="input-group"><p>目录权限：<br/>uploads <?php if ($uploads){echo '<span class="t">'.$uploads.'</span>';}else{echo ' <span class="tt">检测通过</span>';}?><br />system/backup <?php if ($backup){echo '<span class="t">'.$backup.'</span>';}else{echo ' <span class="tt">检测通过</span>';}?><br />system/cache <?php if ($cache){echo '<span class="t">'.$cache.'</span>';}else{echo ' <span class="tt">检测通过</span>';}?><br />config.php <?php if ($config){echo '<span class="t">'.$config.'</span>';}else{echo ' <span class="tt">检测通过</span>';}?><br />styles/ <?php if ($styles){echo '<span class="t">'.$styles.'</span>';}else{echo ' <span class="tt">检测通过</span>';}?><br />template/ <?php if ($template){echo '<span class="t">'.$template.'</span>';}else{echo ' <span class="tt">检测通过</span>';}?><br />system/include.php <?php if ($inc){echo '<span class="t">'.$inc.'</span>';}else{echo ' <span class="tt">检测通过</span>';}?></p></div>
                </div>
				<div class="form-group" id="install_go">
					<input type="submit" id="submit" value="开始安装" class="templatemo-blue-button width-100" />
				</div>
	        </form>
	        <?php }
	if($go==2){
		$conn = mysqli_connect($_POST['host'], $_POST['username'], $_POST['password']);
		if(!$conn){
			echo '<script type="text/javascript">
					Dialog.alert("数据库连接失败，请检查数据库帐号输入是否正确：'.mysqli_connect_error().'",function(){window.location.href="javascript:history.go(-1)";});
					</script>';
			exit();
		}
		mysqli_query($conn, 'CREATE DATABASE IF NOT EXISTS '.$_POST['dbname'].' default charset utf8');
		$mysqlv=mysqli_get_server_info($conn);
		if(substr($mysqlv,0,1)<5){
            echo '<script type="text/javascript">
					Dialog.alert("您的数据库版本过低，Mysql版本要求大于等于5",function(){window.location.href="javascript:history.go(-1)";});
					</script>';
            exit();
		};
		mysqli_query($conn,'SET NAMES UTF8');
		mysqli_query($conn,'set sql_mode=""');
		mysqli_query($conn,'use '.$_POST['dbname']);

		$configfile='../config.php';
		$fp_tp=@fopen($configfile,"r");
		$fp_txt=@fread($fp_tp,filesize($configfile));
		@fclose($fp_tp);
		if((int)$_POST['go_backup']==1){
			$db=array('db','host','port','dbname','login','password');
		}else{
			$db=array('db','host','port','dbname','username','password','prefix','secret_key');
		}
		foreach($db as $v){
			if ($v=='port'){
				$fp_txt=preg_replace("/'port' => .*?,/","'port' => ".$_POST[$v].",",$fp_txt);
			}else if($v=='secret_key'){
				$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				$key = '';
				for($i=0;$i<12;$i++){
					$key .= $chars[ mt_rand(0, strlen($chars) - 1) ];
				}
				$secret_key=md5(md5(time()).md5($key));
				$fp_txt=preg_replace("/'secret_key' => .*?,/","'secret_key' => '".$secret_key."',",$fp_txt);
			}else{
				$fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".$_POST[$v]."',",$fp_txt);
			}
		}
		$fpt_tpl=@fopen($configfile,"w");
		@fwrite($fpt_tpl,$fp_txt);
		@fclose($fpt_tpl);
		if((int)$_POST['vercode']==0){
			$incfile='../system/include.php';
			$inc_tp=@fopen($incfile,"r");
			$inc_txt=@fread($inc_tp,filesize($incfile));
			@fclose($inc_tp);
			$inc_txt=preg_replace("/'vercode' => .*?,/","'vercode' => 0,",$inc_txt);
			$inct_tpl=@fopen($incfile,"w");
			@fwrite($inct_tpl,$inc_txt);
			@fclose($inct_tpl);
		}
		ob_implicit_flush(1);
		ob_end_flush();
?>
<?php
function dbbak_file($fname,$p,$rf){
	$filed=$fname.$p.'.php';
	if(file_exists($filed)){
		$rft=array_merge($rf,array($filed));
		$p=$p+1;dbbak_file($fname,$p,$rft);
	}else{
		$GLOBALS["rfiles"]=$rf;
	}
}
$i=0;$s=1;
echo '<div class="progress progress-striped active"><div style="width:0;" aria-valuemax="0" aria-valuemin="0" aria-valuenow="'.$s.'" role="progressbar" class="progress-bar progress-bar-danger" id="role"><span class="sr-only">安装全部完成!</span></div></div>';
if((int)$_POST['go_backup']==1){
    set_time_limit(99999999);
    $volnum=explode(".ph",$_POST['backup_db']);
    $backups=array('../system/backup/'.$_POST['backup_db']);
    dbbak_file('../system/backup/'.$volnum[0].'_v',2,$backups);
    foreach($GLOBALS["rfiles"] as $v){
        foreach(file($v) as $rsql){
            $sql=str_replace('<?php die();?>','',$rsql);
            $rgo=mysqli_query($conn,$sql);
            if(!$rgo){$i++;}
        }
    }
    set_time_limit(30);
}else{
    $db = file('smcms.sql');
    $total_sql=count($db);
    $db[1]=str_ireplace('|-auser-|',$_POST['auser'],$db[1]);
    $db[1]=str_ireplace('|-apass-|',md5(md5($_POST['apass']).$_POST['auser']),$db[1]);
    $db[1]=str_ireplace(1448001311,time(),$db[1]);
    foreach ($db as $num =>$v) {
        $v=trim($v);
        if((int)$_POST['go_backup']!=1){$v=str_ireplace('`wp_','`'.$_POST['prefix'],$v);}
        if (!mysqli_query($conn,$v)){
        	$i++;
        }else{
            $rule=($s/$total_sql)*100;
            $s++;
            echo '<script type="text/javascript">document.getElementById("role").style.width = "'.$rule.'%"; </script>';
            ob_flush();
            flush();
        }
    }
}

if($i>0){
    echo '<script type="text/javascript">
			Dialog.alert("数据库安装有'.$i.'条失败！检查数据库中是否存在同名表，或之前是否已安装过本系统，请删除表或更改表前缀重新执行安装",function(){window.location.href="javascript:history.go(-1)";});
		  </script>';
    exit();
}else{
	echo '<p style="line-height:35px;">数据库安装成功</p>';
}
?>
<?php
	$filename="install.txt";
	$fp=@fopen("$filename", "w");
	@fclose($fp);
	echo '<p style="line-height:35px;">安装全部完成！[<font color="#F00">请删除install文件夹</font>]</p><p style="line-height:35px;"><a class="btn btn-primary" style="margin-right:20px" href="../">浏览网站</a><a class="btn btn-info" href="../admin.php?action=login">进入后台</a></p>';
?>
</div>
<?php }?>
<div class="white-bg">
	<p align="center">Powered by <strong><a target="_blank" href="http://www.dmqmx.com" class="blue-text">Sagiri</a></strong> All Rights Reserved.</p>
</div>
</body>
</html>
