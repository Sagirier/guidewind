<?php
define("APP_PATH",dirname(__FILE__));
define("WIND_PATH",APP_PATH."/system");
@date_default_timezone_set('PRC');
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
$wpConfig = array(

		'db' => array(
				'host' => '127.0.0.1',			//数据库地址
				'port' => 3306,					//数据库端口
				'dbname' => 'bdzn_db',		//数据库名
				'username' => 'root',				//数据库帐号
				'password' => 'Mifzl@mysql',			//数据库密码
				'prefix' => 'bd_',				//数据库表前缀
		),
		
		'ext' => array(
			'version' => '1.0',
			'update' => '20190418',
			'auto_update' => 1,
			'http_path' => 'http://www.bodenai.com',
			'site_title' => '博登智能',
			'site_keywords' => '博登智能',
			'site_description' => '博登智能',
			'secret_key' => 'ac5f4c1fcf8c0f3636cd51df634bed15',	//站内安全密钥，安装时会随机生成，一旦生成请勿修改，并请牢记，否则可能造成某些数据无法取回。
			'view_themes' => 'default',
			'site_statichtml' => 1,
			'site_statichtml_dir' => 'static',
			'site_statichtml_navrules' => '[dir]/[file].html',
			'site_statichtml_contentrules' => '[dir]/[channel]',
			'site_statichtml_rules' => '[file].html',
			'site_statichtml_suffix' => '.html',
			'site_statichtml_index' => 1,
			'enable_gzip' => 1,
			'enable_gzip_level' => 6,
			'cache_auto' => 1,
			'cache_time' => 0,
			'filetype' => 'jpg,gif,jpeg,bmp,png,swf,flv,wmv,wma,mp3,mp4,rar,zip,7z,txt,doc,docx,xls,xlsx',
			'filesize' => 10485760,  //允许10M上传
			'imgwater' => 0,
			'imgwater_type' => 4,
			'thumbnail' => 0,
			'img_w' => 800,
			'img_h' => 800,
			'comment_audit' => 0,
			'comment_user' => 0,
		),
        'member' => array(
            'default_portrait' => 'manage/admin/template/img/noportrait.png',   //会员默认头像路径
            'default_avator' => 'manage/admin/template/img/adminnopic.png',     //管理员默认头像路径 
            'male_icon' => 'manage/admin/template/img/male.png',                //男性图标路径
            'female_icon' => 'manage/admin/template/img/female.png',            //女性图标路径
        ),
        'sendemail' => array(
            'smtp_server' => 'smtp.ym.163.com', //SMTP服务器
            'smtp_serverport' => '25', //SMTP服务器端口
            'smtp_usermail' => 'sm@nwpit.com', //SMTP服务器的用户邮箱
            'smtp_user' => 'sm@nwpit.com', //SMTP服务器的用户帐号
            'smtp_pass' => 'ov29qBlnP3', //SMTP服务器的用户密码
        ),
);