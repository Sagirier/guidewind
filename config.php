<?php
define("APP_PATH",dirname(__FILE__));
define("WIND_PATH",APP_PATH."/system");
@date_default_timezone_set('PRC');
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
$wpConfig = array(

    'db' => array(
        'host' => '127.0.0.1',			//数据库地址
        'port' => 3306,					//数据库端口
        'dbname' => '',		//数据库名
        'username' => '',				//数据库帐号
        'password' => '',			//数据库密码
        'prefix' => '',				//数据库表前缀
    ),

    'ext' => array(
        'version' => '1.0',
        'update' => '20190418',
        'auto_update' => 1,
        'http_path' => 'http://smcms.dmqmx.com',
        'site_title' => '纱梦cms',
        'site_keywords' => '纱梦cms,cms,建站系统,纱梦',
        'site_description' => '纱梦cms-通用建站系统',
        'secret_key' => '',	//站内安全密钥，安装时会随机生成，一旦生成请勿修改，并请牢记，否则可能造成某些数据无法取回。
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
        'smtp_server' => '', //SMTP服务器
        'smtp_serverport' => '', //SMTP服务器端口
        'smtp_usermail' => '', //SMTP服务器的用户邮箱
        'smtp_user' => '', //SMTP服务器的用户帐号
        'smtp_pass' => '', //SMTP服务器的用户密码
    ),
);
