<?php

/*商品相关函数库*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/**
 * 商品推荐usort用自定义排序行数
 */
function goods_sort($goods_a, $goods_b)
{
    if ($goods_a['sort_order'] == $goods_b['sort_order']) {
        return 0;
    }
    return ($goods_a['sort_order'] < $goods_b['sort_order']) ? -1 : 1;

}

/**
 * 获得指定分类同级的所有分类以及该分类下的子分类
 *
 * @access  public
 * @param   integer     $cat_id     分类编号
 * @return  array
 */
function get_categories_tree($cat_id = 0)
{
    if ($cat_id > 0)
    {
        $sql = 'SELECT parent_id FROM ' . $GLOBALS['aos']->table('category') . " WHERE cat_id = '$cat_id'";
        $parent_id = $GLOBALS['db']->getOne($sql);
    }
    else
    {
        $parent_id = 0;
    }

    /*
     判断当前分类中全是是否是底级分类，
     如果是取出底级分类上级分类，
     如果不是取当前分类及其下的子分类
    */
    $sql = 'SELECT count(*) FROM ' . $GLOBALS['aos']->table('category') . " WHERE parent_id = '$parent_id' AND is_show = 1 ";
    if ($GLOBALS['db']->getOne($sql) || $parent_id == 0)
    {
        /* 获取当前分类及其子分类 */
        $sql = 'SELECT cat_id, cat_name, cat_logo ,parent_id, is_show ' .
                'FROM ' . $GLOBALS['aos']->table('category') .
                "WHERE parent_id = '$parent_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";

        $res = $GLOBALS['db']->getAll($sql);

        foreach ($res AS $row)
        {
            if ($row['is_show'])
            {
                $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
				$cat_arr[$row['cat_id']]['logo'] = $row['cat_logo'];
                $cat_arr[$row['cat_id']]['url']  = 'index.php?c=category&id='.$row['cat_id'];

                if (isset($row['cat_id']) != NULL)
                {
                    $cat_arr[$row['cat_id']]['cat_id'] = get_child_tree($row['cat_id']);
                }
            }
        }
    }
    if(isset($cat_arr))
    {
        return $cat_arr;
    }
}

function get_child_tree($tree_id = 0)
{
    $three_arr = array();
    $sql = 'SELECT count(*) FROM ' . $GLOBALS['aos']->table('category') . " WHERE parent_id = '$tree_id' AND is_show = 1 ";
    if ($GLOBALS['db']->getOne($sql) || $tree_id == 0)
    {
        $child_sql = 'SELECT cat_id, cat_name, cat_logo, parent_id, is_show ' .
                'FROM ' . $GLOBALS['aos']->table('category') .
                "WHERE parent_id = '$tree_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";
        $res = $GLOBALS['db']->getAll($child_sql);
        foreach ($res AS $row)
        {
            if ($row['is_show'])

               $three_arr[$row['cat_id']]['id']   = $row['cat_id'];
               $three_arr[$row['cat_id']]['name'] = $row['cat_name'];
			   $three_arr[$row['cat_id']]['logo'] = $row['cat_logo'];
			   
               $three_arr[$row['cat_id']]['url']  = 'index.php?c=category&id='.$row['cat_id'];

               if (isset($row['cat_id']) != NULL)
                   {
                       $three_arr[$row['cat_id']]['cat_id'] = get_child_tree($row['cat_id']);

            }
        }
    }
    return $three_arr;
}


/**
 * 获得商品的详细信息
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  void
 */
function get_goods_info($goods_id)
{
    $time = gmtime();
    $sql = 'SELECT g.*, m.type_money AS bonus_money, ' .
                'IFNULL(AVG(r.comment_rank), 0) AS comment_rank ' .
            'FROM ' . $GLOBALS['aos']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['aos']->table('category') . ' AS c ON g.cat_id = c.cat_id ' .
            'LEFT JOIN ' . $GLOBALS['aos']->table('comment') . ' AS r '.
                'ON r.id_value = g.goods_id AND r.parent_id = 0 AND r.status = 1 ' .
            'LEFT JOIN ' . $GLOBALS['aos']->table('bonus_type') . ' AS m ' .
                "ON g.bonus_type_id = m.type_id AND m.send_start_date <= '$time' AND m.send_end_date >= '$time'" .
            "WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 " .
            "GROUP BY g.goods_id";
    $row = $GLOBALS['db']->getRow($sql);

    if ($row !== false)
    {
        /* 用户评论级别取整 */
        $row['comment_rank']  = ceil($row['comment_rank']) == 0 ? 5 : ceil($row['comment_rank']);

        /* 获得商品的销售价格 */
        $row['market_price']        = price_format($row['market_price']);
        $row['shop_price_formated'] = price_format($row['shop_price']);


        /* 修正重量显示 */
        $row['goods_weight']  = (intval($row['goods_weight']) > 0) ?
            $row['goods_weight'] . '千克' :
            ($row['goods_weight'] * 1000) . '克';

        /* 修正上架时间显示 */
        $row['add_time']      = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);

        $is_attr = is_attr($row['goods_id']);
        if($is_attr)
        {
            $row['goods_number'] = get_sku_num($row['goods_id']);
        }

        /* 修正积分：转换为可使用多少积分（原来是可以使用多少钱的积分） */
        $row['integral']      = $GLOBALS['_CFG']['integral_scale'] ? round($row['integral'] * 100 / $GLOBALS['_CFG']['integral_scale']) : 0;

        /* 修正优惠券 */
        $row['bonus_money']   = ($row['bonus_money'] == 0) ? 0 : price_format($row['bonus_money'], false);

        /* 修正商品图片 */
        $row['goods_img']   = get_image_path($goods_id, $row['goods_img']);

        /* 获取商品销量 */
        $row['cum_sales']   = get_cum_sales($row['goods_id']);

        return $row;
    }
    else
    {
        return false;
    }
}

