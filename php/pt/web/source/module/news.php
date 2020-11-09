<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
assign_template();

$article_id     = $_REQUEST['id'];
$news_info = get_news_info($article_id);

$smarty->assign('news_info',  $news_info);
$smarty->assign('id',  $article_id);
$share['title'] = $news_info['title'];
$share['desc'] = $news_info['description'];
$share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
$share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$smarty->assign('share',       $share);
$smarty->display('news_info.htm');


function get_news_info($id)
{
    $sql = 'SELECT title, content, add_time FROM ' . $GLOBALS['aos']->table('article') . " WHERE is_open = 1 AND article_id = '$id'";
    $row = $GLOBALS['db']->getRow($sql);

    $row['add_time']      = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);

    return $row;
}
?>