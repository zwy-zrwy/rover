<?php

/*商品分类*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

/* 获得请求的分类 ID */
if (isset($_REQUEST['id']))
{
    $cat_id = intval($_REQUEST['id']);
}
elseif (isset($_REQUEST['category']))
{
    $cat_id = intval($_REQUEST['category']);
}


/* 排序 */
$sort  = isset($_REQUEST['sort']) ? trim($_REQUEST['sort'])  : 'goods_id';


if (!empty($action) && $action == 'ajax')
{
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
	$limit = " limit $last,$amount";//每次加载的个数
	$children = get_children($cat_id);

    $goodslist = category_get_goods($children, $limit);

	foreach($goodslist['goods'] as $val){
		$GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('goods',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/tuan_list.htm');
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}

$cache_id = sprintf('%X', crc32('category-'.$cat_id));
if (!$smarty->is_cached('category.htm', $cache_id))
{
    /* 如果页面没有被缓存则重新获取页面的内容 */
    $children = get_children($cat_id);
    $cat = get_cat_info($cat_id);   // 获得分类的相关信息
    if (!empty($cat))
    {
        $smarty->assign('page_title',    htmlspecialchars($cat['cat_name']));
    }
    else
    {
    	$smarty->assign('page_title','全部分类');
    }

    $categories = get_categories_tree();
   
	$smarty->assign('categories', $categories);


    assign_template('c', array($cat_id));
    
    $smarty->assign('show_marketprice', $_CFG['show_marketprice']);
    $smarty->assign('category',         $cat_id);
	$smarty->assign('children',         $children);
	$smarty->assign('sort',             $sort);

}
$smarty->display('category.htm', $cache_id);

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得分类的信息
 *
 * @param   integer $cat_id
 *
 * @return  void
 */
function get_cat_info($cat_id)
{
    return $GLOBALS['db']->getRow('SELECT cat_name, keywords, cat_desc, style, parent_id FROM ' . $GLOBALS['aos']->table('category') .
        " WHERE cat_id = '$cat_id'");
}

/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function category_get_goods($children, $limit = 1)
{
	$where = "g.is_on_sale = 1 AND g.is_delete = 0 AND $children";

	$sql = 'SELECT count(goods_id) ' .
            'FROM ' . $GLOBALS['aos']->table('goods') . ' AS g ' .
            "WHERE $where ";
    $goods_count=$GLOBALS['db']->getOne($sql);
    /* 获得商品列表 */
    $sql = 'SELECT g.tuan_img,g.goods_id, g.goods_name, g.goods_number, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' .
                'g.goods_brief, g.goods_img ' .
            'FROM ' . $GLOBALS['aos']->table('goods') . ' AS g ' .
            "WHERE $where ORDER BY sort_order DESC,goods_id DESC $limit";
    $result = $GLOBALS['db']->getAll($sql);
	
	
	foreach ($result AS $idx => $row)
	{
		$goods[$idx]['goods_id']           = $row['goods_id'];
		$goods[$idx]['goods_name']         = $row['goods_name'];
		$goods[$idx]['goods_brief']        = $row['goods_brief'];
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['shop_price']   = price_format($row['shop_price']);
		$goods[$idx]['sales']       = get_cum_sales($row['goods_id']);
		$tuan_price_list = get_tuan_price_list($row['goods_id']);
		$goods[$idx]['min_number'] = min(array_column($tuan_price_list,'number'));
		$goods[$idx]['max_number'] = max(array_column($tuan_price_list,'number'));
		$goods[$idx]['tuan_price'] = price_format(max(array_column($tuan_price_list,'price')));
		$goods[$idx]['tuan_img']    = get_image_path($row['goods_id'], $row['tuan_img']);
		$goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img'], true);
		$goods[$idx]['is_best'] = $row['is_best'];
        $goods[$idx]['is_hot'] = $row['is_hot'];
        $goods[$idx]['is_new'] = $row['is_new'];
		$goods[$idx]['url']          = 'index.php?c=goods&id='.$row['goods_id'];
		$is_attr = is_attr($row['goods_id']);
		if($is_attr)
		{
			$goods[$idx]['goods_number'] = get_sku_num($row['goods_id']);
		}
		else
		{
			$goods[$idx]['goods_number']       = $row['goods_number'];
		}
		$goods[$idx]['ing']          = assign_tuan_ing($row['goods_id'],$_SESSION['user_id'],2);
	}
	$arr=array();
	$arr['goods']=$goods;
	$arr['count']=$goods_count;
    return $arr;
}

?>
