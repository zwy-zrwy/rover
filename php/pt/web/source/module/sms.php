<?php

define('IN_AOS', true);

$code = rand(100000,999999);
if ($action == 'send_code')
{
	$mobile = trim($_REQUEST['mobile']);
    $template = $GLOBALS['_CFG']['sms_code'];
	$send = sendsms($mobile, $code, $template);
	$_SESSION['validate_code'] = $code;
    $_SESSION['validate_mobile'] = $mobile;
	echo json_encode($send,true);die;
}
elseif ($action == 'get_code')
{
    $result = array('success' => '');
    if ($_REQUEST['code'] !== $_SESSION['validate_code'])
	{
        $result['success'] = false;
        $result['message'] = '验证码不正确';
    } else {
        $result['success'] = true;
    }
    echo json_encode($result);
    die;
}

?>