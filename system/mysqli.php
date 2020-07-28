<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class db_mysqli {
	public $conn;
	public $arrSql;
	public function __construct($wpConfig){
		$this->conn= mysqli_connect($wpConfig['host'], $wpConfig['username'], $wpConfig['password'], $wpConfig['dbname'], $wpConfig['port']);
		if(!$this->conn){
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />数据库无法链接，如果您是第一次使用，请先执行<a href="install">安装程序</a><br />';exit;
		}
		$this->execute("SET NAMES UTF8");
		if($this->version() > '5.0.1') {
			$this->execute("set sql_mode=''");
		}
	}
	public function getArray($sql)
	{
		if( ! $result = $this->execute($sql) )return array();
		if( ! mysqli_num_rows($result) )return array();
		$rows = array();
		while($rows[] = mysqli_fetch_array($result,MYSQL_ASSOC)){}
		mysqli_free_result($result);
		array_pop($rows);
		return $rows;
	}

	public function newinsertid()
	{
		return mysqli_insert_id($this->conn);
	}
	
	public function showtables($tables)
	{
		return mysqli_num_rows(mysqli_query($this->conn,"show tables like '%".$tables."%'",$this->conn));
	}

	public function setlimit($sql, $limit)
	{
		return $sql. " LIMIT {$limit}";
	}

	public function execute($sql){
		$this->arrSql[] = $sql;
		if( $res = mysqli_query($this->conn,$sql) ){
			return $res;
		}else{
			if(mysqli_error()!=''){
				syError("{$sql}<br />执行错误: " . mysqli_error());
			}else{
				return TRUE;
			}
		}
	}

	public function affected_rows()
	{
		return mysqli_affected_rows($this->conn);
	}

	public function getTable($tbl_name)
	{
		return $this->getArray("DESCRIBE {$tbl_name}");
	}
	public function version() {
		return mysqli_get_server_info($this->conn);
	}
	
	public function __val_escape($value) {
		return '\''.$value.'\'';
	}

	public function __destruct(){
		@mysqli_close($this->conn);
	}
}

