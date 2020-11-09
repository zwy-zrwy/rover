<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
assign_template();
$smarty->assign('close_comment',  $_CFG['close_comment']);
$smarty->display('closed.htm');
?>