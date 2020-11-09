<?php

/*商品分类*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

if (!empty($action) && $action == 'ajax')
{
	$status = $_REQUEST['status'] ? $_REQUEST['status'] : 0;
	$page = $_REQUEST['page'] ? $_REQUEST['page'] : 0;
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
  $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
	$limit = " limit $last,$amount";//每次加载的个数
  $goodslist = get_sale_goods($status, $limit);
	foreach($goodslist['info'] as $val){
		$GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('goods',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/sale_list.htm');
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}
if ($action == 'index')
{
  $cache_id = sprintf('%X', crc32('sale'));
  if (!$smarty->is_cached('sale.htm', $cache_id))
  {
	  assign_template();
    $share['title'] = $GLOBALS['_CFG']['sale_title'];
    $share['desc'] = $GLOBALS['_CFG']['sale_desc'];
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
  }
  $smarty->display('sale.htm', $cache_id);
}

/*获得抽奖商品*/
function get_sale_goods($status, $limit = 1)
{
	//select a.* from 表a  a inner join (select  max(FInterid) as maxf from 表a) b on a.finterid=b.maxf
	
	$where = "g.is_on_sale = 1 AND g.is_delete = 0";
	switch($status)
	{
	  case 1 : //9.9
	    $where .= " and t.tuan_prices <= 9.9 ";
		break;
		case 2 : //19.9
	    $where .= "  and t.tuan_prices <= 19.9 and t.tuan_prices > 9.9";
		  break;
		case 3 : //29.9
	    $where .= "  and t.tuan_prices <= 29.9 and t.tuan_prices > 19.9";
		break;
	  default:
	  	$where .= "  and t.tuan_prices <= 39.9";
		break;
	}
  /* 获得商品列表 */
  $sql = 'SELECT count(g.goods_id)  ' .
            'FROM ' . $GLOBALS['aos']->table('goods') . ' AS g ' .
            "left join ( select max(tuan_price) as tuan_prices,goods_id from ".$GLOBALS['aos']->table('tuan_price').
            " group by goods_id) as t on g.goods_id = t.goods_id WHERE $where ";
  $count=$GLOBALS['db']->getOne($sql);

  /* 获得商品列表 */
    $sql = 'SELECT g.tuan_img,g.goods_id, g.goods_name, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price,t.tuan_prices,' .
                'g.goods_brief, g.goods_img ' .
            'FROM ' . $GLOBALS['aos']->table('goods') . ' AS g ' .
            "left join ( select max(tuan_price) as tuan_prices,goods_id from ".$GLOBALS['aos']->table('tuan_price').
            " group by goods_id) as t on g.goods_id = t.goods_id where $where  ORDER BY t.tuan_prices DESC $limit";
  $result = $GLOBALS['db']->getAll($sql);
  $res=array();
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
        $goods[$idx]['tuan_prices'] = $row['tuan_prices'];
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
	$res['info'] = $goods;
	$res['count'] = $count;
  return $res;
}

?>
