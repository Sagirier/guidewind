<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class district extends syController{
	public $pk = "id";
	public $table = "district";
	function __construct(){
		parent::__construct();
		$this->Class=syClass("c_district");
	}
	function get_city(){
		$city=$this->Class->findAll(array('upid'=>$this->syArgs('upid')));
		echo '<select name="member_residecity" class="ex-select" id="city">';
        foreach($city as $c){
        	echo '<option hassubinfo="true" value="'.$c['id'].'" onclick="changecity(this);">'.$c['name'].'</option>';
        }
        echo '</select>';
		exit();
	}
	function get_dist(){
		$dist=$this->Class->findAll(array('upid'=>$this->syArgs('upid')));
		echo '<select name="member_residedist" class="ex-select">';
		foreach($dist as $d){
			echo '<option hassubinfo="true" value="'.$d['id'].'">'.$d['name'].'</option>';
		}
		echo '</select>';
		exit();
	}
}	