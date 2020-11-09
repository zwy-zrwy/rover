<?php
/*微信支付回调*/
define('IN_AOS', true);
$is_wap = true;

require('../source/aoshop.php');

require(ROOT_PATH . 'source/library/order.php');

require(ROOT_PATH . 'source/library/payment.php');

$payment = get_payment('wxpay');

include_once(ROOT_PATH . 'source/library/wxpay/wxpay.class.php');
$pay_obj    = new wxpay;

$code = $pay_obj->respond(unserialize_config($payment['pay_config']));
?>