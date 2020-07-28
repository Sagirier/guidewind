<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class message extends syController
{	
	function __construct(){
		parent::__construct();
		if(channelsinfo('message','statu')!=1)message("留言功能已关闭");
		$this->Class=syClass('c_message');
		$this->dbl=$GLOBALS['WP']['db']['prefix'];
		$this->db=$this->dbl.'message';
		$this->sy_class_type=syClass('synavigators');
		$this->symember=syClass('symember');
		$this->member=$this->symember->islogin(0);
		$this->uploadfile=$this->syArgs('c',1);
//		$deviceTypeRes = postHttp('/web/data/devicetype',array());
//		$this->devicetypes = $deviceTypeRes['data'];
	}
	function type(){
		if($this->syArgs('file',1)!=''){
			$this->type=syDB('navigators')->find(' `htmlfile`="'.$this->syArgs('file',1).'" or `nid`='.$this->syArgs('file').' ');
			$nid = $this->type['nid'];
		}else{
			$nid = $this->syArgs('nid');
			$this->type=syDB('navigators')->find(" `cmark`='message' and `nid`=".$nid." ");
		}
		if(!$this->type){message("指定栏目不存在");}

	    if($this->type['mrank']==1){
			syClass('symember')->islogin(1,1);
		}
		if($this->type['mrank']==2 && $this->type['password']!=''){
			if(!$_SESSION["type_".$this->type['nid']]){
				message_pass($this->type['nid'],html_url('message', $this->type,'type'),'message',true);
			}
		}
		$this->rid=$this->syArgs('rid');
		$this->cmark=$this->syArgs('cmark',1);
		$this->fields=fields_info($nid,'message');
		$t=$this->type['ct_content'];
        $w = '';
		$w.=" where `statu`=1 ";
		$w.="and `nid`=".$nid." and `cmark`= '' ";
		$order=' order by `order` desc,`addtime` desc,`id` desc';
		$this->fieldinfo=syDB('fields')->findAll(" `cmark`='message' and `navigators` like '%|".$nid."|%' and `lists`=1 ");
        $fields = '';
		if($this->fieldinfo){
			foreach($this->fieldinfo as $v){$fields.=','.$v['fields'];}
			$sql='select `id`,`nid`,`title`,`addtime`,`order`,`statu`,`uid`,`detail`,`restatu`'.$fields.' from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
		}else{
			$sql='select `id`,`nid`,`title`,`addtime`,`order`,`statu`,`uid`,`detail`,`restatu` from '.$this->db.$w.$order;
		}
		$total_count=total_count($this->db.$w);
		$this->lists = $this->Class->syPager($this->syArgs('page',0,1),$this->type['listno'],$total_count)->findSql($sql);
		$pages=$this->Class->syPager()->getPager();
		$this->pages=html_url('navigators',$this->type,$pages,$this->syArgs('page',0,1));
		$this->positions='<a href="'.$GLOBALS["WWW"].'"><i class="fa fa-home"></i> 首页</a>';
		foreach($this->sy_class_type->navi($this->syArgs('nid')) as $v){
			$this->positions.='  <i class="fa fa-angle-double-right"></i>  <a href="'.html_url('navigators',$this->type).'">'.$v['nname'].'</a>';
		}
		$this->display('message/'.$t);
	}
	function add(){
// 		if($GLOBALS['WP']['vercode']==1){
// 		if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
// 		}
		if(!$this->syArgs('nid'))message("请选择栏目");
		$nid=$this->syArgs('nid');
		$this->type=syDB('navigators')->find(array('nid'=>$nid),null,'`cmark`,`nname`,`msubmit`');
		if($this->type['msubmit']==1 || $this->type['msubmit']==2){
			$this->symember->p_v($this->type['msubmit']);
		}
		$statu = ($this->member['group']['audit']==1) ? 1 : 0;
		$uid = ($this->member['uid']!=0) ? $this->member['uid'] : 0;
		$cmark = ($this->syArgs('cmark',1)!='') ? $this->syArgs('cmark',1) : '';
		$title = ($this->syArgs('title',1)!='') ? $this->syArgs('title',1) : $this->type['nname'];
		$detail = code_body($this->syArgs('detail',4));
		$row1 = array('nid' => $nid,'cmark' => $cmark,'rid' => $this->syArgs('rid'),'title' => $title,'addtime' => time(),'order' => 0,'statu' => $statu,'uid' => $uid,'detail' => $detail,'restatu'=>0);
		$row2=$this->fields_args('message',$nid);
		$add = syClass('c_message');
		$a=$add->create($row1);$row2=array_merge($row2,array('aid' => $a));
		syDB('message_field')->create($row2);
// 		if($this->member['uid']!=0){
// 			syDB('member_file')->update(array('hand'=>$this->syArgs('hand'),'uid'=>$this->member['id']),array('hand'=>0,'aid'=>$a,'molds' => 'message'));
// 		}else{
// 			syDB('member_file')->update(array('hand'=>$this->syArgs('hand'),'ip'=>GetIP()),array('hand'=>0,'aid'=>$a,'molds' => 'message'));
// 		}
        if($statu==1){
            message('信息发布成功',$GLOBALS["WWW"]);
        }else {
            message('信息发布成功，请等待管理员审核',$GLOBALS["WWW"]);
        }
	}

    /**
     * 报备
     */
	function preparation(){
	    $datas = $this->syArgs();
	    $res = postHttp('/web/data/preparation',$datas);
	    echo syClass('syjson')->encode($res);die();
    }

    /**
     * 发送邮件
     */
    function sendmail(){
//        $ip = getClientIp();
//        if( $_SESSION['send_mail_'.$ip] ){
//            $res = array(
//                'code' => 502,
//                'msg' => '您已提交过，请勿重复提交！'
//            );
//            echo syClass('syjson')->encode($res);
//            die();
//        }
        $emailtype = "HTML"; //信件类型，文本:text；网页：HTML
        $email = $this->syArgs("email",1); //邮箱地址
        $linkman = $this->syArgs("name",1); //联系姓名
        $phoneNumber = $this->syArgs("phone",1); //电话
        $content = $this->syArgs("message",1); //电话
        $smtpemailfrom = $GLOBALS['WP']['sendemail']['smtp_usermail'];
        $adminEmail = adminuser_oneinfo(0,'aemail');
        $smtpemailto = $adminEmail;
        $emailsubject = $linkman.' 的邮件需求（来自博登智能官网）';
//        $res = array(
//            'code' => 404,
//            'msg' => $smtpemailto
//        );
//        echo syClass('syjson')->encode($res);
//        die();
        $emailbody = '
 	    <style>.tbl_type02{width: 100%;font-size: 14px;}.tbl_type02 p{width: 100%;padding:0;margin:0;text-indent:2em;line-height: 24px;}.tbl_type01 { position: relative; width: 100%;font-size: 14px;}.tbl_type01 caption { position: absolute; overflow: hidden; width: 1px !important; height: 1px !important; padding: 0; border: 0; clip: rect(1px 1px 1px 1px); clip: rect(1px, 1px, 1px, 1px); text-indent: -999em; }.tbl_type01 thead th { padding: 14px 0 12px; color: #fff; font-size: 0.875rem; font-weight: normal; background: #576b7c; border-left: 1px solid #ececec; border-bottom: 1px solid #ececec; }.tbl_type01 tbody td { padding: 14px 0 12px; font-size: 0.75rem; color: #767676; line-height: 20px; border-left: 1px solid #ECECEC; border-bottom: 1px solid #ececec; vertical-align: middle}.txt_left { padding: 14px 15px 12px !important; text-align: left; }.tbl_type01 thead th:first-child,.tbl_type01 tbody td:first-child { border-left: none;}.tbl_type01 tbody td a { color: #767676; }</style>
 	    <table cellspacing="0" class="tbl_type01">
 	        <colgroup>
                <col width="25%">
                <col width="25%">
                <col width="25%">
                <col width="25%">
            </colgroup>
 	        <thead>
        		<tr>
        			<th scope="col">
        				邮箱地址
        			</th>
        			<th scope="col">
        				姓名
        			</th>
        			<th scope="col">
        				联系电话
        			</th>
        			<th scope="col">
        				邮件内容
        			</th>
        		</tr>
        	</thead>
 	        <tbody>
        		<tr>
        			<td class="txt_center">
        				'.$email.'
        			</td>
        			<td class="txt_center">
        				'.$linkman.'
        			</td>
        			<td class="txt_center">
        				'.$phoneNumber.'
        			</td>
        			<td class="txt_center">
        				'.$content.'
        			</td>
        		</tr>
        		<tr>
        		</tr>
        	</tbody></table>
        	<div style="padding:10px 12px;font-size:16px;">
        		邮件说明：
        	</div>
        	<div style="padding:0px 12px;" class="tbl_type02">
        		请勿相信任何冒充官方名义的诈骗邮件。
        	</div>
            <div style="padding:14px 12px;" class="tbl_type02">
				<p>此邮件为系统邮件，无需回复！</p>
        	</div>
        	';
        $rs = syClass('sysendemail')->sendmail($smtpemailto, $smtpemailfrom, $emailsubject, $emailbody, $emailtype);
        if($rs==1){
            $res = array(
                'code' => 200,
                'msg' => '邮件已发送成功，感谢您的来信！'
            );
        }else{
            $res = array(
                'code' => 404,
                'msg' => '邮件发送失败，请重试或联系客服！'
            );
        }
//        if( $res['code'] == 200 ) {
//            $_SESSION['send_mail_'.$ip] = 1;
//        }
        echo syClass('syjson')->encode($res);
        die();
    }

	function m_upload(){
		$aid=$this->syArgs('aid');
		$nid=$this->syArgs('nid');
		$molds=$this->syArgs('molds',1);
		$t=syDB('navigators')->find(array('nid'=>$nid),null,'msubmit');
		if($t['msubmit']!=1){$this->symember->p_r($t['msubmit']);}
		if($this->my['id']!=0){
			$ufm=syDB('member_file')->findSql('SELECT sum(size) FROM '.$GLOBALS['G_DY']['db']['prefix'].'member_file where uid='.$this->my['id']);
			if($ufm[0]['sum(size)']>$this->my['group']['fileallsize']*1024){echo '您的上传空间已满';exit;}
			$fileClass=syClass('syupload',array($this->my['group']['filetype'],$this->my['group']['filesize']*1024));
			$w=' (hand='.$this->syArgs('hand').' and uid='.$this->my['id'].' and fields="'.$this->syArgs('inpunid',1).'") or (hand!='.$this->syArgs('hand').' and hand!=0 and uid='.$this->my['id'].') ';
			if($aid&&$molds)$w.=' or (aid='.$aid.' and molds="'.$molds.'") ';
		}else{
			//游客
			$ip=GetIP();
			$group=syDB('member_group')->find(array('sys'=>1));
			if($group['filesize']<=0||$group['fileallsize']<=0){echo $group['name'].'不能上传文件';exit;}
			$ufm=syDB('member_file')->findSql('SELECT sum(size) FROM '.$GLOBALS['G_DY']['db']['prefix'].'member_file where ip="'.$ip.'"');
			if($ufm[0]['sum(size)']>$group['fileallsize']*1024){echo '您的上传空间已满';exit;}
			$fileClass=syClass('syupload',array($group['filetype'],$group['filesize']*1024));
			$w=' (hand='.$this->syArgs('hand').' and ip="'.$ip.'" and fields="'.$this->syArgs('inpunid',1).'") or (hand!='.$this->syArgs('hand').' and hand!=0 and ip="'.$ip.'") ';
			if($aid&&$molds)$w.=' or (aid='.$aid.' and molds="'.$molds.'") ';
		}
		if (!empty($_FILES)){
			$fileinfos = $fileClass->upload_file($_FILES[$this->syArgs('isfiles',1)]);
			if(is_array($fileinfos)){
				$finfo=array(
					'uid' => $this->my['id'],
					'ip' => $ip,
					'url' => $fileinfos['fn'],
					'size' => $fileinfos['si'],
					'fields' => $this->syArgs('inpunid',1),
					'hand' => $this->syArgs('hand'),
					'molds' => ''
				);
				foreach(syDB('member_file')->findAll($w,null,'url') as $v){@unlink($v['url']);}
				syDB('member_file')->delete($w);
				syDB('member_file')->create($finfo);
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
	}

	function m_upload_load(){
		$this->hand=$this->syArgs('hand',1);
		$this->molds=$this->syArgs('molds',1);
		$this->aid=$this->syArgs('aid');
		$this->nid=$this->syArgs('nid');
		$this->inputid=$this->syArgs('inputid',1);
		if(!$this->hand||$this->inputid=='')message("no hand or inputid");
		$this->multi=$this->syArgs('multi') ? 'true':'false';
		if($this->syArgs('fileExt',1)){$this->fileExt=$this->syArgs('fileExt',1);}else{
			foreach(explode(',',$this->my['group']['filetype']) as $v){
				$fileExt.=';*.'.$v;
			}$this->fileExt=substr($fileExt,1);
		}
		$this->sizeLimit=$this->syArgs('sizeLimit') ? $this->syArgs('sizeLimit'):$this->my['group']['filesize']*1024;
		$this->fileover=$this->syArgs('fileover') ? $this->syArgs('fileover'):1;
		$this->display('system/uploads.php');
	}
	private function fields_args($cmark,$nid=0,$lists=0){
		$fa=array();
		$fieldswhere=" `statu`=1 and issubmit=1 and `cmark`='".$cmark."'";
		if($nid){$fieldswhere.=" and `navigators` like '%|".$nid."|%' ";}
		if($lists){$fieldswhere.=" and `lists`=1 ";}
		$v=syDB('fields')->findAll($fieldswhere,' `order` DESC,`fid` ');
		foreach($v as $f){
			$ns='';$n=array();
			if($f['ftype']=='varchar' || $f['ftype']=='file' || $f['ftype']=='multifile' || $f['ftype']=='radio' || $f['ftype']=='text'){$ns=$this->syArgs($f['fmark'],1);}
			if($f['ftype']=='int'){$ns=$this->syArgs($f['fmark']);}
			if($f['ftype']=='money'){$ns=$this->syArgs($f['fmark'],3);}
			if($f['ftype']=='date'){$ns=strtotime($this->syArgs($f['fmark'],1));}
			if($f['ftype']=='select'){if($this->syArgs($f['fmark'],2)){$ns='|'.implode('|',$this->syArgs($f['fmark'],2)).'|';}else{$ns='';}}
			if($cmark=='member'&&$lists==1){if($ns=='')message("请输入".$f['fname']);}
			$n=array($f['fmark'] => $ns);
			$fa=array_merge($fa,$n);
		}
		return $fa;
	}
}	