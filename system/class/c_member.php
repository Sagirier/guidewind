<?php
class c_member extends syModel
{
	var $pk = "uid";
	var $table = "member";
	var $verifier = array(
	    "rules" => array(
	        'username' => array(
	            'notnull' => TRUE,
	            'isabcnocn' => TRUE,
	        ),
	        'password' => array(
	            'notnull' => TRUE,
	        ),
	    ),
	    "messages" => array(
	        'username' => array(
	            'notnull' => '用户名不能为空',
	            'isabcnocn' => '用户名只能包含数字,英文,中文,下划线',
	        ),
	        'password' => array(
	            'notnull' => '请填写登录密码',
	        ),
	    )
	);
}