<?php
class wechat
{
  private $appid;
  private $appsecret;
  private $access_token;
	private $jsapi_ticket;
	private $token_file ;
	private $ticket_file;
  private $_receive;
  public $errCode = 40001;
	public $errMsg = "no access";
	private $_charset = 'utf-8';
	
	public function __construct($options)
	{
		$this->token = $options['token'];
		$this->appid = $options['appid'];
		$this->appsecret = $options['appsecret'];
		$this->token_file = RLPT_PATH . '/data/wechat/token.php';
		$this->ticket_file = RLPT_PATH . '/data/wechat/ticket.php';
	}

	//For weixin server validation 
	private function checkSignature()
	{
    $signature = isset($_GET["signature"])?$_GET["signature"]:'';
    $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
    $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';		
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if($tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	//For weixin server validation 
	public function valid($return=false)
    {
    	
        $echoStr = isset($_GET["echostr"]) ? $_GET["echostr"]: '';
        
        if ($return) {
        		if ($echoStr) {
        			if ($this->checkSignature()){ 
        				echo $echoStr;exit;
        			}else{
        				return false;
        			}
        		} else 
        			return $this->checkSignature();
        } else {
	        	if ($echoStr) {
	        		if ($this->checkSignature()){
	        			
	        			echo $echoStr; exit; 
	        			
	        		}else{ 
	        			die('no access');
	        		}
	        	}  else {
	        		if ($this->checkSignature())
	        			return true;
	        		else
	        			die('no access');
	        	}
        }
        return false;
    }

    /**
	 * 设置发送消息
	 * @param array $msg 消息数组
	 * @param bool $append 是否在原消息数组追加
	 */
    public function Message($msg = '',$append = false){
    		if (is_null($msg)) {
    			$this->_msg =array();
    		}elseif (is_array($msg)) {
    			if ($append)
    				$this->_msg = array_merge($this->_msg,$msg);
    			else
    				$this->_msg = $msg;
    			//return $this->_msg;
    		} else {
    			//return $this->_msg;
    		}
			return $this->iconvUtf($this->_msg);
    }

    /**
     * 获取微信服务器发来的信息
     */
	public function getRev()
	{
		if ($this->_receive) return $this;
		$postStr = file_get_contents("php://input");
		//$this->log($postStr);
		if (!empty($postStr)) {
			$this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$this->_receive = $this->iconvUtf($this->_receive);
		}
		return $this;
	}
	
	/**
	 * 获取微信服务器发来的信息
	 */
	public function getRevData()
	{
		return $this->iconvUtf($this->_receive);
	}
		
	/**
	 * 获取消息发送者
	 */
	public function getRevFrom() {
		if (isset($this->_receive['FromUserName']))
			return $this->_receive['FromUserName'];
		else 
			return false;
	}
	
	/**
	 * 获取消息接受者
	 */
	public function getRevTo() {
		if (isset($this->_receive['ToUserName']))
			return $this->_receive['ToUserName'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息的类型
	 */
	public function getRevType() {
		if (isset($this->_receive['MsgType']))
			return $this->_receive['MsgType'];
		else 
			return false;
	}
	
	/**
	 * 获取消息ID
	 */
	public function getRevID() {
		if (isset($this->_receive['MsgId']))
			return $this->_receive['MsgId'];
		else 
			return false;
	}
	
	/**
	 * 获取消息发送时间
	 */
	public function getRevCtime() {
		if (isset($this->_receive['CreateTime']))
			return $this->_receive['CreateTime'];
		else 
			return false;
	}

	/**
	 * 获取接收消息内容正文
	 */
	public function getRevContent(){
		if (isset($this->_receive['Content']))
			return $this->_receive['Content'];
		else if (isset($this->_receive['Recognition'])) //获取语音识别文字内容，需申请开通
			return $this->_receive['Recognition'];
		else
			return false;
	}

	/**
	 * 获取接收事件推送
	 */
	public function getRevEvent(){
		if (isset($this->_receive['Event'])){
			return array(
				'event'=>$this->_receive['Event'],
				'key'=>$this->_receive['EventKey'],
				'Latitude'=>$this->_receive['Latitude'],
				'Longitude'=>$this->_receive['Longitude'],
				'Precision'=>$this->_receive['Precision'],
			);
		} else 
			return false;
	}

	public static function xmlSafeStr($str)
	{   
		return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';   
	}

	/**
	 * 数据XML编码
	 * @param mixed $data 数据
	 * @return string
	 */
	public static function data_to_xml($data) {
	    $xml = '';
	    foreach ($data as $key => $val) {
	        is_numeric($key) && $key = "item id=\"$key\"";
	        $xml    .=  "<$key>";
	        $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
	        list($key, ) = explode(' ', $key);
	        $xml    .=  "</$key>";
	    }
	    return $xml;
	}

	/**
	 * XML编码
	 * @param mixed $data 数据
	 * @param string $root 根节点名
	 * @param string $item 数字索引的子节点名
	 * @param string $attr 根节点属性
	 * @param string $id   数字索引子节点key转换的属性名
	 * @param string $encoding 数据编码
	 * @return string
	*/
	public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') {
	    if(is_array($attr)){
	        $_attr = array();
	        foreach ($attr as $key => $value) {
	            $_attr[] = "{$key}=\"{$value}\"";
	        }
	        $attr = implode(' ', $_attr);
	    }
	    $attr   = trim($attr);
	    $attr   = empty($attr) ? '' : " {$attr}";
	    $xml   = "<{$root}{$attr}>";
	    $xml   .= self::data_to_xml($data, $item, $id);
	    $xml   .= "</{$root}>";
	    return $xml;
	}


    //设置回复文本消息
	public function text($text='')
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'MsgType'=>'text',
			'Content'=>$text,
			'CreateTime'=>gmtime(),
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}

	/**
	 * 设置回复图文
	 * @param array $newsData 
	 * 数组结构:
	 *  array(
	 *  	[0]=>array(
	 *  		'Title'=>'msg title',
	 *  		'Description'=>'summary text',
	 *  		'PicUrl'=>'http://www.domain.com/1.jpg',
	 *  		'Url'=>'http://www.domain.com/1.html'
	 *  	),
	 *  	[1]=>....
	 *  )
	 */
	public function news($newsData=array())
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$count = count($newsData);
		
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'MsgType'=>'news',
			'CreateTime'=>gmtime(),
			'ArticleCount'=>$count,
			'Articles'=>$newsData,
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}

	/**
	 * 
	 * 回复微信服务器, 此函数支持链式操作
	 * @example $this->text('msg tips')->reply();
	 * @param string $msg 要发送的信息, 默认取$this->_msg
	 * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
	 */
	public function reply($msg=array(),$return = false)
	{
		if (empty($msg)) 
			$msg = $this->_msg;
		$xmldata=  $this->xml_encode($msg);
		//$this->log($xmldata);
		if ($return)
			return $xmldata;
		else
			echo $xmldata;
	}

	//获取基础支持access_token
	public function accessToken()
	{
		if(is_file($this->token_file)){
			$token_str = file_get_contents($this->token_file);
	    $token_str = strstr($token_str,'{');
			$json = json_decode($token_str, true);
			$file_time = filemtime($this->token_file);
			$now_time = gmtime();
			if(($now_time - $file_time) > 5000)
			{
				return $this->getAccessToken();
			}
			else
			{
				$this->access_token = $json['access_token'];
				$_SESSION['access_token'] = $this->access_token;
			  return $this->access_token;
			}
	  }
	  else
	  {
	  	return $this->getAccessToken();
	  }
	}

  //从微信获取基础支持access_token并缓存
	public function getAccessToken()
	{
  	$result = $this->http_get('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret);
		if($result)
		{
			$token_str = "<?php exit('What do you want?');?>".$result;
			file_put_contents($this->token_file, $token_str);
			@unlink($this->ticket_file);
			$json = json_decode($result,true);
			if (!$json || isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->access_token = $json['access_token'];
			$_SESSION['access_token'] = $this->access_token;
			return $this->access_token;
		}
		return false;
	}


	//获取关注者详细信息
	public function getUserInfo($openid){
		if (!$this->access_token) return false;
		$result = $this->http_get('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN');
		if ($result)
		{
			$json = json_decode($result,true);
			if (isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$json['access_token'] = $this->access_token;
			return $json;
		}
		return false;
	}

	//获取基础支持jsapi_ticket
	public function jsapiTicket()
	{
		$this->accessToken();
		if(is_file($this->ticket_file)){
			$ticket_str = file_get_contents($this->ticket_file);
	    $ticket_str = strstr($ticket_str,'{');
			$json = json_decode($ticket_str, true);

			$file_time = filemtime($this->ticket_file);
			$now_time = gmtime();
			if(($now_time - $file_time) > 5000){
				return $this->getJsapiTicket();
			}else{
				$this->jsapi_ticket = $json['ticket'];
			  return $this->jsapi_ticket;
			}
	  }else{
		  	return $this->getJsapiTicket();
		}
	}

	//从微信获取基础支持jsapi_ticket并缓存
	public function getJsapiTicket()
	{
		$this->accessToken();
  	$result = $this->http_get('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$this->accessToken().'&type=jsapi');
		if($result)
		{
			$ticket_str = "<?php exit('What do you want?');?>".$result;
			file_put_contents($this->ticket_file, $ticket_str);
			$json = json_decode($result,true);
			if (!$json || isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->jsapi_ticket = $json['ticket'];
			return $this->jsapi_ticket;
		}
		return false;
	}

	public function signature($timestamp,$url){
    $this->jsapiTicket();
    $timestamp = gmtime();
    $string = "jsapi_ticket=".$this->jsapi_ticket."&noncestr=$timestamp&timestamp=$timestamp&url=$url";
    $signature = sha1($string);
    //echo $signature;
    return $signature;
	}
/*

	//获取JsApi使用签名
		public function getJsSign($url){
			$appid = $this->appid;
	    $timestamp = gmtime();
	    $noncestr = $this->createNonceStr();
	    $ret = strpos($url,'#');
	    if ($ret)
	        $url = substr($url,0,$ret);
	    $url = trim($url);
	    if (empty($url))
	        return false;
	    $arrdata = array("timestamp" => $timestamp, "noncestr" => $noncestr, "url" => $url, "jsapi_ticket" => $this->jsapi_ticket);
	    $sign = $this->getSignature($arrdata);
	    if (!$sign)
	        return false;
	    $signPackage = array(
	            "appid"     => $appid,
	            "noncestr"  => $noncestr,
	            "timestamp" => $timestamp,
	            "url"       => $url,
	            "signature" => $sign
	    );
	    return $signPackage;
	}

  //获取签名

	public function getSignature($arrdata,$method="sha1") {
		if (!function_exists($method)) return false;
		ksort($arrdata);
		$paramstring = "";
		foreach($arrdata as $key => $value)
		{
			if(strlen($paramstring) == 0)
				$paramstring .= $key . "=" . $value;
			else
				$paramstring .= "&" . $key . "=" . $value;
		}
		$Sign = $method($paramstring);
		return $Sign;
	}

  //生成随机字串
	public function createNonceStr($length=16){
		// 密码字符集，可任意添加你需要的字符
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for($i = 0; $i < $length; $i++)
		{
			$str .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		return $str;
	}

*/

	//oauth 授权跳转接口
	public function getOauthRedirect($callback,$state='',$scope='snsapi_userinfo'){
		return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
	}
	
	//通过code获取Access Token
	public function getOauthAccessToken(){
		$code = isset($_GET['code'])?$_GET['code']:'';
		if (!$code) return false;
		$result = $this->http_get('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code');
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->user_token = $json['access_token'];
			return $json;
		}
		return false;
	}
	
	//刷新access token并续期
	public function getOauthRefreshToken($refresh_token){
		$result = $this->http_get('https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->user_token = $json['access_token'];
			return $json;
		}
		return false;
	}
	
	//获取授权后的用户资料
	public function getOauthUserinfo($token,$openid){
		$result = $this->http_get('https://api.weixin.qq.com/sns/userinfo?access_token='.$token.'&openid='.$openid.'&lang=zh_CN');
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}

	/**
	 * 获取接收消息图片
	 */
	public function getRevPic(){
		if (isset($this->_receive['PicUrl']))
			return $this->_receive['PicUrl'];
		else 
			return false;
	}

	/**
	 * 获取接收地理位置
	 */
	public function getRevGeo(){
		if (isset($this->_receive['Location_X'])){
			return array(
				'x'=>$this->_receive['Location_X'],
				'y'=>$this->_receive['Location_Y'],
				'scale'=>$this->_receive['Scale'],
				'label'=>$this->_receive['Label']
			);
		} else 
			return false;
	}

	//GET 请求
	function http_get($url){
		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);

		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}
	
	//POST 请求
	function http_post($url,$param){
		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		if (is_string($param)) {
			$strPOST = $param;
		} else {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}

	/**
	 * 微信api不支持中文转义的json结构
	 * @param array $arr
	 */
	static function json_encode($arr) {
		$parts = array ();
		$is_list = false;
		//Find out if the given array is a numerical array
		$keys = array_keys ( $arr );
		$max_length = count ( $arr ) - 1;
		if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
			$is_list = true;
			for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
				if ($i != $keys [$i]) { //A key fails at position check.
					$is_list = false; //It is an associative array.
					break;
				}
			}
		}
		foreach ( $arr as $key => $value ) {
			if (is_array ( $value )) { //Custom handling for arrays
				if ($is_list)
					$parts [] = self::json_encode ( $value ); /* :RECURSION: */
				else
					$parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
			} else {
				$str = '';
				if (! $is_list)
					$str = '"' . $key . '":';
				//Custom handling for multiple data types
				if (is_numeric ( $value ) && $value<2000000000)
					$str .= $value; //Numbers
				elseif ($value === false)
				$str .= 'false'; //The booleans
				elseif ($value === true)
				$str .= 'true';
				else
					$str .= '"' . addslashes ( $value ) . '"'; //All other things
				// :TODO: Is there any more datatype we should be in the lookout for? (Object?)
				$parts [] = $str;
			}
		}
		$json = implode ( ',', $parts );
		if ($is_list)
			return '[' . $json . ']'; //Return numerical JSON
		return '{' . $json . '}'; //Return associative JSON
	}

	/**
	 * 发送消息
	 * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
	 * @return boolean|array
	 */
	public function sendWxMsg($openid,$title,$desc,$url,$pic=false){

		if (!$this->access_token && !$this->accessToken()) return false;
		$data = '{"touser":"'.$openid.'","msgtype":"news","news":{"articles":[{"title":"'.$title.'","description":"'.$desc.'","url":"'.$url.'","picurl":"'.$pic.'"}]}}';
		$result = $this->http_post('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->access_token,$data);
		if ($result)
		{ 
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
	public function sendMsg($data){

		if (!$this->access_token && !$this->accessToken()) return false;

		
		$result = $this->http_post('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->access_token,self::json_encode($data));
		if ($result)
		{ 
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}

	/**
	 * 进入客服模式
	 * Examle: $obj->kefu()->reply();
	 * @param string $text
	 */
	public function kefu()
	{
		$msg = array(
				'ToUserName' => $this->getRevFrom(),
				'FromUserName'=>$this->getRevTo(),
				'MsgType'=>'transfer_customer_service',
				'CreateTime'=>time()
		);
		$this->Message($msg);
		return $this;
	}

	//编码转换
	function iconvUtf($content){
		if($this->_charset != 'utf-8'){
			$content = serialize($content);
			$content = iconv($this->_charset,'UTF-8',$content);
			$content = unserialize($content);
		}
		return $content;
	}

	//发送模板消息
	public function sendtemplate($template){
		
		if (!$this->access_token && !$this->accessToken()) return false;
		$result = $this->http_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->access_token,self::json_encode($template));
		return $result;
	}

	/**
	 * 创建菜单
	 * @param array $data 菜单数组数据
	 */
	public function createMenu($data){
		if (!$this->access_token && !$this->accessToken()) return false;
		$result = $this->http_post('https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->access_token,self::json_encode($data));
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * 获取菜单
	 * @return array('menu'=>array(....s))
	 */
	public function getMenu(){
		if (!$this->access_token && !$this->accessToken()) return false;
		$result = $this->http_get('https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$this->access_token);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
	
	/**
	 * 删除菜单
	 * @return boolean
	 */
	public function deleteMenu(){
		if (!$this->access_token && !$this->accessToken()) return false;
		$result = $this->http_get('https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$this->access_token);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * 创建二维码ticket
	 * @param int $scene_id 自定义追踪id
	 * @param int $type 0:临时二维码；1:永久二维码(此时expire参数无效)
	 * @param int $expire 临时二维码有效期，最大为1800秒
	 * @return array('ticket'=>'qrcode字串','expire_seconds'=>1800)
	 */
	public function getQRCode($scene_id,$type=0,$expire=1800){
		if (!$this->access_token && !$this->accessToken()) return false;
		$data = array(
			'action_name'=>$type?"QR_LIMIT_SCENE":"QR_SCENE",
			'expire_seconds'=>$expire,
			'action_info'=>array('scene'=>array('scene_id'=>$scene_id))
		);
		if ($type == 1) {
			unset($data['expire_seconds']);
		}
		$result = $this->http_post('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->access_token,self::json_encode($data));
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
	
	/**
	 * 获取二维码图片
	 * @param string $ticket 传入由getQRCode方法生成的ticket参数
	 * @return string url 返回http地址
	 */
	public function getQRUrl($ticket) {
		return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
	}

	//获取助力二维码
	public function getAssistCode($scene_id,$expire)
	{
		if (!$this->access_token && !$this->accessToken()) return false;
		$data = array(
			'action_name'=>"QR_STR_SCENE",
			'expire_seconds'=>$expire,
			'action_info'=>array('scene'=>array('scene_str'=>$scene_id))
		);
		$result = $this->http_post('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->access_token,self::json_encode($data));
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$code_url = $this->getQRUrl($json['ticket']);
			$url = ROOT_PATH . 'uploads/assist_code/'.$scene_id.'.jpg';
            $img = $this->http_get($code_url);
            $res = file_put_contents($url,$img);
			return 'uploads/assist_code/'.$scene_id.'.jpg';
		}
		return false;
	}
}

?>