<?php
define('IN_AOS', true);
require('source/library/order.php');
include_once('source/library/payment.php');
$out_trade_no = intval($_GET['out_trade_no']);

//根据支付id获取订单id
$pay_log_info = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['aos']->table('pay_log')." WHERE log_id = '$out_trade_no'");

$order_id = $pay_log_info['order_id'];

if(empty($pay_log_info['order_type']))
{
	//获取订单信息
	$order = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['aos']->table('order_info') . " WHERE order_id = '$order_id' limit 1");
	//获取商品名称
	$order['goods_name'] = $GLOBALS['db']->getOne("SELECT goods_name FROM " . $GLOBALS['aos']->table('order_goods') . " WHERE order_id = '$order_id'");
	//获取用户openid兼容小程序
	$order['openid'] = getOpenid($order['user_id']);

	if(!empty($order['extension_code']))
	{
		
		$return_url = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/index.php?c=share&tuan_id='.$order['extension_id'];
		
	}
	else
	{
		$return_url = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/index.php?c=user&a=order_detail&order_id='.$order_id;
	}

}
else
{
	//获取充值信息
	$order = $GLOBALS['db']->getRow("SELECT payment_id,amount FROM " . $GLOBALS['aos']->table('user_account') . " WHERE id = '$order_id' limit 1");

	//获取用户openid兼容小程序
	$order['openid'] = getOpenid($order['user_id']);

	$order['order_sn'] = 'points';
	$order['goods_name'] = '账户充值';
	$order['order_amount'] = $order['amount'];
	$order['pay_id'] = $order['payment_id'];
	$return_url = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/index.php?c=user&a=account_log';
}
if($order)
{
	if ($order['order_amount'] > 0){
		$order['out_trade_no'] = $order['order_sn'].'-'.$out_trade_no; 
		$payment = payment_info($order['pay_id']);
		include_once('source/library/wxpay/wxpay.class.php');
		$pay_obj    = new wxpay;
		$notify_url = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/api/wxpay.php';
		$code = $pay_obj->get_code($order, unserialize_config($payment['pay_config']),$notify_url);
	}
	else
	{
		show_message('此订单已支付！'); 
	}
}
else
{
	echo 1;exit; 
}
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <title>微信安全支付</title>
	<script type="text/javascript">
		function jsApiCall()
		{
			WeixinJSBridge.invoke(
				'getBrandWCPayRequest',
				<?php echo $code;?>,
				function(res){
					//WeixinJSBridge.log(res.err_msg);
					if(res.err_msg == "get_brand_wcpay_request:ok" ) {
						window.location.href = "<?php echo $return_url;?>";
					} else {
						//alert("交易取消");
						window.location.href = "./index.php";
					}
				}
			);
		}
		//function callpay()
		window.onload = function ()

		{
			if (typeof WeixinJSBridge == "undefined"){
			    if( document.addEventListener ){
			        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			    }else if (document.attachEvent){
			        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
			        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			    }
			}else{
			    jsApiCall();
			}
		}
	</script>
</head>
<body>
</body>
</html>