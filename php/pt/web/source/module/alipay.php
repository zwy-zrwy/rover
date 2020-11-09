<?php
define('IN_AOS', true);
$is_wap = true;
require('source/library/order.php');
include_once('source/library/payment.php');
$out_trade_no = intval($_GET['out_trade_no']);
//根据支付id获取订单id
$pay_log_info = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['aos']->table('pay_log')." WHERE log_id = $out_trade_no");
$order_id = $pay_log_info['order_id'];
$is_paid = $GLOBALS['db']->getOne("SELECT is_paid FROM ".$GLOBALS['aos']->table('pay_log')." WHERE log_id = '$out_trade_no'");

if(empty($pay_log_info['order_type']))
{
	//获取订单信息
    $order = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['aos']->table('order_info') . " WHERE order_id = '$order_id' limit 1");

    //获取商品名称
    $order['goods_name'] = $GLOBALS['db']->getOne("SELECT goods_name FROM " . $GLOBALS['aos']->table('order_goods') . " WHERE order_id = '$order_id'");
}else{
	//获取充值信息
    $order = $GLOBALS['db']->getRow("SELECT payment_id,amount FROM " . $GLOBALS['aos']->table('user_account') . " WHERE id = '$order_id' limit 1");
    $order['order_sn'] = 'points';
    $order['goods_name'] = '账户充值';
    $order['order_amount'] = $order['amount'];
    $order['pay_id'] = $order['payment_id'];
}


if(strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger'))
{
	if(empty($pay_log_info['order_type']))
    {
        if($is_paid)
		{
			if(!empty($order['extension_code']))
	        {
				
				$back_act = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/index.php?c=share&tuan_id='.$order['extension_id'];
				
	        }
	        else
	        {
	            $back_act = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/index.php?c=user&a=order_detail&order_id='.$order_id;
	        }
	        aos_header("Location: ".$back_act."\n");
	        exit;
		}
    }
    else
    {
    	if($is_paid)
		{
			$back_act = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/index.php?c=user&a=account_log';
			aos_header("Location: ".$back_act."\n");
	        exit;
		}

    }
	$smarty->display('alipay.htm');
	exit;
}
if($order)
{
	if ($order['order_amount'] > 0){
		//防止商户订单号重复
		$order['out_trade_no'] = $order['order_sn'].'-'.$out_trade_no; 
		$payment = payment_info($order['pay_id']);
		include_once('source/library/' . $payment['pay_code'] . '/' . $payment['pay_code'] . '.class.php');
		$notify_url = AOS_HTTP . $_SERVER['HTTP_HOST'].$_CFG['directory']."/api/alipay.php";
		$call_back_url = AOS_HTTP.$_SERVER['HTTP_HOST'].$_CFG['directory']."/api/respond.php";
		$pay_obj    = new $payment['pay_code'];
		$code = $pay_obj->get_code($order, unserialize_config($payment['pay_config']),$notify_url,$call_back_url);
		//echo $code;
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