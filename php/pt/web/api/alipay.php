<?php
/*支付宝回调*/
define('IN_AOS', true);
$is_wap = true;
require('../source/aoshop.php');
require('../source/library/order.php');
require('../source/library/payment.php');
$payment = get_payment("alipay");
include_once('../source/library/alipay/alipay.class.php');
$pay_obj    = new alipay;
$code = $pay_obj->respond(unserialize_config($payment['pay_config']));
?>