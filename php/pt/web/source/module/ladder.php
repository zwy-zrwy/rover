<?php

/*阶梯团*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

if (!empty($action) && $action == 'ajax')
{
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = $_REQUEST['page'] ? $_REQUEST['page'] : 0;
	$limit = " limit $last,$amount";//每次加载的个数
    $goodslist = get_ladder_goods($limit);
	foreach($goodslist['goods'] as $val){
		$GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('goods',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/tuan_list.htm');
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}

$cache_id = sprintf('%X', crc32('ladder'));
if (!$smarty->is_cached('ladder.htm', $cache_id))
{
    $smarty->assign('page_title','阶梯团');
    assign_template();
    $share['title'] = $GLOBALS['_CFG']['ladder_title'];
    $share['desc'] = $GLOBALS['_CFG']['ladder_desc'];
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
}
$smarty->display('ladder.htm', $cache_id);

/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function get_ladder_goods($limit = 1)
{
	$where = "g.is_on_sale = 1 AND g.is_delete = 0";

	
    $sql = "SELECT count(g.goods_id) " .
            " FROM " . $GLOBALS['aos']->table('goods') .
            " as g,(select goods_id,count(1) as num from ". $GLOBALS['aos']->table('tuan_price') ."  group  by goods_id ) as o WHERE $where and g.goods_id=o.goods_id and o.num > 1";
    $goods_count = $GLOBALS['db']->getOne($sql);

    /* 获得商品列表 */
    $sql = "SELECT g.tuan_img, g.goods_id, g.goods_name, g.market_price, g.shop_price, g.goods_number" .
            " FROM " . $GLOBALS['aos']->table('goods') .
            " as g,(select goods_id,count(1) as num from ". $GLOBALS['aos']->table('tuan_price') ."  group  by goods_id ) as o WHERE $where and g.goods_id=o.goods_id and o.num > 1 ORDER BY sort_order DESC,g.goods_id DESC $limit";
    
    $result = $GLOBALS['db']->getAll($sql);
	
	foreach ($result AS $idx => $row)
	{
		$goods[$idx]['goods_id']           = $row['goods_id'];
		$goods[$idx]['goods_name']         = $row['goods_name'];
		$goods[$idx]['goods_number']       = $row['goods_number'];
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['shop_price']   = price_format($row['shop_price']);
		$goods[$idx]['sales']       = get_cum_sales($row['goods_id']);
		$tuan_price_list = get_tuan_price_list($row['goods_id']);
		$goods[$idx]['min_number'] = min(array_column($tuan_price_list,'number'));
		$goods[$idx]['max_number'] = max(array_column($tuan_price_list,'number'));
		$goods[$idx]['tuan_price'] = price_format(max(array_column($tuan_price_list,'price')));
		$goods[$idx]['tuan_img']    = get_image_path($row['goods_id'], $row['tuan_img']);
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
	}
	$arr = array();
	$arr['goods'] = $goods;
	$arr['count'] = $goods_count;
    return $arr;
}

?>
