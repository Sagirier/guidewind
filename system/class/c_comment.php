<?php
class c_comment extends syModel
{
	var $pk = "id";
	var $table = "comment";
	var $verifier = array(
		"rules" => array(
			'aid' => array(
				'notnull' => TRUE,
			),
			'cmark' => array(
				'notnull' => TRUE,
			),
			'detail' => array(
				'maxlength' => 500,
			),
		),
		"messages" => array(
			'aid' => array(
				'notnull' => '所属内容不能为空',
			),
			'cmark' => array(
				'notnull' => '所属模块不能为空',
			),
			'detail' => array(
				'maxlength' => '评论内容不能超过500字',
			),
		)
	);
	
}