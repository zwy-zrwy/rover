<?php

/*商品分类*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
if(is_mobile())
{
    header("Location:index.php");exit;
}
/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

/* 获得请求的分类 ID */
if (isset($_REQUEST['id']))
{
    $cat_id = intval($_REQUEST['id']);
}

assign_template('cate', array($cat_id));

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
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/pc_goods.htm');
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}

$cache_id = sprintf('%X', crc32('pc_cate-'.$cat_id));
if (!$smarty->is_cached('pc.htm', $cache_id))
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
    	$smarty->assign('page_title','首页');
    }

    $categories = get_categories_tree();
   
	$smarty->assign('categories', $categories);

    $smarty->assign('cat_id',         $cat_id);

}
$smarty->display('pc.htm', $cache_id);

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
    $sql = "SELECT g.tuan_img,g.goods_id, g.goods_name, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price, " .
                "g.goods_brief, g.goods_img " .
            "FROM " . $GLOBALS['aos']->table('goods') . " AS g " .
            "WHERE $where ORDER BY sort_order DESC,goods_id DESC $limit";
    $result = $GLOBALS['db']->getAll($sql);
	
	
	foreach ($result AS $idx => $row)
	{
		$goods[$idx]['goods_id']           = $row['goods_id'];
		$goods[$idx]['goods_name']         = $row['goods_name'];
		$goods[$idx]['goods_brief']        = $row['goods_brief'];
		$goods[$idx]['goods_number']       = $row['goods_number'];
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['shop_price']   = price_format($row['shop_price']);
		$goods[$idx]['sales']       = get_cum_sales($row['goods_id']);
		$tuan_price_list = get_tuan_price_list($row['goods_id']);
		$goods[$idx]['min_number'] = min(array_column($tuan_price_list,'number'));
		$goods[$idx]['max_number'] = max(array_column($tuan_price_list,'number'));
		$goods[$idx]['tuan_price'] = price_format(max(array_column($tuan_price_list,'price')));
		$goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img'], true);
		$goods[$idx]['is_best'] = $row['is_best'];
        $goods[$idx]['is_hot'] = $row['is_hot'];
        $goods[$idx]['is_new'] = $row['is_new'];
	}
	$arr=array();
	$arr['goods']=$goods;
	$arr['count']=$goods_count;
    return $arr;
}

?>
