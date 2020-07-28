<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class district extends syController{
	public $pk = "id";
	public $table = "district";
	function __construct(){
		parent::__construct();
		$this->Class=syClass("c_district");
	}
	function get_city(){
		$city=$this->Class->findAll(array('upid'=>$this->syArgs('upid')));
		$dist=$this->Class->findAll(array('upid'=>$city[0]['id']));
		$ret['city']="<option value=''>-- 城市/城区 --</option>";
        foreach($city as $c){
        	$ret['city'].='<option value="'.$c['id'].'">'.$c['name'].'</option>';
        }
        $ret['dist']="<option value=''>-- 区/县 --</option>";
        foreach($dist as $d){
            $ret['dist'].='<option value="'.$d['id'].'">'.$d['name'].'</option>';
        }
        echo syClass('syjson')->encode($ret);
		exit();
	}
	function get_dist(){
		$dist=$this->Class->findAll(array('upid'=>$this->syArgs('upid')));
		$ret="<option value=''>-- 区/县 --</option>";
		foreach($dist as $d){
			$ret.= '<option value="'.$d['id'].'">'.$d['name'].'</option>';
		}
		echo $ret;
		exit();
	}
}	