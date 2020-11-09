<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

if (!empty($action) && $action == 'ajax')
{
    include_once(ROOT_PATH . 'source/library/order.php');
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';

	$limit = " limit $last,$amount";//每次加载的个数
    $goodslist = get_rank_goods($limit);
	
	foreach($goodslist['goods'] as $key=> $val){
		$key=$key+1+$last;
		$GLOBALS['smarty']->assign('key',$key);
		$GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('goods',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/rank_list.htm');
	}
	if($goodslist['count']>99){
		$goodslist['count']=99;
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}

//var_dump($_CFG['stock_dec_time']);
$cache_id = sprintf('%X', crc32('rank'));
if (!$smarty->is_cached('rank.htm', $cache_id))
{
    assign_template();
    $share['title'] = $GLOBALS['_CFG']['rank_title'];
    $share['desc'] = $GLOBALS['_CFG']['rank_desc'];
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
}


$smarty->display('rank.htm', $cache_id);



/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function get_rank_goods($limit = 1)
{
	$where = "g.is_on_sale = 1 AND g.is_delete = 0 ";

    /* 获得商品列表 */
    $sql='SELECT count(*) ' .
            'FROM ' . $GLOBALS['aos']->table('goods') . ' AS g ' .
            "WHERE $where ";
    $count=$GLOBALS['db']->getOne($sql);

   $sql="select g.goods_id, g.goods_name, g.goods_img,g.goods_number,count(1)
from ". $GLOBALS['aos']->table('goods') ." as g left join ". $GLOBALS['aos']->table('order_goods') ." as o on g.goods_id=o.goods_id
WHERE $where group  by g.goods_id
ORDER BY (count(1)+g.virtual_sales) DESC,g.goods_id DESC $limit ";

    $result = $GLOBALS['db']->getAll($sql);
    $res=array();
	foreach ($result AS $idx => $row)
	{
		$goods[$idx]['goods_id']           = $row['goods_id'];
		$goods[$idx]['goods_name']         = $row['goods_name'];
		$tuan_price_list = get_tuan_price_list($row['goods_id']);
		$goods[$idx]['min_number'] = min(array_column($tuan_price_list,'number'));
		$goods[$idx]['max_number'] = max(array_column($tuan_price_list,'number'));
		$goods[$idx]['tuan_price'] = price_format(max(array_column($tuan_price_list,'price')));
		$goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
		$goods[$idx]['sales']    = get_cum_sales($row['goods_id']);
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
	$res['goods']=$goods;
	$res['count']=$count;
    return $res;
}
?>