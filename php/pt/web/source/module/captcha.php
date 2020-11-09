<?php

/*生成验证码*/

define('IN_AOS', true);
define('INIT_NO_SMARTY', true);

require(ROOT_PATH . 'source/class/captcha.class.php');

$img = new captcha(ROOT_PATH . 'data/captcha/', $_CFG['captcha_width'], $_CFG['captcha_height']);
@ob_end_clean(); //清除之前出现的多余输入
if (isset($_REQUEST['is_login']))
{
    $img->session_word = 'captcha_login';
}
$img->generate_image();

?>