<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}

class uploads extends syController{
	function __construct(){
		parent::__construct();
		$this->filesdir=$this->syArgs('filesdir',1);
	}
	function index(){
// 		$allow = $this->syArgs('allow',1)!='' ? $this->syArgs('allow',1) : syExt('filetype');
// 		$size = $this->syArgs('filesize',1)!='' ? $this->syArgs('filesize',1) : syExt('filesize');
// 		echo $size;
// 		$water = $this->syArgs('water',1)!='' ? $this->syArgs('water',1) : syExt('imgwater');
// 		$caling = $this->syArgs('caling',1)!='' ? $this->syArgs('caling',1) : syExt('imgcaling');
// 		$w = $this->syArgs('w',1)!='' ? $this->syArgs('w',1) : syExt('img_w');
// 		$h = $this->syArgs('h',1)!='' ? $this->syArgs('h',1) : syExt('img_h');
// 		$fileClass=syClass('syupload',array($allow,$size,$water,$caling,$w,$h));
// 		if (!empty($_FILES)){
// 			if($this->syArgs('isfiles',1)=='editor_KindEditor'){
// 				header('Content-type: text/html; charset=UTF-8');
// 				$fileinfos = $fileClass->upload_file($_FILES[$this->syArgs('isfiles',1)]);
// 				if (is_array($fileinfos)){
// 					echo '{"error" : 0,"url" : "'.$fileinfos['fn'].'"}';
// 				}else{
// 					echo '{"error" : 1,"message" : "'.$fileClass->errmsg.'"}';
// 				}
// 			}else{
// 				$fileinfos = $fileClass->upload_file($_FILES[$this->syArgs('isfiles',1)]);
// 				if (is_array($fileinfos)){
// 					echo '0';
// 						$f=explode('.',$fileinfos['fn']);
// 						echo ','.$fileinfos['fn'];
// 						echo ','.preg_replace('/.*\/.*\//si','',$f[0]);
// 						if(stripos($fileinfos['fn'],'jpg') || stripos($fileinfos['fn'],'gif') || stripos($fileinfos['fn'],'png') || stripos($fileinfos['fn'],'jpeg')){
// 							echo ',1';
// 						}else{
// 							echo ','.$f[1];
// 						}
// 				}else{
// 					echo $fileClass->errmsg;
// 				}
// 			}
// 		}else{echo '未上传任何文件';}
	}
	function loadup(){
		$this->inputid=$this->syArgs('inputid',1);
		$this->imgid=$this->syArgs('imgid',1);
		$this->imgclip=$this->syArgs('imgclip',1);
		$this->multi=$this->syArgs('multi') ? 'true':'false';
		if($this->syArgs('nid')){
			$t=syDB('navigators')->find(array('nid'=>$this->syArgs('nid')),null.'imgw,imgh');
			if($t['imgw']&&$t['imgh']){
				$this->w = $t['imgw'];
				$this->h = $t['imgh'];
			}
		}
		if($this->syArgs('imgw')&&$this->syArgs('imgh')){
			$this->w = $this->syArgs('imgw');
			$this->h = $this->syArgs('imgh');
		}
		if($this->syArgs('fileExt',1)){$this->fileExt=$this->syArgs('fileExt',1);}else{
			foreach(explode(',',syExt('filetype')) as $v){
				$fileExt.=';*.'.$v;
			}$this->fileExt=substr($fileExt,1);
		}
		if($this->syArgs('filesType',1)){
			$this->filesType=$this->syArgs('filesType',1);
		}else {
			$this->filesType="All Files";
		}
		$this->sizeLimit=$this->syArgs('filesize') ? $this->syArgs('filesize'):syExt('filesize');
		$this->fileover=$this->syArgs('fileover') ? $this->syArgs('fileover'):1;
		$this->display("uploads.php");
	}
	function editormultiUpload(){
	    $this->inputid=$this->syArgs('inputid',1);
	    $this->imgid=$this->syArgs('imgid',1);
	    $this->imgclip=$this->syArgs('imgclip',1);
	    $this->multi=$this->syArgs('multi') ? 'true':'false';
	    if($this->syArgs('nid')){
	        $t=syDB('navigators')->find(array('nid'=>$this->syArgs('nid')),null.'imgw,imgh');
	        if($t['imgw']&&$t['imgh']){
	            $this->w = $t['imgw'];
	            $this->h = $t['imgh'];
	        }
	    }
	    if($this->syArgs('imgw')&&$this->syArgs('imgh')){
	        $this->w = $this->syArgs('imgw');
	        $this->h = $this->syArgs('imgh');
	    }
	    if($this->syArgs('fileExt',1)){$this->fileExt=$this->syArgs('fileExt',1);}else{
	        foreach(explode(',',syExt('filetype')) as $v){
	            $fileExt.=';*.'.$v;
	        }$this->fileExt=substr($fileExt,1);
	    }
	    if($this->syArgs('filesType',1)){
	        $this->filesType=$this->syArgs('filesType',1);
	    }else {
	        $this->filesType="All Files";
	    }
	    $this->sizeLimit=$this->syArgs('filesize') ? $this->syArgs('filesize'):syExt('filesize');
	    $this->fileover=$this->syArgs('fileover') ? $this->syArgs('fileover'):1;
	    $this->display("multiuploads.php");
	}
	function filemanager(){
		$POST_MAX_SIZE = ini_get('post_max_size');
		$unit = strtoupper(substr($POST_MAX_SIZE, -1));
		$multiplier = ($unit == 'M' ? 1025022100 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
		
		if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "POST exceeded maximum allowed size.";
			exit(0);
		}
		
		// Settings
		$save_path = APP_PATH . '/uploads/' . $this->filesdir . '/';
		if(!is_dir($save_path)){mkdir($save_path,0777,true);}else {chmod($save_path,0777);}
		$upload_name = "Filedata";
		$max_file_size_in_bytes = 2147483647;				// 2GB in bytes
		$extension_whitelist = explode(",", $GLOBALS['WP']['ext']['filetype']);	// Allowed file extensions
		$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';				// Characters allowed in the file name (in a Regular Expression format)
		
		// Other variables
		$MAX_FILENAME_LENGTH = 260;
		$file_name = "";
		$file_extension = "";
		$uploadErrors = array(
				0=>"文件上传成功",
				1=>"上传的文件超过了 php.ini 文件中的 upload_max_filesize directive 里的设置",
				2=>"上传的文件超过了 HTML form 文件中的 MAX_FILE_SIZE directive 里的设置",
				3=>"上传的文件仅为部分文件",
				4=>"没有文件上传",
				6=>"缺少临时文件夹"
		);
		
		
		// Validate the upload
		if (!isset($_FILES[$upload_name])) {
			$this->HandleError("No upload found in \$_FILES for " . $upload_name);
			exit(0);
		} else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
			$this->HandleError($uploadErrors[$_FILES[$upload_name]["error"]]);
			exit(0);
		} else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
			$this->HandleError("Upload failed is_uploaded_file test.");
			exit(0);
		} else if (!isset($_FILES[$upload_name]['name'])) {
			$this->HandleError("File has no name.");
			exit(0);
		}
		
		// Validate the file size (Warning: the largest files supported by this code is 2GB)
		$file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
		if (!$file_size || $file_size > $max_file_size_in_bytes) {
			$this->HandleError("File exceeds the maximum allowed size");
			exit(0);
		}
		
		if ($file_size <= 0) {
			$this->HandleError("File size outside allowed lower bound");
			exit(0);
		}
		
		
		// Validate file name (for our purposes we'll just remove invalid characters)
		$file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
		$file_name = str_replace($file_name, time()."_".rand(1000, 9999), $file_name);
		if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
			$this->HandleError("Invalid file name");
			exit(0);
		}
		
		
		// Validate that we won't over-write an existing file
		if (file_exists($save_path . $file_name)) {
			$this->HandleError("File with this name already exists");
			exit(0);
		}
		
		// Validate file extension
		$path_info = pathinfo($_FILES[$upload_name]['name']);
		$file_extension = $path_info["extension"];
		$is_valid_extension = false;
		foreach ($extension_whitelist as $extension) {
			if (strcasecmp($file_extension, $extension) == 0) {
				$is_valid_extension = true;
				break;
			}
		}
		if (!$is_valid_extension) {
			$this->HandleError("未知文件");
			exit(0);
		}
		if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path.$this->filesdir. "_" . $file_name . "." . $path_info["extension"])) {
			$this->HandleError("文件无法保存.");
			exit(0);
		}
		
		// Return output to the browser (only supported by SWFUpload for Flash Player 9)
		$file_path = $GLOBALS['WWW'] . 'uploads/' . $this->filesdir . '/' . $this->filesdir. "_" . $file_name . "." . $path_info["extension"];
		echo $file_path;
		exit(0);
	}
	function editorfilemanager(){
	    $POST_MAX_SIZE = ini_get('post_max_size');
	    $unit = strtoupper(substr($POST_MAX_SIZE, -1));
	    $multiplier = ($unit == 'M' ? 1025022100 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
	
	    if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
	        header("HTTP/1.1 500 Internal Server Error");
	        echo "POST exceeded maximum allowed size.";
	        exit(0);
	    }
	
	    // Settings
	    $save_path = APP_PATH . '/uploads/' . $this->filesdir . '/';
	    if(!is_dir($save_path)){mkdir($save_path,0777,true);}else {chmod($save_path,0777);}
	    $upload_name = "files";
	    $max_file_size_in_bytes = 2147483647;				// 2GB in bytes
	    $extension_whitelist = explode(",", $GLOBALS['WP']['ext']['filetype']);	// Allowed file extensions
	    $valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';				// Characters allowed in the file name (in a Regular Expression format)
	
	    // Other variables
	    $MAX_FILENAME_LENGTH = 260;
	    $file_name = "";
	    $file_extension = "";
	    $uploadErrors = array(
	        0=>"文件上传成功",
	        1=>"上传的文件超过了 php.ini 文件中的 upload_max_filesize directive 里的设置",
	        2=>"上传的文件超过了 HTML form 文件中的 MAX_FILE_SIZE directive 里的设置",
	        3=>"上传的文件仅为部分文件",
	        4=>"没有文件上传",
	        6=>"缺少临时文件夹"
	    );
	    $files=$_FILES[$upload_name];
	    for($i=0;$i<=count($files);$i++){
    	    // Validate the upload
    	    if (isset($files["error"][$i]) && $files["error"][$i] != 0) {
    	        $this->HandleError($uploadErrors[$files["error"][$i]]);
    	        exit(0);
    	    } else if (!isset($files["tmp_name"][$i]) || !@is_uploaded_file($files["tmp_name"][$i])) {
    	        $this->HandleError("Upload failed is_uploaded_file test.");
    	        exit(0);
    	    } else if (!isset($files['name'][$i])) {
    	        $this->HandleError("File has no name.");
    	        exit(0);
    	    }
    	
    	    // Validate the file size (Warning: the largest files supported by this code is 2GB)
    	    $file_size = @filesize($files["tmp_name"][$i]);
    	    if (!$file_size || $file_size > $max_file_size_in_bytes) {
    	        $this->HandleError("File exceeds the maximum allowed size");
    	        exit(0);
    	    }
    	
    	    if ($file_size <= 0) {
    	        $this->HandleError("File size outside allowed lower bound");
    	        exit(0);
    	    }
    	
    	
    	    // Validate file name (for our purposes we'll just remove invalid characters)
    	    $file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($files['name'][$i]));
    	    $file_name = str_replace($file_name, time()."_".rand(1000, 9999), $file_name);
    	    if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
    	        $this->HandleError("Invalid file name");
    	        exit(0);
    	    }
    	
    	
    	    // Validate that we won't over-write an existing file
    	    if (file_exists($save_path . $file_name)) {
    	        $this->HandleError("File with this name already exists");
    	        exit(0);
    	    }
    	
    	    // Validate file extension
    	    $path_info = pathinfo($files['name'][$i]);
    	    $file_extension = $path_info["extension"];
    	    $is_valid_extension = false;
    	    foreach ($extension_whitelist as $extension) {
    	        if (strcasecmp($file_extension, $extension) == 0) {
    	            $is_valid_extension = true;
    	            break;
    	        }
    	    }
    	    if (!$is_valid_extension) {
    	        $this->HandleError("未知文件");
    	        exit(0);
    	    }
    	    if (!@move_uploaded_file($files["tmp_name"][$i], $save_path.$this->filesdir. "_" . $file_name . "." . $path_info["extension"])) {
    	        $this->HandleError("文件无法保存.");
    	        exit(0);
    	    }
    	
    	    // Return output to the browser (only supported by SWFUpload for Flash Player 9)
    	    $file_path = $GLOBALS['WWW'] . 'uploads/' . $this->filesdir . '/' . $this->filesdir. "_" . $file_name . "." . $path_info["extension"];
    	    echo $file_path;
    	    exit(0);
	    }
	}
	function HandleError($message) {
		header("HTTP/1.1 500 Internal Server Error");
		echo $message;
	}
	function editorupload() {
		$allow = $this->syArgs('allow',1)!='' ? $this->syArgs('allow',1) : syExt('filetype');
		$size = $this->syArgs('size',1)!='' ? $this->syArgs('size',1) : syExt('filesize');
		$water = $this->syArgs('water',1)!='' ? $this->syArgs('water',1) : syExt('imgwater');
		$caling = $this->syArgs('caling',1)!='' ? $this->syArgs('caling',1) : syExt('imgcaling');
		$w = $this->syArgs('w',1)!='' ? $this->syArgs('w',1) : syExt('img_w');
		$h = $this->syArgs('h',1)!='' ? $this->syArgs('h',1) : syExt('img_h');
		$fileClass=syClass('syupload',array($allow,$size,$water,$caling,$w,$h));
		if (!empty($_FILES)){
			if($this->syArgs('isfiles',1)=='editor_KindEditor'){
				header('Content-type: text/html; charset=UTF-8');
				$fileinfos = $fileClass->upload_file($_FILES[$this->syArgs('isfiles',1)]);
				if (is_array($fileinfos)){
					echo '{"error" : 0,"url" : "'.$fileinfos['fn'].'"}';
				}else{
					echo '{"error" : 1,"message" : "'.$fileClass->errmsg.'"}';
				}
			}else{
				$fileinfos = $fileClass->upload_file($_FILES[$this->syArgs('isfiles',1)]);
				if (is_array($fileinfos)){
					echo '0';
						$f=explode('.',$fileinfos['fn']);
						echo ','.$fileinfos['fn'];
						echo ','.preg_replace('/.*\/.*\//si','',$f[0]);
						if(stripos($fileinfos['fn'],'jpg') || stripos($fileinfos['fn'],'gif') || stripos($fileinfos['fn'],'png') || stripos($fileinfos['fn'],'jpeg')){
							echo ',1';
						}else{
							echo ','.$f[1];
						}
				}else{
					echo $fileClass->errmsg;
				}
			}
		}else{echo '未上传任何文件';}
//		if ($_FILES['file']['name']) {
//			if (!$_FILES['file']['error']) {
//				$ext = "";
//				$pathinfo=pathinfo($_FILES['file']['name']);
//				$ext= $pathinfo["extension"];
//	    		$name = time().'_'.rand(1000, 9999).'.'.$ext;
//	    		$savepath = APP_PATH . '/uploads/' . $this->filesdir . '/';
//	    		if(!is_dir($savepath)){mkdir($savepath,0777,true);}else {chmod($savepath,0777);}
//				$destination =$savepath .$this->filesdir."_" . $name; //change this directory
//				$location = $_FILES["file"]["tmp_name"];
//				move_uploaded_file($location, $destination);
//				$img ='uploads/'. $this->filesdir . '/' .$this->filesdir."_".$name;
//		        $ret['result'] = $img;
//			}
//			else
//			{
//				echo  $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
//			}
//			exit( json_encode( $ret ) );
//		}
	}
	function filespace() {
	    $root_path = APP_PATH . '/uploads/';
	    $root_url = $GLOBALS["WWW"] . 'uploads/';
	    $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'swf', 'flv', 'wmv', 'mp3', 'mp4', '3gp', 'wma', 'mpeg', 'rm', 'avi');
	    $dir_name = '';
	    $g_path=$this->syArgs('path',1);
	    if ($dir_name !== '') {
	        $root_path .= $dir_name . "/";
	        $root_url .= $dir_name . "/";
	    }
	    if (empty($g_path)) {
	        $current_path = realpath($root_path) . '/';
	        $current_url = $root_url;
	        $current_dir_path = '';
	        $moveup_dir_path = '';
	    } else {
	        $current_path = realpath($root_path) . '/' . $g_path;
	        $current_url = $root_url . $g_path;
	        $current_dir_path = $g_path;
	        $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
	    }
	    echo realpath($root_path);
	    if (preg_match('/\.\./', $current_path)) {
	        echo 'Access is not allowed.';
	        exit;
	    }
	    if (!preg_match('/\/$/', $current_path)) {
	        echo 'Parameter is not valid.';
	        exit;
	    }
	    if (!file_exists($current_path) || !is_dir($current_path)) {
	        echo 'Directory does not exist.';
	        exit;
	    }
	    $file_list = array();
	    if ($handle = opendir($current_path)) {
	        $i = 0;
	        while (false !== ($filename = readdir($handle))) {
	            if ($filename{0} == '.') continue;
	            $file = $current_path . $filename;
	            if (is_dir($file)) {
	                $file_list[$i]['is_dir'] = true;
	                $file_list[$i]['has_file'] = (count(scandir($file)) > 2);
	                $file_list[$i]['filesize'] = 0;
	                $file_list[$i]['is_photo'] = false;
	                $file_list[$i]['filetype'] = '';
	            } else {
	                $file_list[$i]['is_dir'] = false;
	                $file_list[$i]['has_file'] = false;
	                $file_list[$i]['filesize'] = filesize($file);
	                $file_list[$i]['dir_path'] = '';
	                $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	                $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
	                $file_list[$i]['filetype'] = $file_ext;
	            }
	            $file_list[$i]['filename'] = $filename;
	            $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file));
	            $i++;
	        }
	        closedir($handle);
	    }
	    usort($file_list, 'filemanager_list');
	     
	    $result = array();
	    $result['moveup_dir_path'] = $moveup_dir_path;
	    $result['current_dir_path'] = $current_dir_path;
	    $result['current_url'] = $current_url;
	    $result['total_count'] = count($file_list);
	    $result['file_list'] = $file_list;
	    header('Content-type: application/json; charset=UTF-8');
	    echo syClass('syjson')->encode($result);
	}
}