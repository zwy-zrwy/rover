<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
assign_template();
	
if ($action == 'list')
{
	$faq_list = get_faq_list();
    $smarty->assign('faq_list',  $faq_list);
    $share['title'] = '常见问题';
    $share['desc'] = '常见问题，您有什么问题可以在这里找到答案';
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
    $smarty->display('faq_list.htm');
}
elseif ($action == 'view')
{
    $article_id     = $_REQUEST['id'];
	$faq_info = get_faq_info($article_id);
    $smarty->assign('faq_info',  $faq_info);
    $smarty->assign('id',  $article_id);
    $share['title'] = $news_info['title'];
    $share['desc'] = $news_info['description'];
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
    $smarty->display('faq_info.htm');
}

function get_faq_list()
{
    $sql = 'SELECT article_id, title, link FROM ' . $GLOBALS['aos']->table('article')." WHERE cat_id = 1";
    $result = $GLOBALS['db']->getAll($sql);
    foreach ($result AS $idx => $row)
    {
        $arr[$idx]['title']  = $row['title'];
        if($row['link'] == 'http://' || $row['link'] == 'https://')
        {
            $arr[$idx]['url']    = 'index.php?c=faq&a=view&id='.$row['article_id'];
        }
        else
        {
            $arr[$idx]['url']    = $row['link'];
        }
        
    }
    return $arr;
}
function get_faq_info($id)
{
    $sql = 'SELECT title, description, content FROM ' . $GLOBALS['aos']->table('article') . " WHERE is_open = 1 AND article_id = '$id' ORDER BY article_id";
    $result = $GLOBALS['db']->getRow($sql);
    return $result;
}
?>