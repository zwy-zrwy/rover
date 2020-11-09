<?php

define('IN_AOS', true);
require_once(ROOT_PATH . 'source/class/image.class.php');

/*初始化数据交换对象 */
$exc   = new exchange($aos->table("article"), $db, 'article_id', 'title');
$image = new cls_image();

/* 允许上传的文件类型 */
$allow_file_types = '|GIF|JPG|PNG|JPEG|';
admin_priv('article_manage');
/*------------------------------------------------------ */
//-- 文章列表
/*------------------------------------------------------ */
if ($operation == 'article_list')
{
    /* 取得过滤条件 */
    $filter = array();
    $smarty->assign('cat_select',  article_cat_list());
    $smarty->assign('ur_here',      "文章列表");
    $smarty->assign('action_link',  array('text' => "添加文章", 'href' => 'index.php?act=article&op=add'));
    $smarty->assign('full_page',    1);
    $smarty->assign('filter',       $filter);

    $article_list = get_articleslist();

    $smarty->assign('article_list',    $article_list['arr']);
    $smarty->assign('filter',          $article_list['filter']);
    $smarty->assign('record_count',    $article_list['record_count']);
    $smarty->assign('page_count',      $article_list['page_count']);

    $sort_flag  = sort_flag($article_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    
    $pager = get_page($article_list['filter']);

    $smarty->assign('pager',   $pager);

    

    
    $smarty->display('article_list.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($operation == 'query')
{
    check_authz_json('article_manage');

    $article_list = get_articleslist();

    $smarty->assign('article_list',    $article_list['arr']);
    $smarty->assign('filter',          $article_list['filter']);
    $smarty->assign('record_count',    $article_list['record_count']);
    $smarty->assign('page_count',      $article_list['page_count']);

    $sort_flag  = sort_flag($article_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('article_list.htm'), '',
        array('filter' => $article_list['filter'], 'page_count' => $article_list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加文章
/*------------------------------------------------------ */
if ($operation == 'add')
{
    /* 权限判断 */
    admin_priv('article_manage');

    /*初始化*/
    $article = array();
    $article['is_open'] = 1;


    if (isset($_GET['id']))
    {
        $smarty->assign('cur_id',  $_GET['id']);
    }
    $smarty->assign('article',     $article);
    $smarty->assign('cat_select',  article_cat_list());
    $smarty->assign('ur_here',     "添加文章");
    $smarty->assign('action_link', array('text' => "文章列表", 'href' => 'index.php?act=article&op=article_list'));
    $smarty->assign('form_action', 'insert');

    
    $smarty->display('article_info.htm');
}

/*------------------------------------------------------ */
//-- 添加文章
/*------------------------------------------------------ */
if ($operation == 'insert')
{
    /* 权限判断 */
    admin_priv('article_manage');

    /*检查是否重复*/
    $is_only = $exc->is_only('title', $_POST['title'],0, " cat_id ='$_POST[article_cat]'");

    if (!$is_only)
    {
        sys_msg($_POST['title']."标题重复", 1);
    }

    $spic = basename($image->upload_image($_FILES['spic'],'article'));
    $bpic = basename($image->upload_image($_FILES['bpic'],'article'));


    /*插入数据*/
    $add_time = gmtime();
    $sql = "INSERT INTO ".$aos->table('article')."(title, cat_id, is_open,  ".
                " keywords, content, add_time, spic, bpic, link, description) ".
            "VALUES ('$_POST[title]', '$_POST[cat_id]', '$_POST[is_open]', ".
                "  '$_POST[keywords]', '$_POST[content]', ".
                "'$add_time', '$spic', '$bpic', '$_POST[link_url]', '$_POST[description]')";
    $db->query($sql);

    /* 处理关联商品 */
    $article_id = $db->insert_id();
 

    $link[0]['text'] = "继续添加";
    $link[0]['href'] = 'index.php?act=article&op=add';

    $link[1]['text'] = "返回文章列表";
    $link[1]['href'] = 'index.php?act=article&op=article_list';

    admin_log($_POST['title'],'add','article');

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg("添加成功",0, $link);
}

/*------------------------------------------------------ */
//-- 编辑
/*------------------------------------------------------ */
if ($operation == 'edit')
{
    /* 权限判断 */
    admin_priv('article_manage');

    /* 取文章数据 */
    $sql = "SELECT * FROM " .$aos->table('article'). " WHERE article_id='$_REQUEST[id]'";
    $article = $db->GetRow($sql);

    $smarty->assign('article',     $article);
    $smarty->assign('cat_select',  article_cat_list());
    $smarty->assign('ur_here',     '修改文章');
    $smarty->assign('action_link', array('text' => '文章列表', 'href' => 'index.php?act=article&op=article_list&' . list_link_postfix()));
    $smarty->assign('form_action', 'update');

    
    $smarty->display('article_info.htm');
}

if ($operation =='update')
{
    /* 权限判断 */
    admin_priv('article_manage');

    /*检查文章名是否相同*/
    $is_only = $exc->is_only('title', $_POST['title'], $_POST['id'], "cat_id = '$_POST[article_cat]'");

    if (!$is_only)
    {
        sys_msg($_POST['title']."标题重复", 1);
    }


    if (empty($_POST['cat_id']))
    {
        $_POST['cat_id'] = 0;
    }

    $spic = basename($image->upload_image($_FILES['spic'],'article'));
    $bpic = basename($image->upload_image($_FILES['bpic'],'article'));

    $sql = "SELECT spic,bpic FROM " . $aos->table('article') . " WHERE article_id = '$_POST[id]'";
    $old_url = $db->getRow($sql);


    if(!empty($spic))
    {
        if ($old_url['spic'] != '')
        {
            @unlink(ROOT_PATH.'uploads/article/'.$old_url['spic']);
        }
        $sql = "UPDATE " . $aos->table('article') . " SET spic = '" . $spic . "' WHERE article_id='$_POST[id]'";
        $db->query($sql);
    }
    if(!empty($bpic))
    {
        if ($old_url['bpic'] != '')
        {
            @unlink(ROOT_PATH.'uploads/article/'.$old_url['bpic']);
        }
        $sql = "UPDATE " . $aos->table('article') . " SET bpic = '" . $bpic . "' WHERE article_id='$_POST[id]'";
        $db->query($sql);
    }

    if ($exc->edit("title='$_POST[title]', cat_id='$_POST[cat_id]', is_open='$_POST[is_open]',  keywords ='$_POST[keywords]', content='$_POST[content]', link='$_POST[link_url]', description = '$_POST[description]'", $_POST['id']))
    {
        $link[0]['text'] = "返回文章列表";
        $link[0]['href'] = 'index.php?act=article&op=article_list&' . list_link_postfix();

        
        admin_log($_POST['title'], 'edit', 'article');

        clear_cache_files();

        sys_msg("添加成功", 0, $link);
    }
    else
    {
        die($db->error());
    }
}


/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($operation == 'toggle_show')
{
    check_authz_json('article_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_open = '$val'", $id);
    clear_cache_files();

    make_json_result($val);
}


/*------------------------------------------------------ */
//-- 删除文章主题
/*------------------------------------------------------ */
elseif ($operation == 'remove')
{
    check_authz_json('article_manage');

    $id = intval($_REQUEST['id']);

    /* 删除原来的文件 */
    $sql = "SELECT spic,bpic FROM " . $aos->table('article') . " WHERE article_id = '$id'";
    $old_url = $db->getRow($sql);
    if ($old_url['spic'] != '' && strpos($old_url['spic'], 'http://') === false && strpos($old_url['spic'], 'https://') === false)
    {
        @unlink(ROOT_PATH . $old_url['spic']);
    }
    if ($old_url['bpic'] != '' && strpos($old_url['bpic'], 'http://') === false && strpos($old_url['bpic'], 'https://') === false)
    {
        @unlink(ROOT_PATH . $old_url['bpic']);
    }

    $name = $exc->get_name($id);
    $exc->drop($id);

    make_json_result($id);
}

/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */

elseif ($operation == 'batch')
{
    /* 批量删除 */
    if (isset($_POST['type']))
    {
        if ($_POST['type'] == 'button_remove')
        {
            admin_priv('article_manage');

            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                $lnk[] = array('text' => "返回文章列表", 'href' => 'index.php?act=article&op=article_list');

                sys_msg("没有选择文章", 0,$lnk);
            }

            /* 删除原来的文件 */
            $sql = "SELECT file_url FROM " . $aos->table('article') .
                    " WHERE article_id " . db_create_in(join(',', $_POST['checkboxes'])) .
                    " AND file_url <> ''";

            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                $old_url = $row['file_url'];
                if (strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false)
                {
                    @unlink(ROOT_PATH . $old_url);
                }
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
                if ($exc->drop($id))
                {
                    $name = $exc->get_name($id);
                    admin_log(addslashes($name),'remove','article');
                }
            }

        }

        /* 批量隐藏 */
        if ($_POST['type'] == 'button_hide')
        {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sys_msg('您没有选择任何文章', 1);
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
              $exc->edit("is_open = '0'", $id);
            }
        }

        /* 批量显示 */
        if ($_POST['type'] == 'button_show')
        {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sys_msg('您没有选择任何文章', 1);
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
              $exc->edit("is_open = '1'", $id);
            }
        }

        /* 批量移动分类 */
        if ($_POST['type'] == 'move_to')
        {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']) )
            {
                sys_msg('您没有选择任何文章', 1);
            }

            if(!$_POST['target_cat'])
            {
                sys_msg('请选择文章分类！', 1);
            }
            
            foreach ($_POST['checkboxes'] AS $key => $id)
            {
              $exc->edit("cat_id = '".$_POST['target_cat']."'", $id);
            }
        }
    }

    /* 清除缓存 */
    clear_cache_files();
    $lnk[] = array('text' => "返回文章列表", 'href' => 'index.php?act=article&op=article_list');
    sys_msg("批量操作成功", 0, $lnk);
}

/* 获得文章分类列表 */
function article_cat_list()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('article_cat');
    return $GLOBALS['db']->GetAll($sql);
}

/* 获得文章列表 */
function get_articleslist()
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
        $filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'a.article_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = '';
        if (!empty($filter['keyword']))
        {
            $where = " AND a.title LIKE '%" . mysql_like_quote($filter['keyword']) . "%'";
        }
        if ($filter['cat_id'])
        {
            $where .= " AND a." . get_article_children($filter['cat_id']);
        }

        /* 文章总数 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['aos']->table('article'). ' AS a '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('article_cat'). ' AS ac ON ac.cat_id = a.cat_id '.
               'WHERE 1 ' .$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取文章数据 */
        $sql = 'SELECT a.* , ac.cat_name '.
               'FROM ' .$GLOBALS['aos']->table('article'). ' AS a '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('article_cat'). ' AS ac ON ac.cat_id = a.cat_id '.
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