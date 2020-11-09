<?php

define('IN_AOS', true);


include_once(ROOT_PATH . 'source/class/image.class.php');
$image = new cls_image($_CFG['bgcolor']);
$exc   = new exchange($aos->table("ad"), $db, 'ad_id', 'ad_name');
$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');
admin_priv('ads_manage');
/*------------------------------------------------------ */
//-- 广告列表页面
/*------------------------------------------------------ */
if ($operation == 'ad_list')
{
    $pid = !empty($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;

    $smarty->assign('ur_here',     '广告列表');
    $smarty->assign('action_link', array('text' => '添加广告', 'href' => 'index.php?act=ads&op=add'));
    $smarty->assign('pid',         $pid);
     $smarty->assign('full_page',  1);

    $ads_list = get_adslist();

    $smarty->assign('ads_list',     $ads_list['ads']);
    $smarty->assign('filter',       $ads_list['filter']);
    $smarty->assign('record_count', $ads_list['record_count']);
    $smarty->assign('page_count',   $ads_list['page_count']);

    $sort_flag  = sort_flag($ads_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
   
    $pager = get_page($ads_list['filter']);

    $smarty->assign('pager',   $pager);

    
    $smarty->display('ads_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($operation == 'query')
{
    $ads_list = get_adslist();

    $smarty->assign('ads_list',     $ads_list['ads']);
    $smarty->assign('filter',       $ads_list['filter']);
    $smarty->assign('record_count', $ads_list['record_count']);
    $smarty->assign('page_count',   $ads_list['page_count']);

    $sort_flag  = sort_flag($ads_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('ads_list.htm'), '',
        array('filter' => $ads_list['filter'], 'page_count' => $ads_list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加新广告页面
/*------------------------------------------------------ */
elseif ($operation == 'add')
{
    admin_priv('ads_manage');

    $ad_link = empty($_GET['ad_link']) ? '' : trim($_GET['ad_link']);
    $ad_name = empty($_GET['ad_name']) ? '' : trim($_GET['ad_name']);

    $start_time = local_date('Y-m-d');
    $end_time   = local_date('Y-m-d', gmtime() + 3600 * 24 * 30);  // 默认结束时间为1个月以后

    $smarty->assign('ads',
        array('ad_link' => $ad_link, 'ad_name' => $ad_name, 'start_time' => $start_time,
            'end_time' => $end_time, 'enabled' => 1));

    $smarty->assign('ur_here',       '添加广告');
    $smarty->assign('action_link',   array('href' => 'index.php?act=ads&op=ad_list', 'text' => '广告列表'));
    $smarty->assign('position_list', get_position_list());

    $smarty->assign('form_act', 'insert');
    $smarty->assign('action',   'add');
    $smarty->assign('cfg_lang', $_CFG['lang']);

    
    $smarty->display('ads_info.htm');
}

/*------------------------------------------------------ */
//-- 新广告的处理
/*------------------------------------------------------ */
elseif ($operation == 'insert')
{
    admin_priv('ads_manage');

    /* 初始化变量 */
    $id      = !empty($_POST['id'])      ? intval($_POST['id'])    : 0;
    $type    = !empty($_POST['type'])    ? intval($_POST['type'])  : 0;
    $ad_name = !empty($_POST['ad_name']) ? trim($_POST['ad_name']) : '';
    $ad_link = !empty($_POST['ad_link']) ? trim($_POST['ad_link']) : '';

    /* 获得广告的开始时期与结束日期 */
    $start_time = local_strtotime($_POST['start_time']);
    $end_time   = local_strtotime($_POST['end_time']);

    /* 查看广告名称是否有重复 */
    $sql = "SELECT COUNT(*) FROM " .$aos->table('ad'). " WHERE ad_name = '$ad_name'";
    if ($db->getOne($sql) > 0)
    {
        $link[] = array('text' => "返回", 'href' => 'javascript:history.back(-1)');
        sys_msg("广告名称重复", 0, $link);
    }


    if ((isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] == 0) || (!isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name'] ) &&$_FILES['ad_img']['tmp_name'] != 'none'))
    {
        $ad_code = basename($image->upload_image($_FILES['ad_img'], 'ads_img'));
    }
    if (!empty($_POST['img_url']))
    {
        $ad_code = $_POST['img_url'];
    }
    if (((isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] > 0) || (!isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['ad_img']['tmp_name'] == 'none')) && empty($_POST['img_url']))
    {
        $link[] = array('text' => "返回", 'href' => 'javascript:history.back(-1)');
        sys_msg('广告的图片不能为空!', 0, $link);
    }



    /* 插入数据 */
    $sql = "INSERT INTO ".$aos->table('ad'). " (position_id,ad_name,ad_link,ad_code,start_time,end_time,enabled)
    VALUES ('$_POST[position_id]',
            '$ad_name',
            '$ad_link',
            '$ad_code',
            '$start_time',
            '$end_time',
            '1')";

    $db->query($sql);
    /* 记录管理员操作 */
    admin_log($_POST['ad_name'], 'add', 'ads');

    clear_cache_files(); // 清除缓存文件

    /* 提示信息 */
    $link[0]['text'] = "返回广告列表";
    $link[0]['href'] = 'index.php?act=ads&op=ad_list';

    $link[1]['text'] = "继续添加";
    $link[1]['href'] = 'index.php?act=ads&op=add';
    sys_msg("添加" . "&nbsp;" .$_POST['ad_name'] . "&nbsp;" . "成功",0, $link);

}

/*------------------------------------------------------ */
//-- 广告编辑页面
/*------------------------------------------------------ */
elseif ($operation == 'edit')
{
    admin_priv('ads_manage');

    /* 获取广告数据 */
    $sql = "SELECT * FROM " .$aos->table('ad'). " WHERE ad_id='".intval($_REQUEST['id'])."'";
    $ads_arr = $db->getRow($sql);

    $ads_arr['ad_name'] = htmlspecialchars($ads_arr['ad_name']);
    /* 格式化广告的有效日期 */
    $ads_arr['start_time']  = local_date('Y-m-d', $ads_arr['start_time']);
    $ads_arr['end_time']    = local_date('Y-m-d', $ads_arr['end_time']);


    if (strpos($ads_arr['ad_code'], 'http://') === false && strpos($ads_arr['ad_code'], 'https://') === false)
    {
        $src = '../' . DATA_DIR . '/ads/'. $ads_arr['ad_code'];
        $smarty->assign('img_src', $src);
    }
    else
    {
        $src = $ads_arr['ad_code'];
        $smarty->assign('url_src', $src);
    }




    $smarty->assign('ur_here',       '修改广告');
    $smarty->assign('action_link',   array('href' => 'index.php?act=ads&op=ad_list', 'text' => '广告列表'));
    $smarty->assign('form_act',      'update');
    $smarty->assign('action',        'edit');
    $smarty->assign('position_list', get_position_list());
    $smarty->assign('ads',           $ads_arr);

    
    $smarty->display('ads_info.htm');
}

/*------------------------------------------------------ */
//-- 广告编辑的处理
/*------------------------------------------------------ */
elseif ($operation == 'update')
{
    admin_priv('ads_manage');

    /* 初始化变量 */
    $id   = !empty($_POST['id'])   ? intval($_POST['id'])   : 0;
    $ad_link = !empty($_POST['ad_link']) ? trim($_POST['ad_link']) : '';


    /* 获得广告的开始时期与结束日期 */
    $start_time = local_strtotime($_POST['start_time']);
    $end_time   = local_strtotime($_POST['end_time']);


    if ((isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] == 0) || (!isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['ad_img']['tmp_name'] != 'none'))
    {
        $img_up_info = basename($image->upload_image($_FILES['ad_img'], 'ads_img'));
        $ad_code = "ad_code = '".$img_up_info."'".',';
    }
    else
    {
        $ad_code = '';
    }
    if (!empty($_POST['img_url']))
    {
        $ad_code = "ad_code = '$_POST[img_url]', ";
    }


    $ad_code = str_replace('../uploads/ad_img/', '', $ad_code);
    /* 更新信息 */
    $sql = "UPDATE " .$aos->table('ad'). " SET ".
            "position_id = '$_POST[position_id]', ".
            "ad_name     = '$_POST[ad_name]', ".
            "ad_link     = '$ad_link', ".
            $ad_code.
            "start_time  = '$start_time', ".
            "end_time    = '$end_time', ".
            "enabled     = '$_POST[enabled]' ".
            "WHERE ad_id = '$id'";
    $db->query($sql);

   /* 记录管理员操作 */
   admin_log($_POST['ad_name'], 'edit', 'ads');

   clear_cache_files(); // 清除模版缓存

   /* 提示信息 */
   $href[] = array('text' => "返回广告列表", 'href' => 'index.php?act=ads&op=ad_list');
   sys_msg("编辑" .' '.$_POST['ad_name'].' '. "成功", 0, $href);

}
//修改排序
elseif ($operation== 'edit_sort_order')
{

    $ad_id       = intval($_POST['id']);
    $sort_order     = intval($_POST['val']);

    if ($exc->edit("sort_order = '$sort_order'", $ad_id))
    {
        clear_cache_files();
        make_json_result($sort_order);
    }
}
elseif ($operation== 'toggle_enabled')
{
    $ad_id       = intval($_POST['id']);
    $enabled        = intval($_POST['val']);

    if ($exc->edit("enabled = '$enabled'", $ad_id))
    {
        clear_cache_files();
        make_json_result($enabled);
    }
}
/*------------------------------------------------------ */
//-- 删除广告位置
/*------------------------------------------------------ */
elseif ($operation == 'remove')
{
    check_authz_json('ads_manage');

    $id = intval($_REQUEST['id']);
    $img = $exc->get_name($id, 'ad_code');

    $exc->drop($id);

    if ((strpos($img, 'http://') === false) && (strpos($img, 'https://') === false) && get_file_suffix($img, $allow_suffix))
    {
        $img_name = basename($img);
        @unlink(ROOT_PATH. '/uploads/ads_img/'.$img_name);
    }

    admin_log('', 'remove', 'ads');

    //$url = 'index.php?act=ads&op=query&' . str_replace('op=remove', '', $_SERVER['QUERY_STRING']);

    make_json_result($id);
}

/* 获取广告数据列表 */
function get_adslist()
{
    /* 过滤查询 */
    $pid = !empty($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;

    $filter = array();
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'ad.ad_name' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $where = 'WHERE 1 and ad.position_id !=4 ';
    if ($pid > 0)
    {
        $where .= " AND ad.position_id = '$pid' ";
    }

    /* 获得总记录数据 */
    $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['aos']->table('ad'). ' AS ad ' . $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    $filter = page_and_size($filter);

    /* 获得广告数据 */
    $arr = array();
    $sql = 'SELECT ad.*, p.position_name '.
            'FROM ' .$GLOBALS['aos']->table('ad'). 'AS ad ' .
            'LEFT JOIN ' . $GLOBALS['aos']->table('ad_position'). ' AS p ON p.position_id = ad.position_id '.$where.
            'GROUP BY ad.ad_id '.
            'ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
         /* 格式化日期 */
         $rows['start_date']    = local_date($GLOBALS['_CFG']['date_format'], $rows['start_time']);
         $rows['end_date']      = local_date($GLOBALS['_CFG']['date_format'], $rows['end_time']);

         $arr[] = $rows;
    }

    return array('ads' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>