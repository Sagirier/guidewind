<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class goods extends syController{
	function __construct(){
		parent::__construct();
		$this->types=syClass('synavigators');
		$this->typesdb=$this->types->type_txt();
		$this->a='goods';
		$this->db=$GLOBALS['WP']['db']['prefix']."attribute_type";
		$this->attributes=syDB('attribute_type')->findAll(null,' `order` desc,tid desc ');
	}
	function index(){
		
	}
	function attribute(){
		//$this->lists = syDB('attribute_type')->findAll();
		$this->display("attribute.html");
	}
	function addattribute(){

	    if($this->syArgs('go')==1){
	        $navs=$this->syArgs('navigators',2);
	        foreach ($navs as $n){
	            if(total_count($this->db." where `statu`=1 and `navigators` like '%|".$n."|%' ")>=3){
	                message('栏目【'.navinfo($n,'nname').'】已使用了三种规格，无法再设置更多，请重新勾选栏目');
	            }
	        }
	        $attribute=array(
	            'name'=>$this->syArgs('attribute_name',1),
	            'order'=>$this->syArgs('attribute_order'),
	            'statu'=>$this->syArgs('attribute_statu'),
	            'navigators'=>"|".join("|",$this->syArgs('navigators',2))."|"
	        );
	        if(syDB('attribute_type')->create($attribute)){
	            deleteDir($GLOBALS['WP']['sp_cache']);
	            message_c('规格添加成功','?action=goods&o=attribute','?action=goods&o=addattribute');
	        }
	    }
	    $this->display("attribute_edit.html");
	}
	function editattribute(){
	    $tid=$this->syArgs('tid');
	    $this->attribute=syDB('attribute_type')->find(array('tid'=>$tid));
	    if($this->syArgs('go')==1){
	        $navs=$this->syArgs('navigators',2);
	        foreach ($navs as $n){
	            if(strpos($this->attribute['navigators'],"|".$n."|") === false){
	                if(total_count($this->db." where `statu`=1 and `navigators` like '%|".$n."|%' ")>=3){
	                    message('栏目【'.navinfo($n,'nname').'】已使用了三种规格，无法再设置更多，请重新勾选栏目');
	                }
	            }
	        }
	        $attribute=array(
	            'name'=>$this->syArgs('attribute_name',1),
	            'order'=>$this->syArgs('attribute_order'),
	            'statu'=>$this->syArgs('attribute_statu'),
	            'navigators'=>"|".join("|",$this->syArgs('navigators',2))."|"
	        );
	        if(syDB('attribute_type')->update(array('tid'=>$tid),$attribute)){
	            deleteDir($GLOBALS['WP']['sp_cache']);
	            message('规格修改成功','?action=goods&o=attribute');
	        }
	    }
	    $this->display("attribute_edit.html");
	}
	function deleteattribute() {
	    $tid=$this->syArgs('tid');
	    if(!syDB('attribute_type')->find(array('tid'=>$tid))){
	        echo "NOT EXIT!";
	        exit();
	    }
	    syDB('product_attribute')->delete(array('tid'=>$tid));
	    syDB('attribute')->delete(array('tid'=>$tid));
	    if(syDB('attribute_type')->delete(array('tid'=>$tid))){
	        deleteDir($GLOBALS['WP']['sp_cache']);
	        echo 'ok';
	    }else{
	        echo 'fail';
	    }
	    exit();
	}
	function options(){
	    $tid=$this->syArgs('tid');
	    $this->lists = syDB('attribute')->findAll(array('tid'=>$tid),' `order` desc,sid desc ');
	    $this->display("attribute_options.html");
	}
	function editoption() {
	    $sid=$this->syArgs('sid');
	    $this->option=syDB('attribute')->find(array('sid'=>$sid));
	    if($this->syArgs('go')==1){
	        $sida=$this->syArgs('sid');
	        $option=array(
	            'tid'=>$this->syArgs('tid'),
	            'name'=>$this->syArgs('option_name',1),
	            'order'=>$this->syArgs('option_order'),
	            'statu'=>$this->syArgs('option_statu')
	        );
	        if(syDB('attribute')->update(array('sid'=>$sida),$option)){
	            deleteDir($GLOBALS['WP']['sp_cache']);
	            echo 'ok';
	        }else{
	            echo 'fail';
	        }
	        exit();
	    }
	    $this->display("option_edit.html");
	}
	function addoption(){
	    $this->tid=$this->syArgs('tid');
	    if($this->syArgs('go')==1){
	        $option=array(
	            'tid'=>$this->syArgs('tid'),
	            'name'=>$this->syArgs('option_name',1),
	            'order'=>$this->syArgs('option_order'),
	            'statu'=>$this->syArgs('option_statu')
	        );
	        if(syDB('attribute')->create($option)){
	            deleteDir($GLOBALS['WP']['sp_cache']);
	            echo 'ok';
	        }else{
	            echo 'fail';
	        }
	        exit();
	    }
	    $this->display("option_edit.html");
	}
	function deleteoption(){
	    if(syDB('attribute')->delete(array('sid'=>$this->syArgs('sid')))){
	        syDB('product_attribute')->delete(array('sid'=>$this->syArgs('sid')));
	        deleteDir($GLOBALS['WP']['sp_cache']);
	        echo 'ok';
	    }else{
	        echo 'fail';
	    }
	    exit();
	}
    function getattributes(){
        //获取Datatables发送的参数 必要
        $draw = $this->syArgs('draw');//这个值会直接返回给前台
        //排序
        $order=$this->syArgs('order',2);
        $order_column = $order['0']['column'];//那一列排序，从0开始
        $order_dir = $order['0']['dir'];//ase desc 升序或者降序
 
        //拼接排序sql
        $orderSql = "";
        if(isset($order_column)){
            $k = intval($order_column);
            switch($k){
                case 0;$orderSql = " order by order ".$order_dir;break;
                case 1;$orderSql = " order by tid ".$order_dir;break;
                case 2;$orderSql = " order by name ".$order_dir;break;
                case 3;$orderSql = " order by statu ".$order_dir;break;
                case 4;$orderSql = " order by navigators ".$order_dir;break;
                default;$orderSql ="";
            }
        }
        //搜索
        $searchArr=$this->syArgs('search',2);
        $search = $searchArr['value'];//获取前台传过来的过滤条件
 
        //分页
        $start = $this->syArgs('start');//从多少开始
        $length = $this->syArgs('length');//数据长度
        $limitSql = '';
        $limitFlag = isset($start) && $length != -1 ;
        if ($limitFlag ) {
            $limitSql = " LIMIT ".intval($start).", ".intval($length);
        }
 
        $recordsFiltered = 0;
        //表的总记录数 必要
        $recordsTotal = 0;
        $recordsTotal =  total_count($this->db);
        //定义过滤条件查询过滤后的记录数sql
        $sumSqlWhere="";
        if(strlen($search)>0){
            $sumSqlWhere.="and tid||name||statu||order||navigators LIKE '%".$search."%'";
        }
        if($sumSqlWhere!=''){$sumSqlWhere=' where '.substr($sumSqlWhere,3);}
        $recordsFiltered =  total_count($this->db.$sumSqlWhere);
        //query data
        $totalResultSql = "SELECT * FROM ".$this->db.$sumSqlWhere;
        $infos = array();
        //直接查询所有记录
        $dataResult = syClass('syModel')->findSql($totalResultSql.$orderSql.$limitSql);
        $i=0;
        while ($i<count($dataResult)) {
            $obj = $this->getattributeinfo($dataResult[$i]);
            array_push($infos,$obj);
            $i++;
        }
 
        /*
         * Output 包含的是必要的
         */
        echo syClass('syjson')->encode(array(
            "draw" => intval($draw),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos));
        exit();
	}
	function getattributeinfo($attribute) {
	    $navigator=explode("|",$attribute['navigators']);
	    $navigators=array();
	    foreach ($navigator as $nav){
	       if(navinfo($nav,'nname')!=''){
	           $navigators[]=navinfo($nav,'nname');
	       }
	    }
	    $navigatorstr=join("、",$navigators);
	    $opOperate='<a href="javascript:;" class="btn btn-primary" data-attributeid="'.$attribute['tid'].'" data-attributename="'.$attribute['name'].'" onclick="addOptions(this)"><i class="fa fa-plus"></i> 添加选项</a> <a href="javascript:;" class="btn btn-info" data-attributeid="'.$attribute['tid'].'" data-attributename="'.$attribute['name'].'" onclick="manageOptions(this)"><i class="fa fa-gears"></i> 选项管理</a>';
	    $operate='<a href="javascript:;" class="btn btn-primary" data-attributeid="'.$attribute['tid'].'"  onclick="editAttribute(this)"><i class="fa fa-pencil"></i> 编辑</a> <a href="javascript:;" class="btn btn-danger" data-attributeid="'.$attribute['tid'].'" data-attributename="'.$attribute['name'].'" onclick="deleteAttribute(this)"><i class="fa fa-times"></i> 删除</a>';
	    $attributeinfo=array(
	        0=>$attribute['order'],
	        1=>$attribute['tid'],
	        2=>$attribute['name'],
	        3=>$attribute['statu']==1?"开启":"关闭",
	        4=>$navigatorstr,
	        5=>$opOperate,
	        6=>$operate,
	    );
	    return $attributeinfo;
	}
	function attribute_ajax(){
		$id=$this->syArgs('id');
		if(!$this->syArgs('nid')){echo '<label>规格选项：</label><label>未选择栏目，无法加载规格选项，请先选择栏目。</label>';}
		$t=syDB('attribute_type')->findAll('statu=1 and navigators like "%|'.$this->syArgs('nid').'|%"');
		foreach($t as $v){
			$a=syDB('attribute')->findAll(array('statu'=>1,'tid'=>$v['tid']));
			$t='';
			foreach($a as $vv){
				if($id)$c=syDB($this->syArgs('cmark',1).'_attribute')->find(array('aid'=>$id,'sid'=>$vv['sid']));
				if($c){$checked='checked="checked"';$aprice=$c['price'];}else{$checked='';$aprice=0;}
				$t.='<input name="attribute'.$v['tid'].'[]" id="attribute'.$vv['sid'].'" type="checkbox" value="'.$vv['sid'].'" '.$checked.' /> <label for="attribute'.$vv['sid'].'">'.$vv['name'].'</label>&nbsp;&nbsp;&nbsp;价格增减：<input name="aprice'.$vv['sid'].'" type="text" class="int" style="width:50px; height:20px;" value="'.$aprice.'" /> 元<br />';
			}
			echo '<div class="attname" title="'.$v['name'].'">'.$v['name'].'</div><div class="attprice">'.$t.'</div><div class="clearfix"></div>';
		}
	}
}	