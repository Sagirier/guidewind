<?php
if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');}
class sendsms extends syController {
    public $needstatus = 'false';
    public $product = '';
    public $extno = '';
    function __construct(){
        parent::__construct();
    }
	/**
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $product 产品id，可选
	 * @param string $extno   扩展码，可选
	 */
    function index(){
        
    }
	function sendSMS() {
	    $mobile=$this->syArgs('mobilenum',1);
        $codenum=rand(100024,965320);
        $_SESSION['wp_mobile_verify']=time().'/'.md5($codenum);
	    $msg="您好，您的手机验证码是：".$codenum."，请在5分钟内按页面提示提交验证。";
		//创蓝接口参数
		$postArr = array (
              'account' => $GLOBALS['WP']['sms']['api_account'],
              'pswd' => $GLOBALS['WP']['sms']['api_password'],
              'msg' => $msg,
              'mobile' => $mobile,
              'needstatus' => $this->needstatus,
              'product' => $this->product,
              'extno' => $this->extno,
        );
		
		$result = $this->curlPost( $GLOBALS['WP']['sms']['api_send_url'] , $postArr);
		if(false==$result){
		    $ret['code']=534;
		    $ret['status']='error';
		    $ret['msg']='短信发送失败';
		}
		$ret['code']=1;
		$ret['status']='success';
		$ret['msg']='短信发送成功！';
		echo json_encode($ret);
		exit();
		
	}
	
	/**
	 * 查询额度
	 *
	 *  查询地址
	 */
	function queryBalance() {
		//查询参数
		$postArr = array ( 
		          'account' => $GLOBALS['WP']['sms']['api_account'],
		          'pswd' => $GLOBALS['WP']['sms']['api_password'],
		);
		$result = $this->curlPost($GLOBALS['WP']['sms']['api_balance_query_url'], $postArr);
		return $result;
	}

	/**
	 * 处理返回值
	 * 
	 */
	function execResult($result){
		$result=preg_split("/[,\r\n]/",$result);
		return $result;
	}

	/**
	 * 通过CURL发送HTTP请求
	 * @param string $url  //请求URL
	 * @param array $postFields //请求参数 
	 * @return mixed
	 */
	private function curlPost($url,$postFields){
		$postFields = http_build_query($postFields);
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		return $result;
	}
	
	//魔术获取
	function __get($name){
		return $this->$name;
	}
	
	//魔术设置
	function __set($name,$value){
		$this->$name=$value;
	}
}
