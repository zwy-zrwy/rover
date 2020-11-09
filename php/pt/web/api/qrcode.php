<?php

define('IN_AOS', true);
require_once('init.php');
require_once(ROOT_PATH.'source/class/qrcode.class.php');
$url = substr($aos->url(), 0, -4);
$act  = isset($_REQUEST['c']) ? trim($_REQUEST['c']) : 'index';
$logo = $url.'uploads/images/'.$_CFG['wap_logo'];
if ($act == 'goods')
{
	$goods_id= isset($_GET['id']) ? intval($_GET['id']) : 0;
	$data = $url.'index.php?c=goods&id='.$goods_id;
}
elseif ($act == 'share') //团
{
    $extension_id= isset($_GET['extension_id']) ? trim($_GET['extension_id']) : 0;
    $data = $url.'index.php?c=share&tuan_id='.$extension_id;
}
elseif ($act == 'verification') //核销
{
    $order_id= isset($_GET['id']) ? intval($_GET['id']) : 0;
    $data = $url.'index.php?c=verification&id='.$order_id;
}
elseif ($act == 'wxmanage') //门店微信管理员
{
    $store_id= isset($_GET['id']) ? intval($_GET['id']) : 0;
    $data = $url.'index.php?c=wxmanage&a=binding';
    if($store_id){
        $data .= '&id='.$store_id;
    }
    
}
$errorCorrectionLevel = 'L';//容错级别  
$matrixPointSize = 4;//生成图片大小  
//生成二维码图片
QRcode::png($data, 'qrcode.png', $errorCorrectionLevel, $matrixPointSize, 2);
$QR = 'qrcode.png';//已经生成的原始二维码图
if ($logo !== FALSE) {  
    $QR = imagecreatefromstring(file_get_contents($QR));  
    $logo = imagecreatefromstring(file_get_contents($logo));  
    $QR_width = imagesx($QR);//二维码图片宽度  
    $QR_height = imagesy($QR);//二维码图片高度  
    $logo_width = imagesx($logo);//logo图片宽度  
    $logo_height = imagesy($logo);//logo图片高度  
    $logo_qr_width = $QR_width / 5;  
    $scale = $logo_width/$logo_qr_width;  
    $logo_qr_height = $logo_height/$scale;  
    $from_width = ($QR_width - $logo_qr_width) / 2;  
    //重新组合图片并调整大小  
    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,   
    $logo_qr_height, $logo_width, $logo_height);  
}
header('Content-type: image/png');
imagepng($QR);
imagedestroy($QR);
exit;
?>