/**
 * 获得指定商品的图片
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  array
 */
function get_goods_img($goods_id)
{
    $sql = 'SELECT goods_img FROM ' . $GLOBALS['aos']->table('goods') .
        " WHERE goods_id = '$goods_id'";
    $row = $GLOBALS['db']->getOne($sql);
    return $row;
}



/**
 * 获得指定商品的相册
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  array
 */
function get_goods_album($goods_id)
{
    $sql = 'SELECT album_id, album_img' .
        ' FROM ' . $GLOBALS['aos']->table('goods_album') .
        " WHERE goods_id = '$goods_id' ORDER BY album_sort LIMIT 5";
    $row = $GLOBALS['db']->getAll($sql);
    /* 格式化相册图片路径 */
    foreach($row as $key => $album)
    {
        $row[$key]['album_img'] = get_image_path($goods_id, $album['album_img'], 'album');
    }
    return $row;
}



/**
 * 判断某个商品是否正在特价促销期
 *
 * @access  public
 * @param   float   $price      促销价格
 * @param   string  $start      促销开始日期
 * @param   string  $end        促销结束日期
 * @return  float   如果还在促销期则返回促销价，否则返回0
 */
function bargain_price($price, $start, $end)
{
    if ($price == 0)
    {
        return 0;
    }
    else
    {
        $time = gmtime();
        if ($time >= $start && $time <= $end)
        {
            return $price;
        }
        else
        {
            return 0;
        }
    }
}

/**
 * 获得指定的规格的价格
 *
 * @access  public
 * @param   mix     $sku   规格ID
 * @return  void
 */
function sku_price($sku)
{
    if ($sku)
    {
        $sql = 'SELECT SUM(attr_price) AS attr_price FROM ' . $GLOBALS['aos']->table('goods_attr') . " WHERE attr_id = $sku";
        $price = floatval($GLOBALS['db']->getOne($sql));
    }
    else
    {
        $price = 0;
    }
    return $price;
}


/**
 * 取得商品信息
 * @param   int     $goods_id   商品id
 * @return  array
 */
function goods_info($goods_id)
{
    $sql = "SELECT * " .
            "FROM " . $GLOBALS['aos']->table('goods') .
            "WHERE goods_id = '$goods_id'";
    $row = $GLOBALS['db']->getRow($sql);
    if (!empty($row))
    {
        /* 修正重量显示 */
        $row['goods_weight'] = (intval($row['goods_weight']) > 0) ?
            $row['goods_weight'] . '千克' :
            ($row['goods_weight'] * 1000) . '克';

        /* 修正图片 */
        $row['goods_img'] = get_image_path($goods_id, $row['goods_img']);
    }

    return $row;
}

/**
 *  获取商品的累计销量
 * @param       string      $goods_id
 * @return      int
 */
function get_cum_sales($goods_id)
{
    $sql = "SELECT sum(goods_number) FROM " . $GLOBALS['aos']->table('order_goods') . " AS g ,".$GLOBALS['aos']->table('order_info') . " AS o WHERE o.order_id = g.order_id and g.goods_id = " . $goods_id . " and o.order_status = 5";
    $cum_sales = $GLOBALS['db']->getOne($sql);
    $sql_goods = "SELECT virtual_sales FROM " . $GLOBALS['aos']->table('goods') . " WHERE goods_id = '$goods_id'";
    $cum_sales += $GLOBALS['db']->getOne($sql_goods);
    if($cum_sales > 1000)
    {
       $cum_sales=sprintf("%.1f",$cum_sales/10000).'万';
    }
    return $cum_sales;
}


/**
 *  获取商品的累计评论
 * @param       string      $goods_id
 * @return      int
 */
function get_comment_num($goods_id)
{
     $sql= "select count(*) from ".$GLOBALS['aos']->table('comment')." where id_value='".$goods_id."'  AND status = 1";
     return $GLOBALS['db']->getOne($sql);
}


/*随机获取商品*/
function rand_goods($goods_id)
{
    $where = "is_on_sale = 1 AND is_delete = 0 ";
	if($goods_id)
	{
		$where .= "and goods_id <> $goods_id ";
	}
    $sql = 'SELECT goods_id, goods_name, goods_img ' .
            'FROM ' . $GLOBALS['aos']->table('goods') .
            "WHERE $where ORDER BY rand() limit 12";
    $res = $GLOBALS['db']->query($sql);
    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['goods_id']]['goods_id']     = $row['goods_id'];
        $arr[$row['goods_id']]['goods_name']   = $row['goods_name'];
        $arr[$row['goods_id']]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
        $tuan_price_list = get_tuan_price_list($row['goods_id']);
        $arr[$row['goods_id']]['tuan_price'] = price_format(max(array_column($tuan_price_list,'price')));
        if($tuan_price && $row['is_tuan'])
        {
            $arr[$row['goods_id']]['goods_price'] = price_format($tuan_price);
        }
        else
        {
            $arr[$row['goods_id']]['goods_price'] = price_format($row['shop_price']);
        }
        $arr[$row['goods_id']]['sales']    = get_cum_sales($row['goods_id']);
        $arr[$row['goods_id']]['url']          = 'index.php?c=goods&id='.$row['goods_id'];
    }
    return $arr;
}

/*获取商品标签*/
function get_label_list($label)
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('goods_label')." WHERE enabled = 1 AND label_id in(".$label.") order by sort_order ASC";
    return $GLOBALS['db']->getAll($sql);
}
?>