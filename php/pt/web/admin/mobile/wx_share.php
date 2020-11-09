<?php

define('IN_AOS', true);


admin_priv('article_manage');
/*------------------------------------------------------ */
//-- 文章列表
/*------------------------------------------------------ */
if ($operation == 'share_list')
{
    /* 取得过滤条件 */
    $filter = array();
    $smarty->assign('full_page',    1);
    $smarty->assign('filter',       $filter);

    $share_list = get_share_list();

    $smarty->assign('share_list',    $share_list['arr']);
    
    $pager = get_page($share_list['filter']);

    $smarty->assign('pager',   $pager);

    

    
    $smarty->display('share_list.htm');
}


elseif ($operation == 'remove')
{
    check_authz_json('article_manage');

    $id = intval($_REQUEST['id']);
    $sql="delete from ".$aos->table('share_info')." where id = '$id'";
    $db->query($sql);

    make_json_result($id);
}

/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */

elseif ($operation == 'batch')
{
    /* 批量删除 */
    
        if ($_POST['type'] == 'button_remove')
        {
            admin_priv('article_manage');

            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                $lnk[] = array('text' => "返回列表", 'href' => 'index.php?act=wx_share&op=share_list');

                sys_msg("没有选择信息", 0,$lnk);
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
                $sql="delete from ".$aos->table('share_info')." where id = '$id'";
                $db->query($sql);
            }

        }

    /* 清除缓存 */
    clear_cache_files();
    $lnk[] = array('text' => "返回列表", 'href' => 'index.php?act=wx_share&op=share_list');
    sys_msg("批量操作成功", 0, $lnk);
}



/* 获得文章列表 */
function get_share_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter = array();
        $filter['keyword']    = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 's.id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = '';
        

        /* 文章总数 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['aos']->table('share_info'). ' AS s '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('users'). ' AS u ON u.user_id = s.user_id '.
               'WHERE 1 ' .$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取文章数据 */
        $sql = 'SELECT s.* , u.nickname '.
               'FROM ' .$GLOBALS['aos']->table('share_info'). ' AS s '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('users'). ' AS u ON s.user_id = u.user_id '.
               'WHERE 1 ' .$where. ' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

        $filter['keyword'] = stripslashes($filter['keyword']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $rows['date'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);

        $arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>