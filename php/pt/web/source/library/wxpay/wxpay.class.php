<?php
class wxpay {
	/*构造函数*/
	function __construct() {
		$this->wxpay ();
	}
	function wxpay() {
	}
	
	/*生成支付代码*/
	function get_code($order, $payment, $notify_url) {
		define (APPID, $payment ['appId']); // appid
		define (APPSECRET, $payment ['appSecret']); // appSecret
		define (MCHID, $payment ['partnerId']);
		define (KEY, $payment ['partnerKey']); // 通加密串
		include_once ("pub.class.php");
		
		$unifiedOrder = new UnifiedOrder_pub ();
		// 设置统一支付接口参数
		$unifiedOrder->setParameter ("openid", $order['openid']);
		$unifiedOrder->setParameter ("body", mb_substr($order['goods_name'],0,30,'utf-8'));
		$unifiedOrder->setParameter ("out_trade_no", $order['out_trade_no']); // 商户订单号
		$unifiedOrder->setParameter ("total_fee", $order['order_amount'] * 100); // 总金额
		$unifiedOrder->setParameter ("notify_url", $notify_url); // 通知地址
		$unifiedOrder->setParameter ("trade_type", "JSAPI"); // 交易类型
		$prepay_id = $unifiedOrder->getPrepayId();
		$jsApi = new JsApi_pub ();
		$jsApi->setPrepayId($prepay_id);
		return $jsApi->getParameters();
	}
	
	/*响应操作*/
	function respond() {
		
		include_once ("pub.class.php");
		// 使用通用通知接口
		$notify = new Notify_pub();
		// 存储微信的回调
		//$xml = $GLOBALS ['HTTP_RAW_POST_DATA'];
		$xml = file_get_contents('php://input');
		$notify->saveData ($xml);
		$payment = get_payment ('wxpay');
		define(KEY, $payment ['partnerKey']); // 通加密串
		if ($notify->checkSign () == TRUE) {
			if ($notify->data ["return_code"] == "FAIL") {
				echo 401;
			} elseif ($notify->data ["result_code"] == "FAIL") {
				echo 402;
			} else {
				
				$out_trade_no = $notify->data['out_trade_no'];
				$order_sns = explode('-',$out_trade_no);
				$order_sn = $order_sns[1];
				if (!check_money($order_sn, $notify->data ['total_fee']/100 )) {
					return true;
				}
				
				order_paid ($order_sn, 2);
				echo 'success';exit;
			}
		}
		return true;
	}

	//退款操作
	function refund($info,$differ_price="") {
		define(APPID, $info['appId']);
		define(MCHID, $info ['partnerId']);
		define(KEY, $info ['partnerKey']);

		define('SSLCERT_PATH', ROOT_PATH ."data/cacert/apiclient_cert.pem" );
		define('SSLKEY_PATH', ROOT_PATH ."data/cacert/apiclient_key.pem" );

		include_once ("pub.class.php");
		$refund = new Refund_pub();

		$refund->setParameter("out_trade_no", $info['order_sns']);
		$refund->setParameter("out_refund_no", $info['order_sns']);
		//$refund->setParameter("transaction_id", '4007022001201703244477332450');
		
		$refund->setParameter("total_fee", $info['money_paid']*100);
		if(!empty($differ_price)){
            $refund->setParameter("refund_fee", $differ_price*100);
        }else{
            $refund->setParameter("refund_fee", $info['money_paid']*100);
        }
		$refund->setParameter("op_user_id", MCHID);
		//$refund->createXml();
		$refundResult = $refund->getResult();

		return $refundResult;
	}

	/**
     * 企业付款测试
     */
    public function rebate($info)
    {
    	define(APPID, $info['appId']);
		define(MCHID, $info ['partnerId']);
		define(KEY, $info['partnerKey']);

		define('SSLCERT_PATH', ROOT_PATH ."data/cacert/apiclient_cert.pem" );
		define('SSLKEY_PATH', ROOT_PATH ."data/cacert/apiclient_key.pem" );
        include_once ("pub.class.php");
        $mchPay = new WxMchPay();
        // 用户openid
        $mchPay->setParameter('openid', "$info[openid]");
        // 商户订单号
        $mchPay->setParameter('partner_trade_no',"$info[trade_no]");
        // 校验用户姓名选项
        $mchPay->setParameter('check_name', 'NO_CHECK');
        // 企业付款金额  单位为分
        $mchPay->setParameter('amount', $info[amout]);
        // 企业付款描述信息
        $mchPay->setParameter('desc', "$info[desc]");
        // 调用接口的机器IP地址  自定义
        $mchPay->setParameter('spbill_create_ip', '116.62.193.36'); # getClientIp()
        // 收款用户姓名
        // $mchPay->setParameter('re_user_name', 'Max wen');
        // 设备信息
        // $mchPay->setParameter('device_info', 'dev_server');

        $response = $mchPay->postXmlSSL();
        if( !empty($response) ) {
            $data = simplexml_load_string($response, null, LIBXML_NOCDATA);
            return json_encode($data);
            //echo json_encode($data);
        }else{
        	return json_encode( array('return_code' => 'FAIL', 'return_msg' => 'transfers_接口出错', 'return_ext' => array()) );
            //echo json_encode( array('return_code' => 'FAIL', 'return_msg' => 'transfers_接口出错', 'return_ext' => array()) );
        }
    }
}
?>