<?php

/*动态内容函数库*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/**
 * 调用指定的广告位的广告
 *
 * @access  public
 * @param   integer $id     广告位ID
 * @param   integer $num    广告数量
 * @return  string
 */
function insert_ads($arr)
{
    static $static_res = NULL;
    $time = gmtime();
    if (!empty($arr['num']) && $arr['num'] != 1)
    {
        $sql  = 'SELECT a.ad_id, a.position_id, a.ad_link, a.ad_code, a.ad_name, p.ad_width, ' .
                    'p.ad_height, p.position_style ' .
                'FROM ' . $GLOBALS['aos']->table('ad') . ' AS a '.
                'LEFT JOIN ' . $GLOBALS['aos']->table('ad_position') . ' AS p ON a.position_id = p.position_id ' .
                "WHERE enabled = 1 AND start_time <= '" . $time . "' AND end_time >= '" . $time . "' ".
                    "AND a.position_id = '" . $arr['id'] . "' " .
                'ORDER BY a.ad_id LIMIT ' . $arr['num'];
        $res = $GLOBALS['db']->GetAll($sql);
    }
    else
    {
        if ($static_res[$arr['id']] === NULL)
        {
            $sql  = 'SELECT a.ad_id, a.position_id, a.ad_link, a.ad_code, a.ad_name, p.ad_width, '.
                        'p.ad_height, p.position_style ' .
                    'FROM ' . $GLOBALS['aos']->table('ad') . ' AS a '.
                    'LEFT JOIN ' . $GLOBALS['aos']->table('ad_position') . ' AS p ON a.position_id = p.position_id ' .
                    "WHERE enabled = 1 AND a.position_id = '" . $arr['id'] .
                        "' AND start_time <= '" . $time . "' AND end_time >= '" . $time . "' " .
                    'ORDER BY a.ad_id LIMIT 1';
            $static_res[$arr['id']] = $GLOBALS['db']->GetAll($sql);
        }
        $res = $static_res[$arr['id']];
    }
    $ads = array();
    $position_style = '';
    foreach ($res AS $row)
    {
        if ($row['position_id'] != $arr['id'])
        {
            continue;
        }
        $position_style = $row['position_style'];


		$src ="uploads/ads_img/$row[ad_code]";
		$ads[] = '<a href="'.$row["ad_link"].'"><img src="'.$src.'"/></a>';
    }
    $position_style = 'str:' . $position_style;

    $need_cache = $GLOBALS['smarty']->caching;
    $GLOBALS['smarty']->caching = false;

    $GLOBALS['smarty']->assign('ads', $ads);
    $val = $GLOBALS['smarty']->fetch($position_style);

    $GLOBALS['smarty']->caching = $need_cache;

    return $val;
}

/*进行中的团*/
function insert_tuan_ing($arr)
{
    $need_cache = $GLOBALS['smarty']->caching;
    $need_compile = $GLOBALS['smarty']->force_compile;

    $GLOBALS['smarty']->caching = false;
    $GLOBALS['smarty']->force_compile = true;
    $tuan_ing = assign_tuan_ing($arr['id'],$_SESSION['user_id'],5);
    
    $GLOBALS['smarty']->assign('tuan_ing',     $tuan_ing);

    $val = $GLOBALS['smarty']->fetch('inc/tuan_ing.htm');

    $GLOBALS['smarty']->caching = $need_cache;
    $GLOBALS['smarty']->force_compile = $need_compile;

    return $val;
}

/*商品收藏*/
function insert_collect($arr)
{
    $need_cache = $GLOBALS['smarty']->caching;
    $need_compile = $GLOBALS['smarty']->force_compile;
    $GLOBALS['smarty']->caching = false;
    $GLOBALS['smarty']->force_compile = true;
    $GLOBALS['smarty']->assign('id',           $arr['id']);
    $rec_id = $GLOBALS['db']->getOne("select rec_id from ".$GLOBALS['aos']->table("collect")." where user_id='".$_SESSION['user_id']."' and goods_id='".$arr['id']."'");
    if($rec_id)
    {
      $on = ' on';
    }
    else
    {
      $on = '';
    }
    $GLOBALS['smarty']->assign('on',     $on);
    $val = $GLOBALS['smarty']->fetch('inc/goods_collect.htm');
    $GLOBALS['smarty']->caching = $need_cache;
    $GLOBALS['smarty']->force_compile = $need_compile;
    return $val;
}
//获取关注状态
function insert_subscribe()
{
    $subscribe = $GLOBALS['db']->getOne("select subscribe from ".$GLOBALS['aos']->table("users")." where user_id='".$_SESSION['user_id']."'");
    
    return $subscribe;
}
?>