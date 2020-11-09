<?php

define('IN_AOS', true);
admin_priv('comment_manage');
/* act操作项的初始化 */
if ($operation == 'index')
{
    $operation = 'comment_list';
}

/*------------------------------------------------------ */
//-- 获取没有回复的评论列表
/*------------------------------------------------------ */
if ($operation == 'comment_list')
{

    $smarty->assign('ur_here',      '评论管理');
    $smarty->assign('full_page',    1);

    $list = get_comment_list();

    $smarty->assign('comment_list', $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 分页 */
  
    $pager = get_page($list['filter']);

    $smarty->assign('pager',   $pager);
    $type     = empty($_REQUEST['type']) ? 1 :0 ;
    $smarty->assign('type',$type);
    
    $smarty->display('comment_list.htm');
}

/*------------------------------------------------------ */
//-- 翻页、搜索、排序
/*------------------------------------------------------ */
if ($operation == 'query')
{
    $list = get_comment_list();

    $smarty->assign('comment_list', $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('comment_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

/*------------------------------------------------------ */
//-- 更新评论的状态为显示或者禁止
/*------------------------------------------------------ */
if ($operation == 'check')
{
    if ($_REQUEST['check'] == 'allow')
    {
        /* 允许评论显示 */
        $sql = "UPDATE " .$aos->table('comment'). " SET status = 1 WHERE comment_id = '$_REQUEST[id]'";
        $db->query($sql);

        //add_feed($_REQUEST['id'], COMMENT_GOODS);

        /* 清除缓存 */
        clear_cache_files();

        aos_header("Location: index.php?act=comment&op=reply&id=$_REQUEST[id]\n");
        exit;
    }
    else
    {
        /* 禁止评论显示 */
        $sql = "UPDATE " .$aos->table('comment'). " SET status = 0 WHERE comment_id = '$_REQUEST[id]'";
        $db->query($sql);

        /* 清除缓存 */
        clear_cache_files();

        aos_header("Location: index.php?act=comment&op=reply&id=$_REQUEST[id]\n");
        exit;
    }
}

/*------------------------------------------------------ */
//-- 删除某一条评论
/*------------------------------------------------------ */
elseif ($operation == 'remove')
{
    check_authz_json('comment_priv');

    $id = intval($_POST['id']);

    $sql = "DELETE FROM " .$aos->table('comment'). " WHERE comment_id = '$id'";
    $res = $db->query($sql);
    if ($res)
    {
        $db->query("DELETE FROM " .$aos->table('comment'). " WHERE parent_id = '$id'");
        admin_log('', 'remove', 'ads');
        make_json_result($id);
    }else{
        make_json_error('操作失败');
    }

}

/*------------------------------------------------------ */
//-- 批量删除用户评论
/*------------------------------------------------------ */
if ($operation == 'batch')
{
    $sel_action = isset($_POST['sel_action']) ? trim($_POST['sel_action']) : 'deny';

    if (isset($_POST['checkboxes']))
    {
        switch ($sel_action)
        {
            case 'remove':
                $db->query("DELETE FROM " . $aos->table('comment') . " WHERE " . db_create_in($_POST['checkboxes'], 'comment_id'));
                $db->query("DELETE FROM " . $aos->table('comment') . " WHERE " . db_create_in($_POST['checkboxes'], 'parent_id'));
                break;

           case 'allow' :
               $db->query("UPDATE " . $aos->table('comment') . " SET status = 1  WHERE " . db_create_in($_POST['checkboxes'], 'comment_id'));
               break;

           case 'deny' :
               $db->query("UPDATE " . $aos->table('comment') . " SET status = 0  WHERE " . db_create_in($_POST['checkboxes'], 'comment_id'));
               break;

           default :
               break;
        }

        clear_cache_files();
        $sel_action = ($sel_action == 'remove') ? 'remove' : 'edit';
        admin_log('', $sel_action, 'adminlog');

        $link[] = array('text' => '返回列表', 'href' => 'index.php?act=comment&op=comment_list');
        sys_msg(sprintf('执行成功!', count($_POST['checkboxes'])), 0, $link);
    }
    else
    {
        /* 提示信息 */
        $link[] = array('text' => '返回列表', 'href' => 'index.php?act=comment&op=comment_list');
        sys_msg('您没有选择需要删除的评论!', 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */

if ($operation == 'toggle_status')
{
    check_authz_json('comment_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (comment_update($id, array('status' => $val)) != false)
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}
if ($operation == 'toggle_is_top')
{
    check_authz_json('comment_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (comment_update($id, array('is_top' => $val)) != false)
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}
function comment_update($comment_id, $args)
{
    if (empty($args) || empty($comment_id))
    {
        return false;
    }
    return $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('comment'), $args, 'update', "comment_id='$comment_id'");
}

/**
 * 获取评论列表
 * @access  public
 * @return  array
 */
function get_comment_list()
{
    /* 查询条件 */
    $filter['keywords']     = empty($_REQUEST['keywords']) ? 0 : trim($_REQUEST['keywords']);
    $filter['status']     = empty($_REQUEST['type']) ? 1 :0 ;

    if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
    {
        $filter['keywords'] = json_str_iconv($filter['keywords']);
    }
    $sort = array('comment_id','comment_rank','add_time','id_value','status'); $filter['sort_by'] = in_array($_REQUEST['sort_by'], $sort) ? trim($_REQUEST['sort_by']) : 'add_time'; $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : 'ASC';

    $where = (!empty($filter['keywords'])) ? " AND content LIKE '%" . mysql_like_quote($filter['keywords']) . "%' " : '';
    $where .=" and status = ".$filter['status'] ;

    $sql = "SELECT count(*) FROM " .$GLOBALS['aos']->table('comment'). " WHERE parent_id = 0 $where";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    /* 获取评论数据 */
    $arr = array();
    $sql  = "SELECT c.*, u.nickname FROM " .$GLOBALS['aos']->table('comment'). " as c  left join "
    .$GLOBALS['aos']->table("users")." as u on u.user_id=c.user_id ".
    " WHERE c.parent_id = 0 $where " .
            " ORDER BY $filter[sort_by] $filter[sort_order] ".
            " LIMIT ". $filter['start'] .", $filter[page_size]";
    $res  = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $sql = "SELECT goods_name FROM " .$GLOBALS['aos']->table('goods'). " WHERE goods_id='$row[id_value]'";
        $row['title'] = $GLOBALS['db']->getOne($sql);

        /* 标记是否回复过 */
//        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['aos']->table('comment'). " WHERE parent_id = '$row[comment_id]'";
//        $row['is_reply'] =  ($GLOBALS['db']->getOne($sql) > 0) ?
//            $GLOBALS['_LANG']['yes_reply'] : $GLOBALS['_LANG']['no_reply'];

        $row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

        $arr[] = $row;
    }
    $filter['keywords'] = stripslashes($filter['keywords']);
    $arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>