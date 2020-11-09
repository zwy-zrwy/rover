<?php

/*商品分类*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

$key  = isset($_REQUEST['key']) ? htmlspecialchars(trim($_REQUEST['key']))  : '';

/* 排序 */
$sort  = isset($_REQUEST['sort']) ? trim($_REQUEST['sort'])  : 'goods_id';

if ($action == 'ajax_goods')
{
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
	$limit = " limit $last,$amount";//每次加载的个数
    $goodslist = get_search_goods($key, $limit);
	foreach($goodslist['goods'] as $val){
		$GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('goods',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/tuan_list.htm');
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}

if ($action == 'ajax_key')
{
	$key  = isset($_POST['key']) ? htmlspecialchars(trim($_POST['key']))  : '';
	$where = "is_on_sale = 1 AND is_delete = 0";
	if(!empty($key))
	{
		$where .= " AND keywords LIKE '%$key%' ";
	}

	$sql = 'SELECT keywords FROM ' . $GLOBALS['aos']->table('goods') .
            "WHERE $where ORDER BY goods_id DESC";
    $result = $GLOBALS['db']->getAll($sql);
    $a=array();

    
    foreach($result as $vo){
        $array=explode(' ',$vo['keywords']);
        foreach($array as $v){

            if(strpos($v, $key) || strpos($v, $key)===0){
                $a[]['keywords']=$v;
            }
            
        }
    }
	die(json_encode($a));
}

if ($action == 'index')
{
    assign_template();
    $smarty->assign('show_marketprice', $_CFG['show_marketprice']);
	$smarty->assign('key',             $key);
	$smarty->assign('sort',             $sort);


    $smarty->display('search.htm');
}

/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function get_search_goods($key, $limit = 1)
{
	$where = "g.is_on_sale = 1 AND g.is_delete = 0";
	if(!empty($key))
	{
		$where .= " AND (g.goods_name LIKE '%$key%' OR g.keywords LIKE '%$key%')";
	}

	$sql = 'SELECT count(goods_id) ' .
            'FROM ' . $GLOBALS['aos']->table('goods') . ' AS g ' .
            "WHERE $where ";
    $goods_count=$GLOBALS['db']->getOne($sql);
    /* 获得商品列表 */
    $sql = 'SELECT g.tuan_img,g.goods_id, g.goods_name, g.goods_number, g.market_price, g.shop_price AS org_price, ' .
                'g.goods_brief, g.goods_img ' .
            'FROM ' . $GLOBALS['aos']->table('goods') . ' AS g ' .
            "WHERE $where ORDER BY goods_id DESC $limit";
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
	$arr=array();
	$arr['goods']=$goods;
	$arr['count']=$goods_count;
    return $arr;
}

?>
