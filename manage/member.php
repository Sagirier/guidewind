<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class member extends syController
{	
	function __construct(){
		parent::__construct();
		if(funsinfo('member_sys','statu')!=1)message("会员功能已关闭");
		$this->symember=syClass('symember');
		if($_SESSION['member']['uid']){
		  $this->member=$this->symember->islogin(1);
		}else{
		    $this->member=$this->symember->islogin(0);
		}
		$this->Class=syClass('c_member');
		$this->dbl=$GLOBALS['WP']['db']['prefix'];
		$this->db=$this->dbl.'member';
		$this->tag=$this->syArgs('o',1);
		$imgtypes_arr=array('jpg','jpeg','png','gif','bmp');
		for ($i=0;$i<count($imgtypes_arr);$i++){
		    if(in_array($imgtypes_arr[$i],explode(",", $GLOBALS['WP']['ext']['filetype']))){
		        $member_type[]="*.".$imgtypes_arr[$i];
		    }
		}
		$this->member_type=join(";",$member_type);
		$this->member_typename='图片';
		if($this->syArgs('url',1)){
			$this->backurl=urlencode($this->syArgs('url',1));
			$this->gourl=str_replace('&amp;', '&', $this->syArgs('url',1));
		}else{
			$this->backurl='?action=member';
			$this->gourl='?action=member';
		}
		$this->sy_class_type=syClass('synavigators');
		$this->channels_message=syDB('channels')->find(array('cmark'=>'message'));
		$this->fun_comment_sys=syDB('functions')->find(array('fmark'=>'comment_sys'));
		if($this->member['uid']!=0){
			if($this->member['group']['submit']==1){
				$weight=syDB('member_group')->findAll(' `weight`<'.$this->member['group']['weight'].' ',null,'gid');
				foreach($weight as $v){$w.=$v['gid'].',';}
				$w.=$this->member['gid'];
				$this->typemenu=syDB('navigators')->findAll(' `msubmit` > 0 and `msubmit` in('.$w.') and `cmark`!="message" ',' orders desc,tid ','tid,molds,classname,orders,msubmit');
			}
			$money=syDB('member')->find(array('uid'=>$this->member['uid']),null,'money');
			$this->mymoney=$money['money'];
		}
		if(channelsinfo("message", 'statu')==1){
		    $this->mymessage_switch=true;
		    $this->mymessage=syDB('message')->findAll(array('uid'=>$this->member['uid']));
		}
		if(funsinfo('comment_sys', 'statu')==1){
		    $this->mycomment_switch=true;
		    $this->mycomment=syDB('comment')->findAll(array('uid'=>$this->member['uid']));
		}
		$this->uploadfile=$this->syArgs('c',1);
		//省级地区列表
		$this->province=syDB("district")->findAll(array('level'=>1));
	}
	function index(){
		$this->display("member/index.html");
	}
	function login(){
		if($this->syArgs("go")==1){
			if($this->syArgs("username",1) && $this->syArgs("password",1)){
// 				if($GLOBALS['G_DY']['vercode']==1){
// 				if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
// 				}
				$m_conditions = array('username' => $this->syArgs("username",1),'password' => md5(md5($this->syArgs("password",1)).$this->syArgs("username",1)));
				$mr = syDB('member')->find($m_conditions);
				$this->logtime=$mr['lastlogtime'];
				$a_conditions = array('auser' => $this->syArgs("username",1),'apass' => md5(md5($this->syArgs("password",1)).$this->syArgs("username",1)));
				$ar = syDB('admin')->find($a_conditions);
				if(!$mr){
				    if($ar){
				       $_SESSION['auser'] = array(
        					'auser' => $ar['auser'],
        					'auid' => $ar['auid'],
        				    'uid' => $ar['uid'],
        					'level' => $ar['level'],
        					'gid' => $ar['gid'],
        					'authority' => $ar['authority'],
        				);
        				if($this->syArgs('saveusername',1)){
        					setcookie('username',$ar['auser'],time()+24*3600);
        				}
        				//记录最后一次登录时间
        				syDB('admin')->update(array('auid'=>$_SESSION['auser']['auid']),array('logtime'=>time()));
        				jump($this->gourl);
				    }
					message("用户名或密码错误",null,3,2);
				}else{
				    if($this->syArgs('saveusername',1)){
				        setcookie('username',$this->syArgs("username",1),time()+24*3600);
				    }
					$weight=syDB('member_group')->find(array('gid'=>$mr['gid']));
					$_SESSION['member'] = array(
						'username' => $mr['username'],
						'uid' => $mr['uid'],
					    'lastlogtime' => $mr['lastlogtime'],
					);
					//存储完session后，更新登录时间
					syDB('member')->update(array('uid'=>$mr['uid']),array('lastlogtime'=>time()));
					jump($this->gourl);
				}
			}else{
				message("请输入用户名和密码",null,3,0);
			}
		}
		$this->display("member/login.html");
	}
	function retrieve_password(){
		if($this->syArgs("go")){
// 			if($GLOBALS['G_DY']['vercode']==1){
// 				if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
// 			}
			$ok=false;
			$user=$this->syArgs("user",1);
			$email=$this->syArgs("email",1);
			if($email){
				if($user){
					$conditions = array('user' => $user,'email' => $email);
					$m = syDB('member')->find($conditions,null,'id,user,email');
					if(!$m){message("没有找到匹配的用户和邮箱，请确认用户名及邮箱输入正确。");}else{$ok=true;}
				}else{
					$conditions = array('email' => $email);
					$num = syDB('member')->findCount($conditions);
					if($num<1)message("没有找到匹配的用户和邮箱，请确认用户名及邮箱输入正确。");
					if($num>1)message("此邮箱注册了多个账号，必须填写用户名才可找回密码。");
					if($num==1){$m = syDB('member')->find($conditions,null,'id,user,email');$ok=true;}
				}
			}else{
				message("请输入email地址");
			}
			if($ok){
				$http=get_domain();
				$subject=$http.'密码找回邮件';
				$token=md5($this->syArgs("vercode",1).md5(substr($m['pass'],mt_rand(1,10),mt_rand(10,20)).mt_rand(10000,99999)).$email.time());
				$url=$GLOBALS['WWW'].'index.php?c=member&a=reset_password&id='.$m['id'].'&token='.$token;
				$body='您在'.$GLOBALS['S']['title'].'提交了密码找回邮件，点击下面的链接进行密码重置：<a href="'.$http.$url.'" target="_blank">【点击此处进行密码重置】</a>，如果本次找回密码不是您亲自操作，请忽略本邮件。';
				$send=syClass('syphpmailer');
				$retrieve=$send->Send($email,$m['user'],$subject,$body);
				if(!$retrieve){
					message('邮件发送失败，请联系管理员。');
				}else{
					syDB('member')->update(array('id'=>$m['id']),array('token'=>$token,'tokentime'=>time()));
					message('密码已成功发送至您的邮箱，请点击邮件内容中的链接设置新密码，有效期3天。','?c=member');
				}
			}
		}
		$this->display("member/password.html");
	}
	function reset_password(){
		$id=$this->syArgs("id");
		$token=$this->syArgs("token",1);
		if($id&&$token){
			$conditions = array('id' => $id,'token' => $token);
			$m = syDB('member')->find($conditions,null,'id,user,token,tokentime');
			if(!$m)message('找回密码参数有误');
			$t=time()-$m['tokentime'];
			if($t>259200){
				syDB('member')->update(array('id'=>$id),array('token'=>'','tokentime'=>0));
				message('找回密码链接已过期，请重新申请找回密码。');
			}else{
				if($this->syArgs("go")){
					if($GLOBALS['G_DY']['vercode']==1){
						if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
					}
					if(!$this->syArgs('pass1',1))message("请输入密码");
					if(!$this->syArgs('pass2',1))message("请输入确认密码");
					if($this->syArgs('pass1',1)!=$this->syArgs('pass2',1))message("两次密码输入不一致");
					$newpass=md5(md5($this->syArgs("pass1",1)).$m['user']);
					syDB('member')->update(array('id'=>$id),array('pass'=>$newpass,'token'=>'','tokentime'=>0));
					message('恭喜您，密码已重置成功。',$GLOBALS['WWW'].'index.php?c=member&a=login');
				}else{
					$this->password=$m;
					$this->display("member/password.html");
				}
			}
		}else{
			message('找回密码参数有误');
		}
	}
	function out(){
		$_SESSION['member'] = array();
		if (isset($_COOKIE[session_name()])) {setcookie(session_name(), '', time()-42000, '/');}
		session_destroy();
		jump("index.php?action=member&o=login");
	}
	function rules(){
		if(syDB('member')->find(array('user'=>$this->syArgs('user',1)))){echo 'false';}else{echo 'true';}
	}
	function checkpass(){
	    if(!syDB('member')->find(array('username'=>$this->member['username'],'password'=>md5(md5($this->syArgs('password',1)).$this->member['username'])))){echo 'false';}else{echo 'true';}
	}
	function regedit(){
		$this->fields=fields_info("`issubmit`=1",'member',1);
		if($this->syArgs("go")==1){
		    if(!$this->syArgs('law',1))message('请同意相关条款，否则无法注册',null,3,0);
			if(!$this->syArgs('username',1))message("请输入用户名/密码/邮箱",null,3,0);
			if(syDB('member')->find(array('username'=>$this->syArgs('username',1))))message_member(1,'index.php?action=member&o=regedit','index.php?action=member&o=login',8,0);
			if(!$this->syArgs('password1',1))message("请输入密码",null,3,0);
			if(!$this->syArgs('password2',1))message("请输入确认密码",null,3,0);
			if($this->syArgs('password1',1)!=$this->syArgs('password2',1))message("两次密码输入不一致",null,3,0);
// 			if($GLOBALS['WP']['vercode']==1){
// 			if(md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify']){message("验证码错误");}
// 			}

// 			if(preg_match("/1[3458]{1}\d{9}$/",$this->syArgs('username',1))){
// 			    //验证手机号，发送验证码
// 			    $this->phone=$this->syArgs('username',1);
// 			}
// 			if(preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $this->syArgs('username',1))){
// 			    //验证邮箱，发送验证邮件 
// 			    $this->email=$this->syArgs('username',1);
// 			}
			$newrow1 = array(
				'username' => $this->syArgs('username',1),
				'password' => md5(md5($this->syArgs("password1",1)).$this->syArgs("username",1)),
				'gid' => 2,
				'money' => 0,
			    'credit' => 0,
			    'adminid' => 0,
				'regtime' => time(),
			    'portrait' => $GLOBALS['WP']['member']['default_portrait'],
			    'nickname' => '',
			);
			$addnewrow=$this->Class->create($newrow1);
			if($addnewrow==FALSE){message("注册失败，请重新注册",null,3,2);}
			$newrow2 = array(
			    'uid' => $addnewrow,
			);
			$newrow2=array_merge($newrow2,$this->fields_args('member',0,1));
		    $newrow3=array(
		        'uid' => $addnewrow,
		        'email' => $this->syArgs('email',1),
		    );
		    syDB('member_field')->create($newrow2);
		    syDB("member_contact")->create($newrow3);
		    message_member(2,'javascript:history.go(-1);',$this->gourl);
		}
		$this->display("member/regedit.html");
	}
	function myinfo(){
	    $this->province_info=syDB("district")->find(array('name'=>$this->member['resideprovince']));
	    //所在省下级市级地区列表
	    $this->city_lists=syDB("district")->findAll(array('upid'=>$this->province_info['id']));
	    //该会员所在市信息
	    $this->city_info=syDB("district")->find(array('name'=>$this->member['residecity']));
	    //所在市下级区/乡镇级地区列表
	    $this->dist_lists=syDB("district")->findAll(array('upid'=>$this->city_info['id']));
	    $this->field=syDB("member_field")->find(array('uid'=>$this->member['uid']));
	    $this->fields=syDB('fields')->findAll(array('cmark'=>'member','statu'=>1)," `order` DESC,`fid`");
	    $field_input=array();
	    foreach ($this->fields as $f){
	        switch ($f['ftype']){
	            case "varchar":
	                if($f['flength']!=0 && $f['flength']!=255 ){
	                    $length=$f['flength']."px";
	                }else {
	                    $length="100%";
	                }
	                $input='<input class="form-control" style="width:'.$length.'" type="text" value="'.$this->field[$f['fmark']].'" placeholder="文本格式，不能超过'.$f['flength'].'个字符" name="'.$f['fmark'].'" />';
	                $field_input[$f['fname']]['input']=$input;
	                break;
	            case "int":
	                $input='<input type="text" class="form-control" style="width:100%" type="text" value="'.$this->field[$f['fmark']].'" placeholder="整数格式，请输入整数" name="'.$f['fmark'].'" />';
	                $field_input[$f['fname']]['input']=$input;
	                break;
	            case "money":
	                $input='<span class="input-group-addon" style="display:inline-block;float:left;height:34px;line-height:34px; padding:0; width:10.6%; text-align:center;">¥</span><input type="text" class="form-control" style="display:inline-block;float:left;width:89.4%;" placeholder="货币格式，请输入正确格式" name="'.$f['fmark'].'" value="'.$this->field[$f['fmark']].'" />';
	                $field_input[$f['fname']]['input']=$input;
	                break;
	            case "date":
	                $date=$this->field[$f['fmark']] ? date('Y-m-d H:i:s',$this->field[$f['fmark']]) : date('Y-m-d H:i:s',time());
	                $input='<input class="form-control layer-date" placeholder="日期格式，请输入正确的日期" type="text" name="'.$f['fmark'].'" value="'.$date.'" />';
	                $field_input[$f['fname']]['input']=$input;
	                break;
	            case "file";
	            $input='<input type="file" style="width: 191px;" class="form-control ex-lipic" name="'.$f['fmark'].'" value="'.$this->field[$f['fmark']].'" />';
	            $field_input[$f['fname']]['input']=$input;
	            break;
	            case "multifile":
	                $input='<input type="file" style="width: 191px;" class="form-control ex-lipic" name="'.$f['fmark'].'" value="'.$this->field[$f['fmark']].'" multiple="multiple" />';
	                $field_input[$f['fname']]['input']=$input;
	                break;
	            case "select":
	                $input='<select class="ex-select" data-placeholder="添加/更改要求" multiple="" name="'.$f['fmark'].'" tabindex="2" style="width: 100%;">';
	                foreach (explode(",", $f['selects']) as $n){
	                    $p=explode("=", $n);
	                    if($this->field[$f['fmark']]==$p[1]){
	                        $input.='<option hassubinfo="true" value="'.$p[1].'" selected="selected">'.$p[0].'</option>';
	                    }else {
	                        $input.='<option hassubinfo="true" value="<?php echo $p[1]; ?>"><?php echo $p[0]; ?></option>';
	                    }
	                }
	                $input.='</select>';
	                $field_input[$f['fname']]['input']=$input;
	                break;
	            case "radio":
	                $input='<select class="ex-select" name="'.$f['fmark'].'" tabindex="2" style="width: 100%;">';
	                foreach (explode(",", $f['selects']) as $n){
	                    $p=explode("=", $n);
	                    if($this->field[$f['fmark']]==$p[1]){
	                        $input.='<option hassubinfo="true" value="'.$p[1].'" selected="selected">'.$p[0].'</option>';
	                    }else {
	                        $input.='<option hassubinfo="true" value="'.$p[1].'">'.$p[0].'</option>';
	                    }
	                }
	                $input.='</select>';
	                $field_input[$f['fname']]['input']=$input;
	                break;
	            case "text":
	                $input='<textarea style="width:'.$f['imgw'].'px; height:'.$f['imgh'].'px" name="'.$f['fmark'].'" placeholder="文本域格式，请输入内容" class="summernote">'.code_body($this->field[$f['fmark']],0).'</textarea>';
	                $field_input[$f['fname']]['input']=$input;
	                break;
	        }
	        $field_input[$f['fname']]['group']=trim($f['navigators'],"|");
	    }
	    $field1=array();$field2=array();$field3=array();
	    foreach ($field_input as $key=>$val){
	        if($val['group']==1){  //基本信息字段
	            $field1[$key]=$val['input'];
	        }
	        if($val['group']==2){  //联系信息字段
	            $field2[$key]=$val['input'];
	        }
	        if($val['group']==3){  //账户信息字段
	            $field3[$key]=$val['input'];
	        }
	    }
	    $this->field1=$field1;
	    $this->field2=$field2;
	    $this->field3=$field3;
		if($this->syArgs("go")==1){
			$newrow1 = array(
			    'sexuality' => $this->syArgs('sexuality',1),
			);
			if($this->syArgs('nickname',1)){
			    $newrow1=array_merge($newrow1,array('nickname'=>$this->syArgs('nickname',1)));
			}
			$newrow2=array();
			$newrow2=array_merge($newrow2,$this->fields_args('member'));
			$newrow3=array(
			    'realname' => $this->syArgs('realname',1),
			    'email' => $this->syArgs('email',1),
			    'telephone' => $this->syArgs('telephone',1),
			    'mobile' => $this->syArgs('mobile',1),
			    'birthyear' => $this->syArgs('birthyear',1),
			    'birthmonth' => $this->syArgs('birthmonth',1),
			    'birthday' => $this->syArgs('birthday',1),
			    'zipcode' => $this->syArgs('zipcode',1),
			    'company' => $this->syArgs('company',1),
			);
			if($this->syArgs('resideprovince')){
			    $resideprovince=syDB("district")->find(array('id'=>$this->syArgs('resideprovince')));
			    $newrow3=array_merge($newrow3,array('resideprovince' =>$resideprovince['name']));  
			}
			if($this->syArgs('residecity')){
			    $resideprovince=syDB("district")->find(array('id'=>$this->syArgs('residecity')));
			    $newrow3=array_merge($newrow3,array('residecity' =>$resideprovince['name']));  
			}
			if($this->syArgs('residedist')){
			    $resideprovince=syDB("district")->find(array('id'=>$this->syArgs('residedist')));
			    $newrow3=array_merge($newrow3,array('residedist' =>$resideprovince['name']));  
			}
			if($this->syArgs('address',1)){
			    $newrow3=array_merge($newrow3,array('address' =>$this->syArgs('address',1)));
			}
			if($this->syArgs('resideprovince',1) && (!$this->syArgs('residecity',1) || !$this->syArgs('residedist',1))){
			    message("请选择城市，地区",null,3,0);
			}
			syDB('member')->update(array('uid'=>$this->member['uid']),$newrow1);
			syDB('member_field')->update(array('uid'=>$this->member['uid']),$newrow2);
			syDB('member_contact')->update(array('uid'=>$this->member['uid']),$newrow3);
			message("资料修改成功");
		}
		$user=syDB('member')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where id='.$this->my['id']);
		$this->myinfo=$user[0];
		$this->fields=fields_info(0,'member',0,$this->myinfo);
		$this->display("member/myinfo.html");
	}
	function myportrait(){
	    $this->display('member/avator.html');
	}
	function mypassword(){
	    if($this->syArgs('go')==1){
	        if(!$this->syArgs('password',1))message("请输入当前密码",null,2,0);
	        if(!syDB('member')->find(array('username'=>$this->member['username'],'password'=>md5(md5($this->syArgs("password",1)).$this->member['username']))))message("当前密码输入错误",null,2,2);
	        if(!$this->syArgs('password1',1))message("请输入新密码",null,2,0);
	        if(!$this->syArgs('password2',1))message("请输入确认新密码",null,2,0);
	        if($this->syArgs('password1',1)!=$this->syArgs('password2',1))message("两次密码输入不一致",null,2,0);
	        $newrow1=array();
	        $newrow1=array_merge($newrow1,array('password' => md5(md5($this->syArgs("password1",1)).$this->member['username'])));
	        if(!syDB('member')->update(array('uid'=>$this->member['uid'],'username'=>$this->member['username']),$newrow1)){
	            message("密码修改失败",null,3,2);
	        }
	        message("密码修改成功");
	    }
	    $this->display('member/mypassword.html');
	}
	function mylist(){
		if(!$this->syArgs('tid'))message("请指定内容tid","?c=member");
		$tid = $this->syArgs('tid');
		$this->type=syDB('classtype')->find(array('tid'=>$tid),null,'tid,molds,mrank,classname,msubmit');
		$c=syClass('c_'.$this->type['molds']);
		$this->member->p_v($this->type['mrank']);
		$db=$GLOBALS['G_DY']['db']['prefix'].$this->type['molds'];
		$tid_leafid=$this->sy_class_type->leafid($tid);
			$w=" where tid in(".$tid_leafid.") and user='".$this->my['user']."' and usertype=1 ";
			$order=' order by orders desc,id desc';
			$f=syDB('fields')->findAll(" molds='".$this->type['molds']."' and types like '%|".$tid."|%' and lists=1 ");
			if($f){
				foreach($f as $v){$fields.=','.$v['fields'];}
				$sql='select a.*'.$fields.' from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order;
			}else{
				$sql='select * from '.$db.$w.$order;
			}
		$total_page=total_page($db.$w);
		$this->lists = $c->syPager($this->syArgs('page',0,1),20,$total_page)->findSql($sql);
		$pages=$c->syPager()->getPager();
		$this->pages=pagetxt($pages,$GLOBALS['WP']['url']["url_path_base"].'?c=member&a=mylist&tid='.$tid);
		$this->display("member/mylist.html");
	}
	function mymessage(){
		$c=syClass('c_message');
		$total_count=total_count($GLOBALS['WP']['db']['prefix'].'message where `uid`="'.$this->member['uid'].'"');
		$this->lists=$c->syPager($this->syArgs('page',0,1),10,$total_count)->findAll(array('uid'=>$this->member['uid']),' `addtime` desc ');
		$c_page=$c->syPager()->getPager();
		$this->pages=pagetxt($c_page,$GLOBALS['WP']['url']["url_path_base"].'?action=member&o=mymessage');
		$this->display("member/mymessage.html");
	}
	function mycomment(){
		$c=syClass('c_comment');
		$total_count=total_count($GLOBALS['WP']['db']['prefix'].'comment where `uid`="'.$this->member['uid'].'"');
		$this->lists=$c->syPager($this->syArgs('page',0,1),10,$total_count)->findAll(array('uid'=>$this->member['uid']),' `addtime` desc ');
		$c_page=$c->syPager()->getPager();
		$this->pages=pagetxt($c_page,$GLOBALS['WP']['url']["url_path_base"].'?action=member&o=mycomment');
		$this->display("member/mycomment.html");
	}
	function mymolds(){
		$c=syClass('c_account');
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'account where uid='.$this->my['id'].' and type=4');
		$this->lists = $c->syPager($this->syArgs('page',0,1),20,$total_page)->findAll(array('uid'=>$this->my['id'],'type'=>4),' addtime desc ');
		$pages=$c->syPager()->getPager();
		$this->pages=pagetxt($pages,$GLOBALS['G_DY']['url']["url_path_base"].'?c=member&a=mymolds');
		$this->display("member/mymolds.html");
	}
	function myorder(){
		if($this->syArgs('oid')||$this->syArgs('orderid',1)!=''){
			if($this->syArgs('oid')){$r=array('id'=>$this->syArgs('oid'));}else{$r=array('orderid'=>$this->syArgs('orderid',1));}
			$this->order=syDB('order')->find($r);
			if($this->order['state']>0&&$this->order['virtual']==1)$this->virtuals=syDB('product_virtual')->findAll(array('oid'=>$this->order['id'],'state'=>1));
			$this->goods=order_goods(unserialize($this->order['goods']),$this->order['logistics']);
			$this->info=unserialize($this->order['info']);
			$this->sendgoods=unserialize($this->order['sendgoods']);
			$total=0;
			foreach($this->goods[0] as $v){
				$total=calculate($total,$v['total']);
				$total=calculate($total,$v['logistics_price']);
			}
			$this->aggregate=calculate($total, $this->order['favorable'],2);
			$this->display("member/myorderinfo.html");
		}else{
			$c=syClass('c_order');
			$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'order where uid='.$this->my['id']);
			$this->lists=$c->syPager($this->syArgs('page',0,1),10,$total_page)->findAll(array('uid'=>$this->my['id']),' addtime desc ');
			$c_page=$c->syPager()->getPager();
			$this->pages=pagetxt($c_page,$GLOBALS['G_DY']['url']["url_path_base"].'?c=member&a=myorder');
			$this->display("member/myorder.html");
		}
	}
	function account(){
		$a=syClass('syaccount');
		$c=syClass('c_account');
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'account where uid='.$this->my['id']);
		$this->lists = $c->syPager($this->syArgs('page',0,1),20,$total_page)->findAll(array('uid'=>$this->my['id']),' addtime desc ');
		$lists = $this->lists;
		foreach($lists as $k=>$v){
			$lists[$k]['info']=$a->userinfo($v);
			$lists[$k]['pn']=$a->pn($v['type']);
		}
		$this->lists = $lists;
		$pages=$c->syPager()->getPager();
		$this->pages=pagetxt($pages,$GLOBALS['G_DY']['url']["url_path_base"].'?c=member&a=account');
		$this->display("member/account.html");
	}
	function recharge(){
		$p=syDB('payment')->findall(array('isshow'=>1),'orders desc,id desc');
		foreach($p as $k=>$v){
			$service=unserialize($v['keyv']);
			if($service['service']==2)unset($p[$k]);
			if($v['pay']=='cashbalance')unset($p[$k]);
			if($v['pay']=='offline')unset($p[$k]);
		}
		if($p){$p[0]['n']=1;$this->payment=$p;}
		$this->display("member/recharge.html");
	}
	function mydel(){ //删除内容
		$cmark=$this->syArgs('cmark',1);
		$id=$this->syArgs('id');
		switch ($cmark){
			case 'comment':
				if(contentinfo('comment',$id,'restatu')==1){//已回复的信息
			        message("此内容已经回复，您无法删除，请联系管理员删除",null,3,0);
			    }
				if(!syDB('comment')->delete(array('id'=>$id,'uid'=>$this->member['uid']))){
					message("删除失败,请重新提交",null,3,2);
				}
				syDB('comment_reply')->delete(array('mid'=>$id));
			break;
			case 'comment_reply':
			    if(syDB("comment_reply")->find(array('upid'=>$id))){ //信息被回复
			        message("此内容已经回复，您无法删除，请联系管理员删除",null,3,0);
			    }
			    if(!syDB('comment_reply')->delete(array('id'=>$id,'reuid'=>$this->member['uid']))){
			        message("删除失败,请重新提交",null,3,2);
			    }
			break;
			case 'message':
			    if(contentinfo('message',$id,'restatu')==1){//已回复的信息
			        message("此内容已经回复，您无法删除，请联系管理员删除",null,3,0);
			    }
				if(!syDB('message')->delete(array('id'=>$id,'uid'=>$this->member['uid']))){
					message("删除失败,请重新提交",null,3,2);
				}
				syDB('message_field')->delete(array('aid'=>$id));
				syDB('message_reply')->delete(array('mid'=>$id));
			break;
			case 'message_reply':
			    if(syDB("message_reply")->find(array('upid'=>$id))){ //信息被回复
			        message("此内容已经回复，您无法删除，请联系管理员删除",null,3,0);
			    }
			    if(!syDB('message_reply')->delete(array('id'=>$id,'reuid'=>$this->member['uid']))){
			        message("删除失败,请重新提交",null,3,2);
			    }
			break;
			default:
				$c=syDB($cmark)->find(array('id'=>$id,'user'=>$this->my['user'],'usertype'=>1),null,'id,isshow');
				if(!$c||$c['isshow']==1)message("此内容已经审核或不是您发布的内容，不能删除。");
				if(!syDB($cmark)->delete(array('id'=>$id,'user'=>$this->my['user'],'usertype'=>1))){
					message("删除失败,请重新提交");
				}
				syDB($cmark.'_field')->delete(array('aid'=>$id));
			break;
		}
		message("删除成功");
	}
	function myedit(){ //编辑内容
	    $cmark=$this->syArgs('cmark',1);
	    $id=$this->syArgs('id');
	    switch ($cmark){
	        case 'comment':
	            if(contentinfo('comment',$id,'statu')==1){//已审核的信息
	                message("此内容已经审核，您无法修改，请联系管理员修改",null,3,0);
	            }
	            if($this->syArgs('detail',1)==''){
	                echo "内容不能为空";
	                exit();
	            }
	            if(!syDB('comment')->update(array('id'=>$id,'uid'=>$this->member['uid']),array('detail'=>$this->syArgs('detail',1)))){
	                echo "修改失败,请重新提交";
	                exit();
	            }else {
	                echo "修改成功";
	                exit();
	            }
	        break;
	        case 'comment_reply':
	            if(!syDB('comment_reply')->update(array('id'=>$id,'reuid'=>$this->member['uid']),array('reply'=>$this->syArgs('detail',1)))){
	                echo "修改失败,请重新提交";
	                exit();
	            }else {
	                echo "修改成功";
	                exit();
	            }
	        break;
	        case 'message':
	            if(contentinfo('message',$id,'statu')==1){//已审核的信息
	                message("此内容已经审核，您无法修改，请联系管理员修改",null,3,0);
	            }
	            if($this->syArgs('detail',1)==''){
	                echo "内容不能为空";
	                exit();
	            }
	            if(!syDB('message')->update(array('id'=>$id,'uid'=>$this->member['uid']),array('detail'=>$this->syArgs('detail',1)))){
	                echo "修改失败,请重新提交";
	                exit();
	            }else {
	                echo "修改成功";
	                exit();
	            }
	        break;
            case 'message_reply':
                if(!syDB('message_reply')->update(array('id'=>$id,'reuid'=>$this->member['uid']),array('reply'=>$this->syArgs('detail',1)))){
                    echo "修改失败,请重新提交";
                    exit();
                }else {
                    echo "修改成功";
                    exit();
                }
            break;
	        default:
	            $c=syDB($cmark)->find(array('id'=>$id,'user'=>$this->my['user'],'usertype'=>1),null,'id,isshow');
	            if(!$c||$c['isshow']==1)message("此内容已经审核或不是您发布的内容，不能删除。");
	            if(!syDB($cmark)->delete(array('id'=>$id,'user'=>$this->my['user'],'usertype'=>1))){
	                message("删除失败,请重新提交");
	            }
	            syDB($cmark.'_field')->delete(array('aid'=>$id));
	            break;
	    }
	}
	function myreply(){ //回复管理员
	    $cmark=$this->syArgs('cmark',1);
	    $id=$this->syArgs('id');
	    $upid=$this->syArgs('upid');
	    switch ($cmark){
	        case 'comment':
	            $reply=array(
	               'mid' => $id,
	               'upid' => $upid,
	               'reply' =>$this->syArgs('reply',1),
	               'retime' => time(),
	               'reuid' => $this->member['uid'],
	               'adminid' => 0,
	            );
	            if(!syDB('comment_reply')->create($reply)){
	                message("回复失败",null,2,0);
	            }else {
	                message("回复成功",null,2);
	            }
	            break;
	        case 'message':
	            $reply=array(
	               'mid' => $id,
	               'upid' => $upid,
	               'reply' =>$this->syArgs('reply',1),
	               'retime' => time(),
	               'reuid' => $this->member['uid'],
	               'adminid' => 0,
	            );
	            if(!syDB('message_reply')->create($reply)){
	                message("回复失败",null,2,0);
	            }else {
	                message("回复成功",null,2);
	            }
	            break;
	        default:
	            $c=syDB($cmark)->find(array('id'=>$id,'user'=>$this->my['user'],'usertype'=>1),null,'id,isshow');
	            if(!$c||$c['isshow']==1)message("此内容已经审核或不是您发布的内容，不能删除。");
	            if(!syDB($cmark)->delete(array('id'=>$id,'user'=>$this->my['user'],'usertype'=>1))){
	                message("删除失败,请重新提交");
	            }
	            syDB($cmark.'_field')->delete(array('aid'=>$id));
	            break;
	    }
	    message("修改成功");
	}
	
	function release(){
		if(!$this->syArgs('tid'))message("请选择栏目","?c=member");
		$this->id=$this->syArgs('id');
		$tid=$this->syArgs('tid');
		$this->type=syDB('classtype')->find(array('tid'=>$tid),null,'tid,molds,classname,msubmit');
		if($this->type['msubmit']!=1){
			$this->member->p_r($this->type['msubmit']);
		}
		if($this->syArgs("go")==1){
			if($GLOBALS['G_DY']['vercode']==1){
			if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
			}
			$isshow = ($this->my['group']['audit']==1) ? 1 : 0;
			//按频道投稿入库
			  $row1 = array('tid' => $tid,'sid' => 0,'title' => $this->syArgs('title',1),'style' => '','trait' => '','gourl' => '','htmlfile' => '','htmlurl' => '','addtime' => time(),'hits' => 0,'litpic' => '','orders' => 0,'mrank' => 0,'mgold' => 0,'isshow' => $isshow,'keywords' => '','description' => '','user' => $this->my['user'],'usertype' => 1);
			  if($this->type['molds']=='product')$row1=array_merge(array('price' => $this->syArgs('price',3),'photo' => ''),$row1);
			  $row2=array_merge(array('body' => $this->syArgs('body',1)),$this->fields_args($this->type['molds'],$tid));
			  $add = syClass('c_'.$this->type['molds']);$newv=$add->syVerifier($row1);
			  if(false == $newv){
				  if($this->id){
					  if(syDB($this->type['molds'])->find(array('tid'=>$tid,'id'=>$this->id,'user'=>$this->my['user'],'usertype'=>1))){
						  syDB($this->type['molds'])->update(array('id' => $this->id),$row1);
						  syDB($this->type['molds'].'_field')->update(array('aid' => $this->id),$row2);
					  }else{message('无权操作');}
				  }else{
					  $a=$add->create($row1);$row2=array_merge($row2,array('aid' => $a));
					  syDB($this->type['molds'].'_field')->create($row2);
				  }
				  syDB('member_file')->update(array('hand'=>$this->syArgs('hand'),'uid'=>$this->my['id']),array('hand'=>0,'aid'=>$a,'molds' => $this->type['molds']));
				  message('内容更新成功','?c=member&a=mylist&tid='.$tid);
			  }else{message_err($newv);}
			//--------------
		}
		$this->hand=date('His').mt_rand(100,999);
		if($this->id){
		$c=syDB($this->type['molds'])->findSql('select * from '.$this->dbl.$this->type['molds'].' a left join '.$this->dbl.$this->type['molds'].'_field b on (a.id=b.aid) where user="'.$this->my['user'].'" and usertype=1 and id='.$this->id);
		$c=$c[0];
		}
		
		$this->fields=array();
		//按频道显示投稿字段
		switch ($this->type['molds']){
			case 'article':
				$a=array(
					array('name'=>'标题','input'=>'<input name="title" id="title" type="text" class="inp" value="'.$c['title'].'" style="width:300px;" />','fields'=>'title'),
					array('name'=>'内容','input'=>'<script type="text/javascript">$(function(){KindEditor.create("#body",{resizeType : 1,allowPreviewEmoticons : false,allowImageUpload : false,items : ["fontname", "fontsize", "|", "forecolor", "hilitecolor", "bold", "italic", "underline","removeformat", "|", "justifyleft", "justifycenter", "justifyright", "insertorderedlist","insertunorderedlist", "|", "emoticons", "image", "link"]})});</script><textarea name="body" id="body" class="inp" style="width:550px;height:300px;">'.$c['body'].'</textarea>','fields'=>'body'),
				);
				$this->fields=array_merge($this->fields,$a);
			break;
			case 'product':
				$a=array(
					array('name'=>'标题','input'=>'<input name="title" id="title" type="text" class="inp" value="'.$c['title'].'" style="width:300px;" />','fields'=>'title'),
					array('name'=>'价格','input'=>'<input name="price" id="price" type="text" class="inp" value="'.$c['price'].'" style="width:300px;" />','fields'=>'price'),
					array('name'=>'内容','input'=>'<script type="text/javascript">$(function(){KindEditor.create("#body",{resizeType : 1,allowPreviewEmoticons : false,allowImageUpload : false,items : ["fontname", "fontsize", "|", "forecolor", "hilitecolor", "bold", "italic", "underline","removeformat", "|", "justifyleft", "justifycenter", "justifyright", "insertorderedlist","insertunorderedlist", "|", "emoticons", "image", "link"]})});</script><textarea name="body" id="body" class="inp" style="width:550px;height:300px;">'.$c['body'].'</textarea>','fields'=>'body'),
				);
				$this->fields=array_merge($this->fields,$a);
			break;
			default:
			$a=array(
					array('name'=>'标题','input'=>'<input name="title" id="title" type="text" class="inp" value="'.$c['title'].'" style="width:300px;" />','fields'=>'title'),
				);
			break;
			
		}
		//--------------
		if($c){$this->fields=array_merge($this->fields,fields_info($tid,$this->type['molds'],0,$c));}
		else{$this->fields=array_merge($this->fields,fields_info($tid,$this->type['molds']));}
		$this->display("member/release.html");
	}
	function m_upload(){
		$aid=$this->syArgs('aid');
		$tid=$this->syArgs('tid');
		$molds=$this->syArgs('molds',1);
		$t=syDB('classtype')->find(array('tid'=>$tid),null,'msubmit');
		if($t['msubmit']!=1){$this->member->p_r($t['msubmit']);}
		if($this->my['id']!=0){
			$ufm=syDB('member_file')->findSql('SELECT sum(size) FROM '.$this->db.'_file where uid='.$this->my['id']);
			if($ufm[0]['sum(size)']>$this->my['group']['fileallsize']*1024){echo '您的上传空间已满';exit;}
			$fileClass=syClass('syupload',array($this->my['group']['filetype'],$this->my['group']['filesize']*1024));
			$w=' (hand='.$this->syArgs('hand').' and uid='.$this->my['id'].' and fields="'.$this->syArgs('inputid',1).'") or (hand!='.$this->syArgs('hand').' and hand!=0 and uid='.$this->my['id'].') ';
			if($aid&&$molds)$w.=' or (aid='.$aid.' and molds="'.$molds.'") ';
		}else{
			//游客
			$ip=GetIP();
			$group=syDB('member_group')->find(array('sys'=>1));
			if($group['filesize']<=0||$group['fileallsize']<=0){echo $group['name'].'不能上传文件';exit;}
			$ufm=syDB('member_file')->findSql('SELECT sum(size) FROM '.$this->db.'_file where ip="'.$ip.'"');
			if($ufm[0]['sum(size)']>$group['fileallsize']*1024){echo '您的上传空间已满';exit;}
			$fileClass=syClass('syupload',array($group['filetype'],$group['filesize']*1024));
			$w=' (hand='.$this->syArgs('hand').' and ip="'.$ip.'" and fields="'.$this->syArgs('inputid',1).'") or (hand!='.$this->syArgs('hand').' and hand!=0 and ip="'.$ip.'") ';
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
					'fields' => $this->syArgs('inputid',1),
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
		$this->hand=$this->syArgs('hand');
		$this->molds=$this->syArgs('molds');
		$this->aid=$this->syArgs('aid');
		$this->tid=$this->syArgs('tid');
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
		$this->display('include/uploads.php');
	}
	private function fields_args($cmark,$nid=0,$lists=0){
		$fa=array();
		$fieldswhere=" `statu`=1 and `issubmit`=1 and `cmark`='".$cmark."'";
		if($lists){$fieldswhere.=" and `lists`=1 ";}
		$v=syDB('fields')->findAll($fieldswhere,' `order` DESC,fid ');
		foreach($v as $f){
			$ns='';$n=array();
			if($f['ftype']=='varchar' || $f['ftype']=='file' || $f['ftype']=='multifile' || $f['ftype']=='radio' || $f['ftype']=='text'){$ns=$this->syArgs($f['fmark'],1);}
			if($f['ftype']=='int'){$ns=$this->syArgs($f['fmark']);}
			if($f['ftype']=='money'){$ns=$this->syArgs($f['fmark'],3);}
			if($f['ftype']=='date'){$ns=strtotime($this->syArgs($f['fmark'],1));}
			if($f['ftype']=='select'){if($this->syArgs($f['fmark'],2)){$ns='|'.implode('|',$this->syArgs($f['fmark'],2)).'|';}else{$ns='';}}
			$n=array($f['fmark'] => $ns);
			$fa=array_merge($fa,$n);
		}
		return $fa;
	}
}	