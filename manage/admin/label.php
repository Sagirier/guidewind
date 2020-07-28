<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
if (!$_SESSION['auser']){jump("?action=login");}
class label extends syController{
	function __construct(){
		parent::__construct();
		$this->a='system';	
		$this->title="模板调用生成器";
		$this->navigators=syClass('synavigators')->type_txt();
		$this->article=syDB('channels')->find(array('cmark'=>'article'),null,' `cmark`,`cname` ');
		$this->article_nav=$this->navigators;
		$this->article_traits=syDB('traits')->findAll(array('cmark'=>'article'));
		
		$this->product=syDB('channels')->find(array('cmark'=>'product'),null,' `cmark`,`cname` ');
		$this->product_nav=$this->navigators;
		$this->product_traits=syDB('traits')->findAll(array('cmark'=>'product'));
		
		$this->recruitment=syDB('channels')->find(array('cmark'=>'recruitment'),null,' `cmark`,`cname` ');
		$this->recruitment_nav=$this->navigators;
		$this->recruitment_traits=syDB('traits')->findAll(array('cmark'=>'recruitment'));
		
		$this->message=syDB('channels')->find(array('cmark'=>'message'),null,' `cmark`,`cname` ');
		$this->message_nav=$this->navigators;
		
		$this->perchannel=syDB('channels')->findAll(array('statu'=>1,'sys'=>0),null,' `cmark`,`cname` ');
	}
	function index(){
		$this->display('label.html');
	}
	function channels(){
		echo '<link href="manage/admin/template/css/plugins/chosen/chosen.css" rel="stylesheet">';
		$cmark=$this->syArgs('cmark',1);
		$nav=syDB('navigators')->findAll(array('cmark'=>$cmark),' `order` desc,`nid` desc ',' `nid`,`nname`,`order` ');
		$trait=syDB('traits')->findAll(array('cmark'=>$cmark));
		$c='<label style="width: 140px;margin-right: 10px;"><select name="nid" id="nid" class="chosen-select"><option hassubinfo="true" disabled="disabled" selected="selected" style="display: none;" value="">所属栏目</option>';
		foreach($nav as $v){
			$c.='<option value="'.$v['nid'].'">'.$v['nname'].'</option>';
		}
		$c.='</select></label>';
		$c.='<label style="width: 140px;margin-right: 10px;"><select name="nid" id="nid" class="chosen-select"><option hassubinfo="true" disabled="disabled" selected="selected" style="display: none;" value="">属性</option>';
		foreach($trait as $v){
			$c.='<option value="'.$v['tid'].'">'.$v['tname'].'</option>';
		}
		$c.='</select></label>';
		$c.='<label style="width: 140px;margin-right: 10px;"><select name="lipic" class="chosen-select"><option hassubinfo="true" disabled="disabled" selected="selected" style="display: none;" value="">缩略图</option><option value="">全部</option><option value="1">有</option><option value="2">无</option></select></label>';
		echo $c;
		?>
		<script src="manage/admin/template/js/plugins/chosen/chosen.jquery.js"></script>
    	<script>
    			$(document).ready(function () {
            	var elem = document.querySelector('.js-switch');
            var switchery = new Switchery(elem, {
                color: '#1AB394'
            });

            var elem_2 = document.querySelector('.js-switch_2');
            var switchery_2 = new Switchery(elem_2, {
                color: '#ED5565'
            });

            var elem_3 = document.querySelector('.js-switch_3');
            var switchery_3 = new Switchery(elem_3, {
                color: '#1AB394'
            });

        });
        var config = {
            '.chosen-select': {},
            '.chosen-select-deselect': {
                allow_single_deselect: true
            },
            '.chosen-select-no-single': {
                disable_search_threshold: 10
            },
            '.chosen-select-no-results': {
                no_results_text: 'Oops, nothing found!'
            },
            '.chosen-select-width': {
                width: "100%"
            }
        }
        for (var selector in config) {
            $(selector).chosen(config[selector]);
        }
    </script>
    <?php 
	}
	function output(){
		if($this->syArgs('mark',1)){
			if(preg_match("/^[a-zA-Z][a-zA-Z0-9]*$/",$this->syArgs('mark',1))!=0){
				$mark=$this->syArgs('mark',1);$markv=' mark="'.$this->syArgs('mark',1).'"';
			}else{echo '调用标识必须为英文或数字，并且以英文开头';exit;}
		}else{$mark='v';}
		switch($this->syArgs('cmark',1)){
			case 'article':
				$w='data_table="article"';
				if($this->syArgs('nid'))$w.=' data_nid="'.$this->syArgs('nid').'"';
				if($this->syArgs('traits',1))$w.=' data_traits="'.$this->syArgs('traits',1).'"';
				if($this->syArgs('lipic'))$w.=' data_lipic="'.$this->syArgs('lipic').'"';
				if($this->syArgs('password'))$w.=' data_password="'.$this->syArgs('password').'"';
				if($this->syArgs('order',1))$w.=' data_order="order|desc,'.$this->syArgs('order',1).'"';
				if($this->syArgs('minid')>0 && !$this->syArgs('maxid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-*"';
				}
				if(!$this->syArgs('minid') && $this->syArgs('maxid')>0){
					$w.=' data_idrange="*-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')>=$this->syArgs('minid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')<$this->syArgs('minid')){
					echo '最小id不能大于最大id';exit;
				}
				if($this->syArgs('page',1)){
					$w.=' data_page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' data_limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$markv;
				$l='本循环会自动调用指定栏目及所有下级栏目的内容，栏目nid也可以指定多个，如nid="1,2,3"，多个栏目用英文逗号","分隔<br />';
				$l.='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				
				$l.='<tr><td><label>内容id:</label></td><td><span>{$'.$mark.'[';$l.="'id'";$l.=']}</span></td><td>所调用的文章id（值唯一）</td</tr>';
				$l.='<tr><td><label>顺序:</label></td><td><span>{$'.$mark.'[';$l.="'n'";$l.=']}</span></td><td>顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><label>链接地址:</label></td><td><span>{$'.$mark.'[';$l.="'url'";$l.=']}</span></td><td>链接地址为文章的地址（若设置了外链，则为外链地址）</td></tr>';
				$l.='<tr><td><label>属性:</label></td><td><span>{$'.$mark.'[';$l.="'traits'";$l.=']}</span></td><td>文章的属性（若有）</td></tr>';
				$l.='<tr><td><label>标题:</label></td><td><span>{$'.$mark.'[';$l.="'title'";$l.=']}</span></td><td>说明：限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'title'";$l.='],20)}</span>，其中"20"为限制多少个字符</td></tr>';
				$l.='<tr><td><label>缩略图:</label></td><td><span>{$'.$mark.'[';$l.="'litpic'";$l.=']}</span></td><td>若文章设置了缩略图，可以通过img标签调用</td></tr>';
				$l.='<tr><td><label>密码:</label></td><td><span>{$'.$mark.'[';$l.="'password'";$l.=']}</span></td><td>此密码（若有）是经过加密后的密码，可以通过后台文章编辑界面查看真实密码</td></tr>';
				$l.='<tr><td><label>点击次数:</label></td><td><span>{$'.$mark.'[';$l.="'hints'";$l.=']}</span></td><td>文章的点击次数</td></tr>';
				$l.='<tr><td><label>简介:</label></td><td><span>{$'.$mark.'[';$l.="'description'";$l.=']}</span></td><td>限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'description'";$l.='],20)}</span></td></tr>';
				$l.='<tr><td><label>所属栏目ID:</label></td><td><span>{$'.$mark.'[';$l.="'nid'";$l.=']}</span></td><td>文章所属栏目的id</td></tr>';
				$l.='<tr><td><label>所属栏目名:</label></td><td><span>{fun typename($'.$mark.'[';$l.="'nid'";$l.='])}</span></td><td>文章所属栏目名称</td></tr>';
				$l.='<tr><td><label>发布时间:</label></td><td><span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span></td><td>';
				$l.='说明：时间调用中大写Y为4位年份，小写y为两位年份，m为月，d为日，H为小时，i为分钟，s为秒<br />如需要调用2015-12-25 13:55:59样式的日期，即为<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='另外可使用if标签进行最新时间内的特殊显示，如<br /><em></em><span class="green">{if(newest($'.$mark.'[';$l.="'addtime'";$l.='],24))}<br><em></em>&lt;span style=&quot;color:red&quot;&gt;{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}&lt;/span&gt;<br><em></em>{else}<br><em></em>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}<br><em></em>{/if}</span><br>以上代码使用if判断的方式执行：如果内容是24小时内发布，显示红色的日期，否则正常显示日期</td></tr>';
				if($this->syArgs('nid')){
					foreach(syDB('fields')->findAll(' `statu`=1 and `cmark`="article" and `navigators` like "%|'.$this->syArgs('nid').'|%" ',' `order` desc,`fid` ','`fname`,`fmark`,`ftype`') as $v){
					if($v['ftype']=='multifile'){
						$l.='<tr><td><label>'.$v['fname'].':</label></td><td colspan="2">说明：本字段为多个附件，使用循环调用<br />';
						$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$mark.'[';$l.="'".$v['fmark']."'";$l.=']) as $'.$v['fmark'].'}</span><br />';
						$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$'.$v['fmark'].'[0]}</span> 附件文字说明：<span>{$'.$v['fmark'].'[1]}</span><br />';
						$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';
					}else{
						$l.='<tr><td><label>'.$v['fname'].':</label></td><td><span>{$'.$mark.'[';$l.="'".$v['fmark']."'";$l.=']}</span></td><td></td></tr>';
					}
					}
				}
				$l.='</table><span>{/getdata}</span><br />';
				if($this->syArgs('page',1)){$l.='<label>分页代码：</label><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
			case 'product':
				$w='data_table="product"';
				if($this->syArgs('nid'))$w.=' data_nid="'.$this->syArgs('nid').'"';
				if($this->syArgs('traits',1))$w.=' data_traits="'.$this->syArgs('traits',1).'"';
				if($this->syArgs('lipic'))$w.=' data_lipic="'.$this->syArgs('lipic').'"';
				if($this->syArgs('password'))$w.=' data_password="'.$this->syArgs('password').'"';
				if($this->syArgs('order',1))$w.=' data_order="order|desc,'.$this->syArgs('order',1).'"';
				if($this->syArgs('minid')>0 && !$this->syArgs('maxid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-*"';
				}
				if(!$this->syArgs('minid') && $this->syArgs('maxid')>0){
					$w.=' data_idrange="*-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')>=$this->syArgs('minid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')<$this->syArgs('minid')){
					echo '最小id不能大于最大id';exit;
				}
				if($this->syArgs('page',1)){
					$w.=' data_page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' data_limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$markv;
				$l='本循环会自动调用指定栏目及所有下级栏目的内容，栏目nid也可以指定多个，如nid="1,2,3"，多个栏目用英文逗号","分隔<br />';
				$l.='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				
				$l.='<tr><td><label>内容id:</label></td><td><span>{$'.$mark.'[';$l.="'id'";$l.=']}</span></td><td>所调用的商品id（值唯一）</td</tr>';
				$l.='<tr><td><label>顺序:</label></td><td><span>{$'.$mark.'[';$l.="'n'";$l.=']}</span></td><td>顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><label>链接地址:</label></td><td><span>{$'.$mark.'[';$l.="'url'";$l.=']}</span></td><td>链接地址为商品的地址（若设置了外链，则为外链地址）</td></tr>';
				$l.='<tr><td><label>属性:</label></td><td><span>{$'.$mark.'[';$l.="'traits'";$l.=']}</span></td><td>商品的标签（若有）</td></tr>';
				$l.='<tr><td><label>标题:</label></td><td><span>{$'.$mark.'[';$l.="'title'";$l.=']}</span></td><td>说明：限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'title'";$l.='],20)}</span>，其中"20"为限制多少个字符</td></tr>';
				$l.='<tr><td><label>缩略图:</label></td><td><span>{$'.$mark.'[';$l.="'litpic'";$l.=']}</span></td><td>若商品设置了缩略图，可以通过img标签调用</td></tr>';
				$l.='<tr><td><label>图集:</label></td><td colspan="2">使用循环调用<br />';
				$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$mark.'[';$l.="'picture'";$l.=']) as $pk=>$ps}</span><br />';
				$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$ps[0]}</span> 图集文字说明：<span>{$ps[1]}</span> 序号(0开始)：<span>{$pk}</span><br />';
				$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';
				$l.='<tr><td><label>密码:</label></td><td><span>{$'.$mark.'[';$l.="'password'";$l.=']}</span></td><td>此密码（若有）是经过加密后的密码，可以通过后台商品编辑界面查看真实密码</td></tr>';
				$l.='<tr><td><label>点击次数:</label></td><td><span>{$'.$mark.'[';$l.="'hints'";$l.=']}</span></td><td>商品的点击次数</td></tr>';
				if(funsinfo('payment','statu')==1){
					$l.='<tr><td><label>售价：</label></td><td><span>{$'.$mark.'[';$l.="'price'";$l.=']}</span></td><td>商品单价</td></tr>';
					$l.='<tr><td><label>库存：</label></td><td><span>{$'.$mark.'[';$l.="'inventory'";$l.=']}</span></td><td>商品库存</td></tr>';
					$l.='<tr><td><label>已售出：</label></td><td><span>{$'.$mark.'[';$l.="'record'";$l.=']}</span></td><td>已售出商品件数</td></tr>';
				}
				$l.='<tr><td><label>简介:</label></td><td><span>{$'.$mark.'[';$l.="'description'";$l.=']}</span></td><td>限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'description'";$l.='],20)}</span></td></tr>';
				$l.='<tr><td><label>所属栏目ID:</label></td><td><span>{$'.$mark.'[';$l.="'nid'";$l.=']}</span></td><td>商品所属栏目的id</td></tr>';
				$l.='<tr><td><label>所属栏目名:</label></td><td><span>{fun navname($'.$mark.'[';$l.="'nid'";$l.='])}</span></td><td>商品所属栏目名称</td></tr>';
				$l.='<tr><td><label>发布时间:</label></td><td><span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span></td><td>';
				$l.='说明：时间调用中大写Y为4位年份，小写y为两位年份，m为月，d为日，H为小时，i为分钟，s为秒<br />如需要调用2015-12-25 13:55:59样式的日期，即为<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='另外可使用if标签进行最新时间内的特殊显示，如<br /><em></em><span class="green">{if(newest($'.$mark.'[';$l.="'addtime'";$l.='],24))}<br><em></em>&lt;span style=&quot;color:red&quot;&gt;{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}&lt;/span&gt;<br><em></em>{else}<br><em></em>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}<br><em></em>{/if}</span><br>以上代码使用if判断的方式执行：如果内容是24小时内发布，显示红色的日期，否则正常显示日期</td></tr>';
				if($this->syArgs('nid')){
					foreach(syDB('fields')->findAll(' `statu`=1 and `cmark`="product" and `navigators` like "%|'.$this->syArgs('nid').'|%" ',' `order` desc,`fid` ','`fname`,`fmark`,`ftype`') as $v){
						if($v['ftype']=='multifile'){
							$l.='<tr><td><label>'.$v['fname'].':</label></td><td colspan="2">说明：本字段为多个附件，使用循环调用<br />';
							$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$mark.'[';$l.="'".$v['fmark']."'";$l.=']) as $'.$v['fmark'].'}</span><br />';
							$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$'.$v['fmark'].'[0]}</span> 附件文字说明：<span>{$'.$v['fmark'].'[1]}</span><br />';
							$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';
						}else{
							$l.='<tr><td><label>'.$v['fname'].':</label></td><td><span>{$'.$mark.'[';$l.="'".$v['fmark']."'";$l.=']}</span></td><td></td></tr>';
						}
					}
				}
				$l.='</table><span>{/getdata}</span><br />';
				if($this->syArgs('page',1)){$l.='<label>分页代码：</label><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
			case 'message':
				$w='data_table="message"';
				if($this->syArgs('nid'))$w.=' data_nid="'.$this->syArgs('nid').'"';
				if($this->syArgs('audit',1))$w.=' data_statu="'.$this->syArgs('audit',1).'"';
				if($this->syArgs('restatu'))$w.=' data_restatu="'.$this->syArgs('restatu').'"';
				if($this->syArgs('order',1))$w.=' data_order="order|desc,'.$this->syArgs('order',1).'"';
				if($this->syArgs('minid')>0 && !$this->syArgs('maxid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-*"';
				}
				if(!$this->syArgs('minid') && $this->syArgs('maxid')>0){
					$w.=' data_idrange="*-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')>=$this->syArgs('minid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')<$this->syArgs('minid')){
					echo '最小id不能大于最大id';exit;
				}
				if($this->syArgs('page',1)){
					$w.=' data_page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' data_limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$markv;
				$l='本循环会自动调用指定栏目及所有下级栏目的内容，栏目nid也可以指定多个，如nid="1,2,3"，多个栏目用英文逗号","分隔<br />';
				$l.='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				$l.='<tr><td><label>内容id:</label></td><td><span>{$'.$mark.'[';$l.="'id'";$l.=']}</span></td><td>所调用的留言id（值唯一）</td</tr>';
				$l.='<tr><td><label>顺序:</label></td><td><span>{$'.$mark.'[';$l.="'n'";$l.=']}</span></td><td>顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><label>所属栏目ID:</label></td><td><span>{$'.$mark.'[';$l.="'nid'";$l.=']}</span></td><td>留言所属栏目的id</td></tr>';
				$l.='<tr><td><label>所属栏目名:</label></td><td><span>{fun navname($'.$mark.'[';$l.="'nid'";$l.='])}</span></td><td>留言所属栏目名称</td></tr>';
				$l.='<tr><td><label>留言用户昵称:</label></td><td><span>{fun memberoneinfo($'.$mark.'[';$l.="'uid'";$l.='],';$l.="'nickname'";$l.=')}</span></td><td>留言的用户昵称（非用户名）</td></tr>';
				$l.='<tr><td><label>留言用户头像:</label></td><td><span>{fun memberoneinfo($'.$mark.'[';$l.="'uid'";$l.='],';$l.="'portrait'";$l.=')}</span></td><td>留言的用户头像（未设置头像的将默认显示系统头像）</td></tr>';
				$l.='<tr><td><label>标题:</label></td><td><span>{$'.$mark.'[';$l.="'title'";$l.=']}</span></td><td>说明：限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'title'";$l.='],20)}</span>，其中"20"为限制多少个字符</td></tr>';
				$l.='<tr><td><label>留言内容:</label></td><td><span>{$'.$mark.'[';$l.="'detail'";$l.=']}</span></td><td>限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'detail'";$l.='],200)}</span></td></tr>';
				$l.='<tr><td><label>留言时间:</label></td><td><span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span></td><td>';
				$l.='说明：时间调用中大写Y为4位年份，小写y为两位年份，m为月，d为日，H为小时，i为分钟，s为秒;如需要调用2015-12-25 13:55:59样式的日期，即为<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span></td></tr>';
				$l.='<tr><td><label>回复详情:</label></td><td colspan="2">使用循环调用回复详情<br/> <span>{foreach replyinfo($'.$mark.'[';$l.="'id'";$l.=']) as $'.$mark.'r}</span><br/><label class="normal-text">管理员回复：<br/>&nbsp;&nbsp;&nbsp;<span>{if ($'.$mark.'r[';$l.="'adminid'";$l.=']==1)}</span><br/>&nbsp;&nbsp;&nbsp;管理员名称：<span>{$'.$mark.'r[';$l.="'admin_user'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;管理员头像：<span>{$'.$mark.'r[';$l.="'admin_avator'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;回复内容：<span>{$'.$mark.'r[';$l.="'reply'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;回复时间：<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'r[';$l.="'addtime'";$l.='])}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象名称：<span>{$'.$mark.'r[';$l.="'reply_name'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象头像：<span>{$'.$mark.'r[';$l.="'reply_portrait'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象留言：<span>{$'.$mark.'r[';$l.="'reply_detail'";$l.=']}</span></label><label class="normal-text">会员回复：<br/><span>&nbsp;&nbsp;&nbsp;{else}</span><br/>&nbsp;&nbsp;&nbsp;会员昵称：<span>{$'.$mark.'r[';$l.="'member_nickname'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;会员头像：<span>{$'.$mark.'r[';$l.="'member_portrait'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;回复内容：<span>{$'.$mark.'r[';$l.="'reply'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;回复时间：<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'r[';$l.="'addtime'";$l.='])}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象名称：<span>{$'.$mark.'r[';$l.="'reply_name'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象头像：<span>{$'.$mark.'r[';$l.="'reply_portrait'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象留言：<span>{$'.$mark.'r[';$l.="'reply_detail'";$l.=']}</span></label><label class="normal-text"><br/>&nbsp;&nbsp;&nbsp;<span>{/if}</span></label><div class="clearfix"></div><span>{/foreach}</span></td></tr>';
				$l.='</table><span>{/getdata}</span><br />';
				if($this->syArgs('page',1)){$l.='<label>分页代码：</label><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
			case 'recruitment':
				$w='data_table="recruitment"';
				if($this->syArgs('nid'))$w.=' data_nid="'.$this->syArgs('nid').'"';
				if($this->syArgs('traits',1))$w.=' data_traits="'.$this->syArgs('traits',1).'"';
				if($this->syArgs('lipic'))$w.=' data_lipic="'.$this->syArgs('lipic').'"';
				if($this->syArgs('order',1))$w.=' data_order="order|desc,'.$this->syArgs('order',1).'"';
				if($this->syArgs('minid')>0 && !$this->syArgs('maxid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-*"';
				}
				if(!$this->syArgs('minid') && $this->syArgs('maxid')>0){
					$w.=' data_idrange="*-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')>=$this->syArgs('minid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')<$this->syArgs('minid')){
					echo '最小id不能大于最大id';exit;
				}
				if($this->syArgs('page',1)){
					$w.=' data_page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' data_limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$markv;
				$l='本循环会自动调用指定栏目及所有下级栏目的内容，栏目nid也可以指定多个，如nid="1,2,3"，多个栏目用英文逗号","分隔<br />';
				$l.='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				
				$l.='<tr><td><label>内容id:</label></td><td><span>{$'.$mark.'[';$l.="'id'";$l.=']}</span></td><td>所调用的内容id（值唯一）</td</tr>';
				$l.='<tr><td><label>顺序:</label></td><td><span>{$'.$mark.'[';$l.="'n'";$l.=']}</span></td><td>顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><label>链接地址:</label></td><td><span>{$'.$mark.'[';$l.="'url'";$l.=']}</span></td><td>链接地址为内容的地址所调用的栏目链接（若设置了外链，则为外链地址）</td></tr>';
				$l.='<tr><td><label>属性:</label></td><td><span>{$'.$mark.'[';$l.="'traits'";$l.=']}</span></td><td>内容的属性（若有）</td></tr>';
				$l.='<tr><td><label>标题:</label></td><td><span>{$'.$mark.'[';$l.="'title'";$l.=']}</span></td><td>说明：限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'title'";$l.='],20)}</span>，其中"20"为限制多少个字符</td></tr>';
				$l.='<tr><td><label>缩略图:</label></td><td><span>{$'.$mark.'[';$l.="'litpic'";$l.=']}</span></td><td>若内容设置了缩略图，可以通过img标签调用</td></tr>';
				$l.='<tr><td><label>点击次数:</label></td><td><span>{$'.$mark.'[';$l.="'hints'";$l.=']}</span></td><td>内容的点击次数</td></tr>';
				$l.='<tr><td><label>简介:</label></td><td><span>{$'.$mark.'[';$l.="'description'";$l.=']}</span></td><td>限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'description'";$l.='],20)}</span></td></tr>';
				$l.='<tr><td><label>所属栏目ID:</label></td><td><span>{$'.$mark.'[';$l.="'nid'";$l.=']}</span></td><td>内容所属栏目的id</td></tr>';
				$l.='<tr><td><label>所属栏目名:</label></td><td><span>{fun typename($'.$mark.'[';$l.="'nid'";$l.='])}</span></td><td>内容所属栏目名称</td></tr>';
				$l.='<tr><td><label>发布时间:</label></td><td><span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span></td><td>';
				$l.='说明：时间调用中大写Y为4位年份，小写y为两位年份，m为月，d为日，H为小时，i为分钟，s为秒<br />如需要调用2015-12-25 13:55:59样式的日期，即为<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='另外可使用if标签进行最新时间内的特殊显示，如<br /><em></em><span class="green">{if(newest($'.$mark.'[';$l.="'addtime'";$l.='],24))}<br><em></em>&lt;span style=&quot;color:red&quot;&gt;{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}&lt;/span&gt;<br><em></em>{else}<br><em></em>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}<br><em></em>{/if}</span><br>以上代码使用if判断的方式执行：如果内容是24小时内发布，显示红色的日期，否则正常显示日期</td></tr>';
				if($this->syArgs('nid')){
					foreach(syDB('fields')->findAll(' `statu`=1 and `cmark`="recruitment" and `navigators` like "%|'.$this->syArgs('nid').'|%" ',' `order` desc,`fid` ','`fname`,`fmark`,`ftype`') as $v){
						if($v['ftype']=='multifile'){
							$l.='<tr><td><label>'.$v['fname'].':</label></td><td colspan="2">说明：本字段为多个附件，使用循环调用<br />';
							$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$mark.'[';$l.="'".$v['fmark']."'";$l.=']) as $'.$v['fmark'].'}</span><br />';
							$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$'.$v['fmark'].'[0]}</span> 附件文字说明：<span>{$'.$v['fmark'].'[1]}</span><br />';
							$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';
						}else{
							$l.='<tr><td><label>'.$v['fname'].':</label></td><td><span>{$'.$mark.'[';$l.="'".$v['fmark']."'";$l.=']}</span></td><td></td></tr>';
						}
					}
				}
				$l.='</table><span>{/getdata}</span><br />';
				if($this->syArgs('page',1)){$l.='<label>分页代码：</label><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
			case 'perchannel':
				if(!$this->syArgs('channel',1)){echo '请选择频道';exit;}
				$w='data_table="'.$this->syArgs('channel',1).'"';
				if($this->syArgs('nid'))$w.=' data_nid="'.$this->syArgs('nid').'"';
				if($this->syArgs('traits',1))$w.=' data_traits="'.$this->syArgs('traits',1).'"';
				if($this->syArgs('lipic'))$w.=' data_lipic="'.$this->syArgs('lipic').'"';
				if($this->syArgs('order',1))$w.=' data_order="order|desc,'.$this->syArgs('order',1).'"';
				if($this->syArgs('minid')>0 && !$this->syArgs('maxid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-*"';
				}
				if(!$this->syArgs('minid') && $this->syArgs('maxid')>0){
					$w.=' data_idrange="*-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')>=$this->syArgs('minid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')<$this->syArgs('minid')){
					echo '最小id不能大于最大id';exit;
				}
				if($this->syArgs('page',1)){
					$w.=' data_page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' data_limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$markv;
				$l='本循环会自动调用指定栏目及所有下级栏目的内容，栏目nid也可以指定多个，如nid="1,2,3"，多个栏目用英文逗号","分隔<br />';
				$l.='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				
				$l.='<tr><td><label>内容id:</label></td><td><span>{$'.$mark.'[';$l.="'id'";$l.=']}</span></td><td>所调用的内容id（值唯一）</td</tr>';
				$l.='<tr><td><label>顺序:</label></td><td><span>{$'.$mark.'[';$l.="'n'";$l.=']}</span></td><td>顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><label>链接地址:</label></td><td><span>{$'.$mark.'[';$l.="'url'";$l.=']}</span></td><td>链接地址为内容的地址所调用的栏目链接（若设置了外链，则为外链地址）</td></tr>';
				$l.='<tr><td><label>属性:</label></td><td><span>{$'.$mark.'[';$l.="'traits'";$l.=']}</span></td><td>内容的属性（若有）</td></tr>';
				$l.='<tr><td><label>标题:</label></td><td><span>{$'.$mark.'[';$l.="'title'";$l.=']}</span></td><td>说明：限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'title'";$l.='],20)}</span>，其中"20"为限制多少个字符</td></tr>';
				$l.='<tr><td><label>缩略图:</label></td><td><span>{$'.$mark.'[';$l.="'litpic'";$l.=']}</span></td><td>若内容设置了缩略图，可以通过img标签调用</td></tr>';
				$l.='<tr><td><label>点击次数:</label></td><td><span>{$'.$mark.'[';$l.="'hints'";$l.=']}</span></td><td>内容的点击次数</td></tr>';
				$l.='<tr><td><label>简介:</label></td><td><span>{$'.$mark.'[';$l.="'description'";$l.=']}</span></td><td>限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'description'";$l.='],20)}</span></td></tr>';
				$l.='<tr><td><label>所属栏目ID:</label></td><td><span>{$'.$mark.'[';$l.="'nid'";$l.=']}</span></td><td>内容所属栏目的id</td></tr>';
				$l.='<tr><td><label>所属栏目名:</label></td><td><span>{fun typename($'.$mark.'[';$l.="'nid'";$l.='])}</span></td><td>内容所属栏目名称</td></tr>';
				$l.='<tr><td><label>发布时间:</label></td><td><span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span></td><td>';
				$l.='说明：时间调用中大写Y为4位年份，小写y为两位年份，m为月，d为日，H为小时，i为分钟，s为秒<br />如需要调用2015-12-25 13:55:59样式的日期，即为<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='另外可使用if标签进行最新时间内的特殊显示，如<br /><em></em><span class="green">{if(newest($'.$mark.'[';$l.="'addtime'";$l.='],24))}<br><em></em>&lt;span style=&quot;color:red&quot;&gt;{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}&lt;/span&gt;<br><em></em>{else}<br><em></em>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}<br><em></em>{/if}</span><br>以上代码使用if判断的方式执行：如果内容是24小时内发布，显示红色的日期，否则正常显示日期</td></tr>';
				if($this->syArgs('nid')){
					foreach(syDB('fields')->findAll(' `statu`=1 and `cmark`="'.$this->syArgs('channel',1).'" and `navigators` like "%|'.$this->syArgs('nid').'|%" ',' `order` desc,`fid` ','`fname`,`fmark`,`ftype`') as $v){
						if($v['ftype']=='multifile'){
							$l.='<tr><td><label>'.$v['fname'].':</label></td><td colspan="2">说明：本字段为多个附件，使用循环调用<br />';
							$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$mark.'[';$l.="'".$v['fmark']."'";$l.=']) as $'.$v['fmark'].'}</span><br />';
							$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$'.$v['fmark'].'[0]}</span> 附件文字说明：<span>{$'.$v['fmark'].'[1]}</span><br />';
							$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';
						}else{
							$l.='<tr><td><label>'.$v['fname'].':</label></td><td><span>{$'.$mark.'[';$l.="'".$v['fmark']."'";$l.=']}</span></td><td></td></tr>';
						}
					}
				}
				$l.='</table><span>{/getdata}</span><br />';
				if($this->syArgs('page',1)){$l.='<label>分页代码：</label><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
			case 'navigators':
				$w='data_table="navigators"';
				if($this->syArgs('ngid'))$w.=' data_ngid="'.$this->syArgs('ngid').'"';
				if($this->syArgs('hidden'))$w.=' data_statu="1"';
				if($this->syArgs('isnav'))$w.=' data_isnav="1"';
				if($this->syArgs('detail'))$w.=' data_detail="1"';
				if($this->syArgs('sister'))$w.=' data_sister="1"';
				if($this->syArgs('password'))$w.=' data_password="'.$this->syArgs('password').'"';
				$w.=$markv;
				$l='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				
				$l.='<tr><td><label>栏目id:</label></td><td><span>{$'.$mark.'[';$l.="'nid'";$l.=']}</span></td><td>所调用的栏目id（值唯一）</td></tr>';
				$l.='<tr><td><label>栏目名称:</label></td><td><span>{$'.$mark.'[';$l.="'nname'";$l.=']}</span></td><td>所调用的栏目名称</td></tr>';
				$l.='<tr><td><label>栏目顺序:</label></td><td><span>{$'.$mark.'[';$l.="'n'";$l.=']}</span></td><td>所调用的栏目顺序</td></tr>';
				$l.='<tr><td><label>栏目缩略图:</label></td><td><span>{$'.$mark.'[';$l.="'lipic'";$l.=']}</span></td><td>所调用的栏目缩略图（若有）</td></tr>';
				$l.='<tr><td><label>栏目简介:</label></td><td><span>{$'.$mark.'[';$l.="'description'";$l.=']}</span></td><td>所调用的栏目简介</td></tr>';
				if($this->syArgs('detail')){$l.='<tr><td><label>栏目简介:</label></td><td><span>{$'.$mark.'[';$l.="'detail'";$l.=']}</span></td><td>所调用的栏目介绍</td></tr>';}
				$l.='<tr><td><label>栏目链接:</label></td><td><span>{$'.$mark.'[';$l.="'url'";$l.=']}</span></td><td>所调用的栏目链接（若设置了外链，则为外链地址）</td></tr>';
				$l.='<tr><td><label>当前栏目下级栏目调用方法:</label></td><td colspan="2"><span>{getdata table="navigators" ngid="$'.$mark.'[';$l.="'nid'";$l.=']"';if($this->syArgs('detail')){$l.=' data_detail="1"';}$l.=' as="v1"}</span><br/>';
				$l.='<em class="e3"></em>栏目ID:<span>{$v1[';$l.="'nid'";$l.=']}</span>';
				$l.='栏目名称:<span>{$v1[';$l.="'nname'";$l.=']}</span><br />';
				$l.='<em class="e3"></em>栏目缩略图:<span>{$v1[';$l.="'lipic'";$l.=']}</span> ';
				$l.='栏目简介:<span>{$v1[';$l.="'description'";$l.=']}</span><br/>';
				if($this->syArgs('datail')){$l.='<label>栏目介绍:</label> <span>{$'.$mark.'[';$l.="'datail'";$l.=']}</span>';}
				$l.='链接:<span>{$v1[';$l.="'url'";$l.=']}</span> （若设置了外链，则为外链地址）<br />';
				$l.='<em class="e2"></em><span>{/getdata}</span></td></tr>';
				$l.='<tr><td><label>当前栏目下内容调用方法:</label></td><td colspan="2"><em class="e2"></em>"频道标签"与内容"标签"请参照对应频道调用代码<br /><span>{getdata table="频道标签" nid="$'.$mark.'[';$l.="'nid'";$l.=']" as="a"}</span><br />';
				$l.='<em class="e3"></em><span>{$a[';$l.="'标签'";$l.=']}</span> <br />';
				$l.='<em class="e2"></em><span>{/getdata}</span><br /></td></tr>';
				$l.='</table><span>{/getdata}</span><br />';
				$l.='说明：本调用标签包含多级循环嵌套示例，注意在多级循环嵌套时，必须区分每个循环的调用标识"mark",否则会造成嵌套下的数据调用混乱<br />';
				$l.='使用无下级调用同级data_sister=1标识时，适用于栏目页当前栏目下级调用，在当前栏目无下级栏目时，将调用当前同级栏目<br /><br /><br /><br /><br />';
			break;
			case 'navinfo':
				$w='data_table="navigators"';
				if($this->syArgs('nid')){$w.=' data_nid="'.$this->syArgs('nid').'" data_limit="1"';}else{echo '请选择调用栏目';exit;}
				if($this->syArgs('detail'))$w.=' data_detail="1"';
				$w.=$markv;
				$l='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				
				$l.='<tr><td><label>栏目id:</label></td><td><span>{$'.$mark.'[';$l.="'nid'";$l.=']}</span></td><td>所调用的栏目id（值唯一）</td></tr>';
				$l.='<tr><td><label>栏目名称:</label></td><td><span>{$'.$mark.'[';$l.="'nname'";$l.=']}</span></td><td>所调用的栏目名称</td></tr>';
				$l.='<tr><td><label>栏目顺序:</label></td><td><span>{$'.$mark.'[';$l.="'n'";$l.=']}</span></td><td>所调用的栏目顺序</td></tr>';
				$l.='<tr><td><label>栏目缩略图:</label></td><td><span>{$'.$mark.'[';$l.="'lipic'";$l.=']}</span></td><td>所调用的栏目缩略图（若有）</td></tr>';
				$l.='<tr><td><label>栏目简介:</label></td><td><span>{$'.$mark.'[';$l.="'description'";$l.=']}</span></td><td>所调用的栏目简介</td></tr>';
				if($this->syArgs('detail')){$l.='<tr><td><label>栏目简介:</label></td><td><span>{$'.$mark.'[';$l.="'detail'";$l.=']}</span></td><td>所调用的栏目介绍</td></tr>';}
				$l.='<tr><td><label>栏目链接:</label></td><td><span>{$'.$mark.'[';$l.="'url'";$l.=']}</span></td><td>所调用的栏目链接（若设置了外链，则为外链地址）</td></tr>';
				$l.='<tr><td><label>当前栏目下级栏目调用方法:</label></td><td colspan="2"><span>{getdata data_table="navigators" data_ngid="$'.$mark.'[';$l.="'nid'";$l.=']"';if($this->syArgs('detail')){$l.=' data_detail="1"';}$l.=' mark="v1"}</span><br/>';
				$l.='<em class="e3"></em>栏目ID:<span>{$v1[';$l.="'nid'";$l.=']}</span>';
				$l.='栏目名称:<span>{$v1[';$l.="'nname'";$l.=']}</span><br />';
				$l.='<em class="e3"></em>栏目缩略图:<span>{$v1[';$l.="'lipic'";$l.=']}</span> ';
				$l.='栏目简介:<span>{$v1[';$l.="'description'";$l.=']}</span><br/>';
				if($this->syArgs('datail')){$l.='<label>栏目介绍:</label> <span>{$'.$mark.'[';$l.="'datail'";$l.=']}</span>';}
				$l.='链接:<span>{$v1[';$l.="'url'";$l.=']}</span> （若设置了外链，则为外链地址）<br />';
				$l.='<em class="e2"></em><span>{/getdata}</span></td></tr>';
				$l.='<tr><td><label>当前栏目下内容调用方法:</label></td><td colspan="2"><em class="e2"></em>"频道标签"与内容"标签"请参照对应频道调用代码<br /><span>{getdata table="频道标签" nid="$'.$mark.'[';$l.="'nid'";$l.=']" mark="a"}</span><br />';
				$l.='<em class="e3"></em><span>{$a[';$l.="'标签'";$l.=']}</span> <br />';
				$l.='<em class="e2"></em><span>{/getdata}</span><br /></td></tr>';
				$l.='</table><span>{/getdata}</span><br />';

				$l.='调用栏目信息时,栏目nid也可以指定多个，如nid="1,2,3"，多个栏目用英文逗号","分隔<br />';
				$l.='说明：本调用标签包含多级循环嵌套示例，注意在多级循环嵌套时，必须区分每个循环的调用标识"mark",否则会造成嵌套下的数据调用混乱<br /><br /><br /><br /><br />';
			break;
			case 'ads':
				$w='data_table="ads"';
				if($this->syArgs('taid'))$w.=' data_taid="'.$this->syArgs('taid').'"';
				if($this->syArgs('type'))$w.=' data_type="'.$this->syArgs('type').'"';
				if($this->syArgs('limit',1))$w.=' data_limit="'.$this->syArgs('limit',1).'"';
				if($this->syArgs('minid')>0 && !$this->syArgs('maxid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-*"';
				}
				if(!$this->syArgs('minid') && $this->syArgs('maxid')>0){
					$w.=' data_idrange="*-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')>=$this->syArgs('minid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')<$this->syArgs('minid')){
					echo '最小id不能大于最大id';exit;
				}
				$w.=$markv;
				$l='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				$l.='<tr><td><label>广告内容:</label></td><td><span>{$'.$mark.'[';$l.="'body'";$l.=']}</span></td><td>说明：广告内容为系统根据广告类型，自动生成的广告显示代码，并已包含链接等信息</td></tr>';
				$l.='<tr><td><label>顺序:</label></td><td><span>{$'.$mark.'[';$l.="'n'";$l.=']}</span></td><td>顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><label>广告名称:</label></td><td><span>{$'.$mark.'[';$l.="'name'";$l.=']}</span></td><td>调用的广告名称</td></tr>';
				$l.='<tr><td><label>链接地址:</label></td><td><span>{$'.$mark.'[';$l.="'gourl'";$l.=']}</span></td><td>广告的链接地址</td></tr>';
				$l.='<tr><td><label>广告上传文件:</label></td><td><span>{$'.$mark.'[';$l.="'adfile'";$l.=']}</span></td><td>调用的广告上传文件路径</td></tr>';
				$l.='</table><span>{/getdata}</span><br /><br /><br /><br />';
			break;
			case 'links':
				$w='data_table="links"';
				if($this->syArgs('gid'))$w.=' data_gid="'.$this->syArgs('gid').'"';
				if($this->syArgs('type'))$w.=' data_type="'.$this->syArgs('type').'"';
				if($this->syArgs('limit',1))$w.=' data_limit="'.$this->syArgs('limit',1).'"';
				if($this->syArgs('minid')>0 && !$this->syArgs('maxid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-*"';
				}
				if(!$this->syArgs('minid') && $this->syArgs('maxid')>0){
					$w.=' data_idrange="*-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')>=$this->syArgs('minid')){
					$w.=' data_idrange="'.$this->syArgs('minid',1).'-'.$this->syArgs('maxid',1).'"';
				}
				if($this->syArgs('minid')>0 && $this->syArgs('maxid')>0 && $this->syArgs('maxid')<$this->syArgs('minid')){
					echo '最小id不能大于最大id';exit;
				}
				$w.=$markv;
				$l='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				$l.='<tr><td><label>链接名称:</label></td><td><span>{$'.$mark.'[';$l.="'name'";$l.=']}</span></td><td>调用的友情链接名称</td></tr>';
				$l.='<tr><td><label>链接地址:</label></td><td><span>{$'.$mark.'[';$l.="'url'";$l.=']}</span></td><td>友情链接的链接地址</td></tr>';
				$l.='<tr><td><label>图片上传文件:</label></td><td><span>{$'.$mark.'[';$l.="'lipic'";$l.=']}</span></td><td>图片友链的图片文件路径（若有）</td></tr>';
				$l.='</table><span>{/getdata}</span><br /><br /><br /><br />';
			break;
			break;
			case 'comment':
				$w='data_table="comment"';
				if($this->syArgs('nid'))$w.=' data_nid="'.$this->syArgs('nid').'"';
				if($this->syArgs('audit',1))$w.=' data_statu="'.$this->syArgs('audit',1).'"';
				if($this->syArgs('restatu'))$w.=' data_restatu="'.$this->syArgs('restatu').'"';
				if($this->syArgs('order',1))$w.=' data_order="orders|desc,'.$this->syArgs('order',1).'"';
				if($this->syArgs('page',1)){
					$w.=' data_page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' data_limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$markv;
				$l.='<span>{getdata '.$w.'}</span> <br /><table class="table table-bordered label-table"><thead><tr><td style="background: #efefef;">调用名称</td><td style="background: #efefef;">调用方法</td><td style="background: #efefef;">备注说明</td></tr></thead><tbody>';
				$l.='<tr><td><label>内容id:</label></td><td><span>{$'.$mark.'[';$l.="'id'";$l.=']}</span></td><td>所调用的评论id（值唯一）</td</tr>';
				$l.='<tr><td><label>顺序:</label></td><td><span>{$'.$mark.'[';$l.="'n'";$l.=']}</span></td><td>顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><label>评论目标id:</label></td><td><span>{$'.$mark.'[';$l.="'aid'";$l.=']}</span></td><td>评论目标的id（用于调用某篇内容的评论列表）</td></tr>';
				$l.='<tr><td><label>评论目标所属频道:</label></td><td><span>{fun channelsinfo($'.$mark.'[';$l.="'cmark'";$l.='],';$l.="'cname'";$l.=')}</span></td><td>评论目标所属的频道，用于区分评论的是哪个频道下的内容</td></tr>';
				$l.='<tr><td><label>评论用户昵称:</label></td><td><span>{fun memberoneinfo($'.$mark.'[';$l.="'uid'";$l.='],';$l.="'nickname'";$l.=')}</span></td><td>评论的用户昵称（非用户名）</td></tr>';
				$l.='<tr><td><label>评论用户头像:</label></td><td><span>{fun memberoneinfo($'.$mark.'[';$l.="'uid'";$l.='],';$l.="'portrait'";$l.=')}</span></td><td>评论的用户头像（未设置头像的将默认显示系统头像）</td></tr>';
				$l.='<tr><td><label>评论内容:</label></td><td><span>{$'.$mark.'[';$l.="'detail'";$l.=']}</span></td><td>限定字数调用: <span>{fun newstr($'.$mark.'[';$l.="'detail'";$l.='],200)}</span></td></tr>';
				$l.='<tr><td><label>评论时间:</label></td><td><span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span></td><td>';$l.='说明：时间调用中大写Y为4位年份，小写y为两位年份，m为月，d为日，H为小时，i为分钟，s为秒<br />如需要调用2015-12-25 13:55:59样式的日期，即为<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'[';$l.="'addtime'";$l.='])}</span></td></tr>';
				$l.='<tr><td><label>回复详情:</label></td><td colspan="2">使用循环调用回复详情<br/> <span>{foreach replyinfo($'.$mark.'[';$l.="'id'";$l.=']) as $'.$mark.'r}</span><br/><label class="normal-text">管理员回复：<br/>&nbsp;&nbsp;&nbsp;<span>{if ($'.$mark.'r[';$l.="'adminid'";$l.=']==1)}</span><br/>&nbsp;&nbsp;&nbsp;管理员名称：<span>{$'.$mark.'r[';$l.="'admin_user'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;管理员头像：<span>{$'.$mark.'r[';$l.="'admin_avator'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;回复内容：<span>{$'.$mark.'r[';$l.="'reply'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;回复时间：<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'r[';$l.="'addtime'";$l.='])}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象名称：<span>{$'.$mark.'r[';$l.="'reply_name'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象头像：<span>{$'.$mark.'r[';$l.="'reply_portrait'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象评论：<span>{$'.$mark.'r[';$l.="'reply_detail'";$l.=']}</span></label><label class="normal-text">会员回复：<br/><span>&nbsp;&nbsp;&nbsp;{else}</span><br/>&nbsp;&nbsp;&nbsp;会员昵称：<span>{$'.$mark.'r[';$l.="'member_nickname'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;会员头像：<span>{$'.$mark.'r[';$l.="'member_portrait'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;回复内容：<span>{$'.$mark.'r[';$l.="'reply'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;回复时间：<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$mark.'r[';$l.="'addtime'";$l.='])}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象名称：<span>{$'.$mark.'r[';$l.="'reply_name'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象头像：<span>{$'.$mark.'r[';$l.="'reply_portrait'";$l.=']}</span><br/>&nbsp;&nbsp;&nbsp;所回复对象评论：<span>{$'.$mark.'r[';$l.="'reply_detail'";$l.=']}</span></label><label class="normal-text"><br/>&nbsp;&nbsp;&nbsp;<span>{/if}</span></label><div class="clearfix"></div><span>{/foreach}</span></td></tr>';
				$l.='</table><span>{/getdata}</span><br />';
				if($this->syArgs('page',1)){$l.='<label>分页代码：</label><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
		}
		echo $l;
	}
}