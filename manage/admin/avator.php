<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}

class avator extends syController{
	function __construct(){
		parent::__construct();
		$this->filesdir=$this->syArgs('filesdir',1);
	}
	function index(){
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
	}
	function loadup(){
		$this->inputid=$this->syArgs('inputid',1);
		$this->imgid=$this->syArgs('imgid',1);
		$this->imgclip=$this->syArgs('imgclip',1);
		$this->multi=$this->syArgs('multi') ? 'true':'false';
		if($this->syArgs('nid')){
			$t=syDB('classtype')->find(array('nid'=>$this->syArgs('nid')),null.'imgw,imgh');
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
		$this->sizeLimit=$this->syArgs('sizeLimit') ? $this->syArgs('sizeLimit'):syExt('filesize');
		$this->fileover=$this->syArgs('fileover') ? $this->syArgs('fileover'):1;
		$this->display("avator.html");
	}
	function filemanager(){
		if (!empty($_FILES)) {
    	    $uid = intval( $_REQUEST['uid'] );
    	    $ext = pathinfo($_FILES['Filedata']['name']);
    	    $ext = strtolower($ext['extension']);
    	    $tempFile = $_FILES['Filedata']['tmp_name'];
    	    $targetPath   = APP_PATH . '/uploads/' . $this->filesdir . '/';
    	    if(!is_dir($targetPath)){mkdir($targetPath,0777,true);}else {chmod($targetPath,0777);}
    	    $new_file_name = 'avator_ori_'.time().'_'.rand(1000, 9999).'.'.$ext;
    	    $targetFile = $targetPath . $new_file_name;
    	    move_uploaded_file($tempFile,$targetFile);
    	    if( !file_exists( $targetFile ) ){
    	        $ret['result_code'] = 0;
    	        $ret['result_des'] = 'upload failure';
    	    } elseif( !$imginfo=getImageInfo($targetFile) ) {
    	        $ret['result_code'] = 101;
    	        $ret['result_des'] = 'File is not exist';
    	    } else {
    	        $img ='uploads/'. $this->filesdir . '/'.$new_file_name;
    	        resize($img);
    	        $ret['result_code'] = 1;
    	        $ret['result_des'] = $img;
    	    }
    	} else {
    	    $ret['result_code'] = 100;
    	    $ret['result_des'] = 'No File Given';
    	}
    	echo syClass('syjson')->encode($ret);
	    exit();
	}
    function resizeimg(){  //裁剪图片
	    $image = $this->syArgs('img',1);
		if( !$image){
		    $ret['result_code'] = 101;
		    $ret['result_des'] = "图片不存在";
		} else {
			$image_new = APP_PATH."/".$image;
		    $info = getImageInfo( $image_new);
		    if( !$info ){
		        $ret['result_code'] = $image_new;
		        $ret['result_des'] = "图片不存在";
		    } else {
		        $x = $_POST["x"];
		        $y = $_POST["y"];
		        $w = $_POST["w"];
		        $h = $_POST["h"];
		        $width = $srcWidth = $info['width'];
		        $height = $srcHeight = $info['height'];
		        $type = empty($type)?$info['type']:$type;
		        $type = strtolower($type);
		        unset($info);
		        // 载入原图
		        $createFun = 'imagecreatefrom'.($type=='jpg'?'jpeg':$type);
		        $srcImg     = $createFun($image);
		        //创建缩略图
		        if($type!='gif' && function_exists('imagecreatetruecolor')){
		        	imagesavealpha($srcImg,true);
		            $thumbImg = imagecreatetruecolor($width, $height);
		            imagealphablending($thumbImg,false);
		            imagesavealpha($thumbImg,true);
		        }
		        else{
		            $thumbImg = imagecreate($width, $height);
		        }
		        // 复制图片
		        if(function_exists("imagecopyresampled")){
		            imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth,$srcHeight);
		        }else{
		            imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height,  $srcWidth,$srcHeight);
		        }
		        if('gif'==$type || 'png'==$type) {
		            $background_color  =  imagecolorallocate($thumbImg,0,0,0,127);
		            imagecolortransparent($thumbImg,$background_color);
		        }
		        // 对jpeg图形设置隔行扫描
		        if('jpg'==$type || 'jpeg'==$type)imageinterlace($thumbImg,1);
		        // 生成图片
		        $imageFun = 'image'.($type=='jpg'?'jpeg':$type);
		        $thumbname01 = str_replace("ori", "244", $image);
		        $thumbname02 = str_replace("ori", "140", $image);
		        $thumbname03 = str_replace("ori", "70", $image);
		        $imageFun($thumbImg,$thumbname01);
		        $imageFun($thumbImg,$thumbname02);
		        $imageFun($thumbImg,$thumbname03);
		        
		        imagesavealpha($thumbname01,true);
		        $thumbImg01 = imagecreatetruecolor(244,244);
		        imagealphablending($thumbImg01,false);
		        imagesavealpha($thumbImg01,true);
		        imagecopyresampled($thumbImg01,$thumbImg,0,0,$x,$y,244,244,$w,$h);
		        
		        imagesavealpha($thumbname02,true);
		        $thumbImg02 = imagecreatetruecolor(140,140);
		        imagealphablending($thumbImg02,false);
		        imagesavealpha($thumbImg02,true);
		        imagecopyresampled($thumbImg02,$thumbImg,0,0,$x,$y,140,140,$w,$h);
		        
		        imagesavealpha($thumbname03,true);
		        $thumbImg03 = imagecreatetruecolor(70,70);
		        imagealphablending($thumbImg03,false);
		        imagesavealpha($thumbImg03,true);
		        imagecopyresampled($thumbImg03,$thumbImg,0,0,$x,$y,70,70,$w,$h);
		        
		        $imageFun($thumbImg01,$thumbname01);
		        $imageFun($thumbImg02,$thumbname02);
		        $imageFun($thumbImg03,$thumbname03);
		        imagedestroy($thumbImg01);
		        imagedestroy($thumbImg02);
		        imagedestroy($thumbImg03);
		        imagedestroy($thumbImg);
		        imagedestroy($srcImg);
		        $ret['result_code'] = 1;
		        $ret['result_des'] = array(
		            "big"   => $thumbname01,
		            "middle"=> $thumbname02,
		            "small" => $thumbname03
		        );
		    }
		}
		echo syClass('syjson')->encode($ret);
		exit();
	}
}