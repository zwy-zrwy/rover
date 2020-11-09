<?php
class alipay {
	function __construct() {
		$this->alipay ();
	}
	function alipay() {
	}
	/*生成支付代码*/
	function get_code($order, $payment,$notify_url,$call_back_url) {
		$alipay_config = array(
			"partner" => $payment['alipay_partner'],
			"key" => $payment['alipay_key'],
			"account" => $payment['alipay_account'],
			"sign_type" => 'MD5',
			"input_charset" => 'utf-8',
			"transport" => 'http' 
		);
		include_once ("alipay_submit.class.php");
		//返回格式
		$format = "xml"; //必填，不需要修改
		//返回格式
		$v = "2.0"; //必填，不需要修改
		//请求号
		$req_id = date('Ymdhis');//必填，须保证每次请求都是唯一
		//**req_data详细信息**
		//服务器异步通知页面路径,不允许加?id=123这类自定义参数
		//$notify_url = "https://".$_SERVER['HTTP_HOST'].$_CFG['directory']."/api/alipay.php";
		//页面跳转同步通知页面路径,不允许加?id=123这类自定义参数
		//$call_back_url = "https://".$_SERVER['HTTP_HOST'].$_CFG['directory']."/api/respond.php";
		//卖家支付宝帐户
		$seller_email = $alipay_config['account'];
		//商户网站订单系统中唯一订单号
		$out_trade_no = $order['out_trade_no'];
		//订单名称
		$subject = $order['goods_name'];
		//付款金额
		$total_fee = $order['order_amount'];
		//请求业务参数详细
		$req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee></direct_trade_create_req>';
		//构造要请求的参数数组，无需改动
		$para_token = array(
			"service" => "alipay.wap.trade.create.direct",
			"partner" => trim($alipay_config['partner']),
			"sec_id" => trim($alipay_config['sign_type']),
			"format"	=> $format,
			"v"	=> $v,
			"req_id"	=> $req_id,
			"req_data"	=> $req_data,
			"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestHttp($para_token);
		//URLDECODE返回的信息
		$html_text = urldecode($html_text);
		//解析远程模拟提交后返回的信息
		$para_html_text = $alipaySubmit->parseResponse($html_text);
		//获取request_token
		$request_token = $para_html_text['request_token'];
		//业务详细
		$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
		//构造要请求的参数数组，无需改动
		$parameter = array(
			"service" => "alipay.wap.auth.authAndExecute",
			"partner" => trim($alipay_config['partner']),
			"v"	=> $v,
			"sec_id" => trim($alipay_config['sign_type']),
			"format"	=> $format,
			"req_id"	=> $req_id,
			"req_data"	=> $req_data,
			"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');
		echo $html_text;
	}
	
	/*响应操作*/
	function respond($payment) {
		$alipay_config = array(
			"partner" => $payment['alipay_partner'],
			"key" => $payment['alipay_key'],
			"sign_type" => 'MD5',
			"input_charset" => 'utf-8',
			"transport" => 'http' 
		);
		include_once ("alipay_notify.class.php");
		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {
			if(!empty($_POST['notify_data'])){
				$notify_data = $_POST['notify_data'];
				$doc = new DOMDocument();
				$doc->loadXML($notify_data);
				if(!empty($doc->getElementsByTagName("notify")->item(0)->nodeValue))
				{
					//商户订单日志号
					$out_trade_no = $doc->getElementsByTagName("out_trade_no")->item(0)->nodeValue;
					//支付宝交易号
					$trade_no = $doc->getElementsByTagName("trade_no")->item(0)->nodeValue;
					//交易状态
					$trade_status = $doc->getElementsByTagName("trade_status")->item(0)->nodeValue;
					//交易金额
					$total_fee = $doc->getElementsByTagName("total_fee")->item(0)->nodeValue;
					$pay_time=time();
					$order_sns = explode('-',$out_trade_no);
				    $order_sn = $order_sns[1];
					order_paid($order_sn,2);
					echo "success";
				}
			}
			else
			{
				echo "fail";
			}
		}
		else
		{
			echo "fail";
		}
	}
}
?>